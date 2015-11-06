<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once('vtlib/Vtiger/Module.php');
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Event.php');
include_once('vtlib/Vtiger/Zip.php');
include_once('vtlib/Vtiger/Cron.php');

/**
 * Provides API to package vtiger CRM module and associated files.
 * @package vtlib
 */
class Vtiger_PackageExport
{

	var $_export_tmpdir = 'cache/vtlib';
	var $_export_modulexml_filename = null;
	var $_export_modulexml_file = null;

	/**
	 * Constructor
	 */
	function Vtiger_PackageExport()
	{
		if (is_dir($this->_export_tmpdir) === FALSE) {
			mkdir($this->_export_tmpdir);
		}
	}
	/** Output Handlers */

	/** @access private */
	function openNode($node, $delimiter = "\n")
	{
		$this->__write("<$node>$delimiter");
	}

	/** @access private */
	function closeNode($node, $delimiter = "\n")
	{
		$this->__write("</$node>$delimiter");
	}

	/** @access private */
	function outputNode($value, $node = '')
	{
		if ($node != '')
			$this->openNode($node, '');
		$this->__write($value);
		if ($node != '')
			$this->closeNode($node);
	}

	/** @access private */
	function __write($value)
	{
		fwrite($this->_export_modulexml_file, $value);
	}

	/**
	 * Set the module.xml file path for this export and
	 * return its temporary path.
	 * @access private
	 */
	function __getManifestFilePath()
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
	function __initExport($module, $moduleInstance)
	{
		if ($moduleInstance->isentitytype) {
			// We will be including the file, so do a security check.
			Vtiger_Utils::checkFileAccessForInclusion("modules/$module/$module.php");
		}
		$this->_export_modulexml_file = fopen($this->__getManifestFilePath(), 'w');
		$this->__write("<?xml version='1.0'?>\n");
	}

	/**
	 * Post export work.
	 * @access private
	 */
	function __finishExport()
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
	function __cleanupExport()
	{
		if (!empty($this->_export_modulexml_filename)) {
			unlink($this->__getManifestFilePath());
		}
	}

	/**
	 * Export Module as a zip file.
	 * @param Vtiger_Module Instance of module
	 * @param Path Output directory path
	 * @param String Zipfilename to use
	 * @param Boolean True for sending the output as download
	 */
	function export($moduleInstance, $todir = '', $zipfilename = '', $directDownload = false)
	{

		$module = $moduleInstance->name;

		$this->__initExport($module, $moduleInstance);

		// Call module export function
		$this->export_Module($moduleInstance);

		$this->__finishExport();

		// Export as Zip
		// if($zipfilename == '') $zipfilename = "$module-" . date('YmdHis') . ".zip";
		$zipfilename = $moduleInstance->name . "_" . date('Y-m-d-Hi') . "_" . $moduleInstance->version . ".zip";
		$zipfilename = "$this->_export_tmpdir/$zipfilename";

		$zip = new Vtiger_Zip($zipfilename);

		// Add manifest file
		$zip->addFile($this->__getManifestFilePath(), "manifest.xml");

		// Copy module directory
		$zip->copyDirectoryFromDisk("modules/$module");

		// Copy Settings/module directory
		if (is_dir("modules/Settings/$module"))
			$zip->copyDirectoryFromDisk("modules/Settings/$module", 'settings/');

		// Copy cron files of the module (if any)
		if (is_dir("cron/modules/$module"))
			$zip->copyDirectoryFromDisk("cron/modules/$module", "cron");

		//Copy module templates files
		if (is_dir('layouts/'.Vtiger_Viewer::getDefaultLayoutName().'/modules/'.$module))
			$zip->copyDirectoryFromDisk('layouts/'.Vtiger_Viewer::getDefaultLayoutName().'/modules/'.$module, 'templates');
		if (is_dir('layouts/'.vglobal('defaultLayout').'/modules/'.$module))
			$zip->copyDirectoryFromDisk('layouts/'.vglobal('defaultLayout').'/modules/'.$module, 'templates');
		
		//Copy Settings module templates files, if any
		if (is_dir("layouts/".Vtiger_Viewer::getDefaultLayoutName()."/modules/Settings/$module"))
			$zip->copyDirectoryFromDisk("layouts/".Vtiger_Viewer::getDefaultLayoutName()."/modules/Settings/$module", "settings/templates");
		if (is_dir("layouts/".vglobal('defaultLayout')."/modules/Settings/$module"))
			$zip->copyDirectoryFromDisk("layouts/".vglobal('defaultLayout')."/modules/Settings/$module", "settings/templates");
		
		//Support to multiple layouts of module
		$layoutDirectories = glob('layouts' . '/*', GLOB_ONLYDIR);

		foreach ($layoutDirectories as $key => $layoutName) {
			$moduleLayout = $layoutName . "/modules/$module";
			if (is_dir($moduleLayout)) {
				$zip->copyDirectoryFromDisk($moduleLayout, $moduleLayout);
			}

			$settingsLayout = $layoutName . "/modules/Settings/$module";
			if (is_dir($settingsLayout)) {
				$zip->copyDirectoryFromDisk($settingsLayout, $settingsLayout);
			}
		}

		//Copy language files
		$this->__copyLanguageFiles($zip, $module);

		//Copy image file
		if (file_exists("layouts/".Vtiger_Viewer::getDefaultLayoutName()."/skins/images/$module.png")) {
			$zip->copyFileFromDisk("layouts/".Vtiger_Viewer::getDefaultLayoutName()."/skins/images", "", "$module.png");
		}
		if (file_exists("layouts/".vglobal('defaultLayout')."/skins/images/$module.png")) {
			$zip->copyFileFromDisk("layouts/".vglobal('defaultLayout')."/skins/images", "", "$module.png");
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
	 * @param <Vtiger_Zip> $zip
	 * @param <String> $module
	 */
	function __copyLanguageFiles($zip, $module)
	{
		$languageFolder = "languages";
		if ($dir = @opendir($languageFolder)) {  // open languages folder
			while (($langName = readdir($dir)) !== false) {
				if ($langName != ".." && $langName != "." && is_dir($languageFolder . "/" . $langName)) {
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
	function export_Dependencies($moduleInstance)
	{
		global $YetiForce_current_version, $adb;
		$moduleid = $moduleInstance->id;

		$sqlresult = $adb->pquery("SELECT * FROM vtiger_tab_info WHERE tabid = ?", array($moduleid));
		$vtigerMinVersion = $YetiForce_current_version;
		$vtigerMaxVersion = false;
		$noOfPreferences = $adb->num_rows($sqlresult);
		for ($i = 0; $i < $noOfPreferences; ++$i) {
			$prefName = $adb->query_result($sqlresult, $i, 'prefname');
			$prefValue = $adb->query_result($sqlresult, $i, 'prefvalue');
			if ($prefName == 'vtiger_min_version') {
				$vtigerMinVersion = $prefValue;
			}
			if ($prefName == 'vtiger_max_version') {
				$vtigerMaxVersion = $prefValue;
			}
		}

		$this->openNode('dependencies');
		$this->outputNode($vtigerMinVersion, 'vtiger_version');
		if ($vtigerMaxVersion !== false)
			$this->outputNode($vtigerMaxVersion, 'vtiger_max_version');
		$this->closeNode('dependencies');
	}

	/**
	 * Export Module Handler
	 * @access private
	 */
	function export_Module($moduleInstance)
	{
		$adb = PearDatabase::getInstance();

		$moduleid = $moduleInstance->id;

		$sqlresult = $adb->pquery("SELECT * FROM vtiger_tab WHERE tabid = ?", array($moduleid));
		$tabresultrow = $adb->fetch_array($sqlresult);

		$tabname = $tabresultrow['name'];
		$tablabel = $tabresultrow['tablabel'];
		$tabversion = isset($tabresultrow['version']) ? $tabresultrow['version'] : false;

		$this->openNode('module');
		$this->outputNode(date('Y-m-d H:i:s'), 'exporttime');
		$this->outputNode($tabname, 'name');
		$this->outputNode($tablabel, 'label');

		if (!$moduleInstance->isentitytype) {
			$this->outputNode('extension', 'type');
		}

		if ($tabversion) {
			$this->outputNode($tabversion, 'version');
		}

		// Export dependency information
		$this->export_Dependencies($moduleInstance);

		// Export module tables
		$this->export_Tables($moduleInstance);

		// Export module blocks
		$this->export_Blocks($moduleInstance);

		// Export module filters
		$this->export_CustomViews($moduleInstance);

		// Export Sharing Access
		$this->export_SharingAccess($moduleInstance);

		// Export Events
		$this->export_Events($moduleInstance);

		// Export Actions
		$this->export_Actions($moduleInstance);

		// Export Related Lists
		$this->export_RelatedLists($moduleInstance);

		// Export Custom Links
		$this->export_CustomLinks($moduleInstance);

		//Export cronTasks
		$this->export_CronTasks($moduleInstance);

		$this->closeNode('module');
	}

	/**
	 * Export module base and related tables
	 * @access private
	 */
	function export_Tables($moduleInstance)
	{

		$_exportedTables = Array();

		$modulename = $moduleInstance->name;

		$this->openNode('tables');

		if ($moduleInstance->isentitytype) {
			$focus = CRMEntity::getInstance($modulename);

			// Setup required module variables which is need for vtlib API's
			vtlib_setup_modulevars($modulename, $focus);

			$tables = Array($focus->table_name);
			if (!empty($focus->groupTable))
				$tables[] = $focus->groupTable[0];
			if (!empty($focus->customFieldTable))
				$tables[] = $focus->customFieldTable[0];

			foreach ($tables as $table) {
				$this->openNode('table');
				$this->outputNode($table, 'name');
				$this->outputNode('<![CDATA[' . Vtiger_Utils::CreateTableSql($table) . ']]>', 'sql');
				$this->closeNode('table');

				$_exportedTables[] = $table;
			}
		}

		// Now export table information recorded in schema file
		if (file_exists("modules/$modulename/schema.xml")) {
			$schema = simplexml_load_file("modules/$modulename/schema.xml");

			if (!empty($schema->tables) && !empty($schema->tables->table)) {
				foreach ($schema->tables->table as $tablenode) {
					$table = trim($tablenode->name);
					if (!in_array($table, $_exportedTables)) {
						$this->openNode('table');
						$this->outputNode($table, 'name');
						$this->outputNode('<![CDATA[' . Vtiger_Utils::CreateTableSql($table) . ']]>', 'sql');
						$this->closeNode('table');

						$_exportedTables[] = $table;
					}
				}
			}
		}
		$this->closeNode('tables');
	}

	/**
	 * Export module blocks with its related fields
	 * @access private
	 */
	function export_Blocks($moduleInstance)
	{
		$adb = PearDatabase::getInstance();
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
	function export_Fields($moduleInstance, $blockid)
	{
		$adb = PearDatabase::getInstance();

		$fieldresult = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid=? AND block=?", Array($moduleInstance->id, $blockid));
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

			$info_schema = $adb->pquery("SELECT column_name, column_type FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = SCHEMA() AND table_name = ? AND column_name = ?", Array($fieldresultrow['tablename'], $fieldresultrow['columnname']));
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
					for ($i = 0; $i < $adb->num_rows($treesData); $i++) {
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
	function export_CustomViews($moduleInstance)
	{
		$adb = PearDatabase::getInstance();

		$customviewres = $adb->pquery("SELECT * FROM vtiger_customview WHERE entitytype = ?", Array($moduleInstance->name));
		$customviewcount = $adb->num_rows($customviewres);

		if (empty($customviewcount))
			return;

		$this->openNode('customviews');
		for ($cvindex = 0; $cvindex < $customviewcount; ++$cvindex) {

			$cvid = $adb->query_result($customviewres, $cvindex, 'cvid');

			$cvcolumnres = $adb->pquery("SELECT * FROM vtiger_cvcolumnlist WHERE cvid=?", array($cvid));
			$cvcolumncount = $adb->num_rows($cvcolumnres);

			$this->openNode('customview');

			$setdefault = $adb->query_result($customviewres, $cvindex, 'setdefault');
			$setdefault = ($setdefault == 1) ? 'true' : 'false';

			$setmetrics = $adb->query_result($customviewres, $cvindex, 'setmetrics');
			$setmetrics = ($setmetrics == 1) ? 'true' : 'false';

			$this->outputNode($adb->query_result($customviewres, $cvindex, 'viewname'), 'viewname');
			$this->outputNode($setdefault, 'setdefault');
			$this->outputNode($setmetrics, 'setmetrics');

			$this->openNode('fields');
			for ($index = 0; $index < $cvcolumncount; ++$index) {
				$cvcolumnindex = $adb->query_result($cvcolumnres, $index, 'columnindex');
				$cvcolumnname = $adb->query_result($cvcolumnres, $index, 'columnname');
				$cvcolumnnames = explode(':', $cvcolumnname);
				$cvfieldname = $cvcolumnnames[2];

				$this->openNode('field');
				$this->outputNode($cvfieldname, 'fieldname');
				$this->outputNode($cvcolumnindex, 'columnindex');

				$cvcolumnruleres = $adb->pquery("SELECT * FROM vtiger_cvadvfilter WHERE cvid=? AND columnname=?", Array($cvid, $cvcolumnname));
				$cvcolumnrulecount = $adb->num_rows($cvcolumnruleres);

				if ($cvcolumnrulecount) {
					$this->openNode('rules');
					for ($rindex = 0; $rindex < $cvcolumnrulecount; ++$rindex) {
						$cvcolumnruleindex = $adb->query_result($cvcolumnruleres, $rindex, 'columnindex');
						$cvcolumnrulecomp = $adb->query_result($cvcolumnruleres, $rindex, 'comparator');
						$cvcolumnrulevalue = $adb->query_result($cvcolumnruleres, $rindex, 'value');
						$cvcolumnrulecomp = Vtiger_Filter::translateComparator($cvcolumnrulecomp, true);

						$this->openNode('rule');
						$this->outputNode($cvcolumnruleindex, 'columnindex');
						$this->outputNode($cvcolumnrulecomp, 'comparator');
						$this->outputNode($cvcolumnrulevalue, 'value');
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
	function export_SharingAccess($moduleInstance)
	{
		$adb = PearDatabase::getInstance();

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
	function export_Events($moduleInstance)
	{
		$events = Vtiger_Event::getAll($moduleInstance);
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

	/**
	 * Export actions (tools) associated with module.
	 * TODO: Need to pickup values based on status for all user (profile)
	 * @access private
	 */
	function export_Actions($moduleInstance)
	{

		if (!$moduleInstance->isentitytype)
			return;

		$adb = PearDatabase::getInstance();
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
	function export_RelatedLists($moduleInstance)
	{

		if (!$moduleInstance->isentitytype)
			return;

		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT * FROM vtiger_relatedlists WHERE tabid = ?", Array($moduleInstance->id));
		if ($adb->num_rows($result)) {
			$this->openNode('relatedlists');

			for ($index = 0; $index < $adb->num_rows($result); ++$index) {
				$row = $adb->fetch_array($result);
				$this->openNode('relatedlist');

				$relModuleInstance = Vtiger_Module::getInstance($row['related_tabid']);
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

			for ($index = 0; $index < $adb->num_rows($result); ++$index) {
				$row = $adb->fetch_array($result);
				$this->openNode('inrelatedlist');

				$relModuleInstance = Vtiger_Module::getInstance($row['tabid']);
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
	function export_CustomLinks($moduleInstance)
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
	function export_CronTasks($moduleInstance)
	{
		$cronTasks = Vtiger_Cron::listAllInstancesByModule($moduleInstance->name);
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
		Vtiger_Utils::Log($message, $delim);
	}
}

?>
