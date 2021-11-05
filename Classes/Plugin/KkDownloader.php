<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\Plugin;

use JWeiland\KkDownloader\Domain\Repository\CategoryRepository;
use JWeiland\KkDownloader\Domain\Repository\DownloadRepository;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/*
 * Main Plugin class
 */
class KkDownloader extends AbstractPlugin
{
    /**
     * Same as class name
     *
     * @var string
     */
    public $prefixId = 'tx_kkdownloader_pi1';

    /**
     * Path to this script relative to the extension dir.
     *
     * @var string
     */
    public $scriptRelPath = 'pi1/class.tx_kkdownloader_pi1.php';

    /**
     * Path to extension
     *
     * @var string
     */
    public $extPath = 'typo3conf/ext/kk_downloader/';

    /**
     * The extension key
     *
     * @var string
     */
    public $extKey = 'kk_downloader';

    public $pi_checkCHash = true;
    public $filebasepath = 'uploads/tx_kkdownloader/';
    public $defaultTemplate = 'EXT:kk_downloader/Resources/Private/Templates/MainTemplate.html';

    public $showCats;
    public $template;
    public $internal = [];

    /**
     * Path to download ($_GET)
     *
     * @var string
     */
    protected $download = '';

    /**
     * @var int
     */
    protected $did = 0;

    /**
     * UID of download to show on detail page
     *
     * @var int
     */
    protected $uidOfDownload = 0;

    /**
     * Contains settings of FlexForm
     *
     * @var array
     */
    protected $settings = [];

    protected $languageUid = 0;
    protected $languageOverlayMode = false;

    /**
     * @var DownloadRepository
     */
    protected $downloadRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var MarkerBasedTemplateService
     */
    protected $templateService;

    /**
     * The main method of the PlugIn
     *
     * @param string $content: The PlugIn content
     * @param array $conf: The PlugIn configuration
     * @return string The content that is displayed on the website
     */
    public function main(string $content, array $conf): string
    {
        $this->conf = $conf; // Storing configuration as a member var
        $this->pi_loadLL(); // Loading language-labels
        $this->pi_setPiVarDefaults(); // Set default piVars from TS

        $this->download = GeneralUtility::_GP('download');
        $this->did = (int)GeneralUtility::_GP('did');
        $this->uidOfDownload = (int)$this->piVars['uid'];

        // flexform Integration
        $this->pi_initPIflexform(); // Init and get the flexform data of the plugin
        $this->initialize();

        // if a download has happened
        if (!empty($this->download)) {
            $this->startDownload(basename($this->download), $this->did);
        }

        // Template settings
        $templateFile = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template_file', 'sDEF'));
        $templateFile = $templateFile ?: $this->conf['templateFile'];
        if (empty($templateFile)) {
            $templateFile = $this->defaultTemplate;
        }

        $defaultDownloadPid = $this->conf['defaultDownloadPid'];
        if (empty($defaultDownloadPid)) {
            $defaultDownloadPid = 'all';
        }

        $this->internal['results_at_a_time'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'results_at_a_time', 'sDEF');
        $this->internal['results_at_a_time'] = $this->internal['results_at_a_time'] > 0 ? (int)($this->internal['results_at_a_time']) : (int)($this->conf['results_at_a_time']);
        $this->internal['results_at_a_time'] = $this->internal['results_at_a_time'] > 0 ? (int)($this->internal['results_at_a_time']) : 1001;
        $this->internal['maxPages'] = $this->conf['pageBrowser.']['maxPages'] > 0 ? (int)($this->conf['pageBrowser.']['maxPages']) : 10;

        $view = $this->getView();
        $view->setTemplatePathAndFilename($templateFile);
        if ($this->settings['whatToDisplay'] === 'SINGLE') {
            if (!empty($this->uidOfDownload)) {
                $downloadRecord = $this->downloadRepository->getDownloadByUid($this->uidOfDownload);
                $downloadRecord = $this->recordOverlay($downloadRecord, 'tx_kkdownloader_images');

                if ($this->settings['showCats']) {
                    $downloadRecord['categories'] = $this->getCategoriesAsString((int)$downloadRecord['uid']);
                }
                if ($this->settings['showImagePreview']) {
                    $downloadRecord['previewImage'] = $this->createPreviewImage($downloadRecord);
                }
                $downloadRecord['fileItems'] = $this->generateDownloadLinks(
                    $downloadRecord,
                    (int)$this->conf['linkdescription']
                );

                $view->assign('download', $downloadRecord);
            } else {
                $this->addFlashMessage(
                    LocalizationUtility::translate('error.callSingleViewWithoutUid.description', 'kkDownloader'),
                    LocalizationUtility::translate('error.callSingleViewWithoutUid.title', 'kkDownloader'),
                    FlashMessage::ERROR
                );
            }
        } else {
            $storageFoldersForDownloads = $this->cObj->data['pages'];
            if (!$storageFoldersForDownloads) {
                $storageFoldersForDownloads = $defaultDownloadPid;
            }
            if (
                !empty($storageFoldersForDownloads)
                && strtolower(trim($storageFoldersForDownloads)) === 'all'
            ) {
                $storageFoldersForDownloads = '';
            }
            $downloads = $this->downloadRepository->getDownloads(
                GeneralUtility::intExplode(',', $storageFoldersForDownloads, true),
                $this->settings['categoryUid'],
                $this->settings['orderBy'],
                $this->settings['orderDirection']
            );
            foreach ($downloads as &$downloadRecord) {
                $downloadRecord = $this->recordOverlay($downloadRecord, 'tx_kkdownloader_images');
                if ($this->settings['showCats']) {
                    $downloadRecord['categories'] = $this->getCategoriesAsString((int)$downloadRecord['uid']);
                }
                if ($this->settings['showImagePreview']) {
                    $downloadRecord['previewImage'] = $this->createPreviewImage($downloadRecord);
                }
                $downloadRecord['fileItems'] = $this->generateDownloadLinks(
                    $downloadRecord,
                    (int)$this->conf['linkdescription']
                );
            }
            unset($downloadRecord);

            $view->assign('downloads', $downloads);

            // Browse list items;
            $this->internal['res_count'] = count($downloads);

            if ($this->internal['results_at_a_time'] > 0 && count($downloads) > $this->internal['results_at_a_time']) {
                if (!$this->conf['pageBrowser.']['showPBrowserText']) {
                    $this->LOCAL_LANG[$this->LLkey]['pi_list_browseresults_page'] = '';
                }

                $this->addPageBrowserSettingsToView($view);
            } else {
                if ($this->conf['pageBrowser.']['showResultCount']) {
                    $this->addPageBrowserSettingsToView($view);
                }
            }
        }

        $view->assignMultiple([
            'settings' => $this->settings,
            'pidOfDetailPage' => $this->conf['singlePID'] ?: $this->getTypoScriptFrontendController()->id
        ]);

        return $view->render();
    }

    protected function createPreviewImage(array $downloadRecord): string
    {
        $previewImageForDownload = '';

        // if download record contains a preview image
        if (!empty($downloadRecord['imagepreview'])) {
            $imgConf = $this->conf['image.'];
            $imgConf['file.']['import.']['dataWrap'] = '{file:current:storage}:{file:current:identifier}';
            $imgConf['altText.']['data'] = 'file:current:title';
            $imgConf['titleText.']['data'] = 'file:current:title';

            $previewImageForDownload = $this->cObj->cObjGetSingle(
                'FILES',
                [
                    'references.' => [
                        'table' => 'tx_kkdownloader_images',
                        'uid' => (int)$downloadRecord['uid'],
                        'fieldName' => 'imagepreview'
                    ],
                    'begin' => 0,
                    'maxItems' => 1,
                    'renderObj' => 'IMAGE',
                    'renderObj.' => $imgConf
                ]
            );
        } else {
            $allowedMimeTypes = [
                'image/gif',
                'image/jpeg',
                'image/png',
                'image/bmp',
                'image/tiff',
            ];

            // Loop throw download images and use first image with allowed mimetype as thumbnail
            /** @var FileReference $fileReference */
            foreach ($downloadRecord['files'] as $fileReference) {
                // MimeType is not an image, check against 'pdf'
                // Create IMG-Tag, if image has allowed MimeType.
                if (
                    $fileReference->getExtension() === 'pdf'
                    || in_array($fileReference->getMimeType(), $allowedMimeTypes)
                ) {
                    $img = $this->conf['image.'];
                    $img['file'] = $fileReference->getPublicUrl();
                    $previewImageForDownload = $this->cObj->cObjGetSingle('IMAGE', $img);
                    break;
                }
            }
        }

        return $previewImageForDownload;
    }

    protected function initialize()
    {
        $this->initializeLanguage();
        $this->settings = $this->getFlexFormSettings();

        $this->downloadRepository = GeneralUtility::makeInstance(DownloadRepository::class);
        $this->categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        $this->templateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
    }

    /**
     * Collect language information
     */
    protected function initializeLanguage()
    {
        if (is_object($this->getTypoScriptFrontendController())) {
            $this->languageUid = (int)$this->getTypoScriptFrontendController()->sys_language_content;
            $this->languageOverlayMode = $this->getTypoScriptFrontendController()->sys_language_contentOL ?: false;
        }
    }

    /**
     * load all FLEXFORM data fields into variables for further use:
     */
    protected function getFlexFormSettings(): array
    {
        $settings = [];
        $settings['categoryUid'] = (int)$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'dynField', 'sDEF');
        $settings['showCats'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showCats', 'sDEF');
        $settings['orderBy'] = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'orderby', 'sDEF'));
        $settings['orderDirection'] = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ascdesc', 'sDEF'));
        $settings['orderDirection'] = $settings['orderDirection'] ?: 'ASC';
        $settings['showFileSize'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'filesize', 'sDEF');
        $settings['showPagebrowser'] = (bool)$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showPagebrowser', 'sDEF');
        $settings['showImagePreview'] = (bool)$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imagepreview', 'sDEF');
        $settings['showDownloadsCount'] = (bool)$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'downloads', 'sDEF');
        $settings['showEditDate'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showEditDate', 'sDEF');
        $settings['showDateLastDownload'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showDateLastDownload', 'sDEF');
        $settings['showIPLastDownload'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showIPLastDownload', 'sDEF');
        $settings['showFileMDate'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showFileMDate', 'sDEF');
        $settings['whatToDisplay'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');

        // special handling for creation date
        $creationDateType = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showCRDate', 'sDEF'));
        $settings['creationDateType'] = '';
        if (!empty($creationDateType)) {
            if ($creationDateType === '1') {
                $dtf = $this->conf['dateformat'] ?: 'd.m.Y';
            } else {
                $dtf = $this->conf['datetimeformat'] ?: 'd.m.Y H:i';
            }
            $settings['creationDateType'] = $dtf;
        }

        return $settings;
    }

    /**
     * Generates the download links
     *
     * @param array $downloadRecord The download record
     * @param int $downloadDescriptionType 1 = filename.fileextension, 2 = filename, 3 = fileextension
     * @return string The generated links
     */
    protected function generateDownloadLinks(array $downloadRecord, int $downloadDescriptionType = 1): string
    {
        $content = '';

        /** @var $fileReference FileReference */
        foreach ($downloadRecord['files'] as $key => $fileReference) {
            $fileDescription = $fileReference->getTitle();
            if (empty($fileDescription)) {
                // Set fileDescription as configured by Type
                switch ($downloadDescriptionType) {
                    case 1:
                        $fileDescription = $fileReference->getNameWithoutExtension() . '.' . $fileReference->getExtension();
                        break;
                    case 2:
                        $fileDescription = $fileReference->getNameWithoutExtension();
                        break;
                    case 3:
                        $fileDescription = $fileReference->getExtension();
                        break;
                }
            }

            // Render DownloadIcon
            if (empty($this->conf['downloadIcon'])) {
                // If DownloadIcon is not configured, we try to get Icon by file-ext
                try {
                    $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
                    $fileExtIcon = $iconFactory->getIconForResource(
                        $fileReference->getOriginalFile(),
                        Icon::SIZE_SMALL
                    )->render();
                } catch (\Exception $e) {
                    $fileExtIcon = sprintf(
                        '<img src="%s" alt="allgemeine Datei-Ikone" />&nbsp;',
                        PathUtility::getAbsoluteWebPath(
                            GeneralUtility::getFileAbsFileName($this->conf['missingDownloadIcon'])
                        )
                    );
                }
            } else {
                $fileExtIcon = sprintf(
                    '<img src="%s" alt="File-Icon" />&nbsp;',
                    PathUtility::getAbsoluteWebPath(
                        GeneralUtility::getFileAbsFileName($this->conf['downloadIcon'])
                    )
                );
            }

            // add the filesize block, if desired
            $formattedFileSize = '';
            if ($this->settings['showFileSize']) {
                $decimals = 2;
                if ($fileReference->getSize() < 1024) {
                    $decimals = 0;
                }
                $formattedFileSize = sprintf(
                    '&nbsp;(%s)',
                    $this->format_size($fileReference->getSize(), $decimals)
                );
            }

            // add the file date+time block, if desired
            $formattedFileMDate = '';
            if ($this->settings['showFileMDate']) {
                $dtf = $this->conf['datetimeformat'];
                if ($this->settings['showFileMDate'] === '1') {
                    $dtf = $this->conf['dateformat'];
                }
                if (empty($dtf)) {
                    $dtf = 'd.m.Y H:i';
                }

                $formattedFileDate = date($dtf, $fileReference->getModificationTime());
                $formattedFileMDate = sprintf(
                    '<dd>%s: %s</dd>',
                    LocalizationUtility::translate('fileMDate', 'kkDownloader'),
                    $formattedFileDate
                );
            }

            // render the LINK-Part:
            $content .= sprintf(
                '<dt>%s&nbsp;%s%s</dt>%s',
                $fileExtIcon,
                $this->pi_linkTP(
                    $fileDescription,
                    [
                        'download' => $fileReference->getName(),
                        'did' => $downloadRecord['uid']
                    ]
                ),
                $formattedFileSize,
                $formattedFileMDate
            );
        }

        return '<dl>' . $content . '</dl>';
    }

    /**
     * Get categories for download record
     *
     * @param int $downloadUid
     * @return string comma separated list of category titles
     */
    protected function getCategoriesAsString(int $downloadUid): string
    {
        $categories = [];
        $categoryRecords = $this->categoryRepository->getCategoriesByDownloadUid($downloadUid);
        foreach ($categoryRecords as $categoryRecord) {
            $categoryRecord = $this->recordOverlay($categoryRecord, 'sys_category');
            $categories[] = $categoryRecord['title'];
        }

        return implode(', ', $categories);
    }

    /**
     * Format FileSize
     *
     * @param int $size: size of file in bytes
     * @param int $round: filesize: true/false
     * @return string return formatted FileSize
     */
    protected function format_size(int $size, int $round = 0): string
    {
        //Size must be bytes!
        $sizes = [' Bytes', ' kB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB'];
        for ($i = 0; $size > 1024 && $i < count($sizes) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $round) . $sizes[$i];
    }

    /**
     * Get mime type of file
     *
     * @param string $file
     * @return string
     */
    protected function getMimeTypeOfFile(string $file): string
    {
        $mimeType = '';
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file);
        } else {
            // @ToDo: SF: Hopefully I will find a better method to extract the mimetype instead of using image functions
            $imageInfos = getimagesize($file);
            if (array_key_exists(2, $imageInfos)) {
                $mimeType = image_type_to_mime_type($imageInfos[2]);
            }
        }

        return $mimeType ?: 'application/octet-stream';
    }

    /**
     * Start downloading the file
     *
     * @param string $filename
     * @param int $downloadUid
     */
    protected function startDownload(string $filename, int $downloadUid)
    {
        $downloadRecord = $this->downloadRepository->getDownloadByUid($downloadUid);

        /** @var FileReference $fileReference */
        foreach ($downloadRecord['files'] as $fileReference) {
            if ($fileReference->getName() === $filename) {
                // SF: Update to streamFile when removing TYPO3 8 compatibility
                $fileReference->getStorage()->dumpFileContents($fileReference->getOriginalFile(), true);

                $this->downloadRepository->updateImageRecordAfterDownload($downloadRecord);

                exit;
            }
        }

        exit;
    }

    /**
     * Add variables to view
     *
     * @param StandaloneView $view
     */
    protected function addPageBrowserSettingsToView(StandaloneView $view)
    {
        $amountOfDownloads = $this->internal['res_count'];
        $beginAt = (int)$this->piVars['pointer'] * $this->internal['results_at_a_time'];

        // Make Next link
        if ($amountOfDownloads > $beginAt + $this->internal['results_at_a_time']) {
            $next = ($beginAt + $this->internal['results_at_a_time'] > $amountOfDownloads) ? $amountOfDownloads - $this->internal['results_at_a_time']:$beginAt + $this->internal['results_at_a_time'];
            $next = (int)($next / $this->internal['results_at_a_time']);
            $params = ['pointer' => $next];
            $next_link = $this->pi_linkTP_keepPIvars(
                LocalizationUtility::translate('pi_list_browseresults_next', 'kkDownloader'),
                $params
            );
            $view->assign('linkNext', $this->cObj->stdWrap($next_link, $this->conf['pageBrowser.']['next_stdWrap.']));
        }

        // Make Previous link
        if ($beginAt) {
            $prev = ($beginAt - $this->internal['results_at_a_time'] < 0)?0:$beginAt - $this->internal['results_at_a_time'];
            $prev = (int)($prev / $this->internal['results_at_a_time']);
            $params = ['pointer' => $prev];
            $prev_link = $this->pi_linkTP_keepPIvars(
                LocalizationUtility::translate('pi_list_browseresults_prev', 'kkDownloader'),
                $params
            );
            $view->assign('linkPrev', $this->cObj->stdWrap($prev_link, $this->conf['pageBrowser.']['previous_stdWrap.']));
        }
        $pages = ceil($amountOfDownloads / $this->internal['results_at_a_time']);
        $actualPage = floor($beginAt / $this->internal['results_at_a_time']);

        if (ceil($actualPage - $this->internal['maxPages']/2) > 0) {
            $firstPage = ceil($actualPage - $this->internal['maxPages']/2);
            $addLast = 0;
        } else {
            $firstPage = 0;
            $addLast = floor(($this->internal['maxPages']/2)-$actualPage);
        }

        if (ceil($actualPage + $this->internal['maxPages']/2) <= $pages) {
            $lastPage = ceil($actualPage + $this->internal['maxPages'] / 2) > 0 ? ceil($actualPage + $this->internal['maxPages']/2) : 0;
            $subFirst = 0;
        } else {
            $lastPage = $pages;
            $subFirst = ceil($this->internal['maxPages']/2-($pages-$actualPage));
        }

        $firstPage = ($firstPage - $subFirst) > 0 ? ($firstPage - $subFirst) : $firstPage;
        $lastPage = ($lastPage + $addLast) <= $pages ? ($lastPage + $addLast) : $pages;
        $pages = '';
        for ($i = $firstPage; $i < $lastPage; $i++) {
            $item = (string)($i + 1);
            if ($this->conf['pageBrowser.']['showPBrowserText']) {
                $item = sprintf(
                    '%s %s',
                    LocalizationUtility::translate(
                        'pi_list_browseresults_page',
                        'kkDownloader'
                    ),
                    $item
                );
            }
            if (($beginAt >= $i * $this->internal['results_at_a_time']) && ($beginAt < $i * $this->internal['results_at_a_time'] + $this->internal['results_at_a_time'])) {
                $pages .= $this->cObj->stdWrap($item, $this->conf['pageBrowser.']['activepage_stdWrap.']) . ' ';
            } else {
                $params = ['pointer' => $i];
                $link = $this->pi_linkTP_keepPIvars($this->cObj->stdWrap($item, $this->conf['pageBrowser.']['pagelink_stdWrap.']), $params) . ' ';
                $pages .= $this->cObj->stdWrap($link, $this->conf['pageBrowser.']['page_stdWrap.']);
            }
        }
        $view->assign('pages', $pages);

        $end_at = ($beginAt + $this->internal['results_at_a_time']);

        if ($this->conf['pageBrowser.']['showResultCount']) {
            if ($this->internal['res_count']) {
                $startingSpanTag = '<span' . $this->pi_classParam('browsebox-strong') . '>';
                $closingSpanTag = '</span>';
                $pageResultCount = sprintf(
                    LocalizationUtility::translate('pi_list_browseresults_displays', 'kkDownloader'),
                    $startingSpanTag . ($this->internal['res_count'] > 0 ? ($beginAt + 1) : 0) . $closingSpanTag,
                    $startingSpanTag . (min([$this->internal['res_count'], $end_at])) . $closingSpanTag,
                    $startingSpanTag . $this->internal['res_count'] . $closingSpanTag
                );
            } else {
                $pageResultCount = LocalizationUtility::translate('pi_list_browseresults_noResults', 'kkDownloader');
            }
            $view->assign('resultCount', $pageResultCount);
        }
    }

    protected function getView()
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);

        try {
            // needed for f:translate
            $view->getRequest()->setControllerExtensionName('kkDownloader');
            // needed as identifier part for FlashMessageService
            $view->getRequest()->setPluginName('Pi1');
        } catch (InvalidExtensionNameException $e) {
        }

        return $view;
    }

    protected function recordOverlay(array $row, string $tableName)
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);

        // Workspace overlay
        $pageRepository->versionOL($tableName, $row);

        // Language overlay
        if (
            isset($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'])
            && $row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']] > 0
        ) {
            //force overlay by faking default language record, as getRecordOverlay can only handle default language records
            $row['uid'] = $row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']];
            $row[$GLOBALS['TCA'][$tableName]['ctrl']['languageField']] = 0;
        }

        return $pageRepository->getRecordOverlay(
            $tableName,
            $row,
            $this->languageUid,
            (string)$this->languageOverlayMode
        );
    }

    /**
     * Creates a Message object and adds it to the FlashMessageQueue.
     *
     * @param string $messageBody The message
     * @param string $messageTitle Optional message title
     * @param int $severity Optional severity, must be one of \TYPO3\CMS\Core\Messaging\FlashMessage constants
     * @param bool $storeInSession Optional, defines whether the message should be stored in the session (default) or not
     * @throws \InvalidArgumentException if the message body is no string
     * @see \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    public function addFlashMessage($messageBody, $messageTitle = '', $severity = \TYPO3\CMS\Core\Messaging\AbstractMessage::OK, $storeInSession = true)
    {
        if (!is_string($messageBody)) {
            throw new \InvalidArgumentException('The message body must be of type string, "' . gettype($messageBody) . '" given.', 1243258395);
        }
        /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
        $flashMessage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Messaging\FlashMessage::class,
            (string)$messageBody,
            (string)$messageTitle,
            $severity,
            $storeInSession
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('extbase.flashmessages.tx_kkdownloader_pi1');
        $flashMessageQueue->enqueue($flashMessage);
    }

    /**
     * @return mixed|\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
