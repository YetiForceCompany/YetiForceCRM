<?php

/**
 * Related module - chart.
 *
 * @package   Widget
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Chart for related module.
 */
class Vtiger_RelatedModuleChart_Widget extends Vtiger_Basic_Widget
{
	/**
	 * Gets URL.
	 *
	 * @return string
	 */
	public function getUrl(): string
	{
		$moduleName = is_numeric($this->Data['relatedmodule']) ? App\Module::getModuleName($this->Data['relatedmodule']) : $this->Data['relatedmodule'];
		$url = 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showCharts&relatedModule=' . $moduleName . '&chartType=' . $this->Data['chartType'] . '&valueType=' . $this->Data['valueType'] . '&relationId=' . $this->Data['relation_id'] . '&widgetId=' . $this->Config['id'] . '&search_params=' . App\Json::encode([$this->getSearchParams()]);
		if (isset($this->Data['no_result_text'])) {
			$url .= '&no_result_text=' . $this->Data['no_result_text'];
		}
		if (!empty($this->Data['valueField'])) {
			$url .= '&valueField=' . $this->Data['valueField'];
		}
		return $url;
	}

	/** {@inheritdoc} */
	public function getWidget()
	{
		$widget = [];
		$model = Vtiger_Module_Model::getInstance($this->Data['relatedmodule']);
		if ($model->isPermitted('DetailView')) {
			$this->Config['url'] = $this->getUrl();
			$this->Config['tpl'] = 'Basic.tpl';
			$widget = $this->Config;
		}
		return $widget;
	}

	/**
	 * Function to get params to searching.
	 *
	 * @return array
	 */
	public function getSearchParams(): array
	{
		$params = [];
		if (!empty($this->Data['search_params'])) {
			$params = array_merge($params, $this->Data['search_params']);
		}
		return $params;
	}

	/** {@inheritdoc} */
	public function getConfigTplName()
	{
		return 'RelatedModuleChartConfig';
	}
}
