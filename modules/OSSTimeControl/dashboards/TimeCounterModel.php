<?php
/**
 * Widget model for dashboard - file.
 *
 * @package   Dashboard
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Widget model for dashboard - class.
 */
class OSSTimeControl_TimeCounterModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public $customFields = [
		'default_time' => ['label' => 'LBL_DEFAULT_TIME', 'purifyType' => \App\Purifier::TEXT],
	];

	/** {@inheritdoc} */
	public $editFields = [
		'isdefault' => ['label' => 'LBL_MANDATORY_WIDGET', 'purifyType' => \App\Purifier::BOOL],
		'width' => ['label' => 'LBL_WIDTH', 'purifyType' => \App\Purifier::INTEGER],
		'height' => ['label' => 'LBL_HEIGHT', 'purifyType' => \App\Purifier::INTEGER],
	];

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		return $this->editFields + $this->customFields;
	}

	/** {@inheritdoc} */
	public function getFieldInstanceByName($name)
	{
		$moduleName = 'Settings:WidgetsManagement';
		if (!isset($this->customFields[$name])) {
			return parent::getFieldInstanceByName($name);
		}
		$params = [
			'name' => $name,
			'label' => $this->customFields[$name]['label'],
			'tooltip' => $this->customFields[$name]['tooltip'] ?? ''
		];
		if ('default_time' === $name) {
			$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
			$params['uitype'] = 33;
			$params['typeofdata'] = 'V~O';
			$params['picklistValues'] = [
				'15' => '15',
				'30' => '30',
				'40' => '40',
				'60' => '60',
				'90' => '90',
			];
			$value = $data[$name] ?? [];
			$params['fieldvalue'] = implode(' |##| ', $value);
		}
		return \Vtiger_Field_Model::init($moduleName, $params, $name);
	}

	/** {@inheritdoc} */
	public function setDataFromRequest(App\Request $request)
	{
		parent::setDataFromRequest($request);
		foreach ($this->customFields as $fieldName => $fieldInfo) {
			if ($request->has($fieldName) && 'default_time' === $fieldName) {
				$value = $request->getByType($fieldName, $fieldInfo['purifyType']);
				$fieldModel = $this->getFieldInstanceByName($fieldName)->getUITypeModel();
				$fieldModel->validate($value, true);
				$value = $fieldModel->getDBValue($value);
				$data[$fieldName] = $value ? explode(' |##| ', $value) : [];
				$this->set('data', \App\Json::encode($data));
			}
		}
	}

	/** {@inheritdoc} */
	public function getTitle()
	{
		return \App\Language::translate($this->get('linklabel'), 'Dashboard');
	}
}
