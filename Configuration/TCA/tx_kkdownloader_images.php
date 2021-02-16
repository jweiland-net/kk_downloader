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
        'showRecordFieldList' => 'sys_language_uid, l18n_parent, hidden, name, downloaddescription, description, longdescription, image, imagepreview, cat, clicks'
    ],
    'types' => [
        '0' => [
            'showitem' => '--palette--;LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden,
                name, 
                --palette--;;description,
                longdescription, image, imagepreview, cat, clicks,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access'
        ]
    ],
    'palettes' => [
        'language_hidden' => ['showitem' => 'sys_language_uid, l18n_parent, hidden'],
        'description' => ['showitem' => 'downloaddescription, description'],
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
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'passthrough',
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
        'downloaddescription' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.imagedescription',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ]
        ],
        'description' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ]
        ],
        'longdescription' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.longdescription',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
                'enableRichtext' => true,
            ]
        ],
        'image' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.image',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                [
                    'minitems' => 0,
                    'maxitems' => 10,
                    'default' => 0,
                    'foreign_match_fields' => [
                        'fieldname' => 'image',
                        'tablenames' => 'tx_kkdownloader_images',
                        'table_local' => 'sys_file',
                    ],
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                    'appearance' => [
                        'showPossibleLocalizationRecords' => true,
                        'showRemovedLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ]
                        ]
                    ]
                ]
            )
        ],
        'imagepreview' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.imagepreview',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'imagepreview',
                [
                    'minitems' => 0,
                    'maxitems' => 1,
                    'default' => 0,
                    'foreign_match_fields' => [
                        'fieldname' => 'imagepreview',
                        'tablenames' => 'tx_kkdownloader_images',
                        'table_local' => 'sys_file',
                    ],
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                    'appearance' => [
                        'showPossibleLocalizationRecords' => true,
                        'showRemovedLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ]
                        ]
                    ]
                ]
            )
        ],
        'cat' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.cat',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_kkdownloader_cat',
                'foreign_table_where' => 'AND tx_kkdownloader_cat.sys_language_uid IN (-1,0) ORDER BY tx_kkdownloader_cat.cat',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 10,
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
