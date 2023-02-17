<?php
/**
 * Change stare or delete handler file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Change stare or delete handler class.
 */
class Services_ChangeStateOrDelete_Handler
{
	/**
	 * Register pre delete.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return array
	 */
	public function preDelete(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$result = ['result' => true];
		if (\App\Record::isRelated($recordModel, false, true)) {
			$result = [
				'result' => false,
				'type' => 'confirm',
				'message' => App\Language::translate('LBL_CONFIRM_DELETE_OR_CHANGE_STATE', $recordModel->getModuleName()),
				'hash' => hash('sha256', implode('|', $recordModel->getData()))
			];
		}
		return $result;
	}

	/**
	 * Register pre state change.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return array
	 */
	public function preStateChange(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$result = ['result' => true];
		if (App\Record::STATE_ACTIVE === App\Record::getState($recordModel->getId()) && \App\Record::isRelated($recordModel, false, true)) {
			$result = [
				'result' => false,
				'type' => 'confirm',
				'message' => App\Language::translate('LBL_CONFIRM_DELETE_OR_CHANGE_STATE', $recordModel->getModuleName()),
				'hash' => hash('sha256', implode('|', $recordModel->getData()))
			];
		}
		return $result;
	}
}
