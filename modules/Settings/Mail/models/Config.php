<?php

/**
 * Settings mail config model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Mail_Config_Model extends \App\Base
{
	public $baseTable = 'yetiforce_mail_config';
	public $name = 'Mail';
	public $type;

	/** @var array Record changes */
	protected $changes = [];

	public static function updateConfig($name, $val, $type)
	{
		\App\Db::getInstance()->createCommand()->update('yetiforce_mail_config', ['value' => $val], [
			'type' => $type,
			'name' => $name,
		])->execute();
	}

	public static function getConfig($type)
	{
		$config = [];
		$dataReader = (new \App\Db\Query())->select(['name', 'value'])
			->from('yetiforce_mail_config')
			->where(['type' => $type])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$config[$row['name']] = $row['value'];
		}
		$dataReader->close();

		return $config;
	}

	public static function acceptanceRecord($id)
	{
		\App\Db::getInstance('admin')->createCommand()->update('s_#__mail_queue', ['status' => 1], [
			'id' => $id,
		])->execute();
	}

	/**
	 * Function to get instance.
	 *
	 * @param string $type
	 *
	 * @return Settings_Mail_Config_Model
	 */
	public static function getInstance(string $type)
	{
		$instance = new static();
		$instance->type = $type;
		$instance->loadConfig();
		return $instance;
	}

	private function loadConfig()
	{
		$data = (new \App\Db\Query())->select(['name', 'value'])
			->from('yetiforce_mail_config')
			->where(['type' => $this->type])->orderBy(['sequence' => SORT_ASC])->createCommand()->queryAllByGroup();
		$this->setData($data);

		return $this;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = App\Db::getInstance('admin');
		$transaction = $db->beginTransaction();
		try {
			$this->saveToDb();
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			\App\Log::error($ex->__toString());
			throw $ex;
		}
		// \App\Cache::delete('MailServer', 'all');
	}

	/**
	 * Save data to the database.
	 */
	public function saveToDb()
	{
		$db = \App\Db::getInstance('admin');
		$tablesData = array_intersect_key($this->getData(), $this->changes);
		foreach ($tablesData as $key => $value) {
			$db->createCommand()->update($this->baseTable, ['value' => $value], ['type' => $this->type, 'name' => $key])->execute();
		}
	}

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		if (\array_key_exists($key, $this->value) && $this->value[$key] !== $value) {
			$this->changes[$key] = $this->get($key);
		}
		return parent::set($key, $value);
	}

	public function getFields()
	{
		// $configs = [
		// 	'scanner' => [
		// 		'domain_exceptions',
		// 		'email_exceptions',
		// 	]
		// ];
		$fields = [];
		// echo '<pre>', print_r([$this]);
		// echo '</pre>';
		// exit;
		foreach ($this->getData() as $fieldName => $value) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			if ($fieldModel) {
				$fieldModel->set('fieldvalue', $value);
				$fields[$fieldName] = $fieldModel;
			}
		}

		return $fields;
	}

	/**
	 * Get fields instance by name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstanceByName(string $name)
	{
		$moduleName = 'Settings:' . $this->name;
		$params = [];
		switch ($name) {
			case 'domain_exceptions':
				$params = [
					'name' => $name,
					'label' => 'FL_DOMAIN_EXCEPTIONS',
					'uitype' => 319,
					'typeofdata' => 'V~O',
					'maximumlength' => '6500',
					'defaultvalue' => '',
					'purifyType' => \App\Purifier::TEXT,
					'config' => 'scanner',
					'fieldparams' => \App\Json::encode(['container_class' => 'col-md-12'])
				];
				break;
			case 'email_exceptions':
				$params = [
					'name' => $name,
					'label' => 'FL_EMAIL_EXCEPTIONS',
					'uitype' => 314,
					'typeofdata' => 'V~O',
					'maximumlength' => '6500',
					'defaultvalue' => '',
					'purifyType' => [\App\Purifier::TEXT],
					'config' => 'scanner',
					'fieldparams' => \App\Json::encode(['container_class' => 'col-md-7'])
				];
				break;
			case 'showMailIcon':
				$params = [
					'name' => $name,
					'label' => 'LBL_SHOW_MAIL_ICON',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '0',
					'defaultvalue' => 0,
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'showNumberUnreadEmails':
				$params = [
					'name' => $name,
					'label' => 'LBL_NUMBER_UNREAD_EMAILS',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '0',
					'defaultvalue' => 0,
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'addSignature':
				$params = [
					'name' => $name,
					'label' => 'LBL_ADD_SIGNATURE',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '0',
					'defaultvalue' => 0,
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'signature':
				$params = [
					'name' => $name,
					'label' => '',
					'uitype' => 300,
					'typeofdata' => 'V~O',
					'maximumlength' => '6500',
					'defaultvalue' => '',
					'purifyType' => \App\Purifier::HTML,
					'fieldparams' => \App\Json::encode(['variablePanel' => true])
				];
				break;
			default:
				break;
		}

		return $params ? \Vtiger_Field_Model::init($moduleName, $params, $name) : null;
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
		return $fieldName ? ($this->changes[$fieldName] ?? null) : $this->changes;
	}
}
