<?php

namespace FootageOrganiser;

class InvalidFootageFinder
{
    public static function run (array $argv)
    {
        echo 'INVALID FOOTAGE FINDER' . PHP_EOL;
        echo '======================' . PHP_EOL;

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
        self::listSmallSize($file_list);
    }

    protected static function listSmallSize($file_list): void
    {
        $file_list_size = [];
        foreach ($file_list as $file) {
            if (!in_array($file, exceptions()) && !is_link($file)) {
                $file_list_size[] = [
                    'file' => $file,
                    'size' => filesize($file),
                ];
            }
        }

        $list_filtered = array_filter($file_list_size, function ($item) {
            $extension = FileManagement::getFileExtension($item['file']);
            $min_size = min_sizes()[$extension] ?? min_sizes()['default'];
            if ($item['size'] < 1024 * 1024 * $min_size) {// 1 MB
                return true;
            }
        });

        if (!$list_filtered) {
            CommandLine::printGreen('NO POTENTIAL INVALID FILES!' . PHP_EOL);
            return;
        }

        foreach ($list_filtered as $item) {
            echo $item['file'] . '  ';
            CommandLine::printGreen(number_format($item['size'] / 1024 / 1024, 2));
            echo ' MB' . PHP_EOL;
        }

        echo PHP_EOL;
        CommandLine::printGreen('SCRIPT ENDED SUCCESSFULLY' . PHP_EOL);
    }
}
