{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip} {assign var=SPAN_COUNT value=1}
                        <div  class="row-fluid">
    {foreach item=SETTING_SHORTCUT from=$SETTINGS_SHORTCUT name=shortcuts}
	<span id="shortcut_{$SETTING_SHORTCUT->getId()}" data-actionurl="{$SETTING_SHORTCUT->getPinUnpinActionUrl()}" class="span3 contentsBackground well cursorPointer moduleBlock" data-url="{$SETTING_SHORTCUT->getUrl()}">
		<button data-id="{$SETTING_SHORTCUT->getId()}" title="{vtranslate('LBL_REMOVE',$MODULE)}" style="margin-right: -2%;margin-top: -5%;" title="Close" type="button" class="unpin close hide">x</button>
		<h5 class="themeTextColor">{vtranslate($SETTING_SHORTCUT->get('name'),$MODULE)}</h5>
		<div>{vtranslate($SETTING_SHORTCUT->get('description'),$MODULE)}</div>
	</span>
         {if $SPAN_COUNT==3}</div>{$SPAN_COUNT=1}{if not $smarty.foreach.shortcuts.last}<div class="row-fluid">{/if}{continue}{/if}
                        {$SPAN_COUNT=$SPAN_COUNT+1}
        {/foreach}
                        </div>
{/strip}