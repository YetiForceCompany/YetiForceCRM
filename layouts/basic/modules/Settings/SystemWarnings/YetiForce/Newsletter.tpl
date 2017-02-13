{strip}
	<h3 class="marginTB3">
		{App\Language::translate('LBL_SAVE_TO_NEWSLETTER','Settings:SystemWarnings')}
	</h3>
	<p>{App\Language::translate('LBL_NEWSLETTER_DESC','Settings:SystemWarnings')}</p>
	<form class="form-horizontal row validateForm" method="post" action="index.php">
		<div class="form-group">
			<label class="col-sm-3 control-label"><span class="redColor">*</span>{App\Language::translate('First Name')}</label>
			<div class="col-sm-9">
				<input type="text" name="first_name" class="form-control" placeholder="{App\Language::translate('First Name')}" data-validation-engine="validate[required]">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">{App\Language::translate('Last Name')}</label>
			<div class="col-sm-9">
				<input type="text" name="last_name" class="form-control" placeholder="{App\Language::translate('Last Name')}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label"><span class="redColor">*</span>{App\Language::translate('LBL_EMAIL_ADRESS')}</label>
			<div class="col-sm-9">
				<input type="text" name="email" class="form-control" placeholder="{App\Language::translate('LBL_EMAIL_ADRESS')}" data-validation-engine="validate[required,custom[email]]">
			</div>
		</div>
		<div class="pull-right">
			<button type="button" class="btn btn-success ajaxBtn">
				<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
				&nbsp;&nbsp;{App\Language::translate('LBL_SAVE','Settings:SystemWarnings')}
			</button>&nbsp;&nbsp;
			<button type="button" class="btn btn-danger cancel">
				<span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span>
				&nbsp;&nbsp;{App\Language::translate('LBL_REMIND_LATER','Settings:SystemWarnings')}
			</button>
		</div>
	</form>
{/strip}
