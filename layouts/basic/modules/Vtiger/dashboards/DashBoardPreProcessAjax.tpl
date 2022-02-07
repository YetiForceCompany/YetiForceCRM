{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-dashboards-DashBoardPreProcessAjax mt-2 mb-2">
		<ul class="nav nav-tabs massEditTabs selectDashboard{if count($DASHBOARD_TYPES) === 1} d-none{/if} ml-sm-2">
			{foreach from=$DASHBOARD_TYPES item=DASHBOARD}
				<li class="nav-item" data-id="{$DASHBOARD['dashboard_id']}" data-js="data-id">
					<a class="nav-link {if $CURRENT_DASHBOARD eq $DASHBOARD['dashboard_id']} active {/if}"
						href="#"
						data-toggle="tab">
						<strong>{\App\Language::translate($DASHBOARD['name'])}</strong>
					</a>
				</li>
			{/foreach}
		</ul>
		{if $MODULES_WITH_WIDGET}
			{assign var=COUNT value=count($MODULES_WITH_WIDGET)}
			<ul class="nav nav-inverted-tabs massEditTabs selectDashboradView ml-sm-2">
				{foreach from=$MODULES_WITH_WIDGET item=MODULE_WIDGET}
					<li class="nav-item" data-module="{$MODULE_WIDGET}">
						<a class="nav-link pt-1 pb-1 {if $MODULE_NAME eq $MODULE_WIDGET} active {/if}{if $COUNT === 1} d-none{/if}"
							href="#"
							data-toggle="tab">
							<span class="yfm-{$MODULE_WIDGET} mx-1"></span>
							{\App\Language::translate($MODULE_WIDGET, $MODULE_WIDGET)}
						</a>
					</li>
				{/foreach}
			</ul>
		{/if}
	</div>
{/strip}
