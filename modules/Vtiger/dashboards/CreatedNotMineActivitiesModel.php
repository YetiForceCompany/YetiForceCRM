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
class Vtiger_CreatedNotMineActivitiesModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public $customFields = [
		'default_owner' => ['label' => 'LBL_DEFAULT_FILTER', 'purifyType' => \App\Purifier::STANDARD],
		'owners_all' => ['label' => 'LBL_FILTERS_AVAILABLE', 'purifyType' => \App\Purifier::STANDARD]
	];

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		return $this->editFields + $this->customFields + [
			'limit' => ['label' => 'LBL_NUMBER_OF_RECORDS_DISPLAYED', 'purifyType' => \App\Purifier::INTEGER]
		];
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
			'label' => $this->getEditFields()[$name]['label'],
			'tooltip' => $this->getEditFields()[$name]['tooltip'] ?? ''
		];
		switch ($name) {
			case 'default_owner':
				$params['uitype'] = 16;
				$params['typeofdata'] = 'V~M';
				$picklistValue = ['all' => 'LBL_ALL'];
				foreach ($picklistValue as $key => $label) {
					$params['picklistValues'][$key] = \App\Language::translate($label, $moduleName);
				}
				$value = $this->get('owners') ? \App\Json::decode($this->get('owners')) : [];
				$params['fieldvalue'] = $value['default'] ?? 'all';
				break;
			case 'owners_all':
				$params['uitype'] = 33;
				$params['typeofdata'] = 'V~M';
				$picklistValue = [
					'all' => 'LBL_ALL',
					'users' => 'LBL_USERS',
					'groups' => 'LBL_GROUPS'
				];
				foreach ($picklistValue as $key => $label) {
					$params['picklistValues'][$key] = \App\Language::translate($label, $moduleName);
				}
				$owners = $this->get('owners') ? \App\Json::decode($this->get('owners')) : [];
				$value = $owners['available'] ?? ['all'];
				$params['fieldvalue'] = implode(' |##| ', $value);
				break;
			default: break;
		}
		return \Vtiger_Field_Model::init($moduleName, $params, $name);
	}

	/** {@inheritdoc} */
	public function setDataFromRequest(App\Request $request)
	{
		parent::setDataFromRequest($request);
		foreach ($this->customFields as $fieldName => $fieldInfo) {
			if ($request->has($fieldName)) {
				$value = $request->getByType($fieldName, $fieldInfo['purifyType']);
				$fieldModel = $this->getFieldInstanceByName($fieldName)->getUITypeModel();
				$fieldModel->validate($value, true);
				$value = $fieldModel->getDBValue($value);

				$owners = $this->get('owners') ? \App\Json::decode($this->get('owners')) : [];
				switch ($fieldName) {
					case 'default_owner':
						$owners['default'] = $value;
						$this->set('owners', \App\Json::encode($owners));
						break;
					case 'owners_all':
						$value = $value ? explode(' |##| ', $value) : [];
						$owners['available'] = $value;
						$this->set('owners', \App\Json::encode($owners));
						break;
					default: break;
				}
			}
		}
	}
}
