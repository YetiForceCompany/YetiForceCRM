{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailRbl-ReportModal -->
	<div class="modal-body">
		<div class="alert alert-warning alert-dismissible mb-0 fade show" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<span class="fas fa-info-circle mr-2 u-fs-3x float-left"></span>
			{\App\Language::translate($MODAL_DESC, $QUALIFIED_MODULE)}
		</div>
		<form class="js-validate-form js-send-by-ajax row pt-3" method="post" action="index.php" data-js="validationEngine|submit">
			<input type="hidden" name="module" value="MailRbl" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="SendReport" />
			<input type="hidden" name="id" value="{$RECORD}" />
			<div class="col-md-6 col-12">
				<div class="form-group row">
					<label for="inputIp" class="col-sm-3 col-form-label text-md-right text-left"><span class="redColor">*</span>{\App\Language::translate('LBL_REPORT_IP', $QUALIFIED_MODULE)}:</label>
					<div class="col-sm-9">
						<input type="text" name="ip" readonly class="form-control-plaintext" id="inputIp" value="{$IP}">
					</div>
				</div>
				<div class="form-group row">
					<label for="inputType" class="col-sm-3 col-form-label text-md-right text-left"><span class="redColor">*</span>{\App\Language::translate('LBL_REPORT_TYPE', $QUALIFIED_MODULE)}:</label>
					<div class="col-sm-9">
						<input type="hidden" name="type" value="{$TYPE}">
						<input type="text" readonly class="form-control-plaintext" id="inputType" value="{\App\Language::translate($TYPE_NAME, $QUALIFIED_MODULE)}">
					</div>
				</div>
			</div>
			<div class="col-md-6 col-12">
				<div class="form-group row">
					<label for="inputDesc" class="col-sm-3 col-form-label text-md-right text-left">{\App\Language::translate('LBL_REPORT_DESC', $QUALIFIED_MODULE)}:</label>
					<div class="col-sm-9">
						<textarea name="desc" class="form-control" id="inputDesc" rows="1"></textarea>
					</div>
				</div>
				<div class="form-group row">
					<label for="inputCategory" class="col-sm-3 col-form-label text-md-right text-left"><span class="redColor">*</span>{\App\Language::translate('LBL_REPORT_CATEGORY', $QUALIFIED_MODULE)}:</label>
					<div class="col-sm-9">
						<select name="category" id="inputCategory" class="form-control select2" data-validation-engine="validate[required]">
							{foreach from=$CATEGORIES key=KEY item=ITEM}
								<option value="{$KEY}">{\App\Language::translate($ITEM, $QUALIFIED_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="form-group col-12">
				<label for="inputEvidence" class="col-sm-3 col-form-label text-left">{\App\Language::translate('LBL_REPORT_EVIDENCE', $QUALIFIED_MODULE)}:</label>
				{if $BODY}
					<div>
						<iframe sandbox="allow-same-origin" class="w-100 js-iframe-full-height" frameborder="0" srcdoc="{\App\Purifier::encodeHtml($BODY)}"></iframe>
					</div>
					<hr />
				{/if}
				<div class="px-3">
					<pre>{\App\Purifier::encodeHtml($HEADER)}</pre>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-MailRbl-ReportModal -->
{/strip}
