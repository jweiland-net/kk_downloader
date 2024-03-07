<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\Domain\Repository;

use JWeiland\KkDownloader\Traits\ContextTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractRepository
{
    use ContextTrait;

    protected function recordOverlay(array $row, string $tableName): ?array
    {
        $languageUid = $this->getLanguageUid();
        $languageOverlayMode = $this->getLanguageOverlayMode();

        $pageRepository = $this->getPageRepository();

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

    protected function getPageRepository(): PageRepository
    {
        return GeneralUtility::makeInstance(PageRepository::class);
    }
}
