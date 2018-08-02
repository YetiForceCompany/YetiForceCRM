{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<h6 class="h3">
		{App\Language::translate('LBL_SAVE_TO_NEWSLETTER','Settings:SystemWarnings')}
	</h6>
	<p>{App\Language::translate('LBL_NEWSLETTER_DESC','Settings:SystemWarnings')}</p>
	<form class="validateForm" method="post" action="index.php">
		<div class="form-row">
			<div class="form-group col-md-4">
				<label for="first_name"><span class="redColor">*</span>{App\Language::translate('First Name')}</label>
					<input type="text" name="first_name" class="form-control" id="first_name"
						   placeholder="{App\Language::translate('First Name')}"
						   data-validation-engine="validate[required]">
			</div>
			<div class="form-group col-md-4">
				<label for="last_name">{App\Language::translate('Last Name')}</label>
					<input type="text" name="last_name" class="form-control" id="last_name"
						   placeholder="{App\Language::translate('Last Name')}">
			</div>
			<div class="form-group col-md-4">
				<label for="email"><span class="redColor">*</span>{App\Language::translate('LBL_EMAIL_ADRESS')}</label>
					<input type="text" name="email" class="form-control" id="email"
						   placeholder="{App\Language::translate('LBL_EMAIL_ADRESS')}"
						   data-validation-engine="validate[required,custom[email]]">
			</div>
		</div>
		<div class="float-right mr-2">
			<button type="button" class="btn btn-success ajaxBtn mr-1">
				<span class="fas fa-check mr-1"></span>
				{App\Language::translate('LBL_SAVE','Settings:SystemWarnings')}
			</button>
			<button type="button" class="btn btn-danger cancel">
				<span class="fas fa-ban mr-1"></span>
				{App\Language::translate('LBL_REMIND_LATER','Settings:SystemWarnings')}
			</button>
		</div>
	</form>
{/strip}
