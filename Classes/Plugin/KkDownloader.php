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
use JWeiland\KkDownloader\Traits\TypoScriptFrontendControllerTrait;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
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
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * This is the main entrypoint of kk_downloader.
 * It is based on the old pi_base class system of TYPO3.
 */
class KkDownloader extends AbstractPlugin
{
    use TypoScriptFrontendControllerTrait;

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
     */
    public string $extPath = 'typo3conf/ext/kk_downloader/';

    /**
     * The extension key
     *
     * @var string
     */
    public $extKey = 'kk_downloader';

    public string $defaultTemplate = 'EXT:kk_downloader/Resources/Private/Templates/MainTemplate.html';

    /**
     * @var string[]|null[]|int[]
     */
    public $internal = [];

    /**
     * Path to download ($_GET)
     */
    protected string $download = '';

    protected int $did = 0;

    /**
     * UID of download to show on detail page
     */
    protected int $uidOfDownload = 0;

    /**
     * Contains settings of FlexForm
     */
    protected array $settings = [];

    protected DownloadRepository $downloadRepository;

    protected CategoryRepository $categoryRepository;

    /**
     * @var MarkerBasedTemplateService
     */
    protected $templateService;

    public function __construct(
        CategoryRepository $categoryRepository,
        DownloadRepository $downloadRepository,
        MarkerBasedTemplateService $markerBasedTemplateService,
        TypoScriptFrontendController $frontendController,
        $_ = null
    ) {
        parent::__construct($_, $frontendController);

        $this->categoryRepository = $categoryRepository;
        $this->downloadRepository = $downloadRepository;
        $this->templateService = $markerBasedTemplateService;
    }

    /**
     * The main method of the PlugIn
     *
     * @param string $content The PlugIn content
     * @param array $conf The PlugIn configuration
     * @return string The content that is displayed on the website
     */
    public function main(string $content, array $conf): string
    {
        $this->conf = $conf; // Storing configuration as a member var
        $this->pi_loadLL(); // Loading language-labels
        $this->pi_setPiVarDefaults(); // Set default piVars from TS

        $this->download = GeneralUtility::_GP('download') ?? '';
        $this->did = (int)(GeneralUtility::_GP('did') ?? 0);
        $this->uidOfDownload = (int)($this->piVars['uid'] ?? 0);

        $this->pi_initPIflexform(); // Init and get the flexform data of the plugin
        $this->settings = $this->getFlexFormSettings();

        // if a download has happened
        if ($this->download !== '') {
            $this->startDownload(basename($this->download), $this->did);
        }

        $this->internal['results_at_a_time'] = $this->getFlexFormValue('results_at_a_time');
        $this->internal['results_at_a_time'] = $this->internal['results_at_a_time'] > 0 ? (int)($this->internal['results_at_a_time']) : (int)($this->conf['results_at_a_time']);
        $this->internal['results_at_a_time'] = $this->internal['results_at_a_time'] > 0 ? $this->internal['results_at_a_time'] : 25;
        $this->internal['maxPages'] = $this->conf['pageBrowser.']['maxPages'] > 0 ? (int)($this->conf['pageBrowser.']['maxPages']) : 10;

        $templateFile = $this->getFlexFormValue('template_file') ?: $this->conf['templateFile'] ?: $this->defaultTemplate;

        $view = $this->getView();
        $view->setTemplatePathAndFilename($templateFile);
        if ($this->settings['whatToDisplay'] === 'SINGLE') {
            if ($this->uidOfDownload !== 0) {
                $downloadRecord = $this->downloadRepository->getDownloadByUid($this->uidOfDownload);
                if ($downloadRecord === []) {
                    $view->assign('download', '');
                } else {
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
                }
            } else {
                $this->addFlashMessage(
                    LocalizationUtility::translate('error.callSingleViewWithoutUid.description', 'kkDownloader'),
                    LocalizationUtility::translate('error.callSingleViewWithoutUid.title', 'kkDownloader'),
                    FlashMessage::ERROR
                );
            }
        } else {
            $storagePages = GeneralUtility::intExplode(
                ',',
                $this->cObj->data['pages'] ?: $this->conf['defaultDownloadPid'],
                true
            );

            $downloads = $this->downloadRepository->getDownloads(
                $storagePages,
                $this->settings['categoryUid'],
                $this->settings['orderBy'],
                $this->settings['orderDirection'],
                $this->internal['results_at_a_time'],
                (int)($this->piVars['pointer'] ?? 0) * ($this->internal['results_at_a_time'] ?? 0)
            );

            foreach ($downloads as &$downloadRecord) {
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
            $this->internal['res_count'] = $this->downloadRepository->countDownloads(
                $storagePages,
                $this->settings['categoryUid']
            );

            if (
                $this->internal['results_at_a_time'] > 0
                && $this->internal['res_count'] > $this->internal['results_at_a_time']
            ) {
                if (!$this->conf['pageBrowser.']['showPBrowserText']) {
                    $this->LOCAL_LANG[$this->LLkey]['pi_list_browseresults_page'] = '';
                }
                $this->addPageBrowserSettingsToView($view);
            } elseif ($this->conf['pageBrowser.']['showResultCount']) {
                $this->addPageBrowserSettingsToView($view);
            }
        }

        $view->assignMultiple([
            'settings' => $this->settings,
            'pidOfDetailPage' => $this->conf['singlePID'] ?: $this->getTypoScriptFrontendController()->id,
        ]);

        return $view->render();
    }

    protected function createPreviewImage(array $downloadRecord): string
    {
        $previewImageForDownload = '';

        if (
            $downloadRecord['imagepreview'] !== []
            && ($fileReference = reset($downloadRecord['imagepreview']))
            && $fileReference instanceof FileReference
        ) {
            // if download record contains a preview image
            $img = $this->conf['image.'];
            $img['file'] = $fileReference->getPublicUrl();
            $previewImageForDownload = $this->cObj->cObjGetSingle('IMAGE', $img);
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
            foreach ($downloadRecord['image'] as $fileReference) {
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

    /**
     * Load all FLEXFORM data fields into variables
     */
    protected function getFlexFormSettings(): array
    {
        $settings = [];
        $settings['categoryUid'] = (int)$this->getFlexFormValue('category');
        $settings['showCats'] = $this->getFlexFormValue('showCats');
        $settings['orderBy'] = $this->getFlexFormValue('orderby');
        $settings['orderDirection'] = $this->getFlexFormValue('ascdesc');
        $settings['orderDirection'] = $settings['orderDirection'] ?: 'ASC';
        $settings['showFileSize'] = $this->getFlexFormValue('filesize');
        $settings['showPagebrowser'] = (bool)$this->getFlexFormValue('showPagebrowser');
        $settings['showImagePreview'] = (bool)$this->getFlexFormValue('imagepreview');
        $settings['showDownloadsCount'] = (bool)$this->getFlexFormValue('downloads');
        $settings['showEditDate'] = $this->getFlexFormValue('showEditDate');
        $settings['showDateLastDownload'] = $this->getFlexFormValue('showDateLastDownload');
        $settings['showIPLastDownload'] = $this->getFlexFormValue('showIPLastDownload');
        $settings['showFileMDate'] = $this->getFlexFormValue('showFileMDate');
        $settings['whatToDisplay'] = $this->getFlexFormValue('what_to_display');

        // special handling for creation date
        $creationDateType = $this->getFlexFormValue('showCRDate');
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

    protected function getFlexFormValue(string $field, $sheet = 'sDEF'): string
    {
        return trim((string)$this->pi_getFFvalue($this->cObj->data['pi_flexform'], $field, $sheet));
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
        foreach ($downloadRecord['image'] as $fileReference) {
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
                $fileExtension = strtolower($fileReference->getExtension());
                $fileExtension = $fileExtension === 'jpeg' ? 'jpg' : $fileExtension;
                $iconPath = GeneralUtility::getFileAbsFileName(sprintf(
                    'EXT:%s/Resources/Public/Icons/FileIcons/%s.gif',
                    'frontend',
                    $fileExtension
                ));

                if (is_file($iconPath)) {
                    $fileExtIcon = sprintf(
                        '<img src="%s" alt="%s-file" />&nbsp;',
                        PathUtility::getAbsoluteWebPath($iconPath),
                        $fileReference->getExtension()
                    );
                } else {
                    $fileExtIcon = sprintf(
                        '<img src="%s" alt="Fallback icon for files with unknown file extension" />&nbsp;',
                        PathUtility::getAbsoluteWebPath(
                            GeneralUtility::getFileAbsFileName($this->conf['missingDownloadIcon'])
                        )
                    );
                }
            } else {
                $fileExtIcon = sprintf(
                    '<img src="%s" alt="Default download icon" />&nbsp;',
                    PathUtility::getAbsoluteWebPath(
                        GeneralUtility::getFileAbsFileName($this->conf['downloadIcon'])
                    )
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
                        'did' => $downloadRecord['uid'],
                    ]
                ),
                $this->getFormattedFilesize($fileReference->getSize()),
                $formattedFileMDate
            );
        }

        return '<dl>' . $content . '</dl>';
    }

    protected function getFormattedFilesize(int $filesize): string
    {
        if (!$this->settings['showFileSize']) {
            return '';
        }

        return sprintf(
            '&nbsp;(%s)',
            GeneralUtility::formatSize(
                $filesize,
                ' Bytes| kB| MB| GB| TB| PB| EB| ZB| YB',
                1024
            )
        );
    }

    /**
     * Get categories for download record
     *
     * @return string comma separated list of category titles
     */
    protected function getCategoriesAsString(int $downloadUid): string
    {
        $categories = [];
        $categoryRecords = $this->categoryRepository->getCategoriesByDownloadUid($downloadUid);
        foreach ($categoryRecords as $categoryRecord) {
            $categories[] = $categoryRecord['title'];
        }

        return implode(', ', $categories);
    }

    protected function startDownload(string $filename, int $downloadUid): void
    {
        $downloadRecord = $this->downloadRepository->getDownloadByUid($downloadUid);

        /** @var FileReference $fileReference */
        foreach ($downloadRecord['image'] as $fileReference) {
            if ($fileReference->getName() === $filename) {
                $this->downloadRepository->updateImageRecordAfterDownload($downloadRecord);

                throw new ImmediateResponseException(
                    $fileReference->getStorage()->streamFile($fileReference->getOriginalFile(), true),
                    1636732392
                );
            }
        }

        exit;
    }

    /**
     * Add variables to view
     */
    protected function addPageBrowserSettingsToView(StandaloneView $view): void
    {
        $amountOfDownloads = $this->internal['res_count'];
        $beginAt = (int)($this->piVars['pointer'] ?? 0) * ($this->internal['results_at_a_time'] ?? 0);

        // Make Next link
        if ($amountOfDownloads > $beginAt + $this->internal['results_at_a_time']) {
            $next = ($beginAt + $this->internal['results_at_a_time'] > $amountOfDownloads) ? $amountOfDownloads - $this->internal['results_at_a_time'] : $beginAt + $this->internal['results_at_a_time'];
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
            $prev = ($beginAt - $this->internal['results_at_a_time'] < 0) ? 0 : $beginAt - $this->internal['results_at_a_time'];
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

        if (ceil($actualPage - $this->internal['maxPages'] / 2) > 0) {
            $firstPage = ceil($actualPage - $this->internal['maxPages'] / 2);
            $addLast = 0;
        } else {
            $firstPage = 0;
            $addLast = floor(($this->internal['maxPages'] / 2) - $actualPage);
        }

        if (ceil($actualPage + $this->internal['maxPages'] / 2) <= $pages) {
            $lastPage = ceil($actualPage + $this->internal['maxPages'] / 2) > 0 ? ceil($actualPage + $this->internal['maxPages'] / 2) : 0;
            $subFirst = 0;
        } else {
            $lastPage = $pages;
            $subFirst = ceil($this->internal['maxPages'] / 2 - ($pages - $actualPage));
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

    protected function getView(): StandaloneView
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);

        try {
            // needed for f:translate
            $view->getRequest()->setControllerExtensionName('kkDownloader');
            // needed as identifier part for FlashMessageService
            $view->getRequest()->setPluginName('Pi1');
        } catch (InvalidExtensionNameException $invalidExtensionNameException) {
        }

        return $view;
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
    public function addFlashMessage(
        string $messageBody,
        string $messageTitle = '',
        int $severity = AbstractMessage::OK,
        bool $storeInSession = true
    ): void {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $messageBody,
            $messageTitle,
            $severity,
            $storeInSession
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('extbase.flashmessages.tx_kkdownloader_pi1');
        $flashMessageQueue->enqueue($flashMessage);
    }
}
