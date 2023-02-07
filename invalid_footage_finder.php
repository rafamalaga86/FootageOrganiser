<?php

require 'config.php';
require 'src/CommandLine.php';
require 'src/Exit1Exception.php';
require 'src/FileManagement.php';
require 'src/InvalidFootageFinder.php';
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
