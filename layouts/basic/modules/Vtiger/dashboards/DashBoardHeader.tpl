{*<!--
/************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
************************************************************************************/
-->*}
{strip}
	<div class="row">
		<nav class="widget_header col-12 px-2 d-flex align-items-center">
			<div class="listViewMassActions pr-2 modOn_{$MODULE}">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] BTN_GROUP=false CLASS=buttonTextHolder}
			</div>
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			{include file=\App\Layout::getTemplatePath('dashboards/DashBoardButtons.tpl', $MODULE)}
		</nav>
	</div>
{/strip}
