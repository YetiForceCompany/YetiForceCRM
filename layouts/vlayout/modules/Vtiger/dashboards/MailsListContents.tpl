{if $ACCOUNTSLIST}
{assign var="MAILS" value=OSSMail_Record_Model::getMailsFromIMAP($OWNER)}
<div>
	{foreach from=$MAILS item=item key=key}
	<div class="row mailRow" data-mailId="{$key}">
		<div class="col-md-12" style="font-size:x-small;">
			<div class="pull-right muted" style="font-size:x-small;">
				<small title="{$item['date']}">{Vtiger_Util_Helper::formatDateDiffInStrings($item['date'])}</small>&nbsp;&nbsp;&nbsp;&nbsp;
			</div>
			<h5 style="margin-left:2%;">{$item['subject']} {if count($item['attachments']) > 0}<img alt="{vtranslate('LBL_ATTACHMENT')}" class="pull-right" src="layouts/vlayout/modules/OSSMailView/attachment.png" />{/if}<h5>
		</div>
		<div class="col-md-12 marginLeftZero">
			<div class="pull-right" >
				<a class="showMailBody" >
					<span class="body-icon glyphicon glyphicon-chevron-down"></span>&nbsp;&nbsp;&nbsp;&nbsp;
				</a>
			</div>
			<span class="pull-left" style="margin-left:2%;">{vtranslate('From', 'OSSMailView')}: {$item['fromaddress']}</span>
		</div>
		<div class="col-md-12 mailBody marginLeftZero" style="display: none;border: 1px solid #ddd;">
			{Vtiger_Functions::removeHtmlTags(array('link', 'style', 'a', 'img', 'script', 'base'), $item['body'])}
		</div>
	</div>
	<hr/>
	{/foreach}
</div>
{else}
	<span class="noDataMsg" style="position: relative; top: 115px; left: 133px;">
		{vtranslate('LBL_NOMAILSLIST', 'OSSMail')}
	</span>
{/if}
</div>
