<?php
/**
 * Pdf file.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

/**
 * Pdf class.
 */
class Pdf extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Check permission to method.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	public function checkPermission()
	{
		$result = parent::checkPermission();
		$moduleName = $this->controller->request->getModule();
		if (!\Api\Portal\Privilege::isPermitted($moduleName, 'ExportPdf')) {
			throw new \Api\Core\Exception("No permissions for action {$moduleName}:ExportPdf", 405);
		}
		return $result;
	}

	/**
	 * Get method.
	 *
	 * @return array
	 */
	public function get()
	{
		$file = $pdfFiles = $increment = [];
		$recordId = $this->controller->request->getInteger('record');
		foreach ($this->controller->request->getArray('templates') as $templateId) {
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

			$fileName = ($pdf->getFileName() ? $pdf->getFileName() : time());
			$increment[$fileName] = $increment[$fileName] ?? 0;
			$fileName .= ($increment[$fileName]++ > 0 ? '_' . $increment[$fileName] : '') . '.pdf';

			$filePath = $template->getPath();
			$pdfFiles[] = ['path' => $filePath,	'name' => $fileName];
			$pdf->output($filePath, 'F');
		}
		if (count($pdfFiles) > 1) {
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
