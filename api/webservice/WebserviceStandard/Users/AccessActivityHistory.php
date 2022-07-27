<?php
/**
 * Webservice standard container - Get user history of access activity file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license	YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebserviceStandard\Users;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container - Get user history of access activity class.
 */
class AccessActivityHistory extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-condition', 'x-row-offset', 'x-row-limit'];

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
	}

	/**
	 * Get user history of access activity.
	 *
	 * @api
	 *
	 * @return array
	 *
	 *	@OA\Get(
	 *		path="/webservice/WebserviceStandard/Users/AccessActivityHistory",
	 *		description="Get user history of access activity",
	 *		summary="History of access activity data",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 50", required=false, example=1000),
	 *		@OA\Parameter(name="x-row-offset", in="header", @OA\Schema(type="integer"), description="Offset, default: 0", required=false, example=0),
	 *		@OA\Parameter(name="x-condition", in="header", description="Conditions [Json format]", required=false,
	 *			@OA\JsonContent(ref="#/components/schemas/Conditions-For-Native-Query"),
	 *		),
	 *		@OA\Response(response=200, description="User history of access activity",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Get_AccessActivityHistory_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Get_AccessActivityHistory_Response"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="Users_Get_AccessActivityHistory_Response",
	 *		title="Users module - History of access activity data",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			title="User data",
	 *			type="object",
	 *			@OA\AdditionalProperties(
	 *				title="Condition details",
	 *				type="object",
	 *				@OA\Property(property="time", type="string", description="Date time in user format", example="2021-06-01 11:57"),
	 *				@OA\Property(property="status", type="string", description="Operation name", example="Signed in"),
	 *				@OA\Property(property="agent", type="string", description="User agent", example="PostmanRuntime/7.28.0"),
	 *				@OA\Property(property="ip", type="string", description="IP address", example="127.0.0.1"),
	 *			),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		$limit = 50;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}
		$offset = 0;
		if ($requestOffset = $this->controller->request->getHeader('x-row-offset')) {
			$offset = (int) $requestOffset;
		}
		$query = (new \App\Db\Query())->select(['time', 'status', 'agent', 'ip'])
			->from($this->controller->app['tables']['loginHistory'])
			->where(['user_id' => $this->getUserData('id')])
			->orderBy(['id' => SORT_DESC])
			->limit($limit)->offset($offset);
		if ($conditions = $this->controller->request->getHeader('x-condition')) {
			$query->andWhere(\App\Json::decode($conditions));
		}
		$dataReader = $query->createCommand(\App\Db::getInstance('log'))->query();
		$rows = [];
		while ($row = $dataReader->read()) {
			$rows[] = [
				'time' => \App\Fields\DateTime::formatToDisplay($row['time']),
				'status' => \App\Language::translate($row['status'], 'Settings:WebserviceUsers'),
				'agent' => $row['agent'],
				'ip' => $row['ip'],
			];
		}
		$dataReader->close();
		return $rows;
	}
}
