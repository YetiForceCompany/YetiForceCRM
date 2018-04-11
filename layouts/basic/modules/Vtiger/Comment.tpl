{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	{assign var="HIERARCHY" value=isset($PARENT_RECORD) && $PARENT_RECORD != $COMMENT->get('related_to')}
	<div class="Comment commentDiv">
		<div class="singleComment">
			<div class="commentInfoHeader m-0" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}">
				<div class="float-left">
					{assign var=IMAGE value=$COMMENT->getImage()}
					{if $IMAGE}
						<img class="userImage float-left" alt="" src="{$IMAGE.url}">
						<br/>
					{else}
						<span class="fas fa-user userImage float-left"></span>
					{/if}
				</div>
				<div class="commentTitle ml-5 mb-0 d-flex justify-content-between" id="{$COMMENT->getId()}">
					{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
					{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
					<div class="commentorInfo w-100">
						{assign var=COMMENTOR value=$COMMENT->getCommentedByModel()}
						<div class="d-flex justify-content-between">
							<span class="commentorName">
								<strong>{$COMMENTOR->getName()}</strong>
							</span>
							<span class="pr-2">
								<p class="text-muted"><small>{\App\Fields\DateTime::formatToViewDate($COMMENT->getCommentedTime())}</small></p>
							</span>
						</div>
						{if $HIERARCHY}
							{assign var=RELATED_TO value=$COMMENT->get('related_to')}
							<input hidden="" class="related_to" name="related_to" value="{$RELATED_TO}"  />
							{assign var=RELATED_MODULE value=\App\Record::getType($RELATED_TO)}
							<a href="index.php?module={$RELATED_MODULE}&view=Detail&record={$RELATED_TO}">
								<strong>{\App\Language::translate($RELATED_MODULE,$RELATED_MODULE)}:&nbsp;&nbsp;</strong>
								<strong class="commentRelatedTitle">{$COMMENT->getDisplayValue('related_to')}</strong>
							</a>
						{/if}
						<div class="commentInfoContent ">
							{$COMMENT->getDisplayValue('commentcontent')}
						</div>
					</div>
				</div>
			</div>
			<div class="commentActionsContainer d-flex flex-wrap justify-content-between align-items-center m-0">
				{assign var="REASON_TO_EDIT" value=$COMMENT->getDisplayValue('reasontoedit')}
				<div class="editedStatus" name="editStatus">
					<span class="{if empty($REASON_TO_EDIT)}d-none{/if} editReason text-muted">
						<p>
							<small>[ {\App\Language::translate('LBL_EDIT_REASON',$MODULE_NAME)} ] : <span  name="editReason" class="u-text-ellipsis">{nl2br($REASON_TO_EDIT)}</span></small>
							{if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
								<span class="d-block text-muted"><small><em>{\App\Language::translate('LBL_MODIFIED',$MODULE_NAME)}</em></small>&nbsp;<small class="commentModifiedTime">{\App\Fields\DateTime::formatToViewDate($COMMENT->getModifiedTime())}</small></span>
							{/if}
						</p>
					</span>
				</div>
				<div class="commentActionsDiv p-0">
					{assign var=COMMENTS_MODULE_MODEL value = Vtiger_Module_Model::getInstance('ModComments')}
					<div class="commentActions">
						{if $CHILDS_ROOT_PARENT_MODEL}
							{assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
						{/if}
						{if $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
							<button type="button" class="btn btn-sm btn-success replyComment">
								<span class="fas fa-share"></span>
								&nbsp;{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}
							</button>
						{/if}
						{if \App\Privilege::isPermitted('ModComments','EditableComments') && $CURRENTUSER->getId() eq $COMMENT->get('userid')}
							<button type="button" class="btn btn-sm btn-primary editComment feedback ml-1">
								<span class="fas fa-edit"></span>&nbsp;{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}
							</button>
						{/if}
						{assign var=LINKS value=$COMMENT->getCommentLinks()}
						{if count($LINKS) > 0}
							{foreach from=$LINKS item=LINK}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='comment'}
							{/foreach}
						{/if}
						{assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
						{if $CHILD_COMMENTS_MODEL neq null and ($CHILDS_ROOT_PARENT_ID neq $PARENT_COMMENT_ID)}
							<span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
								<button type="button" class="btn btn-sm btn-info viewThread ml-1">
									<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
									<span class="fas fa-share"></span>
								</button>
							</span>
							<span class="d-none hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
								<a class="u-cursor-pointer hideThread">
									<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
									<img class="alignMiddle" src="{\App\Layout::getImagePath('downArrowSmall.png')}" />
								</a>
							</span>
						{elseif $CHILD_COMMENTS neq null and ($CHILDS_ROOT_PARENT_ID eq $PARENT_COMMENT_ID)}
							<span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
								<button type="button" class="btn btn-sm btn-info viewThread ml-1">
									<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
									<span class="fas fa-share"></span>
								</button>
							</span>
							<span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
								<a class="u-cursor-pointer hideThread">
									<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
									<img class="alignMiddle" src="{\App\Layout::getImagePath('downArrowSmall.png')}" />
								</a>
							</span>
						{/if}
						</small></p>
					</div>
				</div>
			</div>
		</div>
		<div>
		{/strip}

