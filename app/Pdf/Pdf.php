<?php

/**
 * Primary file for generating PDF files.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Pdf;

/**
 * Primary class for generating PDF files.
 */
class Pdf
{
	/**
	 * Get page formats.
	 *
	 * @return string[]
	 */
	public static function getPageFormats(): array
	{
		return [
			'4A0',
			'2A0',
			'A0', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'A9', 'A10',
			'B0', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7', 'B8', 'B9', 'B10',
			'C0', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9', 'C10',
			'RA0', 'RA1', 'RA2', 'RA3', 'RA4',
			'SRA0', 'SRA1', 'SRA2', 'SRA3', 'SRA4',
			'LETTER',
			'LEGAL',
			'LEDGER',
			'TABLOID',
			'EXECUTIVE',
			'FOLIO',
			'B', //	'B' format paperback size 128x198mm
			'A', //	'A' format paperback size 111x178mm
			'DEMY', //	'Demy' format paperback size 135x216mm
			'ROYAL', //	'Royal' format paperback size 153x234mm
		];
	}

	/**
	 * Get supported drivers for generating PDF.
	 *
	 * @return string[]
	 */
	public static function getSupportedDrivers(): array
	{
		$drivers = [];
		foreach ((new \DirectoryIterator(__DIR__ . \DIRECTORY_SEPARATOR . 'Drivers')) as $fileInfo) {
			$fileName = $fileInfo->getBasename('.php');
			if ('Base' !== $fileName && 'php' === $fileInfo->getExtension()) {
				$className = '\App\Pdf\Drivers\\' . $fileName;
				if (!class_exists($className)) {
					\App\Log::warning('Not found custom class: ' . $className);
					continue;
				}
				if ($className::isActive()) {
					$drivers[$fileName] = $className::DRIVER_NAME;
				}
			}
		}
		return $drivers;
	}

	/**
	 * Get driver label.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getDriverLabel(string $name): string
	{
		$className = '\App\Pdf\Drivers\\' . $name;
		$label = $name;
		if (class_exists($className)) {
			$label = \App\Language::translate($className::DRIVER_NAME, 'Settings::PDF');
		}
		return $label;
	}

	/**
	 * Get PDF instance by template id.
	 *
	 * @param int $templateId
	 *
	 * @return Drivers\Base
	 */
	public static function getInstanceByTemplateId(int $templateId): Drivers\Base
	{
		$template = \Vtiger_PDF_Model::getInstanceById($templateId);
		$className = '\App\Pdf\Drivers\\' . $template->get('generator');
		$pdf = new $className();
		$pdf->setTemplate($template);
		return $pdf;
	}

	/**
	 * Function that merges PDF files into one.
	 *
	 * @param string[] $files   List of files to merge
	 * @param string   $path    File name or path to write
	 * @param string   $pdfFlag Merge mode e.g. `I` = show in browser , `D` = download , `F` = save to file
	 *
	 * @return void
	 */
	public static function merge(array $files, string $path, string $pdfFlag): void
	{
		$merger = new \setasign\Fpdi\Fpdi();
		foreach ($files as $file) {
			$pageCount = $merger->setSourceFile($file);
			for ($i = 1; $i <= $pageCount; ++$i) {
				$template = $merger->importPage($i);
				$size = $merger->getTemplateSize($template);
				$merger->AddPage(($size['width'] > $size['height']) ? 'L' : 'P', [$size['width'], $size['height']]);
				$merger->useTemplate($template);
			}
		}
		$merger->Output($path, $pdfFlag);
	}
}
