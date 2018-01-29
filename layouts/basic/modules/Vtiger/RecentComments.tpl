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
	{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
	{* Change to this also refer: AddCommentForm.tpl *}
	<div class="commentContainer recentComments">
		<div class="commentTitle">
			{if !$IS_READ_ONLY && $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
				<div class="addCommentBlock">
					<div class="input-group">
						<span class="input-group-addon" >
							<span class="fas fa-comments"></span>
						</span>
						<textarea name="commentcontent" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="commentcontent form-control" title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" ></textarea>
					</div>
					<button class="btn btn-success detailViewSaveComment  marginTop10 pull-right" type="button" data-mode="add">
						<span class="visible-xs-inline-block glyphicon glyphicon-ok"></span>
						<strong class="hidden-xs">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</strong>
					</button>
					<div class="clearfix"></div>
				</div>
			{/if}
		</div>
		<hr><br />
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
												{if $IMAGE_PATH}
													<img class="userImage pull-left" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE_PATH))}" >
												{else}	
													<span class="fas fa-user userImage pull-left" aria-hidden="true"></span>
												{/if}
											</div>
											<div class="col-xs-8 commentorInfo">
												{assign var=COMMENTOR value=$COMMENT->getCommentedByModel()}
												<span class="commentorName"><strong>{$COMMENTOR->getName()}</strong></span>
												<div class="commentInfoContent">
													{$COMMENT->getDisplayValue('commentcontent')}
												</div>
											</div>
											<div class="inner">
												<span class="pull-right paddingRight15">
													<p class="muted"><small>{\App\Fields\DateTime::formatToViewDate($COMMENT->getCommentedTime())}</small></p>
												</span>
												<div class="clearfix"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="commentActionsContainer">
									{assign var="REASON_TO_EDIT" value=$COMMENT->getDisplayValue('reasontoedit')}
									<div class="pull-left {if empty($REASON_TO_EDIT)}hide {/if}editStatus"  name="editStatus">
										<span class="pull-left paddingRight10 visible-lg-block">
											<p class="muted">
												<small>
													[ {\App\Language::translate('LBL_EDIT_REASON',$MODULE_NAME)} ] :
													<span  name="editReason" class="textOverflowEllipsis">{nl2br($REASON_TO_EDIT)}</span>
												</small>
											</p>
										</span>
									</div>
									{if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
										<div class="clearfix"></div>
										<span class="pull-left visible-lg-block">
											<p class="muted pull-right">
												<small><em>{\App\Language::translate('LBL_MODIFIED',$MODULE_NAME)}</em></small>&nbsp;
												<small class="commentModifiedTime">{\App\Fields\DateTime::formatToViewDate($COMMENT->getModifiedTime())}</small>
											</p>
										</span>
									{/if}
									{if !$IS_READ_ONLY}
										<div class="pull-right commentActions">
											{if $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
												<span>
													<button type="button" class="btn btn-xs btn-success replyComment feedback">
														<span class="fa fa-share" aria-hidden="true"></span>&nbsp;
														{\App\Language::translate('LBL_REPLY',$MODULE_NAME)}
													</button>
													{if \App\Privilege::isPermitted('ModComments','EditableComments') && $CURRENTUSER->getId() eq $COMMENT->get('userid')}
														<button type="button" class="btn btn-xs btn-primary editComment feedback marginLeft5">
															<span class="fas fa-pencil-alt" aria-hidden="true"></span>&nbsp;
															{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}
														</button>
													{/if}
												</span>
											{/if}
											<span>
												{if $PARENT_COMMENT_MODEL neq false or $CHILD_COMMENTS_MODEL neq null}
													<button type="button" class="btn btn-xs btn-info detailViewThread marginLeft5">{\App\Language::translate('LBL_VIEW_THREAD',$MODULE_NAME)}</button>
												{/if}
											</span>
										</div>
									{/if}
									<div class="clearfix"></div>

								</div>
							</div>
						</div>
					</div>
				{/foreach}
			{else}
				{include file=\App\Layout::getTemplatePath('NoComments.tpl')}
			{/if}
		</div>
		{if !$IS_READ_ONLY && $PAGING_MODEL->isNextPageExists()}
			<div class="row">
				<div class="pull-right">
					<a href="javascript:void(0)" class="moreRecentComments btn btn-xs btn-info marginTop5 marginRight15">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}..</a>
				</div>
			</div>
		{/if}
		{if !$IS_READ_ONLY}
			<div class="hide basicAddCommentBlock marginTop10 marginBottom10px">
				<div class="row">
					<div class="col-md-12">
						<div class="input-group">
							<span class="input-group-addon" >
								<span class="fas fa-comments"></span>
							</span>
							<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
						</div>
						<button class="cursorPointer closeCommentBlock marginTop10 btn btn-warning pull-right cancel" type="reset">
							<span class="visible-xs-inline-block fa fa-times"></span>
							<strong class="hidden-xs">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
						</button>
						<button class="btn btn-success saveComment marginTop10 pull-right" type="button" data-mode="add">
							<span class="visible-xs-inline-block glyphicon glyphicon-ok"></span>
							<strong class="hidden-xs">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</strong>
						</button>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="hide basicEditCommentBlock" >
				<div class="row">
					<div class="col-md-12 marginTop10 marginBottom10px">
						<input type="text" name="reasonToEdit" title="{\App\Language::translate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level form-control" />
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 marginBottom10px">
						<div class="input-group">
							<span class="input-group-addon" >
								<span class="fas fa-comments"></span>
							</span>
							<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{\App\Language::translate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" ></textarea>
						</div>
						<button class="cursorPointer closeCommentBlock marginTop10 btn btn-warning pull-right cancel" type="reset">
							<span class="visible-xs-inline-block fa fa-times"></span>
							<strong class="hidden-xs">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</strong>
						</button>
						<button class="btn btn-success saveComment marginTop10 pull-right" type="button" data-mode="edit">
							<span class="visible-xs-inline-block glyphicon glyphicon-ok"></span>
							<strong class="hidden-xs">{\App\Language::translate('LBL_POST', $MODULE_NAME)}</strong>
						</button>
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		{/if}
	</div>
{/strip}
