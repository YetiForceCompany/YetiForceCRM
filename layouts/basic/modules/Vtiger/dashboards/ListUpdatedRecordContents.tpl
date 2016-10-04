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
<div style='padding:5px;'>
	{if $LIST neq false}
		<table class="table table-bordered">
			<tr>
				{foreach from=$COLUMN_LIST item=col key=col_key}
					<th>{vtranslate($col_key, 'Vtiger')}</th>
					{/foreach}
			</tr>
			{foreach key=$index item=record from=$LIST}
				<tr>
					{foreach from=$record item=item key=key}
						<td>{$item}</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>
	{else}
		<span class="noDataMsg">
			{vtranslate('LBL_NO_MOD_RECORDS', $MODULE_NAME)}
		</span>
	{/if}
</div>
{/strip}
