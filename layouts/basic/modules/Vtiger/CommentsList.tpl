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
	{if !empty($CHILD_COMMENTS)}
		<div class="ml-4">
	{/if}
	{if !empty($PARENT_COMMENTS) && !empty($SHOW_CHILD_COMMENTS)}
		{foreach key=CURRENT_COMMENT_KEY item=CURRENT_COMMENT from=$PARENT_COMMENTS}
			{include file=\App\Layout::getTemplatePath('Comments.tpl') PARENT_COMMENTS=$CURRENT_COMMENT CURRENT_COMMENT=$CURRENT_COMMENT}
		{/foreach}
	{else}
		{include file=\App\Layout::getTemplatePath('Comments.tpl') PARENT_COMMENTS=$PARENT_COMMENTS CURRENT_COMMENT=$CURRENT_COMMENT}
	{/if}
	<div class="tpl-CommentsList summaryWidgetContainer noCommentsMsgContainer {if !empty($PARENT_COMMENTS)}d-none{/if}">
		<p class="textAlignCenter"> {\App\Language::translate('LBL_NO_COMMENTS',$MODULE_NAME)}</p>
	</div>
	{if !empty($CHILD_COMMENTS)}
		</div>
	{/if}
{/strip}
