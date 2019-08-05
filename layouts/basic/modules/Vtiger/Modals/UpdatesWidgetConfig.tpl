{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="tpl-Base-UpdatesWidgetConfig updatesWidgetConfig modal" id="updatesWidgetConfig" tabindex="-1" role="dialog"
		 aria-labelledby="c-updates-widget-config__title" aria-hidden="true">
		<div class="modal-dialog c-modal-xxl" role="document">
			<div class="modal-content">
				<div class="modal-header row no-gutters">
					<div class="col col-md-5 col-lg-6 col-xl-8 my-auto pb-1 pb-md-0">
						<h5 class="modal-tile mb-0" id="c-updates-widget-config__title">
							<span class="fas fa-cog fa-xs fa-fw mr-1"></span>
							{\App\Language::translate('LBL_UPDATES_WIDGET_CONFIGURATION')}
						</h5>
					</div>
					<div class="col-md-6 col-lg-5 col-xl-3 order-last order-md-0">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="fa fa-search"></i></span>
							</div>
							<input type="text" class="form-control js-updates-config-search">
						</div>
					</div>
					<button type="button" class="close order-2 order-md-3" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p class="ml-3">{\App\Language::translate("LBL_SHOW_ACTIONS", $MODULE_NAME)}:</p>
					<div class="form-row ml-4 mb-3">
						{foreach key=VALUE item=TRACKER_ACTION from=ModTracker_Record_Model::$statusLabel}
							<div class="form-check col-md-3">
								<input class="form-check-input js-tracker-action" type="checkbox" value="{$VALUE}" {if \in_array($VALUE, $SELECTED_TRACKER_ACTIONS)} checked {/if} data-js=”container” id="{$TRACKER_ACTION}">
								<label class="form-check-label" for="{$TRACKER_ACTION}">
									{\App\Language::translate($TRACKER_ACTION, 'ModTracker')|ucfirst}
								</label>
							</div>
						{{/foreach}}
					</div>
					<div class="u-columns-width-300px-rem u-columns-count-5">
						{foreach item=PARENT_MODULE from=$MODULES_LIST}
							<div class="card u-columns__item mb-2 js-updates-config-search-block">
								<h5 class="card-header pb-2 pt-2">
									<span class="{$PARENT_MODULE['icon']} mr-1"></span>
									{\App\Language::translate($PARENT_MODULE['name'], 'Other:Menu')}
								</h5>
								<ul class="list-group list-group-flush">
									{foreach key=NAME item=MODULEMODEL from=$PARENT_MODULE['modules']}
										{assign var='singularLabel' value=$MODULEMODEL->getSingularLabelKey()}
										{if $singularLabel == 'SINGLE_Calendar'}
											{assign var='singularLabel' value='LBL_EVENT_OR_TASK'}
										{/if}
										<li class="list-group-item pt-1 pb-1 pl-1 js-updates-config-search-item">
											<div class="item" data-tabid="{$MODULEMODEL->id}">
												<input value="" type="checkbox" class="mr-2 js-selected-modules" data-js=”container” {if \in_array($MODULEMODEL->id, $SELECTED_MODULES)} checked {/if} title="{\App\Language::translate("LBL_SELECT_MODULE")}">
													<span class="modCT_{$NAME} userIcon-{$NAME} mr-1"
																title="{\App\Language::translate($singularLabel,$NAME)}">
													</span>
													<span>{\App\Language::translate($singularLabel,$NAME)}</span>
											</div>
										</li>
									{/foreach}
								</ul>
							</div>
						{/foreach}
					</div>
				</div>
				<div class="modal-footer">
					<button class="js-modal__save btn btn-success btn-sm" type="submit" name="saveButton" data-js="click">
							<strong>{\App\Language::translate("LBL_SAVE", $MODULE_NAME)}</strong>
					</button>
					<button class="btn btn-danger btn-sm" type="reset" data-dismiss="modal">
						<span class="fas fa-times mr-1"></span>
						<strong>{\App\Language::translate("LBL_CANCEL", $MODULE_NAME)}</strong>
					</button>
				</div>
			</div>
		</div>
	</div>
{/strip}
