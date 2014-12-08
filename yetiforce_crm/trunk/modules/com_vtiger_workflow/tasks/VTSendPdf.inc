<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTSendPdf extends VTTask {
	
	public $executeImmediately = true; 

	
	public function doTask($entity) {
		list(, $entityId) = explode('x', $entity->getId());
		$module = $entity->getModuleName();
		
		if((is_numeric($this->pdf_tpl) && $this->pdf_tpl != 0) 
				&& (is_numeric($this->email_tpl) && $this->email_tpl != 0)){
			if (false === strpos($this->email_fld, '=')) {
				$email = $entity->get($this->email_fld);
			} else {
				list($parentIdFieldName, $relModuleName, $relModuleField) = explode("=", $this->email_fld);
				list(, $relRecord) = explode('x', $entity->get($parentIdFieldName));
				if($module == $relModuleName)
					$relRecord=$entityId;
				if(is_numeric($relRecord) && $relRecord != '' && $relRecord != 0){
					$Record_Model = Vtiger_Record_Model::getInstanceById($relRecord, $relModuleName);
					$email = $Record_Model->get($relModuleField);
				}	
			}
		}
		
		$_REQUEST['recordid'] = $entityId;
		$_REQUEST['pdfajax'] = 'true';
		$_REQUEST['fromdetailview'] = 'yes';
		$_REQUEST['template'] = $this->pdf_tpl;
		$_REQUEST['export_data'] = 'all';
		$_REQUEST['ParticularSave'] = '';
		$_REQUEST['return_name'] = 'yes';
		$_REQUEST['soap_pdf'] = 'yes';
		$_REQUEST['pdf_lang'] = 'pl_pl';
		$_REQUEST['usingmodule'] = $module;
		
		require_once( 'modules/OSSPdf/PDFExport.php' );
		$pdfName = Soap_generatePDF( 1 );
		if($email != ''){
			$data = array(
				'id' => $this->email_tpl,
				'to_email' => $email,
				'module' => $module,
				'record' => $entityId,
				'attachment_src' => array("$pdfName" => 'storage' . DIRECTORY_SEPARATOR . $pdfName),
			);
			$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
			$recordModel->sendMailFromTemplate($data);
		}
	}

	public function getFieldNames() {
		return array("pdf_tpl", "email_tpl", "email_fld");
	}

}

