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
	<!-- tpl-Base-Comment -->
	{assign var=HIERARCHY value=isset($PARENT_RECORD) && $PARENT_RECORD != $COMMENT->get('related_to')}
	{assign var=COMMENT_BACKGROUND value=''}
	{if $HIERARCHY}
		{assign var=COMMENT_BACKGROUND value='u-bg-light-blue'}
	{/if}
	{if $COMMENT->get('customer')}
		{assign var=COMMENT_BACKGROUND value='u-bg-light-orange'}
	{/if}
	<div class="Comment comment-div js-comment-div border-bottom pb-3" data-js="container">
		<div class="js-comment-single singleComment" data-js="append">
			<div class="js-comment-info-header commentInfoHeader m-0 row" data-commentid="{$COMMENT->getId()}"
				data-parentcommentid="{$COMMENT->get('parent_comments')}"
				data-js="data-commentid|data-parentcommentid">
				{assign var=IS_CURRENT_USER value=$CURRENTUSER->getId() eq $COMMENT->get('userid')}
				<div class="quasar-reset q-message q-message-{if $IS_CURRENT_USER}sent{else}received{/if} full-width" id="{$COMMENT->getId()}">
					{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
					{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
					<div class="q-message-container row items-end no-wrap">
						{assign var=IMAGE value=$COMMENT->getImage()}
						{if $IMAGE}
							<img class="q-message-avatar gt-sm mr-2" alt="userImage" src="{$IMAGE['url']}">
						{else}
							<div class="q-message-avatar gt-sm visible u-fs-26px flex flex-center">
								<span class="{if $COMMENT->get('customer')}yfi-share-portal-record{else}fas fa-user{/if}"></span>
							</div>
						{/if}
						<div class="full-width">
							<div class="d-flex flex-wrap justify-content-between js-hb__container">
								<div>
									<span class="q-message-name">
										{$COMMENT->getCommentatorName()}
									</span>
								</div>
								<div class="items-center comment-actions js-comment-actions">
									{assign var=COMMENTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('ModComments')}
									{if !empty($CHILDS_ROOT_PARENT_MODEL)}
										{assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
									{/if}
									{if empty($IS_READ_ONLY)}
										{if $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
											<button type="button" class="btn text-success js-reply-comment m-0 px-1 py-0"
												title="{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}" data-js="click">
												<span class="fas fa-share"></span>
											</button>
										{/if}
										{if \App\Privilege::isPermitted('ModComments','EditableComments') && $CURRENTUSER->getId() eq $COMMENT->get('userid')}
											<button type="button" class="btn text-primary js-edit-comment feedback m-0 px-1 py-0"
												data-js="click" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}">
												<span class="yfi yfi-full-editing-view"></span>
											</button>
										{/if}
										{assign var=LINKS value=$COMMENT->getCommentLinks()}
										{if count($LINKS) > 0}
											{foreach from=$LINKS item=LINK}
												{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='comment' MODULE=$MODULE_NAME  BTN_CLASS='btn-sm'}
											{/foreach}
										{/if}
									{/if}
									{assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
									{if !empty($CHILD_COMMENTS_MODEL) && !empty($PARENT_COMMENT_ID) && (empty($CHILDS_ROOT_PARENT_ID) || $CHILDS_ROOT_PARENT_ID neq $PARENT_COMMENT_ID) && empty($SHOW_CHILD_COMMENTS)}
										<span class="js-view-thread-block viewThreadBlock"
											data-child-comments-count="{$CHILD_COMMENTS_COUNT}"
											data-js="data-child-comments-count">
											<button type="button" class="btn btn-md text-info viewThread u-text-ellipsis m-0 px-1 py-0"
												title="{$CHILD_COMMENTS_COUNT}&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}"
												data-js="click">
												<span class="js-child-comments-count">{$CHILD_COMMENTS_COUNT}</span>
												{if empty($IS_READ_ONLY)}
													<span class="fas fa-share ml-1"></span>
												{/if}
											</button>
										</span>
										<span class="d-none hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
											<a class="u-cursor-pointer hideThread">
												<span class="js-child-comments-count" data-js="text">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}
												&nbsp;
												<img class="alignMiddle" src="{\App\Layout::getImagePath('downArrowSmall.png')}" />
											</a>
										</span>
									{elseif !empty($CHILD_COMMENTS) && !empty($CHILDS_ROOT_PARENT_ID) && ($CHILDS_ROOT_PARENT_ID eq $PARENT_COMMENT_ID)}
										<span class="js-view-thread-block viewThreadBlock"
											data-child-comments-count="{$CHILD_COMMENTS_COUNT}"
											data-js="data-child-comments-count">
											<button type="button" class="btn btn-md text-info viewThread m-0 px-1 py-0"
												title="{$CHILD_COMMENTS_COUNT}&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}" data-js="click">
												<span class="js-child-comments-count" data-js="text">{$CHILD_COMMENTS_COUNT}</span>
												&nbsp;
												<span class="fas fa-share"></span>
											</button>
										</span>
										<span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
											<a class="u-cursor-pointer hideThread">
												<span class="js-child-comments-count" data-js="text">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}{else}{\App\Language::translate('LBL_REPLIES',$MODULE_NAME)}{/if}
												&nbsp;
												<img class="alignMiddle" src="{\App\Layout::getImagePath('downArrowSmall.png')}" />
											</a>
										</span>
									{/if}
									{if !empty($BUTTON_SHOW_PARENT) && !empty($COMMENT->get('parents'))}
										<span class="view-parent-thread-block">
											<button type="button"
												class="btn btn-md text-secondary js-view-parent-thread m-0 px-1 py-0"
												data-js="click" title="{\App\Language::translate('LBL_THREAD',$MODULE_NAME)}">
												<span class="fas fa-share"></span>
											</button>
										</span>
									{/if}
								</div>
							</div>
							<div class="q-message-text q-py-xs {$COMMENT_BACKGROUND}">
								{if $HIERARCHY}
									<span class="float-right">
										{assign var=RELATED_TO value=$COMMENT->get('related_to')}
										{assign var=RELATED_MODULE value=\App\Record::getType($RELATED_TO)}
										<input type="hidden" class="related_to" name="related_to" value="{$RELATED_TO}" />
										<span class="yfm-{$RELATED_MODULE} mr-1"></span>
										<span class="mr-1">{\App\Language::translateSingularModuleName($RELATED_MODULE)}:</span>
										{$COMMENT->getDisplayValue('related_to',false,false,150)}
									</span>
								{/if}
								<span class="q-message-text-content">
									<div class="js-comment-info" data-js="html">{$COMMENT->getDisplayValue('commentcontent')}</div>
									<div class="u-w-fit q-message-stamp ml-auto">
										{if \Config\Modules\ModComments::$dateFormat === 'user'}
											{\App\Fields\DateTime::formatToViewDate($COMMENT->getCommentedTime())}
										{else}
											{\App\Fields\DateTime::formatToDisplay($COMMENT->getCommentedTime())}
										{/if}
									</div>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="js-comment-container  d-flex flex-wrap justify-content-between align-items-center m-0 mt-n2"
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
										{if \Config\Modules\ModComments::$dateFormat === 'user'}
											{\App\Fields\DateTime::formatToViewDate($COMMENT->getModifiedTime())}
										{else}
											{\App\Fields\DateTime::formatToDisplay($COMMENT->getModifiedTime())}
										{/if}
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
