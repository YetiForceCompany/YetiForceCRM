<?php
/**
 * Users Login action file.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Mail\Users;

/**
 * Users Login action class.
 */
class Login extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['POST'];

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
	 *  			description="The data in different formats",
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
	 *				description="Invalid data access OR Invalid user password"
	 *		),
	 *		@OA\Response(
	 *				response=405,
	 *				description="Invalid method"
	 *		),
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
	 *        description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - sukcess , 0 - error",
	 * 				enum={"0", "1"},
	 *     	  type="integer",
	 * 		),
	 *    @OA\Property(
	 *     	  property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="object",
	 *   			@OA\Property(property="token", type="string", minLength=40, maxLength=40),
	 *   			@OA\Property(property="name", type="string", title="1111", description="2222"),
	 *    		@OA\Property(property="lastLoginTime", type="string", format="date-time", example="2019-10-07 08:32:38"),
	 *    		@OA\Property(property="lastLogoutTime", type="string", format="date-time", example="null"),
	 *    		@OA\Property(property="language", type="string", example="pl-PL"),
	 *    		@OA\Property(
	 * 						property="userDetails",
	 * 						type="object",
	 *    				@OA\Property(property="hourFormat", type="integer"),
	 *    				@OA\Property(property="startHour", type="string"),
	 * 						@OA\Property(property="endHour", type="string"),
	 *    				@OA\Property(property="dateFormat", type="string"),
	 *    				@OA\Property(property="dateJsFormat", type="string"),
	 *    				@OA\Property(property="firstDayOfTheWeek", type="string"),
	 *    				@OA\Property(property="timeZone", type="string"),
	 *    				@OA\Property(property="currencyGroupingPattern", type="string"),
	 *    				@OA\Property(property="currencyDecimalSeparator", type="string"),
	 *    				@OA\Property(property="currencyGroupingSeparator", type="string"),
	 *    				@OA\Property(property="currencySymbolPlacement", type="string"),
	 *    				@OA\Property(property="noOfCurrencyDecimals", type="integer"),
	 *    				@OA\Property(property="truncateTrailingZeros", type="integer"),
	 * 						@OA\Property(property="currencyId", type="integer"),
	 *    				@OA\Property(property="currencyName", type="string"),
	 *    				@OA\Property(property="currencyCode", type="string"),
	 *    				@OA\Property(property="currencySymbol", type="string"),
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
			->from('w_#__mail_user')
			->where(['user_name' => $this->controller->request->get('userName'), 'status' => 1])
			->limit(1)->one($db);
		if (!$row) {
			throw new \Api\Core\Exception('Invalid data access', 401);
		}
		if (\App\Encryption::getInstance()->decrypt($row['password_t']) !== $this->controller->request->get('password')) {
			throw new \Api\Core\Exception('Invalid user password', 401);
		}
		$db->createCommand()->update('w_#__mail_user', ['login_time' => date('Y-m-d H:i:s')], ['id' => $row['id']])->execute();
		$row = $this->updateSession($row);
		$userModel = \App\User::getUserModel($row['user_id']);
		return [
			'token' => $row['token'],
			'name' => $userModel->getName(),
			'lastLoginTime' => $row['login_time'],
			'lastLogoutTime' => $row['logout_time'],
			'language' => $row['language'],
			'userDetails' => [
				'hourFormat' => $userModel->getDetail('hour_format'),
				'startHour' => $userModel->getDetail('start_hour'),
				'endHour' => $userModel->getDetail('end_hour'),
				'dateFormat' => $userModel->getDetail('date_format'),
				'dateJsFormat' => \App\Fields\Date::currentUserJSDateFormat($userModel->getDetail('date_format')),
				'firstDayOfTheWeek' => $userModel->getDetail('dayoftheweek'),
				'timeZone' => $userModel->getDetail('time_zone'),
				'currencyGroupingPattern' => $userModel->getDetail('currency_grouping_pattern'),
				'currencyDecimalSeparator' => $userModel->getDetail('currency_decimal_separator'),
				'currencyGroupingSeparator' => $userModel->getDetail('currency_grouping_separator'),
				'currencySymbolPlacement' => $userModel->getDetail('currency_symbol_placement'),
				'noOfCurrencyDecimals' => (int) $userModel->getDetail('no_of_currency_decimals'),
				'truncateTrailingZeros' => $userModel->getDetail('truncate_trailing_zeros'),
				'currencyId' => $userModel->getDetail('currency_id'),
				'currencyName' => $userModel->getDetail('currency_name'),
				'currencyCode' => $userModel->getDetail('currency_code'),
				'currencySymbol' => $userModel->getDetail('currency_symbol'),
				'convRate' => $userModel->getDetail('conv_rate'),
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
	protected function updateSession($row)
	{
		$db = \App\Db::getInstance('webservice');
		$row['token'] = hash('sha1', microtime(true) . random_int(PHP_INT_MIN, PHP_INT_MAX));
		$params = $this->controller->request->getArray('params');
		if (!empty($params['language'])) {
			$row['language'] = $params['language'];
		} elseif (empty($row['language'])) {
			$row['language'] = $this->getLanguage();
		}
		$db->createCommand()->insert('w_#__mail_session', [
			'id' => $row['token'],
			'user_id' => $row['id'],
			'created' => date('Y-m-d H:i:s'),
			'changed' => date('Y-m-d H:i:s'),
			'language' => $row['language'],
			'params' => \App\Json::encode($params),
		])->execute();
		$db->createCommand()->delete('w_#__mail_session', ['<', 'changed', date('Y-m-d H:i:s', strtotime('-1 day'))])->execute();
		return $row;
	}
}
