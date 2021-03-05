<?php

/**
 * Class using YetiForcePDF as a PDF creator.
 *
 * @package   App\Pdf
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetifoce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Pdf;

use YetiForcePDF\Document;

/**
 * YetiForcePDF class.
 */
class YetiForcePDF extends PDF
{
	const WATERMARK_TYPE_TEXT = 0;
	const WATERMARK_TYPE_IMAGE = 1;

	/**
	 * Charset.
	 *
	 * @var string
	 */
	protected $charset = '';

	/**
	 * HTML content.
	 *
	 * @var string
	 */
	public $html = '';

	/**
	 * Page format.
	 *
	 * @var string
	 */
	public $format = '';
	/**
	 * @var string
	 */
	public $orientation = 'P';
	/**
	 * @var string
	 */
	protected $header = '';

	/**
	 * @var string
	 */
	protected $footer = '';

	/**
	 * @var string
	 */
	protected $footerYetiForce = '';

	/**
	 * @var string
	 */
	protected $watermark = '';
	/**
	 * @var int
	 */
	protected $headerMargin = 10;
	/**
	 * @var int
	 */
	protected $footerMargin = 10;

	/**
	 * Default margins.
	 *
	 * @var array
	 */
	public $defaultMargins = [
		'left' => 30,
		'right' => 30,
		'top' => 40,
		'bottom' => 40,
		'header' => 10,
		'footer' => 10
	];

	/**
	 * Default font.
	 *
	 * @var string
	 */
	protected $defaultFontFamily = 'DejaVu Sans';

	/**
	 * Default font size.
	 *
	 * @var int
	 */
	protected $defaultFontSize = 10;

	/**
	 * @var \Vtiger_Module_Model
	 */
	protected $moduleModel;

	/**
	 * Additional params.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Returns pdf library object.
	 */
	public function getPdf()
	{
		return $this->pdf;
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->setInputCharset(\App\Config::main('default_charset') ?? 'UTF-8');
		$this->pdf = (new Document())->init();
		// Modification of the following condition will violate the license!
		if (!\App\YetiForce\Shop::check('YetiForceDisableBranding')) {
			$this->footer = $this->footerYetiForce = '<table style="font-size:6px;width:100%; margin: 0;"><tbody><tr><td style="width:50%">Powered by YetiForce</td></tr></tbody></table>';
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInputCharset()
	{
		return $this->charset;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setInputCharset(string $charset)
	{
		$this->charset = $charset;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTopMargin(float $margin)
	{
		$this->pdf->setDefaultTopMargin($margin);
		$this->defaultMargins['top'] = $margin;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
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

	/**
	 * {@inheritdoc}
	 */
	public function setRightMargin(float $margin)
	{
		$this->pdf->setDefaultRightMargin((float) $margin);
		$this->defaultMargins['right'] = $margin;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHeaderMargin(float $margin)
	{
		$this->headerMargin = $margin;
		return $this;
	}

	/**
	 * Gets header margin.
	 */
	public function getHeaderMargin()
	{
		return $this->headerMargin;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setFooterMargin(float $margin)
	{
		$this->footerMargin = $margin;
		return $this;
	}

	/**
	 * Gets footer margin.
	 */
	public function getFooterMargin()
	{
		return $this->footerMargin;
	}

	/**
	 * {@inheritdoc}
	 */
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

	/**
	 * {@inheritdoc}
	 */
	public function setPageSize(string $format, string $orientation = null)
	{
		$this->pdf->setDefaultFormat($format);
		if ($orientation) {
			$this->pdf->setDefaultOrientation($orientation);
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setFont(string $family, int $size)
	{
		$this->defaultFontFamily = $family;
		$this->defaultFontSize = $size;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parseParams(array $params)
	{
		foreach ($params as $param => $value) {
			switch ($param) {
				case 'margin-top':
					if (is_numeric($value)) {
						$this->setTopMargin($value);
					} else {
						$this->setTopMargin($this->defaultMargins['top']);
					}
					break;
				case 'margin-bottom':
					if (is_numeric($value)) {
						$this->setBottomMargin($value);
					} else {
						$this->setBottomMargin($this->defaultMargins['bottom']);
					}
					break;
				case 'margin-left':
					if (is_numeric($value)) {
						$this->setLeftMargin($value);
					} else {
						$this->setLeftMargin($this->defaultMargins['left']);
					}
					break;
				case 'margin-right':
					if (is_numeric($value)) {
						$this->setRightMargin($value);
					} else {
						$this->setRightMargin($this->defaultMargins['right']);
					}
					break;
				case 'header_height':
					if (is_numeric($value)) {
						$this->setHeaderMargin($value);
					} else {
						$this->setHeaderMargin($this->defaultMargins['header']);
					}
					break;
				case 'footer_height':
					if (is_numeric($value)) {
						$this->setFooterMargin($value);
					} else {
						$this->setFooterMargin($this->defaultMargins['footer']);
					}
					break;
				case 'title':
					$this->setTitle($value);
					break;
				case 'author':
					$this->setAuthor($value);
					break;
				case 'creator':
					$this->setCreator($value);
					break;
				case 'subject':
					$this->setSubject($value);
					break;
				case 'keywords':
					$this->setKeywords(explode(',', $value));
					break;
				default:
					break;
			}
		}
		return $this;
	}

	// meta attributes

	/**
	 * {@inheritdoc}
	 */
	public function setTitle(string $title)
	{
		$this->pdf->getMeta()->setTitle($title);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setAuthor(string $author)
	{
		$this->pdf->getMeta()->setAuthor($author);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCreator(string $creator)
	{
		$this->pdf->getMeta()->setCreator($creator);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSubject(string $subject)
	{
		$this->pdf->getMeta()->setSubject($subject);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setKeywords(array $keywords)
	{
		$this->pdf->getMeta()->setKeywords($keywords);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHeader(string $headerHtml)
	{
		$this->header = trim($headerHtml);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setFooter(string $footerHtml)
	{
		$this->footer = trim($footerHtml) . $this->footerYetiForce;
		return $this;
	}

	/**
	 * Wrap header content.
	 *
	 * @param string $headerContent
	 *
	 * @return string
	 */
	public function wrapHeaderContent(string $headerContent)
	{
		$style = "padding-top:{$this->headerMargin}px; padding-left:{$this->defaultMargins['left']}px; padding-right:{$this->defaultMargins['right']}px";
		return '<div data-header style="' . $style . '">' . $headerContent . '</div>';
	}

	/**
	 * Wrap footer content.
	 *
	 * @param string $footerContent
	 *
	 * @return string
	 */
	public function wrapFooterContent(string $footerContent)
	{
		$style = "padding-bottom:{$this->footerMargin}px; padding-left:{$this->defaultMargins['left']}px; padding-right:{$this->defaultMargins['right']}px";
		return '<div data-footer style="' . $style . '">' . $footerContent . '</div>';
	}

	/**
	 * Wrap watermark.
	 *
	 * @param string $watermarkContent
	 *
	 * @return string
	 */
	public function wrapWatermark(string $watermarkContent)
	{
		return '<div data-watermark style="text-align:center">' . $watermarkContent . '</div>';
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
	public function getHtml()
	{
		$html = $this->watermark ? $this->wrapWatermark($this->watermark) : '';
		$html .= $this->header ? $this->wrapHeaderContent($this->header) : '';
		$html .= $this->html;
		$html .= $this->footer ? $this->wrapFooterContent($this->footer) : '';
		return $html;
	}

	/**
	 * Get template watermark.
	 *
	 * @param \Vtiger_PDF_Model $templateModel
	 *
	 * @return string
	 */
	public function getTemplateWatermark(\Vtiger_PDF_Model $templateModel)
	{
		$watermark = '';
		if (self::WATERMARK_TYPE_IMAGE === $templateModel->get('watermark_type') && '' !== trim($templateModel->get('watermark_image'))) {
			if ($templateModel->get('watermark_image')) {
				$watermark = '<img src="' . $templateModel->get('watermark_image') . '" style="opacity:0.1;">';
			}
		} elseif (self::WATERMARK_TYPE_TEXT === $templateModel->get('watermark_type') && '' !== trim($templateModel->get('watermark_text'))) {
			$watermark = '<div style="opacity:0.1;display:inline-block;">' . $templateModel->parseVariables($templateModel->get('watermark_text')) . '</div>';
		}
		return $watermark;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setWatermark(string $watermark)
	{
		$this->watermark = $watermark;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadHtml(string $html)
	{
		$this->html = $html;
		return $this;
	}

	/**
	 * Output content to PDF.
	 *
	 * @param string $fileName
	 * @param string $dest
	 */
	public function output($fileName = '', $dest = '')
	{
		if (empty($fileName)) {
			$fileName = ($this->getFileName() ?: time()) . '.pdf';
		}
		if (!$dest) {
			$dest = 'D';
		}
		$this->writeHTML();
		$output = $this->pdf->render();
		if ('I' !== $dest && 'D' !== $dest) {
			return file_put_contents($fileName, $output);
		}
		$destination = 'I' === $dest ? 'inline' : 'attachment';
		header('accept-charset: utf-8');
		header('content-type: application/pdf; charset=utf-8');
		$basename = \App\Fields\File::sanitizeUploadFileName($fileName);
		header("content-disposition: {$destination}; filename=\"{$basename}\"");
		echo $output;
	}

	/**
	 * Export record to PDF file.
	 *
	 * @param int    $recordId   - record ID
	 * @param int    $templateId - id of pdf template
	 * @param string $filePath   - path name for saving pdf file
	 * @param string $saveFlag   - save option flag
	 */
	public function export($recordId, $templateId, $filePath = '', $saveFlag = '')
	{
		\Vtiger_PDF_Model::exportToPdf($recordId, $templateId, $filePath, $saveFlag);
	}
}
