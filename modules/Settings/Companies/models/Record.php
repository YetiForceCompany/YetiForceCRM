<?php

/**
 * Companies record model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Companies_Record_Model extends Settings_Vtiger_Record_Model
{
	public $module;
	/** @var array Record changes */
	protected $changes = [];

	/**
	 * Function to get the Id.
	 *
	 * @return int Id
	 */
	public function getId(): int
	{
		return $this->get('id');
	}

	/**
	 * Function to get the Name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->get('name');
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @return string URL
	 */
	public function getEditViewUrl(): string
	{
		return '?module=Companies&parent=Settings&view=Edit';
	}

	/**
	 * Function to get the instance of companies record model.
	 *
	 * @throws \App\Exceptions\DbException
	 *
	 * @return bool|Settings_Companies_Record_Model instance, if exists
	 */
	public static function getInstance()
	{
		$db = \App\Db::getInstance('admin');
		$row = (new \App\Db\Query())->from('s_#__companies')->one($db);
		if ($row) {
			$instance = new self();
			$instance->setData($row);

			return $instance;
		} else {
			throw new \App\Exceptions\DbException('LBL_RECORD_NOT_FOUND');
		}
	}

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		if ($this->getId() && !\in_array($key, ['id']) && (\array_key_exists($key, $this->value) && $this->value[$key] != $value)) {
			$this->changes[$key] = $this->get($key);
		}
		return parent::set($key, $value);
	}

	/**
	 * Get pervious value by field.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getPreviousValue(string $fieldName = '')
	{
		return $fieldName ? (array_key_exists($fieldName, $this->changes) ? $this->changes[$fieldName] : false) : $this->changes;
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_Companies_Module_Model
	 */
	public function getModule(): Settings_Companies_Module_Model
	{
		if (!isset($this->module)) {
			/** @var Settings_Companies_Module_Model $module */
			$module = Settings_Vtiger_Module_Model::getInstance('Settings:Companies');
			$this->module = $module;
		}

		return $this->module;
	}

	/**
	 * Function to save.
	 */
	public function save(): void
	{
		$db = App\Db::getInstance('admin');
		$transaction = $db->beginTransaction();

		try {
			if (($changes = $this->getPreviousValue()) && (\count($changes) > 1) || false === $this->getPreviousValue('email')) {
				$registration = (new \App\YetiForce\Register());
				$registration->setRawCompanyData($this->getData())->register();
				if ($error = $registration->getError()) {
					throw new \App\Exceptions\AppException($error);
				}
				\App\Process::removeEvent(\Settings_Companies_EditModal_View::MODAL_EVENT['name']);
			}
			$fields = $this->getModule()->getNameFields();
			$params = array_intersect_key($this->getData(), array_flip($fields));
			$db->createCommand()->update('s_#__companies', $params, ['id' => $this->getId()])->execute();
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
			throw $ex;
		}

		\App\Cache::clear();
		\App\Cache::staticDelete('CompanyGet', '');
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue(string $key): string
	{
		$value = $this->get($key) ?? '';

		switch ($key) {
			case 'tabid':
				$value = \App\Module::getModuleName((int) $value);
				break;
			case 'industry':
				$value = App\Language::translate($value);
				break;
			case 'country':
				$value =\App\Language::translateSingleMod($value, 'Other.Country');
				break;
			default:
				$value =\App\Purifier::encodeHtml($value);
				break;
		}

		return $value;
	}

	/**
	 * Function to delete the current Record Model.
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		throw new \App\Exceptions\NoPermitted('LBL_OPERATION_NOT_PERMITTED');
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @param string $name
	 * @param string $label
	 *
	 * @return \Vtiger_Field_Model
	 */
	public function getFieldInstanceByName(string $name, string $label = ''): Vtiger_Field_Model
	{
		$moduleName = $this->getModule()->getName(true);
		$labels = $this->getModule()->getFormFields();
		$label = $label ?: ($labels[$name]['label'] ?? '');
		$sourceModule = $this->get('source');
		$companyId = $this->getId();
		$fieldName = 'YetiForce' === $sourceModule ? "companies[$companyId][$name]" : $name;
		$params = [
			'uitype' => 1,
			'column' => $name,
			'name' => $fieldName,
			'value' => '',
			'label' => $label,
			'displaytype' => 1,
			'typeofdata' => 'V~M',
			'presence' => '',
			'isEditableReadOnly' => false,
			'maximumlength' => '255',
			'purifyType' => \App\Purifier::TEXT
		];
		switch ($name) {
			case 'name':
				unset($params['validator']);
				break;
			case 'industry':
				$params['uitype'] = 16;
				$params['maximumlength'] = '50';
				$params['picklistValues'] = [];
				foreach (Settings_Companies_Module_Model::getIndustryList() as $industry) {
					$params['picklistValues'][$industry] = \App\Language::translate($industry, $moduleName, null, false);
				}
				break;
			case 'country':
				$params['uitype'] = 16;
				$params['maximumlength'] = '100';
				$params['picklistValues'] = [];
				foreach (\App\Fields\Country::getAll() as $country) {
					$params['picklistValues'][$country['name']] = \App\Language::translateSingleMod($country['name'], 'Other.Country', null, false);
				}
				break;
			case 'email':
				$params['uitype'] = 13;
				$params['purifyType'] = \App\Purifier::EMAIL;
				$params['isEditableReadOnly'] = true;
				break;
			case 'website':
				$params['uitype'] = 17;
				$params['typeofdata'] = 'V~O';
				unset($params['validator']);
				$params['purifyType'] = \App\Purifier::URL;
				break;
			default:
				break;
		}

		return Settings_Vtiger_Field_Model::init($moduleName, $params);
	}
}
