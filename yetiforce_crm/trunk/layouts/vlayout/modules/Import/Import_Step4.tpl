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

<table width="100%" cellspacing="0" cellpadding="10" class="importContents">
	<tr>
		<td>
			<strong>{'LBL_IMPORT_STEP_4'|@vtranslate:$MODULE}:</strong>
		</td>
		<td>
			<span class="big">{'LBL_IMPORT_STEP_4_DESCRIPTION'|@vtranslate:$MODULE}</span>
		</td>
		<td>
            <div id="savedMapsContainer" class="textAlignRight pull-right">
				{include file="Import_Saved_Maps.tpl"|@vtemplate_path:'Import'}
			</div>
        </td>
	</tr>
	<tr>
		<td>&nbsp;</td>
        <td colspan="2">
			<input type="hidden" name="field_mapping" id="field_mapping" value="" />
			<input type="hidden" name="default_values" id="default_values" value="" />
			<table width="100%" cellspacing="0" cellpadding="2" class="listRow table table-bordered table-condensed listViewEntriesTable">
				<thead>
					<tr class="listViewHeaders">
						{if $HAS_HEADER eq true}
						<th width="25%"><a>{'LBL_FILE_COLUMN_HEADER'|@vtranslate:$MODULE}</a></th>
						{/if}
						<th width="25%"><a>{'LBL_ROW_1'|@vtranslate:$MODULE}</a></th>
						<th width="23%"><a>{'LBL_CRM_FIELDS'|@vtranslate:$MODULE}</a></th>
						<th width="27%"><a>{'LBL_DEFAULT_VALUE'|@vtranslate:$MODULE}</a></th>
					</tr>
				</thead>
				<tbody>
					{foreach key=_HEADER_NAME item=_FIELD_VALUE from=$ROW_1_DATA name="headerIterator"}
					{assign var="_COUNTER" value=$smarty.foreach.headerIterator.iteration}
					<tr class="fieldIdentifier" id="fieldIdentifier{$_COUNTER}">
						{if $HAS_HEADER eq true}
						<td class="cellLabel">
							<span name="header_name">{$_HEADER_NAME}</span>
						</td>
						{/if}
						<td class="cellLabel">
							<span>{$_FIELD_VALUE|@textlength_check}</span>
						</td>
						<td class="cellLabel">
							<input type="hidden" name="row_counter" value="{$_COUNTER}" />
							<select name="mapped_fields" class="txtBox chzn-select" style="width: 100%" onchange="ImportJs.loadDefaultValueWidget('fieldIdentifier{$_COUNTER}')">
								<option value="">{'LBL_NONE'|@vtranslate:$FOR_MODULE}</option>
								{foreach key=_FIELD_NAME item=_FIELD_INFO from=$AVAILABLE_FIELDS}
								{assign var="_TRANSLATED_FIELD_LABEL" value=$_FIELD_INFO->getFieldLabelKey()|@vtranslate:$FOR_MODULE}
								<option value="{$_FIELD_NAME}" {if decode_html($_HEADER_NAME) eq $_TRANSLATED_FIELD_LABEL} selected {/if} data-label="{$_TRANSLATED_FIELD_LABEL}">{$_TRANSLATED_FIELD_LABEL}{if $_FIELD_INFO->isMandatory() eq 'true'}&nbsp; (*){/if}</option>
								{/foreach}
							</select>
						</td>
						<td class="cellLabel row-fluid" name="default_value_container">&nbsp;</td>
					</tr>
					{/foreach}
			</tbody>
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
        <td align="right" colspan="2">
            <input type="checkbox" name="save_map" id="save_map"/>&nbsp;{'LBL_SAVE_AS_CUSTOM_MAPPING'|@vtranslate:$MODULE}&nbsp;&nbsp;
            <input type="text" name="save_map_as" id="save_map_as"/>
		</td>
	</tr>
</table>
{include file="Import_Default_Values_Widget.tpl"|@vtemplate_path:'Import'}
