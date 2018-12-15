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
	 * Charset
	 *
	 * @var string
	 */
	protected $charset = 'UTF-8';
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
		'bottom' => 40
	];

	/**
	 * Default font.
	 *
	 * @var string
	 */
	protected $defaultFontFamily = 'Noto Serif';

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
	public function __construct($mode = '', $format = 'A4', $defaultFontSize = 10, $defaultFont = 'Noto Serif', $orientation = 'P', $leftMargin = 30, $rightMargin = 30, $topMargin = 40, $bottomMargin = 40, $headerMargin = 10, $footerMargin = 10)
	{
		$args = func_get_args();
		// this two arguments are kind of signal that we are configured (from template or elsewhere) - not from default argument values (empty = default)
		if (!empty($args['format']) || !empty($args['orientation'])) {
			$this->isDefault = false;
		}
		if ($mode) {
			$this->charset = $mode;
		}
		$this->setLibraryName('YetiForcePDF');
		$this->defaultFontFamily = $defaultFont;
		$this->defaultFontSize = $defaultFontSize;
		$this->format = $format;
		$this->orientation = $orientation;
		$this->initializePdf($mode, $format, $defaultFontSize, $defaultFont, $orientation, $leftMargin, $rightMargin, $topMargin, $bottomMargin, $headerMargin, $footerMargin);
	}

	/**
	 * Initialize pdf file params.
	 *
	 * @param string $mode
	 * @param string $format
	 * @param int $defaultFontSize
	 * @param string $defaultFont
	 * @param string $orientation
	 * @param int $leftMargin
	 * @param int $rightMargin
	 * @param int $topMargin
	 * @param int $bottomMargin
	 * @param int $headerMargin
	 * @param int $footerMargin
	 */
	public function initializePdf($mode = '', $format = 'A4', $defaultFontSize = 10, $defaultFont = 'Noto Serif', $orientation = 'P', $leftMargin = 30, $rightMargin = 30, $topMargin = 40, $bottomMargin = 40, $headerMargin = 10, $footerMargin = 10)
	{
		if (empty($mode)) {
			$mode = \AppConfig::main('default_charset') ?? 'UTF-8';
		}
		$this->pdf = (new Document())->init();
		$this->pdf->setDefaultFormat($format);
		$this->pdf->setDefaultOrientation($orientation);
		$this->pdf->setDefaultMargins($leftMargin, $topMargin, $rightMargin, $bottomMargin);
		$this->headerMargin = $headerMargin;
		$this->footerMargin = $footerMargin;
	}

	/**
	 * Returns bank name.
	 */
	public function getLibraryName()
	{
		return $this->library;
	}

	/**
	 * Sets library name.
	 */
	public function setLibraryName($name)
	{
		$this->library = $name;
	}

	/**
	 * Returns template id.
	 */
	public function getTemplateId()
	{
		return $this->templateId;
	}

	/**
	 * Sets the template id.
	 */
	public function setTemplateId($id)
	{
		$this->templateId = $id;
	}

	/**
	 * Returns record id.
	 */
	public function getRecordId()
	{
		return $this->recordId;
	}

	/**
	 * Sets the record id.
	 */
	public function setRecordId($id)
	{
		$this->recordId = $id;
	}

	/**
	 * Returns module name.
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}

	/**
	 * Sets module name.
	 */
	public function setModuleName($name)
	{
		$this->moduleName = $name;
		$handlerClass = \Vtiger_Loader::getComponentClassName('Model', 'PDF', $name);
		$this->moduleModel = new $handlerClass();
	}

	/**
	 * Set top margin.
	 */
	public function setTopMargin($margin)
	{
		$this->pdf->setDefaultTopMargin((float)$margin);
		$this->defaultMargins['top'] = $margin;
		return $this;
	}

	/**
	 * Set bottom margin.
	 */
	public function setBottomMargin($margin)
	{
		$this->pdf->setDefaultBottomMargin((float)$margin);
		$this->defaultMargins['bottom'] = $margin;
		return $this;
	}

	/**
	 * Set left margin.
	 */
	public function setLeftMargin($margin)
	{
		$this->pdf->setDefaultLeftMargin((float)$margin);
		$this->defaultMargins['left'] = $margin;
		return $this;
	}

	/**
	 * Set right margin.
	 */
	public function setRightMargin($margin)
	{
		$this->pdf->setDefaultRightMargin((float)$margin);
		$this->defaultMargins['right'] = $margin;
		return $this;
	}

	/**
	 * Set page size and orientation.
	 *
	 * @param string|null $format - page format
	 * @param string $orientation - page orientation
	 */
	public function setPageSize($format, $orientation = null)
	{
		$this->pdf->setDefaultPageFormat($format);
		if ($orientation) {
			$this->pdf->setDefaultPageORientation($orientation);
		}
	}

	/**
	 * Set language.
	 *
	 * @param $language
	 */
	public function setLanguage($language)
	{
		parent::setLanguage($language);
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
	 * Parse and set options.
	 *
	 * @param array $params - array of parameters
	 * @param bool $defaultMargins - use default margins or custom user specified?
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
						\App\Log::info('NOT IMPLEMENTED: ' . __CLASS__ . __METHOD__ . __LINE__);
					}
					break;
				case 'footer_height':
					if (is_numeric($value)) {
						\App\Log::info('NOT IMPLEMENTED: ' . __CLASS__ . __METHOD__ . __LINE__);
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
					$this->setKeywords($value);
					break;
				default:
					break;
			}
		}
	}

	// meta attributes

	/**
	 * Set Title of the document.
	 */
	public function setTitle($title)
	{
		$this->pdf->getMeta()->setTitle($title);
	}

	/**
	 * Set Title of the document.
	 */
	public function setAuthor($author)
	{
		$this->pdf->getMeta()->setAuthor($author);
	}

	/**
	 * Set Title of the document.
	 */
	public function setCreator($creator)
	{
		$this->pdf->getMeta()->setCreator($creator);
	}

	/**
	 * Set Title of the document.
	 */
	public function setSubject($subject)
	{
		$this->pdf->getMeta()->setSubject($subject);
	}

	/**
	 * Set Title of the document.
	 */
	public function setKeywords($keywords)
	{
		if (is_array($keywords)) {
			$this->pdf->getMeta()->setKeywords($keywords);
		} elseif (is_string($keywords)) {
			$xpld = explode(',', $keywords);
			foreach ($xpld as &$word) {
				$word = trim($word);
			}
			$this->pdf->getMeta()->setKeywords($xpld);
		}
	}

	/**
	 * Set header content.
	 */
	public function setHeader($name, $header)
	{
		$this->header = trim($header);
	}

	/**
	 * Set footer content.
	 */
	public function setFooter($name, $footer)
	{
		$this->footer = trim($footer);
	}

	/**
	 * Wrap header content
	 * @param string $headerContent
	 * @return string
	 */
	public function wrapHeaderContent(string $headerContent)
	{
		$style = "padding-top:{$this->headerMargin}px; padding-left:{$this->defaultMargins['left']}px; padding-right:{$this->defaultMargins['right']}px";
		return '<div data-header style="' . $style . '">' . $headerContent . '</div>';
	}

	/**
	 * Wrap footer content
	 * @param string $footerContent
	 * @return string
	 */
	public function wrapFooterContent(string $footerContent)
	{
		$style = "padding-bottom:{$this->footerMargin}px; padding-left:{$this->defaultMargins['left']}px; padding-right:{$this->defaultMargins['right']}px";
		return '<div data-footer style="' . $style . '">' . $footerContent . '</div>';
	}

	/**
	 * Wrap watermark
	 * @param string $watermarkContent
	 * @return string
	 */
	public function wrapWatermark(string $watermarkContent)
	{
		return '<div data-watermark style="text-align:center">' . $watermarkContent . '</div>';
	}

	/**
	 * Write html.
	 */
	public function writeHTML()
	{
		$footer = '';
		if ($this->footer !== '') {
			$footer = $this->wrapFooterContent($this->footer);
		}
		$header = '';
		if ($this->header !== '') {
			$header = $this->wrapHeaderContent($this->header);
		}
		$watermark = '';
		if ($this->watermark !== '') {
			$watermark = $this->wrapWatermark($this->watermark);
		}
		$html = $this->parseVariables($watermark . $header . $footer . $this->html);
		if (strtoupper($this->charset) !== 'UTF-8') {
			$html = mb_convert_encoding($html, 'UTF-8', $this->charset);
		}
		$this->pdf->loadHtml($html);
	}

	/**
	 * Get watermark
	 * @param $templateModel
	 * @return string
	 */
	public function getWatermark($templateModel)
	{
		$watermark = '';
		if ($templateModel->get('watermark_type') === self::WATERMARK_TYPE_IMAGE && trim($templateModel->get('watermark_image')) !== '') {
			if ($templateModel->get('watermark_image')) {
				$watermark = '<img src="' . $templateModel->get('watermark_image') . '" style="opacity:0.1;">';
			}
		} elseif ($templateModel->get('watermark_type') === self::WATERMARK_TYPE_TEXT && trim($templateModel->get('watermark_text')) !== '') {
			$fontSize = '10';
			if ($templateModel->get('watermark_size')) {
				$fontSize = $templateModel->get('watermark_size');
			}
			$watermark = '<div style="opacity:0.1;display:inline-block;font-size:' . $fontSize . 'px">' . $templateModel->get('watermark_text') . '</div>';
		}
		return $watermark;
	}

	/**
	 * Set watermark.
	 *
	 * @param $templateModel
	 */
	public function setWaterMark($templateModel)
	{
		$this->watermark = $this->getWatermark($templateModel);
	}

	/**
	 * Load html.
	 *
	 * @param string $html
	 */
	public function loadHTML($html)
	{
		$this->html = $html;
	}

	/**
	 * Prepare pdf, generate all content.
	 *
	 * @param int $recordId
	 * @param string $moduleName
	 * @param int $templateId
	 * @param int $templateMainRecordId - optional if null $recordId is used
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
				$self = new self($charset, $template->get('page_format'), $this->defaultFontSize, $this->defaultFontFamily, $pageOrientationValue);
			} else {
				$self = new self($charset, $template->get('page_format'), $this->defaultFontSize, $this->defaultFontFamily, $pageOrientationValue, $template->get('margin_left'), $template->get('margin_right'), $template->get('margin_top'), $template->get('margin_bottom'), $template->get('header_height'), $template->get('footer_height'));
			}
			$self->isDefault = false;
		} else {
			$self = $this;
		}
		$self->setTemplateId($templateId);
		$self->setRecordId($recordId);
		$self->setModuleName($moduleName);
		//\App\Language::setTemporaryLanguage($template->get('language'));
		$self->setWaterMark($template);
		$self->setLanguage($template->get('language'));
		$self->setFileName($self->parseVariables($template->get('filename')));
		$self->parseParams($template->getParameters(), $template->get('margin_chkbox') !== 1);
		$self->loadHtml($template->getBody());
		$self->setHeader('', $template->getHeader());
		$self->setFooter('', $template->getFooter());
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
				$fileName = 'file.pdf';
			}
			$dest = 'I';
		}
		$this->writeHTML();
		$output = $this->pdf->render();
		if ($dest !== 'I') {
			return file_put_contents($fileName, $output);
		}
		header('accept-charset: utf-8');
		header('content-type: application/pdf');
		header('content-disposition: inline; filename="' . basename($fileName) . '"');
		echo $output;
	}

	/**
	 * Export record to PDF file.
	 *
	 * @param int $recordId - id of a record
	 * @param string $moduleName - name of records module
	 * @param int $templateId - id of pdf template
	 * @param string $filePath - path name for saving pdf file
	 * @param string $saveFlag - save option flag
	 */
	public function export($recordId, $moduleName, $templateId, $filePath = '', $saveFlag = '')
	{
		$this->generateContent($recordId, $moduleName, $templateId, $recordId)->output($filePath, $saveFlag);
	}
}
