<?php
/**
 * Get elements of menu.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal\BaseAction;

use OpenApi\Annotations as OA;

/**
 * Action to get menu.
 */
class Files extends \Api\Core\BaseAction
{
	/**
	 * {@inheritdoc}
	 */
	public $allowedMethod = ['PUT'];
	/**
	 * {@inheritdoc}
	 */
	public $responseType = 'file';

	/**
	 * Put method.
	 *
	 * @return \App\Fields\File
	 *
	 * @OA\Put(
	 *		path="/webservice/Files",
	 *		summary="Download files from the system",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *    	},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *			description="Action parameters to download the file",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Files_Request"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Files_Request"),
	 *	  	),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="File content, mediaType is dynamic depending on the type of file being downloaded",
	 * 			@OA\MediaType(
	 *				mediaType="application/octet-stream",
	 * 				@OA\Schema(
	 *					type="string",
	 *					format="binary"
	 *				)
	 * 			)
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="No permissions",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="File not found",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=406,
	 *			description="Not Acceptable",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 * 		schema="BaseAction_Files_Request",
	 * 		title="Base action - Files request",
	 * 		description="Action parameters to download the file",
	 *		type="object",
	 *		example={"module" : "Documents", "actionName" : "DownloadFile", "record" : 1111, "fileid" : 333},
	 * ),
	 */
	public function put()
	{
		$moduleName = $this->controller->request->getModule();
		$action = $this->controller->request->getByType('actionName', 1);
		if (!$moduleName || !$action) {
			throw new \Api\Core\Exception('Invalid method', 405);
		}
		\App\Process::$processName = $action;
		\App\Process::$processType = 'File';
		$handlerClass = \Vtiger_Loader::getComponentClassName('File', $action, $moduleName);
		$handler = new $handlerClass();
		if ($handler) {
			if (!$handler->getCheckPermission($this->controller->request)) {
				throw new \Api\Core\Exception('No permissions', 403);
			}
			return $handler->api($this->controller->request);
		}
		throw new \Api\Core\Exception('Invalid method', 405);
	}
}
