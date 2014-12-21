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
<div id="toggleButton" class="toggleButton" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
				<i id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-left{else}icon-chevron-right{/if}"></i>
			</div>&nbsp
<div style="padding-left: 15px;">
	<form action="index.php" enctype="multipart/form-data" method="POST" name="importAdvanced">
		<input type="hidden" name="module" value="{$FOR_MODULE}" />
		<input type="hidden" name="view" value="Import" />
		<input type="hidden" name="mode" value="import" />
		<input type="hidden" name="type" value="{$USER_INPUT->get('type')}" />
		<input type="hidden" name="has_header" value='{$HAS_HEADER}' />
		<input type="hidden" name="file_encoding" value='{$USER_INPUT->get('file_encoding')}' />
		<input type="hidden" name="delimiter" value='{$USER_INPUT->get('delimiter')}' />
		<input type="hidden" name="merge_type" value='{$USER_INPUT->get('merge_type')}' />
		<input type="hidden" name="merge_fields" value='{$MERGE_FIELDS}' />

		<input type="hidden" id="mandatory_fields" name="mandatory_fields" value='{$ENCODED_MANDATORY_FIELDS}' />

		<table style=" width:90%;margin-left: 5%" cellpadding="2" cellspacing="12" class="searchUIBasic">
			<tr>
				<td class="font-x-large" align="left" colspan="2">
					<strong>{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE}</strong>
				</td>
			</tr>
			{if $ERROR_MESSAGE neq ''}
			<tr>
				<td class="style1" align="left" colspan="2">
					{$ERROR_MESSAGE}
				</td>
			</tr>
			{/if}
			<tr>
				<td class="leftFormBorder1" colspan="2" valign="top">
				{include file='Import_Step4.tpl'|@vtemplate_path:'Import'}
				</td>
			</tr>
			<tr>
				<td align="right" colspan="2">
				{include file='Import_Advanced_Buttons.tpl'|@vtemplate_path:'Import'}
				</td>
			</tr>
		</table>
	</form>
{/strip}