{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div class="col-lg-12 col-xl-3 mx-2 mb-3 p-3 contentsBackground well u-cursor-pointer moduleBlock" id="shortcut_{$SETTINGS_SHORTCUT->getId()}" data-actionurl="{$SETTINGS_SHORTCUT->getPinUnpinActionUrl()}" data-url="{$SETTINGS_SHORTCUT->getUrl()}">
		<div class="d-flex align-items-center">
		<h5 class="themeTextColor col-6 px-0 mb-0 text-left">{\App\Language::translate($SETTINGS_SHORTCUT->get('name'),Vtiger_Menu_Model::getModuleNameFromUrl($SETTINGS_SHORTCUT->get('linkto')))}</h5>
			<button data-id="{$SETTINGS_SHORTCUT->getId()}" title="{\App\Language::translate('LBL_REMOVE',$QUALIFIED_MODULE)}" title="Close" type="button" class="unpin close col-6 px-0 text-right">
				<span class="fas fa-times"></span>
			</button>
		</div>
		<div>{\App\Language::translate($SETTINGS_SHORTCUT->get('description'),Vtiger_Menu_Model::getModuleNameFromUrl($SETTINGS_SHORTCUT->get('linkto')))}</div>
	</div>
{/strip}	
