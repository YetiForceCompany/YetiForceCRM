{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailRbl-Index -->
	<div>
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-md-10">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="col-md-2">
				<button class="btn btn-primary mt-1 js-check-php float-right" data-js="click">
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
				{include file=\App\Layout::getTemplatePath('SearchForm.tpl', $QUALIFIED_MODULE) STATUS_LIST=$RBL_STATUS_LIST TYPE_LIST=$RBL_TYPE_LIST}
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
						<th>{\App\Language::translate('LBL_MAIL_HEADERS', $QUALIFIED_MODULE)}</th>
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
						<th>{\App\Language::translate('LBL_MAIL_HEADERS', $QUALIFIED_MODULE)}</th>
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
