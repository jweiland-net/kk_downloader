<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Simple download-system with counter and categories',
    'description' => 'Download system with counter, simple category management, sorting criteria and page browsing in the LIST-view. Configuration via flexforms and HTML template. (example: http://kupix.de/downloadlist.html)',
    'category' => 'plugin',
    'version' => '5.2.3',
    'state' => 'stable',
    'uploadfolder' => true,
    'createDirs' => '',
    'clearcacheonload' => false,
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.14-9.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
