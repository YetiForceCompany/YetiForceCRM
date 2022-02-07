{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-GenerateModal -->
	<div class="modal-header align-items-center">
		<h5 class="modal-title">
			<span class="fas fa-plus-circle mr-2"></span>
			{\App\Language::translate('LBL_GENERATE_RECORD_FOR_MODULE', $BASE_MODULE_NAME)}
		</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		{if $VIEW eq 'List'}
			<input type="hidden" name="all_records" id="all_records"
				value="{\App\Purifier::encodeHtml(\App\Json::encode($ALL_RECORDS))}" />
			<div class="form-group form-row d-flex justify-content-center">
				<div class="radio-inline mr-3">
					<label>
						<input type="radio" class="mr-1" name="method" id="optionsRadios1" value="0">
						{\App\Language::translate('LBL_AUTOGENERATE',$BASE_MODULE_NAME)}&nbsp;
					</label>
					<span class="js-popover-tooltip delay0" data-js="popover" data-placement="top"
						data-content="{\App\Language::translate('LBL_AUTOGENERATE_INFO',$BASE_MODULE_NAME)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</div>
				<div class="radio-inline">
					<label>
						<input type="radio" class="mr-1" name="method" id="optionsRadios2" value="1" checked>
						{\App\Language::translate('LBL_OPEN_NEW_WINDOWS',$BASE_MODULE_NAME)}&nbsp;
					</label>
					<span class="js-popover-tooltip delay0" data-js="popover" data-placement="top"
						data-content="{\App\Language::translate('LBL_OPEN_NEW_WINDOWS_INFO',$BASE_MODULE_NAME)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</div>
			</div>
		{/if}
		<div class="w-75 float-left">
			<select class="select2 form-control js-generate-mapping" data-template-result="prependDataTemplate"
				data-template-selection="prependDataTemplate" data-js="select">
				{foreach item=TEMPLATE from=$TEMPLATES}
					{assign var=RELATED_MODEL value=$TEMPLATE->getRelatedModule()}
					{assign var=RELATED_MODULE_NAME value=\App\Language::translate($TEMPLATE->getRelatedName(), $TEMPLATE->getRelatedName())}
					<option data-id="{$TEMPLATE->getId()}"
						data-name="{$RELATED_MODEL->getName()}"
						data-url="{$RELATED_MODEL->getCreateRecordUrl()|cat:"&reference_id=$RECORD"}"
						data-template="<span><span class='yfm-{$TEMPLATE->getRelatedName()} mr-1'></span>{$RELATED_MODULE_NAME}</span>">
						{$RELATED_MODULE_NAME}
					</option>
				{/foreach}
			</select>
		</div>
		<div class="text-right float-right w-25 pl-2">
			<button class="btn btn-success js-genetate-button border w-100" data-js="click">
				{\App\Language::translate('LBL_GENERATE')}
			</button>
		</div>
	</div>
	<div class="modal-footer">
	</div>
	<!-- /tpl-Base-GenerateModal -->
{/strip}
