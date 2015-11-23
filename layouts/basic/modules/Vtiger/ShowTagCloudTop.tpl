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
		<div id="tagRecordInput" class="input-group col-xs-6 col-sm-6 col-md-2">
			<span id="tagRecord" class="glyphicon glyphicon-plus cursorPointer pull-right" aria-hidden="true"></span>
			<input placeholder="{vtranslate('TAG_PLACEHOLDER')}" type="text" title="{vtranslate('TAG_PLACEHOLDER')}" id="tagRecordText" class="form-control input-sm pull-right"/>
		</div>
		<div class="col-md-12 pull-right">
			<input type="hidden" id="maxTagLength" value="{$MAX_TAG_LENGTH}" />	
			<input type="hidden" id="maxTag" value="{if isset($MAX_TAG)}{$MAX_TAG}{else}5{/if}" />	
			<div id="tagsList" class="pushDown">
				{foreach from=$TAGS[1] item=TAG_ID key=TAG_NAME}
					<div class="btn-info btn-xs pull-right tag" data-tagname="{$TAG_NAME}" data-tagid="{$TAG_ID}">
						<span class="tagName textOverflowEllipsis">
							<a class="cursorPointer">{$TAG_NAME}</a>
						</span>
						<span id="deleteTag" class="glyphicon glyphicon-remove cursorPointer deleteTag paddingLeft5px" aria-hidden="true"></span>
					</div>
				{/foreach}
			</div>
		</div>
    {/strip}	
{/if}
