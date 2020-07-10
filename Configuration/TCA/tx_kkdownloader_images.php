<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'sortby' => 'sorting',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'translationSource' => 'l10n_source',
        'default_sortby' => 'ORDER BY name',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:kk_downloader/Resources/Public/Icons/tx_kkdownloader_images.svg',
        'searchFields' => 'uid,name',
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid,l18n_parent,l18n_diffsource,hidden,name,image,description,longdescription,clicks,cat'
    ],
    'types' => [
        '0' => ['showitem' => 'sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, name, image, cat, imagepreview, downloaddescription, description,longdescription;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], clicks']
    ],
    'palettes' => [
        '1' => ['showitem' => '']
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
                'default' => 0,
            ]
        ],
        'l18n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => true,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_kkdownloader_images',
                'foreign_table_where' => 'AND tx_kkdownloader_images.pid=###CURRENT_PID### AND tx_kkdownloader_images.sys_language_uid IN (-1,0)',
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => true,
                    ],
                ],
                'default' => 0,
            ]
        ],
        'l18n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => ''
            ]
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ]
        ],
        'image' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.image',
            'config' => [
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => '',
                'disallowed' => 'php',
                'max_size' => 5000000,
                'uploadfolder' => 'uploads/tx_kkdownloader',
                'show_thumbs' => 1,
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 10,
            ]
        ],
        'imagepreview' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.imagepreview',
            'config' => [
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => 'jpg,gif,png',
                'max_size' => 10000,
                'uploadfolder' => 'uploads/tx_kkdownloader',
                'show_thumbs' => 1,
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ]
        ],
        'description' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 3,
            ]
        ],
        'longdescription' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.longdescription',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'wizards' => [
                    '_PADDING' => 2,
                    'RTE' => [
                        'notNewRecords' => 1,
                        'RTEonly' => 1,
                        'type' => 'script',
                        'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon' => 'wizard_rte2.gif',
                        'module' => [
                            'name' => 'wizard_rte'
                        ]
                    ],
                ],
            ]
        ],
        'downloaddescription' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.imagedescription',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ]
        ],
        'clicks' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.clicks',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'default' => 0
            ]
        ],
        'cat' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.cat',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingleBox',
                'foreign_table' => 'tx_kkdownloader_cat',
                'foreign_table_where' => 'AND tx_kkdownloader_cat.sys_language_uid IN (-1,0) ORDER BY tx_kkdownloader_cat.cat',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 10,
            ]
        ],
        'last_downloaded' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'ip_last_download' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
    ],
];
