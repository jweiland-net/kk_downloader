<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * Repository for all download records
 */
class DownloadRepository
{
    /**
     * @var string
     */
    protected $tableName = 'tx_kkdownloader_images';

    public function getDownloadByUid(int $uid): array
    {
        $queryBuilder = $this->getQueryBuilderForTable($this->tableName);
        $download = $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        if ($download === false) {
            $download = [];
        }

        return $download;
    }

    public function getDownloads(
        array $storageFolders = [],
        int $categoryUid = 0,
        string $orderBy = '',
        string $direction = 'ASC'
    ): array {
        $queryBuilder = $this->getQueryBuilderForTable($this->tableName);

        if ($storageFolders !== []) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter($storageFolders, Connection::PARAM_INT_ARRAY)
                )
            );
        }

        if ($categoryUid > 0) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->inSet(
                    'cat',
                    $queryBuilder->quote($categoryUid)
                )
            );
        }

        if ($orderBy === '') {
            $queryBuilder->orderBy('tx_kkdownloader_images.name', 'ASC');
        } else {
            $queryBuilder->orderBy('tx_kkdownloader_images.' . $orderBy, $direction);
        }

        $statement = $queryBuilder->execute();

        $downloads = [];
        while ($download = $statement->fetch()) {
            $downloads[] = $download;
        }

        return $downloads;
    }

    public function updateImageRecordAfterDownload(int $uid)
    {
        $download = $this->getDownloadByUid($uid);

        $amountOfDownloads = (int)$download['clicks'] + 1;

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($this->tableName);
        $queryBuilder
            ->update($this->tableName)
            ->set('tx_kkdownloader_images.clicks', $amountOfDownloads)
            ->set('tx_kkdownloader_images.last_downloaded', date('U'))
            ->set('tx_kkdownloader_images.ip_last_download', $_SERVER['REMOTE_ADDR'])
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute();
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        // Column: sys_language_uid
        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];
        // Column: l10n_parent
        $transOrigPointerField = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'];

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->andWhere(
                $queryBuilder->expr()->in(
                    $languageField,
                    [0, -1]
                ),
                $queryBuilder->expr()->eq(
                    $transOrigPointerField,
                    0
                )
            );

        if ($GLOBALS['TCA'][$table]['ctrl']['versioningWS']) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('t3ver_oid', 0)
            );
        }

        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
