<?php

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
