<?php

namespace FootageOrganiser;

use Exception;

class FootageOrganiser
{
    public static function run(array $argv)
    {
        echo 'FOOTAGE ORGANISING SCRIPT' . PHP_EOL;
        echo '-------------------------' . PHP_EOL;

        // Remove script name from argument list
        unset($argv[0]);
        $argv = array_values($argv);

        $source_dir = $argv[0] ?? null;
        $destiny_dir = $argv[1] ?? null;

        if (!$source_dir) {
            throw new Exit1Exception('The source directory argument is missing.');
        }

        if (!$destiny_dir) {
            throw new Exit1Exception('The destiny directory argument is missing.');
        }

        $destiny_dir = realpath($destiny_dir);
        $source_dir = realpath($source_dir);

        if (!is_dir($destiny_dir)) {
            throw new Exit1Exception('Could not locate the destiny directory argument.');
        }

        if (!is_dir($source_dir)) {
            throw new Exit1Exception('Could not locate the source directory argument.');
        }

        $could_change_dir = chdir($source_dir);

        if (!$could_change_dir) {
            CommandLine::printRed('Could not change dir to the source dir.' . PHP_EOL);
        }

        $file_list = FileManagement::scandirTree('.');
        $replacements = self::getReplacements($file_list, $source_dir);
        self::organise($file_list, $destiny_dir, $replacements);
    }

    protected static function getReplacements(array $file_list, string $source_dir): array
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
            $filename = end($file_exploded);
            if (in_array($filename, fileIgnores())) {
                continue;
            }

            try {
                list($creation_date, $data_source) = FileManagement::getFileCreationDate($file);
            } catch (Exception $exception) {
                throw new Exit1Exception($exception->getMessage());
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
            } catch (Exception $exception) {
                throw new Exit1Exception($exception->getMessage());
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
            return $a['creation_date'] <=> $b['creation_date'];
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
            throw new Exit1Exception('ERROR: There are some files that already exist in the destiny. Script didnt start.');
        }

        if (!$file_moving_list) {
            throw new Exit1Exception('ERROR: There are no files in the dir to organise.');
        }


        echo PHP_EOL;
        sort($dir_existing);
        sort($dir_created);

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
            if (!$dir_created) {
                CommandLine::printGreen('REMEMBER TO NAME THE NEW DIRECTORIES' . PHP_EOL);
            }
        } else {
            CommandLine::printRed('SCRIPT ENDED WITH SOME COPY FAILS' . PHP_EOL);
            CommandLine::printList($fails);
        }
    }

    protected static function printProgression(int $percentage, string $camera = null)
    {
        $bar_size = 30;
        $percentage30 = round($percentage / 100 * $bar_size);

        // The strpad with the character selected works weird. It needs a correction
        $max_bar_correction =  $bar_size * 3;

        $draw_percentage =  str_pad($percentage . '%', 4, ' ', STR_PAD_LEFT);

        $completed_bar = str_repeat('▒', $percentage30);
        $bar = str_pad($completed_bar, $max_bar_correction, '░', STR_PAD_RIGHT);
        CommandLine::printGreen($draw_percentage);
        echo ' ';
        echo $bar;
        echo ' ';
        CommandLine::printGreen($camera);

        return strlen($draw_percentage . $bar . $camera);
    }

    protected static function oldPrintProgression(int $percentage, string $camera = null)
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
