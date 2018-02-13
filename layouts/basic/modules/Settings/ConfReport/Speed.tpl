{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="addIssuesModal validationEngineContainer" tabindex="-1">
		<div  class="modal fade authModalContent">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header row no-margin">
						<div class="col-12 paddingLRZero">
							<div class="col-8 paddingLRZero">
								<h4>{\App\Language::translate('LBL_SERVER_SPEED_TEST', $QUALIFIED_MODULE)}</h4>
							</div>
						</div>
					</div>
					<div class="modal-body row">
						<div class="col-12">
							<h4>{\App\Language::translate('LBL_HDD', $QUALIFIED_MODULE)}:</h4>
							<h5>{\App\Language::translate('LBL_READ_TEST', $QUALIFIED_MODULE)}: {$TESTS['FilesRead']}</h5>
							<h5>{\App\Language::translate('LBL_WRITE_TEST', $QUALIFIED_MODULE)}: {$TESTS['FilesWrite']}</h5>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
