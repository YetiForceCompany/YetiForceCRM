{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-RegistrationOnlineModal modal-body">
		<form>
			<input type="hidden" name="module" value="YetiForce"/>
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" name="action" value="Register"/>
			<input type="hidden" name="mode" value="online"/>
			{assign var="QUALIFIED_MODULE" value="Settings::Companies"}
			<div class="alert alert-info" role="alert">
				<span class="u-font-size-13px">
				{\App\Language::translateArgs('LBL_CONDITIONS_OF_REGISTRATION', $QUALIFIED_MODULE ,"<a href=\"index.php?module=Vtiger&view=Credits&parent=Settings\">Link</a>")}
				</span>
			</div>
			{foreach from=$REGISTER_COMPANIES key=TYPE_LABEL item=COMPANIES}
				{foreach from=$COMPANIES item=COMPANY_ROW}
					{include file=\App\Layout::getTemplatePath('Form.tpl',$QUALIFIED_MODULE) COMPANY_ID=$COMPANY_ROW['id']}
				{/foreach}
			{/foreach}
		</form>
	</div>
{/strip}
