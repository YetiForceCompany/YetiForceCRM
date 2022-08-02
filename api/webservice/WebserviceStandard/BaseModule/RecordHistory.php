<?php
/**
 * Webservice standard container - Get record history file.
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
 * Webservice standard container - Get record history class.
 */
class RecordHistory extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-raw-data', 'x-page', 'x-row-limit', 'x-start-with'];

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
	 * @api
	 *
	 * @return array
	 * @OA\Get(
	 *		path="/webservice/WebserviceStandard/{moduleName}/RecordHistory/{recordId}",
	 *		description="Gets the history of the record",
	 *		summary="Record history",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 100", required=false, example=50),
	 *		@OA\Parameter(name="x-page", in="header", @OA\Schema(type="integer"), description="Page number, default: 1", required=false, example=1),
	 *		@OA\Parameter(name="x-start-with", in="header", @OA\Schema(type="integer"), description="Show history from given ID", required=false, example=5972),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(response=200, description="Recent activities detail",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_RecordHistory_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_RecordHistory_Response"),
	 *		),
	 *		@OA\Response(response=403, description="`No permissions to view record` OR `MadTracker is turned off`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=404, description="Record doesn't exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_Get_RecordHistory_Response",
	 *		title="Base module - Response action history record",
	 *		description="Action module for recent activities in CRM",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="Returns recent activities that took place in CRM",
	 *			required={"records", "isMorePages"},
	 * 			@OA\Property(property="records", type="object", title="Entires of recent record activities",
	 *				@OA\AdditionalProperties(type="object", title="Key indicating the number of changes made to a given record",
	 *					required={"time", "owner", "status"},
	 * 					@OA\Property(property="time", type="string", description="Showing the exact date on which the change took place", example="2019-10-07 08:32:38"),
	 *					@OA\Property(property="owner", type="string", description="Username of the user who made the change", example="System Admin"),
	 *					@OA\Property(property="status", type="string", description="Name of the action that was carried out", example="changed"),
	 * 					@OA\Property(property="rawTime", type="string", description="Showing the exact date on which the change took place",  example="2019-10-07 08:32:38"),
	 *					@OA\Property(property="rawOwner", type="integer", description="User ID of the user who made the change", example=1),
	 *					@OA\Property(property="rawStatus", type="string", description="The name of the untranslated label", example="LBL_UPDATED"),
	 *					@OA\Property(property="data", title="Additional information",
	 *						oneOf={
	 *							@OA\Schema(type="object", title="Record data create",
	 *								@OA\AdditionalProperties(
	 *									required={"label", "value", "raw"},
	 *									@OA\Property(property="label", type="string", description="Translated field label", example="Name"),
	 *									@OA\Property(property="value", type="string", description="Value, the data type depends on the field type", example="Jan Kowalski"),
	 *									@OA\Property(property="raw", type="string", description="Value in database format, only available in `x-raw-data`", example="Jan Kowalski"),
	 *								),
	 *							),
	 *							@OA\Schema(type="object", title="Record data change", description="Edit, conversation",
	 *								@OA\AdditionalProperties(
	 *									required={"label", "from", "to"},
	 *									@OA\Property(property="label", type="string", description="Translated field label", example="Name"),
	 *									@OA\Property(property="from", type="string", description="Value before change, the data type depends on the field type", example="Jan Kowalski"),
	 *									@OA\Property(property="to", type="string", description="Value after change, the data type depends on the field type", example="Jan Nowak"),
	 *									@OA\Property(property="rawFrom", type="string", description="Value before change, value in database format, only available in `x-raw-data`", example="Jan Kowalski"),
	 *									@OA\Property(property="rawTo", type="string", description="Value after change, value in database format, only available in `x-raw-data`", example="Jan Nowak"),
	 *								),
	 *							),
	 *							@OA\Schema(type="object", title="Operations on related records", description="Adding relations, removing relations, transferring records",
	 *								required={"targetModule", "targetModuleLabel", "targetLabel"},
	 *								@OA\Property(property="targetModule", type="string", description="The name of the target related module", example="Contacts"),
	 *								@OA\Property(property="targetModuleLabel", type="string", description="Translated module name", example="Kontakt"),
	 *								@OA\Property(property="targetLabel", type="string", description="The label name of the target related module", example="Jan Kowalski"),
	 *								@OA\Property(property="targetId", type="integer", description="Id of the target related module", example=394),
	 *							),
	 *						},
	 *					),
	 *				),
	 *			),
	 * 			@OA\Property(property="isMorePages", type="boolean", example=true),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		$pagingModel = new \Vtiger_Paging_Model();
		$limit = 100;
		$isMorePages = false;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}
		$pagingModel->set('limit', $limit);
		if ($page = $this->controller->request->getHeader('x-page')) {
			$pagingModel->set('page', (int) $page);
		}
		$startIndex = $pagingModel->getStartIndex();
		$query = (new \App\Db\Query())
			->from('vtiger_modtracker_basic')
			->where(['crmid' => $this->controller->request->getInteger('record')])
			->andWhere(['not in', 'status', [\ModTracker_Record_Model::DISPLAYED, \ModTracker_Record_Model::SHOW_HIDDEN_DATA]])
			->limit($limit + 1)
			->offset($startIndex)
			->orderBy(['changedon' => SORT_DESC]);
		if ($startWith = $this->controller->request->getHeader('x-start-with')) {
			$query->andWhere(['>=', 'id', (int) $startWith]);
		}
		$dataReader = $query->createCommand()->query();
		$records = [];

		$isRawData = $this->isRawData();
		while ($row = $dataReader->read()) {
			if (!$limit--) {
				$isMorePages = true;
				break;
			}
			$recordModel = (new \ModTracker_Record_Model())->setData($row)->setParent($row['crmid'], $row['module']);
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
			if (($isCreate = $recordModel->isCreate()) || $recordModel->isUpdate() || $recordModel->isTransferEdit()) {
				$data = [];
				foreach ($recordModel->getFieldInstances() as $fieldModel) {
					if ($fieldModel && ($fieldInstance = $fieldModel->getFieldInstance())) {
						\Api\WebserviceStandard\Fields::loadWebserviceByField($fieldInstance, $this);
						if ($fieldInstance->isViewable() && 5 !== $fieldModel->getFieldInstance()->getDisplayType()) {
							$fieldName = $fieldInstance->getName();
							$data[$fieldName]['label'] = $fieldInstance->getFullLabelTranslation();
							if ($isCreate) {
								$data[$fieldName]['value'] = $fieldInstance->getUITypeModel()->getHistoryDisplayValue($fieldModel->get('postvalue'), $recordModel, true);
							} else {
								$data[$fieldName]['from'] = $fieldInstance->getUITypeModel()->getHistoryDisplayValue($fieldModel->get('prevalue'), $recordModel, true);
								$data[$fieldName]['to'] = $fieldInstance->getUITypeModel()->getHistoryDisplayValue($fieldModel->get('postvalue'), $recordModel, true);
							}
							if ($isRawData) {
								if ($isCreate) {
									$data[$fieldName]['raw'] = $fieldModel->get('postvalue');
								} else {
									$data[$fieldName]['rawFrom'] = $fieldModel->get('prevalue');
									$data[$fieldName]['rawTo'] = $fieldModel->get('postvalue');
								}
							}
						}
					}
				}
				if (!$data) {
					continue;
				}
				$row['data'] = $data;
			} elseif ($recordModel->isRelationLink() || $recordModel->isRelationUnLink() || $recordModel->isTransferLink() || $recordModel->isTransferUnLink()) {
				$relationInstance = $recordModel->getRelationInstance();
				$row['data'] = [
					'targetModule' => $relationInstance->get('targetmodule'),
					'targetModuleLabel' => \App\Language::translateSingularModuleName($relationInstance->get('targetmodule')),
					'targetLabel' => \App\Purifier::encodeHtml(\App\Utils\Completions::decode(\App\Record::getLabel($relationInstance->get('targetid'), true), \App\Utils\Completions::FORMAT_TEXT)),
				];
				if ($isRawData) {
					$row['data']['targetId'] = $relationInstance->get('targetid');
				}
			}
			$records[$recordModel->get('id')] = $row;
		}
		$dataReader->close();
		return [
			'records' => $records,
			'isMorePages' => $isMorePages,
		];
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
