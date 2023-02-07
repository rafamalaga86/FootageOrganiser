<?php

namespace FootageOrganiser;

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

    public static function getFileExtension(string $filepath): string
    {
        return strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    }

    public static function getFileCreationDate(string $file): array
    {
        $filename = basename($file);
        $result = [self::getCreationDateFromTitleYYYYMMDD($filename), 'title'];

        if (!$result[0]) {
            $result = [self::getFileMTimeDate($file), 'meta'];
        }

        if (!$result[0]) {
            throw new Exception('Could not find the creation date of file ' . $file);
        }

        return $result;
    }

    public static function getFileCreationTime(string $file, $format = 'H;i;s'): string
    {
        // Try to get it from title
        $result = self::getFileMTimeTime($file, $format);

        if (!$result) {
            throw new Exception('Could not find the creation time of file ' . $file);
        }

        return $result;
    }

    public static function hasAtLeastOneCreationDateFromTitleYYYYMMDD(string $file): bool
    {
        try {
            $title = self::getCreationDateFromTitleYYYYMMDD($file);
        } catch (MultipleDatesException $exception) {
            return true; // It has more than one date in the title
        }

        return (bool) $title;
    }

    public static function hasOneCreationDateFromTitleYYYYMMDD(string $file): bool
    {
        try {
            $title = self::getCreationDateFromTitleYYYYMMDD($file);
        } catch (MultipleDatesException $exception) {
            return false; // It has more than one date in the title
        }

        return (bool) $title;
    }

    public static function hasOneCreationTimeFromTitleHHMMSS(string $file): bool
    {
        try {
            $title = self::getCreationTimeFromTitleHHMMSS($file);
        } catch (MultipleDatesException $exception) {
            return false; // It has more than one date in the title
        }

        return (bool) $title;
    }

    public static function getCreationDateFromTitleYYYYMMDD(string $file, string $format = 'Y-m-d'): ?string
    {
        $regexp = '/(20\d{2})-?(0[1-9]|1[0-2])-?(0[1-9]|[12][0-9]|3[01])/';

        return self::getCreationDateFromTitle($regexp, $file, $format);
    }

    public static function getCreationDateFromTitle(string $regexp, string $file, string $format = 'Y-m-d'): ?string
    {
        $matches = [];
        preg_match_all($regexp, $file, $matches);

        if (!$matches[0]) {
            return null;
        }
        if (count($matches[0]) > 1) {
            throw new MultipleDatesException('The file ' . $file . ' has two ore more valid dates in its title: ' . PHP_EOL . implode(PHP_EOL, $matches[0]) . PHP_EOL);
        }

        $datetime = DateTime::createFromFormat('Ymd', $matches[0][0]);
        if (!$datetime) { // Previous failed, try with dashes
            $datetime = DateTime::createFromFormat('Y-m-d', $matches[0][0]);
        }
        if (!$datetime) { // Previous failed, try with dashes
            throw new Exception('Couln\'t get date from the title. ' . PHP_EOL);
        }

        return $format ? $datetime->format($format) : $matches[0][0];
    }

    public static function getCreationTimeFromTitleHHMMSS(string $file, string $format = 'H:i:s'): ?string
    {
        $regexp = '/[^\d]([0-1][0-9]|2[0-3]);?[0-5][0-9];?[0-5][0-9][^\d.]/';

        return self::getCreationTimeFromTitle($regexp, $file, $format);
    }

    public static function getCreationTimeFromTitle(string $regexp, string $file, string $format = 'H:i:s'): ?string
    {
        $matches = [];
        preg_match_all($regexp, $file, $matches);

        if (!$matches[0]) {
            return null;
        }
        if (count($matches[0]) > 1) {
            throw new MultipleDatesException('The file ' . $file . ' has two ore more valid times in its title: ' . PHP_EOL . implode(PHP_EOL, $matches[0]) . PHP_EOL);
        }

        // Remove first and last characters of the string, they are not the time
        $time = substr($matches[0][0], 1, -1);

        $datetime = DateTime::createFromFormat('His', $time);
        if (!$datetime) { // Previous failed, try with dashes
            $datetime = DateTime::createFromFormat('H;i;s', $time);
        }
        if (!$datetime) { // Previous failed, try with dashes
            throw new Exception('Couln\'t get time from the title. ' . PHP_EOL);
        }

        return $format ? $datetime->format($format) : $time;
    }

    public static function getFileBTimeDate(string $file, string $format = 'Y-m-d'): ?string
    {
        $handle = popen('stat -f %B ' . escapeshellarg($file), 'r');
        if (!$handle) {
            return null;
        }

        $btime = trim(fread($handle, 100));
        $date_string = date($format, $btime);
        pclose($handle);

        return $date_string;
    }

    public static function getFileCTimeDate(string $file, string $format = 'Y-m-d'): ?string
    {
        $ctime = filectime($file);
        return date($format, $ctime);
    }

    public static function getFileMTimeDate(string $file, string $format = 'Y-m-d'): ?string
    {
        $mtime = filemtime($file);
        return date($format, $mtime);
    }

    public static function getFileBTimeTime(string $file, string $format = 'H;i;s'): ?string
    {
        $handle = popen('stat -f %B ' . escapeshellarg($file), 'r');
        if (!$handle) {
            return null;
        }

        $btime = trim(fread($handle, 100));
        $time_string = date($format, $btime);
        pclose($handle);

        return $time_string;
    }

    public static function getFileCTimeTime(string $file, string $format = 'H;i;s'): ?string
    {
        $ctime = filectime($file);
        return date($format, $ctime);
    }

    public static function getFileMTimeTime(string $file, string $format = 'H;i;s'): ?string
    {
        $mtime = filemtime($file);
        return date($format, $mtime);
    }


    /**
     * @var $dir directory to scan
     * @var $ignore_hidden wheater ignore the .asdf files or not
     */
    protected static function scandirFiltered(string $dir, bool $ignore_hidden = true): array
    {
        $list = $ignore_hidden ? preg_grep('/^([^.])/', scandir($dir)) : scandir($dir);

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
