<?php
/**
 * Portal container - Get elements of menu file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\RestApi\BaseAction;

use OpenApi\Annotations as OA;

/**
 * Portal container - Get elements of menu class.
 *
 * @OA\Info(
 * 		title="YetiForce API for Webservice App. Type: Portal",
 * 		version="0.1",
 *   	@OA\Contact(
 *     		email="devs@yetiforce.com",
 *     		name="Devs API Team",
 *     		url="https://yetiforce.com/"
 *   	),
 *   	@OA\License(
 *    		name="YetiForce Public License v3",
 *     		url="https://yetiforce.com/en/yetiforce/license"
 *   	),
 *   	termsOfService="https://yetiforce.com/"
 * )
 */
class Files extends \Api\RestApi\BaseAction\Files
{
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
	 * 		title="Base action - Files request schema",
	 * 		description="Action parameters to download the file",
	 *		type="object",
	 *		@OA\Property(property="module", type="string", example="Documents"),
	 *		@OA\Property(property="actionName", type="string", example="DownloadFile"),
	 *		@OA\Property(property="record", type="integer", example=1111),
	 *		@OA\Property(property="fileid", type="integer", example=333),
	 * ),
	 */
	public function put()
	{
		return parent::put();
	}
}
