<?php

namespace RafaMalaga86\FootageOrganiser;

use Exception;

class DateTitleSetter
{
    protected static $extensionsNotFound = [];

    public static function run(array $argv): void
    {
        echo 'DATE TITLE SETTER' . PHP_EOL;
        echo '======================' . PHP_EOL;

        // Remove script name from argument list
        unset($argv[0]);
        $argv = array_values($argv);

        $dir = $argv[0] ?? null;

        if (!$dir) {
            CommandLine::printRed('The directory argument is missing.' . PHP_EOL);
            exit(1);
        }

        $dir = realpath($dir);

        if (!is_dir($dir)) {
            CommandLine::printRed('Could not locate the directory argument.' . PHP_EOL);
            exit(1);
        }

        $could_change_dir = chdir($dir);

        if (!$could_change_dir) {
            CommandLine::printRed('Could not change dir.' . PHP_EOL);
        }

        $file_list = FileManagement::scandirTree('.');

        $filtered_list = [];
        foreach ($file_list as $file) {
            // We have to filter out the ones with title already in the name
            if (FileManagement::hasCreationDateFromTitleYYYYMMDD($file)) {
                continue;
            }

            try {
                list($creation_date, $data_source_date) = FileManagement::getFileCreationDate($file);
                $creation_time = FileManagement::getFileCreationTime($file);
            } catch (Exception $e) {
                CommandLine::printRed($e->getMessage() . PHP_EOL);
                exit(1);
            }

            $filename = basename($file);
            $ext = FileManagement::getFileExtension($file);

            $prefix = self::getPrefix($ext) ? self::getPrefix($ext) . '_' : '';
            $new_name = $prefix
                . $creation_date . '_'
                . $creation_time . '_'
                . $filename;
            $filtered_list[$file] = $new_name;

            echo $file . ' -> ';
            CommandLine::printGreen($new_name . PHP_EOL);
        }

        if (self::$extensionsNotFound) {
            echo PHP_EOL;
            CommandLine::printYellow('One or more files don\'t have a file prefix associated to their extension:');
            echo PHP_EOL;
            echo implode(', ', self::$extensionsNotFound);
            echo PHP_EOL;
        }

        if (!$filtered_list) {
            CommandLine::printRed('There is no files to be renamed.');
            echo PHP_EOL;
            exit(1);
        }

        CommandLine::confirmOrAbort();
        // If arrives here, is confirmed

        foreach ($filtered_list as $file => $new_name) {
            $file_array = array_reverse(explode('/', $file));
            $file_array[0] = $new_name;
            $new_file_array = array_reverse($file_array);
            $new_file = implode('/', $new_file_array);

            $was_renamed = rename($file, $new_file);

            if (!$was_renamed) {
                CommandLine::printRed('File could not be renamed.');
                exit(1);
            }
        }

        CommandLine::printGreen('Done.' . PHP_EOL);
        exit(0);
    }

    protected static function addExtensionNotFound(string $extension)
    {
        if (!in_array($extension, self::$extensionsNotFound)) {
            self::$extensionsNotFound[] = $extension;
        }
    }

    protected static function getPrefix(string $extension)
    {
        if (isset(prefixesByExtension()[$extension])) {
            return prefixesByExtension()[$extension];
        }

        self::addExtensionNotFound($extension);
    }
}
