<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
require_once('includes/main/WebUI.php');
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
require_once 'includes/runtime/Cache.php';
include_once('install/models/InitSchema.php');

class VT610_to_YT100 {
	var $name = 'Vtiger CRM 6.1.0';
	var $version = '6.1.0';
	
	function preProcess() {
		$location = Install_InitSchema_Model::migration_schema;
		Install_InitSchema_Model::initializeDatabase($location, array('VT610_to_YT100_create', 'VT610_to_YT100_update'));
	}
	function postProcess() {
		$location = Install_InitSchema_Model::migration_schema;
		Install_InitSchema_Model::initializeDatabase($location, array('VT610_to_YT100_delete'));
		return true;
	}
	public function process() {
		global $log;
		$log->debug("Entering VT610_to_YT100::process() method ...");
		self::load_default_menu();
		self::addModule();
		self::settingsReplace();
		//if(self::checkModuleExists('OSSMenuManager'))
		//	self::menuManager();
		self::addBlocks();
		self::addFields();
		self::InactiveFields();
		
		$fieldsToDelete = array('Assets'=>array('account',"shippingtrackingnumber"),
		'Contacts'=>array('mailingcity',"mailingstreet",'mailingcountry',"othercountry",'mailingstate',"mailingpobox",'othercity',"otherstate",'mailingzip',"otherzip",'otherstreet',"otherpobox","accountid","fax","reference","title","department","notify_owner","secondaryemail","homephone","otherphone","assistant","assistantphone"),
		'Invoice'=>array('s_h_amount',"adjustment",'s_h_percent','ship_city','ship_code','ship_country','ship_state','ship_street','ship_pobox','bill_city','bill_code','bill_country','bill_state','bill_street','bill_pobox'),
		'Leads'=>array('city',"code",'state','country','lane','leadaddresstype','pobox',"emailoptout","designation","rating"),
		'PurchaseOrder'=>array('s_h_percent',"s_h_amount",'adjustment','ship_city','ship_code','ship_country','ship_state','ship_street','ship_pobox','bill_city','bill_code','bill_country','bill_state','bill_street','bill_pobox'),
		'Quotes'=>array('s_h_percent',"s_h_amount",'adjustment','inventorymanager'),
		'SalesOrder'=>array('s_h_percent',"s_h_amount",'adjustment'),
		'Accounts'=>array('bill_street',"bill_city","bill_state","bill_code","bill_country","bill_pobox","ship_street","ship_city","ship_state","ship_code","ship_country","ship_pobox"),
		'Vendors'=>array('country',"city","street","postalcode","state","pobox")
		);
		self::deleteFields($fieldsToDelete);
		self::handlers();
		self::addEmployees();
		$log->debug("Exiting VT610_to_YT100::process() method ...");
	}
	
	public function addModule(){
		global $log;
		$log->debug("Entering VT610_to_YT100::addModule() method ...");
		$modules = array('OSSMail', 'OSSMailTemplates', 'Password', 'OSSTimeControl', 'OSSMenuManager', 'OSSMailScanner','OSSPdf', 'OSSMailView', 'OSSDocumentControl', 'OSSProjectTemplates', 'OSSOutsourcedServices', 'OSSSoldServices', 'OutsourcedProducts', 'OSSPasswords', 'OSSEmployees', 'Calculations', 'OSSCosts', 'AJAXChat');
		foreach($modules AS $module){
			try {
				if(!self::checkModuleExists($module)){
					$importInstance = new Vtiger_PackageImport();
					$importInstance->_modulexml = simplexml_load_file('install/migrate_schema/VT610_to_YT100/'.$module.'.xml');
					$importInstance->import_Module();
					self::addModuleToMenu($module, (string)$importInstance->_modulexml->parent);
				}
			} catch (Exception $e) {
				Install_InitSchema_Model::addMigrationLog('addModule '.$e->getMessage(),'error');
			}
		}
		Install_InitSchema_Model::addMigrationLog('addModule');
		$log->debug("Exiting VT610_to_YT100::addModule() method ...");
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
				'sequence'      => 0,
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
		$menu_manager = array();
		$menu_manager[] = array(237,0,0,'My Home Page',1,1,0,'',0,'','','','',1);
		$menu_manager[] = array(238,237,-1,'Home',1,1,0,'',0,'','','','',1);
		$menu_manager[] = array(239,237,-1,'Calendar',2,1,0,'',0,'','','','',1);
		$menu_manager[] = array(241,301,-1,'Campaigns',1,1,0,'',0,'','','','',1);
		$menu_manager[] = array(242,282,-1,'Accounts',2,1,0,'',0,'','','','',1);
		$menu_manager[] = array(243,282,-1,'Contacts',3,1,0,'',0,'','','','',1);
		$menu_manager[] = array(244,282,-1,'Leads',1,1,0,'',0,'','','','',1);
		$menu_manager[] = array(245,305,-1,'Documents',1004,1,0,'',0,'','','','en_us*List of documents#pl_pl*Lista dokumentĂłw',1);
		$menu_manager[] = array(247,301,-1,'Potentials',2,1,0,'',0,'','','','',1);
		$menu_manager[] = array(248,301,-1,'Quotes',4,1,0,'',0,'','','','',1);
		$menu_manager[] = array(249,301,-1,'SalesOrder',5,1,0,'',0,'','','','',1);
		$menu_manager[] = array(250,301,-1,'Invoice',7,1,0,'',0,'','','','en_us*Sales invoices#pl_pl*Faktury sprzedaĹĽowe',1);
		$menu_manager[] = array(251,301,-1,'PriceBooks',9,1,0,'',0,'','','','',1);
		$menu_manager[] = array(253,304,-1,'HelpDesk',1,1,0,'',0,'','','','',1);
		$menu_manager[] = array(254,304,-1,'Faq',3,1,0,'',0,'','','','',1);
		$menu_manager[] = array(255,304,-1,'ServiceContracts',2,1,0,'',0,'','','','',1);
		$menu_manager[] = array(256,302,-1,'ProjectMilestone',2,1,0,'',0,'','','','',1);
		$menu_manager[] = array(257,302,-1,'ProjectTask',3,1,0,'',0,'','','','',1);
		$menu_manager[] = array(258,302,-1,'Project',1,1,0,'',0,'','','','',1);
		$menu_manager[] = array(260,305,-1,'Reports',1009,1,0,'',0,'','','','en_us*List of reports#pl_pl*Lista raportĂłw',1);
		$menu_manager[] = array(262,305,-1,'Products',989,1,0,'',0,'','','','',1);
		$menu_manager[] = array(263,282,-1,'Vendors',4,1,0,'',0,'','','','',1);
		$menu_manager[] = array(264,301,-1,'PurchaseOrder',6,1,0,'',0,'','','','',1);
		$menu_manager[] = array(265,305,-1,'Services',994,1,0,'',0,'','','','',1);
		$menu_manager[] = array(266,305,-1,'Assets',991,1,0,'',0,'','','','en_us*Sold Products#pl_pl*Produkty sprzedane',1);
		$menu_manager[] = array(268,305,-1,'PBXManager',1002,1,0,'index.php?module=PBXManager&view=List',0,' 1 ','','16x16','en_us*List of calls#pl_pl*Lista poĹ‚Ä…czeĹ„ telefonicznych',1);
		$menu_manager[] = array(269,305,-1,'RecycleBin',1006,1,0,'index.php?module=RecycleBin&view=List',0,' 1 ','','16x16','en_us*List of deleted records#pl_pl*Lista usuniÄ™tych rekordĂłw',1);
		$menu_manager[] = array(270,305,-1,'SMSNotifier',1001,1,0,'',0,'','','','en_us*List of text messages#pl_pl*Lista smsĂłw',1);
		$menu_manager[] = array(271,305,-1,'OSSPdf',1005,1,0,'',0,'','','','en_us*List of pdf templates#pl_pl*Lista szablonĂłw pdf',1);
		$menu_manager[] = array(272,237,-1,'OSSMail',3,1,0,'',0,'','','','en_us*My mailbox#pl_pl*Moja poczta',1);
		$menu_manager[] = array(273,305,-1,'OSSMailTemplates',1003,1,0,'',0,'','','','en_us*List of email templates#pl_pl*Lista szablonĂłw mailowych',1);
		$menu_manager[] = array(274,292,-1,'OSSTimeControl',2,1,0,'',0,'','','','en_us*Time control#pl_pl*Czas pracy',1);
		$menu_manager[] = array(277,305,-1,'OutsourcedProducts',990,1,0,'index.php?module=OutsourcedProducts&view=List',0,'','','16x16','',0);
		$menu_manager[] = array(278,305,-1,'OSSSoldServices',996,1,0,'index.php?module=OSSSoldServices&view=List',0,'','','16x16','',0);
		$menu_manager[] = array(279,305,-1,'OSSOutsourcedServices',995,1,0,'index.php?module=OSSOutsourcedServices&view=List',0,'','','16x16','',0);
		$menu_manager[] = array(280,305,-1,'OSSMailView',1000,1,0,'index.php?module=OSSMailView&view=List',0,'','','16x16','en_us*List of corporate mailbox#pl_pl*Lista maili',0);
		$menu_manager[] = array(282,0,0,'Companies',2,1,0,'',0,'','','16x16','en_us*Companies#pl_pl*Firmy',0);
		$menu_manager[] = array(292,0,0,'Human resources',6,1,0,'',0,'','','16x16','en_us*HR#pl_pl*Kadry',0);
		$menu_manager[] = array(299,305,0,'*separator*',992,1,3,'*separator*',0,'','','','',0);
		$menu_manager[] = array(301,0,0,'Sales',3,1,0,'',0,'','','16x16','en_us*Sales#pl_pl*SprzedaĹĽ',0);
		$menu_manager[] = array(302,0,0,'Projects',4,1,0,'',0,'','','16x16','en_us*Projects#pl_pl*Projekty',0);
		$menu_manager[] = array(304,0,0,'Support',5,1,0,'',0,'','','16x16','en_us*Support#pl_pl*Wsparcie',0);
		$menu_manager[] = array(305,0,0,'Databases',7,1,0,'',0,'  ','','16x16','en_us*Databases#pl_pl*Bazy danych',0);
		$menu_manager[] = array(306,305,0,'Products database',988,1,2,'*etykieta*',0,'','','16x16','en_us*Products database#pl_pl*Baza produktĂłw',0);
		$menu_manager[] = array(307,305,0,'Services database',993,1,2,'*etykieta*',0,'','','16x16','en_us*Services database#pl_pl*Baza usĹ‚ug',0);
		$menu_manager[] = array(308,305,0,'*separator*',997,1,3,'*separator*',0,'  ','','','',0);
		$menu_manager[] = array(309,305,0,'Lists',998,1,2,'*etykieta*',0,'','','16x16','en_us*Lists#pl_pl*Wykazy',0);
		$menu_manager[] = array(311,292,-1,'OSSEmployees',1,1,0,'index.php?module=OSSEmployees&view=List',0,'','','16x16','en_us*Employees#pl_pl*Pracownicy',0);
		$menu_manager[] = array(312,305,-1,'OSSPasswords',1007,1,0,'index.php?module=OSSPasswords&view=List',0,'','','16x16','en_us*List of passwords#pl_pl*Lista haseĹ‚',0);
		$menu_manager[] = array(323,301,-1,'Calculations',3,1,0,'index.php?module=Calculations&view=List',0,'','','16x16','en_us*Calculations#pl_pl*Kalkulacje',0);
		$menu_manager[] = array(324,301,-1,'OSSCosts',8,1,0,'index.php?module=OSSCosts&view=List',0,'','','16x16','en_us*Purchase invoices#pl_pl*Faktury zakupowe',0);
		$menu_manager[] = array(325,305,-1,'AddressLevel1',999,1,0,'index.php?module=AddressLevel1&view=List',0,'','','16x16','en_us*List of addresses#pl_pl*Lista adresĂłw',0);
		$menu_manager[] = array(327,305,0,'*separator*',1008,1,3,'*separator*',0,'','','','',0);
		$blocksModule = array('My Home Page','Companies','Human resources','Sales','Projects','Support','Databases','*separator*','Lists','Products database','Services database');
		
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
		global $log;
		$log->debug("Entering VT610_to_YT100::settingsReplace() method ...");
		$adb = PearDatabase::getInstance();
		//clear vtiger_settings_blocks table
		//$adb->pquery("DELETE FROM `vtiger_settings_blocks`", array(), true);
		//add new record
		$settings_blocks = array();
		$settings_blocks[] = array('LBL_USER_MANAGEMENT',1);
		$settings_blocks[] = array('LBL_STUDIO',3);
		//$settings_blocks[] = array(3,'LBL_COMPANY',5);
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
		
		//delete row from vtiger_settings_field table
		$delete_settings_field = array('LBL_MENU_EDITOR','index.php?module=MenuEditor&parent=Settings&view=Index','LBL_MAIL_SCANNER','index.php?parent=Settings&module=MailConverter&view=List');
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
		$settings_field[] = array("LBL_OTHER_SETTINGS","PDF","Smarty/templates/modules/OSSValidation/currency_update_mini.png","LBL_OSSPDF_INFO","index.php?module=OSSPdf&view=Index&parent=Settings","12","0","0");
		$settings_field[] = array("LBL_MAIL","Mail","","LBL_OSSMAIL_DESCRIPTION","index.php?module=OSSMail&parent=Settings&view=index","13","0","0");
		$settings_field[] = array("LBL_SECURITY_MANAGEMENT","LBL_PASSWORD_CONF", "","LBL_PASSWORD_DESCRIPTION","index.php?module=Password&parent=Settings&view=Index","1","0","0");
		$settings_field[] = array("LBL_STUDIO","Menu Manager","menueditor.png","LBL_MENU_DESC","index.php?module=OSSMenuManager&view=Configuration&parent=Settings","3","0","1");
		$settings_field[] = array("LBL_STUDIO","LBL_ARRANGE_RELATED_TABS","picklist.gif","LBL_ARRANGE_RELATED_TABS","index.php?module=LayoutEditor&parent=Settings&view=Index&mode=showRelatedListLayout","4","0","1");
		$settings_field[] = array("LBL_MAIL","Mail Scanner","","LBL_MAIL_SCANNER_DESCRIPTION","index.php?module=OSSMailScanner&parent=Settings&view=index","19","0","0");
		$settings_field[] = array("LBL_SECURITY_MANAGEMENT","Mail Logs","","LBL_MAIL_LOGS_DESCRIPTION","index.php?module=OSSMailScanner&parent=Settings&view=logs","20","0","0");
		$settings_field[] = array("LBL_MAIL","Mail View","","LBL_MAIL_VIEW_DESCRIPTION","index.php?module=OSSMailView&parent=Settings&view=index","21","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","Document Control","","LBL_DOCUMENT_CONTROL_DESCRIPTION","index.php?module=OSSDocumentControl&parent=Settings&view=Index","22","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","Project Templates","","LBL_PROJECT_TEMPLATES_DESCRIPTION","index.php?module=OSSProjectTemplates&parent=Settings&view=Index","23","0","0");
		$settings_field[] = array("LBL_About_YetiForce","License", "","LBL_LICENSE_DESCRIPTION","index.php?module=Vtiger&parent=Settings&view=License", "","0","0");
		$settings_field[] = array("LBL_OTHER_SETTINGS","OSSPassword Configuration","migrate.gif","LBL_OSSPASSWORD_CONFIGURATION_DESCRIPTION","index.php?module=OSSPasswords&view=ConfigurePass&parent=Settings","24","0","0");
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
		$log->debug("Entering VT610_to_YT100::handlers() method ...");
		require_once 'modules/com_vtiger_workflow/include.inc';
		require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
		require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
		require_once('include/events/include.inc');
		global $adb;
		
		$removeClass = array('RecurringInvoiceHandler','HelpDeskHandler','ModTrackerHandler','PBXManagerHandler','PBXManagerBatchHandler','ServiceContractsHandler','InvoiceHandler','PurchaseOrderHandler','ModCommentsHandler','Vtiger_RecordLabelUpdater_Handler','SECURE');
		$addHandler = array();
		$addHandler[] = array('vtiger.entity.beforeunlink','data/VTEntityDelta.php','VTEntityDelta',NULL,'1','[]');
		$addHandler[] = array('vtiger.entity.afterunlink','data/VTEntityDelta.php','VTEntityDelta',NULL,'1','[]');
		$addHandler[] = array('vtiger.entity.aftersave.final','modules/ModTracker/handlers/ModTrackerHandler.php','ModTrackerHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.beforedelete','modules/ModTracker/handlers/ModTrackerHandler.php','ModTrackerHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.afterrestore','modules/ModTracker/handlers/ModTrackerHandler.php','ModTrackerHandler','','1','[]');
		$addHandler[] = array('vtiger.entity.aftersave','modules/PBXManager/handlers/PBXManagerHandler.php','PBXManagerHandler','','1','[\"VTEntityDelta\"]');
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
		
		try {
			$em = new VTEventsManager($adb);
			foreach($removeClass as $handlerClass)
				$em->unregisterHandler($handlerClass);

			foreach($addHandler as $handler)
				$em->registerHandler($handler[0], $handler[1], $handler[2], $handler[3], $handler[5]);
		} catch (Exception $e) {
			Install_InitSchema_Model::addMigrationLog('handlers '.$e->getMessage(),'error');
		}
		Install_InitSchema_Model::addMigrationLog('handlers');
	}
	public function checkModuleExists($moduleName){
		global $log;
		$log->debug("Entering VT610_to_YT100::checkModuleExists() method ...");
		global $adb;
		$result = $adb->pquery('SELECT * FROM vtiger_tab WHERE name = ?', array($moduleName));
		if(!$adb->num_rows($result)) {
			return false;
		}
		return true;
	}
	public function blocksTable(){
		global $log;
		$log->debug("Entering VT610_to_YT100::blocksTable() method ...");
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
		$blocksHelpDesk = array(array('LBL_SHARING_INFORMATION',3,0,0,0,0,0,0,1));
		$blocksServiceContracts = array(array('LBL_SUMMARY',2,0,0,0,0,0,1,1),array('BLOCK_INFORMATION_TIME',3,0,0,0,0,0,1,1));
		$blocksAccounts = array(array('LBL_ADDRESS_DELIVERY_INFORMATION',9,0,0,0,0,0,0,1),array('LBL_REGISTRATION_INFO',5,0,0,0,0,0,1,1),array('LBL_CONTACT_INFO',2,0,0,0,0,0,1,1),array('LBL_ADVANCED_BLOCK',4,0,0,0,0,0,1,1),array('LBL_FINANSIAL_SUMMARY',3,0,0,0,0,0,1,1),array('LBL_SHARING_INFORMATION',6,0,0,0,0,0,0,1),array(),array());
		$blocksContacts = array(array('LBL_ADDRESS_MAILING_INFORMATION',7,0,0,0,0,0,0,1),array('LBL_CONTACT_INFO',2,0,0,0,0,0,1,1),array('LBL_SHARING_INFORMATION',5,0,0,0,0,0,0,1),array(),array(),array(),array(),array());
		$blocksPotentials = array(array('LBL_SUMMARY',6,0,0,0,0,0,1,0),array('LBL_FINANSIAL_SUMMARY',2,0,0,0,0,0,1,1),array('LBL_SHARING_INFORMATION',4,0,0,0,0,0,0,1));
		$blocksCampaigns = array(array('LBL_SHARING_INFORMATION',4,0,0,0,0,0,0,1));
		$blocksProject = array(array('LBL_SUMMARY',5,0,0,0,0,0,1,0),array('LBL_SHARING_INFORMATION',3,0,0,0,0,0,0,1));
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
		'HelpDesk'=>$blocksHelpDesk,
		'ServiceContracts'=>$blocksServiceContracts,
		'Accounts'=>$blocksAccounts,
		'Contacts'=>$blocksContacts,
		'Potentials'=>$blocksPotentials,
		'Campaigns'=>$blocksCampaigns,
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
		return $setBlockToCRM;
	}
	/*public function menuManager(){
		global $log;
		$log->debug("Entering VT610_to_YT100::menuManager() method ...");
		$adb = PearDatabase::getInstance();

		try {
			$result = $adb->query("SELECT * FROM `vtiger_ossmenumanager` ;", true);
		} catch (Exception $e) {
			Install_InitSchema_Model::addMigrationLog('menuManager '.$e->getMessage(),'error');
		}
		
		if(!$adb->num_rows($result)){
			$recordModel = Vtiger_Record_Model::getCleanInstance( 'OSSMenuManager' );
			$recordModel->load_default_menu();
		}
		Install_InitSchema_Model::addMigrationLog('menuManager');
	}*/
	// self::addBlocks($setBlockToCRM);
	public function addBlocks(){
		global $log;
		$log->debug("Entering VT610_to_YT100::addBlocks() method ...");
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
	}
	public function checkBlockExists($moduleName, $block){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT100::checkBlockExists() method ...");
		
		if($moduleName == 'Settings')
			$result = $adb->pquery("SELECT * FROM vtiger_settings_blocks WHERE label = ? ;", array($block), true);
		else
			$result = $adb->pquery("SELECT * FROM vtiger_blocks WHERE tabid = ? AND blocklabel = ? ;", array(getTabid($moduleName),$block['blocklabel']));

		if(!$adb->num_rows($result)) {
			$log->debug("Exiting VT610_to_YT100::checkBlockExists() method ...");
			return false;
		}
		$log->debug("Exiting VT610_to_YT100::checkBlockExists() method ...");
		return true;
	}
	public function getFieldsAll(){
		global $log;
		$log->debug("Entering VT610_to_YT100::getFieldsAll() method ...");
		$columnName = array("tabid","id","column","table","generatedtype","uitype","name","label","readonly","presence","defaultvalue","maximumlength","sequence","block","displaytype","typeofdata","quickcreate","quicksequence","info_type","masseditable","helpinfo","summaryfield","columntype","blocklabel","setpicklistvalues");

		$tab = 8;
		$Documents = array(
		array("8","865","ossdc_status","vtiger_notes","1","15","ossdc_status","ossdc_status","1","2","","100","13","17","1","V~O","1", "","BAS","1","0","0","varchar(255)","LBL_NOTE_INFORMATION",array("None","Checked"))
		);

		$tab = 42;
		$ProjectTask = array(
		array("42","816","sum_time","vtiger_projecttask","1","7","sum_time","Total time [h]","1","2","","100","10","104","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_PROJECT_TASK_INFORMATION"),
		array("42","1318","parentid","vtiger_projecttask","2","10","parentid","Parent ID","1","2","","100","11","104","1","V~O","1", "","BAS","1","0","0","int(19)","LBL_PROJECT_TASK_INFORMATION"),
		array("42","1319","projectmilestoneid","vtiger_projecttask","2","10","projectmilestoneid","ProjectMilestone","1","2","","100","12","104","1","V~M","2", "","BAS","1","0","0","int(19)","LBL_PROJECT_TASK_INFORMATION"),
		array("42","1320","targetenddate","vtiger_projecttask","2","5","targetenddate","Target End Date","1","2","","100","6","105","1","D~O","1", "","BAS","1","0","0","date","LBL_CUSTOM_INFORMATION")
		);

		$tab = 14;
		$Products = array(
		array("14","911","pssubcategory","vtiger_products","1","15","pssubcategory","Sub Category","1","2","","100","23","31","1","V~O","2","6","BAS","1","0","0","varchar(255)","LBL_PRODUCT_INFORMATION",array("Dell","Symantec","Eset","Kaspersky","Sophos")),
		array("14","178","pscategory","vtiger_products","1","15","pscategory","Product Category","1","2","","100","6","31","1","V~O","2","5","BAS","1","0","1","varchar(200)","LBL_PRODUCT_INFORMATION",array("Hardware","Software","CRM Applications","Antivirus","Backup")),
		array("14","1392","inheritsharing","vtiger_crmentity","1","56","inheritsharing","Copy permissions automatically","1","2","","100","24","31","1","C~O","1", "","BAS","1","0","0","tinyint(1)","LBL_PRODUCT_INFORMATION")
		);

		$tab = 34;
		$ServiceContracts = array(
		array("34","820","sum_time","vtiger_servicecontracts","1","7","sum_time","Total time [Service Contract]","1","2","","100","3","180","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_SUMMARY"),
		array("34","1046","sum_time_p","vtiger_servicecontracts","1","7","sum_time_p","Total time [Projects]","1","2","","100","2","180","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("34","1047","sum_time_h","vtiger_servicecontracts","1","7","sum_time_h","Total time [Tickets]","1","2","","100","1","180","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("34","1048","sum_time_all","vtiger_servicecontracts","1","7","sum_time_all","Total time [Sum]","1","2","","100","4","180","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY")

		);

		$tab = 35;
		$Services = array(
		array("35","910","pssubcategory","vtiger_service","1","15","pssubcategory","Sub Category","1","2","","100","18","91","1","V~O","2","5","BAS","1","0","0","varchar(255)","LBL_SERVICE_INFORMATION",array("Dell","Symantec","Eset","Kaspersky","Sophos")),
		array("35","574","pscategory","vtiger_service","1","15","pscategory","Service Category","1","2","","100","7","91","1","V~O","2","3","BAS","1","0","1","varchar(200)","LBL_SERVICE_INFORMATION",array("Hardware","Software","CRM Applications","Antivirus","Backup")),
		array("35","1394","inheritsharing","vtiger_crmentity","1","56","inheritsharing","Copy permissions automatically","1","2","","100","19","91","1","C~O","1", "","BAS","1","0","0","tinyint(1)","LBL_SERVICE_INFORMATION")
		);

		$tab = 43;
		$Project = array(
		array("43","826","sum_time","vtiger_project","1","7","sum_time","Total time [Project]","1","2","","100","1","132","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_SUMMARY"),
		array("43","830","sum_time_h","vtiger_project","1","7","sum_time_h","Total time [Tickets]","1","2","","100","5","132","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_SUMMARY"),
		array("43","832","sum_time_all","vtiger_project","1","7","sum_time_all","Total time [Sum]","1","2","","100","7","132","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_SUMMARY"),
		array("43","1044","servicecontractsid","vtiger_project","2","10","servicecontractsid","ServiceContracts","1","2","","100","11","107","1","V~O","1", "","BAS","1","0","0","int(19)","LBL_PROJECT_INFORMATION"),
		array("43","1381","inheritsharing","vtiger_crmentity","1","56","inheritsharing","Copy permissions automatically","1","2","","100","1","203","1","C~O","1", "","BAS","1","0","0","tinyint(1)","LBL_SHARING_INFORMATION")
		);

		$Users = array(
		array("29","1322","end_hour","vtiger_users","1","16","end_hour","Day ends at","1","2","","100","4","118","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_CALENDAR_SETTINGS",array("00:00","01:00","02:00","03:00","04:00","05:00","06:00","07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00","18:00","19:00","20:00","21:00","22:00","23:00"))
		);

		$tab = 13;
		$HelpDesk = array(
		array("13","814","sum_time","vtiger_troubletickets","1","7","sum_time","Total time [h]","1","2","","100","21","25","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","LBL_TICKET_INFORMATION"),
		array("13","1043","projectid","vtiger_troubletickets","2","10","projectid","Project","1","2","","100","22","25","1","V~O","2", "","BAS","1","0","0","int(19)","LBL_TICKET_INFORMATION"),
		array("13","1049","servicecontractsid","vtiger_troubletickets","2","10","servicecontractsid","ServiceContracts","1","2","","100","23","25","1","V~O","1", "","BAS","1","0","0","int(19)","LBL_TICKET_INFORMATION"),
		array("13","1341","attention","vtiger_crmentity","2","300","attention","Attention","1","2","","100","2","28","1","V~O","1", "","BAS","1","0","0","text","LBL_DESCRIPTION_INFORMATION"),
		array("13","1383","inheritsharing","vtiger_crmentity","1","56","inheritsharing","Copy permissions automatically","1","2","","100","1","204","1","C~O","1", "","BAS","1","0","0","tinyint(1)","LBL_SHARING_INFORMATION")
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
		array("7","969","registration_number_2","vtiger_leaddetails","1","1","registration_number_2","Registration number 2","1","2","","100","2","191","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_REGISTRATION_INFO")
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
		array("4","72","parentid","vtiger_contactdetails","2","10","parent_id","Member Of","1","2","","100","6","4","1","I~O","2","6","BAS","1","0","1","int(19)","LBL_CONTACT_INFORMATION"),
		array("4","1368","secondary_email","vtiger_contactdetails","2","13","secondary_email","Secondary Email","1","2","","100","4","197","1","E~O","1", "","BAS","1","0","0","varchar(50)","LBL_CONTACT_INFO"),
		array("4","1385","inheritsharing","vtiger_crmentity","1","56","inheritsharing","Copy permissions automatically","1","2","","100","1","205","1","C~O","1", "","BAS","1","0","0","tinyint(1)","LBL_SHARING_INFORMATION"),
		array("4","1391","notifilanguage","vtiger_contactdetails","2","32","notifilanguage","LBL_LANGUAGE_NOTIFICATIONS","1","2","","100","4","6","1","V~O","1", "","BAS","1","0","0","varchar(100)","LBL_CUSTOMER_PORTAL_INFORMATION")
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
		array("6","1287","buildingnumberb","vtiger_accountaddress","2","1","buildingnumberb","Building number","1","2","","100","1","10","1","V~O~LE~100","1", "","BAS","1","0","0","varchar(100)","LBL_ADDRESS_MAILING_INFORMATION"),
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
		array("6","1376","inheritsharing","vtiger_crmentity","1","56","inheritsharing","Copy permissions automatically","1","2","","100","2","200","1","C~O","1", "","BAS","1","0","0","tinyint(1)","LBL_SHARING_INFORMATION")
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
		array("18","975","registration_number_2","vtiger_vendor","1","1","registration_number_2","Registration number 2","1","2","","100","17","42","1","V~O","1", "","BAS","1","0","0","varchar(30)","LBL_VENDOR_INFORMATION")

		);

		$tab = 20;
		$Quotes = array(
		array("20","1388","form_payment","vtiger_quotes","2","16","form_payment","Form of payment","1","2","","100","25","49","1","V~O","3","26","BAS","1","0","0","varchar(255)","LBL_QUOTE_INFORMATION",array("Bank account")),
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
		array("20","1347","total_marginp","vtiger_quotes","1","7","total_marginp","Total margin Percentage","1","2","","100","3","49","3","NN~O","3","25","BAS","1","0","0","decimal(13,2)","LBL_QUOTE_INFORMATION")


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
		array("21","1356","total_marginp","vtiger_purchaseorder","1","7","total_marginp","Total margin Percentage","1","2","","100","3","58","3","NN~O","3","26","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS")
		);

		$tab = 22;
		$SalesOrder = array(
		array("22","1390","form_payment","vtiger_salesorder","2","16","form_payment","Form of payment","1","2","","100","25","61","1","V~O","3","26","BAS","1","0","0","varchar(255)","LBL_SO_INFORMATION",array("Bank account")),
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
		array("22","1350","total_marginp","vtiger_salesorder","1","7","total_marginp","Total margin Percentage","1","2","","100","3","64","3","NN~O","3","25","BAS","1","0","0","decimal(13,2)","LBL_RELATED_PRODUCTS")
		);

		$tab = 23;
		$Invoice = array(
		array("23","1389","form_payment","vtiger_invoice","2","16","form_payment","Form of payment","1","2","","100","28","67","1","V~O","3","28","BAS","1","0","0","varchar(255)","LBL_INVOICE_INFORMATION"),
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
		array("23","1367","potentialid","vtiger_invoice","2","10","potentialid","Potential","1","2","","100","27","67","1","V~M","1","27","BAS","1","0","0","int(19)","LBL_INVOICE_INFORMATION")
		);


		$tab = 26;
		$Campaigns = array(
		array("26","1377","inheritsharing","vtiger_crmentity","1","56","inheritsharing","Copy permissions automatically","1","2","","100","1","201","1","C~O","1", "","BAS","1","0","0","tinyint(1)","LBL_SHARING_INFORMATION")
		);

		$tab = 2;
		$Potentials = array(
		array("2","1379","inheritsharing","vtiger_crmentity","1","56","inheritsharing","Copy permissions automatically","1","2","","100","1","202","1","C~O","1", "","BAS","1","0","0","tinyint(1)","LBL_SHARING_INFORMATION"),
		array("2","834","sum_time","vtiger_potential","1","7","sum_time","Total time [h]","1","2","","100","1","133","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("2","836","sum_time_so","vtiger_potential","1","7","sum_time_so","Total time [Sales Order]","1","2","","100","3","133","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("2","838","sum_time_q","vtiger_potential","1","7","sum_time_q","Total time [Quotes]","1","2","","100","5","133","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("2","840","sum_time_all","vtiger_potential","1","7","sum_time_all","Total time [Sum]","1","2","","100","7","133","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("2","1281","sum_time_k","vtiger_potential","2","7","sum_time_k","Total time [Calculation]","1","2","","100","8","133","10","NN~O","1", "","BAS","1","0","0","decimal(13,2)","LBL_SUMMARY"),
		array("2","1357","sum_quotes","vtiger_potential","2","71","sum_quotes","Sum quotes","1","2","","100","4","199","10","N~O","1", "","BAS","1","0","1","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("2","1358","sum_salesorders","vtiger_potential","2","71","sum_salesorders","Sum sales orders","1","2","","100","2","199","10","N~O","1", "","BAS","1","0","1","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("2","1359","sum_invoices","vtiger_potential","2","71","sum_invoices","Sum invoices","1","2","","100","3","199","10","N~O","1", "","BAS","1","0","1","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("2","1360","sum_calculations","vtiger_potential","2","71","sum_calculations","Sum calculations","1","2","","100","5","199","10","N~O","1", "","BAS","1","0","0","decimal(25,8)","LBL_FINANSIAL_SUMMARY"),
		array("2","1374","average_profit_so","vtiger_potential","2","9","average_profit_so","Average profit sales order","1","2","","100","6","199","10","N~O~2~2","1", "","BAS","1","0","0","decimal(5,2)","LBL_FINANSIAL_SUMMARY")

		);

		$tab = 37;
		$Assets = array(
		array("37","818","sum_time","vtiger_assets","1","7","sum_time","Total time [h]","1","2","","100","1","192","10","NN~O","1", "","BAS","1","0","0","decimal(10,2)","BLOCK_INFORMATION_TIME"),
		array("37","926","potential","vtiger_assets","1","10","potential","Potential","1","2","","100","3","96","1","I~M","2","8","BAS","1","0","0","int(19)","LBL_CUSTOM_INFORMATION"),
		array("37","1314","parent_id","vtiger_assets","2","10","parent_id","Parent ID","1","2","","100","1","96","1","V~M","2","2","BAS","1","0","1","int(19)","LBL_CUSTOM_INFORMATION"),
		array("37","1325","pot_renewal","vtiger_assets","2","10","pot_renewal","Potential renewal","1","2","","100","4","96","1","V~O","1", "","BAS","1","0","0","int(19)","LBL_CUSTOM_INFORMATION")
		);

		$setToCRM = array('OSSMailTemplates'=>$OSSMailTemplates,'OSSEmployees'=>$OSSEmployees,'Users'=>$Users,'PurchaseOrder'=>$PurchaseOrder,'Vendors'=>$Vendors,'Accounts'=>$Accounts,'Contacts'=>$Contacts,'Leads'=>$Leads,'SalesOrder'=>$SalesOrder,'Invoice'=>$Invoice,'Quotes'=>$Quotes,'OSSCosts'=>$OSSCosts,'Calculations'=>$Calculations,'Campaigns'=>$Campaigns,'Assets'=>$Assets,'HelpDesk'=>$HelpDesk,'Project'=>$Project,'OSSPasswords'=>$OSSPasswords,'OSSMailView'=>$OSSMailView,'OSSTimeControl'=>$OSSTimeControl,'OutsourcedProducts'=>$OutsourcedProducts,'OSSSoldServices'=>$OSSSoldServices,'OSSOutsourcedServices'=>$OSSOutsourcedServices,'Services'=>$Services,'OSSPdf'=>$OSSPdf,'ServiceContracts'=>$ServiceContracts,'Products'=>$Products,'ProjectTask'=>$ProjectTask,'Documents'=>$Documents,'Potentials'=>$Potentials);

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
		$log->debug("Exiting VT610_to_YT100::getFieldsAll() method ...");
		return $setToCRMAfter;
	}
	public function addFields(){
		global $log;
		$log->debug("Entering VT610_to_YT100::addFields() method ...");
		include_once('vtlib/Vtiger/Module.php'); 
		$moduleToCopyValues = array('Contacts'=>array(array('table'=>'vtiger_contactaddress','copy'=>'addresslevel8a = mailingstreet, addresslevel5a = mailingcity, addresslevel1a = mailingcountry, addresslevel2a = mailingstate, addresslevel7a = mailingzip, addresslevel8b = otherstreet, addresslevel5b = othercity, addresslevel2b = otherstate, addresslevel1b = othercountry,  addresslevel7b = otherzip'),array('table'=>'vtiger_contactdetails','copy'=>'secondary_email = secondaryemail')),
		'Accounts'=>array('newTab'=>'vtiger_accountaddress', 'oldTab1'=>'vtiger_accountbillads', 'oldTab2'=>'vtiger_accountshipads', 'newId'=>'accountaddressid', 'oldId1'=>'accountaddressid', 'oldId2'=>'accountaddressid'),
		'Invoice'=>array('newTab'=>'vtiger_invoiceaddress', 'oldTab1'=>'vtiger_invoicebillads', 'oldTab2'=>'vtiger_invoiceshipads', 'newId'=>'invoiceaddressid', 'oldId1'=>'invoicebilladdressid', 'oldId2'=>'invoiceshipaddressid'),
		'Invoice'=>array('newTab'=>'vtiger_purchaseorderaddress', 'oldTab1'=>'vtiger_pobillads', 'oldTab2'=>'vtiger_poshipads', 'newId'=>'purchaseorderaddressid', 'oldId1'=>'pobilladdressid', 'oldId2'=>'poshipaddressid'),
		'Vendors'=>array(),
		'Leads'=>array(array('table'=>'vtiger_leadaddress','copy'=>'addresslevel8a = lane, addresslevel5a = city, addresslevel1a = country, addresslevel2a = state, addresslevel7a = code'),array('table'=>'vtiger_leaddetails','copy'=>'noapprovalemails = emailoptout'))
			);
		$setToCRMAfter = self::getFieldsAll();
		foreach($setToCRMAfter as $moduleName=>$fields){
			foreach($fields as $field){
				if(self::checkFieldExists($field))
					continue;
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
				} catch (Exception $e) {
					Install_InitSchema_Model::addMigrationLog('addFields '.$e->getMessage(),'error');
				}
			}
			if(array_key_exists($moduleName,$moduleToCopyValues))
				self::copyValues($moduleName,$moduleToCopyValues);
		}
		Install_InitSchema_Model::addMigrationLog('addFields');
		$log->debug("Exiting VT610_to_YT100::addFields() method ...");
	}
	public function checkFieldExists($field, $parent = ''){
		global $adb;
		if($parent)
			$result = $adb->pquery("SELECT * FROM vtiger_settings_field WHERE name = ? AND linkto = ? ;", array($field[1],$field[4]));
		else
			$result = $adb->pquery("SELECT * FROM vtiger_field WHERE columnname = ? AND tablename = ? ;", array($field['name'],$field['table']));
		if(!$adb->num_rows($result)) {
			return false;
		}
		return true;
	}
	//copy values
	public function copyValues($moduleName,$moduleToCopyValues){
		global $log;
		$log->debug("Entering VT610_to_YT100::copyValues() method ...");
		$adb = PearDatabase::getInstance();
		try {
			if($moduleName == "Accounts" || $moduleName == "PurchaseOrder" || $moduleName == "Invoice"){
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
		$log->debug("Exiting VT610_to_YT100::copyValues() method ...");
	}
	// self::InactiveFields($fieldsInactive);
	public function InactiveFields (){
		global $log,$adb;
		$log->debug("Entering VT610_to_YT100::InactiveFields() method ...");
		$fieldsInactive = array('HelpDesk'=>array('days',"hours"),
		'Accounts'=>array('tickersymbol',"notify_owner","rating"),
		'ProjectTask'=>array('projecttaskhours'),
		'Potentials'=>array('probability','nextstep','amount'),
		'Users'=>array('address_street','address_city','department','department','department','phone_home','phone_mobile','phone_other','phone_fax','email2','secondaryemail','address_city','address_state','address_country','address_postalcode','phone_work','title'),
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
		$log->debug("Exiting VT610_to_YT100::InactiveFields() method ...");
	}
	function addEmployees() {
		global $log,$adb;
		$log->debug("Entering VT610_to_YT100::addEmployees() method ...");
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
					Install_InitSchema_Model::addMigrationLog('addModule '.$e->getMessage(),'error');
				}
			}
		}
		Install_InitSchema_Model::addMigrationLog('addEmployees');
		$log->debug("Exiting VT610_to_YT100::addEmployees() method ...");
	}
	// self::deleteFields($fieldsToDelete);
	public function deleteFields($fieldsToDelete){
		global $log;
		$log->debug("Entering VT610_to_YT100::deleteFields() method ...");
		require_once('includes/main/WebUI.php');
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
		$log->debug("Exiting VT610_to_YT100::deleteFields() method ...");
	}
}