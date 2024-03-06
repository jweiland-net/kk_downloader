<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'sortby' => 'sorting',
        'default_sortby' => 'ORDER BY name',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'name,description,longdescription',
        'iconfile' => 'EXT:kk_downloader/Resources/Public/Icons/tx_kkdownloader_images.svg',
    ],
    'types' => [
        '0' => [
            'showitem' => '--palette--;;language, --palette--;;nameHidden, image, imagepreview, description, longdescription, clicks',
        ],
    ],
    'palettes' => [
        'language' => ['showitem' => 'sys_language_uid, l18n_parent'],
        'nameHidden' => ['showitem' => 'name, hidden'],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
                'default' => 0,
            ],
        ],
        'l18n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_kkdownloader_images',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'l18n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
                'items' => [
                    [
                        0 => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim',
            ],
        ],
        'image' => [
            'exclude' => true,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.image',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                [
                    'minitems' => 0,
                    'maxitems' => 10,
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
                        'showSynchronizationLink' => true,
                    ],
                ],
            ),
        ],
        'imagepreview' => [
            'exclude' => true,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.imagepreview',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'imagepreview',
                [
                    'minitems' => 0,
                    'maxitems' => 1,
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
                        'showSynchronizationLink' => true,
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                        --palette--;;filePalette',
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                                        --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                        --palette--;;filePalette',
                            ],
                        ],
                    ],
                ]
            ),
        ],
        'description' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 3,
            ],
        ],
        'longdescription' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.longdescription',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'softref' => 'typolink_tag,email[subst],url',
                'enableRichtext' => true,
            ],
        ],
        'clicks' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:kk_downloader/Resources/Private/Language/locallang_db.xlf:tx_kkdownloader_images.clicks',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'default' => 0,
            ],
        ],
        'last_downloaded' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'ip_last_download' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
