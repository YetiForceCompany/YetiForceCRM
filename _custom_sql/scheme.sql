/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.6.17 
*********************************************************************
*/
/*!40101 SET NAMES utf8 */;

CREATE TABLE `a_yf_pdf` (
  `pdfid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id of record',
  `module_name` varchar(25) NOT NULL COMMENT 'name of the module',
  `summary` varchar(255) DEFAULT NULL,
  `cola` varchar(255) DEFAULT NULL,
  `colb` varchar(255) DEFAULT NULL,
  `colc` varchar(255) DEFAULT NULL,
  `cold` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pdfid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8
