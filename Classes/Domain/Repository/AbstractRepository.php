<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\Domain\Repository;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

class AbstractRepository
{
    protected function recordOverlay(array $row, string $tableName)
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $languageUid = (int)$context->getPropertyFromAspect('language', 'contentId');
        $languageOverlayMode = (string)$context->getPropertyFromAspect('language', 'legacyOverlayType') ?: '';

        // SF: Move PageRepo to core while removing TYPO3 9 compatibility
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);

        // Workspace overlay
        $pageRepository->versionOL($tableName, $row);

        // Language overlay
        if (
            isset($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'])
            && $row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']] > 0
        ) {
            //force overlay by faking default language record, as getRecordOverlay can only handle default language records
            $row['uid'] = $row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']];
            $row[$GLOBALS['TCA'][$tableName]['ctrl']['languageField']] = 0;
        }

        return $pageRepository->getRecordOverlay(
            $tableName,
            $row,
            $languageUid,
            $languageOverlayMode
        );
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
