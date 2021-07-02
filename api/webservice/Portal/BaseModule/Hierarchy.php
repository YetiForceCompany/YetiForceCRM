<?php

/**
 * Portal container - Records hierarchy action file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Portal container - Records hierarchy action class.
 */
class Hierarchy extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** @var int Pecursion limit */
	public $limit = 100;

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
	 * Get records hierarchy.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return array
	 * @OA\Get(
	 *		path="/webservice/Portal/{moduleName}/Hierarchy",
	 *		summary="Hierarchy of records",
	 *		description="Get records hierarchy",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Accounts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(response=200, description="Records hierarchy details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Hierarchy_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Hierarchy_ResponseBody"),
	 *		),
	 *		@OA\Response(response=405, description="`No hierarchy` OR `Not available for this type of user`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseAction_Hierarchy_ResponseBody",
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
	 * 				@OA\Property(property="name", type="string", example="YetiForce Sp. z o.o."),
	 * 			),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		$parentCrmId = $this->getParentCrmId();
		if ($this->getPermissionType() > 2) {
			$fields = \App\Field::getRelatedFieldForModule($this->moduleName);
			if (!isset($fields[$this->moduleName])) {
				throw new \Api\Core\Exception('No hierarchy', 405);
			}
			$field = $fields[$this->moduleName];
			$entityFieldInfo = \App\Module::getEntityInfo($this->moduleName);
			$queryGenerator = new \App\QueryGenerator($this->moduleName);
			$this->mainFieldName = $entityFieldInfo['fieldname'];
			$this->childField = $field['fieldname'];
			$this->childColumn = "{$field['tablename']}.{$field['columnname']}";
			$queryGenerator->setFields(['id', $this->childField, $this->mainFieldName]);
			$queryGenerator->permissions = false;
			$this->getRecords($queryGenerator, $parentCrmId);
		}
		if (!isset($this->records[$parentCrmId])) {
			$this->records[$parentCrmId] = [
				'id' => $parentCrmId,
				'name' => \App\Record::getLabel($parentCrmId),
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
			$queryGenerator->addNativeCondition([$this->childColumn => $parentId]);
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
				'name' => $row[$this->mainFieldName],
			];
			if ($this->findId && $this->findId === $id) {
				$this->limit = 0;
				return;
			}
			if (!empty($row[$this->childField])) {
				if (4 === $this->getPermissionType()) {
					$this->getRecords(clone $mainQueryGenerator, $row[$this->childField], 'parent');
					$this->getRecords(clone $mainQueryGenerator, $id, 'parent');
				} elseif (3 === $this->getPermissionType()) {
					$this->getRecords(clone $mainQueryGenerator, $row[$this->childField], 'child');
					$this->getRecords(clone $mainQueryGenerator, $id, 'child');
				}
			}
		}
	}
}
