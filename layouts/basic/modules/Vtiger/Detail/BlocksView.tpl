{*
<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-BlocksView -->
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
		{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
		{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}
			{continue}
		{/if}
		{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
		{assign var=IS_DYNAMIC value=$BLOCK->isDynamic()}
		{assign var=BLOCK_ICON value=$BLOCK->get('icon')}
		{include file=\App\Layout::getTemplatePath('Detail/BlockView.tpl', $MODULE_NAME)}
	{/foreach}
	<!-- /tpl-Base-Detail-BlocksView -->
{/strip}
