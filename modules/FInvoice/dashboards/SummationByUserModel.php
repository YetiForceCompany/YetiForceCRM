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
class FInvoice_SummationByUserModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public $customFields = [
		'showUsers' => ['label' => 'LBL_SHOW_USERS', 'purifyType' => \App\Purifier::BOOL]
	];

	/** {@inheritdoc} */
	public function getFieldInstanceByName($name)
	{
		if (!isset($this->customFields[$name])) {
			return parent::getFieldInstanceByName($name);
		}
		$moduleName = 'Settings:WidgetsManagement';
		$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
		$params = [
			'name' => $name,
			'label' => $this->getEditFields()[$name]['label'],
			'uitype' => 56,
			'typeofdata' => 'C~O',
			'fieldvalue' => $data[$name] ?? 0
		];
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

				$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
				$data[$fieldName] = $value;
				$this->set('data', \App\Json::encode($data));
			}
		}
	}
}
