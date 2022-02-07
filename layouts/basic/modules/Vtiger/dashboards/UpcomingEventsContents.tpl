{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Dashboards-UpcomingEvents -->
	{if $RECORDS}
		<div class="dashboardWidgetContent">
			<div class="row">
				<div class="col-8 px-0">
					<strong>{\App\Language::translate('LBL_NAME')}</strong>
				</div>
				<div class="col-4 px-0">
					<strong>{$FIELD_NAME}</strong>
				</div>
			</div>
			{foreach item=RECORD from=$RECORDS}
				<div class="row">
					<div class="col-8 px-0">
						<a href='{$RECORD['url']}' class="modCT_{$MODULE_NAME} js-popover-tooltip--record">
							{$RECORD['name']}
						</a>
					</div>
					<div class="col-4 px-0">
						{$RECORD['value']}
					</div>
				</div>
			{/foreach}
		</div>
	{/if}
	<!--/ tpl-Base-Dashboards-UpcomingEvents -->
{/strip}
