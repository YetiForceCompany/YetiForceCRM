<?php

/**
 * CustomView save class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('delete');
		$this->exposeMethod('updateField');
		$this->exposeMethod('upadteSequences');
		$this->exposeMethod('setFilterPermissions');
	}

	public function delete(Vtiger_Request $request)
	{
		$params = $request->get('param');
		Settings_CustomView_Module_Model::delete($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $saveResp['success'],
			'message' => vtranslate('Delete CustomView', $request->getModule(false))
		));
		$response->emit();
	}

	public function updateField(Vtiger_Request $request)
	{
		$params = $request->get('param');
		Settings_CustomView_Module_Model::updateField($params);
		Settings_CustomView_Module_Model::updateOrderAndSort($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => vtranslate('Saving CustomView', $request->getModule(false))
		]);
		$response->emit();
	}

	public function upadteSequences(Vtiger_Request $request)
	{
		$params = $request->get('param');
		$result = Settings_CustomView_Module_Model::upadteSequences($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => vtranslate('LBL_SAVE_SEQUENCES', $request->getModule(false))
		]);
		$response->emit();
	}

	public function setFilterPermissions(Vtiger_Request $request)
	{
		$params = $request->get('param');
		$type = $request->get('type');
		if ($type == 'default') {
			$result = Settings_CustomView_Module_Model::setDefaultUsersFilterView($params['tabid'], $params['cvid'], $params['user'], $params['action']);
		} elseif ($type == 'featured') {
			$result = Settings_CustomView_Module_Model::setFeaturedFilterView($params['cvid'], $params['user'], $params['action']);
		}

		if (!empty($result)) {
			$data = [
				'message' => vtranslate('LBL_EXISTS_PERMISSION_IN_CONFIG', $request->getModule(false), vtranslate($result, $params['tabid'])),
				'success' => false
			];
		} else {
			$data = [
				'message' => vtranslate('LBL_SAVE_CONFIG', $request->getModule(false)),
				'success' => true
			];
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
