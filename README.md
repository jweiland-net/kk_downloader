[![Latest Stable Version](https://poser.pugx.org/jweiland/kk-downloader/v/stable.svg)](https://extensions.typo3.org/extension/kk_downloader/)
[![TYPO3 11](https://img.shields.io/badge/TYPO3-11-orange.svg?style=flat-square)](https://get.typo3.org/version/11)
[![TYPO3 10](https://img.shields.io/badge/TYPO3-10-orange.svg?style=flat-square)](https://get.typo3.org/version/10)
[![Total Downloads](https://poser.pugx.org/jweiland/kk-downloader/d/total.svg)](https://packagist.org/packages/jweiland/kk_downloader)
[![Monthly Downloads](https://poser.pugx.org/jweiland/kk-downloader/d/monthly)](https://packagist.org/packages/jweiland/kk_downloader)
![Build Status](https://github.com/jweiland-net/kk_downloader/workflows/CI/badge.svg)

# kk_downloader

KK Downloader is a TYPO3 extensions which you can use as
Download system with counter, simple category management, sorting criteria and page browsing in the LIST-view.

# New Extension Maintainer

Since 05.05.2019 we (jweiland.net) have taken over the extension key `kk_downloader` and updated this extension a lot.

Many thanks to Kurt Kunig for his great work.

# Upgrade

* We have moved the static extension TS-Template to new location: `Configuration/TypoScript`. That's why you have to
  set this TS-Template again in your TS-Template record.
* We have changed Marker-based HTML-Template to Fluid. Please check your Template-Path and the Template itself.
  * In SingleView you can access download-record with `{download}`
  * In ListView you can access download-records with `{downloads}`
  * You have access to all FlexForm Settings with `{settings}`
  * We don't use extbase. So no lowerCamelCase available. Please use `ip_last_download` instead of `ipLastDownload`
* Go to InstallTool and start DB compare. We have changed column `clicks` to INT(10)
* We have changed localization handling of kk_downloader completely and make use of Core API now. So please check, if
  everything is still working for you.
* We have changed handling of TS-setting `downloadIcon`. If a path is set, it will be used for all download files.
  If it is not set (default) we will use TYPO3 internal icons to show file ext related icons. It's not possible
  to set downloadIcon to a Path anymore.
* Please check `Documentation/ChangeLog/Index.rst` to see all changes.
