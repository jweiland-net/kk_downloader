<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Simple download-system with counter and categories',
    'description' => 'Download system with counter, simple category management, sorting criteria and page browsing in the LIST-view. Configuration via flexforms and HTML template. (example: http://kupix.de/downloadlist.html)',
    'category' => 'plugin',
    'version' => '7.0.0',
    'state' => 'stable',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.37-11.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
