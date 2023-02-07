<?php

require 'config.php';
require 'src/CommandLine.php';
require 'src/DeleteUselessFiles.php';
require 'src/Exit1Exception.php';

use FootageOrganiser\DeleteUselessFiles;
use FootageOrganiser\Exit1Exception;
use FootageOrganiser\CommandLine;

try {
    DeleteUselessFiles::run($argv);
} catch (Exit1Exception $exception) {
    CommandLine::printRed($exception->getMessage() . PHP_EOL);
    exit(1);
}
exit(0);
