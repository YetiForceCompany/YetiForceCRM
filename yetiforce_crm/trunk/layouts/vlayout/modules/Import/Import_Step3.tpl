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

<table width="100%" cellspacing="0" cellpadding="2">
	<tr> 
		<td width="8%">
			<input type="checkbox" class="font-x-small" id="auto_merge" name="auto_merge" onclick="ImportJs.toogleMergeConfiguration();" />
			<strong>{'LBL_IMPORT_STEP_3'|@vtranslate:$MODULE}:</strong>
		</td>
		<td>
			<span class="big">{'LBL_IMPORT_STEP_3_DESCRIPTION'|@vtranslate:$MODULE}</span>
			<span class="font-x-small">( {'LBL_IMPORT_STEP_3_DESCRIPTION_DETAILED'|@vtranslate:$MODULE} )</span>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<table width="100%" cellspacing="0" cellpadding="5" id="duplicates_merge_configuration" style="display:none;">
				<tr>
					<td>
						<span class="font-x-small">{'LBL_SPECIFY_MERGE_TYPE'|@vtranslate:$MODULE}</span>&nbsp;&nbsp;
						<select name="merge_type" id="merge_type" class="font-x-small">
							{foreach key=_MERGE_TYPE item=_MERGE_TYPE_LABEL from=$AUTO_MERGE_TYPES}
							<option value="{$_MERGE_TYPE}">{$_MERGE_TYPE_LABEL|@vtranslate:$MODULE}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<td class="font-x-small">{'LBL_SELECT_MERGE_FIELDS'|@vtranslate:$MODULE}</td>
				</tr>
				<tr>
					<td>
						<table class="calDayHour" cellpadding="5" cellspacing="0">
							<tr>
								<td><b>{'LBL_AVAILABLE_FIELDS'|@vtranslate:$MODULE}</b></td>
								<td></td>
								<td><b>{'LBL_SELECTED_FIELDS'|@vtranslate:$MODULE}</b></td>
							</tr>
							<tr>
								<td>
									<select id="available_fields" multiple size="10" name="available_fields" class="txtBox" style="width: 100%">
										{foreach key=_FIELD_NAME item=_FIELD_INFO from=$AVAILABLE_FIELDS}
										<option value="{$_FIELD_NAME}">{$_FIELD_INFO->getFieldLabelKey()|@vtranslate:$FOR_MODULE}</option>
										{/foreach}
									</select>
								</td>
								<td width="6%">
									<div align="center">
										<input type="button" name="Button" value="&nbsp;&rsaquo;&rsaquo;&nbsp;" onClick="ImportJs.copySelectedOptions('#available_fields', '#selected_merge_fields')" class="crmButton font-x-small importButton" /><br /><br />
										<input type="button" name="Button1" value="&nbsp;&lsaquo;&lsaquo;&nbsp;" onClick="ImportJs.removeSelectedOptions('#selected_merge_fields')" class="crmButton font-x-small importButton" /><br /><br />
									</div>
								</td>
								<td>
									<input type="hidden" id="merge_fields" size="10" name="merge_fields" value="" />
									<select id="selected_merge_fields" size="10" name="selected_merge_fields" multiple class="txtBox" style="width: 100%">
										{foreach key=_FIELD_NAME item=_FIELD_INFO from=$ENTITY_FIELDS}
										<option value="{$_FIELD_NAME}">{$_FIELD_INFO->getFieldLabelKey()|@vtranslate:$FOR_MODULE}</option>
										{/foreach}
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>