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
	<!-- tpl-Settings-Base-ReAlignSettingsShortCut-->
	{assign var=SPAN_COUNT value=1}
	{foreach item=SETTING_SHORTCUT from=$SETTINGS_SHORTCUT name=shortcuts}
		<div class="tpl-Settings-Base-ReAlignSettingsShortCut col-lg-12 col-xl-3 mx-2 mb-3 p-3 contentsBackground well u-cursor-pointer moduleBlock"
			 id="shortcut_{$SETTING_SHORTCUT->getId()}" data-actionurl="{$SETTING_SHORTCUT->getPinUnpinActionUrl()}"
			 data-url="{$SETTING_SHORTCUT->getUrl()}">
			<div class="d-flex align-items-center">
				<h5 class="themeTextColor mb-0">
					<span class="{$SETTING_SHORTCUT->get('iconpath')} mr-2"></span>
					{\App\Language::translate($SETTING_SHORTCUT->get('name'), Vtiger_Menu_Model::getModuleNameFromUrl($SETTING_SHORTCUT->getUrl()))}
				</h5>
				<button data-id="{$SETTING_SHORTCUT->getId()}" title="{\App\Language::translate('LBL_REMOVE',$MODULE)}"
						title="Close" type="button" class="unpin close ml-auto">
					<span class="fas fa-times"></span>
				</button>
			</div>
			<div>{\App\Language::translate($SETTING_SHORTCUT->get('description'), Vtiger_Menu_Model::getModuleNameFromUrl($SETTING_SHORTCUT->getUrl()))}</div>
		</div>
		{if $SPAN_COUNT==3}
			{$SPAN_COUNT=1}
			{continue}
		{/if}
		{$SPAN_COUNT=$SPAN_COUNT+1}
	{/foreach}
	<!-- /tpl-Settings-Base-ReAlignSettingsShortCut-->
{/strip}
