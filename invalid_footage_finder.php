<?php

require 'src/CommandLine.php';
require 'src/FileManagement.php';
require 'src/InvalidFootageFinder.php';
require 'config.php';
require 'vendor/autoload.php';

use RafaMalaga86\FootageOrganiser\InvalidFootageFinder;


InvalidFootageFinder::run($argv);
