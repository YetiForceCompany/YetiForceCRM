<?php

/**
 * Mail record model class
 * @package YetiForce.Settings.Record
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_Record_Model extends Settings_Vtiger_Record_Model
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
		return 'index.php?module=Mail&parent=Settings&action=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get the Acceptance Action Url
	 * @return string URL
	 */
	public function getAcceptanceActionUrl()
	{
		return 'index.php?module=Mail&parent=Settings&action=SaveAjax&mode=acceptanceRecord&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		return 'index.php?module=Mail&parent=Settings&view=Detail&record=' . $this->getId();
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
				if ($value) {
					$from = $value;
					$value = '';
					foreach (\App\Json::decode($from) as $email => $name) {
						if (is_numeric($email)) {
							$email = $name;
							$name = '';
						}
						$value .= $name . ' &lt;'.$email.'&gt;, ';
					}
				}
				break;
			case 'to':
				if ($value) {
					$to = $value;
					$value = '';
					foreach (\App\Json::decode($to) as $email => $name) {
						if (is_numeric($email)) {
							$email = $name;
							$name = '';
						}
						$value .= $name . ' &lt;'.$email.'&gt;, ';
					}
				}
				break;
			case 'cc':
				if ($value) {
					$cc = $value;
					$value = '';
					foreach (\App\Json::decode($cc) as $email => $name) {
						if (is_numeric($email)) {
							$email = $name;
							$name = '';
						}
						$value .= $name . ' &lt;'.$email.'&gt;, ';
					}
					break;
				}
			case 'bcc':
				if ($value) {
					$bcc = $value;
					$value = '';
					foreach (\App\Json::decode($bcc) as $email => $name) {
						if (is_numeric($email)) {
							$email = $name;
							$name = '';
						}
						$value .= $name . ' &lt;'.$email.'&gt;, ';
					}
				}
				break;
			case 'attachments':
				if ($value) {
					$attachments = $value;
					$value = '';
					foreach (\App\Json::decode($attachments) as $path => $name) {
						if (is_numeric($path)) {
							$path = $name;
							$name = 'LBL_FILE';
						}
						$actionPath = '?module=Mail&parent=Settings&action=GetDownload&record='. $this->getId().'&path='. $path;
						$value .= '<a href='.$actionPath. ' title='.$path.'>' . $name . '</a> ';
					}
				}
				break;
		}
		return $value;
	}

	/**
	 * Function to delete the current Record Model
	 */
	public function delete()
	{
		\App\Db::getInstance()->createCommand()
			->delete('s_#__mail_queue', ['id' => $this->getId()])
			->execute();
	}

	/**
	 * Function to get the list view actions for the record
	 * @return arraya - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{

		$links = [];
		if ($this->get('status') === 0) {
			$recordLinks[] = [
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_ACCEPTANCE_RECORD',
				'linkurl' => '#',
				'linkicon' => 'glyphicon glyphicon-ok',
				'linkclass' => 'btn-xs btn-success acceptanceRecord'
			];
		}

		$recordLinks[] = [
			'linktype' => 'LISTVIEWRECORD',
			'linklabel' => 'LBL_DELETE_RECORD',
			'linkurl' => $this->getDeleteActionUrl(),
			'linkicon' => 'glyphicon glyphicon-trash',
			'linkclass' => 'btn-xs btn-danger'  
		];

		foreach ($recordLinks as &$recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get the instance of advanced permission record model
	 * @param int $id
	 * @return \self instance, if exists.
	 */
	public static function getInstance($id)
	{

		$query = (new \App\Db\Query())->from('s_#__mail_queue')->where(['id' => $id]);
		$row = $query->createCommand()->queryOne();
		$instance = false;
		if ($row !== false) {
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}
}
