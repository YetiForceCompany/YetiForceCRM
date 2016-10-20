<?php

/**
 * Notification Record Model
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function to get number of unread notification
	 * @return int
	 */
	public static function getNumberOfEntries()
	{
		$count = (new App\Db\Query())->from('u_yf_notification')
			->innerJoin('vtiger_crmentity', 'u_yf_notification.id = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.smownerid' => Users_Record_Model::getCurrentUserModel()->getId(), 'vtiger_crmentity.deleted' => 0, 'notification_status' => 'PLL_UNREAD'])
			->count();
		$max = AppConfig::module('Home', 'MAX_NUMBER_NOTIFICATIONS');
		return $count > $max ? $max : $count;
	}

	/**
	 * Function shoud return array with objects <Notification_Record_Model>
	 * @param int $limit
	 * @param string $conditions
	 * @param int $userId
	 * @param boolean $groupBy
	 * @return array
	 */
	public function getEntries($limit = false, $conditions = false, $userId = false, $groupBy = false)
	{
		$queryGenerator = new QueryGenerator($this->getName());
		$queryGenerator->setFields(['description', 'smwonerid', 'id', 'title', 'link', 'process', 'subprocess', 'createdtime', 'notification_type', 'smcreatorid']);
		if (empty($userId)) {
			$userId = Users_Privileges_Model::getCurrentUserModel()->getId();
		}
		$queryGenerator->setCustomCondition([
			'glue' => 'AND',
			'tablename' => 'vtiger_crmentity',
			'column' => 'vtiger_crmentity.smownerid',
			'operator' => '=',
			'value' => $userId,
		]);
		$query = $queryGenerator->getQuery();
		if (!empty($conditions)) {
			$query .= $conditions;
		}
		$query .= ' AND u_yf_notification.notification_status = \'PLL_UNREAD\' ';
		if (!empty($limit)) {
			$query .= sprintf(' LIMIT %d', $limit);
		}
		$db = PearDatabase::getInstance();
		$result = $db->query($query);
		$entries = [];
		while ($row = $db->getRow($result)) {
			$recordModel = Vtiger_Record_Model::getCleanInstance('Notification');
			$recordModel->setData($row);
			if ($groupBy) {
				$entries[$row['type']][$row['id']] = $recordModel;
			} else {
				$entries[$row['id']] = $recordModel;
			}
		}
		return $entries;
	}

	/**
	 * Function to get types of notification
	 * @return array
	 */
	public function getTypes()
	{
		$fieldModel = Vtiger_Field_Model::getInstance('notification_type', Vtiger_Module_Model::getInstance('Notification'));
		return $fieldModel->getPicklistValues();
	}
}
