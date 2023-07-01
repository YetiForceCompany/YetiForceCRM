<?php
/**
 * Api Token file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Token;

use OpenApi\Annotations as OA;

/**
 * Api Token class.
 *
 * @OA\Info(
 * 		title="YetiForce API for Token. Type: Token",
 * 		description="",
 * 		version="0.1",
 * 		termsOfService="https://yetiforce.com/",
 *   	@OA\Contact(email="devs@yetiforce.com", name="Devs API Team", url="https://yetiforce.com/"),
 *   	@OA\License(name="YetiForce Public License", url="https://yetiforce.com/en/yetiforce/license"),
 * )
 *	@OA\ExternalDocumentation(
 *		description="Platform API Interactive Docs",
 *		url="https://doc.yetiforce.com/api/?urls.primaryName=Token"
 *	),
 * @OA\Server(description="Demo server of the development version", url="https://gitdeveloper.yetiforce.com")
 * @OA\Server(description="Demo server of the latest stable version", url="https://gitstable.yetiforce.com")
 */
class Action extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		\App\User::setCurrentUserId(\Users::getActiveAdminId());
	}

	/** {@inheritdoc} */
	public function updateSession(array $data = []): void
	{
	}

	/**
	 * Token support method.
	 *
	 * @return void
	 *
	 * @OA\Get(
	 *		path="/webservice/Token/{token}",
	 *		summary="Token support",
	 *		@OA\Parameter(name="token", in="path", @OA\Schema(type="string"), description="Token", required=true, example="vrm6bcA3fp2J0RB96AvDCAVsKv1MaMVbGQ1QbFtS5jr0DSXvUvc3ec08fMhCk7kd"),
	 *		@OA\Response(response=200, description="Result", @OA\JsonContent(ref="#/components/schemas/Token_Get_Report")),
	 *		@OA\Response(response=404, description="No token"),
	 *		@OA\Response(response=405, description="The token does not exist or has expired"),
	 * ),
	 * @OA\Schema(
	 *		schema="Token_Get_Report",
	 *		type="string",
	 *		title="Response",
	 *		description="Response",
	 *		example="OK"
	 *	),
	 */
	public function get()
	{
		$token = \App\Process::$processName;
		try {
			$tokenData = \App\Utils\Tokens::get($token);
			if (empty($tokenData)) {
				throw new \App\Exceptions\Security('ERR_TOKEN_DOES_NOT_EXIST', 405);
			}
			$result = \call_user_func($tokenData['method'], $tokenData['params'], $this);
			if (isset($result['redirect'])) {
				header("location: {$result['redirect']}");
			}
		} catch (\Throwable $th) {
			if (is_numeric($th->getCode())) {
				http_response_code($th->getCode());
			}
			$message = $th->getMessage();
			if ($th instanceof \App\Exceptions\AppException) {
				$message = $th->getDisplayMessage();
			}
			echo $message;
		}
	}
}
