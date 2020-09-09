<?php

/**
 * MailClient record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class Settings_MailClient_Record_Model extends Settings_Vtiger_Record_Model
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
		return "index.php?module=MailClient&parent=Settings&view=Detail&record={$this->getId()}";
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @return string URL
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=MailClient&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Save Ajax.
	 *
	 * @return string URL
	 */
	public function getSaveAjaxActionUrl()
	{
		return '?module=MailClient&parent=Settings&action=SaveAjax&mode=save';
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return array - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
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
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-sm btn-danger text-white js-remove',
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
			case 'validate_cert':
				$value = $this->getDisplayCheckboxValue($value);
				break;
			case 'add_connection_type':
				$value = $this->getDisplayCheckboxValue($value);
				break;
			case 'ip_check':
				$value = $this->getDisplayCheckboxValue($value);
				break;
			case 'enable_spellcheck':
				$value = $this->getDisplayCheckboxValue($value);
				break;
			case 'identities_level':
				$value = \App\Language::translate('PLL_IDENTITY_' . $value, 'Settings:MailClient');
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
	 */
	public function delete()
	{
		\App\Db::getInstance()->createCommand()
			->delete('s_#__mail_client', ['id' => $this->getId()])
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
		$row = (new \App\Db\Query())->from('s_#__mail_client')->where(['id' => $id])->one(\App\Db::getInstance());
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
		$moduleInstance = Settings_Vtiger_Module_Model::getInstance('Settings:MailClient');
		$instance = new self();
		$instance->module = $moduleInstance;
		return $instance;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = App\Db::getInstance();
		$params = [];
		foreach ($this->getData() as $key => $value) {
			$params[$key] = $value;
		}
		$result = false;
		if ($params && empty($this->getId())) {
			$db->createCommand()->insert('s_#__mail_client', $params)->execute();
			$this->set('id', $db->getLastInsertID('s_#__mail_client'));
			$result = true;
		} elseif (!empty($this->getId())) {
			$db->createCommand()->update('s_#__mail_client', $params, ['id' => $this->getId()])->execute();
			$result = true;
		}
		return $result;
	}
}
