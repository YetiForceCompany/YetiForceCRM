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
 * @OA\Info(
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
 *   	@OA\Contact(email="devs@yetiforce.com", name="Devs API Team", url="https://yetiforce.com/"),
 *   	@OA\License(name="YetiForce Public License", url="https://yetiforce.com/en/yetiforce/license"),
 * )
 * 	@OA\ExternalDocumentation(
 *		description="Platform API Interactive Docs",
 *		url="https://doc.yetiforce.com/api/?urls.primaryName=Webservice%20Standard"
 * 	),
 * 	@OA\Server(description="Demo server of the development version", url="https://gitdeveloper.yetiforce.com")
 * 	@OA\Server(description="Demo server of the latest stable version", url="https://gitstable.yetiforce.com")
 * 	@OA\Tag(name="BaseModule", description="Access to record methods", x={"displayName" : "Base module"})
 * 	@OA\Tag(name="BaseAction", description="Access to user methods", x={"displayName" : "Base actions"})
 * 	@OA\Tag(name="Users", description="Access to user methods", x={"displayName" : "Users"})
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
 *	@OA\SecurityScheme(
 * 		name="X-TOKEN",
 *   	type="apiKey",
 *   	in="header",
 *		securityScheme="token",
 *   	description="Webservice api token by user header"
 * 	),
 *	@OA\Schema(
 *		schema="Header-Encrypted",
 *		type="integer",
 *		title="Header - Encrypted",
 *  	description="Is the content request is encrypted",
 *  	enum={0, 1},
 *   	default=0
 *	),
 *	@OA\Schema(
 *		schema="Exception",
 *		title="General - Error exception",
 *		type="object",
 *		required={"status", "error"},
 *		@OA\Property(property="status", type="integer", enum={0}, title="0 - error", example=0),
 *		@OA\Property(
 * 			property="error",
 *     	 	description="Error  details",
 *    	 	type="object",
 *    	 	required={"message", "code"},
 *   		@OA\Property(property="message", type="string", example="Invalid method", description="To show more details turn on: config\Debug.php apiShowExceptionMessages = true"),
 *   		@OA\Property(property="code", type="integer", example=405),
 *   		@OA\Property(property="file", type="string", example="api\webservice\WebserviceStandard\BaseAction\Files.php", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
 *   		@OA\Property(property="line", type="integer", example=101, description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
 *   		@OA\Property(property="previous", type="object", description="Previous exception"),
 * 			@OA\Property(property="backtrace", type="string", example="#0 api\webservice\WebserviceStandard\BaseAction\Files.php (101) ....", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
 *    	),
 *	),
 *	@OA\Schema(
 *		schema="Conditions-Mix-For-Query-Generator",
 *		type="object",
 *		title="General - Mix conditions for query generator",
 *  	description="Multiple or one condition for a query generator",
 *		oneOf={
 *			@OA\Schema(ref="#/components/schemas/Condition-For-Query-Generator"),
 *			@OA\Schema(ref="#/components/schemas/Conditions-For-Query-Generator"),
 *		}
 *	),
 *	@OA\Schema(
 *		schema="Condition-For-Query-Generator",
 *		type="object",
 *		title="General - Condition for query generator",
 *  	description="One condition for query generator",
 *  	required={"fieldName", "value", "operator"},
 *		@OA\Property(property="fieldName", description="Field name", type="string", example="lastname"),
 *		@OA\Property(property="value", description="Search value", type="string", example="Kowalski"),
 *		@OA\Property(property="operator", description="Field operator", type="string", example="e"),
 *		@OA\Property(property="group", description="Condition group if true is AND", type="boolean", example=true),
 *	),
 *	@OA\Schema(
 *		schema="Conditions-For-Query-Generator",
 *		type="object",
 *		title="General - Conditions for query generator",
 *  	description="Multiple conditions for query generator",
 *		@OA\AdditionalProperties(
 *			description="Condition details",
 *			type="object",
 *			@OA\Schema(ref="#/components/schemas/Condition-For-Query-Generator"),
 *		),
 *	),
 *	@OA\Schema(
 *		schema="Conditions-For-Native-Query",
 *		type="object",
 *		title="General - Conditions for native query",
 *  	description="Conditions for native query, based on YII 2",
 *		example={"column_name1" : "searched value 1", "column_name2" : "searched value 2"},
 *		@OA\ExternalDocumentation(
 *			description="Database communication engine",
 *			url="https://doc.yetiforce.com/developer-guides/system-components/databases"
 *		),
 *	),
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
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
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
