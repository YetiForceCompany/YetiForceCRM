<?php

/**
 * Abstract driver file for PDF generation.
 *
 * @package App\Pdf
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Pdf\Drivers;

/**
 * Abstract driver class for pdf generation.
 */
abstract class Base
{
	/** @var string Driver name */
	const DRIVER_NAME = self::DRIVER_NAME;

	/** @var string Watermark text type */
	const WATERMARK_TYPE_TEXT = 0;

	/** @var string Watermark image type */
	const WATERMARK_TYPE_IMAGE = 1;

	/** @var array Default margins in mm. */
	public $defaultMargins = [
		'left' => 30,
		'right' => 30,
		'top' => 40,
		'bottom' => 40,
		'header' => 10,
		'footer' => 10,
	];

	/** @var object PDF generator instance. */
	protected $pdf;

	/** @var string Charset. */
	protected $charset;

	/** @var \Vtiger_PDF_Model PDF model instance. */
	protected $template;

	/** @var string HTML body. */
	protected $body;

	/** @var string HTML header. */
	protected $header;

	/** @var string HTML footer. */
	protected $footer;

	/** @var string HTML watermark. */
	protected $watermark;

	/** @var int Header margin. */
	protected $headerMargin = 10;

	/** @var int Footer margin . */
	protected $footerMargin = 10;

	/** @var string File name. */
	protected $fileName;

	/** @var string Default font. */
	protected $font;

	/** @var mixed Default font size. */
	protected $fontSize;

	/**
	 * Check if the driver is active.
	 *
	 * @return bool
	 */
	abstract public static function isActive(): bool;

	/**
	 * Set document margins.
	 *
	 * @param array $margins ['top'=>40,'bottom'=>40,'left'=>30,'right'=>30,'header'=>10,'footer'=>10]
	 *
	 * @return $this
	 */
	abstract public function setMargins(array $margins);

	/**
	 * Set top margin.
	 *
	 * @param float $margin
	 *
	 * @return $this
	 */
	abstract public function setTopMargin(float $margin);

	/**
	 * Set bottom margin.
	 *
	 * @param float $margin
	 *
	 * @return $this
	 */
	abstract public function setBottomMargin(float $margin);

	/**
	 * Set left margin.
	 *
	 * @param float $margin
	 *
	 * @return $this
	 */
	abstract public function setLeftMargin(float $margin);

	/**
	 * Set right margin.
	 *
	 * @param float $margin
	 *
	 * @return $this
	 */
	abstract public function setRightMargin(float $margin);

	/**
	 * Set header margin.
	 *
	 * @param float $margin
	 *
	 * @return $this
	 */
	abstract public function setHeaderMargin(float $margin);

	/**
	 * Set footer margin.
	 *
	 * @param float $margin
	 *
	 * @return $this
	 */
	abstract public function setFooterMargin(float $margin);

	/**
	 * Set page size and orientation.
	 *
	 * @param string $format
	 * @param string $orientation
	 *
	 * @return $this
	 */
	abstract public function setPageSize(string $format, string $orientation = null);

	/**
	 * Set Title of the document.
	 *
	 * @param string $title
	 *
	 * @return $this
	 */
	abstract public function setTitle(string $title);

	/**
	 * Set Title of the document.
	 *
	 * @param string $author
	 *
	 * @return $this
	 */
	abstract public function setAuthor(string $author);

	/**
	 * Set Title of the document.
	 *
	 * @param string $creator
	 *
	 * @return $this
	 */
	abstract public function setCreator(string $creator);

	/**
	 * Set Title of the document.
	 *
	 * @param string $subject
	 *
	 * @return $this
	 */
	abstract public function setSubject(string $subject);

	/**
	 * Set Title of the document.
	 *
	 * @param string[] $keywords
	 *
	 * @return $this
	 */
	abstract public function setKeywords(array $keywords);

	/**
	 * Set header content.
	 *
	 * @param string $headerHtml
	 *
	 * @return $this
	 */
	abstract public function setHeader(string $headerHtml);

	/**
	 * Load watermark.
	 *
	 * @param string $headerHtml
	 *
	 * @return $this
	 */
	abstract public function loadWatermark();

	/**
	 * Set footer content.
	 *
	 * @param string $footerHtml
	 *
	 * @return $this
	 */
	abstract public function setFooter(string $footerHtml);

	/**
	 * Output content to PDF.
	 *
	 * @param string $filePath Path name for saving pdf file
	 * @param string $mode     Output mode, default: `D`,  `I` = show in browser , `D` = download  , `F` = save to file
	 *
	 * @return void
	 */
	abstract public function output($filePath = '', $mode = 'D'): void;

	/**
	 * Load template data to PDF instance.
	 *
	 * @return void
	 */
	public function loadTemplateData(): void
	{
		$this->setPageSize($this->template->getFormat(), $this->template->getOrientation());
		if (1 !== $this->template->get('margin_chkbox')) {
			$this->setMargins([
				'top' => $this->template->get('margin_top'),
				'right' => $this->template->get('margin_right'),
				'bottom' => $this->template->get('margin_bottom'),
				'left' => $this->template->get('margin_left'),
				'header' => $this->template->get('header_height'),
				'footer' => $this->template->get('footer_height'),
			]);
		}
		$this->loadWatermark();
		$this->setFileName($this->template->parseVariables($this->template->get('filename')) ?? '');
		$this->parseParams($this->template->getParameters());
		$this->setBody($this->template->parseVariables($this->template->getBody() ?? ''));
		$this->setHeader($this->template->parseVariables($this->template->getHeader() ?? ''));
		$this->setFooter($this->template->parseVariables($this->template->getFooter() ?? ''));
	}

	/**
	 * Set PDF template model.
	 *
	 * @param \Vtiger_PDF_Model $template
	 *
	 * @return $this
	 */
	public function setTemplate(\Vtiger_PDF_Model $template)
	{
		$this->template = $template;
		return $this;
	}

	/**
	 * Get PDF template model.
	 *
	 * @return \Vtiger_PDF_Model
	 */
	public function getTemplate(): \Vtiger_PDF_Model
	{
		return $this->template;
	}

	/**
	 * Get input charset.
	 *
	 * @return string
	 */
	public function getInputCharset(): string
	{
		return $this->charset;
	}

	/**
	 * Set input charset.
	 *
	 * @param string $charset
	 *
	 * @return $this
	 */
	public function setInputCharset(string $charset)
	{
		$this->charset = $charset;
		return $this;
	}

	/**
	 * Set HTML body content for exporting to PDF.
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	public function setBody(string $html)
	{
		$this->body = $html;
		return $this;
	}

	/**
	 * Get HTML body content.
	 *
	 * @return string
	 */
	public function getBody(): string
	{
		return $this->body;
	}

	/**
	 * Get pdf filename.
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->fileName;
	}

	/**
	 * Set pdf filename.
	 *
	 * @param string $fileName
	 *
	 * @return $this
	 */
	public function setFileName(string $fileName)
	{
		$this->fileName = \App\Fields\File::sanitizeUploadFileName($fileName);
		return $this;
	}

	/**
	 * Set font.
	 *
	 * @param string $family
	 * @param mixed  $size
	 *
	 * @return $this
	 */
	public function setFont(string $family, $size)
	{
		$this->font = $family;
		$this->fontSize = $size;
		return $this;
	}

	/**
	 * Parse and set options.
	 *
	 * @param array $params
	 *
	 * @return $this
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
}
