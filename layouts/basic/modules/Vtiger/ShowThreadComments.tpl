{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
{if !empty($PARENT_COMMENTS)}
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
	<ul class="liStyleNone">
		{if is_array($PARENT_COMMENTS)}
			{foreach key=Index item=COMMENT from=$PARENT_COMMENTS}
				{assign var=PARENT_COMMENT_ID value=$COMMENT->getId()}
				
				{if $CHILDS_ROOT_PARENT_MODEL}
					{if $CHILDS_ROOT_PARENT_MODEL->getId() eq $PARENT_COMMENT_ID}	
						<li class="commentDetails">
						{include file='Comment.tpl'|@vtemplate_path COMMENT=$COMMENT COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL}
						{assign var=CHILD_COMMENTS_MODEL value=$CHILDS_ROOT_PARENT_MODEL->getChildComments()}
						{include file='CommentsListIteration.tpl'|@vtemplate_path CHILD_COMMENTS_MODEL=$CHILD_COMMENTS_MODEL}
						</li>
					{/if}
				
				{/if}
					
			{/foreach}
		{else}
			{include file='Comment.tpl'|@vtemplate_path COMMENT=$PARENT_COMMENTS}
		{/if}
	</ul>
{else}
	{include file="NoComments.tpl"|@vtemplate_path}
{/if}
{/strip}
