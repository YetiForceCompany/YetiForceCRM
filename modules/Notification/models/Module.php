<?php

/**
 * Notification Record Model
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_Module_Model extends Vtiger_Module_Model
{

	public static function getNumberOfEntries()
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$query = 'SELECT count(*) FROM u_yf_notification
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = u_yf_notification.id
			WHERE vtiger_crmentity.smownerid = ? AND vtiger_crmentity.deleted = ? AND notification_status = ?';

		$result = $db->pquery($query, [$currentUser->getId(), 0, 'PLL_UNREAD']);
		$count = $db->getSingleValue($result);
		$max = AppConfig::module('Home', 'MAX_NUMBER_NOTIFICATIONS');
		return $count > $max ? $max : $count;
	}

	public function getEntries($limit = false)
	{
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$queryGenerator = new QueryGenerator($this->getName());
		$queryGenerator->setFields(['description', 'smwonerid', 'id', 'title', 'relatedid', 'relatedmodule', 'createdtime', 'type']);
		$queryGenerator->setCustomCondition([
			'glue' => 'AND',
			'tablename' => 'vtiger_crmentity',
			'column' => 'vtiger_crmentity.smownerid',
			'operator' => '=',
			'value' => $currentUser->getId(),
		]);
		$queryGenerator->setCustomCondition([
			'glue' => 'AND',
			'tablename' => 'u_yf_notification',
			'column' => 'notification_status',
			'operator' => '=',
			'value' => '\'PLL_UNREAD\'',
		]);

		$query = $queryGenerator->getQuery();
		if (!empty($limit)) {
			$query .= sprintf(' LIMIT %d', $limit);
		}
		$db = PearDatabase::getInstance();
		$result = $db->query($query);
		$entries = [];
		while ($row = $db->getRow($result)) {
			$recordModel = Vtiger_Record_Model::getCleanInstance('Notification');
			$recordModel->setData($row);
			$entries[$row['id']] = $recordModel;
		}
		return $entries;
	}
}
