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
		'asterisk', 'plus', 'euro', 'minus', 'cloud', 'envelope', 'pencil', 'glass', 'music', 'search', 'heart', 'star', 'star-empty', 'user', 'film', 'th-large', 'th', 'th-list', 'ok', 'remove', 'zoom-in', 'zoom-out', 'off', 'signal', 'cog', 'trash', 'home', 'file', 'time', 'road', 'download-alt', 'download', 'upload', 'inbox', 'play-circle', 'repeat', 'refresh', 'list-alt', 'lock', 'flag', 'headphones', 'volume-off', 'volume-down', 'volume-up', 'qrcode', 'barcode', 'tag', 'tags', 'book', 'bookmark', 'print', 'camera', 'font', 'bold', 'italic', 'text-height', 'text-width', 'align-left', 'align-center', 'align-right', 'align-justify', 'list', 'indent-left', 'indent-right', 'facetime-video', 'picture', 'map-marker', 'adjust', 'tint', 'edit', 'share', 'check', 'move', 'step-backward', 'fast-backward', 'backward', 'play', 'pause', 'stop', 'forward', 'fast-forward', 'step-forward', 'eject', 'chevron-left', 'chevron-right', 'plus-sign', 'minus-sign', 'remove-sign', 'ok-sign', 'question-sign', 'info-sign', 'screenshot', 'remove-circle', 'ok-circle', 'ban-circle', 'arrow-left', 'arrow-right', 'arrow-up', 'arrow-down', 'share-alt', 'resize-full', 'resize-small', 'exclamation-sign', 'gift', 'leaf', 'fire', 'eye-open', 'eye-close', 'warning-sign', 'plane', 'calendar', 'random', 'comment', 'magnet', 'chevron-up', 'chevron-down', 'retweet', 'shopping-cart', 'folder-close', 'folder-open', 'resize-vertical', 'resize-horizontal', 'hdd', 'bullhorn', 'bell', 'certificate', 'thumbs-up', 'thumbs-down', 'hand-right', 'hand-left', 'hand-up', 'hand-down', 'circle-arrow-right', 'circle-arrow-left', 'circle-arrow-up', 'circle-arrow-down', 'globe', 'wrench', 'tasks', 'filter', 'briefcase', 'fullscreen', 'dashboard', 'paperclip', 'heart-empty', 'link', 'phone', 'pushpin', 'usd', 'gbp', 'sort', 'sort-by-alphabet', 'sort-by-alphabet-alt', 'sort-by-order', 'sort-by-order-alt', 'sort-by-attributes', 'sort-by-attributes-alt', 'unchecked', 'expand', 'collapse-down', 'collapse-up', 'log-in', 'flash', 'log-out', 'new-window', 'record', 'save', 'open', 'saved', 'import', 'export', 'send', 'floppy-disk', 'floppy-saved', 'floppy-remove', 'floppy-save', 'floppy-open', 'credit-card', 'transfer', 'cutlery', 'header', 'compressed', 'earphone', 'phone-alt', 'tower', 'stats', 'sd-video', 'hd-video', 'subtitles', 'sound-stereo', 'sound-dolby', 'sound-5-1', 'sound-6-1', 'sound-7-1', 'copyright-mark', 'registration-mark', 'cloud-download', 'cloud-upload', 'tree-conifer', 'tree-deciduous', 'cd', 'save-file', 'open-file', 'level-up', 'copy', 'paste', 'alert', 'equalizer', 'king', 'queen', 'pawn', 'bishop', 'knight', 'baby-formula', 'tent', 'blackboard', 'bed', 'apple', 'erase', 'hourglass', 'lamp', 'duplicate', 'piggy-bank', 'scissors', 'bitcoin', 'btc', 'xbt', 'yen', 'jpy', 'ruble', 'rub', 'scale', 'ice-lolly', 'ice-lolly-tasted', 'education', 'option-horizontal', 'option-vertical', 'menu-hamburger', 'modal-window', 'oil', 'grain', 'sunglasses', 'text-size', 'text-color', 'text-background', 'object-align-top', 'object-align-bottom', 'object-align-horizontal', 'object-align-left', 'object-align-vertical', 'object-align-right', 'triangle-right', 'triangle-left', 'triangle-bottom', 'triangle-top', 'console', 'superscript', 'subscript', 'menu-left', 'menu-right', 'menu-down', 'menu-up',
	];
	protected static $user = [
		'ISTN', 'ISTDN', 'ISTRN', 'IGDN', 'IGIN', 'IGRN', 'IIDN', 'IPreOrder', 'IStorages', 'KnowledgeBase', 'ShoppingCart', 'Target', 'Plane', 'WithoutOwnersAccounts', 'VendorsAccounts', 'CompanyAccounts', 'ShutdownAccounts', 'MyAccounts', 'UnassignedAccounts', 'AllAccounts', 'RecycleBin', 'FInvoice', 'FInvoiceProforma', 'ModComments', 'FBookkeeping', 'Events', 'Activity', 'Marketing', 'SRecurringOrders', 'Competition', 'Accounts', 'CompaniesAndContact', 'HolidaysEntitlement', 'Assets', 'Bookkeeping', 'SCalculations', 'Calendar', 'CallHistory', 'Campaigns', 'Contacts', 'OSSMailView', 'Database', 'Documents', 'OSSEmployees', 'Faq', 'Home', 'HumanResources', 'Ideas', 'Leads', 'LettersIn', 'LettersOut', 'PBXManager', 'EmailTemplates', 'OSSMail', 'Announcements', 'SSalesProcesses', 'Portal', 'OutsourcedProducts', 'OSSOutsourcedServices', 'Partners', 'OSSPasswords', 'PaymentsIn', 'PaymentsOut', 'PriceBooks', 'Products', 'ProjectMilestone', 'Project', 'ProjectTask', 'SQuotes', 'Realization', 'Reports', 'Reservations', 'Rss', 'Sales', 'SQuoteEnquiries', 'SSingleOrders', 'SRequirementsCards', 'Secretary', 'ServiceContracts', 'Services', 'OSSSoldServices', 'Support', 'SMSNotifier', 'HelpDesk', 'OSSTimeControl', 'Vendors', 'VirtualDesk',
	];
	protected static $admin = [
		'logistics', 'about-yetiforce', 'locks', 'automation', 'passwords-encryption', 'server-updates', 'profiles', 'currencies', 'system-configuration', 'advenced-modules', 'ldap', 'backup', 'calendar-configuration', 'calendar-holidys', 'calendar-labels-colors', 'calendar-types', 'colors', 'company-detlis', 'company-information', 'contributors', 'credit-limit-base_2', 'cron', 'currency', 'customer-portal', 'filed-mapping', 'dav-applications', 'discount-configuration', 'discount-base', 'document_flow', 'document-templates', 'modules-fields', 'field-folders', 'fields-picklists', 'fields-picklists-relations', 'fields-quick-create', 'filters-configuration', 'finances', 'groups', 'filed-hide-bloks', 'integration', 'languages-and-translations', 'license', 'logs', 'oss_mailview', 'mail-auto-login', 'mail-configuration', 'mail-download-history', 'mail-roundcube', 'mail-scanner', 'mail-smtp-server', 'mail-tools', 'mapped-fields', 'address', 'marketing', 'menu-configuration', 'mobile-applications', 'module-access', 'modules-installation', 'modules-prefixes', 'modules-relations', 'modules-track-chanegs', 'modules-widgets', 'online-forms', 'brute-force', 'passwords-configuration', 'pbx-manager', 'modules-pdf-templates', 'permissions', 'processes', 'realization', 'recording-control', 'roles', 'sales', 'search-and-filtres', 'search-configuration', 'security', 'server-configuration', 'special-access', 'standard-modules', 'support', 'users', 'system-messages', 'system-tools', 'taxes-caonfiguration', 'taxes-rates', 'terms-and-conditions', 'triggers', 'user', 'users-login', 'widgets-configuration', 'workflow', 'yeti-force',
	];
	protected static $additional = [
		'NetworkInterfaceCards', 'UtmNgf', 'Virtualization', 'Ups', 'Notebooks', 'VideoConference', 'Encryption', 'Servers', 'EmailProtection', 'Other', 'BackUpCopies', 'Matrixes', 'Monitoring', 'Antivirus'
	];
	protected static $fontAwesome = [
		'500px', 'adjust', 'adn', 'align-center', 'align-justify', 'align-left', 'align-right', 'amazon', 'ambulance', 'american-sign-language-interpreting', 'anchor', 'android', 'angellist', 'angle-double-down', 'angle-double-left', 'angle-double-right', 'angle-double-up', 'angle-down', 'angle-left', 'angle-right', 'angle-up', 'apple', 'archive', 'area-chart', 'arrow-circle-down', 'arrow-circle-left', 'arrow-circle-o-down', 'arrow-circle-o-left', 'arrow-circle-o-right', 'arrow-circle-o-up', 'arrow-circle-right', 'arrow-circle-up', 'arrow-down', 'arrow-left', 'arrow-right', 'arrow-up', 'arrows', 'arrows-alt', 'arrows-h', 'arrows-v', 'assistive-listening-systems', 'asterisk', 'at', 'audio-description', 'backward', 'balance-scale', 'ban', 'bar-chart', 'barcode', 'bars', 'battery-empty', 'battery-full', 'battery-half', 'battery-quarter', 'battery-three-quarters', 'bed', 'beer', 'behance', 'behance-square', 'bell', 'bell-o', 'bell-slash', 'bell-slash-o', 'bicycle', 'binoculars', 'birthday-cake', 'bitbucket', 'bitbucket-square', 'black-tie', 'blind', 'bluetooth', 'bluetooth-b', 'bold', 'bolt', 'bomb', 'book', 'bookmark', 'bookmark-o', 'braille', 'briefcase', 'btc', 'bug', 'building', 'building-o', 'bullhorn', 'bullseye', 'bus', 'buysellads', 'calculator', 'calendar', 'calendar-check-o', 'calendar-minus-o', 'calendar-o', 'calendar-plus-o', 'calendar-times-o', 'camera', 'camera-retro', 'car', 'caret-down', 'caret-left', 'caret-right', 'caret-square-o-down', 'caret-square-o-left', 'caret-square-o-right', 'caret-square-o-up', 'caret-up', 'cart-arrow-down', 'cart-plus', 'cc', 'cc-amex', 'cc-diners-club', 'cc-discover', 'cc-jcb', 'cc-mastercard', 'cc-paypal', 'cc-stripe', 'cc-visa', 'certificate', 'chain-broken', 'check', 'check-circle', 'check-circle-o', 'check-square', 'check-square-o', 'chevron-circle-down', 'chevron-circle-left', 'chevron-circle-right', 'chevron-circle-up', 'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 'child', 'chrome', 'circle', 'circle-o', 'circle-o-notch', 'circle-thin', 'clipboard', 'clock-o', 'clone', 'cloud', 'cloud-download', 'cloud-upload', 'code', 'code-fork', 'codepen', 'codiepie', 'coffee', 'cog', 'cogs', 'columns', 'comment', 'comment-o', 'commenting', 'commenting-o', 'comments', 'comments-o', 'compass', 'compress', 'connectdevelop', 'contao', 'copyright', 'creative-commons', 'credit-card', 'credit-card-alt', 'crop', 'crosshairs', 'css3', 'cube', 'cubes', 'cutlery', 'dashcube', 'database', 'deaf', 'delicious', 'desktop', 'deviantart', 'diamond', 'digg', 'dot-circle-o', 'download', 'dribbble', 'dropbox', 'drupal', 'edge', 'eject', 'ellipsis-h', 'ellipsis-v', 'empire', 'envelope', 'envelope-o', 'envelope-square', 'envira', 'eraser', 'eur', 'exchange', 'exclamation', 'exclamation-circle', 'exclamation-triangle', 'expand', 'expeditedssl', 'external-link', 'external-link-square', 'eye', 'eye-slash', 'eyedropper', 'facebook', 'facebook-official', 'facebook-square', 'fast-backward', 'fast-forward', 'fax', 'female', 'fighter-jet', 'file', 'file-archive-o', 'file-audio-o', 'file-code-o', 'file-excel-o', 'file-image-o', 'file-o', 'file-pdf-o', 'file-powerpoint-o', 'file-text', 'file-text-o', 'file-video-o', 'file-word-o', 'files-o', 'film', 'filter', 'fire', 'fire-extinguisher', 'firefox', 'first-order', 'flag', 'flag-checkered', 'flag-o', 'flask', 'flickr', 'floppy-o', 'folder', 'folder-o', 'folder-open', 'folder-open-o', 'font', 'font-awesome', 'fonticons', 'fort-awesome', 'forumbee', 'forward', 'foursquare', 'frown-o', 'futbol-o', 'gamepad', 'gavel', 'gbp', 'genderless', 'get-pocket', 'gg', 'gg-circle', 'gift', 'git', 'git-square', 'github', 'github-alt', 'github-square', 'gitlab', 'glass', 'glide', 'glide-g', 'globe', 'google', 'google-plus', 'google-plus-official', 'google-plus-square', 'google-wallet', 'graduation-cap', 'gratipay', 'h-square', 'hacker-news', 'hand-lizard-o', 'hand-o-down', 'hand-o-left', 'hand-o-right', 'hand-o-up', 'hand-paper-o', 'hand-peace-o', 'hand-pointer-o', 'hand-rock-o', 'hand-scissors-o', 'hand-spock-o', 'hashtag', 'hdd-o', 'header', 'headphones', 'heart', 'heart-o', 'heartbeat', 'history', 'home', 'hospital-o', 'hourglass', 'hourglass-end', 'hourglass-half', 'hourglass-o', 'hourglass-start', 'houzz', 'html5', 'i-cursor', 'ils', 'inbox', 'indent', 'industry', 'info', 'info-circle', 'inr', 'instagram', 'internet-explorer', 'ioxhost', 'italic', 'joomla', 'jpy', 'jsfiddle', 'key', 'keyboard-o', 'krw', 'language', 'laptop', 'lastfm', 'lastfm-square', 'leaf', 'leanpub', 'lemon-o', 'level-down', 'level-up', 'life-ring', 'lightbulb-o', 'line-chart', 'link', 'linkedin', 'linkedin-square', 'linux', 'list', 'list-alt', 'list-ol', 'list-ul', 'location-arrow', 'lock', 'long-arrow-down', 'long-arrow-left', 'long-arrow-right', 'long-arrow-up', 'low-vision', 'magic', 'magnet', 'male', 'map', 'map-marker', 'map-o', 'map-pin', 'map-signs', 'mars', 'mars-double', 'mars-stroke', 'mars-stroke-h', 'mars-stroke-v', 'maxcdn', 'meanpath', 'medium', 'medkit', 'meh-o', 'mercury', 'microphone', 'microphone-slash', 'minus', 'minus-circle', 'minus-square', 'minus-square-o', 'mixcloud', 'mobile', 'modx', 'money', 'moon-o', 'motorcycle', 'mouse-pointer', 'music', 'neuter', 'newspaper-o', 'object-group', 'object-ungroup', 'odnoklassniki', 'odnoklassniki-square', 'opencart', 'openid', 'opera', 'optin-monster', 'outdent', 'pagelines', 'paint-brush', 'paper-plane', 'paper-plane-o', 'paperclip', 'paragraph', 'pause', 'pause-circle', 'pause-circle-o', 'paw', 'paypal', 'pencil', 'pencil-square', 'pencil-square-o', 'percent', 'phone', 'phone-square', 'picture-o', 'pie-chart', 'pied-piper', 'pied-piper-alt', 'pied-piper-pp', 'pinterest', 'pinterest-p', 'pinterest-square', 'plane', 'play', 'play-circle', 'play-circle-o', 'plug', 'plus', 'plus-circle', 'plus-square', 'plus-square-o', 'power-off', 'print', 'product-hunt', 'puzzle-piece', 'qq', 'qrcode', 'question', 'question-circle', 'question-circle-o', 'quote-left', 'quote-right', 'random', 'rebel', 'recycle', 'reddit', 'reddit-alien', 'reddit-square', 'refresh', 'registered', 'renren', 'repeat', 'reply', 'reply-all', 'retweet', 'road', 'rocket', 'rss', 'rss-square', 'rub', 'safari', 'scissors', 'scribd', 'search', 'search-minus', 'search-plus', 'sellsy', 'server', 'share', 'share-alt', 'share-alt-square', 'share-square', 'share-square-o', 'shield', 'ship', 'shirtsinbulk', 'shopping-bag', 'shopping-basket', 'shopping-cart', 'sign-in', 'sign-language', 'sign-out', 'signal', 'simplybuilt', 'sitemap', 'skyatlas', 'skype', 'slack', 'sliders', 'slideshare', 'smile-o', 'snapchat', 'snapchat-ghost', 'snapchat-square', 'sort', 'sort-alpha-asc', 'sort-alpha-desc', 'sort-amount-asc', 'sort-amount-desc', 'sort-asc', 'sort-desc', 'sort-numeric-asc', 'sort-numeric-desc', 'soundcloud', 'space-shuttle', 'spinner', 'spoon', 'spotify', 'square', 'square-o', 'stack-exchange', 'stack-overflow', 'star', 'star-half', 'star-half-o', 'star-o', 'steam', 'steam-square', 'step-backward', 'step-forward', 'stethoscope', 'sticky-note', 'sticky-note-o', 'stop', 'stop-circle', 'stop-circle-o', 'street-view', 'strikethrough', 'stumbleupon', 'stumbleupon-circle', 'subscript', 'subway', 'suitcase', 'sun-o', 'superscript', 'table', 'tablet', 'tachometer', 'tag', 'tags', 'tasks', 'taxi', 'television', 'tencent-weibo', 'terminal', 'text-height', 'text-width', 'th', 'th-large', 'th-list', 'themeisle', 'thumb-tack', 'thumbs-down', 'thumbs-o-down', 'thumbs-o-up', 'thumbs-up', 'ticket', 'times', 'times-circle', 'times-circle-o', 'tint', 'toggle-off', 'toggle-on', 'trademark', 'train', 'transgender', 'transgender-alt', 'trash', 'trash-o', 'tree', 'trello', 'tripadvisor', 'trophy', 'truck', 'try', 'tty', 'tumblr', 'tumblr-square', 'twitch', 'twitter', 'twitter-square', 'umbrella', 'underline', 'undo', 'universal-access', 'university', 'unlock', 'unlock-alt', 'upload', 'usb', 'usd', 'user', 'user-md', 'user-plus', 'user-secret', 'user-times', 'users', 'venus', 'venus-double', 'venus-mars', 'viacoin', 'viadeo', 'viadeo-square', 'video-camera', 'vimeo', 'vimeo-square', 'vine', 'vk', 'volume-control-phone', 'volume-down', 'volume-off', 'volume-up', 'weibo', 'weixin', 'whatsapp', 'wheelchair', 'wheelchair-alt', 'wifi', 'wikipedia-w', 'windows', 'wordpress', 'wpbeginner', 'wpforms', 'wrench', 'xing', 'xing-square', 'y-combinator', 'yahoo', 'yelp', 'yoast', 'youtube', 'youtube-play', 'youtube-square'
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

	public static function getFontAwesomeIcon()
	{
		$icons = [];
		foreach (self::$fontAwesome as $icon) {
			$icons[$icon] = 'fa-' . $icon;
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
				$mimeType = \App\Fields\File::getMimeContentType($path . $file);
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
		$icons = array_merge($icons, self::getFontAwesomeIcon());
		return $icons;
	}
}
