<?php
/**
 * Portal container - Get PDF templates list file.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

/**
 * Portal container - Get PDF templates list class.
 */
class PdfTemplates extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public function checkPermission(): void
	{
		parent::checkPermission();
		$moduleName = $this->controller->request->getModule();
		if (!\App\Record::isExists($this->controller->request->getInteger('record'), $moduleName)) {
			throw new \Api\Core\Exception('Record doesn\'t exist', 404);
		}
		if (!\Api\Portal\Privilege::isPermitted($moduleName, 'ExportPdf')) {
			throw new \Api\Core\Exception("No permissions for action ExportPdf in {$moduleName} module", 405);
		}
		$recordModel = \Vtiger_Record_Model::getInstanceById($this->controller->request->getInteger('record'), $moduleName);
		if (!$recordModel->isViewable()) {
			throw new \Api\Core\Exception('No permissions to view record', 403);
		}
	}

	/**
	 * Get method - Get PDF templates list.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/Portal/{moduleName}/PdfTemplates/{recordId}",
	 *		summary="Get PDF templates list",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(
	 *				type="string"
	 *			),
	 *			in="path",
	 *			example="Accounts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="recordId",
	 *			description="Record id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="Request body does not occur",
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Get PDF templates list",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_PdfTemplates_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_PdfTemplates_ResponseBody"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="No permissions to view record",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="No record id",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="No permissions for action ExportPdf in {moduleName} module",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseAction_PdfTemplates_ResponseBody",
	 *		title="Base module - Get PDF templates list response schema",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 * 			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 * 			enum={0, 1},
	 *     	  	type="integer",
	 * 			example=1
	 * 		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Pdf templates",
	 *			type="object",
	 *			@OA\AdditionalProperties(
	 *				description="Pdf template detail",
	 *				type="object",
	 *				@OA\Property(property="id", description="Record Id", type="integer", example=38),
	 * 				@OA\Property(property="name", type="string", example="order"),
	 * 				@OA\Property(property="second_name", type="string", example="order"),
	 * 				@OA\Property(property="default", type="integer", example=null),
	 * 			),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		$moduleName = $this->controller->request->getModule();
		$recordId = $this->controller->request->getInteger('record');
		$handlerClass = \Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
		$pdfModel = new $handlerClass();
		$templates = $pdfModel->getActiveTemplatesForRecord($recordId, 'Detail', $moduleName);
		$templatesData = [];
		foreach ($templates as $template) {
			$templatesData[$template->getId()] = [
				'id' => $template->getId(),
				'name' => \App\Language::translate($template->getName(), $template->get('module_name')),
				'second_name' => \App\Language::translate($template->get('secondary_name'), $template->get('module_name')),
				'default' => $template->get('default')
			];
		}
		return $templatesData;
	}
}
