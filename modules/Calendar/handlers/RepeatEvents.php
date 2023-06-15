<?php
/**
 * Select saving mode when event is repeat handler.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
/**
 * Calendar_RepeatEvents_Handler class.
 */
class Calendar_RepeatEvents_Handler
{
	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$response = ['result' => true];
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->get('reapeat') && !isset($recordModel->ext['repeatType'])) {
			$response = [
				'result' => false,
				'type' => 'modal',
				'url' => "index.php?module=Calendar&view=RepeatEvents&record={$recordModel->getId()}",
			];
		}
		return $response;
	}

	/**
	 * Pre delete handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return array
	 */
	public function preDelete(App\EventHandler $eventHandler)
	{
		$response = ['result' => true];
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->get('reapeat') && !isset($recordModel->ext['repeatType'])) {
			$response = [
				'result' => false,
				'type' => 'modal',
				'url' => "index.php?module=Calendar&view=RepeatEventsDelete&record={$recordModel->getId()}",
			];
		}
		return $response;
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
		$responseData = ['result' => true];
		$recordModel = $eventHandler->getRecordModel();
		$repeatCondition = $recordModel->get('reapeat');
		$noRepeatType = !isset($recordModel->ext['repeatType']);
		$newStateExists = isset($recordModel->ext['newState']);
		$isTrashState = \App\Record::STATE_TRASH === $recordModel->ext['newState'];
		if ($repeatCondition && $noRepeatType && $newStateExists && $isTrashState) {
			$responseData = [
				'result' => false,
				'type' => 'modal',
				'url' => "index.php?module=Calendar&view=RepeatEventsDelete&record={$recordModel->getId()}",
			];
		}
		return $responseData;
	}
}
