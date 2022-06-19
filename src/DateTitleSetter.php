<?php

namespace RafaMalaga86\FootageOrganiser;

use Exception;

class DateTitleSetterScript
{
    public static function run(array $file_list): void
    {
        foreach ($file_list as $file) {
            try {
                list($creation_date, $data_source_date) = FileManagement::getFileCreationDate($file);
                $creation_time = FileManagement::getFileCreationTime($file);
            } catch (Exception $e) {
                CommandLine::printRed($e->getMessage() . PHP_EOL);
                exit(1);
            }

            if ($data_source === 'meta') {
                $filename = basename($file);
                CommandLine::printGreen('Renaming ' . $filename);
                $was_renamed = rename($file, $creation_date . '_' . $filename);

                if (!$was_renamed) {
                    CommandLine::printRed('File could not be renamed.');
                    exit(1);
                }
            }
        }

        var_dump($creation_date, $data_source); die('constante');
    }
}
