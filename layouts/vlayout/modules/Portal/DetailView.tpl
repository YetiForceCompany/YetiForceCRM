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
    <div class="listViewPageDiv">
        <span class="btn-toolbar span4">
            <span class="btn-group">
                <button id="addBookmark" class="btn addButton"><i class="icon-plus"></i>&nbsp;<strong>{vtranslate('LBL_ADD_BOOKMARK', $MODULE)}</strong></button>
            </span>
        </span>
        <span class="span2">&nbsp;</span>
        <span>
            <div class="pull-right">
                <div class="control-label span2">
                    <label class="textAlignRight" style="padding-top: 14px;">
                        {vtranslate('LBL_BOOKMARKS_LIST', $MODULE)}
                    </label>
                </div>
                <div class="controls span4" style="padding-top: 10px;">
                    <select class="select2-container select2 pull-right customFilterMainSpan" id="bookmarksDropdown" name="bookmarksList">
                        {foreach item=RECORD from=$RECORDS_LIST}
                            <option value="{$RECORD['id']}" {if $RECORD['id'] eq $RECORD_ID}selected{/if}>{$RECORD['portalname']}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </span>
        <span class="listViewLoadingImageBlock hide modal noprint" id="loadingListViewModal">
            <img class="listViewLoadingImage" src="{vimage_path('loading.gif')}" alt="no-image" title="{vtranslate('LBL_LOADING', $MODULE)}"/>
            <p class="listViewLoadingMsg">{vtranslate('LBL_LOADING_LISTVIEW_CONTENTS', $MODULE)}........</p>
        </span>
        <br>
        {if substr($URL, 0, 8) neq 'https://'}<div id="portalDetailViewHttpError" class="row-fluid"><div class="span12">{vtranslate('HTTP_ERROR', $MODULE)}</div></div>{/if}
        <br>
        <iframe src="{if substr($URL, 0, 4) neq 'http'}//{/if}{$URL}" frameborder="1" height="600" scrolling="auto" width="100%" style="border: solid 2px; border-color: #dddddd;"></iframe>
    </div>
{/strip}