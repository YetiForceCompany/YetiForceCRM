/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.6.17 
*********************************************************************
*/
/*!40101 SET NAMES utf8 */;

insert into `a_yf_pdf` (`pdfid`, `module_name`, `summary`, `cola`, `colb`, `colc`, `cold`, `cole`, `colf`, `colg`) values('1','Potentials','summary', 'a','b','c','d','e','f','g');
insert into `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `iconpath`, `description`, `linkto`, `sequence`, `active`, `pinned`) values ('92', '4', 'LBL_PDF', '', 'LBL_PDF_DESCRIPTION', 'index.php?module=PDF&parent=Settings&view=List', '27', '0', '0'); 
update `vtiger_settings_field_seq` set `id` = '92';
