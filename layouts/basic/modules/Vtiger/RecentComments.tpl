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
	{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
	{* Change to this also refer: AddCommentForm.tpl *}
	<div class="tpl-Base-RecentComments js-comments-container commentContainer recentComments" data-js="container">
		<div class="commentTitle">
			{if !$IS_READ_ONLY && $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
				<div class="js-add-comment-block addCommentBlock" data-js="container|remove">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<span class="fas fa-comments"></span>
							</span>
						</div>
						<textarea name="commentcontent" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"
								  class="js-comment-content commentcontent form-control"
								  title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
								  placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
								  data-js="val"></textarea>
						<div class="input-group-append">
							<button class="btn btn-success js-detail-view-save-comment" type="button" data-mode="add">
								<span class="fa fa-plus"></span>
							</button>
						</div>
					</div>
				</div>
			{/if}
		</div>
		<div class="col-md-12 form-row commentsHeader my-3 mx-0 px-0">
			<div class="col-9 col-lg-5 col-md-12 col-sm-6 p-0">
				<div class="input-group-append bg-white rounded-right">
					<input type="text" class="js-comment-search form-control"
						   placeholder="{\App\Language::translate('LBL_COMMENTS_SEARCH','ModComments')}"
						   aria-describedby="commentSearchAddon"
						   data-container="widget"
						   data-js="keypress|data">
					<button class="btn btn-outline-dark border-0 h-100 js-search-icon searchIcon" type="button"
							data-js="click">
						<span class="fas fa-search fa-fw" title="{\App\Language::translate('LBL_SEARCH')}"></span>
					</button>
				</div>
			</div>
			<div class="col-3 col-lg-7 col-md-12 col-sm-6 p-0 text-md-center text-lg-right m-md-2 m-lg-0">
				{if $LEVEL < 2}
					<div class="btn-group btn-group-toggle hierarchyButtons float-right float-md-none"
						 data-toggle="buttons">
						<label class="btn btn-sm btn-outline-primary {if $HIERARCHY_VALUE !== 'all'}active{/if}">
							<input class="js-hierarchyComments hierarchyComments" type="radio" name="options"
								   id="option1"
								   value="current" autocomplete="off"
								   {if $HIERARCHY_VALUE !== 'all'}checked="checked"{/if}
								   data-js="value"
							> {\App\Language::translate('LBL_COMMENTS_0', 'ModComments')}
						</label>
						<label class="btn btn-sm btn-outline-primary {if $HIERARCHY_VALUE === 'all'}active{/if}">
							<input class="js-hierarchyComments hierarchyComments" type="radio" name="options"
								   id="option2" value="all"
								   {if $HIERARCHY_VALUE === 'all'}checked="checked"{/if}
								   autocomplete="off"
								   data-js="value">
							{\App\Language::translate('LBL_ALL_RECORDS', 'ModComments')}
						</label>
					</div>
				{/if}
			</div>
		</div>
		<hr>
		<div class="js-comments-body commentsBody" data-js="html">
			{if !empty($PARENT_COMMENTS)}
				{include file=\App\Layout::getTemplatePath('Comments.tpl') PARENT_COMMENTS=$PARENT_COMMENTS CURRENT_COMMENT=$CURRENT_COMMENT}
			{else}
				{include file=\App\Layout::getTemplatePath('NoComments.tpl')}
			{/if}
			{if !$IS_READ_ONLY && $PAGING_MODEL->isNextPageExists()}
				<div class="col-12 float-right p-0 mb-2">
					<a href="javascript:void(0)"
					   class="js-moreRecentComments moreRecentComments btn btn-sm btn-info marginTop5 marginRight15"
					   data-js="click">
						{\App\Language::translate('LBL_MORE',$MODULE_NAME)}..
					</a>
				</div>
			{/if}
		</div>
		{if !$IS_READ_ONLY}
			<div class="d-none basicAddCommentBlock my-2">
				<div class="row">
					<div class="col-md-12">
						<div class="input-group">
							<span class="input-group-prepend">
								<span class="input-group-text"><span class="fas fa-comments"></span></span>
							</span>
							<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"
									  class="form-control commentcontenthidden fullWidthAlways js-comment-content"
									  name="commentcontent"
									  title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
									  placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
						</div>
						<button class="u-cursor-pointer js-close-comment-block mt-3 btn btn-warning float-right cancel"
								type="reset">
							<span class="visible-xs-inline-block fas fa-times"></span>
							<strong class="d-none d-sm-none d-md-block">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
						</button>
						<button class="btn btn-success js-saveComment saveComment mt-3 float-right" type="button"
								data-mode="add"
								data-js="click|data-mode">
							<span class="visible-xs-inline-block fas fa-check"></span>
							<strong class="d-none d-sm-none d-md-block">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</strong>
						</button>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="d-none basicEditCommentBlock">
				<div class="row">
					<div class="col-md-12 my-2">
						<input type="text" name="reasonToEdit"
							   title="{\App\Language::translate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}"
							   placeholder="{\App\Language::translate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}"
							   class="js-reason-to-edit input-block-level form-control"
							   data-js="value"
						>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 mb-2">
						<div class="input-group">
							<span class="input-group-prepend">
								<span class="input-group-text"><span class="fas fa-comments"></span></span>
							</span>
							<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"
									  class="form-control commentcontenthidden fullWidthAlways js-comment-content"
									  name="commentcontent"
									  title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
									  placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
						</div>
						<button class="u-cursor-pointer js-close-comment-block mt-3 btn btn-warning float-right cancel"
								type="reset">
							<span class="visible-xs-inline-block fas fa-times"></span>
							<strong class="d-none d-sm-none d-md-block">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
						</button>
						<button class="btn btn-success js-saveComment saveComment mt-3 float-right" type="button"
								data-mode="edit"
								data-js="click|data-mode">
							<span class="visible-xs-inline-block fas fa-check"></span>
							<strong class="d-none d-sm-none d-md-block">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</strong>
						</button>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		{/if}
	</div>
{/strip}
