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
class Vtiger_UpcomingEventsModel_Dashboard extends Vtiger_Widget_Model
{
	/** {@inheritdoc} */
	public $customFields = [
		'skip_year' => ['label' => 'LBL_SKIP_YEAR', 'purifyType' => \App\Purifier::BOOL, 'tooltip' => 'LBL_SKIP_YEAR_DESC'],
		'date_fields' => ['label' => 'LBL_FIELD_DATE', 'purifyType' => \App\Purifier::TEXT]
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
			'label' => $this->getEditFields()[$name]['label'],
			'tooltip' => $this->getEditFields()[$name]['tooltip'] ?? ''
		];
		switch ($name) {
			case 'skip_year':
				$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~O';
				$params['fieldvalue'] = $data[$name] ?? 0;
				break;
			case 'date_fields':
				$data = $this->get('data') ? \App\Json::decode($this->get('data')) : [];
				$params['uitype'] = 16;
				$params['typeofdata'] = 'V~M';
				$params['picklistValues'] = $this->getFieldsByTypeDate();
				$params['fieldvalue'] = $data[$name] ?? '';
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
					case 'skip_year':
					case 'date_fields':
						$data[$fieldName] = $value;
						$this->set('data', \App\Json::encode($data));
						break;
					default: break;
				}
			}
		}
		parent::setDataFromRequest($request);
	}

	/**
	 * Get a date field.
	 *
	 * @return array
	 */
	public function getFieldsByTypeDate(): array
	{
		$query = (new \App\Db\Query())->select(['vtiger_field.fieldid', 'vtiger_field.fieldlabel', 'vtiger_field.tabid'])
			->from('vtiger_field')
			->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
			->where(['vtiger_field.presence' => [0, 2], 'vtiger_field.uitype' => [5, 6, 23]])
			->andWhere(['<>', 'vtiger_tab.presence', 1])->orderBy(['vtiger_field.tabid' => SORT_ASC, 'vtiger_field.sequence' => SORT_ASC]);
		$dataReader = $query->createCommand()->query();
		$fields = [];
		while ($row = $dataReader->read()) {
			$sourceModuleName = App\Module::getModuleName($row['tabid']);
			$fields[$row['fieldid']] = \App\Language::translate($sourceModuleName, $sourceModuleName) . ' - ' . \App\Language::translate($row['fieldlabel'], $sourceModuleName);
		}
		return $fields;
	}
}
