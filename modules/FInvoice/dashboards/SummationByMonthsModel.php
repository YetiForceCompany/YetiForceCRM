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
class FInvoice_SummationByMonthsModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public $customFields = [
		'owners_all' => ['label' => 'LBL_FILTERS_AVAILABLE', 'purifyType' => \App\Purifier::STANDARD],
		'plotTickSize' => ['label' => 'LBL_TICK_SIZE', 'purifyType' => \App\Purifier::TEXT],
		'plotLimit' => ['label' => 'LBL_MAXIMUM_VALUE', 'purifyType' => \App\Purifier::TEXT]
	];

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		$fields = [];
		$fields['default_owner'] = ['label' => 'LBL_DEFAULT_FILTER', 'purifyType' => \App\Purifier::STANDARD];

		return $this->editFields + $fields + $this->customFields;
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
			case 'plotLimit':
			case 'plotTickSize':
				$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
				$params['uitype'] = 7;
				$params['maximumlength'] = '0,99999999999';
				$params['typeofdata'] = 'I~M';
				$params['fieldvalue'] = $data[$name] ?? 0;
				break;
			case 'owners_all':
				$params['uitype'] = 33;
				$params['maximumlength'] = '100';
				$params['typeofdata'] = 'V~M';
				$picklistValue = [
					'mine' => 'LBL_MINE',
					'all' => 'LBL_ALL',
					'users' => 'LBL_USERS',
					'groups' => 'LBL_GROUPS',
					'groupUsers' => 'LBL_GROUP_USERS',
					'roleUsers' => 'LBL_ROLE_USERS',
					'rsUsers' => 'LBL_ROLE_AND_SUBORDINATES_USERS'
				];
				foreach ($picklistValue as $key => $label) {
					$params['picklistValues'][$key] = \App\Language::translate($label, $moduleName);
				}
				$owners = $this->get('owners') ? \App\Json::decode($this->get('owners')) : [];
				$value = $owners['available'] ?? [];
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

				switch ($fieldName) {
					case 'plotLimit':
					case 'plotTickSize':
						$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
						$data[$fieldName] = $value;
						$this->set('data', \App\Json::encode($data));
						break;
					case 'owners_all':
						$value = $value ? explode(' |##| ', $value) : [];
						$owners = $this->get('owners') ? \App\Json::decode($this->get('owners')) : [];
						$owners['available'] = $value;
						$this->set('owners', \App\Json::encode($owners));
						break;
					default: break;
				}
			}
		}
	}
}
