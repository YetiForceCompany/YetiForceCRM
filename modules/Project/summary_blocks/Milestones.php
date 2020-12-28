<?php

/**
 * Milestones file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * Milestones class.
 */
class Milestones
{
	public $name = 'LBL_MILESTONES';
	public $sequence = 1;
	public $reference = 'ProjectMilestone';
	public $icon = 'yfm-ProjectMilestone';
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
		$query = (new App\Db\Query())->from('vtiger_projectmilestone')
			->innerJoin('vtiger_crmentity', 'vtiger_projectmilestone.projectmilestoneid = vtiger_crmentity.crmid')
			->where(['vtiger_projectmilestone.projectid' => $recordModel->getId(), 'vtiger_crmentity.deleted' => 0]);
		$total = $query->count();
		$open = $query->andWhere(['vtiger_projectmilestone.projectmilestone_status' => ['PLL_IN_PROGRESSING', 'PLL_IN_APPROVAL']])->count();
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
