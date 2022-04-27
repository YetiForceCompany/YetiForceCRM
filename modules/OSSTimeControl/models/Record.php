<?php

/**
 * OSSTimeControl record model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Recalculate status.
	 */
	const RECALCULATE_STATUS = 'Accepted';

	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();
		$date = new DateTime();
		$currDate = DateTimeField::convertToUserFormat($date->format('Y-m-d'));
		$time = $date->format('H:i');
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true&date_start='
			. $currDate . '&due_date=' . $currDate . '&time_start=' . $time . '&time_end=' . $time;
	}

	/** {@inheritdoc} */
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
