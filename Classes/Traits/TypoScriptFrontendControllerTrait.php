<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\Traits;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Trait to get the TypoScriptFrontendController
 */
trait TypoScriptFrontendControllerTrait
{
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return GeneralUtility::makeInstance(TypoScriptFrontendController::class);
    }
}
