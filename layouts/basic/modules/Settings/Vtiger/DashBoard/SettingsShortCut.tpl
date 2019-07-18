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
	<div class="tpl-Settings-Base-DashBoard-SettingsShortCut dashboardWidget contentsBackground u-cursor-pointer moduleBlock px-1 py-2 mb-n1 mt-3 u-columns__item d-inline-block"
		 id="shortcut_{$SETTINGS_SHORTCUT->getId()}" data-actionurl="{$SETTINGS_SHORTCUT->getPinUnpinActionUrl()}"
		 data-url="{$SETTINGS_SHORTCUT->getUrl()}">
		<div class="d-flex flex-nowrap">
			<div class="display-4 px-1">
				<span class="{$SETTINGS_SHORTCUT->get('iconpath')}"></span>
			</div>
			<div class="d-flex flex-column px-1 w-100 position-relative">
				<div class="d-flex position-relative">
						{include file=\App\Layout::getTemplatePath('DashBoard/WidgetTitle.tpl', $QUALIFIED_MODULE) CLASS='themeTextColor pr-1'
						TITLE=\App\Language::translate($SETTINGS_SHORTCUT->get('name'), Vtiger_Menu_Model::getModuleNameFromUrl($SETTINGS_SHORTCUT->get('linkto')))}
					<button data-id="{$SETTINGS_SHORTCUT->getId()}"
					title="{\App\Language::translate('LBL_REMOVE', $QUALIFIED_MODULE)}" title="Close" type="button"
					class="unpin close text-grey-6 position-absolute u-position-r-0 px-0 ml-auto mt-n2 mr-n2">
						<span>&times;</span>
					</button>
				</div>
				{include file=\App\Layout::getTemplatePath('DashBoard/WidgetDescription.tpl', $QUALIFIED_MODULE) CLASS='pr-1'
				DESCRIPTION=\App\Language::translate($SETTINGS_SHORTCUT->get('description'), Vtiger_Menu_Model::getModuleNameFromUrl($SETTINGS_SHORTCUT->get('linkto')))}
				<span class="fas fa-ellipsis-v position-absolute u-position-r-0 mt-4 text-grey-5"></span>
			</div>
		</div>
	</div>
{/strip}
