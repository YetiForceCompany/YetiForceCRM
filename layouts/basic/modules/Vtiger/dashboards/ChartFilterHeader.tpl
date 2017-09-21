{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="dashboardWidgetHeader">
		{foreach key=index item=cssModel from=$STYLES}
			<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
		{/foreach}
		{foreach key=index item=jsModel from=$SCRIPTS}
			<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
		{/foreach}
		<div class="row">
			<div class="col-md-8">
				<div class="dashboardTitle" title="{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</strong></div>
			</div>
			<div class="col-md-4">
				<div class="box pull-right">
					{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
				</div>
			</div>
		</div>
		{if $WIDGET_DATA['timeRange'] || $WIDGET_DATA['showOwnerFilter']}	 
			<hr class="widgetHr" />
		{/if}
		<div class="row" >
			{assign var="WIDGET_DATA" value=$WIDGET->getArray('data')}
			{if $WIDGET_DATA['timeRange']}
				<div class="col-md-6">
					<div class="input-group input-group-sm">
						<span class=" input-group-addon"><span class="glyphicon glyphicon-calendar iconMiddle "></span></span>
						<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" placeholder="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter width90 form-control" />
					</div>	
				</div>
			{/if}
			{if $WIDGET_DATA['showOwnerFilter']}
				<div class="col-md-6 ownersFilter">
					<div class="input-group input-group-sm">
						<span class="input-group-addon"><span class="glyphicon glyphicon-user iconMiddle" title="{\App\Language::translate('Assigned To')}"></span></span>
						<select class="widgetFilter select2 width90 owner form-control input-sm" name="owner" title="{\App\Language::translate('LBL_OWNER')}">

						</select>
					</div>
				</div>
			{/if}
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/ChartFilterContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
	</div>
	<div class="dashboardWidgetFooter">
		{include file=\App\Layout::getTemplatePath('dashboards/ChartFilterFooter.tpl', $MODULE_NAME)}
	</div>
{/strip}
