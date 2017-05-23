<?php

/**
 * VTUpdateClosedTime class
 * @package YetiForce.Workflow
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class VTUpdateClosedTime extends VTTask
{

	public $executeImmediately = true;

	public function getFieldNames()
	{
		return [];
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['closedtime' => date('Y-m-d H:i:s')], ['crmid' => $recordModel->getId()])->execute();
	}
}
