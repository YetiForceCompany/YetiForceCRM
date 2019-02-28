{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-ProjectTask-Dashboard-UpcomingProjectTasks dashboardWidgetHeader">
		{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
		{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
		{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME) CLASSNAME="col-md-6"}
			<div class="d-inline-flex">
				{if !empty($LISTVIEWLINKS)}
					<button class="btn btn-light btn-sm ml-1 goToListView"
							title="{\App\Language::translate('LBL_GO_TO_RECORDS_LIST', $MODULE_NAME)}">
						<span class="fas fa-th-list"></span>
					</button>
				{/if}
				{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
			</div>
		</div>
		<hr class="widgetHr"/>
		<div class="row no-gutters">
			<div class="col-ceq-xsm-6">
				<div class="input-group input-group-sm">
					<span class="input-group-prepend">
						<span class="input-group-text">
							<span class="fas fa-filter iconMiddle margintop3"
								  title="{\App\Language::translate('Assigned To', $MODULE_NAME)}"></span>
						</span>
					</span>
					<select class="widgetFilter select2 form-control" aria-label="Small"
							aria-describedby="inputGroup-sizing-sm" name="projecttaskpriority"
							title="{\App\Language::translate('LBL_TICKET_PRIORITY',$MODULE_NAME)}">
						<option value="all">{\App\Language::translate('LBL_ALL')}</option>
						{foreach item=ITEM from=\App\Fields\Picklist::getValues('projecttaskpriority')}
							<option value="{$ITEM['picklistValue']}"{if $ITEM['picklistValue'] === $TICKETPRIORITY} selected{/if}>{\App\Language::translate($ITEM['picklistValue'],$MODULE_NAME)} {$MODULE_NAME}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="col-ceq-xsm-6">
				{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<div name="history" class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/UpcomingProjectTasksContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
	</div>
{/strip}
