{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<div class="pull-right">
			<button type="button" class=" btn btn-warning" data-dismiss="modal" aria-hidden="true">&times;</button>
		</div>
		<h3 class="modal-title">{vtranslate('LBL_SEND_MASS_EMAIL', $MODULE)}</h3>
	</div>
	<div class="modal-body col-md-12">
		<input type="hidden" id="url" value="{OSSMail_Module_Model::getComposeUrl($SOURCE_MODULE,$SOURCE_RECORD)}" />
		<input type="hidden" id="emails" value="{Vtiger_Util_Helper::toSafeHTML(\includes\utils\Json::encode($RECORDS))}" />
		<h4>{vtranslate('LBL_NUMBER_OF_SELECTED_RECORDS', $MODULE)}: {$ALL_RECORDS}</h4>
		<h4>{vtranslate('LBL_NUMBER_OF_FOUND_MAIL_ADDRESSES', $MODULE)}: {$EMAIL_RECORDS}</h4>
	</div>
	<div class="modal-footer">
		<div class="pull-right">
			{if $EMAIL_RECORDS != 0}
				{if $USER_MODEL->get('internal_mailer') == 1}
					<button class="btn btn-success" type="submit" name="saveButton">
						<strong>{vtranslate('LBL_SEND', $MODULE)}</strong>
					</button>
				{else}
					<a class="btn btn-success" href="{$URL}" title="{vtranslate('LBL_CREATEMAIL', 'OSSMailView')}">
						<strong>{vtranslate('LBL_SEND', $MODULE)}</strong>
					</a>
				{/if}
			{/if}
			<button class="btn btn-warning" type="reset" data-dismiss="modal">
				<strong>{vtranslate('LBL_CANCEL', $MODULE)}</strong>
			</button>
		</div>
	</div>
{/strip}
