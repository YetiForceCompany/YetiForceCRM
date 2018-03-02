{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="col-md-12">
		<div class="related paddingLRZero marginLeftZero">
			<div class="">
				<ul class="nav nav-pills">
					{foreach item=RELATED_LINK key=ITERATION from=$DETAILVIEW_LINKS['DETAILVIEWTAB']}
						<li class="nav-item baseLink mainNav{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-iteration="{$ITERATION}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-link-key="{$RELATED_LINK->get('linkKey')}"  data-reference='{$RELATED_LINK->get('related')}' {if $RELATED_LINK->get('countRelated')}data-count="{$RELATED_LINK->get('countRelated')|intval}"{/if}>
							<a href="javascript:void(0);" class="nav-link textOverflowEllipsis" style="width:auto" title="{\App\Language::translate($RELATED_LINK->getLabel(),{$MODULE_NAME})}">
								<strong class="float-left">{\App\Language::translate($RELATED_LINK->getLabel(),{$MODULE_NAME})}</strong>
								{if $RELATED_LINK->get('countRelated')}
									<span class="count badge float-right {$RELATED_LINK->get('badgeClass')}">0</span>
								{/if}
							</a>
						</li>
					{/foreach}
					<li class="spaceRelatedList d-none"><li>
						{foreach item=RELATED_LINK key=ITERATION from=$DETAILVIEW_LINKS['DETAILVIEWRELATED']}
							{assign var="DETAILVIEWRELATEDLINKLBL" value= \App\Language::translate($RELATED_LINK->getLabel(), $RELATED_LINK->getRelatedModuleName())}
						<li {if !AppConfig::relation('SHOW_RELATED_MODULE_NAME')}data-content="{$DETAILVIEWRELATEDLINKLBL}" data-placement="top"{/if} class="nav-item baseLink d-none float-left relatedNav {if !AppConfig::relation('SHOW_RELATED_MODULE_NAME')}popoverTooltip{/if}{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-iteration="{$ITERATION}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-reference='{$RELATED_LINK->getRelatedModuleName()}' data-count="{AppConfig::relation('SHOW_RECORDS_COUNT')}">
							{* Assuming most of the related link label would be module name - we perform dual translation *}
							<a href="javascript:void(0);"  class="nav-link textOverflowEllipsis" title="{$DETAILVIEWRELATEDLINKLBL}">
								{if AppConfig::relation('SHOW_RELATED_ICON')}
									<span class="iconModule userIcon-{$RELATED_LINK->getRelatedModuleName()} float-left">&nbsp;</span>
								{/if}
								{if AppConfig::relation('SHOW_RELATED_MODULE_NAME')}
									<strong class="float-left">{$DETAILVIEWRELATEDLINKLBL}</strong>
								{/if}
								{if AppConfig::relation('SHOW_RECORDS_COUNT')}
									<span class="count badge float-right">0</span>
								{/if}
							</a>
						</li>
					{/foreach}
					<li role="presentation" class="dropdown float-right d-none">
						<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="true" aria-expanded="true">
							<strong>{\App\Language::translate('LBL_MORE',$MODULE)}</strong> <span class="caret"></span>
						</a>
						<ul class="dropdown-menu float-right">
							{foreach item=RELATED_LINK key=ITERATION from=$DETAILVIEW_LINKS['DETAILVIEWTAB']}
								<li class="mainNav{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-iteration="{$ITERATION}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-link-key="{$RELATED_LINK->get('linkKey')}"  data-reference='{$RELATED_LINK->get('related')}' {if $RELATED_LINK->get('countRelated')}data-count="{$RELATED_LINK->get('countRelated')|intval}"{/if}>
									<a href="javascript:void(0);" class="textOverflowEllipsis" style="width:auto" title="{\App\Language::translate($RELATED_LINK->getLabel(),{$MODULE_NAME})}">
										<strong class="float-left">{\App\Language::translate($RELATED_LINK->getLabel(),{$MODULE_NAME})}</strong>
										{if $RELATED_LINK->get('countRelated')}
											<span class="count badge float-right {$RELATED_LINK->get('badgeClass')}">-</span>
										{/if}
									</a>
								</li>
							{/foreach}
							{foreach item=RELATED_LINK key=ITERATION from=$DETAILVIEW_LINKS['DETAILVIEWRELATED']}
								<li class="d-none relatedNav{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-iteration="{$ITERATION}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-reference='{$RELATED_LINK->get('relatedModuleName')}' data-count="{AppConfig::relation('SHOW_RECORDS_COUNT')}">
									{* Assuming most of the related link label would be module name - we perform dual translation *}
									{assign var="DETAILVIEWRELATEDLINKLBL" value= \App\Language::translate($RELATED_LINK->getLabel(), $RELATED_LINK->getRelatedModuleName())}
									<a href="javascript:void(0);" class="textOverflowEllipsis float-left" style="width:100%" title="{$DETAILVIEWRELATEDLINKLBL}">
										{if AppConfig::relation('SHOW_RELATED_ICON')}
											<span class="iconModule userIcon-{$RELATED_LINK->getRelatedModuleName()} float-left">&nbsp;&nbsp;</span>
										{/if}
										<strong class="float-left">{$DETAILVIEWRELATEDLINKLBL}</strong>
										{if AppConfig::relation('SHOW_RECORDS_COUNT')}
											<span class="count badge float-right">0</span>
										{/if}
									</a>
								</li>
							{/foreach}
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</div>
{/strip}
