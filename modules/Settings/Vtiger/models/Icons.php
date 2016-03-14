<?php

/**
 * Settings Icons Model Class
 * @package YetiForce.Settings.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Vtiger_Icons_Model
{

	protected static $glyphicon = [
		'asterisk','plus','euro','minus','cloud','envelope','pencil','glass','music','search','heart','star','star-empty','user','film','th-large','th','th-list','ok','remove','zoom-in','zoom-out','off','signal','cog','trash','home','file','time','road','download-alt','download','upload','inbox','play-circle','repeat','refresh','list-alt','lock','flag','headphones','volume-off','volume-down','volume-up','qrcode','barcode','tag','tags','book','bookmark','print','camera','font','bold','italic','text-height','text-width','align-left','align-center','align-right','align-justify','list','indent-left','indent-right','facetime-video','picture','map-marker','adjust','tint','edit','share','check','move','step-backward','fast-backward','backward','play','pause','stop','forward','fast-forward','step-forward','eject','chevron-left','chevron-right','plus-sign','minus-sign','remove-sign','ok-sign','question-sign','info-sign','screenshot','remove-circle','ok-circle','ban-circle','arrow-left','arrow-right','arrow-up','arrow-down','share-alt','resize-full','resize-small','exclamation-sign','gift','leaf','fire','eye-open','eye-close','warning-sign','plane','calendar','random','comment','magnet','chevron-up','chevron-down','retweet','shopping-cart','folder-close','folder-open','resize-vertical','resize-horizontal','hdd','bullhorn','bell','certificate','thumbs-up','thumbs-down','hand-right','hand-left','hand-up','hand-down','circle-arrow-right','circle-arrow-left','circle-arrow-up','circle-arrow-down','globe','wrench','tasks','filter','briefcase','fullscreen','dashboard','paperclip','heart-empty','link','phone','pushpin','usd','gbp','sort','sort-by-alphabet','sort-by-alphabet-alt','sort-by-order','sort-by-order-alt','sort-by-attributes','sort-by-attributes-alt','unchecked','expand','collapse-down','collapse-up','log-in','flash','log-out','new-window','record','save','open','saved','import','export','send','floppy-disk','floppy-saved','floppy-remove','floppy-save','floppy-open','credit-card','transfer','cutlery','header','compressed','earphone','phone-alt','tower','stats','sd-video','hd-video','subtitles','sound-stereo','sound-dolby','sound-5-1','sound-6-1','sound-7-1','copyright-mark','registration-mark','cloud-download','cloud-upload','tree-conifer','tree-deciduous','cd','save-file','open-file','level-up','copy','paste','alert','equalizer','king','queen','pawn','bishop','knight','baby-formula','tent','blackboard','bed','apple','erase','hourglass','lamp','duplicate','piggy-bank','scissors','bitcoin','btc','xbt','yen','jpy','ruble','rub','scale','ice-lolly','ice-lolly-tasted','education','option-horizontal','option-vertical','menu-hamburger','modal-window','oil','grain','sunglasses','text-size','text-color','text-background','object-align-top','object-align-bottom','object-align-horizontal','object-align-left','object-align-vertical','object-align-right','triangle-right','triangle-left','triangle-bottom','triangle-top','console','superscript','subscript','menu-left','menu-right','menu-down','menu-up',
	];
	protected static $user = [
		'ISTN','ISTDN','ISTRN','IGDN','IGIN','IGRN','IIDN','IPreOrder','IStorages','KnowledgeBase','ShoppingCart','Target','Plane','WithoutOwnersAccounts','VendorsAccounts','CompanyAccounts','ShutdownAccounts','MyAccounts','UnassignedAccounts','AllAccounts','RecycleBin','FInvoice','FInvoiceProforma','ModComments','FBookkeeping','Events','Activity','Marketing','SRecurringOrders','Competition','Accounts','CompaniesAndContact','HolidaysEntitlement','Assets','Bookkeeping','SCalculations','Calendar','CallHistory','Campaigns','Contacts','OSSMailView','Database','Documents','OSSEmployees','Faq','Home','HumanResources','Ideas','Leads','LettersIn','LettersOut','PBXManager','OSSMailTemplates','OSSMail','NewOrders','SSalesProcesses','Portal','OutsourcedProducts','OSSOutsourcedServices','Partners','OSSPasswords','PaymentsIn','PaymentsOut','PriceBooks','Products','ProjectMilestone','Project','ProjectTask','SQuotes','Realization','Reports','Reservations','Rss','Sales','SQuoteEnquiries','SSingleOrders','SRequirementsCards','Secretary','ServiceContracts','Services','OSSSoldServices','Support','SMSNotifier','HelpDesk','OSSTimeControl','Vendors','VirtualDesk',
	];
	protected static $admin = [
		'logistics','about-yetiforce','locks','automation','passwords-encryption','server-updates','profiles','currencies','system-configuration','advenced-modules','ldap','backup','calendar-configuration','calendar-holidys','calendar-labels-colors','calendar-types','colors','company-detlis','company-information','contributors','credit-limit-base_2','cron','currency','customer-portal','filed-mapping','dav-applications','discount-configuration','discount-base','document_flow','document-templates','modules-fields','field-folders','fields-picklists','fields-picklists-relations','fields-quick-create','filters-configuration','finances','groups','filed-hide-bloks','integration','languages-and-translations','license','logs','oss_mailview','mail-auto-login','mail-configuration','mail-download-history','mail-roundcube','mail-scanner','mail-smtp-server','mail-tools','mapped-fields','address','marketing','menu-configuration','mobile-applications','module-access','modules-installation','modules-prefixes','modules-relations','modules-track-chanegs','modules-widgets','online-forms','brute-force','passwords-configuration','pbx-manager','modules-pdf-templates','permissions','processes','realization','recording-control','roles','sales','search-and-filtres','search-configuration','security','server-configuration','special-access','standard-modules','support','users','system-messages','system-tools','taxes-caonfiguration','taxes-rates','terms-and-conditions','triggers','user','users-login','widgets-configuration','workflow','yeti-force',
	];
	protected static $additional = [
		'Cloudmark','Mcafee','Securedoc','Eset','Avast','Deslock','Drweb','Fsecure','Gdata','Kaspersky','Panda','Sophos','Symantec','Ttrendmicro','Webroot','NetworkInterfaceCards','UtmNgf','Virtualization','Ups','Notebooks','VideoConference','Encryption','Servers','EmailProtection','Other','BackUpCopies','Matrixes','Monitoring','Antivirus',
	];

	public static function getGlyphicon()
	{
		$icons = [];
		foreach (self::$glyphicon as $icon) {
			$iconName = str_replace('-', ' ', $icon);
			$icons[$iconName] = 'glyphicon-' . $icon;
		}
		return $icons;
	}

	public static function getUserIcon()
	{
		$icons = [];
		foreach (self::$user as $icon) {
			$icons[$icon] = 'userIcon-' . $icon;
		}
		return $icons;
	}

	public static function getAdminIcon()
	{
		$icons = [];
		foreach (self::$admin as $icon) {
			$icons[$icon] = 'adminIcon-' . $icon;
		}
		return $icons;
	}

	public static function getAdditionalIcon()
	{
		$icons = [];
		foreach (self::$additional as $icon) {
			$icons[$icon] = 'AdditionalIcon-' . $icon;
		}
		return $icons;
	}

	public static function getImageIcon()
	{
		$images = [];
		$path = Vtiger_Theme::getBaseThemePath() . '/icons/images/';
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo) {
			$file = $fileinfo->getFilename();
			if (!$fileinfo->isDot()) {
				$mimeType = Vtiger_Functions::getMimeContentType($path . $file);
				$mimeTypeContents = explode('/', $mimeType);
				if ($mimeTypeContents[0] == 'image') {
					$images[$file] = $path . $file;
				}
			}
		}
		return $images;
	}

	public static function getAll()
	{
		$icons = [];
		$icons = array_merge($icons, self::getGlyphicon());
		$icons = array_merge($icons, self::getUserIcon());
		$icons = array_merge($icons, self::getAdminIcon());
		$icons = array_merge($icons, self::getAdditionalIcon());
		return $icons;
	}
}
