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
<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
<div class="listViewEntriesDiv">
	<span class="listViewLoadingImageBlock hide modal" id="loadingListViewModal">
		<img class="listViewLoadingImage" src="{vimage_path('loading.gif')}" alt="no-image" title="{vtranslate('LBL_LOADING', $MODULE)}"/>
		<p class="listViewLoadingMsg">{vtranslate('LBL_LOADING_LISTVIEW_CONTENTS', $MODULE)}........</p>
	</span>
    <div class="feedContainer">
        {if $RECORD}
            <input id="recordId" type="hidden" value="{$RECORD->getId()}">
            <div class="row-fluid">
                <span class="btn-toolbar pull-right">
                    <span class="btn-group">
                        <button id="deleteButton" class="btn">&nbsp;<strong>{vtranslate('LBL_DELETE', $MODULE)}</strong></button>
                    </span>
                    <span class="btn-group">
                        <button id="makeDefaultButton" class="btn">&nbsp;<strong>{vtranslate('LBL_SET_AS_DEFAULT', $MODULE)}</strong></button>
                    </span>
                </span>
                <span class="row-fluid" id="rssFeedHeading">
                    <h3> {vtranslate('LBL_FEEDS_LIST_FROM',$MODULE)} : {$RECORD->getName()} </h3>
                </span>
            </div>
            <div class="feedListContainer" style="overflow: auto;"> 
                {include file='RssFeedContents.tpl'|@vtemplate_path:$MODULE}
            </div>
            {else}
                <table class="emptyRecordsDiv">
                <tbody>
                    <tr>
                        <td>
                            {assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
                            {vtranslate('LBL_NO')} {vtranslate($MODULE, $MODULE)} {vtranslate('LBL_FOUND')}. {vtranslate('LBL_CREATE')} <a class="rssAddButton" href="#" data-href="{$QUICK_LINKS['SIDEBARLINK'][0]->getUrl()}">&nbsp;{vtranslate($SINGLE_MODULE, $MODULE)}</a>
                        </td>
                    </tr>
                </tbody>
                </table>
        {/if}
    </div>
</div>
<br />
<div class="feedFrame">
</div>
{/strip}