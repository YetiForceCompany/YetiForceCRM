<?php
/**
 * Get record history file.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Get record history class.
 */
class RecordHistory extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];
	/**
	 * {@inheritdoc}
	 */
	public $allowedHeaders = ['x-raw-data', 'x-row-offset', 'x-row-limit', 'x-start-with'];
	/**
	 * Record model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	protected $recordModel;

	/**
	 * {@inheritdoc}
	 */
	public function checkAction()
	{
		parent::checkAction();
		$moduleName = $this->controller->request->getModule();
		if ($this->controller->request->isEmpty('record', true) || !\App\Record::isExists($this->controller->request->getInteger('record'), $moduleName)) {
			throw new \Api\Core\Exception('Record doesn\'t exist', 404);
		}
		$this->recordModel = \Vtiger_Record_Model::getInstanceById($this->controller->request->getInteger('record'), $moduleName);
		if (!$this->recordModel->isViewable()) {
			throw new \Api\Core\Exception('No permissions to view record', 403);
		}
		if (!$this->recordModel->getModule()->isTrackingEnabled()) {
			throw new \Api\Core\Exception('MadTracker is turned off', 403);
		}
		return true;
	}

	/**
	 * Get related record list method.
	 *
	 * @return array
	 * @OA\Get(
	 *		path="/webservice/{moduleName}/RecordHistory/{recordId}",
	 *		summary="Get record history",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *		},
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="recordId",
	 *			description="Record id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-limit",
	 *			description="Get rows limit, default: 1000",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=1000,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-offset",
	 *			description="Offset, default: 0",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=0,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-start-with",
	 *			description="Show history from given ID",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=5972,
	 *			required=false
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
	 *			description="Recent activities detail",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_RecordHistory_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_RecordHistory_ResponseBody"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_RecordHistory_ResponseBody",
	 *		title="Base module - Response action history record",
	 *		description="Action module for recent activities in CRM",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={"0", "1"},
	 *			type="integer",
	 *			example=1
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Returns recent activities that took place in CRM",
	 *			type="object",
	 *			@OA\Property(
	 *				property="response",
	 *				description="Contains what actions have been performed and returns the data that has changed",
	 *				type="object",
	 *				@OA\AdditionalProperties(
	 *					type="object",
	 *					description="Key indicating the number of changes made to a given record",
	 * 					@OA\Property(property="time", type="string", description="Showing the exact date on which the change took place",  format="date-time", example="2019-10-07 08:32:38"),
	 *					@OA\Property(property="owner", type="string", description="Username of the user who made the change", example="System Admin"),
	 *					@OA\Property(property="status", type="string", description="Name of the action that was carried out", example="changed"),
	 *					@OA\Property(property="rawOwner", type="integer", description="User ID of the user who made the change", example=1),
	 *					@OA\Property(property="rawStatus", type="string", description="The name of the untranslated label", example="LBL_UPDATED"),
	 *					@OA\Property(
	 *						property="data",
	 *						type="object",
	 *						description="Field system name",
	 *						@OA\AdditionalProperties(
	 *							@OA\Property(property="from", type="string", description="Value before change, dynamically collected value - the data type depends on the field type", example="Jan Kowalski"),
	 *							@OA\Property(property="to", type="string", description="Value after change, dynamically collected value - the data type depends on the field type", example="Jan Nowak"),
	 *							@OA\Property(property="rawFrom", type="string", description="Value before change", example="Jan Kowalski"),
	 *							@OA\Property(property="rawTo", type="string", description="Value after change", example="Jan Nowak"),
	 *							@OA\Property(property="targetModule", type="string", description="The name of the target related module", example="Contacts"),
	 *							@OA\Property(property="targetLabel", type="string", description="The label name of the target related module", example="Jan Kowalski"),
	 *							@OA\Property(property="targetId", type="integer", description="Id of the target related module", example=394),
	 *						),
	 *					),
	 *				),
	 *			),
	 *		),
	 *	),
	 */
	public function get()
	{
		$pagingModel = new \Vtiger_Paging_Model();
		$limit = 100;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}
		$pagingModel->set('limit', $limit);
		if ($requestOffset = $this->controller->request->getHeader('x-row-offset')) {
			$pagingModel->set('page', (int) $requestOffset);
		}
		$recentActivities = \ModTracker_Record_Model::getUpdates($this->controller->request->getInteger('record'), $pagingModel, 'changes', $this->controller->request->getHeader('x-start-with'));
		$response = [];
		$isRawData = $this->isRawData();
		foreach ($recentActivities as $recordModel) {
			$row = [
				'time' => $recordModel->getDisplayActivityTime(),
				'owner' => \App\Fields\Owner::getUserLabel($recordModel->get('whodid')) ?: '',
				'status' => \App\Language::translate($recordModel->getStatusLabel(), 'ModTracker'),
			];
			if ($isRawData) {
				$row['rawTime'] = $recordModel->getActivityTime();
				$row['rawOwner'] = $recordModel->get('whodid') ?: 0;
				$row['rawStatus'] = $recordModel->getStatusLabel();
			}
			if ($recordModel->isCreate() || $recordModel->isUpdate() || $recordModel->isTransferEdit()) {
				$data = [];
				foreach ($recordModel->getFieldInstances() as $fieldModel) {
					if ($fieldModel && ($fieldInstance = $fieldModel->getFieldInstance()) && $fieldInstance->isViewable() && 5 !== $fieldModel->getFieldInstance()->getDisplayType()) {
						$fieldName = $fieldInstance->getName();
						$data[$fieldName]['from'] = $fieldInstance->getUITypeModel()->getHistoryDisplayValue($fieldModel->get('prevalue'), $recordModel, true);
						$data[$fieldName]['to'] = $fieldInstance->getUITypeModel()->getHistoryDisplayValue($fieldModel->get('postvalue'), $recordModel, true);
						if ($isRawData) {
							$data[$fieldName]['rawFrom'] = $fieldModel->get('prevalue');
							$data[$fieldName]['rawTo'] = $fieldModel->get('postvalue');
						}
					}
				}
				$row['data'] = $data;
			} elseif ($recordModel->isRelationLink() || $recordModel->isRelationUnLink()) {
				$relationInstance = $recordModel->getRelationInstance();
				$row['data'] = [
					'targetModule' => $relationInstance->get('targetmodule'),
					'targetLabel' => $relationInstance->getValue(),
				];
				if ($isRawData) {
					$row['data']['targetId'] = $relationInstance->get('targetid');
				}
			}
			$response[$recordModel->get('id')] = $row;
		}
		return $response;
	}

	/**
	 * Check if you send raw data.
	 *
	 * @return bool
	 */
	protected function isRawData(): bool
	{
		return 1 === (int) $this->controller->headers['x-raw-data'];
	}
}
