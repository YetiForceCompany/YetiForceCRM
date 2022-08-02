<?php
/**
 * Webservice premium container - Gets a list of  PDF templates file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Gets a list of  PDF templates class.
 */
class PdfTemplates extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		parent::checkPermission();
		$moduleName = $this->controller->request->getModule();
		if (!\App\Record::isExists($this->controller->request->getInteger('record'), $moduleName)) {
			throw new \Api\Core\Exception('Record doesn\'t exist', 404);
		}
		if (!\Api\WebservicePremium\Privilege::isPermitted($moduleName, 'ExportPdf')) {
			throw new \Api\Core\Exception("No permissions for action ExportPdf in {$moduleName} module", 405);
		}
		$recordModel = \Vtiger_Record_Model::getInstanceById($this->controller->request->getInteger('record'), $moduleName);
		if (!$recordModel->isViewable()) {
			throw new \Api\Core\Exception('No permissions to view record', 403);
		}
	}

	/**
	 * Get method - Gets a list of  PDF templates.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebservicePremium/{moduleName}/PdfTemplates/{recordId}",
	 *		summary="Gets a list of  PDF templates",
	 *		description="PDF templates",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Accounts"),
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Gets a list of  PDF templates",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_PdfTemplates_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_PdfTemplates_Response"),
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
	 *		schema="BaseModule_Get_PdfTemplates_Response",
	 *		title="Base module - Get PDF templates list response schema",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Pdf templates",
	 *			type="object",
	 *			@OA\AdditionalProperties(
	 *				title="Pdf template detail",
	 *				type="object",
	 *				@OA\Property(property="id", title="Record Id", type="integer", example=38),
	 * 				@OA\Property(property="name", type="string", example="order"),
	 * 				@OA\Property(property="second_name", type="string", example="order"),
	 * 				@OA\Property(property="default", type="integer", example=1),
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
				'default' => $template->get('default'),
			];
		}
		return $templatesData;
	}
}
