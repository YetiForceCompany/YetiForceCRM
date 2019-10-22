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
<!-- tpl-Base-Comment -->
{assign var="HIERARCHY" value=isset($PARENT_RECORD) && $PARENT_RECORD != $COMMENT->get('related_to')}
<div class="Comment comment-div js-comment-div" data-js="container">
	<div class="js-comment-single singleComment" data-js="append">
		<div class="js-comment-info-header commentInfoHeader m-0 row" data-commentid="{$COMMENT->getId()}"
			 data-parentcommentid="{$COMMENT->get('parent_comments')}"
			 data-js="data-commentid|data-parentcommentid">
			{assign var=IS_CURRENT_USER value=$CURRENTUSER->getId() eq $COMMENT->get('userid')}
			<div class="quasar-reset q-message q-message-{if $IS_CURRENT_USER}sent{else}received{/if} full-width" id="{$COMMENT->getId()}">
				{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
				{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
				<div class="q-message-container row items-end no-wrap">
					{assign var=COMMENTOR value=$COMMENT->getCommentedByModel()}
						{assign var=IMAGE value=$COMMENT->getImage()}
					<img class="q-message-avatar gt-sm" alt="userImage" src="{if $IMAGE}{$IMAGE.url}{/if}">
					<div class="full-width">
						<span class="q-message-name">
							{$COMMENTOR->getName()}
						</span>
						<div class="q-message-text q-py-xs">
							{if $HIERARCHY}
								{assign var=RELATED_TO value=$COMMENT->get('related_to')}
								<input hidden="" class="related_to" name="related_to" value="{$RELATED_TO}"/>
								{assign var=RELATED_MODULE value=\App\Record::getType($RELATED_TO)}
								<a href="index.php?module={$RELATED_MODULE}&view=Detail&record={$RELATED_TO}">
									<strong>{\App\Language::translate($RELATED_MODULE,$RELATED_MODULE)}:&nbsp;&nbsp;</strong>
									<strong>{$COMMENT->getDisplayValue('related_to')}</strong>
								</a>
							{/if}
							<span class="q-message-text-content js-comment-info" data-js="html">
								<div>{$COMMENT->getDisplayValue('commentcontent')}</div>
								<div class="u-w-fit q-ml-auto q-message-stamp">{\App\Fields\DateTime::formatToViewDate($COMMENT->getCommentedTime())}</div>
							</span>
						</div>
					</div>
					<div class="q-fab z-fab row inline justify-center js-comment-actions__container">
						<button type="button" tabindex="0" class="js-comment-actions__btn q-btn inline q-btn-item non-selectable no-outline q-btn--flat q-btn--round text-grey-6 q-focusable q-hoverable {if $IS_CURRENT_USER}q-mr-sm{else}q-ml-sm{/if} u-font-size-13px">
							<div tabindex="-1" class="q-focus-helper"></div>
							<div class="q-btn__content text-center col items-center q-anchor--skip justify-center row">
								<i aria-hidden="true" class="mdi mdi-wrench q-icon"></i>
							</div>
						</button>
						<div class="q-fab__actions flex no-wrap inline items-center q-fab__actions--{if $IS_CURRENT_USER}right{else}left{/if} js-comment-actions">
							{assign var=COMMENTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('ModComments')}
							{if !empty($CHILDS_ROOT_PARENT_MODEL)}
								{assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
							{/if}
							{if $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
								<button type="button" class="btn rounded-circle btn-success js-reply-comment mr-1"
										title="{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}" data-js="click">
									<span class="fas fa-share"></span>
								</button>
							{/if}
							{if \App\Privilege::isPermitted('ModComments','EditableComments') && $CURRENTUSER->getId() eq $COMMENT->get('userid')}
								<button type="button" class="btn rounded-circle btn-primary js-edit-comment feedback mr-1"
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
							<button type="button" class="btn rounded-circle btn-info viewThread"
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
							<button type="button" class="btn rounded-circle btn-info viewThread"
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
									class="btn rounded-circle btn-secondary js-view-parent-thread"
									data-js="click" title="{\App\Language::translate('LBL_THREAD',$MODULE_NAME)}">
								<span class="fas fa-share"></span>
							</button>
						</span>
							{/if}
							</small></p>
						</div>
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
<!-- /tpl-Base-Comment -->
{/strip}
