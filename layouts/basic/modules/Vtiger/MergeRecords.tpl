{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-MergeRecords -->
	<div class="modal-body">
		<form class="form-horizontal" name="massMerge" method="post" action="index.php">
			<div class="alert alert-info alert-dismissible fade show" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}">
					<span aria-hidden="true">&times;</span></button>
				{\App\Language::translate('LBL_MERGE_RECORDS_DESCRIPTION', $MODULE_NAME)}
			</div>
			{if $COUNT > count($RECORD_MODELS)}
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}">
						<span aria-hidden="true">&times;</span></button>
					{\App\Language::translateArgs('LBL_NUMBER_OF_MERGED_RECORDS_HAS_BEEN_REDUCED_TO', $MODULE_NAME,count($RECORD_MODELS))}
				</div>
			{/if}
			<input type="hidden" name=module value="{$MODULE_NAME}" />
			<input type="hidden" name="action" value="MergeRecords" />
			<input type="hidden" name="records" value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($RECORD_MODELS)))}" />
			<div class="table-responsive">
				<table class="table table-bordered table-condensed">
					<thead class="listViewHeaders">
						<th class="align-text-top">
							{\App\Language::translate('LBL_FIELDS', $MODULE)}
						</th>
						{foreach item=RECORD from=$RECORD_MODELS name=recordList}
							<th>
								<div class="form-check form-check-inline">
									<div>
										<input {if $smarty.foreach.recordList.first}checked{/if} type="radio" id="radio{$RECORD->getId()}" name="record" class="form-check-input" value="{$RECORD->getId()}" data-js="change">
									</div>
									<label class="ml-1 form-check-label u-word-break-keep-all" for="radio{$RECORD->getId()}">
										#{$smarty.foreach.recordList.index+1} {\App\TextUtils::textTruncate($RECORD->getName())}</label>
								</div>
							</th>
						{/foreach}
					</thead>
					{foreach item=FIELD_NAME from=$FIELDS}
						{assign var=ROW_COLOR value=0}
						{assign var=ROW_LAST_VALUE value=null}
						{foreach item=RECORD from=$RECORD_MODELS name=recordList}
							{if $smarty.foreach.recordList.first}
								{assign var=ROW_LAST_VALUE value=$RECORD->get($FIELD_NAME)}
							{elseif $ROW_LAST_VALUE !== '' && $RECORD->get($FIELD_NAME) !== '' && $ROW_LAST_VALUE !== $RECORD->get($FIELD_NAME)}
								{assign var=ROW_COLOR value=1}
							{/if}
						{/foreach}
						<tr>
							<td>
								{\App\Language::translate($RECORD->getField($FIELD_NAME)->get('label'), $RECORD->getModuleName())}
							</td>
							{foreach item=RECORD from=$RECORD_MODELS}
								<td {if $ROW_COLOR}class="table-info" {/if}>
									<div class="form-check form-check-inline">
										<div>
											<input {if $smarty.foreach.recordList.first}checked{/if} type="radio" id="radio{$FIELD_NAME}{$RECORD->getId()}" name="{$FIELD_NAME}" class="form-check-input" value="{$RECORD->getId()}">
										</div>
										<label class="ml-1 form-check-label" for="radio{$FIELD_NAME}{$RECORD->getId()}">{$RECORD->getListViewDisplayValue($FIELD_NAME)}</label>
									</div>
								</td>
							{/foreach}
						</tr>
					{/foreach}
				</table>
			</div>
		</form>
	</div>
	<!-- /tpl-Base-MergeRecords -->
{/strip}
