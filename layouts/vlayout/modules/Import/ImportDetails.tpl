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
<div class="popupEntriesDiv textAlignCenter">
	<h3>{vtranslate($TYPE, $MODULE)}</h3>
</div>
<table class="table table-bordered listViewEntriesTable">
	<thead>
		<tr class="listViewHeaders">
			{assign var=LISTVIEW_HEADERS value=$IMPORT_RECORDS['headers']}
			{assign var=IMPORT_RESULT_DATA value=$IMPORT_RECORDS[$TYPE]}
			{foreach item=LISTVIEW_HEADER_NAME from=$LISTVIEW_HEADERS}
				<th>{$LISTVIEW_HEADER_NAME}</th>
			{/foreach}
		</tr>
	</thead>
	{foreach item=RECORD from=$IMPORT_RESULT_DATA}
		<tr class="listViewEntries">
			{foreach item=LISTVIEW_HEADER_NAME from=$LISTVIEW_HEADERS}
				<td>
					{$RECORD->get($LISTVIEW_HEADER_NAME)}
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>
{/strip}