{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Modals-RecordCollectorSummary -->
{if $RECORD_COLLECTOR->displayType === 'Summary'}
	<div class="mt-2">
		{if isset($SEARCH_DATA['fields'])}
			<table class="table">
			<tbody>
				{foreach item=VALUE key=LABEL from=$SEARCH_DATA['fields']}
					<tr>
						<th scope="row">{\App\Language::translate($LABEL, $MODULE_NAME)}</th>
						<td>{nl2br($VALUE)}</td>
					</tr>
				{/foreach}
			</tbody>
			</table>
		{elseif isset($SEARCH_DATA['error'])}
			<div class="alert alert-danger m-4" role="alert">
				<span class="mdi mdi-alert-circle-outline mr-2"></span>{$SEARCH_DATA['error']}
			</div>
		{else}
			<div class="alert alert-warning m-4" role="alert">
				<span class="mdi mdi-help-circle-outline mr-2"></span>{\App\Language::translate('LBL_NO_DATA_FOUND')}
			</div>
		{/if}
	</div>
{elseif $RECORD_COLLECTOR->displayType === 'FillFields'}
	{if !empty($SEARCH_DATA['fields'])}
		<form class="js-record-collector__fill_form" data-js="form">
			<table class="table table-bordered mt-2">
				<thead>
					<tr>
						<th class="text-center">{\App\Language::translate('LBL_FIELDS_LIST', $MODULE_NAME)}</th>
						{if empty($SEARCH_DATA['recordModel'])}
							<th class="text-center">
								{\App\Language::translate('LBL_NONE', $MODULE_NAME)}
								<span class="far fa-check-square u-cursor-pointer ml-2 js-record-collector__select" data-column="none" data-js="data|click"></span>
							</th>
						{/if}
						{foreach from=$SEARCH_DATA['keys'] item=KEY}
							<th class="text-center">
								{\App\Language::translate('LBL_DATA_FROM_SOURCE', $MODULE_NAME)}
								<span class="far fa-check-square u-cursor-pointer ml-2 js-record-collector__select" data-column="{$KEY}" data-js="data|click"></span>
							</th>
						{/foreach}
						{if isset($SEARCH_DATA['recordModel'])}
							<th class="text-center">
								{\App\Language::translate('LBL_DATA_FROM_RECORD', $MODULE_NAME)}
								<span class="far fa-check-square u-cursor-pointer ml-2 js-record-collector__select" data-column="record" data-js="data|click"></span>
							</th>
						{/if}
					</tr>
				</thead>
				<tbody>
				{foreach from=$SEARCH_DATA['fields'] key=FIELD_NAME item=ROW}
					<tr class="js-record-collector__field" data-field-name="{$FIELD_NAME}" data-js="data">
						<td>{$ROW['label']}</td>
						{if empty($SEARCH_DATA['recordModel'])}
							<td class="text-center js-record-collector__column" data-column="none">
								<input type="radio" name="{$FIELD_NAME}" value="">
							</td>
						{/if}
						{foreach from=$ROW['data'] key=KEY item=VALUE name=DATA_COLUMN}
							<td class="js-record-collector__column" data-column="{$KEY}">
								<input type="radio" name="{$FIELD_NAME}" {if $smarty.foreach.DATA_COLUMN.first}checked{/if} value="{$VALUE['raw']}">
								<span class="ml-2">{$VALUE['display']}</span>
							</td>
						{/foreach}
						{if isset($SEARCH_DATA['recordModel'])}
							{assign	var=FIELD_MODEL	value=$SEARCH_DATA['recordModel']->getField($FIELD_NAME)}
							<td class="js-record-collector__column" data-column="record">
								<input type="radio" name="{$FIELD_NAME}" value="{$FIELD_MODEL->getEditViewDisplayValue($SEARCH_DATA['recordModel']->get($FIELD_NAME),$SEARCH_DATA['recordModel'])}">
								<span class="ml-2">{$SEARCH_DATA['recordModel']->getDisplayValue($FIELD_NAME)}</span>
							</td>
						{/if}
					</tr>
				{/foreach}
				</tbody>
			</table>
		</form>
		{if !empty($SEARCH_DATA['additional'])}
			<table class="table table-bordered mt-2">
				<thead>
					<tr>
						<th class="text-center" colspan="{count($SEARCH_DATA['additional'])+1}">{\App\Language::translate('LBL_CUSTOM_INFORMATION', $MODULE_NAME)}</th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$SEARCH_DATA['additional'] key=NAME item=VALUES}
					<tr>
						<td>{\App\Language::translate($NAME, $MODULE_NAME)}</td>
						{foreach from=$VALUES item=VALUE}
							<td>{$VALUE}</td>
						{/foreach}
					</tr>
				{/foreach}
				</tbody>
			</table>
		{/if}
		{if !empty($SEARCH_DATA['skip'])}
			<table class="table table-bordered mt-2">
				<thead>
					<tr>
						<th class="text-center">{\App\Language::translate('LBL_FIELDS_OMITTED', $MODULE_NAME)}</th>
						{foreach from=$SEARCH_DATA['keys'] item=KEY}
							<th class="text-center">
								{\App\Language::translate('LBL_DATA_FROM_SOURCE', $MODULE_NAME)}
							</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
				{foreach from=$SEARCH_DATA['skip'] key=FIELD_NAME item=ROW}
					<tr>
						<td>{$ROW['label']}</td>
						{foreach from=$ROW['data'] item=VALUE}
							<td>{$VALUE['display']}</td>
						{/foreach}
					</tr>
				{/foreach}
				</tbody>
			</table>
		{/if}
		<button class="btn btn-success float-right js-record-collector__fill_fields" data-js="click">
			<span class="fas fa-check mr-2"></span>
			{\App\Language::translate('LBL_COMPLETE_FIELDS', $MODULE_NAME)}
		</button>
	{elseif isset($SEARCH_DATA['error'])}
		<div class="alert alert-danger m-4" role="alert">
			<span class="mdi mdi-alert-circle-outline mr-2"></span>{$SEARCH_DATA['error']}
		</div>
	{else}
		<div class="alert alert-warning m-4" role="alert">
			<span class="mdi mdi-help-circle-outline mr-2"></span>{\App\Language::translate('LBL_NO_DATA_FOUND')}
		</div>
	{/if}
{/if}
<!-- /tpl-Base-Modals-RecordCollectorSummary -->
{/strip}
