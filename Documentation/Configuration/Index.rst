.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

**plugin_tx_kkdownloader_pi1.**

.. _configuration-typoscript:

TypoScript Setup Reference
==========================

.. container:: ts-properties

   =========================== ===================================== ====================
   Property                    Data type                             Default
   =========================== ===================================== =========================================
   linkdescription_            Integer                               1
   downloadIcon_               String                                1
   missingDownloadIcon_        String                                EXT:kk_downloader/Resources/Public/Icons/MissingDownload.gif
   templateFile_               String                                EXT:kk_downloader/Resources/Private/Templates/MainTemplate.html
   defaultDownloadPid_         Integer/String                        all
   singlePID_                  Integer                               empty
   displayCreationDate_        Removed                               Removed
   dateformat_                 String                                d.m.Y
   datetimeformat_             String                                d.m.Y H:i
   imageDistance_              String                                5
   image_                      ->imgResource/->stdWrap
   results_at_a_time_          Integer                               25
   pageBrowser_                Array
   =========================== ===================================== =========================================


Property details
================

.. only:: html

   .. contents::
      :local:
      :depth: 1


.. _linkdescription:

linkdescription
---------------

If no linktitle set:

1 = filename.fileextension,
2 = filename
3 = fileextension


.. _downloadIcon:

downloadIcon
------------

Path to download icon (complete – e.g.

downloadIcon = EXT:kk_downloader/pi1/images/downLoad.gif)

or

Path to the folder, where the fileicons are:
(e.g. downloadIcon = typo3/gfx/fileicons/) This
path must end with a Slash! Result: the
corrosponding icons to the fileextension will be
shown, just like “pdf.gif” e.g. - Kurt Kunig


.. _missingDownloadIcon:

missingDownloadIcon
-------------------

If the path is set typo3/gfx/fileicons/ you can set a default download icon
if a icon is missing (e.g. for rar files)


.. _templateFile:

templateFile
------------

Path to template file


.. _defaultDownloadPid:

defaultDownloadPid
------------------

# PID of the general download folder (if no page-“starting-point” is set)

Integer: PID of a page-object
String: "all" -> ALL downloads will be selected


.. _singlePID:

singlePID
---------

Pid of the detail page


.. _displayCreationDate:

displayCreationDate
-------------------

Property removed.
Please use `showCRDate` in FlexForm.


.. _dateformat:

dateformat
----------

Here you can set the date formatting for the template-marker: ###DATE###.

**Example:**

plugin.tx_kkdownloader_pi1 {
  dateformat= l, d.m.Y
}

This will display the date in content elements like this: "Monday, 31.03.2008".


.. _datetimeformat:

datetimeformat
--------------

e.g. used for formatting the date-output of the download-file

datetimeformat = d.m.Y H:i


.. _imageDistance:

imageDistance
-------------

Distance between Image and text


.. _image:

image
-----

Configures the image(s) in download items.

**Example:**

image {
  file.maxW = 140
  imageLinkWrap = 0
  imageLinkWrap {
    enable = 1
    bodyTag = <BODY bgColor=white>
    wrap = |
    width = 400m
    height = 400
    JSwindow = 1
    JSwindow.newWindow = 1
    JSwindow.expand = 17,20
  }
}


.. _results_at_a_time:

results_at_a_time
-----------------

limit of single items on one list-page used with LIST for page-browsing.
Can be overwritten in flexform.


.. _pageBrowser:

pageBrowser
-----------

pageBrowser.maxPages
####################

Default: 10

Maximum x pages will be shown

pageBrowser.showPBrowserText
############################

Default: true

should pagebrowser-text be shown

pageBrowser.showResultCount
###########################

Default: true

Should the list result (Item 1 to 5 of 23) be shown?

pageBrowser.activepage_stdWrap.wrap
###################################

Default: <strong>|</strong>

Wrapping the active page

pageBrowser.page_stdWrap.wrap
###################################

Default: |

Wrapping the active page

pageBrowser.pagelink_stdWrap.wrap
###################################

Default: |

Wrapping the active page

pageBrowser.previous_stdWrap.wrap
###################################

Default: |

Wrapping the active page

pageBrowser.next_stdWrap.wrap
###################################

Default: |

Wrapping the active page
