{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class="tpl-Base-CommentThreadList Comment comment-div js-comment-div" data-js="container">
		<div class="js-comment-single singleComment" data-js="append">
			<div class="js-comment-info-header commentInfoHeader m-0" data-commentid="{$COMMENT->getId()}"
				data-parentcommentid="{$COMMENT->get('parent_comments')}"
				data-js="data-commentid|data-parentcommentid">
				<div class="float-left">
					{assign var=IMAGE value=$COMMENT->getImage()}
					{if $IMAGE}
						<img class="c-img__user float-left" alt="" src="{$IMAGE.url}">
						<br />
					{else}
						<span class="fas fa-user userImage float-left"></span>
					{/if}
				</div>
				<div class="commentTitle ml-5 mb-0 d-flex justify-content-between" id="{$COMMENT->getId()}">
					{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
					{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
					<div class="commentorInfo w-100">
						<div class="d-flex justify-content-between">
							<span class="commentorName">
								<strong>{$COMMENT->getCommentatorName()}</strong>
							</span>
							<span class="pr-2">
								<p class="text-muted"><small>{\App\Fields\DateTime::formatToViewDate($COMMENT->getCommentedTime())}</small></p>
							</span>
						</div>
						{if !empty($HIERARCHY)}
							{assign var=RELATED_TO value=$COMMENT->get('related_to')}
							<input hidden="" class="related_to" name="related_to" value="{$RELATED_TO}" />
							{assign var=RELATED_MODULE value=\App\Record::getType($RELATED_TO)}
							<a href="index.php?module={$RELATED_MODULE}&view=Detail&record={$RELATED_TO}">
								<strong>{\App\Language::translate($RELATED_MODULE,$RELATED_MODULE)}
									:&nbsp;&nbsp;</strong>
								<strong>{$COMMENT->getDisplayValue('related_to')}</strong>
							</a>
						{/if}
						<div class="js-comment-info commentInfoContent" data-js="html">
							{$COMMENT->getDisplayValue('commentcontent')}
						</div>
					</div>
				</div>
			</div>
			<div class="js-comment-container commentActionsContainer row no-margin" data-js="hide|show">
				{assign var="REASON_TO_EDIT" value=$COMMENT->getDisplayValue('reasontoedit')}
				<div class="js-edited-status edited-status" name="editStatus" data-js="class: d-none">
					<span class="{if empty($REASON_TO_EDIT)}d-none{/if} js-edit-reason text-muted"
						data-js="class: d-none">
						<p>
							<small>
								[ {\App\Language::translate('LBL_EDIT_REASON',$MODULE_NAME)} ] :
								<span name="editReason" class="js-edit-reason-span u-text-ellipsis ml-1" data-js="text">
									{nl2br($REASON_TO_EDIT)}
								</span>
							</small>
							{if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
								<span class="d-block text-muted">
									<small>
										<em>{\App\Language::translate('LBL_MODIFIED',$MODULE_NAME)}</em>
									</small>&nbsp;
									<small class="js-comment-modified-time commentModifiedTime" data-js="html">
										{\App\Fields\DateTime::formatToViewDate($COMMENT->getModifiedTime())}
									</small>
								</span>
							{/if}
						</p>
					</span>
				</div>
				<div class="commentActionsDiv">
					{assign var=COMMENTS_MODULE_MODEL value = Vtiger_Module_Model::getInstance('ModComments')}
					<span class="float-right commentActions">
						{assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
						{if $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
							<button type="button" class="btn btn-sm btn-success js-reply-comment m-0 px-1 py-0"
								title="{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}" data-js="click">
								<span class="fas fa-share"></span>
							</button>
						{/if}
						{if \App\Privilege::isPermitted('ModComments','EditableComments') && $CURRENTUSER->getId() eq $COMMENT->get('userid')}
							<button type="button" class="btn btn-sm btn-primary js-edit-comment feedback m-0 px-1 py-0"
								title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}" data-js="click">
								<span class="yfi yfi-full-editing-view"></span>
							</button>
						{/if}
						{assign var=LINKS value=$COMMENT->getCommentLinks()}
						{if count($LINKS) > 0}
							{foreach from=$LINKS item=LINK}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='comment'  MODULE=$MODULE_NAME}
							{/foreach}
						{/if}
					</span>
				</div>
			</div>
		</div>
	</div>
{/strip}
