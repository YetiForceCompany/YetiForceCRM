<?php
/**
 * Widget model for dashboard - file.
 *
 * @package   Dashboard
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Widget model for dashboard - class.
 */
class Vtiger_ChartFilterModel_Dashboard extends Vtiger_Widget_Model
{
	/** @var array Fields for chart modal view */
	public $fieldForChartModal = [
		'chartType' => ['label' => '', 'purifyType' => \App\Purifier::STANDARD],
		'chartModule' => ['label' => '', 'purifyType' => \App\Purifier::ALNUM],
		'filtersId' => ['label' => '', 'purifyType' => \App\Purifier::TEXT],
		'valueType' => ['label' => '', 'purifyType' => \App\Purifier::STANDARD],
		'groupField' => ['label' => '', 'purifyType' => \App\Purifier::ALNUM],
		'valueField' => ['label' => '', 'purifyType' => \App\Purifier::ALNUM],
		'sectorField' => ['label' => '', 'purifyType' => \App\Purifier::ALNUM],
		'dividingField' => ['label' => '', 'purifyType' => \App\Purifier::TEXT],
		'stacked' => ['label' => '', 'purifyType' => \App\Purifier::BOOL],
		'summary' => ['label' => '', 'purifyType' => \App\Purifier::BOOL],
		'sortOrder' => ['label' => '', 'purifyType' => \App\Purifier::STANDARD],
		'additionalFiltersFields' => ['label' => '', 'purifyType' => \App\Purifier::TEXT],
		'colorsFromFilter' => ['label' => '', 'purifyType' => \App\Purifier::BOOL],
		'colorsFromDividingField' => ['label' => '', 'purifyType' => \App\Purifier::BOOL]
	];

	/** {@inheritdoc} */
	public function getTitle()
	{
		$title = $this->get('title');
		if (empty($title) && !$this->getId()) {
			$title = $this->get('linklabel');
		} else {
			$miniListModel = new Vtiger_ChartFilter_Model();
			$miniListModel->setWidgetModel($this);
			$title = $miniListModel->getTitle();
		}
		return $title;
	}

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		return ['title' => ['label' => 'LBL_WIDGET_NAME', 'purifyType' => \App\Purifier::TEXT]] + parent::getEditFields();
	}

	/** {@inheritdoc} */
	public function getSettingsLinks()
	{
		$links = [];
		if ($this->getId() && \App\User::getCurrentUserModel()->isAdmin()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_EDIT_CHART_FILTER',
				'linkclass' => 'btn btn-primary btn-xs js-show-modal',
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkdata' => [
					'url' => "index.php?module=Home&view=ChartFilter&step=step1&linkId={$this->get('linkid')}&templateId={$this->getId()}",
					'module' => \App\Module::getModuleName($this->get('tabid')),
					'modalId' => \App\Layout::getUniqueId('ChartFilter')
				]
			]);
		}
		return array_merge($links, parent::getSettingsLinks());
	}

	/**
	 * Get value by field for edit view.
	 *
	 * @param string $name
	 */
	public function getValueForEditView(string $name)
	{
		$value = '';
		switch ($name) {
			case 'title':
				$value = $this->get('title') ?: '';
				break;
			case 'chartType':
			case 'module':
			case 'valueType':
			case 'groupField':
			case 'valueField':
			case 'sectorField':
			case 'dividingField':
			case 'stacked':
			case 'summary':
			case 'sortOrder':
				$value = $this->getDataValue($name) ?: '';
				break;
			case 'filterid':
				$value = $this->get($name) ? array_filter(explode(',', $this->get($name))) : [];
				break;
			case 'colorsFromDividingField':
			case 'colorsFromFilter':
				$value = $this->getId() ? $this->getDataValue($name) : 1;
				break;
			case 'additionalFiltersFields':
				$value = $this->getDataValue($name) ?: [];
				break;
			default:
				break;
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getFieldInstanceByName($name)
	{
		if (!isset($this->fieldForChartModal[$name]) || isset($this->getEditFields()[$name])) {
			return parent::getFieldInstanceByName($name);
		}

		$moduleName = 'Settings:WidgetsManagement';
		$params = [
			'name' => $name,
			'label' => $this->fieldForChartModal[$name]['label'] ?? ''
		];
		switch ($name) {
			case 'chartType':
			case 'chartModule':
			case 'valueType':
			case 'groupField':
			case 'valueField':
			case 'sectorField':
			case 'dividingField':
			case 'sortOrder':
				$params['uitype'] = 16;
				$params['typeofdata'] = 'V~M';
				$params['picklistValues'] = [];
				break;
			case 'filtersId':
			case 'additionalFiltersFields':
				$params['uitype'] = 33;
				$params['typeofdata'] = 'V~M';
				$params['picklistValues'] = [];
				break;
			case 'stacked':
			case 'summary':
			case 'colorsFromFilter':
			case 'colorsFromDividingField':
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~O';
				break;
			default: break;
		}
		return \Vtiger_Field_Model::init($moduleName, $params, $name);
	}

	/** {@inheritdoc} */
	public function setDataFromRequest(App\Request $request)
	{
		if (array_intersect(array_keys($this->fieldForChartModal), $request->getKeys())) {
			$this->set('data', []);
		}
		foreach ($this->fieldForChartModal as $fieldName => $fieldInfo) {
			if ($request->has($fieldName) && '' !== $request->get($fieldName)) {
				$value = $request->getByType($fieldName, $fieldInfo['purifyType']);
				$fieldModel = $this->getFieldInstanceByName($fieldName)->getUITypeModel();
				$fieldModel->validate($value, true);
				$value = $fieldModel->getDBValue($value);

				$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
				switch ($fieldName) {
					case 'chartType':
					case 'valueType':
					case 'groupField':
					case 'valueField':
					case 'sectorField':
					case 'dividingField':
					case 'sortOrder':
					case 'stacked':
					case 'summary':
					case 'colorsFromFilter':
					case 'colorsFromDividingField':
						$data[$fieldName] = $value;
						$this->set('data', \App\Json::encode($data));
						break;
					case 'chartModule':
						$data['module'] = $value;
						$this->set('data', \App\Json::encode($data));
						break;
					case 'filtersId':
						$value = $value ? explode(' |##| ', $value) : [];
						$this->set('filterid', implode(',', $value));
						break;
					case 'additionalFiltersFields':
						$value = $value ? explode(' |##| ', $value) : [];
						$data[$fieldName] = $value;
						$this->set('data', \App\Json::encode($data));
						break;
					default: break;
				}
			}
		}
		parent::setDataFromRequest($request);
	}

	/** {@inheritdoc} */
	public function isDeletable(): bool
	{
		return parent::isDeletable() && Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModuleActionPermission($this->get('tabid'), 'CreateDashboardChartFilter');
	}

	/** {@inheritdoc} */
	public function isViewable(): bool
	{
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = \App\Json::decode($this->get('data'))['module'];
		$customView = \App\CustomView::getInstance($moduleName);
		$filters = explode(',', $this->get('filterid'));

		return $userPrivModel->hasModulePermission($moduleName) && array_filter($filters, fn ($filterId) => $customView->isPermittedCustomView((int) $filterId));
	}

	/** {@inheritdoc} */
	public function isCreatable(): bool
	{
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		return $this->isViewable() && $userPrivModel->hasModuleActionPermission($this->get('module') ?: $this->get('tabid'), 'CreateDashboardChartFilter');
	}
}
