<?php
/**
 * Record Class for SMSNotifier
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Record Class for SMSNotifier
 */
class SMSNotifier_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function defines the ability to create a record
	 * @return boolean
	 */
	public function isCreateable()
	{
		return false;
	}

	/**
	 * Function defines the ability to edit a record
	 * @return boolean
	 */
	public function isEditable()
	{
		return false;
	}

	/**
	 * Function sends sms
	 * @param string $message
	 * @param string[] $toNumbers
	 * @param int[] $recordIds
	 * @param string $ralModuleName
	 * @return bool
	 */
	public static function sendSMS($message, $toNumbers, $recordIds, $ralModuleName)
	{
		$moduleName = 'SMSNotifier';
		$recordModel = self::getCleanInstance($moduleName);
		$recordModel->set('message', $message);
		$recordModel->set('smsnotifier_status', 'PLL_UNDEFINED');
		$recordModel->save();
		if ($recordModel->getId()) {
			$recordModel->isNew = false;
			$recordModel->getEntity()->save_related_module($moduleName, $recordModel->getId(), $ralModuleName, $recordIds);
		}
		$provider = SMSNotifier_Module_Model::getActiveProviderInstance();
		$numbers = is_array($toNumbers) ? implode(',', $toNumbers) : $toNumbers;
		$provider->set($provider->toName, $numbers);
		$provider->set($provider->messageName, $message);
		$result = $provider->send();
		if ($result) {
			$recordModel->set('smsnotifier_status', 'PLL_DELIVERED');
		} else {
			$recordModel->set('smsnotifier_status', 'PLL_FAILED');
		}
		$recordModel->setHandlerExceptions(['disableWorkflow' => true]);
		$recordModel->save();
		return $result;
	}
}
