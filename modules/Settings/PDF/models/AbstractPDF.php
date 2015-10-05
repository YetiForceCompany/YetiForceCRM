<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

/**
 * Abstract class for connection to bank currency exchange rates
 */
abstract class Settings_PDF_AbstractPDF_Model
{

	protected $pdf;
	protected $library;
	protected $templateId;
	protected $recordId;
	protected $moduleName;
	protected $html;
	protected $header;
	protected $footer;

	/**
	 * Returns library name
	 */
	abstract public function getLibraryName();

	/**
	 * Sets library name
	 */
	abstract public function setLibraryName($name);

	/**
	 * Returns template id
	 */
	abstract public function getTemplateId();

	/**
	 * Sets the template id
	 */
	abstract public function setTemplateId($id);

	/**
	 * Returns record id
	 */
	abstract public function getRecordId();

	/**
	 * Sets the record id
	 */
	abstract public function setRecordId($id);

	/**
	 * Returns module name
	 */
	abstract public function getModuleName();

	/**
	 * Sets module name
	 */
	abstract public function setModuleName($name);
	
	/**
	 * Sets document margins
	 */
	public function setMargins($top, $right, $bottom, $left) {
		$this->setTopMargin($top);
		$this->setBottomMargin($bottom);
		$this->setLeftMargin($left);
		$this->setRightMargin($right);
	}
	
	/**
	 * Set top margin
	 */
	abstract public function setTopMargin($margin);
	
	/**
	 * Set bottom margin
	 */
	abstract public function setBottomMargin($margin);
	
	/**
	 * Set left margin
	 */
	abstract public function setLeftMargin($margin);
	
	/**
	 * Set right margin
	 */
	abstract public function setRightMargin($margin);

	/**
	 * Set header content
	 */
	abstract public function setHeader($name, $header);

	/**
	 * Set footer content
	 */
	abstract public function setFooter($name, $footer);

	/**
	 * Load HTML content for exporting to PDF
	 */
	abstract public function loadHTML($html);

	/**
	 * Output content to PDF
	 */
	abstract public function output();
}
