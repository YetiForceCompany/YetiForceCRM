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

	public static function acceptanceRecord(int $id)
	{
		return \App\Db::getInstance('admin')->createCommand()->update('s_#__mail_queue', ['status' => 1], ['id' => $id])->execute();
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
			->from($this->baseTable)
			->where(['type' => $this->type])
			->orderBy(['sequence' => SORT_ASC])->createCommand()->queryAllByGroup();
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
		\App\Cache::delete('MailConfiguration', $this->type);
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

	/**
	 * Get fields structure.
	 *
	 * @param bool $byBlock
	 *
	 * @return array
	 */
	public function getFields(bool $byBlock = false)
	{
		$fields = [];
		foreach ($this->getData() as $fieldName => $value) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			if ($fieldModel) {
				$fieldModel->set('fieldvalue', $value);
				if ($byBlock) {
					$blockLabel = $fieldModel->get('blockLabel') ?: '';
					$fields[$blockLabel][$fieldName] = $fieldModel;
				} else {
					$fields[$fieldName] = $fieldModel;
				}
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
					'label' => 'FL_SCANNER_DOMAIN_EXCEPTIONS',
					'uitype' => 319,
					'typeofdata' => 'V~O',
					'maximumlength' => '6500',
					'defaultvalue' => '',
					'purifyType' => \App\Purifier::TEXT,
					'tooltip' => 'LBL_SCANNER_DOMAIN_EXCEPTIONS_DESC',
					'fieldparams' => \App\Json::encode(['container_class' => 'col-md-7']),
					'blockLabel' => 'BL_SCANNER_EXCEPTIONS'
				];
				break;
			case 'email_exceptions':
				$params = [
					'name' => $name,
					'label' => 'FL_SCANNER_EMAIL_EXCEPTIONS',
					'uitype' => 314,
					'typeofdata' => 'V~O',
					'maximumlength' => '6500',
					'defaultvalue' => '',
					'purifyType' => [\App\Purifier::TEXT],
					'tooltip' => 'LBL_SCANNER_EMAIL_EXCEPTIONS_DESC',
					'fieldparams' => \App\Json::encode(['container_class' => 'col-md-5']),
					'blockLabel' => 'BL_SCANNER_EXCEPTIONS'
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
					'purifyType' => \App\Purifier::BOOL
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
					'purifyType' => \App\Purifier::BOOL
				];
				break;
			case 'timeCheckingMail':
				$params = [
					'name' => $name,
					'label' => 'LBL_TIME_CHECKING_MAIL',
					'uitype' => 7,
					'typeofdata' => 'I~O',
					'maximumlength' => '5,86400',
					'defaultvalue' => 30,
					'purifyType' => \App\Purifier::TEXT,
					'tooltip' => 'LBL_TIME_CHECKING_MAIL_DESC',
					'fieldparams' => \App\Json::encode(['container_class' => 'col-md-4']),
				];
				break;
			case 'time_for_notification':
				$params = [
					'name' => $name,
					'label' => 'FL_SCANNER_TIME_FOR_NOTIFICATION',
					'uitype' => 7,
					'typeofdata' => 'I~O',
					'maximumlength' => '0,44640',
					'defaultvalue' => 0,
					'tooltip' => 'LBL_SCANNER_TIME_FOR_NOTIFICATION_DESC',
					'purifyType' => \App\Purifier::TEXT,
					'fieldparams' => \App\Json::encode(['container_class' => 'col-md-6']),
					'blockLabel' => 'BL_SCANNER_NOTIFICATION'
				];
				break;
			case 'email_for_notification':
				$params = [
					'name' => $name,
					'label' => 'FL_SCANNER_EMAIL_FOR_NOTIFICATION',
					'uitype' => 13,
					'typeofdata' => 'E~O',
					'maximumlength' => '255',
					'defaultvalue' => 0,
					'tooltip' => 'LBL_SCANNER_EMAIL_FOR_NOTIFICATION_DESC',
					'purifyType' => \App\Purifier::EMAIL,
					'fieldparams' => \App\Json::encode(['container_class' => 'col-md-6']),
					'blockLabel' => 'BL_SCANNER_NOTIFICATION'
				];
				break;
			case 'flag_seen':
				$params = [
					'name' => $name,
					'label' => 'FL_SCANNER_FLAG_SEEN',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'defaultvalue' => 0,
					'tooltip' => 'LBL_SCANNER_FLAG_SEEN_DESC',
					'purifyType' => \App\Purifier::BOOL,
					'fieldparams' => \App\Json::encode(['container_class' => 'col-md-6']),
					'blockLabel' => 'BL_SCANNER_BASIC'
				];
				break;
			case 'deactivation_time':
				$params = [
					'name' => $name,
					'label' => 'FL_SCANNER_DEACTIVATION_TIME',
					'uitype' => 7,
					'typeofdata' => 'I~O',
					'maximumlength' => '0,9999',
					'defaultvalue' => 48,
					'tooltip' => 'LBL_SCANNER_DEACTIVATION_TIME_DESC',
					'purifyType' => \App\Purifier::TEXT,
					'fieldparams' => \App\Json::encode(['container_class' => 'col-md-6']),
					'blockLabel' => 'BL_SCANNER_BASIC'
				];
				break;
			case 'limit':
				$params = [
					'name' => $name,
					'label' => 'FL_SCANNER_LIMIT',
					'uitype' => 7,
					'typeofdata' => 'I~O',
					'maximumlength' => '0,9999',
					'defaultvalue' => 48,
					'tooltip' => 'LBL_SCANNER_LIMIT_DESC',
					'purifyType' => \App\Purifier::TEXT,
					'fieldparams' => \App\Json::encode(['container_class' => 'col-md-6']),
					'blockLabel' => 'BL_SCANNER_BASIC'
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
