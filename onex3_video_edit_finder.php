<?php

require 'vendor/autoload.php';

use FootageOrganiser\OneX3VideoEditFinder;
use FootageOrganiser\Exit1Exception;
use FootageOrganiser\CommandLine;

try {
    OneX3VideoEditFinder::run($argv);
} catch (Exit1Exception $exception) {
    CommandLine::printRed($exception->getMessage() . PHP_EOL);
    exit(1);
}
exit(0);
