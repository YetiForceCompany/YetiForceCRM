<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/../viewers/HeaderViewer.php';

class Vtiger_PDF_InventoryHeaderViewer extends Vtiger_PDF_HeaderViewer {

	function totalHeight($parent) {
		$height = 100;
		
		if($this->onEveryPage) return $height;
		if($this->onFirstPage && $parent->onFirstPage()) $height;
		return 0;
	}
	
	function display($parent) {
		$pdf = $parent->getPDF();
		$headerFrame = $parent->getHeaderFrame();
		if($this->model) {
			$headerColumnWidth = $headerFrame->w/3.0;
			
			$modelColumns = $this->model->get('columns');
			
			// Column 1
			$offsetX = 5;
			
			$modelColumn0 = $modelColumns[0];

			list($imageWidth, $imageHeight, $imageType, $imageAttr) = $parent->getimagesize(
					$modelColumn0['logo']);
			//division because of mm to px conversion
			$w = $imageWidth/3;
			if($w > 60) {
				$w=60;
			}
			$h = $imageHeight/3;
			if($h > 30) {
				$h = 30;
			}
			$pdf->Image($modelColumn0['logo'], $headerFrame->x, $headerFrame->y, $w, $h);
			$imageHeightInMM = 30;
			
			$pdf->SetFont('freeserif', 'B');
			$contentHeight = $pdf->GetStringHeight( $modelColumn0['summary'], $headerColumnWidth);
			$pdf->MultiCell($headerColumnWidth, $contentHeight, $modelColumn0['summary'], 0, 'L', 0, 1, 
				$headerFrame->x, $headerFrame->y+$imageHeightInMM+2);
			
			$pdf->SetFont('freeserif', '');
			$contentHeight = $pdf->GetStringHeight( $modelColumn0['content'], $headerColumnWidth);			
			$pdf->MultiCell($headerColumnWidth, $contentHeight, $modelColumn0['content'], 0, 'L', 0, 1, 
				$headerFrame->x, $pdf->GetY());
				
			// Column 2
			$offsetX = 5;
			$pdf->SetY($headerFrame->y);

			$modelColumn1 = $modelColumns[1];
			
			$offsetY = 8;
			foreach($modelColumn1 as $label => $value) {

				if(!empty($value)) {
					$pdf->SetFont('freeserif', 'B');
					$pdf->SetFillColor(205,201,201);
					$pdf->MultiCell($headerColumnWidth-$offsetX, 7, $label, 1, 'C', 1, 1, $headerFrame->x+$headerColumnWidth+$offsetX, $pdf->GetY()+$offsetY);

					$pdf->SetFont('freeserif', '');
					$pdf->MultiCell($headerColumnWidth-$offsetX, 7, $value, 1, 'C', 0, 1, $headerFrame->x+$headerColumnWidth+$offsetX, $pdf->GetY());
					$offsetY = 2;
				}
			}
			
			// Column 3
			$offsetX = 10;
			
			$modelColumn2 = $modelColumns[2];
			
			$contentWidth = $pdf->GetStringWidth($this->model->get('title'));
			$contentHeight = $pdf->GetStringHeight($this->model->get('title'), $contentWidth);
			
			$roundedRectX = $headerFrame->w+$headerFrame->x-$contentWidth*2.0;
			$roundedRectW = $contentWidth*2.0;
			
			$pdf->RoundedRect($roundedRectX, 10, $roundedRectW, 10, 3, '1111', 'DF', array(), array(205,201,201));
			
			$contentX = $roundedRectX + (($roundedRectW - $contentWidth)/2.0);
			$pdf->SetFont('freeserif', 'B');
			$pdf->MultiCell($contentWidth*2.0, $contentHeight, $this->model->get('title'), 0, 'R', 0, 1, $contentX-$contentWidth,
				 $headerFrame->y+2);

			$offsetY = 4;

			foreach($modelColumn2 as $label => $value) {
				if(is_array($value)) {
					$pdf->SetFont('freeserif', '');
					foreach($value as $l => $v) {
						$pdf->MultiCell($headerColumnWidth-$offsetX, 7, sprintf('%s: %s', $l, $v), 1, 'C', 0, 1, 
							$headerFrame->x+$headerColumnWidth*2.0+$offsetX, $pdf->GetY()+$offsetY);
						$offsetY = 0;
					}
				} else {
					$offsetY = 1;
					
				$pdf->SetFont('freeserif', 'B');
				$pdf->SetFillColor(205,201,201);
                                if($label=='Shipping Address'){ 
                                    $width=$pdf->GetStringWidth($value); 
                                    $height=$pdf->GetStringHeight($value,$width);
                                    $pdf->MultiCell($headerColumnWidth-$offsetX, 7, $label, 1, 'L', 1, 1, $headerFrame->x+$headerColumnWidth*2.0+$offsetX,
                                            $pdf->GetY()+$offsetY-$height-$offsetX-4.0); 

                                    $pdf->SetFont('freeserif', '');
                                    $pdf->MultiCell($headerColumnWidth-$offsetX, 7, $value, 1, 'L', 0, 1, $headerFrame->x+$headerColumnWidth*2.0+$offsetX, 
					$pdf->GetY());
				} else{ 
                                    $pdf->MultiCell($headerColumnWidth-$offsetX, 7, $label, 1, 'L', 1, 1, $headerFrame->x+$headerColumnWidth, 
                                            $pdf->GetY()+$offsetY); 

                                    $pdf->SetFont('freeserif', ''); 
                                    $pdf->MultiCell($headerColumnWidth-$offsetX, 7, $value, 1, 'L', 0, 1, $headerFrame->x+$headerColumnWidth,  
                                            $pdf->GetY()); 
                                    } 
                                } 
                            } 
			$pdf->setFont('freeserif', '');

			// Add the border cell at the end
			// This is required to reset Y position for next write
			$pdf->MultiCell($headerFrame->w, $headerFrame->h-$headerFrame->y, "", 0, 'L', 0, 1, $headerFrame->x, $headerFrame->y);
		}	
		
	}
	
}