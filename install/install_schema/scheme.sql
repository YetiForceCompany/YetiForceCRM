
/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `a_yf_adv_permission` */

CREATE TABLE `a_yf_adv_permission` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `tabid` smallint(5) DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `action` tinyint(1) unsigned NOT NULL,
  `conditions` text DEFAULT NULL,
  `members` text NOT NULL,
  `priority` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tabid` (`tabid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_bruteforce` */

CREATE TABLE `a_yf_bruteforce` (
  `attempsnumber` tinyint(2) NOT NULL,
  `timelock` smallint(5) NOT NULL,
  `active` tinyint(1) DEFAULT 0,
  `sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_bruteforce_blocked` */

CREATE TABLE `a_yf_bruteforce_blocked` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `time` timestamp NULL DEFAULT NULL,
  `attempts` tinyint(2) DEFAULT 0,
  `blocked` tinyint(1) DEFAULT 0,
  `userid` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bf1_mixed` (`ip`,`time`,`blocked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_bruteforce_users` */

CREATE TABLE `a_yf_bruteforce_users` (
  `id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_1_vtiger_bruteforce_users` FOREIGN KEY (`id`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_discounts_config` */

CREATE TABLE `a_yf_discounts_config` (
  `param` varchar(30) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_discounts_global` */

CREATE TABLE `a_yf_discounts_global` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `value` decimal(5,2) unsigned NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_encryption` */

CREATE TABLE `a_yf_encryption` (
  `method` varchar(40) NOT NULL,
  `pass` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_inventory_limits` */

CREATE TABLE `a_yf_inventory_limits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(50) NOT NULL,
  `value` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_mapped_config` */

CREATE TABLE `a_yf_mapped_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tabid` smallint(5) unsigned NOT NULL,
  `reltabid` smallint(5) unsigned NOT NULL,
  `status` tinyint(1) unsigned DEFAULT 0,
  `conditions` text DEFAULT NULL,
  `permissions` varchar(255) DEFAULT NULL,
  `params` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tabid` (`tabid`),
  KEY `reltabid` (`reltabid`),
  KEY `tabid_2` (`tabid`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_mapped_fields` */

CREATE TABLE `a_yf_mapped_fields` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mappedid` int(10) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `source` varchar(30) DEFAULT NULL,
  `target` varchar(30) DEFAULT NULL,
  `default` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `a_yf_mapped_fields_ibfk_1` (`mappedid`),
  CONSTRAINT `a_yf_mapped_fields_ibfk_1` FOREIGN KEY (`mappedid`) REFERENCES `a_yf_mapped_config` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_pdf` */

CREATE TABLE `a_yf_pdf` (
  `pdfid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id of record',
  `module_name` varchar(25) NOT NULL COMMENT 'name of the module',
  `header_content` text DEFAULT NULL,
  `body_content` text DEFAULT NULL,
  `footer_content` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `primary_name` varchar(255) NOT NULL,
  `secondary_name` varchar(255) NOT NULL,
  `meta_author` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `metatags_status` tinyint(1) NOT NULL,
  `meta_subject` varchar(255) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `page_format` varchar(255) NOT NULL,
  `margin_chkbox` tinyint(1) DEFAULT NULL,
  `margin_top` smallint(2) unsigned DEFAULT NULL,
  `margin_bottom` smallint(2) unsigned DEFAULT NULL,
  `margin_left` smallint(2) unsigned DEFAULT NULL,
  `margin_right` smallint(2) unsigned DEFAULT NULL,
  `header_height` smallint(2) unsigned DEFAULT NULL,
  `footer_height` smallint(2) unsigned DEFAULT NULL,
  `page_orientation` varchar(30) NOT NULL,
  `language` varchar(7) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `visibility` varchar(200) NOT NULL,
  `default` tinyint(1) DEFAULT NULL,
  `conditions` text DEFAULT NULL,
  `watermark_type` tinyint(1) NOT NULL DEFAULT 0,
  `watermark_text` text NOT NULL,
  `watermark_angle` smallint(3) unsigned NOT NULL,
  `watermark_image` varchar(255) NOT NULL,
  `template_members` text NOT NULL,
  `one_pdf` tinyint(1) DEFAULT NULL,
  `type` tinyint(1) unsigned DEFAULT 0,
  PRIMARY KEY (`pdfid`),
  KEY `module_name` (`module_name`,`status`),
  KEY `module_name_2` (`module_name`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_record_converter` */

CREATE TABLE `a_yf_record_converter` (
  `id` smallint(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `source_module` smallint(5) NOT NULL,
  `destiny_module` varchar(255) NOT NULL,
  `field_merge` varchar(50) DEFAULT NULL,
  `field_mappging` text DEFAULT NULL,
  `inv_field_mapping` text DEFAULT NULL,
  `redirect_to_edit` tinyint(1) DEFAULT NULL,
  `change_view` smallint(5) DEFAULT NULL,
  `check_duplicate` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`,`source_module`,`destiny_module`),
  KEY `a_yf_record_converter_fk_tab` (`source_module`),
  CONSTRAINT `fk_1_a_yf_record_converter` FOREIGN KEY (`source_module`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_relatedlists_inv_fields` */

CREATE TABLE `a_yf_relatedlists_inv_fields` (
  `relation_id` int(10) DEFAULT NULL,
  `fieldname` varchar(30) DEFAULT NULL,
  `sequence` tinyint(1) DEFAULT NULL,
  KEY `relation_id` (`relation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_smsnotifier_servers` */

CREATE TABLE `a_yf_smsnotifier_servers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `providertype` varchar(50) NOT NULL,
  `isactive` tinyint(1) DEFAULT 0,
  `api_key` varchar(255) NOT NULL,
  `parameters` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_taxes_config` */

CREATE TABLE `a_yf_taxes_config` (
  `param` varchar(30) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `a_yf_taxes_global` */

CREATE TABLE `a_yf_taxes_global` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `value` decimal(5,2) unsigned NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `b_yf_social_media_twitter` */

CREATE TABLE `b_yf_social_media_twitter` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `twitter_login` varchar(15) NOT NULL,
  `id_twitter` varchar(32) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `twitter_name` varchar(50) DEFAULT NULL,
  `reply` int(11) DEFAULT NULL,
  `retweet` int(11) DEFAULT NULL,
  `favorite` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `twitter_login` (`twitter_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `com_vtiger_workflow_activatedonce` */

CREATE TABLE `com_vtiger_workflow_activatedonce` (
  `workflow_id` int(10) NOT NULL,
  `entity_id` int(10) NOT NULL,
  PRIMARY KEY (`workflow_id`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `com_vtiger_workflow_tasktypes` */

CREATE TABLE `com_vtiger_workflow_tasktypes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tasktypename` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `classname` varchar(255) DEFAULT NULL,
  `classpath` varchar(255) DEFAULT NULL,
  `templatepath` varchar(255) DEFAULT NULL,
  `modules` varchar(500) DEFAULT NULL,
  `sourcemodule` varchar(255) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Table structure for table `com_vtiger_workflows` */

CREATE TABLE `com_vtiger_workflows` (
  `workflow_id` int(10) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(100) DEFAULT NULL,
  `summary` varchar(400) NOT NULL,
  `test` text DEFAULT NULL,
  `execution_condition` int(10) NOT NULL,
  `defaultworkflow` int(1) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `filtersavedinnew` int(1) DEFAULT NULL,
  `schtypeid` int(10) DEFAULT NULL,
  `schdayofmonth` varchar(100) DEFAULT NULL,
  `schdayofweek` varchar(100) DEFAULT NULL,
  `schannualdates` varchar(100) DEFAULT NULL,
  `schtime` varchar(50) DEFAULT NULL,
  `nexttrigger_time` datetime DEFAULT NULL,
  PRIMARY KEY (`workflow_id`),
  UNIQUE KEY `com_vtiger_workflows_idx` (`workflow_id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;

/*Table structure for table `com_vtiger_workflowtask_queue` */

CREATE TABLE `com_vtiger_workflowtask_queue` (
  `task_id` int(10) DEFAULT NULL,
  `entity_id` varchar(100) DEFAULT NULL,
  `do_after` int(10) DEFAULT NULL,
  `task_contents` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `com_vtiger_workflowtasks` */

CREATE TABLE `com_vtiger_workflowtasks` (
  `task_id` int(10) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(10) DEFAULT NULL,
  `summary` varchar(400) NOT NULL,
  `task` text DEFAULT NULL,
  PRIMARY KEY (`task_id`),
  KEY `workflow_id` (`workflow_id`),
  CONSTRAINT `com_vtiger_workflowtasks_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `com_vtiger_workflows` (`workflow_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8;

/*Table structure for table `com_vtiger_workflowtasks_entitymethod` */

CREATE TABLE `com_vtiger_workflowtasks_entitymethod` (
  `workflowtasks_entitymethod_id` int(10) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(100) DEFAULT NULL,
  `method_name` varchar(100) DEFAULT NULL,
  `function_path` varchar(400) DEFAULT NULL,
  `function_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`workflowtasks_entitymethod_id`),
  UNIQUE KEY `com_vtiger_workflowtasks_entitymethod_idx` (`workflowtasks_entitymethod_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Table structure for table `com_vtiger_workflowtemplates` */

CREATE TABLE `com_vtiger_workflowtemplates` (
  `template_id` int(10) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(100) DEFAULT NULL,
  `title` varchar(400) DEFAULT NULL,
  `template` text DEFAULT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `dav_addressbookchanges` */

CREATE TABLE `dav_addressbookchanges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varbinary(200) NOT NULL,
  `synctoken` int(10) unsigned NOT NULL,
  `addressbookid` int(10) unsigned NOT NULL,
  `operation` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addressbookid_synctoken` (`addressbookid`,`synctoken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_addressbooks` */

CREATE TABLE `dav_addressbooks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `principaluri` varbinary(255) DEFAULT NULL,
  `displayname` varchar(255) DEFAULT NULL,
  `uri` varbinary(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `synctoken` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `principaluri` (`principaluri`(100),`uri`(100)),
  KEY `dav_addressbooks_idx` (`principaluri`),
  CONSTRAINT `dav_addressbooks_ibfk_1` FOREIGN KEY (`principaluri`) REFERENCES `dav_principals` (`uri`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_calendarchanges` */

CREATE TABLE `dav_calendarchanges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varbinary(200) NOT NULL,
  `synctoken` int(10) unsigned NOT NULL,
  `calendarid` int(10) unsigned NOT NULL,
  `operation` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `calendarid_synctoken` (`calendarid`,`synctoken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_calendarinstances` */

CREATE TABLE `dav_calendarinstances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `calendarid` int(10) unsigned NOT NULL,
  `principaluri` varbinary(100) DEFAULT NULL,
  `access` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = owner, 2 = read, 3 = readwrite',
  `displayname` varchar(100) DEFAULT NULL,
  `uri` varbinary(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `calendarorder` int(11) unsigned NOT NULL DEFAULT 0,
  `calendarcolor` varbinary(10) DEFAULT NULL,
  `timezone` text DEFAULT NULL,
  `transparent` tinyint(1) NOT NULL DEFAULT 0,
  `share_href` varbinary(100) DEFAULT NULL,
  `share_displayname` varchar(100) DEFAULT NULL,
  `share_invitestatus` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1 = noresponse, 2 = accepted, 3 = declined, 4 = invalid',
  PRIMARY KEY (`id`),
  UNIQUE KEY `principaluri` (`principaluri`,`uri`),
  UNIQUE KEY `calendarid` (`calendarid`,`principaluri`),
  UNIQUE KEY `calendarid_2` (`calendarid`,`share_href`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_calendarobjects` */

CREATE TABLE `dav_calendarobjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `calendardata` blob DEFAULT NULL,
  `uri` varbinary(200) DEFAULT NULL,
  `calendarid` int(10) unsigned NOT NULL,
  `lastmodified` int(10) unsigned DEFAULT NULL,
  `etag` varbinary(32) DEFAULT NULL,
  `size` int(10) unsigned NOT NULL,
  `componenttype` varbinary(8) DEFAULT NULL,
  `firstoccurence` int(10) unsigned DEFAULT NULL,
  `lastoccurence` int(10) unsigned DEFAULT NULL,
  `uid` varbinary(200) DEFAULT NULL,
  `crmid` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `calendarid` (`calendarid`,`uri`),
  KEY `uri` (`uri`),
  KEY `crmid` (`crmid`),
  CONSTRAINT `dav_calendarobjects_ibfk_1` FOREIGN KEY (`calendarid`) REFERENCES `dav_calendars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_calendars` */

CREATE TABLE `dav_calendars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `synctoken` int(10) unsigned NOT NULL DEFAULT 1,
  `components` varbinary(21) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `dav_calendarsubscriptions` */

CREATE TABLE `dav_calendarsubscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varbinary(200) NOT NULL,
  `principaluri` varbinary(100) NOT NULL,
  `source` text DEFAULT NULL,
  `displayname` varchar(100) DEFAULT NULL,
  `refreshrate` varchar(10) DEFAULT NULL,
  `calendarorder` int(10) unsigned NOT NULL DEFAULT 0,
  `calendarcolor` varbinary(10) DEFAULT NULL,
  `striptodos` tinyint(1) DEFAULT NULL,
  `stripalarms` tinyint(1) DEFAULT NULL,
  `stripattachments` tinyint(1) DEFAULT NULL,
  `lastmodified` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `principaluri` (`principaluri`,`uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_cards` */

CREATE TABLE `dav_cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addressbookid` int(10) unsigned NOT NULL,
  `carddata` blob DEFAULT NULL,
  `uri` varbinary(200) DEFAULT NULL,
  `lastmodified` int(10) unsigned DEFAULT NULL,
  `etag` varbinary(32) DEFAULT NULL,
  `size` int(10) unsigned NOT NULL,
  `crmid` int(10) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `addressbookid` (`addressbookid`),
  KEY `crmid` (`crmid`),
  KEY `uri` (`uri`),
  CONSTRAINT `dav_cards_ibfk_1` FOREIGN KEY (`addressbookid`) REFERENCES `dav_addressbooks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_groupmembers` */

CREATE TABLE `dav_groupmembers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `principal_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `principal_id` (`principal_id`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_principals` */

CREATE TABLE `dav_principals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varbinary(200) NOT NULL,
  `email` varbinary(80) DEFAULT NULL,
  `displayname` varchar(80) DEFAULT NULL,
  `userid` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_propertystorage` */

CREATE TABLE `dav_propertystorage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `path` varbinary(1024) NOT NULL,
  `name` varbinary(100) NOT NULL,
  `valuetype` int(10) unsigned DEFAULT NULL,
  `value` mediumblob DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path_property` (`path`(600),`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_schedulingobjects` */

CREATE TABLE `dav_schedulingobjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `principaluri` varbinary(255) DEFAULT NULL,
  `calendardata` mediumblob DEFAULT NULL,
  `uri` varbinary(200) DEFAULT NULL,
  `lastmodified` int(10) unsigned DEFAULT NULL,
  `etag` varbinary(32) DEFAULT NULL,
  `size` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `dav_users` */

CREATE TABLE `dav_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varbinary(50) DEFAULT NULL,
  `digesta1` varbinary(32) DEFAULT NULL,
  `userid` int(10) unsigned DEFAULT NULL,
  `key` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `l_yf_batchmethod` */

CREATE TABLE `l_yf_batchmethod` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `method` varchar(50) NOT NULL,
  `params` text DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `userid` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `message` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `l_yf_mail` */

CREATE TABLE `l_yf_mail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `error_code` int(10) unsigned NOT NULL,
  `smtp_id` int(10) unsigned NOT NULL DEFAULT 1,
  `owner` int(10) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `from` text DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `to` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `cc` text DEFAULT NULL,
  `bcc` text DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smtp_id` (`smtp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `l_yf_profile` */

CREATE TABLE `l_yf_profile` (
  `id` int(10) unsigned NOT NULL DEFAULT 0,
  `category` varchar(255) NOT NULL,
  `info` text DEFAULT NULL,
  `log_time` varchar(20) NOT NULL,
  `trace` text DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `duration` decimal(7,3) NOT NULL,
  KEY `id` (`id`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `l_yf_settings_tracker_basic` */

CREATE TABLE `l_yf_settings_tracker_basic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `type` tinyint(1) NOT NULL,
  `action` varchar(50) NOT NULL,
  `record_id` int(10) DEFAULT NULL,
  `module_name` varchar(50) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `l_yf_settings_tracker_detail` */

CREATE TABLE `l_yf_settings_tracker_detail` (
  `id` int(10) unsigned NOT NULL,
  `prev_value` text NOT NULL DEFAULT '',
  `post_value` text NOT NULL DEFAULT '',
  `field` varchar(255) NOT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `l_yf_social_media_logs` */

CREATE TABLE `l_yf_social_media_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `type` varchar(16) NOT NULL,
  `name` varchar(16) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `type` (`type`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `l_yf_sqltime` */

CREATE TABLE `l_yf_sqltime` (
  `id` int(10) NOT NULL,
  `type` varchar(20) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `qtime` decimal(20,3) DEFAULT NULL,
  `group` int(10) DEFAULT NULL,
  KEY `id` (`id`),
  KEY `type` (`type`),
  KEY `group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `l_yf_switch_users` */

CREATE TABLE `l_yf_switch_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `status` varchar(10) NOT NULL,
  `baseid` int(10) NOT NULL,
  `destid` int(10) NOT NULL,
  `busername` varchar(50) NOT NULL,
  `dusername` varchar(50) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `agent` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `baseid` (`baseid`),
  KEY `destid` (`destid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `l_yf_username_history` */

CREATE TABLE `l_yf_username_history` (
  `user_name` varchar(64) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  KEY `user_id` (`user_id`),
  KEY `user_name` (`user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `l_yf_userpass_history` */

CREATE TABLE `l_yf_userpass_history` (
  `pass` varchar(200) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  KEY `user_id` (`user_id`,`pass`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `o_yf_access_for_admin` */

CREATE TABLE `o_yf_access_for_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(100) NOT NULL,
  `module` varchar(30) NOT NULL,
  `url` varchar(300) NOT NULL,
  `agent` varchar(255) NOT NULL,
  `request` varchar(300) NOT NULL,
  `referer` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `o_yf_access_for_api` */

CREATE TABLE `o_yf_access_for_api` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(100) NOT NULL,
  `url` varchar(300) NOT NULL,
  `agent` varchar(255) NOT NULL,
  `request` varchar(300) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `o_yf_access_for_user` */

CREATE TABLE `o_yf_access_for_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `module` varchar(30) NOT NULL,
  `url` varchar(300) NOT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `request` varchar(300) NOT NULL,
  `referer` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `o_yf_access_to_record` */

CREATE TABLE `o_yf_access_to_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(100) NOT NULL,
  `record` int(10) NOT NULL,
  `module` varchar(30) NOT NULL,
  `url` varchar(300) NOT NULL,
  `agent` varchar(255) NOT NULL,
  `request` varchar(300) NOT NULL,
  `referer` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `o_yf_csrf` */

CREATE TABLE `o_yf_csrf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(100) NOT NULL,
  `referer` varchar(300) NOT NULL,
  `url` varchar(300) NOT NULL,
  `agent` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_cache` */

CREATE TABLE `roundcube_cache` (
  `user_id` int(10) unsigned NOT NULL,
  `cache_key` varchar(128) CHARACTER SET ascii NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `expires` datetime DEFAULT NULL,
  `data` longtext NOT NULL,
  KEY `expires_index` (`expires`),
  KEY `user_cache_index` (`user_id`,`cache_key`),
  CONSTRAINT `roundcube_user_id_fk_cache` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_cache_index` */

CREATE TABLE `roundcube_cache_index` (
  `user_id` int(10) unsigned NOT NULL,
  `mailbox` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `expires` datetime DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT 0,
  `data` longtext NOT NULL,
  PRIMARY KEY (`user_id`,`mailbox`),
  KEY `expires_index` (`expires`),
  CONSTRAINT `roundcube_user_id_fk_cache_index` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_cache_messages` */

CREATE TABLE `roundcube_cache_messages` (
  `user_id` int(10) unsigned NOT NULL,
  `mailbox` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `uid` int(10) unsigned NOT NULL DEFAULT 0,
  `expires` datetime DEFAULT NULL,
  `data` longtext NOT NULL,
  `flags` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`mailbox`,`uid`),
  KEY `expires_index` (`expires`),
  CONSTRAINT `roundcube_user_id_fk_cache_messages` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_cache_shared` */

CREATE TABLE `roundcube_cache_shared` (
  `cache_key` varchar(255) CHARACTER SET ascii NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `expires` datetime DEFAULT NULL,
  `data` longtext NOT NULL,
  KEY `expires_index` (`expires`),
  KEY `cache_key_index` (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_cache_thread` */

CREATE TABLE `roundcube_cache_thread` (
  `user_id` int(10) unsigned NOT NULL,
  `mailbox` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `expires` datetime DEFAULT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`user_id`,`mailbox`),
  KEY `expires_index` (`expires`),
  CONSTRAINT `roundcube_user_id_fk_cache_thread` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_contactgroupmembers` */

CREATE TABLE `roundcube_contactgroupmembers` (
  `contactgroup_id` int(10) unsigned NOT NULL,
  `contact_id` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`contactgroup_id`,`contact_id`),
  KEY `roundcube_contactgroupmembers_contact_index` (`contact_id`),
  CONSTRAINT `roundcube_contact_id_fk_contacts` FOREIGN KEY (`contact_id`) REFERENCES `roundcube_contacts` (`contact_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `roundcube_contactgroup_id_fk_contactgroups` FOREIGN KEY (`contactgroup_id`) REFERENCES `roundcube_contactgroups` (`contactgroup_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_contactgroups` */

CREATE TABLE `roundcube_contactgroups` (
  `contactgroup_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `del` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`contactgroup_id`),
  KEY `roundcube_contactgroups_user_index` (`user_id`,`del`),
  CONSTRAINT `roundcube_user_id_fk_contactgroups` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_contacts` */

CREATE TABLE `roundcube_contacts` (
  `contact_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `del` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(128) NOT NULL DEFAULT '',
  `email` text NOT NULL,
  `firstname` varchar(128) NOT NULL DEFAULT '',
  `surname` varchar(128) NOT NULL DEFAULT '',
  `vcard` longtext DEFAULT NULL,
  `words` text DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`contact_id`),
  KEY `roundcube_user_contacts_index` (`user_id`,`del`),
  CONSTRAINT `roundcube_user_id_fk_contacts` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_dictionary` */

CREATE TABLE `roundcube_dictionary` (
  `user_id` int(10) unsigned DEFAULT NULL,
  `language` varchar(5) NOT NULL,
  `data` longtext NOT NULL,
  UNIQUE KEY `uniqueness` (`user_id`,`language`),
  CONSTRAINT `roundcube_user_id_fk_dictionary` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_identities` */

CREATE TABLE `roundcube_identities` (
  `identity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `del` tinyint(1) NOT NULL DEFAULT 0,
  `standard` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(128) NOT NULL,
  `organization` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL,
  `reply-to` varchar(128) NOT NULL DEFAULT '',
  `bcc` varchar(128) NOT NULL DEFAULT '',
  `signature` longtext DEFAULT NULL,
  `html_signature` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`identity_id`),
  KEY `user_identities_index` (`user_id`,`del`),
  KEY `email_identities_index` (`email`,`del`),
  CONSTRAINT `roundcube_user_id_fk_identities` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_searches` */

CREATE TABLE `roundcube_searches` (
  `search_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` int(3) NOT NULL DEFAULT 0,
  `name` varchar(128) NOT NULL,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`search_id`),
  UNIQUE KEY `uniqueness` (`user_id`,`type`,`name`),
  CONSTRAINT `roundcube_user_id_fk_searches` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_session` */

CREATE TABLE `roundcube_session` (
  `sess_id` varchar(128) NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `ip` varchar(40) NOT NULL,
  `vars` mediumtext NOT NULL,
  PRIMARY KEY (`sess_id`),
  KEY `changed_index` (`changed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_system` */

CREATE TABLE `roundcube_system` (
  `name` varchar(64) NOT NULL,
  `value` mediumtext DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_users` */

CREATE TABLE `roundcube_users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `mail_host` varchar(128) NOT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `last_login` datetime DEFAULT NULL,
  `failed_login` datetime DEFAULT NULL,
  `failed_login_counter` int(10) unsigned DEFAULT NULL,
  `language` varchar(5) DEFAULT NULL,
  `preferences` longtext DEFAULT NULL,
  `actions` text DEFAULT NULL,
  `password` varchar(200) DEFAULT NULL,
  `crm_user_id` int(10) DEFAULT 0,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`,`mail_host`),
  KEY `crm_user_id` (`crm_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roundcube_users_autologin` */

CREATE TABLE `roundcube_users_autologin` (
  `rcuser_id` int(10) unsigned NOT NULL,
  `crmuser_id` int(10) NOT NULL,
  KEY `rcuser_id` (`rcuser_id`),
  CONSTRAINT `roundcube_users_autologin_ibfk_1` FOREIGN KEY (`rcuser_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_address_finder` */

CREATE TABLE `s_yf_address_finder` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `level1` varchar(100) DEFAULT NULL,
  `level2` varchar(100) DEFAULT NULL,
  `level3` varchar(100) DEFAULT NULL,
  `level4` varchar(100) DEFAULT NULL,
  `level5` varchar(100) DEFAULT NULL,
  `level6` varchar(100) DEFAULT NULL,
  `level7` varchar(100) DEFAULT NULL,
  `level8` varchar(100) DEFAULT NULL,
  `source` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `source` (`source`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_address_finder_config` */

CREATE TABLE `s_yf_address_finder_config` (
  `id` smallint(4) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `val` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_automatic_assignment` */

CREATE TABLE `s_yf_automatic_assignment` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tabid` smallint(5) unsigned NOT NULL,
  `field` varchar(30) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `roles` text DEFAULT NULL,
  `smowners` text DEFAULT NULL,
  `assign` smallint(5) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `conditions` text DEFAULT NULL,
  `user_limit` tinyint(1) DEFAULT NULL,
  `roleid` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_batchmethod` */

CREATE TABLE `s_yf_batchmethod` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `method` varchar(50) NOT NULL,
  `params` text NOT NULL,
  `created_time` date NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `userid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_companies` */

CREATE TABLE `s_yf_companies` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `industry` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `companysize` mediumint(6) unsigned DEFAULT 0,
  `website` varchar(255) DEFAULT NULL,
  `logo` longtext DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_mail_queue` */

CREATE TABLE `s_yf_mail_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smtp_id` int(10) unsigned NOT NULL DEFAULT 1,
  `date` datetime NOT NULL,
  `owner` int(10) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `from` text DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `to` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `cc` text DEFAULT NULL,
  `bcc` text DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `priority` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `error` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smtp_id` (`smtp_id`),
  KEY `status` (`status`),
  CONSTRAINT `s_yf_mail_queue_ibfk_1` FOREIGN KEY (`smtp_id`) REFERENCES `s_yf_mail_smtp` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_mail_relation_updater` */

CREATE TABLE `s_yf_mail_relation_updater` (
  `tabid` smallint(5) unsigned NOT NULL,
  `crmid` int(10) unsigned NOT NULL,
  KEY `tabid` (`tabid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_mail_smtp` */

CREATE TABLE `s_yf_mail_smtp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mailer_type` varchar(10) DEFAULT 'smtp',
  `default` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `port` smallint(5) unsigned DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `authentication` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `secure` varchar(10) DEFAULT NULL,
  `options` text DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `reply_to` varchar(255) DEFAULT NULL,
  `individual_delivery` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `params` text DEFAULT NULL,
  `save_send_mail` tinyint(1) DEFAULT 0,
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` smallint(5) DEFAULT NULL,
  `smtp_username` varchar(255) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `smtp_folder` varchar(50) DEFAULT NULL,
  `smtp_validate_cert` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_multireference` */

CREATE TABLE `s_yf_multireference` (
  `source_module` varchar(50) NOT NULL,
  `dest_module` varchar(50) NOT NULL,
  `lastid` int(10) unsigned NOT NULL DEFAULT 0,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  KEY `source_module` (`source_module`,`dest_module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_pbx` */

CREATE TABLE `s_yf_pbx` (
  `pbxid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `default` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `name` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `param` text DEFAULT NULL,
  PRIMARY KEY (`pbxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_privileges_updater` */

CREATE TABLE `s_yf_privileges_updater` (
  `module` varchar(30) NOT NULL DEFAULT '',
  `crmid` int(10) NOT NULL DEFAULT 0,
  `priority` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  UNIQUE KEY `module` (`module`,`crmid`,`type`),
  KEY `crmid` (`crmid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `s_yf_smsnotifier_queue` */

CREATE TABLE `s_yf_smsnotifier_queue` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `message` varchar(255) NOT NULL,
  `tonumbers` text NOT NULL,
  `records` text NOT NULL,
  `module` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_activity_invitation` */

CREATE TABLE `u_yf_activity_invitation` (
  `inviteesid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `activityid` int(10) NOT NULL,
  `crmid` int(10) NOT NULL DEFAULT 0,
  `email` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(500) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`inviteesid`),
  KEY `activityid` (`activityid`),
  CONSTRAINT `u_yf_activity_invitation_ibfk_1` FOREIGN KEY (`activityid`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_activityregister` */

CREATE TABLE `u_yf_activityregister` (
  `activityregisterid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `activityregister_status` varchar(255) DEFAULT '',
  `datasetregisterid` int(11) unsigned DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `activity_type` text DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`activityregisterid`),
  KEY `u_yf_activityregister_datasetregisterid_idx` (`datasetregisterid`),
  KEY `u_yf_activityregister_parent_id_idx` (`parent_id`),
  CONSTRAINT `fk_1_u_yf_activityregisteractivityregisterid` FOREIGN KEY (`activityregisterid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_activityregistercf` */

CREATE TABLE `u_yf_activityregistercf` (
  `activityregisterid` int(10) NOT NULL,
  PRIMARY KEY (`activityregisterid`),
  CONSTRAINT `fk_1_u_yf_activityregistercfactivityregisterid` FOREIGN KEY (`activityregisterid`) REFERENCES `u_yf_activityregister` (`activityregisterid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_announcement` */

CREATE TABLE `u_yf_announcement` (
  `announcementid` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `announcement_no` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `announcementstatus` varchar(255) NOT NULL DEFAULT '',
  `interval` smallint(5) DEFAULT NULL,
  `is_mandatory` smallint(5) DEFAULT NULL,
  PRIMARY KEY (`announcementid`),
  KEY `announcementstatus` (`announcementstatus`),
  CONSTRAINT `fk_1_u_yf_announcement` FOREIGN KEY (`announcementid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_announcement_mark` */

CREATE TABLE `u_yf_announcement_mark` (
  `announcementid` int(10) NOT NULL,
  `userid` int(10) NOT NULL,
  `date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`announcementid`,`userid`),
  KEY `userid` (`userid`,`status`),
  KEY `announcementid` (`announcementid`,`userid`,`date`,`status`),
  CONSTRAINT `u_yf_announcement_mark_ibfk_1` FOREIGN KEY (`announcementid`) REFERENCES `u_yf_announcement` (`announcementid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_announcementcf` */

CREATE TABLE `u_yf_announcementcf` (
  `announcementid` int(10) NOT NULL,
  PRIMARY KEY (`announcementid`),
  CONSTRAINT `fk_1_u_yf_announcementcf` FOREIGN KEY (`announcementid`) REFERENCES `u_yf_announcement` (`announcementid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_auditregister` */

CREATE TABLE `u_yf_auditregister` (
  `auditregisterid` int(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `locationregisterid` int(11) unsigned DEFAULT 0,
  `datasetregisterid` int(11) unsigned DEFAULT 0,
  `auditregister_status` varchar(255) DEFAULT '',
  `auditregister_type` varchar(255) DEFAULT '',
  PRIMARY KEY (`auditregisterid`),
  KEY `u_yf_auditregister_locationregisterid_idx` (`locationregisterid`),
  KEY `u_yf_auditregister_datasetregisterid_idx` (`datasetregisterid`),
  CONSTRAINT `fk_1_u_yf_auditregisterauditregisterid` FOREIGN KEY (`auditregisterid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_auditregistercf` */

CREATE TABLE `u_yf_auditregistercf` (
  `auditregisterid` int(10) NOT NULL,
  PRIMARY KEY (`auditregisterid`),
  CONSTRAINT `fk_1_u_yf_auditregistercfauditregisterid` FOREIGN KEY (`auditregisterid`) REFERENCES `u_yf_auditregister` (`auditregisterid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_browsinghistory` */

CREATE TABLE `u_yf_browsinghistory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `date` datetime DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `url` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `browsinghistory_user_idx` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_cfixedassets` */

CREATE TABLE `u_yf_cfixedassets` (
  `cfixedassetsid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `fixed_assets_type` varchar(255) DEFAULT NULL,
  `fixed_assets_status` varchar(255) DEFAULT NULL,
  `producent_designation` varchar(255) DEFAULT NULL,
  `additional_designation` varchar(255) DEFAULT NULL,
  `internal_designation` varchar(255) DEFAULT NULL,
  `date_production` date DEFAULT NULL,
  `date_acquisition` date DEFAULT NULL,
  `purchase_price` decimal(25,8) DEFAULT NULL,
  `actual_price` decimal(25,8) DEFAULT NULL,
  `reservation` smallint(1) DEFAULT NULL,
  `pscategory` varchar(255) DEFAULT NULL,
  `fixed_assets_fuel_type` varchar(255) DEFAULT NULL,
  `timing_change` int(10) DEFAULT 0,
  `oil_change` int(10) DEFAULT NULL,
  `fuel_consumption` int(10) DEFAULT NULL,
  `current_odometer_reading` int(10) DEFAULT NULL,
  `number_repair` smallint(5) DEFAULT NULL,
  `date_last_repair` date DEFAULT NULL,
  PRIMARY KEY (`cfixedassetsid`),
  CONSTRAINT `fk_1_vtiger_cfixedassetscfixedassetsid` FOREIGN KEY (`cfixedassetsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_cfixedassetscf` */

CREATE TABLE `u_yf_cfixedassetscf` (
  `cfixedassetsid` int(10) NOT NULL,
  PRIMARY KEY (`cfixedassetsid`),
  CONSTRAINT `fk_1_vtiger_cfixedassetscfcfixedassetsid` FOREIGN KEY (`cfixedassetsid`) REFERENCES `u_yf_cfixedassets` (`cfixedassetsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_chat_global` */

CREATE TABLE `u_yf_chat_global` (
  `global_room_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`global_room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_chat_messages_crm` */

CREATE TABLE `u_yf_chat_messages_crm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `userid` smallint(5) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `messages` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `room_crmid` (`crmid`),
  CONSTRAINT `fk_chat_messages` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_chat_messages_global` */

CREATE TABLE `u_yf_chat_messages_global` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `globalid` int(10) unsigned NOT NULL,
  `userid` smallint(5) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `messages` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `globalid` (`globalid`),
  CONSTRAINT `u_yf_chat_messages_global_ibfk_1` FOREIGN KEY (`globalid`) REFERENCES `u_yf_chat_global` (`global_room_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_chat_messages_group` */

CREATE TABLE `u_yf_chat_messages_group` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupid` int(10) DEFAULT NULL,
  `userid` smallint(5) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `messages` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `room_groupid` (`groupid`),
  CONSTRAINT `fk_chat_group_messages` FOREIGN KEY (`groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_chat_rooms_crm` */

CREATE TABLE `u_yf_chat_rooms_crm` (
  `roomid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `crmid` int(10) DEFAULT NULL,
  `last_message` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`roomid`),
  KEY `u_yf_chat_rooms_crm_userid_idx` (`userid`),
  KEY `u_yf_chat_rooms_crm_crmid_idx` (`crmid`),
  KEY `u_yf_chat_rooms_crm_last_message_idx` (`last_message`),
  CONSTRAINT `fk_u_yf_chat_rooms_crm_crm` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE,
  CONSTRAINT `fk_u_yf_chat_rooms_crm_users` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_chat_rooms_global` */

CREATE TABLE `u_yf_chat_rooms_global` (
  `roomid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `global_room_id` int(10) unsigned NOT NULL,
  `last_message` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`roomid`),
  KEY `global_room_id` (`global_room_id`),
  KEY `userid` (`userid`),
  KEY `last_message` (`last_message`),
  CONSTRAINT `fk_u_yf_chat_rooms_global_global` FOREIGN KEY (`global_room_id`) REFERENCES `u_yf_chat_global` (`global_room_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_u_yf_chat_rooms_global_users` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_chat_rooms_group` */

CREATE TABLE `u_yf_chat_rooms_group` (
  `roomid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `groupid` int(10) NOT NULL,
  `last_message` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`roomid`),
  KEY `userid` (`userid`),
  KEY `u_yf_chat_rooms_group_groupid_idx` (`groupid`),
  CONSTRAINT `fk_u_yf_chat_rooms_group` FOREIGN KEY (`groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE,
  CONSTRAINT `fk_u_yf_chat_rooms_group_users` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_cinternaltickets` */

CREATE TABLE `u_yf_cinternaltickets` (
  `cinternalticketsid` int(10) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `cinternaltickets_no` varchar(32) DEFAULT NULL,
  `internal_tickets_status` varchar(150) DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  PRIMARY KEY (`cinternalticketsid`),
  CONSTRAINT `fk_1_vtiger_cinternalticketscinternalticketsid` FOREIGN KEY (`cinternalticketsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_cinternalticketscf` */

CREATE TABLE `u_yf_cinternalticketscf` (
  `cinternalticketsid` int(10) NOT NULL,
  PRIMARY KEY (`cinternalticketsid`),
  CONSTRAINT `fk_1_vtiger_cinternalticketscfcinternalticketsid` FOREIGN KEY (`cinternalticketsid`) REFERENCES `u_yf_cinternaltickets` (`cinternalticketsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_cmileagelogbook` */

CREATE TABLE `u_yf_cmileagelogbook` (
  `cmileagelogbookid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `cmileage_logbook_status` varchar(150) DEFAULT NULL,
  `number_kilometers` decimal(13,2) DEFAULT NULL,
  PRIMARY KEY (`cmileagelogbookid`),
  CONSTRAINT `fk_1_vtiger_cmileagelogbookcmileagelogbookid` FOREIGN KEY (`cmileagelogbookid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_cmileagelogbookcf` */

CREATE TABLE `u_yf_cmileagelogbookcf` (
  `cmileagelogbookid` int(10) NOT NULL,
  PRIMARY KEY (`cmileagelogbookid`),
  CONSTRAINT `fk_1_vtiger_cmileagelogbookcfcmileagelogbookid` FOREIGN KEY (`cmileagelogbookid`) REFERENCES `u_yf_cmileagelogbook` (`cmileagelogbookid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_competition` */

CREATE TABLE `u_yf_competition` (
  `competitionid` int(10) NOT NULL DEFAULT 0,
  `competition_no` varchar(255) DEFAULT '',
  `subject` varchar(255) DEFAULT NULL,
  `vat_id` varchar(30) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `email` varchar(100) DEFAULT '',
  `active` tinyint(1) DEFAULT 0,
  `parent_id` int(11) unsigned DEFAULT 0,
  PRIMARY KEY (`competitionid`),
  KEY `u_yf_competition_parent_id_idx` (`parent_id`),
  CONSTRAINT `fk_1_u_yf_competition` FOREIGN KEY (`competitionid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_competition_address` */

CREATE TABLE `u_yf_competition_address` (
  `competitionaddressid` int(10) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`competitionaddressid`),
  CONSTRAINT `u_yf_competition_address_ibfk_1` FOREIGN KEY (`competitionaddressid`) REFERENCES `u_yf_competition` (`competitionid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_competitioncf` */

CREATE TABLE `u_yf_competitioncf` (
  `competitionid` int(10) NOT NULL,
  PRIMARY KEY (`competitionid`),
  CONSTRAINT `fk_1_u_yf_competitioncf` FOREIGN KEY (`competitionid`) REFERENCES `u_yf_competition` (`competitionid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_countries` */

CREATE TABLE `u_yf_countries` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` char(2) NOT NULL,
  `status` tinyint(1) unsigned DEFAULT 0,
  `sortorderid` smallint(5) unsigned NOT NULL,
  `phone` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `uitype` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `phone` (`status`,`phone`),
  KEY `uitype` (`status`,`uitype`)
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_crmentity_label` */

CREATE TABLE `u_yf_crmentity_label` (
  `crmid` int(10) unsigned NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`crmid`),
  KEY `crmentity_label` (`label`),
  FULLTEXT KEY `crmentity_label_fulltext` (`label`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_crmentity_last_changes` */

CREATE TABLE `u_yf_crmentity_last_changes` (
  `crmid` int(10) NOT NULL,
  `fieldname` varchar(50) NOT NULL,
  `user_id` int(10) NOT NULL,
  `date_updated` datetime NOT NULL,
  KEY `crmid` (`crmid`,`fieldname`),
  CONSTRAINT `u_yf_crmentity_last_changes_ibfk_1` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_crmentity_rel_tree` */

CREATE TABLE `u_yf_crmentity_rel_tree` (
  `crmid` int(10) NOT NULL,
  `module` int(10) NOT NULL,
  `tree` varchar(50) NOT NULL,
  `relmodule` int(10) NOT NULL,
  `rel_created_user` int(10) NOT NULL,
  `rel_created_time` datetime NOT NULL,
  `rel_comment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_crmentity_search_label` */

CREATE TABLE `u_yf_crmentity_search_label` (
  `crmid` int(10) unsigned NOT NULL,
  `searchlabel` varchar(255) NOT NULL,
  `setype` varchar(30) NOT NULL,
  `userid` text DEFAULT NULL,
  PRIMARY KEY (`crmid`),
  KEY `crmentity_searchlabel_setype` (`searchlabel`,`setype`),
  FULLTEXT KEY `crmentity_searchlabel_fulltext` (`searchlabel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_crmentity_showners` */

CREATE TABLE `u_yf_crmentity_showners` (
  `crmid` int(10) DEFAULT NULL,
  `userid` smallint(5) unsigned NOT NULL,
  UNIQUE KEY `mix` (`crmid`,`userid`),
  KEY `crmid` (`crmid`),
  KEY `userid` (`userid`),
  CONSTRAINT `fk_u_yf_crmentity_showners` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_cv_condition` */

CREATE TABLE `u_yf_cv_condition` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned DEFAULT NULL,
  `field_name` varchar(50) DEFAULT NULL,
  `module_name` varchar(25) DEFAULT NULL,
  `source_field_name` varchar(50) DEFAULT NULL,
  `operator` varchar(20) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `index` tinyint(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_cv_condition_fk` (`group_id`),
  CONSTRAINT `u_yf_cv_condition_fk` FOREIGN KEY (`group_id`) REFERENCES `u_yf_cv_condition_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_cv_condition_group` */

CREATE TABLE `u_yf_cv_condition_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cvid` int(10) DEFAULT NULL,
  `condition` varchar(3) DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `index` tinyint(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_cv_condition_group_cvid_idx` (`cvid`),
  CONSTRAINT `u_yf_cv_condition_group_fk` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_cv_duplicates` */

CREATE TABLE `u_yf_cv_duplicates` (
  `cvid` int(10) DEFAULT NULL,
  `fieldid` int(10) DEFAULT NULL,
  `ignore` tinyint(1) NOT NULL DEFAULT 0,
  KEY `u_yf_cv_duplicates_cvid_idx` (`cvid`),
  KEY `u_yf_cv_duplicates_fieldid_idx` (`fieldid`),
  CONSTRAINT `u_yf_cv_duplicates_cvid_fk` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE,
  CONSTRAINT `u_yf_cv_duplicates_fieldid_fk` FOREIGN KEY (`fieldid`) REFERENCES `vtiger_field` (`fieldid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_dashboard_type` */

CREATE TABLE `u_yf_dashboard_type` (
  `dashboard_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `system` smallint(1) DEFAULT 0,
  PRIMARY KEY (`dashboard_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_datasetregister` */

CREATE TABLE `u_yf_datasetregister` (
  `datasetregisterid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `datasetregister_status` varchar(255) DEFAULT '',
  `legal_basis` text DEFAULT NULL,
  `scope_data` text DEFAULT NULL,
  `registered_dpo` tinyint(1) DEFAULT 0,
  `data_submitted` tinyint(1) DEFAULT 0,
  `internal_register` tinyint(1) DEFAULT 0,
  `data_set_shared` tinyint(1) DEFAULT 0,
  `added_to_register` date DEFAULT NULL,
  `removed_from_register` date DEFAULT NULL,
  `parent_id` int(10) NOT NULL,
  PRIMARY KEY (`datasetregisterid`),
  KEY `u_yf_datasetregister_parent_id_idx` (`parent_id`),
  CONSTRAINT `fk_1_u_yf_datasetregisterdatasetregisterid` FOREIGN KEY (`datasetregisterid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_datasetregistercf` */

CREATE TABLE `u_yf_datasetregistercf` (
  `datasetregisterid` int(10) NOT NULL,
  PRIMARY KEY (`datasetregisterid`),
  CONSTRAINT `fk_1_u_yf_datasetregistercfdatasetregisterid` FOREIGN KEY (`datasetregisterid`) REFERENCES `u_yf_datasetregister` (`datasetregisterid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_documents_emailtemplates` */

CREATE TABLE `u_yf_documents_emailtemplates` (
  `crmid` int(10) DEFAULT NULL,
  `relcrmid` int(10) DEFAULT NULL,
  KEY `u_yf_documents_emailtemplates_crmid_idx` (`crmid`),
  KEY `u_yf_documents_emailtemplates_relcrmid_idx` (`relcrmid`),
  CONSTRAINT `fk_1_u_yf_documents_emailtemplates` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE,
  CONSTRAINT `fk_2_u_yf_documents_emailtemplates` FOREIGN KEY (`relcrmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_emailtemplates` */

CREATE TABLE `u_yf_emailtemplates` (
  `emailtemplatesid` int(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `email_template_type` varchar(50) DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `sys_name` varchar(50) DEFAULT NULL,
  `email_template_priority` varchar(1) DEFAULT '1',
  `smtp_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`emailtemplatesid`),
  KEY `sys_name` (`sys_name`),
  CONSTRAINT `fk_1_vtiger_emailtemplatesemailtemplatesid` FOREIGN KEY (`emailtemplatesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_favorite_owners` */

CREATE TABLE `u_yf_favorite_owners` (
  `tabid` smallint(5) NOT NULL,
  `userid` int(10) NOT NULL,
  `ownerid` int(10) NOT NULL,
  KEY `u_yf_favorite_owners_tabid_idx` (`tabid`),
  KEY `u_yf_favorite_owners_userid_idx` (`userid`),
  CONSTRAINT `u_yf_favorite_owners_tabid_fk` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE,
  CONSTRAINT `u_yf_favorite_owners_userid_fk` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_favorite_shared_owners` */

CREATE TABLE `u_yf_favorite_shared_owners` (
  `tabid` smallint(5) NOT NULL,
  `userid` int(10) NOT NULL,
  `ownerid` int(10) NOT NULL,
  KEY `u_yf_favorite_shared_owners_tabid_idx` (`tabid`),
  KEY `u_yf_favorite_shared_owners_userid_idx` (`userid`),
  CONSTRAINT `u_yf_favorite_shared_owners_tabid_fk` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE,
  CONSTRAINT `u_yf_favorite_shared_owners_userid_fk` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_favorites` */

CREATE TABLE `u_yf_favorites` (
  `crmid` int(10) DEFAULT NULL,
  `module` varchar(30) DEFAULT NULL,
  `relcrmid` int(10) DEFAULT NULL,
  `relmodule` varchar(30) DEFAULT NULL,
  `userid` int(10) DEFAULT NULL,
  `data` timestamp NULL DEFAULT NULL,
  KEY `crmid` (`crmid`),
  KEY `relcrmid` (`relcrmid`),
  KEY `mix` (`crmid`,`module`,`relcrmid`,`relmodule`,`userid`),
  CONSTRAINT `fk_1_u_yf_favorites` FOREIGN KEY (`relcrmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE,
  CONSTRAINT `fk_u_yf_favorites` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_fbookkeeping` */

CREATE TABLE `u_yf_fbookkeeping` (
  `fbookkeepingid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `related_to` int(10) DEFAULT NULL,
  PRIMARY KEY (`fbookkeepingid`),
  KEY `related_to` (`related_to`),
  CONSTRAINT `u_yf_fbookkeeping_ibfk_1` FOREIGN KEY (`fbookkeepingid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_fbookkeepingcf` */

CREATE TABLE `u_yf_fbookkeepingcf` (
  `fbookkeepingid` int(10) NOT NULL,
  PRIMARY KEY (`fbookkeepingid`),
  CONSTRAINT `u_yf_fbookkeepingcf_ibfk_1` FOREIGN KEY (`fbookkeepingid`) REFERENCES `u_yf_fbookkeeping` (`fbookkeepingid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_fcorectinginvoice` */

CREATE TABLE `u_yf_fcorectinginvoice` (
  `fcorectinginvoiceid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `paymentdate` date DEFAULT NULL,
  `saledate` date DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `fcorectinginvoice_formpayment` varchar(255) DEFAULT '',
  `sum_total` decimal(16,5) DEFAULT NULL,
  `sum_gross` decimal(16,5) DEFAULT NULL,
  `fcorectinginvoice_status` varchar(255) DEFAULT '',
  `finvoiceid` int(10) DEFAULT NULL,
  `externalcomment` text DEFAULT NULL,
  `internalcomment` text DEFAULT NULL,
  PRIMARY KEY (`fcorectinginvoiceid`),
  KEY `accountid` (`accountid`),
  KEY `finvoiceid` (`finvoiceid`),
  CONSTRAINT `fk_1_vtiger_fcorectinginvoice` FOREIGN KEY (`fcorectinginvoiceid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_fcorectinginvoice_address` */

CREATE TABLE `u_yf_fcorectinginvoice_address` (
  `fcorectinginvoiceaddressid` int(10) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(50) DEFAULT NULL,
  `localnumbera` varchar(50) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`fcorectinginvoiceaddressid`),
  CONSTRAINT `u_yf_fcorectinginvoice_address_ibfk_1` FOREIGN KEY (`fcorectinginvoiceaddressid`) REFERENCES `u_yf_fcorectinginvoice` (`fcorectinginvoiceid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_fcorectinginvoice_inventory` */

CREATE TABLE `u_yf_fcorectinginvoice_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `discount` decimal(28,8) DEFAULT 0.00000000,
  `discountparam` varchar(255) DEFAULT NULL,
  `comment1` text DEFAULT NULL,
  `currency` int(10) DEFAULT NULL,
  `currencyparam` varchar(1024) DEFAULT NULL,
  `discountmode` tinyint(1) NOT NULL DEFAULT 0,
  `taxmode` tinyint(1) NOT NULL DEFAULT 0,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `gross` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `net` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `tax` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `taxparam` varchar(255) NOT NULL,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `unit` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_fcorectinginvoice_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_fcorectinginvoice_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_fcorectinginvoice` (`fcorectinginvoiceid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_fcorectinginvoice_invfield` */

CREATE TABLE `u_yf_fcorectinginvoice_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_fcorectinginvoice_invmap` */

CREATE TABLE `u_yf_fcorectinginvoice_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_fcorectinginvoicecf` */

CREATE TABLE `u_yf_fcorectinginvoicecf` (
  `fcorectinginvoiceid` int(10) NOT NULL,
  PRIMARY KEY (`fcorectinginvoiceid`),
  CONSTRAINT `fk_1_u_yf_fcorectinginvoicecf` FOREIGN KEY (`fcorectinginvoiceid`) REFERENCES `u_yf_fcorectinginvoice` (`fcorectinginvoiceid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_featured_filter` */

CREATE TABLE `u_yf_featured_filter` (
  `user` varchar(30) NOT NULL,
  `cvid` int(10) NOT NULL,
  PRIMARY KEY (`user`,`cvid`),
  KEY `cvid` (`cvid`),
  KEY `user` (`user`),
  CONSTRAINT `u_yf_featured_filter_ibfk_1` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_file_upload_temp` */

CREATE TABLE `u_yf_file_upload_temp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `path` text NOT NULL,
  `status` tinyint(1) DEFAULT 0,
  `fieldname` varchar(50) DEFAULT NULL,
  `crmid` int(10) DEFAULT NULL,
  `createdtime` datetime DEFAULT NULL,
  `key` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `crmid` (`crmid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoice` */

CREATE TABLE `u_yf_finvoice` (
  `finvoiceid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `paymentdate` date DEFAULT NULL,
  `saledate` date DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `finvoice_formpayment` varchar(255) DEFAULT '',
  `sum_total` decimal(16,5) DEFAULT NULL,
  `sum_gross` decimal(16,5) DEFAULT NULL,
  `finvoice_status` varchar(255) DEFAULT '',
  `finvoice_paymentstatus` varchar(255) DEFAULT NULL,
  `finvoice_type` varchar(255) DEFAULT NULL,
  `pscategory` varchar(100) DEFAULT NULL,
  `issue_time` date DEFAULT NULL,
  `ssalesprocessesid` int(10) DEFAULT NULL,
  `projectid` int(10) DEFAULT NULL,
  PRIMARY KEY (`finvoiceid`),
  KEY `accountid` (`accountid`),
  KEY `u_yf_finvoice_ssalesprocessesid_idx` (`ssalesprocessesid`),
  KEY `u_yf_finvoice_projectid_idx` (`projectid`),
  CONSTRAINT `fk_1_vtiger_finvoice` FOREIGN KEY (`finvoiceid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoice_address` */

CREATE TABLE `u_yf_finvoice_address` (
  `finvoiceaddressid` int(10) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(50) DEFAULT NULL,
  `localnumbera` varchar(50) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`finvoiceaddressid`),
  CONSTRAINT `u_yf_finvoice_address_ibfk_1` FOREIGN KEY (`finvoiceaddressid`) REFERENCES `u_yf_finvoice` (`finvoiceid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoice_inventory` */

CREATE TABLE `u_yf_finvoice_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `discountmode` tinyint(1) NOT NULL DEFAULT 0,
  `taxmode` tinyint(1) NOT NULL DEFAULT 0,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `discount` decimal(28,8) DEFAULT 0.00000000,
  `gross` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `net` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `tax` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `taxparam` varchar(255) NOT NULL,
  `discountparam` varchar(255) DEFAULT NULL,
  `comment1` text DEFAULT NULL,
  `currency` int(10) DEFAULT NULL,
  `currencyparam` varchar(1024) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_finvoice_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_finvoice_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_finvoice` (`finvoiceid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoice_invfield` */

CREATE TABLE `u_yf_finvoice_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoice_invmap` */

CREATE TABLE `u_yf_finvoice_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoicecf` */

CREATE TABLE `u_yf_finvoicecf` (
  `finvoiceid` int(10) NOT NULL,
  PRIMARY KEY (`finvoiceid`),
  CONSTRAINT `fk_1_u_yf_finvoicecf` FOREIGN KEY (`finvoiceid`) REFERENCES `u_yf_finvoice` (`finvoiceid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoicecost` */

CREATE TABLE `u_yf_finvoicecost` (
  `finvoicecostid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `paymentdate` date DEFAULT NULL,
  `saledate` date DEFAULT NULL,
  `finvoicecost_formpayment` varchar(255) DEFAULT '',
  `sum_total` decimal(16,5) DEFAULT NULL,
  `sum_gross` decimal(16,5) DEFAULT NULL,
  `finvoicecost_status` varchar(255) DEFAULT '',
  `finvoicecost_paymentstatus` varchar(255) DEFAULT NULL,
  `pscategory` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`finvoicecostid`),
  CONSTRAINT `fk_1_vtiger_finvoicecost` FOREIGN KEY (`finvoicecostid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoicecost_address` */

CREATE TABLE `u_yf_finvoicecost_address` (
  `finvoicecostaddressid` int(10) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(50) DEFAULT NULL,
  `localnumbera` varchar(50) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`finvoicecostaddressid`),
  CONSTRAINT `u_yf_finvoicecost_address_ibfk_1` FOREIGN KEY (`finvoicecostaddressid`) REFERENCES `u_yf_finvoicecost` (`finvoicecostid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoicecost_inventory` */

CREATE TABLE `u_yf_finvoicecost_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) DEFAULT 0,
  `qty` decimal(25,3) DEFAULT 0.000,
  `discount` decimal(28,8) DEFAULT 0.00000000,
  `discountparam` varchar(255) DEFAULT NULL,
  `comment1` text DEFAULT NULL,
  `currency` int(10) DEFAULT NULL,
  `currencyparam` varchar(1024) DEFAULT NULL,
  `discountmode` smallint(1) DEFAULT 0,
  `taxmode` smallint(1) DEFAULT 0,
  `price` decimal(28,8) DEFAULT 0.00000000,
  `gross` decimal(28,8) DEFAULT 0.00000000,
  `net` decimal(28,8) DEFAULT 0.00000000,
  `tax` decimal(28,8) DEFAULT 0.00000000,
  `taxparam` varchar(255) DEFAULT NULL,
  `total` decimal(28,8) DEFAULT 0.00000000,
  `unit` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_finvoicecost_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_finvoicecost_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_finvoicecost` (`finvoicecostid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoicecost_invfield` */

CREATE TABLE `u_yf_finvoicecost_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` smallint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` smallint(1) unsigned NOT NULL,
  `displaytype` smallint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` smallint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoicecost_invmap` */

CREATE TABLE `u_yf_finvoicecost_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoicecostcf` */

CREATE TABLE `u_yf_finvoicecostcf` (
  `finvoicecostid` int(10) NOT NULL,
  PRIMARY KEY (`finvoicecostid`),
  CONSTRAINT `fk_1_u_yf_finvoicecostcf` FOREIGN KEY (`finvoicecostid`) REFERENCES `u_yf_finvoicecost` (`finvoicecostid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoiceproforma` */

CREATE TABLE `u_yf_finvoiceproforma` (
  `finvoiceproformaid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `paymentdate` date DEFAULT NULL,
  `saledate` date DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `finvoiceproforma_formpayment` varchar(255) DEFAULT NULL,
  `sum_total` decimal(15,2) DEFAULT NULL,
  `sum_gross` decimal(13,2) DEFAULT NULL,
  `finvoiceproforma_status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`finvoiceproformaid`),
  KEY `accountid` (`accountid`),
  CONSTRAINT `fk_1_vtiger_finvoiceproforma` FOREIGN KEY (`finvoiceproformaid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoiceproforma_address` */

CREATE TABLE `u_yf_finvoiceproforma_address` (
  `finvoiceproformaaddressid` int(10) NOT NULL,
  `addresslevel1c` varchar(255) DEFAULT NULL,
  `addresslevel2c` varchar(255) DEFAULT NULL,
  `addresslevel3c` varchar(255) DEFAULT NULL,
  `addresslevel4c` varchar(255) DEFAULT NULL,
  `addresslevel5c` varchar(255) DEFAULT NULL,
  `addresslevel6c` varchar(255) DEFAULT NULL,
  `addresslevel7c` varchar(255) DEFAULT NULL,
  `addresslevel8c` varchar(255) DEFAULT NULL,
  `buildingnumberc` varchar(255) DEFAULT NULL,
  `localnumberc` varchar(255) DEFAULT NULL,
  `poboxc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`finvoiceproformaaddressid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoiceproforma_inventory` */

CREATE TABLE `u_yf_finvoiceproforma_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `currency` int(10) DEFAULT NULL,
  `currencyparam` varchar(1024) DEFAULT NULL,
  `discountmode` tinyint(1) NOT NULL DEFAULT 0,
  `taxmode` tinyint(1) NOT NULL DEFAULT 0,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `discount` decimal(28,8) DEFAULT 0.00000000,
  `discountparam` varchar(255) DEFAULT NULL,
  `net` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `tax` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `taxparam` varchar(255) NOT NULL,
  `gross` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_finvoiceproforma_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_finvoiceproforma_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_finvoiceproforma` (`finvoiceproformaid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoiceproforma_invfield` */

CREATE TABLE `u_yf_finvoiceproforma_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoiceproforma_invmap` */

CREATE TABLE `u_yf_finvoiceproforma_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_finvoiceproformacf` */

CREATE TABLE `u_yf_finvoiceproformacf` (
  `finvoiceproformaid` int(10) NOT NULL,
  PRIMARY KEY (`finvoiceproformaid`),
  CONSTRAINT `fk_1_vtiger_finvoiceproformacf` FOREIGN KEY (`finvoiceproformaid`) REFERENCES `u_yf_finvoiceproforma` (`finvoiceproformaid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_github` */

CREATE TABLE `u_yf_github` (
  `github_id` int(10) NOT NULL AUTO_INCREMENT,
  `token` varchar(100) DEFAULT NULL,
  `username` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`github_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igdn` */

CREATE TABLE `u_yf_igdn` (
  `igdnid` int(10) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `storageid` int(10) DEFAULT NULL,
  `igdn_status` varchar(255) DEFAULT NULL,
  `acceptance_date` date DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `ssingleordersid` int(10) DEFAULT NULL,
  PRIMARY KEY (`igdnid`),
  KEY `storageid` (`storageid`),
  KEY `accountid` (`accountid`),
  KEY `ssingleordersid` (`ssingleordersid`),
  CONSTRAINT `u_yf_igdn_ibfk_1` FOREIGN KEY (`igdnid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igdn_inventory` */

CREATE TABLE `u_yf_igdn_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(200) DEFAULT NULL,
  `ean` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_igdn_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_igdn_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_igdn` (`igdnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igdn_invfield` */

CREATE TABLE `u_yf_igdn_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igdn_invmap` */

CREATE TABLE `u_yf_igdn_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igdnc` */

CREATE TABLE `u_yf_igdnc` (
  `igdncid` int(10) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `storageid` int(10) DEFAULT NULL,
  `igdnc_status` varchar(255) DEFAULT NULL,
  `acceptance_date` date DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `igdnid` int(10) DEFAULT NULL,
  PRIMARY KEY (`igdncid`),
  KEY `storageid` (`storageid`),
  KEY `accountid` (`accountid`),
  KEY `igdnid` (`igdnid`),
  CONSTRAINT `u_yf_igdnc_ibfk_1` FOREIGN KEY (`igdncid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igdnc_inventory` */

CREATE TABLE `u_yf_igdnc_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `ean` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_igdnc_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_igdnc_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_igdnc` (`igdncid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igdnc_invfield` */

CREATE TABLE `u_yf_igdnc_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igdnc_invmap` */

CREATE TABLE `u_yf_igdnc_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igdnccf` */

CREATE TABLE `u_yf_igdnccf` (
  `igdncid` int(10) NOT NULL,
  PRIMARY KEY (`igdncid`),
  CONSTRAINT `u_yf_igdnccf_ibfk_1` FOREIGN KEY (`igdncid`) REFERENCES `u_yf_igdnc` (`igdncid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igdncf` */

CREATE TABLE `u_yf_igdncf` (
  `igdnid` int(10) NOT NULL,
  PRIMARY KEY (`igdnid`),
  CONSTRAINT `u_yf_igdncf_ibfk_1` FOREIGN KEY (`igdnid`) REFERENCES `u_yf_igdn` (`igdnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igin` */

CREATE TABLE `u_yf_igin` (
  `iginid` int(10) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `storageid` int(10) DEFAULT NULL,
  `igin_status` varchar(255) DEFAULT NULL,
  `acceptance_date` date DEFAULT NULL,
  PRIMARY KEY (`iginid`),
  KEY `storageid` (`storageid`),
  CONSTRAINT `u_yf_igin_ibfk_1` FOREIGN KEY (`iginid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igin_inventory` */

CREATE TABLE `u_yf_igin_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(200) DEFAULT NULL,
  `ean` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_igin_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_igin_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_igin` (`iginid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igin_invfield` */

CREATE TABLE `u_yf_igin_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igin_invmap` */

CREATE TABLE `u_yf_igin_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igincf` */

CREATE TABLE `u_yf_igincf` (
  `iginid` int(10) NOT NULL,
  PRIMARY KEY (`iginid`),
  CONSTRAINT `u_yf_igincf_ibfk_1` FOREIGN KEY (`iginid`) REFERENCES `u_yf_igin` (`iginid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igrn` */

CREATE TABLE `u_yf_igrn` (
  `igrnid` int(10) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `storageid` int(10) DEFAULT NULL,
  `igrn_status` varchar(255) DEFAULT NULL,
  `vendorid` int(10) DEFAULT NULL,
  `acceptance_date` date DEFAULT NULL,
  `sum_total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  PRIMARY KEY (`igrnid`),
  KEY `storageid` (`storageid`),
  KEY `vendorid` (`vendorid`),
  CONSTRAINT `u_yf_igrn_ibfk_1` FOREIGN KEY (`igrnid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igrn_inventory` */

CREATE TABLE `u_yf_igrn_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(200) DEFAULT NULL,
  `ean` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_igrn_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_igrn_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_igrn` (`igrnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igrn_invfield` */

CREATE TABLE `u_yf_igrn_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igrn_invmap` */

CREATE TABLE `u_yf_igrn_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igrnc` */

CREATE TABLE `u_yf_igrnc` (
  `igrncid` int(10) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `storageid` int(10) DEFAULT NULL,
  `igrnc_status` varchar(255) DEFAULT NULL,
  `vendorid` int(10) DEFAULT NULL,
  `acceptance_date` date DEFAULT NULL,
  `sum_total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `igrnid` int(10) DEFAULT NULL,
  PRIMARY KEY (`igrncid`),
  KEY `storageid` (`storageid`),
  KEY `vendorid` (`vendorid`),
  KEY `igrnid` (`igrnid`),
  CONSTRAINT `u_yf_igrnc_ibfk_1` FOREIGN KEY (`igrncid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igrnc_inventory` */

CREATE TABLE `u_yf_igrnc_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `ean` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_igrnc_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_igrnc_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_igrnc` (`igrncid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igrnc_invfield` */

CREATE TABLE `u_yf_igrnc_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igrnc_invmap` */

CREATE TABLE `u_yf_igrnc_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igrnccf` */

CREATE TABLE `u_yf_igrnccf` (
  `igrncid` int(10) NOT NULL,
  PRIMARY KEY (`igrncid`),
  CONSTRAINT `u_yf_igrnccf_ibfk_1` FOREIGN KEY (`igrncid`) REFERENCES `u_yf_igrnc` (`igrncid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_igrncf` */

CREATE TABLE `u_yf_igrncf` (
  `igrnid` int(10) NOT NULL,
  PRIMARY KEY (`igrnid`),
  CONSTRAINT `u_yf_igrncf_ibfk_1` FOREIGN KEY (`igrnid`) REFERENCES `u_yf_igrn` (`igrnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_iidn` */

CREATE TABLE `u_yf_iidn` (
  `iidnid` int(10) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `storageid` int(10) DEFAULT NULL,
  `iidn_status` varchar(255) DEFAULT NULL,
  `acceptance_date` date DEFAULT NULL,
  PRIMARY KEY (`iidnid`),
  KEY `storageid` (`storageid`),
  CONSTRAINT `u_yf_iidn_ibfk_1` FOREIGN KEY (`iidnid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_iidn_inventory` */

CREATE TABLE `u_yf_iidn_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(200) DEFAULT NULL,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `ean` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_iidn_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_iidn_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_iidn` (`iidnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_iidn_invfield` */

CREATE TABLE `u_yf_iidn_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_iidn_invmap` */

CREATE TABLE `u_yf_iidn_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_iidncf` */

CREATE TABLE `u_yf_iidncf` (
  `iidnid` int(10) NOT NULL,
  PRIMARY KEY (`iidnid`),
  CONSTRAINT `u_yf_iidncf_ibfk_1` FOREIGN KEY (`iidnid`) REFERENCES `u_yf_iidn` (`iidnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_incidentregister` */

CREATE TABLE `u_yf_incidentregister` (
  `incidentregisterid` int(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `locationregisterid` int(11) unsigned DEFAULT 0,
  `datasetregisterid` int(11) unsigned DEFAULT 0,
  `incidentregister_status` varchar(255) DEFAULT '',
  `incidentregister_type` varchar(255) DEFAULT '',
  `incident_date` date DEFAULT NULL,
  `discovery_date` date DEFAULT NULL,
  `incident_report_date` date DEFAULT NULL,
  `incident_publication_date` date DEFAULT NULL,
  `peoplne_number` int(9) DEFAULT 0,
  `breach_circumstances` text DEFAULT NULL,
  `breach_nature` text DEFAULT NULL,
  `possible_consequences` text DEFAULT NULL,
  `security_measures` text DEFAULT NULL,
  PRIMARY KEY (`incidentregisterid`),
  KEY `u_yf_incidentregister_locationregisterid_idx` (`locationregisterid`),
  KEY `u_yf_incidentregister_datasetregisterid_idx` (`datasetregisterid`),
  CONSTRAINT `fk_1_u_yf_incidentregisterincidentregisterid` FOREIGN KEY (`incidentregisterid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_incidentregistercf` */

CREATE TABLE `u_yf_incidentregistercf` (
  `incidentregisterid` int(10) NOT NULL,
  PRIMARY KEY (`incidentregisterid`),
  CONSTRAINT `fk_1_u_yf_incidentregistercfincidentregisterid` FOREIGN KEY (`incidentregisterid`) REFERENCES `u_yf_incidentregister` (`incidentregisterid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ipreorder` */

CREATE TABLE `u_yf_ipreorder` (
  `ipreorderid` int(10) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `storageid` int(10) DEFAULT NULL,
  `ipreorder_status` varchar(255) DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `acceptance_date` date DEFAULT NULL,
  PRIMARY KEY (`ipreorderid`),
  KEY `storageid` (`storageid`),
  KEY `accountid` (`accountid`),
  CONSTRAINT `u_yf_ipreorder_ibfk_1` FOREIGN KEY (`ipreorderid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ipreorder_inventory` */

CREATE TABLE `u_yf_ipreorder_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `comment1` text DEFAULT NULL,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `unit` varchar(255) DEFAULT NULL,
  `ean` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_ipreorder_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_ipreorder_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_ipreorder` (`ipreorderid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ipreorder_invfield` */

CREATE TABLE `u_yf_ipreorder_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ipreorder_invmap` */

CREATE TABLE `u_yf_ipreorder_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ipreordercf` */

CREATE TABLE `u_yf_ipreordercf` (
  `ipreorderid` int(10) NOT NULL,
  PRIMARY KEY (`ipreorderid`),
  CONSTRAINT `u_yf_ipreordercf_ibfk_1` FOREIGN KEY (`ipreorderid`) REFERENCES `u_yf_ipreorder` (`ipreorderid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istdn` */

CREATE TABLE `u_yf_istdn` (
  `istdnid` int(10) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `storageid` int(10) DEFAULT NULL,
  `istdn_status` varchar(255) DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `acceptance_date` date DEFAULT NULL,
  `sum_total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `process` int(10) DEFAULT NULL,
  `subprocess` int(10) DEFAULT NULL,
  PRIMARY KEY (`istdnid`),
  KEY `storageid` (`storageid`),
  KEY `accountid` (`accountid`),
  KEY `process` (`process`),
  KEY `subprocess` (`subprocess`),
  CONSTRAINT `u_yf_istdn_ibfk_1` FOREIGN KEY (`istdnid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istdn_inventory` */

CREATE TABLE `u_yf_istdn_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `ean` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_istdn_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_istdn_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_istdn` (`istdnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istdn_invfield` */

CREATE TABLE `u_yf_istdn_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istdn_invmap` */

CREATE TABLE `u_yf_istdn_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istdncf` */

CREATE TABLE `u_yf_istdncf` (
  `istdnid` int(10) NOT NULL,
  PRIMARY KEY (`istdnid`),
  CONSTRAINT `u_yf_istdncf_ibfk_1` FOREIGN KEY (`istdnid`) REFERENCES `u_yf_istdn` (`istdnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istn` */

CREATE TABLE `u_yf_istn` (
  `istnid` int(10) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `istn_status` varchar(255) DEFAULT NULL,
  `estimated_date` date DEFAULT NULL,
  `istn_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`istnid`),
  CONSTRAINT `u_yf_istn_ibfk_1` FOREIGN KEY (`istnid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istncf` */

CREATE TABLE `u_yf_istncf` (
  `istnid` int(10) NOT NULL,
  PRIMARY KEY (`istnid`),
  CONSTRAINT `u_yf_istncf_ibfk_1` FOREIGN KEY (`istnid`) REFERENCES `u_yf_istn` (`istnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istorages` */

CREATE TABLE `u_yf_istorages` (
  `istorageid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `storage_status` varchar(255) DEFAULT '',
  `storage_type` varchar(255) DEFAULT '',
  `parentid` int(10) DEFAULT NULL,
  PRIMARY KEY (`istorageid`),
  KEY `parentid` (`parentid`),
  CONSTRAINT `u_yf_istorages_ibfk_1` FOREIGN KEY (`istorageid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istorages_address` */

CREATE TABLE `u_yf_istorages_address` (
  `istorageaddressid` int(10) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`istorageaddressid`),
  CONSTRAINT `u_yf_istorages_address_ibfk_1` FOREIGN KEY (`istorageaddressid`) REFERENCES `u_yf_istorages` (`istorageid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istorages_products` */

CREATE TABLE `u_yf_istorages_products` (
  `crmid` int(10) DEFAULT NULL,
  `relcrmid` int(10) DEFAULT NULL,
  `qtyinstock` decimal(25,3) DEFAULT NULL,
  KEY `crmid` (`crmid`),
  KEY `relcrmid` (`relcrmid`),
  CONSTRAINT `u_yf_istorages_products_ibfk_1` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE,
  CONSTRAINT `u_yf_istorages_products_ibfk_2` FOREIGN KEY (`relcrmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istoragescf` */

CREATE TABLE `u_yf_istoragescf` (
  `istorageid` int(10) NOT NULL,
  PRIMARY KEY (`istorageid`),
  CONSTRAINT `u_yf_istoragescf_ibfk_1` FOREIGN KEY (`istorageid`) REFERENCES `u_yf_istorages` (`istorageid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istrn` */

CREATE TABLE `u_yf_istrn` (
  `istrnid` int(10) NOT NULL,
  `number` varchar(32) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `storageid` int(10) DEFAULT NULL,
  `istrn_status` varchar(255) DEFAULT NULL,
  `vendorid` int(10) DEFAULT NULL,
  `acceptance_date` date DEFAULT NULL,
  `sum_total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `process` int(10) DEFAULT NULL,
  `subprocess` int(10) DEFAULT NULL,
  PRIMARY KEY (`istrnid`),
  KEY `storageid` (`storageid`),
  KEY `vendorid` (`vendorid`),
  KEY `process` (`process`),
  KEY `subprocess` (`subprocess`),
  CONSTRAINT `u_yf_istrn_ibfk_1` FOREIGN KEY (`istrnid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istrn_inventory` */

CREATE TABLE `u_yf_istrn_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `ean` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_istrn_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_istrn_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_istrn` (`istrnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istrn_invfield` */

CREATE TABLE `u_yf_istrn_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istrn_invmap` */

CREATE TABLE `u_yf_istrn_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_istrncf` */

CREATE TABLE `u_yf_istrncf` (
  `istrnid` int(10) NOT NULL,
  PRIMARY KEY (`istrnid`),
  CONSTRAINT `u_yf_istrncf_ibfk_1` FOREIGN KEY (`istrnid`) REFERENCES `u_yf_istrn` (`istrnid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_knowledgebase` */

CREATE TABLE `u_yf_knowledgebase` (
  `knowledgebaseid` int(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `content` mediumtext DEFAULT NULL,
  `category` varchar(200) DEFAULT NULL,
  `knowledgebase_view` varchar(255) DEFAULT NULL,
  `knowledgebase_status` varchar(255) DEFAULT '',
  PRIMARY KEY (`knowledgebaseid`),
  CONSTRAINT `fk_1_vtiger_knowledgebase` FOREIGN KEY (`knowledgebaseid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_knowledgebase_knowledgebase` */

CREATE TABLE `u_yf_knowledgebase_knowledgebase` (
  `crmid` int(11) DEFAULT NULL,
  `relcrmid` int(11) DEFAULT NULL,
  KEY `u_yf_knowledgebase_knowledgebase_crmid_idx` (`crmid`),
  KEY `u_yf_knowledgebase_knowledgebase_relcrmid_idx` (`relcrmid`),
  CONSTRAINT `fk_1_u_yf_knowledgebase_knowledgebase` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE,
  CONSTRAINT `fk_2_u_yf_knowledgebase_knowledgebase` FOREIGN KEY (`relcrmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_knowledgebasecf` */

CREATE TABLE `u_yf_knowledgebasecf` (
  `knowledgebaseid` int(10) NOT NULL,
  PRIMARY KEY (`knowledgebaseid`),
  CONSTRAINT `fk_1_vtiger_knowledgebasecf` FOREIGN KEY (`knowledgebaseid`) REFERENCES `u_yf_knowledgebase` (`knowledgebaseid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_locationregister` */

CREATE TABLE `u_yf_locationregister` (
  `locationregisterid` int(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `parent_id` int(11) unsigned DEFAULT 0,
  `locationregister_status` varchar(255) DEFAULT '',
  `security_type` text DEFAULT NULL,
  `building_number` varchar(10) DEFAULT '',
  `street` varchar(255) DEFAULT '',
  `district` varchar(255) DEFAULT '',
  `township` varchar(255) DEFAULT '',
  `state` varchar(255) DEFAULT '',
  `pobox` varchar(100) DEFAULT '',
  `local_number` varchar(20) DEFAULT '',
  `post_code` varchar(20) DEFAULT '',
  `city` varchar(150) DEFAULT '',
  `county` varchar(150) DEFAULT '',
  `country` varchar(150) DEFAULT '',
  PRIMARY KEY (`locationregisterid`),
  KEY `u_yf_locationregister_parent_id_idx` (`parent_id`),
  CONSTRAINT `fk_1_u_yf_locationregisterlocationregisterid` FOREIGN KEY (`locationregisterid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_locationregistercf` */

CREATE TABLE `u_yf_locationregistercf` (
  `locationregisterid` int(10) NOT NULL,
  PRIMARY KEY (`locationregisterid`),
  CONSTRAINT `fk_1_u_yf_locationregistercflocationregisterid` FOREIGN KEY (`locationregisterid`) REFERENCES `u_yf_locationregister` (`locationregisterid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_mail_address_book` */

CREATE TABLE `u_yf_mail_address_book` (
  `id` int(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `users` text NOT NULL,
  KEY `email` (`email`,`name`),
  KEY `id` (`id`),
  CONSTRAINT `u_yf_mail_address_book_ibfk_1` FOREIGN KEY (`id`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_mail_autologin` */

CREATE TABLE `u_yf_mail_autologin` (
  `ruid` smallint(5) unsigned NOT NULL,
  `key` varchar(50) NOT NULL,
  `cuid` smallint(5) unsigned NOT NULL,
  `params` text NOT NULL,
  KEY `ruid` (`ruid`),
  KEY `cuid` (`cuid`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_mail_compose_data` */

CREATE TABLE `u_yf_mail_compose_data` (
  `userid` smallint(5) unsigned NOT NULL,
  `key` varchar(32) NOT NULL,
  `data` text NOT NULL,
  UNIQUE KEY `userid` (`userid`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_modentity_sequences` */

CREATE TABLE `u_yf_modentity_sequences` (
  `tabid` smallint(5) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `cur_id` int(10) unsigned DEFAULT 0,
  KEY `u_yf_modentity_sequences_tabid_fk` (`tabid`),
  CONSTRAINT `u_yf_modentity_sequences_tabid_fk` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_modtracker_inv` */

CREATE TABLE `u_yf_modtracker_inv` (
  `id` int(10) NOT NULL,
  `changes` text DEFAULT NULL,
  KEY `u_yf_modtracker_inv_id_idx` (`id`),
  CONSTRAINT `u_yf_modtracker_inv_id_fk` FOREIGN KEY (`id`) REFERENCES `vtiger_modtracker_basic` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_multicompany` */

CREATE TABLE `u_yf_multicompany` (
  `multicompanyid` int(11) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `mulcomp_status` varchar(255) DEFAULT NULL,
  `email1` varchar(100) DEFAULT NULL,
  `email2` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `phone_extra` varchar(100) DEFAULT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `mobile_extra` varchar(100) DEFAULT NULL,
  `fax` varchar(30) DEFAULT NULL,
  `fax_extra` varchar(100) DEFAULT NULL,
  `vat` varchar(255) DEFAULT NULL,
  `companyid1` varchar(255) DEFAULT NULL,
  `companyid2` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(50) DEFAULT NULL,
  `localnumbera` varchar(50) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  `website` varchar(255) DEFAULT '',
  `logo` text DEFAULT NULL,
  PRIMARY KEY (`multicompanyid`),
  KEY `multicompany_parent_id_idx` (`parent_id`),
  CONSTRAINT `fk_1_u_yf_multicompanymulticompanyid` FOREIGN KEY (`multicompanyid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_multicompanycf` */

CREATE TABLE `u_yf_multicompanycf` (
  `multicompanyid` int(11) NOT NULL,
  `public_notes` text DEFAULT NULL,
  `internal_notes` text DEFAULT NULL,
  PRIMARY KEY (`multicompanyid`),
  CONSTRAINT `fk_1_u_yf_multicompanycfmulticompanyid` FOREIGN KEY (`multicompanyid`) REFERENCES `u_yf_multicompany` (`multicompanyid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_notification` */

CREATE TABLE `u_yf_notification` (
  `notificationid` int(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `notification_status` varchar(255) DEFAULT NULL,
  `notification_type` varchar(255) DEFAULT '',
  `link` int(10) DEFAULT NULL,
  `process` int(10) DEFAULT NULL,
  `subprocess` int(10) DEFAULT NULL,
  `linkextend` int(10) DEFAULT NULL,
  PRIMARY KEY (`notificationid`),
  KEY `link` (`link`),
  KEY `process` (`process`),
  KEY `subprocess` (`subprocess`),
  KEY `linkextend` (`linkextend`),
  CONSTRAINT `fk_1_notification` FOREIGN KEY (`notificationid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_openstreetmap` */

CREATE TABLE `u_yf_openstreetmap` (
  `crmid` int(10) unsigned NOT NULL,
  `type` char(1) NOT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lon` decimal(10,7) DEFAULT NULL,
  KEY `u_yf_openstreetmap_lat_lon` (`lat`,`lon`),
  KEY `crmid_type` (`crmid`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_openstreetmap_address_updater` */

CREATE TABLE `u_yf_openstreetmap_address_updater` (
  `crmid` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_openstreetmap_cache` */

CREATE TABLE `u_yf_openstreetmap_cache` (
  `user_id` int(10) unsigned NOT NULL,
  `module_name` varchar(50) NOT NULL,
  `crmids` int(10) unsigned NOT NULL,
  KEY `u_yf_openstreetmap_cache_user_id_module_name_idx` (`user_id`,`module_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_openstreetmap_record_updater` */

CREATE TABLE `u_yf_openstreetmap_record_updater` (
  `crmid` int(10) NOT NULL,
  `type` char(1) NOT NULL,
  `address` text NOT NULL,
  KEY `crmid` (`crmid`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_partners` */

CREATE TABLE `u_yf_partners` (
  `partnersid` int(10) NOT NULL DEFAULT 0,
  `partners_no` varchar(255) DEFAULT '',
  `subject` varchar(255) DEFAULT NULL,
  `vat_id` varchar(30) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `email` varchar(100) DEFAULT '',
  `active` tinyint(1) DEFAULT 0,
  `category` varchar(255) DEFAULT '',
  PRIMARY KEY (`partnersid`),
  CONSTRAINT `fk_1_u_yf_partners` FOREIGN KEY (`partnersid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_partners_address` */

CREATE TABLE `u_yf_partners_address` (
  `partneraddressid` int(10) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`partneraddressid`),
  CONSTRAINT `u_yf_partners_address_ibfk_1` FOREIGN KEY (`partneraddressid`) REFERENCES `u_yf_partners` (`partnersid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_partnerscf` */

CREATE TABLE `u_yf_partnerscf` (
  `partnersid` int(10) NOT NULL,
  PRIMARY KEY (`partnersid`),
  CONSTRAINT `fk_1_u_yf_partnerscf` FOREIGN KEY (`partnersid`) REFERENCES `u_yf_partners` (`partnersid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_pdf_inv_scheme` */

CREATE TABLE `u_yf_pdf_inv_scheme` (
  `crmid` int(10) NOT NULL,
  `columns` text DEFAULT NULL,
  KEY `crmid` (`crmid`),
  CONSTRAINT `fk_u_yf_pdf_inv_scheme_crmid` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_picklist_close_state` */

CREATE TABLE `u_yf_picklist_close_state` (
  `valueid` int(11) NOT NULL,
  `fieldid` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`valueid`),
  KEY `fieldid` (`fieldid`),
  CONSTRAINT `fk_1_u_yf_picklist_close_state` FOREIGN KEY (`fieldid`) REFERENCES `vtiger_field` (`fieldid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_recurring_info` */

CREATE TABLE `u_yf_recurring_info` (
  `srecurringordersid` int(10) NOT NULL DEFAULT 0,
  `target_module` varchar(25) DEFAULT NULL,
  `recurring_frequency` varchar(100) DEFAULT NULL,
  `start_period` date DEFAULT NULL,
  `end_period` date DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `last_recurring_date` date DEFAULT NULL,
  PRIMARY KEY (`srecurringordersid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_reviewed_queue` */

CREATE TABLE `u_yf_reviewed_queue` (
  `id` int(10) NOT NULL,
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  CONSTRAINT `fk_1_u_yf_reviewed_queue` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_scalculations` */

CREATE TABLE `u_yf_scalculations` (
  `scalculationsid` int(10) NOT NULL DEFAULT 0,
  `scalculations_no` varchar(255) DEFAULT '',
  `subject` varchar(255) DEFAULT NULL,
  `salesprocessid` int(10) DEFAULT NULL,
  `srequirementscardsid` int(10) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `scalculations_status` varchar(255) DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `response_time` decimal(10,2) DEFAULT 0.00,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `sum_total` decimal(28,8) DEFAULT NULL,
  `sum_marginp` decimal(10,2) DEFAULT NULL,
  `sum_margin` decimal(28,8) DEFAULT NULL,
  PRIMARY KEY (`scalculationsid`),
  KEY `salesprocessid` (`salesprocessid`),
  KEY `accountid` (`accountid`),
  KEY `srequirementscardsid` (`srequirementscardsid`),
  CONSTRAINT `fk_1_u_yf_scalculations` FOREIGN KEY (`scalculationsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_scalculations_inventory` */

CREATE TABLE `u_yf_scalculations_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `comment1` text DEFAULT NULL,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `purchase` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `marginp` decimal(28,8) DEFAULT 0.00000000,
  `margin` decimal(28,8) DEFAULT 0.00000000,
  `unit` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_scalculations_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_scalculations_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_scalculations` (`scalculationsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_scalculations_invfield` */

CREATE TABLE `u_yf_scalculations_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_scalculations_invmap` */

CREATE TABLE `u_yf_scalculations_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_scalculationscf` */

CREATE TABLE `u_yf_scalculationscf` (
  `scalculationsid` int(10) NOT NULL,
  PRIMARY KEY (`scalculationsid`),
  CONSTRAINT `fk_1_u_yf_scalculationscf` FOREIGN KEY (`scalculationsid`) REFERENCES `u_yf_scalculations` (`scalculationsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_social_media_config` */

CREATE TABLE `u_yf_social_media_config` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_type_unique` (`name`,`type`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_social_media_twitter` */

CREATE TABLE `u_yf_social_media_twitter` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `twitter_login` varchar(15) NOT NULL,
  `id_twitter` varchar(32) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `twitter_name` varchar(50) DEFAULT NULL,
  `reply` int(11) DEFAULT NULL,
  `retweet` int(11) DEFAULT NULL,
  `favorite` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `twitter_login` (`twitter_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squoteenquiries` */

CREATE TABLE `u_yf_squoteenquiries` (
  `squoteenquiriesid` int(10) NOT NULL DEFAULT 0,
  `squoteenquiries_no` varchar(255) DEFAULT '',
  `subject` varchar(255) DEFAULT NULL,
  `salesprocessid` int(10) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `squoteenquiries_status` varchar(255) DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `response_time` decimal(10,2) DEFAULT 0.00,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `campaign_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`squoteenquiriesid`),
  KEY `salesprocessid` (`salesprocessid`),
  KEY `accountid` (`accountid`),
  KEY `u_yf_squoteenquiries_campaign_id_idx` (`campaign_id`),
  CONSTRAINT `fk_1_u_yf_squoteenquiries` FOREIGN KEY (`squoteenquiriesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squoteenquiries_inventory` */

CREATE TABLE `u_yf_squoteenquiries_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_squoteenquiries_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_squoteenquiries_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_squoteenquiries` (`squoteenquiriesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squoteenquiries_invfield` */

CREATE TABLE `u_yf_squoteenquiries_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squoteenquiries_invmap` */

CREATE TABLE `u_yf_squoteenquiries_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squoteenquiriescf` */

CREATE TABLE `u_yf_squoteenquiriescf` (
  `squoteenquiriesid` int(10) NOT NULL,
  PRIMARY KEY (`squoteenquiriesid`),
  CONSTRAINT `fk_1_u_yf_squoteenquiriescf` FOREIGN KEY (`squoteenquiriesid`) REFERENCES `u_yf_squoteenquiries` (`squoteenquiriesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squotes` */

CREATE TABLE `u_yf_squotes` (
  `squotesid` int(10) NOT NULL DEFAULT 0,
  `squotes_no` varchar(255) DEFAULT '',
  `subject` varchar(255) DEFAULT NULL,
  `salesprocessid` int(10) DEFAULT NULL,
  `scalculationsid` int(10) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `squotes_status` varchar(255) DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `response_time` decimal(10,2) DEFAULT 0.00,
  `company` varchar(255) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `sum_total` decimal(28,8) DEFAULT NULL,
  `sum_marginp` decimal(10,2) DEFAULT NULL,
  `sum_margin` decimal(28,8) DEFAULT NULL,
  `sum_gross` decimal(28,8) DEFAULT NULL,
  `sum_discount` decimal(28,8) DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  PRIMARY KEY (`squotesid`),
  KEY `salesprocessid` (`salesprocessid`),
  KEY `scalculationsid` (`scalculationsid`),
  KEY `accountid` (`accountid`),
  CONSTRAINT `fk_1_u_yf_squotes` FOREIGN KEY (`squotesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squotes_address` */

CREATE TABLE `u_yf_squotes_address` (
  `squotesaddressid` int(10) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`squotesaddressid`),
  CONSTRAINT `u_yf_squotes_address_ibfk_1` FOREIGN KEY (`squotesaddressid`) REFERENCES `u_yf_squotes` (`squotesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squotes_inventory` */

CREATE TABLE `u_yf_squotes_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `discount` decimal(28,8) DEFAULT 0.00000000,
  `discountparam` varchar(255) DEFAULT NULL,
  `marginp` decimal(28,8) DEFAULT 0.00000000,
  `margin` decimal(28,8) DEFAULT 0.00000000,
  `comment1` text DEFAULT NULL,
  `price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `purchase` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `tax` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `taxparam` varchar(255) NOT NULL,
  `gross` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `discountmode` tinyint(1) NOT NULL DEFAULT 0,
  `taxmode` tinyint(1) NOT NULL DEFAULT 0,
  `currency` int(10) DEFAULT NULL,
  `currencyparam` varchar(1024) DEFAULT NULL,
  `net` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `unit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_squotes_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_squotes_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_squotes` (`squotesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squotes_invfield` */

CREATE TABLE `u_yf_squotes_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squotes_invmap` */

CREATE TABLE `u_yf_squotes_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_squotescf` */

CREATE TABLE `u_yf_squotescf` (
  `squotesid` int(10) NOT NULL,
  PRIMARY KEY (`squotesid`),
  CONSTRAINT `fk_1_u_yf_squotescf` FOREIGN KEY (`squotesid`) REFERENCES `u_yf_squotes` (`squotesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srecurringorders` */

CREATE TABLE `u_yf_srecurringorders` (
  `srecurringordersid` int(10) NOT NULL DEFAULT 0,
  `srecurringorders_no` varchar(255) DEFAULT '',
  `subject` varchar(255) DEFAULT NULL,
  `salesprocessid` int(10) DEFAULT NULL,
  `squotesid` int(10) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `srecurringorders_status` varchar(255) DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `response_time` decimal(10,2) DEFAULT 0.00,
  `company` varchar(255) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`srecurringordersid`),
  KEY `salesprocessid` (`salesprocessid`),
  KEY `squotesid` (`squotesid`),
  KEY `accountid` (`accountid`),
  CONSTRAINT `fk_1_u_yf_srecurringorders` FOREIGN KEY (`srecurringordersid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srecurringorders_address` */

CREATE TABLE `u_yf_srecurringorders_address` (
  `srecurringordersaddressid` int(10) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`srecurringordersaddressid`),
  CONSTRAINT `u_yf_srecurringorders_address_ibfk_1` FOREIGN KEY (`srecurringordersaddressid`) REFERENCES `u_yf_srecurringorders` (`srecurringordersid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srecurringorders_inventory` */

CREATE TABLE `u_yf_srecurringorders_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) DEFAULT 0,
  `qty` decimal(25,3) DEFAULT 0.000,
  `discount` decimal(28,8) DEFAULT 0.00000000,
  `discountparam` varchar(255) DEFAULT NULL,
  `marginp` decimal(28,8) DEFAULT 0.00000000,
  `margin` decimal(28,8) DEFAULT 0.00000000,
  `tax` decimal(28,8) DEFAULT 0.00000000,
  `taxparam` varchar(255) DEFAULT NULL,
  `comment1` text DEFAULT NULL,
  `price` decimal(28,8) DEFAULT 0.00000000,
  `total` decimal(28,8) DEFAULT 0.00000000,
  `net` decimal(28,8) DEFAULT 0.00000000,
  `purchase` decimal(28,8) DEFAULT 0.00000000,
  `gross` decimal(28,8) DEFAULT 0.00000000,
  `discountmode` smallint(1) DEFAULT 0,
  `taxmode` smallint(1) DEFAULT 0,
  `currency` int(11) DEFAULT NULL,
  `currencyparam` varchar(1024) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_srecurringorders_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_srecurringorders_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_srecurringorders` (`srecurringordersid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srecurringorders_invfield` */

CREATE TABLE `u_yf_srecurringorders_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srecurringorders_invmap` */

CREATE TABLE `u_yf_srecurringorders_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srecurringorderscf` */

CREATE TABLE `u_yf_srecurringorderscf` (
  `srecurringordersid` int(10) NOT NULL,
  PRIMARY KEY (`srecurringordersid`),
  CONSTRAINT `fk_1_u_yf_srecurringorderscf` FOREIGN KEY (`srecurringordersid`) REFERENCES `u_yf_srecurringorders` (`srecurringordersid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srequirementscards` */

CREATE TABLE `u_yf_srequirementscards` (
  `srequirementscardsid` int(10) NOT NULL DEFAULT 0,
  `srequirementscards_no` varchar(255) DEFAULT '',
  `subject` varchar(255) DEFAULT NULL,
  `salesprocessid` int(10) DEFAULT NULL,
  `quoteenquiryid` int(10) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `srequirementscards_status` varchar(255) DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `response_time` decimal(10,2) DEFAULT 0.00,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`srequirementscardsid`),
  KEY `salesprocessid` (`salesprocessid`),
  KEY `accountid` (`accountid`),
  KEY `quoteenquiryid` (`quoteenquiryid`),
  CONSTRAINT `fk_1_u_yf_srequirementscards` FOREIGN KEY (`srequirementscardsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srequirementscards_inventory` */

CREATE TABLE `u_yf_srequirementscards_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) NOT NULL DEFAULT 0,
  `qty` decimal(25,3) NOT NULL DEFAULT 0.000,
  `comment1` text DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_srequirementscards_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_srequirementscards_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_srequirementscards` (`srequirementscardsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srequirementscards_invfield` */

CREATE TABLE `u_yf_srequirementscards_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srequirementscards_invmap` */

CREATE TABLE `u_yf_srequirementscards_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_srequirementscardscf` */

CREATE TABLE `u_yf_srequirementscardscf` (
  `srequirementscardsid` int(10) NOT NULL,
  PRIMARY KEY (`srequirementscardsid`),
  CONSTRAINT `fk_1_u_yf_srequirementscardscf` FOREIGN KEY (`srequirementscardsid`) REFERENCES `u_yf_srequirementscards` (`srequirementscardsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ssalesprocesses` */

CREATE TABLE `u_yf_ssalesprocesses` (
  `ssalesprocessesid` int(10) NOT NULL DEFAULT 0,
  `ssalesprocesses_no` varchar(255) DEFAULT '',
  `subject` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `related_to` int(10) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `estimated` decimal(25,8) DEFAULT NULL,
  `actual_sale` decimal(25,8) DEFAULT NULL,
  `estimated_date` date DEFAULT NULL,
  `actual_date` date DEFAULT NULL,
  `probability` decimal(5,2) DEFAULT NULL,
  `ssalesprocesses_source` varchar(255) DEFAULT NULL,
  `ssalesprocesses_type` varchar(255) DEFAULT NULL,
  `ssalesprocesses_status` varchar(255) DEFAULT NULL,
  `campaignid` int(10) DEFAULT NULL,
  `parentid` int(10) DEFAULT 0,
  `startdate` date DEFAULT NULL,
  PRIMARY KEY (`ssalesprocessesid`),
  KEY `related_to` (`related_to`),
  KEY `campaignid` (`campaignid`),
  KEY `parentid` (`parentid`),
  KEY `ssalesprocesses_no` (`ssalesprocesses_no`),
  CONSTRAINT `fk_1_u_yf_ssalesprocesses` FOREIGN KEY (`ssalesprocessesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ssalesprocessescf` */

CREATE TABLE `u_yf_ssalesprocessescf` (
  `ssalesprocessesid` int(10) NOT NULL,
  PRIMARY KEY (`ssalesprocessesid`),
  CONSTRAINT `fk_1_u_yf_ssalesprocessescf` FOREIGN KEY (`ssalesprocessesid`) REFERENCES `u_yf_ssalesprocesses` (`ssalesprocessesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ssingleorders` */

CREATE TABLE `u_yf_ssingleorders` (
  `ssingleordersid` int(10) NOT NULL DEFAULT 0,
  `ssingleorders_no` varchar(255) DEFAULT '',
  `subject` varchar(255) DEFAULT NULL,
  `salesprocessid` int(10) DEFAULT NULL,
  `squotesid` int(10) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `ssingleorders_status` varchar(255) DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `response_time` decimal(10,2) DEFAULT 0.00,
  `company` varchar(255) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `sum_total` decimal(28,8) DEFAULT NULL,
  `sum_marginp` decimal(10,2) DEFAULT NULL,
  `sum_margin` decimal(28,8) DEFAULT NULL,
  `sum_gross` decimal(28,8) DEFAULT NULL,
  `sum_discount` decimal(28,8) DEFAULT NULL,
  `ssingleorders_source` varchar(255) DEFAULT '',
  PRIMARY KEY (`ssingleordersid`),
  KEY `salesprocessid` (`salesprocessid`),
  KEY `squotesid` (`squotesid`),
  KEY `accountid` (`accountid`),
  CONSTRAINT `fk_1_u_yf_ssingleorders` FOREIGN KEY (`ssingleordersid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ssingleorders_address` */

CREATE TABLE `u_yf_ssingleorders_address` (
  `ssingleordersaddressid` int(10) NOT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ssingleordersaddressid`),
  CONSTRAINT `u_yf_ssingleorders_address_ibfk_1` FOREIGN KEY (`ssingleordersaddressid`) REFERENCES `u_yf_ssingleorders` (`ssingleordersid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ssingleorders_inventory` */

CREATE TABLE `u_yf_ssingleorders_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) DEFAULT 0,
  `qty` decimal(25,3) DEFAULT 0.000,
  `discount` decimal(28,8) DEFAULT 0.00000000,
  `discountparam` varchar(255) DEFAULT NULL,
  `marginp` decimal(28,8) DEFAULT 0.00000000,
  `margin` decimal(28,8) DEFAULT 0.00000000,
  `tax` decimal(28,8) DEFAULT 0.00000000,
  `taxparam` varchar(255) DEFAULT NULL,
  `comment1` text DEFAULT NULL,
  `price` decimal(28,8) DEFAULT 0.00000000,
  `total` decimal(28,8) DEFAULT 0.00000000,
  `net` decimal(28,8) DEFAULT 0.00000000,
  `purchase` decimal(28,8) DEFAULT 0.00000000,
  `gross` decimal(28,8) DEFAULT 0.00000000,
  `discountmode` smallint(1) DEFAULT 0,
  `taxmode` smallint(1) DEFAULT 0,
  `currency` int(11) DEFAULT NULL,
  `currencyparam` varchar(1024) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_ssingleorders_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_ssingleorders_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_ssingleorders` (`ssingleordersid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ssingleorders_invfield` */

CREATE TABLE `u_yf_ssingleorders_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` tinyint(1) unsigned NOT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ssingleorders_invmap` */

CREATE TABLE `u_yf_ssingleorders_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_ssingleorderscf` */

CREATE TABLE `u_yf_ssingleorderscf` (
  `ssingleordersid` int(10) NOT NULL,
  PRIMARY KEY (`ssingleordersid`),
  CONSTRAINT `fk_1_u_yf_ssingleorderscf` FOREIGN KEY (`ssingleordersid`) REFERENCES `u_yf_ssingleorders` (`ssingleordersid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_svendorenquiries` */

CREATE TABLE `u_yf_svendorenquiries` (
  `svendorenquiriesid` int(10) NOT NULL DEFAULT 0,
  `svendorenquiries_no` varchar(50) DEFAULT '',
  `subject` varchar(255) DEFAULT NULL,
  `salesprocessid` int(10) DEFAULT NULL,
  `category` varchar(30) DEFAULT NULL,
  `svendorenquiries_status` varchar(255) DEFAULT NULL,
  `accountid` int(10) DEFAULT NULL,
  `response_time` decimal(10,2) DEFAULT 0.00,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `sum_total` decimal(28,8) DEFAULT NULL,
  `sum_marginp` decimal(10,2) DEFAULT NULL,
  `sum_margin` decimal(28,8) DEFAULT NULL,
  `vendorid` int(10) DEFAULT NULL,
  `scalculationsid` int(10) DEFAULT NULL,
  PRIMARY KEY (`svendorenquiriesid`),
  KEY `svendorenquiries_salesprocessid_idx` (`salesprocessid`),
  KEY `svendorenquiries_accountid_idx` (`accountid`),
  KEY `svendorenquiries_vendorid_idx` (`vendorid`),
  KEY `svendorenquiries_scalculationsid_idx` (`scalculationsid`),
  CONSTRAINT `fk_1_u_yf_svendorenquiries` FOREIGN KEY (`svendorenquiriesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_svendorenquiries_inventory` */

CREATE TABLE `u_yf_svendorenquiries_inventory` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `seq` int(10) DEFAULT NULL,
  `name` int(10) DEFAULT 0,
  `qty` decimal(25,3) DEFAULT 0.000,
  `comment1` text DEFAULT NULL,
  `price` decimal(28,8) DEFAULT 0.00000000,
  `total` decimal(28,8) DEFAULT 0.00000000,
  `purchase` decimal(28,8) DEFAULT 0.00000000,
  `marginp` decimal(28,8) DEFAULT 0.00000000,
  `margin` decimal(28,8) DEFAULT 0.00000000,
  `unit` varchar(255) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_svendorenquiries_inventory_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_svendorenquiries_inventory` FOREIGN KEY (`crmid`) REFERENCES `u_yf_svendorenquiries` (`svendorenquiriesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_svendorenquiries_invfield` */

CREATE TABLE `u_yf_svendorenquiries_invfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `label` varchar(50) NOT NULL,
  `invtype` varchar(30) NOT NULL,
  `presence` smallint(1) unsigned NOT NULL DEFAULT 0,
  `defaultvalue` varchar(255) DEFAULT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `block` smallint(1) unsigned NOT NULL,
  `displaytype` smallint(1) unsigned NOT NULL DEFAULT 1,
  `params` text DEFAULT NULL,
  `colspan` smallint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_svendorenquiries_invmap` */

CREATE TABLE `u_yf_svendorenquiries_invmap` (
  `module` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `tofield` varchar(50) NOT NULL,
  PRIMARY KEY (`module`,`field`,`tofield`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_svendorenquiriescf` */

CREATE TABLE `u_yf_svendorenquiriescf` (
  `svendorenquiriesid` int(10) NOT NULL,
  PRIMARY KEY (`svendorenquiriesid`),
  CONSTRAINT `fk_1_u_yf_svendorenquiriescf` FOREIGN KEY (`svendorenquiriesid`) REFERENCES `u_yf_svendorenquiries` (`svendorenquiriesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_timeline` */

CREATE TABLE `u_yf_timeline` (
  `crmid` int(10) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `userid` int(10) NOT NULL,
  KEY `timeline_crmid_idx` (`crmid`),
  CONSTRAINT `fk_1_u_yf_timeline` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_users_pinned` */

CREATE TABLE `u_yf_users_pinned` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `fav_element_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `u_yf_users_pinned` (`owner_id`),
  CONSTRAINT `u_yf_users_pinned_fk_1` FOREIGN KEY (`owner_id`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_watchdog_module` */

CREATE TABLE `u_yf_watchdog_module` (
  `member` varchar(50) NOT NULL,
  `module` int(10) unsigned NOT NULL,
  `lock` tinyint(1) DEFAULT 0,
  `exceptions` text DEFAULT NULL,
  PRIMARY KEY (`member`,`module`),
  KEY `userid` (`member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_watchdog_record` */

CREATE TABLE `u_yf_watchdog_record` (
  `userid` int(10) unsigned NOT NULL,
  `record` int(10) NOT NULL,
  `state` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`userid`,`record`),
  KEY `userid` (`userid`),
  KEY `record` (`record`),
  KEY `userid_2` (`userid`,`record`,`state`),
  CONSTRAINT `u_yf_watchdog_record_ibfk_1` FOREIGN KEY (`record`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `u_yf_watchdog_schedule` */

CREATE TABLE `u_yf_watchdog_schedule` (
  `userid` int(10) NOT NULL,
  `frequency` smallint(5) NOT NULL,
  `last_execution` datetime DEFAULT NULL,
  `modules` text DEFAULT NULL,
  PRIMARY KEY (`userid`),
  CONSTRAINT `u_yf_watchdog_schedule_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_account` */

CREATE TABLE `vtiger_account` (
  `accountid` int(10) NOT NULL DEFAULT 0,
  `account_no` varchar(100) NOT NULL,
  `accountname` varchar(100) NOT NULL,
  `parentid` int(10) DEFAULT 0,
  `account_type` varchar(200) DEFAULT NULL,
  `industry` varchar(200) DEFAULT NULL,
  `annualrevenue` decimal(25,8) DEFAULT NULL,
  `ownership` varchar(50) DEFAULT NULL,
  `siccode` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `otherphone` varchar(30) DEFAULT NULL,
  `email1` varchar(100) DEFAULT NULL,
  `email2` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `fax` varchar(30) DEFAULT NULL,
  `employees` int(10) DEFAULT 0,
  `emailoptout` smallint(1) DEFAULT 0,
  `isconvertedfromlead` smallint(3) DEFAULT 0,
  `vat_id` varchar(30) DEFAULT NULL,
  `registration_number_1` varchar(30) DEFAULT NULL,
  `registration_number_2` varchar(30) DEFAULT NULL,
  `verification` text DEFAULT NULL,
  `no_approval` smallint(1) DEFAULT 0,
  `balance` decimal(25,8) DEFAULT NULL,
  `payment_balance` decimal(25,8) DEFAULT NULL,
  `legal_form` varchar(255) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT NULL,
  `inventorybalance` decimal(25,8) DEFAULT 0.00000000,
  `discount` decimal(5,2) DEFAULT 0.00,
  `creditlimit` int(10) DEFAULT NULL,
  `products` text DEFAULT NULL,
  `services` text DEFAULT NULL,
  `last_invoice_date` date DEFAULT NULL,
  `active` tinyint(1) DEFAULT 0,
  `accounts_status` varchar(255) DEFAULT NULL,
  `phone_extra` varchar(100) DEFAULT NULL,
  `fax_extra` varchar(100) DEFAULT NULL,
  `otherphone_extra` varchar(100) DEFAULT NULL,
  `pricebook_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`accountid`),
  KEY `account_account_type_idx` (`account_type`),
  KEY `email_idx` (`email1`,`email2`),
  KEY `accountname` (`accountname`),
  KEY `parentid` (`parentid`),
  KEY `vtiger_account_pricebook_id_idx` (`pricebook_id`),
  CONSTRAINT `fk_1_vtiger_account` FOREIGN KEY (`accountid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_accountaddress` */

CREATE TABLE `vtiger_accountaddress` (
  `accountaddressid` int(10) NOT NULL,
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

/*Table structure for table `vtiger_accounts_status` */

CREATE TABLE `vtiger_accounts_status` (
  `accounts_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `accounts_status` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`accounts_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_accountscf` */

CREATE TABLE `vtiger_accountscf` (
  `accountid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`accountid`),
  CONSTRAINT `fk_1_vtiger_accountscf` FOREIGN KEY (`accountid`) REFERENCES `vtiger_account` (`accountid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_accounttype` */

CREATE TABLE `vtiger_accounttype` (
  `accounttypeid` int(10) NOT NULL AUTO_INCREMENT,
  `accounttype` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`accounttypeid`),
  UNIQUE KEY `accounttype_accounttype_idx` (`accounttype`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_actionmapping` */

CREATE TABLE `vtiger_actionmapping` (
  `actionid` smallint(5) unsigned NOT NULL,
  `actionname` varchar(200) NOT NULL,
  `securitycheck` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`actionid`,`actionname`),
  KEY `actionname` (`actionname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_activity` */

CREATE TABLE `vtiger_activity` (
  `activityid` int(10) NOT NULL DEFAULT 0,
  `subject` varchar(100) NOT NULL,
  `activitytype` varchar(200) NOT NULL,
  `date_start` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `sendnotification` smallint(1) NOT NULL DEFAULT 0,
  `duration_hours` smallint(5) DEFAULT NULL,
  `duration_minutes` varchar(3) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `priority` varchar(200) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `notime` smallint(1) NOT NULL DEFAULT 0,
  `visibility` varchar(50) NOT NULL DEFAULT 'all',
  `deleted` tinyint(1) DEFAULT 0,
  `smownerid` smallint(5) unsigned DEFAULT NULL,
  `allday` tinyint(1) DEFAULT NULL,
  `dav_status` tinyint(1) DEFAULT 1,
  `state` varchar(255) DEFAULT NULL,
  `link` int(10) DEFAULT NULL,
  `process` int(10) DEFAULT NULL,
  `subprocess` int(10) DEFAULT NULL,
  `followup` int(10) DEFAULT NULL,
  `reapeat` smallint(1) DEFAULT NULL,
  `recurrence` text DEFAULT NULL,
  `linkextend` int(10) DEFAULT NULL,
  `subprocess_sl` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`activityid`),
  KEY `activity_activityid_subject_idx` (`activityid`,`subject`),
  KEY `activity_activitytype_date_start_idx` (`activitytype`,`date_start`),
  KEY `activity_date_start_due_date_idx` (`date_start`,`due_date`),
  KEY `activity_date_start_time_start_idx` (`date_start`,`time_start`),
  KEY `activity_status_idx` (`status`),
  KEY `activitytype_2` (`activitytype`,`date_start`,`due_date`,`time_start`,`time_end`,`deleted`,`smownerid`),
  KEY `link` (`link`),
  KEY `process` (`process`),
  KEY `followup` (`followup`),
  KEY `subprocess` (`subprocess`),
  KEY `activitytype_3` (`activitytype`,`status`),
  KEY `smownerid` (`smownerid`),
  KEY `linkextend` (`linkextend`),
  CONSTRAINT `fk_1_vtiger_activity` FOREIGN KEY (`activityid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_activity_reminder` */

CREATE TABLE `vtiger_activity_reminder` (
  `activity_id` int(10) NOT NULL,
  `reminder_time` int(10) NOT NULL,
  `reminder_sent` int(2) NOT NULL,
  PRIMARY KEY (`activity_id`),
  CONSTRAINT `vtiger_activity_reminder_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_activity_reminder_popup` */

CREATE TABLE `vtiger_activity_reminder_popup` (
  `reminderid` int(10) NOT NULL AUTO_INCREMENT,
  `recordid` int(10) NOT NULL,
  `datetime` datetime NOT NULL,
  `status` int(2) NOT NULL,
  PRIMARY KEY (`reminderid`),
  KEY `recordid` (`recordid`),
  CONSTRAINT `vtiger_activity_reminder_popup_ibfk_1` FOREIGN KEY (`recordid`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_activity_update_dates` */

CREATE TABLE `vtiger_activity_update_dates` (
  `activityid` int(10) NOT NULL,
  `parent` int(10) NOT NULL,
  `task_id` int(10) NOT NULL,
  PRIMARY KEY (`activityid`),
  KEY `parent` (`parent`),
  KEY `vtiger_activity_update_dates_ibfk_1` (`task_id`),
  CONSTRAINT `vtiger_activity_update_dates_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `com_vtiger_workflowtasks` (`task_id`) ON DELETE CASCADE,
  CONSTRAINT `vtiger_activity_update_dates_ibfk_2` FOREIGN KEY (`parent`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE,
  CONSTRAINT `vtiger_activity_update_dates_ibfk_3` FOREIGN KEY (`activityid`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_activity_view` */

CREATE TABLE `vtiger_activity_view` (
  `activity_viewid` int(10) NOT NULL AUTO_INCREMENT,
  `activity_view` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`activity_viewid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_activitycf` */

CREATE TABLE `vtiger_activitycf` (
  `activityid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`activityid`),
  CONSTRAINT `vtiger_activitycf_ibfk_1` FOREIGN KEY (`activityid`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_activityproductrel` */

CREATE TABLE `vtiger_activityproductrel` (
  `activityid` int(10) NOT NULL DEFAULT 0,
  `productid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`activityid`,`productid`),
  KEY `activityproductrel_activityid_idx` (`activityid`),
  KEY `activityproductrel_productid_idx` (`productid`),
  CONSTRAINT `fk_2_vtiger_activityproductrel` FOREIGN KEY (`productid`) REFERENCES `vtiger_products` (`productid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_activityregister_status` */

CREATE TABLE `vtiger_activityregister_status` (
  `activityregister_statusid` int(11) NOT NULL AUTO_INCREMENT,
  `activityregister_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`activityregister_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_activitystatus` */

CREATE TABLE `vtiger_activitystatus` (
  `activitystatusid` int(10) NOT NULL AUTO_INCREMENT,
  `activitystatus` varchar(200) DEFAULT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`activitystatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_activitytype` */

CREATE TABLE `vtiger_activitytype` (
  `activitytypeid` int(10) NOT NULL AUTO_INCREMENT,
  `activitytype` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  `color` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`activitytypeid`),
  UNIQUE KEY `activitytype_activitytype_idx` (`activitytype`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_announcementstatus` */

CREATE TABLE `vtiger_announcementstatus` (
  `announcementstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `announcementstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`announcementstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_assets` */

CREATE TABLE `vtiger_assets` (
  `assetsid` int(10) NOT NULL,
  `asset_no` varchar(30) NOT NULL,
  `product` int(10) NOT NULL,
  `serialnumber` varchar(200) DEFAULT NULL,
  `datesold` date DEFAULT NULL,
  `dateinservice` date DEFAULT NULL,
  `assetstatus` varchar(200) DEFAULT 'PLL_DRAFT',
  `assetname` varchar(100) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `parent_id` int(10) DEFAULT NULL,
  `ordertime` decimal(10,2) DEFAULT NULL,
  `pscategory` varchar(255) DEFAULT '',
  `ssalesprocessesid` int(10) DEFAULT NULL,
  `assets_renew` varchar(255) DEFAULT NULL,
  `renewalinvoice` int(10) DEFAULT NULL,
  `contactid` int(19) DEFAULT NULL,
  PRIMARY KEY (`assetsid`),
  KEY `parent_id` (`parent_id`),
  KEY `product` (`product`),
  KEY `ssalesprocessesid` (`ssalesprocessesid`),
  KEY `renewalinvoice` (`renewalinvoice`),
  KEY `contactid_idx` (`contactid`),
  CONSTRAINT `fk_1_vtiger_assets` FOREIGN KEY (`assetsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_assets_renew` */

CREATE TABLE `vtiger_assets_renew` (
  `assets_renewid` int(10) NOT NULL AUTO_INCREMENT,
  `assets_renew` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`assets_renewid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_assetscf` */

CREATE TABLE `vtiger_assetscf` (
  `assetsid` int(10) NOT NULL,
  PRIMARY KEY (`assetsid`),
  CONSTRAINT `vtiger_assetscf_ibfk_1` FOREIGN KEY (`assetsid`) REFERENCES `vtiger_assets` (`assetsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_assetstatus` */

CREATE TABLE `vtiger_assetstatus` (
  `assetstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `assetstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`assetstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_asterisk` */

CREATE TABLE `vtiger_asterisk` (
  `server` varchar(30) DEFAULT NULL,
  `port` varchar(30) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_asteriskextensions` */

CREATE TABLE `vtiger_asteriskextensions` (
  `userid` smallint(5) unsigned NOT NULL,
  `asterisk_extension` varchar(50) DEFAULT NULL,
  `use_asterisk` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_asteriskincomingcalls` */

CREATE TABLE `vtiger_asteriskincomingcalls` (
  `from_number` varchar(50) DEFAULT NULL,
  `from_name` varchar(50) DEFAULT NULL,
  `to_number` varchar(50) DEFAULT NULL,
  `callertype` varchar(30) DEFAULT NULL,
  `flag` int(10) DEFAULT NULL,
  `timer` int(10) DEFAULT NULL,
  `refuid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_asteriskincomingevents` */

CREATE TABLE `vtiger_asteriskincomingevents` (
  `uid` varchar(255) NOT NULL,
  `channel` varchar(100) DEFAULT NULL,
  `from_number` bigint(10) DEFAULT NULL,
  `from_name` varchar(100) DEFAULT NULL,
  `to_number` bigint(10) DEFAULT NULL,
  `callertype` varchar(100) DEFAULT NULL,
  `timer` int(10) DEFAULT NULL,
  `flag` varchar(3) DEFAULT NULL,
  `pbxrecordid` int(10) DEFAULT NULL,
  `relcrmid` int(10) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_attachments` */

CREATE TABLE `vtiger_attachments` (
  `attachmentsid` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`attachmentsid`),
  CONSTRAINT `fk_1_vtiger_attachments` FOREIGN KEY (`attachmentsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_audit_trial` */

CREATE TABLE `vtiger_audit_trial` (
  `auditid` int(10) NOT NULL,
  `userid` int(10) DEFAULT NULL,
  `module` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `recordid` varchar(20) DEFAULT NULL,
  `actiondate` datetime DEFAULT NULL,
  PRIMARY KEY (`auditid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_auditregister_status` */

CREATE TABLE `vtiger_auditregister_status` (
  `auditregister_statusid` int(11) NOT NULL AUTO_INCREMENT,
  `auditregister_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`auditregister_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_auditregister_type` */

CREATE TABLE `vtiger_auditregister_type` (
  `auditregister_typeid` int(11) NOT NULL AUTO_INCREMENT,
  `auditregister_type` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`auditregister_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_authy_methods` */

CREATE TABLE `vtiger_authy_methods` (
  `authy_methodsid` int(11) NOT NULL AUTO_INCREMENT,
  `authy_methods` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`authy_methodsid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_blocks` */

CREATE TABLE `vtiger_blocks` (
  `blockid` int(10) NOT NULL AUTO_INCREMENT,
  `tabid` smallint(5) NOT NULL,
  `blocklabel` varchar(100) NOT NULL,
  `sequence` tinyint(3) unsigned DEFAULT NULL,
  `show_title` tinyint(1) unsigned DEFAULT NULL,
  `visible` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `create_view` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `edit_view` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `detail_view` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `display_status` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `iscustom` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`blockid`),
  KEY `block_tabid_idx` (`tabid`),
  KEY `block_sequence_idx` (`sequence`),
  CONSTRAINT `fk_1_vtiger_blocks` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=438 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_blocks_hide` */

CREATE TABLE `vtiger_blocks_hide` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blockid` int(10) unsigned DEFAULT NULL,
  `conditions` text DEFAULT NULL,
  `enabled` tinyint(1) unsigned DEFAULT NULL,
  `view` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blockid` (`blockid`,`enabled`),
  KEY `view` (`view`),
  KEY `blockid_2` (`blockid`,`enabled`,`view`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_calendar_config` */

CREATE TABLE `vtiger_calendar_config` (
  `type` varchar(10) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `label` varchar(20) DEFAULT NULL,
  `value` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_callhistory` */

CREATE TABLE `vtiger_callhistory` (
  `callhistoryid` int(10) NOT NULL,
  `callhistorytype` varchar(255) DEFAULT NULL,
  `from_number` varchar(30) DEFAULT NULL,
  `to_number` varchar(30) DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `phonecallid` varchar(100) DEFAULT NULL,
  `duration` int(10) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `imei` varchar(100) DEFAULT NULL,
  `ipaddress` varchar(100) DEFAULT NULL,
  `simserial` varchar(100) DEFAULT NULL,
  `subscriberid` varchar(100) DEFAULT NULL,
  `destination` int(10) DEFAULT NULL,
  `source` int(10) DEFAULT NULL,
  `from_number_extra` varchar(100) DEFAULT NULL,
  `to_number_extra` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`callhistoryid`),
  KEY `source` (`source`),
  KEY `destination` (`destination`),
  CONSTRAINT `vtiger_callhistory_ibfk_1` FOREIGN KEY (`callhistoryid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_callhistorycf` */

CREATE TABLE `vtiger_callhistorycf` (
  `callhistoryid` int(10) NOT NULL,
  PRIMARY KEY (`callhistoryid`),
  CONSTRAINT `vtiger_callhistorycf_ibfk_1` FOREIGN KEY (`callhistoryid`) REFERENCES `vtiger_callhistory` (`callhistoryid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_callhistorytype` */

CREATE TABLE `vtiger_callhistorytype` (
  `callhistorytypeid` int(10) NOT NULL AUTO_INCREMENT,
  `callhistorytype` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`callhistorytypeid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_campaign` */

CREATE TABLE `vtiger_campaign` (
  `campaign_no` varchar(100) NOT NULL,
  `campaignname` varchar(255) DEFAULT NULL,
  `campaigntype` varchar(200) DEFAULT NULL,
  `campaignstatus` varchar(200) DEFAULT NULL,
  `expectedrevenue` decimal(25,8) DEFAULT NULL,
  `budgetcost` decimal(25,8) DEFAULT NULL,
  `actualcost` decimal(25,8) DEFAULT NULL,
  `expectedresponse` varchar(200) DEFAULT NULL,
  `numsent` decimal(11,0) DEFAULT NULL,
  `product_id` int(10) DEFAULT NULL,
  `sponsor` varchar(255) DEFAULT NULL,
  `targetaudience` varchar(255) DEFAULT NULL,
  `targetsize` int(10) DEFAULT NULL,
  `expectedresponsecount` int(10) DEFAULT NULL,
  `expectedsalescount` int(10) DEFAULT NULL,
  `expectedroi` decimal(25,8) DEFAULT NULL,
  `actualresponsecount` int(10) DEFAULT NULL,
  `actualsalescount` int(10) DEFAULT NULL,
  `actualroi` decimal(25,8) DEFAULT NULL,
  `campaignid` int(10) NOT NULL,
  `closingdate` date DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`campaignid`),
  KEY `campaign_campaignstatus_idx` (`campaignstatus`),
  KEY `campaign_campaignname_idx` (`campaignname`),
  KEY `campaign_campaignid_idx` (`campaignid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_campaign_records` */

CREATE TABLE `vtiger_campaign_records` (
  `campaignid` int(10) NOT NULL DEFAULT 0,
  `crmid` int(10) NOT NULL DEFAULT 0,
  `campaignrelstatusid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`campaignid`,`crmid`,`campaignrelstatusid`),
  KEY `campaigncontrel_contractid_idx` (`crmid`),
  CONSTRAINT `fk_vtiger_crmentity` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_campaignscf` */

CREATE TABLE `vtiger_campaignscf` (
  `campaignid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`campaignid`),
  CONSTRAINT `fk_1_vtiger_campaignscf` FOREIGN KEY (`campaignid`) REFERENCES `vtiger_campaign` (`campaignid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_campaignstatus` */

CREATE TABLE `vtiger_campaignstatus` (
  `campaignstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `campaignstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`campaignstatusid`),
  KEY `campaignstatus_campaignstatus_idx` (`campaignstatus`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_campaigntype` */

CREATE TABLE `vtiger_campaigntype` (
  `campaigntypeid` int(10) NOT NULL AUTO_INCREMENT,
  `campaigntype` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`campaigntypeid`),
  UNIQUE KEY `campaigntype_campaigntype_idx` (`campaigntype`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_cmileage_logbook_status` */

CREATE TABLE `vtiger_cmileage_logbook_status` (
  `cmileage_logbook_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `cmileage_logbook_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`cmileage_logbook_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_contactaddress` */

CREATE TABLE `vtiger_contactaddress` (
  `contactaddressid` int(10) NOT NULL DEFAULT 0,
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
  PRIMARY KEY (`contactaddressid`),
  CONSTRAINT `fk_1_vtiger_contactaddress` FOREIGN KEY (`contactaddressid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_contactdetails` */

CREATE TABLE `vtiger_contactdetails` (
  `contactid` int(10) NOT NULL DEFAULT 0,
  `contact_no` varchar(100) NOT NULL,
  `parentid` int(10) DEFAULT NULL,
  `salutation` varchar(200) DEFAULT NULL,
  `firstname` varchar(40) DEFAULT NULL,
  `lastname` varchar(80) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `reportsto` int(11) DEFAULT NULL,
  `training` varchar(50) DEFAULT NULL,
  `usertype` varchar(50) DEFAULT NULL,
  `contacttype` varchar(50) DEFAULT NULL,
  `otheremail` varchar(100) DEFAULT NULL,
  `donotcall` smallint(1) DEFAULT NULL,
  `emailoptout` smallint(1) DEFAULT 0,
  `imagename` text DEFAULT NULL,
  `isconvertedfromlead` smallint(1) DEFAULT 0,
  `verification` text DEFAULT NULL,
  `secondary_email` varchar(100) DEFAULT '',
  `notifilanguage` varchar(100) DEFAULT '',
  `contactstatus` varchar(255) DEFAULT '',
  `dav_status` tinyint(1) DEFAULT 1,
  `jobtitle` varchar(100) DEFAULT '',
  `decision_maker` tinyint(1) DEFAULT 0,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `active` tinyint(1) DEFAULT 0,
  `phone_extra` varchar(100) DEFAULT NULL,
  `mobile_extra` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`contactid`),
  KEY `contactdetails_accountid_idx` (`parentid`),
  KEY `email_idx` (`email`),
  KEY `lastname` (`lastname`),
  CONSTRAINT `fk_1_vtiger_contactdetails` FOREIGN KEY (`contactid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_contactscf` */

CREATE TABLE `vtiger_contactscf` (
  `contactid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`contactid`),
  CONSTRAINT `fk_1_vtiger_contactscf` FOREIGN KEY (`contactid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_contactstatus` */

CREATE TABLE `vtiger_contactstatus` (
  `contactstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `contactstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`contactstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_contactsubdetails` */

CREATE TABLE `vtiger_contactsubdetails` (
  `contactsubscriptionid` int(10) NOT NULL DEFAULT 0,
  `birthday` date DEFAULT NULL,
  `laststayintouchrequest` int(10) DEFAULT 0,
  `laststayintouchsavedate` int(10) DEFAULT 0,
  `leadsource` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`contactsubscriptionid`),
  CONSTRAINT `fk_1_vtiger_contactsubdetails` FOREIGN KEY (`contactsubscriptionid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_contract_priority` */

CREATE TABLE `vtiger_contract_priority` (
  `contract_priorityid` int(10) NOT NULL AUTO_INCREMENT,
  `contract_priority` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`contract_priorityid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_contract_status` */

CREATE TABLE `vtiger_contract_status` (
  `contract_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `contract_status` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`contract_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_contract_type` */

CREATE TABLE `vtiger_contract_type` (
  `contract_typeid` int(10) NOT NULL AUTO_INCREMENT,
  `contract_type` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`contract_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_convertleadmapping` */

CREATE TABLE `vtiger_convertleadmapping` (
  `cfmid` int(10) NOT NULL AUTO_INCREMENT,
  `leadfid` int(10) NOT NULL,
  `accountfid` int(10) DEFAULT NULL,
  `editable` int(10) DEFAULT 1,
  PRIMARY KEY (`cfmid`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_crmentity` */

CREATE TABLE `vtiger_crmentity` (
  `crmid` int(10) NOT NULL AUTO_INCREMENT,
  `smcreatorid` smallint(5) unsigned NOT NULL DEFAULT 0,
  `smownerid` smallint(5) unsigned NOT NULL DEFAULT 0,
  `shownerid` tinyint(1) DEFAULT NULL,
  `modifiedby` smallint(5) unsigned NOT NULL DEFAULT 0,
  `setype` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `attention` text DEFAULT NULL,
  `createdtime` datetime NOT NULL,
  `modifiedtime` datetime NOT NULL,
  `viewedtime` datetime DEFAULT NULL,
  `closedtime` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT 0,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `was_read` tinyint(1) DEFAULT 0,
  `private` tinyint(1) DEFAULT 0,
  `users` text DEFAULT NULL,
  PRIMARY KEY (`crmid`),
  KEY `crmentity_smcreatorid_idx` (`smcreatorid`),
  KEY `crmentity_modifiedby_idx` (`modifiedby`),
  KEY `crmentity_deleted_idx` (`deleted`),
  KEY `crm_ownerid_del_setype_idx` (`smownerid`,`deleted`,`setype`),
  KEY `crmid` (`crmid`,`deleted`),
  KEY `crmid_2` (`crmid`,`setype`),
  KEY `setypedeleted` (`setype`,`deleted`),
  KEY `setype` (`setype`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_crmentityrel` */

CREATE TABLE `vtiger_crmentityrel` (
  `crmid` int(10) NOT NULL,
  `module` varchar(25) NOT NULL,
  `relcrmid` int(10) NOT NULL,
  `relmodule` varchar(25) NOT NULL,
  `rel_created_user` int(10) DEFAULT NULL,
  `rel_created_time` datetime DEFAULT NULL,
  `rel_comment` varchar(255) DEFAULT NULL,
  KEY `crmid` (`crmid`),
  KEY `relcrmid` (`relcrmid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_cron_task` */

CREATE TABLE `vtiger_cron_task` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `handler_file` varchar(100) DEFAULT NULL,
  `frequency` int(10) DEFAULT NULL,
  `laststart` int(10) unsigned DEFAULT NULL,
  `lastend` int(10) unsigned DEFAULT NULL,
  `status` int(10) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `sequence` int(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `handler_file` (`handler_file`),
  KEY `vtiger_cron_task_status_idx` (`status`),
  KEY `vtiger_cron_task_sequence_idx` (`sequence`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_currencies` */

CREATE TABLE `vtiger_currencies` (
  `currencyid` int(10) NOT NULL AUTO_INCREMENT,
  `currency_name` varchar(200) DEFAULT NULL,
  `currency_code` varchar(50) DEFAULT NULL,
  `currency_symbol` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`currencyid`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_currencies_seq` */

CREATE TABLE `vtiger_currencies_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_currency` */

CREATE TABLE `vtiger_currency` (
  `currencyid` int(10) NOT NULL AUTO_INCREMENT,
  `currency` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`currencyid`),
  UNIQUE KEY `currency_currency_idx` (`currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_currency_decimal_separator` */

CREATE TABLE `vtiger_currency_decimal_separator` (
  `currency_decimal_separatorid` int(10) NOT NULL AUTO_INCREMENT,
  `currency_decimal_separator` varchar(2) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`currency_decimal_separatorid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_currency_grouping_pattern` */

CREATE TABLE `vtiger_currency_grouping_pattern` (
  `currency_grouping_patternid` int(10) NOT NULL AUTO_INCREMENT,
  `currency_grouping_pattern` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`currency_grouping_patternid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_currency_grouping_separator` */

CREATE TABLE `vtiger_currency_grouping_separator` (
  `currency_grouping_separatorid` int(10) NOT NULL AUTO_INCREMENT,
  `currency_grouping_separator` varchar(2) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`currency_grouping_separatorid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_currency_info` */

CREATE TABLE `vtiger_currency_info` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `currency_name` varchar(100) DEFAULT NULL,
  `currency_code` varchar(100) DEFAULT NULL,
  `currency_symbol` varchar(30) DEFAULT NULL,
  `conversion_rate` decimal(12,5) DEFAULT NULL,
  `currency_status` varchar(25) DEFAULT NULL,
  `defaultid` tinyint(3) NOT NULL DEFAULT 0,
  `deleted` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_currency_symbol_placement` */

CREATE TABLE `vtiger_currency_symbol_placement` (
  `currency_symbol_placementid` int(10) NOT NULL AUTO_INCREMENT,
  `currency_symbol_placement` varchar(30) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`currency_symbol_placementid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_customaction` */

CREATE TABLE `vtiger_customaction` (
  `cvid` int(10) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `module` varchar(50) NOT NULL,
  `content` text DEFAULT NULL,
  KEY `customaction_cvid_idx` (`cvid`),
  CONSTRAINT `fk_1_vtiger_customaction` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_customerdetails` */

CREATE TABLE `vtiger_customerdetails` (
  `customerid` int(10) NOT NULL,
  `portal` smallint(1) DEFAULT NULL,
  `support_start_date` date DEFAULT NULL,
  `support_end_date` date DEFAULT NULL,
  PRIMARY KEY (`customerid`),
  CONSTRAINT `fk_1_vtiger_customerdetails` FOREIGN KEY (`customerid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_customview` */

CREATE TABLE `vtiger_customview` (
  `cvid` int(10) NOT NULL AUTO_INCREMENT,
  `viewname` varchar(100) NOT NULL,
  `setdefault` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `setmetrics` tinyint(1) NOT NULL DEFAULT 0,
  `entitytype` varchar(25) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `userid` int(10) DEFAULT 1,
  `privileges` tinyint(2) DEFAULT 1,
  `featured` tinyint(1) DEFAULT 0,
  `sequence` int(10) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `sort` varchar(30) DEFAULT '',
  `color` varchar(10) DEFAULT '',
  PRIMARY KEY (`cvid`),
  KEY `customview_entitytype_idx` (`entitytype`),
  KEY `setdefault` (`setdefault`,`entitytype`),
  KEY `customview_userid_idx` (`userid`),
  CONSTRAINT `fk_1_vtiger_customview` FOREIGN KEY (`entitytype`) REFERENCES `vtiger_tab` (`name`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_cvcolumnlist` */

CREATE TABLE `vtiger_cvcolumnlist` (
  `cvid` int(10) NOT NULL,
  `columnindex` smallint(3) unsigned NOT NULL,
  `field_name` varchar(50) DEFAULT NULL,
  `module_name` varchar(25) DEFAULT NULL,
  `source_field_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`cvid`,`columnindex`),
  KEY `cvcolumnlist_columnindex_idx` (`columnindex`),
  KEY `cvcolumnlist_cvid_idx` (`cvid`),
  CONSTRAINT `fk_1_vtiger_cvcolumnlist` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datasetregister_status` */

CREATE TABLE `vtiger_datasetregister_status` (
  `datasetregister_statusid` int(11) NOT NULL AUTO_INCREMENT,
  `datasetregister_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`datasetregister_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_grp2grp` */

CREATE TABLE `vtiger_datashare_grp2grp` (
  `shareid` int(10) NOT NULL,
  `share_groupid` int(10) DEFAULT NULL,
  `to_groupid` int(10) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_grp2grp_share_groupid_idx` (`share_groupid`),
  KEY `datashare_grp2grp_to_groupid_idx` (`to_groupid`),
  CONSTRAINT `fk_3_vtiger_datashare_grp2grp` FOREIGN KEY (`to_groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_grp2role` */

CREATE TABLE `vtiger_datashare_grp2role` (
  `shareid` int(10) NOT NULL,
  `share_groupid` int(10) DEFAULT NULL,
  `to_roleid` varchar(255) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `idx_datashare_grp2role_share_groupid` (`share_groupid`),
  KEY `idx_datashare_grp2role_to_roleid` (`to_roleid`),
  CONSTRAINT `fk_3_vtiger_datashare_grp2role` FOREIGN KEY (`to_roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_grp2rs` */

CREATE TABLE `vtiger_datashare_grp2rs` (
  `shareid` int(10) NOT NULL,
  `share_groupid` int(10) DEFAULT NULL,
  `to_roleandsubid` varchar(255) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_grp2rs_share_groupid_idx` (`share_groupid`),
  KEY `datashare_grp2rs_to_roleandsubid_idx` (`to_roleandsubid`),
  CONSTRAINT `fk_3_vtiger_datashare_grp2rs` FOREIGN KEY (`to_roleandsubid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_grp2us` */

CREATE TABLE `vtiger_datashare_grp2us` (
  `shareid` int(10) NOT NULL,
  `share_groupid` int(10) DEFAULT NULL,
  `to_userid` int(10) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_grp2us_share_groupid_idx` (`share_groupid`),
  KEY `datashare_grp2us_to_userid_idx` (`to_userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_module_rel` */

CREATE TABLE `vtiger_datashare_module_rel` (
  `shareid` int(10) NOT NULL AUTO_INCREMENT,
  `tabid` smallint(5) NOT NULL,
  `relationtype` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `idx_datashare_module_rel_tabid` (`tabid`),
  CONSTRAINT `fk_1_vtiger_datashare_module_rel` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_relatedmodule_permission` */

CREATE TABLE `vtiger_datashare_relatedmodule_permission` (
  `shareid` int(10) NOT NULL,
  `datashare_relatedmodule_id` int(10) NOT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`,`datashare_relatedmodule_id`),
  KEY `datashare_relatedmodule_permission_shareid_permissions_idx` (`shareid`,`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_relatedmodules` */

CREATE TABLE `vtiger_datashare_relatedmodules` (
  `datashare_relatedmodule_id` int(10) NOT NULL,
  `tabid` smallint(5) DEFAULT NULL,
  `relatedto_tabid` int(10) DEFAULT NULL,
  PRIMARY KEY (`datashare_relatedmodule_id`),
  KEY `datashare_relatedmodules_tabid_idx` (`tabid`),
  KEY `datashare_relatedmodules_relatedto_tabid_idx` (`relatedto_tabid`),
  CONSTRAINT `fk_2_vtiger_datashare_relatedmodules` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_relatedmodules_seq` */

CREATE TABLE `vtiger_datashare_relatedmodules_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_role2group` */

CREATE TABLE `vtiger_datashare_role2group` (
  `shareid` int(10) NOT NULL,
  `share_roleid` varchar(255) DEFAULT NULL,
  `to_groupid` int(10) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `idx_datashare_role2group_share_roleid` (`share_roleid`),
  KEY `idx_datashare_role2group_to_groupid` (`to_groupid`),
  CONSTRAINT `fk_3_vtiger_datashare_role2group` FOREIGN KEY (`share_roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_role2role` */

CREATE TABLE `vtiger_datashare_role2role` (
  `shareid` int(10) NOT NULL,
  `share_roleid` varchar(255) DEFAULT NULL,
  `to_roleid` varchar(255) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_role2role_share_roleid_idx` (`share_roleid`),
  KEY `datashare_role2role_to_roleid_idx` (`to_roleid`),
  CONSTRAINT `fk_3_vtiger_datashare_role2role` FOREIGN KEY (`to_roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_role2rs` */

CREATE TABLE `vtiger_datashare_role2rs` (
  `shareid` int(10) NOT NULL,
  `share_roleid` varchar(255) DEFAULT NULL,
  `to_roleandsubid` varchar(255) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_role2s_share_roleid_idx` (`share_roleid`),
  KEY `datashare_role2s_to_roleandsubid_idx` (`to_roleandsubid`),
  CONSTRAINT `fk_3_vtiger_datashare_role2rs` FOREIGN KEY (`to_roleandsubid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_role2us` */

CREATE TABLE `vtiger_datashare_role2us` (
  `shareid` int(10) NOT NULL,
  `share_roleid` varchar(255) DEFAULT NULL,
  `to_userid` int(10) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_role2us_share_roleid_idx` (`share_roleid`),
  KEY `datashare_role2us_to_userid_idx` (`to_userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_rs2grp` */

CREATE TABLE `vtiger_datashare_rs2grp` (
  `shareid` int(10) NOT NULL,
  `share_roleandsubid` varchar(255) DEFAULT NULL,
  `to_groupid` int(10) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_rs2grp_share_roleandsubid_idx` (`share_roleandsubid`),
  KEY `datashare_rs2grp_to_groupid_idx` (`to_groupid`),
  CONSTRAINT `fk_3_vtiger_datashare_rs2grp` FOREIGN KEY (`share_roleandsubid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_rs2role` */

CREATE TABLE `vtiger_datashare_rs2role` (
  `shareid` int(10) NOT NULL,
  `share_roleandsubid` varchar(255) DEFAULT NULL,
  `to_roleid` varchar(255) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_rs2role_share_roleandsubid_idx` (`share_roleandsubid`),
  KEY `datashare_rs2role_to_roleid_idx` (`to_roleid`),
  CONSTRAINT `fk_3_vtiger_datashare_rs2role` FOREIGN KEY (`to_roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_rs2rs` */

CREATE TABLE `vtiger_datashare_rs2rs` (
  `shareid` int(10) NOT NULL,
  `share_roleandsubid` varchar(255) DEFAULT NULL,
  `to_roleandsubid` varchar(255) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_rs2rs_share_roleandsubid_idx` (`share_roleandsubid`),
  KEY `idx_datashare_rs2rs_to_roleandsubid_idx` (`to_roleandsubid`),
  CONSTRAINT `fk_3_vtiger_datashare_rs2rs` FOREIGN KEY (`to_roleandsubid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_rs2us` */

CREATE TABLE `vtiger_datashare_rs2us` (
  `shareid` int(10) NOT NULL,
  `share_roleandsubid` varchar(255) DEFAULT NULL,
  `to_userid` int(10) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_rs2us_share_roleandsubid_idx` (`share_roleandsubid`),
  KEY `datashare_rs2us_to_userid_idx` (`to_userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_us2grp` */

CREATE TABLE `vtiger_datashare_us2grp` (
  `shareid` int(10) NOT NULL,
  `share_userid` int(10) DEFAULT NULL,
  `to_groupid` int(10) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_us2grp_share_userid_idx` (`share_userid`),
  KEY `datashare_us2grp_to_groupid_idx` (`to_groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_us2role` */

CREATE TABLE `vtiger_datashare_us2role` (
  `shareid` int(10) NOT NULL,
  `share_userid` int(10) DEFAULT NULL,
  `to_roleid` varchar(255) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `idx_datashare_us2role_share_userid` (`share_userid`),
  KEY `idx_datashare_us2role_to_roleid` (`to_roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_us2rs` */

CREATE TABLE `vtiger_datashare_us2rs` (
  `shareid` int(10) NOT NULL,
  `share_userid` int(10) DEFAULT NULL,
  `to_roleandsubid` varchar(255) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_us2rs_share_userid_idx` (`share_userid`),
  KEY `datashare_us2rs_to_roleandsubid_idx` (`to_roleandsubid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_datashare_us2us` */

CREATE TABLE `vtiger_datashare_us2us` (
  `shareid` int(10) NOT NULL,
  `share_userid` int(10) DEFAULT NULL,
  `to_userid` int(10) DEFAULT NULL,
  `permission` int(10) DEFAULT NULL,
  PRIMARY KEY (`shareid`),
  KEY `datashare_us2us_share_userid_idx` (`share_userid`),
  KEY `datashare_us2us_to_userid_idx` (`to_userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_date_format` */

CREATE TABLE `vtiger_date_format` (
  `date_formatid` int(10) NOT NULL AUTO_INCREMENT,
  `date_format` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`date_formatid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_dayoftheweek` */

CREATE TABLE `vtiger_dayoftheweek` (
  `dayoftheweekid` int(10) NOT NULL AUTO_INCREMENT,
  `dayoftheweek` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`dayoftheweekid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_def_org_share` */

CREATE TABLE `vtiger_def_org_share` (
  `ruleid` int(10) NOT NULL AUTO_INCREMENT,
  `tabid` smallint(5) NOT NULL,
  `permission` int(10) DEFAULT NULL,
  `editstatus` int(10) DEFAULT NULL,
  PRIMARY KEY (`ruleid`),
  KEY `fk_1_vtiger_def_org_share` (`permission`),
  CONSTRAINT `fk_1_vtiger_def_org_share` FOREIGN KEY (`permission`) REFERENCES `vtiger_org_share_action_mapping` (`share_action_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_default_record_view` */

CREATE TABLE `vtiger_default_record_view` (
  `default_record_viewid` int(10) NOT NULL AUTO_INCREMENT,
  `default_record_view` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`default_record_viewid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_defaultactivitytype` */

CREATE TABLE `vtiger_defaultactivitytype` (
  `defaultactivitytypeid` int(10) NOT NULL AUTO_INCREMENT,
  `defaultactivitytype` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`defaultactivitytypeid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_defaultcv` */

CREATE TABLE `vtiger_defaultcv` (
  `tabid` smallint(5) NOT NULL,
  `defaultviewname` varchar(50) NOT NULL,
  `query` text DEFAULT NULL,
  PRIMARY KEY (`tabid`),
  CONSTRAINT `fk_1_vtiger_defaultcv` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_defaulteventstatus` */

CREATE TABLE `vtiger_defaulteventstatus` (
  `defaulteventstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `defaulteventstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`defaulteventstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_duration_minutes` */

CREATE TABLE `vtiger_duration_minutes` (
  `minutesid` int(10) NOT NULL AUTO_INCREMENT,
  `duration_minutes` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`minutesid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_durationhrs` */

CREATE TABLE `vtiger_durationhrs` (
  `hrsid` int(10) NOT NULL AUTO_INCREMENT,
  `hrs` varchar(50) DEFAULT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`hrsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_durationmins` */

CREATE TABLE `vtiger_durationmins` (
  `minsid` int(10) NOT NULL AUTO_INCREMENT,
  `mins` varchar(50) DEFAULT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`minsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_email_template_priority` */

CREATE TABLE `vtiger_email_template_priority` (
  `email_template_priorityid` int(10) NOT NULL AUTO_INCREMENT,
  `email_template_priority` tinyint(1) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`email_template_priorityid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_email_template_type` */

CREATE TABLE `vtiger_email_template_type` (
  `email_template_typeid` int(10) NOT NULL AUTO_INCREMENT,
  `email_template_type` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`email_template_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_employee_education` */

CREATE TABLE `vtiger_employee_education` (
  `employee_educationid` int(10) NOT NULL AUTO_INCREMENT,
  `employee_education` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`employee_educationid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_employee_status` */

CREATE TABLE `vtiger_employee_status` (
  `employee_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `employee_status` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`employee_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_end_hour` */

CREATE TABLE `vtiger_end_hour` (
  `end_hourid` int(10) NOT NULL AUTO_INCREMENT,
  `end_hour` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`end_hourid`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_entity_stats` */

CREATE TABLE `vtiger_entity_stats` (
  `crmid` int(10) NOT NULL,
  `crmactivity` mediumint(8) DEFAULT NULL,
  PRIMARY KEY (`crmid`),
  CONSTRAINT `fk_1_vtiger_entity_stats` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_entityname` */

CREATE TABLE `vtiger_entityname` (
  `tabid` smallint(5) NOT NULL DEFAULT 0,
  `modulename` varchar(25) NOT NULL,
  `tablename` varchar(50) NOT NULL,
  `fieldname` varchar(100) NOT NULL,
  `entityidfield` varchar(30) NOT NULL,
  `entityidcolumn` varchar(30) NOT NULL,
  `searchcolumn` varchar(150) NOT NULL,
  `turn_off` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `sequence` smallint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`tabid`),
  KEY `turn_off` (`turn_off`),
  CONSTRAINT `fk_1_vtiger_entityname` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_eventhandlers` */

CREATE TABLE `vtiger_eventhandlers` (
  `eventhandler_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `event_name` varchar(50) NOT NULL,
  `handler_class` varchar(100) NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `include_modules` varchar(255) NOT NULL DEFAULT '',
  `exclude_modules` varchar(255) NOT NULL DEFAULT '',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT 5,
  `owner_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`eventhandler_id`),
  KEY `event_name_class` (`event_name`,`handler_class`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_eventstatus` */

CREATE TABLE `vtiger_eventstatus` (
  `eventstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `eventstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`eventstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_eventstatus_seq` */

CREATE TABLE `vtiger_eventstatus_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_expectedresponse` */

CREATE TABLE `vtiger_expectedresponse` (
  `expectedresponseid` int(10) NOT NULL AUTO_INCREMENT,
  `expectedresponse` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`expectedresponseid`),
  UNIQUE KEY `CampaignExpRes_UK01` (`expectedresponse`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_faq` */

CREATE TABLE `vtiger_faq` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `faq_no` varchar(100) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `question` text DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `category` varchar(200) NOT NULL,
  `status` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `faq_id_idx` (`id`),
  CONSTRAINT `fk_1_vtiger_faq` FOREIGN KEY (`id`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_faqcategories` */

CREATE TABLE `vtiger_faqcategories` (
  `faqcategories_id` int(10) NOT NULL AUTO_INCREMENT,
  `faqcategories` varchar(200) DEFAULT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`faqcategories_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_faqcf` */

CREATE TABLE `vtiger_faqcf` (
  `faqid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`faqid`),
  CONSTRAINT `fk_1_vtiger_faqcf` FOREIGN KEY (`faqid`) REFERENCES `vtiger_faq` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_faqcomments` */

CREATE TABLE `vtiger_faqcomments` (
  `commentid` int(10) NOT NULL AUTO_INCREMENT,
  `faqid` int(10) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `createdtime` datetime NOT NULL,
  PRIMARY KEY (`commentid`),
  KEY `faqcomments_faqid_idx` (`faqid`),
  CONSTRAINT `fk_1_vtiger_faqcomments` FOREIGN KEY (`faqid`) REFERENCES `vtiger_faq` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_faqstatus` */

CREATE TABLE `vtiger_faqstatus` (
  `faqstatus_id` int(10) NOT NULL AUTO_INCREMENT,
  `faqstatus` varchar(200) DEFAULT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`faqstatus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_fcorectinginvoice_formpayment` */

CREATE TABLE `vtiger_fcorectinginvoice_formpayment` (
  `fcorectinginvoice_formpaymentid` int(10) NOT NULL AUTO_INCREMENT,
  `fcorectinginvoice_formpayment` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`fcorectinginvoice_formpaymentid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_fcorectinginvoice_status` */

CREATE TABLE `vtiger_fcorectinginvoice_status` (
  `fcorectinginvoice_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `fcorectinginvoice_status` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`fcorectinginvoice_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_feedback` */

CREATE TABLE `vtiger_feedback` (
  `userid` int(10) DEFAULT NULL,
  `dontshow` varchar(19) DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_field` */

CREATE TABLE `vtiger_field` (
  `tabid` smallint(5) NOT NULL,
  `fieldid` int(10) NOT NULL AUTO_INCREMENT,
  `columnname` varchar(30) NOT NULL,
  `tablename` varchar(50) NOT NULL,
  `generatedtype` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `uitype` smallint(5) unsigned NOT NULL,
  `fieldname` varchar(50) NOT NULL,
  `fieldlabel` varchar(50) NOT NULL,
  `readonly` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `defaultvalue` text DEFAULT NULL,
  `maximumlength` varchar(30) DEFAULT NULL,
  `sequence` smallint(5) unsigned NOT NULL,
  `block` int(10) DEFAULT NULL,
  `displaytype` tinyint(1) unsigned NOT NULL,
  `typeofdata` varchar(100) DEFAULT NULL,
  `quickcreate` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `quickcreatesequence` tinyint(3) unsigned DEFAULT NULL,
  `info_type` char(3) DEFAULT NULL,
  `masseditable` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `helpinfo` varchar(30) DEFAULT '',
  `summaryfield` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `fieldparams` varchar(255) DEFAULT '',
  `header_field` varchar(255) DEFAULT NULL,
  `maxlengthtext` smallint(3) unsigned DEFAULT 0,
  `maxwidthcolumn` smallint(3) unsigned DEFAULT 0,
  `visible` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`fieldid`),
  KEY `field_tabid_idx` (`tabid`),
  KEY `field_fieldname_idx` (`fieldname`),
  KEY `field_block_idx` (`block`),
  KEY `field_displaytype_idx` (`displaytype`),
  KEY `tabid` (`tabid`,`tablename`),
  KEY `quickcreate` (`quickcreate`),
  KEY `presence` (`presence`),
  KEY `tabid_2` (`tabid`,`fieldname`),
  KEY `tabid_3` (`tabid`,`block`),
  KEY `field_sequence_idx` (`sequence`),
  KEY `field_uitype_idx` (`uitype`),
  CONSTRAINT `fk_1_vtiger_field` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2789 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_field_seq` */

CREATE TABLE `vtiger_field_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_fieldmodulerel` */

CREATE TABLE `vtiger_fieldmodulerel` (
  `fieldid` smallint(5) unsigned NOT NULL,
  `module` varchar(25) NOT NULL,
  `relmodule` varchar(25) NOT NULL,
  `status` varchar(10) DEFAULT NULL,
  `sequence` tinyint(1) unsigned DEFAULT 0,
  KEY `fieldid` (`fieldid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_finvoice_formpayment` */

CREATE TABLE `vtiger_finvoice_formpayment` (
  `finvoice_formpaymentid` int(10) NOT NULL AUTO_INCREMENT,
  `finvoice_formpayment` varchar(200) NOT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`finvoice_formpaymentid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_finvoice_paymentstatus` */

CREATE TABLE `vtiger_finvoice_paymentstatus` (
  `finvoice_paymentstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `finvoice_paymentstatus` varchar(200) NOT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`finvoice_paymentstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_finvoice_status` */

CREATE TABLE `vtiger_finvoice_status` (
  `finvoice_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `finvoice_status` varchar(200) NOT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`finvoice_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_finvoice_type` */

CREATE TABLE `vtiger_finvoice_type` (
  `finvoice_typeid` int(10) NOT NULL AUTO_INCREMENT,
  `finvoice_type` varchar(200) NOT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`finvoice_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_finvoicecost_formpayment` */

CREATE TABLE `vtiger_finvoicecost_formpayment` (
  `finvoicecost_formpaymentid` int(10) NOT NULL AUTO_INCREMENT,
  `finvoicecost_formpayment` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`finvoicecost_formpaymentid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_finvoicecost_paymentstatus` */

CREATE TABLE `vtiger_finvoicecost_paymentstatus` (
  `finvoicecost_paymentstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `finvoicecost_paymentstatus` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`finvoicecost_paymentstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_finvoicecost_status` */

CREATE TABLE `vtiger_finvoicecost_status` (
  `finvoicecost_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `finvoicecost_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`finvoicecost_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_finvoiceproforma_formpayment` */

CREATE TABLE `vtiger_finvoiceproforma_formpayment` (
  `finvoiceproforma_formpaymentid` int(10) NOT NULL AUTO_INCREMENT,
  `finvoiceproforma_formpayment` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`finvoiceproforma_formpaymentid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_finvoiceproforma_status` */

CREATE TABLE `vtiger_finvoiceproforma_status` (
  `finvoiceproforma_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `finvoiceproforma_status` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`finvoiceproforma_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_fixed_assets_fuel_type` */

CREATE TABLE `vtiger_fixed_assets_fuel_type` (
  `fixed_assets_fuel_typeid` int(10) NOT NULL AUTO_INCREMENT,
  `fixed_assets_fuel_type` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`fixed_assets_fuel_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_fixed_assets_status` */

CREATE TABLE `vtiger_fixed_assets_status` (
  `fixed_assets_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `fixed_assets_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`fixed_assets_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_fixed_assets_type` */

CREATE TABLE `vtiger_fixed_assets_type` (
  `fixed_assets_typeid` int(10) NOT NULL AUTO_INCREMENT,
  `fixed_assets_type` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`fixed_assets_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_glacct` */

CREATE TABLE `vtiger_glacct` (
  `glacctid` int(10) NOT NULL AUTO_INCREMENT,
  `glacct` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`glacctid`),
  UNIQUE KEY `glacct_glacct_idx` (`glacct`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_group2grouprel` */

CREATE TABLE `vtiger_group2grouprel` (
  `groupid` int(10) NOT NULL,
  `containsgroupid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`groupid`,`containsgroupid`),
  KEY `containsgroupid` (`containsgroupid`),
  KEY `groupid` (`groupid`),
  CONSTRAINT `fk_2_vtiger_group2grouprel` FOREIGN KEY (`groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_group2modules` */

CREATE TABLE `vtiger_group2modules` (
  `groupid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  KEY `groupid` (`groupid`),
  KEY `tabid` (`tabid`),
  CONSTRAINT `vtiger_group2modules_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE,
  CONSTRAINT `vtiger_group2modules_ibfk_2` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_group2role` */

CREATE TABLE `vtiger_group2role` (
  `groupid` int(10) NOT NULL,
  `roleid` varchar(255) NOT NULL,
  PRIMARY KEY (`groupid`,`roleid`),
  KEY `fk_2_vtiger_group2role` (`roleid`),
  CONSTRAINT `fk_2_vtiger_group2role` FOREIGN KEY (`roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_group2rs` */

CREATE TABLE `vtiger_group2rs` (
  `groupid` int(10) unsigned NOT NULL,
  `roleandsubid` varchar(255) NOT NULL,
  PRIMARY KEY (`groupid`,`roleandsubid`),
  KEY `fk_2_vtiger_group2rs` (`roleandsubid`),
  CONSTRAINT `fk_2_vtiger_group2rs` FOREIGN KEY (`roleandsubid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_groups` */

CREATE TABLE `vtiger_groups` (
  `groupid` int(10) NOT NULL,
  `groupname` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(25) DEFAULT '#E6FAD8',
  `modules` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`groupid`),
  UNIQUE KEY `groups_groupname_idx` (`groupname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_holidaysentitlement` */

CREATE TABLE `vtiger_holidaysentitlement` (
  `holidaysentitlementid` int(10) NOT NULL DEFAULT 0,
  `holidaysentitlement_no` varchar(255) DEFAULT NULL,
  `holidaysentitlement_year` varchar(50) DEFAULT NULL,
  `days` int(3) DEFAULT 0,
  `ossemployeesid` int(10) DEFAULT NULL,
  PRIMARY KEY (`holidaysentitlementid`),
  KEY `ossemployeesid` (`ossemployeesid`),
  CONSTRAINT `fk_1_vtiger_holidaysentitlement` FOREIGN KEY (`holidaysentitlementid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_holidaysentitlement_year` */

CREATE TABLE `vtiger_holidaysentitlement_year` (
  `holidaysentitlement_yearid` int(10) NOT NULL AUTO_INCREMENT,
  `holidaysentitlement_year` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`holidaysentitlement_yearid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_holidaysentitlementcf` */

CREATE TABLE `vtiger_holidaysentitlementcf` (
  `holidaysentitlementid` int(10) NOT NULL,
  PRIMARY KEY (`holidaysentitlementid`),
  CONSTRAINT `fk_1_vtiger_holidaysentitlementcf` FOREIGN KEY (`holidaysentitlementid`) REFERENCES `vtiger_holidaysentitlement` (`holidaysentitlementid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_hour_format` */

CREATE TABLE `vtiger_hour_format` (
  `hour_formatid` int(10) NOT NULL AUTO_INCREMENT,
  `hour_format` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`hour_formatid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ideas` */

CREATE TABLE `vtiger_ideas` (
  `ideasid` int(10) NOT NULL DEFAULT 0,
  `ideas_no` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `ideasstatus` varchar(255) DEFAULT '',
  `extent_description` text DEFAULT NULL,
  PRIMARY KEY (`ideasid`),
  CONSTRAINT `fk_1_vtiger_ideas` FOREIGN KEY (`ideasid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ideascf` */

CREATE TABLE `vtiger_ideascf` (
  `ideasid` int(10) NOT NULL,
  PRIMARY KEY (`ideasid`),
  CONSTRAINT `fk_1_vtiger_ideascf` FOREIGN KEY (`ideasid`) REFERENCES `vtiger_ideas` (`ideasid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ideasstatus` */

CREATE TABLE `vtiger_ideasstatus` (
  `ideasstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `ideasstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`ideasstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_igdn_status` */

CREATE TABLE `vtiger_igdn_status` (
  `igdn_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `igdn_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`igdn_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_igdnc_status` */

CREATE TABLE `vtiger_igdnc_status` (
  `igdnc_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `igdnc_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`igdnc_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_igin_status` */

CREATE TABLE `vtiger_igin_status` (
  `igin_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `igin_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`igin_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_igrn_status` */

CREATE TABLE `vtiger_igrn_status` (
  `igrn_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `igrn_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`igrn_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_igrnc_status` */

CREATE TABLE `vtiger_igrnc_status` (
  `igrnc_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `igrnc_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`igrnc_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_iidn_status` */

CREATE TABLE `vtiger_iidn_status` (
  `iidn_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `iidn_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`iidn_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_import_locks` */

CREATE TABLE `vtiger_import_locks` (
  `vtiger_import_lock_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `importid` int(10) NOT NULL,
  `locked_since` datetime DEFAULT NULL,
  PRIMARY KEY (`vtiger_import_lock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_import_maps` */

CREATE TABLE `vtiger_import_maps` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(36) NOT NULL,
  `module` varchar(36) NOT NULL,
  `content` longblob DEFAULT NULL,
  `has_header` int(1) NOT NULL DEFAULT 1,
  `deleted` int(1) NOT NULL DEFAULT 0,
  `date_entered` timestamp NULL DEFAULT NULL,
  `date_modified` timestamp NULL DEFAULT NULL,
  `assigned_user_id` varchar(36) DEFAULT NULL,
  `is_published` varchar(3) NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `import_maps_assigned_user_id_module_name_deleted_idx` (`assigned_user_id`,`module`,`name`,`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_import_queue` */

CREATE TABLE `vtiger_import_queue` (
  `importid` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) unsigned NOT NULL,
  `field_mapping` text DEFAULT NULL,
  `default_values` text DEFAULT NULL,
  `merge_type` int(10) DEFAULT NULL,
  `merge_fields` text DEFAULT NULL,
  `temp_status` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`importid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_incidentregister_status` */

CREATE TABLE `vtiger_incidentregister_status` (
  `incidentregister_statusid` int(11) NOT NULL AUTO_INCREMENT,
  `incidentregister_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`incidentregister_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_incidentregister_type` */

CREATE TABLE `vtiger_incidentregister_type` (
  `incidentregister_typeid` int(11) NOT NULL AUTO_INCREMENT,
  `incidentregister_type` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`incidentregister_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_industry` */

CREATE TABLE `vtiger_industry` (
  `industryid` int(10) NOT NULL AUTO_INCREMENT,
  `industry` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`industryid`),
  UNIQUE KEY `industry_industry_idx` (`industry`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_internal_tickets_status` */

CREATE TABLE `vtiger_internal_tickets_status` (
  `internal_tickets_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `internal_tickets_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`internal_tickets_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_inventory_tandc` */

CREATE TABLE `vtiger_inventory_tandc` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL,
  `tandc` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_inventoryproductrel` */

CREATE TABLE `vtiger_inventoryproductrel` (
  `id` int(10) DEFAULT NULL,
  `productid` int(10) DEFAULT NULL,
  `sequence_no` int(4) DEFAULT NULL,
  `quantity` decimal(25,3) DEFAULT NULL,
  `listprice` decimal(28,8) DEFAULT NULL,
  `discount_percent` decimal(7,3) DEFAULT NULL,
  `discount_amount` decimal(28,8) DEFAULT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `incrementondel` int(10) NOT NULL DEFAULT 0,
  `lineitem_id` int(10) NOT NULL AUTO_INCREMENT,
  `tax` varchar(10) DEFAULT NULL,
  `tax1` decimal(7,3) DEFAULT NULL,
  `tax2` decimal(7,3) DEFAULT NULL,
  `tax3` decimal(7,3) DEFAULT NULL,
  `purchase` decimal(10,2) DEFAULT NULL,
  `margin` decimal(10,2) DEFAULT NULL,
  `marginp` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`lineitem_id`),
  KEY `inventoryproductrel_id_idx` (`id`),
  KEY `inventoryproductrel_productid_idx` (`productid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_inventoryproductrel_seq` */

CREATE TABLE `vtiger_inventoryproductrel_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_inventorysubproductrel` */

CREATE TABLE `vtiger_inventorysubproductrel` (
  `id` int(10) NOT NULL,
  `sequence_no` int(10) NOT NULL,
  `productid` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ipreorder_status` */

CREATE TABLE `vtiger_ipreorder_status` (
  `ipreorder_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `ipreorder_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ipreorder_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_istdn_status` */

CREATE TABLE `vtiger_istdn_status` (
  `istdn_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `istdn_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`istdn_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_istn_status` */

CREATE TABLE `vtiger_istn_status` (
  `istn_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `istn_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`istn_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_istn_type` */

CREATE TABLE `vtiger_istn_type` (
  `istn_typeid` int(10) NOT NULL AUTO_INCREMENT,
  `istn_type` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`istn_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_istrn_status` */

CREATE TABLE `vtiger_istrn_status` (
  `istrn_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `istrn_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`istrn_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_knowledgebase_status` */

CREATE TABLE `vtiger_knowledgebase_status` (
  `knowledgebase_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `knowledgebase_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`knowledgebase_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_knowledgebase_view` */

CREATE TABLE `vtiger_knowledgebase_view` (
  `knowledgebase_viewid` int(10) NOT NULL AUTO_INCREMENT,
  `knowledgebase_view` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`knowledgebase_viewid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_language` */

CREATE TABLE `vtiger_language` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `prefix` varchar(10) NOT NULL,
  `lastupdated` datetime DEFAULT NULL,
  `sequence` smallint(6) unsigned DEFAULT NULL,
  `isdefault` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `progress` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `prefix` (`prefix`),
  KEY `isdefault` (`isdefault`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_layout` */

CREATE TABLE `vtiger_layout` (
  `id` int(10) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `label` varchar(30) DEFAULT NULL,
  `lastupdated` datetime DEFAULT NULL,
  `isdefault` tinyint(1) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lead_view` */

CREATE TABLE `vtiger_lead_view` (
  `lead_viewid` int(10) NOT NULL AUTO_INCREMENT,
  `lead_view` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`lead_viewid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_leadaddress` */

CREATE TABLE `vtiger_leadaddress` (
  `leadaddressid` int(10) NOT NULL DEFAULT 0,
  `phone` varchar(50) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `fax` varchar(50) DEFAULT NULL,
  `addresslevel1a` varchar(255) DEFAULT NULL,
  `addresslevel2a` varchar(255) DEFAULT NULL,
  `addresslevel3a` varchar(255) DEFAULT NULL,
  `addresslevel4a` varchar(255) DEFAULT NULL,
  `addresslevel5a` varchar(255) DEFAULT NULL,
  `addresslevel6a` varchar(255) DEFAULT NULL,
  `addresslevel7a` varchar(255) DEFAULT NULL,
  `addresslevel8a` varchar(255) DEFAULT NULL,
  `buildingnumbera` varchar(100) DEFAULT NULL,
  `localnumbera` varchar(100) DEFAULT NULL,
  `poboxa` varchar(50) DEFAULT NULL,
  `phone_extra` varchar(100) DEFAULT NULL,
  `mobile_extra` varchar(100) DEFAULT NULL,
  `fax_extra` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`leadaddressid`),
  CONSTRAINT `fk_1_vtiger_leadaddress` FOREIGN KEY (`leadaddressid`) REFERENCES `vtiger_leaddetails` (`leadid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_leaddetails` */

CREATE TABLE `vtiger_leaddetails` (
  `leadid` int(10) NOT NULL,
  `lead_no` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `interest` varchar(50) DEFAULT NULL,
  `firstname` varchar(40) DEFAULT NULL,
  `salutation` varchar(200) DEFAULT NULL,
  `lastname` varchar(80) DEFAULT NULL,
  `company` varchar(100) NOT NULL,
  `annualrevenue` decimal(25,8) DEFAULT NULL,
  `industry` varchar(200) DEFAULT NULL,
  `campaign` varchar(30) DEFAULT NULL,
  `leadstatus` varchar(50) DEFAULT NULL,
  `leadsource` varchar(200) DEFAULT NULL,
  `converted` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `licencekeystatus` varchar(50) DEFAULT NULL,
  `space` varchar(250) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `priority` varchar(50) DEFAULT NULL,
  `demorequest` varchar(50) DEFAULT NULL,
  `partnercontact` varchar(50) DEFAULT NULL,
  `productversion` varchar(20) DEFAULT NULL,
  `product` varchar(50) DEFAULT NULL,
  `maildate` date DEFAULT NULL,
  `nextstepdate` date DEFAULT NULL,
  `fundingsituation` varchar(50) DEFAULT NULL,
  `purpose` varchar(50) DEFAULT NULL,
  `evaluationstatus` varchar(50) DEFAULT NULL,
  `transferdate` date DEFAULT NULL,
  `revenuetype` varchar(50) DEFAULT NULL,
  `noofemployees` int(50) DEFAULT NULL,
  `secondaryemail` varchar(100) DEFAULT NULL,
  `assignleadchk` int(1) DEFAULT 0,
  `noapprovalcalls` smallint(1) DEFAULT NULL,
  `noapprovalemails` smallint(1) DEFAULT NULL,
  `vat_id` varchar(30) DEFAULT NULL,
  `registration_number_1` varchar(30) DEFAULT NULL,
  `registration_number_2` varchar(30) DEFAULT NULL,
  `verification` text DEFAULT NULL,
  `subindustry` varchar(255) DEFAULT '',
  `atenttion` text DEFAULT NULL,
  `leads_relation` varchar(255) DEFAULT NULL,
  `legal_form` varchar(255) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `active` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`leadid`),
  KEY `leaddetails_converted_leadstatus_idx` (`converted`,`leadstatus`),
  KEY `email_idx` (`email`),
  KEY `lastname` (`lastname`),
  KEY `converted` (`converted`),
  CONSTRAINT `fk_1_vtiger_leaddetails` FOREIGN KEY (`leadid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_leads_relation` */

CREATE TABLE `vtiger_leads_relation` (
  `leads_relationid` int(10) NOT NULL AUTO_INCREMENT,
  `leads_relation` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`leads_relationid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_leadscf` */

CREATE TABLE `vtiger_leadscf` (
  `leadid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`leadid`),
  CONSTRAINT `fk_1_vtiger_leadscf` FOREIGN KEY (`leadid`) REFERENCES `vtiger_leaddetails` (`leadid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_leadsource` */

CREATE TABLE `vtiger_leadsource` (
  `leadsourceid` int(10) NOT NULL AUTO_INCREMENT,
  `leadsource` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`leadsourceid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_leadstage` */

CREATE TABLE `vtiger_leadstage` (
  `leadstageid` int(10) NOT NULL AUTO_INCREMENT,
  `stage` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`leadstageid`),
  UNIQUE KEY `leadstage_stage_idx` (`stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_leadstatus` */

CREATE TABLE `vtiger_leadstatus` (
  `leadstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `leadstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  `color` varchar(25) DEFAULT '#E6FAD8',
  PRIMARY KEY (`leadstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_leadsubdetails` */

CREATE TABLE `vtiger_leadsubdetails` (
  `leadsubscriptionid` int(10) NOT NULL DEFAULT 0,
  `website` varchar(255) DEFAULT NULL,
  `callornot` int(1) DEFAULT 0,
  `readornot` int(1) DEFAULT 0,
  `empct` int(10) DEFAULT 0,
  PRIMARY KEY (`leadsubscriptionid`),
  CONSTRAINT `fk_1_vtiger_leadsubdetails` FOREIGN KEY (`leadsubscriptionid`) REFERENCES `vtiger_leaddetails` (`leadid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_legal_form` */

CREATE TABLE `vtiger_legal_form` (
  `legal_formid` int(10) NOT NULL AUTO_INCREMENT,
  `legal_form` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`legal_formid`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lettersin` */

CREATE TABLE `vtiger_lettersin` (
  `lettersinid` int(10) NOT NULL DEFAULT 0,
  `number` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `relatedid` int(10) DEFAULT NULL,
  `person_receiving` int(10) DEFAULT NULL,
  `parentid` int(10) DEFAULT NULL,
  `date_adoption` date DEFAULT NULL,
  `lin_type_ship` varchar(255) DEFAULT '',
  `lin_type_doc` text DEFAULT NULL,
  `lin_status` varchar(255) DEFAULT '',
  `deadline_reply` date DEFAULT NULL,
  `cocument_no` varchar(100) DEFAULT '',
  `no_internal` varchar(100) DEFAULT '',
  `lin_dimensions` varchar(255) DEFAULT '',
  `custom_sender` varchar(255) DEFAULT NULL,
  `lin_type` varchar(255) DEFAULT NULL,
  `cash_amount_on_delivery` decimal(25,8) DEFAULT NULL,
  `date_of_receipt` date DEFAULT NULL,
  `outgoing_correspondence` int(10) DEFAULT NULL,
  PRIMARY KEY (`lettersinid`),
  KEY `vtiger_lettersin_outgoing_correspondence_idx` (`outgoing_correspondence`),
  CONSTRAINT `fk_1_vtiger_lettersin` FOREIGN KEY (`lettersinid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lettersincf` */

CREATE TABLE `vtiger_lettersincf` (
  `lettersinid` int(10) NOT NULL,
  `internal_notes` text DEFAULT NULL,
  `public_notes` text DEFAULT NULL,
  PRIMARY KEY (`lettersinid`),
  CONSTRAINT `fk_1_vtiger_lettersincf` FOREIGN KEY (`lettersinid`) REFERENCES `vtiger_lettersin` (`lettersinid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lettersout` */

CREATE TABLE `vtiger_lettersout` (
  `lettersoutid` int(10) NOT NULL DEFAULT 0,
  `number` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `relatedid` int(10) DEFAULT NULL,
  `person_receiving` int(10) DEFAULT NULL,
  `parentid` int(10) DEFAULT NULL,
  `date_adoption` date DEFAULT NULL,
  `lout_type_ship` varchar(255) DEFAULT '',
  `lout_type_doc` text DEFAULT NULL,
  `lout_status` varchar(255) DEFAULT '',
  `deadline_reply` date DEFAULT NULL,
  `cocument_no` varchar(100) DEFAULT '',
  `no_internal` varchar(100) DEFAULT '',
  `lout_dimensions` varchar(255) DEFAULT '',
  `incoming_correspondence` int(10) DEFAULT NULL,
  PRIMARY KEY (`lettersoutid`),
  KEY `vtiger_lettersout_incoming_correspondence_idx` (`incoming_correspondence`),
  CONSTRAINT `fk_1_vtiger_lettersout` FOREIGN KEY (`lettersoutid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lettersoutcf` */

CREATE TABLE `vtiger_lettersoutcf` (
  `lettersoutid` int(10) NOT NULL,
  PRIMARY KEY (`lettersoutid`),
  CONSTRAINT `fk_1_vtiger_lettersoutcf` FOREIGN KEY (`lettersoutid`) REFERENCES `vtiger_lettersout` (`lettersoutid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lin_dimensions` */

CREATE TABLE `vtiger_lin_dimensions` (
  `lin_dimensionsid` int(10) NOT NULL AUTO_INCREMENT,
  `lin_dimensions` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`lin_dimensionsid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lin_status` */

CREATE TABLE `vtiger_lin_status` (
  `lin_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `lin_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`lin_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lin_type` */

CREATE TABLE `vtiger_lin_type` (
  `lin_typeid` int(11) NOT NULL AUTO_INCREMENT,
  `lin_type` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`lin_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lin_type_doc` */

CREATE TABLE `vtiger_lin_type_doc` (
  `lin_type_docid` int(10) NOT NULL AUTO_INCREMENT,
  `lin_type_doc` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`lin_type_docid`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lin_type_ship` */

CREATE TABLE `vtiger_lin_type_ship` (
  `lin_type_shipid` int(10) NOT NULL AUTO_INCREMENT,
  `lin_type_ship` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`lin_type_shipid`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_links` */

CREATE TABLE `vtiger_links` (
  `linkid` int(10) NOT NULL AUTO_INCREMENT,
  `tabid` smallint(5) DEFAULT NULL,
  `linktype` varchar(50) DEFAULT NULL,
  `linklabel` varchar(50) DEFAULT NULL,
  `linkurl` varchar(255) DEFAULT NULL,
  `linkicon` varchar(100) DEFAULT NULL,
  `sequence` tinyint(3) unsigned DEFAULT NULL,
  `handler_path` varchar(128) DEFAULT NULL,
  `handler_class` varchar(50) DEFAULT NULL,
  `handler` varchar(50) DEFAULT NULL,
  `params` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`linkid`),
  KEY `link_tabidtype_idx` (`tabid`,`linktype`),
  KEY `linklabel` (`linklabel`),
  KEY `linkid` (`linkid`,`tabid`,`linktype`,`linklabel`),
  KEY `linktype` (`linktype`)
) ENGINE=InnoDB AUTO_INCREMENT=364 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_locationregister_status` */

CREATE TABLE `vtiger_locationregister_status` (
  `locationregister_statusid` int(11) NOT NULL AUTO_INCREMENT,
  `locationregister_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`locationregister_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_login_method` */

CREATE TABLE `vtiger_login_method` (
  `login_methodid` int(11) NOT NULL AUTO_INCREMENT,
  `login_method` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`login_methodid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_loginhistory` */

CREATE TABLE `vtiger_loginhistory` (
  `login_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(64) DEFAULT NULL,
  `user_ip` varchar(100) DEFAULT NULL,
  `logout_time` timestamp NULL DEFAULT NULL,
  `login_time` timestamp NULL DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `browser` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`login_id`),
  KEY `user_name` (`user_name`),
  KEY `user_ip` (`user_ip`,`login_time`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lout_dimensions` */

CREATE TABLE `vtiger_lout_dimensions` (
  `lout_dimensionsid` int(10) NOT NULL AUTO_INCREMENT,
  `lout_dimensions` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`lout_dimensionsid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lout_status` */

CREATE TABLE `vtiger_lout_status` (
  `lout_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `lout_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`lout_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lout_type_doc` */

CREATE TABLE `vtiger_lout_type_doc` (
  `lout_type_docid` int(10) NOT NULL AUTO_INCREMENT,
  `lout_type_doc` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`lout_type_docid`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_lout_type_ship` */

CREATE TABLE `vtiger_lout_type_ship` (
  `lout_type_shipid` int(10) NOT NULL AUTO_INCREMENT,
  `lout_type_ship` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`lout_type_shipid`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_mail_accounts` */

CREATE TABLE `vtiger_mail_accounts` (
  `account_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `display_name` varchar(50) DEFAULT NULL,
  `mail_id` varchar(50) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `mail_protocol` varchar(20) DEFAULT NULL,
  `mail_username` varchar(50) NOT NULL,
  `mail_password` varchar(250) NOT NULL,
  `mail_servername` varchar(50) DEFAULT NULL,
  `box_refresh` int(10) DEFAULT NULL,
  `mails_per_page` int(10) DEFAULT NULL,
  `ssltype` varchar(50) DEFAULT NULL,
  `sslmeth` varchar(50) DEFAULT NULL,
  `int_mailer` int(1) DEFAULT 0,
  `status` varchar(10) DEFAULT NULL,
  `set_default` int(2) DEFAULT NULL,
  `sent_folder` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_manufacturer` */

CREATE TABLE `vtiger_manufacturer` (
  `manufacturerid` int(10) NOT NULL AUTO_INCREMENT,
  `manufacturer` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`manufacturerid`),
  UNIQUE KEY `manufacturer_manufacturer_idx` (`manufacturer`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_modcomments` */

CREATE TABLE `vtiger_modcomments` (
  `modcommentsid` int(10) NOT NULL,
  `commentcontent` text DEFAULT NULL,
  `related_to` int(10) DEFAULT NULL,
  `parent_comments` int(10) DEFAULT NULL,
  `customer` int(11) DEFAULT NULL,
  `userid` int(10) DEFAULT NULL,
  `reasontoedit` varchar(100) DEFAULT NULL,
  `parents` text DEFAULT NULL,
  PRIMARY KEY (`modcommentsid`),
  KEY `relatedto_idx` (`related_to`),
  KEY `modcommentsid` (`modcommentsid`),
  KEY `parent_comments` (`parent_comments`),
  KEY `userid` (`userid`),
  KEY `related_to` (`related_to`,`parent_comments`),
  CONSTRAINT `vtiger_modcomments_ibfk_1` FOREIGN KEY (`related_to`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_modcommentscf` */

CREATE TABLE `vtiger_modcommentscf` (
  `modcommentsid` int(10) NOT NULL,
  PRIMARY KEY (`modcommentsid`),
  CONSTRAINT `vtiger_modcommentscf_ibfk_1` FOREIGN KEY (`modcommentsid`) REFERENCES `vtiger_modcomments` (`modcommentsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_modentity_num` */

CREATE TABLE `vtiger_modentity_num` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `tabid` smallint(5) unsigned NOT NULL,
  `prefix` varchar(50) NOT NULL DEFAULT '',
  `leading_zeros` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `postfix` varchar(50) NOT NULL DEFAULT '',
  `start_id` int(10) unsigned NOT NULL,
  `cur_id` int(10) unsigned NOT NULL,
  `reset_sequence` char(1) DEFAULT NULL,
  `cur_sequence` varchar(10) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `semodule` (`cur_id`),
  KEY `prefix` (`prefix`,`postfix`,`cur_id`),
  KEY `tabid` (`tabid`),
  KEY `tabid_2` (`tabid`,`cur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_modtracker_basic` */

CREATE TABLE `vtiger_modtracker_basic` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `crmid` int(10) DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `whodid` int(10) DEFAULT NULL,
  `changedon` datetime DEFAULT NULL,
  `status` int(1) DEFAULT 0,
  `last_reviewed_users` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `crmidx` (`crmid`),
  KEY `idx` (`id`),
  KEY `id` (`id`,`module`,`changedon`),
  KEY `crmid` (`crmid`,`changedon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_modtracker_detail` */

CREATE TABLE `vtiger_modtracker_detail` (
  `id` int(10) DEFAULT NULL,
  `fieldname` varchar(100) DEFAULT NULL,
  `prevalue` text DEFAULT NULL,
  `postvalue` text DEFAULT NULL,
  KEY `idx` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_modtracker_relations` */

CREATE TABLE `vtiger_modtracker_relations` (
  `id` int(10) NOT NULL,
  `targetmodule` varchar(100) NOT NULL,
  `targetid` int(10) NOT NULL,
  `changedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_modtracker_tabs` */

CREATE TABLE `vtiger_modtracker_tabs` (
  `tabid` smallint(5) unsigned NOT NULL,
  `visible` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`tabid`),
  KEY `tabid` (`tabid`,`visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_module_dashboard` */

CREATE TABLE `vtiger_module_dashboard` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `blockid` int(10) NOT NULL,
  `linkid` int(10) DEFAULT NULL,
  `filterid` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `limit` tinyint(2) DEFAULT NULL,
  `isdefault` tinyint(1) NOT NULL DEFAULT 0,
  `owners` varchar(100) DEFAULT NULL,
  `cache` tinyint(1) DEFAULT 0,
  `date` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vtiger_module_dashboard_ibfk_1` (`blockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_module_dashboard_blocks` */

CREATE TABLE `vtiger_module_dashboard_blocks` (
  `id` int(100) unsigned NOT NULL AUTO_INCREMENT,
  `authorized` varchar(10) NOT NULL,
  `tabid` smallint(5) unsigned NOT NULL,
  `dashboard_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `authorized` (`authorized`,`tabid`),
  KEY `tabid` (`tabid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_module_dashboard_widgets` */

CREATE TABLE `vtiger_module_dashboard_widgets` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `linkid` int(10) NOT NULL,
  `userid` int(10) DEFAULT NULL,
  `templateid` int(10) NOT NULL,
  `filterid` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `size` text DEFAULT NULL,
  `limit` tinyint(2) DEFAULT NULL,
  `position` text DEFAULT NULL,
  `isdefault` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 0,
  `owners` varchar(100) DEFAULT NULL,
  `module` int(10) DEFAULT 0,
  `cache` tinyint(1) DEFAULT 0,
  `date` varchar(20) DEFAULT NULL,
  `dashboardid` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vtiger_module_dashboard_widgets_ibfk_1` (`templateid`),
  KEY `userid` (`userid`,`active`,`module`),
  KEY `vtiger_module_dashboard_widgets_linkid_idx` (`linkid`),
  KEY `vtiger_module_dashboard_widgets_dashboardid_idx` (`dashboardid`),
  KEY `vtiger_module_dashboard_widgets_module_idx` (`module`),
  CONSTRAINT `vtiger_module_dashboard_widgets_ibfk_1` FOREIGN KEY (`templateid`) REFERENCES `vtiger_module_dashboard` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_mulcomp_status` */

CREATE TABLE `vtiger_mulcomp_status` (
  `mulcomp_statusid` int(11) NOT NULL AUTO_INCREMENT,
  `mulcomp_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`mulcomp_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_no_of_currency_decimals` */

CREATE TABLE `vtiger_no_of_currency_decimals` (
  `no_of_currency_decimalsid` int(10) NOT NULL AUTO_INCREMENT,
  `no_of_currency_decimals` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`no_of_currency_decimalsid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_notebook_contents` */

CREATE TABLE `vtiger_notebook_contents` (
  `userid` int(10) NOT NULL,
  `notebookid` int(10) NOT NULL,
  `contents` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_notes` */

CREATE TABLE `vtiger_notes` (
  `notesid` int(10) NOT NULL DEFAULT 0,
  `note_no` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `filename` varchar(200) DEFAULT NULL,
  `notecontent` text DEFAULT NULL,
  `folderid` varchar(255) DEFAULT NULL,
  `filetype` varchar(100) DEFAULT NULL,
  `filelocationtype` varchar(5) DEFAULT NULL,
  `filedownloadcount` int(10) DEFAULT NULL,
  `filestatus` smallint(1) DEFAULT NULL,
  `filesize` int(10) NOT NULL DEFAULT 0,
  `fileversion` varchar(50) DEFAULT NULL,
  `ossdc_status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`notesid`),
  KEY `notes_title_idx` (`title`),
  KEY `notes_notesid_idx` (`notesid`),
  CONSTRAINT `fk_1_vtiger_notes` FOREIGN KEY (`notesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_notescf` */

CREATE TABLE `vtiger_notescf` (
  `notesid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`notesid`),
  CONSTRAINT `vtiger_notescf_ibfk_1` FOREIGN KEY (`notesid`) REFERENCES `vtiger_notes` (`notesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_notification_status` */

CREATE TABLE `vtiger_notification_status` (
  `notification_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `notification_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`notification_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_notification_type` */

CREATE TABLE `vtiger_notification_type` (
  `notification_typeid` int(10) NOT NULL AUTO_INCREMENT,
  `notification_type` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`notification_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_opportunitystage` */

CREATE TABLE `vtiger_opportunitystage` (
  `potstageid` int(10) NOT NULL AUTO_INCREMENT,
  `stage` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  `probability` decimal(3,2) DEFAULT 0.00,
  PRIMARY KEY (`potstageid`),
  UNIQUE KEY `opportunitystage_stage_idx` (`stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_oproductstatus` */

CREATE TABLE `vtiger_oproductstatus` (
  `oproductstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `oproductstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`oproductstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_org_share_action2tab` */

CREATE TABLE `vtiger_org_share_action2tab` (
  `share_action_id` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  PRIMARY KEY (`share_action_id`,`tabid`),
  KEY `fk_2_vtiger_org_share_action2tab` (`tabid`),
  CONSTRAINT `fk_2_vtiger_org_share_action2tab` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_org_share_action_mapping` */

CREATE TABLE `vtiger_org_share_action_mapping` (
  `share_action_id` int(10) NOT NULL,
  `share_action_name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`share_action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossdc_status` */

CREATE TABLE `vtiger_ossdc_status` (
  `ossdc_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `ossdc_status` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`ossdc_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossemployees` */

CREATE TABLE `vtiger_ossemployees` (
  `ossemployeesid` int(10) NOT NULL DEFAULT 0,
  `ossemployees_no` varchar(255) DEFAULT NULL,
  `parentid` int(10) DEFAULT 0,
  `employee_status` varchar(200) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `last_name` varchar(200) DEFAULT NULL,
  `pesel` varchar(20) DEFAULT NULL,
  `id_card` varchar(200) DEFAULT NULL,
  `employee_education` varchar(200) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `business_phone` varchar(20) DEFAULT NULL,
  `private_phone` varchar(25) DEFAULT NULL,
  `business_mail` varchar(100) DEFAULT NULL,
  `private_mail` varchar(100) DEFAULT NULL,
  `street` varchar(200) DEFAULT NULL,
  `code` varchar(200) DEFAULT NULL,
  `city` varchar(200) DEFAULT NULL,
  `state` varchar(200) DEFAULT NULL,
  `country` varchar(200) DEFAULT NULL,
  `ship_street` varchar(200) DEFAULT NULL,
  `ship_code` varchar(200) DEFAULT NULL,
  `ship_city` varchar(200) DEFAULT NULL,
  `ship_state` varchar(200) DEFAULT NULL,
  `ship_country` varchar(200) DEFAULT NULL,
  `dav_status` tinyint(1) DEFAULT 1,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `secondary_phone` varchar(25) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `rbh` decimal(25,8) DEFAULT NULL,
  `business_phone_extra` varchar(100) DEFAULT NULL,
  `private_phone_extra` varchar(100) DEFAULT NULL,
  `secondary_phone_extra` varchar(100) DEFAULT NULL,
  `multicompanyid` int(10) DEFAULT NULL,
  PRIMARY KEY (`ossemployeesid`),
  KEY `ossemployees_org_struct_idx` (`multicompanyid`),
  CONSTRAINT `fk_1_vtiger_ossemployees` FOREIGN KEY (`ossemployeesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossemployeescf` */

CREATE TABLE `vtiger_ossemployeescf` (
  `ossemployeesid` int(10) NOT NULL,
  PRIMARY KEY (`ossemployeesid`),
  CONSTRAINT `fk_1_vtiger_ossemployeescf` FOREIGN KEY (`ossemployeesid`) REFERENCES `vtiger_ossemployees` (`ossemployeesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_osservicesstatus` */

CREATE TABLE `vtiger_osservicesstatus` (
  `osservicesstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `osservicesstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`osservicesstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossmails_logs` */

CREATE TABLE `vtiger_ossmails_logs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `status` tinyint(3) DEFAULT NULL,
  `user` varchar(100) DEFAULT NULL,
  `count` int(10) DEFAULT NULL,
  `stop_user` varchar(100) DEFAULT NULL,
  `info` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossmailscanner_config` */

CREATE TABLE `vtiger_ossmailscanner_config` (
  `conf_type` varchar(100) NOT NULL,
  `parameter` varchar(100) DEFAULT NULL,
  `value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossmailscanner_folders_uid` */

CREATE TABLE `vtiger_ossmailscanner_folders_uid` (
  `user_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `folder` varchar(100) DEFAULT NULL,
  `uid` int(10) DEFAULT 0,
  KEY `user_id` (`user_id`),
  KEY `folder` (`folder`),
  CONSTRAINT `vtiger_ossmailscanner_folders_uid_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossmailscanner_log_cron` */

CREATE TABLE `vtiger_ossmailscanner_log_cron` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `created_time` timestamp NULL DEFAULT NULL,
  `laststart` int(10) unsigned DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossmailview` */

CREATE TABLE `vtiger_ossmailview` (
  `ossmailviewid` int(10) NOT NULL,
  `ossmailview_no` varchar(50) DEFAULT NULL,
  `from_email` text DEFAULT NULL,
  `to_email` text DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `content` mediumtext DEFAULT NULL,
  `cc_email` text DEFAULT NULL,
  `bcc_email` text DEFAULT NULL,
  `id` int(10) unsigned NOT NULL,
  `mbox` varchar(100) DEFAULT NULL,
  `uid` varchar(150) DEFAULT NULL,
  `cid` char(40) DEFAULT NULL,
  `rc_user` varchar(3) DEFAULT NULL,
  `reply_to_email` text DEFAULT NULL,
  `ossmailview_sendtype` varchar(30) DEFAULT NULL,
  `attachments_exist` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `type` tinyint(1) unsigned DEFAULT NULL,
  `from_id` text NOT NULL,
  `to_id` text NOT NULL,
  `orginal_mail` mediumtext DEFAULT NULL,
  `verify` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`ossmailviewid`),
  KEY `id` (`id`),
  KEY `verify` (`verify`),
  KEY `message_id` (`uid`,`rc_user`),
  KEY `mbox` (`mbox`),
  KEY `ossmailview_cid_idx` (`cid`),
  CONSTRAINT `fk_1_vtiger_ossmailview` FOREIGN KEY (`ossmailviewid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossmailview_files` */

CREATE TABLE `vtiger_ossmailview_files` (
  `ossmailviewid` int(10) NOT NULL,
  `documentsid` int(10) NOT NULL,
  `attachmentsid` int(10) NOT NULL,
  KEY `fk_1_vtiger_ossmailview_files` (`ossmailviewid`),
  KEY `documentsid` (`documentsid`),
  CONSTRAINT `fk_1_vtiger_ossmailview_files` FOREIGN KEY (`ossmailviewid`) REFERENCES `vtiger_ossmailview` (`ossmailviewid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossmailview_relation` */

CREATE TABLE `vtiger_ossmailview_relation` (
  `ossmailviewid` int(10) NOT NULL,
  `crmid` int(10) NOT NULL,
  `date` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  UNIQUE KEY `ossmailviewid_2` (`ossmailviewid`,`crmid`),
  KEY `ossmailviewid` (`ossmailviewid`),
  KEY `crmid` (`crmid`,`deleted`),
  CONSTRAINT `vtiger_ossmailview_relation_ibfk_1` FOREIGN KEY (`ossmailviewid`) REFERENCES `vtiger_ossmailview` (`ossmailviewid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossmailview_sendtype` */

CREATE TABLE `vtiger_ossmailview_sendtype` (
  `ossmailview_sendtypeid` int(10) NOT NULL AUTO_INCREMENT,
  `ossmailview_sendtype` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`ossmailview_sendtypeid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossmailviewcf` */

CREATE TABLE `vtiger_ossmailviewcf` (
  `ossmailviewid` int(10) NOT NULL,
  PRIMARY KEY (`ossmailviewid`),
  CONSTRAINT `fk_1_vtiger_ossmailviewcf` FOREIGN KEY (`ossmailviewid`) REFERENCES `vtiger_ossmailview` (`ossmailviewid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossoutsourcedservices` */

CREATE TABLE `vtiger_ossoutsourcedservices` (
  `ossoutsourcedservicesid` int(10) NOT NULL DEFAULT 0,
  `ossoutsourcedservices_no` varchar(255) DEFAULT NULL,
  `productname` varchar(100) DEFAULT '',
  `osservicesstatus` varchar(50) DEFAULT NULL,
  `pscategory` varchar(255) DEFAULT NULL,
  `datesold` date DEFAULT NULL,
  `dateinservice` date DEFAULT NULL,
  `wherebought` varchar(100) DEFAULT '',
  `parent_id` int(10) DEFAULT NULL,
  `ssalesprocessesid` int(10) DEFAULT NULL,
  PRIMARY KEY (`ossoutsourcedservicesid`),
  KEY `parent_id` (`parent_id`),
  KEY `ssalesprocessesid` (`ssalesprocessesid`),
  CONSTRAINT `fk_1_vtiger_ossoutsourcedservices` FOREIGN KEY (`ossoutsourcedservicesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ossoutsourcedservicescf` */

CREATE TABLE `vtiger_ossoutsourcedservicescf` (
  `ossoutsourcedservicesid` int(10) NOT NULL,
  PRIMARY KEY (`ossoutsourcedservicesid`),
  CONSTRAINT `fk_1_vtiger_ossoutsourcedservicescf` FOREIGN KEY (`ossoutsourcedservicesid`) REFERENCES `vtiger_ossoutsourcedservices` (`ossoutsourcedservicesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_osspasswords` */

CREATE TABLE `vtiger_osspasswords` (
  `osspasswordsid` int(10) NOT NULL,
  `osspassword_no` varchar(100) NOT NULL,
  `passwordname` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varbinary(200) NOT NULL,
  `link_adres` varchar(255) DEFAULT NULL,
  `linkto` int(10) DEFAULT NULL,
  `linkextend` int(10) DEFAULT NULL,
  PRIMARY KEY (`osspasswordsid`),
  KEY `linkto` (`linkto`),
  KEY `linkextend` (`linkextend`),
  CONSTRAINT `fk_1_vtiger_osspasswords` FOREIGN KEY (`osspasswordsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_osspasswordscf` */

CREATE TABLE `vtiger_osspasswordscf` (
  `osspasswordsid` int(10) NOT NULL,
  PRIMARY KEY (`osspasswordsid`),
  CONSTRAINT `fk_1_vtiger_osspasswordscf` FOREIGN KEY (`osspasswordsid`) REFERENCES `vtiger_osspasswords` (`osspasswordsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_osssoldservices` */

CREATE TABLE `vtiger_osssoldservices` (
  `osssoldservicesid` int(10) NOT NULL DEFAULT 0,
  `osssoldservices_no` varchar(255) DEFAULT NULL,
  `productname` varchar(255) DEFAULT '',
  `ssservicesstatus` varchar(255) DEFAULT NULL,
  `pscategory` varchar(255) DEFAULT '',
  `datesold` date DEFAULT NULL,
  `dateinservice` date DEFAULT NULL,
  `invoice` varchar(255) DEFAULT '',
  `parent_id` int(10) DEFAULT NULL,
  `serviceid` int(10) DEFAULT NULL,
  `ordertime` decimal(10,2) DEFAULT NULL,
  `ssalesprocessesid` int(10) DEFAULT NULL,
  `osssoldservices_renew` varchar(255) DEFAULT NULL,
  `renewalinvoice` int(10) DEFAULT NULL,
  `contactid` int(19) DEFAULT NULL,
  PRIMARY KEY (`osssoldservicesid`),
  KEY `parent_id` (`parent_id`),
  KEY `serviceid` (`serviceid`),
  KEY `ssalesprocessesid` (`ssalesprocessesid`),
  KEY `renewalinvoice` (`renewalinvoice`),
  KEY `contactid_idx` (`contactid`),
  CONSTRAINT `fk_1_vtiger_osssoldservices` FOREIGN KEY (`osssoldservicesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_osssoldservices_renew` */

CREATE TABLE `vtiger_osssoldservices_renew` (
  `osssoldservices_renewid` int(10) NOT NULL AUTO_INCREMENT,
  `osssoldservices_renew` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`osssoldservices_renewid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_osssoldservicescf` */

CREATE TABLE `vtiger_osssoldservicescf` (
  `osssoldservicesid` int(10) NOT NULL,
  PRIMARY KEY (`osssoldservicesid`),
  CONSTRAINT `fk_1_vtiger_osssoldservicescf` FOREIGN KEY (`osssoldservicesid`) REFERENCES `vtiger_osssoldservices` (`osssoldservicesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_osstimecontrol` */

CREATE TABLE `vtiger_osstimecontrol` (
  `osstimecontrolid` int(10) NOT NULL DEFAULT 0,
  `name` varchar(128) DEFAULT NULL,
  `osstimecontrol_no` varchar(255) DEFAULT NULL,
  `osstimecontrol_status` varchar(128) DEFAULT NULL,
  `date_start` date NOT NULL,
  `time_start` varchar(50) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `time_end` varchar(50) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `deleted` int(1) DEFAULT 0,
  `timecontrol_type` varchar(255) DEFAULT NULL,
  `process` int(10) DEFAULT NULL,
  `link` int(10) DEFAULT NULL,
  `subprocess` int(10) DEFAULT NULL,
  `linkextend` int(10) DEFAULT NULL,
  `subprocess_sl` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`osstimecontrolid`),
  KEY `on_update_cascade` (`deleted`),
  KEY `osstimecontrol_status_9` (`osstimecontrol_status`,`deleted`),
  KEY `osstimecontrol_status_6` (`osstimecontrol_status`),
  KEY `subprocess` (`subprocess`),
  KEY `link` (`link`),
  KEY `process` (`process`),
  KEY `linkextend` (`linkextend`),
  CONSTRAINT `vtiger_osstimecontrol` FOREIGN KEY (`osstimecontrolid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_osstimecontrol_status` */

CREATE TABLE `vtiger_osstimecontrol_status` (
  `osstimecontrol_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `osstimecontrol_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`osstimecontrol_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_osstimecontrolcf` */

CREATE TABLE `vtiger_osstimecontrolcf` (
  `osstimecontrolid` int(10) NOT NULL,
  PRIMARY KEY (`osstimecontrolid`),
  CONSTRAINT `vtiger_osstimecontrolcf` FOREIGN KEY (`osstimecontrolid`) REFERENCES `vtiger_osstimecontrol` (`osstimecontrolid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_outsourcedproducts` */

CREATE TABLE `vtiger_outsourcedproducts` (
  `outsourcedproductsid` int(10) NOT NULL DEFAULT 0,
  `asset_no` varchar(32) DEFAULT NULL,
  `productname` varchar(255) DEFAULT NULL,
  `datesold` date DEFAULT NULL,
  `dateinservice` date DEFAULT NULL,
  `oproductstatus` varchar(255) DEFAULT NULL,
  `pscategory` varchar(255) DEFAULT '',
  `wherebought` varchar(255) DEFAULT '',
  `prodcount` int(11) DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `ssalesprocessesid` int(10) DEFAULT NULL,
  PRIMARY KEY (`outsourcedproductsid`),
  KEY `parent_id` (`parent_id`),
  KEY `ssalesprocessesid` (`ssalesprocessesid`),
  CONSTRAINT `fk_1_vtiger_outsourcedproducts` FOREIGN KEY (`outsourcedproductsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_outsourcedproductscf` */

CREATE TABLE `vtiger_outsourcedproductscf` (
  `outsourcedproductsid` int(10) NOT NULL,
  PRIMARY KEY (`outsourcedproductsid`),
  CONSTRAINT `fk_1_vtiger_outsourcedproductscf` FOREIGN KEY (`outsourcedproductsid`) REFERENCES `vtiger_outsourcedproducts` (`outsourcedproductsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_password` */

CREATE TABLE `vtiger_password` (
  `type` varchar(20) NOT NULL,
  `val` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_passwords_config` */

CREATE TABLE `vtiger_passwords_config` (
  `pass_length_min` int(3) NOT NULL,
  `pass_length_max` int(3) NOT NULL,
  `pass_allow_chars` varchar(200) NOT NULL,
  `register_changes` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_paymentsin` */

CREATE TABLE `vtiger_paymentsin` (
  `paymentsinid` int(10) NOT NULL DEFAULT 0,
  `paymentsvalue` decimal(25,3) DEFAULT NULL,
  `paymentsno` varchar(32) DEFAULT NULL,
  `paymentsname` varchar(128) DEFAULT NULL,
  `paymentstitle` text DEFAULT NULL,
  `paymentscurrency` varchar(32) DEFAULT NULL,
  `bank_account` varchar(128) DEFAULT NULL,
  `paymentsin_status` varchar(128) DEFAULT NULL,
  `relatedid` int(10) DEFAULT NULL,
  PRIMARY KEY (`paymentsinid`),
  CONSTRAINT `fk_1_vtiger_paymentsin` FOREIGN KEY (`paymentsinid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_paymentsin_status` */

CREATE TABLE `vtiger_paymentsin_status` (
  `paymentsin_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `paymentsin_status` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`paymentsin_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_paymentsincf` */

CREATE TABLE `vtiger_paymentsincf` (
  `paymentsinid` int(10) NOT NULL,
  PRIMARY KEY (`paymentsinid`),
  CONSTRAINT `fk_1_vtiger_paymentsincf` FOREIGN KEY (`paymentsinid`) REFERENCES `vtiger_paymentsin` (`paymentsinid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_paymentsout` */

CREATE TABLE `vtiger_paymentsout` (
  `paymentsoutid` int(10) NOT NULL DEFAULT 0,
  `paymentsvalue` decimal(25,3) DEFAULT NULL,
  `paymentsno` varchar(32) DEFAULT NULL,
  `paymentsname` varchar(128) DEFAULT NULL,
  `paymentstitle` varchar(128) DEFAULT NULL,
  `paymentscurrency` varchar(32) DEFAULT NULL,
  `bank_account` varchar(128) DEFAULT NULL,
  `paymentsout_status` varchar(128) DEFAULT NULL,
  `relatedid` int(10) DEFAULT NULL,
  `parentid` int(10) DEFAULT NULL,
  PRIMARY KEY (`paymentsoutid`),
  CONSTRAINT `fk_1_vtiger_paymentsout` FOREIGN KEY (`paymentsoutid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_paymentsout_status` */

CREATE TABLE `vtiger_paymentsout_status` (
  `paymentsout_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `paymentsout_status` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`paymentsout_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_paymentsoutcf` */

CREATE TABLE `vtiger_paymentsoutcf` (
  `paymentsoutid` int(10) NOT NULL,
  PRIMARY KEY (`paymentsoutid`),
  CONSTRAINT `fk_1_vtiger_paymentsoutcf` FOREIGN KEY (`paymentsoutid`) REFERENCES `vtiger_paymentsout` (`paymentsoutid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_picklist` */

CREATE TABLE `vtiger_picklist` (
  `picklistid` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`picklistid`),
  UNIQUE KEY `picklist_name_idx` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_picklist_dependency` */

CREATE TABLE `vtiger_picklist_dependency` (
  `id` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `sourcefield` varchar(255) DEFAULT NULL,
  `targetfield` varchar(255) DEFAULT NULL,
  `sourcevalue` varchar(100) DEFAULT NULL,
  `targetvalues` text DEFAULT NULL,
  `criteria` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_picklist_dependency_seq` */

CREATE TABLE `vtiger_picklist_dependency_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_picklistvalues_seq` */

CREATE TABLE `vtiger_picklistvalues_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_portal` */

CREATE TABLE `vtiger_portal` (
  `portalid` int(10) NOT NULL AUTO_INCREMENT,
  `portalname` varchar(200) NOT NULL,
  `portalurl` varchar(255) NOT NULL,
  `sequence` int(3) NOT NULL,
  `setdefault` int(3) NOT NULL DEFAULT 0,
  `createdtime` datetime DEFAULT NULL,
  PRIMARY KEY (`portalid`),
  KEY `portal_portalname_idx` (`portalname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_pricebook` */

CREATE TABLE `vtiger_pricebook` (
  `pricebookid` int(10) NOT NULL DEFAULT 0,
  `pricebook_no` varchar(100) NOT NULL,
  `bookname` varchar(100) DEFAULT NULL,
  `active` smallint(1) DEFAULT NULL,
  `currency_id` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`pricebookid`),
  CONSTRAINT `fk_1_vtiger_pricebook` FOREIGN KEY (`pricebookid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_pricebookcf` */

CREATE TABLE `vtiger_pricebookcf` (
  `pricebookid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`pricebookid`),
  CONSTRAINT `fk_1_vtiger_pricebookcf` FOREIGN KEY (`pricebookid`) REFERENCES `vtiger_pricebook` (`pricebookid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_pricebookproductrel` */

CREATE TABLE `vtiger_pricebookproductrel` (
  `pricebookid` int(10) NOT NULL,
  `productid` int(10) NOT NULL,
  `listprice` decimal(28,8) DEFAULT NULL,
  `usedcurrency` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`pricebookid`,`productid`),
  KEY `pricebookproductrel_pricebookid_idx` (`pricebookid`),
  KEY `pricebookproductrel_productid_idx` (`productid`),
  CONSTRAINT `fk_1_vtiger_pricebookproductrel` FOREIGN KEY (`pricebookid`) REFERENCES `vtiger_pricebook` (`pricebookid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_priority` */

CREATE TABLE `vtiger_priority` (
  `priorityid` int(10) NOT NULL AUTO_INCREMENT,
  `priority` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`priorityid`),
  UNIQUE KEY `priority_priority_idx` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_productcf` */

CREATE TABLE `vtiger_productcf` (
  `productid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`productid`),
  CONSTRAINT `fk_1_vtiger_productcf` FOREIGN KEY (`productid`) REFERENCES `vtiger_products` (`productid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_productcurrencyrel` */

CREATE TABLE `vtiger_productcurrencyrel` (
  `productid` int(10) NOT NULL,
  `currencyid` int(10) NOT NULL,
  `converted_price` decimal(28,8) DEFAULT NULL,
  `actual_price` decimal(28,8) DEFAULT NULL,
  KEY `productid` (`productid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_products` */

CREATE TABLE `vtiger_products` (
  `productid` int(10) NOT NULL,
  `product_no` varchar(100) NOT NULL,
  `productname` varchar(100) DEFAULT NULL,
  `productcode` varchar(40) DEFAULT NULL,
  `pscategory` varchar(200) DEFAULT NULL,
  `manufacturer` varchar(200) DEFAULT NULL,
  `qty_per_unit` decimal(11,2) DEFAULT 0.00,
  `unit_price` decimal(25,8) DEFAULT NULL,
  `weight` decimal(11,3) DEFAULT NULL,
  `pack_size` int(10) DEFAULT NULL,
  `sales_start_date` date DEFAULT NULL,
  `sales_end_date` date DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `cost_factor` int(10) DEFAULT NULL,
  `commissionrate` decimal(7,3) DEFAULT NULL,
  `commissionmethod` varchar(50) DEFAULT NULL,
  `discontinued` tinyint(1) NOT NULL DEFAULT 0,
  `usageunit` varchar(200) DEFAULT NULL,
  `reorderlevel` int(10) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `mfr_part_no` varchar(200) DEFAULT NULL,
  `vendor_part_no` varchar(200) DEFAULT NULL,
  `serialno` varchar(200) DEFAULT NULL,
  `qtyinstock` decimal(25,3) DEFAULT NULL,
  `productsheet` varchar(200) DEFAULT NULL,
  `qtyindemand` int(10) DEFAULT NULL,
  `glacct` varchar(200) DEFAULT NULL,
  `vendor_id` int(10) DEFAULT NULL,
  `imagename` text DEFAULT NULL,
  `currency_id` int(10) NOT NULL DEFAULT 1,
  `taxes` varchar(50) DEFAULT NULL,
  `ean` varchar(30) DEFAULT NULL,
  `subunit` varchar(255) DEFAULT '',
  `renewable` tinyint(1) DEFAULT 0,
  `category_multipicklist` text DEFAULT NULL,
  PRIMARY KEY (`productid`),
  CONSTRAINT `fk_1_vtiger_products` FOREIGN KEY (`productid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_profile` */

CREATE TABLE `vtiger_profile` (
  `profileid` int(10) NOT NULL AUTO_INCREMENT,
  `profilename` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `directly_related_to_role` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`profileid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_profile2field` */

CREATE TABLE `vtiger_profile2field` (
  `profileid` int(10) NOT NULL,
  `tabid` smallint(5) DEFAULT NULL,
  `fieldid` int(10) NOT NULL,
  `visible` tinyint(1) DEFAULT NULL,
  `readonly` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`profileid`,`fieldid`),
  KEY `profile2field_profileid_tabid_fieldname_idx` (`profileid`,`tabid`),
  KEY `profile2field_tabid_profileid_idx` (`tabid`,`profileid`),
  KEY `profile2field_visible_profileid_idx` (`visible`,`profileid`),
  KEY `profile2field_readonly_idx` (`readonly`),
  CONSTRAINT `vtiger_profile2field_ibfk_1` FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_profile2globalpermissions` */

CREATE TABLE `vtiger_profile2globalpermissions` (
  `profileid` int(10) NOT NULL,
  `globalactionid` smallint(5) NOT NULL,
  `globalactionpermission` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`profileid`,`globalactionid`),
  KEY `idx_profile2globalpermissions` (`profileid`,`globalactionid`),
  CONSTRAINT `fk_1_vtiger_profile2globalpermissions` FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_profile2standardpermissions` */

CREATE TABLE `vtiger_profile2standardpermissions` (
  `profileid` smallint(5) unsigned NOT NULL,
  `tabid` smallint(5) unsigned NOT NULL,
  `operation` smallint(5) unsigned NOT NULL,
  `permissions` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`profileid`,`tabid`,`operation`),
  KEY `profile2standardpermissions_profileid_tabid_operation_idx` (`profileid`,`tabid`,`operation`),
  KEY `profileid` (`profileid`,`tabid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_profile2tab` */

CREATE TABLE `vtiger_profile2tab` (
  `profileid` int(10) DEFAULT NULL,
  `tabid` smallint(5) DEFAULT NULL,
  `permissions` tinyint(1) NOT NULL DEFAULT 0,
  KEY `profile2tab_profileid_tabid_idx` (`profileid`,`tabid`),
  CONSTRAINT `vtiger_profile2tab_ibfk_1` FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_profile2utility` */

CREATE TABLE `vtiger_profile2utility` (
  `profileid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `activityid` smallint(5) NOT NULL,
  `permission` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`profileid`,`tabid`,`activityid`),
  KEY `profile2utility_tabid_activityid_idx` (`tabid`,`activityid`),
  KEY `profile2utility_profileid` (`profileid`),
  CONSTRAINT `vtiger_profile2utility_ibfk_1` FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_progress` */

CREATE TABLE `vtiger_progress` (
  `progressid` int(10) NOT NULL AUTO_INCREMENT,
  `progress` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`progressid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_progress_seq` */

CREATE TABLE `vtiger_progress_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_project` */

CREATE TABLE `vtiger_project` (
  `projectid` int(10) NOT NULL,
  `projectname` varchar(255) DEFAULT NULL,
  `project_no` varchar(100) DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `targetenddate` date DEFAULT NULL,
  `actualenddate` date DEFAULT NULL,
  `targetbudget` varchar(255) DEFAULT NULL,
  `projecturl` varchar(255) DEFAULT NULL,
  `projectstatus` varchar(100) DEFAULT NULL,
  `projectpriority` varchar(100) DEFAULT NULL,
  `projecttype` varchar(100) DEFAULT NULL,
  `progress` decimal(5,2) DEFAULT NULL,
  `linktoaccountscontacts` int(10) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `servicecontractsid` int(10) DEFAULT NULL,
  `ssalesprocessesid` int(10) DEFAULT NULL,
  `parentid` int(10) DEFAULT NULL,
  `estimated_work_time` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`projectid`),
  KEY `servicecontractsid` (`servicecontractsid`),
  KEY `linktoaccountscontacts` (`linktoaccountscontacts`),
  KEY `projectname` (`projectname`),
  KEY `ssalesprocessesid` (`ssalesprocessesid`),
  KEY `project_parentid_idx` (`parentid`),
  KEY `project_no` (`project_no`),
  CONSTRAINT `vtiger_project_ibfk_1` FOREIGN KEY (`projectid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projectcf` */

CREATE TABLE `vtiger_projectcf` (
  `projectid` int(10) NOT NULL,
  PRIMARY KEY (`projectid`),
  CONSTRAINT `vtiger_projectcf_ibfk_1` FOREIGN KEY (`projectid`) REFERENCES `vtiger_project` (`projectid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projectmilestone` */

CREATE TABLE `vtiger_projectmilestone` (
  `projectmilestoneid` int(10) NOT NULL,
  `projectmilestonename` varchar(255) DEFAULT NULL,
  `projectmilestone_no` varchar(100) DEFAULT NULL,
  `projectmilestonedate` varchar(255) DEFAULT NULL,
  `projectmilestone_status` varchar(255) DEFAULT '',
  `projectid` int(10) DEFAULT NULL,
  `projectmilestonetype` varchar(100) DEFAULT NULL,
  `projectmilestone_priority` varchar(255) DEFAULT NULL,
  `projectmilestone_progress` decimal(5,2) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `parentid` int(10) DEFAULT NULL,
  `estimated_work_time` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`projectmilestoneid`),
  KEY `projectid` (`projectid`),
  KEY `vtiger_projectmilestone_parentid_idx` (`parentid`),
  CONSTRAINT `vtiger_projectmilestone_ibfk_1` FOREIGN KEY (`projectmilestoneid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projectmilestone_priority` */

CREATE TABLE `vtiger_projectmilestone_priority` (
  `projectmilestone_priorityid` int(10) NOT NULL AUTO_INCREMENT,
  `projectmilestone_priority` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`projectmilestone_priorityid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projectmilestone_status` */

CREATE TABLE `vtiger_projectmilestone_status` (
  `projectmilestone_statusid` int(11) NOT NULL AUTO_INCREMENT,
  `projectmilestone_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` int(10) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  `automation` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`projectmilestone_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projectmilestonecf` */

CREATE TABLE `vtiger_projectmilestonecf` (
  `projectmilestoneid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`projectmilestoneid`),
  CONSTRAINT `vtiger_projectmilestonecf_ibfk_1` FOREIGN KEY (`projectmilestoneid`) REFERENCES `vtiger_projectmilestone` (`projectmilestoneid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projectmilestonetype` */

CREATE TABLE `vtiger_projectmilestonetype` (
  `projectmilestonetypeid` int(10) NOT NULL AUTO_INCREMENT,
  `projectmilestonetype` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`projectmilestonetypeid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projectpriority` */

CREATE TABLE `vtiger_projectpriority` (
  `projectpriorityid` int(10) NOT NULL AUTO_INCREMENT,
  `projectpriority` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`projectpriorityid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projectstatus` */

CREATE TABLE `vtiger_projectstatus` (
  `projectstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `projectstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  `color` varchar(25) DEFAULT '#E6FAD8',
  `automation` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`projectstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projecttask` */

CREATE TABLE `vtiger_projecttask` (
  `projecttaskid` int(10) NOT NULL,
  `projecttaskname` varchar(255) DEFAULT NULL,
  `projecttask_no` varchar(100) DEFAULT NULL,
  `projecttasktype` varchar(100) DEFAULT NULL,
  `projecttaskpriority` varchar(100) DEFAULT NULL,
  `projecttaskprogress` decimal(5,2) DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `projectid` int(10) DEFAULT NULL,
  `projecttasknumber` int(10) DEFAULT NULL,
  `projecttaskstatus` varchar(100) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `parentid` int(10) DEFAULT NULL,
  `projectmilestoneid` int(10) DEFAULT NULL,
  `targetenddate` date DEFAULT NULL,
  `estimated_work_time` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`projecttaskid`),
  KEY `parentid` (`parentid`),
  KEY `projectmilestoneid` (`projectmilestoneid`),
  KEY `projectid` (`projectid`),
  KEY `projecttaskname` (`projecttaskname`),
  CONSTRAINT `vtiger_projecttask_ibfk_1` FOREIGN KEY (`projecttaskid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projecttaskcf` */

CREATE TABLE `vtiger_projecttaskcf` (
  `projecttaskid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`projecttaskid`),
  CONSTRAINT `vtiger_projecttaskcf_ibfk_1` FOREIGN KEY (`projecttaskid`) REFERENCES `vtiger_projecttask` (`projecttaskid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projecttaskpriority` */

CREATE TABLE `vtiger_projecttaskpriority` (
  `projecttaskpriorityid` int(10) NOT NULL AUTO_INCREMENT,
  `projecttaskpriority` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`projecttaskpriorityid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projecttaskprogress` */

CREATE TABLE `vtiger_projecttaskprogress` (
  `projecttaskprogressid` int(10) NOT NULL AUTO_INCREMENT,
  `projecttaskprogress` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`projecttaskprogressid`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projecttaskprogress_seq` */

CREATE TABLE `vtiger_projecttaskprogress_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projecttaskstatus` */

CREATE TABLE `vtiger_projecttaskstatus` (
  `projecttaskstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `projecttaskstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  `automation` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`projecttaskstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projecttasktype` */

CREATE TABLE `vtiger_projecttasktype` (
  `projecttasktypeid` int(10) NOT NULL AUTO_INCREMENT,
  `projecttasktype` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`projecttasktypeid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_projecttype` */

CREATE TABLE `vtiger_projecttype` (
  `projecttypeid` int(10) NOT NULL AUTO_INCREMENT,
  `projecttype` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`projecttypeid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_publicholiday` */

CREATE TABLE `vtiger_publicholiday` (
  `publicholidayid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id of public holiday',
  `holidaydate` date NOT NULL COMMENT 'date of holiday',
  `holidayname` varchar(255) NOT NULL COMMENT 'name of holiday',
  `holidaytype` varchar(25) DEFAULT NULL COMMENT 'type of holiday',
  PRIMARY KEY (`publicholidayid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_realization_process` */

CREATE TABLE `vtiger_realization_process` (
  `module_id` int(10) NOT NULL,
  `status_indicate_closing` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_recurring_frequency` */

CREATE TABLE `vtiger_recurring_frequency` (
  `recurring_frequency_id` int(10) NOT NULL AUTO_INCREMENT,
  `recurring_frequency` varchar(200) DEFAULT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) DEFAULT NULL,
  PRIMARY KEY (`recurring_frequency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_rel_mod` */

CREATE TABLE `vtiger_rel_mod` (
  `rel_modid` int(10) NOT NULL AUTO_INCREMENT,
  `rel_mod` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`rel_modid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_relatedlists` */

CREATE TABLE `vtiger_relatedlists` (
  `relation_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `tabid` smallint(5) unsigned NOT NULL,
  `related_tabid` smallint(5) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `sequence` tinyint(3) unsigned NOT NULL,
  `label` varchar(50) NOT NULL,
  `presence` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `actions` varchar(50) NOT NULL DEFAULT '',
  `favorites` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `creator_detail` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `relation_comment` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `view_type` varchar(100) NOT NULL DEFAULT 'RelatedTab',
  PRIMARY KEY (`relation_id`),
  KEY `tabid` (`tabid`),
  KEY `related_tabid` (`related_tabid`),
  KEY `tabid_2` (`tabid`,`related_tabid`),
  KEY `tabid_3` (`tabid`,`related_tabid`,`label`),
  KEY `tabid_4` (`tabid`,`related_tabid`,`presence`)
) ENGINE=InnoDB AUTO_INCREMENT=606 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_relatedlists_fields` */

CREATE TABLE `vtiger_relatedlists_fields` (
  `relation_id` int(10) DEFAULT NULL,
  `fieldid` int(10) DEFAULT NULL,
  `fieldname` varchar(30) DEFAULT NULL,
  `sequence` smallint(3) DEFAULT NULL,
  KEY `relation_id` (`relation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_reminder_interval` */

CREATE TABLE `vtiger_reminder_interval` (
  `reminder_intervalid` int(10) NOT NULL AUTO_INCREMENT,
  `reminder_interval` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL,
  `presence` int(1) NOT NULL,
  PRIMARY KEY (`reminder_intervalid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_reservations` */

CREATE TABLE `vtiger_reservations` (
  `reservationsid` int(10) NOT NULL DEFAULT 0,
  `title` varchar(128) DEFAULT NULL,
  `reservations_no` varchar(255) DEFAULT NULL,
  `reservations_status` varchar(128) DEFAULT NULL,
  `date_start` date NOT NULL,
  `time_start` varchar(50) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `time_end` varchar(50) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `link` int(10) DEFAULT 0,
  `process` int(10) DEFAULT 0,
  `deleted` int(1) DEFAULT 0,
  `type` varchar(128) DEFAULT NULL,
  `subprocess` int(10) DEFAULT 0,
  `linkextend` int(10) DEFAULT NULL,
  PRIMARY KEY (`reservationsid`),
  KEY `process` (`process`),
  KEY `link` (`link`),
  KEY `subprocess` (`subprocess`),
  KEY `linkextend` (`linkextend`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `vtiger_reservations` FOREIGN KEY (`reservationsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_reservations_status` */

CREATE TABLE `vtiger_reservations_status` (
  `reservations_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `reservations_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`reservations_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_reservationscf` */

CREATE TABLE `vtiger_reservationscf` (
  `reservationsid` int(10) NOT NULL,
  PRIMARY KEY (`reservationsid`),
  CONSTRAINT `vtiger_reservationscf` FOREIGN KEY (`reservationsid`) REFERENCES `vtiger_reservations` (`reservationsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_role` */

CREATE TABLE `vtiger_role` (
  `roleid` varchar(255) NOT NULL,
  `rolename` varchar(200) DEFAULT NULL,
  `parentrole` varchar(255) DEFAULT NULL,
  `depth` smallint(5) unsigned NOT NULL DEFAULT 0,
  `company` int(10) unsigned DEFAULT 0,
  `allowassignedrecordsto` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `changeowner` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `searchunpriv` text DEFAULT NULL,
  `clendarallorecords` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `listrelatedrecord` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `previewrelatedrecord` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `editrelatedrecord` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `permissionsrelatedfield` varchar(10) NOT NULL DEFAULT '0',
  `globalsearchadv` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `assignedmultiowner` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `auto_assign` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`roleid`),
  KEY `parentrole` (`parentrole`),
  KEY `parentrole_2` (`parentrole`,`depth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_role2picklist` */

CREATE TABLE `vtiger_role2picklist` (
  `roleid` varchar(255) NOT NULL,
  `picklistvalueid` int(10) NOT NULL,
  `picklistid` int(10) NOT NULL,
  `sortid` smallint(5) DEFAULT NULL,
  PRIMARY KEY (`roleid`,`picklistvalueid`,`picklistid`),
  KEY `role2picklist_roleid_picklistid_idx` (`roleid`,`picklistid`,`picklistvalueid`),
  KEY `fk_2_vtiger_role2picklist` (`picklistid`),
  CONSTRAINT `fk_1_vtiger_role2picklist` FOREIGN KEY (`roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE,
  CONSTRAINT `fk_2_vtiger_role2picklist` FOREIGN KEY (`picklistid`) REFERENCES `vtiger_picklist` (`picklistid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_role2profile` */

CREATE TABLE `vtiger_role2profile` (
  `roleid` varchar(255) NOT NULL,
  `profileid` int(10) NOT NULL,
  PRIMARY KEY (`roleid`,`profileid`),
  KEY `role2profile_roleid_profileid_idx` (`roleid`,`profileid`),
  KEY `roleid` (`roleid`),
  KEY `profileid` (`profileid`),
  CONSTRAINT `vtiger_role2profile_ibfk_1` FOREIGN KEY (`roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE,
  CONSTRAINT `vtiger_role2profile_ibfk_2` FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_role_seq` */

CREATE TABLE `vtiger_role_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_rowheight` */

CREATE TABLE `vtiger_rowheight` (
  `rowheightid` int(10) NOT NULL AUTO_INCREMENT,
  `rowheight` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`rowheightid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_rss` */

CREATE TABLE `vtiger_rss` (
  `rssid` int(10) NOT NULL AUTO_INCREMENT,
  `rssurl` varchar(200) NOT NULL DEFAULT '',
  `rsstitle` varchar(200) DEFAULT NULL,
  `rsstype` int(10) DEFAULT 0,
  `starred` int(1) DEFAULT 0,
  PRIMARY KEY (`rssid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_salesmanticketrel` */

CREATE TABLE `vtiger_salesmanticketrel` (
  `smid` int(10) NOT NULL DEFAULT 0,
  `id` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`smid`,`id`),
  KEY `salesmanticketrel_smid_idx` (`smid`),
  KEY `salesmanticketrel_id_idx` (`id`),
  CONSTRAINT `fk_2_vtiger_salesmanticketrel` FOREIGN KEY (`smid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_salutationtype` */

CREATE TABLE `vtiger_salutationtype` (
  `salutationtypeid` int(10) NOT NULL AUTO_INCREMENT,
  `salutationtype` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`salutationtypeid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_scalculations_status` */

CREATE TABLE `vtiger_scalculations_status` (
  `scalculations_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `scalculations_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`scalculations_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_seattachmentsrel` */

CREATE TABLE `vtiger_seattachmentsrel` (
  `crmid` int(10) NOT NULL DEFAULT 0,
  `attachmentsid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`crmid`,`attachmentsid`),
  KEY `seattachmentsrel_attachmentsid_idx` (`attachmentsid`),
  KEY `seattachmentsrel_crmid_idx` (`crmid`),
  KEY `seattachmentsrel_attachmentsid_crmid_idx` (`attachmentsid`,`crmid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_senotesrel` */

CREATE TABLE `vtiger_senotesrel` (
  `crmid` int(10) NOT NULL DEFAULT 0,
  `notesid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`crmid`,`notesid`),
  KEY `senotesrel_notesid_idx` (`notesid`),
  KEY `senotesrel_crmid_idx` (`crmid`),
  CONSTRAINT `fk_2_vtiger_senotesrel` FOREIGN KEY (`notesid`) REFERENCES `vtiger_notes` (`notesid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_seproductsrel` */

CREATE TABLE `vtiger_seproductsrel` (
  `crmid` int(10) NOT NULL DEFAULT 0,
  `productid` int(10) NOT NULL DEFAULT 0,
  `setype` varchar(30) NOT NULL,
  `rel_created_user` int(10) NOT NULL,
  `rel_created_time` datetime NOT NULL,
  `rel_comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`crmid`,`productid`),
  KEY `seproductsrel_productid_idx` (`productid`),
  KEY `seproductrel_crmid_idx` (`crmid`),
  CONSTRAINT `fk_2_vtiger_seproductsrel` FOREIGN KEY (`productid`) REFERENCES `vtiger_products` (`productid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_service` */

CREATE TABLE `vtiger_service` (
  `serviceid` int(10) NOT NULL,
  `service_no` varchar(100) NOT NULL,
  `servicename` varchar(255) NOT NULL,
  `pscategory` varchar(200) DEFAULT NULL,
  `qty_per_unit` decimal(11,2) DEFAULT 0.00,
  `unit_price` decimal(25,8) DEFAULT NULL,
  `sales_start_date` date DEFAULT NULL,
  `sales_end_date` date DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `discontinued` tinyint(1) NOT NULL DEFAULT 0,
  `service_usageunit` varchar(200) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `currency_id` int(10) NOT NULL DEFAULT 1,
  `commissionrate` decimal(7,3) DEFAULT NULL,
  `renewable` tinyint(1) DEFAULT 0,
  `taxes` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`serviceid`),
  CONSTRAINT `fk_1_vtiger_service` FOREIGN KEY (`serviceid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_service_usageunit` */

CREATE TABLE `vtiger_service_usageunit` (
  `service_usageunitid` int(10) NOT NULL AUTO_INCREMENT,
  `service_usageunit` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`service_usageunitid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_servicecf` */

CREATE TABLE `vtiger_servicecf` (
  `serviceid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`serviceid`),
  CONSTRAINT `vtiger_servicecf_ibfk_1` FOREIGN KEY (`serviceid`) REFERENCES `vtiger_service` (`serviceid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_servicecontracts` */

CREATE TABLE `vtiger_servicecontracts` (
  `servicecontractsid` int(10) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `sc_related_to` int(10) DEFAULT NULL,
  `tracking_unit` varchar(100) DEFAULT NULL,
  `total_units` decimal(5,2) DEFAULT NULL,
  `used_units` decimal(5,2) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `planned_duration` varchar(256) DEFAULT NULL,
  `actual_duration` varchar(256) DEFAULT NULL,
  `contract_status` varchar(200) DEFAULT NULL,
  `priority` varchar(200) DEFAULT NULL,
  `contract_type` varchar(200) DEFAULT NULL,
  `progress` decimal(5,2) DEFAULT NULL,
  `contract_no` varchar(100) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`servicecontractsid`),
  KEY `sc_related_to` (`sc_related_to`),
  CONSTRAINT `vtiger_servicecontracts_ibfk_1` FOREIGN KEY (`servicecontractsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_servicecontractscf` */

CREATE TABLE `vtiger_servicecontractscf` (
  `servicecontractsid` int(10) NOT NULL,
  PRIMARY KEY (`servicecontractsid`),
  CONSTRAINT `vtiger_servicecontractscf_ibfk_1` FOREIGN KEY (`servicecontractsid`) REFERENCES `vtiger_servicecontracts` (`servicecontractsid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_seticketsrel` */

CREATE TABLE `vtiger_seticketsrel` (
  `crmid` int(10) NOT NULL DEFAULT 0,
  `ticketid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`crmid`,`ticketid`),
  KEY `seticketsrel_crmid_idx` (`crmid`),
  KEY `seticketsrel_ticketid_idx` (`ticketid`),
  CONSTRAINT `fk_2_vtiger_seticketsrel` FOREIGN KEY (`ticketid`) REFERENCES `vtiger_troubletickets` (`ticketid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_settings_blocks` */

CREATE TABLE `vtiger_settings_blocks` (
  `blockid` int(10) NOT NULL,
  `label` varchar(250) DEFAULT NULL,
  `sequence` int(10) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  `linkto` text DEFAULT NULL,
  `admin_access` text DEFAULT NULL,
  PRIMARY KEY (`blockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_settings_blocks_seq` */

CREATE TABLE `vtiger_settings_blocks_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_settings_field` */

CREATE TABLE `vtiger_settings_field` (
  `fieldid` int(10) NOT NULL AUTO_INCREMENT,
  `blockid` int(10) DEFAULT NULL,
  `name` varchar(250) DEFAULT NULL,
  `iconpath` varchar(300) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `linkto` text DEFAULT NULL,
  `sequence` tinyint(3) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned DEFAULT 0,
  `pinned` tinyint(1) unsigned DEFAULT 0,
  `admin_access` text DEFAULT NULL,
  PRIMARY KEY (`fieldid`),
  KEY `fk_1_vtiger_settings_field` (`blockid`),
  CONSTRAINT `fk_1_vtiger_settings_field` FOREIGN KEY (`blockid`) REFERENCES `vtiger_settings_blocks` (`blockid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_sharedcalendar` */

CREATE TABLE `vtiger_sharedcalendar` (
  `userid` int(10) NOT NULL,
  `sharedid` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`sharedid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_shorturls` */

CREATE TABLE `vtiger_shorturls` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` varchar(50) DEFAULT NULL,
  `handler_path` varchar(400) DEFAULT NULL,
  `handler_class` varchar(100) DEFAULT NULL,
  `handler_function` varchar(100) DEFAULT NULL,
  `handler_data` varchar(255) DEFAULT NULL,
  `onetime` int(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_smsnotifier` */

CREATE TABLE `vtiger_smsnotifier` (
  `smsnotifierid` int(10) NOT NULL,
  `message` text DEFAULT NULL,
  `smsnotifier_status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`smsnotifierid`),
  CONSTRAINT `vtiger_smsnotifier_ibfk_1` FOREIGN KEY (`smsnotifierid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_smsnotifier_status` */

CREATE TABLE `vtiger_smsnotifier_status` (
  `smsnotifier_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `smsnotifier_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`smsnotifier_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_smsnotifiercf` */

CREATE TABLE `vtiger_smsnotifiercf` (
  `smsnotifierid` int(10) NOT NULL,
  PRIMARY KEY (`smsnotifierid`),
  CONSTRAINT `vtiger_smsnotifiercf_ibfk_1` FOREIGN KEY (`smsnotifierid`) REFERENCES `vtiger_smsnotifier` (`smsnotifierid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_soapservice` */

CREATE TABLE `vtiger_soapservice` (
  `id` int(10) DEFAULT NULL,
  `type` varchar(25) DEFAULT NULL,
  `sessionid` varchar(100) DEFAULT NULL,
  `lang` varchar(10) DEFAULT NULL,
  KEY `id` (`id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_squoteenquiries_status` */

CREATE TABLE `vtiger_squoteenquiries_status` (
  `squoteenquiries_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `squoteenquiries_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`squoteenquiries_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_squotes_status` */

CREATE TABLE `vtiger_squotes_status` (
  `squotes_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `squotes_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`squotes_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_srecurringorders_status` */

CREATE TABLE `vtiger_srecurringorders_status` (
  `srecurringorders_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `srecurringorders_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`srecurringorders_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_srequirementscards_status` */

CREATE TABLE `vtiger_srequirementscards_status` (
  `srequirementscards_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `srequirementscards_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`srequirementscards_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ssalesprocesses_source` */

CREATE TABLE `vtiger_ssalesprocesses_source` (
  `ssalesprocesses_sourceid` int(10) NOT NULL AUTO_INCREMENT,
  `ssalesprocesses_source` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ssalesprocesses_sourceid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ssalesprocesses_status` */

CREATE TABLE `vtiger_ssalesprocesses_status` (
  `ssalesprocesses_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `ssalesprocesses_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ssalesprocesses_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ssalesprocesses_type` */

CREATE TABLE `vtiger_ssalesprocesses_type` (
  `ssalesprocesses_typeid` int(10) NOT NULL AUTO_INCREMENT,
  `ssalesprocesses_type` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ssalesprocesses_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ssingleorders_source` */

CREATE TABLE `vtiger_ssingleorders_source` (
  `ssingleorders_sourceid` int(10) NOT NULL AUTO_INCREMENT,
  `ssingleorders_source` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ssingleorders_sourceid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ssingleorders_status` */

CREATE TABLE `vtiger_ssingleorders_status` (
  `ssingleorders_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `ssingleorders_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ssingleorders_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ssservicesstatus` */

CREATE TABLE `vtiger_ssservicesstatus` (
  `ssservicesstatusid` int(10) NOT NULL AUTO_INCREMENT,
  `ssservicesstatus` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`ssservicesstatusid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_start_hour` */

CREATE TABLE `vtiger_start_hour` (
  `start_hourid` int(10) NOT NULL AUTO_INCREMENT,
  `start_hour` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`start_hourid`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_state` */

CREATE TABLE `vtiger_state` (
  `stateid` int(10) NOT NULL AUTO_INCREMENT,
  `state` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`stateid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_status` */

CREATE TABLE `vtiger_status` (
  `statusid` int(10) NOT NULL AUTO_INCREMENT,
  `status` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_storage_status` */

CREATE TABLE `vtiger_storage_status` (
  `storage_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `storage_status` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`storage_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_storage_type` */

CREATE TABLE `vtiger_storage_type` (
  `storage_typeid` int(10) NOT NULL AUTO_INCREMENT,
  `storage_type` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`storage_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_subindustry` */

CREATE TABLE `vtiger_subindustry` (
  `subindustryid` int(10) NOT NULL AUTO_INCREMENT,
  `subindustry` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`subindustryid`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_subunit` */

CREATE TABLE `vtiger_subunit` (
  `subunitid` int(10) NOT NULL AUTO_INCREMENT,
  `subunit` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`subunitid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_support_processes` */

CREATE TABLE `vtiger_support_processes` (
  `id` int(10) NOT NULL,
  `ticket_status_indicate_closing` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_svendorenquiries_status` */

CREATE TABLE `vtiger_svendorenquiries_status` (
  `svendorenquiries_statusid` int(10) NOT NULL AUTO_INCREMENT,
  `svendorenquiries_status` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `picklist_valueid` smallint(5) DEFAULT 0,
  `sortorderid` smallint(5) DEFAULT 0,
  PRIMARY KEY (`svendorenquiries_statusid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_sync_caldav` */

CREATE TABLE `vtiger_sync_caldav` (
  `sync_caldavid` int(11) NOT NULL AUTO_INCREMENT,
  `sync_caldav` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`sync_caldavid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_sync_carddav` */

CREATE TABLE `vtiger_sync_carddav` (
  `sync_carddavid` int(11) NOT NULL AUTO_INCREMENT,
  `sync_carddav` varchar(255) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`sync_carddavid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_systems` */

CREATE TABLE `vtiger_systems` (
  `id` int(10) NOT NULL,
  `server` varchar(100) DEFAULT NULL,
  `server_port` int(10) DEFAULT NULL,
  `server_username` varchar(100) DEFAULT NULL,
  `server_password` varchar(100) DEFAULT NULL,
  `server_type` varchar(20) DEFAULT NULL,
  `smtp_auth` varchar(5) DEFAULT NULL,
  `server_path` varchar(256) DEFAULT NULL,
  `from_email_field` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tab` */

CREATE TABLE `vtiger_tab` (
  `tabid` smallint(5) NOT NULL DEFAULT 0,
  `name` varchar(25) NOT NULL,
  `presence` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `tabsequence` smallint(5) NOT NULL DEFAULT 0,
  `tablabel` varchar(25) NOT NULL,
  `modifiedby` smallint(5) DEFAULT NULL,
  `modifiedtime` int(10) DEFAULT NULL,
  `customized` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `ownedby` tinyint(1) NOT NULL DEFAULT 0,
  `isentitytype` tinyint(1) NOT NULL DEFAULT 1,
  `version` varchar(10) DEFAULT NULL,
  `parent` varchar(30) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `coloractive` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`tabid`),
  UNIQUE KEY `tab_name_idx` (`name`),
  KEY `tab_modifiedby_idx` (`modifiedby`),
  KEY `tab_tabid_idx` (`tabid`),
  KEY `name` (`name`,`presence`),
  KEY `presence` (`presence`),
  KEY `name_2` (`name`,`presence`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tab_info` */

CREATE TABLE `vtiger_tab_info` (
  `tabid` smallint(5) DEFAULT NULL,
  `prefname` varchar(256) DEFAULT NULL,
  `prefvalue` varchar(256) DEFAULT NULL,
  KEY `fk_1_vtiger_tab_info` (`tabid`),
  CONSTRAINT `fk_1_vtiger_tab_info` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_taskpriority` */

CREATE TABLE `vtiger_taskpriority` (
  `taskpriorityid` int(10) NOT NULL AUTO_INCREMENT,
  `taskpriority` varchar(200) DEFAULT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`taskpriorityid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ticketcf` */

CREATE TABLE `vtiger_ticketcf` (
  `ticketid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ticketid`),
  CONSTRAINT `fk_1_vtiger_ticketcf` FOREIGN KEY (`ticketid`) REFERENCES `vtiger_troubletickets` (`ticketid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ticketpriorities` */

CREATE TABLE `vtiger_ticketpriorities` (
  `ticketpriorities_id` int(10) NOT NULL AUTO_INCREMENT,
  `ticketpriorities` varchar(200) DEFAULT NULL,
  `presence` int(1) NOT NULL DEFAULT 0,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  `color` varchar(25) DEFAULT '	#E6FAD8',
  PRIMARY KEY (`ticketpriorities_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ticketseverities` */

CREATE TABLE `vtiger_ticketseverities` (
  `ticketseverities_id` int(10) NOT NULL AUTO_INCREMENT,
  `ticketseverities` varchar(200) DEFAULT NULL,
  `presence` int(1) NOT NULL DEFAULT 0,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`ticketseverities_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ticketstatus` */

CREATE TABLE `vtiger_ticketstatus` (
  `ticketstatus_id` int(10) NOT NULL AUTO_INCREMENT,
  `ticketstatus` varchar(200) DEFAULT NULL,
  `presence` int(1) NOT NULL DEFAULT 0,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  `color` varchar(25) DEFAULT '#E6FAD8',
  PRIMARY KEY (`ticketstatus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_time_zone` */

CREATE TABLE `vtiger_time_zone` (
  `time_zoneid` int(10) NOT NULL AUTO_INCREMENT,
  `time_zone` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`time_zoneid`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_timecontrol_type` */

CREATE TABLE `vtiger_timecontrol_type` (
  `timecontrol_typeid` int(10) NOT NULL AUTO_INCREMENT,
  `timecontrol_type` varchar(200) NOT NULL,
  `sortorderid` int(10) DEFAULT NULL,
  `presence` int(10) NOT NULL DEFAULT 1,
  `color` varchar(25) DEFAULT '#E6FAD8',
  PRIMARY KEY (`timecontrol_typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tmp_read_group_rel_sharing_per` */

CREATE TABLE `vtiger_tmp_read_group_rel_sharing_per` (
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `relatedtabid` int(10) NOT NULL,
  `sharedgroupid` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`tabid`,`relatedtabid`,`sharedgroupid`),
  KEY `tmp_read_group_rel_sharing_per_userid_sharedgroupid_tabid` (`userid`,`sharedgroupid`,`tabid`),
  CONSTRAINT `fk_4_vtiger_tmp_read_group_rel_sharing_per` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tmp_read_group_sharing_per` */

CREATE TABLE `vtiger_tmp_read_group_sharing_per` (
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `sharedgroupid` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`tabid`,`sharedgroupid`),
  KEY `tmp_read_group_sharing_per_userid_sharedgroupid_idx` (`userid`,`sharedgroupid`),
  CONSTRAINT `fk_3_vtiger_tmp_read_group_sharing_per` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tmp_read_user_rel_sharing_per` */

CREATE TABLE `vtiger_tmp_read_user_rel_sharing_per` (
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `relatedtabid` int(10) NOT NULL,
  `shareduserid` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`tabid`,`relatedtabid`,`shareduserid`),
  KEY `tmp_read_user_rel_sharing_per_userid_shared_reltabid_idx` (`userid`,`shareduserid`,`relatedtabid`),
  CONSTRAINT `fk_4_vtiger_tmp_read_user_rel_sharing_per` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tmp_read_user_sharing_per` */

CREATE TABLE `vtiger_tmp_read_user_sharing_per` (
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `shareduserid` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`tabid`,`shareduserid`),
  KEY `tmp_read_user_sharing_per_userid_shareduserid_idx` (`userid`,`shareduserid`),
  CONSTRAINT `fk_3_vtiger_tmp_read_user_sharing_per` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tmp_write_group_rel_sharing_per` */

CREATE TABLE `vtiger_tmp_write_group_rel_sharing_per` (
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `relatedtabid` int(10) NOT NULL,
  `sharedgroupid` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`tabid`,`relatedtabid`,`sharedgroupid`),
  KEY `tmp_write_group_rel_sharing_per_userid_sharedgroupid_tabid_idx` (`userid`,`sharedgroupid`,`tabid`),
  CONSTRAINT `fk_4_vtiger_tmp_write_group_rel_sharing_per` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tmp_write_group_sharing_per` */

CREATE TABLE `vtiger_tmp_write_group_sharing_per` (
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `sharedgroupid` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`tabid`,`sharedgroupid`),
  KEY `tmp_write_group_sharing_per_UK1` (`userid`,`sharedgroupid`),
  CONSTRAINT `fk_3_vtiger_tmp_write_group_sharing_per` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tmp_write_user_rel_sharing_per` */

CREATE TABLE `vtiger_tmp_write_user_rel_sharing_per` (
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `relatedtabid` int(10) NOT NULL,
  `shareduserid` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`tabid`,`relatedtabid`,`shareduserid`),
  KEY `tmp_write_user_rel_sharing_per_userid_sharduserid_tabid_idx` (`userid`,`shareduserid`,`tabid`),
  CONSTRAINT `fk_4_vtiger_tmp_write_user_rel_sharing_per` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tmp_write_user_sharing_per` */

CREATE TABLE `vtiger_tmp_write_user_sharing_per` (
  `userid` int(10) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `shareduserid` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`tabid`,`shareduserid`),
  KEY `tmp_write_user_sharing_per_userid_shareduserid_idx` (`userid`,`shareduserid`),
  CONSTRAINT `fk_3_vtiger_tmp_write_user_sharing_per` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_tracking_unit` */

CREATE TABLE `vtiger_tracking_unit` (
  `tracking_unitid` int(10) NOT NULL AUTO_INCREMENT,
  `tracking_unit` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`tracking_unitid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_trees_templates` */

CREATE TABLE `vtiger_trees_templates` (
  `templateid` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `module` int(10) DEFAULT NULL,
  `access` int(1) DEFAULT 1,
  `share` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`templateid`),
  KEY `module` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_trees_templates_data` */

CREATE TABLE `vtiger_trees_templates_data` (
  `templateid` smallint(5) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `tree` varchar(255) NOT NULL,
  `parentTree` varchar(255) NOT NULL,
  `depth` tinyint(3) unsigned NOT NULL,
  `label` varchar(255) NOT NULL,
  `state` varchar(100) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL DEFAULT '',
  KEY `id` (`templateid`),
  KEY `parentTree` (`parentTree`,`templateid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_troubletickets` */

CREATE TABLE `vtiger_troubletickets` (
  `ticketid` int(10) NOT NULL,
  `ticket_no` varchar(100) NOT NULL,
  `groupname` varchar(100) DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `product_id` int(10) DEFAULT NULL,
  `priority` varchar(200) DEFAULT NULL,
  `severity` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `category` varchar(200) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `solution` text DEFAULT NULL,
  `update_log` text DEFAULT NULL,
  `version_id` int(10) DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `servicecontractsid` int(10) DEFAULT NULL,
  `attention` text DEFAULT NULL,
  `pssold_id` int(10) DEFAULT NULL,
  `ordertime` decimal(10,2) DEFAULT NULL,
  `from_portal` smallint(1) DEFAULT NULL,
  `contract_type` varchar(255) DEFAULT NULL,
  `contracts_end_date` date DEFAULT NULL,
  `report_time` int(10) DEFAULT NULL,
  `response_time` datetime DEFAULT NULL,
  PRIMARY KEY (`ticketid`),
  KEY `troubletickets_ticketid_idx` (`ticketid`),
  KEY `troubletickets_status_idx` (`status`),
  KEY `parent_id` (`parent_id`),
  KEY `product_id` (`product_id`),
  KEY `servicecontractsid` (`servicecontractsid`),
  KEY `pssold_id` (`pssold_id`),
  KEY `ticket_no` (`ticket_no`),
  CONSTRAINT `fk_1_vtiger_troubletickets` FOREIGN KEY (`ticketid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_usageunit` */

CREATE TABLE `vtiger_usageunit` (
  `usageunitid` int(10) NOT NULL AUTO_INCREMENT,
  `usageunit` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT NULL,
  PRIMARY KEY (`usageunitid`),
  UNIQUE KEY `usageunit_usageunit_idx` (`usageunit`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_user2mergefields` */

CREATE TABLE `vtiger_user2mergefields` (
  `userid` int(10) DEFAULT NULL,
  `tabid` smallint(5) DEFAULT NULL,
  `fieldid` int(10) DEFAULT NULL,
  `visible` int(2) DEFAULT NULL,
  KEY `userid_tabid_idx` (`userid`,`tabid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_user2role` */

CREATE TABLE `vtiger_user2role` (
  `userid` int(10) NOT NULL,
  `roleid` varchar(255) NOT NULL,
  PRIMARY KEY (`userid`),
  KEY `user2role_roleid_idx` (`roleid`),
  CONSTRAINT `fk_2_vtiger_user2role` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_user_module_preferences` */

CREATE TABLE `vtiger_user_module_preferences` (
  `userid` varchar(30) NOT NULL,
  `tabid` smallint(5) NOT NULL,
  `default_cvid` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`tabid`),
  KEY `fk_2_vtiger_user_module_preferences` (`tabid`),
  CONSTRAINT `fk_2_vtiger_user_module_preferences` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_users` */

CREATE TABLE `vtiger_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(64) DEFAULT NULL,
  `first_name` varchar(30) DEFAULT NULL,
  `last_name` varchar(30) DEFAULT NULL,
  `email1` varchar(100) DEFAULT NULL,
  `is_admin` varchar(3) DEFAULT '0',
  `status` varchar(25) DEFAULT NULL,
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `language` varchar(36) DEFAULT NULL,
  `user_password` varchar(200) DEFAULT NULL,
  `internal_mailer` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `reports_to_id` mediumint(10) unsigned DEFAULT NULL,
  `modified_user_id` varchar(36) DEFAULT NULL,
  `currency_id` mediumint(10) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `date_entered` timestamp NULL DEFAULT NULL,
  `date_modified` timestamp NULL DEFAULT NULL,
  `date_password_change` datetime DEFAULT NULL,
  `force_password_change` tinyint(1) DEFAULT 0,
  `holidays` varchar(60) DEFAULT NULL,
  `namedays` varchar(60) DEFAULT NULL,
  `workdays` varchar(30) DEFAULT NULL,
  `weekstart` int(10) DEFAULT NULL,
  `date_format` varchar(200) DEFAULT NULL,
  `hour_format` varchar(30) DEFAULT 'am/pm',
  `start_hour` varchar(30) DEFAULT '10:00',
  `end_hour` varchar(30) DEFAULT '23:00',
  `view_date_format` varchar(50) DEFAULT 'PLL_ELAPSED',
  `activity_view` varchar(200) DEFAULT 'Today',
  `lead_view` varchar(200) DEFAULT 'Today',
  `imagename` text DEFAULT NULL,
  `reminder_interval` varchar(100) DEFAULT NULL,
  `reminder_next_time` varchar(100) DEFAULT NULL,
  `theme` varchar(100) DEFAULT NULL,
  `tz` varchar(30) DEFAULT NULL,
  `time_zone` varchar(200) DEFAULT NULL,
  `currency_grouping_pattern` varchar(100) DEFAULT NULL,
  `currency_decimal_separator` varchar(2) DEFAULT NULL,
  `currency_grouping_separator` varchar(2) DEFAULT NULL,
  `currency_symbol_placement` varchar(20) DEFAULT NULL,
  `no_of_currency_decimals` varchar(1) DEFAULT NULL,
  `truncate_trailing_zeros` tinyint(1) unsigned DEFAULT NULL,
  `dayoftheweek` varchar(100) DEFAULT NULL,
  `othereventduration` text DEFAULT NULL,
  `default_record_view` varchar(10) DEFAULT NULL,
  `leftpanelhide` tinyint(3) unsigned DEFAULT NULL,
  `rowheight` varchar(10) DEFAULT NULL,
  `defaulteventstatus` varchar(50) DEFAULT NULL,
  `defaultactivitytype` varchar(50) DEFAULT NULL,
  `is_owner` varchar(5) DEFAULT NULL,
  `emailoptout` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `available` tinyint(1) DEFAULT 0,
  `auto_assign` tinyint(1) DEFAULT 0,
  `records_limit` int(10) DEFAULT NULL,
  `phone_crm_extension` varchar(100) DEFAULT NULL,
  `phone_crm_extension_extra` varchar(100) DEFAULT NULL,
  `accesskey` varchar(36) DEFAULT NULL,
  `confirm_password` varchar(200) DEFAULT NULL,
  `cal_color` varchar(25) DEFAULT NULL,
  `user_preferences` text DEFAULT NULL,
  `authy_methods` varchar(255) DEFAULT NULL,
  `authy_secret_totp` varchar(255) DEFAULT NULL,
  `login_method` varchar(255) DEFAULT 'PLL_PASSWORD',
  `sync_carddav` varchar(100) DEFAULT 'PLL_OWNER',
  `sync_caldav` varchar(100) DEFAULT 'PLL_OWNER',
  `sync_carddav_default_country` varchar(255) DEFAULT NULL,
  `default_search_module` varchar(25) DEFAULT NULL,
  `default_search_override` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email1` (`email1`),
  KEY `user_user_name_idx` (`user_name`),
  KEY `user_user_password_idx` (`user_password`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_users2group` */

CREATE TABLE `vtiger_users2group` (
  `groupid` int(10) NOT NULL,
  `userid` int(10) NOT NULL,
  PRIMARY KEY (`groupid`,`userid`),
  KEY `users2group_groupname_uerid_idx` (`groupid`,`userid`),
  KEY `fk_2_vtiger_users2group` (`userid`),
  CONSTRAINT `fk_2_vtiger_users2group` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_users_last_import` */

CREATE TABLE `vtiger_users_last_import` (
  `id` int(36) NOT NULL AUTO_INCREMENT,
  `assigned_user_id` varchar(36) DEFAULT NULL,
  `bean_type` varchar(36) DEFAULT NULL,
  `bean_id` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`assigned_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_users_seq` */

CREATE TABLE `vtiger_users_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_userscf` */

CREATE TABLE `vtiger_userscf` (
  `usersid` int(10) NOT NULL,
  PRIMARY KEY (`usersid`),
  CONSTRAINT `vtiger_userscf_ibfk_1` FOREIGN KEY (`usersid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_vendor` */

CREATE TABLE `vtiger_vendor` (
  `vendorid` int(10) NOT NULL DEFAULT 0,
  `vendor_no` varchar(100) NOT NULL,
  `vendorname` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `glacct` varchar(200) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `vat_id` varchar(30) DEFAULT NULL,
  `registration_number_1` varchar(30) DEFAULT NULL,
  `registration_number_2` varchar(30) DEFAULT NULL,
  `verification` text DEFAULT NULL,
  `sum_time` decimal(10,2) DEFAULT 0.00,
  `active` tinyint(1) DEFAULT 0,
  `phone_extra` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`vendorid`),
  KEY `vendorname` (`vendorname`),
  CONSTRAINT `fk_1_vtiger_vendor` FOREIGN KEY (`vendorid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_vendoraddress` */

CREATE TABLE `vtiger_vendoraddress` (
  `vendorid` int(10) NOT NULL,
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

/*Table structure for table `vtiger_vendorcf` */

CREATE TABLE `vtiger_vendorcf` (
  `vendorid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`vendorid`),
  CONSTRAINT `fk_1_vtiger_vendorcf` FOREIGN KEY (`vendorid`) REFERENCES `vtiger_vendor` (`vendorid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_vendorcontactrel` */

CREATE TABLE `vtiger_vendorcontactrel` (
  `vendorid` int(10) NOT NULL DEFAULT 0,
  `contactid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`vendorid`,`contactid`),
  KEY `vendorcontactrel_vendorid_idx` (`vendorid`),
  KEY `vendorcontactrel_contact_idx` (`contactid`),
  CONSTRAINT `fk_2_vtiger_vendorcontactrel` FOREIGN KEY (`vendorid`) REFERENCES `vtiger_vendor` (`vendorid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_verification` */

CREATE TABLE `vtiger_verification` (
  `verificationid` int(10) NOT NULL AUTO_INCREMENT,
  `verification` varchar(200) NOT NULL,
  `presence` int(1) NOT NULL DEFAULT 1,
  `picklist_valueid` int(10) NOT NULL DEFAULT 0,
  `sortorderid` int(10) DEFAULT 0,
  PRIMARY KEY (`verificationid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_version` */

CREATE TABLE `vtiger_version` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `old_version` varchar(30) DEFAULT NULL,
  `current_version` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_version_seq` */

CREATE TABLE `vtiger_version_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_view_date_format` */

CREATE TABLE `vtiger_view_date_format` (
  `view_date_formatid` int(11) NOT NULL AUTO_INCREMENT,
  `view_date_format` varchar(50) DEFAULT NULL,
  `presence` tinyint(1) DEFAULT 1,
  `sortorderid` smallint(6) DEFAULT 0,
  PRIMARY KEY (`view_date_formatid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_visibility` */

CREATE TABLE `vtiger_visibility` (
  `visibilityid` int(10) NOT NULL AUTO_INCREMENT,
  `visibility` varchar(200) NOT NULL,
  `sortorderid` int(10) NOT NULL DEFAULT 0,
  `presence` int(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`visibilityid`),
  UNIQUE KEY `visibility_visibility_idx` (`visibility`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_widgets` */

CREATE TABLE `vtiger_widgets` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tabid` smallint(5) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `wcol` tinyint(1) DEFAULT 1,
  `sequence` tinyint(2) DEFAULT NULL,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tabid` (`tabid`),
  CONSTRAINT `vtiger_widgets_ibfk_1` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ws_entity` */

CREATE TABLE `vtiger_ws_entity` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `handler_path` varchar(255) NOT NULL,
  `handler_class` varchar(64) NOT NULL,
  `ismodule` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ws_entity_seq` */

CREATE TABLE `vtiger_ws_entity_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ws_fieldinfo` */

CREATE TABLE `vtiger_ws_fieldinfo` (
  `id` varchar(64) NOT NULL,
  `property_name` varchar(32) DEFAULT NULL,
  `property_value` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ws_fieldtype` */

CREATE TABLE `vtiger_ws_fieldtype` (
  `fieldtypeid` int(10) NOT NULL AUTO_INCREMENT,
  `uitype` smallint(3) NOT NULL,
  `fieldtype` varchar(200) NOT NULL,
  PRIMARY KEY (`fieldtypeid`),
  UNIQUE KEY `uitype_idx` (`uitype`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ws_operation` */

CREATE TABLE `vtiger_ws_operation` (
  `operationid` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `handler_path` varchar(255) NOT NULL,
  `handler_method` varchar(64) NOT NULL,
  `type` varchar(8) NOT NULL,
  `prelogin` int(3) NOT NULL,
  PRIMARY KEY (`operationid`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ws_operation_parameters` */

CREATE TABLE `vtiger_ws_operation_parameters` (
  `operationid` int(10) NOT NULL,
  `name` varchar(128) NOT NULL,
  `type` varchar(64) NOT NULL,
  `sequence` int(10) NOT NULL,
  PRIMARY KEY (`operationid`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ws_operation_seq` */

CREATE TABLE `vtiger_ws_operation_seq` (
  `id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ws_referencetype` */

CREATE TABLE `vtiger_ws_referencetype` (
  `fieldtypeid` int(10) NOT NULL,
  `type` varchar(25) NOT NULL,
  PRIMARY KEY (`fieldtypeid`,`type`),
  KEY `fieldtypeid` (`fieldtypeid`),
  CONSTRAINT `fk_1_vtiger_referencetype` FOREIGN KEY (`fieldtypeid`) REFERENCES `vtiger_ws_fieldtype` (`fieldtypeid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vtiger_ws_userauthtoken` */

CREATE TABLE `vtiger_ws_userauthtoken` (
  `userid` int(10) NOT NULL,
  `token` varchar(36) NOT NULL,
  `expiretime` int(10) NOT NULL,
  PRIMARY KEY (`userid`,`expiretime`),
  UNIQUE KEY `userid_idx` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `w_yf_portal_session` */

CREATE TABLE `w_yf_portal_session` (
  `id` varchar(32) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `language` varchar(10) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `changed` datetime DEFAULT NULL,
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `w_yf_portal_user` */

CREATE TABLE `w_yf_portal_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `server_id` int(10) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `user_name` varchar(50) NOT NULL,
  `password_h` varchar(200) DEFAULT NULL,
  `password_t` varchar(200) DEFAULT NULL,
  `type` tinyint(1) unsigned DEFAULT 1,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `language` varchar(10) DEFAULT NULL,
  `crmid` int(10) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `istorage` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `user_name_2` (`user_name`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `w_yf_servers` */

CREATE TABLE `w_yf_servers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `pass` varchar(100) DEFAULT NULL,
  `acceptable_url` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `api_key` varchar(100) NOT NULL,
  `type` varchar(40) NOT NULL,
  `accounts_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `yetiforce_auth` */

CREATE TABLE `yetiforce_auth` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL,
  `param` varchar(20) DEFAULT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`param`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `yetiforce_currencyupdate` */

CREATE TABLE `yetiforce_currencyupdate` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `currency_id` int(10) NOT NULL,
  `fetch_date` date NOT NULL,
  `exchange_date` date NOT NULL,
  `exchange` decimal(10,4) NOT NULL,
  `bank_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fetchdate_currencyid_unique` (`currency_id`,`exchange_date`,`bank_id`),
  KEY `fk_1_vtiger_osscurrencies` (`currency_id`),
  CONSTRAINT `fk_1_vtiger_osscurrencies` FOREIGN KEY (`currency_id`) REFERENCES `vtiger_currency_info` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `yetiforce_currencyupdate_banks` */

CREATE TABLE `yetiforce_currencyupdate_banks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `bank_name` varchar(255) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bankname` (`bank_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `yetiforce_mail_config` */

CREATE TABLE `yetiforce_mail_config` (
  `type` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `value` text DEFAULT NULL,
  UNIQUE KEY `type` (`type`,`name`),
  KEY `type_2` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `yetiforce_mail_quantities` */

CREATE TABLE `yetiforce_mail_quantities` (
  `userid` int(10) unsigned NOT NULL,
  `num` int(10) unsigned DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`userid`),
  CONSTRAINT `yetiforce_mail_quantities_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `yetiforce_menu` */

CREATE TABLE `yetiforce_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` int(10) DEFAULT NULL,
  `parentid` int(10) DEFAULT 0,
  `type` tinyint(1) DEFAULT NULL,
  `sequence` int(3) DEFAULT NULL,
  `module` smallint(5) DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `newwindow` tinyint(1) DEFAULT 0,
  `dataurl` text DEFAULT NULL,
  `showicon` tinyint(1) DEFAULT 0,
  `icon` varchar(255) DEFAULT NULL,
  `sizeicon` varchar(255) DEFAULT NULL,
  `hotkey` varchar(30) DEFAULT NULL,
  `filters` varchar(255) DEFAULT NULL,
  `source` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parentid`),
  KEY `role` (`role`),
  KEY `module` (`module`),
  CONSTRAINT `yetiforce_menu_ibfk_1` FOREIGN KEY (`module`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8;

/*Table structure for table `yetiforce_proc_marketing` */

CREATE TABLE `yetiforce_proc_marketing` (
  `type` varchar(30) DEFAULT NULL,
  `param` varchar(30) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  KEY `type` (`type`,`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `yetiforce_proc_sales` */

CREATE TABLE `yetiforce_proc_sales` (
  `type` varchar(30) DEFAULT NULL,
  `param` varchar(30) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  KEY `type` (`type`,`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `yetiforce_proc_tc` */

CREATE TABLE `yetiforce_proc_tc` (
  `type` varchar(30) DEFAULT NULL,
  `param` varchar(30) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `yetiforce_updates` */

CREATE TABLE `yetiforce_updates` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT NULL,
  `user` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `from_version` varchar(10) DEFAULT NULL,
  `to_version` varchar(10) DEFAULT NULL,
  `result` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
