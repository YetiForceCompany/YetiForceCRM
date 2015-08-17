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

{* Change to this also refer: RecentComments.tpl *}
{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}

<div class="commentContainer">
	<div class="commentTitle">
		{if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
			<div class="addCommentBlock">
				<div>
					<textarea name="commentcontent" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="commentcontent" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"  placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
				</div>
				<div class="pull-right">
					<button class="btn btn-success saveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
				</div>
			</div>
		{/if}
	</div>
	<br>
	<div class="commentsList commentsBody">
		{include file='CommentsList.tpl'|@vtemplate_path COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL}
	</div>
	<div class="hide basicAddCommentBlock">
		<div class="row">
			<span class="col-md-1">&nbsp;</span>
			<div class="col-md-11">
				<textarea class="commentcontenthidden fullWidthAlways" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" name="commentcontent" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
			</div>
		</div>
		<div class="pull-right">
			<button class="btn btn-success saveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
			<button class="cursorPointer closeCommentBlock btn btn-warning" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</button>
		</div>
		<div class="clearfix"></div>
	</div>
		<div class="hide basicEditCommentBlock" style="min-height: 150px;">
		<div class="row">
			<span class="col-md-1">&nbsp;</span>
			<div class="col-md-11">
				<input type="text" name="reasonToEdit" title="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}"" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level"/>
			</div>
		</div>
		<div class="row">
			<span class="col-md-1">&nbsp;</span>
			<div class="col-md-11">
				<textarea class="commentcontenthidden fullWidthAlways" name="commentcontent" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
			</div>
		</div>
		<div class="pull-right">
			<button class="btn btn-success saveComment" type="button" data-mode="edit"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
			<button class="cursorPointer closeCommentBlock cancelLink btn btn-warning" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</button>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
