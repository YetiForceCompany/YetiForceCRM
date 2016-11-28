<?php

/**
 * 
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_Module_Model extends Settings_Vtiger_Module_Model
{

	static public function getTypes()
	{
		return ['Portal', 'POS'];
	}

	static public function getServers()
	{
		
		$db = \App\Db::getInstance('portal');
		$query = new \App\Db\Query();
		$query->from('w_#__servers');
        return $query->createCommand($db)->queryAllByGroup(true);

	}

	static public function getActiveServers($type = '')
	{
		$db = \App\Db::getInstance('portal');
		$query = new \App\Db\Query();
		$query->from('w_#__servers')->andWhere(['status' => 1]);
		if (!empty($type)) {
			$query->andWhere(['type' => $type]);
		}

		return $query->createCommand($db)->queryAllByGroup(true);
	}
}
