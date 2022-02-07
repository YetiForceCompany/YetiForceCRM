<?php

/**
 * Webservice premium container - Gets records hierarchy file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Gets records hierarchy class.
 */
class Hierarchy extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** @var int Recursion limit */
	public $limit = 20;

	/** @var string Module name */
	public $moduleName;

	/** @var bool|int Search id in the hierarchy */
	public $findId = false;

	public $mainField;
	public $childField;
	public $records = [];
	public $recursion = [];

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		parent::checkPermission();
		if (1 === $this->getPermissionType()) {
			throw new \Api\Core\Exception('Not available for this type of user', 405);
		}
		$this->moduleName = $this->controller->request->get('module');
	}

	/**
	 * Get method - Gets records hierarchy.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebservicePremium/{moduleName}/Hierarchy",
	 *		summary="Gets records hierarchy",
	 *		description="Hierarchy of records",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Accounts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(response=200, description="Records hierarchy details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_Hierarchy_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_Hierarchy_Response"),
	 *		),
	 *		@OA\Response(response=405, description="`No hierarchy` OR `Not available for this type of user`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Get_Hierarchy_Response",
	 *		title="Base module - Hierarchy response schema",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Records",
	 *			type="object",
	 *			@OA\AdditionalProperties(
	 *				description="Record details",
	 *				type="object",
	 * 				@OA\Property(property="id", type="integer", example=117),
	 * 				@OA\Property(property="parent", type="integer", example=0),
	 * 				@OA\Property(property="name", type="string", example="YetiForce S.A."),
	 * 			),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		$parentId = \App\Record::getParentRecord($this->getUserCrmId());
		if (\in_array($this->getPermissionType(), [\Api\WebservicePremium\Privilege::ACCOUNTS_RELATED_RECORDS_IN_HIERARCHY, \Api\WebservicePremium\Privilege::ACCOUNTS_RELATED_RECORDS_AND_LOWER_IN_HIERARCHY])) {
			$fields = \App\Field::getRelatedFieldForModule($this->moduleName);
			if (!isset($fields[$this->moduleName])) {
				throw new \Api\Core\Exception('No hierarchy', 405);
			}
			$field = $fields[$this->moduleName];
			$queryGenerator = new \App\QueryGenerator($this->moduleName);
			$this->childField = $field['fieldname'];
			$queryGenerator->setFields(['id', $this->childField]);
			$queryGenerator->permissions = false;
			$this->getRecords($queryGenerator, $parentId);
			if (\Api\WebservicePremium\Privilege::ACCOUNTS_RELATED_RECORDS_IN_HIERARCHY === $this->getPermissionType()) {
				$this->getRecords($queryGenerator, $parentId, 'parent');
			} else {
				$this->records[$parentId] = [
					'id' => $parentId,
					'parent' => 0,
					'name' => \App\Record::getLabel($parentId),
				];
			}
		} else {
			$this->records[$parentId] = [
				'id' => $parentId,
				'name' => \App\Record::getLabel($parentId),
			];
		}
		return $this->records;
	}

	/**
	 * Get records in hierarchy.
	 *
	 * @param \App\QueryGenerator $mainQueryGenerator
	 * @param int                 $parentId
	 * @param string              $type
	 *
	 * @return void
	 */
	public function getRecords(\App\QueryGenerator $mainQueryGenerator, int $parentId, string $type = 'child'): void
	{
		if (0 === $this->limit || isset($this->recursion[$parentId][$type])) {
			return;
		}
		--$this->limit;
		$queryGenerator = clone $mainQueryGenerator;
		if ('parent' === $type) {
			$queryGenerator->addCondition('id', $parentId, 'e');
		} else {
			$queryGenerator->addCondition($this->childField, $parentId, 'eid');
		}
		$this->recursion[$parentId][$type] = true;
		foreach ($queryGenerator->createQuery()->all() as $row) {
			$id = $row['id'];
			if (isset($this->records[$id])) {
				continue;
			}
			$this->records[$id] = [
				'id' => $id,
				'parent' => $row[$this->childField],
				'name' => \App\Record::getLabel($id),
			];
			if ($this->findId && $this->findId === $id) {
				$this->limit = 0;
				return;
			}
			if (!empty($row[$this->childField])) {
				if ('parent' === $type) {
					$this->getRecords($mainQueryGenerator, $row[$this->childField], $type);
					$this->getRecords($mainQueryGenerator, $row[$this->childField], 'child');
				} else {
					$this->getRecords($mainQueryGenerator, $id, $type);
				}
			}
		}
	}
}
