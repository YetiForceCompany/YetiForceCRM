{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="col-xs-12 paddingLRZero">
		{if count($DASHBOARD_TYPES) > 1}
			<ul class="nav nav-tabs massEditTabs selectDashboard">
				{foreach from=$DASHBOARD_TYPES item=DASHBOARD}
					<li class="nav-item{if $CURRENT_DASHBOARD eq $DASHBOARD['dashboard_id']} active{/if}" data-id="{$DASHBOARD['dashboard_id']}">
						<a class="nav-link" data-toggle="tab"><strong>{\App\Language::translate($DASHBOARD['name'])}</strong></a>
					</li>
				{/foreach}
			</ul>
		{/if}
		{if count($MODULES_WITH_WIDGET) > 1 && ($MODULE_NAME eq 'Home' || $SRC_MODULE_NAME eq 'Home')}
			<ul class="nav nav-tabs massEditTabs selectDashboradView">
				{foreach from=$MODULES_WITH_WIDGET item=MODULE_WIDGET}
					<li class="{if $MODULE_NAME eq $MODULE_WIDGET} active {/if}" data-module="{$MODULE_WIDGET}"><a>{\App\Language::translate($MODULE_WIDGET, $MODULE_WIDGET)}</a></li>
						{/foreach}
			</ul>
		{/if}
	</div>
	{include file=\App\Layout::getTemplatePath('dashboards/DashBoardButtons.tpl', $MODULE)}
	<div class="col-xs-12 paddingLRZero">
	{/strip}
