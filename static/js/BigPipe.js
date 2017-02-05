"use strict";
var BigPipe = function (count) {
    var pagelets = [];
	var all_js = [];
	var all_css = [];
    
	var Loader = function () {
		var doc = document;
		return {
			loadJs : function (js, cb) {
				var url = js.src;
				var _this = this;
                var elementExists = document.getElementById(js.id);
				if (url.match(/js/) && "" != url && !elementExists) {
					var script = doc.createElement("script"),
					loaded = !1,
					trs = this.readyState;
                    script.id = js.id;
					script.type = "text/javascript";
					script.async = true;
				
                    if(script.depends){
                        var dep = script.depends;
                        for (index = 0; index < dep.length; ++index) {
                            _this.loadJs(dep[index]);
                        }
                    }
					/opera/i.test(navigator.userAgent) && trs && "complete" != trs || (script.onload = function () {
						loaded || (loaded = !0, cb && cb());
					}, script.onreadystatechange = function () {
						loaded || trs && "loaded" !== trs && "complete" !== trs || (script.onerror = script.onload = script.onreadystatechange = null, loaded = !0, a && script.parentNode && a.removeChild(script))
					}, (doc.getElementsByTagName("head")[0] || doc.getElementsByTagName("body")[0]).appendChild(script), script.src = url)
				}
			},
			loadCss : function (css, cb, scope) {
                var path = css.src;
                var elementExists = document.getElementById(css.id);
                if(elementExists){
                    cb.call(scope || _win, !1);
                }
				if (path.match(/css/) && "" != path && !elementExists) {
                    var _link = doc.createElement("link"),
                        sheet,
                        cssRules,
                        _win = window;
                        _link.id = css.id;
                        _link.href = path;
                        _link.rel = "stylesheet";
                        _link.type = "text/css";

                        "sheet" in _link ? (sheet = "sheet", cssRules = "cssRules") : (sheet = "styleSheet", cssRules = "rules");
                        var g = setInterval(function () {
                                try {
                                    _link[sheet] && _link[sheet][cssRules].length && (clearInterval(g), clearTimeout(k), cb.call(scope || _win, !0, _link))
                                } catch (a) {}

                                finally {}

                            }, 10),
                        k = setTimeout(function () {
                                clearInterval(g);
                                clearTimeout(k);
                                a.removeChild(_link);
                                cb.call(scope || _win, !1, _link);
                            }, 1500);
                        var id = setTimeout(function () {
                                clearTimeout(id);
                                id = null;
                                (doc.getElementsByTagName("head")[0] || doc.getElementsByTagName("body")[0]).appendChild(_link);
                            }, 1);
				}
			}
		}
	}
	();
	function PageLet(data, domInserted) {
		var remainingCss = 0;
		var insertDom = function () {
            if(!data.enable){
                document.getElementById(data.id).innerHTML = data.content;
                domInserted && domInserted();
            }
		}
		var loadCss = function () {
			if (data.css && data.css.length) {
				remainingCss = data.css.length;
				for (var i = remainingCss; i--; ) {
                    var css = data.css[i];
					Loader.loadCss(all_css[css], function () {
						!--remainingCss && insertDom();
					},this);
				}
			} else {
				insertDom();
			}
		}
		var loadJs = function () {
			if (!data.js)
				return;
			for (var i = 0; i < data.js.length; i++) {
                var pjs = data.js[i];
				Loader.loadJs(all_js[pjs]);
			}
		}
		return {
			loadCss : loadCss,
			loadJs : loadJs
		};
	}


	return {
		jsResource : function (js) {
			all_js = js;
		},
		cssResource : function (css) {
            all_css = css;
        },
		onPageletArrive : function (p) {
			var domInserted = p.dom_inserted || function () {};
			var pagelet = new PageLet(p, domInserted);
			pagelets.push(pagelet);
			pagelet.loadCss();
		},
		done : function () {
            for (var i = 0; i < pagelets.length; i++) {
                pagelets[i].loadJs();
            }
		}
	};
};