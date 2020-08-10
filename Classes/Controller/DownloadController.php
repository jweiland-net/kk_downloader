<?php

declare(strict_types=1);

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

namespace JWeiland\KkDownloader\Controller;

use JWeiland\KkDownloader\Domain\Model\Download;
use JWeiland\KkDownloader\Domain\Repository\DownloadRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class DownloadController extends ActionController
{
    protected $downloadRepository;

    public function __construct(DownloadRepository $downloadRepository)
    {
        $this->downloadRepository = $downloadRepository;
    }

    public function listAction()
    {
        $this->view->assign('downloads', $this->downloadRepository->findAll());
    }

    /**
     * @param Download $download
     */
    public function showAction(Download $download)
    {
        $this->view->assign('download', $download);
    }
}
