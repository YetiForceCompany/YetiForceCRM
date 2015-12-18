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
	<div class="commentContainer recentComments">
		<div class="commentTitle">
			{if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
				<div class="addCommentBlock">
					<div class="input-group">
						<span class="input-group-addon" >
							<span class="glyphicon glyphicon-comment"></span>
						</span>
						<input type="text"  name="commentcontent" class="commentcontent form-control" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" >
						<span class="input-group-btn" >
							<button class="btn btn-success detailViewSaveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
						</span>
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
								<div class="commentInfoHeader" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}">
									<div class="commentTitle">
										{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
										{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
										<div class="row">
											<div class="paddingLeftMd">
												{assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
												<img class="alignMiddle pull-left" width="48" alt="" src="{if !empty($IMAGE_PATH)}{$IMAGE_PATH}{else}{vimage_path('DefaultUserIcon.png')}{/if}">
											</div>
											<div class="col-md-8 commentorInfo">
												{assign var=COMMENTOR value=$COMMENT->getCommentedByModel()}
												<span class="commentorName"><strong>{$COMMENTOR->getName()}</strong></span>
												<div class="commentInfoContent">
													{nl2br($COMMENT->get('commentcontent'))}
												</div>
											</div>
											<div class="inner">
												<span class="pull-right paddingRight15">
													<p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}</small></p>
												</span>
												<div class="clearfix"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="commentActionsContainer">
									{assign var="REASON_TO_EDIT" value=$COMMENT->get('reasontoedit')}
									<div class="pull-left {if empty($REASON_TO_EDIT)}hide {/if}editStatus"  name="editStatus">
										<span class="pull-left paddingRight10">
											<p class="muted">
												<small>
													[ {vtranslate('LBL_EDIT_REASON',$MODULE_NAME)} ] :
													<span  name="editReason" class="textOverflowEllipsis">{nl2br($REASON_TO_EDIT)}</span>
												</small>
											</p>
										</span>
										{if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
											<span class="pull-right">
												<p class="muted pull-right">
													<small><em>{vtranslate('LBL_MODIFIED',$MODULE_NAME)}</em></small>&nbsp;
													<small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getModifiedTime())}" class="commentModifiedTime">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getModifiedTime())}</small>
												</p>
											</span>
										{/if}
									</div>
									<div class="pull-right commentActions">
										{if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
											<span>
												<a class="cursorPointer replyComment feedback">
													<span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>&nbsp;
													{vtranslate('LBL_REPLY',$MODULE_NAME)}
												</a>
												{if Users_Privileges_Model::isPermitted('ModComments','EditableComments') && $CURRENTUSER->getId() eq $COMMENT->get('userid')}
													&nbsp;<span>|</span><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>&nbsp;
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
									<div class="clearfix"></div>

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
			<div class="row">
				<div class="pull-right">
					<a href="javascript:void(0)" class="moreRecentComments">{vtranslate('LBL_MORE',$MODULE_NAME)}..</a>
				</div>
			</div>
		{/if}
		<div class="hide basicAddCommentBlock marginTop10 marginBottom10px">
			<div class="row">
				<div class="col-md-12">
					<div class="input-group">
						<span class="input-group-addon" >
							<span class="glyphicon glyphicon-comment"></span>
						</span>
						<input class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}">
						<span class="input-group-btn">
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
