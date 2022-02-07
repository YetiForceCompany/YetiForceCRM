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
	<!-- tpl-Home-dashboards-DashBoardPreProcess -->
	{include file=\App\Layout::getTemplatePath('PageHeader.tpl', $MODULE_NAME)}
	<div class="bodyContents">
		<div class="mainContainer">
			<div class="contentsDiv mx-md-0 dashboardContainer">
				{include file=\App\Layout::getTemplatePath('dashboards/DashBoardHeader.tpl', $MODULE_NAME) DASHBOARDHEADER_TITLE=\App\Language::translate($MODULE, $MODULE)}
				<div class="dashboardViewContainer">
					{include file=\App\Layout::getTemplatePath('dashboards/DashBoardPreProcessAjax.tpl', $MODULE_NAME)}
					<!-- /tpl-Home-dashboards-DashBoardPreProcess -->
{/strip}
