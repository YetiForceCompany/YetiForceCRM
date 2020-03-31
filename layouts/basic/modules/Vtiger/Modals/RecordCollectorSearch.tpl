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
	{/if}
	{if $RECORD_COLLECTOR->displayType === 'LoadToForm'}
		{if isset($SEARCH_DATA['fields'])}
			<input type="hidden" class="formFieldsToRecordMap" value="{\App\Purifier::encodeHtml(App\Json::encode($SEARCH_DATA['formFieldsToRecordMap']))}">
			<table class="table table-bordered mt-2">
				<thead>
				<tr>
					<th>{\App\Language::translate('LBL_FIELDS', $MODULE_NAME)}</th>
						<th>{\App\Language::translate('LBL_DATA_FROM_SOURCE', $MODULE_NAME)}</th>
					{if isset($SEARCH_DATA['recordModel'])}
						<th>{\App\Language::translate('LBL_DATA_FROM_RECORD', $MODULE_NAME)}</th>
					{/if}
				</tr>
				</thead>
				<tbody>
				{foreach from=$SEARCH_DATA['fields'] key=FIELD_NAME item=VALUE}
					<tr>
						<td>{$FIELD_NAME}</td>
						<td class="value{$FIELD_NAME}">
								<input type="radio" name="{$FIELD_NAME}"checked value="true">&nbsp;
								<span class="fieldValue">{\App\Purifier::encodeHtml($VALUE)}</span>
							</td>
						{if isset($SEARCH_DATA['recordModel'])}
							<td class="value{$FIELD_NAME}">
								<input type="radio" name="{$FIELD_NAME}" value="false">&nbsp;
								<span class="fieldValue">{$SEARCH_DATA['recordModel']->get($SEARCH_DATA['formFieldsToRecordMap'][$FIELD_NAME])}</span>
							</td>
						{/if}
					</tr>
				{/foreach}
				</tbody>
			</table>
			<button class="btn btn-success float-right js-record-collector__fill_fields" data-js="click">
				<span class="fas fa-check mr-1"></span>
				{\App\Language::translate('LBL_COMPLETE_FIELDS', $MODULE_NAME)}</button>
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
