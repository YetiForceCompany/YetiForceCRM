<?php
/**
 * Class using mPDF as a PDF creator
 * @package YetiForce.PDF
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
vimport('~/libraries/mPDF/mpdf.php');

class Vtiger_mPDF_Pdf extends Vtiger_AbstractPDF_Pdf
{

	const WATERMARK_TYPE_TEXT = 0;
	const WATERMARK_TYPE_IMAGE = 1;

	public $pageOrientation = ['PLL_PORTRAIT' => 'P', 'PLL_LANDSCAPE' => 'L'];

	/**
	 * Returns pdf library object
	 */
	public function pdf()
	{
		return $this->pdf;
	}

	/**
	 * Constructor
	 */
	public function __construct($mode = '', $format = 'A4', $defaultFontSize = 0, $defaultFont = '', $orientation = 'P', $leftMargin = 15, $rightMargin = 15, $topMargin = 16, $bottomMargin = 16, $headerMargin = 9, $footerMargin = 9)
	{
		$this->setLibraryName('mPDF');
		$this->pdf = new mPDF($mode, $format, $defaultFontSize, $defaultFont, $leftMargin, $rightMargin, $topMargin, $bottomMargin, $headerMargin, $footerMargin, $orientation);
	}

	/**
	 * Returns bank name
	 */
	public function getLibraryName()
	{
		return $this->library;
	}

	/**
	 * Sets library name
	 */
	public function setLibraryName($name)
	{
		$this->library = $name;
	}

	/**
	 * Returns template id
	 */
	public function getTemplateId()
	{
		return $this->templateId;
	}

	/**
	 * Sets the template id
	 */
	public function setTemplateId($id)
	{
		$this->templateId = $id;
	}

	/**
	 * Returns record id
	 */
	public function getRecordId()
	{
		return $this->recordId;
	}

	/**
	 * Sets the record id
	 */
	public function setRecordId($id)
	{
		$this->recordId = $id;
	}

	/**
	 * Returns module name
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}

	/**
	 * Sets module name
	 */
	public function setModuleName($name)
	{
		$this->moduleName = $name;
	}

	/**
	 * Set top margin
	 */
	public function setTopMargin($margin)
	{
		$this->pdf->tMargin = $margin;
	}

	/**
	 * Set bottom margin
	 */
	public function setBottomMargin($margin)
	{
		$this->pdf->bMargin = $margin;
	}

	/**
	 * Set left margin
	 */
	public function setLeftMargin($margin)
	{
		$this->pdf->lMargin = $margin;
	}

	/**
	 * Set right margin
	 */
	public function setRightMargin($margin)
	{
		$this->pdf->rMargin = $margin;
	}

	/**
	 * Set page size and orientation
	 * @param string $format - page format
	 * @param string $orientation - page orientation
	 */
	public function setPageSize($format, $orientation)
	{
		$orientation = $this->pageOrientation[$orientation];
		if ($orientation === 'L') {
			$format .= '-L';
		} else {
			$format .= '-P';
		}
		$this->pdf->_setPageSize($format, $orientation);
	}

	/**
	 * Parse and set options
	 * @param <Array> $params - array of parameters
	 */
	public function parseParams(array &$params)
	{
		foreach ($params as $param => &$value) {
			switch ($param) {
				case 'page_format':
					$pageOrientation = '';
					if (array_key_exists('page_orientation', $params)) {
						$pageOrientation = $params['page_orientation'];
					}
					$this->setPageSize($value, $pageOrientation);
					break;

				case 'margin-top':
					if (is_numeric($value)) {
						$this->setTopMargin($value);
					}
					break;

				case 'margin-bottom':
					if (is_numeric($value)) {
						$this->setBottomMargin($value);
					}
					break;

				case 'margin-left':
					if (is_numeric($value)) {
						$this->setLeftMargin($value);
					}
					break;

				case 'margin-right':
					if (is_numeric($value)) {
						$this->setRightMargin($value);
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
			}
		}
	}

	// meta attributes
	/**
	 * Set Title of the document
	 */
	public function setTitle($title)
	{
		$this->pdf->SetTitle($title);
	}

	/**
	 * Set Title of the document
	 */
	public function setAuthor($author)
	{
		$this->pdf->SetAuthor($author);
	}

	/**
	 * Set Title of the document
	 */
	public function setCreator($creator)
	{
		$this->pdf->SetCreator($creator);
	}

	/**
	 * Set Title of the document
	 */
	public function setSubject($subject)
	{
		$this->pdf->SetSubject($subject);
	}

	/**
	 * Set Title of the document
	 */
	public function setKeywords($keywords)
	{
		$this->pdf->SetKeywords($keywords);
	}

	/**
	 * Set header content
	 */
	public function setHeader($name, $header)
	{
		$this->pdf->DefHTMLHeaderByName($name, $header);
		$this->pdf->SetHTMLHeaderByName($name, '', true);
	}

	/**
	 * Set footer content
	 */
	public function setFooter($name, $footer)
	{
		$this->pdf->DefHTMLFooterByName($name, $footer);
		$this->pdf->SetHTMLFooterByName($name);
	}

	public function loadHTML($html)
	{
		$this->html = $html;
	}

	/**
	 * Output content to PDF
	 */
	public function output($fileName = '', $dest = '')
	{
		if (empty($fileName)) {
			$fileName = $this->getFileName() . '.pdf';
			$dest = 'I';
		}
		$pages = explode('<div style="page-break-after:always;"><span style="display:none;"> </span></div>', $this->html);
		foreach ($pages as $page) {
			$this->pdf->AddPage();
			$this->pdf->WriteHTML($page);
		}
		$this->pdf->Output($fileName, $dest);
	}

	public function writeHTML()
	{
		$this->pdf->WriteHTML($this->html);
	}

	public function setWaterMark($templateModel)
	{
		if ($templateModel->get('watermark_type') === self::WATERMARK_TYPE_TEXT) {
			$this->pdf->SetWatermarkText($templateModel->get('watermark_text'), 0.15);
			$this->pdf->showWatermarkText = true;
		} elseif ($templateModel->get('watermark_type') === self::WATERMARK_TYPE_IMAGE) {
			$this->pdf->SetWatermarkImage($templateModel->get('watermark_image'), 0.15, 'P');
			$this->pdf->showWatermarkImage = true;
		}
	}

	/**
	 * Export record to PDF file
	 * @param int $recordId - id of a record
	 * @param string $moduleName - name of records module
	 * @param int $templateId - id of pdf template
	 * @param string $filePath - path name for saving pdf file
	 * @param string $saveFlag - save option flag
	 */
	public function export($recordId, $moduleName, $templateId, $filePath = '', $saveFlag = '')
	{
		$template = Vtiger_PDF_Model::getInstanceById($templateId, $moduleName);
		$template->setMainRecordId($recordId);

		$pageOrientation = $template->get('page_orientation') === 'PLL_PORTRAIT' ? 'P' : 'L';
		if ($template->get('margin_chkbox') == 1) {
			$pdf = new self('utf-8', $template->get('page_format'), 0, '', $pageOrientation);
		} else {
			$pdf = new self(
				'utf-8', $template->get('page_format'), 0, '', $pageOrientation, $template->get('margin_left'), $template->get('margin_right'), $template->get('margin_top'), $template->get('margin_bottom'), $template->get('header_height'), $template->get('footer_height')
			);
		}
		$pdf->setTemplateId($templateId);
		$pdf->setRecordId($recordId);
		$pdf->setModuleName($moduleName);
		$pdf->setWaterMark($template);
		$pdf->setLanguage($template->get('language'));
		$pdf->setFileName($template->get('filename'));

		$origLanguage = vglobal('default_language');
		vglobal('default_language', $template->get('language'));

		$pdf->parseParams($template->getParameters());
		$pdf->setHeader('Header', $template->getHeader());
		$pdf->setFooter('Footer', $template->getFooter());
		$html = $template->getBody();

		$pdf->loadHTML($html);

		vglobal('default_language', $origLanguage);

		$pdf->output($filePath, $saveFlag);
	}
}
