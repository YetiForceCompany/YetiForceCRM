{if $ACCOUNTSLIST}
{assign var="MAILS" value=OSSMail_Record_Model::getMailsFromIMAP($USER)}
<div>
	{foreach from=$MAILS item=item key=key}
	<div class="row-fluid mailRow" data-mailId="{$key}">
		<div class="span12" style="font-size:x-small;">
			<div class="pull-right muted" style="font-size:x-small;">
				<small title="{$item['date']}">{Vtiger_Util_Helper::formatDateDiffInStrings($item['date'])}</small>&nbsp;&nbsp;&nbsp;&nbsp;
			</div>
			<h5 style="margin-left:2%;">{$item['subject']} {if count($item['attachments']) > 0}<img alt="{vtranslate('LBL_ATTACHMENT')}" class="pull-right" src="layouts/vlayout/modules/OSSMailView/zalacznik.png" />{/if}<h5>
		</div>
		<div class="span12 marginLeftZero">
			<div class="pull-right" >
				<a class="showMailBody" >
					<span class="body-icon icon-chevron-down"></span>&nbsp;&nbsp;&nbsp;&nbsp;
				</a>
			</div>
			<span class="pull-left" style="margin-left:2%;">{vtranslate('From', 'OSSMailView')}: {$item['fromaddress']}</span>
		</div>
		<div class="span12 mailBody marginLeftZero" style="display: none;border: 1px solid #ddd;">
			{Vtiger_Functions::removeHtmlTags(array('link', 'style', 'a', 'img', 'script'), $item['body'])}
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