<?php

/**
 * MailSmtp record model class
 * @package YetiForce.Settings.Record
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_MailSmtp_Record_Model extends Settings_Vtiger_Record_Model
{

	/**
	 * Function to get the Id
	 * @return int Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get the Name
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get the Delete Action Url
	 * @return string URL
	 */
	public function getDeleteActionUrl()
	{
		return 'index.php?module=MailSmtp&parent=Settings&action=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		return '?module=MailSmtp&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to get the Edit View Url
	 * @return string URL
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=MailSmtp&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Save Ajax 
	 * @return string URL
	 */
	public function getSaveAjaxActionUrl()
	{
		return '?module=MailSmtp&parent=Settings&action=SaveAjax&mode=save';
	}

	/**
	 * Function to get the list view actions for the record
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
				'linkicon' => 'glyphicon glyphicon-pencil',
				'linkclass' => 'btn btn-xs btn-info'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => $this->getDeleteActionUrl(),
				'linkicon' => 'glyphicon glyphicon-trash',
				'linkclass' => 'btn btn-xs btn-danger'
			]
		];
		foreach ($recordLinks as &$recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $key
	 * @return string
	 */
	public function getDisplayValue($key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'default':
			case 'authentication':
			case 'individual_delivery':
				$value = $this->getDisplayCheckboxValue($value);
				break;
			case 'password':
				$passLength = strlen($value);
				$value = '';
				for ($i = 0; $i < $passLength; $i++) {
					$value .= '*';
				}
				break;
			case 'status':
				if (isset(\App\Mailer::$statuses[$value])) {
					$value = \App\Mailer::$statuses[$value];
				}
				break;
		}
		return $value;
	}

	/**
	 * Function to get the Display Value, for the checbox field type with given DB Insert Value
	 * @param int $value
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
	 * Function to delete the current Record Model
	 */
	public function delete()
	{
		\App\Db::getInstance('admin')->createCommand()
			->delete('s_#__mail_smtp', ['id' => $this->getId()])
			->execute();
	}

	/**
	 * Function to get the instance of advanced permission record model
	 * @param int $id
	 * @return \self instance, if exists.
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
	 * Function to get the clean instance
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
	 * Function to save
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
