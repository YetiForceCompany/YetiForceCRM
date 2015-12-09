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
<input type="hidden" id="currentComment" value="{if !empty($CURRENT_COMMENT)}{$CURRENT_COMMENT->getId()}{/if}">
<div class="col-md-12 commentsBar paddingLRZero">
	{if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
		<div class="commentTitle" >
			<div class="addCommentBlock col-md-8 pull-left">
				<div class="input-group">
					<span class="input-group-addon " >
						<span class="glyphicon glyphicon-comment"></span>
					</span>
					<input type="text" name="commentcontent" class="commentcontent form-control" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}">
					<span class="input-group-btn">
						<button class="btn btn-success saveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
					</span>
				</div>
			</div>
		</div>
	{/if}
	<div class="col-md-4 pull-right">
		<div class="bootstrap-switch-container pull-right">
			<input class="switchBtn" type="checkbox" checked="" data-size="small" data-handle-width="90" data-label-width="5" data-off-text="{vtranslate('LBL_RECORDS_LIST', $MODULE_NAME)}" data-on-text="{vtranslate('LBL_TIMELINE', $MODULE_NAME)}">
		</div>
	</div>
</div>
<div id="timeline" class="timelineContainer"></div>
<div class="commentContainer">
	<div class="commentsList commentsBody  col-md-12 ">
	</div>
	<div class="hide basicAddCommentBlock marginBottom10px">
		<div class="row">
			<div class="col-md-12">
				<div class="input-group">
					<span class="input-group-addon" >
						<span class="glyphicon glyphicon-comment"></span>
					</span>
					<input class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}">
					<span class="input-group-btn">
						<button class="btn btn-success saveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
					</span>
					<span class="input-group-btn">
						<button class="cursorPointer closeCommentBlock btn btn-warning" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</button>
				
					</span>
				</div>
			</div>
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
		<div class="pull-right pushDown">
			<button class="btn btn-success saveComment" type="button" data-mode="edit"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
			<button class="cursorPointer closeCommentBlock cancelLink btn btn-warning" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</button>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
