<?php
/**
 * Webservice premium container - Get elements of menu file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseAction;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Get elements of menu class.
 *
 *	@OA\Info(
 * 		title="YetiForce API for Webservice App. Type: WebservicePremium",
 * 		description="Skip the `/webservice` fragment for connections via ApiProxy. There are two ways to connect to API, with or without rewrite, below are examples of both:
 * rewrite
 * - __CRM_URL__/webservice/WebservicePremium/Users/Login
 * - __CRM_URL__/webservice/WebservicePremium/Accounts/RecordRelatedList/117/Contacts
 * without rewrite
 * - __CRM_URL__/webservice.php?_container=WebservicePremium&module=Users&action=Login
 * - __CRM_URL__/webservice.php?_container=WebservicePremium&module=Accounts&action=RecordRelatedList&record=117&param=Contacts",
 * 		version="0.2",
 *   	termsOfService="https://yetiforce.com/",
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
 *	)
 *	@OA\Tag(
 *		name="BaseModule",
 *		description="Access to record methods"
 *	)
 *	@OA\Tag(
 *		name="BaseAction",
 *		description="Access to user methods"
 *	)
 *	@OA\Tag(
 *		name="Products",
 *		description="Products methods"
 * )
 *	@OA\Tag(
 *		name="Users",
 *		description="Access to user methods"
 *	)
 */
class Files extends \Api\WebserviceStandard\BaseAction\Files
{
	/**
	 * Put method.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return \App\Fields\File
	 *
	 * @OA\Put(
	 *		path="/webservice/WebservicePremium/Files",
	 *		description="Download files from the system",
	 *		summary="Download files",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Action parameters to download the file",
	 *			@OA\MediaType(
	 *				mediaType="application/x-www-form-urlencoded",
	 *				@OA\Schema(ref="#/components/schemas/BaseAction_Put_Files_Request")
	 *			),
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Put_Files_Request"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Put_Files_Request"),
	 *		),
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
	 * 		schema="BaseAction_Put_Files_Request",
	 * 		title="Base action - Files request schema",
	 * 		description="Action parameters to download the file",
	 *		type="object",
	 *		required={"module", "actionName", "record"},
	 *		@OA\Property(property="module", type="string", description="Module name", example="Contacts"),
	 *		@OA\Property(property="actionName", type="string", enum={"MultiImage", "DownloadFile"}, description="Action name",  example="MultiImage"),
	 *		@OA\Property(property="record", type="integer", description="Record ID",  example=123),
	 *		@OA\Property(property="field", type="string", description="Field name. Required for MultiImage action", example="imagename"),
	 *		@OA\Property(property="key", type="string", description="Unique key for attachment. Required for MultiImage action", example="14f01c4ea4da107c4145f0519ea1b9027fb24aa7MS2AqcUFuC")
	 * ),
	 */
	public function put()
	{
		return parent::put();
	}
}
