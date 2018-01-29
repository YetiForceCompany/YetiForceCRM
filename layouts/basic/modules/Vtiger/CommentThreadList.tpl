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
	<div class="commentDiv">
		<div class="singleComment">
			<div class="commentInfoHeader"  data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}">
				<div class="commentTitle" id="{$COMMENT->getId()}">
					{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
					{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
					<div class="row no-margin">
						<div class="">
							{assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
							{if $IMAGE_PATH}
								<img class="userImage pull-left" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE_PATH))}" >
							{else}	
								<span class="fa fa-user userImage pull-left" aria-hidden="true"></span>
							{/if}
						</div>
						<div class="col-xs-8 commentorInfo">
							{assign var=COMMENTOR value=$COMMENT->getCommentedByModel()}
							<div class="inner">
								<span class="commentorName pull-left"><strong>{$COMMENTOR->getName()}</strong></span>
								<div class="clearfix"></div>
							</div>
							<div class="commentInfoContent">
								{$COMMENT->getDisplayValue('commentcontent')}
							</div>
						</div>
						<span class="pull-right paddingRight15">
							<p class="muted"><small class="commentModifiedTime">{\App\Fields\DateTime::formatToViewDate($COMMENT->getCommentedTime())}</small></p>
						</span>
					</div>
				</div>
			</div>
			<div class="commentActionsContainer row no-margin ">
				{assign var="REASON_TO_EDIT" value=$COMMENT->getDisplayValue('reasontoedit')}
				<div class="editedStatus visible-lg-block col-xs-6"  name="editStatus">
					<p class="col-xs-6 marginLeftZero">
						<small>
							<span class="{if empty($REASON_TO_EDIT)}hide{/if} marginLeftZero editReason">
								[ {\App\Language::translate('LBL_EDIT_REASON',$MODULE_NAME)} ] : <span  name="editReason" class="textOverflowEllipsis">{nl2br($REASON_TO_EDIT)}</span>
							</span>
						</small>
					</p>
					{if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
						<span class="{if empty($REASON_TO_EDIT)}row{else} col-xs-6 paddingRightZero{/if}">
							<span class="pull-right">
								<p class="muted"><small>{\App\Fields\DateTime::formatToViewDate($COMMENT->getModifiedTime())}</small></p>
							</span>
						</span>
					{/if}
				</div>
				<div class="commentActionsDiv">
					{assign var=COMMENTS_MODULE_MODEL value = Vtiger_Module_Model::getInstance('ModComments')}
					<span class="pull-right commentActions">
						{assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
						{if $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
							<button type="button" class="btn btn-xs btn-success replyComment">
								<span class="fa fa-share" aria-hidden="true"></span>
								&nbsp;{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}
							</button>
						{/if}
						{if \App\Privilege::isPermitted('ModComments','EditableComments') && $CURRENTUSER->getId() eq $COMMENT->get('userid')}
							<button type="button" class="btn btn-xs btn-primary editComment feedback marginLeft5">
								<span class="fas fa-pencil-alt" aria-hidden="true"></span>&nbsp;{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}
							</button>
						{/if}
						{assign var=LINKS value=$COMMENT->getCommentLinks()}
						{if count($LINKS) > 0}
							{foreach from=$LINKS item=LINK}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='comment'}
							{/foreach}
						{/if}
					</span>
				</div>
			</div>
		</div>
	</div>
{/strip}
