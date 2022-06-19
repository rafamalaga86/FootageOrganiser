<?php

require 'src/CommandLine.php';
require 'src/FileManagement.php';
require 'src/DateTitleSetter.php';
require 'config.php';

use RafaMalaga86\FootageOrganiser\DateTitleSetterScript;
use RafaMalaga86\FootageOrganiser\FileManagement;
use RafaMalaga86\FootageOrganiser\CommandLine;



echo 'DATE TITLE SETTER' . PHP_EOL;
echo '======================' . PHP_EOL;

// Remove script name from argument list
unset($argv[0]);
$argv = array_values($argv);

// // For option --test
// $is_test = false;
// $option_position = array_search('--test', $argv);
// if ($option_position) {
//     $is_test = true;

//     unset($argv[$option_position]);
//     $argv = array_values($argv);
// }

$dir = $argv[0] ?? null;

if (!$dir) {
    CommandLine::printRed('The directory argument is missing.' . PHP_EOL);
    exit(1);
}

$dir = realpath($dir);

if (!is_dir($dir)) {
    CommandLine::printRed('Could not locate the directory argument.' . PHP_EOL);
    exit(1);
}

$could_change_dir = chdir($dir);

if (!$could_change_dir) {
    CommandLine::printRed('Could not change dir.' . PHP_EOL);
}

$file_list = FileManagement::scandirTree('.');

DateTitleSetterScript::run($file_list);

exit(0);
