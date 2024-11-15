<?php

namespace FootageOrganiser;

class OneX3VideoEditFinder
{
    // const ONEX3_VIDEO_EDIT = '/\d{4}\d{2}\d{2}_\d{2}\d{2}\d{2}_\d{3}\.mp4$/';

    const ONEX3_VIDEO_EDIT = '/^(20\d{2})(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])_([0-1][0-9]|2[0-3])([0-5][0-9])([0-5][0-9])_(\d{3}).mp4$/';

    public static function run(array $argv)
    {
        echo 'ONEX3 VIDEO EDIT FINDER' . PHP_EOL;
        echo '=======================' . PHP_EOL;

        // Remove script name from argument list
        unset($argv[0]);
        $argv = array_values($argv);

        $source = $argv[0] ?? null;

        if (!$source) {
            throw new Exit1Exception('The source directory argument is missing.');
        }

        $source = realpath($source);
        if (!is_dir($source)) {
            throw new Exit1Exception('Could not locate the source directory argument.');
        }

        $could_change_dir = chdir($source);

        $file_list = FileManagement::scandirTree('.');

        $file_moving_list = [];
        foreach ($file_list as $file_path) {
            $file_path_array = explode('/', $file_path);
            $file_name = end($file_path_array);

            $result = preg_match(self::ONEX3_VIDEO_EDIT, $file_name);

            if ($result) {
                $file_moving_list[] = [
                    'file_path' => $file_path,
                    'filesize' => filesize($file_path),
                ];
            }
        }

        foreach ($file_moving_list as $file) {
            echo $file['file_path'];
            echo PHP_EOL;
        }
    }

}



