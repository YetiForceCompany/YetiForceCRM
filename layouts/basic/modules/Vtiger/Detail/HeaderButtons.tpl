{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-HeaderButtons d-flex flex-nowrap align-items-end justify-content-end my-1 o-header-toggle__detail js-btn-toolbar"
		data-js="container">
		<a class="btn btn-primary d-md-none my-auto o-header-toggle__actions-btn js-header-toggle__actions-btn"
			href="#" data-js="click" role="button"
			aria-expanded="false" aria-controls="o-view-actions__container">
			<span class="fas fa-ellipsis-h fa-fw"
				title="{\App\Language::translate('LBL_ACTION_MENU')}"></span>
		</a>
		<div class="my-auto o-header-toggle__actions js-header-toggle__actions d-md-flex float-right flex-md-row flex-wrap"
			id="o-view-actions__container">
			{if isset($DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL'])}
				<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap d-block d-md-flex">
					{include file=\App\Layout::getTemplatePath('ButtonLinks.tpl', $MODULE_NAME) LINKS=$DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL'] BUTTON_VIEW='detailViewAdditional' BREAKPOINT='md' MODULE=$MODULE_NAME CLASS='c-btn-link--responsive'}
				</div>
			{/if}
			{if isset($DETAILVIEW_LINKS['DETAIL_VIEW_BASIC'])}
				<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap d-block d-md-flex">
					{include file=\App\Layout::getTemplatePath('ButtonLinks.tpl', $MODULE_NAME) LINKS=$DETAILVIEW_LINKS['DETAIL_VIEW_BASIC'] BUTTON_VIEW='detailViewBasic' BREAKPOINT='md' MODULE=$MODULE_NAME CLASS='c-btn-link--responsive'}
				</div>
			{/if}
			{if isset($DETAILVIEW_LINKS['DETAIL_VIEW_EXTENDED'])}
				<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap d-block d-md-flex">
					{include file=\App\Layout::getTemplatePath('ButtonLinks.tpl', $MODULE_NAME) LINKS=$DETAILVIEW_LINKS['DETAIL_VIEW_EXTENDED'] BUTTON_VIEW='detailViewExtended' BREAKPOINT='md' MODULE=$MODULE_NAME CLASS='c-btn-link--responsive'}
				</div>
			{/if}
		</div>
	</div>
{/strip}
