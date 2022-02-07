<?php

/**
 * Summary block for list of stages file.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * Summary block for list of stages class.
 */
class Milestones
{
	/** @var string Block name */
	public $name = 'LBL_MILESTONES';

	/** @var int Block sequence */
	public $sequence = 1;

	/** @var string Block reference */
	public $reference = 'ProjectMilestone';

	/** @var string Block icon */
	public $icon = 'yfm-ProjectMilestone';

	/** @var string Block type */
	public $type = 'badge';

	/** @var array Block status */
	public $status = ['PLL_IN_PROGRESSING', 'PLL_IN_APPROVAL'];

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
		$query = (new App\Db\Query())->from('vtiger_projectmilestone')
			->innerJoin('vtiger_crmentity', 'vtiger_projectmilestone.projectmilestoneid = vtiger_crmentity.crmid')
			->where(['vtiger_projectmilestone.projectid' => $recordModel->getId(), 'vtiger_crmentity.deleted' => 0]);
		$total = $query->count();
		$open = $query->andWhere(['vtiger_projectmilestone.projectmilestone_status' => $this->status])->count();
		return [
			[
				'label' => \App\Language::translate('LBL_OPEN'),
				'value' => $open,
				'class' => 'badge-success',
				'badgeLink' => $badgeLink . '&search_params=' . urlencode(\App\Json::encode([[['projectmilestone_status', 'e', $this->status]]]))
			], [
				'label' => \App\Language::translate('LBL_ALL'),
				'value' => $total,
				'class' => 'badge-secondary',
				'badgeLink' => $badgeLink
			]
		];
	}
}
