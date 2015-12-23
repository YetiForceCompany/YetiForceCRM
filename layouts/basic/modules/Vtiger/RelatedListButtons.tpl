{strip}
	<div class="col-md-12">
		<div class="related paddingLRZero marginLeftZero">
			<div class="">
				<ul class="nav nav-pills">
					{foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWTAB']}
						<li class="mainNav{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-link-key="{$RELATED_LINK->get('linkKey')}"  data-reference="{$RELATED_LINK->get('related')}" {if $RELATED_LINK->get('countRelated')}data-count="{AppConfig::relation('SHOW_RECORDS_COUNT')}"{/if}>
							<a href="javascript:void(0);" class="textOverflowEllipsis" style="width:auto" title="{vtranslate($RELATED_LINK->getLabel(),{$MODULE_NAME})}">
								<strong class="pull-left">{vtranslate($RELATED_LINK->getLabel(),{$MODULE_NAME})}</strong>
								{if $RELATED_LINK->get('countRelated') && AppConfig::relation('SHOW_RECORDS_COUNT')}
									<span class="count badge pull-right">-</span>
								{/if}
							</a>
						</li>
					{/foreach}
					<li class="spaceRelatedList hide"><li>
					<li role="presentation" class="dropdown pull-right hide">
						<a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="true" aria-expanded="true">
							<strong>{vtranslate('LBL_MORE',$MODULE)}</strong> <span class="caret"></span>
						</a>
						<ul class="dropdown-menu pull-right">
							{foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWTAB']}
								<li class="mainNav{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-link-key="{$RELATED_LINK->get('linkKey')}"  data-reference="{$RELATED_LINK->get('related')}" {if $RELATED_LINK->get('countRelated')}data-count="{AppConfig::relation('SHOW_RECORDS_COUNT')}"{/if}>
									<a href="javascript:void(0);" class="textOverflowEllipsis" style="width:auto" title="{vtranslate($RELATED_LINK->getLabel(),{$MODULE_NAME})}">
										<strong class="pull-left">{vtranslate($RELATED_LINK->getLabel(),{$MODULE_NAME})}</strong>
										{if $RELATED_LINK->get('countRelated') && AppConfig::relation('SHOW_RECORDS_COUNT')}
											<span class="count badge pull-right">-</span>
										{/if}
									</a>
								</li>
							{/foreach}
							{foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWRELATED']}
								<li class="hide relatedNav{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-reference="{$RELATED_LINK->get('relatedModuleName')}" data-count="{AppConfig::relation('SHOW_RECORDS_COUNT')}">
									{* Assuming most of the related link label would be module name - we perform dual translation *}
									{assign var="DETAILVIEWRELATEDLINKLBL" value= vtranslate($RELATED_LINK->getLabel(), $RELATED_LINK->getRelatedModuleName())}
									<a href="javascript:void(0);" class="textOverflowEllipsis moduleColor_{$RELATED_LINK->getLabel()}" style="width:auto" title="{$DETAILVIEWRELATEDLINKLBL}">
										<span class="iconModule userIcon-{$RELATED_LINK->get('relatedModuleName')} pull-left">&nbsp;&nbsp;</span>
										<strong class="pull-left">{$DETAILVIEWRELATEDLINKLBL}</strong>
										{if AppConfig::relation('SHOW_RECORDS_COUNT')}
											<span class="count badge pull-right">0</span>
										{/if}
									</a>
								</li>
							{/foreach}
						</ul>
					</li>
					{foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWRELATED']}
						{assign var="DETAILVIEWRELATEDLINKLBL" value= vtranslate($RELATED_LINK->getLabel(), $RELATED_LINK->getRelatedModuleName())}
							<li {if !AppConfig::relation('SHOW_RELATED_MODULE_NAME')}data-content="{$DETAILVIEWRELATEDLINKLBL}" data-placement="top"{/if} class="hide pull-left relatedNav {if !AppConfig::relation('SHOW_RELATED_MODULE_NAME')}popoverTooltip{/if}{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-reference="{$RELATED_LINK->get('relatedModuleName')}" data-count="{AppConfig::relation('SHOW_RECORDS_COUNT')}">
							{* Assuming most of the related link label would be module name - we perform dual translation *}
							<a href="javascript:void(0);"  class="textOverflowEllipsis moduleColor_{$RELATED_LINK->getLabel()}">
								<span class="iconModule userIcon-{$RELATED_LINK->get('relatedModuleName')} pull-left">&nbsp;</span>
								{if AppConfig::relation('SHOW_RELATED_MODULE_NAME')}
									<strong class="pull-left">{$DETAILVIEWRELATEDLINKLBL}</strong>
								{/if}
								{if AppConfig::relation('SHOW_RECORDS_COUNT')}
									<span class="count badge pull-right">0</span>
								{/if}
							</a>
						</li>
					{/foreach}
				</ul>
			</div>
		</div>
	</div>
{/strip}
