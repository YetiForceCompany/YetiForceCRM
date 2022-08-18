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
	<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
	<div class="listViewEntriesDiv u-overflow-scroll-non-desktop">
		<span class="listViewLoadingImageBlock d-none modal" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}" />
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</span>
		<div class="feedContainer">
			{if $RECORD}
				<input id="recordId" type="hidden" value="{$RECORD->getId()}">
				<div class="d-flex justify-content-between flex-wrap">
					<div id="rssFeedHeading">
						<h3> {\App\Language::translate('LBL_FEEDS_LIST_FROM',$MODULE)}: {\App\Purifier::encodeHtml($RECORD->getName())} </h3>
					</div>
					<div class="btn-toolbar btn-group flex-column flex-sm-row u-w-sm-down-100">
						<button id="changeFeedSource" class="changeFeedSource btn btn-primary c-btn-block-sm-down" title="{\App\Language::translate('LBL_CHANGE_RSS_CHANNEL', $MODULE)}"><span class="fas fa-exchange-alt mr-1"></span><span class="yfm-Rss"></span></button>
						<button id="rssAddButton" class="rssAddButton btn btn-success c-btn-block-sm-down" title="{\App\Language::translate('LBL_ADD_FEED_SOURCE', $MODULE)}"><span class="fas fa-plus mr-1"></span><span class="yfm-Rss"></span></button>
						<button id="makeDefaultButton" class="btn btn-info c-btn-block-sm-down" title="{\App\Language::translate('LBL_SET_AS_DEFAULT', $MODULE)}">{\App\Language::translate('LBL_SET_AS_DEFAULT', $MODULE)}</button>
						<button id="deleteButton" class="btn btn-danger c-btn-block-sm-down" title="{\App\Language::translate('LBL_DELETE', $MODULE)}"><span class="fas fa-trash-alt"></span></button>
					</div>
				</div>
				<div class="feedListContainer pushDown">
					{include file=\App\Layout::getTemplatePath('RssFeedContents.tpl', $MODULE)}
				</div>
			{else}
				<table class="emptyRecordsDiv">
					<tbody>
						<tr>
							<td>
								{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
								<button class="rssAddButton btn btn-link tdUnderline">{\App\Language::translate('LBL_RECORDS_NO_FOUND')}. {\App\Language::translate('LBL_CREATE')} {\App\Language::translate($SINGLE_MODULE, $MODULE)}</button>
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
