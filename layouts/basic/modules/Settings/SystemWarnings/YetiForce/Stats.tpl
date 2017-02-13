{strip}
	<form class="form-horizontal row validateForm" method="post" action="index.php">
		<h3 class="marginTB3">
			{App\Language::translate('LBL_STATS','Settings:SystemWarnings')}
		</h3>
		<p>{App\Language::translate('LBL_STATS_DESC','Settings:SystemWarnings')}</p>
		{assign var=COMPANY value=\App\Company::getInstanceById()}
		<div class="input-group">
			<span class="input-group-addon">
				<input type="checkbox" checked>
			</span>
			<input type="text" name="company_name" class="form-control" placeholder="{App\Language::translate('LBL_NAME','Settings:Companies')}" value="{$COMPANY->get('name')}">
		</div><br>
		<div class="input-group">
			<span class="input-group-addon">
				<input type="checkbox" checked>
			</span>
			<input type="text" name="company_city" class="form-control" placeholder="{App\Language::translate('LBL_CITY','Settings:Companies')}" value="{$COMPANY->get('city')}">
		</div><br>
		<div class="input-group">
			<span class="input-group-addon">
				<input type="checkbox" checked>
			</span>
			<input type="text" name="company_country" class="form-control" placeholder="{App\Language::translate('LBL_COUNTRY','Settings:Companies')}" value="{$COMPANY->get('country')}">
		</div><br>
		<div class="input-group">
			<span class="input-group-addon">
				<input type="checkbox" checked>
			</span>
			<input type="text" name="company_website" class="form-control" placeholder="{App\Language::translate('LBL_WEBSITE','Settings:Companies')}" value="{$COMPANY->get('website')}">
		</div><br>
		<div class="input-group">
			<span class="input-group-addon">
				<input type="checkbox" checked>
			</span>
			<input type="text" name="company_email" class="form-control" placeholder="{App\Language::translate('LBL_EMAIL','Settings:Companies')}" value="{$COMPANY->get('email')}">
		</div><br>
		<div class="pull-right">
			<button type="button" class="btn btn-success ajaxBtn">
				<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
				&nbsp;&nbsp;{App\Language::translate('LBL_SEND','Settings:SystemWarnings')}
			</button>&nbsp;&nbsp;
			<button type="button" class="btn btn-danger cancel">
				<span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span>
				&nbsp;&nbsp;{App\Language::translate('LBL_REMIND_LATER','Settings:SystemWarnings')}
			</button>
		</div>
		<div class="clearfix"></div>
	</form>
{/strip}
