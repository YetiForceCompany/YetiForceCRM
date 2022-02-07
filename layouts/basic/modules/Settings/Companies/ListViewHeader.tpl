{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Companies-ListViewHeader -->
	<div class="">
		<div class="o-breadcrumb widget_header row">
			<div class="col-9 d-flex">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			{assign var=LAST_CHECK_TIME value=\App\YetiForce\Register::getLastCheckTime()}
			{if !empty($LAST_CHECK_TIME)}
				<div class="col-md-3 ml-2 ml-md-0 d-flex justify-content-end align-items-center">
					<span class="mr-2">
						<strong>{\App\Fields\DateTime::formatToViewDate($LAST_CHECK_TIME)}</strong>
					</span>
					<span class="js-popover-tooltip u-cursor-pointer" data-js="popover" data-placement="top" data-content="{\App\Language::translate('LBL_LAST_SCAN_DATE', $QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</div>
			{else}
				{assign var=LAST_CHECK_ERROR value=\App\YetiForce\Register::getLastCheckError()}
				<div class="col-md-3 ml-2 ml-md-0 d-flex justify-content-end align-items-center">
					{if !empty($LAST_CHECK_ERROR)}
						<span class="mr-2">
							<strong>{\App\Language::translateArgs('LBL_ERROR_MESSAGE', $QUALIFIED_MODULE, \App\Language::translate($LAST_CHECK_ERROR, 'Other:Exceptions'))}</strong>
						</span>
					{/if}
					<span class="js-popover-tooltip u-cursor-pointer" data-js="popover" data-placement="top" data-content="{\App\Language::translate('LBL_LAST_SCAN_ERROR', $QUALIFIED_MODULE)}">
						<span class="fas fa-question-circle"></span>
					</span>
				</div>
			{/if}
		</div>
		<div class="listViewActionsDiv row mt-2 mb-2">
			<div class="{if !empty($SUPPORTED_MODULE_MODELS)}col-md-5{else}col-md-8{/if} btn-toolbar">
				{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
					{if $LINK->getLabel()}
						{assign var="LABEL" value=\App\Language::translate($LINK->getLabel(), $QUALIFIED_MODULE)}
					{/if}
					<button type="button" title="{if $LINK->getLabel()}{$LABEL}{/if}"
						class="btn{if $LINK->getClassName()} {$LINK->getClassName()}{else} btn-light{/if} {if $LINK->get('modalView')}js-show-modal{/if}"
						{if $LINK->getUrl()}
							{if stripos($LINK->getUrl(), 'javascript:')===0} onclick='{$LINK->getUrl()|substr:strlen("javascript:")};'
							{else} onclick='window.location.href = "{$LINK->getUrl()}"' 
							{/if}
						{/if}
						{if $LINK->get('linkdata') neq '' && is_array($LINK->get('linkdata'))}
							{foreach from=$LINK->get('linkdata') key=NAME item=DATA}
								data-{$NAME}="{$DATA}"
							{/foreach}
						{/if}>
						{if $LINK->get('linkicon')}
							<span class="{$LINK->get('linkicon')}"></span>
						{/if}
						{if $LINK->getLabel() && $LINK->get('showLabel') eq 1}
							&nbsp;
							<strong>{$LABEL}</strong>
						{/if}
					</button>
				{/foreach}
			</div>
			<div class="col-12 col-sm-4 d-flex flex-row-reverse">
				{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="listViewContentDiv listViewPageDiv" id="listViewContents">
			<!-- /tpl-Settings-ListViewHeader -->

{/strip}
