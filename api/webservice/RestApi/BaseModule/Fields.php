<?php

/**
 * RestApi container - Get fields file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\RestApi\BaseModule;

use OpenApi\Annotations as OA;

/**
 * RestApi container - Get fields class.
 */
class Fields extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/**
	 * Get data about fields, blocks and inventory.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/{moduleName}/Fields",
	 *		summary="Get data about fields, blocks and inventory",
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
	 *				@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="Request body does not occur",
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Fields, blocks and inventory details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Fields_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Fields_ResponseBody"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_Fields_ResponseBody",
	 *		title="Base module - Response action fields",
	 *		description="Fields, blocks and inventory details",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 * 			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 * 			enum={0, 1},
	 *     	  	type="integer",
	 * 			example=1
	 * 		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Fields parameters",
	 *			type="object",
	 *			@OA\Property(
	 *				property="fields",
	 *				description="List of all available fields in the module",
	 *				type="object",
	 *				@OA\AdditionalProperties(
	 *					@OA\Property(property="name", type="string", description="Field name", example="subject"),
	 *					@OA\Property(property="label", type="string", description="Field label translated into the user's language", example="Subject"),
	 *					@OA\Property(property="type", type="string", description="Field type", example="string"),
	 *					@OA\Property(property="mandatory", type="boolean", description="Check if field is mandatory", example=true),
	 *					@OA\Property(property="defaultvalue", type="string", description="Default field value", example=""),
	 *					@OA\Property(property="presence", type="boolean", description="Check if field is active", example=true),
	 *					@OA\Property(property="quickcreate", type="boolean", description="Check if field is active", example=true),
	 *					@OA\Property(property="masseditable", type="boolean", description="Check if field is quick create enabled", example=true),
	 *					@OA\Property(
	 *						property="header_field",
	 *						type="object",
	 *						description="Field configuration available in the header",
	 *						@OA\Property(property="type", type="string", description="Type", example="value"),
	 *						@OA\Property(property="class", type="string", description="Gui class", example="badge-info"),
	 *					),
	 *					@OA\Property(property="maxlengthtext", type="integer", description="Max length text", example=0),
	 *					@OA\Property(property="maximumlength", type="string", description="Maximum field range", example="-2147483648,2147483647"),
	 *					@OA\Property(property="maxwidthcolumn", type="integer", description="Max width column", example=0),
	 *					@OA\Property(property="tabindex", type="integer", description="Field tab index", example=0),
	 *					@OA\Property(property="fieldtype", type="string", description="Field short data type", example="V"),
	 *					@OA\Property(
	 *						property="picklistvalues",
	 *						type="object",
	 *						description="Picklist values, available only for type of field: picklist, multipicklist, multiowner, multiReferenceValue, inventoryLimit, languages, currencyList, fileLocationType, taxes, multiListFields, mailScannerFields, country, modules, sharedOwner, categoryMultipicklist, tree",
	 *					),
	 *					@OA\Property(
	 *						property="date-format",
	 *						type="string",
	 *						description="Date format, available only for type of field: date, datetime",
	 *					),
	 *					@OA\Property(
	 *	 					property="time-format",
	 *						type="string",
	 *						description="Time format, available only for type of field: time",
	 *					),
	 *					@OA\Property(
	 *	 					property="currency_symbol",
	 *						type="string",
	 *						description="Currency symbol, available only for type of field: currency",
	 *					),
	 *					@OA\Property(
	 *						property="decimal_separator",
	 *						type="string",
	 *						description="Currency decimal separator, available only for type of field: currency",
	 *					),
	 *					@OA\Property(
	 *						property="group_separator",
	 *						type="string",
	 *						description="Currency group separator, available only for type of field: currency",
	 *					),
	 *					@OA\Property(
	 *						property="restrictedDomains",
	 *						description="Email restricted domains, available only for type of field: email",
	 *						type="object",
	 *						@OA\Property(property="yeti.com", description="List of domains reserved by email", example="yeti.com"),
	 *					),
	 *					@OA\Property(
	 *						property="limit",
	 *						type="integer",
	 *						description="Limit the amount of images, available only for type of field: multiImage, image",
	 *					),
	 *					@OA\Property(
	 *						property="formats",
	 *						description="File Format, available only for type of field: multiImage, image",
	 *						type="object",
	 *						@OA\Property(property="jpg", description="List of file data formats", example="jpg"),
	 *					),
	 *					@OA\Property(property="id", type="integer", description="Field ID", example=24862),
	 *					@OA\Property(property="isEditable", description="Check if record is editable", type="boolean", example=true),
	 *					@OA\Property(property="isViewable", description="Check if record is viewable", type="boolean", example=true),
	 *					@OA\Property(property="isEditableReadOnly", description="Check if record is editable or read only", type="boolean", example=false),
	 *					@OA\Property(property="sequence", description="Sequence field", type="integer", example=24862),
	 *					@OA\Property(property="fieldparams", description="Field params", type="object"),
	 *					@OA\Property(property="blockId", type="integer", description="Field block id", example=280),
	 *					@OA\Property(property="helpInfo", type="string", description="Additional field description", example="Edit,Detail"),
	 *					@OA\Property(
	 *	 					property="dbStructure",
	 *						type="object",
	 *						description="Info about field structure in database",
	 *					),
	 *					@OA\Property(
	 *	 					property="queryOperators",
	 *						type="object",
	 *						description="Field query operators",
	 *					),
	 *					@OA\Property(property="isEmptyPicklistOptionAllowed", description="Defines empty picklist element availability", type="boolean", example=false),
	 *					@OA\Property(
	 *						property="referenceList",
	 *						description="List of related modules, available only for reference field",
	 *						type="object",
	 *						@OA\AdditionalProperties(
	 *							description="Tree item",
	 *							type="string",
	 *							example="Accounts"
	 *						),
	 *					),
	 *					@OA\Property(
	 *						property="treeValues",
	 *						description="Tree items, available only for tree field",
	 *						type="object",
	 *						@OA\AdditionalProperties(
	 *							description="Tree item",
	 *							type="object",
	 *							@OA\Property(property="id", description="Number tree without prefix", type="integer", example=1),
	 *							@OA\Property(property="tree", description="Tree id", type="string", example="T10"),
	 *							@OA\Property(property="parent", description="Parent tree id", type="string", example="T1"),
	 *							@OA\Property(property="text", description="Tree value", type="string", example="Tree value"),
	 *						),
	 *					),
	 *				),
	 *			),
	 *			@OA\Property(
	 *				property="blocks",
	 *				description="List of all available blocks in the module",
	 *				type="object",
	 *				@OA\AdditionalProperties(
	 *					description="Block details",
	 *					type="object",
	 *					@OA\Property(property="id", description="Block id", type="integer", example=195),
	 *					@OA\Property(property="tabid", description="Module id", type="integer", example=9),
	 *					@OA\Property(property="label", description="Block label", type="string", example="Account details"),
	 *					@OA\Property(property="sequence", description="Block sequence", type="integer", example=1),
	 *					@OA\Property(property="showtitle", description="Specifies whether the title should be visible", type="integer", example=0),
	 *					@OA\Property(property="visible", description="Determines the visibility", type="integer", example=0),
	 *					@OA\Property(property="increateview", description="Determines the visibility in creat view", type="integer", example=0),
	 *					@OA\Property(property="ineditview", description="Determines the visibility in edit view", type="integer", example=0),
	 *					@OA\Property(property="indetailview", description="Determines the visibility in detail view", type="integer", example=0),
	 *					@OA\Property(property="display_status", description="Determines whether the block should be expanded", type="integer", example=2),
	 *					@OA\Property(property="iscustom", description="Determines if the block has been added by the user", type="integer", example=0),
	 *					@OA\Property(property="icon", description="Block icon class", type="string",  example="far fa-calendar-alt"),
	 *					@OA\Property(property="name", description="Block name translated into the user's language", type="string", example="Informacje podstawowe o firmie"),
	 *				),
	 *			),
	 *			@OA\Property(
	 *				property="inventory",
	 *				description="Inventory field group, available depending on the type of module",
	 *				type="object",
	 *				@OA\Property(
	 *					property="1",
	 *					description="Inventory field list",
	 *					type="object",
	 *					@OA\AdditionalProperties(
	 *						description="Inventory field details",
	 *						type="object",
	 *						@OA\Property(property="label", description="Field label translated into the user's language", type="string", example="Unit price"),
	 *						@OA\Property(property="type", description="Field type", type="string", example="UnitPrice"),
	 *						@OA\Property(property="columnname", description="Field column name in db", type="string", example="price"),
	 *						@OA\Property(property="isSummary", description="Is the field contains summary", type="boolean", example=false),
	 *						@OA\Property(property="isVisibleInDetail", description="Check if field is visible in detail view", type="boolean", example=true),
	 *					),
	 *				),
	 *			),
	 *		),
	 *	),
	 * )
	 */
	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$module = \Vtiger_Module_Model::getInstance($moduleName);
		$return = $inventoryFields = $fields = $blocks = [];
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
			$fieldInfo['helpInfo'] = \App\Language::getTranslateHelpInfo($fieldModel, 'all');
			$fieldInfo['dbStructure'] = $fieldModel->getDBColumnType(false);
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
		$return['fields'] = $fields;
		$return['blocks'] = $blocks;
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
			$return['inventory'] = $inventoryFields;
		}
		return $return;
	}
}
