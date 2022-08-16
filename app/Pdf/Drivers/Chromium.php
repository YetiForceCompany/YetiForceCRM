<?php
/**
 * Chromium driver file for PDF generation.
 *
 * @see https://github.com/chrome-php/chrome
 * @see https://www.chromium.org
 *
 * @package App\Pdf
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Pdf\Drivers;

/**
 * Chromium driver class for PDF generation.
 */
class Chromium extends Base
{
	/** {@inheritdoc} */
	const DRIVER_NAME = 'LBL_CHROMIUM_PDF';

	/** @var float Millimeter to inch converter */
	const MM_TO_IN = 0.0393701;

	/** @var float Millimeter to pixel converter */
	const MM_TO_PX = 3.7795275591;

	const PAGE_FORMATS = [
		// ISO 216 A Series + 2 SIS 014711 extensions
		'A0' => [33.11, 46.81], // = (  841 x 1189 ) mm  = ( 33.11 x 46.81 ) in
		'A1' => [23.39, 33.11], // = (  594 x 841  ) mm  = ( 23.39 x 33.11 ) in
		'A2' => [16.54, 23.39], // = (  420 x 594  ) mm  = ( 16.54 x 23.39 ) in
		'A3' => [11.69, 16.54], // = (  297 x 420  ) mm  = ( 11.69 x 16.54 ) in
		'A4' => [8.27, 11.69], // = (  210 x 297  ) mm  = (  8.27 x 11.69 ) in
		'A5' => [5.83, 8.27], // = (  148 x 210  ) mm  = (  5.83 x 8.27  ) in
		'A6' => [4.13, 5.83], // = (  105 x 148  ) mm  = (  4.13 x 5.83  ) in
		'A7' => [2.91, 4.13], // = (   74 x 105  ) mm  = (  2.91 x 4.13  ) in
		'A8' => [2.05, 2.91], // = (   52 x 74   ) mm  = (  2.05 x 2.91  ) in
		'A9' => [1.46, 2.05], // = (   37 x 52   ) mm  = (  1.46 x 2.05  ) in
		'A10' => [1.02, 1.46], // = (   26 x 37   ) mm  = (  1.02 x 1.46  ) in
		// ISO 216 B Series + 2 SIS 014711 extensions
		'B0' => [39.37, 55.67], // = ( 1000 x 1414 ) mm  = ( 39.37 x 55.67 ) in
		'B1' => [27.83, 39.37], // = (  707 x 1000 ) mm  = ( 27.83 x 39.37 ) in
		'B2' => [19.69, 27.83], // = (  500 x 707  ) mm  = ( 19.69 x 27.83 ) in
		'B3' => [13.90, 19.69], // = (  353 x 500  ) mm  = ( 13.90 x 19.69 ) in
		'B4' => [9.84, 13.90], // = (  250 x 353  ) mm  = (  9.84 x 13.90 ) in
		'B5' => [6.93, 9.84], // = (  176 x 250  ) mm  = (  6.93 x 9.84  ) in
		'B6' => [4.92, 6.93], // = (  125 x 176  ) mm  = (  4.92 x 6.93  ) in
		'B7' => [3.46, 4.92], // = (   88 x 125  ) mm  = (  3.46 x 4.92  ) in
		'B8' => [2.44, 3.46], // = (   62 x 88   ) mm  = (  2.44 x 3.46  ) in
		'B9' => [1.73, 2.44], // = (   44 x 62   ) mm  = (  1.73 x 2.44  ) in
		'B10' => [1.22, 1.73], // = (   31 x 44   ) mm  = (  1.22 x 1.73  ) in
		// ISO 216 C Series + 2 SIS 014711 extensions + 5 EXTENSION
		'C0' => [36.10, 51.06], // = (  917 x 1297 ) mm  = ( 36.10 x 51.06 ) in
		'C1' => [25.51, 36.10], // = (  648 x 917  ) mm  = ( 25.51 x 36.10 ) in
		'C2' => [18.03, 25.51], // = (  458 x 648  ) mm  = ( 18.03 x 25.51 ) in
		'C3' => [12.76, 18.03], // = (  324 x 458  ) mm  = ( 12.76 x 18.03 ) in
		'C4' => [9.02, 12.76], // = (  229 x 324  ) mm  = (  9.02 x 12.76 ) in
		'C5' => [6.38, 9.02], // = (  162 x 229  ) mm  = (  6.38 x 9.02  ) in
		'C6' => [4.49, 6.38], // = (  114 x 162  ) mm  = (  4.49 x 6.38  ) in
		'C7' => [3.19, 4.49], // = (   81 x 114  ) mm  = (  3.19 x 4.49  ) in
		'C8' => [2.24, 3.19], // = (   57 x 81   ) mm  = (  2.24 x 3.19  ) in
		'C9' => [1.57, 2.24], // = (   40 x 57   ) mm  = (  1.57 x 2.24  ) in
		'C10' => [1.10, 1.57], // = (   28 x 40   ) mm  = (  1.10 x 1.57  ) in
		// ISO Press
		'RA0' => [33.86, 48.03], // = (  860 x 1220 ) mm  = ( 33.86 x 48.03 ) in
		'RA1' => [24.02, 33.86], // = (  610 x 860  ) mm  = ( 24.02 x 33.86 ) in
		'RA2' => [16.93, 24.02], // = (  430 x 610  ) mm  = ( 16.93 x 24.02 ) in
		'RA3' => [12.01, 16.93], // = (  305 x 430  ) mm  = ( 12.01 x 16.93 ) in
		'RA4' => [8.46, 12.01], // = (  215 x 305  ) mm  = (  8.46 x 12.01 ) in
		'SRA0' => [35.43, 50.39], // = (  900 x 1280 ) mm  = ( 35.43 x 50.39 ) in
		'SRA1' => [25.20, 35.43], // = (  640 x 900  ) mm  = ( 25.20 x 35.43 ) in
		'SRA2' => [17.72, 25.20], // = (  450 x 640  ) mm  = ( 17.72 x 25.20 ) in
		'SRA3' => [12.60, 17.72], // = (  320 x 450  ) mm  = ( 12.60 x 17.72 ) in
		'SRA4' => [8.86, 12.60], // = (  225 x 320  ) mm  = (  8.86 x 12.60 ) in
		// German DIN 476
		'4A0' => [66.22, 93.62], // = ( 1682 x 2378 ) mm  = ( 66.22 x 93.62 ) in
		'2A0' => [46.81, 66.22], // = ( 1189 x 1682 ) mm  = ( 46.81 x 66.22 ) in
		// Traditional 'Loose' North American Paper Sizes
		'LETTER' => [8.50, 11.00], // = (  216 x 279  ) mm  = (  8.50 x 11.00 ) in
		'LEGAL' => [8.50, 14.00], // = (  216 x 356  ) mm  = (  8.50 x 14.00 ) in
		'LEDGER' => [17.00, 11.00], // = (  432 x 279  ) mm  = ( 17.00 x 11.00 ) in
		'TABLOID' => [11.00, 17.00], // = (  279 x 432  ) mm  = ( 11.00 x 17.00 ) in
		'EXECUTIVE' => [7.25, 10.50], // = (  184 x 267  ) mm  = (  7.25 x 10.50 ) in
		'FOLIO' => [8.50, 13.00], // = (  216 x 330  ) mm  = (  8.50 x 13.00 ) in
		'B' => [5.04, 7.80],  // = (  128 x 198  ) mm  = (  5.04 x 7.80 ) in
		'A' => [4.37, 7.00], // = (  111 x 178  ) mm  = (  4.37 x 7.00 ) in
		'DEMY' => [8.50, 5.31], // = (  135 x 216  ) mm  = (  8.50 x 5.31 ) in
		'ROYAL' => [6.02, 9.21], // = (  153 x 234  ) mm  = (  6.02 x 9.21 ) in
	];

	/** @var string Default font. */
	protected $font = '"Times New Roman", Times, serif';

	/** @var int Default font size (px). */
	protected $fontSize = '16px';

	/** @var \HeadlessChromium\Browser\ProcessAwareBrowser PDF generator instance. */
	protected $pdf;

	/** @var string Pdf HTML content. */
	protected $pdfHtml;

	/** @var array Pdf options. */
	protected $pdfOptions = [];

	/** {@inheritdoc} */
	public static function isActive(): bool
	{
		$status = false;
		if (\App\YetiForce\Register::isRegistered() && class_exists('HeadlessChromium\BrowserFactory')) {
			try {
				if (!empty(\Config\Components\Pdf::$chromiumBinaryPath)) {
					$browserFactory = new \HeadlessChromium\BrowserFactory(\Config\Components\Pdf::$chromiumBinaryPath ?? '');
					$browser = $browserFactory->createBrowser(\Config\Components\Pdf::$chromiumBrowserOptions ?? []);
					$status = $browser instanceof \HeadlessChromium\Browser;
				}
			} catch (\Throwable $th) {
				\App\Log::warning($th->__toString(), __CLASS__);
			}
		}
		return $status;
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->setInputCharset(\App\Config::main('default_charset', 'UTF-8'));
		$browserFactory = new \HeadlessChromium\BrowserFactory(\Config\Components\Pdf::$chromiumBinaryPath ?? '');
		$this->pdf = $browserFactory->createBrowser(\Config\Components\Pdf::$chromiumBrowserOptions ?? []);
		$this->pdfOptions = [
			'printBackground' => true,
			'headerTemplate' => ' ',
			'footerTemplate' => ' ',
		];
		$this->setTopMargin($this->defaultMargins['top']);
		$this->setBottomMargin($this->defaultMargins['bottom']);
		$this->setLeftMargin($this->defaultMargins['left']);
		$this->setRightMargin($this->defaultMargins['right']);
	}

	/** {@inheritdoc} */
	public function setTopMargin(float $margin)
	{
		$this->pdfOptions['marginTop'] = self::MM_TO_IN * $margin;
		return $this;
	}

	/** {@inheritdoc} */
	public function setBottomMargin(float $margin)
	{
		$this->pdfOptions['marginBottom'] = self::MM_TO_IN * $margin;
		return $this;
	}

	/**
	 * Set left margin.
	 *
	 * @param float $margin
	 */
	public function setLeftMargin(float $margin)
	{
		$this->pdfOptions['marginLeft'] = self::MM_TO_IN * $margin;
		return $this;
	}

	/** {@inheritdoc} */
	public function setRightMargin(float $margin)
	{
		$this->pdfOptions['marginRight'] = self::MM_TO_IN * $margin;
		return $this;
	}

	/** {@inheritdoc} */
	public function setHeaderMargin(float $margin)
	{
		$this->headerMargin = self::MM_TO_PX * $margin;
		return $this;
	}

	/** {@inheritdoc} */
	public function setFooterMargin(float $margin)
	{
		$this->footerMargin = self::MM_TO_PX * $margin;
		return $this;
	}

	/** {@inheritdoc} */
	public function setMargins(array $margins)
	{
		$this->setTopMargin($margins['top'] ?? $this->defaultMargins['top']);
		$this->setBottomMargin($margins['bottom'] ?? $this->defaultMargins['bottom']);
		$this->setLeftMargin($margins['left'] ?? $this->defaultMargins['left']);
		$this->setRightMargin($margins['right'] ?? $this->defaultMargins['right']);
		$this->setHeaderMargin($margins['header'] ?? $this->defaultMargins['header']);
		$this->setFooterMargin($margins['footer'] ?? $this->defaultMargins['footer']);
		return $this;
	}

	/** {@inheritdoc} */
	public function setPageSize(string $format, string $orientation = null)
	{
		$this->pdfOptions['paperWidth'] = self::PAGE_FORMATS[$format][0];
		$this->pdfOptions['paperHeight'] = self::PAGE_FORMATS[$format][1];
		if ($orientation) {
			$this->pdfOptions['landscape'] = 'L' === $orientation;
		}
		return $this;
	}

	/** {@inheritdoc} */
	public function setTitle(string $title)
	{
		// does not support this feature
		return $this;
	}

	/** {@inheritdoc} */
	public function setAuthor(string $author)
	{
		// does not support this feature
		return $this;
	}

	/** {@inheritdoc} */
	public function setCreator(string $creator)
	{
		// does not support this feature
		return $this;
	}

	/** {@inheritdoc} */
	public function setSubject(string $subject)
	{
		// does not support this feature
		return $this;
	}

	/** {@inheritdoc} */
	public function setKeywords(array $keywords)
	{
		// does not support this feature
		return $this;
	}

	/** {@inheritdoc} */
	public function setHeader(string $headerHtml)
	{
		$this->header = trim($headerHtml);
		return $this;
	}

	/** {@inheritdoc} */
	public function setFooter(string $footerHtml)
	{
		$this->footer = trim($footerHtml);
		return $this;
	}

	/**
	 * Get PDF options.
	 *
	 * @return array
	 */
	protected function getPdfOptions(): array
	{
		return $this->pdfOptions;
	}

	/**
	 * Get PDF HTML.
	 *
	 * @return string
	 */
	protected function getPdfHtml(): string
	{
		return $this->pdfHtml;
	}

	/**
	 * Write html.
	 *
	 * @return $this
	 */
	public function writeHTML()
	{
		if ($this->header) {
			$this->pdfOptions['headerTemplate'] = $this->wrapHeaderContent($this->header);
		}
		if ($this->footer) {
			$this->pdfOptions['footerTemplate'] = $this->wrapFooterContent($this->footer);
		} else {
			// Modification of the following condition will violate the license!
			if (!\App\YetiForce\Shop::check('YetiForceDisableBranding')) {
				$this->pdfOptions['footerTemplate'] = '<div style="position: fixed; font-size:6px; margin-left: 20px; text-align: left; z-index: 9999999;color: black !important">Powered by YetiForce</div>';
				$this->pdfOptions['marginBottom'] = 0.4;
			}
			// Modification of the following condition will violate the license!
		}
		if (!empty($this->pdfOptions['headerTemplate']) || !empty($this->pdfOptions['footerTemplate'])) {
			$this->pdfOptions['displayHeaderFooter'] = true;
		}
		$watermark = $this->watermark ? $this->wrapWatermark($this->template->parseVariables($this->watermark)) : '';
		$this->pdfHtml = $this->wrapContent('<div id="body">' . $watermark . '<div id="content">' . $this->getBody() . '</div></div>');
		return $this;
	}

	/**
	 * Wrap body content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	protected function wrapContent(string $content): string
	{
		$style = 'body{' . ($this->font ? ('font-family: ' . $this->font . ';') : '') . ($this->fontSize ? ('font-size: ' . $this->fontSize . ';') : '') . ';}';
		if ($this->template->get('styles')) {
			$style .= PHP_EOL . str_replace(['<', '>'], '', $this->template->get('styles'));
		}
		$content = str_replace(['{p}', '{a}'], ['<span class="pageNumber"></span>', '<span class="totalPages"></span>'], $content);
		return '<!DOCTYPE html><html lang="' . ($this->template->get('language') ?: \App\Language::getShortLanguageName()) . '">
			<head>
				<meta charset="' . $this->charset . '" />
				<style>' . $style . '</style>
			</head>
			<body style="margin: 0; padding: 0;">' . $content . '</body></html>';
	}

	/**
	 * Wrap header content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function wrapHeaderContent(string $content): string
	{
		$style = ";padding-top: {$this->headerMargin}px; padding-left: {$this->pdfOptions['marginLeft']}mm; padding-right: {$this->pdfOptions['marginRight']}mm;";
		return $this->wrapContent('<div id="header" style="color-adjust: exact; -webkit-print-color-adjust: exact; print-color-adjust: exact; ' . $style . '">' . $content . '</div>');
	}

	/**
	 * Wrap footer content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function wrapFooterContent(string $content): string
	{
		$style = "padding-bottom: {$this->footerMargin}px; padding-left: {$this->pdfOptions['marginLeft']}mm; padding-right: {$this->pdfOptions['marginRight']}mm;";
		$content = '<div id="footer" style="color-adjust: exact; -webkit-print-color-adjust: exact; print-color-adjust: exact; ' . $style . '">' . $content;
		// Modification of the following condition will violate the license!
		if (!\App\YetiForce\Shop::check('YetiForceDisableBranding')) {
			$content .= '<div style="position: fixed; font-size:6px; margin-left: 20px; text-align: left; z-index: 9999999;color: black !important">Powered by YetiForce</div>';
			if ($this->pdfOptions['marginBottom'] < (float) 0.4) {
				$this->pdfOptions['marginBottom'] = 0.4;
			}
		}
		// Modification of the following condition will violate the license!
		$content .= '</div>';
		if (empty(trim($this->pdfOptions['headerTemplate']))) {
			return $this->wrapContent($content);
		}
		return str_replace(['{p}', '{a}'], ['<span class="pageNumber"></span>', '<span class="totalPages"></span>'], $content);
	}

	/**
	 * Wrap watermark.
	 *
	 * @param string $watermarkContent
	 *
	 * @return string
	 */
	public function wrapWatermark(string $watermarkContent): string
	{
		return '<div id="watermark" style="position: fixed; z-index: -1; text-align:center; vertical-align: middle; width: 100%; height: 100%;">' . $watermarkContent . '</div>';
	}

	/** {@inheritdoc} */
	public function loadWatermark()
	{
		$this->watermark = '';
		if (self::WATERMARK_TYPE_IMAGE === $this->template->get('watermark_type') && '' !== trim($this->template->get('watermark_image'))) {
			if ($this->template->get('watermark_image') && file_exists(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $this->template->get('watermark_image')) && ($base64 = \App\Fields\File::getImageBaseData(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $this->template->get('watermark_image')))) {
				$this->watermark = '<img id="watermark-image" src="' . $base64 . '" style="max-width: 100%; max-height: 100%;"/>';
			}
		} elseif (self::WATERMARK_TYPE_TEXT === $this->template->get('watermark_type') && '' !== trim($this->template->get('watermark_text'))) {
			$this->watermark = '<div id="watermark-text" style="position: relative; top: 50%; transform: rotate(-' . $this->template->get('watermark_angle') . 'deg);">' . $this->template->get('watermark_text') . '</div>';
		}
		return $this;
	}

	/** {@inheritdoc} */
	public function output($fileName = '', $mode = 'D'): void
	{
		if (empty($fileName)) {
			$fileName = ($this->getFileName() ?: time()) . '.pdf';
		}
		$this->writeHTML();
		$page = $this->pdf->createPage();
		$tempFileName = \App\Fields\File::getTmpPath() . \App\Encryption::generatePassword(15) . '.html';
		file_put_contents($tempFileName, $this->getPdfHtml());
		$page->navigate('file://' . $tempFileName)->waitForNavigation();
		$pdf = $page->pdf($this->getPdfOptions());
		unlink($tempFileName);
		if ('I' !== $mode && 'D' !== $mode) {
			$pdf->saveToFile($fileName);
			return;
		}
		$destination = 'I' === $mode ? 'inline' : 'attachment';
		header('accept-charset: utf-8');
		header('content-type: application/pdf; charset=utf-8');
		$basename = \App\Fields\File::sanitizeUploadFileName($fileName);
		header("content-disposition: {$destination}; filename=\"{$basename}\"");
		echo base64_decode($pdf->getBase64(60000));
	}
}
