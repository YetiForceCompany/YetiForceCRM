{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-PickListDependency-DependentFieldSettings -->
	<div id="accordion" class="my-3">
		<div class="card border-light">
			<div class="card-header bg-transparent border-light" id="headingOne">
				<span class="mr-2">
					<span class="fas fa-info-circle align-middle mr-3"></span>
					{\App\Language::translate('LBL_CONFIGURE_DEPENDENCY_INFO', $QUALIFIED_MODULE)}
				</span>
				<h5 class="mb-0">
					<span class="btn btn-link px-0" data-toggle="collapse" data-target="#collapseOne"
						aria-expanded="true" aria-controls="collapseOne">
						{\App\Language::translate('LBL_MORE', $QUALIFIED_MODULE)}..
					</span>
				</h5>
			</div>
			<div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
				<div class="card-body" id="dependencyHelp">
					<ul>
						<li>{\App\Language::translate('LBL_CONFIGURE_DEPENDENCY_HELP_1', $QUALIFIED_MODULE)}</li>
						<li class="my-3">{\App\Language::translate('LBL_CONFIGURE_DEPENDENCY_HELP_2', $QUALIFIED_MODULE)|unescape:"html"}</li>
						<li>{\App\Language::translate('LBL_CONFIGURE_DEPENDENCY_HELP_3', $QUALIFIED_MODULE)}
							<span class="selectedCell p-1"> {\App\Language::translate('Selected Values', $QUALIFIED_MODULE)} </span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	{include file=\App\Layout::getTemplatePath('DependentTable.tpl', $QUALIFIED_MODULE)}
	<div class="modal sourcePicklistValuesModal modalCloneCopy fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-hand-point-up mr-1"></span>
						{\App\Language::translate('LBL_SELECT_SOURCE_PICKLIST_VALUES', $QUALIFIED_MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="m-0 table-responsive">
						<table class="table table-borderless table-sm mb-0">
							<tr>
								{foreach key=SOURCE_INDEX item=SOURCE_VALUE from=$SOURCE_PICKLIST_VALUES name=sourceValuesLoop}
									{if $smarty.foreach.sourceValuesLoop.index % 3 == 0}
									</tr>
									<tr>
									{/if}
									<td>
										<div class="form-group">
											<div class="controls checkbox">
												<label class="ml-1">
													<input type="checkbox"
														class="sourceValue {\App\Purifier::encodeHtml($SOURCE_VALUE)} mr-1"
														id="sourceValue-{$smarty.foreach.sourceValuesLoop.index}"
														data-source-value="{\App\Purifier::encodeHtml($SOURCE_VALUE)}"
														value="{\App\Purifier::encodeHtml($SOURCE_VALUE)}"
														{if empty($MAPPED_VALUES) || in_array($SOURCE_VALUE, array_map('App\Purifier::decodeHtml', $MAPPED_SOURCE_PICKLIST_VALUES))} checked {/if} />
													{\App\Language::translate($SOURCE_VALUE, $SELECTED_MODULE)}
												</label>
											</div>
										</div>
									</td>
								{/foreach}
							</tr>
						</table>
					</div>
				</div>
				{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', 'Vtiger') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
			</div>
		</div>
	</div>
	<div class="p-3">
		<div class="btn-toolbar float-right">
			<button class="btn btn-success mr-2 js-save-dependent-picklists" type="button" data-js="click"><span
					class="fa fa-check u-mr-5px"></span><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
			</button>
			<button type="button" class="cancelLink cancelDependency btn btn-danger text-white"
				title="{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}">
				<span class="fa fa-times u-mr-5px"></span><strong>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</strong>
			</button>
		</div>
		<br /><br />
	</div>
	</div>
	<!-- /tpl-Settings-PickListDependency-DependentFieldSettings -->
{/strip}
