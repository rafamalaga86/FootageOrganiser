<?php

namespace FootageOrganiser;

class CommandLine
{
    public static function printYellow(string $string): void
    {
        echo "\e[0;33m" . $string . "\e[0m";
    }

    public static function printGreen(string $string): void
    {
        echo "\e[0;32m" . $string . "\e[0m";
    }

    public static function printRed(string $string): void
    {
        echo "\e[0;31m" . $string . "\e[0m";
    }

    public static function printList(array $array, bool $withReturns = false ): void
    {
        foreach ($array as $key => $item) {
            if (!is_array($item)) {
                if (is_string($key)) {
                    echo ucfirst($key) . ': ';
                }
                echo $item . '  ';
                if ($withReturns) {
                    echo PHP_EOL;
                }
            } elseif (is_array($item)) {
                self::printList($item);
            }
        }
        echo PHP_EOL;
    }

    public static function confirmOrAbort(): void
    {
        echo PHP_EOL;
        echo 'Are you sure you want to do this? (y/n):';
        $handle = fopen('php://stdin', 'r');
        $response = strtolower(trim(fgets($handle)));
        fclose($handle);

        if ($response != 'y' && $response != 'yes') {
            CommandLine::printRed('Aborting.' . PHP_EOL);
            exit(0);
        }
    }
}
