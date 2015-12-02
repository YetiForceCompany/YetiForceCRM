<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

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

	function getComposeUrl($moduleName = false, $record = false, $view = false, $popup = false)
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
		if ($popup) {
			$url .= '&popup=1';
		}
		return $url;
	}

	function getComposeUrlParam($moduleName = false, $record = false, $view = false)
	{
		$url = '';
		if (!empty($record) && isRecordExists($record)) {
			$recordModel_OSSMailView = Vtiger_Record_Model::getCleanInstance('OSSMailView');
			$email = $recordModel_OSSMailView->findEmail($record, $moduleName);
			if ($email) {
				$url = '&to=' . $email;
			}
			$InstanceModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			if ($moduleName == 'HelpDesk') {
				$urldata = '&subject=' . $InstanceModel->get('ticket_no') . ' - ' . $InstanceModel->get('ticket_title');
			} elseif ($moduleName == 'Potentials') {
				$urldata = '&subject=' . $InstanceModel->get('potential_no') . ' - ' . $InstanceModel->get('potentialname');
			} elseif ($moduleName == 'Project') {
				$urldata = '&subject=' . $InstanceModel->get('project_no') . ' - ' . $InstanceModel->get('projectname');
			}
			$url .= $urldata;
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

	function getComposeParameters()
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
}
