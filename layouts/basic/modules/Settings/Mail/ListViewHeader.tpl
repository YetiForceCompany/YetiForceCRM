{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
	<div class="">
		<div class='widget_header row '>
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@\App\Layout::getTemplatePath:$QUALIFIED_MODULE}
				{App\Language::translate('LBL_EMAILS_TO_SEND_DESCRIPTION',$QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="listViewActionsDiv row">
			<div class="col-md-8 btn-toolbar">
				{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
					{if $LINK->getLabel()}
						{assign var="LABEL" value=\App\Language::translate($LINK->getLabel(), $QUALIFIED_MODULE)}
					{/if}
					<button type="button" title="{if $LINK->getLabel()}{$LABEL}{/if}" class="btn{if $LINK->getClassName()} {$LINK->getClassName()}{else} btn-default{/if}" 
							{if $LINK->getUrl()}
								{if stripos($LINK->getUrl(), 'javascript:')===0} onclick='{$LINK->getUrl()|substr:strlen("javascript:")};'
								{else} onclick='window.location.href = "{$LINK->getUrl()}"' {/if}
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
							&nbsp;<strong>{$LABEL}</strong>
						{/if}
					</button>
				{/foreach}
			</div>
			<div class="col-md-4">
				{include file='ListViewActions.tpl'|@\App\Layout::getTemplatePath:$QUALIFIED_MODULE}
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="listViewContentDiv listViewPageDiv" id="listViewContents">
		{/strip}
