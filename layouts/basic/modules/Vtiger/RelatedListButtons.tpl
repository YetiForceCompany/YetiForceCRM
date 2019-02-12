{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="col-md-12">
		<div class="related px-0 ml-0">
			<div class="">
				<ul class="nav nav-pills js-tabdrop">
					{foreach item=RELATED_LINK key=ITERATION from=$DETAILVIEW_LINKS['DETAILVIEWTAB']}
						<li class="c-tab--small c-tab--hover nav-item js-detail-tab baseLink mainNav{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL || ($ITERATION===0 && $SELECTED_TAB_LABEL==='LBL_RECORD_PREVIEW')} active{/if}" data-iteration="{$ITERATION}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-link-key="{$RELATED_LINK->get('linkKey')}" data-reference='{$RELATED_LINK->get('related')}' {if $RELATED_LINK->get('countRelated')}data-count="{$RELATED_LINK->get('countRelated')|intval}"{/if}>
							<a href="javascript:void(0);" class="nav-link u-text-ellipsis" title="{\App\Language::translate($RELATED_LINK->getLabel(),{$MODULE_NAME})}">
								{\App\Language::translate($RELATED_LINK->getLabel(),{$MODULE_NAME})}
								{if $RELATED_LINK->get('countRelated')}
									<span class="count badge badge-danger c-badge--md c-badge--top-right {$RELATED_LINK->get('badgeClass')}"></span>
								{/if}
							</a>
						</li>
					{/foreach}
					<li class="spaceRelatedList d-none">
					<li>
						{assign var="SHOW_RELATED_TAB_NAME" value=AppConfig::relation('SHOW_RELATED_MODULE_NAME')}
						{foreach item=RELATED_LINK key=ITERATION from=$DETAILVIEW_LINKS['DETAILVIEWRELATED']}
						{assign var="DETAILVIEWRELATEDLINKLBL" value= \App\Language::translate($RELATED_LINK->getLabel(), $RELATED_LINK->getRelatedModuleName())}
					<li {if !$SHOW_RELATED_TAB_NAME}data-content="{$DETAILVIEWRELATEDLINKLBL}" data-placement="top"{/if} class="c-tab--small c-tab--hover c-tab--gray js-detail-tab nav-item baseLink d-none float-left relatedNav {if !$SHOW_RELATED_TAB_NAME}js-popover-tooltip{/if}{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-js="popover | tabdrop" data-iteration="{$ITERATION}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-reference='{$RELATED_LINK->getRelatedModuleName()}' data-count="{AppConfig::relation('SHOW_RECORDS_COUNT')}">
						{* Assuming most of the related link label would be module name - we perform dual translation *}
						<a href="javascript:void(0);" class="nav-link u-text-ellipsis" title="{$DETAILVIEWRELATEDLINKLBL}">
							{if AppConfig::relation('SHOW_RELATED_ICON')}
								<span class="iconModule userIcon-{$RELATED_LINK->getRelatedModuleName()}{if $SHOW_RELATED_TAB_NAME} mr-1{/if}"></span>
							{/if}
							<span class="{if !$SHOW_RELATED_TAB_NAME}c-tab__text d-none{/if}">{$DETAILVIEWRELATEDLINKLBL}</span>
							{if AppConfig::relation('SHOW_RECORDS_COUNT')}
								<span class="count badge badge-danger c-badge--md c-badge--top-right"></span>
							{/if}
						</a>
					</li>
					{/foreach}
				</ul>
			</div>
		</div>
	</div>
{/strip}
