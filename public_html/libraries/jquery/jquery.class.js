(function (l) {
	var j = {
		undHash: /_|-/,
		colons: /::/,
		words: /([A-Z]+)([A-Z][a-z])/g,
		lowUp: /([a-z\d])([A-Z])/g,
		dash: /([a-z\d])([A-Z])/g,
		replacer: /\{([^\}]+)\}/g,
		dot: /\./
	},
			r = function (b, g, f) {
				return b[g] || f && (b[g] = {})
			},
			s = function (b) {
				return (b = typeof b) && (b == "function" || b == "object")
			},
			q = function (b, g, f) {
				b = b ? b.split(j.dot) : [];
				var h = b.length;
				g = l.isArray(g) ? g : [g || window];
				var a, d, e, c = 0;
				if (h == 0)
					return g[0];
				for (; a = g[c++]; ) {
					for (e = 0; e < h - 1 && s(a); e++)
						a = r(a, b[e], f);
					if (s(a)) {
						d = r(a, b[e], f);
						if (d !== undefined) {
							f === false && delete a[b[e]];
							return d
						}
					}
				}
			},
			p = l.String = l.extend(l.String || {}, {
				getObject: q,
				capitalize: function (b) {
					return b.charAt(0).toUpperCase() + b.substr(1)
				},
				camelize: function (b) {
					b = p.classize(b);
					return b.charAt(0).toLowerCase() + b.substr(1)
				},
				classize: function (b, g) {
					b = b.split(j.undHash);
					for (var f = 0; f < b.length; f++)
						b[f] = p.capitalize(b[f]);
					return b.join(g || "")
				},
				niceName: function () {
					p.classize(parts[i], " ")
				},
				underscore: function (b) {
					return b.replace(j.colons, "/").replace(j.words, "$1_$2").replace(j.lowUp, "$1_$2").replace(j.dash, "_").toLowerCase()
				},
				sub: function (b, g, f) {
					var h = [];
					h.push(b.replace(j.replacer, function (a, d) {
						a = q(d, g, typeof f == "boolean" ? !f : f);
						d = typeof a;
						if ((d === "object" || d === "function") && d !== null) {
							h.push(a);
							return ""
						} else
							return "" + a
					}));
					return h.length <= 1 ? h[0] : h
				}
			})
})(jQuery);
(function (l) {
	var j = false,
			r = l.makeArray,
			s = l.isFunction,
			q = l.isArray,
			p = l.extend,
			b = function (a, d) {
				return a.concat(r(d))
			},
			g = /xyz/.test(function () {}) ? /\b_super\b/ : /.*/,
			f = function (a, d, e) {
				e = e || a;
				for (var c in a)
					e[c] = s(a[c]) && s(d[c]) && g.test(a[c]) ? function (n, o) {
						return function () {
							var m = this._super,
									k;
							this._super = d[n];
							k = o.apply(this, arguments);
							this._super = m;
							return k
						}
					}(c, a[c]) : a[c]
			},
			h = l.Class = function () {
				arguments.length && h.extend.apply(h, arguments)
			};
	p(h, {
		callback: function (a) {
			var d = r(arguments),
					e;
			a = d.shift();
			q(a) ||
					(a = [a]);
			e = this;
			return function () {
				for (var c = b(d, arguments), n, o = a.length, m = 0, k; m < o; m++)
					if (k = a[m]) {
						if ((n = typeof k == "string") && e._set_called)
							e.called = k;
						c = (n ? e[k] : k).apply(e, c || []);
						if (m < o - 1)
							c = !q(c) || c._use_call ? [c] : c
					}
				return c
			}
		},
		getObject: l.String.getObject,
		newInstance: function () {
			var a = this.rawInstance(),
					d;
			if (a.setup)
				d = a.setup.apply(a, arguments);
			if (a.init)
				a.init.apply(a, q(d) ? d : arguments);
			return a
		},
		setup: function (a) {
			this.defaults = p(true, {}, a.defaults, this.defaults);
			return arguments
		},
		rawInstance: function () {
			j =
					true;
			var a = new this;
			j = false;
			return a
		},
		extend: function (a, d, e) {
			function c() {
				if (!j)
					return this.constructor !== c && arguments.length ? arguments.callee.extend.apply(arguments.callee, arguments) : this.Class.newInstance.apply(this.Class, arguments)
			}
			if (typeof a != "string") {
				e = d;
				d = a;
				a = null
			}
			if (!e) {
				e = d;
				d = null
			}
			e = e || {};
			var n = this,
					o = this.prototype,
					m, k, t, u;
			j = true;
			u = new this;
			j = false;
			f(e, o, u);
			for (m in this)
				if (this.hasOwnProperty(m))
					c[m] = this[m];
			f(d, this, c);
			if (a) {
				t = a.split(/\./);
				k = t.pop();
				t = o = h.getObject(t.join("."), window,
						true);
				o[k] = c
			}
			p(c, {
				prototype: u,
				namespace: t,
				shortName: k,
				constructor: c,
				fullName: a
			});
			c.prototype.Class = c.prototype.constructor = c;
			n = c.setup.apply(c, b([n], arguments));
			if (c.init)
				c.init.apply(c, n || []);
			return c
		}
	});
	h.prototype.callback = h.callback
})(jQuery);
