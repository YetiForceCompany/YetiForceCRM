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
	<!-- tpl-Base-CommentsListIteration -->
	{if !empty($CHILD_COMMENTS_MODEL)}
		<ul class="pl-2">
			{foreach item=COMMENT from=$CHILD_COMMENTS_MODEL}
				<li class="js-commentDetails commentDetails" data-js="container|append">
					{include file=\App\Layout::getTemplatePath('CommentThreadList.tpl') COMMENT=$COMMENT}
					{assign var=CHILD_COMMENTS value=$COMMENT->getChildComments()}
					{if !empty($CHILD_COMMENTS)}
						{include file=\App\Layout::getTemplatePath('CommentsListIteration.tpl') CHILD_COMMENTS_MODEL=$COMMENT->getChildComments()}
					{/if}
				</li>
			{/foreach}
		</ul>
	{/if}
	<!-- /tpl-Base-CommentsListIteration -->
{/strip}
