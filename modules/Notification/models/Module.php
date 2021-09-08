<?php

/**
 * Notification Record Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Notification_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Get query.
	 *
	 * @param array $conditions
	 *
	 * @return \App\Db\Query
	 */
	public function getQuery(array $conditions = []): App\Db\Query
	{
		$queryGenerator = new App\QueryGenerator($this->getName());
		$queryGenerator->setFields(['description', 'assigned_user_id', 'id', 'title', 'link', 'linkextend', 'process', 'subprocess', 'createdtime', 'notification_type', 'smcreatorid', 'notification_type']);
		if (!empty($conditions)) {
			$queryGenerator->addNativeCondition($conditions);
		}
		$queryGenerator->addNativeCondition(['smownerid' => \App\User::getCurrentUserId()]);
		$queryGenerator->addNativeCondition(['u_#__notification.notification_status' => 'PLL_UNREAD']);
		return $queryGenerator->createQuery();
	}

	/**
	 * Function returns notifications list.
	 *
	 * @param int   $limit
	 * @param array $conditions
	 *
	 * @return Vtiger_Record_Model[]
	 */
	public function getEntriesInstance($limit = false, $conditions = [])
	{
		$query = $this->getQuery($conditions);
		$query->andWhere(['u_#__notification.notification_status' => 'PLL_UNREAD']);
		if (!empty($limit)) {
			$query->limit($limit);
		}
		$dataReader = $query->createCommand()->query();
		$entries = [];
		while ($row = $dataReader->read()) {
			$recordModel = Vtiger_Record_Model::getCleanInstance($this->getName());
			$recordModel->setData($row);
			$entries[$row['id']] = $recordModel;
		}
		$dataReader->close();
		return $entries;
	}

	/**
	 * Get number of notifications.
	 *
	 * @return int
	 */
	public function getEntriesCount(): int
	{
		$query = $this->getQuery();
		$query->andWhere(['u_#__notification.notification_status' => 'PLL_UNREAD']);
		return $query->count();
	}

	/**
	 * Function gets notifications to be sent.
	 *
	 * @param int    $userId
	 * @param array  $modules
	 * @param string $startDate
	 * @param string $endDate
	 * @param bool   $isExists
	 *
	 * @return array|bool
	 */
	public static function getEmailSendEntries($userId, $modules, $startDate, $endDate, $isExists = false)
	{
		$query = (new \App\Db\Query())
			->from('u_#__notification')
			->innerJoin('vtiger_crmentity', 'u_#__notification.notificationid = vtiger_crmentity.crmid')
			->leftJoin('vtiger_crmentity as crmlink', 'u_#__notification.link = crmlink.crmid')
			->leftJoin('vtiger_crmentity as crmprocess', 'u_#__notification.process = crmprocess.crmid')
			->leftJoin('vtiger_crmentity as crmsubprocess', 'u_#__notification.subprocess = crmsubprocess.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_crmentity.smownerid' => $userId])
			->andWhere(['or', ['in', 'crmlink.setype', $modules], ['in', 'crmprocess.setype', $modules], ['in', 'crmsubprocess.setype', $modules]])
			->andWhere(['between', 'vtiger_crmentity.createdtime', (string) $startDate, $endDate])
			->andWhere(['notification_status' => 'PLL_UNREAD']);
		if ($isExists) {
			return $query->exists();
		}
		$query->select(['u_#__notification.*', 'vtiger_crmentity.*']);
		$dataReader = $query->createCommand()->query();
		$entries = [];
		while ($row = $dataReader->read()) {
			$recordModel = Vtiger_Record_Model::getCleanInstance('Notification');
			$recordModel->setData($row);
			$entries[$row['notification_type']][$row['notificationid']] = $recordModel;
		}
		$dataReader->close();
		return $entries;
	}

	/**
	 * Function to get types of notification.
	 *
	 * @return array
	 */
	public function getTypes()
	{
		return Vtiger_Field_Model::getInstance('notification_type', Vtiger_Module_Model::getInstance($this->getName()))->getPicklistValues();
	}
}
