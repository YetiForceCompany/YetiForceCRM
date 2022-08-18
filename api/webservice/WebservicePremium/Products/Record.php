<?php
/**
 * Webservice premium container - Loads the details of a product file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebservicePremium\Products;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Loads the details of a product class.
 */
class Record extends \Api\WebservicePremium\BaseModule\Record
{
	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-parent-id', 'x-unit-price', 'x-unit-gross', 'x-product-bundles'];

	/** @var bool Is user permissions. */
	private $isUserPermissions;

	/** @var float|null Unit price. */
	private $unitPrice;

	/**
	 * {@inheritdoc}
	 *
	 *	@OA\Get(
	 *		path="/webservice/WebservicePremium/Products/Record/{recordId}",
	 *		summary="Gets the details of a product",
	 *		description="Data for the product",
	 *		tags={"Products"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="x-parent-id", in="header", @OA\Schema(type="integer"), description="Parent record id", required=false, example=5),
	 *		@OA\Parameter(
	 *			name="x-unit-price",
	 *			description="Get additional unit price",
	 *			@OA\Schema(type="integer", enum={0, 1}),
	 *			in="header",
	 *			example=1,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-unit-gross",
	 *			description="Get additional unit gross",
	 *			@OA\Schema(type="integer", enum={0, 1}),
	 *			in="header",
	 *			example=1,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-product-bundles",
	 *			description="Get additional product bundles",
	 *			@OA\Schema(type="integer", enum={0, 1}),
	 *			in="header",
	 *			example=1,
	 *			required=false
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Gets data for the record",
	 *			@OA\JsonContent(ref="#/components/schemas/Products_Get_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Products_Get_Record_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="`No permissions to remove record` OR `No permissions to view record` OR `No permissions to edit record`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="`No record id` OR `Record doesn't exist`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="Products_Get_Record_Response",
	 *		title="Base module - Response body for Record",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Record data",
	 *			type="object",
	 *			required={"name", "id", "fields", "data"},
	 *			@OA\Property(property="name", description="Record name", type="string", example="Driving school"),
	 *			@OA\Property(property="id", description="Record Id", type="integer", example=152),
	 *			@OA\Property(property="fields", type="object", title="System field names and field labels", example={"field_name_1" : "Field label 1", "field_name_2" : "Field label 2", "assigned_user_id" : "Assigned user", "createdtime" : "Created time"},
	 * 				@OA\AdditionalProperties(type="string", description="Field label"),
	 *			),
	 *			@OA\Property(
	 *				property="data",
	 *				description="Record data",
	 *				type="object",
	 *				ref="#/components/schemas/Record_Display_Details",
	 *			),
	 *			@OA\Property(
	 *				property="privileges",
	 *				title="Parameters determining checking of editing rights and moving to the trash",
	 * 				type="object",
	 * 				required={"isEditable", "moveToTrash"},
	 *				@OA\Property(property="isEditable", description="Check if record is editable", type="boolean", example=true),
	 *				@OA\Property(property="moveToTrash", description="Permission to delete", type="boolean", example=false),
	 *			),
	 *			@OA\Property(property="rawData", type="object", description="Raw record data", ref="#/components/schemas/Record_Raw_Details"),
	 *			@OA\Property(
	 * 				property="productBundles",
	 *				description="Product bundles",
	 *				type="object",
	 *				@OA\AdditionalProperties(
	 *					description="Product",
	 *					type="object",
	 * 					@OA\Property(property="data", type="object", description="Record data", ref="#/components/schemas/Record_Raw_Details"),
	 * 					@OA\Property(property="rawData", type="object", description="Raw record data", ref="#/components/schemas/Record_Raw_Details"),
	 * 				),
	 *			),
	 *			@OA\Property(
	 * 				property="ext",
	 *				description="Product bundles",
	 *				type="object",
	 *				@OA\Property(property="unit_price", description="Unit price", type="integer", example=44),
	 *				@OA\Property(property="unit_gross", description="Unit gross", type="integer", example=55),
	 *				@OA\Property(property="qtyinstock", description="Qty In Stock", type="integer", example=66),
	 *			),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		$this->isUserPermissions = \Api\WebservicePremium\Privilege::USER_PERMISSIONS === $this->getPermissionType();
		$response = parent::get();
		$response['ext'] = $response['productBundles'] = [];
		if (1 === $this->controller->request->getHeader('x-unit-price')) {
			$response['ext']['unit_price'] = $this->getUnitPrice($response);
		}
		if (1 === $this->controller->request->getHeader('x-unit-gross')) {
			$response['ext']['unit_gross'] = $this->getUnitGross($response);
		}
		if ($storage = $this->getUserStorageId()) {
			$stock = (new \App\Db\Query())->select(['qtyinstock'])->from('u_#__istorages_products')->where(['crmid' => $storage, 'relcrmid' => $response['id']])->scalar();
			$response['ext']['qtyinstock'] = (int) ($stock ?? 0);
		}
		if (1 === $this->controller->request->getHeader('x-product-bundles')) {
			$response['productBundles'] = $this->getProductBundles();
		}

		return $response;
	}

	/**
	 * Get unit gross.
	 *
	 * @param array $response
	 *
	 * @return float
	 */
	private function getUnitGross(array $response): float
	{
		$availableTaxes = [];
		if ($this->isUserPermissions) {
			$availableTaxes = 'LBL_GROUP_TAX';
			$regionalTaxes = '';
		} else {
			$parentRecordModel = \Vtiger_Record_Model::getInstanceById($this->getParentCrmId(), 'Accounts');
			$availableTaxes = $parentRecordModel->get('accounts_available_taxes');
			$regionalTaxes = $parentRecordModel->get('taxes');
		}
		$taxParam = \Api\WebservicePremium\Record::getTaxParam($availableTaxes, $response['rawData']['taxes'], $regionalTaxes);
		$taxConfig = \Vtiger_Inventory_Model::getTaxesConfig();
		$unitPrice = $this->getUnitPrice($response);
		return $unitPrice + (new \Vtiger_Tax_InventoryField())->getTaxValue($taxParam, $unitPrice, (int) $taxConfig['aggregation']);
	}

	/**
	 * Get unit price.
	 *
	 * @param array $response
	 *
	 * @return float
	 */
	private function getUnitPrice(array $response): float
	{
		if (null === $this->unitPrice) {
			if ($this->isUserPermissions) {
				$unitPrice = (new \Vtiger_MultiCurrency_UIType())->getValueForCurrency($response['rawData']['unit_price'] ?? [], \App\Fields\Currency::getDefault()['id']);
			} else {
				$unitPrice = \Api\WebservicePremium\Record::getPriceFromPricebook($this->getParentCrmId(), $this->controller->request->getInteger('record'));
				if (null === $unitPrice) {
					$unitPrice = (new \Vtiger_MultiCurrency_UIType())->getValueForCurrency($response['rawData']['unit_price'] ?? [], \App\Fields\Currency::getDefault()['id']);
				}
			}
			$this->unitPrice = $unitPrice;
		}
		return $this->unitPrice;
	}

	/**
	 * Get product bundles.
	 *
	 * @return array
	 */
	private function getProductBundles(): array
	{
		$products = [];
		$productRelationModel = \Vtiger_Relation_Model::getInstance($this->recordModel->getModule(), $this->recordModel->getModule());
		$productRelationModel->set('parentRecord', $this->recordModel);
		$queryGenerator = $productRelationModel->getQuery();
		$queryGenerator->setField('ean')->setField('taxes')->setField('imagename');
		if ($this->isUserPermissions) {
			$availableTaxes = 'LBL_GROUP_TAX';
			$regionalTaxes = '';
		} else {
			$parentRecordModel = \Vtiger_Record_Model::getInstanceById($this->getParentCrmId(), 'Accounts');
			$availableTaxes = $parentRecordModel->get('accounts_available_taxes');
			$regionalTaxes = $parentRecordModel->get('taxes');
			$pricebookId = $parentRecordModel->get('pricebook_id');
			if (!empty($pricebookId)) {
				$queryGenerator->setCustomColumn('vtiger_pricebookproductrel.listprice');
				$queryGenerator->addJoin([
					'LEFT JOIN',
					'vtiger_pricebookproductrel',
					"vtiger_pricebookproductrel.pricebookid={$pricebookId} AND vtiger_pricebookproductrel.productid = vtiger_products.productid", ]
				);
			}
		}
		$storage = $this->getUserStorageId();
		if ($storage) {
			$queryGenerator->setCustomColumn('u_#__istorages_products.qtyinstock as storage_qtyinstock');
			$queryGenerator->addJoin([
				'LEFT JOIN',
				'u_#__istorages_products',
				"u_#__istorages_products.crmid={$storage} AND u_#__istorages_products.relcrmid = vtiger_products.productid", ]
			);
		}
		$fieldsModel = $queryGenerator->getListViewFields();
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$taxConfigAggregation = (int) \Vtiger_Inventory_Model::getTaxesConfig()['aggregation'];
		foreach ($dataReader as $row) {
			$row['qtyinstock'] = (int) ($row['storage_qtyinstock'] ?? 0);
			if (!$this->isUserPermissions && !empty($row['listprice'])) {
				$unitPrice = $row['unit_price'] = $row['listprice'];
			} else {
				$unitPrice = $row['unit_price'] = (new \Vtiger_MultiCurrency_UIType())->getValueForCurrency($row['unit_price'], \App\Fields\Currency::getDefault()['id']);
			}
			$taxParam = \Api\WebservicePremium\Record::getTaxParam($availableTaxes, $row['taxes'], $regionalTaxes);
			$row['unit_gross'] = $unitPrice + (new \Vtiger_Tax_InventoryField())->getTaxValue($taxParam, $unitPrice, $taxConfigAggregation);
			$products[$row['id']] = [
				'data' => $this->getRecordFromRow($row, $fieldsModel),
				'rawData' => $row,
			];
		}
		$dataReader->close();
		return $products;
	}

	/**
	 * Get record from row.
	 *
	 * @param array                 $row
	 * @param \Vtiger_Field_Model[] $fieldsModel
	 *
	 * @return array
	 */
	private function getRecordFromRow(array $row, array $fieldsModel): array
	{
		$record = [
			'id' => $row['id'],
			'recordLabel' => \App\Record::getLabel($row['id']),
		];
		$recordModel = \Vtiger_Record_Model::getCleanInstance($this->controller->request->getModule());
		foreach ($fieldsModel as $fieldName => $fieldModel) {
			if (isset($row[$fieldName])) {
				$recordModel->set($fieldName, $row[$fieldName]);
				$record[$fieldName] = $recordModel->getDisplayValue($fieldName, $row['id'], true);
			}
		}
		$record['unit_price'] = \CurrencyField::convertToUserFormatSymbol($row['unit_price']);
		$record['unit_gross'] = \CurrencyField::convertToUserFormatSymbol($row['unit_gross']);
		return $record;
	}
}
