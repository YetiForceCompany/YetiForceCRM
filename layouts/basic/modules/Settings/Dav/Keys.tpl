{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Dav-Keys -->
	<div class="" id="DavKeysContainer">
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-4 d-flex justify-content-end align-items-center">
				<a href="https://yetiforce.com/en/knowledge-base/documentation/administrator-documentation/category/dav-applications" target="_blank" class="btn btn-outline-info float-right mr-3 js-popover-tooltip" data-content="{App\Language::translate('BTM_GOTO_YETIFORCE_DOCUMENTATION')}" rel="noreferrer noopener" data-js="popover">
					<span class="mdi mdi-book-open-page-variant u-fs-lg"></span>
				</a>
				<button class="btn btn-primary js-add-key" data-js="click">
					<span class="fas fa-plus mr-1"></span>{\App\Language::translate('LBL_ADD_KEY',$QUALIFIED_MODULE)}
				</button>
			</div>
		</div>
		{if !\App\YetiForce\Register::isRegistered()}
			<div class="col-md-12">
				<div class="alert alert-danger">
					<span class="yfi yfi-yeti-register-alert color-red-600 u-fs-5x mr-4 float-left"></span>
					<h1 class="alert-heading">{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_TITLE',$QUALIFIED_MODULE)}</h1>
					{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_DESC', $QUALIFIED_MODULE)}
				</div>
			</div>
		{else}
			{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceDav')}
			{if $CHECK_ALERT}
				<div class="alert alert-warning">
					<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
					{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceDav&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
				</div>
			{/if}
			<div class="contents">
				{if $ENABLEDAV}
					<div class="alert alert-warning">
						<button type="button" class="close" data-dismiss="alert">×</button>
						<h5 class="alert-heading">{\App\Language::translate('LBL_ALERT_DAV_NO_ACTIVE_TITLE', $QUALIFIED_MODULE)}</h5>
						<p>{\App\Language::translate('LBL_ALERT_DAV_NO_ACTIVE_DESC', $QUALIFIED_MODULE)}</p>
					</div>
				{/if}
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h5 class="alert-heading">
						<span class="mdi mdi-information-outline u-fs-2em mr-2 float-left"></span>
						{\App\Language::translate('LBL_ALERT_DAV_CONFIG_TITLE', $QUALIFIED_MODULE)}
					</h5>
					<p>{\App\Language::translate('LBL_ALERT_DAV_CONFIG_DESC', $QUALIFIED_MODULE)}</p>
					<ul>
						<li>{App\Config::main('site_URL')}dav.php/addressbooks/(__dav_login__)/YFAddressBook/</li>
						<li>{App\Config::main('site_URL')}dav.php/calendars/(__dav_login__)/YFCalendar/</li>
						<li>{App\Config::main('site_URL')}dav.php/principals/(__dav_login__)/</li>
					</ul>
					<h6><a href="https://www.davx5.com/download" target="_blank" rel="noreferrer noopener">DAVdroid</a></h6>
					<ul>
						<li>{App\Config::main('site_URL')}dav.php</li>
					</ul>
					<h6><a href="https://addons.thunderbird.net/pl/thunderbird/addon/lightning/" target="_blank" rel="noreferrer noopener">Thunderbird Lightning</a>, <a href="http://caldavsynchronizer.org/" target="_blank" rel="noreferrer noopener">Outlook CalDav Synchronizer</a></h6>
					<ul>
						<li>{App\Config::main('site_URL')}dav.php/calendars/(__dav_login__)/YFCalendar/</li>
					</ul>
					<h6><a href="https://addons.thunderbird.net/en-US/thunderbird/addon/cardbook/" target="_blank" rel="noreferrer noopener">Thunderbird CardBook</a>, <a href="http://caldavsynchronizer.org/" target="_blank" rel="noreferrer noopener">Outlook CalDav Synchronizer</a></h6>
					<ul>
						<li>{App\Config::main('site_URL')}dav.php/addressbooks/(__dav_login__)/YFAddressBook/</li>
					</ul>
					<h6>iOS</h6>
					<ul>
						<li>https: {str_replace('https://','http://',rtrim(App\Config::main('site_URL'),'/'))}:443/dav.php/principals/(__dav_login__)/</li>
					</ul>
				</div>
				<div>
					<div class="contents tabbable">
						<table class="table table-bordered  tableRWD table-sm listViewEntriesTable">
							<thead>
								<tr class="blockHeader">
									<th><strong>{\App\Language::translate('LBL_LOGIN',$QUALIFIED_MODULE)}</strong></th>
									<th><strong>{\App\Language::translate('LBL_KEY',$QUALIFIED_MODULE)}</strong></th>
									<th><strong>{\App\Language::translate('LBL_DISPLAY_NAME',$QUALIFIED_MODULE)}</strong></th>
									<th><strong>{\App\Language::translate('LBL_EMAIL',$QUALIFIED_MODULE)}</strong></th>
									<th><strong>{\App\Language::translate('LBL_ACTIVE_USER',$QUALIFIED_MODULE)}</strong></th>
									<th><strong>{\App\Language::translate('CardDAV',$QUALIFIED_MODULE)}</strong></th>
									<th><strong>{\App\Language::translate('CalDAV',$QUALIFIED_MODULE)}</strong></th>
									<th><strong>{\App\Language::translate('WebDAV',$QUALIFIED_MODULE)}</strong></th>
									<th><strong>{\App\Language::translate('LBL_COUNT_CARD',$QUALIFIED_MODULE)}</strong></th>
									<th><strong>{\App\Language::translate('LBL_COUNT_CAL',$QUALIFIED_MODULE)}</strong></th>
									<th><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
								</tr>
							</thead>
							<tbody>
								{assign var=AMOUNT_DATA value=$MODULE_MODEL->getAmountData()}
								{foreach from=$MODULE_MODEL->getAllKeys() item=RECORD}
									{if !empty($AMOUNT_DATA['addressbook'][$RECORD['addressbooksid']])}
										{assign var=ADDRESSBOOK value=$AMOUNT_DATA['addressbook'][$RECORD['addressbooksid']]}
									{else}
										{assign var="ADDRESSBOOK" value=""}
									{/if}
									{if !empty($AMOUNT_DATA['calendar'][$RECORD['calendarsid']])}
										{assign var=CALENDAR value=$AMOUNT_DATA['calendar'][$RECORD['calendarsid']]}
									{else}
										{assign var=CALENDAR value=""}
									{/if}
									<tr data-user="{$RECORD['userid']}" class="js-tr-row" data-js="data/remove">
										<td>
											{$RECORD['user_name']}
											<button class="btn btn-sm btn-primary clipboard ml-2" title="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD')}" data-copy-attribute="clipboard-text" data-clipboard-text="{$RECORD['user_name']}">
												<span class="fas fa-copy"></span>
											</button>
										</td>
										<td>
											**********
											<button class="btn btn-sm btn-primary clipboard ml-2" title="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD')}" data-copy-attribute="clipboard-text" data-clipboard-text="{App\Encryption::getInstance()->decrypt($RECORD['key'])}">
												<span class="fas fa-copy"></span>
											</button>
										</td>
										<td>{$RECORD['displayname']}</td>
										<td>{$RECORD['email']}</td>
										<td>{\App\Language::translate($RECORD['status'],'Users')}</td>
										<td>{if $RECORD['addressbooksid']}{\App\Language::translate('LBL_YES')}{else}{\App\Language::translate('LBL_NO')}{/if}</td>
										<td>{if $RECORD['calendarsid']}{\App\Language::translate('LBL_YES')}{else}{\App\Language::translate('LBL_NO')}{/if}</td>
										<td>{\App\Language::translate('LBL_YES')}</td>
										<td>{if $ADDRESSBOOK}{$ADDRESSBOOK}{else}0{/if}</td>
										<td>{if $CALENDAR}{$CALENDAR}{else}0{/if}</td>
										<td>
											<button class="btn btn-danger btn-sm js-delete-key ml-2 mr-1" data-js="click">
												<span class="fas fa-trash mr-1"></span>{\App\Language::translate('LBL_DELETE_KEY',$QUALIFIED_MODULE)}
											</button>
											<button class="btn btn-sm js-popover-tooltip" data-toggle="popover" data-js="popover" data-content="{App\Config::main('site_URL')}dav.php/principals/{$RECORD['user_name']}/">
												<span class="fas fa-info-circle"></span>
											</button>

										</td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</div>
				<div class="modal addKeyContainer fade" tabindex="-1">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">
									<span class="fas fa-plus fa-sm mr-2"></span>{\App\Language::translate('LBL_ADD_KEY', $QUALIFIED_MODULE)}
								</h5>
								<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form class="form-horizontal">
									<div class="form-group form-row">
										<label class="col-sm-5 col-form-label u-text-small-bold">{\App\Language::translate('LBL_SELECT_USER', $QUALIFIED_MODULE)}</label>
										<div class="col-sm-7 controls">
											<select class="select user form-control" name="user"
												data-validation-engine="validate[required]">
												{foreach from=$USERS item=ITEM key=KEY}
													<option value="{$KEY}">{$ITEM->getDisplayName()}</option>
												{/foreach}
											</select>
										</div>
									</div>
									<div class="form-group form-row">
										<label class="col-sm-5 col-form-label u-text-small-bold">{\App\Language::translate('LBL_SELECT_TYPE', $QUALIFIED_MODULE)}</label>
										<div class="col-sm-7 controls">
											<select multiple="" class="select type form-control" name="type">
												{foreach from=$MODULE_MODEL->getTypes() item=ITEM}
													<option selected="" value="{$ITEM}">{$ITEM}</option>
												{/foreach}
											</select>
										</div>
									</div>
								</form>
							</div>
							{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
						</div>
					</div>
				</div>
			</div>
		{/if}
	</div>
	<!-- /tpl-Settings-Dav-Keys -->
{/strip}
