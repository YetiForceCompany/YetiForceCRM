<?php

/**
 * OSSPasswords SaveAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
	public function process(\App\Request $request)
	{
		$db = PearDatabase::getInstance();

		\App\Log::trace('Starting Quick Edit OSSPasswords');

		// czy to 'password'????
		$isPassword = $request->get('field') == 'password' ? true : false;
		// check if password was added thrue related module view
		$isRelatedPassword = $request->get('password') != '' && $request->get('password') != '**********' ? true : false;

		// check if encryption is enabled
		$config = false;
		if (file_exists('modules/OSSPasswords/config.ini.php')) {
			$config = parse_ini_file('modules/OSSPasswords/config.ini.php');
		}

		// force updateing password
		if ($isPassword) {
			$recordId = $request->get('record');
			$properPassword = $isPassword ? $request->get('value') : '**********';

			\App\Log::trace('recordid: ' . $recordId . ' properpass:' . $properPassword);

			// if the password is hidden, get the proper one
			if (strcmp($properPassword, '**********') == 0) {
				\App\Log::trace('Hidden password...');
				if ($config) { // when encryption is on
					\App\Log::trace('Get encrypted password.');
					$sql = sprintf("SELECT AES_DECRYPT(`password`, '%s') AS pass FROM `vtiger_osspasswords` WHERE `osspasswordsid` = ?;", $config['key']);
					$result = $db->pquery($sql, [$recordId], true);
					$properPassword = $db->queryResult($result, 0, 'pass');
				} else {  // encryption mode is off
					\App\Log::trace('Get plain text password.');
					$sql = 'SELECT `password` AS pass FROM `vtiger_osspasswords` WHERE `osspasswordsid` = ?;';
					$result = $db->pquery($sql, [$recordId], true);
					$properPassword = $db->queryResult($result, 0, 'pass');
					\App\Log::trace('Plain text pass: ' . $properPassword);
				}
			}

			$request->set('value', $properPassword);
		}

		$recordModel = $this->saveRecord($request);

		// apply encryption if encryption mode is on
		if ($isPassword && $config) {
			\App\Log::trace('Encrypt new password: ' . $properPassword);
			$sql = 'UPDATE `vtiger_osspasswords` SET `password` = AES_ENCRYPT(?, ?) WHERE `osspasswordsid` = ?;';
			$result = $db->pquery($sql, [$properPassword, $config['key'], $recordId], true);
		}
		// encrypt password added thrue related module
		elseif ($isRelatedPassword && $config) {
			$record = $recordModel->getId();
			$properPassword = $request->get('password');
			\App\Log::trace('Encrypt new related module password: ' . $properPassword);
			$sql = 'UPDATE `vtiger_osspasswords` SET `password` = AES_ENCRYPT(?, ?) WHERE `osspasswordsid` = ?;';
			$result = $db->pquery($sql, [$properPassword, $config['key'], $record], true);
		}

		$fieldModelList = $recordModel->getModule()->getFields();
		$result = [];
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$recordFieldValue = $recordModel->get($fieldName);
			if (is_array($recordFieldValue) && $fieldModel->getFieldDataType() == 'multipicklist') {
				$recordFieldValue = implode(' |##| ', $recordFieldValue);
			}
			$fieldValue = $displayValue = \App\Purifier::encodeHtml($recordFieldValue);
			if ($fieldModel->getFieldDataType() !== 'currency' && $fieldModel->getFieldDataType() !== 'datetime' && $fieldModel->getFieldDataType() !== 'time' && $fieldModel->getFieldDataType() !== 'date') {
				$displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId());
			}
			if ($fieldName === 'password') {
				$fieldValue = $displayValue = '**********';
			}
			$result[$fieldName] = ['value' => $fieldValue, 'display_value' => $displayValue];
		}

		// Handling salutation type
		if ($request->get('field') === 'firstname' && in_array($request->getModule(), ['Contacts', 'Leads'])) {
			$salutationType = $recordModel->getDisplayValue('salutationtype');
			$firstNameDetails = $result['firstname'];
			$firstNameDetails['display_value'] = $salutationType . ' ' . $firstNameDetails['display_value'];
			if ($salutationType != '--None--') {
				$result['firstname'] = $firstNameDetails;
			}
		}

		$result['_recordLabel'] = $recordModel->getName();
		$result['_recordId'] = $recordModel->getId();

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}
