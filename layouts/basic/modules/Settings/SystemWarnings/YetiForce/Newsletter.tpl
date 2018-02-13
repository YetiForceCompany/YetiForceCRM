{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<h3 class="marginTB3">
		{App\Language::translate('LBL_SAVE_TO_NEWSLETTER','Settings:SystemWarnings')}
	</h3>
	<p>{App\Language::translate('LBL_NEWSLETTER_DESC','Settings:SystemWarnings')}</p>
	<form class="form-horizontal row validateForm" method="post" action="index.php">
		<div class="form-group">
			<label class="col-sm-3 col-form-label"><span class="redColor">*</span>{App\Language::translate('First Name')}</label>
			<div class="col-sm-9">
				<input type="text" name="first_name" class="form-control" placeholder="{App\Language::translate('First Name')}" data-validation-engine="validate[required]">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 col-form-label">{App\Language::translate('Last Name')}</label>
			<div class="col-sm-9">
				<input type="text" name="last_name" class="form-control" placeholder="{App\Language::translate('Last Name')}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 col-form-label"><span class="redColor">*</span>{App\Language::translate('LBL_EMAIL_ADRESS')}</label>
			<div class="col-sm-9">
				<input type="text" name="email" class="form-control" placeholder="{App\Language::translate('LBL_EMAIL_ADRESS')}" data-validation-engine="validate[required,custom[email]]">
			</div>
		</div>
		<div class="float-right">
			<button type="button" class="btn btn-success ajaxBtn">
				<span class="fas fa-check"></span>
				&nbsp;&nbsp;{App\Language::translate('LBL_SAVE','Settings:SystemWarnings')}
			</button>&nbsp;&nbsp;
			<button type="button" class="btn btn-danger cancel">
				<span class="fas fa-ban"></span>
				&nbsp;&nbsp;{App\Language::translate('LBL_REMIND_LATER','Settings:SystemWarnings')}
			</button>
		</div>
	</form>
{/strip}
