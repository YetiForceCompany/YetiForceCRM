<?php

/**
 * CustomView save class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('delete');
		$this->exposeMethod('updateField');
		$this->exposeMethod('upadteSequences');
		$this->exposeMethod('setFilterPermissions');
	}

	/**
	 * Action to delete filter.
	 *
	 * @param \App\Request $request
	 */
	public function delete(\App\Request $request)
	{
		Settings_CustomView_Module_Model::delete($request->getInteger('cvid'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('Delete CustomView', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Action to update parameter in the filter.
	 *
	 * @param \App\Request $request
	 */
	public function updateField(\App\Request $request)
	{
		$params = [
			'cvid' => $request->getInteger('cvid'),
			'mod' => $request->getByType('mod', 2),
			'name' => $request->getByType('name', 2),
			'value' => $request->getByType('value', 'Text')
		];
		Settings_CustomView_Module_Model::updateField($params);
		Settings_CustomView_Module_Model::updateOrderAndSort($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('Saving CustomView', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Action to update sequences.
	 *
	 * @param \App\Request $request
	 */
	public function upadteSequences(\App\Request $request)
	{
		$params = $request->getArray('param', 'Integer');
		Settings_CustomView_Module_Model::upadteSequences($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_SAVE_SEQUENCES', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Action to set permissions.
	 *
	 * @param \App\Request $request
	 */
	public function setFilterPermissions(\App\Request $request)
	{
		$tabid = $request->getInteger('tabid');
		$cvid = $request->getInteger('cvid');
		$user = $request->getByType('user', 'Text');
		$type = $request->getByType('type');
		$operator = $request->getByType('operator');
		if ($type === 'default') {
			$result = Settings_CustomView_Module_Model::setDefaultUsersFilterView($tabid, $cvid, $user, $operator);
		} elseif ($type === 'featured') {
			$result = CustomView_Record_Model::setFeaturedFilterView($cvid, $user, $operator);
		}
		if (!empty($result)) {
			$data = [
				'message' => \App\Language::translate('LBL_EXISTS_PERMISSION_IN_CONFIG', $request->getModule(false), \App\Language::translate($result, $tabid)),
				'success' => false,
			];
		} else {
			$data = [
				'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false)),
				'success' => true,
			];
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
