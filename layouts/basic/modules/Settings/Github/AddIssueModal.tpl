{if $GITHUB_CLIENT_MODEL->isAuthorized()}
	<div class="addIssuesModal" tabindex="-1">
		<div  class="modal fade authModalContent ">
			<div class="modal-dialog modal-lg ">
				<div class="modal-content">
					<div class="modal-header row no-margin">
						<div class="col-xs-12 paddingLRZero">
							<div class="col-xs-8 paddingLRZero">
								<h4>{vtranslate('LBL_ADD_ISSUE', $QUALIFIED_MODULE)}</h4>
							</div>
							<div class="pull-right">
								<button class="btn btn-success saveIssues paddingLeftMd" type="submit">
									{vtranslate('LBL_ADD_ISSUES', $QUALIFIED_MODULE)}
								</button>
								<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
							</div>
						</div>
					</div>
					<div class="modal-body row ">
						<div class="col-xs-12">
							<div class="col-xs-12 paddingLRZero marginBottom10px">
								{vtranslate('LBL_TITLE', $QUALIFIED_MODULE)}
								<input id="titleIssues" class="form-control" type="text" name="title" value="">
							</div>
							<div class="col-xs-12 paddingLRZero marginBottom10px">
								{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
								<textarea id="bodyIssues" class="form-control ckEditorSource" type="text" name="title"></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/if}
