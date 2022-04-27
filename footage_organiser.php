<?php

require 'src/CommandLine.php';
require 'src/FileManagement.php';
require 'src/OrganiserScript.php';
require 'vendor/autoload.php';

use RafaMalaga86\FootageOrganiser\OrganiserScript;

OrganiserScript::run($argv);
