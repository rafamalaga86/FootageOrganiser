<?php

require 'config.php';
require 'src/CommandLine.php';
require 'src/DateTitleSetter.php';
require 'src/Exit1Exception.php';
require 'src/FileManagement.php';
require 'src/MultipleDatesException.php';


use FootageOrganiser\DateTitleSetter;
use FootageOrganiser\Exit1Exception;
use FootageOrganiser\CommandLine;

try {
    DateTitleSetter::run($argv);
} catch (Exit1Exception $exception) {
    CommandLine::printRed($exception->getMessage() . PHP_EOL);
    exit(1);
}
exit(0);
