<?php

/**
 * Mail record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_Record_Model extends Settings_Vtiger_Record_Model
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
	 * Function to get the Acceptance Action Url.
	 *
	 * @return string URL
	 */
	public function getAcceptanceActionUrl()
	{
		return 'index.php?module=Mail&parent=Settings&action=SaveAjax&mode=acceptanceRecord&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url.
	 *
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		$menu = Settings_Vtiger_MenuItem_Model::getInstance('LBL_EMAILS_TO_SEND');

		return 'index.php?module=Mail&parent=Settings&view=Detail&record=' . $this->getId() . '&fieldid=' . $menu->get('fieldid');
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue($key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'smtp_id':
				$smtpName = \App\Mail::getSmtpById($value)['name'];
				$value = '<a href=index.php?module=MailSmtp&parent=Settings&view=Detail&record=' . $value . '>' . $smtpName . '</a>';
				break;
			case 'status':
				if (isset(\App\Mailer::$statuses[$value])) {
					$value = \App\Language::translate(\App\Mailer::$statuses[$value], 'Settings::Mail');
				}
				break;
			case 'owner':
				$value = \App\Fields\Owner::getUserLabel($value);
				break;
			case 'content':
				$value = vtlib\Functions::getHtmlOrPlainText($value);
				break;
			case 'date':
				$value = DateTimeField::convertToUserFormat($value);
				break;
			case 'from':
			case 'to':
			case 'cc':
			case 'bcc':
				$value = $this->getDisplayValueForEmail($value);
				break;
			case 'attachments':
				if ($value) {
					$attachments = $value;
					$value = '';
					$fileCounter = 0;
					foreach (\App\Json::decode($attachments) as $path => $name) {
						if (is_numeric($path)) {
							$path = $name;
							$name = 'LBL_FILE';
						}
						$actionPath = "?module=Mail&parent=Settings&action=DownloadAttachment&record={$this->getId()}&selectedFile=$fileCounter";
						$value .= '<form action="' . $actionPath . '" method="POST"><button class="btn btn-sm btn-outline-secondary" title="' . $name . '" data-selected-file="' . $fileCounter . '">' . $name . '</button></form>';
						++$fileCounter;
					}
				}
				break;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Function to get the display value for emails.
	 *
	 * @param array $emails
	 *
	 * @return string
	 */
	public function getDisplayValueForEmail($emails)
	{
		$value = '';
		if ($emails) {
			foreach (\App\Json::decode($emails) as $email => $name) {
				if (is_numeric($email)) {
					$email = $name;
					$name = '';
					$value .= $email . ', ';
				} else {
					$value .= $name . ' &lt;' . $email . '&gt;, ';
				}
			}
		}
		return rtrim($value, ', ');
	}

	/**
	 * Function to delete the current Record Model.
	 */
	public function delete()
	{
		\App\Db::getInstance('admin')->createCommand()
			->delete('s_#__mail_queue', ['id' => $this->getId()])
			->execute();
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return array - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{
		$links = [];
		if ($this->get('status') === 0) {
			$recordLinks[] = [
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_ACCEPTANCE_RECORD',
				'linkurl' => '#',
				'linkicon' => 'fas fa-check',
				'linkclass' => 'btn btn-xs btn-success acceptanceRecord',
			];
		}

		$recordLinks[] = [
			'linktype' => 'LISTVIEWRECORD',
			'linklabel' => 'LBL_DELETE_RECORD',
			'linkurl' => "javascript:Settings_Vtiger_List_Js.deleteById('{$this->getId()}')",
			'linkicon' => 'fas fa-trash-alt',
			'linkclass' => 'btn btn-xs btn-danger text-white',
		];

		foreach ($recordLinks as &$recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get the instance of advanced permission record model.
	 *
	 * @param int $id
	 *
	 * @return \self instance, if exists
	 */
	public static function getInstance($id)
	{
		$query = (new \App\Db\Query())->from('s_#__mail_queue')->where(['id' => $id]);
		$row = $query->createCommand(\App\Db::getInstance('admin'))->queryOne();
		$instance = false;
		if ($row !== false) {
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}
}
