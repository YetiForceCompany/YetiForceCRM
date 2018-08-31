<?php

Vtiger_Loader::includeOnce('~/vendor/tecnickcom/tcpdf/tcpdf.php');

class Vtiger_Yftcpdf_Pdf extends TCPDF
{
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
		'alpha' => 0
	];

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

	/*public function SetFont($family, $style = '', $size = null, $fontfile = '', $subset = 'default', $out = true)
	{
		$this->setFontSubsetting(true);
		parent::SetFont($family, $style, $size, $fontfile, $subset, $out);
	}*/

	public function setLanguage($lang)
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
	 * Set watermark text.
	 *
	 * @param string $text
	 * @param float  $alpha
	 */
	public function setWatermarkText(string $text, float $alpha)
	{
		/*$width = $this->GetStringWidth($text, $this->footerFontFamily, $this->headerFontVariation, $this->headerFontSize);
		$height = $this->GetStringHeight($this->GetPageWidth(), $text, $this->footerFontFamily, $this->headerFontVariation, $this->headerFontSize);
		$image = imagecreate($width, $height);

		$this->watermark['imagePath'] = $filePath;
		$this->watermark['alpha'] = $alpha;
		$this->watermark['render'] = true;*/
	}

	/**
	 * {@inheritdoc}
	 */
	public function Header()
	{
		$this->setFontSubsetting(true);
		$this->setFont($this->headerFontFamily, $this->headerFontVariation, $this->headerFontSize);
		if (!empty($this->watermark['render'])) {
			$pageBreakMargin = $this->getBreakMargin();
			$pageBreak = $this->AutoPageBreak;
			$oldX = $this->GetX();
			$oldY = $this->GetY();
			$oldAplha = $this->GetAlpha();
			$this->SetAlpha($this->watermark['alpha']);
			$this->SetAutoPageBreak(false, 0);
			$this->Image($this->watermark['image']['path'], 0, 0, $this->watermark['image']['width'], $this->watermark['image']['height'], '', '', 'M', true, 300, 'C', false, false, 0, false, false, true);
			$this->SetAutoPageBreak($pageBreak, $pageBreakMargin);
			$this->setPageMark();
			$this->SetX($oldX);
			$this->SetY($oldY);
			$this->SetAlpha($oldAplha);
		}
		$this->writeHTML($this->getHtmlHeader());
	}

	/**
	 * {@inheritdoc}
	 */
	public function Footer()
	{
		$this->setFont($this->footerFontFamily, $this->footerFontVariation, $this->footerFontSize);
		$this->writeHTML($this->getHtmlFooter());
	}
}
