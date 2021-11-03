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
	<!-- tpl-ShowAllComments -->
	{* Change to this also refer: RecentComments.tpl *}
	{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
	<div class="js-completions__container" data-js="container">
		<div class="d-flex flex-wrap">
			{if !$IS_READ_ONLY && $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
				<div class="js-add-comment-block addCommentBlock mb-2 {if $HIERARCHY !== false && $HIERARCHY < 2}col-xl-8 col-lg-6{else}col-xl-10 col-lg-9{/if} col-12 pl-0 pr-lg-1 pr-0" data-js="container">
					<div class="input-group">
						<span class="input-group-prepend">
							<div class="input-group-text"><span class="fas fa-comments"></span></div>
						</span>
						<div name="commentcontent" contenteditable="true"
							class="js-comment-content commentcontent form-control js-chat-message js-completions"
							title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
							placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
							data-js="html | tribute.js"></div>
						<div class="input-group-append">
							<button class="btn btn-success js-save-comment" type="button"
								data-mode="add"
								data-js="click|data-mode">
								<span class="visible-xs-inline fas fa-plus"></span>
								<span class="d-none d-md-inline ml-1">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</span>
							</button>
						</div>
					</div>
				</div>
			{/if}
			<div class="{if $HIERARCHY !== false && $HIERARCHY < 2}col-xl-4 col-lg-6{else}col-xl-2 col-lg-3{/if} col-12 px-0 d-flex">
				{if $HIERARCHY !== false && $HIERARCHY < 2}
					<div class="px-0">
						<div class="btn-group btn-group-toggle detailCommentsHierarchy" data-toggle="buttons">
							<label class="js-detail-hierarchy-comments-btn u-text-ellipsis c-btn-block-sm-down btn btn-outline-primary {if in_array('current', $HIERARCHY_VALUE)}active{/if}"
								title="{\App\Language::translate('LBL_COMMENTS_0', 'ModComments')}" data-js="click">
								<input name="options" type="checkbox"
									class="js-detail-hierarchy-comments"
									data-js="val"
									value="current"
									{if in_array('current', $HIERARCHY_VALUE)} checked="checked" {/if}
									autocomplete="off" />
								{\App\Language::translate('LBL_COMMENTS_0', 'ModComments')}
							</label>
							<label class="js-detail-hierarchy-comments-btn u-text-ellipsis c-btn-block-sm-down btn btn-outline-primary {if in_array('related', $HIERARCHY_VALUE)}active{/if}"
								title="{\App\Language::translate('LBL_ALL_RECORDS', 'ModComments')}" data-js="click">
								<input name="options" type="checkbox"
									class="js-detail-hierarchy-comments"
									data-js="val"
									value="related"
									{if in_array('related', $HIERARCHY_VALUE)} checked="checked" {/if}
									autocomplete="off" />
								{\App\Language::translate('LBL_ALL_RECORDS', 'ModComments')}
							</label>
						</div>
					</div>
				{/if}
				<input type="hidden" id="currentComment" value="{if !empty($CURRENT_COMMENT)}{$CURRENT_COMMENT->getId()}{/if}">
				<div class="col mb-sm-0  pr-0  pl-1">
					<div class="input-group">
						<input type="text" class="js-comment-search form-control"
							placeholder="{\App\Language::translate('LBL_COMMENTS_SEARCH','ModComments')}"
							aria-describedby="commentSearchAddon"
							data-js="keypress|data" />
						<div class="input-group-append">
							<button class="btn btn-light js-search-icon" type="button" data-js="click">
								<span class="fas fa-search" title="{\App\Language::translate('LBL_SEARCH')}"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="commentContainer">
			<div class="js-completions__messages commentsList col-md-12 px-0" data-js="click">
				{include file=\App\Layout::getTemplatePath('CommentsList.tpl') COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL}
			</div>
		</div>
	</div>
	<!-- /tpl-ShowAllComments -->
{/strip}
