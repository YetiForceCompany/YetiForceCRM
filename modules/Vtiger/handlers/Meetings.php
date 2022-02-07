<?php

/**
 * Meetings handler file.
 *
 * @package Handler
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Vtiger_Meetings_Handler class.
 */
class Vtiger_Meetings_Handler
{
	/**
	 * EntityBeforeSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$meeting = \App\MeetingService::getInstance();
		if ($meeting->isActive()) {
			foreach ($recordModel->getModule()->getFieldsByType(['meetingUrl'], true) as $fieldName => $fieldModel) {
				if (($url = $recordModel->get($fieldName)) &&
					0 === strpos($url, $meeting->get('url')) &&
					($expFieldName = $fieldModel->getFieldParams()['exp'] ?? '') &&
					$recordModel->getField($expFieldName) &&
					($recordModel->isNew() || false !== $recordModel->getPreviousValue($expFieldName))
				) {
					$expDate = $recordModel->get($expFieldName);
					if ($expDate) {
						$expDate = date('Y-m-d', strtotime($expDate)) . ' 23:59:59';
					} else {
						$expDate = date('Y-m-d') . ' 23:59:59';
					}
					$room = $meeting->getRoomFromUrl($url);
					$url = $meeting->getUrl(['room' => $room, 'exp' => strtotime($expDate)]);
					$recordModel->set($fieldModel->getName(), $url);
					$recordModel->setDataForSave([$fieldModel->getTableName() => [$fieldModel->getColumnName() => $url]]);
				}
			}
		}
	}
}
