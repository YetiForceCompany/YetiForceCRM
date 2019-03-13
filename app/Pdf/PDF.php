<?php

/**
 * Abstract class for pdf generation.
 *
 * @package App\Pdf
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace App\Pdf;

/**
 * Class PDF.
 */
abstract class PDF
{
	protected $pdf;
	protected $charset;
	protected $library;
	protected $templateId;
	protected $recordId;
	protected $moduleName;
	protected $html;
	protected $header;
	protected $footer;
	protected $language;
	protected $fileName;

	/**
	 * Returns pdf library object.
	 */
	abstract public function pdf();

	/**
	 * Set input charset.
	 *
	 * @param string $charset
	 *
	 * @return $this
	 */
	abstract public function setInputCharset(string $charset);

	/**
	 * Get input charset.
	 *
	 * @return string
	 */
	abstract public function getInputCharset();

	/**
	 * Returns library name.
	 *
	 * @return string
	 */
	abstract public function getLibraryName();

	/**
	 * Sets library name.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	abstract public function setLibraryName(string $name);

	/**
	 * Returns template id.
	 *
	 * @return int|string
	 */
	abstract public function getTemplateId();

	/**
	 * Sets the template id.
	 *
	 * @param int|string $id
	 *
	 * @return $this
	 */
	abstract public function setTemplateId($id);

	/**
	 * Returns record id.
	 *
	 * @return int|string
	 */
	abstract public function getRecordId();

	/**
	 * Sets the record id.
	 *
	 * @param int|string $id
	 *
	 * @return $this
	 */
	abstract public function setRecordId($id);

	/**
	 * Returns module name.
	 *
	 * @return string
	 */
	abstract public function getModuleName();

	/**
	 * Sets module name.
	 *
	 * @param string $name
	 *                     return $this
	 */
	abstract public function setModuleName(string $name);

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
	 * Parse and set options.
	 *
	 * @param array $params
	 *
	 * @return $this
	 */
	abstract public function parseParams(array $params);

	// meta attributes

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
	 * Set footer content.
	 *
	 * @param string $footerHtml
	 *
	 * @return $this
	 */
	abstract public function setFooter(string $footerHtml);

	/**
	 * Set watermark.
	 *
	 * @param \Vtiger_PDF_Model $templateModel
	 *
	 * @return $this
	 */
	abstract public function setWatermark(\Vtiger_PDF_Model $templateModel);

	/**
	 * Load HTML content for exporting to PDF.
	 *
	 * @param string $html
	 *
	 * @return $this
	 */
	abstract public function loadHTML(string $html);

	/**
	 * Output content to PDF.
	 */
	abstract public function output();

	/**
	 * Get template language.
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Set template language.
	 *
	 * @param string $language
	 *
	 * @return $this
	 */
	public function setLanguage(string $language)
	{
		$this->language = $language;
		return $this;
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
	 * Export record to PDF file.
	 *
	 * @param int    $recordId   - id of a record
	 * @param string $moduleName - name of records module
	 * @param int    $templateId - id of pdf template
	 * @param string $filePath   - path name for saving pdf file
	 * @param string $saveFlag   - save option flag
	 */
	abstract public function export($recordId, $moduleName, $templateId, $filePath = '', $saveFlag = '');
}
