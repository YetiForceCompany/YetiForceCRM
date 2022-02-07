{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-DashBoard-SystemMonitoring  -->
	<div class="mb-3 d-flex flex-wrap mr-n3">
		{foreach from=$SYSTEM_MONITORING item=ITEM}
			{assign var="TRANSLATION" value=\App\Language::translatePluralized($ITEM['LABEL'], $QUALIFIED_MODULE, $ITEM['VALUE'])}
			<div class="dashboardWidget px-1 pt-1 pb-4 mt-3 mr-3 flex-grow-1">
				<div class="pl-3 d-flex flex-nowrap justify-content-center">
					<div class="d-flex u-fs-50px pb-1 mr-2">
						<span class="mt-auto mb-2 {$ITEM['ICON']}"></span>
					</div>
					<div class="display-3 u-font-weight-350" {if strlen($ITEM['VALUE']) > 3}title="{$ITEM['VALUE']}">999+{else}>{$ITEM['VALUE']}{/if}</div>
				</div>
				<div class="px-3">
					{include file=\App\Layout::getTemplatePath('DashBoard/WidgetTitle.tpl', $QUALIFIED_MODULE) CLASS='text-center' TITLE=$TRANSLATION}
					{if not empty($ITEM['HREF'])}
						<a href="{$ITEM['HREF']}" class="btn btn-dark btn-block mt-2">{\App\Language::translate('LBL_MORE')}</a>
					{/if}
				</div>
			</div>
		{/foreach}
	</div>
	<!-- /tpl-Settings-Base-DashBoard-SystemMonitoring  -->
{/strip}
