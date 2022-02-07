{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailRbl-ConfigModal -->
	<div class="modal-body">
		<form class="js-form-single-save js-validate-form" data-js="container|validationEngine">
			<input type="hidden" name="parent" value="Settings">
			<input type="hidden" name="module" value="MailRbl">
			<input type="hidden" name="action" value="ConfigModal">
			{include file=\App\Layout::getTemplatePath('ConfigForm.tpl','Vtiger/Utils')}
		</form>
	</div>
	<!-- /tpl-Settings-MailRbl-ConfigModal -->
{/strip}
