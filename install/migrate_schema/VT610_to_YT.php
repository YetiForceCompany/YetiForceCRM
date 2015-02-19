<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 *************************************************************************************************************************************/
include_once('vtlib/Vtiger/Access.php');
include_once('vtlib/Vtiger/Block.php');
include_once('vtlib/Vtiger/Field.php');
include_once('vtlib/Vtiger/Filter.php');
include_once('vtlib/Vtiger/Profile.php');
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Link.php');
include_once('vtlib/Vtiger/Event.php');
include_once('vtlib/Vtiger/Webservice.php');
include_once('vtlib/Vtiger/Version.php');
require_once 'include/runtime/Cache.php';
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
include_once('install/models/InitSchema.php');
include_once('config/config.php');

// migration to version '1.2.105 RC';
class VT610_to_YT {
	var $name = 'Vtiger CRM 6.1.0';
	var $version = '6.1.0';
	var $adminId = '';
	var $source = '';
	
	function preProcess($userName, $source) {
		global $current_user, $adb, $log;
		$log->debug("Entering VT610_to_YT::preProcess(".$userName. ', '.$source.") method ...");
		include('config/config.inc.php');
		$globalConfig = $dbconfig;
		global $dbconfig;
		if(!$dbconfig)
			$dbconfig = $globalConfig;
		$this->source = $source;
		
		$query = "SELECT * from vtiger_users where user_name=? AND status = ? AND is_admin = ? ;";
		$result = $adb->pquery( $query, array($userName, 'Active', 'on'), true );
		$assigned_user_id = $adb->query_result( $result, 0, 'id' );
		$this->adminId = $assigned_user_id;
		$user = new Users();
		$current_user = $user->retrieveCurrentUserInfoFromFile( $assigned_user_id );
		
		$location = Install_InitSchema_Model::migration_schema;
		Install_InitSchema_Model::initializeDatabase($location, array('VT610_to_YT_create', 'VT610_to_YT_update'));
		
		$log->debug("Exiting VT610_to_YT::preProcess() method ...");
	}
	function postProcess() {
		global $log;
		$log->debug("Entering VT610_to_YT::postProcess() method ...");
		

		$location = Install_InitSchema_Model::migration_schema;
		Install_InitSchema_Model::initializeDatabase($location, array('VT610_to_YT_delete'));
		$log->debug("Exiting VT610_to_YT::postProcess() method ...");
		return true;
	}
	public function process() {
		global $log;
		$log->debug("Entering VT610_to_YT::process() method ...");
		self::transferLogo();
		self::removeModules();
		self::load_default_menu();
		self::settingsReplace();
		self::addModule();
		self::addBlocks();
		self::addFields();
		self::relatedList();
		self::addSharingToModules();
		self::addClosedtimeField();
		self::pobox();
		self::wasRead();
		self::updateRecords();
		self::foldersToTree();
		self::changeFieldOnTree();
		self::InactiveFields();
		
		$fieldsToDelete = array(
		'Contacts'=>array('mailingcity',"mailingstreet",'mailingcountry',"othercountry",'mailingstate',"mailingpobox",'othercity',"otherstate",'mailingzip',"otherzip",'otherstreet',"otherpobox"),
		'Invoice'=>array('s_h_amount',"adjustment",'s_h_percent','ship_city','ship_code','ship_country','ship_state','ship_street','ship_pobox','bill_city','bill_code','bill_country','bill_state','bill_street','bill_pobox'),
		'Leads'=>array('city',"code",'state','country','lane','leadaddresstype','pobox',"designation","rating"),
		'PurchaseOrder'=>array('s_h_percent',"s_h_amount",'adjustment','ship_city','ship_code','ship_country','ship_state','ship_street','ship_pobox','bill_city','bill_code','bill_country','bill_state','bill_street','bill_pobox'),
		'Quotes'=>array('s_h_percent',"s_h_amount",'adjustment','inventorymanager'),
		'SalesOrder'=>array('s_h_percent',"s_h_amount",'adjustment'),
		'Accounts'=>array('bill_street',"bill_city","bill_state","bill_code","bill_country","bill_pobox","ship_street","ship_city","ship_state","ship_code","ship_country","ship_pobox"),
		'Vendors'=>array('country',"city","street","postalcode","state","pobox"),
		);
		self::deleteFields($fieldsToDelete);
		self::leadMapping();
		self::handlers();
		self::worflowEnityMethod();
		self::deleteWorkflow();
		self::addWorkflowType();
		self::addWorkflow();
		self::fieldsModuleRel();
		self::customerPortal();
		self::cron();
		self::picklists();
		self::addWidget();
		self::actionMapping();
		self::addSearchfield();
		self::customView();
		self::addRecords();
		self::addEmployees();
		$log->debug("Exiting VT610_to_YT::process() method ...");
	}
	
	public function addModule(){
		global $log;
		$log->debug("Entering VT610_to_YT::addModule() method ...");
		
		$modules = array();
		foreach(new DirectoryIterator('install/migrate_schema/VT610_to_YT') as $file){
			if(!$file->isDot()){
				if( strpos($file->getFilename(), '.xml') !== false){
					$modules[]= str_replace(".xml", "",$file->getFilename()) ;
				}
			}
		}
		foreach($modules AS $module){
			try {
				if(!self::checkModuleExists($module)){
					$importInstance = new Vtiger_PackageImport();
					$importInstance->_modulexml = simplexml_load_file('install/migrate_schema/VT610_to_YT/'.$module.'.xml');
					$importInstance->import_Module();
					self::addModuleToMenu($module, (string)$importInstance->_modulexml->parent);
				}
			} catch (Exception $e) {
				Install_InitSchema_Model::addMigrationLog('addModule '.$e->getMessage(),'error');
			}
		}
		Install_InitSchema_Model::addMigrationLog('addModule');
		$log->debug("Exiting VT610_to_YT::addModule() method ...");
	}
	public function addModuleToMenu($moduleName, $parent){
		$adb = PearDatabase::getInstance();
		if(!$parent)
			return false;
		
		$sql = "SELECT `profileid` FROM `vtiger_profile` WHERE 1;";
        $result = $adb->query( $sql, true );
        $num = $adb->num_rows( $result );
        
        $profiles = array();
        for ( $i=0; $i<$num; $i++ ) {
            $profiles[] = $adb->query_result( $result, $i, 'profileid' );
        }
        
        $profilePermissions = implode( ' |##| ', $profiles );
		$profilePermissions = ' ' . $profilePermissions . ' ';
		
		//$blocksModule = array('My Home Page','Companies','Human resources','Sales','Projects','Support','Databases');
		$sql = "SELECT `id` FROM `vtiger_ossmenumanager` WHERE label = ? AND tabid = ? AND parent_id = ?;";
		$result = $adb->pquery( $sql, array($parent, 0, 0), true );
		$num = $adb->num_rows( $result );
		if($num == 1){
			$subParams = array(
				'parent_id'     => $adb->query_result( $result, 0, 'id' ),
				'tabid'         => getTabid($moduleName),
				'label'         => $moduleName,
				'sequence'      => -1,
				'visible'       => '1',
				'type'          => 0,
				'url'           => '',
				'new_window'    => 0,
				'permission'    => $profilePermissions,
				'locationicon'  => '',
				'sizeicon'      => '',
				'langfield'     => ''
				
			);
			$id = OSSMenuManager_Record_Model::addMenu( $subParams ); 
		}
	}
	public function load_default_menu( ) {
		$adb = PearDatabase::getInstance();
		
		//menu manager
		$menu_manager[] = array(237,0,0,'My Home Page',1,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(238,237,3,'Home',1,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(239,237,9,'Calendar',2,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(241,301,26,'Campaigns',1,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(242,282,6,'Accounts',2,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(243,282,4,'Contacts',3,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(244,282,7,'Leads',1,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(245,305,8,'Documents',17,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,'en_us*List of documents#pl_pl*Lista dokumentów',1);
		$menu_manager[] = array(247,301,2,'Potentials',2,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(248,301,20,'Quotes',4,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(249,301,22,'SalesOrder',5,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(250,301,23,'Invoice',7,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,'en_us*Sales invoices#pl_pl*Faktury sprzedażowe',1);
		$menu_manager[] = array(251,301,19,'PriceBooks',9,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(253,304,13,'HelpDesk',1,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(254,304,15,'Faq',3,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(255,304,34,'ServiceContracts',2,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(256,302,41,'ProjectMilestone',2,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(257,302,42,'ProjectTask',3,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(258,302,43,'Project',1,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(260,305,25,'Reports',23,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,'en_us*List of reports#pl_pl*Lista raportów',1);
		$menu_manager[] = array(262,305,14,'Products',2,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(263,282,18,'Vendors',4,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(264,301,21,'PurchaseOrder',6,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(265,305,35,'Services',7,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,NULL,1);
		$menu_manager[] = array(266,305,37,'Assets',4,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,'en_us*Sold Products#pl_pl*Produkty sprzedane',1);
		$menu_manager[] = array(268,305,33,'PBXManager',15,1,0,'index.php?module=PBXManager&view=List',0,' 1 ','','16x16','en_us*List of calls#pl_pl*Lista połączeń telefonicznych',1);
		$menu_manager[] = array(269,305,44,'RecycleBin',19,1,0,'index.php?module=RecycleBin&view=List',0,' 1 ','','16x16','en_us*List of deleted records#pl_pl*Lista usuniętych rekordów',1);
		$menu_manager[] = array(270,305,45,'SMSNotifier',14,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,'en_us*List of text messages#pl_pl*Lista smsów',1);
		$menu_manager[] = array(271,305,47,'OSSPdf',18,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,'en_us*List of pdf templates#pl_pl*Lista szablonów pdf',1);
		$menu_manager[] = array(272,237,48,'OSSMail',3,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,'en_us*My mailbox#pl_pl*Moja poczta',1);
		$menu_manager[] = array(273,305,49,'OSSMailTemplates',16,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,'en_us*List of email templates#pl_pl*Lista szablonów mailowych',1);
		$menu_manager[] = array(274,292,51,'OSSTimeControl',2,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ',NULL,NULL,'en_us*Time control#pl_pl*Czas pracy',1);
		$menu_manager[] = array(277,305,59,'OutsourcedProducts',3,1,0,'index.php?module=OutsourcedProducts&view=List',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','',0);
		$menu_manager[] = array(278,305,58,'OSSSoldServices',9,1,0,'index.php?module=OSSSoldServices&view=List',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','',0);
		$menu_manager[] = array(279,305,57,'OSSOutsourcedServices',8,1,0,'index.php?module=OSSOutsourcedServices&view=List',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','',0);
		$menu_manager[] = array(280,305,54,'OSSMailView',13,1,0,'index.php?module=OSSMailView&view=List',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*List of corporate mailbox#pl_pl*Lista maili',0);
		$menu_manager[] = array(282,0,0,'Companies',2,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*Companies#pl_pl*Firmy',0);
		$menu_manager[] = array(292,0,0,'Human resources',6,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*HR#pl_pl*Kadry',0);
		$menu_manager[] = array(299,305,0,'*separator*',5,1,3,'*separator*',0,' 1 |##| 2 |##| 3 |##| 4 ','','','',0);
		$menu_manager[] = array(301,0,0,'Sales',3,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*Sales#pl_pl*Sprzedaż',0);
		$menu_manager[] = array(302,0,0,'Projects',4,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*Projects#pl_pl*Projekty',0);
		$menu_manager[] = array(304,0,0,'Support',5,1,0,'',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*Support#pl_pl*Wsparcie',0);
		$menu_manager[] = array(305,0,0,'Databases',7,1,0,'',0,'  ','','16x16','en_us*Databases#pl_pl*Bazy danych',0);
		$menu_manager[] = array(306,305,0,'Products database',1,1,2,'*etykieta*',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*Products database#pl_pl*Baza produktów',0);
		$menu_manager[] = array(307,305,0,'Services database',6,1,2,'*etykieta*',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*Services database#pl_pl*Baza usług',0);
		$menu_manager[] = array(308,305,0,'*separator*',10,1,3,'*separator*',0,'  ','','','',0);
		$menu_manager[] = array(309,305,0,'Lists',11,1,2,'*etykieta*',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*Lists#pl_pl*Wykazy',0);
		$menu_manager[] = array(311,292,61,'OSSEmployees',1,1,0,'index.php?module=OSSEmployees&view=List',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*Employees#pl_pl*Pracownicy',0);
		$menu_manager[] = array(312,305,60,'OSSPasswords',20,1,0,'index.php?module=OSSPasswords&view=List',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*List of passwords#pl_pl*Lista haseł',0);
		$menu_manager[] = array(323,301,70,'Calculations',3,1,0,'index.php?module=Calculations&view=List',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*Calculations#pl_pl*Kalkulacje',0);
		$menu_manager[] = array(324,301,71,'OSSCosts',8,1,0,'index.php?module=OSSCosts&view=List',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*Purchase invoices#pl_pl*Faktury zakupowe',0);
		$menu_manager[] = array(325,305,69,'AddressLevel1',12,1,0,'index.php?module=AddressLevel1&view=List',0,' 1 |##| 2 |##| 3 |##| 4 ','','16x16','en_us*List of addresses#pl_pl*Lista adresów',0);
		$menu_manager[] = array(327,305,0,'*separator*',22,1,3,'*separator*',0,' 1 |##| 2 |##| 3 |##| 4 ','','','',0);
		$menu_manager[] = array(328,305,74,'CallHistory',21,1,0,'index.php?module=CallHistory&view=List',0,'  ','','16x16','',0);
		$menu_manager[] = array(329,0,0,'Group Card',8,1,0,'',0,'  ','','16x16','en_us*Group Card#pl_pl*Karta grupowa',0);


		$blocksModule = array('My Home Page','Companies','Human resources','Sales','Projects','Support','Databases','*separator*','Lists','Products database','Services database','Group Card');
		
		$sql = "SELECT `profileid` FROM `vtiger_profile` WHERE 1;";
        $result = $adb->query( $sql, true );
        $num = $adb->num_rows( $result );
        
        $profiles = array();
        for ( $i=0; $i<$num; $i++ ) {
            $profiles[] = $adb->query_result( $result, $i, 'profileid' );
        }
        
        $profilePermissions = implode( ' |##| ', $profiles );
		$profilePermissions = ' ' . $profilePermissions . ' ';

		foreach ($menu_manager AS $module){
			if(self::checkModuleExists($module[3]) || in_array($module[3],$blocksModule)){
				if(!in_array($module[3],$blocksModule))
					$module[2] = getTabid($module[3]);
				$module[9] = $profilePermissions;
			$adb->pquery("insert  into `vtiger_ossmenumanager`(`id`,`parent_id`,`tabid`,`label`,`sequence`,`visible`,`type`,`url`,`new_window`,`permission`,`locationicon`,`sizeicon`,`langfield`,`paintedicon`) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);", $module);
			}
		}
    }
	function settingsReplace() {
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::settingsReplace() method ...");
		//add new record
		$settings_blocks = array();
		$settings_blocks[] = array('LBL_USER_MANAGEMENT',1);
		$settings_blocks[] = array('LBL_STUDIO',3);
		$settings_blocks[] = array('LBL_COMPANY',5);
		$settings_blocks[] = array('LBL_OTHER_SETTINGS',20);
		$settings_blocks[] = array('LBL_INTEGRATION',6);
		$settings_blocks[] = array('LBL_SECURITY_MANAGEMENT',2);
		$settings_blocks[] = array('LBL_MAIL',8);
		$settings_blocks[] = array('LBL_About_YetiForce',21);
		$settings_blocks[] = array('LBL_CUSTOMIZE_TRANSLATIONS',4);
		
		//change label
		try {
				$adb->pquery('UPDATE `vtiger_settings_blocks` SET `label` = ? WHERE `label` = ?;', array('LBL_COMPANY', 'LBL_COMMUNICATION_TEMPLATES'), true);
			} catch (Exception $e) {
				Install_InitSchema_Model::addMigrationLog('settingsReplace '.$e->getMessage(),'error');
			}
		foreach ($settings_blocks AS $block){
			try {
				if(!self::checkBlockExists('Settings', $block[0])){
					$count = self::countRow('vtiger_settings_blocks', 'blockid');
					array_unshift($block, ++$count);
					$adb->pquery('insert  into `vtiger_settings_blocks`(`blockid`,`label`,`sequence`) values (?, ?, ?)', $block);
				}
			} catch (Exception $e) {
				Install_InitSchema_Model::addMigrationLog('settingsReplace '.$e->getMessage(),'error');
			}
		}
		try {
			$adb->pquery('UPDATE `vtiger_settings_blocks_seq` SET id = ?;', array(self::countRow('vtiger_settings_blocks', 'blockid')));
		} catch (Exception $e) {
			Install_InitSchema_Model::addMigrationLog('settingsReplace '.$e->getMessage(),'error');
		}
		//sequance
		self::sequanceSettingsBlocks($settings_blocks);
		
		//delete row from vtiger_settings_field table
		$delete_settings_field = array('LBL_MENU_EDITOR','index.php?module=MenuEditor&parent=Settings&view=Index','LBL_MAIL_SCANNER','index.php?parent=Settings&module=MailConverter&view=List','LBL_LOGIN_HISTORY_DETAILS','index.php?module=LoginHistory&parent=Settings&view=List','LBL_MAIL_SERVER_SETTINGS','index.php?parent=Settings&module=Vtiger&view=OutgoingServerDetail','LBL_CUSTOMER_PORTAL','index.php?module=CustomerPortal&action=index&parenttab=Settings','Webforms','index.php?module=Webforms&action=index&parenttab=Settings');
		try {
			if($delete_settings_field){
				$sql = "DELETE FROM `vtiger_settings_field` WHERE (`name` = ? AND `linkto` = ?) ";
				for($i=1;$i<(count($delete_settings_field)/2);$i++){
					$sql .= " OR (`name` = ? AND `linkto` = ?) ";
				}
				$adb->pquery($sql, $delete_settings_field, true);
			}
		} catch (Exception $e) {
			Install_InitSchema_Model::addMigrationLog('settingsReplace '.$e->getMessage(),'error');
		}
		//update name from vtiger_settings_field table
		$update_settings_field = array();
		$update_settings_field[] = array('LBL_TERMS_AND_CONDITIONS','LBL_INV_TANDC_DESCRIPTION', 'INVENTORYTERMSANDCONDITIONS','index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit');
		$update_settings_field[] = array('LBL_CUSTOMIZE_RECORD_NUMBERING','LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION', 'LBL_CUSTOMIZE_MODENT_NUMBER','index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering');
		$update_settings_field[] = array('Scheduler', 'LBL_SCHEDULER_DESCRIPTION','Scheduler','index.php?module=CronTasks&parent=Settings&view=List');
		$update_settings_field[] = array('LBL_PBXMANAGER', 'LBL_PBXMANAGER_DESCRIPTION','LBL_PBXMANAGER','index.php?module=PBXManager&parent=Settings&view=Index');
		
		foreach($update_settings_field AS $params){
			try {
				$adb->pquery("UPDATE `vtiger_settings_field` SET `name` = ?, `description` = ? WHERE (`name` = ? AND `linkto` = ?);", $params, true);
			} catch (Exception $e) {
				Install_InitSchema_Model::addMigrationLog('settingsReplace '.$e->getMessage(),'error');
			}
		}
		//add new record
		$settings_field = array();
		$settings_field[] = array("LBL_USER_MANAGEMENT","LBL_USERS","ico-users.gif","LBL_USER_DESCRIPTION","index.php?module=Users&parent=Settings&view=List","1","0","1");
		$settings_field[] = array("LBL_USER_MANAGEMENT","LBL_ROLES","ico-roles.gif","LBL_ROLE_DESCRIPTION","index.php?module=Roles&parent=Settings&view=Index","2","0","0");
		$settings_field[] = array("LBL_USER_MANAGEMENT","LBL_PROFILES","ico-profile.gif","LBL_PROFILE_DESCRIPTION","index.php?module=Profiles&parent=Settings&view=List","3","0","0");
		$settings_field[] = array("LBL_USER_MANAGEMENT","USERGROUPLIST","ico-groups.gif","LBL_GROUP_DESCRIPTION","index.php?module=Groups&parent=Settings&view=List","4","0","0");
		$settings_field[] = array("LBL_USER_MANAGEMENT","LBL_SHARING_ACCESS","shareaccess.gif","LBL_SHARING_ACCESS_DESCRIPTION","index.php?module=SharingAccess&parent=Settings&view=Index","5","0","0");
		$settings_field[] = array("LBL_USER_MANAGEMENT","LBL_FIELDS_ACCESS","orgshar.gif","LBL_SHARING_FIELDS_DESCRIPTION","index.php?module=FieldAccess&parent=Settings&view=Index","6","0","0");
		$settings_field[] = array("LBL_SECURITY_MANAGEMENT","LBL_LOGIN_HISTORY_DETAILS","set-IcoLoginHistory.gif","LBL_LOGIN_HISTORY_DESCRIPTION","index.php?module=LoginHistory&parent=Settings&view=List","7","0","0");
		$settings_field[] = array("LBL_STUDIO","VTLIB_LBL_MODULE_MANAGER","vtlib_modmng.gif","VTLIB_LBL_MODULE_MANAGER_DESCRIPTION","index.php?module=ModuleManager&parent=Settings&view=List","8","0","1");
		$settings_field[] = array("LBL_STUDIO","LBL_PICKLIST_EDITOR","picklist.gif","LBL_PICKLIST_DESCRIPTION","index.php?parent=Settings&module=Picklist&view=Index","1","0","1");
		$settings_field[] = array("LBL_STUDIO","LBL_PICKLIST_DEPENDENCY_SETUP","picklistdependency.gif","LBL_PICKLIST_DEPENDENCY_DESCRIPTION","index.php?parent=Settings&module=PickListDependency&view=List","2","0","0");
		$settings_field[] = array("LBL_COMPANY","NOTIFICATIONSCHEDULERS","notification.gif","LBL_NOTIF_SCHED_DESCRIPTION","index.php?module=Settings&view=listnotificationschedulers&parenttab=Settings","4","0","0");
		$settings_field[] = array("LBL_COMPANY","INVENTORYNOTIFICATION","inventory.gif","LBL_INV_NOTIF_DESCRIPTION","index.php?module=Settings&view=listinventorynotifications&parenttab=Settings","1","0","0");
		$settings_field[] = array("LBL_COMPANY","LBL_COMPANY_DETAILS","company.gif","LBL_COMPANY_DESCRIPTION","index.php?parent=Settings&module=Vtiger&view=CompanyDetails","2","0","0");
		$settings_field[] = array("LBL_MAIL","LBL_MAIL_SERVER_SETTINGS","ogmailserver.gif","LBL_MAIL_SERVER_DESCRIPTION","index.php?parent=Settings&module=Vtiger&view=OutgoingServerDetail","3","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","LBL_CURRENCY_SETTINGS","currency.gif","LBL_CURRENCY_DESCRIPTION","index.php?parent=Settings&module=Currency&view=List","4","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","LBL_TAX_SETTINGS","taxConfiguration.gif","LBL_TAX_DESCRIPTION","index.php?module=Vtiger&parent=Settings&view=TaxIndex","5","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","LBL_SYSTEM_INFO","system.gif","LBL_SYSTEM_DESCRIPTION","index.php?module=Settings&submodule=Server&view=ProxyConfig","6","1","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","LBL_ANNOUNCEMENT","announ.gif","LBL_ANNOUNCEMENT_DESCRIPTION","index.php?parent=Settings&module=Vtiger&view=AnnouncementEdit","1","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","LBL_DEFAULT_MODULE_VIEW","set-IcoTwoTabConfig.gif","LBL_DEFAULT_MODULE_VIEW_DESC","index.php?module=Settings&action=DefModuleView&parenttab=Settings","2","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","LBL_TERMS_AND_CONDITIONS","terms.gif","LBL_INV_TANDC_DESCRIPTION","index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit","3","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","LBL_CUSTOMIZE_RECORD_NUMBERING","settingsInvNumber.gif","LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION","index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering","4","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","LBL_LIST_WORKFLOWS","settingsWorkflow.png","LBL_LIST_WORKFLOWS_DESCRIPTION","index.php?module=Workflows&parent=Settings&view=List","6","0","1");
		$settings_field[] = array("LBL_OTHER_SETTINGS","LBL_CONFIG_EDITOR","migrate.gif","LBL_CONFIG_EDITOR_DESCRIPTION","index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail","7","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","Scheduler","Cron.png","LBL_SCHEDULER_DESCRIPTION","index.php?module=CronTasks&parent=Settings&view=List","8","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","LBL_WORKFLOW_LIST","settingsWorkflow.png","LBL_AVAILABLE_WORKLIST_LIST","index.php?module=com_vtiger_workflow&action=workflowlist","8","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","ModTracker","set-IcoLoginHistory.gif","LBL_MODTRACKER_DESCRIPTION","index.php?module=ModTracker&action=BasicSettings&parenttab=Settings&formodule=ModTracker","9","0","0");
		$settings_field[] = array("LBL_INTEGRATION","LBL_PBXMANAGER","","LBL_PBXMANAGER_DESCRIPTION","index.php?module=PBXManager&parent=Settings&view=Index","2","0","0");
		$settings_field[] = array("LBL_INTEGRATION","LBL_CUSTOMER_PORTAL","portal_icon.png","PORTAL_EXTENSION_DESCRIPTION","index.php?module=CustomerPortal&action=index&parenttab=Settings","1","0","0");
		$settings_field[] = array("LBL_INTEGRATION","Webforms","modules/Webforms/img/Webform.png","LBL_WEBFORMS_DESCRIPTION","index.php?module=Webforms&action=index&parenttab=Settings","3","0","0");
		$settings_field[] = array("LBL_STUDIO","LBL_EDIT_FIELDS","","LBL_LAYOUT_EDITOR_DESCRIPTION","index.php?module=LayoutEditor&parent=Settings&view=Index","10","0","0");
		$settings_field[] = array("LBL_SECURITY_MANAGEMENT","LBL_PASSWORD_CONF", "","LBL_PASSWORD_DESCRIPTION","index.php?module=Password&parent=Settings&view=Index","1","0","0");
		$settings_field[] = array("LBL_STUDIO","LBL_ARRANGE_RELATED_TABS","picklist.gif","LBL_ARRANGE_RELATED_TABS","index.php?module=LayoutEditor&parent=Settings&view=Index&mode=showRelatedListLayout","4","0","1");
		$settings_field[] = array("LBL_About_YetiForce","License", "","LBL_LICENSE_DESCRIPTION","index.php?module=Vtiger&parent=Settings&view=License", "","0","0");
		$settings_field[] = array("LBL_STUDIO","LBL_DATAACCESS", "","LBL_DATAACCESS_DESCRIPTION","index.php?module=DataAccess&parent=Settings&view=Index","5","0","0");
		$settings_field[] = array("LBL_CUSTOMIZE_TRANSLATIONS","LangManagement", "","LBL_LANGMANAGEMENT_DESCRIPTION","index.php?module=LangManagement&parent=Settings&view=Index","6","0","0");
		$settings_field[] = array("LBL_USER_MANAGEMENT","GlobalPermission","","LBL_GLOBALPERMISSION_DESCRIPTION","index.php?module=GlobalPermission&parent=Settings&view=Index","7","0","0");
		$settings_field[] = array("LBL_STUDIO","Search Setup","","LBL_SEARCH_SETUP_DESCRIPTION","index.php?module=Search&parent=Settings&view=Index","6","0","0");
		$settings_field[] = array("LBL_STUDIO","CustomView", "","LBL_CUSTOMVIEW_DESCRIPTION","index.php?module=CustomView&parent=Settings&view=Index","8","0","0");
		$settings_field[] = array("LBL_STUDIO","Widgets", "","LBL_WIDGETS_DESCRIPTION","index.php?module=Widgets&parent=Settings&view=Index","9","0","1");
		$settings_field[] = array("LBL_About_YetiForce","Credits", "","LBL_CREDITS_DESCRIPTION","index.php?module=Home&view=Credits&parent=Settings","10","0","0");
		$settings_field[] = array("LBL_STUDIO","LBL_QUICK_CREATE_EDITOR", "","LBL_QUICK_CREATE_EDITOR_DESCRIPTION","index.php?module=QuickCreateEditor&parent=Settings&view=Index","11","0","0");
		$settings_field[] = array("LBL_INTEGRATION","LBL_API_ADDRESS","","LBL_API_ADDRESS_DESCRIPTION","index.php?module=ApiAddress&parent=Settings&view=Configuration","4","0","0");
		$settings_field[] = array("LBL_SECURITY_MANAGEMENT","LBL_BRUTEFORCE","","LBL_BRUTEFORCE_DESCRIPTION","index.php?module=BruteForce&parent=Settings&view=Show","20","0","0");
		$settings_field[] = array('LBL_SECURITY_MANAGEMENT','Backup','','LBL_BACKUP_DESCRIPTION','index.php?parent=Settings&module=BackUp&view=Index','20','0','0');
		$settings_field[] = array('LBL_About_YetiForce','LBL_CONFREPORT','','LBL_CONFREPORT_DESCRIPTION','index.php?parent=Settings&module=ConfReport&view=Index','20','0','0');
		$settings_field[] = array('LBL_About_YetiForce','LBL_UPDATES_HISTORY',NULL,'LBL_UPDATES_HISTORY_DESCRIPTION','index.php?parent=Settings&module=Updates&view=Index','3','0','0');
		$settings_field[] = array('LBL_STUDIO','LBL_ACTIVITY_TYPES','','LBL_ACTIVITY_TYPES_DESCRIPTION','index.php?parent=Settings&module=Calendar&view=ActivityTypes','25','0','0');
		$settings_field[] = array('LBL_STUDIO','LBL_WIDGETS_MANAGEMENT','','LBL_WIDGETS_MANAGEMENT_DESCRIPTION','index.php?module=WidgetsManagement&parent=Settings&view=Configuration','12','0','0');
		$settings_field[] = array('LBL_STUDIO','LBL_MDULES_COLOR_EDITOR','','LBL_MDULES_COLOR_EDITOR_DESCRIPTION','index.php?parent=Settings&module=MenuEditor&view=Colors','13','0','0');
		$settings_field[] = array('LBL_INTEGRATION','LBL_MOBILE_KEYS',NULL,'LBL_MOBILE_KEYS_DESCRIPTION','index.php?parent=Settings&module=MobileApps&view=MobileKeys','5','0','0');
		$settings_field[] = array('LBL_STUDIO','LBL_TREES_MANAGER',NULL,'LBL_TREES_MANAGER_DESCRIPTION','index.php?module=TreesManager&parent=Settings&view=List','15','0','0');
		$settings_field[] = array('LBL_STUDIO','LBL_MODTRACKER_SETTINGS',NULL,'LBL_MODTRACKER_SETTINGS_DESCRIPTION','index.php?module=ModTracker&parent=Settings&view=List','16','0','0');

		foreach ($settings_field AS $field){
			try {
				if(!self::checkFieldExists( $field, 'Settings' )){
					$field[0] = self::getBlockId($field[0]);
					$count = self::countRow('vtiger_settings_field', 'fieldid');
					array_unshift($field, ++$count);
					$adb->pquery('insert into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values (?, ?, ?, ?, ?, ?, ?, ?, ?)', $field);
				}
			} catch (Exception $e) {
				Install_InitSchema_Model::addMigrationLog('settingsReplace '.$e->getMessage(),'error');
			}
		}
		try {
			$adb->pquery('UPDATE `vtiger_settings_field_seq` SET id = ?;', array(self::countRow('vtiger_settings_field', 'fieldid')));
		} catch (Exception $e) {
			Install_InitSchema_Model::addMigrationLog('settingsReplace '.$e->getMessage(),'error');
		}
		Install_InitSchema_Model::addMigrationLog('settingsReplace');
		$log->debug("Exiting VT610_to_YT::settingsReplace() method ...");
	}
	public function sequanceSettingsBlocks($blockSequence){
		global $adb,$log;
		$log->debug("Entering VT610_to_YT::sequanceSettingsBlocks() method ...");
		$blockList = array();
        $query = 'UPDATE vtiger_settings_blocks SET ';
        $query .=' sequence = CASE ';
        foreach($blockSequence as $newBlockSequence ) {
			$blockLabel = $newBlockSequence[0];
			$sequence = $newBlockSequence[1];
			$blockList[] = $blockLabel;
			$query .= ' WHEN label="'.$blockLabel.'" THEN '.$sequence;
        }
		$query .=' END ';
        $query .= ' WHERE label IN ('.generateQuestionMarks($blockList).')';
		$adb->pquery($query, array($blockList));
		$log->debug("Exiting VT610_to_YT::sequanceSettingsBlocks() method ...");
	}
	public function countRow($table, $field){
		global $adb;
		$result = $adb->query("SELECT MAX(".$field.") AS max_seq  FROM ".$table." ;");
		return $adb->query_result($result, 0, 'max_seq');
	}
	public function getBlockId($label){
		global $adb;
		$result = $adb->pquery("SELECT blockid FROM vtiger_settings_blocks WHERE label = ? ;",array($label), true);
		return $adb->query_result($result, 0, 'blockid');
	}
	public function handlers(){
		global $log;
		$log->debug("Entering VT610_to_YT::handlers() method ...");
		require_once 'modules/com_vtiger_workflow/include.inc';
		require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
		require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
		require_once('include/events/include.inc');
		global $adb;
		
		$removeClass = array('none'=>'RecurringInvoiceHandler','none1'=>'HelpDeskHandler','ModTracker'=>'ModTrackerHandler','none2'=>'PBXManagerHandler','none3'=>'PBXManagerBatchHandler','ServiceContracts'=>'ServiceContractsHandler','Invoice'=>'InvoiceHandler','PurchaseOrder'=>'PurchaseOrderHandler','none4'=>'ModCommentsHandler','Home'=>'Vtiger_RecordLabelUpdater_Handler','none5'=>'SECURE');

		$addHandler = array();
		$addHandler[] = array('vtiger.entity.beforeunlink','include/events/VTEntityDelta.php','VTEntityDelta',NULL,'1','[]');
		$addHandler[] = array('vtiger.entity.afterunlink','include/events/VTEntityDelta.php','VTEntityDelta',NULL,'1','[]');
		$addHandler[] = array('vtiger.entity.aftersave.final','modules/ModTracker/handlers/ModTrackerHandler.php','ModTrackerHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.beforedelete','modules/ModTracker/handlers/ModTrackerHandler.php','ModTrackerHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.afterrestore','modules/ModTracker/handlers/ModTrackerHandler.php','ModTrackerHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.aftersave','modules/PBXManager/handlers/PBXManagerHandler.php','PBXManagerHandler','','1','["VTEntityDelta"]');
		$addHandler[] = array('vtiger.entity.afterdelete','modules/PBXManager/handlers/PBXManagerHandler.php','PBXManagerHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.afterrestore','modules/PBXManager/handlers/PBXManagerHandler.php','PBXManagerHandler','','1','[]');
		$addHandler[] = array('vtiger.batchevent.save','modules/PBXManager/handlers/PBXManagerHandler.php','PBXManagerBatchHandler','','1','[]');
		$addHandler[] = array('vtiger.batchevent.delete','modules/PBXManager/handlers/PBXManagerHandler.php','PBXManagerBatchHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.beforesave','modules/ServiceContracts/handlers/ServiceContractsHandler.php','ServiceContractsHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.aftersave','modules/ServiceContracts/handlers/ServiceContractsHandler.php','ServiceContractsHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.aftersave','modules/PurchaseOrder/handlers/PurchaseOrderHandler.php','PurchaseOrderHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.aftersave','modules/ModComments/handlers/ModCommentsHandler.php','ModCommentsHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.aftersave.final','modules/OSSPasswords/handlers/secure.php','SECURE','','1','[]');
		$addHandler[] = array('vtiger.entity.aftersave.final','modules/OSSTimeControl/handlers/TimeControl.php','TimeControlHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.aftersave.final','modules/Potentials/handlers/PotentialsHandler.php','PotentialsHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.aftersave.final','modules/Accounts/handlers/AccountsHandler.php','AccountsHandler',NULL,'1','[]');
		$addHandler[] = array('vtiger.entity.aftersave.final','modules/Vtiger/handlers/SharedOwnerUpdater.php','Vtiger_SharedOwnerUpdater_Handler',NULL,'1','[]');
		$addHandler[] = array('vtiger.entity.aftersave.final','modules/Vtiger/handlers/SharingPrivileges.php','Vtiger_SharingPrivileges_Handler',NULL,'1','[]');
		$addHandler[] = array('vtiger.entity.aftersave','modules/SalesOrder/handlers/RecurringInvoiceHandler.php','RecurringInvoiceHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.aftersave.final','modules/HelpDesk/handlers/HelpDeskHandler.php','HelpDeskHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.afterunlink','modules/OSSTimeControl/handlers/TimeControl.php','TimeControlHandler',NULL,'1','[]');
		$addHandler[] = array('vtiger.entity.afterdelete','modules/OSSTimeControl/handlers/TimeControl.php','TimeControlHandler',NULL,'1','[]');
		$addHandler[] = array('vtiger.entity.aftersave','modules/Vtiger/handlers/RecordLabelUpdater.php','Vtiger_RecordLabelUpdater_Handler','','1','[]');
		
		$adb->query('UPDATE vtiger_eventhandlers SET handler_path = "include/events/VTEntityDelta.php" WHERE handler_path = "data/VTEntityDelta.php";');
		try {
			$em = new VTEventsManager($adb);
			foreach($removeClass as $moduleName=>$handlerClass){
				$em->unregisterHandler($handlerClass);
				if (strpos($moduleName, 'none') === false) 
					$em->setModuleForHandler($moduleName, $handlerClass);
			}
			foreach($addHandler as $handler){
				$em->registerHandler($handler[0], $handler[1], $handler[2], $handler[3], $handler[5]);
			}
		} catch (Exception $e) {
			Install_InitSchema_Model::addMigrationLog('handlers '.$e->getMessage(),'error');
		}
		Install_InitSchema_Model::addMigrationLog('handlers');
		$log->debug("Exiting VT610_to_YT::handlers() method ...");
	}
	public function checkModuleExists($moduleName){
		global $log;
		$log->debug("Entering VT610_to_YT::checkModuleExists() method ...");
		global $adb;
		$result = $adb->pquery('SELECT * FROM vtiger_tab WHERE name = ?', array($moduleName));
		if(!$adb->num_rows($result)) {
			$log->debug("Exiting VT610_to_YT::checkModuleExists() method ...");
			return false;
		}
		$log->debug("Exiting VT610_to_YT::checkModuleExists() method ...");
		return true;
	}
	
	public function worflowEnityMethod (){
		global $log, $adb;
		$log->debug("Entering VT610_to_YT::worflowEnityMethod() method ...");
		// delete all entity method
		$adb->query("DELETE FROM `com_vtiger_workflowtasks_entitymethod` ");
		//add new entity method
		$task_entity_method = array();
		$task_entity_method[] = array('SalesOrder','UpdateInventory','modules/Inventory/handlers/InventoryHandler.php','handleInventoryProductRel');
		$task_entity_method[] = array('Invoice','UpdateInventory','modules/Inventory/handlers/InventoryHandler.php','handleInventoryProductRel');
		$task_entity_method[] = array('Contacts','CreatePortalLoginDetails','modules/Contacts/handlers/ContactsHandler.php','Contacts_createPortalLoginDetails');
		$task_entity_method[] = array('ModComments','CustomerCommentFromPortal','modules/ModComments/handlers/ModCommentsHandler.php','CustomerCommentFromPortal');
		$task_entity_method[] = array('ModComments','TicketOwnerComments','modules/ModComments/handlers/ModCommentsHandler.php','TicketOwnerComments');
		$task_entity_method[] = array('PurchaseOrder','UpdateInventory','modules/Inventory/handlers/InventoryHandler.php','handleInventoryProductRel');
		$emm = new VTEntityMethodManager($adb);
		foreach($task_entity_method as $method){
			$emm->addEntityMethod($method[0], $method[1], $method[2], $method[3]);
		}
		$log->debug("Exiting VT610_to_YT::worflowEnityMethod() method ...");
	}
	public function deleteWorkflow (){
		global $log, $adb;
		$log->debug("Entering VT610_to_YT::deleteWorkflow() method ...");
		// delete all tasks
		$adb->query('UPDATE com_vtiger_workflows SET defaultworkflow = "0";');
		$result = $adb->query('SELECT * FROM com_vtiger_workflows ');
		for($i=0;$i<$adb->num_rows($result);$i++){
			$recordId = $adb->query_result($result, $i, 'workflow_id');
			$recordModel = Settings_Workflows_Record_Model::getInstance($recordId);
			$recordModel->delete();
		}
		$log->debug("Exiting VT610_to_YT::deleteWorkflow() method ...");
	}
	public function addWorkflowType (){
		global $log, $adb;
		$log->debug("Entering VT610_to_YT::addWorkflowType() method ...");
		
		$newTaskType = array();
		$newTaskType[] = array('VTEmailTemplateTask','Email Template Task','VTEmailTemplateTask','modules/com_vtiger_workflow/tasks/VTEmailTemplateTask.inc','com_vtiger_workflow/taskforms/VTEmailTemplateTask.tpl','{"include":[],"exclude":[]}', '');
		$newTaskType[] = array('VTSendPdf','Send Pdf','VTSendPdf','modules/com_vtiger_workflow/tasks/VTSendPdf.inc','com_vtiger_workflow/taskforms/VTSendPdf.tpl','{"include":[],"exclude":[]}', '');
		$newTaskType[] = array('VTUpdateClosedTime','Update Closed Time','VTUpdateClosedTime','modules/com_vtiger_workflow/tasks/VTUpdateClosedTime.inc','com_vtiger_workflow/taskforms/VTUpdateClosedTime.tpl','{"include":[],"exclude":[]}',NULL);
		$newTaskType[] = array('VTSendNotificationTask','Send Notification','VTSendNotificationTask','modules/com_vtiger_workflow/tasks/VTSendNotificationTask.inc','com_vtiger_workflow/taskforms/VTSendNotificationTask.tpl','{"include":["Calendar","Events"],"exclude":[]}',NULL);
		$newTaskType[] = array('VTAddressBookTask','Create Address Book','VTAddressBookTask','modules/com_vtiger_workflow/tasks/VTAddressBookTask.inc','com_vtiger_workflow/taskforms/VTAddressBookTask.tpl','{"include":["Contacts"],"exclude":[]}',NULL);
		
		foreach($newTaskType as $taskType){
			$taskTypeId = $adb->getUniqueID("com_vtiger_workflow_tasktypes");
			$adb->pquery("INSERT INTO com_vtiger_workflow_tasktypes (id, tasktypename, label, classname, classpath, templatepath, modules, sourcemodule) values (?,?,?,?,?,?,?,?)", array($taskTypeId, $taskType[0], $taskType[1], $taskType[2],  $taskType[3], $taskType[4], $taskType[5], $taskType[6]));
		}
		$log->debug("Exiting VT610_to_YT::addWorkflowType() method ...");
	}
	public function addWorkflow (){
		global $log, $adb;
		$log->debug("Entering VT610_to_YT::addWorkflow() method ...");
		
		$workflow = array();
		$workflow[] = array(1,'Invoice','UpdateInventoryProducts On Every Save','[{"fieldname":"subject","operation":"does not contain","value":"`!`"}]',3,1,'basic',5,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(13,'Events','Workflow for Events when Send Notification is True','[{"fieldname":"sendnotification","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',4,1,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(14,'Calendar','Workflow for Calendar Todos when Send Notification is True','[{"fieldname":"sendnotification","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',4,1,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(16,'PurchaseOrder','Update Inventory Products On Every Save',NULL,3,1,'basic',5,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(25,'HelpDesk','Ticket change: Send Email to Record Owner','[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',4,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(26,'HelpDesk','Ticket change: Send Email to Record Contact','[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(contact_id : (Contacts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',4,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(27,'HelpDesk','Ticket change: Send Email to Record Account','[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',4,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(28,'HelpDesk','Ticket Closed: Send Email to Record Owner','[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',4,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(29,'HelpDesk','Ticket Closed: Send Email to Record Contact','[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(contact_id : (Contacts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',4,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(30,'HelpDesk','Ticket Closed: Send Email to Record Account','[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',4,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(31,'HelpDesk','Ticket Creation: Send Email to Record Owner','[]',1,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(32,'HelpDesk','Ticket Creation: Send Email to Record Contact','[{"fieldname":"(contact_id : (Contacts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',1,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(33,'HelpDesk','Ticket Creation: Send Email to Record Account','[{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',1,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(34,'Potentials','Proces sprzedażowy - Weryfikacja danych','[{"fieldname":"sales_stage","operation":"is","value":"Data verification","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(35,'Potentials','Proces sprzedażowy - Wewnętrzna analiza Klienta','[{"fieldname":"sales_stage","operation":"is","value":"Customer internal analysis","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(36,'Potentials','Proces sprzedażowy - Pierwszy kontakt z Klientem','[{"fieldname":"sales_stage","operation":"is","value":"First contact with a customer","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(37,'Potentials','Proces sprzedażowy - Zaawansowana analiza biznesowa','[{"fieldname":"sales_stage","operation":"is","value":"Advanced business analysis","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(38,'Potentials','Proces sprzedażowy - Przygotowywanie kalkulacji','[{"fieldname":"sales_stage","operation":"is","value":"Preparation of calculations","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(39,'Potentials','Proces sprzedażowy - Przygotowywanie oferty','[{"fieldname":"sales_stage","operation":"is","value":"Preparation of offers","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(40,'Potentials','Proces sprzedażowy - Oczekiwanie na decyzje','[{"fieldname":"sales_stage","operation":"is","value":"Awaiting a decision","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(41,'Potentials','Proces sprzedażowy - Negocjacje','[{"fieldname":"sales_stage","operation":"is","value":"Negotiations","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(42,'Potentials','Proces sprzedażowy - Zamówienie i umowa','[{"fieldname":"sales_stage","operation":"is","value":"Order and contract","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(43,'Potentials','Proces sprzedażowy - Weryfikacja dokumentów','[{"fieldname":"sales_stage","operation":"is","value":"Documentation verification","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(44,'Potentials','Proces sprzedażowy - Sprzedaż wygrana - oczekiwanie na realizacje','[{"fieldname":"sales_stage","operation":"is","value":"Closed Waiting for processing","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(45,'Potentials','Proces sprzedażowy - Sprzedaż wygrana - realizacja zamówienia/umowy','[{"fieldname":"sales_stage","operation":"is","value":"Closed Order\\/contract processing","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(46,'Potentials','Proces sprzedażowy - Sprzedaż wygrana - działania posprzedażowe','[{"fieldname":"sales_stage","operation":"is","value":"Closed Presale activities","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(47,'Leads','Proces marketingowy - Weryfikacja danych','[{"fieldname":"leadstatus","operation":"is","value":"LBL_REQUIRES_VERIFICATION","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(48,'Leads','Proces marketingowy - Wstępna analiza','[{"fieldname":"leadstatus","operation":"is","value":"LBL_PRELIMINARY_ANALYSIS_OF","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(49,'Leads','Proces marketingowy - Zaawansowana analiza','[{"fieldname":"leadstatus","operation":"is","value":"LBL_ADVANCED_ANALYSIS","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(50,'Leads','Proces marketingowy - Wstępne pozyskanie','[{"fieldname":"leadstatus","operation":"is","value":"LBL_INITIAL_ACQUISITION","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(51,'Leads','Proces marketingowy - Kontakt w przyszłości','[{"fieldname":"leadstatus","operation":"is","value":"LBL_CONTACTS_IN_THE_FUTURE","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(52,'Contacts','Generate Customer Login Details','[{"fieldname":"portal","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',4,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(53,'Contacts','Send Customer Login Details','[{"fieldname":"emailoptout","operation":"is","value":"1","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"portal","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"portal","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',4,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(54,'HelpDesk','Update Closed Time','[{"fieldname":"ticketstatus","operation":"is","value":"Rejected","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(55,'Contacts','Generate mail address book','[]',3,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);

		$workflowTask = array();
		$workflowTask[] = array(1,1,'Update Inventory Products','O:18:"VTEntityMethodTask":6:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:1;s:7:"summary";s:0:"";s:6:"active";b:0;s:10:"methodName";s:15:"UpdateInventory";s:2:"id";i:1;}');
		$workflowTask[] = array(18,16,'Update Inventory Products','O:18:"VTEntityMethodTask":6:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:16;s:7:"summary";s:25:"Update Inventory Products";s:6:"active";b:0;s:10:"methodName";s:15:"UpdateInventory";s:2:"id";i:18;}');
		$workflowTask[] = array(38,34,'Weryfikacja danych','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"34";s:7:"summary";s:18:"Weryfikacja danych";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:18:"Weryfikacja danych";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:38;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(41,35,'Zapoznanie się z historią współpracy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"35";s:7:"summary";s:40:"Zapoznanie się z historią współpracy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:40:"Zapoznanie się z historią współpracy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"09:09";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:4:"High";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:41;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(42,35,'Zapoznanie się z aktualnościami na stronie Klienta','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"35";s:7:"summary";s:52:"Zapoznanie się z aktualnościami na stronie Klienta";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:52:"Zapoznanie się z aktualnościami na stronie Klienta";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:4:"High";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:42;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(43,35,'Zapoznanie się z aktualnościami społecznościowymi','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"35";s:7:"summary";s:53:"Zapoznanie się z aktualnościami społecznościowymi";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:53:"Zapoznanie się z aktualnościami społecznościowymi";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:4:"High";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:43;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(44,36,'Kontakt telefoniczny lub mailowy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"36";s:7:"summary";s:32:"Kontakt telefoniczny lub mailowy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:32:"Kontakt telefoniczny lub mailowy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:44;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(45,36,'Przypisanie osoby decyzyjnej do szansy sprzedaży','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"36";s:7:"summary";s:49:"Przypisanie osoby decyzyjnej do szansy sprzedaży";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:49:"Przypisanie osoby decyzyjnej do szansy sprzedaży";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:45;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(46,36,'Wstępna analiza potrzeb Klienta','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"36";s:7:"summary";s:32:"Wstępna analiza potrzeb Klienta";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:32:"Wstępna analiza potrzeb Klienta";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:46;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(47,36,'Uzupełnienie wstępnych ustaleń na szansie sprzedażowej','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"36";s:7:"summary";s:58:"Uzupełnienie wstępnych ustaleń na szansie sprzedażowej";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:58:"Uzupełnienie wstępnych ustaleń na szansie sprzedażowej";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:47;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(48,36,'Ustalenie terminu kolejnego kontaktu','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"36";s:7:"summary";s:36:"Ustalenie terminu kolejnego kontaktu";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:36:"Ustalenie terminu kolejnego kontaktu";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:48;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(49,36,'Wysłanie maila z podziękowaniem za rozmowę oraz podsumowaniem ustaleń','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"36";s:7:"summary";s:73:"Wysłanie maila z podziękowaniem za rozmowę oraz podsumowaniem ustaleń";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:73:"Wysłanie maila z podziękowaniem za rozmowę oraz podsumowaniem ustaleń";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:49;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(50,37,'Uzupełnienie informacji o: \'Zainteresowany produktami\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"37";s:7:"summary";s:55:"Uzupełnienie informacji o: \'Zainteresowany produktami\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:55:"Uzupełnienie informacji o: \'Zainteresowany produktami\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:50;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(51,37,'Uzupełnienie informacji o: \'Zainteresowany usługami\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"37";s:7:"summary";s:54:"Uzupełnienie informacji o: \'Zainteresowany usługami\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:54:"Uzupełnienie informacji o: \'Zainteresowany usługami\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:51;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(52,37,'Uzupełnienie informacji o: \'Produkty obce\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"37";s:7:"summary";s:43:"Uzupełnienie informacji o: \'Produkty obce\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:43:"Uzupełnienie informacji o: \'Produkty obce\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"09:24";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:52;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(53,37,'Uzupełnienie informacji o: \'Usługi obce\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"37";s:7:"summary";s:42:"Uzupełnienie informacji o: \'Usługi obce\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:42:"Uzupełnienie informacji o: \'Usługi obce\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"09:24";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:53;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(54,37,'Uzupełnienie dodatkowych ustaleń na szansie sprzedażowej','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"37";s:7:"summary";s:59:"Uzupełnienie dodatkowych ustaleń na szansie sprzedażowej";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:59:"Uzupełnienie dodatkowych ustaleń na szansie sprzedażowej";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"09:25";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:54;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(55,38,'Utworzenie kalkulacji o statusie \'Do przygotowania\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"38";s:7:"summary";s:51:"Utworzenie kalkulacji o statusie \'Do przygotowania\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:51:"Utworzenie kalkulacji o statusie \'Do przygotowania\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:55;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(56,38,'Monitorowanie przygotowywanych kalkulacji','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"38";s:7:"summary";s:41:"Monitorowanie przygotowywanych kalkulacji";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:41:"Monitorowanie przygotowywanych kalkulacji";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:56;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(57,38,'Weryfikacja przygotowanych kalkulacji','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"38";s:7:"summary";s:37:"Weryfikacja przygotowanych kalkulacji";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:37:"Weryfikacja przygotowanych kalkulacji";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:57;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(58,39,'Utworzenie oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"39";s:7:"summary";s:17:"Utworzenie oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:17:"Utworzenie oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:58;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(59,39,'Przygotowywanie oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"39";s:7:"summary";s:22:"Przygotowywanie oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:22:"Przygotowywanie oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:59;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(60,39,'Weryfikacja przygotowanej oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"39";s:7:"summary";s:32:"Weryfikacja przygotowanej oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:32:"Weryfikacja przygotowanej oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:60;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(61,39,'Akceptacja oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"39";s:7:"summary";s:17:"Akceptacja oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:17:"Akceptacja oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:61;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(62,39,'Wysyłka oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"39";s:7:"summary";s:15:"Wysyłka oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:15:"Wysyłka oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"09:32";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:62;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(63,40,'Monitorowanie decyzji w sprawie oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"40";s:7:"summary";s:38:"Monitorowanie decyzji w sprawie oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:38:"Monitorowanie decyzji w sprawie oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"09:34";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:63;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(64,41,'Utworzenie kalkulacji o statusie \'Do przygotowania\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"41";s:7:"summary";s:51:"Utworzenie kalkulacji o statusie \'Do przygotowania\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:51:"Utworzenie kalkulacji o statusie \'Do przygotowania\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:64;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(65,41,'Monitorowanie przygotowywanych kalkulacji','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"41";s:7:"summary";s:41:"Monitorowanie przygotowywanych kalkulacji";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:41:"Monitorowanie przygotowywanych kalkulacji";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:65;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(66,41,'Weryfikacja przygotowanych kalkulacji','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"41";s:7:"summary";s:37:"Weryfikacja przygotowanych kalkulacji";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:37:"Weryfikacja przygotowanych kalkulacji";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:66;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(67,41,'Utworzenie oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"41";s:7:"summary";s:17:"Utworzenie oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:17:"Utworzenie oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:67;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(68,41,'Przygotowywanie oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"41";s:7:"summary";s:22:"Przygotowywanie oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:22:"Przygotowywanie oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:68;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(69,41,'Weryfikacja przygotowanej oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"41";s:7:"summary";s:32:"Weryfikacja przygotowanej oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:32:"Weryfikacja przygotowanej oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:69;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(70,41,'Uzyskanie akceptacji dla oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"41";s:7:"summary";s:31:"Uzyskanie akceptacji dla oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:31:"Uzyskanie akceptacji dla oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:70;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(71,41,'Wysyłka oferty','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"41";s:7:"summary";s:15:"Wysyłka oferty";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:15:"Wysyłka oferty";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:71;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(72,42,'Tworzenie zamówienia/umowy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"42";s:7:"summary";s:27:"Tworzenie zamówienia/umowy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:27:"Tworzenie zamówienia/umowy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:72;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(73,42,'Weryfikacja od strony technicznej','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"42";s:7:"summary";s:33:"Weryfikacja od strony technicznej";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:33:"Weryfikacja od strony technicznej";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:73;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(74,42,'Weryfikacja od strony finansowej','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"42";s:7:"summary";s:32:"Weryfikacja od strony finansowej";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:32:"Weryfikacja od strony finansowej";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:74;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(75,42,'Weryfikacja od strony prawnej','O:16:"VTCreateTodoTask":17:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"42";s:7:"summary";s:29:"Weryfikacja od strony prawnej";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:29:"Weryfikacja od strony prawnej";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:1:"3";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:75;}');
		$workflowTask[] = array(76,42,'Uzyskanie akceptacji dla zamówienia/umowy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"42";s:7:"summary";s:42:"Uzyskanie akceptacji dla zamówienia/umowy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:42:"Uzyskanie akceptacji dla zamówienia/umowy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:76;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(77,42,'Wysyłka zamówienia/umowy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"42";s:7:"summary";s:26:"Wysyłka zamówienia/umowy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:26:"Wysyłka zamówienia/umowy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:77;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(78,42,'Monitorowanie otrzymania oryginałów podpisanych dokumentów','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"42";s:7:"summary";s:61:"Monitorowanie otrzymania oryginałów podpisanych dokumentów";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:61:"Monitorowanie otrzymania oryginałów podpisanych dokumentów";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"09:50";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:78;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(79,43,'Weryfikacja od strony finansowej','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"43";s:7:"summary";s:32:"Weryfikacja od strony finansowej";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:32:"Weryfikacja od strony finansowej";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:79;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(80,43,'Weryfikacja od strony prawnej','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"43";s:7:"summary";s:29:"Weryfikacja od strony prawnej";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:29:"Weryfikacja od strony prawnej";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:80;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(81,43,'Wysłanie informacji do Klienta w sprawie zamówienia/usługi','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"43";s:7:"summary";s:61:"Wysłanie informacji do Klienta w sprawie zamówienia/usługi";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:61:"Wysłanie informacji do Klienta w sprawie zamówienia/usługi";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:81;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(82,44,'Uzupełnienie informacji o: \'Produkty sprzedane\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"44";s:7:"summary";s:48:"Uzupełnienie informacji o: \'Produkty sprzedane\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:48:"Uzupełnienie informacji o: \'Produkty sprzedane\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:82;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(83,44,'Uzupełnienie informacji o: \'Usługi sprzedane\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"44";s:7:"summary";s:47:"Uzupełnienie informacji o: \'Usługi sprzedane\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:47:"Uzupełnienie informacji o: \'Usługi sprzedane\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:83;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(84,44,'Utworzenie projektów/zadań/etapów w celu realizacji zamówienia/umowy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"44";s:7:"summary";s:72:"Utworzenie projektów/zadań/etapów w celu realizacji zamówienia/umowy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:72:"Utworzenie projektów/zadań/etapów w celu realizacji zamówienia/umowy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:84;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(85,45,'Monitorowanie realizacji projektów','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"45";s:7:"summary";s:35:"Monitorowanie realizacji projektów";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:35:"Monitorowanie realizacji projektów";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:85;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(86,45,'Ogólna weryfikacja procesu sprzedażowego','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"45";s:7:"summary";s:42:"Ogólna weryfikacja procesu sprzedażowego";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:42:"Ogólna weryfikacja procesu sprzedażowego";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:86;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(87,46,'Ocena procesu realizacji przez Klienta','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"46";s:7:"summary";s:38:"Ocena procesu realizacji przez Klienta";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:38:"Ocena procesu realizacji przez Klienta";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:87;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(88,46,'Ocena Klienta pod względem współpracy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"46";s:7:"summary";s:40:"Ocena Klienta pod względem współpracy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:40:"Ocena Klienta pod względem współpracy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:88;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(89,46,'Wyznaczenie kolejnych kontaktów na najbliższe 6 miesięcy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"46";s:7:"summary";s:59:"Wyznaczenie kolejnych kontaktów na najbliższe 6 miesięcy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:59:"Wyznaczenie kolejnych kontaktów na najbliższe 6 miesięcy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:89;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(90,46,'Utworzenie nowej szansy sprzedaży z datą przyszłą','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"46";s:7:"summary";s:53:"Utworzenie nowej szansy sprzedaży z datą przyszłą";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:53:"Utworzenie nowej szansy sprzedaży z datą przyszłą";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:90;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(91,47,'Weryfikacja danych','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"47";s:7:"summary";s:18:"Weryfikacja danych";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:18:"Weryfikacja danych";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:91;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(92,48,'Zapoznanie się z aktualnościami na stronie','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"48";s:7:"summary";s:44:"Zapoznanie się z aktualnościami na stronie";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:44:"Zapoznanie się z aktualnościami na stronie";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:92;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(93,48,'Zapoznanie się z aktualnościami społecznościowymi','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"48";s:7:"summary";s:53:"Zapoznanie się z aktualnościami społecznościowymi";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:53:"Zapoznanie się z aktualnościami społecznościowymi";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:93;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(94,49,'Kontakt telefoniczny lub mailowy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:32:"Kontakt telefoniczny lub mailowy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:32:"Kontakt telefoniczny lub mailowy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:94;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(95,49,'Określenie osoby decyzyjnej','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:28:"Określenie osoby decyzyjnej";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:28:"Określenie osoby decyzyjnej";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:95;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(96,49,'Prezentacja doświadczenia firmy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:32:"Prezentacja doświadczenia firmy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:32:"Prezentacja doświadczenia firmy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:96;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(97,49,'Prezentacja produktów i usług','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:31:"Prezentacja produktów i usług";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:31:"Prezentacja produktów i usług";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:97;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(98,49,'Wstępna analiza potrzeb Klienta','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:32:"Wstępna analiza potrzeb Klienta";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:32:"Wstępna analiza potrzeb Klienta";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:98;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(99,49,'Uzupełnienie informacji o: \'Usługi obce\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:42:"Uzupełnienie informacji o: \'Usługi obce\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:42:"Uzupełnienie informacji o: \'Usługi obce\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:99;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(100,49,'Uzupełnienie informacji o: \'Produkty obce\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:43:"Uzupełnienie informacji o: \'Produkty obce\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:43:"Uzupełnienie informacji o: \'Produkty obce\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:100;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(101,49,'Uzupełnienie wstępnych ustaleń w systemie','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:44:"Uzupełnienie wstępnych ustaleń w systemie";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:44:"Uzupełnienie wstępnych ustaleń w systemie";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:101;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(102,50,'Uszczegółowienie potrzeb Klienta','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"50";s:7:"summary";s:34:"Uszczegółowienie potrzeb Klienta";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:34:"Uszczegółowienie potrzeb Klienta";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:102;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(103,50,'Uzupełnienie informacji o: \'Zainteresowany usługami\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"50";s:7:"summary";s:54:"Uzupełnienie informacji o: \'Zainteresowany usługami\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:54:"Uzupełnienie informacji o: \'Zainteresowany usługami\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:103;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(104,50,'Uzupełnienie informacji o: \'Zainteresowany produktami\'','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"50";s:7:"summary";s:55:"Uzupełnienie informacji o: \'Zainteresowany produktami\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:55:"Uzupełnienie informacji o: \'Zainteresowany produktami\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:104;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(105,51,'Kontakt telefoniczny lub mailowy','O:16:"VTCreateTodoTask":23:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"51";s:7:"summary";s:32:"Kontakt telefoniczny lub mailowy";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:32:"Kontakt telefoniczny lub mailowy";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:11:"Not Started";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:105;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";}');
		$workflowTask[] = array(106,33,'Notify Account On Ticket Create','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"33";s:7:"summary";s:31:"Notify Account On Ticket Create";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"40";s:11:"attachments";s:0:"";s:5:"email";s:25:"parent_id=Accounts=email1";s:10:"copy_email";s:0:"";s:2:"id";i:106;}');
		$workflowTask[] = array(107,32,'Notify Contact On Ticket Create','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"32";s:7:"summary";s:31:"Notify Contact On Ticket Create";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"39";s:11:"attachments";s:0:"";s:5:"email";s:25:"contact_id=Contacts=email";s:10:"copy_email";s:0:"";s:2:"id";i:107;}');
		$workflowTask[] = array(108,31,'Notify Owner On Ticket Create','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"31";s:7:"summary";s:29:"Notify Owner On Ticket Create";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"43";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:108;}');
		$workflowTask[] = array(109,30,'Notify Account On Ticket Closed','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"30";s:7:"summary";s:31:"Notify Account On Ticket Closed";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"38";s:11:"attachments";s:0:"";s:5:"email";s:25:"parent_id=Accounts=email1";s:10:"copy_email";s:0:"";s:2:"id";i:109;}');
		$workflowTask[] = array(110,29,'Notify Contact On Ticket Closed','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"29";s:7:"summary";s:31:"Notify Contact On Ticket Closed";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"37";s:11:"attachments";s:0:"";s:5:"email";s:25:"contact_id=Contacts=email";s:10:"copy_email";s:0:"";s:2:"id";i:110;}');
		$workflowTask[] = array(111,28,'Notify Owner On Ticket Closed','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"28";s:7:"summary";s:29:"Notify Owner On Ticket Closed";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"42";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:111;}');
		$workflowTask[] = array(112,27,'Notify Account On Ticket Change','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"27";s:7:"summary";s:31:"Notify Account On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"36";s:11:"attachments";s:0:"";s:5:"email";s:25:"parent_id=Accounts=email1";s:10:"copy_email";s:0:"";s:2:"id";i:112;}');
		$workflowTask[] = array(113,26,'Notify Contact On Ticket Change','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"26";s:7:"summary";s:31:"Notify Contact On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"41";s:11:"attachments";s:0:"";s:5:"email";s:25:"contact_id=Contacts=email";s:10:"copy_email";s:0:"";s:2:"id";i:113;}');
		$workflowTask[] = array(114,25,'Notify Owner On Ticket Change','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"25";s:7:"summary";s:29:"Notify Owner On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"35";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:114;}');
		$workflowTask[] = array(116,52,'Create Portal Login Details','O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"52";s:7:"summary";s:27:"Create Portal Login Details";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:24:"CreatePortalLoginDetails";s:2:"id";i:116;}');
		$workflowTask[] = array(119,14,'Notification Email to Record Owner','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"14";s:7:"summary";s:34:"Notification Email to Record Owner";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"46";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:119;}');
		$workflowTask[] = array(120,53,'Send Customer Login Details','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"53";s:7:"summary";s:27:"Send Customer Login Details";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"44";s:11:"attachments";s:0:"";s:5:"email";s:5:"email";s:10:"copy_email";s:0:"";s:2:"id";i:120;}');
		$workflowTask[] = array(121,54,'Update Closed Time','O:18:"VTUpdateClosedTime":6:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"54";s:7:"summary";s:18:"Update Closed Time";s:6:"active";b:1;s:7:"trigger";N;s:2:"id";i:121;}');
		$workflowTask[] = array(122,13,'Send invitations','O:22:"VTSendNotificationTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"13";s:7:"summary";s:16:"Send invitations";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"45";s:2:"id";i:122;}');
		$workflowTask[] = array(123,55,'Generate mail address book','O:17:"VTAddressBookTask":7:{s:18:"executeImmediately";b:0;s:10:"workflowId";s:2:"55";s:7:"summary";s:26:"Generate mail address book";s:6:"active";b:1;s:7:"trigger";N;s:4:"test";s:0:"";s:2:"id";i:123;}');

		$workflowManager = new VTWorkflowManager($adb);
		$taskManager = new VTTaskManager($adb);
		foreach($workflow as $record){
			$newWorkflow = $workflowManager->newWorkFlow($record[1]);
			$newWorkflow->description = $record[2];
			$newWorkflow->test = $record[3];
			$newWorkflow->executionCondition = $record[4];
			$newWorkflow->defaultworkflow = $record[5];
			$newWorkflow->type = $record[6];
			$newWorkflow->filtersavedinnew = $record[7];
			$workflowManager->save($newWorkflow);
			foreach($workflowTask as $indexTask){
				if($indexTask[1] == $record[0]){
					$task = $taskManager->unserializeTask($indexTask[3]);
					$task->id = '';
					$task->workflowId = $newWorkflow->id;
					$taskManager->saveTask($task);
				}
			}
		}
		$log->debug("Exiting VT610_to_YT::addWorkflow() method ...");
	}

	public function blocksTable(){
		global $log;
		$log->debug("Entering VT610_to_YT::blocksTable() method ...");
		// add Blocks
		$blockColumnName = array('blocklabel','sequence','show_title','visible','create_view','edit_view','detail_view','display_status','iscustom');
		$blocksOSSPdf = array(array('LBL_MAIN_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_FOOTER_HEADER',2,0,0,0,0,0,1,0),array('HEADER',3,0,0,0,0,0,1,0),array('CONTENT',4,0,0,0,0,0,1,0),array('FOOTER',5,0,0,0,0,0,1,0),array('LBL_CUSTOM_INFORMATION',6,0,0,0,0,0,1,0));
		$blocksOSSMailTemplates = array(array('LBL_OSSMAILTEMPLATES_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_CONTENT_INFORMATION',2,0,0,0,0,0,1,0));
		$blocksOSSTimeControl = array(array('LBL_MAIN_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_BLOCK',2,0,0,0,0,0,1,0),array('LBL_DESCRIPTION_INFORMATION',3,0,0,0,0,0,1,0));
		$blocksOSSMailView = array(array('LBL_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_CUSTOM_INFORMATION',2,0,0,0,0,0,1,0),array('LBL_DESCRIPTION_INFORMATION',3,0,0,0,0,0,0,0),array('Oryginalna wiadomość',4,0,0,0,0,0,0,0));
		$blocksOSSOutsourcedServices = array(array('LBL_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_CUSTOM_INFORMATION',2,0,0,0,0,0,1,0),array('LBL_DESCRIPTION_INFORMATION',3,0,0,0,0,0,1,0));
		$blocksOSSSoldServices = array(array('LBL_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_CUSTOM_INFORMATION',2,0,0,0,0,0,1,0),array('LBL_DESCRIPTION_INFORMATION',3,0,0,0,0,0,1,0));
		$blocksOutsourcedProducts = array(array('LBL_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_CUSTOM_INFORMATION',2,0,0,0,0,0,1,0),array('LBL_DESCRIPTION_INFORMATION',3,0,0,0,0,0,1,0));
		$blocksOSSPasswords = array(array('LBL_OSSPASSWORD_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_CUSTOM_INFORMATION',2,0,0,0,0,0,1,0),array('LBL_DESCRIPTION_INFORMATION',3,0,0,0,0,0,1,0));
		$blocksLeads = array(array('Contact Information',2,0,0,0,0,0,1,1),array('LBL_REGISTRATION_INFO',3,0,0,0,0,0,0,1));
		$blocksOSSEmployees = array(array('LBL_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_CONTACTS',2,0,0,0,0,0,1,0),array('LBL_PERMANENT_ADDRESS',3,0,0,0,0,0,1,0),array('LBL_CORRESPONDANCE_ADDRESS',4,0,0,0,0,0,1,0));
		$blocksVendors = array(array('LBL_ADDRESS_DELIVERY_INFORMATION',4,0,0,0,0,0,1,1));
		$blocksCalculations = array(array('LBL_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_CUSTOM_INFORMATION',2,0,0,0,0,0,1,0),array('LBL_DESCRIPTION_INFORMATION',3,0,0,0,0,0,1,0),array('LBL_PRODUCT_INFORMATION',4,0,0,0,0,0,1,0));
		$blocksOSSCosts = array(array('LBL_INFORMATION',1,0,0,0,0,0,1,0),array('LBL_ADDRESS_INFORMATION',2,0,0,0,0,0,1,0),array('LBL_CUSTOM_INFORMATION',3,0,0,0,0,0,1,0),array('LBL_DESCRIPTION_INFORMATION',4,0,0,0,0,0,1,0));
		$blocksServiceContracts = array(array('LBL_SUMMARY',2,0,0,0,0,0,1,1),array('BLOCK_INFORMATION_TIME',3,0,0,0,0,0,1,1));
		$blocksAccounts = array(array('LBL_ADDRESS_DELIVERY_INFORMATION',9,0,0,0,0,0,0,1),array('LBL_REGISTRATION_INFO',5,0,0,0,0,0,1,1),array('LBL_CONTACT_INFO',2,0,0,0,0,0,1,1),array('LBL_ADVANCED_BLOCK',4,0,0,0,0,0,1,1),array('LBL_FINANSIAL_SUMMARY',3,0,0,0,0,0,1,1));
		$blocksContacts = array(array('LBL_ADDRESS_MAILING_INFORMATION',7,0,0,0,0,0,0,1),array('LBL_CONTACT_INFO',2,0,0,0,0,0,1,1));
		$blocksPotentials = array(array('LBL_SUMMARY',6,0,0,0,0,0,1,0),array('LBL_FINANSIAL_SUMMARY',2,0,0,0,0,0,1,1));
		$blocksProject = array(array('LBL_SUMMARY',5,0,0,0,0,0,1,0));
		$blocksAssets = array(array('BLOCK_INFORMATION_TIME',3,0,0,0,0,0,1,1));

		$moduleBlocks = array(
		'OSSPdf'=>$blocksOSSPdf,
		'OSSMailTemplates'=>$blocksOSSMailTemplates,
		'OSSTimeControl'=>$blocksOSSTimeControl,
		'OSSMailView'=>$blocksOSSMailView,
		'OSSOutsourcedServices'=>$blocksOSSOutsourcedServices,
		'OSSSoldServices'=>$blocksOSSSoldServices,
		'OutsourcedProducts'=>$blocksOutsourcedProducts,
		'OSSPasswords'=>$blocksOSSPasswords,
		'Leads'=>$blocksLeads,
		'OSSEmployees'=>$blocksOSSEmployees,
		'Vendors'=>$blocksVendors,
		'Calculations'=>$blocksCalculations,
		'OSSCosts'=>$blocksOSSCosts,
		'ServiceContracts'=>$blocksServiceContracts,
		'Accounts'=>$blocksAccounts,
		'Contacts'=>$blocksContacts,
		'Potentials'=>$blocksPotentials,
		'Project'=>$blocksProject,
		'Assets'=>$blocksAssets
		);

		$setBlockToCRM = array();
		foreach($moduleBlocks as $nameModule=>$module){
			foreach($module as $key=>$fieldValues){
				for($i=0;$i<count($fieldValues);$i++){
					$setBlockToCRM[$nameModule][$key][$blockColumnName[$i]] = $fieldValues[$i];
				}
			}
		}
		$log->debug("Exiting VT610_to_YT::blocksTable() method ...");
		return $setBlockToCRM;
	}

	public function addBlocks(){
		global $log;
		$log->debug("Entering VT610_to_YT::addBlocks() method ...");
		include_once('vtlib/Vtiger/Module.php'); 
		
		$adb = PearDatabase::getInstance();
		try {
			$sql = "UPDATE vtiger_blocks SET `blocklabel` = 'LBL_ADDRESS_MAILING_INFORMATION' WHERE `tabid` IN (?,?) AND `blocklabel` = 'LBL_CUSTOM_INFORMATION';";
			$result = $adb->pquery($sql, array(6,18), true);
			$sql = "UPDATE vtiger_blocks SET `blocklabel` = 'LBL_ADDRESS_INFORMATION' WHERE `tabid` = ? AND `blocklabel` = 'LBL_VENDOR_ADDRESS_INFORMATION';";
			$result = $adb->pquery($sql, array(18), true);
			$sql = "UPDATE vtiger_blocks SET `blocklabel` = 'LBL_ADDRESS_DELIVERY_INFORMATION' WHERE `tabid` IN (?,?,?,?) AND `blocklabel` = 'LBL_CUSTOM_INFORMATION';";
			$result = $adb->pquery($sql, array(20,21,22,23), true);
			//delete
			$sql = "DELETE FROM vtiger_blocks WHERE `blocklabel` = ? AND `tabid` = ?;";
			$result = $adb->pquery($sql, array('LBL_ADDRESS_INFORMATION',29), true);
			$sql = "DELETE FROM vtiger_tab WHERE `name` = ? AND `tabid` = ?;";
			$result = $adb->pquery($sql, array('MailManager',31), true);
		} catch (Exception $e) {
			Install_InitSchema_Model::addMigrationLog('addBlocks '.$e->getMessage(),'error');
		}
		
		$setBlockToCRM = self::blocksTable();
		foreach($setBlockToCRM as $moduleName=>$blocks){
			foreach($blocks as $block){
				if(self::checkBlockExists($moduleName, $block))
					continue;
				try {
					$moduleInstance = Vtiger_Module::getInstance($moduleName);
					$blockInstance = new Vtiger_Block();
					$blockInstance->label = $block['blocklabel'];
					$blockInstance->sequence = $block['sequence'];
					$blockInstance->showtitle = $block['show_title'];
					$blockInstance->visible = $block['visible'];
					$blockInstance->increateview = $block['create_view'];
					$blockInstance->ineditview = $block['edit_view'];
					$blockInstance->indetailview = $block['detail_view'];
					$blockInstance->iscustom = $block['iscustom'];
					$moduleInstance->addBlock($blockInstance);
				} catch (Exception $e) {
					Install_InitSchema_Model::addMigrationLog('addBlocks '.$e->getMessage(),'error');
				}
			}
		}
		Install_InitSchema_Model::addMigrationLog('addBlocks');
		$log->debug("Exiting VT610_to_YT::addBlocks() method ...");
	}
	public function checkBlockExists($moduleName, $block){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::checkBlockExists() method ...");
		
		if($moduleName == 'Settings')
			$result = $adb->pquery("SELECT * FROM vtiger_settings_blocks WHERE label = ? ;", array($block), true);
		else
			$result = $adb->pquery("SELECT * FROM vtiger_blocks WHERE tabid = ? AND blocklabel = ? ;", array(getTabid($moduleName),$block['blocklabel']));

		if(!$adb->num_rows($result)) {
			$log->debug("Exiting VT610_to_YT::checkBlockExists() method ...");
			return false;
		}
		$log->debug("Exiting VT610_to_YT::checkBlockExists() method ...");
		return true;
	}
	public function getFieldsAll(){
		global $log;
		$log->debug("Entering VT610_to_YT::getFieldsAll() method ...");
		$columnName = array("tabid","id","column","table","generatedtype","uitype","name","label","readonly","presence","defaultvalue","maximumlength","sequence","block","displaytype","typeofdata","quickcreate","quicksequence","info_type","masseditable","helpinfo","summaryfield","columntype","blocklabel","setpicklistvalues","setrelatedmodules");

		$tab = 8;
		$Documents = array(
		array("8","865","ossdc_status","vtiger_notes","1","15","ossdc_status","ossdc_status","1","2","","100","13","17","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_NOTE_INFORMATION",array("None","Checked"))
		);

		$tab = 42;
		$ProjectTask = array(
		array("42","816","sum_time","vtiger_projecttask","1","7","sum_time","Total time [h]","1","2","","100","10","104","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_PROJECT_TASK_INFORMATION"),
		array("42","1318","parentid","vtiger_projecttask","2","10","parentid","Parent ID","1","2","","100","11","104","1","V~O","1", "","BAS","1","0","0","int(19)","LBL_PROJECT_TASK_INFORMATION",array(),array('ProjectTask')),
		array("42","1319","projectmilestoneid","vtiger_projecttask","2","10","projectmilestoneid","ProjectMilestone","1","2","","100","12","104","1","V~M","2", "","BAS","1","0","0","int(19)","LBL_PROJECT_TASK_INFORMATION",array(),array('ProjectMilestone')),
		array("42","1320","targetenddate","vtiger_projecttask","2","5","targetenddate","Target End Date","1","2","","100","6","105","1","D~O","1", "","BAS","1","0","0","date","LBL_CUSTOM_INFORMATION"),
		array("42","747","smcreatorid","vtiger_crmentity","1","52","created_user_id","Created By","1","2","","100","9","104","2","V~O","3","7","BAS","0","0","0","int(19)","LBL_PROJECT_TASK_INFORMATION")
		);

		$tab = 14;
		$Products = array(
		array("14","178","pscategory","vtiger_products","1","302","pscategory","Product Category","1","2","","100","6","31","1","V~O","2","5","BAS","1","0","1","varchar(200)","LBL_PRODUCT_INFORMATION"),
		array("14","1392","inheritsharing","vtiger_crmentity","1","56","inheritsharing","Copy permissions automatically","1","2","","100","24","31","1","C~O","1", "","BAS","1","0","0","tinyint(1)","LBL_PRODUCT_INFORMATION")
		);

		$tab = 34;
		$ServiceContracts = array(
		array("34","820","sum_time","vtiger_servicecontracts","1","7","sum_time","Total time [Service Contract]","1","2","","100","3","180","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_SUMMARY"),
		array("34","1046","sum_time_p","vtiger_servicecontracts","1","7","sum_time_p","Total time [Projects]","1","2","","100","2","180","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("34","1047","sum_time_h","vtiger_servicecontracts","1","7","sum_time_h","Total time [Tickets]","1","2","","100","1","180","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("34","1048","sum_time_all","vtiger_servicecontracts","1","7","sum_time_all","Total time [Sum]","1","2","","100","4","180","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array('34','742','smcreatorid','vtiger_crmentity','1','52','created_user_id','Created By','1','2','','100','19','89','2','V~O','3','10','BAS','0','0','0',"int(19)","LBL_SERVICE_CONTRACT_INFORMATION")

		);

		$tab = 35;
		$Services = array(
		array("35","574","pscategory","vtiger_service","1","302","pscategory","Service Category","1","2","","100","7","91","1","V~O","2","3","BAS","1","0","1","varchar(200)","LBL_SERVICE_INFORMATION"),
		array("35","1394","inheritsharing","vtiger_crmentity","1","56","inheritsharing","Copy permissions automatically","1","2","","100","19","91","1","C~O","1", "","BAS","1","0","0","tinyint(1)","LBL_SERVICE_INFORMATION"),
		array('35','743','smcreatorid','vtiger_crmentity','1','52','created_user_id','Created By','1','2','','100','17','91','2','V~O','3','4','BAS','0','0','0',"int(19)","LBL_SERVICE_INFORMATION")
		);

		$tab = 43;
		$Project = array(
		array("43","826","sum_time","vtiger_project","1","7","sum_time","Total time [Project]","1","2","","100","1","132","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_SUMMARY"),
		array('43','828','sum_time_pt','vtiger_project','1','7','sum_time_pt','Total time [Project Task]','1','2','','100','3','132','10','NN~O','1','','BAS','1','0','0',"decimal(10,2)","LBL_SUMMARY"),
		array("43","830","sum_time_h","vtiger_project","1","7","sum_time_h","Total time [Tickets]","1","2","","100","5","132","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_SUMMARY"),
		array("43","832","sum_time_all","vtiger_project","1","7","sum_time_all","Total time [Sum]","1","2","","100","7","132","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_SUMMARY"),
		array("43","1044","servicecontractsid","vtiger_project","2","10","servicecontractsid","ServiceContracts","1","2","","100","11","107","1","V~O","1", "","BAS","1","0","0","int(19)","LBL_PROJECT_INFORMATION",array(),array('ServiceContracts')),
		array("43","1340","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","109","1","V~O","1","","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION"),
		array("43","748","smcreatorid","vtiger_crmentity","1","52","created_user_id","Created By","1","2","","100","10","107","2","V~O","3","5","BAS","0","0","0","int(19)","LBL_PROJECT_INFORMATION")
		);

		$Users = array(
		array("29","1322","end_hour","vtiger_users","1","16","end_hour","Day ends at","1","2","","100","4","118","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_CALENDAR_SETTINGS",array("00:00","01:00","02:00","03:00","04:00","05:00","06:00","07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00","21:00","22:00","23:00"))
		);
		
		$tab = 40;
		$ModComments = array(
		array("40","745","smcreatorid","vtiger_crmentity","1","52","created_user_id","Created By","1","2","","100","8","98","2","V~O","3","6","BAS","0","0","0","int(19)","LBL_MODCOMMENTS_INFORMATION")
		);

		$tab = 13;
		$HelpDesk = array(
		array("13","814","sum_time","vtiger_troubletickets","1","7","sum_time","Total time [h]","1","2","","100","21","25","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_TICKET_INFORMATION"),
		array("13","1043","projectid","vtiger_troubletickets","2","10","projectid","Project","1","2","","100","22","25","1","V~O","2", "","BAS","1","0","0","int(19)","LBL_TICKET_INFORMATION",array(),array('Project')),
		array("13","1049","servicecontractsid","vtiger_troubletickets","2","10","servicecontractsid","ServiceContracts","1","2","","100","23","25","1","V~O","1", "","BAS","1","0","0","int(19)","LBL_TICKET_INFORMATION",array(),array('ServiceContracts')),
		array("13","1341","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","28","1","V~O","1", "","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION"),
		array('13','1482','pssold_id','vtiger_troubletickets','2','10','pssold_id','P&S Sold','1','2','','100','25','25','1','V~O','1','','BAS','1','0','0',"int(19)","LBL_TICKET_INFORMATION",array(),array('Assets','OSSSoldServices')),
		array('13','1483','ordertime','vtiger_troubletickets','2','7','ordertime','LBL_ORDER_TIME','1','2','','100','26','25','1','NN~O','1','','BAS','1','0','0',"decimal(10,2)","LBL_TICKET_INFORMATION")
		);

		$tab = 7;
		$Leads = array(
		array("7","1065","addresslevel1a","vtiger_leadaddress","1","1","addresslevel1a","AddressLevel1","1","2","","100","9","15","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("7","1066","addresslevel2a","vtiger_leadaddress","1","1","addresslevel2a","AddressLevel2","1","2","","100","7","15","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("7","1067","addresslevel3a","vtiger_leadaddress","1","1","addresslevel3a","AddressLevel3","1","2","","100","10","15","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("7","1068","addresslevel4a","vtiger_leadaddress","1","1","addresslevel4a","AddressLevel4","1","2","","100","8","15","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("7","1069","addresslevel5a","vtiger_leadaddress","1","1","addresslevel5a","AddressLevel5","1","2","","100","3","15","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("7","1070","addresslevel6a","vtiger_leadaddress","1","1","addresslevel6a","AddressLevel6","1","2","","100","6","15","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("7","1071","addresslevel7a","vtiger_leadaddress","1","1","addresslevel7a","AddressLevel7","1","2","","100","5","15","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("7","1072","addresslevel8a","vtiger_leadaddress","1","1","addresslevel8a","AddressLevel8","1","2","","100","1","15","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("7","1291","buildingnumbera","vtiger_leadaddress","1","1","buildingnumbera","Building number","1","2","","100","2","15","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("7","1292","localnumbera","vtiger_leadaddress","1","1","localnumbera","Local number","1","2","","100","4","15","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("7","40","phone","vtiger_leadaddress","1","11","phone","Phone","1","2","","100","1","150","1","V~O","2","4","BAS","1","0","1","varchar(50)","Contact Information"),
		array("7","42","mobile","vtiger_leadaddress","1","11","mobile","Mobile","1","2","","100","3","150","1","V~O","1", "","BAS","1","0","0","varchar(50)","Contact Information"),
		array("7","44","fax","vtiger_leadaddress","1","11","fax","Fax","1","2","","100","5","150","1","V~O","1", "","BAS","1","0","0","varchar(50)","Contact Information"),
		array("7","1321","subindustry","vtiger_leaddetails","2","16","subindustry","Sub industry","1","2","","100","5","13","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_LEAD_INFORMATION",array("Ministry","Chancellery","Voivodeship Office","Marshal Office","Poviat","City/Township/District","Social Welfare Centre","Water and Sewerage Company","Voivodeship Job Centre","Poviat Job Centre","Court of justice","Attorney General's Office","Other","Deweloperzy","Real Estate","Primary Schools","High Schools","Banking","Capital Market","Financial Services","Investments","Insurance","Retail","Wholesale","Resale","Automotive","Plastics","Chamical","Raw material","Fuel","Wood and paper","Electromechanical","Pharmaceutical","Building Materials","Metal","Light","Food industry","Recycling","Army","Police","Information Technology","Telecommunication","Media")),
		array("7","1276","verification","vtiger_leaddetails","2","33","verification","Werification data","1","2","","100","4","14","1","V~O","1", "","BAS","1","0","0","text","LBL_CUSTOM_INFORMATION",array("Address details","Contact details","Registration details")),
		array("7","938","noapprovalcalls","vtiger_leaddetails","1","56","noapprovalcalls","Approval for phone calls","1","2","","100","7","150","1","C~O","1", "","BAS","1","0","0","varchar(3)","Contact Information"),
		array("7","940","noapprovalemails","vtiger_leaddetails","1","56","noapprovalemails","Approval for email","1","2","","100","8","150","1","C~O","1", "","BAS","1","0","0","varchar(3)","Contact Information"),
		array("7","967","vat_id","vtiger_leaddetails","1","1","vat_id","Vat ID","1","2","","100","6","13","1","V~M","2", "","BAS","1","0","0","varchar(30)","LBL_LEAD_INFORMATION"),
		array("7","968","registration_number_1","vtiger_leaddetails","1","1","registration_number_1","Registration number 1","1","2","","100","3","191","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_REGISTRATION_INFO"),
		array("7","969","registration_number_2","vtiger_leaddetails","1","1","registration_number_2","Registration number 2","1","2","","100","2","191","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_REGISTRATION_INFO"),
		array("7","1329","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","16","1","V~O","1","","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION")
		);

		$tab = 4;
		$Contacts = array(
		array("4","1073","addresslevel1a","vtiger_contactaddress","1","1","addresslevel1a","AddressLevel1","1","2","","100","10","7","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("4","1074","addresslevel1b","vtiger_contactaddress","1","1","addresslevel1b","AddressLevel1","1","2","","100","10","178","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("4","1075","addresslevel2a","vtiger_contactaddress","1","1","addresslevel2a","AddressLevel2","1","2","","100","9","7","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("4","1076","addresslevel2b","vtiger_contactaddress","1","1","addresslevel2b","AddressLevel2","1","2","","100","9","178","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("4","1077","addresslevel3a","vtiger_contactaddress","1","1","addresslevel3a","AddressLevel3","1","2","","100","8","7","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("4","1078","addresslevel3b","vtiger_contactaddress","1","1","addresslevel3b","AddressLevel3","1","2","","100","8","178","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("4","1079","addresslevel4a","vtiger_contactaddress","1","1","addresslevel4a","AddressLevel4","1","2","","100","7","7","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("4","1080","addresslevel4b","vtiger_contactaddress","1","1","addresslevel4b","AddressLevel4","1","2","","100","7","178","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("4","1081","addresslevel5a","vtiger_contactaddress","1","1","addresslevel5a","AddressLevel5","1","2","","100","6","7","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("4","1082","addresslevel5b","vtiger_contactaddress","1","1","addresslevel5b","AddressLevel5","1","2","","100","6","178","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("4","1083","addresslevel6a","vtiger_contactaddress","1","1","addresslevel6a","AddressLevel6","1","2","","100","5","7","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("4","1084","addresslevel6b","vtiger_contactaddress","1","1","addresslevel6b","AddressLevel6","1","2","","100","5","178","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("4","1085","addresslevel7a","vtiger_contactaddress","1","1","addresslevel7a","AddressLevel7","1","2","","100","4","7","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("4","1086","addresslevel7b","vtiger_contactaddress","1","1","addresslevel7b","AddressLevel7","1","2","","100","4","178","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("4","1087","addresslevel8a","vtiger_contactaddress","1","1","addresslevel8a","AddressLevel8","1","2","","100","3","7","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("4","1088","addresslevel8b","vtiger_contactaddress","1","1","addresslevel8b","AddressLevel8","1","2","","100","3","178","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("4","1293","buildingnumbera","vtiger_contactaddress","1","1","buildingnumbera","Building number","1","2","","100","1","7","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("4","1294","localnumbera","vtiger_contactaddress","1","1","localnumbera","Local number","1","2","","100","2","7","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("4","1295","buildingnumberb","vtiger_contactaddress","1","1","buildingnumberb","Building number","1","2","","100","1","178","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("4","1296","localnumberb","vtiger_contactaddress","1","1","localnumberb","Local number","1","2","","100","2","178","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("4","1278","verification","vtiger_contactdetails","2","33","verification","Werification data","1","2","","100","3","5","1","V~O","1", "","BAS","1","0","0","text","LBL_CUSTOM_INFORMATION"),
		array("4","72","parentid","vtiger_contactdetails","2","10","parent_id","Member Of","1","2","","100","6","4","1","I~O","2","6","BAS","1","0","1","int(19)","LBL_CONTACT_INFORMATION",array(),array('Accounts','Leads')),
		array("4","1368","secondary_email","vtiger_contactdetails","2","13","secondary_email","Secondary Email","1","2","","100","4","197","1","E~O","1", "","BAS","1","0","0","varchar(50)","LBL_CONTACT_INFO"),
		array("4","1391","notifilanguage","vtiger_contactdetails","2","32","notifilanguage","LBL_LANGUAGE_NOTIFICATIONS","1","2","","100","4","6","1","V~O","1", "","BAS","1","0","0","varchar(100)","LBL_CUSTOMER_PORTAL_INFORMATION"),
		array("4","1332","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","8","1","V~O","1","","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION"),
		array('4','1503','contactstatus','vtiger_contactdetails','2','15','contactstatus','Status','1','2','','100','29','4','1','V~O','1',NULL,'BAS','1','0','0',"varchar(255)","LBL_CONTACT_INFORMATION", array('Active','Inactive'))
		);

		$tab = 6;
		$Accounts = array(
		array("6","1089","addresslevel1a","vtiger_accountaddress","1","1","addresslevel1a","AddressLevel1","1","2","","100","10","11","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("6","1090","addresslevel1b","vtiger_accountaddress","1","1","addresslevel1b","AddressLevel1","1","2","","100","10","10","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("6","1091","addresslevel2a","vtiger_accountaddress","1","1","addresslevel2a","AddressLevel2","1","2","","100","9","11","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("6","1092","addresslevel2b","vtiger_accountaddress","1","1","addresslevel2b","AddressLevel2","1","2","","100","9","10","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("6","1093","addresslevel3a","vtiger_accountaddress","1","1","addresslevel3a","AddressLevel3","1","2","","100","8","11","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("6","1094","addresslevel3b","vtiger_accountaddress","1","1","addresslevel3b","AddressLevel3","1","2","","100","8","10","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("6","1095","addresslevel4a","vtiger_accountaddress","1","1","addresslevel4a","AddressLevel4","1","2","","100","7","11","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("6","1096","addresslevel4b","vtiger_accountaddress","1","1","addresslevel4b","AddressLevel4","1","2","","100","7","10","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("6","1097","addresslevel5a","vtiger_accountaddress","1","1","addresslevel5a","AddressLevel5","1","2","","100","6","11","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("6","1098","addresslevel5b","vtiger_accountaddress","1","1","addresslevel5b","AddressLevel5","1","2","","100","6","10","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("6","1099","addresslevel6a","vtiger_accountaddress","1","1","addresslevel6a","AddressLevel6","1","2","","100","5","11","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("6","1100","addresslevel6b","vtiger_accountaddress","1","1","addresslevel6b","AddressLevel6","1","2","","100","5","10","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("6","1101","addresslevel7a","vtiger_accountaddress","1","1","addresslevel7a","AddressLevel7","1","2","","100","4","11","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("6","1102","addresslevel7b","vtiger_accountaddress","1","1","addresslevel7b","AddressLevel7","1","2","","100","4","10","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("6","1103","addresslevel8a","vtiger_accountaddress","1","1","addresslevel8a","AddressLevel8","1","2","","100","3","11","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("6","1104","addresslevel8b","vtiger_accountaddress","1","1","addresslevel8b","AddressLevel8","1","2","","100","3","10","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("6","1285","buildingnumbera","vtiger_accountaddress","2","1","buildingnumbera","Building number","1","2","","100","1","11","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("6","1286","localnumbera","vtiger_accountaddress","2","1","localnumbera","Local number","1","2","","100","2","11","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("6","1290","localnumberc","vtiger_accountaddress","2","1","localnumberc","Local number","1","2","","100","2","181","1","V~O~LE~100","1","","BAS","1","0","0","varchar(100)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("6","1287","buildingnumberb","vtiger_accountaddress","2","1","buildingnumberb","Building number","1","2","","100","1","10","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("6","1289","buildingnumberc","vtiger_accountaddress","2","1","buildingnumberc","Building number","1","2","","100","1","181","1","V~O~LE~100","1","","BAS","1","0","0","varchar(100)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("6","1288","localnumberb","vtiger_accountaddress","2","1","localnumberb","Local number","1","2","","100","2","10","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("6","1160","addresslevel1c","vtiger_accountaddress","1","1","addresslevel1c","AddressLevel1","1","2","","100","10","181","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("6","1161","addresslevel2c","vtiger_accountaddress","1","1","addresslevel2c","AddressLevel2","1","2","","100","9","181","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("6","1162","addresslevel3c","vtiger_accountaddress","1","1","addresslevel3c","AddressLevel3","1","2","","100","8","181","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("6","1163","addresslevel4c","vtiger_accountaddress","1","1","addresslevel4c","AddressLevel4","1","2","","100","7","181","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("6","1164","addresslevel5c","vtiger_accountaddress","1","1","addresslevel5c","AddressLevel5","1","2","","100","6","181","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("6","1165","addresslevel6c","vtiger_accountaddress","1","1","addresslevel6c","AddressLevel6","1","2","","100","5","181","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("6","1166","addresslevel7c","vtiger_accountaddress","1","1","addresslevel7c","AddressLevel7","1","2","","100","4","181","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("6","1167","addresslevel8c","vtiger_accountaddress","1","1","addresslevel8c","AddressLevel8","1","2","","100","3","181","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("6","1277","verification","vtiger_account","2","33","verification","Werification data","1","2","","100","2","196","1","V~O","1", "","BAS","1","0","0","text","LBL_ADVANCED_BLOCK"),
		array("6","970","vat_id","vtiger_account","1","1","vat_id","Vat ID","1","2","","100","2","194","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_REGISTRATION_INFO"),
		array("6","971","registration_number_1","vtiger_account","1","1","registration_number_1","Registration number 1","1","2","","100","6","194","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_REGISTRATION_INFO"),
		array("6","972","registration_number_2","vtiger_account","1","1","registration_number_2","Registration number 2","1","2","","100","4","194","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_REGISTRATION_INFO"),
		array("6","1330","no_approval","vtiger_account","2","56","no_approval","Approval for phone calls","1","2","","100","8","195","1","C~O","1", "","BAS","1","0","0","varchar(3)","LBL_CONTACT_INFO"),
		array("6","1363","sum_salesorders","vtiger_account","2","71","sum_salesorders","Sum sales orders","1","2","","100","3","198","10","N~O","1", "","BAS","1","0","0","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("6","1364","sum_invoices","vtiger_account","2","71","sum_invoices","Sum invoices","1","2","","100","1","198","10","N~O","1", "","BAS","1","0","0","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("6","1369","balance","vtiger_account","2","71","balance","Balance","1","2","","100","2","198","1","N~O","1", "","BAS","1","0","0","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("6","1370","average_profit_so","vtiger_account","2","9","average_profit_so","Average profit sales order","1","2","","100","4","198","10","N~O~2~2","1", "","BAS","1","0","0","decimal(5,2)","LBL_FINANSIAL_SUMMARY"),
		array("6","1331","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","12","1","V~O","1","","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION")
		);

		$tab = 18;
		$Vendors = array(
		array("18","1105","addresslevel1a","vtiger_vendoraddress","1","1","addresslevel1a","AddressLevel1","1","2","","100","8","44","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("18","1106","addresslevel1b","vtiger_vendoraddress","1","1","addresslevel1b","AddressLevel1","1","2","","100","8","43","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("18","1108","addresslevel2a","vtiger_vendoraddress","1","1","addresslevel2a","AddressLevel2","1","2","","100","7","44","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("18","1109","addresslevel2b","vtiger_vendoraddress","1","1","addresslevel2b","AddressLevel2","1","2","","100","7","43","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("18","1111","addresslevel3a","vtiger_vendoraddress","1","1","addresslevel3a","AddressLevel3","1","2","","100","6","44","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("18","1112","addresslevel3b","vtiger_vendoraddress","1","1","addresslevel3b","AddressLevel3","1","2","","100","6","43","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("18","1114","addresslevel4a","vtiger_vendoraddress","1","1","addresslevel4a","AddressLevel4","1","2","","100","5","44","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("18","1115","addresslevel4b","vtiger_vendoraddress","1","1","addresslevel4b","AddressLevel4","1","2","","100","5","43","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("18","1117","addresslevel5a","vtiger_vendoraddress","1","1","addresslevel5a","AddressLevel5","1","2","","100","4","44","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("18","1118","addresslevel5b","vtiger_vendoraddress","1","1","addresslevel5b","AddressLevel5","1","2","","100","4","43","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("18","1120","addresslevel6a","vtiger_vendoraddress","1","1","addresslevel6a","AddressLevel6","1","2","","100","3","44","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("18","1121","addresslevel6b","vtiger_vendoraddress","1","1","addresslevel6b","AddressLevel6","1","2","","100","3","43","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("18","1123","addresslevel7a","vtiger_vendoraddress","1","1","addresslevel7a","AddressLevel7","1","2","","100","2","44","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("18","1124","addresslevel7b","vtiger_vendoraddress","1","1","addresslevel7b","AddressLevel7","1","2","","100","2","43","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("18","1126","addresslevel8a","vtiger_vendoraddress","1","1","addresslevel8a","AddressLevel8","1","2","","100","1","44","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("18","1127","addresslevel8b","vtiger_vendoraddress","1","1","addresslevel8b","AddressLevel8","1","2","","100","1","43","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_MAILING_INFORMATION"),
		array("18","1107","addresslevel1c","vtiger_vendoraddress","1","1","addresslevel1c","AddressLevel1","1","2","","100","8","179","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("18","1110","addresslevel2c","vtiger_vendoraddress","1","1","addresslevel2c","AddressLevel2","1","2","","100","7","179","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("18","1113","addresslevel3c","vtiger_vendoraddress","1","1","addresslevel3c","AddressLevel3","1","2","","100","6","179","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("18","1116","addresslevel4c","vtiger_vendoraddress","1","1","addresslevel4c","AddressLevel4","1","2","","100","5","179","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("18","1119","addresslevel5c","vtiger_vendoraddress","1","1","addresslevel5c","AddressLevel5","1","2","","100","4","179","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("18","1122","addresslevel6c","vtiger_vendoraddress","1","1","addresslevel6c","AddressLevel6","1","2","","100","3","179","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("18","1125","addresslevel7c","vtiger_vendoraddress","1","1","addresslevel7c","AddressLevel7","1","2","","100","2","179","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("18","1128","addresslevel8c","vtiger_vendoraddress","1","1","addresslevel8c","AddressLevel8","1","2","","100","1","179","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("18","1279","verification","vtiger_vendor","2","33","verification","Werification data","1","2","","100","18","42","1","V~O","1", "","BAS","1","0","0","text","LBL_VENDOR_INFORMATION"),
		array("18","973","vat_id","vtiger_vendor","1","1","vat_id","Vat ID","1","2","","100","15","42","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_VENDOR_INFORMATION"),
		array("18","974","registration_number_1","vtiger_vendor","1","1","registration_number_1","Registration number 1","1","2","","100","16","42","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_VENDOR_INFORMATION"),
		array("18","975","registration_number_2","vtiger_vendor","1","1","registration_number_2","Registration number 2","1","2","","100","17","42","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_VENDOR_INFORMATION"),
		array("18","1333","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","45","1","V~O","1","","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION")

		);

		$tab = 20;
		$Quotes = array(
		array("20","1388","form_payment","vtiger_quotes","2","16","form_payment","Form of payment","1","2","Transfer","100","25","49","1","V~O","3","26","BAS","1","0","0","varchar(255)","LBL_QUOTE_INFORMATION",array("Transfer","Cash")),
		array("20","1168","addresslevel1a","vtiger_quotesaddress","1","1","addresslevel1a","AddressLevel1","1","2","","100","3","51","1","V~O","3","2","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("20","1169","addresslevel1b","vtiger_quotesaddress","1","1","addresslevel1b","AddressLevel1","1","2","","100","3","50","1","V~O","3","3","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("20","1170","addresslevel2a","vtiger_quotesaddress","1","1","addresslevel2a","AddressLevel2","1","2","","100","4","51","1","V~O","3","4","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("20","1171","addresslevel2b","vtiger_quotesaddress","1","1","addresslevel2b","AddressLevel2","1","2","","100","4","50","1","V~O","3","5","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("20","1172","addresslevel3a","vtiger_quotesaddress","1","1","addresslevel3a","AddressLevel3","1","2","","100","5","51","1","V~O","3","6","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("20","1173","addresslevel3b","vtiger_quotesaddress","1","1","addresslevel3b","AddressLevel3","1","2","","100","5","50","1","V~O","3","7","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("20","1174","addresslevel4a","vtiger_quotesaddress","1","1","addresslevel4a","AddressLevel4","1","2","","100","6","51","1","V~O","3","8","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("20","1175","addresslevel4b","vtiger_quotesaddress","1","1","addresslevel4b","AddressLevel4","1","2","","100","6","50","1","V~O","3","9","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("20","1176","addresslevel5a","vtiger_quotesaddress","1","1","addresslevel5a","AddressLevel5","1","2","","100","7","51","1","V~O","3","10","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("20","1177","addresslevel5b","vtiger_quotesaddress","1","1","addresslevel5b","AddressLevel5","1","2","","100","7","50","1","V~O","3","11","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("20","1178","addresslevel6a","vtiger_quotesaddress","1","1","addresslevel6a","AddressLevel6","1","2","","100","8","51","1","V~O","3","12","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("20","1179","addresslevel6b","vtiger_quotesaddress","1","1","addresslevel6b","AddressLevel6","1","2","","100","8","50","1","V~O","3","13","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("20","1180","addresslevel7a","vtiger_quotesaddress","1","1","addresslevel7a","AddressLevel7","1","2","","100","9","51","1","V~O","3","14","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("20","1181","addresslevel7b","vtiger_quotesaddress","1","1","addresslevel7b","AddressLevel7","1","2","","100","9","50","1","V~O","3","15","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("20","1182","addresslevel8a","vtiger_quotesaddress","1","1","addresslevel8a","AddressLevel8","1","2","","100","10","51","1","V~O","3","16","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("20","1183","addresslevel8b","vtiger_quotesaddress","1","1","addresslevel8b","AddressLevel8","1","2","","100","10","50","1","V~O","3","17","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("20","1297","buildingnumbera","vtiger_quotesaddress","1","1","buildingnumbera","Building number","1","2","","100","1","51","1","V~O~LE~100","3","18","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("20","1298","localnumbera","vtiger_quotesaddress","1","1","localnumbera","Local number","1","2","","100","2","51","1","V~O~LE~100","3","19","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("20","1299","buildingnumberb","vtiger_quotesaddress","1","1","buildingnumberb","Building number","1","2","","100","1","50","1","V~O~LE~100","3","20","BAS","1","0","0","varchar(100)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("20","1300","localnumberb","vtiger_quotesaddress","1","1","localnumberb","Local number","1","2","","100","2","50","1","V~O~LE~100","3","21","BAS","1","0","0","varchar(100)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("20","824","sum_time","vtiger_quotes","1","7","sum_time","Total time [h]","1","2","","100","24","49","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_QUOTE_INFORMATION"),
		array("20","1345","total_purchase","vtiger_quotes","1","7","total_purchase","Total Purchase","1","2","","100","1","49","3","NN~O","3","23","BAS","1","0","0","decimal(13,2)","LBL_QUOTE_INFORMATION"),
		array("20","1346","total_margin","vtiger_quotes","1","7","total_margin","Total margin","1","2","","100","2","49","3","NN~O","3","24","BAS","1","0","0","decimal(13,2)","LBL_QUOTE_INFORMATION"),
		array("20","1347","total_marginp","vtiger_quotes","1","7","total_marginp","Total margin Percentage","1","2","","100","3","49","3","NN~O","3","25","BAS","1","0","0","decimal(13,2)","LBL_QUOTE_INFORMATION"),
		array("20","1334","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","54","1","V~O","3","22","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION")


		);

		$tab = 21;
		$PurchaseOrder = array(
		array("21","1184","addresslevel1a","vtiger_purchaseorderaddress","1","1","addresslevel1a","AddressLevel1","1","2","","100","10","57","1","V~O","3","3","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("21","1185","addresslevel1b","vtiger_purchaseorderaddress","1","1","addresslevel1b","AddressLevel1","1","2","","100","10","56","1","V~O","3","4","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("21","1186","addresslevel2a","vtiger_purchaseorderaddress","1","1","addresslevel2a","AddressLevel2","1","2","","100","9","57","1","V~O","3","5","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("21","1187","addresslevel2b","vtiger_purchaseorderaddress","1","1","addresslevel2b","AddressLevel2","1","2","","100","9","56","1","V~O","3","6","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("21","1188","addresslevel3a","vtiger_purchaseorderaddress","1","1","addresslevel3a","AddressLevel3","1","2","","100","8","57","1","V~O","3","7","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("21","1189","addresslevel3b","vtiger_purchaseorderaddress","1","1","addresslevel3b","AddressLevel3","1","2","","100","8","56","1","V~O","3","8","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("21","1190","addresslevel4a","vtiger_purchaseorderaddress","1","1","addresslevel4a","AddressLevel4","1","2","","100","7","57","1","V~O","3","9","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("21","1191","addresslevel4b","vtiger_purchaseorderaddress","1","1","addresslevel4b","AddressLevel4","1","2","","100","7","56","1","V~O","3","10","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("21","1192","addresslevel5a","vtiger_purchaseorderaddress","1","1","addresslevel5a","AddressLevel5","1","2","","100","6","57","1","V~O","3","11","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("21","1193","addresslevel5b","vtiger_purchaseorderaddress","1","1","addresslevel5b","AddressLevel5","1","2","","100","6","56","1","V~O","3","12","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("21","1194","addresslevel6a","vtiger_purchaseorderaddress","1","1","addresslevel6a","AddressLevel6","1","2","","100","5","57","1","V~O","3","13","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("21","1195","addresslevel6b","vtiger_purchaseorderaddress","1","1","addresslevel6b","AddressLevel6","1","2","","100","5","56","1","V~O","3","14","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("21","1196","addresslevel7a","vtiger_purchaseorderaddress","1","1","addresslevel7a","AddressLevel7","1","2","","100","4","57","1","V~O","3","15","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("21","1197","addresslevel7b","vtiger_purchaseorderaddress","1","1","addresslevel7b","AddressLevel7","1","2","","100","4","56","1","V~O","3","16","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("21","1199","addresslevel8a","vtiger_purchaseorderaddress","1","1","addresslevel8a","AddressLevel8","1","2","","100","3","57","1","V~O","3","17","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("21","1200","addresslevel8b","vtiger_purchaseorderaddress","1","1","addresslevel8b","AddressLevel8","1","2","","100","3","56","1","V~O","3","18","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("21","1301","buildingnumbera","vtiger_purchaseorderaddress","1","1","buildingnumbera","Building number","1","2","","100","1","57","1","V~O~LE~100","3","19","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("21","1302","localnumbera","vtiger_purchaseorderaddress","1","1","localnumbera","Local number","1","2","","100","2","57","1","V~O~LE~100","3","20","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("21","1303","buildingnumberb","vtiger_purchaseorderaddress","1","1","buildingnumberb","Building number","1","2","","100","1","56","1","V~O~LE~100","3","21","BAS","1","0","0","varchar(100)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("21","1304","localnumberb","vtiger_purchaseorderaddress","1","1","localnumberb","Local number","1","2","","100","2","56","1","V~O~LE~100","3","22","BAS","1","0","0","varchar(100)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("21","1354","total_purchase","vtiger_purchaseorder","1","7","total_purchase","Total Purchase","1","2","","100","1","58","3","NN~O","3","24","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS"),
		array("21","1355","total_margin","vtiger_purchaseorder","1","7","total_margin","Total margin","1","2","","100","2","58","3","NN~O","3","25","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS"),
		array("21","1356","total_marginp","vtiger_purchaseorder","1","7","total_marginp","Total margin Percentage","1","2","","100","3","58","3","NN~O","3","26","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS"),
		array("21","1336","attention","vtiger_crmentity","1","300","attention","Attention","1","2","","100","2","60","1","V~O","3","23","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION")
		);

		$tab = 22;
		$SalesOrder = array(
		array("22","1390","form_payment","vtiger_salesorder","2","16","form_payment","Form of payment","1","2","Transfer","100","25","61","1","V~O","3","26","BAS","1","0","0","varchar(255)","LBL_SO_INFORMATION",array("Bank account")),
		array("22","1201","addresslevel1a","vtiger_salesorderaddress","1","1","addresslevel1a","AddressLevel1","1","2","","100","10","63","1","V~O","3","2","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("22","1202","addresslevel1b","vtiger_salesorderaddress","1","1","addresslevel1b","AddressLevel1","1","2","","100","10","62","1","V~O","3","3","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("22","1203","addresslevel2a","vtiger_salesorderaddress","1","1","addresslevel2a","AddressLevel2","1","2","","100","9","63","1","V~O","3","4","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("22","1204","addresslevel2b","vtiger_salesorderaddress","1","1","addresslevel2b","AddressLevel2","1","2","","100","9","62","1","V~O","3","5","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("22","1205","addresslevel3a","vtiger_salesorderaddress","1","1","addresslevel3a","AddressLevel3","1","2","","100","8","63","1","V~O","3","6","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("22","1206","addresslevel3b","vtiger_salesorderaddress","1","1","addresslevel3b","AddressLevel3","1","2","","100","8","62","1","V~O","3","7","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("22","1207","addresslevel4a","vtiger_salesorderaddress","1","1","addresslevel4a","AddressLevel4","1","2","","100","7","63","1","V~O","3","8","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("22","1208","addresslevel4b","vtiger_salesorderaddress","1","1","addresslevel4b","AddressLevel4","1","2","","100","7","62","1","V~O","3","9","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("22","1209","addresslevel5a","vtiger_salesorderaddress","1","1","addresslevel5a","AddressLevel5","1","2","","100","6","63","1","V~O","3","10","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("22","1210","addresslevel5b","vtiger_salesorderaddress","1","1","addresslevel5b","AddressLevel5","1","2","","100","6","62","1","V~O","3","11","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("22","1211","addresslevel6a","vtiger_salesorderaddress","1","1","addresslevel6a","AddressLevel6","1","2","","100","5","63","1","V~O","3","12","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("22","1212","addresslevel6b","vtiger_salesorderaddress","1","1","addresslevel6b","AddressLevel6","1","2","","100","5","62","1","V~O","3","13","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("22","1213","addresslevel7a","vtiger_salesorderaddress","1","1","addresslevel7a","AddressLevel7","1","2","","100","4","63","1","V~O","3","14","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("22","1214","addresslevel7b","vtiger_salesorderaddress","1","1","addresslevel7b","AddressLevel7","1","2","","100","4","62","1","V~O","3","15","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("22","1215","addresslevel8a","vtiger_salesorderaddress","1","1","addresslevel8a","AddressLevel8","1","2","","100","3","63","1","V~O","3","16","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("22","1216","addresslevel8b","vtiger_salesorderaddress","1","1","addresslevel8b","AddressLevel8","1","2","","100","3","62","1","V~O","3","17","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("22","1305","buildingnumbera","vtiger_salesorderaddress","1","1","buildingnumbera","Building number","1","2","","100","1","63","1","V~O~LE~100","3","18","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("22","1306","localnumbera","vtiger_salesorderaddress","1","1","localnumbera","Local number","1","2","","100","2","63","1","V~O~LE~100","3","19","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("22","1307","buildingnumberb","vtiger_salesorderaddress","1","1","buildingnumberb","Building number","1","2","","100","1","62","1","V~O~LE~100","3","20","BAS","1","0","0","varchar(100)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("22","1308","localnumberb","vtiger_salesorderaddress","1","1","localnumberb","Local number","1","2","","100","2","62","1","V~O~LE~100","3","21","BAS","1","0","0","varchar(100)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("22","822","sum_time","vtiger_salesorder","1","7","sum_time","Total time [h]","1","2","","100","24","61","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_SO_INFORMATION"),
		array("22","1348","total_purchase","vtiger_salesorder","1","7","total_purchase","Total Purchase","1","2","","100","1","64","3","NN~O","3","23","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS"),
		array("22","1349","total_margin","vtiger_salesorder","1","7","total_margin","Total margin","1","2","","100","2","64","3","NN~O","3","24","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS"),
		array("22","1350","total_marginp","vtiger_salesorder","1","7","total_marginp","Total margin Percentage","1","2","","100","3","64","3","NN~O","3","25","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS"),
		array("22","1335","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","66","1","V~O","3","22","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION")
		);

		$tab = 23;
		$Invoice = array(
		array("23","1389","form_payment","vtiger_invoice","2","16","form_payment","Form of payment","1","2","Transfer","100","28","67","1","V~O","3","28","BAS","1","0","0","varchar(255)","LBL_INVOICE_INFORMATION"),
		array("23","1217","addresslevel1a","vtiger_invoiceaddress","1","1","addresslevel1a","AddressLevel1","1","2","","100","10","69","1","V~O","3","3","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("23","1218","addresslevel1b","vtiger_invoiceaddress","1","1","addresslevel1b","AddressLevel1","1","2","","100","10","68","1","V~O","3","4","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("23","1219","addresslevel2a","vtiger_invoiceaddress","1","1","addresslevel2a","AddressLevel2","1","2","","100","9","69","1","V~O","3","5","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("23","1220","addresslevel2b","vtiger_invoiceaddress","1","1","addresslevel2b","AddressLevel2","1","2","","100","9","68","1","V~O","3","6","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("23","1221","addresslevel3a","vtiger_invoiceaddress","1","1","addresslevel3a","AddressLevel3","1","2","","100","8","69","1","V~O","3","7","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("23","1222","addresslevel3b","vtiger_invoiceaddress","1","1","addresslevel3b","AddressLevel3","1","2","","100","8","68","1","V~O","3","8","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("23","1223","addresslevel4a","vtiger_invoiceaddress","1","1","addresslevel4a","AddressLevel4","1","2","","100","7","69","1","V~O","3","9","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("23","1224","addresslevel4b","vtiger_invoiceaddress","1","1","addresslevel4b","AddressLevel4","1","2","","100","7","68","1","V~O","3","10","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("23","1225","addresslevel5a","vtiger_invoiceaddress","1","1","addresslevel5a","AddressLevel5","1","2","","100","6","69","1","V~O","3","11","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("23","1226","addresslevel5b","vtiger_invoiceaddress","1","1","addresslevel5b","AddressLevel5","1","2","","100","6","68","1","V~O","3","12","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("23","1227","addresslevel6a","vtiger_invoiceaddress","1","1","addresslevel6a","AddressLevel6","1","2","","100","5","69","1","V~O","3","13","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("23","1228","addresslevel6b","vtiger_invoiceaddress","1","1","addresslevel6b","AddressLevel6","1","2","","100","5","68","1","V~O","3","14","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("23","1229","addresslevel7a","vtiger_invoiceaddress","1","1","addresslevel7a","AddressLevel7","1","2","","100","4","69","1","V~O","3","15","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("23","1230","addresslevel7b","vtiger_invoiceaddress","1","1","addresslevel7b","AddressLevel7","1","2","","100","4","68","1","V~O","3","16","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("23","1231","addresslevel8a","vtiger_invoiceaddress","1","1","addresslevel8a","AddressLevel8","1","2","","100","3","69","1","V~O","3","17","BAS","1","0","0","varchar(255)","LBL_ADDRESS_INFORMATION"),
		array("23","1232","addresslevel8b","vtiger_invoiceaddress","1","1","addresslevel8b","AddressLevel8","1","2","","100","3","68","1","V~O","3","18","BAS","1","0","0","varchar(255)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("23","1309","buildingnumbera","vtiger_invoiceaddress","1","1","buildingnumbera","Building number","1","2","","100","1","69","1","V~O~LE~100","3","19","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("23","1310","localnumbera","vtiger_invoiceaddress","1","1","localnumbera","Local number","1","2","","100","2","69","1","V~O~LE~100","3","20","BAS","1","0","0","varchar(100)","LBL_ADDRESS_INFORMATION"),
		array("23","1311","buildingnumberb","vtiger_invoiceaddress","1","1","buildingnumberb","Building number","1","2","","100","1","68","1","V~O~LE~100","3","21","BAS","1","0","0","varchar(100)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("23","1312","localnumberb","vtiger_invoiceaddress","1","1","localnumberb","Local number","1","2","","100","2","68","1","V~O~LE~100","3","22","BAS","1","0","0","varchar(100)","LBL_ADDRESS_DELIVERY_INFORMATION"),
		array("23","1351","total_purchase","vtiger_invoice","1","7","total_purchase","Total Purchase","1","2","","100","1","70","3","NN~O","3","24","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS"),
		array("23","1352","total_margin","vtiger_invoice","1","7","total_margin","Total margin","1","2","","100","2","70","3","NN~O","3","25","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS"),
		array("23","1353","total_marginp","vtiger_invoice","1","7","total_marginp","Total margin Percentage","1","2","","100","3","70","3","NN~O","3","26","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS"),
		array("23","1367","potentialid","vtiger_invoice","2","10","potentialid","Potential","1","2","","100","27","67","1","V~M","1","27","BAS","1","0","0","int(19)","LBL_INVOICE_INFORMATION",array(),array('Potentials')),
		array("23","1337","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","72","1","V~O","3","23","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION")
		);

		$tab = 2;
		$Potentials = array(
		array("2","834","sum_time","vtiger_potential","1","7","sum_time","Total time [h]","1","2","","100","1","133","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("2","836","sum_time_so","vtiger_potential","1","7","sum_time_so","Total time [Sales Order]","1","2","","100","3","133","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("2","838","sum_time_q","vtiger_potential","1","7","sum_time_q","Total time [Quotes]","1","2","","100","5","133","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("2","840","sum_time_all","vtiger_potential","1","7","sum_time_all","Total time [Sum]","1","2","","100","7","133","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("2","1281","sum_time_k","vtiger_potential","2","7","sum_time_k","Total time [Calculation]","1","2","","100","8","133","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("2","1357","sum_quotes","vtiger_potential","2","71","sum_quotes","Sum quotes","1","2","","100","4","199","10","N~O","1", "","BAS","1","0","1","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("2","1358","sum_salesorders","vtiger_potential","2","71","sum_salesorders","Sum sales orders","1","2","","100","2","199","10","N~O","1", "","BAS","1","0","1","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("2","1359","sum_invoices","vtiger_potential","2","71","sum_invoices","Sum invoices","1","2","","100","3","199","10","N~O","1", "","BAS","1","0","1","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("2","1360","sum_calculations","vtiger_potential","2","71","sum_calculations","Sum calculations","1","2","","100","5","199","10","N~O","1", "","BAS","1","0","0","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("2","1339","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","3","1","V~O","1","","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION"),
		array("2","1374","average_profit_so","vtiger_potential","2","9","average_profit_so","Average profit sales order","1","2","","100","6","199","10","N~O~2~2","1", "","BAS","1","0","0","decimal(5,2)","LBL_FINANSIAL_SUMMARY")

		);

		$tab = 37;
		$Assets = array(
		array("37","818","sum_time","vtiger_assets","1","7","sum_time","Total time [h]","1","2","","100","1","192","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","BLOCK_INFORMATION_TIME"),
		array("37","1324","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","97","1","V~O","1", "","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION"),
		array("37","926","potential","vtiger_assets","1","10","potential","Potential","1","2","","100","3","96","1","I~M","2","8","BAS","1","0","0","int(19)","LBL_CUSTOM_INFORMATION",array(),array('Potentials')),
		array("37","1314","parent_id","vtiger_assets","2","10","parent_id","Parent ID","1","2","","100","1","96","1","V~M","2","2","BAS","1","0","1","int(19)","LBL_CUSTOM_INFORMATION",array(),array('Accounts','Contacts','Leads')),
		array("37","1325","pot_renewal","vtiger_assets","2","10","pot_renewal","Potential renewal","1","2","","100","4","96","1","V~O","1", "","BAS","1","0","0","int(19)","LBL_CUSTOM_INFORMATION",array(),array('Potentials')),
		array('37','1484','ordertime','vtiger_assets','2','7','ordertime','LBL_ORDER_TIME','1','2','','100','7','192','1','NN~O','1','','BAS','1','0','0',"decimal(10,2)","BLOCK_INFORMATION_TIME",array()),
		array('37','744','smcreatorid','vtiger_crmentity','1','52','created_user_id','Created By','1','2','','100','8','96','2','V~O','3','7','BAS','0','0','0',"int(19)","LBL_CUSTOM_INFORMATION",array())
		);

		$tab = 41;
		$ProjectMilestone = array(
		array("41","746","smcreatorid","vtiger_crmentity","1","52","created_user_id","Created By","1","2","","100","8","101","2","V~O","3","5","BAS","0","0","0","int(19)","LBL_PROJECT_MILESTONE_INFORMATION")
		);

		$tab = 33;
		$PBXManager = array(
		array('33','741','smcreatorid','vtiger_crmentity','1','52','created_user_id','Created By','1','2','','100','17','88','2','V~O','3','1','BAS','0','0','0',"int(19)","LBL_PBXMANAGER_INFORMATION")
		);

		$tab = 45;
		$SMSNotifier = array(
		array("45","749","smcreatorid","vtiger_crmentity","1","52","created_user_id","Created By","1","2","","100","8","110","2","V~O","3","1","BAS","0","0","0","int(19)","LBL_SMSNOTIFIER_INFORMATION")
		);

		$setToCRM = array('OSSMailTemplates'=>$OSSMailTemplates,'OSSEmployees'=>$OSSEmployees,'Users'=>$Users,'PurchaseOrder'=>$PurchaseOrder,'Vendors'=>$Vendors,'Accounts'=>$Accounts,'Contacts'=>$Contacts,'Leads'=>$Leads,'SalesOrder'=>$SalesOrder,'Invoice'=>$Invoice,'Quotes'=>$Quotes,'OSSCosts'=>$OSSCosts,'Calculations'=>$Calculations,'Assets'=>$Assets,'HelpDesk'=>$HelpDesk,'Project'=>$Project,'OSSPasswords'=>$OSSPasswords,'OSSMailView'=>$OSSMailView,'OSSTimeControl'=>$OSSTimeControl,'OutsourcedProducts'=>$OutsourcedProducts,'OSSSoldServices'=>$OSSSoldServices,'OSSOutsourcedServices'=>$OSSOutsourcedServices,'Services'=>$Services,'OSSPdf'=>$OSSPdf,'ServiceContracts'=>$ServiceContracts,'Products'=>$Products,'ProjectTask'=>$ProjectTask,'Documents'=>$Documents,'Potentials'=>$Potentials,'ModComments'=>$ModComments,'ProjectMilestone'=>$ProjectMilestone,'SMSNotifier'=>$SMSNotifier,'PBXManager'=>$PBXManager);

		$setToCRMAfter = array();
		foreach($setToCRM as $nameModule=>$module){
			if(!$module)
				continue;
			foreach($module as $key=>$fieldValues){
				for($i=0;$i<count($fieldValues);$i++){
					$setToCRMAfter[$nameModule][$key][$columnName[$i]] = $fieldValues[$i];
				}
			}
		}
		$log->debug("Exiting VT610_to_YT::getFieldsAll() method ...");
		return $setToCRMAfter;
	}
	public function addFields(){
		global $log, $adb;
		$log->debug("Entering VT610_to_YT::addFields() method ...");
		include_once('vtlib/Vtiger/Module.php'); 
		$moduleToCopyValues = array('Contacts'=>array(array('table'=>'vtiger_contactaddress','copy'=>'addresslevel8a = mailingstreet, addresslevel5a = mailingcity, addresslevel1a = mailingcountry, addresslevel2a = mailingstate, addresslevel7a = mailingzip, addresslevel8b = otherstreet, addresslevel5b = othercity, addresslevel2b = otherstate, addresslevel1b = othercountry,  addresslevel7b = otherzip'),array('table'=>'vtiger_contactdetails','copy'=>'secondary_email = secondaryemail'),array('table'=>'vtiger_contactdetails','copy'=>'parentid = accountid')),
		'Accounts'=>array('newTab'=>'vtiger_accountaddress', 'oldTab1'=>'vtiger_accountbillads', 'oldTab2'=>'vtiger_accountshipads', 'newId'=>'accountaddressid', 'oldId1'=>'accountaddressid', 'oldId2'=>'accountaddressid'),
		'Invoice'=>array('newTab'=>'vtiger_invoiceaddress', 'oldTab1'=>'vtiger_invoicebillads', 'oldTab2'=>'vtiger_invoiceshipads', 'newId'=>'invoiceaddressid', 'oldId1'=>'invoicebilladdressid', 'oldId2'=>'invoiceshipaddressid'),
		'PurchaseOrder'=>array('newTab'=>'vtiger_purchaseorderaddress', 'oldTab1'=>'vtiger_pobillads', 'oldTab2'=>'vtiger_poshipads', 'newId'=>'purchaseorderaddressid', 'oldId1'=>'pobilladdressid', 'oldId2'=>'poshipaddressid'),
		'SalesOrder'=>array('newTab'=>'vtiger_salesorderaddress', 'oldTab1'=>'vtiger_sobillads', 'oldTab2'=>'vtiger_soshipads', 'newId'=>'salesorderaddressid', 'oldId1'=>'sobilladdressid', 'oldId2'=>'soshipaddressid'),
		'Vendors'=>array(),
		'Assets'=>array(array('table'=>'vtiger_assets','copy'=>'parent_id = account')),
		'Leads'=>array(array('table'=>'vtiger_leadaddress','copy'=>'addresslevel8a = lane, addresslevel5a = city, addresslevel1a = country, addresslevel2a = state, addresslevel7a = code'),array('table'=>'vtiger_leaddetails','copy'=>'noapprovalemails = emailoptout'))
			);
		$setToCRMAfter = self::getFieldsAll();
		foreach($setToCRMAfter as $moduleName=>$fields){
			foreach($fields as $field){
				if(self::checkFieldExists($field, $moduleName)){
					continue;
				}
				try {
					$moduleInstance = Vtiger_Module::getInstance($moduleName);
					$blockInstance = Vtiger_Block::getInstance($field['blocklabel'],$moduleInstance);
					$fieldInstance = new Vtiger_Field();
					$fieldInstance->column = $field['column'];
					$fieldInstance->name = $field['name'];
					$fieldInstance->label = $field['label'];
					$fieldInstance->table = $field['table'];
					$fieldInstance->uitype = $field['uitype'];
					$fieldInstance->typeofdata = $field['typeofdata'];
					$fieldInstance->readonly = $field['readonly'];
					$fieldInstance->displaytype = $field['displaytype'];
					$fieldInstance->masseditable = $field['masseditable'];
					$fieldInstance->quickcreate = $field['quickcreate'];
					$fieldInstance->columntype = $field['columntype'];
					$fieldInstance->presence = $field['presence'];
					$fieldInstance->maximumlength = $field['maximumlength'];
					$fieldInstance->info_type = $field['info_type'];
					$fieldInstance->helpinfo = $field['helpinfo'];
					$fieldInstance->summaryfield = $field['summaryfield'];
					$fieldInstance->generatedtype = $field['generatedtype'];
					$fieldInstance->defaultvalue = $field['defaultvalue'];
					$blockInstance->addField($fieldInstance);
					if($field['setpicklistvalues'] && ($field['uitype'] == 15 || $field['uitype'] == 16 || $field['uitype'] == 33 ))
						$fieldInstance->setPicklistValues($field['setpicklistvalues']);
					if($field['setrelatedmodules'] && $field['uitype'] == 10){
						$fieldInstance->setRelatedModules($field['setrelatedmodules']);
					}
				} catch (Exception $e) {
					Install_InitSchema_Model::addMigrationLog('addFields '.$e->getMessage(),'error');
				}
			}
			if(array_key_exists($moduleName,$moduleToCopyValues))
				self::copyValues($moduleName,$moduleToCopyValues);
		}
		Install_InitSchema_Model::addMigrationLog('addFields');
		$log->debug("Exiting VT610_to_YT::addFields() method ...");
	}
	
	public function addSharingToModules(){
		global $log, $adb;
		$log->debug("Entering VT610_to_YT::addSharingToModules() method ...");
		$restrictedModules = array('Emails', 'Integration', 'Dashboard', 'ModComments', 'SMSNotifier','PBXManager');
		$sql = 'SELECT * FROM vtiger_tab WHERE isentitytype = ? AND name NOT IN ('.generateQuestionMarks($restrictedModules).')';
		$params = array(1, $restrictedModules);
		$tabresult = $adb->pquery($sql, $params,true);

		for($i = 0; $i < $adb->num_rows($tabresult); $i++){
			$tabId = $adb->query_result_raw($tabresult, $i, 'tabid');
			$fieldresult = $adb->query("SELECT * FROM `vtiger_field` WHERE tabid = '$tabId' AND columnname = 'shownerid'",true);
			if( $adb->num_rows($fieldresult) == 0){
				$name = $adb->query_result_raw($tabresult, $i, 'name');
			
				$moduleInstance = Vtiger_Module::getInstance($name);
				$blockInstance = new Vtiger_Block();
				$blockInstance->label = 'LBL_SHARING_INFORMATION';
				$blockInstance->showtitle = 0;
				$blockInstance->visible = 0;
				$blockInstance->increateview = 0;
				$blockInstance->ineditview = 0;
				$blockInstance->indetailview = 0;
				$blockInstance->iscustom = 1;
				$blockInstance->__create($moduleInstance); 
				
				$blockInstance = Vtiger_Block::getInstance('LBL_SHARING_INFORMATION',$moduleInstance);
				$fieldInstance = new Vtiger_Field(); 
				$fieldInstance->name = 'inheritsharing';
				$fieldInstance->table = 'vtiger_crmentity';
				$fieldInstance->label = 'Copy permissions automatically';
				$fieldInstance->column = 'inheritsharing';
				$fieldInstance->columntype = 'tinyint(1) default 0';
				$fieldInstance->uitype = 56;
				$fieldInstance->typeofdata = 'C~O'; 
				$fieldInstance->displaytype = 1;
				$blockInstance->addField($fieldInstance); 	

				$fieldInstance = new Vtiger_Field(); 
				$fieldInstance->name = 'shownerid';
				$fieldInstance->table = 'vtiger_crmentity';
				$fieldInstance->label = 'Share with users';
				$fieldInstance->column = 'shownerid';
				$fieldInstance->columntype = "set('1')";
				$fieldInstance->uitype = 120;
				$fieldInstance->typeofdata = 'V~O'; 
				$fieldInstance->displaytype = 1;
				$blockInstance->addField($fieldInstance); 
			}
		}
		$log->debug("Exiting VT610_to_YT::addSharingToModules() method ...");
	}
	
	public function checkFieldExists($field, $moduleName){
		global $adb;
		if($moduleName == 'Settings')
			$result = $adb->pquery("SELECT * FROM vtiger_settings_field WHERE name = ? AND linkto = ? ;", array($field[1],$field[4]));
		else
			$result = $adb->pquery("SELECT * FROM vtiger_field WHERE columnname = ? AND tablename = ? AND tabid = ?;", array($field['name'],$field['table'], getTabid($moduleName)));
		if(!$adb->num_rows($result)) {
			return false;
		}
		return true;
	}
	//copy values
	public function copyValues($moduleName,$moduleToCopyValues){
		global $log;
		$log->debug("Entering VT610_to_YT::copyValues() method ...");
		$adb = PearDatabase::getInstance();
		try {
			if($moduleName == "Accounts" || $moduleName == "PurchaseOrder" || $moduleName == "Invoice" || $moduleName == "SalesOrder"){
				$query = "INSERT INTO ".$moduleToCopyValues[$moduleName]['newTab']." (".$moduleToCopyValues[$moduleName]['newId'].", addresslevel5a, addresslevel7a, addresslevel1a, addresslevel2a, addresslevel8a) 
				SELECT ".$moduleToCopyValues[$moduleName]['oldId1'].", bill_city, bill_code, bill_country, bill_state, bill_street 
				FROM ".$moduleToCopyValues[$moduleName]['oldTab1'].";";
				$adb->pquery($query, array());
				
				$query = "UPDATE ".$moduleToCopyValues[$moduleName]['newTab']." LEFT JOIN `".$moduleToCopyValues[$moduleName]['oldTab2']."` ON `".$moduleToCopyValues[$moduleName]['oldTab2']."`.`".$moduleToCopyValues[$moduleName]['oldId2']."` = `".$moduleToCopyValues[$moduleName]['newTab']."`.`".$moduleToCopyValues[$moduleName]['newId']."` SET addresslevel5b = ship_city, addresslevel7b = ship_code, addresslevel1b = ship_country, addresslevel2b = ship_state, addresslevel8b = ship_street ;";
				$adb->pquery($query, array());
			} elseif($moduleName == "Vendors"){
				$query = "INSERT INTO vtiger_vendoraddress (vendorid, addresslevel5c, addresslevel7c, addresslevel1c, addresslevel2c, addresslevel8c) 
				SELECT vendorid, city, postalcode, country, state, street 
				FROM vtiger_vendor;";
				$adb->pquery($query, array());
			} else
				for($i=0;$i<count($moduleToCopyValues[$moduleName]);$i++){
					$query = "UPDATE ".$moduleToCopyValues[$moduleName][$i]['table']." SET  ".$moduleToCopyValues[$moduleName][$i]['copy']."; ";
					$adb->pquery($query, array());
				}
		} catch (Exception $e) {
			Install_InitSchema_Model::addMigrationLog('copyValues '.$e->getMessage(),'error');
		}
		$log->debug("Exiting VT610_to_YT::copyValues() method ...");
	}
	// self::InactiveFields($fieldsInactive);
	public function InactiveFields (){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::InactiveFields() method ...");
		$fieldsInactive = array('HelpDesk'=>array('days',"hours"),
		'Accounts'=>array('tickersymbol',"notify_owner","rating"),
		'Quotes'=>array('bill_city',"bill_code","bill_country","bill_pobox","bill_state","bill_street","ship_city","ship_code","ship_country","ship_pobox","ship_state","ship_street"),
		'SalesOrder'=>array('bill_city',"bill_code","bill_country","bill_pobox","bill_state","bill_street","ship_city","ship_code","ship_country","ship_pobox","ship_state","ship_street"),
		'Products'=>array('productcategory'),
		'Assets'=>array('account',"shippingtrackingnumber",'shippingmethod','tagnumber'),
		'ProjectTask'=>array('projecttaskhours'),
		'Contacts'=>array('accountid',"fax","reference","title","department","notify_owner","secondaryemail","homephone","otherphone","assistant","assistantphone"),
		'Potentials'=>array('probability','nextstep','amount'),
		'Leads'=>array('firstname'),
		'Users'=>array('address_street','address_city','department','phone_home','phone_mobile','phone_other','phone_fax','email2','secondaryemail','address_state','address_country','address_postalcode','phone_work','title'),
		'Services'=>array('servicecategory') //copy to picklist
		);
		foreach($fieldsInactive AS $moduleName=>$fields){
			try {
				$query = "UPDATE vtiger_field SET presence = ? WHERE tabid = ? AND columnname IN(".generateQuestionMarks($fields).") ; ";
				array_unshift($fields,1,getTabid($moduleName));
				$adb->pquery($query, $fields);
			} catch (Exception $e) {
				Install_InitSchema_Model::addMigrationLog('InactiveFields '.$e->getMessage(),'error');
			}
		}
		Install_InitSchema_Model::addMigrationLog('InactiveFields');
		$log->debug("Exiting VT610_to_YT::InactiveFields() method ...");
	}
	function addEmployees() {
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::addEmployees() method ...");
		$moduleName = 'OSSEmployees';
		vimport('~~modules/' . $moduleName . '/' . $moduleName . '.php');
		$result = $adb->pquery("SELECT * FROM vtiger_users WHERE id NOT IN (?) ;", array(1));
		$num = $adb->num_rows($result);
		if($num>0) {
			for($i=0;$i<$num;$i++){
				try {
					$employee = new $moduleName();
					$employee->column_fields['assigned_user_id'] = $adb->query_result($result, $i, 'id');
					$employee->column_fields['name'] = $adb->query_result($result, $i, 'first_name');
					$employee->column_fields['last_name'] = $adb->query_result($result, $i, 'last_name');
					$employee->column_fields['street'] = $adb->query_result($result, $i, 'address_street');
					$employee->column_fields['business_phone'] = $adb->query_result($result, $i, 'phone_home');
					$employee->column_fields['private_phone'] = $adb->query_result($result, $i, 'phone_mobile');
					$employee->column_fields['business_mail'] = $adb->query_result($result, $i, 'email2');
					$employee->column_fields['private_mail'] = $adb->query_result($result, $i, 'secondaryemail');
					$employee->column_fields['city'] = $adb->query_result($result, $i, 'address_city');
					$employee->column_fields['state'] = $adb->query_result($result, $i, 'address_state');
					$employee->column_fields['country'] = $adb->query_result($result, $i, 'address_country');
					$employee->column_fields['code'] = $adb->query_result($result, $i, 'address_postalcode');
					$saved = $employee->save('OSSEmployees');
				} catch (Exception $e) {
					Install_InitSchema_Model::addMigrationLog('addEmployees '.$e->getMessage(),'error');
				}
			}
		}
		Install_InitSchema_Model::addMigrationLog('addEmployees');
		$log->debug("Exiting VT610_to_YT::addEmployees() method ...");
	}
	// self::deleteFields($fieldsToDelete);
	public function deleteFields($fieldsToDelete){
		global $log;
		$log->debug("Entering VT610_to_YT::deleteFields() method ...");
		require_once('include/main/WebUI.php');
		$adb = PearDatabase::getInstance();
		foreach($fieldsToDelete AS $fld_module=>$columnnames){
			$moduleId = getTabid($fld_module);
			foreach($columnnames AS $columnname){
				$fieldquery = 'select * from vtiger_field where tabid = ? AND columnname = ?';
				$res = $adb->pquery($fieldquery,array($moduleId,$columnname));
				$id = $adb->query_result($res,0,'fieldid');
				if(empty($id))
					continue;
				$typeofdata = $adb->query_result($res,0,'typeofdata');
				$fieldname = $adb->query_result($res,0,'fieldname');
				$oldfieldlabel = $adb->query_result($res,0,'fieldlabel');
				$tablename = $adb->query_result($res,0,'tablename');
				$uitype = $adb->query_result($res,0,'uitype');
				$colName = $adb->query_result($res,0,'columnname');
				$tablica = $adb->query_result($res,0,'tablename');
				$fieldtype =  explode("~",$typeofdata);

				//Deleting the CustomField from the Custom Field Table
				$query='delete from vtiger_field where fieldid = ? and vtiger_field.presence in (0,2)';
				$adb->pquery($query, array($id));

				//Deleting from vtiger_profile2field table
				$query='delete from vtiger_profile2field where fieldid=?';
				$adb->pquery($query, array($id));

				//Deleting from vtiger_def_org_field table
				$query='delete from vtiger_def_org_field where fieldid=?';
				$adb->pquery($query, array($id));

				$focus = CRMEntity::getInstance($fld_module);

				$deletecolumnname =$tablename .":". $columnname .":".$fieldname.":".$fld_module. "_" .str_replace(" ","_",$oldfieldlabel).":".$fieldtype[0];
				$column_cvstdfilter = 	$tablename .":". $columnname .":".$fieldname.":".$fld_module. "_" .str_replace(" ","_",$oldfieldlabel);
				$select_columnname = $tablename.":".$columnname .":".$fld_module. "_" . str_replace(" ","_",$oldfieldlabel).":".$fieldname.":".$fieldtype[0];
				$reportsummary_column = $tablename.":".$columnname.":".str_replace(" ","_",$oldfieldlabel);

				$dbquery = 'alter table '. $adb->sql_escape_string($tablica).' drop column '. $adb->sql_escape_string($colName);
				$adb->pquery($dbquery, array());

				//To remove customfield entry from vtiger_field table
				$dbquery = 'delete from vtiger_field where columnname= ? and fieldid=? and vtiger_field.presence in (0,2)';
				$adb->pquery($dbquery, array($colName, $id));
				//we have to remove the entries in customview and report related tables which have this field ($colName)
				$adb->pquery("delete from vtiger_cvcolumnlist where columnname = ? ", array($deletecolumnname));
				$adb->pquery("delete from vtiger_cvstdfilter where columnname = ?", array($column_cvstdfilter));
				$adb->pquery("delete from vtiger_cvadvfilter where columnname = ?", array($deletecolumnname));
				$adb->pquery("delete from vtiger_selectcolumn where columnname = ?", array($select_columnname));
				$adb->pquery("delete from vtiger_relcriteria where columnname = ?", array($select_columnname));
				$adb->pquery("delete from vtiger_reportsortcol where columnname = ?", array($select_columnname));
				$adb->pquery("delete from vtiger_reportdatefilter where datecolumnname = ?", array($column_cvstdfilter));
				$adb->pquery("delete from vtiger_reportsummary where columnname like ?", array('%'.$reportsummary_column.'%'));
				$adb->pquery("delete from vtiger_fieldmodulerel where fieldid = ?", array($id));

				//Deleting from convert lead mapping vtiger_table- Jaguar
				if($fld_module=="Leads") {
					$deletequery = 'delete from vtiger_convertleadmapping where leadfid=?';
					$adb->pquery($deletequery, array($id));
				}elseif($fld_module=="Accounts" || $fld_module=="Contacts" || $fld_module=="Potentials") {
					$map_del_id = array("Accounts"=>"accountfid","Contacts"=>"contactfid","Potentials"=>"potentialfid");
					$map_del_q = "update vtiger_convertleadmapping set ".$map_del_id[$fld_module]."=0 where ".$map_del_id[$fld_module]."=?";
					$adb->pquery($map_del_q, array($id));
				}

				//HANDLE HERE - we have to remove the table for other picklist type values which are text area and multiselect combo box
				if($uitype == 15) {
					$deltablequery = 'drop table vtiger_'.$adb->sql_escape_string($colName);
					$adb->pquery($deltablequery, array());
					$deltablequeryseq = 'drop table vtiger_'.$adb->sql_escape_string($colName).'_seq';
					$adb->pquery($deltablequeryseq, array());		
					$adb->pquery("delete from  vtiger_picklist_dependency where sourcefield=? or targetfield=?", array($colName,$colName));
					
					$fieldquery = 'select * from vtiger_picklist where name = ?';
					$res = $adb->pquery($fieldquery,array($columnname));
					$picklistid = $adb->query_result($res,0,'picklistid');
					$adb->pquery("delete from vtiger_picklist where name = ?", array($columnname));
					$adb->pquery("delete from vtiger_role2picklist where picklistid = ?", array($picklistid));
				}
			}
			
		}
		Install_InitSchema_Model::addMigrationLog('deleteFields');
		$log->debug("Exiting VT610_to_YT::deleteFields() method ...");
	}
	public function picklists(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::picklists() method ...");
		
		$addPicklists = array();
		$addPicklists['Assets'][] = array('name'=>'assetstatus','uitype'=>'15','add_values'=>array('Draft','Realization proceeding','Warranty proceeding','Delivered to Organization'),'remove_values'=>array('Out-of-service','In Service'));
		$addPicklists['HelpDesk'][] = array('name'=>'ticketstatus','uitype'=>'15','add_values'=>array('Answered','Rejected'),'remove_values'=>array());
		$addPicklists['Users'][] = array('name'=>'date_format','uitype'=>'16','add_values'=>array('dd.mm.yyyy','mm.dd.yyyy','yyyy.mm.dd','dd/mm/yyyy','mm/dd/yyyy','yyyy/mm/dd'),'remove_values'=>array());
		$addPicklists['Users'][] = array('name'=>'end_hour','add_values'=>array("00:00","01:00","02:00","03:00","04:00","05:00","06:00","07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00","21:00","22:00","23:00"),'remove_values'=>array());
		$addPicklists['Invoice'][] = array('name'=>'invoicestatus','uitype'=>'15','add_values'=>array("Derecognized","Invoice entered"),'remove_values'=>array('Sent','Credit Invoice','Paid'));
		$addPicklists['Leads'][] = array('name'=>'leadsource','uitype'=>'15','add_values'=>array(),'remove_values'=>array('Existing Customer','Employee','Partner','Public Relations','Direct Mail'));
		$addPicklists['Leads'][] = array('name'=>'leadstatus','uitype'=>'15','add_values'=>array('LBL_TO_REALIZE','LBL_REQUIRES_VERIFICATION','LBL_PRELIMINARY_ANALYSIS_OF','LBL_ADVANCED_ANALYSIS','LBL_INITIAL_ACQUISITION','LBL_CONTACTS_IN_THE_FUTURE','LBL_LEAD_UNTAPPED','LBL_LEAD_ACQUIRED'),'remove_values'=>array('Attempted to Contact','Cold','Contact in Future','Contacted','Hot','Junk Lead','Lost Lead','Not Contacted','Pre Qualified','Qualified','Warm'));
		$addPicklists['Leads'][] = array('name'=>'salutationtype','uitype'=>'15','add_values'=>array(),'remove_values'=>array('--None--'));
		$addPicklists['Leads'][] = array('name'=>'industry','uitype'=>'15','add_values'=>array('Administration','Construction Industry','Power Industry','Trade','Hotels and Restaurants','Health Care','Industry / Manufacturing','Uniformed Services','Transport & Logistics','Technologies'),'remove_values'=>array('--None--','Apparel','Banking','Biotechnology','Chemicals','Communications','Construction','Consulting','Electronics','Energy','Engineering','Entertainment','Environmental','Food & Beverage','Government','Healthcare','Hospitality','Insurance','Machinery','Manufacturing','Media','Not For Profit','Recreation','Retail','Shipping','Technology','Telecommunications','Transportation','Utilities','Other'));
		$addPicklists['Project'][] = array('name'=>'projecttype','uitype'=>'15','add_values'=>array('PLL_INTERNAL','PLL_EXTERNAL','PLL_COMMON'),'remove_values'=>array('administrative','operative','other'));
		
		$roleRecordList = Settings_Roles_Record_Model::getAll();
		$rolesSelected = array();
		foreach($roleRecordList as $roleRecord) {
			$rolesSelected[] = $roleRecord->getId();
		}
		foreach($addPicklists as $moduleName=>$piscklists){
			$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
			if(!$moduleModel)
				continue;
			foreach($piscklists as $piscklist){
				$fieldModel = Settings_Picklist_Field_Model::getInstance($piscklist['name'], $moduleModel);
				if(!$fieldModel)
					continue;
				$pickListValues = Vtiger_Util_Helper::getPickListValues($piscklist['name']);
				foreach($piscklist['add_values'] as $newValue){
					if(!in_array($newValue, $pickListValues)){
						//$moduleModel->addPickListValues($fieldModel, $newValue);
						$moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected);
					}
				}
				foreach($piscklist['remove_values'] as $newValue){
					if(!in_array($newValue, $pickListValues))
						continue;
					if($piscklist['uitype'] == '15'){
						$deletePicklistValueId = self::getPicklistId($piscklist['name'], $newValue);
						if($deletePicklistValueId)
							$adb->pquery("DELETE FROM `vtiger_role2picklist` WHERE picklistvalueid = ? ", array($deletePicklistValueId));
					}
					$adb->pquery("DELETE FROM `vtiger_".$piscklist['name']."` WHERE ".$piscklist['name']." = ? ", array($newValue));
					//$moduleModel->remove($piscklist['name'], $deletePicklistId, '', $moduleName); // remove and replace in records
				}
			}
		}
		$log->debug("Exiting VT610_to_YT::picklists() method ...");
	}
	public function cron(){
		global $log;
		$log->debug("Entering VT610_to_YT::cron() method ...");
		$removeCrons = array();
		$removeCrons[] = 'MailScanner';
		$addCrons = array();
		$addCrons[] = array('Backup','cron/backup.service','43200','','','0','BackUp','11','');
		foreach($removeCrons as $cron)
			Vtiger_Cron::deregister($cron);
		foreach($addCrons as $cron)
			Vtiger_Cron::register($cron[0],$cron[1],$cron[2],$cron[6],$cron[5],0,$cron[8]);
		
		$log->debug("Exiting VT610_to_YT::cron() method ...");
	}
	public function leadMapping(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::leadMapping() method ...");
		$adb->query("DELETE FROM `vtiger_convertleadmapping` ");
		$fieldsLeads = array("phone","lastname","mobile","company","fax","email","leadsource","website","industry","annualrevenue","noofemployees","description","vat_id","registration_number_2","addresslevel1a","addresslevel2a","addresslevel3a","addresslevel4a","addresslevel5a","addresslevel6a","addresslevel7a","addresslevel8a","buildingnumbera","localnumbera");
		$fieldsAccounts = array('accountname','phone','website','fax','email1','employees','industry','annual_revenue','description','vat_id','registration_number_2','adresslevel1a','addresslevel2a','addresslevel3a','addresslevel4a','addresslevel5a','addresslevel6a','addresslevel7a','addresslevel8a','buildingnumbera','localnumbera','addresslevel1a');
		$fieldsContacts = array('phone','lastname','mobile','leadsource','email','description','addresslevel1a','addresslevel2a','addresslevel3a','addresslevel4a','addresslevel5a','addresslevel6a','addresslevel7a','addresslevel8a','buildingnumbera','localnumbera');
		$fieldsPotentials = array('leadsource','description');
		$fieldsPotentials = array('leadsource','description');
		$lead = self::getFieldsId($fieldsLeads, 'Leads');
		$account = self::getFieldsId($fieldsAccounts, 'Accounts');
		$contacts = self::getFieldsId($fieldsContacts, 'Contacts');
		$potentials = self::getFieldsId($fieldsPotentials, 'Potentials');
		
		$convertLeadMapping = array();
		$convertLeadMapping[] = array('company','accountname',0,0,0);
		$convertLeadMapping[] = array('industry','industry',0,0,1);
		$convertLeadMapping[] = array('phone','phone','phone',0,NULL);
		$convertLeadMapping[] = array('fax','fax',0,0,1);
		$convertLeadMapping[] = array('email','email1','email',0,0);
		$convertLeadMapping[] = array('website','website',0,0,1);
		$convertLeadMapping[] = array('description','description','description','description',1);
		$convertLeadMapping[] = array('lastname',0,'lastname',0,0);
		$convertLeadMapping[] = array('mobile',0,'mobile',0,1);
		$convertLeadMapping[] = array('leadsource',0,'leadsource','leadsource',1);
		$convertLeadMapping[] = array('noofemployees','employees',0,0,1);
		$convertLeadMapping[] = array('annualrevenue','annual_revenue',0,0,1);
		$convertLeadMapping[] = array('buildingnumbera','buildingnumbera','buildingnumbera',0,1);
		$convertLeadMapping[] = array('localnumbera','localnumbera','localnumbera',0,1);
		$convertLeadMapping[] = array('addresslevel1a','addresslevel1a','addresslevel1a',0,1);
		$convertLeadMapping[] = array('addresslevel2a','addresslevel2a','addresslevel2a',0,1);
		$convertLeadMapping[] = array('addresslevel3a','addresslevel3a','addresslevel3a',0,1);
		$convertLeadMapping[] = array('addresslevel4a','addresslevel4a','addresslevel4a',0,1);
		$convertLeadMapping[] = array('addresslevel5a','addresslevel5a','addresslevel5a',0,1);
		$convertLeadMapping[] = array('addresslevel6a','addresslevel6a','addresslevel6a',0,1);
		$convertLeadMapping[] = array('addresslevel7a','addresslevel7a','addresslevel7a',0,1);
		$convertLeadMapping[] = array('addresslevel8a','addresslevel8a','addresslevel8a',0,1);
		$convertLeadMapping[] = array('vat_id','vat_id',0,0,1);
		$convertLeadMapping[] = array('registration_number_2','registration_number_2',0,0,1);
		
		foreach($convertLeadMapping as $fieldConvert){
			$leadfid = $lead[$fieldConvert[0]]?$lead[$fieldConvert[0]]:0;
			$accountid = $account[$fieldConvert[1]]?$account[$fieldConvert[1]]:0;
			$contactsid = $contacts[$fieldConvert[2]]?$contacts[$fieldConvert[2]]:0;
			$potentialsid = $potentials[$fieldConvert[3]]?$potentials[$fieldConvert[3]]:0;
			$query = "INSERT INTO vtiger_convertleadmapping (leadfid, accountfid, contactfid, potentialfid, editable) values (?,?,?,?,?);";
			$adb->pquery($query, array($leadfid, $accountid, $contactsid, $potentialsid, $fieldConvert[4]));
		}
		$log->debug("Exiting VT610_to_YT::leadMapping() method ...");
	}
	public function getFieldsId($fields, $moduleName){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::getFieldsId(".$fields.', '. $moduleName.") method ...");
		if(!is_array($fields))
			$fields = array($fields);
		$params = $fields;
		array_unshift($params, getTabid($moduleName));
		$result = $adb->pquery("SELECT fieldid, fieldname FROM vtiger_field WHERE tabid = ? AND fieldname IN (". generateQuestionMarks($fields) .") ;", $params);
		$fieldsResult = array();
		for($i=0;$i<$adb->num_rows($result);$i++){
			$fieldsResult[$adb->query_result($result, $i, 'fieldname')] = $adb->query_result($result, $i, 'fieldid');
		}
		$log->debug("Exiting VT610_to_YT::getFieldsId() method ...");
		return $fieldsResult;
	}
	
	public function foldersToTree(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::foldersToTree() method ...");
		
		$sql = 'INSERT INTO vtiger_trees_templates(`name`, `module`, `access`) VALUES (?,?,?)';
		$params = array('System', getTabid('Documents'), 0);
		$adb->pquery($sql, $params);
		$templateId = $adb->getLastInsertID();
		
		$sql = 'INSERT INTO vtiger_trees_templates_data(`templateid`, `name`, `tree`, `parenttrre`, `depth`, `label`) VALUES (?,?,?,?,?,?)';
		$params = array($templateId, 'Default', 'T1', 'T1', 0, 'Default');
		$adb->pquery($sql, $params);
		
		$result = $adb->query("SELECT * FROM `vtiger_attachmentsfolder` ORDER BY `sequence`;");
		
		$fieldsResult = array();
		for($i=0;$i<$adb->num_rows($result);$i++){
			$folderid = $adb->query_result($result, $i, 'folderid');
			$name = $adb->query_result($result, $i, 'foldername');
			if($folderid != 1){
				$sql = 'INSERT INTO vtiger_trees_templates_data(templateid, name, tree, parenttrre, depth, label) VALUES (?,?,?,?,?,?)';
				$params = array($templateId, $name, 'T'.$folderid, 'T'.$folderid, 0, $name);
				$adb->pquery($sql, $params);
			}
			$query = "UPDATE `vtiger_notes` SET `folderid` = ? WHERE `folderid` = ? ; ";
			$adb->pquery($query, array('T'.$folderid, $folderid));
		}
		$log->debug("Exiting VT610_to_YT::foldersToTree() method ...");
	}
	
	public function updateRecords(){
		global $log,$adb,$YetiForce_current_version;
		$log->debug("Entering VT610_to_YT::updateRecords() method ...");
		$changes = array();
		$changes[] = array('where'=>array('columnname'=>array('calendarsharedtype')), 'setColumn'=>array('displaytype'), 'setValue'=>array(1));
		$changes[] = array('where'=>array('columnname'=>array('description'), 'tabid'=>array(getTabid('Accounts'),getTabid('Leads'),getTabid('Assets'),getTabid('Vendors'),getTabid('Quotes'),getTabid('Contacts'),getTabid('Potentials'),getTabid('PurchaseOrder'),getTabid('Project'),getTabid('HelpDesk'),getTabid('SalesOrder'),getTabid('Invoice'))), 'setColumn'=>array('uitype'), 'setValue'=>array(300));
		$changes[] = array('where'=>array('columnname'=>array('createdtime','modifiedtime' )), 'setColumn'=>array('typeofdata'), 'setValue'=>array('DT~O'));
		$changes[] = array('where'=>array('columnname'=>array('donotcall'),'tabid'=>array(getTabid('Contacts'))), 'setColumn'=>array('fieldlabel'), 'setValue'=>array('Approval for phone calls'));
		$changes[] = array('where'=>array('columnname'=>array('emailoptout'),'tabid'=>array(getTabid('Contacts'), getTabid('Accounts'))), 'setColumn'=>array('fieldlabel'), 'setValue'=>array('Approval for email'));
		$changes[] = array('where'=>array('columnname'=>array('end_hour'),'tabid'=>array(getTabid('Users'))), 'setColumn'=>array('uitype'), 'setValue'=>array(16));
		$changes[] = array('where'=>array('columnname'=>array('eventstatus'),'tabid'=>array(getTabid('Events'),getTabid('Calendar'))), 'setColumn'=>array('defaultvalue'), 'setValue'=>array('Planned'));
		$changes[] = array('where'=>array('columnname'=>array('industry'),'tabid'=>array(getTabid('Accounts'))), 'setColumn'=>array('uitype'), 'setValue'=>array(16));
		$changes[] = array('where'=>array('columnname'=>array('invoiceid'),'tabid'=>array(getTabid('Assets'))), 'setColumn'=>array('typeofdata'), 'setValue'=>array('V~M'));
		$changes[] = array('where'=>array('columnname'=>array('lastname'),'tabid'=>array(getTabid('Leads'))), 'setColumn'=>array('fieldlabel'), 'setValue'=>array('Short name'));
		$changes[] = array('where'=>array('columnname'=>array('notecontent '),'tabid'=>array(getTabid('Documents'))), 'setColumn'=>array('uitype'), 'setValue'=>array(300));
		$changes[] = array('where'=>array('columnname'=>array('parent_id'),'tabid'=>array(getTabid('HelpDesk'))), 'setColumn'=>array('typeofdata'), 'setValue'=>array('I~M'));
		$changes[] = array('where'=>array('columnname'=>array('priority', 'projectpriority','projecttaskpriority')), 'setColumn'=>array('defaultvalue'), 'setValue'=>array('Low'));
		$changes[] = array('where'=>array('columnname'=>array('quotestage')), 'setColumn'=>array('defaultvalue'), 'setValue'=>array('Created'));
		$changes[] = array('where'=>array('columnname'=>array('sales_stage')), 'setColumn'=>array('defaultvalue'), 'setValue'=>array('Accepted for processing'));
		$changes[] = array('where'=>array('columnname'=>array('projecttaskstatus')), 'setColumn'=>array('defaultvalue', 'typeofdata'), 'setValue'=>array('Open','V~M'));
		$changes[] = array('where'=>array('columnname'=>array('solution')), 'setColumn'=>array('uitype'), 'setValue'=>array(300));
		$changes[] = array('where'=>array('columnname'=>array('start_hour')), 'setColumn'=>array('defaultvalue'), 'setValue'=>array('08:00'));
		$changes[] = array('where'=>array('columnname'=>array('status'), 'tabid'=>array('HelpDesk')), 'setColumn'=>array('defaultvalue'), 'setValue'=>array('Open'));
		$changes[] = array('where'=>array('columnname'=>array('status'), 'tabid'=>array('Calendar')), 'setColumn'=>array('defaultvalue'), 'setValue'=>array('Not Started'));
		$changes[] = array('where'=>array('columnname'=>array('totalduration'), 'tabid'=>array('PBXManager')), 'setColumn'=>array('uitype'), 'setValue'=>array(1));
		$changes[] = array('where'=>array('columnname'=>array('campaignrelstatus')), 'setColumn'=>array('fieldlabel'), 'setValue'=>array('Campaign status'));
		$changes[] = array('where'=>array('columnname'=>array('user_name'), 'tablename'=>array('vtiger_users')), 'setColumn'=>array('displaytype'), 'setValue'=>array(4));
		$changes[] = array('where'=>array('columnname'=>array('product_id'), 'tablename'=>array('vtiger_troubletickets')), 'setColumn'=>array('displaytype','uitype'), 'setValue'=>array(10,10));
		$changes[] = array('where'=>array('columnname'=>array('startdate'), 'tablename'=>array('vtiger_projecttask')), 'setColumn'=>array('typeofdata','quickcreate'), 'setValue'=>array('D~M',2));
		$changes[] = array('where'=>array('columnname'=>array('targetenddate'), 'tablename'=>array('vtiger_projecttask')), 'setColumn'=>array('typeofdata','quickcreate'), 'setValue'=>array('D~M',2));
		$changes[] = array('where'=>array('columnname'=>array('folderid'), 'tablename'=>array('vtiger_notes')), 'setColumn'=>array('uitype','defaultvalue','fieldparams'), 'setValue'=>array(302,'T1','1'));

		foreach($changes as $update){
			$setColumn = implode(' = ?, ',$update['setColumn']) . ' = ? ';
			$params = $update['setValue'];
			$i=0;
			$where = '';
			foreach($update['where'] as $whereColumn=>$whereValue){
				$where .= $whereColumn .' IN ('.generateQuestionMarks($whereValue).') ';
				$i++;
				if($i != count($update['where']))
					$where .= ' AND ';
				$params = array_merge($params, $whereValue);
			}
			$query = "UPDATE vtiger_field SET ".$setColumn." WHERE ".$where." ; ";
			$adb->pquery($query, $params);
		}
		
		$adb->query("DELETE FROM `vtiger_module_dashboard_widgets` ");
		//delete value?
		$adb->query("UPDATE vtiger_inventory_tandc SET tandc = '';");
		// delete all language
		$adb->query("DELETE FROM `vtiger_language` ");
		// add lang from yeti
		$lang[] = array('English','en_us','US English','2014-07-16 11:20:12',NULL,'1','1');
		$lang[] = array('Język Polski','pl_pl','Język Polski','2014-07-16 11:20:40',NULL,'0','1');
		$lang[] = array('Deutsch','de_de','DE Deutsch','2014-11-21 11:20:40',NULL,'0','1');
		$lang[] = array('Portuguese','pt_br','Brazilian Portuguese','2014-12-11 11:12:39',NULL,'0','1');
		$lang[] = array('Russian','ru_ru','Russian','2015-01-13 15:12:39',NULL,'0','1');
		foreach($lang as $params)
			$adb->pquery("insert  into `vtiger_language`(`name`,`prefix`,`label`,`lastupdated`,`sequence`,`isdefault`,`active`) values (?,?,?,?,?,?,?);", $params);
		$adb->query("UPDATE vtiger_language_seq SET `id` = (SELECT count(*) FROM `vtiger_language`);");
		$adb->pquery("UPDATE vtiger_version SET `current_version` = ? ;",array(1, $YetiForce_current_version));
		//update tax in inventoryproductrel
		$adb->query(" UPDATE `vtiger_inventoryproductrel` SET tax = 
			  CASE
				WHEN `tax1` IS NOT NULL 
				OR
				`tax2` IS NOT NULL 
				OR 
				`tax3` IS NOT NULL 
				THEN 'tax1' 
			  END,
			  tax1 = IFNULL(tax1, 0) + IFNULL(tax2, 0) + IFNULL(tax3, 0) ;"
		);
		$adb->pquery("UPDATE vtiger_calendar_default_activitytypes SET fieldname = ? WHERE `module` = ? AND fieldname = ? ;",array('End of support for contact','Contacts','support_end_date'));
		$adb->pquery("UPDATE vtiger_calendar_default_activitytypes SET fieldname = ? WHERE `module` = ? AND fieldname = ? ;",array('Birthdays of contacts','Contacts','birthday'));
		
		$instanceModule = Vtiger_Module::getInstance('Potentials');
		$instanceModule->addLink('DASHBOARDWIDGET', 'KPI', 'index.php?module=Potentials&view=ShowWidget&name=Kpi');
		$instanceModule = Vtiger_Module::getInstance('Home');
		$instanceModule->addLink('DASHBOARDWIDGET', 'Employees Time Control', 'index.php?module=OSSEmployees&view=ShowWidget&name=TimeControl');
		
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_fieldmodulerel`(`fieldid`,`module`,`relmodule`,`status`,`sequence`) values ((SELECT fieldid FROM `vtiger_field` WHERE `columnname` = 'product_id' AND `tablename` = 'vtiger_troubletickets'),'HelpDesk','Products',NULL,1);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_fieldmodulerel` WHERE module = 'HelpDesk' AND relmodule = 'Services'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_fieldmodulerel`(`fieldid`,`module`,`relmodule`,`status`,`sequence`) values ((SELECT fieldid FROM `vtiger_field` WHERE `columnname` = 'product_id' AND `tablename` = 'vtiger_troubletickets'),'HelpDesk','Services',NULL,2);");
		}
		$log->debug("Exiting VT610_to_YT::updateRecords() method ...");
	}
	
	public function addClosedtimeField(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::addClosedtimeField() method ...");
		$restrictedModules = array('Emails', 'Integration', 'Dashboard', 'ModComments', 'SMSNotifier','PBXManager');
		$sql = 'SELECT * FROM vtiger_tab WHERE isentitytype = ? AND name NOT IN ('.generateQuestionMarks($restrictedModules).')';
		$params = array(1, $restrictedModules);
		$tabresult = $adb->pquery($sql, $params,true);

		for($i = 0; $i < $adb->num_rows($tabresult); $i++){
			$tabId = $adb->query_result_raw($tabresult, $i, 'tabid');
			$fieldresult = $adb->query("SELECT * FROM `vtiger_field` WHERE tabid = '$tabId' AND columnname = 'closedtime'",true);
			$blockresult = $adb->query("SELECT block FROM `vtiger_field` WHERE tabid = '$tabId' AND columnname = 'createdtime'",true);
			if( $adb->num_rows($fieldresult) == 0 && $adb->num_rows($blockresult) > 0){
				$name = $adb->query_result_raw($tabresult, $i, 'name');
				$block = $adb->query_result_raw($blockresult, 0, 'block');
				
				$moduleInstance = Vtiger_Module::getInstance($name);
				$blockInstance = Vtiger_Block::getInstance($block,$moduleInstance);

				$fieldInstance = new Vtiger_Field(); 
				$fieldInstance->name = 'closedtime';
				$fieldInstance->table = 'vtiger_crmentity';
				$fieldInstance->label = 'Closed Time';
				$fieldInstance->column = 'closedtime';
				$fieldInstance->columntype = 'datetime';
				$fieldInstance->uitype = 70;
				$fieldInstance->typeofdata = 'DT~O'; 
				$fieldInstance->displaytype = 2;
				$blockInstance->addField($fieldInstance); 
			}
		}
		$log->debug("Exiting VT610_to_YT::addClosedtimeField() method ...");
	}
	
	public function pobox(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::pobox() method ...");
		$sql = "SELECT * FROM `vtiger_field` WHERE `fieldname` LIKE 'addresslevel1%';";
		$result = $adb->query($sql,true);
		$Num = $adb->num_rows($result);

		for($i = 0; $i < $Num; $i++){
			$row = $adb->query_result_rowdata($result, $i);
			$tabid = $row['tabid'];
			$moduleName = Vtiger_Functions::getModuleName($tabid);
			$block = $row['block'];
			$tablename = $row['tablename'];
			$fieldname = $row['fieldname'];
			$name = 'pobox'.substr($fieldname, -1);
			
			$moduleInstance = Vtiger_Module::getInstance($moduleName);
			$blockInstance = Vtiger_Block::getInstance($block,$moduleInstance);
			$fieldInstance = new Vtiger_Field(); 
			$fieldInstance->name = $name; 
			$fieldInstance->table = $tablename; 
			$fieldInstance->label = 'Po Box'; 
			$fieldInstance->column = $name; 
			$fieldInstance->columntype = 'varchar(50)'; 
			$fieldInstance->uitype = 1;
			$fieldInstance->typeofdata = 'V~O'; 
			$blockInstance->addField($fieldInstance);
		}
		$log->debug("Exiting VT610_to_YT::pobox() method ...");
	}
	
	public function wasRead(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::wasRead() method ...");
		$sql = "SELECT tabid,name FROM `vtiger_tab` WHERE `isentitytype` = '1' AND name not in ('SMSNotifier','ModComments','PBXManager','Events','Emails','');";
		$result = $adb->query($sql,true);
		$Num = $adb->num_rows($result);
		for($i = 0; $i < $Num; $i++){
			$name = $adb->query_result($result, $i, 'name');
			$tabid = $adb->query_result($result, $i, 'tabid');
			$row = $adb->query_result_rowdata($result, $i); 
			$result2 = $adb->pquery('SELECT * FROM vtiger_field WHERE tabid = ? AND block <> ? ORDER BY block, sequence ASC',array($tabid,0));
			$block = $adb->query_result_raw($result2, 0, 'block');

			$moduleInstance = Vtiger_Module::getInstance($name);
			$blockInstance = Vtiger_Block::getInstance($block,$moduleInstance);
			$fieldInstance = new Vtiger_Field(); 
			$fieldInstance->name = 'was_read'; 
			$fieldInstance->table = 'vtiger_crmentity'; 
			$fieldInstance->label = 'Was read'; 
			$fieldInstance->column = 'was_read'; 
			$fieldInstance->columntype = 'tinyint(1)'; 
			$fieldInstance->uitype = 56;
			$fieldInstance->typeofdata = 'C~O'; 
			$fieldInstance->displaytype = 2;
			$blockInstance->addField($fieldInstance); 

		}
		$log->debug("Exiting VT610_to_YT::wasRead() method ...");
	}
	
	public function fieldsModuleRel(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::fieldsModuleRel() method ...");
		
		$fieldsModuleRel = array();
		//$fieldsModuleRel[] = array('field'=>'related_to','module'=>'ModComments', 'relmodule'=>array('OSSTimeControl','OSSEmployees'));//testing
		$fieldsModuleRel[] = array('field'=>'parent_id','module'=>'HelpDesk', 'relmodule'=>array('Contacts'));
		foreach($fieldsModuleRel as $column=>$fieldModuleRel){
			$moduleModel = Vtiger_Module_Model::getInstance($fieldModuleRel['module']);
			$fieldModel = Vtiger_Field_Model::getInstance($fieldModuleRel['field'], $moduleModel);
			$fieldModel->setRelatedModules($fieldModuleRel['relmodule']);
		}
		$log->debug("Exiting VT610_to_YT::fieldsModuleRel() method ...");
	}
	public function actionMapping(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::actionMapping() method ...");

		
		$adb->query("insert into `vtiger_actionmapping`(`actionid`,`actionname`,`securitycheck`) values (14,'CreateCustomFilter',0);");
		$adb->query("insert into `vtiger_actionmapping`(`actionid`,`actionname`,`securitycheck`) values (15,'DuplicateRecord',0);");
		$adb->query("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES ('16', 'EditableComments','0');");

		$profileresult = $adb->query('SELECT * FROM `vtiger_profile`',true);
		for($p = 0; $p < $adb->num_rows($profileresult); $p++){
			$profileId = $adb->query_result_raw($profileresult, $p, 'profileid');
			
			$adb->pquery("INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES  (?, ?, ?, ?)", array($profileId, 40, 16, 0));
		}
		Vtiger_Deprecated::createModuleMetaFile();
		Vtiger_Access::syncSharingAccess();
		$log->debug("Exiting VT610_to_YT::actionMapping() method ...");
	}
	
	public function getPicklistId($fieldName, $value){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::getPicklistId(".$fieldName.','.$value.") method ...");
		if(Vtiger_Utils::CheckTable('vtiger_' .$fieldName)) {
			$sql = 'SELECT * FROM vtiger_' .$fieldName. ' WHERE ' .$fieldName. ' = ? ;';
			$result = $adb->pquery($sql, array($value));
			if($adb->num_rows($result) > 0){
				$log->debug("Exiting VT610_to_YT::getPicklistId() method ...");
				return $adb->query_result($result, 0, 'picklist_valueid');
			}
		}
		$log->debug("Exiting VT610_to_YT::getPicklistId() method ...");
		return false;
		
	}
	
	public function changeOutgoingServerFile($id){
		global $log,$adb,$root_directory;
		$log->debug("Entering VT610_to_YT::changeOutgoingServerFile(".$id.") method ...");
		
		if(!$root_directory)
			$root_directory = getcwd();
		$fileName = $root_directory.'/modules/Settings/Vtiger/models/OutgoingServer.php';
		$completeData = file_get_contents($fileName);
		$updatedFields = "'id'";
		$patternString = "%s => %s,";
		$pattern = '/' . $updatedFields . '[\s]+=([^,]+),/';
		$replacement = sprintf($patternString, $updatedFields, ltrim($id, '0'));
		$fileContent = preg_replace($pattern, $replacement, $completeData);
		$filePointer = fopen($fileName, 'w');
		fwrite($filePointer, $fileContent);
		fclose($filePointer);
		
		$log->debug("Exiting VT610_to_YT::changeOutgoingServerFile() method ...");
	}
	
	public function updateForgotPassword($id){
		global $log,$adb,$root_directory;
		$log->debug("Entering VT610_to_YT::updateForgotPassword(".$id.") method ...");
		if(!$root_directory)
			$root_directory = getcwd();
		$fileName = $root_directory.'/modules/Users/actions/ForgotPassword.php';
		$completeData = file_get_contents($fileName);
		$updatedFields = "'id'";
		$patternString = "%s => %s,";
		$pattern = '/' . $updatedFields . '[\s]+=([^,]+),/';
		$replacement = sprintf($patternString, $updatedFields, ltrim($id, '0'));
		$fileContent = preg_replace($pattern, $replacement, $completeData);
		$filePointer = fopen($fileName, 'w');
		fwrite($filePointer, $fileContent);
		fclose($filePointer);
		
		$log->debug("Exiting VT610_to_YT::updateForgotPassword() method ...");
	}
	
	public function addRecords(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::addRecords() method ...");
		//include('config/config.inc.php');
		global $dbconfig;
		$assigned_user_id = $this->adminId;
		$user = new Users();
		$current_user = $user->retrieveCurrentUserInfoFromFile( $assigned_user_id );
		$moduleName = 'OSSMailTemplates';
		vimport('~~modules/' . $moduleName . '/' . $moduleName . '.php');
		$records = array();

$records[] = array('Notify Owner On Ticket Change','HelpDesk','#t#LBL_NOTICE_MODIFICATION#tEnd# #a#155#aEnd#: #a#169#aEnd#','<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_UPDATED#tEnd# #a#168#aEnd#). #s#ChangesList#sEnd#

<hr /><h1><a href="%23s%23LinkToCRMRecord%23sEnd%23">#t#LBL_NOTICE_MODIFICATION#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
	<li>#b#718#bEnd#: #a#718#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /> #b#171#bEnd#: #a#171#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
$records[] = array('Notify Account On Ticket Change','HelpDesk','#t#LBL_NOTICE_MODIFICATION#tEnd# #a#155#aEnd#: #a#169#aEnd#','<div>
<h3><span>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></span></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_UPDATED#tEnd# #a#168#aEnd#). #s#ChangesList#sEnd#

<hr /><h1><a href="%23s%23LinkToPortalRecord%23sEnd%23">#t#LBL_NOTICE_MODIFICATION#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
	<li>#b#718#bEnd#: #a#718#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /> #b#171#bEnd#: #a#171#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
$records[] = array('Notify Contact On Ticket Closed','HelpDesk','#t#LBL_NOTICE_CLOSE#tEnd# #a#155#aEnd#: #a#169#aEnd#','<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_CLOSED#tEnd# #a#168#aEnd#). #s#ChangesList#sEnd#

<hr /><h1><a href="%23s%23LinkToPortalRecord%23sEnd%23">#t#LBL_NOTICE_CLOSE#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
	<li>#b#718#bEnd#: #a#718#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /> #b#171#bEnd#: #a#171#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
$records[] = array('Notify Account On Ticket Closed','HelpDesk','#t#LBL_NOTICE_CLOSE#tEnd# #a#155#aEnd#: #a#169#aEnd#','<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_CLOSED#tEnd# #a#168#aEnd#). #s#ChangesList#sEnd#

<hr /><h1><a href="%23s%23LinkToPortalRecord%23sEnd%23">#t#LBL_NOTICE_CLOSE#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
	<li>#b#718#bEnd#: #a#718#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /> #b#171#bEnd#: #a#171#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
$records[] = array('Notify Contact On Ticket Create','HelpDesk','#t#LBL_NOTICE_CREATE#tEnd# #a#155#aEnd#: #a#169#aEnd#','<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_CREATED#tEnd# #a#168#aEnd#).

<hr /><h1><a href="%23s%23LinkToPortalRecord%23sEnd%23">#t#LBL_NOTICE_CREATE#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
	<li>#b#718#bEnd#: #a#718#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
$records[] = array('Notify Account On Ticket Create','HelpDesk','#t#LBL_NOTICE_CREATE#tEnd# #a#155#aEnd#: #a#169#aEnd#','<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_CREATED#tEnd# #a#168#aEnd#).

<hr /><h1><a href="%23s%23LinkToPortalRecord%23sEnd%23">#t#LBL_NOTICE_CREATE#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
	<li>#b#718#bEnd#: #a#718#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
$records[] = array('Notify Contact On Ticket Change','HelpDesk','#t#LBL_NOTICE_MODIFICATION#tEnd# #a#155#aEnd#: #a#169#aEnd#','<div>
<h3><span>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></span></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_UPDATED#tEnd# #a#168#aEnd#). #s#ChangesList#sEnd#

<hr /><h1><a href="%23s%23LinkToPortalRecord%23sEnd%23">#t#LBL_NOTICE_MODIFICATION#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
	<li>#b#718#bEnd#: #a#718#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /> #b#171#bEnd#: #a#171#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
$records[] = array('Notify Owner On Ticket Closed','HelpDesk','#t#LBL_NOTICE_CLOSE#tEnd# #a#155#aEnd#: #a#169#aEnd#','<div>
<h3><span>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></span></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_CLOSED#tEnd# #a#168#aEnd#). #s#ChangesList#sEnd#

<hr /><h1><a href="%23s%23LinkToCRMRecord%23sEnd%23">#t#LBL_NOTICE_CLOSE#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
	<li>#b#718#bEnd#: #a#718#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /> #b#171#bEnd#: #a#171#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
$records[] = array('Notify Owner On Ticket Create','HelpDesk','#t#LBL_NOTICE_CREATE#tEnd# #a#155#aEnd#: #a#169#aEnd#','<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_CREATED#tEnd# #a#168#aEnd#).

<hr /><h1><a href="%23s%23LinkToCRMRecord%23sEnd%23">#t#LBL_NOTICE_CREATE#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
	<li>#b#718#bEnd#: #a#718#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
$records[] = array('Customer Portal Login Details','Contacts','Customer Portal Login Details','<p>#s#LogoImage#sEnd# </p><p>Dear #a#67#aEnd#  #a#68#aEnd#</p><p>Created for your account in the customer portal, below sending data access.</p><p>Login: #a#80#aEnd#<br />Password: #s#ContactsPortalPass#sEnd#</p><p>Regards</p>');
$records[] = array('Send invitations','Events','#a#267#aEnd#:  #a#255#aEnd#','<table border="0" cellpadding="8" cellspacing="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;" summary=""><tbody><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>#a#255#aEnd#</span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0" cellpadding="0" cellspacing="0"><tbody><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;" valign="top">
						<div><i style="font-style:normal;">#b#257#bEnd#</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;" valign="top">#a#257#aEnd# #a#258#aEnd#</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;" valign="top">
						<div><i style="font-style:normal;">#b#259#bEnd#</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;" valign="top">#a#259#aEnd# #a#260#aEnd#</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;" valign="top">
						<div><i style="font-style:normal;">#b#264#bEnd#</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;" valign="top">#a#264#aEnd#</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;" valign="top">
						<div><i style="font-style:normal;">#b#277#bEnd#</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;" valign="top">#a#277#aEnd#</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;" valign="top">
						<div><i style="font-style:normal;">#b#267#bEnd#</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;" valign="top">#a#267#aEnd#</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;" valign="top">
						<div><i style="font-style:normal;">#b#271#bEnd#</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;" valign="top">#a#271#aEnd#</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;" valign="top">
						<div><i style="font-style:normal;">#b#268#bEnd#</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;" valign="top"><span><span>#a#268#aEnd#</span><span dir="ltr"> (<a href="https://maps.google.pl/maps?q=%23a%23268%23aEnd%23" style="color:#20c;white-space:nowrap;">mapa</a>)</span></span></td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;" valign="top">
						<div><i style="font-style:normal;">#b#265#bEnd#</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;" valign="top">#a#265#aEnd#</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;" valign="top">
						<div><i style="font-style:normal;">#b#275#bEnd#</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;" valign="top">#a#275#aEnd#</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;" valign="top">
						<div><i style="font-style:normal;">#b#256#bEnd#</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;" valign="top">#a#256#aEnd#</td>
					</tr></tbody></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<p>YetiForce CRM - Notification activities on the calendar</p>
			</td>
		</tr></tbody></table>');
$records[] = array('Send Notification Email to Record Owner','Calendar','Task :  #a#231#aEnd#','#a#232#aEnd#<br /><br />Activity Notification Details:<br />Subject : #a#231#aEnd#<br />Start date and time : #a#233#aEnd# #a#234#aEnd#<br />End date and time : #a#235#aEnd# #a#236#aEnd#<br />Status : #a#239#aEnd#<br />Priority : #a#241#aEnd#<br />Related To : #a#237#aEnd#<br />Contacts List : #a#238#aEnd#<br />Location : #a#250#aEnd#<br />Description : #a#247#aEnd#');
$records[] = array('Activity Reminder Notification','Calendar','Reminder:  #a#231#aEnd#','This is a reminder notification for the Activity:<br />Subject: #a#231#aEnd#<br />Date & Time: #a#233#aEnd# #a#234#aEnd#<br /><span style="color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:20.7999992370605px;">Contact Name: </span>#a#238#aEnd#<br style="color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:20.7999992370605px;" /><span style="color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:20.7999992370605px;">Related To: </span>#a#237#aEnd#<br style="color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:20.7999992370605px;" /><span style="color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:20.7999992370605px;">Description: </span>#a#247#aEnd#');
$records[] = array('Activity Reminder Notification','Events','Reminder: #a#255#aEnd#','<span style="line-height:20.7999992370605px;">This is a reminder notification for the Activity:</span><br style="line-height:20.7999992370605px;" /><span style="line-height:20.7999992370605px;">Subject:</span>#a#255#aEnd#<br style="line-height:20.7999992370605px;" /><span style="line-height:20.7999992370605px;">Date & Time: </span>#a#257#aEnd# #a#258#aEnd#<br style="line-height:20.7999992370605px;" /><span style="line-height:20.7999992370605px;color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;">Contact Name: </span>#a#277#aEnd#<br style="line-height:20.7999992370605px;color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;" /><span style="line-height:20.7999992370605px;color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;">Related To: </span>#a#264#aEnd#<br style="line-height:20.7999992370605px;color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;" /><span style="line-height:20.7999992370605px;color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;">Description: </span>#a#275#aEnd#');
$records[] = array('Test mail about the mail server configuration.','Users','Test mail about the mail server configuration.','<span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Dear </span>#a#478#aEnd# #a#479#aEnd#<span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">, </span><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><b style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">This is a test mail sent to confirm if a mail is actually being sent through the smtp server that you have configured. </b><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Feel free to delete this mail. </span><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Thanks and Regards,</span><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Team YetiForce</span>');
$records[] = array('ForgotPassword','Users','Request: ForgotPassword','Dear user,<br /><br />\r\nYou recently requested a password reset for your YetiForce CRM.<br />\r\nTo create a new password, click on the link #s#LinkToForgotPassword#sEnd#.<br /><br />\r\nThis request was made on #s#CurrentDateTime#sEnd# and will expire in next 24 hours.<br /><br />\r\nRegards,<br />\r\nYetiForce CRM Support Team.');
$records[] = array('Customer Portal - ForgotPassword','Contacts','Request: ForgotPassword','Dear #a#67#aEnd# #a#67#aEnd#,<br /><br />
You recently requested a reminder of your access data for the YetiForce Portal.<br /><br />
You can login by entering the following data:<br /><br />
Your username: #a#80#aEnd#<br />
Your password: #s#ContactsPortalPass#sEnd#<br /><br /><br />
Regards,<br />
YetiForce CRM Support Team.');
		foreach($records as $record){
			try {
				$instance = new $moduleName();
				$instance->column_fields['assigned_user_id'] = $assigned_user_id;
				$instance->column_fields['name'] = $record[0];
				$instance->column_fields['oss_module_list'] = $record[1];
				$instance->column_fields['subject'] = $record[2];
				$instance->column_fields['content'] = $record[3];
				$save = $instance->save($moduleName);
				if($record[0] == 'Test mail about the mail server configuration.')
					self::changeOutgoingServerFile($instance->id);
				if($record[0] == 'ForgotPassword')
					self::updateForgotPassword($instance->id);
			} catch (Exception $e) {
				Install_InitSchema_Model::addMigrationLog('addRecords '.$e->getMessage(),'error');
			}
		}
		//vtiger_osspdf
		$moduleName = 'OSSPdf';
		vimport('~~modules/' . $moduleName . '/' . $moduleName . '.php');
		$records = array();
		$records[] = array('Quotes PDF','20','A4','Portrait','<title></title>
<title></title>
<table align="left" border="0" cellpadding="1" cellspacing="1" style="width: 100%;">
	<tbody>
		<tr>
			<td><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>#company_organizationname#</strong><br />
			#company_address#<br />
			#company_code# #company_city#<br />
			#company_country#<br />
			tel.: #company_phone#<br />
			fax: #company_fax#<br />
			WWW: <a href="#company_website#"> #company_website#</a><br />
			VAT: #company_vatid#</span></span></td>
			<td>&nbsp;</td>
			<td>
			<div style="text-align: right;"><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#company_city#, #special_function#CurrentDate#end_special_function#</span></span></div>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#Contacts_firstname# #Contacts_lastname#<br />
			tel: #Contacts_phone#<br />
			email: #Contacts_email#<br />
			<br />
			<strong>#Accounts_accountname#</strong><br />
			#addresslevel8a# #buildingnumbera# #localnumbera#<br />
			#addresslevel7a# #addresslevel5a#<br />
			#Accounts_label_vat_id#: #Accounts_vat_id#</span></span></td>
		</tr>
	</tbody>
</table>

<p style="text-align: center;"><br />
<span style="font-size:14px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>Offer #quote_no#</strong></span></span></p>
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#description#</span></span><br />
<span>#special_function#replaceProductTable#end_special_function#</span><br />
<br />
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>#label_attention#: </strong>#attention#<br />
<strong>#label_currency_id#: </strong>#currency_id#<br />
<strong>#label_validtill#: </strong>#validtill#<br />
<strong>#label_shipping#: </strong>#shipping#<br />
<strong>#label_form_payment#: </strong>#form_payment#<br />
<strong>#label_terms_conditions#:</strong>#terms_conditions#</span></span><br />
<br />
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#Users_first_name# #Users_last_name#<br />
email: <a href="mailto:#Users_email1#">#Users_email1#</a><br />
<br />
<strong>#company_organizationname#</strong><br />
tel.: #company_phone#<br />
fax: #company_fax#<br />
WWW: <a href="#company_website#"> #company_website#</a></span></span>','','','10','10','10','10','No','No','','','No','1','1','1','Lista |##| Podgląd');
		$records[] = array('Sales Order PDF','22','A4','Portrait','<title></title>
<title></title>
<table align="left" border="0" cellpadding="1" cellspacing="1" style="width: 100%;">
	<tbody>
		<tr>
			<td><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>#company_organizationname#</strong><br />
			#company_address#<br />
			#company_code# #company_city#<br />
			#company_country#<br />
			tel.: #company_phone#<br />
			fax: #company_fax#<br />
			WWW: <a href="#company_website#"> #company_website#</a><br />
			VAT: #company_vatid#</span></span></td>
			<td>&nbsp;</td>
			<td>
			<div style="text-align: right;"><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#company_city#, #special_function#CurrentDate#end_special_function#
			</span></span></div>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#Contacts_firstname# #Contacts_lastname#<br />
			tel: #Contacts_phone#<br />
			email: #Contacts_email#<br />
			<br />
			<strong>#Accounts_accountname#</strong><br />
			#addresslevel8a# #buildingnumbera# #localnumbera#<br />
			#addresslevel7a# #addresslevel5a#<br />
			#Accounts_label_vat_id#: #Accounts_vat_id#</span></span></td>
		</tr>
	</tbody>
</table>

<p style="text-align: center;"><br />
<span style="font-size:14px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>Sales Order #salesorder_no#</strong></span></span></p>
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#description#</span></span><br />
<span>#special_function#replaceProductTable#end_special_function#</span><br />
<br />
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>#label_attention#: </strong>#attention#<br />
<strong>#label_currency_id#: </strong>#currency_id#<br />
<strong>#label_duedate#: </strong>#duedate#<br />
<strong>#label_form_payment#: </strong>#form_payment#<br />
<strong>#Quotes_label_quote_no#: </strong>#Quotes_quote_no#<br />
<strong>#label_terms_conditions#:</strong>#terms_conditions#</span></span><br />
<br />
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#Users_first_name# #Users_last_name#<br />
email: <a href="mailto:#Users_email1#">#Users_email1#</a><br />
<br />
<strong>#company_organizationname#</strong><br />
tel.: #company_phone#<br />
fax: #company_fax#<br />
WWW: <a href="#company_website#"> #company_website#</a></span></span>','','','10','10','10','10','No','No','','','No','1','1','1','Lista |##| Podgląd');
		$records[] = array('Invoice PDF','23','A4','Portrait','<title></title>
<title></title>
<table align="left" border="0" cellpadding="1" cellspacing="1" style="width: 100%;">
	<tbody>
		<tr>
			<td><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>#company_organizationname#</strong><br />
			#company_address#<br />
			#company_code# #company_city#<br />
			#company_country#<br />
			tel.: #company_phone#<br />
			fax: #company_fax#<br />
			WWW: <a href="#company_website#"> #company_website#</a><br />
			VAT: #company_vatid#</span></span></td>
			<td>&nbsp;</td>
			<td>
			<div style="text-align: right;"><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#label_invoicedate#: #invoicedate#
			</span></span></div>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#Contacts_firstname# #Contacts_lastname#<br />
			tel: #Contacts_phone#<br />
			email: #Contacts_email#<br />
			<br />
			<strong>#Accounts_accountname#</strong><br />
			#addresslevel8a# #buildingnumbera# #localnumbera#<br />
			#addresslevel7a# #addresslevel5a#<br />
			#Accounts_label_vat_id#: #Accounts_vat_id#</span></span></td>
		</tr>
	</tbody>
</table>

<p style="text-align: center;"><br />
<span style="font-size:14px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>Invoice #invoice_no#</strong></span></span></p>
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#description#</span></span><br />
<span>#special_function#replaceProductTable#end_special_function#</span><br />
<br />
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>#label_attention#: </strong>#attention#<br />
<strong>#label_currency_id#: </strong>#currency_id#<br />
<strong>#label_invoicedate#: </strong>#invoicedate#<br />
<strong>#label_duedate#: </strong>#duedate#<br />
<strong>#label_form_payment#: </strong>#form_payment#<br />
<strong>Sales Order: </strong>#SalesOrder_salesorder_no#<br />
<strong>#label_terms_conditions#:</strong>#terms_conditions#</span></span><br />
<br />
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#Users_first_name# #Users_last_name#<br />
email: <a href="mailto:#Users_email1#">#Users_email1#</a><br />
<br />
<strong>#company_organizationname#</strong><br />
tel.: #company_phone#<br />
fax: #company_fax#<br />
WWW: <a href="#company_website#"> #company_website#</a></span></span>','','','10','10','10','10','No','No','','','No','1','1','1','Lista |##| Podgląd');
		$records[] = array('Purchase Order PDF','21','A4','Portrait','<title></title>
<title></title>
<table align="left" border="0" cellpadding="1" cellspacing="1" style="width: 100%;">
	<tbody>
		<tr>
			<td><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>#company_organizationname#</strong><br />
			#company_address#<br />
			#company_code# #company_city#<br />
			#company_country#<br />
			tel.: #company_phone#<br />
			fax: #company_fax#<br />
			WWW: <a href="#company_website#"> #company_website#</a><br />
			VAT: #company_vatid#</span></span></td>
			<td>&nbsp;</td>
			<td>
			<div style="text-align: right;"><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#company_city#, #special_function#CurrentDate#end_special_function# </span></span></div>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#Contacts_firstname# #Contacts_lastname#<br />
			tel: #Contacts_phone#<br />
			email: #Contacts_email#<br />
			<br />
			<strong>#Vendors_vendorname#</strong><br />
			#addresslevel8a# #buildingnumbera# #localnumbera#<br />
			#addresslevel7a# #addresslevel5a#<br />
			#Vendors_label_vat_id#: #Vendors_vat_id#</span></span></td>
		</tr>
	</tbody>
</table>

<p style="text-align: center;"><br />
<span style="font-size:14px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>Order confirmation #purchaseorder_no#</strong></span></span></p>
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">Thank you for your order. Herewith we are pleased to confirm it as follows.<br />
#description#</span></span><br />
<span>#special_function#replaceProductTable#end_special_function#</span><br />
<br />
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;"><strong>#label_attention#: </strong>#attention#<br />
<strong>#label_currency_id#: </strong>#currency_id#<br />
<strong>#label_duedate#: </strong>#duedate#<br />
<strong>#label_terms_conditions#:</strong>#terms_conditions#</span></span><br />
<br />
<span style="font-size:9px;"><span style="font-family: tahoma,geneva,sans-serif;">#Users_first_name# #Users_last_name#<br />
email: <a href="mailto:#Users_email1#">#Users_email1#</a><br />
<br />
<strong>#company_organizationname#</strong><br />
tel.: #company_phone#<br />
fax: #company_fax#<br />
WWW: <a href="#company_website#"> #company_website#</a></span></span>','','','10','10','10','10','No','No','','','No','1','1','1','Lista |##| Podgląd');
		$records[] = array('Calculation PDF','70','A4','Portrait','','','','10','10','10','10','No','No','','<title></title>
<table width="537px">
	<tbody>
		<tr>
			<td colspan="6" rowspan="2"><img src="#special_function#siteUrl#end_special_function#storage/Logo/logo_yetiforce.png" style="width: 200px;" width="200" /></td>
			<td colspan="4"><span style="font-size:6px;">#company_organizationname# #company_address# #company_code# #company_city#. VAT:#company_vatid#</span></td>
		</tr>
		<tr>
			<td colspan="5">
			<table border="1">
				<tbody>
					<tr>
						<td>
						<table cellpadding="1">
							<tbody>
								<tr>
									<td style="text-align: center;"><span style="font-size:9px;">Calculation confirmation: <strong>#calculations_no#</strong></span></td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
					<tr>
						<td>
						<table cellpadding="1">
							<tbody>
								<tr>
									<td style="text-align: center;"><span style="font-size:9px;">Date: #special_function#CreatedDateTime#end_special_function#</span></td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td colspan="7">&nbsp;</td>
			<td colspan="5" rowspan="2">
			<table border="1">
				<tbody>
					<tr>
						<td>
						<table cellpadding="5">
							<tbody>
								<tr>
									<td>
									<table cellpadding="0" style="font-size:8px;">
										<tbody>
											<tr>
												<td colspan="2">Issued by:</td>
												<td colspan="3">#Users_first_name# #Users_last_name#</td>
											</tr>
											<tr>
												<td colspan="2">Email:</td>
												<td colspan="3">#Users_email1#</td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			<table>
				<tbody>
					<tr>
						<td><span style="font-size:10px;">&nbsp;<span style="font-size:8px;">#Accounts_account_no#</span></span></td>
					</tr>
					<tr>
						<td>
						<table>
							<tbody>
								<tr>
									<td>
									<p><span style="font-size:10px;">#Accounts_accountname#<br />
									<span style="font-size:8px;">#Accounts_addresslevel8b# #Accounts_buildingnumberb# #Accounts_localnumberb#<br />
									#Accounts_addresslevel7b#, #Accounts_addresslevel5b#<br />
									<span style="font-size:10px;">#Accounts_addresslevel1b#</span><br />
									#Accounts_vat_id#<br />
									#Contacts_email#</span></span></p>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td colspan="3">&nbsp;</td>
		</tr>
	</tbody>
</table>
&nbsp;

<table>
	<tbody>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>#special_function#replaceProductTable#end_special_function#</td>
		</tr>
	</tbody>
</table>','No','1','1','0','Lista |##| Podgląd');
		
		foreach($records as $record){
			try {
				$instance = new $moduleName();
				$instance->column_fields['assigned_user_id'] = $assigned_user_id;
				$instance->column_fields['title'] = $record[0];
				$instance->column_fields['moduleid'] = $record[1];
				$instance->column_fields['osspdf_pdf_format'] = $record[2];
				$instance->column_fields['osspdf_pdf_orientation'] = $record[3];
				$instance->column_fields['content'] = $record[4];
				$instance->column_fields['constraints'] = $record[5];
				$instance->column_fields['filename'] = $record[6];
				$instance->column_fields['left_margin'] = $record[7];
				$instance->column_fields['right_margin'] = $record[8];
				$instance->column_fields['top_margin'] = $record[9];
				$instance->column_fields['bottom_margin'] = $record[10];
				$instance->column_fields['osspdf_enable_footer'] = $record[11];
				$instance->column_fields['osspdf_enable_header'] = $record[12];
				$instance->column_fields['header_content'] = $record[13];
				$instance->column_fields['footer_content'] = $record[14];
				$instance->column_fields['osspdf_enable_numbering'] = $record[15];
				$instance->column_fields['height_header'] = $record[16];
				$instance->column_fields['height_footer'] = $record[17];
				$instance->column_fields['selected'] = $record[18];
				$instance->column_fields['osspdf_view'] = $record[19];
				$saved = $instance->save($moduleName);
			} catch (Exception $e) {
				Install_InitSchema_Model::addMigrationLog('addRecords '.$e->getMessage(),'error');
			}
		}
		// vtiger_picklist_dependency
		$sql = "SELECT * FROM `vtiger_picklist_dependency_seq`;";
		$result = $adb->query( $sql );
        $num = $adb->num_rows( $result );
		if(!$num){
			$adb->pquery('insert  into `vtiger_picklist_dependency_seq`(`id`) values (?);',array(0));
		}
		$moduleName = 'Leads';
		$dependencyMap['sourcefield'] = 'industry';
		$dependencyMap['targetfield'] = 'subindustry';
		$targetvalues = '["Ministry","Chancellery","Voivodeship Office","Marshal Office","Poviat","City\/Township\/District","Social Welfare Centre","Water and Sewerage Company","Voivodeship Job Centre","Poviat Job Centre","Court of justice","Attorney General\'s Office","Other"]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Administration','targetvalues'=>$targetvalues);
		$targetvalues = '["Other","Deweloperzy","Real Estate"]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Construction Industry','targetvalues'=>$targetvalues);
		$targetvalues = '["Other","Primary Schools","High Schools"]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Education','targetvalues'=>$targetvalues);
		$targetvalues = '[""]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Power Industry','targetvalues'=>$targetvalues);
		$targetvalues = '["Other","Banking","Capital Market","Financial Services","Investments","Insurance"]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Finance','targetvalues'=>$targetvalues);
		$targetvalues = '["Other","Retail","Wholesale","Resale"]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Trade','targetvalues'=>$targetvalues);
		$targetvalues = '[""]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Hotels and Restaurants','targetvalues'=>$targetvalues);
		$targetvalues = '[""]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Health Care','targetvalues'=>$targetvalues);
		$targetvalues = '["Other","Automotive","Plastics","Chamical","Raw material","Fuel","Wood and paper","Electromechanical","Pharmaceutical","Building Materials","Metal","Light","Food industry","Recycling"]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Industry / Manufacturing','targetvalues'=>$targetvalues);
		$targetvalues = '["Army","Police"]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Uniformed Services','targetvalues'=>$targetvalues);
		$targetvalues = '[""]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Transport & Logistics','targetvalues'=>$targetvalues);
		$targetvalues = '["Other","Information Technology","Telecommunication","Media"]';
		$targetvalues = Zend_Json::decode($targetvalues);
		$dependencyMap['valuemapping'][] = array('sourcevalue'=>'Technologies','targetvalues'=>$targetvalues);
		Vtiger_DependencyPicklist::savePickListDependencies($moduleName, $dependencyMap);
		//info on migration
		$adb->pquery("INSERT INTO yetiforce_updates (`time`, `user`, `name`, `from_version`, `to_version`, `result`) VALUES  (?, ?, ?, ?, ?, ?)", array(date('Y-m-d H:i:s'), $this->adminId, 'migration', $this->name, 'Yetiforce CRM 1.0.0', 1 ));
		$log->debug("Exiting VT610_to_YT::addRecords() method ...");
	}
	public function customerPortal(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::customerPortal() method ...");
		$portal_tabs[] = array(getTabid('Contacts'),1,8);
		$portal_tabs[] = array(getTabid('Accounts'),1,9);
		$portal_tabs[] = array(getTabid('Documents'),1,10);
		$portal_tabs[] = array(getTabid('HelpDesk'),1,1);
		$portal_tabs[] = array(getTabid('Products'),1,13);
		$portal_tabs[] = array(getTabid('Faq'),1,11);
		$portal_tabs[] = array(getTabid('Quotes'),1,2);
		$portal_tabs[] = array(getTabid('SalesOrder'),1,3);
		$portal_tabs[] = array(getTabid('Invoice'),1,4);
		$portal_tabs[] = array(getTabid('Services'),1,12);
		$portal_tabs[] = array(getTabid('Assets'),1,14);
		$portal_tabs[] = array(getTabid('ProjectMilestone'),1,7);
		$portal_tabs[] = array(getTabid('ProjectTask'),1,6);
		$portal_tabs[] = array(getTabid('Project'),1,5);
		
		$sql = "SELECT `tabid` FROM `vtiger_customerportal_tabs`;";
		$result = $adb->query( $sql, true );
        $num = $adb->num_rows( $result );
		$tabId = array();
        for ( $i=0; $i<$num; $i++ ) {
			$tabId[] = $adb->query_result( $result, $i, 'tabid' );
			
		}
		foreach($portal_tabs as $portal){
			if(in_array($portal[0],$tabId))
				continue;
			$count = self::countRow('vtiger_customerportal_tabs', 'sequence');
			$adb->pquery("INSERT INTO vtiger_customerportal_tabs (tabid,visible,sequence) VALUES (?,?,?)", array($portal[0], $portal[1], ++$count));
			$adb->pquery("INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (?,?,?)", array($portal[0],'showrelatedinfo', 1));
		}
		$log->debug("Exiting VT610_to_YT::customerPortal() method ...");
	}
	
	public function customView(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::customView() method ...");
		$columnList = array();
		$columnList['Leads'][] = array(1,1,'vtiger_leaddetails:firstname:firstname:Leads_First_Name:V');
		$columnList['Leads'][] = array(1,2,'vtiger_leaddetails:lastname:lastname:Leads_Last_Name:V');
		$columnList['Leads'][] = array(1,3,'vtiger_leaddetails:company:company:Leads_Company:V');
		$columnList['Leads'][] = array(1,4,'vtiger_leadaddress:phone:phone:Leads_Phone:V');
		$columnList['Leads'][] = array(1,5,'vtiger_leadsubdetails:website:website:Leads_Website:V');
		$columnList['Leads'][] = array(1,6,'vtiger_leaddetails:email:email:Leads_Email:E');
		$columnList['Leads'][] = array(1,7,'vtiger_crmentity:smownerid:assigned_user_id:Leads_Assigned_To:V');
		$columnList['Accounts'][] = array(4,1,'vtiger_account:accountname:accountname:Accounts_Account_Name:V');
		$columnList['Accounts'][] = array(4,2,'vtiger_accountbillads:bill_city:bill_city:Accounts_City:V');
		$columnList['Accounts'][] = array(4,3,'vtiger_account:website:website:Accounts_Website:V');
		$columnList['Accounts'][] = array(4,4,'vtiger_account:phone:phone:Accounts_Phone:V');
		$columnList['Accounts'][] = array(4,5,'vtiger_crmentity:smownerid:assigned_user_id:Accounts_Assigned_To:V');
		$columnList['Contacts'][] = array(7,1,'vtiger_contactdetails:firstname:firstname:Contacts_First_Name:V');
		$columnList['Contacts'][] = array(7,2,'vtiger_contactdetails:lastname:lastname:Contacts_Last_Name:V');
		$columnList['Contacts'][] = array(7,4,'vtiger_contactdetails:parentid:parent_id:Contacts_Member_Of:I');
		$columnList['Contacts'][] = array(7,5,'vtiger_contactdetails:email:email:Contacts_Email:E');
		$columnList['Contacts'][] = array(7,6,'vtiger_contactdetails:phone:phone:Contacts_Office_Phone:V');
		$columnList['Contacts'][] = array(7,7,'vtiger_crmentity:smownerid:assigned_user_id:Contacts_Assigned_To:V');
		$columnList['Potentials'][] = array(10,0,'vtiger_potential:potentialname:potentialname:Potentials_Potential_Name:V');
		$columnList['Potentials'][] = array(10,1,'vtiger_potential:related_to:related_to:Potentials_Related_To:V');
		$columnList['Potentials'][] = array(10,3,'vtiger_potential:closingdate:closingdate:Potentials_Expected_Close_Date:D');
		$columnList['Potentials'][] = array(10,4,'vtiger_potential:leadsource:leadsource:Potentials_Lead_Source:V');
		$columnList['Potentials'][] = array(10,5,'vtiger_crmentity:smownerid:assigned_user_id:Potentials_Assigned_To:V');
		$columnList['Potentials'][] = array(10,6,'vtiger_potential:sales_stage:sales_stage:Potentials_Sales_Stage:V');
		$columnList['Potentials'][] = array(10,7,'vtiger_potential:contact_id:contact_id:Potentials_Contact_Name:V');
		$columnList['Potentials'][] = array(10,8,'vtiger_potential:potentialtype:opportunity_type:Potentials_Type:V');
		$columnList['HelpDesk'][] = array(13,1,'vtiger_troubletickets:title:ticket_title:HelpDesk_Title:V');
		$columnList['HelpDesk'][] = array(13,2,'vtiger_troubletickets:parent_id:parent_id:HelpDesk_Related_To:I');
		$columnList['HelpDesk'][] = array(13,3,'vtiger_troubletickets:status:ticketstatus:HelpDesk_Status:V');
		$columnList['HelpDesk'][] = array(13,4,'vtiger_troubletickets:priority:ticketpriorities:HelpDesk_Priority:V');
		$columnList['HelpDesk'][] = array(13,5,'vtiger_crmentity:smownerid:assigned_user_id:HelpDesk_Assigned_To:V');
		$columnList['HelpDesk'][] = array(13,6,'vtiger_troubletickets:contact_id:contact_id:HelpDesk_Contact_Name:V');
		$columnList['Quotes'][] = array(16,1,'vtiger_quotes:subject:subject:Quotes_Subject:V');
		$columnList['Quotes'][] = array(16,2,'vtiger_quotes:quotestage:quotestage:Quotes_Quote_Stage:V');
		$columnList['Quotes'][] = array(16,3,'vtiger_quotes:potentialid:potential_id:Quotes_Potential_Name:I');
		$columnList['Quotes'][] = array(16,4,'vtiger_quotes:accountid:account_id:Quotes_Account_Name:I');
		$columnList['Quotes'][] = array(16,5,'vtiger_quotes:total:hdnGrandTotal:Quotes_Total:I');
		$columnList['Quotes'][] = array(16,6,'vtiger_crmentity:smownerid:assigned_user_id:Quotes_Assigned_To:V');
		$columnList['Calendar'][] = array(19,0,'vtiger_activity:status:taskstatus:Calendar_Status:V');
		$columnList['Calendar'][] = array(19,1,'vtiger_activity:activitytype:activitytype:Calendar_Type:V');
		$columnList['Calendar'][] = array(19,2,'vtiger_activity:subject:subject:Calendar_Subject:V');
		$columnList['Calendar'][] = array(19,3,'vtiger_seactivityrel:crmid:parent_id:Calendar_Related_to:V');
		$columnList['Calendar'][] = array(19,4,'vtiger_activity:date_start:date_start:Calendar_Start_Date:D');
		$columnList['Calendar'][] = array(19,5,'vtiger_activity:due_date:due_date:Calendar_End_Date:D');
		$columnList['Calendar'][] = array(19,6,'vtiger_crmentity:smownerid:assigned_user_id:Calendar_Assigned_To:V');
		$columnList['Emails'][] = array(20,0,'vtiger_activity:subject:subject:Emails_Subject:V');
		$columnList['Emails'][] = array(20,1,'vtiger_emaildetails:to_email:saved_toid:Emails_To:V');
		$columnList['Emails'][] = array(20,2,'vtiger_activity:date_start:date_start:Emails_Date_Sent:D');
		$columnList['Invoice'][] = array(21,1,'vtiger_invoice:subject:subject:Invoice_Subject:V');
		$columnList['Invoice'][] = array(21,2,'vtiger_invoice:salesorderid:salesorder_id:Invoice_Sales_Order:I');
		$columnList['Invoice'][] = array(21,3,'vtiger_invoice:invoicestatus:invoicestatus:Invoice_Status:V');
		$columnList['Invoice'][] = array(21,4,'vtiger_invoice:total:hdnGrandTotal:Invoice_Total:I');
		$columnList['Invoice'][] = array(21,5,'vtiger_crmentity:smownerid:assigned_user_id:Invoice_Assigned_To:V');
		$columnList['Documents'][] = array(22,1,'vtiger_notes:title:notes_title:Notes_Title:V');
		$columnList['Documents'][] = array(22,2,'vtiger_notes:filename:filename:Notes_File:V');
		$columnList['Documents'][] = array(22,3,'vtiger_notes:folderid:folderid:Documents_Folder_Name:V');
		$columnList['Documents'][] = array(22,4,'vtiger_crmentity:smownerid:assigned_user_id:Notes_Assigned_To:V');
		$columnList['PriceBooks'][] = array(23,1,'vtiger_pricebook:bookname:bookname:PriceBooks_Price_Book_Name:V');
		$columnList['PriceBooks'][] = array(23,2,'vtiger_pricebook:active:active:PriceBooks_Active:V');
		$columnList['PriceBooks'][] = array(23,3,'vtiger_pricebook:currency_id:currency_id:PriceBooks_Currency:I');
		$columnList['Products'][] = array(24,0,'vtiger_products:productname:productname:Products_Product_Name:V');
		$columnList['Products'][] = array(24,1,'vtiger_products:pscategory:pscategory:Products_Product_Category:V');
		$columnList['Products'][] = array(24,3,'vtiger_products:unit_price:unit_price:Products_Unit_Price:N');
		$columnList['Products'][] = array(24,4,'vtiger_products:qtyinstock:qtyinstock:Products_Qty_In_Stock:NN');
		$columnList['Products'][] = array(24,5,'vtiger_products:discontinued:discontinued:Products_Product_Active:C');
		$columnList['Products'][] = array(24,6,'vtiger_products:sales_start_date:sales_start_date:Products_Sales_Start_Date:D');
		$columnList['PurchaseOrder'][] = array(25,1,'vtiger_purchaseorder:subject:subject:PurchaseOrder_Subject:V');
		$columnList['PurchaseOrder'][] = array(25,2,'vtiger_purchaseorder:vendorid:vendor_id:PurchaseOrder_Vendor_Name:I');
		$columnList['PurchaseOrder'][] = array(25,3,'vtiger_purchaseorder:tracking_no:tracking_no:PurchaseOrder_Tracking_Number:V');
		$columnList['PurchaseOrder'][] = array(25,4,'vtiger_purchaseorder:total:hdnGrandTotal:PurchaseOrder_Total:V');
		$columnList['PurchaseOrder'][] = array(25,5,'vtiger_crmentity:smownerid:assigned_user_id:PurchaseOrder_Assigned_To:V');
		$columnList['SalesOrder'][] = array(26,1,'vtiger_salesorder:subject:subject:SalesOrder_Subject:V');
		$columnList['SalesOrder'][] = array(26,2,'vtiger_salesorder:accountid:account_id:SalesOrder_Account_Name:I');
		$columnList['SalesOrder'][] = array(26,3,'vtiger_salesorder:quoteid:quote_id:SalesOrder_Quote_Name:I');
		$columnList['SalesOrder'][] = array(26,4,'vtiger_salesorder:total:hdnGrandTotal:SalesOrder_Total:V');
		$columnList['SalesOrder'][] = array(26,5,'vtiger_crmentity:smownerid:assigned_user_id:SalesOrder_Assigned_To:V');
		$columnList['Vendors'][] = array(27,1,'vtiger_vendor:vendorname:vendorname:Vendors_Vendor_Name:V');
		$columnList['Vendors'][] = array(27,2,'vtiger_vendor:phone:phone:Vendors_Phone:V');
		$columnList['Vendors'][] = array(27,3,'vtiger_vendor:email:email:Vendors_Email:E');
		$columnList['Vendors'][] = array(27,4,'vtiger_vendor:category:category:Vendors_Category:V');
		$columnList['Vendors'][] = array(27,5,'vtiger_crmentity:smownerid:assigned_user_id:Vendors_Assigned_To:V');
		$columnList['Faq'][] = array(28,1,'vtiger_faq:question:question:Faq_Question:V');
		$columnList['Faq'][] = array(28,2,'vtiger_faq:category:faqcategories:Faq_Category:V');
		$columnList['Faq'][] = array(28,3,'vtiger_faq:product_id:product_id:Faq_Product_Name:I');
		$columnList['Faq'][] = array(28,4,'vtiger_crmentity:createdtime:createdtime:Faq_Created_Time:DT');
		$columnList['Faq'][] = array(28,5,'vtiger_crmentity:modifiedtime:modifiedtime:Faq_Modified_Time:DT');
		$columnList['Campaigns'][] = array(29,1,'vtiger_campaign:campaignname:campaignname:Campaigns_Campaign_Name:V');
		$columnList['Campaigns'][] = array(29,2,'vtiger_campaign:campaigntype:campaigntype:Campaigns_Campaign_Type:N');
		$columnList['Campaigns'][] = array(29,3,'vtiger_campaign:campaignstatus:campaignstatus:Campaigns_Campaign_Status:N');
		$columnList['Campaigns'][] = array(29,4,'vtiger_campaign:expectedrevenue:expectedrevenue:Campaigns_Expected_Revenue:V');
		$columnList['Campaigns'][] = array(29,5,'vtiger_campaign:closingdate:closingdate:Campaigns_Expected_Close_Date:D');
		$columnList['Campaigns'][] = array(29,6,'vtiger_crmentity:smownerid:assigned_user_id:Campaigns_Assigned_To:V');
		$columnList['ServiceContracts'][] = array(39,1,'vtiger_servicecontracts:subject:subject:ServiceContracts_Subject:V');
		$columnList['ServiceContracts'][] = array(39,2,'vtiger_servicecontracts:sc_related_to:sc_related_to:ServiceContracts_Related_to:V');
		$columnList['ServiceContracts'][] = array(39,3,'vtiger_crmentity:smownerid:assigned_user_id:ServiceContracts_Assigned_To:V');
		$columnList['ServiceContracts'][] = array(39,4,'vtiger_servicecontracts:start_date:start_date:ServiceContracts_Start_Date:D');
		$columnList['ServiceContracts'][] = array(39,5,'vtiger_servicecontracts:due_date:due_date:ServiceContracts_Due_date:D');
		$columnList['ServiceContracts'][] = array(39,7,'vtiger_servicecontracts:progress:progress:ServiceContracts_Progress:N');
		$columnList['ServiceContracts'][] = array(39,8,'vtiger_servicecontracts:contract_status:contract_status:ServiceContracts_Status:V');
		$columnList['Services'][] = array(40,0,'vtiger_service:servicename:servicename:Services_Service_Name:V');
		$columnList['Services'][] = array(40,1,'vtiger_service:unit_price:unit_price:Services_Price:N');
		$columnList['Services'][] = array(40,2,'vtiger_service:sales_start_date:sales_start_date:Services_Sales_Start_Date:D');
		$columnList['Services'][] = array(40,3,'vtiger_service:sales_end_date:sales_end_date:Services_Sales_End_Date:D');
		$columnList['Services'][] = array(40,4,'vtiger_service:pscategory:pscategory:Services_Service_Category:V');
		$columnList['Services'][] = array(40,6,'vtiger_service:discontinued:discontinued:Services_Service_Active:V');
		$columnList['Assets'][] = array(41,0,'vtiger_assets:assetname:assetname:Assets_Asset_Name:V');
		$columnList['Assets'][] = array(41,1,'vtiger_assets:product:product:Assets_Product_Name:V');
		$columnList['Assets'][] = array(41,2,'vtiger_assets:parent_id:parent_id:Assets_Parent_ID:V');
		$columnList['Assets'][] = array(41,3,'vtiger_assets:dateinservice:dateinservice:Assets_Date_in_Service:D');
		$columnList['Assets'][] = array(41,4,'vtiger_assets:potential:potential:Assets_Potential:V');
		$columnList['Assets'][] = array(41,5,'vtiger_assets:potential renewal:Potential renewal:Assets_Potential_renewal:V');
		$columnList['Assets'][] = array(41,6,'vtiger_assets:assetstatus:assetstatus:Assets_Status:V');
		$columnList['ModComments'][] = array(42,0,'vtiger_modcomments:commentcontent:commentcontent:ModComments_Comment:V');
		$columnList['ModComments'][] = array(42,1,'vtiger_modcomments:related_to:related_to:ModComments_Related_To:V');
		$columnList['ModComments'][] = array(42,2,'vtiger_crmentity:modifiedtime:modifiedtime:ModComments_Modified_Time:DT');
		$columnList['ModComments'][] = array(42,3,'vtiger_crmentity:smownerid:assigned_user_id:ModComments_Assigned_To:V');
		$columnList['ProjectMilestone'][] = array(43,0,'vtiger_projectmilestone:projectmilestonename:projectmilestonename:ProjectMilestone_Project_Milestone_Name:V');
		$columnList['ProjectMilestone'][] = array(43,1,'vtiger_projectmilestone:projectmilestonedate:projectmilestonedate:ProjectMilestone_Milestone_Date:D');
		$columnList['ProjectMilestone'][] = array(43,3,'vtiger_crmentity:description:description:ProjectMilestone_description:V');
		$columnList['ProjectMilestone'][] = array(43,4,'vtiger_crmentity:createdtime:createdtime:ProjectMilestone_Created_Time:T');
		$columnList['ProjectMilestone'][] = array(43,5,'vtiger_crmentity:modifiedtime:modifiedtime:ProjectMilestone_Modified_Time:T');
		$columnList['ProjectTask'][] = array(44,2,'vtiger_projecttask:projecttaskname:projecttaskname:ProjectTask_Project_Task_Name:V');
		$columnList['ProjectTask'][] = array(44,3,'vtiger_projecttask:projectid:projectid:ProjectTask_Related_to:V');
		$columnList['ProjectTask'][] = array(44,4,'vtiger_projecttask:projecttaskpriority:projecttaskpriority:ProjectTask_Priority:V');
		$columnList['ProjectTask'][] = array(44,5,'vtiger_projecttask:projecttaskprogress:projecttaskprogress:ProjectTask_Progress:V');
		$columnList['ProjectTask'][] = array(44,6,'vtiger_projecttask:startdate:startdate:ProjectTask_Start_Date:D');
		$columnList['ProjectTask'][] = array(44,7,'vtiger_projecttask:enddate:enddate:ProjectTask_End_Date:D');
		$columnList['ProjectTask'][] = array(44,8,'vtiger_crmentity:smownerid:assigned_user_id:ProjectTask_Assigned_To:V');
		$columnList['Project'][] = array(45,0,'vtiger_project:projectname:projectname:Project_Project_Name:V');
		$columnList['Project'][] = array(45,1,'vtiger_project:linktoaccountscontacts:linktoaccountscontacts:Project_Related_to:V');
		$columnList['Project'][] = array(45,2,'vtiger_project:startdate:startdate:Project_Start_Date:D');
		$columnList['Project'][] = array(45,3,'vtiger_project:targetenddate:targetenddate:Project_Target_End_Date:D');
		$columnList['Project'][] = array(45,4,'vtiger_project:actualenddate:actualenddate:Project_Actual_End_Date:D');
		$columnList['Project'][] = array(45,5,'vtiger_project:targetbudget:targetbudget:Project_Target_Budget:V');
		$columnList['Project'][] = array(45,6,'vtiger_project:progress:progress:Project_Progress:V');
		$columnList['Project'][] = array(45,7,'vtiger_project:projectstatus:projectstatus:Project_Status:V');
		$columnList['Project'][] = array(45,8,'vtiger_crmentity:smownerid:assigned_user_id:Project_Assigned_To:V');
		$columnList['SMSNotifier'][] = array(46,0,'vtiger_smsnotifier:message:message:SMSNotifier_message:V');
		$columnList['SMSNotifier'][] = array(46,2,'vtiger_crmentity:smownerid:assigned_user_id:SMSNotifier_Assigned_To:V');
		$columnList['SMSNotifier'][] = array(46,3,'vtiger_crmentity:createdtime:createdtime:SMSNotifier_Created_Time:DT');
		$columnList['SMSNotifier'][] = array(46,4,'vtiger_crmentity:modifiedtime:modifiedtime:SMSNotifier_Modified_Time:DT');
		$columnList['PBXManager'][] = array(57,0,'vtiger_pbxmanager:callstatus:callstatus:PBXManager_Call_Status:V');
		$columnList['PBXManager'][] = array(57,1,'vtiger_pbxmanager:customernumber:customernumber:PBXManager_Customer_Number:V');
		$columnList['PBXManager'][] = array(57,2,'vtiger_pbxmanager:customer:customer:PBXManager_Customer:V');
		$columnList['PBXManager'][] = array(57,3,'vtiger_pbxmanager:user:user:PBXManager_User:V');
		$columnList['PBXManager'][] = array(57,4,'vtiger_pbxmanager:recordingurl:recordingurl:PBXManager_Recording_URL:V');
		$columnList['PBXManager'][] = array(57,5,'vtiger_pbxmanager:totalduration:totalduration:PBXManager_Total_Duration:V');
		$columnList['PBXManager'][] = array(57,6,'vtiger_pbxmanager:starttime:starttime:PBXManager_Start_Time:DT');
		
		$modules = array('Leads','Accounts','Contacts','Potentials','HelpDesk','Quotes','Calendar','Emails','Invoice','Documents','PriceBooks','Products','PurchaseOrder','SalesOrder','Vendors','Faq','Campaigns','ServiceContracts','Services','Assets','ModComments','ProjectMilestone','ProjectTask','Project','SMSNotifier','PBXManager');

		foreach($modules as $module){
			$sql = "SELECT `cvid` FROM `vtiger_customview` WHERE entitytype = ?;";
			$result = $adb->pquery( $sql, array($module) );
			$num = $adb->num_rows( $result );
			if($num == 1){
				$cvId = $adb->query_result( $result, 0, 'cvid' );
				$adb->pquery("DELETE FROM `vtiger_cvcolumnlist` WHERE cvid = ? ", array($cvId));
				foreach($columnList[$module] as $cvc){
					$adb->pquery("INSERT INTO vtiger_cvcolumnlist (cvid,columnindex,columnname) VALUES (?,?,?)", array($cvId, $cvc[1], $cvc[2]));
				}
			}
		}
		$log->debug("Exiting VT610_to_YT::customView() method ...");
	}
	
	public function addSearchfield(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::addSearchfield() method ...");
		$entityName[] = array(2,'Potentials','vtiger_potential','potentialname','potentialid','potential_id','potentialname',1,0);
		$entityName[] = array(4,'Contacts','vtiger_contactdetails','firstname,lastname','contactid','contact_id','firstname,lastname',1,0);
		$entityName[] = array(6,'Accounts','vtiger_account','accountname','accountid','account_id','accountname',1,0);
		$entityName[] = array(7,'Leads','vtiger_leaddetails','lastname','leadid','leadid','lastname',1,0);
		$entityName[] = array(8,'Documents','vtiger_notes','title','notesid','notesid','title',1,0);
		$entityName[] = array(9,'Calendar','vtiger_activity','subject','activityid','activityid','subject',1,0);
		$entityName[] = array(10,'Emails','vtiger_activity','subject','activityid','activityid','subject',1,0);
		$entityName[] = array(13,'HelpDesk','vtiger_troubletickets','title','ticketid','ticketid','title',1,0);
		$entityName[] = array(14,'Products','vtiger_products','productname','productid','product_id','productname',1,0);
		$entityName[] = array(15,'Faq','vtiger_faq','question','id','id','question',1,0);
		$entityName[] = array(16,'Events','vtiger_activity','subject','activityid','activityid','subject',1,0);
		$entityName[] = array(18,'Vendors','vtiger_vendor','vendorname','vendorid','vendor_id','vendorname',1,0);
		$entityName[] = array(19,'PriceBooks','vtiger_pricebook','bookname','pricebookid','pricebookid','bookname',1,0);
		$entityName[] = array(20,'Quotes','vtiger_quotes','subject','quoteid','quote_id','subject',1,0);
		$entityName[] = array(21,'PurchaseOrder','vtiger_purchaseorder','subject','purchaseorderid','purchaseorderid','subject',1,0);
		$entityName[] = array(22,'SalesOrder','vtiger_salesorder','subject','salesorderid','salesorder_id','subject',1,0);
		$entityName[] = array(23,'Invoice','vtiger_invoice','subject','invoiceid','invoiceid','subject',1,0);
		$entityName[] = array(26,'Campaigns','vtiger_campaign','campaignname','campaignid','campaignid','campaignname',1,0);
		$entityName[] = array(29,'Users','vtiger_users','last_name,first_name','id','id','last_name,first_name',1,0);
		$entityName[] = array(33,'PBXManager','vtiger_pbxmanager','customernumber','pbxmanagerid','pbxmanagerid','customernumber',1,0);
		$entityName[] = array(34,'ServiceContracts','vtiger_servicecontracts','subject','servicecontractsid','servicecontractsid','subject',1,0);
		$entityName[] = array(35,'Services','vtiger_service','servicename','serviceid','serviceid','servicename',1,0);
		$entityName[] = array(37,'Assets','vtiger_assets','assetname','assetsid','assetsid','assetname',1,0);
		$entityName[] = array(40,'ModComments','vtiger_modcomments','commentcontent','modcommentsid','modcommentsid','commentcontent',1,0);
		$entityName[] = array(41,'ProjectMilestone','vtiger_projectmilestone','projectmilestonename','projectmilestoneid','projectmilestoneid','projectmilestonename',1,0);
		$entityName[] = array(42,'ProjectTask','vtiger_projecttask','projecttaskname','projecttaskid','projecttaskid','projecttaskname',1,0);
		$entityName[] = array(43,'Project','vtiger_project','projectname','projectid','projectid','projectname',1,0);
		$entityName[] = array(45,'SMSNotifier','vtiger_smsnotifier','message','smsnotifierid','smsnotifierid','message',1,0);
		$entityName[] = array(47,'OSSPdf','vtiger_osspdf','title','osspdfid','osspdfid','title',1,0);
		$entityName[] = array(49,'OSSMailTemplates','vtiger_ossmailtemplates','name','ossmailtemplatesid','ossmailtemplatesid','name',1,0);
		$entityName[] = array(51,'OSSTimeControl','vtiger_osstimecontrol','name','osstimecontrolid','osstimecontrolid','name',1,0);
		$entityName[] = array(54,'OSSMailView','vtiger_ossmailview','subject','ossmailviewid','ossmailviewid','subject',1,0);
		$entityName[] = array(57,'OSSOutsourcedServices','vtiger_ossoutsourcedservices','productname','ossoutsourcedservicesid','ossoutsourcedservicesid','productname',1,0);
		$entityName[] = array(58,'OSSSoldServices','vtiger_osssoldservices','productname','osssoldservicesid','osssoldservicesid','productname',1,0);
		$entityName[] = array(59,'OutsourcedProducts','vtiger_outsourcedproducts','productname','outsourcedproductsid','outsourcedproductsid','productname',1,0);
		$entityName[] = array(60,'OSSPasswords','vtiger_osspasswords','passwordname','osspasswordsid','osspasswordsid','passwordname',1,0);
		$entityName[] = array(61,'OSSEmployees','vtiger_ossemployees','last_name','ossemployeesid','ossemployeesid','last_name',1,0);
		$entityName[] = array(70,'Calculations','vtiger_calculations','name','calculationsid','calculationsid','calculations_no,name',1,0);
		$entityName[] = array(71,'OSSCosts','vtiger_osscosts','name','osscostsid','osscostsid','name',1,0);
		$entityName[] = array(74,'CallHistory','vtiger_callhistory','to_number','callhistoryid','callhistoryid','to_number',1,0);
		$entityName[] = array(75,'Ideas','vtiger_ideas','subject','ideasid','ideasid','subject',1,0);
		
		foreach($entityName as $name){
			$adb->pquery('UPDATE `vtiger_entityname` SET `searchcolumn` = ? WHERE `modulename` = ?;', array($name[6], $name[1]), true);
		}
		
		$log->debug("Exiting VT610_to_YT::addSearchfield() method ...");
	}
	
	public function addWidget(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::addWidget() method ...");
		$widgets[] = array(1,'Accounts','Summary',NULL,1,0,NULL,'[]');
		$widgets[] = array(2,'Accounts','Comments','ModComments',2,6,NULL,'{"relatedmodule":"ModComments","limit":"5"}');
		$widgets[] = array(3,'Accounts','Updates','LBL_UPDATES',1,2,NULL,'[]');
		$widgets[] = array(4,'Accounts','Activities','Calendar',2,4,NULL,'{"limit":"5"}');
		$widgets[] = array(5,'Accounts','RelatedModule','Contacts',2,3,NULL,'{"limit":"5","relatedmodule":"'.getTabid('Contacts').'","columns":"3","action":"1","filter":"-"}');
		$widgets[] = array(6,'Accounts','RelatedModule','Potentials',1,1,NULL,'{"limit":"5","relatedmodule":"'.getTabid('Potentials').'","columns":"3","action":"1","filter":"-"}');
		$widgets[] = array(7,'Leads','Summary',NULL,1,1,NULL,'[]');
		$widgets[] = array(8,'Leads','Comments','ModComments',1,2,1,'{"relatedmodule":"ModComments","limit":"5"}');
		$widgets[] = array(9,'Leads','Updates','LBL_UPDATES',2,6,NULL,'[]');
		$widgets[] = array(10,'Leads','Activities','Calendar',2,4,NULL,'{"limit":"5"}');
		$widgets[] = array(11,'Leads','EmailList','Emails',2,5,NULL,'{"relatedmodule":"Emails","limit":"5"}');
		$widgets[] = array(12,'Accounts','EmailList','Emails',2,5,NULL,'{"relatedmodule":"Emails","limit":"5"}');
		$widgets[] = array(14,'Contacts','Summary',NULL,1,1,NULL,'[]');
		$widgets[] = array(15,'Contacts','Comments','ModComments',2,2,NULL,'{"relatedmodule":"ModComments","limit":"5"}');
		$widgets[] = array(16,'Contacts','Updates','LBL_UPDATES',1,3,NULL,'[]');
		$widgets[] = array(17,'Contacts','Activities','Calendar',2,4,NULL,'{"limit":"5"}');
		$widgets[] = array(18,'Contacts','EmailList','Emails',2,5,NULL,'{"relatedmodule":"Emails","limit":"5"}');
		$widgets[] = array(19,'Potentials','Summary',NULL,1,0,NULL,'[]');
		$widgets[] = array(20,'Potentials','Comments','ModComments',2,4,NULL,'{"relatedmodule":"ModComments","limit":"5"}');
		$widgets[] = array(21,'Potentials','EmailList','Emails',1,3,NULL,'{"relatedmodule":"Emails","limit":"5"}');
		$widgets[] = array(22,'Potentials','Activities','Calendar',2,1,NULL,'{"limit":"5"}');
		$widgets[] = array(23,'Potentials','RelatedModule','Contacts',2,6,NULL,'{"limit":"5","relatedmodule":"'.getTabid('Contacts').'","columns":"3","filter":"-"}');
		$widgets[] = array(24,'Potentials','RelatedModule','Documents',2,2,NULL,'{"limit":"5","relatedmodule":"'.getTabid('Documents').'","columns":"3","action":"1","filter":"-"}');
		$widgets[] = array(25,'Potentials','Updates','LBL_UPDATES',2,5,NULL,'[]');
		$widgets[] = array(26,'Project','Summary',NULL,1,1,NULL,'[]');
		$widgets[] = array(27,'Project','Comments','ModComments',2,2,NULL,'{"relatedmodule":"ModComments","limit":"5"}');
		$widgets[] = array(28,'Project','Updates','LBL_UPDATES',1,3,NULL,'[]');
		$widgets[] = array(30,'Project','EmailList','Emails',1,5,NULL,'{"relatedmodule":"Emails","limit":"5"}');
		$widgets[] = array(31,'Project','RelatedModule','ProjectTask',2,6,NULL,'{"limit":"5","relatedmodule":"'.getTabid('ProjectTask').'","columns":"3","action":"1","filter":"vtiger_projecttask::projecttaskstatus::projecttaskstatus"}');
		$widgets[] = array(32,'Project','RelatedModule','ProjectMilestone',2,7,NULL,'{"limit":"5","relatedmodule":"'.getTabid('ProjectMilestone').'","columns":"3","action":"1","filter":"vtiger_projectmilestone::projectmilestonetype::projectmilestonetype"}');
		$widgets[] = array(33,'Project','RelatedModule','HelpDesk',2,8,NULL,'{"limit":"5","relatedmodule":"'.getTabid('HelpDesk').'","columns":"3","action":"1","filter":"-"}');
		$widgets[] = array(34,'HelpDesk','Summary',NULL,1,1,NULL,'[]');
		$widgets[] = array(35,'HelpDesk','Comments','ModComments',1,2,NULL,'{"relatedmodule":"ModComments","limit":"5"}');
		$widgets[] = array(36,'HelpDesk','Updates','LBL_UPDATES',1,3,NULL,'[]');
		$widgets[] = array(37,'HelpDesk','EmailList','Emails',2,4,NULL,'{"relatedmodule":"Emails","limit":"5"}');
		$widgets[] = array(38,'HelpDesk','Activities','Calendar',2,5,NULL,'{"limit":"5"}');
		$widgets[] = array(39,'HelpDesk','RelatedModule','Documents',2,6,NULL,'{"limit":"5","relatedmodule":"'.getTabid('Documents').'","columns":"3","action":"1","filter":"-"}');
		$widgets[] = array(40,'OSSTimeControl','Summary',NULL,1,1,NULL,'[]');
		$widgets[] = array(41,'OSSTimeControl','Comments','ModComments',2,2,NULL,'{"relatedmodule":"ModComments","limit":"5"}');
		$widgets[] = array(42,'OSSTimeControl','RelatedModule','Documents',2,3,NULL,'{"limit":"5","relatedmodule":"'.getTabid('Documents').'","columns":"3","filter":"-"}');
		$widgets[] = array(43,'Leads','RelatedModule','Contacts',2,3,NULL,'{"limit":"5","relatedmodule":"'.getTabid('Contacts').'","columns":"3","action":"1","filter":"-"}');
		$widgets[] = array(47,'HelpDesk','WYSIWYG','WYSIWYG',1,7,NULL,'{"field_name":"description"}');
		$widgets[] = array(48,'OSSMailView','PreviewMail',NULL,1,1,NULL,'{"relatedmodule":"Emails"}');
		$widgets[] = array(49,'ProjectTask','Summary',NULL,1,0,NULL,'[]');
		$widgets[] = array(50,'ProjectTask','Comments','ModComments',2,1,NULL,'{"relatedmodule":"ModComments","limit":"5"}');
		
		foreach($widgets as $widget){
			if(self::checkModuleExists($widget[1])){
				$sql = "INSERT INTO vtiger_widgets (tabid, type, label, wcol, sequence, nomargin, data) VALUES (?, ?, ?, ?, ?, ?, ?);";
				$adb->pquery($sql, array( getTabid($widget[1]), $widget[2], $widget[3], $widget[4], $widget[5], $widget[6], $widget[7]));
			}
		}
		$log->debug("Exiting VT610_to_YT::addWidget() method ...");
	}
	
	public function relatedList(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::relatedList() method ...");
		
		$targetModule = Vtiger_Module::getInstance('Contacts');
		$moduleInstance = Vtiger_Module::getInstance('CallHistory');
		$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		$targetModule = Vtiger_Module::getInstance('Accounts');
		$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		$targetModule = Vtiger_Module::getInstance('Leads');
		$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		$targetModule = Vtiger_Module::getInstance('Vendors');
		$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		//$targetModule = Vtiger_Module::getInstance('OSSEmployees');
		//$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		$targetModule = Vtiger_Module::getInstance('Potentials');
		$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		$targetModule = Vtiger_Module::getInstance('HelpDesk');
		$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		
		$addRelations = array();
		$addRelations['Potentials'][] = array('related_tabid'=>'Assets', 'label'=>'Assets', 'actions'=>'ADD', 'name'=>'get_dependents_list');
		$addRelations['Potentials'][] = array('related_tabid'=>'Calculations', 'label'=>'Calculations', 'actions'=>'ADD', 'name'=>'get_dependents_list');
		$addRelations['Contacts'][] = array('related_tabid'=>'Calculations', 'label'=>'Calculations', 'actions'=>'ADD', 'name'=>'get_dependents_list');
		$addRelations['Accounts'][] = array('related_tabid'=>'Calculations', 'label'=>'Calculations', 'actions'=>'ADD', 'name'=>'get_dependents_list');
		$addRelations['Quotes'][] = array('related_tabid'=>'Calculations', 'label'=>'Calculations', 'actions'=>'ADD', 'name'=>'get_dependents_list');
		$addRelations['Leads'][] = array('related_tabid'=>'Contacts', 'label'=>'Contacts', 'actions'=>'ADD', 'name'=>'get_dependents_list');
		$addRelations['ServiceContracts'][] = array('related_tabid'=>'Calendar', 'label'=>'Activities', 'actions'=>'ADD', 'name'=>'get_activities');
		$addRelations['Project'][] = array('related_tabid'=>'Calendar', 'label'=>'Activities', 'actions'=>'ADD', 'name'=>'get_activities');
		$addRelations['HelpDesk'][] = array('related_tabid'=>'Assets', 'label'=>'Assets', 'actions'=>'ADD,SELECT', 'name'=>'get_dependents_list');
		foreach($addRelations as $moduleName=>$relations){
			$moduleInstance = Vtiger_Module::getInstance($moduleName);
			foreach($relations as $relation){
				$relatedInstance = Vtiger_Module::getInstance($relation['related_tabid']);
				$moduleInstance->setRelatedList($relatedInstance,$relation['label'],$relation['actions'],$relation['name']);
			}
		}
		
		$update[] = array('select'=>array('tabid'=>getTabid('Accounts'), 'related_tabid'=>getTabid('OSSOutsourcedServices')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Accounts'), 'related_tabid'=>getTabid('OSSSoldServices')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Accounts'), 'related_tabid'=>getTabid('OutsourcedProducts')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Accounts'), 'related_tabid'=>getTabid('Services')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Accounts'), 'related_tabid'=>getTabid('Products')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Accounts'), 'related_tabid'=>getTabid('Assets')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Leads'), 'related_tabid'=>getTabid('Products')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Leads'), 'related_tabid'=>getTabid('OutsourcedProducts')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Potentials'), 'related_tabid'=>getTabid('Products')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Potentials'), 'related_tabid'=>getTabid('Services')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Potentials'), 'related_tabid'=>getTabid('Assets')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Potentials'), 'related_tabid'=>getTabid('OSSOutsourcedServices')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Potentials'), 'related_tabid'=>getTabid('OSSSoldServices')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('Potentials'), 'related_tabid'=>getTabid('OutsourcedProducts')),'change'=>array('presence'=>1));
		$update[] = array('select'=>array('tabid'=>getTabid('ServiceContracts'), 'related_tabid'=>getTabid('HelpDesk')),'change'=>array('label'=>'HelpDesk'));
		foreach($update as $presents){
			$sql = 'UPDATE `vtiger_relatedlists` SET ';
			foreach($presents['change'] as $column=>$value)
				$sql .= $column.' = ? ';
			$sql .= 'WHERE `tabid` = ? AND `related_tabid` = ? ;';
			$adb->pquery($sql, array(1,$presents['tabid'],$presents['related_tabid']), true);
		}
		$log->debug("Exiting VT610_to_YT::relatedList() method ...");
	}
	
	public function transferLogo(){
		global $log,$adb,$root_directory;
		$log->debug("Entering VT610_to_YT::transferLogo() method ...");
		$result = $adb->query( "SELECT `logoname` FROM `vtiger_organizationdetails` ;");
		$num = $adb->num_rows( $result );
		if($num == 1){
			$logoName = $adb->query_result( $result, 0, 'logoname' );
			$source = $this->source;
			if(!$root_directory)
				$root_directory = getcwd();
			copy($source.'storage/Logo/'.$logoName, $root_directory.'/storage/Logo/'.$logoName);
		}
		$log->debug("Exiting VT610_to_YT::transferLogo() method ...");
	}
	/////////////
	public function changeFieldOnTree(){
		global $log,$adb;
			$log->debug("Entering VT610_to_YT::changeFieldOnTree() method ...");
		$tab = array('vtiger_products'=>'pscategory',
					'vtiger_service'=>'pscategory',	
					'vtiger_ossoutsourcedservices'=>'pscategory',	
					'vtiger_osssoldservices'=>'pscategory',	
					'vtiger_outsourcedproducts'=>'pscategory'	
					);
		$templateNames = array('pscategory'=>'Category');
		foreach($tab as $tablename=>$columnname){
			$result = $adb->pquery("SELECT * FROM `vtiger_field` WHERE `columnname` = ? AND `tablename` = ?;", array($columnname, $tablename));
			if($adb->num_rows($result) == 1){
				$fieldparams = $adb->query_result_raw($result, 0, 'fieldparams');
				$moduleId = $adb->query_result_raw($result, 0, 'tabid');
				if(!$fieldparams){
					$stem = array("Hardware","Software","CRM Applications","Antivirus","Backup");
					$k=1;
					$tree = array();
					foreach($stem AS $storey){
						$tree[] = array('data'=>$storey, 'attr'=>array('id'=>$k));
						$k = $k+1;
					}
					$templateId = self::createTree($moduleId, $templateNames[$columnname], $tree);
					if($templateId){
						self::updateRecordsTree($tablename, $columnname, $tree);
						$adb->pquery("UPDATE `vtiger_field` SET `fieldparams` = ? WHERE `columnname` = ? AND `tablename` = ?;", array($templateId, $columnname, $tablename));
					}
				}
			}
		}
	}
	public function updateRecordsTree($tablename, $columnName, $tree ){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::updateRecordsTree(".$tablename.", ".$columnName.", ".$tree.") method ...");
		foreach($tree AS $treeElement){
			$query = 'UPDATE '.$tablename.' SET '.$columnName.' = ? WHERE '.$columnName.' = ?';
			$params = array('T'.$treeElement['attr']['id'], $treeElement['data']);
			$adb->pquery($query, $params);
			if($treeElement['children']){
				$this->updateRecordsTree( $tablename, $columnName, $treeElement['children']);
			}
		}
		$log->debug("Exiting VT610_to_YT::updateRecordsTree() method ...");
	}
	public function createTree($moduleId, $nameTemplate, $tree){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::createTree(".$moduleId.", ".$nameTemplate.", ".$tree.") method ...");
		vimport('~~modules/Settings/TreesManager/models/Record.php');
		
		$sql = 'INSERT INTO vtiger_trees_templates(`name`, `module`, `access`) VALUES (?,?,?)';
		$params = array($nameTemplate, $moduleId, 0);
		$adb->pquery($sql, $params);
		$templateId = $adb->getLastInsertID();
		
		$recordModel = new Settings_TreesManager_Record_Model();
		$recordModel->set('name', $nameTemplate);
		$recordModel->set('module', $moduleId);
		$recordModel->set('tree', $tree);
		$recordModel->set('templateid', $templateId);
		$recordModel->save();

		$log->debug("Exiting VT610_to_YT::createTree() method ...");
		return $templateId;
	}
	////////////
	public function removeModules(){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT::removeModules() method ...");
		$removeModules = array('EmailTemplates'=>array(),'Webmails'=>array(),'FieldFormulas'=>array(),
		'Google'=>array('added_links'=>array(array('type' => 'DETAILVIEWSIDEBARWIDGET', 'label'  => 'Google Map'),array('type' => 'LISTVIEWSIDEBARWIDGET', 'label'  => 'Google Contacts'),array('type' => 'LISTVIEWSIDEBARWIDGET', 'label'  => 'Google Calendar'))),
		'ExtensionStore'=>array()
		);
		foreach($removeModules as $moduleName=>$removeModule){
			if(!self::checkModuleExists($moduleName))
				continue;
			$moduleInstance = Vtiger_Module::getInstance($moduleName);
			$moduleInstance->delete();
			$obiekt = new RemoveModule( $moduleName );
			if($removeModule['added_links'])
				$obiekt->added_links = $removeModule['added_links'];
			$obiekt->DeleteAll();
		}
		$log->debug("Exiting VT610_to_YT::removeModules() method ...");
	}
}
class RemoveModule {

	var $module_name;
	var $tabid;
	var $cvid;
	var $table_list = array( );
	var $added_links = array();
	var $added_fields = array();
	var $added_handlers = array();
	// supported languages
	var $supported_languages = array();
	// setting links in "crm settings"
	var $settings_links = array();

	function __construct( $module_name ) 
	{
		global $adb;
		
		$this->module_name = $module_name;
		$take_tabid = $adb->query( "select tabid from vtiger_tab where name = '".$this->module_name."'", true, "Błąd podczas pobierania tabid w konstruktorze klasy RemoveModule - vtiger_tab" );
		if( $adb->num_rows( $take_tabid ) > 0 )
		{
			$this->tabid = $adb->query_result( $take_tabid,0,"tabid" );
		}
		$take_cvid = $adb->query( "select cvid from vtiger_customview where entitytype = '".$this->module_name."'", true, "Błąd podczas pobierania tabid w konstruktorze klasy RemoveModule - vtiger_customview" );
		if( $adb->num_rows( $take_cvid ) > 0 )
		{
			$this->cvid = $take_cvid->GetArray();
			
		}

		$take_info = $adb->query( "select * from vtiger_field where tabid = '$field_tabid' and uitype = '15'" , true, "Błąd podczas pobierania pól w funkcji uitype 15" );
		if( $adb->num_rows( $take_info ) > 0 )
		{
			$_SESSION['picklist_tables'][$adb->query_result( $take_info,0,"fieldname" )] = $adb->query_result( $take_info,0,"fieldname" );
		}
	}
	
	function DeleteAll()
	{
		$this->DeleteHandlers();
		$this->DeleteAddedFields();
		$this->DeleteALLFields();
		$this->DeleteLinks();
		$this->DeletePicklistsTables();
		$this->DeleteFromProfile2Field();
		$this->DeleteFromModentitynum();
		$this->DeleteDefOrgInformations();
		$this->DeleteTables();
		$this->DeleteFromCronTask();
		$this->DeleteFromCRMEntity();
		$this->DeleteBlocks();
		$this->DeleteCustomview();
		$this->DeleteEntityname();
		$this->DeleteParenttabrel();
		//$this->DeleteTab();
		$this->DeleteWsEntity();
		//$this->DeleteDir( 'modules/'.$this->module_name );
		//$this->DeleteDir( 'modules/Settings/'.$this->module_name );
		//$this->DeleteDir( 'layouts/vlayout/modules/'.$this->module_name );
        //$this->DeleteLanguageFiles();
        $this->DeleteRelatedLists();
        $this->DeleteSettingsField();
		$this->DeleteWorkflows();
		$this->DeleteCrmentityrel();
        $this->DeleteIcon();
	}
	
	function DeleteAddedFields()
	{
	global $adb;
	include_once('vtlib/Vtiger/Module.php');
		if( count( $this->added_fields ) > 0 )
		{
			foreach( $this->added_fields as $single_field )
			{
			$take_tabid = $adb->query( "select tabid from vtiger_tab where name = '".$single_field['module']."'", true, "Błąd podczas pobierania tabid w funkcji DeleteAddedFields()" );
				if( $adb->num_rows( $take_tabid ) > 0 )
				{
					$field_tabid = $adb->query_result( $take_tabid, 0, "tabid" );
					
					$take_info = $adb->query( "select * from vtiger_field where tabid = '$field_tabid' and fieldname = '".$single_field['fieldname']."'" , true, "Błąd podczas pobierania pól w funkcji DeleteAddedFields()" );
					if( $adb->num_rows( $take_info ) > 0 )
					{
					$valuemap = array();
						$moduleInstance = Vtiger_Module::getInstance( $single_field['module'] );
						$valuemap['fieldid'] = $adb->query_result( $take_info, 0, 'fieldid');
						$valuemap['fieldname']=$adb->query_result( $take_info, 0, 'fieldname'); 
						$valuemap['fieldlabel'] = $adb->query_result( $take_info, 0, 'fieldlabel');
						$valuemap['columnname'] =$adb->query_result( $take_info, 0, 'columnname');
						$valuemap['tablename']  =$adb->query_result( $take_info, 0, 'tablename'); 
						$valuemap['uitype'] = $adb->query_result( $take_info, 0, 'uitype');
						$valuemap['typeofdata'] = $adb->query_result( $take_info, 0, 'typeofdata'); 
						$valuemap['block'] = $adb->query_result( $take_info, 0, "block" );
						$fieldInstance = new Vtiger_Field();
						$fieldInstance->initialize( $valuemap, $moduleInstance );
						$fieldInstance->delete();
					}
				}
			}
		}
		
		$delete = $adb->query( "delete from vtiger_fieldmodulerel where module = '".$this->module_name."' or relmodule = '".$this->module_name."' ", true, "Błąd podczas usuwania rekordów z tabeli vtiger_fieldmodulerel w funkcji DeleteAddedFields()");
	}
	function DeleteALLFields()
	{
	global $adb;
	include_once('vtlib/Vtiger/Module.php');

			$take_info = $adb->query( "select * from vtiger_field where tabid = '".$this->tabid."'" , true, "Błąd podczas pobierania pól w funkcji DeleteAddedFields()" );
	
			foreach( $take_info as $single_field )
			{
				$take_info = $adb->query( "select * from vtiger_field where tabid = '".$single_field['tabid']."' and fieldname = '".$single_field['fieldname']."'" , true, "Błąd podczas pobierania pól w funkcji DeleteAddedFields()" );
				if( $adb->num_rows( $take_info ) > 0 )
				{
				$valuemap = array();
					$moduleInstance = Vtiger_Module::getInstance( $single_field['module'] );
					$valuemap['fieldid'] = $adb->query_result( $take_info, 0, 'fieldid');
					$valuemap['fieldname']=$adb->query_result( $take_info, 0, 'fieldname'); 
					$valuemap['fieldlabel'] = $adb->query_result( $take_info, 0, 'fieldlabel');
					$valuemap['columnname'] =$adb->query_result( $take_info, 0, 'columnname');
					$valuemap['tablename']  =$adb->query_result( $take_info, 0, 'tablename'); 
					$valuemap['uitype'] = $adb->query_result( $take_info, 0, 'uitype');
					$valuemap['typeofdata'] = $adb->query_result( $take_info, 0, 'typeofdata'); 
					$valuemap['block'] = $adb->query_result( $take_info, 0, "block" );
					$fieldInstance = new Vtiger_Field();
					$fieldInstance->initialize( $valuemap, $moduleInstance );
					$fieldInstance->delete();
				}
			}

		
		$delete = $adb->query( "delete from vtiger_fieldmodulerel where module = '".$this->module_name."' or relmodule = '".$this->module_name."' ", true, "Błąd podczas usuwania rekordów z tabeli vtiger_fieldmodulerel w funkcji DeleteAddedFields()");
	}
	
	function DeleteLinks()
	{
		global $adb;
		if( count( $this->added_links ) > 0 )
		{
			foreach( $this->added_links as $single_link )
			{
				$drop_table_query = $adb->query( "DELETE FROM vtiger_links WHERE linktype='".$single_link['type']."' AND linklabel='".$single_link['label']."'", true, "Błąd podczas usuwania linków w funkcji DeleteLinks()" );
			}
		}
		$adb->query( "DELETE FROM vtiger_links WHERE linkurl like '%module=".$this->module_name."%'", true, "Błąd podczas usuwania linków w funkcji DeleteLinks()" );
	}
	
	function DeleteHandlers()
	{
		global $adb;
		require_once( 'include/events/include.inc' );
		if( count( $this->added_handlers ) > 0 )
		{
			foreach( $this->added_handlers as $handler )
			{
				$delete = $adb->query("delete from vtiger_eventhandlers where handler_class='".$handler['class']."' and event_name = '".$handler['type']."'", true, "Błąd podczas usuwania rekordów z tabeli vtiger_eventhandlers w funkcji DeleteHandlers()");
				$delete = $adb->query("delete from vtiger_eventhandler_module where handler_class='".$handler['class']."'", true, "Błąd podczas usuwania rekordów z tabeli vtiger_eventhandler_module w funkcji DeleteHandlers()");
			}
		}
	}
	
	function DeleteTables()
	{
		global $adb;
		if( count( $this->table_list ) > 0 )
		{
			foreach( $this->table_list as $tablename )
			{
				$drop_table_query = $adb->query( "drop table if exists $tablename", true, "Błąd podczas usuwania tabeli modułu w funkcji DeleteTables()" );
			}
		}
	}
	function DeleteEntityname()
	{
		global $adb;
		$delete = $adb->query( "delete from vtiger_entityname where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_entityname w funkcji DeleteEntityname()" );
	}
	function DeleteBlocks()
	{
		global $adb;
		$delete = $adb->query( "delete from vtiger_blocks where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_blocks w funkcji DeleteBlocks()" );
	}
	function DeleteCustomview()
	{
		if(count($this->cvid) >0){
		foreach( $this->cvid as $cvid ){
			$customViewModel = CustomView_Record_Model::getInstanceById($cvid[0]);
			$customViewModel->delete();
		}
		}
	}
	function DeletePicklistsTables()
	{
		global $adb;
		
		
		
		if( isset( $_SESSION['picklist_tables'] ) && count( $_SESSION['picklist_tables'] ) > 0 )
		{
			foreach( $_SESSION['picklist_tables'] as $picklist_name )
			{
				$drop_table_query = $adb->query( "drop table if exists vtiger_".$picklist_name, true, "Błąd podczas usuwania tabeli modułu w funkcji DeletePicklistsTables()" );
				$drop_table_query = $adb->query( "drop table if exists vtiger_".$picklist_name."_seq", true, "Błąd podczas usuwania tabeli modułu w funkcji DeletePicklistsTables()" );
				$select = $adb->query( "select picklistid from vtiger_picklist where name = '$picklist_name'", true, "Błąd podczas pobierania rekordów z tabeli vtiger_picklist w funkcji DeletePicklistsTables()" );
				$picklistid = $adb->query_result( $select,0,"picklistid" );
				$delete_from = $adb->query( "delete from vtiger_role2picklist where picklistid = '$picklistid'", true, "Błąd podczas usuwania rekordów w funkcji DeletePicklistsTables()" );
				$delete_from = $adb->query( "delete from vtiger_picklist where name = '$picklist_name'", true, "Błąd podczas usuwania rekordów w funkcji DeletePicklistsTables()" );
			}
		}
	}
	function DeleteFromProfile2Field()
	{
		global $adb;
		$delete = $adb->query( "delete from vtiger_profile2field where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_profile2field w funkcji DeleteFromProfile2Field()" );
		$delete = $adb->query( "delete from vtiger_profile2standardpermissions where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_profile2standardpermissions w funkcji DeleteFromProfile2Field()" );
		$delete = $adb->query( "delete from vtiger_profile2tab where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_profile2tab w funkcji DeleteFromProfile2Field()" );
	}
	
	function DeleteParenttabrel()
	{
		global $adb; 
		$delete = $adb->query( "delete from vtiger_parenttabrel where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_parenttabrel w funkcji DeleteParenttabrel()" );
	}
	function DeleteFromModentitynum()
	{
		global $adb; 
		$delete = $adb->query( "delete from vtiger_modentity_num where semodule = '".$this->module_name."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_profile2field w funkcji DeleteFromModentitynum()" );
	}
	function DeleteDefOrgInformations()
	{
		global $adb;
		
		$delete = $adb->query( "delete from vtiger_def_org_field where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_def_org_field w funkcji DeleteDefOrgInformations()" );
		$delete = $adb->query( "delete from vtiger_def_org_share where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_def_org_share w funkcji DeleteDefOrgInformations()" );
		$delete = $adb->query( "delete from vtiger_org_share_action2tab where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_org_share_action2tab w funkcji DeleteDefOrgInformations()" );
	}
	
	function DeleteFromCronTask()
	{
		global $adb;
		
		$delete = $adb->query( "delete from vtiger_cron_task where module = '".$this->module_name."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_cron_task w funkcji DeleteFromCronTask()" );
	}
	
	function DeleteFromCRMEntity()
	{
		global $adb;
		
		$delete = $adb->query( "delete from vtiger_crmentity where setype = '".$this->module_name."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_crmentity w funkcji DeleteFromCRMEntity()" );
	}
	function DeleteTab()
	{
		global $adb;
		
		$delete = $adb->query( "delete from vtiger_tab where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_tab w funkcji DeleteTab()" );
		$delete = $adb->query( "delete from vtiger_tab_info where tabid = '".$this->tabid."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_tab_info w funkcji DeleteTab()" );
	}
	function DeleteWsEntity()
	{
		global $adb;
		
		$delete = $adb->query( "delete from vtiger_ws_entity where name = '".$this->module_name."'", true,  "Błąd podczas usuwania rekordów z tabeli vtiger_ws_entity w funkcji DeleteWsEntity()" );
	}
	function DeleteDir($dir)
    {
     
        $fd = opendir($dir);
        if(!$fd) return false;
        while (($file = readdir($fd))!== false)
        {
            if($file =="." || $file== "..") continue;
            if(is_dir($dir."/".$file))
            {
                  $this->DeleteDir($dir."/".$file);
            }
            else
            {
                unlink("$dir/$file");
            }
        }
        closedir($fd);
        rmdir($dir);
    }
	
    // dodano 2013-10-03
    function DeleteLanguageFiles() {
        if( count( $this->supported_languages ) > 0 ) {
			foreach( $this->supported_languages as $lang ) {
				@unlink( "languages/$lang/".$this->module_name.".php" );
			}
		}
    }
    
    function DeleteRelatedLists() {
        global $adb;
		
		$delete = $adb->query( "DELETE FROM `vtiger_relatedlists` WHERE `label` = '".$this->module_name."'", true,  
        "Błąd podczas usuwania rekordów z tabeli vtiger_relatedlists w funkcji DeleteRelatedLists()" );
    }
    
    function DeleteSettingsField() {
		 global $adb;
        if( count( $this->settings_links ) > 0 ) {
			foreach( $this->settings_links as $setting ) { print_r($setting);
				$delete = $adb->query( "DELETE FROM `vtiger_settings_field` WHERE `name` = '".$setting['name']."' AND `linkto` = '".$setting['linkto']."'", true,  
                            "Błąd podczas usuwania rekordów z tabeli vtiger_settings_field w funkcji DeleteSettingsField()" );
			}
		}
		$adb->query( "DELETE FROM `vtiger_settings_field` WHERE `linkto` like '%module=".$this->module_name."%'", true,  
                            "Błąd podczas usuwania rekordów z tabeli vtiger_settings_field w funkcji DeleteSettingsField()" );
    }
    function DeleteWorkflows() {
		global $adb;
		$adb->query( "DELETE com_vtiger_workflows,com_vtiger_workflowtasks FROM `com_vtiger_workflows` 
			LEFT JOIN `com_vtiger_workflowtasks` ON com_vtiger_workflowtasks.workflow_id = com_vtiger_workflows.workflow_id
			WHERE `module_name` = '".$this->module_name."'", true,  
                            "Błąd podczas usuwania rekordów z tabeli vtiger_settings_field w funkcji DeleteWorkflows()" );
    }  
    function DeleteCrmentityrel() {
		global $adb;
		$adb->query( "DELETE FROM `vtiger_crmentityrel` WHERE `module` = '".$this->module_name."' OR `relmodule` = '".$this->module_name."'", true,  
                            "Błąd w funkcji DeleteCrmentityrel()" );
    }
    function DeleteIcon() {
        $filename = "layouts/vlayout/skins/images/".$this->module_name.".png";
        
        if ( file_exists( $filename ) ) {
            @unlink( $filename );
        }
    }
}