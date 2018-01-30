{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
		<div class="authModal modal fade" tabindex="-1">
			<div  class="authModalContent validationEngineContainer ">
				<div class="modal-dialog ">
					<div class="modal-content">
						<div class="modal-header">
							<div class="row no-margin">
								<div class="col-md-7 col-xs-10">
									<h3 class="modal-title">{\App\Language::translate('LBL_AUTHORIZATION', $QUALIFIED_MODULE)}</h3>
								</div>
								<div class="pull-right">
									<div class="pull-right">
										<button class="btn btn-success paddingRight15 saveKeys" type="button" aria-hidden="true">
											{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
										</button>
										<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-body row ">
							<div class="col-xs-12">
								<div class="alert alert-warning">
									<ul>
										<li>{\App\Language::translate('LBL_USERNAME_DESCRIPTION', $QUALIFIED_MODULE)}</li>
										<li>{\App\Language::translate('LBL_ID_CLIENT_DESCRIPTION', $QUALIFIED_MODULE)}</li>
										<li>{\App\Language::translate('LBL_TOKEN_DESCRIPTION', $QUALIFIED_MODULE)}</li>
									</ul>
								</div>
							</div>
							<div class="col-xs-12">
								<div class="alert alert-danger errorMsg hide"></div>
							</div>
							<div class="col-xs-12 marginBottom10px">
								<span class="redColor">*</span>
								{\App\Language::translate('LBL_USER_NAME', $QUALIFIED_MODULE)}
								<input class="form-control" name="username" data-validation-engine="validate[required]" value="" type="text">
							</div>
							<div class="col-xs-12 marginBottom10px">
								<span class="redColor">*</span>
								{\App\Language::translate('LBL_ID_CLIENT', $QUALIFIED_MODULE)}
								<input class="form-control" data-validation-engine="validate[required]" name="client_id" value="" type="text">
							</div>
							<div class="col-xs-12">
								<span class="redColor">*</span>
								{\App\Language::translate('LBL_TOKEN', $QUALIFIED_MODULE)}
								<input class="form-control" data-validation-engine="validate[required]" name="token" value="" type="text">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	{if !$GITHUB_CLIENT_MODEL->isAuthorized()}
		<div class="alert alert-danger" role="alert">
			{\App\Language::translate('LBL_NOT_AUTHORIZED', $QUALIFIED_MODULE)}
			<button class="btn btn-danger showModal marginLeft10">
				{\App\Language::translate('LBL_AUTHORIZATION', $QUALIFIED_MODULE)}
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
				<button class="btn btn-primary addIssuesBtn marginRight10">
					{\App\Language::translate('LBL_ADD_ISSUES', $QUALIFIED_MODULE)}
				</button>
			</div>
		{/if}
		<div class="listViewActions pull-right paginationDiv paddingLeft5px">
			{include file=\App\Layout::getTemplatePath('Pagination.tpl')}
		</div>
		<div class="col-sm-4 pull-right">
			{if $GITHUB_CLIENT_MODEL->isAuthorized()}
				<div class="bootstrap-switch-container pull-right marginLeft10">
					<input class="switchBtn switchAuthor" {if $IS_AUTHOR} checked {/if}type="checkbox" data-size="small" data-handle-width="90" data-label-width="5" data-off-text="{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}" data-on-text="{\App\Language::translate('LBL_ME', $QUALIFIED_MODULE)}">
				</div>
			{/if}
			<div class="bootstrap-switch-container pull-right">
				<input class="switchBtn switchState" {if $ISSUES_STATE eq 'closed'}checked {/if}type="checkbox" data-size="small" data-handle-width="90" data-label-width="5" data-off-text="{\App\Language::translate('LBL_OPEN', $QUALIFIED_MODULE)}" data-on-text="{\App\Language::translate('LBL_CLOSED', $QUALIFIED_MODULE)}">
			</div>
		</div>
		<table class="table listViewEntriesTable">
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
							<a href="{$ISSUE->get('html_url')}" target="_blank" rel="noreferrer">
								{$ISSUE->get('title')}
							</a>
						</td>
						<td>
							<a href="{$ISSUE->get('user')->html_url}" target="_blank" rel="noreferrer">
								{$ISSUE->get('user')->login}
							</a>
						</td>
						<td>
							{\App\Language::translate($ISSUE->get('state'), $QUALIFIED_MODULE)}
						</td>
						<td>
							<div class="pull-right actions">
								<span class="actionImages">
									<a href="{$ISSUE->get('html_url')}" target="_blank" rel="noreferrer">
										<span title="" class="fa fa-th-list alignMiddle"></span>
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
	{else}
		<div class="alert alert-danger marginTop10">
			{\App\Language::translate('LBL_ERROR_CONNECTED', $QUALIFIED_MODULE)}
		</div>
	{/if}
{/strip}
