{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	{if $LIST neq false}
		<table class="table table-bordered table-sm">
			<tr>
				{foreach from=$COLUMN_LIST item=col key=col_key}
					<th>
						{\App\Language::translate($col_key, 'Vtiger')}
					</th>
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
			{\App\Language::translate('LBL_NO_MOD_RECORDS', $MODULE_NAME)}
		</span>
	{/if}
{/strip}
