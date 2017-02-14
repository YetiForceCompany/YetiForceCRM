{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
<div class="SendEmailFormStep2" id="emailPreview" name="emailPreview">
	<div class="well-large zeroPaddingAndMargin">
		<form class="form-horizontal emailPreview" style="overflow: overlay;">
			<div class="row padding-bottom1per">
				<span class="col-md-12">
					<span class="col-xs-1">
						<span class="pull-right muted">{vtranslate('From',$MODULENAME)}</span>
					</span>
					<span class="col-xs-11">
						<span id="emailPreview_From" class="">{$FROM}</span>
						<span style="display: none;" id="_mailopen_date" class="row">{$SENT}</span>
					</span>
				</span>
			</div>
			<div class="row padding-bottom1per">
				<span class="col-md-12">
					<span class="col-xs-1">
						<span class="pull-right muted">{vtranslate('To',$MODULENAME)}</span>
					</span>
					<span class="col-xs-11">
						<span id="emailPreview_To" class="">{assign var=TO_EMAILS value=","|implode:$TO}{$TO_EMAILS}</span>
					</span>
				</span>
			</div>
			{if !empty($CC)}
			<div class="row padding-bottom1per">
				<span class="col-md-12">
					<span class="col-xs-1">
						<span class="pull-right muted">{vtranslate('CC',$MODULENAME)}</span>
					</span>
					<span class="col-xs-11">
						<span id="emailPreview_Cc" class="row">
							{$CC}
						</span>
					</span>
				</span>
			</div>
			{/if}
			{if !empty($BCC)}
			<div class="row padding-bottom1per">
				<span class="col-md-12">
					<span class="col-xs-1">
						<span class="pull-right muted">{vtranslate('BCC',$MODULENAME)}</span>
					</span>
					<span class="col-xs-11">
						<span id="emailPreview_Bcc" class="row">
							{$BCC}
						</span>
					</span>
				</span>
			</div>
			{/if}
			<div class="row padding-bottom1per">
				<span class="col-md-12">
					<span class="col-xs-1">
						<span class="pull-right muted">{vtranslate('Subject',$MODULENAME)}</span>
					</span>
					<span class="col-xs-11">
						<span id="emailPreview_Subject" class="">
							{$SUBJECT}
						</span>
					</span>
				</span>
			</div>
			{if !empty($ATTACHMENTS)}
			<div class="row padding-bottom1per">
				<span class="col-md-12">
					<span class="col-xs-1">
						<span class="pull-right muted">{vtranslate('Attachments_Exist',$MODULENAME)}</span>
					</span>
					<span class="col-xs-11">
						<span id="emailPreview_attachment" class="row">
							{foreach item=ATTACHMENT from=$ATTACHMENTS}
                                <a href="index.php?module=Documents&action=DownloadFile&record={$ATTACHMENT['id']}">{$ATTACHMENT['file']}</a>&nbsp;&nbsp;
							{/foreach}
						</span>
					</span>
				</span>
			</div>
			{/if}
			<div class="row padding-bottom1per">
				<span class="col-md-12">
					<span class="col-xs-1">
						<span class="pull-right muted">{vtranslate('Content',$MODULENAME)}</span>
					</span>
					<span class="col-xs-11">
						
					</span>
				</span>
			</div>
		<div>
			<iframe id="emailPreview_Content" style="width: 100%;height: 600px;" src="{$URL}" frameborder="0"></iframe>
		</div>
		</form>
	</div>
</div>
{/strip}
{literal}
<script>

</script>
{/literal}
