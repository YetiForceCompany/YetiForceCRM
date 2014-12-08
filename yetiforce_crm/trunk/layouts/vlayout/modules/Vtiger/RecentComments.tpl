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
{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}

<div class="commentContainer recentComments">
	<div class="commentTitle row-fluid">
		{if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
			<div class="addCommentBlock">
				<div>
					<textarea name="commentcontent" class="commentcontent"  placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
				</div>
				<div class="pull-right">
					<button class="btn btn-success detailViewSaveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
				</div>
			</div>
		{/if}
	</div>
	<hr><br>
	<div class="commentsBody">
		{if !empty($COMMENTS)}
			{foreach key=index item=COMMENT from=$COMMENTS}
				<div class="commentDetails">
					<div class="commentDiv">
						<div class="singleComment">
							<div class="commentInfoHeader row-fluid" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}">
								<div class="commentTitle">
									{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
									{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
									<div class="row-fluid">
										<div class="span1">
											{assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
											<img class="alignMiddle pull-left" src="{if !empty($IMAGE_PATH)}{$IMAGE_PATH}{else}{vimage_path('DefaultUserIcon.png')}{/if}">
										</div>
										<div class="span11 commentorInfo">
											{assign var=COMMENTOR value=$COMMENT->getCommentedByModel()}
											<div class="inner">
												<span class="commentorName"><strong>{$COMMENTOR->getName()}</strong></span>
												<span class="pull-right">
													<p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}</small></p>
												</span>
												<div class="clearfix"></div>
											</div>
											<div class="commentInfoContent">
												{nl2br($COMMENT->get('commentcontent'))}
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row-fluid commentActionsContainer">
								
								{assign var="REASON_TO_EDIT" value=$COMMENT->get('reasontoedit')}
								<div class="row-fluid editStatus"  name="editStatus">
										<span class="span6{if empty($REASON_TO_EDIT)} hide{/if}">
												<p class="muted">
													<small>
													[ {vtranslate('LBL_EDIT_REASON',$MODULE_NAME)} ] :
													<span  name="editReason" class="textOverflowEllipsis">{nl2br($REASON_TO_EDIT)}</span>
													</small>
												</p>
										</span>
										{if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
											<span class="{if empty($REASON_TO_EDIT)}row-fluid{else} span6{/if}">
												<p class="muted pull-right">
													<small><em>{vtranslate('LBL_MODIFIED',$MODULE_NAME)}</em></small>&nbsp;
													<small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getModifiedTime())}" class="commentModifiedTime">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getModifiedTime())}</small>
												</p>
											</span>
										{/if}
								</div>
								<div class="row-fluid">
									<div class="pull-right commentActions">
										{if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
											<span>
												<a class="cursorPointer replyComment feedback">
													<i class="icon-share-alt"></i>{vtranslate('LBL_REPLY',$MODULE_NAME)}
												</a>
												{if $CURRENTUSER->getId() eq $COMMENT->get('userid')}
													&nbsp;<span>|</span>&nbsp;
													<a class="cursorPointer editComment feedback">
														{vtranslate('LBL_EDIT',$MODULE_NAME)}
													</a>
												{/if}
											</span>
										{/if}
										<span>
											{if $PARENT_COMMENT_MODEL neq false or $CHILD_COMMENTS_MODEL neq null}
												{if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}&nbsp;<span>|</span>&nbsp;{/if}
												<a href="javascript:void(0);" class="cursorPointer detailViewThread">{vtranslate('LBL_VIEW_THREAD',$MODULE_NAME)}</a>
											{/if}
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			{/foreach}
		{else}
			{include file="NoComments.tpl"|@vtemplate_path}
		{/if}
	</div>
	{if $PAGING_MODEL->isNextPageExists()}
		<div class="row-fluid">
			<div class="pull-right">
				<a href="javascript:void(0)" class="moreRecentComments">{vtranslate('LBL_MORE',$MODULE_NAME)}..</a>
			</div>
		</div>
	{/if}
	<div class="hide basicAddCommentBlock">
		<div class="row-fluid">
			<span class="span1">&nbsp;</span>
			<div class="span11">
				<textarea class="commentcontenthidden fullWidthAlways" name="commentcontent" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
			</div>
		</div>
		<div class="pull-right">
			<button class="btn btn-success detailViewSaveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
			<a class="cursorPointer closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
		</div>
	</div>
	<div class="hide basicEditCommentBlock" style="min-height: 150px;">
		<div class="row-fluid">
			<span class="span1">&nbsp;</span>
			<div class="span11">
				<input type="text" name="reasonToEdit" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level"/>
			</div>
		</div>
		<div class="row-fluid">
			<span class="span1">&nbsp;</span>
			<div class="span11">
				<textarea class="commentcontenthidden fullWidthAlways" name="commentcontent" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
			</div>
		</div>
		<div class="pull-right">
			<button class="btn btn-success detailViewSaveComment" type="button" data-mode="edit"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
			<a class="cursorPointer closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
		</div>
	</div>
</div>
{/strip}