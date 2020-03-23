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
	<!-- /tpl-Base-Modals-RecordCollectorSummary -->
{/strip}
