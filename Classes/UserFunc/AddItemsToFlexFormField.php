<?php
namespace JWeiland\KkDownloader\UserFunc;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * UserFunc to add items to FlexForm fields
 */
class AddItemsToFlexFormField
{
    /**
     * Fill field with configured categories
     *
     * @param array $config
     * @return array
     */
    public function addCategoryItems(array $config): array
    {
        $storagePid = $this->getStorageFolderPid();

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_kkdownloader_cat');
        if (!empty($storagePid)) {
            $queryBuilder->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($storagePid, \PDO::PARAM_INT)
                )
            );
        }

        $statement = $queryBuilder
            ->select('uid', 'cat')
            ->from('tx_kkdownloader_cat')
            ->andWhere(
                $queryBuilder->expr()->in(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter([-1, 0], Connection::PARAM_INT_ARRAY)
                )
            )
            ->orderBy('cat', 'ASC')
            ->execute();

        $categoryItems = [];
        while ($row = $statement->fetch()) {
            $categoryItems[] = [
                0 => $row['cat'],
                1 => $row['uid']
            ];
        }

        $config['items'] = array_merge($config['items'], $categoryItems);

        return $config;
    }

    /**
     * Returning StorageFolder PID where records are stored
     */
    public function getStorageFolderPid(): int
    {
        $positionPid = (int)GeneralUtility::_GET('id');

        if (empty($positionPid)) {
            $siteId = GeneralUtility::explodeUrl2Array(GeneralUtility::_GET('returnUrl'));
            $positionPid = (int)$siteId['db_list.php?id'];
        }

        // Negative PID values are pointing to a page on the same level as the current.
        if ($positionPid < 0) {
            $pidRow = BackendUtility::getRecord('pages', abs($positionPid), 'pid');
            $positionPid = (int)$pidRow['pid'];
        }

        $row = BackendUtility::getRecord('pages', $positionPid);
        $TSconfig = BackendUtility::getTCEFORM_TSconfig('pages', $row);

        return (int)$TSconfig['_STORAGE_PID'];
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
