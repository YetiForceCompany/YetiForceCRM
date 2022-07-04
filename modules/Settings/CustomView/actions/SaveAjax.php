<?php

/**
 * CustomView save class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function delete(App\Request $request)
	{
		CustomView_Record_Model::getInstanceById($request->getInteger('cvid'))->delete();
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
	public function updateField(App\Request $request)
	{
		$recordModel = CustomView_Record_Model::getInstanceById($request->getInteger('cvid'));
		$recordModel->set('mode', 'edit');
		$recordModel->setValueFromRequest($request, $request->getByType('name', \App\Purifier::STANDARD), 'value');
		$recordModel->save();

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
	public function upadteSequences(App\Request $request)
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
	public function setFilterPermissions(App\Request $request)
	{
		$user = $request->getByType('user', 'Text');
		$add = $request->getBoolean('operator');

		$recordModel = CustomView_Record_Model::getInstanceById($request->getInteger('cvid'));

		switch ($request->getByType('type')) {
			case 'default':
				$result = $add ? $recordModel->setDefaultForMember($user) : $recordModel->removeDefaultForMember($user);
				break;
			case 'featured':
				$result = $add ? $recordModel->setFeaturedForMember($user) : $recordModel->removeFeaturedForMember($user);
				break;
			case 'permissions':
				$result = $add ? $recordModel->setPrivilegesForMember($user) : $recordModel->removePrivilegesForMember($user);
				break;
			default:
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
				break;
		}
		$data = ['success' => $result];
		if ($result) {
			$data['message'] = \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false));
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
