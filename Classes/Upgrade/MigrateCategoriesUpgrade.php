<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\Upgrade;

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * With kk_downloader 5.1.0 we use sys_categories.
 * This UpgradeWizard creates a new root category in sys_category called "KK Downloader"
 * and migrates all kk_downloader categories to this root sys_category.
 */
class MigrateCategoriesUpgrade implements UpgradeWizardInterface
{
    public function getIdentifier(): string
    {
        return 'kkMigrateCategories';
    }

    public function getTitle(): string
    {
        return '[kk_downloader] Migrate KK categories to TYPO3 sys_categories';
    }

    public function getDescription(): string
    {
        return 'Migrate KK categories to TYPO3 sys_categories. Please backup your database as we delete successfully ' .
            'migrated kk_downloader categories afterwards.';
    }

    /**
     * @var int[]
     */
    protected $migratedCategories = [];

    /**
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = $this->getQueryBuilderForKkDownloaderCategories();
        $schemaManager = $queryBuilder->getConnection()->getSchemaManager();
        if ($schemaManager === null) {
            return false;
        }
        if (!$schemaManager->tablesExist('tx_kkdownloader_cat')) {
            return false;
        }

        return (bool)$queryBuilder
            ->select('*')
            ->execute()
            ->fetchColumn(0);
    }

    public function executeUpdate(): bool
    {
        $this->migrateCategories();
        $this->migrateCatInDownloadRecord();
        $this->migrateCatInFlexForm();

        return true;
    }

    protected function migrateCategories(): void
    {
        $rootSysCategoryUid = $this->getUidOfRootSysCategory();
        $queryBuilder = $this->getQueryBuilderForKkDownloaderCategories();
        $statement = $queryBuilder
            ->select('*')
            ->execute();

        while ($kkDownloaderCategory = $statement->fetch()) {
            $l18nParent = $kkDownloaderCategory['l18n_parent'];
            if (array_key_exists($l18nParent, $this->migratedCategories)) {
                $l18nParent = $this->migratedCategories[$l18nParent];
            }

            $connection = $this->getConnectionPool()->getConnectionForTable('sys_category');
            $connection->insert(
                'sys_category',
                [
                    'parent' => $rootSysCategoryUid,
                    'pid' => $kkDownloaderCategory['pid'],
                    'hidden' => $kkDownloaderCategory['hidden'],
                    'tstamp' => $kkDownloaderCategory['tstamp'],
                    'crdate' => $kkDownloaderCategory['crdate'],
                    'cruser_id' => $kkDownloaderCategory['cruser_id'],
                    'sys_language_uid' => $kkDownloaderCategory['sys_language_uid'],
                    'l10n_parent' => $l18nParent,
                    'l10n_diffsource' => $kkDownloaderCategory['l18n_diffsource'],
                    'title' => $kkDownloaderCategory['cat'],
                ],
                [
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_STR,
                ]
            );

            $this->migratedCategories[$kkDownloaderCategory['uid']] = (int)$connection->lastInsertId('sys_category');
        }
    }

    protected function migrateCatInDownloadRecord(): void
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_kkdownloader_images');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $statement = $queryBuilder
            ->select('uid', 'cat')
            ->from('tx_kkdownloader_images')
            ->orWhere(
                $queryBuilder->expr()->neq(
                    'cat',
                    $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->isNotNull(
                    'cat'
                )
            )
            ->execute();

        while ($downloadRecord = $statement->fetch()) {
            $sorting = 0;
            $oldCategories = GeneralUtility::intExplode(',', $downloadRecord['cat'], true);
            foreach ($oldCategories as $oldCategory) {
                if ($oldCategory === 0) {
                    continue;
                }

                if (!array_key_exists($oldCategory, $this->migratedCategories)) {
                    continue;
                }

                $connection = $this->getConnectionPool()->getConnectionForTable('sys_category_record_mm');
                $connection->insert(
                    'sys_category_record_mm',
                    [
                        'uid_local' => $this->migratedCategories[$oldCategory],
                        'uid_foreign' => $downloadRecord['uid'],
                        'tablenames' => 'tx_kkdownloader_images',
                        'fieldname' => 'categories',
                        'sorting' => $sorting,
                    ]
                );
                $sorting++;
            }

            $connection = $this->getConnectionPool()->getConnectionForTable('tx_kkdownloader_images');
            $connection->update(
                'tx_kkdownloader_images',
                [
                    'categories' => $sorting,
                ],
                [
                    'uid' => $downloadRecord['uid'],
                ]
            );
        }
    }

    protected function migrateCatInFlexForm(): void
    {
        $records = $this->getTtContentRecordsWithKkDownloaderPlugin();
        foreach ($records as $record) {
            $valueFromDatabase = (string)$record['pi_flexform'] !== '' ? GeneralUtility::xml2array($record['pi_flexform']) : [];
            if (!is_array($valueFromDatabase) || empty($valueFromDatabase)) {
                continue;
            }

            try {
                $csvCategories = ArrayUtility::getValueByPath($valueFromDatabase, 'data/sDEF/lDEF/dynField/vDEF');
                $oldCategories = GeneralUtility::intExplode(',', $csvCategories, true);

                $newCategories = [];
                foreach ($oldCategories as $oldCategory) {
                    if ($oldCategory === 0) {
                        continue;
                    }

                    $newCategories[] = $this->migratedCategories[$oldCategory];
                }

                $valueFromDatabase = ArrayUtility::setValueByPath(
                    $valueFromDatabase,
                    'data/sDEF/lDEF/dynField/vDEF',
                    implode(',', $newCategories)
                );
            } catch (\Exception $exception) {
                // Value does not exists
            }

            $connection = $this->getConnectionPool()->getConnectionForTable('tt_content');
            $connection->update(
                'tt_content',
                [
                    'pi_flexform' => $this->checkValue_flexArray2Xml($valueFromDatabase)
                ],
                [
                    'uid' => (int)$record['uid']
                ],
                [
                    'pi_flexform' => \PDO::PARAM_STR
                ]
            );
        }
    }

    /**
     * Get tt_content records with plugin kkdownloader_pi1
     *
     * @return array[]
     */
    protected function getTtContentRecordsWithKkDownloaderPlugin(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $statement = $queryBuilder
            ->select('uid', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter('list', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'list_type',
                    $queryBuilder->createNamedParameter('kkdownloader_pi1', \PDO::PARAM_STR)
                )
            )
            ->execute();

        $records = [];
        while ($record = $statement->fetch()) {
            $records[] = $record;
        }

        return $records;
    }

    protected function getQueryBuilderForKkDownloaderCategories(): QueryBuilder
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_kkdownloader_cat');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        // orderBy to have cat in default language migrated first.
        $queryBuilder
            ->from('tx_kkdownloader_cat')
            ->orderBy('sys_language_uid', 'ASC');

        return $queryBuilder;
    }

    protected function getUidOfRootSysCategory(): int
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_category');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $sysCategory = $queryBuilder
            ->select('uid')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq(
                    'title',
                    $queryBuilder->createNamedParameter('KK Downloader', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'parent',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        if (empty($sysCategory)) {
            $connection = $this->getConnectionPool()->getConnectionForTable('sys_category');
            $connection->insert(
                'sys_category',
                [
                    'pid' => 0,
                    'tstamp' => time(),
                    'crdate' => time(),
                    'parent' => 0,
                    'title' => 'KK Downloader',
                ],
                [
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_STR,
                ]
            );
            $sysCategoryUid = $connection->lastInsertId('sys_category');
        } else {
            $sysCategoryUid = $sysCategory['uid'];
        }

        return (int)$sysCategoryUid;
    }

    /**
     * Converts an array to FlexForm XML
     *
     * @param array $array Array with FlexForm data
     * @return string Input array converted to XML
     */
    public function checkValue_flexArray2Xml(array $array): string
    {
        $flexObj = GeneralUtility::makeInstance(FlexFormTools::class);

        return $flexObj->flexArray2Xml($array, true);
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
