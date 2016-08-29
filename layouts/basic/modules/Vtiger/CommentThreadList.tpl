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
						<img class="alignMiddle pull-left" width="48px" alt="" src="{if !empty($IMAGE_PATH)}{$IMAGE_PATH}{else}{vimage_path('DefaultUserIcon.png')}{/if}">
					</div>
					<div class="col-xs-8 commentorInfo">
						{assign var=COMMENTOR value=$COMMENT->getCommentedByModel()}
						<div class="inner">
							<span class="commentorName pull-left"><strong>{$COMMENTOR->getName()}</strong></span>
							<div class="clearfix"></div>
						</div>
						<div class="commentInfoContent">
							{nl2br($COMMENT->get('commentcontent'))}
						</div>
					</div>
					<span class="pull-right paddingRight15">
						<p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}" class="commentModifiedTime">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}</small></p>
					</span>
				</div>
			</div>
		</div>
		<div class="commentActionsContainer row no-margin ">

			{assign var="REASON_TO_EDIT" value=$COMMENT->get('reasontoedit')}
			<div class="editedStatus visible-lg-block col-xs-6"  name="editStatus">
				<p class="col-xs-6 marginLeftZero">
					<small>
						<span class="{if empty($REASON_TO_EDIT)}hide{/if} marginLeftZero editReason">
							[ {vtranslate('LBL_EDIT_REASON',$MODULE_NAME)} ] : <span  name="editReason" class="textOverflowEllipsis">{nl2br($REASON_TO_EDIT)}</span>
						</span>
					</small>
				</p>
				{if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
					<span class="{if empty($REASON_TO_EDIT)}row{else} col-xs-6 paddingRightZero{/if}">
						<span class="pull-right">
							<p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getModifiedTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getModifiedTime())}</small></p>
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
							<span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>
							&nbsp;{vtranslate('LBL_REPLY',$MODULE_NAME)}
						</button>
					{/if}
					{if Users_Privileges_Model::isPermitted('ModComments','EditableComments') && $CURRENTUSER->getId() eq $COMMENT->get('userid')}
						<button type="button" class="btn btn-xs btn-primary editComment feedback marginLeft5">
							<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>&nbsp;{vtranslate('LBL_EDIT',$MODULE_NAME)}
						</button>
					{/if}
					{if $COMMENTS_MODULE_MODEL->isPermitted('Delete')}
						<button type="button" class="btn btn-xs btn-danger deleteComment marginLeft5">
							<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;{vtranslate('LBL_DELETE',$MODULE_NAME)}
						</button>
					{/if}
				</span>
			</div>
		</div>
	</div>
</div>
{/strip}
