CREATE TABLE form_max_entries (
  max_ent_id smallint(5) NOT NULL auto_increment,
  max_ent_id_form smallint(5),
  max_ent_uid int(10),
  max_ent_entcount smallint(5),
  PRIMARY KEY (`max_ent_id`)
) TYPE=MyISAM;

CREATE TABLE form_reports (
  report_id smallint(5) NOT NULL auto_increment,
  report_name varchar(255) default NULL,
  report_id_form smallint(5),
  report_uid int(10),
  report_ispublished tinyint(1) NOT NULL default '0',
  report_groupids text NOT NULL,
  report_scope text NOT NULL,
  report_fields text NOT NULL,
  report_search_typeArray text NOT NULL,
  report_search_textArray text NOT NULL,
  report_andorArray text NOT NULL,
  report_calc_typeArray text NOT NULL,
  report_sort_orderArray text NOT NULL,
  report_ascdscArray text NOT NULL,
  report_globalandor varchar(10) default NULL,
  PRIMARY KEY (`report_id`)
) TYPE=MyISAM;

CREATE TABLE form_chains (
  chain_id smallint(5) NOT NULL auto_increment,
  chain_name varchar(255) default NULL,
  chain_startform smallint(5),
  chain_allforms varchar(255) default NULL,
  PRIMARY KEY (`chain_id`)
) TYPE=MyISAM;

CREATE TABLE form_chains_entries (
  chain_entry_id smallint(5) NOT NULL auto_increment,
  chain_id smallint(5),
  chain_reqs varchar(255) default NULL,
  PRIMARY KEY (`chain_entry_id`)
) TYPE=MyISAM;

CREATE TABLE form_id (
  id_form smallint(5) NOT NULL auto_increment,
  desc_form varchar(60) NOT NULL default '',
  admin varchar(5) default NULL,
  groupe varchar(255) default NULL,
  email varchar(255) default NULL,
  expe varchar(5) default NULL,
  singleentry varchar(5) default NULL,
  groupscope varchar(5) default NULL,
  headerlist text NOT NULL,
  showviewentries varchar(5) default NULL,
  maxentries smallint(5) NOT NULL default '0',
  even varchar(255) default NULL,
  odd varchar(255) default NULL, 
  PRIMARY KEY  (`id_form`),
  UNIQUE KEY `` (`desc_form`)
) TYPE=MyISAM;

CREATE TABLE form (
  id_form int(5) NOT NULL default '0',
  ele_id smallint(5) unsigned NOT NULL auto_increment,
  ele_type varchar(10) NOT NULL default '',
  ele_caption varchar(255) NOT NULL default '',
  ele_order smallint(2) NOT NULL default '0',
  ele_req tinyint(1) NOT NULL default '1',
  ele_value text NOT NULL,
  ele_display tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`ele_id`),
  KEY `ele_display` (`ele_display`),
  KEY `ele_order` (`ele_order`)
) TYPE=MyISAM;

CREATE TABLE form_menu (
  menuid int(4) unsigned NOT NULL auto_increment,
  position int(4) unsigned NOT NULL,
  indent int(2) unsigned NOT NULL default '0',
  itemname varchar(60) NOT NULL default '',
  margintop varchar(12) NOT NULL default '0px',
  marginbottom varchar(12) NOT NULL default '0px',
  itemurl varchar(255) NOT NULL default '',
  bold tinyint(1) NOT NULL default '0',
  mainmenu tinyint(1) NOT NULL default '0',
  membersonly tinyint(1) NOT NULL default '1',
  status tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (menuid),
  KEY idxmymenustatus (status)
) TYPE=MyISAM;

CREATE TABLE form_form (
  id_form int(5) NOT NULL default '0',
  id_req smallint(5) ,
  ele_id smallint(5) unsigned NOT NULL auto_increment,
  ele_type varchar(10) NOT NULL default '',
  ele_caption varchar(255) NOT NULL default '',
  ele_value text NOT NULL,
  date Date NOT NULL default '2004-06-03',
  uid int(10) default '0',
  proxyid int(10) NULL ,
  PRIMARY KEY  (`ele_id`),
  KEY `ele_id` (`ele_id`)
) TYPE=MyISAM;