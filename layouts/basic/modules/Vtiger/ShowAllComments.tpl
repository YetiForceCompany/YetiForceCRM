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
{* Change to this also refer: RecentComments.tpl *}
{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
<input type="hidden" id="currentComment" value="{if !empty($CURRENT_COMMENT)}{$CURRENT_COMMENT->getId()}{/if}">
<div class="col-md-12 row no-margin commentsBar paddingLRZero">
	{if $COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
		<div class="commentTitle col-xs-12 paddingTop10" >
			<div class="addCommentBlock">
				<div class="input-group">
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-comment"></span>
					</span>
					<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" name="commentcontent" class="commentcontent form-control" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
				</div>
				<button class="btn btn-success marginTop10 saveComment pull-right" type="button" data-mode="add">
					<span class="visible-xs-inline-block glyphicon glyphicon-ok"></span>
					<strong class="hidden-xs">{vtranslate('LBL_POST', $MODULE_NAME)}</strong>
				</button>
			</div>
		</div>
	{/if}
</div>
{if count($HIERARCHY_LIST) != 1}
	<div class="col-md-12 row commentsHeader marginTop10">
		<div class="col-md-4"> </div>
		<div class="col-md-4">
			<div class="input-group">
			  <span class="input-group-addon" id="commentSearchAddon">
				  <span class="glyphicon glyphicon-search" aria-hidden="true"></span> 
			  </span>
			  <input type="text" class="form-control commentSearch" placeholder="{vtranslate('LBL_COMMENTS_SEARCH','ModComments')}" aria-describedby="commentSearchAddon">
			</div>
		</div>
		<div class="col-md-4">
			<select class="chzn-select form-control commentsHierarchy" multiple>
				{foreach key=NAME item=LABEL from=$HIERARCHY_LIST}
					<option value="{$NAME}" {if in_array($NAME, $HIERARCHY)}selected{/if}>{vtranslate($LABEL, 'ModComments')}</option>
				{/foreach}
			</select>
		</div>
	</div>
{/if}	
<div class="commentContainer">
	<div class="commentsList commentsBody  col-md-12 paddingLRZero">
	{include file='CommentsList.tpl'|@vtemplate_path COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL}
	</div>
	<div class="hide basicAddCommentBlock marginTop10 marginBottom10px">
		<div class="row">
			<div class="col-md-12">
				<div class="input-group">
					<span class="input-group-addon" >
						<span class="glyphicon glyphicon-comment"></span>
					</span>
					<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}"></textarea>
				</div>
				<button class="cursorPointer marginTop10 closeCommentBlock btn btn-warning pull-right cancel" type="reset">
					<span class="visible-xs-inline-block glyphicon glyphicon-remove"></span>
					<strong class="hidden-xs">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</strong>
				</button>
				<button class="btn btn-success marginTop10 saveComment pull-right" type="button" data-mode="add">
					<span class="visible-xs-inline-block glyphicon glyphicon-ok"></span>
					<strong class="hidden-xs">{vtranslate('LBL_POST', $MODULE_NAME)}</strong>
				</button>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="hide basicEditCommentBlock" >
		<div class="row">
			<div class="col-md-12 marginTop10 marginBottom10px">
				<input type="text" name="reasonToEdit" title="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level form-control"/>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 marginBottom10px">
				<div class="input-group">
					<span class="input-group-addon" >
						<span class="glyphicon glyphicon-comment"></span>
					</span>
					<textarea rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" class="form-control commentcontenthidden fullWidthAlways" name="commentcontent" title="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" ></textarea>
				</div>
				<button class="cursorPointer marginTop10 closeCommentBlock btn btn-warning pull-right cancel" type="reset">
					<span class="visible-xs-inline-block glyphicon glyphicon-remove"></span>
					<strong class="hidden-xs">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</strong>
				</button>
				<button class="btn btn-success marginTop10 saveComment pull-right" type="button" data-mode="edit">
					<span class="visible-xs-inline-block glyphicon glyphicon-ok"></span>
					<strong class="hidden-xs">{vtranslate('LBL_POST', $MODULE_NAME)}</strong>
				</button>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
{/strip}
