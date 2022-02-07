{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailRbl-Index -->
	<div class="js-base-container" data-js="container">
		<div class="o-breadcrumb widget_header row mb-2 ">
			<div class="col-md-6">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="col-md-6 mt-1">
				<button class="btn btn-primary btn-sm mr-2 js-show-modal float-right" data-url="index.php?module=MailRbl&parent=Settings&view=ConfigModal" title="{\App\Language::translate('BTN_RBL_CONFIG', $QUALIFIED_MODULE)}" data-js="click">
					<span class="fas fa-cogs"></span>
				</button>
				<button class="btn btn-success btn-sm mr-2 js-show-modal float-right" data-js="click" data-url="index.php?module=MailRbl&parent=Settings&view=UploadListModal">
					<span class="fas fa-download mr-2"></span>{\App\Language::translate('BTN_IMPORT_LIST', $QUALIFIED_MODULE)}
				</button>
				<a href="https://soc.yetiforce.com/" target="_blank" class="btn btn-outline-info btn-sm float-right mr-3 js-popover-tooltip" data-content="YetiForce Security Operations Center (SOC)" rel="noreferrer noopener" data-js="popover">
					<span class="mdi mdi-book-open-page-variant u-fs-lg"></span>
				</a>
			</div>
		</div>
		<div>
			<ul id="tabs" class="nav nav-tabs nav-justified my-2 mr-0" role="tablist" data-tabs="tabs">
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'forVerification'}active{/if}" href="#forVerification" data-name="forVerification" data-toggle="tab">
						<span class="far fa-question-circle mr-2"></span>{\App\Language::translate('LBL_FOR_VERIFICATION', $QUALIFIED_MODULE)}
						<span class="badge badge-warning badge-pill ml-2 js-badge" data-js="container"></span>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'toSend'}active{/if}" href="#toSend" data-name="toSend" data-toggle="tab">
						<span class="fas fa-paper-plane mr-2"></span>{\App\Language::translate('LBL_TO_SEND', $QUALIFIED_MODULE)}
						<span class="badge badge-warning badge-pill ml-2 js-badge" data-js="container"></span>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'request'}active{/if}" href="#request" data-name="request" data-toggle="tab">
						<span class="fas fa-border-all mr-2"></span>{\App\Language::translate('LBL_REQUEST_LIST', $QUALIFIED_MODULE)}
						<span class="badge badge-warning badge-pill ml-2 js-badge" data-js="container"></span>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'blackList'}active{/if}" href="#blackList" data-name="blackList" data-toggle="tab">
						<span class="fas fa-ban mr-2"></span>{\App\Language::translate('LBL_BLACK_LIST', $QUALIFIED_MODULE)}
						<span class="badge badge-warning badge-pill ml-2 js-badge" data-js="container"></span>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'whiteList'}active{/if}" href="#whiteList" data-name="whiteList" data-toggle="tab">
						<span class="far fa-check-circle mr-2"></span>{\App\Language::translate('LBL_WHITE_LIST', $QUALIFIED_MODULE)}
						<span class="badge badge-warning badge-pill ml-2 js-badge" data-js="container"></span>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'publicRbl'}active{/if}" href="#publicRbl" data-name="publicRbl" data-toggle="tab">
						<span class="fas fa-globe mr-2"></span>{\App\Language::translate('LBL_PUBLIC_RBL', $QUALIFIED_MODULE)}
						<span class="badge badge-warning badge-pill ml-2 js-badge" data-js="container"></span>
						<span class="yfi-premium u-fs-26px color-red-600 float-right js-popover-tooltip" data-class="u-min-w-500px" data-content="{\App\Purifier::encodeHtml(App\Language::translateArgs('LBL_SYNCH_PAID_FEATURE', $QUALIFIED_MODULE,"<a target=\"_blank\" href=\"index.php?module=YetiForce&parent=Settings&view=Shop&product=YetiForceRbl&mode=showProductModal\">{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>" ))}"></span>
				</a>
			</li>
		</ul>
	</div>
	<div id="my-tab-content" class="tab-content">
		<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'forVerification'}active{/if}" id="forVerification" data-name="forVerification" data-js="data">
			<form class="js-filter-form form-inline" data-js="container">
				<div class="input-group col-lg-2 col-md-6 col-12 px-0 mb-lg-0 mb-sm-2 mb-2">
					<div class="input-group-prepend">
						<span class="input-group-text" id="rblInputDate1">
							<span class="fas fa-calendar-alt mr-2"></span>
							{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}
						</span>
					</div>
					<input name="date" type="text" class="dateRangeField dateFilter form-control text-center" data-calendar-type="range" value="{$DATE}" aria-describedby="rblInputDate1" />
				</div>
				<div class="input-group col-lg-3 col-md-6 col-12 pl-lg-2 pl-md-0 px-0 mb-md-0 mb-2">
					<div class="input-group-prepend">
						<span class="input-group-text" id="rblTypeList1">
							<span class="yfi yfi-field-folders mr-2"></span>
							{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}
						</span>
					</div>
					<select class="form-control select2" multiple="true" name="type[]" aria-describedby="rblTypeList1">
						{foreach from=\App\Mail\Rbl::LIST_TYPES key=KEY item=TYPE}
						{if $TYPE['label'] neq 'LBL_PUBLIC_WHITE_LIST' && $TYPE['label'] neq LBL_PUBLIC_BLACK_LIST}
						<option value="{$KEY}">
							{\App\Language::translate($TYPE['label'], $QUALIFIED_MODULE)}
						</option>
						{/if}
						{/foreach}
					</select>
				</div>
				<div class="input-group col-lg-4 col-md-6 col-12 pl-md-2 pl-sm-0 px-0">
					<div class="input-group-prepend">
						<span class="input-group-text" id="rblUsersList1">
							<span class="yfi yfi-users-2 mr-2"></span>
							{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}
						</span>
					</div>
					<select class="form-control select2" multiple="true" name="users[]" aria-describedby="rblUsersList1">
						{foreach from=\Users_Record_Model::getAll() key=USER_ID item=USER}
						<option value="{$USER_ID}">
							{$USER->getDisplayName()} ({$USER->getRoleDetail()->get('rolename')})
						</option>
						{/foreach}
					</select>
				</div>
			</form>
			<table id="verification-table" class="table table-sm table-striped display js-data-table text-center mt-2 o-tab__container">
				<thead>
					<tr>
						<th>{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_SENDER', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_RECIPIENT', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_IP_ADDRESS')}</th>
						<th>{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}</th>
						<th class="u-w-158px">{\App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
			</table>
		</div>
		<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'toSend'}active{/if}" id="toSend" data-name="toSend" data-js="data">
			<form class="js-filter-form form-inline" data-js="container">
				<div class="input-group col-lg-2 col-md-6 col-12 px-0 mb-lg-0 mb-sm-2 mb-2">
					<div class="input-group-prepend">
						<span class="input-group-text" id="rblInputDate3">
							<span class="fas fa-calendar-alt mr-2"></span>
							{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}
						</span>
					</div>
					<input name="date" type="text" class="dateRangeField dateFilter form-control text-center" data-calendar-type="range" value="{$DATE}" aria-describedby="rblInputDate3" />
				</div>
				<div class="input-group col-lg-3 col-md-6 col-12 pl-lg-2 pl-md-0 px-0 mb-md-0 mb-2">
					<div class="input-group-prepend">
						<span class="input-group-text" id="rblTypeList3">
							<span class="yfi yfi-field-folders mr-2"></span>
							{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}
						</span>
					</div>
					<select class="form-control select2" multiple="true" name="type[]" aria-describedby="rblTypeList3">
						{foreach from=\App\Mail\Rbl::LIST_TYPES key=KEY item=TYPE}
						{if $TYPE['label'] neq 'LBL_PUBLIC_WHITE_LIST' && $TYPE['label'] neq LBL_PUBLIC_BLACK_LIST}
						<option value="{$KEY}">
							{\App\Language::translate($TYPE['label'], $QUALIFIED_MODULE)}
						</option>
						{/if}
						{/foreach}
					</select>
				</div>
				<div class="input-group col-lg-4 col-md-6 col-12 pl-md-2 pl-sm-0 px-0">
					<div class="input-group-prepend">
						<span class="input-group-text" id="rblUsersList3">
							<span class="yfi yfi-users-2 mr-2"></span>
							{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}
						</span>
					</div>
					<select class="form-control select2" multiple="true" name="users[]" aria-describedby="rblUsersList3">
						{foreach from=\Users_Record_Model::getAll() key=USER_ID item=USER}
						<option value="{$USER_ID}">
							{$USER->getDisplayName()} ({$USER->getRoleDetail()->get('rolename')})
						</option>
						{/foreach}
					</select>
				</div>
			</form>
			<table id="send-table" class="table table-sm table-striped display js-data-table text-center mt-2 o-tab__container">
				<thead>
					<tr>
						<th>{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_SENDER', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_RECIPIENT', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_IP_ADDRESS')}</th>
						<th>{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}</th>
						<th class="u-w-158px">{\App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
			</table>
		</div>
		<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'request'}active{/if}" id="request" data-name="request" data-js="data">
			<form class="js-filter-form form-inline" data-js="container">
				<div class="input-group col-lg-2 col-md-6 col-12 px-0 mb-lg-0 mb-sm-2 mb-2">
					<div class="input-group-prepend">
						<span class="input-group-text" id="rblInputDate2">
							<span class="fas fa-calendar-alt mr-2"></span>
							{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}
						</span>
					</div>
					<input name="date" type="text" class="dateRangeField dateFilter form-control text-center" data-calendar-type="range" value="{$DATE}" aria-describedby="rblInputDate2" />
				</div>
				<div class="input-group col-lg-3 col-md-6 col-12 pl-md-2 pl-sm-0 px-0 mb-lg-0 mb-sm-2 mb-2">
					<div class="input-group-prepend">
						<span class="input-group-text" id="rblStatusList">
							<span class="fas fa-stream mr-2"></span>
							{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}
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
						<span class="input-group-text" id="rblTypeList2">
							<span class="yfi yfi-field-folders mr-2"></span>
							{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}
						</span>
					</div>
					<select class="form-control select2" multiple="true" name="type[]" aria-describedby="rblTypeList2">
						{foreach from=\App\Mail\Rbl::LIST_TYPES key=KEY item=TYPE}
						{if $TYPE['label'] neq 'LBL_PUBLIC_WHITE_LIST' && $TYPE['label'] neq LBL_PUBLIC_BLACK_LIST}
						<option value="{$KEY}">
							{\App\Language::translate($TYPE['label'], $QUALIFIED_MODULE)}
						</option>
						{/if}
						{/foreach}
					</select>
				</div>
				<div class="input-group col-lg-4 col-md-6 col-12 pl-md-2 pl-sm-0 px-0">
					<div class="input-group-prepend">
						<span class="input-group-text" id="rblUsersList2">
							<span class="yfi yfi-users-2 mr-2"></span>
							{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}
						</span>
					</div>
					<select class="form-control select2" multiple="true" name="users[]" aria-describedby="rblUsersList2">
						{foreach from=\Users_Record_Model::getAll() key=USER_ID item=USER}
						<option value="{$USER_ID}">
							{$USER->getDisplayName()} ({$USER->getRoleDetail()->get('rolename')})
						</option>
						{/foreach}
					</select>
				</div>
			</form>
			<table id="request-table" class="table table-sm table-striped display js-data-table text-center mt-2 o-tab__container">
				<thead>
					<tr>
						<th>{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_SENDER', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_RECIPIENT', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_IP_ADDRESS')}</th>
						<th>{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('Status', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_USER', $QUALIFIED_MODULE)}</th>
						<th class="u-w-158px">{\App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
			</table>
		</div>
		<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'blackList'}active{/if}" id="blackList" data-name="blackList" data-js="data">
			{include file=\App\Layout::getTemplatePath('SearchFormList.tpl', $QUALIFIED_MODULE) ID='blackList'}
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
			{include file=\App\Layout::getTemplatePath('SearchFormList.tpl', $QUALIFIED_MODULE) ID='whiteList'}
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
			{include file=\App\Layout::getTemplatePath('SearchFormList.tpl', $QUALIFIED_MODULE) ID='publicRbl'}
			<table id="publicRbl-table" class="table table-sm table-striped display js-data-table text-center mt-2">
					<thead>
						<tr>
							<th>{\App\Language::translate('LBL_IP', $QUALIFIED_MODULE)}</th>
							<th>{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}</th>
							<th>{\App\Language::translate('Status', $QUALIFIED_MODULE)}</th>
							<th>{\App\Language::translate('LBL_SERVER_COMMENTS', $QUALIFIED_MODULE)}</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-MailRbl-Index -->
{/strip}
