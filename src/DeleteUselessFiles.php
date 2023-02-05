<?php

namespace RafaMalaga86\FootageOrganiser;

use Exception;

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
            CommandLine::printRed('The camera argument is missing.' . PHP_EOL);
            exit(1);
        }

        $cameras_and_paths = array_change_key_case(validCamerasAndPaths());

        if (!isset($cameras_and_paths[strtolower($camera)])) {
            CommandLine::printRed('The camera is not in the config file.' . PHP_EOL);
            exit(1);
        }

        $result = shell_exec(self::COMMAND_FIND);

        if ($result === false) {
            CommandLine::printRed('The pipe cannot be established.' . PHP_EOL);
            exit(1);
        }

        if ($result === null) {
            CommandLine::printRed('No useless file found.' . PHP_EOL);
            exit(1);
        }
        CommandLine::printGreen($result);
        CommandLine::confirmOrAbort();

        shell_exec(self::COMMAND_FIND_AND_DELETE);

        CommandLine::printGreen('Files deleted successfully!' . PHP_EOL);
    }
}
