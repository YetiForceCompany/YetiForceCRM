<?php
/**
 * Webservice premium container - Users Login action file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\Users;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Users Login action class.
 */
class Login extends \Api\WebserviceStandard\Users\Login
{
	/**
	 * Post method.
	 *
	 * @return array|null
	 *
	 *	@OA\Post(
	 *		path="/webservice/WebservicePremium/Users/Login",
	 *		description="Authenticate into the system",
	 *		summary="Login",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *  		description="Input data format",
	 *    		@OA\JsonContent(ref="#/components/schemas/Users_Post_Login_Request"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Post_Login_Request")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Post_Login_Request")
	 *     		),
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="User details",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Post_Login_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Post_Login_Response")
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="`Invalid data access` OR `Invalid user password` OR `No crmid` OR `2FA verification error`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=412,
	 *			description="No 2FA TOTP code",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Post_Login_Request",
	 * 		title="Users module - Users login request body",
	 * 		description="JSON or form-data",
	 *		type="object",
	 *		required={"userName", "password"},
	 *  	@OA\Property(property="userName", description="Webservice user name", type="string"),
	 *  	@OA\Property(property="password", description="Webservice user password", type="string"),
	 *  	@OA\Property(property="code", description="2FA TOTP code (optional property), Pass code length = 6, Code period = 30", type="string"),
	 *		@OA\Property(
	 *       	property="params",
	 *       	description="Additional parameters sent by the user, extending the current settings, e.g. language",
	 *       	type="object",
	 *       	@OA\Property(property="version", type="string", description="Portal version", example="1.1"),
	 *       	@OA\Property(property="language", type="string", description="Portal language", example="pl-PL"),
	 *       	@OA\Property(property="ip", type="string", description="Portal user IP", example="127.0.0.1"),
	 *       	@OA\Property(property="fromUrl", type="string", description="Portal URL", example="https://gitdevportal.yetiforce.com/"),
	 *       	@OA\Property(property="deviceId", type="string", description="Portal user device ID", example="d520c7a8-421b-4563-b955-f5abc56b97ec"),
	 *		)
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Post_Login_Response",
	 * 		title="Users module - Users login response body",
	 * 		description="Users login response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *     		property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="object",
	 *    	 	required={"token", "name", "lastLoginTime", "lastLogoutTime", "language", "type", "login_method", "preferences"},
	 *   		@OA\Property(property="token", type="string", minLength=40, maxLength=40),
	 *   		@OA\Property(property="name", type="string"),
	 *    		@OA\Property(property="lastLoginTime", type="string", example="2019-10-07 08:32:38"),
	 *    		@OA\Property(property="lastLogoutTime", type="string", example=""),
	 *    		@OA\Property(property="language", type="string", example="pl-PL"),
	 *    		@OA\Property(property="type", type="integer"),
	 *    		@OA\Property(property="login_method", type="string", enum={"PLL_PASSWORD", "PLL_PASSWORD_2FA"}, example="PLL_PASSWORD_2FA"),
	 *    		@OA\Property(property="logged", type="boolean"),
	 *    		@OA\Property(
	 * 				property="preferences",
	 * 				type="object",
	 * 				required={"hour_format", "start_hour", "end_hour", "date_format", "time_zone", "raw_time_zone", "currency_id", "currency_grouping_pattern", "currency_decimal_separator", "currency_grouping_separator", "currency_symbol_placement", "no_of_currency_decimals", "currency_name", "currency_code", "currency_symbol", "conv_rate"},
	 *    			@OA\Property(property="hour_format", type="string", example="24"),
	 *    			@OA\Property(property="start_hour", type="string", example="08:00"),
	 *    			@OA\Property(property="end_hour", type="string", example="16:00"),
	 *    			@OA\Property(property="date_format", type="string", example="yyyy-mm-dd"),
	 *    			@OA\Property(property="time_zone", type="string", example="Europe/Warsaw", description="User time zone, all data you will be in this time zone"),
	 *    			@OA\Property(property="raw_time_zone", type="string", example="UTC", description="System main time zone (data in database format)"),
	 *    			@OA\Property(property="currency_id", type="integer", example=1),
	 *    			@OA\Property(property="currency_grouping_pattern", type="string", example="123,456,789"),
	 *    			@OA\Property(property="currency_decimal_separator", type="string", example="."),
	 *    			@OA\Property(property="currency_grouping_separator", type="string", example=" "),
	 *    			@OA\Property(property="currency_symbol_placement", type="string", example="1.0$"),
	 *    			@OA\Property(property="no_of_currency_decimals", type="integer", example=2),
	 *    			@OA\Property(property="currency_name", type="string", example="Poland, Zlotych"),
	 *    			@OA\Property(property="currency_code", type="string", example="PLN"),
	 *    			@OA\Property(property="currency_symbol", type="string", example="zł"),
	 *    			@OA\Property(property="conv_rate", type="number", format="float", example=1.00000),
	 *    			@OA\Property(property="activity_view", type="string", example="This Month"),
	 *    			@OA\Property(property="date_format_js", type="string", example="Y-m-d"),
	 *    			@OA\Property(property="dayoftheweek", type="string", example="Monday"),
	 *    			@OA\Property(property="truncate_trailing_zeros", type="integer", example=0),
	 * 			),
	 *    		@OA\Property(property="authy_methods", type="string", enum={"", "PLL_AUTHY_TOTP"}, example="PLL_AUTHY_TOTP"),
	 *    		@OA\Property(property="2faObligatory", type="boolean", example=true),
	 *    		@OA\Property(property="userPreferences", type="object", description="User preferences", example={"menuPin" : 1}),
	 *    		@OA\Property(property="companyId", type="integer"),
	 *    		@OA\Property(property="parentName", type="string", example="YetiForce Company"),
	 *    		@OA\Property(
	 * 				property="companyDetails",
	 * 				type="object",
	 * 				title="Company details, optional parameter depending on the user type",
	 * 				required={"check_stock_levels", "sum_open_orders"},
	 *  			@OA\Property(property="check_stock_levels", type="boolean"),
	 * 				@OA\Property(property="sum_open_orders", type="number", format="double"),
	 * 				@OA\Property(property="creditlimit", type="integer")
	 * 			),
	 *    ),
	 *	),
	 */
	public function post(): ?array
	{
		return parent::post();
	}

	/** {@inheritdoc}  */
	protected function saveData(): void
	{
		$params = $this->controller->request->getArray('params');
		$this->updateUser([
			'login_time' => date(static::DATE_TIME_FORMAT),
			'custom_params' => [
				'deviceId' => $params['deviceId'] ?? '',
			],
		]);
		$this->createSession();
		$this->saveLoginHistory([
			'status' => 'LBL_SIGNED_IN',
			'device_id' => $params['deviceId'] ?? '',
		]);
	}

	/** {@inheritdoc}  */
	protected function returnData(): array
	{
		$data = parent::returnData();
		$data['userPreferences'] = $this->getUserData('preferences');
		$parentId = \Api\WebservicePremium\Privilege::USER_PERMISSIONS !== $this->getPermissionType() ? \App\Record::getParentRecord($this->getUserCrmId()) : 0;
		if (!empty($parentId)) {
			$parentRecordModel = \Vtiger_Record_Model::getInstanceById($parentId, 'Accounts');
			$data['companyId'] = $parentId;
			$data['parentName'] = \App\Purifier::decodeHtml($parentRecordModel->getName());
			$companyDetails = [];
			$companyDetails['check_stock_levels'] = (bool) $parentRecordModel->get('check_stock_levels');
			$companyDetails['sum_open_orders'] = $parentRecordModel->get('sum_open_orders');
			$creditLimitId = $parentRecordModel->get('creditlimit');
			if (!empty($creditLimitId)) {
				$limits = \Vtiger_InventoryLimit_UIType::getLimits();
				$companyDetails['creditlimit'] = $limits[$creditLimitId]['value'] ?? 0;
			}
			$data['companyDetails'] = $companyDetails;
		}
		$userModel = \App\User::getUserModel($this->getUserData('user_id'));
		$data['preferences'] += [
			'activity_view' => $userModel->getDetail('activity_view'),
			'date_format_js' => \App\Fields\Date::currentUserJSDateFormat($userModel->getDetail('date_format')),
			'dayoftheweek' => $userModel->getDetail('dayoftheweek'),
			'truncate_trailing_zeros' => $userModel->getDetail('truncate_trailing_zeros'),
		];
		return $data;
	}
}
