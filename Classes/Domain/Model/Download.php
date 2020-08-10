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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Download extends AbstractEntity
{
    /**
     * @var \DateTime
     */
    protected $crdate;

    /**
     * @var \DateTime
     */
    protected $tstamp;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $filesDescription = '';

    /**
     * @var string
     */
    protected $longDescription = '';

    /**
     * @var int
     */
    protected $lastDownloaded = 0;

    /**
     * @var string
     */
    protected $ipLastDownloaded = '';

    /**
     * @var int
     */
    protected $clicks = 0;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $files;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $preview;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\KkDownloader\Domain\Model\Category>
     */
    protected $categories;

    public function __construct()
    {
        $this->files = new ObjectStorage();
        $this->preview = new ObjectStorage();
        $this->categories = new ObjectStorage();
    }

    public function getCrdate(): \DateTime
    {
        return $this->crdate;
    }

    public function setCrdate(\DateTime $crdate): void
    {
        $this->crdate = $crdate;
    }

    public function getTstamp(): \DateTime
    {
        return $this->tstamp;
    }

    public function setTstamp(\DateTime $tstamp): void
    {
        $this->tstamp = $tstamp;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getFilesDescription(): string
    {
        return $this->filesDescription;
    }

    public function setFilesDescription(string $filesDescription): void
    {
        $this->filesDescription = $filesDescription;
    }

    public function getLongDescription(): string
    {
        return $this->longDescription;
    }

    public function setLongDescription(string $longDescription): void
    {
        $this->longDescription = $longDescription;
    }

    public function getLastDownloaded(): int
    {
        return $this->lastDownloaded;
    }

    public function setLastDownloaded(int $lastDownloaded): void
    {
        $this->lastDownloaded = $lastDownloaded;
    }

    public function getIpLastDownloaded(): string
    {
        return $this->ipLastDownloaded;
    }

    public function setIpLastDownloaded(string $ipLastDownloaded): void
    {
        $this->ipLastDownloaded = $ipLastDownloaded;
    }

    public function getClicks(): int
    {
        return $this->clicks;
    }

    public function setClicks(int $clicks): void
    {
        $this->clicks = $clicks;
    }

    public function getFiles(): ObjectStorage
    {
        return $this->files;
    }

    public function setFiles(ObjectStorage $files): void
    {
        $this->files = $files;
    }

    public function getPreview(): ObjectStorage
    {
        return $this->preview;
    }

    public function setPreview(ObjectStorage $preview): void
    {
        $this->preview = $preview;
    }

    /**
     * @return ObjectStorage|Category[]
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    public function getCategoriesAsString(): string
    {
        $categories = [];
        foreach ($this->getCategories() as $category) {
            $categories[] = $category->getTitle();
        }
        return implode(', ', $categories);
    }

    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }
}
