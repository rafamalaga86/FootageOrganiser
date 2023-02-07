<?php

// use LOWERCASE and MEGABYTES
function min_sizes()
{
    return [
        'mp4' => 5,
        'jpg' => 1,
        'wav' => 2,
        'insp' => 2,
        'default' => 5,
    ];
}

function exceptions(): array
{
    return [
        './2021-12-01/Mobile/VID_20211201_135934.mp4',
        './2022-04-13/Mobile/VID_20220413_220300.mp4',
        './2022-04-18/Mobile/VID_20220418_195748.mp4',
    ];
}

function fileIgnores(): array
{
    return [
        'fileinfo_list.list',
        'leinfo.sav',
        'Thumb',
    ];
}

function routeReplacement()
{
    return  [
        '/^.*\/OneX2\/DCIM\/Camera01\/.*$/' => ['Camera01', 'OneX2'],
        '/^.*\/Insta360GO2\/DCIM\/Camera01\/.*$/' => ['Camera01', 'Go2-NotRendered'],
        '/^.*\/GoPro11\/DCIM\/100GOPRO\/.*$/' => ['100GOPRO', 'GoPro11'],
        '/^.*\/GoPro9\/DCIM\/100GOPRO\/.*$/' => ['100GOPRO', 'GoPro9'],
    ];
}

function prefixesByExtension()
{
    return [
        'mp4' => 'VID',
        'mov' => 'VID',
        'lrv' => 'VID',
        'jpg' => 'IMG',
        'jpeg' => 'IMG',
    ];
}

function validCamerasAndPaths()
{
    return [
        'GoPro9' => '/Volumes/GoPro9/DCIM/',
        'GoPro11' => '/Volumes/GoPro11/DCIM/',
    ];
}

function sourceAliasPaths()
{
    return [
        'GoPro9' => '/Volumes/GoPro9/DCIM',
        'GoPro11' => '/Volumes/GoPro11/DCIM',
        'OneX2' => '/Volumes/OneX2/DCIM',
        'GO2' => '/Volumes/Insta360GO2/DCIM',
    ];
}

function destinyAliasPaths()
{
    return [
        'new' => '/Volumes/Extreme SSD/Video Workspace/Footage/new',
        'USA' => '/Volumes/Extreme SSD/Video Workspace/Footage/USA',
    ];
}
