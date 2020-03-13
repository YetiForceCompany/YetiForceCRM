<?php
/**
 * Multi reference value cron.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_MultiReference_Cron class.
 */
class Vtiger_MultiReference_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$db = \App\Db::getInstance();
		$executed = [];
		$limit = 1000;
		$rows = (new \App\Db\Query())->from('s_#__multireference')->all();
		foreach ($rows as $multiReference) {
			$sourceModule = $multiReference['source_module'];
			$destModule = $multiReference['dest_module'];
			if (0 === (int) $multiReference['type']) {
				$queryGenerator = (new App\QueryGenerator($sourceModule))
					->setFields(['id'])
					->addCondition('id', $multiReference['lastid'], 'a')
					->setOrder('id', 'DESC');
				$lastId = $queryGenerator->createQuery()->limit(1)->scalar();
				$fields = \Vtiger_MultiReferenceValue_UIType::getFieldsByModules($sourceModule, $destModule);

				$dataReader = $queryGenerator->setOrder('id', 'ASC')->createQuery(true)->limit($limit)->createCommand()->query();
				while ($id = $dataReader->readColumn(0)) {
					foreach ($fields as $fieldId) {
						\Vtiger_Field_Model::getInstanceFromFieldId($fieldId)
							->getUITypeModel()
							->reloadValue($id);
					}
					if ($lastId === $id) {
						$db->createCommand()->delete('s_#__multireference', [
							'source_module' => $sourceModule,
							'dest_module' => $destModule,
							'type' => 0,
						])->execute();
					} else {
						$db->createCommand()
							->update('s_#__multireference', [
								'lastid' => $id,
							], ['source_module' => $sourceModule, 'dest_module' => $destModule, 'type' => 0])
							->execute();
					}
				}
				$dataReader->close();
			} else {
				if (\App\Record::isExists($multiReference['lastid'], $sourceModule)) {
					if (\in_array($multiReference['lastid'], $executed)) {
						continue;
					}
					$fields = Vtiger_MultiReferenceValue_UIType::getFieldsByModules($sourceModule, $destModule);
					foreach ($fields as $fieldId) {
						\Vtiger_Field_Model::getInstanceFromFieldId($fieldId)
							->getUITypeModel()
							->reloadValue($multiReference['lastid']);
						$executed[] = $multiReference['lastid'];
					}
				}
				$db->createCommand()->delete('s_#__multireference', [
					'source_module' => $sourceModule,
					'dest_module' => $destModule,
					'lastid' => $multiReference['lastid'],
					'type' => $multiReference['type'],
				])->execute();
			}
		}
	}
}
