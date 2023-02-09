<?php

namespace FootageOrganiser;

class DeleteUselessFiles
{
    protected static $extensionsNotFound = [];

    protected const COMMAND_FIND = 'find %s \( -name "*.LRV" -o -name "*.THM" -o -name "*LRV_*.mp4" \) -print -type f %s | sort';

    public static function run(array $argv): void
    {
        echo 'DELETE USELESS FILES' . PHP_EOL;
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

        $command = self::getFindCommand($dir);
        $result = shell_exec($command);

        if ($result === false) {
            throw new Exit1Exception('The pipe cannot be established.');
        }

        if ($result === null) {
            CommandLine::printGreen('No useless files found.' . PHP_EOL);
            return;
        }

        CommandLine::printGreen($result);
        CommandLine::confirmOrAbort();

        $command = self::getFindCommand($dir, delete:true);
        shell_exec($command);

        CommandLine::printGreen('Files deleted successfully!' . PHP_EOL);
    }

    protected static function getFindCommand($path, $delete = false): string
    {
        return sprintf(self::COMMAND_FIND, $path, $delete ? '-delete' : '');
    }
}
