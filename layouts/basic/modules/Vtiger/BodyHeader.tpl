{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
    {assign var='count' value=0}
	<nav class="navbar navbar-expand-md navbar-dark fixed-top px-2 bodyHeader{if $LEFTPANELHIDE} menuOpen{/if}">
		{if AppConfig::performance('GLOBAL_SEARCH')}
		<div class="searchMenuBtn d-xl-none">
			<div class="quickAction">
				<a class="btn btn-light" href="#">
					<span aria-hidden="true" class="fas fa-search"></span>
				</a>
			</div>
		</div>
		<div class="input-group input-group-sm mb-2 d-none d-xl-flex globalSearchInput">
			<div class="input-group-prepend">
				<select class="chzn-select basicSearchModulesList form-control" title="{\App\Language::translate('LBL_SEARCH_MODULE')}">
					<option value="">{\App\Language::translate('LBL_ALL_RECORDS')}</option>
					{foreach key=SEARCHABLE_MODULE item=fieldObject from=$SEARCHABLE_MODULES}
						{if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $SEARCHABLE_MODULE && $SEARCHED_MODULE !== 'All'}
							<option value="{$SEARCHABLE_MODULE}" selected>{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
						{else}
							<option value="{$SEARCHABLE_MODULE}">{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
						{/if}
					{/foreach}
				</select>
			</div>
			<input type="text" class="form-control globalSearchValue" title="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" placeholder="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" results="10" data-operator="contains" />
			<div class="input-group-append bg-white rounded-right">
				<button class="btn btn-outline-dark border-0 searchIcon" type="button">
					<span class="fas fa-search"></span>
				</button>
				{if AppConfig::search('GLOBAL_SEARCH_OPERATOR')}
					<div class="btn-group">
						<button type="button" class="btn btn-outline-dark border-bottom-0 border-top-0 dropdown-toggle rounded-0 border-left border-right" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<span class="fas fa-crosshairs"></span>
						</button>
						<ul class="dropdown-menu globalSearchOperator">
							<li class="active"><a href="#" data-operator="contains">{\App\Language::translate('contains')}</a></li>
							<li><a href="#" data-operator="starts">{\App\Language::translate('starts with')}</a></li>
							<li><a href="#" data-operator="ends">{\App\Language::translate('ends with')}</a></li>
						</ul>
					</div>
				{/if}
				<button class="btn btn-outline-dark border-0 globalSearch" title="{\App\Language::translate('LBL_ADVANCE_SEARCH')}" type="button">
					<span class="fas fa-th-large"></span>
				</button>
			</div>
		</div>
		{/if}
		{if !Settings_ModuleManager_Library_Model::checkLibrary('roundcube')}
			<div class="float-right">
				{assign var=CONFIG value=Settings_Mail_Config_Model::getConfig('mailIcon')}
				{if $CONFIG['showMailIcon']=='true' && App\Privilege::isPermitted('OSSMail')}
					{assign var=AUTOLOGINUSERS value=OSSMail_Autologin_Model::getAutologinUsers()}
					{if count($AUTOLOGINUSERS) > 0}
						{assign var=MAIN_MAIL value=OSSMail_Module_Model::getDefaultMailAccount($AUTOLOGINUSERS)}
						<div class="headerLinksMails" id="OSSMailBoxInfo" {if $CONFIG['showNumberUnreadEmails']=='true'}data-numberunreademails="true" data-interval="{$CONFIG['timeCheckingMail']}"{/if}>
							<div class="btn-group">
								{if count($AUTOLOGINUSERS) eq 1}
									<a type="button" class="btn btn-sm btn-light" title="{$MAIN_MAIL.username}" href="index.php?module=OSSMail&view=Index">
										<div class="d-none d-sm-none d-md-block">
											{$ITEM.username}
											<span class="mail_user_name">{$MAIN_MAIL.username}</span>
											<span data-id="{$MAIN_MAIL.rcuser_id}" class="noMails"></span>
										</div>
										<div class="d-none d-block d-sm-block d-md-none">
											<span class="fas fa-inbox"></span>
										</div>
									</a>
								{elseif $CONFIG['showMailAccounts']=='true'}
									<select class="form-control" title="{\App\Language::translate('LBL_SEARCH_MODULE', $MODULE_NAME)}">
										{foreach key=KEY item=ITEM from=$AUTOLOGINUSERS}
											<option value="{$KEY}" {if $ITEM.active}selected{/if} data-id="{$KEY}" data-nomail="" class="noMails">
												{$ITEM.username}
											</option>
										{/foreach}
									</select>
								{/if}
							</div>
						</div>
					{/if}
				{/if}
			</div>
		{/if}

	</nav>
{/strip}
