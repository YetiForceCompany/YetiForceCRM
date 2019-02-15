{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-ButtonLink -->
	{if empty($CLASS)}
		{assign var='CLASS' value=''}
	{/if}
	<div class="c-btn-link btn-group {if $BUTTON_VIEW|strrpos:'listView' !== false && $USER_MODEL->get('rowheight') eq 'narrow'}btn-group-sm{/if} {$CLASS}">
		{assign var="LABEL" value=$LINK->getLabel()}
		{assign var="ACTION_NAME" value=$LABEL}
		{if $LINK->get('linkhint') neq ''}
			{assign var="ACTION_NAME" value=$LINK->get('linkhint')}
			{assign var="LABEL" value=$LINK->get('linkhint')}
		{/if}
		{assign var="LINK_URL" value=$LINK->getUrl()}
		{assign var="BTN_MODULE" value=$LINK->getRelatedModuleName($MODULE)}
		{if $LINK->get('linkhref')}<a role="button"{else}
		<button type="button"{/if} {if !$LINK->isActive()}{' '}disabled{/if}{' '}
				class="btn {if $LINK->getClassName() neq ''}{if $LINK->getClassName()|strrpos:"btn-" === false}btn-outline-dark {/if}{$LINK->getClassName()}{else}btn-outline-dark{/if}  {if $LINK->get('modalView')}showModal{/if} {$MODULE}_{$BUTTON_VIEW}_action_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($ACTION_NAME)} {if $LABEL neq '' && $LINK->get('showLabel') != '1'} js-popover-tooltip"
				data-js="popover{/if}"
				{if $LINK->get('linkdata') neq '' && is_array($LINK->get('linkdata'))}
					{foreach from=$LINK->get('linkdata') key=NAME item=DATA}
						{' '}data-{$NAME}="{$DATA}"
					{/foreach}
				{/if}
				{if $LABEL neq '' && $LINK->get('showLabel') != 1}{' '}
					data-placement="{if $BUTTON_VIEW|strrpos:'listView'!==false}top{else}bottom{/if}"{' '}
					data-content="{\App\Language::translate($LABEL, $BTN_MODULE)}"
					data-target="focus hover"
				{/if}
				{if $LINK->get('linkhref')}
					{' '}href="{$LINK_URL}"
				{/if}
				{if $LINK->get('linktarget')}
					{' '}target="{$LINK->get('linktarget')}"
				{/if}
				{if $LINK->get('style')}
					{' '}style="{$LINK->get('style')}"
				{/if}
				{if $LINK->get('dataUrl')}
					{' '}data-url="{$LINK->get('dataUrl')}"
				{/if}
				{' '}
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
								onclick='app.openUrl("{$LINK_URL}")'
							{/if}
						{/if}
					{/if}
				{/if}
		>
			{if !empty($LINK->get('linkicon'))}
				<span class="{$LINK->get('linkicon')} {if !empty($LINK->get('linkimg')) || !empty($LINK->get('linkicon')) && $LINK->get('showLabel') neq null && $BUTTON_VIEW neq 'detailViewBasic'}mr-1{/if} "></span>
			{/if}
			{if !empty($LABEL) && !empty($CLASS) && $CLASS == 'c-btn-link--responsive'}
				<span class="d-{if isset($BREAKPOINT)}{$BREAKPOINT}{else}sm{/if}-none ml-1">{\App\Language::translate($LABEL, $BTN_MODULE)}</span>
			{elseif !empty($LABEL) && $LINK->get('showLabel') == 1}
				{\App\Language::translate($LABEL, $BTN_MODULE)}
			{/if}
			{if $LINK->get('linkhref')}</a>{else}</button>{/if}
	</div>
	<!-- /tpl-Base-ButtonLink -->
{/strip}
