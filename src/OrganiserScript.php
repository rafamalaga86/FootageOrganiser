<?php

namespace RafaMalaga86\FootageOrganiser;

use Exception;

class OrganiserScript
{
    public static function run(array $argv)
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
            CommandLine::printRed('Could not locate the main directory argument.' . PHP_EOL);
            exit(1);
        }

        if (!is_dir($organise_dir)) {
            CommandLine::printRed('Could not locate the organise directory argument.' . PHP_EOL);
            exit(1);
        }

        $could_change_dir = chdir($organise_dir);

        if (!$could_change_dir) {
            CommandLine::printRed('Could not change dir to the organise dir.' . PHP_EOL);
        }

        $file_list = FileManagement::scandirTree('.');
        self::organise($file_list, $main_dir);

        exit(0);
    }

    protected static function organise($file_list, $dir): void
    {
        $total_bits = 0;
        $file_moving_list = [];
        foreach ($file_list as $file) {
            try {
                list($creation_time, $data_source) = FileManagement::getFileCreationDate($file);
            } catch (Exception $e) {
                CommandLine::printRed($e->getMessage());
                exit(1);
            }

            $total_bits += filesize($file);
            $file_moving_list[] = [
                'file' => $file,
                'filesize' => filesize($file),
                'creation_time' => $creation_time,
                'data_source' => $data_source,
                'absolute_destiny' => $dir . '/' . $creation_time . '/' . FileManagement::trimFirstDot($file),
            ];
        }

        // Order by creation time
        usort($file_moving_list, function ($a, $b) {
            return $a['creation_time'] > $b['creation_time'];
        });

        // Check which directories exists already
        $dir_existing = [];
        $dir_created = [];
        foreach ($file_moving_list as $file) {
            $dir_exists = file_exists($dir . '/' . $file['creation_time']);
            if (!$dir_exists && !in_array($file['creation_time'], $dir_created)) {
                $dir_created[] = $file['creation_time'];
            } elseif ($dir_exists && !in_array($file['creation_time'], $dir_existing)) {
                $dir_existing[] = $file['creation_time'];
            }
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

            echo ' -> ' . $file_moving['absolute_destiny'];
            echo PHP_EOL;

            if (file_exists($file_moving['absolute_destiny'])) {
                CommandLine::printRed('File already exists in the destiny.' . PHP_EOL);
                $abort = true;
            }
        }

        if ($abort) {
            CommandLine::printRed('ERROR: There are some files that already exist in the destiny. Script didnt start.' . PHP_EOL);
            exit(1);
        }

        if (!$file_moving_list) {
            CommandLine::printRed('ERROR: There are no files in the dir to organise.' . PHP_EOL);
            exit(1);
        }


        echo PHP_EOL;
        if ($dir_existing) {
            echo 'Existing dirs: ' . implode(', ', $dir_existing) . PHP_EOL;
        }
        if ($dir_created) {
            echo 'Creating dirs: ';
            CommandLine::printGreen(implode(', ', $dir_created) . PHP_EOL);
        }

        // Start with the moving if confirm
        echo PHP_EOL;
        echo 'Are you sure you want to do this? (y/n):';
        $handle = fopen('php://stdin', 'r');
        $response = strtolower(trim(fgets($handle)));
        fclose($handle);

        if ($response != 'y' && $response != 'yes'){
            CommandLine::printRed('Aborting.' . PHP_EOL);
            exit(0);
        }


        echo PHP_EOL . 'Starting to move....' . PHP_EOL;
        echo '====================' . PHP_EOL;
        // Move all the files
        $count = 0;
        $bits_moved = 0;
        $fails = [];
        foreach ($file_moving_list as $file_moving) {
            // Destiny dir exists? Create it
            if (!is_dir(dirname($file_moving['absolute_destiny']))) {
                mkdir(dirname($file_moving['absolute_destiny']), 0755, true);
            }

            echo $file_moving['file'] . ' -> ' . $file_moving['absolute_destiny'] . PHP_EOL;
            $bits_moved += $file_moving['filesize'];
            $percentage = round($bits_moved / $total_bits * 100);

            if (file_exists($file_moving['absolute_destiny'])) {
                CommandLine::printRed('File already exists in the destiny.' . PHP_EOL);
                $was_successful = false;
            } else {
                $bar_length = self::printProgression($percentage);
                $was_successful = copy($file_moving['file'], $file_moving['absolute_destiny']);
                sleep(3);
                self::deleteProgression($bar_length);
            }

            if ($was_successful) {
                $count++;
            } else {
                CommandLine::printRed('Not moved.' . PHP_EOL);
                $fails[] = $file_moving;
            }
        }

        echo PHP_EOL . $count . ' of ' . count($file_moving_list) . ' files copied.' . PHP_EOL;
        if (!$fails) {
            CommandLine::printGreen('SCRIPT ENDED SUCCESSFULLY' . PHP_EOL);
        } else {
            CommandLine::printRed('SCRIPT ENDED WITH SOME COPY FAILS' . PHP_EOL);
            CommandLine::printList($fails);
        }
    }

    protected static function printProgression(int $percentage)
    {
        $percentage29 = round($percentage / 100 * 29);

        $drawing = '|===================>          | 72%';

        $bar = '|' . str_repeat('=', $percentage29) . '>';
        $drawing = str_pad($bar, 30, ' ', STR_PAD_RIGHT) . '| ' . $percentage . '%';
        CommandLine::printGreen($drawing);

        return strlen($drawing);
    }

    protected static function deleteProgression(int $bar_length = 0)
    {
        echo "\033[" . $bar_length . "D";      // Move 5 characters backward
    }
}
