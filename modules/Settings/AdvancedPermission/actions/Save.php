<?php

/**
 * Advanced permission save action model class
 * @package YetiForce.Settings.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_Save_Action extends Settings_Vtiger_Save_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Save first step
	 * @param \App\Request $request
	 */
	public function step1(\App\Request $request)
	{
		if ($request->isEmpty('record') === false) {
			$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($request->get('record'));
		} else {
			$recordModel = new Settings_AdvancedPermission_Record_Model();
		}
		$recordModel->set('name', $request->get('name'));
		$recordModel->set('tabid', $request->get('tabid'));
		$recordModel->set('action', $request->get('actions'));
		$recordModel->set('status', $request->get('status'));
		$recordModel->set('members', $request->get('members'));
		$recordModel->set('priority', $request->get('priority'));
		$recordModel->save();

		header("Location: {$recordModel->getEditViewUrl(2)}");
	}

	/**
	 * Save second step
	 * @param \App\Request $request
	 */
	public function step2(\App\Request $request)
	{
		$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($request->get('record'));
		$conditions = Vtiger_AdvancedFilter_Helper::transformToSave($request->get('conditions'));
		$recordModel->set('conditions', $conditions);
		$recordModel->save();

		header("Location: {$recordModel->getDetailViewUrl()}");
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
