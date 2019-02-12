{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Github-Github authModal modal fade" tabindex="-1">
		<div class="authModalContent validationEngineContainer ">
			<div class="modal-dialog ">
				<div class="modal-content">
					<div class="modal-header">
						<div class="d-flex w-100 m-0">
							<div>
								<span class="fas fa-user-secret u-mr-5px mt-2 float-left"></span>
								<h5 class="modal-title float-left ml-1">{\App\Language::translate('LBL_AUTHORIZATION', $QUALIFIED_MODULE)}</h5>

							</div>
							<div class="ml-auto">
								<button class="btn btn-sm btn-success saveKeys" type="button" aria-hidden="true">
									<span class="fas fa-check mr-1"></span>
									{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
								</button>
								<button class="btn btn-sm btn-danger ml-1" type="button" data-dismiss="modal"
										aria-label="Close" aria-hidden="true">
									<span class="fas fa-times"></span>
								</button>
							</div>
						</div>
					</div>
					<div class="modal-body row ">
						<div class="col-12">
							<div class="alert alert-warning">
								<ul>
									<li>{\App\Language::translate('LBL_USERNAME_DESCRIPTION', $QUALIFIED_MODULE)}</li>
									<li>{\App\Language::translateArgs('LBL_TOKEN_DESCRIPTION', $QUALIFIED_MODULE,'<a href="https://help.github.com/articles/creating-an-access-token-for-command-line-use" rel="noreferrer noopener">help.github.com</a>')}</li>
								</ul>
							</div>
						</div>
						<div class="col-12">
							<div class="alert alert-danger errorMsg d-none"></div>
						</div>
						<div class="col-12 mb-2">
							<span class="redColor">*</span>
							{\App\Language::translate('LBL_USER_NAME', $QUALIFIED_MODULE)}
							<input class="form-control" name="username" data-validation-engine="validate[required]"
								   value="" type="text">
						</div>
						<div class="col-12">
							<span class="redColor">*</span>
							{\App\Language::translate('LBL_TOKEN', $QUALIFIED_MODULE)}
							<input class="form-control" data-validation-engine="validate[required]" name="token"
								   value="" type="text">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	{if !$GITHUB_CLIENT_MODEL->isAuthorized()}
		<div class="alert alert-danger mb-2" role="alert">
			{\App\Language::translate('LBL_NOT_AUTHORIZED', $QUALIFIED_MODULE)}
			<button class="btn btn-danger showModal ml-2">
				<span class="fas fa-user-secret u-mr-5px"></span>{\App\Language::translate('LBL_AUTHORIZATION', $QUALIFIED_MODULE)}
			</button>
		</div>
	{else}
		<button class="btn btn-primary showModal">
			{\App\Language::translate('LBL_CHANGE_AUTHORIZATION', $QUALIFIED_MODULE)}
		</button>
	{/if}
	{if $GITHUB_ISSUES !== false}
		{if $GITHUB_CLIENT_MODEL->isAuthorized()}
			<div class="float-left">
				<button class="btn btn-primary addIssuesBtn mr-2">
					{\App\Language::translate('LBL_ADD_ISSUES', $QUALIFIED_MODULE)}
				</button>
			</div>
		{/if}
		<div class="listViewActions float-right paginationDiv pl-1 pb-2">
			{include file=\App\Layout::getTemplatePath('Pagination.tpl')}
		</div>
		<div class="col-sm-4 float-right pb-2">
			{if $GITHUB_CLIENT_MODEL->isAuthorized()}
				<div class="bootstrap-switch-container float-right ml-2">
					<div class="btn-group btn-group-toggle" data-toggle="buttons">
						<label class="btn btn-outline-primary {if $IS_AUTHOR}active{/if}">
							<input class="js-switch--author" type="radio" name="author" id="author1" data-js="change"
								   autocomplete="off" {if $IS_AUTHOR}checked{/if}
							> {\App\Language::translate('LBL_ME', $QUALIFIED_MODULE)}
						</label>
						<label class="btn btn-outline-primary {if !$IS_AUTHOR}active{/if}">
							<input class="js-switch--author" type="radio" name="author" id="author2" data-js="change"
								   autocomplete="off" {if !$IS_AUTHOR}checked{/if}
							> {\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}
						</label>
					</div>
				</div>
			{/if}
			<div class="bootstrap-switch-container float-right">
				<div class="btn-group btn-group-toggle" data-toggle="buttons">
					<label class="btn btn-outline-primary {if $ISSUES_STATE neq 'closed'}active{/if}">
						<input class="js-switch--state" type="radio" name="state" id="state1" data-js="change"
							   autocomplete="off" {if $ISSUES_STATE neq 'closed'}checked{/if}
						> {\App\Language::translate('LBL_OPEN', $QUALIFIED_MODULE)}
					</label>
					<label class="btn btn-outline-primary {if $ISSUES_STATE eq 'closed'}active{/if}">
						<input class="js-switch--state" type="radio" name="state" id="state2" data-js="change"
							   autocomplete="off" {if $ISSUES_STATE eq 'closed'}checked{/if}
						> {\App\Language::translate('LBL_CLOSED', $QUALIFIED_MODULE)}
					</label>
				</div>
			</div>
		</div>
		<div class="table-responsive">
			<table class="table table-bordered listViewEntriesTable mt-1">
				<thead>
				<th>{\App\Language::translate('LBL_TITLE', $QUALIFIED_MODULE)}</th>
				<th>{\App\Language::translate('LBL_AUTHOR', $QUALIFIED_MODULE)}</th>
				<th>{\App\Language::translate('LBL_STATUS', $QUALIFIED_MODULE)}</th>
				<th></th>
				</thead>
				<tbody>
				{foreach from=$GITHUB_ISSUES item=ISSUE}
					<tr class="">
						<td>
							<a href="{$ISSUE->get('html_url')}" target="_blank" rel="noreferrer noopener">
								{$ISSUE->get('title')}
							</a>
						</td>
						<td>
							<a href="{$ISSUE->get('user')->html_url}" target="_blank" rel="noreferrer noopener">
								{$ISSUE->get('user')->login}
							</a>
						</td>
						<td>
							{\App\Language::translate($ISSUE->get('state'), $QUALIFIED_MODULE)}
						</td>
						<td>
							<div class="float-right actions">
							<span class="actionImages">
								<a href="{$ISSUE->get('html_url')}" target="_blank" rel="noreferrer noopener">
									<span title="" class="fas fa-th-list alignMiddle"></span>
								</a>
							</span>
							</div>
						</td>
					</tr>
					{foreachelse}
					<tr>
						<td>
							{\App\Language::translate('LBL_NO_ISSUES', $QUALIFIED_MODULE)}
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
	{else}
		<div class="alert alert-danger mt-2">
			{\App\Language::translate('LBL_ERROR_CONNECTED', $QUALIFIED_MODULE)}
		</div>
	{/if}
{/strip}
