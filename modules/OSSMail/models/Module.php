<?php

/**
 *
 * @package YetiForce.models
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_Module_Model extends Vtiger_Module_Model
{

	public function getDefaultViewName()
	{
		return 'index';
	}

	public function getSettingLinks()
	{
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');

		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		$settingsLinks = array();

		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT fieldid FROM vtiger_settings_field WHERE name =  'OSSMail' AND description =  'OSSMail'", true);

		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_MODULE_CONFIGURATION',
			'linkurl' => 'index.php?module=OSSMail&parent=Settings&view=index&block=4&fieldid=' . $db->query_result($result, 0, 'fieldid'),
			'linkicon' => $layoutEditorImagePath
		);

		return $settingsLinks;
	}

	public function createBookMailsFiles($tables)
	{
		$mails = [];
		foreach ($tables as $table) {
			$mails = self::getAdresBookMails($table, $mails);
		}

		$fstart = '<?php $bookMails = [';
		$fend .= '];';

		foreach ($mails as $user => $file) {
			file_put_contents('cache/addressBook/mails_' . $user . '.php', $fstart . $file . $fend);
		}
	}

	public function getAdresBookMails($table, $mails)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->query("SELECT * FROM $table;");
		while ($row = $adb->fetch_array($result)) {
			$name = $row['name'];
			$email = $row['email'];
			$users = $row['users'];
			if ($users != '') {
				$users = explode(',', $users);
				foreach ($users as $user) {
					$mails[$user] .= "'" . addslashes($name) . " <$email>',";
				}
			}
		}
		return $mails;
	}

	public function getDefaultMailAccount($accounts)
	{
		$rcUser = (isset($_SESSION['AutoLoginUser']) && array_key_exists($_SESSION['AutoLoginUser'], $accounts)) ? $accounts[$_SESSION['AutoLoginUser']] : reset($accounts);
		return $rcUser;
	}

	public static function getComposeUrl($moduleName = false, $record = false, $view = false, $type = false)
	{
		$url = 'index.php?module=OSSMail&view=compose';
		if ($moduleName) {
			$url .= '&crmModule=' . $moduleName;
		}
		if ($record) {
			$url .= '&crmRecord=' . $record;
		}
		if ($view) {
			$url .= '&crmView=' . $view;
		}
		if ($type) {
			$url .= '&type=' . $type;
		}
		return $url;
	}

	function getComposeUrlParam($moduleName = false, $record = false, $type = false, $view = false)
	{
		$url = '';
		if (!empty($record) && isRecordExists($record) && Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $record)) {
			$recordModel_OSSMailView = Vtiger_Record_Model::getCleanInstance('OSSMailView');
			$email = $recordModel_OSSMailView->findEmail($record, $moduleName);
			if (!empty($email)) {
				$url = '&to=' . $email;
			}

			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$moduleModel = $recordModel->getModule();

			$modulesLevel1 = Vtiger_Module_Model::getModulesByLevel();
			if (!in_array($moduleName, array_keys($modulesLevel1))) {
				$db = PearDatabase::getInstance();
				$result = $db->pquery('SELECT fieldname FROM vtiger_field WHERE tabid = ? AND uitype = ?', [$moduleModel->getId(), 4]);
				if ($db->getRowCount($result) > 0) {
					$subject = '&subject=' . $recordModel->get($db->getSingleValue($result));
					if ($type == 'new') {
						switch ($moduleName) {
							case 'HelpDesk':
								$subject .= ' - ' . $recordModel->get('ticket_title');
								break;
							case 'SSalesProcesses':
								$subject .= ' - ' . $recordModel->get('subject');
								break;
							case 'Project':
								$subject .= ' - ' . $recordModel->get('projectname');
								break;
						}
					}
					$url .= $subject;
				}
			}
		}
		if (!empty($moduleName)) {
			$url .= '&crmmodule=' . $moduleName;
		}
		if (!empty($record)) {
			$url .= '&crmrecord=' . $record;
		}
		if (!empty($view)) {
			$url .= '&crmview=' . $view;
		}
		return $url;
	}

	protected static $composeParam = false;

	public static function getComposeParameters()
	{
		if (!self::$composeParam) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT parameter,value FROM vtiger_ossmailscanner_config WHERE conf_type = ?', ['email_list']);
			$config = [];
			for ($i = 0; $i < $db->num_rows($result); $i++) {
				$config[$db->query_result($result, $i, 'parameter')] = $db->query_result($result, $i, 'value');
			}
			$config['popup'] = $config['target'] == '_blank' ? true : false;
			self::$composeParam = $config;
		}
		return self::$composeParam;
	}

	function getExternalUrl($moduleName = false, $record = false, $view = false, $type = false)
	{
		$url = 'mailto:';
		if (!empty($record) && isRecordExists($record) && Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $record)) {
			$recordModel_OSSMailView = Vtiger_Record_Model::getCleanInstance('OSSMailView');
			$email = $recordModel_OSSMailView->findEmail($record, $moduleName);
			if (!empty($email)) {
				$url .= $email;
			}
			$url .= '?';
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$moduleModel = $recordModel->getModule();

			$modulesLevel1 = Vtiger_Module_Model::getModulesByLevel();
			if (!in_array($moduleName, array_keys($modulesLevel1))) {
				$db = PearDatabase::getInstance();
				$result = $db->pquery('SELECT fieldname FROM vtiger_field WHERE tabid = ? AND uitype = ?', [$moduleModel->getId(), 4]);
				if ($db->getRowCount($result) > 0) {
					$subject = 'subject=' . $recordModel->get($db->getSingleValue($result));
					if ($type == 'new') {
						switch ($moduleName) {
							case 'HelpDesk':
								$subject .= ' - ' . $recordModel->get('ticket_title');
								break;
							case 'SSalesProcesses':
								$subject .= ' - ' . $recordModel->get('subject');
								break;
							case 'Project':
								$subject .= ' - ' . $recordModel->get('projectname');
								break;
						}
					}
					$url .= $subject;
				}
			}
		}
		return $url;
	}

	function getExternalUrlForWidget($record, $type)
	{
		if (is_object($record)) {
			$body = $record->get('content');
			$subject = $record->get('subject');
			$from = $record->get('from_email');
			$to = $record->get('to_email');
			$cc = $record->get('cc_email');
			$date = $record->get('date');
		} else {
			$body = $record['bodyRaw'];
			$subject = $record['subjectRaw'];
			$from = $record['fromRaw'];
			$to = $record['toRaw'];
			$cc = $record['ccRaw'];
			$date = $record['date'];
		}

		if ($type == 'forward') {
			$url = 'mailto:';
		} else {
			$url = 'mailto:' . $from;
		}
		$url .= '?subject=' . $subject;
		if ($type == 'replyAll' && !empty($cc)) {
			$url .= '&cc=' . $cc;
		}
		$body = preg_replace('/<[^>]*>/', '', $body);
		$body = preg_replace('/\r?\n/', "\n", $body);
		$content = '';
		$mailtoLimit = AppConfig::module('Email', 'MAILTO_LIMIT');

		if ($type == 'forward') {
			$content .= vtranslate('LBL_MAIL_FORWARD_INTRO', 'OSSMailView') . "\n";
			$content .= vtranslate('Subject', 'OSSMailView') . ': ' . $subject . "\n";
			$content .= vtranslate('Date', 'OSSMailView') . ': ' . $date . "\n";
			$content .= vtranslate('From', 'OSSMailView') . ': ' . $from . "\n";
			$content .= vtranslate('To', 'OSSMailView') . ': ' . $to . "\n";
			foreach (explode("\n", $body) as $line) {
				$line = trim($line);
				if (!empty($line)) {
					$line = '> ' . $line . "\n";
					if (strlen($url . '&body=' . rawurlencode($content . $line)) > $mailtoLimit) {
						break;
					}
					$content .= $line;
				}
			}
		} else {
			$content .= vtranslate('LBL_MAIL_REPLY_INTRO', 'OSSMailView', $date, $from) . "\n";
			foreach (explode("\n", $body) as $line) {
				$line = trim($line);
				if (!empty($line)) {
					$line = '> ' . $line . "\n";
					if (strlen($url . '&body=' . rawurlencode($content . $line)) > $mailtoLimit) {
						break;
					}
					$content .= $line;
				}
			}
		}
		$url .= '&body=' . rawurlencode($content);
		return $url;
	}
}
