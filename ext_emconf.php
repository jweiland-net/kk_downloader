<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Simple download-system with counter and categories',
    'description' => 'Download system with counter, simple category management, sorting criteria and page browsing in the LIST-view. Configuration via flexforms and HTML template. (example: http://kupix.de/downloadlist.html)',
    'category' => 'plugin',
    'version' => '6.0.0',
    'state' => 'stable',
    'uploadfolder' => true,
    'createDirs' => '',
    'clearcacheonload' => false,
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.17-10.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
