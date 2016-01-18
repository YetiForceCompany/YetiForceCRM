{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
		<div class="authModal" tabindex="-1">
			<div  class="modal fade authModalContent validationEngineContainer ">
				<div class="modal-dialog ">
					<div class="modal-content">
						<div class="modal-header">
							<div class="row no-margin">
								<div class="col-md-7 col-xs-10">
									<h3 class="modal-title">{vtranslate('LBL_AUTHORIZATION', $QUALIFIED_MODULE_NAME)}</h3>
								</div>
								<div class="pull-right">
									<div class="pull-right">
										<button class="btn btn-success paddingRight15 saveKeys" type="button" aria-hidden="true">
											{vtranslate('LBL_SAVE', $QUALIFIED_MODULE_NAME)}
										</button>
										<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-body row ">
							<div class="col-xs-12">
								<div class="alert alert-danger errorMsg hide"></div>
							</div>
							<div class="col-xs-12 marginBottom10px">
								<span class="redColor">*</span>
								{vtranslate('LBL_USER_NAME', $QUALIFIED_MODULE_NAME)}
								<input class="form-control" name="username" data-validation-engine="validate[required]" value="" type="text">
							</div>
							<div class="col-xs-12 marginBottom10px">
								<span class="redColor">*</span>
								{vtranslate('LBL_ID_CLIENT', $QUALIFIED_MODULE_NAME)}
								<input class="form-control" data-validation-engine="validate[required]" name="client_id" value="" type="text">
							</div>
							<div class="col-xs-12">
								<span class="redColor">*</span>
								{vtranslate('LBL_TOKEN', $QUALIFIED_MODULE_NAME)}
								<input class="form-control" data-validation-engine="validate[required]" name="token" value="" type="text">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	{if !$GITHUB_CLIENT_MODEL->isAuthorized()}
		<div class="alert alert-danger" role="alert">
			{vtranslate('LBL_NOT_AUTHORIZED', $QUALIFIED_MODULE_NAME)}
			<button class="btn btn-danger showModal marginLeft10">
				{vtranslate('LBL_AUTHORIZATION', $QUALIFIED_MODULE_NAME)}
			</button>
		</div>
	{else}
		<button class="btn btn-primary showModal">
			{vtranslate('LBL_CHANGE_AUTHORIZATION', $QUALIFIED_MODULE_NAME)}
		</button>
	{/if}
	{if $GITHUB_ISSUES !== false}
		{if $GITHUB_CLIENT_MODEL->isAuthorized()}
			<div class="pull-left">
				<button class="btn btn-primary addIssuesBtn marginRight10">
					{vtranslate('LBL_ADD_ISSUES', $QUALIFIED_MODULE_NAME)}
				</button>
			</div>
		{/if}
		<div class="listViewActions pull-right paginationDiv paddingLeft5px">
			{include file='Pagination.tpl'|@vtemplate_path}
		</div>
		<div class="col-sm-4 pull-right">
			{if $GITHUB_CLIENT_MODEL->isAuthorized()}
				<div class="bootstrap-switch-container pull-right marginLeft10">
					<input class="switchBtn switchAuthor" {if $IS_AUTHOR} checked {/if}type="checkbox" data-size="small" data-handle-width="90" data-label-width="5" data-off-text="{vtranslate('LBL_ALL', $QUALIFIED_MODULE_NAME)}" data-on-text="{vtranslate('LBL_ME', $QUALIFIED_MODULE_NAME)}">
				</div>
			{/if}
			<div class="bootstrap-switch-container pull-right">
				<input class="switchBtn switchState" {if $ISSUES_STATE eq 'closed'}checked {/if}type="checkbox" data-size="small" data-handle-width="90" data-label-width="5" data-off-text="{vtranslate('LBL_OPEN', $QUALIFIED_MODULE_NAME)}" data-on-text="{vtranslate('LBL_CLOSED', $QUALIFIED_MODULE_NAME)}">
			</div>
		</div>
		<table class="table">
			<thead>
				<th>{vtranslate('LBL_TITLE', $QUALIFIED_MODULE_NAME)}</th>
				<th>{vtranslate('LBL_AUTHOR', $QUALIFIED_MODULE_NAME)}</th>
				<th>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE_NAME)}</th>
				<th></th>
			</thead>
			<tbody>
				{foreach from=$GITHUB_ISSUES item=ISSUE}
					<tr>
						<td>
							{$ISSUE->get('title')}
						</td>
						<td>
							{$ISSUE->get('user')->login}
						</td>
						<td>
							{$ISSUE->get('state')}
						</td>
						<td>
							<div class="pull-right actions">
								<a href="{$ISSUE->get('html_url')}">
									<span title="" class="glyphicon glyphicon-pencil alignMiddle"></span>
								</a>
							</div>
						</td>
					</tr>	
				{foreachelse}
					<tr>
						<td>
							{vtranslate('LBL_NO_ISSUES', $QUALIFIED_MODULE_NAME)}
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	{else}
		<div class="alert alert-danger marginTop10">
			{vtranslate('LBL_ERROR_CONNECTED', $QUALIFIED_MODULE_NAME)}
		</div>
	{/if}
{/strip}
