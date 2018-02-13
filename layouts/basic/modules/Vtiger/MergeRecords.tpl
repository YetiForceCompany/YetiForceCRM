{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="padding1per">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div>
					<h3 class="panel-title">{\App\Language::translate('LBL_MERGE_RECORDS_IN', $MODULE)}: {\App\Language::translate($MODULE, $MODULE)}</h3>
				</div>
			</div>
			<div class="panel-body">
				<div class="alert  alert-info">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					{\App\Language::translate('LBL_MERGE_RECORDS_DESCRIPTION', $MODULE)}
				</div>
				<form class="form-horizontal contentsBackground" name="massMerge" method="post" action="index.php">
					<input type="hidden" name=module value="{$MODULE}" />
					<input type="hidden" name="action" value="ProcessDuplicates" />
					<input type="hidden" name="records" value={\App\Json::encode($RECORDS)} />

					<div>
						<table class='table table-bordered table-sm'>
							<thead class='listViewHeaders'>
							<th>
								{\App\Language::translate('LBL_FIELDS', $MODULE)}
							</th>
							{foreach item=RECORD from=$RECORDMODELS name=recordList}
								<th>
									{\App\Language::translate('LBL_RECORD')} #{$smarty.foreach.recordList.index+1} &nbsp;
									<input {if $smarty.foreach.recordList.index eq 0}checked{/if} type=radio value="{$RECORD->getId()}" name=primaryRecord style='bottom:1px;position:relative;'/>
								</th>
							{/foreach}
							</thead>
							{foreach item=FIELD from=$FIELDS}
								{if $FIELD->isEditable()}
									<tr>
										<td>
											{\App\Language::translate($FIELD->get('label'), $MODULE)}
										</td>
										{foreach item=RECORD from=$RECORDMODELS name=recordList}
											<td>
												<input {if $smarty.foreach.recordList.index eq 0}checked{/if} type=radio name="{$FIELD->getName()}" data-id="{$RECORD->getId()}" value="{\App\Purifier::encodeHtml($RECORD->get($FIELD->getName()))}" style='bottom:1px;position:relative;'/>
												&nbsp;&nbsp;{$RECORD->getDisplayValue($FIELD->getName())}
											</td>
										{/foreach}
									</tr>
								{/if}
							{/foreach}
						</table>
					</div>
					<div>
						<div class="float-right marginTB10">
							<button type=submit class='btn btn-success'>{\App\Language::translate('LBL_MERGE', $MODULE)}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
