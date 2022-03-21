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
	{assign var="totalCount" value=0}
	{assign var="totalModulesSearched" value=count($MATCHING_RECORDS)}
	{foreach key=module item=searchRecords from=$MATCHING_RECORDS}
		{assign var=modulesCount value=count($searchRecords)}
		{assign var="totalCount" value=$totalCount+$modulesCount}
	{/foreach}
	<div class="tpl-UnifiedSearchResults globalSearchResults modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header padding1per">
					<div class="form-row w-100 m-0">
						<div class="col-6 u-text-ellipsis">
							<strong><span
									class="fas fa-search fa-fw"></span> {\App\Language::translate('LBL_SEARCH_RESULTS',$MODULE)}
								&nbsp;({$totalCount})</strong>
						</div>
						<div class="col-6">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
						</div>
						{if $IS_ADVANCE_SEARCH }
							<span class="col-12">
								<span class="float-left">
									<a href="javascript:void(0);"
										id="showFilter">{\App\Language::translate('LBL_SAVE_MODIFY_FILTER',$MODULE)}</a>
								</span>
							</span>
						{/if}
					</div>
				</div>
				<div class="contents modal-body">
					{if $totalCount eq 100}
						<div class='alert alert-info fade in'>
							<button type=button class="close" data-dismiss="alert">&times;</button>
							{if $SEARCH_MODULE}
								{\App\Language::translate('LBL_GLOBAL_SEARCH_MAX_MESSAGE_FOR_MODULE', 'Vtiger')}
							{else}
								{\App\Language::translate('LBL_GLOBAL_SEARCH_MAX_MESSAGE', 'Vtiger')}
							{/if}
						</div>
					{/if}
					{foreach key=module item=searchRecords from=$MATCHING_RECORDS name=matchingRecords}
						{assign var="modulesCount" value=count($searchRecords)}
						<form method="POST" action="index.php?module={$module}&view=List" name="form_{$module}"
							enctype="multipart/form-data">
							<input type="hidden" id="recordList" name="searchResult"
								value="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($searchRecords)))}" />
							<div class="clearfix">
								<span onclick="form_{$module}.submit()"><span
										class="fas fa-list mr-1"></span> <strong>{\App\Language::translate($module,$module)}
										&nbsp;({$modulesCount})</strong></span>
								<!-- &nbsp;&nbsp;<i title="" class="fas fa-th-list alignMiddle"></i> -->
								{if {$smarty.foreach.matchingRecords.index+1} eq 1}
									<div class="float-right">
										<p
											class="muted">{\App\Language::translate('LBL_CREATED_ON', $MODULE)}</small></p>
									</div>
								{/if}
							</div>
							<ul class="nav d-flex justify-content-center">
								{foreach item=recordObject from=$searchRecords name=globalSearch}
									{assign var="ID" value="{$module}_globalSearch_row_{$smarty.foreach.globalSearch.index+1}"}
									{assign var=DETAILVIEW_URL value=$recordObject->getDetailViewUrl()}
									{if $recordObject->get('permitted')}
										<li id="{$ID}" class="col-12 form-row px-0">
											<a target="_blank" id="{$ID}_link"
												class="u-cursor-pointer col-12 form-row py-1" {if stripos($DETAILVIEW_URL, 'javascript:')===0}
												onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href = "{$DETAILVIEW_URL}"' 
												{/if}>
												<span class="col-8 text-left u-text-ellipsis">{$recordObject->getName()} {if $recordObject->get('assigned_user_id')}({$recordObject->getDisplayValue('assigned_user_id',$ID,true)}){/if}</span>
												<span id="{$ID}_time"
													class="col-4 text-right px-0 u-text-ellipsis">{\App\Fields\DateTime::formatToViewDate($recordObject->get('createdtime'))}</span>
											</a>
										</li>
									{else}
										<li id="{$ID}">
											<a class="cursorDefault">
												<span>{$recordObject->getName()} {if $recordObject->get('assigned_user_id')}({$recordObject->getDisplayValue('assigned_user_id',$ID,true)}){/if}</span>&nbsp;
												<span class="fas fa-exclamation-circle"></span>
												<span id="{$ID}_time"
													class="float-right">{\App\Fields\DateTime::formatToViewDate($recordObject->get('createdtime'))}</span>
											</a>
										</li>
									{/if}
								{foreachelse}
									<li>{\App\Language::translate('LBL_NO_RECORDS', $module)}</li>
								{/foreach}
							</ul>
						</form>
					{/foreach}
				</div>
			</div>
		</div>
	</div>
{/strip}
