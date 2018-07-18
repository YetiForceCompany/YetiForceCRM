{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-ConfReport-Speed modal-body">
		<h5>{\App\Language::translate('LBL_READ_TEST', $QUALIFIED_MODULE)}
			: {$TESTS['FilesRead']}{\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}</h5>
		<h5>{\App\Language::translate('LBL_WRITE_TEST', $QUALIFIED_MODULE)}
			: {$TESTS['FilesWrite']}{\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}</h5>
		<h5>{\App\Language::translate('LBL_CPU', $QUALIFIED_MODULE)}
			: {$TESTS['CPU']}{\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}</h5>
		<h5>{\App\Language::translate('LBL_RAM', $QUALIFIED_MODULE)}
			: {$TESTS['RAM']}{\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}</h5>
		<h5>{\App\Language::translate('LBL_DB', $QUALIFIED_MODULE)}
		: {$TESTS['DB']}{\App\Language::translate('LBL_PER_SECOND', $QUALIFIED_MODULE)}</h5>
	</div>
{/strip}
