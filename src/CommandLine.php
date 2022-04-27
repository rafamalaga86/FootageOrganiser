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
        foreach ($array as $item) {
            if (is_string($item)) {
                echo $item . PHP_EOL;
            } elseif (is_array($item)) {
                print_list($item);
            }
        }
    }
}
