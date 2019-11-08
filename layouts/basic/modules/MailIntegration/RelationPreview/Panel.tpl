{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-MailIntegration-RelationPreview-Panel -->
	<div class="js-panel" data-mail-id="{$MAIL_ID}" data-js="data">
		{if isset($URL)}
			<div class="mx-1">
				{if $MAIL_ID}
					<div class="js-relations-container" data-js="html">
						{include file=\App\Layout::getTemplatePath('RelationPreview/ActionsMailExist.tpl', $MODULE_NAME)}
					</div>
				{else}
					{include file=\App\Layout::getTemplatePath('RelationPreview/ActionsNoMail.tpl', $MODULE_NAME)}
				{/if}
			</div>
			<iframe id="js-iframe" frameborder="0" width="100%" frameborder="0" allowfullscreen="true" data-js="iframe"></iframe>
			{foreach item=MODEL from=$MODAL_SCRIPTS}
				<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
			{/foreach}
		{else}
			<div class="alert alert-danger m-2" role="alert">{\App\Language::translate('LBL_PERMISSION_DENIED')}</div>
		{/if}
	</div>
<!-- /tpl-MailIntegration-RelationPreview-Panel -->
{/strip}
