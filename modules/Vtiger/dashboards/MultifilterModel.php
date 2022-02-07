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
class Vtiger_MultifilterModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public $customFields = [
		'showFullName' => ['label' => 'LBL_SHOW_FULL_NAME', 'purifyType' => \App\Purifier::BOOL],
		'customMultiFilter' => ['label' => 'LBL_FILTERS_AVAILABLE', 'purifyType' => \App\Purifier::TEXT]
	];

	/** {@inheritdoc} */
	public function getEditFields(): array
	{
		$fields = ['title' => ['label' => 'LBL_WIDGET_NAME', 'purifyType' => \App\Purifier::TEXT, 'required' => true]];
		return $fields + parent::getEditFields() + ['limit' => ['label' => 'LBL_NUMBER_OF_RECORDS_DISPLAYED', 'purifyType' => \App\Purifier::INTEGER]];
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
		switch ($name) {
			case 'showFullName':
				$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~O';
				$params['fieldvalue'] = $data[$name] ?? 0;
				break;
			case 'customMultiFilter':
				$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
				$params['uitype'] = 33;
				$params['typeofdata'] = 'V~M';
				$picklistValue = $cvByModule = [];
				foreach (CustomView_Record_Model::getAll('', false) as $key => $cv) {
					$sourceModuleName = $cv->getModule()->getName();
					$cvByModule[$sourceModuleName][$key] = \App\Language::translate($sourceModuleName, $sourceModuleName) . ' - ' . \App\Language::translate($cv->getName(), $sourceModuleName);
				}
				$cvIt = new RecursiveIteratorIterator(new RecursiveArrayIterator($cvByModule));
				foreach ($cvIt as $key => $val) {
					$picklistValue[(string) $key] = $val;
				}
				$params['picklistValues'] = $picklistValue;
				$value = $data[$name] ?? [];
				$params['fieldvalue'] = implode(' |##| ', $value);
				break;
			default: break;
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
				switch ($fieldName) {
					case 'showFullName':
						$data[$fieldName] = $value;
						$this->set('data', \App\Json::encode($data));
						break;
					case 'customMultiFilter':
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
}
