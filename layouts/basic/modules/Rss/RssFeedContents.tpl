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
	{if $RECORD->get('error')}
		<div class="alert alert-warning" role="alert">
			{\App\Purifier::encodeHtml($RECORD->get('error'))}
		</div>
	{/if}
	<table class="table table-bordered listViewEntriesTable">
		<thead>
			<tr class="listViewHeaders">
				<th nowrap class="{$WIDTHTYPE}">
					{\App\Language::translate('LBL_SUBJECT', $MODULE)}
				</th>
				<th nowrap class="{$WIDTHTYPE}">
					{\App\Language::translate('LBL_DATE', $MODULE)}
				</th>
				<th class="{$WIDTHTYPE}"></th>
			</tr>
		</thead>
		{foreach item=ITEM from=$RECORD->getRssItems() name=listview}
			<tr class="listViewEntries" data-id='{$RECORD->getId()}'>
				<td class="listViewEntryValue {$WIDTHTYPE}" nowrap>
					<a href="{\App\Purifier::encodeHtml($ITEM['link'])}" target="_blank" rel="noreferrer noopener">
						<strong title="{\App\Purifier::encodeHtml($ITEM['fullTitle'])}">{\App\Purifier::encodeHtml($ITEM['title'])}</strong>
					</a>
				</td>
				<td class="listViewEntryValue {$WIDTHTYPE}" nowrap>{$ITEM['date']}</td>
				<td nowrap class="{$WIDTHTYPE}">
					<span class="actions">
						<span class="actionImages float-right">
							<a href="{\App\Purifier::encodeHtml($ITEM['link'])}" target="_blank" rel="noreferrer noopener">
								<i title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="fas fa-th-list alignMiddle"></i>
							</a>
						</span>
					</span>
				</td>
			</tr>
		{/foreach}
	</table>
	<a href="{\App\Purifier::encodeHtml($RECORD->get('url'))}" target="_blank" rel="noreferrer noopener" name="history_more">{\App\Language::translate('LBL_MORE')}...</a>
	<!-- /tpl-Rss-RssFeedContents -->
{/strip}
