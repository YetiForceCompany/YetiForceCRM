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
 * Provides API to work with vtiger CRM Module.
 */
class ModuleBasic
{
	/** ID of this instance */
	public $id = false;
	public $name = false;
	public $label = false;
	public $version = 0;
	public $minversion = false;
	public $maxversion = false;
	public $presence = 0;
	public $ownedby = 0; // 0 - Sharing Access Enabled, 1 - Sharing Access Disabled
	public $tabsequence = false;
	public $parent = false;
	public $customized = 1;
	public $isentitytype = true; // Real module or an extension?
	public $entityidcolumn = false;
	public $entityidfield = false;
	public $basetable = false;
	public $basetableid = false;
	public $customtable = false;
	public $grouptable = false;
	public $type = 0;
	public $tableName;

	const EVENT_MODULE_ENABLED = 'module.enabled';
	const EVENT_MODULE_DISABLED = 'module.disabled';
	const EVENT_MODULE_POSTINSTALL = 'module.postinstall';
	const EVENT_MODULE_PREUNINSTALL = 'module.preuninstall';
	const EVENT_MODULE_PREUPDATE = 'module.preupdate';
	const EVENT_MODULE_POSTUPDATE = 'module.postupdate';

	/**
	 * Initialize this instance.
	 */
	public function initialize($valuemap)
	{
		$this->id = (int) $valuemap['tabid'];
		$this->name = $valuemap['name'];
		$this->label = $valuemap['tablabel'];
		$this->version = $valuemap['version'];
		$this->presence = (int) $valuemap['presence'];
		$this->ownedby = $valuemap['ownedby'];
		$this->tabsequence = (int) $valuemap['tabsequence'];
		$this->parent = $valuemap['parent'];
		$this->customized = (int) $valuemap['customized'];
		$this->type = (int) $valuemap['type'];
		$this->isentitytype = (int) $valuemap['isentitytype'];
		if ($this->isentitytype || $this->name === 'Users') {
			$entitydata = \App\Module::getEntityInfo($this->name);
			if ($entitydata) {
				$this->basetable = $entitydata['tablename'];
				$this->basetableid = $entitydata['entityidfield'];
			}
		}
	}

	/**
	 * Create this module instance.
	 */
	public function __create()
	{
		\App\Log::trace("Creating Module $this->name ... STARTED", __METHOD__);
		$db = \App\Db::getInstance();

		$this->id = $db->getUniqueID('vtiger_tab', 'tabid', false);
		if (!$this->tabsequence) {
			$this->tabsequence = $db->getUniqueID('vtiger_tab', 'tabsequence', false);
		}
		if (!$this->label) {
			$this->label = $this->name;
		}
		$db->createCommand()->insert('vtiger_tab', [
			'tabid' => $this->id,
			'name' => $this->name,
			'presence' => $this->presence,
			'tabsequence' => $this->tabsequence,
			'tablabel' => $this->label,
			'modifiedby' => null,
			'modifiedtime' => null,
			'customized' => $this->customized,
			'ownedby' => $this->ownedby,
			'version' => $this->version,
			'parent' => $this->parent,
			'isentitytype' => $this->isentitytype ? 1 : 0,
			'type' => $this->type,
		])->execute();

		if ($this->minversion) {
			$isExists = (new \App\Db\Query())->from('vtiger_tab_info')->where(['tabid' => $this->id, 'prefname' => 'vtiger_min_version'])->exists();
			if ($isExists) {
				$db->createCommand()->update('vtiger_tab_info', ['prefvalue' => $this->minversion], ['tabid' => $this->id, 'prefname' => 'vtiger_min_version'])->execute();
			} else {
				$db->createCommand()->insert('vtiger_tab_info', [
					'tabid' => $this->id,
					'prefname' => 'vtiger_min_version',
					'prefvalue' => $this->minversion,
				])->execute();
			}
		}
		if ($this->maxversion) {
			$isExists = (new \App\Db\Query())->from('vtiger_tab_info')->where(['tabid' => $this->id, 'prefname' => 'vtiger_max_version'])->exists();
			if ($isExists) {
				$db->createCommand()->update('vtiger_tab_info', ['prefvalue' => $this->maxversion], ['tabid' => $this->id, 'prefname' => 'vtiger_max_version'])->execute();
			} else {
				$db->createCommand()->insert('vtiger_tab_info', [
					'tabid' => $this->id,
					'prefname' => 'vtiger_max_version',
					'prefvalue' => $this->maxversion,
				])->execute();
			}
		}

		Profile::initForModule($this);

		self::syncfile();

		if ($this->isentitytype) {
			Access::initSharing($this);
		}
		\App\Log::trace("Creating Module $this->name ... DONE", __METHOD__);
	}

	/**
	 * Update this instance.
	 */
	public function __update()
	{
		\App\Log::trace("Updating Module $this->name ... DONE", __METHOD__);
	}

	/**
	 * Delete this instance.
	 */
	public function __delete()
	{
		Module::fireEvent($this->name, Module::EVENT_MODULE_PREUNINSTALL);
		if ($this->isentitytype) {
			$this->unsetEntityIdentifier();
		}
		\App\Db::getInstance()->createCommand()->delete('vtiger_tab', ['tabid' => $this->id])->execute();
		\App\Log::trace("Deleting Module $this->name ... DONE", __METHOD__);
	}

	/**
	 * Update module version information.
	 *
	 * @param string $newVersion
	 */
	public function __updateVersion($newVersion)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['version' => $newVersion], ['tabid' => $this->id])->execute();
		$this->version = $newVersion;
		\App\Log::trace("Updating version to $newVersion ... DONE", __METHOD__);
	}

	/**
	 * Save this instance.
	 */
	public function save()
	{
		if ($this->id) {
			$this->__update();
		} else {
			$this->__create();
		}
		return $this->id;
	}

	/**
	 * Delete this instance.
	 */
	public function delete()
	{
		$moduleInstance = \Vtiger_Module_Model::getInstance($this->name);
		$focus = \CRMEntity::getInstance($this->name);
		if (isset($focus->table_name)) {
			$this->tableName = $focus->table_name;
		}
		if ($this->isentitytype) {
			$this->deleteFromCRMEntity();
			Access::deleteTools($this);
			Filter::deleteForModule($this);
			Block::deleteForModule($this);
		}
		$this->deleteIcons();
		$this->unsetAllRelatedList();
		\ModComments_Module_Model::deleteForModule($moduleInstance);
		Language::deleteForModule($moduleInstance);
		Access::deleteSharing($moduleInstance);
		$this->deleteFromModentityNum();
		Cron::deleteForModule($moduleInstance);
		Profile::deleteForModule($moduleInstance);
		\Settings_Workflows_Module_Model::deleteForModule($moduleInstance);
		Menu::deleteForModule($moduleInstance);
		$this->deleteGroup2Modules();
		$this->deleteModuleTables();
		$this->deleteCRMEntityRel();
		Profile::deleteForModule($this);
		\App\Fields\Tree::deleteForModule($this->id);
		Link::deleteAll($this->id);
		\Settings_Vtiger_Module_Model::deleteSettingsFieldBymodule($this->name);
		$this->__delete();
		$this->deleteDir($moduleInstance);
		self::syncfile();
		\App\Cache::clear();
	}

	/**
	 * Initialize table required for the module.
	 *
	 * @param string Base table name (default modulename in lowercase)
	 * @param string Base table column (default modulenameid in lowercase)
	 *
	 * Creates basetable, customtable, grouptable <br />
	 * customtable name is basetable + 'cf'<br />
	 * grouptable name is basetable + 'grouprel'<br />
	 */
	public function initTables($basetable = false, $basetableid = false)
	{
		$this->basetable = $basetable;
		$this->basetableid = $basetableid;
		$db = \App\Db::getInstance();
		// Initialize tablename and index column names
		$lcasemodname = strtolower($this->name);
		if (!$this->basetable) {
			$this->basetable = 'u_' . $db->getConfig('base')['tablePrefix'] . $lcasemodname;
		}
		if (!$this->basetableid) {
			$this->basetableid = $lcasemodname . 'id';
		}
		if (!$this->customtable) {
			$this->customtable = $this->basetable . 'cf';
		}
		$db = \App\Db::getInstance();
		$importer = new \App\Db\Importers\Base();
		$db->createTable($this->basetable, [
			$this->basetableid => $importer->integer(10),
		]);
		$db->createCommand()->addPrimaryKey("{$this->basetable}_pk", $this->basetable, $this->basetableid)->execute();
		$db->createCommand()->addForeignKey(
			"fk_1_{$this->basetable}{$this->basetableid}", $this->basetable, $this->basetableid, 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'
		)->execute();
		$db->createTable($this->customtable, [
			$this->basetableid => $importer->integer(10),
		]);
		$db->createCommand()->addPrimaryKey("{$this->customtable}_pk", $this->customtable, $this->basetableid)->execute();
		$db->createCommand()->addForeignKey(
			"fk_1_{$this->customtable}{$this->basetableid}", $this->customtable, $this->basetableid, $this->basetable, $this->basetableid, 'CASCADE', 'RESTRICT'
		)->execute();
	}

	/**
	 * Set entity identifier field for this module.
	 *
	 * @param FieldBasic $fieldInstance
	 */
	public function setEntityIdentifier(FieldBasic $fieldInstance)
	{
		$db = \App\Db::getInstance();

		if ($this->basetableid) {
			if (!$this->entityidfield) {
				$this->entityidfield = $this->basetableid;
			}
			if (!$this->entityidcolumn) {
				$this->entityidcolumn = $this->basetableid;
			}
		}
		if ($this->entityidfield && $this->entityidcolumn) {
			$isExists = (new \App\Db\Query())->from('vtiger_entityname')->where(['tablename' => $fieldInstance->table, 'tabid' => $this->id])->exists();
			if (!$isExists) {
				$db->createCommand()->insert('vtiger_entityname', [
					'tabid' => $this->id,
					'modulename' => $this->name,
					'tablename' => $fieldInstance->table,
					'fieldname' => $fieldInstance->name,
					'entityidfield' => $this->entityidfield,
					'entityidcolumn' => $this->entityidcolumn,
					'searchcolumn' => $fieldInstance->name,
				])->execute();
				\App\Log::trace('Setting entity identifier ... DONE', __METHOD__);
			} else {
				$db->createCommand()->update('vtiger_entityname', ['fieldname' => $fieldInstance->name, 'entityidfield' => $this->entityidfield, 'entityidcolumn' => $this->name], ['tabid' => $this->id, 'tablename' => $fieldInstance->table])->execute();
				\App\Log::trace('Updating entity identifier ... DONE', __METHOD__);
			}
		}
	}

	/**
	 * Unset entity identifier information.
	 */
	public function unsetEntityIdentifier()
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_entityname', ['tabid' => $this->id])->execute();
		\App\Log::trace('Unsetting entity identifier ... DONE', __METHOD__);
	}

	/**
	 * Configure default sharing access for the module.
	 *
	 * @param string Permission text should be one of ['Public_ReadWriteDelete', 'Public_ReadOnly', 'Public_ReadWrite', 'Private']
	 */
	public function setDefaultSharing($permission_text = 'Public_ReadWriteDelete')
	{
		Access::setDefaultSharing($this, $permission_text);
	}

	/**
	 * Allow module sharing control.
	 */
	public function allowSharing()
	{
		Access::allowSharing($this, true);
	}

	/**
	 * Disallow module sharing control.
	 */
	public function disallowSharing()
	{
		Access::allowSharing($this, false);
	}

	/**
	 * Enable tools for this module.
	 *
	 * @param mixed String or Array with value ['Import', 'Export']
	 */
	public function enableTools($tools)
	{
		if (is_string($tools)) {
			$tools = [$tools];
		}

		foreach ($tools as $tool) {
			Access::updateTool($this, $tool, true);
		}
	}

	/**
	 * Disable tools for this module.
	 *
	 * @param mixed String or Array with value ['Import', 'Export']
	 */
	public function disableTools($tools)
	{
		if (is_string($tools)) {
			$tools = [0 => $tools];
		}
		foreach ($tools as $tool) {
			Access::updateTool($this, $tool, false);
		}
	}

	/**
	 * Add block to this module.
	 *
	 * @param Block $blockInstance
	 *
	 * @return $this
	 */
	public function addBlock(Block $blockInstance)
	{
		$blockInstance->save($this);

		return $this;
	}

	/**
	 * Add filter to this module.
	 *
	 * @param Filter $filterInstance
	 *
	 * @return $this
	 */
	public function addFilter(Filter $filterInstance)
	{
		$filterInstance->save($this);

		return $this;
	}

	/**
	 * Function to get the Module/Tab id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get all the fields of the module or block.
	 *
	 * @param vtlib\Block Instance of block to use to get fields, false to get all the block fields
	 */
	public function getFields($blockInstance = false)
	{
		$fields = false;
		if ($blockInstance) {
			$fields = Field::getAllForBlock($blockInstance, $this);
		} else {
			$fields = Field::getAllForModule($this);
		}
		return $fields;
	}

	/**
	 * Get all the custom links related to this module for exporting.
	 */
	public function getLinksForExport()
	{
		return Link::getAllForExport($this->id);
	}

	/**
	 * Synchronize the menu information to flat file.
	 */
	public static function syncfile()
	{
		\App\Log::trace('Updating tabdata file ... ', __METHOD__);
		\App\Module::createModuleMetaFile();
		\App\Log::trace('DONE', __METHOD__);
	}

	/**
	 * Unset related list information that exists with other module.
	 */
	public function unsetAllRelatedList()
	{
		\App\Log::trace('Start', __METHOD__);
		$db = \App\Db::getInstance();
		$ids = (new \App\Db\Query())->select(['relation_id'])->from('vtiger_relatedlists')->where(['or', ['tabid' => $this->id], ['related_tabid' => $this->id]])->column();
		$db->createCommand()->delete('vtiger_relatedlists', ['or', ['tabid' => $this->id], ['related_tabid' => $this->id]])->execute();
		if ($ids) {
			$db->createCommand()->delete('vtiger_relatedlists_fields', ['relation_id' => $ids])->execute();
			$db->createCommand()->delete('a_#__relatedlists_inv_fields', ['relation_id' => $ids])->execute();
		}
		\App\Log::trace('End', __METHOD__);
	}

	/**
	 * Function to remove rows in vtiger_group2modules table.
	 */
	public function deleteGroup2Modules()
	{
		\App\Log::trace('Start', __METHOD__);
		\App\Db::getInstance()->createCommand()->delete('vtiger_group2modules', ['tabid' => $this->id])->execute();
		\App\Log::trace('End', __METHOD__);
	}

	/**
	 * Function to remove rows in vtiger_crmentityrel.
	 */
	public function deleteCRMEntityRel()
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_crmentityrel', ['or', ['module' => $this->name], ['relmodule' => $this->name]])->execute();
	}

	/**
	 * Function to remove rows in vtiger_crmentity, vtiger_crmentityrel.
	 */
	public function deleteFromCRMEntity()
	{
		\App\Log::trace('Start', __METHOD__);
		$query = (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentity')->where(['setype' => $this->name]);
		$dataReader = $query->createCommand()->query();
		while ($crmId = $dataReader->readColumn(0)) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($crmId, $this->name);
			$recordModel->delete();
		}
		\App\Log::trace('End', __METHOD__);
	}

	/**
	 * Function to remove row in vtiger_modentity_num table.
	 */
	public function deleteFromModentityNum()
	{
		\App\Log::trace('Start', __METHOD__);
		\App\Db::getInstance()->createCommand()->delete('vtiger_modentity_num', ['tabid' => $this->id])->execute();
		\App\Log::trace('End', __METHOD__);
	}

	/**
	 * Function to remove tables created by a module.
	 */
	public function deleteModuleTables()
	{
		\App\Log::trace('Start', __METHOD__);
		$db = \App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();
		$moduleInstance = \Vtiger_Module_Model::getInstance($this->name);
		if ($moduleInstance->isInventory()) {
			$tablesName = [$this->tableName . '_inventory', $this->tableName . '_invfield', $this->tableName . '_invmap'];
			foreach ($tablesName as $tableName) {
				if ($db->isTableExists($tableName)) {
					$db->createCommand()->dropTable($tableName)->execute();
				}
			}
		}
		if (!empty($this->tableName)) {
			$tablesName = [$this->tableName . 'cf', $this->tableName];
			foreach ($tablesName as $tableName) {
				if ($db->isTableExists($tableName)) {
					$db->createCommand()->dropTable($tableName)->execute();
				}
			}
		}
		$db->createCommand()->checkIntegrity(true)->execute();
		\App\Log::trace('End', __METHOD__);
	}

	/**
	 * Function to remove files related to a module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public function deleteDir(self $moduleInstance)
	{
		\App\Log::trace('Start', __METHOD__);
		Functions::recurseDelete("config/Modules/{$moduleInstance->name}.php");
		Functions::recurseDelete('modules/' . $moduleInstance->name);
		Functions::recurseDelete('modules/Settings/' . $moduleInstance->name);
		foreach (\App\Layout::getAllLayouts() as $name => $label) {
			Functions::recurseDelete("layouts/$name/modules/{$moduleInstance->name}");
			Functions::recurseDelete("layouts/$name/modules/Settings/{$moduleInstance->name}");
			Functions::recurseDelete("public_html/layouts/$name/modules/{$moduleInstance->name}");
			Functions::recurseDelete("public_html/layouts/$name/modules/Settings/{$moduleInstance->name}");
		}
		\App\Log::trace('End', __METHOD__);
	}

	/**
	 * Function to remove icons related to a module.
	 */
	public function deleteIcons()
	{
		\App\Log::trace('Start', __METHOD__);
		$iconSize = ['', 48, 64, 128];
		foreach ($iconSize as $value) {
			foreach (\App\Layout::getAllLayouts() as $name => $label) {
				$fileName = "layouts/$name/images/{$this->name}{$value}.png";
				if (file_exists($fileName)) {
					@unlink($fileName);
				}
			}
		}
		\App\Log::trace('End', __METHOD__);
	}
}
