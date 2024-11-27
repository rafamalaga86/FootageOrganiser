<?php

require 'vendor/autoload.php';

use FootageOrganiser\InvalidNameFinder;
use FootageOrganiser\Exit1Exception;
use FootageOrganiser\CommandLine;

try {
    InvalidNameFinder::run($argv);
} catch (Exit1Exception $exception) {
    CommandLine::printRed($exception->getMessage() . PHP_EOL);
    exit(1);
}
exit(0);
