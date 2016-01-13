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
		<div id="tagRecordInput">
			<div class="col-lg-2 col-md-3 col-sm-8 col-xs-12 paddingLRZero pull-left tagInputs">
				<div class="input-group">
					<span class="tagAsterisk input-group-addon">
						<span class="glyphicon glyphicon-asterisk"></span>
					</span>
					<input placeholder="{vtranslate('TAG_PLACEHOLDER')}" type="text" title="{vtranslate('TAG_PLACEHOLDER')}" id="tagRecordText" class="form-control tagText"/>
					<span id="tagRecord" class="input-group-btn">
						<button  class="btn btn-info " type="button"><span  class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
					</span>
				</div>
			</div>
			<input type="hidden" id="maxTagLength" value="{$MAX_TAG_LENGTH}" />	
			<input type="hidden" id="maxTag" value="{if isset($MAX_TAG)}{$MAX_TAG}{else}5{/if}" />
			<div id="tagsList" class="col-xs-12 col-md-9 col-lg-10 paddingLRZero">
				{foreach from=$TAGS[1] item=TAG_ID key=TAG_NAME}
					<div class="btn-info btn-xs pull-left tag" data-tagname="{$TAG_NAME}" data-tagid="{$TAG_ID}">
						<span class="glyphicon glyphicon-asterisk">&nbsp;</span>
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
