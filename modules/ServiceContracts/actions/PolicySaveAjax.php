<?php
/**
 * ServiceContracts PolicySaveAjax Action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class ServiceContracts_PolicySaveAjax_Action extends \App\Controller\Action
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
		$crmId = $request->getInteger('recordId');
		$result = [];
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('u_#__servicecontracts_sla_policy', ['crmid' => $crmId])->execute();
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
			$data['crmid'] = $crmId;
			$data['tabid'] = \App\Module::getModuleId($request->getByType('targetModule', 'Alnum'));
			$db->createCommand()->insert('u_#__servicecontracts_sla_policy', $data)->execute();
			$data['id'] = $db->getLastInsertID();
			$result[] = $data;
		}
		return $result;
	}

	/**
	 * Save.
	 *
	 * @param array $data
	 */
	public function save(array $data)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('u_#__servicecontracts_sla_policy', ['crmid' => $data['crmid']])->execute();
		if ($data['policy_type']) {
			$db->createCommand()->insert('u_#__servicecontracts_sla_policy', $data)->execute();
		}
		return $db->getLastInsertID();
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
				$data['crmid'] = $request->getInteger('recordId');
				$data['tabid'] = \App\Module::getModuleId($request->getByType('targetModule', 'Alnum'));
				$result = ['id' => $this->save($data)];
				break;
			case 2: // custom
				$result = $this->saveCustomRecords($request);
				break;
			case 0:
			default:
				$data['policy_type'] = 0;
				$data['crmid'] = $request->getInteger('recordId');
				$data['tabid'] = \App\Module::getModuleId($request->getByType('targetModule', 'Alnum'));
				$result = ['id' => $this->save($data)];
				break;
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
