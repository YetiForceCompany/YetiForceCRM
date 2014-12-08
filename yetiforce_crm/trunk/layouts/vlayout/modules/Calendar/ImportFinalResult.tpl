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
	<input type="hidden" name="module" value="{$MODULE}" />
	<table style=" width:90%;margin-left: 5% "   cellpadding="5" class="searchUIBasic well">
		<tr>
			<td class="font-x-large" align="left" colspan="2">
				<strong>{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$MODULE} - {'LBL_RESULT'|@vtranslate:$MODULE}</strong>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<table cellpadding="5" cellspacing="0" align="center" width="100%" class="dvtSelectedCell thickBorder importContents">
					<tr>
						<td>{'LBL_LAST_IMPORT_UNDONE'|@vtranslate:$MODULE}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="right" colspan="2">
				<button name="next" class="create btn"
				onclick="location.href='index.php?module={$MODULE}&view=List'" ><strong>{'LBL_FINISH'|@vtranslate:$MODULE}</strong></button>
			</td>
		</tr>
	</table>
{/strip}