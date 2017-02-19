{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
		<div class="addIssuesModal validationEngineContainer" tabindex="-1">
			<div  class="modal fade authModalContent">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header row no-margin">
							<div class="col-xs-12 paddingLRZero">
								<div class="col-xs-8 paddingLRZero">
									<h4>{vtranslate('LBL_SERVER_SPEED_TEST', $QUALIFIED_MODULE)}</h4>
								</div>
							</div>
						</div>
						<div class="modal-body row">
							<div class="col-xs-12">
								<h4>{vtranslate('LBL_HDD', $QUALIFIED_MODULE)}:</h4>
								<h5>{vtranslate('LBL_READ_TEST', $QUALIFIED_MODULE)}: {$TESTS['FilesRead']}</h5>
								<h5>{vtranslate('LBL_WRITE_TEST', $QUALIFIED_MODULE)}: {$TESTS['FilesWrite']}</h5>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
{/strip}
