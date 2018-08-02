{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<ul class="paddingLRZero Comments">
		{if $CURRENT_COMMENT}
			{assign var=CHILDS_ROOT_PARENT_MODEL value=$CURRENT_COMMENT}
			{assign var=CURRENT_COMMENT_PARENT_MODEL value=$CURRENT_COMMENT->getParentCommentModel()}
			{while $CURRENT_COMMENT_PARENT_MODEL neq false}
				{assign var=TEMP_COMMENT value=$CURRENT_COMMENT_PARENT_MODEL}
				{assign var=CURRENT_COMMENT_PARENT_MODEL value=$CURRENT_COMMENT_PARENT_MODEL->getParentCommentModel()}
				{if $CURRENT_COMMENT_PARENT_MODEL eq false}
					{assign var=CHILDS_ROOT_PARENT_MODEL value=$TEMP_COMMENT}
				{/if}	
			{/while}
		{/if}
		{if is_array($PARENT_COMMENTS)}
			{foreach key=Index item=COMMENT from=$PARENT_COMMENTS}
				{assign var=PARENT_COMMENT_ID value=$COMMENT->getId()}
				<li class="commentDetails">
					{include file=\App\Layout::getTemplatePath('Comment.tpl') COMMENT=$COMMENT COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL}
					{if $CHILDS_ROOT_PARENT_MODEL}
						{if $CHILDS_ROOT_PARENT_MODEL->getId() eq $PARENT_COMMENT_ID}		
							{assign var=CHILD_COMMENTS_MODEL value=$CHILDS_ROOT_PARENT_MODEL->getChildComments()}
							{include file=\App\Layout::getTemplatePath('CommentsListIteration.tpl') CHILD_COMMENTS_MODEL=$CHILD_COMMENTS_MODEL}
						{/if}
					{/if}
				</li>	
			{/foreach}
		{else}
			{include file=\App\Layout::getTemplatePath('Comment.tpl') COMMENT=$PARENT_COMMENTS}
		{/if}
	</ul>
{/strip}
