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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * Repository for kk_downloader categories
 */
class CategoryRepository
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
