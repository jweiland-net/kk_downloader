<?php
return [
    \JWeiland\KkDownloader\Domain\Model\Download::class => [
        'tableName' => 'tx_kkdownloader_images',
        'properties' => [
            'title' => [
                'fieldName' => 'name'
            ],
            'files' => [
                'fieldName' => 'image'
            ],
            'preview' => [
                'fieldName' => 'imagepreview'
            ],
            'longDescription' => [
                'fieldName' => 'longdescription'
            ],
            'filesDescription' => [
                'fieldName' => 'downloaddescription'
            ],
            'categories' => [
                'fieldName' => 'cat'
            ],
        ],
    ],
    \JWeiland\KkDownloader\Domain\Model\Category::class => [
        'tableName' => 'tx_kkdownloader_cat',
        'properties' => [
            'title' => [
                'fieldName' => 'cat'
            ],
        ],
    ],
];
