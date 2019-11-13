<?php
/**
 * Users Login action file.
 *
 * @package Api
 *
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\Users;

use OpenApi\Annotations as OA;

/**
 * Users Login action class.
 */
class Login extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['POST'];

	/** String constant 'Y-m-d H:i:s' @var string */
	public const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Check permission to method.
	 *
	 * @return bool
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * Check permission to module.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	public function checkPermissionToModule()
	{
		return true;
	}

	/**
	 * Post method.
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/Users/Login",
	 *		summary="Logs user into the system",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : ""}
	 *    },
	 *		@OA\RequestBody(
	 *  			required=true,
	 *  			description="Input data format",
	 *    		@OA\JsonContent(ref="#/components/schemas/UsersLoginRequestBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/UsersLoginRequestBody")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/UsersLoginRequestBody")
	 *     		),
	 *	  ),
	 *    @OA\Parameter(
	 *        name="X-ENCRYPTED",
	 *        in="header",
	 *        required=true,
	 * 				@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *    ),
	 *		@OA\Response(
	 *				response=200,
	 *				description="User details",
	 *				@OA\JsonContent(ref="#/components/schemas/UsersLoginResponseBody"),
	 *				@OA\XmlContent(ref="#/components/schemas/UsersLoginResponseBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="text/html",
	 *         		@OA\Schema(ref="#/components/schemas/UsersLoginResponseBody")
	 *     		),
	 *		),
	 *		@OA\Response(
	 *				response=401,
	 *				description="Invalid data access OR Invalid user password OR No crmid"
	 *		),
	 *		@OA\Response(
	 *				response=405,
	 *				description="Invalid method"
	 *		),
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="basicAuth",
	 *		type="http",
	 *    in="header",
	 *		scheme="basic"
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="ApiKeyAuth",
	 *   	type="apiKey",
	 *    in="header",
	 * 		name="X-API-KEY",
	 *   	description="Webservice api key"
	 * ),
	 * @OA\Schema(
	 *	  schema="X-ENCRYPTED",
	 *		type="string",
	 *  	description="Is the content request is encrypted",
	 *  	enum={0, 1},
	 *   	default=0
	 * ),
	 * @OA\Schema(
	 * 		schema="UsersLoginRequestBody",
	 * 		title="Users login request body",
	 * 		description="JSON or form-data",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="userName",
	 *        description="Webservice user name",
	 *     	  type="string",
	 * 		),
	 *    @OA\Property(
	 *     	  property="password",
	 *     	 	description="Webservice user password",
	 *    	 	type="string"
	 *    ),
	 *    @OA\Property(
	 *     	  property="params",
	 *     	 	description="Additional parameters sent by the user, extending the current settings, e.g. language",
	 *   		 	type="array",
	 * 				@OA\Items(type="string"),
	 *    )
	 * ),
	 * @OA\Schema(
	 * 		schema="UsersLoginResponseBody",
	 * 		title="Users login response body",
	 * 		description="Users login response body",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="status",
	 *        description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 * 				enum={0, 1},
	 *     	  type="integer",
	 * 				example=1
	 * 		),
	 *    @OA\Property(
	 *     	  property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="object",
	 *   			@OA\Property(property="token", type="string", minLength=40, maxLength=40),
	 *   			@OA\Property(property="name", type="string"),
	 *    		@OA\Property(property="parentName", type="string"),
	 *    		@OA\Property(property="lastLoginTime", type="string", format="date-time", example="2019-10-07 08:32:38"),
	 *    		@OA\Property(property="lastLogoutTime", type="string", format="date-time", example="null"),
	 *    		@OA\Property(property="language", type="string", example="pl-PL"),
	 *    		@OA\Property(property="type", type="integer"),
	 *    		@OA\Property(property="companyId", type="integer"),
	 *    		@OA\Property(
	 * 						property="companyDetails",
	 * 						type="object",
	 * 						title="Company details, optional parameter depending on the user type",
	 *  					@OA\Property(property="check_stock_levels", type="boolean"),
	 * 						@OA\Property(property="sum_open_orders", type="integer"),
	 * 						@OA\Property(property="creditlimit", type="integer")
	 * 				),
	 *    		@OA\Property(property="logged", type="boolean"),
	 *    		@OA\Property(
	 * 						property="preferences",
	 * 						type="object",
	 *    				@OA\Property(property="activity_view", type="string"),
	 *    				@OA\Property(property="hour_format", type="integer"),
	 *    				@OA\Property(property="start_hour", type="string"),
	 *    				@OA\Property(property="date_format", type="string"),
	 *    				@OA\Property(property="date_format_js", type="string"),
	 *    				@OA\Property(property="dayoftheweek", type="string"),
	 *    				@OA\Property(property="time_zone", type="string"),
	 *    				@OA\Property(property="currency_id", type="integer"),
	 *    				@OA\Property(property="currency_grouping_pattern", type="string"),
	 *    				@OA\Property(property="currency_decimal_separator", type="string"),
	 *    				@OA\Property(property="currency_grouping_separator", type="string"),
	 *    				@OA\Property(property="currency_symbol_placement", type="string"),
	 *    				@OA\Property(property="no_of_currency_decimals", type="integer"),
	 *    				@OA\Property(property="truncate_trailing_zeros", type="integer"),
	 *    				@OA\Property(property="end_hour", type="string"),
	 *    				@OA\Property(property="currency_name", type="string"),
	 *    				@OA\Property(property="currency_code", type="string"),
	 *    				@OA\Property(property="currency_symbol", type="string"),
	 *    				@OA\Property(property="conv_rate", type="number", format="float"),
	 * 				),
	 *    ),
	 * ),
	 * @OA\Tag(
	 *   name="Users",
	 *   description="Access to user methods"
	 * )
	 */
	public function post()
	{
		$db = \App\Db::getInstance('webservice');
		$row = (new \App\Db\Query())
			->from('w_#__portal_user')
			->where(['user_name' => $this->controller->request->get('userName'), 'status' => 1])
			->limit(1)->one($db);
		if (!$row) {
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
		if (\App\Encryption::getInstance()->decrypt($row['password_t']) !== $this->controller->request->get('password')) {
			throw new \Api\Core\Exception('Invalid user password', 401);
		}
		if (\Api\Portal\Privilege::USER_PERMISSIONS !== $row['type'] && (empty($row['crmid']) || !\App\Record::isExists($row['crmid']))) {
			throw new \Api\Core\Exception('No crmid', 401);
		}
		$db->createCommand()->update('w_#__portal_user', ['login_time' => date(static::DATE_TIME_FORMAT)], ['id' => $row['id']])->execute();
		$row = $this->updateSession($row);
		$userModel = \App\User::getUserModel($row['user_id']);
		$parentId = \Api\Portal\Privilege::USER_PERMISSIONS !== $row['type'] ? \App\Record::getParentRecord($row['crmid']) : 0;
		$companyDetails = [];
		if (!empty($parentId)) {
			$parentRecordModel = \Vtiger_Record_Model::getInstanceById($parentId, 'Accounts');
			$companyDetails['check_stock_levels'] = (bool) $parentRecordModel->get('check_stock_levels');
			$companyDetails['sum_open_orders'] = $parentRecordModel->get('sum_open_orders');
			$creditLimitId = $parentRecordModel->get('creditlimit');
			if (!empty($creditLimitId)) {
				$limits = \Vtiger_InventoryLimit_UIType::getLimits();
				$companyDetails['creditlimit'] = $limits[$creditLimitId]['value'] ?? 0;
			}
		}
		return [
			'token' => $row['token'],
			'name' => \App\Record::getLabel($row['crmid']),
			'parentName' => empty($parentId) ? '' : \App\Record::getLabel($parentId),
			'lastLoginTime' => $row['login_time'],
			'lastLogoutTime' => $row['logout_time'],
			'language' => $row['language'],
			'type' => $row['type'],
			'companyId' => (\Api\Portal\Privilege::USER_PERMISSIONS !== $row['type']) ? $parentId : 0,
			'companyDetails' => $companyDetails,
			'logged' => true,
			'preferences' => [
				'activity_view' => $userModel->getDetail('activity_view'),
				'hour_format' => $userModel->getDetail('hour_format'),
				'start_hour' => $userModel->getDetail('start_hour'),
				'date_format' => $userModel->getDetail('date_format'),
				'date_format_js' => \App\Fields\Date::currentUserJSDateFormat($userModel->getDetail('date_format')),
				'dayoftheweek' => $userModel->getDetail('dayoftheweek'),
				'time_zone' => $userModel->getDetail('time_zone'),
				'currency_id' => $userModel->getDetail('currency_id'),
				'currency_grouping_pattern' => $userModel->getDetail('currency_grouping_pattern'),
				'currency_decimal_separator' => $userModel->getDetail('currency_decimal_separator'),
				'currency_grouping_separator' => $userModel->getDetail('currency_grouping_separator'),
				'currency_symbol_placement' => $userModel->getDetail('currency_symbol_placement'),
				'no_of_currency_decimals' => (int) $userModel->getDetail('no_of_currency_decimals'),
				'truncate_trailing_zeros' => $userModel->getDetail('truncate_trailing_zeros'),
				'end_hour' => $userModel->getDetail('end_hour'),
				'currency_name' => $userModel->getDetail('currency_name'),
				'currency_code' => $userModel->getDetail('currency_code'),
				'currency_symbol' => $userModel->getDetail('currency_symbol'),
				'conv_rate' => $userModel->getDetail('conv_rate'),
			],
		];
	}

	/**
	 * Update session.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function updateSession($row)
	{
		$db = \App\Db::getInstance('webservice');
		$row['token'] = hash('sha1', microtime(true) . random_int(PHP_INT_MIN, PHP_INT_MAX));
		$params = $this->controller->request->getArray('params');
		if (!empty($params['language'])) {
			$language = $params['language'];
		} else {
			$language = empty($row['language']) ? $this->getLanguage() : $row['language'];
		}
		$db->createCommand()->insert('w_#__portal_session', [
			'id' => $row['token'],
			'user_id' => $row['id'],
			'created' => date(static::DATE_TIME_FORMAT),
			'changed' => date(static::DATE_TIME_FORMAT),
			'language' => $language,
			'params' => \App\Json::encode($params),
		])->execute();
		$row['language'] = $language;
		$db->createCommand()->delete('w_#__portal_session', ['<', 'changed', date(static::DATE_TIME_FORMAT, strtotime('-1 day'))])->execute();
		return $row;
	}
}
