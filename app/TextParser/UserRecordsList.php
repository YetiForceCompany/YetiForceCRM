<?php

/**
 * User record filtered list class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\TextParser;

/**
 *  User Records Filtered List class.
 */
class UserRecordsList extends Base
{
	/** @var string Class name */
	public $name = 'LBL_USER_RECORD_FILTERED_LIST';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template
	 * Example: $(custom : UserRecordsList|SSalesProcesses|subject:related_to|[[["description","y",""]]]|[["createdtime","ASC"]]|50)$
	 */
	public $default = '$(custom : UserRecordsList|__MODULE_NAME__|__FIELDS_TO_SHOW__|__CONDITIONS__|__ORDER_BY__|__LIMIT__|__VIEW_ID__|__ADVANCE_CONDITIONS__)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$html = '';
		$moduleName = $this->params[0];
		if (!empty($textParserParams = $this->textParser->getParam('textParserParams')) && isset($textParserParams['userId'])
			&& !empty($userId = $textParserParams['userId']) && \App\User::isExists($userId) && \App\Module::isModuleActive($moduleName)
		) {
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$fields = [];
			$fieldsName = !empty($this->params[1]) ? explode(':', $this->params[1]) : $moduleModel->getNameFields();
			foreach ($fieldsName as $fieldName) {
				if (!($fieldModel = $moduleModel->getFieldByName($fieldName)) || !$fieldModel->isActiveField()) {
					continue;
				}
				$fields[$fieldName] = $fieldModel;
			}
			$advConditions = $this->params[6] ?? null;
			$viewId = $this->params[5] ?? null;
			$limit = $this->params[4] ?? \App\Config::performance('REPORT_RECORD_NUMBERS');
			$orderBy = $this->params[3] ?? null;
			$searchParams = $this->params[2] ?? null;
			$queryGenerator = (new \App\QueryGenerator($moduleName, $userId))->setFields(array_merge(['id'], array_keys($fields)));

			if (!empty($viewId)) {
				$queryGenerator->initForCustomViewById($viewId);
			}
			if (!\App\Json::isEmpty($searchParams)) {
				$searchParams = \App\Json::decode($searchParams);
				$this->parseConditions($queryGenerator, $searchParams, $userId);
			}
			if (!\App\Json::isEmpty($advConditions)) {
				$advConditions = \App\Json::decode($advConditions);
				foreach ($advConditions as $advCondition) {
					[$relationId, $operator, $condition] = array_pad($advCondition, 3, null);
					$this->addAdvConditions($queryGenerator, $relationId, $operator, $condition, $userId);
				}
			}
			if ($orderBy && !\App\Json::isEmpty($orderBy)) {
				$orderBy = \App\Json::decode($orderBy);
				foreach ($orderBy as $order) {
					$queryGenerator->setOrder($order[0], $order[1]);
				}
			}

			$dataReader = $queryGenerator->setLimit($limit)->createQuery()->createCommand()->query();
			$entries = [];
			if (!empty($fields)) {
				$count = 1;
				while ($row = $dataReader->read()) {
					if (isset($entries[$row['id']])) {
						continue;
					}
					$recordHtml = '';
					$entriesPart = [];
					$recordModel = 1 === \count($row) ? \Vtiger_Record_Model::getInstanceById($row['id']) : $moduleModel->getRecordFromArray($row);
					foreach ($fields as $field) {
						if ($recordModel->isEmpty($field->getName()) || !($value = $recordModel->getDisplayValue($field->getName(), false, true))) {
							continue;
						}
						if ($field->isReferenceField()) {
							$relModule = \App\Record::getType($recordModel->get($field->getName()));
							if ($relModule && 'Users' !== $relModule && \App\Privilege::isPermitted($relModule, 'DetailView', $recordModel->get($field->getName()), $userId)) {
								$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($recordModel->get($field->getName()));
								$entriesPart[] = ' [<a href="' . \App\Config::main('site_URL') . $relatedRecordModel->getDetailViewUrl() . '">' . $value . '</a>] ';
							} else {
								$entriesPart[] = " [{$value}] ";
							}
						} else {
							$recordHtml .= " {$value} ";
						}
					}
					if (!empty($recordHtml)) {
						if ('ModComments' === $moduleName) {
							$entries[$recordModel->getId()] = "{$count}. " . ($entriesPart ? implode(', ', $entriesPart) . '<br>  ' : '') . " {$recordHtml} ";
						} else {
							$entries[$recordModel->getId()] = "{$count}. " . ' <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordHtml . '</a> ' . implode(' ', $entriesPart);
						}
					}
					++$count;
				}
			}
			$html = implode('<br>', $entries);
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}

	/**
	 * Add advance conditions.
	 *
	 * @param \App\QueryGenerator $queryGenerator
	 * @param int                 $relationId
	 * @param string              $operator
	 * @param array|null          $condition
	 * @param int|null            $userId
	 */
	private function addAdvConditions(\App\QueryGenerator $queryGenerator, int $relationId, string $operator, ?array $condition, ?int $userId)
	{
		$relationModel = \Vtiger_Relation_Model::getInstanceById($relationId);
		if (\Vtiger_Relation_Model::RELATION_M2M !== $relationModel->getRelationType()) {
			$parentModuleName = $relationModel->getParentModuleModel()->getName();
			$subQueryBase = (new \App\QueryGenerator($parentModuleName, $userId))->setFields(['id'])->createQuery();
			$subQuery = (new \App\QueryGenerator($relationModel->getRelationModuleName(), $userId));
			if ($condition) {
				$this->parseConditions($subQuery, $condition, $userId);
			}
			foreach ($relationModel->getRelationModuleModel()->getReferenceFieldsForModule($parentModuleName) as $fieldModel) {
				$subQuery->permissions = false;
				$subQuery = $subQuery->setFields([$fieldModel->getName()])
					->addNativeCondition([$subQuery->getQueryField($fieldModel->getName())->getColumnName() => $subQueryBase])
					->setDistinct($fieldModel->getName())
					->createQuery();
			}
			switch ($operator) {
				case 'y':
					$queryGenerator->addNativeCondition(['not', ['in', $queryGenerator->getQueryField('id')->getColumnName(), $subQuery]])
						->setFields(['id'])->setDistinct('id');
					break;
				case 'ny':
					$queryGenerator->addNativeCondition([$queryGenerator->getQueryField('id')->getColumnName() => $subQuery])
						->setFields(['id'])->setDistinct('id');
					break;
				default:
			}
		} else {
			$relation = $relationModel->getTypeRelationModel();
			$tableName = \get_class($relation)::TABLE_NAME;
			$subQuery = (new \App\QueryGenerator($relationModel->getRelationModuleName(), $userId))->setFields(['id']);
			if ($condition) {
				$this->parseConditions($subQuery, $condition, $userId);
			}
			$subQuery = $subQuery->createQuery();
			switch ($operator) {
				case 'y':
					$subQueryBase = (new \App\QueryGenerator($relationModel->getParentModuleModel()->getName(), $userId))->setFields(['id'])
						->addJoin(['INNER JOIN', $tableName, "({$tableName}.relcrmid = vtiger_crmentity.crmid OR {$tableName}.crmid = vtiger_crmentity.crmid)"])
						->addNativeCondition(['or', ["{$tableName}.crmid" => $subQuery], ["{$tableName}.relcrmid" => $subQuery]])->createQuery();
					$queryGenerator->addNativeCondition(['not', [$queryGenerator->getQueryField('id')->getColumnName() => $subQueryBase]])
						->setFields(['id'])->setDistinct('id');
					break;
				case 'ny':
					$queryGenerator->addJoin(['INNER JOIN', $tableName, "({$tableName}.relcrmid = vtiger_crmentity.crmid OR {$tableName}.crmid = vtiger_crmentity.crmid)"]);
					$queryGenerator->addNativeCondition(['or', ["{$tableName}.crmid" => $subQuery], ["{$tableName}.relcrmid" => $subQuery]])
						->setFields(['id'])->setDistinct('id');
					break;
				default:
			}
		}
	}

	/**
	 * Parse conditions.
	 *
	 * @param \App\QueryGenerator $queryGenerator
	 * @param array               $searchParams
	 * @param int|null            $userId
	 */
	private function parseConditions(\App\QueryGenerator $queryGenerator, array $searchParams, ?int $userId)
	{
		foreach ($searchParams as &$conditions) {
			if (empty($conditions)) {
				continue;
			}
			foreach ($conditions as &$condition) {
				if ('om' === $condition[1]) {
					$condition[1] = 'e';
					$condition[2] = $userId;
				} elseif ('nom' === $condition[1]) {
					$condition[1] = 'n';
					$condition[2] = $userId;
				}
			}
		}
		$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($searchParams);
		$queryGenerator->parseAdvFilter($transformedSearchParams);
	}
}
