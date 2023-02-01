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
        $replacements = self::getReplacements($file_list, $organise_dir);
        self::organise($file_list, $main_dir, $replacements);
        exit(0);
    }

    protected static function getReplacements(array $file_list, string $organise_dir): array
    {
        $replacements = [];
        foreach ($file_list as $file) {
            foreach (routeReplacement() as $regExp => $replacement) {
                $it_matches = preg_match($regExp, realpath($file));
                if ($it_matches && !in_array($replacement, $replacements)) {
                    $replacements[] = $replacement;
                }
            }
        }

        return $replacements;
    }

    protected static function organise($file_list, $dir, $replacements = null): void
    {
        $total_bits = 0;
        $camera = null;
        $file_moving_list = [];
        $dir_existing = [];
        $dir_created = [];

        foreach ($file_list as $file) {
            // Is this file meant to be ignored?
            $file_exploded = explode('/', $file);
            $file_name = end($file_exploded);
            if (in_array($file_name, organiserScriptIgnores())) {
                continue;
            }

            try {
                list($creation_date, $data_source) = FileManagement::getFileCreationDate($file);
            } catch (Exception $e) {
                CommandLine::printRed($e->getMessage() . PHP_EOL);
                exit(1);
            }

            $file_destiny = $file;
            if ($replacements) {
                foreach ($replacements as $replace) {
                    $search = $replace[0];
                    $replacement = $replace[1];
                    $file_destiny = str_replace($search, $replacement, $file);
                    $camera = $replacement;
                }
            }

            $total_bits += filesize($file);



            try {
                $dir_found = self::findDir($dir, $creation_date);
            } catch (Exception $e) {
                CommandLine::printRed($e->getMessage() . PHP_EOL);
                exit(1);
            }
            // $dir_exists = file_exists($dir . '/' . $file['creation_date']);

            $date_dir = $dir_found ? $dir_found : $creation_date;

            // Count them to show final results
            if (!$dir_found && !in_array($date_dir, $dir_created)) {
                $dir_created[] = $date_dir;
            } elseif ($dir_found && !in_array($date_dir, $dir_existing)) {
                $dir_existing[] = $date_dir;
            }

            $file_moving_list[] = [
                'file' => $file,
                'filesize' => filesize($file),
                'creation_date' => $creation_date,
                'data_source' => $data_source,
                'absolute_destiny' => $dir . '/' . $date_dir . '/' . FileManagement::trimFirstDot($file_destiny),
            ];
        }

        // Order by creation time
        usort($file_moving_list, function ($a, $b) {
            return $a['creation_date'] > $b['creation_date'];
        });

        $abort = false;
        // Print the array of files and dates and check it won't override
        foreach ($file_moving_list as $file_moving) {
            echo $file_moving['file'] . ' ';

            switch ($file_moving['data_source']) {
                case 'title':
                    CommandLine::printGreen($file_moving['creation_date']);
                    break;
                case 'meta':
                    CommandLine::printYellow($file_moving['creation_date']);
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

        CommandLine::confirmOrAbort();
        // If arrives here, is confirmed

        echo PHP_EOL . 'Starting to copy....' . PHP_EOL;
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
                $bar_length = self::printProgression($percentage, $camera);
                $was_successful = copy($file_moving['file'], $file_moving['absolute_destiny']);
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

    protected static function printProgression(int $percentage, string $camera = null)
    {
        $percentage29 = round($percentage / 100 * 29);
        // '|===================>          | 72%';

        $bar = '|' . str_repeat('#', $percentage29) . '>';
        $draw_percentage =  str_pad($percentage . '%', 4, ' ', STR_PAD_LEFT);
        $drawing = $draw_percentage . ' ' . str_pad($bar, 30, ' ', STR_PAD_RIGHT) . '| ' . $camera . ' ';
        CommandLine::printGreen($drawing);

        return strlen($drawing);
    }

    protected static function deleteProgression(int $bar_length = 0)
    {
        echo "\033[" . $bar_length . "D";      // Move 5 characters backward
    }

    protected static function findDir(string $dir_name, string $creation_date): ?string
    {
        $founds = [];
        $file_list = scandir($dir_name);

        foreach ($file_list as $file) {
            // Check if the dir contains the date
            if (strpos($file, $creation_date) !== false && is_dir($dir_name . '/' . $file)) {
                $founds[] = $file;
            }
        }

        if (count($founds) > 1) {
            throw new Exception('Error: More than one directory matched the file creation date: ' . implode(', ', $founds));
        }

        return $founds ? $founds[0] : null;
    }
}
