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
{include file='CommentModal.tpl'|@vtemplate_path}
<input type="hidden" id="currentComment" value="{if !empty($CURRENT_COMMENT)}{$CURRENT_COMMENT->getId()}{/if}">
<div class="col-md-12 row no-margin commentsBar paddingLRZero">
	{if $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
		<div class="commentTitle col-xs-12 paddingTop10" >
			<div class="addCommentBlock pull-left">
				<div class="input-group">
					<span class="input-group-addon " >
						<span class="glyphicon glyphicon-comment"></span>
					</span>
					<input type="text" name="commentcontent" class="commentcontent form-control" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}">
					<span class="input-group-btn">
						<button class="btn btn-primary commentModalBtn" type="button" data-mode="add">
							<strong>{vtranslate('LBL_MORE', $MODULE_NAME)}</strong>
						</button>
						<button class="btn btn-success saveComment" type="button" data-mode="add">
							<span class="visible-xs-inline-block glyphicon glyphicon-ok"></span>
							<strong class="hidden-xs">{vtranslate('LBL_POST', $MODULE_NAME)}</strong>
						</button>
					</span>
				</div>
			</div>
		</div>
	{/if}
</div>
<div class="commentContainer">
	<div class="commentsList commentsBody  col-md-12 paddingLRZero">
	{include file='CommentsList.tpl'|@vtemplate_path COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL}
	</div>
	<div class="hide basicAddCommentBlock marginTop10 marginBottom10px">
		<div class="row">
			<div class="col-md-12">
				<div class="input-group">
					<span class="input-group-addon" >
						<span class="glyphicon glyphicon-comment"></span>
					</span>
					<input class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}">
					<span class="input-group-btn">
						<button class="btn btn-primary commentModalBtn" type="button" data-mode="add">
							<strong>{vtranslate('LBL_MORE', $MODULE_NAME)}</strong>
						</button>
						<button class="btn btn-success saveComment" type="button" data-mode="add">
							<span class="visible-xs-inline-block glyphicon glyphicon-ok"></span>
							<strong class="hidden-xs">{vtranslate('LBL_POST', $MODULE_NAME)}</strong>
						</button>
						<button class="cursorPointer closeCommentBlock btn btn-warning" type="reset">
							<span class="visible-xs-inline-block glyphicon glyphicon-remove"></span>
							<strong class="hidden-xs">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</strong>
						</button>
					</span>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="hide basicEditCommentBlock" >
		<div class="row">
			<div class="col-md-12 marginTop10 marginBottom10px">
				<input type="text" name="reasonToEdit" title="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level form-control"/>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 marginBottom10px">
				<div class="input-group">
					<span class="input-group-addon" >
						<span class="glyphicon glyphicon-comment"></span>
					</span>
					<input  class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" >
					<span class="input-group-btn">
						<button class="btn btn-primary commentModalBtn" type="button" data-mode="edit">
							<strong>{vtranslate('LBL_MORE', $MODULE_NAME)}</strong>
						</button>
						<button class="btn btn-success saveComment" type="button" data-mode="edit">
							<span class="visible-xs-inline-block glyphicon glyphicon-ok"></span>
							<strong class="hidden-xs">{vtranslate('LBL_POST', $MODULE_NAME)}</strong>
						</button>
						<button class="cursorPointer closeCommentBlock btn btn-warning" type="reset">
							<span class="visible-xs-inline-block glyphicon glyphicon-remove"></span>
							<strong class="hidden-xs">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</strong>
						</button>
					</span>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
{/strip}
