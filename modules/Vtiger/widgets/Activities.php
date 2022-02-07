<?php

/**
 * Activities widget - file.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Activities widget class.
 */
class Vtiger_Activities_Widget extends Vtiger_Basic_Widget
{
	/** {@inheritdoc} */
	public function isPermitted(): bool
	{
		return parent::isPermitted() && \App\Relation::getByModule($this->moduleModel->getName(), true, 'Calendar');
	}

	/**
	 * Get URL.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=getActivities&page=1&limit=' . $this->Data['limit'] . '&search_params=' . App\Json::encode([$this->getSearchParams('current')]) . '&orderby=' . App\Json::encode(['date_start' => 'ASC', 'time_start' => 'ASC']);
	}

	public function getConfigTplName()
	{
		return 'ActivitiesConfig';
	}

	public function getWidget()
	{
		$widget = [];
		$model = Vtiger_Module_Model::getInstance('Calendar');
		if ($model->isPermitted('DetailView')) {
			if (!isset($this->Data['switchTypeInHeader']) || '-' != $this->Data['switchTypeInHeader']) {
				$this->Config['switchTypeInHeader'] = [];
				$this->Config['switchTypeInHeader']['on'] = \App\Json::encode($this->getSearchParams('current'));
				$this->Config['switchTypeInHeader']['off'] = \App\Json::encode($this->getSearchParams('history'));
				$this->Config['switchHeaderLables']['on'] = \App\Language::translate('LBL_CURRENT', $model->getName());
				$this->Config['switchHeaderLables']['off'] = \App\Language::translate('LBL_HISTORY', $model->getName());
			}

			$this->Config['url'] = $this->getUrl();
			$this->Config['tpl'] = 'Activities.tpl';
			$widget = $this->Config;
		}
		return $widget;
	}

	/**
	 *  Function to get params to searching.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function getSearchParams(string $type): array
	{
		if ('history' === $type) {
			$values = Calendar_Module_Model::getComponentActivityStateLabel('history');
		} else {
			$values = Calendar_Module_Model::getComponentActivityStateLabel('current');
		}
		return [['activitystatus', 'e', implode('##', $values)]];
	}
}
