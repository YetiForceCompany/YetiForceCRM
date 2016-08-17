{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
<div class="dashboardWidgetHeader">
	{foreach key=index item=cssModel from=$STYLES}
		<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
	{/foreach}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), 'OSSMail')}"><strong>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(),'OSSMail')}</strong></div>
		</div>
		<div class="col-md-4">
			<div class="box pull-right">
				{if !$WIDGET->isDefault()}
					<a name="dclose" class="btn btn-xs btn-default widget" data-url="{$WIDGET->getDeleteUrl()}">
						<span class="glyphicon glyphicon-remove" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_CLOSE')}" alt="{vtranslate('LBL_CLOSE')}"></span>
					</a>
				{/if}
			</div>
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row" >
		<div class="col-md-6 pull-right">
			<select class="mailUserList form-control input-sm select2" id="mailUserList" title="{vtranslate('LBL_MAIL_USERS_LIST')}" name="type">
				{if count($ACCOUNTSLIST) eq 0}
					<option value="-">{vtranslate('--None--', $MODULE_NAME)}</option>
				{else}
					{foreach from=$ACCOUNTSLIST item=item key=key}
						<option title="{$item['username']}" value="{$item['user_id']}" {if $USER == $item['user_id']}selected{/if}>{$item['username']}</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/MailsListContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
{/strip}
