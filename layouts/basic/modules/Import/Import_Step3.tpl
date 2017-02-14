{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
{strip}
<div class="col-xs-12 paddingLRZero">
	<div class='col-xs-2 paddingLRZero'>
		<strong>{'LBL_IMPORT_STEP_3'|@vtranslate:$MODULE}:</strong>&nbsp;&nbsp;&nbsp;
		<input type="checkbox" class="font-x-small" id="auto_merge" title="{vtranslate('LBL_IMPORT_STEP_3', $MODULE)}" name="auto_merge" onclick="ImportJs.toogleMergeConfiguration();" />
	</div>
	<div class="col-xs-10">
		<span>{'LBL_IMPORT_STEP_3_DESCRIPTION'|@vtranslate:$MODULE}</span>
		<span class="font-x-small">({'LBL_IMPORT_STEP_3_DESCRIPTION_DETAILED'|@vtranslate:$MODULE}).</span>
	</div>
	<div class="col-xs-12">
			<div class='row' id="duplicates_merge_configuration" style="display:none;">
				<div class='col-xs-12 paddingBottom10'>
					<div>
						<div class="col-md-6 paddingLRZero">
							<span class="font-x-small">{'LBL_SPECIFY_MERGE_TYPE'|@vtranslate:$MODULE}</span>&nbsp;&nbsp;
						</div>
						<div class="col-md-6 paddingLRZero">
							<select name="merge_type" id="merge_type" class="font-x-small form-control" title="{vtranslate('LBL_SPECIFY_MERGE_TYPE', $MODULE)}">
								{foreach key=_MERGE_TYPE item=_MERGE_TYPE_LABEL from=$AUTO_MERGE_TYPES}
								<option value="{$_MERGE_TYPE}">{$_MERGE_TYPE_LABEL|@vtranslate:$MODULE}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
				<div class='col-xs-12'>
					<div class="font-x-small">{'LBL_SELECT_MERGE_FIELDS'|@vtranslate:$MODULE}</div>
				</div>
				<div class='col-xs-12'>
						<div class="row calDayHour">
							<div class='col-xs-12 '>
								<div><strong>{'LBL_AVAILABLE_FIELDS'|@vtranslate:$MODULE}</strong></div>
								<div><strong>{'LBL_SELECTED_FIELDS'|@vtranslate:$MODULE}</strong></div>
							</div>
							<div class='col-xs-12 row'>
								<div class='col-xs-5'>
									<select id="available_fields" multiple size="10" name="available_fields" title="{vtranslate('LBL_AVAILABLE_FIELDS', $MODULE)}'" class="txtBox" style="width: 100%">
										{foreach key=BLOCK_NAME item=_FIELDS from=$AVAILABLE_BLOCKS}
											<optgroup label="{vtranslate($BLOCK_NAME, $FOR_MODULE)}">
												{foreach key=_FIELD_NAME item=_FIELD_INFO from=$_FIELDS}
													<option value="{$_FIELD_NAME}">{$_FIELD_INFO->getFieldLabel()|@vtranslate:$FOR_MODULE}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
								</div>
								<div class='col-xs-1'>
									<div align="center">
										<input type="button" name="Button" value="&nbsp;&rsaquo;&rsaquo;&nbsp;" onClick="ImportJs.copySelectedOptions('#available_fields', '#selected_merge_fields')" class="crmButton font-x-small importButton" /><br /><br />
										<input type="button" name="Button1" value="&nbsp;&lsaquo;&lsaquo;&nbsp;" onClick="ImportJs.removeSelectedOptions('#selected_merge_fields')" class="crmButton font-x-small importButton" /><br /><br />
									</div>
								</div>
								<div class='col-xs-5'>
									<input type="hidden" id="merge_fields" size="10" name="merge_fields" value="" />
									<select id="selected_merge_fields" size="10" name="selected_merge_fields" title="{vtranslate('lBL_SELECTED_FIELDS', $MODULE)}" multiple class="txtBox" style="width: 100%">
										{foreach item=FIELD_NAME from=$FOR_MODULE_MODEL->getNameFields()}
											{assign var="FIELD" value=$FOR_MODULE_MODEL->getFieldByName($FIELD_NAME)}
											<option value="{$FIELD_NAME}">{$FIELD->getFieldLabel()|@vtranslate:$FOR_MODULE}</option>
										{/foreach}
									</select>
								</div>
							</div>
						</div>
				</div>
			</div>
	</div>
</div>
{/strip}
