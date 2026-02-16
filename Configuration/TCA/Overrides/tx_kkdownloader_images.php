<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords(
    'tx_kkdownloader_images'
);

// Add column "categories" to tx_kkdownloader_images table
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'kk_downloader',
    'tx_kkdownloader_images',
    'categories',
    []
);
