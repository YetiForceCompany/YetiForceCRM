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
	<div class="commentContainer recentComments">
		<div class="commentTitle">
			{if !$IS_READ_ONLY && $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
				<div class="addCommentBlock">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<span class="fas fa-comments"></span>
							</span>
						</div>
						<textarea name="commentcontent" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="commentcontent form-control" title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" ></textarea>
						<div class="input-group-append">
							<button class="btn btn-success detailViewSaveComment" type="button" data-mode="add">
								<span class="fa fa-plus"></span> 
							</button>
						</div>
					</div>
				</div>
			{/if}
		</div>
		<hr><br />
		<div class="commentsBody">
			{if !empty($PARENT_COMMENTS)}
				{include file=\App\Layout::getTemplatePath('Comments.tpl') PARENT_COMMENTS=$PARENT_COMMENTS CURRENT_COMMENT=$CURRENT_COMMENT}
			{else}
				{include file=\App\Layout::getTemplatePath('NoComments.tpl')}
			{/if}
		</div>
		{if !$IS_READ_ONLY && $PAGING_MODEL->isNextPageExists()}
			<div class="row">
				<div class="float-right">
					<a href="javascript:void(0)" class="moreRecentComments btn btn-sm btn-info marginTop5 marginRight15">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}..</a>
				</div>
			</div>
		{/if}
		{if !$IS_READ_ONLY}
			<div class="d-none basicAddCommentBlock my-2">
				<div class="row">
					<div class="col-md-12">
						<div class="input-group">
							<span class="input-group-prepend">
								<span class="input-group-text"><span class="fas fa-comments"></span></span>
							</span>
							<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
						</div>
						<button class="u-cursor-pointer closeCommentBlock mt-3 btn btn-warning float-right cancel" type="reset">
							<span class="visible-xs-inline-block fas fa-times"></span>
							<strong class="d-none d-sm-none d-md-block">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
						</button>
						<button class="btn btn-success saveComment mt-3 float-right" type="button" data-mode="add">
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
						<input type="text" name="reasonToEdit" title="{\App\Language::translate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level form-control" />
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 mb-2">
						<div class="input-group">
							<span class="input-group-prepend">
								<span class="input-group-text"><span class="fas fa-comments"></span></span>
							</span>
							<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" ></textarea>
						</div>
						<button class="u-cursor-pointer closeCommentBlock mt-3 btn btn-warning float-right cancel" type="reset">
							<span class="visible-xs-inline-block fas fa-times"></span>
							<strong class="d-none d-sm-none d-md-block">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
						</button>
						<button class="btn btn-success saveComment mt-3 float-right" type="button" data-mode="edit">
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
