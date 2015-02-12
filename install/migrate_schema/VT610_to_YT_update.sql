ALTER TABLE `vtiger_leadaddress` 
	CHANGE `phone` `phone` varchar(50)  COLLATE utf8_general_ci NULL after `leadaddressid` , 
	ADD COLUMN `addresslevel1a` varchar(255)  COLLATE utf8_general_ci NULL after `fax` , 
	ADD COLUMN `addresslevel2a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel1a` , 
	ADD COLUMN `addresslevel3a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel2a` , 
	ADD COLUMN `addresslevel4a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel3a` , 
	ADD COLUMN `addresslevel5a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel4a` , 
	ADD COLUMN `addresslevel6a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel5a` , 
	ADD COLUMN `addresslevel7a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel6a` , 
	ADD COLUMN `addresslevel8a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel7a` , 
	ADD COLUMN `buildingnumbera` varchar(100)  COLLATE utf8_general_ci NULL after `addresslevel8a` , 
	ADD COLUMN `localnumbera` varchar(100)  COLLATE utf8_general_ci NULL after `buildingnumbera` ; 
ALTER TABLE `vtiger_leadaddress`
	ADD CONSTRAINT `fk_1_vtiger_leadaddress` 
	FOREIGN KEY (`leadaddressid`) REFERENCES `vtiger_leaddetails` (`leadid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_contactaddress` 
	ADD COLUMN `addresslevel1a` varchar(255)  COLLATE utf8_general_ci NULL after `contactaddressid` , 
	ADD COLUMN `addresslevel1b` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel1a` , 
	ADD COLUMN `addresslevel2a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel1b` , 
	ADD COLUMN `addresslevel2b` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel2a` , 
	ADD COLUMN `addresslevel3a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel2b` , 
	ADD COLUMN `addresslevel3b` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel3a` , 
	ADD COLUMN `addresslevel4a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel3b` , 
	ADD COLUMN `addresslevel4b` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel4a` , 
	ADD COLUMN `addresslevel5a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel4b` , 
	ADD COLUMN `addresslevel5b` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel5a` , 
	ADD COLUMN `addresslevel6a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel5b` , 
	ADD COLUMN `addresslevel6b` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel6a` , 
	ADD COLUMN `addresslevel7a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel6b` , 
	ADD COLUMN `addresslevel7b` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel7a` , 
	ADD COLUMN `addresslevel8a` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel7b` , 
	ADD COLUMN `addresslevel8b` varchar(255)  COLLATE utf8_general_ci NULL after `addresslevel8a` , 
	ADD COLUMN `buildingnumbera` varchar(100)  COLLATE utf8_general_ci NULL after `addresslevel8b` , 
	ADD COLUMN `localnumbera` varchar(100)  COLLATE utf8_general_ci NULL after `buildingnumbera` , 
	ADD COLUMN `buildingnumberb` varchar(100)  COLLATE utf8_general_ci NULL after `localnumbera` , 
	ADD COLUMN `localnumberb` varchar(100)  COLLATE utf8_general_ci NULL after `buildingnumberb` ; 
	
ALTER TABLE `vtiger_field` 
	ADD COLUMN `fieldparams` varchar(255) COLLATE utf8_general_ci NULL after `summaryfield` ;

ALTER TABLE `vtiger_contactscf`
	ADD CONSTRAINT `fk_1_vtiger_contactscf` 
	FOREIGN KEY (`contactid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_contactaddress`
	ADD CONSTRAINT `fk_1_vtiger_contactaddress` 
	FOREIGN KEY (`contactaddressid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_leadscf`
	ADD CONSTRAINT `fk_1_vtiger_leadscf` 
	FOREIGN KEY (`leadid`) REFERENCES `vtiger_leaddetails` (`leadid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_leadsubdetails`
	ADD CONSTRAINT `fk_1_vtiger_leadsubdetails` 
	FOREIGN KEY (`leadsubscriptionid`) REFERENCES `vtiger_leaddetails` (`leadid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_links` 
	ADD KEY `linkid`(`linkid`,`tabid`,`linktype`,`linklabel`) , 
	ADD KEY `linklabel`(`linklabel`) ;
	
ALTER TABLE `vtiger_loginhistory` 
	ADD COLUMN `browser` varchar(25)  COLLATE utf8_general_ci NULL after `status` , 
	ADD COLUMN `unblock` tinyint(1)   NULL DEFAULT 0 after `browser` , 
	ADD KEY `user_name`(`user_name`) ;
	
ALTER TABLE `vtiger_users` 
	CHANGE `email1` `email1` varchar(100)  COLLATE utf8_general_ci NULL after `modified_user_id` , 
	CHANGE `status` `status` varchar(25)  COLLATE utf8_general_ci NULL after `email1` , 
	CHANGE `signature` `signature` text  COLLATE utf8_general_ci NULL after `status` , 
	CHANGE `user_preferences` `user_preferences` text  COLLATE utf8_general_ci NULL after `signature` , 
	CHANGE `tz` `tz` varchar(30)  COLLATE utf8_general_ci NULL after `user_preferences` , 
	CHANGE `holidays` `holidays` varchar(60)  COLLATE utf8_general_ci NULL after `tz` , 
	CHANGE `namedays` `namedays` varchar(60)  COLLATE utf8_general_ci NULL after `holidays` , 
	CHANGE `workdays` `workdays` varchar(30)  COLLATE utf8_general_ci NULL after `namedays` , 
	CHANGE `weekstart` `weekstart` int(11)   NULL after `workdays` , 
	CHANGE `date_format` `date_format` varchar(200)  COLLATE utf8_general_ci NULL after `weekstart` , 
	CHANGE `hour_format` `hour_format` varchar(30)  COLLATE utf8_general_ci NULL DEFAULT 'am/pm' after `date_format` , 
	CHANGE `start_hour` `start_hour` varchar(30)  COLLATE utf8_general_ci NULL DEFAULT '10:00' after `hour_format` , 
	CHANGE `end_hour` `end_hour` varchar(30)  COLLATE utf8_general_ci NULL DEFAULT '23:00' after `start_hour` , 
	CHANGE `activity_view` `activity_view` varchar(200)  COLLATE utf8_general_ci NULL DEFAULT 'Today' after `end_hour` , 
	CHANGE `lead_view` `lead_view` varchar(200)  COLLATE utf8_general_ci NULL DEFAULT 'Today' after `activity_view` , 
	CHANGE `imagename` `imagename` varchar(250)  COLLATE utf8_general_ci NULL after `lead_view` , 
	CHANGE `deleted` `deleted` int(1)   NOT NULL DEFAULT 0 after `imagename` , 
	CHANGE `confirm_password` `confirm_password` varchar(300)  COLLATE utf8_general_ci NULL after `deleted` , 
	CHANGE `internal_mailer` `internal_mailer` varchar(3)  COLLATE utf8_general_ci NOT NULL DEFAULT '1' after `confirm_password` , 
	CHANGE `reminder_interval` `reminder_interval` varchar(100)  COLLATE utf8_general_ci NULL after `internal_mailer` , 
	CHANGE `reminder_next_time` `reminder_next_time` varchar(100)  COLLATE utf8_general_ci NULL after `reminder_interval` , 
	CHANGE `crypt_type` `crypt_type` varchar(20)  COLLATE utf8_general_ci NOT NULL DEFAULT 'MD5' after `reminder_next_time` , 
	CHANGE `accesskey` `accesskey` varchar(36)  COLLATE utf8_general_ci NULL after `crypt_type` , 
	CHANGE `theme` `theme` varchar(100)  COLLATE utf8_general_ci NULL after `accesskey` , 
	CHANGE `language` `language` varchar(36)  COLLATE utf8_general_ci NULL after `theme` , 
	CHANGE `time_zone` `time_zone` varchar(200)  COLLATE utf8_general_ci NULL after `language` , 
	CHANGE `currency_grouping_pattern` `currency_grouping_pattern` varchar(100)  COLLATE utf8_general_ci NULL after `time_zone` , 
	CHANGE `currency_decimal_separator` `currency_decimal_separator` varchar(2)  COLLATE utf8_general_ci NULL after `currency_grouping_pattern` , 
	CHANGE `currency_grouping_separator` `currency_grouping_separator` varchar(2)  COLLATE utf8_general_ci NULL after `currency_decimal_separator` , 
	CHANGE `currency_symbol_placement` `currency_symbol_placement` varchar(20)  COLLATE utf8_general_ci NULL after `currency_grouping_separator` , 
	CHANGE `phone_crm_extension` `phone_crm_extension` varchar(100)  COLLATE utf8_general_ci NULL after `currency_symbol_placement` , 
	CHANGE `no_of_currency_decimals` `no_of_currency_decimals` varchar(2)  COLLATE utf8_general_ci NULL after `phone_crm_extension` , 
	CHANGE `truncate_trailing_zeros` `truncate_trailing_zeros` varchar(3)  COLLATE utf8_general_ci NULL after `no_of_currency_decimals` , 
	CHANGE `dayoftheweek` `dayoftheweek` varchar(100)  COLLATE utf8_general_ci NULL after `truncate_trailing_zeros` , 
	CHANGE `callduration` `callduration` varchar(100)  COLLATE utf8_general_ci NULL after `dayoftheweek` , 
	CHANGE `othereventduration` `othereventduration` varchar(100)  COLLATE utf8_general_ci NULL after `callduration` , 
	CHANGE `calendarsharedtype` `calendarsharedtype` varchar(100)  COLLATE utf8_general_ci NULL after `othereventduration` , 
	CHANGE `default_record_view` `default_record_view` varchar(10)  COLLATE utf8_general_ci NULL after `calendarsharedtype` , 
	CHANGE `leftpanelhide` `leftpanelhide` varchar(3)  COLLATE utf8_general_ci NULL after `default_record_view` , 
	CHANGE `rowheight` `rowheight` varchar(10)  COLLATE utf8_general_ci NULL after `leftpanelhide` , 
	CHANGE `defaulteventstatus` `defaulteventstatus` varchar(50)  COLLATE utf8_general_ci NULL DEFAULT 'Planned' after `rowheight` , 
	CHANGE `defaultactivitytype` `defaultactivitytype` varchar(50)  COLLATE utf8_general_ci NULL after `defaulteventstatus` , 
	CHANGE `hidecompletedevents` `hidecompletedevents` int(11)   NULL after `defaultactivitytype` , 
	CHANGE `is_owner` `is_owner` varchar(5)  COLLATE utf8_general_ci NULL after `hidecompletedevents` , 
	ADD UNIQUE KEY `email1`(`email1`) ;
	
	
ALTER TABLE `vtiger_modcomments` 
	CHANGE `modcommentsid` `modcommentsid` int(19)   NOT NULL first , 
	CHANGE `parent_comments` `parent_comments` int(19)   NULL after `related_to` , 
	CHANGE `userid` `userid` int(19)   NULL after `customer` , 
	ADD KEY `modcommentsid`(`modcommentsid`) , 
	ADD KEY `parent_comments`(`parent_comments`) , 
	ADD PRIMARY KEY(`modcommentsid`) , 
	ADD KEY `related_to`(`related_to`,`parent_comments`) , 
	ADD KEY `userid`(`userid`) ;
ALTER TABLE `vtiger_modcomments`
	ADD CONSTRAINT `vtiger_modcomments_ibfk_1` 
	FOREIGN KEY (`related_to`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_modcommentscf`
	ADD CONSTRAINT `vtiger_modcommentscf_ibfk_1` 
	FOREIGN KEY (`modcommentsid`) REFERENCES `vtiger_modcomments` (`modcommentsid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_modtracker_basic` 
	ADD KEY `crmid`(`crmid`,`changedon`) , 
	ADD KEY `id`(`id`,`module`,`changedon`) ;
	
ALTER TABLE `vtiger_notes`
	ADD CONSTRAINT `fk_1_vtiger_notes` 
	FOREIGN KEY (`notesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_notescf`
	ADD CONSTRAINT `vtiger_notescf_ibfk_1` 
	FOREIGN KEY (`notesid`) REFERENCES `vtiger_notes` (`notesid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_org_share_action2tab` 
	ADD KEY `fk_2_vtiger_org_share_action2tab`(`tabid`) ;
ALTER TABLE `vtiger_org_share_action2tab`
	ADD CONSTRAINT `fk_2_vtiger_org_share_action2tab` 
	FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_potential` 
	CHANGE `currency` `currency` varchar(20)  COLLATE utf8_general_ci NULL after `potentialname` , 
	CHANGE `closingdate` `closingdate` date   NULL after `currency` , 
	CHANGE `typeofrevenue` `typeofrevenue` varchar(50)  COLLATE utf8_general_ci NULL after `closingdate` , 
	CHANGE `private` `private` int(1)   NULL DEFAULT 0 after `typeofrevenue` , 
	CHANGE `campaignid` `campaignid` int(19)   NULL after `private` , 
	CHANGE `sales_stage` `sales_stage` varchar(200)  COLLATE utf8_general_ci NULL after `campaignid` , 
	CHANGE `potentialtype` `potentialtype` varchar(200)  COLLATE utf8_general_ci NULL after `sales_stage` , 
	CHANGE `leadsource` `leadsource` varchar(200)  COLLATE utf8_general_ci NULL after `potentialtype` , 
	CHANGE `productid` `productid` int(50)   NULL after `leadsource` , 
	CHANGE `productversion` `productversion` varchar(50)  COLLATE utf8_general_ci NULL after `productid` , 
	CHANGE `quotationref` `quotationref` varchar(50)  COLLATE utf8_general_ci NULL after `productversion` , 
	CHANGE `partnercontact` `partnercontact` varchar(50)  COLLATE utf8_general_ci NULL after `quotationref` , 
	CHANGE `remarks` `remarks` varchar(50)  COLLATE utf8_general_ci NULL after `partnercontact` , 
	CHANGE `runtimefee` `runtimefee` int(19)   NULL DEFAULT 0 after `remarks` , 
	CHANGE `followupdate` `followupdate` date   NULL after `runtimefee` , 
	CHANGE `evaluationstatus` `evaluationstatus` varchar(50)  COLLATE utf8_general_ci NULL after `followupdate` , 
	CHANGE `description` `description` text  COLLATE utf8_general_ci NULL after `evaluationstatus` , 
	CHANGE `forecastcategory` `forecastcategory` int(19)   NULL DEFAULT 0 after `description` , 
	CHANGE `outcomeanalysis` `outcomeanalysis` int(19)   NULL DEFAULT 0 after `forecastcategory` , 
	CHANGE `forecast_amount` `forecast_amount` decimal(25,8)   NULL after `outcomeanalysis` , 
	CHANGE `isconvertedfromlead` `isconvertedfromlead` varchar(3)  COLLATE utf8_general_ci NULL DEFAULT '0' after `forecast_amount` , 
	CHANGE `contact_id` `contact_id` int(19)   NULL after `isconvertedfromlead` , 
	ADD COLUMN `sum_time` decimal(13,2)   NULL DEFAULT 0.00 after `contact_id` , 
	ADD COLUMN `sum_time_so` decimal(13,2)   NULL DEFAULT 0.00 after `sum_time` , 
	ADD COLUMN `sum_time_q` decimal(13,2)   NULL DEFAULT 0.00 after `sum_time_so` , 
	ADD COLUMN `sum_time_all` decimal(13,2)   NULL DEFAULT 0.00 after `sum_time_q` , 
	ADD COLUMN `sum_time_k` decimal(13,2)   NULL DEFAULT 0.00 after `sum_time_all` , 
	ADD COLUMN `sum_quotes` decimal(25,8)   NULL DEFAULT 0.00000000 after `sum_time_k` , 
	ADD COLUMN `sum_salesorders` decimal(25,8)   NULL DEFAULT 0.00000000 after `sum_quotes` , 
	ADD COLUMN `sum_invoices` decimal(25,8)   NULL DEFAULT 0.00000000 after `sum_salesorders` , 
	ADD COLUMN `sum_calculations` decimal(25,8)   NULL DEFAULT 0.00000000 after `sum_invoices` , 
	ADD COLUMN `average_profit_so` decimal(5,2)   NULL after `sum_calculations` , 
	ADD KEY `campaignid`(`campaignid`) , 
	DROP KEY `potentail_sales_stage_amount_idx`, ADD KEY `potentail_sales_stage_amount_idx`(`sales_stage`) , 
	ADD KEY `productid`(`productid`) , 
	ADD KEY `sum_calculations`(`sum_calculations`) , 
	ADD KEY `sum_invoices`(`sum_invoices`) , 
	ADD KEY `sum_quotes`(`sum_quotes`) , 
	ADD KEY `sum_salesorders`(`sum_salesorders`) ;
ALTER TABLE `vtiger_potential`
	ADD CONSTRAINT `fk_1_vtiger_potential` 
	FOREIGN KEY (`potentialid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;

	
ALTER TABLE `vtiger_potentialscf`
	ADD CONSTRAINT `fk_1_vtiger_potentialscf` 
	FOREIGN KEY (`potentialid`) REFERENCES `vtiger_potential` (`potentialid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_potstagehistory`
	ADD CONSTRAINT `fk_1_vtiger_potstagehistory` 
	FOREIGN KEY (`potentialid`) REFERENCES `vtiger_potential` (`potentialid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_pricebook`
	ADD CONSTRAINT `fk_1_vtiger_pricebook` 
	FOREIGN KEY (`pricebookid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_pricebookcf`
	ADD CONSTRAINT `fk_1_vtiger_pricebookcf` 
	FOREIGN KEY (`pricebookid`) REFERENCES `vtiger_pricebook` (`pricebookid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_pricebookproductrel`
	ADD CONSTRAINT `fk_1_vtiger_pricebookproductrel` 
	FOREIGN KEY (`pricebookid`) REFERENCES `vtiger_pricebook` (`pricebookid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_productcf`
	ADD CONSTRAINT `fk_1_vtiger_productcf` 
	FOREIGN KEY (`productid`) REFERENCES `vtiger_products` (`productid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_productcurrencyrel` 
	ADD KEY `productid`(`productid`) ;
	
ALTER TABLE `vtiger_profile2field`
	ADD CONSTRAINT `vtiger_profile2field_ibfk_1` 
	FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_profile2globalpermissions`
	ADD CONSTRAINT `fk_1_vtiger_profile2globalpermissions` 
	FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_profile2tab`
	ADD CONSTRAINT `vtiger_profile2tab_ibfk_1` 
	FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_profile2utility`
	ADD CONSTRAINT `vtiger_profile2utility_ibfk_1` 
	FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE ;
	


ALTER TABLE `vtiger_projectmilestone` 
	CHANGE `projectmilestoneid` `projectmilestoneid` int(19)   NOT NULL first , 
	CHANGE `projectid` `projectid` int(19)   NULL after `projectmilestonedate` , 
	ADD KEY `projectid`(`projectid`) ;
ALTER TABLE `vtiger_projectmilestone`
	ADD CONSTRAINT `vtiger_projectmilestone_ibfk_1` 
	FOREIGN KEY (`projectmilestoneid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_projectmilestonecf` 
	CHANGE `projectmilestoneid` `projectmilestoneid` int(19)   NOT NULL DEFAULT 0 first ;
ALTER TABLE `vtiger_projectmilestonecf`
	ADD CONSTRAINT `vtiger_projectmilestonecf_ibfk_1` 
	FOREIGN KEY (`projectmilestoneid`) REFERENCES `vtiger_projectmilestone` (`projectmilestoneid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_projecttaskcf` 
	CHANGE `projecttaskid` `projecttaskid` int(19)   NOT NULL DEFAULT 0 first ;
ALTER TABLE `vtiger_projecttaskcf`
	ADD CONSTRAINT `vtiger_projecttaskcf_ibfk_1` 
	FOREIGN KEY (`projecttaskid`) REFERENCES `vtiger_projecttask` (`projecttaskid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_purchaseordercf`
	ADD CONSTRAINT `fk_1_vtiger_purchaseordercf` 
	FOREIGN KEY (`purchaseorderid`) REFERENCES `vtiger_purchaseorder` (`purchaseorderid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_quotescf`
	ADD CONSTRAINT `fk_1_vtiger_quotescf` 
	FOREIGN KEY (`quoteid`) REFERENCES `vtiger_quotes` (`quoteid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_quotestagehistory`
	ADD CONSTRAINT `fk_1_vtiger_quotestagehistory` 
	FOREIGN KEY (`quoteid`) REFERENCES `vtiger_quotes` (`quoteid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_recurringevents` 
	ADD KEY `fk_1_vtiger_recurringevents`(`activityid`) ;
ALTER TABLE `vtiger_recurringevents`
	ADD CONSTRAINT `fk_1_vtiger_recurringevents` 
	FOREIGN KEY (`activityid`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_relatedlists` 
	ADD KEY `related_tabid`(`related_tabid`) , 
	ADD KEY `tabid`(`tabid`) , 
	ADD KEY `tabid_2`(`tabid`,`related_tabid`) , 
	ADD KEY `tabid_3`(`tabid`,`related_tabid`,`label`) , 
	ADD KEY `tabid_4`(`tabid`,`related_tabid`,`presence`) ;

ALTER TABLE `vtiger_relcriteria`
	ADD CONSTRAINT `fk_1_vtiger_relcriteria` 
	FOREIGN KEY (`queryid`) REFERENCES `vtiger_selectquery` (`queryid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_relcriteria_grouping` 
	ADD KEY `queryid`(`queryid`) ;
ALTER TABLE `vtiger_relcriteria_grouping`
	ADD CONSTRAINT `vtiger_relcriteria_grouping_ibfk_1` 
	FOREIGN KEY (`queryid`) REFERENCES `vtiger_relcriteria` (`queryid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_report`
	ADD CONSTRAINT `fk_2_vtiger_report` 
	FOREIGN KEY (`queryid`) REFERENCES `vtiger_selectquery` (`queryid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_reportdatefilter`
	ADD CONSTRAINT `fk_1_vtiger_reportdatefilter` 
	FOREIGN KEY (`datefilterid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_reportgroupbycolumn` 
	ADD KEY `fk_1_vtiger_reportgroupbycolumn`(`reportid`) ;
ALTER TABLE `vtiger_reportgroupbycolumn`
	ADD CONSTRAINT `fk_1_vtiger_reportgroupbycolumn` 
	FOREIGN KEY (`reportid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_reportmodules`
	ADD CONSTRAINT `fk_1_vtiger_reportmodules` 
	FOREIGN KEY (`reportmodulesid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_reportsortcol` 
	ADD KEY `fk_1_vtiger_reportsortcol`(`reportid`) ;
ALTER TABLE `vtiger_reportsortcol`
	ADD CONSTRAINT `fk_1_vtiger_reportsortcol` 
	FOREIGN KEY (`reportid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_reportsummary`
	ADD CONSTRAINT `fk_1_vtiger_reportsummary` 
	FOREIGN KEY (`reportsummaryid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_role` 
	ADD KEY `parentrole`(`parentrole`) , 
	ADD KEY `parentrole_2`(`parentrole`,`depth`) , 
	ADD KEY `roleid`(`roleid`) ;

ALTER TABLE `vtiger_role2picklist` 
	ADD KEY `fk_2_vtiger_role2picklist`(`picklistid`) ;
ALTER TABLE `vtiger_role2picklist`
	ADD CONSTRAINT `fk_1_vtiger_role2picklist` 
	FOREIGN KEY (`roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE , 
	ADD CONSTRAINT `fk_2_vtiger_role2picklist` 
	FOREIGN KEY (`picklistid`) REFERENCES `vtiger_picklist` (`picklistid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_role2profile` 
	ADD KEY `profileid`(`profileid`) , 
	ADD KEY `roleid`(`roleid`) ;
ALTER TABLE `vtiger_role2profile`
	ADD CONSTRAINT `vtiger_role2profile_ibfk_1` 
	FOREIGN KEY (`roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE , 
	ADD CONSTRAINT `vtiger_role2profile_ibfk_2` 
	FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_salesmanactivityrel`
	ADD CONSTRAINT `fk_2_vtiger_salesmanactivityrel` 
	FOREIGN KEY (`smid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_salesmanattachmentsrel`
	ADD CONSTRAINT `fk_2_vtiger_salesmanattachmentsrel` 
	FOREIGN KEY (`attachmentsid`) REFERENCES `vtiger_attachments` (`attachmentsid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_salesmanticketrel`
	ADD CONSTRAINT `fk_2_vtiger_salesmanticketrel` 
	FOREIGN KEY (`smid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_salesordercf`
	ADD CONSTRAINT `fk_1_vtiger_salesordercf` 
	FOREIGN KEY (`salesorderid`) REFERENCES `vtiger_salesorder` (`salesorderid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_scheduled_reports`
	ADD CONSTRAINT `vtiger_scheduled_reports_ibfk_1` 
	FOREIGN KEY (`reportid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_schedulereports` 
	ADD KEY `reportid`(`reportid`) ;
ALTER TABLE `vtiger_schedulereports`
	ADD CONSTRAINT `vtiger_schedulereports_ibfk_1` 
	FOREIGN KEY (`reportid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_seactivityrel`
	ADD CONSTRAINT `fk_2_vtiger_seactivityrel` 
	FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_selectcolumn`
	ADD CONSTRAINT `fk_1_vtiger_selectcolumn` 
	FOREIGN KEY (`queryid`) REFERENCES `vtiger_selectquery` (`queryid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_senotesrel`
	ADD CONSTRAINT `fk_2_vtiger_senotesrel` 
	FOREIGN KEY (`notesid`) REFERENCES `vtiger_notes` (`notesid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_seproductsrel`
	ADD CONSTRAINT `fk_2_vtiger_seproductsrel` 
	FOREIGN KEY (`productid`) REFERENCES `vtiger_products` (`productid`) ON DELETE CASCADE ;

	ALTER TABLE `vtiger_servicecf`
	ADD CONSTRAINT `vtiger_servicecf_ibfk_1` 
	FOREIGN KEY (`serviceid`) REFERENCES `vtiger_service` (`serviceid`) ON DELETE CASCADE ;
	

ALTER TABLE `vtiger_seticketsrel`
	ADD CONSTRAINT `fk_2_vtiger_seticketsrel` 
	FOREIGN KEY (`ticketid`) REFERENCES `vtiger_troubletickets` (`ticketid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_settings_field` 
	ADD KEY `fk_1_vtiger_settings_field`(`blockid`) ;
ALTER TABLE `vtiger_settings_field`
	ADD CONSTRAINT `fk_1_vtiger_settings_field` 
	FOREIGN KEY (`blockid`) REFERENCES `vtiger_settings_blocks` (`blockid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_smsnotifier` 
	CHANGE `smsnotifierid` `smsnotifierid` int(19)   NOT NULL first , 
	ADD PRIMARY KEY(`smsnotifierid`) ;
ALTER TABLE `vtiger_smsnotifier`
	ADD CONSTRAINT `vtiger_smsnotifier_ibfk_1` 
	FOREIGN KEY (`smsnotifierid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_smsnotifiercf` 
	CHANGE `smsnotifierid` `smsnotifierid` int(19)   NOT NULL first ;
ALTER TABLE `vtiger_smsnotifiercf`
	ADD CONSTRAINT `vtiger_smsnotifiercf_ibfk_1` 
	FOREIGN KEY (`smsnotifierid`) REFERENCES `vtiger_smsnotifier` (`smsnotifierid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_soapservice` 
	ADD COLUMN `lang` varchar(10)  COLLATE utf8_general_ci NULL after `sessionid` , 
	ADD KEY `id`(`id`,`type`) ;

ALTER TABLE `vtiger_sostatushistory`
	ADD CONSTRAINT `fk_1_vtiger_sostatushistory` 
	FOREIGN KEY (`salesorderid`) REFERENCES `vtiger_salesorder` (`salesorderid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_tab` 
	CHANGE `version` `version` varchar(10)  COLLATE utf8_general_ci NULL after `isentitytype` , 
	CHANGE `trial` `trial` int(1)   NULL DEFAULT 0 after `parent` ;

ALTER TABLE `vtiger_tab_info` 
	ADD KEY `fk_1_vtiger_tab_info`(`tabid`) ;
ALTER TABLE `vtiger_tab_info`
	ADD CONSTRAINT `fk_1_vtiger_tab_info` 
	FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `vtiger_ticketcf`
	ADD CONSTRAINT `fk_1_vtiger_ticketcf` 
	FOREIGN KEY (`ticketid`) REFERENCES `vtiger_troubletickets` (`ticketid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_ticketcomments`
	ADD CONSTRAINT `fk_1_vtiger_ticketcomments` 
	FOREIGN KEY (`ticketid`) REFERENCES `vtiger_troubletickets` (`ticketid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_tmp_read_group_rel_sharing_per`
	ADD CONSTRAINT `fk_4_vtiger_tmp_read_group_rel_sharing_per` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_tmp_read_group_sharing_per`
	ADD CONSTRAINT `fk_3_vtiger_tmp_read_group_sharing_per` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_tmp_read_user_rel_sharing_per`
	ADD CONSTRAINT `fk_4_vtiger_tmp_read_user_rel_sharing_per` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_tmp_read_user_sharing_per`
	ADD CONSTRAINT `fk_3_vtiger_tmp_read_user_sharing_per` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_tmp_write_group_rel_sharing_per`
	ADD CONSTRAINT `fk_4_vtiger_tmp_write_group_rel_sharing_per` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_tmp_write_group_sharing_per`
	ADD CONSTRAINT `fk_3_vtiger_tmp_write_group_sharing_per` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_tmp_write_user_rel_sharing_per`
	ADD CONSTRAINT `fk_4_vtiger_tmp_write_user_rel_sharing_per` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_tmp_write_user_sharing_per`
	ADD CONSTRAINT `fk_3_vtiger_tmp_write_user_sharing_per` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_user2role`
	ADD CONSTRAINT `fk_2_vtiger_user2role` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_user_module_preferences` 
	ADD KEY `fk_2_vtiger_user_module_preferences`(`tabid`) ;
ALTER TABLE `vtiger_user_module_preferences`
	ADD CONSTRAINT `fk_2_vtiger_user_module_preferences` 
	FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ON UPDATE CASCADE ;
	
ALTER TABLE `vtiger_users2group` 
	ADD KEY `fk_2_vtiger_users2group`(`userid`) ;
ALTER TABLE `vtiger_users2group`
	ADD CONSTRAINT `fk_2_vtiger_users2group` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_userscf`
	ADD CONSTRAINT `vtiger_userscf_ibfk_1` 
	FOREIGN KEY (`usersid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_vendorcf`
	ADD CONSTRAINT `fk_1_vtiger_vendorcf` 
	FOREIGN KEY (`vendorid`) REFERENCES `vtiger_vendor` (`vendorid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_vendorcontactrel`
	ADD CONSTRAINT `fk_2_vtiger_vendorcontactrel` 
	FOREIGN KEY (`vendorid`) REFERENCES `vtiger_vendor` (`vendorid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_ws_entity_referencetype`
	ADD CONSTRAINT `vtiger_fk_1_actors_referencetype` 
	FOREIGN KEY (`fieldtypeid`) REFERENCES `vtiger_ws_entity_fieldtype` (`fieldtypeid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_ws_entity_tables`
	ADD CONSTRAINT `fk_1_vtiger_ws_actor_tables` 
	FOREIGN KEY (`webservice_entity_id`) REFERENCES `vtiger_ws_entity` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_ws_referencetype`
	ADD CONSTRAINT `fk_1_vtiger_referencetype` 
	FOREIGN KEY (`fieldtypeid`) REFERENCES `vtiger_ws_fieldtype` (`fieldtypeid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_service` 
	ADD COLUMN `pscategory` varchar(200)  COLLATE utf8_general_ci NULL after `servicename` , 
	CHANGE `qty_per_unit` `qty_per_unit` decimal(11,2)   NULL DEFAULT 0.00 after `pscategory` , 
	DROP COLUMN `servicecategory` ;
ALTER TABLE `vtiger_service` 
	DROP FOREIGN KEY `fk_1_vtiger_service`  ;
ALTER TABLE `vtiger_service` 
	ADD CONSTRAINT `fk_1_vtiger_service` 
	FOREIGN KEY (`serviceid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	



	
ALTER TABLE `vtiger_products`
	ADD CONSTRAINT `fk_1_vtiger_products` 
	FOREIGN KEY (`productid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_portalinfo`
	ADD CONSTRAINT `fk_1_vtiger_portalinfo` 
	FOREIGN KEY (`id`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_postatushistory`
	ADD CONSTRAINT `fk_1_vtiger_postatushistory` 
	FOREIGN KEY (`purchaseorderid`) REFERENCES `vtiger_purchaseorder` (`purchaseorderid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_pbxmanagercf`
	ADD CONSTRAINT `vtiger_pbxmanagercf_ibfk_1` 
	FOREIGN KEY (`pbxmanagerid`) REFERENCES `vtiger_pbxmanager` (`pbxmanagerid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_accountscf`
	ADD CONSTRAINT `fk_1_vtiger_accountscf` 
	FOREIGN KEY (`accountid`) REFERENCES `vtiger_account` (`accountid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_activity` 
	ADD COLUMN `deleted` tinyint(1)   NULL DEFAULT 0 after `recurringtype` , 
	ADD COLUMN `smownerid` int(19)   NULL after `deleted` , 
	ADD KEY `activitytype`(`activitytype`,`date_start`,`due_date`,`time_start`,`time_end`,`eventstatus`,`deleted`,`smownerid`) , 
	ADD KEY `activitytype_2`(`activitytype`,`date_start`,`due_date`,`time_start`,`time_end`,`deleted`,`smownerid`) ;
ALTER TABLE `vtiger_activity`
	ADD CONSTRAINT `fk_1_vtiger_activity` 
	FOREIGN KEY (`activityid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_activity_reminder`
	ADD CONSTRAINT `vtiger_activity_reminder_ibfk_1` 
	FOREIGN KEY (`activity_id`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_activity_reminder_popup` 
	ADD KEY `recordid`(`recordid`) ;
ALTER TABLE `vtiger_activity_reminder_popup`
	ADD CONSTRAINT `vtiger_activity_reminder_popup_ibfk_1` 
	FOREIGN KEY (`recordid`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_activitycf`
	ADD CONSTRAINT `vtiger_activitycf_ibfk_1` 
	FOREIGN KEY (`activityid`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_activityproductrel`
	ADD CONSTRAINT `fk_2_vtiger_activityproductrel` 
	FOREIGN KEY (`productid`) REFERENCES `vtiger_products` (`productid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_attachments`
	ADD CONSTRAINT `fk_1_vtiger_attachments` 
	FOREIGN KEY (`attachmentsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_blocks`
	ADD CONSTRAINT `fk_1_vtiger_blocks` 
	FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_calendar_user_activitytypes` 
	CHANGE `visible` `visible` int(19)   NULL DEFAULT 0 after `color` ;
	
ALTER TABLE `vtiger_campaigncontrel`
	ADD CONSTRAINT `fk_2_vtiger_campaigncontrel` 
	FOREIGN KEY (`contactid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_campaignleadrel`
	ADD CONSTRAINT `fk_2_vtiger_campaignleadrel` 
	FOREIGN KEY (`leadid`) REFERENCES `vtiger_leaddetails` (`leadid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_campaignscf`
	ADD CONSTRAINT `fk_1_vtiger_campaignscf` 
	FOREIGN KEY (`campaignid`) REFERENCES `vtiger_campaign` (`campaignid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_cntactivityrel`
	ADD CONSTRAINT `fk_2_vtiger_cntactivityrel` 
	FOREIGN KEY (`contactid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_assets` 
	CHANGE `product` `product` int(19)   NOT NULL after `asset_no` , 
	CHANGE `assetname` `assetname` varchar(100)  COLLATE utf8_general_ci NULL after `shippingmethod` , 
	ADD COLUMN `sum_time` decimal(10,2)   NULL DEFAULT 0.00 after `contact` , 
	ADD COLUMN `potential` int(19)   NULL after `sum_time` , 
	ADD COLUMN `parent_id` int(19)   NULL after `potential` , 
	ADD COLUMN `pot_renewal` int(19)   NULL after `parent_id` , 
	ADD KEY `contact`(`contact`) , 
	ADD KEY `invoiceid`(`invoiceid`) , 
	ADD KEY `parent_id`(`parent_id`) , 
	ADD KEY `pot_renewal`(`pot_renewal`) , 
	ADD KEY `potential`(`potential`) , 
	ADD KEY `product`(`product`) ;
ALTER TABLE `vtiger_assets` 
	DROP FOREIGN KEY `fk_1_vtiger_assets`  ;
	
ALTER TABLE `vtiger_assets` 
	ADD CONSTRAINT `fk_1_vtiger_assets` 
	FOREIGN KEY (`assetsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_assetscf`
	ADD CONSTRAINT `vtiger_assetscf_ibfk_1` 
	FOREIGN KEY (`assetsid`) REFERENCES `vtiger_assets` (`assetsid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_contactdetails` 
	ADD COLUMN `parentid` int(19)   NULL after `contact_no` , 
	CHANGE `salutation` `salutation` varchar(200)  COLLATE utf8_general_ci NULL after `parentid` , 
	CHANGE `reportsto` `reportsto` varchar(30)  COLLATE utf8_general_ci NULL after `mobile` , 
	CHANGE `training` `training` varchar(50)  COLLATE utf8_general_ci NULL after `reportsto` , 
	CHANGE `usertype` `usertype` varchar(50)  COLLATE utf8_general_ci NULL after `training` , 
	CHANGE `contacttype` `contacttype` varchar(50)  COLLATE utf8_general_ci NULL after `usertype` , 
	CHANGE `otheremail` `otheremail` varchar(100)  COLLATE utf8_general_ci NULL after `contacttype` , 
	CHANGE `donotcall` `donotcall` varchar(3)  COLLATE utf8_general_ci NULL after `otheremail` , 
	CHANGE `emailoptout` `emailoptout` varchar(3)  COLLATE utf8_general_ci NULL DEFAULT '0' after `donotcall` , 
	CHANGE `imagename` `imagename` varchar(150)  COLLATE utf8_general_ci NULL after `emailoptout` , 
	CHANGE `isconvertedfromlead` `isconvertedfromlead` varchar(3)  COLLATE utf8_general_ci NULL DEFAULT '0' after `imagename` , 
	ADD COLUMN `verification` text  COLLATE utf8_general_ci NULL after `isconvertedfromlead` , 
	ADD COLUMN `secondary_email` varchar(50)  COLLATE utf8_general_ci NULL DEFAULT '' after `verification` , 
	ADD COLUMN `notifilanguage` varchar(100)  COLLATE utf8_general_ci NULL DEFAULT '' after `secondary_email` , 
	ADD COLUMN `contactstatus` varchar(255)  COLLATE utf8_general_ci NULL DEFAULT '' after `notifilanguage` ,
	DROP KEY `contactdetails_accountid_idx`, ADD KEY `contactdetails_accountid_idx`(`parentid`) ;
ALTER TABLE `vtiger_contactdetails`
	ADD CONSTRAINT `fk_1_vtiger_contactdetails` 
	FOREIGN KEY (`contactid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_contactsubdetails` 
	CHANGE `birthday` `birthday` date   NULL after `contactsubscriptionid` ;
ALTER TABLE `vtiger_contactsubdetails`
	ADD CONSTRAINT `fk_1_vtiger_contactsubdetails` 
	FOREIGN KEY (`contactsubscriptionid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_contpotentialrel`
	ADD CONSTRAINT `fk_2_vtiger_contpotentialrel` 
	FOREIGN KEY (`potentialid`) REFERENCES `vtiger_potential` (`potentialid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_crmentity` 
	ADD COLUMN `shownerid` set('1')  COLLATE utf8_general_ci NULL after `smownerid` , 
	CHANGE `modifiedby` `modifiedby` int(19)   NOT NULL DEFAULT 0 after `shownerid` , 
	CHANGE `setype` `setype` varchar(30)  COLLATE utf8_general_ci NOT NULL after `modifiedby` , 
	CHANGE `description` `description` text  COLLATE utf8_general_ci NULL after `setype` , 
	ADD COLUMN `attention` text  COLLATE utf8_general_ci NULL after `description` , 
	CHANGE `createdtime` `createdtime` datetime   NOT NULL after `attention` , 
	CHANGE `modifiedtime` `modifiedtime` datetime   NOT NULL after `createdtime` , 
	CHANGE `viewedtime` `viewedtime` datetime   NULL after `modifiedtime` , 
	ADD COLUMN `closedtime` datetime   NULL after `viewedtime` , 
	CHANGE `status` `status` varchar(50)  COLLATE utf8_general_ci NULL after `closedtime` , 
	CHANGE `version` `version` int(19)   NOT NULL DEFAULT 0 after `status` , 
	CHANGE `presence` `presence` tinyint(1)   NULL DEFAULT 1 after `version` , 
	CHANGE `deleted` `deleted` tinyint(1)   NOT NULL DEFAULT 0 after `presence` , 
	CHANGE `label` `label` varchar(255)  COLLATE utf8_general_ci NULL after `deleted` , 
	ADD COLUMN `searchlabel` varchar(255)  COLLATE utf8_general_ci NULL after `label` , 
	ADD COLUMN `inheritsharing` tinyint(1)   NULL DEFAULT 0 after `searchlabel` , 
	ADD COLUMN `was_read` tinyint(1)   NULL after `inheritsharing` , 
	ADD KEY `crmid`(`crmid`,`deleted`) , 
	ADD KEY `searchlabel`(`setype`,`label`) , 
	ADD KEY `setype`(`setype`,`deleted`,`searchlabel`) ;
	
ALTER TABLE `vtiger_customaction`
	ADD CONSTRAINT `fk_1_vtiger_customaction` 
	FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE ;

	ALTER TABLE `vtiger_cvadvfilter`
	ADD CONSTRAINT `fk_1_vtiger_cvadvfilter` 
	FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_cvcolumnlist`
	ADD CONSTRAINT `fk_1_vtiger_cvcolumnlist` 
	FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_cvstdfilter`
	ADD CONSTRAINT `fk_1_vtiger_cvstdfilter` 
	FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE ;
ALTER TABLE `vtiger_customerdetails`

	ADD CONSTRAINT `fk_1_vtiger_customerdetails` 
	FOREIGN KEY (`customerid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_customview` 
	ADD COLUMN `privileges` tinyint(2)   NULL DEFAULT 1 after `userid` ;
ALTER TABLE `vtiger_customview`
	ADD CONSTRAINT `fk_1_vtiger_customview` 
	FOREIGN KEY (`entitytype`) REFERENCES `vtiger_tab` (`name`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_grp2grp`
	ADD CONSTRAINT `fk_3_vtiger_datashare_grp2grp` 
	FOREIGN KEY (`to_groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_grp2role`
	ADD CONSTRAINT `fk_3_vtiger_datashare_grp2role` 
	FOREIGN KEY (`to_roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_grp2rs`
	ADD CONSTRAINT `fk_3_vtiger_datashare_grp2rs` 
	FOREIGN KEY (`to_roleandsubid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_module_rel`
	ADD CONSTRAINT `fk_1_vtiger_datashare_module_rel` 
	FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_relatedmodules`
	ADD CONSTRAINT `fk_2_vtiger_datashare_relatedmodules` 
	FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_role2group`
	ADD CONSTRAINT `fk_3_vtiger_datashare_role2group` 
	FOREIGN KEY (`share_roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_role2role`
	ADD CONSTRAINT `fk_3_vtiger_datashare_role2role` 
	FOREIGN KEY (`to_roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_role2rs`
	ADD CONSTRAINT `fk_3_vtiger_datashare_role2rs` 
	FOREIGN KEY (`to_roleandsubid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_rs2grp`
	ADD CONSTRAINT `fk_3_vtiger_datashare_rs2grp` 
	FOREIGN KEY (`share_roleandsubid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_rs2role`
	ADD CONSTRAINT `fk_3_vtiger_datashare_rs2role` 
	FOREIGN KEY (`to_roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_datashare_rs2rs`
	ADD CONSTRAINT `fk_3_vtiger_datashare_rs2rs` 
	FOREIGN KEY (`to_roleandsubid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_def_org_share` 
	ADD KEY `fk_1_vtiger_def_org_share`(`permission`) ;
ALTER TABLE `vtiger_def_org_share`
	ADD CONSTRAINT `fk_1_vtiger_def_org_share` 
	FOREIGN KEY (`permission`) REFERENCES `vtiger_org_share_action_mapping` (`share_action_id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_defaultcv`
	ADD CONSTRAINT `fk_1_vtiger_defaultcv` 
	FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_faq`
	ADD CONSTRAINT `fk_1_vtiger_faq` 
	FOREIGN KEY (`id`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_faqcf`
	ADD CONSTRAINT `fk_1_vtiger_faqcf` 
	FOREIGN KEY (`faqid`) REFERENCES `vtiger_faq` (`id`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_faqcomments`
	ADD CONSTRAINT `fk_1_vtiger_faqcomments` 
	FOREIGN KEY (`faqid`) REFERENCES `vtiger_faq` (`id`) ON DELETE CASCADE ;

	
ALTER TABLE `vtiger_field` 
	CHANGE `helpinfo` `helpinfo` tinyint(1)   NULL DEFAULT 0 after `masseditable` , 
	ADD KEY `tabid`(`tabid`,`tablename`) ;
ALTER TABLE `vtiger_field`
	ADD CONSTRAINT `fk_1_vtiger_field` 
	FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_freetagged_objects` 
	ADD KEY `object_id`(`object_id`) ;
ALTER TABLE `vtiger_freetagged_objects`
	ADD CONSTRAINT `vtiger_freetagged_objects_ibfk_1` 
	FOREIGN KEY (`object_id`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;


/* Alter table in target */
ALTER TABLE `vtiger_group2grouprel`
	ADD CONSTRAINT `fk_2_vtiger_group2grouprel` 
	FOREIGN KEY (`groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE ON UPDATE CASCADE ;


/* Alter table in target */
ALTER TABLE `vtiger_group2role` 
	ADD KEY `fk_2_vtiger_group2role`(`roleid`) ;
ALTER TABLE `vtiger_group2role`
	ADD CONSTRAINT `fk_2_vtiger_group2role` 
	FOREIGN KEY (`roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE ;


/* Alter table in target */
ALTER TABLE `vtiger_group2rs` 
	ADD KEY `fk_2_vtiger_group2rs`(`roleandsubid`) ;
ALTER TABLE `vtiger_group2rs`
	ADD CONSTRAINT `fk_2_vtiger_group2rs` 
	FOREIGN KEY (`roleandsubid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_homedashbd`
	ADD CONSTRAINT `fk_1_vtiger_homedashbd` 
	FOREIGN KEY (`stuffid`) REFERENCES `vtiger_homestuff` (`stuffid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_homedefault`
	ADD CONSTRAINT `fk_1_vtiger_homedefault` 
	FOREIGN KEY (`stuffid`) REFERENCES `vtiger_homestuff` (`stuffid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_homemodule`
	ADD CONSTRAINT `fk_1_vtiger_homemodule` 
	FOREIGN KEY (`stuffid`) REFERENCES `vtiger_homestuff` (`stuffid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_homemoduleflds`
	ADD CONSTRAINT `fk_1_vtiger_homemoduleflds` 
	FOREIGN KEY (`stuffid`) REFERENCES `vtiger_homemodule` (`stuffid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_homerss`
	ADD CONSTRAINT `fk_1_vtiger_homerss` 
	FOREIGN KEY (`stuffid`) REFERENCES `vtiger_homestuff` (`stuffid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_homestuff` 
	ADD KEY `fk_1_vtiger_homestuff`(`userid`) ;
ALTER TABLE `vtiger_homestuff`
	ADD CONSTRAINT `fk_1_vtiger_homestuff` 
	FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_import_queue` 
	ADD COLUMN `temp_status` int(11)   NULL DEFAULT 0 after `merge_fields` , 
	DROP COLUMN `status` ;
	
ALTER TABLE `vtiger_inventoryproductrel` 
	ADD COLUMN `tax` varchar(10)  COLLATE utf8_general_ci NULL after `lineitem_id` , 
	CHANGE `tax1` `tax1` decimal(7,3)   NULL after `tax` , 
	ADD COLUMN `calculationsid` int(19)   NULL after `tax3` , 
	ADD COLUMN `purchase` decimal(10,2)   NULL after `calculationsid` , 
	ADD COLUMN `margin` decimal(10,2)   NULL after `purchase` , 
	ADD COLUMN `marginp` decimal(10,2)   NULL after `margin` ;

ALTER TABLE `vtiger_invitees`
	ADD CONSTRAINT `vtiger_invitees_ibfk_1` 
	FOREIGN KEY (`activityid`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_invoice` 
	CHANGE `salescommission` `salescommission` decimal(25,3)   NULL after `type` , 
	CHANGE `shipping` `shipping` varchar(100)  COLLATE utf8_general_ci NULL after `discount_amount` , 
	ADD COLUMN `total_purchase` decimal(13,2)   NULL after `balance` , 
	ADD COLUMN `total_margin` decimal(13,2)   NULL after `total_purchase` , 
	ADD COLUMN `total_marginp` decimal(13,2)   NULL after `total_margin` , 
	ADD COLUMN `potentialid` int(19)   NULL after `total_marginp` , 
	ADD COLUMN `form_payment` varchar(255)  COLLATE utf8_general_ci NULL DEFAULT '' after `potentialid` , 
	ADD KEY `accountid`(`accountid`) , 
	ADD KEY `contactid`(`contactid`) , 
	ADD KEY `fk_2_vtiger_invoice`(`salesorderid`) , 
	ADD KEY `potentialid`(`potentialid`) ;
ALTER TABLE `vtiger_invoice`
	ADD CONSTRAINT `fk_2_vtiger_invoice` 
	FOREIGN KEY (`salesorderid`) REFERENCES `vtiger_salesorder` (`salesorderid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_invoicecf`
	ADD CONSTRAINT `fk_1_vtiger_invoicecf` 
	FOREIGN KEY (`invoiceid`) REFERENCES `vtiger_invoice` (`invoiceid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_entityname` 
	ADD COLUMN `searchcolumn` varchar(150)  COLLATE utf8_general_ci NOT NULL after `entityidcolumn` , 
	ADD COLUMN `turn_off` int(1)   NOT NULL DEFAULT 1 after `searchcolumn` , 
	ADD COLUMN `sequence` int(19)   NOT NULL after `turn_off` ;
ALTER TABLE `vtiger_entityname`
	ADD CONSTRAINT `fk_1_vtiger_entityname` 
	FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ;


ALTER TABLE `vtiger_invoicestatushistory`
	ADD CONSTRAINT `fk_1_vtiger_invoicestatushistory` 
	FOREIGN KEY (`invoiceid`) REFERENCES `vtiger_invoice` (`invoiceid`) ON DELETE CASCADE ;

ALTER TABLE `vtiger_language` 
	CHANGE `isdefault` `isdefault` int(1)   NULL DEFAULT 0 after `sequence` , 
	CHANGE `active` `active` int(1)   NULL DEFAULT 1 after `isdefault` ;
	
	
-- UPDATE vtiger_settings_blocks SET `label` = 'LBL_COMPANY' WHERE `label` = 'LBL_COMMUNICATION_TEMPLATES' ;
	
ALTER TABLE `vtiger_leaddetails` 
	CHANGE `leadstatus` `leadstatus` varchar(50)  COLLATE utf8_general_ci NULL after `campaign` , 
	CHANGE `licencekeystatus` `licencekeystatus` varchar(50)  COLLATE utf8_general_ci NULL after `converted` , 
	ADD COLUMN `noapprovalcalls` varchar(3)  COLLATE utf8_general_ci NULL after `emailoptout` , 
	ADD COLUMN `noapprovalemails` varchar(3)  COLLATE utf8_general_ci NULL after `noapprovalcalls` , 
	ADD COLUMN `vat_id` varchar(30)  COLLATE utf8_general_ci NULL after `noapprovalemails` , 
	ADD COLUMN `registration_number_1` varchar(30)  COLLATE utf8_general_ci NULL after `vat_id` , 
	ADD COLUMN `registration_number_2` varchar(30)  COLLATE utf8_general_ci NULL after `registration_number_1` , 
	ADD COLUMN `verification` text  COLLATE utf8_general_ci NULL after `registration_number_2` , 
	ADD COLUMN `subindustry` varchar(255)  COLLATE utf8_general_ci NULL DEFAULT '' after `verification` , 
	ADD COLUMN `atenttion` text  COLLATE utf8_general_ci NULL after `subindustry` ; 
ALTER TABLE `vtiger_leaddetails`
	ADD CONSTRAINT `fk_1_vtiger_leaddetails` 
	FOREIGN KEY (`leadid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_notes` 
	CHANGE `folderid` `folderid` varchar(255)  COLLATE utf8_general_ci NOT NULL after `notecontent` , 
	ADD COLUMN `ossdc_status` varchar(255)  COLLATE utf8_general_ci NULL after `fileversion` ;
	
ALTER TABLE `vtiger_pbxmanager` 
	CHANGE `sourceuuid` `sourceuuid` int(19)   NULL after `recordingurl` ;
ALTER TABLE `vtiger_pbxmanager`
	ADD CONSTRAINT `vtiger_pbxmanager_ibfk_1` 
	FOREIGN KEY (`pbxmanagerid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_products` 
	CHANGE `productid` `productid` int(19)   NOT NULL first , 
	ADD COLUMN `pscategory` varchar(200)  COLLATE utf8_general_ci NULL after `productcode` , 
	CHANGE `manufacturer` `manufacturer` varchar(200)  COLLATE utf8_general_ci NULL after `pscategory`; 
	
ALTER TABLE `vtiger_project` 
	CHANGE `projectid` `projectid` int(19)   NOT NULL first , 
	CHANGE `linktoaccountscontacts` `linktoaccountscontacts` int(19)   NULL after `progress` , 
	ADD COLUMN `sum_time` decimal(10,2)   NULL DEFAULT 0.00 after `linktoaccountscontacts` , 
	ADD COLUMN `sum_time_pt` decimal(10,2)   NULL DEFAULT 0.00 after `sum_time` , 
	ADD COLUMN `sum_time_h` decimal(10,2)   NULL DEFAULT 0.00 after `sum_time_pt` , 
	ADD COLUMN `sum_time_all` decimal(10,2)   NULL DEFAULT 0.00 after `sum_time_h` , 
	ADD COLUMN `servicecontractsid` int(19)   NULL after `sum_time_all` , 
	ADD KEY `linktoaccountscontacts`(`linktoaccountscontacts`) , 
	ADD PRIMARY KEY(`projectid`) , 
	ADD KEY `servicecontractsid`(`servicecontractsid`) ;
ALTER TABLE `vtiger_project`
	ADD CONSTRAINT `vtiger_project_ibfk_1` 
	FOREIGN KEY (`projectid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_projectcf` 
	CHANGE `projectid` `projectid` int(19)   NOT NULL first ;
ALTER TABLE `vtiger_projectcf`
	ADD CONSTRAINT `vtiger_projectcf_ibfk_1` 
	FOREIGN KEY (`projectid`) REFERENCES `vtiger_project` (`projectid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_projecttask` 
	CHANGE `projecttaskid` `projecttaskid` int(19)   NOT NULL first , 
	CHANGE `startdate` `startdate` date   NULL after `projecttaskprogress` , 
	CHANGE `projectid` `projectid` int(19)   NULL after `enddate` , 
	CHANGE `projecttasknumber` `projecttasknumber` int(10)   NULL after `projectid` , 
	ADD COLUMN `sum_time` decimal(10,2)   NULL DEFAULT 0.00 after `projecttaskstatus` , 
	ADD COLUMN `parentid` int(19)   NULL after `sum_time` , 
	ADD COLUMN `projectmilestoneid` int(19)   NULL after `parentid` , 
	ADD COLUMN `targetenddate` date   NULL after `projectmilestoneid` , 
	DROP COLUMN `projecttaskhours` , 
	ADD KEY `parentid`(`parentid`) , 
	ADD KEY `projectid`(`projectid`) , 
	ADD KEY `projectmilestoneid`(`projectmilestoneid`) ;
ALTER TABLE `vtiger_projecttask`
	ADD CONSTRAINT `vtiger_projecttask_ibfk_1` 
	FOREIGN KEY (`projecttaskid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_purchaseorder` 
	CHANGE `salescommission` `salescommission` decimal(25,3)   NULL after `type` , 
	CHANGE `terms_conditions` `terms_conditions` text  COLLATE utf8_general_ci NULL after `discount_amount` , 
	ADD COLUMN `total_purchase` decimal(13,2)   NULL after `balance` , 
	ADD COLUMN `total_margin` decimal(13,2)   NULL after `total_purchase` , 
	ADD COLUMN `total_marginp` decimal(13,2)   NULL after `total_margin` ; 
ALTER TABLE `vtiger_purchaseorder`
	ADD CONSTRAINT `vtiger_purchaseorder_ibfk_1` 
	FOREIGN KEY (`purchaseorderid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_quotes` 
	CHANGE `type` `type` varchar(100)  COLLATE utf8_general_ci NULL after `shipping` , 
	CHANGE `total` `total` decimal(25,8)   NULL after `type` , 
	CHANGE `taxtype` `taxtype` varchar(25)  COLLATE utf8_general_ci NULL after `total` , 
	CHANGE `discount_percent` `discount_percent` decimal(25,3)   NULL after `taxtype` , 
	CHANGE `discount_amount` `discount_amount` decimal(25,8)   NULL after `discount_percent` , 
	CHANGE `accountid` `accountid` int(19)   NULL after `discount_amount` , 
	CHANGE `terms_conditions` `terms_conditions` text  COLLATE utf8_general_ci NULL after `accountid` , 
	CHANGE `currency_id` `currency_id` int(19)   NOT NULL DEFAULT 1 after `terms_conditions` , 
	CHANGE `conversion_rate` `conversion_rate` decimal(10,3)   NOT NULL DEFAULT 1.000 after `currency_id` , 
	CHANGE `pre_tax_total` `pre_tax_total` decimal(25,8)   NULL after `conversion_rate` , 
	ADD COLUMN `sum_time` decimal(10,2)   NULL DEFAULT 0.00 after `pre_tax_total` , 
	ADD COLUMN `total_purchase` decimal(13,2)   NULL after `sum_time` , 
	ADD COLUMN `total_margin` decimal(13,2)   NULL after `total_purchase` , 
	ADD COLUMN `total_marginp` decimal(13,2)   NULL after `total_margin` , 
	ADD COLUMN `form_payment` varchar(255)  COLLATE utf8_general_ci NULL DEFAULT '' after `total_marginp` , 
	ADD KEY `accountid`(`accountid`) ;
ALTER TABLE `vtiger_quotes`
	ADD CONSTRAINT `vtiger_quotes_ibfk_1` 
	FOREIGN KEY (`quoteid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_salesorder` 
	CHANGE `salescommission` `salescommission` decimal(25,3)   NULL after `type` , 
	CHANGE `accountid` `accountid` int(19)   NULL after `discount_amount` , 
	ADD COLUMN `sum_time` decimal(10,2)   NULL DEFAULT 0.00 after `pre_tax_total` , 
	ADD COLUMN `total_purchase` decimal(13,2)   NULL after `sum_time` , 
	ADD COLUMN `total_margin` decimal(13,2)   NULL after `total_purchase` , 
	ADD COLUMN `total_marginp` decimal(13,2)   NULL after `total_margin` , 
	ADD COLUMN `form_payment` varchar(255)  COLLATE utf8_general_ci NULL DEFAULT '' after `total_marginp` , 
	ADD KEY `accountid`(`accountid`) , 
	ADD KEY `potentialid`(`potentialid`,`sostatus`) , 
	ADD KEY `quoteid`(`quoteid`) , 
	ADD KEY `sostatus`(`sostatus`) ;
ALTER TABLE `vtiger_salesorder`
	ADD CONSTRAINT `vtiger_salesorder_ibfk_1` 
	FOREIGN KEY (`salesorderid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;


	
ALTER TABLE `vtiger_servicecontracts` 
	CHANGE `servicecontractsid` `servicecontractsid` int(19)   NOT NULL first , 
	CHANGE `sc_related_to` `sc_related_to` int(19)   NULL after `end_date` , 
	ADD COLUMN `sum_time` decimal(10,2)   NULL DEFAULT 0.00 after `contract_no` , 
	ADD COLUMN `sum_time_p` decimal(13,2)   NULL after `sum_time` , 
	ADD COLUMN `sum_time_h` decimal(13,2)   NULL after `sum_time_p` , 
	ADD COLUMN `sum_time_all` decimal(13,2)   NULL after `sum_time_h` , 
	ADD PRIMARY KEY(`servicecontractsid`) , 
	ADD KEY `sc_related_to`(`sc_related_to`) ;
ALTER TABLE `vtiger_servicecontracts`
	ADD CONSTRAINT `vtiger_servicecontracts_ibfk_1` 
	FOREIGN KEY (`servicecontractsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_servicecontractscf` 
	CHANGE `servicecontractsid` `servicecontractsid` int(19)   NOT NULL first ;
ALTER TABLE `vtiger_servicecontractscf`
	ADD CONSTRAINT `vtiger_servicecontractscf_ibfk_1` 
	FOREIGN KEY (`servicecontractsid`) REFERENCES `vtiger_servicecontracts` (`servicecontractsid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_troubletickets` 
	CHANGE `parent_id` `parent_id` int(19)   NULL after `groupname` , 
	CHANGE `product_id` `product_id` int(19)   NULL after `parent_id` , 
	CHANGE `contact_id` `contact_id` int(19)   NULL after `version_id` , 
	ADD COLUMN `sum_time` decimal(10,2)   NULL DEFAULT 0.00 after `contact_id` , 
	ADD COLUMN `projectid` int(19)   NULL after `sum_time` , 
	ADD COLUMN `servicecontractsid` int(19)   NULL after `projectid` , 
	ADD COLUMN `attention` text  COLLATE utf8_general_ci NULL after `servicecontractsid` , 
	ADD KEY `contact_id`(`contact_id`) , 
	ADD KEY `parent_id`(`parent_id`) , 
	ADD KEY `product_id`(`product_id`) , 
	ADD KEY `projectid`(`projectid`) , 
	ADD KEY `servicecontractsid`(`servicecontractsid`) ;
ALTER TABLE `vtiger_troubletickets`
	ADD CONSTRAINT `fk_1_vtiger_troubletickets` 
	FOREIGN KEY (`ticketid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_vendor` 
	CHANGE `description` `description` text  COLLATE utf8_general_ci NULL after `category` , 
	ADD COLUMN `vat_id` varchar(30)  COLLATE utf8_general_ci NULL after `description` , 
	ADD COLUMN `registration_number_1` varchar(30)  COLLATE utf8_general_ci NULL after `vat_id` , 
	ADD COLUMN `registration_number_2` varchar(30)  COLLATE utf8_general_ci NULL after `registration_number_1` , 
	ADD COLUMN `verification` text  COLLATE utf8_general_ci NULL after `registration_number_2` ;
ALTER TABLE `vtiger_vendor`
	ADD CONSTRAINT `fk_1_vtiger_vendor` 
	FOREIGN KEY (`vendorid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_account` 
	CHANGE `ownership` `ownership` varchar(50)  COLLATE utf8_general_ci NULL after `annualrevenue` , 
	CHANGE `siccode` `siccode` varchar(50)  COLLATE utf8_general_ci NULL after `ownership` , 
	CHANGE `phone` `phone` varchar(30)  COLLATE utf8_general_ci NULL after `siccode` , 
	CHANGE `otherphone` `otherphone` varchar(30)  COLLATE utf8_general_ci NULL after `phone` , 
	CHANGE `email1` `email1` varchar(100)  COLLATE utf8_general_ci NULL after `otherphone` , 
	CHANGE `email2` `email2` varchar(100)  COLLATE utf8_general_ci NULL after `email1` , 
	CHANGE `website` `website` varchar(100)  COLLATE utf8_general_ci NULL after `email2` , 
	CHANGE `fax` `fax` varchar(30)  COLLATE utf8_general_ci NULL after `website` , 
	CHANGE `employees` `employees` int(10)   NULL DEFAULT 0 after `fax` , 
	CHANGE `emailoptout` `emailoptout` varchar(3)  COLLATE utf8_general_ci NULL DEFAULT '0' after `employees` , 
	CHANGE `isconvertedfromlead` `isconvertedfromlead` varchar(3)  COLLATE utf8_general_ci NULL DEFAULT '0' after `emailoptout` , 
	ADD COLUMN `vat_id` varchar(30)  COLLATE utf8_general_ci NULL after `isconvertedfromlead` , 
	ADD COLUMN `registration_number_1` varchar(30)  COLLATE utf8_general_ci NULL after `vat_id` , 
	ADD COLUMN `registration_number_2` varchar(30)  COLLATE utf8_general_ci NULL after `registration_number_1` , 
	ADD COLUMN `verification` text  COLLATE utf8_general_ci NULL after `registration_number_2` , 
	ADD COLUMN `no_approval` varchar(3)  COLLATE utf8_general_ci NULL DEFAULT '0' after `verification` , 
	ADD COLUMN `sum_salesorders` decimal(25,8)   NULL DEFAULT 0.00000000 after `no_approval` , 
	ADD COLUMN `sum_invoices` decimal(25,8)   NULL DEFAULT 0.00000000 after `sum_salesorders` , 
	ADD COLUMN `balance` decimal(25,8)   NULL after `sum_invoices` , 
	ADD COLUMN `average_profit_so` decimal(5,2)   NULL after `balance` , 
	ADD KEY `sum_invoices`(`sum_invoices`) , 
	ADD KEY `sum_salesorders`(`sum_salesorders`) ;
ALTER TABLE `vtiger_account`
	ADD CONSTRAINT `fk_1_vtiger_account` 
	FOREIGN KEY (`accountid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_module_dashboard_widgets` 
	CHANGE `linkid` `linkid` int(19)   NOT NULL after `id` , 
	ADD COLUMN `templateid` int(19)   NOT NULL after `userid` , 
	CHANGE `filterid` `filterid` int(19)   NULL after `templateid` , 
	CHANGE `title` `title` varchar(100)  COLLATE utf8_general_ci NULL after `filterid` , 
	CHANGE `data` `data` text  COLLATE utf8_general_ci NULL after `title` , 
	ADD COLUMN `size` varchar(50)  COLLATE utf8_general_ci NULL after `data` , 
	ADD COLUMN `limit` int(10)   NULL after `size` , 
	ADD COLUMN `owners` varchar(100) DEFAULT NULL after `limit` ,
	CHANGE `position` `position` varchar(50)  COLLATE utf8_general_ci NULL after `limit` , 
	ADD COLUMN `isdefault` int(1)   NULL DEFAULT 0 after `position` , 
	ADD COLUMN `active` int(1)   NULL DEFAULT 0 after `isdefault` , 
	ADD KEY `vtiger_module_dashboard_widgets_ibfk_1`(`templateid`) ;
	
ALTER TABLE `vtiger_module_dashboard_widgets`
	ADD CONSTRAINT `vtiger_module_dashboard_widgets_ibfk_1` 
	FOREIGN KEY (`templateid`) REFERENCES `vtiger_module_dashboard` (`id`) ON DELETE CASCADE ;
	
ALTER TABLE `vtiger_calendar_default_activitytypes` 
	ADD COLUMN `active` tinyint(1)   NULL DEFAULT 0 after `defaultcolor` ;

ALTER TABLE `vtiger_tab` 
	ADD COLUMN `color` VARCHAR(30) NULL AFTER `trial`, 
	ADD COLUMN `coloractive` TINYINT(1) DEFAULT 0 NULL AFTER `color`;
	