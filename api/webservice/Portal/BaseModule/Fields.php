<?php

/**
 * Portal container - Get fields file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Portal container - Get fields class.
 */
class Fields extends \Api\RestApi\BaseModule\Fields
{
	/**
	 * Get data about fields, blocks and inventory.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/Portal/{moduleName}/Fields",
	 *		description="Returns information about fields, blocks and inventory based on the selected module",
	 *		summary="Get data about fields, blocks and inventory",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(response=200, description="Fields, blocks and inventory details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Fields_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Fields_ResponseBody"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_Fields_ResponseBody",
	 *		title="Base module - Response action fields",
	 *		description="Fields, blocks and inventory details",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Fields parameters",
	 *			type="object",
	 *			required={"fields", "blocks"},
	 *			@OA\Property(
	 *				property="fields",
	 *				title="List of all available fields in the module",
	 *				type="object",
	 *				required={"name", "label", "type", "mandatory", "defaultvalue", "presence", "quickcreate", "masseditable", "header_field", "maxlengthtext", "maximumlength", "maxwidthcolumn", "tabindex", "fieldtype", "id", "uitype", "isEditable", "isViewable", "isReadOnly", "isEditableReadOnly", "sequence", "fieldparams", "blockId", "helpInfo", "dbStructure", "queryOperators"},
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
	 *						title="Field configuration available in the header",
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
	 *						title="Picklist values, available only for type of field: picklist, multipicklist, multiowner, multiReferenceValue, inventoryLimit, languages, currencyList, fileLocationType, taxes, multiListFields, mailScannerFields, country, modules, sharedOwner, categoryMultipicklist, tree",
	 *					),
	 *					@OA\Property(
	 *						property="date-format",
	 *						type="string",
	 *						title="Date format, available only for type of field: date, datetime",
	 *					),
	 *					@OA\Property(
	 *	 					property="time-format",
	 *						type="string",
	 *						title="Time format, available only for type of field: time",
	 *					),
	 *					@OA\Property(
	 *	 					property="currency_symbol",
	 *						type="string",
	 *						title="Currency symbol, available only for type of field: currency",
	 *					),
	 *					@OA\Property(
	 *						property="decimal_separator",
	 *						type="string",
	 *						title="Currency decimal separator, available only for type of field: currency",
	 *					),
	 *					@OA\Property(
	 *						property="group_separator",
	 *						type="string",
	 *						title="Currency group separator, available only for type of field: currency",
	 *					),
	 *					@OA\Property(
	 *						property="restrictedDomains",
	 *						title="Email restricted domains, available only for type of field: email",
	 *						type="object",
	 *						@OA\Property(property="yeti.com", description="List of domains reserved by email", example="yeti.com"),
	 *					),
	 *					@OA\Property(
	 *						property="limit",
	 *						type="integer",
	 *						title="Limit the amount of images, available only for type of field: multiImage, image",
	 *					),
	 *					@OA\Property(
	 *						property="formats",
	 *						title="File Format, available only for type of field: multiImage, image",
	 *						type="object",
	 *						@OA\Property(property="jpg", description="List of file data formats", example="jpg"),
	 *					),
	 *					@OA\Property(property="id", type="integer", description="Field ID", example=24862),
	 *					@OA\Property(property="uitype", type="integer", description="Field UiType", example=1),
	 *					@OA\Property(property="isEditable", description="Check if record is editable", type="boolean", example=true),
	 *					@OA\Property(property="isViewable", description="Check if record is viewable", type="boolean", example=true),
	 *					@OA\Property(property="isReadOnly", description="Check if record is read only (based on profiles)", type="boolean", example=false),
	 *					@OA\Property(property="isEditableReadOnly", description="Check if record is editable or read only (based on the field type)", type="boolean", example=false),
	 *					@OA\Property(property="sequence", description="Sequence field", type="integer", example=24862),
	 *					@OA\Property(property="fieldparams", description="Field params", type="object"),
	 *					@OA\Property(property="blockId", type="integer", description="Field block id", example=280),
	 *					@OA\Property(property="helpInfo", type="string", description="Additional field description", example="Edit,Detail"),
	 *					@OA\Property(property="dbStructure", type="object", description="Info about field structure in database"),
	 *					@OA\Property(property="queryOperators", type="object", description="Field query operators"),
	 *					@OA\Property(property="isEmptyPicklistOptionAllowed", description="Defines empty picklist element availability", type="boolean", example=false),
	 *					@OA\Property(
	 *						property="referenceList",
	 *						title="List of related modules, available only for reference field",
	 *						type="object",
	 *						@OA\AdditionalProperties(
	 *							description="Tree item",
	 *							type="string",
	 *							example="Accounts"
	 *						),
	 *					),
	 *					@OA\Property(
	 *						property="treeValues",
	 *						title="Tree items, available only for tree field",
	 *						type="object",
	 *						@OA\AdditionalProperties(
	 *							title="Tree item",
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
	 *				title="List of all available blocks in the module",
	 *				type="object",
	 *				@OA\AdditionalProperties(
	 *					title="Block details",
	 *					type="object",
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
	 *			@OA\Property(
	 *				property="inventory",
	 *				title="Inventory field group, available depending on the type of module",
	 *				type="object",
	 *				@OA\Property(
	 *					property="1",
	 *					title="Inventory field list",
	 *					type="object",
	 *					@OA\AdditionalProperties(
	 *						title="Inventory field details",
	 *						type="object",
	 *						required={"label", "type", "columnname", "isSummary", "isVisibleInDetail"},
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
		return parent::get();
	}
}
