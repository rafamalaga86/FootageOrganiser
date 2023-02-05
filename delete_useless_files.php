<?php

require 'config.php';
require 'src/CommandLine.php';
require 'src/DeleteUselessFiles.php';
require 'src/Exit1Exception.php';

use RafaMalaga86\FootageOrganiser\DeleteUselessFiles;
use RafaMalaga86\FootageOrganiser\Exit1Exception;
use RafaMalaga86\FootageOrganiser\CommandLine;

try {
    DeleteUselessFiles::run($argv);
} catch (Exit1Exception $exception) {
    CommandLine::printRed($exception->getMessage() . PHP_EOL);
    exit(1);
}
exit(0);
