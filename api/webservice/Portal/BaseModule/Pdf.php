<?php
/**
 * Portal container - Pdf action file.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Portal container - Pdf action class.
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
		if (!\Api\Portal\Privilege::isPermitted($moduleName, 'ExportPdf')) {
			throw new \Api\Core\Exception("No permissions for action ExportPdf in {$moduleName} module", 405);
		}
		$recordModel = \Vtiger_Record_Model::getInstanceById($this->controller->request->getInteger('record'), $moduleName);
		if (!$recordModel->isViewable()) {
			throw new \Api\Core\Exception('No permissions to view record', 403);
		}
	}

	/**
	 * Get method - Generate PDF.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/Portal/{moduleName}/Pdf/{recordId}",
	 *		summary="Generate PDF",
	 *		description="Generate and download a PDF file from a template",
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
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Pdf_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Pdf_ResponseBody"),
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
	 *		schema="BaseAction_Pdf_ResponseBody",
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
			$template = \Vtiger_PDF_Model::getInstanceById($templateId);
			if (!$template || !$template->isVisible('Detail') || !$template->checkFiltersForRecord($recordId) || !$template->checkUserPermissions()) {
				continue;
			}
			$template->setVariable('recordId', $recordId);
			$pdf = new \App\Pdf\YetiForcePDF();
			$pdf->setPageSize($template->getFormat(), $template->getOrientation())
				->setWatermark($pdf->getTemplateWatermark($template))
				->setFileName($template->parseVariables($template->get('filename')))
				->parseParams($template->getParameters())
				->loadHtml($template->parseVariables($template->getBody()))
				->setHeader($template->parseVariables($template->getHeader()))
				->setFooter($template->parseVariables($template->getFooter()));

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
