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
	{* Change to this also refer: AddCommentForm.tpl *}
	<div class="tpl-Base-RecentComments js-comments-container js-completions__container commentContainer recentComments" data-js="container">
		<div class="commentTitle">
			{if !$IS_READ_ONLY && $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
				<div class="js-add-comment-block addCommentBlock" data-js="container|remove">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<span class="fas fa-comments"></span>
							</span>
						</div>
						<div name="commentcontent" contenteditable="true"
							 class="js-comment-content js-completions commentcontent form-control"
							 title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
							 placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
							 data-js="html | tribute.js"></div>
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
				<div class="input-group">
					<input type="text" class="js-comment-search form-control"
						   placeholder="{\App\Language::translate('LBL_COMMENTS_SEARCH','ModComments')}"
						   aria-describedby="commentSearchAddon"
						   data-container="widget"
						   data-js="keypress|data">
					<div class="input-group-append">
						<button class="btn btn-light js-search-icon" type="button"
								data-js="click">
							<span class="fas fa-search fa-fw" title="{\App\Language::translate('LBL_SEARCH')}"></span>
						</button>
					</div>
				</div>
			</div>
			<div class="col-3 col-lg-7 col-md-12 col-sm-6 p-0 text-md-center text-lg-right m-md-2 m-lg-0">
				{if $HIERARCHY !== false && $HIERARCHY < 2}
					<div class="btn-group btn-group-toggle float-right float-md-none"
						 data-toggle="buttons">
						<label class="js-hierarchy-comments-btn mt-1 mt-sm-0 btn btn-outline-primary {if in_array('current', $HIERARCHY_VALUE)}active{/if}"
							   title="{\App\Language::translate('LBL_COMMENTS_0', 'ModComments')}" data-js="click">
							<input type="checkbox"
								   class="js-hierarchy-comments"
								   data-js="val"
								   value="current"
									{if in_array('current', $HIERARCHY_VALUE)} checked="checked"{/if}
								   autocomplete="off"/>
							{\App\Language::translate('LBL_COMMENTS_0', 'ModComments')}
						</label>
						<label class="js-hierarchy-comments-btn mt-1 mt-sm-0 btn btn-outline-primary {if in_array('related', $HIERARCHY_VALUE)}active{/if}"
							   title="{\App\Language::translate('LBL_ALL_RECORDS', 'ModComments')}" data-js="click">
							<input type="checkbox"
								   class="js-hierarchy-comments"
								   data-js="val"
								   value="related"
									{if in_array('related', $HIERARCHY_VALUE)} checked="checked"{/if}
								   autocomplete="off"/>
							{\App\Language::translate('LBL_ALL_RECORDS', 'ModComments')}
						</label>
					</div>
				{/if}
			</div>
		</div>
		<hr>
		<div class="js-comments-body js-completions__messages commentsBody" data-js="html | click">
			{if !empty($PARENT_COMMENTS)}
				{include file=\App\Layout::getTemplatePath('Comments.tpl') PARENT_COMMENTS=$PARENT_COMMENTS CURRENT_COMMENT=$CURRENT_COMMENT}
			{else}
				{include file=\App\Layout::getTemplatePath('NoComments.tpl')}
			{/if}
			{if !$IS_READ_ONLY && $PAGING_MODEL->isNextPageExists()}
				<div class="col-12 float-right p-0 mb-2">
					<a href="javascript:void(0)"
					   class="js-more-recent-comments btn btn-sm btn-info marginTop5 marginRight15"
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
							<div contenteditable="true"
								 class="form-control commentcontenthidden fullWidthAlways js-comment-content js-completions"
								 name="commentcontent"
								 title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
								 placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" data-js="html | tribute.js"></div>
						</div>
						<button class="u-cursor-pointer js-close-comment-block mt-3 btn btn-warning float-right ml-1 cancel"
								type="reset">
							<span class="visible-xs-inline fas fa-times"></span>
							<span class="d-none d-sm-none d-md-inline ml-1">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</span>
						</button>
						<button class="btn btn-success js-save-comment mt-3 float-right" type="button"
								data-mode="add"
								data-js="click|data-mode">
							<span class="visible-xs-inline fas fa-check"></span>
							<span class="d-none d-sm-none d-md-inline ml-1">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</span>
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
							<div contenteditable="true"
								 class="form-control commentcontenthidden fullWidthAlways js-comment-content js-completions"
								 name="commentcontent"
								 title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"
								 placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" data-js="html | tribute.js"></div>
						</div>
						<button class="u-cursor-pointer js-close-comment-block mt-3 btn btn-warning float-right ml-1 cancel"
								type="reset">
							<span class="visible-xs-inline fas fa-times"></span>
							<span class="d-none d-sm-none d-md-inline ml-1">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</span>
						</button>
						<button class="btn btn-success js-save-comment mt-3 float-right" type="button"
								data-mode="edit"
								data-js="click|data-mode">
							<span class="visible-xs-inline fas fa-check"></span>
							<span class="d-none d-sm-none d-md-inline ml-1">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</span>
						</button>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		{/if}
	</div>
{/strip}
