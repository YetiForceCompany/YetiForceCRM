{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if !$GITHUB_CLIENT_MODEL->isAuthorized()}
		<div class="authModal" tabindex="-1">
			<div  class="modal fade authModalContent ">
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
							<div class="col-xs-12 marginBottom10px">
								{vtranslate('LBL_ID_CLIENT', $QUALIFIED_MODULE_NAME)}
								<input class="form-control" name="client_id" value="" type="text">
							</div>
							<div class="col-xs-12">
								{vtranslate('LBL_TOKEN', $QUALIFIED_MODULE_NAME)}
								<input class="form-control" name="token" value="" type="text">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="alert alert-danger" role="alert">
			{vtranslate('LBL_NOT_AUTHORIZED', $QUALIFIED_MODULE_NAME)}
			<button class="btn btn-danger showModal marginLeft10 ">
				{vtranslate('LBL_AUTHORIZATION', $QUALIFIED_MODULE_NAME)}
			</button>
		</div>
	{/if}
	<div class="listViewActions pull-right paginationDiv paddingLeft5px">
		{include file='Pagination.tpl'|@vtemplate_path}
	</div>
	<div class="col-sm-4 pull-right ">
		<div class="bootstrap-switch-container pull-right">
			<input class="switchBtn" {if $ISSUES_STATE eq 'closed'}checked {/if}type="checkbox" data-size="small" data-handle-width="90" data-label-width="5" data-off-text="{vtranslate('LBL_OPEN', $QUALIFIED_MODULE_NAME)}" data-on-text="{vtranslate('LBL_CLOSED', $QUALIFIED_MODULE_NAME)}">
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
						{$ISSUE->getTitle()}
					</td>
					<td>
						{$ISSUE->getUser()->getLogin()}
					</td>
					<td>
						{$ISSUE->getState()}
					</td>
					<td>
						<div class="pull-right actions">
							<a href="{$ISSUE->getHtmlUrl()}">
								<span title="" class="glyphicon glyphicon-pencil alignMiddle"></span>
							</a>
						</div>
					</td>
				</tr>	
			{/foreach}
		</tbody>
	</table>
	{if $GITHUB_CLIENT_MODEL->isAuthorized()}
		<hr>
		<div class="col-xs-12 paddingLRZero">
			<div class="col-xs-8 paddingLRZero">
				<h4>{vtranslate('LBL_ADD_ISSUE', $QUALIFIED_MODULE_NAME)}</h4>
			</div>
			<div class="pull-right">
				<button class="btn btn-success pull-right saveIssues" type="submit">
					{vtranslate('LBL_ADD_ISSUES', $QUALIFIED_MODULE_NAME)}
				</button>
			</div>
		</div>
		<div class="col-xs-12 paddingLRZero">
			<div class="col-xs-12 paddingLRZero marginBottom10px">
				{vtranslate('LBL_TITLE', $QUALIFIED_MODULE_NAME)}
				<input id="titleIssues" class="form-control" type="text" name="title" value="">
			</div>
			<div class="col-xs-12 paddingLRZero marginBottom10px">
				{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE_NAME)}
				<textarea id="bodyIssues" class="form-control ckEditorSource" type="text" name="title"></textarea>
			</div>
		</div>
	{/if}
{/strip}
