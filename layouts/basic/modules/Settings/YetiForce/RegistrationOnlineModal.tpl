{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-RegistrationOnlineModal modal-body">

		<form>
			<input type="hidden" name="module" value="YetiForce"/>
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" name="action" value="Register"/>
			<input type="hidden" name="mode" value="online"/>
			{if !empty($REGISTER_COMPANIES['users'])}
				<h6>{\App\Language::translate('LBL_END_USERS',$QUALIFIED_MODULE)}:</h6>
				{foreach $REGISTER_COMPANIES['users'] as $COMPANY_ROW}
					{include file=\App\Layout::getTemplatePath('RegistrationForm.tpl',$QUALIFIED_MODULE) COMPANY_ID=$COMPANY_ROW['id']}
				{/foreach}
			{/if}
			{if !empty($REGISTER_COMPANIES['integrators'])}
				<h6>{\App\Language::translate('LBL_INTEGRATORS',$QUALIFIED_MODULE)}:</h6>
				{foreach $REGISTER_COMPANIES['integrators'] as $COMPANY_ROW}
					{include file=\App\Layout::getTemplatePath('RegistrationForm.tpl',$QUALIFIED_MODULE) COMPANY_ID=$COMPANY_ROW['id']}
				{/foreach}
			{/if}
			{if $REGISTER_COMPANIES['suppliers']}
				<h6>{\App\Language::translate('LBL_PROVIDERS',$QUALIFIED_MODULE)}:</h6>
				{foreach $REGISTER_COMPANIES['suppliers'] as $COMPANY_ROW}
					{include file=\App\Layout::getTemplatePath('RegistrationForm.tpl',$QUALIFIED_MODULE) COMPANY_ID=$COMPANY_ROW['id']}
				{/foreach}
			{/if}
		</form>
	</div>
{/strip}
