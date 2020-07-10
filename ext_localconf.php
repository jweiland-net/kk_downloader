<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function() {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('options.saveDocNew.tx_kkdownloader_images = 1');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('options.saveDocNew.tx_kkdownloader_cat = 1');

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

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'kk_downloader',
        'setup',
        '
# Setting kk_downloader plugin TypoScript
tt_content.list.20.kkdownloader_pi1 = < plugin.tx_kkdownloader_pi1
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

    // add kk_downloader plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:kk_downloader/Configuration/TSconfig/ContentElementWizard.txt">');
});
