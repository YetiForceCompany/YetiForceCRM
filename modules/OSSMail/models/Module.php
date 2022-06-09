<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_Module_Model extends Vtiger_Module_Model
{
	public function getDefaultViewName()
	{
		return 'Index';
	}

	/** {@inheritdoc} */
	public function getSettingLinks(): array
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$settingsLinks = [];
		if ($menu = Settings_Vtiger_MenuItem_Model::getInstance('Mail')) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_MODULE_CONFIGURATION',
				'linkurl' => 'index.php?module=OSSMail&parent=Settings&view=Index&block=' . $menu->get('blockid') . '&fieldid=' . $menu->get('fieldid'),
				'linkicon' => 'adminIcon-mail-download-history',
			];
		}
		return $settingsLinks;
	}

	public static function getDefaultMailAccount($accounts)
	{
		return (isset($_SESSION['AutoLoginUser']) && \array_key_exists($_SESSION['AutoLoginUser'], $accounts)) ? $accounts[$_SESSION['AutoLoginUser']] : reset($accounts);
	}

	/**
	 * URL generation for internal mail clients.
	 *
	 * @param mixed $moduleName
	 * @param mixed $record
	 * @param mixed $view
	 * @param mixed $type
	 *
	 * @return string
	 */
	public static function getComposeUrl($moduleName = false, $record = false, $view = false, $type = false): string
	{
		$url = 'index.php?module=OSSMail&view=Compose';
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

	public static function getComposeParam(App\Request $request)
	{
		$moduleName = $request->getByType('crmModule');
		$record = $request->getInteger('crmRecord');
		$type = $request->getByType('type');
		$return = [];
		if (('Users' === $moduleName && $record === \App\User::getCurrentUserRealId()) || ('Users' !== $moduleName && !empty($record) && \App\Record::isExists($record) && \App\Privilege::isPermitted($moduleName, 'DetailView', $record))) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$eventHandler = new App\EventHandler();
			$eventHandler->setRecordModel($recordModel)->setModuleName($moduleName)->setParams($return);
			$eventHandler->trigger('MailComposeParamBefore');
			$return = $eventHandler->getParams();

			$recordModel_OSSMailView = OSSMailView_Record_Model::getCleanInstance('OSSMailView');
			if ($request->isEmpty('to') && ($email = $recordModel_OSSMailView->findEmail($record, $moduleName))) {
				$return['to'] = $email;
			}
			foreach (['_to', '_cc'] as $name) {
				$content = $request->has($name) ? $request->getRaw($name) : ($return[$name] ?? '');
				if ($content) {
					$emailParser = \App\EmailParser::getInstanceByModel($recordModel);
					$emailParser->emailoptout = false;
					$fromEmailDetails = $emailParser->setContent($content)->parse()->getContent();
					if ($fromEmailDetails) {
						$return[substr($name, -2)] = $fromEmailDetails;
					}
					if (isset($return[$name])) {
						unset($return[$name]);
					}
				}
			}
			if (!\in_array($moduleName, array_keys(array_merge(\App\ModuleHierarchy::getModulesByLevel(0), \App\ModuleHierarchy::getModulesByLevel(3)))) || 'Campaigns' === $moduleName) {
				$subject = '';
				if ('new' === $type || 'Campaigns' === $moduleName) {
					$return['title'] = $recordModel->getName();
					$subject .= $recordModel->getName();
				}
				$recordNumber = $recordModel->getRecordNumber();
				if (!empty($recordNumber)) {
					$return['recordNumber'] = $recordNumber;
					$subject = "[$recordNumber] $subject";
				}
				if (($templateId = $request->getInteger('template', 0)) && \App\Record::isExists($templateId, 'EmailTemplates')) {
					$params = $request->getArray('templateParams', \App\Purifier::TEXT, [], App\Purifier::ALNUM);
					$templateModel = \Vtiger_Record_Model::getInstanceById($templateId, 'EmailTemplates');
					$textParser = \App\TextParser::getInstanceByModel($recordModel);
					foreach ($params as $key => $value) {
						$textParser->setParam($key, $value);
					}
					if ('Calendar' === $moduleName && !$recordModel->isEmpty('meeting_url') && !\array_key_exists('meetingUrl', $params) ) {
						$textParser->setParam('meetingUrl', $recordModel->get('meeting_url'));
					}
					$subject = $textParser->setContent($templateModel->get('subject'))->parse()->getContent();
					$return['html'] = true;
					$return['body'] = $textParser->setContent($templateModel->get('content'))->parse()->getContent();
				}
				$return['subject'] = $subject;
				if ('Calendar' === $moduleName && $request->getBoolean('ics')) {
					$filePath = \App\Config::main('tmp_dir');
					$tmpFileName = tempnam($filePath, 'ics');
					$filePath .= basename($tmpFileName);
					if (false !== file_put_contents($filePath, $recordModel->getICal())) {
						$fileName = \App\Fields\File::sanitizeUploadFileName($recordModel->getName()) . '.ics';
						$return['filePath'] = [['path' => $filePath, 'name' => $fileName]];
					}
				}
			}

			$eventHandler->setParams($return);
			$eventHandler->trigger('MailComposeParamAfter');
			$return = $eventHandler->getParams();
		}
		if (!empty($moduleName)) {
			$return['crmmodule'] = $moduleName;
		}
		if (!empty($record)) {
			$return['crmrecord'] = $record;
		}
		if (!$request->isEmpty('crmView')) {
			$return['crmview'] = $request->getByType('crmView');
		}
		if (!$request->isEmpty('mid') && !empty($type)) {
			$return['mailId'] = (int) $request->getInteger('mid');
			$return['type'] = $type;
		}
		if (!$request->isEmpty('pdf_path')) {
			$return['filePath'] = $request->get('pdf_path');
		}
		if (!empty($moduleName)) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$moduleConfig = App\Config::module($moduleName);
			if ($moduleConfig && isset($moduleConfig['SEND_IDENTITY'][$currentUser->get('roleid')])) {
				$return['from'] = $moduleConfig['SEND_IDENTITY'][$currentUser->get('roleid')];
			}
		}
		if (!$request->isEmpty('to')) {
			$return['to'] = $request->get('to');
		}
		if (!$request->isEmpty('cc')) {
			$return['cc'] = $request->get('cc');
		}
		if (!$request->isEmpty('bcc')) {
			$return['bcc'] = $request->get('bcc');
		}
		if (!$request->isEmpty('subject')) {
			$return['subject'] = $request->get('subject');
		}
		if (!$request->isEmpty('emails')) {
			$return['bcc'] = implode(',', $request->get('emails'));
		}
		return $return;
	}

	protected static $composeParam = false;

	/**
	 * Function get compose parameters.
	 *
	 * @return array
	 */
	public static function getComposeParameters()
	{
		if (!self::$composeParam) {
			$config = (new \App\Db\Query())->select(['parameter', 'value'])->from('vtiger_ossmailscanner_config')
				->where(['conf_type' => 'email_list'])->createCommand()->queryAllByGroup(0);
			$config['popup'] = '_blank' == $config['target'] ? true : false;
			self::$composeParam = $config;
		}
		return self::$composeParam;
	}

	/**
	 * URL generation for external mail clients.
	 *
	 * @param mixed $moduleName
	 * @param mixed $record
	 * @param mixed $view
	 * @param mixed $type
	 *
	 * @return string
	 */
	public static function getExternalUrl($moduleName = false, $record = false, $view = false, $type = false): string
	{
		$url = 'mailto:';
		$request = new App\Request([]);
		if ($moduleName) {
			$request->set('crmModule', $moduleName);
		}
		if ($record) {
			$request->set('crmRecord', $record);
		}
		if ($view) {
			$request->set('crmView', $view);
		}
		if ($type) {
			$request->set('type', $type);
		}
		$param = self::getComposeParam($request);
		if (isset($param['to'])) {
			$url .= str_replace(',', ';', $param['to']);
		}
		$url .= '?';
		foreach (['cc', 'bcc'] as $value) {
			if (isset($param[$value])) {
				$url .= $value . '=' . str_replace(',', ';', $param[$value]) . '&';
			}
		}
		if (isset($param['subject'])) {
			$url .= 'subject=' . \App\Purifier::encodeHtml($param['subject']) . '&';
		}
		if (isset($param['body'])) {
			$url .= 'body=' . \App\Purifier::encodeHtml($param['body']) . '&';
		}
		return $url;
	}

	/**
	 * Get mail url for widget.
	 *
	 * @param int    $record
	 * @param string $type
	 * @param int    $srecord
	 * @param string $smoduleName
	 *
	 * @return string
	 */
	public static function getExternalUrlForWidget($record, $type, $srecord = false, $smoduleName = false)
	{
		if (\is_object($record)) {
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

		if (!empty($srecord) && !empty($smoduleName)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($srecord);
			$moduleModel = $recordModel->getModule();
			if (!\in_array($smoduleName, array_keys(array_merge(\App\ModuleHierarchy::getModulesByLevel(0), \App\ModuleHierarchy::getModulesByLevel(3))))) {
				$fieldName = $moduleModel->getSequenceNumberFieldName();
				if ($fieldName) {
					$subject = "[$fieldName] $subject";
				}
			}
		}
		if ('forward' == $type) {
			$url = 'mailto:';
			$subject = 'Fwd: ' . $subject;
		} else {
			$url = 'mailto:' . $from;
			$subject = 'Re: ' . $subject;
		}
		$url .= '?subject=' . $subject;
		if ('replyAll' == $type && !empty($cc)) {
			$url .= '&cc=' . $cc;
		}
		include_once 'vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', \App\Config::main('default_charset'));
		$config->set('Cache.SerializerPath', ROOT_DIRECTORY . '/cache/vtlib');
		$config->set('CSS.AllowTricky', false);
		$config->set('HTML.AllowedElements', 'div,p,br');
		$config->set('HTML.AllowedAttributes', '');
		$purifier = new HTMLPurifier($config);
		$body = $purifier->purify($body);
		$body = str_replace(['<p> </p>', '<p></p>', '</p>', '<br />', '<p>', '<div>', '</div>', PHP_EOL . PHP_EOL, PHP_EOL . PHP_EOL], ['', '', PHP_EOL, PHP_EOL, '', '', PHP_EOL, PHP_EOL, PHP_EOL], nl2br($body));

		$content = '';
		$mailtoLimit = \App\Config::component('Mail', 'MAILTO_LIMIT');

		if ('forward' == $type) {
			$content .= \App\Language::translate('LBL_MAIL_FORWARD_INTRO', 'OSSMailView') . PHP_EOL;
			$content .= \App\Language::translate('Subject', 'OSSMailView') . ': ' . $subject . PHP_EOL;
			$content .= \App\Language::translate('Date', 'OSSMailView') . ': ' . $date . PHP_EOL;
			$content .= \App\Language::translate('From', 'OSSMailView') . ': ' . $from . PHP_EOL;
			$content .= \App\Language::translate('To', 'OSSMailView') . ': ' . $to . PHP_EOL;
			foreach (explode(PHP_EOL, $body) as $line) {
				$line = trim($line);
				if (!empty($line)) {
					$line = '> ' . $line . PHP_EOL;
					if (\strlen($url . '&body=' . rawurlencode($content . $line)) > $mailtoLimit) {
						break;
					}
					$content .= $line;
				}
			}
		} else {
			$content .= \App\Language::translateArgs('LBL_MAIL_REPLY_INTRO', 'OSSMailView', $date, $from) . PHP_EOL;
			foreach (explode(PHP_EOL, $body) as $line) {
				$line = trim($line);
				if (!empty($line)) {
					$line = '> ' . $line . PHP_EOL;
					if (\strlen($url . '&body=' . rawurlencode($content . $line)) > $mailtoLimit) {
						break;
					}
					$content .= $line;
				}
			}
		}
		return $url . '&body=' . rawurlencode($content);
	}

	/** {@inheritdoc} */
	public function getModalRecordsListSourceFields(App\QueryGenerator $queryGenerator, Vtiger_Module_Model $baseModule, $popupFields)
	{
		foreach ($baseModule->getFieldsByType('email') as $item) {
			$popupFields[$item->getName()] = $item->getName();
		}
		return $popupFields;
	}
}
