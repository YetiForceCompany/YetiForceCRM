{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="tpl-Base-QuickCreateModel quickCreateModules modal" id="quickCreateModules" tabindex="-1" role="dialog"
		aria-labelledby="c-quick-create__title" aria-hidden="true">
		<div class="modal-dialog c-modal-xxl" role="document">
			<div class="modal-content">
				<div class="modal-header row no-gutters">
					<div class="col col-md-5 col-lg-6 col-xl-8 my-auto pb-1 pb-md-0">
						<h5 class="modal-tile mb-0" id="c-quick-create__title">
							<span class="fas fa-plus fa-xs fa-fw mr-1"></span>
							{\App\Language::translate('LBL_QUICK_CREATE')}
						</h5>
					</div>
					<div class="col-md-6 col-lg-5 col-xl-3 order-last order-md-0">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="fa fa-search"></i></span>
							</div>
							<input type="text" class="form-control js-quickcreate-search">
						</div>
					</div>
					<button type="button" class="close order-2 order-md-3" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="u-columns-width-300px-rem u-columns-count-5">
						{foreach item=PARENT_MODULE from=$QUICKCREATE_MODULES_PARENT}
							<div class="card u-columns__item mb-2 js-quickcreate-search-block">
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
										<li class="list-group-item pt-1 pb-1 js-quickcreate-search-item">
											<a id="menubar_quickCreate_{$NAME}"
												data-name="{$NAME}"
												{if $quickCreateModule}
													class="quickCreateModule text-dark"
													data-url="{$MODULEMODEL->getQuickCreateUrl()}"
													href="javascript:void(0)"
												{else}
													class="text-dark"
													href="{$MODULEMODEL->getCreateRecordUrl()}"
												{/if}>
												<span class="modCT_{$NAME} yfm-{$NAME} mr-1"
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
