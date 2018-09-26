{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-HeaderButtons d-flex flex-nowrap align-items-end justify-content-end my-1 js-popover-tooltip">
		{if $DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL']}
			<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap d-block d-md-flex">
				{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL']}
					{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='detailViewAdditional' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
				{/foreach}
			</div>
		{/if}
		{if $DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']}
			<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap d-block d-md-flex">
				{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']}
					{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='detailViewBasic' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
				{/foreach}
			</div>
		{/if}
		{if $DETAILVIEW_LINKS['DETAIL_VIEW_EXTENDED']}
			<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap d-block d-md-flex">
				{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_EXTENDED']}
					{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='detailViewExtended' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
				{/foreach}
			</div>
		{/if}
	</div>
{/strip}
