{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $GITHUB_CLIENT_MODEL->isAuthorized()}
		<div class="addIssuesModal validationEngineContainer" tabindex="-1">
			<div  class="modal fade authModalContent">
				<div class="modal-dialog modal-lg ">
					<div class="modal-content">
						<div class="modal-header row no-margin">
							<div class="col-12 paddingLRZero">
								<div class="col-8 paddingLRZero">
									<h4>{\App\Language::translate('LBL_TITLE_ADD_ISSUE', $QUALIFIED_MODULE)}</h4>
								</div>
								<div class="float-right">
									<button class="btn btn-success saveIssues paddingLeftMd" type="submit" disabled>
										{\App\Language::translate('LBL_ADD_ISSUES', $QUALIFIED_MODULE)}
									</button>
									<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
								</div>
							</div>
						</div>
						<div class="modal-body row">
							<div class="col-12">
								<div class="col-12 paddingLRZero marginBottom10px">
									<span class="redColor">*</span>
									{\App\Language::translate('LBL_TITLE', $QUALIFIED_MODULE)}
									<input id="titleIssues" class="form-control" data-validation-engine="validate[required]" type="text" name="title" value="">
								</div>
								<div class="col-12 paddingLRZero marginBottom10px">
									<div class="checkbox">
										<label>
											<input type="checkbox" name="confirmRegulations">
											{\App\Language::translate('LBL_CONFIRM_REGULATIONS', $QUALIFIED_MODULE)}
										</label>
									</div>
								</div>
								<div class="col-12 paddingLRZero marginBottom10px">
									{\App\Language::translate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
									<textarea id="bodyIssues" class="form-control js-editor" data-js="ckeditor" type="text" name="title">
									<br />
									<hr>
										{\App\Language::translate('LBL_DEFAULT_DESCRIPTION', $QUALIFIED_MODULE)}
										{\App\Version::get()}
									<br />
										{\App\Language::translate('LBL_PHP_VERSION', $QUALIFIED_MODULE)}: {$PHP_VERSION}
									<br />
										{if $ERROR_CONFIGURATION}
										<br />
										<strong>{\App\Language::translate('LBL_CONFIGURATION_ERROR', $QUALIFIED_MODULE)}:</strong>
											{foreach from=$ERROR_CONFIGURATION key=NAME item=CONFIG}
											<br />
												{$NAME}: {$CONFIG['current']}
											{/foreach}
										{/if}
										{if $ERROR_LIBRARIES}
										<br />
										<strong>{\App\Language::translate('LBL_LIBRARIES_ERROR', $QUALIFIED_MODULE)}:</strong>
											{foreach from=$ERROR_LIBRARIES key=NAME item=LIBRARY}
											<br />
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
