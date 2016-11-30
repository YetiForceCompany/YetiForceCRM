<?php

/**
 * UIType POS Field Class
 * @package YetiForce.UIType
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_PosList_UIType extends Vtiger_Taxes_UIType
{

	private function getServers()
	{
		$dataReader = (new \App\Db\Query())->select(['id', 'name'])
				->from('w_#__servers')
				->where(['type' => 'POS', 'status' => 1])
				->createCommand()->query();
		$listServers = [];
		while ($server = $dataReader->read()) {
			$listServers[$server['id']] = $server['name'];
		}
		return $listServers;
	}

	public function getPicklistValues()
	{
		return $this->getServers();
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (is_array($value)) {
			$value = implode(',', $value);
		}
		return $value;
	}
}
