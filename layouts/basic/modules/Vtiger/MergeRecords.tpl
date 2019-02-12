{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-body tpl-MergeRecords">
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
			<input type="hidden" name=module value="{$MODULE_NAME}"/>
			<input type="hidden" name="action" value="MergeRecords"/>
			<input type="hidden" name="records" value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($RECORD_MODELS)))}"/>
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
									#{$smarty.foreach.recordList.index+1} {\App\TextParser::textTruncate($RECORD->getName())}</label>
							</div>
						</th>
					{/foreach}
					</thead>
					{foreach item=FIELD_NAME from=$FIELDS}
						<tr>
							{foreach item=RECORD from=$RECORD_MODELS name=recordList}
								{if $smarty.foreach.recordList.first}
									<td>
										{\App\Language::translate($RECORD->getField($FIELD_NAME)->get('label'), $RECORD->getModuleName())}
									</td>
								{/if}
								<td>
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
{/strip}
