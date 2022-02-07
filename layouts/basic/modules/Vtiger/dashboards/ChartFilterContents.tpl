{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" name="typeChart" value="{$CHART_MODEL->getType()}">
	<input type="hidden" name="stacked" value="{$CHART_STACKED}">
	<input type="hidden" name="colorsFromDividingField" value="{$CHART_COLORS_FROM_DIVIDING_FIELD}">
	<input type="hidden" name="colorsFromFilters" value="{$CHART_COLORS_FROM_FILTERS}">
	<input type="hidden" name="filterIds"
		value="{\App\Purifier::encodeHtml(App\Json::encode($CHART_MODEL->getFilterIds()))}">
	{if $CHART_MODEL->getType() === 'Table' && $CHART_DATA}
		{assign var=FIRST_ROW value=current($CHART_DATA)}
		{assign var=HEADERS value=array_keys($CHART_DATA)}
		{assign var=IS_SUMMARY value=$CHART_MODEL->getExtraData('summary')}
		{assign var=SUMMARY value=[]}
		<div style="margin: -5px; line-height: 1;">
			<div class="table-responsive">
				<table class="config-table table u-word-break-all">
					{if $CHART_MODEL->isDividedByField()}
						<thead>
							<th class="u-white-space-nowrap"></th>
							{foreach from=$HEADERS item=HEADER}
								{if $HEADER === 0}
									{assign "HEADER" ""}
								{/if}
								<th class="u-white-space-nowrap text-center p-1">
									<div class="mt-1">
										{$HEADER}
									</div>
								</th>
							{/foreach}
						</thead>
					{/if}
					<tbody>
						{assign var=GROUPS value=array_keys($FIRST_ROW)}
						{assign var=VALUE_TYPE value=$CHART_MODEL->getValueType()}
						{foreach from=$GROUPS item=GROUP_HEADER}
							<tr>
								<td class="u-white-space-nowrap pr-0">
									{$GROUP_HEADER}
								</td>
								{foreach from=$HEADERS item=HEADER}
									<td class="text-center noWrap listButtons narrow">
										{if $IS_SUMMARY} {$SUMMARY[$HEADER][] = $CHART_DATA.$HEADER.$GROUP_HEADER.$VALUE_TYPE} {/if}
										{assign var=VALUE value=$CHART_MODEL->convertToUserFormat($CHART_DATA.$HEADER.$GROUP_HEADER.$VALUE_TYPE)}
										{if !empty($CHART_DATA.$HEADER.$GROUP_HEADER.link)}
											<a href="{$CHART_DATA.$HEADER.$GROUP_HEADER.link}">{$VALUE}</a>
										{else}
											{$VALUE}
										{/if}
									</td>
								{/foreach}
							</tr>
						{/foreach}
						{if $IS_SUMMARY}
							<tr>
								<td class="u-white-space-nowrap pr-0 border-secondary">
									<b>{\App\Language::translate('LBL_SUMMARY')}</b>
								</td>
								{foreach from=$HEADERS item=HEADER}
									<td class="text-center noWrap listButtons narrow border-secondary">
										{assign var=HEADER_SUM value=array_sum($SUMMARY[$HEADER])}
										<b>{$CHART_MODEL->convertToUserFormat($HEADER_SUM)}</b>
									</td>
								{/foreach}
							</tr>
						{/if}
					</tbody>
				</table>
			</div>
		</div>
	{elseif !empty($CHART_DATA['show_chart']) }
		<input class="widgetData" name="data" type="hidden" value="{\App\Purifier::encodeHtml(\App\Json::encode($CHART_DATA))}" />
		<div class="widgetChartContainer chartcontent">
			<canvas></canvas>
		</div>
	{else}
		<span class="noDataMsg">
			{\App\Language::translate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
		</span>
	{/if}
{/strip}
