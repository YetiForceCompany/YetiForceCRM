<?php

/**
 * @file
 *
 * csrf-magic is a PHP library that makes adding CSRF-protection to your
 * web applications a snap. No need to modify every form or create a database
 * of valid nonces; just include this file at the top of every
 * web-accessible page (or even better, your common include file included
 * in every page), and forget about it! (There are, of course, configuration
 * options for advanced users).
 *
 * This library is PHP4 and PHP5 compatible.
 */
class CSRF
{

	/**
	 * By default, when you include this file csrf-magic will automatically check
	 * and exit if the CSRF token is invalid. This will defer executing
	 * csrf_check() until you're ready.  You can also pass false as a parameter to
	 * that function, in which case the function will not exit but instead return
	 * a boolean false if the CSRF check failed. This allows for tighter integration
	 * with your system.
	 */
	public static $defer = false;

	/**
	 * This is the amount of seconds you wish to allow before any token becomes
	 * invalid; the default is two hours, which should be more than enough for
	 * most websites.
	 */
	public static $expires = 7200;

	/**
	 * Callback function to execute when there's the CSRF check fails and
	 * $fatal === true (see csrf_check). This will usually output an error message
	 * about the failure.
	 */
	public static $callback = 'CSRF::responseForIllegalAccess'; //'csrf_callback'

	/**
	 * Whether or not to include our JavaScript library which also rewrites
	 * AJAX requests on this domain. Set this to the web path. This setting only works
	 * with supported JavaScript libraries in Internet Explorer; see README.txt for
	 * a list of supported libraries.
	 */
	public static $rewriteJs = 'libraries/csrf-magic/csrf-magic.js';

	/**
	 * A secret key used when hashing items. Please generate a random string and
	 * place it here. If you change this value, all previously generated tokens
	 * will become invalid.
	 * nota bene: library code should use csrf_get_secret() and not access
	 * this global directly
	 */
	public static $secret = '';

	/**
	 * Set this to false to disable csrf-magic's output handler, and therefore,
	 * its rewriting capabilities. If you're serving non HTML content, you should
	 * definitely set this false.
	 */
	public static $rewrite = true;

	/**
	 * Whether or not to use IP addresses when binding a user to a token. This is
	 * less reliable and less secure than sessions, but is useful when you need
	 * to give facilities to anonymous users and do not wish to maintain a database
	 * of valid keys.
	 */
	public static $allowIp = true;

	/**
	 * If this information is available, use the cookie by this name to determine
	 * whether or not to allow the request. This is a shortcut implementation
	 * very similar to 'key', but we randomly set the cookie ourselves.
	 */
	public static $cookie = '__vtrfck'; // __csrf_cookie

	/**
	 * If this information is available, set this to a unique identifier (it
	 * can be an integer or a unique username) for the current "user" of this
	 * application. The token will then be globally valid for all of that user's
	 * operations, but no one else. This requires that 'secret' be set.
	 */
	public static $user = false;

	/**
	 * This is an arbitrary secret value associated with the user's session. This
	 * will most probably be the contents of a cookie, as an attacker cannot easily
	 * determine this information. Warning: If the attacker knows this value, they
	 * can easily spoof a token. This is a generic implementation; sessions should
	 * work in most cases.
	 *
	 * Why would you want to use this? Lets suppose you have a squid cache for your
	 * website, and the presence of a session cookie bypasses it. Let's also say
	 * you allow anonymous users to interact with the website; submitting forms
	 * and AJAX. Previously, you didn't have any CSRF protection for anonymous users
	 * and so they never got sessions; you don't want to start using sessions either,
	 * otherwise you'll bypass the Squid cache. Setup a different cookie for CSRF
	 * tokens, and have Squid ignore that cookie for get requests, for anonymous
	 * users. (If you haven't guessed, this scheme was(?) used for MediaWiki).
	 */
	public static $key = false;

	/**
	 * The name of the magic CSRF token that will be placed in all forms, i.e.
	 * the contents of <input type="hidden" name="$name" value="CSRF-TOKEN" />
	 */
	public static $inputName = '__vtrftk'; // __csrf_magic

	/**
	 * Set this to false if your site must work inside of frame/iframe elements,
	 * but do so at your own risk: this configuration protects you against CSS
	 * overlay attacks that defeat tokens.
	 */
	public static $frameBreaker = true;

	/**
	 * Whether or not CSRF Magic should be allowed to start a new session in order
	 * to determine the key.
	 */
	public static $autoSession = true;

	/**
	 * Whether or not csrf-magic should produce XHTML style tags.
	 */
	public static $xhtml = true;

	/**
	 * Don't edit this!
	 */
	public static $version = '1.0.4';

	/**
	 * Even though the user told us to rewrite, we should do a quick heuristic
	 * to check if the page is *actually* HTML. We don't begin rewriting until
	 * we hit the first <html tag.
	 */
	public static $isHtml = false;
	public static $isPartial = false;

	/**
	 * Rewrites <form> on the fly to add CSRF tokens to them. This can also
	 * inject our JavaScript library.
	 */
	public static function obHandler($buffer, $flags)
	{
		if (!static::$isHtml) {
			// not HTML until proven otherwise
			if (stripos($buffer, '<html') !== false) {
				static::$isHtml = true;
			} else {
				// Customized to take the partial HTML with form
				static::$isHtml = true;
				static::$isPartial = true;

				// Determine based on content type.
				$headers = headers_list();
				foreach ($headers as $header) {
					if (static::$isHtml) {
						break;
					} else if (stripos('Content-type', $header) !== false && stripos('/html', $header) === false) {
						static::$isHtml = false;
					}
				}
				if (!static::$isHtml) {
					return $buffer;
				}
			}
		}
		$count = 1;
		$tokens = static::getTokens();
		$endSlash = static::$xhtml ? ' /' : '';

		$input = "<input type='hidden' name='" . static::$inputName . "' value=\"$tokens\"$endSlash>";
		$buffer = preg_replace('#(<form[^>]*method\s*=\s*["\']post["\'][^>]*>)#i', '$1' . $input, $buffer);
		if (static::$frameBreaker && !static::$isPartial) {
			$buffer = preg_replace('/<\/head>/', '<script type="text/javascript">if (top != self) {top.location.href = self.location.href;}</script></head>', $buffer, $count);
		}
		if (($js = static::$rewriteJs) && !static::$isPartial) {
			$buffer = preg_replace(
				'/<\/head>/', '<script type="text/javascript">' .
				'var csrfMagicToken = "' . $tokens . '";' .
				'var csrfMagicName = "' . static::$inputName . '";</script>' .
				'<script src="' . $js . '" type="text/javascript"></script></head>', $buffer, $count
			);
			$script = '<script type="text/javascript">CsrfMagic.end();</script>';
			$buffer = preg_replace('/<\/body>/', $script . '</body>', $buffer, $count);
			if (!$count) {
				$buffer .= $script;
			}
		}
		return $buffer;
	}

	/**
	 * Checks if this is a post request, and if it is, checks if the nonce is valid.
	 * @param bool $fatal Whether or not to fatally error out if there is a problem.
	 * @return True if check passes or is not necessary, false if failure.
	 */
	public static function check($fatal = true)
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST')
			return true;
		static::start();
		$ok = false;
		$tokens = '';
		do {
			if (!isset($_POST[static::$inputName]))
				break;
			// we don't regenerate a token and check it because some token creation
			// schemes are volatile.
			$tokens = $_POST[static::$inputName];
			if (!static::checkTokens($tokens))
				break;
			$ok = true;
		} while (false);
		if ($fatal && !$ok) {
			if (trim($tokens, 'A..Za..z0..9:;,') !== '')
				$tokens = 'hidden';
			call_user_func(static::$callback, $tokens);
			exit;
		}
		return $ok;
	}

	/**
	 * Retrieves a valid token(s) for a particular context. Tokens are separated
	 * by semicolons.
	 */
	public static function getTokens()
	{
		$hasCookies = !empty($_COOKIE);

		// $ip implements a composite key, which is sent if the user hasn't sent
		// any cookies. It may or may not be used, depending on whether or not
		// the cookies "stick"
		$secret = static::getSecret();
		if (!$hasCookies && $secret) {
			$ip = ';ip:' . static::hash($_SERVER['IP_ADDRESS']);
		} else {
			$ip = '';
		}
		static::start();

		// These are "strong" algorithms that don't require per se a secret
		if (session_id())
			return 'sid:' . static::hash(session_id()) . $ip;
		if (static::$cookie) {
			$val = static::generateSecret();
			setcookie(static::$cookie, $val);
			return 'cookie:' . static::hash($val) . $ip;
		}
		if (static::$key)
			return 'key:' . static::hash(static::$key) . $ip;
		// These further algorithms require a server-side secret
		if (!$secret)
			return 'invalid';
		if (static::$user !== false) {
			return 'user:' . static::hash(static::$user);
		}
		if (static::$allowIp) {
			return ltrim($ip, ';');
		}
		return 'invalid';
	}

	public static function flattenpost($data)
	{
		$ret = [];
		foreach ($data as $n => $v) {
			$ret = array_merge($ret, static::flattenpost2(1, $n, $v));
		}
		return $ret;
	}

	public static function flattenpost2($level, $key, $data)
	{
		if (!is_array($data))
			return array($key => $data);
		$ret = array();
		foreach ($data as $n => $v) {
			$nk = $level >= 1 ? $key . "[$n]" : "[$n]";
			$ret = array_merge($ret, static::flattenpost2($level + 1, $nk, $v));
		}
		return $ret;
	}

	/**
	 * @param $tokens is safe for HTML consumption
	 */
	public static function callback($tokens)
	{
		// (yes, $tokens is safe to echo without escaping)
		header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
		$data = '';
		foreach (static::flattenpost($_POST) as $key => $value) {
			if ($key == static::$inputName)
				continue;
			$data .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" />';
		}
		echo "<html><head><title>CSRF check failed</title></head>
        <body>
        <p>CSRF check failed. Your form session may have expired, or you may not have
        cookies enabled.</p>
        <form method='post' action=''>$data<input type='submit' value='Try again' /></form>
        <p>Debug: $tokens</p></body></html>";
	}

	/**
	 * Function to echo response when CSRF check fails
	 * This should be helpful in production. For debigging use csrf_callback().
	 * It is configurable by setting $GLOBALS['csrf']['callback'] in this file
	 */
	public static function responseForIllegalAccess($tokens)
	{
		echo 'Invalid request - Response For Illegal Access';
	}

	/**
	 * Checks if a composite token is valid. Outward facing code should use this
	 * instead of csrf_check_token()
	 */
	public static function checkTokens($tokens)
	{
		if (is_string($tokens))
			$tokens = explode(';', $tokens);
		foreach ($tokens as $token) {
			if (static::checkToken($token))
				return true;
		}
		return false;
	}

	/**
	 * Checks if a token is valid.
	 */
	public static function checkToken($token)
	{
		if (strpos($token, ':') === false)
			return false;
		list($type, $value) = explode(':', $token, 2);
		if (strpos($value, ',') === false)
			return false;
		list($x, $time) = explode(',', $token, 2);
		if (static::$expires) {
			if (time() > $time + static::$expires)
				return false;
		}
		switch ($type) {
			case 'sid':
				return $value === static::hash(session_id(), $time);
			case 'cookie':
				$n = static::$cookie;
				if (!$n)
					return false;
				if (!isset($_COOKIE[$n]))
					return false;
				return $value === static::hash($_COOKIE[$n], $time);
			case 'key':
				if (!static::$key)
					return false;
				return $value === static::hash(static::$key, $time);
			// We could disable these 'weaker' checks if 'key' was set, but
			// that doesn't make me feel good then about the cookie-based
			// implementation.
			case 'user':
				if (!static::getSecret())
					return false;
				if (static::$user === false)
					return false;
				return $value === static::hash(static::$user, $time);
			case 'ip':
				if (!static::getSecret())
					return false;
				// do not allow IP-based checks if the username is set, or if
				// the browser sent cookies
				if (static::$user !== false)
					return false;
				if (!empty($_COOKIE))
					return false;
				if (!static::$allowIp)
					return false;
				return $value === static::hash($_SERVER['IP_ADDRESS'], $time);
		}
		return false;
	}

	/**
	 * Sets a configuration value.
	 */
	public static function conf($key, $val)
	{
		if (!isset(static::$$key)) {
			trigger_error('No such configuration ' . $key, E_USER_WARNING);
			return;
		}
		static::$$key = $val;
	}

	/**
	 * Starts a session if we're allowed to.
	 */
	public static function start()
	{
		if (static::$autoSession && !session_id()) {
			session_start();
		}
	}

	/**
	 * Retrieves the secret, and generates one if necessary.
	 */
	public static function getSecret()
	{
		if (static::$secret) {
			return static::$secret;
		}
		$dir = dirname(__FILE__);
		$file = $dir . '/../../config/csrf_secret.php';
		$secret = '';
		if (file_exists($file)) {
			include $file;
			return $secret;
		}
		if (is_writable($dir)) {
			$secret = static::generateSecret();
			$fh = fopen($file, 'w');
			fwrite($fh, '<?php $secret = "' . $secret . '";' . PHP_EOL);
			fclose($fh);
			return $secret;
		}
		return '';
	}

	/**
	 * Generates a random string as the hash of time, microtime, and mt_rand.
	 */
	public static function generateSecret($len = 32)
	{
		$r = '';
		for ($i = 0; $i < 32; $i++) {
			$r .= chr(mt_rand(0, 255));
		}
		$r .= time() . microtime();
		return sha1($r);
	}

	/**
	 * Generates a hash/expiry double. If time isn't set it will be calculated
	 * from the current time.
	 */
	public static function hash($value, $time = null)
	{
		if (!$time) {
			$time = time();
		}
		return sha1(static::getSecret() . $value . $time) . ',' . $time;
	}

	public static function init()
	{
		// Load user configuration
		if (function_exists('CSRFConfig::startup')) {
			CSRFConfig::startup();
		}
		// Initialize our handler
		if (static::$rewrite) {
			ob_start(['self', 'obHandler']);
		}
		// Perform check
		if (!static::$defer) {
			static::check();
		}
	}
}

CSRF::init();
