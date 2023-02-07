<?php

require 'config.php';
require 'src/CommandLine.php';
require 'src/Exit1Exception.php';
require 'src/FileManagement.php';
require 'src/FootageOrganiser.php';
require 'vendor/autoload.php';

use FootageOrganiser\CommandLine;
use FootageOrganiser\Exit1Exception;
use FootageOrganiser\FootageOrganiser;


try {
    FootageOrganiser::run($argv);
} catch (Exit1Exception $exception) {
    CommandLine::printRed($exception->getMessage() . PHP_EOL);
    exit(1);
}
exit(0);
