<?php

namespace RafaMalaga86\FootageOrganiser;

use Exception;

class OrganiserScript {
    public static function run (array $argv)
    {
        echo 'FOOTAGE ORGANISING SCRIPT' . PHP_EOL;
        echo '-------------------------' . PHP_EOL;

        // Remove script name from argument list
        unset($argv[0]);
        $argv = array_values($argv);

        // // For option --test
        // $is_test = false;
        // $option_position = array_search('--test', $argv);
        // if ($option_position) {
        //     $is_test = true;

        //     unset($argv[$option_position]);
        //     $argv = array_values($argv);
        // }

        $main_dir = $argv[0] ?? null;
        $organise_dir = $argv[1] ?? null;

        if (!$main_dir) {
            CommandLine::printRed('The main directory argument is missing.' . PHP_EOL);
            exit(1);
        }

        if (!$organise_dir) {
            CommandLine::printRed('The organise directory argument is missing.' . PHP_EOL);
            exit(1);
        }

        $main_dir = realpath($main_dir);
        $organise_dir = realpath($organise_dir);

        if (!is_dir($main_dir)) {
            CommandLine::printRed('Could not locate the main directory argument' . PHP_EOL);
            exit(1);
        }

        if (!is_dir($organise_dir)) {
            CommandLine::printRed('Could not locate the organise directory argument' . PHP_EOL);
            exit(1);
        }

        $could_change_dir = chdir($organise_dir);

        if (!$could_change_dir) {
            CommandLine::printRed('Could not change dir to the organise dir' . PHP_EOL);
        }

        $file_list = FileManagement::scandirTree('.');
        self::organise($file_list, $main_dir);

        exit(0);
    }

    protected static function organise($file_list, $dir): void
    {
        $file_moving_list = [];
        foreach($file_list as $file) {
            try {
                list($creation_time, $data_source) = FileManagement::getFileCreationDate($file);
            } catch (Exception $e) {
                CommandLine::printRed($e->getMessage());
                exit(1);
            }

            $file_moving_list[] = [
                'file' => $file,
                'creation_time' => $creation_time,
                'data_source' => $data_source,
                'absolute' => $dir . '/' . $creation_time . '/' . $file
            ];
        }

        $abort = false;
        // Print the array of files and dates and check it won't override
        foreach ($file_moving_list as $file_moving) {
            echo $file_moving['file'] . ' ';

            switch ($file_moving['data_source']) {
                case 'title':
                    CommandLine::printGreen($file_moving['creation_time']);
                    break;
                case 'meta':
                    CommandLine::printYellow($file_moving['creation_time']);
            }

            echo ' -> ' . $file_moving['absolute'];
            echo PHP_EOL;

            if (file_exists($file_moving['absolute'])) {
                CommandLine::printRed('File already exists in the destiny' . PHP_EOL);
                $abort = true;
            }
        }

        if ($abort) {
            CommandLine::printRed('ERROR: There are some files that already exist in the destiny. Script didnt start.' . PHP_EOL);
            exit(1);
        }


        // Start with the moving if confirm
        echo 'Are you sure you want to do this? (y/n):';
        $handle = fopen('php://stdin','r');
        $response = strtolower(trim(fgets($handle)));
        fclose($handle);

        if($response != 'y' && $response != 'yes'){
            CommandLine::printRed('Aborting' . PHP_EOL);
            exit (0);
        }


        echo PHP_EOL . 'Starting to move....' . PHP_EOL;
        echo '====================' . PHP_EOL;
        // Move all the files
        $count = 0;
        $fails = [];
        foreach ($file_moving_list as $file_moving) {
            // Destiny dir exists? Create it
            if (!is_dir(dirname($file_moving['absolute']))) {
                mkdir(dirname($file_moving['absolute']), 0755, true);
            }

            echo $file_moving['file'] . ' -> ' . $file_moving['absolute'] . PHP_EOL;

            if (file_exists($file_moving['absolute'])) {
                CommandLine::printRed('File already exists in the destiny' . PHP_EOL);
                $was_successful = false;
            } else {
                $was_successful = copy($file_moving['file'], $file_moving['absolute']);
            }

            if ($was_successful) {
                $count++;
                CommandLine::printGreen('Done.' . PHP_EOL);
            } else {
                CommandLine::printRed('Not moved.' . PHP_EOL);
                $fails[] = $file_moving;
            }
        }

        echo PHP_EOL . $count . ' of ' . count($file_moving_list) . ' files copied' . PHP_EOL;
        if (!$fails) {
            CommandLine::printGreen('SCRIPT ENDED SUCCESSFULLY' . PHP_EOL);
        } else {
            CommandLine::printRed('SCRIPT ENDED WITH SOME COPY FAILS' . PHP_EOL);
            CommandLine::printList($fails);
        }
}
    }