<?php

/**
 * User record without related class.
 *
 * @package 	App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\TextParser;

/**
 *  User Records Without Related class.
 */
class UserRecordsWithoutRelated extends Base
{
	/** @var string Class name */
	public $name = 'LBL_USER_RECORD_WITHOUT_RELATED';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template */
	public $default = '$(custom : UserRecordsWithoutRelated|__MODULE__|__RELATED_MODULE_TO_CHECK__|__FIELDS_TO_SHOW__|)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$html = '';
		$moduleName = $this->params[0];
		$relatedModuleName = $this->params[1];
		if (!empty($textParserParams = $this->textParser->getParam('textParserParams')) && isset($textParserParams['userId']) &&
			!empty($userId = $textParserParams['userId']) && \App\User::isExists($userId) && \App\Module::isModuleActive($moduleName)
		) {
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$relatedModuleModel = \Vtiger_Module_Model::getInstance($relatedModuleName);
			$fields = [];
			$fieldsName = !empty($this->params[2]) ? explode(':', $this->params[2]) : $moduleModel->getNameFields();
			foreach ($fieldsName as $fieldName) {
				if (!($fieldModel = $moduleModel->getFieldByName($fieldName)) || !$fieldModel->isActiveField()) {
					continue;
				}
				$fields[$fieldName] = $fieldModel;
			}
			foreach ($moduleModel->getRelations() as $relation) {
				$relatedModules[$relation->get('relatedModuleName')] = $relation;
			}
			if (isset($relatedModules[$relatedModuleName])) {
				$moduleTable = $moduleModel->getEntityInstance()->table_name;
				$moduleIndexColumn = $moduleModel->getEntityInstance()->table_index;
				if (\Vtiger_Relation_Model::getInstance($moduleModel, $relatedModuleModel)->isDirectRelation()) {
					$relatedTable = $relatedModules[$relatedModuleName]->getRelationField()->getTableName();
					$relatedColumn = $relatedModules[$relatedModuleName]->getRelationField()->getColumnName();
					$relatedFieldName = $relatedModuleModel->getFieldByColumn($relatedColumn)->getName();
					$queryGeneratorRelated = (new \App\QueryGenerator($relatedModuleName))
						->addCondition('assigned_user_id', $userId, 'e')
						->addCondition($relatedFieldName, false, 'ny')
						->setFields([$relatedFieldName])->createQuery();
					$queryGenerator = (new \App\QueryGenerator($moduleName))
						->addCondition('assigned_user_id', $userId, 'e')
						->addJoin(['LEFT JOIN',$relatedTable, 'vtiger_crmentity.crmid = ' . $relatedTable . '.' . $relatedColumn])
						->addNativeCondition(['NOT',["{$moduleTable}.{$moduleIndexColumn}" => $queryGeneratorRelated]])
						->setFields(array_merge(['id'], array_keys($fields)));
					$query = $queryGenerator->createQuery();
					$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
					$dataReader = $query->createCommand()->query();
				} else {
					$queryGeneratorRelated = (new \App\QueryGenerator($relatedModuleName));
					$queryGeneratorRelated = $queryGeneratorRelated->addCondition('assigned_user_id', $userId, 'e')
						->setFields(['id'])
						->createQuery();
					$queryGenerator = (new \App\QueryGenerator($moduleName))
						->addCondition('assigned_user_id', $userId, 'e')
						->addNativeCondition(['OR', ['IS', 'vtiger_crmentityrel.relcrmid', null],  ['NOT IN', 'vtiger_crmentityrel.relcrmid', $queryGeneratorRelated]])
						->addNativeCondition(['OR', ['IS', 'vtiger_crmentityrel.crmid', null], ['NOT IN', 'vtiger_crmentityrel.crmid', $queryGeneratorRelated]])
						->setFields(array_merge(['id'], array_keys($fields)))
						->addJoin(['LEFT JOIN', 'vtiger_crmentityrel', 'vtiger_crmentity.crmid = vtiger_crmentityrel.crmid OR vtiger_crmentity.crmid = vtiger_crmentityrel.relcrmid']);
					$query = $queryGenerator->createQuery();
					$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
					$dataReader = $query->createCommand()->query();
				}
				if (!empty($fields)) {
					$count = 1;
					while ($row = $dataReader->read()) {
						$recordModel = $moduleModel->getRecordFromArray($row);
						$html .= $count . '. ';
						foreach ($fields as $fieldName => $fieldModel) {
							$relatedRecordModel = null;
							if ($fieldModel->isReferenceField()) {
								if (($relatedTo = $recordModel->get($fieldName)) && \App\Record::isExists($relatedTo)) {
									$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($relatedTo);
									if (!empty($relatedRecordModel) && $relatedRecordModel->isViewable()) {
										$html .= ' [<a href="' . \App\Config::main('site_URL') . $relatedRecordModel->getDetailViewUrl() . '">' . $relatedRecordModel->getName() . '</a>] ';
									} else {
										$html .= ' [' . $relatedRecordModel->getName() . '] ';
									}
								}
							} else {
								$html .= " {$recordModel->getDisplayValue($fieldName, false, true)} ";
							}
						}
						$html .= ' <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . \App\Language::translate('LBL_GO_TO_PREVIEW') . '</a><br>';
						++$count;
					}
				}
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
