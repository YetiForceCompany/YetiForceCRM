{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-RegistrationOnlineModal -->
	<div class="modal-body">
		<input type="hidden" name="module" value="YetiForce" />
		<input type="hidden" name="parent" value="Settings" />
		<input type="hidden" name="action" value="Register" />
		<input type="hidden" name="mode" value="online" />
		{assign var="QUALIFIED_MODULE" value="Settings::Companies"}
		<div class="alert alert-info" role="alert">
			<span class="u-fs-13px">
				{assign var=CREDITS_LINK value=''}
				{if \App\Security\AdminAccess::isPermitted('Dependencies')}
					{assign var=CREDITS_LINK value="<a target=\"_blank\" href=\"index.php?module=Dependencies&view=Credits&parent=Settings&displayLicenseModal=YetiForce\">Link</a>"}
				{/if}
				{\App\Language::translateArgs('LBL_CONDITIONS_OF_REGISTRATION', $QUALIFIED_MODULE, $CREDITS_LINK, \App\Language::translate('LBL_CHANGING_ENTITY_NAME', $QUALIFIED_MODULE))}
			</span>
		</div>
		<div class="alert alert-info" role="alert">
			<a target="_blank" rel="noreferrer noopener" href="https://github.com/YetiForceCompany/YetiForceCRM/blob/{\App\Version::getShort()}.0/licenses/{$LICENSE['fileName']}">
				licenses/{$LICENSE['fileName']}
			</a>
			<pre class="u-pre">{$LICENSE['text']}</pre>
		</div>
		{foreach from=$REGISTER_COMPANIES key=TYPE_LABEL item=COMPANIES}
			<form>
				{foreach from=$COMPANIES item=COMPANY_ROW}
					<input type="hidden" name="id" value="{$COMPANY_ROW['id']}" />
					{include file=\App\Layout::getTemplatePath('Form.tpl',$QUALIFIED_MODULE) COMPANY_ID=$COMPANY_ROW['id']}
				{/foreach}
			</form>
		{/foreach}
	</div>
	<!-- /tpl-Settings-YetiForce-RegistrationOnlineModal -->
{/strip}
