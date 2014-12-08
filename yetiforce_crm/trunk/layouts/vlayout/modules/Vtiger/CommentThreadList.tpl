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
<div class="commentDiv cursorPointer">
	<div class="singleComment">
		<div class="commentInfoHeader row-fluid"  data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}">
			<div class="commentTitle" id="{$COMMENT->getId()}">
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
							<span class="commentorName pull-left"><strong>{$COMMENTOR->getName()}</strong></span>
							<span class="pull-right">
								<p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}" class="commentModifiedTime">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}</small></p>
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
			<div class="row-fluid editedStatus"  name="editStatus">
				<div class="row-fluid">
					<p class="span6 marginLeftZero"><small>
					<span class="{if empty($REASON_TO_EDIT)}hide{/if} span6 marginLeftZero editReason">
						[ {vtranslate('LBL_EDIT_REASON',$MODULE_NAME)} ] : <span  name="editReason" class="textOverflowEllipsis">{nl2br($REASON_TO_EDIT)}</span>
					</span>
					</small></p>
					{if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
						<span class="{if empty($REASON_TO_EDIT)}row-fluid{else} span6{/if}">
							<span class="pull-right">
								<p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getModifiedTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getModifiedTime())}</small></p>
							</span>
						</span>
					{/if}
					
				</div>
			</div>
			<div class="row-fluid commentActionsDiv">
				{assign var=COMMENTS_MODULE_MODEL value = Vtiger_Module_Model::getInstance('ModComments')}
					<span class="pull-right commentActions">
						{assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
						{if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
							<span>
								<a class="cursorPointer replyComment">&nbsp;
									<i class="icon-share-alt"></i>
									{vtranslate('LBL_REPLY',$MODULE_NAME)}
								</a>
								{if $CURRENTUSER->getId() eq $COMMENT->get('userid')}
									&nbsp;<span style="color:black">|</span>&nbsp;
									<a class="cursorPointer editComment feedback">
										{vtranslate('LBL_EDIT',$MODULE_NAME)}
									</a>
								{/if}
							</span>
						{/if}
						{if $CHILD_COMMENTS_COUNT neq 0}
							{if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}&nbsp;<span style="color:black">|</span>&nbsp;{/if}
							<span class="hide viewThreadBlock" data-child-comments-count="{$COMMENT->getChildCommentsCount()}">
									<a class="cursorPointer viewThread">
										<span class="childCommentsCount">{$COMMENT->getChildCommentsCount()}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
										<img class="alignMiddle" src="{vimage_path('rightArrowSmall.png')}" />
									</a>
							</span>
							<span class="hideThreadBlock" data-child-comments-count="{$COMMENT->getChildCommentsCount()}">
								<a class="cursorPointer hideThread">
									<span class="childCommentsCount">{$COMMENT->getChildCommentsCount()}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
									<img class="alignMiddle" src="{vimage_path('downArrowSmall.png')}" />
								</a>
							</span>
						{/if}
					</span>
			</div>
		</div>
					</div>
				</div>
