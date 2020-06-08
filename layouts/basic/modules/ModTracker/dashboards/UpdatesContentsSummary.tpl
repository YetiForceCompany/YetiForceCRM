{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-ModTracker-dashboards-UpdatesContentsSummary -->
	<input type="hidden" class="js-widget-data" value="{\App\Purifier::encodeHtml(App\Json::encode($WIDGET_DATA))}" data-js="value">
		{if $UPDATES}
			{function DISPLAY_RECORD_NAME RECORD_MODEL=false CHECK_PERMISSIONS=true SHOW_MODULE=true}
				{if $RECORD_MODEL}
					{assign var=DISPLAY_TEXT value=$RECORD_MODEL->getName()}
					{if $RECORD_MODEL->getModuleName() eq 'ModComments'}
						{assign var=IS_PERMITTED_RECORD value=false}
						{assign var=DISPLAY_TEXT value=\App\Utils\Completions::decode(Vtiger_Util_Helper::toVtiger6SafeHTML(\App\Purifier::decodeHtml($RECORD_MODEL->getName())))}
					{else if $CHECK_PERMISSIONS}
						{assign var=IS_PERMITTED_RECORD value=$RECORD_MODEL->isViewable()}
					{else}
						{assign var=IS_PERMITTED_RECORD value=true}
					{/if}
					{if $SHOW_MODULE}
						<span class="yfm-{$RECORD_MODEL->getModuleName()} fa-lg fa-fw mr-1"
							title="{\App\Language::translateSingularModuleName($RECORD_MODEL->getModuleName())}"></span>
					{/if}
					<span {if $IS_PERMITTED_RECORD}
						class="js-popover-tooltip--ellipsis u-text-ellipsis--no-hover" data-toggle="popover"
						data-content="{\App\Purifier::encodeHtml($DISPLAY_TEXT)}"
						data-js="popover"{else}class="text-truncate"{/if}>
						{if $IS_PERMITTED_RECORD}
							<a class="modCT_{$RECORD_MODEL->getModuleName()} js-popover-tooltip--record"
								href="{$RECORD_MODEL->getDetailViewUrl()}">
								{$DISPLAY_TEXT}
							</a>
						{else}
							<strong>{$DISPLAY_TEXT}</strong>
						{/if}
					</span>
				{/if}
			{/function}
			<div class="" style="margin: -5px; line-height: 1;">
				<div class="table-responsive">
					<table class="config-table table u-word-break-all">
						<thead>
							<th class="u-white-space-nowrap">{\App\Language::translate('LBL_MODULE_NAME', $MODULE_NAME)}</th>
							{foreach from=$ACTIONS item=KEY}
								<th class="u-white-space-nowrap text-center">
									<span class="mr-1" style="color: {ModTracker::$colorsActions[$KEY]};">
										<span class="{ModTracker::$iconActions[$KEY]} fa-fw"></span>
									</span>
									{\App\Utils::mbUcfirst(\App\Language::translate(ModTracker_Record_Model::$statusLabel[$KEY], $MODULE_NAME))}
								</th>
							{/foreach}
						</thead>
						<tbody>
							{foreach item=UPDATE_ROW key=UPDATE_MODULE_NAME from=$UPDATES}
								<tr>
									<td class="u-white-space-nowrap">
										<span class='modCT_{$UPDATE_MODULE_NAME} yfm-{$UPDATE_MODULE_NAME} mr-1'></span>
										{\App\Language::translate($UPDATE_MODULE_NAME, $UPDATE_MODULE_NAME)}
									</td>
									{foreach from=$ACTIONS item=KEY}
										<td class="text-center">
											{if isset($UPDATE_ROW[$KEY])}{$UPDATE_ROW[$KEY]}{/if}
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
