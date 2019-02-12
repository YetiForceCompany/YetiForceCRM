{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-RegistrationOnlineModal modal-body">
		<form>
			<input type="hidden" name="module" value="YetiForce"/>
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" name="action" value="Register"/>
			<input type="hidden" name="mode" value="online"/>
			{assign var="QUALIFIED_MODULE" value="Settings::Companies"}
			{foreach from=$REGISTER_COMPANIES key=TYPE_LABEL item=COMPANIES}
				<h6>{\App\Language::translate($TYPE_LABEL, $QUALIFIED_MODULE)}:</h6>
				{foreach from=$COMPANIES item=COMPANY_ROW}
					{include file=\App\Layout::getTemplatePath('Form.tpl',$QUALIFIED_MODULE) COMPANY_ID=$COMPANY_ROW['id']}
				{/foreach}
			{/foreach}
		</form>
	</div>
{/strip}
