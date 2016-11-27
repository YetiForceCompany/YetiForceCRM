<?php
/**
 * Multi reference value cron
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$db = \App\Db::getInstance();
$executed = [];
$rows = (new \App\Db\Query())->from('s_yf_multireference')->all();
foreach ($rows as &$multireference) {
	if ($multireference['type'] == 0) {
		$entity = CRMEntity::getInstance($multireference['source_module']);
		$queryGenerator = new App\QueryGenerator($multireference['source_module']);
		$queryGenerator->setFields(['id']);
		$queryGenerator->addCondition('id', $multireference['lastid'], 'a');
		$queryGenerator->setOrder('id', 'ASC');

		$fields = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($multireference['source_module'], $multireference['dest_module']);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($id = $dataReader->readColumn(0)) {
			foreach ($fields as &$field) {
				$fieldModel = new Vtiger_Field_Model ();
				$fieldModel->initialize($field);
				$UITypeModel = $fieldModel->getUITypeModel();
				$UITypeModel->reloadValue($multireference['source_module'], $id);
			}
			$db->createCommand()
				->update('s_#__multireference', [
					'lastid' => $id,
					], ['source_module' => $multireference['source_module'], 'dest_module' => $multireference['dest_module']])
				->execute();
		}
		unset($queryGenerator);
	} else {
		vglobal('currentModule', $multireference['dest_module']);
		$sourceRecordModel = Vtiger_Record_Model::getInstanceById($multireference['lastid'], $multireference['dest_module']);
		$targetModel = Vtiger_RelationListView_Model::getInstance($sourceRecordModel, $multireference['source_module']);
		$relationModel = $targetModel->getRelationModel();
		if (!$targetModel->getRelationModel()) {
			continue;
		}
		$query = $targetModel->getRelationQuery();
		$query->setFields(['id']);
		$dataReader = $query->createCommand()->query();
		while ($crmid = $dataReader->readColumn(0)) {
			if (in_array($crmid, $executed)) {
				continue;
			}
			$fields = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($multireference['source_module'], $multireference['dest_module']);
			foreach ($fields as $field) {
				$fieldModel = new Vtiger_Field_Model();
				$fieldModel->initialize($field);
				$UITypeModel = $fieldModel->getUITypeModel();
				$UITypeModel->reloadValue($multireference['source_module'], $crmid);
			}
			$executed[] = $crmid;
		}
		unset($query);
	}
	$db->createCommand()->delete('s_#__multireference', [
		'source_module' => $multireference['source_module'],
		'dest_module' => $multireference['dest_module'],
		'type' => $multireference['type']
	])->execute();
}
