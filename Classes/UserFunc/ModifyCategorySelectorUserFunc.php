<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\UserFunc;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * UserFunc to add items to category selector (FlexForm)
 */
class ModifyCategorySelectorUserFunc
{
    /**
     * Add items to category selector in FlexForm
     */
    public function addCategoryItemsToSelector(array &$parameters): void
    {
        $storagePid = $this->getStorageFolderPid();

        $queryBuilder = $this->getQueryBuilderForTable('sys_category');
        if (!empty($storagePid)) {
            $queryBuilder->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($storagePid, \PDO::PARAM_INT)
                )
            );
        }

        $statement = $queryBuilder
            ->select('uid', 'title')
            ->from('sys_category')
            ->andWhere(
                $queryBuilder->expr()->in(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter([-1, 0], Connection::PARAM_INT_ARRAY)
                )
            )
            ->orderBy('title', 'ASC')
            ->execute();

        $categoryItem = [];
        while ($categoryRecord = $statement->fetch()) {
            $categoryItem[] = [
                0 => $categoryRecord['title'],
                1 => $categoryRecord['uid']
            ];
        }

        $parameters['items'] = array_merge($parameters['items'], $categoryItem);
    }

    /**
     * Returning sysfolder ID where records are stored
     */
    public function getStorageFolderPid(): int
    {
        $positionPid = htmlspecialchars_decode(GeneralUtility::_GET('id') ?? '');

        if (empty($positionPid)) {
            $siteId = GeneralUtility::_GET('returnUrl');
            $siteId = GeneralUtility::explodeUrl2Array($siteId);
            $positionPid = (int)$siteId['id'];
        }

        // Negative PID values is pointing to a page on the same level as the current.
        if ($positionPid < 0) {
            $pidRow = BackendUtility::getRecord('pages', abs($positionPid), 'pid');
            $positionPid = $pidRow['pid'];
        }

        $row = BackendUtility::getRecord('pages', $positionPid);
        $TSconfig = BackendUtility::getTCEFORM_TSconfig('pages', $row);

        return (int)$TSconfig['_STORAGE_PID'];
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
