<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once dirname(__FILE__) . '/../viewers/ContentViewer.php';

class Vtiger_PDF_InventoryContentViewer extends Vtiger_PDF_ContentViewer {

	protected $headerRowHeight = 8;
	protected $onSummaryPage   = false;

	function __construct() {
		// NOTE: General A4 PDF width ~ 189 (excluding margins on either side)
			
		$this->cells = array( // Name => Width
			'Code'    => 30,
			'Name'    => 55,
			'Quantity'=> 20,
			'Price'   => 20,
			'Discount'=> 19,
			'Tax'     => 16,
			'Total'   => 30
		);
	}
	
	function initDisplay($parent) {

		$pdf = $parent->getPDF();
		$contentFrame = $parent->getContentFrame();

		$pdf->MultiCell($contentFrame->w, $contentFrame->h, "", 1, 'L', 0, 1, $contentFrame->x, $contentFrame->y);
		
		// Defer drawing the cell border later.
		if(!$parent->onLastPage()) {
			$this->displayWatermark($parent);
		}
		
		// Header	
		$offsetX = 0;
		$pdf->SetFont('','B');
		foreach($this->cells as $cellName => $cellWidth) {
			$cellLabel = ($this->labelModel)? $this->labelModel->get($cellName, $cellName) : $cellName;
			$pdf->MultiCell($cellWidth, $this->headerRowHeight, $cellLabel, 1, 'L', 0, 1, $contentFrame->x+$offsetX, $contentFrame->y);
			$offsetX += $cellWidth;
		}
		$pdf->SetFont('','');
		// Reset the y to use
		$contentFrame->y += $this->headerRowHeight;
	}
	
	function drawCellBorder($parent, $cellHeights=False) {		
		$pdf = $parent->getPDF();
		$contentFrame = $parent->getContentFrame();
		
		if(empty($cellHeights)) $cellHeights = array();

		$offsetX = 0;
		foreach($this->cells as $cellName => $cellWidth) {
			$cellHeight = isset($cellHeights[$cellName])? $cellHeights[$cellName] : $contentFrame->h;

			$offsetY = $contentFrame->y-$this->headerRowHeight;			
			
			$pdf->MultiCell($cellWidth, $cellHeight, "", 1, 'L', 0, 1, $contentFrame->x+$offsetX, $offsetY);
			$offsetX += $cellWidth;
		}
	}

	function display($parent) {
		$this->displayPreLastPage($parent);
		$this->displayLastPage($parent);
	}

	function displayPreLastPage($parent) {

		$models = $this->contentModels;

		$totalModels = count($models);
		$pdf = $parent->getPDF();

		$parent->createPage();
		$contentFrame = $parent->getContentFrame();

		$contentLineX = $contentFrame->x; $contentLineY = $contentFrame->y;
		$overflowOffsetH = 8; // This is offset used to detect overflow to next page
		for ($index = 0; $index < $totalModels; ++$index) {
			$model = $models[$index];
			
			$contentHeight = 1;
			
			// Determine the content height to use
			foreach($this->cells as $cellName => $cellWidth) {
				$contentString = $model->get($cellName);
				if(empty($contentString)) continue;
				$contentStringHeight = $pdf->GetStringHeight($contentString, $cellWidth);
				if ($contentStringHeight > $contentHeight) $contentHeight = $contentStringHeight;
			}
			
			// Are we overshooting the height?
			if(ceil($contentLineY + $contentHeight) > ceil($contentFrame->h+$contentFrame->y)) {
			
				$this->drawCellBorder($parent);
				$parent->createPage();

				$contentFrame = $parent->getContentFrame();
				$contentLineX = $contentFrame->x; $contentLineY = $contentFrame->y;
			}

			$offsetX = 0;
			foreach($this->cells as $cellName => $cellWidth) {
				$pdf->MultiCell($cellWidth, $contentHeight, $model->get($cellName), 0, 'L', 0, 1, $contentLineX+$offsetX, $contentLineY);
				$offsetX += $cellWidth;
			}
			
			$contentLineY = $pdf->GetY();
			
			$commentContent = $model->get('Comment');
			
			if (!empty($commentContent)) {
				$commentCellWidth = $this->cells['Name'];
				$offsetX = $this->cells['Code'];
				
				$contentHeight = $pdf->GetStringHeight($commentContent, $commentCellWidth);			
				if(ceil($contentLineY + $contentHeight + $overflowOffsetH) > ceil($contentFrame->h+$contentFrame->y)) {
					
					$this->drawCellBorder($parent);
					$parent->createPage();

					$contentFrame = $parent->getContentFrame();
					$contentLineX = $contentFrame->x; $contentLineY = $contentFrame->y;
				}			
				$pdf->MultiCell($commentCellWidth, $contentHeight, $model->get('Comment'), 0, 'L', 0, 1, $contentLineX+$offsetX,
					 $contentLineY);
					 
				$contentLineY = $pdf->GetY();
			}
		}

		// Summary
		$cellHeights = array();
		
		if ($this->contentSummaryModel) {
			$summaryCellKeys = $this->contentSummaryModel->keys(); $summaryCellCount = count($summaryCellKeys);
		
			$summaryCellLabelWidth = $this->cells['Quantity'] + $this->cells['Price'] + $this->cells['Discount'] + $this->cells['Tax'];
			$summaryCellHeight = $pdf->GetStringHeight("TEST", $summaryCellLabelWidth); // Pre-calculate cell height
		
			$summaryTotalHeight = ceil(($summaryCellHeight * $summaryCellCount));
	
			if (($contentFrame->h+$contentFrame->y) - ($contentLineY+$overflowOffsetH)  < $summaryTotalHeight) { //$overflowOffsetH is added so that last Line Item is not overlapping
				$this->drawCellBorder($parent);
				$parent->createPage();
					
				$contentFrame = $parent->getContentFrame();
				$contentLineX = $contentFrame->x; $contentLineY = $contentFrame->y;
			}
				
			$summaryLineX = $contentLineX + $this->cells['Code'] + $this->cells['Name'];		
			$summaryLineY = ($contentFrame->h+$contentFrame->y-$this->headerRowHeight)-$summaryTotalHeight;
		
			foreach($summaryCellKeys as $key) {	
				$pdf->MultiCell($summaryCellLabelWidth, $summaryCellHeight, $key, 1, 'L', 0, 1, $summaryLineX, $summaryLineY);
				$pdf->MultiCell($contentFrame->w-$summaryLineX+10-$summaryCellLabelWidth, $summaryCellHeight, 
					$this->contentSummaryModel->get($key), 1, 'R', 0, 1, $summaryLineX+$summaryCellLabelWidth, $summaryLineY);
				$summaryLineY = $pdf->GetY();
			}
		
			$cellIndex = 0;
			foreach($this->cells as $cellName=>$cellWidth) {
				if ($cellIndex < 2) $cellHeights[$cellName] = $contentFrame->h;
				else $cellHeights[$cellName] = $contentFrame->h - $summaryTotalHeight;
				++$cellIndex;
			}
		}
		$this->onSummaryPage = true;
		$this->drawCellBorder($parent, $cellHeights);
	}

	function displayLastPage($parent) {
		// Add last page to take care of footer display
		if($parent->createLastPage()) {
			$this->onSummaryPage = false;
		}
	}

	function drawStatusWaterMark($parent) {
		$pdf = $parent->getPDF();

		$waterMarkPositions=array("30","180");
		$waterMarkRotate=array("45","50","180");

		$pdf->SetFont('Arial','B',50);
		$pdf->SetTextColor(230,230,230);
		$pdf->Rotate($waterMarkRotate[0], $waterMarkRotate[1], $waterMarkRotate[2]);
		$pdf->Text($waterMarkPositions[0], $waterMarkPositions[1], 'created');
		$pdf->Rotate(0);
		$pdf->SetTextColor(0,0,0);
	}
}