<?php

/**
 * Mail module model class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Mail_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 's_#__mail_queue';
	public $baseIndex = 'id';
	public $listFields = ['smtp_id' => 'LBL_SMTP_NAME', 'date' => 'LBL_DATE', 'owner' => 'LBL_CREATED_BY', 'subject' => 'LBL_SUBJECT', 'status' => 'LBL_STATUS', 'priority' => 'LBL_PRIORITY', 'error' => 'LBL_ERROR'];
	public $name = 'Mail';
	public $filterFields = ['smtp_id', 'status', 'priority'];

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		$menu = Settings_Vtiger_MenuItem_Model::getInstance('LBL_EMAILS_TO_SEND');

		return 'index.php?module=Mail&parent=Settings&view=List&fieldid=' . $menu->get('fieldid');
	}

	/**
	 * Function to get the url for create view of the module.
	 *
	 * @return string URL
	 */
	public function getCreateRecordUrl()
	{
		return '';
	}

	public function getFilterFields()
	{
		return $this->filterFields;
	}

	/**
	 * Function to gets the file info for attachment file.
	 *
	 * @param int $id
	 * @param int $selectedFile
	 *
	 * @return string URL
	 */
	public static function getAttachmentInfo($id, $selectedFile)
	{
		$path = '';
		$attachments = (new \App\Db\Query())->select(['attachments'])->from('s_#__mail_queue')->where(['id' => $id])->scalar(\App\Db::getInstance('admin')) ?: '[]';
		$counter = 0;
		$attachments = \App\Json::decode($attachments);
		if (isset($attachments['ids'])) {
			$attachments = array_merge($attachments, \App\Mail::getAttachmentsFromDocument($attachments['ids']));
			unset($attachments['ids']);
		}
		foreach ($attachments as $path => $name) {
			if ($counter === $selectedFile) {
				return ['path' => $path, 'name' => $name];
			}
			++$counter;
		}
		return ['path' => $path, 'name' => $name];
	}
}
