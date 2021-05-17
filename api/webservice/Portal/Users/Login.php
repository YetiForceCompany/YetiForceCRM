<?php
/**
 * Portal container - Users Login action file.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\Users;

use OpenApi\Annotations as OA;

/**
 * Portal container - Users Login action class.
 */
class Login extends \Api\RestApi\Users\Login
{
	/**
	 * Post method.
	 *
	 * @return array
	 *
	 *	@OA\Post(
	 *		path="/webservice/Portal/Users/Login",
	 *		summary="Logs user into the system",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *  		description="Input data format",
	 *    		@OA\JsonContent(ref="#/components/schemas/UsersLoginRequestBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/UsersLoginRequestBody")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/UsersLoginRequestBody")
	 *     		),
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 * 			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *      ),
	 *		@OA\Response(
	 *			response=200,
	 *			description="User details",
	 *			@OA\JsonContent(ref="#/components/schemas/UsersLoginResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/UsersLoginResponseBody")
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="Invalid data access OR Invalid user password OR No crmid",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *	),
	 *	@OA\SecurityScheme(
	 *		type="http",
	 *		securityScheme="basicAuth",
	 *		scheme="basic",
	 *   	description="Basic Authentication header"
	 *	),
	 *	@OA\SecurityScheme(
	 * 		name="X-API-KEY",
	 *   	type="apiKey",
	 *    	in="header",
	 *		securityScheme="ApiKeyAuth",
	 *   	description="Webservice api key header"
	 *	),
	 *	@OA\Schema(
	 *		schema="X-ENCRYPTED",
	 *		type="string",
	 *  	description="Is the content request is encrypted",
	 *  	enum={0, 1},
	 *   	default=0
	 *	),
	 *	@OA\Schema(
	 * 		schema="UsersLoginRequestBody",
	 * 		title="Users module - Users login request body",
	 * 		description="JSON or form-data",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="userName",
	 *			description="Webservice user name",
	 *			type="string",
	 * 		),
	 *  	@OA\Property(
	 *       	property="password",
	 *			description="Webservice user password",
	 *		type="string"
	 *      ),
	 *  	@OA\Property(
	 *       	property="params",
	 *       	description="Additional parameters sent by the user, extending the current settings, e.g. language",
	 *       	type="object",
	 *       	@OA\Property(property="language", type="string", example="pl-PL"),
	 *		)
	 *	),
	 *	@OA\Schema(
	 * 		schema="UsersLoginResponseBody",
	 * 		title="Users module - Users login response body",
	 * 		description="Users login response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 * 			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 * 			enum={0, 1},
	 *     	  	type="integer",
	 * 			example=1
	 * 		),
	 *		@OA\Property(
	 *     		property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="object",
	 *   		@OA\Property(property="token", type="string", minLength=40, maxLength=40),
	 *   		@OA\Property(property="name", type="string"),
	 *    		@OA\Property(property="parentName", type="string", example="YetiForce Company"),
	 *    		@OA\Property(property="lastLoginTime", type="string", format="date-time", example="2019-10-07 08:32:38"),
	 *    		@OA\Property(property="lastLogoutTime", type="string", format="date-time", example=null),
	 *    		@OA\Property(property="language", type="string", example="pl-PL"),
	 *    		@OA\Property(property="type", type="integer"),
	 *    		@OA\Property(property="companyId", type="integer"),
	 *    		@OA\Property(
	 * 				property="companyDetails",
	 * 				type="object",
	 * 				title="Company details, optional parameter depending on the user type",
	 *  			@OA\Property(property="check_stock_levels", type="boolean"),
	 * 				@OA\Property(property="sum_open_orders", type="number", format="double"),
	 * 				@OA\Property(property="creditlimit", type="integer")
	 * 			),
	 *    		@OA\Property(property="logged", type="boolean"),
	 *    		@OA\Property(
	 * 				property="preferences",
	 * 				type="object",
	 *    			@OA\Property(property="activity_view", type="string", example="This Month"),
	 *    			@OA\Property(property="hour_format", type="string", example="24"),
	 *    			@OA\Property(property="start_hour", type="string", example="08:00"),
	 *    			@OA\Property(property="end_hour", type="string", example="16:00"),
	 *    			@OA\Property(property="date_format", type="string", example="yyyy-mm-dd"),
	 *    			@OA\Property(property="date_format_js", type="string", example="Y-m-d"),
	 *    			@OA\Property(property="dayoftheweek", type="string", example="Monday"),
	 *    			@OA\Property(property="time_zone", type="string", example="Europe/Warsaw"),
	 *    			@OA\Property(property="currency_id", type="integer", example=1),
	 *    			@OA\Property(property="currency_grouping_pattern", type="string", example="123,456,789"),
	 *    			@OA\Property(property="currency_decimal_separator", type="string", example="."),
	 *    			@OA\Property(property="currency_grouping_separator", type="string", example=" "),
	 *    			@OA\Property(property="currency_symbol_placement", type="string", example="1.0$"),
	 *    			@OA\Property(property="no_of_currency_decimals", type="integer", example=2),
	 *    			@OA\Property(property="truncate_trailing_zeros", type="integer", example=0),
	 *    			@OA\Property(property="currency_name", type="string", example="Poland, Zlotych"),
	 *    			@OA\Property(property="currency_code", type="string", example="PLN"),
	 *    			@OA\Property(property="currency_symbol", type="string", example="zł"),
	 *    			@OA\Property(property="conv_rate", type="number", format="float", example="1.00000"),
	 * 			),
	 *    ),
	 *	),
	 *	@OA\Schema(
	 *		schema="Exception",
	 *		title="Error exception",
	 *		type="object",
	 *  	@OA\Property(
	 * 			property="status",
	 *			description="0 - error",
	 * 			enum={0},
	 *			type="integer",
	 *			example=0
	 * 		),
	 *		@OA\Property(
	 * 			property="error",
	 *     	 	description="Error  details",
	 *    	 	type="object",
	 *   		@OA\Property(property="message", type="string", example="Invalid method", description="To show more details turn on: config\Debug.php apiShowExceptionMessages = true"),
	 *   		@OA\Property(property="code", type="integer", example=405),
	 *   		@OA\Property(property="file", type="string", example="api\webservice\Portal\BaseAction\Files.php", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 *   		@OA\Property(property="line", type="integer", example=101, description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 * 			@OA\Property(property="backtrace", type="string", example="#0 api\webservice\Portal\BaseAction\Files.php (101) ....", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 *    	),
	 *	),
	 *	@OA\Tag(
	 *		name="Users",
	 *		description="Access to user methods"
	 *	)
	 */
	public function post(): array
	{
		$db = \App\Db::getInstance('webservice');
		$row = (new \App\Db\Query())
			->from('w_#__portal_user')
			->where(['user_name' => $this->controller->request->get('userName'), 'status' => 1])
			->limit(1)->one($db);
		if (!$row) {
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
		if (\App\Encryption::getInstance()->decrypt($row['password']) !== $this->controller->request->getRaw('password')) {
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
			'name' => $row['crmid'] ? \App\Record::getLabel($row['crmid']) : $userModel->getName(),
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
				'end_hour' => $userModel->getDetail('end_hour'),
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
				'currency_name' => $userModel->getDetail('currency_name'),
				'currency_code' => $userModel->getDetail('currency_code'),
				'currency_symbol' => $userModel->getDetail('currency_symbol'),
				'conv_rate' => $userModel->getDetail('conv_rate'),
			],
		];
	}
}
