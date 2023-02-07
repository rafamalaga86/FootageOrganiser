<?php

require 'vendor/autoload.php';

use FootageOrganiser\InvalidFootageFinder;
use FootageOrganiser\Exit1Exception;
use FootageOrganiser\CommandLine;

try {
    InvalidFootageFinder::run($argv);
} catch (Exit1Exception $exception) {
    CommandLine::printRed($exception->getMessage() . PHP_EOL);
    exit(1);
}
exit(0);
