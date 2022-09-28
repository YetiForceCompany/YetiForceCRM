<?php
/**
 * Calendar activities model for dashboard - file.
 *
 * @package   Dashboard
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Calendar activities model for dashboard - class.
 */
class Vtiger_CalendarActivitiesModel_Dashboard extends Vtiger_Widget_Model
{
	/** @var string Module name */
	protected $moduleName = 'Calendar';

	/** {@inheritdoc} */
	public $customFields = [
		'baseModuleFilter' => ['label' => 'LBL_SELECT_BASE_MODULE_FILTER', 'purifyType' => \App\Purifier::TEXT],
		'additionalFiltersFields' => ['label' => 'LBL_SELECT_ADDITIONAL_FILTER_FIELDS', 'purifyType' => App\Purifier::TEXT]
	];

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		$fields['title'] = ['label' => 'LBL_WIDGET_NAME', 'purifyType' => \App\Purifier::TEXT];
		return $fields + parent::getEditFields();
	}

	/** {@inheritdoc} */
	public function getFieldInstanceByName($name)
	{
		if (!isset($this->customFields[$name])) {
			return parent::getFieldInstanceByName($name);
		}
		$params = [
			'name' => $name,
			'label' => $this->getEditFields()[$name]['label'],
			'tooltip' => $this->getEditFields()[$name]['tooltip'] ?? ''
		];
		if ('baseModuleFilter' === $name) {
			$params['uitype'] = 16;
			$params['typeofdata'] = 'V~O';
			$params['picklistValues'] = $this->getCalendarFilters();
			$params['fieldvalue'] = $this->get('filterid') ?: '';
		}
		if ('additionalFiltersFields' === $name) {
			$params['uitype'] = 33;
			$params['typeofdata'] = 'V~O';
			$params['picklistValues'] = [
				'activitytype' => App\Language::translate('Activity Type', $this->moduleName),
				'taskpriority' => App\Language::translate('Priority', $this->moduleName),
				'owner' => App\Language::translate('LBL_ASSIGNED_TO', $this->moduleName),
			];
			$dataValue = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
			$value = $dataValue[$name] ?? [];
			$params['fieldvalue'] = implode(' |##| ', $value);
		}

		return \Vtiger_Field_Model::init('Settings:WidgetsManagement', $params, $name);
	}

	/**
	 * Get calendar module filters.
	 *
	 * @return array
	 */
	protected function getCalendarFilters(): array
	{
		$filtersForPicklist = [];
		foreach (App\CustomView::getFiltersByModule('Calendar') as $filterId => $filter) {
			if ($filter['setmetrics']) {
				$filtersForPicklist[$filterId] = App\Language::translate($filter['viewname'], $this->moduleName);
			}
		}
		return $filtersForPicklist;
	}

	/** {@inheritdoc} */
	public function setDataFromRequest(App\Request $request)
	{
		foreach ($this->customFields as $fieldName => $fieldInfo) {
			if ($request->has($fieldName)) {
				$value = $request->getByType($fieldName, $fieldInfo['purifyType']);
				$fieldModel = $this->getFieldInstanceByName($fieldName)->getUITypeModel();
				$fieldModel->validate($value, true);
				$value = $fieldModel->getDBValue($value);
				if ('baseModuleFilter' === $fieldName) {
					$this->set('filterid', (int) $value);
				}
				if ('additionalFiltersFields' === $fieldName) {
					$value = $value ? explode(' |##| ', $value) : [];
					$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
					$data[$fieldName] = $value;
					$this->set('data', \App\Json::encode($data));
				}
			}
		}
		parent::setDataFromRequest($request);
	}

	/** {@inheritdoc} */
	public function isViewable(): bool
	{
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$isPermittedToCustomView = true;
		if ($filterId = $this->get('filterid')) {
			$isPermittedToCustomView = \App\CustomView::getInstance($this->moduleName)->isPermittedCustomView((int) $filterId);
		}
		return $userPrivModel->hasModulePermission($this->moduleName) && $isPermittedToCustomView;
	}
}
