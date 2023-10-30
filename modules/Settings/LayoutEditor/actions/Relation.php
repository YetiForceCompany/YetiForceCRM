<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_LayoutEditor_Relation_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('changeStatusRelation');
		$this->exposeMethod('updateSequenceRelatedModule');
		$this->exposeMethod('updateSelectedFields');
		$this->exposeMethod('updateStateFavorites');
		$this->exposeMethod('addRelation');
		$this->exposeMethod('removeRelation');
		$this->exposeMethod('updateRelatedViewType');
		$this->exposeMethod('updateCustomView');
		Settings_Vtiger_Tracker_Model::addBasic('save');
	}

	public function changeStatusRelation(App\Request $request)
	{
		$relationId = $request->getInteger('relationId');
		$status = $request->getBoolean('status');
		$response = new Vtiger_Response();
		try {
			Vtiger_Relation_Model::updateRelationPresence($relationId, $status);
			$response->setResult(['success' => true]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function updateSequenceRelatedModule(App\Request $request)
	{
		$modules = $request->get('modules');
		$response = new Vtiger_Response();
		try {
			Vtiger_Relation_Model::updateRelationSequence($modules);
			$response->setResult(['success' => true]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function updateSelectedFields(App\Request $request)
	{
		$fields = $request->get('fields');
		$relationId = $request->getInteger('relationId');
		$isInventory = $request->get('inventory');
		$response = new Vtiger_Response();
		try {
			if ($isInventory) {
				Vtiger_Relation_Model::updateModuleRelatedInventoryFields($relationId, $fields);
			} else {
				Vtiger_Relation_Model::updateModuleRelatedFields($relationId, $fields);
			}
			$response->setResult(['success' => true]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Update relation custom view.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function updateCustomView(App\Request $request): void
	{
		$relationId = $request->getInteger('relationId');
		$cv = $request->getArray('cv', 'Alnum');
		$response = new Vtiger_Response();
		try {
			Vtiger_Relation_Model::updateRelationCustomView($relationId, $cv);
			$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_SAVE_NOTIFY_OK', '')]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function addRelation(App\Request $request)
	{
		$source = $request->getByType('source', 'Alnum');
		$target = $request->getByType('target', 'Alnum');
		$label = $request->getByType('label', 'Text');
		$type = $request->getByType('type', 'Standard');
		$response = new Vtiger_Response();

		if ('getAttachments' === $type && 'Documents' !== $target) {
			$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_WRONG_RELATION', 'Settings::LayoutEditor')]);
		} else {
			$module = vtlib\Module::getInstance($source);
			$moduleInstance = vtlib\Module::getInstance($target);
			$module->setRelatedList($moduleInstance, $label, $request->getArray('actions', 'Standard'), $type);
			$response->setResult(['success' => true]);
		}
		$response->emit();
	}

	public function removeRelation(App\Request $request)
	{
		$relationId = $request->getInteger('relationId');
		$response = new Vtiger_Response();
		try {
			Vtiger_Relation_Model::removeRelationById($relationId);
			$response->setResult(['success' => true]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Update related view type mode.
	 *
	 * @param \App\Request $request
	 */
	public function updateRelatedViewType(App\Request $request)
	{
		$response = new Vtiger_Response();
		try {
			Settings_LayoutEditor_Module_Model::updateRelatedViewType($request->getInteger('relationId'), $request->getArray('types', 'Standard'));
			$response->setResult(['text' => \App\Language::translate('LBL_CHANGES_SAVED')]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function updateStateFavorites(App\Request $request)
	{
		$relationId = $request->getInteger('relationId');
		$status = $request->get('status');
		$response = new Vtiger_Response();
		try {
			Vtiger_Relation_Model::updateStateFavorites($relationId, $status);
			$response->setResult(['success' => true]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
