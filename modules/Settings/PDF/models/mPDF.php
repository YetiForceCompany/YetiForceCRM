<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
vimport('~/libraries/mPDF/mpdf.php');

/**
 * Class using mPDF as a PDF creator
 */
class Settings_PDF_mPDF_Model extends Settings_PDF_AbstractPDF_Model
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
	public function setTopMargin($margin) {
		$this->pdf->tMargin = $margin;
	}
	
	/**
	 * Set bottom margin
	 */
	public function setBottomMargin($margin) {
		$this->pdf->bMargin = $margin;
	}
	
	/**
	 * Set left margin
	 */
	public function setLeftMargin($margin) {
		$this->pdf->lMargin = $margin;
	}
	
	/**
	 * Set right margin
	 */
	public function setRightMargin($margin) {
		$this->pdf->rMargin = $margin;
	}

	/**
	 * Set page size and orientation
	 * @param <String> $format - page format
	 * @param <String> $orientation - page orientation
	 */
	public function setPageSize($format, $orientation)
	{
		$this->pdf->_setPageSize($format, $this->pageOrientation[$orientation]);
	}

	/**
	 * Set header content
	 */
	public function setHeader($name, $header) {
		$this->pdf->DefHTMLHeaderByName($name, $header);
		$this->pdf->SetHTMLHeaderByName($name);
	}

	/**
	 * Set footer content
	 */
	public function setFooter($name, $footer) {
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
	public function output($fileName='', $dest='' )
	{
		$this->pdf->WriteHTML($this->html);
		$this->pdf->Output($fileName, $dest);
	}

	public function writeHTML()
	{
		$this->pdf->WriteHTML($this->html);
	}
}
