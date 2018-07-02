{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
	{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
	{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME) CLASSNAME="col-md-10"}
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
		</div>
		<hr class="widgetHr" />
		<div class="d-flex flex-nowrap" >
			{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
			<button class="btn btn-light btn-sm changeRecordSort" title="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}" alt="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}" data-sort="{if $DATA['sortorder'] eq 'desc'}asc{else}desc{/if}" data-asc="{\App\Language::translate('LBL_SORT_ASCENDING', $MODULE_NAME)}" data-desc="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}">
				<span class="fas fa-sort-amount-down" aria-hidden="true" ></span>
			</button>
		</div>
	</div>
	<div name="history" class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/CalendarActivitiesContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
	</div>
{/strip}
