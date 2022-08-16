<?php

/**
 * YetiForcePDF driver file for PDF generation.
 *
 * @package App\Pdf
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetifoce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author	  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Pdf\Drivers;

/**
 * YetiForcePDF driver class for PDF generation.
 */
class YetiForcePDF extends Base
{
	/** {@inheritdoc} */
	const DRIVER_NAME = 'LBL_YETIFORCE_PDF';

	/** @var string Default font. */
	protected $font = 'DejaVu Sans';

	/** @var int Default font size */
	protected $fontSize = 10;

	/** @var \YetiForcePDF\Document PDF generator instance. */
	protected $pdf;

	/** {@inheritdoc} */
	public static function isActive(): bool
	{
		return true;
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->setInputCharset(\App\Config::main('default_charset') ?? 'UTF-8');
		$this->pdf = (new \YetiForcePDF\Document())->init();
	}

	/** {@inheritdoc} */
	public function setTopMargin(float $margin)
	{
		$this->pdf->setDefaultTopMargin($margin);
		$this->defaultMargins['top'] = $margin;
		return $this;
	}

	/** {@inheritdoc} */
	public function setBottomMargin(float $margin)
	{
		$this->pdf->setDefaultBottomMargin((float) $margin);
		$this->defaultMargins['bottom'] = $margin;
		return $this;
	}

	/**
	 * Set left margin.
	 *
	 * @param float $margin
	 */
	public function setLeftMargin(float $margin)
	{
		$this->pdf->setDefaultLeftMargin((float) $margin);
		$this->defaultMargins['left'] = $margin;
		return $this;
	}

	/** {@inheritdoc} */
	public function setRightMargin(float $margin)
	{
		$this->pdf->setDefaultRightMargin((float) $margin);
		$this->defaultMargins['right'] = $margin;
		return $this;
	}

	/** {@inheritdoc} */
	public function setHeaderMargin(float $margin)
	{
		$this->headerMargin = $margin;
		return $this;
	}

	/** {@inheritdoc} */
	public function setFooterMargin(float $margin)
	{
		$this->footerMargin = $margin;
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
		$this->pdf->setDefaultFormat($format);
		if ($orientation) {
			$this->pdf->setDefaultOrientation($orientation);
		}
		return $this;
	}

	/** {@inheritdoc} */
	public function setTitle(string $title)
	{
		$this->pdf->getMeta()->setTitle($title);
		return $this;
	}

	/** {@inheritdoc} */
	public function setAuthor(string $author)
	{
		$this->pdf->getMeta()->setAuthor($author);
		return $this;
	}

	/** {@inheritdoc} */
	public function setCreator(string $creator)
	{
		$this->pdf->getMeta()->setCreator($creator);
		return $this;
	}

	/** {@inheritdoc} */
	public function setSubject(string $subject)
	{
		$this->pdf->getMeta()->setSubject($subject);
		return $this;
	}

	/** {@inheritdoc} */
	public function setKeywords(array $keywords)
	{
		$this->pdf->getMeta()->setKeywords($keywords);
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
	 * Write html.
	 *
	 * @return $this
	 */
	public function writeHTML()
	{
		$this->loadCustomFonts();
		$this->pdf->loadHtml($this->getHtml(), $this->charset);
		return $this;
	}

	/**
	 * Gets full html.
	 *
	 * @return string
	 */
	public function getHtml(): string
	{
		$html = $this->watermark ? $this->wrapWatermark($this->watermark) : '';
		$html .= $this->header ? $this->wrapHeaderContent($this->header) : '';
		$html .= $this->getBody();
		$html .= $this->footer ? $this->wrapFooterContent($this->footer) : '';
		return $html;
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
		$style = "padding-top:{$this->headerMargin}px; padding-left:{$this->defaultMargins['left']}px; padding-right:{$this->defaultMargins['right']}px";
		return '<div id="header" data-header style="' . $style . '">' . $content . '</div>';
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
		$style = "padding-bottom:{$this->footerMargin}px; padding-left:{$this->defaultMargins['left']}px; padding-right:{$this->defaultMargins['right']}px";
		// Modification of the following condition will violate the license!
		if (!\App\YetiForce\Shop::check('YetiForceDisableBranding')) {
			$content .= '<table style="font-size:6px;width:100%; margin: 0;"><tbody><tr><td style="width:50%">Powered by YetiForce</td></tr></tbody></table>';
		}
		// Modification of the following condition will violate the license!
		return '<div id="footer" data-footer style="' . $style . '">' . $content . '</div>';
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
		return '<div id="watermark" data-watermark style="text-align:center">' . $watermarkContent . '</div>';
	}

	/**
	 * Load custom fonts.
	 *
	 * @return $this
	 */
	private function loadCustomFonts()
	{
		$fontsDir = 'layouts' . \DIRECTORY_SEPARATOR . 'resources' . \DIRECTORY_SEPARATOR . 'fonts' . \DIRECTORY_SEPARATOR;
		$resolvedDir = \Vtiger_Loader::resolveNameToPath('~' . $fontsDir, 'css');
		$customFonts = \App\Json::read($resolvedDir . 'fonts.json');
		foreach ($customFonts as &$font) {
			$font['file'] = $resolvedDir . $font['file'];
		}
		\YetiForcePDF\Document::addFonts($customFonts);
		return $this;
	}

	/** {@inheritdoc} */
	public function loadWatermark()
	{
		$this->watermark = '';
		if (self::WATERMARK_TYPE_IMAGE === $this->template->get('watermark_type') && '' !== trim($this->template->get('watermark_image'))) {
			if ($this->template->get('watermark_image')) {
				$this->watermark = '<img src="' . $this->template->get('watermark_image') . '" style="opacity:0.1;">';
			}
		} elseif (self::WATERMARK_TYPE_TEXT === $this->template->get('watermark_type') && '' !== trim($this->template->get('watermark_text'))) {
			$this->watermark = '<div style="opacity:0.1;display:inline-block;">' . $this->template->parseVariables($this->template->get('watermark_text')) . '</div>';
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
		$output = $this->pdf->render();
		if ('I' !== $mode && 'D' !== $mode) {
			file_put_contents($fileName, $output);
			return;
		}
		$destination = 'I' === $mode ? 'inline' : 'attachment';
		header('accept-charset: utf-8');
		header('content-type: application/pdf; charset=utf-8');
		$basename = \App\Fields\File::sanitizeUploadFileName($fileName);
		header("content-disposition: {$destination}; filename=\"{$basename}\"");
		echo $output;
	}
}
