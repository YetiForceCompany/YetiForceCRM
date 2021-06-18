<?php
/**
 * RestApi container - Get record history file.
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
 * RestApi container - Get record history class.
 */
class RecordHistory extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-raw-data', 'x-row-offset', 'x-row-limit', 'x-start-with'];

	/** @var \Vtiger_Record_Model Record model instance. */
	protected $recordModel;

	/** {@inheritdoc}  */
	public function checkAction(): void
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
	}

	/**
	 * Get related record list method.
	 *
	 * @return array
	 * @OA\Get(
	 *		path="/webservice/RestApi/{moduleName}/RecordHistory/{recordId}",
	 *		description="Gets the history of the record",
	 *		summary="Record history",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 1000", required=false, example=1000),
	 *		@OA\Parameter(name="x-row-offset", in="header", @OA\Schema(type="integer"), description="Offset, default: 0", required=false, example=0),
	 *		@OA\Parameter(
	 *			name="x-start-with",
	 *			description="Show history from given ID",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=5972,
	 *			required=false
	 *		),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Recent activities detail",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_RecordHistory_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_RecordHistory_ResponseBody"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="`No permissions to view record` OR `MadTracker is turned off`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="Record doesn't exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_RecordHistory_ResponseBody",
	 *		title="Base module - Response action history record",
	 *		description="Action module for recent activities in CRM",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, title="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Returns recent activities that took place in CRM",
	 *			type="object",
	 *			@OA\Property(
	 *				property="response",
	 *				title="Contains what actions have been performed and returns the data that has changed",
	 *				type="object",
	 *				@OA\AdditionalProperties(
	 *					type="object",
	 *					title="Key indicating the number of changes made to a given record",
	 * 					@OA\Property(property="time", type="string", title="Showing the exact date on which the change took place",  example="2019-10-07 08:32:38"),
	 *					@OA\Property(property="owner", type="string", title="Username of the user who made the change", example="System Admin"),
	 *					@OA\Property(property="status", type="string", title="Name of the action that was carried out", example="changed"),
	 * 					@OA\Property(property="rawTime", type="string", title="Showing the exact date on which the change took place",  example="2019-10-07 08:32:38"),
	 *					@OA\Property(property="rawOwner", type="integer", title="User ID of the user who made the change", example=1),
	 *					@OA\Property(property="rawStatus", type="string", title="The name of the untranslated label", example="LBL_UPDATED"),
	 *					@OA\Property(
	 *						property="data",
	 *						type="object",
	 *						title="Field system name",
	 *						@OA\AdditionalProperties(
	 *							@OA\Property(property="from", type="string", title="Value before change, dynamically collected value - the data type depends on the field type", example="Jan Kowalski"),
	 *							@OA\Property(property="to", type="string", title="Value after change, dynamically collected value - the data type depends on the field type", example="Jan Nowak"),
	 *							@OA\Property(property="rawFrom", type="string", title="Value before change", example="Jan Kowalski"),
	 *							@OA\Property(property="rawTo", type="string", title="Value after change", example="Jan Nowak"),
	 *							@OA\Property(property="targetModule", type="string", title="The name of the target related module", example="Contacts"),
	 *							@OA\Property(property="targetLabel", type="string", title="The label name of the target related module", example="Jan Kowalski"),
	 *							@OA\Property(property="targetId", type="integer", title="Id of the target related module", example=394),
	 *						),
	 *					),
	 *				),
	 *			),
	 *		),
	 *	),
	 */
	public function get(): array
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
		return 1 === (int) ($this->controller->headers['x-raw-data'] ?? 0);
	}
}
