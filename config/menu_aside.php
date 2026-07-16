<?php
// Aside menu
return [

    'items' => [
        [
            'title' => 'Dashboard',
            'root' => true,
            'icon' => 'media/svg/icons/Design/Layers.svg',
            'page' => '/dashboard',
            'new-tab' => false,
            'roles' => ['superadmin', 'musician', 'composer', 'editorial'],
        ],
        [
            'title' => 'Users',
            'icon' => 'media/svg/icons/Communication/Group.svg',
            'page' => '/users',
            'bullet' => 'line',
            'root' => true,
            'roles' => ['superadmin'],
        ],
        [
            'title' => 'Composers',
            'icon' => 'media/svg/icons/Communication/Chat-smile.svg',
            'page' => '/composers',
            'bullet' => 'line',
            'root' => true,
            'roles' => ['superadmin'],
        ],
        [
            'title' => 'Composer Request',
            'icon' => 'media/svg/icons/Communication/Thumbtack.svg',
            'page' => '/composer_request',
            'bullet' => 'line',
            'root' => true,
            'roles' => ['superadmin'],
        ],
        [
            'title' => 'Subscription Plan',
            'icon' => 'media/svg/icons/Communication/Clipboard-list.svg',
            'page' => '/subscription-plan',
            'bullet' => 'line',
            'root' => true,
            'roles' => ['superadmin'],
        ],
        [
            'title' => 'Subscribed User',
            'icon' => 'media/svg/icons/Communication/Thumbtack.svg',
            'page' => '/subscribed-user',
            'bullet' => 'line',
            'root' => true,
            'roles' => ['superadmin'],
        ],
        [
            'title' => 'Instrument',
            'icon' => 'media/svg/icons/Devices/Midi.svg',
            'page' => '/instruments',
            'bullet' => 'line',
            'root' => true,
            'roles' => ['superadmin'],
        ],
        [
            'title' => 'StyleMusic',
            'icon' => 'media/svg/icons/Devices/Headphones.svg',
            'page' => '/style-music',
            'bullet' => 'line',
            'root' => true,
            'roles' => ['superadmin'],
        ],
        [
            'title' => 'FamilyInstrument',
            'icon' => 'media/svg/icons/Devices/Homepod.svg',
            'page' => '/family-instruments',
            'bullet' => 'line',
            'root' => true,
            'roles' => ['superadmin'],
        ],
        [
            'title' => 'Music Score',
            'icon' => 'media/svg/icons/Communication/Thumbtack.svg',
            'page' => '/music-score',
            'bullet' => 'line',
            'root' => true,
            'roles' => ['superadmin'],
        ],
        [
            'title' => 'Ensembles',
            'icon' => 'media/svg/icons/Communication/Thumbtack.svg',
            'page' => '/ensembles',
            'bullet' => 'line',
            'root' => true,
            'roles' => ['superadmin'],
        ],
        
    ]

];
