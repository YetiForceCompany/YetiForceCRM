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
	<div class="col-md-3 mr-3 mb-3 contentsBackground well cursorPointer moduleBlock" id="shortcut_{$SETTINGS_SHORTCUT->getId()}" data-actionurl="{$SETTINGS_SHORTCUT->getPinUnpinActionUrl()}" data-url="{$SETTINGS_SHORTCUT->getUrl()}">
		<button data-id="{$SETTINGS_SHORTCUT->getId()}" title="{\App\Language::translate('LBL_REMOVE',$QUALIFIED_MODULE)}" title="Close" type="button" class="unpin close">x</button>
		<h5 class="themeTextColor">{\App\Language::translate($SETTINGS_SHORTCUT->get('name'),Vtiger_Menu_Model::getModuleNameFromUrl($SETTINGS_SHORTCUT->get('linkto')))}</h5>
		<div>{\App\Language::translate($SETTINGS_SHORTCUT->get('description'),Vtiger_Menu_Model::getModuleNameFromUrl($SETTINGS_SHORTCUT->get('linkto')))}</div>
	</div>
{/strip}	
