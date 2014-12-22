<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/Viewer.php';

class Vtiger_PDF_FooterViewer extends Vtiger_PDF_Viewer {

	protected $model;
	
	protected $onEveryPage = true;
	protected $onLastPage = false;
	
	function setOnEveryPage() {
		$this->onEveryPage = true;
		$this->onLastPage = false;
	}
	
	function onEveryPage() {
		return $this->onEveryPage;
	}
	
	function setOnLastPage() {
		$this->onEveryPage = false;
		$this->onLastPage = true;
	}
	
	function onLastPage() {
		return $this->onLastPage;
	}
	
	function setModel($m) {
		$this->model = $m;
	}
	
	function totalHeight($parent) {
		$height = 0.1;
		
		if($this->model && $this->onEveryPage()) {
			$pdf = $parent->getPDF();

			$contentText = $this->model->get('content');
			$height = $pdf->GetStringHeight($contentText, $parent->getTotalWidth());
		}		

		if($this->onEveryPage) return $height;
		if($this->onLastPage && $parent->onLastPage()) return $height;
		return 0;
	}
	
	function initDisplay($parent) {
		
	}

	function display($parent) {

		$pdf = $parent->getPDF();
		$footerFrame = $parent->getFooterFrame();
		
		if($this->model) {
			$targetFooterHeight = ($this->onEveryPage())? $footerFrame->h : 0;
				
			$pdf->MultiCell($footerFrame->w, $targetFooterHeight, $this->model->get('content'), 1, 'L', 0, 1, $footerFrame->x, $footerFrame->y);
		}
		
	}
	
}
