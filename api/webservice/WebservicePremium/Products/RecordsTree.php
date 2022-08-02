<?php
/**
 * Webservice premium container - A store functionality - gets a list of products for orders file.
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
 * Webservice premium container - A store functionality - gets a list of products for orders class.
 */
class RecordsTree extends \Api\WebservicePremium\BaseModule\RecordsList
{
	/** @var int Permission type. */
	private $permissionType;

	/** @var bool Is user permissions. */
	private $isUserPermissions;

	/** @var \Vtiger_Record_Model Parent record model. */
	private $parentRecordModel;

	/**
	 * Get method - A store functionality - gets a list of products for orders.
	 *
	 *	@OA\Get(
	 *		path="/webservice/WebservicePremium/Products/RecordsTree",
	 *		summary="A store functionality - gets a list of products for orders",
	 *		description="Tree list of records",
	 *		tags={"Products"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 1000", required=false, example=1000),
	 *		@OA\Parameter(name="x-row-offset", in="header", @OA\Schema(type="integer"), description="Offset, default: 0", required=false, example=0),
	 *		@OA\Parameter(name="x-order-by", in="header", description="Set the sorted results by columns [Json format]", required=false,
	 * 			@OA\JsonContent(type="object", title="Sort conditions", description="Multiple or one condition for a query generator",
	 * 				example={"field_name_1" : "ASC", "field_name_2" : "DESC"},
	 * 				@OA\AdditionalProperties(type="string", title="Sort Direction", enum={"ASC", "DESC"}),
	 * 			),
	 *		),
	 *		@OA\Parameter(name="x-fields", in="header", description="JSON array in the list of fields to be returned in response", required=false,
	 *			@OA\JsonContent(type="array", example={"field_name_1", "field_name_2"}, @OA\Items(type="string")),
	 *		),
	 *		@OA\Parameter(name="x-condition", in="header", description="Conditions [Json format]", required=false,
	 *			@OA\JsonContent(ref="#/components/schemas/Conditions-Mix-For-Query-Generator"),
	 *		),
	 *		@OA\Parameter(name="x-parent-id", in="header", @OA\Schema(type="integer"), description="Parent record id", required=false, example=5),
	 *		@OA\Response(response=200, description="List of entries",
	 *			@OA\JsonContent(ref="#/components/schemas/Products_Get_RecordsList_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Products_Get_RecordsList_Response"),
	 *		),
	 *		@OA\Response(response=400, description="Incorrect json syntax: x-fields",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=401, description="No sent token, Invalid token, Token has expired",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=403, description="No permissions for module",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=405, description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="Products_Get_RecordsList_Response",
	 *		title="Products - Response action record list",
	 *		description="Module action record list response body",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="List of records",
	 *			type="object",
	 *			@OA\Property(
	 *				property="headers",
	 *				description="Column names",
	 *				type="object",
	 *				@OA\AdditionalProperties,
	 *			),
	 *			@OA\Property(
	 *				property="records",
	 *				description="Records display details",
	 *				type="object",
	 *				@OA\AdditionalProperties(type="object", ref="#/components/schemas/Record_Display_Details"),
	 *			),
	 *			@OA\Property(
	 *				property="rawData",
	 *				description="Records raw details",
	 *				type="object",
	 *				@OA\AdditionalProperties(type="object", ref="#/components/schemas/Record_Raw_Details"),
	 *			),
	 * 			@OA\Property(property="count", type="integer", example=54),
	 * 			@OA\Property(property="isMorePages", type="boolean", example=true),
	 * 		),
	 *	),
	 */
	public function createQuery(): void
	{
		$this->isUserPermissions = \Api\WebservicePremium\Privilege::USER_PERMISSIONS === $this->getPermissionType();
		if ($this->isUserPermissions) {
			parent::createQuery();
		} else {
			if ($parent = $this->getParentCrmId()) {
				$this->parentRecordModel = \Vtiger_Record_Model::getInstanceById($parent, 'Accounts');
				$pricebookId = $this->parentRecordModel->get('pricebook_id');
				if (empty($pricebookId)) {
					parent::createQuery();
				} else {
					parent::createQuery();
					$this->queryGenerator->setCustomColumn('vtiger_pricebookproductrel.listprice');
					$this->queryGenerator->addJoin([
						'LEFT JOIN',
						'vtiger_pricebookproductrel',
						"vtiger_pricebookproductrel.pricebookid={$pricebookId} AND vtiger_pricebookproductrel.productid = vtiger_products.productid", ]
					);
				}
			} else {
				parent::createQuery();
			}
		}
		$storage = $this->getUserStorageId();
		if ($storage) {
			$this->queryGenerator->setCustomColumn('u_#__istorages_products.qtyinstock as storage_qtyinstock');
			$this->queryGenerator->addJoin([
				'LEFT JOIN',
				'u_#__istorages_products',
				"u_#__istorages_products.crmid={$storage} AND u_#__istorages_products.relcrmid = vtiger_products.productid", ]
			);
		}
	}

	/** {@inheritdoc}  */
	protected function isRawData(): bool
	{
		return true;
	}

	/** {@inheritdoc}  */
	protected function getRecordFromRow(array $row): array
	{
		$record = parent::getRecordFromRow($row);
		$unitPrice = (new \Vtiger_MultiCurrency_UIType())->getValueForCurrency($row['unit_price'], \App\Fields\Currency::getDefault()['id']);
		$regionalTaxes = $availableTaxes = '';
		if ($this->isUserPermissions) {
			$availableTaxes = 'LBL_GROUP_TAX';
		} else {
			if (isset($this->parentRecordModel)) {
				$availableTaxes = $this->parentRecordModel->get('accounts_available_taxes');
				$regionalTaxes = $this->parentRecordModel->get('taxes');
			}
			if (!empty($row['listprice'])) {
				$unitPrice = $row['listprice'];
			}
		}
		$record['unit_price'] = \CurrencyField::convertToUserFormatSymbol($unitPrice);
		$taxParam = \Api\WebservicePremium\Record::getTaxParam($availableTaxes, $row['taxes'] ?? '', $regionalTaxes);
		$taxConfig = \Vtiger_Inventory_Model::getTaxesConfig();
		$record['unit_gross'] = \CurrencyField::convertToUserFormatSymbol($unitPrice + (new \Vtiger_Tax_InventoryField())->getTaxValue($taxParam, $unitPrice, (int) $taxConfig['aggregation']));
		return $record;
	}

	/** {@inheritdoc}  */
	protected function getRawDataFromRow(array $row): array
	{
		$row = parent::getRawDataFromRow($row);
		$unitPrice = $row['unit_price'] = (new \Vtiger_MultiCurrency_UIType())->getValueForCurrency($row['unit_price'], \App\Fields\Currency::getDefault()['id']);
		$regionalTaxes = $availableTaxes = '';
		if ($this->isUserPermissions) {
			$availableTaxes = 'LBL_GROUP_TAX';
		} else {
			if (isset($this->parentRecordModel)) {
				$availableTaxes = $this->parentRecordModel->get('accounts_available_taxes');
				$regionalTaxes = $this->parentRecordModel->get('taxes');
			}
			if (!empty($row['listprice'])) {
				$unitPrice = $row['unit_price'] = $row['listprice'];
			}
		}
		$taxParam = \Api\WebservicePremium\Record::getTaxParam($availableTaxes, $row['taxes'] ?? '', $regionalTaxes);
		$taxConfig = \Vtiger_Inventory_Model::getTaxesConfig();
		$row['unit_gross'] = $unitPrice + (new \Vtiger_Tax_InventoryField())->getTaxValue($taxParam, $unitPrice, (int) $taxConfig['aggregation']);
		$row['qtyinstock'] = $row['storage_qtyinstock'] ?? 0;
		return $row;
	}

	/** {@inheritdoc}  */
	protected function getColumnNames(): array
	{
		$headers = parent::getColumnNames();
		$headers['unit_gross'] = \App\Language::translate('LBL_GRAND_TOTAL');
		return $headers;
	}
}
