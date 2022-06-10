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

function exceptions()
{
    return [
        './2021-12-01/Mobile/VID_20211201_135934.mp4',
        './2022-04-13/Mobile/VID_20220413_220300.mp4',
        './2022-04-18/Mobile/VID_20220418_195748.mp4',
    ];
}

function organiserScriptIgnores()
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
        '/Insta360GO2/DCIM/Camera01/' => '/Insta360GO2/DCIM/Go2/'
    ];
}