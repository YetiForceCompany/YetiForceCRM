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
	<div class="row-fluid tagCloudContainer">
		<input type="hidden" id="maxTagLength" value="{$MAX_TAG_LENGTH}" />
		<div class="row-fluid">
			<span class="span1">&nbsp;</span>
			<input type="text" class="span10" id="tagRecordText" />
		</div>
		<div class="row-fluid">
			<div class="row-fluid">
				<span class="span1">&nbsp;</span>
				<input type="button" class="btn span" id="tagRecord" value="{vtranslate('LBL_TAG_THIS_RECORD',$MODULE)}" /></div>
			</div>
			<div class="span11 row-fluid padding10" id="tagsList">
				{foreach from=$TAGS[1] item=TAG_ID key=TAG_NAME}
					<div class="tag row-fluid span11 marginLeftZero" data-tagname="{$TAG_NAME}" data-tagid="{$TAG_ID}"><span class="tagName textOverflowEllipsis span11"><a class="cursorPointer">{$TAG_NAME}</a></span><span class="pull-right deleteTag cursorPointer">x</span></div>
				{/foreach}
			</div>
	</div>
{/strip}	