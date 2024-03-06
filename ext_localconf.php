<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(static function(): void {
    // ExtensionManagementUtility::addPItoST43 can not work with namespaced classname. So, I have extracted and
    // modified the needed parts from addPItoST43 here:
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'kk_downloader',
        'setup',
        '
# Setting kk_downloader plugin TypoScript
' . trim('
plugin.tx_kkdownloader_pi1 = USER
plugin.tx_kkdownloader_pi1.userFunc = JWeiland\KkDownloader\Plugin\KkDownloader->main
'
        )
    );

    // addPItoST43 calls addTypoScript() two times. This one here will be added just behind fluid_styled_content
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'kk_downloader',
        'setup',
        '
# Setting kk_downloader plugin TypoScript
tt_content.list.20.kkdownloader_pi1 =< plugin.tx_kkdownloader_pi1
'
        ,
        'defaultContentRendering'
    );

    // Register SVG Icon Identifier
    $svgIcons = [
        'ext-kkdownloader-wizard-icon' => 'plugin_wizard.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($svgIcons as $identifier => $fileName) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            ['source' => 'EXT:kk_downloader/Resources/Public/Icons/' . $fileName]
        );
    }

    // Add kk_downloader plugin to new content element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        "@import 'EXT:kk_downloader/Configuration/TSconfig/ContentElementWizard.tsconfig'>"
    );

    // Migrate kk_downloader categories to sys_category
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['kkMigrateCategories']
        = \JWeiland\KkDownloader\Upgrade\MigrateCategoriesUpgrade::class;
    // Migrate kk_downloader preview images to FAL
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['kkMigrateImagePreview']
        = \JWeiland\KkDownloader\Upgrade\MigratePreviewImageUpgrade::class;
    // Migrate kk_downloader downloads to FAL
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['kkMigrateDownloads']
        = \JWeiland\KkDownloader\Upgrade\MigrateDownloadsUpgrade::class;
    // Migrate FlexForm field dynField to category
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['kkMigrateDynField']
        = \JWeiland\KkDownloader\Upgrade\MigrateDynFieldToCategoryUpgrade::class;
});
