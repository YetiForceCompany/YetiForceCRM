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
class Vtiger_RssModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		return ['title' => ['label' => 'LBL_WIDGET_NAME', 'purifyType' => \App\Purifier::TEXT, 'required' => true]] + $this->editFields;
	}

	/** {@inheritdoc} */
	public function getFieldInstanceByName($name)
	{
		if (!isset($this->customFields[$name]) && 'channels' !== $name) {
			return parent::getFieldInstanceByName($name);
		}

		$moduleName = 'Settings:WidgetsManagement';
		$params = [
			'name' => $name,
			'label' => 'LBL_ADDRESS_RSS',
			'tooltip' => ''
		];
		switch ($name) {
			case 'channels':
				$params['uitype'] = 33;
				$params['typeofdata'] = 'V~M';
				$params['picklistValues'] = [];
				$owners = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
				$value = $owners[$name] ?? [];
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

		$fieldName = 'channels';
		if ($request->has($fieldName)) {
			$value = $request->getByType($fieldName, \App\Purifier::TEXT);
			$fieldModel = $this->getFieldInstanceByName($fieldName)->getUITypeModel();
			$fieldModel->validate($value, true);
			$value = $fieldModel->getDBValue($value);

			$value = $value ? explode(' |##| ', $value) : [];
			$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
			$data[$fieldName] = $value;
			$this->set('data', \App\Json::encode($data));
		}
	}
}
