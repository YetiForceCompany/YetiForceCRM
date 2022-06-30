{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-RecrodCollector-ConfigModal -->
	<div class="modal-body pb-0">
		<form class="js-form-validation">
			<div class="row no-gutters">
				<div class="col-sm-18 col-md-12">
					<table class="table table-sm mb-0">
						<tbody class="u-word-break-all small">
							{foreach from=$FIELDS item=FIELD}
								<div class="form-group row">
								<label class="col-form-label col-md-3 u-text-small-bold text-right">
									{\App\Language::translate($FIELD->get('label'), $QUALIFIED_MODULE)}
									{if $FIELD->isMandatory()}<span class="redColor">*</span>{/if}:
								</label>
								<div class="col-md-9 fieldValue">
									{include file=\App\Layout::getTemplatePath($FIELD->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD MODULE=$QUALIFIED_MODULE RECORD=null}
								</div>
							</div>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-RecrodCollector-ConfigModal -->
{/strip}
