<?php

/**
 * Mail record model class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	public function getDisplayValue(string $key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'smtp_id':
				$smtpName = \App\Mail::getSmtpById($value)['name'] ?? '';
				$value = '<a href=index.php?module=MailSmtp&parent=Settings&view=Detail&record=' . $value . '>' . $smtpName . '</a>';
				break;
			case 'status':
				if (isset(\App\Mailer::$statuses[$value])) {
					$value = \App\Language::translate(\App\Mailer::$statuses[$value], 'Settings::Mail');
					if (2 === (int) $this->get('status')) {
						$value = '<span class="fas fa-exclamation-triangle js-popover-tooltip color-red-a200" data-content="' . $this->get('error') . '">&nbsp;' . $value . '</span>';
					}
				}
				break;
			case 'owner':
				$value = \App\Fields\Owner::getUserLabel($value);
				break;
			case 'content':
				$value = \App\Layout::truncateHtml(vtlib\Functions::getHtmlOrPlainText($value), 'full');
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
					$attachments = \App\Json::decode($value);
					$value = '';
					$fileCounter = 0;
					if (isset($attachments['ids'])) {
						$attachments = array_merge($attachments, \App\Mail::getAttachmentsFromDocument($attachments['ids']));
						unset($attachments['ids']);
					}
					foreach ($attachments as $path => $name) {
						if (is_numeric($path)) {
							$path = $name;
							$name = \App\Language::translate('LBL_FILE');
						}
						$actionPath = "?module=Mail&parent=Settings&action=DownloadAttachment&record={$this->getId()}&selectedFile=$fileCounter";
						$value .= '<form action="' . $actionPath . '"
						method="POST"><button class="btn btn-sm btn-outline-secondary"
						title="' . $name . '"
						data-selected-file="' . $fileCounter . '">
						' . $name . '</button></form>';
						++$fileCounter;
					}
				}
				break;
			case 'error':
				 $value = \App\Layout::truncateHtml($value, 'mini', 30);
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
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		return \App\Db::getInstance('admin')->createCommand()
			->delete('s_#__mail_queue', ['id' => $this->getId()])
			->execute();
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		if (0 === $this->get('status')) {
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
		if (false !== $row) {
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}
}
