/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.6.17 
*********************************************************************
*/
/*!40101 SET NAMES utf8 */;

CREATE TABLE `a_yf_pdf` (
  `pdfid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id of record',
  `module_name` varchar(25) NOT NULL COMMENT 'name of the module',
  `header_content` text NOT NULL,
  `body_content` text NOT NULL,
  `footer_content` text NOT NULL,
  `cole` varchar(255) DEFAULT NULL,
  `colf` varchar(255) DEFAULT NULL,
  `colg` varchar(255) DEFAULT NULL,
  `status` set('active','inactive') NOT NULL,
  `primary_name` varchar(255) NOT NULL,
  `secondary_name` varchar(255) NOT NULL,
  `meta_author` varchar(255) NOT NULL,
  `meta_creator` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `metatags_status` tinyint(1) NOT NULL,
  `meta_subject` varchar(255) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `page_format` varchar(255) NOT NULL,
  `margin_chkbox` tinyint(1) DEFAULT NULL,
  `margin_top` smallint(2) unsigned NOT NULL,
  `margin_bottom` smallint(2) unsigned NOT NULL,
  `margin_left` smallint(2) unsigned NOT NULL,
  `margin_right` smallint(2) unsigned NOT NULL,
  `page_orientation` set('PLL_PORTRAIT','PLL_LANDSCAPE') NOT NULL,
  `language` varchar(7) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `visibility` set('PLL_LISTVIEW','PLL_DETAILVIEW') NOT NULL,
  `default` tinyint(1) DEFAULT NULL,
  `conditions` text NOT NULL,
  PRIMARY KEY (`pdfid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
