{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="col-xs-12 paddingLRZero">
		<ul class="nav nav-tabs massEditTabs selectDashboradView">
			{foreach from=$MODULES_WITH_WIDGET item=MODULE_WIDGET}
				<li class="{if $MODULE_NAME eq $MODULE_WIDGET} active {/if}" data-module="{$MODULE_WIDGET}"><a>{vtranslate($MODULE_WIDGET, $MODULE_WIDGET)}</a></li>
			{/foreach}
		</ul>
	</div>
	{include file='dashboards/DashBoardButtons.tpl'|@vtemplate_path:$MODULE}
	<div class="col-xs-12 paddingLRZero">
{/strip}
