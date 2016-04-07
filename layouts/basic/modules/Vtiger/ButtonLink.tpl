{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="btn-group">
		{assign var="LABEL" value=$LINK->getLabel()}
		{assign var="ACTION_NAME" value=$LABEL}
		{if $LINK->get('linkhint') neq ''}
			{assign var="ACTION_NAME" value=$LINK->get('linkhint')}
			{assign var="LABEL" value=$LINK->get('linkhint')}
		{/if}
	{if $LINK->get('linkhref')}<a{else}<button{/if}{/strip} {strip}
				id="{$MODULE}_{$BUTTON_VIEW}_action_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($ACTION_NAME)}"{/strip} {strip}
				class="btn btn-default {if $LINK->getClassName() neq ''}{$LINK->getClassName()}{/if} {if $LABEL neq '' && $LINK->get('showLabel') != '1'} popoverTooltip{/if}"{/strip} {strip}
				{if $LINK->get('linkdata') neq '' && is_array($LINK->get('linkdata'))}
					{foreach from=$LINK->get('linkdata') key=NAME item=DATA}
						data-{$NAME}="{$DATA}" 
					{/foreach}
				{/if}
			{/strip} {strip}
				{if $LABEL neq '' && $LINK->get('showLabel') != 1}
					data-placement="bottom"
					data-content="{vtranslate($LABEL, $MODULE)}"
				{/if}
			{/strip} {strip}
				{if $LINK->get('linkhref')}
					href="{$LINK->getUrl()}"
				{/if}
			{/strip} {strip}
				{if $LINK->get('linkPopup')}
					onclick="window.open('{$LINK->getUrl()}', '{if $LINK->get('linktarget')}{$LINK->get('linktarget')}{else}_self{/if}'{if $LINK->get('linkPopup')}, 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})" 
				{else}
					{if $LINK->getUrl() neq '' && !$LINK->get('linkhref')}
						{if stripos($LINK->getUrl(), 'javascript:')===0}
							onclick='{$LINK->getUrl()|substr:strlen("javascript:")};'
						{else}
							onclick='window.location.href = "{$LINK->getUrl()}"'
						{/if} 
					{/if}
				{/if}
				>
				{if $LINK->get('linkimg') neq ''}
					<img class="image-in-button" src="{$LINK->get('linkimg')}">
				{elseif $LINK->get('linkicon') neq ''}
					<span class="{$LINK->get('linkicon')}"></span>
				{/if}
				{if $LABEL neq '' && $LINK->get('showLabel') == 1}
					{if $LINK->get('linkimg') neq '' || $LINK->get('linkicon') neq ''}&nbsp;&nbsp;{/if}
					<strong>{vtranslate($LABEL, $MODULE)}</strong>
				{/if}
				{if $LINK->get('linkhref')}</a>{else}</button>{/if}
</div>
{/strip}
