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
	{* Change to this also refer: RecentComments.tpl *}
	{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
	<input type="hidden" id="currentComment" value="{if !empty($CURRENT_COMMENT)}{$CURRENT_COMMENT->getId()}{/if}">
	<div class="col-md-12 form-row m-0 commentsBar px-0">
		{if $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
			<div class="commentTitle col-12 pt-2">
				<div class="addCommentBlock">
					<div class="input-group">
						<span class="input-group-prepend">
							<div class="input-group-text"><span class="fas fa-comments"></span></div>
						</span>
						<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" name="commentcontent" class="commentcontent form-control" title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
					</div>
					<button class="btn btn-success mt-3 saveComment float-right" type="button" data-mode="add">
						<span class="visible-xs-inline-block fas fa-check"></span>
						<strong class="d-none d-sm-none d-md-inline ml-1">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</strong>
					</button>
				</div>
			</div>
		{/if}
	</div>
	{if count($HIERARCHY_LIST) != 1}
		<div class="col-md-12 form-row commentsHeader my-3 mx-0 px-0">
			<div class="col-md-4"></div>
			<div class="col-md-4">
				<div class="input-group">
					<span class="input-group-prepend" id="commentSearchAddon">
						<div class="input-group-text"><span class="fas fa-search"></span> </div>
					</span>
					<input type="text" class="form-control commentSearch" placeholder="{\App\Language::translate('LBL_COMMENTS_SEARCH','ModComments')}" aria-describedby="commentSearchAddon">
				</div>
			</div>
			<div class="col-md-4 pr-0">
				<select class="select2 form-control commentsHierarchy" multiple>
					{foreach key=NAME item=LABEL from=$HIERARCHY_LIST}
						<option value="{$NAME}" {if in_array($NAME, $HIERARCHY)}selected{/if}>{\App\Language::translate($LABEL, 'ModComments')}</option>
					{/foreach}
				</select>
			</div>
		</div>
	{/if}
	<div class="commentContainer">
		<div class="commentsList commentsBody  col-md-12 px-0">
			{include file=\App\Layout::getTemplatePath('CommentsList.tpl') COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL}
		</div>
		<div class="d-none basicAddCommentBlock my-3">
			<div class="form-row">
				<div class="col-md-12">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<span class="fas fa-comments"></span>
							</span>
						</div>
						<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
					</div>
					<button class="u-cursor-pointer mt-3 closeCommentBlock btn btn-warning float-right cancel"
							type="reset">
						<span class="visible-xs-inline-block fas fa-times"></span>
						<strong class="d-none d-sm-none d-md-inline ml-1">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
					</button>
					<button class="btn btn-success mt-3 saveComment float-right" type="button" data-mode="add">
						<span class="visible-xs-inline-block fas fa-check"></span>
						<strong class="d-none d-sm-none d-md-inline ml-1">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</strong>
					</button>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
		<div class="d-none basicEditCommentBlock">
			<div class="form-row">
				<div class="col-md-12 my-3">
					<input type="text" name="reasonToEdit" title="{\App\Language::translate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level form-control"/>
				</div>
			</div>
			<div class="form-row">
				<div class="col-md-12 mb-3">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<span class="fas fa-comments"></span>
							</span>
						</div>
						<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
					</div>
					<button class="u-cursor-pointer mt-3 closeCommentBlock btn btn-warning float-right cancel" type="reset">
						<span class="visible-xs-inline-block fas fa-times"></span>
						<strong class="d-none d-sm-none d-md-inline ml-1">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
					</button>
					<button class="btn btn-success mt-3 saveComment float-right" type="button" data-mode="edit">
						<span class="visible-xs-inline-block fas fa-check"></span>
						<strong class="d-none d-sm-none d-md-inline ml-1">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</strong>
					</button>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
{/strip}
