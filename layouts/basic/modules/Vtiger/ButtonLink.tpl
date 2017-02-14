{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="btn-group {if $BUTTON_VIEW|strrpos:'listView' !== false && $WIDTHTYPE eq 'medium'}btn-group-sm{/if}">
		{assign var="LABEL" value=$LINK->getLabel()}
		{assign var="ACTION_NAME" value=$LABEL}
		{if $LINK->get('linkhint') neq ''}
			{assign var="ACTION_NAME" value=$LINK->get('linkhint')}
			{assign var="LABEL" value=$LINK->get('linkhint')}
		{/if}
		{assign var="LINK_URL" value=$LINK->getUrl()}
		{assign var="BTN_MODULE" value=$LINK->getRelatedModuleName($MODULE)}
	{if $LINK->get('linkhref')}<a{else}<button type="button"{/if}{/strip} {strip}
				{if !$LINK->isActive()} disabled {/if}
				id="{$MODULE}_{$BUTTON_VIEW}_action_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($ACTION_NAME)}"{/strip} {strip}
				class="btn {if $LINK->getClassName() neq ''}{if $LINK->getClassName()|strrpos:"btn-" === false}btn-default {/if}{$LINK->getClassName()}{else}btn-default{/if} {if $LABEL neq '' && $LINK->get('showLabel') != '1'} popoverTooltip{/if} {if $LINK->get('modalView')}showModal{/if}"{/strip} {strip}
				{if $LINK->get('linkdata') neq '' && is_array($LINK->get('linkdata'))}
					{foreach from=$LINK->get('linkdata') key=NAME item=DATA}
						data-{$NAME}="{$DATA}" 
					{/foreach}
				{/if}
			{/strip} {strip}
				{if $LABEL neq '' && $LINK->get('showLabel') != 1}
					data-placement="bottom"
					data-content="{vtranslate($LABEL, $BTN_MODULE)}"
				{/if}
			{/strip} {strip}
				{if $LINK->get('linkhref')}
					href="{$LINK_URL}"
				{/if}
			{/strip} {strip}
				{if $LINK->get('linktarget')}
					target="{$LINK->get('linktarget')}"
				{/if}
			{/strip} {strip}
				{if $LINK->get('modalView')}
					data-url="{$LINK_URL}"
				{else}
					{if $LINK->get('linkPopup')}
						onclick="window.open('{$LINK_URL}', '{if $LINK->get('linktarget')}{$LINK->get('linktarget')}{else}_self{/if}'{if $LINK->get('linkPopup')}, 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})" 
					{else}
						{if $LINK_URL neq '' && !$LINK->get('linkhref')}
							{if stripos($LINK_URL, 'javascript:')===0}
								onclick='{$LINK_URL|substr:strlen("javascript:")};'
							{else}
								onclick='window.location.href = "{$LINK_URL}"'
							{/if} 
						{/if}
					{/if}
				{/if}
				>
				{if $LINK->get('linkimg') neq ''}
					<img class="image-in-button" src="{$LINK->get('linkimg')}" title="{vtranslate($LABEL, $BTN_MODULE)}">
				{elseif $LINK->get('linkicon') neq ''}
					<span class="{$LINK->get('linkicon')}"></span>
				{/if}
				{if $LABEL neq '' && $LINK->get('showLabel') == 1}
					{if $LINK->get('linkimg') neq '' || $LINK->get('linkicon') neq ''}&nbsp;&nbsp;{/if}
					<strong>{vtranslate($LABEL, $BTN_MODULE)}</strong>
				{/if}
				{if $LINK->get('linkhref')}</a>{else}</button>{/if}
</div>
{/strip}
