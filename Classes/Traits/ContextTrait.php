<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/kk-downloader.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\KkDownloader\Traits;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Context trait to retrieve various values from TYPO3 aspects
 */
trait ContextTrait
{
    protected function getLanguageUid(): int
    {
        if ($this->getContext()->hasAspect('language')) {
            try {
                return $this->getContext()->getPropertyFromAspect('language', 'contentId');
            } catch (AspectNotFoundException $e) {
            }
        }

        return 0;
    }

    protected function getLanguageOverlayMode(): string
    {
        if ($this->getContext()->hasAspect('language')) {
            try {
                return $this->getContext()->getPropertyFromAspect('language', 'legacyOverlayType');
            } catch (AspectNotFoundException $e) {
            }
        }

        return '';
    }

    protected function getContext(): Context
    {
        return GeneralUtility::makeInstance(Context::class);
    }
}
