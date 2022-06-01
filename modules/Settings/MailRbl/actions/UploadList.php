<?php

/**
 * Settings MailRbl Upload List action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license	  YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

/**
 * Settings MailRbl Upload List action class.
 */
class Settings_MailRbl_UploadList_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * File delimiter string.
	 *
	 * @var string
	 */
	public $delimiter = "\n";

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$response = new Vtiger_Response();
		$result = ['success' => false, 'message' => \App\Language::translate('LBL_UPLOAD_ERROR', $request->getModule(false))];
		if (!empty($_FILES['imported_list'])) {
			$fileInstance = \App\Fields\File::loadFromRequest($_FILES['imported_list']);
			if ($fileInstance->validateAndSecure('text') && $fileInstance->getSize() < \App\Config::getMaxUploadSize()) {
				if (!empty($fileContent = $fileInstance->getContents())) {
					$importResult = $this->saveToDb($request->getByType('source', 'Alnum'), $request->getByType('type', 'Integer'), $fileContent);
					if (!empty($importResult)) {
						$message = \App\Language::translate('LBL_UPLOAD_SUCCESS', $request->getModule(false)) . '<br/>';
						$message .= \App\Language::translate('LBL_LIST_RECORD_SAVED', $request->getModule(false)) . ': ' . $importResult['saved'] . '<br/>';
						$message .= \App\Language::translate('LBL_LIST_RECORD_DUPLICATES', $request->getModule(false)) . ': ' . $importResult['duplicates'] . '<br/>';
						$message .= \App\Language::translate('LBL_LIST_RECORD_ERRORS', $request->getModule(false)) . ': ' . $importResult['errors'] . '<br/>';
						$result = ['success' => true, 'message' => $message];
					} else {
						$result = ['success' => false, 'message' => \App\Language::translate('LBL_UPLOAD_WRONG_FILE', $request->getModule(false))];
					}
				}
			}
		}
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Save to DB imported record.
	 *
	 * @param string $source
	 * @param int    $type
	 * @param string $content
	 *
	 * @return array
	 */
	public function saveToDb(string $source, int $type, string $content): array
	{
		$firstLine = true;
		$explodedElements = explode($this->delimiter, $content);
		$db = \App\Db::getInstance('admin');
		$report = [
			'saved' => 0,
			'duplicates' => 0,
			'errors' => 0,
		];
		$source = \App\TextUtils::textTruncate($source, 10);
		foreach ($explodedElements as $elementToSave) {
			$clearIp = trim($elementToSave);
			if (\App\Validator::ip($clearIp)) {
				$isExists = (new \App\Db\Query())->from('s_#__mail_rbl_list')->where(['ip' => $clearIp])->exists($db);
				try {
					if (!$isExists) {
						$db->createCommand()->insert('s_#__mail_rbl_list', [
							'ip' => $clearIp,
							'status' => 0,
							'type' => $type,
							'source' => $source,
							'request' => 0,
						])->execute();
						++$report['saved'];
					} else {
						++$report['duplicates'];
					}
					\App\Cache::delete('MailRblIpColor', $clearIp);
					\App\Cache::delete('MailRblList', $clearIp);
				} catch (\Throwable $e) {
					++$report['errors'];
				}
			} else {
				if ($firstLine) {
					$report = [];
					break;
				}
				++$report['errors'];
			}
		}
		return $report;
	}
}
