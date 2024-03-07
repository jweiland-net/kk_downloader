<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\Upgrade;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Service\UpgradeWizardsService;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * With kk_downloader 6.0.0 we have renamed the FlexForm field "dynField" to "category".
 * This UpgradeWizard migrated all tt_content records to use this new field.
 */
class MigrateDynFieldToCategoryUpgrade implements UpgradeWizardInterface, ChattyInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    public function getIdentifier(): string
    {
        return 'kkMigrateDynField';
    }

    public function getTitle(): string
    {
        return '[kk_downloader] Migrate FlexForm dynField to category';
    }

    public function getDescription(): string
    {
        return 'Migrate FlexForm dynField to category';
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

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
        $records = $this->getTtContentRecordsWithKkDownloaderPlugin();
        foreach ($records as $record) {
            $valueFromDatabase = (string)$record['pi_flexform'] !== '' ? GeneralUtility::xml2array($record['pi_flexform']) : [];
            if (
                !is_array($valueFromDatabase)
                || empty($valueFromDatabase)
                || !isset($valueFromDatabase['data'])
                || !is_array($valueFromDatabase['data'])
            ) {
                continue;
            }

            try {
                if (
                    ArrayUtility::getValueByPath(
                        $valueFromDatabase,
                        'data/sDEF/lDEF/dynField'
                    )
                ) {
                    return true;
                }
            } catch (MissingArrayPathException $e) {
                // $path does not exist in array
                continue;
            } catch (\InvalidArgumentException $e) {
                // $path is not a string or array
                continue;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function executeUpdate(): bool
    {
        $upgradeWizardsService = GeneralUtility::makeInstance(UpgradeWizardsService::class);
        if (version_compare(TYPO3_branch, '9.5', '<=')) {
            $wizards = array_filter($upgradeWizardsService->getUpgradeWizardsList(), static function ($wizard) {
                return $wizard['identifier'] === 'kkMigrateCategories' && $wizard['shouldRenderWizard'] === true;
            });
            if ($wizards !== []) {
                $this->output->writeln('Please execute kk_downloader UpgradeWizard to migrate categories first.');

                return false;
            }
        } elseif ($upgradeWizardsService->getWizardInformationByIdentifier('kkMigrateCategories')['shouldRenderWizard']) {
            $this->output->writeln('Please execute kk_downloader UpgradeWizard to migrate categories first.');

            return false;
        }

        $records = $this->getTtContentRecordsWithKkDownloaderPlugin();
        foreach ($records as $record) {
            $valueFromDatabase = (string)$record['pi_flexform'] !== '' ? GeneralUtility::xml2array($record['pi_flexform']) : [];
            if (!is_array($valueFromDatabase) || empty($valueFromDatabase)) {
                continue;
            }

            $this->moveField($valueFromDatabase, 'dynField', 'category');

            $connection = $this->getConnectionPool()->getConnectionForTable('tt_content');
            $connection->update(
                'tt_content',
                [
                    'pi_flexform' => $this->checkValue_flexArray2Xml($valueFromDatabase),
                ],
                [
                    'uid' => (int)$record['uid'],
                ],
                [
                    'pi_flexform' => \PDO::PARAM_STR,
                ]
            );
        }

        return true;
    }

    protected function getTtContentRecordsWithKkDownloaderPlugin(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

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

    /**
     * Move field from one sheet to another and remove field from old location
     */
    protected function moveField(
        array &$valueFromDatabase,
        string $oldField,
        string $newField = '',
        string $oldSheet = 'sDEF',
        string $newSheet = ''
    ): void {
        $newField = $newField ?: $oldField;
        $newSheet = $newSheet ?: $oldSheet;

        try {
            $value = ArrayUtility::getValueByPath(
                $valueFromDatabase,
                sprintf(
                    'data/%s/lDEF/%s',
                    $oldSheet,
                    $oldField
                )
            );

            // Create base sheet, if not exist
            if (!array_key_exists($newSheet, $valueFromDatabase['data'])) {
                $valueFromDatabase['data'][$newSheet] = [
                    'lDEF' => [],
                ];
            }

            // Move field to new location, if not already done
            if (!array_key_exists($newField, $valueFromDatabase['data'][$newSheet]['lDEF'])) {
                $valueFromDatabase['data'][$newSheet]['lDEF'][$newField] = $value;
            }

            // Remove old reference
            unset($valueFromDatabase['data'][$oldSheet]['lDEF'][$oldField]);
        } catch (MissingArrayPathException $e) {
            // $path does not exist in array
        } catch (\InvalidArgumentException $e) {
            // $path is not a string or array
        }
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
