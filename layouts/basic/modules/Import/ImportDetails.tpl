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
	<div class="popupEntriesDiv textAlignCenter">
		<h3>{\App\Language::translate($TYPE, $MODULE)}</h3>
	</div>
	<table class="table table-bordered listViewEntriesTable">
		<thead>
			<tr class="listViewHeaders">
				{assign var=LISTVIEW_HEADERS value=$IMPORT_RECORDS['headers']}
				{assign var=IMPORT_RESULT_DATA value=$IMPORT_RECORDS[$TYPE]}
				{foreach item=LISTVIEW_HEADER_NAME from=$LISTVIEW_HEADERS}
					<th>{\App\Language::translate($LISTVIEW_HEADER_NAME, $FOR_MODULE)}</th>
					{/foreach}
			</tr>
		</thead>
		{foreach item=RECORD from=$IMPORT_RESULT_DATA}
			<tr class="listViewEntries">
				{foreach key=FIELD_NAME item=LABEL from=$LISTVIEW_HEADERS}
					{if $RECORD->getField($FIELD_NAME)->isViewable()}
						<td>{$RECORD->getListViewDisplayValue($FIELD_NAME)}</td>
					{/if}
				{/foreach}
			</tr>
		{/foreach}
	</table>
{/strip}
