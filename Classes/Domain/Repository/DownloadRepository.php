<?php
namespace JWeiland\KkDownloader\Domain\Repository;

/*
 * This file is part of the kk_downloader project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Repository for all download records
 */
class DownloadRepository
{
    /**
     * @var string
     */
    protected $tableName = 'tx_kkdownloader_images';

    /**
     * @var array
     */
    protected $columnsToSelect = [
        'tx_kkdownloader_images.uid',
        'tx_kkdownloader_images.crdate',
        'tx_kkdownloader_images.tstamp',
        'tx_kkdownloader_images.name',
        'tx_kkdownloader_images.image',
        'tx_kkdownloader_images.imagepreview',
        'tx_kkdownloader_images.description',
        'tx_kkdownloader_images.longdescription',
        'tx_kkdownloader_images.clicks',
        'tx_kkdownloader_images.last_downloaded',
        'tx_kkdownloader_images.ip_last_download',
        'tx_kkdownloader_images.cat',
        'tx_kkdownloader_images.downloaddescription'
    ];

    public function getDownloadByUid(int $uid): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($this->tableName);
        $download = $queryBuilder
            ->select(...$this->columnsToSelect)
            ->from($this->tableName)
            ->where(
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

    public function getDownloads(array $storageFolders = [], int $categoryUid = 0, string $orderBy = '', string $direction = 'ASC'): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($this->tableName);

        if (!empty($storageFolders)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter($storageFolders, Connection::PARAM_INT_ARRAY)
                )
            );
        }

        if (!empty($categoryUid)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->inSet(
                    'cat',
                    $queryBuilder->quote($categoryUid)
                )
            );
        }

        if (empty($orderBy)) {
            $queryBuilder->orderBy('tx_kkdownloader_images.name', 'ASC');
        } else {
            $queryBuilder->orderBy('tx_kkdownloader_images.' . $orderBy, $direction);
        }

        $downloads = $queryBuilder
            ->select(...$this->columnsToSelect)
            ->from($this->tableName)
            ->andWhere(
                $queryBuilder->expr()->in(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter([-1, 0], Connection::PARAM_INT_ARRAY)
                )
            )
            ->execute()
            ->fetchAll();

        if ($downloads === false) {
            $downloads = [];
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

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
