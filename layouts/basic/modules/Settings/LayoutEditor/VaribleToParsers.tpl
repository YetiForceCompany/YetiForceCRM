{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-VaribleToParsers -->
	<div class="modal-header">
		<h5 class="modal-title">{\App\Language::translate('LBL_CUSTOM_VARIABLES', 'Other::TextParser')}</h5>
		<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body row">
		<div class="col-md-12">
			<select class="col-md-2 select2" name="varibles" data-validation-engine="validate[required]"
				data-fieldinfo='{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}'>
				{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$VARIBLES}
					<option value="">{\App\Language::translate('LBL_SELECT', $QUALIFIED_MODULE)}</option>
					<option value="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}" {if $DEFAULT_VALUE eq $PICKLIST_VALUE} selected="" {/if}>{App\Language::translate($PICKLIST_NAME, 'Other::TextParser')}</option>
				{/foreach}
			</select>
			{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
		</div>
	</div>
	<!-- /tpl-Settings-LayoutEditor-VaribleToParsers -->
{/strip}
