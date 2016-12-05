<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
namespace vtlib;

/**
 * Provides API to package vtiger CRM module and associated files.
 * @package vtlib
 */
class PackageExport
{

	public $_export_tmpdir = 'cache/vtlib';
	public $_export_modulexml_filename = null;
	public $_export_modulexml_file = null;
	protected $moduleInstance = false;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		if (is_dir($this->_export_tmpdir) === false) {
			mkdir($this->_export_tmpdir);
		}
	}
	/** Output Handlers */

	/** @access private */
	public function openNode($node, $delimiter = PHP_EOL)
	{
		$this->__write("<$node>$delimiter");
	}

	/** @access private */
	public function closeNode($node, $delimiter = PHP_EOL)
	{
		$this->__write("</$node>$delimiter");
	}

	/** @access private */
	public function outputNode($value, $node = '')
	{
		if ($node != '')
			$this->openNode($node, '');
		$this->__write($value);
		if ($node != '')
			$this->closeNode($node);
	}

	/** @access private */
	public function __write($value)
	{
		fwrite($this->_export_modulexml_file, $value);
	}

	/**
	 * Set the module.xml file path for this export and
	 * return its temporary path.
	 * @access private
	 */
	public function __getManifestFilePath()
	{
		if (empty($this->_export_modulexml_filename)) {
			// Set the module xml filename to be written for exporting.
			$this->_export_modulexml_filename = "manifest-" . time() . ".xml";
		}
		return "$this->_export_tmpdir/$this->_export_modulexml_filename";
	}

	/**
	 * Initialize Export
	 * @access private
	 */
	public function __initExport($module)
	{
		if ($this->moduleInstance->isentitytype) {
			// We will be including the file, so do a security check.
			Utils::checkFileAccessForInclusion("modules/$module/$module.php");
		}
		$this->_export_modulexml_file = fopen($this->__getManifestFilePath(), 'w');
		$this->__write("<?xml version='1.0'?>\n");
	}

	/**
	 * Post export work.
	 * @access private
	 */
	public function __finishExport()
	{
		if (!empty($this->_export_modulexml_file)) {
			fclose($this->_export_modulexml_file);
			$this->_export_modulexml_file = null;
		}
	}

	/**
	 * Clean up the temporary files created.
	 * @access private
	 */
	public function __cleanupExport()
	{
		if (!empty($this->_export_modulexml_filename)) {
			unlink($this->__getManifestFilePath());
		}
	}

	/**
	 * Export Module as a zip file.
	 * @param Module Instance of module
	 * @param Path Output directory path
	 * @param String Zipfilename to use
	 * @param Boolean True for sending the output as download
	 */
	public function export($moduleInstance, $todir = '', $zipfilename = '', $directDownload = false)
	{
		$this->moduleInstance = $moduleInstance;
		$module = $this->moduleInstance->name;

		$this->__initExport($module);

		// Call module export function
		$this->export_Module();

		$this->__finishExport();

		// Export as Zip
		// if($zipfilename == '') $zipfilename = "$module-" . date('YmdHis') . ".zip";
		$zipfilename = $this->moduleInstance->name . '_' . date('Y-m-d-Hi') . '_' . $this->moduleInstance->version . '.zip';
		$zipfilename = "$this->_export_tmpdir/$zipfilename";

		$zip = new Zip($zipfilename);

		// Add manifest file
		$zip->addFile($this->__getManifestFilePath(), 'manifest.xml');

		// Copy module directory
		$zip->copyDirectoryFromDisk("modules/$module");

		// Copy Settings/module directory
		if (is_dir("modules/Settings/$module"))
			$zip->copyDirectoryFromDisk("modules/Settings/$module", 'settings/');

		// Copy cron files of the module (if any)
		if (is_dir("cron/modules/$module"))
			$zip->copyDirectoryFromDisk("cron/modules/$module", "cron");

		//Copy module templates files
		if (is_dir('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . '/modules/' . $module))
			$zip->copyDirectoryFromDisk('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . '/modules/' . $module, 'templates');

		//Copy Settings module templates files, if any
		if (is_dir('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/Settings/$module"))
			$zip->copyDirectoryFromDisk('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/Settings/$module", "settings/templates");

		//Support to multiple layouts of module
		$layoutDirectories = glob('layouts' . '/*', GLOB_ONLYDIR);

		foreach ($layoutDirectories as $key => $layoutName) {
			if ($layoutName != 'layouts/' . \Vtiger_Viewer::getDefaultLayoutName()) {
				$moduleLayout = $layoutName . "/modules/$module";
				if (is_dir($moduleLayout)) {
					$zip->copyDirectoryFromDisk($moduleLayout, $moduleLayout);
				}

				$settingsLayout = $layoutName . "/modules/Settings/$module";
				if (is_dir($settingsLayout)) {
					$zip->copyDirectoryFromDisk($settingsLayout, $settingsLayout);
				}
			}
		}

		//Copy language files
		$this->__copyLanguageFiles($zip, $module);

		//Copy image file
		if (file_exists('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/skins/images/$module.png")) {
			$zip->copyFileFromDisk('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . '/skins/images', '', "$module.png");
		}

		// Copty config files
		if (file_exists("config/modules/$module.php")) {
			$zip->copyFileFromDisk("config/modules/", 'config/', "$module.php");
		}

		$zip->save();

		if ($todir) {
			copy($zipfilename, $todir);
		}

		if ($directDownload) {
			$zip->forceDownload($zipfilename);
			unlink($zipfilename);
		}
		$this->__cleanupExport();
	}

	/**
	 * Function copies language files to zip
	 * @param <vtlib\Zip> $zip
	 * @param string $module
	 */
	public function __copyLanguageFiles($zip, $module)
	{
		$languageFolder = 'languages';
		if ($dir = @opendir($languageFolder)) {  // open languages folder
			while (($langName = readdir($dir)) !== false) {
				if ($langName != '..' && $langName != '.' && is_dir($languageFolder . "/" . $langName)) {
					$langDir = @opendir($languageFolder . '/' . $langName);  //open languages/en_us folder
					while (($moduleLangFile = readdir($langDir)) !== false) {
						$langFilePath = $languageFolder . '/' . $langName . '/' . $moduleLangFile;
						if (is_file($langFilePath) && $moduleLangFile === $module . '.php') { //check if languages/en_us/module.php file exists
							$zip->copyFileFromDisk($languageFolder . '/' . $langName . '/', $languageFolder . '/' . $langName . '/', $moduleLangFile);
						} else if (is_dir($langFilePath) && $moduleLangFile == 'Settings') {
							$settingsLangDir = @opendir($langFilePath);
							while ($settingLangFileName = readdir($settingsLangDir)) {
								$settingsLangFilePath = $languageFolder . '/' . $langName . '/' . $moduleLangFile . '/' . $settingLangFileName;
								if (is_file($settingsLangFilePath) && $settingLangFileName === $module . '.php') {  //check if languages/en_us/Settings/module.php file exists
									$zip->copyFileFromDisk($languageFolder . '/' . $langName . '/' . $moduleLangFile . '/', $languageFolder . '/' . $langName . '/' . $moduleLangFile . '/', $settingLangFileName);
								}
							}
							closedir($settingsLangDir);
						}
					}
					closedir($langDir);
				}
			}
			closedir($dir);
		}
	}

	/**
	 * Export vtiger dependencies
	 * @access private
	 */
	public function export_Dependencies($moduleInstance)
	{
		$adb = \PearDatabase::getInstance();
		$moduleid = $moduleInstance->id;

		$sqlresult = $adb->pquery("SELECT * FROM vtiger_tab_info WHERE tabid = ?", array($moduleid));
		$minVersion = \App\Version::get();
		$maxVersion = false;
		$noOfPreferences = $adb->num_rows($sqlresult);
		for ($i = 0; $i < $noOfPreferences; ++$i) {
			$prefName = $adb->query_result($sqlresult, $i, 'prefname');
			$prefValue = $adb->query_result($sqlresult, $i, 'prefvalue');
			if ($prefName == 'vtiger_min_version') {
				$minVersion = $prefValue;
			}
			if ($prefName == 'vtiger_max_version') {
				$maxVersion = $prefValue;
			}
		}
		$this->openNode('dependencies');
		$this->outputNode($minVersion, 'vtiger_version');
		if ($maxVersion !== false)
			$this->outputNode($maxVersion, 'vtiger_max_version');
		$this->closeNode('dependencies');
	}

	/**
	 * Export Module Handler
	 * @access private
	 */
	public function export_Module()
	{
		$adb = \PearDatabase::getInstance();

		$moduleid = $this->moduleInstance->id;

		$sqlresult = $adb->pquery('SELECT * FROM vtiger_tab WHERE tabid = ?', [$moduleid]);
		$tabInfo = $adb->getRow($sqlresult);

		$tabname = $tabInfo['name'];
		$tablabel = $tabInfo['tablabel'];
		$tabVersion = isset($tabInfo['version']) ? $tabInfo['version'] : false;

		$this->openNode('module');
		$this->outputNode(date('Y-m-d H:i:s'), 'exporttime');
		$this->outputNode($tabname, 'name');
		$this->outputNode($tablabel, 'label');

		if (!$this->moduleInstance->isentitytype) {
			$type = 'extension';
		} elseif ($tabInfo['type'] == 1) {
			$type = 'inventory';
		} else {
			$type = 'entity';
		}
		$this->outputNode($type, 'type');

		if ($tabVersion) {
			$this->outputNode($tabVersion, 'version');
		}

		// Export dependency information
		$this->export_Dependencies($this->moduleInstance);

		// Export module tables
		$this->export_Tables();

		// Export module blocks
		$this->export_Blocks($this->moduleInstance);

		// Export module filters
		$this->export_CustomViews($this->moduleInstance);

		// Export module inventory fields
		if ($tabInfo['type'] == 1) {
			$this->exportInventory();
		}

		// Export Sharing Access
		$this->export_SharingAccess($this->moduleInstance);

		// Export Events
		$this->export_Events($this->moduleInstance);

		// Export Actions
		$this->export_Actions($this->moduleInstance);

		// Export Related Lists
		$this->export_RelatedLists($this->moduleInstance);

		// Export Custom Links
		$this->export_CustomLinks($this->moduleInstance);

		//Export cronTasks
		$this->export_CronTasks($this->moduleInstance);

		$this->closeNode('module');
	}

	/**
	 * Export module base and related tables
	 * @access private
	 */
	public function export_Tables()
	{
		$_exportedTables = [];
		$modulename = $this->moduleInstance->name;

		$this->openNode('tables');

		if ($this->moduleInstance->isentitytype) {
			$focus = \CRMEntity::getInstance($modulename);

			// Setup required module variables which is need for vtlib API's
			vtlib_setup_modulevars($modulename, $focus);
			$tables = $focus->tab_name;
			if (($key = array_search('vtiger_crmentity', $tables)) !== false) {
				unset($tables[$key]);
			}
			foreach ($tables as $table) {
				$this->openNode('table');
				$this->outputNode($table, 'name');
				$this->outputNode('<![CDATA[' . Utils::CreateTableSql($table) . ']]>', 'sql');
				$this->closeNode('table');

				$_exportedTables[] = $table;
			}
		}
		$this->closeNode('tables');
	}

	/**
	 * Export module blocks with its related fields
	 * @access private
	 */
	public function export_Blocks($moduleInstance)
	{
		$adb = \PearDatabase::getInstance();
		$sqlresult = $adb->pquery("SELECT * FROM vtiger_blocks WHERE tabid = ?", Array($moduleInstance->id));
		$resultrows = $adb->num_rows($sqlresult);

		if (empty($resultrows))
			return;

		$this->openNode('blocks');
		for ($index = 0; $index < $resultrows; ++$index) {
			$blockid = $adb->query_result($sqlresult, $index, 'blockid');
			$blocklabel = $adb->query_result($sqlresult, $index, 'blocklabel');
			$block_sequence = $adb->query_result($sqlresult, $index, 'sequence');
			$block_show_title = $adb->query_result($sqlresult, $index, 'show_title');
			$block_visible = $adb->query_result($sqlresult, $index, 'visible');
			$block_create_view = $adb->query_result($sqlresult, $index, 'create_view');
			$block_edit_view = $adb->query_result($sqlresult, $index, 'edit_view');
			$block_detail_view = $adb->query_result($sqlresult, $index, 'detail_view');
			$block_display_status = $adb->query_result($sqlresult, $index, 'display_status');
			$block_iscustom = $adb->query_result($sqlresult, $index, 'iscustom');
			$block_islist = $adb->query_result($sqlresult, $index, 'islist');

			$this->openNode('block');
			$this->outputNode($blocklabel, 'label');
			$this->outputNode($block_sequence, 'sequence');
			$this->outputNode($block_show_title, 'show_title');
			$this->outputNode($block_visible, 'visible');
			$this->outputNode($block_create_view, 'create_view');
			$this->outputNode($block_edit_view, 'edit_view');
			$this->outputNode($block_detail_view, 'detail_view');
			$this->outputNode($block_display_status, 'display_status');
			$this->outputNode($block_iscustom, 'iscustom');
			$this->outputNode($block_islist, 'islist');

			// Export fields associated with the block
			$this->export_Fields($moduleInstance, $blockid);
			$this->closeNode('block');
		}
		$this->closeNode('blocks');
	}

	/**
	 * Export fields related to a module block
	 * @access private
	 */
	public function export_Fields($moduleInstance, $blockid)
	{
		$adb = \PearDatabase::getInstance();

		$fieldresult = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid=? && block=?", Array($moduleInstance->id, $blockid));
		$fieldcount = $adb->num_rows($fieldresult);

		if (empty($fieldcount))
			return;

		$entityresult = $adb->pquery("SELECT * FROM vtiger_entityname WHERE tabid=?", Array($moduleInstance->id));
		$entity_fieldname = $adb->query_result($entityresult, 0, 'fieldname');

		$this->openNode('fields');
		for ($index = 0; $index < $fieldcount; ++$index) {
			$this->openNode('field');
			$fieldresultrow = $adb->fetchByAssoc($fieldresult);

			$fieldname = $fieldresultrow['fieldname'];
			$uitype = $fieldresultrow['uitype'];
			$fieldid = $fieldresultrow['fieldid'];

			$info_schema = $adb->pquery("SELECT column_name, column_type FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = SCHEMA() && table_name = ? && column_name = ?", Array($fieldresultrow['tablename'], $fieldresultrow['columnname']));
			$info_schemarow = $adb->fetchByAssoc($info_schema);

			$this->outputNode($fieldname, 'fieldname');
			$this->outputNode($uitype, 'uitype');
			$this->outputNode($fieldresultrow['columnname'], 'columnname');
			$this->outputNode($info_schemarow['column_type'], 'columntype');
			$this->outputNode($fieldresultrow['tablename'], 'tablename');
			$this->outputNode($fieldresultrow['generatedtype'], 'generatedtype');
			$this->outputNode($fieldresultrow['fieldlabel'], 'fieldlabel');
			$this->outputNode($fieldresultrow['readonly'], 'readonly');
			$this->outputNode($fieldresultrow['presence'], 'presence');
			$this->outputNode($fieldresultrow['defaultvalue'], 'defaultvalue');
			$this->outputNode($fieldresultrow['sequence'], 'sequence');
			$this->outputNode($fieldresultrow['maximumlength'], 'maximumlength');
			$this->outputNode($fieldresultrow['typeofdata'], 'typeofdata');
			$this->outputNode($fieldresultrow['quickcreate'], 'quickcreate');
			$this->outputNode($fieldresultrow['quickcreatesequence'], 'quickcreatesequence');
			$this->outputNode($fieldresultrow['displaytype'], 'displaytype');
			$this->outputNode($fieldresultrow['info_type'], 'info_type');
			$this->outputNode($fieldresultrow['fieldparams'], 'fieldparams');
			$this->outputNode($fieldresultrow['helpinfo'], 'helpinfo');

			if (isset($fieldresultrow['masseditable'])) {
				$this->outputNode($fieldresultrow['masseditable'], 'masseditable');
			}
			if (isset($fieldresultrow['summaryfield'])) {
				$this->outputNode($fieldresultrow['summaryfield'], 'summaryfield');
			}
			// Export Entity Identifier Information
			if ($fieldname == $entity_fieldname) {
				$this->openNode('entityidentifier');
				$this->outputNode($adb->query_result($entityresult, 0, 'entityidfield'), 'entityidfield');
				$this->outputNode($adb->query_result($entityresult, 0, 'entityidcolumn'), 'entityidcolumn');
				$this->closeNode('entityidentifier');
			}

			// Export picklist values for picklist fields
			if ($uitype == '15' || $uitype == '16' || $uitype == '111' || $uitype == '33' || $uitype == '55') {
				if ($uitype == '16') {
					$picklistvalues = vtlib_getPicklistValues($fieldname);
				} else {
					$picklistvalues = vtlib_getPicklistValues_AccessibleToAll($fieldname);
				}
				$this->openNode('picklistvalues');
				foreach ($picklistvalues as $picklistvalue) {
					$this->outputNode($picklistvalue, 'picklistvalue');
				}
				$this->closeNode('picklistvalues');
			}

			// Export field to module relations
			if ($uitype == '10') {
				$relatedmodres = $adb->pquery("SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=?", Array($fieldid));
				$relatedmodcount = $adb->num_rows($relatedmodres);
				if ($relatedmodcount) {
					$this->openNode('relatedmodules');
					for ($relmodidx = 0; $relmodidx < $relatedmodcount; ++$relmodidx) {
						$this->outputNode($adb->query_result($relatedmodres, $relmodidx, 'relmodule'), 'relatedmodule');
					}
					$this->closeNode('relatedmodules');
				}
			}
			if ($uitype == '302') {
				$this->outputNode('', 'fieldparams');
				$this->openNode('tree_template');
				$trees = $adb->pquery('SELECT * FROM vtiger_trees_templates WHERE templateid=?;', Array($fieldresultrow['fieldparams']));
				if ($adb->num_rows($trees) > 0) {
					$this->outputNode($adb->query_result_raw($trees, 0, 'name'), 'name');
					$this->outputNode($adb->query_result_raw($trees, 0, 'access'), 'access');
					$treesData = $adb->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid=?;', Array($fieldresultrow['fieldparams']));
					$this->openNode('tree_values');
					$countTreesData = $adb->num_rows($treesData);
					for ($i = 0; $i < $countTreesData; $i++) {
						$this->openNode('tree_value');
						$this->outputNode($adb->query_result_raw($treesData, $i, 'name'), 'name');
						$this->outputNode($adb->query_result_raw($treesData, $i, 'tree'), 'tree');
						$this->outputNode($adb->query_result_raw($treesData, $i, 'parenttrre'), 'parenttrre');
						$this->outputNode($adb->query_result_raw($treesData, $i, 'depth'), 'depth');
						$this->outputNode($adb->query_result_raw($treesData, $i, 'label'), 'label');
						$this->outputNode($adb->query_result_raw($treesData, $i, 'state'), 'state');
						$this->closeNode('tree_value');
					}
					$this->closeNode('tree_values');
				}
				$this->closeNode('tree_template');
			}
			$this->closeNode('field');
		}
		$this->closeNode('fields');
	}

	/**
	 * Export Custom views of the module
	 * @access private
	 */
	public function export_CustomViews($moduleInstance)
	{
		$db = \PearDatabase::getInstance();

		$customviewres = $db->pquery("SELECT * FROM vtiger_customview WHERE entitytype = ?", [$moduleInstance->name]);
		if (!$customviewres->rowCount())
			return;

		$this->openNode('customviews');
		while ($row = $db->getRow($customviewres)) {
			$setdefault = ($row['setdefault'] == 1) ? 'true' : 'false';
			$setmetrics = ($row['setmetrics'] == 1) ? 'true' : 'false';

			$this->openNode('customview');
			$this->outputNode($row['viewname'], 'viewname');
			$this->outputNode($setdefault, 'setdefault');
			$this->outputNode($setmetrics, 'setmetrics');
			$this->outputNode($row['featured'], 'featured');
			$this->outputNode($row['privileges'], 'privileges');
			$this->outputNode($row['presence'], 'presence');
			$this->outputNode($row['sequence'], 'sequence');
			$this->outputNode('<![CDATA[' . $row['description'] . ']]>', 'description');
			$this->outputNode($row['sort'], 'sort');

			$this->openNode('fields');
			$cvid = $row['cvid'];
			$cvcolumnres = $db->pquery("SELECT * FROM vtiger_cvcolumnlist WHERE cvid=?", [$cvid]);
			while ($cvRow = $db->getRow($cvcolumnres)) {
				$cvColumnNames = explode(':', $cvRow['columnname']);

				$this->openNode('field');
				$this->outputNode($cvColumnNames[2], 'fieldname');
				$this->outputNode($cvRow['columnindex'], 'columnindex');

				$cvcolumnruleres = $db->pquery("SELECT * FROM vtiger_cvadvfilter WHERE cvid=? && columnname=?", [$cvid, $cvRow['columnname']]);
				if ($cvcolumnruleres->rowCount()) {
					$this->openNode('rules');
					while ($rulesRow = $db->getRow($cvcolumnruleres)) {
						$cvColumnRuleComp = Filter::translateComparator($rulesRow['comparator'], true);
						$this->openNode('rule');
						$this->outputNode($rulesRow['columnindex'], 'columnindex');
						$this->outputNode($cvColumnRuleComp, 'comparator');
						$this->outputNode($rulesRow['value'], 'value');
						$this->closeNode('rule');
					}
					$this->closeNode('rules');
				}
				$this->closeNode('field');
			}
			$this->closeNode('fields');
			$this->closeNode('customview');
		}
		$this->closeNode('customviews');
	}

	/**
	 * Export Sharing Access of the module
	 * @access private
	 */
	public function export_SharingAccess($moduleInstance)
	{
		$adb = \PearDatabase::getInstance();

		$deforgshare = $adb->pquery("SELECT * FROM vtiger_def_org_share WHERE tabid=?", Array($moduleInstance->id));
		$deforgshareCount = $adb->num_rows($deforgshare);

		if (empty($deforgshareCount))
			return;

		$this->openNode('sharingaccess');
		if ($deforgshareCount) {
			for ($index = 0; $index < $deforgshareCount; ++$index) {
				$permission = $adb->query_result($deforgshare, $index, 'permission');
				$permissiontext = '';
				if ($permission == '0')
					$permissiontext = 'public_readonly';
				if ($permission == '1')
					$permissiontext = 'public_readwrite';
				if ($permission == '2')
					$permissiontext = 'public_readwritedelete';
				if ($permission == '3')
					$permissiontext = 'private';

				$this->outputNode($permissiontext, 'default');
			}
		}
		$this->closeNode('sharingaccess');
	}

	/**
	 * Export Events of the module
	 * @access private
	 */
	public function export_Events($moduleInstance)
	{
		//TODU: needs updating
		return false;
		//$events = Event::getAll($moduleInstance);
		if (!$events)
			return;

		$this->openNode('events');
		foreach ($events as $event) {
			$this->openNode('event');
			$this->outputNode($event->eventname, 'eventname');
			$this->outputNode('<![CDATA[' . $event->classname . ']]>', 'classname');
			$this->outputNode('<![CDATA[' . $event->filename . ']]>', 'filename');
			$this->outputNode('<![CDATA[' . $event->condition . ']]>', 'condition');
			$this->closeNode('event');
		}
		$this->closeNode('events');
	}

	public function export_Actions($moduleInstance)
	{

		if (!$moduleInstance->isentitytype)
			return;

		$adb = \PearDatabase::getInstance();
		$result = $adb->pquery('SELECT distinct(actionname) FROM vtiger_profile2utility, vtiger_actionmapping
			WHERE vtiger_profile2utility.activityid=vtiger_actionmapping.actionid and tabid=?', Array($moduleInstance->id));

		if ($adb->num_rows($result)) {
			$this->openNode('actions');
			while ($resultrow = $adb->fetch_array($result)) {
				$this->openNode('action');
				$this->outputNode('<![CDATA[' . $resultrow['actionname'] . ']]>', 'name');
				$this->outputNode('enabled', 'status');
				$this->closeNode('action');
			}
			$this->closeNode('actions');
		}
	}

	/**
	 * Export related lists associated with module.
	 * @access private
	 */
	public function export_RelatedLists($moduleInstance)
	{

		if (!$moduleInstance->isentitytype)
			return;

		$adb = \PearDatabase::getInstance();
		$result = $adb->pquery("SELECT * FROM vtiger_relatedlists WHERE tabid = ?", Array($moduleInstance->id));
		if ($adb->num_rows($result)) {
			$this->openNode('relatedlists');

			$countResult = $adb->num_rows($result);
			for ($index = 0; $index < $countResult; ++$index) {
				$row = $adb->fetch_array($result);
				$this->openNode('relatedlist');

				$relModuleInstance = Module::getInstance($row['related_tabid']);
				$this->outputNode($relModuleInstance->name, 'relatedmodule');
				$this->outputNode($row['name'], 'function');
				$this->outputNode($row['label'], 'label');
				$this->outputNode($row['sequence'], 'sequence');
				$this->outputNode($row['presence'], 'presence');

				$action_text = $row['actions'];
				if (!empty($action_text)) {
					$this->openNode('actions');
					$actions = explode(',', $action_text);
					foreach ($actions as $action) {
						$this->outputNode($action, 'action');
					}
					$this->closeNode('actions');
				}
				$this->closeNode('relatedlist');
			}

			$this->closeNode('relatedlists');
		}

		// Relations in the opposite direction
		$result = $adb->pquery("SELECT * FROM vtiger_relatedlists WHERE related_tabid = ?", Array($moduleInstance->id));
		if ($adb->num_rows($result)) {
			$this->openNode('inrelatedlists');

			$countResult = $adb->num_rows($result);
			for ($index = 0; $index < $countResult; ++$index) {
				$row = $adb->fetch_array($result);
				$this->openNode('inrelatedlist');

				$relModuleInstance = Module::getInstance($row['tabid']);
				$this->outputNode($relModuleInstance->name, 'inrelatedmodule');
				$this->outputNode($row['name'], 'function');
				$this->outputNode($row['label'], 'label');
				$this->outputNode($row['sequence'], 'sequence');
				$this->outputNode($row['presence'], 'presence');

				$action_text = $row['actions'];
				if (!empty($action_text)) {
					$this->openNode('actions');
					$actions = explode(',', $action_text);
					foreach ($actions as $action) {
						$this->outputNode($action, 'action');
					}
					$this->closeNode('actions');
				}
				$this->closeNode('inrelatedlist');
			}
			$this->closeNode('inrelatedlists');
		}
	}

	/**
	 * Export custom links of the module.
	 * @access private
	 */
	public function export_CustomLinks($moduleInstance)
	{
		$customlinks = $moduleInstance->getLinksForExport();
		if (!empty($customlinks)) {
			$this->openNode('customlinks');
			foreach ($customlinks as $customlink) {
				$this->openNode('customlink');
				$this->outputNode($customlink->linktype, 'linktype');
				$this->outputNode($customlink->linklabel, 'linklabel');
				$this->outputNode("<![CDATA[$customlink->linkurl]]>", 'linkurl');
				$this->outputNode("<![CDATA[$customlink->linkicon]]>", 'linkicon');
				$this->outputNode($customlink->sequence, 'sequence');
				$this->outputNode("<![CDATA[$customlink->handler_path]]>", 'handler_path');
				$this->outputNode("<![CDATA[$customlink->handler_class]]>", 'handler_class');
				$this->outputNode("<![CDATA[$customlink->handler]]>", 'handler');
				$this->closeNode('customlink');
			}
			$this->closeNode('customlinks');
		}
	}

	/**
	 * Export cron tasks for the module.
	 * @access private
	 */
	public function export_CronTasks($moduleInstance)
	{
		$cronTasks = Cron::listAllInstancesByModule($moduleInstance->name);
		$this->openNode('crons');
		foreach ($cronTasks as $cronTask) {
			$this->openNode('cron');
			$this->outputNode($cronTask->getName(), 'name');
			$this->outputNode($cronTask->getFrequency(), 'frequency');
			$this->outputNode($cronTask->getStatus(), 'status');
			$this->outputNode($cronTask->getHandlerFile(), 'handler');
			$this->outputNode($cronTask->getSequence(), 'sequence');
			$this->outputNode($cronTask->getDescription(), 'description');
			$this->closeNode('cron');
		}
		$this->closeNode('crons');
	}

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim = true)
	{
		Utils::Log($message, $delim);
	}

	/**
	 * Export module inventory fields
	 * @access private
	 */
	public function exportInventory()
	{
		$db = \PearDatabase::getInstance();
		$inventoryFieldModel = \Vtiger_InventoryField_Model::getInstance($this->moduleInstance->name);
		$tableName = $inventoryFieldModel->getTableName('fields');

		$result = $db->query(sprintf('SELECT * FROM %s', $tableName));
		if ($db->getRowCount($result) == 0)
			return false;

		$this->openNode('inventory');
		$this->openNode('fields');
		while ($row = $db->getRow($result)) {
			$this->openNode('field');

			$this->outputNode($row['columnname'], 'columnname');
			$this->outputNode($row['label'], 'label');
			$this->outputNode($row['invtype'], 'invtype');
			$this->outputNode($row['presence'], 'presence');
			$this->outputNode($row['defaultvalue'], 'defaultvalue');
			$this->outputNode($row['sequence'], 'sequence');
			$this->outputNode($row['block'], 'block');
			$this->outputNode($row['displaytype'], 'displaytype');
			$this->outputNode($row['params'], 'params');
			$this->outputNode($row['colspan'], 'colspan');

			$this->closeNode('field');
		}
		$this->closeNode('fields');
		$this->closeNode('inventory');
	}
}
