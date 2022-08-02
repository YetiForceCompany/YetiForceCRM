<?php
/**
 * Webservice standard container - Get elements of menu file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebserviceStandard\BaseAction;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container - Get elements of menu class.
 *
 *	@OA\Info(
 * 		title="YetiForce API for Webservice App. Type: WebserviceStandard",
 * 		description="Skip the `/webservice` fragment for connections via ApiProxy. There are two ways to connect to API, with or without rewrite, below are examples of both:
 * rewrite
 * - __CRM_URL__/webservice/WebserviceStandard/Users/Login
 * - __CRM_URL__/webservice/WebserviceStandard/Accounts/RecordRelatedList/117/Contacts
 * without rewrite
 * - __CRM_URL__/webservice.php?_container=WebserviceStandard&module=Users&action=Login
 * - __CRM_URL__/webservice.php?_container=WebserviceStandard&module=Accounts&action=RecordRelatedList&record=117&param=Contacts",
 * 		version="0.2",
 * 		termsOfService="https://yetiforce.com/",
 *   	@OA\Contact(
 *     		email="devs@yetiforce.com",
 *     		name="Devs API Team",
 *     		url="https://yetiforce.com/"
 *   	),
 *   	@OA\License(
 *    		name="YetiForce Public License",
 *     		url="https://yetiforce.com/en/yetiforce/license"
 *   	),
 *	)
 *	@OA\Server(
 *		url="https://gitdeveloper.yetiforce.com",
 *		description="Demo server of the development version",
 *	)
 *	@OA\Server(
 *		url="https://gitstable.yetiforce.com",
 *		description="Demo server of the latest stable version",
 * 	)
 *	@OA\Tag(
 *		name="BaseModule",
 *		description="Access to record methods"
 *	)
 *	@OA\Tag(
 *		name="BaseAction",
 *		description="Access to user methods"
 *	)
 *	@OA\Tag(
 *		name="Users",
 *		description="Access to user methods"
 *	)
 */
class Files extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['PUT'];

	/** {@inheritdoc}  */
	public $responseType = 'file';

	/**
	 * Put method.
	 *
	 * @api
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return \App\Fields\File
	 * @OA\Put(
	 *		path="/webservice/WebserviceStandard/Files",
	 *		description="Download files from the system",
	 *		summary="Download files",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *    	},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *			description="Action parameters to download the file",
	 * 			@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/BaseAction_Files_Request")
	 *     		),
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Files_Request"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Files_Request"),
	 *	  	),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
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
	 * 		title="Base action - Files request schema",
	 * 		description="Action parameters to download the file",
	 *		type="object",
	 * 		required={"module", "actionName", "record"},
	 *		@OA\Property(property="module", type="string", description="Module name", example="Contacts"),
	 *		@OA\Property(property="actionName", type="string", enum={"MultiImage", "DownloadFile"}, description="Action name",  example="MultiImage"),
	 *		@OA\Property(property="record", type="integer", description="Record ID",  example=123),
	 *		@OA\Property(property="field", type="string", description="Field name. Required for MultiImage action", example="imagename"),
	 *		@OA\Property(property="key", type="string", description="Unique key for attachment. Required for MultiImage action", example="14f01c4ea4da107c4145f0519ea1b9027fb24aa7MS2AqcUFuC")
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
