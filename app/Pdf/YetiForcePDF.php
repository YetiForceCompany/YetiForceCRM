<?php

/**
 * Class using YetiForcePDF as a PDF creator.
 *
 * @package   App\Pdf
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetifoce.com>
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
	 * Are we fully configured or default params were given in constructor (not fully configured) ?
	 *
	 * @var bool
	 */
	private $isDefault = true;

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
	 * Page orientation.
	 *
	 * @var array
	 */
	public $pageOrientation = ['PLL_PORTRAIT' => 'P', 'PLL_LANDSCAPE' => 'L'];

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
	 * Returns pdf library object.
	 */
	public function pdf()
	{
		return $this->pdf;
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->setLibraryName('YetiForcePDF');
		$this->setInputCharset(\AppConfig::main('default_charset') ?? 'UTF-8');
		$this->pdf = (new Document())->init();
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
	 * Returns bank name.
	 */
	public function getLibraryName()
	{
		return $this->library;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setLibraryName(string $name)
	{
		$this->library = $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateId()
	{
		return $this->templateId;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTemplateId($id)
	{
		$this->templateId = $id;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRecordId()
	{
		return $this->recordId;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRecordId($id)
	{
		$this->recordId = $id;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setModuleName(string $name)
	{
		$this->moduleName = $name;
		$handlerClass = \Vtiger_Loader::getComponentClassName('Model', 'PDF', $name);
		$this->moduleModel = new $handlerClass();
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
	 * {@inheritdoc}
	 */
	public function setFooterMargin(float $margin)
	{
		$this->footerMargin = $margin;
		return $this;
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
	 * Set font.
	 *
	 * @param string $family
	 * @param int    $size
	 *
	 * @return $this
	 */
	public function setFont(string $family, int $size)
	{
		$this->defaultFontFamily = $family;
		$this->defaultFontSize = $size;
		return $this;
	}

	/**
	 * Parse variables.
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public function parseVariables(string $str)
	{
		$textParser = \App\TextParser::getInstanceById($this->recordId, $this->moduleName);
		$textParser->setType('pdf');
		$textParser->setParams(['pdf' => $this->moduleModel]);
		if ($this->language) {
			$textParser->setLanguage($this->language);
		}
		return $textParser->setContent($str)->parse()->getContent();
	}

	/**
	 * {@inheritdoc}
	 */
	public function parseParams(array $params, $defaultMargins = true)
	{
		if ($defaultMargins) {
			$params = array_diff_key($params, ['margin-top', 'margin-bottom', 'margin-left', 'margin-right', 'header_height', 'footer_height']);
		}
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
		$this->pdf->getMeta()->setTitle($this->parseVariables($title));
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setAuthor(string $author)
	{
		$this->pdf->getMeta()->setAuthor($this->parseVariables($author));
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
		$this->pdf->getMeta()->setSubject($this->parseVariables($subject));
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
		$this->footer = trim($footerHtml);
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
		$fontsDir = 'layouts' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR;
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
		$footer = $this->footer ? $this->wrapFooterContent($this->footer) : '';
		$header = $this->header ? $this->wrapHeaderContent($this->header) : '';
		$watermark = $this->watermark ? $this->wrapWatermark($this->watermark) : '';
		$html = $this->parseVariables($watermark . $header . $footer . $this->html);
		$this->pdf->loadHtml($html, $this->charset);
		return $this;
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
		if ($templateModel->get('watermark_type') === self::WATERMARK_TYPE_IMAGE && trim($templateModel->get('watermark_image')) !== '') {
			if ($templateModel->get('watermark_image')) {
				$watermark = '<img src="' . $templateModel->get('watermark_image') . '" style="opacity:0.1;">';
			}
		} elseif ($templateModel->get('watermark_type') === self::WATERMARK_TYPE_TEXT && trim($templateModel->get('watermark_text')) !== '') {
			$watermark = '<div style="opacity:0.1;display:inline-block;">' . $templateModel->get('watermark_text') . '</div>';
		}
		return $watermark;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setWatermark(\Vtiger_PDF_Model $templateModel)
	{
		$this->watermark = $this->getTemplateWatermark($templateModel);
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
	 * Prepare pdf, generate all content.
	 *
	 * @param int    $recordId
	 * @param string $moduleName
	 * @param int    $templateId
	 * @param int    $templateMainRecordId - optional if null $recordId is used
	 *
	 * @return YetiForcePDF current or new instance if needed
	 */
	public function generateContent($recordId, $moduleName, $templateId, $templateMainRecordId = null)
	{
		$template = \Vtiger_PDF_Model::getInstanceById($templateId, $moduleName);
		$template->setMainRecordId($templateMainRecordId ? $templateMainRecordId : $recordId);
		$pageOrientationValue = $template->get('page_orientation') === 'PLL_PORTRAIT' ? 'P' : 'L';
		if ($this->isDefault) {
			$charset = \AppConfig::main('default_charset') ?? 'UTF-8';
			if ($template->get('margin_chkbox') === 1) {
				$self = new self($charset);
				$self->setPageSize($template->get('page_format'), $pageOrientationValue);
				$self->setFont($this->defaultFontFamily, $this->defaultFontSize);
			} else {
				$self = new self($charset);
				$self->setPageSize($template->get('page_format'), $pageOrientationValue);
				$self->setFont($this->defaultFontFamily, $this->defaultFontSize);
				$self->setMargins([
					'top' => $template->get('margin_top'),
					'right' => $template->get('margin_right'),
					'bottom' => $template->get('margin_bottom'),
					'left' => $template->get('margin_left'),
					'header' => $template->get('header_height'),
					'footer' => $template->get('footer_height')
				]);
			}
			$self->isDefault = false;
		} else {
			$self = $this;
		}
		$self->setTemplateId($templateId);
		$self->setRecordId($recordId);
		$self->setModuleName($moduleName);
		\App\Language::setTemporaryLanguage($template->get('language'));
		$self->setWatermark($template);
		$self->setLanguage($template->get('language'));
		$self->setFileName($self->parseVariables($template->get('filename')));
		$self->parseParams($template->getParameters(), $template->get('margin_chkbox') !== 1);
		$self->loadHtml($template->getBody());
		$self->setHeader($template->getHeader());
		$self->setFooter($template->getFooter());
		\App\Language::clearTemporaryLanguage();
		return $self;
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
			if ($this->getFileName()) {
				$fileName = $this->getFileName() . '.pdf';
			} else {
				$date = date('Y-m-d');
				$fileName = "{$this->moduleName} {$this->recordId} $date.pdf";
			}
			$dest = 'I';
		}
		$this->writeHTML();
		$output = $this->pdf->render();
		if ($dest !== 'I') {
			return file_put_contents($fileName, $output);
		}
		header('accept-charset: utf-8');
		header('content-type: application/pdf; charset=utf-8');
		$basename = \App\Fields\File::sanitizeUploadFileName($fileName);
		header("content-disposition: attachment; filename=\"{$basename}\"");
		echo $output;
	}

	/**
	 * Export record to PDF file.
	 *
	 * @param int    $recordId   - id of a record
	 * @param string $moduleName - name of records module
	 * @param int    $templateId - id of pdf template
	 * @param string $filePath   - path name for saving pdf file
	 * @param string $saveFlag   - save option flag
	 */
	public function export($recordId, $moduleName, $templateId, $filePath = '', $saveFlag = '')
	{
		$this->generateContent($recordId, $moduleName, $templateId, $recordId)->output($filePath, $saveFlag);
	}
}
