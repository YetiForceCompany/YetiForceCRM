<?php

namespace Api\Portal\BaseModule;

/**
 * Get fields class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Fields extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get data about fields.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/{moduleName}/Fields",
	 *		summary="Get data about fields",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *		},
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(
	 *				type="string"
	 *			),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 * 				@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="Request body does not occur",
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Fields details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Fields_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Fields_ResponseBody"),
	 *			@OA\MediaType(
	 *				mediaType="text/html",
	 *				@OA\Schema(ref="#/components/schemas/BaseModule_Fields_ResponseBody")
	 *			),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_Fields_ResponseBody",
	 *		title="Base module - Response action fields",
	 *		description="Module action fields response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={"0", "1"},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Fields parameters",
	 *			type="object",
	 *			@OA\Property(
	 *				property="fields",
	 *				description="List of all available fields in the module",
	 *				type="object",
	 *				@OA\Property(property="id", type="integer", description="Check if record is editable", example="24862"),
	 *				@OA\Items(items="isEditable", description="Check if record is editable", type="boolean", example="false"),
	 *				@OA\Items(items="isViewable", description="Check if record is viewable", type="boolean", example="false"),
	 *				@OA\Items(items="isEditableReadOnly", description="Check if record is editable or read only", type="boolean", example="false"),
	 *				@OA\Items(items="sequence", description="Sequence field", type="integer", example="24862"),
	 *				@OA\Items(items="fieldparams", description="Field params", type="array"),
	 *				@OA\Items(items="dbStructure", description="Info about field structure in database", type="array"),
	 *				@OA\Items(items="isEmptyPicklistOptionAllowed", description="Defines empty picklist element availability", type="boolean", example="false"),
	 *				@OA\Items(
	 *					items="referenceList",
	 *					description="List of modules the field refernced to",
	 *					type="array",
	 *					@OA\Items(items="Accounts", description="Module name", example="Accounts"),
	 *				),
	 *				@OA\Items(
	 *					items="treeValues",
	 *					description="Tree values for jstree",
	 *					type="array",
	 *					@OA\Items(items="id", description="Number tree without prefix", type="integer", example="1"),
	 *					@OA\Items(items="tree", description="Template badge", type="string", example="T1"),
	 *					@OA\Items(items="parent", description="Template parent badge", type="string", example="T1"),
	 *					@OA\Items(items="text", description="Template name", type="string", example="Category"),
	 *				),
	 *			),
	 *			@OA\Property(
	 *				property="blocks",
	 *				description="Block data",
	 *				type="object",
	 *				@OA\AdditionalProperties(
	 *					description="Block details",
	 *					type="object",
	 *					@OA\Property(property="id", description="Block id", type="integer", example="195"),
	 *					@OA\Property(property="tabid", description="Module id", type="integer", example="9"),
	 *					@OA\Property(property="label", description="Block label", type="string", example="Account details"),
	 *					@OA\Property(property="sequence", description="Block sequence", type="integer", example="1"),
	 *					@OA\Property(property="showtitle", description="Specifies whether the title should be visible", type="integer", example="0"),
	 *					@OA\Property(property="visible", description="Determines the visibility", type="integer", example="0"),
	 *					@OA\Property(property="increateview", description="Determines the visibility in creat view", type="integer", example="0"),
	 *					@OA\Property(property="ineditview", description="Determines the visibility in edit view", type="integer", example="0"),
	 *					@OA\Property(property="indetailview", description="Determines the visibility in detail view", type="integer", example="0"),
	 *					@OA\Property(property="display_status", description="Determines whether the block should be expanded", type="integer", example="2"),
	 *					@OA\Property(property="iscustom", description="Determines if the block has been added by the user", type="integer", example="0"),
	 *					@OA\Property(property="icon", description="Block icon class", type="string", example="far fa-calendar-alt"),
	 *					@OA\Property(property="name", description="Block name translated into the user's language", type="string", example="Informacje podstawowe o firmie"),
	 *				),
	 *			),
	 *			@OA\Property(
	 *				property="inventory",
	 *				description="Inventory field list",
	 *				type="object",
	 *				@OA\AdditionalProperties(
	 *					description="Inventory field details",
	 *					type="object",
	 *					@OA\Property(property="label", description="Field label translated into the user's language", type="string", example="Double"),
	 *					@OA\Property(property="type", description="Field type", type="string", example="Double"),
	 *					@OA\Property(property="columnname", description="Field column name in db", type="string", example="double"),
	 *					@OA\Property(property="isSummary", description="Is the field contains summary", type="boolean", example="false"),
	 *					@OA\Property(property="isVisibleInDetail", description="Check if field is visible in detail view", type="boolean", example="true"),
	 *				),
	 * 			),
	 *		),
	 * ),
	 */
	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$module = \Vtiger_Module_Model::getInstance($moduleName);
		$fields = $blocks = [];
		foreach ($module->getFields() as $fieldModel) {
			$block = $fieldModel->get('block');
			if (!isset($blocks[$block->id])) {
				$blockProperties = get_object_vars($block);
				$blocks[$block->id] = array_filter($blockProperties, function ($v) {
					return !\is_object($v);
				});
				$blocks[$block->id]['name'] = \App\Language::translate($block->label, $moduleName);
			}
			$fieldInfo = $fieldModel->getFieldInfo();
			$fieldInfo['id'] = $fieldModel->getId();
			$fieldInfo['isEditable'] = $fieldModel->isEditable();
			$fieldInfo['isViewable'] = $fieldModel->isViewable();
			$fieldInfo['isEditableReadOnly'] = $fieldModel->isEditableReadOnly();
			$fieldInfo['sequence'] = $fieldModel->get('sequence');
			$fieldInfo['fieldparams'] = $fieldModel->getFieldParams();
			$fieldInfo['blockId'] = $block->id;
			$fieldInfo['dbStructure'] = $fieldModel->getDBColumnType(false);
			$fieldInfo['queryOperators'] = $fieldModel->getQueryOperators();
			$fieldInfo['queryOperators'] = array_map(function ($value) use ($moduleName) {
				return \App\Language::translate($value, $moduleName);
			}, $fieldModel->getQueryOperators());
			if (isset($fieldInfo['picklistvalues']) && $fieldModel->isEmptyPicklistOptionAllowed()) {
				$fieldInfo['isEmptyPicklistOptionAllowed'] = $fieldModel->isEmptyPicklistOptionAllowed();
			}
			if ($fieldModel->isReferenceField()) {
				$fieldInfo['referenceList'] = $fieldModel->getReferenceList();
			}
			if ($fieldModel->isTreeField()) {
				$fieldInfo['treeValues'] = \App\Fields\Tree::getTreeValues((int) $fieldModel->getFieldParams(), $moduleName);
			}
			$fields[$fieldModel->getId()] = $fieldInfo;
		}
		$inventoryFields = [];
		if ($module->isInventory()) {
			$inventoryInstance = \Vtiger_Inventory_Model::getInstance($moduleName);
			$fieldsInInventory = $inventoryInstance->getFieldsByBlocks();
			if (isset($fieldsInInventory[1])) {
				foreach ($fieldsInInventory[1] as $fieldName => $fieldModel) {
					$inventoryFields[1][$fieldName] = [
						'label' => \App\Language::translate($fieldModel->get('label'), $moduleName),
						'type' => $fieldModel->getType(),
						'columnname' => $fieldModel->getColumnName(),
						'isSummary' => $fieldModel->isSummary(),
						'isVisibleInDetail' => $fieldModel->isVisibleInDetail(),
					];
				}
			}
		}
		return [
			'fields' => $fields,
			'blocks' => $blocks,
			'inventory' => $inventoryFields
		];
	}
}
