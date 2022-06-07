{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WidgetsManagement-WidgetListModal -->
	<form class="form-horizontal js-validate-form" method="POST">
		<div class="modal-body">
			<div class="clearfix">
				<div class="form-group row mb-2">
					<label class="col-form-label col-md-3 u-text-small-bold text-left text-lg-right text-md-right">
						{\App\Language::translate('LBL_SELECT_WIDGET', $QUALIFIED_MODULE)}:
					</label>
					<div class="col-md-9 fieldValue m-auto">
						<select class="js-widget form-control select2" name="widgets" data-validation-engine="validate[required]">
							{foreach from=$WIDGETS item=WIDGET}
								<option value="index.php?parent=Settings&module=WidgetsManagement&view=EditWidget&linkId={$WIDGET->get('linkid')}&blockId={$WIDGET->get('blockid')}" data-name="{$WIDGET->get('linklabel')}">
									{$WIDGET->getTranslatedTitle()}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</div>
	</form>
	<!-- /tpl-Settings-WidgetsManagement-WidgetListModal -->
{/strip}
