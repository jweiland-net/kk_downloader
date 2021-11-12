<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\Domain\Repository;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * Repository for kk_downloader categories
 */
class CategoryRepository extends AbstractRepository
{
    /**
     * Returns all categories by Download UID
     *
     * @return array[]
     */
    public function getCategoriesByDownloadUid(int $downloadUid): array
    {
        $queryBuilder = $this->getQueryBuilderForCategories();
        $statement = $queryBuilder
            ->select('sc.*')
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'sc_mm.uid_foreign',
                    $queryBuilder->createNamedParameter($downloadUid, \PDO::PARAM_INT)
                )
            )
            ->execute();

        $categories = [];
        while ($category = $statement->fetch()) {
            $categories[] = $this->recordOverlay($category, 'sys_category');
        }

        return $categories;
    }

    protected function getQueryBuilderForCategories(): QueryBuilder
    {
        // Column: sys_language_uid
        $languageField = $GLOBALS['TCA']['sys_category']['ctrl']['languageField'];
        // Column: l10n_parent
        $transOrigPointerField = $GLOBALS['TCA']['sys_category']['ctrl']['transOrigPointerField'];

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_category');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        return $queryBuilder
            ->from('sys_category', 'sc')
            ->leftJoin(
                'sc',
                'sys_category_record_mm',
                'sc_mm',
                (string)$queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        'sc.uid',
                        $queryBuilder->quoteIdentifier('sc_mm.uid_local')
                    ),
                    $queryBuilder->expr()->eq(
                        'sc_mm.tablenames',
                        $queryBuilder->createNamedParameter('tx_kkdownloader_images', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'sc_mm.fieldname',
                        $queryBuilder->createNamedParameter('categories', \PDO::PARAM_STR)
                    )
                )
            )
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
    }
}
