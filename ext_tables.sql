#
# Table structure for table 'tx_kkdownloader_images'
#
CREATE TABLE tx_kkdownloader_images
(
	name             tinytext,
	image            int(11) unsigned DEFAULT '0',
	imagepreview     int(11) unsigned DEFAULT '0',
	description      text,
	longdescription  text,
	clicks           int(10) DEFAULT '0' NOT NULL,
	last_downloaded  int(11) DEFAULT '0' NOT NULL,
	ip_last_download varchar(45) DEFAULT '' NOT NULL
);
