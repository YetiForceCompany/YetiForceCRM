{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-footer">
		<div class="btn-group">
			<button id="generate_pdf" href="" target="_blank" data-url="index.php?parent=Settings&module=PDF&action=Export{$EXPORT_VARS}&template=" type="button" class="btn btn-success">{\App\Language::translate('LBL_GENERATE', $QUALIFIED_MODULE)}</button>
			<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="caret"></span>
				<span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu">
				<li><a href="#" id="single_pdf" data-url="index.php?parent=Settings&module=PDF&action=Export{$EXPORT_VARS}&single_pdf=1&template=">{\App\Language::translate('LBL_GENERATE_SINGLE', $QUALIFIED_MODULE)}</a></li>
				<li><a href="#" id="email_pdf" data-url="index.php?parent=Settings&module=PDF&action=Export{$EXPORT_VARS}&email_pdf=1&template=">{\App\Language::translate('LBL_SEND_EMAIL', $QUALIFIED_MODULE)}</a></li>
			</ul>
		</div>&nbsp;
		<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong></button>
	</div>
{/strip}
