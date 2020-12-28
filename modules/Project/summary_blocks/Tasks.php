<?php

/**
 * Tasks file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * Tasks class.
 */
class Tasks
{
	public $name = 'LBL_TASKS_LIST';
	public $sequence = 2;
	public $reference = 'ProjectTask';
	public $icon = 'yfm-ProjectTask';
	public $type = 'badge';

	/**
	 * Process.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		$query = (new App\Db\Query())->from('vtiger_projecttask')
			->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')
			->where(['vtiger_projecttask.projectid' => $recordModel->getId(), 'vtiger_crmentity.deleted' => 0]);
		$total = $query->count();
		$open = $query->andWhere(['vtiger_projecttask.projecttaskstatus' => ['PLL_IN_PROGRESSING', 'PLL_IN_APPROVAL', 'PLL_SUBMITTED_COMMENTS']])->count();
		return [
			[
				'label' => \App\Language::translate('LBL_OPEN'),
				'value' => $open,
				'class' => 'badge-success'
			], [
				'label' => \App\Language::translate('LBL_ALL'),
				'value' => $total,
				'class' => 'badge-secondary'
			]
		];
	}
}
