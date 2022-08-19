<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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
	private $openNode = 0;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if (false === is_dir($this->_export_tmpdir)) {
			mkdir($this->_export_tmpdir, 0755);
		}
	}

	/** Output Handlers */
	public function openNode($node, $delimiter = PHP_EOL)
	{
		$pre = '';
		if ('' === $delimiter || PHP_EOL === $delimiter) {
			$pre = str_repeat("\t", $this->openNode);
		}
		$this->__write($pre . "<$node>$delimiter");
		++$this->openNode;
	}

	public function closeNode($node, $delimiter = PHP_EOL, $space = true)
	{
		--$this->openNode;
		$pre = '';
		if ($space) {
			$pre = str_repeat("\t", $this->openNode);
		}
		$this->__write($pre . "</$node>$delimiter");
	}

	public function outputNode($value, $node = '')
	{
		if ('' != $node) {
			$this->openNode($node, '');
		}
		$this->__write($value);
		if ('' != $node) {
			$this->closeNode($node, PHP_EOL, false);
		}
	}

	public function __write($value)
	{
		fwrite($this->_export_modulexml_file, $value ?? '');
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
	 *
	 * @param mixed $module
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
	 * @param \vtlib\Module $moduleInstance Instance of module
	 * @param string        $todir          Output directory path
	 * @param string        $zipFileName    Zipfilename to use
	 * @param bool          $directDownload True for sending the output as download
	 */
	public function export(Module $moduleInstance, $todir = '', $zipFileName = '', $directDownload = false)
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
			throw new \App\Exceptions\AppException('File already exists: ' . $this->zipFileName);
		}
		$zip = \App\Zip::createFile($this->zipFileName);
		// Add manifest file
		$zip->addFile($this->__getManifestFilePath(), 'manifest.xml');
		// Copy module directory
		$zip->addDirectory("modules/$module");
		// Copy Settings/module directory
		if (is_dir("modules/Settings/$module")) {
			$zip->addDirectory(
				"modules/Settings/{$module}",
				'settings/modules',
				true
			);
		}
		// Copy cron files of the module (if any)
		if (is_dir("cron/modules/{$module}")) {
			$zip->addDirectory("cron/modules/{$module}", 'cron', true);
		}
		//Copy module templates files
		if (is_dir('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/{$module}")) {
			$zip->addDirectory(
				'layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/{$module}",
				'templates',
				true
			);
		}
		//Copy Settings module templates files, if any
		if (is_dir('layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/Settings/{$module}")) {
			$zip->addDirectory(
				'layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/Settings/{$module}",
				'settings/templates',
				true
			);
		}
		//Copy module public resources files
		if (is_dir('public_html/layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/{$module}/resources")) {
			$zip->addDirectory(
				'public_html/layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/{$module}/resources",
				'public_resources',
				true
			);
		}
		//Copy module public Settings resources files
		if (is_dir('public_html/layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/Settings/{$module}/resources")) {
			$zip->addDirectory(
				'public_html/layouts/' . \Vtiger_Viewer::getDefaultLayoutName() . "/modules/Settings/{$module}/resources",
				'settings/public_resources',
				true
			);
		}
		//Support to multiple layouts of module
		$layoutDirectories = glob('layouts' . '/*', GLOB_ONLYDIR);
		foreach ($layoutDirectories as $layoutName) {
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
		if (file_exists("config/Modules/$module.php")) {
			$zip->addFile("config/Modules/$module.php", "config/$module.php");
		}
		if ($directDownload) {
			$zip->download($module);
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
			if ($item->isFile() && $item->getFilename() === $module . '.json') {
				$filePath = $item->getRealPath();
				$zipPath = str_replace(\DIRECTORY_SEPARATOR, '/', $item->getPath() . \DIRECTORY_SEPARATOR . $item->getFilename());
				$zip->addFile($filePath, $zipPath);
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
		$moduleId = $moduleInstance->id;
		$minVersion = \App\Version::get();
		$maxVersion = false;
		$dataReader = (new \App\Db\Query())->from('vtiger_tab_info')->where(['tabid' => $moduleId])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$prefName = $row['prefname'];
			$prefValue = $row['prefvalue'];
			if ('vtiger_min_version' == $prefName) {
				$minVersion = $prefValue;
			}
			if ('vtiger_max_version' == $prefName) {
				$maxVersion = $prefValue;
			}
		}
		$dataReader->close();

		$this->openNode('dependencies');
		$this->outputNode($minVersion, 'vtiger_version');
		if (false !== $maxVersion) {
			$this->outputNode($maxVersion, 'vtiger_max_version');
		}
		$this->closeNode('dependencies');
	}

	/**
	 * Export Module Handler.
	 */
	public function exportModule()
	{
		$moduleId = $this->moduleInstance->id;
		$row = (new \App\Db\Query())
			->select(['name', 'tablabel', 'version', 'type', 'premium'])
			->from('vtiger_tab')
			->where(['tabid' => $moduleId])
			->one();
		$tabVersion = $row['version'] ?? false;
		$tabType = $row['type'];
		$this->openNode('module');
		$this->outputNode(date('Y-m-d H:i:s'), 'exporttime');
		$this->outputNode($row['name'], 'name');
		$this->outputNode($row['tablabel'], 'label');
		$this->outputNode($row['premium'], 'premium');

		if (!$this->moduleInstance->isentitytype) {
			$type = 'extension';
		} elseif (1 == $tabType) {
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
		if (1 == $tabType) {
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
			if (false !== ($key = array_search('vtiger_crmentity', $tables))) {
				unset($tables[$key]);
			}
			foreach ($tables as $table) {
				$createTable = \App\Db::getInstance()->createCommand('SHOW CREATE TABLE ' . $table)->queryOne();
				$this->openNode('table');
				$this->outputNode($table, 'name');
				$this->outputNode('<![CDATA[' . \App\Purifier::decodeHtml($createTable['Create Table']) . ']]>', 'sql');
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
		$dataReader = (new \App\Db\Query())->from('vtiger_blocks')->where(['tabid' => $moduleInstance->id])->orderBy(['sequence' => SORT_ASC])->createCommand()->query();
		if (0 === $dataReader->count()) {
			return;
		}
		$this->openNode('blocks');
		while ($row = $dataReader->read()) {
			$this->openNode('block');
			$this->outputNode($row['blocklabel'], 'blocklabel');
			$this->outputNode($row['sequence'], 'sequence');
			$this->outputNode($row['show_title'], 'show_title');
			$this->outputNode($row['visible'], 'visible');
			$this->outputNode($row['create_view'], 'create_view');
			$this->outputNode($row['edit_view'], 'edit_view');
			$this->outputNode($row['detail_view'], 'detail_view');
			$this->outputNode($row['display_status'], 'display_status');
			$this->outputNode($row['iscustom'], 'iscustom');
			// Export fields associated with the block
			$this->exportFields($moduleInstance, $row['blockid']);
			$this->closeNode('block');
		}
		$dataReader->close();
		$this->closeNode('blocks');
	}

	/**
	 * Export fields related to a module block.
	 *
	 * @param ModuleBasic $moduleInstance
	 * @param int         $blockid
	 */
	public function exportFields(ModuleBasic $moduleInstance, $blockid)
	{
		$dataReader = (new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $moduleInstance->id, 'block' => $blockid])->createCommand()->query();
		if (0 === $dataReader->count()) {
			return;
		}
		$entityField = (new \App\Db\Query())->select(['fieldname', 'entityidfield', 'entityidcolumn'])->from('vtiger_entityname')->where(['tabid' => $moduleInstance->id])->one();
		$this->openNode('fields');
		while ($row = $dataReader->read()) {
			$this->openNode('field');
			$fieldname = $row['fieldname'];
			$uiType = $row['uitype'];
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			$fieldParams = $row['fieldparams'];
			$infoSchema = \App\Db::getInstance()->getTableSchema($tableName);
			$this->outputNode($fieldname, 'fieldname');
			$this->outputNode($uiType, 'uitype');
			$this->outputNode($columnName, 'columnname');
			$this->outputNode($infoSchema->columns[$columnName]->dbType, 'columntype');
			$this->outputNode($tableName, 'tablename');
			$this->outputNode($row['generatedtype'], 'generatedtype');
			$this->outputNode($row['fieldlabel'], 'fieldlabel');
			$this->outputNode($row['readonly'], 'readonly');
			$this->outputNode($row['presence'], 'presence');
			$this->outputNode($row['defaultvalue'], 'defaultvalue');
			$this->outputNode($row['sequence'], 'sequence');
			$this->outputNode($row['maximumlength'], 'maximumlength');
			$this->outputNode($row['typeofdata'], 'typeofdata');
			$this->outputNode($row['quickcreate'], 'quickcreate');
			$this->outputNode($row['quickcreatesequence'], 'quickcreatesequence');
			$this->outputNode($row['displaytype'], 'displaytype');
			$this->outputNode($row['info_type'], 'info_type');
			$this->outputNode($row['fieldparams'], 'fieldparams');
			$this->outputNode($row['helpinfo'], 'helpinfo');

			if (isset($row['masseditable'])) {
				$this->outputNode($row['masseditable'], 'masseditable');
			}
			if (isset($row['summaryfield'])) {
				$this->outputNode($row['summaryfield'], 'summaryfield');
			}
			// Export Entity Identifier Information
			if ($fieldname == $entityField['fieldname']) {
				$this->openNode('entityidentifier');
				$this->outputNode($entityField['entityidfield'], 'entityidfield');
				$this->outputNode($entityField['entityidcolumn'], 'entityidcolumn');
				$this->closeNode('entityidentifier');
			}
			// Export picklist values for picklist fields
			if ('15' == $uiType || '16' == $uiType || '111' == $uiType || '33' == $uiType || '55' == $uiType) {
				$this->openNode('picklistvalues');
				foreach (\App\Fields\Picklist::getValuesName($fieldname) as $picklistvalue) {
					$this->outputNode($picklistvalue, 'picklistvalue');
				}
				$this->closeNode('picklistvalues');
			}
			// Export field to module relations
			if ('10' == $uiType) {
				$fieldRelModule = (new \App\Db\Query())->select(['relmodule'])->from('vtiger_fieldmodulerel')->where(['fieldid' => $row['fieldid']])->column();
				if ($fieldRelModule) {
					$this->openNode('relatedmodules');
					foreach ($fieldRelModule as $row) {
						$this->outputNode($row, 'relatedmodule');
					}
					$this->closeNode('relatedmodules');
				}
			}
			if ('4' == $uiType) {
				$valueFieldNumber = \App\Fields\RecordNumber::getInstance($moduleInstance->id);
				$this->openNode('numberInfo');
				$this->outputNode($valueFieldNumber->get('prefix'), 'prefix');
				$this->outputNode($valueFieldNumber->get('leading_zeros'), 'leading_zeros');
				$this->outputNode($valueFieldNumber->get('postfix'), 'postfix');
				$this->outputNode($valueFieldNumber->get('start_id'), 'start_id');
				$this->outputNode($valueFieldNumber->get('cur_id'), 'cur_id');
				$this->outputNode($valueFieldNumber->get('reset_sequence'), 'reset_sequence');
				$this->outputNode($valueFieldNumber->get('cur_sequence'), 'cur_sequence');
				$this->closeNode('numberInfo');
			}
			if ('302' == $uiType) {
				$this->outputNode('', 'fieldparams');
				$this->openNode('tree_template');
				$treesExist = (new \App\Db\Query())->select(['name', 'access'])->from('vtiger_trees_templates')->where(['templateid' => $fieldParams])->one();
				if ($treesExist) {
					$this->outputNode($treesExist['name'], 'name');
					$this->outputNode($treesExist['access'], 'access');
					$dataReaderRow = (new \App\Db\Query())->from('vtiger_trees_templates_data')->where(['templateid' => $fieldParams])->createCommand()->query();
					$this->openNode('tree_values');
					while ($row = $dataReaderRow->read()) {
						$this->openNode('tree_value');
						$this->outputNode($row['name'], 'name');
						$this->outputNode($row['tree'], 'tree');
						$this->outputNode($row['parentTree'], 'parentTree');
						$this->outputNode($row['depth'], 'depth');
						$this->outputNode($row['label'], 'label');
						$this->outputNode($row['state'], 'state');
						$this->closeNode('tree_value');
					}
					$dataReaderRow->close();
					$this->closeNode('tree_values');
				}
				$this->closeNode('tree_template');
			}
			$this->closeNode('field');
		}
		$dataReader->close();
		$this->closeNode('fields');
	}

	/**
	 * Export Custom views of the module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportCustomViews(ModuleBasic $moduleInstance)
	{
		$customViewDataReader = (new \App\Db\Query())->from('vtiger_customview')->where(['entitytype' => $moduleInstance->name])
			->createCommand()->query();
		if (!$customViewDataReader->count()) {
			return;
		}
		$this->openNode('customviews');
		while ($row = $customViewDataReader->read()) {
			$setdefault = (1 == $row['setdefault']) ? 'true' : 'false';
			$setmetrics = (1 == $row['setmetrics']) ? 'true' : 'false';
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
			$cvColumnDataReader = (new \App\Db\Query())->from('vtiger_cvcolumnlist')->where(['cvid' => $cvid])->createCommand()->query();
			while ($cvRow = $cvColumnDataReader->read()) {
				$this->openNode('field');
				$this->outputNode($cvRow['field_name'], 'fieldname');
				$this->outputNode($cvRow['module_name'], 'modulename');
				if ($cvRow['source_field_name']) {
					$this->outputNode($cvRow['source_field_name'], 'sourcefieldname');
				}
				$this->outputNode($cvRow['columnindex'], 'columnindex');
				$this->closeNode('field');
			}
			$rules = \App\CustomView::getConditions($cvid);
			$this->closeNode('fields');
			if (!empty($rules)) {
				$this->outputNode('<![CDATA[' . \App\Json::encode($rules) . ']]>', 'rules');
			}
			$this->closeNode('customview');
		}
		$customViewDataReader->close();
		$this->closeNode('customviews');
	}

	/**
	 * Export Sharing Access of the module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function exportSharingAccess(ModuleBasic $moduleInstance)
	{
		$permission = (new \App\Db\Query())->select(['permission'])->from('vtiger_def_org_share')->where(['tabid' => $moduleInstance->id])->column();
		if (empty($permission)) {
			return;
		}
		$this->openNode('sharingaccess');
		if ($permission) {
			foreach ($permission as $row) {
				switch ($row) {
					case '0':
						$permissiontext = '';
						break;
					case '1':
						$permissiontext = 'public_readwrite';
						break;
					case '2':
						$permissiontext = 'public_readwritedelete';
						break;
					case '3':
						$permissiontext = 'private';
						break;
					default:
						break;
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
		$dataReader = (new \App\Db\Query())->select(['actionname'])->from('vtiger_profile2utility')->innerJoin('vtiger_actionmapping', 'vtiger_profile2utility.activityid = vtiger_actionmapping.actionid')->where(['tabid' => $moduleInstance->id])->distinct('actionname')->createCommand()->query();
		if ($dataReader->count()) {
			$this->openNode('actions');
			while ($row = $dataReader->read()) {
				$this->openNode('action');
				$this->outputNode('<![CDATA[' . $row['actionname'] . ']]>', 'name');
				$this->outputNode('enabled', 'status');
				$this->closeNode('action');
			}
			$dataReader->close();
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
		$moduleId = $moduleInstance->id;
		$dataReader = (new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => $moduleId])->createCommand()->query();
		if ($dataReader->count()) {
			$this->openNode('relatedlists');
			while ($row = $dataReader->read()) {
				$this->openNode('relatedlist');
				$this->outputNode(Module::getInstance($row['related_tabid'])->name, 'relatedmodule');
				$this->outputNode($row['name'], 'function');
				$this->outputNode($row['label'], 'label');
				$this->outputNode($row['sequence'], 'sequence');
				$this->outputNode($row['presence'], 'presence');
				$this->outputNode($row['favorites'], 'favorites');
				$this->outputNode($row['creator_detail'], 'creator_detail');
				$this->outputNode($row['relation_comment'], 'relation_comment');
				$this->outputNode($row['view_type'], 'view_type');
				$this->outputNode($row['field_name'], 'field_name');
				$actionText = $row['actions'];
				if (!empty($actionText)) {
					$this->openNode('actions');
					$actions = explode(',', $actionText);
					foreach ($actions as $action) {
						$this->outputNode($action, 'action');
					}
					$this->closeNode('actions');
				}
				if ($fields = \App\Field::getFieldsFromRelation($row['relation_id'])) {
					$this->openNode('fields');
					foreach ($fields as $field) {
						$this->outputNode($field, 'field');
					}
					$this->closeNode('fields');
				}
				$this->closeNode('relatedlist');
			}
			$dataReader->close();
			$this->closeNode('relatedlists');
		}

		// Relations in the opposite direction
		$dataReaderRow = (new \App\Db\Query())->from('vtiger_relatedlists')->where(['related_tabid' => $moduleId])->createCommand()->query();
		if ($dataReaderRow->count()) {
			$this->openNode('inrelatedlists');
			while ($row = $dataReaderRow->read()) {
				$this->openNode('inrelatedlist');
				$this->outputNode(Module::getInstance($row['tabid'])->name, 'inrelatedmodule');
				$this->outputNode($row['name'], 'function');
				$this->outputNode($row['label'], 'label');
				$this->outputNode($row['sequence'], 'sequence');
				$this->outputNode($row['presence'], 'presence');
				$this->outputNode($row['favorites'], 'favorites');
				$this->outputNode($row['creator_detail'], 'creator_detail');
				$this->outputNode($row['relation_comment'], 'relation_comment');
				$this->outputNode($row['view_type'], 'view_type');
				$this->outputNode($row['field_name'], 'field_name');
				$actionText = $row['actions'];
				if (!empty($actionText)) {
					$this->openNode('actions');
					$actions = explode(',', $actionText);
					foreach ($actions as $action) {
						$this->outputNode($action, 'action');
					}
					$this->closeNode('actions');
				}
				if ($fields = \App\Field::getFieldsFromRelation($row['relation_id'])) {
					$this->openNode('fields');
					foreach ($fields as $field) {
						$this->outputNode($field, 'field');
					}
					$this->closeNode('fields');
				}
				$this->closeNode('inrelatedlist');
			}
			$dataReaderRow->close();
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
			$this->outputNode($cronTask->getHandlerClass(), 'handler');
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
		$dataReader = (new \App\Db\Query())->from(\Vtiger_Inventory_Model::getInstance($this->moduleInstance->name)->getTableName())->createCommand()->query();
		if (0 == $dataReader->count()) {
			return false;
		}
		$this->openNode('inventory');
		$this->openNode('fields');
		while ($row = $dataReader->read()) {
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
		$dataReader->close();
		$this->closeNode('fields');
		$this->closeNode('inventory');
	}
}
