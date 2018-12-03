<?php

/**
 * Class using YetiForcePDF as a PDF creator.
 *
 * @package App\Pdf
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
		'left' => 15,
		'right' => 15,
		'top' => 16,
		'bottom' => 16
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
	public function __construct($mode = '', $format = 'A4', $defaultFontSize = 10, $defaultFont = 'Noto Serif', $orientation = 'P', $leftMargin = 15, $rightMargin = 15, $topMargin = 16, $bottomMargin = 16, $headerMargin = 9, $footerMargin = 9)
	{
		$args = func_get_args();
		// this two arguments are kind of signal that we are configured (from template or elsewhere) - not from default argument values (empty = default)
		if (!empty($args['format']) || !empty($args['orientation'])) {
			$this->isDefault = false;
		}
		$this->setLibraryName('YetiForcePDF');
		$this->defaultFontFamily = $defaultFont;
		$this->defaultFontSize = $defaultFontSize;
		$this->format = $format;
		$this->initializePdf($mode, $format, $defaultFontSize, $defaultFont, $orientation, $leftMargin, $rightMargin, $topMargin, $bottomMargin, $headerMargin, $footerMargin);
	}

	/**
	 * Initialize pdf file params.
	 *
	 * @param string $mode
	 * @param string $format
	 * @param int    $defaultFontSize
	 * @param string $defaultFont
	 * @param string $orientation
	 * @param int    $leftMargin
	 * @param int    $rightMargin
	 * @param int    $topMargin
	 * @param int    $bottomMargin
	 * @param int    $headerMargin
	 * @param int    $footerMargin
	 */
	public function initializePdf($mode = '', $format = 'A4', $defaultFontSize = 10, $defaultFont = 'Noto Serif', $orientation = 'P', $leftMargin = 15, $rightMargin = 15, $topMargin = 16, $bottomMargin = 16, $headerMargin = 9, $footerMargin = 9)
	{
		if (empty($mode)) {
			$mode = \AppConfig::main('default_charset') ?? 'UTF-8';
		}
		$this->pdf = (new Document())->init();
		$this->pdf->setDefaultFormat($format);
		$this->pdf->setDefaultOrientation($orientation);
		$this->pdf->setDefaultMargins($leftMargin, $topMargin, $rightMargin, $bottomMargin);
		/*$this->pdf->setFontSubsetting(true);
		$this->pdf->SetFont($this->defaultFontFamily, '', $this->defaultFontSize);
		$this->pdf->SetMargins($leftMargin, $topMargin, $rightMargin, true);
		$this->pdf->SetHeaderMargin($headerMargin);
		$this->pdf->SetFooterMargin($footerMargin);
		$this->pdf->SetAutoPageBreak(true, $bottomMargin);
		$this->pdf->setHeaderFontFamily($defaultFont);
		$this->pdf->setHeaderFontVariation('');
		$this->pdf->setHeaderFontSize($defaultFontSize);
		$this->pdf->setFooterFontFamily($defaultFont);
		$this->pdf->setFooterFontVariation('');
		$this->pdf->setFooterFontSize($defaultFontSize);
		$this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);*/
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
		//$this->pdf->SetTopMargin($margin);
	}

	/**
	 * Set bottom margin.
	 */
	public function setBottomMargin($margin)
	{
		//$this->pdf->SetAutoPageBreak(true, $margin);
	}

	/**
	 * Set left margin.
	 */
	public function setLeftMargin($margin)
	{
		//$this->pdf->SetLeftMargin($margin);
	}

	/**
	 * Set right margin.
	 */
	public function setRightMargin($margin)
	{
		//$this->pdf->SetRightMargin($margin);
	}

	/**
	 * Set page size and orientation.
	 *
	 * @param string|null $format      - page format
	 * @param string      $orientation - page orientation
	 */
	public function setPageSize($format, $orientation = null)
	{
		//$this->pdf->setPageSize($format, $orientation);
	}

	/**
	 * Set language.
	 *
	 * @param $language
	 */
	public function setLanguage($language)
	{
		parent::setLanguage($language);
		//$this->pdf->setLanguage($language);
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
	 * @param array $params         - array of parameters
	 * @param bool  $defaultMargins - use default margins or custom user specified?
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
						//$this->pdf->setHeaderMargin($value);
					}
					break;
				case 'footer_height':
					if (is_numeric($value)) {
						//$this->pdf->setFooterMargin($value);
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
		//$this->pdf->SetTitle($this->parseVariables($title));
	}

	/**
	 * Set Title of the document.
	 */
	public function setAuthor($author)
	{
		//$this->pdf->SetAuthor($this->parseVariables($author));
	}

	/**
	 * Set Title of the document.
	 */
	public function setCreator($creator)
	{
		//$this->pdf->SetCreator($creator);
	}

	/**
	 * Set Title of the document.
	 */
	public function setSubject($subject)
	{
		//$this->pdf->SetSubject($this->parseVariables($subject));
	}

	/**
	 * Set Title of the document.
	 */
	public function setKeywords($keywords)
	{
		//$this->pdf->SetKeywords($this->parseVariables($keywords));
	}

	/**
	 * Set header content.
	 */
	public function setHeader($name, $header)
	{
		//$this->pdf->setHtmlHeader($header);
	}

	/**
	 * Set footer content.
	 */
	public function setFooter($name, $footer)
	{
		//$this->pdf->setHtmlFooter($footer);
	}

	/**
	 * Write html.
	 */
	public function writeHTML()
	{
		//$this->pdf->writeHTML($this->parseVariables($this->html));
		$this->pdf->loadHtml($this->parseVariables($this->html));
	}

	/**
	 * Set watermark.
	 *
	 * @param $templateModel
	 */
	public function setWaterMark($templateModel)
	{
		/*if ($templateModel->get('watermark_type') === self::WATERMARK_TYPE_IMAGE) {
			if ($templateModel->get('watermark_image')) {
				$this->pdf->setWatermarkImage($templateModel->get('watermark_image'), 0.15, 'P');
			} else {
				$this->pdf->clearWatermarkImage();
			}
		} elseif ($templateModel->get('watermark_type') === self::WATERMARK_TYPE_TEXT) {
			$this->pdf->SetWatermarkText($this->parseVariables($templateModel->get('watermark_text')), 0.15, $templateModel->get('watermark_size'), $templateModel->get('watermark_angle'));
		}*/
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
	 * @param int    $recordId
	 * @param string $moduleName
	 * @param int    $templateId
	 * @param int    $templateMainRecordId - optional if null $recordId is used
	 *
	 * @return Tcpdf current or new instance if needed
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
		\App\Language::setTemporaryLanguage($template->get('language'));
		$self->setWaterMark($template);
		$self->setLanguage($template->get('language'));
		$self->setFileName($self->parseVariables($template->get('filename')));
		//$self->pdf->setHeaderFont([$self->defaultFontFamily, '', $self->defaultFontSize]);
		//$self->pdf->setFooterFont([$self->defaultFontFamily, '', $self->defaultFontSize]);
		$self->parseParams($template->getParameters(), $template->get('margin_chkbox') !== 1);
		/*$self->pdf()->setHtmlHeader($self->parseVariables($template->getHeader()));
		$self->pdf()->AddPage($template->get('page_orientation') === 'PLL_PORTRAIT' ? 'P' : 'L', $template->get('page_format'));
		$self->pdf()->setHtmlFooter($self->parseVariables($template->getFooter()));
		$self->pdf()->writeHTML($self->parseVariables($template->getBody()));
		$self->pdf()->lastPage();*/
		$self->pdf->loadHtml($self->parseVariables($template->getBody()));
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
			$fileName = $this->getFileName() . '.pdf';
			$dest = 'I';
		}
		//$this->pdf->Output($fileName, $dest);
		$output = $this->pdf->render();
		if ($dest !== 'I') {
			return file_put_contents($fileName, $output);
		}
		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
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
