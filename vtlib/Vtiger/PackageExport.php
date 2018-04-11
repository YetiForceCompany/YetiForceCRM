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
 */
class PackageExport
{
	public $_export_tmpdir = 'cache/vtlib';
	public $_export_modulexml_filename;
	public $_export_modulexml_file;
	protected $moduleInstance = false;
	private $zipFileName;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if (is_dir($this->_export_tmpdir) === false) {
			mkdir($this->_export_tmpdir, 0755);
		}
	}

	/** Output Handlers */
	public function openNode($node, $delimiter = PHP_EOL)
	{
		$this->__write("<$node>$delimiter");
	}

	public function closeNode($node, $delimiter = PHP_EOL)
	{
		$this->__write("</$node>$delimiter");
	}

	public function outputNode($value, $node = '')
	{
		if ($node != '') {
			$this->openNode($node, '');
		}
		$this->__write($value);
		if ($node != '') {
			$this->closeNode($node);
		}
	}

	public function __write($value)
	{
		fwrite($this->_export_modulexml_file, $value);
	}

	/**
	 * Set the module.xml file path for this export and
	 * return its temporary path.
	 */
	public function __getManifestFilePath()
	{
		if (empty($this->_export_modulexml_filename)) {
			// Set the module xml filename to be written for exporting.
			$this->_export_modulexml_filename = 'manifest-' . time() . '.xml';
		}

		return "$this->_export_tmpdir/$this->_export_modulexml_filename";
	}

	/**
	 * Initialize Export.
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
	 */
	public function __cleanupExport()
	{
		if (!empty($this->_export_modulexml_filename)) {
			unlink($this->__getManifestFilePath());
		}
	}

	/**
	 * Get last name of zip file.
	 *
	 * @return string
	 */
	public function getZipFileName()
	{
		return $this->zipFileName;
	}

	/**
	 * Export Module as a zip file.
	 *
	 * @param Module Instance of module
	 * @param Path Output directory path
	 * @param string Zipfilename to use
	 * @param bool True for sending the output as download
	 */
	public function export(\vtlib\Module $moduleInstance, $todir = '', $zipFileName = '', $directDownload = false)
	{
		$this->zipFileName = $zipFileName;
		$this->moduleInstance = $moduleInstance;
		$module = $this->moduleInstance->name;
		$this->__initExport($module);

		// Call module export function
		$this->exportModule();
		$this->__finishExport();

		// Export as Zip
		if (empty($this->zipFileName)) {
			$this->zipFileName = $this->moduleInstance->name . '_' . date('Y-m-d-Hi') . '_' . $this->moduleInstance->version . '.zip';
			$this->zipFileName = $this->_export_tmpdir . '/' . $this->zipFileName;
		}

		if (file_exists($this->zipFileName)) {
			throw new \Exception('File already exists: ' . $this->zipFileName);
		}
		$zip = \App\Zip::createFile($this->zipFileName);
		// Add manifest file
		$zip->addFile($this->__getManifestFilePath(), 'manifest.xml');
		// Copy module directory
		$zip->addDirectory("modules/$module");
		// Copy Settings/module directory
		if (is_dir("modules/Settings/$module")) {
			$zip->addDirectory("modules/Settings/$module", 'settings');
		}
		// Copy cron files of the module (if any)
		if (is_dir("cron/modules/$module")) {
			$zip->addDirectory("cron/modules/$module", 'cron');
		}
		//Copy module templates files
		if (is_dir('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . '/modules/' . $module)) {
			$zip->addDirectory('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . '/modules/' . $module, 'templates');
		}
		//Copy Settings module templates files, if any
		if (is_dir('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/Settings/$module")) {
			$zip->addDirectory('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/Settings/$module", 'settings/templates');
		}
		//Support to multiple layouts of module
		$layoutDirectories = glob('layouts' . '/*', GLOB_ONLYDIR);
		foreach ($layoutDirectories as $key => $layoutName) {
			if ($layoutName != 'layouts/' . \Vtiger_Viewer::getDefaultLayoutName()) {
				$moduleLayout = $layoutName . "/modules/$module";
				if (is_dir($moduleLayout)) {
					$zip->addDirectory($moduleLayout, $moduleLayout);
				}
				$settingsLayout = $layoutName . "/modules/Settings/$module";
				if (is_dir($settingsLayout)) {
					$zip->addDirectory($settingsLayout, $settingsLayout);
				}
			}
		}
		//Copy language files
		$this->__copyLanguageFiles($zip, $module);
		//Copy image file
		if (file_exists('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/skins/images/$module.png")) {
			$zip->addFile('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/skins/images/$module.png", "$module.png");
		}
		// Copy config files
		if (file_exists("config/modules/$module.php")) {
			$zip->addFile("config/modules/$module.php", "config/$module.php");
		}
		if ($directDownload) {
			$zip->download();
		} else {
			$zip->close();
			if ($todir) {
				copy($this->zipFileName, $todir);
			}
		}
		$this->__cleanupExport();
	}

	/**
	 * Function copies language files to zip.
	 *
	 * @param \App\Zip $zip
	 * @param string   $module
	 */
	public function __copyLanguageFiles(\App\Zip $zip, $module)
	{
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator('languages', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			// @var $item \SplFileInfo
			if ($item->isFile() && $item->getFilename() === $module . '.json') {
				$zip->addFile($item->getPath() . DIRECTORY_SEPARATOR . $item->getFilename());
			}
		}
	}

	/**
	 * Export vtiger dependencies.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportDependencies(ModuleBasic $moduleInstance)
	{
		$adb = \PearDatabase::getInstance();
		$moduleId = $moduleInstance->id;

		$sqlResult = $adb->pquery('SELECT * FROM vtiger_tab_info WHERE tabid = ?', [$moduleId]);
		$minVersion = \App\Version::get();
		$maxVersion = false;
		$noOfPreferences = $adb->numRows($sqlResult);
		for ($i = 0; $i < $noOfPreferences; ++$i) {
			$prefName = $adb->queryResult($sqlResult, $i, 'prefname');
			$prefValue = $adb->queryResult($sqlResult, $i, 'prefvalue');
			if ($prefName == 'vtiger_min_version') {
				$minVersion = $prefValue;
			}
			if ($prefName == 'vtiger_max_version') {
				$maxVersion = $prefValue;
			}
		}
		$this->openNode('dependencies');
		$this->outputNode($minVersion, 'vtiger_version');
		if ($maxVersion !== false) {
			$this->outputNode($maxVersion, 'vtiger_max_version');
		}
		$this->closeNode('dependencies');
	}

	/**
	 * Export Module Handler.
	 */
	public function exportModule()
	{
		$adb = \PearDatabase::getInstance();

		$moduleId = $this->moduleInstance->id;

		$sqlresult = $adb->pquery('SELECT * FROM vtiger_tab WHERE tabid = ?', [$moduleId]);
		$tabInfo = $adb->getRow($sqlresult);

		$tabname = $tabInfo['name'];
		$tablabel = $tabInfo['tablabel'];
		$tabVersion = $tabInfo['version'] ?? false;

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
		$this->exportDependencies($this->moduleInstance);

		// Export module tables
		$this->exportTables();

		// Export module blocks
		$this->exportBlocks($this->moduleInstance);

		// Export module filters
		$this->exportCustomViews($this->moduleInstance);

		// Export module inventory fields
		if ($tabInfo['type'] == 1) {
			$this->exportInventory();
		}

		// Export Sharing Access
		$this->exportSharingAccess($this->moduleInstance);

		// Export Actions
		$this->exportActions($this->moduleInstance);

		// Export Related Lists
		$this->exportRelatedLists($this->moduleInstance);

		// Export Custom Links
		$this->exportCustomLinks($this->moduleInstance);

		//Export cronTasks
		$this->exportCronTasks($this->moduleInstance);

		$this->closeNode('module');
	}

	/**
	 * Export module base and related tables.
	 */
	public function exportTables()
	{
		$modulename = $this->moduleInstance->name;
		$this->openNode('tables');

		if ($this->moduleInstance->isentitytype) {
			$focus = \CRMEntity::getInstance($modulename);

			// Setup required module variables which is need for vtlib API's
			\VtlibUtils::vtlibSetupModulevars($modulename, $focus);
			$tables = $focus->tab_name;
			if (($key = array_search('vtiger_crmentity', $tables)) !== false) {
				unset($tables[$key]);
			}
			foreach ($tables as $table) {
				$this->openNode('table');
				$this->outputNode($table, 'name');
				$this->outputNode('<![CDATA[' . Utils::createTableSql($table) . ']]>', 'sql');
				$this->closeNode('table');
			}
		}
		$this->closeNode('tables');
	}

	/**
	 * Export module blocks with its related fields.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportBlocks(ModuleBasic $moduleInstance)
	{
		$adb = \PearDatabase::getInstance();
		$sqlresult = $adb->pquery('SELECT * FROM vtiger_blocks WHERE tabid = ? order by sequence', [$moduleInstance->id]);
		$resultrows = $adb->numRows($sqlresult);

		if (empty($resultrows)) {
			return;
		}

		$this->openNode('blocks');
		for ($index = 0; $index < $resultrows; ++$index) {
			$blockid = $adb->queryResult($sqlresult, $index, 'blockid');
			$blocklabel = $adb->queryResult($sqlresult, $index, 'blocklabel');
			$block_sequence = $adb->queryResult($sqlresult, $index, 'sequence');
			$block_show_title = $adb->queryResult($sqlresult, $index, 'show_title');
			$block_visible = $adb->queryResult($sqlresult, $index, 'visible');
			$block_create_view = $adb->queryResult($sqlresult, $index, 'create_view');
			$block_edit_view = $adb->queryResult($sqlresult, $index, 'edit_view');
			$block_detail_view = $adb->queryResult($sqlresult, $index, 'detail_view');
			$block_display_status = $adb->queryResult($sqlresult, $index, 'display_status');
			$block_iscustom = $adb->queryResult($sqlresult, $index, 'iscustom');
			$block_islist = $adb->queryResult($sqlresult, $index, 'islist');

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
			$this->exportFields($moduleInstance, $blockid);
			$this->closeNode('block');
		}
		$this->closeNode('blocks');
	}

	/**
	 * Export fields related to a module block.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportFields(ModuleBasic $moduleInstance, $blockid)
	{
		$adb = \PearDatabase::getInstance();

		$fieldresult = $adb->pquery('SELECT * FROM vtiger_field WHERE tabid=? && block=?', [$moduleInstance->id, $blockid]);
		$fieldcount = $adb->numRows($fieldresult);

		if (empty($fieldcount)) {
			return;
		}

		$entityresult = $adb->pquery('SELECT * FROM vtiger_entityname WHERE tabid=?', [$moduleInstance->id]);
		$entity_fieldname = $adb->queryResult($entityresult, 0, 'fieldname');

		$this->openNode('fields');
		for ($index = 0; $index < $fieldcount; ++$index) {
			$this->openNode('field');
			$fieldresultrow = $adb->fetchByAssoc($fieldresult);

			$fieldname = $fieldresultrow['fieldname'];
			$uitype = $fieldresultrow['uitype'];
			$fieldid = $fieldresultrow['fieldid'];

			$info_schema = $adb->pquery('SELECT column_name, column_type FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = SCHEMA() && table_name = ? && column_name = ?', [$fieldresultrow['tablename'], $fieldresultrow['columnname']]);
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
				$this->outputNode($adb->queryResult($entityresult, 0, 'entityidfield'), 'entityidfield');
				$this->outputNode($adb->queryResult($entityresult, 0, 'entityidcolumn'), 'entityidcolumn');
				$this->closeNode('entityidentifier');
			}

			// Export picklist values for picklist fields
			if ($uitype == '15' || $uitype == '16' || $uitype == '111' || $uitype == '33' || $uitype == '55') {
				$this->openNode('picklistvalues');
				foreach (\App\Fields\Picklist::getValuesName($fieldname) as $picklistvalue) {
					$this->outputNode($picklistvalue, 'picklistvalue');
				}
				$this->closeNode('picklistvalues');
			}

			// Export field to module relations
			if ($uitype == '10') {
				$relatedmodres = $adb->pquery('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=?', [$fieldid]);
				$relatedmodcount = $adb->numRows($relatedmodres);
				if ($relatedmodcount) {
					$this->openNode('relatedmodules');
					for ($relmodidx = 0; $relmodidx < $relatedmodcount; ++$relmodidx) {
						$this->outputNode($adb->queryResult($relatedmodres, $relmodidx, 'relmodule'), 'relatedmodule');
					}
					$this->closeNode('relatedmodules');
				}
			}
			if ($uitype == '302') {
				$this->outputNode('', 'fieldparams');
				$this->openNode('tree_template');
				$trees = $adb->pquery('SELECT * FROM vtiger_trees_templates WHERE templateid=?;', [$fieldresultrow['fieldparams']]);
				if ($adb->numRows($trees) > 0) {
					$this->outputNode($adb->queryResultRaw($trees, 0, 'name'), 'name');
					$this->outputNode($adb->queryResultRaw($trees, 0, 'access'), 'access');
					$treesData = $adb->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid=?;', [$fieldresultrow['fieldparams']]);
					$this->openNode('tree_values');
					$countTreesData = $adb->numRows($treesData);
					for ($i = 0; $i < $countTreesData; ++$i) {
						$this->openNode('tree_value');
						$this->outputNode($adb->queryResultRaw($treesData, $i, 'name'), 'name');
						$this->outputNode($adb->queryResultRaw($treesData, $i, 'tree'), 'tree');
						$this->outputNode($adb->queryResultRaw($treesData, $i, 'parenttrre'), 'parenttrre');
						$this->outputNode($adb->queryResultRaw($treesData, $i, 'depth'), 'depth');
						$this->outputNode($adb->queryResultRaw($treesData, $i, 'label'), 'label');
						$this->outputNode($adb->queryResultRaw($treesData, $i, 'state'), 'state');
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
	 * Export Custom views of the module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportCustomViews(ModuleBasic $moduleInstance)
	{
		$db = \PearDatabase::getInstance();

		$customviewres = $db->pquery('SELECT * FROM vtiger_customview WHERE entitytype = ?', [$moduleInstance->name]);
		if (!$customviewres->rowCount()) {
			return;
		}

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
			$cvcolumnres = $db->pquery('SELECT * FROM vtiger_cvcolumnlist WHERE cvid=?', [$cvid]);
			while ($cvRow = $db->getRow($cvcolumnres)) {
				$cvColumnNames = explode(':', $cvRow['columnname']);

				$this->openNode('field');
				$this->outputNode($cvColumnNames[2], 'fieldname');
				$this->outputNode($cvRow['columnindex'], 'columnindex');

				$cvcolumnruleres = $db->pquery('SELECT * FROM vtiger_cvadvfilter WHERE cvid=? && columnname=?', [$cvid, $cvRow['columnname']]);
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
	 * Export Sharing Access of the module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportSharingAccess(ModuleBasic $moduleInstance)
	{
		$adb = \PearDatabase::getInstance();

		$deforgshare = $adb->pquery('SELECT * FROM vtiger_def_org_share WHERE tabid=?', [$moduleInstance->id]);
		$deforgshareCount = $adb->numRows($deforgshare);

		if (empty($deforgshareCount)) {
			return;
		}

		$this->openNode('sharingaccess');
		if ($deforgshareCount) {
			for ($index = 0; $index < $deforgshareCount; ++$index) {
				$permission = $adb->queryResult($deforgshare, $index, 'permission');
				$permissiontext = '';
				if ($permission == '0') {
					$permissiontext = 'public_readonly';
				}
				if ($permission == '1') {
					$permissiontext = 'public_readwrite';
				}
				if ($permission == '2') {
					$permissiontext = 'public_readwritedelete';
				}
				if ($permission == '3') {
					$permissiontext = 'private';
				}

				$this->outputNode($permissiontext, 'default');
			}
		}
		$this->closeNode('sharingaccess');
	}

	/**
	 * Export actions.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportActions(ModuleBasic $moduleInstance)
	{
		if (!$moduleInstance->isentitytype) {
			return;
		}

		$adb = \PearDatabase::getInstance();
		$result = $adb->pquery('SELECT distinct(actionname) FROM vtiger_profile2utility, vtiger_actionmapping
			WHERE vtiger_profile2utility.activityid=vtiger_actionmapping.actionid and tabid=?', [$moduleInstance->id]);

		if ($adb->numRows($result)) {
			$this->openNode('actions');
			while ($resultrow = $adb->fetchArray($result)) {
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
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportRelatedLists(ModuleBasic $moduleInstance)
	{
		if (!$moduleInstance->isentitytype) {
			return;
		}

		$adb = \PearDatabase::getInstance();
		$result = $adb->pquery('SELECT * FROM vtiger_relatedlists WHERE tabid = ?', [$moduleInstance->id]);
		if ($adb->numRows($result)) {
			$this->openNode('relatedlists');

			$countResult = $adb->numRows($result);
			for ($index = 0; $index < $countResult; ++$index) {
				$row = $adb->fetchArray($result);
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
		$result = $adb->pquery('SELECT * FROM vtiger_relatedlists WHERE related_tabid = ?', [$moduleInstance->id]);
		if ($adb->numRows($result)) {
			$this->openNode('inrelatedlists');

			$countResult = $adb->numRows($result);
			for ($index = 0; $index < $countResult; ++$index) {
				$row = $adb->fetchArray($result);
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
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportCustomLinks(ModuleBasic $moduleInstance)
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
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportCronTasks(ModuleBasic $moduleInstance)
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
	 * Export module inventory fields.
	 */
	public function exportInventory()
	{
		$db = \PearDatabase::getInstance();
		$inventoryFieldModel = \Vtiger_InventoryField_Model::getInstance($this->moduleInstance->name);
		$tableName = $inventoryFieldModel->getTableName('fields');

		$result = $db->query(sprintf('SELECT * FROM %s', $tableName));
		if ($db->getRowCount($result) == 0) {
			return false;
		}

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
