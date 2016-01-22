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
<!DOCTYPE>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	</head>
	<body onLoad="javascript:window.print()" style="padding:3% 5%;">
		{assign var="TO_EMAILS" value=$RECORD->get('saved_toid')|replace:']':''}
		{assign var="TO_EMAIL" value=$TO_EMAILS|replace:'[':''}
		{assign var="TO_EMAIL_VALUE" value=$TO_EMAIL|replace:'&quot;':''}
		<span style="position:absolute;right:6%;top:3%;font-family:'Lucida Grande';font-size:15px">
			{$USER_MODEL->get('last_name')} {$USER_MODEL->get('first_name')} &lt;{$USER_MODEL->get('email1')}&gt;
		</span><hr/>
		<span style="font-family:'Lucida Grande';font-size:15px">
			{$RECORD->get('subject')}
		</span><hr/>
		<div>
			<div style="width:100%;text-align: right;font-family:'Lucida Grande';font-size:15px">
				<span>
					{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECORD->get('createdtime'))}
				</span>
			</div>
		</div>
		<div>
			<div style="float:left;width:10%;text-align: right;font-family:'Lucida Grande';font-size:15px">
				{vtranslate('LBL_FROM',$MODULE)}
			</div>
			<div style="width:90%;text-align: left;font-family:'Lucida Grande';font-size:15px">
				<span style="margin-left:7%;">
					{$FROM}
				</span>
			</div>
			<div class="clear-both"></div>
		</div>
		<div>
			<div style="float:left;width:10%;text-align: right;font-family:'Lucida Grande';font-size:15px">
				{vtranslate('LBL_TO',$MODULE)}
			</div>
			<div style="width:90%;text-align: left;font-family:'Lucida Grande';font-size:15px">
				<span style="margin-left:7%;font-family:'Lucida Grande';font-size:15px">
					{assign var=TO_EMAILS value=","|implode:$TO}
					{$TO_EMAILS}
				</span>
			</div>
			<div class="clear-both"></div>
		</div>
		{if !empty($CC)}
			<div>
				<div style="float:left;width:10%;text-align: right;font-family:'Lucida Grande';font-size:15px">
					{vtranslate('LBL_CC',$MODULE)}
				</div>
				<div style="width:90%;text-align: left;font-family:'Lucida Grande';font-size:15px">
					<span style="margin-left:7%;">
						{if !empty($CC)}
							{$CC}
						{/if}
					</span>
				</div>
				<div class="clear-both"></div>
			</div>
		{/if}
		{if !empty($BCC)}
			<div>
				<div style="float:left;width:10%;text-align: right;font-family:'Lucida Grande';font-size:15px">
					{vtranslate('LBL_BCC',$MODULE)}
				</div>
				<div style="width:90%;text-align: left;font-family:'Lucida Grande';font-size:15px">
					<span style="margin-left:7%;">
						{if !empty($BCC)}
							{$BCC}
						{/if}
					</span>
				</div>
				<div class="clear-both"></div>
			</div>
		{/if}
		<div>
			<div style="float:left;width:10%;text-align: right;font-family:'Lucida Grande';font-size:15px">
				{vtranslate('LBL_SUBJECT',$MODULE)}
			</div>
			<div style="width:90%;text-align: left;font-family:'Lucida Grande';font-size:15px">
				<span style="margin-left:7%;">
					{$RECORD->get('subject')}
				</span>
			</div>
			<div class="clear-both"></div>
		</div>
		{assign var="ATTACHMENT_DETAILS" value=$RECORD->getAttachmentDetails()}
		{if !empty($ATTACHMENT_DETAILS)}
			<div>
				<div style="float:left;width:10%;text-align: right;font-family:'Lucida Grande';font-size:15px">
					{vtranslate('LBL_ATTACHMENT',$MODULE)}
				</div>
				<div style="width:90%;text-align: left;font-family:'Lucida Grande';font-size:15px">
					<span style="margin-left:7%;">
						{foreach item=ATTACHMENT_DETAIL  from=$ATTACHMENT_DETAILS}
							<a href="javascript:void(0)">{$ATTACHMENT_DETAIL['attachment']}</a>&nbsp;&nbsp;
						{/foreach}
					</span>
				</div>
				<div class="clear-both"></div>
			</div>
		{/if}
		<div>
			<div style="width:90%;text-align: left;margin-left:7%;font-family:'Lucida Grande';font-size:15px">
				<span style="margin-left:7%;">
					{decode_html($RECORD->get('description'))}
				</span>
			</div>
			<div class="clear-both"></div>
		</div>
	</body>
</html>
{/strip}
