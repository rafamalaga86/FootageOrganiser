<?php

namespace FootageOrganiser;

use Carbon\Carbon;
use Exception;
use TypeError;

class DateTitleAdder
{
    protected static $files_without_date = [];

    protected static $files_without_time = [];

    public static function run(array $argv): void
    {
        echo 'DATE TITLE ADDER' . PHP_EOL;
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

        $carbon_modifier = $argv[1] ?? null;

        if (!$carbon_modifier) {
            throw new Exit1Exception('The carbon modifier argument is missing.');
        }
        try {
            $test = (new Carbon())->add($carbon_modifier);
        } catch(TypeError $error) {
            throw new Exit1Exception($carbon_modifier . ': There is something wrong with the carbon modifier you entered.');
        }

        $file_list = FileManagement::scandirTree('.');

        $filtered_list = [];
        foreach ($file_list as $file) {
            $quit = false;
            $filename = basename($file);

            if (in_array($filename, fileIgnores())) {
                continue;
            }

            // We have to filter out the ones without date in the title
            if (!FileManagement::hasOneCreationDateFromTitleYYYYMMDD($file)) {
                self::$files_without_date[] = $file;
                $quit = true;
            }

            // We have to filter out the ones without time in the title
            if (!FileManagement::hasOneCreationTimeFromTitleHHMMSS($file)) {
                self::$files_without_time[] = $file;
                $quit = true;
            }

            if ($quit) {
                continue;
            }


            // START
            try {
                $old_creation_date = FileManagement::getCreationDateFromTitleYYYYMMDD($filename, false);
                $old_creation_time = FileManagement::getCreationTimeFromTitleHHMMSS($filename, false);
                $old_creation_date_formatted = FileManagement::getCreationDateFromTitleYYYYMMDD($filename);
                $old_creation_time_formatted = FileManagement::getCreationTimeFromTitleHHMMSS($filename);
            } catch (Exception $exception) {
                throw new Exit1Exception($exception->getMessage());
            }

            $carbon = new Carbon($old_creation_date_formatted . 'T' . $old_creation_time_formatted);
            $carbon->add($carbon_modifier);

            $new_name = str_replace($old_creation_date, $carbon->format('Y-m-d'), $filename);
            $new_name = str_replace($old_creation_time, $carbon->format('H;i;s'), $new_name);

            $filtered_list[$file] = $new_name;

            echo $file . ' -> ';
            CommandLine::printGreen($new_name . PHP_EOL);
        }

        if (!$filtered_list) {
            CommandLine::printGreen('There is no files to be renamed.' . PHP_EOL);
            return;
        }

        echo PHP_EOL;
        if (self::$files_without_date) {
            echo 'One or more files didn\'t have ';
            CommandLine::printYellow('date: ' . PHP_EOL);
            CommandLine::printList(self::$files_without_date);
        }

        echo PHP_EOL;
        if (self::$files_without_time) {
            echo 'One or more files didn\'t have ';
            CommandLine::printYellow('time: ' . PHP_EOL);
            CommandLine::printList(self::$files_without_time);
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
}
