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

namespace JWeiland\KkDownloader\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Category extends AbstractEntity
{
    /**
     * @var string
     */
    protected $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
