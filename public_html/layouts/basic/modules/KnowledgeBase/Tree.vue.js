'use strict';

var global$1 = (typeof global !== "undefined" ? global :
            typeof self !== "undefined" ? self :
            typeof window !== "undefined" ? window : {});

// shim for using process in browser
// based off https://github.com/defunctzombie/node-process/blob/master/browser.js

function defaultSetTimout() {
    throw new Error('setTimeout has not been defined');
}
function defaultClearTimeout () {
    throw new Error('clearTimeout has not been defined');
}
var cachedSetTimeout = defaultSetTimout;
var cachedClearTimeout = defaultClearTimeout;
if (typeof global$1.setTimeout === 'function') {
    cachedSetTimeout = setTimeout;
}
if (typeof global$1.clearTimeout === 'function') {
    cachedClearTimeout = clearTimeout;
}

function runTimeout(fun) {
    if (cachedSetTimeout === setTimeout) {
        //normal enviroments in sane situations
        return setTimeout(fun, 0);
    }
    // if setTimeout wasn't available but was latter defined
    if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
        cachedSetTimeout = setTimeout;
        return setTimeout(fun, 0);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedSetTimeout(fun, 0);
    } catch(e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
            return cachedSetTimeout.call(null, fun, 0);
        } catch(e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
            return cachedSetTimeout.call(this, fun, 0);
        }
    }


}
function runClearTimeout(marker) {
    if (cachedClearTimeout === clearTimeout) {
        //normal enviroments in sane situations
        return clearTimeout(marker);
    }
    // if clearTimeout wasn't available but was latter defined
    if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
        cachedClearTimeout = clearTimeout;
        return clearTimeout(marker);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedClearTimeout(marker);
    } catch (e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
            return cachedClearTimeout.call(null, marker);
        } catch (e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
            // Some versions of I.E. have different rules for clearTimeout vs setTimeout
            return cachedClearTimeout.call(this, marker);
        }
    }



}
var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
    if (!draining || !currentQueue) {
        return;
    }
    draining = false;
    if (currentQueue.length) {
        queue = currentQueue.concat(queue);
    } else {
        queueIndex = -1;
    }
    if (queue.length) {
        drainQueue();
    }
}

function drainQueue() {
    if (draining) {
        return;
    }
    var timeout = runTimeout(cleanUpNextTick);
    draining = true;

    var len = queue.length;
    while(len) {
        currentQueue = queue;
        queue = [];
        while (++queueIndex < len) {
            if (currentQueue) {
                currentQueue[queueIndex].run();
            }
        }
        queueIndex = -1;
        len = queue.length;
    }
    currentQueue = null;
    draining = false;
    runClearTimeout(timeout);
}
function nextTick(fun) {
    var args = new Array(arguments.length - 1);
    if (arguments.length > 1) {
        for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
        }
    }
    queue.push(new Item(fun, args));
    if (queue.length === 1 && !draining) {
        runTimeout(drainQueue);
    }
}
// v8 likes predictible objects
function Item(fun, array) {
    this.fun = fun;
    this.array = array;
}
Item.prototype.run = function () {
    this.fun.apply(null, this.array);
};
var title = 'browser';
var platform = 'browser';
var browser = true;
var env = {};
var argv = [];
var version = ''; // empty string to avoid regexp issues
var versions = {};
var release = {};
var config = {};

function noop() {}

var on = noop;
var addListener = noop;
var once = noop;
var off = noop;
var removeListener = noop;
var removeAllListeners = noop;
var emit = noop;

function binding(name) {
    throw new Error('process.binding is not supported');
}

function cwd () { return '/' }
function chdir (dir) {
    throw new Error('process.chdir is not supported');
}function umask() { return 0; }

// from https://github.com/kumavis/browser-process-hrtime/blob/master/index.js
var performance = global$1.performance || {};
var performanceNow =
  performance.now        ||
  performance.mozNow     ||
  performance.msNow      ||
  performance.oNow       ||
  performance.webkitNow  ||
  function(){ return (new Date()).getTime() };

// generate timestamp or delta
// see http://nodejs.org/api/process.html#process_process_hrtime
function hrtime(previousTimestamp){
  var clocktime = performanceNow.call(performance)*1e-3;
  var seconds = Math.floor(clocktime);
  var nanoseconds = Math.floor((clocktime%1)*1e9);
  if (previousTimestamp) {
    seconds = seconds - previousTimestamp[0];
    nanoseconds = nanoseconds - previousTimestamp[1];
    if (nanoseconds<0) {
      seconds--;
      nanoseconds += 1e9;
    }
  }
  return [seconds,nanoseconds]
}

var startTime = new Date();
function uptime() {
  var currentTime = new Date();
  var dif = currentTime - startTime;
  return dif / 1000;
}

var process = {
  nextTick: nextTick,
  title: title,
  browser: browser,
  env: env,
  argv: argv,
  version: version,
  versions: versions,
  on: on,
  addListener: addListener,
  once: once,
  off: off,
  removeListener: removeListener,
  removeAllListeners: removeAllListeners,
  emit: emit,
  binding: binding,
  cwd: cwd,
  chdir: chdir,
  umask: umask,
  hrtime: hrtime,
  platform: platform,
  release: release,
  config: config,
  uptime: uptime
};

/*!
 * Vue.js v2.6.10
 * (c) 2014-2019 Evan You
 * Released under the MIT License.
 */
/*  */

var emptyObject = Object.freeze({});

// These helpers produce better VM code in JS engines due to their
// explicitness and function inlining.
function isUndef (v) {
  return v === undefined || v === null
}

function isDef (v) {
  return v !== undefined && v !== null
}

function isTrue (v) {
  return v === true
}

function isFalse (v) {
  return v === false
}

/**
 * Check if value is primitive.
 */
function isPrimitive (value) {
  return (
    typeof value === 'string' ||
    typeof value === 'number' ||
    // $flow-disable-line
    typeof value === 'symbol' ||
    typeof value === 'boolean'
  )
}

/**
 * Quick object check - this is primarily used to tell
 * Objects from primitive values when we know the value
 * is a JSON-compliant type.
 */
function isObject (obj) {
  return obj !== null && typeof obj === 'object'
}

/**
 * Get the raw type string of a value, e.g., [object Object].
 */
var _toString = Object.prototype.toString;

function toRawType (value) {
  return _toString.call(value).slice(8, -1)
}

/**
 * Strict object type check. Only returns true
 * for plain JavaScript objects.
 */
function isPlainObject (obj) {
  return _toString.call(obj) === '[object Object]'
}

function isRegExp (v) {
  return _toString.call(v) === '[object RegExp]'
}

/**
 * Check if val is a valid array index.
 */
function isValidArrayIndex (val) {
  var n = parseFloat(String(val));
  return n >= 0 && Math.floor(n) === n && isFinite(val)
}

function isPromise (val) {
  return (
    isDef(val) &&
    typeof val.then === 'function' &&
    typeof val.catch === 'function'
  )
}

/**
 * Convert a value to a string that is actually rendered.
 */
function toString (val) {
  return val == null
    ? ''
    : Array.isArray(val) || (isPlainObject(val) && val.toString === _toString)
      ? JSON.stringify(val, null, 2)
      : String(val)
}

/**
 * Convert an input value to a number for persistence.
 * If the conversion fails, return original string.
 */
function toNumber (val) {
  var n = parseFloat(val);
  return isNaN(n) ? val : n
}

/**
 * Make a map and return a function for checking if a key
 * is in that map.
 */
function makeMap (
  str,
  expectsLowerCase
) {
  var map = Object.create(null);
  var list = str.split(',');
  for (var i = 0; i < list.length; i++) {
    map[list[i]] = true;
  }
  return expectsLowerCase
    ? function (val) { return map[val.toLowerCase()]; }
    : function (val) { return map[val]; }
}

/**
 * Check if a tag is a built-in tag.
 */
var isBuiltInTag = makeMap('slot,component', true);

/**
 * Check if an attribute is a reserved attribute.
 */
var isReservedAttribute = makeMap('key,ref,slot,slot-scope,is');

/**
 * Remove an item from an array.
 */
function remove (arr, item) {
  if (arr.length) {
    var index = arr.indexOf(item);
    if (index > -1) {
      return arr.splice(index, 1)
    }
  }
}

/**
 * Check whether an object has the property.
 */
var hasOwnProperty = Object.prototype.hasOwnProperty;
function hasOwn (obj, key) {
  return hasOwnProperty.call(obj, key)
}

/**
 * Create a cached version of a pure function.
 */
function cached (fn) {
  var cache = Object.create(null);
  return (function cachedFn (str) {
    var hit = cache[str];
    return hit || (cache[str] = fn(str))
  })
}

/**
 * Camelize a hyphen-delimited string.
 */
var camelizeRE = /-(\w)/g;
var camelize = cached(function (str) {
  return str.replace(camelizeRE, function (_, c) { return c ? c.toUpperCase() : ''; })
});

/**
 * Capitalize a string.
 */
var capitalize = cached(function (str) {
  return str.charAt(0).toUpperCase() + str.slice(1)
});

/**
 * Hyphenate a camelCase string.
 */
var hyphenateRE = /\B([A-Z])/g;
var hyphenate = cached(function (str) {
  return str.replace(hyphenateRE, '-$1').toLowerCase()
});

/**
 * Simple bind polyfill for environments that do not support it,
 * e.g., PhantomJS 1.x. Technically, we don't need this anymore
 * since native bind is now performant enough in most browsers.
 * But removing it would mean breaking code that was able to run in
 * PhantomJS 1.x, so this must be kept for backward compatibility.
 */

/* istanbul ignore next */
function polyfillBind (fn, ctx) {
  function boundFn (a) {
    var l = arguments.length;
    return l
      ? l > 1
        ? fn.apply(ctx, arguments)
        : fn.call(ctx, a)
      : fn.call(ctx)
  }

  boundFn._length = fn.length;
  return boundFn
}

function nativeBind (fn, ctx) {
  return fn.bind(ctx)
}

var bind = Function.prototype.bind
  ? nativeBind
  : polyfillBind;

/**
 * Convert an Array-like object to a real Array.
 */
function toArray (list, start) {
  start = start || 0;
  var i = list.length - start;
  var ret = new Array(i);
  while (i--) {
    ret[i] = list[i + start];
  }
  return ret
}

/**
 * Mix properties into target object.
 */
function extend (to, _from) {
  for (var key in _from) {
    to[key] = _from[key];
  }
  return to
}

/**
 * Merge an Array of Objects into a single Object.
 */
function toObject (arr) {
  var res = {};
  for (var i = 0; i < arr.length; i++) {
    if (arr[i]) {
      extend(res, arr[i]);
    }
  }
  return res
}

/* eslint-disable no-unused-vars */

/**
 * Perform no operation.
 * Stubbing args to make Flow happy without leaving useless transpiled code
 * with ...rest (https://flow.org/blog/2017/05/07/Strict-Function-Call-Arity/).
 */
function noop$1 (a, b, c) {}

/**
 * Always return false.
 */
var no = function (a, b, c) { return false; };

/* eslint-enable no-unused-vars */

/**
 * Return the same value.
 */
var identity = function (_) { return _; };

/**
 * Check if two values are loosely equal - that is,
 * if they are plain objects, do they have the same shape?
 */
function looseEqual (a, b) {
  if (a === b) { return true }
  var isObjectA = isObject(a);
  var isObjectB = isObject(b);
  if (isObjectA && isObjectB) {
    try {
      var isArrayA = Array.isArray(a);
      var isArrayB = Array.isArray(b);
      if (isArrayA && isArrayB) {
        return a.length === b.length && a.every(function (e, i) {
          return looseEqual(e, b[i])
        })
      } else if (a instanceof Date && b instanceof Date) {
        return a.getTime() === b.getTime()
      } else if (!isArrayA && !isArrayB) {
        var keysA = Object.keys(a);
        var keysB = Object.keys(b);
        return keysA.length === keysB.length && keysA.every(function (key) {
          return looseEqual(a[key], b[key])
        })
      } else {
        /* istanbul ignore next */
        return false
      }
    } catch (e) {
      /* istanbul ignore next */
      return false
    }
  } else if (!isObjectA && !isObjectB) {
    return String(a) === String(b)
  } else {
    return false
  }
}

/**
 * Return the first index at which a loosely equal value can be
 * found in the array (if value is a plain object, the array must
 * contain an object of the same shape), or -1 if it is not present.
 */
function looseIndexOf (arr, val) {
  for (var i = 0; i < arr.length; i++) {
    if (looseEqual(arr[i], val)) { return i }
  }
  return -1
}

/**
 * Ensure a function is called only once.
 */
function once$1 (fn) {
  var called = false;
  return function () {
    if (!called) {
      called = true;
      fn.apply(this, arguments);
    }
  }
}

var SSR_ATTR = 'data-server-rendered';

var ASSET_TYPES = [
  'component',
  'directive',
  'filter'
];

var LIFECYCLE_HOOKS = [
  'beforeCreate',
  'created',
  'beforeMount',
  'mounted',
  'beforeUpdate',
  'updated',
  'beforeDestroy',
  'destroyed',
  'activated',
  'deactivated',
  'errorCaptured',
  'serverPrefetch'
];

/*  */



var config$1 = ({
  /**
   * Option merge strategies (used in core/util/options)
   */
  // $flow-disable-line
  optionMergeStrategies: Object.create(null),

  /**
   * Whether to suppress warnings.
   */
  silent: false,

  /**
   * Show production mode tip message on boot?
   */
  productionTip: process.env.NODE_ENV !== 'production',

  /**
   * Whether to enable devtools
   */
  devtools: process.env.NODE_ENV !== 'production',

  /**
   * Whether to record perf
   */
  performance: false,

  /**
   * Error handler for watcher errors
   */
  errorHandler: null,

  /**
   * Warn handler for watcher warns
   */
  warnHandler: null,

  /**
   * Ignore certain custom elements
   */
  ignoredElements: [],

  /**
   * Custom user key aliases for v-on
   */
  // $flow-disable-line
  keyCodes: Object.create(null),

  /**
   * Check if a tag is reserved so that it cannot be registered as a
   * component. This is platform-dependent and may be overwritten.
   */
  isReservedTag: no,

  /**
   * Check if an attribute is reserved so that it cannot be used as a component
   * prop. This is platform-dependent and may be overwritten.
   */
  isReservedAttr: no,

  /**
   * Check if a tag is an unknown element.
   * Platform-dependent.
   */
  isUnknownElement: no,

  /**
   * Get the namespace of an element
   */
  getTagNamespace: noop$1,

  /**
   * Parse the real tag name for the specific platform.
   */
  parsePlatformTagName: identity,

  /**
   * Check if an attribute must be bound using property, e.g. value
   * Platform-dependent.
   */
  mustUseProp: no,

  /**
   * Perform updates asynchronously. Intended to be used by Vue Test Utils
   * This will significantly reduce performance if set to false.
   */
  async: true,

  /**
   * Exposed for legacy reasons
   */
  _lifecycleHooks: LIFECYCLE_HOOKS
});

/*  */

/**
 * unicode letters used for parsing html tags, component names and property paths.
 * using https://www.w3.org/TR/html53/semantics-scripting.html#potentialcustomelementname
 * skipping \u10000-\uEFFFF due to it freezing up PhantomJS
 */
var unicodeRegExp = /a-zA-Z\u00B7\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u037D\u037F-\u1FFF\u200C-\u200D\u203F-\u2040\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD/;

/**
 * Check if a string starts with $ or _
 */
function isReserved (str) {
  var c = (str + '').charCodeAt(0);
  return c === 0x24 || c === 0x5F
}

/**
 * Define a property.
 */
function def (obj, key, val, enumerable) {
  Object.defineProperty(obj, key, {
    value: val,
    enumerable: !!enumerable,
    writable: true,
    configurable: true
  });
}

/**
 * Parse simple path.
 */
var bailRE = new RegExp(("[^" + (unicodeRegExp.source) + ".$_\\d]"));
function parsePath (path) {
  if (bailRE.test(path)) {
    return
  }
  var segments = path.split('.');
  return function (obj) {
    for (var i = 0; i < segments.length; i++) {
      if (!obj) { return }
      obj = obj[segments[i]];
    }
    return obj
  }
}

/*  */

// can we use __proto__?
var hasProto = '__proto__' in {};

// Browser environment sniffing
var inBrowser = typeof window !== 'undefined';
var inWeex = typeof WXEnvironment !== 'undefined' && !!WXEnvironment.platform;
var weexPlatform = inWeex && WXEnvironment.platform.toLowerCase();
var UA = inBrowser && window.navigator.userAgent.toLowerCase();
var isIE = UA && /msie|trident/.test(UA);
var isIE9 = UA && UA.indexOf('msie 9.0') > 0;
var isEdge = UA && UA.indexOf('edge/') > 0;
var isAndroid = (UA && UA.indexOf('android') > 0) || (weexPlatform === 'android');
var isIOS = (UA && /iphone|ipad|ipod|ios/.test(UA)) || (weexPlatform === 'ios');
var isChrome = UA && /chrome\/\d+/.test(UA) && !isEdge;
var isPhantomJS = UA && /phantomjs/.test(UA);
var isFF = UA && UA.match(/firefox\/(\d+)/);

// Firefox has a "watch" function on Object.prototype...
var nativeWatch = ({}).watch;

var supportsPassive = false;
if (inBrowser) {
  try {
    var opts = {};
    Object.defineProperty(opts, 'passive', ({
      get: function get () {
        /* istanbul ignore next */
        supportsPassive = true;
      }
    })); // https://github.com/facebook/flow/issues/285
    window.addEventListener('test-passive', null, opts);
  } catch (e) {}
}

// this needs to be lazy-evaled because vue may be required before
// vue-server-renderer can set VUE_ENV
var _isServer;
var isServerRendering = function () {
  if (_isServer === undefined) {
    /* istanbul ignore if */
    if (!inBrowser && !inWeex && typeof global$1 !== 'undefined') {
      // detect presence of vue-server-renderer and avoid
      // Webpack shimming the process
      _isServer = global$1['process'] && global$1['process'].env.VUE_ENV === 'server';
    } else {
      _isServer = false;
    }
  }
  return _isServer
};

// detect devtools
var devtools = inBrowser && window.__VUE_DEVTOOLS_GLOBAL_HOOK__;

/* istanbul ignore next */
function isNative (Ctor) {
  return typeof Ctor === 'function' && /native code/.test(Ctor.toString())
}

var hasSymbol =
  typeof Symbol !== 'undefined' && isNative(Symbol) &&
  typeof Reflect !== 'undefined' && isNative(Reflect.ownKeys);

var _Set;
/* istanbul ignore if */ // $flow-disable-line
if (typeof Set !== 'undefined' && isNative(Set)) {
  // use native Set when available.
  _Set = Set;
} else {
  // a non-standard Set polyfill that only works with primitive keys.
  _Set = /*@__PURE__*/(function () {
    function Set () {
      this.set = Object.create(null);
    }
    Set.prototype.has = function has (key) {
      return this.set[key] === true
    };
    Set.prototype.add = function add (key) {
      this.set[key] = true;
    };
    Set.prototype.clear = function clear () {
      this.set = Object.create(null);
    };

    return Set;
  }());
}

/*  */

var warn = noop$1;
var tip = noop$1;
var generateComponentTrace = (noop$1); // work around flow check
var formatComponentName = (noop$1);

{
  var hasConsole = typeof console !== 'undefined';
  var classifyRE = /(?:^|[-_])(\w)/g;
  var classify = function (str) { return str
    .replace(classifyRE, function (c) { return c.toUpperCase(); })
    .replace(/[-_]/g, ''); };

  warn = function (msg, vm) {
    var trace = vm ? generateComponentTrace(vm) : '';

    if (config$1.warnHandler) {
      config$1.warnHandler.call(null, msg, vm, trace);
    } else if (hasConsole && (!config$1.silent)) {
      console.error(("[Vue warn]: " + msg + trace));
    }
  };

  tip = function (msg, vm) {
    if (hasConsole && (!config$1.silent)) {
      console.warn("[Vue tip]: " + msg + (
        vm ? generateComponentTrace(vm) : ''
      ));
    }
  };

  formatComponentName = function (vm, includeFile) {
    if (vm.$root === vm) {
      return '<Root>'
    }
    var options = typeof vm === 'function' && vm.cid != null
      ? vm.options
      : vm._isVue
        ? vm.$options || vm.constructor.options
        : vm;
    var name = options.name || options._componentTag;
    var file = options.__file;
    if (!name && file) {
      var match = file.match(/([^/\\]+)\.vue$/);
      name = match && match[1];
    }

    return (
      (name ? ("<" + (classify(name)) + ">") : "<Anonymous>") +
      (file && includeFile !== false ? (" at " + file) : '')
    )
  };

  var repeat = function (str, n) {
    var res = '';
    while (n) {
      if (n % 2 === 1) { res += str; }
      if (n > 1) { str += str; }
      n >>= 1;
    }
    return res
  };

  generateComponentTrace = function (vm) {
    if (vm._isVue && vm.$parent) {
      var tree = [];
      var currentRecursiveSequence = 0;
      while (vm) {
        if (tree.length > 0) {
          var last = tree[tree.length - 1];
          if (last.constructor === vm.constructor) {
            currentRecursiveSequence++;
            vm = vm.$parent;
            continue
          } else if (currentRecursiveSequence > 0) {
            tree[tree.length - 1] = [last, currentRecursiveSequence];
            currentRecursiveSequence = 0;
          }
        }
        tree.push(vm);
        vm = vm.$parent;
      }
      return '\n\nfound in\n\n' + tree
        .map(function (vm, i) { return ("" + (i === 0 ? '---> ' : repeat(' ', 5 + i * 2)) + (Array.isArray(vm)
            ? ((formatComponentName(vm[0])) + "... (" + (vm[1]) + " recursive calls)")
            : formatComponentName(vm))); })
        .join('\n')
    } else {
      return ("\n\n(found in " + (formatComponentName(vm)) + ")")
    }
  };
}

/*  */

var uid = 0;

/**
 * A dep is an observable that can have multiple
 * directives subscribing to it.
 */
var Dep = function Dep () {
  this.id = uid++;
  this.subs = [];
};

Dep.prototype.addSub = function addSub (sub) {
  this.subs.push(sub);
};

Dep.prototype.removeSub = function removeSub (sub) {
  remove(this.subs, sub);
};

Dep.prototype.depend = function depend () {
  if (Dep.target) {
    Dep.target.addDep(this);
  }
};

Dep.prototype.notify = function notify () {
  // stabilize the subscriber list first
  var subs = this.subs.slice();
  if (!config$1.async) {
    // subs aren't sorted in scheduler if not running async
    // we need to sort them now to make sure they fire in correct
    // order
    subs.sort(function (a, b) { return a.id - b.id; });
  }
  for (var i = 0, l = subs.length; i < l; i++) {
    subs[i].update();
  }
};

// The current target watcher being evaluated.
// This is globally unique because only one watcher
// can be evaluated at a time.
Dep.target = null;
var targetStack = [];

function pushTarget (target) {
  targetStack.push(target);
  Dep.target = target;
}

function popTarget () {
  targetStack.pop();
  Dep.target = targetStack[targetStack.length - 1];
}

/*  */

var VNode = function VNode (
  tag,
  data,
  children,
  text,
  elm,
  context,
  componentOptions,
  asyncFactory
) {
  this.tag = tag;
  this.data = data;
  this.children = children;
  this.text = text;
  this.elm = elm;
  this.ns = undefined;
  this.context = context;
  this.fnContext = undefined;
  this.fnOptions = undefined;
  this.fnScopeId = undefined;
  this.key = data && data.key;
  this.componentOptions = componentOptions;
  this.componentInstance = undefined;
  this.parent = undefined;
  this.raw = false;
  this.isStatic = false;
  this.isRootInsert = true;
  this.isComment = false;
  this.isCloned = false;
  this.isOnce = false;
  this.asyncFactory = asyncFactory;
  this.asyncMeta = undefined;
  this.isAsyncPlaceholder = false;
};

var prototypeAccessors = { child: { configurable: true } };

// DEPRECATED: alias for componentInstance for backwards compat.
/* istanbul ignore next */
prototypeAccessors.child.get = function () {
  return this.componentInstance
};

Object.defineProperties( VNode.prototype, prototypeAccessors );

var createEmptyVNode = function (text) {
  if ( text === void 0 ) text = '';

  var node = new VNode();
  node.text = text;
  node.isComment = true;
  return node
};

function createTextVNode (val) {
  return new VNode(undefined, undefined, undefined, String(val))
}

// optimized shallow clone
// used for static nodes and slot nodes because they may be reused across
// multiple renders, cloning them avoids errors when DOM manipulations rely
// on their elm reference.
function cloneVNode (vnode) {
  var cloned = new VNode(
    vnode.tag,
    vnode.data,
    // #7975
    // clone children array to avoid mutating original in case of cloning
    // a child.
    vnode.children && vnode.children.slice(),
    vnode.text,
    vnode.elm,
    vnode.context,
    vnode.componentOptions,
    vnode.asyncFactory
  );
  cloned.ns = vnode.ns;
  cloned.isStatic = vnode.isStatic;
  cloned.key = vnode.key;
  cloned.isComment = vnode.isComment;
  cloned.fnContext = vnode.fnContext;
  cloned.fnOptions = vnode.fnOptions;
  cloned.fnScopeId = vnode.fnScopeId;
  cloned.asyncMeta = vnode.asyncMeta;
  cloned.isCloned = true;
  return cloned
}

/*
 * not type checking this file because flow doesn't play well with
 * dynamically accessing methods on Array prototype
 */

var arrayProto = Array.prototype;
var arrayMethods = Object.create(arrayProto);

var methodsToPatch = [
  'push',
  'pop',
  'shift',
  'unshift',
  'splice',
  'sort',
  'reverse'
];

/**
 * Intercept mutating methods and emit events
 */
methodsToPatch.forEach(function (method) {
  // cache original method
  var original = arrayProto[method];
  def(arrayMethods, method, function mutator () {
    var args = [], len = arguments.length;
    while ( len-- ) args[ len ] = arguments[ len ];

    var result = original.apply(this, args);
    var ob = this.__ob__;
    var inserted;
    switch (method) {
      case 'push':
      case 'unshift':
        inserted = args;
        break
      case 'splice':
        inserted = args.slice(2);
        break
    }
    if (inserted) { ob.observeArray(inserted); }
    // notify change
    ob.dep.notify();
    return result
  });
});

/*  */

var arrayKeys = Object.getOwnPropertyNames(arrayMethods);

/**
 * In some cases we may want to disable observation inside a component's
 * update computation.
 */
var shouldObserve = true;

function toggleObserving (value) {
  shouldObserve = value;
}

/**
 * Observer class that is attached to each observed
 * object. Once attached, the observer converts the target
 * object's property keys into getter/setters that
 * collect dependencies and dispatch updates.
 */
var Observer = function Observer (value) {
  this.value = value;
  this.dep = new Dep();
  this.vmCount = 0;
  def(value, '__ob__', this);
  if (Array.isArray(value)) {
    if (hasProto) {
      protoAugment(value, arrayMethods);
    } else {
      copyAugment(value, arrayMethods, arrayKeys);
    }
    this.observeArray(value);
  } else {
    this.walk(value);
  }
};

/**
 * Walk through all properties and convert them into
 * getter/setters. This method should only be called when
 * value type is Object.
 */
Observer.prototype.walk = function walk (obj) {
  var keys = Object.keys(obj);
  for (var i = 0; i < keys.length; i++) {
    defineReactive$$1(obj, keys[i]);
  }
};

/**
 * Observe a list of Array items.
 */
Observer.prototype.observeArray = function observeArray (items) {
  for (var i = 0, l = items.length; i < l; i++) {
    observe(items[i]);
  }
};

// helpers

/**
 * Augment a target Object or Array by intercepting
 * the prototype chain using __proto__
 */
function protoAugment (target, src) {
  /* eslint-disable no-proto */
  target.__proto__ = src;
  /* eslint-enable no-proto */
}

/**
 * Augment a target Object or Array by defining
 * hidden properties.
 */
/* istanbul ignore next */
function copyAugment (target, src, keys) {
  for (var i = 0, l = keys.length; i < l; i++) {
    var key = keys[i];
    def(target, key, src[key]);
  }
}

/**
 * Attempt to create an observer instance for a value,
 * returns the new observer if successfully observed,
 * or the existing observer if the value already has one.
 */
function observe (value, asRootData) {
  if (!isObject(value) || value instanceof VNode) {
    return
  }
  var ob;
  if (hasOwn(value, '__ob__') && value.__ob__ instanceof Observer) {
    ob = value.__ob__;
  } else if (
    shouldObserve &&
    !isServerRendering() &&
    (Array.isArray(value) || isPlainObject(value)) &&
    Object.isExtensible(value) &&
    !value._isVue
  ) {
    ob = new Observer(value);
  }
  if (asRootData && ob) {
    ob.vmCount++;
  }
  return ob
}

/**
 * Define a reactive property on an Object.
 */
function defineReactive$$1 (
  obj,
  key,
  val,
  customSetter,
  shallow
) {
  var dep = new Dep();

  var property = Object.getOwnPropertyDescriptor(obj, key);
  if (property && property.configurable === false) {
    return
  }

  // cater for pre-defined getter/setters
  var getter = property && property.get;
  var setter = property && property.set;
  if ((!getter || setter) && arguments.length === 2) {
    val = obj[key];
  }

  var childOb = !shallow && observe(val);
  Object.defineProperty(obj, key, {
    enumerable: true,
    configurable: true,
    get: function reactiveGetter () {
      var value = getter ? getter.call(obj) : val;
      if (Dep.target) {
        dep.depend();
        if (childOb) {
          childOb.dep.depend();
          if (Array.isArray(value)) {
            dependArray(value);
          }
        }
      }
      return value
    },
    set: function reactiveSetter (newVal) {
      var value = getter ? getter.call(obj) : val;
      /* eslint-disable no-self-compare */
      if (newVal === value || (newVal !== newVal && value !== value)) {
        return
      }
      /* eslint-enable no-self-compare */
      if (customSetter) {
        customSetter();
      }
      // #7981: for accessor properties without setter
      if (getter && !setter) { return }
      if (setter) {
        setter.call(obj, newVal);
      } else {
        val = newVal;
      }
      childOb = !shallow && observe(newVal);
      dep.notify();
    }
  });
}

/**
 * Set a property on an object. Adds the new property and
 * triggers change notification if the property doesn't
 * already exist.
 */
function set (target, key, val) {
  if (isUndef(target) || isPrimitive(target)
  ) {
    warn(("Cannot set reactive property on undefined, null, or primitive value: " + ((target))));
  }
  if (Array.isArray(target) && isValidArrayIndex(key)) {
    target.length = Math.max(target.length, key);
    target.splice(key, 1, val);
    return val
  }
  if (key in target && !(key in Object.prototype)) {
    target[key] = val;
    return val
  }
  var ob = (target).__ob__;
  if (target._isVue || (ob && ob.vmCount)) {
    warn(
      'Avoid adding reactive properties to a Vue instance or its root $data ' +
      'at runtime - declare it upfront in the data option.'
    );
    return val
  }
  if (!ob) {
    target[key] = val;
    return val
  }
  defineReactive$$1(ob.value, key, val);
  ob.dep.notify();
  return val
}

/**
 * Delete a property and trigger change if necessary.
 */
function del (target, key) {
  if (isUndef(target) || isPrimitive(target)
  ) {
    warn(("Cannot delete reactive property on undefined, null, or primitive value: " + ((target))));
  }
  if (Array.isArray(target) && isValidArrayIndex(key)) {
    target.splice(key, 1);
    return
  }
  var ob = (target).__ob__;
  if (target._isVue || (ob && ob.vmCount)) {
    warn(
      'Avoid deleting properties on a Vue instance or its root $data ' +
      '- just set it to null.'
    );
    return
  }
  if (!hasOwn(target, key)) {
    return
  }
  delete target[key];
  if (!ob) {
    return
  }
  ob.dep.notify();
}

/**
 * Collect dependencies on array elements when the array is touched, since
 * we cannot intercept array element access like property getters.
 */
function dependArray (value) {
  for (var e = (void 0), i = 0, l = value.length; i < l; i++) {
    e = value[i];
    e && e.__ob__ && e.__ob__.dep.depend();
    if (Array.isArray(e)) {
      dependArray(e);
    }
  }
}

/*  */

/**
 * Option overwriting strategies are functions that handle
 * how to merge a parent option value and a child option
 * value into the final value.
 */
var strats = config$1.optionMergeStrategies;

/**
 * Options with restrictions
 */
{
  strats.el = strats.propsData = function (parent, child, vm, key) {
    if (!vm) {
      warn(
        "option \"" + key + "\" can only be used during instance " +
        'creation with the `new` keyword.'
      );
    }
    return defaultStrat(parent, child)
  };
}

/**
 * Helper that recursively merges two data objects together.
 */
function mergeData (to, from) {
  if (!from) { return to }
  var key, toVal, fromVal;

  var keys = hasSymbol
    ? Reflect.ownKeys(from)
    : Object.keys(from);

  for (var i = 0; i < keys.length; i++) {
    key = keys[i];
    // in case the object is already observed...
    if (key === '__ob__') { continue }
    toVal = to[key];
    fromVal = from[key];
    if (!hasOwn(to, key)) {
      set(to, key, fromVal);
    } else if (
      toVal !== fromVal &&
      isPlainObject(toVal) &&
      isPlainObject(fromVal)
    ) {
      mergeData(toVal, fromVal);
    }
  }
  return to
}

/**
 * Data
 */
function mergeDataOrFn (
  parentVal,
  childVal,
  vm
) {
  if (!vm) {
    // in a Vue.extend merge, both should be functions
    if (!childVal) {
      return parentVal
    }
    if (!parentVal) {
      return childVal
    }
    // when parentVal & childVal are both present,
    // we need to return a function that returns the
    // merged result of both functions... no need to
    // check if parentVal is a function here because
    // it has to be a function to pass previous merges.
    return function mergedDataFn () {
      return mergeData(
        typeof childVal === 'function' ? childVal.call(this, this) : childVal,
        typeof parentVal === 'function' ? parentVal.call(this, this) : parentVal
      )
    }
  } else {
    return function mergedInstanceDataFn () {
      // instance merge
      var instanceData = typeof childVal === 'function'
        ? childVal.call(vm, vm)
        : childVal;
      var defaultData = typeof parentVal === 'function'
        ? parentVal.call(vm, vm)
        : parentVal;
      if (instanceData) {
        return mergeData(instanceData, defaultData)
      } else {
        return defaultData
      }
    }
  }
}

strats.data = function (
  parentVal,
  childVal,
  vm
) {
  if (!vm) {
    if (childVal && typeof childVal !== 'function') {
      warn(
        'The "data" option should be a function ' +
        'that returns a per-instance value in component ' +
        'definitions.',
        vm
      );

      return parentVal
    }
    return mergeDataOrFn(parentVal, childVal)
  }

  return mergeDataOrFn(parentVal, childVal, vm)
};

/**
 * Hooks and props are merged as arrays.
 */
function mergeHook (
  parentVal,
  childVal
) {
  var res = childVal
    ? parentVal
      ? parentVal.concat(childVal)
      : Array.isArray(childVal)
        ? childVal
        : [childVal]
    : parentVal;
  return res
    ? dedupeHooks(res)
    : res
}

function dedupeHooks (hooks) {
  var res = [];
  for (var i = 0; i < hooks.length; i++) {
    if (res.indexOf(hooks[i]) === -1) {
      res.push(hooks[i]);
    }
  }
  return res
}

LIFECYCLE_HOOKS.forEach(function (hook) {
  strats[hook] = mergeHook;
});

/**
 * Assets
 *
 * When a vm is present (instance creation), we need to do
 * a three-way merge between constructor options, instance
 * options and parent options.
 */
function mergeAssets (
  parentVal,
  childVal,
  vm,
  key
) {
  var res = Object.create(parentVal || null);
  if (childVal) {
    assertObjectType(key, childVal, vm);
    return extend(res, childVal)
  } else {
    return res
  }
}

ASSET_TYPES.forEach(function (type) {
  strats[type + 's'] = mergeAssets;
});

/**
 * Watchers.
 *
 * Watchers hashes should not overwrite one
 * another, so we merge them as arrays.
 */
strats.watch = function (
  parentVal,
  childVal,
  vm,
  key
) {
  // work around Firefox's Object.prototype.watch...
  if (parentVal === nativeWatch) { parentVal = undefined; }
  if (childVal === nativeWatch) { childVal = undefined; }
  /* istanbul ignore if */
  if (!childVal) { return Object.create(parentVal || null) }
  {
    assertObjectType(key, childVal, vm);
  }
  if (!parentVal) { return childVal }
  var ret = {};
  extend(ret, parentVal);
  for (var key$1 in childVal) {
    var parent = ret[key$1];
    var child = childVal[key$1];
    if (parent && !Array.isArray(parent)) {
      parent = [parent];
    }
    ret[key$1] = parent
      ? parent.concat(child)
      : Array.isArray(child) ? child : [child];
  }
  return ret
};

/**
 * Other object hashes.
 */
strats.props =
strats.methods =
strats.inject =
strats.computed = function (
  parentVal,
  childVal,
  vm,
  key
) {
  if (childVal && process.env.NODE_ENV !== 'production') {
    assertObjectType(key, childVal, vm);
  }
  if (!parentVal) { return childVal }
  var ret = Object.create(null);
  extend(ret, parentVal);
  if (childVal) { extend(ret, childVal); }
  return ret
};
strats.provide = mergeDataOrFn;

/**
 * Default strategy.
 */
var defaultStrat = function (parentVal, childVal) {
  return childVal === undefined
    ? parentVal
    : childVal
};

/**
 * Validate component names
 */
function checkComponents (options) {
  for (var key in options.components) {
    validateComponentName(key);
  }
}

function validateComponentName (name) {
  if (!new RegExp(("^[a-zA-Z][\\-\\.0-9_" + (unicodeRegExp.source) + "]*$")).test(name)) {
    warn(
      'Invalid component name: "' + name + '". Component names ' +
      'should conform to valid custom element name in html5 specification.'
    );
  }
  if (isBuiltInTag(name) || config$1.isReservedTag(name)) {
    warn(
      'Do not use built-in or reserved HTML elements as component ' +
      'id: ' + name
    );
  }
}

/**
 * Ensure all props option syntax are normalized into the
 * Object-based format.
 */
function normalizeProps (options, vm) {
  var props = options.props;
  if (!props) { return }
  var res = {};
  var i, val, name;
  if (Array.isArray(props)) {
    i = props.length;
    while (i--) {
      val = props[i];
      if (typeof val === 'string') {
        name = camelize(val);
        res[name] = { type: null };
      } else {
        warn('props must be strings when using array syntax.');
      }
    }
  } else if (isPlainObject(props)) {
    for (var key in props) {
      val = props[key];
      name = camelize(key);
      res[name] = isPlainObject(val)
        ? val
        : { type: val };
    }
  } else {
    warn(
      "Invalid value for option \"props\": expected an Array or an Object, " +
      "but got " + (toRawType(props)) + ".",
      vm
    );
  }
  options.props = res;
}

/**
 * Normalize all injections into Object-based format
 */
function normalizeInject (options, vm) {
  var inject = options.inject;
  if (!inject) { return }
  var normalized = options.inject = {};
  if (Array.isArray(inject)) {
    for (var i = 0; i < inject.length; i++) {
      normalized[inject[i]] = { from: inject[i] };
    }
  } else if (isPlainObject(inject)) {
    for (var key in inject) {
      var val = inject[key];
      normalized[key] = isPlainObject(val)
        ? extend({ from: key }, val)
        : { from: val };
    }
  } else {
    warn(
      "Invalid value for option \"inject\": expected an Array or an Object, " +
      "but got " + (toRawType(inject)) + ".",
      vm
    );
  }
}

/**
 * Normalize raw function directives into object format.
 */
function normalizeDirectives (options) {
  var dirs = options.directives;
  if (dirs) {
    for (var key in dirs) {
      var def$$1 = dirs[key];
      if (typeof def$$1 === 'function') {
        dirs[key] = { bind: def$$1, update: def$$1 };
      }
    }
  }
}

function assertObjectType (name, value, vm) {
  if (!isPlainObject(value)) {
    warn(
      "Invalid value for option \"" + name + "\": expected an Object, " +
      "but got " + (toRawType(value)) + ".",
      vm
    );
  }
}

/**
 * Merge two option objects into a new one.
 * Core utility used in both instantiation and inheritance.
 */
function mergeOptions (
  parent,
  child,
  vm
) {
  {
    checkComponents(child);
  }

  if (typeof child === 'function') {
    child = child.options;
  }

  normalizeProps(child, vm);
  normalizeInject(child, vm);
  normalizeDirectives(child);

  // Apply extends and mixins on the child options,
  // but only if it is a raw options object that isn't
  // the result of another mergeOptions call.
  // Only merged options has the _base property.
  if (!child._base) {
    if (child.extends) {
      parent = mergeOptions(parent, child.extends, vm);
    }
    if (child.mixins) {
      for (var i = 0, l = child.mixins.length; i < l; i++) {
        parent = mergeOptions(parent, child.mixins[i], vm);
      }
    }
  }

  var options = {};
  var key;
  for (key in parent) {
    mergeField(key);
  }
  for (key in child) {
    if (!hasOwn(parent, key)) {
      mergeField(key);
    }
  }
  function mergeField (key) {
    var strat = strats[key] || defaultStrat;
    options[key] = strat(parent[key], child[key], vm, key);
  }
  return options
}

/**
 * Resolve an asset.
 * This function is used because child instances need access
 * to assets defined in its ancestor chain.
 */
function resolveAsset (
  options,
  type,
  id,
  warnMissing
) {
  /* istanbul ignore if */
  if (typeof id !== 'string') {
    return
  }
  var assets = options[type];
  // check local registration variations first
  if (hasOwn(assets, id)) { return assets[id] }
  var camelizedId = camelize(id);
  if (hasOwn(assets, camelizedId)) { return assets[camelizedId] }
  var PascalCaseId = capitalize(camelizedId);
  if (hasOwn(assets, PascalCaseId)) { return assets[PascalCaseId] }
  // fallback to prototype chain
  var res = assets[id] || assets[camelizedId] || assets[PascalCaseId];
  if (warnMissing && !res) {
    warn(
      'Failed to resolve ' + type.slice(0, -1) + ': ' + id,
      options
    );
  }
  return res
}

/*  */



function validateProp (
  key,
  propOptions,
  propsData,
  vm
) {
  var prop = propOptions[key];
  var absent = !hasOwn(propsData, key);
  var value = propsData[key];
  // boolean casting
  var booleanIndex = getTypeIndex(Boolean, prop.type);
  if (booleanIndex > -1) {
    if (absent && !hasOwn(prop, 'default')) {
      value = false;
    } else if (value === '' || value === hyphenate(key)) {
      // only cast empty string / same name to boolean if
      // boolean has higher priority
      var stringIndex = getTypeIndex(String, prop.type);
      if (stringIndex < 0 || booleanIndex < stringIndex) {
        value = true;
      }
    }
  }
  // check default value
  if (value === undefined) {
    value = getPropDefaultValue(vm, prop, key);
    // since the default value is a fresh copy,
    // make sure to observe it.
    var prevShouldObserve = shouldObserve;
    toggleObserving(true);
    observe(value);
    toggleObserving(prevShouldObserve);
  }
  {
    assertProp(prop, key, value, vm, absent);
  }
  return value
}

/**
 * Get the default value of a prop.
 */
function getPropDefaultValue (vm, prop, key) {
  // no default, return undefined
  if (!hasOwn(prop, 'default')) {
    return undefined
  }
  var def = prop.default;
  // warn against non-factory defaults for Object & Array
  if (isObject(def)) {
    warn(
      'Invalid default value for prop "' + key + '": ' +
      'Props with type Object/Array must use a factory function ' +
      'to return the default value.',
      vm
    );
  }
  // the raw prop value was also undefined from previous render,
  // return previous default value to avoid unnecessary watcher trigger
  if (vm && vm.$options.propsData &&
    vm.$options.propsData[key] === undefined &&
    vm._props[key] !== undefined
  ) {
    return vm._props[key]
  }
  // call factory function for non-Function types
  // a value is Function if its prototype is function even across different execution context
  return typeof def === 'function' && getType(prop.type) !== 'Function'
    ? def.call(vm)
    : def
}

/**
 * Assert whether a prop is valid.
 */
function assertProp (
  prop,
  name,
  value,
  vm,
  absent
) {
  if (prop.required && absent) {
    warn(
      'Missing required prop: "' + name + '"',
      vm
    );
    return
  }
  if (value == null && !prop.required) {
    return
  }
  var type = prop.type;
  var valid = !type || type === true;
  var expectedTypes = [];
  if (type) {
    if (!Array.isArray(type)) {
      type = [type];
    }
    for (var i = 0; i < type.length && !valid; i++) {
      var assertedType = assertType(value, type[i]);
      expectedTypes.push(assertedType.expectedType || '');
      valid = assertedType.valid;
    }
  }

  if (!valid) {
    warn(
      getInvalidTypeMessage(name, value, expectedTypes),
      vm
    );
    return
  }
  var validator = prop.validator;
  if (validator) {
    if (!validator(value)) {
      warn(
        'Invalid prop: custom validator check failed for prop "' + name + '".',
        vm
      );
    }
  }
}

var simpleCheckRE = /^(String|Number|Boolean|Function|Symbol)$/;

function assertType (value, type) {
  var valid;
  var expectedType = getType(type);
  if (simpleCheckRE.test(expectedType)) {
    var t = typeof value;
    valid = t === expectedType.toLowerCase();
    // for primitive wrapper objects
    if (!valid && t === 'object') {
      valid = value instanceof type;
    }
  } else if (expectedType === 'Object') {
    valid = isPlainObject(value);
  } else if (expectedType === 'Array') {
    valid = Array.isArray(value);
  } else {
    valid = value instanceof type;
  }
  return {
    valid: valid,
    expectedType: expectedType
  }
}

/**
 * Use function string name to check built-in types,
 * because a simple equality check will fail when running
 * across different vms / iframes.
 */
function getType (fn) {
  var match = fn && fn.toString().match(/^\s*function (\w+)/);
  return match ? match[1] : ''
}

function isSameType (a, b) {
  return getType(a) === getType(b)
}

function getTypeIndex (type, expectedTypes) {
  if (!Array.isArray(expectedTypes)) {
    return isSameType(expectedTypes, type) ? 0 : -1
  }
  for (var i = 0, len = expectedTypes.length; i < len; i++) {
    if (isSameType(expectedTypes[i], type)) {
      return i
    }
  }
  return -1
}

function getInvalidTypeMessage (name, value, expectedTypes) {
  var message = "Invalid prop: type check failed for prop \"" + name + "\"." +
    " Expected " + (expectedTypes.map(capitalize).join(', '));
  var expectedType = expectedTypes[0];
  var receivedType = toRawType(value);
  var expectedValue = styleValue(value, expectedType);
  var receivedValue = styleValue(value, receivedType);
  // check if we need to specify expected value
  if (expectedTypes.length === 1 &&
      isExplicable(expectedType) &&
      !isBoolean(expectedType, receivedType)) {
    message += " with value " + expectedValue;
  }
  message += ", got " + receivedType + " ";
  // check if we need to specify received value
  if (isExplicable(receivedType)) {
    message += "with value " + receivedValue + ".";
  }
  return message
}

function styleValue (value, type) {
  if (type === 'String') {
    return ("\"" + value + "\"")
  } else if (type === 'Number') {
    return ("" + (Number(value)))
  } else {
    return ("" + value)
  }
}

function isExplicable (value) {
  var explicitTypes = ['string', 'number', 'boolean'];
  return explicitTypes.some(function (elem) { return value.toLowerCase() === elem; })
}

function isBoolean () {
  var args = [], len = arguments.length;
  while ( len-- ) args[ len ] = arguments[ len ];

  return args.some(function (elem) { return elem.toLowerCase() === 'boolean'; })
}

/*  */

function handleError (err, vm, info) {
  // Deactivate deps tracking while processing error handler to avoid possible infinite rendering.
  // See: https://github.com/vuejs/vuex/issues/1505
  pushTarget();
  try {
    if (vm) {
      var cur = vm;
      while ((cur = cur.$parent)) {
        var hooks = cur.$options.errorCaptured;
        if (hooks) {
          for (var i = 0; i < hooks.length; i++) {
            try {
              var capture = hooks[i].call(cur, err, vm, info) === false;
              if (capture) { return }
            } catch (e) {
              globalHandleError(e, cur, 'errorCaptured hook');
            }
          }
        }
      }
    }
    globalHandleError(err, vm, info);
  } finally {
    popTarget();
  }
}

function invokeWithErrorHandling (
  handler,
  context,
  args,
  vm,
  info
) {
  var res;
  try {
    res = args ? handler.apply(context, args) : handler.call(context);
    if (res && !res._isVue && isPromise(res) && !res._handled) {
      res.catch(function (e) { return handleError(e, vm, info + " (Promise/async)"); });
      // issue #9511
      // avoid catch triggering multiple times when nested calls
      res._handled = true;
    }
  } catch (e) {
    handleError(e, vm, info);
  }
  return res
}

function globalHandleError (err, vm, info) {
  if (config$1.errorHandler) {
    try {
      return config$1.errorHandler.call(null, err, vm, info)
    } catch (e) {
      // if the user intentionally throws the original error in the handler,
      // do not log it twice
      if (e !== err) {
        logError(e, null, 'config.errorHandler');
      }
    }
  }
  logError(err, vm, info);
}

function logError (err, vm, info) {
  {
    warn(("Error in " + info + ": \"" + (err.toString()) + "\""), vm);
  }
  /* istanbul ignore else */
  if ((inBrowser || inWeex) && typeof console !== 'undefined') {
    console.error(err);
  } else {
    throw err
  }
}

/*  */

var isUsingMicroTask = false;

var callbacks = [];
var pending = false;

function flushCallbacks () {
  pending = false;
  var copies = callbacks.slice(0);
  callbacks.length = 0;
  for (var i = 0; i < copies.length; i++) {
    copies[i]();
  }
}

// Here we have async deferring wrappers using microtasks.
// In 2.5 we used (macro) tasks (in combination with microtasks).
// However, it has subtle problems when state is changed right before repaint
// (e.g. #6813, out-in transitions).
// Also, using (macro) tasks in event handler would cause some weird behaviors
// that cannot be circumvented (e.g. #7109, #7153, #7546, #7834, #8109).
// So we now use microtasks everywhere, again.
// A major drawback of this tradeoff is that there are some scenarios
// where microtasks have too high a priority and fire in between supposedly
// sequential events (e.g. #4521, #6690, which have workarounds)
// or even between bubbling of the same event (#6566).
var timerFunc;

// The nextTick behavior leverages the microtask queue, which can be accessed
// via either native Promise.then or MutationObserver.
// MutationObserver has wider support, however it is seriously bugged in
// UIWebView in iOS >= 9.3.3 when triggered in touch event handlers. It
// completely stops working after triggering a few times... so, if native
// Promise is available, we will use it:
/* istanbul ignore next, $flow-disable-line */
if (typeof Promise !== 'undefined' && isNative(Promise)) {
  var p = Promise.resolve();
  timerFunc = function () {
    p.then(flushCallbacks);
    // In problematic UIWebViews, Promise.then doesn't completely break, but
    // it can get stuck in a weird state where callbacks are pushed into the
    // microtask queue but the queue isn't being flushed, until the browser
    // needs to do some other work, e.g. handle a timer. Therefore we can
    // "force" the microtask queue to be flushed by adding an empty timer.
    if (isIOS) { setTimeout(noop$1); }
  };
  isUsingMicroTask = true;
} else if (!isIE && typeof MutationObserver !== 'undefined' && (
  isNative(MutationObserver) ||
  // PhantomJS and iOS 7.x
  MutationObserver.toString() === '[object MutationObserverConstructor]'
)) {
  // Use MutationObserver where native Promise is not available,
  // e.g. PhantomJS, iOS7, Android 4.4
  // (#6466 MutationObserver is unreliable in IE11)
  var counter = 1;
  var observer = new MutationObserver(flushCallbacks);
  var textNode = document.createTextNode(String(counter));
  observer.observe(textNode, {
    characterData: true
  });
  timerFunc = function () {
    counter = (counter + 1) % 2;
    textNode.data = String(counter);
  };
  isUsingMicroTask = true;
} else if (typeof setImmediate !== 'undefined' && isNative(setImmediate)) {
  // Fallback to setImmediate.
  // Techinically it leverages the (macro) task queue,
  // but it is still a better choice than setTimeout.
  timerFunc = function () {
    setImmediate(flushCallbacks);
  };
} else {
  // Fallback to setTimeout.
  timerFunc = function () {
    setTimeout(flushCallbacks, 0);
  };
}

function nextTick$1 (cb, ctx) {
  var _resolve;
  callbacks.push(function () {
    if (cb) {
      try {
        cb.call(ctx);
      } catch (e) {
        handleError(e, ctx, 'nextTick');
      }
    } else if (_resolve) {
      _resolve(ctx);
    }
  });
  if (!pending) {
    pending = true;
    timerFunc();
  }
  // $flow-disable-line
  if (!cb && typeof Promise !== 'undefined') {
    return new Promise(function (resolve) {
      _resolve = resolve;
    })
  }
}

/*  */

/* not type checking this file because flow doesn't play well with Proxy */

var initProxy;

{
  var allowedGlobals = makeMap(
    'Infinity,undefined,NaN,isFinite,isNaN,' +
    'parseFloat,parseInt,decodeURI,decodeURIComponent,encodeURI,encodeURIComponent,' +
    'Math,Number,Date,Array,Object,Boolean,String,RegExp,Map,Set,JSON,Intl,' +
    'require' // for Webpack/Browserify
  );

  var warnNonPresent = function (target, key) {
    warn(
      "Property or method \"" + key + "\" is not defined on the instance but " +
      'referenced during render. Make sure that this property is reactive, ' +
      'either in the data option, or for class-based components, by ' +
      'initializing the property. ' +
      'See: https://vuejs.org/v2/guide/reactivity.html#Declaring-Reactive-Properties.',
      target
    );
  };

  var warnReservedPrefix = function (target, key) {
    warn(
      "Property \"" + key + "\" must be accessed with \"$data." + key + "\" because " +
      'properties starting with "$" or "_" are not proxied in the Vue instance to ' +
      'prevent conflicts with Vue internals' +
      'See: https://vuejs.org/v2/api/#data',
      target
    );
  };

  var hasProxy =
    typeof Proxy !== 'undefined' && isNative(Proxy);

  if (hasProxy) {
    var isBuiltInModifier = makeMap('stop,prevent,self,ctrl,shift,alt,meta,exact');
    config$1.keyCodes = new Proxy(config$1.keyCodes, {
      set: function set (target, key, value) {
        if (isBuiltInModifier(key)) {
          warn(("Avoid overwriting built-in modifier in config.keyCodes: ." + key));
          return false
        } else {
          target[key] = value;
          return true
        }
      }
    });
  }

  var hasHandler = {
    has: function has (target, key) {
      var has = key in target;
      var isAllowed = allowedGlobals(key) ||
        (typeof key === 'string' && key.charAt(0) === '_' && !(key in target.$data));
      if (!has && !isAllowed) {
        if (key in target.$data) { warnReservedPrefix(target, key); }
        else { warnNonPresent(target, key); }
      }
      return has || !isAllowed
    }
  };

  var getHandler = {
    get: function get (target, key) {
      if (typeof key === 'string' && !(key in target)) {
        if (key in target.$data) { warnReservedPrefix(target, key); }
        else { warnNonPresent(target, key); }
      }
      return target[key]
    }
  };

  initProxy = function initProxy (vm) {
    if (hasProxy) {
      // determine which proxy handler to use
      var options = vm.$options;
      var handlers = options.render && options.render._withStripped
        ? getHandler
        : hasHandler;
      vm._renderProxy = new Proxy(vm, handlers);
    } else {
      vm._renderProxy = vm;
    }
  };
}

/*  */

var seenObjects = new _Set();

/**
 * Recursively traverse an object to evoke all converted
 * getters, so that every nested property inside the object
 * is collected as a "deep" dependency.
 */
function traverse (val) {
  _traverse(val, seenObjects);
  seenObjects.clear();
}

function _traverse (val, seen) {
  var i, keys;
  var isA = Array.isArray(val);
  if ((!isA && !isObject(val)) || Object.isFrozen(val) || val instanceof VNode) {
    return
  }
  if (val.__ob__) {
    var depId = val.__ob__.dep.id;
    if (seen.has(depId)) {
      return
    }
    seen.add(depId);
  }
  if (isA) {
    i = val.length;
    while (i--) { _traverse(val[i], seen); }
  } else {
    keys = Object.keys(val);
    i = keys.length;
    while (i--) { _traverse(val[keys[i]], seen); }
  }
}

var mark;
var measure;

{
  var perf = inBrowser && window.performance;
  /* istanbul ignore if */
  if (
    perf &&
    perf.mark &&
    perf.measure &&
    perf.clearMarks &&
    perf.clearMeasures
  ) {
    mark = function (tag) { return perf.mark(tag); };
    measure = function (name, startTag, endTag) {
      perf.measure(name, startTag, endTag);
      perf.clearMarks(startTag);
      perf.clearMarks(endTag);
      // perf.clearMeasures(name)
    };
  }
}

/*  */

var normalizeEvent = cached(function (name) {
  var passive = name.charAt(0) === '&';
  name = passive ? name.slice(1) : name;
  var once$$1 = name.charAt(0) === '~'; // Prefixed last, checked first
  name = once$$1 ? name.slice(1) : name;
  var capture = name.charAt(0) === '!';
  name = capture ? name.slice(1) : name;
  return {
    name: name,
    once: once$$1,
    capture: capture,
    passive: passive
  }
});

function createFnInvoker (fns, vm) {
  function invoker () {
    var arguments$1 = arguments;

    var fns = invoker.fns;
    if (Array.isArray(fns)) {
      var cloned = fns.slice();
      for (var i = 0; i < cloned.length; i++) {
        invokeWithErrorHandling(cloned[i], null, arguments$1, vm, "v-on handler");
      }
    } else {
      // return handler return value for single handlers
      return invokeWithErrorHandling(fns, null, arguments, vm, "v-on handler")
    }
  }
  invoker.fns = fns;
  return invoker
}

function updateListeners (
  on,
  oldOn,
  add,
  remove$$1,
  createOnceHandler,
  vm
) {
  var name, def$$1, cur, old, event;
  for (name in on) {
    def$$1 = cur = on[name];
    old = oldOn[name];
    event = normalizeEvent(name);
    if (isUndef(cur)) {
      warn(
        "Invalid handler for event \"" + (event.name) + "\": got " + String(cur),
        vm
      );
    } else if (isUndef(old)) {
      if (isUndef(cur.fns)) {
        cur = on[name] = createFnInvoker(cur, vm);
      }
      if (isTrue(event.once)) {
        cur = on[name] = createOnceHandler(event.name, cur, event.capture);
      }
      add(event.name, cur, event.capture, event.passive, event.params);
    } else if (cur !== old) {
      old.fns = cur;
      on[name] = old;
    }
  }
  for (name in oldOn) {
    if (isUndef(on[name])) {
      event = normalizeEvent(name);
      remove$$1(event.name, oldOn[name], event.capture);
    }
  }
}

/*  */

function mergeVNodeHook (def, hookKey, hook) {
  if (def instanceof VNode) {
    def = def.data.hook || (def.data.hook = {});
  }
  var invoker;
  var oldHook = def[hookKey];

  function wrappedHook () {
    hook.apply(this, arguments);
    // important: remove merged hook to ensure it's called only once
    // and prevent memory leak
    remove(invoker.fns, wrappedHook);
  }

  if (isUndef(oldHook)) {
    // no existing hook
    invoker = createFnInvoker([wrappedHook]);
  } else {
    /* istanbul ignore if */
    if (isDef(oldHook.fns) && isTrue(oldHook.merged)) {
      // already a merged invoker
      invoker = oldHook;
      invoker.fns.push(wrappedHook);
    } else {
      // existing plain hook
      invoker = createFnInvoker([oldHook, wrappedHook]);
    }
  }

  invoker.merged = true;
  def[hookKey] = invoker;
}

/*  */

function extractPropsFromVNodeData (
  data,
  Ctor,
  tag
) {
  // we are only extracting raw values here.
  // validation and default values are handled in the child
  // component itself.
  var propOptions = Ctor.options.props;
  if (isUndef(propOptions)) {
    return
  }
  var res = {};
  var attrs = data.attrs;
  var props = data.props;
  if (isDef(attrs) || isDef(props)) {
    for (var key in propOptions) {
      var altKey = hyphenate(key);
      {
        var keyInLowerCase = key.toLowerCase();
        if (
          key !== keyInLowerCase &&
          attrs && hasOwn(attrs, keyInLowerCase)
        ) {
          tip(
            "Prop \"" + keyInLowerCase + "\" is passed to component " +
            (formatComponentName(tag || Ctor)) + ", but the declared prop name is" +
            " \"" + key + "\". " +
            "Note that HTML attributes are case-insensitive and camelCased " +
            "props need to use their kebab-case equivalents when using in-DOM " +
            "templates. You should probably use \"" + altKey + "\" instead of \"" + key + "\"."
          );
        }
      }
      checkProp(res, props, key, altKey, true) ||
      checkProp(res, attrs, key, altKey, false);
    }
  }
  return res
}

function checkProp (
  res,
  hash,
  key,
  altKey,
  preserve
) {
  if (isDef(hash)) {
    if (hasOwn(hash, key)) {
      res[key] = hash[key];
      if (!preserve) {
        delete hash[key];
      }
      return true
    } else if (hasOwn(hash, altKey)) {
      res[key] = hash[altKey];
      if (!preserve) {
        delete hash[altKey];
      }
      return true
    }
  }
  return false
}

/*  */

// The template compiler attempts to minimize the need for normalization by
// statically analyzing the template at compile time.
//
// For plain HTML markup, normalization can be completely skipped because the
// generated render function is guaranteed to return Array<VNode>. There are
// two cases where extra normalization is needed:

// 1. When the children contains components - because a functional component
// may return an Array instead of a single root. In this case, just a simple
// normalization is needed - if any child is an Array, we flatten the whole
// thing with Array.prototype.concat. It is guaranteed to be only 1-level deep
// because functional components already normalize their own children.
function simpleNormalizeChildren (children) {
  for (var i = 0; i < children.length; i++) {
    if (Array.isArray(children[i])) {
      return Array.prototype.concat.apply([], children)
    }
  }
  return children
}

// 2. When the children contains constructs that always generated nested Arrays,
// e.g. <template>, <slot>, v-for, or when the children is provided by user
// with hand-written render functions / JSX. In such cases a full normalization
// is needed to cater to all possible types of children values.
function normalizeChildren (children) {
  return isPrimitive(children)
    ? [createTextVNode(children)]
    : Array.isArray(children)
      ? normalizeArrayChildren(children)
      : undefined
}

function isTextNode (node) {
  return isDef(node) && isDef(node.text) && isFalse(node.isComment)
}

function normalizeArrayChildren (children, nestedIndex) {
  var res = [];
  var i, c, lastIndex, last;
  for (i = 0; i < children.length; i++) {
    c = children[i];
    if (isUndef(c) || typeof c === 'boolean') { continue }
    lastIndex = res.length - 1;
    last = res[lastIndex];
    //  nested
    if (Array.isArray(c)) {
      if (c.length > 0) {
        c = normalizeArrayChildren(c, ((nestedIndex || '') + "_" + i));
        // merge adjacent text nodes
        if (isTextNode(c[0]) && isTextNode(last)) {
          res[lastIndex] = createTextVNode(last.text + (c[0]).text);
          c.shift();
        }
        res.push.apply(res, c);
      }
    } else if (isPrimitive(c)) {
      if (isTextNode(last)) {
        // merge adjacent text nodes
        // this is necessary for SSR hydration because text nodes are
        // essentially merged when rendered to HTML strings
        res[lastIndex] = createTextVNode(last.text + c);
      } else if (c !== '') {
        // convert primitive to vnode
        res.push(createTextVNode(c));
      }
    } else {
      if (isTextNode(c) && isTextNode(last)) {
        // merge adjacent text nodes
        res[lastIndex] = createTextVNode(last.text + c.text);
      } else {
        // default key for nested array children (likely generated by v-for)
        if (isTrue(children._isVList) &&
          isDef(c.tag) &&
          isUndef(c.key) &&
          isDef(nestedIndex)) {
          c.key = "__vlist" + nestedIndex + "_" + i + "__";
        }
        res.push(c);
      }
    }
  }
  return res
}

/*  */

function initProvide (vm) {
  var provide = vm.$options.provide;
  if (provide) {
    vm._provided = typeof provide === 'function'
      ? provide.call(vm)
      : provide;
  }
}

function initInjections (vm) {
  var result = resolveInject(vm.$options.inject, vm);
  if (result) {
    toggleObserving(false);
    Object.keys(result).forEach(function (key) {
      /* istanbul ignore else */
      {
        defineReactive$$1(vm, key, result[key], function () {
          warn(
            "Avoid mutating an injected value directly since the changes will be " +
            "overwritten whenever the provided component re-renders. " +
            "injection being mutated: \"" + key + "\"",
            vm
          );
        });
      }
    });
    toggleObserving(true);
  }
}

function resolveInject (inject, vm) {
  if (inject) {
    // inject is :any because flow is not smart enough to figure out cached
    var result = Object.create(null);
    var keys = hasSymbol
      ? Reflect.ownKeys(inject)
      : Object.keys(inject);

    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];
      // #6574 in case the inject object is observed...
      if (key === '__ob__') { continue }
      var provideKey = inject[key].from;
      var source = vm;
      while (source) {
        if (source._provided && hasOwn(source._provided, provideKey)) {
          result[key] = source._provided[provideKey];
          break
        }
        source = source.$parent;
      }
      if (!source) {
        if ('default' in inject[key]) {
          var provideDefault = inject[key].default;
          result[key] = typeof provideDefault === 'function'
            ? provideDefault.call(vm)
            : provideDefault;
        } else {
          warn(("Injection \"" + key + "\" not found"), vm);
        }
      }
    }
    return result
  }
}

/*  */



/**
 * Runtime helper for resolving raw children VNodes into a slot object.
 */
function resolveSlots (
  children,
  context
) {
  if (!children || !children.length) {
    return {}
  }
  var slots = {};
  for (var i = 0, l = children.length; i < l; i++) {
    var child = children[i];
    var data = child.data;
    // remove slot attribute if the node is resolved as a Vue slot node
    if (data && data.attrs && data.attrs.slot) {
      delete data.attrs.slot;
    }
    // named slots should only be respected if the vnode was rendered in the
    // same context.
    if ((child.context === context || child.fnContext === context) &&
      data && data.slot != null
    ) {
      var name = data.slot;
      var slot = (slots[name] || (slots[name] = []));
      if (child.tag === 'template') {
        slot.push.apply(slot, child.children || []);
      } else {
        slot.push(child);
      }
    } else {
      (slots.default || (slots.default = [])).push(child);
    }
  }
  // ignore slots that contains only whitespace
  for (var name$1 in slots) {
    if (slots[name$1].every(isWhitespace)) {
      delete slots[name$1];
    }
  }
  return slots
}

function isWhitespace (node) {
  return (node.isComment && !node.asyncFactory) || node.text === ' '
}

/*  */

function normalizeScopedSlots (
  slots,
  normalSlots,
  prevSlots
) {
  var res;
  var hasNormalSlots = Object.keys(normalSlots).length > 0;
  var isStable = slots ? !!slots.$stable : !hasNormalSlots;
  var key = slots && slots.$key;
  if (!slots) {
    res = {};
  } else if (slots._normalized) {
    // fast path 1: child component re-render only, parent did not change
    return slots._normalized
  } else if (
    isStable &&
    prevSlots &&
    prevSlots !== emptyObject &&
    key === prevSlots.$key &&
    !hasNormalSlots &&
    !prevSlots.$hasNormal
  ) {
    // fast path 2: stable scoped slots w/ no normal slots to proxy,
    // only need to normalize once
    return prevSlots
  } else {
    res = {};
    for (var key$1 in slots) {
      if (slots[key$1] && key$1[0] !== '$') {
        res[key$1] = normalizeScopedSlot(normalSlots, key$1, slots[key$1]);
      }
    }
  }
  // expose normal slots on scopedSlots
  for (var key$2 in normalSlots) {
    if (!(key$2 in res)) {
      res[key$2] = proxyNormalSlot(normalSlots, key$2);
    }
  }
  // avoriaz seems to mock a non-extensible $scopedSlots object
  // and when that is passed down this would cause an error
  if (slots && Object.isExtensible(slots)) {
    (slots)._normalized = res;
  }
  def(res, '$stable', isStable);
  def(res, '$key', key);
  def(res, '$hasNormal', hasNormalSlots);
  return res
}

function normalizeScopedSlot(normalSlots, key, fn) {
  var normalized = function () {
    var res = arguments.length ? fn.apply(null, arguments) : fn({});
    res = res && typeof res === 'object' && !Array.isArray(res)
      ? [res] // single vnode
      : normalizeChildren(res);
    return res && (
      res.length === 0 ||
      (res.length === 1 && res[0].isComment) // #9658
    ) ? undefined
      : res
  };
  // this is a slot using the new v-slot syntax without scope. although it is
  // compiled as a scoped slot, render fn users would expect it to be present
  // on this.$slots because the usage is semantically a normal slot.
  if (fn.proxy) {
    Object.defineProperty(normalSlots, key, {
      get: normalized,
      enumerable: true,
      configurable: true
    });
  }
  return normalized
}

function proxyNormalSlot(slots, key) {
  return function () { return slots[key]; }
}

/*  */

/**
 * Runtime helper for rendering v-for lists.
 */
function renderList (
  val,
  render
) {
  var ret, i, l, keys, key;
  if (Array.isArray(val) || typeof val === 'string') {
    ret = new Array(val.length);
    for (i = 0, l = val.length; i < l; i++) {
      ret[i] = render(val[i], i);
    }
  } else if (typeof val === 'number') {
    ret = new Array(val);
    for (i = 0; i < val; i++) {
      ret[i] = render(i + 1, i);
    }
  } else if (isObject(val)) {
    if (hasSymbol && val[Symbol.iterator]) {
      ret = [];
      var iterator = val[Symbol.iterator]();
      var result = iterator.next();
      while (!result.done) {
        ret.push(render(result.value, ret.length));
        result = iterator.next();
      }
    } else {
      keys = Object.keys(val);
      ret = new Array(keys.length);
      for (i = 0, l = keys.length; i < l; i++) {
        key = keys[i];
        ret[i] = render(val[key], key, i);
      }
    }
  }
  if (!isDef(ret)) {
    ret = [];
  }
  (ret)._isVList = true;
  return ret
}

/*  */

/**
 * Runtime helper for rendering <slot>
 */
function renderSlot (
  name,
  fallback,
  props,
  bindObject
) {
  var scopedSlotFn = this.$scopedSlots[name];
  var nodes;
  if (scopedSlotFn) { // scoped slot
    props = props || {};
    if (bindObject) {
      if (!isObject(bindObject)) {
        warn(
          'slot v-bind without argument expects an Object',
          this
        );
      }
      props = extend(extend({}, bindObject), props);
    }
    nodes = scopedSlotFn(props) || fallback;
  } else {
    nodes = this.$slots[name] || fallback;
  }

  var target = props && props.slot;
  if (target) {
    return this.$createElement('template', { slot: target }, nodes)
  } else {
    return nodes
  }
}

/*  */

/**
 * Runtime helper for resolving filters
 */
function resolveFilter (id) {
  return resolveAsset(this.$options, 'filters', id, true) || identity
}

/*  */

function isKeyNotMatch (expect, actual) {
  if (Array.isArray(expect)) {
    return expect.indexOf(actual) === -1
  } else {
    return expect !== actual
  }
}

/**
 * Runtime helper for checking keyCodes from config.
 * exposed as Vue.prototype._k
 * passing in eventKeyName as last argument separately for backwards compat
 */
function checkKeyCodes (
  eventKeyCode,
  key,
  builtInKeyCode,
  eventKeyName,
  builtInKeyName
) {
  var mappedKeyCode = config$1.keyCodes[key] || builtInKeyCode;
  if (builtInKeyName && eventKeyName && !config$1.keyCodes[key]) {
    return isKeyNotMatch(builtInKeyName, eventKeyName)
  } else if (mappedKeyCode) {
    return isKeyNotMatch(mappedKeyCode, eventKeyCode)
  } else if (eventKeyName) {
    return hyphenate(eventKeyName) !== key
  }
}

/*  */

/**
 * Runtime helper for merging v-bind="object" into a VNode's data.
 */
function bindObjectProps (
  data,
  tag,
  value,
  asProp,
  isSync
) {
  if (value) {
    if (!isObject(value)) {
      warn(
        'v-bind without argument expects an Object or Array value',
        this
      );
    } else {
      if (Array.isArray(value)) {
        value = toObject(value);
      }
      var hash;
      var loop = function ( key ) {
        if (
          key === 'class' ||
          key === 'style' ||
          isReservedAttribute(key)
        ) {
          hash = data;
        } else {
          var type = data.attrs && data.attrs.type;
          hash = asProp || config$1.mustUseProp(tag, type, key)
            ? data.domProps || (data.domProps = {})
            : data.attrs || (data.attrs = {});
        }
        var camelizedKey = camelize(key);
        var hyphenatedKey = hyphenate(key);
        if (!(camelizedKey in hash) && !(hyphenatedKey in hash)) {
          hash[key] = value[key];

          if (isSync) {
            var on = data.on || (data.on = {});
            on[("update:" + key)] = function ($event) {
              value[key] = $event;
            };
          }
        }
      };

      for (var key in value) loop( key );
    }
  }
  return data
}

/*  */

/**
 * Runtime helper for rendering static trees.
 */
function renderStatic (
  index,
  isInFor
) {
  var cached = this._staticTrees || (this._staticTrees = []);
  var tree = cached[index];
  // if has already-rendered static tree and not inside v-for,
  // we can reuse the same tree.
  if (tree && !isInFor) {
    return tree
  }
  // otherwise, render a fresh tree.
  tree = cached[index] = this.$options.staticRenderFns[index].call(
    this._renderProxy,
    null,
    this // for render fns generated for functional component templates
  );
  markStatic(tree, ("__static__" + index), false);
  return tree
}

/**
 * Runtime helper for v-once.
 * Effectively it means marking the node as static with a unique key.
 */
function markOnce (
  tree,
  index,
  key
) {
  markStatic(tree, ("__once__" + index + (key ? ("_" + key) : "")), true);
  return tree
}

function markStatic (
  tree,
  key,
  isOnce
) {
  if (Array.isArray(tree)) {
    for (var i = 0; i < tree.length; i++) {
      if (tree[i] && typeof tree[i] !== 'string') {
        markStaticNode(tree[i], (key + "_" + i), isOnce);
      }
    }
  } else {
    markStaticNode(tree, key, isOnce);
  }
}

function markStaticNode (node, key, isOnce) {
  node.isStatic = true;
  node.key = key;
  node.isOnce = isOnce;
}

/*  */

function bindObjectListeners (data, value) {
  if (value) {
    if (!isPlainObject(value)) {
      warn(
        'v-on without argument expects an Object value',
        this
      );
    } else {
      var on = data.on = data.on ? extend({}, data.on) : {};
      for (var key in value) {
        var existing = on[key];
        var ours = value[key];
        on[key] = existing ? [].concat(existing, ours) : ours;
      }
    }
  }
  return data
}

/*  */

function resolveScopedSlots (
  fns, // see flow/vnode
  res,
  // the following are added in 2.6
  hasDynamicKeys,
  contentHashKey
) {
  res = res || { $stable: !hasDynamicKeys };
  for (var i = 0; i < fns.length; i++) {
    var slot = fns[i];
    if (Array.isArray(slot)) {
      resolveScopedSlots(slot, res, hasDynamicKeys);
    } else if (slot) {
      // marker for reverse proxying v-slot without scope on this.$slots
      if (slot.proxy) {
        slot.fn.proxy = true;
      }
      res[slot.key] = slot.fn;
    }
  }
  if (contentHashKey) {
    (res).$key = contentHashKey;
  }
  return res
}

/*  */

function bindDynamicKeys (baseObj, values) {
  for (var i = 0; i < values.length; i += 2) {
    var key = values[i];
    if (typeof key === 'string' && key) {
      baseObj[values[i]] = values[i + 1];
    } else if (key !== '' && key !== null) {
      // null is a speical value for explicitly removing a binding
      warn(
        ("Invalid value for dynamic directive argument (expected string or null): " + key),
        this
      );
    }
  }
  return baseObj
}

// helper to dynamically append modifier runtime markers to event names.
// ensure only append when value is already string, otherwise it will be cast
// to string and cause the type check to miss.
function prependModifier (value, symbol) {
  return typeof value === 'string' ? symbol + value : value
}

/*  */

function installRenderHelpers (target) {
  target._o = markOnce;
  target._n = toNumber;
  target._s = toString;
  target._l = renderList;
  target._t = renderSlot;
  target._q = looseEqual;
  target._i = looseIndexOf;
  target._m = renderStatic;
  target._f = resolveFilter;
  target._k = checkKeyCodes;
  target._b = bindObjectProps;
  target._v = createTextVNode;
  target._e = createEmptyVNode;
  target._u = resolveScopedSlots;
  target._g = bindObjectListeners;
  target._d = bindDynamicKeys;
  target._p = prependModifier;
}

/*  */

function FunctionalRenderContext (
  data,
  props,
  children,
  parent,
  Ctor
) {
  var this$1 = this;

  var options = Ctor.options;
  // ensure the createElement function in functional components
  // gets a unique context - this is necessary for correct named slot check
  var contextVm;
  if (hasOwn(parent, '_uid')) {
    contextVm = Object.create(parent);
    // $flow-disable-line
    contextVm._original = parent;
  } else {
    // the context vm passed in is a functional context as well.
    // in this case we want to make sure we are able to get a hold to the
    // real context instance.
    contextVm = parent;
    // $flow-disable-line
    parent = parent._original;
  }
  var isCompiled = isTrue(options._compiled);
  var needNormalization = !isCompiled;

  this.data = data;
  this.props = props;
  this.children = children;
  this.parent = parent;
  this.listeners = data.on || emptyObject;
  this.injections = resolveInject(options.inject, parent);
  this.slots = function () {
    if (!this$1.$slots) {
      normalizeScopedSlots(
        data.scopedSlots,
        this$1.$slots = resolveSlots(children, parent)
      );
    }
    return this$1.$slots
  };

  Object.defineProperty(this, 'scopedSlots', ({
    enumerable: true,
    get: function get () {
      return normalizeScopedSlots(data.scopedSlots, this.slots())
    }
  }));

  // support for compiled functional template
  if (isCompiled) {
    // exposing $options for renderStatic()
    this.$options = options;
    // pre-resolve slots for renderSlot()
    this.$slots = this.slots();
    this.$scopedSlots = normalizeScopedSlots(data.scopedSlots, this.$slots);
  }

  if (options._scopeId) {
    this._c = function (a, b, c, d) {
      var vnode = createElement(contextVm, a, b, c, d, needNormalization);
      if (vnode && !Array.isArray(vnode)) {
        vnode.fnScopeId = options._scopeId;
        vnode.fnContext = parent;
      }
      return vnode
    };
  } else {
    this._c = function (a, b, c, d) { return createElement(contextVm, a, b, c, d, needNormalization); };
  }
}

installRenderHelpers(FunctionalRenderContext.prototype);

function createFunctionalComponent (
  Ctor,
  propsData,
  data,
  contextVm,
  children
) {
  var options = Ctor.options;
  var props = {};
  var propOptions = options.props;
  if (isDef(propOptions)) {
    for (var key in propOptions) {
      props[key] = validateProp(key, propOptions, propsData || emptyObject);
    }
  } else {
    if (isDef(data.attrs)) { mergeProps(props, data.attrs); }
    if (isDef(data.props)) { mergeProps(props, data.props); }
  }

  var renderContext = new FunctionalRenderContext(
    data,
    props,
    children,
    contextVm,
    Ctor
  );

  var vnode = options.render.call(null, renderContext._c, renderContext);

  if (vnode instanceof VNode) {
    return cloneAndMarkFunctionalResult(vnode, data, renderContext.parent, options, renderContext)
  } else if (Array.isArray(vnode)) {
    var vnodes = normalizeChildren(vnode) || [];
    var res = new Array(vnodes.length);
    for (var i = 0; i < vnodes.length; i++) {
      res[i] = cloneAndMarkFunctionalResult(vnodes[i], data, renderContext.parent, options, renderContext);
    }
    return res
  }
}

function cloneAndMarkFunctionalResult (vnode, data, contextVm, options, renderContext) {
  // #7817 clone node before setting fnContext, otherwise if the node is reused
  // (e.g. it was from a cached normal slot) the fnContext causes named slots
  // that should not be matched to match.
  var clone = cloneVNode(vnode);
  clone.fnContext = contextVm;
  clone.fnOptions = options;
  {
    (clone.devtoolsMeta = clone.devtoolsMeta || {}).renderContext = renderContext;
  }
  if (data.slot) {
    (clone.data || (clone.data = {})).slot = data.slot;
  }
  return clone
}

function mergeProps (to, from) {
  for (var key in from) {
    to[camelize(key)] = from[key];
  }
}

/*  */

/*  */

/*  */

/*  */

// inline hooks to be invoked on component VNodes during patch
var componentVNodeHooks = {
  init: function init (vnode, hydrating) {
    if (
      vnode.componentInstance &&
      !vnode.componentInstance._isDestroyed &&
      vnode.data.keepAlive
    ) {
      // kept-alive components, treat as a patch
      var mountedNode = vnode; // work around flow
      componentVNodeHooks.prepatch(mountedNode, mountedNode);
    } else {
      var child = vnode.componentInstance = createComponentInstanceForVnode(
        vnode,
        activeInstance
      );
      child.$mount(hydrating ? vnode.elm : undefined, hydrating);
    }
  },

  prepatch: function prepatch (oldVnode, vnode) {
    var options = vnode.componentOptions;
    var child = vnode.componentInstance = oldVnode.componentInstance;
    updateChildComponent(
      child,
      options.propsData, // updated props
      options.listeners, // updated listeners
      vnode, // new parent vnode
      options.children // new children
    );
  },

  insert: function insert (vnode) {
    var context = vnode.context;
    var componentInstance = vnode.componentInstance;
    if (!componentInstance._isMounted) {
      componentInstance._isMounted = true;
      callHook(componentInstance, 'mounted');
    }
    if (vnode.data.keepAlive) {
      if (context._isMounted) {
        // vue-router#1212
        // During updates, a kept-alive component's child components may
        // change, so directly walking the tree here may call activated hooks
        // on incorrect children. Instead we push them into a queue which will
        // be processed after the whole patch process ended.
        queueActivatedComponent(componentInstance);
      } else {
        activateChildComponent(componentInstance, true /* direct */);
      }
    }
  },

  destroy: function destroy (vnode) {
    var componentInstance = vnode.componentInstance;
    if (!componentInstance._isDestroyed) {
      if (!vnode.data.keepAlive) {
        componentInstance.$destroy();
      } else {
        deactivateChildComponent(componentInstance, true /* direct */);
      }
    }
  }
};

var hooksToMerge = Object.keys(componentVNodeHooks);

function createComponent (
  Ctor,
  data,
  context,
  children,
  tag
) {
  if (isUndef(Ctor)) {
    return
  }

  var baseCtor = context.$options._base;

  // plain options object: turn it into a constructor
  if (isObject(Ctor)) {
    Ctor = baseCtor.extend(Ctor);
  }

  // if at this stage it's not a constructor or an async component factory,
  // reject.
  if (typeof Ctor !== 'function') {
    {
      warn(("Invalid Component definition: " + (String(Ctor))), context);
    }
    return
  }

  // async component
  var asyncFactory;
  if (isUndef(Ctor.cid)) {
    asyncFactory = Ctor;
    Ctor = resolveAsyncComponent(asyncFactory, baseCtor);
    if (Ctor === undefined) {
      // return a placeholder node for async component, which is rendered
      // as a comment node but preserves all the raw information for the node.
      // the information will be used for async server-rendering and hydration.
      return createAsyncPlaceholder(
        asyncFactory,
        data,
        context,
        children,
        tag
      )
    }
  }

  data = data || {};

  // resolve constructor options in case global mixins are applied after
  // component constructor creation
  resolveConstructorOptions(Ctor);

  // transform component v-model data into props & events
  if (isDef(data.model)) {
    transformModel(Ctor.options, data);
  }

  // extract props
  var propsData = extractPropsFromVNodeData(data, Ctor, tag);

  // functional component
  if (isTrue(Ctor.options.functional)) {
    return createFunctionalComponent(Ctor, propsData, data, context, children)
  }

  // extract listeners, since these needs to be treated as
  // child component listeners instead of DOM listeners
  var listeners = data.on;
  // replace with listeners with .native modifier
  // so it gets processed during parent component patch.
  data.on = data.nativeOn;

  if (isTrue(Ctor.options.abstract)) {
    // abstract components do not keep anything
    // other than props & listeners & slot

    // work around flow
    var slot = data.slot;
    data = {};
    if (slot) {
      data.slot = slot;
    }
  }

  // install component management hooks onto the placeholder node
  installComponentHooks(data);

  // return a placeholder vnode
  var name = Ctor.options.name || tag;
  var vnode = new VNode(
    ("vue-component-" + (Ctor.cid) + (name ? ("-" + name) : '')),
    data, undefined, undefined, undefined, context,
    { Ctor: Ctor, propsData: propsData, listeners: listeners, tag: tag, children: children },
    asyncFactory
  );

  return vnode
}

function createComponentInstanceForVnode (
  vnode, // we know it's MountedComponentVNode but flow doesn't
  parent // activeInstance in lifecycle state
) {
  var options = {
    _isComponent: true,
    _parentVnode: vnode,
    parent: parent
  };
  // check inline-template render functions
  var inlineTemplate = vnode.data.inlineTemplate;
  if (isDef(inlineTemplate)) {
    options.render = inlineTemplate.render;
    options.staticRenderFns = inlineTemplate.staticRenderFns;
  }
  return new vnode.componentOptions.Ctor(options)
}

function installComponentHooks (data) {
  var hooks = data.hook || (data.hook = {});
  for (var i = 0; i < hooksToMerge.length; i++) {
    var key = hooksToMerge[i];
    var existing = hooks[key];
    var toMerge = componentVNodeHooks[key];
    if (existing !== toMerge && !(existing && existing._merged)) {
      hooks[key] = existing ? mergeHook$1(toMerge, existing) : toMerge;
    }
  }
}

function mergeHook$1 (f1, f2) {
  var merged = function (a, b) {
    // flow complains about extra args which is why we use any
    f1(a, b);
    f2(a, b);
  };
  merged._merged = true;
  return merged
}

// transform component v-model info (value and callback) into
// prop and event handler respectively.
function transformModel (options, data) {
  var prop = (options.model && options.model.prop) || 'value';
  var event = (options.model && options.model.event) || 'input'
  ;(data.attrs || (data.attrs = {}))[prop] = data.model.value;
  var on = data.on || (data.on = {});
  var existing = on[event];
  var callback = data.model.callback;
  if (isDef(existing)) {
    if (
      Array.isArray(existing)
        ? existing.indexOf(callback) === -1
        : existing !== callback
    ) {
      on[event] = [callback].concat(existing);
    }
  } else {
    on[event] = callback;
  }
}

/*  */

var SIMPLE_NORMALIZE = 1;
var ALWAYS_NORMALIZE = 2;

// wrapper function for providing a more flexible interface
// without getting yelled at by flow
function createElement (
  context,
  tag,
  data,
  children,
  normalizationType,
  alwaysNormalize
) {
  if (Array.isArray(data) || isPrimitive(data)) {
    normalizationType = children;
    children = data;
    data = undefined;
  }
  if (isTrue(alwaysNormalize)) {
    normalizationType = ALWAYS_NORMALIZE;
  }
  return _createElement(context, tag, data, children, normalizationType)
}

function _createElement (
  context,
  tag,
  data,
  children,
  normalizationType
) {
  if (isDef(data) && isDef((data).__ob__)) {
    warn(
      "Avoid using observed data object as vnode data: " + (JSON.stringify(data)) + "\n" +
      'Always create fresh vnode data objects in each render!',
      context
    );
    return createEmptyVNode()
  }
  // object syntax in v-bind
  if (isDef(data) && isDef(data.is)) {
    tag = data.is;
  }
  if (!tag) {
    // in case of component :is set to falsy value
    return createEmptyVNode()
  }
  // warn against non-primitive key
  if (isDef(data) && isDef(data.key) && !isPrimitive(data.key)
  ) {
    {
      warn(
        'Avoid using non-primitive value as key, ' +
        'use string/number value instead.',
        context
      );
    }
  }
  // support single function children as default scoped slot
  if (Array.isArray(children) &&
    typeof children[0] === 'function'
  ) {
    data = data || {};
    data.scopedSlots = { default: children[0] };
    children.length = 0;
  }
  if (normalizationType === ALWAYS_NORMALIZE) {
    children = normalizeChildren(children);
  } else if (normalizationType === SIMPLE_NORMALIZE) {
    children = simpleNormalizeChildren(children);
  }
  var vnode, ns;
  if (typeof tag === 'string') {
    var Ctor;
    ns = (context.$vnode && context.$vnode.ns) || config$1.getTagNamespace(tag);
    if (config$1.isReservedTag(tag)) {
      // platform built-in elements
      vnode = new VNode(
        config$1.parsePlatformTagName(tag), data, children,
        undefined, undefined, context
      );
    } else if ((!data || !data.pre) && isDef(Ctor = resolveAsset(context.$options, 'components', tag))) {
      // component
      vnode = createComponent(Ctor, data, context, children, tag);
    } else {
      // unknown or unlisted namespaced elements
      // check at runtime because it may get assigned a namespace when its
      // parent normalizes children
      vnode = new VNode(
        tag, data, children,
        undefined, undefined, context
      );
    }
  } else {
    // direct component options / constructor
    vnode = createComponent(tag, data, context, children);
  }
  if (Array.isArray(vnode)) {
    return vnode
  } else if (isDef(vnode)) {
    if (isDef(ns)) { applyNS(vnode, ns); }
    if (isDef(data)) { registerDeepBindings(data); }
    return vnode
  } else {
    return createEmptyVNode()
  }
}

function applyNS (vnode, ns, force) {
  vnode.ns = ns;
  if (vnode.tag === 'foreignObject') {
    // use default namespace inside foreignObject
    ns = undefined;
    force = true;
  }
  if (isDef(vnode.children)) {
    for (var i = 0, l = vnode.children.length; i < l; i++) {
      var child = vnode.children[i];
      if (isDef(child.tag) && (
        isUndef(child.ns) || (isTrue(force) && child.tag !== 'svg'))) {
        applyNS(child, ns, force);
      }
    }
  }
}

// ref #5318
// necessary to ensure parent re-render when deep bindings like :style and
// :class are used on slot nodes
function registerDeepBindings (data) {
  if (isObject(data.style)) {
    traverse(data.style);
  }
  if (isObject(data.class)) {
    traverse(data.class);
  }
}

/*  */

function initRender (vm) {
  vm._vnode = null; // the root of the child tree
  vm._staticTrees = null; // v-once cached trees
  var options = vm.$options;
  var parentVnode = vm.$vnode = options._parentVnode; // the placeholder node in parent tree
  var renderContext = parentVnode && parentVnode.context;
  vm.$slots = resolveSlots(options._renderChildren, renderContext);
  vm.$scopedSlots = emptyObject;
  // bind the createElement fn to this instance
  // so that we get proper render context inside it.
  // args order: tag, data, children, normalizationType, alwaysNormalize
  // internal version is used by render functions compiled from templates
  vm._c = function (a, b, c, d) { return createElement(vm, a, b, c, d, false); };
  // normalization is always applied for the public version, used in
  // user-written render functions.
  vm.$createElement = function (a, b, c, d) { return createElement(vm, a, b, c, d, true); };

  // $attrs & $listeners are exposed for easier HOC creation.
  // they need to be reactive so that HOCs using them are always updated
  var parentData = parentVnode && parentVnode.data;

  /* istanbul ignore else */
  {
    defineReactive$$1(vm, '$attrs', parentData && parentData.attrs || emptyObject, function () {
      !isUpdatingChildComponent && warn("$attrs is readonly.", vm);
    }, true);
    defineReactive$$1(vm, '$listeners', options._parentListeners || emptyObject, function () {
      !isUpdatingChildComponent && warn("$listeners is readonly.", vm);
    }, true);
  }
}

var currentRenderingInstance = null;

function renderMixin (Vue) {
  // install runtime convenience helpers
  installRenderHelpers(Vue.prototype);

  Vue.prototype.$nextTick = function (fn) {
    return nextTick$1(fn, this)
  };

  Vue.prototype._render = function () {
    var vm = this;
    var ref = vm.$options;
    var render = ref.render;
    var _parentVnode = ref._parentVnode;

    if (_parentVnode) {
      vm.$scopedSlots = normalizeScopedSlots(
        _parentVnode.data.scopedSlots,
        vm.$slots,
        vm.$scopedSlots
      );
    }

    // set parent vnode. this allows render functions to have access
    // to the data on the placeholder node.
    vm.$vnode = _parentVnode;
    // render self
    var vnode;
    try {
      // There's no need to maintain a stack becaues all render fns are called
      // separately from one another. Nested component's render fns are called
      // when parent component is patched.
      currentRenderingInstance = vm;
      vnode = render.call(vm._renderProxy, vm.$createElement);
    } catch (e) {
      handleError(e, vm, "render");
      // return error render result,
      // or previous vnode to prevent render error causing blank component
      /* istanbul ignore else */
      if (vm.$options.renderError) {
        try {
          vnode = vm.$options.renderError.call(vm._renderProxy, vm.$createElement, e);
        } catch (e) {
          handleError(e, vm, "renderError");
          vnode = vm._vnode;
        }
      } else {
        vnode = vm._vnode;
      }
    } finally {
      currentRenderingInstance = null;
    }
    // if the returned array contains only a single node, allow it
    if (Array.isArray(vnode) && vnode.length === 1) {
      vnode = vnode[0];
    }
    // return empty vnode in case the render function errored out
    if (!(vnode instanceof VNode)) {
      if (Array.isArray(vnode)) {
        warn(
          'Multiple root nodes returned from render function. Render function ' +
          'should return a single root node.',
          vm
        );
      }
      vnode = createEmptyVNode();
    }
    // set parent
    vnode.parent = _parentVnode;
    return vnode
  };
}

/*  */

function ensureCtor (comp, base) {
  if (
    comp.__esModule ||
    (hasSymbol && comp[Symbol.toStringTag] === 'Module')
  ) {
    comp = comp.default;
  }
  return isObject(comp)
    ? base.extend(comp)
    : comp
}

function createAsyncPlaceholder (
  factory,
  data,
  context,
  children,
  tag
) {
  var node = createEmptyVNode();
  node.asyncFactory = factory;
  node.asyncMeta = { data: data, context: context, children: children, tag: tag };
  return node
}

function resolveAsyncComponent (
  factory,
  baseCtor
) {
  if (isTrue(factory.error) && isDef(factory.errorComp)) {
    return factory.errorComp
  }

  if (isDef(factory.resolved)) {
    return factory.resolved
  }

  var owner = currentRenderingInstance;
  if (owner && isDef(factory.owners) && factory.owners.indexOf(owner) === -1) {
    // already pending
    factory.owners.push(owner);
  }

  if (isTrue(factory.loading) && isDef(factory.loadingComp)) {
    return factory.loadingComp
  }

  if (owner && !isDef(factory.owners)) {
    var owners = factory.owners = [owner];
    var sync = true;
    var timerLoading = null;
    var timerTimeout = null

    ;(owner).$on('hook:destroyed', function () { return remove(owners, owner); });

    var forceRender = function (renderCompleted) {
      for (var i = 0, l = owners.length; i < l; i++) {
        (owners[i]).$forceUpdate();
      }

      if (renderCompleted) {
        owners.length = 0;
        if (timerLoading !== null) {
          clearTimeout(timerLoading);
          timerLoading = null;
        }
        if (timerTimeout !== null) {
          clearTimeout(timerTimeout);
          timerTimeout = null;
        }
      }
    };

    var resolve = once$1(function (res) {
      // cache resolved
      factory.resolved = ensureCtor(res, baseCtor);
      // invoke callbacks only if this is not a synchronous resolve
      // (async resolves are shimmed as synchronous during SSR)
      if (!sync) {
        forceRender(true);
      } else {
        owners.length = 0;
      }
    });

    var reject = once$1(function (reason) {
      warn(
        "Failed to resolve async component: " + (String(factory)) +
        (reason ? ("\nReason: " + reason) : '')
      );
      if (isDef(factory.errorComp)) {
        factory.error = true;
        forceRender(true);
      }
    });

    var res = factory(resolve, reject);

    if (isObject(res)) {
      if (isPromise(res)) {
        // () => Promise
        if (isUndef(factory.resolved)) {
          res.then(resolve, reject);
        }
      } else if (isPromise(res.component)) {
        res.component.then(resolve, reject);

        if (isDef(res.error)) {
          factory.errorComp = ensureCtor(res.error, baseCtor);
        }

        if (isDef(res.loading)) {
          factory.loadingComp = ensureCtor(res.loading, baseCtor);
          if (res.delay === 0) {
            factory.loading = true;
          } else {
            timerLoading = setTimeout(function () {
              timerLoading = null;
              if (isUndef(factory.resolved) && isUndef(factory.error)) {
                factory.loading = true;
                forceRender(false);
              }
            }, res.delay || 200);
          }
        }

        if (isDef(res.timeout)) {
          timerTimeout = setTimeout(function () {
            timerTimeout = null;
            if (isUndef(factory.resolved)) {
              reject(
                "timeout (" + (res.timeout) + "ms)"
              );
            }
          }, res.timeout);
        }
      }
    }

    sync = false;
    // return in case resolved synchronously
    return factory.loading
      ? factory.loadingComp
      : factory.resolved
  }
}

/*  */

function isAsyncPlaceholder (node) {
  return node.isComment && node.asyncFactory
}

/*  */

function getFirstComponentChild (children) {
  if (Array.isArray(children)) {
    for (var i = 0; i < children.length; i++) {
      var c = children[i];
      if (isDef(c) && (isDef(c.componentOptions) || isAsyncPlaceholder(c))) {
        return c
      }
    }
  }
}

/*  */

/*  */

function initEvents (vm) {
  vm._events = Object.create(null);
  vm._hasHookEvent = false;
  // init parent attached events
  var listeners = vm.$options._parentListeners;
  if (listeners) {
    updateComponentListeners(vm, listeners);
  }
}

var target;

function add (event, fn) {
  target.$on(event, fn);
}

function remove$1 (event, fn) {
  target.$off(event, fn);
}

function createOnceHandler (event, fn) {
  var _target = target;
  return function onceHandler () {
    var res = fn.apply(null, arguments);
    if (res !== null) {
      _target.$off(event, onceHandler);
    }
  }
}

function updateComponentListeners (
  vm,
  listeners,
  oldListeners
) {
  target = vm;
  updateListeners(listeners, oldListeners || {}, add, remove$1, createOnceHandler, vm);
  target = undefined;
}

function eventsMixin (Vue) {
  var hookRE = /^hook:/;
  Vue.prototype.$on = function (event, fn) {
    var vm = this;
    if (Array.isArray(event)) {
      for (var i = 0, l = event.length; i < l; i++) {
        vm.$on(event[i], fn);
      }
    } else {
      (vm._events[event] || (vm._events[event] = [])).push(fn);
      // optimize hook:event cost by using a boolean flag marked at registration
      // instead of a hash lookup
      if (hookRE.test(event)) {
        vm._hasHookEvent = true;
      }
    }
    return vm
  };

  Vue.prototype.$once = function (event, fn) {
    var vm = this;
    function on () {
      vm.$off(event, on);
      fn.apply(vm, arguments);
    }
    on.fn = fn;
    vm.$on(event, on);
    return vm
  };

  Vue.prototype.$off = function (event, fn) {
    var vm = this;
    // all
    if (!arguments.length) {
      vm._events = Object.create(null);
      return vm
    }
    // array of events
    if (Array.isArray(event)) {
      for (var i$1 = 0, l = event.length; i$1 < l; i$1++) {
        vm.$off(event[i$1], fn);
      }
      return vm
    }
    // specific event
    var cbs = vm._events[event];
    if (!cbs) {
      return vm
    }
    if (!fn) {
      vm._events[event] = null;
      return vm
    }
    // specific handler
    var cb;
    var i = cbs.length;
    while (i--) {
      cb = cbs[i];
      if (cb === fn || cb.fn === fn) {
        cbs.splice(i, 1);
        break
      }
    }
    return vm
  };

  Vue.prototype.$emit = function (event) {
    var vm = this;
    {
      var lowerCaseEvent = event.toLowerCase();
      if (lowerCaseEvent !== event && vm._events[lowerCaseEvent]) {
        tip(
          "Event \"" + lowerCaseEvent + "\" is emitted in component " +
          (formatComponentName(vm)) + " but the handler is registered for \"" + event + "\". " +
          "Note that HTML attributes are case-insensitive and you cannot use " +
          "v-on to listen to camelCase events when using in-DOM templates. " +
          "You should probably use \"" + (hyphenate(event)) + "\" instead of \"" + event + "\"."
        );
      }
    }
    var cbs = vm._events[event];
    if (cbs) {
      cbs = cbs.length > 1 ? toArray(cbs) : cbs;
      var args = toArray(arguments, 1);
      var info = "event handler for \"" + event + "\"";
      for (var i = 0, l = cbs.length; i < l; i++) {
        invokeWithErrorHandling(cbs[i], vm, args, vm, info);
      }
    }
    return vm
  };
}

/*  */

var activeInstance = null;
var isUpdatingChildComponent = false;

function setActiveInstance(vm) {
  var prevActiveInstance = activeInstance;
  activeInstance = vm;
  return function () {
    activeInstance = prevActiveInstance;
  }
}

function initLifecycle (vm) {
  var options = vm.$options;

  // locate first non-abstract parent
  var parent = options.parent;
  if (parent && !options.abstract) {
    while (parent.$options.abstract && parent.$parent) {
      parent = parent.$parent;
    }
    parent.$children.push(vm);
  }

  vm.$parent = parent;
  vm.$root = parent ? parent.$root : vm;

  vm.$children = [];
  vm.$refs = {};

  vm._watcher = null;
  vm._inactive = null;
  vm._directInactive = false;
  vm._isMounted = false;
  vm._isDestroyed = false;
  vm._isBeingDestroyed = false;
}

function lifecycleMixin (Vue) {
  Vue.prototype._update = function (vnode, hydrating) {
    var vm = this;
    var prevEl = vm.$el;
    var prevVnode = vm._vnode;
    var restoreActiveInstance = setActiveInstance(vm);
    vm._vnode = vnode;
    // Vue.prototype.__patch__ is injected in entry points
    // based on the rendering backend used.
    if (!prevVnode) {
      // initial render
      vm.$el = vm.__patch__(vm.$el, vnode, hydrating, false /* removeOnly */);
    } else {
      // updates
      vm.$el = vm.__patch__(prevVnode, vnode);
    }
    restoreActiveInstance();
    // update __vue__ reference
    if (prevEl) {
      prevEl.__vue__ = null;
    }
    if (vm.$el) {
      vm.$el.__vue__ = vm;
    }
    // if parent is an HOC, update its $el as well
    if (vm.$vnode && vm.$parent && vm.$vnode === vm.$parent._vnode) {
      vm.$parent.$el = vm.$el;
    }
    // updated hook is called by the scheduler to ensure that children are
    // updated in a parent's updated hook.
  };

  Vue.prototype.$forceUpdate = function () {
    var vm = this;
    if (vm._watcher) {
      vm._watcher.update();
    }
  };

  Vue.prototype.$destroy = function () {
    var vm = this;
    if (vm._isBeingDestroyed) {
      return
    }
    callHook(vm, 'beforeDestroy');
    vm._isBeingDestroyed = true;
    // remove self from parent
    var parent = vm.$parent;
    if (parent && !parent._isBeingDestroyed && !vm.$options.abstract) {
      remove(parent.$children, vm);
    }
    // teardown watchers
    if (vm._watcher) {
      vm._watcher.teardown();
    }
    var i = vm._watchers.length;
    while (i--) {
      vm._watchers[i].teardown();
    }
    // remove reference from data ob
    // frozen object may not have observer.
    if (vm._data.__ob__) {
      vm._data.__ob__.vmCount--;
    }
    // call the last hook...
    vm._isDestroyed = true;
    // invoke destroy hooks on current rendered tree
    vm.__patch__(vm._vnode, null);
    // fire destroyed hook
    callHook(vm, 'destroyed');
    // turn off all instance listeners.
    vm.$off();
    // remove __vue__ reference
    if (vm.$el) {
      vm.$el.__vue__ = null;
    }
    // release circular reference (#6759)
    if (vm.$vnode) {
      vm.$vnode.parent = null;
    }
  };
}

function mountComponent (
  vm,
  el,
  hydrating
) {
  vm.$el = el;
  if (!vm.$options.render) {
    vm.$options.render = createEmptyVNode;
    {
      /* istanbul ignore if */
      if ((vm.$options.template && vm.$options.template.charAt(0) !== '#') ||
        vm.$options.el || el) {
        warn(
          'You are using the runtime-only build of Vue where the template ' +
          'compiler is not available. Either pre-compile the templates into ' +
          'render functions, or use the compiler-included build.',
          vm
        );
      } else {
        warn(
          'Failed to mount component: template or render function not defined.',
          vm
        );
      }
    }
  }
  callHook(vm, 'beforeMount');

  var updateComponent;
  /* istanbul ignore if */
  if (config$1.performance && mark) {
    updateComponent = function () {
      var name = vm._name;
      var id = vm._uid;
      var startTag = "vue-perf-start:" + id;
      var endTag = "vue-perf-end:" + id;

      mark(startTag);
      var vnode = vm._render();
      mark(endTag);
      measure(("vue " + name + " render"), startTag, endTag);

      mark(startTag);
      vm._update(vnode, hydrating);
      mark(endTag);
      measure(("vue " + name + " patch"), startTag, endTag);
    };
  } else {
    updateComponent = function () {
      vm._update(vm._render(), hydrating);
    };
  }

  // we set this to vm._watcher inside the watcher's constructor
  // since the watcher's initial patch may call $forceUpdate (e.g. inside child
  // component's mounted hook), which relies on vm._watcher being already defined
  new Watcher(vm, updateComponent, noop$1, {
    before: function before () {
      if (vm._isMounted && !vm._isDestroyed) {
        callHook(vm, 'beforeUpdate');
      }
    }
  }, true /* isRenderWatcher */);
  hydrating = false;

  // manually mounted instance, call mounted on self
  // mounted is called for render-created child components in its inserted hook
  if (vm.$vnode == null) {
    vm._isMounted = true;
    callHook(vm, 'mounted');
  }
  return vm
}

function updateChildComponent (
  vm,
  propsData,
  listeners,
  parentVnode,
  renderChildren
) {
  {
    isUpdatingChildComponent = true;
  }

  // determine whether component has slot children
  // we need to do this before overwriting $options._renderChildren.

  // check if there are dynamic scopedSlots (hand-written or compiled but with
  // dynamic slot names). Static scoped slots compiled from template has the
  // "$stable" marker.
  var newScopedSlots = parentVnode.data.scopedSlots;
  var oldScopedSlots = vm.$scopedSlots;
  var hasDynamicScopedSlot = !!(
    (newScopedSlots && !newScopedSlots.$stable) ||
    (oldScopedSlots !== emptyObject && !oldScopedSlots.$stable) ||
    (newScopedSlots && vm.$scopedSlots.$key !== newScopedSlots.$key)
  );

  // Any static slot children from the parent may have changed during parent's
  // update. Dynamic scoped slots may also have changed. In such cases, a forced
  // update is necessary to ensure correctness.
  var needsForceUpdate = !!(
    renderChildren ||               // has new static slots
    vm.$options._renderChildren ||  // has old static slots
    hasDynamicScopedSlot
  );

  vm.$options._parentVnode = parentVnode;
  vm.$vnode = parentVnode; // update vm's placeholder node without re-render

  if (vm._vnode) { // update child tree's parent
    vm._vnode.parent = parentVnode;
  }
  vm.$options._renderChildren = renderChildren;

  // update $attrs and $listeners hash
  // these are also reactive so they may trigger child update if the child
  // used them during render
  vm.$attrs = parentVnode.data.attrs || emptyObject;
  vm.$listeners = listeners || emptyObject;

  // update props
  if (propsData && vm.$options.props) {
    toggleObserving(false);
    var props = vm._props;
    var propKeys = vm.$options._propKeys || [];
    for (var i = 0; i < propKeys.length; i++) {
      var key = propKeys[i];
      var propOptions = vm.$options.props; // wtf flow?
      props[key] = validateProp(key, propOptions, propsData, vm);
    }
    toggleObserving(true);
    // keep a copy of raw propsData
    vm.$options.propsData = propsData;
  }

  // update listeners
  listeners = listeners || emptyObject;
  var oldListeners = vm.$options._parentListeners;
  vm.$options._parentListeners = listeners;
  updateComponentListeners(vm, listeners, oldListeners);

  // resolve slots + force update if has children
  if (needsForceUpdate) {
    vm.$slots = resolveSlots(renderChildren, parentVnode.context);
    vm.$forceUpdate();
  }

  {
    isUpdatingChildComponent = false;
  }
}

function isInInactiveTree (vm) {
  while (vm && (vm = vm.$parent)) {
    if (vm._inactive) { return true }
  }
  return false
}

function activateChildComponent (vm, direct) {
  if (direct) {
    vm._directInactive = false;
    if (isInInactiveTree(vm)) {
      return
    }
  } else if (vm._directInactive) {
    return
  }
  if (vm._inactive || vm._inactive === null) {
    vm._inactive = false;
    for (var i = 0; i < vm.$children.length; i++) {
      activateChildComponent(vm.$children[i]);
    }
    callHook(vm, 'activated');
  }
}

function deactivateChildComponent (vm, direct) {
  if (direct) {
    vm._directInactive = true;
    if (isInInactiveTree(vm)) {
      return
    }
  }
  if (!vm._inactive) {
    vm._inactive = true;
    for (var i = 0; i < vm.$children.length; i++) {
      deactivateChildComponent(vm.$children[i]);
    }
    callHook(vm, 'deactivated');
  }
}

function callHook (vm, hook) {
  // #7573 disable dep collection when invoking lifecycle hooks
  pushTarget();
  var handlers = vm.$options[hook];
  var info = hook + " hook";
  if (handlers) {
    for (var i = 0, j = handlers.length; i < j; i++) {
      invokeWithErrorHandling(handlers[i], vm, null, vm, info);
    }
  }
  if (vm._hasHookEvent) {
    vm.$emit('hook:' + hook);
  }
  popTarget();
}

/*  */

var MAX_UPDATE_COUNT = 100;

var queue$1 = [];
var activatedChildren = [];
var has = {};
var circular = {};
var waiting = false;
var flushing = false;
var index = 0;

/**
 * Reset the scheduler's state.
 */
function resetSchedulerState () {
  index = queue$1.length = activatedChildren.length = 0;
  has = {};
  {
    circular = {};
  }
  waiting = flushing = false;
}

// Async edge case #6566 requires saving the timestamp when event listeners are
// attached. However, calling performance.now() has a perf overhead especially
// if the page has thousands of event listeners. Instead, we take a timestamp
// every time the scheduler flushes and use that for all event listeners
// attached during that flush.
var currentFlushTimestamp = 0;

// Async edge case fix requires storing an event listener's attach timestamp.
var getNow = Date.now;

// Determine what event timestamp the browser is using. Annoyingly, the
// timestamp can either be hi-res (relative to page load) or low-res
// (relative to UNIX epoch), so in order to compare time we have to use the
// same timestamp type when saving the flush timestamp.
// All IE versions use low-res event timestamps, and have problematic clock
// implementations (#9632)
if (inBrowser && !isIE) {
  var performance$1 = window.performance;
  if (
    performance$1 &&
    typeof performance$1.now === 'function' &&
    getNow() > document.createEvent('Event').timeStamp
  ) {
    // if the event timestamp, although evaluated AFTER the Date.now(), is
    // smaller than it, it means the event is using a hi-res timestamp,
    // and we need to use the hi-res version for event listener timestamps as
    // well.
    getNow = function () { return performance$1.now(); };
  }
}

/**
 * Flush both queues and run the watchers.
 */
function flushSchedulerQueue () {
  currentFlushTimestamp = getNow();
  flushing = true;
  var watcher, id;

  // Sort queue before flush.
  // This ensures that:
  // 1. Components are updated from parent to child. (because parent is always
  //    created before the child)
  // 2. A component's user watchers are run before its render watcher (because
  //    user watchers are created before the render watcher)
  // 3. If a component is destroyed during a parent component's watcher run,
  //    its watchers can be skipped.
  queue$1.sort(function (a, b) { return a.id - b.id; });

  // do not cache length because more watchers might be pushed
  // as we run existing watchers
  for (index = 0; index < queue$1.length; index++) {
    watcher = queue$1[index];
    if (watcher.before) {
      watcher.before();
    }
    id = watcher.id;
    has[id] = null;
    watcher.run();
    // in dev build, check and stop circular updates.
    if (has[id] != null) {
      circular[id] = (circular[id] || 0) + 1;
      if (circular[id] > MAX_UPDATE_COUNT) {
        warn(
          'You may have an infinite update loop ' + (
            watcher.user
              ? ("in watcher with expression \"" + (watcher.expression) + "\"")
              : "in a component render function."
          ),
          watcher.vm
        );
        break
      }
    }
  }

  // keep copies of post queues before resetting state
  var activatedQueue = activatedChildren.slice();
  var updatedQueue = queue$1.slice();

  resetSchedulerState();

  // call component updated and activated hooks
  callActivatedHooks(activatedQueue);
  callUpdatedHooks(updatedQueue);

  // devtool hook
  /* istanbul ignore if */
  if (devtools && config$1.devtools) {
    devtools.emit('flush');
  }
}

function callUpdatedHooks (queue) {
  var i = queue.length;
  while (i--) {
    var watcher = queue[i];
    var vm = watcher.vm;
    if (vm._watcher === watcher && vm._isMounted && !vm._isDestroyed) {
      callHook(vm, 'updated');
    }
  }
}

/**
 * Queue a kept-alive component that was activated during patch.
 * The queue will be processed after the entire tree has been patched.
 */
function queueActivatedComponent (vm) {
  // setting _inactive to false here so that a render function can
  // rely on checking whether it's in an inactive tree (e.g. router-view)
  vm._inactive = false;
  activatedChildren.push(vm);
}

function callActivatedHooks (queue) {
  for (var i = 0; i < queue.length; i++) {
    queue[i]._inactive = true;
    activateChildComponent(queue[i], true /* true */);
  }
}

/**
 * Push a watcher into the watcher queue.
 * Jobs with duplicate IDs will be skipped unless it's
 * pushed when the queue is being flushed.
 */
function queueWatcher (watcher) {
  var id = watcher.id;
  if (has[id] == null) {
    has[id] = true;
    if (!flushing) {
      queue$1.push(watcher);
    } else {
      // if already flushing, splice the watcher based on its id
      // if already past its id, it will be run next immediately.
      var i = queue$1.length - 1;
      while (i > index && queue$1[i].id > watcher.id) {
        i--;
      }
      queue$1.splice(i + 1, 0, watcher);
    }
    // queue the flush
    if (!waiting) {
      waiting = true;

      if (!config$1.async) {
        flushSchedulerQueue();
        return
      }
      nextTick$1(flushSchedulerQueue);
    }
  }
}

/*  */



var uid$2 = 0;

/**
 * A watcher parses an expression, collects dependencies,
 * and fires callback when the expression value changes.
 * This is used for both the $watch() api and directives.
 */
var Watcher = function Watcher (
  vm,
  expOrFn,
  cb,
  options,
  isRenderWatcher
) {
  this.vm = vm;
  if (isRenderWatcher) {
    vm._watcher = this;
  }
  vm._watchers.push(this);
  // options
  if (options) {
    this.deep = !!options.deep;
    this.user = !!options.user;
    this.lazy = !!options.lazy;
    this.sync = !!options.sync;
    this.before = options.before;
  } else {
    this.deep = this.user = this.lazy = this.sync = false;
  }
  this.cb = cb;
  this.id = ++uid$2; // uid for batching
  this.active = true;
  this.dirty = this.lazy; // for lazy watchers
  this.deps = [];
  this.newDeps = [];
  this.depIds = new _Set();
  this.newDepIds = new _Set();
  this.expression = expOrFn.toString();
  // parse expression for getter
  if (typeof expOrFn === 'function') {
    this.getter = expOrFn;
  } else {
    this.getter = parsePath(expOrFn);
    if (!this.getter) {
      this.getter = noop$1;
      warn(
        "Failed watching path: \"" + expOrFn + "\" " +
        'Watcher only accepts simple dot-delimited paths. ' +
        'For full control, use a function instead.',
        vm
      );
    }
  }
  this.value = this.lazy
    ? undefined
    : this.get();
};

/**
 * Evaluate the getter, and re-collect dependencies.
 */
Watcher.prototype.get = function get () {
  pushTarget(this);
  var value;
  var vm = this.vm;
  try {
    value = this.getter.call(vm, vm);
  } catch (e) {
    if (this.user) {
      handleError(e, vm, ("getter for watcher \"" + (this.expression) + "\""));
    } else {
      throw e
    }
  } finally {
    // "touch" every property so they are all tracked as
    // dependencies for deep watching
    if (this.deep) {
      traverse(value);
    }
    popTarget();
    this.cleanupDeps();
  }
  return value
};

/**
 * Add a dependency to this directive.
 */
Watcher.prototype.addDep = function addDep (dep) {
  var id = dep.id;
  if (!this.newDepIds.has(id)) {
    this.newDepIds.add(id);
    this.newDeps.push(dep);
    if (!this.depIds.has(id)) {
      dep.addSub(this);
    }
  }
};

/**
 * Clean up for dependency collection.
 */
Watcher.prototype.cleanupDeps = function cleanupDeps () {
  var i = this.deps.length;
  while (i--) {
    var dep = this.deps[i];
    if (!this.newDepIds.has(dep.id)) {
      dep.removeSub(this);
    }
  }
  var tmp = this.depIds;
  this.depIds = this.newDepIds;
  this.newDepIds = tmp;
  this.newDepIds.clear();
  tmp = this.deps;
  this.deps = this.newDeps;
  this.newDeps = tmp;
  this.newDeps.length = 0;
};

/**
 * Subscriber interface.
 * Will be called when a dependency changes.
 */
Watcher.prototype.update = function update () {
  /* istanbul ignore else */
  if (this.lazy) {
    this.dirty = true;
  } else if (this.sync) {
    this.run();
  } else {
    queueWatcher(this);
  }
};

/**
 * Scheduler job interface.
 * Will be called by the scheduler.
 */
Watcher.prototype.run = function run () {
  if (this.active) {
    var value = this.get();
    if (
      value !== this.value ||
      // Deep watchers and watchers on Object/Arrays should fire even
      // when the value is the same, because the value may
      // have mutated.
      isObject(value) ||
      this.deep
    ) {
      // set new value
      var oldValue = this.value;
      this.value = value;
      if (this.user) {
        try {
          this.cb.call(this.vm, value, oldValue);
        } catch (e) {
          handleError(e, this.vm, ("callback for watcher \"" + (this.expression) + "\""));
        }
      } else {
        this.cb.call(this.vm, value, oldValue);
      }
    }
  }
};

/**
 * Evaluate the value of the watcher.
 * This only gets called for lazy watchers.
 */
Watcher.prototype.evaluate = function evaluate () {
  this.value = this.get();
  this.dirty = false;
};

/**
 * Depend on all deps collected by this watcher.
 */
Watcher.prototype.depend = function depend () {
  var i = this.deps.length;
  while (i--) {
    this.deps[i].depend();
  }
};

/**
 * Remove self from all dependencies' subscriber list.
 */
Watcher.prototype.teardown = function teardown () {
  if (this.active) {
    // remove self from vm's watcher list
    // this is a somewhat expensive operation so we skip it
    // if the vm is being destroyed.
    if (!this.vm._isBeingDestroyed) {
      remove(this.vm._watchers, this);
    }
    var i = this.deps.length;
    while (i--) {
      this.deps[i].removeSub(this);
    }
    this.active = false;
  }
};

/*  */

var sharedPropertyDefinition = {
  enumerable: true,
  configurable: true,
  get: noop$1,
  set: noop$1
};

function proxy (target, sourceKey, key) {
  sharedPropertyDefinition.get = function proxyGetter () {
    return this[sourceKey][key]
  };
  sharedPropertyDefinition.set = function proxySetter (val) {
    this[sourceKey][key] = val;
  };
  Object.defineProperty(target, key, sharedPropertyDefinition);
}

function initState (vm) {
  vm._watchers = [];
  var opts = vm.$options;
  if (opts.props) { initProps(vm, opts.props); }
  if (opts.methods) { initMethods(vm, opts.methods); }
  if (opts.data) {
    initData(vm);
  } else {
    observe(vm._data = {}, true /* asRootData */);
  }
  if (opts.computed) { initComputed(vm, opts.computed); }
  if (opts.watch && opts.watch !== nativeWatch) {
    initWatch(vm, opts.watch);
  }
}

function initProps (vm, propsOptions) {
  var propsData = vm.$options.propsData || {};
  var props = vm._props = {};
  // cache prop keys so that future props updates can iterate using Array
  // instead of dynamic object key enumeration.
  var keys = vm.$options._propKeys = [];
  var isRoot = !vm.$parent;
  // root instance props should be converted
  if (!isRoot) {
    toggleObserving(false);
  }
  var loop = function ( key ) {
    keys.push(key);
    var value = validateProp(key, propsOptions, propsData, vm);
    /* istanbul ignore else */
    {
      var hyphenatedKey = hyphenate(key);
      if (isReservedAttribute(hyphenatedKey) ||
          config$1.isReservedAttr(hyphenatedKey)) {
        warn(
          ("\"" + hyphenatedKey + "\" is a reserved attribute and cannot be used as component prop."),
          vm
        );
      }
      defineReactive$$1(props, key, value, function () {
        if (!isRoot && !isUpdatingChildComponent) {
          warn(
            "Avoid mutating a prop directly since the value will be " +
            "overwritten whenever the parent component re-renders. " +
            "Instead, use a data or computed property based on the prop's " +
            "value. Prop being mutated: \"" + key + "\"",
            vm
          );
        }
      });
    }
    // static props are already proxied on the component's prototype
    // during Vue.extend(). We only need to proxy props defined at
    // instantiation here.
    if (!(key in vm)) {
      proxy(vm, "_props", key);
    }
  };

  for (var key in propsOptions) loop( key );
  toggleObserving(true);
}

function initData (vm) {
  var data = vm.$options.data;
  data = vm._data = typeof data === 'function'
    ? getData(data, vm)
    : data || {};
  if (!isPlainObject(data)) {
    data = {};
    warn(
      'data functions should return an object:\n' +
      'https://vuejs.org/v2/guide/components.html#data-Must-Be-a-Function',
      vm
    );
  }
  // proxy data on instance
  var keys = Object.keys(data);
  var props = vm.$options.props;
  var methods = vm.$options.methods;
  var i = keys.length;
  while (i--) {
    var key = keys[i];
    {
      if (methods && hasOwn(methods, key)) {
        warn(
          ("Method \"" + key + "\" has already been defined as a data property."),
          vm
        );
      }
    }
    if (props && hasOwn(props, key)) {
      warn(
        "The data property \"" + key + "\" is already declared as a prop. " +
        "Use prop default value instead.",
        vm
      );
    } else if (!isReserved(key)) {
      proxy(vm, "_data", key);
    }
  }
  // observe data
  observe(data, true /* asRootData */);
}

function getData (data, vm) {
  // #7573 disable dep collection when invoking data getters
  pushTarget();
  try {
    return data.call(vm, vm)
  } catch (e) {
    handleError(e, vm, "data()");
    return {}
  } finally {
    popTarget();
  }
}

var computedWatcherOptions = { lazy: true };

function initComputed (vm, computed) {
  // $flow-disable-line
  var watchers = vm._computedWatchers = Object.create(null);
  // computed properties are just getters during SSR
  var isSSR = isServerRendering();

  for (var key in computed) {
    var userDef = computed[key];
    var getter = typeof userDef === 'function' ? userDef : userDef.get;
    if (getter == null) {
      warn(
        ("Getter is missing for computed property \"" + key + "\"."),
        vm
      );
    }

    if (!isSSR) {
      // create internal watcher for the computed property.
      watchers[key] = new Watcher(
        vm,
        getter || noop$1,
        noop$1,
        computedWatcherOptions
      );
    }

    // component-defined computed properties are already defined on the
    // component prototype. We only need to define computed properties defined
    // at instantiation here.
    if (!(key in vm)) {
      defineComputed(vm, key, userDef);
    } else {
      if (key in vm.$data) {
        warn(("The computed property \"" + key + "\" is already defined in data."), vm);
      } else if (vm.$options.props && key in vm.$options.props) {
        warn(("The computed property \"" + key + "\" is already defined as a prop."), vm);
      }
    }
  }
}

function defineComputed (
  target,
  key,
  userDef
) {
  var shouldCache = !isServerRendering();
  if (typeof userDef === 'function') {
    sharedPropertyDefinition.get = shouldCache
      ? createComputedGetter(key)
      : createGetterInvoker(userDef);
    sharedPropertyDefinition.set = noop$1;
  } else {
    sharedPropertyDefinition.get = userDef.get
      ? shouldCache && userDef.cache !== false
        ? createComputedGetter(key)
        : createGetterInvoker(userDef.get)
      : noop$1;
    sharedPropertyDefinition.set = userDef.set || noop$1;
  }
  if (sharedPropertyDefinition.set === noop$1) {
    sharedPropertyDefinition.set = function () {
      warn(
        ("Computed property \"" + key + "\" was assigned to but it has no setter."),
        this
      );
    };
  }
  Object.defineProperty(target, key, sharedPropertyDefinition);
}

function createComputedGetter (key) {
  return function computedGetter () {
    var watcher = this._computedWatchers && this._computedWatchers[key];
    if (watcher) {
      if (watcher.dirty) {
        watcher.evaluate();
      }
      if (Dep.target) {
        watcher.depend();
      }
      return watcher.value
    }
  }
}

function createGetterInvoker(fn) {
  return function computedGetter () {
    return fn.call(this, this)
  }
}

function initMethods (vm, methods) {
  var props = vm.$options.props;
  for (var key in methods) {
    {
      if (typeof methods[key] !== 'function') {
        warn(
          "Method \"" + key + "\" has type \"" + (typeof methods[key]) + "\" in the component definition. " +
          "Did you reference the function correctly?",
          vm
        );
      }
      if (props && hasOwn(props, key)) {
        warn(
          ("Method \"" + key + "\" has already been defined as a prop."),
          vm
        );
      }
      if ((key in vm) && isReserved(key)) {
        warn(
          "Method \"" + key + "\" conflicts with an existing Vue instance method. " +
          "Avoid defining component methods that start with _ or $."
        );
      }
    }
    vm[key] = typeof methods[key] !== 'function' ? noop$1 : bind(methods[key], vm);
  }
}

function initWatch (vm, watch) {
  for (var key in watch) {
    var handler = watch[key];
    if (Array.isArray(handler)) {
      for (var i = 0; i < handler.length; i++) {
        createWatcher(vm, key, handler[i]);
      }
    } else {
      createWatcher(vm, key, handler);
    }
  }
}

function createWatcher (
  vm,
  expOrFn,
  handler,
  options
) {
  if (isPlainObject(handler)) {
    options = handler;
    handler = handler.handler;
  }
  if (typeof handler === 'string') {
    handler = vm[handler];
  }
  return vm.$watch(expOrFn, handler, options)
}

function stateMixin (Vue) {
  // flow somehow has problems with directly declared definition object
  // when using Object.defineProperty, so we have to procedurally build up
  // the object here.
  var dataDef = {};
  dataDef.get = function () { return this._data };
  var propsDef = {};
  propsDef.get = function () { return this._props };
  {
    dataDef.set = function () {
      warn(
        'Avoid replacing instance root $data. ' +
        'Use nested data properties instead.',
        this
      );
    };
    propsDef.set = function () {
      warn("$props is readonly.", this);
    };
  }
  Object.defineProperty(Vue.prototype, '$data', dataDef);
  Object.defineProperty(Vue.prototype, '$props', propsDef);

  Vue.prototype.$set = set;
  Vue.prototype.$delete = del;

  Vue.prototype.$watch = function (
    expOrFn,
    cb,
    options
  ) {
    var vm = this;
    if (isPlainObject(cb)) {
      return createWatcher(vm, expOrFn, cb, options)
    }
    options = options || {};
    options.user = true;
    var watcher = new Watcher(vm, expOrFn, cb, options);
    if (options.immediate) {
      try {
        cb.call(vm, watcher.value);
      } catch (error) {
        handleError(error, vm, ("callback for immediate watcher \"" + (watcher.expression) + "\""));
      }
    }
    return function unwatchFn () {
      watcher.teardown();
    }
  };
}

/*  */

var uid$3 = 0;

function initMixin (Vue) {
  Vue.prototype._init = function (options) {
    var vm = this;
    // a uid
    vm._uid = uid$3++;

    var startTag, endTag;
    /* istanbul ignore if */
    if (config$1.performance && mark) {
      startTag = "vue-perf-start:" + (vm._uid);
      endTag = "vue-perf-end:" + (vm._uid);
      mark(startTag);
    }

    // a flag to avoid this being observed
    vm._isVue = true;
    // merge options
    if (options && options._isComponent) {
      // optimize internal component instantiation
      // since dynamic options merging is pretty slow, and none of the
      // internal component options needs special treatment.
      initInternalComponent(vm, options);
    } else {
      vm.$options = mergeOptions(
        resolveConstructorOptions(vm.constructor),
        options || {},
        vm
      );
    }
    /* istanbul ignore else */
    {
      initProxy(vm);
    }
    // expose real self
    vm._self = vm;
    initLifecycle(vm);
    initEvents(vm);
    initRender(vm);
    callHook(vm, 'beforeCreate');
    initInjections(vm); // resolve injections before data/props
    initState(vm);
    initProvide(vm); // resolve provide after data/props
    callHook(vm, 'created');

    /* istanbul ignore if */
    if (config$1.performance && mark) {
      vm._name = formatComponentName(vm, false);
      mark(endTag);
      measure(("vue " + (vm._name) + " init"), startTag, endTag);
    }

    if (vm.$options.el) {
      vm.$mount(vm.$options.el);
    }
  };
}

function initInternalComponent (vm, options) {
  var opts = vm.$options = Object.create(vm.constructor.options);
  // doing this because it's faster than dynamic enumeration.
  var parentVnode = options._parentVnode;
  opts.parent = options.parent;
  opts._parentVnode = parentVnode;

  var vnodeComponentOptions = parentVnode.componentOptions;
  opts.propsData = vnodeComponentOptions.propsData;
  opts._parentListeners = vnodeComponentOptions.listeners;
  opts._renderChildren = vnodeComponentOptions.children;
  opts._componentTag = vnodeComponentOptions.tag;

  if (options.render) {
    opts.render = options.render;
    opts.staticRenderFns = options.staticRenderFns;
  }
}

function resolveConstructorOptions (Ctor) {
  var options = Ctor.options;
  if (Ctor.super) {
    var superOptions = resolveConstructorOptions(Ctor.super);
    var cachedSuperOptions = Ctor.superOptions;
    if (superOptions !== cachedSuperOptions) {
      // super option changed,
      // need to resolve new options.
      Ctor.superOptions = superOptions;
      // check if there are any late-modified/attached options (#4976)
      var modifiedOptions = resolveModifiedOptions(Ctor);
      // update base extend options
      if (modifiedOptions) {
        extend(Ctor.extendOptions, modifiedOptions);
      }
      options = Ctor.options = mergeOptions(superOptions, Ctor.extendOptions);
      if (options.name) {
        options.components[options.name] = Ctor;
      }
    }
  }
  return options
}

function resolveModifiedOptions (Ctor) {
  var modified;
  var latest = Ctor.options;
  var sealed = Ctor.sealedOptions;
  for (var key in latest) {
    if (latest[key] !== sealed[key]) {
      if (!modified) { modified = {}; }
      modified[key] = latest[key];
    }
  }
  return modified
}

function Vue (options) {
  if (!(this instanceof Vue)
  ) {
    warn('Vue is a constructor and should be called with the `new` keyword');
  }
  this._init(options);
}

initMixin(Vue);
stateMixin(Vue);
eventsMixin(Vue);
lifecycleMixin(Vue);
renderMixin(Vue);

/*  */

function initUse (Vue) {
  Vue.use = function (plugin) {
    var installedPlugins = (this._installedPlugins || (this._installedPlugins = []));
    if (installedPlugins.indexOf(plugin) > -1) {
      return this
    }

    // additional parameters
    var args = toArray(arguments, 1);
    args.unshift(this);
    if (typeof plugin.install === 'function') {
      plugin.install.apply(plugin, args);
    } else if (typeof plugin === 'function') {
      plugin.apply(null, args);
    }
    installedPlugins.push(plugin);
    return this
  };
}

/*  */

function initMixin$1 (Vue) {
  Vue.mixin = function (mixin) {
    this.options = mergeOptions(this.options, mixin);
    return this
  };
}

/*  */

function initExtend (Vue) {
  /**
   * Each instance constructor, including Vue, has a unique
   * cid. This enables us to create wrapped "child
   * constructors" for prototypal inheritance and cache them.
   */
  Vue.cid = 0;
  var cid = 1;

  /**
   * Class inheritance
   */
  Vue.extend = function (extendOptions) {
    extendOptions = extendOptions || {};
    var Super = this;
    var SuperId = Super.cid;
    var cachedCtors = extendOptions._Ctor || (extendOptions._Ctor = {});
    if (cachedCtors[SuperId]) {
      return cachedCtors[SuperId]
    }

    var name = extendOptions.name || Super.options.name;
    if (name) {
      validateComponentName(name);
    }

    var Sub = function VueComponent (options) {
      this._init(options);
    };
    Sub.prototype = Object.create(Super.prototype);
    Sub.prototype.constructor = Sub;
    Sub.cid = cid++;
    Sub.options = mergeOptions(
      Super.options,
      extendOptions
    );
    Sub['super'] = Super;

    // For props and computed properties, we define the proxy getters on
    // the Vue instances at extension time, on the extended prototype. This
    // avoids Object.defineProperty calls for each instance created.
    if (Sub.options.props) {
      initProps$1(Sub);
    }
    if (Sub.options.computed) {
      initComputed$1(Sub);
    }

    // allow further extension/mixin/plugin usage
    Sub.extend = Super.extend;
    Sub.mixin = Super.mixin;
    Sub.use = Super.use;

    // create asset registers, so extended classes
    // can have their private assets too.
    ASSET_TYPES.forEach(function (type) {
      Sub[type] = Super[type];
    });
    // enable recursive self-lookup
    if (name) {
      Sub.options.components[name] = Sub;
    }

    // keep a reference to the super options at extension time.
    // later at instantiation we can check if Super's options have
    // been updated.
    Sub.superOptions = Super.options;
    Sub.extendOptions = extendOptions;
    Sub.sealedOptions = extend({}, Sub.options);

    // cache constructor
    cachedCtors[SuperId] = Sub;
    return Sub
  };
}

function initProps$1 (Comp) {
  var props = Comp.options.props;
  for (var key in props) {
    proxy(Comp.prototype, "_props", key);
  }
}

function initComputed$1 (Comp) {
  var computed = Comp.options.computed;
  for (var key in computed) {
    defineComputed(Comp.prototype, key, computed[key]);
  }
}

/*  */

function initAssetRegisters (Vue) {
  /**
   * Create asset registration methods.
   */
  ASSET_TYPES.forEach(function (type) {
    Vue[type] = function (
      id,
      definition
    ) {
      if (!definition) {
        return this.options[type + 's'][id]
      } else {
        /* istanbul ignore if */
        if (type === 'component') {
          validateComponentName(id);
        }
        if (type === 'component' && isPlainObject(definition)) {
          definition.name = definition.name || id;
          definition = this.options._base.extend(definition);
        }
        if (type === 'directive' && typeof definition === 'function') {
          definition = { bind: definition, update: definition };
        }
        this.options[type + 's'][id] = definition;
        return definition
      }
    };
  });
}

/*  */



function getComponentName (opts) {
  return opts && (opts.Ctor.options.name || opts.tag)
}

function matches (pattern, name) {
  if (Array.isArray(pattern)) {
    return pattern.indexOf(name) > -1
  } else if (typeof pattern === 'string') {
    return pattern.split(',').indexOf(name) > -1
  } else if (isRegExp(pattern)) {
    return pattern.test(name)
  }
  /* istanbul ignore next */
  return false
}

function pruneCache (keepAliveInstance, filter) {
  var cache = keepAliveInstance.cache;
  var keys = keepAliveInstance.keys;
  var _vnode = keepAliveInstance._vnode;
  for (var key in cache) {
    var cachedNode = cache[key];
    if (cachedNode) {
      var name = getComponentName(cachedNode.componentOptions);
      if (name && !filter(name)) {
        pruneCacheEntry(cache, key, keys, _vnode);
      }
    }
  }
}

function pruneCacheEntry (
  cache,
  key,
  keys,
  current
) {
  var cached$$1 = cache[key];
  if (cached$$1 && (!current || cached$$1.tag !== current.tag)) {
    cached$$1.componentInstance.$destroy();
  }
  cache[key] = null;
  remove(keys, key);
}

var patternTypes = [String, RegExp, Array];

var KeepAlive = {
  name: 'keep-alive',
  abstract: true,

  props: {
    include: patternTypes,
    exclude: patternTypes,
    max: [String, Number]
  },

  created: function created () {
    this.cache = Object.create(null);
    this.keys = [];
  },

  destroyed: function destroyed () {
    for (var key in this.cache) {
      pruneCacheEntry(this.cache, key, this.keys);
    }
  },

  mounted: function mounted () {
    var this$1 = this;

    this.$watch('include', function (val) {
      pruneCache(this$1, function (name) { return matches(val, name); });
    });
    this.$watch('exclude', function (val) {
      pruneCache(this$1, function (name) { return !matches(val, name); });
    });
  },

  render: function render () {
    var slot = this.$slots.default;
    var vnode = getFirstComponentChild(slot);
    var componentOptions = vnode && vnode.componentOptions;
    if (componentOptions) {
      // check pattern
      var name = getComponentName(componentOptions);
      var ref = this;
      var include = ref.include;
      var exclude = ref.exclude;
      if (
        // not included
        (include && (!name || !matches(include, name))) ||
        // excluded
        (exclude && name && matches(exclude, name))
      ) {
        return vnode
      }

      var ref$1 = this;
      var cache = ref$1.cache;
      var keys = ref$1.keys;
      var key = vnode.key == null
        // same constructor may get registered as different local components
        // so cid alone is not enough (#3269)
        ? componentOptions.Ctor.cid + (componentOptions.tag ? ("::" + (componentOptions.tag)) : '')
        : vnode.key;
      if (cache[key]) {
        vnode.componentInstance = cache[key].componentInstance;
        // make current key freshest
        remove(keys, key);
        keys.push(key);
      } else {
        cache[key] = vnode;
        keys.push(key);
        // prune oldest entry
        if (this.max && keys.length > parseInt(this.max)) {
          pruneCacheEntry(cache, keys[0], keys, this._vnode);
        }
      }

      vnode.data.keepAlive = true;
    }
    return vnode || (slot && slot[0])
  }
};

var builtInComponents = {
  KeepAlive: KeepAlive
};

/*  */

function initGlobalAPI (Vue) {
  // config
  var configDef = {};
  configDef.get = function () { return config$1; };
  {
    configDef.set = function () {
      warn(
        'Do not replace the Vue.config object, set individual fields instead.'
      );
    };
  }
  Object.defineProperty(Vue, 'config', configDef);

  // exposed util methods.
  // NOTE: these are not considered part of the public API - avoid relying on
  // them unless you are aware of the risk.
  Vue.util = {
    warn: warn,
    extend: extend,
    mergeOptions: mergeOptions,
    defineReactive: defineReactive$$1
  };

  Vue.set = set;
  Vue.delete = del;
  Vue.nextTick = nextTick$1;

  // 2.6 explicit observable API
  Vue.observable = function (obj) {
    observe(obj);
    return obj
  };

  Vue.options = Object.create(null);
  ASSET_TYPES.forEach(function (type) {
    Vue.options[type + 's'] = Object.create(null);
  });

  // this is used to identify the "base" constructor to extend all plain-object
  // components with in Weex's multi-instance scenarios.
  Vue.options._base = Vue;

  extend(Vue.options.components, builtInComponents);

  initUse(Vue);
  initMixin$1(Vue);
  initExtend(Vue);
  initAssetRegisters(Vue);
}

initGlobalAPI(Vue);

Object.defineProperty(Vue.prototype, '$isServer', {
  get: isServerRendering
});

Object.defineProperty(Vue.prototype, '$ssrContext', {
  get: function get () {
    /* istanbul ignore next */
    return this.$vnode && this.$vnode.ssrContext
  }
});

// expose FunctionalRenderContext for ssr runtime helper installation
Object.defineProperty(Vue, 'FunctionalRenderContext', {
  value: FunctionalRenderContext
});

Vue.version = '2.6.10';

/*  */

// these are reserved for web because they are directly compiled away
// during template compilation
var isReservedAttr = makeMap('style,class');

// attributes that should be using props for binding
var acceptValue = makeMap('input,textarea,option,select,progress');
var mustUseProp = function (tag, type, attr) {
  return (
    (attr === 'value' && acceptValue(tag)) && type !== 'button' ||
    (attr === 'selected' && tag === 'option') ||
    (attr === 'checked' && tag === 'input') ||
    (attr === 'muted' && tag === 'video')
  )
};

var isEnumeratedAttr = makeMap('contenteditable,draggable,spellcheck');

var isValidContentEditableValue = makeMap('events,caret,typing,plaintext-only');

var convertEnumeratedValue = function (key, value) {
  return isFalsyAttrValue(value) || value === 'false'
    ? 'false'
    // allow arbitrary string value for contenteditable
    : key === 'contenteditable' && isValidContentEditableValue(value)
      ? value
      : 'true'
};

var isBooleanAttr = makeMap(
  'allowfullscreen,async,autofocus,autoplay,checked,compact,controls,declare,' +
  'default,defaultchecked,defaultmuted,defaultselected,defer,disabled,' +
  'enabled,formnovalidate,hidden,indeterminate,inert,ismap,itemscope,loop,multiple,' +
  'muted,nohref,noresize,noshade,novalidate,nowrap,open,pauseonexit,readonly,' +
  'required,reversed,scoped,seamless,selected,sortable,translate,' +
  'truespeed,typemustmatch,visible'
);

var xlinkNS = 'http://www.w3.org/1999/xlink';

var isXlink = function (name) {
  return name.charAt(5) === ':' && name.slice(0, 5) === 'xlink'
};

var getXlinkProp = function (name) {
  return isXlink(name) ? name.slice(6, name.length) : ''
};

var isFalsyAttrValue = function (val) {
  return val == null || val === false
};

/*  */

function genClassForVnode (vnode) {
  var data = vnode.data;
  var parentNode = vnode;
  var childNode = vnode;
  while (isDef(childNode.componentInstance)) {
    childNode = childNode.componentInstance._vnode;
    if (childNode && childNode.data) {
      data = mergeClassData(childNode.data, data);
    }
  }
  while (isDef(parentNode = parentNode.parent)) {
    if (parentNode && parentNode.data) {
      data = mergeClassData(data, parentNode.data);
    }
  }
  return renderClass(data.staticClass, data.class)
}

function mergeClassData (child, parent) {
  return {
    staticClass: concat(child.staticClass, parent.staticClass),
    class: isDef(child.class)
      ? [child.class, parent.class]
      : parent.class
  }
}

function renderClass (
  staticClass,
  dynamicClass
) {
  if (isDef(staticClass) || isDef(dynamicClass)) {
    return concat(staticClass, stringifyClass(dynamicClass))
  }
  /* istanbul ignore next */
  return ''
}

function concat (a, b) {
  return a ? b ? (a + ' ' + b) : a : (b || '')
}

function stringifyClass (value) {
  if (Array.isArray(value)) {
    return stringifyArray(value)
  }
  if (isObject(value)) {
    return stringifyObject(value)
  }
  if (typeof value === 'string') {
    return value
  }
  /* istanbul ignore next */
  return ''
}

function stringifyArray (value) {
  var res = '';
  var stringified;
  for (var i = 0, l = value.length; i < l; i++) {
    if (isDef(stringified = stringifyClass(value[i])) && stringified !== '') {
      if (res) { res += ' '; }
      res += stringified;
    }
  }
  return res
}

function stringifyObject (value) {
  var res = '';
  for (var key in value) {
    if (value[key]) {
      if (res) { res += ' '; }
      res += key;
    }
  }
  return res
}

/*  */

var namespaceMap = {
  svg: 'http://www.w3.org/2000/svg',
  math: 'http://www.w3.org/1998/Math/MathML'
};

var isHTMLTag = makeMap(
  'html,body,base,head,link,meta,style,title,' +
  'address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,hgroup,nav,section,' +
  'div,dd,dl,dt,figcaption,figure,picture,hr,img,li,main,ol,p,pre,ul,' +
  'a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,rtc,ruby,' +
  's,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,' +
  'embed,object,param,source,canvas,script,noscript,del,ins,' +
  'caption,col,colgroup,table,thead,tbody,td,th,tr,' +
  'button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,' +
  'output,progress,select,textarea,' +
  'details,dialog,menu,menuitem,summary,' +
  'content,element,shadow,template,blockquote,iframe,tfoot'
);

// this map is intentionally selective, only covering SVG elements that may
// contain child elements.
var isSVG = makeMap(
  'svg,animate,circle,clippath,cursor,defs,desc,ellipse,filter,font-face,' +
  'foreignObject,g,glyph,image,line,marker,mask,missing-glyph,path,pattern,' +
  'polygon,polyline,rect,switch,symbol,text,textpath,tspan,use,view',
  true
);

var isReservedTag = function (tag) {
  return isHTMLTag(tag) || isSVG(tag)
};

function getTagNamespace (tag) {
  if (isSVG(tag)) {
    return 'svg'
  }
  // basic support for MathML
  // note it doesn't support other MathML elements being component roots
  if (tag === 'math') {
    return 'math'
  }
}

var unknownElementCache = Object.create(null);
function isUnknownElement (tag) {
  /* istanbul ignore if */
  if (!inBrowser) {
    return true
  }
  if (isReservedTag(tag)) {
    return false
  }
  tag = tag.toLowerCase();
  /* istanbul ignore if */
  if (unknownElementCache[tag] != null) {
    return unknownElementCache[tag]
  }
  var el = document.createElement(tag);
  if (tag.indexOf('-') > -1) {
    // http://stackoverflow.com/a/28210364/1070244
    return (unknownElementCache[tag] = (
      el.constructor === window.HTMLUnknownElement ||
      el.constructor === window.HTMLElement
    ))
  } else {
    return (unknownElementCache[tag] = /HTMLUnknownElement/.test(el.toString()))
  }
}

var isTextInputType = makeMap('text,number,password,search,email,tel,url');

/*  */

/**
 * Query an element selector if it's not an element already.
 */
function query (el) {
  if (typeof el === 'string') {
    var selected = document.querySelector(el);
    if (!selected) {
      warn(
        'Cannot find element: ' + el
      );
      return document.createElement('div')
    }
    return selected
  } else {
    return el
  }
}

/*  */

function createElement$1 (tagName, vnode) {
  var elm = document.createElement(tagName);
  if (tagName !== 'select') {
    return elm
  }
  // false or null will remove the attribute but undefined will not
  if (vnode.data && vnode.data.attrs && vnode.data.attrs.multiple !== undefined) {
    elm.setAttribute('multiple', 'multiple');
  }
  return elm
}

function createElementNS (namespace, tagName) {
  return document.createElementNS(namespaceMap[namespace], tagName)
}

function createTextNode (text) {
  return document.createTextNode(text)
}

function createComment (text) {
  return document.createComment(text)
}

function insertBefore (parentNode, newNode, referenceNode) {
  parentNode.insertBefore(newNode, referenceNode);
}

function removeChild (node, child) {
  node.removeChild(child);
}

function appendChild (node, child) {
  node.appendChild(child);
}

function parentNode (node) {
  return node.parentNode
}

function nextSibling (node) {
  return node.nextSibling
}

function tagName (node) {
  return node.tagName
}

function setTextContent (node, text) {
  node.textContent = text;
}

function setStyleScope (node, scopeId) {
  node.setAttribute(scopeId, '');
}

var nodeOps = /*#__PURE__*/Object.freeze({
  createElement: createElement$1,
  createElementNS: createElementNS,
  createTextNode: createTextNode,
  createComment: createComment,
  insertBefore: insertBefore,
  removeChild: removeChild,
  appendChild: appendChild,
  parentNode: parentNode,
  nextSibling: nextSibling,
  tagName: tagName,
  setTextContent: setTextContent,
  setStyleScope: setStyleScope
});

/*  */

var ref = {
  create: function create (_, vnode) {
    registerRef(vnode);
  },
  update: function update (oldVnode, vnode) {
    if (oldVnode.data.ref !== vnode.data.ref) {
      registerRef(oldVnode, true);
      registerRef(vnode);
    }
  },
  destroy: function destroy (vnode) {
    registerRef(vnode, true);
  }
};

function registerRef (vnode, isRemoval) {
  var key = vnode.data.ref;
  if (!isDef(key)) { return }

  var vm = vnode.context;
  var ref = vnode.componentInstance || vnode.elm;
  var refs = vm.$refs;
  if (isRemoval) {
    if (Array.isArray(refs[key])) {
      remove(refs[key], ref);
    } else if (refs[key] === ref) {
      refs[key] = undefined;
    }
  } else {
    if (vnode.data.refInFor) {
      if (!Array.isArray(refs[key])) {
        refs[key] = [ref];
      } else if (refs[key].indexOf(ref) < 0) {
        // $flow-disable-line
        refs[key].push(ref);
      }
    } else {
      refs[key] = ref;
    }
  }
}

/**
 * Virtual DOM patching algorithm based on Snabbdom by
 * Simon Friis Vindum (@paldepind)
 * Licensed under the MIT License
 * https://github.com/paldepind/snabbdom/blob/master/LICENSE
 *
 * modified by Evan You (@yyx990803)
 *
 * Not type-checking this because this file is perf-critical and the cost
 * of making flow understand it is not worth it.
 */

var emptyNode = new VNode('', {}, []);

var hooks = ['create', 'activate', 'update', 'remove', 'destroy'];

function sameVnode (a, b) {
  return (
    a.key === b.key && (
      (
        a.tag === b.tag &&
        a.isComment === b.isComment &&
        isDef(a.data) === isDef(b.data) &&
        sameInputType(a, b)
      ) || (
        isTrue(a.isAsyncPlaceholder) &&
        a.asyncFactory === b.asyncFactory &&
        isUndef(b.asyncFactory.error)
      )
    )
  )
}

function sameInputType (a, b) {
  if (a.tag !== 'input') { return true }
  var i;
  var typeA = isDef(i = a.data) && isDef(i = i.attrs) && i.type;
  var typeB = isDef(i = b.data) && isDef(i = i.attrs) && i.type;
  return typeA === typeB || isTextInputType(typeA) && isTextInputType(typeB)
}

function createKeyToOldIdx (children, beginIdx, endIdx) {
  var i, key;
  var map = {};
  for (i = beginIdx; i <= endIdx; ++i) {
    key = children[i].key;
    if (isDef(key)) { map[key] = i; }
  }
  return map
}

function createPatchFunction (backend) {
  var i, j;
  var cbs = {};

  var modules = backend.modules;
  var nodeOps = backend.nodeOps;

  for (i = 0; i < hooks.length; ++i) {
    cbs[hooks[i]] = [];
    for (j = 0; j < modules.length; ++j) {
      if (isDef(modules[j][hooks[i]])) {
        cbs[hooks[i]].push(modules[j][hooks[i]]);
      }
    }
  }

  function emptyNodeAt (elm) {
    return new VNode(nodeOps.tagName(elm).toLowerCase(), {}, [], undefined, elm)
  }

  function createRmCb (childElm, listeners) {
    function remove$$1 () {
      if (--remove$$1.listeners === 0) {
        removeNode(childElm);
      }
    }
    remove$$1.listeners = listeners;
    return remove$$1
  }

  function removeNode (el) {
    var parent = nodeOps.parentNode(el);
    // element may have already been removed due to v-html / v-text
    if (isDef(parent)) {
      nodeOps.removeChild(parent, el);
    }
  }

  function isUnknownElement$$1 (vnode, inVPre) {
    return (
      !inVPre &&
      !vnode.ns &&
      !(
        config$1.ignoredElements.length &&
        config$1.ignoredElements.some(function (ignore) {
          return isRegExp(ignore)
            ? ignore.test(vnode.tag)
            : ignore === vnode.tag
        })
      ) &&
      config$1.isUnknownElement(vnode.tag)
    )
  }

  var creatingElmInVPre = 0;

  function createElm (
    vnode,
    insertedVnodeQueue,
    parentElm,
    refElm,
    nested,
    ownerArray,
    index
  ) {
    if (isDef(vnode.elm) && isDef(ownerArray)) {
      // This vnode was used in a previous render!
      // now it's used as a new node, overwriting its elm would cause
      // potential patch errors down the road when it's used as an insertion
      // reference node. Instead, we clone the node on-demand before creating
      // associated DOM element for it.
      vnode = ownerArray[index] = cloneVNode(vnode);
    }

    vnode.isRootInsert = !nested; // for transition enter check
    if (createComponent(vnode, insertedVnodeQueue, parentElm, refElm)) {
      return
    }

    var data = vnode.data;
    var children = vnode.children;
    var tag = vnode.tag;
    if (isDef(tag)) {
      {
        if (data && data.pre) {
          creatingElmInVPre++;
        }
        if (isUnknownElement$$1(vnode, creatingElmInVPre)) {
          warn(
            'Unknown custom element: <' + tag + '> - did you ' +
            'register the component correctly? For recursive components, ' +
            'make sure to provide the "name" option.',
            vnode.context
          );
        }
      }

      vnode.elm = vnode.ns
        ? nodeOps.createElementNS(vnode.ns, tag)
        : nodeOps.createElement(tag, vnode);
      setScope(vnode);

      /* istanbul ignore if */
      {
        createChildren(vnode, children, insertedVnodeQueue);
        if (isDef(data)) {
          invokeCreateHooks(vnode, insertedVnodeQueue);
        }
        insert(parentElm, vnode.elm, refElm);
      }

      if (data && data.pre) {
        creatingElmInVPre--;
      }
    } else if (isTrue(vnode.isComment)) {
      vnode.elm = nodeOps.createComment(vnode.text);
      insert(parentElm, vnode.elm, refElm);
    } else {
      vnode.elm = nodeOps.createTextNode(vnode.text);
      insert(parentElm, vnode.elm, refElm);
    }
  }

  function createComponent (vnode, insertedVnodeQueue, parentElm, refElm) {
    var i = vnode.data;
    if (isDef(i)) {
      var isReactivated = isDef(vnode.componentInstance) && i.keepAlive;
      if (isDef(i = i.hook) && isDef(i = i.init)) {
        i(vnode, false /* hydrating */);
      }
      // after calling the init hook, if the vnode is a child component
      // it should've created a child instance and mounted it. the child
      // component also has set the placeholder vnode's elm.
      // in that case we can just return the element and be done.
      if (isDef(vnode.componentInstance)) {
        initComponent(vnode, insertedVnodeQueue);
        insert(parentElm, vnode.elm, refElm);
        if (isTrue(isReactivated)) {
          reactivateComponent(vnode, insertedVnodeQueue, parentElm, refElm);
        }
        return true
      }
    }
  }

  function initComponent (vnode, insertedVnodeQueue) {
    if (isDef(vnode.data.pendingInsert)) {
      insertedVnodeQueue.push.apply(insertedVnodeQueue, vnode.data.pendingInsert);
      vnode.data.pendingInsert = null;
    }
    vnode.elm = vnode.componentInstance.$el;
    if (isPatchable(vnode)) {
      invokeCreateHooks(vnode, insertedVnodeQueue);
      setScope(vnode);
    } else {
      // empty component root.
      // skip all element-related modules except for ref (#3455)
      registerRef(vnode);
      // make sure to invoke the insert hook
      insertedVnodeQueue.push(vnode);
    }
  }

  function reactivateComponent (vnode, insertedVnodeQueue, parentElm, refElm) {
    var i;
    // hack for #4339: a reactivated component with inner transition
    // does not trigger because the inner node's created hooks are not called
    // again. It's not ideal to involve module-specific logic in here but
    // there doesn't seem to be a better way to do it.
    var innerNode = vnode;
    while (innerNode.componentInstance) {
      innerNode = innerNode.componentInstance._vnode;
      if (isDef(i = innerNode.data) && isDef(i = i.transition)) {
        for (i = 0; i < cbs.activate.length; ++i) {
          cbs.activate[i](emptyNode, innerNode);
        }
        insertedVnodeQueue.push(innerNode);
        break
      }
    }
    // unlike a newly created component,
    // a reactivated keep-alive component doesn't insert itself
    insert(parentElm, vnode.elm, refElm);
  }

  function insert (parent, elm, ref$$1) {
    if (isDef(parent)) {
      if (isDef(ref$$1)) {
        if (nodeOps.parentNode(ref$$1) === parent) {
          nodeOps.insertBefore(parent, elm, ref$$1);
        }
      } else {
        nodeOps.appendChild(parent, elm);
      }
    }
  }

  function createChildren (vnode, children, insertedVnodeQueue) {
    if (Array.isArray(children)) {
      {
        checkDuplicateKeys(children);
      }
      for (var i = 0; i < children.length; ++i) {
        createElm(children[i], insertedVnodeQueue, vnode.elm, null, true, children, i);
      }
    } else if (isPrimitive(vnode.text)) {
      nodeOps.appendChild(vnode.elm, nodeOps.createTextNode(String(vnode.text)));
    }
  }

  function isPatchable (vnode) {
    while (vnode.componentInstance) {
      vnode = vnode.componentInstance._vnode;
    }
    return isDef(vnode.tag)
  }

  function invokeCreateHooks (vnode, insertedVnodeQueue) {
    for (var i$1 = 0; i$1 < cbs.create.length; ++i$1) {
      cbs.create[i$1](emptyNode, vnode);
    }
    i = vnode.data.hook; // Reuse variable
    if (isDef(i)) {
      if (isDef(i.create)) { i.create(emptyNode, vnode); }
      if (isDef(i.insert)) { insertedVnodeQueue.push(vnode); }
    }
  }

  // set scope id attribute for scoped CSS.
  // this is implemented as a special case to avoid the overhead
  // of going through the normal attribute patching process.
  function setScope (vnode) {
    var i;
    if (isDef(i = vnode.fnScopeId)) {
      nodeOps.setStyleScope(vnode.elm, i);
    } else {
      var ancestor = vnode;
      while (ancestor) {
        if (isDef(i = ancestor.context) && isDef(i = i.$options._scopeId)) {
          nodeOps.setStyleScope(vnode.elm, i);
        }
        ancestor = ancestor.parent;
      }
    }
    // for slot content they should also get the scopeId from the host instance.
    if (isDef(i = activeInstance) &&
      i !== vnode.context &&
      i !== vnode.fnContext &&
      isDef(i = i.$options._scopeId)
    ) {
      nodeOps.setStyleScope(vnode.elm, i);
    }
  }

  function addVnodes (parentElm, refElm, vnodes, startIdx, endIdx, insertedVnodeQueue) {
    for (; startIdx <= endIdx; ++startIdx) {
      createElm(vnodes[startIdx], insertedVnodeQueue, parentElm, refElm, false, vnodes, startIdx);
    }
  }

  function invokeDestroyHook (vnode) {
    var i, j;
    var data = vnode.data;
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.destroy)) { i(vnode); }
      for (i = 0; i < cbs.destroy.length; ++i) { cbs.destroy[i](vnode); }
    }
    if (isDef(i = vnode.children)) {
      for (j = 0; j < vnode.children.length; ++j) {
        invokeDestroyHook(vnode.children[j]);
      }
    }
  }

  function removeVnodes (parentElm, vnodes, startIdx, endIdx) {
    for (; startIdx <= endIdx; ++startIdx) {
      var ch = vnodes[startIdx];
      if (isDef(ch)) {
        if (isDef(ch.tag)) {
          removeAndInvokeRemoveHook(ch);
          invokeDestroyHook(ch);
        } else { // Text node
          removeNode(ch.elm);
        }
      }
    }
  }

  function removeAndInvokeRemoveHook (vnode, rm) {
    if (isDef(rm) || isDef(vnode.data)) {
      var i;
      var listeners = cbs.remove.length + 1;
      if (isDef(rm)) {
        // we have a recursively passed down rm callback
        // increase the listeners count
        rm.listeners += listeners;
      } else {
        // directly removing
        rm = createRmCb(vnode.elm, listeners);
      }
      // recursively invoke hooks on child component root node
      if (isDef(i = vnode.componentInstance) && isDef(i = i._vnode) && isDef(i.data)) {
        removeAndInvokeRemoveHook(i, rm);
      }
      for (i = 0; i < cbs.remove.length; ++i) {
        cbs.remove[i](vnode, rm);
      }
      if (isDef(i = vnode.data.hook) && isDef(i = i.remove)) {
        i(vnode, rm);
      } else {
        rm();
      }
    } else {
      removeNode(vnode.elm);
    }
  }

  function updateChildren (parentElm, oldCh, newCh, insertedVnodeQueue, removeOnly) {
    var oldStartIdx = 0;
    var newStartIdx = 0;
    var oldEndIdx = oldCh.length - 1;
    var oldStartVnode = oldCh[0];
    var oldEndVnode = oldCh[oldEndIdx];
    var newEndIdx = newCh.length - 1;
    var newStartVnode = newCh[0];
    var newEndVnode = newCh[newEndIdx];
    var oldKeyToIdx, idxInOld, vnodeToMove, refElm;

    // removeOnly is a special flag used only by <transition-group>
    // to ensure removed elements stay in correct relative positions
    // during leaving transitions
    var canMove = !removeOnly;

    {
      checkDuplicateKeys(newCh);
    }

    while (oldStartIdx <= oldEndIdx && newStartIdx <= newEndIdx) {
      if (isUndef(oldStartVnode)) {
        oldStartVnode = oldCh[++oldStartIdx]; // Vnode has been moved left
      } else if (isUndef(oldEndVnode)) {
        oldEndVnode = oldCh[--oldEndIdx];
      } else if (sameVnode(oldStartVnode, newStartVnode)) {
        patchVnode(oldStartVnode, newStartVnode, insertedVnodeQueue, newCh, newStartIdx);
        oldStartVnode = oldCh[++oldStartIdx];
        newStartVnode = newCh[++newStartIdx];
      } else if (sameVnode(oldEndVnode, newEndVnode)) {
        patchVnode(oldEndVnode, newEndVnode, insertedVnodeQueue, newCh, newEndIdx);
        oldEndVnode = oldCh[--oldEndIdx];
        newEndVnode = newCh[--newEndIdx];
      } else if (sameVnode(oldStartVnode, newEndVnode)) { // Vnode moved right
        patchVnode(oldStartVnode, newEndVnode, insertedVnodeQueue, newCh, newEndIdx);
        canMove && nodeOps.insertBefore(parentElm, oldStartVnode.elm, nodeOps.nextSibling(oldEndVnode.elm));
        oldStartVnode = oldCh[++oldStartIdx];
        newEndVnode = newCh[--newEndIdx];
      } else if (sameVnode(oldEndVnode, newStartVnode)) { // Vnode moved left
        patchVnode(oldEndVnode, newStartVnode, insertedVnodeQueue, newCh, newStartIdx);
        canMove && nodeOps.insertBefore(parentElm, oldEndVnode.elm, oldStartVnode.elm);
        oldEndVnode = oldCh[--oldEndIdx];
        newStartVnode = newCh[++newStartIdx];
      } else {
        if (isUndef(oldKeyToIdx)) { oldKeyToIdx = createKeyToOldIdx(oldCh, oldStartIdx, oldEndIdx); }
        idxInOld = isDef(newStartVnode.key)
          ? oldKeyToIdx[newStartVnode.key]
          : findIdxInOld(newStartVnode, oldCh, oldStartIdx, oldEndIdx);
        if (isUndef(idxInOld)) { // New element
          createElm(newStartVnode, insertedVnodeQueue, parentElm, oldStartVnode.elm, false, newCh, newStartIdx);
        } else {
          vnodeToMove = oldCh[idxInOld];
          if (sameVnode(vnodeToMove, newStartVnode)) {
            patchVnode(vnodeToMove, newStartVnode, insertedVnodeQueue, newCh, newStartIdx);
            oldCh[idxInOld] = undefined;
            canMove && nodeOps.insertBefore(parentElm, vnodeToMove.elm, oldStartVnode.elm);
          } else {
            // same key but different element. treat as new element
            createElm(newStartVnode, insertedVnodeQueue, parentElm, oldStartVnode.elm, false, newCh, newStartIdx);
          }
        }
        newStartVnode = newCh[++newStartIdx];
      }
    }
    if (oldStartIdx > oldEndIdx) {
      refElm = isUndef(newCh[newEndIdx + 1]) ? null : newCh[newEndIdx + 1].elm;
      addVnodes(parentElm, refElm, newCh, newStartIdx, newEndIdx, insertedVnodeQueue);
    } else if (newStartIdx > newEndIdx) {
      removeVnodes(parentElm, oldCh, oldStartIdx, oldEndIdx);
    }
  }

  function checkDuplicateKeys (children) {
    var seenKeys = {};
    for (var i = 0; i < children.length; i++) {
      var vnode = children[i];
      var key = vnode.key;
      if (isDef(key)) {
        if (seenKeys[key]) {
          warn(
            ("Duplicate keys detected: '" + key + "'. This may cause an update error."),
            vnode.context
          );
        } else {
          seenKeys[key] = true;
        }
      }
    }
  }

  function findIdxInOld (node, oldCh, start, end) {
    for (var i = start; i < end; i++) {
      var c = oldCh[i];
      if (isDef(c) && sameVnode(node, c)) { return i }
    }
  }

  function patchVnode (
    oldVnode,
    vnode,
    insertedVnodeQueue,
    ownerArray,
    index,
    removeOnly
  ) {
    if (oldVnode === vnode) {
      return
    }

    if (isDef(vnode.elm) && isDef(ownerArray)) {
      // clone reused vnode
      vnode = ownerArray[index] = cloneVNode(vnode);
    }

    var elm = vnode.elm = oldVnode.elm;

    if (isTrue(oldVnode.isAsyncPlaceholder)) {
      if (isDef(vnode.asyncFactory.resolved)) {
        hydrate(oldVnode.elm, vnode, insertedVnodeQueue);
      } else {
        vnode.isAsyncPlaceholder = true;
      }
      return
    }

    // reuse element for static trees.
    // note we only do this if the vnode is cloned -
    // if the new node is not cloned it means the render functions have been
    // reset by the hot-reload-api and we need to do a proper re-render.
    if (isTrue(vnode.isStatic) &&
      isTrue(oldVnode.isStatic) &&
      vnode.key === oldVnode.key &&
      (isTrue(vnode.isCloned) || isTrue(vnode.isOnce))
    ) {
      vnode.componentInstance = oldVnode.componentInstance;
      return
    }

    var i;
    var data = vnode.data;
    if (isDef(data) && isDef(i = data.hook) && isDef(i = i.prepatch)) {
      i(oldVnode, vnode);
    }

    var oldCh = oldVnode.children;
    var ch = vnode.children;
    if (isDef(data) && isPatchable(vnode)) {
      for (i = 0; i < cbs.update.length; ++i) { cbs.update[i](oldVnode, vnode); }
      if (isDef(i = data.hook) && isDef(i = i.update)) { i(oldVnode, vnode); }
    }
    if (isUndef(vnode.text)) {
      if (isDef(oldCh) && isDef(ch)) {
        if (oldCh !== ch) { updateChildren(elm, oldCh, ch, insertedVnodeQueue, removeOnly); }
      } else if (isDef(ch)) {
        {
          checkDuplicateKeys(ch);
        }
        if (isDef(oldVnode.text)) { nodeOps.setTextContent(elm, ''); }
        addVnodes(elm, null, ch, 0, ch.length - 1, insertedVnodeQueue);
      } else if (isDef(oldCh)) {
        removeVnodes(elm, oldCh, 0, oldCh.length - 1);
      } else if (isDef(oldVnode.text)) {
        nodeOps.setTextContent(elm, '');
      }
    } else if (oldVnode.text !== vnode.text) {
      nodeOps.setTextContent(elm, vnode.text);
    }
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.postpatch)) { i(oldVnode, vnode); }
    }
  }

  function invokeInsertHook (vnode, queue, initial) {
    // delay insert hooks for component root nodes, invoke them after the
    // element is really inserted
    if (isTrue(initial) && isDef(vnode.parent)) {
      vnode.parent.data.pendingInsert = queue;
    } else {
      for (var i = 0; i < queue.length; ++i) {
        queue[i].data.hook.insert(queue[i]);
      }
    }
  }

  var hydrationBailed = false;
  // list of modules that can skip create hook during hydration because they
  // are already rendered on the client or has no need for initialization
  // Note: style is excluded because it relies on initial clone for future
  // deep updates (#7063).
  var isRenderedModule = makeMap('attrs,class,staticClass,staticStyle,key');

  // Note: this is a browser-only function so we can assume elms are DOM nodes.
  function hydrate (elm, vnode, insertedVnodeQueue, inVPre) {
    var i;
    var tag = vnode.tag;
    var data = vnode.data;
    var children = vnode.children;
    inVPre = inVPre || (data && data.pre);
    vnode.elm = elm;

    if (isTrue(vnode.isComment) && isDef(vnode.asyncFactory)) {
      vnode.isAsyncPlaceholder = true;
      return true
    }
    // assert node match
    {
      if (!assertNodeMatch(elm, vnode, inVPre)) {
        return false
      }
    }
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.init)) { i(vnode, true /* hydrating */); }
      if (isDef(i = vnode.componentInstance)) {
        // child component. it should have hydrated its own tree.
        initComponent(vnode, insertedVnodeQueue);
        return true
      }
    }
    if (isDef(tag)) {
      if (isDef(children)) {
        // empty element, allow client to pick up and populate children
        if (!elm.hasChildNodes()) {
          createChildren(vnode, children, insertedVnodeQueue);
        } else {
          // v-html and domProps: innerHTML
          if (isDef(i = data) && isDef(i = i.domProps) && isDef(i = i.innerHTML)) {
            if (i !== elm.innerHTML) {
              /* istanbul ignore if */
              if (typeof console !== 'undefined' &&
                !hydrationBailed
              ) {
                hydrationBailed = true;
                console.warn('Parent: ', elm);
                console.warn('server innerHTML: ', i);
                console.warn('client innerHTML: ', elm.innerHTML);
              }
              return false
            }
          } else {
            // iterate and compare children lists
            var childrenMatch = true;
            var childNode = elm.firstChild;
            for (var i$1 = 0; i$1 < children.length; i$1++) {
              if (!childNode || !hydrate(childNode, children[i$1], insertedVnodeQueue, inVPre)) {
                childrenMatch = false;
                break
              }
              childNode = childNode.nextSibling;
            }
            // if childNode is not null, it means the actual childNodes list is
            // longer than the virtual children list.
            if (!childrenMatch || childNode) {
              /* istanbul ignore if */
              if (typeof console !== 'undefined' &&
                !hydrationBailed
              ) {
                hydrationBailed = true;
                console.warn('Parent: ', elm);
                console.warn('Mismatching childNodes vs. VNodes: ', elm.childNodes, children);
              }
              return false
            }
          }
        }
      }
      if (isDef(data)) {
        var fullInvoke = false;
        for (var key in data) {
          if (!isRenderedModule(key)) {
            fullInvoke = true;
            invokeCreateHooks(vnode, insertedVnodeQueue);
            break
          }
        }
        if (!fullInvoke && data['class']) {
          // ensure collecting deps for deep class bindings for future updates
          traverse(data['class']);
        }
      }
    } else if (elm.data !== vnode.text) {
      elm.data = vnode.text;
    }
    return true
  }

  function assertNodeMatch (node, vnode, inVPre) {
    if (isDef(vnode.tag)) {
      return vnode.tag.indexOf('vue-component') === 0 || (
        !isUnknownElement$$1(vnode, inVPre) &&
        vnode.tag.toLowerCase() === (node.tagName && node.tagName.toLowerCase())
      )
    } else {
      return node.nodeType === (vnode.isComment ? 8 : 3)
    }
  }

  return function patch (oldVnode, vnode, hydrating, removeOnly) {
    if (isUndef(vnode)) {
      if (isDef(oldVnode)) { invokeDestroyHook(oldVnode); }
      return
    }

    var isInitialPatch = false;
    var insertedVnodeQueue = [];

    if (isUndef(oldVnode)) {
      // empty mount (likely as component), create new root element
      isInitialPatch = true;
      createElm(vnode, insertedVnodeQueue);
    } else {
      var isRealElement = isDef(oldVnode.nodeType);
      if (!isRealElement && sameVnode(oldVnode, vnode)) {
        // patch existing root node
        patchVnode(oldVnode, vnode, insertedVnodeQueue, null, null, removeOnly);
      } else {
        if (isRealElement) {
          // mounting to a real element
          // check if this is server-rendered content and if we can perform
          // a successful hydration.
          if (oldVnode.nodeType === 1 && oldVnode.hasAttribute(SSR_ATTR)) {
            oldVnode.removeAttribute(SSR_ATTR);
            hydrating = true;
          }
          if (isTrue(hydrating)) {
            if (hydrate(oldVnode, vnode, insertedVnodeQueue)) {
              invokeInsertHook(vnode, insertedVnodeQueue, true);
              return oldVnode
            } else {
              warn(
                'The client-side rendered virtual DOM tree is not matching ' +
                'server-rendered content. This is likely caused by incorrect ' +
                'HTML markup, for example nesting block-level elements inside ' +
                '<p>, or missing <tbody>. Bailing hydration and performing ' +
                'full client-side render.'
              );
            }
          }
          // either not server-rendered, or hydration failed.
          // create an empty node and replace it
          oldVnode = emptyNodeAt(oldVnode);
        }

        // replacing existing element
        var oldElm = oldVnode.elm;
        var parentElm = nodeOps.parentNode(oldElm);

        // create new node
        createElm(
          vnode,
          insertedVnodeQueue,
          // extremely rare edge case: do not insert if old element is in a
          // leaving transition. Only happens when combining transition +
          // keep-alive + HOCs. (#4590)
          oldElm._leaveCb ? null : parentElm,
          nodeOps.nextSibling(oldElm)
        );

        // update parent placeholder node element, recursively
        if (isDef(vnode.parent)) {
          var ancestor = vnode.parent;
          var patchable = isPatchable(vnode);
          while (ancestor) {
            for (var i = 0; i < cbs.destroy.length; ++i) {
              cbs.destroy[i](ancestor);
            }
            ancestor.elm = vnode.elm;
            if (patchable) {
              for (var i$1 = 0; i$1 < cbs.create.length; ++i$1) {
                cbs.create[i$1](emptyNode, ancestor);
              }
              // #6513
              // invoke insert hooks that may have been merged by create hooks.
              // e.g. for directives that uses the "inserted" hook.
              var insert = ancestor.data.hook.insert;
              if (insert.merged) {
                // start at index 1 to avoid re-invoking component mounted hook
                for (var i$2 = 1; i$2 < insert.fns.length; i$2++) {
                  insert.fns[i$2]();
                }
              }
            } else {
              registerRef(ancestor);
            }
            ancestor = ancestor.parent;
          }
        }

        // destroy old node
        if (isDef(parentElm)) {
          removeVnodes(parentElm, [oldVnode], 0, 0);
        } else if (isDef(oldVnode.tag)) {
          invokeDestroyHook(oldVnode);
        }
      }
    }

    invokeInsertHook(vnode, insertedVnodeQueue, isInitialPatch);
    return vnode.elm
  }
}

/*  */

var directives = {
  create: updateDirectives,
  update: updateDirectives,
  destroy: function unbindDirectives (vnode) {
    updateDirectives(vnode, emptyNode);
  }
};

function updateDirectives (oldVnode, vnode) {
  if (oldVnode.data.directives || vnode.data.directives) {
    _update(oldVnode, vnode);
  }
}

function _update (oldVnode, vnode) {
  var isCreate = oldVnode === emptyNode;
  var isDestroy = vnode === emptyNode;
  var oldDirs = normalizeDirectives$1(oldVnode.data.directives, oldVnode.context);
  var newDirs = normalizeDirectives$1(vnode.data.directives, vnode.context);

  var dirsWithInsert = [];
  var dirsWithPostpatch = [];

  var key, oldDir, dir;
  for (key in newDirs) {
    oldDir = oldDirs[key];
    dir = newDirs[key];
    if (!oldDir) {
      // new directive, bind
      callHook$1(dir, 'bind', vnode, oldVnode);
      if (dir.def && dir.def.inserted) {
        dirsWithInsert.push(dir);
      }
    } else {
      // existing directive, update
      dir.oldValue = oldDir.value;
      dir.oldArg = oldDir.arg;
      callHook$1(dir, 'update', vnode, oldVnode);
      if (dir.def && dir.def.componentUpdated) {
        dirsWithPostpatch.push(dir);
      }
    }
  }

  if (dirsWithInsert.length) {
    var callInsert = function () {
      for (var i = 0; i < dirsWithInsert.length; i++) {
        callHook$1(dirsWithInsert[i], 'inserted', vnode, oldVnode);
      }
    };
    if (isCreate) {
      mergeVNodeHook(vnode, 'insert', callInsert);
    } else {
      callInsert();
    }
  }

  if (dirsWithPostpatch.length) {
    mergeVNodeHook(vnode, 'postpatch', function () {
      for (var i = 0; i < dirsWithPostpatch.length; i++) {
        callHook$1(dirsWithPostpatch[i], 'componentUpdated', vnode, oldVnode);
      }
    });
  }

  if (!isCreate) {
    for (key in oldDirs) {
      if (!newDirs[key]) {
        // no longer present, unbind
        callHook$1(oldDirs[key], 'unbind', oldVnode, oldVnode, isDestroy);
      }
    }
  }
}

var emptyModifiers = Object.create(null);

function normalizeDirectives$1 (
  dirs,
  vm
) {
  var res = Object.create(null);
  if (!dirs) {
    // $flow-disable-line
    return res
  }
  var i, dir;
  for (i = 0; i < dirs.length; i++) {
    dir = dirs[i];
    if (!dir.modifiers) {
      // $flow-disable-line
      dir.modifiers = emptyModifiers;
    }
    res[getRawDirName(dir)] = dir;
    dir.def = resolveAsset(vm.$options, 'directives', dir.name, true);
  }
  // $flow-disable-line
  return res
}

function getRawDirName (dir) {
  return dir.rawName || ((dir.name) + "." + (Object.keys(dir.modifiers || {}).join('.')))
}

function callHook$1 (dir, hook, vnode, oldVnode, isDestroy) {
  var fn = dir.def && dir.def[hook];
  if (fn) {
    try {
      fn(vnode.elm, dir, vnode, oldVnode, isDestroy);
    } catch (e) {
      handleError(e, vnode.context, ("directive " + (dir.name) + " " + hook + " hook"));
    }
  }
}

var baseModules = [
  ref,
  directives
];

/*  */

function updateAttrs (oldVnode, vnode) {
  var opts = vnode.componentOptions;
  if (isDef(opts) && opts.Ctor.options.inheritAttrs === false) {
    return
  }
  if (isUndef(oldVnode.data.attrs) && isUndef(vnode.data.attrs)) {
    return
  }
  var key, cur, old;
  var elm = vnode.elm;
  var oldAttrs = oldVnode.data.attrs || {};
  var attrs = vnode.data.attrs || {};
  // clone observed objects, as the user probably wants to mutate it
  if (isDef(attrs.__ob__)) {
    attrs = vnode.data.attrs = extend({}, attrs);
  }

  for (key in attrs) {
    cur = attrs[key];
    old = oldAttrs[key];
    if (old !== cur) {
      setAttr(elm, key, cur);
    }
  }
  // #4391: in IE9, setting type can reset value for input[type=radio]
  // #6666: IE/Edge forces progress value down to 1 before setting a max
  /* istanbul ignore if */
  if ((isIE || isEdge) && attrs.value !== oldAttrs.value) {
    setAttr(elm, 'value', attrs.value);
  }
  for (key in oldAttrs) {
    if (isUndef(attrs[key])) {
      if (isXlink(key)) {
        elm.removeAttributeNS(xlinkNS, getXlinkProp(key));
      } else if (!isEnumeratedAttr(key)) {
        elm.removeAttribute(key);
      }
    }
  }
}

function setAttr (el, key, value) {
  if (el.tagName.indexOf('-') > -1) {
    baseSetAttr(el, key, value);
  } else if (isBooleanAttr(key)) {
    // set attribute for blank value
    // e.g. <option disabled>Select one</option>
    if (isFalsyAttrValue(value)) {
      el.removeAttribute(key);
    } else {
      // technically allowfullscreen is a boolean attribute for <iframe>,
      // but Flash expects a value of "true" when used on <embed> tag
      value = key === 'allowfullscreen' && el.tagName === 'EMBED'
        ? 'true'
        : key;
      el.setAttribute(key, value);
    }
  } else if (isEnumeratedAttr(key)) {
    el.setAttribute(key, convertEnumeratedValue(key, value));
  } else if (isXlink(key)) {
    if (isFalsyAttrValue(value)) {
      el.removeAttributeNS(xlinkNS, getXlinkProp(key));
    } else {
      el.setAttributeNS(xlinkNS, key, value);
    }
  } else {
    baseSetAttr(el, key, value);
  }
}

function baseSetAttr (el, key, value) {
  if (isFalsyAttrValue(value)) {
    el.removeAttribute(key);
  } else {
    // #7138: IE10 & 11 fires input event when setting placeholder on
    // <textarea>... block the first input event and remove the blocker
    // immediately.
    /* istanbul ignore if */
    if (
      isIE && !isIE9 &&
      el.tagName === 'TEXTAREA' &&
      key === 'placeholder' && value !== '' && !el.__ieph
    ) {
      var blocker = function (e) {
        e.stopImmediatePropagation();
        el.removeEventListener('input', blocker);
      };
      el.addEventListener('input', blocker);
      // $flow-disable-line
      el.__ieph = true; /* IE placeholder patched */
    }
    el.setAttribute(key, value);
  }
}

var attrs = {
  create: updateAttrs,
  update: updateAttrs
};

/*  */

function updateClass (oldVnode, vnode) {
  var el = vnode.elm;
  var data = vnode.data;
  var oldData = oldVnode.data;
  if (
    isUndef(data.staticClass) &&
    isUndef(data.class) && (
      isUndef(oldData) || (
        isUndef(oldData.staticClass) &&
        isUndef(oldData.class)
      )
    )
  ) {
    return
  }

  var cls = genClassForVnode(vnode);

  // handle transition classes
  var transitionClass = el._transitionClasses;
  if (isDef(transitionClass)) {
    cls = concat(cls, stringifyClass(transitionClass));
  }

  // set the class
  if (cls !== el._prevClass) {
    el.setAttribute('class', cls);
    el._prevClass = cls;
  }
}

var klass = {
  create: updateClass,
  update: updateClass
};

/*  */

/*  */

/*  */

/*  */

// in some cases, the event used has to be determined at runtime
// so we used some reserved tokens during compile.
var RANGE_TOKEN = '__r';
var CHECKBOX_RADIO_TOKEN = '__c';

/*  */

// normalize v-model event tokens that can only be determined at runtime.
// it's important to place the event as the first in the array because
// the whole point is ensuring the v-model callback gets called before
// user-attached handlers.
function normalizeEvents (on) {
  /* istanbul ignore if */
  if (isDef(on[RANGE_TOKEN])) {
    // IE input[type=range] only supports `change` event
    var event = isIE ? 'change' : 'input';
    on[event] = [].concat(on[RANGE_TOKEN], on[event] || []);
    delete on[RANGE_TOKEN];
  }
  // This was originally intended to fix #4521 but no longer necessary
  // after 2.5. Keeping it for backwards compat with generated code from < 2.4
  /* istanbul ignore if */
  if (isDef(on[CHECKBOX_RADIO_TOKEN])) {
    on.change = [].concat(on[CHECKBOX_RADIO_TOKEN], on.change || []);
    delete on[CHECKBOX_RADIO_TOKEN];
  }
}

var target$1;

function createOnceHandler$1 (event, handler, capture) {
  var _target = target$1; // save current target element in closure
  return function onceHandler () {
    var res = handler.apply(null, arguments);
    if (res !== null) {
      remove$2(event, onceHandler, capture, _target);
    }
  }
}

// #9446: Firefox <= 53 (in particular, ESR 52) has incorrect Event.timeStamp
// implementation and does not fire microtasks in between event propagation, so
// safe to exclude.
var useMicrotaskFix = isUsingMicroTask && !(isFF && Number(isFF[1]) <= 53);

function add$1 (
  name,
  handler,
  capture,
  passive
) {
  // async edge case #6566: inner click event triggers patch, event handler
  // attached to outer element during patch, and triggered again. This
  // happens because browsers fire microtask ticks between event propagation.
  // the solution is simple: we save the timestamp when a handler is attached,
  // and the handler would only fire if the event passed to it was fired
  // AFTER it was attached.
  if (useMicrotaskFix) {
    var attachedTimestamp = currentFlushTimestamp;
    var original = handler;
    handler = original._wrapper = function (e) {
      if (
        // no bubbling, should always fire.
        // this is just a safety net in case event.timeStamp is unreliable in
        // certain weird environments...
        e.target === e.currentTarget ||
        // event is fired after handler attachment
        e.timeStamp >= attachedTimestamp ||
        // bail for environments that have buggy event.timeStamp implementations
        // #9462 iOS 9 bug: event.timeStamp is 0 after history.pushState
        // #9681 QtWebEngine event.timeStamp is negative value
        e.timeStamp <= 0 ||
        // #9448 bail if event is fired in another document in a multi-page
        // electron/nw.js app, since event.timeStamp will be using a different
        // starting reference
        e.target.ownerDocument !== document
      ) {
        return original.apply(this, arguments)
      }
    };
  }
  target$1.addEventListener(
    name,
    handler,
    supportsPassive
      ? { capture: capture, passive: passive }
      : capture
  );
}

function remove$2 (
  name,
  handler,
  capture,
  _target
) {
  (_target || target$1).removeEventListener(
    name,
    handler._wrapper || handler,
    capture
  );
}

function updateDOMListeners (oldVnode, vnode) {
  if (isUndef(oldVnode.data.on) && isUndef(vnode.data.on)) {
    return
  }
  var on = vnode.data.on || {};
  var oldOn = oldVnode.data.on || {};
  target$1 = vnode.elm;
  normalizeEvents(on);
  updateListeners(on, oldOn, add$1, remove$2, createOnceHandler$1, vnode.context);
  target$1 = undefined;
}

var events = {
  create: updateDOMListeners,
  update: updateDOMListeners
};

/*  */

var svgContainer;

function updateDOMProps (oldVnode, vnode) {
  if (isUndef(oldVnode.data.domProps) && isUndef(vnode.data.domProps)) {
    return
  }
  var key, cur;
  var elm = vnode.elm;
  var oldProps = oldVnode.data.domProps || {};
  var props = vnode.data.domProps || {};
  // clone observed objects, as the user probably wants to mutate it
  if (isDef(props.__ob__)) {
    props = vnode.data.domProps = extend({}, props);
  }

  for (key in oldProps) {
    if (!(key in props)) {
      elm[key] = '';
    }
  }

  for (key in props) {
    cur = props[key];
    // ignore children if the node has textContent or innerHTML,
    // as these will throw away existing DOM nodes and cause removal errors
    // on subsequent patches (#3360)
    if (key === 'textContent' || key === 'innerHTML') {
      if (vnode.children) { vnode.children.length = 0; }
      if (cur === oldProps[key]) { continue }
      // #6601 work around Chrome version <= 55 bug where single textNode
      // replaced by innerHTML/textContent retains its parentNode property
      if (elm.childNodes.length === 1) {
        elm.removeChild(elm.childNodes[0]);
      }
    }

    if (key === 'value' && elm.tagName !== 'PROGRESS') {
      // store value as _value as well since
      // non-string values will be stringified
      elm._value = cur;
      // avoid resetting cursor position when value is the same
      var strCur = isUndef(cur) ? '' : String(cur);
      if (shouldUpdateValue(elm, strCur)) {
        elm.value = strCur;
      }
    } else if (key === 'innerHTML' && isSVG(elm.tagName) && isUndef(elm.innerHTML)) {
      // IE doesn't support innerHTML for SVG elements
      svgContainer = svgContainer || document.createElement('div');
      svgContainer.innerHTML = "<svg>" + cur + "</svg>";
      var svg = svgContainer.firstChild;
      while (elm.firstChild) {
        elm.removeChild(elm.firstChild);
      }
      while (svg.firstChild) {
        elm.appendChild(svg.firstChild);
      }
    } else if (
      // skip the update if old and new VDOM state is the same.
      // `value` is handled separately because the DOM value may be temporarily
      // out of sync with VDOM state due to focus, composition and modifiers.
      // This  #4521 by skipping the unnecesarry `checked` update.
      cur !== oldProps[key]
    ) {
      // some property updates can throw
      // e.g. `value` on <progress> w/ non-finite value
      try {
        elm[key] = cur;
      } catch (e) {}
    }
  }
}

// check platforms/web/util/attrs.js acceptValue


function shouldUpdateValue (elm, checkVal) {
  return (!elm.composing && (
    elm.tagName === 'OPTION' ||
    isNotInFocusAndDirty(elm, checkVal) ||
    isDirtyWithModifiers(elm, checkVal)
  ))
}

function isNotInFocusAndDirty (elm, checkVal) {
  // return true when textbox (.number and .trim) loses focus and its value is
  // not equal to the updated value
  var notInFocus = true;
  // #6157
  // work around IE bug when accessing document.activeElement in an iframe
  try { notInFocus = document.activeElement !== elm; } catch (e) {}
  return notInFocus && elm.value !== checkVal
}

function isDirtyWithModifiers (elm, newVal) {
  var value = elm.value;
  var modifiers = elm._vModifiers; // injected by v-model runtime
  if (isDef(modifiers)) {
    if (modifiers.number) {
      return toNumber(value) !== toNumber(newVal)
    }
    if (modifiers.trim) {
      return value.trim() !== newVal.trim()
    }
  }
  return value !== newVal
}

var domProps = {
  create: updateDOMProps,
  update: updateDOMProps
};

/*  */

var parseStyleText = cached(function (cssText) {
  var res = {};
  var listDelimiter = /;(?![^(]*\))/g;
  var propertyDelimiter = /:(.+)/;
  cssText.split(listDelimiter).forEach(function (item) {
    if (item) {
      var tmp = item.split(propertyDelimiter);
      tmp.length > 1 && (res[tmp[0].trim()] = tmp[1].trim());
    }
  });
  return res
});

// merge static and dynamic style data on the same vnode
function normalizeStyleData (data) {
  var style = normalizeStyleBinding(data.style);
  // static style is pre-processed into an object during compilation
  // and is always a fresh object, so it's safe to merge into it
  return data.staticStyle
    ? extend(data.staticStyle, style)
    : style
}

// normalize possible array / string values into Object
function normalizeStyleBinding (bindingStyle) {
  if (Array.isArray(bindingStyle)) {
    return toObject(bindingStyle)
  }
  if (typeof bindingStyle === 'string') {
    return parseStyleText(bindingStyle)
  }
  return bindingStyle
}

/**
 * parent component style should be after child's
 * so that parent component's style could override it
 */
function getStyle (vnode, checkChild) {
  var res = {};
  var styleData;

  if (checkChild) {
    var childNode = vnode;
    while (childNode.componentInstance) {
      childNode = childNode.componentInstance._vnode;
      if (
        childNode && childNode.data &&
        (styleData = normalizeStyleData(childNode.data))
      ) {
        extend(res, styleData);
      }
    }
  }

  if ((styleData = normalizeStyleData(vnode.data))) {
    extend(res, styleData);
  }

  var parentNode = vnode;
  while ((parentNode = parentNode.parent)) {
    if (parentNode.data && (styleData = normalizeStyleData(parentNode.data))) {
      extend(res, styleData);
    }
  }
  return res
}

/*  */

var cssVarRE = /^--/;
var importantRE = /\s*!important$/;
var setProp = function (el, name, val) {
  /* istanbul ignore if */
  if (cssVarRE.test(name)) {
    el.style.setProperty(name, val);
  } else if (importantRE.test(val)) {
    el.style.setProperty(hyphenate(name), val.replace(importantRE, ''), 'important');
  } else {
    var normalizedName = normalize(name);
    if (Array.isArray(val)) {
      // Support values array created by autoprefixer, e.g.
      // {display: ["-webkit-box", "-ms-flexbox", "flex"]}
      // Set them one by one, and the browser will only set those it can recognize
      for (var i = 0, len = val.length; i < len; i++) {
        el.style[normalizedName] = val[i];
      }
    } else {
      el.style[normalizedName] = val;
    }
  }
};

var vendorNames = ['Webkit', 'Moz', 'ms'];

var emptyStyle;
var normalize = cached(function (prop) {
  emptyStyle = emptyStyle || document.createElement('div').style;
  prop = camelize(prop);
  if (prop !== 'filter' && (prop in emptyStyle)) {
    return prop
  }
  var capName = prop.charAt(0).toUpperCase() + prop.slice(1);
  for (var i = 0; i < vendorNames.length; i++) {
    var name = vendorNames[i] + capName;
    if (name in emptyStyle) {
      return name
    }
  }
});

function updateStyle (oldVnode, vnode) {
  var data = vnode.data;
  var oldData = oldVnode.data;

  if (isUndef(data.staticStyle) && isUndef(data.style) &&
    isUndef(oldData.staticStyle) && isUndef(oldData.style)
  ) {
    return
  }

  var cur, name;
  var el = vnode.elm;
  var oldStaticStyle = oldData.staticStyle;
  var oldStyleBinding = oldData.normalizedStyle || oldData.style || {};

  // if static style exists, stylebinding already merged into it when doing normalizeStyleData
  var oldStyle = oldStaticStyle || oldStyleBinding;

  var style = normalizeStyleBinding(vnode.data.style) || {};

  // store normalized style under a different key for next diff
  // make sure to clone it if it's reactive, since the user likely wants
  // to mutate it.
  vnode.data.normalizedStyle = isDef(style.__ob__)
    ? extend({}, style)
    : style;

  var newStyle = getStyle(vnode, true);

  for (name in oldStyle) {
    if (isUndef(newStyle[name])) {
      setProp(el, name, '');
    }
  }
  for (name in newStyle) {
    cur = newStyle[name];
    if (cur !== oldStyle[name]) {
      // ie9 setting to null has no effect, must use empty string
      setProp(el, name, cur == null ? '' : cur);
    }
  }
}

var style = {
  create: updateStyle,
  update: updateStyle
};

/*  */

var whitespaceRE = /\s+/;

/**
 * Add class with compatibility for SVG since classList is not supported on
 * SVG elements in IE
 */
function addClass (el, cls) {
  /* istanbul ignore if */
  if (!cls || !(cls = cls.trim())) {
    return
  }

  /* istanbul ignore else */
  if (el.classList) {
    if (cls.indexOf(' ') > -1) {
      cls.split(whitespaceRE).forEach(function (c) { return el.classList.add(c); });
    } else {
      el.classList.add(cls);
    }
  } else {
    var cur = " " + (el.getAttribute('class') || '') + " ";
    if (cur.indexOf(' ' + cls + ' ') < 0) {
      el.setAttribute('class', (cur + cls).trim());
    }
  }
}

/**
 * Remove class with compatibility for SVG since classList is not supported on
 * SVG elements in IE
 */
function removeClass (el, cls) {
  /* istanbul ignore if */
  if (!cls || !(cls = cls.trim())) {
    return
  }

  /* istanbul ignore else */
  if (el.classList) {
    if (cls.indexOf(' ') > -1) {
      cls.split(whitespaceRE).forEach(function (c) { return el.classList.remove(c); });
    } else {
      el.classList.remove(cls);
    }
    if (!el.classList.length) {
      el.removeAttribute('class');
    }
  } else {
    var cur = " " + (el.getAttribute('class') || '') + " ";
    var tar = ' ' + cls + ' ';
    while (cur.indexOf(tar) >= 0) {
      cur = cur.replace(tar, ' ');
    }
    cur = cur.trim();
    if (cur) {
      el.setAttribute('class', cur);
    } else {
      el.removeAttribute('class');
    }
  }
}

/*  */

function resolveTransition (def$$1) {
  if (!def$$1) {
    return
  }
  /* istanbul ignore else */
  if (typeof def$$1 === 'object') {
    var res = {};
    if (def$$1.css !== false) {
      extend(res, autoCssTransition(def$$1.name || 'v'));
    }
    extend(res, def$$1);
    return res
  } else if (typeof def$$1 === 'string') {
    return autoCssTransition(def$$1)
  }
}

var autoCssTransition = cached(function (name) {
  return {
    enterClass: (name + "-enter"),
    enterToClass: (name + "-enter-to"),
    enterActiveClass: (name + "-enter-active"),
    leaveClass: (name + "-leave"),
    leaveToClass: (name + "-leave-to"),
    leaveActiveClass: (name + "-leave-active")
  }
});

var hasTransition = inBrowser && !isIE9;
var TRANSITION = 'transition';
var ANIMATION = 'animation';

// Transition property/event sniffing
var transitionProp = 'transition';
var transitionEndEvent = 'transitionend';
var animationProp = 'animation';
var animationEndEvent = 'animationend';
if (hasTransition) {
  /* istanbul ignore if */
  if (window.ontransitionend === undefined &&
    window.onwebkittransitionend !== undefined
  ) {
    transitionProp = 'WebkitTransition';
    transitionEndEvent = 'webkitTransitionEnd';
  }
  if (window.onanimationend === undefined &&
    window.onwebkitanimationend !== undefined
  ) {
    animationProp = 'WebkitAnimation';
    animationEndEvent = 'webkitAnimationEnd';
  }
}

// binding to window is necessary to make hot reload work in IE in strict mode
var raf = inBrowser
  ? window.requestAnimationFrame
    ? window.requestAnimationFrame.bind(window)
    : setTimeout
  : /* istanbul ignore next */ function (fn) { return fn(); };

function nextFrame (fn) {
  raf(function () {
    raf(fn);
  });
}

function addTransitionClass (el, cls) {
  var transitionClasses = el._transitionClasses || (el._transitionClasses = []);
  if (transitionClasses.indexOf(cls) < 0) {
    transitionClasses.push(cls);
    addClass(el, cls);
  }
}

function removeTransitionClass (el, cls) {
  if (el._transitionClasses) {
    remove(el._transitionClasses, cls);
  }
  removeClass(el, cls);
}

function whenTransitionEnds (
  el,
  expectedType,
  cb
) {
  var ref = getTransitionInfo(el, expectedType);
  var type = ref.type;
  var timeout = ref.timeout;
  var propCount = ref.propCount;
  if (!type) { return cb() }
  var event = type === TRANSITION ? transitionEndEvent : animationEndEvent;
  var ended = 0;
  var end = function () {
    el.removeEventListener(event, onEnd);
    cb();
  };
  var onEnd = function (e) {
    if (e.target === el) {
      if (++ended >= propCount) {
        end();
      }
    }
  };
  setTimeout(function () {
    if (ended < propCount) {
      end();
    }
  }, timeout + 1);
  el.addEventListener(event, onEnd);
}

var transformRE = /\b(transform|all)(,|$)/;

function getTransitionInfo (el, expectedType) {
  var styles = window.getComputedStyle(el);
  // JSDOM may return undefined for transition properties
  var transitionDelays = (styles[transitionProp + 'Delay'] || '').split(', ');
  var transitionDurations = (styles[transitionProp + 'Duration'] || '').split(', ');
  var transitionTimeout = getTimeout(transitionDelays, transitionDurations);
  var animationDelays = (styles[animationProp + 'Delay'] || '').split(', ');
  var animationDurations = (styles[animationProp + 'Duration'] || '').split(', ');
  var animationTimeout = getTimeout(animationDelays, animationDurations);

  var type;
  var timeout = 0;
  var propCount = 0;
  /* istanbul ignore if */
  if (expectedType === TRANSITION) {
    if (transitionTimeout > 0) {
      type = TRANSITION;
      timeout = transitionTimeout;
      propCount = transitionDurations.length;
    }
  } else if (expectedType === ANIMATION) {
    if (animationTimeout > 0) {
      type = ANIMATION;
      timeout = animationTimeout;
      propCount = animationDurations.length;
    }
  } else {
    timeout = Math.max(transitionTimeout, animationTimeout);
    type = timeout > 0
      ? transitionTimeout > animationTimeout
        ? TRANSITION
        : ANIMATION
      : null;
    propCount = type
      ? type === TRANSITION
        ? transitionDurations.length
        : animationDurations.length
      : 0;
  }
  var hasTransform =
    type === TRANSITION &&
    transformRE.test(styles[transitionProp + 'Property']);
  return {
    type: type,
    timeout: timeout,
    propCount: propCount,
    hasTransform: hasTransform
  }
}

function getTimeout (delays, durations) {
  /* istanbul ignore next */
  while (delays.length < durations.length) {
    delays = delays.concat(delays);
  }

  return Math.max.apply(null, durations.map(function (d, i) {
    return toMs(d) + toMs(delays[i])
  }))
}

// Old versions of Chromium (below 61.0.3163.100) formats floating pointer numbers
// in a locale-dependent way, using a comma instead of a dot.
// If comma is not replaced with a dot, the input will be rounded down (i.e. acting
// as a floor function) causing unexpected behaviors
function toMs (s) {
  return Number(s.slice(0, -1).replace(',', '.')) * 1000
}

/*  */

function enter (vnode, toggleDisplay) {
  var el = vnode.elm;

  // call leave callback now
  if (isDef(el._leaveCb)) {
    el._leaveCb.cancelled = true;
    el._leaveCb();
  }

  var data = resolveTransition(vnode.data.transition);
  if (isUndef(data)) {
    return
  }

  /* istanbul ignore if */
  if (isDef(el._enterCb) || el.nodeType !== 1) {
    return
  }

  var css = data.css;
  var type = data.type;
  var enterClass = data.enterClass;
  var enterToClass = data.enterToClass;
  var enterActiveClass = data.enterActiveClass;
  var appearClass = data.appearClass;
  var appearToClass = data.appearToClass;
  var appearActiveClass = data.appearActiveClass;
  var beforeEnter = data.beforeEnter;
  var enter = data.enter;
  var afterEnter = data.afterEnter;
  var enterCancelled = data.enterCancelled;
  var beforeAppear = data.beforeAppear;
  var appear = data.appear;
  var afterAppear = data.afterAppear;
  var appearCancelled = data.appearCancelled;
  var duration = data.duration;

  // activeInstance will always be the <transition> component managing this
  // transition. One edge case to check is when the <transition> is placed
  // as the root node of a child component. In that case we need to check
  // <transition>'s parent for appear check.
  var context = activeInstance;
  var transitionNode = activeInstance.$vnode;
  while (transitionNode && transitionNode.parent) {
    context = transitionNode.context;
    transitionNode = transitionNode.parent;
  }

  var isAppear = !context._isMounted || !vnode.isRootInsert;

  if (isAppear && !appear && appear !== '') {
    return
  }

  var startClass = isAppear && appearClass
    ? appearClass
    : enterClass;
  var activeClass = isAppear && appearActiveClass
    ? appearActiveClass
    : enterActiveClass;
  var toClass = isAppear && appearToClass
    ? appearToClass
    : enterToClass;

  var beforeEnterHook = isAppear
    ? (beforeAppear || beforeEnter)
    : beforeEnter;
  var enterHook = isAppear
    ? (typeof appear === 'function' ? appear : enter)
    : enter;
  var afterEnterHook = isAppear
    ? (afterAppear || afterEnter)
    : afterEnter;
  var enterCancelledHook = isAppear
    ? (appearCancelled || enterCancelled)
    : enterCancelled;

  var explicitEnterDuration = toNumber(
    isObject(duration)
      ? duration.enter
      : duration
  );

  if (explicitEnterDuration != null) {
    checkDuration(explicitEnterDuration, 'enter', vnode);
  }

  var expectsCSS = css !== false && !isIE9;
  var userWantsControl = getHookArgumentsLength(enterHook);

  var cb = el._enterCb = once$1(function () {
    if (expectsCSS) {
      removeTransitionClass(el, toClass);
      removeTransitionClass(el, activeClass);
    }
    if (cb.cancelled) {
      if (expectsCSS) {
        removeTransitionClass(el, startClass);
      }
      enterCancelledHook && enterCancelledHook(el);
    } else {
      afterEnterHook && afterEnterHook(el);
    }
    el._enterCb = null;
  });

  if (!vnode.data.show) {
    // remove pending leave element on enter by injecting an insert hook
    mergeVNodeHook(vnode, 'insert', function () {
      var parent = el.parentNode;
      var pendingNode = parent && parent._pending && parent._pending[vnode.key];
      if (pendingNode &&
        pendingNode.tag === vnode.tag &&
        pendingNode.elm._leaveCb
      ) {
        pendingNode.elm._leaveCb();
      }
      enterHook && enterHook(el, cb);
    });
  }

  // start enter transition
  beforeEnterHook && beforeEnterHook(el);
  if (expectsCSS) {
    addTransitionClass(el, startClass);
    addTransitionClass(el, activeClass);
    nextFrame(function () {
      removeTransitionClass(el, startClass);
      if (!cb.cancelled) {
        addTransitionClass(el, toClass);
        if (!userWantsControl) {
          if (isValidDuration(explicitEnterDuration)) {
            setTimeout(cb, explicitEnterDuration);
          } else {
            whenTransitionEnds(el, type, cb);
          }
        }
      }
    });
  }

  if (vnode.data.show) {
    toggleDisplay && toggleDisplay();
    enterHook && enterHook(el, cb);
  }

  if (!expectsCSS && !userWantsControl) {
    cb();
  }
}

function leave (vnode, rm) {
  var el = vnode.elm;

  // call enter callback now
  if (isDef(el._enterCb)) {
    el._enterCb.cancelled = true;
    el._enterCb();
  }

  var data = resolveTransition(vnode.data.transition);
  if (isUndef(data) || el.nodeType !== 1) {
    return rm()
  }

  /* istanbul ignore if */
  if (isDef(el._leaveCb)) {
    return
  }

  var css = data.css;
  var type = data.type;
  var leaveClass = data.leaveClass;
  var leaveToClass = data.leaveToClass;
  var leaveActiveClass = data.leaveActiveClass;
  var beforeLeave = data.beforeLeave;
  var leave = data.leave;
  var afterLeave = data.afterLeave;
  var leaveCancelled = data.leaveCancelled;
  var delayLeave = data.delayLeave;
  var duration = data.duration;

  var expectsCSS = css !== false && !isIE9;
  var userWantsControl = getHookArgumentsLength(leave);

  var explicitLeaveDuration = toNumber(
    isObject(duration)
      ? duration.leave
      : duration
  );

  if (isDef(explicitLeaveDuration)) {
    checkDuration(explicitLeaveDuration, 'leave', vnode);
  }

  var cb = el._leaveCb = once$1(function () {
    if (el.parentNode && el.parentNode._pending) {
      el.parentNode._pending[vnode.key] = null;
    }
    if (expectsCSS) {
      removeTransitionClass(el, leaveToClass);
      removeTransitionClass(el, leaveActiveClass);
    }
    if (cb.cancelled) {
      if (expectsCSS) {
        removeTransitionClass(el, leaveClass);
      }
      leaveCancelled && leaveCancelled(el);
    } else {
      rm();
      afterLeave && afterLeave(el);
    }
    el._leaveCb = null;
  });

  if (delayLeave) {
    delayLeave(performLeave);
  } else {
    performLeave();
  }

  function performLeave () {
    // the delayed leave may have already been cancelled
    if (cb.cancelled) {
      return
    }
    // record leaving element
    if (!vnode.data.show && el.parentNode) {
      (el.parentNode._pending || (el.parentNode._pending = {}))[(vnode.key)] = vnode;
    }
    beforeLeave && beforeLeave(el);
    if (expectsCSS) {
      addTransitionClass(el, leaveClass);
      addTransitionClass(el, leaveActiveClass);
      nextFrame(function () {
        removeTransitionClass(el, leaveClass);
        if (!cb.cancelled) {
          addTransitionClass(el, leaveToClass);
          if (!userWantsControl) {
            if (isValidDuration(explicitLeaveDuration)) {
              setTimeout(cb, explicitLeaveDuration);
            } else {
              whenTransitionEnds(el, type, cb);
            }
          }
        }
      });
    }
    leave && leave(el, cb);
    if (!expectsCSS && !userWantsControl) {
      cb();
    }
  }
}

// only used in dev mode
function checkDuration (val, name, vnode) {
  if (typeof val !== 'number') {
    warn(
      "<transition> explicit " + name + " duration is not a valid number - " +
      "got " + (JSON.stringify(val)) + ".",
      vnode.context
    );
  } else if (isNaN(val)) {
    warn(
      "<transition> explicit " + name + " duration is NaN - " +
      'the duration expression might be incorrect.',
      vnode.context
    );
  }
}

function isValidDuration (val) {
  return typeof val === 'number' && !isNaN(val)
}

/**
 * Normalize a transition hook's argument length. The hook may be:
 * - a merged hook (invoker) with the original in .fns
 * - a wrapped component method (check ._length)
 * - a plain function (.length)
 */
function getHookArgumentsLength (fn) {
  if (isUndef(fn)) {
    return false
  }
  var invokerFns = fn.fns;
  if (isDef(invokerFns)) {
    // invoker
    return getHookArgumentsLength(
      Array.isArray(invokerFns)
        ? invokerFns[0]
        : invokerFns
    )
  } else {
    return (fn._length || fn.length) > 1
  }
}

function _enter (_, vnode) {
  if (vnode.data.show !== true) {
    enter(vnode);
  }
}

var transition = inBrowser ? {
  create: _enter,
  activate: _enter,
  remove: function remove$$1 (vnode, rm) {
    /* istanbul ignore else */
    if (vnode.data.show !== true) {
      leave(vnode, rm);
    } else {
      rm();
    }
  }
} : {};

var platformModules = [
  attrs,
  klass,
  events,
  domProps,
  style,
  transition
];

/*  */

// the directive module should be applied last, after all
// built-in modules have been applied.
var modules = platformModules.concat(baseModules);

var patch = createPatchFunction({ nodeOps: nodeOps, modules: modules });

/**
 * Not type checking this file because flow doesn't like attaching
 * properties to Elements.
 */

/* istanbul ignore if */
if (isIE9) {
  // http://www.matts411.com/post/internet-explorer-9-oninput/
  document.addEventListener('selectionchange', function () {
    var el = document.activeElement;
    if (el && el.vmodel) {
      trigger(el, 'input');
    }
  });
}

var directive = {
  inserted: function inserted (el, binding, vnode, oldVnode) {
    if (vnode.tag === 'select') {
      // #6903
      if (oldVnode.elm && !oldVnode.elm._vOptions) {
        mergeVNodeHook(vnode, 'postpatch', function () {
          directive.componentUpdated(el, binding, vnode);
        });
      } else {
        setSelected(el, binding, vnode.context);
      }
      el._vOptions = [].map.call(el.options, getValue);
    } else if (vnode.tag === 'textarea' || isTextInputType(el.type)) {
      el._vModifiers = binding.modifiers;
      if (!binding.modifiers.lazy) {
        el.addEventListener('compositionstart', onCompositionStart);
        el.addEventListener('compositionend', onCompositionEnd);
        // Safari < 10.2 & UIWebView doesn't fire compositionend when
        // switching focus before confirming composition choice
        // this also fixes the issue where some browsers e.g. iOS Chrome
        // fires "change" instead of "input" on autocomplete.
        el.addEventListener('change', onCompositionEnd);
        /* istanbul ignore if */
        if (isIE9) {
          el.vmodel = true;
        }
      }
    }
  },

  componentUpdated: function componentUpdated (el, binding, vnode) {
    if (vnode.tag === 'select') {
      setSelected(el, binding, vnode.context);
      // in case the options rendered by v-for have changed,
      // it's possible that the value is out-of-sync with the rendered options.
      // detect such cases and filter out values that no longer has a matching
      // option in the DOM.
      var prevOptions = el._vOptions;
      var curOptions = el._vOptions = [].map.call(el.options, getValue);
      if (curOptions.some(function (o, i) { return !looseEqual(o, prevOptions[i]); })) {
        // trigger change event if
        // no matching option found for at least one value
        var needReset = el.multiple
          ? binding.value.some(function (v) { return hasNoMatchingOption(v, curOptions); })
          : binding.value !== binding.oldValue && hasNoMatchingOption(binding.value, curOptions);
        if (needReset) {
          trigger(el, 'change');
        }
      }
    }
  }
};

function setSelected (el, binding, vm) {
  actuallySetSelected(el, binding, vm);
  /* istanbul ignore if */
  if (isIE || isEdge) {
    setTimeout(function () {
      actuallySetSelected(el, binding, vm);
    }, 0);
  }
}

function actuallySetSelected (el, binding, vm) {
  var value = binding.value;
  var isMultiple = el.multiple;
  if (isMultiple && !Array.isArray(value)) {
    warn(
      "<select multiple v-model=\"" + (binding.expression) + "\"> " +
      "expects an Array value for its binding, but got " + (Object.prototype.toString.call(value).slice(8, -1)),
      vm
    );
    return
  }
  var selected, option;
  for (var i = 0, l = el.options.length; i < l; i++) {
    option = el.options[i];
    if (isMultiple) {
      selected = looseIndexOf(value, getValue(option)) > -1;
      if (option.selected !== selected) {
        option.selected = selected;
      }
    } else {
      if (looseEqual(getValue(option), value)) {
        if (el.selectedIndex !== i) {
          el.selectedIndex = i;
        }
        return
      }
    }
  }
  if (!isMultiple) {
    el.selectedIndex = -1;
  }
}

function hasNoMatchingOption (value, options) {
  return options.every(function (o) { return !looseEqual(o, value); })
}

function getValue (option) {
  return '_value' in option
    ? option._value
    : option.value
}

function onCompositionStart (e) {
  e.target.composing = true;
}

function onCompositionEnd (e) {
  // prevent triggering an input event for no reason
  if (!e.target.composing) { return }
  e.target.composing = false;
  trigger(e.target, 'input');
}

function trigger (el, type) {
  var e = document.createEvent('HTMLEvents');
  e.initEvent(type, true, true);
  el.dispatchEvent(e);
}

/*  */

// recursively search for possible transition defined inside the component root
function locateNode (vnode) {
  return vnode.componentInstance && (!vnode.data || !vnode.data.transition)
    ? locateNode(vnode.componentInstance._vnode)
    : vnode
}

var show = {
  bind: function bind (el, ref, vnode) {
    var value = ref.value;

    vnode = locateNode(vnode);
    var transition$$1 = vnode.data && vnode.data.transition;
    var originalDisplay = el.__vOriginalDisplay =
      el.style.display === 'none' ? '' : el.style.display;
    if (value && transition$$1) {
      vnode.data.show = true;
      enter(vnode, function () {
        el.style.display = originalDisplay;
      });
    } else {
      el.style.display = value ? originalDisplay : 'none';
    }
  },

  update: function update (el, ref, vnode) {
    var value = ref.value;
    var oldValue = ref.oldValue;

    /* istanbul ignore if */
    if (!value === !oldValue) { return }
    vnode = locateNode(vnode);
    var transition$$1 = vnode.data && vnode.data.transition;
    if (transition$$1) {
      vnode.data.show = true;
      if (value) {
        enter(vnode, function () {
          el.style.display = el.__vOriginalDisplay;
        });
      } else {
        leave(vnode, function () {
          el.style.display = 'none';
        });
      }
    } else {
      el.style.display = value ? el.__vOriginalDisplay : 'none';
    }
  },

  unbind: function unbind (
    el,
    binding,
    vnode,
    oldVnode,
    isDestroy
  ) {
    if (!isDestroy) {
      el.style.display = el.__vOriginalDisplay;
    }
  }
};

var platformDirectives = {
  model: directive,
  show: show
};

/*  */

var transitionProps = {
  name: String,
  appear: Boolean,
  css: Boolean,
  mode: String,
  type: String,
  enterClass: String,
  leaveClass: String,
  enterToClass: String,
  leaveToClass: String,
  enterActiveClass: String,
  leaveActiveClass: String,
  appearClass: String,
  appearActiveClass: String,
  appearToClass: String,
  duration: [Number, String, Object]
};

// in case the child is also an abstract component, e.g. <keep-alive>
// we want to recursively retrieve the real component to be rendered
function getRealChild (vnode) {
  var compOptions = vnode && vnode.componentOptions;
  if (compOptions && compOptions.Ctor.options.abstract) {
    return getRealChild(getFirstComponentChild(compOptions.children))
  } else {
    return vnode
  }
}

function extractTransitionData (comp) {
  var data = {};
  var options = comp.$options;
  // props
  for (var key in options.propsData) {
    data[key] = comp[key];
  }
  // events.
  // extract listeners and pass them directly to the transition methods
  var listeners = options._parentListeners;
  for (var key$1 in listeners) {
    data[camelize(key$1)] = listeners[key$1];
  }
  return data
}

function placeholder (h, rawChild) {
  if (/\d-keep-alive$/.test(rawChild.tag)) {
    return h('keep-alive', {
      props: rawChild.componentOptions.propsData
    })
  }
}

function hasParentTransition (vnode) {
  while ((vnode = vnode.parent)) {
    if (vnode.data.transition) {
      return true
    }
  }
}

function isSameChild (child, oldChild) {
  return oldChild.key === child.key && oldChild.tag === child.tag
}

var isNotTextNode = function (c) { return c.tag || isAsyncPlaceholder(c); };

var isVShowDirective = function (d) { return d.name === 'show'; };

var Transition = {
  name: 'transition',
  props: transitionProps,
  abstract: true,

  render: function render (h) {
    var this$1 = this;

    var children = this.$slots.default;
    if (!children) {
      return
    }

    // filter out text nodes (possible whitespaces)
    children = children.filter(isNotTextNode);
    /* istanbul ignore if */
    if (!children.length) {
      return
    }

    // warn multiple elements
    if (children.length > 1) {
      warn(
        '<transition> can only be used on a single element. Use ' +
        '<transition-group> for lists.',
        this.$parent
      );
    }

    var mode = this.mode;

    // warn invalid mode
    if (mode && mode !== 'in-out' && mode !== 'out-in'
    ) {
      warn(
        'invalid <transition> mode: ' + mode,
        this.$parent
      );
    }

    var rawChild = children[0];

    // if this is a component root node and the component's
    // parent container node also has transition, skip.
    if (hasParentTransition(this.$vnode)) {
      return rawChild
    }

    // apply transition data to child
    // use getRealChild() to ignore abstract components e.g. keep-alive
    var child = getRealChild(rawChild);
    /* istanbul ignore if */
    if (!child) {
      return rawChild
    }

    if (this._leaving) {
      return placeholder(h, rawChild)
    }

    // ensure a key that is unique to the vnode type and to this transition
    // component instance. This key will be used to remove pending leaving nodes
    // during entering.
    var id = "__transition-" + (this._uid) + "-";
    child.key = child.key == null
      ? child.isComment
        ? id + 'comment'
        : id + child.tag
      : isPrimitive(child.key)
        ? (String(child.key).indexOf(id) === 0 ? child.key : id + child.key)
        : child.key;

    var data = (child.data || (child.data = {})).transition = extractTransitionData(this);
    var oldRawChild = this._vnode;
    var oldChild = getRealChild(oldRawChild);

    // mark v-show
    // so that the transition module can hand over the control to the directive
    if (child.data.directives && child.data.directives.some(isVShowDirective)) {
      child.data.show = true;
    }

    if (
      oldChild &&
      oldChild.data &&
      !isSameChild(child, oldChild) &&
      !isAsyncPlaceholder(oldChild) &&
      // #6687 component root is a comment node
      !(oldChild.componentInstance && oldChild.componentInstance._vnode.isComment)
    ) {
      // replace old child transition data with fresh one
      // important for dynamic transitions!
      var oldData = oldChild.data.transition = extend({}, data);
      // handle transition mode
      if (mode === 'out-in') {
        // return placeholder node and queue update when leave finishes
        this._leaving = true;
        mergeVNodeHook(oldData, 'afterLeave', function () {
          this$1._leaving = false;
          this$1.$forceUpdate();
        });
        return placeholder(h, rawChild)
      } else if (mode === 'in-out') {
        if (isAsyncPlaceholder(child)) {
          return oldRawChild
        }
        var delayedLeave;
        var performLeave = function () { delayedLeave(); };
        mergeVNodeHook(data, 'afterEnter', performLeave);
        mergeVNodeHook(data, 'enterCancelled', performLeave);
        mergeVNodeHook(oldData, 'delayLeave', function (leave) { delayedLeave = leave; });
      }
    }

    return rawChild
  }
};

/*  */

var props = extend({
  tag: String,
  moveClass: String
}, transitionProps);

delete props.mode;

var TransitionGroup = {
  props: props,

  beforeMount: function beforeMount () {
    var this$1 = this;

    var update = this._update;
    this._update = function (vnode, hydrating) {
      var restoreActiveInstance = setActiveInstance(this$1);
      // force removing pass
      this$1.__patch__(
        this$1._vnode,
        this$1.kept,
        false, // hydrating
        true // removeOnly (!important, avoids unnecessary moves)
      );
      this$1._vnode = this$1.kept;
      restoreActiveInstance();
      update.call(this$1, vnode, hydrating);
    };
  },

  render: function render (h) {
    var tag = this.tag || this.$vnode.data.tag || 'span';
    var map = Object.create(null);
    var prevChildren = this.prevChildren = this.children;
    var rawChildren = this.$slots.default || [];
    var children = this.children = [];
    var transitionData = extractTransitionData(this);

    for (var i = 0; i < rawChildren.length; i++) {
      var c = rawChildren[i];
      if (c.tag) {
        if (c.key != null && String(c.key).indexOf('__vlist') !== 0) {
          children.push(c);
          map[c.key] = c
          ;(c.data || (c.data = {})).transition = transitionData;
        } else {
          var opts = c.componentOptions;
          var name = opts ? (opts.Ctor.options.name || opts.tag || '') : c.tag;
          warn(("<transition-group> children must be keyed: <" + name + ">"));
        }
      }
    }

    if (prevChildren) {
      var kept = [];
      var removed = [];
      for (var i$1 = 0; i$1 < prevChildren.length; i$1++) {
        var c$1 = prevChildren[i$1];
        c$1.data.transition = transitionData;
        c$1.data.pos = c$1.elm.getBoundingClientRect();
        if (map[c$1.key]) {
          kept.push(c$1);
        } else {
          removed.push(c$1);
        }
      }
      this.kept = h(tag, null, kept);
      this.removed = removed;
    }

    return h(tag, null, children)
  },

  updated: function updated () {
    var children = this.prevChildren;
    var moveClass = this.moveClass || ((this.name || 'v') + '-move');
    if (!children.length || !this.hasMove(children[0].elm, moveClass)) {
      return
    }

    // we divide the work into three loops to avoid mixing DOM reads and writes
    // in each iteration - which helps prevent layout thrashing.
    children.forEach(callPendingCbs);
    children.forEach(recordPosition);
    children.forEach(applyTranslation);

    // force reflow to put everything in position
    // assign to this to avoid being removed in tree-shaking
    // $flow-disable-line
    this._reflow = document.body.offsetHeight;

    children.forEach(function (c) {
      if (c.data.moved) {
        var el = c.elm;
        var s = el.style;
        addTransitionClass(el, moveClass);
        s.transform = s.WebkitTransform = s.transitionDuration = '';
        el.addEventListener(transitionEndEvent, el._moveCb = function cb (e) {
          if (e && e.target !== el) {
            return
          }
          if (!e || /transform$/.test(e.propertyName)) {
            el.removeEventListener(transitionEndEvent, cb);
            el._moveCb = null;
            removeTransitionClass(el, moveClass);
          }
        });
      }
    });
  },

  methods: {
    hasMove: function hasMove (el, moveClass) {
      /* istanbul ignore if */
      if (!hasTransition) {
        return false
      }
      /* istanbul ignore if */
      if (this._hasMove) {
        return this._hasMove
      }
      // Detect whether an element with the move class applied has
      // CSS transitions. Since the element may be inside an entering
      // transition at this very moment, we make a clone of it and remove
      // all other transition classes applied to ensure only the move class
      // is applied.
      var clone = el.cloneNode();
      if (el._transitionClasses) {
        el._transitionClasses.forEach(function (cls) { removeClass(clone, cls); });
      }
      addClass(clone, moveClass);
      clone.style.display = 'none';
      this.$el.appendChild(clone);
      var info = getTransitionInfo(clone);
      this.$el.removeChild(clone);
      return (this._hasMove = info.hasTransform)
    }
  }
};

function callPendingCbs (c) {
  /* istanbul ignore if */
  if (c.elm._moveCb) {
    c.elm._moveCb();
  }
  /* istanbul ignore if */
  if (c.elm._enterCb) {
    c.elm._enterCb();
  }
}

function recordPosition (c) {
  c.data.newPos = c.elm.getBoundingClientRect();
}

function applyTranslation (c) {
  var oldPos = c.data.pos;
  var newPos = c.data.newPos;
  var dx = oldPos.left - newPos.left;
  var dy = oldPos.top - newPos.top;
  if (dx || dy) {
    c.data.moved = true;
    var s = c.elm.style;
    s.transform = s.WebkitTransform = "translate(" + dx + "px," + dy + "px)";
    s.transitionDuration = '0s';
  }
}

var platformComponents = {
  Transition: Transition,
  TransitionGroup: TransitionGroup
};

/*  */

// install platform specific utils
Vue.config.mustUseProp = mustUseProp;
Vue.config.isReservedTag = isReservedTag;
Vue.config.isReservedAttr = isReservedAttr;
Vue.config.getTagNamespace = getTagNamespace;
Vue.config.isUnknownElement = isUnknownElement;

// install platform runtime directives & components
extend(Vue.options.directives, platformDirectives);
extend(Vue.options.components, platformComponents);

// install platform patch function
Vue.prototype.__patch__ = inBrowser ? patch : noop$1;

// public mount method
Vue.prototype.$mount = function (
  el,
  hydrating
) {
  el = el && inBrowser ? query(el) : undefined;
  return mountComponent(this, el, hydrating)
};

// devtools global hook
/* istanbul ignore next */
if (inBrowser) {
  setTimeout(function () {
    if (config$1.devtools) {
      if (devtools) {
        devtools.emit('init', Vue);
      } else {
        console[console.info ? 'info' : 'log'](
          'Download the Vue Devtools extension for a better development experience:\n' +
          'https://github.com/vuejs/vue-devtools'
        );
      }
    }
    if (config$1.productionTip !== false &&
      typeof console !== 'undefined'
    ) {
      console[console.info ? 'info' : 'log'](
        "You are running Vue in development mode.\n" +
        "Make sure to turn on production mode when deploying for production.\n" +
        "See more tips at https://vuejs.org/guide/deployment.html"
      );
    }
  }, 0);
}

/* eslint-disable no-useless-escape */

const isSSR = typeof window === 'undefined';
let fromSSR = false;
let onSSR = isSSR;

function getMatch (userAgent, platformMatch) {
  const match = /(edge)\/([\w.]+)/.exec(userAgent) ||
    /(opr)[\/]([\w.]+)/.exec(userAgent) ||
    /(vivaldi)[\/]([\w.]+)/.exec(userAgent) ||
    /(chrome)[\/]([\w.]+)/.exec(userAgent) ||
    /(iemobile)[\/]([\w.]+)/.exec(userAgent) ||
    /(version)(applewebkit)[\/]([\w.]+).*(safari)[\/]([\w.]+)/.exec(userAgent) ||
    /(webkit)[\/]([\w.]+).*(version)[\/]([\w.]+).*(safari)[\/]([\w.]+)/.exec(userAgent) ||
    /(webkit)[\/]([\w.]+)/.exec(userAgent) ||
    /(opera)(?:.*version|)[\/]([\w.]+)/.exec(userAgent) ||
    /(msie) ([\w.]+)/.exec(userAgent) ||
    userAgent.indexOf('trident') >= 0 && /(rv)(?::| )([\w.]+)/.exec(userAgent) ||
    userAgent.indexOf('compatible') < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(userAgent) ||
    [];

  return {
    browser: match[5] || match[3] || match[1] || '',
    version: match[2] || match[4] || '0',
    versionNumber: match[4] || match[2] || '0',
    platform: platformMatch[0] || ''
  }
}

function getPlatformMatch (userAgent) {
  return /(ipad)/.exec(userAgent) ||
    /(ipod)/.exec(userAgent) ||
    /(windows phone)/.exec(userAgent) ||
    /(iphone)/.exec(userAgent) ||
    /(kindle)/.exec(userAgent) ||
    /(silk)/.exec(userAgent) ||
    /(android)/.exec(userAgent) ||
    /(win)/.exec(userAgent) ||
    /(mac)/.exec(userAgent) ||
    /(linux)/.exec(userAgent) ||
    /(cros)/.exec(userAgent) ||
    /(playbook)/.exec(userAgent) ||
    /(bb)/.exec(userAgent) ||
    /(blackberry)/.exec(userAgent) ||
    []
}

function getPlatform (userAgent) {
  userAgent = (userAgent || navigator.userAgent || navigator.vendor || window.opera).toLowerCase();

  const
    platformMatch = getPlatformMatch(userAgent),
    matched = getMatch(userAgent, platformMatch),
    browser = {};

  if (matched.browser) {
    browser[matched.browser] = true;
    browser.version = matched.version;
    browser.versionNumber = parseInt(matched.versionNumber, 10);
  }

  if (matched.platform) {
    browser[matched.platform] = true;
  }

  // These are all considered mobile platforms, meaning they run a mobile browser
  if (browser.android || browser.bb || browser.blackberry || browser.ipad || browser.iphone ||
    browser.ipod || browser.kindle || browser.playbook || browser.silk || browser['windows phone']) {
    browser.mobile = true;
  }

  // Set iOS if on iPod, iPad or iPhone
  if (browser.ipod || browser.ipad || browser.iphone) {
    browser.ios = true;
  }

  if (browser['windows phone']) {
    browser.winphone = true;
    delete browser['windows phone'];
  }

  // These are all considered desktop platforms, meaning they run a desktop browser
  if (browser.cros || browser.mac || browser.linux || browser.win) {
    browser.desktop = true;
  }

  // Chrome, Opera 15+, Vivaldi and Safari are webkit based browsers
  if (browser.chrome || browser.opr || browser.safari || browser.vivaldi) {
    browser.webkit = true;
  }

  // IE11 has a new token so we will assign it msie to avoid breaking changes
  if (browser.rv || browser.iemobile) {
    matched.browser = 'ie';
    browser.ie = true;
  }

  // Edge is officially known as Microsoft Edge, so rewrite the key to match
  if (browser.edge) {
    matched.browser = 'edge';
    browser.edge = true;
  }

  // Blackberry browsers are marked as Safari on BlackBerry
  if (browser.safari && browser.blackberry || browser.bb) {
    matched.browser = 'blackberry';
    browser.blackberry = true;
  }

  // Playbook browsers are marked as Safari on Playbook
  if (browser.safari && browser.playbook) {
    matched.browser = 'playbook';
    browser.playbook = true;
  }

  // Opera 15+ are identified as opr
  if (browser.opr) {
    matched.browser = 'opera';
    browser.opera = true;
  }

  // Stock Android browsers are marked as Safari on Android.
  if (browser.safari && browser.android) {
    matched.browser = 'android';
    browser.android = true;
  }

  // Kindle browsers are marked as Safari on Kindle
  if (browser.safari && browser.kindle) {
    matched.browser = 'kindle';
    browser.kindle = true;
  }

  // Kindle Silk browsers are marked as Safari on Kindle
  if (browser.safari && browser.silk) {
    matched.browser = 'silk';
    browser.silk = true;
  }

  if (browser.vivaldi) {
    matched.browser = 'vivaldi';
    browser.vivaldi = true;
  }

  // Assign the name and platform variable
  browser.name = matched.browser;
  browser.platform = matched.platform;

  if (isSSR === false) {
    if (window.process && window.process.versions && window.process.versions.electron) {
      browser.electron = true;
    }
    else if (document.location.href.indexOf('chrome-extension://') === 0) {
      browser.chromeExt = true;
    }
    else if (window._cordovaNative || window.cordova) {
      browser.cordova = true;
    }

    fromSSR = browser.cordova === void 0 &&
      browser.electron === void 0 &&
      !!document.querySelector('[data-server-rendered]');

    fromSSR === true && (onSSR = true);
  }

  return browser
}

let webStorage;

function hasWebStorage () {
  if (webStorage !== void 0) {
    return webStorage
  }

  try {
    if (window.localStorage) {
      webStorage = true;
      return true
    }
  }
  catch (e) {}

  webStorage = false;
  return false
}

function getClientProperties () {
  return {
    has: {
      touch: (() => !!('ontouchstart' in document.documentElement) || window.navigator.msMaxTouchPoints > 0)(),
      webStorage: hasWebStorage()
    },
    within: {
      iframe: window.self !== window.top
    }
  }
}

var Platform = {
  has: {
    touch: false,
    webStorage: false
  },
  within: { iframe: false },

  parseSSR (/* ssrContext */ ssr) {
    return ssr ? {
      is: getPlatform(ssr.req.headers['user-agent']),
      has: this.has,
      within: this.within
    } : {
      is: getPlatform(),
      ...getClientProperties()
    }
  },

  install ($q, queues) {
    if (isSSR === true) {
      queues.server.push((q, ctx) => {
        q.platform = this.parseSSR(ctx.ssr);
      });
      return
    }

    this.is = getPlatform();

    if (fromSSR === true) {
      queues.takeover.push(q => {
        onSSR = fromSSR = false;
        Object.assign(q.platform, getClientProperties());
      });
      Vue.util.defineReactive($q, 'platform', this);
    }
    else {
      Object.assign(this, getClientProperties());
      $q.platform = this;
    }
  }
};

/* eslint-disable no-extend-native, one-var, no-self-compare */

function assign (target, firstSource) {
  if (target === undefined || target === null) {
    throw new TypeError('Cannot convert first argument to object')
  }

  var to = Object(target);
  for (var i = 1; i < arguments.length; i++) {
    var nextSource = arguments[i];
    if (nextSource === undefined || nextSource === null) {
      continue
    }

    var keysArray = Object.keys(Object(nextSource));
    for (var nextIndex = 0, len = keysArray.length; nextIndex < len; nextIndex++) {
      var nextKey = keysArray[nextIndex];
      var desc = Object.getOwnPropertyDescriptor(nextSource, nextKey);
      if (desc !== undefined && desc.enumerable) {
        to[nextKey] = nextSource[nextKey];
      }
    }
  }
  return to
}

if (!Object.assign) {
  Object.defineProperty(Object, 'assign', {
    enumerable: false,
    configurable: true,
    writable: true,
    value: assign
  });
}

if (!Number.isInteger) {
  Number.isInteger = function (value) {
    return typeof value === 'number' &&
      isFinite(value) &&
      Math.floor(value) === value
  };
}

if (!Array.prototype.includes) {
  Array.prototype.includes = function (searchEl, startFrom) {

    let O = Object(this);
    let len = parseInt(O.length, 10) || 0;
    if (len === 0) {
      return false
    }
    let n = parseInt(startFrom, 10) || 0;
    let k;
    if (n >= 0) {
      k = n;
    }
    else {
      k = len + n;
      if (k < 0) { k = 0; }
    }
    let curEl;
    while (k < len) {
      curEl = O[k];
      if (searchEl === curEl ||
         (searchEl !== searchEl && curEl !== curEl)) { // NaN !== NaN
        return true
      }
      k++;
    }
    return false
  };
}

if (!String.prototype.startsWith) {
  String.prototype.startsWith = function (str, position) {
    position = position || 0;
    return this.substr(position, str.length) === str
  };
}

if (!String.prototype.endsWith) {
  String.prototype.endsWith = function (str, position) {
    let subjectString = this.toString();

    if (typeof position !== 'number' || !isFinite(position) || Math.floor(position) !== position || position > subjectString.length) {
      position = subjectString.length;
    }
    position -= str.length;

    let lastIndex = subjectString.indexOf(str, position);

    return lastIndex !== -1 && lastIndex === position
  };
}

if (isSSR === false) {
  if (typeof Element.prototype.matches !== 'function') {
    Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.webkitMatchesSelector || function matches (selector) {
      let
        element = this,
        elements = (element.document || element.ownerDocument).querySelectorAll(selector),
        index = 0;

      while (elements[index] && elements[index] !== element) {
        ++index;
      }

      return Boolean(elements[index])
    };
  }

  if (typeof Element.prototype.closest !== 'function') {
    Element.prototype.closest = function closest (selector) {
      let el = this;
      while (el && el.nodeType === 1) {
        if (el.matches(selector)) {
          return el
        }
        el = el.parentNode;
      }
      return null
    };
  }

  // from:https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/remove()/remove().md
  (function (arr) {
    arr.forEach(item => {
      if (item.hasOwnProperty('remove')) { return }
      Object.defineProperty(item, 'remove', {
        configurable: true,
        enumerable: true,
        writable: true,
        value () {
          if (this.parentNode !== null) {
            this.parentNode.removeChild(this);
          }
        }
      });
    });
  })([Element.prototype, CharacterData.prototype, DocumentType.prototype]);
}

if (!Array.prototype.find) {
  Object.defineProperty(Array.prototype, 'find', {
    value (predicate) {
      if (this == null) {
        throw new TypeError('Array.prototype.find called on null or undefined')
      }
      if (typeof predicate !== 'function') {
        throw new TypeError('predicate must be a function')
      }

      let value;
      const
        list = Object(this),
        length = list.length >>> 0,
        thisArg = arguments[1];

      for (let i = 0; i < length; i++) {
        value = list[i];
        if (predicate.call(thisArg, value, i, list)) {
          return value
        }
      }
      return undefined
    }
  });
}

var version$1 = "1.0.0-beta.24";

const listenOpts = {
  hasPassive: false,
  passiveCapture: true,
  notPassiveCapture: true
};

try {
  var opts$1 = Object.defineProperty({}, 'passive', {
    get () {
      Object.assign(listenOpts, {
        hasPassive: true,
        passive: { passive: true },
        notPassive: { passive: false },
        passiveCapture: { passive: true, capture: true },
        notPassiveCapture: { passive: false, capture: true }
      });
    }
  });
  window.addEventListener('qtest', null, opts$1);
  window.removeEventListener('qtest', null, opts$1);
}
catch (e) {}

function leftClick (e) {
  return e.button === 0
}

function position (e) {
  if (e.touches && e.touches[0]) {
    e = e.touches[0];
  }
  else if (e.changedTouches && e.changedTouches[0]) {
    e = e.changedTouches[0];
  }

  return {
    top: e.clientY,
    left: e.clientX
  }
}

function getEventPath (e) {
  if (e.path) {
    return e.path
  }
  if (e.composedPath) {
    return e.composedPath()
  }

  const path = [];
  let el = e.target;

  while (el) {
    path.push(el);

    if (el.tagName === 'HTML') {
      path.push(document);
      path.push(window);
      return path
    }

    el = el.parentElement;
  }
}

// Reasonable defaults
const
  LINE_HEIGHT = 40,
  PAGE_HEIGHT = 800;

function getMouseWheelDistance (e) {
  let x = e.deltaX, y = e.deltaY;

  if ((x || y) && e.deltaMode) {
    const multiplier = e.deltaMode === 1 ? LINE_HEIGHT : PAGE_HEIGHT;
    x *= multiplier;
    y *= multiplier;
  }

  if (e.shiftKey && !x) {
    [y, x] = [x, y];
  }

  return { x, y }
}

function stop (e) {
  e.stopPropagation();
}

function prevent (e) {
  e.cancelable !== false && e.preventDefault();
}

function stopAndPrevent (e) {
  e.cancelable !== false && e.preventDefault();
  e.stopPropagation();
}

function create (name, { bubbles = false, cancelable = false } = {}) {
  try {
    return new Event(name, { bubbles, cancelable })
  }
  catch (e) {
    // IE doesn't support `new Event()`, so...`
    const evt = document.createEvent('Event');
    evt.initEvent(name, bubbles, cancelable);
    return evt
  }
}

function debounce (fn, wait = 250, immediate) {
  let timeout;

  function debounced (...args) {
    const later = () => {
      timeout = null;
      if (!immediate) {
        fn.apply(this, args);
      }
    };

    clearTimeout(timeout);
    if (immediate && !timeout) {
      fn.apply(this, args);
    }
    timeout = setTimeout(later, wait);
  }

  debounced.cancel = () => {
    clearTimeout(timeout);
  };

  return debounced
}

const SIZE_LIST = ['sm', 'md', 'lg', 'xl'];

var Screen = {
  width: 0,
  height: 0,

  sizes: {
    sm: 600,
    md: 1024,
    lg: 1440,
    xl: 1920
  },

  lt: {
    sm: true,
    md: true,
    lg: true,
    xl: true
  },
  gt: {
    xs: false,
    sm: false,
    md: false,
    lg: false
  },
  xs: true,
  sm: false,
  md: false,
  lg: false,
  xl: false,

  setSizes () {},
  setDebounce () {},

  install ($q, queues) {
    if (isSSR === true) {
      $q.screen = this;
      return
    }

    let update = force => {
      if (window.innerHeight !== this.height) {
        this.height = window.innerHeight;
      }

      const w = window.innerWidth;

      if (w !== this.width) {
        this.width = w;
      }
      else if (force !== true) {
        return
      }

      const s = this.sizes;

      this.gt.xs = w >= s.sm;
      this.gt.sm = w >= s.md;
      this.gt.md = w >= s.lg;
      this.gt.lg = w >= s.xl;
      this.lt.sm = w < s.sm;
      this.lt.md = w < s.md;
      this.lt.lg = w < s.lg;
      this.lt.xl = w < s.xl;
      this.xs = this.lt.sm;
      this.sm = this.gt.xs && this.lt.md;
      this.md = this.gt.sm && this.lt.lg;
      this.lg = this.gt.md && this.lt.xl;
      this.xl = w > s.xl;
    };

    let updateEvt, updateSizes = {}, updateDebounce = 16;

    this.setSizes = sizes => {
      SIZE_LIST.forEach(name => {
        if (sizes[name] !== void 0) {
          updateSizes[name] = sizes[name];
        }
      });
    };
    this.setDebounce = deb => {
      updateDebounce = deb;
    };

    const start = () => {
      const style = getComputedStyle(document.body);

      // if css props available
      if (style.getPropertyValue('--q-size-sm')) {
        SIZE_LIST.forEach(name => {
          this.sizes[name] = parseInt(style.getPropertyValue(`--q-size-${name}`), 10);
        });
      }

      this.setSizes = sizes => {
        SIZE_LIST.forEach(name => {
          if (sizes[name]) {
            this.sizes[name] = sizes[name];
          }
        });
        update(true);
      };

      this.setDebounce = delay => {
        const fn = () => { update(); };
        updateEvt && window.removeEventListener('resize', updateEvt, listenOpts.passive);
        updateEvt = delay > 0
          ? debounce(fn, delay)
          : fn;
        window.addEventListener('resize', updateEvt, listenOpts.passive);
      };

      this.setDebounce(updateDebounce);

      if (Object.keys(updateSizes).length > 0) {
        this.setSizes(updateSizes);
        updateSizes = void 0; // free up memory
      }
      else {
        update();
      }
    };

    if (fromSSR === true) {
      queues.takeover.push(start);
    }
    else {
      start();
    }

    Vue.util.defineReactive($q, 'screen', this);
  }
};

var History = {
  __history: [],
  add: () => {},
  remove: () => {},

  install ($q, cfg) {
    if (isSSR || !$q.platform.is.cordova) {
      return
    }

    this.add = definition => {
      this.__history.push(definition);
    };
    this.remove = definition => {
      const index = this.__history.indexOf(definition);
      if (index >= 0) {
        this.__history.splice(index, 1);
      }
    };

    const exit = cfg.cordova === void 0 || cfg.cordova.backButtonExit !== false;

    document.addEventListener('deviceready', () => {
      document.addEventListener('backbutton', () => {
        if (this.__history.length) {
          this.__history.pop().handler();
        }
        else if (exit && window.location.hash === '#/') {
          navigator.app.exitApp();
        }
        else {
          window.history.back();
        }
      }, false);
    });
  }
};

var langEn = {
  isoName: 'en-us',
  nativeName: 'English (US)',
  label: {
    clear: 'Clear',
    ok: 'OK',
    cancel: 'Cancel',
    close: 'Close',
    set: 'Set',
    select: 'Select',
    reset: 'Reset',
    remove: 'Remove',
    update: 'Update',
    create: 'Create',
    search: 'Search',
    filter: 'Filter',
    refresh: 'Refresh'
  },
  date: {
    days: 'Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday'.split('_'),
    daysShort: 'Sun_Mon_Tue_Wed_Thu_Fri_Sat'.split('_'),
    months: 'January_February_March_April_May_June_July_August_September_October_November_December'.split('_'),
    monthsShort: 'Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec'.split('_'),
    firstDayOfWeek: 0, // 0-6, 0 - Sunday, 1 Monday, ...
    format24h: false
  },
  table: {
    noData: 'No data available',
    noResults: 'No matching records found',
    loading: 'Loading...',
    selectedRecords: function (rows) {
      return rows === 1
        ? '1 record selected.'
        : (rows === 0 ? 'No' : rows) + ' records selected.'
    },
    recordsPerPage: 'Records per page:',
    allRows: 'All',
    pagination: function (start, end, total) {
      return start + '-' + end + ' of ' + total
    },
    columns: 'Columns'
  },
  editor: {
    url: 'URL',
    bold: 'Bold',
    italic: 'Italic',
    strikethrough: 'Strikethrough',
    underline: 'Underline',
    unorderedList: 'Unordered List',
    orderedList: 'Ordered List',
    subscript: 'Subscript',
    superscript: 'Superscript',
    hyperlink: 'Hyperlink',
    toggleFullscreen: 'Toggle Fullscreen',
    quote: 'Quote',
    left: 'Left align',
    center: 'Center align',
    right: 'Right align',
    justify: 'Justify align',
    print: 'Print',
    outdent: 'Decrease indentation',
    indent: 'Increase indentation',
    removeFormat: 'Remove formatting',
    formatting: 'Formatting',
    fontSize: 'Font Size',
    align: 'Align',
    hr: 'Insert Horizontal Rule',
    undo: 'Undo',
    redo: 'Redo',
    header1: 'Header 1',
    header2: 'Header 2',
    header3: 'Header 3',
    header4: 'Header 4',
    header5: 'Header 5',
    header6: 'Header 6',
    paragraph: 'Paragraph',
    code: 'Code',
    size1: 'Very small',
    size2: 'A bit small',
    size3: 'Normal',
    size4: 'Medium-large',
    size5: 'Big',
    size6: 'Very big',
    size7: 'Maximum',
    defaultFont: 'Default Font'
  },
  tree: {
    noNodes: 'No nodes available',
    noResults: 'No matching nodes found'
  }
};

var lang = {
  install ($q, queues, lang) {
    if (isSSR === true) {
      queues.server.push((q, ctx) => {
        const
          opt = {
            lang: q.lang.isoName,
            dir: q.lang.rtl === true ? 'rtl' : 'ltr'
          },
          fn = ctx.ssr.setHtmlAttrs;

        if (typeof fn === 'function') {
          fn(opt);
        }
        else {
          ctx.ssr.Q_HTML_ATTRS = Object.keys(opt)
            .map(key => `${key}=${opt[key]}`)
            .join(' ');
        }
      });
    }

    this.set = (lang = langEn) => {
      lang.set = this.set;
      lang.getLocale = this.getLocale;
      lang.rtl = lang.rtl || false;

      if (isSSR === false) {
        const el = document.documentElement;
        el.setAttribute('dir', lang.rtl ? 'rtl' : 'ltr');
        el.setAttribute('lang', lang.isoName);
      }

      if (isSSR === true || $q.lang !== void 0) {
        $q.lang = lang;
      }
      else {
        Vue.util.defineReactive($q, 'lang', lang);
      }

      this.isoName = lang.isoName;
      this.nativeName = lang.nativeName;
      this.props = lang;
    };

    this.set(lang);
  },

  getLocale () {
    if (isSSR === true) { return }

    let val =
      navigator.language ||
      navigator.languages[0] ||
      navigator.browserLanguage ||
      navigator.userLanguage ||
      navigator.systemLanguage;

    if (val) {
      return val.toLowerCase()
    }
  }
};

function rgbToHex ({ r, g, b, a }) {
  const alpha = a !== void 0;

  r = Math.round(r);
  g = Math.round(g);
  b = Math.round(b);

  if (
    r > 255 ||
    g > 255 ||
    b > 255 ||
    (alpha && a > 100)
  ) {
    throw new TypeError('Expected 3 numbers below 256 (and optionally one below 100)')
  }

  a = alpha
    ? (Math.round(255 * a / 100) | 1 << 8).toString(16).slice(1)
    : '';

  return '#' + ((b | g << 8 | r << 16) | 1 << 24).toString(16).slice(1) + a
}

function rgbToString ({ r, g, b, a }) {
  return `rgb${a !== void 0 ? 'a' : ''}(${r},${g},${b}${a !== void 0 ? ',' + (a / 100) : ''})`
}

function stringToRgb (str) {
  if (typeof str !== 'string') {
    throw new TypeError('Expected a string')
  }

  str = str.replace(/ /g, '');

  if (str.startsWith('#')) {
    return hexToRgb(str)
  }

  const model = str.substring(str.indexOf('(') + 1, str.length - 1).split(',');

  return {
    r: parseInt(model[0], 10),
    g: parseInt(model[1], 10),
    b: parseInt(model[2], 10),
    a: model[3] !== void 0 ? parseFloat(model[3]) * 100 : void 0
  }
}

function hexToRgb (hex) {
  if (typeof hex !== 'string') {
    throw new TypeError('Expected a string')
  }

  hex = hex.replace(/^#/, '');

  if (hex.length === 3) {
    hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
  }
  else if (hex.length === 4) {
    hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
  }

  let num = parseInt(hex, 16);

  return hex.length > 6
    ? { r: num >> 24 & 255, g: num >> 16 & 255, b: num >> 8 & 255, a: Math.round((num & 255) / 2.55) }
    : { r: num >> 16, g: num >> 8 & 255, b: num & 255 }
}

function hsvToRgb ({ h, s, v, a }) {
  let r, g, b, i, f, p, q, t;
  s = s / 100;
  v = v / 100;

  h = h / 360;
  i = Math.floor(h * 6);
  f = h * 6 - i;
  p = v * (1 - s);
  q = v * (1 - f * s);
  t = v * (1 - (1 - f) * s);

  switch (i % 6) {
    case 0:
      r = v;
      g = t;
      b = p;
      break
    case 1:
      r = q;
      g = v;
      b = p;
      break
    case 2:
      r = p;
      g = v;
      b = t;
      break
    case 3:
      r = p;
      g = q;
      b = v;
      break
    case 4:
      r = t;
      g = p;
      b = v;
      break
    case 5:
      r = v;
      g = p;
      b = q;
      break
  }

  return {
    r: Math.round(r * 255),
    g: Math.round(g * 255),
    b: Math.round(b * 255),
    a
  }
}

function rgbToHsv ({ r, g, b, a }) {
  let
    max = Math.max(r, g, b), min = Math.min(r, g, b),
    d = max - min,
    h,
    s = (max === 0 ? 0 : d / max),
    v = max / 255;

  switch (max) {
    case min:
      h = 0;
      break
    case r:
      h = (g - b) + d * (g < b ? 6 : 0);
      h /= 6 * d;
      break
    case g:
      h = (b - r) + d * 2;
      h /= 6 * d;
      break
    case b:
      h = (r - g) + d * 4;
      h /= 6 * d;
      break
  }

  return {
    h: Math.round(h * 360),
    s: Math.round(s * 100),
    v: Math.round(v * 100),
    a
  }
}

const reRGBA = /^\s*rgb(a)?\s*\((\s*(\d+)\s*,\s*?){2}(\d+)\s*,?\s*([01]?\.?\d*?)?\s*\)\s*$/;

function textToRgb (color) {
  if (typeof color !== 'string') {
    throw new TypeError('Expected a string')
  }

  const m = reRGBA.exec(color);
  if (m) {
    const rgb = {
      r: Math.max(255, parseInt(m[2], 10)),
      g: Math.max(255, parseInt(m[3], 10)),
      b: Math.max(255, parseInt(m[4], 10))
    };
    if (m[1]) {
      rgb.a = Math.max(1, parseFloat(m[5]));
    }
    return rgb
  }
  return hexToRgb(color)
}

/* works as darken if percent < 0 */
function lighten (color, percent) {
  if (typeof color !== 'string') {
    throw new TypeError('Expected a string as color')
  }
  if (typeof percent !== 'number') {
    throw new TypeError('Expected a numeric percent')
  }

  const rgb = textToRgb(color),
    t = percent < 0 ? 0 : 255,
    p = Math.abs(percent) / 100,
    R = rgb.r,
    G = rgb.g,
    B = rgb.b;

  return '#' + (
    0x1000000 + (Math.round((t - R) * p) + R) * 0x10000 +
    (Math.round((t - G) * p) + G) * 0x100 +
    (Math.round((t - B) * p) + B)
  ).toString(16).slice(1)
}

function luminosity (color) {
  if (typeof color !== 'string' && (!color || color.r === void 0)) {
    throw new TypeError('Expected a string or a {r, g, b} object as color')
  }

  const
    rgb = typeof color === 'string' ? textToRgb(color) : color,
    r = rgb.r / 255,
    g = rgb.g / 255,
    b = rgb.b / 255,
    R = r <= 0.03928 ? r / 12.92 : Math.pow((r + 0.055) / 1.055, 2.4),
    G = g <= 0.03928 ? g / 12.92 : Math.pow((g + 0.055) / 1.055, 2.4),
    B = b <= 0.03928 ? b / 12.92 : Math.pow((b + 0.055) / 1.055, 2.4);
  return 0.2126 * R + 0.7152 * G + 0.0722 * B
}

function setBrand (color, value, element = document.body) {
  if (typeof color !== 'string') {
    throw new TypeError('Expected a string as color')
  }
  if (typeof value !== 'string') {
    throw new TypeError('Expected a string as value')
  }
  if (!(element instanceof Element)) {
    throw new TypeError('Expected a DOM element')
  }

  element.style.setProperty(`--q-color-${color}`, value);
  switch (color) {
    case 'negative':
    case 'warning':
      element.style.setProperty(`--q-color-${color}-l`, lighten(value, 46));
      break
    case 'light':
      element.style.setProperty(`--q-color-${color}-d`, lighten(value, -10));
  }
}

function getMobilePlatform (is) {
  if (is.ios === true) return 'ios'
  if (is.android === true) return 'android'
  if (is.winphone === true) return 'winphone'
}

function getBodyClasses ({ is, has, within }, cfg) {
  const cls = [
    is.desktop ? 'desktop' : 'mobile',
    has.touch ? 'touch' : 'no-touch'
  ];

  if (is.mobile === true) {
    const mobile = getMobilePlatform(is);
    if (mobile !== void 0) {
      cls.push('platform-' + mobile);
    }
  }

  if (is.cordova === true) {
    cls.push('cordova');

    if (
      is.ios === true &&
      (cfg.cordova === void 0 || cfg.cordova.iosStatusBarPadding !== false)
    ) {
      cls.push('q-ios-padding');
    }
  }
  else if (is.electron === true) {
    cls.push('electron');
  }

  within.iframe === true && cls.push('within-iframe');

  return cls
}

function bodyInit (Platform, cfg) {
  const cls = getBodyClasses(Platform, cfg);

  if (Platform.is.ie === true && Platform.is.versionNumber === 11) {
    cls.forEach(c => document.body.classList.add(c));
  }
  else {
    document.body.classList.add.apply(document.body.classList, cls);
  }

  if (Platform.is.ios === true) {
    // needed for iOS button active state
    document.body.addEventListener('touchstart', () => {});
  }
}

function setColors (brand) {
  for (let color in brand) {
    setBrand(color, brand[color]);
  }
}

var Body = {
  install ($q, queues, cfg) {
    if (isSSR === true) {
      queues.server.push((q, ctx) => {
        const
          cls = getBodyClasses(q.platform, cfg),
          fn = ctx.ssr.setBodyClasses;

        if (typeof fn === 'function') {
          fn(cls);
        }
        else {
          ctx.ssr.Q_BODY_CLASSES = cls.join(' ');
        }
      });
      return
    }

    cfg.brand && setColors(cfg.brand);
    bodyInit($q.platform, cfg);
  }
};

var materialIcons = {
  name: 'material-icons',
  type: {
    positive: 'check_circle',
    negative: 'warning',
    info: 'info',
    warning: 'priority_high'
  },
  arrow: {
    up: 'arrow_upward',
    right: 'arrow_forward',
    down: 'arrow_downward',
    left: 'arrow_back',
    dropdown: 'arrow_drop_down'
  },
  chevron: {
    left: 'chevron_left',
    right: 'chevron_right'
  },
  colorPicker: {
    spectrum: 'gradient',
    tune: 'tune',
    palette: 'style'
  },
  pullToRefresh: {
    icon: 'refresh'
  },
  carousel: {
    left: 'chevron_left',
    right: 'chevron_right',
    navigationIcon: 'lens',
    thumbnails: 'view_carousel'
  },
  chip: {
    remove: 'cancel',
    selected: 'check'
  },
  datetime: {
    arrowLeft: 'chevron_left',
    arrowRight: 'chevron_right',
    now: 'access_time',
    today: 'today'
  },
  editor: {
    bold: 'format_bold',
    italic: 'format_italic',
    strikethrough: 'strikethrough_s',
    underline: 'format_underlined',
    unorderedList: 'format_list_bulleted',
    orderedList: 'format_list_numbered',
    subscript: 'vertical_align_bottom',
    superscript: 'vertical_align_top',
    hyperlink: 'link',
    toggleFullscreen: 'fullscreen',
    quote: 'format_quote',
    left: 'format_align_left',
    center: 'format_align_center',
    right: 'format_align_right',
    justify: 'format_align_justify',
    print: 'print',
    outdent: 'format_indent_decrease',
    indent: 'format_indent_increase',
    removeFormat: 'format_clear',
    formatting: 'text_format',
    fontSize: 'format_size',
    align: 'format_align_left',
    hr: 'remove',
    undo: 'undo',
    redo: 'redo',
    header: 'format_size',
    code: 'code',
    size: 'format_size',
    font: 'font_download'
  },
  expansionItem: {
    icon: 'keyboard_arrow_down',
    denseIcon: 'arrow_drop_down'
  },
  fab: {
    icon: 'add',
    activeIcon: 'close'
  },
  field: {
    clear: 'cancel',
    error: 'error'
  },
  pagination: {
    first: 'first_page',
    prev: 'keyboard_arrow_left',
    next: 'keyboard_arrow_right',
    last: 'last_page'
  },
  rating: {
    icon: 'grade'
  },
  stepper: {
    done: 'check',
    active: 'edit',
    error: 'warning'
  },
  tabs: {
    left: 'chevron_left',
    right: 'chevron_right',
    up: 'keyboard_arrow_up',
    down: 'keyboard_arrow_down'
  },
  table: {
    arrowUp: 'arrow_upward',
    warning: 'warning',
    prevPage: 'chevron_left',
    nextPage: 'chevron_right'
  },
  tree: {
    icon: 'play_arrow'
  },
  uploader: {
    done: 'done',
    clear: 'clear',
    add: 'add_box',
    upload: 'cloud_upload',
    removeQueue: 'clear_all',
    removeUploaded: 'done_all'
  }
};

var iconSet = {
  __installed: false,
  install ($q, iconSet) {
    this.set = (iconDef = materialIcons) => {
      iconDef.set = this.set;

      if (isSSR === true || $q.iconSet !== void 0) {
        $q.iconSet = iconDef;
      }
      else {
        Vue.util.defineReactive($q, 'iconSet', iconDef);
      }

      this.name = iconDef.name;
      this.def = iconDef;
    };

    this.set(iconSet);
  }
};

const queues = {
  server: [], // on SSR update
  takeover: [] // on client takeover
};

const $q = {
  version: version$1
};

function install (Vue, opts = {}) {
  if (this.__installed) { return }
  this.__installed = true;

  const cfg = opts.config || {};

  // required plugins
  Platform.install($q, queues);
  Body.install($q, queues, cfg);
  Screen.install($q, queues);
  History.install($q, cfg);
  lang.install($q, queues, opts.lang);
  iconSet.install($q, opts.iconSet);

  if (isSSR === true) {
    Vue.mixin({
      beforeCreate () {
        this.$q = this.$root.$options.$q;
      }
    });
  }
  else {
    Vue.prototype.$q = $q;
  }

  opts.components && Object.keys(opts.components).forEach(key => {
    const c = opts.components[key];
    if (typeof c === 'function') {
      Vue.component(c.options.name, c);
    }
  });

  opts.directives && Object.keys(opts.directives).forEach(key => {
    const d = opts.directives[key];
    if (d.name !== undefined && d.unbind !== void 0) {
      Vue.directive(d.name, d);
    }
  });

  if (opts.plugins) {
    const param = { $q, queues, cfg };
    Object.keys(opts.plugins).forEach(key => {
      const p = opts.plugins[key];
      if (typeof p.install === 'function' && p !== Platform && p !== Screen) {
        p.install(param);
      }
    });
  }
}

const mixin = {
  mounted () {
    queues.takeover.forEach(run => {
      run(this.$q);
    });
  }
};

function ssrUpdate (ctx) {
  if (ctx.ssr) {
    const q = { ...$q };

    Object.assign(ctx.ssr, {
      Q_HEAD_TAGS: '',
      Q_BODY_ATTRS: '',
      Q_BODY_TAGS: ''
    });

    queues.server.forEach(run => {
      run(q, ctx);
    });

    ctx.app.$q = q;
  }
  else {
    const mixins = ctx.app.mixins || [];
    if (!mixins.includes(mixin)) {
      ctx.app.mixins = mixins.concat(mixin);
    }
  }
}

var VuePlugin = {
  version: version$1,
  install,
  lang,
  iconSet,
  ssrUpdate
};

const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

function humanStorageSize (bytes) {
  let u = 0;

  while (parseInt(bytes, 10) >= 1024 && u < units.length - 1) {
    bytes /= 1024;
    ++u;
  }

  return `${bytes.toFixed(1)} ${units[u]}`
}

function between (v, min, max) {
  return max <= min
    ? min
    : Math.min(max, Math.max(min, v))
}

function normalizeToInterval (v, min, max) {
  if (max <= min) {
    return min
  }

  const size = (max - min + 1);

  let index = min + (v - min) % size;
  if (index < min) {
    index = size + index;
  }

  return index === 0 ? 0 : index // fix for (-a % a) => -0
}

function pad (v, length = 2, char = '0') {
  let val = '' + v;
  return val.length >= length
    ? val
    : new Array(length - val.length + 1).join(char) + val
}

const
  xhr = isSSR ? null : XMLHttpRequest,
  send = isSSR ? null : xhr.prototype.send,
  stack = { start: [], stop: [] };

let highjackCount = 0;

function translate ({ p, pos, active, horiz, reverse, dir }) {
  let x = 1, y = 1;

  if (horiz) {
    if (reverse) { x = -1; }
    if (pos === 'bottom') { y = -1; }
    return { transform: `translate3d(${x * (p - 100)}%,${active ? 0 : y * -200}%,0)` }
  }

  if (reverse) { y = -1; }
  if (pos === 'right') { x = -1; }
  return { transform: `translate3d(${active ? 0 : dir * x * -200}%,${y * (p - 100)}%,0)` }
}

function inc (p, amount) {
  if (typeof amount !== 'number') {
    if (p < 25) {
      amount = Math.random() * 3 + 3;
    }
    else if (p < 65) {
      amount = Math.random() * 3;
    }
    else if (p < 85) {
      amount = Math.random() * 2;
    }
    else if (p < 99) {
      amount = 0.6;
    }
    else {
      amount = 0;
    }
  }
  return between(p + amount, 0, 100)
}

function highjackAjax (start, stop) {
  stack.start.push(start);
  stack.stop.push(stop);

  highjackCount++;

  if (highjackCount > 1) { return }

  function endHandler () {
    stack.stop.map(fn => { fn(); });
  }

  xhr.prototype.send = function (...args) {
    stack.start.map(fn => { fn(); });

    this.addEventListener('abort', endHandler, false);
    this.addEventListener('readystatechange', () => {
      if (this.readyState === 4) { endHandler(); }
    }, false);

    send.apply(this, args);
  };
}

function restoreAjax (start, stop) {
  stack.start = stack.start.filter(fn => fn !== start);
  stack.stop = stack.stop.filter(fn => fn !== stop);

  highjackCount = Math.max(0, highjackCount - 1);
  if (!highjackCount) {
    xhr.prototype.send = send;
  }
}

Vue.extend({
  name: 'QAjaxBar',

  props: {
    position: {
      type: String,
      default: 'top',
      validator (val) {
        return ['top', 'right', 'bottom', 'left'].includes(val)
      }
    },
    size: {
      type: String,
      default: '2px'
    },
    color: {
      type: String,
      default: 'red'
    },
    skipHijack: Boolean,
    reverse: Boolean
  },

  data () {
    return {
      calls: 0,
      progress: 0,
      onScreen: false,
      animate: true
    }
  },

  computed: {
    classes () {
      return [
        `q-loading-bar--${this.position}`,
        `bg-${this.color}`,
        this.animate ? '' : 'no-transition'
      ]
    },

    style () {
      const active = this.onScreen;

      let o = translate({
        p: this.progress,
        pos: this.position,
        active,
        horiz: this.horizontal,
        reverse: this.$q.lang.rtl && ['top', 'bottom'].includes(this.position)
          ? !this.reverse
          : this.reverse,
        dir: this.$q.lang.rtl ? -1 : 1
      });

      o[this.sizeProp] = this.size;
      o.opacity = active ? 1 : 0;

      return o
    },

    horizontal () {
      return this.position === 'top' || this.position === 'bottom'
    },

    sizeProp () {
      return this.horizontal ? 'height' : 'width'
    }
  },

  methods: {
    start (speed = 300) {
      this.calls++;
      if (this.calls > 1) { return }

      clearTimeout(this.timer);
      this.$emit('start');

      if (this.onScreen) { return }

      this.progress = 0;
      this.onScreen = true;
      this.animate = false;
      this.timer = setTimeout(() => {
        this.animate = true;
        this.__work(speed);
      }, 100);
    },

    increment (amount) {
      this.calls > 0 && (this.progress = inc(this.progress, amount));
    },

    stop () {
      this.calls = Math.max(0, this.calls - 1);
      if (this.calls > 0) { return }

      clearTimeout(this.timer);
      this.$emit('stop');

      const end = () => {
        this.animate = true;
        this.progress = 100;
        this.timer = setTimeout(() => {
          this.onScreen = false;
        }, 1000);
      };

      if (this.progress === 0) {
        this.timer = setTimeout(end, 1);
      }
      else {
        end();
      }
    },

    __work (speed) {
      if (this.progress < 100) {
        this.timer = setTimeout(() => {
          this.increment();
          this.__work(speed);
        }, speed);
      }
    }
  },

  mounted () {
    if (!this.skipHijack) {
      this.hijacked = true;
      highjackAjax(this.start, this.stop);
    }
  },

  beforeDestroy () {
    clearTimeout(this.timer);
    this.hijacked && restoreAjax(this.start, this.stop);
  },

  render (h) {
    return h('div', {
      staticClass: 'q-loading-bar',
      class: this.classes,
      style: this.style
    })
  }
});

function slot (vm, slotName) {
  return vm.$scopedSlots[slotName] !== void 0
    ? vm.$scopedSlots[slotName]()
    : void 0
}

var QIcon = Vue.extend({
  name: 'QIcon',

  props: {
    name: String,
    color: String,
    size: String,
    left: Boolean,
    right: Boolean
  },

  computed: {
    type () {
      let cls;
      const icon = this.name;

      if (!icon) {
        return {
          cls: void 0,
          content: void 0
        }
      }

      const commonCls = 'q-icon' +
        (this.left === true ? ' on-left' : '') +
        (this.right === true ? ' on-right' : '');

      if (icon.startsWith('img:') === true) {
        return {
          img: true,
          cls: commonCls,
          src: icon.substring(4)
        }
      }

      let content = ' ';

      if (/^fa[s|r|l|b]{0,1} /.test(icon) || icon.startsWith('icon-') === true) {
        cls = icon;
      }
      else if (icon.startsWith('bt-') === true) {
        cls = `bt ${icon}`;
      }
      else if (icon.startsWith('eva-') === true) {
        cls = `eva ${icon}`;
      }
      else if (/^ion-(md|ios|logo)/.test(icon) === true) {
        cls = `ionicons ${icon}`;
      }
      else if (icon.startsWith('ion-') === true) {
        cls = `ionicons ion-${this.$q.platform.is.ios === true ? 'ios' : 'md'}${icon.substr(3)}`;
      }
      else if (icon.startsWith('mdi-') === true) {
        cls = `mdi ${icon}`;
      }
      else if (icon.startsWith('iconfont ') === true) {
        cls = `${icon}`;
      }
      else if (icon.startsWith('ti-') === true) {
        cls = `themify-icon ${icon}`;
      }
      else {
        cls = 'material-icons';
        content = icon;
      }

      return {
        cls: cls + ' ' + commonCls +
          (this.color !== void 0 ? ` text-${this.color}` : ''),
        content
      }
    },

    style () {
      if (this.size !== void 0) {
        return { fontSize: this.size }
      }
    }
  },

  render (h) {
    return this.type.img === true
      ? h('img', {
        staticClass: this.type.cls,
        style: this.style,
        on: this.$listeners,
        attrs: { src: this.type.src }
      })
      : h('i', {
        staticClass: this.type.cls,
        style: this.style,
        on: this.$listeners,
        attrs: { 'aria-hidden': true }
      }, [
        this.type.content,
        slot(this, 'default')
      ])
  }
});

Vue.extend({
  name: 'QAvatar',

  props: {
    size: String,
    fontSize: String,

    color: String,
    textColor: String,

    icon: String,
    square: Boolean,
    rounded: Boolean
  },

  computed: {
    contentClass () {
      return {
        [`bg-${this.color}`]: this.color,
        [`text-${this.textColor} q-chip--colored`]: this.textColor,
        'q-avatar__content--square': this.square,
        'rounded-borders': this.rounded
      }
    },

    style () {
      if (this.size) {
        return { fontSize: this.size }
      }
    },

    contentStyle () {
      if (this.fontSize) {
        return { fontSize: this.fontSize }
      }
    }
  },

  methods: {
    __getContent (h) {
      return this.icon !== void 0
        ? [ h(QIcon, { props: { name: this.icon } }) ].concat(slot(this, 'default'))
        : slot(this, 'default')
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-avatar',
      style: this.style,
      on: this.$listeners
    }, [
      h('div', {
        staticClass: 'q-avatar__content row flex-center overflow-hidden',
        class: this.contentClass,
        style: this.contentStyle
      }, [
        this.__getContent(h)
      ])
    ])
  }
});

Vue.extend({
  name: 'QBadge',

  props: {
    color: String,
    textColor: String,

    floating: Boolean,
    transparent: Boolean,
    multiLine: Boolean,

    label: [Number, String],

    align: {
      type: String,
      validator: v => ['top', 'middle', 'bottom'].includes(v)
    }
  },

  computed: {
    style () {
      if (this.align !== void 0) {
        return { verticalAlign: this.align }
      }
    },

    classes () {
      return 'q-badge flex inline items-center no-wrap' +
        ` q-badge--${this.multiLine === true ? 'multi' : 'single'}-line` +
        (this.color !== void 0 ? ` bg-${this.color}` : '') +
        (this.textColor !== void 0 ? ` text-${this.textColor}` : '') +
        (this.floating === true ? ' q-badge--floating' : '') +
        (this.transparent === true ? ' q-badge--transparent' : '')
    }
  },

  render (h) {
    return h('div', {
      style: this.style,
      class: this.classes,
      on: this.$listeners
    }, this.label !== void 0 ? [ this.label ] : slot(this, 'default'))
  }
});

Vue.extend({
  name: 'QBanner',

  props: {
    inlineActions: Boolean,
    dense: Boolean,
    rounded: Boolean
  },

  render (h) {
    const actions = slot(this, 'action');

    return h('div', {
      staticClass: 'q-banner row items-center',
      class: {
        'q-banner--top-padding': actions !== void 0 && !this.inlineActions,
        'q-banner--dense': this.dense,
        'rounded-borders': this.rounded
      },
      on: this.$listeners
    }, [

      h('div', {
        staticClass: 'q-banner__avatar col-auto row items-center'
      }, slot(this, 'avatar')),

      h('div', {
        staticClass: 'q-banner__content col text-body2'
      }, slot(this, 'default')),

      actions !== void 0
        ? h('div', {
          staticClass: 'q-banner__actions row items-center justify-end',
          class: this.inlineActions ? 'col-auto' : 'col-all'
        }, actions)
        : null

    ])
  }
});

var QBar = Vue.extend({
  name: 'QBar',

  props: {
    dense: Boolean,
    dark: Boolean
  },

  computed: {
    classes () {
      return `q-bar--${this.dense ? 'dense' : 'standard'} q-bar--${this.dark ? 'dark' : 'light'}`
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-bar row no-wrap items-center',
      class: this.classes,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

const
  alignMap = {
    left: 'start',
    center: 'center',
    right: 'end',
    between: 'between',
    around: 'around'
  },
  alignValues = Object.keys(alignMap);

var AlignMixin = {
  props: {
    align: {
      type: String,
      default: 'left',
      validator: v => alignValues.includes(v)
    }
  },

  computed: {
    alignClass () {
      return `justify-${alignMap[this.align]}`
    }
  }
};

var QBreadcrumbs = Vue.extend({
  name: 'QBreadcrumbs',

  mixins: [ AlignMixin ],

  props: {
    separator: {
      type: String,
      default: '/'
    },
    separatorColor: String,

    activeColor: {
      type: String,
      default: 'primary'
    },

    gutter: {
      type: String,
      validator: v => ['none', 'xs', 'sm', 'md', 'lg', 'xl'].includes(v),
      default: 'sm'
    }
  },

  computed: {
    classes () {
      return `${this.alignClass}${this.gutter === 'none' ? '' : ` q-gutter-${this.gutter}`}`
    },

    sepClass () {
      if (this.separatorColor) {
        return `text-${this.separatorColor}`
      }
    },

    activeClass () {
      return `text-${this.activeColor}`
    }
  },

  render (h) {
    const nodes = slot(this, 'default');
    if (nodes === void 0) { return }

    let els = 1;

    const
      child = [],
      len = nodes.filter(c => c.tag !== void 0 && c.tag.endsWith('-QBreadcrumbsEl')).length,
      separator = this.$scopedSlots.separator || (() => this.separator);

    nodes.forEach(comp => {
      if (comp.tag !== void 0 && comp.tag.endsWith('-QBreadcrumbsEl')) {
        const middle = els < len;
        els++;

        child.push(h('div', {
          staticClass: 'flex items-center',
          class: middle ? this.activeClass : 'q-breadcrumbs--last'
        }, [ comp ]));

        if (middle) {
          child.push(h('div', {
            staticClass: 'q-breadcrumbs__separator', class: this.sepClass
          }, separator()));
        }
      }
      else {
        child.push(comp);
      }
    });

    return h('div', {
      staticClass: 'q-breadcrumbs',
      on: this.$listeners
    }, [
      h('div', {
        staticClass: 'flex items-center',
        class: this.classes
      }, child)
    ])
  }
});

const routerLinkProps = {
  to: [String, Object],
  exact: Boolean,
  append: Boolean,
  replace: Boolean,
  activeClass: String,
  exactActiveClass: String,
  disable: Boolean
};

const RouterLinkMixin = {
  props: routerLinkProps,

  computed: {
    hasRouterLink () {
      return this.disable !== true && this.to !== void 0 && this.to !== null && this.to !== ''
    },

    routerLinkProps () {
      return {
        to: this.to,
        exact: this.exact,
        append: this.append,
        replace: this.replace,
        activeClass: this.activeClass || 'q-router-link--active',
        exactActiveClass: this.exactActiveClass || 'q-router-link--exact-active',
        event: this.disable === true ? '' : void 0
      }
    }
  }
};

var QBreadcrumbsEl = Vue.extend({
  name: 'QBreadcrumbsEl',

  mixins: [ RouterLinkMixin ],

  props: {
    label: String,
    icon: String
  },

  render (h) {
    return h(this.hasRouterLink === true ? 'router-link' : 'span', {
      staticClass: 'q-breadcrumbs__el q-link flex inline items-center relative-position',
      props: this.hasRouterLink === true ? this.routerLinkProps : null,
      [this.hasRouterLink === true ? 'nativeOn' : 'on']: this.$listeners
    }, [

      this.icon !== void 0
        ? h(QIcon, {
          staticClass: 'q-breadcrumbs__el-icon',
          class: this.label !== void 0 ? 'q-breadcrumbs__el-icon--with-label' : null,
          props: { name: this.icon }
        })
        : null,

      this.label

    ].concat(slot(this, 'default')))
  }
});

var mixin$1 = {
  props: {
    color: String,
    size: {
      type: [Number, String],
      default: '1em'
    }
  },

  computed: {
    classes () {
      if (this.color) {
        return `text-${this.color}`
      }
    }
  }
};

var QSpinner = Vue.extend({
  name: 'QSpinner',

  mixins: [ mixin$1 ],

  props: {
    thickness: {
      type: Number,
      default: 5
    }
  },

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner q-spinner-mat',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'viewBox': '25 25 50 50'
      }
    }, [
      h('circle', {
        staticClass: 'path',
        attrs: {
          'cx': '50',
          'cy': '50',
          'r': '20',
          'fill': 'none',
          'stroke': 'currentColor',
          'stroke-width': this.thickness,
          'stroke-miterlimit': '10'
        }
      })
    ])
  }
});

function offset (el) {
  if (el === window) {
    return { top: 0, left: 0 }
  }
  const { top, left } = el.getBoundingClientRect();
  return { top, left }
}

function height (el) {
  return el === window
    ? window.innerHeight
    : el.getBoundingClientRect().height
}

function css (element, css) {
  let style = element.style;

  Object.keys(css).forEach(prop => {
    style[prop] = css[prop];
  });
}

function showRipple (evt, el, ctx, forceCenter) {
  ctx.modifiers.stop === true && stop(evt);

  let { center, color } = ctx.modifiers;
  center = center === true || forceCenter === true;

  const
    node = document.createElement('span'),
    innerNode = document.createElement('span'),
    pos = position(evt),
    { left, top, width, height } = el.getBoundingClientRect(),
    diameter = Math.sqrt(width * width + height * height),
    radius = diameter / 2,
    centerX = `${(width - diameter) / 2}px`,
    x = center ? centerX : `${pos.left - left - radius}px`,
    centerY = `${(height - diameter) / 2}px`,
    y = center ? centerY : `${pos.top - top - radius}px`;

  innerNode.className = 'q-ripple__inner';
  css(innerNode, {
    height: `${diameter}px`,
    width: `${diameter}px`,
    transform: `translate3d(${x}, ${y}, 0) scale3d(0.2, 0.2, 1)`,
    opacity: 0
  });

  node.className = `q-ripple${color ? ' text-' + color : ''}`;
  node.setAttribute('dir', 'ltr');
  node.appendChild(innerNode);
  el.appendChild(node);

  ctx.abort = () => {
    node && node.remove();
    clearTimeout(timer);
  };

  let timer = setTimeout(() => {
    innerNode.classList.add('q-ripple__inner--enter');
    innerNode.style.transform = `translate3d(${centerX}, ${centerY}, 0) scale3d(1, 1, 1)`;
    innerNode.style.opacity = 0.2;

    timer = setTimeout(() => {
      innerNode.classList.remove('q-ripple__inner--enter');
      innerNode.classList.add('q-ripple__inner--leave');
      innerNode.style.opacity = 0;

      timer = setTimeout(() => {
        node && node.remove();
        ctx.abort = void 0;
      }, 275);
    }, 250);
  }, 50);
}

function updateCtx (ctx, { value, modifiers, arg }) {
  ctx.enabled = value !== false;

  if (ctx.enabled === true) {
    ctx.modifiers = Object(value) === value
      ? {
        stop: value.stop === true || modifiers.stop === true,
        center: value.center === true || modifiers.center === true,
        color: value.color || arg
      }
      : {
        stop: modifiers.stop,
        center: modifiers.center,
        color: arg
      };
  }
}

var Ripple = {
  name: 'ripple',

  inserted (el, binding) {
    const ctx = {
      modifiers: {},

      click (evt) {
        // on ENTER in form IE emits a PointerEvent with negative client cordinates
        if (ctx.enabled === true && (Platform.is.ie !== true || evt.clientX >= 0)) {
          showRipple(evt, el, ctx, evt.qKeyEvent === true);
        }
      },

      keyup (evt) {
        if (ctx.enabled === true && evt.keyCode === 13 && evt.qKeyEvent !== true) {
          showRipple(evt, el, ctx, true);
        }
      }
    };

    updateCtx(ctx, binding);

    if (el.__qripple) {
      el.__qripple_old = el.__qripple;
    }

    el.__qripple = ctx;
    el.addEventListener('click', ctx.click);
    el.addEventListener('keyup', ctx.keyup);
  },

  update (el, binding) {
    el.__qripple !== void 0 && updateCtx(el.__qripple, binding);
  },

  unbind (el) {
    const ctx = el.__qripple_old || el.__qripple;
    if (ctx !== void 0) {
      ctx.abort !== void 0 && ctx.abort();
      el.removeEventListener('click', ctx.click);
      el.removeEventListener('keyup', ctx.keyup);
      delete el[el.__qripple_old ? '__qripple_old' : '__qripple'];
    }
  }
};

var RippleMixin = {
  directives: {
    Ripple
  },

  props: {
    ripple: {
      type: [Boolean, Object],
      default: true
    }
  }
};

const sizes = {
  xs: 8,
  sm: 10,
  md: 14,
  lg: 20,
  xl: 24
};

var BtnMixin = {
  mixins: [ RippleMixin, AlignMixin ],

  props: {
    type: String,
    to: [Object, String],
    replace: Boolean,

    label: [Number, String],
    icon: String,
    iconRight: String,

    round: Boolean,
    outline: Boolean,
    flat: Boolean,
    unelevated: Boolean,
    rounded: Boolean,
    push: Boolean,
    glossy: Boolean,

    size: String,
    fab: Boolean,
    fabMini: Boolean,

    color: String,
    textColor: String,
    noCaps: Boolean,
    noWrap: Boolean,
    dense: Boolean,

    tabindex: [Number, String],

    align: { default: 'center' },
    stack: Boolean,
    stretch: Boolean,
    loading: {
      type: Boolean,
      default: null
    },
    disable: Boolean
  },

  computed: {
    style () {
      if (this.size && !this.fab && !this.fabMini) {
        return {
          fontSize: this.size in sizes ? `${sizes[this.size]}px` : this.size
        }
      }
    },

    isRound () {
      return this.round === true || this.fab === true || this.fabMini === true
    },

    isDisabled () {
      return this.disable === true || this.loading === true
    },

    computedTabIndex () {
      return this.isDisabled === true ? -1 : this.tabindex || 0
    },

    hasRouterLink () {
      return this.disable !== true && this.to !== void 0 && this.to !== null && this.to !== ''
    },

    isLink () {
      return this.type === 'a' || this.hasRouterLink === true
    },

    design () {
      if (this.flat === true) return 'flat'
      if (this.outline === true) return 'outline'
      if (this.push === true) return 'push'
      if (this.unelevated === true) return 'unelevated'
      return 'standard'
    },

    attrs () {
      const att = { tabindex: this.computedTabIndex };
      if (this.type !== 'a') {
        att.type = this.type || 'button';
      }
      if (this.hasRouterLink === true) {
        att.href = this.$router.resolve(this.to).href;
      }
      if (this.isDisabled === true) {
        att.disabled = true;
      }
      return att
    },

    classes () {
      let colors;

      if (this.color !== void 0) {
        if (this.flat === true || this.outline === true) {
          colors = `text-${this.textColor || this.color}`;
        }
        else {
          colors = `bg-${this.color} text-${this.textColor || 'white'}`;
        }
      }
      else if (this.textColor) {
        colors = `text-${this.textColor}`;
      }

      return `q-btn--${this.design} q-btn--${this.isRound === true ? 'round' : 'rectangle'}` +
        (colors !== void 0 ? ' ' + colors : '') +
        (this.isDisabled !== true ? ' q-focusable q-hoverable' : ' disabled') +
        (this.fab === true ? ' q-btn--fab' : (this.fabMini === true ? ' q-btn--fab-mini' : '')) +
        (this.noCaps === true ? ' q-btn--no-uppercase' : '') +
        (this.rounded === true ? ' q-btn--rounded' : '') +
        (this.dense === true ? ' q-btn--dense' : '') +
        (this.stretch === true ? ' no-border-radius self-stretch' : '') +
        (this.glossy === true ? ' glossy' : '')
    },

    innerClasses () {
      return this.alignClass + (this.stack === true ? ' column' : ' row') +
        (this.noWrap === true ? ' no-wrap text-no-wrap' : '') +
        (this.loading === true ? ' q-btn__content--hidden' : '')
    }
  }
};

var QBtn = Vue.extend({
  name: 'QBtn',

  mixins: [ BtnMixin ],

  props: {
    percentage: Number,
    darkPercentage: Boolean
  },

  computed: {
    hasLabel () {
      return this.label !== void 0 && this.label !== null && this.label !== ''
    }
  },

  methods: {
    click (e) {
      if (this.pressed === true) { return }

      if (e !== void 0) {
        // focus button if it came from ENTER on form
        // prevent the new submit (already done)
        if (this.type === 'submit') {
          if (
            (document.activeElement !== document.body && this.$el.contains(document.activeElement) === false) ||
            (this.$q.platform.is.ie === true && e.clientX < 0)
          ) {
            stopAndPrevent(e);
            this.$el.focus();
            return
          }
        }

        if (e.qKeyEvent !== true && this.$refs.blurTarget !== void 0) {
          this.$refs.blurTarget.focus();
        }

        if (e.defaultPrevented === true) {
          return
        }

        this.hasRouterLink === true && stopAndPrevent(e);
      }

      const go = () => {
        this.$router[this.replace === true ? 'replace' : 'push'](this.to);
      };

      this.$emit('click', e, go);
      this.hasRouterLink === true && e.navigate !== false && go();
    },

    __onKeydown (e) {
      if ([13, 32].includes(e.keyCode) === true) {
        // focus external button if the focus helper was focused before
        this.$el.focus();

        stopAndPrevent(e);

        if (this.pressed !== true) {
          this.pressed = true;
          this.$el.classList.add('q-btn--active');
          document.addEventListener('keyup', this.__onKeyupAbort);
        }
      }

      this.$emit('keydown', e);
    },

    __onKeyup (e) {
      if ([13, 32].includes(e.keyCode) === true) {
        this.__onKeyupAbort();

        // for click trigger
        const evt = new MouseEvent('click', e);
        evt.qKeyEvent = true;
        e.defaultPrevented === true && evt.preventDefault();
        this.$el.dispatchEvent(evt);

        stopAndPrevent(e);

        // for ripple
        e.qKeyEvent = true;
      }

      this.$emit('keyup', e);
    },

    __onKeyupAbort () {
      this.pressed = false;
      document.removeEventListener('keyup', this.__onKeyupAbort);
      this.$el && this.$el.classList.remove('q-btn--active');
    }
  },

  beforeDestroy () {
    document.removeEventListener('keyup', this.__onKeyupAbort);
  },

  render (h) {
    const
      inner = [].concat(slot(this, 'default')),
      data = {
        staticClass: 'q-btn inline q-btn-item non-selectable',
        class: this.classes,
        style: this.style,
        attrs: this.attrs
      };

    if (this.isDisabled === false) {
      data.on = {
        ...this.$listeners,
        click: this.click,
        keydown: this.__onKeydown,
        keyup: this.__onKeyup
      };

      if (this.ripple !== false) {
        data.directives = [{
          name: 'ripple',
          value: this.ripple,
          modifiers: { center: this.isRound }
        }];
      }
    }

    if (this.hasLabel === true) {
      inner.unshift(
        h('div', [ this.label ])
      );
    }

    if (this.icon !== void 0) {
      inner.unshift(
        h(QIcon, {
          props: { name: this.icon, left: this.stack === false && this.hasLabel === true }
        })
      );
    }

    if (this.iconRight !== void 0 && this.isRound === false) {
      inner.push(
        h(QIcon, {
          props: { name: this.iconRight, right: this.stack === false && this.hasLabel === true }
        })
      );
    }

    return h(this.isLink ? 'a' : 'button', data, [
      h('div', {
        staticClass: 'q-focus-helper',
        ref: 'blurTarget',
        attrs: { tabindex: -1 }
      }),

      this.loading === true && this.percentage !== void 0
        ? h('div', {
          staticClass: 'q-btn__progress absolute-full',
          class: this.darkPercentage ? 'q-btn__progress--dark' : null,
          style: { transform: `scale3d(${this.percentage / 100},1,1)` }
        })
        : null,

      h('div', {
        staticClass: 'q-btn__content text-center col items-center q-anchor--skip',
        class: this.innerClasses
      }, inner),

      this.loading !== null
        ? h('transition', {
          props: { name: 'q-transition--fade' }
        }, this.loading === true ? [
          h('div', {
            key: 'loading',
            staticClass: 'absolute-full flex flex-center'
          }, this.$scopedSlots.loading !== void 0 ? this.$scopedSlots.loading() : [ h(QSpinner) ])
        ] : void 0)
        : null
    ])
  }
});

var QBtnGroup = Vue.extend({
  name: 'QBtnGroup',

  props: {
    unelevated: Boolean,
    outline: Boolean,
    flat: Boolean,
    rounded: Boolean,
    push: Boolean,
    stretch: Boolean,
    glossy: Boolean,
    spread: Boolean
  },

  computed: {
    classes () {
      return ['unelevated', 'outline', 'flat', 'rounded', 'push', 'stretch', 'glossy']
        .filter(t => this[t] === true)
        .map(t => `q-btn-group--${t}`).join(' ')
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-btn-group row no-wrap ' +
        (this.spread === true ? 'q-btn-group--spread' : 'inline'),
      class: this.classes,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

function clearSelection () {
  if (window.getSelection !== void 0) {
    const selection = window.getSelection();
    if (selection.empty !== void 0) {
      selection.empty();
    }
    else if (selection.removeAllRanges !== void 0) {
      selection.removeAllRanges();
      Platform.is.mobile !== true && selection.addRange(document.createRange());
    }
  }
  else if (document.selection !== void 0) {
    document.selection.empty();
  }
}

var AnchorMixin = {
  props: {
    target: {
      type: [Boolean, String],
      default: true
    },
    contextMenu: Boolean
  },

  watch: {
    contextMenu (val) {
      if (this.anchorEl !== void 0) {
        this.__unconfigureAnchorEl(!val);
        this.__configureAnchorEl(val);
      }
    },

    target () {
      if (this.anchorEl !== void 0) {
        this.__unconfigureAnchorEl();
      }

      this.__pickAnchorEl();
    }
  },

  methods: {
    __showCondition (evt) {
      // abort with no parent configured or on multi-touch
      if (this.anchorEl === void 0) {
        return false
      }
      if (evt === void 0) {
        return true
      }
      return evt.touches === void 0 || evt.touches.length <= 1
    },

    __contextClick (evt) {
      this.hide(evt);
      this.show(evt);
      prevent(evt);
    },

    __toggleKey (evt) {
      if (evt !== void 0 && evt.keyCode === 13 && evt.qKeyEvent !== true) {
        this.toggle(evt);
      }
    },

    __mobileTouch (evt) {
      this.__mobileCleanup(evt);

      if (this.__showCondition(evt) !== true) {
        return
      }

      this.hide(evt);
      this.anchorEl.classList.add('non-selectable');

      this.touchTimer = setTimeout(() => {
        this.show(evt);
      }, 300);
    },

    __mobileCleanup (evt) {
      this.anchorEl.classList.remove('non-selectable');
      clearTimeout(this.touchTimer);

      if (this.showing === true && evt !== void 0) {
        clearSelection();
        prevent(evt);
      }
    },

    __unconfigureAnchorEl (context = this.contextMenu) {
      if (context === true) {
        if (this.$q.platform.is.mobile) {
          this.anchorEl.removeEventListener('touchstart', this.__mobileTouch)
          ;['touchcancel', 'touchmove', 'touchend'].forEach(evt => {
            this.anchorEl.removeEventListener(evt, this.__mobileCleanup);
          });
        }
        else {
          this.anchorEl.removeEventListener('click', this.hide);
          this.anchorEl.removeEventListener('contextmenu', this.__contextClick);
        }
      }
      else {
        this.anchorEl.removeEventListener('click', this.toggle);
        this.anchorEl.removeEventListener('keyup', this.__toggleKey);
      }
    },

    __configureAnchorEl (context = this.contextMenu) {
      if (this.noParentEvent === true) { return }

      if (context === true) {
        if (this.$q.platform.is.mobile) {
          this.anchorEl.addEventListener('touchstart', this.__mobileTouch)
          ;['touchcancel', 'touchmove', 'touchend'].forEach(evt => {
            this.anchorEl.addEventListener(evt, this.__mobileCleanup);
          });
        }
        else {
          this.anchorEl.addEventListener('click', this.hide);
          this.anchorEl.addEventListener('contextmenu', this.__contextClick);
        }
      }
      else {
        this.anchorEl.addEventListener('click', this.toggle);
        this.anchorEl.addEventListener('keyup', this.__toggleKey);
      }
    },

    __setAnchorEl (el) {
      this.anchorEl = el;
      while (this.anchorEl.classList.contains('q-anchor--skip')) {
        this.anchorEl = this.anchorEl.parentNode;
      }
      this.__configureAnchorEl();
    },

    __pickAnchorEl () {
      if (this.target && typeof this.target === 'string') {
        const el = document.querySelector(this.target);
        if (el !== null) {
          this.anchorEl = el;
          this.__configureAnchorEl();
        }
        else {
          this.anchorEl = void 0;
          console.error(`Anchor: target "${this.target}" not found`, this);
        }
      }
      else if (this.target !== false) {
        this.__setAnchorEl(this.parentEl);
      }
      else {
        this.anchorEl = void 0;
      }
    }
  },

  mounted () {
    this.parentEl = this.$el.parentNode;

    this.$nextTick(() => {
      this.__pickAnchorEl();

      if (this.value === true) {
        if (this.anchorEl === void 0) {
          this.$emit('input', false);
        }
        else {
          this.show();
        }
      }
    });
  },

  beforeDestroy () {
    clearTimeout(this.touchTimer);
    this.__anchorCleanup !== void 0 && this.__anchorCleanup();

    if (this.anchorEl !== void 0) {
      this.__unconfigureAnchorEl();
    }
  }
};

var ModelToggleMixin = {
  props: {
    value: Boolean
  },

  data () {
    return {
      showing: false
    }
  },

  watch: {
    value (val) {
      if (this.disable === true && val === true) {
        this.$emit('input', false);
        return
      }

      if (val !== this.showing) {
        this[val ? 'show' : 'hide']();
      }
    }
  },

  methods: {
    toggle (evt) {
      this[this.showing === true ? 'hide' : 'show'](evt);
    },

    show (evt) {
      if (this.disable === true || this.showing === true) {
        return
      }
      if (this.__showCondition !== void 0 && this.__showCondition(evt) !== true) {
        return
      }

      this.$emit('before-show', evt);
      this.showing = true;
      this.$emit('input', true);

      if (this.$options.modelToggle !== void 0 && this.$options.modelToggle.history === true) {
        this.__historyEntry = {
          handler: this.hide
        };
        History.add(this.__historyEntry);
      }

      if (this.__show !== void 0) {
        this.__show(evt);
      }
      else {
        this.$emit('show', evt);
      }
    },

    hide (evt) {
      if (this.disable === true || this.showing === false) {
        return
      }

      this.$emit('before-hide', evt);
      this.showing = false;
      this.value !== false && this.$emit('input', false);

      this.__removeHistory();

      if (this.__hide !== void 0) {
        this.__hide(evt);
      }
      else {
        this.$emit('hide', evt);
      }
    },

    __removeHistory () {
      if (this.__historyEntry !== void 0) {
        History.remove(this.__historyEntry);
        this.__historyEntry = void 0;
      }
    }
  },

  beforeDestroy () {
    this.showing === true && this.__removeHistory();
  }
};

let inject;

function fillInject (root) {
  const
    options = (new Vue()).$root.$options,
    skip = [ 'el', 'methods', 'render', 'mixins' ]
      .concat(Vue.config._lifecycleHooks)
      .concat(Object.keys(options).filter(key => options[key] !== null));

  inject = {};

  Object.keys(root)
    .filter(name => skip.includes(name) === false)
    .forEach(p => {
      inject[p] = root[p];
    });
}

function getVm (root, vm) {
  inject === void 0 && root !== void 0 && fillInject(root.$root.$options);
  return new Vue(inject !== void 0 ? { ...inject, ...vm } : vm)
}

function getAllChildren (vm) {
  let children = vm.$children;
  vm.$children.forEach(child => {
    if (child.$children.length > 0) {
      children = children.concat(getAllChildren(child));
    }
  });
  return children
}

var PortalMixin = {
  inheritAttrs: false,

  props: {
    contentClass: [Array, String, Object],
    contentStyle: [Array, String, Object]
  },

  methods: {
    __showPortal () {
      if (this.__portal !== void 0 && this.__portal.showing !== true) {
        document.body.appendChild(this.__portal.$el);
        this.__portal.showing = true;
      }
    },

    __hidePortal () {
      if (this.__portal !== void 0 && this.__portal.showing === true) {
        this.__portal.$el.remove();
        this.__portal.showing = false;
      }
    }
  },

  render () {
    this.__portal !== void 0 && this.__portal.$forceUpdate();
  },

  beforeMount () {
    const obj = {
      inheritAttrs: false,

      render: h => this.__render(h),

      components: this.$options.components,
      directives: this.$options.directives
    };

    if (this.__onPortalClose !== void 0) {
      obj.methods = {
        __qClosePopup: this.__onPortalClose
      };
    }

    const onCreated = this.__onPortalCreated;

    if (onCreated !== void 0) {
      obj.created = function () {
        onCreated(this);
      };
    }

    this.__portal = getVm(this, obj).$mount();
  },

  beforeDestroy () {
    this.__portal.$destroy();
    this.__portal.$el.remove();
    this.__portal = void 0;
  }
};

var TransitionMixin = {
  props: {
    transitionShow: {
      type: String,
      default: 'fade'
    },

    transitionHide: {
      type: String,
      default: 'fade'
    }
  },

  data () {
    return {
      transitionState: this.showing
    }
  },

  watch: {
    showing (val) {
      this.transitionShow !== this.transitionHide && this.$nextTick(() => {
        this.transitionState = val;
      });
    }
  },

  computed: {
    transition () {
      return 'q-transition--' + (this.transitionState === true ? this.transitionHide : this.transitionShow)
    }
  }
};

const evtOpts = listenOpts.notPassiveCapture;

var ClickOutside = {
  name: 'click-outside',

  bind (el, { value, arg }) {
    const ctx = {
      trigger: value,
      handler (evt) {
        const target = evt && evt.target;

        if (target && target !== document.body) {
          const related = arg !== void 0
            ? [ ...arg, el ]
            : [ el ];

          for (let i = related.length - 1; i >= 0; i--) {
            if (related[i].contains(target)) {
              return
            }
          }

          let parent = target;
          while (parent !== document.body) {
            if (parent.classList.contains('q-menu') || parent.classList.contains('q-dialog')) {
              let sibling = parent;
              while ((sibling = sibling.previousElementSibling) !== null) {
                if (sibling.contains(el)) {
                  return
                }
              }
            }
            parent = parent.parentNode;
          }
        }

        // prevent accidental click/tap on something else
        // that has a trigger --> improves UX
        Platform.is.mobile === true && stopAndPrevent(evt);

        ctx.trigger(evt);
      }
    };

    if (el.__qclickoutside) {
      el.__qclickoutside_old = el.__qclickoutside;
    }

    el.__qclickoutside = ctx;
    document.body.addEventListener('mousedown', ctx.handler, evtOpts);
    document.body.addEventListener('touchstart', ctx.handler, evtOpts);
    Platform.is.desktop === true && document.body.addEventListener('focusin', ctx.handler, evtOpts);
  },

  update (el, { value, oldValue }) {
    if (value !== oldValue) {
      el.__qclickoutside.trigger = value;
    }
  },

  unbind (el) {
    const ctx = el.__qclickoutside_old || el.__qclickoutside;
    if (ctx !== void 0) {
      document.body.removeEventListener('mousedown', ctx.handler, evtOpts);
      document.body.removeEventListener('touchstart', ctx.handler, evtOpts);
      Platform.is.desktop === true && document.body.removeEventListener('focusin', ctx.handler, evtOpts);
      delete el[el.__qclickoutside_old ? '__qclickoutside_old' : '__qclickoutside'];
    }
  }
};

function s4 () {
  return Math.floor((1 + Math.random()) * 0x10000)
    .toString(16)
    .substring(1)
}

function uid$1 () {
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
    s4() + '-' + s4() + s4() + s4()
}

function getScrollTarget (el) {
  return el.closest('.scroll,.scroll-y,.overflow-auto') || window
}

function getScrollHeight (el) {
  return (el === window ? document.body : el).scrollHeight
}

function getScrollPosition (scrollTarget) {
  if (scrollTarget === window) {
    return window.pageYOffset || window.scrollY || document.body.scrollTop || 0
  }
  return scrollTarget.scrollTop
}

function getHorizontalScrollPosition (scrollTarget) {
  if (scrollTarget === window) {
    return window.pageXOffset || window.scrollX || document.body.scrollLeft || 0
  }
  return scrollTarget.scrollLeft
}

function animScrollTo (el, to, duration) {
  const pos = getScrollPosition(el);

  if (duration <= 0) {
    if (pos !== to) {
      setScroll(el, to);
    }
    return
  }

  requestAnimationFrame(() => {
    const newPos = pos + (to - pos) / Math.max(16, duration) * 16;
    setScroll(el, newPos);
    if (newPos !== to) {
      animScrollTo(el, to, duration - 16);
    }
  });
}

function animHorizontalScrollTo (el, to, duration) {
  const pos = getHorizontalScrollPosition(el);

  if (duration <= 0) {
    if (pos !== to) {
      setHorizontalScroll(el, to);
    }
    return
  }

  requestAnimationFrame(() => {
    const newPos = pos + (to - pos) / Math.max(16, duration) * 16;
    setHorizontalScroll(el, newPos);
    if (newPos !== to) {
      animHorizontalScrollTo(el, to, duration - 16);
    }
  });
}

function setScroll (scrollTarget, offset) {
  if (scrollTarget === window) {
    window.scrollTo(0, offset);
    return
  }
  scrollTarget.scrollTop = offset;
}

function setHorizontalScroll (scrollTarget, offset) {
  if (scrollTarget === window) {
    window.scrollTo(offset, 0);
    return
  }
  scrollTarget.scrollLeft = offset;
}

function setScrollPosition (scrollTarget, offset, duration) {
  if (duration) {
    animScrollTo(scrollTarget, offset, duration);
    return
  }
  setScroll(scrollTarget, offset);
}

function setHorizontalScrollPosition (scrollTarget, offset, duration) {
  if (duration) {
    animHorizontalScrollTo(scrollTarget, offset, duration);
    return
  }
  setHorizontalScroll(scrollTarget, offset);
}

let size;
function getScrollbarWidth () {
  if (size !== undefined) {
    return size
  }

  const
    inner = document.createElement('p'),
    outer = document.createElement('div');

  css(inner, {
    width: '100%',
    height: '200px'
  });
  css(outer, {
    position: 'absolute',
    top: '0px',
    left: '0px',
    visibility: 'hidden',
    width: '200px',
    height: '150px',
    overflow: 'hidden'
  });

  outer.appendChild(inner);

  document.body.appendChild(outer);

  let w1 = inner.offsetWidth;
  outer.style.overflow = 'scroll';
  let w2 = inner.offsetWidth;

  if (w1 === w2) {
    w2 = outer.clientWidth;
  }

  outer.remove();
  size = w1 - w2;

  return size
}

function hasScrollbar (el, onY = true) {
  if (!el || el.nodeType !== Node.ELEMENT_NODE) {
    return false
  }

  return onY
    ? (
      el.scrollHeight > el.clientHeight && (
        el.classList.contains('scroll') ||
        el.classList.contains('overflow-auto') ||
        ['auto', 'scroll'].includes(window.getComputedStyle(el)['overflow-y'])
      )
    )
    : (
      el.scrollWidth > el.clientWidth && (
        el.classList.contains('scroll') ||
        el.classList.contains('overflow-auto') ||
        ['auto', 'scroll'].includes(window.getComputedStyle(el)['overflow-x'])
      )
    )
}

let handlers = [];

var EscapeKey = {
  __install () {
    this.__installed = true;
    window.addEventListener('keyup', evt => {
      if (
        handlers.length !== 0 &&
        (evt.which === 27 || evt.keyCode === 27)
      ) {
        handlers[handlers.length - 1].fn(evt);
      }
    });
  },

  register (comp, fn) {
    if (Platform.is.desktop === true) {
      this.__installed !== true && this.__install();
      handlers.push({ comp, fn });
    }
  },

  pop (comp) {
    if (Platform.is.desktop === true) {
      const index = handlers.findIndex(h => h.comp === comp);
      if (index > -1) {
        handlers.splice(index, 1);
      }
    }
  }
};

const
  bus = new Vue(),
  tree = {},
  rootHide = {};

/*
 * Tree has (key: value) entries where
 *
 *    key: menuId
 *
 *    value --> (true / menuId)
 *       true --- means has no sub-menu opened
 *       menuId --- menuId of the sub-menu that is currently opened
 *
 */

function closeRootMenu (id) {
  while (tree[id] !== void 0) {
    const res = Object.keys(tree).find(key => tree[key] === id);
    if (res !== void 0) {
      id = res;
    }
    else {
      rootHide[id] !== void 0 && rootHide[id]();
      return true
    }
  }
}

const MenuTreeMixin = {
  methods: {
    __registerTree () {
      tree[this.menuId] = true;

      if (this.$root.menuParentId === void 0) {
        rootHide[this.menuId] = this.hide;
        return
      }

      if (tree[this.$root.menuParentId] !== true) {
        bus.$emit('hide', tree[this.$root.menuParentId]);
      }

      bus.$on('hide', this.__processEvent);
      tree[this.$root.menuParentId] = this.menuId;
    },

    __unregisterTree () {
      // if it hasn't been registered or already unregistered (beforeDestroy)
      if (tree[this.menuId] === void 0) {
        return
      }

      delete rootHide[this.menuId];

      if (this.$root.menuParentId !== void 0) {
        bus.$off('hide', this.__processEvent);
      }

      const child = tree[this.menuId];

      delete tree[this.menuId];

      if (child !== true) {
        bus.$emit('hide', child);
      }
    },

    __processEvent (id) {
      this.menuId === id && this.hide();
    }
  }
};

function validatePosition (pos) {
  let parts = pos.split(' ');
  if (parts.length !== 2) {
    return false
  }
  if (!['top', 'center', 'bottom'].includes(parts[0])) {
    console.error('Anchor/Self position must start with one of top/center/bottom');
    return false
  }
  if (!['left', 'middle', 'right'].includes(parts[1])) {
    console.error('Anchor/Self position must end with one of left/middle/right');
    return false
  }
  return true
}

function validateOffset (val) {
  if (!val) { return true }
  if (val.length !== 2) { return false }
  if (typeof val[0] !== 'number' || typeof val[1] !== 'number') {
    return false
  }
  return true
}

function parsePosition (pos) {
  let parts = pos.split(' ');
  return { vertical: parts[0], horizontal: parts[1] }
}

function getAnchorProps (el, offset) {
  let { top, left, right, bottom, width, height } = el.getBoundingClientRect();

  if (offset !== void 0) {
    top -= offset[1];
    left -= offset[0];
    bottom += offset[1];
    right += offset[0];

    width += offset[0];
    height += offset[1];
  }

  return {
    top,
    left,
    right,
    bottom,
    width,
    height,
    middle: left + (right - left) / 2,
    center: top + (bottom - top) / 2
  }
}

function getTargetProps (el) {
  return {
    top: 0,
    center: el.offsetHeight / 2,
    bottom: el.offsetHeight,
    left: 0,
    middle: el.offsetWidth / 2,
    right: el.offsetWidth
  }
}

// cfg: { el, anchorEl, anchorOrigin, selfOrigin, offset, absoluteOffset, cover, fit, maxHeight, maxWidth }
function setPosition (cfg) {
  let anchorProps;

  // scroll position might change
  // if max-height changes, so we
  // need to restore it after we calculate
  // the new positioning
  const scrollTop = cfg.el.scrollTop;

  cfg.el.style.maxHeight = cfg.maxHeight;
  cfg.el.style.maxWidth = cfg.maxWidth;

  if (cfg.absoluteOffset === void 0) {
    anchorProps = getAnchorProps(cfg.anchorEl, cfg.cover === true ? [0, 0] : cfg.offset);
  }
  else {
    const
      { top: anchorTop, left: anchorLeft } = cfg.anchorEl.getBoundingClientRect(),
      top = anchorTop + cfg.absoluteOffset.top,
      left = anchorLeft + cfg.absoluteOffset.left;

    anchorProps = { top, left, width: 1, height: 1, right: left + 1, center: top, middle: left, bottom: top + 1 };
  }

  if (cfg.fit === true || cfg.cover === true) {
    cfg.el.style.minWidth = anchorProps.width + 'px';
    if (cfg.cover === true) {
      cfg.el.style.minHeight = anchorProps.height + 'px';
    }
  }

  const
    targetProps = getTargetProps(cfg.el),
    props = {
      top: anchorProps[cfg.anchorOrigin.vertical] - targetProps[cfg.selfOrigin.vertical],
      left: anchorProps[cfg.anchorOrigin.horizontal] - targetProps[cfg.selfOrigin.horizontal]
    };

  applyBoundaries(props, anchorProps, targetProps, cfg.anchorOrigin, cfg.selfOrigin);

  cfg.el.style.top = Math.max(0, Math.floor(props.top)) + 'px';
  cfg.el.style.left = Math.max(0, Math.floor(props.left)) + 'px';

  if (props.maxHeight !== void 0) {
    cfg.el.style.maxHeight = Math.floor(props.maxHeight) + 'px';
  }
  if (props.maxWidth !== void 0) {
    cfg.el.style.maxWidth = Math.floor(props.maxWidth) + 'px';
  }

  // restore scroll position
  if (cfg.el.scrollTop !== scrollTop) {
    cfg.el.scrollTop = scrollTop;
  }
}

function applyBoundaries (props, anchorProps, targetProps, anchorOrigin, selfOrigin) {
  const margin = getScrollbarWidth();
  let { innerHeight, innerWidth } = window;

  // don't go bellow scrollbars
  innerHeight -= margin;
  innerWidth -= margin;

  if (props.top < 0 || props.top + targetProps.bottom > innerHeight) {
    if (selfOrigin.vertical === 'center') {
      props.top = anchorProps[selfOrigin.vertical] > innerHeight / 2
        ? innerHeight - targetProps.bottom
        : 0;
      props.maxHeight = Math.min(targetProps.bottom, innerHeight);
    }
    else if (anchorProps[selfOrigin.vertical] > innerHeight / 2) {
      const anchorY = Math.min(
        innerHeight,
        anchorOrigin.vertical === 'center'
          ? anchorProps.center
          : (anchorOrigin.vertical === selfOrigin.vertical ? anchorProps.bottom : anchorProps.top)
      );
      props.maxHeight = Math.min(targetProps.bottom, anchorY);
      props.top = Math.max(0, anchorY - props.maxHeight);
    }
    else {
      props.top = anchorOrigin.vertical === 'center'
        ? anchorProps.center
        : (anchorOrigin.vertical === selfOrigin.vertical ? anchorProps.top : anchorProps.bottom);
      props.maxHeight = Math.min(targetProps.bottom, innerHeight - props.top);
    }
  }

  if (props.left < 0 || props.left + targetProps.right > innerWidth) {
    props.maxWidth = Math.min(targetProps.right, innerWidth);
    if (selfOrigin.horizontal === 'middle') {
      props.left = anchorProps[selfOrigin.horizontal] > innerWidth / 2 ? innerWidth - targetProps.right : 0;
    }
    else if (anchorProps[selfOrigin.horizontal] > innerWidth / 2) {
      const anchorX = Math.min(
        innerWidth,
        anchorOrigin.horizontal === 'middle'
          ? anchorProps.center
          : (anchorOrigin.horizontal === selfOrigin.horizontal ? anchorProps.right : anchorProps.left)
      );
      props.maxWidth = Math.min(targetProps.right, anchorX);
      props.left = Math.max(0, anchorX - props.maxWidth);
    }
    else {
      props.left = anchorOrigin.horizontal === 'middle'
        ? anchorProps.center
        : (anchorOrigin.horizontal === selfOrigin.horizontal ? anchorProps.left : anchorProps.right);
      props.maxWidth = Math.min(targetProps.right, innerWidth - props.left);
    }
  }
}

var QMenu = Vue.extend({
  name: 'QMenu',

  mixins: [ AnchorMixin, ModelToggleMixin, PortalMixin, MenuTreeMixin, TransitionMixin ],

  directives: {
    ClickOutside
  },

  props: {
    persistent: Boolean,
    autoClose: Boolean,

    noParentEvent: Boolean,
    noRefocus: Boolean,
    noFocus: Boolean,

    fit: Boolean,
    cover: Boolean,

    square: Boolean,

    anchor: {
      type: String,
      validator: validatePosition
    },
    self: {
      type: String,
      validator: validatePosition
    },
    offset: {
      type: Array,
      validator: validateOffset
    },

    touchPosition: Boolean,

    maxHeight: {
      type: String,
      default: null
    },
    maxWidth: {
      type: String,
      default: null
    }
  },

  data () {
    return {
      menuId: uid$1()
    }
  },

  computed: {
    horizSide () {
      return this.$q.lang.rtl ? 'right' : 'left'
    },

    anchorOrigin () {
      return parsePosition(
        this.anchor || (
          this.cover === true ? `center middle` : `bottom ${this.horizSide}`
        )
      )
    },

    selfOrigin () {
      return this.cover === true
        ? this.anchorOrigin
        : parsePosition(this.self || `top ${this.horizSide}`)
    },

    menuClass () {
      return this.square === true ? ' q-menu--square' : ''
    }
  },

  watch: {
    noParentEvent (val) {
      if (this.anchorEl !== void 0) {
        if (val === true) {
          this.__unconfigureAnchorEl();
        }
        else {
          this.__configureAnchorEl();
        }
      }
    }
  },

  methods: {
    focus () {
      let node = this.__portal.$refs !== void 0 ? this.__portal.$refs.inner : void 0;

      if (node !== void 0 && node.contains(document.activeElement) !== true) {
        node = node.querySelector('[autofocus]') || node;
        node.focus();
      }
    },

    __show (evt) {
      clearTimeout(this.timer);

      this.__refocusTarget = this.noRefocus === false
        ? document.activeElement
        : void 0;

      this.scrollTarget = getScrollTarget(this.anchorEl);
      this.scrollTarget.addEventListener('scroll', this.updatePosition, listenOpts.passive);
      if (this.scrollTarget !== window) {
        window.addEventListener('scroll', this.updatePosition, listenOpts.passive);
      }

      EscapeKey.register(this, () => {
        this.$emit('escape-key');
        this.hide();
      });

      this.__showPortal();
      this.__registerTree();

      this.timer = setTimeout(() => {
        const { top, left } = this.anchorEl.getBoundingClientRect();

        if (this.touchPosition || this.contextMenu) {
          const pos = position(evt);
          this.absoluteOffset = { left: pos.left - left, top: pos.top - top };
        }
        else {
          this.absoluteOffset = void 0;
        }

        this.updatePosition();

        if (this.unwatch === void 0) {
          this.unwatch = this.$watch('$q.screen.width', this.updatePosition);
        }

        this.$el.dispatchEvent(create('popup-show', { bubbles: true }));

        if (this.noFocus !== true) {
          document.activeElement.blur();

          this.$nextTick(() => {
            this.focus();
          });
        }

        this.timer = setTimeout(() => {
          this.$emit('show', evt);
        }, 300);
      }, 0);
    },

    __hide (evt) {
      this.__anchorCleanup(true);

      if (this.__refocusTarget !== void 0) {
        this.__refocusTarget.focus();
      }

      this.$el.dispatchEvent(create('popup-hide', { bubbles: true }));

      this.timer = setTimeout(() => {
        this.__hidePortal();
        this.$emit('hide', evt);
      }, 300);
    },

    __anchorCleanup (hiding) {
      clearTimeout(this.timer);
      this.absoluteOffset = void 0;

      if (this.unwatch !== void 0) {
        this.unwatch();
        this.unwatch = void 0;
      }

      if (hiding === true || this.showing === true) {
        EscapeKey.pop(this);
        this.__unregisterTree();

        this.scrollTarget.removeEventListener('scroll', this.updatePosition, listenOpts.passive);
        if (this.scrollTarget !== window) {
          window.removeEventListener('scroll', this.updatePosition, listenOpts.passive);
        }
      }
    },

    __onAutoClose (e) {
      closeRootMenu(this.menuId);
      this.$emit('click', e);
    },

    updatePosition () {
      const el = this.__portal.$el;

      if (el.nodeType === 8) { // IE replaces the comment with delay
        setTimeout(() => {
          this.__portal !== void 0 && this.__portal.showing === true && this.updatePosition();
        }, 25);
        return
      }

      setPosition({
        el,
        offset: this.offset,
        anchorEl: this.anchorEl,
        anchorOrigin: this.anchorOrigin,
        selfOrigin: this.selfOrigin,
        absoluteOffset: this.absoluteOffset,
        fit: this.fit,
        cover: this.cover,
        maxHeight: this.maxHeight,
        maxWidth: this.maxWidth
      });
    },

    __render (h) {
      const on = {
        ...this.$listeners,
        input: stop
      };

      if (this.autoClose === true) {
        on.click = this.__onAutoClose;
      }

      return h('transition', {
        props: { name: this.transition }
      }, [
        this.showing === true ? h('div', {
          ref: 'inner',
          staticClass: 'q-menu scroll' + this.menuClass,
          class: this.contentClass,
          style: this.contentStyle,
          attrs: {
            tabindex: -1,
            ...this.$attrs
          },
          on,
          directives: this.persistent !== true ? [{
            name: 'click-outside',
            value: this.hide,
            arg: [ this.anchorEl ]
          }] : null
        }, slot(this, 'default')) : null
      ])
    },

    __onPortalCreated (vm) {
      vm.menuParentId = this.menuId;
    },

    __onPortalClose () {
      closeRootMenu(this.menuId);
    }
  }
});

var QBtnDropdown = Vue.extend({
  name: 'QBtnDropdown',

  mixins: [ BtnMixin ],

  props: {
    value: Boolean,
    split: Boolean,

    contentClass: [Array, String, Object],
    contentStyle: [Array, String, Object],

    cover: Boolean,
    persistent: Boolean,
    autoClose: Boolean,
    menuAnchor: {
      type: String,
      default: 'bottom right'
    },
    menuSelf: {
      type: String,
      default: 'top right'
    },

    disableMainBtn: Boolean,
    disableDropdown: Boolean
  },

  data () {
    return {
      showing: this.value
    }
  },

  watch: {
    value (val) {
      this.$refs.menu !== void 0 && this.$refs.menu[val ? 'show' : 'hide']();
    }
  },

  render (h) {
    const label = this.$scopedSlots.label !== void 0
      ? this.$scopedSlots.label()
      : [];

    const Arrow = [
      h(QIcon, {
        props: {
          name: this.$q.iconSet.arrow.dropdown
        },
        staticClass: 'q-btn-dropdown__arrow',
        class: {
          'rotate-180': this.showing,
          'q-btn-dropdown__arrow-container': this.split === false
        }
      })
    ];

    this.disableDropdown !== true && Arrow.push(
      h(QMenu, {
        ref: 'menu',
        props: {
          cover: this.cover,
          fit: true,
          persistent: this.persistent,
          autoClose: this.autoClose,
          anchor: this.menuAnchor,
          self: this.menuSelf,
          contentClass: this.contentClass,
          contentStyle: this.contentStyle
        },
        on: {
          'before-show': e => {
            this.showing = true;
            this.$emit('before-show', e);
          },
          show: e => {
            this.$emit('show', e);
            this.$emit('input', true);
          },
          'before-hide': e => {
            this.showing = false;
            this.$emit('before-hide', e);
          },
          hide: e => {
            this.$emit('hide', e);
            this.$emit('input', false);
          }
        }
      }, slot(this, 'default'))
    );

    if (this.split === false) {
      return h(QBtn, {
        class: 'q-btn-dropdown q-btn-dropdown--simple',
        props: {
          ...this.$props,
          disable: this.disable === true || this.disableMainBtn === true,
          noWrap: true
        },
        on: {
          click: e => {
            this.$emit('click', e);
          }
        }
      }, label.concat(Arrow))
    }

    const Btn = h(QBtn, {
      class: 'q-btn-dropdown--current',
      props: {
        ...this.$props,
        disable: this.disable === true || this.disableMainBtn === true,
        noWrap: true,
        iconRight: this.iconRight
      },
      on: {
        click: e => {
          this.hide();
          this.$emit('click', e);
        }
      }
    }, label);

    return h(QBtnGroup, {
      props: {
        outline: this.outline,
        flat: this.flat,
        rounded: this.rounded,
        push: this.push,
        unelevated: this.unelevated,
        glossy: this.glossy
      },
      staticClass: 'q-btn-dropdown q-btn-dropdown--split no-wrap q-btn-item',
      class: this.stretch === true ? 'self-stretch no-border-radius' : null
    }, [
      Btn,

      h(QBtn, {
        staticClass: 'q-btn-dropdown__arrow-container',
        props: {
          disable: this.disable === true || this.disableDropdown === true,
          outline: this.outline,
          flat: this.flat,
          rounded: this.rounded,
          push: this.push,
          size: this.size,
          color: this.color,
          textColor: this.textColor,
          dense: this.dense,
          ripple: this.ripple
        }
      }, Arrow)
    ])
  },

  methods: {
    toggle (evt) {
      this.$refs.menu && this.$refs.menu.toggle(evt);
    },
    show (evt) {
      this.$refs.menu && this.$refs.menu.show(evt);
    },
    hide (evt) {
      this.$refs.menu && this.$refs.menu.hide(evt);
    }
  },

  mounted () {
    this.value === true && this.show();
  }
});

Vue.extend({
  name: 'QBtnToggle',

  props: {
    value: {
      required: true
    },

    options: {
      type: Array,
      required: true,
      validator: v => v.every(
        opt => ('label' in opt || 'icon' in opt || 'slot' in opt) && 'value' in opt
      )
    },

    // To avoid seeing the active raise shadow through the transparent button, give it a color (even white).
    color: String,
    textColor: String,
    toggleColor: {
      type: String,
      default: 'primary'
    },
    toggleTextColor: String,

    outline: Boolean,
    flat: Boolean,
    unelevated: Boolean,
    rounded: Boolean,
    push: Boolean,
    glossy: Boolean,

    size: String,

    noCaps: Boolean,
    noWrap: Boolean,
    dense: Boolean,
    readonly: Boolean,
    disable: Boolean,

    stack: Boolean,
    stretch: Boolean,

    spread: Boolean,

    ripple: {
      type: [Boolean, Object],
      default: true
    }
  },

  computed: {
    val () {
      return this.options.map(opt => opt.value === this.value)
    }
  },

  methods: {
    set (value, opt) {
      if (this.readonly === false && value !== this.value) {
        this.$emit('input', value, opt);
      }
    }
  },

  render (h) {
    return h(QBtnGroup, {
      staticClass: 'q-btn-toggle',
      props: {
        outline: this.outline,
        flat: this.flat,
        rounded: this.rounded,
        push: this.push,
        stretch: this.stretch,
        unelevated: this.unelevated,
        glossy: this.glossy,
        spread: this.spread
      },
      on: this.$listeners
    },
    this.options.map(
      (opt, i) => h(QBtn, {
        key: i,
        on: { click: () => this.set(opt.value, opt) },
        props: {
          disable: this.disable || opt.disable,
          label: opt.label,
          // Colors come from the button specific options first, then from general props
          color: this.val[i] === true ? opt.toggleColor || this.toggleColor : opt.color || this.color,
          textColor: this.val[i] === true ? opt.toggleTextColor || this.toggleTextColor : opt.textColor || this.textColor,
          icon: opt.icon,
          iconRight: opt.iconRight,
          noCaps: this.noCaps === true || opt.noCaps === true,
          noWrap: this.noWrap === true || opt.noWrap === true,
          outline: this.outline,
          flat: this.flat,
          rounded: this.rounded,
          push: this.push,
          unelevated: this.unelevated,
          size: this.size,
          dense: this.dense,
          ripple: this.ripple || opt.ripple,
          stack: this.stack === true || opt.stack === true,
          tabindex: opt.tabindex,
          stretch: this.stretch
        }
      }, opt.slot !== void 0 ? slot(this, opt.slot) : void 0)
    ))
  }
});

var QCard = Vue.extend({
  name: 'QCard',

  props: {
    dark: Boolean,

    square: Boolean,
    flat: Boolean,
    bordered: Boolean
  },

  render (h) {
    return h('div', {
      staticClass: 'q-card',
      class: {
        'q-card--dark': this.dark,
        'q-card--bordered': this.bordered,
        'q-card--square no-border-radius': this.square,
        'q-card--flat no-shadow': this.flat
      },
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

var QCardSection = Vue.extend({
  name: 'QCardSection',

  render (h) {
    return h('div', {
      staticClass: 'q-card__section',
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

var QCardActions = Vue.extend({
  name: 'QCardActions',

  mixins: [ AlignMixin ],

  props: {
    vertical: Boolean
  },

  computed: {
    classes () {
      return `q-card__actions--${this.vertical === true ? 'vert column justify-start' : 'horiz row ' + this.alignClass}`
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-card__actions',
      class: this.classes,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

function setObserver (el, evt, ctx) {
  const target = evt.target;
  ctx.touchTargetObserver = new MutationObserver(() => {
    el.contains(target) === false && ctx.end(evt);
  });
  ctx.touchTargetObserver.observe(el, { childList: true, subtree: true });
}

function removeObserver (ctx) {
  if (ctx.touchTargetObserver !== void 0) {
    ctx.touchTargetObserver.disconnect();
    ctx.touchTargetObserver = void 0;
  }
}

function getDirection (mod) {
  let dir = {}

  ;['left', 'right', 'up', 'down', 'horizontal', 'vertical'].forEach(direction => {
    if (mod[direction]) {
      dir[direction] = true;
    }
  });

  if (Object.keys(dir).length === 0) {
    return {
      left: true, right: true, up: true, down: true, horizontal: true, vertical: true
    }
  }

  if (dir.horizontal) {
    dir.left = dir.right = true;
  }
  if (dir.vertical) {
    dir.up = dir.down = true;
  }
  if (dir.left && dir.right) {
    dir.horizontal = true;
  }
  if (dir.up && dir.down) {
    dir.vertical = true;
  }

  return dir
}

function parseArg (arg) {
  // delta (min velocity -- dist / time)
  // mobile min distance on first move
  // desktop min distance until deciding if it's a swipe or not
  const data = [0.06, 6, 50];

  if (typeof arg === 'string' && arg.length) {
    arg.split(':').forEach((val, index) => {
      const v = parseInt(val, 10);
      v && (data[index] = v);
    });
  }

  return data
}

var TouchSwipe = {
  name: 'touch-swipe',

  bind (el, binding) {
    const mouse = binding.modifiers.mouse === true;

    let ctx = {
      handler: binding.value,
      sensitivity: parseArg(binding.arg),
      mod: binding.modifiers,
      direction: getDirection(binding.modifiers),

      mouseStart (evt) {
        if (leftClick(evt)) {
          document.addEventListener('mousemove', ctx.move, true);
          document.addEventListener('mouseup', ctx.mouseEnd, true);
          ctx.start(evt, true);
        }
      },

      mouseEnd (evt) {
        document.removeEventListener('mousemove', ctx.move, true);
        document.removeEventListener('mouseup', ctx.mouseEnd, true);
        ctx.end(evt);
      },

      start (evt, mouseEvent) {
        removeObserver(ctx);
        mouseEvent !== true && setObserver(el, evt, ctx);

        const pos = position(evt);

        ctx.event = {
          x: pos.left,
          y: pos.top,
          time: new Date().getTime(),
          dir: false,
          abort: false
        };
      },

      move (evt) {
        if (ctx.event === void 0 || ctx.event.abort === true) {
          return
        }

        if (ctx.event.dir !== false) {
          stopAndPrevent(evt);
          return
        }

        const time = new Date().getTime() - ctx.event.time;

        if (time === 0) {
          return
        }

        const
          pos = position(evt),
          distX = pos.left - ctx.event.x,
          absX = Math.abs(distX),
          distY = pos.top - ctx.event.y,
          absY = Math.abs(distY);

        if (Platform.is.mobile) {
          if (absX < ctx.sensitivity[1] && absY < ctx.sensitivity[1]) {
            ctx.event.abort = true;
            return
          }
        }
        else if (absX < ctx.sensitivity[2] && absY < ctx.sensitivity[2]) {
          return
        }

        const
          velX = absX / time,
          velY = absY / time;

        if (
          ctx.direction.vertical &&
          absX < absY &&
          absX < 100 &&
          velY > ctx.sensitivity[0]
        ) {
          ctx.event.dir = distY < 0 ? 'up' : 'down';
        }

        if (
          ctx.direction.horizontal &&
          absX > absY &&
          absY < 100 &&
          velX > ctx.sensitivity[0]
        ) {
          ctx.event.dir = distX < 0 ? 'left' : 'right';
        }

        if (
          ctx.direction.up &&
          absX < absY &&
          distY < 0 &&
          absX < 100 &&
          velY > ctx.sensitivity[0]
        ) {
          ctx.event.dir = 'up';
        }

        if (
          ctx.direction.down &&
          absX < absY &&
          distY > 0 &&
          absX < 100 &&
          velY > ctx.sensitivity[0]
        ) {
          ctx.event.dir = 'down';
        }

        if (
          ctx.direction.left &&
          absX > absY &&
          distX < 0 &&
          absY < 100 &&
          velX > ctx.sensitivity[0]
        ) {
          ctx.event.dir = 'left';
        }

        if (
          ctx.direction.right &&
          absX > absY &&
          distX > 0 &&
          absY < 100 &&
          velX > ctx.sensitivity[0]
        ) {
          ctx.event.dir = 'right';
        }

        if (ctx.event.dir !== false) {
          document.body.classList.add('no-pointer-events');
          stopAndPrevent(evt);
          clearSelection();

          ctx.handler({
            evt,
            direction: ctx.event.dir,
            duration: time,
            distance: {
              x: absX,
              y: absY
            }
          });
        }
        else {
          ctx.event.abort = true;
        }
      },

      end (evt) {
        if (ctx.event === void 0) {
          return
        }

        removeObserver(ctx);

        if (ctx.event.abort === false && ctx.event.dir !== false) {
          document.body.classList.remove('no-pointer-events');
          stopAndPrevent(evt);
        }

        ctx.event = void 0;
      }
    };

    if (el.__qtouchswipe) {
      el.__qtouchswipe_old = el.__qtouchswipe;
    }

    el.__qtouchswipe = ctx;

    if (mouse === true) {
      el.addEventListener('mousedown', ctx.mouseStart);
    }

    el.addEventListener('touchstart', ctx.start, listenOpts.notPassive);
    el.addEventListener('touchmove', ctx.move, listenOpts.notPassive);
    el.addEventListener('touchcancel', ctx.end);
    el.addEventListener('touchend', ctx.end);
  },

  update (el, binding) {
    if (binding.oldValue !== binding.value) {
      el.__qtouchswipe.handler = binding.value;
    }
  },

  unbind (el, binding) {
    const ctx = el.__qtouchswipe_old || el.__qtouchswipe;
    if (ctx !== void 0) {
      removeObserver(ctx);
      document.body.classList.remove('no-pointer-events');

      if (binding.modifiers.mouse === true) {
        el.removeEventListener('mousedown', ctx.mouseStart);
        document.removeEventListener('mousemove', ctx.move, true);
        document.removeEventListener('mouseup', ctx.mouseEnd, true);
      }
      el.removeEventListener('touchstart', ctx.start, listenOpts.notPassive);
      el.removeEventListener('touchmove', ctx.move, listenOpts.notPassive);
      el.removeEventListener('touchcancel', ctx.end);
      el.removeEventListener('touchend', ctx.end);

      delete el[el.__qtouchswipe_old ? '__qtouchswipe_old' : '__qtouchswipe'];
    }
  }
};

const PanelWrapper = Vue.extend({
  name: 'QTabPanelWrapper',

  render (h) {
    return h('div', {
      staticClass: 'q-panel scroll',
      attrs: { role: 'tabpanel' },
      // stop propagation of content emitted @input
      // which would tamper with Panel's model
      on: {
        input: stop
      }
    }, slot(this, 'default'))
  }
});

const PanelParentMixin = {
  directives: {
    TouchSwipe
  },

  props: {
    value: {
      required: true
    },

    animated: Boolean,
    infinite: Boolean,
    swipeable: Boolean,

    transitionPrev: {
      type: String,
      default: 'slide-right'
    },
    transitionNext: {
      type: String,
      default: 'slide-left'
    },

    keepAlive: Boolean
  },

  data () {
    return {
      panelIndex: null,
      panelTransition: null
    }
  },

  computed: {
    panelDirectives () {
      if (this.swipeable) {
        return [{
          name: 'touch-swipe',
          value: this.__swipe,
          modifiers: {
            horizontal: true,
            mouse: true
          }
        }]
      }
    },

    contentKey () {
      return typeof this.value === 'string' || typeof this.value === 'number'
        ? this.value
        : String(this.value)
    }
  },

  watch: {
    value (newVal, oldVal) {
      const index = this.__isValidPanelName(newVal) === true
        ? this.__getPanelIndex(newVal)
        : -1;

      if (this.__forcedPanelTransition !== true) {
        this.__updatePanelTransition(
          index === -1 ? 0 : (index < this.__getPanelIndex(oldVal) ? -1 : 1)
        );
      }

      if (this.panelIndex !== index) {
        this.panelIndex = index;
        this.$emit('before-transition', newVal, oldVal);
        this.$nextTick(() => {
          this.$emit('transition', newVal, oldVal);
        });
      }
    }
  },

  methods: {
    next () {
      this.__go(1);
    },

    previous () {
      this.__go(-1);
    },

    goTo (name) {
      this.$emit('input', name);
    },

    __isValidPanelName (name) {
      return name !== void 0 && name !== null && name !== ''
    },

    __getPanelIndex (name) {
      return this.panels.findIndex(panel => {
        const opt = panel.componentOptions;
        return opt &&
          opt.propsData.name === name &&
          opt.propsData.disable !== '' &&
          opt.propsData.disable !== true
      })
    },

    __getAllPanels () {
      return this.panels.filter(
        panel => panel.componentOptions !== void 0 &&
          this.__isValidPanelName(panel.componentOptions.propsData.name)
      )
    },

    __getAvailablePanels () {
      return this.panels.filter(panel => {
        const opt = panel.componentOptions;
        return opt &&
          opt.propsData.name !== void 0 &&
          opt.propsData.disable !== '' &&
          opt.propsData.disable !== true
      })
    },

    __updatePanelTransition (direction) {
      const val = direction !== 0 && this.animated === true && this.panelIndex !== -1
        ? 'q-transition--' + (direction === -1 ? this.transitionPrev : this.transitionNext)
        : null;

      if (this.panelTransition !== val) {
        this.panelTransition = val;
      }
    },

    __go (direction, startIndex = this.panelIndex) {
      let index = startIndex + direction;
      const slots = this.panels;

      while (index > -1 && index < slots.length) {
        const opt = slots[index].componentOptions;

        if (
          opt !== void 0 &&
          opt.propsData.disable !== '' &&
          opt.propsData.disable !== true
        ) {
          this.__updatePanelTransition(direction);
          this.__forcedPanelTransition = true;
          this.$emit('input', slots[index].componentOptions.propsData.name);
          setTimeout(() => {
            this.__forcedPanelTransition = false;
          });
          return
        }

        index += direction;
      }

      if (this.infinite === true && slots.length > 0 && startIndex !== -1 && startIndex !== slots.length) {
        this.__go(direction, direction === -1 ? slots.length : -1);
      }
    },

    __swipe (evt) {
      this.__go((this.$q.lang.rtl === true ? -1 : 1) * (evt.direction === 'left' ? 1 : -1));
    },

    __updatePanelIndex () {
      const index = this.__getPanelIndex(this.value);

      if (this.panelIndex !== index) {
        this.panelIndex = index;
      }

      return true
    },

    __getPanelContent (h) {
      if (this.panels.length === 0) {
        return
      }

      const panel = this.__isValidPanelName(this.value) &&
        this.__updatePanelIndex() &&
        this.panels[this.panelIndex];

      const content = this.keepAlive === true
        ? [
          h('keep-alive', [
            h(PanelWrapper, {
              key: this.contentKey
            }, [ panel ])
          ])
        ]
        : [
          h('div', {
            staticClass: 'q-panel scroll',
            key: this.contentKey,
            attrs: { role: 'tabpanel' },
            // stop propagation of content emitted @input
            // which would tamper with Panel's model
            on: { input: stop }
          }, [ panel ])
        ];

      return this.animated === true
        ? [
          h('transition', {
            props: {
              name: this.panelTransition
            }
          }, content)
        ]
        : content
    }
  },

  render (h) {
    this.panels = this.$scopedSlots.default !== void 0
      ? this.$scopedSlots.default()
      : [];

    return this.__render(h)
  }
};

const PanelChildMixin = {
  props: {
    name: {
      required: true
    },
    disable: Boolean
  }
};

var FullscreenMixin = {
  props: {
    fullscreen: Boolean
  },

  data () {
    return {
      inFullscreen: false
    }
  },

  watch: {
    $route () {
      this.exitFullscreen();
    },

    fullscreen (v) {
      if (this.inFullscreen !== v) {
        this.toggleFullscreen();
      }
    },

    inFullscreen (v) {
      this.$emit('update:fullscreen', v);
      this.$emit('fullscreen', v);
    }
  },

  methods: {
    toggleFullscreen () {
      if (this.inFullscreen === true) {
        this.exitFullscreen();
      }
      else {
        this.setFullscreen();
      }
    },

    setFullscreen () {
      if (this.inFullscreen === true) {
        return
      }

      this.inFullscreen = true;
      this.container = this.$el.parentNode;
      this.container.replaceChild(this.fullscreenFillerNode, this.$el);
      document.body.appendChild(this.$el);
      document.body.classList.add('q-body--fullscreen-mixin');

      this.__historyFullscreen = {
        handler: this.exitFullscreen
      };
      History.add(this.__historyFullscreen);
    },

    exitFullscreen () {
      if (this.inFullscreen !== true) {
        return
      }

      if (this.__historyFullscreen !== void 0) {
        History.remove(this.__historyFullscreen);
        this.__historyFullscreen = void 0;
      }
      this.container.replaceChild(this.$el, this.fullscreenFillerNode);
      document.body.classList.remove('q-body--fullscreen-mixin');
      this.inFullscreen = false;

      if (this.$el.scrollIntoView !== void 0) {
        setTimeout(() => { this.$el.scrollIntoView(); });
      }
    }
  },

  beforeMount () {
    this.fullscreenFillerNode = document.createElement('span');
  },

  mounted () {
    this.fullscreen === true && this.setFullscreen();
  },

  beforeDestroy () {
    this.exitFullscreen();
  }
};

function isDeepEqual (a, b) {
  if (a === b) {
    return true
  }

  if (a instanceof Date && b instanceof Date) {
    return a.getTime() === b.getTime()
  }

  if (a !== Object(a) || b !== Object(b)) {
    return false
  }

  const props = Object.keys(a);

  if (props.length !== Object.keys(b).length) {
    return false
  }

  return props.every(prop => isDeepEqual(a[prop], b[prop]))
}

function isDate (v) {
  return Object.prototype.toString.call(v) === '[object Date]'
}

function isNumber (v) {
  return typeof v === 'number' && isFinite(v)
}

var QCarousel = Vue.extend({
  name: 'QCarousel',

  mixins: [ PanelParentMixin, FullscreenMixin ],

  props: {
    height: String,
    padding: Boolean,

    transitionPrev: {
      default: 'fade'
    },
    transitionNext: {
      default: 'fade'
    },

    controlColor: String,
    autoplay: [Number, Boolean],

    arrows: Boolean,
    prevIcon: String,
    nextIcon: String,

    navigation: Boolean,
    navigationIcon: String,

    thumbnails: Boolean
  },

  computed: {
    style () {
      if (this.inFullscreen !== true && this.height !== void 0) {
        return {
          height: this.height
        }
      }
    },

    classes () {
      return {
        fullscreen: this.inFullscreen,
        'q-carousel--arrows': this.padding === true && this.arrows === true,
        'q-carousel--navigation': this.padding === true && this.navigation === true
      }
    },

    arrowIcons () {
      const ico = [
        this.prevIcon || this.$q.iconSet.carousel.left,
        this.nextIcon || this.$q.iconSet.carousel.right
      ];

      return this.$q.lang.rtl
        ? ico.reverse()
        : ico
    },

    navIcon () {
      return this.navigationIcon || this.$q.iconSet.carousel.navigationIcon
    }
  },

  watch: {
    value () {
      if (this.autoplay) {
        clearInterval(this.timer);
        this.__startTimer();
      }
    },

    autoplay (val) {
      if (val) {
        this.__startTimer();
      }
      else {
        clearInterval(this.timer);
      }
    }
  },

  methods: {
    __startTimer () {
      this.timer = setTimeout(
        this.next,
        isNumber(this.autoplay) ? this.autoplay : 5000
      );
    },

    __getNavigationContainer (h, type, mapping) {
      return h('div', {
        staticClass: 'q-carousel__control q-carousel__navigation no-wrap absolute flex scroll-x q-carousel__navigation--' + type,
        class: this.controlColor ? `text-${this.controlColor}` : null
      }, [
        h('div', {
          staticClass: 'q-carousel__navigation-inner flex no-wrap justify-center'
        }, this.__getAvailablePanels().map(mapping))
      ])
    },

    __getContent (h) {
      const node = [];

      if (this.arrows === true) {
        node.push(
          h(QBtn, {
            staticClass: 'q-carousel__control q-carousel__prev-arrow absolute',
            props: { size: 'lg', color: this.controlColor, icon: this.arrowIcons[0], round: true, flat: true, dense: true },
            on: { click: this.previous }
          }),
          h(QBtn, {
            staticClass: 'q-carousel__control q-carousel__next-arrow absolute',
            props: { size: 'lg', color: this.controlColor, icon: this.arrowIcons[1], round: true, flat: true, dense: true },
            on: { click: this.next }
          })
        );
      }

      if (this.navigation === true) {
        node.push(this.__getNavigationContainer(h, 'buttons', panel => {
          const name = panel.componentOptions.propsData.name;

          return h(QBtn, {
            key: name,
            staticClass: 'q-carousel__navigation-icon',
            class: { 'q-carousel__navigation-icon--active': name === this.value },
            props: {
              icon: this.navIcon,
              round: true,
              flat: true,
              size: 'sm'
            },
            on: {
              click: () => { this.goTo(name); }
            }
          })
        }));
      }
      else if (this.thumbnails) {
        node.push(this.__getNavigationContainer(h, 'thumbnails', panel => {
          const slide = panel.componentOptions.propsData;

          return h('img', {
            class: { 'q-carousel__thumbnail--active': slide.name === this.value },
            attrs: {
              src: slide.imgSrc
            },
            on: {
              click: () => { this.goTo(slide.name); }
            }
          })
        }));
      }

      return node.concat(slot(this, 'control'))
    },

    __render (h) {
      return h('div', {
        staticClass: 'q-carousel q-panel-parent',
        style: this.style,
        class: this.classes
      }, [
        h('div', {
          staticClass: 'q-carousel__slides-container',
          directives: this.panelDirectives
        }, [
          this.__getPanelContent(h)
        ])
      ].concat(this.__getContent(h)))
    }
  },

  mounted () {
    this.autoplay && this.__startTimer();
  },

  beforeDestroy () {
    clearInterval(this.timer);
  }
});

var QCarouselSlide = Vue.extend({
  name: 'QCarouselSlide',

  mixins: [ PanelChildMixin ],

  props: {
    imgSrc: String
  },

  computed: {
    style () {
      if (this.imgSrc) {
        return {
          backgroundImage: `url(${this.imgSrc})`,
          backgroundSize: 'cover',
          backgroundPosition: '50%'
        }
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-carousel__slide',
      style: this.style,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

var QCarouselControl = Vue.extend({
  name: 'QCarouselControl',

  props: {
    position: {
      type: String,
      default: 'bottom-right'
    },
    offset: {
      type: Array,
      default: () => [18, 18]
    }
  },

  computed: {
    classes () {
      return `absolute-${this.position}`
    },

    style () {
      return {
        margin: `${this.offset[1]}px ${this.offset[0]}px`
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-carousel__control absolute',
      style: this.style,
      class: this.classes,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

Vue.extend({
  name: 'QChatMessage',

  props: {
    sent: Boolean,
    label: String,
    bgColor: String,
    textColor: String,
    name: String,
    avatar: String,
    text: Array,
    stamp: String,
    size: String,
    labelSanitize: Boolean,
    nameSanitize: Boolean,
    textSanitize: Boolean,
    stampSanitize: Boolean
  },

  computed: {
    textClass () {
      if (this.textColor) {
        return `text-${this.textColor}`
      }
    },

    messageClass () {
      if (this.bgColor) {
        return `text-${this.bgColor}`
      }
    },

    sizeClass () {
      if (this.size) {
        return `col-${this.size}`
      }
    },

    classes () {
      return {
        'q-message-sent': this.sent,
        'q-message-received': !this.sent
      }
    }
  },

  methods: {
    __getText (h) {
      const
        domPropText = this.textSanitize === true ? 'textContent' : 'innerHTML',
        domPropStamp = this.stampSanitize === true ? 'textContent' : 'innerHTML';

      return this.text.map((msg, index) => h('div', {
        key: index,
        staticClass: 'q-message-text',
        class: this.messageClass
      }, [
        h('span', {
          staticClass: 'q-message-text-content',
          class: this.textClass
        }, [
          h('div', { domProps: { [domPropText]: msg } }),
          this.stamp
            ? h('div', {
              staticClass: 'q-message-stamp',
              domProps: { [domPropStamp]: this.stamp }
            })
            : null
        ])
      ]))
    },

    __getMessage (h) {
      return h('div', {
        staticClass: 'q-message-text',
        class: this.messageClass
      }, [
        h('span', {
          staticClass: 'q-message-text-content',
          class: this.textClass
        }, this.$scopedSlots.default().concat([
          this.stamp !== void 0
            ? h('div', {
              staticClass: 'q-message-stamp',
              domProps: { [this.stampSanitize === true ? 'textContent' : 'innerHTML']: this.stamp }
            })
            : null
        ]))
      ])
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-message',
      class: this.classes
    }, [
      this.label
        ? h('div', {
          staticClass: 'q-message-label text-center',
          domProps: { [this.labelSanitize === true ? 'textContent' : 'innerHTML']: this.label }
        })
        : null,

      h('div', {
        staticClass: 'q-message-container row items-end no-wrap'
      }, [
        this.$scopedSlots.avatar !== void 0
          ? this.$scopedSlots.avatar()
          : (
            this.avatar !== void 0
              ? h('img', {
                staticClass: 'q-message-avatar col-auto',
                attrs: { src: this.avatar }
              })
              : null
          ),

        h('div', { class: this.sizeClass }, [
          this.name !== void 0
            ? h('div', {
              staticClass: 'q-message-name',
              domProps: { [this.nameSanitize === true ? 'textContent' : 'innerHTML']: this.name }
            })
            : null,

          this.text !== void 0 ? this.__getText(h) : null,
          this.$scopedSlots.default !== void 0 ? this.__getMessage(h) : null
        ])
      ])
    ])
  }
});

var CheckboxMixin = {
  props: {
    value: {
      required: true
    },
    val: {},

    trueValue: { default: true },
    falseValue: { default: false },

    label: String,
    leftLabel: Boolean,

    color: String,
    keepColor: Boolean,
    dark: Boolean,
    dense: Boolean,

    disable: Boolean,
    tabindex: [String, Number]
  },

  computed: {
    isTrue () {
      return this.modelIsArray
        ? this.index > -1
        : this.value === this.trueValue
    },

    isFalse () {
      return this.modelIsArray
        ? this.index === -1
        : this.value === this.falseValue
    },

    index () {
      if (this.modelIsArray === true) {
        return this.value.indexOf(this.val)
      }
    },

    modelIsArray () {
      return Array.isArray(this.value)
    },

    computedTabindex () {
      return this.disable === true ? -1 : this.tabindex || 0
    }
  },

  methods: {
    toggle (e) {
      e !== void 0 && stopAndPrevent(e);

      if (this.disable === true) {
        return
      }

      let val;

      if (this.modelIsArray === true) {
        if (this.isTrue === true) {
          val = this.value.slice();
          val.splice(this.index, 1);
        }
        else {
          val = this.value.concat(this.val);
        }
      }
      else if (this.isTrue === true) {
        val = this.toggleIndeterminate ? this.indeterminateValue : this.falseValue;
      }
      else if (this.isFalse === true) {
        val = this.trueValue;
      }
      else {
        val = this.falseValue;
      }

      this.$emit('input', val);
    },

    __keyDown (e) {
      if (e.keyCode === 13 || e.keyCode === 32) {
        this.toggle(e);
      }
    }
  }
};

var QCheckbox = Vue.extend({
  name: 'QCheckbox',

  mixins: [ CheckboxMixin ],

  props: {
    toggleIndeterminate: Boolean,
    indeterminateValue: { default: null }
  },

  computed: {
    isIndeterminate () {
      return this.value === void 0 || this.value === this.indeterminateValue
    },

    classes () {
      return {
        'disabled': this.disable,
        'q-checkbox--dark': this.dark,
        'q-checkbox--dense': this.dense,
        'reverse': this.leftLabel
      }
    },

    innerClass () {
      if (this.isTrue === true) {
        return 'q-checkbox__inner--active' +
          (this.color !== void 0 ? ' text-' + this.color : '')
      }
      else if (this.isIndeterminate === true) {
        return 'q-checkbox__inner--indeterminate' +
          (this.color !== void 0 ? ' text-' + this.color : '')
      }
      else if (this.keepColor === true && this.color !== void 0) {
        return 'text-' + this.color
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-checkbox cursor-pointer no-outline row inline no-wrap items-center',
      class: this.classes,
      attrs: { tabindex: this.computedTabindex },
      on: {
        click: this.toggle,
        keydown: this.__keyDown
      }
    }, [
      h('div', {
        staticClass: 'q-checkbox__inner relative-position',
        class: this.innerClass
      }, [
        this.disable !== true
          ? h('input', {
            staticClass: 'q-checkbox__native q-ma-none q-pa-none invisible',
            attrs: { type: 'checkbox' },
            on: { change: this.toggle }
          })
          : null,

        h('div', {
          staticClass: 'q-checkbox__bg absolute'
        }, [
          h('svg', {
            staticClass: 'q-checkbox__check fit absolute-full',
            attrs: { viewBox: '0 0 24 24' }
          }, [
            h('path', {
              attrs: {
                fill: 'none',
                d: 'M1.73,12.91 8.1,19.28 22.79,4.59'
              }
            })
          ]),

          h('div', { staticClass: 'q-checkbox__check-indet absolute' })
        ])
      ]),

      this.label !== void 0 || this.$scopedSlots.default !== void 0
        ? h('div', {
          staticClass: 'q-checkbox__label q-anchor--skip'
        }, (this.label !== void 0 ? [ this.label ] : []).concat(slot(this, 'default')))
        : null
    ])
  }
});

var QChip = Vue.extend({
  name: 'QChip',

  mixins: [ RippleMixin ],

  model: {
    event: 'remove'
  },

  props: {
    dense: Boolean,

    icon: String,
    iconRight: String,
    label: [String, Number],

    color: String,
    textColor: String,

    value: {
      type: Boolean,
      default: true
    },
    selected: {
      type: Boolean,
      default: null
    },

    square: Boolean,
    outline: Boolean,
    clickable: Boolean,
    removable: Boolean,

    tabindex: [String, Number],
    disable: Boolean
  },

  computed: {
    classes () {
      const text = this.outline
        ? this.color || this.textColor
        : this.textColor;

      return {
        [`bg-${this.color}`]: this.outline === false && this.color !== void 0,
        [`text-${text} q-chip--colored`]: text,
        disabled: this.disable,
        'q-chip--dense': this.dense,
        'q-chip--outline': this.outline,
        'q-chip--selected': this.selected,
        'q-chip--clickable cursor-pointer non-selectable q-hoverable': this.isClickable,
        'q-chip--square': this.square
      }
    },

    hasLeftIcon () {
      return this.selected === true || this.icon !== void 0
    },

    isClickable () {
      return this.disable === false && (this.clickable === true || this.selected !== null)
    },

    computedTabindex () {
      return this.disable === true ? -1 : this.tabindex || 0
    }
  },

  methods: {
    __onKeyup (e) {
      e.keyCode === 13 /* ENTER */ && this.__onClick(e);
    },

    __onClick (e) {
      if (!this.disable) {
        this.$emit('update:selected', !this.selected);
        this.$emit('click', e);
      }
    },

    __onRemove (e) {
      if (e.keyCode === void 0 || e.keyCode === 13) {
        stopAndPrevent(e);
        !this.disable && this.$emit('remove', false);
      }
    },

    __getContent (h) {
      const child = [];

      this.isClickable && child.push(
        h('div', { staticClass: 'q-focus-helper' })
      );

      this.hasLeftIcon && child.push(
        h(QIcon, {
          staticClass: 'q-chip__icon q-chip__icon--left',
          props: { name: this.selected === true ? this.$q.iconSet.chip.selected : this.icon }
        })
      );

      child.push(
        h('div', {
          staticClass: 'q-chip__content row no-wrap items-center q-anchor--skip'
        }, this.label !== void 0 ? [ this.label ] : slot(this, 'default'))
      );

      this.iconRight && child.push(
        h(QIcon, {
          staticClass: 'q-chip__icon q-chip__icon--right',
          props: { name: this.iconRight }
        })
      );

      this.removable && child.push(
        h(QIcon, {
          staticClass: 'q-chip__icon q-chip__icon--remove cursor-pointer',
          props: { name: this.$q.iconSet.chip.remove },
          attrs: { tabindex: this.computedTabindex },
          nativeOn: {
            click: this.__onRemove,
            keyup: this.__onRemove
          }
        })
      );

      return child
    }
  },

  render (h) {
    if (!this.value) { return }

    const data = this.isClickable ? {
      attrs: { tabindex: this.computedTabindex },
      on: {
        click: this.__onClick,
        keyup: this.__onKeyup
      },
      directives: [{ name: 'ripple', value: this.ripple }]
    } : {};

    data.staticClass = 'q-chip row inline no-wrap items-center';
    data.class = this.classes;

    return h('div', data, this.__getContent(h))
  }
});

const
  radius = 50,
  diameter = 2 * radius,
  circumference = diameter * Math.PI,
  strokeDashArray = Math.round(circumference * 1000) / 1000;

var QCircularProgress = Vue.extend({
  name: 'QCircularProgress',

  props: {
    value: {
      type: Number,
      default: 0
    },

    min: {
      type: Number,
      default: 0
    },
    max: {
      type: Number,
      default: 100
    },

    color: String,
    centerColor: String,
    trackColor: String,

    size: String,
    fontSize: String,

    // ratio
    thickness: {
      type: Number,
      default: 0.2,
      validator: v => v >= 0 && v <= 1
    },

    angle: {
      type: Number,
      default: 0
    },

    indeterminate: Boolean,
    showValue: Boolean,
    reverse: Boolean,

    instantFeedback: Boolean // used by QKnob, private
  },

  computed: {
    style () {
      if (this.size !== void 0) {
        return {
          fontSize: this.size
        }
      }
    },

    svgStyle () {
      return { transform: `rotate3d(0, 0, 1, ${this.angle - 90}deg)` }
    },

    circleStyle () {
      if (this.instantFeedback !== true && this.indeterminate !== true) {
        return { transition: 'stroke-dashoffset 0.6s ease 0s, stroke 0.6s ease' }
      }
    },

    dir () {
      return (this.$q.lang.rtl ? -1 : 1) * (this.reverse ? -1 : 1)
    },

    viewBox () {
      return diameter / (1 - this.thickness / 2)
    },

    viewBoxAttr () {
      return `${this.viewBox / 2} ${this.viewBox / 2} ${this.viewBox} ${this.viewBox}`
    },

    strokeDashOffset () {
      const progress = 1 - (this.value - this.min) / (this.max - this.min);
      return (this.dir * progress) * circumference
    },

    strokeWidth () {
      return this.thickness / 2 * this.viewBox
    }
  },

  methods: {
    __getCircle (h, { thickness, offset, color, cls }) {
      return h('circle', {
        staticClass: 'q-circular-progress__' + cls,
        class: color !== void 0 ? `text-${color}` : null,
        style: this.circleStyle,
        attrs: {
          fill: 'transparent',
          stroke: 'currentColor',
          'stroke-width': thickness,
          'stroke-dasharray': strokeDashArray,
          'stroke-dashoffset': offset,
          cx: this.viewBox,
          cy: this.viewBox,
          r: radius
        }
      })
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-circular-progress',
      'class': `q-circular-progress--${this.indeterminate === true ? 'in' : ''}determinate`,
      style: this.style,
      on: this.$listeners,
      attrs: {
        'role': 'progressbar',
        'aria-valuemin': this.min,
        'aria-valuemax': this.max,
        'aria-valuenow': this.indeterminate !== true ? this.value : null
      }
    }, [
      h('svg', {
        staticClass: 'q-circular-progress__svg',
        style: this.svgStyle,
        attrs: {
          viewBox: this.viewBoxAttr
        }
      }, [
        this.centerColor !== void 0 && this.centerColor !== 'transparent' ? h('circle', {
          staticClass: 'q-circular-progress__center',
          class: `text-${this.centerColor}`,
          attrs: {
            fill: 'currentColor',
            r: radius - this.strokeWidth / 2,
            cx: this.viewBox,
            cy: this.viewBox
          }
        }) : null,

        this.trackColor !== void 0 && this.trackColor !== 'transparent' ? this.__getCircle(h, {
          cls: 'track',
          thickness: this.strokeWidth,
          offset: 0,
          color: this.trackColor
        }) : null,

        this.__getCircle(h, {
          cls: 'circle',
          thickness: this.strokeWidth,
          offset: this.strokeDashOffset,
          color: this.color
        })
      ]),

      this.showValue === true
        ? h('div', {
          staticClass: 'q-circular-progress__text absolute-full row flex-center content-center',
          style: { fontSize: this.fontSize }
        }, this.$scopedSlots.default !== void 0 ? this.$scopedSlots.default() : [ h('div', [ this.value ]) ])
        : null
    ])
  }
});

const
  hex = /^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/,
  hexa = /^#[0-9a-fA-F]{4}([0-9a-fA-F]{4})?$/,
  hexOrHexa = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/,
  rgb = /^rgb\(((0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5]),){2}(0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5])\)$/,
  rgba = /^rgba\(((0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5]),){2}(0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5]),(0|0\.[0-9]+[1-9]|0\.[1-9]+|1)\)$/;

const testPattern = {
  date: v => /^-?[\d]+\/[0-1]\d\/[0-3]\d$/.test(v),
  time: v => /^([0-1]?\d|2[0-3]):[0-5]\d$/.test(v),
  fulltime: v => /^([0-1]?\d|2[0-3]):[0-5]\d:[0-5]\d$/.test(v),
  timeOrFulltime: v => /^([0-1]?\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/.test(v),

  hexColor: v => hex.test(v),
  hexaColor: v => hexa.test(v),
  hexOrHexaColor: v => hexOrHexa.test(v),

  rgbColor: v => rgb.test(v),
  rgbaColor: v => rgba.test(v),
  rgbOrRgbaColor: v => rgb.test(v) || rgba.test(v),

  hexOrRgbColor: v => hex.test(v) || rgb.test(v),
  hexaOrRgbaColor: v => hexa.test(v) || rgba.test(v),
  anyColor: v => hexOrHexa.test(v) || rgb.test(v) || rgba.test(v)
};

function throttle (fn, limit = 250) {
  let wait = false;
  let result;

  return function (...args) {
    if (wait) {
      return result
    }

    wait = true;
    result = fn.apply(this, args);
    setTimeout(() => {
      wait = false;
    }, limit);
    return result
  }
}

function getDirection$1 (mod) {
  const
    none = mod.horizontal !== true && mod.vertical !== true,
    dir = {
      all: none === true || (mod.horizontal === true && mod.vertical === true)
    };

  if (mod.horizontal === true || none === true) {
    dir.horizontal = true;
  }
  if (mod.vertical === true || none === true) {
    dir.vertical = true;
  }

  return dir
}

function processChanges (evt, ctx, isFinal) {
  let
    pos = position(evt),
    direction,
    distX = pos.left - ctx.event.x,
    distY = pos.top - ctx.event.y,
    absDistX = Math.abs(distX),
    absDistY = Math.abs(distY);

  if (ctx.direction.horizontal && !ctx.direction.vertical) {
    direction = distX < 0 ? 'left' : 'right';
  }
  else if (!ctx.direction.horizontal && ctx.direction.vertical) {
    direction = distY < 0 ? 'up' : 'down';
  }
  else if (absDistX >= absDistY) {
    direction = distX < 0 ? 'left' : 'right';
  }
  else {
    direction = distY < 0 ? 'up' : 'down';
  }

  return {
    evt,
    position: pos,
    direction,
    isFirst: ctx.event.isFirst,
    isFinal: isFinal === true,
    isMouse: ctx.event.mouse,
    duration: new Date().getTime() - ctx.event.time,
    distance: {
      x: absDistX,
      y: absDistY
    },
    offset: {
      x: distX,
      y: distY
    },
    delta: {
      x: pos.left - ctx.event.lastX,
      y: pos.top - ctx.event.lastY
    }
  }
}

function shouldTrigger (ctx, changes) {
  if (ctx.direction.horizontal && ctx.direction.vertical) {
    return true
  }
  if (ctx.direction.horizontal && !ctx.direction.vertical) {
    return Math.abs(changes.delta.x) > 0
  }
  if (!ctx.direction.horizontal && ctx.direction.vertical) {
    return Math.abs(changes.delta.y) > 0
  }
}

var TouchPan = {
  name: 'touch-pan',

  bind (el, binding) {
    const
      mouse = binding.modifiers.mouse === true,
      mouseEvtPassive = binding.modifiers.mouseMightPrevent !== true && binding.modifiers.mousePrevent !== true,
      mouseEvtOpts = listenOpts.hasPassive === true ? { passive: mouseEvtPassive, capture: true } : true,
      touchEvtPassive = binding.modifiers.mightPrevent !== true && binding.modifiers.prevent !== true,
      touchEvtOpts = listenOpts[touchEvtPassive === true ? 'passive' : 'notPassive'];

    function handleEvent (evt, mouseEvent) {
      if (mouse && mouseEvent) {
        binding.modifiers.mouseStop && stop(evt);
        binding.modifiers.mousePrevent && prevent(evt);
      }
      else {
        binding.modifiers.stop && stop(evt);
        binding.modifiers.prevent && prevent(evt);
      }
    }

    const ctx = {
      handler: binding.value,
      direction: getDirection$1(binding.modifiers),

      mouseStart (evt) {
        if (leftClick(evt)) {
          document.addEventListener('mousemove', ctx.move, mouseEvtOpts);
          document.addEventListener('mouseup', ctx.mouseEnd, mouseEvtOpts);
          ctx.start(evt, true);
        }
      },

      mouseEnd (evt) {
        document.removeEventListener('mousemove', ctx.move, mouseEvtOpts);
        document.removeEventListener('mouseup', ctx.mouseEnd, mouseEvtOpts);
        ctx.end(evt);
      },

      start (evt, mouseEvent) {
        removeObserver(ctx);
        mouseEvent !== true && setObserver(el, evt, ctx);

        const pos = position(evt);

        ctx.event = {
          x: pos.left,
          y: pos.top,
          time: new Date().getTime(),
          mouse: mouseEvent === true,
          detected: false,
          abort: false,
          isFirst: true,
          isFinal: false,
          lastX: pos.left,
          lastY: pos.top
        };
      },

      move (evt) {
        if (ctx.event === void 0 || ctx.event.abort === true) {
          return
        }

        if (ctx.event.detected === true) {
          handleEvent(evt, ctx.event.mouse);

          const changes = processChanges(evt, ctx, false);
          if (shouldTrigger(ctx, changes)) {
            ctx.handler(changes);
            ctx.event.lastX = changes.position.left;
            ctx.event.lastY = changes.position.top;
            ctx.event.isFirst = false;
          }

          return
        }

        const
          pos = position(evt),
          distX = Math.abs(pos.left - ctx.event.x),
          distY = Math.abs(pos.top - ctx.event.y);

        if (distX === distY) {
          return
        }

        ctx.event.detected = true;

        if (ctx.direction.all === false && (ctx.event.mouse === false || binding.modifiers.mouseAllDir !== true)) {
          ctx.event.abort = ctx.direction.vertical
            ? distX > distY
            : distX < distY;
        }

        if (ctx.event.abort !== true) {
          document.documentElement.style.cursor = 'grabbing';
          document.body.classList.add('no-pointer-events');
          document.body.classList.add('non-selectable');
          clearSelection();
        }

        ctx.move(evt);
      },

      end (evt) {
        if (ctx.event === void 0) {
          return
        }

        ctx.event.mouse !== true && removeObserver(ctx);

        document.documentElement.style.cursor = '';
        document.body.classList.remove('no-pointer-events');
        document.body.classList.remove('non-selectable');

        if (ctx.event.abort !== true && ctx.event.detected === true && ctx.event.isFirst !== true) {
          handleEvent(evt, ctx.event.mouse);
          ctx.handler(processChanges(evt, ctx, true));
        }

        ctx.event = void 0;
      }
    };

    if (el.__qtouchpan) {
      el.__qtouchpan_old = el.__qtouchpan;
    }

    el.__qtouchpan = ctx;

    if (mouse === true) {
      el.addEventListener('mousedown', ctx.mouseStart, mouseEvtOpts);
    }
    el.addEventListener('touchstart', ctx.start, touchEvtOpts);
    el.addEventListener('touchmove', ctx.move, touchEvtOpts);
    el.addEventListener('touchcancel', ctx.end);
    el.addEventListener('touchend', ctx.end);
  },

  update (el, { oldValue, value, modifiers }) {
    const ctx = el.__qtouchpan;

    if (oldValue !== value) {
      ctx.handler = value;
    }

    if (
      (modifiers.horizontal !== ctx.direction.horizontal) ||
      (modifiers.vertical !== ctx.direction.vertical)
    ) {
      ctx.direction = getDirection$1(modifiers);
    }
  },

  unbind (el, binding) {
    let ctx = el.__qtouchpan_old || el.__qtouchpan;
    if (ctx !== void 0) {
      removeObserver(ctx);

      document.documentElement.style.cursor = '';
      document.body.classList.remove('no-pointer-events');
      document.body.classList.remove('non-selectable');

      const
        mouse = binding.modifiers.mouse === true,
        mouseEvtPassive = binding.modifiers.mouseMightPrevent !== true && binding.modifiers.mousePrevent !== true,
        mouseEvtOpts = listenOpts.hasPassive === true ? { passive: mouseEvtPassive, capture: true } : true,
        touchEvtPassive = binding.modifiers.mightPrevent !== true && binding.modifiers.prevent !== true,
        touchEvtOpts = listenOpts[touchEvtPassive === true ? 'passive' : 'notPassive'];

      if (mouse === true) {
        el.removeEventListener('mousedown', ctx.mouseStart, mouseEvtOpts);
        document.removeEventListener('mousemove', ctx.move, mouseEvtOpts);
        document.removeEventListener('mouseup', ctx.mouseEnd, mouseEvtOpts);
      }
      el.removeEventListener('touchstart', ctx.start, touchEvtOpts);
      el.removeEventListener('touchmove', ctx.move, touchEvtOpts);
      el.removeEventListener('touchcancel', ctx.end);
      el.removeEventListener('touchend', ctx.end);

      delete el[el.__qtouchpan_old ? '__qtouchpan_old' : '__qtouchpan'];
    }
  }
};

// PGDOWN, LEFT, DOWN, PGUP, RIGHT, UP
const keyCodes = [34, 37, 40, 33, 39, 38];

function getRatio (evt, dragging, rtl) {
  const
    pos = position(evt),
    val = between((pos.left - dragging.left) / dragging.width, 0, 1);

  return rtl ? 1.0 - val : val
}

function getModel (ratio, min, max, step, decimals) {
  let model = min + ratio * (max - min);

  if (step > 0) {
    const modulo = (model - min) % step;
    model += (Math.abs(modulo) >= step / 2 ? (modulo < 0 ? -1 : 1) * step : 0) - modulo;
  }

  if (decimals > 0) {
    model = parseFloat(model.toFixed(decimals));
  }

  return between(model, min, max)
}

let SliderMixin = {
  directives: {
    TouchPan
  },

  props: {
    min: {
      type: Number,
      default: 0
    },
    max: {
      type: Number,
      default: 100
    },
    step: {
      type: Number,
      default: 1,
      validator: v => v >= 0
    },

    color: String,
    labelColor: String,
    dark: Boolean,
    dense: Boolean,

    label: Boolean,
    labelAlways: Boolean,
    markers: Boolean,
    snap: Boolean,

    disable: Boolean,
    readonly: Boolean,
    tabindex: [String, Number]
  },

  data () {
    return {
      active: false,
      preventFocus: false,
      focus: false
    }
  },

  computed: {
    classes () {
      return {
        [`text-${this.color}`]: this.color,
        [`q-slider--${this.active ? '' : 'in'}active`]: true,
        'disabled': this.disable,
        'q-slider--editable': this.editable,
        'q-slider--focus': this.focus === 'both',
        'q-slider--label': this.label || this.labelAlways,
        'q-slider--label-always': this.labelAlways,
        'q-slider--dark': this.dark,
        'q-slider--dense': this.dense
      }
    },

    editable () {
      return !this.disable && !this.readonly
    },

    decimals () {
      return (String(this.step).trim('0').split('.')[1] || '').length
    },

    computedStep () {
      return this.step === 0 ? 1 : this.step
    },

    markerStyle () {
      return {
        backgroundSize: 100 * this.computedStep / (this.max - this.min) + '% 2px'
      }
    },

    computedTabindex () {
      return this.editable === true ? this.tabindex || 0 : -1
    },

    horizProp () {
      return this.$q.lang.rtl === true ? 'right' : 'left'
    }
  },

  methods: {
    __pan (event) {
      if (event.isFinal) {
        if (this.dragging) {
          this.__updatePosition(event.evt);
          this.__updateValue(true);
          this.dragging = false;
        }
        this.active = false;
      }
      else if (event.isFirst) {
        this.dragging = this.__getDragging(event.evt);
        this.__updatePosition(event.evt);
        this.active = true;
      }
      else {
        this.__updatePosition(event.evt);
        this.__updateValue();
      }
    },

    __blur () {
      this.focus = false;
    },

    __activate (evt) {
      this.__updatePosition(evt, this.__getDragging(evt));

      this.preventFocus = true;
      this.active = true;

      document.addEventListener('mouseup', this.__deactivate, true);
    },

    __deactivate () {
      this.preventFocus = false;
      this.active = false;

      this.__updateValue(true);
      this.__blur();

      document.removeEventListener('mouseup', this.__deactivate, true);
    },

    __mobileClick (evt) {
      this.__updatePosition(evt, this.__getDragging(evt));
      this.__updateValue(true);
    },

    __keyup (evt) {
      if (keyCodes.includes(evt.keyCode)) {
        this.__updateValue(true);
      }
    }
  },

  beforeDestroy () {
    document.removeEventListener('mouseup', this.__deactivate, true);
  }
};

var QSlider = Vue.extend({
  name: 'QSlider',

  mixins: [ SliderMixin ],

  props: {
    value: {
      type: Number,
      required: true
    },

    labelValue: [String, Number]
  },

  data () {
    return {
      model: this.value,
      curRatio: 0
    }
  },

  watch: {
    value (v) {
      this.model = between(v, this.min, this.max);
    },

    min (v) {
      this.model = between(this.model, v, this.max);
    },

    max (v) {
      this.model = between(this.model, this.min, v);
    }
  },

  computed: {
    ratio () {
      return this.active === true ? this.curRatio : this.modelRatio
    },

    modelRatio () {
      return (this.model - this.min) / (this.max - this.min)
    },

    trackStyle () {
      return { width: (100 * this.ratio) + '%' }
    },

    thumbStyle () {
      return {
        [this.horizProp]: (100 * this.ratio) + '%'
      }
    },

    thumbClass () {
      return this.preventFocus === false && this.focus === true ? 'q-slider--focus' : null
    },

    pinClass () {
      return this.labelColor !== void 0 ? `text-${this.labelColor}` : null
    },

    events () {
      if (this.editable === true) {
        return this.$q.platform.is.mobile === true
          ? { click: this.__mobileClick }
          : {
            mousedown: this.__activate,
            focus: this.__focus,
            blur: this.__blur,
            keydown: this.__keydown,
            keyup: this.__keyup
          }
      }
    },

    computedLabel () {
      return this.labelValue !== void 0
        ? this.labelValue
        : this.model
    }
  },

  methods: {
    __updateValue (change) {
      if (this.model !== this.value) {
        this.$emit('input', this.model);
        change === true && this.$emit('change', this.model);
      }
    },

    __getDragging () {
      return this.$el.getBoundingClientRect()
    },

    __updatePosition (event, dragging = this.dragging) {
      const ratio = getRatio(
        event,
        dragging,
        this.$q.lang.rtl
      );

      this.model = getModel(ratio, this.min, this.max, this.step, this.decimals);
      this.curRatio = this.snap !== true || this.step === 0
        ? ratio
        : (this.model - this.min) / (this.max - this.min);
    },

    __focus () {
      this.focus = true;
    },

    __keydown (evt) {
      if (!keyCodes.includes(evt.keyCode)) {
        return
      }

      stopAndPrevent(evt);

      const
        step = ([34, 33].includes(evt.keyCode) ? 10 : 1) * this.computedStep,
        offset = [34, 37, 40].includes(evt.keyCode) ? -step : step;

      this.model = between(
        parseFloat((this.model + offset).toFixed(this.decimals)),
        this.min,
        this.max
      );

      this.__updateValue();
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-slider',
      attrs: {
        role: 'slider',
        'aria-valuemin': this.min,
        'aria-valuemax': this.max,
        'aria-valuenow': this.value,
        'data-step': this.step,
        'aria-disabled': this.disable,
        tabindex: this.computedTabindex
      },
      class: this.classes,
      on: this.events,
      directives: this.editable ? [{
        name: 'touch-pan',
        value: this.__pan,
        modifiers: {
          horizontal: true,
          prevent: true,
          stop: true,
          mouse: true,
          mouseAllDir: true,
          mouseStop: true
        }
      }] : null
    }, [
      h('div', { staticClass: 'q-slider__track-container absolute overflow-hidden' }, [
        h('div', {
          staticClass: 'q-slider__track absolute-full',
          style: this.trackStyle
        }),

        this.markers === true
          ? h('div', {
            staticClass: 'q-slider__track-markers absolute-full fit',
            style: this.markerStyle
          })
          : null
      ]),

      h('div', {
        staticClass: 'q-slider__thumb-container absolute non-selectable',
        class: this.thumbClass,
        style: this.thumbStyle
      }, [
        h('svg', {
          staticClass: 'q-slider__thumb absolute',
          attrs: { width: '21', height: '21' }
        }, [
          h('circle', {
            attrs: {
              cx: '10.5',
              cy: '10.5',
              r: '7.875'
            }
          })
        ]),

        this.label === true || this.labelAlways === true ? h('div', {
          staticClass: 'q-slider__pin absolute flex flex-center',
          class: this.pinClass
        }, [
          h('div', { staticClass: 'q-slider__pin-value-marker' }, [
            h('div', { staticClass: 'q-slider__pin-value-marker-bg' }),
            h('div', { staticClass: 'q-slider__pin-value-marker-text' }, [
              this.computedLabel
            ])
          ])
        ]) : null,

        h('div', { staticClass: 'q-slider__focus-ring' })
      ])
    ])
  }
});

// using it to manage SSR rendering with best performance

var CanRenderMixin = {
  data () {
    return {
      canRender: !onSSR
    }
  },

  mounted () {
    this.canRender === false && (this.canRender = true);
  }
};

var QResizeObserver = Vue.extend({
  name: 'QResizeObserver',

  mixins: [ CanRenderMixin ],

  props: {
    debounce: {
      type: [String, Number],
      default: 100
    }
  },

  data () {
    return this.hasObserver
      ? {}
      : { url: this.$q.platform.is.ie ? null : 'about:blank' }
  },

  methods: {
    trigger (immediately) {
      if (immediately === true || this.debounce === 0 || this.debounce === '0') {
        this.__onResize();
      }
      else if (!this.timer) {
        this.timer = setTimeout(this.__onResize, this.debounce);
      }
    },

    __onResize () {
      this.timer = null;

      if (!this.$el || !this.$el.parentNode) {
        return
      }

      const
        parent = this.$el.parentNode,
        size = {
          width: parent.offsetWidth,
          height: parent.offsetHeight
        };

      if (size.width === this.size.width && size.height === this.size.height) {
        return
      }

      this.size = size;
      this.$emit('resize', this.size);
    }
  },

  render (h) {
    if (this.canRender === false || this.hasObserver === true) {
      return
    }

    return h('object', {
      style: this.style,
      attrs: {
        tabindex: -1, // fix for Firefox
        type: 'text/html',
        data: this.url,
        'aria-hidden': true
      },
      on: {
        load: () => {
          this.$el.contentDocument.defaultView.addEventListener('resize', this.trigger, listenOpts.passive);
          this.trigger(true);
        }
      }
    })
  },

  beforeCreate () {
    this.size = { width: -1, height: -1 };
    if (isSSR === true) { return }

    this.hasObserver = typeof ResizeObserver !== 'undefined';

    if (this.hasObserver !== true) {
      this.style = `${this.$q.platform.is.ie ? 'visibility:hidden;' : ''}display:block;position:absolute;top:0;left:0;right:0;bottom:0;height:100%;width:100%;overflow:hidden;pointer-events:none;z-index:-1;`;
    }
  },

  mounted () {
    if (this.hasObserver === true) {
      this.observer = new ResizeObserver(this.trigger);
      this.observer.observe(this.$el.parentNode);
      return
    }

    this.trigger(true);

    if (this.$q.platform.is.ie) {
      this.url = 'about:blank';
    }
  },

  beforeDestroy () {
    clearTimeout(this.timer);

    if (this.hasObserver === true) {
      this.$el.parentNode && this.observer.unobserve(this.$el.parentNode);
      return
    }

    if (this.$el.contentDocument) {
      this.$el.contentDocument.defaultView.removeEventListener('resize', this.trigger, listenOpts.passive);
    }
  }
});

function getIndicatorClass (color, top, vertical) {
  const pos = vertical === true
    ? ['left', 'right']
    : ['top', 'bottom'];

  return `absolute-${top === true ? pos[0] : pos[1]}${color ? ` text-${color}` : ''}`
}

function bufferPrioritySort (t1, t2) {
  if (t1.priorityMatched === t2.priorityMatched) {
    return t2.priorityHref - t1.priorityHref
  }
  return t2.priorityMatched - t1.priorityMatched
}

function bufferCleanSelected (t) {
  t.selected = false;
  return t
}

const
  bufferFilters = [
    function (t) { return t.selected === true && t.exact === true && t.redirected !== true },
    function (t) { return t.selected === true && t.exact === true },
    function (t) { return t.selected === true && t.redirected !== true },
    function (t) { return t.selected === true },
    function (t) { return t.exact === true && t.redirected !== true },
    function (t) { return t.redirected !== true },
    function (t) { return t.exact === true },
    function (t) { return true }
  ],
  bufferFiltersLen = bufferFilters.length;

var QTabs = Vue.extend({
  name: 'QTabs',

  provide () {
    return {
      tabs: this.tabs,
      __activateTab: this.__activateTab,
      __activateRoute: this.__activateRoute
    }
  },

  props: {
    value: [Number, String],

    align: {
      type: String,
      default: 'center',
      validator: v => ['left', 'center', 'right', 'justify'].includes(v)
    },
    breakpoint: {
      type: [String, Number],
      default: 600
    },

    vertical: Boolean,
    shrink: Boolean,

    activeColor: String,
    activeBgColor: String,
    indicatorColor: String,
    leftIcon: String,
    rightIcon: String,

    // TODO remove in v1 final
    topIndicator: Boolean,
    switchIndicator: Boolean,

    narrowIndicator: Boolean,
    inlineLabel: Boolean,
    noCaps: Boolean,

    dense: Boolean
  },

  data () {
    return {
      tabs: {
        current: this.value,
        activeColor: this.activeColor,
        activeBgColor: this.activeBgColor,
        indicatorClass: getIndicatorClass(
          this.indicatorColor,
          this.topIndicator || this.switchIndicator,
          this.vertical
        ),
        narrowIndicator: this.narrowIndicator,
        inlineLabel: this.inlineLabel,
        noCaps: this.noCaps
      },
      scrollable: false,
      leftArrow: true,
      rightArrow: false,
      justify: false
    }
  },

  watch: {
    value (name) {
      this.__activateTab(name);
    },

    activeColor (v) {
      this.tabs.activeColor = v;
    },

    activeBgColor (v) {
      this.tabs.activeBgColor = v;
    },

    vertical (v) {
      this.tabs.indicatorClass = getIndicatorClass(this.indicatorColor, this.switchIndicatorPos, v);
    },

    indicatorColor (v) {
      this.tabs.indicatorClass = getIndicatorClass(v, this.switchIndicatorPos, this.vertical);
    },

    // TODO remove in v1 final
    topIndicator (v) {
      this.tabs.indicatorClass = getIndicatorClass(this.indicatorColor, v, this.vertical);
    },

    switchIndicator (v) {
      this.tabs.indicatorClass = getIndicatorClass(this.indicatorColor, v, this.vertical);
    },

    narrowIndicator (v) {
      this.tabs.narrowIndicator = v;
    },

    inlineLabel (v) {
      this.tabs.inlineLabel = v;
    },

    noCaps (v) {
      this.tabs.noCaps = v;
    }
  },

  computed: {
    alignClass () {
      const align = this.scrollable === true
        ? 'left'
        : (this.justify === true ? 'justify' : this.align);

      return `q-tabs__content--align-${align}`
    },

    classes () {
      return `q-tabs--${this.scrollable === true ? '' : 'not-'}scrollable` +
        (this.dense === true ? ' q-tabs--dense' : '') +
        (this.shrink === true ? ' col-shrink' : '') +
        (this.vertical === true ? ' q-tabs--vertical' : '')
    },

    // TODO remove in v1 final, directly use switchIndicator
    switchIndicatorPos () {
      return this.topIndicator || this.switchIndicator
    }
  },

  methods: {
    __activateTab (name) {
      if (this.tabs.current !== name) {
        this.__animate(this.tabs.current, name);
        this.tabs.current = name;
        this.$emit('input', name);
      }
    },

    __activateRoute (params) {
      if (this.bufferRoute !== this.$route && this.buffer.length > 0) {
        clearTimeout(this.bufferTimer);
        this.bufferTimer = void 0;
        this.buffer.length = 0;
      }
      this.bufferRoute = this.$route;

      if (params !== void 0) {
        if (params.remove === true) {
          this.buffer = this.buffer.filter(t => t.name !== params.name);
        }
        else {
          this.buffer.push(params);
        }
      }

      if (this.bufferTimer === void 0) {
        this.bufferTimer = setTimeout(() => {
          let tabs = [];

          for (let i = 0; i < bufferFiltersLen && tabs.length === 0; i++) {
            tabs = this.buffer.filter(bufferFilters[i]);
          }

          tabs.sort(bufferPrioritySort);
          this.__activateTab(tabs.length === 0 ? null : tabs[0].name);
          this.buffer = this.buffer.map(bufferCleanSelected);
          this.bufferTimer = void 0;
        }, 1);
      }
    },

    __updateContainer ({ width, height }) {
      const scroll = this.vertical === true
        ? this.$refs.content.scrollHeight > height
        : this.$refs.content.scrollWidth > width;

      if (this.scrollable !== scroll) {
        this.scrollable = scroll;
      }

      scroll === true && this.$nextTick(() => this.__updateArrows());

      const justify = (this.vertical === true ? height : width) < parseInt(this.breakpoint, 10);
      if (this.justify !== justify) {
        this.justify = justify;
      }
    },

    __animate (oldName, newName) {
      const
        oldTab = oldName
          ? this.$children.find(tab => tab.name === oldName)
          : null,
        newTab = newName
          ? this.$children.find(tab => tab.name === newName)
          : null;

      if (oldTab && newTab) {
        const
          oldEl = oldTab.$el.getElementsByClassName('q-tab__indicator')[0],
          newEl = newTab.$el.getElementsByClassName('q-tab__indicator')[0];

        clearTimeout(this.animateTimer);

        oldEl.style.transition = 'none';
        oldEl.style.transform = 'none';
        newEl.style.transition = 'none';
        newEl.style.transform = 'none';

        const
          oldPos = oldEl.getBoundingClientRect(),
          newPos = newEl.getBoundingClientRect();

        newEl.style.transform = this.vertical === true
          ? `translate3d(0, ${oldPos.top - newPos.top}px, 0) scale3d(1, ${newPos.height ? oldPos.height / newPos.height : 1}, 1)`
          : `translate3d(${oldPos.left - newPos.left}px, 0, 0) scale3d(${newPos.width ? oldPos.width / newPos.width : 1}, 1, 1)`;

        // allow scope updates to kick in
        this.$nextTick(() => {
          this.animateTimer = setTimeout(() => {
            newEl.style.transition = 'transform .25s cubic-bezier(.4, 0, .2, 1)';
            newEl.style.transform = 'none';
          }, 30);
        });
      }

      if (newTab && this.scrollable) {
        const
          { left, width, top, height } = this.$refs.content.getBoundingClientRect(),
          newPos = newTab.$el.getBoundingClientRect();

        let offset = this.vertical === true ? newPos.top - top : newPos.left - left;

        if (offset < 0) {
          this.$refs.content[this.vertical === true ? 'scrollTop' : 'scrollLeft'] += offset;
          this.__updateArrows();
          return
        }

        offset += this.vertical === true ? newPos.height - height : newPos.width - width;
        if (offset > 0) {
          this.$refs.content[this.vertical === true ? 'scrollTop' : 'scrollLeft'] += offset;
          this.__updateArrows();
        }
      }
    },

    __updateArrows () {
      const
        content = this.$refs.content,
        rect = content.getBoundingClientRect(),
        left = this.vertical === true ? content.scrollTop : content.scrollLeft;

      this.leftArrow = left > 0;
      this.rightArrow = this.vertical === true
        ? left + rect.height + 5 < content.scrollHeight
        : left + rect.width + 5 < content.scrollWidth;
    },

    __animScrollTo (value) {
      this.__stopAnimScroll();
      this.__scrollTowards(value);

      this.scrollTimer = setInterval(() => {
        if (this.__scrollTowards(value)) {
          this.__stopAnimScroll();
        }
      }, 5);
    },

    __scrollToStart () {
      this.__animScrollTo(0);
    },

    __scrollToEnd () {
      this.__animScrollTo(9999);
    },

    __stopAnimScroll () {
      clearInterval(this.scrollTimer);
    },

    __scrollTowards (value) {
      let
        content = this.$refs.content,
        left = this.vertical === true ? content.scrollTop : content.scrollLeft,
        direction = value < left ? -1 : 1,
        done = false;

      left += direction * 5;
      if (left < 0) {
        done = true;
        left = 0;
      }
      else if (
        (direction === -1 && left <= value) ||
        (direction === 1 && left >= value)
      ) {
        done = true;
        left = value;
      }

      content[this.vertical === true ? 'scrollTop' : 'scrollLeft'] = left;
      this.__updateArrows();
      return done
    }
  },

  created () {
    this.buffer = [];
  },

  // TODO remove in v1 final
  mounted () {
    if (this.topIndicator === true) {
      {
        console.info('\n\n[Quasar] QTabs info: please rename top-indicator to switch-indicator prop');
      }
    }
  },

  beforeDestroy () {
    clearTimeout(this.bufferTimer);
    clearTimeout(this.animateTimer);
  },

  render (h) {
    return h('div', {
      staticClass: 'q-tabs row no-wrap items-center',
      class: this.classes,
      on: {
        input: stop,
        ...this.$listeners
      },
      attrs: { role: 'tablist' }
    }, [
      h(QResizeObserver, {
        on: { resize: this.__updateContainer }
      }),

      h(QIcon, {
        staticClass: 'q-tabs__arrow q-tabs__arrow--left q-tab__icon',
        class: this.leftArrow === true ? '' : 'q-tabs__arrow--faded',
        props: { name: this.leftIcon || (this.vertical === true ? this.$q.iconSet.tabs.up : this.$q.iconSet.tabs.left) },
        nativeOn: {
          mousedown: this.__scrollToStart,
          touchstart: this.__scrollToStart,
          mouseup: this.__stopAnimScroll,
          mouseleave: this.__stopAnimScroll,
          touchend: this.__stopAnimScroll
        }
      }),

      h('div', {
        ref: 'content',
        staticClass: 'q-tabs__content row no-wrap items-center',
        class: this.alignClass
      }, slot(this, 'default')),

      h(QIcon, {
        staticClass: 'q-tabs__arrow q-tabs__arrow--right q-tab__icon',
        class: this.rightArrow === true ? '' : 'q-tabs__arrow--faded',
        props: { name: this.rightIcon || (this.vertical === true ? this.$q.iconSet.tabs.down : this.$q.iconSet.tabs.right) },
        nativeOn: {
          mousedown: this.__scrollToEnd,
          touchstart: this.__scrollToEnd,
          mouseup: this.__stopAnimScroll,
          mouseleave: this.__stopAnimScroll,
          touchend: this.__stopAnimScroll
        }
      })
    ])
  }
});

var QTab = Vue.extend({
  name: 'QTab',

  mixins: [ RippleMixin ],

  inject: {
    tabs: {
      default () {
        console.error('QTab/QRouteTab components need to be child of QTabsBar');
      }
    },
    __activateTab: {}
  },

  props: {
    icon: String,
    label: [Number, String],

    alert: [Boolean, String],

    name: {
      type: [Number, String],
      default: () => uid$1()
    },

    noCaps: Boolean,

    tabindex: [String, Number],
    disable: Boolean
  },

  computed: {
    isActive () {
      return this.tabs.current === this.name
    },

    classes () {
      return {
        [`q-tab--${this.isActive ? '' : 'in'}active`]: true,
        [`text-${this.tabs.activeColor}`]: this.isActive && this.tabs.activeColor,
        [`bg-${this.tabs.activeBgColor}`]: this.isActive && this.tabs.activeBgColor,
        'q-tab--full': this.icon && this.label && !this.tabs.inlineLabel,
        'q-tab--no-caps': this.noCaps === true || this.tabs.noCaps === true,
        'q-focusable q-hoverable cursor-pointer': !this.disable,
        disabled: this.disable
      }
    },

    computedTabIndex () {
      return this.disable === true || this.isActive === true ? -1 : this.tabindex || 0
    }
  },

  methods: {
    activate (e, keyboard) {
      keyboard !== true && this.$refs.blurTarget !== void 0 && this.$refs.blurTarget.focus();

      if (this.disable !== true) {
        this.$listeners.click !== void 0 && this.$emit('click', e);
        this.__activateTab(this.name);
      }
    },

    __onKeyup (e) {
      e.keyCode === 13 && this.activate(e, true);
    },

    __getContent (h) {
      const
        narrow = this.tabs.narrowIndicator,
        content = [],
        indicator = h('div', {
          staticClass: 'q-tab__indicator',
          class: this.tabs.indicatorClass
        });

      this.icon !== void 0 && content.push(h(QIcon, {
        staticClass: 'q-tab__icon',
        props: { name: this.icon }
      }));

      this.label !== void 0 && content.push(h('div', {
        staticClass: 'q-tab__label'
      }, [ this.label ]));

      this.alert !== false && content.push(h('div', {
        staticClass: 'q-tab__alert',
        class: this.alert !== true ? `text-${this.alert}` : null
      }));

      narrow && content.push(indicator);

      const node = [
        h('div', { staticClass: 'q-focus-helper', attrs: { tabindex: -1 }, ref: 'blurTarget' }),

        h('div', {
          staticClass: 'q-tab__content flex-center relative-position no-pointer-events non-selectable',
          class: this.tabs.inlineLabel === true ? 'row no-wrap q-tab__content--inline' : 'column'
        }, content.concat(slot(this, 'default')))
      ];

      !narrow && node.push(indicator);

      return node
    },

    __render (h, tag, props) {
      const data = {
        staticClass: 'q-tab relative-position self-stretch flex flex-center text-center',
        class: this.classes,
        attrs: {
          tabindex: this.computedTabIndex,
          role: 'tab',
          'aria-selected': this.isActive
        },
        directives: this.ripple !== false && this.disable === true ? null : [
          { name: 'ripple', value: this.ripple }
        ],
        [tag === 'div' ? 'on' : 'nativeOn']: {
          input: stop,
          ...this.$listeners,
          click: this.activate,
          keyup: this.__onKeyup
        }
      };

      if (props !== void 0) {
        data.props = props;
      }

      return h(tag, data, this.__getContent(h))
    }
  },

  render (h) {
    return this.__render(h, 'div')
  }
});

var QTabPanels = Vue.extend({
  name: 'QTabPanels',

  mixins: [ PanelParentMixin ],

  methods: {
    __render (h) {
      return h('div', {
        staticClass: 'q-tab-panels q-panel-parent',
        directives: this.panelDirectives,
        on: this.$listeners
      }, this.__getPanelContent(h))
    }
  }
});

var QTabPanel = Vue.extend({
  name: 'QTabPanel',

  mixins: [ PanelChildMixin ],

  render (h) {
    return h('div', {
      staticClass: 'q-tab-panel',
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

const palette = [
  'rgb(255,204,204)', 'rgb(255,230,204)', 'rgb(255,255,204)', 'rgb(204,255,204)', 'rgb(204,255,230)', 'rgb(204,255,255)', 'rgb(204,230,255)', 'rgb(204,204,255)', 'rgb(230,204,255)', 'rgb(255,204,255)',
  'rgb(255,153,153)', 'rgb(255,204,153)', 'rgb(255,255,153)', 'rgb(153,255,153)', 'rgb(153,255,204)', 'rgb(153,255,255)', 'rgb(153,204,255)', 'rgb(153,153,255)', 'rgb(204,153,255)', 'rgb(255,153,255)',
  'rgb(255,102,102)', 'rgb(255,179,102)', 'rgb(255,255,102)', 'rgb(102,255,102)', 'rgb(102,255,179)', 'rgb(102,255,255)', 'rgb(102,179,255)', 'rgb(102,102,255)', 'rgb(179,102,255)', 'rgb(255,102,255)',
  'rgb(255,51,51)', 'rgb(255,153,51)', 'rgb(255,255,51)', 'rgb(51,255,51)', 'rgb(51,255,153)', 'rgb(51,255,255)', 'rgb(51,153,255)', 'rgb(51,51,255)', 'rgb(153,51,255)', 'rgb(255,51,255)',
  'rgb(255,0,0)', 'rgb(255,128,0)', 'rgb(255,255,0)', 'rgb(0,255,0)', 'rgb(0,255,128)', 'rgb(0,255,255)', 'rgb(0,128,255)', 'rgb(0,0,255)', 'rgb(128,0,255)', 'rgb(255,0,255)',
  'rgb(245,0,0)', 'rgb(245,123,0)', 'rgb(245,245,0)', 'rgb(0,245,0)', 'rgb(0,245,123)', 'rgb(0,245,245)', 'rgb(0,123,245)', 'rgb(0,0,245)', 'rgb(123,0,245)', 'rgb(245,0,245)',
  'rgb(214,0,0)', 'rgb(214,108,0)', 'rgb(214,214,0)', 'rgb(0,214,0)', 'rgb(0,214,108)', 'rgb(0,214,214)', 'rgb(0,108,214)', 'rgb(0,0,214)', 'rgb(108,0,214)', 'rgb(214,0,214)',
  'rgb(163,0,0)', 'rgb(163,82,0)', 'rgb(163,163,0)', 'rgb(0,163,0)', 'rgb(0,163,82)', 'rgb(0,163,163)', 'rgb(0,82,163)', 'rgb(0,0,163)', 'rgb(82,0,163)', 'rgb(163,0,163)',
  'rgb(92,0,0)', 'rgb(92,46,0)', 'rgb(92,92,0)', 'rgb(0,92,0)', 'rgb(0,92,46)', 'rgb(0,92,92)', 'rgb(0,46,92)', 'rgb(0,0,92)', 'rgb(46,0,92)', 'rgb(92,0,92)',
  'rgb(255,255,255)', 'rgb(205,205,205)', 'rgb(178,178,178)', 'rgb(153,153,153)', 'rgb(127,127,127)', 'rgb(102,102,102)', 'rgb(76,76,76)', 'rgb(51,51,51)', 'rgb(25,25,25)', 'rgb(0,0,0)'
];

Vue.extend({
  name: 'QColor',

  directives: {
    TouchPan
  },

  props: {
    value: String,

    defaultValue: String,
    defaultView: {
      type: String,
      default: 'spectrum',
      validator: v => ['spectrum', 'tune', 'palette']
    },

    formatModel: {
      type: String,
      default: 'auto',
      validator: v => ['auto', 'hex', 'rgb', 'hexa', 'rgba'].includes(v)
    },

    noHeader: Boolean,
    noFooter: Boolean,

    disable: Boolean,
    readonly: Boolean,
    dark: Boolean
  },

  data () {
    return {
      topView: this.formatModel === 'auto'
        ? (
          (this.value === void 0 || this.value === null || this.value === '' || this.value.startsWith('#'))
            ? 'hex'
            : 'rgb'
        )
        : (this.formatModel.startsWith('hex') ? 'hex' : 'rgb'),
      view: this.defaultView,
      model: this.__parseModel(this.value || this.defaultValue)
    }
  },

  watch: {
    value (v) {
      const model = this.__parseModel(v || this.defaultValue);
      if (model.hex !== this.model.hex) {
        this.model = model;
      }
    },

    defaultValue (v) {
      if (!this.value && v) {
        const model = this.__parseModel(v);
        if (model.hex !== this.model.hex) {
          this.model = model;
        }
      }
    }
  },

  computed: {
    editable () {
      return this.disable !== true && this.readonly !== true
    },

    forceHex () {
      return this.formatModel === 'auto'
        ? null
        : this.formatModel.indexOf('hex') > -1
    },

    forceAlpha () {
      return this.formatModel === 'auto'
        ? null
        : this.formatModel.indexOf('a') > -1
    },

    isHex () {
      return this.value === void 0 || this.value === null || this.value === '' || this.value.startsWith('#')
    },

    isOutputHex () {
      return this.forceHex !== null
        ? this.forceHex
        : this.isHex
    },

    hasAlpha () {
      if (this.forceAlpha !== null) {
        return this.forceAlpha
      }
      return this.model.a !== void 0
    },

    currentBgColor () {
      return {
        backgroundColor: this.model.rgb || '#000'
      }
    },

    headerClass () {
      const light = this.model.a !== void 0 && this.model.a < 65
        ? true
        : luminosity(this.model) > 0.4;

      return `q-color-picker__header-content--${light ? 'light' : 'dark'}`
    },

    spectrumStyle () {
      return {
        background: `hsl(${this.model.h},100%,50%)`
      }
    },

    spectrumPointerStyle () {
      return {
        top: `${100 - this.model.v}%`,
        [this.$q.lang.rtl ? 'right' : 'left']: `${this.model.s}%`
      }
    },

    inputsArray () {
      const inp = ['r', 'g', 'b'];
      if (this.hasAlpha === true) {
        inp.push('a');
      }
      return inp
    }
  },

  created () {
    this.__spectrumChange = throttle(this.__spectrumChange, 20);
  },

  render (h) {
    const child = [ this.__getContent(h) ];

    this.noHeader !== true && child.unshift(
      this.__getHeader(h)
    );

    this.noFooter !== true && child.push(
      this.__getFooter(h)
    );

    return h('div', {
      staticClass: 'q-color-picker',
      class: {
        disabled: this.disable,
        'q-color-picker--dark': this.dark
      }
    }, child)
  },

  methods: {
    __getHeader (h) {
      return h('div', {
        staticClass: 'q-color-picker__header relative-position overflow-hidden'
      }, [
        h('div', { staticClass: 'q-color-picker__header-bg absolute-full' }),

        h('div', {
          staticClass: 'q-color-picker__header-content absolute-full',
          class: this.headerClass,
          style: this.currentBgColor
        }, [
          h(QTabs, {
            props: {
              value: this.topView,
              dense: true,
              align: 'justify'
            },
            on: {
              input: val => { this.topView = val; }
            }
          }, [
            h(QTab, {
              props: {
                label: 'HEX' + (this.hasAlpha === true ? 'A' : ''),
                name: 'hex',
                ripple: false
              }
            }),

            h(QTab, {
              props: {
                label: 'RGB' + (this.hasAlpha === true ? 'A' : ''),
                name: 'rgb',
                ripple: false
              }
            })
          ]),

          h('div', {
            staticClass: 'q-color-picker__header-banner row flex-center no-wrap'
          }, [
            h('input', {
              staticClass: 'fit',
              domProps: { value: this.model[this.topView] },
              attrs: !this.editable ? {
                readonly: true
              } : null,
              on: {
                input: evt => {
                  this.__updateErrorIcon(this.__onEditorChange(evt) === true);
                },
                blur: evt => {
                  this.__onEditorChange(evt, true) === true && this.$forceUpdate();
                  this.__updateErrorIcon(false);
                }
              }
            }),

            h(QIcon, {
              ref: 'errorIcon',
              staticClass: 'q-color-picker__error-icon absolute no-pointer-events',
              props: { name: this.$q.iconSet.type.negative }
            })
          ])
        ])
      ])
    },

    __getContent (h) {
      return h(QTabPanels, {
        props: {
          value: this.view,
          animated: true
        }
      }, [
        h(QTabPanel, {
          staticClass: 'q-color-picker__spectrum-tab',
          props: { name: 'spectrum' }
        }, this.__getSpectrumTab(h)),

        h(QTabPanel, {
          staticClass: 'q-pa-md q-color-picker__tune-tab',
          props: { name: 'tune' }
        }, this.__getTuneTab(h)),

        h(QTabPanel, {
          staticClass: 'q-pa-sm q-color-picker__palette-tab',
          props: { name: 'palette' }
        }, this.__getPaletteTab(h))
      ])
    },

    __getFooter (h) {
      return h(QTabs, {
        staticClass: 'q-color-picker__footer',
        props: {
          value: this.view,
          dense: true,
          align: 'justify'
        },
        on: {
          input: val => { this.view = val; }
        }
      }, [
        h(QTab, {
          props: {
            icon: this.$q.iconSet.colorPicker.spectrum,
            name: 'spectrum',
            ripple: false
          }
        }),

        h(QTab, {
          props: {
            icon: this.$q.iconSet.colorPicker.tune,
            name: 'tune',
            ripple: false
          }
        }),

        h(QTab, {
          props: {
            icon: this.$q.iconSet.colorPicker.palette,
            name: 'palette',
            ripple: false
          }
        })
      ])
    },

    __getSpectrumTab (h) {
      return [
        h('div', {
          ref: 'spectrum',
          staticClass: 'q-color-picker__spectrum non-selectable relative-position cursor-pointer',
          style: this.spectrumStyle,
          class: { readonly: !this.editable },
          on: this.editable
            ? { click: this.__spectrumClick }
            : null,
          directives: this.editable
            ? [{
              name: 'touch-pan',
              modifiers: {
                prevent: true,
                stop: true,
                mouse: true,
                mousePrevent: true,
                mouseStop: true
              },
              value: this.__spectrumPan
            }]
            : null
        }, [
          h('div', { style: { paddingBottom: '100%' } }),
          h('div', { staticClass: 'q-color-picker__spectrum-white absolute-full' }),
          h('div', { staticClass: 'q-color-picker__spectrum-black absolute-full' }),
          h('div', {
            staticClass: 'absolute',
            style: this.spectrumPointerStyle
          }, [
            this.model.hex !== void 0 ? h('div', { staticClass: 'q-color-picker__spectrum-circle' }) : null
          ])
        ]),

        h('div', {
          staticClass: 'q-color-picker__sliders'
        }, [
          h('div', { staticClass: 'q-color-picker__hue q-mx-sm non-selectable' }, [
            h(QSlider, {
              props: {
                value: this.model.h,
                min: 0,
                max: 360,
                fillHandleAlways: true,
                readonly: !this.editable
              },
              on: {
                input: this.__onHueChange,
                dragend: val => this.__onHueChange(val, true)
              }
            })
          ]),
          this.hasAlpha === true
            ? h('div', { staticClass: 'q-mx-sm q-color-picker__alpha non-selectable' }, [
              h(QSlider, {
                props: {
                  value: this.model.a,
                  min: 0,
                  max: 100,
                  fillHandleAlways: true,
                  readonly: !this.editable
                },
                on: {
                  input: value => this.__onNumericChange({ target: { value } }, 'a', 100),
                  dragend: value => this.__onNumericChange({ target: { value } }, 'a', 100, true)
                }
              })
            ])
            : null
        ])
      ]
    },

    __getTuneTab (h) {
      return [
        h('div', { staticClass: 'row items-center no-wrap' }, [
          h('div', ['R']),
          h(QSlider, {
            props: {
              value: this.model.r,
              min: 0,
              max: 255,
              color: 'red',
              dark: this.dark,
              readonly: !this.editable
            },
            on: {
              input: value => this.__onNumericChange({ target: { value } }, 'r', 255)
            }
          }),
          h('input', {
            domProps: {
              value: this.model.r
            },
            attrs: {
              maxlength: 3,
              readonly: !this.editable
            },
            on: {
              input: evt => this.__onNumericChange(evt, 'r', 255),
              blur: evt => this.__onNumericChange(evt, 'r', 255, true)
            }
          })
        ]),

        h('div', { staticClass: 'row items-center no-wrap' }, [
          h('div', ['G']),
          h(QSlider, {
            props: {
              value: this.model.g,
              min: 0,
              max: 255,
              color: 'green',
              dark: this.dark,
              readonly: !this.editable
            },
            on: {
              input: value => this.__onNumericChange({ target: { value } }, 'g', 255)
            }
          }),
          h('input', {
            domProps: {
              value: this.model.g
            },
            attrs: {
              maxlength: 3,
              readonly: !this.editable
            },
            on: {
              input: evt => this.__onNumericChange(evt, 'g', 255),
              blur: evt => this.__onNumericChange(evt, 'g', 255, true)
            }
          })
        ]),

        h('div', { staticClass: 'row items-center no-wrap' }, [
          h('div', ['B']),
          h(QSlider, {
            props: {
              value: this.model.b,
              min: 0,
              max: 255,
              color: 'blue',
              readonly: !this.editable,
              dark: this.dark
            },
            on: {
              input: value => this.__onNumericChange({ target: { value } }, 'b', 255)
            }
          }),
          h('input', {
            domProps: {
              value: this.model.b
            },
            attrs: {
              maxlength: 3,
              readonly: !this.editable
            },
            on: {
              input: evt => this.__onNumericChange(evt, 'b', 255),
              blur: evt => this.__onNumericChange(evt, 'b', 255, true)
            }
          })
        ]),

        this.hasAlpha === true ? h('div', { staticClass: 'row items-center no-wrap' }, [
          h('div', ['A']),
          h(QSlider, {
            props: {
              value: this.model.a,
              color: 'grey',
              readonly: !this.editable,
              dark: this.dark
            },
            on: {
              input: value => this.__onNumericChange({ target: { value } }, 'a', 100)
            }
          }),
          h('input', {
            domProps: {
              value: this.model.a
            },
            attrs: {
              maxlength: 3,
              readonly: !this.editable
            },
            on: {
              input: evt => this.__onNumericChange(evt, 'a', 100),
              blur: evt => this.__onNumericChange(evt, 'a', 100, true)
            }
          })
        ]) : null
      ]
    },

    __getPaletteTab (h) {
      return [
        h('div', {
          staticClass: 'row items-center',
          class: this.editable ? 'cursor-pointer' : null
        }, palette.map(color => h('div', {
          staticClass: 'q-color-picker__cube col-auto',
          style: { backgroundColor: color },
          on: this.editable ? {
            click: () => {
              this.__onPalettePick(color);
            }
          } : null
        })))
      ]
    },

    __onSpectrumChange (left, top, change) {
      const panel = this.$refs.spectrum;
      if (panel === void 0) { return }

      const
        width = panel.clientWidth,
        height = panel.clientHeight,
        rect = panel.getBoundingClientRect();

      let x = Math.min(width, Math.max(0, left - rect.left));

      if (this.$q.lang.rtl) {
        x = width - x;
      }

      const
        y = Math.min(height, Math.max(0, top - rect.top)),
        s = Math.round(100 * x / width),
        v = Math.round(100 * Math.max(0, Math.min(1, -(y / height) + 1))),
        rgb = hsvToRgb({
          h: this.model.h,
          s,
          v,
          a: this.hasAlpha === true ? this.model.a : void 0
        });

      this.model.s = s;
      this.model.v = v;
      this.__update(rgb, change);
    },

    __onHueChange (h, change) {
      h = Math.round(h);
      const rgb = hsvToRgb({
        h,
        s: this.model.s,
        v: this.model.v,
        a: this.hasAlpha === true ? this.model.a : void 0
      });

      this.model.h = h;
      this.__update(rgb, change);
    },

    __onNumericChange (evt, formatModel, max, change) {
      if (!/^[0-9]+$/.test(evt.target.value)) {
        change && this.$forceUpdate();
        return
      }

      const val = Math.floor(Number(evt.target.value));

      if (val < 0 || val > max) {
        change && this.$forceUpdate();
        return
      }

      const rgb = {
        r: formatModel === 'r' ? val : this.model.r,
        g: formatModel === 'g' ? val : this.model.g,
        b: formatModel === 'b' ? val : this.model.b,
        a: this.hasAlpha === true
          ? (formatModel === 'a' ? val : this.model.a)
          : void 0
      };

      if (formatModel !== 'a') {
        const hsv = rgbToHsv(rgb);
        this.model.h = hsv.h;
        this.model.s = hsv.s;
        this.model.v = hsv.v;
      }

      this.__update(rgb, change);

      if (change !== true && evt.target.selectionEnd !== void 0) {
        const index = evt.target.selectionEnd;
        this.$nextTick(() => {
          evt.target.setSelectionRange(index, index);
        });
      }
    },

    __onEditorChange (evt, change) {
      let rgb;
      const inp = evt.target.value;

      if (this.topView === 'hex') {
        if (
          inp.length !== (this.hasAlpha === true ? 9 : 7) ||
          !/^#[0-9A-Fa-f]+$/.test(inp)
        ) {
          return true
        }

        rgb = hexToRgb(inp);
      }
      else {
        let model;

        if (!inp.endsWith(')')) {
          return true
        }
        else if (this.hasAlpha !== true && inp.startsWith('rgb(')) {
          model = inp.substring(4, inp.length - 1).split(',').map(n => parseInt(n, 10));

          if (
            model.length !== 3 ||
            !/^rgb\([0-9]{1,3},[0-9]{1,3},[0-9]{1,3}\)$/.test(inp)
          ) {
            return true
          }
        }
        else if (this.hasAlpha === true && inp.startsWith('rgba(')) {
          model = inp.substring(5, inp.length - 1).split(',');

          if (
            model.length !== 4 ||
            !/^rgba\([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},(0|0\.[0-9]+[1-9]|0\.[1-9]+|1)\)$/.test(inp)
          ) {
            return true
          }

          for (let i = 0; i < 3; i++) {
            const v = parseInt(model[i], 10);
            if (v < 0 || v > 255) {
              return true
            }
            model[i] = v;
          }

          const v = parseFloat(model[3]);
          if (v < 0 || v > 1) {
            return true
          }
          model[3] = v;
        }
        else {
          return true
        }

        if (
          model[0] < 0 || model[0] > 255 ||
          model[1] < 0 || model[1] > 255 ||
          model[2] < 0 || model[2] > 255 ||
          (this.hasAlpha === true && (model[3] < 0 || model[3] > 1))
        ) {
          return true
        }

        rgb = {
          r: model[0],
          g: model[1],
          b: model[2],
          a: this.hasAlpha === true
            ? model[3] * 100
            : void 0
        };
      }

      const hsv = rgbToHsv(rgb);
      this.model.h = hsv.h;
      this.model.s = hsv.s;
      this.model.v = hsv.v;

      this.__update(rgb, change);

      if (change !== true) {
        const index = evt.target.selectionEnd;
        this.$nextTick(() => {
          evt.target.setSelectionRange(index, index);
        });
      }
    },

    __onPalettePick (color) {
      const model = color.substring(4, color.length - 1).split(',');

      const rgb = {
        r: parseInt(model[0], 10),
        g: parseInt(model[1], 10),
        b: parseInt(model[2], 10),
        a: this.model.a
      };

      const hsv = rgbToHsv(rgb);
      this.model.h = hsv.h;
      this.model.s = hsv.s;
      this.model.v = hsv.v;

      this.__update(rgb, true);
    },

    __update (rgb, change) {
      // update internally
      this.model.hex = rgbToHex(rgb);
      this.model.rgb = rgbToString(rgb);
      this.model.r = rgb.r;
      this.model.g = rgb.g;
      this.model.b = rgb.b;
      this.model.a = rgb.a;

      const value = this.model[this.isOutputHex === true ? 'hex' : 'rgb'];

      // emit new value
      this.$emit('input', value);
      change && value !== this.value && this.$emit('change', value);
    },

    __updateErrorIcon (val) {
      // we MUST avoid vue triggering a render,
      // so manually changing this
      this.$refs.errorIcon.$el.style.opacity = val ? 1 : 0;
    },

    __parseModel (v) {
      const forceAlpha = this.forceAlpha !== void 0
        ? this.forceAlpha
        : (
          this.formatModel === 'auto'
            ? null
            : this.formatModel.indexOf('a') > -1
        );

      if (v === null || v === void 0 || v === '' || testPattern.anyColor(v) !== true) {
        return {
          h: 0,
          s: 0,
          v: 0,
          r: 0,
          g: 0,
          b: 0,
          a: forceAlpha === true ? 100 : void 0,
          hex: void 0,
          rgb: void 0
        }
      }

      let model = stringToRgb(v);

      if (forceAlpha === true && model.a === void 0) {
        model.a = 100;
      }

      model.hex = rgbToHex(model);
      model.rgb = rgbToString(model);

      return Object.assign(model, rgbToHsv(model))
    },

    __spectrumPan (evt) {
      if (evt.isFinal) {
        this.__onSpectrumChange(
          evt.position.left,
          evt.position.top,
          true
        );
      }
      else {
        this.__spectrumChange(evt);
      }
    },

    // throttled in created()
    __spectrumChange (evt) {
      this.__onSpectrumChange(
        evt.position.left,
        evt.position.top
      );
    },

    __spectrumClick (evt) {
      this.__onSpectrumChange(
        evt.pageX - window.pageXOffset,
        evt.pageY - window.pageYOffset,
        true
      );
    }
  }
});

// taken from https://github.com/jalaali/jalaali-js

/*
  Jalaali years starting the 33-year rule.
*/
const breaks = [ -61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210,
  1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178
];

/*
  Converts a Gregorian date to Jalaali.
*/
function toJalaali (gy, gm, gd) {
  if (Object.prototype.toString.call(gy) === '[object Date]') {
    gd = gy.getDate();
    gm = gy.getMonth() + 1;
    gy = gy.getFullYear();
  }
  return d2j(g2d(gy, gm, gd))
}

/*
  Converts a Jalaali date to Gregorian.
*/
function toGregorian (jy, jm, jd) {
  return d2g(j2d(jy, jm, jd))
}

/*
  Is this a leap year or not?
*/
function isLeapJalaaliYear (jy) {
  return jalCalLeap(jy) === 0
}

/*
  Number of days in a given month in a Jalaali year.
*/
function jalaaliMonthLength (jy, jm) {
  if (jm <= 6) return 31
  if (jm <= 11) return 30
  if (isLeapJalaaliYear(jy)) return 30
  return 29
}

/*
    This function determines if the Jalaali (Persian) year is
    leap (366-day long) or is the common year (365 days)

    @param jy Jalaali calendar year (-61 to 3177)
    @returns number of years since the last leap year (0 to 4)
 */
function jalCalLeap (jy) {
  let bl = breaks.length,
    jp = breaks[0],
    jm,
    jump,
    leap,
    n,
    i;

  if (jy < jp || jy >= breaks[bl - 1]) { throw new Error('Invalid Jalaali year ' + jy) }

  for (i = 1; i < bl; i += 1) {
    jm = breaks[i];
    jump = jm - jp;
    if (jy < jm) { break }
    jp = jm;
  }
  n = jy - jp;

  if (jump - n < 6) { n = n - jump + div(jump + 4, 33) * 33; }
  leap = mod(mod(n + 1, 33) - 1, 4);
  if (leap === -1) {
    leap = 4;
  }

  return leap
}

/*
  This function determines if the Jalaali (Persian) year is
  leap (366-day long) or is the common year (365 days), and
  finds the day in March (Gregorian calendar) of the first
  day of the Jalaali year (jy).

  @param jy Jalaali calendar year (-61 to 3177)
  @param withoutLeap when don't need leap (true or false) default is false
  @return
    leap: number of years since the last leap year (0 to 4)
    gy: Gregorian year of the beginning of Jalaali year
    march: the March day of Farvardin the 1st (1st day of jy)
  @see: http://www.astro.uni.torun.pl/~kb/Papers/EMP/PersianC-EMP.htm
  @see: http://www.fourmilab.ch/documents/calendar/
*/
function jalCal (jy, withoutLeap) {
  let bl = breaks.length,
    gy = jy + 621,
    leapJ = -14,
    jp = breaks[0],
    jm,
    jump,
    leap,
    leapG,
    march,
    n,
    i;

  if (jy < jp || jy >= breaks[bl - 1]) { throw new Error('Invalid Jalaali year ' + jy) }

  // Find the limiting years for the Jalaali year jy.
  for (i = 1; i < bl; i += 1) {
    jm = breaks[i];
    jump = jm - jp;
    if (jy < jm) { break }
    leapJ = leapJ + div(jump, 33) * 8 + div(mod(jump, 33), 4);
    jp = jm;
  }
  n = jy - jp;

  // Find the number of leap years from AD 621 to the beginning
  // of the current Jalaali year in the Persian calendar.
  leapJ = leapJ + div(n, 33) * 8 + div(mod(n, 33) + 3, 4);
  if (mod(jump, 33) === 4 && jump - n === 4) { leapJ += 1; }

  // And the same in the Gregorian calendar (until the year gy).
  leapG = div(gy, 4) - div((div(gy, 100) + 1) * 3, 4) - 150;

  // Determine the Gregorian date of Farvardin the 1st.
  march = 20 + leapJ - leapG;

  // Find how many years have passed since the last leap year.
  if (!withoutLeap) {
    if (jump - n < 6) { n = n - jump + div(jump + 4, 33) * 33; }
    leap = mod(mod(n + 1, 33) - 1, 4);
    if (leap === -1) {
      leap = 4;
    }
  }

  return {
    leap: leap,
    gy: gy,
    march: march
  }
}

/*
  Converts a date of the Jalaali calendar to the Julian Day number.

  @param jy Jalaali year (1 to 3100)
  @param jm Jalaali month (1 to 12)
  @param jd Jalaali day (1 to 29/31)
  @return Julian Day number
*/
function j2d (jy, jm, jd) {
  const r = jalCal(jy, true);
  return g2d(r.gy, 3, r.march) + (jm - 1) * 31 - div(jm, 7) * (jm - 7) + jd - 1
}

/*
  Converts the Julian Day number to a date in the Jalaali calendar.

  @param jdn Julian Day number
  @return
    jy: Jalaali year (1 to 3100)
    jm: Jalaali month (1 to 12)
    jd: Jalaali day (1 to 29/31)
*/
function d2j (jdn) {
  let gy = d2g(jdn).gy, // Calculate Gregorian year (gy).
    jy = gy - 621,
    r = jalCal(jy, false),
    jdn1f = g2d(gy, 3, r.march),
    jd,
    jm,
    k;

  // Find number of days that passed since 1 Farvardin.
  k = jdn - jdn1f;
  if (k >= 0) {
    if (k <= 185) {
      // The first 6 months.
      jm = 1 + div(k, 31);
      jd = mod(k, 31) + 1;
      return { jy: jy,
        jm: jm,
        jd: jd
      }
    }
    else {
      // The remaining months.
      k -= 186;
    }
  }
  else {
    // Previous Jalaali year.
    jy -= 1;
    k += 179;
    if (r.leap === 1) { k += 1; }
  }
  jm = 7 + div(k, 30);
  jd = mod(k, 30) + 1;
  return { jy: jy,
    jm: jm,
    jd: jd
  }
}

/*
  Calculates the Julian Day number from Gregorian or Julian
  calendar dates. This integer number corresponds to the noon of
  the date (i.e. 12 hours of Universal Time).
  The procedure was tested to be good since 1 March, -100100 (of both
  calendars) up to a few million years into the future.

  @param gy Calendar year (years BC numbered 0, -1, -2, ...)
  @param gm Calendar month (1 to 12)
  @param gd Calendar day of the month (1 to 28/29/30/31)
  @return Julian Day number
*/
function g2d (gy, gm, gd) {
  let d = div((gy + div(gm - 8, 6) + 100100) * 1461, 4) +
      div(153 * mod(gm + 9, 12) + 2, 5) +
      gd - 34840408;
  d = d - div(div(gy + 100100 + div(gm - 8, 6), 100) * 3, 4) + 752;
  return d
}

/*
  Calculates Gregorian and Julian calendar dates from the Julian Day number
  (jdn) for the period since jdn=-34839655 (i.e. the year -100100 of both
  calendars) to some millions years ahead of the present.

  @param jdn Julian Day number
  @return
    gy: Calendar year (years BC numbered 0, -1, -2, ...)
    gm: Calendar month (1 to 12)
    gd: Calendar day of the month M (1 to 28/29/30/31)
*/
function d2g (jdn) {
  let j,
    i,
    gd,
    gm,
    gy;
  j = 4 * jdn + 139361631;
  j = j + div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
  i = div(mod(j, 1461), 4) * 5 + 308;
  gd = div(mod(i, 153), 5) + 1;
  gm = mod(div(i, 153), 12) + 1;
  gy = div(j, 1461) - 100100 + div(8 - gm, 6);
  return {
    gy: gy,
    gm: gm,
    gd: gd
  }
}

/*
  Utility helper functions.
*/

function div (a, b) {
  return ~~(a / b)
}

function mod (a, b) {
  return a - ~~(a / b) * b
}

var DateTimeMixin = {
  props: {
    value: {
      required: true
    },

    mask: {
      type: String
    },
    locale: Object,

    calendar: {
      type: String,
      validator: v => ['gregorian', 'persian'].includes(v),
      default: 'gregorian'
    },

    landscape: Boolean,

    color: String,
    textColor: String,
    dark: Boolean,

    readonly: Boolean,
    disable: Boolean
  },

  watch: {
    mask () {
      this.$nextTick(() => {
        this.__updateValue({}, /* reason for QDate only */ 'mask');
      });
    },

    computedLocale () {
      this.$nextTick(() => {
        this.__updateValue({}, /* reason for QDate only */ 'locale');
      });
    }
  },

  computed: {
    editable () {
      return this.disable !== true && this.readonly !== true
    },

    computedColor () {
      return this.color || 'primary'
    },

    computedTextColor () {
      return this.textColor || 'white'
    },

    computedTabindex () {
      return this.editable === true ? 0 : -1
    },

    headerClass () {
      const cls = [];
      this.color !== void 0 && cls.push(`bg-${this.color}`);
      this.textColor !== void 0 && cls.push(`text-${this.textColor}`);
      return cls.join(' ')
    },

    computedLocale () {
      return this.__getComputedLocale()
    }
  },

  methods: {
    __getComputedLocale () {
      return this.locale || this.$q.lang.date
    },

    __getCurrentDate () {
      const d = new Date();

      if (this.calendar === 'persian') {
        const jDate = toJalaali(d);
        return {
          year: jDate.jy,
          month: jDate.jm,
          day: jDate.jd
        }
      }

      return {
        year: d.getFullYear(),
        month: d.getMonth() + 1,
        day: d.getDate()
      }
    },

    __getCurrentTime () {
      const d = new Date();

      return {
        hour: d.getHours(),
        minute: d.getMinutes(),
        second: d.getSeconds(),
        millisecond: d.getMilliseconds()
      }
    }
  }
};

/* eslint no-fallthrough: 0 */

const
  MILLISECONDS_IN_DAY = 86400000,
  MILLISECONDS_IN_HOUR = 3600000,
  MILLISECONDS_IN_MINUTE = 60000,
  defaultMask = 'YYYY-MM-DDTHH:mm:ss.SSSZ',
  token = /\[((?:[^\]\\]|\\]|\\)*)\]|d{1,4}|M{1,4}|m{1,2}|w{1,2}|Qo|Do|D{1,4}|YY(?:YY)?|H{1,2}|h{1,2}|s{1,2}|S{1,3}|Z{1,2}|a{1,2}|[AQExX]/g,
  reverseToken = /(\[[^\]]*\])|d{1,4}|M{1,4}|m{1,2}|w{1,2}|Qo|Do|D{1,4}|YY(?:YY)?|H{1,2}|h{1,2}|s{1,2}|S{1,3}|Z{1,2}|a{1,2}|[AQExX]|([.*+:?^,\s${}()|\\]+)/g,
  regexStore = {};

function getRegexData (mask, locale) {
  const
    days = '(' + locale.days.join('|') + ')',
    key = mask + days;

  if (regexStore[key] !== void 0) {
    return regexStore[key]
  }

  const
    daysShort = '(' + locale.daysShort.join('|') + ')',
    months = '(' + locale.months.join('|') + ')',
    monthsShort = '(' + locale.monthsShort.join('|') + ')';

  const map = {};
  let index = 0;

  const regexText = mask.replace(reverseToken, match => {
    index++;
    switch (match) {
      case 'YY':
        map.YY = index;
        return '(-?\\d{1,2})'
      case 'YYYY':
        map.YYYY = index;
        return '(-?\\d{1,4})'
      case 'M':
        map.M = index;
        return '(\\d{1,2})'
      case 'MM':
        map.M = index; // bumping to M
        return '(\\d{2})'
      case 'MMM':
        map.MMM = index;
        return monthsShort
      case 'MMMM':
        map.MMMM = index;
        return months
      case 'D':
        map.D = index;
        return '(\\d{1,2})'
      case 'Do':
        map.D = index++; // bumping to D
        return '(\\d{1,2}(st|nd|rd|th))'
      case 'DD':
        map.D = index; // bumping to D
        return '(\\d{2})'
      case 'H':
        map.H = index;
        return '(\\d{1,2})'
      case 'HH':
        map.H = index; // bumping to H
        return '(\\d{2})'
      case 'h':
        map.h = index;
        return '(\\d{1,2})'
      case 'hh':
        map.h = index; // bumping to h
        return '(\\d{2})'
      case 'm':
        map.m = index;
        return '(\\d{1,2})'
      case 'mm':
        map.m = index; // bumping to m
        return '(\\d{2})'
      case 's':
        map.s = index;
        return '(\\d{1,2})'
      case 'ss':
        map.s = index; // bumping to s
        return '(\\d{2})'
      case 'S':
        map.S = index;
        return '(\\d{1})'
      case 'SS':
        map.S = index; // bump to S
        return '(\\d{2})'
      case 'SSS':
        map.S = index; // bump to S
        return '(\\d{3})'
      case 'A':
        map.A = index;
        return '(AM|PM)'
      case 'a':
        map.a = index;
        return '(am|pm)'
      case 'aa':
        map.aa = index;
        return '(a\\.m\\.|p\\.m\\.)'

      case 'ddd':
        return daysShort
      case 'dddd':
        return days
      case 'Q':
      case 'd':
      case 'E':
        return '(\\d{1})'
      case 'Qo':
        return '(1st|2nd|3rd|4th)'
      case 'DDD':
      case 'DDDD':
        return '(\\d{1,3})'
      case 'w':
        return '(\\d{1,2})'
      case 'ww':
        return '(\\d{2})'

      case 'Z': // to split: (?:(Z)()()|([+-])?(\\d{2}):?(\\d{2}))
        return '(Z|[+-]\\d{2}:\\d{2})'
      case 'ZZ':
        return '(Z|[+-]\\d{2}\\d{2})'

      case 'X':
        return '(-?\\d+)'
      case 'x':
        return '(-?\\d{4,})'

      default:
        index--;
        if (match[0] === '[') {
          match = match.substring(1, match.length - 1);
        }
        return match.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    }
  });

  const res = { map, regex: new RegExp('^' + regexText + '$') };
  regexStore[key] = res;

  return res
}

function __splitDate (str, mask, locale, calendar) {
  const date = {
    year: null,
    month: null,
    day: null,
    hour: null,
    minute: null,
    second: null,
    millisecond: null,
    dateHash: null,
    timeHash: null
  };

  if (
    str === void 0 ||
    str === null ||
    str === '' ||
    typeof str !== 'string'
  ) {
    return date
  }

  if (mask === void 0) {
    mask = defaultMask;
  }

  const
    langOpts = locale !== void 0 ? locale : lang.props.date,
    months = langOpts.months,
    monthsShort = langOpts.monthsShort;

  const { regex, map } = getRegexData(mask, langOpts);

  const match = str.match(regex);

  if (match === null) {
    return date
  }

  if (map.YYYY !== void 0) {
    date.year = parseInt(match[map.YYYY], 10);
  }
  else if (map.YY !== void 0) {
    const y = parseInt(match[map.YY], 10);
    date.year = y < 0 ? y : 2000 + y;
  }

  if (map.M !== void 0) {
    date.month = parseInt(match[map.M], 10);
    if (date.month < 1 || date.month > 12) {
      return date
    }
  }
  else if (map.MMM !== void 0) {
    date.month = monthsShort.indexOf(match[map.MMM]) + 1;
  }
  else if (map.MMMM !== void 0) {
    date.month = months.indexOf(match[map.MMMM]) + 1;
  }

  if (map.D !== void 0) {
    date.day = parseInt(match[map.D], 10);

    if (date.year === null || date.month === null || date.day < 1) {
      return date
    }

    const maxDay = calendar !== 'persian'
      ? (new Date(date.year, date.month, 0)).getDate()
      : jalaaliMonthLength(date.year, date.month);

    if (date.day > maxDay) {
      return date
    }
  }

  if (map.H !== void 0) {
    date.hour = parseInt(match[map.H], 10) % 24;
  }
  else if (map.h !== void 0) {
    date.hour = parseInt(match[map.h], 10);
    if (
      (map.A && match[map.A] === 'PM') ||
      (map.a && match[map.a] === 'pm') ||
      (map.aa && match[map.aa] === 'p.m.')
    ) {
      date.hour += 12;
    }
    date.hour = date.hour % 12 || 12;
  }

  if (map.m !== void 0) {
    date.minute = parseInt(match[map.m], 10) % 60;
  }

  if (map.s !== void 0) {
    date.second = parseInt(match[map.s], 10) % 60;
  }

  if (map.S !== void 0) {
    date.millisecond = parseInt(match[map.S], 10) * 10 ** (3 - match[map.S].length);
  }

  date.dateHash = date.year + '/' + pad(date.month) + '/' + pad(date.day);
  date.timeHash = pad(date.hour) + ':' + pad(date.minute) + ':' + pad(date.second);

  return date
}

function formatTimezone (offset, delimeter = '') {
  const
    sign = offset > 0 ? '-' : '+',
    absOffset = Math.abs(offset),
    hours = Math.floor(absOffset / 60),
    minutes = absOffset % 60;

  return sign + pad(hours) + delimeter + pad(minutes)
}

function getWeekOfYear (date) {
  // Remove time components of date
  const thursday = new Date(date.getFullYear(), date.getMonth(), date.getDate());

  // Change date to Thursday same week
  thursday.setDate(thursday.getDate() - ((thursday.getDay() + 6) % 7) + 3);

  // Take January 4th as it is always in week 1 (see ISO 8601)
  const firstThursday = new Date(thursday.getFullYear(), 0, 4);

  // Change date to Thursday same week
  firstThursday.setDate(firstThursday.getDate() - ((firstThursday.getDay() + 6) % 7) + 3);

  // Check if daylight-saving-time-switch occurred and correct for it
  const ds = thursday.getTimezoneOffset() - firstThursday.getTimezoneOffset();
  thursday.setHours(thursday.getHours() - ds);

  // Number of weeks between target Thursday and first Thursday
  const weekDiff = (thursday - firstThursday) / (MILLISECONDS_IN_DAY * 7);
  return 1 + Math.floor(weekDiff)
}

function startOfDate (date, unit) {
  const t = new Date(date);
  switch (unit) {
    case 'year':
      t.setMonth(0);
    case 'month':
      t.setDate(1);
    case 'day':
      t.setHours(0);
    case 'hour':
      t.setMinutes(0);
    case 'minute':
      t.setSeconds(0);
    case 'second':
      t.setMilliseconds(0);
  }
  return t
}

function getDiff (t, sub, interval) {
  return (
    (t.getTime() - t.getTimezoneOffset() * MILLISECONDS_IN_MINUTE) -
    (sub.getTime() - sub.getTimezoneOffset() * MILLISECONDS_IN_MINUTE)
  ) / interval
}

function getDateDiff (date, subtract, unit = 'days') {
  let
    t = new Date(date),
    sub = new Date(subtract);

  switch (unit) {
    case 'years':
      return (t.getFullYear() - sub.getFullYear())

    case 'months':
      return (t.getFullYear() - sub.getFullYear()) * 12 + t.getMonth() - sub.getMonth()

    case 'days':
      return getDiff(startOfDate(t, 'day'), startOfDate(sub, 'day'), MILLISECONDS_IN_DAY)

    case 'hours':
      return getDiff(startOfDate(t, 'hour'), startOfDate(sub, 'hour'), MILLISECONDS_IN_HOUR)

    case 'minutes':
      return getDiff(startOfDate(t, 'minute'), startOfDate(sub, 'minute'), MILLISECONDS_IN_MINUTE)

    case 'seconds':
      return getDiff(startOfDate(t, 'second'), startOfDate(sub, 'second'), 1000)
  }
}

function getDayOfYear (date) {
  return getDateDiff(date, startOfDate(date, 'year'), 'days') + 1
}

function getOrdinal (n) {
  if (n >= 11 && n <= 13) {
    return `${n}th`
  }
  switch (n % 10) {
    case 1: return `${n}st`
    case 2: return `${n}nd`
    case 3: return `${n}rd`
  }
  return `${n}th`
}

const formatter = {
  // Year: 00, 01, ..., 99
  YY (date, _, forcedYear) {
    // workaround for < 1900 with new Date()
    const y = this.YYYY(date, _, forcedYear) % 100;
    return y > 0
      ? pad(y)
      : '-' + pad(Math.abs(y))
  },

  // Year: 1900, 1901, ..., 2099
  YYYY (date, _, forcedYear) {
    // workaround for < 1900 with new Date()
    return forcedYear !== void 0 && forcedYear !== null
      ? forcedYear
      : date.getFullYear()
  },

  // Month: 1, 2, ..., 12
  M (date) {
    return date.getMonth() + 1
  },

  // Month: 01, 02, ..., 12
  MM (date) {
    return pad(date.getMonth() + 1)
  },

  // Month Short Name: Jan, Feb, ...
  MMM (date, opts) {
    const langOpts = opts !== void 0 ? opts : lang.props.date;
    return langOpts.monthsShort[date.getMonth()]
  },

  // Month Name: January, February, ...
  MMMM (date, opts) {
    const langOpts = opts !== void 0 ? opts : lang.props.date;
    return langOpts.months[date.getMonth()]
  },

  // Quarter: 1, 2, 3, 4
  Q (date) {
    return Math.ceil((date.getMonth() + 1) / 3)
  },

  // Quarter: 1st, 2nd, 3rd, 4th
  Qo (date) {
    return getOrdinal(this.Q(date))
  },

  // Day of month: 1, 2, ..., 31
  D (date) {
    return date.getDate()
  },

  // Day of month: 1st, 2nd, ..., 31st
  Do (date) {
    return getOrdinal(date.getDate())
  },

  // Day of month: 01, 02, ..., 31
  DD (date) {
    return pad(date.getDate())
  },

  // Day of year: 1, 2, ..., 366
  DDD (date) {
    return getDayOfYear(date)
  },

  // Day of year: 001, 002, ..., 366
  DDDD (date) {
    return pad(getDayOfYear(date), 3)
  },

  // Day of week: 0, 1, ..., 6
  d (date) {
    return date.getDay()
  },

  // Day of week: Su, Mo, ...
  dd (date) {
    return this.dddd(date).slice(0, 2)
  },

  // Day of week: Sun, Mon, ...
  ddd (date, opts) {
    const langOpts = opts !== void 0 ? opts : lang.props.date;
    return langOpts.daysShort[date.getDay()]
  },

  // Day of week: Sunday, Monday, ...
  dddd (date, opts) {
    const langOpts = opts !== void 0 ? opts : lang.props.date;
    return langOpts.days[date.getDay()]
  },

  // Day of ISO week: 1, 2, ..., 7
  E (date) {
    return date.getDay() || 7
  },

  // Week of Year: 1 2 ... 52 53
  w (date) {
    return getWeekOfYear(date)
  },

  // Week of Year: 01 02 ... 52 53
  ww (date) {
    return pad(getWeekOfYear(date))
  },

  // Hour: 0, 1, ... 23
  H (date) {
    return date.getHours()
  },

  // Hour: 00, 01, ..., 23
  HH (date) {
    return pad(date.getHours())
  },

  // Hour: 1, 2, ..., 12
  h (date) {
    const hours = date.getHours();
    if (hours === 0) {
      return 12
    }
    if (hours > 12) {
      return hours % 12
    }
    return hours
  },

  // Hour: 01, 02, ..., 12
  hh (date) {
    return pad(this.h(date))
  },

  // Minute: 0, 1, ..., 59
  m (date) {
    return date.getMinutes()
  },

  // Minute: 00, 01, ..., 59
  mm (date) {
    return pad(date.getMinutes())
  },

  // Second: 0, 1, ..., 59
  s (date) {
    return date.getSeconds()
  },

  // Second: 00, 01, ..., 59
  ss (date) {
    return pad(date.getSeconds())
  },

  // 1/10 of second: 0, 1, ..., 9
  S (date) {
    return Math.floor(date.getMilliseconds() / 100)
  },

  // 1/100 of second: 00, 01, ..., 99
  SS (date) {
    return pad(Math.floor(date.getMilliseconds() / 10))
  },

  // Millisecond: 000, 001, ..., 999
  SSS (date) {
    return pad(date.getMilliseconds(), 3)
  },

  // Meridiem: AM, PM
  A (date) {
    return this.H(date) < 12 ? 'AM' : 'PM'
  },

  // Meridiem: am, pm
  a (date) {
    return this.H(date) < 12 ? 'am' : 'pm'
  },

  // Meridiem: a.m., p.m.
  aa (date) {
    return this.H(date) < 12 ? 'a.m.' : 'p.m.'
  },

  // Timezone: -01:00, +00:00, ... +12:00
  Z (date) {
    return formatTimezone(date.getTimezoneOffset(), ':')
  },

  // Timezone: -0100, +0000, ... +1200
  ZZ (date) {
    return formatTimezone(date.getTimezoneOffset())
  },

  // Seconds timestamp: 512969520
  X (date) {
    return Math.floor(date.getTime() / 1000)
  },

  // Milliseconds timestamp: 512969520900
  x (date) {
    return date.getTime()
  }
};

function formatDate (val, mask, opts, __forcedYear) {
  if (
    (val !== 0 && !val) ||
    val === Infinity ||
    val === -Infinity
  ) {
    return
  }

  let date = new Date(val);

  if (isNaN(date)) {
    return
  }

  if (mask === void 0) {
    mask = defaultMask;
  }

  return mask.replace(
    token,
    (match, text) => match in formatter
      ? formatter[match](date, opts, __forcedYear)
      : (text === void 0 ? match : text.split('\\]').join(']'))
  )
}

const yearsInterval = 20;

Vue.extend({
  name: 'QDate',

  mixins: [ DateTimeMixin ],

  props: {
    emitImmediately: Boolean,

    mask: {
      default: 'YYYY/MM/DD'
    },

    defaultYearMonth: {
      type: String,
      validator: v => /^-?[\d]+\/[0-1]\d$/.test(v)
    },

    events: [Array, Function],
    eventColor: [String, Function],

    options: [Array, Function],

    firstDayOfWeek: [String, Number],
    todayBtn: Boolean,
    minimal: Boolean,
    defaultView: {
      type: String,
      default: 'Calendar',
      validator: v => ['Calendar', 'Years', 'Months'].includes(v)
    }
  },

  data () {
    const { inner, external } = this.__getModels(this.value, this.mask, this.__getComputedLocale());
    return {
      view: this.defaultView,
      monthDirection: 'left',
      yearDirection: 'left',
      startYear: inner.year - inner.year % yearsInterval,
      innerModel: inner,
      extModel: external
    }
  },

  watch: {
    value (v) {
      const { inner, external } = this.__getModels(v, this.mask, this.__getComputedLocale());

      if (
        this.extModel.dateHash !== external.dateHash ||
        this.extModel.timeHash !== external.timeHash
      ) {
        this.extModel = external;
      }

      if (inner.dateHash !== this.innerModel.dateHash) {
        this.monthDirection = this.innerModel.dateHash < inner.dateHash ? 'left' : 'right';
        if (inner.year !== this.innerModel.year) {
          this.yearDirection = this.monthDirection;
        }

        this.$nextTick(() => {
          this.startYear = inner.year - inner.year % yearsInterval;
          this.innerModel = inner;
        });
      }
    },

    view () {
      this.$refs.blurTarget !== void 0 && this.$refs.blurTarget.focus();
    }
  },

  computed: {
    classes () {
      const type = this.landscape === true ? 'landscape' : 'portrait';
      return `q-date--${type} q-date--${type}-${this.minimal === true ? 'minimal' : 'standard'}` +
        (this.dark === true ? ' q-date--dark' : '') +
        (this.readonly === true ? ' q-date--readonly' : '') +
        (this.disable === true ? ' disabled' : '')
    },

    headerTitle () {
      const model = this.extModel;
      if (model.dateHash === null) { return ' --- ' }

      let date;

      if (this.calendar !== 'persian') {
        date = new Date(model.year, model.month - 1, model.day);
      }
      else {
        const gDate = toGregorian(model.year, model.month, model.day);
        date = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
      }

      if (isNaN(date.valueOf()) === true) { return ' --- ' }

      if (this.computedLocale.headerTitle !== void 0) {
        return this.computedLocale.headerTitle(date, model)
      }

      return this.computedLocale.daysShort[ date.getDay() ] + ', ' +
        this.computedLocale.monthsShort[ model.month - 1 ] + ' ' +
        model.day
    },

    headerSubtitle () {
      return this.extModel.year !== null
        ? this.extModel.year
        : ' --- '
    },

    dateArrow () {
      const val = [ this.$q.iconSet.datetime.arrowLeft, this.$q.iconSet.datetime.arrowRight ];
      return this.$q.lang.rtl ? val.reverse() : val
    },

    computedFirstDayOfWeek () {
      return this.firstDayOfWeek !== void 0
        ? Number(this.firstDayOfWeek)
        : this.computedLocale.firstDayOfWeek
    },

    daysOfWeek () {
      const
        days = this.computedLocale.daysShort,
        first = this.computedFirstDayOfWeek;

      return first > 0
        ? days.slice(first, 7).concat(days.slice(0, first))
        : days
    },

    daysInMonth () {
      return this.__getDaysInMonth(this.innerModel)
    },

    today () {
      return this.__getCurrentDate()
    },

    evtFn () {
      return typeof this.events === 'function'
        ? this.events
        : date => this.events.includes(date)
    },

    evtColor () {
      return typeof this.eventColor === 'function'
        ? this.eventColor
        : date => this.eventColor
    },

    isInSelection () {
      return typeof this.options === 'function'
        ? this.options
        : date => this.options.includes(date)
    },

    days () {
      let date, endDay;

      const res = [];

      if (this.calendar !== 'persian') {
        date = new Date(this.innerModel.year, this.innerModel.month - 1, 1);
        endDay = (new Date(this.innerModel.year, this.innerModel.month - 1, 0)).getDate();
      }
      else {
        const gDate = toGregorian(this.innerModel.year, this.innerModel.month, 1);
        date = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        let prevJM = this.innerModel.month - 1;
        let prevJY = this.innerModel.year;
        if (prevJM === 0) {
          prevJM = 12;
          prevJY--;
        }
        endDay = jalaaliMonthLength(prevJY, prevJM);
      }

      const days = (date.getDay() - this.computedFirstDayOfWeek - 1);

      const len = days < 0 ? days + 7 : days;
      if (len < 6) {
        for (let i = endDay - len; i <= endDay; i++) {
          res.push({ i });
        }
      }

      const
        index = res.length,
        prefix = this.innerModel.year + '/' + pad(this.innerModel.month) + '/';

      for (let i = 1; i <= this.daysInMonth; i++) {
        const day = prefix + pad(i);

        if (this.options !== void 0 && this.isInSelection(day) !== true) {
          res.push({ i });
        }
        else {
          const event = this.events !== void 0 && this.evtFn(day) === true
            ? this.evtColor(day)
            : false;

          res.push({ i, in: true, flat: true, event });
        }
      }

      if (this.innerModel.year === this.extModel.year && this.innerModel.month === this.extModel.month) {
        const i = index + this.innerModel.day - 1;
        res[i] !== void 0 && Object.assign(res[i], {
          unelevated: true,
          flat: false,
          color: this.computedColor,
          textColor: this.computedTextColor
        });
      }

      if (this.innerModel.year === this.today.year && this.innerModel.month === this.today.month) {
        res[index + this.today.day - 1].today = true;
      }

      const left = res.length % 7;
      if (left > 0) {
        const afterDays = 7 - left;
        for (let i = 1; i <= afterDays; i++) {
          res.push({ i });
        }
      }

      return res
    }
  },

  methods: {
    __getModels (val, mask, locale) {
      const external = __splitDate(val, mask, locale, this.calendar);
      return {
        external,
        inner: external.dateHash === null
          ? this.__getDefaultModel()
          : { ...external }
      }
    },

    __getDefaultModel () {
      let year, month;

      if (this.defaultYearMonth !== void 0) {
        const d = this.defaultYearMonth.split('/');
        year = parseInt(d[0], 10);
        month = parseInt(d[1], 10);
      }
      else {
        // may come from data() where computed
        // props are not yet available
        const d = this.today !== void 0
          ? this.today
          : this.__getCurrentDate();

        year = d.year;
        month = d.month;
      }

      return {
        year,
        month,
        day: 1,
        hour: 0,
        minute: 0,
        second: 0,
        millisecond: 0,
        dateHash: year + '/' + pad(month) + '/01'
      }
    },

    __getHeader (h) {
      if (this.minimal === true) { return }

      return h('div', {
        staticClass: 'q-date__header',
        class: this.headerClass
      }, [
        h('div', {
          staticClass: 'relative-position'
        }, [
          h('transition', {
            props: {
              name: 'q-transition--fade'
            }
          }, [
            h('div', {
              key: 'h-yr-' + this.headerSubtitle,
              staticClass: 'q-date__header-subtitle q-date__header-link',
              class: this.view === 'Years' ? 'q-date__header-link--active' : 'cursor-pointer',
              attrs: { tabindex: this.computedTabindex },
              on: {
                click: () => { this.view = 'Years'; },
                keyup: e => { e.keyCode === 13 && (this.view = 'Years'); }
              }
            }, [ this.headerSubtitle ])
          ])
        ]),

        h('div', {
          staticClass: 'q-date__header-title relative-position flex no-wrap'
        }, [
          h('div', {
            staticClass: 'relative-position col'
          }, [
            h('transition', {
              props: {
                name: 'q-transition--fade'
              }
            }, [
              h('div', {
                key: 'h-sub' + this.headerTitle,
                staticClass: 'q-date__header-title-label q-date__header-link',
                class: this.view === 'Calendar' ? 'q-date__header-link--active' : 'cursor-pointer',
                attrs: { tabindex: this.computedTabindex },
                on: {
                  click: () => { this.view = 'Calendar'; },
                  keyup: e => { e.keyCode === 13 && (this.view = 'Calendar'); }
                }
              }, [ this.headerTitle ])
            ])
          ]),

          this.todayBtn === true ? h(QBtn, {
            staticClass: 'q-date__header-today',
            props: {
              icon: this.$q.iconSet.datetime.today,
              flat: true,
              size: 'sm',
              round: true,
              tabindex: this.computedTabindex
            },
            on: {
              click: this.__setToday
            }
          }) : null
        ])
      ])
    },

    __getNavigation (h, { label, view, key, dir, goTo, cls }) {
      return [
        h('div', {
          staticClass: 'row items-center q-date__arrow'
        }, [
          h(QBtn, {
            props: {
              round: true,
              dense: true,
              size: 'sm',
              flat: true,
              icon: this.dateArrow[0],
              tabindex: this.computedTabindex
            },
            on: {
              click () { goTo(-1); }
            }
          })
        ]),

        h('div', {
          staticClass: 'relative-position overflow-hidden flex flex-center' + cls
        }, [
          h('transition', {
            props: {
              name: 'q-transition--jump-' + dir
            }
          }, [
            h('div', { key }, [
              h(QBtn, {
                props: {
                  flat: true,
                  dense: true,
                  noCaps: true,
                  label,
                  tabindex: this.computedTabindex
                },
                on: {
                  click: () => { this.view = view; }
                }
              })
            ])
          ])
        ]),

        h('div', {
          staticClass: 'row items-center q-date__arrow'
        }, [
          h(QBtn, {
            props: {
              round: true,
              dense: true,
              size: 'sm',
              flat: true,
              icon: this.dateArrow[1],
              tabindex: this.computedTabindex
            },
            on: {
              click () { goTo(1); }
            }
          })
        ])
      ]
    },

    __getCalendarView (h) {
      return [
        h('div', {
          key: 'calendar-view',
          staticClass: 'q-date__view q-date__calendar'
        }, [
          h('div', {
            staticClass: 'q-date__navigation row items-center no-wrap'
          }, this.__getNavigation(h, {
            label: this.computedLocale.months[ this.innerModel.month - 1 ],
            view: 'Months',
            key: this.innerModel.month,
            dir: this.monthDirection,
            goTo: this.__goToMonth,
            cls: ' col'
          }).concat(this.__getNavigation(h, {
            label: this.innerModel.year,
            view: 'Years',
            key: this.innerModel.year,
            dir: this.yearDirection,
            goTo: this.__goToYear,
            cls: ''
          }))),

          h('div', {
            staticClass: 'q-date__calendar-weekdays row items-center no-wrap'
          }, this.daysOfWeek.map(day => h('div', { staticClass: 'q-date__calendar-item' }, [ h('div', [ day ]) ]))),

          h('div', {
            staticClass: 'q-date__calendar-days-container relative-position overflow-hidden'
          }, [
            h('transition', {
              props: {
                name: 'q-transition--slide-' + this.monthDirection
              }
            }, [
              h('div', {
                key: this.innerModel.year + '/' + this.innerModel.month,
                staticClass: 'q-date__calendar-days fit'
              }, this.days.map(day => h('div', {
                staticClass: `q-date__calendar-item q-date__calendar-item--${day.in === true ? 'in' : 'out'}`
              }, [
                day.in === true
                  ? h(QBtn, {
                    staticClass: day.today === true ? 'q-date__today' : null,
                    props: {
                      dense: true,
                      flat: day.flat,
                      unelevated: day.unelevated,
                      color: day.color,
                      textColor: day.textColor,
                      label: day.i,
                      tabindex: this.computedTabindex
                    },
                    on: {
                      click: () => { this.__setDay(day.i); }
                    }
                  }, day.event !== false ? [
                    h('div', { staticClass: 'q-date__event bg-' + day.event })
                  ] : null)
                  : h('div', [ day.i ])
              ])))
            ])
          ])
        ])
      ]
    },

    __getMonthsView (h) {
      const currentYear = this.innerModel.year === this.today.year;

      const content = this.computedLocale.monthsShort.map((month, i) => {
        const active = this.innerModel.month === i + 1;

        return h('div', {
          staticClass: 'q-date__months-item flex flex-center'
        }, [
          h(QBtn, {
            staticClass: currentYear === true && this.today.month === i + 1 ? 'q-date__today' : null,
            props: {
              flat: !active,
              label: month,
              unelevated: active,
              color: active ? this.computedColor : null,
              textColor: active ? this.computedTextColor : null,
              tabindex: this.computedTabindex
            },
            on: {
              click: () => { this.__setMonth(i + 1); }
            }
          })
        ])
      });

      return h('div', {
        key: 'months-view',
        staticClass: 'q-date__view q-date__months column flex-center'
      }, [
        h('div', {
          staticClass: 'q-date__months-content row'
        }, content)
      ])
    },

    __getYearsView (h) {
      const
        start = this.startYear,
        stop = start + yearsInterval,
        years = [];

      for (let i = start; i <= stop; i++) {
        const active = this.innerModel.year === i;

        years.push(
          h('div', {
            staticClass: 'q-date__years-item flex flex-center'
          }, [
            h(QBtn, {
              staticClass: this.today.year === i ? 'q-date__today' : null,
              props: {
                flat: !active,
                label: i,
                dense: true,
                unelevated: active,
                color: active ? this.computedColor : null,
                textColor: active ? this.computedTextColor : null,
                tabindex: this.computedTabindex
              },
              on: {
                click: () => { this.__setYear(i); }
              }
            })
          ])
        );
      }

      return h('div', {
        staticClass: 'q-date__view q-date__years flex flex-center full-height'
      }, [
        h('div', {
          staticClass: 'col-auto'
        }, [
          h(QBtn, {
            props: {
              round: true,
              dense: true,
              flat: true,
              icon: this.dateArrow[0],
              tabindex: this.computedTabindex
            },
            on: {
              click: () => { this.startYear -= yearsInterval; }
            }
          })
        ]),

        h('div', {
          staticClass: 'q-date__years-content col full-height row items-center'
        }, years),

        h('div', {
          staticClass: 'col-auto'
        }, [
          h(QBtn, {
            props: {
              round: true,
              dense: true,
              flat: true,
              icon: this.dateArrow[1],
              tabindex: this.computedTabindex
            },
            on: {
              click: () => { this.startYear += yearsInterval; }
            }
          })
        ])
      ])
    },

    __getDaysInMonth (obj) {
      return this.calendar !== 'persian'
        ? (new Date(obj.year, obj.month, 0)).getDate()
        : jalaaliMonthLength(obj.year, obj.month)
    },

    __goToMonth (offset) {
      let
        month = Number(this.innerModel.month) + offset,
        yearDir = this.yearDirection;

      if (month === 13) {
        month = 1;
        this.innerModel.year++;
        yearDir = 'left';
      }
      else if (month === 0) {
        month = 12;
        this.innerModel.year--;
        yearDir = 'right';
      }

      this.monthDirection = offset > 0 ? 'left' : 'right';
      this.yearDirection = yearDir;
      this.innerModel.month = month;

      this.emitImmediately === true && this.__updateValue({}, 'month');
    },

    __goToYear (offset) {
      this.monthDirection = this.yearDirection = offset > 0 ? 'left' : 'right';
      this.innerModel.year = Number(this.innerModel.year) + offset;
      this.emitImmediately === true && this.__updateValue({}, 'year');
    },

    __setYear (year) {
      this.innerModel.year = year;
      this.emitImmediately === true && this.__updateValue({ year }, 'year');
      this.view = 'Calendar';
    },

    __setMonth (month) {
      this.innerModel.month = month;
      this.emitImmediately === true && this.__updateValue({ month }, 'month');
      this.view = 'Calendar';
    },

    __setDay (day) {
      this.__updateValue({ day }, 'day');
    },

    __setToday () {
      this.__updateValue({ ...this.today }, 'today');
      this.view = 'Calendar';
    },

    __updateValue (date, reason) {
      if (date.year === void 0) {
        date.year = this.innerModel.year;
      }
      if (date.month === void 0) {
        date.month = this.innerModel.month;
      }
      if (
        date.day === void 0 ||
        (this.emitImmediately === true && (reason === 'year' || reason === 'month'))
      ) {
        date.day = this.innerModel.day;
        const maxDay = this.emitImmediately === true
          ? this.__getDaysInMonth(date)
          : this.daysInMonth;

        date.day = Math.min(date.day, maxDay);
      }

      const val = formatDate(
        new Date(
          date.year,
          date.month - 1,
          date.day,
          this.extModel.hour,
          this.extModel.minute,
          this.extModel.second,
          this.extModel.millisecond
        ),
        this.mask,
        this.computedLocale,
        date.year
      );

      if (val !== this.value) {
        this.$emit('input', val, reason, date);
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-date',
      class: this.classes,
      on: this.$listeners
    }, [
      this.__getHeader(h),

      h('div', {
        staticClass: 'q-date__content relative-position overflow-auto',
        attrs: { tabindex: -1 },
        ref: 'blurTarget'
      }, [
        h('transition', {
          props: {
            name: 'q-transition--fade'
          }
        }, [
          this[`__get${this.view}View`](h)
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QTime',

  mixins: [ DateTimeMixin ],

  directives: {
    TouchPan
  },

  props: {
    mask: {
      default: null
    },

    format24h: {
      type: Boolean,
      default: null
    },

    options: Function,
    hourOptions: Array,
    minuteOptions: Array,
    secondOptions: Array,

    withSeconds: Boolean,
    nowBtn: Boolean
  },

  data () {
    const model = __splitDate(
      this.value,
      this.__getComputedMask(),
      this.__getComputedLocale(),
      this.calendar
    );

    let view = 'Hour';

    if (model.hour !== null) {
      if (model.minute === null) {
        view = 'Minute';
      }
      else if (this.withSeconds === true && model.second === null) {
        view = 'Second';
      }
    }

    return {
      view,
      isAM: model.hour === null || model.hour < 12,
      innerModel: model
    }
  },

  watch: {
    value (v) {
      const model = __splitDate(v, this.computedMask, this.computedLocale, this.calendar);

      if (
        model.dateHash !== this.innerModel.dateHash ||
        model.timeHash !== this.innerModel.timeHash
      ) {
        this.innerModel = model;

        if (model.hour === null) {
          this.view = 'Hour';
        }
        else {
          this.isAM = model.hour < 12;
        }
      }
    }
  },

  computed: {
    classes () {
      return {
        'q-time--dark': this.dark,
        'q-time--readonly': this.readonly,
        'disabled': this.disable,
        [`q-time--${this.landscape === true ? 'landscape' : 'portrait'}`]: true
      }
    },

    computedMask () {
      return this.__getComputedMask()
    },

    stringModel () {
      const time = this.innerModel;

      return {
        hour: time.hour === null
          ? '--'
          : (
            this.computedFormat24h === true
              ? pad(time.hour)
              : String(
                this.isAM === true
                  ? (time.hour === 0 ? 12 : time.hour)
                  : (time.hour > 12 ? time.hour - 12 : time.hour)
              )
          ),
        minute: time.minute === null
          ? '--'
          : pad(time.minute),
        second: time.second === null
          ? '--'
          : pad(time.second)
      }
    },

    computedFormat24h () {
      return this.format24h !== null
        ? this.format24h
        : this.$q.lang.date.format24h
    },

    pointerStyle () {
      const
        forHour = this.view === 'Hour',
        divider = forHour === true ? 12 : 60,
        amount = this.innerModel[this.view.toLowerCase()],
        degrees = Math.round(amount * (360 / divider)) - 180;

      let transform = `rotate3d(0,0,1,${degrees}deg) translate3d(-50%,0,0)`;

      if (
        forHour === true &&
        this.computedFormat24h === true &&
        !(this.innerModel.hour > 0 && this.innerModel.hour < 13)
      ) {
        transform += ' scale3d(.7,.7,.7)';
      }

      return { transform }
    },

    minLink () {
      return this.innerModel.hour !== null
    },

    secLink () {
      return this.minLink === true && this.innerModel.minute !== null
    },

    hourInSelection () {
      return this.hourOptions !== void 0
        ? val => this.hourOptions.includes(val)
        : (
          this.options !== void 0
            ? val => this.options(val, null, null)
            : void 0
        )
    },

    minuteInSelection () {
      return this.minuteOptions !== void 0
        ? val => this.minuteOptions.includes(val)
        : (
          this.options !== void 0
            ? val => this.options(this.innerModel.hour, val, null)
            : void 0
        )
    },

    secondInSelection () {
      return this.secondOptions !== void 0
        ? val => this.secondOptions.includes(val)
        : (
          this.options !== void 0
            ? val => this.options(this.innerModel.hour, this.innerModel.minute, val)
            : void 0
        )
    },

    positions () {
      let start, end, offset = 0, step = 1, inSel;

      if (this.view === 'Hour') {
        inSel = this.hourInSelection;

        if (this.computedFormat24h === true) {
          start = 0;
          end = 23;
        }
        else {
          start = 0;
          end = 11;

          if (this.isAM === false) {
            offset = 12;
          }
        }
      }
      else {
        start = 0;
        end = 55;
        step = 5;

        if (this.view === 'Minute') {
          inSel = this.minuteInSelection;
        }
        else {
          inSel = this.secondInSelection;
        }
      }

      const pos = [];

      for (let val = start, index = start; val <= end; val += step, index++) {
        const
          actualVal = val + offset,
          disable = inSel !== void 0 && inSel(actualVal) === false,
          label = this.view === 'Hour' && val === 0
            ? (this.format24h === true ? '00' : '12')
            : val;

        pos.push({ val: actualVal, index, disable, label });
      }

      return pos
    }
  },

  methods: {
    __click (evt) {
      this.__drag({ isFirst: true, evt });
      this.__drag({ isFinal: true, evt });
    },

    __drag (event) {
      // cases when on a popup getting closed
      // on previously emitted value
      if (this._isBeingDestroyed === true || this._isDestroyed === true) {
        return
      }

      if (event.isFirst) {
        const
          clock = this.$refs.clock,
          { top, left, width } = clock.getBoundingClientRect(),
          dist = width / 2;

        this.dragging = {
          top: top + dist,
          left: left + dist,
          dist: dist * 0.7
        };
        this.dragCache = null;
        this.__updateClock(event.evt);
        return
      }

      this.__updateClock(event.evt);

      if (event.isFinal) {
        this.dragging = false;

        if (this.view === 'Hour') {
          this.view = 'Minute';
        }
        else if (this.withSeconds && this.view === 'Minute') {
          this.view = 'Second';
        }
      }
    },

    __updateClock (evt) {
      let
        val,
        pos = position(evt),
        height = Math.abs(pos.top - this.dragging.top),
        distance = Math.sqrt(
          Math.pow(Math.abs(pos.top - this.dragging.top), 2) +
          Math.pow(Math.abs(pos.left - this.dragging.left), 2)
        ),
        angle = Math.asin(height / distance) * (180 / Math.PI);

      if (pos.top < this.dragging.top) {
        angle = this.dragging.left < pos.left ? 90 - angle : 270 + angle;
      }
      else {
        angle = this.dragging.left < pos.left ? angle + 90 : 270 - angle;
      }

      if (this.view === 'Hour') {
        val = Math.round(angle / 30);

        if (this.computedFormat24h === true) {
          if (distance < this.dragging.dist) {
            if (val !== 0) {
              val += 12;
            }
          }
          else if (val === 0) {
            val = 12;
          }
        }
        else if (this.isAM === true && val === 12) {
          val = 0;
        }
        else if (this.isAM === false && val !== 12) {
          val += 12;
        }

        if (val === 24) {
          val = 0;
        }
      }
      else {
        val = Math.round(angle / 6);

        if (val === 60) {
          val = 0;
        }
      }

      if (this.dragCache === val) {
        return
      }

      const opt = this[`${this.view.toLowerCase()}InSelection`];

      if (opt !== void 0 && opt(val) !== true) {
        return
      }

      this.dragCache = val;
      this[`__set${this.view}`](val);
    },

    __onKeyupHour (e) {
      if (e.keyCode === 13) { // ENTER
        this.view = 'Hour';
      }
      else {
        const
          wrap = this.computedFormat24h === true ? 24 : 12,
          offset = this.computedFormat24h !== true && this.isAM === false ? 12 : 0;

        if (e.keyCode === 37) { // ARROW LEFT
          this.__setHour(offset + (24 + this.innerModel.hour - 1) % wrap);
        }
        else if (e.keyCode === 39) { // ARROW RIGHT
          this.__setHour(offset + (24 + this.innerModel.hour + 1) % wrap);
        }
      }
    },

    __onKeyupMinute (e) {
      if (e.keyCode === 13) { // ENTER
        this.view = 'Minute';
      }
      else if (e.keyCode === 37) { // ARROW LEFT
        this.__setMinute((60 + this.innerModel.minute - 1) % 60);
      }
      else if (e.keyCode === 39) { // ARROW RIGHT
        this.__setMinute((60 + this.innerModel.minute + 1) % 60);
      }
    },

    __onKeyupSecond (e) {
      if (e.keyCode === 13) { // ENTER
        this.view = 'Second';
      }
      else if (e.keyCode === 37) { // ARROW LEFT
        this.__setSecond((60 + this.innerModel.second - 1) % 60);
      }
      else if (e.keyCode === 39) { // ARROW RIGHT
        this.__setSecond((60 + this.innerModel.second + 1) % 60);
      }
    },

    __getHeader (h) {
      const label = [
        h('div', {
          staticClass: 'q-time__link',
          class: this.view === 'Hour' ? 'q-time__link--active' : 'cursor-pointer',
          attrs: { tabindex: this.computedTabindex },
          on: {
            click: () => { this.view = 'Hour'; },
            keyup: this.__onKeyupHour
          }
        }, [ this.stringModel.hour ]),
        h('div', [ ':' ]),
        h(
          'div',
          this.minLink === true
            ? {
              staticClass: 'q-time__link',
              class: this.view === 'Minute' ? 'q-time__link--active' : 'cursor-pointer',
              attrs: { tabindex: this.computedTabindex },
              on: {
                click: () => { this.view = 'Minute'; },
                keyup: this.__onKeyupMinute
              }
            }
            : { staticClass: 'q-time__link' },
          [ this.stringModel.minute ]
        )
      ];

      if (this.withSeconds === true) {
        label.push(
          h('div', [ ':' ]),
          h(
            'div',
            this.secLink === true
              ? {
                staticClass: 'q-time__link',
                class: this.view === 'Second' ? 'q-time__link--active' : 'cursor-pointer',
                attrs: { tabindex: this.computedTabindex },
                on: {
                  click: () => { this.view = 'Second'; },
                  keyup: this.__onKeyupSecond
                }
              }
              : { staticClass: 'q-time__link' },
            [ this.stringModel.second ]
          )
        );
      }

      return h('div', {
        staticClass: 'q-time__header flex flex-center no-wrap',
        class: this.headerClass
      }, [
        h('div', {
          staticClass: 'q-time__header-label row items-center no-wrap',
          attrs: { dir: 'ltr' }
        }, label),

        this.computedFormat24h === false ? h('div', {
          staticClass: 'q-time__header-ampm column items-between no-wrap'
        }, [
          h('div', {
            staticClass: 'q-time__link',
            class: this.isAM === true ? 'q-time__link--active' : 'cursor-pointer',
            attrs: { tabindex: this.computedTabindex },
            on: {
              click: this.__setAm,
              keyup: e => { e.keyCode === 13 && this.__setAm(); }
            }
          }, [ 'AM' ]),

          h('div', {
            staticClass: 'q-time__link',
            class: this.isAM !== true ? 'q-time__link--active' : 'cursor-pointer',
            attrs: { tabindex: this.computedTabindex },
            on: {
              click: this.__setPm,
              keyup: e => { e.keyCode === 13 && this.__setPm(); }
            }
          }, [ 'PM' ])
        ]) : null
      ])
    },

    __getClock (h) {
      const
        view = this.view.toLowerCase(),
        current = this.innerModel[view],
        f24 = this.view === 'Hour' && this.computedFormat24h === true
          ? ' fmt24'
          : '';

      return h('div', {
        staticClass: 'q-time__content col relative-position'
      }, [
        h('transition', {
          props: { name: 'q-transition--scale' }
        }, [
          h('div', {
            key: 'clock' + this.view,
            staticClass: 'q-time__container-parent absolute-full'
          }, [
            h('div', {
              ref: 'clock',
              staticClass: 'q-time__container-child fit overflow-hidden'
            }, [
              h('div', {
                staticClass: 'q-time__clock cursor-pointer non-selectable',
                on: {
                  click: this.__click
                },
                directives: [{
                  name: 'touch-pan',
                  value: this.__drag,
                  modifiers: {
                    stop: true,
                    prevent: true,
                    mouse: true,
                    mouseStop: true,
                    mousePrevent: true
                  }
                }]
              }, [
                h('div', { staticClass: 'q-time__clock-circle fit' }, [
                  this.innerModel[view] !== null
                    ? h('div', {
                      staticClass: 'q-time__clock-pointer',
                      style: this.pointerStyle,
                      class: this.color !== void 0 ? `text-${this.color}` : null
                    })
                    : null,

                  this.positions.map(pos => h('div', {
                    staticClass: `q-time__clock-position row flex-center${f24} q-time__clock-pos-${pos.index}`,
                    class: pos.val === current
                      ? this.headerClass.concat(' q-time__clock-position--active')
                      : (pos.disable ? 'q-time__clock-position--disable' : null)
                  }, [ h('span', [ pos.label ]) ]))
                ])
              ])
            ])
          ])
        ]),

        this.nowBtn === true ? h(QBtn, {
          staticClass: 'q-time__now-button absolute',
          props: {
            icon: this.$q.iconSet.datetime.now,
            unelevated: true,
            size: 'sm',
            round: true,
            color: this.color,
            textColor: this.textColor,
            tabindex: this.computedTabindex
          },
          on: {
            click: this.__setNow
          }
        }) : null
      ])
    },

    __setHour (hour) {
      if (this.innerModel.hour !== hour) {
        this.innerModel.hour = hour;
        this.innerModel.minute = null;
        this.innerModel.second = null;
      }
    },

    __setMinute (minute) {
      if (this.innerModel.minute !== minute) {
        this.innerModel.minute = minute;
        this.innerModel.second = null;
        this.withSeconds !== true && this.__updateValue({ minute });
      }
    },

    __setSecond (second) {
      this.innerModel.second !== second && this.__updateValue({ second });
    },

    __setAm () {
      if (this.isAM) { return }

      this.isAM = true;

      if (this.innerModel.hour === null) { return }
      this.innerModel.hour -= 12;
      this.__verifyAndUpdate();
    },

    __setPm () {
      if (!this.isAM) { return }

      this.isAM = false;

      if (this.innerModel.hour === null) { return }
      this.innerModel.hour += 12;
      this.__verifyAndUpdate();
    },

    __setNow () {
      this.__updateValue({
        ...this.__getCurrentDate(),
        ...this.__getCurrentTime()
      });
      this.view = 'Hour';
    },

    __verifyAndUpdate () {
      if (this.hourInSelection !== void 0 && this.hourInSelection(this.innerModel.hour) !== true) {
        this.innerModel = __splitDate();
        this.isAM = true;
        this.view = 'Hour';
        return
      }

      if (this.minuteInSelection !== void 0 && this.minuteInSelection(this.innerModel.minute) !== true) {
        this.innerModel.minute = null;
        this.innerModel.second = null;
        this.view = 'Minute';
        return
      }

      if (this.withSeconds === true && this.secondInSelection !== void 0 && this.secondInSelection(this.innerModel.second) !== true) {
        this.innerModel.second = null;
        this.view = 'Second';
        return
      }

      if (this.innerModel.hour === null || this.innerModel.minute === null || (this.withSeconds === true && this.innerModel.second === null)) {
        return
      }

      this.__updateValue({});
    },

    __getComputedMask () {
      return this.mask !== null
        ? this.mask
        : `HH:mm${this.withSeconds === true ? ':ss' : ''}`
    },

    __updateValue (obj) {
      const date = {
        ...this.innerModel,
        ...obj
      };

      const val = formatDate(
        new Date(
          date.year,
          date.month === null ? null : date.month - 1,
          date.day,
          date.hour,
          date.minute,
          date.second,
          date.millisecond
        ),
        this.computedMask,
        this.computedLocale,
        date.year
      );

      if (val !== this.value) {
        this.$emit('input', val);
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-time',
      class: this.classes,
      on: this.$listeners,
      attrs: { tabindex: -1 }
    }, [
      this.__getHeader(h),
      this.__getClock(h)
    ])
  }
});

let registered = 0;

function onWheel (e) {
  if (shouldPreventScroll(e)) {
    stopAndPrevent(e);
  }
}

function shouldPreventScroll (e) {
  if (e.target === document.body || e.target.classList.contains('q-layout__backdrop')) {
    return true
  }

  const
    path = getEventPath(e),
    shift = e.shiftKey && !e.deltaX,
    scrollY = !shift && Math.abs(e.deltaX) <= Math.abs(e.deltaY),
    delta = shift || scrollY ? e.deltaY : e.deltaX;

  for (let index = 0; index < path.length; index++) {
    const el = path[index];

    if (hasScrollbar(el, scrollY)) {
      return scrollY
        ? (
          delta < 0 && el.scrollTop === 0
            ? true
            : delta > 0 && el.scrollTop + el.clientHeight === el.scrollHeight
        )
        : (
          delta < 0 && el.scrollLeft === 0
            ? true
            : delta > 0 && el.scrollLeft + el.clientWidth === el.scrollWidth
        )
    }
  }

  return true
}

function prevent$1 (register) {
  registered += register ? 1 : -1;
  if (registered > 1) { return }

  const action = register ? 'add' : 'remove';

  if (Platform.is.mobile) {
    document.body.classList[action]('q-body--prevent-scroll');
  }
  else if (Platform.is.desktop) {
    // ref. https://developers.google.com/web/updates/2017/01/scrolling-intervention
    window[`${action}EventListener`]('wheel', onWheel, listenOpts.notPassive);
  }
}

var PreventScrollMixin = {
  methods: {
    __preventScroll (state) {
      if (this.preventedScroll === void 0 && state !== true) {
        return
      }

      if (state !== this.preventedScroll) {
        this.preventedScroll = state;
        prevent$1(state);
      }
    }
  }
};

let maximizedModals = 0;

const positionClass = {
  standard: 'fixed-full flex-center',
  top: 'fixed-top justify-center',
  bottom: 'fixed-bottom justify-center',
  right: 'fixed-right items-center',
  left: 'fixed-left items-center'
};

const transitions = {
  top: ['down', 'up'],
  bottom: ['up', 'down'],
  right: ['left', 'right'],
  left: ['right', 'left']
};

var QDialog = Vue.extend({
  name: 'QDialog',

  mixins: [ ModelToggleMixin, PortalMixin, PreventScrollMixin ],

  modelToggle: {
    history: true
  },

  props: {
    persistent: Boolean,
    autoClose: Boolean,

    noEscDismiss: Boolean,
    noBackdropDismiss: Boolean,
    noRouteDismiss: Boolean,
    noRefocus: Boolean,
    noFocus: Boolean,

    seamless: Boolean,

    maximized: Boolean,
    fullWidth: Boolean,
    fullHeight: Boolean,

    square: Boolean,

    position: {
      type: String,
      default: 'standard',
      validator (val) {
        return val === 'standard' || ['top', 'bottom', 'left', 'right'].includes(val)
      }
    },

    transitionShow: {
      type: String,
      default: 'scale'
    },
    transitionHide: {
      type: String,
      default: 'scale'
    }
  },

  data () {
    return {
      transitionState: this.showing
    }
  },

  watch: {
    $route () {
      this.persistent !== true &&
        this.noRouteDismiss !== true &&
        this.seamless !== true &&
        this.hide();
    },

    showing (val) {
      if (this.position !== 'standard' || this.transitionShow !== this.transitionHide) {
        this.$nextTick(() => {
          this.transitionState = val;
        });
      }
    },

    maximized (newV, oldV) {
      if (this.showing === true) {
        this.__updateState(false, oldV);
        this.__updateState(true, newV);
      }
    },

    seamless (v) {
      this.showing === true && this.__preventScroll(!v);
    },

    useBackdrop (v) {
      if (this.$q.platform.is.desktop === true) {
        const action = `${v === true ? 'add' : 'remove'}EventListener`;
        document.body[action]('focusin', this.__onFocusChange);
      }
    }
  },

  computed: {
    classes () {
      return `q-dialog__inner--${this.maximized === true ? 'maximized' : 'minimized'} ` +
        `q-dialog__inner--${this.position} ${positionClass[this.position]}` +
        (this.fullWidth === true ? ' q-dialog__inner--fullwidth' : '') +
        (this.fullHeight === true ? ' q-dialog__inner--fullheight' : '') +
        (this.square === true ? ' q-dialog__inner--square' : '')
    },

    transition () {
      return 'q-transition--' + (
        this.position === 'standard'
          ? (this.transitionState === true ? this.transitionHide : this.transitionShow)
          : 'slide-' + transitions[this.position][this.transitionState === true ? 1 : 0]
      )
    },

    useBackdrop () {
      return this.showing === true && this.seamless !== true
    }
  },

  methods: {
    focus () {
      let node = this.__portal.$refs !== void 0 ? this.__portal.$refs.inner : void 0;

      if (node === void 0 || node.contains(document.activeElement) === true) {
        return
      }

      if (this.$q.platform.is.ios) {
        // workaround the iOS hover/touch issue
        this.avoidAutoClose = true;
        node.click();
        this.avoidAutoClose = false;
      }

      node = node.querySelector('[autofocus]') || node;
      node.focus();
    },

    shake () {
      this.focus();

      const node = this.__portal.$refs !== void 0 ? this.__portal.$refs.inner : void 0;

      if (node !== void 0) {
        node.classList.remove('q-animate--scale');
        node.classList.add('q-animate--scale');
        clearTimeout(this.shakeTimeout);
        this.shakeTimeout = setTimeout(() => {
          node.classList.remove('q-animate--scale');
        }, 170);
      }
    },

    __show (evt) {
      clearTimeout(this.timer);

      this.__refocusTarget = this.noRefocus === false
        ? document.activeElement
        : void 0;

      this.$el.dispatchEvent(create('popup-show', { bubbles: true }));

      this.__updateState(true, this.maximized);

      EscapeKey.register(this, () => {
        if (this.seamless !== true) {
          if (this.persistent === true || this.noEscDismiss === true) {
            this.maximized !== true && this.shake();
          }
          else {
            this.$emit('escape-key');
            this.hide();
          }
        }
      });

      this.__showPortal();

      if (this.noFocus !== true) {
        document.activeElement.blur();

        this.$nextTick(() => {
          this.focus();
        });
      }

      if (this.$q.platform.is.desktop === true && this.useBackdrop === true) {
        document.body.addEventListener('focusin', this.__onFocusChange);
      }

      this.timer = setTimeout(() => {
        this.$emit('show', evt);
      }, 300);
    },

    __hide (evt) {
      this.__cleanup(true);

      if (this.__refocusTarget !== void 0) {
        this.__refocusTarget.focus();
      }

      this.$el.dispatchEvent(create('popup-hide', { bubbles: true }));

      this.timer = setTimeout(() => {
        this.__hidePortal();
        this.$emit('hide', evt);
      }, 300);
    },

    __cleanup (hiding) {
      clearTimeout(this.timer);
      clearTimeout(this.shakeTimeout);

      if (this.$q.platform.is.desktop === true && this.seamless !== true) {
        document.body.removeEventListener('focusin', this.__onFocusChange);
      }

      if (hiding === true || this.showing === true) {
        EscapeKey.pop(this);
        this.__updateState(false, this.maximized);
      }
    },

    __updateState (opening, maximized) {
      if (this.seamless !== true) {
        this.__preventScroll(opening);
      }

      if (maximized === true) {
        if (opening === true) {
          maximizedModals < 1 && document.body.classList.add('q-body--dialog');
        }
        else if (maximizedModals < 2) {
          document.body.classList.remove('q-body--dialog');
        }
        maximizedModals += opening === true ? 1 : -1;
      }
    },

    __onAutoClose (e) {
      if (this.avoidAutoClose !== true) {
        this.hide(e);
        this.$listeners.click !== void 0 && this.$emit('click', e);
      }
    },

    __onBackdropClick (e) {
      if (this.persistent !== true && this.noBackdropDismiss !== true) {
        this.hide(e);
      }
      else {
        this.shake();
      }
    },

    __onFocusChange (e) {
      if (
        this.__portal !== void 0 &&
        this.__portal.$el !== void 0 &&
        // we don't have another portal opened:
        this.__portal.$el.nextElementSibling === null &&
        this.__portal.$el.contains(e.target) !== true
      ) {
        this.__portal.$refs.inner.focus();
      }
    },

    __render (h) {
      const on = {
        ...this.$listeners,
        input: stop
      };

      if (this.autoClose === true) {
        on.click = this.__onAutoClose;
      }

      return h('div', {
        staticClass: 'q-dialog fullscreen no-pointer-events',
        class: this.contentClass,
        style: this.contentStyle,
        attrs: this.$attrs
      }, [
        h('transition', {
          props: { name: 'q-transition--fade' }
        }, this.useBackdrop === true ? [
          h('div', {
            staticClass: 'q-dialog__backdrop fixed-full',
            on: {
              touchmove: stopAndPrevent, // prevent iOS page scroll
              click: this.__onBackdropClick
            }
          })
        ] : null),

        h('transition', {
          props: { name: this.transition }
        }, [
          this.showing === true ? h('div', {
            ref: 'inner',
            staticClass: 'q-dialog__inner flex no-pointer-events',
            class: this.classes,
            attrs: { tabindex: -1 },
            on
          }, slot(this, 'default')) : null
        ])
      ])
    },

    __onPortalClose (evt) {
      this.hide(evt);
    }
  },

  mounted () {
    this.value === true && this.show();
  },

  beforeDestroy () {
    this.__cleanup();
  }
});

var ValidateMixin = {
  props: {
    value: {},

    error: {
      type: Boolean,
      default: null
    },
    errorMessage: String,
    noErrorIcon: Boolean,

    rules: Array,
    lazyRules: Boolean
  },

  data () {
    return {
      isDirty: false,
      innerError: false,
      innerErrorMessage: void 0
    }
  },

  watch: {
    value (v) {
      if (this.rules === void 0) {
        return
      }
      if (this.lazyRules === true && this.isDirty === false) {
        return
      }

      this.validate(v);
    },

    focused (focused) {
      focused === false && this.__triggerValidation();
    }
  },

  computed: {
    hasError () {
      return this.error === true || this.innerError === true
    },

    computedErrorMessage () {
      return typeof this.errorMessage === 'string' && this.errorMessage.length > 0
        ? this.errorMessage
        : this.innerErrorMessage
    }
  },

  mounted () {
    this.validateIndex = 0;
    this.focused === void 0 && this.$el.addEventListener('focusout', this.__triggerValidation);
  },

  beforeDestroy () {
    this.focused === void 0 && this.$el.removeEventListener('focusout', this.__triggerValidation);
  },

  methods: {
    resetValidation () {
      this.validateIndex++;
      this.innerLoading = false;
      this.isDirty = false;
      this.innerError = false;
      this.innerErrorMessage = void 0;
    },

    /*
     * Return value
     *   - true (validation succeeded)
     *   - false (validation failed)
     *   - Promise (pending async validation)
     */
    validate (val = this.value) {
      if (!this.rules || this.rules.length === 0) {
        return true
      }

      this.validateIndex++;

      if (this.innerLoading !== true && this.lazyRules !== true) {
        this.isDirty = true;
      }

      const update = (err, msg) => {
        if (this.innerError !== err) {
          this.innerError = err;
        }

        const m = msg || void 0;
        if (this.innerErrorMessage !== m) {
          this.innerErrorMessage = m;
        }

        if (this.innerLoading !== false) {
          this.innerLoading = false;
        }
      };

      const promises = [];

      for (let i = 0; i < this.rules.length; i++) {
        const rule = this.rules[i];
        let res;

        if (typeof rule === 'function') {
          res = rule(val);
        }
        else if (typeof rule === 'string' && testPattern[rule] !== void 0) {
          res = testPattern[rule](val);
        }

        if (res === false || typeof res === 'string') {
          update(true, res);
          return false
        }
        else if (res !== true && res !== void 0) {
          promises.push(res);
        }
      }

      if (promises.length === 0) {
        update(false);
        return true
      }

      if (this.innerLoading !== true) {
        this.innerLoading = true;
      }

      const index = this.validateIndex;

      return Promise.all(promises).then(
        res => {
          if (index === this.validateIndex) {
            if (res === void 0 || Array.isArray(res) === false || res.length === 0) {
              update(false);
              return true
            }
            else {
              const msg = res.find(r => r === false || typeof r === 'string');
              update(msg !== void 0, msg);
              return msg === void 0
            }
          }
          return true
        },
        (e) => {
          if (index === this.validateIndex) {
            console.error(e);
            update(true);
            return false
          }
        }
      )
    },

    __triggerValidation () {
      if (this.isDirty === false && this.rules !== void 0) {
        this.isDirty = true;
        this.validate(this.value);
      }
    }
  }
};

var QField = Vue.extend({
  name: 'QField',

  inheritAttrs: false,

  mixins: [ ValidateMixin ],

  props: {
    label: String,
    stackLabel: Boolean,
    hint: String,
    hideHint: Boolean,
    prefix: String,
    suffix: String,

    color: String,
    bgColor: String,
    dark: Boolean,

    filled: Boolean,
    outlined: Boolean,
    borderless: Boolean,
    standout: [Boolean, String],

    square: Boolean,

    loading: Boolean,

    bottomSlots: Boolean,
    hideBottomSpace: Boolean,

    rounded: Boolean,
    dense: Boolean,
    itemAligned: Boolean,

    counter: Boolean,

    clearable: Boolean,
    clearIcon: String,

    disable: Boolean,
    readonly: Boolean,

    autofocus: Boolean,

    maxlength: [Number, String],
    maxValues: [Number, String] // do not add to JSON, internally needed by QSelect
  },

  data () {
    return {
      focused: false,

      // used internally by validation for QInput
      // or menu handling for QSelect
      innerLoading: false
    }
  },

  computed: {
    editable () {
      return this.disable !== true && this.readonly !== true
    },

    hasValue () {
      const value = this.__getControl === void 0 ? this.value : this.innerValue;

      return value !== void 0 &&
        value !== null &&
        ('' + value).length > 0
    },

    computedCounter () {
      if (this.counter !== false) {
        const len = typeof this.value === 'string' || typeof this.value === 'number'
          ? ('' + this.value).length
          : (Array.isArray(this.value) === true ? this.value.length : 0);
        const max = this.maxlength !== void 0 ? this.maxlength : this.maxValues;

        return len + (max !== void 0 ? ' / ' + max : '')
      }
    },

    floatingLabel () {
      return this.hasError === true ||
        this.stackLabel === true ||
        this.focused === true ||
        (
          this.inputValue !== void 0 && this.hideSelected === true
            ? this.inputValue.length > 0
            : this.hasValue === true
        ) ||
        (
          this.displayValue !== void 0 &&
          this.displayValue !== null &&
          ('' + this.displayValue).length > 0
        )
    },

    shouldRenderBottom () {
      return this.bottomSlots === true ||
        this.hint !== void 0 ||
        this.rules !== void 0 ||
        this.counter === true ||
        this.error !== null
    },

    classes () {
      return {
        [this.fieldClass]: this.fieldClass !== void 0,

        [`q-field--${this.styleType}`]: true,
        'q-field--rounded': this.rounded,
        'q-field--square': this.square,

        'q-field--focused': this.focused === true || this.hasError === true,
        'q-field--float': this.floatingLabel,
        'q-field--labeled': this.label !== void 0,

        'q-field--dense': this.dense,
        'q-field--item-aligned q-item-type': this.itemAligned,
        'q-field--dark': this.dark,

        'q-field--auto-height': this.__getControl === void 0,

        'q-field--with-bottom': this.hideBottomSpace !== true && this.shouldRenderBottom === true,
        'q-field--error': this.hasError,

        'q-field--readonly': this.readonly,
        'q-field--disabled': this.disable
      }
    },

    styleType () {
      if (this.filled === true) { return 'filled' }
      if (this.outlined === true) { return 'outlined' }
      if (this.borderless === true) { return 'borderless' }
      if (this.standout) { return 'standout' }
      return 'standard'
    },

    contentClass () {
      const cls = [];

      if (this.hasError === true) {
        cls.push('text-negative');
      }
      else if (typeof this.standout === 'string' && this.standout.length > 0 && this.focused === true) {
        return this.standout
      }
      else if (this.color !== void 0) {
        cls.push('text-' + this.color);
      }

      if (this.bgColor !== void 0) {
        cls.push(`bg-${this.bgColor}`);
      }

      return cls
    }
  },

  methods: {
    focus () {
      if (this.showPopup !== void 0 && this.$q.platform.is.desktop !== true) {
        this.showPopup();
        return
      }

      let target = this.$refs.target;
      if (target !== void 0) {
        target.matches('[tabindex]') || (target = target.querySelector('[tabindex]'));
        target !== null && target.focus();
      }
    },

    blur () {
      const el = document.activeElement;
      this.$el.contains(el) && el.blur();
    },

    __getContent (h) {
      const node = [];

      this.$scopedSlots.prepend !== void 0 && node.push(
        h('div', {
          staticClass: 'q-field__prepend q-field__marginal row no-wrap items-center',
          key: 'prepend'
        }, this.$scopedSlots.prepend())
      );

      node.push(
        h('div', {
          staticClass: 'q-field__control-container col relative-position row no-wrap q-anchor--skip'
        }, this.__getControlContainer(h))
      );

      this.hasError === true && this.noErrorIcon === false && node.push(
        this.__getInnerAppendNode(h, 'error', [
          h(QIcon, { props: { name: this.$q.iconSet.field.error, color: 'negative' } })
        ])
      );

      if (this.loading === true || this.innerLoading === true) {
        node.push(
          this.__getInnerAppendNode(
            h,
            'inner-loading-append',
            this.$scopedSlots.loading !== void 0
              ? this.$scopedSlots.loading()
              : [ h(QSpinner, { props: { color: this.color } }) ]
          )
        );
      }

      if (this.clearable === true && this.hasValue === true && this.editable === true) {
        node.push(
          this.__getInnerAppendNode(h, 'inner-clearable-append', [
            h(QIcon, {
              staticClass: 'cursor-pointer',
              props: { name: this.clearIcon || this.$q.iconSet.field.clear },
              on: {
                click: this.__clearValue
              }
            })
          ])
        );
      }

      this.$scopedSlots.append !== void 0 && node.push(
        h('div', {
          staticClass: 'q-field__append q-field__marginal row no-wrap items-center',
          key: 'append'
        }, this.$scopedSlots.append())
      );

      this.__getInnerAppend !== void 0 && node.push(
        this.__getInnerAppendNode(h, 'inner-append', this.__getInnerAppend(h))
      );

      this.__getPopup !== void 0 && node.push(
        this.__getPopup(h)
      );

      return node
    },

    __getControlContainer (h) {
      const node = [];

      this.prefix !== void 0 && this.prefix !== null && node.push(
        h('div', {
          staticClass: 'q-field__prefix no-pointer-events row items-center'
        }, [ this.prefix ])
      );

      if (this.__getControl !== void 0) {
        node.push(
          this.__getControl(h)
        );
      }
      // internal usage only:
      else if (this.$scopedSlots.rawControl !== void 0) {
        node.push(this.$scopedSlots.rawControl());
      }
      else if (this.$scopedSlots.control !== void 0) {
        node.push(
          h('div', {
            ref: 'target',
            staticClass: 'q-field__native row',
            attrs: {
              ...this.$attrs,
              autofocus: this.autofocus
            }
          }, this.$scopedSlots.control())
        );
      }

      this.label !== void 0 && node.push(
        h('div', {
          staticClass: 'q-field__label no-pointer-events absolute ellipsis'
        }, [ this.label ])
      );

      this.suffix !== void 0 && this.suffix !== null && node.push(
        h('div', {
          staticClass: 'q-field__suffix no-pointer-events row items-center'
        }, [ this.suffix ])
      );

      return node.concat(
        this.__getDefaultSlot !== void 0
          ? this.__getDefaultSlot(h)
          : slot(this, 'default')
      )
    },

    __getBottom (h) {
      let msg, key;

      if (this.hasError === true) {
        if (this.computedErrorMessage !== void 0) {
          msg = [ h('div', [ this.computedErrorMessage ]) ];
          key = this.computedErrorMessage;
        }
        else {
          msg = slot(this, 'error');
          key = 'q--slot-error';
        }
      }
      else if (this.hideHint !== true || this.focused === true) {
        if (this.hint !== void 0) {
          msg = [ h('div', [ this.hint ]) ];
          key = this.hint;
        }
        else {
          msg = slot(this, 'hint');
          key = 'q--slot-hint';
        }
      }

      const hasCounter = this.counter === true || this.$scopedSlots.counter !== void 0;

      if (this.hideBottomSpace === true && hasCounter === false && msg === void 0) {
        return
      }

      const main = h('div', {
        key,
        staticClass: 'q-field__messages col'
      }, msg);

      return h('div', {
        staticClass: 'q-field__bottom row items-start q-field__bottom--' +
          (this.hideBottomSpace !== true ? 'animated' : 'stale')
      }, [
        this.hideBottomSpace === true
          ? main
          : h('transition', { props: { name: 'q-transition--field-message' } }, [
            main
          ]),

        hasCounter === true
          ? h('div', {
            staticClass: 'q-field__counter'
          }, this.$scopedSlots.counter !== void 0 ? this.$scopedSlots.counter() : [ this.computedCounter ])
          : null
      ])
    },

    __getInnerAppendNode (h, key, content) {
      return h('div', {
        staticClass: 'q-field__append q-field__marginal row no-wrap items-center q-anchor--skip',
        key
      }, content)
    },

    __onControlPopupShow (e) {
      this.hasPopupOpen = true;
      this.__onControlFocusin(e);
    },

    __onControlPopupHide (e) {
      this.hasPopupOpen = false;
      this.__onControlFocusout(e);
    },

    __onControlFocusin (e) {
      if (this.editable === true && this.focused === false) {
        this.focused = true;
        this.$emit('focus', e);
      }
    },

    __onControlFocusout (e, then) {
      clearTimeout(this.focusoutTimer);
      this.focusoutTimer = setTimeout(() => {
        if (
          document.hasFocus() === true && (
            this.hasPopupOpen === true ||
            this.$refs === void 0 ||
            this.$refs.control === void 0 ||
            this.$refs.control.contains(document.activeElement) !== false
          )
        ) {
          return
        }

        if (this.focused === true) {
          this.focused = false;
          this.$emit('blur', e);
        }

        then !== void 0 && then();
      });
    },

    __clearValue (e) {
      stop(e);
      this.$emit('input', null);
    }
  },

  render (h) {
    this.__onPreRender !== void 0 && this.__onPreRender();
    this.__onPostRender !== void 0 && this.$nextTick(this.__onPostRender);

    return h('div', {
      staticClass: 'q-field row no-wrap items-start',
      class: this.classes
    }, [
      this.$scopedSlots.before !== void 0 ? h('div', {
        staticClass: 'q-field__before q-field__marginal row no-wrap items-center'
      }, this.$scopedSlots.before()) : null,

      h('div', {
        staticClass: 'q-field__inner relative-position col self-stretch column justify-center'
      }, [
        h('div', {
          ref: 'control',
          staticClass: 'q-field__control relative-position row no-wrap',
          class: this.contentClass,
          attrs: { tabindex: -1 },
          on: this.controlEvents
        }, this.__getContent(h)),

        this.shouldRenderBottom === true
          ? this.__getBottom(h)
          : null
      ]),

      this.$scopedSlots.after !== void 0 ? h('div', {
        staticClass: 'q-field__after q-field__marginal row no-wrap items-center'
      }, this.$scopedSlots.after()) : null
    ])
  },

  created () {
    this.__onPreRender !== void 0 && this.__onPreRender();

    this.controlEvents = this.__getControlEvents !== void 0
      ? this.__getControlEvents()
      : {
        focus: this.focus,
        focusin: this.__onControlFocusin,
        focusout: this.__onControlFocusout,
        'popup-show': this.__onControlPopupShow,
        'popup-hide': this.__onControlPopupHide
      };
  },

  mounted () {
    this.autofocus === true && setTimeout(this.focus);
  },

  beforeDestroy () {
    clearTimeout(this.focusoutTimer);
  }
});

const TOKENS = {
  '#': { pattern: '[\\d]', negate: '[^\\d]' },

  S: { pattern: '[a-zA-Z]', negate: '[^a-zA-Z]' },
  N: { pattern: '[0-9a-zA-Z]', negate: '[^0-9a-zA-Z]' },

  A: { pattern: '[a-zA-Z]', negate: '[^a-zA-Z]', transform: v => v.toLocaleUpperCase() },
  a: { pattern: '[a-zA-Z]', negate: '[^a-zA-Z]', transform: v => v.toLocaleLowerCase() },

  X: { pattern: '[0-9a-zA-Z]', negate: '[^0-9a-zA-Z]', transform: v => v.toLocaleUpperCase() },
  x: { pattern: '[0-9a-zA-Z]', negate: '[^0-9a-zA-Z]', transform: v => v.toLocaleLowerCase() }
};

const KEYS = Object.keys(TOKENS);
KEYS.forEach(key => {
  TOKENS[key].regex = new RegExp(TOKENS[key].pattern);
});

const
  tokenRegexMask = new RegExp('\\\\([^.*+?^${}()|([\\]])|([.*+?^${}()|[\\]])|([' + KEYS.join('') + '])|(.)', 'g'),
  escRegex = /[.*+?^${}()|[\]\\]/g;

const NAMED_MASKS = {
  date: '####/##/##',
  datetime: '####/##/## ##:##',
  time: '##:##',
  fulltime: '##:##:##',
  phone: '(###) ### - ####',
  card: '#### #### #### ####'
};

const MARKER = String.fromCharCode(1);

var MaskMixin = {
  props: {
    mask: String,
    reverseFillMask: Boolean,
    fillMask: [Boolean, String],
    unmaskedValue: Boolean
  },

  watch: {
    type () {
      this.__updateMaskInternals();
    },

    mask (v) {
      if (v !== void 0) {
        this.__updateMaskValue(this.innerValue, true);
      }
      else {
        const val = this.__unmask(this.innerValue);
        this.__updateMaskInternals();
        this.value !== val && this.$emit('input', val);
      }
    },

    fillMask () {
      this.hasMask === true && this.__updateMaskValue(this.innerValue, true);
    },

    reverseFillMask () {
      this.hasMask === true && this.__updateMaskValue(this.innerValue, true);
    },

    unmaskedValue () {
      this.hasMask === true && this.__updateMaskValue(this.innerValue);
    }
  },

  methods: {
    __getInitialMaskedValue () {
      this.__updateMaskInternals();

      if (this.hasMask === true) {
        const masked = this.__mask(this.__unmask(this.value));

        return this.fillMask !== false
          ? this.__fillWithMask(masked)
          : masked
      }

      return this.value
    },

    __getPaddedMaskMarked (size) {
      if (size < this.maskMarked.length) {
        return this.maskMarked.slice(-size)
      }

      let
        maskMarked = this.maskMarked,
        padPos = maskMarked.indexOf(MARKER),
        pad = '';

      if (padPos > -1) {
        for (let i = size - maskMarked.length; i > 0; i--) {
          pad += MARKER;
        }

        maskMarked = maskMarked.slice(0, padPos) + pad + maskMarked.slice(padPos);
      }

      return maskMarked
    },

    __updateMaskInternals () {
      this.hasMask = this.mask !== void 0 &&
        this.mask.length > 0 &&
        ['text', 'search', 'url', 'tel', 'password'].includes(this.type);

      if (this.hasMask === false) {
        this.computedUnmask = void 0;
        this.maskMarked = '';
        this.maskReplaced = '';
        return
      }

      const
        computedMask = NAMED_MASKS[this.mask] === void 0
          ? this.mask
          : NAMED_MASKS[this.mask],
        fillChar = typeof this.fillMask === 'string' && this.fillMask.length > 0
          ? this.fillMask.slice(0, 1)
          : '_',
        fillCharEscaped = fillChar.replace(escRegex, '\\$&'),
        unmask = [],
        extract = [],
        mask = [];

      let
        firstMatch = this.reverseFillMask === true,
        unmaskChar = '',
        negateChar = '';

      computedMask.replace(tokenRegexMask, (_, char1, esc, token, char2) => {
        if (token !== void 0) {
          const c = TOKENS[token];
          mask.push(c);
          negateChar = c.negate;
          if (firstMatch === true) {
            extract.push('(?:' + negateChar + '+?)?(' + c.pattern + '+)?(?:' + negateChar + '+?)?(' + c.pattern + '+)?');
            firstMatch = false;
          }
          extract.push('(?:' + negateChar + '+?)?(' + c.pattern + ')?');
        }
        else if (esc !== void 0) {
          unmaskChar = '\\' + esc;
          mask.push(esc);
          unmask.push('([^' + unmaskChar + ']+)?' + unmaskChar + '?');
        }
        else {
          const c = char1 !== void 0 ? char1 : char2;
          unmaskChar = c.replace(escRegex, '\\\\$&');
          mask.push(c);
          unmask.push('([^' + unmaskChar + ']+)?' + unmaskChar + '?');
        }
      });

      const
        unmaskMatcher = new RegExp(
          '^' +
          unmask.join('') +
          '(' + (unmaskChar === '' ? '.' : '[^' + unmaskChar + ']') + '+)?' +
          '$'
        ),
        extractMatcher = new RegExp(
          '^' +
          (this.reverseFillMask === true ? fillCharEscaped + '*' : '') +
          extract.join('') +
          '(' + (negateChar === '' ? '.' : negateChar) + '+)?' +
          (this.reverseFillMask === true ? '' : fillCharEscaped + '*') +
          '$'
        );

      this.computedMask = mask;
      this.computedUnmask = val => {
        const unmaskMatch = unmaskMatcher.exec(val);
        if (unmaskMatch !== null) {
          val = unmaskMatch.slice(1).join('');
        }

        const extractMatch = extractMatcher.exec(val);
        if (extractMatch !== null) {
          return extractMatch.slice(1).join('')
        }

        return val
      };
      this.maskMarked = mask.map(v => typeof v === 'string' ? v : MARKER).join('');
      this.maskReplaced = this.maskMarked.split(MARKER).join(fillChar);
    },

    __updateMaskValue (rawVal, updateMaskInternals) {
      const
        inp = this.$refs.input,
        oldCursor = this.reverseFillMask === true
          ? inp.value.length - inp.selectionEnd
          : inp.selectionEnd,
        unmasked = this.__unmask(rawVal);

      // Update here so unmask uses the original fillChar
      updateMaskInternals === true && this.__updateMaskInternals();

      const
        masked = this.fillMask !== false
          ? this.__fillWithMask(this.__mask(unmasked))
          : this.__mask(unmasked),
        changed = this.innerValue !== masked;

      // We want to avoid "flickering" so we set value immediately
      inp.value !== masked && (inp.value = masked);

      changed === true && (this.innerValue = masked);

      this.$nextTick(() => {
        if (this.reverseFillMask === true) {
          if (changed === true) {
            const cursor = Math.max(0, masked.length - (masked === this.maskReplaced ? 0 : oldCursor + 1));
            this.__moveCursorRightReverse(inp, cursor, cursor);
          }
          else {
            const cursor = masked.length - oldCursor;
            inp.setSelectionRange(cursor, cursor);
          }
        }
        else if (changed === true) {
          if (masked === this.maskReplaced) {
            this.__moveCursorLeft(inp, 0, 0);
          }
          else {
            const cursor = Math.max(0, this.maskMarked.indexOf(MARKER), oldCursor - 1);
            this.__moveCursorRight(inp, cursor, cursor);
          }
        }
        else {
          this.__moveCursorLeft(inp, oldCursor, oldCursor);
        }
      });

      const val = this.unmaskedValue === true
        ? this.__unmask(masked)
        : masked;

      this.value !== val && this.__emitValue(val, true);
    },

    __moveCursorLeft (inp, start, end, selection) {
      const noMarkBefore = this.maskMarked.slice(start - 1).indexOf(MARKER) === -1;
      let i = Math.max(0, start - 1);

      for (; i >= 0; i--) {
        if (this.maskMarked[i] === MARKER) {
          start = i;
          noMarkBefore === true && start++;
          break
        }
      }

      if (
        i < 0 &&
        this.maskMarked[start] !== void 0 &&
        this.maskMarked[start] !== MARKER
      ) {
        return this.__moveCursorRight(inp, 0, 0)
      }

      start >= 0 && inp.setSelectionRange(
        start,
        selection === true ? end : start, 'backward'
      );
    },

    __moveCursorRight (inp, start, end, selection) {
      const limit = inp.value.length;
      let i = Math.min(limit, end + 1);

      for (; i <= limit; i++) {
        if (this.maskMarked[i] === MARKER) {
          end = i;
          break
        }
        else if (this.maskMarked[i - 1] === MARKER) {
          end = i;
        }
      }

      if (
        i > limit &&
        this.maskMarked[end - 1] !== void 0 &&
        this.maskMarked[end - 1] !== MARKER
      ) {
        return this.__moveCursorLeft(inp, limit, limit)
      }

      inp.setSelectionRange(selection ? start : end, end, 'forward');
    },

    __moveCursorLeftReverse (inp, start, end, selection) {
      const
        maskMarked = this.__getPaddedMaskMarked(inp.value.length);
      let i = Math.max(0, start - 1);

      for (; i >= 0; i--) {
        if (maskMarked[i - 1] === MARKER) {
          start = i;
          break
        }
        else if (maskMarked[i] === MARKER) {
          start = i;
          if (i === 0) {
            break
          }
        }
      }

      if (
        i < 0 &&
        maskMarked[start] !== void 0 &&
        maskMarked[start] !== MARKER
      ) {
        return this.__moveCursorRightReverse(inp, 0, 0)
      }

      start >= 0 && inp.setSelectionRange(
        start,
        selection === true ? end : start, 'backward'
      );
    },

    __moveCursorRightReverse (inp, start, end, selection) {
      const
        limit = inp.value.length,
        maskMarked = this.__getPaddedMaskMarked(limit),
        noMarkBefore = maskMarked.slice(0, end + 1).indexOf(MARKER) === -1;
      let i = Math.min(limit, end + 1);

      for (; i <= limit; i++) {
        if (maskMarked[i - 1] === MARKER) {
          end = i;
          end > 0 && noMarkBefore === true && end--;
          break
        }
      }

      if (
        i > limit &&
        maskMarked[end - 1] !== void 0 &&
        maskMarked[end - 1] !== MARKER
      ) {
        return this.__moveCursorLeftReverse(inp, limit, limit)
      }

      inp.setSelectionRange(selection === true ? start : end, end, 'forward');
    },

    __onMaskedKeydown (e) {
      const
        inp = this.$refs.input,
        start = inp.selectionStart,
        end = inp.selectionEnd;

      if (e.keyCode === 37 || e.keyCode === 39) { // Left / Right
        const fn = this['__moveCursor' + (e.keyCode === 39 ? 'Right' : 'Left') + (this.reverseFillMask === true ? 'Reverse' : '')];

        e.preventDefault();
        fn(inp, start, end, e.shiftKey);
      }
      else if (
        e.keyCode === 8 && // Backspace
        this.reverseFillMask !== true &&
        start === end
      ) {
        this.__moveCursorLeft(inp, start, end, true);
      }
      else if (
        e.keyCode === 46 && // Delete
        this.reverseFillMask === true &&
        start === end
      ) {
        this.__moveCursorRightReverse(inp, start, end, true);
      }

      this.$emit('keydown', e);
    },

    __mask (val) {
      if (val === void 0 || val === null || val === '') { return '' }

      if (this.reverseFillMask === true) {
        return this.__maskReverse(val)
      }

      const mask = this.computedMask;

      let valIndex = 0, output = '';

      for (let maskIndex = 0; maskIndex < mask.length; maskIndex++) {
        const
          valChar = val[valIndex],
          maskDef = mask[maskIndex];

        if (typeof maskDef === 'string') {
          output += maskDef;
          valChar === maskDef && valIndex++;
        }
        else if (valChar !== void 0 && maskDef.regex.test(valChar)) {
          output += maskDef.transform !== void 0
            ? maskDef.transform(valChar)
            : valChar;
          valIndex++;
        }
        else {
          return output
        }
      }

      return output
    },

    __maskReverse (val) {
      const
        mask = this.computedMask,
        firstTokenIndex = this.maskMarked.indexOf(MARKER);

      let valIndex = val.length - 1, output = '';

      for (let maskIndex = mask.length - 1; maskIndex >= 0; maskIndex--) {
        const maskDef = mask[maskIndex];

        let valChar = val[valIndex];

        if (typeof maskDef === 'string') {
          output = maskDef + output;
          valChar === maskDef && valIndex--;
        }
        else if (valChar !== void 0 && maskDef.regex.test(valChar)) {
          do {
            output = (maskDef.transform !== void 0 ? maskDef.transform(valChar) : valChar) + output;
            valIndex--;
            valChar = val[valIndex];
          // eslint-disable-next-line no-unmodified-loop-condition
          } while (firstTokenIndex === maskIndex && valChar !== void 0 && maskDef.regex.test(valChar))
        }
        else {
          return output
        }
      }

      return output
    },

    __unmask (val) {
      return typeof val !== 'string' || this.computedUnmask === void 0
        ? val
        : this.computedUnmask(val)
    },

    __fillWithMask (val) {
      if (this.maskReplaced.length - val.length <= 0) {
        return val
      }

      return this.reverseFillMask === true && val.length > 0
        ? this.maskReplaced.slice(0, -val.length) + val
        : val + this.maskReplaced.slice(val.length)
    }
  }
};

var QInput = Vue.extend({
  name: 'QInput',

  mixins: [ QField, MaskMixin ],

  props: {
    value: [String, Number],

    type: {
      type: String,
      default: 'text'
    },

    debounce: [String, Number],

    maxlength: [Number, String],
    autogrow: Boolean, // makes a textarea

    inputClass: [Array, String, Object],
    inputStyle: [Array, String, Object]
  },

  watch: {
    value (v) {
      if (this.hasMask === true) {
        if (this.stopValueWatcher === true) {
          this.stopValueWatcher = false;
          return
        }

        this.__updateMaskValue(v);
      }
      else if (this.innerValue !== v) {
        this.innerValue = v;
      }

      // textarea only
      this.autogrow === true && this.$nextTick(this.__adjustHeightDebounce);
    },

    autogrow (autogrow) {
      // textarea only
      if (autogrow === true) {
        this.$nextTick(this.__adjustHeightDebounce);
      }
      // if it has a number of rows set respect it
      else if (this.$attrs.rows > 0) {
        const inp = this.$refs.input;
        inp.style.height = 'auto';
      }
    }
  },

  data () {
    return { innerValue: this.__getInitialMaskedValue() }
  },

  computed: {
    isTextarea () {
      return this.type === 'textarea' || this.autogrow === true
    },

    fieldClass () {
      return `q-${this.isTextarea === true ? 'textarea' : 'input'}` +
        (this.autogrow === true ? ' q-textarea--autogrow' : '')
    }
  },

  methods: {
    focus () {
      this.$refs.input.focus();
    },

    __onInput (e) {
      if (this.type === 'file') {
        this.$emit('input', e.target.files);
        return
      }

      const val = e.target.value;

      if (this.hasMask === true) {
        this.__updateMaskValue(val);
      }
      else {
        this.__emitValue(val);
      }

      // we need to trigger it immediately too,
      // to avoid "flickering"
      this.autogrow === true && this.__adjustHeight();
    },

    __emitValue (val, stopWatcher) {
      const fn = () => {
        if (this.hasOwnProperty('tempValue') === true) {
          delete this.tempValue;
        }
        if (this.value !== val) {
          stopWatcher === true && (this.stopValueWatcher = true);
          this.$emit('input', val);
        }
      };

      if (this.debounce !== void 0) {
        clearTimeout(this.emitTimer);
        this.tempValue = val;
        this.emitTimer = setTimeout(fn, this.debounce);
      }
      else {
        fn();
      }
    },

    // textarea only
    __adjustHeight () {
      const inp = this.$refs.input;
      inp.style.height = '1px';
      inp.style.height = inp.scrollHeight + 'px';
    },

    __getControl (h) {
      const on = {
        ...this.$listeners,
        input: this.__onInput,
        focus: stop,
        blur: stop
      };

      if (this.hasMask === true) {
        on.keydown = this.__onMaskedKeydown;
      }

      const attrs = {
        tabindex: 0,
        autofocus: this.autofocus,
        rows: this.type === 'textarea' ? 6 : void 0,
        ...this.$attrs,
        'aria-label': this.label,
        type: this.type,
        maxlength: this.maxlength,
        disabled: this.disable,
        readonly: this.readonly
      };

      if (this.autogrow === true) {
        attrs.rows = 1;
      }

      return h(this.isTextarea ? 'textarea' : 'input', {
        ref: 'input',
        staticClass: 'q-field__native q-placeholder',
        style: this.inputStyle,
        class: this.inputClass,
        attrs,
        on,
        domProps: this.type !== 'file'
          ? {
            value: this.hasOwnProperty('tempValue') === true
              ? this.tempValue
              : this.innerValue
          }
          : null
      })
    }
  },

  created () {
    // textarea only
    this.__adjustHeightDebounce = debounce(this.__adjustHeight, 100);
  },

  mounted () {
    // textarea only
    this.autogrow === true && this.__adjustHeight();
  },

  beforeDestroy () {
    clearTimeout(this.emitTimer);
  }
});

var QTooltip = Vue.extend({
  name: 'QTooltip',

  mixins: [ AnchorMixin, ModelToggleMixin, PortalMixin, TransitionMixin ],

  props: {
    maxHeight: {
      type: String,
      default: null
    },
    maxWidth: {
      type: String,
      default: null
    },

    transitionShow: {
      default: 'jump-down'
    },
    transitionHide: {
      default: 'jump-up'
    },

    anchor: {
      type: String,
      default: 'bottom middle',
      validator: validatePosition
    },
    self: {
      type: String,
      default: 'top middle',
      validator: validatePosition
    },
    offset: {
      type: Array,
      default: () => [14, 14],
      validator: validateOffset
    },

    delay: {
      type: Number,
      default: 0
    }
  },

  watch: {
    $route () {
      this.hide();
    }
  },

  computed: {
    anchorOrigin () {
      return parsePosition(this.anchor)
    },

    selfOrigin () {
      return parsePosition(this.self)
    }
  },

  methods: {
    __show (evt) {
      clearTimeout(this.timer);

      this.scrollTarget = getScrollTarget(this.anchorEl);
      this.scrollTarget.addEventListener('scroll', this.hide, listenOpts.passive);
      if (this.scrollTarget !== window) {
        window.addEventListener('scroll', this.updatePosition, listenOpts.passive);
      }

      this.__showPortal();

      this.timer = setTimeout(() => {
        this.updatePosition();

        this.timer = setTimeout(() => {
          this.$emit('show', evt);
        }, 300);
      }, 0);
    },

    __hide (evt) {
      this.__anchorCleanup();

      this.timer = setTimeout(() => {
        this.__hidePortal();
        this.$emit('hide', evt);
      }, 300);
    },

    __anchorCleanup () {
      clearTimeout(this.timer);

      if (this.scrollTarget) {
        this.scrollTarget.removeEventListener('scroll', this.updatePosition, listenOpts.passive);
        if (this.scrollTarget !== window) {
          window.removeEventListener('scroll', this.updatePosition, listenOpts.passive);
        }
      }
    },

    updatePosition () {
      const el = this.__portal.$el;

      if (el.nodeType === 8) { // IE replaces the comment with delay
        setTimeout(() => {
          this.__portal !== void 0 && this.__portal.showing === true && this.updatePosition();
        }, 25);
        return
      }

      setPosition({
        el,
        offset: this.offset,
        anchorEl: this.anchorEl,
        anchorOrigin: this.anchorOrigin,
        selfOrigin: this.selfOrigin,
        maxHeight: this.maxHeight,
        maxWidth: this.maxWidth
      });
    },

    __delayShow (evt) {
      clearTimeout(this.timer);
      this.$q.platform.is.mobile === true && document.body.classList.add('non-selectable');
      this.timer = setTimeout(() => {
        this.show(evt);
      }, this.delay);
    },

    __delayHide (evt) {
      clearTimeout(this.timer);
      this.$q.platform.is.mobile === true && document.body.classList.remove('non-selectable');
      this.hide(evt);
    },

    __unconfigureAnchorEl () {
      // mobile hover ref https://stackoverflow.com/a/22444532
      if (this.$q.platform.is.mobile) {
        this.anchorEl.removeEventListener('touchstart', this.__delayShow)
        ;['touchcancel', 'touchmove', 'click'].forEach(evt => {
          this.anchorEl.removeEventListener(evt, this.__delayHide);
        });
      }
      else {
        this.anchorEl.removeEventListener('mouseenter', this.__delayShow);
      }

      if (this.$q.platform.is.ios !== true) {
        this.anchorEl.removeEventListener('mouseleave', this.__delayHide);
      }
    },

    __configureAnchorEl () {
      // mobile hover ref https://stackoverflow.com/a/22444532
      if (this.$q.platform.is.mobile) {
        this.anchorEl.addEventListener('touchstart', this.__delayShow)
        ;['touchcancel', 'touchmove', 'click'].forEach(evt => {
          this.anchorEl.addEventListener(evt, this.__delayHide);
        });
      }
      else {
        this.anchorEl.addEventListener('mouseenter', this.__delayShow);
      }

      if (this.$q.platform.is.ios !== true) {
        this.anchorEl.addEventListener('mouseleave', this.__delayHide);
      }
    },

    __render (h) {
      return h('transition', {
        props: { name: this.transition }
      }, [
        this.showing === true ? h('div', {
          staticClass: 'q-tooltip no-pointer-events',
          class: this.contentClass,
          style: this.contentStyle
        }, slot(this, 'default')) : null
      ])
    }
  }
});

var QList = Vue.extend({
  name: 'QList',

  props: {
    bordered: Boolean,
    dense: Boolean,
    separator: Boolean,
    dark: Boolean,
    padding: Boolean
  },

  computed: {
    classes () {
      return {
        'q-list--bordered': this.bordered,
        'q-list--dense': this.dense,
        'q-list--separator': this.separator,
        'q-list--dark': this.dark,
        'q-list--padding': this.padding
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-list',
      class: this.classes,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

var QItem = Vue.extend({
  name: 'QItem',

  mixins: [ RouterLinkMixin ],

  props: {
    active: Boolean,
    dark: Boolean,

    clickable: Boolean,
    dense: Boolean,
    insetLevel: Number,

    tabindex: [String, Number],
    tag: {
      type: String,
      default: 'div'
    },

    focused: Boolean,
    manualFocus: Boolean
  },

  computed: {
    isClickable () {
      return this.disable !== true && (
        this.clickable === true ||
        this.hasRouterLink === true ||
        this.tag === 'a' ||
        this.tag === 'label'
      )
    },

    classes () {
      return {
        'q-item--clickable q-link cursor-pointer': this.isClickable,
        'q-focusable q-hoverable': this.isClickable === true && this.manualFocus === false,

        'q-manual-focusable': this.isClickable === true && this.manualFocus === true,
        'q-manual-focusable--focused': this.isClickable === true && this.focused === true,

        'q-item--dense': this.dense,
        'q-item--dark': this.dark,
        'q-item--active': this.active,
        [this.activeClass]: this.active === true && this.hasRouterLink !== true && this.activeClass !== void 0,

        'disabled': this.disable
      }
    },

    style () {
      if (this.insetLevel !== void 0) {
        return {
          paddingLeft: (16 + this.insetLevel * 56) + 'px'
        }
      }
    }
  },

  methods: {
    __getContent (h) {
      const child = [].concat(slot(this, 'default'));
      this.isClickable === true && child.unshift(h('div', { staticClass: 'q-focus-helper', attrs: { tabindex: -1 }, ref: 'blurTarget' }));
      return child
    },

    __onClick (e) {
      if (this.isClickable === true) {
        if (e.qKeyEvent !== true && this.$refs.blurTarget !== void 0) {
          this.$refs.blurTarget.focus();
        }

        this.$emit('click', e);
      }
    },

    __onKeyup (e) {
      if (e.keyCode === 13 && this.isClickable === true) {
        stopAndPrevent(e);

        // for ripple
        e.qKeyEvent = true;

        // for click trigger
        const evt = new MouseEvent('click', e);
        evt.qKeyEvent = true;
        this.$el.dispatchEvent(evt);
      }

      this.$emit('keyup', e);
    }
  },

  render (h) {
    const data = {
      staticClass: 'q-item q-item-type row no-wrap',
      class: this.classes,
      style: this.style
    };

    const evtProp = this.hasRouterLink === true ? 'nativeOn' : 'on';
    data[evtProp] = {
      ...this.$listeners,
      click: this.__onClick,
      keyup: this.__onKeyup
    };

    if (this.isClickable === true) {
      data.attrs = {
        tabindex: this.tabindex || '0'
      };
    }

    if (this.hasRouterLink === true) {
      data.tag = 'a';
      data.props = this.routerLinkProps;

      return h('router-link', data, this.__getContent(h))
    }

    return h(
      this.tag,
      data,
      this.__getContent(h)
    )
  }
});

var QItemSection = Vue.extend({
  name: 'QItemSection',

  props: {
    avatar: Boolean,
    thumbnail: Boolean,
    side: Boolean,
    top: Boolean,
    noWrap: Boolean
  },

  computed: {
    classes () {
      const side = this.avatar || this.side || this.thumbnail;

      return {
        'q-item__section--top': this.top,
        'q-item__section--avatar': this.avatar,
        'q-item__section--thumbnail': this.thumbnail,
        'q-item__section--side': side,
        'q-item__section--nowrap': this.noWrap,
        'q-item__section--main': !side,
        [`justify-${this.top ? 'start' : 'center'}`]: true
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-item__section column',
      class: this.classes,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

function run (e, btn, vm) {
  if (btn.handler) {
    btn.handler(e, vm, vm.caret);
  }
  else {
    vm.runCmd(btn.cmd, btn.param);
  }
}

function __getGroup (h, children) {
  return h('div', {
    staticClass: 'q-editor__toolbar-group'
  }, children)
}

function getBtn (h, vm, btn, clickHandler, active = false) {
  const
    toggled = active || (btn.type === 'toggle'
      ? (btn.toggled ? btn.toggled(vm) : btn.cmd && vm.caret.is(btn.cmd, btn.param))
      : false),
    child = [],
    events = {
      click (e) {
        clickHandler && clickHandler();
        run(e, btn, vm);
      }
    };

  if (btn.tip && vm.$q.platform.is.desktop) {
    const Key = btn.key
      ? h('div', [h('small', `(CTRL + ${String.fromCharCode(btn.key)})`)])
      : null;
    child.push(
      h(QTooltip, { props: { delay: 1000 } }, [
        h('div', { domProps: { innerHTML: btn.tip } }),
        Key
      ])
    );
  }

  return h(QBtn, {
    props: {
      ...vm.buttonProps,
      icon: btn.icon,
      color: toggled ? btn.toggleColor || vm.toolbarToggleColor : btn.color || vm.toolbarColor,
      textColor: toggled && !vm.toolbarPush ? null : btn.textColor || vm.toolbarTextColor,
      label: btn.label,
      disable: btn.disable ? (typeof btn.disable === 'function' ? btn.disable(vm) : true) : false,
      size: 'sm'
    },
    on: events
  }, child)
}

function getDropdown (h, vm, btn) {
  let
    label = btn.label,
    icon = btn.icon,
    onlyIcons = btn.list === 'only-icons',
    contentClass,
    Items;

  function closeDropdown () {
    Dropdown.componentInstance.hide();
  }

  if (onlyIcons) {
    Items = btn.options.map(btn => {
      const active = btn.type === void 0
        ? vm.caret.is(btn.cmd, btn.param)
        : false;

      if (active) {
        label = btn.tip;
        icon = btn.icon;
      }
      return getBtn(h, vm, btn, closeDropdown, active)
    });
    contentClass = vm.toolbarBackgroundClass;
    Items = [
      __getGroup(h, Items)
    ];
  }
  else {
    const activeClass = vm.toolbarToggleColor !== void 0
      ? `text-${vm.toolbarToggleColor}`
      : null;
    const inactiveClass = vm.toolbarTextColor !== void 0
      ? `text-${vm.toolbarTextColor}`
      : null;

    Items = btn.options.map(btn => {
      const disable = btn.disable ? btn.disable(vm) : false;
      const active = btn.type === void 0
        ? vm.caret.is(btn.cmd, btn.param)
        : false;

      if (active) {
        label = btn.tip;
        icon = btn.icon;
      }

      const htmlTip = btn.htmlTip;

      return h(
        QItem,
        {
          props: { active, activeClass, clickable: true, disable: disable, dense: true },
          on: {
            click (e) {
              closeDropdown();
              vm.$refs.content && vm.$refs.content.focus();
              vm.caret.restore();
              run(e, btn, vm);
            }
          }
        },
        [
          btn.list === 'no-icons'
            ? null
            : h(QItemSection, {
              class: active ? activeClass : inactiveClass,
              props: { side: true }
            }, [
              h(QIcon, { props: { name: btn.icon } })
            ]),

          h(QItemSection, [
            htmlTip
              ? h('div', {
                domProps: { innerHTML: btn.htmlTip }
              })
              : (btn.tip ? h('div', [ btn.tip ]) : null)
          ])
        ]
      )
    });
    contentClass = [vm.toolbarBackgroundClass, inactiveClass];
    Items = [
      h(QList, [ Items ])
    ];
  }

  const highlight = btn.highlight && label !== btn.label;
  const Dropdown = h(
    QBtnDropdown,
    {
      props: {
        ...vm.buttonProps,
        noCaps: true,
        noWrap: true,
        color: highlight ? vm.toolbarToggleColor : vm.toolbarColor,
        textColor: highlight && !vm.toolbarPush ? null : vm.toolbarTextColor,
        label: btn.fixedLabel ? btn.label : label,
        icon: btn.fixedIcon ? btn.icon : icon,
        contentClass
      }
    },
    Items
  );
  return Dropdown
}

function getToolbar (h, vm) {
  if (vm.caret) {
    return vm.buttons.map(group => __getGroup(
      h,
      group.map(btn => {
        if (btn.type === 'slot') {
          return slot(vm, btn.slot)
        }

        if (btn.type === 'dropdown') {
          return getDropdown(h, vm, btn)
        }

        return getBtn(h, vm, btn)
      })
    ))
  }
}

function getFonts (defaultFont, defaultFontLabel, defaultFontIcon, fonts = {}) {
  const aliases = Object.keys(fonts);
  if (aliases.length === 0) {
    return {}
  }

  const def = {
    default_font: {
      cmd: 'fontName',
      param: defaultFont,
      icon: defaultFontIcon,
      tip: defaultFontLabel
    }
  };

  aliases.forEach(alias => {
    const name = fonts[alias];
    def[alias] = {
      cmd: 'fontName',
      param: name,
      icon: defaultFontIcon,
      tip: name,
      htmlTip: `<font face="${name}">${name}</font>`
    };
  });

  return def
}

function getLinkEditor (h, vm) {
  if (vm.caret) {
    const color = vm.toolbarColor || vm.toolbarTextColor;
    let link = vm.editLinkUrl;
    const updateLink = () => {
      vm.caret.restore();
      if (link !== vm.editLinkUrl) {
        document.execCommand('createLink', false, link === '' ? ' ' : link);
      }
      vm.editLinkUrl = null;
    };

    return [
      h('div', { staticClass: 'q-mx-xs', 'class': `text-${color}` }, [`${vm.$q.lang.editor.url}: `]),
      h(QInput, {
        key: 'qedt_btm_input',
        staticClass: 'q-ma-none q-pa-none col q-editor-input',
        props: {
          value: link,
          color,
          autofocus: true,
          borderless: true,
          dense: true
        },
        on: {
          input: val => { link = val; },
          keydown: event => {
            switch (event.keyCode) {
              case 13: // ENTER key
                prevent(event);
                return updateLink()
              case 27: // ESCAPE key
                prevent(event);
                vm.caret.restore();
                !vm.editLinkUrl && document.execCommand('unlink');
                vm.editLinkUrl = null;
                break
            }
          }
        }
      }),
      __getGroup(h, [
        h(QBtn, {
          key: 'qedt_btm_rem',
          attrs: { tabindex: -1 },
          props: {
            ...vm.buttonProps,
            label: vm.$q.lang.label.remove,
            noCaps: true
          },
          on: {
            click: () => {
              vm.caret.restore();
              document.execCommand('unlink');
              vm.editLinkUrl = null;
            }
          }
        }),
        h(QBtn, {
          key: 'qedt_btm_upd',
          props: {
            ...vm.buttonProps,
            label: vm.$q.lang.label.update,
            noCaps: true
          },
          on: {
            click: updateLink
          }
        })
      ])
    ]
  }
}

function getBlockElement (el, parent) {
  if (parent && el === parent) {
    return null
  }

  const
    style = window.getComputedStyle
      ? window.getComputedStyle(el)
      : el.currentStyle,
    display = style.display;

  if (display === 'block' || display === 'table') {
    return el
  }

  return getBlockElement(el.parentNode)
}

function isChildOf (el, parent) {
  if (!el) {
    return false
  }
  while ((el = el.parentNode)) {
    if (el === document.body) {
      return false
    }
    if (el === parent) {
      return true
    }
  }
  return false
}

const urlRegex = /^https?:\/\//;

class Caret {
  constructor (el, vm) {
    this.el = el;
    this.vm = vm;
  }

  get selection () {
    if (!this.el) {
      return
    }
    const sel = document.getSelection();
    // only when the selection in element
    if (isChildOf(sel.anchorNode, this.el) && isChildOf(sel.focusNode, this.el)) {
      return sel
    }
  }

  get hasSelection () {
    return this.selection
      ? this.selection.toString().length > 0
      : null
  }

  get range () {
    const sel = this.selection;

    if (!sel) {
      return
    }

    return sel.rangeCount
      ? sel.getRangeAt(0)
      : null
  }

  get parent () {
    const range = this.range;
    if (!range) {
      return
    }

    const node = range.startContainer;
    return node.nodeType === document.ELEMENT_NODE
      ? node
      : node.parentNode
  }

  get blockParent () {
    const parent = this.parent;
    if (!parent) {
      return
    }
    return getBlockElement(parent, this.el)
  }

  save (range = this.range) {
    this._range = range;
  }

  restore (range = this._range) {
    const
      r = document.createRange(),
      sel = document.getSelection();

    if (range) {
      r.setStart(range.startContainer, range.startOffset);
      r.setEnd(range.endContainer, range.endOffset);
      sel.removeAllRanges();
      sel.addRange(r);
    }
    else {
      sel.selectAllChildren(this.el);
      sel.collapseToEnd();
    }
  }

  hasParent (name, spanLevel) {
    const el = spanLevel
      ? this.parent
      : this.blockParent;

    return el
      ? el.nodeName.toLowerCase() === name.toLowerCase()
      : false
  }

  hasParents (list) {
    const el = this.parent;
    return el
      ? list.includes(el.nodeName.toLowerCase())
      : false
  }

  is (cmd, param) {
    switch (cmd) {
      case 'formatBlock':
        if (param === 'DIV' && this.parent === this.el) {
          return true
        }
        return this.hasParent(param, param === 'PRE')
      case 'link':
        return this.hasParent('A', true)
      case 'fontSize':
        return document.queryCommandValue(cmd) === param
      case 'fontName':
        const res = document.queryCommandValue(cmd);
        return res === `"${param}"` || res === param
      case 'fullscreen':
        return this.vm.inFullscreen
      case void 0:
        return false
      default:
        const state = document.queryCommandState(cmd);
        return param ? state === param : state
    }
  }

  getParentAttribute (attrib) {
    if (this.parent) {
      return this.parent.getAttribute(attrib)
    }
  }

  can (name) {
    if (name === 'outdent') {
      return this.hasParents(['blockquote', 'li'])
    }
    if (name === 'indent') {
      const parentName = this.parent ? this.parent.nodeName.toLowerCase() : false;
      if (parentName === 'blockquote') {
        return false
      }
      if (parentName === 'li') {
        const previousEl = this.parent.previousSibling;
        return previousEl && previousEl.nodeName.toLowerCase() === 'li'
      }
      return false
    }
  }

  apply (cmd, param, done = () => {}) {
    if (cmd === 'formatBlock') {
      if (['BLOCKQUOTE', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(param) && this.is(cmd, param)) {
        cmd = 'outdent';
        param = null;
      }

      if (param === 'PRE' && this.is(cmd, 'PRE')) {
        param = 'P';
      }
    }
    else if (cmd === 'print') {
      done();
      const win = window.open();
      win.document.write(`
        <!doctype html>
        <html>
          <head>
            <title>Print - ${document.title}</title>
          </head>
          <body>
            <div>${this.el.innerHTML}</div>
          </body>
        </html>
      `);
      win.print();
      win.close();
      return
    }
    else if (cmd === 'link') {
      const link = this.getParentAttribute('href');
      if (!link) {
        const selection = this.selectWord(this.selection);
        const url = selection ? selection.toString() : '';
        if (!url.length) {
          return
        }
        this.vm.editLinkUrl = urlRegex.test(url) ? url : '';
        document.execCommand('createLink', false, this.vm.editLinkUrl === '' ? ' ' : this.vm.editLinkUrl);
      }
      else {
        this.vm.editLinkUrl = link;
      }
      this.range.selectNodeContents(this.parent);
      this.save();
      return
    }
    else if (cmd === 'fullscreen') {
      this.vm.toggleFullscreen();
      done();
      return
    }

    if (this.vm.$q.platform.is.ie === true || this.vm.$q.platform.is.edge === true) {
      // workaround for IE/Edge, otherwise it messes up
      // the DOM of toolbar
      const dummyDiv = document.createElement('div');
      this.vm.$refs.content.appendChild(dummyDiv);
      document.execCommand(cmd, false, param);
      dummyDiv.remove();
    }
    else {
      document.execCommand(cmd, false, param);
    }

    done();
  }

  selectWord (sel) {
    if (!sel.isCollapsed) {
      return sel
    }

    // Detect if selection is backwards
    const range = document.createRange();
    range.setStart(sel.anchorNode, sel.anchorOffset);
    range.setEnd(sel.focusNode, sel.focusOffset);
    const direction = range.collapsed ? ['backward', 'forward'] : ['forward', 'backward'];
    range.detach();

    // modify() works on the focus of the selection
    const
      endNode = sel.focusNode,
      endOffset = sel.focusOffset;
    sel.collapse(sel.anchorNode, sel.anchorOffset);
    sel.modify('move', direction[0], 'character');
    sel.modify('move', direction[1], 'word');
    sel.extend(endNode, endOffset);
    sel.modify('extend', direction[1], 'character');
    sel.modify('extend', direction[0], 'word');

    return sel
  }
}

let
  toString$1 = Object.prototype.toString,
  hasOwn$1 = Object.prototype.hasOwnProperty,
  class2type = {};

'Boolean Number String Function Array Date RegExp Object'.split(' ').forEach(name => {
  class2type['[object ' + name + ']'] = name.toLowerCase();
});

function type (obj) {
  return obj === null ? String(obj) : class2type[toString$1.call(obj)] || 'object'
}

function isPlainObject$1 (obj) {
  if (!obj || type(obj) !== 'object') {
    return false
  }

  if (obj.constructor &&
    !hasOwn$1.call(obj, 'constructor') &&
    !hasOwn$1.call(obj.constructor.prototype, 'isPrototypeOf')) {
    return false
  }

  let key;
  for (key in obj) {}

  return key === undefined || hasOwn$1.call(obj, key)
}

function extend$1 () {
  let
    options, name, src, copy, copyIsArray, clone,
    target = arguments[0] || {},
    i = 1,
    length = arguments.length,
    deep = false;

  if (typeof target === 'boolean') {
    deep = target;
    target = arguments[1] || {};
    i = 2;
  }

  if (Object(target) !== target && type(target) !== 'function') {
    target = {};
  }

  if (length === i) {
    target = this;
    i--;
  }

  for (; i < length; i++) {
    if ((options = arguments[i]) !== null) {
      for (name in options) {
        src = target[name];
        copy = options[name];

        if (target === copy) {
          continue
        }

        if (deep && copy && (isPlainObject$1(copy) || (copyIsArray = type(copy) === 'array'))) {
          if (copyIsArray) {
            copyIsArray = false;
            clone = src && type(src) === 'array' ? src : [];
          }
          else {
            clone = src && isPlainObject$1(src) ? src : {};
          }

          target[name] = extend$1(deep, clone, copy);
        }
        else if (copy !== undefined) {
          target[name] = copy;
        }
      }
    }
  }

  return target
}

Vue.extend({
  name: 'QEditor',

  mixins: [ FullscreenMixin ],

  props: {
    value: {
      type: String,
      required: true
    },
    readonly: Boolean,
    disable: Boolean,
    minHeight: {
      type: String,
      default: '10rem'
    },
    maxHeight: String,
    height: String,
    definitions: Object,
    fonts: Object,

    toolbar: {
      type: Array,
      validator: v => v.length === 0 || v.every(group => group.length),
      default () {
        return [
          ['left', 'center', 'right', 'justify'],
          ['bold', 'italic', 'underline', 'strike'],
          ['undo', 'redo']
        ]
      }
    },
    toolbarColor: String,
    toolbarBg: String,
    toolbarTextColor: String,
    toolbarToggleColor: {
      type: String,
      default: 'primary'
    },
    toolbarOutline: Boolean,
    toolbarPush: Boolean,
    toolbarRounded: Boolean,

    contentStyle: Object,
    contentClass: [Object, Array, String],

    square: Boolean,
    flat: Boolean,
    dense: Boolean
  },

  computed: {
    editable () {
      return !this.readonly && !this.disable
    },

    hasToolbar () {
      return this.toolbar && this.toolbar.length > 0
    },

    toolbarBackgroundClass () {
      if (this.toolbarBg) {
        return `bg-${this.toolbarBg}`
      }
    },

    buttonProps () {
      const flat = this.toolbarOutline !== true &&
        this.toolbarPush !== true;

      return {
        flat,
        noWrap: true,
        outline: this.toolbarOutline,
        push: this.toolbarPush,
        rounded: this.toolbarRounded,
        dense: true,
        color: this.toolbarColor,
        disable: !this.editable,
        size: 'sm'
      }
    },

    buttonDef () {
      const
        e = this.$q.lang.editor,
        i = this.$q.iconSet.editor;

      return {
        bold: { cmd: 'bold', icon: i.bold, tip: e.bold, key: 66 },
        italic: { cmd: 'italic', icon: i.italic, tip: e.italic, key: 73 },
        strike: { cmd: 'strikeThrough', icon: i.strikethrough, tip: e.strikethrough, key: 83 },
        underline: { cmd: 'underline', icon: i.underline, tip: e.underline, key: 85 },
        unordered: { cmd: 'insertUnorderedList', icon: i.unorderedList, tip: e.unorderedList },
        ordered: { cmd: 'insertOrderedList', icon: i.orderedList, tip: e.orderedList },
        subscript: { cmd: 'subscript', icon: i.subscript, tip: e.subscript, htmlTip: 'x<subscript>2</subscript>' },
        superscript: { cmd: 'superscript', icon: i.superscript, tip: e.superscript, htmlTip: 'x<superscript>2</superscript>' },
        link: { cmd: 'link', icon: i.hyperlink, tip: e.hyperlink, key: 76 },
        fullscreen: { cmd: 'fullscreen', icon: i.toggleFullscreen, tip: e.toggleFullscreen, key: 70 },

        quote: { cmd: 'formatBlock', param: 'BLOCKQUOTE', icon: i.quote, tip: e.quote, key: 81 },
        left: { cmd: 'justifyLeft', icon: i.left, tip: e.left },
        center: { cmd: 'justifyCenter', icon: i.center, tip: e.center },
        right: { cmd: 'justifyRight', icon: i.right, tip: e.right },
        justify: { cmd: 'justifyFull', icon: i.justify, tip: e.justify },

        print: { type: 'no-state', cmd: 'print', icon: i.print, tip: e.print, key: 80 },
        outdent: { type: 'no-state', disable: vm => vm.caret && !vm.caret.can('outdent'), cmd: 'outdent', icon: i.outdent, tip: e.outdent },
        indent: { type: 'no-state', disable: vm => vm.caret && !vm.caret.can('indent'), cmd: 'indent', icon: i.indent, tip: e.indent },
        removeFormat: { type: 'no-state', cmd: 'removeFormat', icon: i.removeFormat, tip: e.removeFormat },
        hr: { type: 'no-state', cmd: 'insertHorizontalRule', icon: i.hr, tip: e.hr },
        undo: { type: 'no-state', cmd: 'undo', icon: i.undo, tip: e.undo, key: 90 },
        redo: { type: 'no-state', cmd: 'redo', icon: i.redo, tip: e.redo, key: 89 },

        h1: { cmd: 'formatBlock', param: 'H1', icon: i.header, tip: e.header1, htmlTip: `<h1 class="q-ma-none">${e.header1}</h1>` },
        h2: { cmd: 'formatBlock', param: 'H2', icon: i.header, tip: e.header2, htmlTip: `<h2 class="q-ma-none">${e.header2}</h2>` },
        h3: { cmd: 'formatBlock', param: 'H3', icon: i.header, tip: e.header3, htmlTip: `<h3 class="q-ma-none">${e.header3}</h3>` },
        h4: { cmd: 'formatBlock', param: 'H4', icon: i.header, tip: e.header4, htmlTip: `<h4 class="q-ma-none">${e.header4}</h4>` },
        h5: { cmd: 'formatBlock', param: 'H5', icon: i.header, tip: e.header5, htmlTip: `<h5 class="q-ma-none">${e.header5}</h5>` },
        h6: { cmd: 'formatBlock', param: 'H6', icon: i.header, tip: e.header6, htmlTip: `<h6 class="q-ma-none">${e.header6}</h6>` },
        p: { cmd: 'formatBlock', param: 'DIV', icon: i.header, tip: e.paragraph },
        code: { cmd: 'formatBlock', param: 'PRE', icon: i.code, htmlTip: `<code>${e.code}</code>` },

        'size-1': { cmd: 'fontSize', param: '1', icon: i.size, tip: e.size1, htmlTip: `<font size="1">${e.size1}</font>` },
        'size-2': { cmd: 'fontSize', param: '2', icon: i.size, tip: e.size2, htmlTip: `<font size="2">${e.size2}</font>` },
        'size-3': { cmd: 'fontSize', param: '3', icon: i.size, tip: e.size3, htmlTip: `<font size="3">${e.size3}</font>` },
        'size-4': { cmd: 'fontSize', param: '4', icon: i.size, tip: e.size4, htmlTip: `<font size="4">${e.size4}</font>` },
        'size-5': { cmd: 'fontSize', param: '5', icon: i.size, tip: e.size5, htmlTip: `<font size="5">${e.size5}</font>` },
        'size-6': { cmd: 'fontSize', param: '6', icon: i.size, tip: e.size6, htmlTip: `<font size="6">${e.size6}</font>` },
        'size-7': { cmd: 'fontSize', param: '7', icon: i.size, tip: e.size7, htmlTip: `<font size="7">${e.size7}</font>` }
      }
    },

    buttons () {
      const userDef = this.definitions || {};
      const def = this.definitions || this.fonts
        ? extend$1(
          true,
          {},
          this.buttonDef,
          userDef,
          getFonts(
            this.defaultFont,
            this.$q.lang.editor.defaultFont,
            this.$q.iconSet.editor.font,
            this.fonts
          )
        )
        : this.buttonDef;

      return this.toolbar.map(
        group => group.map(token => {
          if (token.options) {
            return {
              type: 'dropdown',
              icon: token.icon,
              label: token.label,
              size: 'sm',
              dense: true,
              fixedLabel: token.fixedLabel,
              fixedIcon: token.fixedIcon,
              highlight: token.highlight,
              list: token.list,
              options: token.options.map(item => def[item])
            }
          }

          const obj = def[token];

          if (obj) {
            return obj.type === 'no-state' || (userDef[token] && (
              obj.cmd === void 0 || (this.buttonDef[obj.cmd] && this.buttonDef[obj.cmd].type === 'no-state')
            ))
              ? obj
              : Object.assign({ type: 'toggle' }, obj)
          }
          else {
            return {
              type: 'slot',
              slot: token
            }
          }
        })
      )
    },

    keys () {
      const
        k = {},
        add = btn => {
          if (btn.key) {
            k[btn.key] = {
              cmd: btn.cmd,
              param: btn.param
            };
          }
        };

      this.buttons.forEach(group => {
        group.forEach(token => {
          if (token.options) {
            token.options.forEach(add);
          }
          else {
            add(token);
          }
        });
      });
      return k
    },

    innerStyle () {
      return this.inFullscreen
        ? this.contentStyle
        : [
          {
            minHeight: this.minHeight,
            height: this.height,
            maxHeight: this.maxHeight
          },
          this.contentStyle
        ]
    },
    innerClass () {
      return [
        this.contentClass,
        { col: this.inFullscreen, 'overflow-auto': this.inFullscreen || this.maxHeight }
      ]
    }
  },

  data () {
    return {
      editWatcher: true,
      editLinkUrl: null
    }
  },

  watch: {
    value (v) {
      if (this.editWatcher) {
        this.$refs.content.innerHTML = v;
      }
      else {
        this.editWatcher = true;
      }
    }
  },

  methods: {
    __onInput () {
      if (this.editWatcher) {
        const val = this.$refs.content.innerHTML;
        if (val !== this.value) {
          this.editWatcher = false;
          this.$emit('input', val);
        }
      }
    },

    __onKeydown (e) {
      if (!e.ctrlKey) {
        this.refreshToolbar();
        this.$q.platform.is.ie && this.$nextTick(this.__onInput);
        return
      }

      const key = e.keyCode;
      const target = this.keys[key];
      if (target !== void 0) {
        const { cmd, param } = target;
        stopAndPrevent(e);
        this.runCmd(cmd, param, false);
      }
    },

    runCmd (cmd, param, update = true) {
      this.focus();
      this.caret.apply(cmd, param, () => {
        this.focus();
        if (this.$q.platform.is.ie === true || this.$q.platform.is.edge === true) {
          this.$nextTick(this.__onInput);
        }
        if (update) {
          this.refreshToolbar();
        }
      });
    },

    refreshToolbar () {
      setTimeout(() => {
        this.editLinkUrl = null;
        this.$forceUpdate();
      }, 1);
    },

    focus () {
      this.$refs.content.focus();
    },

    getContentEl () {
      return this.$refs.content
    }
  },

  created () {
    if (isSSR === false) {
      document.execCommand('defaultParagraphSeparator', false, 'div');
      this.defaultFont = window.getComputedStyle(document.body).fontFamily;
    }
  },

  mounted () {
    this.caret = new Caret(this.$refs.content, this);
    this.$refs.content.innerHTML = this.value;
    this.refreshToolbar();
  },

  render (h) {
    let toolbars;

    if (this.hasToolbar) {
      const bars = [];

      bars.push(
        h('div', {
          key: 'qedt_top',
          staticClass: 'q-editor__toolbar row no-wrap scroll-x',
          class: this.toolbarBackgroundClass
        }, getToolbar(h, this))
      );

      this.editLinkUrl !== null && bars.push(
        h('div', {
          key: 'qedt_btm',
          staticClass: 'q-editor__toolbar row no-wrap items-center scroll-x',
          class: this.toolbarBackgroundClass
        }, getLinkEditor(h, this))
      );

      toolbars = h('div', {
        key: 'toolbar_ctainer',
        staticClass: 'q-editor__toolbars-container'
      }, bars);
    }

    return h(
      'div',
      {
        staticClass: 'q-editor',
        style: {
          height: this.inFullscreen ? '100vh' : null
        },
        'class': {
          disabled: this.disable,
          'fullscreen column': this.inFullscreen,
          'q-editor--square no-border-radius': this.square,
          'q-editor--flat': this.flat,
          'q-editor--dense': this.dense
        }
      },
      [
        toolbars,

        h(
          'div',
          {
            ref: 'content',
            staticClass: `q-editor__content`,
            style: this.innerStyle,
            class: this.innerClass,
            attrs: { contenteditable: this.editable },
            domProps: isSSR
              ? { innerHTML: this.value }
              : undefined,
            on: {
              input: this.__onInput,
              keydown: this.__onKeydown,
              click: this.refreshToolbar,
              blur: () => {
                this.caret.save();
              }
            }
          }
        )
      ]
    )
  }
});

var FabMixin = {
  props: {
    outline: Boolean,
    push: Boolean,
    flat: Boolean,
    color: String,
    textColor: String,
    glossy: Boolean,

    disable: Boolean
  }
};

Vue.extend({
  name: 'QFab',

  mixins: [ FabMixin, ModelToggleMixin ],

  provide () {
    return {
      __qFabClose: evt => {
        this.hide(evt);
        this.$refs.trigger && this.$refs.trigger.$el && this.$refs.trigger.$el.focus();
      }
    }
  },

  props: {
    icon: String,
    activeIcon: String,
    direction: {
      type: String,
      default: 'right',
      validator: v => ['up', 'right', 'down', 'left'].includes(v)
    },
    persistent: Boolean
  },

  watch: {
    $route () {
      this.persistent !== true && this.hide();
    }
  },

  render (h) {
    const tooltip = this.$scopedSlots.tooltip !== void 0
      ? this.$scopedSlots.tooltip()
      : [];

    return h('div', {
      staticClass: 'q-fab z-fab row inline justify-center',
      class: this.showing === true ? 'q-fab--opened' : null,
      on: this.$listeners
    }, [
      h(QBtn, {
        ref: 'trigger',
        props: {
          fab: true,
          outline: this.outline,
          push: this.push,
          flat: this.flat,
          color: this.color,
          textColor: this.textColor,
          glossy: this.glossy,
          disable: this.disable
        },
        on: {
          click: this.toggle
        }
      }, tooltip.concat([
        h(QIcon, {
          staticClass: 'q-fab__icon absolute-full',
          props: { name: this.icon || this.$q.iconSet.fab.icon }
        }),
        h(QIcon, {
          staticClass: 'q-fab__active-icon absolute-full',
          props: { name: this.activeIcon || this.$q.iconSet.fab.activeIcon }
        })
      ])),

      h('div', {
        staticClass: 'q-fab__actions flex no-wrap inline items-center',
        class: `q-fab__actions--${this.direction}`
      }, slot(this, 'default'))
    ])
  },

  created () {
    if (this.value === true && this.disable !== true) {
      this.showing = true;
    }
  }
});

Vue.extend({
  name: 'QFabAction',

  mixins: [ FabMixin ],

  props: {
    icon: {
      type: String,
      required: true
    },

    to: [String, Object],
    replace: Boolean
  },

  inject: {
    __qFabClose: {
      default () {
        console.error('QFabAction needs to be child of QFab');
      }
    }
  },

  methods: {
    click (e) {
      this.__qFabClose();
      this.$emit('click', e);
    }
  },

  render (h) {
    return h(QBtn, {
      props: {
        ...this.$props,
        disable: this.disable,
        fabMini: true
      },
      on: {
        ...this.$listeners,
        click: this.click
      }
    }, slot(this, 'default'))
  }
});

Vue.extend({
  name: 'QForm',

  props: {
    autofocus: Boolean,
    noErrorFocus: Boolean,
    noResetFocus: Boolean
  },

  mounted () {
    this.validateIndex = 0;
    this.autofocus === true && this.focus();
  },

  methods: {
    validate (shouldFocus) {
      const promises = [];
      const focus = typeof shouldFocus === 'boolean'
        ? shouldFocus
        : this.noErrorFocus !== true;

      this.validateIndex++;

      const components = getAllChildren(this);
      const emit = res => {
        this.$emit('validation-' + (res === true ? 'success' : 'error'));
      };

      for (let i = 0; i < components.length; i++) {
        const comp = components[i];

        if (typeof comp.validate === 'function') {
          const valid = comp.validate();

          if (typeof valid.then === 'function') {
            promises.push(
              valid.then(
                v => ({ valid: v, comp }),
                error => ({ valid: false, comp, error })
              )
            );
          }
          else if (valid !== true) {
            emit(false);

            if (focus === true && typeof comp.focus === 'function') {
              comp.focus();
            }

            return Promise.resolve(false)
          }
        }
      }

      if (promises.length === 0) {
        emit(true);
        return Promise.resolve(true)
      }

      const index = this.validateIndex;

      return Promise.all(promises).then(
        res => {
          if (index === this.validateIndex) {
            const { valid, comp } = res[0];

            emit(valid);

            if (
              focus === true &&
              valid !== true &&
              typeof comp.focus === 'function'
            ) {
              comp.focus();
            }

            return valid
          }
        }
      )
    },

    resetValidation () {
      this.validateIndex++;

      getAllChildren(this).forEach(comp => {
        if (typeof comp.resetValidation === 'function') {
          comp.resetValidation();
        }
      });
    },

    submit (evt) {
      evt !== void 0 && stopAndPrevent(evt);

      this.validate().then(val => {
        val === true && this.$emit('submit');
      });
    },

    reset (evt) {
      evt !== void 0 && stopAndPrevent(evt);

      this.$emit('reset');

      this.$nextTick(() => { // allow userland to reset values before
        this.resetValidation();
        if (this.autofocus === true && this.noResetFocus !== true) {
          this.focus();
        }
      });
    },

    focus () {
      const target = this.$el.querySelector('[autofocus]') || this.$el.querySelector('[tabindex]');
      target !== null && target.focus();
    }
  },

  render (h) {
    return h('form', {
      staticClass: 'q-form',
      on: {
        ...this.$listeners,
        submit: this.submit,
        reset: this.reset
      }
    }, slot(this, 'default'))
  }
});

Vue.extend({
  name: 'QImg',

  props: {
    src: String,
    srcset: String,
    sizes: String,
    alt: String,

    placeholderSrc: String,

    basic: Boolean,
    contain: Boolean,
    position: {
      type: String,
      default: '50% 50%'
    },
    ratio: [String, Number],
    transition: {
      type: String,
      default: 'fade'
    },

    spinnerColor: String,
    spinnerSize: String
  },

  data () {
    return {
      currentSrc: '',
      image: null,
      isLoading: !!this.src,
      hasError: false,
      naturalRatio: void 0
    }
  },

  watch: {
    src () {
      this.__load();
    },

    srcset (val) {
      this.__updateWatcher(val);
    }
  },

  computed: {
    aspectRatio () {
      return this.ratio || this.naturalRatio
    },

    padding () {
      return this.aspectRatio !== void 0
        ? (1 / this.aspectRatio) * 100 + '%'
        : void 0
    },

    url () {
      return this.currentSrc || this.placeholderSrc || void 0
    },

    attrs () {
      const att = { role: 'img' };
      if (this.alt !== void 0) {
        att['aria-label'] = this.alt;
      }
      return att
    }
  },

  methods: {
    __onLoad () {
      this.__updateSrc();
      this.__updateWatcher(this.srcset);
      this.$emit('load', this.currentSrc);
    },

    __onError (err) {
      this.hasError = true;
      this.currentSrc = '';
      this.$emit('error', err);
    },

    __updateSrc () {
      if (this.image !== void 0 && this.isLoading === false) {
        const src = this.image.currentSrc || this.image.src;
        if (this.currentSrc !== src) {
          this.currentSrc = src;
        }
      }
    },

    __updateWatcher (srcset) {
      if (srcset) {
        if (this.unwatch === void 0) {
          this.unwatch = this.$watch('$q.screen.width', this.__updateSrc);
        }
      }
      else if (this.unwatch !== void 0) {
        this.unwatch();
        this.unwatch = void 0;
      }
    },

    __load () {
      clearTimeout(this.timer);
      this.hasError = false;

      if (!this.src) {
        this.isLoading = false;
        this.image = void 0;
        this.currentSrc = '';
        return
      }

      this.isLoading = true;

      const img = new Image();
      this.image = img;

      img.onerror = err => {
        // if we are still rendering same image
        if (this.image === img) {
          this.isLoading = false;
          this.__onError(err);
        }
      };

      img.onload = () => {
        // if we are still rendering same image
        if (this.image === img) {
          this.isLoading = false;

          if (this.image.decode) {
            this.image
              .decode()
              .catch(this.__onError)
              .then(this.__onLoad);
          }
          else {
            this.__onLoad();
          }
        }
      };

      img.src = this.src;

      if (this.srcset) {
        img.srcset = this.srcset;
      }
      if (this.sizes) {
        img.sizes = this.sizes;
      }

      this.__computeRatio(img);
    },

    __computeRatio (img) {
      const { naturalHeight, naturalWidth } = img;

      if (naturalHeight || naturalWidth) {
        this.naturalRatio = naturalWidth / naturalHeight;
      }
      else {
        this.timer = setTimeout(() => {
          this.__computeRatio(img);
        }, 100);
      }
    },

    __getImage (h) {
      const content = this.url !== void 0 ? h('div', {
        key: this.url,
        staticClass: 'q-img__image absolute-full',
        style: {
          backgroundImage: `url("${this.url}")`,
          backgroundSize: this.contain ? 'contain' : 'cover',
          backgroundPosition: this.position
        }
      }) : null;

      return this.basic === true
        ? content
        : h('transition', {
          props: { name: 'q-transition--' + this.transition }
        }, [ content ])
    },

    __getContent (h) {
      const slotVm = slot(this, this.hasError === true ? 'error' : 'default');

      if (this.basic === true) {
        return h('div', {
          key: 'content',
          staticClass: 'q-img__content absolute-full'
        }, slotVm)
      }

      const content = this.isLoading === true
        ? h('div', {
          key: 'placeholder',
          staticClass: 'q-img__loading absolute-full flex flex-center'
        }, this.$scopedSlots.loading !== void 0 ? this.$scopedSlots.loading() : [
          h(QSpinner, {
            props: {
              color: this.spinnerColor,
              size: this.spinnerSize
            }
          })
        ])
        : h('div', {
          key: 'content',
          staticClass: 'q-img__content absolute-full'
        }, slotVm);

      return h('transition', {
        props: { name: 'q-transition--fade' }
      }, [ content ])
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-img overflow-hidden',
      attrs: this.attrs,
      on: this.$listeners
    }, [
      h('div', {
        style: { paddingBottom: this.padding }
      }),
      this.__getImage(h),
      this.__getContent(h)
    ])
  },

  beforeMount () {
    if (this.placeholderSrc !== void 0 && this.ratio === void 0) {
      const img = new Image();
      img.src = this.placeholderSrc;
      this.__computeRatio(img);
    }
    this.isLoading === true && this.__load();
  },

  beforeDestroy () {
    clearTimeout(this.timer);
    this.unwatch !== void 0 && this.unwatch();
  }
});

Vue.extend({
  name: 'QInfiniteScroll',

  props: {
    offset: {
      type: Number,
      default: 500
    },
    scrollTarget: {},
    disable: Boolean,
    reverse: Boolean
  },

  data () {
    return {
      index: 0,
      fetching: false,
      working: true
    }
  },

  watch: {
    disable (val) {
      if (val === true) {
        this.stop();
      }
      else {
        this.resume();
      }
    },

    scrollTarget () {
      this.updateScrollTarget();
    }
  },

  methods: {
    poll () {
      if (this.disable === true || this.fetching === true || this.working === false) {
        return
      }

      const
        scrollHeight = getScrollHeight(this.scrollContainer),
        scrollPosition = getScrollPosition(this.scrollContainer),
        containerHeight = height(this.scrollContainer);

      if (this.reverse === false) {
        if (scrollPosition + containerHeight + this.offset >= scrollHeight) {
          this.trigger();
        }
      }
      else {
        if (scrollPosition < this.offset) {
          this.trigger();
        }
      }
    },

    trigger () {
      if (this.disable === true || this.fetching === true || this.working === false) {
        return
      }

      this.index++;
      this.fetching = true;

      const heightBefore = getScrollHeight(this.scrollContainer);

      this.$emit('load', this.index, () => {
        if (this.working === true) {
          this.fetching = false;
          this.$nextTick(() => {
            if (this.reverse === true) {
              const
                heightAfter = getScrollHeight(this.scrollContainer),
                scrollPosition = getScrollPosition(this.scrollContainer),
                heightDifference = heightAfter - heightBefore;

              this.scrollContainer.scrollTop = scrollPosition + heightDifference;
            }

            this.$el.closest('body') && this.poll();
          });
        }
      });
    },

    reset () {
      this.index = 0;
    },

    resume () {
      if (this.working === false) {
        this.working = true;
        this.scrollContainer.addEventListener('scroll', this.poll, listenOpts.passive);
      }
      this.immediatePoll();
    },

    stop () {
      if (this.working === true) {
        this.working = false;
        this.fetching = false;
        this.scrollContainer.removeEventListener('scroll', this.poll, listenOpts.passive);
      }
    },

    updateScrollTarget () {
      if (this.scrollContainer && this.working === true) {
        this.scrollContainer.removeEventListener('scroll', this.poll, listenOpts.passive);
      }

      if (typeof this.scrollTarget === 'string') {
        this.scrollContainer = document.querySelector(this.scrollTarget);
        if (this.scrollContainer === null) {
          console.error(`InfiniteScroll: scroll target container "${this.scrollTarget}" not found`, this);
          return
        }
      }
      else {
        this.scrollContainer = this.scrollTarget === document.defaultView || this.scrollTarget instanceof Element
          ? this.scrollTarget
          : getScrollTarget(this.$el);
      }

      if (this.working === true) {
        this.scrollContainer.addEventListener('scroll', this.poll, listenOpts.passive);
      }
    }
  },

  mounted () {
    this.immediatePoll = this.poll;
    this.poll = debounce(this.poll, 100);

    this.updateScrollTarget();
    this.immediatePoll();

    if (this.reverse === true) {
      const
        scrollHeight = getScrollHeight(this.scrollContainer),
        containerHeight = height(this.scrollContainer);

      this.scrollContainer.scrollTop = scrollHeight - containerHeight;
    }
  },

  beforeDestroy () {
    if (this.working === true) {
      this.scrollContainer.removeEventListener('scroll', this.poll, listenOpts.passive);
    }
  },

  render (h) {
    const content = this.$scopedSlots.default !== void 0
      ? this.$scopedSlots.default()
      : [];
    const body = this.fetching === true
      ? [ h('div', { staticClass: 'q-infinite-scroll__loading' }, slot(this, 'loading')) ]
      : [];

    return h(
      'div',
      { staticClass: 'q-infinite-scroll' },
      this.reverse === false
        ? content.concat(body)
        : body.concat(content)
    )
  }
});

Vue.extend({
  name: 'QInnerLoading',

  mixins: [ TransitionMixin ],

  props: {
    showing: Boolean,
    color: String,

    size: {
      type: [String, Number],
      default: 42
    },

    dark: Boolean
  },

  render (h) {
    const content = this.$scopedSlots.default !== void 0
      ? this.$scopedSlots.default()
      : [
        h(QSpinner, {
          props: {
            size: this.size,
            color: this.color
          }
        })
      ];

    return h('transition', {
      props: { name: this.transition }
    }, [
      this.showing === true ? h('div', {
        staticClass: 'q-inner-loading absolute-full column flex-center',
        class: this.dark ? 'q-inner-loading--dark' : null,
        on: this.$listeners
      }, content) : null
    ])
  }
});

// PGDOWN, LEFT, DOWN, PGUP, RIGHT, UP
const keyCodes$1 = [34, 37, 40, 33, 39, 38];

Vue.extend({
  name: 'QKnob',

  mixins: [{
    props: QCircularProgress.options.props
  }],

  directives: {
    TouchPan
  },

  props: {
    step: {
      type: Number,
      default: 1,
      validator: v => v >= 0
    },

    tabindex: {
      type: [Number, String],
      default: 0
    },

    disable: Boolean,
    readonly: Boolean
  },

  data () {
    return {
      model: this.value,
      dragging: false
    }
  },

  watch: {
    value (value) {
      if (value < this.min) {
        this.model = this.min;
      }
      else if (value > this.max) {
        this.model = this.max;
      }
      else {
        if (value !== this.model) {
          this.model = value;
        }
        return
      }

      if (this.model !== this.value) {
        this.$emit('input', this.model);
        this.$emit('change', this.model);
      }
    }
  },

  computed: {
    classes () {
      return {
        disabled: this.disable,
        'q-knob--editable': this.editable
      }
    },

    editable () {
      return !this.disable && !this.readonly
    },

    decimals () {
      return (String(this.step).trim('0').split('.')[1] || '').length
    },

    computedStep () {
      return this.step === 0 ? 1 : this.step
    }
  },

  methods: {
    __pan (event) {
      if (event.isFinal) {
        this.__updatePosition(event.evt, true);
        this.dragging = false;
      }
      else if (event.isFirst) {
        const { top, left, width, height } = this.$el.getBoundingClientRect();
        this.centerPosition = {
          top: top + height / 2,
          left: left + width / 2
        };
        this.dragging = true;
        this.__updatePosition(event.evt);
      }
      else {
        this.__updatePosition(event.evt);
      }
    },

    __click (evt) {
      const { top, left, width, height } = this.$el.getBoundingClientRect();
      this.centerPosition = {
        top: top + height / 2,
        left: left + width / 2
      };
      this.__updatePosition(evt, true);
    },

    __keydown (evt) {
      if (!keyCodes$1.includes(evt.keyCode)) {
        return
      }

      stopAndPrevent(evt);

      const
        step = ([34, 33].includes(evt.keyCode) ? 10 : 1) * this.computedStep,
        offset = [34, 37, 40].includes(evt.keyCode) ? -step : step;

      this.model = between(
        parseFloat((this.model + offset).toFixed(this.decimals)),
        this.min,
        this.max
      );

      this.__updateValue();
    },

    __keyup (evt) {
      if (keyCodes$1.includes(evt.keyCode)) {
        this.__updateValue(true);
      }
    },

    __updatePosition (evt, change) {
      const
        center = this.centerPosition,
        pos = position(evt),
        height = Math.abs(pos.top - center.top),
        distance = Math.sqrt(
          height ** 2 +
          Math.abs(pos.left - center.left) ** 2
        );

      let angle = Math.asin(height / distance) * (180 / Math.PI);

      if (pos.top < center.top) {
        angle = center.left < pos.left ? 90 - angle : 270 + angle;
      }
      else {
        angle = center.left < pos.left ? angle + 90 : 270 - angle;
      }

      if (this.angle) {
        angle = normalizeToInterval(angle - this.angle, 0, 360);
      }

      if (this.$q.lang.rtl) {
        angle = 360 - angle;
      }

      let model = this.min + (angle / 360) * (this.max - this.min);

      if (this.step !== 0) {
        const
          step = this.computedStep,
          modulo = model % step;

        model = model - modulo +
          (Math.abs(modulo) >= step / 2 ? (modulo < 0 ? -1 : 1) * step : 0);

        model = parseFloat(model.toFixed(this.decimals));
      }

      model = between(model, this.min, this.max);

      this.$emit('drag-value', model);

      if (this.model !== model) {
        this.model = model;
      }

      this.__updateValue(change);
    },

    __updateValue (change) {
      this.value !== this.model && this.$emit('input', this.model);
      change === true && this.$emit('change', this.model);
    }
  },

  render (h) {
    const data = {
      staticClass: 'q-knob non-selectable',
      class: this.classes,

      props: {
        ...this.$props,
        value: this.model,
        instantFeedback: this.dragging
      }
    };

    if (this.editable === true) {
      data.attrs = { tabindex: this.tabindex };
      data.on = {
        click: this.__click,
        keydown: this.__keydown,
        keyup: this.__keyup
      };
      data.directives = [{
        name: 'touch-pan',
        value: this.__pan,
        modifiers: {
          prevent: true,
          stop: true,
          mouse: true,
          mousePrevent: true,
          mouseStop: true
        }
      }];
    }

    return h(QCircularProgress, data, slot(this, 'default'))
  }
});

var QScrollObserver = Vue.extend({
  name: 'QScrollObserver',

  props: {
    debounce: [String, Number],
    horizontal: Boolean
  },

  render () {}, // eslint-disable-line

  data () {
    return {
      pos: 0,
      dir: this.horizontal === true ? 'right' : 'down',
      dirChanged: false,
      dirChangePos: 0
    }
  },

  methods: {
    getPosition () {
      return {
        position: this.pos,
        direction: this.dir,
        directionChanged: this.dirChanged,
        inflexionPosition: this.dirChangePos
      }
    },

    trigger (immediately) {
      if (immediately === true || this.debounce === 0 || this.debounce === '0') {
        this.__emit();
      }
      else if (!this.timer) {
        this.timer = this.debounce
          ? setTimeout(this.__emit, this.debounce)
          : requestAnimationFrame(this.__emit);
      }
    },

    __emit () {
      const
        pos = Math.max(0, (this.horizontal === true ? getHorizontalScrollPosition(this.target) : getScrollPosition(this.target))),
        delta = pos - this.pos,
        dir = this.horizontal
          ? delta < 0 ? 'left' : 'right'
          : delta < 0 ? 'up' : 'down';
      this.dirChanged = this.dir !== dir;
      if (this.dirChanged) {
        this.dir = dir;
        this.dirChangePos = this.pos;
      }

      this.timer = null;
      this.pos = pos;
      this.$emit('scroll', this.getPosition());
    }
  },

  mounted () {
    this.target = getScrollTarget(this.$el.parentNode);
    this.target.addEventListener('scroll', this.trigger, listenOpts.passive);
    this.trigger(true);
  },

  beforeDestroy () {
    clearTimeout(this.timer);
    cancelAnimationFrame(this.timer);
    this.target.removeEventListener('scroll', this.trigger, listenOpts.passive);
  }
});

var QLayout = Vue.extend({
  name: 'QLayout',

  provide () {
    return {
      layout: this
    }
  },

  props: {
    container: Boolean,
    view: {
      type: String,
      default: 'hhh lpr fff',
      validator: v => /^(h|l)h(h|r) lpr (f|l)f(f|r)$/.test(v.toLowerCase())
    }
  },

  data () {
    return {
      // page related
      height: onSSR === true ? 0 : window.innerHeight,
      width: onSSR === true || this.container === true ? 0 : window.innerWidth,

      // container only prop
      containerHeight: 0,
      scrollbarWidth: onSSR === true ? 0 : getScrollbarWidth(),

      header: {
        size: 0,
        offset: 0,
        space: false
      },
      right: {
        size: 300,
        offset: 0,
        space: false
      },
      footer: {
        size: 0,
        offset: 0,
        space: false
      },
      left: {
        size: 300,
        offset: 0,
        space: false
      },

      scroll: {
        position: 0,
        direction: 'down'
      }
    }
  },

  computed: {
    rows () {
      const rows = this.view.toLowerCase().split(' ');
      return {
        top: rows[0].split(''),
        middle: rows[1].split(''),
        bottom: rows[2].split('')
      }
    },

    // used by container only
    targetStyle () {
      if (this.scrollbarWidth !== 0) {
        return { [this.$q.lang.rtl === true ? 'left' : 'right']: `${this.scrollbarWidth}px` }
      }
    },

    targetChildStyle () {
      if (this.scrollbarWidth !== 0) {
        return {
          [this.$q.lang.rtl === true ? 'right' : 'left']: 0,
          [this.$q.lang.rtl === true ? 'left' : 'right']: `-${this.scrollbarWidth}px`,
          width: `calc(100% + ${this.scrollbarWidth}px)`
        }
      }
    }
  },

  created () {
    this.instances = {};
  },

  render (h) {
    const layout = h('div', {
      staticClass: 'q-layout q-layout--' +
        (this.container === true ? 'containerized' : 'standard')
    }, [
      h(QScrollObserver, {
        on: { scroll: this.__onPageScroll }
      }),
      h(QResizeObserver, {
        on: { resize: this.__onPageResize }
      })
    ].concat(
      slot(this, 'default')
    ));

    return this.container === true
      ? h('div', {
        staticClass: 'q-layout-container overflow-hidden'
      }, [
        h(QResizeObserver, {
          on: { resize: this.__onContainerResize }
        }),
        h('div', {
          staticClass: 'absolute-full',
          style: this.targetStyle
        }, [
          h('div', {
            staticClass: 'overflow-auto',
            style: this.targetChildStyle
          }, [ layout ])
        ])
      ])
      : layout
  },

  methods: {
    __animate () {
      if (this.timer !== void 0) {
        clearTimeout(this.timer);
      }
      else {
        document.body.classList.add('q-body--layout-animate');
      }
      this.timer = setTimeout(() => {
        document.body.classList.remove('q-body--layout-animate');
        this.timer = void 0;
      }, 150);
    },

    __onPageScroll (data) {
      this.scroll = data;
      this.$listeners.scroll !== void 0 && this.$emit('scroll', data);
    },

    __onPageResize ({ height, width }) {
      let resized = false;

      if (this.height !== height) {
        resized = true;
        this.height = height;
        if (this.$listeners['scroll-height'] !== void 0) {
          this.$emit('scroll-height', height);
        }
        this.__updateScrollbarWidth();
      }
      if (this.width !== width) {
        resized = true;
        this.width = width;
      }

      if (resized === true && this.$listeners.resize !== void 0) {
        this.$emit('resize', { height, width });
      }
    },

    __onContainerResize ({ height }) {
      if (this.containerHeight !== height) {
        this.containerHeight = height;
        this.__updateScrollbarWidth();
      }
    },

    __updateScrollbarWidth () {
      if (this.container) {
        const width = this.height > this.containerHeight
          ? getScrollbarWidth()
          : 0;

        if (this.scrollbarWidth !== width) {
          this.scrollbarWidth = width;
        }
      }
    }
  }
});

const duration = 150;

var QDrawer = Vue.extend({
  name: 'QDrawer',

  inject: {
    layout: {
      default () {
        console.error('QDrawer needs to be child of QLayout');
      }
    }
  },

  mixins: [ ModelToggleMixin, PreventScrollMixin ],

  directives: {
    TouchPan
  },

  props: {
    overlay: Boolean,
    side: {
      type: String,
      default: 'left',
      validator: v => ['left', 'right'].includes(v)
    },
    width: {
      type: Number,
      default: 300
    },
    mini: Boolean,
    miniWidth: {
      type: Number,
      default: 57
    },
    breakpoint: {
      type: Number,
      default: 1023
    },
    behavior: {
      type: String,
      validator: v => ['default', 'desktop', 'mobile'].includes(v),
      default: 'default'
    },
    bordered: Boolean,
    elevated: Boolean,
    persistent: Boolean,
    showIfAbove: Boolean,
    contentStyle: [String, Object, Array],
    contentClass: [String, Object, Array],
    noSwipeOpen: Boolean,
    noSwipeClose: Boolean
  },

  data () {
    const
      largeScreenState = this.showIfAbove === true || (
        this.value !== void 0 ? this.value : true
      ),
      showing = this.behavior !== 'mobile' && this.breakpoint < this.layout.width && this.overlay === false
        ? largeScreenState
        : false;

    if (this.value !== void 0 && this.value !== showing) {
      // setTimeout needed otherwise
      // it breaks Vue state
      setTimeout(() => {
        this.$emit('input', showing);
      });
    }

    return {
      showing,
      belowBreakpoint: (
        this.behavior === 'mobile' ||
        (this.behavior !== 'desktop' && this.breakpoint >= this.layout.width)
      ),
      largeScreenState,
      mobileOpened: false
    }
  },

  watch: {
    belowBreakpoint (val) {
      if (this.mobileOpened === true) {
        return
      }

      if (val === true) { // from lg to xs
        if (this.overlay === false) {
          this.largeScreenState = this.showing;
        }
        // ensure we close it for small screen
        this.hide(false);
      }
      else if (this.overlay === false) { // from xs to lg
        this[this.largeScreenState ? 'show' : 'hide'](false);
      }
    },

    side (_, oldSide) {
      this.layout[oldSide].space = false;
      this.layout[oldSide].offset = 0;
    },

    behavior (val) {
      this.__updateLocal('belowBreakpoint', (
        val === 'mobile' ||
        (val !== 'desktop' && this.breakpoint >= this.layout.width)
      ));
    },

    breakpoint (val) {
      this.__updateLocal('belowBreakpoint', (
        this.behavior === 'mobile' ||
        (this.behavior !== 'desktop' && val >= this.layout.width)
      ));
    },

    'layout.width' (val) {
      this.__updateLocal('belowBreakpoint', (
        this.behavior === 'mobile' ||
        (this.behavior !== 'desktop' && this.breakpoint >= val)
      ));
    },

    'layout.scrollbarWidth' () {
      this.applyPosition(this.showing === true ? 0 : void 0);
    },

    offset (val) {
      this.__update('offset', val);
    },

    onLayout (val) {
      this.$listeners['on-layout'] !== void 0 && this.$emit('on-layout', val);
      this.__update('space', val);
    },

    $route () {
      if (
        this.persistent !== true &&
        (this.mobileOpened === true || this.onScreenOverlay === true)
      ) {
        this.hide();
      }
    },

    rightSide () {
      this.applyPosition();
    },

    size (val) {
      this.applyPosition();
      this.__update('size', val);
    },

    '$q.lang.rtl' () {
      this.applyPosition();
    },

    mini () {
      if (this.value === true) {
        this.__animateMini();
        this.layout.__animate();
      }
    }
  },

  computed: {
    rightSide () {
      return this.side === 'right'
    },

    offset () {
      return this.showing === true && this.mobileOpened === false && this.overlay === false
        ? this.size
        : 0
    },

    size () {
      return this.isMini === true ? this.miniWidth : this.width
    },

    fixed () {
      return this.overlay === true || this.layout.view.indexOf(this.rightSide ? 'R' : 'L') > -1
    },

    onLayout () {
      return this.showing === true && this.mobileView === false && this.overlay === false
    },

    onScreenOverlay () {
      return this.showing === true && this.mobileView === false && this.overlay === true
    },

    backdropClass () {
      return this.showing === false ? 'no-pointer-events' : null
    },

    mobileView () {
      return this.belowBreakpoint === true || this.mobileOpened === true
    },

    headerSlot () {
      return this.rightSide === true
        ? this.layout.rows.top[2] === 'r'
        : this.layout.rows.top[0] === 'l'
    },

    footerSlot () {
      return this.rightSide === true
        ? this.layout.rows.bottom[2] === 'r'
        : this.layout.rows.bottom[0] === 'l'
    },

    aboveStyle () {
      const css = {};

      if (this.layout.header.space === true && this.headerSlot === false) {
        if (this.fixed === true) {
          css.top = `${this.layout.header.offset}px`;
        }
        else if (this.layout.header.space === true) {
          css.top = `${this.layout.header.size}px`;
        }
      }

      if (this.layout.footer.space === true && this.footerSlot === false) {
        if (this.fixed === true) {
          css.bottom = `${this.layout.footer.offset}px`;
        }
        else if (this.layout.footer.space === true) {
          css.bottom = `${this.layout.footer.size}px`;
        }
      }

      return css
    },

    style () {
      const style = { width: `${this.size}px` };
      return this.mobileView === true
        ? style
        : Object.assign(style, this.aboveStyle)
    },

    classes () {
      return `q-drawer--${this.side}` +
        (this.bordered === true ? ' q-drawer--bordered' : '') +
        (
          this.mobileView === true
            ? ' fixed q-drawer--on-top q-drawer--mobile q-drawer--top-padding'
            : ` q-drawer--${this.isMini === true ? 'mini' : 'standard'}` +
              (this.fixed === true || this.onLayout !== true ? ' fixed' : '') +
              (this.overlay === true ? ' q-drawer--on-top' : '') +
              (this.headerSlot === true ? ' q-drawer--top-padding' : '')
        )
    },

    stateDirection () {
      return (this.$q.lang.rtl === true ? -1 : 1) * (this.rightSide === true ? 1 : -1)
    },

    isMini () {
      return this.mini === true && this.mobileView !== true
    },

    onNativeEvents () {
      if (this.mobileView !== true) {
        return {
          '!click': e => { this.$emit('click', e); },
          mouseover: e => { this.$emit('mouseover', e); },
          mouseout: e => { this.$emit('mouseout', e); }
        }
      }
    }
  },

  methods: {
    applyPosition (position) {
      if (position === void 0) {
        this.$nextTick(() => {
          position = this.showing === true ? 0 : this.size;

          this.applyPosition(this.stateDirection * position);
        });
      }
      else if (this.$refs.content !== void 0) {
        if (
          this.layout.container === true &&
          this.rightSide === true &&
          (this.mobileView === true || Math.abs(position) === this.size)
        ) {
          position += this.stateDirection * this.layout.scrollbarWidth;
        }
        this.$refs.content.style.transform = `translate3d(${position}px, 0, 0)`;
      }
    },

    applyBackdrop (x) {
      if (this.$refs.backdrop !== void 0) {
        this.$refs.backdrop.style.backgroundColor =
          this.lastBackdropBg = `rgba(0,0,0,${x * 0.4})`;
      }
    },

    __setScrollable (v) {
      if (this.layout.container !== true) {
        document.body.classList[v === true ? 'add' : 'remove']('q-body--drawer-toggle');
      }
    },

    __animateMini () {
      if (this.timerMini !== void 0) {
        clearTimeout(this.timerMini);
      }
      else if (this.$el !== void 0) {
        this.$el.classList.add('q-drawer--mini-animate');
      }
      this.timerMini = setTimeout(() => {
        this.$el !== void 0 && this.$el.classList.remove('q-drawer--mini-animate');
        this.timerMini = void 0;
      }, 150);
    },

    __openByTouch (evt) {
      const
        width = this.size,
        position = between(evt.distance.x, 0, width);

      if (evt.isFinal === true) {
        const
          el = this.$refs.content,
          opened = position >= Math.min(75, width);

        el.classList.remove('no-transition');

        if (opened === true) {
          this.show();
        }
        else {
          this.layout.__animate();
          this.applyBackdrop(0);
          this.applyPosition(this.stateDirection * width);
          el.classList.remove('q-drawer--delimiter');
        }

        return
      }

      this.applyPosition(
        (this.$q.lang.rtl === true ? !this.rightSide : this.rightSide)
          ? Math.max(width - position, 0)
          : Math.min(0, position - width)
      );
      this.applyBackdrop(
        between(position / width, 0, 1)
      );

      if (evt.isFirst === true) {
        const el = this.$refs.content;
        el.classList.add('no-transition');
        el.classList.add('q-drawer--delimiter');
      }
    },

    __closeByTouch (evt) {
      const
        width = this.size,
        dir = evt.direction === this.side,
        position = (this.$q.lang.rtl === true ? !dir : dir)
          ? between(evt.distance.x, 0, width)
          : 0;

      if (evt.isFinal === true) {
        const opened = Math.abs(position) < Math.min(75, width);
        this.$refs.content.classList.remove('no-transition');

        if (opened === true) {
          this.layout.__animate();
          this.applyBackdrop(1);
          this.applyPosition(0);
        }
        else {
          this.hide();
        }

        return
      }

      this.applyPosition(this.stateDirection * position);
      this.applyBackdrop(between(1 - position / width, 0, 1));

      if (evt.isFirst === true) {
        this.$refs.content.classList.add('no-transition');
      }
    },

    __show (evt = true) {
      evt !== false && this.layout.__animate();
      this.applyPosition(0);

      const otherSide = this.layout.instances[this.rightSide === true ? 'left' : 'right'];
      if (otherSide !== void 0 && otherSide.mobileOpened === true) {
        otherSide.hide(false);
      }

      if (this.belowBreakpoint === true) {
        this.mobileOpened = true;
        this.applyBackdrop(1);
        if (this.layout.container !== true) {
          this.__preventScroll(true);
        }
      }
      else {
        this.__setScrollable(true);
      }

      clearTimeout(this.timer);
      this.timer = setTimeout(() => {
        this.__setScrollable(false);
        this.$emit('show', evt);
      }, duration);
    },

    __hide (evt = true) {
      evt !== false && this.layout.__animate();

      if (this.mobileOpened === true) {
        this.mobileOpened = false;
      }

      this.applyPosition(this.stateDirection * this.size);
      this.applyBackdrop(0);

      this.__cleanup();

      clearTimeout(this.timer);
      this.timer = setTimeout(() => {
        this.$emit('hide', evt);
      }, duration);
    },

    __cleanup () {
      this.__preventScroll(false);
      this.__setScrollable(false);
    },

    __update (prop, val) {
      if (this.layout[this.side][prop] !== val) {
        this.layout[this.side][prop] = val;
      }
    },

    __updateLocal (prop, val) {
      if (this[prop] !== val) {
        this[prop] = val;
      }
    }
  },

  created () {
    this.layout.instances[this.side] = this;
    this.__update('size', this.size);
    this.__update('space', this.onLayout);
    this.__update('offset', this.offset);
  },

  mounted () {
    this.$listeners['on-layout'] !== void 0 && this.$emit('on-layout', this.onLayout);
    this.applyPosition(this.showing === true ? 0 : void 0);
  },

  beforeDestroy () {
    clearTimeout(this.timer);
    clearTimeout(this.timerMini);
    this.showing === true && this.__cleanup();
    if (this.layout.instances[this.side] === this) {
      this.layout.instances[this.side] = void 0;
      this.__update('size', 0);
      this.__update('offset', 0);
      this.__update('space', false);
    }
  },

  render (h) {
    const directives = [{
      name: 'touch-pan',
      modifiers: {
        horizontal: true,
        mouse: true,
        mouseAllDir: true
      },
      value: this.__closeByTouch
    }];

    const child = [
      this.noSwipeOpen !== true && this.belowBreakpoint === true
        ? h('div', {
          staticClass: `q-drawer__opener fixed-${this.side}`,
          directives: [{
            name: 'touch-pan',
            modifiers: {
              horizontal: true,
              mouse: true,
              mouseAllDir: true
            },
            value: this.__openByTouch
          }]
        })
        : null,

      this.mobileView === true ? h('div', {
        ref: 'backdrop',
        staticClass: 'fullscreen q-drawer__backdrop q-layout__section--animate',
        class: this.backdropClass,
        style: this.lastBackdropBg !== void 0
          ? { backgroundColor: this.lastBackdropBg }
          : null,
        on: { click: this.hide },
        directives
      }) : null
    ];

    const content = [
      h('div', {
        staticClass: 'q-drawer__content fit ' + (this.layout.container === true ? 'overflow-auto' : 'scroll'),
        class: this.contentClass,
        style: this.contentStyle
      }, this.isMini === true && this.$scopedSlots.mini !== void 0 ? this.$scopedSlots.mini() : slot(this, 'default'))
    ];

    if (this.elevated === true && this.showing === true) {
      content.push(
        h('div', {
          staticClass: 'q-layout__shadow absolute-full overflow-hidden no-pointer-events'
        })
      );
    }

    return h('div', {
      staticClass: 'q-drawer-container'
    }, child.concat([
      h('aside', {
        ref: 'content',
        staticClass: `q-drawer q-layout__section--animate`,
        class: this.classes,
        style: this.style,
        on: this.onNativeEvents,
        directives: this.mobileView === true && this.noSwipeClose !== true
          ? directives
          : void 0
      }, content)
    ]))
  }
});

var QFooter = Vue.extend({
  name: 'QFooter',

  mixins: [ CanRenderMixin ],

  inject: {
    layout: {
      default () {
        console.error('QFooter needs to be child of QLayout');
      }
    }
  },

  props: {
    value: {
      type: Boolean,
      default: true
    },
    reveal: Boolean,
    bordered: Boolean,
    elevated: Boolean
  },

  data () {
    return {
      size: 0,
      revealed: true,
      windowHeight: onSSR || this.layout.container ? 0 : window.innerHeight
    }
  },

  watch: {
    value (val) {
      this.__update('space', val);
      this.__updateLocal('revealed', true);
      this.layout.__animate();
    },

    offset (val) {
      this.__update('offset', val);
    },

    reveal (val) {
      val === false && this.__updateLocal('revealed', this.value);
    },

    revealed (val) {
      this.layout.__animate();
      this.$emit('reveal', val);
    },

    'layout.scroll' () {
      this.__updateRevealed();
    },

    'layout.height' () {
      this.__updateRevealed();
    },

    size () {
      this.__updateRevealed();
    },

    '$q.screen.height' (val) {
      this.layout.container !== true && this.__updateLocal('windowHeight', val);
    }
  },

  computed: {
    fixed () {
      return this.reveal === true ||
        this.layout.view.indexOf('F') > -1 ||
        this.layout.container === true
    },

    containerHeight () {
      return this.layout.container === true
        ? this.layout.containerHeight
        : this.windowHeight
    },

    offset () {
      if (this.canRender !== true || this.value !== true) {
        return 0
      }
      if (this.fixed === true) {
        return this.revealed === true ? this.size : 0
      }
      const offset = this.layout.scroll.position + this.containerHeight + this.size - this.layout.height;
      return offset > 0 ? offset : 0
    },

    classes () {
      return (
        (this.fixed === true ? 'fixed' : 'absolute') + '-bottom') +
        (this.value === true || this.fixed === true ? '' : ' hidden') +
        (this.bordered === true ? ' q-footer--bordered' : '') +
        (
          this.canRender !== true || this.value !== true || (this.fixed === true && this.revealed !== true)
            ? ' q-footer--hidden'
            : ''
        )
    },

    style () {
      const
        view = this.layout.rows.bottom,
        css = {};

      if (view[0] === 'l' && this.layout.left.space === true) {
        css[this.$q.lang.rtl ? 'right' : 'left'] = `${this.layout.left.size}px`;
      }
      if (view[2] === 'r' && this.layout.right.space === true) {
        css[this.$q.lang.rtl ? 'left' : 'right'] = `${this.layout.right.size}px`;
      }

      return css
    }
  },

  render (h) {
    const child = [
      h(QResizeObserver, {
        props: { debounce: 0 },
        on: { resize: this.__onResize }
      })
    ];

    this.elevated === true && child.push(
      h('div', {
        staticClass: 'q-layout__shadow absolute-full overflow-hidden no-pointer-events'
      })
    );

    return h('footer', {
      staticClass: 'q-footer q-layout__section--marginal q-layout__section--animate',
      class: this.classes,
      style: this.style,
      on: {
        ...this.$listeners,
        input: stop
      }
    }, child.concat(slot(this, 'default')))
  },

  created () {
    this.layout.instances.footer = this;
    this.__update('space', this.value);
    this.__update('offset', this.offset);
  },

  beforeDestroy () {
    if (this.layout.instances.footer === this) {
      this.layout.instances.footer = void 0;
      this.__update('size', 0);
      this.__update('offset', 0);
      this.__update('space', false);
    }
  },

  methods: {
    __onResize ({ height }) {
      this.__updateLocal('size', height);
      this.__update('size', height);
    },

    __update (prop, val) {
      if (this.layout.footer[prop] !== val) {
        this.layout.footer[prop] = val;
      }
    },

    __updateLocal (prop, val) {
      if (this[prop] !== val) {
        this[prop] = val;
      }
    },

    __updateRevealed () {
      if (this.reveal !== true) { return }

      const { direction, position, inflexionPosition } = this.layout.scroll;

      this.__updateLocal('revealed', (
        direction === 'up' ||
        position - inflexionPosition < 100 ||
        this.layout.height - this.containerHeight - position - this.size < 300
      ));
    }
  }
});

var QHeader = Vue.extend({
  name: 'QHeader',

  mixins: [ CanRenderMixin ],

  inject: {
    layout: {
      default () {
        console.error('QHeader needs to be child of QLayout');
      }
    }
  },

  props: {
    value: {
      type: Boolean,
      default: true
    },
    reveal: Boolean,
    revealOffset: {
      type: Number,
      default: 250
    },
    bordered: Boolean,
    elevated: Boolean
  },

  data () {
    return {
      size: 0,
      revealed: true
    }
  },

  watch: {
    value (val) {
      this.__update('space', val);
      this.__updateLocal('revealed', true);
      this.layout.__animate();
    },

    offset (val) {
      this.__update('offset', val);
    },

    reveal (val) {
      val === false && this.__updateLocal('revealed', this.value);
    },

    revealed (val) {
      this.layout.__animate();
      this.$emit('reveal', val);
    },

    'layout.scroll' (scroll) {
      this.reveal === true && this.__updateLocal('revealed',
        scroll.direction === 'up' ||
        scroll.position <= this.revealOffset ||
        scroll.position - scroll.inflexionPosition < 100
      );
    }
  },

  computed: {
    fixed () {
      return this.reveal === true ||
        this.layout.view.indexOf('H') > -1 ||
        this.layout.container === true
    },

    offset () {
      if (this.canRender !== true || this.value !== true) {
        return 0
      }
      if (this.fixed === true) {
        return this.revealed === true ? this.size : 0
      }
      const offset = this.size - this.layout.scroll.position;
      return offset > 0 ? offset : 0
    },

    classes () {
      return (
        this.fixed === true ? 'fixed' : 'absolute') + '-top' +
        (this.bordered === true ? ' q-header--bordered' : '') +
        (
          this.canRender !== true || this.value !== true || (this.fixed === true && this.revealed !== true)
            ? ' q-header--hidden'
            : ''
        )
    },

    style () {
      const
        view = this.layout.rows.top,
        css = {};

      if (view[0] === 'l' && this.layout.left.space === true) {
        css[this.$q.lang.rtl ? 'right' : 'left'] = `${this.layout.left.size}px`;
      }
      if (view[2] === 'r' && this.layout.right.space === true) {
        css[this.$q.lang.rtl ? 'left' : 'right'] = `${this.layout.right.size}px`;
      }

      return css
    }
  },

  render (h) {
    const child = [
      h(QResizeObserver, {
        props: { debounce: 0 },
        on: { resize: this.__onResize }
      })
    ].concat(
      slot(this, 'default')
    );

    this.elevated === true && child.push(
      h('div', {
        staticClass: 'q-layout__shadow absolute-full overflow-hidden no-pointer-events'
      })
    );

    return h('header', {
      staticClass: 'q-header q-layout__section--marginal q-layout__section--animate',
      class: this.classes,
      style: this.style,
      on: {
        ...this.$listeners,
        input: stop
      }
    }, child)
  },

  created () {
    this.layout.instances.header = this;
    this.__update('space', this.value);
    this.__update('offset', this.offset);
  },

  beforeDestroy () {
    if (this.layout.instances.header === this) {
      this.layout.instances.header = void 0;
      this.__update('size', 0);
      this.__update('offset', 0);
      this.__update('space', false);
    }
  },

  methods: {
    __onResize ({ height }) {
      this.__updateLocal('size', height);
      this.__update('size', height);
    },

    __update (prop, val) {
      if (this.layout.header[prop] !== val) {
        this.layout.header[prop] = val;
      }
    },

    __updateLocal (prop, val) {
      if (this[prop] !== val) {
        this[prop] = val;
      }
    }
  }
});

var QPage = Vue.extend({
  name: 'QPage',

  inject: {
    pageContainer: {
      default () {
        console.error('QPage needs to be child of QPageContainer');
      }
    },
    layout: {}
  },

  props: {
    padding: Boolean,
    styleFn: Function
  },

  computed: {
    style () {
      const offset =
        (this.layout.header.space === true ? this.layout.header.size : 0) +
        (this.layout.footer.space === true ? this.layout.footer.size : 0);

      if (typeof this.styleFn === 'function') {
        return this.styleFn(offset)
      }

      const minHeight = this.layout.container === true
        ? (this.layout.containerHeight - offset) + 'px'
        : (offset !== 0 ? `calc(100vh - ${offset}px)` : `100vh`);

      return { minHeight }
    },

    classes () {
      if (this.padding === true) {
        return 'q-layout-padding'
      }
    }
  },

  render (h) {
    return h('main', {
      staticClass: 'q-page',
      style: this.style,
      class: this.classes,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

var QPageContainer = Vue.extend({
  name: 'QPageContainer',

  inject: {
    layout: {
      default () {
        console.error('QPageContainer needs to be child of QLayout');
      }
    }
  },

  provide: {
    pageContainer: true
  },

  computed: {
    style () {
      const css = {};

      if (this.layout.header.space === true) {
        css.paddingTop = `${this.layout.header.size}px`;
      }
      if (this.layout.right.space === true) {
        css[`padding${this.$q.lang.rtl === true ? 'Left' : 'Right'}`] = `${this.layout.right.size}px`;
      }
      if (this.layout.footer.space === true) {
        css.paddingBottom = `${this.layout.footer.size}px`;
      }
      if (this.layout.left.space === true) {
        css[`padding${this.$q.lang.rtl === true ? 'Right' : 'Left'}`] = `${this.layout.left.size}px`;
      }

      return css
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-page-container q-layout__section--animate',
      style: this.style,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

var QPageSticky = Vue.extend({
  name: 'QPageSticky',

  inject: {
    layout: {
      default () {
        console.error('QPageSticky needs to be child of QLayout');
      }
    }
  },

  props: {
    position: {
      type: String,
      default: 'bottom-right',
      validator: v => [
        'top-right', 'top-left',
        'bottom-right', 'bottom-left',
        'top', 'right', 'bottom', 'left'
      ].includes(v)
    },
    offset: {
      type: Array,
      validator: v => v.length === 2
    },
    expand: Boolean
  },

  computed: {
    attach () {
      const pos = this.position;

      return {
        top: pos.indexOf('top') > -1,
        right: pos.indexOf('right') > -1,
        bottom: pos.indexOf('bottom') > -1,
        left: pos.indexOf('left') > -1,
        vertical: pos === 'top' || pos === 'bottom',
        horizontal: pos === 'left' || pos === 'right'
      }
    },

    top () {
      return this.layout.header.offset
    },

    right () {
      return this.layout.right.offset
    },

    bottom () {
      return this.layout.footer.offset
    },

    left () {
      return this.layout.left.offset
    },

    style () {
      let
        posX = 0,
        posY = 0;

      const
        attach = this.attach,
        dir = this.$q.lang.rtl === true ? -1 : 1;

      if (attach.top === true && this.top !== 0) {
        posY = `${this.top}px`;
      }
      else if (attach.bottom === true && this.bottom !== 0) {
        posY = `${-this.bottom}px`;
      }

      if (attach.left === true && this.left !== 0) {
        posX = `${dir * this.left}px`;
      }
      else if (attach.right === true && this.right !== 0) {
        posX = `${-dir * this.right}px`;
      }

      const css = { transform: `translate3d(${posX}, ${posY}, 0)` };

      if (this.offset) {
        css.margin = `${this.offset[1]}px ${this.offset[0]}px`;
      }

      if (attach.vertical === true) {
        if (this.left !== 0) {
          css[this.$q.lang.rtl === true ? 'right' : 'left'] = `${this.left}px`;
        }
        if (this.right !== 0) {
          css[this.$q.lang.rtl === true ? 'left' : 'right'] = `${this.right}px`;
        }
      }
      else if (attach.horizontal === true) {
        if (this.top !== 0) {
          css.top = `${this.top}px`;
        }
        if (this.bottom !== 0) {
          css.bottom = `${this.bottom}px`;
        }
      }

      return css
    },

    classes () {
      return `fixed-${this.position} q-page-sticky--${this.expand === true ? 'expand' : 'shrink'}`
    }
  },

  render (h) {
    const content = slot(this, 'default');

    return h('div', {
      staticClass: 'q-page-sticky q-layout__section--animate row flex-center',
      class: this.classes,
      style: this.style
    },
    this.expand === true
      ? content
      : [ h('div', content) ]
    )
  }
});

var QItemLabel = Vue.extend({
  name: 'QItemLabel',

  props: {
    overline: Boolean,
    caption: Boolean,
    header: Boolean,
    lines: [Number, String]
  },

  computed: {
    classes () {
      return {
        'q-item__label--overline text-overline': this.overline,
        'q-item__label--caption text-caption': this.caption,
        'q-item__label--header': this.header,
        'ellipsis': parseInt(this.lines, 10) === 1
      }
    },

    style () {
      if (this.lines !== void 0 && parseInt(this.lines, 10) > 1) {
        return {
          overflow: 'hidden',
          display: '-webkit-box',
          '-webkit-box-orient': 'vertical',
          '-webkit-line-clamp': this.lines
        }
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-item__label',
      style: this.style,
      class: this.classes,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

var QSlideTransition = Vue.extend({
  name: 'QSlideTransition',

  props: {
    appear: Boolean,
    duration: {
      type: Number,
      default: 300
    }
  },

  methods: {
    __begin (el, height, done) {
      el.style.overflowY = 'hidden';
      if (height !== void 0) {
        el.style.height = `${height}px`;
      }
      el.style.transition = `height ${this.duration}ms cubic-bezier(.25, .8, .50, 1)`;

      this.animating = true;
      this.done = done;
    },

    __end (el, event) {
      el.style.overflowY = null;
      el.style.height = null;
      el.style.transition = null;
      this.__cleanup();
      event !== this.lastEvent && this.$emit(event);
    },

    __cleanup () {
      this.done && this.done();
      this.done = null;
      this.animating = false;

      clearTimeout(this.timer);
      this.el.removeEventListener('transitionend', this.animListener);
      this.animListener = null;
    }
  },

  beforeDestroy () {
    this.animating && this.__cleanup();
  },

  render (h) {
    return h('transition', {
      props: {
        css: false,
        appear: this.appear
      },
      on: {
        enter: (el, done) => {
          let pos = 0;
          this.el = el;

          if (this.animating === true) {
            this.__cleanup();
            pos = el.offsetHeight === el.scrollHeight ? 0 : void 0;
          }
          else {
            this.lastEvent = 'hide';
          }

          this.__begin(el, pos, done);

          this.timer = setTimeout(() => {
            el.style.height = `${el.scrollHeight}px`;
            this.animListener = () => {
              this.__end(el, 'show');
            };
            el.addEventListener('transitionend', this.animListener);
          }, 100);
        },
        leave: (el, done) => {
          let pos;
          this.el = el;

          if (this.animating === true) {
            this.__cleanup();
          }
          else {
            this.lastEvent = 'show';
            pos = el.scrollHeight;
          }

          this.__begin(el, pos, done);

          this.timer = setTimeout(() => {
            el.style.height = 0;
            this.animListener = () => {
              this.__end(el, 'hide');
            };
            el.addEventListener('transitionend', this.animListener);
          }, 100);
        }
      }
    }, slot(this, 'default'))
  }
});

var QSeparator = Vue.extend({
  name: 'QSeparator',

  props: {
    dark: Boolean,
    spaced: Boolean,
    inset: [Boolean, String],
    vertical: Boolean,
    color: String
  },

  computed: {
    classes () {
      return {
        [`bg-${this.color}`]: this.color,
        'q-separator--dark': this.dark,
        'q-separator--spaced': this.spaced,
        'q-separator--inset': this.inset === true,
        'q-separator--item-inset': this.inset === 'item',
        'q-separator--item-thumbnail-inset': this.inset === 'item-thumbnail',
        [`q-separator--${this.vertical ? 'vertical self-stretch' : 'horizontal col-grow'}`]: true
      }
    }
  },

  render (h) {
    return h('hr', {
      staticClass: 'q-separator',
      class: this.classes
    })
  }
});

const eventName = 'q:expansion-item:close';

Vue.extend({
  name: 'QExpansionItem',

  mixins: [ RouterLinkMixin, ModelToggleMixin ],

  props: {
    icon: String,

    label: String,
    labelLines: [ Number, String ],

    caption: String,
    captionLines: [ Number, String ],

    dark: Boolean,
    dense: Boolean,

    expandIcon: String,
    expandIconClass: String,
    duration: Number,

    headerInsetLevel: Number,
    contentInsetLevel: Number,

    expandSeparator: Boolean,
    defaultOpened: Boolean,
    expandIconToggle: Boolean,
    switchToggleSide: Boolean,
    denseToggle: Boolean,
    group: String,
    popup: Boolean,

    headerStyle: [Array, String, Object],
    headerClass: [Array, String, Object]
  },

  watch: {
    showing (val) {
      if (val && this.group) {
        this.$root.$emit(eventName, this);
      }
    }
  },

  computed: {
    classes () {
      return `q-expansion-item--${this.showing === true ? 'expanded' : 'collapsed'}` +
        ` q-expansion-item--${this.popup === true ? 'popup' : 'standard'}`
    },

    contentStyle () {
      if (this.contentInsetLevel !== void 0) {
        return {
          paddingLeft: (this.contentInsetLevel * 56) + 'px'
        }
      }
    },

    isClickable () {
      return this.hasRouterLink === true || this.expandIconToggle !== true
    },

    expansionIcon () {
      return this.expandIcon || (this.denseToggle ? this.$q.iconSet.expansionItem.denseIcon : this.$q.iconSet.expansionItem.icon)
    },

    activeToggleIcon () {
      return this.disable !== true && (this.hasRouterLink === true || this.expandIconToggle === true)
    }
  },

  methods: {
    __onHeaderClick (e) {
      this.hasRouterLink !== true && this.toggle(e);
      this.$emit('click', e);
    },

    __toggleIconKeyboard (e) {
      e.keyCode === 13 && this.__toggleIcon(e, true);
    },

    __toggleIcon (e, keyboard) {
      keyboard !== true && this.$refs.blurTarget !== void 0 && this.$refs.blurTarget.focus();
      this.toggle(e);
      stopAndPrevent(e);
    },

    __eventHandler (comp) {
      if (this.group && this !== comp && comp.group === this.group) {
        this.hide();
      }
    },

    __getToggleIcon (h) {
      return h(QItemSection, {
        staticClass: `cursor-pointer${this.denseToggle === true && this.switchToggleSide === true ? ' items-end' : ''}`,
        class: this.expandIconClass,
        props: {
          side: this.switchToggleSide !== true,
          avatar: this.switchToggleSide
        },
        on: this.activeToggleIcon === true ? {
          click: this.__toggleIcon,
          keyup: this.__toggleIconKeyboard
        } : void 0
      }, [
        h(QIcon, {
          staticClass: 'q-expansion-item__toggle-icon q-focusable',
          class: {
            'rotate-180': this.showing,
            invisible: this.disable
          },
          props: {
            name: this.expansionIcon
          },
          attrs: this.activeToggleIcon === true
            ? { tabindex: 0 }
            : void 0
        }, [
          h('div', {
            staticClass: 'q-focus-helper q-focus-helper--round',
            attrs: { tabindex: -1 },
            ref: 'blurTarget'
          })
        ])
      ])
    },

    __getHeader (h) {
      let child;

      if (this.$scopedSlots.header !== void 0) {
        child = [].concat(this.$scopedSlots.header());
      }
      else {
        child = [
          h(QItemSection, [
            h(QItemLabel, {
              props: { lines: this.labelLines }
            }, [ this.label || '' ]),

            this.caption
              ? h(QItemLabel, {
                props: { lines: this.captionLines, caption: true }
              }, [ this.caption ])
              : null
          ])
        ];

        this.icon && child[this.switchToggleSide === true ? 'push' : 'unshift'](
          h(QItemSection, {
            props: {
              side: this.switchToggleSide === true,
              avatar: this.switchToggleSide !== true
            }
          }, [
            h(QIcon, {
              props: { name: this.icon }
            })
          ])
        );
      }

      child[this.switchToggleSide === true ? 'unshift' : 'push'](this.__getToggleIcon(h));

      const data = {
        ref: 'item',
        style: this.headerStyle,
        class: this.headerClass,
        props: {
          dark: this.dark,
          disable: this.disable,
          dense: this.dense,
          insetLevel: this.headerInsetLevel
        }
      };

      if (this.isClickable === true) {
        const evtProp = this.hasRouterLink === true ? 'nativeOn' : 'on';

        data.props.clickable = true;
        data[evtProp] = {
          ...this.$listeners,
          click: this.__onHeaderClick
        };

        this.hasRouterLink === true && Object.assign(
          data.props,
          this.routerLinkProps
        );
      }

      return h(QItem, data, child)
    },

    __getContent (h) {
      const node = [
        this.__getHeader(h),

        h(QSlideTransition, {
          props: { duration: this.duration }
        }, [
          h('div', {
            staticClass: 'q-expansion-item__content relative-position',
            style: this.contentStyle,
            directives: [{ name: 'show', value: this.showing }]
          }, slot(this, 'default'))
        ])
      ];

      if (this.expandSeparator) {
        node.push(
          h(QSeparator, {
            staticClass: 'q-expansion-item__border q-expansion-item__border--top absolute-top',
            props: { dark: this.dark }
          }),
          h(QSeparator, {
            staticClass: 'q-expansion-item__border q-expansion-item__border--bottom absolute-bottom',
            props: { dark: this.dark }
          })
        );
      }

      return node
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-expansion-item q-item-type',
      class: this.classes
    }, [
      h(
        'div',
        { staticClass: 'q-expansion-item__container relative-position' },
        this.__getContent(h)
      )
    ])
  },

  created () {
    this.$root.$on(eventName, this.__eventHandler);

    if (this.value === true) {
      this.showing = true;
    }
    else if (this.defaultOpened === true) {
      this.$emit('input', true);
      this.showing = true;
    }
  },

  beforeDestroy () {
    this.$root.$off(eventName, this.__eventHandler);
  }
});

Vue.extend({
  name: 'QSlideItem',

  props: {
    leftColor: String,
    rightColor: String
  },

  directives: {
    TouchPan
  },

  methods: {
    reset () {
      this.$refs.content.style.transform = `translate3d(0,0,0)`;
    },

    __pan (evt) {
      const node = this.$refs.content;

      if (evt.isFirst) {
        this.__dir = null;
        this.__size = { left: 0, right: 0 };
        this.__scale = 0;
        node.classList.add('no-transition');

        if (this.$scopedSlots.left !== void 0) {
          const slot = this.$refs.leftContent;
          slot.style.transform = `scale3d(1,1,1)`;
          this.__size.left = slot.getBoundingClientRect().width;
        }

        if (this.$scopedSlots.right !== void 0) {
          const slot = this.$refs.rightContent;
          slot.style.transform = `scale3d(1,1,1)`;
          this.__size.right = slot.getBoundingClientRect().width;
        }
      }
      else if (evt.isFinal) {
        node.classList.remove('no-transition');

        if (this.__scale === 1) {
          node.style.transform = `translate3d(${this.__dir * 100}%,0,0)`;
          this.timer = setTimeout(() => {
            this.$emit(this.__showing, { reset: this.reset });
            this.$emit('action', { side: this.__showing, reset: this.reset });
          }, 230);
        }
        else {
          node.style.transform = `translate3d(0,0,0)`;
        }

        return
      }

      if (
        (this.$scopedSlots.left === void 0 && evt.direction === 'right') ||
        (this.$scopedSlots.right === void 0 && evt.direction === 'left')
      ) {
        node.style.transform = `translate3d(0,0,0)`;
        return
      }

      const
        dir = evt.direction === 'left' ? -1 : 1,
        showing = dir * (this.$q.lang.rtl === true ? -1 : 1) === 1 ? 'left' : 'right',
        otherDir = showing === 'left' ? 'right' : 'left',
        dist = evt.distance.x,
        scale = Math.max(0, Math.min(1, (dist - 40) / this.__size[showing])),
        content = this.$refs[`${showing}Content`];

      if (this.__dir !== dir) {
        this.$refs[otherDir] !== void 0 && (this.$refs[otherDir].style.visibility = 'hidden');
        this.$refs[showing] !== void 0 && (this.$refs[showing].style.visibility = 'visible');
        this.__showing = showing;
        this.__dir = dir;
      }

      this.__scale = scale;
      node.style.transform = `translate3d(${dist * dir}px,0,0)`;

      if (dir === 1) {
        content.style.transform = `scale3d(${scale},${scale},1)`;
      }
      else {
        content.style.transform = `scale3d(${scale},${scale},1)`;
      }
    }
  },

  render (h) {
    let
      content = [],
      left = this.$scopedSlots.left !== void 0,
      right = this.$scopedSlots.right !== void 0;

    if (left) {
      content.push(
        h('div', {
          ref: 'left',
          staticClass: 'q-slide-item__left absolute-full row no-wrap items-center justify-start',
          class: this.leftColor ? `bg-${this.leftColor}` : ''
        }, [
          h('div', { ref: 'leftContent' }, slot(this, 'left'))
        ])
      );
    }

    if (right) {
      content.push(
        h('div', {
          ref: 'right',
          staticClass: 'q-slide-item__right absolute-full row no-wrap items-center justify-end',
          class: this.rightColor ? `bg-${this.rightColor}` : ''
        }, [
          h('div', { ref: 'rightContent' }, slot(this, 'right'))
        ])
      );
    }

    content.push(
      h('div', {
        ref: 'content',
        staticClass: 'q-slide-item__content',
        directives: left || right ? [{
          name: 'touch-pan',
          value: this.__pan,
          modifiers: {
            horizontal: true,
            mouse: true,
            mouseAllDir: true
          }
        }] : null
      }, slot(this, 'default'))
    );

    return h('div', {
      staticClass: 'q-slide-item q-item-type overflow-hidden'
    }, content)
  },

  beforeDestroy () {
    clearTimeout(this.timer);
  }
});

Vue.extend({
  name: 'QNoSsr',

  mixins: [ CanRenderMixin ],

  props: {
    tag: {
      type: String,
      default: 'div'
    },
    placeholder: String
  },

  render (h) {
    if (this.canRender === true) {
      const node = slot(this, 'default');
      return node === void 0
        ? node
        : (node.length > 1 ? h(this.tag, node) : node[0])
    }

    if (this.$scopedSlots.placeholder !== void 0) {
      const node = slot(this, 'placeholder');
      return node === void 0
        ? node
        : (
          node.length > 1
            ? h(this.tag, { staticClass: 'q-no-ssr-placeholder' }, node)
            : node[0]
        )
    }

    if (this.placeholder !== void 0) {
      return h(this.tag, { staticClass: 'q-no-ssr-placeholder' }, [
        this.placeholder
      ])
    }
  }
});

var QRadio = Vue.extend({
  name: 'QRadio',

  props: {
    value: {
      required: true
    },
    val: {
      required: true
    },

    label: String,
    leftLabel: Boolean,

    color: String,
    keepColor: Boolean,
    dark: Boolean,
    dense: Boolean,

    disable: Boolean,
    tabindex: [String, Number]
  },

  computed: {
    isTrue () {
      return this.value === this.val
    },

    classes () {
      return {
        'disabled': this.disable,
        'q-radio--dark': this.dark,
        'q-radio--dense': this.dense,
        'reverse': this.leftLabel
      }
    },

    innerClass () {
      if (this.isTrue === true) {
        return 'q-radio__inner--active' +
          (this.color !== void 0 ? ' text-' + this.color : '')
      }
      else if (this.keepColor === true && this.color !== void 0) {
        return 'text-' + this.color
      }
    },

    computedTabindex () {
      return this.disable === true ? -1 : this.tabindex || 0
    }
  },

  methods: {
    set (e) {
      e !== void 0 && stopAndPrevent(e);
      if (this.disable !== true && this.isTrue !== true) {
        this.$emit('input', this.val);
      }
    },

    __keyDown (e) {
      if (e.keyCode === 13 || e.keyCode === 32) {
        this.set(e);
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-radio cursor-pointer no-outline row inline no-wrap items-center',
      class: this.classes,
      attrs: { tabindex: this.computedTabindex },
      on: {
        click: this.set,
        keydown: this.__keyDown
      }
    }, [
      h('div', {
        staticClass: 'q-radio__inner relative-position',
        class: this.innerClass
      }, [
        this.disable !== true
          ? h('input', {
            staticClass: 'q-radio__native q-ma-none q-pa-none invisible',
            attrs: { type: 'checkbox' },
            on: { change: this.set }
          })
          : null,

        h('div', {
          staticClass: 'q-radio__bg absolute'
        }, [
          h('div', { staticClass: 'q-radio__outer-circle absolute-full' }),
          h('div', { staticClass: 'q-radio__inner-circle absolute-full' })
        ])
      ]),

      this.label !== void 0 || this.$scopedSlots.default !== void 0
        ? h('div', {
          staticClass: 'q-radio__label q-anchor--skip'
        }, (this.label !== void 0 ? [ this.label ] : []).concat(slot(this, 'default')))
        : null
    ])
  }
});

var QToggle = Vue.extend({
  name: 'QToggle',

  mixins: [ CheckboxMixin ],

  props: {
    icon: String,
    checkedIcon: String,
    uncheckedIcon: String
  },

  computed: {
    classes () {
      return {
        'disabled': this.disable,
        'q-toggle--dark': this.dark,
        'q-toggle--dense': this.dense,
        'reverse': this.leftLabel
      }
    },

    innerClass () {
      if (this.isTrue === true) {
        return 'q-toggle__inner--active' +
          (this.color !== void 0 ? ' text-' + this.color : '')
      }
      else if (this.keepColor === true && this.color !== void 0) {
        return 'text-' + this.color
      }
    },

    computedIcon () {
      return (this.isTrue === true ? this.checkedIcon : this.uncheckedIcon) || this.icon
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-toggle cursor-pointer no-outline row inline no-wrap items-center',
      class: this.classes,
      attrs: { tabindex: this.computedTabindex },
      on: {
        click: this.toggle,
        keydown: this.__keyDown
      }
    }, [
      h('div', {
        staticClass: 'q-toggle__inner relative-position',
        class: this.innerClass
      }, [
        this.disable !== true
          ? h('input', {
            staticClass: 'q-toggle__native absolute q-ma-none q-pa-none invisible',
            attrs: { type: 'toggle' },
            on: { change: this.toggle }
          })
          : null,

        h('div', { staticClass: 'q-toggle__track' }),
        h('div', { staticClass: 'q-toggle__thumb-container absolute' }, [
          h('div', {
            staticClass: 'q-toggle__thumb row flex-center'
          }, this.computedIcon !== void 0
            ? [ h(QIcon, { props: { name: this.computedIcon } }) ]
            : null
          )
        ])
      ]),

      h('div', {
        staticClass: 'q-toggle__label q-anchor--skip'
      }, (this.label !== void 0 ? [ this.label ] : []).concat(slot(this, 'default')))
    ])
  }
});

const components = {
  radio: QRadio,
  checkbox: QCheckbox,
  toggle: QToggle
};

var QOptionGroup = Vue.extend({
  name: 'QOptionGroup',

  props: {
    value: {
      required: true
    },
    options: {
      type: Array,
      validator (opts) {
        return opts.every(opt => 'value' in opt && 'label' in opt)
      }
    },

    type: {
      default: 'radio',
      validator: v => ['radio', 'checkbox', 'toggle'].includes(v)
    },

    color: String,
    keepColor: Boolean,
    dark: Boolean,
    dense: Boolean,

    leftLabel: Boolean,
    inline: Boolean,
    disable: Boolean
  },

  computed: {
    component () {
      return components[this.type]
    },

    model () {
      return Array.isArray(this.value) ? this.value.slice() : this.value
    }
  },

  methods: {
    __update (value) {
      this.$emit('input', value);
    }
  },

  created () {
    const isArray = Array.isArray(this.value);

    if (this.type === 'radio') {
      if (isArray) {
        console.error('q-option-group: model should not be array');
      }
    }
    else if (!isArray) {
      console.error('q-option-group: model should be array in your case');
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-option-group q-gutter-x-sm',
      class: this.inline ? 'q-option-group--inline' : null
    }, this.options.map(opt => h('div', [
      h(this.component, {
        props: {
          value: this.value,
          val: opt.value,
          disable: this.disable || opt.disable,
          label: opt.label,
          leftLabel: this.leftLabel || opt.leftLabel,
          color: opt.color || this.color,
          checkedIcon: opt.checkedIcon,
          uncheckedIcon: opt.uncheckedIcon,
          dark: opt.dark || this.dark,
          dense: this.dense,
          keepColor: opt.keepColor || this.keepColor
        },
        on: {
          input: this.__update
        }
      })
    ])))
  }
});

var QPageScroller = Vue.extend({
  name: 'QPageScroller',

  mixins: [ QPageSticky ],

  props: {
    scrollOffset: {
      type: Number,
      default: 1000
    },

    duration: {
      type: Number,
      default: 300
    },

    offset: {
      default: () => [18, 18]
    }
  },

  inject: {
    layout: {
      default () {
        console.error('QPageScroller needs to be used within a QLayout');
      }
    }
  },

  data () {
    return {
      showing: this.__isVisible(this.layout.scroll.position)
    }
  },

  watch: {
    'layout.scroll.position' (val) {
      const newVal = this.__isVisible(val);
      if (this.showing !== newVal) {
        this.showing = newVal;
      }
    }
  },

  methods: {
    __isVisible (val) {
      return val > this.scrollOffset
    },

    __onClick (e) {
      const target = this.layout.container === true
        ? getScrollTarget(this.$el)
        : getScrollTarget(this.layout.$el);

      setScrollPosition(target, 0, this.duration);
      this.$listeners.click !== void 0 && this.$emit('click', e);
    }
  },

  render (h) {
    return h('transition', {
      props: { name: 'q-transition--fade' }
    },
    this.showing === true
      ? [
        h('div', {
          staticClass: 'q-page-scroller',
          on: {
            ...this.$listeners,
            click: this.__onClick
          }
        }, [
          QPageSticky.options.render.call(this, h)
        ])
      ]
      : null
    )
  }
});

Vue.extend({
  name: 'QPagination',

  props: {
    value: {
      type: Number,
      required: true
    },
    min: {
      type: Number,
      default: 1
    },
    max: {
      type: Number,
      required: true
    },

    color: {
      type: String,
      default: 'primary'
    },
    textColor: String,

    size: String,

    disable: Boolean,

    input: Boolean,
    boundaryLinks: {
      type: Boolean,
      default: null
    },
    boundaryNumbers: {
      type: Boolean,
      default: null
    },
    directionLinks: {
      type: Boolean,
      default: null
    },
    ellipses: {
      type: Boolean,
      default: null
    },
    maxPages: {
      type: Number,
      default: 0,
      validator: v => v >= 0
    }
  },

  data () {
    return {
      newPage: null
    }
  },

  watch: {
    min (value) {
      this.model = this.value;
    },

    max (value) {
      this.model = this.value;
    }
  },

  computed: {
    model: {
      get () {
        return this.value
      },
      set (val) {
        if (this.disable || !val || isNaN(val)) {
          return
        }
        const value = between(parseInt(val, 10), this.min, this.max);
        this.$emit('input', value);
      }
    },

    inputPlaceholder () {
      return this.model + ' / ' + this.max
    },

    __boundaryLinks () {
      return this.__getBool(this.boundaryLinks, this.input)
    },

    __boundaryNumbers () {
      return this.__getBool(this.boundaryNumbers, !this.input)
    },

    __directionLinks () {
      return this.__getBool(this.directionLinks, this.input)
    },

    __ellipses () {
      return this.__getBool(this.ellipses, !this.input)
    },

    icons () {
      const ico = [
        this.$q.iconSet.pagination.first,
        this.$q.iconSet.pagination.prev,
        this.$q.iconSet.pagination.next,
        this.$q.iconSet.pagination.last
      ];
      return this.$q.lang.rtl ? ico.reverse() : ico
    }
  },

  methods: {
    set (value) {
      this.model = value;
    },

    setByOffset (offset) {
      this.model = this.model + offset;
    },

    __update () {
      this.model = this.newPage;
      this.newPage = null;
    },

    __getBool (val, otherwise) {
      return [true, false].includes(val)
        ? val
        : otherwise
    },

    __getBtn (h, data, props) {
      data.props = {
        color: this.color,
        flat: true,
        size: this.size,
        ...props
      };
      return h(QBtn, data)
    }
  },

  render (h) {
    const
      contentStart = [],
      contentEnd = [],
      contentMiddle = [];

    if (this.__boundaryLinks) {
      contentStart.push(this.__getBtn(h, {
        key: 'bls',
        on: {
          click: () => this.set(this.min)
        }
      }, {
        disable: this.disable || this.value <= this.min,
        icon: this.icons[0]
      }));
      contentEnd.unshift(this.__getBtn(h, {
        key: 'ble',
        on: {
          click: () => this.set(this.max)
        }
      }, {
        disable: this.disable || this.value >= this.max,
        icon: this.icons[3]
      }));
    }

    if (this.__directionLinks) {
      contentStart.push(this.__getBtn(h, {
        key: 'bdp',
        on: {
          click: () => this.setByOffset(-1)
        }
      }, {
        disable: this.disable || this.value <= this.min,
        icon: this.icons[1]
      }));
      contentEnd.unshift(this.__getBtn(h, {
        key: 'bdn',
        on: {
          click: () => this.setByOffset(1)
        }
      }, {
        disable: this.disable || this.value >= this.max,
        icon: this.icons[2]
      }));
    }

    if (this.input === true) {
      contentMiddle.push(h(QInput, {
        staticClass: 'inline',
        style: {
          width: `${this.inputPlaceholder.length / 2}em`
        },
        props: {
          type: 'number',
          dense: true,
          value: this.newPage,
          color: this.color,
          disable: this.disable,
          borderless: true
        },
        attrs: {
          placeholder: this.inputPlaceholder,
          min: this.min,
          max: this.max
        },
        on: {
          input: value => (this.newPage = value),
          keyup: e => (e.keyCode === 13 && this.__update()),
          blur: () => this.__update()
        }
      }));
    }
    else { // is type select
      let
        maxPages = Math.max(
          this.maxPages,
          1 + (this.__ellipses ? 2 : 0) + (this.__boundaryNumbers ? 2 : 0)
        ),
        pgFrom = this.min,
        pgTo = this.max,
        ellipsesStart = false,
        ellipsesEnd = false,
        boundaryStart = false,
        boundaryEnd = false;

      if (this.maxPages && maxPages < (this.max - this.min + 1)) {
        maxPages = 1 + Math.floor(maxPages / 2) * 2;
        pgFrom = Math.max(this.min, Math.min(this.max - maxPages + 1, this.value - Math.floor(maxPages / 2)));
        pgTo = Math.min(this.max, pgFrom + maxPages - 1);
        if (this.__boundaryNumbers) {
          boundaryStart = true;
          pgFrom += 1;
        }
        if (this.__ellipses && pgFrom > (this.min + (this.__boundaryNumbers ? 1 : 0))) {
          ellipsesStart = true;
          pgFrom += 1;
        }
        if (this.__boundaryNumbers) {
          boundaryEnd = true;
          pgTo -= 1;
        }
        if (this.__ellipses && pgTo < (this.max - (this.__boundaryNumbers ? 1 : 0))) {
          ellipsesEnd = true;
          pgTo -= 1;
        }
      }
      const style = {
        minWidth: `${Math.max(2, String(this.max).length)}em`
      };
      if (boundaryStart) {
        const active = this.min === this.value;
        contentStart.push(this.__getBtn(h, {
          key: 'bns',
          style,
          on: {
            click: () => this.set(this.min)
          }
        }, {
          disable: this.disable,
          flat: !active,
          textColor: active ? this.textColor : null,
          label: this.min,
          ripple: false
        }));
      }
      if (boundaryEnd) {
        const active = this.max === this.value;
        contentEnd.unshift(this.__getBtn(h, {
          key: 'bne',
          style,
          on: {
            click: () => this.set(this.max)
          }
        }, {
          disable: this.disable,
          flat: !active,
          textColor: active ? this.textColor : null,
          label: this.max,
          ripple: false
        }));
      }
      if (ellipsesStart) {
        contentStart.push(this.__getBtn(h, {
          key: 'bes',
          style,
          on: {
            click: () => this.set(pgFrom - 1)
          }
        }, {
          disable: this.disable,
          label: '…'
        }));
      }
      if (ellipsesEnd) {
        contentEnd.unshift(this.__getBtn(h, {
          key: 'bee',
          style,
          on: {
            click: () => this.set(pgTo + 1)
          }
        }, {
          disable: this.disable,
          label: '…'
        }));
      }
      for (let i = pgFrom; i <= pgTo; i++) {
        const active = i === this.value;
        contentMiddle.push(this.__getBtn(h, {
          key: `bpg${i}`,
          style,
          on: {
            click: () => this.set(i)
          }
        }, {
          disable: this.disable,
          flat: !active,
          textColor: active ? this.textColor : null,
          label: i,
          ripple: false
        }));
      }
    }

    return h('div', {
      staticClass: 'q-pagination row no-wrap items-center',
      class: { disabled: this.disable },
      on: this.$listeners
    }, [
      contentStart,

      h('div', {
        staticClass: 'row justify-center',
        on: this.input === true
          ? { input: stop }
          : {}
      }, [
        contentMiddle
      ]),

      contentEnd
    ])
  }
});

function frameDebounce (fn) {
  let wait = false, frame;

  function debounced (...args) {
    if (wait) { return }

    wait = true;
    frame = requestAnimationFrame(() => {
      fn.apply(this, args);
      wait = false;
    });
  }

  debounced.cancel = () => {
    window.cancelAnimationFrame(frame);
    wait = false;
  };

  return debounced
}

Vue.extend({
  name: 'QParallax',

  props: {
    src: String,
    height: {
      type: Number,
      default: 500
    },
    speed: {
      type: Number,
      default: 1,
      validator: v => v >= 0 && v <= 1
    }
  },

  data () {
    return {
      scrolling: false,
      percentScrolled: 0
    }
  },

  watch: {
    height () {
      this.__updatePos();
    }
  },

  methods: {
    __update (percentage) {
      this.percentScrolled = percentage;
      this.$listeners.scroll !== void 0 && this.$emit('scroll', percentage);
    },

    __onResize () {
      if (this.scrollTarget) {
        this.mediaHeight = this.media.naturalHeight || this.media.videoHeight || height(this.media);
        this.__updatePos();
      }
    },

    __updatePos () {
      let containerTop, containerHeight, containerBottom, top, bottom;

      if (this.scrollTarget === window) {
        containerTop = 0;
        containerHeight = window.innerHeight;
        containerBottom = containerHeight;
      }
      else {
        containerTop = offset(this.scrollTarget).top;
        containerHeight = height(this.scrollTarget);
        containerBottom = containerTop + containerHeight;
      }

      top = offset(this.$el).top;
      bottom = top + this.height;

      if (bottom > containerTop && top < containerBottom) {
        const percent = (containerBottom - top) / (this.height + containerHeight);
        this.__setPos((this.mediaHeight - this.height) * percent * this.speed);
        this.__update(percent);
      }
    },

    __setPos (offset) {
      // apply it immediately without any delay
      this.media.style.transform = `translate3D(-50%,${Math.round(offset)}px, 0)`;
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-parallax',
      style: { height: `${this.height}px` },
      on: this.$listeners
    }, [
      h('div', {
        ref: 'mediaParent',
        staticClass: 'q-parallax__media absolute-full'
      }, this.$scopedSlots.media !== void 0 ? this.$scopedSlots.media() : [
        h('img', {
          ref: 'media',
          attrs: {
            src: this.src
          }
        })
      ]),

      h(
        'div',
        { staticClass: 'q-parallax__content absolute-full column flex-center' },
        this.$scopedSlots.content !== void 0
          ? this.$scopedSlots.content({ percentScrolled: this.percentScrolled })
          : slot(this, 'default')
      )
    ])
  },

  beforeMount () {
    this.__setPos = frameDebounce(this.__setPos);
  },

  mounted () {
    this.__update = frameDebounce(this.__update);
    this.resizeHandler = frameDebounce(this.__onResize);

    this.media = this.$scopedSlots.media !== void 0
      ? this.$refs.mediaParent.children[0]
      : this.$refs.media;

    this.media.onload = this.media.onloadstart = this.media.loadedmetadata = this.__onResize;

    this.scrollTarget = getScrollTarget(this.$el);

    window.addEventListener('resize', this.resizeHandler, listenOpts.passive);
    this.scrollTarget.addEventListener('scroll', this.__updatePos, listenOpts.passive);

    this.__onResize();
  },

  beforeDestroy () {
    window.removeEventListener('resize', this.resizeHandler, listenOpts.passive);
    this.scrollTarget.removeEventListener('scroll', this.__updatePos, listenOpts.passive);
    this.media.onload = this.media.onloadstart = this.media.loadedmetadata = null;
  }
});

function clone (data) {
  const s = JSON.stringify(data);
  if (s) {
    return JSON.parse(s)
  }
}

Vue.extend({
  name: 'QPopupEdit',

  props: {
    value: {
      required: true
    },
    title: String,
    buttons: Boolean,
    labelSet: String,
    labelCancel: String,

    persistent: Boolean,
    color: {
      type: String,
      default: 'primary'
    },
    validate: {
      type: Function,
      default: () => true
    },

    contentClass: String,
    contentStyle: [String, Array, Object],

    disable: Boolean
  },

  data () {
    return {
      initialValue: ''
    }
  },

  computed: {
    classes () {
      return 'q-popup-edit' +
        (this.contentClass ? ' ' + this.contentClass : '')
    }
  },

  methods: {
    set () {
      if (this.__hasChanged()) {
        if (this.validate(this.value) === false) {
          return
        }
        this.$emit('save', this.value, this.initialValue);
      }
      this.__close();
    },

    cancel () {
      if (this.__hasChanged()) {
        this.$emit('cancel', this.value, this.initialValue);
        this.$emit('input', this.initialValue);
      }
      this.__close();
    },

    __hasChanged () {
      return !isDeepEqual(this.value, this.initialValue)
    },

    __close () {
      this.validated = true;
      this.$refs.menu.hide();
    },

    __reposition () {
      this.$nextTick(() => {
        this.$refs.menu.updatePosition();
      });
    },

    __getContent (h) {
      const
        child = [].concat(slot(this, 'default')),
        title = this.$scopedSlots.title !== void 0
          ? this.$scopedSlots.title()
          : this.title;

      title && child.unshift(
        h('div', { staticClass: 'q-dialog__title q-mt-sm q-mb-sm' }, [ title ])
      );

      this.buttons === true && child.push(
        h('div', { staticClass: 'q-popup-edit__buttons row justify-center no-wrap' }, [
          h(QBtn, {
            props: {
              flat: true,
              color: this.color,
              label: this.labelCancel || this.$q.lang.label.cancel
            },
            on: { click: this.cancel }
          }),
          h(QBtn, {
            props: {
              flat: true,
              color: this.color,
              label: this.labelSet || this.$q.lang.label.set
            },
            on: { click: this.set }
          })
        ])
      );

      return child
    }
  },

  render (h) {
    if (this.disable === true) { return }

    return h(QMenu, {
      ref: 'menu',
      props: {
        contentClass: this.classes,
        contentStyle: this.contentStyle,
        cover: true,
        persistent: this.persistent,
        noFocus: true
      },
      on: {
        show: () => {
          this.$emit('show');
          this.validated = false;
          this.initialValue = clone(this.value);
          this.watcher = this.$watch('value', this.__reposition);
        },
        'before-hide': () => {
          this.watcher();

          if (this.validated === false && this.__hasChanged()) {
            this.$emit('cancel', this.value, this.initialValue);
            this.$emit('input', this.initialValue);
          }
        },
        hide: () => {
          this.$emit('hide');
        },
        keyup: e => {
          e.keyCode === 13 && this.set();
        }
      }
    }, this.__getContent(h))
  }
});

Vue.extend({
  name: 'QPopupProxy',

  mixins: [ AnchorMixin ],

  props: {
    breakpoint: {
      type: [String, Number],
      default: 450
    }
  },

  data () {
    const breakpoint = parseInt(this.breakpoint, 10);
    return {
      type: this.$q.screen.width < breakpoint || this.$q.screen.height < breakpoint
        ? 'dialog'
        : 'menu'
    }
  },

  computed: {
    parsedBreakpoint () {
      return parseInt(this.breakpoint, 10)
    }
  },

  watch: {
    '$q.screen.width' (width) {
      if (this.$refs.popup.showing !== true) {
        this.__updateType(width, this.$q.screen.height, this.parsedBreakpoint);
      }
    },

    '$q.screen.height' (height) {
      if (this.$refs.popup.showing !== true) {
        this.__updateType(this.$q.screen.width, height, this.parsedBreakpoint);
      }
    },

    breakpoint (breakpoint) {
      if (this.$refs.popup.showing !== true) {
        this.__updateType(this.$q.screen.width, this.$q.screen.height, parseInt(breakpoint, 10));
      }
    }
  },

  methods: {
    toggle (evt) {
      this.$refs.popup.toggle(evt);
    },

    show (evt) {
      this.$refs.popup.show(evt);
    },

    hide (evt) {
      this.$refs.popup.hide(evt);
    },

    __onHide (evt) {
      this.__updateType(this.$q.screen.width, this.$q.screen.height, this.parsedBreakpoint);
      this.$emit('hide', evt);
    },

    __updateType (width, height, breakpoint) {
      const type = width < breakpoint || height < breakpoint
        ? 'dialog'
        : 'menu';

      if (this.type !== type) {
        this.type = type;
      }
    }
  },

  render (h) {
    const child = slot(this, 'default');

    let props = (
      this.type === 'menu' &&
      child !== void 0 &&
      child[0] !== void 0 &&
      child[0].componentOptions !== void 0 &&
      child[0].componentOptions.Ctor !== void 0 &&
      child[0].componentOptions.Ctor.sealedOptions !== void 0 &&
      ['QDate', 'QTime', 'QCarousel', 'QColor'].includes(
        child[0].componentOptions.Ctor.sealedOptions.name
      )
    ) ? { cover: true, maxHeight: '99vh' } : {};

    const data = {
      ref: 'popup',
      props: Object.assign(props, this.$attrs),
      on: {
        ...this.$listeners,
        hide: this.__onHide
      }
    };

    let component;

    if (this.type === 'dialog') {
      component = QDialog;
    }
    else {
      component = QMenu;
      data.props.contextMenu = this.contextMenu;
      data.props.noParentEvent = true;
    }

    return h(component, data, slot(this, 'default'))
  }
});

function width (val) {
  return { transform: `scale3d(${val},1,1)` }
}

var QLinearProgress = Vue.extend({
  name: 'QLinearProgress',

  props: {
    value: {
      type: Number,
      default: 0
    },
    buffer: Number,

    color: String,
    trackColor: String,
    dark: Boolean,

    reverse: Boolean,
    stripe: Boolean,
    indeterminate: Boolean,
    query: Boolean,
    rounded: Boolean
  },

  computed: {
    motion () {
      return this.indeterminate || this.query
    },

    classes () {
      return {
        [`text-${this.color}`]: this.color !== void 0,
        'q-linear-progress--reverse': this.reverse === true || this.query === true,
        'rounded-borders': this.rounded === true
      }
    },

    trackStyle () {
      return width(this.buffer !== void 0 ? this.buffer : 1)
    },

    trackClass () {
      return 'q-linear-progress__track--' + (this.dark === true ? 'dark' : 'light') +
        (this.trackColor !== void 0 ? ` bg-${this.trackColor}` : '')
    },

    modelStyle () {
      return width(this.motion ? 1 : this.value)
    },

    modelClasses () {
      return `q-linear-progress__model--${this.motion ? 'in' : ''}determinate`
    },

    stripeStyle () {
      return { width: (this.value * 100) + '%' }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-linear-progress',
      class: this.classes,
      on: this.$listeners
    }, [
      h('div', {
        staticClass: 'q-linear-progress__track absolute-full',
        style: this.trackStyle,
        class: this.trackClass
      }),

      h('div', {
        staticClass: 'q-linear-progress__model absolute-full',
        style: this.modelStyle,
        class: this.modelClasses
      }),

      this.stripe === true && this.motion === false ? h('div', {
        staticClass: 'q-linear-progress__stripe absolute-full',
        style: this.stripeStyle
      }) : null
    ].concat(slot(this, 'default')))
  }
});

const
  PULLER_HEIGHT = 40,
  OFFSET_TOP = 20;

Vue.extend({
  name: 'QPullToRefresh',

  directives: {
    TouchPan
  },

  props: {
    color: String,
    icon: String,
    noMouse: Boolean,
    disable: Boolean
  },

  data () {
    return {
      state: 'pull',
      pullRatio: 0,
      pulling: false,
      pullPosition: -PULLER_HEIGHT,
      animating: false,
      positionCSS: {}
    }
  },

  computed: {
    style () {
      return {
        opacity: this.pullRatio,
        transform: `translate3d(0, ${this.pullPosition}px, 0) rotate3d(0, 0, 1, ${this.pullRatio * 360}deg)`
      }
    }
  },

  methods: {
    trigger () {
      this.$emit('refresh', () => {
        this.__animateTo({ pos: -PULLER_HEIGHT, ratio: 0 }, () => {
          this.state = 'pull';
        });
      });
    },

    updateScrollTarget () {
      this.scrollContainer = getScrollTarget(this.$el);
    },

    __pull (event) {
      if (event.isFinal) {
        this.scrolling = false;

        if (this.pulling) {
          this.pulling = false;

          if (this.state === 'pulled') {
            this.state = 'refreshing';
            this.__animateTo({ pos: OFFSET_TOP });
            this.trigger();
          }
          else if (this.state === 'pull') {
            this.__animateTo({ pos: -PULLER_HEIGHT, ratio: 0 });
          }
        }

        return
      }

      if (this.animating || this.scrolling || this.state === 'refreshing') {
        return true
      }

      let top = getScrollPosition(this.scrollContainer);
      if (top !== 0 || (top === 0 && event.direction !== 'down')) {
        this.scrolling = true;

        if (this.pulling) {
          this.pulling = false;
          this.state = 'pull';
          this.__animateTo({ pos: -PULLER_HEIGHT, ratio: 0 });
        }

        return true
      }

      if (event.isFirst) {
        this.pulling = true;

        const { top, left } = this.$el.getBoundingClientRect();
        this.positionCSS = {
          top: top + 'px',
          left: left + 'px',
          width: window.getComputedStyle(this.$el).getPropertyValue('width')
        };
      }

      prevent(event.evt);

      const distance = Math.min(140, Math.max(0, event.distance.y));
      this.pullPosition = distance - PULLER_HEIGHT;
      this.pullRatio = between(distance / (OFFSET_TOP + PULLER_HEIGHT), 0, 1);

      const state = this.pullPosition > OFFSET_TOP ? 'pulled' : 'pull';
      if (this.state !== state) {
        this.state = state;
      }
    },

    __animateTo ({ pos, ratio }, done) {
      this.animating = true;
      this.pullPosition = pos;

      if (ratio !== void 0) {
        this.pullRatio = ratio;
      }

      clearTimeout(this.timer);
      this.timer = setTimeout(() => {
        this.animating = false;
        done && done();
      }, 300);
    }
  },

  mounted () {
    this.updateScrollTarget();
  },

  beforeDestroy () {
    clearTimeout(this.timer);
  },

  render (h) {
    return h('div', {
      staticClass: 'q-pull-to-refresh overflow-hidden',
      directives: this.disable
        ? null
        : [{
          name: 'touch-pan',
          modifiers: {
            vertical: true,
            mightPrevent: true,
            mouseMightPrevent: true,
            mouse: !this.noMouse
          },
          value: this.__pull
        }]
    }, [
      h('div', {
        staticClass: 'q-pull-to-refresh__content',
        class: this.pulling ? 'no-pointer-events' : null
      }, slot(this, 'default')),

      h('div', {
        staticClass: 'q-pull-to-refresh__puller-container fixed row flex-center no-pointer-events z-top',
        style: this.positionCSS
      }, [
        h('div', {
          staticClass: 'q-pull-to-refresh__puller row flex-center',
          style: this.style,
          class: this.animating ? 'q-pull-to-refresh__puller--animating' : null
        }, [
          this.state !== 'refreshing'
            ? h(QIcon, {
              props: {
                name: this.icon || this.$q.iconSet.pullToRefresh.icon,
                color: this.color,
                size: '32px'
              }
            })
            : h(QSpinner, {
              props: {
                size: '24px',
                color: this.color
              }
            })
        ])
      ])
    ])
  }
});

const dragType = {
  MIN: 0,
  RANGE: 1,
  MAX: 2
};

Vue.extend({
  name: 'QRange',

  mixins: [ SliderMixin ],

  props: {
    value: {
      type: Object,
      default: () => ({
        min: 0,
        max: 0
      }),
      validator (val) {
        return 'min' in val && 'max' in val
      }
    },

    dragRange: Boolean,
    dragOnlyRange: Boolean,

    leftLabelColor: String,
    rightLabelColor: String,

    leftLabelValue: [String, Number],
    rightLabelValue: [String, Number]
  },

  data () {
    return {
      model: { ...this.value },
      curMinRatio: 0,
      curMaxRatio: 0
    }
  },

  watch: {
    'value.min' (val) {
      this.model.min = val;
    },

    'value.max' (val) {
      this.model.max = val;
    },

    min (value) {
      if (this.model.min < value) {
        this.model.min = value;
      }
      if (this.model.max < value) {
        this.model.max = value;
      }
    },

    max (value) {
      if (this.model.min > value) {
        this.model.min = value;
      }
      if (this.model.max > value) {
        this.model.max = value;
      }
    }
  },

  computed: {
    ratioMin () {
      return this.active === true ? this.curMinRatio : this.modelMinRatio
    },

    ratioMax () {
      return this.active === true ? this.curMaxRatio : this.modelMaxRatio
    },

    modelMinRatio () {
      return (this.model.min - this.min) / (this.max - this.min)
    },

    modelMaxRatio () {
      return (this.model.max - this.min) / (this.max - this.min)
    },

    trackStyle () {
      return {
        [this.horizProp]: 100 * this.ratioMin + '%',
        width: 100 * (this.ratioMax - this.ratioMin) + '%'
      }
    },

    minThumbStyle () {
      return {
        [this.horizProp]: (100 * this.ratioMin) + '%',
        'z-index': this.__nextFocus === 'min' ? 2 : void 0
      }
    },

    maxThumbStyle () {
      return { [this.horizProp]: (100 * this.ratioMax) + '%' }
    },

    minThumbClass () {
      return this.preventFocus === false && this.focus === 'min' ? 'q-slider--focus' : null
    },

    maxThumbClass () {
      return this.preventFocus === false && this.focus === 'max' ? 'q-slider--focus' : null
    },

    events () {
      if (this.editable === true) {
        if (this.$q.platform.is.mobile === true) {
          return { click: this.__mobileClick }
        }

        const evt = { mousedown: this.__activate };

        this.dragOnlyRange === true && Object.assign(evt, {
          focus: () => { this.__focus('both'); },
          blur: this.__blur,
          keydown: this.__keydown,
          keyup: this.__keyup
        });

        return evt
      }
    },

    minEvents () {
      if (this.editable && !this.$q.platform.is.mobile && this.dragOnlyRange !== true) {
        return {
          focus: () => { this.__focus('min'); },
          blur: this.__blur,
          keydown: this.__keydown,
          keyup: this.__keyup
        }
      }
    },

    maxEvents () {
      if (this.editable && !this.$q.platform.is.mobile && this.dragOnlyRange !== true) {
        return {
          focus: () => { this.__focus('max'); },
          blur: this.__blur,
          keydown: this.__keydown,
          keyup: this.__keyup
        }
      }
    },

    minPinClass () {
      const color = this.leftLabelColor || this.labelColor;
      if (color) {
        return `text-${color}`
      }
    },

    maxPinClass () {
      const color = this.rightLabelColor || this.labelColor;
      if (color) {
        return `text-${color}`
      }
    },

    minLabel () {
      return this.leftLabelValue !== void 0
        ? this.leftLabelValue
        : this.model.min
    },

    maxLabel () {
      return this.rightLabelValue !== void 0
        ? this.rightLabelValue
        : this.model.max
    }
  },

  methods: {
    __updateValue (change) {
      if (this.model.min !== this.value.min || this.model.max !== this.value.max) {
        this.$emit('input', this.model);
        change === true && this.$emit('change', this.model);
      }
    },

    __getDragging (event) {
      const
        { left, width } = this.$el.getBoundingClientRect(),
        sensitivity = this.dragOnlyRange ? 0 : this.$refs.minThumb.offsetWidth / (2 * width),
        diff = this.max - this.min;

      let dragging = {
        left,
        width,
        valueMin: this.model.min,
        valueMax: this.model.max,
        ratioMin: (this.value.min - this.min) / diff,
        ratioMax: (this.value.max - this.min) / diff
      };

      let
        ratio = getRatio(event, dragging, this.$q.lang.rtl),
        type;

      if (this.dragOnlyRange !== true && ratio < dragging.ratioMin + sensitivity) {
        type = dragType.MIN;
      }
      else if (this.dragOnlyRange === true || ratio < dragging.ratioMax - sensitivity) {
        if (this.dragRange || this.dragOnlyRange) {
          type = dragType.RANGE;
          Object.assign(dragging, {
            offsetRatio: ratio,
            offsetModel: getModel(ratio, this.min, this.max, this.step, this.decimals),
            rangeValue: dragging.valueMax - dragging.valueMin,
            rangeRatio: dragging.ratioMax - dragging.ratioMin
          });
        }
        else {
          type = dragging.ratioMax - ratio < ratio - dragging.ratioMin
            ? dragType.MAX
            : dragType.MIN;
        }
      }
      else {
        type = dragType.MAX;
      }

      dragging.type = type;
      this.__nextFocus = void 0;

      return dragging
    },

    __updatePosition (event, dragging = this.dragging) {
      let
        ratio = getRatio(event, dragging, this.$q.lang.rtl),
        model = getModel(ratio, this.min, this.max, this.step, this.decimals),
        pos;

      switch (dragging.type) {
        case dragType.MIN:
          if (ratio <= dragging.ratioMax) {
            pos = {
              minR: ratio,
              maxR: dragging.ratioMax,
              min: model,
              max: dragging.valueMax
            };
            this.__nextFocus = 'min';
          }
          else {
            pos = {
              minR: dragging.ratioMax,
              maxR: ratio,
              min: dragging.valueMax,
              max: model
            };
            this.__nextFocus = 'max';
          }
          break

        case dragType.MAX:
          if (ratio >= dragging.ratioMin) {
            pos = {
              minR: dragging.ratioMin,
              maxR: ratio,
              min: dragging.valueMin,
              max: model
            };
            this.__nextFocus = 'max';
          }
          else {
            pos = {
              minR: ratio,
              maxR: dragging.ratioMin,
              min: model,
              max: dragging.valueMin
            };
            this.__nextFocus = 'min';
          }
          break

        case dragType.RANGE:
          let
            ratioDelta = ratio - dragging.offsetRatio,
            minR = between(dragging.ratioMin + ratioDelta, 0, 1 - dragging.rangeRatio),
            modelDelta = model - dragging.offsetModel,
            min = between(dragging.valueMin + modelDelta, this.min, this.max - dragging.rangeValue);

          pos = {
            minR,
            maxR: minR + dragging.rangeRatio,
            min: parseFloat(min.toFixed(this.decimals)),
            max: parseFloat((min + dragging.rangeValue).toFixed(this.decimals))
          };
          break
      }

      this.model = {
        min: pos.min,
        max: pos.max
      };

      if (this.snap !== true || this.step === 0) {
        this.curMinRatio = pos.minR;
        this.curMaxRatio = pos.maxR;
      }
      else {
        const diff = this.max - this.min;
        this.curMinRatio = (this.model.min - this.min) / diff;
        this.curMaxRatio = (this.model.max - this.min) / diff;
      }
    },

    __focus (which) {
      this.focus = which;
    },

    __keydown (evt) {
      // PGDOWN, LEFT, DOWN, PGUP, RIGHT, UP
      if (![34, 37, 40, 33, 39, 38].includes(evt.keyCode)) {
        return
      }

      stopAndPrevent(evt);

      const
        step = ([34, 33].includes(evt.keyCode) ? 10 : 1) * this.computedStep,
        offset = [34, 37, 40].includes(evt.keyCode) ? -step : step;

      if (this.dragOnlyRange) {
        const interval = this.dragOnlyRange ? this.model.max - this.model.min : 0;

        this.model.min = between(
          parseFloat((this.model.min + offset).toFixed(this.decimals)),
          this.min,
          this.max - interval
        );

        this.model.max = parseFloat((this.model.min + interval).toFixed(this.decimals));
      }
      else {
        const which = this.focus;

        this.model[which] = between(
          parseFloat((this.model[which] + offset).toFixed(this.decimals)),
          which === 'min' ? this.min : this.model.min,
          which === 'max' ? this.max : this.model.max
        );
      }

      this.__updateValue();
    },

    __getThumb (h, which) {
      return h('div', {
        ref: which + 'Thumb',
        staticClass: 'q-slider__thumb-container absolute non-selectable',
        style: this[which + 'ThumbStyle'],
        class: this[which + 'ThumbClass'],
        on: this[which + 'Events'],
        attrs: { tabindex: this.dragOnlyRange !== true ? this.computedTabindex : null }
      }, [
        h('svg', {
          staticClass: 'q-slider__thumb absolute',
          attrs: { width: '21', height: '21' }
        }, [
          h('circle', {
            attrs: {
              cx: '10.5',
              cy: '10.5',
              r: '7.875'
            }
          })
        ]),

        this.label === true || this.labelAlways === true ? h('div', {
          staticClass: 'q-slider__pin absolute flex flex-center',
          class: this[which + 'PinClass']
        }, [
          h('div', { staticClass: 'q-slider__pin-value-marker' }, [
            h('div', { staticClass: 'q-slider__pin-value-marker-bg' }),
            h('div', { staticClass: 'q-slider__pin-value-marker-text' }, [
              this[which + 'Label']
            ])
          ])
        ]) : null,

        h('div', { staticClass: 'q-slider__focus-ring' })
      ])
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-slider',
      attrs: {
        role: 'slider',
        'aria-valuemin': this.min,
        'aria-valuemax': this.max,
        'data-step': this.step,
        'aria-disabled': this.disable,
        tabindex: this.dragOnlyRange && !this.$q.platform.is.mobile
          ? this.computedTabindex
          : null
      },
      class: this.classes,
      on: this.events,
      directives: this.editable ? [{
        name: 'touch-pan',
        value: this.__pan,
        modifiers: {
          horizontal: true,
          prevent: true,
          stop: true,
          mouse: true,
          mouseAllDir: true,
          mouseStop: true
        }
      }] : null
    }, [
      h('div', { staticClass: 'q-slider__track-container absolute overflow-hidden' }, [
        h('div', {
          staticClass: 'q-slider__track absolute-full',
          style: this.trackStyle
        }),

        this.markers === true
          ? h('div', {
            staticClass: 'q-slider__track-markers absolute-full fit',
            style: this.markerStyle
          })
          : null
      ]),

      this.__getThumb(h, 'min'),
      this.__getThumb(h, 'max')
    ])
  }
});

Vue.extend({
  name: 'QRating',

  props: {
    value: {
      type: Number,
      required: true
    },

    max: {
      type: [String, Number],
      default: 5
    },

    icon: String,
    color: String,
    size: String,

    noReset: Boolean,

    readonly: Boolean,
    disable: Boolean
  },

  data () {
    return {
      mouseModel: 0
    }
  },

  computed: {
    editable () {
      return !this.readonly && !this.disable
    },

    classes () {
      return `q-rating--${this.editable === true ? '' : 'non-'}editable` +
        (this.disable === true ? ' disabled' : '') +
        (this.color !== void 0 ? ` text-${this.color}` : '')
    },

    style () {
      if (this.size !== void 0) {
        return { fontSize: this.size }
      }
    }
  },

  methods: {
    __set (value) {
      if (this.editable === true) {
        const
          model = between(parseInt(value, 10), 1, parseInt(this.max, 10)),
          newVal = this.noReset !== true && this.value === model ? 0 : model;

        newVal !== this.value && this.$emit('input', newVal);
        this.mouseModel = 0;
      }
    },

    __setHoverValue (value) {
      if (this.editable === true) {
        this.mouseModel = value;
      }
    },

    __keyup (e, i) {
      switch (e.keyCode) {
        case 13:
        case 32:
          this.__set(i);
          return stopAndPrevent(e)
        case 37: // LEFT ARROW
        case 40: // DOWN ARROW
          if (this.$refs[`rt${i - 1}`]) {
            this.$refs[`rt${i - 1}`].focus();
          }
          return stopAndPrevent(e)
        case 39: // RIGHT ARROW
        case 38: // UP ARROW
          if (this.$refs[`rt${i + 1}`]) {
            this.$refs[`rt${i + 1}`].focus();
          }
          return stopAndPrevent(e)
      }
    }
  },

  render (h) {
    const
      child = [],
      tabindex = this.editable === true ? 0 : null;

    for (let i = 1; i <= this.max; i++) {
      child.push(
        h(QIcon, {
          key: i,
          ref: `rt${i}`,
          staticClass: 'q-rating__icon',
          class: {
            'q-rating__icon--active': (!this.mouseModel && this.value >= i) || (this.mouseModel && this.mouseModel >= i),
            'q-rating__icon--exselected': this.mouseModel && this.value >= i && this.mouseModel < i,
            'q-rating__icon--hovered': this.mouseModel === i
          },
          props: { name: this.icon || this.$q.iconSet.rating.icon },
          attrs: { tabindex },
          on: {
            click: () => this.__set(i),
            mouseover: () => this.__setHoverValue(i),
            mouseout: () => { this.mouseModel = 0; },
            focus: () => this.__setHoverValue(i),
            blur: () => { this.mouseModel = 0; },
            keyup: e => { this.__keyup(e, i); }
          }
        })
      );
    }

    return h('div', {
      staticClass: 'q-rating row inline items-center',
      class: this.classes,
      style: this.style,
      on: this.$listeners
    }, child)
  }
});

var QScrollArea = Vue.extend({
  name: 'QScrollArea',

  directives: {
    TouchPan
  },

  props: {
    thumbStyle: {
      type: Object,
      default: () => ({})
    },
    contentStyle: {
      type: Object,
      default: () => ({})
    },
    contentActiveStyle: {
      type: Object,
      default: () => ({})
    },
    delay: {
      type: [String, Number],
      default: 1000
    },
    horizontal: Boolean
  },

  data () {
    return {
      active: false,
      hover: false,
      containerWidth: 0,
      containerHeight: 0,
      scrollPosition: 0,
      scrollSize: 0
    }
  },

  computed: {
    thumbHidden () {
      return this.scrollSize <= this.containerSize || (!this.active && !this.hover)
    },

    thumbSize () {
      return Math.round(
        between(
          this.containerSize * this.containerSize / this.scrollSize,
          50,
          this.containerSize
        )
      )
    },

    style () {
      const pos = this.scrollPercentage * (this.containerSize - this.thumbSize);
      return Object.assign(
        {},
        this.thumbStyle,
        this.horizontal === true
          ? {
            left: `${pos}px`,
            width: `${this.thumbSize}px` }
          : {
            top: `${pos}px`,
            height: `${this.thumbSize}px`
          }
      )
    },

    mainStyle () {
      return this.thumbHidden === true
        ? this.contentStyle
        : this.contentActiveStyle
    },

    scrollPercentage () {
      const p = between(this.scrollPosition / (this.scrollSize - this.containerSize), 0, 1);
      return Math.round(p * 10000) / 10000
    },

    direction () {
      return this.horizontal ? 'right' : 'down'
    },

    containerSize () {
      return this.horizontal === true
        ? this.containerWidth
        : this.containerHeight
    },

    dirProps () {
      return this.horizontal === true
        ? { el: 'scrollLeft', wheel: 'x' }
        : { el: 'scrollTop', wheel: 'y' }
    },

    thumbClass () {
      return `q-scrollarea__thumb--${this.horizontal === true ? 'h absolute-bottom' : 'v absolute-right'}` +
        (this.thumbHidden === true ? ' q-scrollarea__thumb--invisible' : '')
    }
  },

  methods: {
    setScrollPosition (offset, duration) {
      if (this.horizontal === true) {
        setHorizontalScrollPosition(this.$refs.target, offset, duration);
      }
      else {
        setScrollPosition(this.$refs.target, offset, duration);
      }
    },

    __updateContainer ({ height, width }) {
      if (this.containerWidth !== width) {
        this.containerWidth = width;
        this.__setActive(true, true);
      }
      if (this.containerHeight !== height) {
        this.containerHeight = height;
        this.__setActive(true, true);
      }
    },

    __updateScroll ({ position }) {
      if (this.scrollPosition !== position) {
        this.scrollPosition = position;
        this.__setActive(true, true);
      }
    },

    __updateScrollSize ({ height, width }) {
      if (this.horizontal) {
        if (this.scrollSize !== width) {
          this.scrollSize = width;
          this.__setActive(true, true);
        }
      }
      else {
        if (this.scrollSize !== height) {
          this.scrollSize = height;
          this.__setActive(true, true);
        }
      }
    },

    __panThumb (e) {
      if (e.isFirst) {
        this.refPos = this.scrollPosition;
        this.__setActive(true, true);
      }

      if (e.isFinal) {
        this.__setActive(false);
      }

      const multiplier = (this.scrollSize - this.containerSize) / (this.containerSize - this.thumbSize);
      const distance = this.horizontal ? e.distance.x : e.distance.y;
      const pos = this.refPos + (e.direction === this.direction ? 1 : -1) * distance * multiplier;
      this.__setScroll(pos);
    },

    __panContainer (e) {
      if (e.isFirst) {
        this.refPos = this.scrollPosition;
        this.__setActive(true, true);
      }
      if (e.isFinal) {
        this.__setActive(false);
      }

      const distance = this.horizontal ? e.distance.x : e.distance.y;
      const pos = this.refPos + (e.direction === this.direction ? -1 : 1) * distance;
      this.__setScroll(pos);

      if (pos > 0 && pos + this.containerSize < this.scrollSize) {
        prevent(e.evt);
      }
    },

    __mouseWheel (e) {
      const el = this.$refs.target;

      el[this.dirProps.el] += getMouseWheelDistance(e)[this.dirProps.wheel];
      if (el[this.dirProps.el] > 0 && el[this.dirProps.el] + this.containerSize < this.scrollSize) {
        prevent(e);
      }
    },

    __setActive (active, timer) {
      clearTimeout(this.timer);

      if (active === this.active) {
        if (active && this.timer) {
          this.__startTimer();
        }
        return
      }

      if (active) {
        this.active = true;
        if (timer) {
          this.__startTimer();
        }
      }
      else {
        this.active = false;
      }
    },

    __startTimer () {
      this.timer = setTimeout(() => {
        this.active = false;
        this.timer = null;
      }, this.delay);
    },

    __setScroll (scroll) {
      this.$refs.target[this.dirProps.el] = scroll;
    }
  },

  render (h) {
    if (!this.$q.platform.is.desktop) {
      return h('div', {
        staticClass: 'q-scroll-area',
        style: this.contentStyle
      }, [
        h('div', {
          ref: 'target',
          staticClass: 'scroll relative-position fit'
        }, slot(this, 'default'))
      ])
    }

    return h('div', {
      staticClass: 'q-scrollarea',
      on: {
        mouseenter: () => { this.hover = true; },
        mouseleave: () => { this.hover = false; }
      }
    }, [
      h('div', {
        ref: 'target',
        staticClass: 'scroll relative-position overflow-hidden fit',
        on: {
          wheel: this.__mouseWheel
        },
        directives: [{
          name: 'touch-pan',
          modifiers: {
            vertical: !this.horizontal,
            horizontal: this.horizontal,
            mightPrevent: true
          },
          value: this.__panContainer
        }]
      }, [
        h('div', {
          staticClass: 'absolute',
          style: this.mainStyle,
          class: `full-${this.horizontal === true ? 'height' : 'width'}`
        }, [
          h(QResizeObserver, {
            on: { resize: this.__updateScrollSize }
          }),
          slot(this, 'default')
        ]),
        h(QScrollObserver, {
          props: { horizontal: this.horizontal },
          on: { scroll: this.__updateScroll }
        })
      ]),

      h(QResizeObserver, {
        on: { resize: this.__updateContainer }
      }),

      h('div', {
        staticClass: 'q-scrollarea__thumb',
        style: this.style,
        class: this.thumbClass,
        directives: this.thumbHidden === true ? null : [{
          name: 'touch-pan',
          modifiers: {
            vertical: !this.horizontal,
            horizontal: this.horizontal,
            prevent: true,
            mouse: true,
            mouseAllDir: true,
            mousePrevent: true
          },
          value: this.__panThumb
        }]
      })
    ])
  }
});

const validateNewValueMode = v => ['add', 'add-unique', 'toggle'].includes(v);

var QSelect = Vue.extend({
  name: 'QSelect',

  mixins: [ QField ],

  props: {
    value: {
      required: true
    },

    multiple: Boolean,

    displayValue: [String, Number],
    displayValueSanitize: Boolean,
    dropdownIcon: String,

    options: {
      type: Array,
      default: () => []
    },

    optionValue: [Function, String],
    optionLabel: [Function, String],
    optionDisable: [Function, String],

    hideSelected: Boolean,
    hideDropdownIcon: Boolean,
    fillInput: Boolean,

    maxValues: [Number, String],

    optionsDense: Boolean,
    optionsDark: Boolean,
    optionsSelectedClass: String,
    optionsCover: Boolean,
    optionsSanitize: Boolean,

    popupContentClass: String,
    popupContentStyle: [String, Array, Object],

    useInput: Boolean,
    useChips: Boolean,

    newValueMode: {
      type: String,
      validator: validateNewValueMode
    },

    mapOptions: Boolean,
    emitValue: Boolean,

    inputDebounce: {
      type: [Number, String],
      default: 500
    },

    transitionShow: {
      type: String,
      default: 'fade'
    },

    transitionHide: {
      type: String,
      default: 'fade'
    }
  },

  data () {
    return {
      menu: false,
      dialog: false,
      optionIndex: -1,
      optionsToShow: 20,
      inputValue: ''
    }
  },

  watch: {
    innerValue: {
      handler () {
        if (
          this.useInput === true && this.fillInput === true && this.multiple !== true &&
          ((this.dialog !== true && this.menu !== true) || this.hasValue !== true)
        ) {
          this.__resetInputValue();
          if (this.dialog === true || this.menu === true) {
            this.filter('');
          }
        }
      },
      immediate: true
    },

    menu (show) {
      this.__updateMenu(show);
    }
  },

  computed: {
    fieldClass () {
      return `q-select q-field--auto-height q-select--with${this.useInput !== true ? 'out' : ''}-input`
    },

    menuClass () {
      return (this.optionsDark === true ? 'q-select__menu--dark' : '') +
        (this.popupContentClass ? ' ' + this.popupContentClass : '')
    },

    innerValue () {
      const
        mapNull = this.mapOptions === true && this.multiple !== true,
        val = this.value !== void 0 && (this.value !== null || mapNull === true)
          ? (this.multiple === true ? this.value : [ this.value ])
          : [];

      return this.mapOptions === true && Array.isArray(this.options) === true
        ? (
          this.value === null && mapNull === true
            ? val.map(v => this.__getOption(v)).filter(v => v !== null)
            : val.map(v => this.__getOption(v))
        )
        : val
    },

    noOptions () {
      return this.options === void 0 || this.options === null || this.options.length === 0
    },

    selectedString () {
      return this.innerValue
        .map(opt => this.__getOptionLabel(opt))
        .join(', ')
    },

    displayAsText () {
      return this.displayValueSanitize === true || (
        this.displayValue === void 0 && (
          this.optionsSanitize === true ||
          this.innerValue.some(opt => opt !== null && opt.sanitize === true)
        )
      )
    },

    selectedScope () {
      const tabindex = this.focused === true ? 0 : -1;

      return this.innerValue.map((opt, i) => ({
        index: i,
        opt,
        sanitize: this.optionsSanitize === true || opt.sanitize === true,
        selected: true,
        removeAtIndex: this.__removeAtIndexAndFocus,
        toggleOption: this.toggleOption,
        tabindex
      }))
    },

    optionScope () {
      return this.options.slice(0, this.optionsToShow).map((opt, i) => {
        const disable = this.__isDisabled(opt);

        const itemProps = {
          clickable: true,
          active: false,
          activeClass: this.optionsSelectedClass,
          manualFocus: true,
          focused: false,
          disable,
          tabindex: -1,
          dense: this.optionsDense,
          dark: this.optionsDark
        };

        if (disable !== true) {
          this.__isSelected(opt) === true && (itemProps.active = true);
          this.optionIndex === i && (itemProps.focused = true);
        }

        const itemEvents = {
          click: () => { this.toggleOption(opt); }
        };

        if (this.$q.platform.is.desktop === true) {
          itemEvents.mousemove = () => { this.setOptionIndex(i); };
        }

        return {
          index: i,
          opt,
          sanitize: this.optionsSanitize === true || opt.sanitize === true,
          selected: itemProps.active,
          focused: itemProps.focused,
          toggleOption: this.toggleOption,
          setOptionIndex: this.setOptionIndex,
          itemProps,
          itemEvents
        }
      })
    },

    dropdownArrowIcon () {
      return this.dropdownIcon !== void 0
        ? this.dropdownIcon
        : this.$q.iconSet.arrow.dropdown
    },

    squaredMenu () {
      return this.optionsCover === false &&
        this.outlined !== true &&
        this.standout !== true &&
        this.borderless !== true &&
        this.rounded !== true
    }
  },

  methods: {
    removeAtIndex (index) {
      if (index > -1 && index < this.innerValue.length) {
        if (this.multiple === true) {
          const model = [].concat(this.value);
          this.$emit('remove', { index, value: model.splice(index, 1) });
          this.$emit('input', model);
        }
        else {
          this.$emit('input', null);
        }
      }
    },

    __removeAtIndexAndFocus (index) {
      this.removeAtIndex(index);
      this.focus();
    },

    add (opt, unique) {
      const val = this.emitValue === true
        ? this.__getOptionValue(opt)
        : opt;

      if (this.multiple !== true) {
        this.$emit('input', val);
        return
      }

      if (this.innerValue.length === 0) {
        this.$emit('add', { index: 0, value: val });
        this.$emit('input', this.multiple === true ? [ val ] : val);
        return
      }

      if (unique === true && this.__isSelected(opt) === true) {
        return
      }

      const model = [].concat(this.value);

      if (this.maxValues !== void 0 && model.length >= this.maxValues) {
        return
      }

      this.$emit('add', { index: model.length, value: val });
      model.push(val);
      this.$emit('input', model);
    },

    toggleOption (opt) {
      if (this.editable !== true || opt === void 0 || this.__isDisabled(opt) === true) {
        return
      }

      const optValue = this.__getOptionValue(opt);

      this.multiple !== true && this.updateInputValue(this.fillInput === true ? optValue : '', true);
      this.focus();

      if (this.multiple !== true) {
        this.hidePopup();

        if (isDeepEqual(this.__getOptionValue(this.value), optValue) !== true) {
          this.$emit('input', this.emitValue === true ? optValue : opt);
        }
        return
      }

      if (this.innerValue.length === 0) {
        const val = this.emitValue === true ? optValue : opt;
        this.$emit('add', { index: 0, value: val });
        this.$emit('input', this.multiple === true ? [ val ] : val);
        return
      }

      const
        model = [].concat(this.value),
        index = this.value.findIndex(v => isDeepEqual(this.__getOptionValue(v), optValue));

      if (index > -1) {
        this.$emit('remove', { index, value: model.splice(index, 1) });
      }
      else {
        if (this.maxValues !== void 0 && model.length >= this.maxValues) {
          return
        }

        const val = this.emitValue === true ? optValue : opt;

        this.$emit('add', { index: model.length, value: val });
        model.push(val);
      }

      this.$emit('input', model);
    },

    setOptionIndex (index) {
      if (this.$q.platform.is.desktop !== true) { return }

      const val = index >= -1 && index < this.optionsToShow
        ? index
        : -1;

      if (this.optionIndex !== val) {
        this.optionIndex = val;
      }
    },

    __getOption (value) {
      return this.options.find(opt => isDeepEqual(this.__getOptionValue(opt), value)) || value
    },

    __getOptionValue (opt) {
      if (typeof this.optionValue === 'function') {
        return this.optionValue(opt)
      }
      if (Object(opt) === opt) {
        return typeof this.optionValue === 'string'
          ? opt[this.optionValue]
          : opt.value
      }
      return opt
    },

    __getOptionLabel (opt) {
      if (typeof this.optionLabel === 'function') {
        return this.optionLabel(opt)
      }
      if (Object(opt) === opt) {
        return typeof this.optionLabel === 'string'
          ? opt[this.optionLabel]
          : opt.label
      }
      return opt
    },

    __isDisabled (opt) {
      if (typeof this.optionDisable === 'function') {
        return this.optionDisable(opt) === true
      }
      if (Object(opt) === opt) {
        return typeof this.optionDisable === 'string'
          ? opt[this.optionDisable] === true
          : opt.disable === true
      }
      return false
    },

    __isSelected (opt) {
      const val = this.__getOptionValue(opt);
      return this.innerValue
        .find(v => isDeepEqual(this.__getOptionValue(v), val)) !== void 0
    },

    __onTargetKeydown (e) {
      // escape, tab
      if (e.keyCode === 27 || e.keyCode === 9) {
        this.__closeMenu();
        return
      }

      if (e.target !== this.$refs.target) { return }

      // down
      if (
        e.keyCode === 40 &&
        this.innerLoading !== true &&
        this.menu === false
      ) {
        stopAndPrevent(e);
        this.showPopup();
        return
      }

      // delete
      if (
        e.keyCode === 8 &&
        this.multiple === true &&
        this.inputValue.length === 0 &&
        Array.isArray(this.value)
      ) {
        this.removeAtIndex(this.value.length - 1);
        return
      }

      // up, down
      if (e.keyCode === 38 || e.keyCode === 40) {
        stopAndPrevent(e);

        if (this.menu === true) {
          let index = this.optionIndex;
          do {
            index = normalizeToInterval(
              index + (e.keyCode === 38 ? -1 : 1),
              -1,
              Math.min(this.optionsToShow, this.options.length) - 1
            );

            if (index === -1) {
              this.optionIndex = -1;
              return
            }
          }
          while (index !== this.optionIndex && this.__isDisabled(this.options[index]) === true)

          const dir = index > this.optionIndex ? 1 : -1;
          this.optionIndex = index;

          this.$nextTick(() => {
            const el = this.__getMenuContentEl().querySelector('.q-manual-focusable--focused');
            if (el !== null && el.scrollIntoView !== void 0) {
              if (el.scrollIntoViewIfNeeded !== void 0) {
                el.scrollIntoViewIfNeeded(false);
              }
              else {
                el.scrollIntoView(dir === -1);
              }
            }
          });
        }
      }

      // enter
      if (e.target !== this.$refs.target || e.keyCode !== 13) { return }

      stopAndPrevent(e);

      if (this.optionIndex > -1 && this.optionIndex < this.optionsToShow) {
        this.toggleOption(this.options[this.optionIndex]);
        return
      }

      // below is meant for multiple mode only
      if (
        this.inputValue.length > 0 &&
        (this.newValueMode !== void 0 || this.$listeners['new-value'] !== void 0)
      ) {
        const done = (val, mode) => {
          if (mode) {
            if (validateNewValueMode(mode) !== true) {
              console.error('QSelect: invalid new value mode - ' + mode);
              return
            }
          }
          else {
            mode = this.newValueMode;
          }

          if (val !== void 0 && val !== null) {
            this[mode === 'toggle' ? 'toggleOption' : 'add'](
              val,
              mode === 'add-unique'
            );
          }

          this.updateInputValue('');
        };

        if (this.$listeners['new-value'] !== void 0) {
          this.$emit('new-value', this.inputValue, done);
        }
        else {
          done(this.inputValue);
        }
      }

      if (this.menu === true) {
        this.dialog !== true && this.__closeMenu();
      }
      else if (this.innerLoading !== true) {
        this.showPopup();
      }
    },

    __getMenuContentEl () {
      return this.hasDialog === true
        ? this.$refs.menuContent
        : (
          this.$refs.menu !== void 0
            ? this.$refs.menu.__portal.$el
            : void 0
        )
    },

    __hydrateOptions () {
      if (this.avoidScroll !== true) {
        if (this.optionsToShow < this.options.length) {
          const el = this.__getMenuContentEl();

          if (el.scrollHeight - el.scrollTop - el.clientHeight < 200) {
            this.optionsToShow += 20;
            this.avoidScroll = true;
            this.$nextTick(() => {
              this.avoidScroll = false;
              this.__hydrateOptions();
            });
          }
        }
      }
    },

    __getSelection (h, fromDialog) {
      if (this.hideSelected === true) {
        return fromDialog !== true && this.hasDialog === true
          ? [
            h('span', {
              domProps: {
                'textContent': this.inputValue
              }
            })
          ]
          : []
      }

      if (this.$scopedSlots['selected-item'] !== void 0) {
        return this.selectedScope.map(scope => this.$scopedSlots['selected-item'](scope))
      }

      if (this.$scopedSlots.selected !== void 0) {
        return this.$scopedSlots.selected()
      }

      if (this.useChips === true) {
        const tabindex = this.focused === true ? 0 : -1;

        return this.selectedScope.map((scope, i) => h(QChip, {
          key: 'option-' + i,
          props: {
            removable: this.__isDisabled(scope.opt) !== true,
            dense: true,
            textColor: this.color,
            tabindex
          },
          on: {
            remove () { scope.removeAtIndex(i); }
          }
        }, [
          h('span', {
            domProps: {
              [scope.sanitize === true ? 'textContent' : 'innerHTML']: this.__getOptionLabel(scope.opt)
            }
          })
        ]))
      }

      return [
        h('span', {
          domProps: {
            [this.displayAsText ? 'textContent' : 'innerHTML']: this.displayValue !== void 0
              ? this.displayValue
              : this.selectedString
          }
        })
      ]
    },

    __getControl (h, fromDialog) {
      let data = {};
      const child = this.__getSelection(h, fromDialog);

      if (this.useInput === true && (fromDialog === true || this.hasDialog === false)) {
        child.push(this.__getInput(h));
      }
      else if (this.editable === true) {
        data = {
          ref: 'target',
          attrs: {
            tabindex: 0,
            autofocus: this.autofocus,
            ...this.$attrs
          },
          on: {
            keydown: this.__onTargetKeydown
          }
        };
      }

      data.staticClass = 'q-field__native row items-center';

      return h('div', data, child)
    },

    __getOptions (h) {
      const fn = this.$scopedSlots.option !== void 0
        ? this.$scopedSlots.option
        : scope => h(QItem, {
          key: scope.index,
          props: scope.itemProps,
          on: scope.itemEvents
        }, [
          h(QItemSection, [
            h(QItemLabel, {
              domProps: {
                [scope.sanitize === true ? 'textContent' : 'innerHTML']: this.__getOptionLabel(scope.opt)
              }
            })
          ])
        ]);

      return this.optionScope.map(fn)
    },

    __getInnerAppend (h) {
      return this.hideDropdownIcon !== true
        ? [
          h(QIcon, {
            staticClass: 'q-select__dropdown-icon',
            props: { name: this.dropdownArrowIcon }
          })
        ]
        : null
    },

    __getInput (h) {
      return h('input', {
        ref: 'target',
        staticClass: 'q-select__input q-placeholder col',
        class: this.hideSelected !== true && this.innerValue.length > 0
          ? 'q-select__input--padding'
          : null,
        domProps: { value: this.inputValue },
        attrs: {
          tabindex: 0,
          autofocus: this.autofocus,
          ...this.$attrs,
          disabled: this.editable !== true
        },
        on: {
          input: this.__onInputValue,
          keydown: this.__onTargetKeydown
        }
      })
    },

    __onInputValue (e) {
      clearTimeout(this.inputTimer);
      this.inputValue = e.target.value || '';

      if (this.$listeners.filter !== void 0) {
        this.inputTimer = setTimeout(() => {
          this.filter(this.inputValue);
        }, this.inputDebounce);
      }
    },

    updateInputValue (val, noFiltering) {
      if (this.useInput === true) {
        if (this.inputValue !== val) {
          this.inputValue = val;
        }

        noFiltering !== true && this.filter(val);
      }
    },

    filter (val) {
      if (this.$listeners.filter === void 0 || this.focused !== true) {
        return
      }

      if (this.innerLoading === true) {
        this.$emit('filter-abort');
      }
      else {
        this.innerLoading = true;
      }

      if (
        val !== '' &&
        this.multiple !== true &&
        this.innerValue.length > 0 &&
        val === this.__getOptionLabel(this.innerValue[0])
      ) {
        val = '';
      }

      const filterId = setTimeout(() => {
        this.menu === true && (this.menu = false);
      }, 10);
      clearTimeout(this.filterId);
      this.filterId = filterId;

      this.$emit(
        'filter',
        val,
        fn => {
          if (this.focused === true && this.filterId === filterId) {
            clearTimeout(this.filterId);
            typeof fn === 'function' && fn();
            this.$nextTick(() => {
              this.innerLoading = false;
              if (this.menu === true) {
                this.__updateMenu(true);
              }
              else {
                this.menu = true;
              }
            });
          }
        },
        () => {
          if (this.focused === true && this.filterId === filterId) {
            clearTimeout(this.filterId);
            this.innerLoading = false;
          }
          this.menu === true && (this.menu = false);
        }
      );
    },

    __getControlEvents () {
      const focusout = e => {
        this.__onControlFocusout(e, () => {
          this.__resetInputValue();
          this.__closeMenu();
        });
      };

      return {
        focus: e => {
          this.hasDialog !== true && this.focus(e);
        },
        focusin: this.__onControlFocusin,
        focusout,
        'popup-show': this.__onControlPopupShow,
        'popup-hide': e => {
          this.hasPopupOpen = false;
          focusout(e);
        },
        click: e => {
          if (this.hasDialog !== true && this.menu === true) {
            this.__closeMenu();
          }
          else {
            this.showPopup(e);
          }
        }
      }
    },

    __getPopup (h) {
      if (
        this.editable !== false && (
          this.dialog === true || // dialog always has menu displayed, so need to render it
          this.noOptions !== true ||
          this.$scopedSlots['no-option'] !== void 0
        )
      ) {
        return this[`__get${this.hasDialog === true ? 'Dialog' : 'Menu'}`](h)
      }
    },

    __getMenu (h) {
      const child = this.noOptions === true
        ? (
          this.$scopedSlots['no-option'] !== void 0
            ? this.$scopedSlots['no-option']({ inputValue: this.inputValue })
            : null
        )
        : this.__getOptions(h);

      return h(QMenu, {
        ref: 'menu',
        props: {
          value: this.menu,
          fit: true,
          cover: this.optionsCover === true && this.noOptions !== true && this.useInput !== true,
          contentClass: this.menuClass,
          contentStyle: this.popupContentStyle,
          noParentEvent: true,
          noRefocus: true,
          noFocus: true,
          square: this.squaredMenu,
          transitionShow: this.transitionShow,
          transitionHide: this.transitionHide
        },
        on: {
          '&scroll': this.__hydrateOptions,
          'before-hide': this.__closeMenu
        }
      }, child)
    },

    __getDialog (h) {
      const content = [
        h(QField, {
          staticClass: `col-auto ${this.fieldClass}`,
          props: {
            ...this.$props,
            dark: this.optionsDark,
            square: true,
            loading: this.innerLoading,
            filled: true
          },
          on: {
            ...this.$listeners,
            focus: stop,
            blur: stop
          },
          scopedSlots: {
            ...this.$scopedSlots,
            rawControl: () => this.__getControl(h, true),
            before: void 0,
            after: void 0
          }
        })
      ];

      this.menu === true && content.push(
        h('div', {
          ref: 'menuContent',
          staticClass: 'scroll',
          class: this.popupContentClass,
          style: this.popupContentStyle,
          on: {
            click: prevent,
            '&scroll': this.__hydrateOptions
          }
        }, (
          this.noOptions === true
            ? (
              this.$scopedSlots['no-option'] !== void 0
                ? this.$scopedSlots['no-option']({ inputValue: this.inputValue })
                : null
            )
            : this.__getOptions(h)
        ))
      );

      return h(QDialog, {
        props: {
          value: this.dialog,
          noRefocus: true,
          noFocus: true,
          position: this.useInput === true ? 'top' : void 0
        },
        on: {
          'before-hide': () => {
            this.focused = false;
          },
          hide: e => {
            this.hidePopup();
            this.$emit('blur', e);
            this.__resetInputValue();
          },
          show: () => {
            this.$refs.target.focus();
          }
        }
      }, [
        h('div', {
          staticClass: 'q-select__dialog' + (this.optionsDark === true ? ' q-select__menu--dark' : '')
        }, content)
      ])
    },

    __closeMenu () {
      this.menu = false;

      if (this.focused === false) {
        clearTimeout(this.filterId);
        this.filterId = void 0;

        if (this.innerLoading === true) {
          this.$emit('filter-abort');
          this.innerLoading = false;
        }
      }
    },

    showPopup (e) {
      if (this.hasDialog === true) {
        this.__onControlFocusin(e);
        this.dialog = true;
      }
      else {
        this.focus(e);
      }

      if (this.$listeners.filter !== void 0) {
        this.filter(this.inputValue);
      }
      else if (this.noOptions !== true || this.$scopedSlots['no-option'] !== void 0) {
        this.menu = true;
      }
    },

    hidePopup () {
      this.dialog = false;
      this.__closeMenu();
    },

    __resetInputValue () {
      this.useInput === true && this.updateInputValue(
        this.multiple !== true && this.fillInput === true && this.innerValue.length > 0
          ? this.__getOptionLabel(this.innerValue[0]) || ''
          : '',
        true
      );
    },

    __updateMenu (show) {
      this.optionIndex = -1;

      if (show === true) {
        this.optionsToShow = 20;
        this.$nextTick(() => {
          this.__hydrateOptions();
        });
      }
    },

    __onPreRender () {
      this.hasDialog = this.$q.platform.is.mobile !== true
        ? false
        : (
          this.useInput === true
            ? this.$scopedSlots['no-option'] !== void 0 || this.$listeners.filter !== void 0
            : true
        );
    },

    __onPostRender () {
      if (this.dialog === false && this.$refs.menu !== void 0) {
        this.$refs.menu.updatePosition();
      }
    }
  },

  beforeDestroy () {
    clearTimeout(this.inputTimer);
  }
});

var QSpace = Vue.extend({
  name: 'QSpace',

  render (h) {
    return h('div', {
      staticClass: 'q-space'
    })
  }
});

Vue.extend({
  name: 'QSpinnerAudio',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'fill': 'currentColor',
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 55 80',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('g', {
        attrs: {
          'transform': 'matrix(1 0 0 -1 0 80)'
        }
      }, [
        h('rect', {
          attrs: {
            'width': '10',
            'height': '20',
            'rx': '3'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'height',
              'begin': '0s',
              'dur': '4.3s',
              'values': '20;45;57;80;64;32;66;45;64;23;66;13;64;56;34;34;2;23;76;79;20',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('rect', {
          attrs: {
            'x': '15',
            'width': '10',
            'height': '80',
            'rx': '3'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'height',
              'begin': '0s',
              'dur': '2s',
              'values': '80;55;33;5;75;23;73;33;12;14;60;80',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('rect', {
          attrs: {
            'x': '30',
            'width': '10',
            'height': '50',
            'rx': '3'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'height',
              'begin': '0s',
              'dur': '1.4s',
              'values': '50;34;78;23;56;23;34;76;80;54;21;50',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('rect', {
          attrs: {
            'x': '45',
            'width': '10',
            'height': '30',
            'rx': '3'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'height',
              'begin': '0s',
              'dur': '2s',
              'values': '30;45;13;80;56;72;45;76;34;23;67;30',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerBall',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'stroke': 'currentColor',
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 57 57',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('g', {
        attrs: {
          'transform': 'translate(1 1)',
          'stroke-width': '2',
          'fill': 'none',
          'fill-rule': 'evenodd'
        }
      }, [
        h('circle', {
          attrs: {
            'cx': '5',
            'cy': '50',
            'r': '5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'cy',
              'begin': '0s',
              'dur': '2.2s',
              'values': '50;5;50;50',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'cx',
              'begin': '0s',
              'dur': '2.2s',
              'values': '5;27;49;5',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '27',
            'cy': '5',
            'r': '5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'cy',
              'begin': '0s',
              'dur': '2.2s',
              'from': '5',
              'to': '5',
              'values': '5;50;50;5',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'cx',
              'begin': '0s',
              'dur': '2.2s',
              'from': '27',
              'to': '27',
              'values': '27;49;5;27',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '49',
            'cy': '50',
            'r': '5'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'cy',
              'begin': '0s',
              'dur': '2.2s',
              'values': '50;50;5;50',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'cx',
              'from': '49',
              'to': '49',
              'begin': '0s',
              'dur': '2.2s',
              'values': '49;5;27;49',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerBars',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'fill': 'currentColor',
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 135 140',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('rect', {
        attrs: {
          'y': '10',
          'width': '15',
          'height': '120',
          'rx': '6'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'height',
            'begin': '0.5s',
            'dur': '1s',
            'values': '120;110;100;90;80;70;60;50;40;140;120',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        }),
        h('animate', {
          attrs: {
            'attributeName': 'y',
            'begin': '0.5s',
            'dur': '1s',
            'values': '10;15;20;25;30;35;40;45;50;0;10',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('rect', {
        attrs: {
          'x': '30',
          'y': '10',
          'width': '15',
          'height': '120',
          'rx': '6'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'height',
            'begin': '0.25s',
            'dur': '1s',
            'values': '120;110;100;90;80;70;60;50;40;140;120',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        }),
        h('animate', {
          attrs: {
            'attributeName': 'y',
            'begin': '0.25s',
            'dur': '1s',
            'values': '10;15;20;25;30;35;40;45;50;0;10',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('rect', {
        attrs: {
          'x': '60',
          'width': '15',
          'height': '140',
          'rx': '6'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'height',
            'begin': '0s',
            'dur': '1s',
            'values': '120;110;100;90;80;70;60;50;40;140;120',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        }),
        h('animate', {
          attrs: {
            'attributeName': 'y',
            'begin': '0s',
            'dur': '1s',
            'values': '10;15;20;25;30;35;40;45;50;0;10',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('rect', {
        attrs: {
          'x': '90',
          'y': '10',
          'width': '15',
          'height': '120',
          'rx': '6'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'height',
            'begin': '0.25s',
            'dur': '1s',
            'values': '120;110;100;90;80;70;60;50;40;140;120',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        }),
        h('animate', {
          attrs: {
            'attributeName': 'y',
            'begin': '0.25s',
            'dur': '1s',
            'values': '10;15;20;25;30;35;40;45;50;0;10',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('rect', {
        attrs: {
          'x': '120',
          'y': '10',
          'width': '15',
          'height': '120',
          'rx': '6'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'height',
            'begin': '0.5s',
            'dur': '1s',
            'values': '120;110;100;90;80;70;60;50;40;140;120',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        }),
        h('animate', {
          attrs: {
            'attributeName': 'y',
            'begin': '0.5s',
            'dur': '1s',
            'values': '10;15;20;25;30;35;40;45;50;0;10',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerComment',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'xmlns': 'http://www.w3.org/2000/svg',
        'viewBox': '0 0 100 100',
        'preserveAspectRatio': 'xMidYMid'
      }
    }, [
      h('rect', {
        attrs: {
          'x': '0',
          'y': '0',
          'width': '100',
          'height': '100',
          'fill': 'none'
        }
      }),
      h('path', {
        attrs: {
          'd': 'M78,19H22c-6.6,0-12,5.4-12,12v31c0,6.6,5.4,12,12,12h37.2c0.4,3,1.8,5.6,3.7,7.6c2.4,2.5,5.1,4.1,9.1,4 c-1.4-2.1-2-7.2-2-10.3c0-0.4,0-0.8,0-1.3h8c6.6,0,12-5.4,12-12V31C90,24.4,84.6,19,78,19z',
          'fill': 'currentColor'
        }
      }),
      h('circle', {
        attrs: {
          'cx': '30',
          'cy': '47',
          'r': '5',
          'fill': '#fff'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'opacity',
            'from': '0',
            'to': '1',
            'values': '0;1;1',
            'keyTimes': '0;0.2;1',
            'dur': '1s',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '50',
          'cy': '47',
          'r': '5',
          'fill': '#fff'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'opacity',
            'from': '0',
            'to': '1',
            'values': '0;0;1;1',
            'keyTimes': '0;0.2;0.4;1',
            'dur': '1s',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '70',
          'cy': '47',
          'r': '5',
          'fill': '#fff'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'opacity',
            'from': '0',
            'to': '1',
            'values': '0;0;1;1',
            'keyTimes': '0;0.4;0.6;1',
            'dur': '1s',
            'repeatCount': 'indefinite'
          }
        })
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerCube',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'xmlns': 'http://www.w3.org/2000/svg',
        'viewBox': '0 0 100 100',
        'preserveAspectRatio': 'xMidYMid'
      }
    }, [
      h('rect', {
        attrs: {
          'x': '0',
          'y': '0',
          'width': '100',
          'height': '100',
          'fill': 'none'
        }
      }),
      h('g', {
        attrs: {
          'transform': 'translate(25 25)'
        }
      }, [
        h('rect', {
          attrs: {
            'x': '-20',
            'y': '-20',
            'width': '40',
            'height': '40',
            'fill': 'currentColor',
            'opacity': '0.9'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'scale',
              'from': '1.5',
              'to': '1',
              'repeatCount': 'indefinite',
              'begin': '0s',
              'dur': '1s',
              'calcMode': 'spline',
              'keySplines': '0.2 0.8 0.2 0.8',
              'keyTimes': '0;1'
            }
          })
        ])
      ]),
      h('g', {
        attrs: {
          'transform': 'translate(75 25)'
        }
      }, [
        h('rect', {
          attrs: {
            'x': '-20',
            'y': '-20',
            'width': '40',
            'height': '40',
            'fill': 'currentColor',
            'opacity': '0.8'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'scale',
              'from': '1.5',
              'to': '1',
              'repeatCount': 'indefinite',
              'begin': '0.1s',
              'dur': '1s',
              'calcMode': 'spline',
              'keySplines': '0.2 0.8 0.2 0.8',
              'keyTimes': '0;1'
            }
          })
        ])
      ]),
      h('g', {
        attrs: {
          'transform': 'translate(25 75)'
        }
      }, [
        h('rect', {
          staticClass: 'cube',
          attrs: {
            'x': '-20',
            'y': '-20',
            'width': '40',
            'height': '40',
            'fill': 'currentColor',
            'opacity': '0.7'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'scale',
              'from': '1.5',
              'to': '1',
              'repeatCount': 'indefinite',
              'begin': '0.3s',
              'dur': '1s',
              'calcMode': 'spline',
              'keySplines': '0.2 0.8 0.2 0.8',
              'keyTimes': '0;1'
            }
          })
        ])
      ]),
      h('g', {
        attrs: {
          'transform': 'translate(75 75)'
        }
      }, [
        h('rect', {
          staticClass: 'cube',
          attrs: {
            'x': '-20',
            'y': '-20',
            'width': '40',
            'height': '40',
            'fill': 'currentColor',
            'opacity': '0.6'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'scale',
              'from': '1.5',
              'to': '1',
              'repeatCount': 'indefinite',
              'begin': '0.2s',
              'dur': '1s',
              'calcMode': 'spline',
              'keySplines': '0.2 0.8 0.2 0.8',
              'keyTimes': '0;1'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerDots',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'fill': 'currentColor',
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 120 30',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('circle', {
        attrs: {
          'cx': '15',
          'cy': '15',
          'r': '15'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'r',
            'from': '15',
            'to': '15',
            'begin': '0s',
            'dur': '0.8s',
            'values': '15;9;15',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        }),
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'from': '1',
            'to': '1',
            'begin': '0s',
            'dur': '0.8s',
            'values': '1;.5;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '60',
          'cy': '15',
          'r': '9',
          'fill-opacity': '.3'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'r',
            'from': '9',
            'to': '9',
            'begin': '0s',
            'dur': '0.8s',
            'values': '9;15;9',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        }),
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'from': '.5',
            'to': '.5',
            'begin': '0s',
            'dur': '0.8s',
            'values': '.5;1;.5',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '105',
          'cy': '15',
          'r': '15'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'r',
            'from': '15',
            'to': '15',
            'begin': '0s',
            'dur': '0.8s',
            'values': '15;9;15',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        }),
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'from': '1',
            'to': '1',
            'begin': '0s',
            'dur': '0.8s',
            'values': '1;.5;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerFacebook',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 100 100',
        'xmlns': 'http://www.w3.org/2000/svg',
        'preserveAspectRatio': 'xMidYMid'
      }
    }, [
      h('g', {
        attrs: {
          'transform': 'translate(20 50)'
        }
      }, [
        h('rect', {
          attrs: {
            'x': '-10',
            'y': '-30',
            'width': '20',
            'height': '60',
            'fill': 'currentColor',
            'opacity': '0.6'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'scale',
              'from': '2',
              'to': '1',
              'begin': '0s',
              'repeatCount': 'indefinite',
              'dur': '1s',
              'calcMode': 'spline',
              'keySplines': '0.1 0.9 0.4 1',
              'keyTimes': '0;1',
              'values': '2;1'
            }
          })
        ])
      ]),
      h('g', {
        attrs: {
          'transform': 'translate(50 50)'
        }
      }, [
        h('rect', {
          attrs: {
            'x': '-10',
            'y': '-30',
            'width': '20',
            'height': '60',
            'fill': 'currentColor',
            'opacity': '0.8'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'scale',
              'from': '2',
              'to': '1',
              'begin': '0.1s',
              'repeatCount': 'indefinite',
              'dur': '1s',
              'calcMode': 'spline',
              'keySplines': '0.1 0.9 0.4 1',
              'keyTimes': '0;1',
              'values': '2;1'
            }
          })
        ])
      ]),
      h('g', {
        attrs: {
          'transform': 'translate(80 50)'
        }
      }, [
        h('rect', {
          attrs: {
            'x': '-10',
            'y': '-30',
            'width': '20',
            'height': '60',
            'fill': 'currentColor',
            'opacity': '0.9'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'scale',
              'from': '2',
              'to': '1',
              'begin': '0.2s',
              'repeatCount': 'indefinite',
              'dur': '1s',
              'calcMode': 'spline',
              'keySplines': '0.1 0.9 0.4 1',
              'keyTimes': '0;1',
              'values': '2;1'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerGears',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 100 100',
        'preserveAspectRatio': 'xMidYMid',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('g', {
        attrs: {
          'transform': 'translate(-20,-20)'
        }
      }, [
        h('path', {
          attrs: {
            'd': 'M79.9,52.6C80,51.8,80,50.9,80,50s0-1.8-0.1-2.6l-5.1-0.4c-0.3-2.4-0.9-4.6-1.8-6.7l4.2-2.9c-0.7-1.6-1.6-3.1-2.6-4.5 L70,35c-1.4-1.9-3.1-3.5-4.9-4.9l2.2-4.6c-1.4-1-2.9-1.9-4.5-2.6L59.8,27c-2.1-0.9-4.4-1.5-6.7-1.8l-0.4-5.1C51.8,20,50.9,20,50,20 s-1.8,0-2.6,0.1l-0.4,5.1c-2.4,0.3-4.6,0.9-6.7,1.8l-2.9-4.1c-1.6,0.7-3.1,1.6-4.5,2.6l2.1,4.6c-1.9,1.4-3.5,3.1-5,4.9l-4.5-2.1 c-1,1.4-1.9,2.9-2.6,4.5l4.1,2.9c-0.9,2.1-1.5,4.4-1.8,6.8l-5,0.4C20,48.2,20,49.1,20,50s0,1.8,0.1,2.6l5,0.4 c0.3,2.4,0.9,4.7,1.8,6.8l-4.1,2.9c0.7,1.6,1.6,3.1,2.6,4.5l4.5-2.1c1.4,1.9,3.1,3.5,5,4.9l-2.1,4.6c1.4,1,2.9,1.9,4.5,2.6l2.9-4.1 c2.1,0.9,4.4,1.5,6.7,1.8l0.4,5.1C48.2,80,49.1,80,50,80s1.8,0,2.6-0.1l0.4-5.1c2.3-0.3,4.6-0.9,6.7-1.8l2.9,4.2 c1.6-0.7,3.1-1.6,4.5-2.6L65,69.9c1.9-1.4,3.5-3,4.9-4.9l4.6,2.2c1-1.4,1.9-2.9,2.6-4.5L73,59.8c0.9-2.1,1.5-4.4,1.8-6.7L79.9,52.6 z M50,65c-8.3,0-15-6.7-15-15c0-8.3,6.7-15,15-15s15,6.7,15,15C65,58.3,58.3,65,50,65z',
            'fill': 'currentColor'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'rotate',
              'from': '90 50 50',
              'to': '0 50 50',
              'dur': '1s',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ]),
      h('g', {
        attrs: {
          'transform': 'translate(20,20) rotate(15 50 50)'
        }
      }, [
        h('path', {
          attrs: {
            'd': 'M79.9,52.6C80,51.8,80,50.9,80,50s0-1.8-0.1-2.6l-5.1-0.4c-0.3-2.4-0.9-4.6-1.8-6.7l4.2-2.9c-0.7-1.6-1.6-3.1-2.6-4.5 L70,35c-1.4-1.9-3.1-3.5-4.9-4.9l2.2-4.6c-1.4-1-2.9-1.9-4.5-2.6L59.8,27c-2.1-0.9-4.4-1.5-6.7-1.8l-0.4-5.1C51.8,20,50.9,20,50,20 s-1.8,0-2.6,0.1l-0.4,5.1c-2.4,0.3-4.6,0.9-6.7,1.8l-2.9-4.1c-1.6,0.7-3.1,1.6-4.5,2.6l2.1,4.6c-1.9,1.4-3.5,3.1-5,4.9l-4.5-2.1 c-1,1.4-1.9,2.9-2.6,4.5l4.1,2.9c-0.9,2.1-1.5,4.4-1.8,6.8l-5,0.4C20,48.2,20,49.1,20,50s0,1.8,0.1,2.6l5,0.4 c0.3,2.4,0.9,4.7,1.8,6.8l-4.1,2.9c0.7,1.6,1.6,3.1,2.6,4.5l4.5-2.1c1.4,1.9,3.1,3.5,5,4.9l-2.1,4.6c1.4,1,2.9,1.9,4.5,2.6l2.9-4.1 c2.1,0.9,4.4,1.5,6.7,1.8l0.4,5.1C48.2,80,49.1,80,50,80s1.8,0,2.6-0.1l0.4-5.1c2.3-0.3,4.6-0.9,6.7-1.8l2.9,4.2 c1.6-0.7,3.1-1.6,4.5-2.6L65,69.9c1.9-1.4,3.5-3,4.9-4.9l4.6,2.2c1-1.4,1.9-2.9,2.6-4.5L73,59.8c0.9-2.1,1.5-4.4,1.8-6.7L79.9,52.6 z M50,65c-8.3,0-15-6.7-15-15c0-8.3,6.7-15,15-15s15,6.7,15,15C65,58.3,58.3,65,50,65z',
            'fill': 'currentColor'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'rotate',
              'from': '0 50 50',
              'to': '90 50 50',
              'dur': '1s',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerGrid',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'fill': 'currentColor',
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 105 105',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('circle', {
        attrs: {
          'cx': '12.5',
          'cy': '12.5',
          'r': '12.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '0s',
            'dur': '1s',
            'values': '1;.2;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '12.5',
          'cy': '52.5',
          'r': '12.5',
          'fill-opacity': '.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '100ms',
            'dur': '1s',
            'values': '1;.2;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '52.5',
          'cy': '12.5',
          'r': '12.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '300ms',
            'dur': '1s',
            'values': '1;.2;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '52.5',
          'cy': '52.5',
          'r': '12.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '600ms',
            'dur': '1s',
            'values': '1;.2;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '92.5',
          'cy': '12.5',
          'r': '12.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '800ms',
            'dur': '1s',
            'values': '1;.2;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '92.5',
          'cy': '52.5',
          'r': '12.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '400ms',
            'dur': '1s',
            'values': '1;.2;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '12.5',
          'cy': '92.5',
          'r': '12.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '700ms',
            'dur': '1s',
            'values': '1;.2;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '52.5',
          'cy': '92.5',
          'r': '12.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '500ms',
            'dur': '1s',
            'values': '1;.2;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('circle', {
        attrs: {
          'cx': '92.5',
          'cy': '92.5',
          'r': '12.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '200ms',
            'dur': '1s',
            'values': '1;.2;1',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerHearts',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'fill': 'currentColor',
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 140 64',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('path', {
        attrs: {
          'd': 'M30.262 57.02L7.195 40.723c-5.84-3.976-7.56-12.06-3.842-18.063 3.715-6 11.467-7.65 17.306-3.68l4.52 3.76 2.6-5.274c3.716-6.002 11.47-7.65 17.304-3.68 5.84 3.97 7.56 12.054 3.842 18.062L34.49 56.118c-.897 1.512-2.793 1.915-4.228.9z',
          'fill-opacity': '.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '0s',
            'dur': '1.4s',
            'values': '0.5;1;0.5',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('path', {
        attrs: {
          'd': 'M105.512 56.12l-14.44-24.272c-3.716-6.008-1.996-14.093 3.843-18.062 5.835-3.97 13.588-2.322 17.306 3.68l2.6 5.274 4.52-3.76c5.84-3.97 13.593-2.32 17.308 3.68 3.718 6.003 1.998 14.088-3.842 18.064L109.74 57.02c-1.434 1.014-3.33.61-4.228-.9z',
          'fill-opacity': '.5'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'fill-opacity',
            'begin': '0.7s',
            'dur': '1.4s',
            'values': '0.5;1;0.5',
            'calcMode': 'linear',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('path', {
        attrs: {
          'd': 'M67.408 57.834l-23.01-24.98c-5.864-6.15-5.864-16.108 0-22.248 5.86-6.14 15.37-6.14 21.234 0L70 16.168l4.368-5.562c5.863-6.14 15.375-6.14 21.235 0 5.863 6.14 5.863 16.098 0 22.247l-23.007 24.98c-1.43 1.556-3.757 1.556-5.188 0z'
        }
      })
    ])
  }
});

Vue.extend({
  name: 'QSpinnerHourglass',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 100 100',
        'preserveAspectRatio': 'xMidYMid',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('g', [
        h('path', {
          staticClass: 'glass',
          attrs: {
            'fill': 'none',
            'stroke': 'currentColor',
            'stroke-width': '5',
            'stroke-miterlimit': '10',
            'd': 'M58.4,51.7c-0.9-0.9-1.4-2-1.4-2.3s0.5-0.4,1.4-1.4 C70.8,43.8,79.8,30.5,80,15.5H70H30H20c0.2,15,9.2,28.1,21.6,32.3c0.9,0.9,1.4,1.2,1.4,1.5s-0.5,1.6-1.4,2.5 C29.2,56.1,20.2,69.5,20,85.5h10h40h10C79.8,69.5,70.8,55.9,58.4,51.7z'
          }
        }),
        h('clipPath', {
          attrs: {
            'id': 'uil-hourglass-clip1'
          }
        }, [
          h('rect', {
            staticClass: 'clip',
            attrs: {
              'x': '15',
              'y': '20',
              'width': '70',
              'height': '25'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'height',
                'from': '25',
                'to': '0',
                'dur': '1s',
                'repeatCount': 'indefinite',
                'vlaues': '25;0;0',
                'keyTimes': '0;0.5;1'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'y',
                'from': '20',
                'to': '45',
                'dur': '1s',
                'repeatCount': 'indefinite',
                'vlaues': '20;45;45',
                'keyTimes': '0;0.5;1'
              }
            })
          ])
        ]),
        h('clipPath', {
          attrs: {
            'id': 'uil-hourglass-clip2'
          }
        }, [
          h('rect', {
            staticClass: 'clip',
            attrs: {
              'x': '15',
              'y': '55',
              'width': '70',
              'height': '25'
            }
          }, [
            h('animate', {
              attrs: {
                'attributeName': 'height',
                'from': '0',
                'to': '25',
                'dur': '1s',
                'repeatCount': 'indefinite',
                'vlaues': '0;25;25',
                'keyTimes': '0;0.5;1'
              }
            }),
            h('animate', {
              attrs: {
                'attributeName': 'y',
                'from': '80',
                'to': '55',
                'dur': '1s',
                'repeatCount': 'indefinite',
                'vlaues': '80;55;55',
                'keyTimes': '0;0.5;1'
              }
            })
          ])
        ]),
        h('path', {
          staticClass: 'sand',
          attrs: {
            'd': 'M29,23c3.1,11.4,11.3,19.5,21,19.5S67.9,34.4,71,23H29z',
            'clip-path': 'url(#uil-hourglass-clip1)',
            'fill': 'currentColor'
          }
        }),
        h('path', {
          staticClass: 'sand',
          attrs: {
            'd': 'M71.6,78c-3-11.6-11.5-20-21.5-20s-18.5,8.4-21.5,20H71.6z',
            'clip-path': 'url(#uil-hourglass-clip2)',
            'fill': 'currentColor'
          }
        }),
        h('animateTransform', {
          attrs: {
            'attributeName': 'transform',
            'type': 'rotate',
            'from': '0 50 50',
            'to': '180 50 50',
            'repeatCount': 'indefinite',
            'dur': '1s',
            'values': '0 50 50;0 50 50;180 50 50',
            'keyTimes': '0;0.7;1'
          }
        })
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerInfinity',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 100 100',
        'preserveAspectRatio': 'xMidYMid'
      }
    }, [
      h('path', {
        attrs: {
          'd': 'M24.3,30C11.4,30,5,43.3,5,50s6.4,20,19.3,20c19.3,0,32.1-40,51.4-40C88.6,30,95,43.3,95,50s-6.4,20-19.3,20C56.4,70,43.6,30,24.3,30z',
          'fill': 'none',
          'stroke': 'currentColor',
          'stroke-width': '8',
          'stroke-dasharray': '10.691205342610678 10.691205342610678',
          'stroke-dashoffset': '0'
        }
      }, [
        h('animate', {
          attrs: {
            'attributeName': 'stroke-dashoffset',
            'from': '0',
            'to': '21.382410685221355',
            'begin': '0',
            'dur': '2s',
            'repeatCount': 'indefinite',
            'fill': 'freeze'
          }
        })
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerIos',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'stroke': 'currentColor',
        'fill': 'currentColor',
        'viewBox': '0 0 64 64'
      }
    }, [
      h('g', {
        attrs: {
          'stroke-width': '4',
          'stroke-linecap': 'round'
        }
      }, [
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(180)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '1;.85;.7;.65;.55;.45;.35;.25;.15;.1;0;1',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(210)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '0;1;.85;.7;.65;.55;.45;.35;.25;.15;.1;0',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(240)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '.1;0;1;.85;.7;.65;.55;.45;.35;.25;.15;.1',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(270)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '.15;.1;0;1;.85;.7;.65;.55;.45;.35;.25;.15',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(300)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '.25;.15;.1;0;1;.85;.7;.65;.55;.45;.35;.25',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(330)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '.35;.25;.15;.1;0;1;.85;.7;.65;.55;.45;.35',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(0)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '.45;.35;.25;.15;.1;0;1;.85;.7;.65;.55;.45',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(30)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '.55;.45;.35;.25;.15;.1;0;1;.85;.7;.65;.55',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(60)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '.65;.55;.45;.35;.25;.15;.1;0;1;.85;.7;.65',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(90)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '.7;.65;.55;.45;.35;.25;.15;.1;0;1;.85;.7',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(120)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '.85;.7;.65;.55;.45;.35;.25;.15;.1;0;1;.85',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('line', {
          attrs: {
            'y1': '17',
            'y2': '29',
            'transform': 'translate(32,32) rotate(150)'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'dur': '750ms',
              'values': '1;.85;.7;.65;.55;.45;.35;.25;.15;.1;0;1',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerOval',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'stroke': 'currentColor',
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 38 38',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('g', {
        attrs: {
          'transform': 'translate(1 1)',
          'stroke-width': '2',
          'fill': 'none',
          'fill-rule': 'evenodd'
        }
      }, [
        h('circle', {
          attrs: {
            'stroke-opacity': '.5',
            'cx': '18',
            'cy': '18',
            'r': '18'
          }
        }),
        h('path', {
          attrs: {
            'd': 'M36 18c0-9.94-8.06-18-18-18'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'rotate',
              'from': '0 18 18',
              'to': '360 18 18',
              'dur': '1s',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerPie',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 100 100',
        'preserveAspectRatio': 'xMidYMid',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('path', {
        attrs: {
          'd': 'M0 50A50 50 0 0 1 50 0L50 50L0 50',
          'fill': 'currentColor',
          'opacity': '0.5'
        }
      }, [
        h('animateTransform', {
          attrs: {
            'attributeName': 'transform',
            'type': 'rotate',
            'from': '0 50 50',
            'to': '360 50 50',
            'dur': '0.8s',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('path', {
        attrs: {
          'd': 'M50 0A50 50 0 0 1 100 50L50 50L50 0',
          'fill': 'currentColor',
          'opacity': '0.5'
        }
      }, [
        h('animateTransform', {
          attrs: {
            'attributeName': 'transform',
            'type': 'rotate',
            'from': '0 50 50',
            'to': '360 50 50',
            'dur': '1.6s',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('path', {
        attrs: {
          'd': 'M100 50A50 50 0 0 1 50 100L50 50L100 50',
          'fill': 'currentColor',
          'opacity': '0.5'
        }
      }, [
        h('animateTransform', {
          attrs: {
            'attributeName': 'transform',
            'type': 'rotate',
            'from': '0 50 50',
            'to': '360 50 50',
            'dur': '2.4s',
            'repeatCount': 'indefinite'
          }
        })
      ]),
      h('path', {
        attrs: {
          'd': 'M50 100A50 50 0 0 1 0 50L50 50L50 100',
          'fill': 'currentColor',
          'opacity': '0.5'
        }
      }, [
        h('animateTransform', {
          attrs: {
            'attributeName': 'transform',
            'type': 'rotate',
            'from': '0 50 50',
            'to': '360 50 50',
            'dur': '3.2s',
            'repeatCount': 'indefinite'
          }
        })
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerPuff',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'stroke': 'currentColor',
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 44 44',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('g', {
        attrs: {
          'fill': 'none',
          'fill-rule': 'evenodd',
          'stroke-width': '2'
        }
      }, [
        h('circle', {
          attrs: {
            'cx': '22',
            'cy': '22',
            'r': '1'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'r',
              'begin': '0s',
              'dur': '1.8s',
              'values': '1; 20',
              'calcMode': 'spline',
              'keyTimes': '0; 1',
              'keySplines': '0.165, 0.84, 0.44, 1',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'begin': '0s',
              'dur': '1.8s',
              'values': '1; 0',
              'calcMode': 'spline',
              'keyTimes': '0; 1',
              'keySplines': '0.3, 0.61, 0.355, 1',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '22',
            'cy': '22',
            'r': '1'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'r',
              'begin': '-0.9s',
              'dur': '1.8s',
              'values': '1; 20',
              'calcMode': 'spline',
              'keyTimes': '0; 1',
              'keySplines': '0.165, 0.84, 0.44, 1',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'begin': '-0.9s',
              'dur': '1.8s',
              'values': '1; 0',
              'calcMode': 'spline',
              'keyTimes': '0; 1',
              'keySplines': '0.3, 0.61, 0.355, 1',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerRadio',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 100 100',
        'preserveAspectRatio': 'xMidYMid',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('g', {
        attrs: {
          'transform': 'scale(0.55)'
        }
      }, [
        h('circle', {
          attrs: {
            'cx': '30',
            'cy': '150',
            'r': '30',
            'fill': 'currentColor'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'opacity',
              'from': '0',
              'to': '1',
              'dur': '1s',
              'begin': '0',
              'repeatCount': 'indefinite',
              'keyTimes': '0;0.5;1',
              'values': '0;1;1'
            }
          })
        ]),
        h('path', {
          attrs: {
            'd': 'M90,150h30c0-49.7-40.3-90-90-90v30C63.1,90,90,116.9,90,150z',
            'fill': 'currentColor'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'opacity',
              'from': '0',
              'to': '1',
              'dur': '1s',
              'begin': '0.1',
              'repeatCount': 'indefinite',
              'keyTimes': '0;0.5;1',
              'values': '0;1;1'
            }
          })
        ]),
        h('path', {
          attrs: {
            'd': 'M150,150h30C180,67.2,112.8,0,30,0v30C96.3,30,150,83.7,150,150z',
            'fill': 'currentColor'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'opacity',
              'from': '0',
              'to': '1',
              'dur': '1s',
              'begin': '0.2',
              'repeatCount': 'indefinite',
              'keyTimes': '0;0.5;1',
              'values': '0;1;1'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerRings',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'stroke': 'currentColor',
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 45 45',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('g', {
        attrs: {
          'fill': 'none',
          'fill-rule': 'evenodd',
          'transform': 'translate(1 1)',
          'stroke-width': '2'
        }
      }, [
        h('circle', {
          attrs: {
            'cx': '22',
            'cy': '22',
            'r': '6'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'r',
              'begin': '1.5s',
              'dur': '3s',
              'values': '6;22',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'begin': '1.5s',
              'dur': '3s',
              'values': '1;0',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'stroke-width',
              'begin': '1.5s',
              'dur': '3s',
              'values': '2;0',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '22',
            'cy': '22',
            'r': '6'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'r',
              'begin': '3s',
              'dur': '3s',
              'values': '6;22',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'stroke-opacity',
              'begin': '3s',
              'dur': '3s',
              'values': '1;0',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          }),
          h('animate', {
            attrs: {
              'attributeName': 'stroke-width',
              'begin': '3s',
              'dur': '3s',
              'values': '2;0',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'cx': '22',
            'cy': '22',
            'r': '8'
          }
        }, [
          h('animate', {
            attrs: {
              'attributeName': 'r',
              'begin': '0s',
              'dur': '1.5s',
              'values': '6;1;2;3;4;5;6',
              'calcMode': 'linear',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSpinnerTail',

  mixins: [mixin$1],

  render (h) {
    return h('svg', {
      staticClass: 'q-spinner',
      class: this.classes,
      on: this.$listeners,
      attrs: {
        'width': this.size,
        'height': this.size,
        'viewBox': '0 0 38 38',
        'xmlns': 'http://www.w3.org/2000/svg'
      }
    }, [
      h('defs', [
        h('linearGradient', {
          attrs: {
            'x1': '8.042%',
            'y1': '0%',
            'x2': '65.682%',
            'y2': '23.865%',
            'id': 'a'
          }
        }, [
          h('stop', {
            attrs: {
              'stop-color': 'currentColor',
              'stop-opacity': '0',
              'offset': '0%'
            }
          }),
          h('stop', {
            attrs: {
              'stop-color': 'currentColor',
              'stop-opacity': '.631',
              'offset': '63.146%'
            }
          }),
          h('stop', {
            attrs: {
              'stop-color': 'currentColor',
              'offset': '100%'
            }
          })
        ])
      ]),
      h('g', {
        attrs: {
          'transform': 'translate(1 1)',
          'fill': 'none',
          'fill-rule': 'evenodd'
        }
      }, [
        h('path', {
          attrs: {
            'd': 'M36 18c0-9.94-8.06-18-18-18',
            'stroke': 'url(#a)',
            'stroke-width': '2'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'rotate',
              'from': '0 18 18',
              'to': '360 18 18',
              'dur': '0.9s',
              'repeatCount': 'indefinite'
            }
          })
        ]),
        h('circle', {
          attrs: {
            'fill': 'currentColor',
            'cx': '36',
            'cy': '18',
            'r': '1'
          }
        }, [
          h('animateTransform', {
            attrs: {
              'attributeName': 'transform',
              'type': 'rotate',
              'from': '0 18 18',
              'to': '360 18 18',
              'dur': '0.9s',
              'repeatCount': 'indefinite'
            }
          })
        ])
      ])
    ])
  }
});

Vue.extend({
  name: 'QSplitter',

  directives: {
    TouchPan
  },

  props: {
    value: {
      type: Number,
      required: true
    },
    horizontal: Boolean,

    limits: {
      type: Array,
      default: () => [10, 90],
      validator: v => {
        if (v.length !== 2) return false
        if (typeof v[0] !== 'number' || typeof v[1] !== 'number') return false
        return v[0] >= 0 && v[0] <= v[1] && v[1] <= 100
      }
    },

    disable: Boolean,

    dark: Boolean,

    beforeClass: [Array, String, Object],
    afterClass: [Array, String, Object],

    separatorClass: [Array, String, Object],
    separatorStyle: [Array, String, Object]
  },

  watch: {
    value: {
      immediate: true,
      handler (v) {
        this.__normalize(v, this.limits);
      }
    },

    limits: {
      deep: true,
      handler (v) {
        this.__normalize(this.value, v);
      }
    }
  },

  computed: {
    classes () {
      return (this.horizontal ? 'column' : 'row') +
        ` q-splitter--${this.horizontal ? 'horizontal' : 'vertical'}` +
        ` q-splitter--${this.disable === true ? 'disabled' : 'workable'}` +
        (this.dark === true ? ' q-splitter--dark' : '')
    },

    prop () {
      return this.horizontal ? 'height' : 'width'
    },

    beforeStyle () {
      return { [this.prop]: this.value + '%' }
    },

    afterStyle () {
      return { [this.prop]: (100 - this.value) + '%' }
    }
  },

  methods: {
    __pan (evt) {
      if (evt.isFirst) {
        this.__size = this.$el.getBoundingClientRect()[this.prop];
        this.__value = this.value;
        this.__dir = this.horizontal ? 'up' : 'left';
        this.__rtlDir = this.horizontal ? 1 : (this.$q.lang.rtl === true ? -1 : 1);

        this.$el.classList.add('q-splitter--active');
        return
      }

      if (evt.isFinal) {
        if (this.__normalized !== this.value) {
          this.$emit('input', this.__normalized);
        }

        this.$el.classList.remove('q-splitter--active');
        return
      }

      const val = this.__value +
        this.__rtlDir *
        (evt.direction === this.__dir ? -100 : 100) *
        evt.distance[this.horizontal ? 'y' : 'x'] / this.__size;

      this.__normalized = Math.min(this.limits[1], Math.max(this.limits[0], val));
      this.$refs.before.style[this.prop] = this.__normalized + '%';
      this.$refs.after.style[this.prop] = (100 - this.__normalized) + '%';
    },

    __normalize (val, limits) {
      if (val < limits[0]) {
        this.$emit('input', limits[0]);
      }
      else if (val > limits[1]) {
        this.$emit('input', limits[1]);
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-splitter no-wrap',
      class: this.classes,
      on: this.$listeners
    }, [
      h('div', {
        ref: 'before',
        staticClass: 'q-splitter__panel q-splitter__before',
        style: this.beforeStyle,
        class: this.beforeClass,
        on: { input: stop }
      }, slot(this, 'before')),

      h('div', {
        staticClass: 'q-splitter__separator',
        style: this.separatorStyle,
        class: this.separatorClass
      }, [
        h('div', {
          staticClass: 'absolute-full q-splitter__separator-area',
          directives: this.disable === true ? void 0 : [{
            name: 'touch-pan',
            value: this.__pan,
            modifiers: {
              horizontal: !this.horizontal,
              vertical: this.horizontal,
              prevent: true,
              mouse: true,
              mouseAllDir: true,
              mousePrevent: true
            }
          }]
        }, slot(this, 'separator'))
      ]),

      h('div', {
        ref: 'after',
        staticClass: 'q-splitter__panel q-splitter__after',
        style: this.afterStyle,
        class: this.afterClass,
        on: { input: stop }
      }, slot(this, 'after'))
    ].concat(slot(this, 'default')))
  }
});

var StepHeader = Vue.extend({
  name: 'StepHeader',

  directives: {
    Ripple
  },

  props: {
    stepper: {},
    step: {}
  },

  computed: {
    isActive () {
      return this.stepper.value === this.step.name
    },

    isDisable () {
      const opt = this.step.disable;
      return opt === true || opt === ''
    },

    isError () {
      const opt = this.step.error;
      return opt === true || opt === ''
    },

    isDone () {
      const opt = this.step.done;
      return !this.isDisable && (opt === true || opt === '')
    },

    headerNav () {
      const
        opt = this.step.headerNav,
        nav = opt === true || opt === '' || opt === void 0;

      return !this.isDisable && this.stepper.headerNav && (this.isActive || nav)
    },

    hasPrefix () {
      return this.step.prefix && !this.isActive && !this.isError && !this.isDone
    },

    icon () {
      if (this.isActive) {
        return this.step.activeIcon || this.stepper.activeIcon || this.$q.iconSet.stepper.active
      }
      if (this.isError) {
        return this.step.errorIcon || this.stepper.errorIcon || this.$q.iconSet.stepper.error
      }
      if (!this.isDisable && this.isDone) {
        return this.step.doneIcon || this.stepper.doneIcon || this.$q.iconSet.stepper.done
      }

      return this.step.icon || this.stepper.inactiveIcon
    },

    color () {
      if (this.isActive) {
        return this.step.activeColor || this.stepper.activeColor || this.step.color
      }
      if (this.isError) {
        return this.step.errorColor || this.stepper.errorColor
      }
      if (!this.disable && this.isDone) {
        return this.step.doneColor || this.stepper.doneColor || this.step.color || this.stepper.inactiveColor
      }

      return this.step.color || this.stepper.inactiveColor
    },

    classes () {
      return {
        [`text-${this.color}`]: this.color,
        'q-stepper__tab--error': this.isError,
        'q-stepper__tab--active': this.isActive,
        'q-stepper__tab--done': this.isDone,
        'q-stepper__tab--navigation q-focusable q-hoverable': this.headerNav,
        'q-stepper__tab--disabled': this.isDisable
      }
    }
  },

  methods: {
    activate () {
      this.$refs.blurTarget !== void 0 && this.$refs.blurTarget.focus();
      !this.isActive && this.stepper.goTo(this.step.name);
    },
    keyup (e) {
      e.keyCode === 13 && !this.isActive && this.stepper.goTo(this.step.name);
    }
  },

  render (h) {
    const data = {
      staticClass: 'q-stepper__tab col-grow flex items-center no-wrap relative-position',
      class: this.classes,
      directives: this.stepper.headerNav ? [{
        name: 'ripple',
        value: this.headerNav
      }] : null
    };

    if (this.headerNav) {
      data.on = {
        click: this.activate,
        keyup: this.keyup
      };
      data.attrs = { tabindex: this.isDisable === true ? -1 : this.$attrs.tabindex || 0 };
    }

    return h('div', data, [
      h('div', { staticClass: 'q-focus-helper', attrs: { tabindex: -1 }, ref: 'blurTarget' }),

      h('div', { staticClass: 'q-stepper__dot row flex-center q-stepper__line relative-position' }, [
        h('span', { staticClass: 'row flex-center' }, [
          this.hasPrefix === true
            ? this.step.prefix
            : h(QIcon, { props: { name: this.icon } })
        ])
      ]),

      this.step.title
        ? h('div', {
          staticClass: 'q-stepper__label q-stepper__line relative-position'
        }, [
          h('div', { staticClass: 'q-stepper__title' }, [ this.step.title ]),
          this.step.caption
            ? h('div', { staticClass: 'q-stepper__caption' }, [ this.step.caption ])
            : null
        ])
        : null
    ])
  }
});

const StepWrapper = Vue.extend({
  name: 'QStepWrapper',

  render (h) {
    return h('div', {
      staticClass: 'q-stepper__step-content'
    }, [
      h('div', {
        staticClass: 'q-stepper__step-inner'
      }, slot(this, 'default'))
    ])
  }
});

Vue.extend({
  name: 'QStep',

  inject: {
    stepper: {
      default () {
        console.error('QStep needs to be child of QStepper');
      }
    }
  },

  mixins: [ PanelChildMixin ],

  props: {
    icon: String,
    color: String,
    title: {
      type: String,
      required: true
    },
    caption: String,
    prefix: [ String, Number ],

    doneIcon: String,
    doneColor: String,
    activeIcon: String,
    activeColor: String,
    errorIcon: String,
    errorColor: String,

    headerNav: {
      type: Boolean,
      default: true
    },
    done: Boolean,
    error: Boolean
  },

  computed: {
    isActive () {
      return this.stepper.value === this.name
    }
  },

  render (h) {
    const vertical = this.stepper.vertical;
    const content = vertical === true && this.stepper.keepAlive === true
      ? h(
        'keep-alive',
        this.isActive === true
          ? [ h(StepWrapper, { key: this.name }, slot(this, 'default')) ]
          : void 0
      )
      : (
        vertical !== true || this.isActive === true
          ? StepWrapper.options.render.call(this, h)
          : void 0
      );

    return h(
      'div',
      {
        staticClass: 'q-stepper__step',
        on: this.$listeners
      },
      vertical === true
        ? [
          h(StepHeader, {
            props: {
              stepper: this.stepper,
              step: this
            }
          }),

          this.stepper.animated === true
            ? h(QSlideTransition, [ content ])
            : content
        ]
        : [ content ]
    )
  }
});

Vue.extend({
  name: 'QStepper',

  provide () {
    return {
      stepper: this
    }
  },

  mixins: [ PanelParentMixin ],

  props: {
    dark: Boolean,

    flat: Boolean,
    bordered: Boolean,
    vertical: Boolean,
    alternativeLabels: Boolean,
    headerNav: Boolean,
    contracted: Boolean,

    inactiveColor: String,
    inactiveIcon: String,
    doneIcon: String,
    doneColor: String,
    activeIcon: String,
    activeColor: String,
    errorIcon: String,
    errorColor: String
  },

  computed: {
    classes () {
      return `q-stepper--${this.vertical ? 'vertical' : 'horizontal'}` +
        (this.flat || this.dark ? ' q-stepper--flat no-shadow' : '') +
        (this.bordered || (this.dark && !this.flat) ? ' q-stepper--bordered' : '') +
        (this.contracted === true ? ' q-stepper--contracted' : '') +
        (this.dark === true ? ' q-stepper--dark' : '')
    }
  },

  methods: {
    __getContent (h) {
      const top = slot(this, 'message');

      if (this.vertical === true) {
        this.__isValidPanelName(this.value) && this.__updatePanelIndex();

        return (top !== void 0 ? top : []).concat([
          h('div', {
            staticClass: 'q-stepper__content',
            // stop propagation of content emitted @input
            // which would tamper with Panel's model
            on: { input: stop }
          }, slot(this, 'default'))
        ])
      }

      return [
        h('div', {
          staticClass: 'q-stepper__header row items-stretch justify-between',
          class: {
            [`q-stepper__header--${this.alternativeLabels ? 'alternative' : 'standard'}-labels`]: true,
            'q-stepper__header--border': !this.flat || this.bordered
          }
        }, this.__getAllPanels().map(panel => {
          const step = panel.componentOptions.propsData;

          return h(StepHeader, {
            key: step.name,
            props: {
              stepper: this,
              step
            }
          })
        }))
      ].concat((top !== void 0 ? top : [])).concat([
        h('div', {
          staticClass: 'q-stepper__content q-panel-parent',
          directives: this.panelDirectives
        }, [
          this.__getPanelContent(h)
        ])
      ])
    },

    __render (h) {
      return h('div', {
        staticClass: 'q-stepper',
        class: this.classes,
        on: this.$listeners
      }, this.__getContent(h).concat(slot(this, 'navigation')))
    }
  }
});

Vue.extend({
  name: 'QStepperNavigation',

  render (h) {
    return h('div', {
      staticClass: 'q-stepper__nav',
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

var Top = {
  computed: {
    marginalsProps () {
      return {
        pagination: this.computedPagination,
        pagesNumber: this.pagesNumber,
        isFirstPage: this.isFirstPage,
        isLastPage: this.isLastPage,
        prevPage: this.prevPage,
        nextPage: this.nextPage,

        inFullscreen: this.inFullscreen,
        toggleFullscreen: this.toggleFullscreen
      }
    }
  },

  methods: {
    getTop (h) {
      const
        top = this.$scopedSlots.top,
        topLeft = this.$scopedSlots['top-left'],
        topRight = this.$scopedSlots['top-right'],
        topSelection = this.$scopedSlots['top-selection'],
        hasSelection = this.hasSelectionMode === true &&
          topSelection !== void 0 &&
          this.rowsSelectedNumber > 0,
        staticClass = 'q-table__top relative-position row items-center',
        child = [];

      if (top !== void 0) {
        return h('div', { staticClass }, [ top(this.marginalsProps) ])
      }

      if (hasSelection === true) {
        child.push(topSelection(this.marginalsProps));
      }
      else {
        if (topLeft !== void 0) {
          child.push(
            h('div', { staticClass: 'q-table-control' }, [
              topLeft(this.marginalsProps)
            ])
          );
        }
        else if (this.title) {
          child.push(
            h('div', { staticClass: 'q-table__control' }, [
              h('div', { staticClass: 'q-table__title' }, this.title)
            ])
          );
        }
      }

      if (topRight !== void 0) {
        child.push(h('div', { staticClass: 'q-table__separator col' }));
        child.push(
          h('div', { staticClass: 'q-table__control' }, [
            topRight(this.marginalsProps)
          ])
        );
      }

      if (child.length === 0) {
        return
      }

      return h('div', { staticClass }, child)
    }
  }
};

var QTh = Vue.extend({
  name: 'QTh',

  props: {
    props: Object,
    autoWidth: Boolean
  },

  render (h) {
    if (this.props === void 0) {
      return h('td', {
        class: this.autoWidth === true ? 'q-table--col-auto-width' : null
      }, slot(this, 'default'))
    }

    let col;
    const
      name = this.$vnode.key,
      child = [].concat(slot(this, 'default'));

    if (name) {
      col = this.props.colsMap[name];
      if (col === void 0) { return }
    }
    else {
      col = this.props.col;
    }

    if (col.sortable === true) {
      const action = col.align === 'right'
        ? 'unshift'
        : 'push';

      child[action](
        h(QIcon, {
          props: { name: this.$q.iconSet.table.arrowUp },
          staticClass: col.__iconClass
        })
      );
    }

    return h('th', {
      class: col.__thClass +
        (this.autoWidth === true ? ' q-table--col-auto-width' : ''),
      on: col.sortable === true
        ? { click: () => { this.props.sort(col); } }
        : null
    }, child)
  }
});

var TableHeader = {
  methods: {
    getTableHeader (h) {
      const child = [ this.getTableHeaderRow(h) ];

      this.loading === true && child.push(
        h('tr', { staticClass: 'q-table__progress' }, [
          h('td', { staticClass: 'relative-position', attrs: { colspan: '100%' } }, [
            h(QLinearProgress, {
              staticClass: 'q-table__linear-progress',
              props: {
                color: this.color,
                dark: this.dark,
                indeterminate: true
              }
            })
          ])
        ])
      );

      return h('thead', child)
    },

    getTableHeaderRow (h) {
      const
        header = this.$scopedSlots.header,
        headerCell = this.$scopedSlots['header-cell'];

      if (header !== void 0) {
        return header(this.addTableHeaderRowMeta({
          header: true, cols: this.computedCols, sort: this.sort, colsMap: this.computedColsMap
        }))
      }

      let mapFn;

      if (headerCell !== void 0) {
        mapFn = col => headerCell({
          col, cols: this.computedCols, sort: this.sort, colsMap: this.computedColsMap
        });
      }
      else {
        mapFn = col => h(QTh, {
          key: col.name,
          props: {
            props: {
              col,
              cols: this.computedCols,
              sort: this.sort,
              colsMap: this.computedColsMap
            }
          },
          style: col.style,
          class: col.classes
        }, col.label);
      }
      const child = this.computedCols.map(mapFn);

      if (this.singleSelection === true && this.grid !== true) {
        child.unshift(h('th', { staticClass: 'q-table--col-auto-width' }, [' ']));
      }
      else if (this.multipleSelection === true) {
        child.unshift(h('th', { staticClass: 'q-table--col-auto-width' }, [
          h(QCheckbox, {
            props: {
              color: this.color,
              value: this.someRowsSelected ? null : this.allRowsSelected,
              dark: this.dark,
              dense: this.dense
            },
            on: {
              input: val => {
                if (this.someRowsSelected) {
                  val = false;
                }
                this.__updateSelection(
                  this.computedRows.map(row => row[this.rowKey]),
                  this.computedRows,
                  val
                );
              }
            }
          })
        ]));
      }

      return h('tr', {
        style: this.tableHeaderStyle,
        class: this.tableHeaderClass
      }, child)
    },

    addTableHeaderRowMeta (data) {
      if (this.multipleSelection === true) {
        Object.defineProperty(data, 'selected', {
          get: () => this.someRowsSelected ? 'some' : this.allRowsSelected,
          set: val => {
            if (this.someRowsSelected) {
              val = false;
            }
            this.__updateSelection(
              this.computedRows.map(row => row[this.rowKey]),
              this.computedRows,
              val
            );
          }
        });
        data.partialSelected = this.someRowsSelected;
        data.multipleSelect = true;
      }

      return data
    }
  }
};

var TableBody = {
  methods: {
    getTableBody (h) {
      const
        body = this.$scopedSlots.body,
        bodyCell = this.$scopedSlots['body-cell'],
        topRow = this.$scopedSlots['top-row'],
        bottomRow = this.$scopedSlots['bottom-row'];
      let
        child = [];

      if (body !== void 0) {
        child = this.computedRows.map(row => {
          const
            key = row[this.rowKey],
            selected = this.isRowSelected(key);

          return body(this.addBodyRowMeta({
            key,
            row,
            cols: this.computedCols,
            colsMap: this.computedColsMap,
            __trClass: selected ? 'selected' : ''
          }))
        });
      }
      else {
        child = this.computedRows.map(row => {
          const
            key = row[this.rowKey],
            selected = this.isRowSelected(key),
            child = bodyCell
              ? this.computedCols.map(col => bodyCell(this.addBodyCellMetaData({ row, col })))
              : this.computedCols.map(col => {
                const slot = this.$scopedSlots[`body-cell-${col.name}`];
                return slot !== void 0
                  ? slot(this.addBodyCellMetaData({ row, col: col }))
                  : h('td', {
                    staticClass: col.__tdClass,
                    style: col.style,
                    class: col.classes
                  }, this.getCellValue(col, row))
              });

          this.hasSelectionMode === true && child.unshift(
            h('td', { staticClass: 'q-table--col-auto-width' }, [
              h(QCheckbox, {
                props: {
                  value: selected,
                  color: this.color,
                  dark: this.dark,
                  dense: this.dense
                },
                on: {
                  input: adding => {
                    this.__updateSelection([key], [row], adding);
                  }
                }
              })
            ])
          );

          return h('tr', { key, class: { selected } }, child)
        });
      }

      if (topRow !== void 0) {
        child.unshift(topRow({ cols: this.computedCols }));
      }
      if (bottomRow !== void 0) {
        child.push(bottomRow({ cols: this.computedCols }));
      }

      return h('tbody', child)
    },

    addBodyRowMeta (data) {
      this.hasSelectionMode === true && Object.defineProperty(data, 'selected', {
        get: () => this.isRowSelected(data.key),
        set: adding => {
          this.__updateSelection([data.key], [data.row], adding);
        }
      });

      Object.defineProperty(data, 'expand', {
        get: () => this.rowsExpanded[data.key] === true,
        set: val => {
          this.$set(this.rowsExpanded, data.key, val);
        }
      });

      data.cols = data.cols.map(col => {
        const c = { ...col };
        Object.defineProperty(c, 'value', {
          get: () => this.getCellValue(col, data.row)
        });
        return c
      });

      return data
    },

    addBodyCellMetaData (data) {
      Object.defineProperty(data, 'value', {
        get: () => this.getCellValue(data.col, data.row)
      });
      return data
    },

    getCellValue (col, row) {
      const val = typeof col.field === 'function' ? col.field(row) : row[col.field];
      return col.format !== void 0 ? col.format(val, row) : val
    }
  }
};

var Bottom = {
  computed: {
    navIcon () {
      const ico = [ this.$q.iconSet.table.prevPage, this.$q.iconSet.table.nextPage ];
      return this.$q.lang.rtl === true ? ico.reverse() : ico
    }
  },

  methods: {
    getBottom (h) {
      if (this.hideBottom === true) {
        return
      }

      if (this.nothingToDisplay === true) {
        const message = this.filter
          ? this.noResultsLabel || this.$q.lang.table.noResults
          : (this.loading === true ? this.loadingLabel || this.$q.lang.table.loading : this.noDataLabel || this.$q.lang.table.noData);

        return h('div', { staticClass: 'q-table__bottom row items-center q-table__bottom--nodata' }, [
          h(QIcon, { props: { name: this.$q.iconSet.table.warning } }),
          message
        ])
      }

      const bottom = this.$scopedSlots.bottom;

      return h('div', {
        staticClass: 'q-table__bottom row items-center',
        class: bottom !== void 0 ? null : 'justify-end'
      }, bottom !== void 0 ? [ bottom(this.marginalsProps) ] : this.getPaginationRow(h))
    },

    getPaginationRow (h) {
      const
        { rowsPerPage } = this.computedPagination,
        paginationLabel = this.paginationLabel || this.$q.lang.table.pagination,
        paginationSlot = this.$scopedSlots.pagination;

      return [
        h('div', { staticClass: 'q-table__control' }, [
          h('div', [
            this.hasSelectionMode === true && this.rowsSelectedNumber > 0
              ? (this.selectedRowsLabel || this.$q.lang.table.selectedRecords)(this.rowsSelectedNumber)
              : ''
          ])
        ]),

        h('div', { staticClass: 'q-table__separator col' }),

        this.rowsPerPageOptions.length > 1
          ? h('div', { staticClass: 'q-table__control' }, [
            h('span', { staticClass: 'q-table__bottom-item' }, [
              this.rowsPerPageLabel || this.$q.lang.table.recordsPerPage
            ]),
            h(QSelect, {
              staticClass: 'inline q-table__bottom-item',
              props: {
                color: this.color,
                value: rowsPerPage,
                options: this.computedRowsPerPageOptions,
                displayValue: rowsPerPage === 0
                  ? this.$q.lang.table.allRows
                  : rowsPerPage,
                dark: this.dark,
                borderless: true,
                dense: true,
                optionsDense: true
              },
              on: {
                input: pag => {
                  this.setPagination({
                    page: 1,
                    rowsPerPage: pag.value
                  });
                }
              }
            })
          ])
          : null,

        h('div', { staticClass: 'q-table__control' }, [
          paginationSlot !== void 0
            ? paginationSlot(this.marginalsProps)
            : [
              h('span', { staticClass: 'q-table__bottom-item' }, [
                rowsPerPage
                  ? paginationLabel(this.firstRowIndex + 1, Math.min(this.lastRowIndex, this.computedRowsNumber), this.computedRowsNumber)
                  : paginationLabel(1, this.computedRowsNumber, this.computedRowsNumber)
              ]),
              h(QBtn, {
                props: {
                  color: this.color,
                  round: true,
                  icon: this.navIcon[0],
                  dense: true,
                  flat: true,
                  disable: this.isFirstPage
                },
                on: { click: this.prevPage }
              }),
              h(QBtn, {
                props: {
                  color: this.color,
                  round: true,
                  icon: this.navIcon[1],
                  dense: true,
                  flat: true,
                  disable: this.isLastPage
                },
                on: { click: this.nextPage }
              })
            ]
        ])
      ]
    }
  }
};

var TableGrid = {
  methods: {
    getTableGrid (h) {
      const item = this.$scopedSlots.item !== void 0
        ? this.$scopedSlots.item
        : scope => {
          const child = scope.cols.map(
            col => h('div', { staticClass: 'q-table__grid-item-row' }, [
              h('div', { staticClass: 'q-table__grid-item-title' }, [ col.label ]),
              h('div', { staticClass: 'q-table__grid-item-value' }, [ col.value ])
            ])
          );

          this.hasSelectionMode === true && child.unshift(
            h('div', { staticClass: 'q-table__grid-item-row' }, [
              h(QCheckbox, {
                props: {
                  value: scope.selected,
                  color: this.color,
                  dark: this.dark,
                  dense: true
                },
                on: {
                  input: val => {
                    scope.selected = val;
                  }
                }
              })
            ]),

            h(QSeparator, { props: { dark: this.dark } })
          );

          return h('div', {
            staticClass: 'q-table__grid-item col-xs-12 col-sm-6 col-md-4 col-lg-3',
            class: scope.selected === true ? 'q-table__grid-item--selected' : null
          }, [
            h('div', {
              staticClass: 'q-table__grid-item-card' + this.cardDefaultClass,
              class: this.cardClass,
              style: this.cardStyle
            }, child)
          ])
        };

      return [
        this.hideHeader === false
          ? h('div', { staticClass: 'q-table__middle' }, [
            this.loading === true
              ? h(QLinearProgress, {
                staticClass: 'q-table__linear-progress',
                props: {
                  color: this.color,
                  dark: this.dark,
                  indeterminate: true
                }
              })
              : null
          ])
          : null,

        h('div', { staticClass: 'row' }, this.computedRows.map(row => {
          const
            key = row[this.rowKey],
            selected = this.isRowSelected(key);

          return item(this.addBodyRowMeta({
            key,
            row,
            cols: this.computedCols,
            colsMap: this.computedColsMap,
            __trClass: selected ? 'selected' : ''
          }))
        }))
      ]
    }
  }
};

function sortDate (a, b) {
  return (new Date(a)) - (new Date(b))
}

var Sort = {
  props: {
    sortMethod: {
      type: Function,
      default (data, sortBy, descending) {
        const col = this.columns.find(def => def.name === sortBy);
        if (col === null || col.field === void 0) {
          return data
        }

        const
          dir = descending === true ? -1 : 1,
          val = typeof col.field === 'function'
            ? v => col.field(v)
            : v => v[col.field];

        return data.sort((a, b) => {
          let
            A = val(a),
            B = val(b);

          if (A === null || A === void 0) {
            return -1 * dir
          }
          if (B === null || B === void 0) {
            return 1 * dir
          }
          if (col.sort !== void 0) {
            return col.sort(A, B, a, b) * dir
          }
          if (isNumber(A) === true && isNumber(B) === true) {
            return (A - B) * dir
          }
          if (isDate(A) === true && isDate(B) === true) {
            return sortDate(A, B) * dir
          }
          if (typeof A === 'boolean' && typeof B === 'boolean') {
            return (a - b) * dir
          }

          [A, B] = [A, B].map(s => (s + '').toLocaleString().toLowerCase());

          return A < B
            ? -1 * dir
            : (A === B ? 0 : dir)
        })
      }
    }
  },

  computed: {
    columnToSort () {
      const { sortBy } = this.computedPagination;

      if (sortBy) {
        return this.columns.find(def => def.name === sortBy) || null
      }
    }
  },

  methods: {
    sort (col /* String(col name) or Object(col definition) */) {
      if (col === Object(col)) {
        col = col.name;
      }

      let { sortBy, descending } = this.computedPagination;

      if (sortBy !== col) {
        sortBy = col;
        descending = false;
      }
      else {
        if (this.binaryStateSort === true) {
          descending = !descending;
        }
        else {
          if (descending === true) {
            sortBy = null;
          }
          else {
            descending = true;
          }
        }
      }

      this.setPagination({ sortBy, descending, page: 1 });
    }
  }
};

var Filter = {
  props: {
    filter: [String, Object],
    filterMethod: {
      type: Function,
      default (rows, terms, cols = this.computedCols, cellValue = this.getCellValue) {
        const lowerTerms = terms ? terms.toLowerCase() : '';
        return rows.filter(
          row => cols.some(col => (cellValue(col, row) + '').toLowerCase().indexOf(lowerTerms) !== -1)
        )
      }
    }
  },

  watch: {
    filter () {
      this.$nextTick(() => {
        this.setPagination({ page: 1 }, true);
      });
    }
  }
};

function samePagination (oldPag, newPag) {
  for (let prop in newPag) {
    if (newPag[prop] !== oldPag[prop]) {
      return false
    }
  }
  return true
}

function fixPagination (p) {
  if (p.page < 1) {
    p.page = 1;
  }
  if (p.rowsPerPage !== void 0 && p.rowsPerPage < 1) {
    p.rowsPerPage = 0;
  }
  return p
}

var Pagination = {
  props: {
    pagination: Object,
    rowsPerPageOptions: {
      type: Array,
      default: () => [3, 5, 7, 10, 15, 20, 25, 50, 0]
    }
  },

  computed: {
    computedPagination () {
      return fixPagination({
        ...this.innerPagination,
        ...this.pagination
      })
    },

    firstRowIndex () {
      const { page, rowsPerPage } = this.computedPagination;
      return (page - 1) * rowsPerPage
    },

    lastRowIndex () {
      const { page, rowsPerPage } = this.computedPagination;
      return page * rowsPerPage
    },

    isFirstPage () {
      return this.computedPagination.page === 1
    },

    pagesNumber () {
      return Math.max(
        1,
        Math.ceil(this.computedRowsNumber / this.computedPagination.rowsPerPage)
      )
    },

    isLastPage () {
      return this.lastRowIndex === 0
        ? true
        : this.computedPagination.page >= this.pagesNumber
    },

    computedRowsPerPageOptions () {
      return this.rowsPerPageOptions.map(count => ({
        label: count === 0 ? this.$q.lang.table.allRows : '' + count,
        value: count
      }))
    }
  },

  watch: {
    pagesNumber (lastPage, oldLastPage) {
      if (lastPage === oldLastPage) {
        return
      }

      const currentPage = this.computedPagination.page;
      if (lastPage && !currentPage) {
        this.setPagination({ page: 1 });
      }
      else if (lastPage < currentPage) {
        this.setPagination({ page: lastPage });
      }
    }
  },

  methods: {
    __sendServerRequest (pagination) {
      this.requestServerInteraction({
        pagination,
        filter: this.filter
      });
    },

    setPagination (val, forceServerRequest) {
      const newPagination = fixPagination({
        ...this.computedPagination,
        ...val
      });

      if (samePagination(this.computedPagination, newPagination)) {
        if (this.isServerSide && forceServerRequest) {
          this.__sendServerRequest(newPagination);
        }
        return
      }

      if (this.isServerSide) {
        this.__sendServerRequest(newPagination);
        return
      }

      if (this.pagination) {
        this.$emit('update:pagination', newPagination);
      }
      else {
        this.innerPagination = newPagination;
      }
    },

    prevPage () {
      const { page } = this.computedPagination;
      if (page > 1) {
        this.setPagination({ page: page - 1 });
      }
    },

    nextPage () {
      const { page, rowsPerPage } = this.computedPagination;
      if (this.lastRowIndex > 0 && page * rowsPerPage < this.computedRowsNumber) {
        this.setPagination({ page: page + 1 });
      }
    }
  },

  created () {
    this.$emit('update:pagination', { ...this.computedPagination });
  }
};

var RowSelection = {
  props: {
    selection: {
      type: String,
      default: 'none',
      validator: v => ['single', 'multiple', 'none'].includes(v)
    },
    selected: {
      type: Array,
      default: () => []
    }
  },

  computed: {
    selectedKeys () {
      const keys = {};
      this.selected.map(row => row[this.rowKey]).forEach(key => {
        keys[key] = true;
      });
      return keys
    },

    hasSelectionMode () {
      return this.selection !== 'none'
    },

    singleSelection () {
      return this.selection === 'single'
    },

    multipleSelection () {
      return this.selection === 'multiple'
    },

    allRowsSelected () {
      if (this.multipleSelection === true) {
        return this.computedRows.length > 0 && this.computedRows.every(row => this.selectedKeys[row[this.rowKey]] === true)
      }
    },

    someRowsSelected () {
      if (this.multipleSelection === true) {
        return !this.allRowsSelected && this.computedRows.some(row => this.selectedKeys[row[this.rowKey]] === true)
      }
    },

    rowsSelectedNumber () {
      return this.selected.length
    }
  },

  methods: {
    isRowSelected (key) {
      return this.selectedKeys[key] === true
    },

    clearSelection () {
      this.$emit('update:selected', []);
    },

    __updateSelection (keys, rows, added) {
      this.$emit('selection', { rows, added, keys });

      const payload = this.singleSelection === true
        ? (added === true ? rows : [])
        : (
          added === true
            ? this.selected.concat(rows)
            : this.selected.filter(
              row => keys.includes(row[this.rowKey]) === false
            )
        );

      this.$emit('update:selected', payload);
    }
  }
};

var ColumnSelection = {
  props: {
    visibleColumns: Array
  },

  computed: {
    computedCols () {
      let { sortBy, descending } = this.computedPagination;

      const cols = this.visibleColumns !== void 0
        ? this.columns.filter(col => col.required === true || this.visibleColumns.includes(col.name) === true)
        : this.columns;

      return cols.map(col => {
        col.align = col.align || 'right';
        col.__iconClass = `q-table__sort-icon q-table__sort-icon--${col.align}`;
        col.__thClass = `text-${col.align}${col.sortable ? ' sortable' : ''}${col.name === sortBy ? ` sorted ${descending ? 'sort-desc' : ''}` : ''}`;
        col.__tdClass = `text-${col.align}`;
        return col
      })
    },

    computedColsMap () {
      const names = {};
      this.computedCols.forEach(col => {
        names[col.name] = col;
      });
      return names
    }
  }
};

var QTable = Vue.extend({
  name: 'QTable',

  mixins: [
    FullscreenMixin,
    Top,
    TableHeader,
    TableBody,
    Bottom,
    TableGrid,
    Sort,
    Filter,
    Pagination,
    RowSelection,
    ColumnSelection
  ],

  props: {
    data: {
      type: Array,
      default: () => []
    },
    rowKey: {
      type: String,
      default: 'id'
    },

    columns: Array,
    loading: Boolean,
    binaryStateSort: Boolean,

    title: String,

    hideHeader: Boolean,
    hideBottom: Boolean,

    grid: Boolean,
    dense: Boolean,
    flat: Boolean,
    bordered: Boolean,
    square: Boolean,
    separator: {
      type: String,
      default: 'horizontal',
      validator: v => ['horizontal', 'vertical', 'cell', 'none'].includes(v)
    },
    wrapCells: Boolean,

    noDataLabel: String,
    noResultsLabel: String,
    loadingLabel: String,
    selectedRowsLabel: Function,
    rowsPerPageLabel: String,
    paginationLabel: Function,

    color: {
      type: String,
      default: 'grey-8'
    },

    tableStyle: [String, Array, Object],
    tableClass: [String, Array, Object],
    tableHeaderStyle: [String, Array, Object],
    tableHeaderClass: [String, Array, Object],
    cardStyle: [String, Array, Object],
    cardClass: [String, Array, Object],

    dark: Boolean
  },

  data () {
    return {
      rowsExpanded: {},
      innerPagination: {
        sortBy: null,
        descending: false,
        page: 1,
        rowsPerPage: 5
      }
    }
  },

  computed: {
    computedData () {
      let rows = this.data.slice().map((row, i) => {
        row.__index = i;
        return row
      });

      if (rows.length === 0) {
        return {
          rowsNumber: 0,
          rows: []
        }
      }

      if (this.isServerSide === true) {
        return { rows }
      }

      const { sortBy, descending, rowsPerPage } = this.computedPagination;

      if (this.filter) {
        rows = this.filterMethod(rows, this.filter, this.computedCols, this.getCellValue);
      }

      if (this.columnToSort) {
        rows = this.sortMethod(rows, sortBy, descending);
      }

      const rowsNumber = rows.length;

      if (rowsPerPage) {
        rows = rows.slice(this.firstRowIndex, this.lastRowIndex);
      }

      return { rowsNumber, rows }
    },

    computedRows () {
      return this.computedData.rows
    },

    computedRowsNumber () {
      return this.isServerSide === true
        ? this.computedPagination.rowsNumber || 0
        : this.computedData.rowsNumber
    },

    nothingToDisplay () {
      return this.computedRows.length === 0
    },

    isServerSide () {
      return this.computedPagination.rowsNumber !== void 0
    },

    cardDefaultClass () {
      return ` q-table__card` +
        (this.dark === true ? ' q-table__card--dark' : '') +
        (this.square === true ? ` q-table--square` : '') +
        (this.flat === true ? ` q-table--flat` : '') +
        (this.bordered === true ? ` q-table--bordered` : '')
    },

    containerClass () {
      return `q-table__container q-table--${this.separator}-separator` +
        (this.grid === true ? ' q-table--grid' : this.cardDefaultClass) +
        (this.dark === true ? ` q-table--dark` : '') +
        (this.dense === true ? ` q-table--dense` : '') +
        (this.wrapCells === false ? ` q-table--no-wrap` : '') +
        (this.inFullscreen === true ? ` fullscreen scroll` : '')
    }
  },

  render (h) {
    const data = { staticClass: this.containerClass };

    if (this.grid === false) {
      data.class = this.cardClass;
      data.style = this.cardStyle;
    }

    return h('div', data, [
      this.getTop(h),
      this.getBody(h),
      this.getBottom(h)
    ])
  },

  methods: {
    requestServerInteraction (prop = {}) {
      this.$nextTick(() => {
        this.$emit('request', {
          pagination: prop.pagination || this.computedPagination,
          filter: prop.filter || this.filter,
          getCellValue: this.getCellValue
        });
      });
    },

    getBody (h) {
      if (this.grid === true) {
        return this.getTableGrid(h)
      }

      return h('div', {
        staticClass: 'q-table__middle scroll',
        class: this.tableClass,
        style: this.tableStyle
      }, [
        h('table', { staticClass: 'q-table' }, [
          this.hideHeader !== true ? this.getTableHeader(h) : null,
          this.getTableBody(h)
        ])
      ])
    }
  }
});

Vue.extend({
  name: 'QTr',

  props: {
    props: Object
  },

  render (h) {
    return h(
      'tr',
      this.props === void 0 || this.props.header === true
        ? {}
        : { class: this.props.__trClass },
      slot(this, 'default')
    )
  }
});

Vue.extend({
  name: 'QTd',

  props: {
    props: Object,
    autoWidth: Boolean
  },

  render (h) {
    if (this.props === void 0) {
      return h('td', {
        class: { 'q-table--col-auto-width': this.autoWidth }
      }, slot(this, 'default'))
    }

    let col;
    const name = this.$vnode.key;

    if (name) {
      col = this.props.colsMap[name];
      if (col === void 0) { return }
    }
    else {
      col = this.props.col;
    }

    return h('td', {
      class: col.__tdClass +
        (this.autoWidth === true ? ' q-table--col-auto-width' : '')
    }, slot(this, 'default'))
  }
});

Vue.extend({
  name: 'QMarkupTable',

  props: {
    dense: Boolean,
    dark: Boolean,
    flat: Boolean,
    bordered: Boolean,
    square: Boolean,
    separator: {
      type: String,
      default: 'horizontal',
      validator: v => ['horizontal', 'vertical', 'cell', 'none'].includes(v)
    },
    wrapCells: Boolean
  },

  computed: {
    classes () {
      return `q-table--${this.separator}-separator` +
        (this.dark === true ? ` q-table--dark q-table__card--dark` : '') +
        (this.dense === true ? ` q-table--dense` : '') +
        (this.flat === true ? ` q-table--flat` : '') +
        (this.bordered === true ? ` q-table--bordered` : '') +
        (this.square === true ? ` q-table--square` : '') +
        (this.wrapCells === false ? ` q-table--no-wrap` : '')
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-markup-table q-table__container q-table__card',
      class: this.classes,
      on: this.$listeners
    }, [
      h('table', { staticClass: 'q-table' }, slot(this, 'default'))
    ])
  }
});

const trailingSlashRE = /\/?$/;

function queryIncludes (current, target) {
  for (const key in target) {
    if (!(key in current)) {
      return false
    }
  }
  return true
}

function isSameRoute (current, target) {
  if (!target) {
    return false
  }
  if (current.path && target.path) {
    return (
      current.path.replace(trailingSlashRE, '') === target.path.replace(trailingSlashRE, '') &&
      current.hash === target.hash &&
      isDeepEqual(current.query, target.query)
    )
  }
  if (current.name && target.name) {
    return (
      current.name === target.name &&
      current.hash === target.hash &&
      isDeepEqual(current.query, target.query) &&
      isDeepEqual(current.params, target.params)
    )
  }
  return false
}

function isIncludedRoute (current, target) {
  return (
    current.path.replace(trailingSlashRE, '/').indexOf(target.path.replace(trailingSlashRE, '/')) === 0 &&
    (!target.hash || current.hash === target.hash) &&
    queryIncludes(current.query, target.query)
  )
}

Vue.extend({
  name: 'QRouteTab',

  mixins: [ QTab, RouterLinkMixin ],

  props: {
    to: { required: true }
  },

  inject: {
    __activateRoute: {}
  },

  watch: {
    $route () {
      this.__checkActivation();
    }
  },

  methods: {
    activate (e, keyboard) {
      if (this.disable !== true) {
        this.__checkActivation(true);
      }

      if (keyboard === true) {
        this.$el.focus();
      }
      else {
        this.$refs.blurTarget !== void 0 && this.$refs.blurTarget.focus();
      }
    },

    __checkActivation (selected = false) {
      const
        current = this.$route,
        { href, location, route } = this.$router.resolve(this.to, current, this.append),
        redirected = route.redirectedFrom !== void 0,
        checkFunction = this.exact === true ? isSameRoute : isIncludedRoute,
        params = {
          name: this.name,
          selected,
          exact: this.exact,
          priorityMatched: route.matched.length,
          priorityHref: href.length
        };

      checkFunction(current, route) && this.__activateRoute({ ...params, redirected });
      redirected === true && checkFunction(current, {
        path: route.redirectedFrom,
        ...location
      }) && this.__activateRoute(params);
      this.isActive && this.__activateRoute();
    }
  },

  mounted () {
    this.$router !== void 0 && this.__checkActivation();
  },

  beforeDestroy () {
    this.__activateRoute({ remove: true, name: this.name });
  },

  render (h) {
    return this.__render(h, 'router-link', this.routerLinkProps)
  }
});

Vue.extend({
  name: 'QTimeline',

  provide () {
    return {
      __timeline: this
    }
  },

  props: {
    color: {
      type: String,
      default: 'primary'
    },
    side: {
      type: String,
      default: 'right',
      validator: v => ['left', 'right'].includes(v)
    },
    layout: {
      type: String,
      default: 'dense',
      validator: v => ['dense', 'comfortable', 'loose'].includes(v)
    },
    dark: Boolean
  },

  computed: {
    classes () {
      return {
        'q-timeline--dark': this.dark,
        [`q-timeline--${this.layout}`]: true,
        [`q-timeline--${this.layout}--${this.side}`]: true
      }
    }
  },

  render (h) {
    return h('ul', {
      staticClass: 'q-timeline',
      class: this.classes,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

Vue.extend({
  name: 'QTimelineEntry',

  inject: {
    __timeline: {
      default () {
        console.error('QTimelineEntry needs to be child of QTimeline');
      }
    }
  },

  props: {
    heading: Boolean,
    tag: {
      type: String,
      default: 'h3'
    },
    side: {
      type: String,
      default: 'right',
      validator: v => ['left', 'right'].includes(v)
    },

    icon: String,
    avatar: String,

    color: String,

    title: String,
    subtitle: String,
    body: String
  },

  computed: {
    colorClass () {
      return `text-${this.color || this.__timeline.color}`
    },

    classes () {
      return `q-timeline__entry--${this.side}` +
        (this.icon !== void 0 || this.avatar !== void 0 ? ' q-timeline__entry--icon' : '')
    },

    reverse () {
      return this.__timeline.layout === 'comfortable' && this.__timeline.side === 'left'
    }
  },

  render (h) {
    const defSlot = this.$scopedSlots.default !== void 0
      ? this.$scopedSlots.default()
      : [];

    if (this.body !== void 0) {
      defSlot.unshift(this.body);
    }

    if (this.heading === true) {
      const content = [
        h('div'),
        h('div'),
        h(
          this.tag,
          { staticClass: 'q-timeline__heading-title' },
          defSlot
        )
      ];

      return h('div', {
        staticClass: 'q-timeline__heading',
        on: this.$listeners
      }, this.reverse === true ? content.reverse() : content)
    }

    let dot;

    if (this.icon !== void 0) {
      dot = [
        h(QIcon, {
          staticClass: 'row items-center justify-center',
          props: { name: this.icon }
        })
      ];
    }
    else if (this.avatar !== void 0) {
      dot = [
        h('img', {
          staticClass: 'q-timeline__dot-img',
          domProps: { src: this.avatar }
        })
      ];
    }

    const content = [
      h('div', { staticClass: 'q-timeline__subtitle' }, [
        h(
          'span',
          this.$scopedSlots.subtitle !== void 0
            ? this.$scopedSlots.subtitle()
            : [ this.subtitle ]
        )
      ]),

      h('div', {
        staticClass: 'q-timeline__dot',
        class: this.colorClass
      }, dot),

      h('div', { staticClass: 'q-timeline__content' }, [
        h(
          'h6',
          { staticClass: 'q-timeline__title' },
          this.$scopedSlots.title !== void 0
            ? this.$scopedSlots.title()
            : [ this.title ]
        )
      ].concat(defSlot))
    ];

    return h('li', {
      staticClass: 'q-timeline__entry',
      class: this.classes,
      on: this.$listeners
    }, this.reverse === true ? content.reverse() : content)
  }
});

var QToolbar = Vue.extend({
  name: 'QToolbar',

  props: {
    inset: Boolean
  },

  render (h) {
    return h('div', {
      staticClass: 'q-toolbar row no-wrap items-center',
      class: this.inset ? 'q-toolbar--inset' : null,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

var QToolbarTitle = Vue.extend({
  name: 'QToolbarTitle',

  props: {
    shrink: Boolean
  },

  render (h) {
    return h('div', {
      staticClass: 'q-toolbar__title ellipsis',
      class: this.shrink === true ? 'col-shrink' : null,
      on: this.$listeners
    }, slot(this, 'default'))
  }
});

Vue.extend({
  name: 'QTree',

  props: {
    nodes: {
      type: Array,
      required: true
    },
    nodeKey: {
      type: String,
      required: true
    },
    labelKey: {
      type: String,
      default: 'label'
    },

    color: String,
    controlColor: String,
    textColor: String,
    selectedColor: String,
    dark: Boolean,

    icon: String,

    tickStrategy: {
      type: String,
      default: 'none',
      validator: v => ['none', 'strict', 'leaf', 'leaf-filtered'].includes(v)
    },
    ticked: Array, // sync
    expanded: Array, // sync
    selected: {}, // sync

    defaultExpandAll: Boolean,
    accordion: Boolean,

    filter: String,
    filterMethod: {
      type: Function,
      default (node, filter) {
        const filt = filter.toLowerCase();
        return node[this.labelKey] &&
          node[this.labelKey].toLowerCase().indexOf(filt) > -1
      }
    },

    duration: Number,

    noNodesLabel: String,
    noResultsLabel: String
  },

  computed: {
    classes () {
      return {
        [`text-${this.color}`]: this.color,
        'q-tree--dark': this.dark
      }
    },

    hasSelection () {
      return this.selected !== void 0
    },

    computedIcon () {
      return this.icon || this.$q.iconSet.tree.icon
    },

    computedControlColor () {
      return this.controlColor || this.color
    },

    textColorClass () {
      if (this.textColor !== void 0) {
        return `text-${this.textColor}`
      }
    },

    selectedColorClass () {
      const color = this.selectedColor || this.color;
      if (color) {
        return `text-${color}`
      }
    },

    meta () {
      const meta = {};

      const travel = (node, parent) => {
        const tickStrategy = node.tickStrategy || (parent ? parent.tickStrategy : this.tickStrategy);
        const
          key = node[this.nodeKey],
          isParent = node.children && node.children.length > 0,
          isLeaf = !isParent,
          selectable = !node.disabled && this.hasSelection && node.selectable !== false,
          expandable = !node.disabled && node.expandable !== false,
          hasTicking = tickStrategy !== 'none',
          strictTicking = tickStrategy === 'strict',
          leafFilteredTicking = tickStrategy === 'leaf-filtered',
          leafTicking = tickStrategy === 'leaf' || tickStrategy === 'leaf-filtered';

        let tickable = !node.disabled && node.tickable !== false;
        if (leafTicking && tickable && parent && !parent.tickable) {
          tickable = false;
        }

        let lazy = node.lazy;
        if (lazy && this.lazy[key]) {
          lazy = this.lazy[key];
        }

        const m = {
          key,
          parent,
          isParent,
          isLeaf,
          lazy,
          disabled: node.disabled,
          link: !node.disabled && (selectable || (expandable && (isParent || lazy === true))),
          children: [],
          matchesFilter: this.filter ? this.filterMethod(node, this.filter) : true,

          selected: key === this.selected && selectable,
          selectable,
          expanded: isParent ? this.innerExpanded.includes(key) : false,
          expandable,
          noTick: node.noTick || (!strictTicking && lazy && lazy !== 'loaded'),
          tickable,
          tickStrategy,
          hasTicking,
          strictTicking,
          leafFilteredTicking,
          leafTicking,
          ticked: strictTicking
            ? this.innerTicked.includes(key)
            : (isLeaf ? this.innerTicked.includes(key) : false)
        };

        meta[key] = m;

        if (isParent) {
          m.children = node.children.map(n => travel(n, m));

          if (this.filter) {
            if (!m.matchesFilter) {
              m.matchesFilter = m.children.some(n => n.matchesFilter);
            }
            if (
              m.matchesFilter &&
              !m.noTick &&
              !m.disabled &&
              m.tickable &&
              leafFilteredTicking &&
              m.children.every(n => !n.matchesFilter || n.noTick || !n.tickable)
            ) {
              m.tickable = false;
            }
          }

          if (m.matchesFilter) {
            if (!m.noTick && !strictTicking && m.children.every(n => n.noTick)) {
              m.noTick = true;
            }

            if (leafTicking) {
              m.ticked = false;
              m.indeterminate = m.children.some(node => node.indeterminate);

              if (!m.indeterminate) {
                const sel = m.children
                  .reduce((acc, meta) => meta.ticked ? acc + 1 : acc, 0);

                if (sel === m.children.length) {
                  m.ticked = true;
                }
                else if (sel > 0) {
                  m.indeterminate = true;
                }
              }
            }
          }
        }

        return m
      };

      this.nodes.forEach(node => travel(node, null));
      return meta
    }
  },

  data () {
    return {
      lazy: {},
      innerTicked: this.ticked || [],
      innerExpanded: this.expanded || []
    }
  },

  watch: {
    ticked (val) {
      this.innerTicked = val;
    },

    expanded (val) {
      this.innerExpanded = val;
    }
  },

  methods: {
    getNodeByKey (key) {
      const reduce = [].reduce;

      const find = (result, node) => {
        if (result || !node) {
          return result
        }
        if (Array.isArray(node)) {
          return reduce.call(Object(node), find, result)
        }
        if (node[this.nodeKey] === key) {
          return node
        }
        if (node.children) {
          return find(null, node.children)
        }
      };

      return find(null, this.nodes)
    },

    getTickedNodes () {
      return this.innerTicked.map(key => this.getNodeByKey(key))
    },

    getExpandedNodes () {
      return this.innerExpanded.map(key => this.getNodeByKey(key))
    },

    isExpanded (key) {
      return key && this.meta[key]
        ? this.meta[key].expanded
        : false
    },

    collapseAll () {
      if (this.expanded !== void 0) {
        this.$emit('update:expanded', []);
      }
      else {
        this.innerExpanded = [];
      }
    },

    expandAll () {
      const
        expanded = this.innerExpanded,
        travel = node => {
          if (node.children && node.children.length > 0) {
            if (node.expandable !== false && node.disabled !== true) {
              expanded.push(node[this.nodeKey]);
              node.children.forEach(travel);
            }
          }
        };

      this.nodes.forEach(travel);

      if (this.expanded !== void 0) {
        this.$emit('update:expanded', expanded);
      }
      else {
        this.innerExpanded = expanded;
      }
    },

    setExpanded (key, state, node = this.getNodeByKey(key), meta = this.meta[key]) {
      if (meta.lazy && meta.lazy !== 'loaded') {
        if (meta.lazy === 'loading') {
          return
        }

        this.$set(this.lazy, key, 'loading');
        this.$emit('lazy-load', {
          node,
          key,
          done: children => {
            this.lazy[key] = 'loaded';
            if (children) {
              this.$set(node, 'children', children);
            }
            this.$nextTick(() => {
              const m = this.meta[key];
              if (m && m.isParent) {
                this.__setExpanded(key, true);
              }
            });
          },
          fail: () => {
            this.$delete(this.lazy, key);
          }
        });
      }
      else if (meta.isParent && meta.expandable) {
        this.__setExpanded(key, state);
      }
    },

    __setExpanded (key, state) {
      let target = this.innerExpanded;
      const emit = this.expanded !== void 0;

      if (emit === true) {
        target = target.slice();
      }

      if (state) {
        if (this.accordion) {
          if (this.meta[key]) {
            const collapse = [];
            if (this.meta[key].parent) {
              this.meta[key].parent.children.forEach(m => {
                if (m.key !== key && m.expandable) {
                  collapse.push(m.key);
                }
              });
            }
            else {
              this.nodes.forEach(node => {
                const k = node[this.nodeKey];
                if (k !== key) {
                  collapse.push(k);
                }
              });
            }
            if (collapse.length > 0) {
              target = target.filter(k => !collapse.includes(k));
            }
          }
        }

        target = target.concat([ key ])
          .filter((key, index, self) => self.indexOf(key) === index);
      }
      else {
        target = target.filter(k => k !== key);
      }

      if (emit === true) {
        this.$emit(`update:expanded`, target);
      }
      else {
        this.innerExpanded = target;
      }
    },

    isTicked (key) {
      return key && this.meta[key]
        ? this.meta[key].ticked
        : false
    },

    setTicked (keys, state) {
      let target = this.innerTicked;
      const emit = this.ticked !== void 0;

      if (emit === true) {
        target = target.slice();
      }

      if (state) {
        target = target.concat(keys)
          .filter((key, index, self) => self.indexOf(key) === index);
      }
      else {
        target = target.filter(k => !keys.includes(k));
      }

      if (emit === true) {
        this.$emit(`update:ticked`, target);
      }
    },

    __getSlotScope (node, meta, key) {
      const scope = { tree: this, node, key, color: this.color, dark: this.dark };

      Object.defineProperty(scope, 'expanded', {
        get: () => { return meta.expanded },
        set: val => { val !== meta.expanded && this.setExpanded(key, val); }
      });
      Object.defineProperty(scope, 'ticked', {
        get: () => { return meta.ticked },
        set: val => { val !== meta.ticked && this.setTicked([ key ], val); }
      });

      return scope
    },

    __getChildren (h, nodes) {
      return (
        this.filter
          ? nodes.filter(n => this.meta[n[this.nodeKey]].matchesFilter)
          : nodes
      ).map(child => this.__getNode(h, child))
    },

    __getNodeMedia (h, node) {
      if (node.icon !== void 0) {
        return h(QIcon, {
          staticClass: `q-tree__icon q-mr-sm`,
          props: { name: node.icon, color: node.iconColor }
        })
      }
      const src = node.img || node.avatar;
      if (src) {
        return h('img', {
          staticClass: `q-tree__${node.img ? 'img' : 'avatar'} q-mr-sm`,
          attrs: { src }
        })
      }
    },

    __getNode (h, node) {
      const
        key = node[this.nodeKey],
        meta = this.meta[key],
        header = node.header
          ? this.$scopedSlots[`header-${node.header}`] || this.$scopedSlots['default-header']
          : this.$scopedSlots['default-header'];

      const children = meta.isParent
        ? this.__getChildren(h, node.children)
        : [];

      const isParent = children.length > 0 || (meta.lazy && meta.lazy !== 'loaded');

      let
        body = node.body
          ? this.$scopedSlots[`body-${node.body}`] || this.$scopedSlots['default-body']
          : this.$scopedSlots['default-body'],
        slotScope = header || body
          ? this.__getSlotScope(node, meta, key)
          : null;

      if (body !== void 0) {
        body = h('div', { staticClass: 'q-tree__node-body relative-position' }, [
          h('div', { class: this.textColorClass }, [
            body(slotScope)
          ])
        ]);
      }

      return h('div', {
        key,
        staticClass: 'q-tree__node relative-position',
        class: { 'q-tree__node--parent': isParent, 'q-tree__node--child': !isParent }
      }, [
        h('div', {
          staticClass: 'q-tree__node-header relative-position row no-wrap items-center',
          class: {
            'q-tree__node--link q-hoverable q-focusable': meta.link,
            'q-tree__node--selected': meta.selected,
            disabled: meta.disabled
          },
          attrs: { tabindex: meta.link ? 0 : -1 },
          on: {
            click: (e) => {
              this.__onClick(node, meta, e);
            },
            keypress: e => {
              if (e.keyCode === 13) { this.__onClick(node, meta, e, true); }
              else if (e.keyCode === 32) { this.__onExpandClick(node, meta, e, true); }
            }
          }
        }, [
          h('div', { staticClass: 'q-focus-helper', attrs: { tabindex: -1 }, ref: `blurTarget_${meta.key}` }),

          meta.lazy === 'loading'
            ? h(QSpinner, {
              staticClass: 'q-tree__spinner q-mr-xs',
              props: { color: this.computedControlColor }
            })
            : (
              isParent === true
                ? h(QIcon, {
                  staticClass: 'q-tree__arrow q-mr-xs',
                  class: { 'q-tree__arrow--rotate': meta.expanded },
                  props: { name: this.computedIcon },
                  nativeOn: {
                    click: e => {
                      this.__onExpandClick(node, meta, e);
                    }
                  }
                })
                : null
            ),

          meta.hasTicking && !meta.noTick
            ? h(QCheckbox, {
              staticClass: 'q-mr-xs',
              props: {
                value: meta.indeterminate ? null : meta.ticked,
                color: this.computedControlColor,
                dark: this.dark,
                dense: true,
                keepColor: true,
                disable: !meta.tickable
              },
              on: {
                keydown: stopAndPrevent,
                input: v => {
                  this.__onTickedClick(meta, v);
                }
              }
            })
            : null,

          h('div', {
            'staticClass': 'q-tree__node-header-content col row no-wrap items-center',
            class: meta.selected ? this.selectedColorClass : this.textColorClass
          }, [
            header
              ? header(slotScope)
              : [
                this.__getNodeMedia(h, node),
                h('div', node[this.labelKey])
              ]
          ])
        ]),

        isParent === true
          ? h(QSlideTransition, {
            props: { duration: this.duration }
          }, [
            h('div', {
              staticClass: 'q-tree__node-collapsible',
              class: this.textColorClass,
              directives: [{ name: 'show', value: meta.expanded }]
            }, [
              body,

              h('div', {
                staticClass: 'q-tree__children',
                class: { disabled: meta.disabled }
              }, children)
            ])
          ])
          : body
      ])
    },

    __blur (key) {
      const blurTarget = this.$refs[`blurTarget_${key}`];
      blurTarget !== void 0 && blurTarget.focus();
    },

    __onClick (node, meta, e, keyboard) {
      keyboard !== true && this.__blur(meta.key);

      if (this.hasSelection) {
        if (meta.selectable) {
          this.$emit('update:selected', meta.key !== this.selected ? meta.key : null);
        }
      }
      else {
        this.__onExpandClick(node, meta, e, keyboard);
      }

      if (typeof node.handler === 'function') {
        node.handler(node);
      }
    },

    __onExpandClick (node, meta, e, keyboard) {
      if (e !== void 0) {
        stopAndPrevent(e);
      }
      keyboard !== true && this.__blur(meta.key);
      this.setExpanded(meta.key, !meta.expanded, node, meta);
    },

    __onTickedClick (meta, state) {
      if (meta.indeterminate && state) {
        state = false;
      }
      if (meta.strictTicking) {
        this.setTicked([ meta.key ], state);
      }
      else if (meta.leafTicking) {
        const keys = [];
        const travel = meta => {
          if (meta.isParent) {
            if (!state && !meta.noTick && meta.tickable) {
              keys.push(meta.key);
            }
            if (meta.leafTicking) {
              meta.children.forEach(travel);
            }
          }
          else if (!meta.noTick && meta.tickable && (!meta.leafFilteredTicking || meta.matchesFilter)) {
            keys.push(meta.key);
          }
        };
        travel(meta);
        this.setTicked(keys, state);
      }
    }
  },

  render (h) {
    const children = this.__getChildren(h, this.nodes);

    return h(
      'div', {
        staticClass: 'q-tree',
        class: this.classes
      },
      children.length === 0
        ? (
          this.filter
            ? this.noResultsLabel || this.$q.lang.tree.noResults
            : this.noNodesLabel || this.$q.lang.tree.noNodes
        )
        : children
    )
  },

  created () {
    this.defaultExpandAll === true && this.expandAll();
  }
});

var QUploaderBase = {
  props: {
    label: String,

    color: String,
    textColor: String,

    dark: Boolean,

    square: Boolean,
    flat: Boolean,
    bordered: Boolean,

    multiple: Boolean,
    accept: String,
    maxFileSize: Number,
    maxTotalSize: Number,
    filter: Function,
    noThumbnails: Boolean,
    autoUpload: Boolean,
    hideUploadBtn: Boolean,

    disable: Boolean,
    readonly: Boolean
  },

  provide () {
    return {
      __qUploaderGetInput: this.__getInputControl
    }
  },

  data () {
    return {
      files: [],
      queuedFiles: [],
      uploadedFiles: [],
      dnd: false,
      expanded: false,

      uploadSize: 0,
      uploadedSize: 0
    }
  },

  watch: {
    isUploading (newVal, oldVal) {
      if (oldVal === false && newVal === true) {
        this.$emit('start');
      }
      else if (oldVal === true && newVal === false) {
        this.$emit('finish');
      }
    }
  },

  computed: {
    /*
     * When extending:
     *   Required : isUploading
     *   Optional: isBusy
     */

    canUpload () {
      return this.editable === true &&
        this.isUploading !== true &&
        this.queuedFiles.length > 0
    },

    canAddFiles () {
      return this.editable &&
        this.isUploading !== true &&
        (this.multiple === true || this.queuedFiles.length === 0)
    },

    extensions () {
      if (this.accept !== void 0) {
        return this.accept.split(',').map(ext => {
          ext = ext.trim();
          // support "image/*"
          if (ext.endsWith('/*')) {
            ext = ext.slice(0, ext.length - 1);
          }
          return ext
        })
      }
    },

    uploadProgress () {
      return this.uploadSize === 0
        ? 0
        : this.uploadedSize / this.uploadSize
    },

    uploadProgressLabel () {
      return this.__getProgressLabel(this.uploadProgress)
    },

    uploadedSizeLabel () {
      return humanStorageSize(this.uploadedSize)
    },

    uploadSizeLabel () {
      return humanStorageSize(this.uploadSize)
    },

    colorClass () {
      const cls = [];
      this.color !== void 0 && cls.push(`bg-${this.color}`);
      this.textColor !== void 0 && cls.push(`text-${this.textColor}`);
      return cls.join(' ')
    },

    editable () {
      return this.disable !== true && this.readonly !== true
    }
  },

  methods: {
    pickFiles (e) {
      if (this.editable) {
        const input = this.__getFileInput();
        input && input.click(e);
      }
    },

    addFiles (files) {
      if (this.editable && files) {
        this.__addFiles(null, files);
      }
    },

    reset () {
      if (!this.disable) {
        this.abort();
        this.uploadedSize = 0;
        this.uploadSize = 0;
        this.files = [];
        this.queuedFiles = [];
        this.uploadedFiles = [];
      }
    },

    removeUploadedFiles () {
      if (!this.disable) {
        this.files = this.files.filter(f => f.__status !== 'uploaded');
        this.uploadedFiles = [];
      }
    },

    removeQueuedFiles () {
      if (!this.disable) {
        const removedFiles = [];

        this.files.forEach(file => {
          if (file.__status === 'idle' || file.__status === 'failed') {
            this.uploadSize -= file.size;
            removedFiles.push(file);
          }
        });

        if (removedFiles.length > 0) {
          this.files = this.files.filter(f => f.__status !== 'idle' && f.__status !== 'failed');
          this.queuedFiles = [];
          this.$emit('removed', removedFiles);
        }
      }
    },

    removeFile (file) {
      if (this.disable) { return }

      if (file.__status === 'uploaded') {
        this.uploadedFiles = this.uploadedFiles.filter(f => f.name !== file.name);
      }
      else if (file.__status === 'uploading') {
        file.__abort();
      }
      else {
        this.uploadSize -= file.size;
      }

      this.files = this.files.filter(f => f.name !== file.name);
      this.queuedFiles = this.queuedFiles.filter(f => f.name !== file.name);
      this.$emit('removed', [ file ]);
    },

    __getFileInput () {
      return this.$refs.input ||
        this.$el.getElementsByClassName('q-uploader__input')
    },

    __getProgressLabel (p) {
      return (p * 100).toFixed(2) + '%'
    },

    __updateFile (file, status, uploadedSize) {
      file.__status = status;

      if (status === 'idle') {
        file.__uploaded = 0;
        file.__progress = 0;
        file.__sizeLabel = humanStorageSize(file.size);
        file.__progressLabel = '0.00%';
        return
      }
      if (status === 'failed') {
        this.$forceUpdate();
        return
      }

      file.__uploaded = status === 'uploaded'
        ? file.size
        : uploadedSize;

      file.__progress = status === 'uploaded'
        ? 1
        : Math.min(0.9999, file.__uploaded / file.size);

      file.__progressLabel = this.__getProgressLabel(file.__progress);
      this.$forceUpdate();
    },

    __addFiles (e, files) {
      files = Array.prototype.slice.call(files || e.target.files);
      this.__getFileInput().value = '';

      // make sure we don't duplicate files
      files = files.filter(file => !this.files.some(f => file.name === f.name));
      if (files.length === 0) { return }

      // filter file types
      if (this.accept !== void 0) {
        files = Array.prototype.filter.call(files, file => {
          return this.extensions.some(ext => (
            file.type.toUpperCase().startsWith(ext.toUpperCase()) ||
            file.name.toUpperCase().endsWith(ext.toUpperCase())
          ))
        });
        if (files.length === 0) { return }
      }

      // filter max file size
      if (this.maxFileSize !== void 0) {
        files = Array.prototype.filter.call(files, file => file.size <= this.maxFileSize);
        if (files.length === 0) { return }
      }

      if (this.maxTotalSize !== void 0) {
        let size = 0;
        for (let i = 0; i < files.length; i++) {
          size += files[i].size;
          if (size > this.maxTotalSize) {
            if (i > 0) {
              files = files.slice(0, i - 1);
              break
            }
            else {
              return
            }
          }
        }
        if (files.length === 0) { return }
      }

      // do we have custom filter function?
      if (typeof this.filter === 'function') {
        files = this.filter(files);
      }

      if (files.length === 0) { return }

      let filesReady = []; // List of image load promises

      files.forEach(file => {
        this.__updateFile(file, 'idle');
        this.uploadSize += file.size;

        if (this.noThumbnails !== true && file.type.toUpperCase().startsWith('IMAGE')) {
          const reader = new FileReader();
          let p = new Promise((resolve, reject) => {
            reader.onload = e => {
              let img = new Image();
              img.src = e.target.result;
              file.__img = img;
              resolve(true);
            };
            reader.onerror = e => { reject(e); };
          });

          reader.readAsDataURL(file);
          filesReady.push(p);
        }
      });

      Promise.all(filesReady).then(() => {
        this.files = this.files.concat(files);
        this.queuedFiles = this.queuedFiles.concat(files);
        this.$emit('added', files);
        this.autoUpload === true && this.upload();
      });
    },

    __onDragOver (e) {
      stopAndPrevent(e);
      this.dnd = true;
    },

    __onDragLeave (e) {
      stopAndPrevent(e);
      this.dnd = false;
    },

    __onDrop (e) {
      stopAndPrevent(e);
      let files = e.dataTransfer.files;

      if (files.length > 0) {
        files = this.multiple ? files : [ files[0] ];
        this.__addFiles(null, files);
      }

      this.dnd = false;
    },

    __getBtn (h, show, icon, fn) {
      if (show === true) {
        return h(QBtn, {
          props: {
            icon: this.$q.iconSet.uploader[icon],
            flat: true,
            dense: true
          },
          on: icon === 'add' ? null : { click: fn }
        }, icon === 'add' ? this.__getInputControl(h) : null)
      }
    },

    __getInputControl (h) {
      return [
        h('input', {
          ref: 'input',
          staticClass: 'q-uploader__input absolute-full',
          attrs: {
            type: 'file',
            title: '', // try to remove default tooltip
            accept: this.accept,
            ...(this.multiple === true ? { multiple: true } : {})
          },
          on: {
            change: this.__addFiles
          }
        })
      ]
    },

    __getHeader (h) {
      if (this.$scopedSlots.header !== void 0) {
        return this.$scopedSlots.header(this)
      }

      return h('div', {
        staticClass: 'q-uploader__header-content flex flex-center no-wrap q-gutter-xs'
      }, [
        this.__getBtn(h, this.queuedFiles.length > 0, 'removeQueue', this.removeQueuedFiles),
        this.__getBtn(h, this.uploadedFiles.length > 0, 'removeUploaded', this.removeUploadedFiles),

        this.isUploading === true
          ? h(QSpinner, { staticClass: 'q-uploader__spinner' })
          : null,

        h('div', { staticClass: 'col column justify-center' }, [
          this.label !== void 0
            ? h('div', { staticClass: 'q-uploader__title' }, [ this.label ])
            : null,

          h('div', { staticClass: 'q-uploader__subtitle' }, [
            this.uploadSizeLabel + ' / ' + this.uploadProgressLabel
          ])
        ]),

        this.__getBtn(h, this.canAddFiles, 'add', this.pickFiles),
        this.__getBtn(h, this.hideUploadBtn === false && this.canUpload === true, 'upload', this.upload),
        this.__getBtn(h, this.isUploading, 'clear', this.abort)
      ])
    },

    __getList (h) {
      if (this.$scopedSlots.list !== void 0) {
        return this.$scopedSlots.list(this)
      }

      return this.files.map(file => h('div', {
        key: file.name,
        staticClass: 'q-uploader__file relative-position',
        class: {
          'q-uploader__file--img': file.__img !== void 0,
          'q-uploader__file--failed': file.__status === 'failed',
          'q-uploader__file--uploaded': file.__status === 'uploaded'
        },
        style: file.__img !== void 0 ? {
          backgroundImage: 'url(' + file.__img.src + ')'
        } : null
      }, [
        h('div', {
          staticClass: 'q-uploader__file-header row flex-center no-wrap'
        }, [
          file.__status === 'failed'
            ? h(QIcon, {
              staticClass: 'q-uploader__file-status',
              props: {
                name: this.$q.iconSet.type.negative,
                color: 'negative'
              }
            })
            : null,

          h('div', { staticClass: 'q-uploader__file-header-content col' }, [
            h('div', { staticClass: 'q-uploader__title' }, [ file.name ]),
            h('div', {
              staticClass: 'q-uploader__subtitle row items-center no-wrap'
            }, [
              file.__sizeLabel + ' / ' + file.__progressLabel
            ])
          ]),

          file.__status === 'uploading'
            ? h(QCircularProgress, {
              props: {
                value: file.__progress,
                min: 0,
                max: 1,
                indeterminate: file.__progress === 0
              }
            })
            : h(QBtn, {
              props: {
                round: true,
                dense: true,
                flat: true,
                icon: this.$q.iconSet.uploader[file.__status === 'uploaded' ? 'done' : 'clear']
              },
              on: {
                click: () => { this.removeFile(file); }
              }
            })
        ])
      ]))
    }
  },

  beforeDestroy () {
    this.isUploading === true && this.abort();
  },

  render (h) {
    return h('div', {
      staticClass: 'q-uploader column no-wrap',
      class: {
        'q-uploader--dark': this.dark,
        'q-uploader--bordered': this.bordered,
        'q-uploader--square no-border-radius': this.square,
        'q-uploader--flat no-shadow': this.flat,
        'disabled q-uploader--disable': this.disable
      },
      on: this.editable === true && this.isUploading !== true
        ? { dragover: this.__onDragOver }
        : null
    }, [
      h('div', {
        staticClass: 'q-uploader__header',
        class: this.colorClass
      }, [
        this.__getHeader(h)
      ]),

      h('div', {
        staticClass: 'q-uploader__list scroll'
      }, this.__getList(h)),

      this.dnd === true ? h('div', {
        staticClass: 'q-uploader__dnd absolute-full',
        on: {
          dragenter: stopAndPrevent,
          dragover: stopAndPrevent,
          dragleave: this.__onDragLeave,
          drop: this.__onDrop
        }
      }) : null,

      this.isBusy === true ? h('div', {
        staticClass: 'q-uploader__overlay absolute-full flex flex-center'
      }, [
        h(QSpinner)
      ]) : null
    ])
  }
};

function getFn (prop) {
  return typeof prop === 'function'
    ? prop
    : () => prop
}

var UploaderXHRMixin = {
  props: {
    url: [Function, String],
    method: {
      type: [Function, String],
      default: 'POST'
    },
    fieldName: {
      type: [Function, String],
      default: file => file.name
    },
    headers: [Function, Array],
    fields: [Function, Array], /* TODO remove in v1 final */
    formFields: [Function, Array],
    withCredentials: [Function, Boolean],
    sendRaw: [Function, Boolean],

    batch: [Function, Boolean],
    factory: Function
  },

  data () {
    return {
      xhrs: [],
      promises: [],
      workingThreads: 0
    }
  },

  computed: {
    xhrProps () {
      return {
        url: getFn(this.url),
        method: getFn(this.method),
        headers: getFn(this.headers),
        fields: getFn(this.fields),
        formFields: getFn(this.formFields),
        fieldName: getFn(this.fieldName),
        withCredentials: getFn(this.withCredentials),
        sendRaw: getFn(this.sendRaw),
        batch: getFn(this.batch)
      }
    },

    isUploading () {
      return this.workingThreads > 0
    },

    isBusy () {
      return this.promises.length > 0
    }
  },

  methods: {
    abort () {
      this.xhrs.forEach(x => { x.abort(); });
      this.promises.forEach(p => { p.abort(); });
    },

    upload () {
      if (this.canUpload === false) {
        return
      }

      const queue = this.queuedFiles.slice(0);
      this.queuedFiles = [];

      if (this.xhrProps.batch(queue)) {
        this.__runFactory(queue);
      }
      else {
        queue.forEach(file => {
          this.__runFactory([ file ]);
        });
      }
    },

    __runFactory (files) {
      this.workingThreads++;

      if (typeof this.factory !== 'function') {
        this.__uploadFiles(files, {});
        return
      }

      const res = this.factory(files);

      if (!res) {
        this.$emit(
          'factory-failed',
          new Error('QUploader: factory() does not return properly'),
          files
        );
        this.workingThreads--;
      }
      else if (typeof res.catch === 'function' && typeof res.then === 'function') {
        this.promises.push(res);

        res.then(factory => {
          if (this._isBeingDestroyed === true || this._isDestroyed === true) {
            this.promises = this.promises.filter(p => p !== res);
            this.__uploadFiles(files, factory);
          }
        }).catch(err => {
          if (this._isBeingDestroyed === true || this._isDestroyed === true) {
            this.promises = this.promises.filter(p => p !== res);

            this.queuedFiles = this.queuedFiles.concat(files);
            files.forEach(f => { this.__updateFile(f, 'failed'); });

            this.$emit('factory-failed', err, files);
            this.workingThreads--;
          }
        });
      }
      else {
        this.__uploadFiles(files, res || {});
      }
    },

    __uploadFiles (files, factory) {
      const
        form = new FormData(),
        xhr = new XMLHttpRequest();

      const getProp = (name, arg) => {
        return factory[name] !== void 0
          ? getFn(factory[name])(arg)
          : this.xhrProps[name](arg)
      };

      const url = getProp('url', files);

      if (!url) {
        console.error('q-uploader: invalid or no URL specified');
        this.workingThreads--;
        return
      }

      const fields = (
        getProp('formFields', files) ||
        /* TODO remove in v1 final */ getProp('fields', files)
      );
      fields !== void 0 && fields.forEach(field => {
        form.append(field.name, field.value);
      });

      let
        uploadIndex = 0,
        uploadIndexSize = 0,
        uploadedSize = 0,
        maxUploadSize = 0,
        aborted;

      xhr.upload.addEventListener('progress', e => {
        if (aborted === true) { return }

        const loaded = Math.min(maxUploadSize, e.loaded);

        this.uploadedSize += loaded - uploadedSize;
        uploadedSize = loaded;

        let size = uploadedSize - uploadIndexSize;
        for (let i = uploadIndex; size > 0 && i < files.length; i++) {
          const
            file = files[i],
            uploaded = size > file.size;

          if (uploaded) {
            size -= file.size;
            uploadIndex++;
            uploadIndexSize += file.size;
            this.__updateFile(file, 'uploading', file.size);
          }
          else {
            this.__updateFile(file, 'uploading', size);
            return
          }
        }
      }, false);

      xhr.onreadystatechange = () => {
        if (xhr.readyState < 4) {
          return
        }

        if (xhr.status && xhr.status < 400) {
          this.uploadedFiles = this.uploadedFiles.concat(files);
          files.forEach(f => { this.__updateFile(f, 'uploaded'); });
          this.$emit('uploaded', { files, xhr });
        }
        else {
          aborted = true;
          this.uploadedSize -= uploadedSize;
          this.queuedFiles = this.queuedFiles.concat(files);
          files.forEach(f => { this.__updateFile(f, 'failed'); });
          this.$emit('failed', { files, xhr });
        }

        this.workingThreads--;
        this.xhrs = this.xhrs.filter(x => x !== xhr);
      };

      xhr.open(
        getProp('method', files),
        url
      );

      if (getProp('withCredentials', files) === true) {
        xhr.withCredentials = true;
      }

      const headers = getProp('headers', files);
      headers !== void 0 && headers.forEach(head => {
        xhr.setRequestHeader(head.name, head.value);
      });

      const sendRaw = getProp('sendRaw', files);

      files.forEach(file => {
        this.__updateFile(file, 'uploading', 0);
        if (sendRaw !== true) {
          form.append(getProp('fieldName', file), file);
        }
        file.xhr = xhr;
        file.__abort = xhr.abort;
        maxUploadSize += file.size;
      });

      this.$emit('uploading', { files, xhr });
      this.xhrs.push(xhr);

      if (sendRaw === true) {
        xhr.send(new Blob(files));
      }
      else {
        xhr.send(form);
      }
    }
  },

  // TODO remove in v1 final
  mounted () {
    if (this.fields !== void 0) {
      {
        console.info('\n\n[Quasar] QUploader: please rename "fields" prop to "form-fields"');
      }
    }
  }
};

Vue.extend({
  name: 'QUploader',
  mixins: [ QUploaderBase, UploaderXHRMixin ]
});

Vue.extend({
  name: 'QUploaderAddTrigger',

  inject: {
    __qUploaderGetInput: {
      default () {
        console.error('QUploaderAddTrigger needs to be child of QUploader');
      }
    }
  },

  render (h) {
    return this.__qUploaderGetInput(h)
  }
});

Vue.extend({
  name: 'QVideo',

  props: {
    src: {
      type: String,
      required: true
    }
  },

  computed: {
    iframeData () {
      return {
        attrs: {
          src: this.src,
          frameborder: '0',
          allowfullscreen: true
        }
      }
    }
  },

  render (h) {
    return h('div', {
      staticClass: 'q-video',
      on: this.$listeners
    }, [
      h('iframe', this.iframeData)
    ])
  }
});

var ClosePopup = {
  name: 'close-popup',

  bind (el, { value }, vnode) {
    const ctx = {
      enabled: value !== false,

      handler (evt) {
        // allow @click to be emitted
        ctx.enabled !== false && evt.defaultPrevented !== true && setTimeout(() => {
          const vm = (vnode.componentInstance || vnode.context).$root;
          vm.__qClosePopup !== void 0 && vm.__qClosePopup(evt);
        });
      },

      handlerKey (evt) {
        evt.keyCode === 13 && ctx.handler(evt);
      }
    };

    if (el.__qclosepopup !== void 0) {
      el.__qclosepopup_old = el.__qclosepopup;
    }

    el.__qclosepopup = ctx;
    el.addEventListener('click', ctx.handler);
    el.addEventListener('keyup', ctx.handlerKey);
  },

  update (el, { value }) {
    if (el.__qclosepopup !== void 0) {
      el.__qclosepopup.enabled = value !== false;
    }
  },

  unbind (el) {
    const ctx = el.__qclosepopup_old || el.__qclosepopup;
    if (ctx !== void 0) {
      el.removeEventListener('click', ctx.handler);
      el.removeEventListener('keyup', ctx.handlerKey);
      delete el[el.__qclosepopup_old ? '__qclosepopup_old' : '__qclosepopup'];
    }
  }
};

var CloseDialog = {
  ...ClosePopup,
  name: 'close-dialog',
  bind (el, bindings, vnode) {
    {
      console.info('\n\n[Quasar] info: please rename v-close-dialog (deprecated) with v-close-popup');
    }
    ClosePopup.bind(el, bindings, vnode);
  }
};

var CloseMenu = {
  ...ClosePopup,
  name: 'close-menu',
  bind (el, bindings, vnode) {
    {
      console.info('\n\n[Quasar] info: please rename v-close-menu (deprecated) with v-close-popup');
    }
    ClosePopup.bind(el, bindings, vnode);
  }
};

var GoBack = {
  name: 'go-back',

  bind (el, { value, modifiers }, vnode) {
    let ctx = { value, position: window.history.length - 1, single: modifiers.single };

    if (Platform.is.cordova) {
      ctx.goBack = () => {
        vnode.context.$router.go(ctx.single ? -1 : ctx.position - window.history.length);
      };
    }
    else {
      ctx.goBack = () => {
        vnode.context.$router.replace(ctx.value);
      };
    }
    ctx.goBackKey = ev => {
      if (ev.keyCode === 13) {
        ctx.goBack(ev);
      }
    };

    if (el.__qgoback) {
      el.__qgoback_old = el.__qgoback;
    }

    el.__qgoback = ctx;
    el.addEventListener('click', ctx.goBack);
    el.addEventListener('keyup', ctx.goBackKey);
  },

  update (el, { value, oldValue }) {
    if (value !== oldValue) {
      el.__qgoback.value = value;
    }
  },

  unbind (el) {
    const ctx = el.__qgoback_old || el.__qgoback;
    if (ctx !== void 0) {
      el.removeEventListener('click', ctx.goBack);
      el.removeEventListener('keyup', ctx.goBackKey);
      delete el[el.__qgoback_old ? '__qgoback_old' : '__qgoback'];
    }
  }
};

function updateBinding (el, { value, oldValue }) {
  const ctx = el.__qscrollfire;

  if (typeof value !== 'function') {
    ctx.scrollTarget.removeEventListener('scroll', ctx.scroll);
    console.error('v-scroll-fire requires a function as parameter', el);
    return
  }

  ctx.handler = value;
  if (typeof oldValue !== 'function') {
    ctx.scrollTarget.addEventListener('scroll', ctx.scroll, listenOpts.passive);
    ctx.scroll();
  }
}

var ScrollFire = {
  name: 'scroll-fire',

  bind (el) {
    let ctx = {
      scroll: debounce(() => {
        let containerBottom, elBottom;

        if (ctx.scrollTarget === window) {
          elBottom = el.getBoundingClientRect().bottom;
          containerBottom = window.innerHeight;
        }
        else {
          elBottom = offset(el).top + height(el);
          containerBottom = offset(ctx.scrollTarget).top + height(ctx.scrollTarget);
        }

        if (elBottom > 0 && elBottom < containerBottom) {
          ctx.scrollTarget.removeEventListener('scroll', ctx.scroll, listenOpts.passive);
          ctx.handler(el);
        }
      }, 25)
    };

    if (el.__qscrollfire) {
      el.__qscrollfire_old = el.__qscrollfire;
    }

    el.__qscrollfire = ctx;
  },

  inserted (el, binding) {
    let ctx = el.__qscrollfire;
    ctx.scrollTarget = getScrollTarget(el);
    updateBinding(el, binding);
  },

  update (el, binding) {
    if (binding.value !== binding.oldValue) {
      updateBinding(el, binding);
    }
  },

  unbind (el) {
    let ctx = el.__qscrollfire_old || el.__qscrollfire;
    if (ctx !== void 0) {
      ctx.scrollTarget.removeEventListener('scroll', ctx.scroll, listenOpts.passive);
      delete el[el.__qscrollfire_old ? '__qscrollfire_old' : '__qscrollfire'];
    }
  }
};

function updateBinding$1 (el, { value, oldValue }) {
  const ctx = el.__qscroll;

  if (typeof value !== 'function') {
    ctx.scrollTarget.removeEventListener('scroll', ctx.scroll, listenOpts.passive);
    console.error('v-scroll requires a function as parameter', el);
    return
  }

  ctx.handler = value;
  if (typeof oldValue !== 'function') {
    ctx.scrollTarget.addEventListener('scroll', ctx.scroll, listenOpts.passive);
  }
}

var Scroll = {
  name: 'scroll',

  bind (el) {
    let ctx = {
      scroll () {
        ctx.handler(
          getScrollPosition(ctx.scrollTarget),
          getHorizontalScrollPosition(ctx.scrollTarget)
        );
      }
    };

    if (el.__qscroll) {
      el.__qscroll_old = el.__qscroll;
    }

    el.__qscroll = ctx;
  },

  inserted (el, binding) {
    let ctx = el.__qscroll;
    ctx.scrollTarget = getScrollTarget(el);
    updateBinding$1(el, binding);
  },

  update (el, binding) {
    if (binding.oldValue !== binding.value) {
      updateBinding$1(el, binding);
    }
  },

  unbind (el) {
    let ctx = el.__qscroll_old || el.__qscroll;
    if (ctx !== void 0) {
      ctx.scrollTarget.removeEventListener('scroll', ctx.scroll, listenOpts.passive);
      delete el[el.__qscroll_old ? '__qscroll_old' : '__qscroll'];
    }
  }
};

function updateBinding$2 (el, binding) {
  const ctx = el.__qtouchhold;

  ctx.duration = parseInt(binding.arg, 10) || 600;

  if (binding.oldValue !== binding.value) {
    ctx.handler = binding.value;
  }
}

var TouchHold = {
  name: 'touch-hold',

  bind (el, binding) {
    const mouse = binding.modifiers.mouse === true;

    const ctx = {
      mouseStart (evt) {
        if (leftClick(evt)) {
          document.addEventListener('mousemove', ctx.mouseEnd, true);
          document.addEventListener('click', ctx.mouseEnd, true);
          ctx.start(evt, true);
        }
      },

      mouseEnd (evt) {
        document.removeEventListener('mousemove', ctx.mouseEnd, true);
        document.removeEventListener('click', ctx.mouseEnd, true);
        ctx.end(evt);
      },

      start (evt, mouseEvent) {
        removeObserver(ctx);
        mouseEvent !== true && setObserver(el, evt, ctx);

        const startTime = new Date().getTime();

        if (Platform.is.mobile === true) {
          document.body.classList.add('non-selectable');
          clearSelection();
        }

        ctx.triggered = false;

        ctx.timer = setTimeout(() => {
          if (Platform.is.mobile !== true) {
            document.body.classList.add('non-selectable');
            clearSelection();
          }
          ctx.triggered = true;

          ctx.handler({
            evt,
            position: position(evt),
            duration: new Date().getTime() - startTime
          });
        }, ctx.duration);
      },

      end (evt) {
        removeObserver(ctx);
        document.body.classList.remove('non-selectable');

        if (ctx.triggered === true) {
          stopAndPrevent(evt);
        }
        else {
          clearTimeout(ctx.timer);
        }
      }
    };

    if (el.__qtouchhold) {
      el.__qtouchhold_old = el.__qtouchhold;
    }

    el.__qtouchhold = ctx;
    updateBinding$2(el, binding);

    if (mouse === true) {
      el.addEventListener('mousedown', ctx.mouseStart);
    }
    el.addEventListener('touchstart', ctx.start, listenOpts.notPassive);
    el.addEventListener('touchmove', ctx.end, listenOpts.notPassive);
    el.addEventListener('touchcancel', ctx.end);
    el.addEventListener('touchend', ctx.end);
  },

  update (el, binding) {
    updateBinding$2(el, binding);
  },

  unbind (el, binding) {
    let ctx = el.__qtouchhold_old || el.__qtouchhold;
    if (ctx !== void 0) {
      removeObserver(ctx);
      clearTimeout(ctx.timer);
      document.body.classList.remove('non-selectable');

      if (binding.modifiers.mouse === true) {
        el.removeEventListener('mousedown', ctx.mouseStart);
        document.removeEventListener('mousemove', ctx.mouseEnd, true);
        document.removeEventListener('click', ctx.mouseEnd, true);
      }
      el.removeEventListener('touchstart', ctx.start, listenOpts.notPassive);
      el.removeEventListener('touchmove', ctx.end, listenOpts.notPassive);
      el.removeEventListener('touchcancel', ctx.end);
      el.removeEventListener('touchend', ctx.end);

      delete el[el.__qtouchhold_old ? '__qtouchhold_old' : '__qtouchhold'];
    }
  }
};

const
  keyCodes$2 = {
    esc: 27,
    tab: 9,
    enter: 13,
    space: 32,
    up: 38,
    left: 37,
    right: 39,
    down: 40,
    'delete': [8, 46]
  },
  keyRegex = new RegExp(`^([\\d+]+|${Object.keys(keyCodes$2).join('|')})$`, 'i');

var TouchRepeat = {
  name: 'touch-repeat',

  bind (el, binding) {
    const keyboard = Object.keys(binding.modifiers).reduce((acc, key) => {
      if (keyRegex.test(key)) {
        const keyCode = parseInt(key, 10);
        acc.push(keyCode || keyCodes$2[key.toLowerCase()]);
      }

      return acc
    }, []);

    const durations = typeof binding.arg === 'string' && binding.arg.length
      ? binding.arg.split(':').map(val => parseInt(val, 10))
      : [0, 600, 300];

    const durationsLast = durations.length - 1;

    let ctx = {
      keyboard,
      handler: binding.value,

      mouseStart (evt) {
        if (leftClick(evt)) {
          document.addEventListener('mousemove', ctx.mouseEnd, true);
          document.addEventListener('click', ctx.mouseEnd, true);
          ctx.start(evt, true);
        }
      },

      mouseEnd (evt) {
        document.removeEventListener('mousemove', ctx.mouseEnd, true);
        document.removeEventListener('click', ctx.mouseEnd, true);
        ctx.end(evt);
      },

      keyboardStart (evt) {
        if (keyboard.includes(evt.keyCode)) {
          if (durations[0] === 0 || ctx.event !== void 0) {
            stopAndPrevent(evt);
            el.focus();
            if (ctx.event !== void 0) {
              return
            }
          }

          document.addEventListener('keyup', ctx.keyboardEnd, true);
          ctx.start(evt, false, true);
        }
      },

      keyboardEnd (evt) {
        document.removeEventListener('keyup', ctx.keyboardEnd, true);
        ctx.end(evt);
      },

      start (evt, mouseEvent, keyboardEvent) {
        removeObserver(ctx);
        if (mouseEvent !== true && keyboardEvent !== true) {
          setObserver(el, evt, ctx);
        }

        if (Platform.is.mobile === true) {
          document.body.classList.add('non-selectable');
          clearSelection();
        }

        ctx.event = {
          mouse: mouseEvent === true,
          keyboard: keyboardEvent === true,
          startTime: new Date().getTime(),
          repeatCount: 0
        };

        const fn = () => {
          if (ctx.event === void 0) {
            return
          }

          if (ctx.event.repeatCount === 0) {
            ctx.event.evt = evt;
            ctx.event.position = position(evt);
            if (Platform.is.mobile !== true) {
              document.documentElement.style.cursor = 'pointer';
              document.body.classList.add('non-selectable');
              clearSelection();
            }
          }

          ctx.event.duration = new Date().getTime() - ctx.event.startTime;
          ctx.event.repeatCount += 1;

          ctx.handler(ctx.event);

          const index = durationsLast < ctx.event.repeatCount
            ? durationsLast
            : ctx.event.repeatCount;

          ctx.timer = setTimeout(fn, durations[index]);
        };

        ctx.timer = setTimeout(fn, durations[0]);
      },

      end (evt) {
        if (ctx.event === void 0) {
          return
        }

        removeObserver(ctx);

        const triggered = ctx.event.repeatCount > 0;

        triggered === true && stopAndPrevent(evt);

        if (Platform.is.mobile === true || triggered === true) {
          document.documentElement.style.cursor = '';
          document.body.classList.remove('non-selectable');
        }

        clearTimeout(ctx.timer);
        ctx.timer = void 0;
        ctx.event = void 0;
      }
    };

    if (el.__qtouchrepeat) {
      el.__qtouchrepeat_old = el.__qtouchrepeat;
    }

    el.__qtouchrepeat = ctx;

    if (binding.modifiers.mouse === true) {
      el.addEventListener('mousedown', ctx.mouseStart);
    }
    if (keyboard.length > 0) {
      el.addEventListener('keydown', ctx.keyboardStart);
    }
    el.addEventListener('touchstart', ctx.start, listenOpts.notPassive);
    el.addEventListener('touchmove', ctx.end, listenOpts.notPassive);
    el.addEventListener('touchcancel', ctx.end);
    el.addEventListener('touchend', ctx.end);
  },

  update (el, binding) {
    if (binding.oldValue !== binding.value) {
      el.__qtouchrepeat.handler = binding.value;
    }
  },

  unbind (el, binding) {
    let ctx = el.__qtouchrepeat_old || el.__qtouchrepeat;
    if (ctx !== void 0) {
      removeObserver(ctx);
      clearTimeout(ctx.timer);

      if (Platform.is.mobile === true || (ctx.event !== void 0 && ctx.event.repeatCount > 0)) {
        document.documentElement.style.cursor = '';
        document.body.classList.remove('non-selectable');
      }

      ctx.timer = void 0;
      ctx.event = void 0;

      if (binding.modifiers.mouse === true) {
        el.removeEventListener('mousedown', ctx.mouseStart);
        document.removeEventListener('mousemove', ctx.mouseEnd, true);
        document.removeEventListener('click', ctx.mouseEnd, true);
      }
      if (ctx.keyboard.length > 0) {
        el.removeEventListener('keydown', ctx.keyboardStart);
        document.removeEventListener('keyup', ctx.keyboardEnd, true);
      }
      el.removeEventListener('touchstart', ctx.start, listenOpts.notPassive);
      el.removeEventListener('touchmove', ctx.end, listenOpts.notPassive);
      el.removeEventListener('touchcancel', ctx.end);
      el.removeEventListener('touchend', ctx.end);

      delete el[el.__qtouchrepeat_old ? '__qtouchrepeat_old' : '__qtouchrepeat'];
    }
  }
};



var directives$1 = /*#__PURE__*/Object.freeze({
            CloseDialog: CloseDialog,
            CloseMenu: CloseMenu,
            ClosePopup: ClosePopup,
            GoBack: GoBack,
            Ripple: Ripple,
            ScrollFire: ScrollFire,
            Scroll: Scroll,
            TouchHold: TouchHold,
            TouchPan: TouchPan,
            TouchRepeat: TouchRepeat,
            TouchSwipe: TouchSwipe
});

Vue.extend({
  name: 'BottomSheetPlugin',

  inheritAttrs: false,

  props: {
    title: String,
    message: String,
    actions: Array,

    grid: Boolean,

    cardClass: [String, Array, Object],
    cardStyle: [String, Array, Object],

    dark: Boolean
  },

  methods: {
    show () {
      this.$refs.dialog.show();
    },

    hide () {
      this.$refs.dialog.hide();
    },

    onOk (action) {
      this.$emit('ok', action);
      this.hide();
    },

    __getGrid (h) {
      return this.actions.map(action => {
        const img = action.avatar || action.img;

        return action.label === void 0
          ? h(QSeparator, {
            staticClass: 'col-all',
            props: { dark: this.dark }
          })
          : h('div', {
            staticClass: 'q-bottom-sheet__item q-hoverable q-focusable cursor-pointer relative-position',
            class: action.classes,
            attrs: { tabindex: 0 },
            on: {
              click: () => this.onOk(action),
              keyup: e => {
                e.keyCode === 13 && this.onOk(action);
              }
            }
          }, [
            h('div', { staticClass: 'q-focus-helper' }),

            action.icon
              ? h(QIcon, { props: { name: action.icon, color: action.color } })
              : (
                img
                  ? h('img', {
                    attrs: { src: img },
                    staticClass: action.avatar ? 'q-bottom-sheet__avatar' : null
                  })
                  : h('div', { staticClass: 'q-bottom-sheet__empty-icon' })
              ),

            h('div', [ action.label ])
          ])
      })
    },

    __getList (h) {
      return this.actions.map(action => {
        const img = action.avatar || action.img;

        return action.label === void 0
          ? h(QSeparator, { props: { spaced: true, dark: this.dark } })
          : h(QItem, {
            staticClass: 'q-bottom-sheet__item',
            class: action.classes,
            props: {
              tabindex: 0,
              clickable: true,
              dark: this.dark
            },
            on: {
              click: () => this.onOk(action),
              keyup: e => {
                e.keyCode === 13 && this.onOk(action);
              }
            }
          }, [
            h(QItemSection, { props: { avatar: true } }, [
              action.icon
                ? h(QIcon, { props: { name: action.icon, color: action.color } })
                : (
                  img
                    ? h('img', {
                      attrs: { src: img },
                      staticClass: action.avatar ? 'q-bottom-sheet__avatar' : null
                    })
                    : null
                )
            ]),
            h(QItemSection, [ action.label ])
          ])
      })
    }
  },

  render (h) {
    let child = [];

    if (this.title) {
      child.push(
        h(QCardSection, {
          staticClass: 'q-dialog__title'
        }, [ this.title ])
      );
    }

    if (this.message) {
      child.push(
        h(QCardSection, {
          staticClass: 'q-dialog__message scroll'
        }, [ this.message ])
      );
    }

    child.push(
      this.grid === true
        ? h('div', {
          staticClass: 'scroll row items-stretch justify-start'
        }, this.__getGrid(h))
        : h('div', { staticClass: 'scroll' }, this.__getList(h))
    );

    return h(QDialog, {
      ref: 'dialog',

      props: {
        ...this.$attrs,
        position: 'bottom'
      },

      on: {
        hide: () => {
          this.$emit('hide');
        }
      }
    }, [
      h(QCard, {
        staticClass: `q-bottom-sheet q-bottom-sheet--${this.grid === true ? 'grid' : 'list'}` +
          (this.dark === true ? ' q-bottom-sheet--dark' : ''),
        style: this.cardStyle,
        class: this.cardClass
      }, child)
    ])
  }
});

Vue.extend({
  name: 'DialogPlugin',

  inheritAttrs: false,

  props: {
    title: String,
    message: String,
    prompt: Object,
    options: Object,

    ok: {
      type: [String, Object, Boolean],
      default: true
    },
    cancel: [String, Object, Boolean],

    stackButtons: Boolean,
    color: String,

    cardClass: [String, Array, Object],
    cardStyle: [String, Array, Object],

    dark: Boolean
  },

  computed: {
    hasForm () {
      return this.prompt || this.options
    },

    okLabel () {
      return this.ok === true
        ? this.$q.lang.label.ok
        : this.ok
    },

    cancelLabel () {
      return this.cancel === true
        ? this.$q.lang.label.cancel
        : this.cancel
    },

    vmColor () {
      return this.color || (this.dark === true ? 'amber' : 'primary')
    },

    okProps () {
      return Object(this.ok) === this.ok
        ? {
          color: this.vmColor,
          label: this.$q.lang.label.ok,
          ripple: false,
          ...this.ok
        }
        : {
          color: this.vmColor,
          flat: true,
          label: this.okLabel,
          ripple: false
        }
    },

    cancelProps () {
      return Object(this.cancel) === this.cancel
        ? {
          color: this.vmColor,
          label: this.$q.lang.label.cancel,
          ripple: false,
          ...this.cancel
        }
        : {
          color: this.vmColor,
          flat: true,
          label: this.cancelLabel,
          ripple: false
        }
    }
  },

  methods: {
    show () {
      this.$refs.dialog.show();
    },

    hide () {
      this.$refs.dialog.hide();
    },

    getPrompt (h) {
      return [
        h(QInput, {
          props: {
            value: this.prompt.model,
            type: this.prompt.type || 'text',
            color: this.vmColor,
            dense: true,
            autofocus: true,
            dark: this.dark
          },
          on: {
            input: v => { this.prompt.model = v; },
            keyup: evt => {
              // if ENTER key
              if (evt.keyCode === 13) {
                this.onOk();
              }
            }
          }
        })
      ]
    },

    getOptions (h) {
      return [
        h(QOptionGroup, {
          props: {
            value: this.options.model,
            type: this.options.type,
            color: this.vmColor,
            inline: this.options.inline,
            options: this.options.items,
            dark: this.dark
          },
          on: {
            input: v => { this.options.model = v; }
          }
        })
      ]
    },

    getButtons (h) {
      const child = [];

      if (this.cancel) {
        child.push(h(QBtn, {
          props: this.cancelProps,
          attrs: { autofocus: !this.prompt && !this.ok },
          on: { click: this.onCancel }
        }));
      }
      if (this.ok) {
        child.push(h(QBtn, {
          props: this.okProps,
          attrs: { autofocus: !this.prompt },
          on: { click: this.onOk }
        }));
      }

      if (child.length > 0) {
        return h(QCardActions, {
          staticClass: this.stackButtons === true ? 'items-end' : null,
          props: {
            vertical: this.stackButtons,
            align: 'right'
          }
        }, child)
      }
    },

    onOk () {
      this.$emit('ok', clone(this.getData()));
      this.hide();
    },

    onCancel () {
      this.hide();
    },

    getData () {
      if (this.prompt) {
        return this.prompt.model
      }
      if (this.options) {
        return this.options.model
      }
    }
  },

  render (h) {
    const child = [];

    if (this.title) {
      child.push(
        h(QCardSection, {
          staticClass: 'q-dialog__title'
        }, [ this.title ])
      );
    }

    if (this.message) {
      child.push(
        h(QCardSection, {
          staticClass: 'q-dialog__message scroll'
        }, [ this.message ])
      );
    }

    if (this.hasForm) {
      child.push(
        h(
          QCardSection,
          { staticClass: 'scroll' },
          this.prompt ? this.getPrompt(h) : this.getOptions(h)
        )
      );
    }

    if (this.ok || this.cancel) {
      child.push(this.getButtons(h));
    }

    return h(QDialog, {
      ref: 'dialog',

      props: {
        ...this.$attrs,
        value: this.value
      },

      on: {
        hide: () => {
          this.$emit('hide');
        }
      }
    }, [
      h(QCard, {
        staticClass: 'q-dialog-plugin' +
          (this.dark === true ? ' q-dialog-plugin--dark' : ''),
        style: this.cardStyle,
        class: this.cardClass,
        props: {
          dark: this.dark
        }
      }, child)
    ])
  }
});

//
//
//
//
//
//
//
//

var script = {
  name: 'Icon',
  props: {
    icon: {
      type: String,
      required: true
    },
    size: {
      type: String,
      required: false
    }
  }
};

function normalizeComponent(template, style, script, scopeId, isFunctionalTemplate, moduleIdentifier
/* server only */
, shadowMode, createInjector, createInjectorSSR, createInjectorShadow) {
  if (typeof shadowMode !== 'boolean') {
    createInjectorSSR = createInjector;
    createInjector = shadowMode;
    shadowMode = false;
  } // Vue.extend constructor export interop.


  var options = typeof script === 'function' ? script.options : script; // render functions

  if (template && template.render) {
    options.render = template.render;
    options.staticRenderFns = template.staticRenderFns;
    options._compiled = true; // functional template

    if (isFunctionalTemplate) {
      options.functional = true;
    }
  } // scopedId


  if (scopeId) {
    options._scopeId = scopeId;
  }

  var hook;

  if (moduleIdentifier) {
    // server build
    hook = function hook(context) {
      // 2.3 injection
      context = context || // cached call
      this.$vnode && this.$vnode.ssrContext || // stateful
      this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext; // functional
      // 2.2 with runInNewContext: true

      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__;
      } // inject component styles


      if (style) {
        style.call(this, createInjectorSSR(context));
      } // register component module identifier for async chunk inference


      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier);
      }
    }; // used by ssr in case component is cached and beforeCreate
    // never gets called


    options._ssrRegister = hook;
  } else if (style) {
    hook = shadowMode ? function () {
      style.call(this, createInjectorShadow(this.$root.$options.shadowRoot));
    } : function (context) {
      style.call(this, createInjector(context));
    };
  }

  if (hook) {
    if (options.functional) {
      // register for functional component in vue file
      var originalRender = options.render;

      options.render = function renderWithStyleInjection(h, context) {
        hook.call(context);
        return originalRender(h, context);
      };
    } else {
      // inject component registration as beforeCreate hook
      var existing = options.beforeCreate;
      options.beforeCreate = existing ? [].concat(existing, hook) : [hook];
    }
  }

  return script;
}

var normalizeComponent_1 = normalizeComponent;

var isOldIE = typeof navigator !== 'undefined' && /msie [6-9]\\b/.test(navigator.userAgent.toLowerCase());
function createInjector(context) {
  return function (id, style) {
    return addStyle(id, style);
  };
}
var HEAD = document.head || document.getElementsByTagName('head')[0];
var styles = {};

function addStyle(id, css) {
  var group = isOldIE ? css.media || 'default' : id;
  var style = styles[group] || (styles[group] = {
    ids: new Set(),
    styles: []
  });

  if (!style.ids.has(id)) {
    style.ids.add(id);
    var code = css.source;

    if (css.map) {
      // https://developer.chrome.com/devtools/docs/javascript-debugging
      // this makes source maps inside style tags work properly in Chrome
      code += '\n/*# sourceURL=' + css.map.sources[0] + ' */'; // http://stackoverflow.com/a/26603875

      code += '\n/*# sourceMappingURL=data:application/json;base64,' + btoa(unescape(encodeURIComponent(JSON.stringify(css.map)))) + ' */';
    }

    if (!style.element) {
      style.element = document.createElement('style');
      style.element.type = 'text/css';
      if (css.media) style.element.setAttribute('media', css.media);
      HEAD.appendChild(style.element);
    }

    if ('styleSheet' in style.element) {
      style.styles.push(code);
      style.element.styleSheet.cssText = style.styles.filter(Boolean).join('\n');
    } else {
      var index = style.ids.size - 1;
      var textNode = document.createTextNode(code);
      var nodes = style.element.childNodes;
      if (nodes[index]) style.element.removeChild(nodes[index]);
      if (nodes.length) style.element.insertBefore(textNode, nodes[index]);else style.element.appendChild(textNode);
    }
  }
}

var browser$1 = createInjector;

/* script */
const __vue_script__ = script;

/* template */
var __vue_render__ = function() {
  var _vm = this;
  var _h = _vm.$createElement;
  var _c = _vm._self._c || _h;
  return _c(
    "div",
    [
      /^mdi|^fa/.test(_vm.icon)
        ? _c("q-icon", { attrs: { name: _vm.icon, size: _vm.size, dense: "" } })
        : _c("q-icon", {
            class: [_vm.icon, "q-icon"],
            style: { "font-size": _vm.size }
          })
    ],
    1
  )
};
var __vue_staticRenderFns__ = [];
__vue_render__._withStripped = true;

  /* style */
  const __vue_inject_styles__ = function (inject) {
    if (!inject) return
    inject("data-v-47ba957c_0", { source: "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n", map: {"version":3,"sources":[],"names":[],"mappings":"","file":"Icon.vue"}, media: undefined });

  };
  /* scoped */
  const __vue_scope_id__ = "data-v-47ba957c";
  /* module identifier */
  const __vue_module_identifier__ = undefined;
  /* functional template */
  const __vue_is_functional_template__ = false;
  /* style inject SSR */
  

  
  var Icon = normalizeComponent_1(
    { render: __vue_render__, staticRenderFns: __vue_staticRenderFns__ },
    __vue_inject_styles__,
    __vue_script__,
    __vue_scope_id__,
    __vue_is_functional_template__,
    __vue_module_identifier__,
    browser$1,
    undefined
  );

//
var script$1 = {
  name: 'TreeView',
  components: { Icon },
  data() {
    return {
      iconSize: '18px',
      left: true,
      filter: '',
      record: false,
      dialog: false,
      categorySearch: false,
      maximizedToggle: true,
      slide: 0,
      pagination: {
        rowsPerPage: 0
      },
      columns: [
        {
          name: 'desc',
          required: true,
          label: 'Title',
          align: 'left',
          field: row => row.subject,
          format: val => `${val}`,
          sortable: true
        },
        { name: 'short_time', align: 'center', label: 'Short time', field: 'short_time', sortable: true },
        { name: 'introduction', align: 'center', label: 'Introduction', field: 'introduction', sortable: true }
      ],
      activeCategory: '',
      tree: {
        data: {
          records: [],
          featured: {}
        },
        categories: {}
      },
      searchData: false
    }
  },
  methods: {
    getTableArray(tableObject) {
      return Object.keys(tableObject).map(function(key) {
        return { ...tableObject[key], id: key }
      })
    },
    getCategories() {
      const aDeferred = $.Deferred();
      return AppConnector.request({ module: 'KnowledgeBase', action: 'TreeAjax', mode: 'categories' }).done(data => {
        this.tree.categories = data.result;
        aDeferred.resolve(data.result);
      })
    },
    getData(category = '') {
      const aDeferred = $.Deferred();
      this.activeCategory = category;
      const progressIndicatorElement = $.progressIndicator({
        blockInfo: { enabled: true }
      });
      return AppConnector.request({
        module: 'KnowledgeBase',
        action: 'TreeAjax',
        mode: 'list',
        category: category
      }).done(data => {
        this.tree.data = data.result;
        progressIndicatorElement.progressIndicator({ mode: 'hide' });
        aDeferred.resolve(data.result);
      })
    },
    getRecord(id) {
      const aDeferred = $.Deferred();
      const progressIndicatorElement = $.progressIndicator({
        blockInfo: { enabled: true }
      });
      return AppConnector.request({
        module: 'KnowledgeBase',
        action: 'TreeAjax',
        mode: 'detail',
        record: id
      }).done(data => {
        this.record = data.result;
        this.dialog = true;
        progressIndicatorElement.progressIndicator({ mode: 'hide' });
        aDeferred.resolve(data.result);
      })
    },
    search(e) {
      if (this.filter.length > 3) {
        const aDeferred = $.Deferred();
        const progressIndicatorElement = $.progressIndicator({
          blockInfo: { enabled: true }
        });
        AppConnector.request({
          module: 'KnowledgeBase',
          action: 'TreeAjax',
          mode: 'search',
          value: this.filter,
          category: this.categorySearch ? this.activeCategory : ''
        }).done(data => {
          this.searchData = data.result;
          aDeferred.resolve(data.result);
          progressIndicatorElement.progressIndicator({ mode: 'hide' });
          return data.result
        });
      } else {
        this.searchData = false;
      }
    }
  },
  async created() {
    await this.getCategories();
    await this.getData();
  }
};

/* script */
const __vue_script__$1 = script$1;

/* template */
var __vue_render__$1 = function() {
  var _vm = this;
  var _h = _vm.$createElement;
  var _c = _vm._self._c || _h;
  return _c(
    "div",
    { staticClass: "h-100" },
    [
      _c(
        "q-layout",
        {
          staticClass: "absolute",
          attrs: { view: "hHh lpr fFf", container: "" }
        },
        [
          _c(
            "q-header",
            { staticClass: "bg-white text-primary", attrs: { elevated: "" } },
            [
              _c(
                "q-toolbar",
                [
                  _c("q-btn", {
                    attrs: { dense: "", flat: "", round: "", icon: "mdi-menu" },
                    on: {
                      click: function($event) {
                        _vm.left = !_vm.left;
                      }
                    }
                  }),
                  _vm._v(" "),
                  _c(
                    "q-breadcrumbs",
                    {
                      directives: [
                        {
                          name: "show",
                          rawName: "v-show",
                          value: !_vm.searchData,
                          expression: "!searchData"
                        }
                      ],
                      staticClass: "ml-2",
                      scopedSlots: _vm._u([
                        {
                          key: "separator",
                          fn: function() {
                            return [
                              _c("q-icon", {
                                attrs: {
                                  size: "1.5em",
                                  name: "mdi-chevron-right"
                                }
                              })
                            ]
                          },
                          proxy: true
                        }
                      ])
                    },
                    [
                      _vm._v(" "),
                      _c("q-breadcrumbs-el", {
                        attrs: { icon: "mdi-file-tree" },
                        on: {
                          click: function($event) {
                            return _vm.getData()
                          }
                        }
                      }),
                      _vm._v(" "),
                      this.activeCategory !== ""
                        ? _vm._l(
                            _vm.tree.categories[this.activeCategory].parentTree,
                            function(category) {
                              return _c(
                                "q-breadcrumbs-el",
                                {
                                  key: _vm.tree.categories[category].label,
                                  on: {
                                    click: function($event) {
                                      return _vm.getData(category)
                                    }
                                  }
                                },
                                [
                                  _c("icon", {
                                    staticClass: "q-mr-sm",
                                    attrs: {
                                      size: _vm.iconSize,
                                      icon: _vm.tree.categories[category].icon
                                    }
                                  }),
                                  _vm._v(
                                    "\n              " +
                                      _vm._s(
                                        _vm.tree.categories[category].label
                                      ) +
                                      "\n            "
                                  )
                                ],
                                1
                              )
                            }
                          )
                        : _vm._e()
                    ],
                    2
                  ),
                  _vm._v(" "),
                  _c(
                    "div",
                    { staticClass: "mx-auto w-50 flex no-wrap" },
                    [
                      _c("q-input", {
                        staticClass: "tree-search",
                        attrs: {
                          placeholder: "Search",
                          rounded: "",
                          outlined: "",
                          type: "search"
                        },
                        on: { input: _vm.search },
                        scopedSlots: _vm._u([
                          {
                            key: "append",
                            fn: function() {
                              return [
                                _c("q-icon", { attrs: { name: "mdi-magnify" } })
                              ]
                            },
                            proxy: true
                          }
                        ]),
                        model: {
                          value: _vm.filter,
                          callback: function($$v) {
                            _vm.filter = $$v;
                          },
                          expression: "filter"
                        }
                      }),
                      _vm._v(" "),
                      _c(
                        "div",
                        [
                          _c("q-toggle", {
                            attrs: { icon: "mdi-file-tree" },
                            model: {
                              value: _vm.categorySearch,
                              callback: function($$v) {
                                _vm.categorySearch = $$v;
                              },
                              expression: "categorySearch"
                            }
                          }),
                          _vm._v(" "),
                          _c("q-tooltip", [
                            _vm._v(
                              "\n              Search current category\n            "
                            )
                          ])
                        ],
                        1
                      )
                    ],
                    1
                  )
                ],
                1
              )
            ],
            1
          ),
          _vm._v(" "),
          _c(
            "q-drawer",
            {
              directives: [
                {
                  name: "show",
                  rawName: "v-show",
                  value: !_vm.searchData,
                  expression: "!searchData"
                }
              ],
              attrs: {
                side: "left",
                elevated: "",
                width: _vm.searchData ? 0 : 250,
                breakpoint: 700,
                "content-class": "bg-white text-black"
              },
              model: {
                value: _vm.left,
                callback: function($$v) {
                  _vm.left = $$v;
                },
                expression: "left"
              }
            },
            [
              _c(
                "q-scroll-area",
                { staticClass: "fit" },
                [
                  _c(
                    "q-list",
                    [
                      _c(
                        "q-item",
                        {
                          directives: [
                            {
                              name: "show",
                              rawName: "v-show",
                              value: _vm.activeCategory === "",
                              expression: "activeCategory === ''"
                            }
                          ],
                          attrs: { clickable: "", active: "" }
                        },
                        [
                          _c(
                            "q-item-section",
                            { attrs: { avatar: "" } },
                            [
                              _c("q-icon", {
                                attrs: {
                                  name: "mdi-file-tree",
                                  size: _vm.iconSize
                                }
                              })
                            ],
                            1
                          ),
                          _vm._v(" "),
                          _c("q-item-section", [
                            _vm._v("\n              Categories\n            ")
                          ])
                        ],
                        1
                      ),
                      _vm._v(" "),
                      _vm.activeCategory !== ""
                        ? _c(
                            "q-item",
                            {
                              attrs: { clickable: "", active: "" },
                              on: {
                                click: function($event) {
                                  return _vm.getData(
                                    _vm.tree.categories[_vm.activeCategory]
                                      .parentTree.length !== 1
                                      ? _vm.tree.categories[_vm.activeCategory]
                                          .parentTree[
                                          _vm.tree.categories[
                                            _vm.activeCategory
                                          ].parentTree.length - 2
                                        ]
                                      : ""
                                  )
                                }
                              }
                            },
                            [
                              _c(
                                "q-item-section",
                                { attrs: { avatar: "" } },
                                [
                                  _c("icon", {
                                    attrs: {
                                      size: _vm.iconSize,
                                      icon:
                                        _vm.tree.categories[_vm.activeCategory]
                                          .icon
                                    }
                                  })
                                ],
                                1
                              ),
                              _vm._v(" "),
                              _c("q-item-section", [
                                _vm._v(
                                  "\n              " +
                                    _vm._s(
                                      _vm.tree.categories[_vm.activeCategory]
                                        .label
                                    ) +
                                    "\n            "
                                )
                              ]),
                              _vm._v(" "),
                              _c(
                                "q-item-section",
                                { attrs: { avatar: "" } },
                                [
                                  _c("q-icon", {
                                    attrs: { name: "mdi-chevron-left" }
                                  })
                                ],
                                1
                              )
                            ],
                            1
                          )
                        : _vm._e(),
                      _vm._v(" "),
                      _vm._l(_vm.tree.data.categories, function(
                        categoryValue,
                        categoryKey
                      ) {
                        return _c(
                          "q-item",
                          {
                            directives: [
                              { name: "ripple", rawName: "v-ripple" }
                            ],
                            key: categoryKey,
                            attrs: { clickable: "" },
                            on: {
                              click: function($event) {
                                return _vm.getData(categoryValue)
                              }
                            }
                          },
                          [
                            _c(
                              "q-item-section",
                              { attrs: { avatar: "" } },
                              [
                                _c("icon", {
                                  attrs: {
                                    size: _vm.iconSize,
                                    icon:
                                      _vm.tree.categories[categoryValue].icon
                                  }
                                })
                              ],
                              1
                            ),
                            _vm._v(" "),
                            _c("q-item-section", [
                              _vm._v(
                                "\n              " +
                                  _vm._s(
                                    _vm.tree.categories[categoryValue].label
                                  ) +
                                  "\n            "
                              )
                            ]),
                            _vm._v(" "),
                            _c(
                              "q-item-section",
                              { attrs: { avatar: "" } },
                              [
                                _c("q-icon", {
                                  attrs: { name: "mdi-chevron-right" }
                                })
                              ],
                              1
                            )
                          ],
                          1
                        )
                      })
                    ],
                    2
                  )
                ],
                1
              )
            ],
            1
          ),
          _vm._v(" "),
          _c(
            "q-page-container",
            [
              _c(
                "q-page",
                { staticClass: "q-pa-md" },
                [
                  !_vm.searchData
                    ? _c(
                        "div",
                        [
                          _c(
                            "div",
                            {
                              directives: [
                                {
                                  name: "show",
                                  rawName: "v-show",
                                  value:
                                    typeof _vm.tree.data.featured.length ===
                                    "undefined",
                                  expression:
                                    "typeof tree.data.featured.length === 'undefined'"
                                }
                              ],
                              staticClass: "q-pa-md row items-start q-gutter-md"
                            },
                            [
                              _vm._l(_vm.tree.data.categories, function(
                                categoryValue,
                                categoryKey
                              ) {
                                return [
                                  _vm.tree.data.featured[categoryValue]
                                    ? _c(
                                        "q-list",
                                        {
                                          key: categoryKey,
                                          staticClass: "home-card",
                                          attrs: {
                                            bordered: "",
                                            padding: "",
                                            dense: ""
                                          }
                                        },
                                        [
                                          _c(
                                            "q-item-label",
                                            {
                                              staticClass: "text-black flex",
                                              attrs: { header: "" },
                                              on: {
                                                click: function($event) {
                                                  return _vm.getData(
                                                    categoryValue
                                                  )
                                                }
                                              }
                                            },
                                            [
                                              _c("icon", {
                                                staticClass: "mr-2",
                                                attrs: {
                                                  icon:
                                                    _vm.tree.categories[
                                                      categoryValue
                                                    ].icon,
                                                  size: _vm.iconSize
                                                }
                                              }),
                                              _vm._v(
                                                "\n                  " +
                                                  _vm._s(
                                                    _vm.tree.categories[
                                                      categoryValue
                                                    ].label
                                                  ) +
                                                  "\n                "
                                              )
                                            ],
                                            1
                                          ),
                                          _vm._v(" "),
                                          _vm._l(
                                            _vm.tree.data.featured[
                                              categoryValue
                                            ],
                                            function(featuredValue) {
                                              return _c(
                                                "q-item",
                                                {
                                                  directives: [
                                                    {
                                                      name: "ripple",
                                                      rawName: "v-ripple"
                                                    }
                                                  ],
                                                  key: featuredValue.id,
                                                  staticClass: "text-subtitle2",
                                                  attrs: { clickable: "" },
                                                  on: {
                                                    click: function($event) {
                                                      $event.preventDefault();
                                                      return _vm.getRecord(
                                                        featuredValue.id
                                                      )
                                                    }
                                                  }
                                                },
                                                [
                                                  _c(
                                                    "q-item-section",
                                                    { attrs: { avatar: "" } },
                                                    [
                                                      _c("q-icon", {
                                                        attrs: {
                                                          name: "mdi-star",
                                                          size: _vm.iconSize
                                                        }
                                                      })
                                                    ],
                                                    1
                                                  ),
                                                  _vm._v(" "),
                                                  _c("q-item-section", [
                                                    _c(
                                                      "a",
                                                      {
                                                        staticClass:
                                                          "js-popover-tooltip--record",
                                                        attrs: {
                                                          href:
                                                            "index.php?module=KnowledgeBase&view=Detail&record=" +
                                                            featuredValue.id
                                                        }
                                                      },
                                                      [
                                                        _vm._v(
                                                          "\n                      " +
                                                            _vm._s(
                                                              featuredValue.subject
                                                            )
                                                        )
                                                      ]
                                                    )
                                                  ])
                                                ],
                                                1
                                              )
                                            }
                                          )
                                        ],
                                        2
                                      )
                                    : _vm._e()
                                ]
                              })
                            ],
                            2
                          ),
                          _vm._v(" "),
                          _c("q-table", {
                            directives: [
                              {
                                name: "show",
                                rawName: "v-show",
                                value: _vm.activeCategory !== "",
                                expression: "activeCategory !== ''"
                              }
                            ],
                            attrs: {
                              data: _vm.getTableArray(_vm.tree.data.records),
                              columns: _vm.columns,
                              "row-key": "subject",
                              grid: "",
                              "hide-header": "",
                              pagination: _vm.pagination
                            },
                            on: {
                              "update:pagination": function($event) {
                                _vm.pagination = $event;
                              }
                            },
                            scopedSlots: _vm._u(
                              [
                                {
                                  key: "item",
                                  fn: function(props) {
                                    return [
                                      _c(
                                        "q-list",
                                        {
                                          staticClass: "list-item",
                                          attrs: { padding: "" },
                                          on: {
                                            click: function($event) {
                                              return _vm.getRecord(props.row.id)
                                            }
                                          }
                                        },
                                        [
                                          _c(
                                            "q-item",
                                            { attrs: { clickable: "" } },
                                            [
                                              _c(
                                                "q-item-section",
                                                { attrs: { avatar: "" } },
                                                [
                                                  _c("q-icon", {
                                                    attrs: { name: "mdi-text" }
                                                  })
                                                ],
                                                1
                                              ),
                                              _vm._v(" "),
                                              _c(
                                                "q-item-section",
                                                [
                                                  _c(
                                                    "q-item-label",
                                                    {
                                                      staticClass:
                                                        "text-primary"
                                                    },
                                                    [
                                                      _vm._v(
                                                        " " +
                                                          _vm._s(
                                                            props.row.subject
                                                          )
                                                      )
                                                    ]
                                                  ),
                                                  _vm._v(" "),
                                                  _c(
                                                    "q-item-label",
                                                    {
                                                      staticClass: "flex",
                                                      attrs: { overline: "" }
                                                    },
                                                    [
                                                      _c(
                                                        "q-breadcrumbs",
                                                        {
                                                          staticClass:
                                                            "mr-2 text-grey-8",
                                                          attrs: {
                                                            "active-color":
                                                              "grey-8"
                                                          }
                                                        },
                                                        _vm._l(
                                                          _vm.tree.categories[
                                                            props.row.category
                                                          ].parentTree,
                                                          function(category) {
                                                            return _c(
                                                              "q-breadcrumbs-el",
                                                              {
                                                                key:
                                                                  _vm.tree
                                                                    .categories[
                                                                    category
                                                                  ].label
                                                              },
                                                              [
                                                                _c("icon", {
                                                                  staticClass:
                                                                    "q-mr-sm",
                                                                  attrs: {
                                                                    size:
                                                                      _vm.iconSize,
                                                                    icon:
                                                                      _vm.tree
                                                                        .categories[
                                                                        category
                                                                      ].icon
                                                                  }
                                                                }),
                                                                _vm._v(
                                                                  "\n                          " +
                                                                    _vm._s(
                                                                      _vm.tree
                                                                        .categories[
                                                                        category
                                                                      ].label
                                                                    ) +
                                                                    "\n                        "
                                                                )
                                                              ],
                                                              1
                                                            )
                                                          }
                                                        ),
                                                        1
                                                      ),
                                                      _vm._v(
                                                        "\n\n                      | Authored by: " +
                                                          _vm._s(
                                                            props.row
                                                              .assigned_user_id
                                                          )
                                                      )
                                                    ],
                                                    1
                                                  ),
                                                  _vm._v(" "),
                                                  _c(
                                                    "q-item-label",
                                                    { attrs: { caption: "" } },
                                                    [
                                                      _vm._v(
                                                        _vm._s(
                                                          props.row.introduction
                                                        )
                                                      )
                                                    ]
                                                  )
                                                ],
                                                1
                                              ),
                                              _vm._v(" "),
                                              _c(
                                                "q-item-section",
                                                {
                                                  attrs: { side: "", top: "" }
                                                },
                                                [
                                                  _c(
                                                    "q-item-label",
                                                    { attrs: { caption: "" } },
                                                    [
                                                      _vm._v(
                                                        _vm._s(
                                                          props.row.short_time
                                                        )
                                                      )
                                                    ]
                                                  ),
                                                  _vm._v(" "),
                                                  _c("q-tooltip", [
                                                    _vm._v(
                                                      "\n                      " +
                                                        _vm._s(
                                                          props.row.full_time
                                                        ) +
                                                        "\n                    "
                                                    )
                                                  ])
                                                ],
                                                1
                                              )
                                            ],
                                            1
                                          )
                                        ],
                                        1
                                      )
                                    ]
                                  }
                                },
                                {
                                  key: "bottom",
                                  fn: function(props) {
                                    return undefined
                                  }
                                }
                              ],
                              null,
                              false,
                              294689421
                            )
                          })
                        ],
                        1
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.searchData
                    ? _c("q-table", {
                        attrs: {
                          data: _vm.getTableArray(_vm.searchData),
                          columns: _vm.columns,
                          "row-key": "subject",
                          grid: "",
                          "hide-header": ""
                        },
                        scopedSlots: _vm._u(
                          [
                            {
                              key: "item",
                              fn: function(props) {
                                return [
                                  _c(
                                    "q-list",
                                    {
                                      staticClass: "list-item",
                                      attrs: { padding: "" },
                                      on: {
                                        click: function($event) {
                                          return _vm.getRecord(props.row.id)
                                        }
                                      }
                                    },
                                    [
                                      _c(
                                        "q-item",
                                        { attrs: { clickable: "" } },
                                        [
                                          _c(
                                            "q-item-section",
                                            { attrs: { avatar: "" } },
                                            [
                                              _c("q-icon", {
                                                attrs: { name: "mdi-text" }
                                              })
                                            ],
                                            1
                                          ),
                                          _vm._v(" "),
                                          _c(
                                            "q-item-section",
                                            [
                                              _c(
                                                "q-item-label",
                                                { staticClass: "text-primary" },
                                                [
                                                  _vm._v(
                                                    " " +
                                                      _vm._s(props.row.subject)
                                                  )
                                                ]
                                              ),
                                              _vm._v(" "),
                                              _c(
                                                "q-item-label",
                                                {
                                                  staticClass: "flex",
                                                  attrs: { overline: "" }
                                                },
                                                [
                                                  _c(
                                                    "q-breadcrumbs",
                                                    {
                                                      staticClass:
                                                        "mr-2 text-grey-8",
                                                      attrs: {
                                                        "active-color": "grey-8"
                                                      }
                                                    },
                                                    _vm._l(
                                                      _vm.tree.categories[
                                                        props.row.category
                                                      ].parentTree,
                                                      function(category) {
                                                        return _c(
                                                          "q-breadcrumbs-el",
                                                          {
                                                            key:
                                                              _vm.tree
                                                                .categories[
                                                                category
                                                              ].label
                                                          },
                                                          [
                                                            _c("icon", {
                                                              staticClass:
                                                                "q-mr-sm",
                                                              attrs: {
                                                                size:
                                                                  _vm.iconSize,
                                                                icon:
                                                                  _vm.tree
                                                                    .categories[
                                                                    category
                                                                  ].icon
                                                              }
                                                            }),
                                                            _vm._v(
                                                              "\n                        " +
                                                                _vm._s(
                                                                  _vm.tree
                                                                    .categories[
                                                                    category
                                                                  ].label
                                                                ) +
                                                                "\n                      "
                                                            )
                                                          ],
                                                          1
                                                        )
                                                      }
                                                    ),
                                                    1
                                                  ),
                                                  _vm._v(
                                                    "\n\n                    | Authored by: " +
                                                      _vm._s(
                                                        props.row
                                                          .assigned_user_id
                                                      )
                                                  )
                                                ],
                                                1
                                              ),
                                              _vm._v(" "),
                                              _c(
                                                "q-item-label",
                                                { attrs: { caption: "" } },
                                                [
                                                  _vm._v(
                                                    _vm._s(
                                                      props.row.introduction
                                                    )
                                                  )
                                                ]
                                              )
                                            ],
                                            1
                                          ),
                                          _vm._v(" "),
                                          _c(
                                            "q-item-section",
                                            { attrs: { side: "", top: "" } },
                                            [
                                              _c(
                                                "q-item-label",
                                                { attrs: { caption: "" } },
                                                [
                                                  _vm._v(
                                                    _vm._s(props.row.short_time)
                                                  )
                                                ]
                                              ),
                                              _vm._v(" "),
                                              _c("q-tooltip", [
                                                _vm._v(
                                                  "\n                    " +
                                                    _vm._s(
                                                      props.row.full_time
                                                    ) +
                                                    "\n                  "
                                                )
                                              ])
                                            ],
                                            1
                                          )
                                        ],
                                        1
                                      )
                                    ],
                                    1
                                  )
                                ]
                              }
                            },
                            {
                              key: "bottom",
                              fn: function(props) {
                                return undefined
                              }
                            }
                          ],
                          null,
                          false,
                          2628749
                        )
                      })
                    : _vm._e()
                ],
                1
              )
            ],
            1
          )
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "q-dialog",
        {
          attrs: {
            persistent: "",
            maximized: _vm.maximizedToggle,
            "transition-show": "slide-up",
            "transition-hide": "slide-down"
          },
          model: {
            value: _vm.dialog,
            callback: function($$v) {
              _vm.dialog = $$v;
            },
            expression: "dialog"
          }
        },
        [
          _c(
            "q-card",
            { staticClass: "quasar-reset" },
            [
              _c(
                "q-bar",
                [
                  _c("q-space"),
                  _vm._v(" "),
                  _c(
                    "q-btn",
                    {
                      attrs: {
                        dense: "",
                        flat: "",
                        icon: "mdi-window-minimize",
                        disable: !_vm.maximizedToggle
                      },
                      on: {
                        click: function($event) {
                          _vm.maximizedToggle = false;
                        }
                      }
                    },
                    [
                      _vm.maximizedToggle
                        ? _c(
                            "q-tooltip",
                            {
                              attrs: {
                                "content-class": "bg-white text-primary"
                              }
                            },
                            [_vm._v("Minimize")]
                          )
                        : _vm._e()
                    ],
                    1
                  ),
                  _vm._v(" "),
                  _c(
                    "q-btn",
                    {
                      attrs: {
                        dense: "",
                        flat: "",
                        icon: "mdi-window-maximize",
                        disable: _vm.maximizedToggle
                      },
                      on: {
                        click: function($event) {
                          _vm.maximizedToggle = true;
                        }
                      }
                    },
                    [
                      !_vm.maximizedToggle
                        ? _c(
                            "q-tooltip",
                            {
                              attrs: {
                                "content-class": "bg-white text-primary"
                              }
                            },
                            [_vm._v("Maximize")]
                          )
                        : _vm._e()
                    ],
                    1
                  ),
                  _vm._v(" "),
                  _c(
                    "q-btn",
                    {
                      directives: [
                        { name: "close-popup", rawName: "v-close-popup" }
                      ],
                      attrs: { dense: "", flat: "", icon: "mdi-close" }
                    },
                    [
                      _c(
                        "q-tooltip",
                        { attrs: { "content-class": "bg-white text-primary" } },
                        [_vm._v("Close")]
                      )
                    ],
                    1
                  )
                ],
                1
              ),
              _vm._v(" "),
              _c("q-card-section", [
                _c("div", { staticClass: "text-h6" }, [
                  _vm._v(_vm._s(_vm.record.subject))
                ])
              ]),
              _vm._v(" "),
              _c("q-card-section", [
                _vm._v(
                  "\n        " + _vm._s(_vm.record.introduction) + "\n      "
                )
              ]),
              _vm._v(" "),
              _c(
                "q-card-section",
                [
                  _vm.record.knowledgebase_view === "PLL_PRESENTATION"
                    ? _c(
                        "q-carousel",
                        {
                          staticClass:
                            "bg-white text-black shadow-1 rounded-borders",
                          attrs: {
                            "transition-prev": "scale",
                            "transition-next": "scale",
                            swipeable: "",
                            animated: "",
                            "control-color": "black",
                            navigation: "",
                            padding: "",
                            arrows: "",
                            height: "300px"
                          },
                          model: {
                            value: _vm.slide,
                            callback: function($$v) {
                              _vm.slide = $$v;
                            },
                            expression: "slide"
                          }
                        },
                        _vm._l(_vm.record.content, function(slide, index) {
                          return _c(
                            "q-carousel-slide",
                            {
                              key: index,
                              staticClass: "column no-wrap flex-center",
                              attrs: { name: index }
                            },
                            [
                              _c("div", {
                                domProps: { innerHTML: _vm._s(slide) }
                              })
                            ]
                          )
                        }),
                        1
                      )
                    : _c("div", {
                        domProps: { innerHTML: _vm._s(_vm.record.content) }
                      })
                ],
                1
              )
            ],
            1
          )
        ],
        1
      )
    ],
    1
  )
};
var __vue_staticRenderFns__$1 = [];
__vue_render__$1._withStripped = true;

  /* style */
  const __vue_inject_styles__$1 = function (inject) {
    if (!inject) return
    inject("data-v-05a4b4f8_0", { source: "\n.tree-search {\r\n  width: 100%;\n}\n.tree-search .q-field__control,\r\n.tree-search .q-field__marginal {\r\n  height: 40px;\n}\n.home-card {\r\n  width: 100%;\r\n  max-width: 250px;\n}\n.list-item {\r\n  width: 100%;\n}\r\n", map: {"version":3,"sources":["C:\\www\\YetiForceCRM\\public_html\\src\\modules\\KnowledgeBase\\TreeView.vue"],"names":[],"mappings":";AA+ZA;EACA,WAAA;AACA;AACA;;EAEA,YAAA;AACA;AACA;EACA,WAAA;EACA,gBAAA;AACA;AACA;EACA,WAAA;AACA","file":"TreeView.vue","sourcesContent":["/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */\r\n\r\n<template>\r\n  <div class=\"h-100\">\r\n    <q-layout view=\"hHh lpr fFf\" container class=\"absolute\">\r\n      <q-header elevated class=\"bg-white text-primary\">\r\n        <q-toolbar>\r\n          <q-btn dense flat round icon=\"mdi-menu\" @click=\"left = !left\"></q-btn>\r\n          <q-breadcrumbs v-show=\"!searchData\" class=\"ml-2\">\r\n            <template v-slot:separator>\r\n              <q-icon size=\"1.5em\" name=\"mdi-chevron-right\" />\r\n            </template>\r\n            <q-breadcrumbs-el icon=\"mdi-file-tree\" @click=\"getData()\" />\r\n            <template v-if=\"this.activeCategory !== ''\">\r\n              <q-breadcrumbs-el\r\n                v-for=\"category in tree.categories[this.activeCategory].parentTree\"\r\n                :key=\"tree.categories[category].label\"\r\n                @click=\"getData(category)\"\r\n              >\r\n                <icon :size=\"iconSize\" :icon=\"tree.categories[category].icon\" class=\"q-mr-sm\"></icon>\r\n                {{ tree.categories[category].label }}\r\n              </q-breadcrumbs-el>\r\n            </template>\r\n          </q-breadcrumbs>\r\n          <div class=\"mx-auto w-50 flex no-wrap\">\r\n            <q-input\r\n              class=\"tree-search\"\r\n              v-model=\"filter\"\r\n              placeholder=\"Search\"\r\n              rounded\r\n              outlined\r\n              type=\"search\"\r\n              @input=\"search\"\r\n            >\r\n              <template v-slot:append>\r\n                <q-icon name=\"mdi-magnify\" />\r\n              </template>\r\n            </q-input>\r\n            <div>\r\n              <q-toggle v-model=\"categorySearch\" icon=\"mdi-file-tree\" />\r\n              <q-tooltip>\r\n                Search current category\r\n              </q-tooltip>\r\n            </div>\r\n          </div>\r\n        </q-toolbar>\r\n      </q-header>\r\n\r\n      <q-drawer\r\n        v-show=\"!searchData\"\r\n        v-model=\"left\"\r\n        side=\"left\"\r\n        elevated\r\n        :width=\"searchData ? 0 : 250\"\r\n        :breakpoint=\"700\"\r\n        content-class=\"bg-white text-black\"\r\n      >\r\n        <q-scroll-area class=\"fit\">\r\n          <q-list>\r\n            <q-item v-show=\"activeCategory === ''\" clickable active>\r\n              <q-item-section avatar>\r\n                <q-icon name=\"mdi-file-tree\" :size=\"iconSize\" />\r\n              </q-item-section>\r\n              <q-item-section>\r\n                Categories\r\n              </q-item-section>\r\n            </q-item>\r\n            <q-item\r\n              v-if=\"activeCategory !== ''\"\r\n              clickable\r\n              active\r\n              @click=\"\r\n                getData(\r\n                  tree.categories[activeCategory].parentTree.length !== 1\r\n                    ? tree.categories[activeCategory].parentTree[tree.categories[activeCategory].parentTree.length - 2]\r\n                    : ''\r\n                )\r\n              \"\r\n            >\r\n              <q-item-section avatar>\r\n                <icon :size=\"iconSize\" :icon=\"tree.categories[activeCategory].icon\" />\r\n              </q-item-section>\r\n              <q-item-section>\r\n                {{ tree.categories[activeCategory].label }}\r\n              </q-item-section>\r\n              <q-item-section avatar>\r\n                <q-icon name=\"mdi-chevron-left\" />\r\n              </q-item-section>\r\n            </q-item>\r\n            <q-item\r\n              v-for=\"(categoryValue, categoryKey) in tree.data.categories\"\r\n              :key=\"categoryKey\"\r\n              clickable\r\n              v-ripple\r\n              @click=\"getData(categoryValue)\"\r\n            >\r\n              <q-item-section avatar>\r\n                <icon :size=\"iconSize\" :icon=\"tree.categories[categoryValue].icon\" />\r\n              </q-item-section>\r\n              <q-item-section>\r\n                {{ tree.categories[categoryValue].label }}\r\n              </q-item-section>\r\n              <q-item-section avatar>\r\n                <q-icon name=\"mdi-chevron-right\" />\r\n              </q-item-section>\r\n            </q-item>\r\n          </q-list>\r\n        </q-scroll-area>\r\n      </q-drawer>\r\n\r\n      <q-page-container>\r\n        <q-page class=\"q-pa-md\">\r\n          <div v-if=\"!searchData\">\r\n            <div v-show=\"typeof tree.data.featured.length === 'undefined'\" class=\"q-pa-md row items-start q-gutter-md\">\r\n              <template v-for=\"(categoryValue, categoryKey) in tree.data.categories\">\r\n                <q-list\r\n                  bordered\r\n                  padding\r\n                  dense\r\n                  v-if=\"tree.data.featured[categoryValue]\"\r\n                  :key=\"categoryKey\"\r\n                  class=\"home-card\"\r\n                >\r\n                  <q-item-label header class=\"text-black flex\" @click=\"getData(categoryValue)\">\r\n                    <icon :icon=\"tree.categories[categoryValue].icon\" :size=\"iconSize\" class=\"mr-2\"></icon>\r\n                    {{ tree.categories[categoryValue].label }}\r\n                  </q-item-label>\r\n                  <q-item\r\n                    clickable\r\n                    v-for=\"featuredValue in tree.data.featured[categoryValue]\"\r\n                    :key=\"featuredValue.id\"\r\n                    class=\"text-subtitle2\"\r\n                    v-ripple\r\n                    @click.prevent=\"getRecord(featuredValue.id)\"\r\n                  >\r\n                    <q-item-section avatar>\r\n                      <q-icon name=\"mdi-star\" :size=\"iconSize\"></q-icon>\r\n                    </q-item-section>\r\n                    <q-item-section>\r\n                      <a\r\n                        class=\"js-popover-tooltip--record\"\r\n                        :href=\"`index.php?module=KnowledgeBase&view=Detail&record=${featuredValue.id}`\"\r\n                      >\r\n                        {{ featuredValue.subject }}</a\r\n                      >\r\n                    </q-item-section>\r\n                  </q-item>\r\n                </q-list>\r\n              </template>\r\n            </div>\r\n            <q-table\r\n              v-show=\"activeCategory !== ''\"\r\n              :data=\"getTableArray(tree.data.records)\"\r\n              :columns=\"columns\"\r\n              row-key=\"subject\"\r\n              grid\r\n              hide-header\r\n              :pagination.sync=\"pagination\"\r\n            >\r\n              <template v-slot:item=\"props\">\r\n                <q-list class=\"list-item\" padding @click=\"getRecord(props.row.id)\">\r\n                  <q-item clickable>\r\n                    <q-item-section avatar>\r\n                      <q-icon name=\"mdi-text\" />\r\n                    </q-item-section>\r\n                    <q-item-section>\r\n                      <q-item-label class=\"text-primary\"> {{ props.row.subject }}</q-item-label>\r\n                      <q-item-label class=\"flex\" overline>\r\n                        <q-breadcrumbs class=\"mr-2 text-grey-8\" active-color=\"grey-8\">\r\n                          <q-breadcrumbs-el\r\n                            v-for=\"category in tree.categories[props.row.category].parentTree\"\r\n                            :key=\"tree.categories[category].label\"\r\n                          >\r\n                            <icon :size=\"iconSize\" :icon=\"tree.categories[category].icon\" class=\"q-mr-sm\" />\r\n                            {{ tree.categories[category].label }}\r\n                          </q-breadcrumbs-el>\r\n                        </q-breadcrumbs>\r\n\r\n                        | Authored by: {{ props.row.assigned_user_id }}</q-item-label\r\n                      >\r\n                      <q-item-label caption>{{ props.row.introduction }}</q-item-label>\r\n                    </q-item-section>\r\n                    <q-item-section side top>\r\n                      <q-item-label caption>{{ props.row.short_time }}</q-item-label>\r\n                      <q-tooltip>\r\n                        {{ props.row.full_time }}\r\n                      </q-tooltip>\r\n                    </q-item-section>\r\n                  </q-item>\r\n                </q-list>\r\n              </template>\r\n              <template v-slot:bottom=\"props\"> </template>\r\n            </q-table>\r\n          </div>\r\n          <q-table\r\n            v-if=\"searchData\"\r\n            :data=\"getTableArray(searchData)\"\r\n            :columns=\"columns\"\r\n            row-key=\"subject\"\r\n            grid\r\n            hide-header\r\n          >\r\n            <template v-slot:item=\"props\">\r\n              <q-list class=\"list-item\" padding @click=\"getRecord(props.row.id)\">\r\n                <q-item clickable>\r\n                  <q-item-section avatar>\r\n                    <q-icon name=\"mdi-text\" />\r\n                  </q-item-section>\r\n                  <q-item-section>\r\n                    <q-item-label class=\"text-primary\"> {{ props.row.subject }}</q-item-label>\r\n                    <q-item-label class=\"flex\" overline>\r\n                      <q-breadcrumbs class=\"mr-2 text-grey-8\" active-color=\"grey-8\">\r\n                        <q-breadcrumbs-el\r\n                          v-for=\"category in tree.categories[props.row.category].parentTree\"\r\n                          :key=\"tree.categories[category].label\"\r\n                        >\r\n                          <icon :size=\"iconSize\" :icon=\"tree.categories[category].icon\" class=\"q-mr-sm\" />\r\n                          {{ tree.categories[category].label }}\r\n                        </q-breadcrumbs-el>\r\n                      </q-breadcrumbs>\r\n\r\n                      | Authored by: {{ props.row.assigned_user_id }}</q-item-label\r\n                    >\r\n                    <q-item-label caption>{{ props.row.introduction }}</q-item-label>\r\n                  </q-item-section>\r\n                  <q-item-section side top>\r\n                    <q-item-label caption>{{ props.row.short_time }}</q-item-label>\r\n                    <q-tooltip>\r\n                      {{ props.row.full_time }}\r\n                    </q-tooltip>\r\n                  </q-item-section>\r\n                </q-item>\r\n              </q-list>\r\n            </template>\r\n            <template v-slot:bottom=\"props\"> </template>\r\n          </q-table>\r\n        </q-page>\r\n      </q-page-container>\r\n    </q-layout>\r\n    <q-dialog\r\n      v-model=\"dialog\"\r\n      persistent\r\n      :maximized=\"maximizedToggle\"\r\n      transition-show=\"slide-up\"\r\n      transition-hide=\"slide-down\"\r\n    >\r\n      <q-card class=\"quasar-reset\">\r\n        <q-bar>\r\n          <q-space />\r\n          <q-btn dense flat icon=\"mdi-window-minimize\" @click=\"maximizedToggle = false\" :disable=\"!maximizedToggle\">\r\n            <q-tooltip v-if=\"maximizedToggle\" content-class=\"bg-white text-primary\">Minimize</q-tooltip>\r\n          </q-btn>\r\n          <q-btn dense flat icon=\"mdi-window-maximize\" @click=\"maximizedToggle = true\" :disable=\"maximizedToggle\">\r\n            <q-tooltip v-if=\"!maximizedToggle\" content-class=\"bg-white text-primary\">Maximize</q-tooltip>\r\n          </q-btn>\r\n          <q-btn dense flat icon=\"mdi-close\" v-close-popup>\r\n            <q-tooltip content-class=\"bg-white text-primary\">Close</q-tooltip>\r\n          </q-btn>\r\n        </q-bar>\r\n\r\n        <q-card-section>\r\n          <div class=\"text-h6\">{{ record.subject }}</div>\r\n        </q-card-section>\r\n        <q-card-section>\r\n          {{ record.introduction }}\r\n        </q-card-section>\r\n        <q-card-section>\r\n          <q-carousel\r\n            v-if=\"record.knowledgebase_view === 'PLL_PRESENTATION'\"\r\n            v-model=\"slide\"\r\n            transition-prev=\"scale\"\r\n            transition-next=\"scale\"\r\n            swipeable\r\n            animated\r\n            control-color=\"black\"\r\n            navigation\r\n            padding\r\n            arrows\r\n            height=\"300px\"\r\n            class=\"bg-white text-black shadow-1 rounded-borders\"\r\n          >\r\n            <q-carousel-slide\r\n              v-for=\"(slide, index) in record.content\"\r\n              :name=\"index\"\r\n              :key=\"index\"\r\n              class=\"column no-wrap flex-center\"\r\n            >\r\n              <div v-html=\"slide\"></div>\r\n            </q-carousel-slide>\r\n          </q-carousel>\r\n          <div v-else v-html=\"record.content\"></div>\r\n        </q-card-section>\r\n      </q-card>\r\n    </q-dialog>\r\n  </div>\r\n</template>\r\n<script>\r\nimport Icon from '../../components/Icon.vue'\r\nexport default {\r\n  name: 'TreeView',\r\n  components: { Icon },\r\n  data() {\r\n    return {\r\n      iconSize: '18px',\r\n      left: true,\r\n      filter: '',\r\n      record: false,\r\n      dialog: false,\r\n      categorySearch: false,\r\n      maximizedToggle: true,\r\n      slide: 0,\r\n      pagination: {\r\n        rowsPerPage: 0\r\n      },\r\n      columns: [\r\n        {\r\n          name: 'desc',\r\n          required: true,\r\n          label: 'Title',\r\n          align: 'left',\r\n          field: row => row.subject,\r\n          format: val => `${val}`,\r\n          sortable: true\r\n        },\r\n        { name: 'short_time', align: 'center', label: 'Short time', field: 'short_time', sortable: true },\r\n        { name: 'introduction', align: 'center', label: 'Introduction', field: 'introduction', sortable: true }\r\n      ],\r\n      activeCategory: '',\r\n      tree: {\r\n        data: {\r\n          records: [],\r\n          featured: {}\r\n        },\r\n        categories: {}\r\n      },\r\n      searchData: false\r\n    }\r\n  },\r\n  methods: {\r\n    getTableArray(tableObject) {\r\n      return Object.keys(tableObject).map(function(key) {\r\n        return { ...tableObject[key], id: key }\r\n      })\r\n    },\r\n    getCategories() {\r\n      const aDeferred = $.Deferred()\r\n      return AppConnector.request({ module: 'KnowledgeBase', action: 'TreeAjax', mode: 'categories' }).done(data => {\r\n        this.tree.categories = data.result\r\n        aDeferred.resolve(data.result)\r\n      })\r\n    },\r\n    getData(category = '') {\r\n      const aDeferred = $.Deferred()\r\n      this.activeCategory = category\r\n      const progressIndicatorElement = $.progressIndicator({\r\n        blockInfo: { enabled: true }\r\n      })\r\n      return AppConnector.request({\r\n        module: 'KnowledgeBase',\r\n        action: 'TreeAjax',\r\n        mode: 'list',\r\n        category: category\r\n      }).done(data => {\r\n        this.tree.data = data.result\r\n        progressIndicatorElement.progressIndicator({ mode: 'hide' })\r\n        aDeferred.resolve(data.result)\r\n      })\r\n    },\r\n    getRecord(id) {\r\n      const aDeferred = $.Deferred()\r\n      const progressIndicatorElement = $.progressIndicator({\r\n        blockInfo: { enabled: true }\r\n      })\r\n      return AppConnector.request({\r\n        module: 'KnowledgeBase',\r\n        action: 'TreeAjax',\r\n        mode: 'detail',\r\n        record: id\r\n      }).done(data => {\r\n        this.record = data.result\r\n        this.dialog = true\r\n        progressIndicatorElement.progressIndicator({ mode: 'hide' })\r\n        aDeferred.resolve(data.result)\r\n      })\r\n    },\r\n    search(e) {\r\n      if (this.filter.length > 3) {\r\n        const aDeferred = $.Deferred()\r\n        const progressIndicatorElement = $.progressIndicator({\r\n          blockInfo: { enabled: true }\r\n        })\r\n        AppConnector.request({\r\n          module: 'KnowledgeBase',\r\n          action: 'TreeAjax',\r\n          mode: 'search',\r\n          value: this.filter,\r\n          category: this.categorySearch ? this.activeCategory : ''\r\n        }).done(data => {\r\n          this.searchData = data.result\r\n          aDeferred.resolve(data.result)\r\n          progressIndicatorElement.progressIndicator({ mode: 'hide' })\r\n          return data.result\r\n        })\r\n      } else {\r\n        this.searchData = false\r\n      }\r\n    }\r\n  },\r\n  async created() {\r\n    await this.getCategories()\r\n    await this.getData()\r\n  }\r\n}\r\n</script>\r\n<style>\r\n.tree-search {\r\n  width: 100%;\r\n}\r\n.tree-search .q-field__control,\r\n.tree-search .q-field__marginal {\r\n  height: 40px;\r\n}\r\n.home-card {\r\n  width: 100%;\r\n  max-width: 250px;\r\n}\r\n.list-item {\r\n  width: 100%;\r\n}\r\n</style>\r\n"]}, media: undefined });

  };
  /* scoped */
  const __vue_scope_id__$1 = undefined;
  /* module identifier */
  const __vue_module_identifier__$1 = undefined;
  /* functional template */
  const __vue_is_functional_template__$1 = false;
  /* style inject SSR */
  

  
  var TreeView = normalizeComponent_1(
    { render: __vue_render__$1, staticRenderFns: __vue_staticRenderFns__$1 },
    __vue_inject_styles__$1,
    __vue_script__$1,
    __vue_scope_id__$1,
    __vue_is_functional_template__$1,
    __vue_module_identifier__$1,
    browser$1,
    undefined
  );

//
var script$2 = {
  name: 'Tree',
  components: { TreeView }
};

/* script */
const __vue_script__$2 = script$2;

/* template */
var __vue_render__$2 = function() {
  var _vm = this;
  var _h = _vm.$createElement;
  var _c = _vm._self._c || _h;
  return _c("tree-view")
};
var __vue_staticRenderFns__$2 = [];
__vue_render__$2._withStripped = true;

  /* style */
  const __vue_inject_styles__$2 = undefined;
  /* scoped */
  const __vue_scope_id__$2 = undefined;
  /* module identifier */
  const __vue_module_identifier__$2 = undefined;
  /* functional template */
  const __vue_is_functional_template__$2 = false;
  /* style inject */
  
  /* style inject SSR */
  

  
  var Tree = normalizeComponent_1(
    { render: __vue_render__$2, staticRenderFns: __vue_staticRenderFns__$2 },
    __vue_inject_styles__$2,
    __vue_script__$2,
    __vue_scope_id__$2,
    __vue_is_functional_template__$2,
    __vue_module_identifier__$2,
    undefined,
    undefined
  );

/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const Quasar = {
	...VuePlugin,
	install(Vue, opts) {
		VuePlugin.install(Vue, {
			components: {
				QLayout,
				QPageContainer,
				QPage,
				QHeader,
				QFooter,
				QDrawer,
				QPageSticky,
				QPageScroller,
				QToolbar,
				QToolbarTitle,
				QBtn,
				QBreadcrumbs,
				QBreadcrumbsEl,
				QIcon,
				QInput,
				QToggle,
				QTooltip,
				QScrollArea,
				QList,
				QItem,
				QItemLabel,
				QItemSection,
				QTable,
				QDialog,
				QCard,
				QBar,
				QSpace,
				QCardSection,
				QCarousel,
				QCarouselSlide,
				QCarouselControl
			},
			directives: directives$1,
			plugins: {},
			...opts
		});
	}
};
window.Vue.use(Quasar);

let VueInstance = null;
window.KnowledgeBaseTree = {
	component: Tree,
	mount(config) {
		VueInstance = new window.Vue(Tree).$mount(config.el);
		return VueInstance
	}
};
var VueInstance$1 = VueInstance;

module.exports = VueInstance$1;
//# sourceMappingURL=Tree.vue.js.map
