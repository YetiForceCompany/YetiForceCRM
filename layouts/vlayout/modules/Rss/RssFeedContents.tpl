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
{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
<table class="table table-bordered listViewEntriesTable">
    <thead>
        <tr class="listViewHeaders">
            {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
            <th nowrap {if $LISTVIEW_HEADER@last} colspan="2" {/if} class="{$WIDTHTYPE}">
                {vtranslate($LISTVIEW_HEADER->get('label'), $MODULE)}
            </th>
            {/foreach}
        </tr>
    </thead>
    {foreach item=LISTVIEW_ENTRY from=$RECORD->getRssObject() name=listview}
    <tr class="listViewEntries" data-id='{$RECORD->getId()}'>
        {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
            {assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
            <td class="listViewEntryValue {$WIDTHTYPE}" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}" nowrap>
                <a class='feedLink' href="javascript:;" data-url="{$LISTVIEW_ENTRY['link']}">{$LISTVIEW_ENTRY[$LISTVIEW_HEADERNAME]}</a>
            {if $LISTVIEW_HEADER@last}
            </td>
                <td nowrap class="{$WIDTHTYPE}">
                    <span class="actions">
                        <span class="actionImages pull-right">
                            <a href="{$LISTVIEW_ENTRY['link']}" target="_BLANK"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></i></a>&nbsp;
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
            <a href="{$RECORD->get('url')}" target="_BLANK" name="history_more">{vtranslate('LBL_MORE')}...</a>
        </td>
        <td nowrap class="{$WIDTHTYPE}">
        </td>
        <td nowrap class="{$WIDTHTYPE}">
        </td>
    </tr>
</table>
{/strip}