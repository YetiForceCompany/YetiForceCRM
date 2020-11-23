{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-RegistrationOnlineModal modal-body">
		<input type="hidden" name="module" value="YetiForce"/>
		<input type="hidden" name="parent" value="Settings"/>
		<input type="hidden" name="action" value="Register"/>
		<input type="hidden" name="mode" value="online"/>
		{assign var="QUALIFIED_MODULE" value="Settings::Companies"}
		<div class="alert alert-info" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<span class="u-fs-13px">
				{assign var=CREDITS_LINK value=''}
				{if \App\Security\AdminAccess::isPermitted('Dependencies')}
					{assign var=CREDITS_LINK value="<a href=\"index.php?module=Dependencies&view=Credits&parent=Settings\">Link</a>"}
				{/if}
				{\App\Language::translateArgs('LBL_CONDITIONS_OF_REGISTRATION', $QUALIFIED_MODULE, $CREDITS_LINK, \App\Language::translate('LBL_CHANGING_COMPANY_NAME', $QUALIFIED_MODULE))}
			</span>
		</div>
		{foreach from=$REGISTER_COMPANIES key=TYPE_LABEL item=COMPANIES}
			<form>
				{foreach from=$COMPANIES item=COMPANY_ROW}
					<input type="hidden" name="id" value="{$COMPANY_ROW['id']}"/>
					{include file=\App\Layout::getTemplatePath('Form.tpl',$QUALIFIED_MODULE) COMPANY_ID=$COMPANY_ROW['id']}
				{/foreach}
			</form>
		{/foreach}
	</div>
{/strip}
