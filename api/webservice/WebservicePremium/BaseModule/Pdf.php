<?php
/**
 * Webservice premium container - Generates and downloads a PDF file from a template file.
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
 * Webservice premium container - Generates and downloads a PDF file from a template class.
 */
class Pdf extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		parent::checkPermission();
		if ($this->controller->request->isEmpty('record')) {
			throw new \Api\Core\Exception('No record id', 404);
		}
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
	 * Get method - Generates and downloads a PDF file from a template.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebservicePremium/{moduleName}/Pdf/{recordId}",
	 *		summary="Generates and downloads a PDF file from a template",
	 *		description="Get PDF file by template",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Accounts"),
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Parameter(name="templates", in="query", description="Pdf templates ids", required=true,
	 *			@OA\JsonContent(type="integer", description="Pdf templates ids"),
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Generate PDF",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_Pdf_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_Pdf_Response"),
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
	 *		schema="BaseModule_Get_Pdf_Response",
	 *		title="Base module - Generate PDF response schema",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Pdf",
	 *			type="object",
	 *			@OA\AdditionalProperties(
	 *				title="Pdf detail",
	 *				type="object",
	 * 				@OA\Property(property="name", type="string", example="order.pdf"),
	 * 				@OA\Property(property="data", type="string", format="binary"),
	 * 			),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		$file = $pdfFiles = $increment = [];
		$recordId = $this->controller->request->getInteger('record');
		foreach ($this->controller->request->getArray('templates', 'Integer') as $templateId) {
			$pdf = \App\Pdf\Pdf::getInstanceByTemplateId($templateId);
			$template = $pdf->getTemplate();
			if (!$template || !$template->isVisible('Detail') || !$template->checkFiltersForRecord($recordId) || !$template->checkUserPermissions()) {
				continue;
			}
			$template->setVariable('recordId', $recordId);
			$pdf->loadTemplateData();

			$fileName = ($pdf->getFileName() ?: time());
			$increment[$fileName] = $increment[$fileName] ?? 0;
			$fileName .= ($increment[$fileName]++ > 0 ? '_' . $increment[$fileName] : '') . '.pdf';

			$filePath = $template->getPath();
			$pdfFiles[] = ['path' => $filePath,	'name' => $fileName];
			$pdf->output($filePath, 'F');
		}
		if (\count($pdfFiles) > 1) {
			$zipPath = $template->getPath('APIZIP');
			$zip = \App\Zip::createFile($zipPath);
			foreach ($pdfFiles as $file) {
				$zip->addFile($file['path'], $file['name']);
			}
			$zip->close();
			$file = ['name' => 'PdfZipFile_' . time() . '.zip', 'data' => base64_encode(file_get_contents($zipPath))];
			unlink($zipPath);
		} elseif ($pdfFiles) {
			$file = current($pdfFiles);
			$file = ['name' => $file['name'], 'data' => base64_encode(file_get_contents($file['path']))];
		}
		foreach ($pdfFiles as $pdfFile) {
			unlink($pdfFile['path']);
		}
		return $file;
	}
}
