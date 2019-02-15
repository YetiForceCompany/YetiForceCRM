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
	<!-- tpl-Rss-RssFeedContents -->
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<table class="table table-bordered listViewEntriesTable">
		<thead>
		<tr class="listViewHeaders">
			{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
				<th nowrap {if $LISTVIEW_HEADER@last} colspan="2" {/if} class="{$WIDTHTYPE}">
					{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE)}
				</th>
			{/foreach}
		</tr>
		</thead>
		{foreach item=LISTVIEW_ENTRY from=$RECORD->getRssObject() name=listview}
			<tr class="listViewEntries" data-id='{$RECORD->getId()}'>
				{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
					{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
				<td class="listViewEntryValue {$WIDTHTYPE}" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}"
					nowrap>
					<a href="{\App\Purifier::encodeHtml((string)$LISTVIEW_ENTRY->link)}" target="_blank"
					   rel="noreferrer noopener">{\App\Purifier::encodeHtml((string)$LISTVIEW_ENTRY->$LISTVIEW_HEADERNAME)}</a>
					{if $LISTVIEW_HEADER@last}
						</td>
						<td nowrap class="{$WIDTHTYPE}">
							<span class="actions">
								<span class="actionImages float-right">
									<a href="{\App\Purifier::encodeHtml((string)$LISTVIEW_ENTRY->link)}"
									   target="_blank" rel="noreferrer noopener"><i
												title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}"
												class="fas fa-th-list alignMiddle"></i></a>&nbsp;
								</span>
							</span>
						</td>
					{/if}
					</td>
				{/foreach}
			</tr>
		{/foreach}
		<tr class="listViewEntrie {$WIDTHTYPE}" nowrap>
			<td class="listViewEntryValue">
				<a href="{$RECORD->get('url')}" target="_blank" rel="noreferrer noopener"
				   name="history_more">{\App\Language::translate('LBL_MORE')}...</a>
			</td>
			<td nowrap class="{$WIDTHTYPE}">
			</td>
			<td nowrap class="{$WIDTHTYPE}">
			</td>
		</tr>
	</table>
	<!-- /tpl-Rss-RssFeedContents -->
{/strip}
