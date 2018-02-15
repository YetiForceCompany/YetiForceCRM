<?php

/**
 * VTUpdateClosedTime class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class VTUpdateClosedTime extends VTTask
{
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return [];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['closedtime' => date('Y-m-d H:i:s')], ['crmid' => $recordModel->getId()])->execute();
	}
}
