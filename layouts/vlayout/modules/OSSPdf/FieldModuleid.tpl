{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
<select name="moduleid" title="{vtranslate('LBL_moduleid', 'OSSPdf')}" class="form-control" onchange="test();">
{foreach item=record from=$TABLIST}
	<option value="{$record.id}" {if $record.id eq $SELECTED_MODULE} SELECTED {/if}>{$record.label}</option>
{/foreach}
</select>
<input type="hidden" name="base_module" id="base_module" value="{$SMODULE}" />
