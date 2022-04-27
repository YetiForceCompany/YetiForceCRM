{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="dashboardWidgetHeader">
		{foreach key=index item=cssModel from=$STYLES}
			<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
		{/foreach}
		{foreach key=index item=jsModel from=$SCRIPTS}
			<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
		{/foreach}
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			<div class="d-inline-flex">
				<button class="btn btn-sm btn-light js-widget-refresh" title="{\App\Language::translate('LBL_REFRESH')}" data-url="{$WIDGET->getUrl()}&content=data" data-js="click">
					<span class="fas fa-sync-alt"></span>
				</button>
				{if !$WIDGET->isDefault()}
					<button class="btn btn-sm btn-light js-widget-remove" title="{\App\Language::translate('LBL_CLOSE')}" data-url="{$WIDGET->getDeleteUrl()}" data-js="click">
						<span class="fas fa-times"></span>
					</button>
				{/if}
			</div>
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters">
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="input-group-text">
							<span class="fas fa-envelope"></span>
						</span>
					</div>
					<select class="form-control select2noactive widgetFilter" name="mailAccount" aria-describedby="inputGroup-sizing-sm" id="mailUserList" title="{\App\Language::translate('LBL_MAIL_USERS_LIST')}">
						{if count($ACCOUNTSLIST) eq 0}
							<option value="-">{\App\Language::translate('--None--', $MODULE_NAME)}</option>
						{else}
							{foreach from=$ACCOUNTSLIST item=item key=key}
								<option title="{$item['username']}" value="{$item['user_id']}" {if $USER == $item['user_id']}selected{/if}>{$item['username']}</option>
							{/foreach}
						{/if}
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/MailsListContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
