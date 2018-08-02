{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}

<div class="dashboardWidgetHeader">
	{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeader.tpl', $MODULE_NAME) SETTING_EXIST=true}
	<div class="row filterContainer d-none" style="position:absolute;z-index:100001">
		<div class="row">
			<span class="col-md-5">
				<span class="float-right">
					{\App\Language::translate('Created Time', $MODULE_NAME)} &nbsp; {\App\Language::translate('LBL_BETWEEN', $MODULE_NAME)}
				</span>
			</span>
			<span class="col-md-4">
				<input type="text" name="createdtime" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter" />
			</span>
		</div>
		<div class="row">
			<span class="col-md-5">
				<span class="float-right">
					{\App\Language::translate('Assigned To', $MODULE_NAME)}
				</span>
			</span>
			<span class="col-md-4">
				<select class="widgetFilter" name="owner">
					<option value="">{\App\Language::translate('LBL_ALL', $MODULE_NAME)}</option>
					{foreach key=USER_ID item=USER_NAME from=$ACCESSIBLE_USERS}
						<option value="{$USER_ID}">
							{if $USER_ID eq $CURRENTUSERID}
								{\App\Language::translate('LBL_MINE',$MODULE_NAME)}
							{else}
								{$USER_NAME}
							{/if}
						</option>
					{/foreach}
				</select>
			</span>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
</div>
