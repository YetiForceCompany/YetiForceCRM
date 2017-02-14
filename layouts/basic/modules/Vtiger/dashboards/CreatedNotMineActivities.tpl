{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</strong></div>
		</div>
		<div class="col-md-4">
			<div class="box pull-right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</div>
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row" >
		<div class="col-sm-12">
			<div class="pull-right">&nbsp;
				<button class="btn btn-default btn-sm changeRecordSort" title="{vtranslate('LBL_SORT_DESCENDING', $MODULE_NAME)}" alt="{vtranslate('LBL_SORT_DESCENDING', $MODULE_NAME)}" data-sort="{if $DATA['sortorder'] eq 'desc'}asc{else}desc{/if}" data-asc="{vtranslate('LBL_SORT_ASCENDING', $MODULE_NAME)}" data-desc="{vtranslate('LBL_SORT_DESCENDING', $MODULE_NAME)}">
					<span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true" ></span>
				</button>
			</div>
			<div class="col-sm-6 pull-right">
				{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
			</div>
		</div>
	</div>
</div>
<div name="history" class="dashboardWidgetContent">
	{include file="dashboards/CalendarActivitiesContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
</div>
{/strip}
