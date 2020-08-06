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
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for kk_downloader categories
 */
class CategoryRepository extends Repository
{
    /**
     * TableName for categories
     *
     * @var string
     */
    protected $tableName = 'tx_kkdownloader_cat';

    /**
     * Returns all categories by UIDs
     *
     * @param string $commaSeparatedCategories
     * @return array
     */
    public function getCategoriesByUids(string $commaSeparatedCategories): array
    {
        $categories = [];

        if (empty($commaSeparatedCategories)) {
            return $categories;
        }

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($this->tableName);
        $categories = $queryBuilder
            ->select('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter(
                        GeneralUtility::intExplode(',', $commaSeparatedCategories, true),
                        Connection::PARAM_INT_ARRAY
                    )
                )
            )
            ->execute()
            ->fetchAll();

        if ($categories === false) {
            $categories = [];
        }

        return $categories;
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
