{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Base-DashBoard-SystemMonitoring px-3 mb-4 u-columns-width-200px-rem u-columns-count-5">
		{function BOX LABEL='' VALUE='' HREF='' ICON=''}
			{assign var="TRANSLATION" value=\App\Language::translatePluralized($LABEL, $QUALIFIED_MODULE, $VALUE)}
			<div class="dashboardWidget px-1 pt-3 pb-4 u-columns__item mb-n1 mt-3 d-inline-block">
				<div class="pl-3 d-flex flex-nowrap justify-content-center">
					<div class="d-flex u-font-size-50px pb-1 mr-2">
						<span class="mt-auto mb-2 {$ICON}"></span>
					</div>
					<div class="display-3 u-font-weight-350" {if strlen($VALUE) > 3}title="{$VALUE}">999+{else}>{$VALUE}{/if}</div>
				</div>
				<div class="px-3">
				{include file=\App\Layout::getTemplatePath('DashBoard/WidgetTitle.tpl', $QUALIFIED_MODULE) CLASS='text-center' TITLE=$TRANSLATION}
				{if not empty($HREF)}
					<a href="{$HREF}" class="btn btn-dark btn-block mt-2">{\App\Language::translate('LBL_MORE')}</a>
				{/if}
				</div>
			</div>
		{/function}
		{foreach from=$SYSTEM_MONITORING item=ITEM}
			{BOX LABEL=$ITEM['LABEL'] VALUE=$ITEM['VALUE'] HREF=$ITEM['HREF'] ICON=$ITEM['ICON']}
		{/foreach}
	</div>
{/strip}
