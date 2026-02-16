<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'kk_downloader',
    'Configuration/TypoScript/',
    'KK Downloader'
);
