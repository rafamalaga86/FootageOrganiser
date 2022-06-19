<?php

namespace RafaMalaga86\FootageOrganiser;

use DateTime;
use Exception;

class FileManagement
{
    protected const FILTER_OUT_REGEXPS = [
        '/^\.$/',
        '/^\.\.$/',
        '/^\.DS_Store$/',
        '/^\._.*$/',
    ];

    public static function getFileCreationDate(string $file): array
    {
        $result = [self::getCreationDateFromTitleYYYYMMDD($file), 'title'];

        if (!$result[0]) {
            $result = [self::getFileCreationDateFromMeta($file), 'meta'];
        }

        if (!$result[0]) {
            throw new Exception('Could not find the creation date of file ' . $file);
        }

        return $result;
    }

    public static function getFileCreationTime(string $file): string
    {
        $result = self::getFileCreationTimeFromMeta($file);

        if (!$result) {
            throw new Exception('Could not find the creation time of file ' . $file);
        }

        return $result;
    }

    public static function getCreationDateFromTitleYYYYMMDD(string $file): ?string
    {
        $regexp = '/(20\d{2})(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])/';
        $matches = [];
        preg_match_all($regexp, $file, $matches);

        if (!$matches[0]) {
            return null;
        }
        if (count($matches[0]) > 1) {
            throw new Exception('The file ' . $file . ' has two ore more valid dates in its title' . PHP_EOL);
        }

        $result = DateTime::createFromFormat('Ymd', $matches[0][0])->format('Y-m-d');

        return $result;
    }

    public static function getFileCreationDateFromMeta(string $file): ?string
    {
        $handle = popen('stat -f %B ' . escapeshellarg($file), 'r');
        if (!$handle) {
            return null;
        }

        $btime = trim(fread($handle, 100));
        $date_string = date("Y-m-d", $btime);
        pclose($handle);

        return $date_string;
    }

    public static function getFileCreationTimeFromMeta(string $file): ?string
    {
        $handle = popen('stat -f %B ' . escapeshellarg($file), 'r');
        if (!$handle) {
            return null;
        }

        $btime = trim(fread($handle, 100));
        $time_string = date("His", $btime);
        pclose($handle);

        return $time_string;
    }


    protected static function scandirFiltered(string $dir): array
    {
        $list = scandir($dir);

        $to_filter = [];

        foreach ($list as $item) {
            foreach (self::FILTER_OUT_REGEXPS as $regexp) {
                if (preg_match($regexp, $item)) {
                    $to_filter[] = $item;
                }
            }
        }
        $result_list = array_diff($list, $to_filter);

        return $result_list;
    }


    public static function scandirTree(string $dir = null): array
    {
        $list = [];
        $list_level = self::scandirFiltered($dir ?? '.');

        if ($dir !== null) {
            foreach ($list_level as $item) {
                $list[] = $dir . '/' . $item;
            }
        }

        $result_list = [];
        foreach ($list as $item) {
            if (is_dir($item)) {
                $returned_list = self::scandirTree($item);
                $result_list = array_merge($result_list, $returned_list);
            } else {
                $result_list[] = $item;
            }
        }

        return $result_list;
    }

    public static function trimFirstDot(string $string): string
    {
        if ($string[0] === '.' && $string[1] === '/') {
            return substr($string, 2);
        }

        return $string;
    }
}
