<?php

/**
 * OSSTimeControl record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_Record_Model extends Vtiger_Record_Model
{
	const RECALCULATE_STATUS = 'Accepted';

	public static function recalculateTimeControl($id, $name)
	{
		$sumTime = (new App\Db\Query())->from('vtiger_osstimecontrol')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_osstimecontrol.osstimecontrolid')
			->where(['vtiger_crmentity.deleted' => 0, 'osstimecontrol_status' => self::RECALCULATE_STATUS, $name => $id])
			->sum('sum_time');
		$metaData = vtlib\Functions::getCRMRecordMetadata($id);
		$moduleModel = Vtiger_Module_Model::getInstance($metaData['setype']);
		$focus = $moduleModel->getEntityInstance();
		if ($moduleModel->getFieldByColumn('sum_time')) {
			App\Db::getInstance()->createCommand()->update($focus->table_name, ['sum_time' => round($sumTime, 2)], [$focus->table_index => $id])->execute();
		}
	}

	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();
		$date = new DateTime();
		$currDate = DateTimeField::convertToUserFormat($date->format('Y-m-d'));

		$time = $date->format('H:i');

		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true&date_start='
			. $currDate . '&due_date=' . $currDate . '&time_start=' . $time . '&time_end=' . $time;
	}

	/**
	 * {@inheritdoc}
	 */
	public function changeState($state)
	{
		parent::changeState($state);
		$stateId = 0;
		switch ($state) {
			case 'Active':
				$stateId = 0;
				break;
			case 'Trash':
				$stateId = 1;
				break;
			case 'Archived':
				$stateId = 2;
				break;
			default:
				break;
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_osstimecontrol', ['deleted' => $stateId], ['osstimecontrolid' => $this->getId()])->execute();
	}
}
