<?php
/**
 * Api PBX Genesys action file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\PBX;

use OpenApi\Annotations as OA;

/**
 * Api PBX Genesys action class.
 *
 * @OA\Info(
 * 		title="YetiForce API for PBX. Type: PBX",
 * 		description="",
 * 		version="0.1",
 * 		termsOfService="https://yetiforce.com/",
 *   	@OA\Contact(
 *     		email="devs@yetiforce.com",
 *     		name="Devs API Team",
 *     		url="https://yetiforce.com/"
 *   	),
 *   	@OA\License(
 *    		name="YetiForce Public License",
 *     		url="https://yetiforce.com/en/yetiforce/license"
 *		),
 * )
 * @OA\Server(
 *		url="https://gitdeveloper.yetiforce.com",
 *		description="Demo server of the development version",
 * )
 * @OA\Server(
 *		url="https://gitstable.yetiforce.com",
 *		description="Demo server of the latest stable version",
 * )
 * @OA\Schema(
 *		schema="PBX_Genesys_Error",
 *		title="Response for Genesys errors",
 *		type="object",
 *		required={"status", "description"},
 *		@OA\Property(property="status", type="integer", description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error", example=0),
 *		@OA\Property(property="description", type="string", description="Error description", example="No data"),
 * ),
 */
class Genesys extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['POST', 'PUT'];

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		\App\User::setCurrentUserId(\Users::getActiveAdminId());
	}

	/** {@inheritdoc} */
	protected function checkPermissionToModule(): void
	{
		if (!\in_array(\App\Process::$processName, ['registerInteraction', 'registerInteractionCampaign', 'updateInteraction'])) {
			throw new \Api\Core\Exception('Method Not Found', 404);
		}
	}

	/** {@inheritdoc} */
	public function updateSession(array $data = []): void
	{
	}

	/**
	 * Api PBX Genesys creating interactions method.
	 *
	 * @return void
	 *
	 * @OA\Post(
	 *		path="/webservice/PBX/Genesys/registerInteraction",
	 *		summary="PBX Genesys creating interactions",
	 *		tags={"PBX"},
	 * 		security={{"ApiKeyAuth" : {}}},
	 *		@OA\Response(response=200, description="Correct server response", @OA\JsonContent(ref="#/components/schemas/PBX_Genesys_Full_Response")),
	 *		@OA\Response(response=401, description="Invalid api key"),
	 *		@OA\Response(response=404, description="Method Not Found"),
	 *		@OA\Response(response=500, description="Error", @OA\JsonContent(ref="#/components/schemas/PBX_Genesys_Error")),
	 * ),
	 * @OA\Post(
	 *		path="/webservice/PBX/Genesys/registerInteractionCampaign",
	 *		summary="PBX Genesys creating interactions for campaign",
	 *		tags={"PBX"},
	 * 		security={{"ApiKeyAuth" : {}}},
	 *		@OA\Response(response=200, description="Correct server response", @OA\JsonContent(ref="#/components/schemas/PBX_Genesys_Simple_Response")),
	 *		@OA\Response(response=401, description="Invalid api key"),
	 *		@OA\Response(response=404, description="Method Not Found"),
	 *		@OA\Response(response=500, description="Error", @OA\JsonContent(ref="#/components/schemas/PBX_Genesys_Error")),
	 * ),
	 * @OA\Schema(
	 *		schema="PBX_Genesys_Full_Response",
	 *		title="Response for creating interactions",
	 *		type="object",
	 *		required={"status", "interactionId", "url"},
	 *		@OA\Property(property="status", type="integer", description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error", example=1),
	 *		@OA\Property(property="interactionId", type="integer", description="CRM interaction ID", example=3345),
	 *		@OA\Property(property="url", type="string", description="The full URL to call on the Genesys app", example="https://gitstable.yetiforce.com/index.php?module=Accounts&view=List"),
	 * ),
	 * @OA\Schema(
	 *		schema="PBX_Genesys_Simple_Response",
	 *		title="Response for update interactions",
	 *		type="object",
	 *		required={"status"},
	 *		@OA\Property(property="status", type="integer", description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error", example=1),
	 * ),
	 */
	public function post()
	{
		try {
			file_put_contents(__DIR__ . '/_Genesys_' . date('Y-m-d-H') . '.log', print_r([
				'datetime' => date('Y-m-d H:i:s'),
				'method' => \App\Request::getRequestMethod(),
				'REQUEST' => $_REQUEST,
			], true), FILE_APPEND);
		} catch (\Throwable $th) {
			http_response_code(500);
			$message = $th->getMessage();
			if ($th instanceof \App\Exceptions\AppException) {
				$message = $th->getDisplayMessage();
			}
			echo $message;
		}
	}

	/**
	 * Api PBX Genesys update interactions method.
	 *
	 * @return void
	 *
	 * @OA\Put(
	 *		path="/webservice/PBX/Genesys/updateInteraction",
	 *		summary="PBX Genesys interaction update",
	 *		tags={"PBX"},
	 * 		security={{"ApiKeyAuth" : {}}},
	 *		@OA\Response(response=200, description="Correct server response", @OA\JsonContent(ref="#/components/schemas/PBX_Genesys_Simple_Response")),
	 *		@OA\Response(response=401, description="Invalid api key"),
	 *		@OA\Response(response=404, description="Method Not Found"),
	 *		@OA\Response(response=500, description="Error", @OA\JsonContent(ref="#/components/schemas/PBX_Genesys_Error")),
	 * ),
	 */
	public function put()
	{
		try {
			file_put_contents(__DIR__ . '/_Genesys_' . date('Y-m-d-H') . '.log', print_r([
				'datetime' => date('Y-m-d H:i:s'),
				'method' => \App\Request::getRequestMethod(),
				'REQUEST' => $_REQUEST,
			], true), FILE_APPEND);
		} catch (\Throwable $th) {
			http_response_code($th->getCode());
			$message = $th->getMessage();
			if ($th instanceof \App\Exceptions\AppException) {
				$message = $th->getDisplayMessage();
			}
			echo $message;
		}
	}
}
