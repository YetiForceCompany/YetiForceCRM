{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-ModTracker-dashboards-UpdatesContentsSummary -->
	<input type="hidden" class="js-widget-data" value="{\App\Purifier::encodeHtml(App\Json::encode($WIDGET_DATA))}" data-js="value">
	{if $UPDATES}
		<div style="margin: -5px; line-height: 1;">
			<div class="table-responsive">
				<table class="config-table table u-word-break-all">
					<thead>
						<th class="u-white-space-nowrap">{\App\Language::translate('LBL_MODULE_NAME', $MODULE_NAME)}</th>
						{foreach from=$ACTIONS item=KEY}
							<th class="u-white-space-nowrap text-center u-fs-10px p-1" title="{\App\Utils::mbUcfirst(\App\Language::translate(ModTracker_Record_Model::$statusLabel[$KEY], $MODULE_NAME))}">
								<span class="mr-1" style="color: {ModTracker::$colorsActions[$KEY]};">
									<span class="{ModTracker::$iconActions[$KEY]} fa-fw"></span>
								</span>
								<div class="mt-1">
									{App\TextUtils::textTruncate(\App\Utils::mbUcfirst(\App\Language::translate(ModTracker_Record_Model::$statusLabel[$KEY], $MODULE_NAME)), 15)}
								</div>
							</th>
						{/foreach}
					</thead>
					<tbody>
						{foreach item=UPDATE_ROW key=UPDATE_MODULE_NAME from=$UPDATES}
							<tr>
								<td class="u-white-space-nowrap pr-0">
									<span class='modCT_{$UPDATE_MODULE_NAME} yfm-{$UPDATE_MODULE_NAME} mr-1'></span>
									{\App\Language::translate($UPDATE_MODULE_NAME, $UPDATE_MODULE_NAME)}
								</td>
								{foreach from=$ACTIONS item=KEY}
									<td class="text-center noWrap listButtons narrow">
										{if isset($UPDATE_ROW[$KEY])}
											<button type="button" class="btn btn-sm btn-outline-light js-history-detail"
												title="{\App\Utils::mbUcfirst(\App\Language::translate(ModTracker_Record_Model::$statusLabel[$KEY], $MODULE_NAME))}"
												data-action="{$KEY}" data-module="{$UPDATE_MODULE_NAME}">
												<span class="mr-1" style="color: {ModTracker::$colorsActions[$KEY]};">
													<span class="{ModTracker::$iconActions[$KEY]} fa-fw"></span>
												</span>
												<span class="u-fs-xs text-dark">{$UPDATE_ROW[$KEY]}</span>
											</button>
										{/if}
									</td>
								{/foreach}
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	{else}
		<span class="noDataMsg">
			{\App\Language::translate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA', $MODULE_NAME)}
		</span>
	{/if}
	<!-- /tpl-ModTracker-dashboards-UpdatesContentsSummary -->
{/strip}
