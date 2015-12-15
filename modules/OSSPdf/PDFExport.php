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
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('modules/Users/Users.php');
require_once('include/utils/UserInfoUtil.php');
require_once('modules/CustomView/CustomView.php');
require_once 'modules/PickList/PickListUtils.php';
require_once('include/utils/CommonUtils.php');
require_once "modules/OSSPdf/Print.php";
require_once('modules/OSSPdf/ModulesQueries.php');
// Set the current language and the language strings, if not already set.
//setCurrentLanguage();
global $allow_exports, $app_strings, $adb, $current_user;
$current_language = vglobal('current_language');
if (!isset($current_language))
	$current_language = vglobal('default_language');
session_start();
$current_user = new Users();
if (isset($_SESSION['authenticated_user_id'])) {
    $result = $current_user->retrieveCurrentUserInfoFromFile($_SESSION['authenticated_user_id'], "Users");
    if ($result == null) {
        session_destroy();
        header("Location: index.php?action=Login&module=Users");
        exit;
    }
}

/* ----------------------------------------------------------------- */
/* ----------------------------------------------------------------- */

function find_special_functions(&$content, $offset, &$pdf, $module, $id, $tcpdf) {

    $start = (int) strpos($content, '#special_function#', $offset);
    if ($start != 0) {
        $koniec = (int) strpos($content, '#end_special_function#', $start);
        $functionname = substr($content, $start + 18, $koniec - $start - 18);
        $dl = (int) ($koniec - $start - 18);
        if ($functionname != '') {
            $content = $pdf->RunSpecialFunction($functionname, $content, $id, $module, $start, $dl, $_REQUEST['template_to_perfom'], $tcpdf);
        }

        $content = find_special_functions($content, $start + 7, $pdf, $module, $id, $tcpdf);
    }
	return $content;
}

/**
 * This function is used to generate file name if more than one image with same name is added to a given Product.
 * Param $filename - product file name
 * Param $exist - number time the file name is repeated.
 */
function file_exist_fn($filename, $exist) {
    $log = vglobal('log');
    $log->debug("Entering file_exist_fn(" . $filename . "," . $exist . ") method ...");
    global $uploaddir;

    if (!isset($exist)) {
        $exist = 0;
    }
    $filename_path = $uploaddir . $filename;
    if (file_exists($filename_path)) { //Checking if the file name already exists in the directory
        if ($exist != 0) {
            $previous = $exist - 1;
            $next = $exist + 1;
            $explode_name = explode("_", $filename);
            $implode_array = array();
            for ($j = 0; $j < count($explode_name); $j++) {
                if ($j != 0) {
                    $implode_array[] = $explode_name[$j];
                }
            }
            $implode_name = implode("_", $implode_array);
            $test_name = $implode_name;
        } else {
            $implode_name = $filename;
        }
        $exist++;
        $filename_val = $exist . "_" . $implode_name;
        $testfilename = file_exist_fn($filename_val, $exist);
        if ($testfilename != "") {
            $log->debug("Exiting file_exist_fn method ...");
            return $testfilename;
        }
    } else {
        $log->debug("Exiting file_exist_fn method ...");
        return $filename;
    }
}

/* ----------------------------------------------------------------- */

function find_report_tags(&$content, $offset, &$pdf, $module, $id) {

    $start = mb_strpos($content, '#REP_NR#', $offset);
	
	if (false !== $start) {
		
        $end = (int) mb_strpos($content, '#REP_NR_END#', $start);
        $if = (int) mb_strpos($content, '#ONLY#', $end + 12);

        $reportid = mb_substr($content, $start + 8, $end - $start - 8);
		
		$dl = (int) ($end - $start - 8);
        if ($if != 0) {
            if (( $end + 12 ) == $if) {
                $only = 1;
            } else {
                $only = 0;
            }
        } else {
            $only = 0;
        }
        if ($reportid != 0) {
            $content = $pdf->ReportToPdf($reportid, $content, $only, $id, $module, $start, $dl);
        }

        find_report_tags($content, $start + 7, $pdf, $module, $id);
    }
}

///////////////////////////////////////////////////////////////////////
/* ----------------------------------------------------------------- */
# TakeContent
# Funkcja do pobrania zawartości danego dokumentu PDF
# (Dla pojedynczego rekordu CRM
function TakeContent(&$pdf, $module, $id, $site_URL) {
    $adb = PearDatabase::getInstance(); $current_user = vglobal('current_user');
    $pdf->AddPage();
    if ($module == 'Calendar') {
        require_once "modules/Calendar/Activity.php";
        $module = "Activity";
    } else {
        require_once "modules/$module/$module.php";
    }
    require_once "modules/OSSPdf/OSSPdf.php";
    require_once "modules/OSSPdf/Print.php";
    ### Zmienne

    $modulelist = array('Quotes', 'PurchaseOrder', 'Invoice');
    $focus = new $module();
    $template = $_REQUEST['template_to_perfom'];
    ### pobranie danych danego modułu
    if ($module == "Activity") {
        $focus->retrieve_entity_info($id, "Calendar");
    } else {
        $focus->retrieve_entity_info($id, $module);
    }

    $fields = $focus->column_fields;
    $focus->apply_field_security();
    $recordModel = Vtiger_Record_Model::getCleanInstance('OSSPdf');
    $_SESSION['wrong_footer'] = "No";
    $footer_header_info = $recordModel->getFooterHeaderInfo($module, $id, $template);
    $_SESSION['bottom_margin'] = $_REQUEST['bottom'];
    $_SESSION['header_enable'] = $footer_header_info[1];
    $_SESSION['footer_enable'] = $footer_header_info[2];
    $_SESSION['enable_numbering'] = $footer_header_info[4];
    $fcontent = $footer_header_info[3];
	/////////////// 
	$recordM = Vtiger_Record_Model::getInstanceById( $template );
	$height_footer = $recordM->get('height_footer');
	$auto_page_break = $pdf->getAutoPageBreak();
	if ($_SESSION['enable_numbering'] == 'NumberFormat' || $_SESSION['enable_numbering'] == 'PageXofY') {
		$height_footer += 10;
	}
	$pdf->SetAutoPageBreak($auto_page_break, $height_footer);
	///////////////
    ### ustawienie zawartości PDFa
    $content = $recordModel->getContent($module, $id, $template);

    if ($_REQUEST['usingmodule'] != 'Reports') {
        $content = $recordModel->replaceModuleFields($content, $fields, $module, $id);
        $content = $recordModel->replaceRelatedModuleFields($content, $module, $id, $fields, $site_URL); ///echo "<br/><br/>".$content;
    }
    $content = $recordModel->replaceCompanyInformations($content);

    $reportid = 0;
    find_report_tags($content, 0, $recordModel, $module, $id);
    $content = find_special_functions($content, 0, $recordModel, $module, $id, $pdf);
    $raport = (int) strpos($content, '#report_tag#');
    if ($raport != 0) {
        $content = $recordModel->replaceReport($content, $id, $raport);
    }

### Tabela produktów	
    if (in_array($module, $modulelist)) {
        $produkty_np = (int) strpos($content, '#product_tableNP#');
        $produkty = (int) strpos($content, '#product_table#');
        $produkty_lista = (int) strpos($content, '#product_list#');

        if ($produkty != 0) {
            $content = $recordModel->replaceProductTable($content, $module, $id);
        }
        if ($produkty_np != 0) {
            $content = $recordModel->replaceProductTableNP($content, $module, $id);
        }
        if ($produkty_lista != 0) {
            $content = $recordModel->replaceProductList($content, $module, $id);
        }
    }

    $wymiary = $pdf->getPageDimensions();
    /* print_r($wymiary);
      ///Domyslna rozdzielczosc TCPDF
      $pdf_dpi = 72;
      $szerokosc = floor( $wymiary['w'] );
      $lewy_margines = ceil( ($wymiary['lm'] * $pdf_dpi) / 25.4 );
      $prawy_margines = ceil( ($wymiary['rm'] * $pdf_dpi) / 25.4 );
      $wymiary_strony[ 'width' ] = $szerokosc;
     */

    //$pdf->setPrintHeader(true);
    //$pdf->setPrintFooter(true);

    if ($_SESSION['footer_enable'] == 'Yes') {
        $czy_table = strpos($footer_header_info[3], "<table", 0);

        $znajdz = strpos($footer_header_info[3], 'height="', $czy_table);
        if ($znajdz === FALSE) {

            $_SESSION['wrong_footer'] = 'Yes';
            $_SESSION['footer_height'] = $_SESSION['bottom_margin'];
        } else {
            $koniec = strpos($footer_header_info[3], 'px"', $znajdz + 8);
            $dl = $koniec - $znajdz - 8;
            $string = substr($footer_header_info[3], $znajdz + 8, $dl);
            $_SESSION['footer_height'] = round($pdf->pixelsToUnits($string));
            if ($_SESSION['bottom_margin'] != 0 && $_SESSION['bottom_margin'] != "") {
                if ($_SESSION['footer_height'] > $_SESSION['bottom_margin']) {
                    //$pdf->SetAutoPageBreak(true, $_SESSION['footer_height']);
                } else {
                    $_SESSION['footer_height'] = $_SESSION['bottom_margin'];
                }
            } else {
                $_SESSION['footer_height'] = 25;
            }
        }
    }

    if ($_SESSION['header_enable'] == 'Yes') {        

        if ($_REQUEST['usingmodule'] != 'Reports') {
            $footer_header_info[0] = $recordModel->replaceModuleFields($footer_header_info[0], $fields, $module, $id);
            $footer_header_info[0] = $recordModel->replaceRelatedModuleFields($footer_header_info[0], $module, $id, $fields, $site_URL); ///echo "<br/><br/>".$content;
        }
        $footer_header_info[0] = $recordModel->replaceCompanyInformations($footer_header_info[0]);

        $reportid = 0;
        find_report_tags($footer_header_info[0], 0, $recordModel, $module, $id);
        $footer_header_info[0] = find_special_functions($footer_header_info[0], 0, $recordModel, $module, $id, $pdf);
        $raport = (int) strpos($footer_header_info[0], '#report_tag#');
        if ($raport != 0) {
            $footer_header_info[0] = $recordModel->replaceReport($footer_header_info[0], $id, $raport);
        }
        $_REQUEST['header_content'] = $footer_header_info[0];
        $pdf->Header();
    }
	if ($_SESSION['footer_enable'] == "Yes") {
        if ($_REQUEST['usingmodule'] != 'Reports') {
            $fcontent = $recordModel->replaceModuleFields($fcontent, $fields, $module, $id);
            $fcontent = $recordModel->replaceRelatedModuleFields($fcontent, $module, $id, $fields, $site_URL); ///echo "<br/><br/>".$content;
        }
        $fcontent = $recordModel->replaceCompanyInformations($fcontent);

        $reportid = 0;
        find_report_tags($fcontent, 0, $recordModel, $module, $id);
        $fcontent = find_special_functions($fcontent, 0, $recordModel, $module, $id, $pdf);
        $raport = (int) strpos($fcontent, '#report_tag#');
        if ($raport != 0) {
            $fcontent = $recordModel->replaceReport($fcontent, $id, $raport);
        }
        $_REQUEST['footer_content'] = $fcontent;
	}
    $pdf->setContent($content);


    if ($_SESSION['footer_enable'] == "Yes") {
        $pdf->Footer();
    }

    $_SESSION['header_enable'] = "No";
    $_SESSION['footer_enable'] = "No";
    //$pdf->setPrintHeader(false);
    //$pdf->setPrintFooter(false);
}

/* ----------------------------------------------------------------- */

function CreateDocument($filepath, $ifattach, $id, $module, &$docid) {
    $adb = PearDatabase::getInstance(); $current_user = vglobal('current_user');
    $size = filesize($filepath);
    //echo $size;
    require_once( 'modules/Documents/Documents.php' );
    $obiekt = new Documents();
    ###
    $storage_path = decideFilePath();
    $pelnasciezka = $storage_path . $filepath;
    //echo $pelnasciezka;
    $wynik = $adb->query("select value from vtiger_osspdf_config where name='$module' and conf_id = 'GENERALCONFIGURATION'", true);
    $wartosc = $adb->query_result($wynik, 0, "value");
    if ($wartosc == 'default') {
        $assign = $current_user->id;
    } else {
        $assign = $wartosc;
    }

    ### Tworzenie nowego Dokumentu
    $obiekt->column_fields['notes_title'] = getTranslatedString($module, $module) . ' ' . $_REQUEST['file_name'] . ' ' . date("Y-m-d H:i:s");
    $obiekt->column_fields['filename'] = $filepath;
    $obiekt->column_fields['notecontent'] = 'OSSPdf';
    $obiekt->column_fields['folderid'] = '1';
    $obiekt->column_fields['filetype'] = "application/pdf";
    $obiekt->column_fields['filesize'] = $size;
    $obiekt->column_fields['filelocationtype'] = 'I';
    $obiekt->column_fields['filestatus'] = '1';
    $obiekt->column_fields['assigned_user_id'] = $assign;
    ### Zapis Dokumentu
    $obiekt->saveentity("Documents");
    $docid = $obiekt->id;
    $newid = $adb->getUniqueId('vtiger_crmentity');
    ### Dodanie relacji między załacznikiem a dokumentem
    $sql = "INSERT INTO vtiger_seattachmentsrel (`crmid`,`attachmentsid`) VALUES ('$docid','$newid')";
    //echo $sql;
    $wykonaj = $adb->query($sql, true);
    $date_var = date("Y-m-d H:i:s");
    ### Dodanie wpisu o załączniku
    $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
    $params1 = array($newid, $current_user->id, $current_user->id, "Documents Attachment", NULL, $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
    $adb->pquery($sql1, $params1, true);
    ### Dodanie informacji o załączniku do tabeli attachments
    $sql = "INSERT INTO vtiger_attachments (`attachmentsid`,`name`,`type`,`path`) VALUES ('$newid','$filepath','application/pdf','$storage_path')";
    //echo $sql;
    $wykonaj = $adb->pquery($sql, array(), true);
    ### Przeniesienie pliku do właściwego podkatalogu


    return $docid;
}

/* ----------------------------------------------------------------- */

function zipFilesAndDownload($file_names, $archive_file_name, $file_path, $zipname = "") {


    //create the object
    $zip = new ZipArchive();

    //create the file and throw the error if unsuccessful
    chmod('storage', 0777);
    if ($zip->open($archive_file_name, ZIPARCHIVE::CREATE) !== TRUE) {
        exit("cannot open <$archive_file_name>\n");
    }

    //add each files of $file_name array to archive
    foreach ($file_names as $files) {
        $zip->addFile($file_path . $files, $files);
    }
    $zip->close();
    $onlyGenerate = (int)$_REQUEST['only_generate'];
    
    if (0 == $onlyGenerate) {
        if ($_REQUEST['return_name'] != "yes") {
            header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=\"$archive_file_name\"");
            header("Pragma: no-cache");
            header("Expires: 0");
			header('Content-Length: '.file_get_contents($archive_file_name));
            readfile("$archive_file_name");
            exit;
        } elseif ($_REQUEST['return_name'] == "yes") {
            if (isset($_REQUEST['soap_pdf'])) {
                return $zipname;
            } else {
                echo $zipname;
            }
        }
    } else {
        header("Location: index.php?module=OSSMail&view=compose&pdf_path=" . urldecode($archive_file_name));
    }
    //then send the headers to foce download the zip file
}

/* ----------------------------------------------------------------- */
# Generate PDF

function GeneratePDF($module, &$pdf, $pdf_orientation) {
    global $adb, $list_max_entries_per_page;
    $product_module_list = Array('Quotes', 'PurchaseOrder', 'Invoice');

    $idlist = trim($_REQUEST['idstring'], ';');
    $idlist = explode(';', $idlist);
    chmod('storage', 0777);

    $_SESSION['no_of_records'] = count($idlist);
    if ($_REQUEST['ParticularSave'] == 'yes') {
        $document_list = array();
        foreach ($idlist as $id) {
            /* ----------------------------- */
            $date_var = $adb->formatDate(date('Y-m-d H:i:s'), true);
            $query = "insert into vtiger_audit_trial values(?,?,?,?,?,?)";
            $qparams = array($adb->getUniqueID('vtiger_audit_trial'), $current_user->id, $module, 'Generate PDF', $id, $date_var);
            $adb->pquery($query, $qparams, true);
            /* ----------------------------- */
            $singlepdf = new Printer();
            $singlepdf->setPrintHeader(false);
            $singlepdf->setPrintFooter(false);

            $singlepdf->SetCompression(true);
            $pdf->setPageFormat($_REQUEST['pdf_format'], $pdf_orientation);
			
			$default_margin = 10;
			if ( $_REQUEST['left'] != '' ) {
				$pdf->SetLeftMargin($_REQUEST['left']);
			}else{
				$pdf->SetLeftMargin( $default_margin );
			}

			if ( $_REQUEST['right'] != '' ) {
				$pdf->SetRightMargin($_REQUEST['right']);
			}else{
				$pdf->SetRightMargin( $default_margin );
			}
			$_SESSION['top'] = $_REQUEST['top'];
/*
			if ( $_REQUEST['top'] != '' ) {
				$pdf->SetTopMargin($_REQUEST['top']);
			}else{
				$pdf->SetTopMargin( $default_margin );
			}

			if ( $_REQUEST['bottom'] != '' ) {
				$pdf->SetAutoPageBreak(true, $_REQUEST['bottom']);
			}else{
				$pdf->SetAutoPageBreak( $default_margin );
			}*/

            TakeContent($singlepdf, $module, $id);
## przetworzenie danych z nazwy pliku
			/*if ($module == 'Calendar') {
			require_once "modules/Calendar/Activity.php";
			$module = "Activity";
			} else {
				require_once "modules/$module/$module.php";
			}
			require_once "modules/OSSPdf/OSSPdf.php";
			require_once "modules/OSSPdf/Print.php";
			### Zmienne
			*/
			$focus = new $module();
			### pobranie danych danego modułu
			if ($module == "Activity") {
				$focus->retrieve_entity_info($id, "Calendar");
			} else {
				$focus->retrieve_entity_info($id, $module);
			}
			$fields = $focus->column_fields;
			
			$recordModel = Vtiger_Record_Model::getCleanInstance('OSSPdf');
			$_REQUEST['file_name'] = $recordModel->replaceModuleFields($_REQUEST['file_name'], $fields, $module, $id);
			$_REQUEST['file_name'] = $recordModel->replaceRelatedModuleFields($_REQUEST['file_name'], $module, $id, $fields, $site_URL=0);
			$_REQUEST['file_name'] = str_replace( "#dd-mm-yyyy#", date("d-m-Y"), $_REQUEST['file_name'] );
			$_REQUEST['file_name'] = str_replace( "#mm-dd-yyyy#", date("m-d-Y"), $_REQUEST['file_name'] );
			$_REQUEST['file_name'] = str_replace( "#yyyy-mm-dd#", date("Y-m-d"), $_REQUEST['file_name'] );
	// ## Koniec przetwarzania danych z nazwy pliku
			if(empty($_REQUEST['file_name']))
				$filepath = $_REQUEST['file_name'] . '_' . $id . '_' . date("YmdHis") . '.pdf';
			else
				$filepath = $_REQUEST['file_name'] . '.pdf';
            
            $singlepdf->Output($filepath, 'F');
            $docid = 0;
            if ($data['ifsave'] == 'yes') {

                $document_id = CreateDocument($filepath, $data['ifattach'], $id, $module, $docid);
                $nr = $document_id + 1;
                $document_list[] = $nr . '_' . $filepath;
                $storage_path = decideFilePath();
                $pelnasciezka = $storage_path . $nr . '_' . $filepath;
            } else {
                $document_list[] = $filepath;
                $storage_path = decideFilePath();
                $pelnasciezka = $storage_path . $filepath;
            }

            chmod('storage', 0777);
            if ($_REQUEST['return_name'] != "yes" || $_REQUEST['return_name'] == "") {
                rename($filepath, $pelnasciezka);
            } else {
                $sciezka = "storage/" . $filepath;
                rename($filepath, $sciezka);
            }

            if ($data['ifattach'] == 'yes') {
                $sql = "INSERT INTO vtiger_senotesrel (`crmid`,`notesid`) VALUES ('$id','$docid')";
                $wykonaj = $adb->query($sql, true);
            }
        }
        if ($_REQUEST['return_name'] != "yes" || $_REQUEST['return_name'] == "") {
            $storage_path = decideFilePath();
        } else {
            $storage_path = "storage/";
        }
        $zip = getTranslatedString($_REQUEST['usingmodule']) . '_' . date("YmdHis") . '.zip';
        $zipname = 'storage/' . $zip;
        $zipname = file_exist_fn($zipname, 0);
        zipFilesAndDownload($document_list, $zipname, $storage_path, $zip);
    } else {

        foreach ($idlist as $id) {
            #############################
            $date_var = $adb->formatDate(date('Y-m-d H:i:s'), true);
            $query = "insert into vtiger_audit_trial values(?,?,?,?,?,?)";
            $qparams = array($adb->getUniqueID('vtiger_audit_trial'), $current_user->id, $module, 'Generate PDF', $id, $date_var);
            $adb->pquery($query, $qparams, true);
            ##############################
            TakeContent($pdf, $module, $id, $site_URL);
        }
	## przetworzenie danych z nazwy pliku
			/*if ($module == 'Calendar') {
			require_once "modules/Calendar/Activity.php";
			$module = "Activity";
			} else {
				require_once "modules/$module/$module.php";
			}
			require_once "modules/OSSPdf/OSSPdf.php";
			require_once "modules/OSSPdf/Print.php";
			### Zmienne
			*/
			$focus = new $module();
			### pobranie danych danego modułu
			if ($module == "Activity") {
				$focus->retrieve_entity_info($id, "Calendar");
			} else {
				$focus->retrieve_entity_info($id, $module);
			}
			$fields = $focus->column_fields;
			
			$recordModel = Vtiger_Record_Model::getCleanInstance('OSSPdf');
			$_REQUEST['file_name'] = $recordModel->replaceModuleFields($_REQUEST['file_name'], $fields, $module, $id);
			$_REQUEST['file_name'] = $recordModel->replaceRelatedModuleFields($_REQUEST['file_name'], $module, $id, $fields, $site_URL=0);
			$_REQUEST['file_name'] = str_replace( "#dd-mm-yyyy#", date("d-m-Y"), $_REQUEST['file_name'] );
			$_REQUEST['file_name'] = str_replace( "#mm-dd-yyyy#", date("m-d-Y"), $_REQUEST['file_name'] );
			$_REQUEST['file_name'] = str_replace( "#yyyy-mm-dd#", date("Y-m-d"), $_REQUEST['file_name'] );
	// ## Koniec przetwarzania danych z nazwy pliku
			if(empty($_REQUEST['file_name']))
				$filepath = $_REQUEST['file_name'] . '_' . date("YmdHis") . '_ALL.pdf';
			else
				$filepath = $_REQUEST['file_name'] . '_ALL.pdf';	
		
		
        $onlyGenerate = (int)$_REQUEST['only_generate'];

            $pdf->Output($filepath, 'F');
            chmod('storage', 0777); //exit;	
            $storage_path = decideFilePath();
            $pelnasciezka = $storage_path . $filepath;
            if ($data['ifsave'] == 'yes') {
                $nr_documentu = CreateDocument($filepath, $data['ifattach'], $id, $module, $docid);
                $nr = $nr_documentu + 1;
                $pelnasciezka = $storage_path . $nr . '_' . $filepath;
            }
            //var_dump($storage_path);

            if ($_REQUEST['return_name'] != "yes") {
                rename($filepath, $pelnasciezka);
                if (0 == $onlyGenerate) {
                    $pdf->Output($filepath, 'D');
                } else {
                    $path = decideFilePath() . $filepath;
                    header("Location: index.php?module=OSSMail&view=compose&pdf_path=" . urldecode($pelnasciezka));
                }
                //echo $pelnasciezka;
            } elseif ($_REQUEST['return_name'] == "yes") {
                //echo $filepath;
                $sciezka = "storage/" . $filepath;
                echo $filepath;
                rename($filepath, $sciezka);
            }
        //} else {
          //  $path = decideFilePath() . $filepath;
//            header("Location: index.php?module=OSSMail&view=compose&pdf_path=" . urldecode($path));
//        }

        if ($data['ifattach'] == 'yes') {
            $sql = "INSERT INTO vtiger_senotesrel (`crmid`,`notesid`) VALUES ('$id','$docid')";
            $wykonaj = $adb->query($sql, true);
        }
    }
}

function Soap_generatePDF($userid) {
    $adb = PearDatabase::getInstance(); $current_user = vglobal('current_user');
    $_SESSION['type'] = "single";
    $user = new Users();
    $current_user = $user->retrieveCurrentUserInfoFromFile($userid);

    require_once "modules/OSSPdf/Print.php";
    require_once('modules/OSSPdf/ModulesQueries.php');
    $module = $_REQUEST['usingmodule'];
    $id = $_REQUEST['recordid'];

    if (isset($_REQUEST['fromdetailview']) && $_REQUEST['fromdetailview'] == 'yes') {
        $document_list = array();
        if ($_REQUEST['return_name'] == "yes" || isset($_REQUEST['pdfajax'])) {
            $_REQUEST['template'] = explode(';', trim($_REQUEST['template'], ';'));
        }

        /* ----------------------------- */##############
        ### PRZETWANIA ZMIENNYCH POCZATKOWYCH
        foreach ($_REQUEST['template'] as $templateid) {

            $_SESSION['template_to_perfom'] = $_REQUEST['template_to_perfom'] = $templateid;
            $pobierzdane = $adb->query("select osspdf_pdf_format,osspdf_pdf_orientation, filename, left_margin, right_margin, top_margin, bottom_margin from vtiger_osspdf where osspdfid = '$templateid'", true);
            $_REQUEST['pdf_format'] = $adb->query_result($pobierzdane, 0, "osspdf_pdf_format");

            $pdf_orientation_result = $adb->query_result($pobierzdane, 0, "osspdf_pdf_orientation");
            $_REQUEST['file_name'] = $adb->query_result($pobierzdane, 0, "filename");

            $_REQUEST['left'] = $adb->query_result($pobierzdane, 0, "left_margin");
            $_REQUEST['right'] = $adb->query_result($pobierzdane, 0, "right_margin");
            $_REQUEST['top'] = $adb->query_result($pobierzdane, 0, "top_margin");
            $_REQUEST['bottom'] = $adb->query_result($pobierzdane, 0, "bottom_margin");
			$_SESSION['top'] = $_REQUEST['top'];
            if ($pdf_orientation_result == 'Portrait') {
                $pdf_orientation = "P";
            } elseif ($pdf_orientation_result == 'Landscape') {
                $pdf_orientation = "L";
            }

            /* ----------------------------- */##############
            ### INICJOWANIE PDFA, POBIERANIE DANYCH ETC
            $pdf = new Printer();
            $pdf->setPageFormat($_REQUEST['pdf_format'], $pdf_orientation);
            //$pdf->setPrintHeader(false);
            //$pdf->setPrintFooter(false);

            //	$pdf->SetHeaderData( '','','asd','' );
            $pdf->SetCompression(true);
            //$pdf->SetMargins( $left,$top, $right = -1,$keepmargins = false );	
			
            if (isset($_REQUEST['left']) && $_REQUEST['left'] != '' && $_REQUEST['left'] != 0) {
                $pdf->SetLeftMargin($_REQUEST['left']);
            }
            if (isset($_REQUEST['right']) && $_REQUEST['right'] != '' && $_REQUEST['right'] != 0) {
                $pdf->SetRightMargin($_REQUEST['right']);
            }
/*
            if (isset($_REQUEST['top']) && $_REQUEST['top'] != '' && $_REQUEST['top'] != 0) {
                $pdf->SetTopMargin($_REQUEST['top']);
            }

            if (isset($_REQUEST['bottom']) && $_REQUEST['bottom'] != '' && $_REQUEST['bottom'] != 0) {
                $pdf->SetAutoPageBreak(true, $_REQUEST['bottom']);
            }*/

            /* ----------------------------- */################
            $date_var = $adb->formatDate(date('Y-m-d H:i:s'), true);
            $query = "insert into vtiger_audit_trial values(?,?,?,?,?,?)";
            $qparams = array($adb->getUniqueID('vtiger_audit_trial'), $current_user->id, $module, 'Generate PDF', $id, $date_var);
            $adb->pquery($query, $qparams, true);

            TakeContent($pdf, $module, $id, $site_URL);
            $filepath = $_REQUEST['file_name'] . '_' . $id . $templateid . '_' . date("YmdHis") . '.pdf';

            $pdf->Output($filepath, 'F');
            ###
            $pobierz = $adb->query("select * from vtiger_osspdf_config where conf_id = 'GENERALCONFIGURATION'", true);
            ###
            $data = array();
            for ($i = 0; $i < $adb->num_rows($pobierz); $i++) {
                $data[$adb->query_result($pobierz, $i, "name")] = $adb->query_result($pobierz, $i, "value");
            }

            $docid = 0;
            if ($data['ifsave'] == 'yes') {

                $document_id = CreateDocument($filepath, $data['ifattach'], $id, $module, $docid);
                $nr = $document_id + 1;
                $document_list[] = $nr . '_' . $filepath;
                $storage_path = decideFilePath();
                $pelnasciezka = $storage_path . $nr . '_' . $filepath;
            } else {
                $document_list[] = $filepath;
                $storage_path = decideFilePath();
                $pelnasciezka = $storage_path . $filepath;
            }
            chmod('storage', 0777);
            if ($_REQUEST['return_name'] != "yes" || $_REQUEST['return_name'] == "") {
                rename($filepath, $pelnasciezka);
            } else {
                $sciezka = "storage/" . $filepath;
                rename($filepath, $sciezka);
            }

            if ($data['ifattach'] == 'yes') {
                $sql = "INSERT INTO vtiger_senotesrel (`crmid`,`notesid`) VALUES ('$id','$docid')";
                $wykonaj = $adb->query($sql, true);
            }
        }

        if ($_REQUEST['return_name'] != "yes" || $_REQUEST['return_name'] == "") {
            $storage_path = decideFilePath();
        } else {
            $storage_path = "storage/";
        }

        $zip = getTranslatedString($_REQUEST['usingmodule']) . '_' . date("YmdHis") . '.zip';

        $zipname = 'storage/' . $zip;
        $zipname = file_exist_fn($zipname, 0);
        if (count($document_list) > 1) {
            zipFilesAndDownload($document_list, $zipname, $storage_path, $zip);
        } else {
            if ($_REQUEST['return_name'] != "yes") {
                header("Content-type: application/pdf");
                header("Content-Disposition: attachment; filename=" . $document_list[0]);
                header("Pragma: no-cache");
                header("Expires: 0");
                readfile($storage_path . "/" . $document_list[0]);
                exit;
            } elseif ($_REQUEST['return_name'] == "yes") {
                return $document_list[0];
            }
        }
    }
}

/* ----------------------------------------------------------------- */
///////////////////////////////////////////////////////////////////////
/** Send the output header and invoke function for contents output */
if (!isset($_REQUEST['soap_pdf'])) {
    $_SESSION['type'] = "single";
    $_SESSION['counter'] = 0;
    $module = $_REQUEST['usingmodule'];
    $id = $_REQUEST['recordid'];

    if (isset($_REQUEST['fromdetailview']) && $_REQUEST['fromdetailview'] == 'yes') {
        $document_list = array();
        if ($_REQUEST['return_name'] == "yes" || isset($_REQUEST['pdfajax'])) {
            $_REQUEST['template'] = explode(';', trim($_REQUEST['template'], ';'));
        }

        /* ----------------------------- */##############
        ### PRZETWANIA ZMIENNYCH POCZATKOWYCH
        foreach ($_REQUEST['template'] as $templateid) {

            $_SESSION['template_to_perfom'] = $_REQUEST['template_to_perfom'] = $templateid;
            $pobierzdane = $adb->query("select osspdf_pdf_format, osspdf_pdf_orientation, filename, left_margin, right_margin, top_margin, bottom_margin from vtiger_osspdf where osspdfid = '$templateid'", true);
            $_REQUEST['pdf_format'] = $adb->query_result($pobierzdane, 0, "osspdf_pdf_format");

            $pdf_orientation_result = $adb->query_result($pobierzdane, 0, "osspdf_pdf_orientation");
            $_REQUEST['file_name'] = $adb->query_result($pobierzdane, 0, "filename");
			
            $_REQUEST['left'] = $adb->query_result($pobierzdane, 0, "left_margin");
            $_REQUEST['right'] = $adb->query_result($pobierzdane, 0, "right_margin");
            $_REQUEST['top'] = $adb->query_result($pobierzdane, 0, "top_margin");
            $_REQUEST['bottom'] = $adb->query_result($pobierzdane, 0, "bottom_margin");
			$_SESSION['top'] = $_REQUEST['top'];
            if ($pdf_orientation_result == 'Portrait') {
                $pdf_orientation = "P";
            } elseif ($pdf_orientation_result == 'Landscape') {
                $pdf_orientation = "L";
            }
            /* ----------------------------- */##############
            ### INICJOWANIE PDFA, POBIERANIE DANYCH ETC
            $pdf = new Printer();
            $pdf->setPageFormat($_REQUEST['pdf_format'], $pdf_orientation);
            //$pdf->setPrintHeader(false);
            //$pdf->setPrintFooter(false);
			
			$default_margin = 10;
            //	$pdf->SetHeaderData( '','','asd','' );
            $pdf->SetCompression(true);
            //$pdf->SetMargins( $left,$top, $right = -1,$keepmargins = false );	

			if ( $_REQUEST['left'] != '' ) {
				$pdf->SetLeftMargin($_REQUEST['left']);
			}else{
				$pdf->SetLeftMargin( $default_margin );
			}

			if ( $_REQUEST['right'] != '' ) {
				$pdf->SetRightMargin($_REQUEST['right']);
			}else{
				$pdf->SetRightMargin( $default_margin );
			}
/*
			if ( $_REQUEST['top'] != '' ) {
				$pdf->SetTopMargin($_REQUEST['top']);
			}else{
				$pdf->SetTopMargin( $default_margin );
			}

			if ( $_REQUEST['bottom'] != '' ) {
				$pdf->SetAutoPageBreak(true, $_REQUEST['bottom']);
			}else{
				$pdf->SetAutoPageBreak( $default_margin );
			}*/
			
            /* ----------------------------- */################
            $date_var = $adb->formatDate(date('Y-m-d H:i:s'), true);
            $query = "insert into vtiger_audit_trial values(?,?,?,?,?,?)";
            $qparams = array($adb->getUniqueID('vtiger_audit_trial'), $current_user->id, $module, 'Generate PDF', $id, $date_var);
            $adb->pquery($query, $qparams, true);

            TakeContent($pdf, $module, $id, $site_URL);
	## przetworzenie danych z nazwy pliku
			/*if ($module == 'Calendar') {
			require_once "modules/Calendar/Activity.php";
			$module = "Activity";
			} else {
				require_once "modules/$module/$module.php";
			}
			require_once "modules/OSSPdf/OSSPdf.php";
			require_once "modules/OSSPdf/Print.php";
			### Zmienne
			*/
			$focus = new $module();
			### pobranie danych danego modułu
			if ($module == "Activity") {
				$focus->retrieve_entity_info($id, "Calendar");
			} else {
				$focus->retrieve_entity_info($id, $module);
			}
			$fields = $focus->column_fields;
			
			$recordModel = Vtiger_Record_Model::getCleanInstance('OSSPdf');
			$_REQUEST['file_name'] = $recordModel->replaceModuleFields($_REQUEST['file_name'], $fields, $module, $id);
			$_REQUEST['file_name'] = $recordModel->replaceRelatedModuleFields($_REQUEST['file_name'], $module, $id, $fields, $site_URL);
			$_REQUEST['file_name'] = str_replace( "#dd-mm-yyyy#", date("d-m-Y"), $_REQUEST['file_name'] );
			$_REQUEST['file_name'] = str_replace( "#mm-dd-yyyy#", date("m-d-Y"), $_REQUEST['file_name'] );
			$_REQUEST['file_name'] = str_replace( "#yyyy-mm-dd#", date("Y-m-d"), $_REQUEST['file_name'] );
			if(empty($_REQUEST['file_name'])){
				$_REQUEST['file_name'] = $id . $templateid . '_' . date("YmdHis");
			}
			
	//exit;		
	// ## Koniec przetwarzania danych z nazwy pliku
            $filepath = $_REQUEST['file_name'] . '.pdf';
            $pdf->Output($filepath, 'F');
            ###
            $pobierz = $adb->query("select * from vtiger_osspdf_config where conf_id = 'GENERALCONFIGURATION'", true);
            ###
            $data = array();

            for ($i = 0; $i < $adb->num_rows($pobierz); $i++) {
                $data[$adb->query_result($pobierz, $i, "name")] = $adb->query_result($pobierz, $i, "value");
            }

            $docid = 0;
            if ($data['ifsave'] == 'yes') {

                $document_id = CreateDocument($filepath, $data['ifattach'], $id, $module, $docid);
                $nr = $document_id + 1;
                $document_list[] = $nr . '_' . $filepath;
                $storage_path = decideFilePath();
                $pelnasciezka = $storage_path . $nr . '_' . $filepath;
            } else {
                $document_list[] = $filepath;
                $storage_path = decideFilePath();
                $pelnasciezka = $storage_path . $filepath;
            }
            chmod('storage', 0777);
            if ($_REQUEST['return_name'] != "yes" || $_REQUEST['return_name'] == "") {
                rename($filepath, $pelnasciezka);
            } else {
                $sciezka = "storage/" . $filepath;
                rename($filepath, $sciezka);
            }

            if ($data['ifattach'] == 'yes') {
                $sql = "INSERT INTO vtiger_senotesrel (`crmid`,`notesid`) VALUES ('$id','$docid')";
                $wykonaj = $adb->query($sql, true);
            }
        }


        if ($_REQUEST['return_name'] != "yes" || $_REQUEST['return_name'] == "") {
            $storage_path = decideFilePath();
        } else {
            $storage_path = "storage/";
        }


        $zip = getTranslatedString($_REQUEST['usingmodule']) . '_' . date("YmdHis") . '.zip';

        $zipname = 'storage/' . $zip;

        $zipname = file_exist_fn($zipname, 0);
        if (count($document_list) > 1) {
            zipFilesAndDownload($document_list, $zipname, $storage_path, $zip);
        } else {
            $onlyGenerate = (int)$_REQUEST['only_generate'];

            if (0 == $onlyGenerate) {
                if ($_REQUEST['return_name'] != "yes") {
                    header("Content-type: application/pdf");
                    header("Content-Disposition: attachment; filename=" . $document_list[0]);
                    header("Pragma: no-cache");
                    header("Expires: 0");
                    readfile($storage_path . $document_list[0]);
                    exit;
                } elseif ($_REQUEST['return_name'] == "yes") {
                    echo $document_list[0];
                }
            } else {
                header("Location: index.php?module=OSSMail&view=compose&pdf_path=" . urldecode($storage_path . $document_list[0]));
            }
        }
    } else {
        $templateid = $_REQUEST['template'];

        $_REQUEST['template_to_perfom'] = $templateid;
        $pobierzdane = $adb->query("select osspdf_pdf_format, osspdf_pdf_orientation, filename, left_margin, right_margin, top_margin, bottom_margin from vtiger_osspdf where osspdfid = '$templateid'", true);
        $_REQUEST['pdf_format'] = $adb->query_result($pobierzdane, 0, "osspdf_pdf_format");

        $pdf_orientation_result = $adb->query_result($pobierzdane, 0, "osspdf_pdf_orientation");
        $_REQUEST['file_name'] = $adb->query_result($pobierzdane, 0, "filename");

        $_REQUEST['left'] = $adb->query_result($pobierzdane, 0, "left_margin");
        $_REQUEST['right'] = $adb->query_result($pobierzdane, 0, "right_margin");
        $_REQUEST['top'] = $adb->query_result($pobierzdane, 0, "top_margin");
        $_REQUEST['bottom'] = $adb->query_result($pobierzdane, 0, "bottom_margin");
		$_SESSION['top'] = $_REQUEST['top'];
        if ($pdf_orientation_result == 'Portrait') {
            $pdf_orientation = "P";
        } elseif ($pdf_orientation_result == 'Landscape') {
            $pdf_orientation = "L";
        }
		$default_margin = 10;
        $pdf = new Printer();
        $pdf->setPageFormat($_REQUEST['pdf_format'], $pdf_orientation);
        //$pdf->setPrintHeader(false);
        //$pdf->setPrintFooter(false);
        $pdf->SetCompression(true);

		if ( $_REQUEST['left'] != '' ) {
			$pdf->SetLeftMargin($_REQUEST['left']);
		}else{
			$pdf->SetLeftMargin( $default_margin );
		}

		if ( $_REQUEST['right'] != '' ) {
			$pdf->SetRightMargin($_REQUEST['right']);
		}else{
			$pdf->SetRightMargin( $default_margin );
		}
/*
		if ( $_REQUEST['top'] != '' ) {
			$pdf->SetTopMargin($_REQUEST['top']);
		}else{
			$pdf->SetTopMargin( $default_margin );
		}

		if ( $_REQUEST['bottom'] != '' ) {
			$pdf->SetAutoPageBreak(true, $_REQUEST['bottom']);
		}else{
			$pdf->SetAutoPageBreak( $default_margin );
		}
		*/
        $_SESSION['type'] = "multiple";

        GeneratePDF($module, $pdf, $pdf_orientation);
    }
    exit;
}
?>
