<?php
/**
 * Multi reference value cron.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
$db = \App\Db::getInstance();
$executed = [];
$limit = 1000;
$rows = (new \App\Db\Query())->from('s_#__multireference')->all();
foreach ($rows as &$multireference) {
	if ((int) $multireference['type'] === 0) {
		$entity = CRMEntity::getInstance($multireference['source_module']);
		$queryGenerator = new App\QueryGenerator($multireference['source_module']);
		$queryGenerator->setFields(['id']);
		$queryGenerator->addCondition('id', $multireference['lastid'], 'a');
		$queryGenerator->setOrder('id', 'ASC');

		$fields = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($multireference['source_module'], $multireference['dest_module']);
		$dataReader = $queryGenerator->createQuery()->limit($limit)->createCommand()->query();
		unset($queryGenerator);
		$queryGenerator = new App\QueryGenerator($multireference['source_module']);
		$queryGenerator->setFields(['id']);
		$queryGenerator->addCondition('id', $multireference['lastid'], 'a');
		$queryGenerator->setOrder('id', 'DESC');
		$lastId = $queryGenerator->createQuery()->limit(1)->scalar();
		unset($queryGenerator);
		while ($id = $dataReader->readColumn(0)) {
			foreach ($fields as &$field) {
				$fieldModel = new Vtiger_Field_Model();
				$fieldModel->initialize($field);
				$UITypeModel = $fieldModel->getUITypeModel();
				$UITypeModel->reloadValue($multireference['source_module'], $id);
			}
			if ($lastId === $id) {
				$db->createCommand()->delete('s_#__multireference', [
					'source_module' => $multireference['source_module'],
					'dest_module' => $multireference['dest_module'],
					'type' => 0,
				])->execute();
			} else {
				$db->createCommand()
					->update('s_#__multireference', [
						'lastid' => $id,
						], ['source_module' => $multireference['source_module'], 'dest_module' => $multireference['dest_module'], 'type' => 0])
						->execute();
			}
		}
		$dataReader->close();
	} else {
		if (\App\Record::isExists($multireference['lastid'], $multireference['source_module'])) {
			if (in_array($multireference['lastid'], $executed)) {
				continue;
			}
			$fields = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($multireference['source_module'], $multireference['dest_module']);
			foreach ($fields as $field) {
				$fieldModel = new Vtiger_Field_Model();
				$fieldModel->initialize($field);
				$UITypeModel = $fieldModel->getUITypeModel();
				$UITypeModel->reloadValue($multireference['source_module'], $multireference['lastid']);
				$executed[] = $multireference['lastid'];
			}
		}
		$db->createCommand()->delete('s_#__multireference', [
			'source_module' => $multireference['source_module'],
			'dest_module' => $multireference['dest_module'],
			'lastid' => $multireference['lastid'],
			'type' => $multireference['type'],
		])->execute();
	}
}
