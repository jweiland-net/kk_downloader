<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Resource\FileCollector;

/*
 * Repository for all download records
 */
class DownloadRepository extends AbstractRepository
{
    public function getDownloadByUid(int $uid): array
    {
        $queryBuilder = $this->getQueryBuilderForDownloads();
        $downloadRecord = $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'i.uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        if ($downloadRecord === false) {
            $downloadRecord = [];
        } else {
            $downloadRecord = $this->recordOverlay($downloadRecord, 'tx_kkdownloader_images');
            if ($downloadRecord !== null) {
                $this->attachFilesToDownloadRecord($downloadRecord, 'image');
                $this->attachFilesToDownloadRecord($downloadRecord, 'imagepreview');
            }
        }

        return $downloadRecord ?? [];
    }

    public function getDownloads(
        array $storagePages = [],
        int $categoryUid = 0,
        string $orderBy = '',
        string $direction = 'ASC',
        int $limit = 25,
        int $offset = 0
    ): array {
        $queryBuilder = $this->getQueryBuilderForDownloads();
        $this->addStoragePagesToQueryBuilder($storagePages, $queryBuilder);
        $this->addCategoryToQueryBuilder($categoryUid, $queryBuilder);

        if ($orderBy === '') {
            $queryBuilder->orderBy('i.name', 'ASC');
        } else {
            $queryBuilder->orderBy('i.' . $orderBy, $direction);
        }

        $statement = $queryBuilder
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->execute();

        $downloads = [];
        while ($downloadRecord = $statement->fetch()) {
            $downloadRecord = $this->recordOverlay($downloadRecord, 'tx_kkdownloader_images');
            if ($downloadRecord !== null) {
                $this->attachFilesToDownloadRecord($downloadRecord, 'image');
                $this->attachFilesToDownloadRecord($downloadRecord, 'imagepreview');
                $downloads[] = $downloadRecord;
            }
        }

        return $downloads;
    }

    public function countDownloads(array $storagePages = [], int $categoryUid = 0): int
    {
        $queryBuilder = $this->getQueryBuilderForDownloads();
        $this->addStoragePagesToQueryBuilder($storagePages, $queryBuilder);
        $this->addCategoryToQueryBuilder($categoryUid, $queryBuilder);

        return (int)$queryBuilder
            ->resetQueryParts(['select', 'groupBy', 'orderBy'])
            ->count('*')
            ->execute()
            ->fetchColumn();
    }

    protected function addStoragePagesToQueryBuilder(array $storagePages, QueryBuilder $queryBuilder): void
    {
        if ($storagePages === []) {
            return;
        }

        $queryBuilder->andWhere(
            $queryBuilder->expr()->in(
                'i.pid',
                $queryBuilder->createNamedParameter($storagePages, Connection::PARAM_INT_ARRAY)
            )
        );
    }

    protected function addCategoryToQueryBuilder(int $category, QueryBuilder $queryBuilder): void
    {
        if ($category === 0) {
            return;
        }

        $queryBuilder
            ->join(
                'i',
                'sys_category_record_mm',
                'sc_mm',
                (string)$queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        'i.uid',
                        $queryBuilder->quoteIdentifier('sc_mm.uid_foreign')
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
                $queryBuilder->expr()->eq(
                    'sc_mm.uid_local',
                    $queryBuilder->createNamedParameter($category, \PDO::PARAM_INT)
                )
            );
    }

    protected function attachFilesToDownloadRecord(array &$downloadRecord, string $column): void
    {
        $fileCollector = GeneralUtility::makeInstance(FileCollector::class);
        $fileCollector->addFilesFromRelation(
            'tx_kkdownloader_images',
            $column,
            $downloadRecord
        );
        $downloadRecord[$column] = $fileCollector->getFiles();
    }

    public function updateImageRecordAfterDownload(array $downloadRecord): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_kkdownloader_images');
        $connection->update(
            'tx_kkdownloader_images',
            [
                'clicks' => (int)$downloadRecord['clicks'] + 1,
                'last_downloaded' => date('U'),
                'ip_last_download' => $_SERVER['REMOTE_ADDR'],
            ],
            [
                'uid' => (int)$downloadRecord['uid'],
            ]
        );
    }

    protected function getQueryBuilderForDownloads(): QueryBuilder
    {
        // Column: sys_language_uid
        $languageField = $GLOBALS['TCA']['tx_kkdownloader_images']['ctrl']['languageField'];
        // Column: l10n_parent
        $transOrigPointerField = $GLOBALS['TCA']['tx_kkdownloader_images']['ctrl']['transOrigPointerField'];

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_kkdownloader_images');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder
            ->select(...$this->getColumnsForDownloadTable())
            ->from('tx_kkdownloader_images', 'i')
            ->andWhere(
                $queryBuilder->expr()->in(
                    'i.' . $languageField,
                    [0, -1]
                ),
                $queryBuilder->expr()->eq(
                    'i.' . $transOrigPointerField,
                    0
                )
            )
            ->groupBy(...$this->getColumnsForDownloadTable()); // keep that because of category relation

        if ($GLOBALS['TCA']['tx_kkdownloader_images']['ctrl']['versioningWS']) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('i.t3ver_oid', 0)
            );
        }

        return $queryBuilder;
    }

    /**
     * ->select() and ->groupBy() has to be the same in DB configuration
     * where only_full_group_by is activated.
     */
    protected function getColumnsForDownloadTable(): array
    {
        $columns = [];
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_kkdownloader_images');
        if ($connection->getSchemaManager() instanceof AbstractSchemaManager) {
            $columns = array_map(
                static function ($column): string {
                    return 'i.' . $column;
                },
                array_keys(
                    $connection->getSchemaManager()->listTableColumns('tx_kkdownloader_images') ?? []
                )
            );
        }

        return $columns;
    }
}
