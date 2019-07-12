<?php

/**
 * OSSMailView Relation model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * Add relation.
	 *
	 * @param int       $sourceRecordId
	 * @param int|int[] $destinationRecordId
	 * @param mixed     $params
	 *
	 * @return bool
	 */
	public function addRelation($sourceRecordId, $destinationRecordId, $params = false)
	{
		$return = false;
		if (!\is_array($destinationRecordId)) {
			$destinationRecordId = [$destinationRecordId];
		}

		$typeRelation = empty($this->getData) ? (new OSSMailView_GetRecordToMails_Relation()) : $this->getTypeRelationModel();
		if ($params) {
			$typeRelation->date = $params;
		}
		foreach ($destinationRecordId as $crmId) {
			$destinationModuleName = \App\Record::getType($crmId);
			$data = [
				'CRMEntity' => CRMEntity::getInstance($destinationModuleName),
				'sourceModule' => $destinationModuleName,
				'sourceRecordId' => $crmId,
				'destinationModule' => 'OSSMailView',
				'destinationRecordId' => $sourceRecordId,
			];
			$eventHandler = new App\EventHandler();
			$eventHandler->setModuleName($destinationModuleName);
			$eventHandler->setParams($data);
			$eventHandler->trigger('EntityBeforeLink');
			if ($return = $typeRelation->create($sourceRecordId, $crmId)) {
				CRMEntity::trackLinkedInfo($crmId);
				$eventHandler->trigger('EntityAfterLink');
			}
		}

		return $return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transferDb(array $params)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$result = $dbCommand->update('vtiger_ossmailview_relation', ['crmid' => $params['sourceRecordId'], 'ossmailviewid' => $params['destinationRecordId']], ['crmid' => $params['fromRecordId'], 'ossmailviewid' => $params['destinationRecordId']])->execute();
		if ($result && $parentId = Users_Privileges_Model::getParentRecord($params['sourceRecordId'])) {
			$relationExists = (new App\Db\Query())->from('vtiger_ossmailview_relation')->where(['ossmailviewid' => $params['destinationRecordId'], 'crmid' => $parentId])->exists();
			if (!$relationExists) {
				$date = (new App\Db\Query())->select(['date'])->from('vtiger_ossmailview_relation')->where(['crmid' => $params['sourceRecordId'], 'ossmailviewid' => $params['destinationRecordId']])->scalar();
				$dbCommand->insert('vtiger_ossmailview_relation', [
					'ossmailviewid' => $params['destinationRecordId'],
					'crmid' => $parentId,
					'date' => $date,
				])->execute();
				if ($parentId = Users_Privileges_Model::getParentRecord($parentId)) {
					$relationExists = (new App\Db\Query())->from('vtiger_ossmailview_relation')->where(['ossmailviewid' => $params['destinationRecordId'], 'crmid' => $parentId])->exists();
					if (!$relationExists) {
						$dbCommand->insert('vtiger_ossmailview_relation', [
							'ossmailviewid' => $params['destinationRecordId'],
							'crmid' => $parentId,
							'date' => $date,
						])->execute();
					}
				}
			}
		}
		return $result;
	}
}
