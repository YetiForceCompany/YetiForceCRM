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

class Vtiger_PDF_ContentViewer extends Vtiger_PDF_Viewer {

	protected $cells;
	
	protected $contentModels = array();
	protected $contentSummaryModel;
	protected $watermarkModel;
	
	function addContentModel($m) {
		$this->contentModels[] = $m;
	}
	
	function setContentModels($m) {
		if(!is_array($m)) $m = array($m);
		$this->contentModels = $m;
	}
	
	function setSummaryModel($m) {
		$this->contentSummaryModel = $m;
	}

	function setWatermarkModel($m) {
		$this->watermarkModel = $m;
	}
	
	function totalHeight($parent) {
		return 0; // Variable height
	}
	
	function initDisplay($parent) {
		$pdf = $parent->getPDF();
		$contentFrame = $parent->getContentFrame();

		$pdf->MultiCell($contentFrame->w, $contentFrame->h, "", 1, 'L', 0, 1, $contentFrame->x, $contentFrame->y);
	}

	function displayWatermark($parent) {
		$pdf = $parent->getPDF();
		$contentFrame = $parent->getContentFrame();
		
		if($this->watermarkModel) {
			$content = $this->watermarkModel->get('content');

			$currentFontSize = $pdf->getFontSize();
			$pdf->SetFont('','B',40);
			$pdf->SetTextColor(240,240,240);

			$contentW = $pdf->GetStringWidth($content);
			$contentH = $pdf->GetStringHeight($content, $contentFrame->w);
			$contentLineY = $contentFrame->y + ($contentFrame->h/2.0) - ($contentH/2.0);
			$contentLineX = $contentFrame->x + ($contentFrame->w/2.0) - ($contentW/2.0);

			$pdf->Text($contentLineX, $contentLineY, $content);
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','',$currentFontSize);
		}
	}
	
	function display($parent) {
		$models = $this->contentModels;

		$totalModels = count($models);
		$pdf = $parent->getPDF();
		
		$parent->createPage();
		$contentFrame = $parent->getContentFrame();

		$contentLineX = $contentFrame->x; $contentLineY = $contentFrame->y;
		
		for ($index = 0; $index < $totalModels; ++$index) {
			$model = $models[$index];			
			
			$contentHeight = $pdf->GetStringHeight($model->get('content'), $contentFrame->w);			
			if($contentLineY + $contentHeight > ($contentFrame->h+$contentFrame->y)) {
				$parent->createPage();
				
				$contentFrame = $parent->getContentFrame();
				$contentLineX = $contentFrame->x; $contentLineY = $contentFrame->y;
			}			
				
			$pdf->MultiCell($contentFrame->w, $contentHeight, $model->get('content'), 1, 'L', 0, 1, $contentLineX, $contentLineY);			
			$contentLineY = $pdf->GetY();			
		}
		
		// Add last page to take care of footer display
		$parent->createLastPage();
	}
	
}
