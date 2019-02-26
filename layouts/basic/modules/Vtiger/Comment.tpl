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
<div class="tpl-Base-Comment Comment comment-div js-comment-div" data-js="container">
	<div class="js-comment-single singleComment" data-js="append">
		<div class="js-comment-info-header commentInfoHeader m-0 row" data-commentid="{$COMMENT->getId()}"
			 data-parentcommentid="{$COMMENT->get('parent_comments')}"
			 data-js="data-commentid|data-parentcommentid">
			<div class="u-w-50px d-flex justify-content-center col-1 px-0">
				{assign var=IMAGE value=$COMMENT->getImage()}
				{if $IMAGE}
					<img class="c-img__user" alt="" src="{$IMAGE.url}">
					<br/>
				{else}
					<span class="fas fa-user fa-2x userImage"></span>
				{/if}
			</div>
			<div class="commentTitle mb-0 d-flex justify-content-between col-11 px-0" id="{$COMMENT->getId()}">
				{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
				{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
				<div class="commentorInfo w-100">
					{assign var=COMMENTOR value=$COMMENT->getCommentedByModel()}
					<div class="d-flex justify-content-between">
						<span class="commentorName">
							<strong>{$COMMENTOR->getName()}</strong>
						</span>
						<div class="commentActionsDiv p-0 ml-auto mr-1">
							{assign var=COMMENTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('ModComments')}
							<div class="commentActions">
								{if !empty($CHILDS_ROOT_PARENT_MODEL)}
									{assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
								{/if}
								{if $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
									<button type="button" class="btn btn-xs btn-success js-reply-comment mr-1"
											title="{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}" data-js="click">
										<span class="fas fa-share"></span>
									</button>
								{/if}
								{if \App\Privilege::isPermitted('ModComments','EditableComments') && $CURRENTUSER->getId() eq $COMMENT->get('userid')}
									<button type="button" class="btn btn-xs btn-primary js-edit-comment feedback mr-1"
											data-js="click" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}">
										<span class="fas fa-edit"></span>
									</button>
								{/if}
								{assign var=LINKS value=$COMMENT->getCommentLinks()}
								{if count($LINKS) > 0}
									{foreach from=$LINKS item=LINK}
										{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='comment' MODULE=$MODULE_NAME}
									{/foreach}
								{/if}
								{assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
								{if !empty($CHILD_COMMENTS_MODEL) && !empty($PARENT_COMMENT_ID) && (empty($CHILDS_ROOT_PARENT_ID) || $CHILDS_ROOT_PARENT_ID neq $PARENT_COMMENT_ID) && empty($SHOW_CHILD_COMMENTS)}
									<span class="js-view-thread-block viewThreadBlock"
										  data-child-comments-count="{$CHILD_COMMENTS_COUNT}"
										  data-js="data-child-comments-count">
								<button type="button" class="btn btn-xs btn-info viewThread"
										title="{$CHILD_COMMENTS_COUNT}&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}"
										data-js="click">
									<span class="js-child-comments-count">{$CHILD_COMMENTS_COUNT}</span>
									&nbsp;
									<span class="fas fa-share"></span>
								</button>
							</span>
									<span class="d-none hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
								<a class="u-cursor-pointer hideThread">
									<span class="js-child-comments-count" data-js="text">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}
									&nbsp;
									<img class="alignMiddle" src="{\App\Layout::getImagePath('downArrowSmall.png')}"/>
								</a>
							</span>
								{elseif !empty($CHILD_COMMENTS) && !empty($CHILDS_ROOT_PARENT_ID) && ($CHILDS_ROOT_PARENT_ID eq $PARENT_COMMENT_ID)}
									<span class="js-view-thread-block viewThreadBlock"
										  data-child-comments-count="{$CHILD_COMMENTS_COUNT}"
										  data-js="data-child-comments-count">
								<button type="button" class="btn btn-xs btn-info viewThread"
										title="{$CHILD_COMMENTS_COUNT}&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}"
										data-js="click">
									<span class="js-child-comments-count" data-js="text">{$CHILD_COMMENTS_COUNT}</span>
									&nbsp;
									<span class="fas fa-share"></span>
								</button>
							</span>
									<span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
								<a class="u-cursor-pointer hideThread">
									<span class="js-child-comments-count" data-js="text">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}
									&nbsp;
									<img class="alignMiddle" src="{\App\Layout::getImagePath('downArrowSmall.png')}"/>
								</a>
							</span>
								{/if}
								{if !empty($BUTTON_SHOW_PARENT) && !empty($COMMENT->get('parents'))}
									<span class="view-parent-thread-block">
								<button type="button"
										class="btn btn-xs btn-secondary js-view-parent-thread"
										data-js="click" title="{\App\Language::translate('LBL_THREAD',$MODULE_NAME)}">
									<span class="fas fa-share"></span>
								</button>
							</span>
								{/if}
								</small></p>
							</div>
						</div>
						<span class="pr-2">
								<p class="text-muted"><small>{\App\Fields\DateTime::formatToViewDate($COMMENT->getCommentedTime())}</small></p>
						</span>
					</div>
					{if $HIERARCHY}
						{assign var=RELATED_TO value=$COMMENT->get('related_to')}
						<input hidden="" class="related_to" name="related_to" value="{$RELATED_TO}"/>
						{assign var=RELATED_MODULE value=\App\Record::getType($RELATED_TO)}
						<a href="index.php?module={$RELATED_MODULE}&view=Detail&record={$RELATED_TO}">
							<strong>{\App\Language::translate($RELATED_MODULE,$RELATED_MODULE)}:&nbsp;&nbsp;</strong>
							<strong>{$COMMENT->getDisplayValue('related_to')}</strong>
						</a>
					{/if}
					<div class="js-comment-info commentInfoContent" data-js="html">
						{$COMMENT->getDisplayValue('commentcontent')}
					</div>
				</div>
			</div>
		</div>
		<div class="js-comment-container  d-flex flex-wrap justify-content-between align-items-center m-0"
			 data-js="hide|show">
			{assign var="REASON_TO_EDIT" value=$COMMENT->getDisplayValue('reasontoedit')}
			<div class="js-edited-status edited-status w-100" name="editStatus" data-js="class: d-none">
				<span class="{if empty($REASON_TO_EDIT)}d-none{/if} js-edit-reason text-muted" data-js="class: d-none">
					<p class="d-flex flex-wrap small">
						<span>
							[ {\App\Language::translate('LBL_EDIT_REASON',$MODULE_NAME)} ] :

							<span name="editReason" class="js-edit-reason-span ml-1" data-js="text">
								{nl2br($REASON_TO_EDIT)}
							</span>
							</span>
						{if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
							<span class="ml-auto">
									<em class="mr-1">{\App\Language::translate('LBL_MODIFIED',$MODULE_NAME)}</em>
								<span class="js-comment-modified-time commentModifiedTime" data-js="html">
									{\App\Fields\DateTime::formatToViewDate($COMMENT->getModifiedTime())}
								</span>
							</span>
						{/if}
					</p>
				</span>
			</div>
		</div>
	</div>
	<div>
		{/strip}

