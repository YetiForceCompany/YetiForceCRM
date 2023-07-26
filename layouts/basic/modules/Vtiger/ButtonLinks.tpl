{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-ButtonLinks -->
	{if empty($CLASS)}
		{assign var=CLASS value=''}
	{/if}
	{if !isset($BUTTON_VIEW)}
		{assign var=BUTTON_VIEW value=''}
	{/if}
	{if !isset($BREAKPOINT)}
		{assign var=BREAKPOINT value=''}
	{/if}
	{assign var=COUNT_LINKS value=count($LINKS)}
	<div class="c-btn-link {if ($COUNT_LINKS > 1 && $USER_MODEL->get('rowheight') eq 'narrow') && !isset($SKIP_GROUP)}btn-group-sm{elseif $COUNT_LINKS > 1 && !isset($SKIP_GROUP)}btn-group{/if} {$CLASS} {if !empty($CLASS) && $CLASS eq 'c-btn-link--responsive'}d-inline-block d-md-inline-flex{/if}">
		{foreach item=LINK from=$LINKS}
			{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW=$BUTTON_VIEW MODULE_NAME=$MODULE BREAKPOINT=$BREAKPOINT}
		{/foreach}
	</div>
	<!-- /tpl-Base-ButtonLinks -->
{/strip}
