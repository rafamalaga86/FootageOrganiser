<?php

require 'config.php';
require 'src/CommandLine.php';
require 'src/DeleteUselessFiles.php';
require 'src/FileManagement.php';

use RafaMalaga86\FootageOrganiser\DeleteUselessFiles;

DeleteUselessFiles::run($argv);
exit(0);
