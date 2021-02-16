#
# Table structure for table 'tx_kkdownloader_images'
#
CREATE TABLE tx_kkdownloader_images (
	name tinytext NOT NULL,
	image blob NOT NULL,
	imagepreview blob NOT NULL,
	description text NOT NULL,
	longdescription text NOT NULL,
	downloaddescription text NOT NULL,
	clicks int(10) DEFAULT '0' NOT NULL,
	cat tinytext NOT NULL,
	last_downloaded int(11) DEFAULT '0' NOT NULL,
	ip_last_download varchar(45) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_kkdownloader_cat'
#
CREATE TABLE tx_kkdownloader_cat (
	cat tinytext NOT NULL
);
