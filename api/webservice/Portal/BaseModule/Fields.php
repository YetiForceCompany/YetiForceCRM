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
	 * Get method.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/Portal/BaseModule/Fields",
	 *		summary="Gets data about fields",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *    },
	 *		@OA\RequestBody(
	 *  			required=false,
	 *  			description="Request body does not occur",
	 *	  ),
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
	 *			name="module",
	 *			description="Module Modal",
	 *			@OA\Schema(
	 *				type="object",
	 *			),
	 *			in="path",
	 *			required=true
	 *		),
	 *    @OA\Parameter(
	 *        name="X-ENCRYPTED",
	 *        in="header",
	 *        required=true,
	 * 				@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *    ),
	 *		@OA\Response(
	 *				response=200,
	 *				description="Fields details",
	 *				@OA\JsonContent(ref="#/components/schemas/BaseModuleFieldsResponseBody"),
	 *				@OA\XmlContent(ref="#/components/schemas/BaseModuleFieldsResponseBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="text/html",
	 *         		@OA\Schema(ref="#/components/schemas/BaseModuleFieldsResponseBody")
	 *     		),
	 *		),
	 * ),
	 * @OA\Schema(
	 * 		schema="BaseModuleFieldsResponseBody",
	 * 		title="Base action fields",
	 * 		description="Base action fields response body",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="status",
	 *        	description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 * 			enum={"0", "1"},
	 *     	  	type="integer",
	 * 		),
	 *    @OA\Property(
	 *     	  property="result",
	 *     	 	description="Field parameters",
	 *    	 	type="object",
	 * 			@OA\Property(
	 * 					property="fields",
	 * 					description="Field name items",
	 * 					type="array",
	 * 					@OA\Items(items="id", description="Check if record is editable", type="integer", example="24862"),
	 *					@OA\Items(items="isEditable", description="Check if record is editable", type="boolean", example="false"),
	 *					@OA\Items(items="isViewable", description="Check if record is viewable", type="boolean", example="false"),
	 *					@OA\Items(items="isEditableReadOnly", description="Check if record is editable or read only", type="boolean", example="false"),
	 *					@OA\Items(items="sequence", description="Sequence field", type="integer", example="24862"),
	 *					@OA\Items(items="fieldparams", description="Field params", type="array"),
	 *					@OA\Items(items="dbStructure", description="Info about field structure in database", type="array"),
	 *					@OA\Items(items="isEmptyPicklistOptionAllowed", description="Defines empty picklist element availability", type="boolean", example="false"),
	 *					@OA\Items(
	 *							items="referenceList",
	 *							description="List of modules the field refernced to",
	 *							type="array",
	 *							@OA\Items(items="Accounts", description="Module name" type="string", example="Accounts"),
	 *					),
	 *					@OA\Items(
	 *							items="treeValues",
	 *							description="Tree values for jstree",
	 *							type="array",
	 *							@OA\Items(items="id", description="Number tree without prefix", type="integer", example="1"),
	 *							@OA\Items(items="tree", description="Template badge", type="string", example="T1"),
	 *							@OA\Items(items="parent", description="Template parent badge", type="string", example="T1"),
	 *							@OA\Items(items="text", description="Template name", type="string", example="Category"),
	 *					),
	 * 				),
	 * 				@OA\Property(
	 * 					property="blocks",
	 *  				description="Block data",
	 * 					type="array",
	 * 					@OA\Items(
	 * 							items="block id",
	 * 							description="Block id from the database",
	 * 							type="array",
	 * 							example="195",
	 * 							@OA\Items(items="block id", description="Block id", type="integer", example="195"),
	 * 							@OA\Items(items="tabid", description="Module id", type="integer", example="9"),
	 * 							@OA\Items(items="label", description="Block label", type="string", example="Account details"),
	 * 							@OA\Items(items="sequence", description="Block sequence", type="integer", example="1"),
	 * 							@OA\Items(items="showtitle", description="Specifies whether the title should be visible", type="integer", example="0"),
	 * 							@OA\Items(items="visible", description="Determines the visibility", type="integer", example="0"),
	 * 							@OA\Items(items="increateview", description="Determines the visibility in creat view", type="integer", example="0"),
	 * 							@OA\Items(items="ineditview", description="Determines the visibility in edit view", type="integer", example="0"),
	 * 							@OA\Items(items="indetailview", description="Determines the visibility in detail view", type="integer", example="0"),
	 * 							@OA\Items(items="display_status", description="Determines whether the block should be expanded", type="integer", example="2"),
	 * 							@OA\Items(items="iscustom", description="Determines if the block has been added by the user", type="integer", example="0"),
	 * 							@OA\Items(items="icon", description="Block icon class", type="string", example="far fa-calendar-alt"),
	 * 							@OA\Items(items="name", description="Block name translated into the user's language", type="string", example="Informacje podstawowe o firmie"),
	 * 					),
	 * 				),
	 * 				@OA\Property(
	 * 					property="inventory",
	 * 					description="Value inventory data",
	 * 					type="array",
	 * 					@OA\Items(items="label", description="Field name", type="string", example="Double"),
	 *					@OA\Items(items="isVisibleInDetail", description="Check if field is visible in detail view", type="boolean", example="false"),
	 *					@OA\Items(items="type", description="Field type", type="string", example="Double"),
	 *					@OA\Items(items="columnname", description="Column name", type="string", example="double"),
	 *					@OA\Items(items="isSummary", description="Sequence field", type="boolean", example="false"),
	 * 				),
	 *    	),
	 * ),
	 */
	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$module = \Vtiger_Module_Model::getInstance($moduleName);
		$fields = $blocks = [];
		foreach ($module->getFields() as &$field) {
			$block = $field->get('block');
			if (!isset($blocks[$block->id])) {
				$blockProperties = get_object_vars($block);
				$blocks[$block->id] = array_filter($blockProperties, function ($v) {
					return !\is_object($v);
				});
				$blocks[$block->id]['name'] = \App\Language::translate($block->label, $moduleName);
			}
			$fieldInfo = $field->getFieldInfo();
			$fieldInfo['id'] = $field->getId();
			$fieldInfo['isEditable'] = $field->isEditable();
			$fieldInfo['isViewable'] = $field->isViewable();
			$fieldInfo['isEditableReadOnly'] = $field->isEditableReadOnly();
			$fieldInfo['sequence'] = $field->get('sequence');
			$fieldInfo['fieldparams'] = $field->getFieldParams();
			$fieldInfo['blockId'] = $block->id;
			$fieldInfo['dbStructure'] = $field->getDBColumnType(false);
			if (isset($fieldInfo['picklistvalues']) && $field->isEmptyPicklistOptionAllowed()) {
				$fieldInfo['isEmptyPicklistOptionAllowed'] = $field->isEmptyPicklistOptionAllowed();
			}
			if ($field->isReferenceField()) {
				$fieldInfo['referenceList'] = $field->getReferenceList();
			}
			if ($field->isTreeField()) {
				$fieldInfo['treeValues'] = \App\Fields\Tree::getTreeValues((int) $field->getFieldParams(), $moduleName);
			}
			$fields[$field->getId()] = $fieldInfo;
		}
		$inventoryFields = [];
		if ($module->isInventory()) {
			$inventoryInstance = \Vtiger_Inventory_Model::getInstance($moduleName);
			$fieldsInInventory = $inventoryInstance->getFieldsByBlocks();
			if (isset($fieldsInInventory[1])) {
				foreach ($fieldsInInventory[1] as $fieldName => $fieldModel) {
					$inventoryFields[1][$fieldName] = [
						'label' => \App\Language::translate($fieldModel->get('label'), $moduleName),
						'isVisibleInDetail' => $fieldModel->isVisibleInDetail(),
						'type' => $fieldModel->getType(),
						'columnname' => $fieldModel->getColumnName(),
						'isSummary' => $fieldModel->isSummary()
					];
				}
			}
		}
		var_dump($blocks);
		return [
			'fields' => $fields,
			'blocks' => $blocks,
			'inventory' => $inventoryFields
		];
	}
}
