<?php

require 'src/CommandLine.php';
require 'src/FileManagement.php';
require 'src/DateTitleSetter.php';
require 'src/MultipleDatesException.php';
require 'config.php';

use RafaMalaga86\FootageOrganiser\DateTitleSetter;

DateTitleSetter::run($argv);
