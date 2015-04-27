<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
require_once("libraries/tcpdf/tcpdf.php");
//require_once("libraries/tcpdf/tcpdf.php");

class Printer extends TCPDF {
    function setContent($html) {
        $this->SetFont($this->font, '', 12);
        $this->writeHTML($html);
    }

    function Close() {
        if ($this->state == 3) {
            return;
        }
        if ($this->page == 0) {
            $this->AddPage();
        }
        // close page
        $this->endPage();
        if ($_SESSION['generated_footer'] == "Yes") {
            if ($_SESSION['type'] == 'multiple') {
                $ile_stron = $this->PageNo();

                if ($ile_stron > $_SESSION['no_of_records']) {
                    $strona = $ile_stron / $_SESSION['no_of_records'];

                    $pomocnicza = 0;
                    for ($i = 1; $i <= $ile_stron; $i++) {
                        if ($i % $strona == 0) {
                            //$this->deletePage( $i - $pomocnicza ); 
                            $pomocnicza = $pomocnicza + 1;
                        }
                    }
                }
            } elseif ($_SESSION['type'] == 'single') {
                if ($this->PageNo() > 1) {
                    $this->deletePage($this->PageNo());
                }
            }
        }
        // close document
        $this->_enddoc();
        // unset all class variables (except critical ones)
        $this->_destroy(false);
    }

    public function Header() {
		if ($_SESSION['header_enable'] == 'Yes') {
			$this->SetFont('helvetica', '', 12);
			if ( $_REQUEST['top'] != '' ) {
				//$this->setHeaderMargin($_REQUEST['top']);
				//$this->SetTopMargin(0);
			}
			$this->SetY($_REQUEST['top'], true, true);
			$recordModel = Vtiger_Record_Model::getInstanceById( $_SESSION['template_to_perfom'] );
			$height_header = (int)$recordModel->get('height_header');
			$bMargin = $this->getBreakMargin();
			// get current auto-page-break mode
			$auto_page_break = $this->AutoPageBreak;
			// disable auto-page-break
			$this->SetAutoPageBreak(false, 0);
			
			$this->writeHTML($_REQUEST['header_content']);
			
			$this->SetTopMargin($height_header);
			// restore auto-page-break status
			$this->SetAutoPageBreak($auto_page_break, $bMargin);
			// set the starting point for the page content
			$this->setPageMark();
			//echo '<pre>';print_r($_SESSION['header_content']);echo '</pre>';exit;
		}
    }

    public function Footer() {
        if ($_SESSION['footer_enable'] == 'Yes') {
            $recordModel = Vtiger_Record_Model::getInstanceById( $_SESSION['template_to_perfom'] );
			$height_footer = $recordModel->get('height_footer');
            $y = -(int)$height_footer;
			$bMargin = $this->getBreakMargin();
			$auto_page_break = $this->AutoPageBreak;
			$height_header = (int)$recordModel->get('height_header');
            if ($_SESSION['enable_numbering'] == 'NumberFormat' || $_SESSION['enable_numbering'] == 'PageXofY') {
                $y -= 10;
				$bMargin += 10;
            }
			$this->SetFont($this->font, '', 8);
            $this->SetY($y);
			$this->SetAutoPageBreak(false, 0);
			//$this->writeHTML($this->bMargin.' - '.$this->PageBreakTrigger);
            $this->writeHTML($_REQUEST['footer_content']);
			$this->SetFont('helvetica', 'I', 8);
            if ($_SESSION['enable_numbering'] == 'NumberFormat') {
                $this->writeHTML('<span align="center">' . $this->getAliasNumPage() . '</span>');
            } elseif ($_SESSION['enable_numbering'] == 'PageXofY') {
                $this->writeHTML('<span align="center">' . vtranslate('Page', 'OSSPdf') . ' ' . $this->getAliasNumPage() . ' ' . vtranslate('of', 'OSSPdf') . ' ' . $this->getAliasNbPages() . '</span>');
            }
			$this->SetFont($this->font, '', 12);
			$this->SetAutoPageBreak($auto_page_break, $bMargin);
			$this->setPageMark();
			//echo '<pre>';print_r($bMargin);echo '</pre>';exit;
        }
    }
}

?>
