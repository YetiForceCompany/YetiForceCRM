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
	<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
	<div class="listViewEntriesDiv">
		<span class="listViewLoadingImageBlock hide modal" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}" />
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</span>
		<div class="feedContainer">
			{if $RECORD}
				<input id="recordId" type="hidden" value="{$RECORD->getId()}">
				<div class="row">
					<div class="col-md-8" id="rssFeedHeading">
						<h3> {\App\Language::translate('LBL_FEEDS_LIST_FROM',$MODULE)}: {$RECORD->getName()} </h3>
					</div>
					<div class="btn-toolbar col-md-4">
						<span class="btn-group pull-right">
							<button id="deleteButton" class="btn btn-danger" title="{\App\Language::translate('LBL_DELETE', $MODULE)}"><span class="fas fa-trash-alt"></span></button>
						</span>
						<span class="btn-group pull-right">
							<button id="makeDefaultButton" class="btn btn-info" title="{\App\Language::translate('LBL_SET_AS_DEFAULT', $MODULE)}">&nbsp;<strong>{\App\Language::translate('LBL_SET_AS_DEFAULT', $MODULE)}</strong></button>
						</span>
						<span class="btn-group pull-right">
							<button id="rssAddButton" class="rssAddButton btn btn-success" title="{\App\Language::translate('LBL_ADD_FEED_SOURCE', $MODULE)}"><span class="fas fa-plus"></span>&nbsp;<span class="userIcon-Rss"></span></button>
						</span>
						<span class="btn-group pull-right">
							<button id="changeFeedSource" class="changeFeedSource btn btn-primary" title="{\App\Language::translate('LBL_CHANGE_RSS_CHANNEL', $MODULE)}"><span class="fas fa-exchange-alt"></span>&nbsp;<span class="userIcon-Rss"></span></button>
						</span>
					</div>
				</div>
				<div class="feedListContainer pushDown" style="overflow: auto;"> 
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
