<?php

/**
 * MailSmtp record model class.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MailSmtp_Record_Model extends Settings_Vtiger_Record_Model
{
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
	 * Function to get the Detail Url.
	 *
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		return '?module=MailSmtp&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @return string URL
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=MailSmtp&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Save Ajax.
	 *
	 * @return string URL
	 */
	public function getSaveAjaxActionUrl()
	{
		return '?module=MailSmtp&parent=Settings&action=SaveAjax&mode=save';
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-sm btn-info',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.deleteById('{$this->getId()}')",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-sm btn-danger text-white',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
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
		$value = $this->get($key);
		switch ($key) {
			case 'default':
			case 'authentication':
			case 'individual_delivery':
			case 'save_send_mail':
			case 'smtp_validate_cert':
				$value = $this->getDisplayCheckboxValue($value);
				break;
			case 'password':
			case 'smtp_password':
				$value = str_repeat('*', \strlen($value));
				break;
			case 'status':
				if (isset(\App\Mailer::$statuses[$value])) {
					$value = \App\Mailer::$statuses[$value];
				}
				break;
			case 'unsubscribe':
				$unsubscribe = App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
				foreach ($unsubscribe as &$row) {
					$row = "<$row>";
				}
				$value = App\Purifier::encodeHtml(implode(',', $unsubscribe));
				break;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Function to get the Display Value, for the checbox field type with given DB Insert Value.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public function getDisplayCheckboxValue($value)
	{
		if (0 === $value) {
			$value = \App\Language::translate('LBL_NO');
		} else {
			$value = \App\Language::translate('LBL_YES');
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
		return \App\Db::getInstance('admin')->createCommand()
			->delete('s_#__mail_smtp', ['id' => $this->getId()])
			->execute();
	}

	/**
	 * Function to get the instance of advanced permission record model.
	 *
	 * @param int $id
	 *
	 * @return \self instance, if exists
	 */
	public static function getInstanceById($id)
	{
		$row = (new \App\Db\Query())->from('s_#__mail_smtp')->where(['id' => $id])->one(\App\Db::getInstance('admin'));
		$instance = false;
		if ($row) {
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:MailSmtp');
		$instance = new self();
		$instance->module = $moduleInstance;

		return $instance;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = App\Db::getInstance('admin');
		$params = [];
		foreach ($this->getData() as $key => $value) {
			$params[$key] = $value;
		}
		if ($params && empty($this->getId())) {
			$db->createCommand()->insert('s_#__mail_smtp', $params)->execute();
			$this->set('id', $db->getLastInsertID('s_#__mail_smtp_id_seq'));
		} elseif (!empty($this->getId())) {
			$this->set('id', $this->getId());
			$db->createCommand()->update('s_#__mail_smtp', $params, ['id' => $this->getId()])->execute();
		}
	}
}
