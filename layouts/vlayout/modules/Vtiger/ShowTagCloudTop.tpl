{if $SHOW_TAG && $MODULE!='Users'}
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
    {strip}		
	<div class="tagCloudContainer col-md-2 pull-right" style="margin-top: 4px;">
		<input type="hidden" id="maxTagLength" value="{$MAX_TAG_LENGTH}" />	
		<input type="hidden" id="maxTag" value="{if isset($MAX_TAG)}{$MAX_TAG}{else}5{/if}" />	
		<div class="col-md-2 marginLeftZero " style="min-width: 140px;float: right;">				
			<input placeholder="{vtranslate('TAG_PLACEHOLDER')}" style="padding: 0px; width: 85%;margin-bottom: 0px;" type="text" title="{vtranslate('TAG_PLACEHOLDER')}" id="tagRecordText"/>
			<img id="tagRecord" alt="{vtranslate('LBL_ADD_TAG')}" class="cursorPointer" src="layouts/vlayout/skins/images/btnAdd.png" style="width: 10%; margin-left: 1%;vertical-align: middle;"/>		    
		</div>
		<div class="" id="tagsList">
			{foreach from=$TAGS[1] item=TAG_ID key=TAG_NAME}
				<div style="" class=" tag" data-tagname="{$TAG_NAME}" data-tagid="{$TAG_ID}">
					<span class="tagName textOverflowEllipsis">
						<a class="cursorPointer">{$TAG_NAME}</a>
					</span>
					<span class=" deleteTag cursorPointer"> x</span>
				</div>
			{/foreach}
		</div>
	</div>
    {/strip}	
{/if}
