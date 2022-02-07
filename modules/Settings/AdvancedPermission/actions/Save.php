<?php

/**
 * Advanced permission save action model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_Save_Action extends Settings_Vtiger_Save_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
	}

	/**
	 * Save first step.
	 *
	 * @param \App\Request $request
	 */
	public function step1(App\Request $request)
	{
		if (!$request->isEmpty('record', true)) {
			$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($request->getInteger('record'));
		} else {
			$recordModel = new Settings_AdvancedPermission_Record_Model();
		}
		$recordModel->set('name', $request->getByType('name', 'Text'));
		$recordModel->set('tabid', $request->getInteger('tabid'));
		$recordModel->set('action', $request->getInteger('actions'));
		$recordModel->set('status', $request->getInteger('status'));
		$recordModel->set('members', $request->getArray('members', 'Text'));
		$recordModel->set('priority', $request->getInteger('priority'));
		$recordModel->save();

		header("location: {$recordModel->getEditViewUrl(2)}");
	}

	/**
	 * Save second step.
	 *
	 * @param \App\Request $request
	 */
	public function step2(App\Request $request)
	{
		$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($request->getInteger('record'));
		$conditions = Vtiger_AdvancedFilter_Helper::transformToSave($request->getArray('conditions', 'Text'));
		$recordModel->set('conditions', $conditions);
		$recordModel->save();

		header("location: {$recordModel->getDetailViewUrl()}");
	}
}
