<?php

/**
 * Summary block for a task list file.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * Summary block for a task list class.
 */
class Tasks
{
	/** @var string Block name */
	public $name = 'LBL_TASKS_LIST';

	/** @var int Block sequence */
	public $sequence = 2;

	/** @var string Block reference */
	public $reference = 'ProjectTask';

	/** @var string Block icon */
	public $icon = 'yfm-ProjectTask';

	/** @var string Block type */
	public $type = 'badge';

	/** @var array Block type */
	public $status = ['PLL_IN_PROGRESSING', 'PLL_IN_APPROVAL', 'PLL_SUBMITTED_COMMENTS'];

	/**
	 * Process.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		$badgeLink = \Vtiger_Relation_Model::getInstance(\Vtiger_Module_Model::getInstance($recordModel->getModuleName()), \Vtiger_Module_Model::getInstance($this->reference))->getListUrl($recordModel);
		$query = (new App\Db\Query())->from('vtiger_projecttask')
			->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')
			->where(['vtiger_projecttask.projectid' => $recordModel->getId(), 'vtiger_crmentity.deleted' => 0]);
		$total = $query->count();
		$open = $query->andWhere(['vtiger_projecttask.projecttaskstatus' => $this->status])->count();
		return [
			[
				'label' => \App\Language::translate('LBL_OPEN'),
				'value' => $open,
				'class' => 'badge-success',
				'badgeLink' => $badgeLink . '&search_params=' . urlencode(\App\Json::encode([[['projecttaskstatus', 'e', $this->status]]]))
			], [
				'label' => \App\Language::translate('LBL_ALL'),
				'value' => $total,
				'class' => 'badge-secondary',
				'badgeLink' => $badgeLink
			]
		];
	}
}
