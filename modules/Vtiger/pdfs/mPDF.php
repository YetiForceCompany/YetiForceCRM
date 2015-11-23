<?php
/**
 * Class using mPDF as a PDF creator
 * @package YetiForce.PDF
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
vimport('~/libraries/mPDF/mpdf.php');

class Vtiger_mPDF_Pdf extends Vtiger_AbstractPDF_Pdf
{

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
	public function __construct()
	{
		$this->setLibraryName('mPDF');
		$this->pdf = new mPDF();
		//$this->pdf->debugfonts = true;
		//$this->pdf->debug = true;
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
	 * @param <String> $format - page format
	 * @param <String> $orientation - page orientation
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
					$this->setTopMargin($value);
					break;

				case 'margin-bottom':
					$this->setBottomMargin($value);
					break;

				case 'margin-left':
					$this->setLeftMargin($value);
					break;

				case 'margin-right':
					$this->setRightMargin($value);
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
		$this->pdf->WriteHTML($this->html);
		$this->pdf->Output($fileName, $dest);
	}

	public function writeHTML()
	{
		$this->pdf->WriteHTML($this->html);
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
		$pdf = new self();
		$pdf->setTemplateId($templateId);
		$pdf->setRecordId($recordId);
		$pdf->setModuleName($moduleName);

		$template = Vtiger_PDF_Model::getInstanceById($templateId, $moduleName);
		$template->setMainRecordId($recordId);

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
