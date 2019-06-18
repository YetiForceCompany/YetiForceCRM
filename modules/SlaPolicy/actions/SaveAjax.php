<?php
/**
 * SlaPolicy SaveAjax Action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class SlaPolicy_SaveAjax_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getByType('targetModule', 'Alnum')) || !$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Save custom records.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function saveCustomRecords(App\Request $request)
	{
		$rowsIds = $request->getArray('rowid', 'Integer');
		$result = [];
		foreach ($rowsIds as $rowIndex => $rowId) {
			$data = [];
			$data['policy_type'] = 2;
			$rowConditions = \App\Json::decode($request->getArray('conditions', 'Text')[$rowIndex]);
			if ($rowConditions) {
				$conditions = \App\Condition::getConditionsFromRequest($rowConditions);
				$data['conditions'] = \App\Json::encode($conditions);
			} else {
				$data['conditions'] = '';
			}
			$data['business_hours'] = implode(',', $request->getArray('business_hours', 'Integer')[$rowIndex]);
			$data['reaction_time'] = $request->getArray('reaction_time', 'TimePeriod')[$rowIndex];
			$data['idle_time'] = $request->getArray('idle_time', 'TimePeriod')[$rowIndex];
			$data['resolve_time'] = $request->getArray('resolve_time', 'TimePeriod')[$rowIndex];
			$data['crmid'] = $request->getInteger('recordId');
			$data['tabid'] = \App\Module::getModuleId($request->getByType('targetModule', 'Alnum'));
			$db = \App\Db::getInstance();
			if (!$rowId) {
				$db->createCommand()->insert('u_#__servicecontracts_sla_policy', $data)->execute();
				$data['id'] = $db->getLastInsertID();
			} else {
				$db->createCommand()->update('u_#__servicecontracts_sla_policy', $data, ['id' => $rowId])->execute();
				$data['id'] = $rowId;
			}
			$result[] = $data;
		}
		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$result = [];
		$policyType = $request->getByType('policyType', 'Text');
		switch ($policyType) {
			case 1: // template
				$data['policy_type'] = 1;
				$data['sla_policy_id'] = $request->getInteger('policyId');
				break;
			case 2: // custom
				$result = $this->saveCustomRecords($request);
				break;
			case 0:
			default:
				$data['policy_type'] = 0;
				break;
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
