<?php
/**
 * Webservice premium container - A store functionality - creates a record in an advanced module (orders) file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - A store functionality - creates a record in an advanced module (orders) class.
 */
class SaveInventory extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['POST'];

	/** @var string Module name. */
	private $moduleName;

	/** @var \Vtiger_Module_Model Module model. */
	private $moduleModel;

	/** @var \Vtiger_Record_Model Record model. */
	private $recordModel;

	/** @var \Api\WebservicePremium\Inventory Inventory object. */
	private $inventory;

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		parent::checkPermission();
		$moduleName = $this->controller->request->getModule();
		$this->recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
		if (!$this->recordModel->isCreateable()) {
			throw new \Api\Core\Exception('No permissions to create record', 403);
		}
	}

	/**
	 * Put method - A store functionality - creates a record in an advanced module (orders).
	 *
	 * @return array
	 *
	 *	@OA\Post(
	 *		path="/webservice/WebservicePremium/{moduleName}/SaveInventory",
	 *		summary="A store functionality - creates a record in an advanced module (orders)",
	 *		description="Creating inventory records for the functionality of the store",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Accounts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Contents of the response contains only id nd module name",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_SaveInventory_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_SaveInventory_ResponseBody"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="No permissions to create record",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseAction_SaveInventory_ResponseBody",
	 *		title="Base module - Create inventory record response schema",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Create result",
	 *			type="object",
	 *			oneOf={
	 *				@OA\Schema(ref="#/components/schemas/BaseAction_SaveInventory_ResponseBodySuccess"),
	 *				@OA\Schema(ref="#/components/schemas/BaseAction_SaveInventory_ResponseBodyError"),
	 *			}
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseAction_SaveInventory_ResponseBodySuccess",
	 *		title="Base module - Create inventory record response success schema",
	 *		@OA\Property(property="id", description="Record Id", type="integer", example=38),
	 *		@OA\Property(property="moduleName", type="string", example="SSingleOrders"),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseAction_SaveInventory_ResponseBodyError",
	 *		title="Base module - Create inventory record response error schema",
	 *		type="object",
	 *		@OA\Property(property="errors", description="Error details", type="object"),
	 *	),
	 */
	public function post(): array
	{
		if ($result = $this->checkBeforeSave()) {
			return $result;
		}
		foreach ($this->moduleModel->getFields() as $fieldName => $fieldModel) {
			if (!$fieldModel->isWritable()) {
				continue;
			}
			if ($this->controller->request->has($fieldName)) {
				$fieldModel->getUITypeModel()->setValueFromRequest($this->controller->request, $this->recordModel);
			}
		}
		$parentRecordId = $this->getParentCrmId();
		if (\Api\WebservicePremium\Privilege::USER_PERMISSIONS !== $this->getPermissionType()) {
			$fieldModel = current($this->moduleModel->getReferenceFieldsForModule('Accounts'));
			if ($fieldModel) {
				$this->recordModel->set($fieldModel->getFieldName(), $parentRecordId);
			}
			$fieldModel = current($this->moduleModel->getReferenceFieldsForModule('Contacts'));
			if ($fieldModel && !$this->controller->request->has($fieldModel->getFieldName())) {
				$this->recordModel->set($fieldModel->getFieldName(), $this->getUserCrmId());
			}
		}
		$fieldModel = current($this->moduleModel->getReferenceFieldsForModule('IStorages'));
		if ($fieldModel) {
			$this->recordModel->set($fieldModel->getFieldName(), $this->getUserStorageId());
		}
		$fieldPermission = \Api\Core\Module::getApiFieldPermission($this->moduleName, (int) $this->controller->app['id']);
		if ($fieldPermission) {
			$this->recordModel->setDataForSave([$fieldPermission['tablename'] => [$fieldPermission['columnname'] => 1]]);
		}
		$inventoryData = [];
		if ($this->controller->request->has('reference_id') && $this->controller->request->has('reference_module')) {
			$inventoryData = $this->inventory->getInventoryFromRecord($this->controller->request->getInteger('reference_id'), $this->controller->request->getByType('reference_module', 'Alnum'));
		} else {
			$inventoryData = $this->inventory->getInventoryData();
		}
		$this->recordModel->initInventoryData($inventoryData, false);
		if (!empty($parentRecordId)) {
			$parentRecordModel = \Vtiger_Record_Model::getInstanceById($parentRecordId, 'Accounts');
			$fieldName = 'assigned_user_id';
			if (!$this->controller->request->has($fieldName)) {
				$this->recordModel->set($fieldName, $parentRecordModel->get($fieldName));
			}
			$creditLimitId = $parentRecordModel->get('creditlimit');
			if (!empty($creditLimitId)) {
				$grossFieldModel = \Vtiger_Inventory_Model::getInstance($this->moduleName)->getField('gross');
				$limits = \Vtiger_InventoryLimit_UIType::getLimits();
				if ($grossFieldModel && $grossFieldModel->getSummaryValuesFromData($inventoryData) > (($limits[$creditLimitId]['value'] ?? 0) - $parentRecordModel->get('sum_open_orders'))) {
					return [
						'errors' => [
							'limit' => 'Merchant limit was exceeded',
						],
					];
				}
			}
		}
		$this->recordModel->save();
		return [
			'id' => $this->recordModel->getId(),
			'moduleName' => $this->moduleName,
		];
	}

	/**
	 * Check the request before the save.
	 *
	 * @return array
	 */
	private function checkBeforeSave(): array
	{
		$this->moduleName = $this->controller->request->getModule();
		if (!$this->controller->request->has('inventory')) {
			return [
				'errors' => [
					'record' => 'There are no inventory records',
				],
			];
		}
		$this->moduleModel = $this->recordModel->getModule();
		if (!$this->moduleModel->isInventory()) {
			return [
				'errors' => [
					'record' => 'This is not an inventory module',
				],
			];
		}
		$this->inventory = new \Api\WebservicePremium\Inventory($this->moduleName, $this);
		if ($this->getCheckStockLevels() && !$this->inventory->validate()) {
			return [
				'errors' => $this->inventory->getErrors(),
			];
		}
		return [];
	}

	/**
	 * Get information, whether to check inventory levels.
	 *
	 * @return bool
	 */
	public function getCheckStockLevels(): bool
	{
		if (\Api\WebservicePremium\Privilege::USER_PERMISSIONS !== $this->getPermissionType() && ($parentId = $this->getParentCrmId())) {
			return (bool) \Vtiger_Record_Model::getInstanceById($parentId)->get('check_stock_levels');
		}
		return false;
	}
}
