<?php

/**
 * Settings TimeControlProcesses module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TimeControlProcesses_Module_Model extends \App\Base
{
	public static function getCleanInstance()
	{
		return new self();
	}

	public function getConfigInstance($type = false)
	{
		\App\Log::trace('Start ' . __METHOD__ . ' | Type: ' . print_r($type, true));
		$query = (new App\Db\Query())->from('yetiforce_proc_tc');
		if ($type) {
			$query->where(['type' => $type]);
		}
		$dataReader = $query->createCommand()->query();
		$output = [];
		while ($row = $dataReader->read()) {
			$output[$row['type']][$row['param']] = $row['value'];
		}
		$dataReader->close();
		$this->setData($output);
		\App\Log::trace('End ' . __METHOD__);

		return $this;
	}

	public function setConfig($param)
	{
		\App\Log::trace('Start ' . __METHOD__);
		\App\Db::getInstance()->createCommand()
			->update('yetiforce_proc_tc', ['value' => $param['value']], ['type' => $param['type'], 'param' => $param['param']])->execute();
		\App\Log::trace('End ' . __METHOD__);

		return true;
	}
}
