{*<!--
/************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
************************************************************************************/
-->*}
{strip}
	<div class="row">
		<nav class="o-breadcrumb widget_header col-12 px-3 d-xsm-flex align-items-center flex-xsm-row" aria-label="{\App\Language::translate("LBL_BREADCRUMB")}">
			{if {$MODULE} neq 'Home'}
				<div class="listViewMassActions px-2">
					{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] BTN_GROUP=false CLASS=buttonTextHolder}
				</div>
			{/if}
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('dashboards/DashBoardButtons.tpl', $MODULE)}
		</nav>
	</div>
{/strip}
