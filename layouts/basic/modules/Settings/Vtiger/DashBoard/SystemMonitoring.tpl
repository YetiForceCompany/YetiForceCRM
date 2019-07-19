{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Base-DashBoard-SystemMonitoring px-3 mb-4 u-columns-width-200px-rem u-columns-count-5">
		{function BOX LABEL='' VALUE='' HREF='' IMG=''}
			{assign var="TRANSLATION" value=\App\Language::translatePluralized($LABEL, $QUALIFIED_MODULE, $VALUE)}
			<div class="dashboardWidget px-1 pt-3 pb-4 u-columns__item mb-n1 mt-3 d-inline-block">
				<div class="pl-3 d-flex flex-nowrap">
					<div class="d-flex align-items-center">
						{if isset($IMG)}
							<img src="{$IMG}" class="grow thumbnail-image card-img-top intrinsic-item"
								alt="{$TRANSLATION}" title="{$TRANSLATION}" />
						{else}
							<div class="product-no-image">
									<span class="fa-stack fa-2x product-no-image">
											<i class="fas fa-camera fa-stack-1x"></i>
											<i class="fas fa-ban fa-stack-2x"></i>
									</span>
							</div>
						{/if}
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
			{if isset($ITEM['IMG'])}
				{assign var="IMG" value=$ITEM['IMG']}
			{else}
				{assign var="IMG" value=null}
			{/if}
			{BOX LABEL=$ITEM['LABEL'] VALUE=$ITEM['VALUE'] HREF=$ITEM['HREF'] IMG=$IMG}
		{/foreach}
	</div>
{/strip}
