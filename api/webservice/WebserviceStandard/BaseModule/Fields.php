<?php

/**
 * Webservice standard container - Get fields file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebserviceStandard\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container - Get fields class.
 */
class Fields extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-response-params'];

	/** @var string[] Response params, options allowed: ["inventory","blocks","privileges","dbStructure","queryOperators"] */
	protected $responseParams = [];

	/**
	 * Get data about fields, blocks and inventory.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebserviceStandard/{moduleName}/Fields",
	 *		description="Returns information about fields, blocks and inventory based on the selected module",
	 *		summary="Get data about fields, blocks and inventory",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Parameter(name="x-response-params", in="header", description="The header contains information about additional data to be returned in the response [Json array]", required=false,
	 *			@OA\JsonContent(type="array", @OA\Items(type="string", enum={"inventory", "blocks", "privileges", "dbStructure", "queryOperators"})),
	 *		),
	 *		@OA\Response(response=200, description="Fields, blocks and inventory details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_Fields_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_Fields_Response"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_Get_Fields_Response",
	 *		title="Base module - Response action fields",
	 *		description="Fields, blocks and inventory details",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="Fields parameters", required={"fields", "blocks"},
	 *			@OA\Property(property="fields", type="object", title="List of all available fields in the module",
	 *				required={"name", "label", "type", "mandatory", "defaultvalue", "presence", "quickcreate", "masseditable", "header_field", "maxlengthtext", "maximumlength", "maxwidthcolumn", "tabindex", "fieldtype", "id", "uitype", "sequence", "fieldparams", "blockId", "helpInfo"},
	 *				@OA\AdditionalProperties(
	 *					@OA\Property(property="name", type="string", description="Field name", example="subject"),
	 *					@OA\Property(property="label", type="string", description="Field label translated into the user's language", example="Subject"),
	 *					@OA\Property(property="type", type="string", description="Field type", example="string"),
	 *					@OA\Property(property="mandatory", type="boolean", description="Check if field is mandatory", example=true),
	 *					@OA\Property(property="defaultvalue", type="string", description="Default field value", example=""),
	 *					@OA\Property(property="presence", type="boolean", description="Check if field is active", example=true),
	 *					@OA\Property(property="quickcreate", type="boolean", description="Check if field is active", example=true),
	 *					@OA\Property(property="masseditable", type="boolean", description="Check if field is quick create enabled", example=true),
	 *					@OA\Property(property="header_field", type="object", title="Field configuration available in the header",
	 *						@OA\Property(property="type", type="string", description="Type", example="value"),
	 *						@OA\Property(property="class", type="string", description="Gui class", example="badge-info"),
	 *					),
	 *					@OA\Property(property="maxlengthtext", type="integer", description="Max length text", example=0),
	 *					@OA\Property(property="maximumlength", type="string", description="Maximum field range", example="-2147483648,2147483647"),
	 *					@OA\Property(property="maxwidthcolumn", type="integer", description="Max width column", example=0),
	 *					@OA\Property(property="tabindex", type="integer", description="Field tab index", example=0),
	 *					@OA\Property(property="fieldtype", type="string", description="Field short data type", example="V"),
	 *					@OA\Property(property="picklistvalues", type="object", title="Picklist values, available only for type of field: picklist, multipicklist, multiowner, multiReferenceValue, inventoryLimit, languages, currencyList, fileLocationType, taxes, multiListFields, mailScannerFields, country, modules, sharedOwner, categoryMultipicklist, tree",
	 *					),
	 *					@OA\Property(property="date-format", type="string", title="Date format, available only for type of field: date, datetime"),
	 *					@OA\Property(property="time-format", type="string", title="Time format, available only for type of field: time"),
	 *					@OA\Property(property="currency_symbol", type="string", title="Currency symbol, available only for type of field: currency"),
	 *					@OA\Property(property="decimal_separator", type="string", title="Currency decimal separator, available only for type of field: currency"),
	 *					@OA\Property(property="group_separator", type="string", title="Currency group separator, available only for type of field: currency"),
	 *					@OA\Property(property="restrictedDomains", type="object", title="Email restricted domains, available only for type of field: email",
	 *						@OA\Property(property="yeti.com", description="List of domains reserved by email", example="yeti.com"),
	 *					),
	 *					@OA\Property(property="limit", type="integer", title="Limit the amount of images, available only for type of field: multiImage, image"),
	 *					@OA\Property(property="formats", type="object", title="File Format, available only for type of field: multiImage, image",
	 *						@OA\Property(property="jpg", description="List of file data formats", example="jpg"),
	 *					),
	 *					@OA\Property(property="id", type="integer", description="Field ID", example=24862),
	 *					@OA\Property(property="uitype", type="integer", description="Field UiType", example=1),
	 *					@OA\Property(property="isViewable", description="Check if the field is viewable, depends on header `x-response-params`", type="boolean", example=true),
	 *					@OA\Property(property="isReadOnly", description="Check if the field is read only (based on profiles), depends on header `x-response-params`", type="boolean", example=false),
	 *					@OA\Property(property="isCreatable", description="Check if the field is creatable, depends on header `x-response-params`", type="boolean", example=true),
	 *					@OA\Property(property="isEditable", description="Check if the field is editable, depends on header `x-response-params`", type="boolean", example=true),
	 *					@OA\Property(property="isEditableReadOnly", description="Check if the field is editable or read only (based on the field type), depends on header `x-response-params`", type="boolean", example=false),
	 *					@OA\Property(property="isEditableHidden", description="Check if the field is hidden in the edit (based on the field type), depends on header `x-response-params`", type="boolean", example=false),
	 *					@OA\Property(property="sequence", description="Sequence field", type="integer", example=24862),
	 *					@OA\Property(property="fieldparams", description="Field params", type="object"),
	 *					@OA\Property(property="blockId", type="integer", description="Field block id", example=280),
	 *					@OA\Property(property="helpInfo", type="string", description="Additional field description", example="Edit,Detail"),
	 *					@OA\Property(property="dbStructure", type="object", title="Info about field structure in database, depends on header `x-response-params`",
	 *						@OA\Property(property="name", type="string", description="Name of this column (without quotes).", example="parent_id"),
	 *						@OA\Property(property="allowNull", type="boolean", description="Whether this column can be null.", example=true),
	 *						@OA\Property(property="type", type="string", description="Abstract type of this column.", example="integer"),
	 *						@OA\Property(property="phpType", type="string", description="The PHP type of this column.", example="integer"),
	 *						@OA\Property(property="dbType", type="string", description="The DB type of this column.", example="int(10)"),
	 *						@OA\Property(property="defaultValue", type="string", description="Default value of this column", example="10"),
	 *						@OA\Property(property="enumValues", type="string", description="Enumerable values.", example=""),
	 *						@OA\Property(property="size", type="integer", description="Display size of the column.", example=10),
	 *						@OA\Property(property="precision", type="integer", description="Precision of the column data, if it is numeric.", example=10),
	 *						@OA\Property(property="scale", type="integer", description="Scale of the column data, if it is numeric.", example=0),
	 *						@OA\Property(property="isPrimaryKey", type="boolean", description="Whether this column is a primary key", example=false),
	 *						@OA\Property(property="autoIncrement", type="boolean", description="Whether this column is auto-incremental", example=false),
	 *						@OA\Property(property="unsigned", type="boolean", description="Whether this column is unsigned.", example=false),
	 *						@OA\Property(property="comment", type="string", description="Comment of this column.", example=""),
	 *					),
	 *					@OA\Property(property="queryOperators", type="object", description="Field query operators, depends on header `x-response-params`"),
	 *					@OA\Property(property="isEmptyPicklistOptionAllowed", description="Defines empty picklist element availability", type="boolean", example=false),
	 *					@OA\Property(property="referenceList", type="object", title="List of related modules, available only for reference field",
	 *						@OA\AdditionalProperties(description="Tree item", type="string", example="Accounts"),
	 *					),
	 *					@OA\Property(property="treeValues", type="object", title="Tree items, available only for tree field",
	 *						@OA\AdditionalProperties(type="object", title="Tree item",
	 *							@OA\Property(property="id", description="Number tree without prefix", type="integer", example=1),
	 *							@OA\Property(property="tree", description="Tree id", type="string", example="T10"),
	 *							@OA\Property(property="parent", description="Parent tree id", type="string", example="T1"),
	 *							@OA\Property(property="text", description="Tree value", type="string", example="Tree value"),
	 *						),
	 *					),
	 *					@OA\Property(property="defaultEditValue", type="object", title="Default field value in editable format",
	 *						@OA\Property(property="value", type="string", description="Value in editable format", example="Some value"),
	 *						@OA\Property(property="raw", type="string", description="Raw value", example="T10"),
	 *					),
	 *				),
	 *			),
	 *			@OA\Property(property="blocks", type="object", title="List of all available blocks in the module, depends on header `x-response-params`",
	 *				@OA\AdditionalProperties(type="object", title="Block details",
	 *					required={"id", "tabid", "label", "sequence", "showtitle", "visible", "increateview", "ineditview", "indetailview", "display_status", "iscustom", "icon", "name"},
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
	 *			@OA\Property(property="inventory", type="object", title="Inventory field group, available depending on the type of module, depends on header `x-response-params`",
	 *				@OA\Property(property="1", type="object", title="Inventory field list",
	 *					@OA\AdditionalProperties(type="object", title="Inventory field details", required={"label", "type", "columnname", "isSummary", "isVisibleInDetail"},
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
	public function get(): array
	{
		$this->loadResponseParams();
		$moduleName = $this->controller->request->get('module');
		$module = \Vtiger_Module_Model::getInstance($moduleName);
		$return = $inventoryFields = $fields = $blocks = [];
		$returnBlocks = isset($this->responseParams['blocks']);
		$returnPrivileges = isset($this->responseParams['privileges']);
		$returnDbStructure = isset($this->responseParams['dbStructure']);
		$returnQueryOperators = isset($this->responseParams['queryOperators']);
		foreach ($module->getFields() as $fieldModel) {
			if (!$fieldModel->isActiveField()) {
				continue;
			}
			\Api\WebserviceStandard\Fields::loadWebserviceByField($fieldModel, $this);
			$block = $fieldModel->get('block');
			if ($returnBlocks && !isset($blocks[$block->id])) {
				$blockProperties = get_object_vars($block);
				$blocks[$block->id] = array_filter($blockProperties, fn ($v) => !\is_object($v));
				$blocks[$block->id]['name'] = \App\Language::translate($block->label, $moduleName);
			}
			$fieldInfo = $fieldModel->getFieldInfo();
			$fieldInfo['id'] = $fieldModel->getId();
			$fieldInfo['uitype'] = $fieldModel->getUIType();
			if ($returnPrivileges) {
				$isEditable = $fieldModel->isEditable();
				$fieldInfo['isViewable'] = $fieldModel->isViewable();
				$fieldInfo['isReadOnly'] = $fieldModel->isReadOnly();
				$fieldInfo['isCreatable'] = $isEditable || 4 === $fieldModel->get('displaytype');
				$fieldInfo['isEditable'] = $isEditable;
				$fieldInfo['isEditableReadOnly'] = $fieldModel->isEditableReadOnly();
				$fieldInfo['isEditableHidden'] = 9 === $fieldModel->get('displaytype');
			}
			$fieldInfo['sequence'] = $fieldModel->get('sequence');
			$fieldInfo['fieldparams'] = $fieldModel->getFieldParams();
			$fieldInfo['blockId'] = $block->id;
			$fieldInfo['helpInfo'] = \App\Language::getTranslateHelpInfo($fieldModel, 'all');
			if ($returnDbStructure) {
				$fieldInfo['dbStructure'] = $fieldModel->getDBColumnType(false);
			}
			if ($returnQueryOperators) {
				$fieldInfo['queryOperators'] = array_map(fn ($value) => \App\Language::translate($value, $moduleName), $fieldModel->getQueryOperators());
			}
			if (isset($fieldInfo['picklistvalues']) && $fieldModel->isEmptyPicklistOptionAllowed()) {
				$fieldInfo['isEmptyPicklistOptionAllowed'] = $fieldModel->isEmptyPicklistOptionAllowed();
			}
			if ($fieldModel->isReferenceField()) {
				$fieldInfo['referenceList'] = $fieldModel->getReferenceList();
			}
			if ($fieldModel->isTreeField()) {
				$fieldInfo['treeValues'] = \App\Fields\Tree::getTreeValues((int) $fieldModel->getFieldParams(), $moduleName);
			}
			if ($fieldModel->get('defaultvalue')) {
				$fieldInfo['defaultEditValue'] = $fieldModel->getUITypeModel()->getApiEditValue($fieldModel->getDefaultFieldValue());
			}
			$fields[$fieldModel->getName()] = $fieldInfo;
		}
		$return['fields'] = $fields;
		if ($returnBlocks) {
			$return['blocks'] = $blocks;
		}
		if (isset($this->responseParams['inventory']) && $module->isInventory()) {
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

	/**
	 * Load response params.
	 *
	 * @return void
	 */
	protected function loadResponseParams(): void
	{
		$responseParams = $this->controller->request->getHeader('x-response-params');
		if (empty($responseParams) || '[]' === $responseParams) {
			return;
		}
		if (!\App\Json::isJson($responseParams)) {
			throw new \Api\Core\Exception('Incorrect json syntax: x-response-params', 400);
		}
		$this->responseParams = array_flip(\App\Json::decode($responseParams));
	}
}
