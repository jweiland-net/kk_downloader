..  include:: /Includes.rst.txt


========
Updating
========

If you update `kk_downloader` to a newer version, please read this
section carefully!

Update to Version 7.0.0
=======================

We have removed TYPO3 9.6 compatibility.
TYPO3 11.5 compatibility added.

The selectbox side-by-side for `what_to_display` in plugin settings has never
ever worked as this plugin can only display one view at once. We have migrated
that box to a single selectbox. Please open your plugins, check and correct the
value and store the plugin.

We have removed the `ext_typoscript_setup.typoscript`. Please use a
TypoScript +ext Template to add kk_downloader TypoScript where needed.

It is not allowed to create download records on default pages anymore. Please
mark page record as storage folder and store download records there.

We have removed the default UserTSConfig:
`options.saveDocNew.tx_kkdownloader_images`. In newer TYPO3 versions a "new"
button will already be shown in list-module.

Update to Version 6.0.0
=======================

We have removed TYPO3 8.7 compatibility.
TYPO3 10.4 compatibility added.

Please click `Flush caches` in Installtool as we have added some more PHP
classes and changed TCA structure.

We have removed the possibility to reduce category items in selector of
FlexForm with help of `TCEFORM.pages._STORAGE_PID`. Please use following
PageTSConfig instead:
`TCEFORM.tt_content.pi_flexform.kkdownloader_pi1.sDEF.category.PAGE_TSCONFIG_IDLIST = 23,24,25`

We have renamed the field `dynField` in FlexForm to `category`. Please execute
the according UpgradeWizard.


Update to Version 5.2.0
=======================

With TYPO3 9.5 the compatibility for files and file references in TCA group
fields was deprecated and will be
removed in TYPO3 10.0. See here:
`Deprecation: #86406 - TCA type group internal_type file and file_reference <https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Deprecation-86406-TCATypeGroupInternal_typeFileAndFile_reference.html#deprecation-86406-tca-type-group-internal-type-file-and-file-reference>`__

As kk_downloader uses this system a lot we have added two new UpdateWizards:

*   UpdateWizard to migrate your downloadable files from `/uploads/kk_downloader/` to `fileadmin/_migrated/kk_downloader`.
*   UpdateWizard to migrate imagepreview files from `/uploads/kk_downloader/` to `fileadmin/_migrated/kk_downloader`.

As all values from column `downloaddescription` were migrated into the file
reference of your downloaded files, we have removed this column from
table `tx_kkdownloader_images`.

..  hint::

    The UpdateWizards checks each file for existence in full `fileadmin/`
    folder. If found, it will update the relation automatically. So it may
    happen that `fileadmin/_migrated/kk_downloader/` folder will contain less
    files then in `uploads/kk_downloader/`.


Update to Version 5.1.0
=======================

With TYPO3 6.0 we got a new possibility to manage categories. So there is no
need to keep our own system for kk_downloader categories.

Please execute UpdateWizard `[kk_downloader] Migrate KK categories to
TYPO3 sys_categories` to migrate the categories. This Wizard also updates the
category relations to your download records and updates the selected categories
in all of your FlexForms.


Update to Version 5.0.0
=======================

This version is not TYPO3 7.6 compatible anymore!
We have added TYPO3 9.6 compatibility.

We have moved TS template from `styles/css` to `Configuration/TypoScript`. As
there is no UpdateWizard available you have to re-add the TS template of
`kk_downloader` to your system.

You will find the new default fluid template here:
`EXT:kk_downloader/Resources/Private/Templates/MainTemplate.html`.
If you have overwritten this file, please adopt changes into your own template.
All marker based variables have been replaced with fluid variables!

A lot of TCA was moved to more accepted folders. Please clear system cache
after upgrading.


Update to Version 4.0.0
=======================

Was never released

PHP classes of plugin were moved from `/pi` directory to `Classes/Plugin`.


Update to Version 3.0.1
=======================

jweiland.net has taken over the extension key.
