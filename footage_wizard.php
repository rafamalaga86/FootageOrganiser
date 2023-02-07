<?php

require 'config.php';
require 'src/CommandLine.php';
require 'src/DateTitleSetter.php';
require 'src/DeleteUselessFiles.php';
require 'src/Exit1Exception.php';
require 'src/FileManagement.php';
require 'src/FootageOrganiser.php';
require 'src/FootageWizard.php';
require 'src/InvalidFootageFinder.php';
require 'src/MultipleDatesException.php';
require 'vendor/autoload.php';

use FootageOrganiser\CommandLine;
use FootageOrganiser\Exit1Exception;
use FootageOrganiser\FootageWizard;

try {
    FootageWizard::run($argv);
} catch (Exit1Exception $exception) {
    CommandLine::printRed($exception->getMessage() . PHP_EOL);
    exit(1);
}
exit(0);
