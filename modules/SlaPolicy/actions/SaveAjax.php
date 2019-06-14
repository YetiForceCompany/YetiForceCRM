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
class SlaPolicy_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('recordId');
		$recordModel = SlaPolicy_Record_Model::getInstanceByTargetId($recordId);
		$policyType = $request->getByType('policyType', 'Text');
		switch ($policyType) {
			case 'template':
				$recordModel->set('policy_type', 1);
				$recordModel->set('sla_policy_id', $request->getInteger('policyId'));
				break;
			case 'custom':
				$recordModel->set('policy_type', 2);
				break;
			case 'default':
			default:
				$recordModel->set('policy_type', 0);
				break;
		}
		$recordModel->save();
		$response = new Vtiger_Response();
		$response->setResult(['ok']);
		$response->emit();
	}
}
