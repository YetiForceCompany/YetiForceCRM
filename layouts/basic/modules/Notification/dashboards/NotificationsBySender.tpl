{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="dashboardWidgetHeader">
		<div class="row">
			<div class="col-md-8">
				<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), 'Home')}"><strong>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), 'Home')}</strong></div>
			</div>
			<div class="col-md-4">
				<div class="box pull-right">
					{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
				</div>
			</div>
		</div>
		<hr class="widgetHr"/>
		<div class="row" >
			<div class="col-sm-6">
				<div class="input-group input-group-sm">
					<span class=" input-group-addon"><span class="glyphicon glyphicon-calendar iconMiddle margintop3"></span></span>
					<input type="text" name="time" title="{vtranslate('Created Time', $MODULE_NAME)}" class="dateRange form-control widgetFilter width90" value="{implode(',',$DTIME)}"/>
				</div>
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file="dashboards/DashBoardWidgetContents.tpl"|@vtemplate_path:$MODULE_NAME}
	</div>
{/strip}
