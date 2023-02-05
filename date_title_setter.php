<?php

require 'config.php';
require 'src/CommandLine.php';
require 'src/DateTitleSetter.php';
require 'src/FileManagement.php';
require 'src/MultipleDatesException.php';


use RafaMalaga86\FootageOrganiser\DateTitleSetter;
use RafaMalaga86\FootageOrganiser\Exit1Exception;
use RafaMalaga86\FootageOrganiser\CommandLine;

try {
    DateTitleSetter::run($argv);
} catch (Exit1Exception $exception) {
    CommandLine::printRed($exception->getMessage() . PHP_EOL);
    exit(1);
}
exit(0);
