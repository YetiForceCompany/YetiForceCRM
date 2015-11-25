{strip}
	<div class="col-md-12">
		<div class="related paddingLRZero marginLeftZero">
			<div class="">
				<ul class="nav nav-pills">
					{foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWTAB']}
						<li class="mainNav{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-link-key="{$RELATED_LINK->get('linkKey')}"  data-reference="{$RELATED_LINK->get('related')}">
							<a href="javascript:void(0);" class="textOverflowEllipsis" style="width:auto" title="{vtranslate($RELATED_LINK->getLabel(),{$MODULE_NAME})}"><strong>{vtranslate($RELATED_LINK->getLabel(),{$MODULE_NAME})}</strong></a>
						</li>
					{/foreach}
					{assign var=COUNTER value=0}
					{foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWRELATED']}
						{assign var=COUNTER value=$COUNTER+1}
						{if $COUNTER == 5}
							<li role="presentation" class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="true" aria-expanded="true">
									<strong>{vtranslate('LBL_MORE',$MODULE)}</strong> <span class="caret"></span>
								</a>
								<ul class="dropdown-menu">
								{/if}
								<li class="relatedNav{if $RELATED_LINK->getLabel()==$SELECTED_TAB_LABEL} active{/if}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}" data-label-key="{$RELATED_LINK->getLabel()}" data-reference="{$RELATED_LINK->get('relatedModuleName')}" data-count="{vglobal('showRecordsCount')}">
									{* Assuming most of the related link label would be module name - we perform dual translation *}
									{assign var="DETAILVIEWRELATEDLINKLBL" value= vtranslate($RELATED_LINK->getLabel(), $RELATED_LINK->getRelatedModuleName())}
									<a href="javascript:void(0);" class="textOverflowEllipsis moduleColor_{$RELATED_LINK->getLabel()}" style="width:auto" title="{$DETAILVIEWRELATEDLINKLBL}"><strong class="pull-left">{$DETAILVIEWRELATEDLINKLBL}</strong> <span class="count pull-right"></span></a>
								</li>
							{/foreach}
							{if $COUNTER > 5}
							</ul></li>
						{/if}
				</ul>
			</div>
		</div>
	</div>
{/strip}
