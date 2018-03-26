<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Contacts_Edit_View extends Vtiger_Edit_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('IMAGE_DETAILS', $this->record->getImageDetails());

		$salutationFieldModel = Vtiger_Field_Model::getInstance('salutationtype', $this->record->getModule());
		// Fix for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/7851
		$salutationType = $request->get('salutationtype');
		if (!empty($salutationType)) {
			$salutationFieldModel->set('fieldvalue', $salutationFieldModel->getUITypeModel()->getDBValue($salutationType, $this->record));
		} else {
			$salutationFieldModel->set('fieldvalue', $this->record->get('salutationtype'));
		}
		$viewer->assign('SALUTATION_FIELD_MODEL', $salutationFieldModel);

		parent::process($request);
	}

	/**
	 * Get header css files that need to loaded in the page.
	 *
	 * @param \App\Request $request Request instance
	 *
	 * @return Vtiger_CssScript_Model[]
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/blueimp-file-upload/css/jquery.fileupload.css',
		]), parent::getHeaderCss($request));
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'~libraries/blueimp-file-upload/js/vendor/jquery.ui.widget.js',
			'~libraries/blueimp-file-upload/js/jquery.iframe-transport.js',
			'~libraries/blueimp-load-image/js/load-image.all.min.js',
			'~libraries/blueimp-canvas-to-blob/js/canvas-to-blob.js',
			'~libraries/blueimp-file-upload/js/jquery.fileupload.js',
			'~libraries/blueimp-file-upload/js/jquery.fileupload-process.js',
			'~libraries/blueimp-file-upload/js/jquery.fileupload-image.js',
		]), parent::getFooterScripts($request));
	}
}
