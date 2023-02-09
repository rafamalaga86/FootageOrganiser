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

    /**
     * Get the extension of the file
     * @var $filepath file(path)
     */
    public static function getFileExtension(string $filepath): string
    {
        return strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    }

    /**
     * This method is used for when you don't mind where the DATE is coming from
     * @var $filepath file path
     * @var $return_format the fortmat in which the creation DATE will be returned
     */
    public static function getFileCreationDate(string $filepath, $return_format = 'Y-m-d'): array
    {
        $filename = basename($filepath);
        $result = [self::getDateFromTitle($filename, $return_format), 'title'];

        if (!$result[0]) {
            $result = [self::getFileMTimeDate($filepath, $return_format), 'meta'];
        }

        if (!$result[0]) {
            throw new Exception('Could not find the creation date of file ' . $filepath);
        }

        return $result;
    }

    /**
     * This method is used for when you don't mind where the TIME is coming from
     * @var $filepath file path
     * @var $return_format the fortmat in which the creation TIME will be returned
     */
    public static function getFileCreationTime(string $filepath, $return_format = 'H:i:s'): array
    {
        $filename = basename($filepath);
        // Try to get it from title
        $result = [self::getTimeFromTitle($filename, $return_format), 'title'];

        if (!$result[0]) { // Get it from the metadata
            $result = [self::getFileMTimeTime($filepath, $return_format), 'meta'];
        }

        if (!$result) {
            throw new Exception('Could not find the creation time of file ' . $filepath);
        }

        return $result;
    }

    /**
     * Check if the filename has at least ONE date in the title
     * @var $filename
     */
    public static function hasAtLeastOneDateInTitle(string $filename): bool
    {
        try {
            $title = self::getDateFromTitle($filename);
        } catch (MultipleDatesException $exception) {
            return true; // It has more than one date in the title
        }

        return (bool) $title;
    }

    /**
     * Check if the filename has at least ONE time in the title
     * @var $filename
     */
    public static function hasAtLeastOneTimeInTitle(string $filename): bool
    {
        try {
            $title = self::getTimeFromTitle($filename, 'H:i:s');
        } catch (MultipleDatesException $exception) {
            return true; // It has more than one time in the title
        }

        return (bool) $title;
    }

    /**
     * Check if the filename has EXACTLY ONE date in the title
     * @var $filename
     */
    public static function hasExactlyOneDateInTitle(string $filename): bool
    {
        try {
            $title = self::getDateFromTitle($filename);
        } catch (MultipleDatesException $exception) {
            return false; // It has more than one date in the title
        }

        return (bool) $title;
    }

    /**
     * Check if the filename has EXACTLY ONE date with the format Y-m-d
     * @var $filename
     */
    public static function hasExactlyOneTitleDashedIsoDate(string $filename): bool
    {
        try {
            $title = self::getDashedIsoDateInTitle($filename);
        } catch (MultipleDatesException $exception) {
            return false; // It has more than one date in the title
        }

        return (bool) $title;
    }

    /**
     * Check if the filename has EXACTLY ONE time with the format H;i;s
     * @var $filename
     */
    public static function hasExactlyOneTimeWithSemicolonInTitle(string $filename): bool
    {
        try {
            $title = self::getTimeWithSemicolonInTitle($filename);
        } catch (MultipleDatesException $exception) {
            return false; // It has more than one date in the title
        }

        return (bool) $title;
    }

    /**
     * Find a date in the format 'Y-m-d' in the filename
     * @var $filename
     * @var $return_format
     */
    public static function getDashedIsoDateInTitle(string $filename, string $return_format = null): ?string
    {
        $regexp = '/(20\d{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])/';

        return self::getDateFromTitleWithRegExp($regexp, $filename, $return_format);
    }

    /**
     * Find a date in the format 'H;i;s' in the filename
     * @var $filename
     * @var $return_format
     */
    public static function getTimeWithSemicolonInTitle(string $filename, string $return_format = null): ?string
    {
        $regexp = '/[^\d]([0-1][0-9]|2[0-3]);[0-5][0-9];[0-5][0-9][^\d]/';

        return self::getTimeFromTitleWithRegExp($regexp, $filename, $return_format);
    }

    /**
     * Check if the filename has EXACTLY ONE time in the title
     * @var $filename
     */
    public static function hasExactlyOneTimeInTitle(string $filename): bool
    {
        try {
            $title = self::getTimeFromTitle($filename);
        } catch (MultipleDatesException $exception) {
            return false; // It has more than one date in the title
        }

        return (bool) $title;
    }

    /**
     * Get a date from title in all formats, at the moment Ymd and Y-m-d
     * @var $filename
     * @var $return_format
     */
    public static function getDateFromTitle(string $filename, string $return_format = null): ?string
    {
        $regexp = '/(20\d{2})-?(0[1-9]|1[0-2])-?(0[1-9]|[12][0-9]|3[01])/';

        return self::getDateFromTitleWithRegExp($regexp, $filename, $return_format);
    }

    /**
     * Get a date from title in all formats depending on the regular expression passed
     * @var $regexp
     * @var $filename
     * @var $return_format
     */
    protected static function getDateFromTitleWithRegExp(string $regexp, string $filename, string $return_format = null): ?string
    {
        $matches = [];
        preg_match_all($regexp, $filename, $matches);

        if (!$matches[0]) {
            return null;
        }
        if (count($matches[0]) > 1) {
            throw new MultipleDatesException('The file ' . $filename . ' has two ore more valid dates in its title: ' . PHP_EOL . implode(PHP_EOL, $matches[0]) . PHP_EOL);
        }

        $datetime = DateTime::createFromFormat('Ymd', $matches[0][0]);
        if (!$datetime) { // Previous failed, try with dashes
            $datetime = DateTime::createFromFormat('Y-m-d', $matches[0][0]);
        }
        if (!$datetime) { // Previous failed, try with dashes
            throw new Exception('Couln\'t get date from the title. ' . PHP_EOL);
        }

        return $return_format ? $datetime->format($return_format) : $matches[0][0];
    }

    /**
     * Get a time from title in all formats, at the moment His and H;i;s
     * @var $filename
     * @var $return_format
     */
    public static function getTimeFromTitle(string $filename, string $return_format = null): ?string
    {
        $search_regexp = '/[^\dXH]([0-1][0-9]|2[0-3]);?[0-5][0-9];?[0-5][0-9][^\d]/';

        return self::getTimeFromTitleWithRegExp($search_regexp, $filename, $return_format);
    }

    /**
     * Get a time from title in all formats depending on the regular expression passed
     * @var $search_regexp
     * @var $exclude_regexp
     * @var $filename
     * @var $return_format
     */
    protected static function getTimeFromTitleWithRegExp(
        string $search_regexp,
        string $filename,
        string $return_format = null,
    ): ?string {
        $matches = [];
        preg_match_all($search_regexp, $filename, $matches);

        if (!$matches[0]) {
            return null;
        }
        if (count($matches[0]) > 1) {
            $message = 'The file ' . $filename . ' has two ore more valid times in its title: ';
            $message .= PHP_EOL . implode(PHP_EOL, $matches[0]) . PHP_EOL;
            throw new MultipleDatesException($message);
        }

        $time = substr($matches[0][0], 1, -1);
        // Remove first and last characters of the string, they are not the time
        $datetime = DateTime::createFromFormat('His', $time);
        if (!$datetime) { // Previous failed, try with dashes
            $datetime = DateTime::createFromFormat('H;i;s', $time);
        }
        if (!$datetime) { // Previous failed, try with dashes
            throw new Exception('Couln\'t get time from the title. ' . PHP_EOL);
        }

        return $return_format ? $datetime->format($return_format) : $time;
    }

    /**
     * Get the BTime date from the file
     * @var $filepath
     * @var $return_format
     */
    public static function getFileBTimeDate(string $filepath, string $return_format): ?string
    {
        return self::getBTime($filepath, $return_format);
    }

    /**
     * Get the CTime date from the file
     * @var $filepath
     * @var $return_format
     */
    public static function getFileCTimeDate(string $filepath, string $return_format): ?string
    {
        $ctime = filectime($filepath);
        return date($return_format, $ctime);
    }

    /**
     * Get the MTime date from the file
     * @var $filepath
     * @var $return_format
     */
    public static function getFileMTimeDate(string $filepath, string $return_format): ?string
    {
        $mtime = filemtime($filepath);
        return date($return_format, $mtime);
    }

    /**
     * Get the BTime time from the file
     * @var $filepath
     * @var $return_format
     */
    public static function getFileBTimeTime(string $filepath, string $return_format): ?string
    {
        return self::getBTime($filepath, $return_format);
    }

    /**
     * Get the BTime from the file in any format desired
     * @var $filepath
     * @var $return_format
     */
    protected static function getBTime(string $filepath, string $return_format)
    {
        $handle = popen('stat -f %B ' . escapeshellarg($filepath), 'r');
        if (!$handle) {
            return null;
        }

        $btime = trim(fread($handle, 100));
        $date_string = date($return_format, $btime);
        pclose($handle);

        return $date_string;
    }

    /**
     * Get the CTime time from the file
     * @var $filepath
     * @var $return_format
     */
    public static function getFileCTimeTime(string $filepath, string $return_format): ?string
    {
        $ctime = filectime($filepath);
        return date($return_format, $ctime);
    }

    /**
     * Get the MTime time from the file
     * @var $filepath
     * @var $return_format
     */
    public static function getFileMTimeTime(string $filepath, string $return_format): ?string
    {
        $mtime = filemtime($filepath);
        return date($return_format, $mtime);
    }

    /**
     * Scandir Filtered
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

    /**
     * Scandir Tree
     * @var $dir directory to scan
     */
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

    /**
     * Trim First Dot
     * @var $string string to be trimmmed
     */
    public static function trimFirstDot(string $string): string
    {
        if ($string[0] === '.' && $string[1] === '/') {
            return substr($string, 2);
        }

        return $string;
    }
}
