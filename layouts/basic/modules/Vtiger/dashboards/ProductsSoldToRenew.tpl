{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
	{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
	{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
	<div class="dashboardWidgetHeader">
		<div class="row">
			<div class="col-md-8">
				<h5 class="dashboardTitle h6" title="{\App\Language::translate($WIDGET->getTitle())}"><strong>{\App\Language::translate($WIDGET->getTitle())}</strong></h5>
			</div>
			<div class="col-md-4">
				<div class="box float-right">
					{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
				</div>
			</div>
		</div>
		<hr class="widgetHr" />
		<div class="row" >
			<div class="col-md-6 input-group input-group-sm">
				<div class="input-group-prepend">
					<span class="input-group-text">
						<span class="fas fa-filter"></span>
					</span>
				</div>
				<select class="widgetFilter form-control orderby" aria-label="Small" aria-describedby="inputGroup-sizing-sm" name="orderby" title="{\App\Language::translate('LBL_CUSTOM_FILTER')}">
					{foreach item=FIELD from=$WIDGET_MODEL->getHeaders()}
						{assign var="FIELD_VALUE" value=$FIELD->get('name')}
						<option value="{$FIELD_VALUE}" {if $DATA['orderby'] eq $FIELD_VALUE} selected {/if}>{\App\Language::translate($FIELD->get('label'),$BASE_MODULE)}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-md-6">
				{if $LISTVIEWLINKS}
					<div class="float-right">&nbsp;
						<button class="btn btn-light btn-sm goToListView" data-url="{$WIDGET_MODEL->getTargetModuleModel()->getListViewUrl()}" title="{\App\Language::translate('LBL_GO_TO_RECORDS_LIST', $MODULE_NAME)}" >
							<span class="fas fa-th-list"></span>
						</button>
					</div>
				{/if}
				<div class="float-right">&nbsp;
					<button class="btn btn-light btn-sm changeRecordSort" title="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}" alt="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}" data-sort="{if $DATA['sortorder'] eq 'desc'}asc{else}desc{/if}" data-asc="{\App\Language::translate('LBL_SORT_ASCENDING', $MODULE_NAME)}" data-desc="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}">
						<span class="fas fa-sort-amount-down" ></span>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/ProductsSoldToRenewContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
