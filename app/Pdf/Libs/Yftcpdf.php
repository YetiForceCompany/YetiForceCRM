<?php

namespace App\Pdf\Libs;

\Vtiger_Loader::includeOnce('~/vendor/tecnickcom/tcpdf/tcpdf.php');

/**
 * Ytcpdf class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Yftcpdf extends \TCPDF
{
	/**
	 * Css styles declaration.
	 *
	 * @var array
	 */
	protected $cssStyles = [
		'' => '
			.header-table {
				width: 100%;
			    color: #ffffff;
			    text-align: center;
			    font-family: dejavusans;
			    font-size: 16px;
			    background-color: #4a5364;
			}
		'
	];

	/**
	 * Current CSS style name (for current page - AddPage).
	 *
	 * @var string
	 */
	protected $currentCssStyleName = '';

	/**
	 * Header html.
	 *
	 * @var string
	 */
	protected $htmlHeader = '';

	/**
	 * Footer html.
	 *
	 * @var string
	 */
	protected $htmlFooter = '';

	/**
	 * Header font.
	 *
	 * @var string
	 */
	protected $headerFontFamily = 'dejavusans';

	/*
	 * Header font variation (bold, italic...)
	 * @var string
	 */
	protected $headerFontVariation = ''; // B I etc

	/**
	 * Header font size.
	 *
	 * @var int
	 */
	protected $headerFontSize = 10;

	/**
	 * Footer font.
	 *
	 * @var string
	 */
	protected $footerFontFamily = 'dejavusans';

	/*
	 * Footer font variation (bold, italic...)
	 * @var string
	 */
	protected $footerFontVariation = ''; // B I etc

	/**
	 * Footer font size.
	 *
	 * @var int
	 */
	protected $footerFontSize = 10;

	/**
	 * Watermark image.
	 *
	 * @var array
	 */
	protected $watermark = [
		'render' => false,
		'image' => [
			'path' => '',
			'width' => '',
			'height' => '',
		],
		'text' => [
			'text' => '',
			'fontSize' => 10,
			'angle' => 0
		],
		'alpha' => 0
	];

	/**
	 * Set current pdf css style.
	 *
	 * @param string $style
	 */
	public function setCssStyle(string $style, $name = '')
	{
		$this->cssStyles[$name] = $style;
	}

	/**
	 * Get CSS styles for specified name or default if name is empty.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getCssStyle(string $name = '')
	{
		return '<style>' . $this->cssStyles[$name] . '</style>';
	}

	/**
	 * Set html header.
	 *
	 * @param string $html
	 */
	public function setHtmlHeader(string $html)
	{
		$this->htmlHeader = $html;
	}

	/**
	 * Get html header.
	 *
	 * @return string
	 */
	public function getHtmlHeader()
	{
		return $this->htmlHeader;
	}

	/**
	 * Set html footer.
	 *
	 * @param string $html
	 */
	public function setHtmlFooter(string $html)
	{
		$this->htmlFooter = $html;
	}

	/**
	 * Get html footer.
	 *
	 * @return string
	 */
	public function getHtmlFooter()
	{
		return $this->htmlFooter;
	}

	/**
	 * Set header font.
	 *
	 * @param string $font
	 */
	public function setHeaderFontFamily(string $font)
	{
		$this->headerFontFamily = $font;
	}

	/**
	 * Set header font variation (bold, italic ...).
	 *
	 * @param string $variation
	 */
	public function setHeaderFontVariation(string $variation)
	{
		$this->headerFontVariation = $variation;
	}

	/**
	 * Set header font size.
	 *
	 * @param int $size
	 */
	public function setHeaderFontSize(int $size)
	{
		$this->headerFontSize = $size;
	}

	/**
	 * Set header font.
	 *
	 * @param string $font
	 */
	public function setFooterFontFamily(string $font)
	{
		$this->footerFontFamily = $font;
	}

	/**
	 * Set header font variation (bold, italic ...).
	 *
	 * @param string $variation
	 */
	public function setFooterFontVariation(string $variation)
	{
		$this->footerFontVariation = $variation;
	}

	/**
	 * Set header font size.
	 *
	 * @param int $size
	 */
	public function setFooterFontSize(int $size)
	{
		$this->footerFontSize = $size;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setLeftMargin($margin)
	{
		parent::SetLeftMargin($margin);
		$this->original_lMargin = $margin;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRightMargin($margin)
	{
		parent::SetRightMargin($margin);
		$this->original_rMargin = $margin;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setMargins($left, $top, $right = -1, $keepmargins = false)
	{
		//Set left, top and right margins
		$this->SetLeftMargin($left);
		$this->SetTopMargin($top);
		if ($right === -1) {
			$right = $left;
		}
		$this->SetRightMargin($right);
		$this->original_lMargin = $this->lMargin;
		$this->original_rMargin = $this->rMargin;
	}

	/**
	 * Set language.
	 *
	 * @param string $lang
	 */
	public function setLanguage(string $lang)
	{
		$this->setFontSubsetting(true);
		$this->setFont($this->FontFamily, $this->FontStyle, $this->FontSize);
		$lg = [
			'a_meta_charset' => 'UTF-8',
			'a_meta_dir' => 'ltl',
			'a_meta_language' => $lang,
			'w_page' => 'page'
		];
		$this->setLanguageArray($lg);
	}

	/**
	 * Set watermark image.
	 *
	 * @param string $filePath
	 * @param float  $alpha
	 */
	public function setWatermarkImage(string $filePath, float $alpha)
	{
		$this->watermark['image']['path'] = $filePath;
		$this->watermark['alpha'] = $alpha;
		$this->watermark['render'] = true;
		$imageSize = getimagesize($filePath);
		$this->watermark['image']['width'] = $imageSize[0];
		$this->watermark['image']['height'] = $imageSize[1];
	}

	/**
	 * Clear watermark image.
	 */
	public function clearWatermarkImage()
	{
		$this->watermark['image'] = [
			'path' => '',
			'width' => '',
			'height' => '',
		];
	}

	/**
	 * Set watermark text.
	 *
	 * @param string $text
	 * @param float  $alpha
	 */
	public function setWatermarkText(string $text, float $alpha, float $fontSize, float $angle)
	{
		$this->clearWatermarkImage();
		$this->watermark['text']['text'] = $text;
		$this->watermark['text']['fontSize'] = $fontSize;
		$this->watermark['text']['angle'] = $angle;
		$this->watermark['alpha'] = $alpha;
		$this->watermark['render'] = true;
	}

	/**
	 * Replace pdf variables like '{nbn}' with TCPDF variables - this can't be done before TCPDF instance is created.
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public function replacePdfVariables(string $str)
	{
		return str_replace(['{nb}', '{PAGENO}'], [$this->getAliasNbPages(), $this->getAliasNumPage()], $str);
	}

	/**
	 * {@inheritdoc}
	 */
	public function header()
	{
		if (!empty($this->watermark['render'])) {
			$pageBreakMargin = $this->getBreakMargin();
			$pageBreak = $this->AutoPageBreak;
			$oldX = $this->GetX();
			$oldY = $this->GetY();
			$oldAplha = $this->GetAlpha();
			$this->SetAlpha($this->watermark['alpha']);
			$this->SetAutoPageBreak(false, $this->getFooterMargin());
			if (!empty($this->watermark['image']['path'])) {
				$this->Image(
					$this->watermark['image']['path'], // filename
					0,  // x
					$this->tMargin,  // y
					$this->watermark['image']['width'], // width
					$this->watermark['image']['height'], // height
					'', // type jpg png
					'', // url link
					'M', // align
					true,// resize
					300, // dpi
					'C', // palign
					false, // is mask
					false, // img mask
					0, // border
					false, // fitbox
					false, // hidden
					true // fit on page
				);
			} elseif (!empty($this->watermark['text']['text'])) {
				$this->setFontSubsetting(true);
				$this->setFont($this->headerFontFamily, $this->headerFontVariation, $this->watermark['text']['fontSize']);
				$this->StartTransform();
				if ((int) $this->watermark['text']['angle']) {
					$this->Rotate(-(float) $this->watermark['text']['angle']);
				}
				$this->Text($this->lMargin, $this->tMargin, $this->watermark['text']['text']);
				$this->StopTransform();
				$this->setFont($this->headerFontFamily, $this->headerFontVariation, $this->headerFontSize);
			}
			$this->SetAutoPageBreak($pageBreak, $pageBreakMargin);
			$this->setPageMark();
			$this->SetX($oldX);
			$this->SetY($oldY);
			$this->SetAlpha($oldAplha);
		}
		if ($header = $this->getHtmlHeader()) {
			$this->setFontSubsetting(true);
			$this->setFont($this->headerFontFamily, $this->headerFontVariation, $this->headerFontSize);
			$this->writeHTML($this->replacePdfVariables($header));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function footer()
	{
		if ($footer = $this->getHtmlFooter()) {
			$this->setFont($this->footerFontFamily, $this->footerFontVariation, $this->footerFontSize);
			$this->writeHTML($this->replacePdfVariables($footer));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function addPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false, $cssStyleName = '')
	{
		if (!empty(func_get_args()['cssStyleName'])) {
			$this->currentCssStyleName = $cssStyleName;
		}
		parent::AddPage($orientation, $format, $keepmargins, $tocpage);
	}

	/**
	 * {@inheritdoc}
	 */
	public function writeHTML($html, $ln = true, $fill = false, $reseth = false, $cell = false, $align = '')
	{
		parent::writeHTML($this->getCssStyle($this->currentCssStyleName) . $html, $ln, $fill, $reseth, $cell, $align);
	}
}
