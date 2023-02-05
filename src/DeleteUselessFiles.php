<?php

namespace RafaMalaga86\FootageOrganiser;

class DeleteUselessFiles
{
    protected static $extensionsNotFound = [];

    protected const COMMAND_FIND = 'find /Volumes/GoPro11/DCIM \( -name "*.LRV" -o -name "*.THM" \) -print -type f | sort';

    protected const COMMAND_FIND_AND_DELETE = 'find /Volumes/GoPro11/DCIM \( -name "*.LRV" -o -name "*.THM" \) -print -type f -delete | sort';

    public static function run(array $argv): void
    {
        echo 'DELETE USELESS FILES' . PHP_EOL;
        echo '======================' . PHP_EOL;

        // Remove script name from argument list
        unset($argv[0]);
        $argv = array_values($argv);

        $camera = $argv[0] ?? null;

        if (!$camera) {
            throw new Exit1Exception('The camera argument is missing.');
        }

        $cameras_and_paths = array_change_key_case(validCamerasAndPaths());

        if (!isset($cameras_and_paths[strtolower($camera)])) {
            throw new Exit1Exception('The camera is not in the config file.');
        }

        $result = shell_exec(self::COMMAND_FIND);

        if ($result === false) {
            throw new Exit1Exception('The pipe cannot be established.');
        }

        if ($result === null) {
            throw new Exit1Exception('No useless file found.');
        }
        CommandLine::printGreen($result);
        CommandLine::confirmOrAbort();

        shell_exec(self::COMMAND_FIND_AND_DELETE);

        CommandLine::printGreen('Files deleted successfully!' . PHP_EOL);
    }
}
