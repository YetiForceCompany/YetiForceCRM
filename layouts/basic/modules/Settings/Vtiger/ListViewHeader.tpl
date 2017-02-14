{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div class="">
		<div class='widget_header row '>
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</div>
		</div>
		<div class="listViewActionsDiv row">
			<div class="{if $SUPPORTED_MODULE_MODELS}col-md-5{else}col-md-8{/if} btn-toolbar">
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
			{if $SUPPORTED_MODULE_MODELS}
				<div class="col-md-3 btn-toolbar marginLeftZero">
					<select class="chzn-select form-control" id="moduleFilter" >
						<option value="">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</option>
						{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
							<option {if $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if} value="{$TAB_ID}">
									{App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
							</option>
						{/foreach}
					</select>
				</div>
			{/if}
			<div class="col-md-4">
				{include file='ListViewActions.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="listViewContentDiv listViewPageDiv" id="listViewContents">
		{/strip}
