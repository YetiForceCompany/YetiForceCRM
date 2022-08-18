<?php

/**
 * Companies record model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Companies_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * List of types.
	 */
	public const TYPES = [1 => 'LBL_TYPE_TARGET_USER', 2 => 'LBL_TYPE_INTEGRATOR', 3 => 'LBL_TYPE_PROVIDER'];

	/**
	 * Function to get the Id.
	 *
	 * @return int Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get the Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @param mixed $step
	 *
	 * @return string URL
	 */
	public function getEditViewUrl($step = false)
	{
		return '?module=Companies&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url.
	 *
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		return '?module=Companies&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to get the instance of companies record model.
	 *
	 * @param int $id
	 *
	 * @return \self instance, if exists
	 */
	public static function getInstance($id)
	{
		$db = \App\Db::getInstance('admin');
		$row = (new \App\Db\Query())->from('s_#__companies')->where(['id' => $id])->one($db);
		$instance = false;
		if ($row) {
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}

	/**
	 * function to get clean instance.
	 *
	 * @return \static
	 */
	public static function getCleanInstance()
	{
		return new static();
	}

	/** {@inheritdoc} */
	public function get($key)
	{
		if ('newsletter' === $key && !empty(parent::get('email'))) {
			return 1;
		}
		return parent::get($key);
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_Companies_Module_Model
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
			$this->module = Settings_Vtiger_Module_Model::getInstance('Settings:Companies');
		}
		return $this->module;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = \App\Db::getInstance('admin');
		$recordId = $this->getId();
		$fields = $this->getModule()->getNameFields();
		$params = array_intersect_key($this->getData(), array_flip($fields));
		if ($recordId) {
			$db->createCommand()->update('s_#__companies', $params, ['id' => $recordId])->execute();
		} else {
			$db->createCommand()->insert('s_#__companies', $params)->execute();
			$this->set('id', $db->getLastInsertID('s_#__companies_id_seq'));
		}
		if ('LBL_TYPE_TARGET_USER' === self::TYPES[$params['type']] || 1 === (new \App\Db\Query())->from('s_#__companies')->count()) {
			$configFile = new \App\ConfigFile('component', 'Branding');
			$configFile->set('footerName', $params['name']);
			$configFile->set('urlFacebook', $params['facebook'] ?? '');
			$configFile->set('urlTwitter', $params['twitter'] ?? '');
			$configFile->set('urlLinkedIn', $params['linkedin'] ?? '');
			$configFile->create();
		}
		\App\Cache::clear();
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue(string $key)
	{
		$value = $this->get($key) ?? '';
		switch ($key) {
			case 'type':
				$value = \App\Language::translate(self::TYPES[$value], 'Settings::Companies');
				break;
			case 'status':
				$value = \App\Language::translate(\App\YetiForce\Register::STATUS_MESSAGES[(int) $value], 'Settings::Companies');
				break;
			case 'tabid':
				$value = \App\Module::getModuleName((int) $value);
				break;
			case 'industry':
				$value = App\Language::translate($value);
				break;
			case 'country':
				$value = \App\Language::translateSingleMod($value, 'Other.Country');
				break;
			case 'logo':
				$src = \App\Purifier::encodeHtml($value);
				$value = $src ? "<img src='$src' class='img-thumbnail sad'/>" : \App\Language::translate('LBL_COMPANY_LOGO', 'Settings::Companies');
				break;
			default:
				$value = \App\Purifier::encodeHtml($value);
				break;
		}
		return $value;
	}

	/**
	 * Function to delete the current Record Model.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		$delete = \App\Db::getInstance('admin')->createCommand()
			->delete('s_#__companies', ['id' => $this->getId()])
			->execute();
		if ($delete) {
			\App\Cache::clear();
		}
		return $delete;
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [];
		$recordLinks[] = [
			'linktype' => 'LISTVIEWRECORD',
			'linklabel' => 'LBL_EDIT_RECORD',
			'linkurl' => $this->getEditViewUrl(),
			'linkicon' => 'yfi yfi-full-editing-view',
			'linkclass' => 'btn btn-xs btn-info',
		];
		if (null === Settings_Companies_ListView_Model::$recordsCount) {
			Settings_Companies_ListView_Model::$recordsCount = (new \App\Db\Query())->from('s_#__companies')->count();
		}
		if (Settings_Companies_ListView_Model::$recordsCount > 1) {
			$recordLinks[] = [
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.deleteById('{$this->getId()}')",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-xs btn-danger',
			];
		}
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to save company logo.
	 *
	 * @throws \Exception
	 */
	public function saveCompanyLogos()
	{
		if (!empty($_FILES['logo']['name'])) {
			$fileInstance = \App\Fields\File::loadFromRequest($_FILES['logo']);
			if ($fileInstance->validateAndSecure('image')) {
				$this->set('logo', \App\Fields\File::getImageBaseData($fileInstance->getPath()));
			}
		}
	}

	/**
	 * Function to check if company duplicated.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool
	 */
	public function isCompanyDuplicated(App\Request $request)
	{
		$db = App\Db::getInstance('admin');
		$query = new \App\Db\Query();
		$query->from('s_#__companies')
			->where(['name' => $request->getByType('name', 'Text')]);
		if (!$request->isEmpty('record')) {
			$query->andWhere(['<>', 'id', $request->getInteger('record')]);
		}
		return $query->exists($db);
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @param string $name
	 * @param string $label
	 *
	 * @return \Settings_Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name, $label = '')
	{
		$moduleName = $this->getModule()->getName(true);
		$labels = $this->getModule()->getFormFields();
		$label = $label ?: ($labels[$name]['label'] ?? '');
		$sourceModule = $this->get('source');
		$companyId = $this->getId();
		$fieldName = 'YetiForce' === $sourceModule ? "companies[$companyId][$name]" : $name;
		$params = ['uitype' => 1, 'column' => $name, 'name' => $fieldName, 'value' => '', 'label' => $label, 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => '', 'isEditableReadOnly' => false, 'maximumlength' => '255'];
		switch ($name) {
			case 'name':
				unset($params['validator']);
				break;
			case 'industry':
				$params['uitype'] = 16;
				$params['maximumlength'] = '50';
				$params['picklistValues'] = [];
				foreach (Settings_Companies_Module_Model::getIndustryList() as $industry) {
					$params['picklistValues'][$industry] = \App\Language::translate($industry, $moduleName);
				}
				break;
			case 'city':
				$params['maximumlength'] = '100';
				unset($params['validator']);
				break;
			case 'country':
				$params['uitype'] = 16;
				$params['maximumlength'] = '100';
				$params['picklistValues'] = [];
				foreach (\App\Fields\Country::getAll() as $country) {
					$params['picklistValues'][$country['name']] = \App\Language::translateSingleMod($country['name'], 'Other.Country');
				}
				break;
			case 'companysize':
				$params['uitype'] = 7;
				$params['typeofdata'] = 'I~M';
				$params['maximumlength'] = '16777215';
				unset($params['validator']);
				break;
			case 'website':
			case 'facebook':
			case 'linkedin':
			case 'twitter':
				$params['uitype'] = 17;
				$params['typeofdata'] = 'V~O';
				unset($params['validator']);
				break;
			case 'firstname':
				unset($params['validator']);
				break;
			case 'lastname':
				break;
			case 'email':
				$params['uitype'] = 13;
				break;
			case 'newsletter':
				$params['typeofdata'] = 'V~O';
				$params['uitype'] = 56;
				unset($params['validator']);
				break;
			default:
				break;
		}
		return Settings_Vtiger_Field_Model::init($moduleName, $params);
	}
}
