<?php

namespace FootageOrganiser;

class InvalidNameFinder
{
    public static function run(array $argv)
    {
        echo 'INVALID NAME FINDER' . PHP_EOL;
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
        $file_list_filtered = [];
        $file_list = array_map(function($item){
            return [
                'string' => $item,
                'array' => explode('/', $item),
            ];
        }, $file_list);

        foreach ($file_list as $item) {
            $valid_camera_name = in_array($item['array'][2], validDirNames());
            if (!$valid_camera_name) {
                $dir = $item['array'][0] . '/' . $item['array'][1]. '/' . $item['array'][2];
                $file_list_filtered[$dir] = 'Camera name not valid';
            }
            $valid_date = preg_match('/\d{4}-\d{2}-\d{2}.*/', $item['array'][1]);
            if (!$valid_date) {
                $dir = $item['array'][0] . '/' . $item['array'][1];
                $file_list_filtered[$dir] = 'Date dir not valid';
            }
        }
        

        CommandLine::printList($file_list_filtered, true);
        if (!$file_list_filtered) {
            CommandLine::printGreen('No problems with naming found' . PHP_EOL);
        }
    }

}
