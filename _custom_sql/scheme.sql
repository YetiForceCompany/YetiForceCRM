/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.6.17 
*********************************************************************
*/
/*!40101 SET NAMES utf8 */;

CREATE TABLE `a_yf_pdf` (
  `pdfid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id of record',
  `module_name` varchar(25) NOT NULL COMMENT 'name of the module',
  `cola` varchar(255) DEFAULT NULL,
  `colb` varchar(255) DEFAULT NULL,
  `colc` varchar(255) DEFAULT NULL,
  `cold` varchar(255) DEFAULT NULL,
  `cole` varchar(255) DEFAULT NULL,
  `colf` varchar(255) DEFAULT NULL,
  `colg` varchar(255) DEFAULT NULL,
  `status` set('active','inactive') NOT NULL,
  `primary_name` varchar(255) NOT NULL,
  `secondary_name` varchar(255) NOT NULL,
  `set_author` varchar(255) NOT NULL,
  `set_creator` varchar(255) NOT NULL,
  `set_keywords` varchar(255) NOT NULL,
  `metatags_status` tinyint(1) NOT NULL,
  `set_subject` varchar(255) NOT NULL,
  `set_title` varchar(255) NOT NULL,
  PRIMARY KEY (`pdfid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
