<?php

namespace FootageOrganiser;

use Carbon\Carbon;
use TypeError;

class FootageWizard
{
    public static function run(array $argv): void
    {
        $source = $argv[1] ?? null;
        $destiny = $argv[2] ?? null;
        $carbon_modifier = $argv[3] ?? null;

        if (!$source) {
            throw new Exit1Exception('The directory argument is missing.');
        }
        if (!$destiny) {
            throw new Exit1Exception('The directory argument is missing.');
        }

        list($source, $destiny) = self::getPathsFromAlias($source, $destiny);
        $source = realpath($source);
        if (!is_dir($source)) {
            throw new Exit1Exception('Could not locate the source directory argument.');
        }

        $destiny = realpath($destiny);
        if (!is_dir($destiny)) {
            throw new Exit1Exception('Could not locate the destiny directory argument.');
        }

        if ($carbon_modifier) {
            try {
                $test = (new Carbon())->add($carbon_modifier);
            } catch(TypeError $error) {
                throw new Exit1Exception($carbon_modifier . ': There is something wrong with the carbon modifier you entered.');
            }
        }

        $argv = ['', $source, $destiny];
        DeleteUselessFiles::run($argv);
        echo PHP_EOL;

        DateTitleSetter::run($argv);
        echo PHP_EOL;

        if ($carbon_modifier) {
            DateTitleAdder::run([$argv[0], $argv[1], $carbon_modifier]);
            echo PHP_EOL;
        }

        FootageOrganiser::run($argv);
        echo PHP_EOL;

        InvalidFootageFinder::run($argv);
        echo PHP_EOL;

        CommandLine::printGreen(PHP_EOL . 'Finished FOOTAGE WIZZARD SUCCESSFULLY.' . PHP_EOL);
    }

    protected static function getPathsFromAlias(string $original_source, string $original_destiny): array
    {
        $source  = strtolower($original_source);
        $destiny = strtolower($original_destiny);

        $source_alias_paths = array_change_key_case(sourceAliasPaths(), CASE_LOWER);
        $destiny_alias_paths = array_change_key_case(destinyAliasPaths(), CASE_LOWER);

        $source  = $source_alias_paths[$source] ?? $original_source;
        $destiny = $destiny_alias_paths[$destiny] ?? $original_destiny;

        return [$source, $destiny];
    }
}
