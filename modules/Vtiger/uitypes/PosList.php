<?php

/**
 * UIType POS Field Class
 * @package YetiForce.UIType
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_PosList_UIType extends Vtiger_Base_UIType
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

	public function getTemplateName()
	{
		return 'uitypes/Pos.tpl';
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/MultiSelectFieldSearchView.tpl';
	}

	public function getDisplayValue($values, $record = false, $recordInstance = false, $rawText = false)
	{
		$listServers = $this->getServers();
		$namesOfServers = '';
		if (!empty($values)) {
			$values = explode(',', $values);
			foreach ($values as $server) {
				$namesOfServers .= $listServers[$server] . ', ';
			}
		}
		return rtrim($namesOfServers, ', ');
	}

	public function getPicklistValues()
	{
		return $this->getServers();
	}
}
