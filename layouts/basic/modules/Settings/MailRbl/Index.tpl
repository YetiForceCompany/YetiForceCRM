{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailRbl-Index -->
	<div>
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-md-10">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="col-md-2">
				<button class="btn btn-primary mt-1 js-send-request float-right" data-js="click">
					<span class="fas fa-paper-plane mr-2"></span>{\App\Language::translate('BTN_SEND_SPAM_REQUEST', $QUALIFIED_MODULE)}
				</button>
			</div>
		</div>
		<div>
			<ul id="tabs" class="nav nav-tabs nav-justified my-2 mr-0" role="tablist" data-tabs="tabs">
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'request'}active{/if}" href="#request" data-toggle="tab">
						<span class="far fa-question-circle mr-2"></span>{\App\Language::translate('LBL_REQUEST_LIST', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'blackList'}active{/if}" href="#blackList" data-toggle="tab">
						<span class="fas fa-ban mr-2"></span>{\App\Language::translate('LBL_BLACK_LIST', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'whiteList'}active{/if}" href="#whiteList" data-toggle="tab">
						<span class="far fa-check-circle mr-2"></span>{\App\Language::translate('LBL_WHITE_LIST', $QUALIFIED_MODULE)}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'publicRbl'}active{/if}" href="#publicRbl" data-toggle="tab">
						<span class="fas fa-globe mr-2"></span>{\App\Language::translate('LBL_PUBLIC_RBL', $QUALIFIED_MODULE)}
					</a>
				</li>
			</ul>
		</div>
		<div id="my-tab-content" class="tab-content">
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'request'}active{/if}" id="request" data-name="request" data-js="data">
				<form class="js-filter-form form-inline" data-js="container">
					<div class="input-group col-lg-2 col-md-6 col-12 px-0 mb-lg-0 mb-sm-2 mb-2">
						<div class="input-group-prepend">
							<span class="input-group-text" id="rblInputDate">
								<span class="fas fa-calendar-alt mr-2"></span>
								{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}
							</span>
						</div>
						<input name="date" type="text" class="dateRangeField dateFilter form-control text-center" data-calendar-type="range" value="{$DATE}" aria-describedby="rblInputDate"/>
					</div>
					<div class="input-group col-lg-3 col-md-6 col-12 pl-md-2 pl-sm-0 px-0 mb-lg-0 mb-sm-2 mb-2">
						<div class="input-group-prepend">
							<span class="input-group-text" id="rblStatusList">
								<span class="fas fa-stream mr-2"></span>
								{\App\Language::translate('Status', $QUALIFIED_MODULE)}
							</span>
						</div>
						<select id="rblStatus" class="form-control select2" multiple="true" name="status[]" aria-describedby="rblStatusList">
							{foreach from=\App\Mail\Rbl::REQUEST_STATUS key=KEY item=STATUS}
								<option value="{$KEY}">
									{\App\Language::translate($STATUS['label'], $QUALIFIED_MODULE)}
								</option>
							{/foreach}
						</select>
					</div>
					<div class="input-group col-lg-3 col-md-6 col-12 pl-lg-2 pl-md-0 px-0 mb-md-0 mb-2">
						<div class="input-group-prepend">
							<span class="input-group-text" id="rblTypeList">
								<span class="yfi yfi-field-folders mr-2"></span>
								{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}
							</span>
						</div>
						<select id="rblTypeList" class="form-control select2" multiple="true" name="type[]" aria-describedby="rblTypeList">
							{foreach from=\App\Mail\Rbl::LIST_TYPES key=KEY item=TYPE}
								<option value="{$KEY}">
									{\App\Language::translate($TYPE['label'], $QUALIFIED_MODULE)}
								</option>
							{/foreach}
						</select>
					</div>
					<div class="input-group col-lg-4 col-md-6 col-12 pl-md-2 pl-sm-0 px-0">
						<div class="input-group-prepend">
							<span class="input-group-text" id="rblUsersList">
								<span class="yfi yfi-users-2 mr-2"></span>
								{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}
							</span>
						</div>
						<select id="rblUsersList" class="form-control select2" multiple="true" name="users[]" aria-describedby="rblUsersList">
							{foreach from=\Users_Record_Model::getAll() key=USER_ID item=USER}
								<option value="{$USER_ID}">
									{$USER->getDisplayName()} ({$USER->getRoleDetail()->get('rolename')})
								</option>
							{/foreach}
						</select>
					</div>
				</form>
				<table id="request-table" class="table table-sm table-striped display js-data-table text-center mt-2">
				<thead>
					<tr>
						<th>{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_SENDER', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_RECIPIENT', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('Status', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				</table>
			</div>
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'blackList'}active{/if}" id="blackList" data-name="blackList" data-js="data">
				<table id="blackList-table" class="table table-sm table-striped display js-data-table text-center mt-2">
				<thead>
					<tr>
						<th>{\App\Language::translate('LBL_IP', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('Status', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_REQUEST', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				</table>
			</div>
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'whiteList'}active{/if}" id="whiteList" data-name="whiteList" data-js="data">
				<table id="whiteList-table" class="table table-sm table-striped display js-data-table text-center mt-2">
				<thead>
					<tr>
						<th>{\App\Language::translate('LBL_IP', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('Status', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_REQUEST', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				</table>
			</div>
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'publicRbl'}active{/if}" id="publicRbl" data-name="publicRbl" data-js="data">
				<table id="publicRbl-table" class="table table-sm table-striped display js-data-table text-center mt-2">
				<thead>
					<tr>
						<th>{\App\Language::translate('LBL_IP', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('Status', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				</table>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-MailRbl-Index -->
{/strip}
