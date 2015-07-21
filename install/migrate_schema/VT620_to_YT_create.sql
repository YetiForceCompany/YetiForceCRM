CREATE TABLE IF NOT EXISTS `vtiger_vendoraddress` (
  `vendorid` int(19) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel1b` varchar(255) DEFAULT NULL,
  `addresslevel1c` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel2b` varchar(255) DEFAULT NULL,
  `addresslevel2c` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel3b` varchar(255) DEFAULT NULL,
  `addresslevel3c` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel4b` varchar(255) DEFAULT NULL,
  `addresslevel4c` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel5b` varchar(255) DEFAULT NULL,
  `addresslevel5c` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel6b` varchar(255) DEFAULT NULL,
  `addresslevel6c` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel7b` varchar(255) DEFAULT NULL,
  `addresslevel7c` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `addresslevel8b` varchar(255) DEFAULT NULL,
  `addresslevel8c` varchar(255) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  `poboxb` varchar(50) DEFAULT NULL,
  `poboxc` varchar(50) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `buildingnumberb` varchar(100) DEFAULT NULL,
  `buildingnumberc` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `localnumberb` varchar(100) DEFAULT NULL,
  `localnumberc` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`vendorid`),
  CONSTRAINT `vtiger_vendoraddress_ibfk_1` FOREIGN KEY (`vendorid`) REFERENCES `vtiger_vendor` (`vendorid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vtiger_salesorderaddress` (
  `salesorderaddressid` int(19) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel1b` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel2b` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel3b` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel4b` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel5b` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel6b` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel7b` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `addresslevel8b` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `buildingnumberb` varchar(100) DEFAULT NULL,
  `localnumberb` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  `poboxb` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`salesorderaddressid`),
  CONSTRAINT `vtiger_salesorderaddress_ibfk_1` FOREIGN KEY (`salesorderaddressid`) REFERENCES `vtiger_salesorder` (`salesorderid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vtiger_quotesaddress` (
  `quoteaddressid` int(19) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel1b` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel2b` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel3b` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel4b` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel5b` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel6b` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel7b` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `addresslevel8b` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `buildingnumberb` varchar(100) DEFAULT NULL,
  `localnumberb` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  `poboxb` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`quoteaddressid`),
  CONSTRAINT `vtiger_quotesaddress_ibfk_1` FOREIGN KEY (`quoteaddressid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vtiger_purchaseorderaddress` (
  `purchaseorderaddressid` int(19) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel1b` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel2b` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel3b` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel4b` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel5b` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel6b` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel7b` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `addresslevel8b` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `buildingnumberb` varchar(100) DEFAULT NULL,
  `localnumberb` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  `poboxb` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`purchaseorderaddressid`),
  CONSTRAINT `vtiger_purchaseorderaddress_ibfk_1` FOREIGN KEY (`purchaseorderaddressid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vtiger_invoiceaddress` (
  `invoiceaddressid` int(19) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel1b` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel2b` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel3b` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel4b` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel5b` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel6b` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel7b` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `addresslevel8b` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `buildingnumberb` varchar(100) DEFAULT NULL,
  `localnumberb` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  `poboxb` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`invoiceaddressid`),
  CONSTRAINT `vtiger_invoiceaddress_ibfk_1` FOREIGN KEY (`invoiceaddressid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vtiger_accountaddress` (
  `accountaddressid` int(19) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel1b` varchar(255) DEFAULT NULL,
  `addresslevel1c` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel2b` varchar(255) DEFAULT NULL,
  `addresslevel2c` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel3b` varchar(255) DEFAULT NULL,
  `addresslevel3c` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel4b` varchar(255) DEFAULT NULL,
  `addresslevel4c` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel5b` varchar(255) DEFAULT NULL,
  `addresslevel5c` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel6b` varchar(255) DEFAULT NULL,
  `addresslevel6c` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel7b` varchar(255) DEFAULT NULL,
  `addresslevel7c` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `addresslevel8b` varchar(255) DEFAULT NULL,
  `addresslevel8c` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `buildingnumberb` varchar(100) DEFAULT NULL,
  `localnumberb` varchar(100) DEFAULT NULL,
  `buildingnumberc` varchar(100) DEFAULT NULL,
  `localnumberc` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  `poboxb` varchar(50) DEFAULT NULL,
  `poboxc` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`accountaddressid`),
  CONSTRAINT `vtiger_accountaddress_ibfk_1` FOREIGN KEY (`accountaddressid`) REFERENCES `vtiger_account` (`accountid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_ossmailscanner_config` (
  `conf_type` varchar(100) NOT NULL,
  `parameter` varchar(100) DEFAULT NULL,
  `value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_osspdf` (
  `osspdfid` int(11) NOT NULL DEFAULT '0',
  `oss_mod_no` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `moduleid` varchar(255) DEFAULT NULL,
  `osspdf_pdf_format` varchar(20) DEFAULT NULL,
  `osspdf_pdf_orientation` varchar(50) DEFAULT NULL,
  `content` text,
  `constraints` text,
  `filename` varchar(100) DEFAULT NULL,
  `left_margin` int(15) DEFAULT NULL,
  `right_margin` int(15) DEFAULT NULL,
  `top_margin` int(15) DEFAULT NULL,
  `bottom_margin` int(15) DEFAULT NULL,
  `osspdf_enable_footer` varchar(15) DEFAULT NULL,
  `osspdf_enable_header` varchar(15) DEFAULT NULL,
  `header_content` text,
  `footer_content` text,
  `osspdf_enable_numbering` varchar(15) DEFAULT NULL,
  `height_header` int(10) DEFAULT NULL,
  `height_footer` int(10) DEFAULT NULL,
  `selected` varchar(5) DEFAULT NULL,
  `osspdf_view` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`osspdfid`),
  CONSTRAINT `fk_1_vtiger_osspdf` FOREIGN KEY (`osspdfid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_osspdf_config` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `conf_id` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `ordering` int(11) NOT NULL,
  `display` int(3) NOT NULL,
  `type` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `vtiger_osspdf_constraints` (
  `id` int(19) NOT NULL AUTO_INCREMENT,
  `relid` int(19) NOT NULL,
  `fieldname` varchar(255) NOT NULL,
  `comparator` varchar(255) NOT NULL,
  `val` varchar(255) NOT NULL,
  `required` tinyint(19) NOT NULL,
  `field_type` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_osspdfcf` (
  `osspdfid` int(11) NOT NULL,
  PRIMARY KEY (`osspdfid`),
  CONSTRAINT `fk_1_vtiger_osspdfcf` FOREIGN KEY (`osspdfid`) REFERENCES `vtiger_osspdf` (`osspdfid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_outsourcedproducts` (
  `outsourcedproductsid` int(11) NOT NULL DEFAULT '0',
  `asset_no` varchar(32) DEFAULT NULL,
  `productname` varchar(255) DEFAULT NULL,
  `datesold` date DEFAULT NULL,
  `dateinservice` date DEFAULT NULL,
  `oproductstatus` varchar(255) DEFAULT NULL,
  `pscategory` varchar(255) DEFAULT '',
  `wherebought` varchar(255) DEFAULT '',
  `prodcount` varchar(255) DEFAULT '',
  `potential` int(19) DEFAULT NULL,
  `parent_id` int(19) DEFAULT NULL,
  PRIMARY KEY (`outsourcedproductsid`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `fk_1_vtiger_outsourcedproducts` FOREIGN KEY (`outsourcedproductsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_outsourcedproductscf` (
  `outsourcedproductsid` int(11) NOT NULL,
  PRIMARY KEY (`outsourcedproductsid`),
  CONSTRAINT `fk_1_vtiger_outsourcedproductscf` FOREIGN KEY (`outsourcedproductsid`) REFERENCES `vtiger_outsourcedproducts` (`outsourcedproductsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_password` (
  `type` varchar(20) NOT NULL,
  `val` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_passwords_config` (
  `pass_length_min` int(3) NOT NULL,
  `pass_length_max` int(3) NOT NULL,
  `pass_allow_chars` varchar(200) NOT NULL,
  `register_changes` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_osscosts` (
  `osscostsid` int(19) NOT NULL,
  `osscosts_no` varchar(30) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `parentid` int(19) DEFAULT NULL,
  `potentialid` int(19) DEFAULT NULL,
  `projectid` int(19) DEFAULT NULL,
  `ticketid` int(19) DEFAULT NULL,
  `relategid` int(19) DEFAULT NULL,
  `street` varchar(250) DEFAULT NULL,
  `code` varchar(40) DEFAULT NULL,
  `city` varchar(40) DEFAULT NULL,
  `country` varchar(40) DEFAULT NULL,
  `state` varchar(40) DEFAULT NULL,
  `total` decimal(25,8) DEFAULT NULL,
  `subtotal` decimal(25,8) DEFAULT NULL,
  `taxtype` varchar(25) DEFAULT NULL,
  `discount_percent` decimal(25,3) DEFAULT NULL,
  `discount_amount` decimal(25,8) DEFAULT NULL,
  `currency_id` int(19) NOT NULL DEFAULT '1',
  `conversion_rate` decimal(10,3) NOT NULL DEFAULT '1.000',
  `pre_tax_total` decimal(25,8) DEFAULT NULL,
  `total_purchase` decimal(13,2) DEFAULT NULL,
  `total_margin` decimal(13,2) DEFAULT NULL,
  `total_marginp` decimal(13,2) DEFAULT NULL,
  PRIMARY KEY (`osscostsid`),
  KEY `osscosts_parentid_idx` (`parentid`),
  KEY `osscosts_potentialid_idx` (`potentialid`),
  KEY `osscosts_projectid_idx` (`projectid`),
  KEY `osscosts_ticketid_idx` (`ticketid`),
  KEY `osscosts_relategid_idx` (`relategid`),
  CONSTRAINT `fk_1_vtiger_osscosts` FOREIGN KEY (`osscostsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
CREATE TABLE IF NOT EXISTS `vtiger_ossdocumentcontrol` (
  `ossdocumentcontrolid` int(19) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) DEFAULT NULL,
  `summary` varchar(255) NOT NULL,
  `doc_folder` int(19) NOT NULL,
  `doc_name` varchar(255) NOT NULL,
  `doc_request` tinyint(1) NOT NULL,
  `doc_order` int(19) NOT NULL,
  PRIMARY KEY (`ossdocumentcontrolid`),
  KEY `ossdocumentcontrolid` (`ossdocumentcontrolid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_ossdocumentcontrol_cnd` (
  `ossdocumentcontrol_cndid` int(19) NOT NULL AUTO_INCREMENT,
  `ossdocumentcontrolid` int(19) NOT NULL,
  `fieldname` varchar(255) NOT NULL,
  `comparator` varchar(255) NOT NULL,
  `val` varchar(255) DEFAULT NULL,
  `required` tinyint(19) NOT NULL,
  `field_type` varchar(100) NOT NULL,
  PRIMARY KEY (`ossdocumentcontrol_cndid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_ossmails_logs` (
  `id` int(19) NOT NULL AUTO_INCREMENT,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NULL DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `status` tinyint(3) DEFAULT NULL,
  `user` varchar(100) DEFAULT NULL,
  `count` int(10) DEFAULT NULL,
  `stop_user` varchar(100) DEFAULT NULL,
  `info` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `vtiger_ossmailscanner_folders_uid` (
  `user_id` int(10) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `folder` varchar(100) DEFAULT NULL,
  `uid` int(19) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_ossmailscanner_log_cron` (
  `id` int(19) NOT NULL AUTO_INCREMENT,
  `created_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `laststart` int(11) unsigned DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_ossmailview` (
  `ossmailviewid` int(19) NOT NULL,
  `ossmailview_no` varchar(50) DEFAULT NULL,
  `from_email` text,
  `to_email` text,
  `subject` text,
  `content` text,
  `cc_email` text,
  `bcc_email` text,
  `id` int(19) DEFAULT NULL,
  `mbox` varchar(100) DEFAULT NULL,
  `uid` varchar(150) DEFAULT NULL,
  `reply_to_email` text,
  `ossmailview_sendtype` varchar(30) DEFAULT NULL,
  `attachments_exist` varchar(3) DEFAULT '0',
  `rc_user` varchar(3) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  `from_id` int(19) DEFAULT NULL,
  `to_id` int(19) DEFAULT NULL,
  `orginal_mail` text,
  `verify` varchar(5) DEFAULT '0',
  `rel_mod` varchar(128) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`ossmailviewid`),
  KEY `id` (`id`),
  KEY `message_id` (`uid`),
  CONSTRAINT `fk_1_vtiger_ossmailview` FOREIGN KEY (`ossmailviewid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_ossmailview_files` (
  `ossmailviewid` int(19) NOT NULL,
  `documentsid` int(19) NOT NULL,
  `attachmentsid` int(19) NOT NULL,
  KEY `fk_1_vtiger_ossmailview_files` (`ossmailviewid`),
  KEY `documentsid` (`documentsid`),
  CONSTRAINT `fk_1_vtiger_ossmailview_files` FOREIGN KEY (`ossmailviewid`) REFERENCES `vtiger_ossmailview` (`ossmailviewid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_ossmailview_relation` (
  `ossmailviewid` int(19) NOT NULL,
  `crmid` int(19) NOT NULL,
  `date` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  KEY `ossmailviewid` (`ossmailviewid`),
  KEY `crmid` (`crmid`,`deleted`),
  CONSTRAINT `vtiger_ossmailview_relation_ibfk_1` FOREIGN KEY (`ossmailviewid`) REFERENCES `vtiger_ossmailview` (`ossmailviewid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_ossmailviewcf` (
  `ossmailviewid` int(19) NOT NULL,
  PRIMARY KEY (`ossmailviewid`),
  CONSTRAINT `fk_1_vtiger_ossmailviewcf` FOREIGN KEY (`ossmailviewid`) REFERENCES `vtiger_ossmailview` (`ossmailviewid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;









CREATE TABLE IF NOT EXISTS `vtiger_oss_project_templates` (
  `id` int(19) NOT NULL AUTO_INCREMENT,
  `fld_name` varchar(255) NOT NULL,
  `fld_val` varchar(255) NOT NULL,
  `id_tpl` int(11) NOT NULL,
  `parent` int(19) NOT NULL,
  `module` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `chat_bans` (
  `userID` int(11) NOT NULL,
  `userName` varchar(64) NOT NULL,
  `dateTime` datetime NOT NULL,
  `ip` varbinary(16) NOT NULL,
  PRIMARY KEY (`userID`),
  KEY `userName` (`userName`),
  KEY `dateTime` (`dateTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chat_invitations` (
  `userID` int(11) NOT NULL,
  `channel` int(11) NOT NULL,
  `dateTime` datetime NOT NULL,
  PRIMARY KEY (`userID`,`channel`),
  KEY `dateTime` (`dateTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `userName` varchar(64) NOT NULL,
  `userRole` int(1) NOT NULL,
  `channel` int(11) NOT NULL,
  `dateTime` datetime NOT NULL,
  `ip` varbinary(16) NOT NULL,
  `text` text,
  PRIMARY KEY (`id`),
  KEY `message_condition` (`id`,`channel`,`dateTime`),
  KEY `dateTime` (`dateTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chat_online` (
  `userID` int(11) NOT NULL,
  `userName` varchar(64) NOT NULL,
  `userRole` int(1) NOT NULL,
  `channel` int(11) NOT NULL,
  `dateTime` datetime NOT NULL,
  `ip` varbinary(16) NOT NULL,
  PRIMARY KEY (`userID`),
  KEY `userName` (`userName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `roundcube_users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `mail_host` varchar(128) NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `last_login` datetime DEFAULT NULL,
  `language` varchar(5) DEFAULT NULL,
  `preferences` longtext,
  `actions` text,
  `password` varchar(200) DEFAULT NULL,
  `crm_user_id` int(19) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`,`mail_host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `roundcube_cache` (
  `user_id` int(10) unsigned NOT NULL,
  `cache_key` varchar(128) CHARACTER SET ascii NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `expires` datetime DEFAULT NULL,
  `data` longtext NOT NULL,
  KEY `expires_index` (`expires`),
  KEY `user_cache_index` (`user_id`,`cache_key`),
  CONSTRAINT `roundcube_user_id_fk_cache` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_cache_index` (
  `user_id` int(10) unsigned NOT NULL,
  `mailbox` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `expires` datetime DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT '0',
  `data` longtext NOT NULL,
  PRIMARY KEY (`user_id`,`mailbox`),
  KEY `expires_index` (`expires`),
  CONSTRAINT `roundcube_user_id_fk_cache_index` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_cache_messages` (
  `user_id` int(10) unsigned NOT NULL,
  `mailbox` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `expires` datetime DEFAULT NULL,
  `data` longtext NOT NULL,
  `flags` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`mailbox`,`uid`),
  KEY `expires_index` (`expires`),
  CONSTRAINT `roundcube_user_id_fk_cache_messages` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_cache_shared` (
  `cache_key` varchar(255) CHARACTER SET ascii NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `expires` datetime DEFAULT NULL,
  `data` longtext NOT NULL,
  KEY `expires_index` (`expires`),
  KEY `cache_key_index` (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_cache_thread` (
  `user_id` int(10) unsigned NOT NULL,
  `mailbox` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `expires` datetime DEFAULT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`user_id`,`mailbox`),
  KEY `expires_index` (`expires`),
  CONSTRAINT `roundcube_user_id_fk_cache_thread` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `roundcube_contactgroups` (
  `contactgroup_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`contactgroup_id`),
  KEY `roundcube_contactgroups_user_index` (`user_id`,`del`),
  CONSTRAINT `roundcube_user_id_fk_contactgroups` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_contacts` (
  `contact_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  `email` text NOT NULL,
  `firstname` varchar(128) NOT NULL DEFAULT '',
  `surname` varchar(128) NOT NULL DEFAULT '',
  `vcard` longtext,
  `words` text,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`contact_id`),
  KEY `roundcube_user_contacts_index` (`user_id`,`del`),
  CONSTRAINT `roundcube_user_id_fk_contacts` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_contactgroupmembers` (
  `contactgroup_id` int(10) unsigned NOT NULL,
  `contact_id` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`contactgroup_id`,`contact_id`),
  KEY `roundcube_contactgroupmembers_contact_index` (`contact_id`),
  CONSTRAINT `roundcube_contactgroup_id_fk_contactgroups` FOREIGN KEY (`contactgroup_id`) REFERENCES `roundcube_contactgroups` (`contactgroup_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `roundcube_contact_id_fk_contacts` FOREIGN KEY (`contact_id`) REFERENCES `roundcube_contacts` (`contact_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_dictionary` (
  `user_id` int(10) unsigned DEFAULT NULL,
  `language` varchar(5) NOT NULL,
  `data` longtext NOT NULL,
  UNIQUE KEY `uniqueness` (`user_id`,`language`),
  CONSTRAINT `roundcube_user_id_fk_dictionary` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_identities` (
  `identity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `standard` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL,
  `organization` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL,
  `reply-to` varchar(128) NOT NULL DEFAULT '',
  `bcc` varchar(128) NOT NULL DEFAULT '',
  `signature` longtext,
  `html_signature` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`identity_id`),
  KEY `user_identities_index` (`user_id`,`del`),
  KEY `email_identities_index` (`email`,`del`),
  CONSTRAINT `roundcube_user_id_fk_identities` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_searches` (
  `search_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` int(3) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL,
  `data` text,
  PRIMARY KEY (`search_id`),
  UNIQUE KEY `uniqueness` (`user_id`,`type`,`name`),
  CONSTRAINT `roundcube_user_id_fk_searches` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_session` (
  `sess_id` varchar(128) NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `ip` varchar(40) NOT NULL,
  `vars` mediumtext NOT NULL,
  PRIMARY KEY (`sess_id`),
  KEY `changed_index` (`changed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_system` (
  `name` varchar(64) NOT NULL,
  `value` mediumtext,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_apiaddress` (
  `id` int(19) NOT NULL,
  `name` varchar(255) NOT NULL,
  `val` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_bruteforce`(
	`attempsnumber` int(11) NOT NULL  COMMENT 'Number of attempts' , 
	`timelock` int(11) NULL  COMMENT 'Time lock' , 
	`active` tinyint(1) NULL  DEFAULT 1 
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE IF NOT EXISTS `vtiger_osscosts_config` (
  `param` varchar(100) DEFAULT NULL,
  `value` varchar(100) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `vtiger_osscostscf` (
  `osscostsid` int(19) NOT NULL,
  PRIMARY KEY (`osscostsid`),
  CONSTRAINT `fk_1_vtiger_osscostscf` FOREIGN KEY (`osscostsid`) REFERENCES `vtiger_osscosts` (`osscostsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_calculationsproductrel` (
  `id` int(19) DEFAULT NULL,
  `productid` int(19) DEFAULT NULL,
  `sequence_no` int(4) DEFAULT NULL,
  `quantity` decimal(25,3) DEFAULT NULL,
  `listprice` decimal(27,8) DEFAULT NULL,
  `comment` text,
  `description` text,
  `lineitem_id` int(11) NOT NULL AUTO_INCREMENT,
  `incrementondel` int(19) DEFAULT NULL,
  `rbh` decimal(10,2) DEFAULT NULL,
  `purchase` decimal(10,2) DEFAULT NULL,
  `margin` decimal(10,2) DEFAULT NULL,
  `marginp` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`lineitem_id`),
  KEY `calculationsproductrel_id_idx` (`id`),
  KEY `calculationsproductrel_productid_idx` (`productid`),
  CONSTRAINT `vtiger_calculationsproductrel_ibfk_1` FOREIGN KEY (`id`) REFERENCES `vtiger_calculations` (`calculationsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `vtiger_dataaccess` (
  `dataaccessid` int(19) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) DEFAULT NULL,
  `summary` varchar(255) NOT NULL,
  `data` text,
  PRIMARY KEY (`dataaccessid`),
  KEY `dataaccesid` (`dataaccessid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_dataaccess_cnd` (
  `dataaccess_cndid` int(19) NOT NULL AUTO_INCREMENT,
  `dataaccessid` int(19) NOT NULL,
  `fieldname` varchar(255) NOT NULL,
  `comparator` varchar(255) NOT NULL,
  `val` varchar(255) DEFAULT NULL,
  `required` tinyint(19) NOT NULL,
  `field_type` varchar(100) NOT NULL,
  PRIMARY KEY (`dataaccess_cndid`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `vtiger_invoicestatushistory_seq`(
	`id` int(11) NOT NULL  
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE IF NOT EXISTS `vtiger_widgets`(
	`id` int(19) NOT NULL  auto_increment , 
	`tabid` int(19) NULL  , 
	`type` varchar(30) COLLATE utf8_general_ci NULL  , 
	`label` varchar(100) COLLATE utf8_general_ci NULL  , 
	`wcol` tinyint(1) NULL  DEFAULT 1 , 
	`sequence` tinyint(2) NULL  , 
	`nomargin` tinyint(1) NULL  DEFAULT 0 , 
	`data` text COLLATE utf8_general_ci NULL  , 
	PRIMARY KEY (`id`) , 
	KEY `tabid`(`tabid`) , 
	CONSTRAINT `vtiger_widgets_ibfk_1` 
	FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE IF NOT EXISTS `yetiforce_updates`(
	`id` int(11) NOT NULL  auto_increment , 
	`time` timestamp NOT NULL  DEFAULT CURRENT_TIMESTAMP , 
	`user` varchar(50) COLLATE utf8_general_ci NULL  , 
	`name` varchar(100) COLLATE utf8_general_ci NULL  , 
	`from_version` varchar(10) COLLATE utf8_general_ci NULL  , 
	`to_version` varchar(10) COLLATE utf8_general_ci NULL  , 
	`result` tinyint(1) NULL  , 
	PRIMARY KEY (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE IF NOT EXISTS `vtiger_picklist_dependency_seq`(
	`id` int(11) NOT NULL  
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE IF NOT EXISTS `vtiger_end_hour`(
	`end_hourid` int(11) NOT NULL  auto_increment , 
	`end_hour` varchar(200) COLLATE utf8_general_ci NOT NULL  , 
	`sortorderid` int(11) NULL  , 
	`presence` int(11) NOT NULL  DEFAULT 1 , 
	PRIMARY KEY (`end_hourid`) 
) ENGINE=InnoDB DEFAULT CHARSET='utf8';
CREATE TABLE IF NOT EXISTS `vtiger_end_hour_seq`(
	`id` int(11) NOT NULL  
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE IF NOT EXISTS `vtiger_backup` (
`backupid` int(11) NOT NULL AUTO_INCREMENT,
`file_name` varchar(50) NOT NULL,
`created_at` datetime NOT NULL,
`create_time` varchar(40) NOT NULL,
`how_many` int(11) NOT NULL,
PRIMARY KEY (`backupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_backup_db` (
`tmpbackupid` int(11) NOT NULL AUTO_INCREMENT,
`table_name` varchar(100) NOT NULL,
`status` tinyint(1) NOT NULL,
PRIMARY KEY (`tmpbackupid`)) 
ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_backup_info` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`file_name` varchar(50) NOT NULL,
`status` varchar(20) NOT NULL,
`time` varchar(40) DEFAULT '0',
`howmany` int(11) NOT NULL DEFAULT '0',
`tables_prepare` tinyint(1) NOT NULL,
`backup_db` tinyint(1) NOT NULL,
PRIMARY KEY (`id`))
ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_backup_files` (
`name` varchar (200) NOT NULL,
`backup` int (11) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_backup_ftp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(50) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `port` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_contactsbookmails` (
  `contactid` int(19) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `users` text,
  KEY `email` (`email`,`name`),
  KEY `contactid` (`contactid`),
  CONSTRAINT `vtiger_contactsbookmails_ibfk_1` FOREIGN KEY (`contactid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert  into `vtiger_bruteforce`(`attempsnumber`,`timelock`,`active`) values (10,15,1);
insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (1,'min_lenght','3','global');
insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (2,'key','','google_map_api');
insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (3,'nominatim','0','google_map_api');
insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (4,'source','https://maps.googleapis.com/maps/api/geocode/json','google_map_api');
insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (5,'key','','opencage_data');
insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (6,'source','https://api.opencagedata.com/geocode/v1/','opencage_data');
insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (7,'nominatim','0','opencage_data');
insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (8,'result_num','10','global');

insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (1,'HelpDesk','Adding time period to status change','a:1:{i:0;a:3:{s:2:\"an\";s:25:\"Vtiger!!show_quick_create\";s:7:\"modules\";s:14:\"OSSTimeControl\";s:2:\"cf\";b:1;}}');
insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (2,'Calendar','Adding time period to status change','a:1:{i:0;a:3:{s:2:\"an\";s:25:\"Vtiger!!show_quick_create\";s:7:\"modules\";s:14:\"OSSTimeControl\";s:2:\"cf\";b:1;}}');
insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (3,'ProjectTask','Adding time period to status change','a:1:{i:0;a:3:{s:2:\"an\";s:25:\"Vtiger!!show_quick_create\";s:7:\"modules\";s:14:\"OSSTimeControl\";s:2:\"cf\";b:1;}}');
insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (4,'Potentials','Check if there are any tasks that are not closed','a:1:{i:0;a:4:{s:2:\"an\";s:21:\"Vtiger!!check_alltask\";s:6:\"status\";a:5:{i:0;s:11:\"Not Started\";i:1;s:11:\"In Progress\";i:2;s:13:\"Pending Input\";i:3;s:8:\"Deferred\";i:4;s:7:\"Planned\";}s:7:\"message\";s:67:\"There are unsolved tasks, complete them to be able to change status\";s:2:\"cf\";b:1;}}');
insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (5,'ProjectTask','Date validation','a:1:{i:0;a:2:{s:2:\"cf\";b:0;s:2:\"an\";s:22:\"Vtiger!!check_taskdate\";}}');
insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (6,'ProjectTask','Check parent task','a:1:{i:0;a:3:{s:2:\"an\";s:24:\"Vtiger!!check_taskstatus\";s:6:\"status\";a:2:{i:0;s:4:\"Open\";i:1;s:11:\"In Progress\";}s:2:\"cf\";b:1;}}');
insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (7,'Leads','Check if there are any tasks that are not closed','a:1:{i:0;a:4:{s:2:\"an\";s:21:\"Vtiger!!check_alltask\";s:6:\"status\";a:5:{i:0;s:11:\"Not Started\";i:1;s:11:\"In Progress\";i:2;s:13:\"Pending Input\";i:3;s:8:\"Deferred\";i:4;s:7:\"Planned\";}s:7:\"message\";s:67:\"There are unsolved tasks, complete them to be able to change status\";s:2:\"cf\";b:1;}}');
insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (8,'Accounts','Check for duplicate names','a:1:{i:0;a:8:{s:2:\"an\";s:20:\"Vtiger!!unique_value\";s:5:\"what1\";s:11:\"accountname\";s:6:\"where1\";a:2:{i:0;s:28:\"vtiger_account=accountname=6\";i:1;s:28:\"vtiger_leaddetails=company=7\";}s:5:\"info0\";s:24:\"This name already exists\";s:5:\"info1\";s:24:\"This name already exists\";s:5:\"info2\";s:0:\"\";s:8:\"locksave\";s:1:\"1\";s:2:\"cf\";b:1;}}');
insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (9,'All','Check whether all mandatory fields in quick edit are filled in','a:1:{i:0;a:2:{s:2:\"cf\";b:0;s:2:\"an\";s:26:\"Vtiger!!validate_mandatory\";}}');
insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (10,'HelpDesk','Lock edit on the status','a:1:{i:0;a:2:{s:2:\"cf\";b:0;s:2:\"an\";s:21:\"Vtiger!!blockEditView\";}}');
insert  into `vtiger_dataaccess`(`dataaccessid`,`module_name`,`summary`,`data`) values (11,'Events','Adding time period to status change','a:1:{i:0;a:3:{s:2:\"an\";s:25:\"Vtiger!!show_quick_create\";s:7:\"modules\";s:14:\"OSSTimeControl\";s:2:\"cf\";b:1;}}');

insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (37,1,'ticketstatus','has changed','Open',1,'picklist');
insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (38,2,'taskstatus','has changed','Not Started',1,'picklist');
insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (39,3,'projecttaskstatus','has changed','Open',1,'picklist');
insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (42,5,'projectmilestoneid','is not empty','',1,'reference');
insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (46,7,'leadstatus','is','LBL_LEAD_ACQUIRED',1,'picklist');
insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (47,4,'sales_stage','is','Closed Lost',0,'picklist');
insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (48,4,'sales_stage','is','Closed Won',0,'picklist');
insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (49,8,'accountname','is not empty','',1,'string');
insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (50,10,'ticketstatus','is','Rejected',0,'picklist');
insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (51,10,'ticketstatus','is','Closed',0,'picklist');
insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (52,11,'eventstatus','has changed','Held',1,'picklist');

insert  into `vtiger_password`(`type`,`val`) values ('min_length','8');
insert  into `vtiger_password`(`type`,`val`) values ('max_length','32');
insert  into `vtiger_password`(`type`,`val`) values ('big_letters','true');
insert  into `vtiger_password`(`type`,`val`) values ('small_letters','true');
insert  into `vtiger_password`(`type`,`val`) values ('numbers','true');
insert  into `vtiger_password`(`type`,`val`) values ('special','false');

insert  into `vtiger_ws_fieldtype`(`uitype`,`fieldtype`) values ('300','text');
insert  into `vtiger_ws_fieldtype`(`uitype`,`fieldtype`) values ('120','sharedOwner');
insert  into `vtiger_ws_fieldtype`(`uitype`,`fieldtype`) values ('301','modules');
insert  into `vtiger_ws_fieldtype`(`uitype`,`fieldtype`) values ('302','tree');
insert  into `vtiger_ws_referencetype`(`fieldtypeid`,`type`) values (34,'Project');
insert  into `vtiger_ws_referencetype`(`fieldtypeid`,`type`) values (34,'ServiceContracts');

CREATE TABLE IF NOT EXISTS `com_vtiger_workflowtask_queue` (
  `task_id` int(11) DEFAULT NULL,
  `entity_id` varchar(100) DEFAULT NULL,
  `do_after` int(11) DEFAULT NULL,
  `task_contents` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `yetiforce_mobile_keys` (
  `id` int(19) NOT NULL AUTO_INCREMENT,
  `user` int(19) NOT NULL,
  `service` varchar(50) NOT NULL,
  `key` varchar(30) NOT NULL,
  `privileges_users` text,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `yetiforce_mobile_pushcall` (
  `user` int(19) NOT NULL,
  `number` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_module_dashboard`(
	`id` int(100) NOT NULL  auto_increment , 
	`blockid` int(100) NOT NULL  , 
	`linkid` int(19) NULL  , 
	`filterid` int(19) NULL  , 
	`title` varchar(100) COLLATE utf8_general_ci NULL  , 
	`data` text COLLATE utf8_general_ci NULL  , 
	`size` varchar(50) COLLATE utf8_general_ci NULL  , 
	`limit` int(10) NULL  , 
	`isdefault` int(1) NOT NULL  DEFAULT 0 , 
	`owners` varchar(100) DEFAULT NULL,
	PRIMARY KEY (`id`) , 
	KEY `vtiger_module_dashboard_ibfk_1`(`blockid`) , 
	CONSTRAINT `vtiger_module_dashboard_ibfk_1` 
	FOREIGN KEY (`blockid`) REFERENCES `vtiger_module_dashboard_blocks` (`id`) ON DELETE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE IF NOT EXISTS  `vtiger_module_dashboard_blocks`(
	`id` int(100) NOT NULL  auto_increment , 
	`authorized` varchar(10) COLLATE utf8_general_ci NOT NULL  , 
	`tabid` int(19) NOT NULL  , 
	PRIMARY KEY (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET='utf8';


CREATE TABLE IF NOT EXISTS `vtiger_relatedlists_fields` (
  `relation_id` int(19) DEFAULT NULL,
  `fieldid` int(19) DEFAULT NULL,
  `fieldname` varchar(30) DEFAULT NULL,
  `sequence` int(10) DEFAULT NULL,
  KEY `relation_id` (`relation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_trees_templates_data`(
	`templateid` int(19) NOT NULL  , 
	`name` varchar(255) NULL  , 
	`tree` varchar(255) NULL  , 
	`parenttrre` varchar(255) NULL  , 
	`depth` int(10) NULL  , 
	`label` varchar(255) NULL  , 
	`state` varchar(10) NULL  , 
	KEY `id`(`templateid`) , 
	KEY `parenttrre`(`parenttrre`,`templateid`) 
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE IF NOT EXISTS `vtiger_trees_templates` (
  `templateid` int(19) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `module` int(19) DEFAULT NULL,
  `access` int(1) DEFAULT '1',
  PRIMARY KEY (`templateid`),
  KEY `module` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_blocks_hide`(
	`id` int(19) NOT NULL  auto_increment , 
	`blockid` int(19) NULL  , 
	`conditions` text COLLATE utf8_general_ci NULL  , 
	`enabled` tinyint(1) NULL  , 
	`view` varchar(100) COLLATE utf8_general_ci NULL  , 
	PRIMARY KEY (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE IF NOT EXISTS `vtiger_publicholiday` (
  `publicholidayid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id of public holiday',
  `holidaydate` date NOT NULL COMMENT 'date of holiday',
  `holidayname` varchar(255) NOT NULL COMMENT 'name of holiday',
  `holidaytype` varchar(25) DEFAULT NULL COMMENT 'type of holiday',
  PRIMARY KEY (`publicholidayid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_calendar_config`(
	`type` varchar(10) NULL  , 
	`name` varchar(20) NULL  , 
	`label` varchar(20) NULL  , 
	`value` varchar(100) NULL  
) ENGINE=InnoDB DEFAULT CHARSET='utf8';
insert  into `vtiger_calendar_config`(`type`,`name`,`label`,`value`) values ('colors','break','PLL_BREAK_TIME','#ffd000');
insert  into `vtiger_calendar_config`(`type`,`name`,`label`,`value`) values ('colors','holiday','PLL_HOLIDAY_TIME','#00d4f5');
insert  into `vtiger_calendar_config`(`type`,`name`,`label`,`value`) values ('colors','work','PLL_WORKING_TIME','#FFD500');
insert  into `vtiger_calendar_config`(`type`,`name`,`label`,`value`) values ('colors','Task','Task','#00d4f5');
insert  into `vtiger_calendar_config`(`type`,`name`,`label`,`value`) values ('colors','Meeting','Meeting','#FFD500');
insert  into `vtiger_calendar_config`(`type`,`name`,`label`,`value`) values ('reminder','update_event','LBL_UPDATE_EVENT','0');
insert  into `vtiger_calendar_config`(`type`,`name`,`label`,`value`) values ('info','notworkingdays ','LBL_NOTWORKING_DAYS',NULL);

CREATE TABLE IF NOT EXISTS `vtiger_bruteforce_users`(
	`id` int(19) NOT NULL  , 
	KEY `fk_1_vtiger_bruteforce_users`(`id`) , 
	CONSTRAINT `fk_1_vtiger_bruteforce_users` 
	FOREIGN KEY (`id`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE IF NOT EXISTS `dav_addressbookchanges` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `uri` varchar(200) NOT NULL,
					  `synctoken` int(11) unsigned NOT NULL,
					  `addressbookid` int(11) unsigned NOT NULL,
					  `operation` tinyint(1) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `addressbookid_synctoken` (`addressbookid`,`synctoken`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `dav_groupmembers` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `principal_id` int(10) unsigned NOT NULL,
				  `member_id` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `principal_id` (`principal_id`,`member_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				CREATE TABLE IF NOT EXISTS `dav_principals` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `uri` varchar(200) NOT NULL,
				  `email` varchar(80) DEFAULT NULL,
				  `displayname` varchar(80) DEFAULT NULL,
				  `vcardurl` varchar(255) DEFAULT NULL,
				  `userid` int(19) DEFAULT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `uri` (`uri`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `dav_addressbooks` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `principaluri` varchar(255) DEFAULT NULL,
				  `displayname` varchar(255) DEFAULT NULL,
				  `uri` varchar(200) DEFAULT NULL,
				  `description` text,
				  `synctoken` int(11) unsigned NOT NULL DEFAULT '1',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `principaluri` (`principaluri`(100),`uri`(100)),
				  KEY `principaluri_2` (`principaluri`),
				  CONSTRAINT `dav_addressbooks_ibfk_1` FOREIGN KEY (`principaluri`) REFERENCES `dav_principals` (`uri`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `dav_cards` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `addressbookid` int(11) unsigned NOT NULL,
				  `carddata` mediumblob,
				  `uri` varchar(200) DEFAULT NULL,
				  `lastmodified` int(11) unsigned DEFAULT NULL,
				  `etag` varbinary(32) DEFAULT NULL,
				  `size` int(11) unsigned NOT NULL,
				  `crmid` int(19) DEFAULT '0',
				  PRIMARY KEY (`id`),
				  KEY `addressbookid` (`addressbookid`,`crmid`),
				  CONSTRAINT `dav_cards_ibfk_1` FOREIGN KEY (`addressbookid`) REFERENCES `dav_addressbooks` (`id`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `dav_users` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `username` varchar(50) DEFAULT NULL,
				  `digesta1` varchar(32) DEFAULT NULL,
				  `userid` int(19) unsigned DEFAULT NULL,
				  `key` varchar(50) DEFAULT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `username` (`username`),
				  UNIQUE KEY `userid` (`userid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `dav_calendarchanges` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `uri` varchar(200) NOT NULL,
					  `synctoken` int(11) unsigned NOT NULL,
					  `calendarid` int(11) unsigned NOT NULL,
					  `operation` tinyint(1) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `calendarid_synctoken` (`calendarid`,`synctoken`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `dav_calendars` (
					  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `principaluri` varbinary(100) DEFAULT NULL,
					  `displayname` varchar(100) DEFAULT NULL,
					  `uri` varbinary(200) DEFAULT NULL,
					  `synctoken` int(10) unsigned NOT NULL DEFAULT '1',
					  `description` text,
					  `calendarorder` int(11) unsigned NOT NULL DEFAULT '0',
					  `calendarcolor` varbinary(10) DEFAULT NULL,
					  `timezone` text,
					  `components` varbinary(20) DEFAULT NULL,
					  `transparent` tinyint(1) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `principaluri` (`principaluri`,`uri`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `dav_calendarobjects` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `calendardata` mediumblob,
					  `uri` varbinary(200) DEFAULT NULL,
					  `calendarid` int(10) unsigned NOT NULL,
					  `lastmodified` int(11) unsigned DEFAULT NULL,
					  `etag` varbinary(32) DEFAULT NULL,
					  `size` int(11) unsigned NOT NULL,
					  `componenttype` varbinary(8) DEFAULT NULL,
					  `firstoccurence` int(11) unsigned DEFAULT NULL,
					  `lastoccurence` int(11) unsigned DEFAULT NULL,
					  `uid` varchar(200) DEFAULT NULL,
					  `crmid` int(19) DEFAULT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `calendarid` (`calendarid`,`uri`),
					  CONSTRAINT `dav_calendarobjects_ibfk_1` FOREIGN KEY (`calendarid`) REFERENCES `dav_calendars` (`id`) ON DELETE CASCADE
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `dav_schedulingobjects` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `principaluri` varchar(255) DEFAULT NULL,
					  `calendardata` mediumblob,
					  `uri` varchar(200) DEFAULT NULL,
					  `lastmodified` int(11) unsigned DEFAULT NULL,
					  `etag` varchar(32) DEFAULT NULL,
					  `size` int(11) unsigned NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `dav_calendarsubscriptions` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `uri` varchar(200) NOT NULL,
					  `principaluri` varchar(100) NOT NULL,
					  `source` text,
					  `displayname` varchar(100) DEFAULT NULL,
					  `refreshrate` varchar(10) DEFAULT NULL,
					  `calendarorder` int(11) unsigned NOT NULL DEFAULT '0',
					  `calendarcolor` varchar(10) DEFAULT NULL,
					  `striptodos` tinyint(1) DEFAULT NULL,
					  `stripalarms` tinyint(1) DEFAULT NULL,
					  `stripattachments` tinyint(1) DEFAULT NULL,
					  `lastmodified` int(11) unsigned DEFAULT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `principaluri` (`principaluri`,`uri`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roundcube_users_autologin` (
				  `rcuser_id` int(10) unsigned NOT NULL,
				  `crmuser_id` int(19) NOT NULL,
				  KEY `rcuser_id` (`rcuser_id`),
				  CONSTRAINT `roundcube_users_autologin_ibfk_1` FOREIGN KEY (`rcuser_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `yetiforce_mail_config` (
				  `type` varchar(50) DEFAULT NULL,
				  `name` varchar(50) DEFAULT NULL,
				  `value` text,
				  UNIQUE KEY `type` (`type`,`name`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `yetiforce_mail_quantities` (
				  `userid` int(10) unsigned NOT NULL,
				  `num` int(10) unsigned DEFAULT '0',
				  `status` tinyint(1) DEFAULT '0',
				  PRIMARY KEY (`userid`),
				  CONSTRAINT `yetiforce_mail_quantities_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
CREATE TABLE IF NOT EXISTS `vtiger_support_processes` (
					`id` int(11) NOT NULL,
					`ticket_status_indicate_closing` varchar(255) NOT NULL
				  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;				
				
CREATE TABLE IF NOT EXISTS `vtiger_realization_process` (
  `module_id` int(11) NOT NULL,
  `status_indicate_closing` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
CREATE TABLE IF NOT EXISTS `yetiforce_menu` (
					  `id` int(19) unsigned NOT NULL AUTO_INCREMENT,
					  `role` int(19) DEFAULT NULL,
					  `parentid` int(19) DEFAULT '0',
					  `type` tinyint(1) DEFAULT NULL,
					  `sequence` int(3) DEFAULT NULL,
					  `module` int(19) DEFAULT NULL,
					  `label` varchar(100) DEFAULT NULL,
					  `newwindow` tinyint(1) DEFAULT '0',
					  `dataurl` text,
					  `showicon` tinyint(1) DEFAULT '0',
					  `icon` varchar(255) DEFAULT NULL,
					  `sizeicon` varchar(255) DEFAULT NULL,
					  `hotkey` varchar(30) DEFAULT NULL,
					  PRIMARY KEY (`id`),
					  KEY `parent_id` (`parentid`),
					  KEY `role` (`role`),
					  KEY `module` (`module`),
					  CONSTRAINT `yetiforce_menu_ibfk_1` FOREIGN KEY (`module`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
CREATE TABLE IF NOT EXISTS `yetiforce_auth` (
					  `type` varchar(20) DEFAULT NULL,
					  `param` varchar(20) DEFAULT NULL,
					  `value` text,
					  UNIQUE KEY `type` (`type`,`param`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
CREATE TABLE IF NOT EXISTS `vtiger_group2modules` (
				  `groupid` int(19) NOT NULL,
				  `tabid` int(19) NOT NULL,
				  KEY `groupid` (`groupid`),
				  KEY `tabid` (`tabid`),
				  CONSTRAINT `vtiger_group2modules_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE,
				  CONSTRAINT `vtiger_group2modules_ibfk_2` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
CREATE TABLE IF NOT EXISTS `yetiforce_proc_marketing` (
  `type` varchar(30) DEFAULT NULL,
  `param` varchar(30) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  KEY `type` (`type`,`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
CREATE TABLE IF NOT EXISTS `yetiforce_proc_sales` (
  `type` varchar(30) DEFAULT NULL,
  `param` varchar(30) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  KEY `type` (`type`,`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
CREATE TABLE IF NOT EXISTS `vtiger_backup_users` (
  `id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vtiger_backup_settings` (
  `type` varchar(100) DEFAULT NULL,
  `param` varchar(100) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
insert  into `vtiger_backup_settings`(`type`,`param`,`value`) values ('folder','storage_folder','false');
insert  into `vtiger_backup_settings`(`type`,`param`,`value`) values ('folder','storage_folder','false');







