{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if $GITHUB_CLIENT_MODEL->isAuthorized()}
		<div class="addIssuesModal validationEngineContainer" tabindex="-1">
			<div  class="modal fade authModalContent">
				<div class="modal-dialog modal-lg ">
					<div class="modal-content">
						<div class="modal-header row no-margin">
							<div class="col-xs-12 paddingLRZero">
								<div class="col-xs-8 paddingLRZero">
									<h4>{vtranslate('LBL_TITLE_ADD_ISSUE', $QUALIFIED_MODULE)}</h4>
								</div>
								<div class="pull-right">
									<button class="btn btn-success saveIssues paddingLeftMd" type="submit" disabled>
										{vtranslate('LBL_ADD_ISSUES', $QUALIFIED_MODULE)}
									</button>
									<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
								</div>
							</div>
						</div>
						<div class="modal-body row">
							<div class="col-xs-12">
								<div class="col-xs-12 paddingLRZero marginBottom10px">
									<span class="redColor">*</span>
									{vtranslate('LBL_TITLE', $QUALIFIED_MODULE)}
									<input id="titleIssues" class="form-control" data-validation-engine="validate[required]" type="text" name="title" value="">
								</div>
								<div class="col-xs-12 paddingLRZero marginBottom10px">
									<div class="checkbox">
										<label>
											<input type="checkbox" name="confirmRegulations">
											{vtranslate('LBL_CONFIRM_REGULATIONS', $QUALIFIED_MODULE)}
										</label>
									</div>
								</div>
								<div class="col-xs-12 paddingLRZero marginBottom10px">
									{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
									<textarea id="bodyIssues" class="form-control ckEditorSource" type="text" name="title">
									<br>
									<hr>
										{vtranslate('LBL_DEFAULT_DESCRIPTION', $QUALIFIED_MODULE)}
										{\App\Version::get()}
									<br>
										{vtranslate('LBL_PHP_VERSION', $QUALIFIED_MODULE)}: {$PHP_VERSION}
									<br>
										{if $ERROR_CONFIGURATION}
										<br>
										<strong>{vtranslate('LBL_CONFIGURATION_ERROR', $QUALIFIED_MODULE)}:</strong>
											{foreach from=$ERROR_CONFIGURATION key=NAME item=CONFIG}
											<br>
												{$NAME}: {$CONFIG['current']}
											{/foreach}
										{/if}
										{if $ERROR_LIBRARIES}
										<br>
										<strong>{vtranslate('LBL_LIBRARIES_ERROR', $QUALIFIED_MODULE)}:</strong>
											{foreach from=$ERROR_LIBRARIES key=NAME item=LIBRARY}
											<br>
												{$LIBRARY['name']}
											{/foreach}
										{/if}
									</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	{/if}
{/strip}
