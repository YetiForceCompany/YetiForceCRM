{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" name="typeChart" value="{$CHART_TYPE}">
	<input type="hidden" name="stacked" value="{$CHART_STACKED}">
	<input type="hidden" name="colorsFromDividingField" value="{$CHART_COLORS_FROM_DIVIDING_FIELD}">
	<input type="hidden" name="colorsFromFilters" value="{$CHART_COLORS_FROM_FILTERS}">
	<input type="hidden" name="filterIds" value="{\App\Purifier::encodeHtml(App\Json::encode($FILTERS))}">
	{if $CHART_OWNERS}
		<input class="widgetOwners" type="hidden" value="{\App\Purifier::encodeHtml(\App\Json::encode($CHART_OWNERS))}" />
	{/if}
	{foreach from=$ADDITIONAL_FILTERS_FIELDS item=FIELD}
		{assign var=VALUE value=$ADDITIONAL_FILTER_FIELD_VALUE[$FIELD->getName()]}
		{if !is_array($VALUE)}
			<input class="js-chartFilter__additional-filter-field" name="additional_filter_field[{$FIELD->getName()}]" type="hidden" value="{$VALUE}" data-js="container">
		{else}
			{foreach from=$VALUE item=FIELD_VAL}
				<input class="js-chartFilter__additional-filter-field"
					   name="additional_filter_field[{$FIELD->getName()}][]" type="hidden"
					   value="{$FIELD_VAL}" data-js="container">
			{/foreach}
		{/if}
	{/foreach}
	<input class="widgetData" name="data" type="hidden" value="{\App\Purifier::encodeHtml(\App\Json::encode($CHART_DATA))}" />
	{if $CHART_DATA['show_chart'] }
		<div class="widgetChartContainer chartcontent"><canvas></canvas></div>
	{else}
		<span class="noDataMsg">
			{\App\Language::translate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
		</span>
	{/if}
{/strip}
