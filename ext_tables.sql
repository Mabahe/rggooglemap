#
# Table structure for table 'tt_address'
#
CREATE TABLE tt_address (
    tx_rggooglemap_lng tinytext NOT NULL,
    tx_rggooglemap_lat tinytext NOT NULL,
    tx_rggooglemap_display tinyint(3) DEFAULT '0' NOT NULL,
    tx_rggooglemap_cat int(11) DEFAULT '0' NOT NULL,
    tx_rggooglemap_tab int(11) DEFAULT '0' NOT NULL,
    tx_rggooglemap_cat2 tinytext NOT NULL,
    tx_rggooglemap_ce blob NOT NULL,
);





#
# Table structure for table 'tx_rggooglemap_cat'
#
CREATE TABLE tx_rggooglemap_cat (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
  title tinytext NOT NULL,
  descr text NOT NULL,
  color tinytext NOT NULL,
  image blob NOT NULL,
  tabprefix tinytext NOT NULL,
	parent_uid int(11) unsigned DEFAULT '0' NOT NULL,
      
  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tx_rggooglemap_generic'
#
#CREATE TABLE tx_rggooglemap_generic (
#    uid int(11) NOT NULL auto_increment,
#    pid int(11) DEFAULT '0' NOT NULL,
#    tstamp int(11) DEFAULT '0' NOT NULL,
#    crdate int(11) DEFAULT '0' NOT NULL,
#    cruser_id int(11) DEFAULT '0' NOT NULL,
#    deleted tinyint(4) DEFAULT '0' NOT NULL,
#    hidden tinyint(4) DEFAULT '0' NOT NULL,
#    tablename tinytext NOT NULL,
#    mapping text NOT NULL,
#    
#    PRIMARY KEY (uid),
#    KEY parent (pid)
#);
