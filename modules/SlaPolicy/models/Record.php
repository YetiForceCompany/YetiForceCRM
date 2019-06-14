<?php
/**
 * SlaPolicy Record Model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class SlaPolicy_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Get instance by target id.
	 *
	 * @param int $targetId
	 *
	 * @return self
	 */
	public static function getInstanceByTargetId(int $targetId): self
	{
		$data = (new \App\Db\Query())->from('u_#__servicecontracts_sla_policy')->where(['recordid' => $targetId])->one();
		$instance = static::getCleanInstance('SlaPolicy');
		if ($data) {
			$instance->setData($data);
		}
		$instance->set('recordid', $targetId);
		return $instance;
	}
}
