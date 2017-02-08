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
	<div class="commentDiv">
		<div class="singleComment">
			<div class="commentInfoHeader row no-margin" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}">
				<div class="pull-left">
					{assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
					<img class="alignMiddle pull-left" alt="" width="48px" src="{if !empty($IMAGE_PATH)}{$IMAGE_PATH}{else}{vimage_path('DefaultUserIcon.png')}{/if}">
				</div>
				<div class="commentTitle row no-margin" id="{$COMMENT->getId()}">
					{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
					{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
					<div class="col-xs-8 pull-left commentorInfo">
						{assign var=COMMENTOR value=$COMMENT->getCommentedByModel()}
						<span class="commentorName pull-left"><strong>{$COMMENTOR->getName()}</strong></span><br> 
								{if $HIERARCHY}
									{assign var=RELATED_TO value=$COMMENT->get('related_to')}
							<input hidden="" class="related_to" name="related_to" value="{$RELATED_TO}"  />
							{assign var=RELATED_MODULE value=vtlib\Functions::getCRMRecordType($RELATED_TO)}
							<a href="index.php?module={$RELATED_MODULE}&view=Detail&record={$RELATED_TO}">
								<strong>
									{vtranslate($RELATED_MODULE,$RELATED_MODULE)}: 
								</strong>
								<strong class="commentRelatedTitle">
									{vtlib\Functions::getCRMRecordLabel($RELATED_TO)}
								</strong>
							</a>
						{/if}
						<div class="commentInfoContent ">
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
			<div class="commentActionsContainer row no-margin">
				{assign var="REASON_TO_EDIT" value=$COMMENT->get('reasontoedit')}
				<div class="editedStatus visible-lg-block"  name="editStatus">
					<div class="col-xs-6">
						<span class="{if empty($REASON_TO_EDIT)}hide{/if} col-xs-6 editReason">
							<p><small>[ {vtranslate('LBL_EDIT_REASON',$MODULE_NAME)} ] : <span  name="editReason" class="textOverflowEllipsis">{nl2br($REASON_TO_EDIT)}</span></small></p>
						</span>
						{if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
							<span class="{if !empty($REASON_TO_EDIT)} col-xs-6{/if}">
								<span class="pull-right">
									<p class="muted"><small><em>{vtranslate('LBL_MODIFIED',$MODULE_NAME)}</em></small>&nbsp;<small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getModifiedTime())}" class="commentModifiedTime">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getModifiedTime())}</small></p>
								</span>
							</span>
						{/if}
					</div>
				</div>
				<div class="commentActionsDiv">
					{assign var=COMMENTS_MODULE_MODEL value = Vtiger_Module_Model::getInstance('ModComments')}
					<div class="pull-right commentActions">
						{if $CHILDS_ROOT_PARENT_MODEL}
							{assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
						{/if}
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
						{assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
						{if $CHILD_COMMENTS_MODEL neq null and ($CHILDS_ROOT_PARENT_ID neq $PARENT_COMMENT_ID)}
							<span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
								<button type="button" class="btn btn-xs btn-info viewThread marginLeft5">
									<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
									<span class="glyphicon glyphicon-share-alt"></span>
								</button>
							</span>
							<span class="hide hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
								<a class="cursorPointer hideThread">
									<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
									<img class="alignMiddle" src="{vimage_path('downArrowSmall.png')}" />
								</a>
							</span>
						{elseif $CHILD_COMMENTS neq null and ($CHILDS_ROOT_PARENT_ID eq $PARENT_COMMENT_ID)}
							<span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
								<button type="button" class="btn btn-xs btn-info viewThread marginLeft5">
									<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
									<span class="glyphicon glyphicon-share-alt"></span>
								</button>
							</span>
							<span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
								<a class="cursorPointer hideThread">
									<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
									<img class="alignMiddle" src="{vimage_path('downArrowSmall.png')}" />
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

