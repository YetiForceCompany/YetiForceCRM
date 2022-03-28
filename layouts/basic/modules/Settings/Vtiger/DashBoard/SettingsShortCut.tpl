{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<a class="tpl-Settings-Base-DashBoard-SettingsShortCut dashboardWidget text-black js-shortcut c-menu-shortcut bg-white u-bg-white-darken u-cursor-pointer px-1 py-2 mt-3 mr-3 flex-grow-1 u-w-max-320px"
		id="shortcut_{$SETTINGS_SHORTCUT->getId()}" data-actionurl="{$SETTINGS_SHORTCUT->getPinUnpinActionUrl()}"
		href="{$SETTINGS_SHORTCUT->getUrl()}" data-js="container | remove">
		<div class="d-flex flex-nowrap">
			<div class="u-fs-38px my-auto px-1">
				<span class="{$SETTINGS_SHORTCUT->get('iconpath')}"></span>
			</div>
			<div class="d-flex flex-column px-1 w-100 position-relative">
				<div class="d-flex position-relative">
					{include file=\App\Layout::getTemplatePath('DashBoard/WidgetTitle.tpl', $QUALIFIED_MODULE) CLASS='themeTextColor pr-1'
							TITLE=\App\Language::translate($SETTINGS_SHORTCUT->get('name'), $SETTINGS_SHORTCUT->getModuleName())}
					<button data-id="{$SETTINGS_SHORTCUT->getId()}"
						title="{\App\Language::translate('LBL_REMOVE', $QUALIFIED_MODULE)}" title="Close" type="button"
						class="unpin close position-absolute u-font-weight-550 u-position-r-0 px-0 ml-auto mt-n2 mr-n2">
						<span>&times;</span>
					</button>
				</div>
				{include file=\App\Layout::getTemplatePath('DashBoard/WidgetDescription.tpl', $QUALIFIED_MODULE) CLASS='pr-1'
					DESCRIPTION=\App\Language::translate($SETTINGS_SHORTCUT->get('description'), $SETTINGS_SHORTCUT->getModuleName())}
				<span class="fas fa-ellipsis-v position-absolute text-muted u-position-r-0 u-cursor-grab mt-4 pl-2 js-drag-handler"></span>
			</div>
		</div>
	</a>
{/strip}
