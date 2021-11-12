.. include:: ../Includes.txt


.. _changelog:

=========
ChangeLog
=========

**Version 6.0.0**

- Remove TYPO3 8.7 compatibility
- Add TYPO3 10 compatibility
- Replace git icons with svg
- Restructure TCA fields
- Repair record overlay
- Remove TYPO3 columns from ext_tables.sql

**Version 5.2.2**

- Change group type of template file in FlexForm to input type

**Version 5.2.1**

- Add renderType to FlexForm
- add method getFlexFormValue to prevent return of NULL values

**Version 5.2.0**

- Migrate previewimage to FAL (Please execute UpdateWizard)
- Migrate download files to FAL (Please execute UpdateWizard)
- Repair date of last download in view
- Rename a lot of variables/methods for better understanding
- Repair workspace for category records

**Version 5.1.0**

- Use categories of sys_category instead of our own cat table. Please use UpgradeWizard for migration

**Version 5.0.1**

- Typo in orderBy of DownloadRepository
- Convert Documentation to RST format only. No Update!

**Version 5.0.0**

- Extension key kk_downloader was transferred to jweiland.net
- Update version number to 5.0.0
- Change Template to Fluid
- Move marker based logic to Fluid-Template
- Change default template to EXT:kk_downloader/Resources/Private/Templates/MainTemplate.html
- Collect all FlexForm configuration in $this->settings
- Assign downloads variable to ListView
- Assign download variable to SingleView
- $this->settings is available in Fluid, too
- Removed all PHP code for translation handling and rebuild it with TYPO3 API
- Moved all TCA from tca.php into Configuration/TCA
- Moved all TCA changing PHP into Configuration/TCA/Overrides/*
- Moved pi-class to Classes/Plugin
- Moved additional fields UserFunc class to Classes/UserFunc
- Changed yes/no selectboxes in FlexForm to Checkboxes
- Change PHP code to be PSR-2 compatible
- Add strict_types where possible
- Add composer.json. So it's now available over packagist, too.
- Set TYPO3 compatibility to 8.7 and 9.5. Yes, we have removed compatibility to TYPO3 7.6
- Changed column "clicks" from tinytext to int(10)
- Moved all DB Queries to non-extbase-based Repositories
- Changed all DB-Queries to Doctrine
- Add Namespacing and correct PHP DocHeaders
- Simplify many if-conditions (don't test on empty() and empty string)
- Removed all Debugging-Output. Please use xdebug or f:debug or Admin-Panel or...
- Change pi_getLL to LocalizationUtility to use new path of language files
- Change language files to XLF
- Divide Languagefile to locallang, locallang_db and FlexForm
- Change IMAGE() call to cObjGetSingle()
- Remove or change many deprecated code
- Create date with f:format.date in template directly
- Moved all Icons to Resources/Public/Icons
- Moved TS-Template from styles/css to Configuration/TypoScript
- Remove imageDistance, as the result "$imagewidth" was not used
- Change downloadIcon handling. If not set, use Icons of Core, else use Icon of DownloadIcon
