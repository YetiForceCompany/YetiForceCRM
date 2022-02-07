{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-UpdatesListConfig -->
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget">
					{if !empty($WID)}<input type="hidden" name="wid" value="{$WID}" />{/if}
					<input type="hidden" name="type" value="{$TYPE}" />
					<div class="modal-header">
						<h5 id="massEditHeader" class="modal-title">{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}</h5>
						<button type="button" data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">&times;</button>
					</div>
					<div class="modal-body">
						<div class="modal-Fields">
							<div class="form-horizontal">
								<div class="form-group row">
									<div class="col-md-4">
										<strong>{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}</strong>:
									</div>
									<div class="col-md-7">
										{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-4">
										<label class="col-form-label">{\App\Language::translate('Label', $QUALIFIED_MODULE)}:</label>
									</div>
									<div class="col-md-7">
										<input name="label" class="form-control" type="text" data-validation-engine="validate[required]" value="{$WIDGETINFO['label']}" />
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-4">
										<label class="col-form-label">{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}:</label>
									</div>
									<div class="col-md-7">
										<select name="field_name" multiple="multiple" class="select2 form-control marginLeftZero columnsSelect" data-validation-engine="validate[required]">
											{assign var=FIELDS value=$MODULE_MODEL->getFields($SOURCE, array(15,16,52,53,302)) }
											{if isset($WIDGETINFO['data']['field_name'])}
												{assign var=FIELD_NAME value=(array)($WIDGETINFO['data']['field_name'])}
											{else}
												{assign var=FIELD_NAME value=[]}
											{/if}
											{foreach from=$FIELDS['fields'] item=item key=key}
												<option {if isset($WIDGETINFO['data']['field_name']) && in_array($key, $FIELD_NAME)}selected{/if} value="{$key}">
													{\App\Language::translate($item, $SOURCEMODULE)}
												</option>
											{foreachelse}
												<option disabled value="-">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
											{/foreach}
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-UpdatesListConfig -->
{/strip}
