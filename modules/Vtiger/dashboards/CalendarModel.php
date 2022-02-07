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
class Vtiger_CalendarModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public $customFields = [
		'defaultFilter' => ['label' => 'LBL_DEFAULT_LIST_FILTER', 'purifyType' => \App\Purifier::INTEGER]
	];

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		$fields = [];
		$fields['default_owner'] = ['label' => 'LBL_DEFAULT_FILTER', 'purifyType' => \App\Purifier::STANDARD];
		$fields['owners_all'] = ['label' => 'LBL_FILTERS_AVAILABLE', 'purifyType' => \App\Purifier::STANDARD];

		return parent::getEditFields() + $fields;
	}

	/** {@inheritdoc} */
	public function getFieldInstanceByName($name)
	{
		if (!isset($this->customFields[$name])) {
			return parent::getFieldInstanceByName($name);
		}
		$moduleName = 'Settings:WidgetsManagement';
		$params = [
			'name' => $name,
			'label' => $this->getEditFields()[$name]['label']
		];
		if ('defaultFilter' === $name) {
			$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
			$params['uitype'] = 16;
			$params['typeofdata'] = 'V~M';
			$picklistValue = [];
			$sourceModuleName = 'Calendar';
			foreach (\App\CustomView::getFiltersByModule($sourceModuleName) as $key => $cvData) {
				$picklistValue[$key] = \App\Language::translate($cvData['viewname'], $sourceModuleName);
			}
			$params['picklistValues'] = $picklistValue;
			$params['fieldvalue'] = $data[$name] ?? '';
		}
		return \Vtiger_Field_Model::init($moduleName, $params, $name);
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

				$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
				$data[$fieldName] = $value;
				$this->set('data', \App\Json::encode($data));
			}
		}
		parent::setDataFromRequest($request);
	}
}
