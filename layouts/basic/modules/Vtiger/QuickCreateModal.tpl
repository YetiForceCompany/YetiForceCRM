{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="tpl-QuickCreateModel quickCreateModules modal" id="quickCreateModules" tabindex="-1" role="dialog"
		 aria-labelledby="c-quick-create__title" aria-hidden="true">
		<div class="modal-dialog c-modal-xxl" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-tile" id="c-quick-create__title">
						<span class="fas fa-plus fa-fw mr-1"></span>
						{\App\Language::translate('LBL_QUICK_CREATE')}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="u-columns-width-300px-rem">
						{foreach item=PARENT_MODULE from=$QUICKCREATE_MODULES_PARENT}
							<div class="card u-columns__item mb-2">
								<h5 class="card-header pb-2 pt-2">
									<span class="{$PARENT_MODULE['icon']} mr-1"></span>
									{\App\Language::translate($PARENT_MODULE['name'], 'Other:Menu')}
								</h5>
								<ul class="list-group list-group-flush">
									{foreach key=NAME item=MODULEMODEL from=$PARENT_MODULE['modules']}
										{assign var='quickCreateModule' value=$MODULEMODEL->isQuickCreateSupported()}
										{assign var='singularLabel' value=$MODULEMODEL->getSingularLabelKey()}
										{if $singularLabel == 'SINGLE_Calendar'}
											{assign var='singularLabel' value='LBL_EVENT_OR_TASK'}
										{/if}
										<li class="list-group-item">
											<a id="menubar_quickCreate_{$NAME}"
											   data-name="{$NAME}"
													{if $quickCreateModule}
														class="quickCreateModule text-dark"
														data-url="{$MODULEMODEL->getQuickCreateUrl()}"
														href="javascript:void(0)"
													{else}
														class="text-dark"
														href="{$MODULEMODEL->getCreateRecordUrl()}"
													{/if}
											>
													<span class="modCT_{$NAME} userIcon-{$NAME} mr-1"
														  title="{\App\Language::translate($singularLabel,$NAME)}"></span>
												<span>{\App\Language::translate($singularLabel,$NAME)}</span>
											</a>
										</li>
									{/foreach}
								</ul>
							</div>
						{/foreach}
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-danger btn-sm" type="reset" data-dismiss="modal">
						<span class="fas fa-times mr-1"></span>
						<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
					</button>
				</div>
			</div>
		</div>
	</div>
{/strip}