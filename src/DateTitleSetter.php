<?php

namespace FootageOrganiser;

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
            throw new Exit1Exception('The directory argument is missing.');
        }

        $dir = realpath($dir);

        if (!is_dir($dir)) {
            throw new Exit1Exception('Could not locate the directory argument.');
        }

        $could_change_dir = chdir($dir);

        if (!$could_change_dir) {
            throw new Exit1Exception('Could not change dir.');
        }

        $file_list = FileManagement::scandirTree('.');

        $filtered_list = [];
        foreach ($file_list as $file) {
            $filename = basename($file);

            // Is it in the list of files to ignore
            if (in_array($filename, fileIgnores())) {
                continue;
            }

            // We have to filter out the ones with date and time already in the title
            if (FileManagement::hasAtLeastOneDateInTitle($file)) {
                continue;
            }

            try {
                list($creation_date, $data_source_date) = FileManagement::getFileCreationDate($file);
                list($creation_time, $data_source_time) = FileManagement::getFileCreationTime($file, 'H;i;s');
            } catch (Exception $exception) {
                throw new Exit1Exception($exception->getMessage());
            }

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
            CommandLine::printGreen('There is no files to be renamed.' . PHP_EOL);
            return;
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
                throw new Exit1Exception($file .' <- File could not be renamed. Every file before this one in the list, was removed');
            }
        }

        CommandLine::printGreen('Done.' . PHP_EOL);
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
