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
<div style='background: white;'>
	<div>
		<br>
		<div style='margin-left:10px'><h3>{vtranslate('LBL_MERGE_RECORDS_IN', $MODULE)} > {$MODULE}</h3></div><br>
		<div class='alert-info'>{vtranslate('LBL_MERGE_RECORDS_DESCRIPTION', $MODULE)}</div>
	</div>

	<form class="form-horizontal contentsBackground" name="massMerge" method="post" action="index.php">
		<input type="hidden" name=module value="{$MODULE}" />
		<input type="hidden" name="action" value="ProcessDuplicates" />
		<input type="hidden" name="records" value={Zend_Json::encode($RECORDS)} />

	<div>
		<table class='table table-bordered table-condensed'>
			<thead class='listViewHeaders'>
			<th>
				{vtranslate('LBL_FIELDS', $MODULE)}
			</th>
			{foreach item=RECORD from=$RECORDMODELS name=recordList}
				<th>
					{vtranslate('LBL_RECORD')} #{$smarty.foreach.recordList.index+1} &nbsp;
					<input {if $smarty.foreach.recordList.index eq 0}checked{/if} type=radio value="{$RECORD->getId()}" name=primaryRecord style='bottom:1px;position:relative;'/>
				</th>
			{/foreach}
			</thead>
			{foreach item=FIELD from=$FIELDS}
				{if $FIELD->isEditable()}
				<tr>
					<td>
						{vtranslate($FIELD->get('label'), $MODULE)}
					</td>
					{foreach item=RECORD from=$RECORDMODELS name=recordList}
						<td>
							<input {if $smarty.foreach.recordList.index eq 0}checked{/if} type=radio name="{$FIELD->getName()}"
							data-id="{$RECORD->getId()}" value="{$RECORD->get($FIELD->getName())}" style='bottom:1px;position:relative;'/>
							 &nbsp;&nbsp;{$RECORD->getDisplayValue($FIELD->getName())}
						</td>
					{/foreach}
				</tr>
				{/if}
			{/foreach}
		</table>
	</div>
	<div class='row-fluid'>
		<div class="offset4">
			<button type=submit class='btn btn-success'>{vtranslate('LBL_MERGE', $MODULE)}</button>
		</div>
	</div>
	</form>
	<br>
</div>
{/strip}