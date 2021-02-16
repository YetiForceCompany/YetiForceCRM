<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_LayoutEditor_Block_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('updateSequenceNumber');
		$this->exposeMethod('delete');
		Settings_Vtiger_Tracker_Model::addBasic('save');
	}

	public function save(App\Request $request)
	{
		$blockId = $request->get('blockid');
		$sourceModule = $request->getByType('sourceModule', 2);
		$modueInstance = Vtiger_Module_Model::getInstance($sourceModule);
		$beforeBlockId = false;

		if (!empty($blockId)) {
			$blockInstance = Settings_LayoutEditor_Block_Model::getInstance($blockId);
			$blockInstance->set('display_status', $request->get('display_status'));
			$isDuplicate = false;
		} else {
			$blockInstance = new Settings_LayoutEditor_Block_Model();
			$blockInstance->set('label', $request->get('label'));
			$blockInstance->set('iscustom', '1');
			//Indicates block id after which you need to add the new block
			$beforeBlockId = $request->get('beforeBlockId');
			if (!empty($beforeBlockId)) {
				$beforeBlockInstance = Vtiger_Block_Model::getInstance($beforeBlockId);
				$beforeBlockSequence = $beforeBlockInstance->get('sequence');
				$newBlockSequence = ($beforeBlockSequence + 1);
				//To give sequence one more than prev block
				$blockInstance->set('sequence', $newBlockSequence);
				//push all other block down so that we can keep new block there
				Vtiger_Block_Model::pushDown($beforeBlockSequence, $modueInstance->getId());
			}
			$isDuplicate = Vtiger_Block_Model::checkDuplicate($request->get('label'), $modueInstance->getId());
		}

		$response = new Vtiger_Response();
		if (!$isDuplicate) {
			try {
				$id = $blockInstance->save($modueInstance);
				$responseInfo = ['id' => $id, 'label' => $blockInstance->get('label'), 'isCustom' => $blockInstance->isCustomized(), 'beforeBlockId' => $beforeBlockId, 'isAddCustomFieldEnabled' => $blockInstance->isAddCustomFieldEnabled(), 'success' => true];
				if (empty($blockId)) {
					//if mode is create add all blocks sequence so that client will place the new block correctly
					$responseInfo['sequenceList'] = Vtiger_Block_Model::getAllBlockSequenceList($modueInstance->getId());
				}
				$response->setResult($responseInfo);
			} catch (Exception $e) {
				$response->setError($e->getCode(), $e->getMessage());
			}
		} else {
			$response->setResult([
				'success' => false,
				'message' => \App\Language::translate('LBL_DUPLICATES_EXIST', $request->getModule(false))
			]);
		}
		$response->emit();
	}

	public function updateSequenceNumber(App\Request $request)
	{
		$response = new Vtiger_Response();
		try {
			$sequenceList = $request->get('sequence');
			Vtiger_Block_Model::updateSequenceNumber($sequenceList);
			$response->setResult(['success' => true]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Delete block.
	 *
	 * @param App\Request $request
	 */
	public function delete(App\Request $request)
	{
		$response = new Vtiger_Response();
		$blockId = $request->get('blockid');
		$checkIfFieldsExists = Vtiger_Block_Model::checkFieldsExists($blockId);
		if ($checkIfFieldsExists) {
			$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_FIELDS_EXISTS_IN_BLOCK', $request->getModule(false))]);
			$response->emit();
			return;
		}
		$blockInstance = Vtiger_Block_Model::getInstance($blockId);
		if (!$blockInstance->isCustomized()) {
			$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_DELETE_CUSTOM_BLOCKS', $request->getModule(false))]);
			$response->emit();
			return;
		}
		try {
			$blockInstance->delete(false);
			$response->setResult(['success' => true]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
