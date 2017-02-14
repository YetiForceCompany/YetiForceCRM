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
 * Provides API to work with vtiger CRM Module
 * @package vtlib
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
	public $customized = 0;
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
	 * Initialize this instance
	 * @access private
	 */
	public function initialize($valuemap)
	{
		$this->id = $valuemap['tabid'];
		$this->name = $valuemap['name'];
		$this->label = $valuemap['tablabel'];
		$this->version = $valuemap['version'];

		$this->presence = $valuemap['presence'];
		$this->ownedby = $valuemap['ownedby'];
		$this->tabsequence = $valuemap['tabsequence'];
		$this->parent = $valuemap['parent'];
		$this->customized = $valuemap['customized'];
		$this->type = $valuemap['type'];

		$this->isentitytype = $valuemap['isentitytype'];

		if ($this->isentitytype || $this->name == 'Users') {
			// Initialize other details too
			$this->initialize2();
		}
	}

	/**
	 * Initialize more information of this instance
	 * @access private
	 */
	public function initialize2()
	{
		$entitydata = \App\Module::getEntityInfo($this->name);
		if ($entitydata) {
			$this->basetable = $entitydata['tablename'];
			$this->basetableid = $entitydata['entityidfield'];
		}
	}

	/**
	 * Create this module instance
	 */
	public function __create()
	{
		self::log("Creating Module $this->name ... STARTED");
		$db = \App\Db::getInstance();

		$this->id = $db->getUniqueID('vtiger_tab', 'tabid', false);
		if (!$this->tabsequence) {
			$this->tabsequence = $db->getUniqueID('vtiger_tab', 'tabsequence', false);
		}
		if (!$this->label) {
			$this->label = $this->name;
		}

		$customized = 1; // To indicate this is a Custom Module

		$db->createCommand()->insert('vtiger_tab', [
			'tabid' => $this->id,
			'name' => $this->name,
			'presence' => $this->presence,
			'tabsequence' => $this->tabsequence,
			'tablabel' => $this->label,
			'modifiedby' => NULL,
			'modifiedtime' => NULL,
			'customized' => $customized,
			'ownedby' => $this->ownedby,
			'version' => $this->version,
			'parent' => $this->parent,
			'isentitytype' => $this->isentitytype ? 1 : 0,
			'type' => $this->type
		])->execute();

		if ($this->minversion) {
			$isExists = (new \App\Db\Query())->from('vtiger_tab_info')->where(['tabid' => $this->id, 'prefname' => 'vtiger_min_version'])->exists();
			if ($isExists) {
				$db->createCommand()->update('vtiger_tab_info', ['prefvalue' => $this->minversion], ['tabid' => $this->id, 'prefname' => 'vtiger_min_version'])->execute();
			} else {
				$db->createCommand()->insert('vtiger_tab_info', [
					'tabid' => $this->id,
					'prefname' => 'vtiger_min_version',
					'prefvalue' => $this->minversion
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
					'prefvalue' => $this->maxversion
				])->execute();
			}
		}

		Profile::initForModule($this);

		self::syncfile();

		if ($this->isentitytype) {
			Access::initSharing($this);
		}

		$moduleInstance = Module::getInstance($this->name);
		$parentTab = $this->parent;
		if (!empty($parentTab)) {
			
		}
		self::log("Creating Module $this->name ... DONE");
	}

	/**
	 * Update this instance
	 * @access private
	 */
	public function __update()
	{
		self::log("Updating Module $this->name ... DONE");
	}

	/**
	 * Delete this instance
	 */
	public function __delete()
	{
		Module::fireEvent($this->name, Module::EVENT_MODULE_PREUNINSTALL);

		if ($this->isentitytype) {
			$this->unsetEntityIdentifier();
		}
		\App\Db::getInstance()->createCommand()->delete('vtiger_tab', ['tabid' => $this->id])->execute();
		self::log("Deleting Module $this->name ... DONE");
	}

	/**
	 * Update module version information
	 * @param string $newVersion
	 */
	public function __updateVersion($newVersion)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['version' => $newVersion], ['tabid' => $this->id])->execute();
		$this->version = $newVersion;
		self::log("Updating version to $newVersion ... DONE");
	}

	/**
	 * Save this instance
	 */
	public function save()
	{
		if ($this->id)
			$this->__update();
		else
			$this->__create();
		return $this->id;
	}

	/**
	 * Delete this instance
	 */
	public function delete()
	{
		$moduleInstance = \Vtiger_Module_Model::getInstance($this->name);
		$focus = \CRMEntity::getInstance($this->name);
		$this->tableName = $focus->table_name;
		if ($this->isentitytype) {
			$this->deleteFromCRMEntity();
			Access::deleteTools($this);
			Filter::deleteForModule($this);
			Block::deleteForModule($this);
			if (method_exists($this, 'deinitWebservice')) {
				$this->deinitWebservice();
			}
		}
		$this->deleteIcons();
		$this->unsetAllRelatedList($moduleInstance);
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
		Link::deleteAll($this->id);
		\Settings_Vtiger_Module_Model::deleteSettingsFieldBymodule($this->name);
		$this->deleteDir($moduleInstance);
		$this->__delete();
		self::syncfile();
		\App\Cache::clear();
	}

	/**
	 * Initialize table required for the module
	 * @param String Base table name (default modulename in lowercase)
	 * @param String Base table column (default modulenameid in lowercase)
	 *
	 * Creates basetable, customtable, grouptable <br>
	 * customtable name is basetable + 'cf'<br>
	 * grouptable name is basetable + 'grouprel'<br>
	 */
	public function initTables($basetable = false, $basetableid = false)
	{
		$this->basetable = $basetable;
		$this->basetableid = $basetableid;

		// Initialize tablename and index column names
		$lcasemodname = strtolower($this->name);
		if (!$this->basetable) {
			$this->basetable = "vtiger_$lcasemodname";
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
			$this->basetableid => 'int'
		]);
		$db->createCommand()->addPrimaryKey("{$this->basetable}_pk", $this->basetable, $this->basetableid)->execute();
		$db->createCommand()->addForeignKey(
			"fk_1_{$this->basetable}{$this->basetableid}", $this->basetable, $this->basetableid, 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'
		)->execute();
		$db->createTable($this->customtable, [
			$this->basetableid => 'int'
		]);
		$db->createCommand()->addPrimaryKey("{$this->customtable}_pk", $this->customtable, $this->basetableid)->execute();
		$db->createCommand()->addForeignKey(
			"fk_1_{$this->customtable}{$this->basetableid}", $this->customtable, $this->basetableid, $this->basetable, $this->basetableid, 'CASCADE', 'RESTRICT'
		)->execute();
		if ($this->type === 1) {
			$db->createTable($this->basetable . '_invfield', [
				'id' => 'pk',
				'columnname' => 'string(30)',
				'label' => $importer->stringType(50)->notNull(),
				'invtype' => $importer->stringType(30)->notNull(),
				'presence' => $importer->boolean()->defaultValue(false),
				'defaultvalue' => 'string',
				'sequence' => $importer->smallInteger()->unsigned()->notNull(),
				'block' => $importer->smallInteger()->unsigned()->notNull(),
				'displaytype' => $importer->smallInteger()->unsigned()->notNull()->defaultValue(1),
				'params' => 'text',
				'colspan' => $importer->smallInteger()->unsigned()->notNull()->defaultValue(1),
			]);
			$db->createTable($this->basetable . '_inventory', [
				'id' => 'int'
			]);
			$db->createCommand()->createIndex("{$this->basetable}_inventory_id_idx", $this->basetable . '_inventory', 'id')->execute();
			$db->createCommand()->addForeignKey(
				"fk_1_{$this->basetable}_inventory{$this->basetableid}", $this->basetable . '_inventory', 'id', $this->basetable, $this->basetableid, 'CASCADE', 'RESTRICT'
			)->execute();
			$db->createTable($this->basetable . '_invmap', [
				'module' => $importer->stringType(50)->notNull(),
				'field' => $importer->stringType(50)->notNull(),
				'tofield' => $importer->stringType(50)->notNull()
			]);
			$db->createCommand()->addPrimaryKey("{$this->basetable}_invmap_pk", $this->basetable . '_invmap', ['module', 'field', 'tofield'])->execute();
		}
	}

	/**
	 * Set entity identifier field for this module
	 * @param \Field Instance of field to use
	 */
	public function setEntityIdentifier($fieldInstance)
	{
		$db = \App\Db::getInstance();

		if ($this->basetableid) {
			if (!$this->entityidfield)
				$this->entityidfield = $this->basetableid;
			if (!$this->entityidcolumn)
				$this->entityidcolumn = $this->basetableid;
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
					'searchcolumn' => $fieldInstance->name
				])->execute();
				self::log('Setting entity identifier ... DONE');
			} else {
				$db->createCommand()->update('vtiger_entityname', ['fieldname' => $fieldInstance->name, 'entityidfield' => $this->entityidfield, 'entityidcolumn' => $this->name,], ['tabid' => $this->id, 'tablename' => $fieldInstance->table])->execute();
				self::log('Updating entity identifier ... DONE');
			}
		}
	}

	/**
	 * Unset entity identifier information
	 */
	public function unsetEntityIdentifier()
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_entityname', ['tabid' => $this->id])->execute();
		self::log('Unsetting entity identifier ... DONE');
	}

	/**
	 * Configure default sharing access for the module
	 * @param String Permission text should be one of ['Public_ReadWriteDelete', 'Public_ReadOnly', 'Public_ReadWrite', 'Private']
	 */
	public function setDefaultSharing($permission_text = 'Public_ReadWriteDelete')
	{
		Access::setDefaultSharing($this, $permission_text);
	}

	/**
	 * Allow module sharing control
	 */
	public function allowSharing()
	{
		Access::allowSharing($this, true);
	}

	/**
	 * Disallow module sharing control
	 */
	public function disallowSharing()
	{
		Access::allowSharing($this, false);
	}

	/**
	 * Enable tools for this module
	 * @param mixed String or Array with value ['Import', 'Export', 'Merge']
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
	 * Disable tools for this module
	 * @param mixed String or Array with value ['Import', 'Export', 'Merge']
	 */
	public function disableTools($tools)
	{
		if (is_string($tools)) {
			$tools = Array(0 => $tools);
		}
		foreach ($tools as $tool) {
			Access::updateTool($this, $tool, false);
		}
	}

	/**
	 * Add block to this module
	 * @param vtlib\Block Instance of block to add
	 */
	public function addBlock($blockInstance)
	{
		$blockInstance->save($this);
		return $this;
	}

	/**
	 * Add filter to this module
	 * @param vtlib\Filter Instance of filter to add
	 */
	public function addFilter($filterInstance)
	{
		$filterInstance->save($this);
		return $this;
	}

	/**
	 * Get all the fields of the module or block
	 * @param vtlib\Block Instance of block to use to get fields, false to get all the block fields
	 */
	public function getFields($blockInstance = false)
	{
		$fields = false;
		if ($blockInstance)
			$fields = Field::getAllForBlock($blockInstance, $this);
		else
			$fields = Field::getAllForModule($this);
		return $fields;
	}

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delimit = true)
	{
		Utils::Log($message, $delimit);
	}

	/**
	 * Synchronize the menu information to flat file
	 * @access private
	 */
	static function syncfile()
	{
		self::log('Updating tabdata file ... ', false);
		Deprecated::createModuleMetaFile();
		self::log('DONE');
	}

	/**
	 * Unset related list information that exists with other module
	 */
	public function unsetAllRelatedList()
	{
		self::log(__METHOD__ . ' | Start');
		$db = \App\Db::getInstance();
		$ids = (new \App\Db\Query())->select(['relation_id'])->from('vtiger_relatedlists')->where(['or', ['tabid' => $this->id], ['related_tabid' => $this->id]])->column();
		$db->createCommand()->delete('vtiger_relatedlists', ['or', ['tabid' => $this->id], ['related_tabid' => $this->id]])->execute();
		if ($ids) {
			$db->createCommand()->delete('vtiger_relatedlists_fields', ['relation_id' => $ids])->execute();
			$db->createCommand()->delete('a_yf_relatedlists_inv_fields', ['relation_id' => $ids])->execute();
		}
		self::log(__METHOD__ . ' | END');
	}

	/**
	 * Function to remove rows in vtiger_group2modules table
	 */
	public function deleteGroup2Modules()
	{
		self::log(__METHOD__ . ' | Start');
		\App\Db::getInstance()->createCommand()->delete('vtiger_group2modules', ['tabid' => $this->id])->execute();
		self::log(__METHOD__ . ' | END');
	}

	/**
	 * Function to remove rows in vtiger_crmentityrel
	 */
	public function deleteCRMEntityRel()
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_crmentityrel', ['or', ['module' => $this->name], ['relmodule' => $this->name]])->execute();
	}

	/**
	 * Function to remove rows in vtiger_crmentity, vtiger_crmentityrel
	 */
	public function deleteFromCRMEntity()
	{
		self::log(__METHOD__ . ' | Start');
		$query = (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentity')->where(['setype' => $this->name]);
		$dataReader = $query->createCommand()->query();
		while ($id = $dataReader->readColumn(0)) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, $this->name);
			$recordModel->delete();
		}
		\App\Db::getInstance()->createCommand()->delete('vtiger_crmentity', ['setype' => $this->name])->execute();
		self::log(__METHOD__ . ' | END');
	}

	/**
	 * Function to remove row in vtiger_modentity_num table
	 */
	public function deleteFromModentityNum()
	{
		self::log(__METHOD__ . ' | Start');
		\App\Db::getInstance()->createCommand()->delete('vtiger_modentity_num', ['tabid' => $this->id])->execute();
		self::log(__METHOD__ . ' | END');
	}

	/**
	 * Function to remove tables created by a module
	 */
	public function deleteModuleTables()
	{
		self::log(__METHOD__ . ' | Start');
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
		self::log(__METHOD__ . ' | END');
	}

	/**
	 * Function to remove files related to a module
	 * @param  string $path - dir path
	 */
	public function deleteDir($moduleInstance)
	{
		self::log(__METHOD__ . ' | Start');
		Functions::recurseDelete("config/modules/{$moduleInstance->name}.php");
		Functions::recurseDelete('modules/' . $moduleInstance->name);
		Functions::recurseDelete('modules/Settings/' . $moduleInstance->name);
		foreach (\Yeti_Layout::getAllLayouts() as $name => $label) {
			Functions::recurseDelete("layouts/$name/modules/{$moduleInstance->name}");
			Functions::recurseDelete("layouts/$name/modules/Settings/{$moduleInstance->name}");
		}
		self::log(__METHOD__ . ' | END');
	}

	/**
	 * Function to remove icons related to a module
	 */
	public function deleteIcons()
	{
		self::log(__METHOD__ . ' | Start');
		$iconSize = ['', 48, 64, 128];
		foreach ($iconSize as $value) {
			foreach (\Yeti_Layout::getAllLayouts() as $name => $label) {
				$fileName = "layouts/$name/skins/images/" . $this->name . $value . ".png";
				if (file_exists($fileName)) {
					@unlink($fileName);
				}
			}
		}
		self::log(__METHOD__ . ' | End');
	}
}
