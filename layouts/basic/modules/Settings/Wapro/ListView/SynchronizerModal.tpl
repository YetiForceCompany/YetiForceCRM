{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Wapro-ListView-SynchronizerModal -->
	<div class="modal-body pb-0">
		<div class="row no-gutters">
			<div class="col-sm-18 col-md-12">
				<form name="importList" class="js-update-synchronizer form-horizontal" action="index.php" method="post" class="form-horizontal" enctype="multipart/form-data">
					<div class="modal-body">
						{if $SYNCHRONIZERS}
							{foreach from=$SYNCHRONIZERS item=SYNCHRONIZER key=SYNCHRONIZER_NAME }
								<div class="form-group row">
									<div class="col-sm-2 d-flex align-items-center px-0">
										<input type="checkbox" title="{\App\Language::translate($SYNCHRONIZER->name, $QUALIFIED_MODULE)}" />
									</div>
									<div class="col-sm-10 px-0">
										<label class="col-form-label">
											{\App\Language::translate($SYNCHRONIZER->name, $QUALIFIED_MODULE)}
										</label>
									</div>
								</div>
							{/foreach}
						{/if}
					</div>

				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-Wapro-ListView-SynchronizerModal -->
{/strip}
