{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if count($DASHBOARD_TYPES) > 1}
		<ul class="nav nav-tabs massEditTabs selectDashboard m-2">
			{foreach from=$DASHBOARD_TYPES item=DASHBOARD}
				<li class="nav-item" data-id="{$DASHBOARD['dashboard_id']}">
					<a class="nav-link{if $CURRENT_DASHBOARD eq $DASHBOARD['dashboard_id']} active{/if}" href="#" data-toggle="tab"><strong>{\App\Language::translate($DASHBOARD['name'])}</strong></a>
				</li>
			{/foreach}
		</ul>
	{/if}
	{if count($MODULES_WITH_WIDGET) > 1 && ($MODULE_NAME eq 'Home' || $SRC_MODULE_NAME eq 'Home')}
		<ul class="nav nav-tabs massEditTabs selectDashboradView m-2">
			{foreach from=$MODULES_WITH_WIDGET item=MODULE_WIDGET}
				<li class="nav-item" data-module="{$MODULE_WIDGET}">
					<a class="nav-link{if $MODULE_NAME eq $MODULE_WIDGET} active {/if}" href="#" data-toggle="tab">{\App\Language::translate($MODULE_WIDGET, $MODULE_WIDGET)}</a></li>
				{/foreach}
		</ul>
	{/if}
	{/strip}
