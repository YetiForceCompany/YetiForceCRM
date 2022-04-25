<?php
/**
 * SMS Notifier handler file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * SMS Notifier handler class.
 */
class SMSNotifier_Parser_Handler
{
	/**
	 * EntityBeforeSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		foreach (['related_to', 'phone', 'message'] as $fieldName) {
			if ($recordModel->isEmpty($fieldName)) {
				throw new \App\Exceptions\NoPermitted('ERR_MISSING_DATA: ' . $fieldName);
			}
		}
		$message = $recordModel->get('message');
		if (false !== strpos($message, '$')) {
			$relatedRecordId = $recordModel->get('related_to');
			$parser = \App\TextParser::getInstanceById($relatedRecordId);
			$message = $parser->setContent($recordModel->get('message'))->parse()->getContent();
			$recordModel->set('message', $message);
		}
	}
}
