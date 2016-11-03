{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="col-xs-12 paddingLRZero">
		<ul class="nav nav-tabs massEditTabs selectDashboard">
			{if count($DASHBOARD_TYPES) > 1}
				{foreach from=$DASHBOARD_TYPES item=DASHBOARD}
					<li {if $CURRENT_DASHBOARD eq $DASHBOARD['dashboard_id']}class="active"{/if} data-id="{$DASHBOARD['dashboard_id']}">
						<a data-toggle="tab"><strong>{$DASHBOARD['name']}</strong></a>
					</li>
				{/foreach}
			{/if}
		</ul>
		{if count($MODULES_WITH_WIDGET) > 1 && ($MODULE_NAME eq 'Home' || $SRC_MODULE_NAME eq 'Home')}
			<ul class="nav nav-tabs massEditTabs selectDashboradView">
				{foreach from=$MODULES_WITH_WIDGET item=MODULE_WIDGET}
					<li class="{if $MODULE_NAME eq $MODULE_WIDGET} active {/if}" data-module="{$MODULE_WIDGET}"><a>{vtranslate($MODULE_WIDGET, $MODULE_WIDGET)}</a></li>
						{/foreach}
			</ul>
		{/if}
	</div>
	{include file='dashboards/DashBoardButtons.tpl'|@vtemplate_path:$MODULE}
	<div class="col-xs-12 paddingLRZero">
{/strip}
