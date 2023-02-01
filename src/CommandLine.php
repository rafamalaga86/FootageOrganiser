<?php

namespace RafaMalaga86\FootageOrganiser;

class CommandLine {
    static public function printYellow(string $string): void
    {
        echo "\e[0;33m" . $string . "\e[0m";
    }

    static public function printGreen(string $string): void
    {
        echo "\e[0;32m" . $string . "\e[0m";
    }

    static public function printRed(string $string): void
    {
        echo "\e[0;31m" . $string . "\e[0m";
    }

    static public function printList(array $array): void
    {
        foreach ($array as $key => $item) {
            if (!is_array($item)) {
                if (is_string($key)) {
                    echo ucfirst($key) . ': ';
                }
                echo $item . '  ';
            } elseif (is_array($item)) {
                self::printList($item);
            }
        }
        echo PHP_EOL;
    }

    static public function confirmOrAbort(): void
    {
        echo PHP_EOL;
        echo 'Are you sure you want to do this? (y/n):';
        $handle = fopen('php://stdin', 'r');
        $response = strtolower(trim(fgets($handle)));
        fclose($handle);

        if ($response != 'y' && $response != 'yes'){
            CommandLine::printRed('Aborting.' . PHP_EOL);
            exit(0);
        }
    }
}
