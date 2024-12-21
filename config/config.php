<?php

// use LOWERCASE and MEGABYTES
function min_sizes()
{
    return [
        'mp4' => 20,
        'jpg' => 1,
        'jpeg' => 1,
        'heic' => 1,
        'wav' => 2,
        'insp' => 2,
        'insv' => 40,
        'default' => 5,
    ];
}

function min_sizes_exceptions()
{
    return ['/Mobile/'];
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
        '/^.*\/Insta360GO2\/DCIM\/Camera01\/.*$/' => ['Camera01', 'GO2NotRendered'],
        '/^.*\/GoPro9\/DCIM\/100GOPRO\/.*$/' => ['100GOPRO', 'GoPro9'],
        '/^.*\/GoPro11\/DCIM\/100GOPRO\/.*$/' => ['100GOPRO', 'GoPro11'],
        '/^.*\/GoPro12\/DCIM\/100GOPRO\/.*$/' => ['100GOPRO', 'GoPro12'],
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
        'GoPro12' => '/Volumes/GoPro12/DCIM/',
    ];
}

function sourceAliasPaths()
{
    return [
        'GoPro9' => '/Volumes/GoPro9/DCIM',
        'GoPro11' => '/Volumes/GoPro11/DCIM',
        'GoPro12' => '/Volumes/GoPro12/DCIM',
        'OneX3' => '/Volumes/OneX3/DCIM',
        'OneX3-2' => '/Volumes/OneX3-2/DCIM',
        'OneX3Export' => '/Volumes/OneX3/export',
        'OneX3-2Export' => '/Volumes/OneX3-2/export',
        'Extra' => '/Volumes/Extra/DCIM',
        'GO2' => '/Volumes/Insta360GO2/DCIM',
        '00' => '/Volumes/Extreme SSD/00 To Organise',
    ];
}

function destinyAliasPaths()
{
    return [
        'Peru-LaCie2' => '/Volumes/LaCie2/Peru/',
        'Peru-2TB' => '/Volumes/2TB/Peru/',
        'Peru-MyPassport2' => '/Volumes/MyPassport2/Peru/',
        'Peru-SSD' => '/Volumes/Extreme SSD/Peru/',
    ];
}


function validDirNames()
{
    return [
        'GoPro12',
        'GoPro11',
        'GoPro9',
        'GO2',
        'GO2NotRendered',
        'Mobile',
        'OneX2',
        'OneX3',
        'OneX3Rendered',
        'OneX2Rendered',
        'TourCamera',
        'GoogleEarth',
    ];
}
