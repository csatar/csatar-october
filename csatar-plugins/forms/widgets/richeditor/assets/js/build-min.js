(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = function (root, jQuery) {
            if (jQuery === undefined) {
                if (typeof window !== 'undefined') {
                    jQuery = require('jquery');
                } else {
                    jQuery = require('jquery')(root);
                }
            }
            return factory(jQuery);
        };
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    var FE = function (element, options) {
        this.id = ++$.FE.ID;
        var presets = {}
        if (options && options.documentReady) {
            presets.toolbarButtons = ['fullscreen', 'undo', 'redo', 'getPDF', 'print', '|', 'bold', 'italic', 'underline', 'color', 'clearFormatting', '|', 'alignLeft', 'alignCenter', 'alignRight', 'alignJustify', '|', 'formatOL', 'formatUL', 'indent', 'outdent', '-', 'paragraphFormat', '|', 'fontFamily', '|', 'fontSize', '|', 'insertLink', 'insertImage', 'quote']
            presets.paragraphFormatSelection = true
            presets.fontFamilySelection = true
            presets.fontSizeSelection = true
            presets.placeholderText = ''
            presets.quickInsertEnabled = false
            presets.charCounterCount = false
        }
        this.opts = $.extend(true, {}, $.extend({}, FE.DEFAULTS, presets, typeof options == 'object' && options));
        var opts_string = JSON.stringify(this.opts);
        $.FE.OPTS_MAPPING[opts_string] = $.FE.OPTS_MAPPING[opts_string] || this.id;
        this.sid = $.FE.OPTS_MAPPING[opts_string];
        $.FE.SHARED[this.sid] = $.FE.SHARED[this.sid] || {};
        this.shared = $.FE.SHARED[this.sid];
        this.shared.count = (this.shared.count || 0) + 1;
        this.$oel = $(element);
        this.$oel.data('froala.editor', this);
        this.o_doc = element.ownerDocument;
        this.o_win = 'defaultView' in this.o_doc ? this.o_doc.defaultView : this.o_doc.parentWindow;
        var c_scroll = $(this.o_win).scrollTop();
        this.$oel.on('froala.doInit', $.proxy(function () {
            this.$oel.off('froala.doInit');
            this.doc = this.$el.get(0).ownerDocument;
            this.win = 'defaultView' in this.doc ? this.doc.defaultView : this.doc.parentWindow;
            this.$doc = $(this.doc);
            this.$win = $(this.win);
            if (!this.opts.pluginsEnabled) this.opts.pluginsEnabled = Object.keys($.FE.PLUGINS);
            if (this.opts.initOnClick) {
                this.load($.FE.MODULES);
                this.$el.on('touchstart.init', function () {
                    $(this).data('touched', true);
                });
                this.$el.on('touchmove.init', function () {
                    $(this).removeData('touched');
                })
                this.$el.on('mousedown.init touchend.init dragenter.init focus.init', $.proxy(function (e) {
                    if (e.type == 'touchend' && !this.$el.data('touched')) {
                        return true;
                    }
                    if (e.which === 1 || !e.which) {
                        this.$el.off('mousedown.init touchstart.init touchmove.init touchend.init dragenter.init focus.init');
                        this.load($.FE.MODULES);
                        this.load($.FE.PLUGINS);
                        var target = e.originalEvent && e.originalEvent.originalTarget;
                        if (target && target.tagName == 'IMG') $(target).trigger('mousedown');
                        if (typeof this.ul == 'undefined') this.destroy();
                        if (e.type == 'touchend' && this.image && e.originalEvent && e.originalEvent.target && $(e.originalEvent.target).is('img')) {
                            setTimeout($.proxy(function () {
                                this.image.edit($(e.originalEvent.target));
                            }, this), 100);
                        }
                        this.ready = true;
                        this.events.trigger('initialized');
                    }
                }, this));
                this.events.trigger('initializationDelayed');
            } else {
                this.load($.FE.MODULES);
                this.load($.FE.PLUGINS);
                $(this.o_win).scrollTop(c_scroll);
                if (typeof this.ul == 'undefined') this.destroy();
                this.ready = true;
                this.events.trigger('initialized');
            }
        }, this));
        this._init();
    };
    FE.DEFAULTS = {initOnClick: false, pluginsEnabled: null};
    FE.MODULES = {};
    FE.PLUGINS = {};
    FE.VERSION = '2.9.3';
    FE.INSTANCES = [];
    FE.OPTS_MAPPING = {};
    FE.SHARED = {};
    FE.ID = 0;
    FE.prototype._init = function () {
        var tag_name = this.$oel.prop('tagName');
        if (this.$oel.closest('label').length >= 1) {
            console.warn('Note! It is not recommended to initialize the Froala Editor within a label tag.');
        }
        var initOnDefault = $.proxy(function () {
            if (tag_name != 'TEXTAREA') {
                this._original_html = (this._original_html || this.$oel.html());
            }
            this.$box = this.$box || this.$oel;
            if (this.opts.fullPage) this.opts.iframe = true;
            if (!this.opts.iframe) {
                this.$el = $('<div></div>');
                this.el = this.$el.get(0);
                this.$wp = $('<div></div>').append(this.$el);
                this.$box.html(this.$wp);
                this.$oel.trigger('froala.doInit');
            } else {
                this.$iframe = $('<iframe src="about:blank" frameBorder="0">');
                this.$wp = $('<div></div>');
                this.$box.html(this.$wp);
                this.$wp.append(this.$iframe);
                this.$iframe.get(0).contentWindow.document.open();
                this.$iframe.get(0).contentWindow.document.write('<!DOCTYPE html>');
                this.$iframe.get(0).contentWindow.document.write('<html><head></head><body></body></html>');
                this.$iframe.get(0).contentWindow.document.close();
                this.$el = this.$iframe.contents().find('body');
                this.el = this.$el.get(0);
                this.$head = this.$iframe.contents().find('head');
                this.$html = this.$iframe.contents().find('html');
                this.iframe_document = this.$iframe.get(0).contentWindow.document;
                this.$oel.trigger('froala.doInit');
            }
        }, this);
        var initOnTextarea = $.proxy(function () {
            this.$box = $('<div>');
            this.$oel.before(this.$box).hide();
            this._original_html = this.$oel.val();
            this.$oel.parents('form').on('submit.' + this.id, $.proxy(function () {
                this.events.trigger('form.submit');
            }, this));
            this.$oel.parents('form').on('reset.' + this.id, $.proxy(function () {
                this.events.trigger('form.reset');
            }, this));
            initOnDefault();
        }, this);
        var initOnA = $.proxy(function () {
            this.$el = this.$oel;
            this.el = this.$el.get(0);
            this.$el.attr('contenteditable', true).css('outline', 'none').css('display', 'inline-block');
            this.opts.multiLine = false;
            this.opts.toolbarInline = false;
            this.$oel.trigger('froala.doInit');
        }, this)
        var initOnImg = $.proxy(function () {
            this.$el = this.$oel;
            this.el = this.$el.get(0);
            this.opts.toolbarInline = false;
            this.$oel.trigger('froala.doInit');
        }, this)
        var editInPopup = $.proxy(function () {
            this.$el = this.$oel;
            this.el = this.$el.get(0);
            this.opts.toolbarInline = false;
            this.$oel.on('click.popup', function (e) {
                e.preventDefault();
            })
            this.$oel.trigger('froala.doInit');
        }, this)
        if (this.opts.editInPopup) editInPopup(); else if (tag_name == 'TEXTAREA') initOnTextarea(); else if (tag_name == 'A') initOnA(); else if (tag_name == 'IMG') initOnImg(); else if (tag_name == 'BUTTON' || tag_name == 'INPUT') {
            this.opts.editInPopup = true;
            this.opts.toolbarInline = false;
            editInPopup();
        } else {
            initOnDefault();
        }
    }
    FE.prototype.load = function (module_list) {
        for (var m_name in module_list) {
            if (module_list.hasOwnProperty(m_name)) {
                if (this[m_name]) continue;
                if ($.FE.PLUGINS[m_name] && this.opts.pluginsEnabled.indexOf(m_name) < 0) continue;
                this[m_name] = new module_list[m_name](this);
                if (this[m_name]._init) {
                    this[m_name]._init();
                    if (this.opts.initOnClick && m_name == 'core') {
                        return false;
                    }
                }
            }
        }
    }
    FE.prototype.destroy = function () {
        this.destroying = true;
        this.shared.count--;
        this.events.$off();
        var html = this.html.get();
        if (this.opts.iframe) {
            this.events.disableBlur()
            this.win.focus();
            this.events.enableBlur()
        }
        this.events.trigger('destroy', [], true);
        this.events.trigger('shared.destroy', undefined, true);
        if (this.shared.count === 0) {
            for (var k in this.shared) {
                if (this.shared.hasOwnProperty(k)) {
                    this.shared[k] == null;
                    $.FE.SHARED[this.sid][k] = null;
                }
            }
            delete $.FE.SHARED[this.sid];
        }
        this.$oel.parents('form').off('.' + this.id);
        this.$oel.off('click.popup');
        this.$oel.removeData('froala.editor');
        this.$oel.off('froalaEditor');
        this.core.destroy(html);
        $.FE.INSTANCES.splice($.FE.INSTANCES.indexOf(this), 1);
    }
    $.fn.froalaEditor = function (option) {
        var arg_list = [];
        for (var i = 0; i < arguments.length; i++) {
            arg_list.push(arguments[i]);
        }
        if (typeof option == 'string') {
            var returns = [];
            this.each(function () {
                var $this = $(this);
                var editor = $this.data('froala.editor');
                if (!editor) {
                    return console.warn('Editor should be initialized before calling the ' + option + ' method.');
                }
                var context;
                var nm;
                if (option.indexOf('.') > 0 && editor[option.split('.')[0]]) {
                    if (editor[option.split('.')[0]]) {
                        context = editor[option.split('.')[0]];
                    }
                    nm = option.split('.')[1];
                } else {
                    context = editor;
                    nm = option.split('.')[0]
                }
                if (context[nm]) {
                    var returned_value = context[nm].apply(editor, arg_list.slice(1));
                    if (returned_value === undefined) {
                        returns.push(this);
                    } else if (returns.length === 0) {
                        returns.push(returned_value);
                    }
                } else {
                    return $.error('Method ' + option + ' does not exist in Froala Editor.');
                }
            });
            return (returns.length == 1) ? returns[0] : returns;
        } else if (typeof option === 'object' || !option) {
            return this.each(function () {
                var editor = $(this).data('froala.editor');
                if (!editor) {
                    var that = this;
                    new FE(that, option);
                }
            });
        }
    }
    $.fn.froalaEditor.Constructor = FE;
    $.FroalaEditor = FE;
    $.FE = FE;
    $.FE.XS = 0;
    $.FE.SM = 1;
    $.FE.MD = 2;
    $.FE.LG = 3;
    var x = 'a-z\\u0080-\\u009f\\u00a1-\\uffff0-9-_\.';
    $.FE.LinkRegExCommon = '[' + x + ']{1,}';
    $.FE.LinkRegExEnd = '((:[0-9]{1,5})|)(((\\/|\\?|#)[a-z\\u00a1-\\uffff0-9@?\\|!^=%&amp;\/~+#-\\\'*-_{}]*)|())';
    $.FE.LinkRegExTLD = '((' + $.FE.LinkRegExCommon + ')(\\.(com|net|org|edu|mil|gov|co|biz|info|me|dev)))';
    $.FE.LinkRegExHTTP = '((ftp|http|https):\\/\\/' + $.FE.LinkRegExCommon + ')';
    $.FE.LinkRegExAuth = '((ftp|http|https):\\/\\/[\\u0021-\\uffff]{1,}@' + $.FE.LinkRegExCommon + ')';
    $.FE.LinkRegExWWW = '(www\\.' + $.FE.LinkRegExCommon + '\\.[a-z0-9-]{2,24})';
    $.FE.LinkRegEx = '(' + $.FE.LinkRegExTLD + '|' + $.FE.LinkRegExHTTP + '|' + $.FE.LinkRegExWWW + '|' + $.FE.LinkRegExAuth + ')' + $.FE.LinkRegExEnd;
    $.FE.LinkProtocols = ['mailto', 'tel', 'sms', 'notes', 'data'];
    $.FE.MAIL_REGEX = /.+@.+\..+/i;
    $.FE.MODULES.helpers = function (editor) {
        function _ieVersion() {
            var rv = -1;
            var ua;
            var re;
            if (navigator.appName == 'Microsoft Internet Explorer') {
                ua = navigator.userAgent;
                re = new RegExp('MSIE ([0-9]{1,}[\\.0-9]{0,})');
                if (re.exec(ua) !== null)
                    rv = parseFloat(RegExp.$1);
            } else if (navigator.appName == 'Netscape') {
                ua = navigator.userAgent;
                re = new RegExp('Trident/.*rv:([0-9]{1,}[\\.0-9]{0,})');
                if (re.exec(ua) !== null)
                    rv = parseFloat(RegExp.$1);
            }
            return rv;
        }

        function _browser() {
            var browser = {};
            var ie_version = _ieVersion();
            if (ie_version > 0) {
                browser.msie = true;
            } else {
                var ua = navigator.userAgent.toLowerCase();
                var match = /(edge)[ \/]([\w.]+)/.exec(ua) || /(chrome)[ \/]([\w.]+)/.exec(ua) || /(webkit)[ \/]([\w.]+)/.exec(ua) || /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) || /(msie) ([\w.]+)/.exec(ua) || ua.indexOf('compatible') < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) || [];
                var matched = {browser: match[1] || '', version: match[2] || '0'};
                if (match[1]) browser[matched.browser] = true;
                if (browser.chrome) {
                    browser.webkit = true;
                } else if (browser.webkit) {
                    browser.safari = true;
                }
            }
            if (browser.msie) browser.version = ie_version;
            return browser;
        }

        function isIOS() {
            return /(iPad|iPhone|iPod)/g.test(navigator.userAgent) && !isWindowsPhone();
        }

        function isAndroid() {
            return /(Android)/g.test(navigator.userAgent) && !isWindowsPhone();
        }

        function isBlackberry() {
            return /(Blackberry)/g.test(navigator.userAgent);
        }

        function isWindowsPhone() {
            return /(Windows Phone)/gi.test(navigator.userAgent);
        }

        function isMobile() {
            return isAndroid() || isIOS() || isBlackberry();
        }

        function requestAnimationFrame() {
            return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || function (callback) {
                window.setTimeout(callback, 1000 / 60);
            };
        }

        function getPX(val) {
            return parseInt(val, 10) || 0;
        }

        function screenSize() {
            var $test = $('<div class="fr-visibility-helper"></div>').appendTo('body:first');
            try {
                var size = getPX($test.css('margin-left'));
                $test.remove();
                return size;
            } catch (ex) {
                return $.FE.LG;
            }
        }

        function isTouch() {
            return ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch;
        }

        function isURL(url) {
            if (!/^(https?:|ftps?:|)\/\//i.test(url)) return false;
            url = String(url).replace(/</g, '%3C').replace(/>/g, '%3E').replace(/"/g, '%22').replace(/ /g, '%20');
            var test_reg = new RegExp('^' + $.FE.LinkRegExHTTP + $.FE.LinkRegExEnd + '$', 'gi');
            return test_reg.test(url);
        }

        function isEmail(url) {
            if (/^(https?:|ftps?:|)\/\//i.test(url)) {
                return false;
            }
            return $.FE.MAIL_REGEX.test(url);
        }

        function sanitizeURL(url) {
            var local_path = /^([A-Za-z]:(\\){1,2}|[A-Za-z]:((\\){1,2}[^\\]+)+)(\\)?$/i;
            if (/^(https?:|ftps?:|)\/\//i.test(url)) {
                return url;
            } else if (local_path.test(url)) {
                return url;
            } else if (new RegExp('^(' + $.FE.LinkProtocols.join('|') + '):\\/\\/', 'i').test(url)) {
                return url;
            } else {
                url = encodeURIComponent(url).replace(/%23/g, '#').replace(/%2F/g, '/').replace(/%25/g, '%').replace(/mailto%3A/gi, 'mailto:').replace(/file%3A/gi, 'file:').replace(/sms%3A/gi, 'sms:').replace(/tel%3A/gi, 'tel:').replace(/notes%3A/gi, 'notes:').replace(/data%3Aimage/gi, 'data:image').replace(/blob%3A/gi, 'blob:').replace(/%3A(\d)/gi, ':$1').replace(/webkit-fake-url%3A/gi, 'webkit-fake-url:').replace(/%3F/g, '?').replace(/%3D/g, '=').replace(/%26/g, '&').replace(/&amp;/g, '&').replace(/%2C/g, ',').replace(/%3B/g, ';').replace(/%2B/g, '+').replace(/%40/g, '@').replace(/%5B/g, '[').replace(/%5D/g, ']').replace(/%7B/g, '{').replace(/%7D/g, '}');
            }
            return url;
        }

        function isArray(obj) {
            return obj && !(obj.propertyIsEnumerable('length')) && typeof obj === 'object' && typeof obj.length === 'number';
        }

        function RGBToHex(rgb) {
            function hex(x) {
                return ('0' + parseInt(x, 10).toString(16)).slice(-2);
            }

            try {
                if (!rgb || rgb === 'transparent') return '';
                if (/^#[0-9A-F]{6}$/i.test(rgb)) return rgb;
                rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
                return ('#' + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3])).toUpperCase();
            } catch (ex) {
                return null;
            }
        }

        function HEXtoRGB(hex) {
            var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
            hex = hex.replace(shorthandRegex, function (m, r, g, b) {
                return r + r + g + g + b + b;
            });
            var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? 'rgb(' + parseInt(result[1], 16) + ', ' + parseInt(result[2], 16) + ', ' + parseInt(result[3], 16) + ')' : '';
        }

        var default_alignment;

        function getAlignment($block) {
            var alignment = ($block.css('text-align') || '').replace(/-(.*)-/g, '');
            if (['left', 'right', 'justify', 'center'].indexOf(alignment) < 0) {
                if (!default_alignment) {
                    var $div = $('<div dir="' + (editor.opts.direction == 'rtl' ? 'rtl' : 'auto') + '" style="text-align: ' + editor.$el.css('text-align') + '; position: fixed; left: -3000px;"><span id="s1">.</span><span id="s2">.</span></div>');
                    $('body:first').append($div);
                    var l1 = $div.find('#s1').get(0).getBoundingClientRect().left;
                    var l2 = $div.find('#s2').get(0).getBoundingClientRect().left;
                    $div.remove();
                    default_alignment = l1 < l2 ? 'left' : 'right';
                }
                alignment = default_alignment;
            }
            return alignment;
        }

        var is_mac = null;

        function isMac() {
            if (is_mac == null) {
                is_mac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
            }
            return is_mac;
        }

        function _scopeShim() {
            function overrideNodeMethod(prototype, methodName) {
                var oldMethod = prototype[methodName];
                prototype[methodName] = function (query) {
                    var nodeList;
                    var gaveId = false;
                    var gaveContainer = false;
                    if (query && query.match(scopeRE)) {
                        query = query.replace(scopeRE, '');
                        if (!this.parentNode) {
                            container.appendChild(this);
                            gaveContainer = true;
                        }
                        var parentNode = this.parentNode;
                        if (!this.id) {
                            this.id = 'rootedQuerySelector_id_' + (new Date()).getTime();
                            gaveId = true;
                        }
                        nodeList = oldMethod.call(parentNode, '#' + this.id + ' ' + query);
                        if (gaveId) {
                            this.id = '';
                        }
                        if (gaveContainer) {
                            container.removeChild(this);
                        }
                        return nodeList;
                    } else {
                        return oldMethod.call(this, query);
                    }
                };
            }

            var container = editor.o_doc.createElement('div');
            try {
                container.querySelectorAll(':scope *');
            } catch (e) {
                var scopeRE = /^\s*:scope/gi;
                overrideNodeMethod(Element.prototype, 'querySelector');
                overrideNodeMethod(Element.prototype, 'querySelectorAll');
                overrideNodeMethod(HTMLElement.prototype, 'querySelector');
                overrideNodeMethod(HTMLElement.prototype, 'querySelectorAll');
            }
        }

        function scrollTop() {
            if (editor.o_win.pageYOffset) return editor.o_win.pageYOffset;
            if (editor.o_doc.documentElement && editor.o_doc.documentElement.scrollTop)
                return editor.o_doc.documentElement.scrollTop;
            if (editor.o_doc.body.scrollTop) return editor.o_doc.body.scrollTop;
            return 0;
        }

        function scrollLeft() {
            if (editor.o_win.pageXOffset) return editor.o_win.pageXOffset;
            if (editor.o_doc.documentElement && editor.o_doc.documentElement.scrollLeft)
                return editor.o_doc.documentElement.scrollLeft;
            if (editor.o_doc.body.scrollLeft) return editor.o_doc.body.scrollLeft;
            return 0;
        }

        function _closestShim() {
            if (!Element.prototype.matches) {
                Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
            }
            if (!Element.prototype.closest) {
                Element.prototype.closest = function (s) {
                    var el = this;
                    var ancestor = this;
                    if (!ancestor) return null;
                    if (!document.documentElement.contains(el)) return null;
                    do {
                        if (ancestor.matches(s)) return ancestor;
                        ancestor = ancestor.parentElement;
                    } while (ancestor !== null);
                    return null;
                }
            }
        }

        function isInViewPort(el) {
            var rect = el.getBoundingClientRect();
            rect = {top: Math.round(rect.top), bottom: Math.round(rect.bottom)};
            return ((rect.top >= 0 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight)) || (rect.top <= 0 && rect.bottom >= (window.innerHeight || document.documentElement.clientHeight)));
        }

        function _init() {
            editor.browser = _browser();
            _scopeShim();
            _closestShim();
        }

        return {
            _init: _init,
            isIOS: isIOS,
            isMac: isMac,
            isAndroid: isAndroid,
            isBlackberry: isBlackberry,
            isWindowsPhone: isWindowsPhone,
            isMobile: isMobile,
            isEmail: isEmail,
            requestAnimationFrame: requestAnimationFrame,
            getPX: getPX,
            screenSize: screenSize,
            isTouch: isTouch,
            sanitizeURL: sanitizeURL,
            isArray: isArray,
            RGBToHex: RGBToHex,
            HEXtoRGB: HEXtoRGB,
            isURL: isURL,
            getAlignment: getAlignment,
            scrollTop: scrollTop,
            scrollLeft: scrollLeft,
            isInViewPort: isInViewPort
        }
    }
    $.FE.MODULES.events = function (editor) {
        var _events = {};
        var _do_blur;

        function _assignEvent($el, evs, handler) {
            $on($el, evs, handler);
        }

        function _forPaste() {
            _assignEvent(editor.$el, 'cut copy paste beforepaste', function (e) {
                trigger(e.type, [e]);
            });
        }

        function _forElement() {
            _assignEvent(editor.$el, 'click mouseup mousedown touchstart touchend dragenter dragover dragleave dragend drop dragstart', function (e) {
                trigger(e.type, [e]);
            });
            on('mousedown', function () {
                for (var i = 0; i < $.FE.INSTANCES.length; i++) {
                    if ($.FE.INSTANCES[i] != editor && $.FE.INSTANCES[i].popups && $.FE.INSTANCES[i].popups.areVisible()) {
                        $.FE.INSTANCES[i].$el.find('.fr-marker').remove();
                    }
                }
            })
        }

        function _forKeys() {
            _assignEvent(editor.$el, 'keydown keypress keyup input', function (e) {
                trigger(e.type, [e]);
            });
        }

        function _forWindow() {
            _assignEvent(editor.$win, editor._mousedown, function (e) {
                trigger('window.mousedown', [e]);
                enableBlur();
            });
            _assignEvent(editor.$win, editor._mouseup, function (e) {
                trigger('window.mouseup', [e]);
            });
            _assignEvent(editor.$win, 'cut copy keydown keyup touchmove touchend', function (e) {
                trigger('window.' + e.type, [e]);
            });
        }

        function _forDocument() {
            _assignEvent(editor.$doc, 'dragend drop', function (e) {
                trigger('document.' + e.type, [e]);
            })
        }

        function focus(do_focus) {
            var info;
            if (typeof do_focus == 'undefined') do_focus = true;
            if (!editor.$wp) return false;
            if (editor.helpers.isIOS()) {
                editor.$win.get(0).focus();
                var offsetY = editor.$win.scrollTop() === 0 ? 1 : editor.$win.scrollTop();
                window.scrollTo(0, offsetY);
            }
            if (editor.core.hasFocus()) {
                return false;
            }
            if (!editor.core.hasFocus() && do_focus) {
                var st = editor.$win.scrollTop();
                if (editor.browser.msie && editor.$box) editor.$box.css('position', 'fixed');
                if (editor.browser.msie && editor.$wp) editor.$wp.css('overflow', 'visible');
                if (editor.browser.msie && editor.$sc) editor.$sc.css('position', 'fixed');
                disableBlur();
                editor.$el.focus();
                editor.events.trigger('focus');
                enableBlur();
                if (editor.browser.msie && editor.$sc) editor.$sc.css('position', '');
                if (editor.browser.msie && editor.$box) editor.$box.css('position', '');
                if (editor.browser.msie && editor.$wp) editor.$wp.css('overflow', 'auto');
                if (st != editor.$win.scrollTop()) {
                    editor.$win.scrollTop(st);
                }
                info = editor.selection.info(editor.el);
                if (!info.atStart) {
                    return false;
                }
            }
            if (!editor.core.hasFocus() || editor.$el.find('.fr-marker').length > 0) {
                return false;
            }
            info = editor.selection.info(editor.el);
            if (info.atStart && editor.selection.isCollapsed()) {
                if (editor.html.defaultTag() != null) {
                    var marker = editor.markers.insert();
                    if (marker && !editor.node.blockParent(marker)) {
                        $(marker).remove();
                        var element = editor.$el.find(editor.html.blockTagsQuery()).get(0);
                        if (element) {
                            $(element).prepend($.FE.MARKERS);
                            editor.selection.restore();
                        }
                    } else if (marker) {
                        $(marker).remove();
                    }
                }
            }
        }

        var focused = false;

        function _forFocus() {
            _assignEvent(editor.$el, 'focus', function (e) {
                if (blurActive()) {
                    focus(false);
                    if (focused === false) {
                        trigger(e.type, [e]);
                    }
                }
            });
            _assignEvent(editor.$el, 'blur', function (e) {
                if (blurActive()) {
                    if (focused === true) {
                        trigger(e.type, [e]);
                        enableBlur();
                    }
                }
            });
            $on(editor.$el, 'mousedown', '[contenteditable="true"]', function () {
                disableBlur();
                editor.$el.blur();
            })
            on('focus', function () {
                focused = true;
            });
            on('blur', function () {
                focused = false;
            });
        }

        function _forMouse() {
            if (editor.helpers.isMobile()) {
                editor._mousedown = 'touchstart';
                editor._mouseup = 'touchend';
                editor._move = 'touchmove';
                editor._mousemove = 'touchmove';
            } else {
                editor._mousedown = 'mousedown';
                editor._mouseup = 'mouseup';
                editor._move = '';
                editor._mousemove = 'mousemove';
            }
        }

        function _buttonMouseDown(e) {
            var $btn = $(e.currentTarget);
            if (editor.edit.isDisabled() || editor.node.hasClass($btn.get(0), 'fr-disabled')) {
                e.preventDefault();
                return false;
            }
            if (e.type === 'mousedown' && e.which !== 1) return true;
            if (!editor.helpers.isMobile()) {
                e.preventDefault();
            }
            if ((editor.helpers.isAndroid() || editor.helpers.isWindowsPhone()) && $btn.parents('.fr-dropdown-menu').length === 0) {
                e.preventDefault();
                e.stopPropagation();
            }
            $btn.addClass('fr-selected');
            editor.events.trigger('commands.mousedown', [$btn]);
        }

        function _buttonMouseUp(e, handler) {
            var $btn = $(e.currentTarget);
            if (editor.edit.isDisabled() || editor.node.hasClass($btn.get(0), 'fr-disabled')) {
                e.preventDefault();
                return false;
            }
            if (e.type === 'mouseup' && e.which !== 1) return true;
            if (!editor.node.hasClass($btn.get(0), 'fr-selected')) return true;
            if (e.type != 'touchmove') {
                e.stopPropagation();
                e.stopImmediatePropagation();
                e.preventDefault();
                if (!editor.node.hasClass($btn.get(0), 'fr-selected')) {
                    editor.button.getButtons('.fr-selected', true).removeClass('fr-selected');
                    return false;
                }
                editor.button.getButtons('.fr-selected', true).removeClass('fr-selected');
                if ($btn.data('dragging') || $btn.attr('disabled')) {
                    $btn.removeData('dragging');
                    return false;
                }
                var timeout = $btn.data('timeout');
                if (timeout) {
                    clearTimeout(timeout);
                    $btn.removeData('timeout');
                }
                handler.apply(editor, [e]);
            } else {
                if (!$btn.data('timeout')) {
                    $btn.data('timeout', setTimeout(function () {
                        $btn.data('dragging', true);
                    }, 100));
                }
            }
        }

        function enableBlur() {
            _do_blur = true;
        }

        function disableBlur() {
            _do_blur = false;
        }

        function blurActive() {
            return _do_blur;
        }

        function bindClick($element, selector, handler) {
            $on($element, editor._mousedown, selector, function (e) {
                if (!editor.edit.isDisabled()) _buttonMouseDown(e);
            }, true);
            $on($element, editor._mouseup + ' ' + editor._move, selector, function (e) {
                if (!editor.edit.isDisabled()) _buttonMouseUp(e, handler);
            }, true);
            $on($element, 'mousedown click mouseup', selector, function (e) {
                if (!editor.edit.isDisabled()) e.stopPropagation();
            }, true);
            on('window.mouseup', function () {
                if (!editor.edit.isDisabled()) {
                    $element.find(selector).removeClass('fr-selected');
                    enableBlur();
                }
            });
            $on($element, 'mouseenter', selector, function () {
                if ($(this).hasClass('fr-options')) {
                    $(this).prev('.fr-btn').addClass('fr-btn-hover')
                }
                if ($(this).next('.fr-btn').hasClass('fr-options')) {
                    $(this).next('.fr-btn').addClass('fr-btn-hover')
                }
            })
            $on($element, 'mouseleave', selector, function () {
                if ($(this).hasClass('fr-options')) {
                    $(this).prev('.fr-btn').removeClass('fr-btn-hover')
                }
                if ($(this).next('.fr-btn').hasClass('fr-options')) {
                    $(this).next('.fr-btn').removeClass('fr-btn-hover')
                }
            })
        }

        function on(name, callback, first) {
            var names = name.split(' ');
            if (names.length > 1) {
                for (var i = 0; i < names.length; i++) {
                    on(names[i], callback, first);
                }
                return true;
            }
            if (typeof first == 'undefined') first = false;
            var callbacks;
            if (name.indexOf('shared.') !== 0) {
                callbacks = (_events[name] = _events[name] || []);
            } else {
                callbacks = (editor.shared._events[name] = editor.shared._events[name] || []);
            }
            if (first) {
                callbacks.unshift(callback);
            } else {
                callbacks.push(callback);
            }
        }

        var $_events = [];

        function _callback(callback) {
            return function () {
                if (!editor.destroying) {
                    callback.apply(this, arguments);
                }
            }
        }

        function $on($el, evs, selector, callback, shared) {
            if (typeof selector == 'function') {
                shared = callback;
                callback = selector;
                selector = false;
            }
            var ary = (!shared ? $_events : editor.shared.$_events);
            var id = (!shared ? editor.id : editor.sid);
            callback = _callback(callback);
            if (!selector) {
                $el.on(evs.split(' ').join('.ed' + id + ' ') + '.ed' + id, callback);
            } else {
                $el.on(evs.split(' ').join('.ed' + id + ' ') + '.ed' + id, selector, callback);
            }
            ary.push([$el, evs.split(' ').join('.ed' + id + ' ') + '.ed' + id]);
        }

        function _$off(evs) {
            for (var i = 0; i < evs.length; i++) {
                evs[i][0].off(evs[i][1]);
            }
        }

        function $off() {
            _$off($_events);
            $_events = [];
            if (editor.shared.count === 0) {
                _$off(editor.shared.$_events);
                editor.shared.$_events = [];
            }
        }

        function trigger(name, args, force) {
            if (!editor.edit.isDisabled() || force) {
                var callbacks;
                if (name.indexOf('shared.') !== 0) {
                    callbacks = _events[name];
                } else {
                    if (editor.shared.count > 0) return false;
                    callbacks = editor.shared._events[name];
                }
                var val;
                if (callbacks) {
                    for (var i = 0; i < callbacks.length; i++) {
                        val = callbacks[i].apply(editor, args);
                        if (val === false) return false;
                    }
                }
                val = editor.$oel.triggerHandler('froalaEditor.' + name, $.merge([editor], (args || [])));
                if (val === false) return false;
                return val;
            }
        }

        function chainTrigger(name, param, force) {
            if (!editor.edit.isDisabled() || force) {
                var callbacks;
                if (name.indexOf('shared.') !== 0) {
                    callbacks = _events[name];
                } else {
                    if (editor.shared.count > 0) return false;
                    callbacks = editor.shared._events[name];
                }
                var resp;
                if (callbacks) {
                    for (var i = 0; i < callbacks.length; i++) {
                        resp = callbacks[i].apply(editor, [param]);
                        if (typeof resp !== 'undefined') param = resp;
                    }
                }
                resp = editor.$oel.triggerHandler('froalaEditor.' + name, $.merge([editor], [param]));
                if (typeof resp !== 'undefined') param = resp;
                return param;
            }
        }

        function _destroy() {
            for (var k in _events) {
                if (_events.hasOwnProperty(k)) {
                    delete _events[k];
                }
            }
        }

        function _sharedDestroy() {
            for (var k in editor.shared._events) {
                if (editor.shared._events.hasOwnProperty(k)) {
                    delete editor.shared._events[k];
                }
            }
        }

        function _init() {
            editor.shared.$_events = editor.shared.$_events || [];
            editor.shared._events = {};
            _forMouse();
            _forElement();
            _forWindow();
            _forDocument();
            _forKeys();
            _forFocus();
            enableBlur();
            _forPaste();
            on('destroy', _destroy);
            on('shared.destroy', _sharedDestroy);
        }

        return {
            _init: _init,
            on: on,
            trigger: trigger,
            bindClick: bindClick,
            disableBlur: disableBlur,
            enableBlur: enableBlur,
            blurActive: blurActive,
            focus: focus,
            chainTrigger: chainTrigger,
            $on: $on,
            $off: $off
        }
    };
    $.FE.MODULES.node = function (editor) {
        function getContents(node) {
            if (!node || node.tagName == 'IFRAME') return [];
            return Array.prototype.slice.call(node.childNodes || []);
        }

        function isBlock(node) {
            if (!node) return false;
            if (node.nodeType != Node.ELEMENT_NODE) return false;
            return $.FE.BLOCK_TAGS.indexOf(node.tagName.toLowerCase()) >= 0;
        }

        function isLink(node) {
            if (!node) return false;
            if (node.nodeType != Node.ELEMENT_NODE) return false;
            return node.tagName.toLowerCase() == 'a';
        }

        function isEmpty(el, ignore_markers) {
            if (!el) return true;
            if (el.querySelector('table')) return false;
            var contents = getContents(el);
            if (contents.length == 1 && isBlock(contents[0])) {
                contents = getContents(contents[0]);
            }
            var has_br = false;
            for (var i = 0; i < contents.length; i++) {
                var node = contents[i];
                if (ignore_markers && editor.node.hasClass(node, 'fr-marker')) continue;
                if (node.nodeType == Node.TEXT_NODE && node.textContent.length === 0) continue;
                if (node.tagName != 'BR' && (node.textContent || '').replace(/\u200B/gi, '').replace(/\n/g, '').length > 0) return false;
                if (has_br) {
                    return false;
                } else if (node.tagName == 'BR') {
                    has_br = true;
                }
            }
            if (el.querySelectorAll($.FE.VOID_ELEMENTS.join(',')).length - el.querySelectorAll('br').length) return false;
            if (el.querySelector(editor.opts.htmlAllowedEmptyTags.join(':not(.fr-marker),') + ':not(.fr-marker)')) return false;
            if (el.querySelectorAll($.FE.BLOCK_TAGS.join(',')).length > 1) return false;
            if (el.querySelector(editor.opts.htmlDoNotWrapTags.join(':not(.fr-marker),') + ':not(.fr-marker)')) return false;
            return true;
        }

        function blockParent(node) {
            while (node && node.parentNode !== editor.el && !(node.parentNode && editor.node.hasClass(node.parentNode, 'fr-inner'))) {
                node = node.parentNode;
                if (isBlock(node)) {
                    return node;
                }
            }
            return null;
        }

        function deepestParent(node, until, simple_enter) {
            if (typeof until == 'undefined') until = [];
            if (typeof simple_enter == 'undefined') simple_enter = true;
            until.push(editor.el);
            if (until.indexOf(node.parentNode) >= 0 || (node.parentNode && editor.node.hasClass(node.parentNode, 'fr-inner')) || (node.parentNode && $.FE.SIMPLE_ENTER_TAGS.indexOf(node.parentNode.tagName) >= 0 && simple_enter)) {
                return null;
            }
            while (until.indexOf(node.parentNode) < 0 && node.parentNode && !editor.node.hasClass(node.parentNode, 'fr-inner') && ($.FE.SIMPLE_ENTER_TAGS.indexOf(node.parentNode.tagName) < 0 || !simple_enter) && (!(isBlock(node) && isBlock(node.parentNode)) || !simple_enter)) {
                node = node.parentNode;
            }
            return node;
        }

        function rawAttributes(node) {
            var attrs = {};
            var atts = node.attributes;
            if (atts) {
                for (var i = 0; i < atts.length; i++) {
                    var att = atts[i];
                    attrs[att.nodeName] = att.value;
                }
            }
            return attrs;
        }

        function attributes(node) {
            var str = '';
            var atts = rawAttributes(node);
            var keys = Object.keys(atts).sort();
            for (var i = 0; i < keys.length; i++) {
                var nodeName = keys[i];
                var value = atts[nodeName];
                if (value.indexOf('\'') < 0 && value.indexOf('"') >= 0) {
                    str += ' ' + nodeName + '=\'' + value + '\'';
                } else if (value.indexOf('"') >= 0 && value.indexOf('\'') >= 0) {
                    value = value.replace(/"/g, '&quot;');
                    str += ' ' + nodeName + '="' + value + '"';
                } else {
                    str += ' ' + nodeName + '="' + value + '"';
                }
            }
            return str;
        }

        function clearAttributes(node) {
            var atts = node.attributes;
            for (var i = atts.length - 1; i >= 0; i--) {
                var att = atts[i];
                node.removeAttribute(att.nodeName);
            }
        }

        function openTagString(node) {
            return '<' + node.tagName.toLowerCase() + attributes(node) + '>';
        }

        function closeTagString(node) {
            return '</' + node.tagName.toLowerCase() + '>';
        }

        function isFirstSibling(node, ignore_markers) {
            if (typeof ignore_markers == 'undefined') ignore_markers = true;
            var sibling = node.previousSibling;
            while (sibling && ignore_markers && editor.node.hasClass(sibling, 'fr-marker')) {
                sibling = sibling.previousSibling;
            }
            if (!sibling) return true;
            if (sibling.nodeType == Node.TEXT_NODE && sibling.textContent === '') return isFirstSibling(sibling);
            return false;
        }

        function isLastSibling(node, ignore_markers) {
            if (typeof ignore_markers == 'undefined') ignore_markers = true;
            var sibling = node.nextSibling;
            while (sibling && ignore_markers && editor.node.hasClass(sibling, 'fr-marker')) {
                sibling = sibling.nextSibling;
            }
            if (!sibling) return true;
            if (sibling.nodeType == Node.TEXT_NODE && sibling.textContent === '') return isLastSibling(sibling);
            return false;
        }

        function isVoid(node) {
            return node && node.nodeType == Node.ELEMENT_NODE && $.FE.VOID_ELEMENTS.indexOf((node.tagName || '').toLowerCase()) >= 0
        }

        function isList(node) {
            if (!node) return false;
            return ['UL', 'OL'].indexOf(node.tagName) >= 0;
        }

        function isElement(node) {
            return node === editor.el;
        }

        function isDeletable(node) {
            return node && node.nodeType == Node.ELEMENT_NODE && node.getAttribute('class') && (node.getAttribute('class') || '').indexOf('fr-deletable') >= 0;
        }

        function hasFocus(node) {
            return node === editor.doc.activeElement && (!editor.doc.hasFocus || editor.doc.hasFocus()) && !!(isElement(node) || node.type || node.href || ~node.tabIndex);
        }

        function isEditable(node) {
            return (!node.getAttribute || node.getAttribute('contenteditable') != 'false') && ['STYLE', 'SCRIPT'].indexOf(node.tagName) < 0;
        }

        function hasClass(el, cls) {
            if (el instanceof $) el = el.get(0);
            return (el && el.classList && el.classList.contains(cls));
        }

        function filter(callback) {
            if (editor.browser.msie) {
                return callback;
            } else {
                return {acceptNode: callback}
            }
        }

        return {
            isBlock: isBlock,
            isEmpty: isEmpty,
            blockParent: blockParent,
            deepestParent: deepestParent,
            rawAttributes: rawAttributes,
            attributes: attributes,
            clearAttributes: clearAttributes,
            openTagString: openTagString,
            closeTagString: closeTagString,
            isFirstSibling: isFirstSibling,
            isLastSibling: isLastSibling,
            isList: isList,
            isLink: isLink,
            isElement: isElement,
            contents: getContents,
            isVoid: isVoid,
            hasFocus: hasFocus,
            isEditable: isEditable,
            isDeletable: isDeletable,
            hasClass: hasClass,
            filter: filter
        }
    };
    $.FE.INVISIBLE_SPACE = '&#8203;';
    $.FE.START_MARKER = '<span class="fr-marker" data-id="0" data-type="true" style="display: none; line-height: 0;">' + $.FE.INVISIBLE_SPACE + '</span>';
    $.FE.END_MARKER = '<span class="fr-marker" data-id="0" data-type="false" style="display: none; line-height: 0;">' + $.FE.INVISIBLE_SPACE + '</span>';
    $.FE.MARKERS = $.FE.START_MARKER + $.FE.END_MARKER;
    $.FE.MODULES.markers = function (editor) {
        function _build(marker, id) {
            return $('<span class="fr-marker" data-id="' + id + '" data-type="' + marker + '" style="display: ' + (editor.browser.safari ? 'none' : 'inline-block') + '; line-height: 0;">' + $.FE.INVISIBLE_SPACE + '</span>', editor.doc)[0];
        }

        function place(range, marker, id) {
            var mk;
            var contents;
            var sibling;
            try {
                var boundary = range.cloneRange();
                boundary.collapse(marker);
                boundary.insertNode(_build(marker, id));
                if (marker === true) {
                    mk = editor.$el.find('span.fr-marker[data-type="true"][data-id="' + id + '"]').get(0);
                    sibling = mk.nextSibling;
                    while (sibling && sibling.nodeType === Node.TEXT_NODE && sibling.textContent.length === 0) {
                        $(sibling).remove();
                        sibling = mk.nextSibling;
                    }
                }
                if (marker === true && !range.collapsed) {
                    while (!editor.node.isElement(mk.parentNode) && !sibling) {
                        $(mk.parentNode).after(mk);
                        sibling = mk.nextSibling;
                    }
                    if (sibling && sibling.nodeType === Node.ELEMENT_NODE && editor.node.isBlock(sibling) && sibling.tagName !== 'HR') {
                        contents = [sibling];
                        do {
                            sibling = contents[0];
                            contents = editor.node.contents(sibling);
                        } while (contents[0] && editor.node.isBlock(contents[0]));
                        $(sibling).prepend($(mk));
                    }
                }
                if (marker === false && !range.collapsed) {
                    mk = editor.$el.find('span.fr-marker[data-type="false"][data-id="' + id + '"]').get(0);
                    sibling = mk.previousSibling;
                    if (sibling && sibling.nodeType === Node.ELEMENT_NODE && editor.node.isBlock(sibling) && sibling.tagName !== 'HR') {
                        contents = [sibling];
                        do {
                            sibling = contents[contents.length - 1];
                            contents = editor.node.contents(sibling);
                        } while (contents[contents.length - 1] && editor.node.isBlock(contents[contents.length - 1]));
                        $(sibling).append($(mk));
                    }
                    if (mk.parentNode && ['TD', 'TH'].indexOf(mk.parentNode.tagName) >= 0) {
                        if (mk.parentNode.previousSibling && !mk.previousSibling) {
                            $(mk.parentNode.previousSibling).append(mk);
                        }
                    }
                }
                var dom_marker = editor.$el.find('span.fr-marker[data-type="' + marker + '"][data-id="' + id + '"]').get(0);
                if (dom_marker) dom_marker.style.display = 'none';
                return dom_marker;
            } catch (ex) {
                return null;
            }
        }

        function insert() {
            if (!editor.$wp) return null;
            try {
                var range = editor.selection.ranges(0);
                var containter = range.commonAncestorContainer;
                if (editor.core.isEmpty()) {
                    editor.selection.setAtStart(editor.el);
                    editor.$el.find('.fr-marker:first').replaceWith('<span class="fr-single-marker" style="display: none; line-height: 0;">' + $.FE.INVISIBLE_SPACE + '</span>');
                    editor.$el.find('.fr-marker').remove();
                    return editor.$el.find('.fr-single-marker').removeClass('fr-single-marker').addClass('fr-marker').get(0);
                }
                if (containter != editor.el && editor.$el.find(containter).length === 0) return null;
                var boundary = range.cloneRange();
                var original_range = range.cloneRange();
                boundary.collapse(true);
                var mk = $('<span class="fr-marker" style="display: none; line-height: 0;">' + $.FE.INVISIBLE_SPACE + '</span>', editor.doc)[0];
                boundary.insertNode(mk);
                mk = editor.$el.find('span.fr-marker').get(0);
                if (mk) {
                    var sibling = mk.nextSibling;
                    while (sibling && sibling.nodeType === Node.TEXT_NODE && sibling.textContent.length === 0) {
                        $(sibling).remove();
                        sibling = editor.$el.find('span.fr-marker').get(0).nextSibling;
                    }
                    editor.selection.clear();
                    editor.selection.get().addRange(original_range);
                    return mk;
                } else {
                    return null;
                }
            } catch (ex) {
                console.warn('MARKER', ex)
            }
        }

        function split() {
            if (!editor.selection.isCollapsed()) {
                editor.selection.remove();
            }
            var marker = editor.$el.find('.fr-marker').get(0);
            if (marker == null) marker = insert();
            if (marker == null) return null;
            var deep_parent = editor.node.deepestParent(marker);
            if (!deep_parent) {
                deep_parent = editor.node.blockParent(marker);
                if (deep_parent && deep_parent.tagName != 'LI') {
                    deep_parent = null;
                }
            }
            if (deep_parent) {
                if (editor.node.isBlock(deep_parent) && editor.node.isEmpty(deep_parent)) {
                    if (deep_parent.tagName == 'LI' && (deep_parent.parentNode.firstElementChild == deep_parent && !editor.node.isEmpty(deep_parent.parentNode))) {
                        $(deep_parent).append('<span class="fr-marker"></span>');
                    } else {
                        $(deep_parent).replaceWith('<span class="fr-marker"></span>');
                    }
                } else if (editor.cursor.isAtStart(marker, deep_parent)) {
                    $(deep_parent).before('<span class="fr-marker"></span>');
                    $(marker).remove();
                } else if (editor.cursor.isAtEnd(marker, deep_parent)) {
                    $(deep_parent).after('<span class="fr-marker"></span>');
                    $(marker).remove();
                } else {
                    var node = marker;
                    var close_str = '';
                    var open_str = '';
                    do {
                        node = node.parentNode;
                        close_str = close_str + editor.node.closeTagString(node);
                        open_str = editor.node.openTagString(node) + open_str;
                    } while (node != deep_parent);
                    $(marker).replaceWith('<span id="fr-break"></span>');
                    var h = editor.node.openTagString(deep_parent) + $(deep_parent).html() + editor.node.closeTagString(deep_parent);
                    h = h.replace(/<span id="fr-break"><\/span>/g, close_str + '<span class="fr-marker"></span>' + open_str);
                    $(deep_parent).replaceWith(h);
                }
            }
            return editor.$el.find('.fr-marker').get(0)
        }

        function insertAtPoint(e) {
            var x = e.clientX;
            var y = e.clientY;
            remove();
            var start;
            var range = null;
            if (typeof editor.doc.caretPositionFromPoint != 'undefined') {
                start = editor.doc.caretPositionFromPoint(x, y);
                range = editor.doc.createRange();
                range.setStart(start.offsetNode, start.offset);
                range.setEnd(start.offsetNode, start.offset);
            } else if (typeof editor.doc.caretRangeFromPoint != 'undefined') {
                start = editor.doc.caretRangeFromPoint(x, y);
                range = editor.doc.createRange();
                range.setStart(start.startContainer, start.startOffset);
                range.setEnd(start.startContainer, start.startOffset);
            }
            if (range !== null && typeof editor.win.getSelection != 'undefined') {
                var sel = editor.win.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            } else if (typeof editor.doc.body.createTextRange != 'undefined') {
                try {
                    range = editor.doc.body.createTextRange();
                    range.moveToPoint(x, y);
                    var end_range = range.duplicate();
                    end_range.moveToPoint(x, y);
                    range.setEndPoint('EndToEnd', end_range);
                    range.select();
                } catch (ex) {
                    return false;
                }
            }
            insert();
        }

        function remove() {
            editor.$el.find('.fr-marker').remove();
        }

        return {place: place, insert: insert, split: split, insertAtPoint: insertAtPoint, remove: remove}
    };
    $.FE.MODULES.selection = function (editor) {
        function text() {
            var text = '';
            if (editor.win.getSelection) {
                text = editor.win.getSelection();
            } else if (editor.doc.getSelection) {
                text = editor.doc.getSelection();
            } else if (editor.doc.selection) {
                text = editor.doc.selection.createRange().text;
            }
            return text.toString();
        }

        function get() {
            var selection = '';
            if (editor.win.getSelection) {
                selection = editor.win.getSelection();
            } else if (editor.doc.getSelection) {
                selection = editor.doc.getSelection();
            } else {
                selection = editor.doc.selection.createRange();
            }
            return selection;
        }

        function ranges(index) {
            var sel = get();
            var ranges = [];
            if (sel && sel.getRangeAt && sel.rangeCount) {
                ranges = [];
                for (var i = 0; i < sel.rangeCount; i++) {
                    ranges.push(sel.getRangeAt(i));
                }
            } else {
                if (editor.doc.createRange) {
                    ranges = [editor.doc.createRange()];
                } else {
                    ranges = [];
                }
            }
            return (typeof index != 'undefined' ? ranges[index] : ranges);
        }

        function clear() {
            var sel = get();
            try {
                if (sel.removeAllRanges) {
                    sel.removeAllRanges();
                } else if (sel.empty) {
                    sel.empty();
                } else if (sel.clear) {
                    sel.clear();
                }
            } catch (ex) {
            }
        }

        function element() {
            var sel = get();
            try {
                if (sel.rangeCount) {
                    var range = ranges(0);
                    var node = range.startContainer;
                    var child;
                    if (editor.node.isElement(node) && range.startOffset === 0 && node.childNodes.length) {
                        while (node.childNodes.length && node.childNodes[0].nodeType === Node.ELEMENT_NODE) {
                            node = node.childNodes[0];
                        }
                    }
                    if (node.nodeType == Node.TEXT_NODE && range.startOffset == (node.textContent || '').length && node.nextSibling) {
                        node = node.nextSibling;
                    }
                    if (node.nodeType == Node.ELEMENT_NODE) {
                        var node_found = false;
                        if (node.childNodes.length > 0 && node.childNodes[range.startOffset]) {
                            child = node.childNodes[range.startOffset];
                            while (child && child.nodeType == Node.TEXT_NODE && child.textContent.length === 0) {
                                child = child.nextSibling;
                            }
                            if (child && child.textContent.replace(/\u200B/g, '') === text().replace(/\u200B/g, '')) {
                                node = child;
                                node_found = true;
                            }
                            if (!node_found && node.childNodes.length > 1 && range.startOffset > 0 && node.childNodes[range.startOffset - 1]) {
                                child = node.childNodes[range.startOffset - 1];
                                while (child && child.nodeType == Node.TEXT_NODE && child.textContent.length === 0) {
                                    child = child.nextSibling;
                                }
                                if (child && child.textContent.replace(/\u200B/g, '') === text().replace(/\u200B/g, '')) {
                                    node = child;
                                    node_found = true;
                                }
                            }
                        } else if (!range.collapsed && node.nextSibling && node.nextSibling.nodeType == Node.ELEMENT_NODE) {
                            child = node.nextSibling;
                            if (child && child.textContent.replace(/\u200B/g, '') === text().replace(/\u200B/g, '')) {
                                node = child;
                                node_found = true;
                            }
                        }
                        if (!node_found && node.childNodes.length > 0 && $(node.childNodes[0]).text().replace(/\u200B/g, '') === text().replace(/\u200B/g, '') && ['BR', 'IMG', 'HR'].indexOf(node.childNodes[0].tagName) < 0) {
                            node = node.childNodes[0];
                        }
                    }
                    while (node.nodeType != Node.ELEMENT_NODE && node.parentNode) {
                        node = node.parentNode;
                    }
                    var p = node;
                    while (p && p.tagName != 'HTML') {
                        if (p == editor.el) {
                            return node;
                        }
                        p = $(p).parent()[0];
                    }
                }
            } catch (ex) {
            }
            return editor.el;
        }

        function endElement() {
            var sel = get();
            try {
                if (sel.rangeCount) {
                    var range = ranges(0);
                    var node = range.endContainer;
                    var child;
                    if (node.nodeType == Node.ELEMENT_NODE) {
                        var node_found = false;
                        if (node.childNodes.length > 0 && node.childNodes[range.endOffset] && $(node.childNodes[range.endOffset]).text() === text()) {
                            node = node.childNodes[range.endOffset];
                            node_found = true;
                        } else if (!range.collapsed && node.previousSibling && node.previousSibling.nodeType == Node.ELEMENT_NODE) {
                            child = node.previousSibling;
                            if (child && child.textContent.replace(/\u200B/g, '') === text().replace(/\u200B/g, '')) {
                                node = child;
                                node_found = true;
                            }
                        } else if (!range.collapsed && node.childNodes.length > 0 && node.childNodes[range.endOffset]) {
                            child = node.childNodes[range.endOffset].previousSibling;
                            if (child.nodeType == Node.ELEMENT_NODE) {
                                if (child && child.textContent.replace(/\u200B/g, '') === text().replace(/\u200B/g, '')) {
                                    node = child;
                                    node_found = true;
                                }
                            }
                        }
                        if (!node_found && node.childNodes.length > 0 && $(node.childNodes[node.childNodes.length - 1]).text() === text() && ['BR', 'IMG', 'HR'].indexOf(node.childNodes[node.childNodes.length - 1].tagName) < 0) {
                            node = node.childNodes[node.childNodes.length - 1];
                        }
                    }
                    if (node.nodeType == Node.TEXT_NODE && range.endOffset === 0 && node.previousSibling && node.previousSibling.nodeType == Node.ELEMENT_NODE) {
                        node = node.previousSibling;
                    }
                    while (node.nodeType != Node.ELEMENT_NODE && node.parentNode) {
                        node = node.parentNode;
                    }
                    var p = node;
                    while (p && p.tagName != 'HTML') {
                        if (p == editor.el) {
                            return node;
                        }
                        p = $(p).parent()[0];
                    }
                }
            } catch (ex) {
            }
            return editor.el;
        }

        function rangeElement(rangeContainer, offset) {
            var node = rangeContainer;
            if (node.nodeType == Node.ELEMENT_NODE) {
                if (node.childNodes.length > 0 && node.childNodes[offset]) {
                    node = node.childNodes[offset];
                }
            }
            if (node.nodeType == Node.TEXT_NODE) {
                node = node.parentNode;
            }
            return node;
        }

        function blocks() {
            var blks = [];
            var i;
            var sel = get();
            if (inEditor() && sel.rangeCount) {
                var rngs = ranges();
                for (i = 0; i < rngs.length; i++) {
                    var range = rngs[i];
                    var block_parent;
                    var start_node = rangeElement(range.startContainer, range.startOffset);
                    var end_node = rangeElement(range.endContainer, range.endOffset);
                    if ((editor.node.isBlock(start_node) || editor.node.hasClass(start_node, 'fr-inner')) && blks.indexOf(start_node) < 0) blks.push(start_node);
                    block_parent = editor.node.blockParent(start_node);
                    if (block_parent && blks.indexOf(block_parent) < 0) {
                        blks.push(block_parent);
                    }
                    var was_into = [];
                    var next_node = start_node;
                    while (next_node !== end_node && next_node !== editor.el) {
                        if (was_into.indexOf(next_node) < 0 && next_node.children && next_node.children.length) {
                            was_into.push(next_node);
                            next_node = next_node.children[0];
                        } else if (next_node.nextSibling) {
                            next_node = next_node.nextSibling;
                        } else if (next_node.parentNode) {
                            next_node = next_node.parentNode;
                            was_into.push(next_node);
                        }
                        if (editor.node.isBlock(next_node) && was_into.indexOf(next_node) < 0 && blks.indexOf(next_node) < 0) {
                            if (next_node !== end_node || range.endOffset > 0) {
                                blks.push(next_node);
                            }
                        }
                    }
                    if (editor.node.isBlock(end_node) && blks.indexOf(end_node) < 0 && range.endOffset > 0) blks.push(end_node);
                    block_parent = editor.node.blockParent(end_node);
                    if (block_parent && blks.indexOf(block_parent) < 0) {
                        blks.push(block_parent);
                    }
                }
            }
            for (i = blks.length - 1; i > 0; i--) {
                if ($(blks[i]).find(blks).length) blks.splice(i, 1);
            }
            return blks;
        }

        function save() {
            if (editor.$wp) {
                editor.markers.remove();
                var rgs = ranges();
                var new_ranges = [];
                var range;
                var i;
                for (i = 0; i < rgs.length; i++) {
                    if (rgs[i].startContainer !== editor.doc || editor.browser.msie) {
                        range = rgs[i];
                        var collapsed = range.collapsed;
                        var start_m = editor.markers.place(range, true, i);
                        var end_m = editor.markers.place(range, false, i);
                        if ((typeof start_m == 'undefined' || !start_m) && collapsed) {
                            $('.fr-marker').remove();
                            editor.selection.setAtEnd(editor.el);
                        }
                        editor.el.normalize();
                        if (editor.browser.safari && !collapsed) {
                            try {
                                range = editor.doc.createRange();
                                range.setStartAfter(start_m);
                                range.setEndBefore(end_m);
                                new_ranges.push(range);
                            } catch (ex) {
                            }
                        }
                    }
                }
                if (editor.browser.safari && new_ranges.length) {
                    editor.selection.clear();
                    for (i = 0; i < new_ranges.length; i++) {
                        editor.selection.get().addRange(new_ranges[i]);
                    }
                }
            }
        }

        function restore() {
            var i;
            var markers = editor.el.querySelectorAll('.fr-marker[data-type="true"]');
            if (!editor.$wp) {
                editor.markers.remove();
                return false;
            }
            if (markers.length === 0) {
                return false;
            }
            if (editor.browser.msie || editor.browser.edge) {
                for (i = 0; i < markers.length; i++) {
                    markers[i].style.display = 'inline-block';
                }
            }
            if (!editor.core.hasFocus() && !editor.browser.msie && !editor.browser.webkit) {
                editor.$el.focus();
            }
            clear();
            var sel = get();
            for (i = 0; i < markers.length; i++) {
                var id = $(markers[i]).data('id');
                var start_marker = markers[i];
                var range = editor.doc.createRange();
                var end_marker = editor.$el.find('.fr-marker[data-type="false"][data-id="' + id + '"]');
                if (editor.browser.msie || editor.browser.edge) end_marker.css('display', 'inline-block');
                var ghost = null;
                if (end_marker.length > 0) {
                    end_marker = end_marker[0];
                    try {
                        var tmp;
                        var special_case = false;
                        var s_node = start_marker.nextSibling;
                        while (s_node && s_node.nodeType == Node.TEXT_NODE && s_node.textContent.length === 0) {
                            tmp = s_node;
                            s_node = s_node.nextSibling;
                            $(tmp).remove();
                        }
                        var e_node = end_marker.nextSibling;
                        while (e_node && e_node.nodeType == Node.TEXT_NODE && e_node.textContent.length === 0) {
                            tmp = e_node;
                            e_node = e_node.nextSibling;
                            $(tmp).remove();
                        }
                        if (start_marker.nextSibling == end_marker || end_marker.nextSibling == start_marker) {
                            var first_node = (start_marker.nextSibling == end_marker) ? start_marker : end_marker;
                            var last_node = (first_node == start_marker) ? end_marker : start_marker;
                            var prev_node = first_node.previousSibling;
                            while (prev_node && prev_node.nodeType == Node.TEXT_NODE && prev_node.length === 0) {
                                tmp = prev_node;
                                prev_node = prev_node.previousSibling;
                                $(tmp).remove();
                            }
                            if (prev_node && prev_node.nodeType == Node.TEXT_NODE) {
                                while (prev_node && prev_node.previousSibling && prev_node.previousSibling.nodeType == Node.TEXT_NODE) {
                                    prev_node.previousSibling.textContent = prev_node.previousSibling.textContent + prev_node.textContent;
                                    prev_node = prev_node.previousSibling;
                                    $(prev_node.nextSibling).remove();
                                }
                            }
                            var next_node = last_node.nextSibling;
                            while (next_node && next_node.nodeType == Node.TEXT_NODE && next_node.length === 0) {
                                tmp = next_node;
                                next_node = next_node.nextSibling;
                                $(tmp).remove();
                            }
                            if (next_node && next_node.nodeType == Node.TEXT_NODE) {
                                while (next_node && next_node.nextSibling && next_node.nextSibling.nodeType == Node.TEXT_NODE) {
                                    next_node.nextSibling.textContent = next_node.textContent + next_node.nextSibling.textContent;
                                    next_node = next_node.nextSibling;
                                    $(next_node.previousSibling).remove();
                                }
                            }
                            if (prev_node && (editor.node.isVoid(prev_node) || editor.node.isBlock(prev_node))) prev_node = null;
                            if (next_node && (editor.node.isVoid(next_node) || editor.node.isBlock(next_node))) next_node = null;
                            if (prev_node && next_node && prev_node.nodeType == Node.TEXT_NODE && next_node.nodeType == Node.TEXT_NODE) {
                                $(start_marker).remove();
                                $(end_marker).remove();
                                var len = prev_node.textContent.length;
                                prev_node.textContent = prev_node.textContent + next_node.textContent;
                                $(next_node).remove();
                                if (!editor.opts.htmlUntouched) editor.spaces.normalize(prev_node);
                                range.setStart(prev_node, len);
                                range.setEnd(prev_node, len);
                                special_case = true;
                            } else if (!prev_node && next_node && next_node.nodeType == Node.TEXT_NODE) {
                                $(start_marker).remove();
                                $(end_marker).remove();
                                if (!editor.opts.htmlUntouched) editor.spaces.normalize(next_node);
                                ghost = $(editor.doc.createTextNode('\u200B'));
                                $(next_node).before(ghost);
                                range.setStart(next_node, 0);
                                range.setEnd(next_node, 0);
                                special_case = true;
                            } else if (!next_node && prev_node && prev_node.nodeType == Node.TEXT_NODE) {
                                $(start_marker).remove();
                                $(end_marker).remove();
                                if (!editor.opts.htmlUntouched) editor.spaces.normalize(prev_node);
                                ghost = $(editor.doc.createTextNode('\u200B'));
                                $(prev_node).after(ghost);
                                range.setStart(prev_node, prev_node.textContent.length);
                                range.setEnd(prev_node, prev_node.textContent.length);
                                special_case = true;
                            }
                        }
                        if (!special_case) {
                            var x;
                            var y;
                            if ((editor.browser.chrome || editor.browser.edge) && start_marker.nextSibling == end_marker) {
                                x = _normalizedMarker(end_marker, range, true) || range.setStartAfter(end_marker);
                                y = _normalizedMarker(start_marker, range, false) || range.setEndBefore(start_marker);
                            } else {
                                if (start_marker.previousSibling == end_marker) {
                                    start_marker = end_marker;
                                    end_marker = start_marker.nextSibling;
                                }
                                if (!(end_marker.nextSibling && end_marker.nextSibling.tagName === 'BR') && !(!end_marker.nextSibling && editor.node.isBlock(start_marker.previousSibling)) && !(start_marker.previousSibling && start_marker.previousSibling.tagName == 'BR')) {
                                    start_marker.style.display = 'inline';
                                    end_marker.style.display = 'inline';
                                    ghost = $(editor.doc.createTextNode('\u200B'));
                                }
                                x = _normalizedMarker(start_marker, range, true) || ($(start_marker).before(ghost) && range.setStartBefore(start_marker));
                                y = _normalizedMarker(end_marker, range, false) || ($(end_marker).after(ghost) && range.setEndAfter(end_marker));
                            }
                            if (typeof x == 'function') x();
                            if (typeof y == 'function') y();
                        }
                    } catch (ex) {
                        console.warn('RESTORE RANGE', ex);
                    }
                }
                if (ghost) {
                    ghost.remove();
                }
                try {
                    sel.addRange(range);
                } catch (ex) {
                    console.warn('ADD RANGE', ex);
                }
            }
            editor.markers.remove();
        }

        function _normalizedMarker(marker, range, start) {
            var len;
            var prev_node = marker.previousSibling;
            var next_node = marker.nextSibling;
            if (prev_node && next_node && prev_node.nodeType == Node.TEXT_NODE && next_node.nodeType == Node.TEXT_NODE) {
                len = prev_node.textContent.length;
                if (start) {
                    next_node.textContent = prev_node.textContent + next_node.textContent;
                    $(prev_node).remove();
                    $(marker).remove();
                    if (!editor.opts.htmlUntouched) editor.spaces.normalize(next_node);
                    return function () {
                        range.setStart(next_node, len);
                    }
                } else {
                    prev_node.textContent = prev_node.textContent + next_node.textContent;
                    $(next_node).remove();
                    $(marker).remove();
                    if (!editor.opts.htmlUntouched) editor.spaces.normalize(prev_node);
                    return function () {
                        range.setEnd(prev_node, len);
                    }
                }
            } else if (prev_node && !next_node && prev_node.nodeType == Node.TEXT_NODE) {
                len = prev_node.textContent.length;
                if (start) {
                    if (!editor.opts.htmlUntouched) editor.spaces.normalize(prev_node);
                    return function () {
                        range.setStart(prev_node, len);
                    }
                } else {
                    if (!editor.opts.htmlUntouched) editor.spaces.normalize(prev_node);
                    return function () {
                        range.setEnd(prev_node, len);
                    }
                }
            } else if (next_node && !prev_node && next_node.nodeType == Node.TEXT_NODE) {
                if (start) {
                    if (!editor.opts.htmlUntouched) editor.spaces.normalize(next_node);
                    return function () {
                        range.setStart(next_node, 0);
                    }
                } else {
                    if (!editor.opts.htmlUntouched) editor.spaces.normalize(next_node);
                    return function () {
                        range.setEnd(next_node, 0);
                    }
                }
            }
            return false;
        }

        function _canDelete() {
            var markers = editor.$el.find('.fr-marker');
            for (var i = 0; i < markers.length; i++) {
                if ($(markers[i]).parentsUntil('.fr-element, [contenteditable="true"]', '[contenteditable="false"]').length) {
                    return false;
                }
            }
            return true;
        }

        function isCollapsed() {
            var rgs = ranges();
            for (var i = 0; i < rgs.length; i++) {
                if (!rgs[i].collapsed) return false;
            }
            return true;
        }

        function info(el) {
            var atStart = false;
            var atEnd = false;
            var selRange;
            var testRange;
            if (editor.win.getSelection) {
                var sel = editor.win.getSelection();
                if (sel.rangeCount) {
                    selRange = sel.getRangeAt(0);
                    testRange = selRange.cloneRange();
                    testRange.selectNodeContents(el);
                    testRange.setEnd(selRange.startContainer, selRange.startOffset);
                    atStart = (testRange.toString() === '');
                    testRange.selectNodeContents(el);
                    testRange.setStart(selRange.endContainer, selRange.endOffset);
                    atEnd = (testRange.toString() === '');
                }
            } else if (editor.doc.selection && editor.doc.selection.type != 'Control') {
                selRange = editor.doc.selection.createRange();
                testRange = selRange.duplicate();
                testRange.moveToElementText(el);
                testRange.setEndPoint('EndToStart', selRange);
                atStart = (testRange.text === '');
                testRange.moveToElementText(el);
                testRange.setEndPoint('StartToEnd', selRange);
                atEnd = (testRange.text === '');
            }
            return {atStart: atStart, atEnd: atEnd};
        }

        function isFull() {
            if (isCollapsed()) return false;
            editor.selection.save()
            var els = editor.el.querySelectorAll('td, th, img, br');
            var i;
            for (i = 0; i < els.length; i++) {
                if (els[i].nextSibling) {
                    els[i].innerHTML = '<span class="fr-mk">' + $.FE.INVISIBLE_SPACE + '</span>' + els[i].innerHTML;
                }
            }
            var full = false;
            var inf = info(editor.el);
            if (inf.atStart && inf.atEnd) full = true;
            els = editor.el.querySelectorAll('.fr-mk');
            for (i = 0; i < els.length; i++) {
                els[i].parentNode.removeChild(els[i]);
            }
            editor.selection.restore();
            return full;
        }

        function _emptyInnerNodes(node, first) {
            if (typeof first == 'undefined') first = true;
            var h = $(node).html();
            if (h && h.replace(/\u200b/g, '').length != h.length) $(node).html(h.replace(/\u200b/g, ''));
            var contents = editor.node.contents(node);
            for (var j = 0; j < contents.length; j++) {
                if (contents[j].nodeType != Node.ELEMENT_NODE) {
                    $(contents[j]).remove();
                } else {
                    _emptyInnerNodes(contents[j], j === 0);
                    if (j === 0) first = false;
                }
            }
            if (node.nodeType == Node.TEXT_NODE) {
                $(node).replaceWith('<span data-first="true" data-text="true"></span>');
            } else if (first) {
                $(node).attr('data-first', true);
            }
        }

        function _filterFrInner() {
            return $(this).find('fr-inner').length === 0;
        }

        function _processNodeDelete($node, should_delete) {
            var contents = editor.node.contents($node.get(0));
            if (['TD', 'TH'].indexOf($node.get(0).tagName) >= 0 && $node.find('.fr-marker').length == 1 && editor.node.hasClass(contents[0], 'fr-marker')) {
                $node.attr('data-del-cell', true);
            }
            for (var i = 0; i < contents.length; i++) {
                var node = contents[i];
                if (editor.node.hasClass(node, 'fr-marker')) {
                    should_delete = (should_delete + 1) % 2;
                } else if (should_delete) {
                    if ($(node).find('.fr-marker').length > 0) {
                        should_delete = _processNodeDelete($(node), should_delete);
                    } else {
                        if (['TD', 'TH'].indexOf(node.tagName) < 0 && !editor.node.hasClass(node, 'fr-inner')) {
                            if (!editor.opts.keepFormatOnDelete || editor.$el.find('[data-first]').length > 0 || editor.node.isVoid(node)) {
                                $(node).remove();
                            } else {
                                _emptyInnerNodes(node);
                            }
                        } else if (editor.node.hasClass(node, 'fr-inner')) {
                            if ($(node).find('.fr-inner').length === 0) {
                                $(node).html('<br>');
                            } else {
                                $(node).find('.fr-inner').filter(_filterFrInner).html('<br>');
                            }
                        } else {
                            $(node).empty();
                            $(node).attr('data-del-cell', true);
                        }
                    }
                } else {
                    if ($(node).find('.fr-marker').length > 0) {
                        should_delete = _processNodeDelete($(node), should_delete);
                    }
                }
            }
            return should_delete;
        }

        function inEditor() {
            try {
                if (!editor.$wp) return false;
                var range = ranges(0);
                var container = range.commonAncestorContainer;
                while (container && !editor.node.isElement(container)) {
                    container = container.parentNode;
                }
                if (editor.node.isElement(container)) return true;
                return false;
            } catch (ex) {
                return false;
            }
        }

        function remove() {
            if (isCollapsed()) return true;
            var i;
            save();
            var _prevSibling = function (node) {
                var prev_node = node.previousSibling;
                while (prev_node && prev_node.nodeType == Node.TEXT_NODE && prev_node.textContent.length === 0) {
                    var tmp = prev_node;
                    prev_node = prev_node.previousSibling;
                    $(tmp).remove();
                }
                return prev_node;
            }
            var _nextSibling = function (node) {
                var next_node = node.nextSibling;
                while (next_node && next_node.nodeType == Node.TEXT_NODE && next_node.textContent.length === 0) {
                    var tmp = next_node;
                    next_node = next_node.nextSibling;
                    $(tmp).remove();
                }
                return next_node;
            }
            var start_markers = editor.$el.find('.fr-marker[data-type="true"]');
            for (i = 0; i < start_markers.length; i++) {
                var sm = start_markers[i];
                while (!_prevSibling(sm) && !editor.node.isBlock(sm.parentNode) && !editor.$el.is(sm.parentNode) && !editor.node.hasClass(sm.parentNode, 'fr-inner')) {
                    $(sm.parentNode).before(sm);
                }
            }
            var end_markers = editor.$el.find('.fr-marker[data-type="false"]');
            for (i = 0; i < end_markers.length; i++) {
                var em = end_markers[i];
                while (!_nextSibling(em) && !editor.node.isBlock(em.parentNode) && !editor.$el.is(em.parentNode) && !editor.node.hasClass(em.parentNode, 'fr-inner')) {
                    $(em.parentNode).after(em);
                }
                if (em.parentNode && editor.node.isBlock(em.parentNode) && editor.node.isEmpty(em.parentNode) && !editor.$el.is(em.parentNode) && !editor.node.hasClass(em.parentNode, 'fr-inner') && editor.opts.keepFormatOnDelete) {
                    $(em.parentNode).after(em);
                }
            }
            if (_canDelete()) {
                _processNodeDelete(editor.$el, 0);
                var $first_node = editor.$el.find('[data-first="true"]');
                if ($first_node.length) {
                    editor.$el.find('.fr-marker').remove();
                    $first_node.append($.FE.INVISIBLE_SPACE + $.FE.MARKERS).removeAttr('data-first');
                    if ($first_node.attr('data-text')) {
                        $first_node.replaceWith($first_node.html());
                    }
                } else {
                    editor.$el.find('table').filter(function () {
                        var ok = $(this).find('[data-del-cell]').length > 0 && $(this).find('[data-del-cell]').length == $(this).find('td, th').length;
                        return ok;
                    }).remove();
                    editor.$el.find('[data-del-cell]').removeAttr('data-del-cell');
                    start_markers = editor.$el.find('.fr-marker[data-type="true"]');
                    for (i = 0; i < start_markers.length; i++) {
                        var start_marker = start_markers[i];
                        var next_node = start_marker.nextSibling;
                        var end_marker = editor.$el.find('.fr-marker[data-type="false"][data-id="' + $(start_marker).data('id') + '"]').get(0);
                        if (end_marker) {
                            if (start_marker && !(next_node && next_node == end_marker)) {
                                var start_parent = editor.node.blockParent(start_marker);
                                var end_parent = editor.node.blockParent(end_marker);
                                var list_start = false;
                                var list_end = false;
                                if (start_parent && ['UL', 'OL'].indexOf(start_parent.tagName) >= 0) {
                                    start_parent = null;
                                    list_start = true;
                                }
                                if (end_parent && ['UL', 'OL'].indexOf(end_parent.tagName) >= 0) {
                                    end_parent = null;
                                    list_end = true;
                                }
                                $(start_marker).after(end_marker);
                                if (start_parent != end_parent) {
                                    if (start_parent == null && !list_start) {
                                        var deep_parent = editor.node.deepestParent(start_marker);
                                        if (deep_parent) {
                                            $(deep_parent).after($(end_parent).html());
                                            $(end_parent).remove();
                                        } else if ($(end_parent).parentsUntil(editor.$el, 'table').length === 0) {
                                            $(start_marker).next().after($(end_parent).html());
                                            $(end_parent).remove();
                                        }
                                    } else if (end_parent == null && !list_end && $(start_parent).parentsUntil(editor.$el, 'table').length === 0) {
                                        next_node = start_parent;
                                        while (!next_node.nextSibling && next_node.parentNode != editor.el) {
                                            next_node = next_node.parentNode;
                                        }
                                        next_node = next_node.nextSibling;
                                        while (next_node && next_node.tagName != 'BR') {
                                            var tmp_node = next_node.nextSibling;
                                            $(start_parent).append(next_node);
                                            next_node = tmp_node;
                                        }
                                        if (next_node && next_node.tagName == 'BR') {
                                            $(next_node).remove();
                                        }
                                    } else if (start_parent && end_parent && $(start_parent).parentsUntil(editor.$el, 'table').length === 0 && $(end_parent).parentsUntil(editor.$el, 'table').length === 0 && $(start_parent).find(end_parent).length === 0 && $(end_parent).find(start_parent).length === 0) {
                                        $(start_parent).append($(end_parent).html());
                                        $(end_parent).remove();
                                    }
                                }
                            }
                        } else {
                            end_marker = $(start_marker).clone().attr('data-type', false);
                            $(start_marker).after(end_marker);
                        }
                    }
                }
            }
            editor.$el.find('li:empty').remove()
            if (!editor.opts.keepFormatOnDelete) {
                editor.html.fillEmptyBlocks();
            }
            editor.html.cleanEmptyTags(true);
            if (!editor.opts.htmlUntouched) {
                editor.clean.lists();
                editor.$el.find('li:empty').append('<br>');
                editor.spaces.normalize();
            }
            var last_marker = editor.$el.find('.fr-marker:last').get(0);
            var first_marker = editor.$el.find('.fr-marker:first').get(0);
            if ((typeof last_marker !== 'undefined' && typeof first_marker !== 'undefined') && !last_marker.nextSibling && first_marker.previousSibling && first_marker.previousSibling.tagName == 'BR' && editor.node.isElement(last_marker.parentNode) && editor.node.isElement(first_marker.parentNode)) {
                editor.$el.append('<br>');
            }
            restore();
        }

        function setAtStart(node, deep) {
            if (!node || node.getElementsByClassName('fr-marker').length > 0) return false;
            var child = node.firstChild;
            while (child && (editor.node.isBlock(child) || (deep && !editor.node.isVoid(child) && child.nodeType == Node.ELEMENT_NODE))) {
                node = child;
                child = child.firstChild;
            }
            node.innerHTML = $.FE.MARKERS + node.innerHTML;
        }

        function setAtEnd(node, deep) {
            if (!node || node.getElementsByClassName('fr-marker').length > 0) return false;
            var child = node.lastChild;
            while (child && (editor.node.isBlock(child) || (deep && !editor.node.isVoid(child) && child.nodeType == Node.ELEMENT_NODE))) {
                node = child;
                child = child.lastChild;
            }
            var span = editor.doc.createElement('SPAN');
            span.setAttribute('id', 'fr-sel-markers');
            span.innerHTML = $.FE.MARKERS;
            while (node.parentNode && editor.opts.htmlAllowedEmptyTags && editor.opts.htmlAllowedEmptyTags.indexOf(node.tagName.toLowerCase()) >= 0) {
                node = node.parentNode;
            }
            node.appendChild(span);
            var nd = node.querySelector('#fr-sel-markers');
            nd.outerHTML = nd.innerHTML;
        }

        function setBefore(node, use_current_node) {
            if (typeof use_current_node == 'undefined') use_current_node = true;
            var prev_node = node.previousSibling;
            while (prev_node && prev_node.nodeType == Node.TEXT_NODE && prev_node.textContent.length === 0) {
                prev_node = prev_node.previousSibling;
            }
            if (prev_node) {
                if (editor.node.isBlock(prev_node)) {
                    setAtEnd(prev_node);
                } else if (prev_node.tagName == 'BR') {
                    $(prev_node).before($.FE.MARKERS);
                } else {
                    $(prev_node).after($.FE.MARKERS);
                }
                return true;
            } else if (use_current_node) {
                if (editor.node.isBlock(node)) {
                    setAtStart(node);
                } else {
                    $(node).before($.FE.MARKERS);
                }
                return true;
            } else {
                return false;
            }
        }

        function setAfter(node, use_current_node) {
            if (typeof use_current_node == 'undefined') use_current_node = true;
            var next_node = node.nextSibling;
            while (next_node && next_node.nodeType == Node.TEXT_NODE && next_node.textContent.length === 0) {
                next_node = next_node.nextSibling;
            }
            if (next_node) {
                if (editor.node.isBlock(next_node)) {
                    setAtStart(next_node);
                } else {
                    $(next_node).before($.FE.MARKERS);
                }
                return true;
            } else if (use_current_node) {
                if (editor.node.isBlock(node)) {
                    setAtEnd(node);
                } else {
                    $(node).after($.FE.MARKERS);
                }
                return true;
            } else {
                return false;
            }
        }

        return {
            text: text,
            get: get,
            ranges: ranges,
            clear: clear,
            element: element,
            endElement: endElement,
            save: save,
            restore: restore,
            isCollapsed: isCollapsed,
            isFull: isFull,
            inEditor: inEditor,
            remove: remove,
            blocks: blocks,
            info: info,
            setAtEnd: setAtEnd,
            setAtStart: setAtStart,
            setBefore: setBefore,
            setAfter: setAfter,
            rangeElement: rangeElement
        }
    };
    $.extend($.FE.DEFAULTS, {
        htmlAllowedTags: ['a', 'abbr', 'address', 'area', 'article', 'aside', 'audio', 'b', 'base', 'bdi', 'bdo', 'blockquote', 'br', 'button', 'canvas', 'caption', 'cite', 'code', 'col', 'colgroup', 'datalist', 'dd', 'del', 'details', 'dfn', 'dialog', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'hr', 'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'keygen', 'label', 'legend', 'li', 'link', 'main', 'map', 'mark', 'menu', 'menuitem', 'meter', 'nav', 'noscript', 'object', 'ol', 'optgroup', 'option', 'output', 'p', 'param', 'pre', 'progress', 'queue', 'rp', 'rt', 'ruby', 's', 'samp', 'script', 'style', 'section', 'select', 'small', 'source', 'span', 'strike', 'strong', 'sub', 'summary', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'time', 'tr', 'track', 'u', 'ul', 'var', 'video', 'wbr'],
        htmlRemoveTags: ['script', 'style'],
        htmlAllowedAttrs: ['accept', 'accept-charset', 'accesskey', 'action', 'align', 'allowfullscreen', 'allowtransparency', 'alt', 'aria-.*', 'async', 'autocomplete', 'autofocus', 'autoplay', 'autosave', 'background', 'bgcolor', 'border', 'charset', 'cellpadding', 'cellspacing', 'checked', 'cite', 'class', 'color', 'cols', 'colspan', 'content', 'contenteditable', 'contextmenu', 'controls', 'coords', 'data', 'data-.*', 'datetime', 'default', 'defer', 'dir', 'dirname', 'disabled', 'download', 'draggable', 'dropzone', 'enctype', 'for', 'form', 'formaction', 'frameborder', 'headers', 'height', 'hidden', 'high', 'href', 'hreflang', 'http-equiv', 'icon', 'id', 'ismap', 'itemprop', 'keytype', 'kind', 'label', 'lang', 'language', 'list', 'loop', 'low', 'max', 'maxlength', 'media', 'method', 'min', 'mozallowfullscreen', 'multiple', 'muted', 'name', 'novalidate', 'open', 'optimum', 'pattern', 'ping', 'placeholder', 'playsinline', 'poster', 'preload', 'pubdate', 'radiogroup', 'readonly', 'rel', 'required', 'reversed', 'rows', 'rowspan', 'sandbox', 'scope', 'scoped', 'scrolling', 'seamless', 'selected', 'shape', 'size', 'sizes', 'span', 'src', 'srcdoc', 'srclang', 'srcset', 'start', 'step', 'summary', 'spellcheck', 'style', 'tabindex', 'target', 'title', 'type', 'translate', 'usemap', 'value', 'valign', 'webkitallowfullscreen', 'width', 'wrap'],
        htmlAllowedStyleProps: ['.*'],
        htmlAllowComments: true,
        htmlUntouched: false,
        fullPage: false
    });
    $.FE.HTML5Map = {B: 'STRONG', I: 'EM', STRIKE: 'S'}, $.FE.MODULES.clean = function (editor) {
        var allowedTagsRE;
        var removeTagsRE;
        var allowedAttrsRE;
        var allowedStylePropsRE;

        function _removeInvisible(node) {
            if (node.nodeType == Node.ELEMENT_NODE && node.getAttribute('class') && node.getAttribute('class').indexOf('fr-marker') >= 0) return false;
            var contents = editor.node.contents(node);
            var markers = [];
            var i;
            for (i = 0; i < contents.length; i++) {
                if (contents[i].nodeType == Node.ELEMENT_NODE && !editor.node.isVoid(contents[i])) {
                    if (contents[i].textContent.replace(/\u200b/g, '').length != contents[i].textContent.length) {
                        _removeInvisible(contents[i]);
                    }
                } else if (contents[i].nodeType == Node.TEXT_NODE) {
                    contents[i].textContent = contents[i].textContent.replace(/\u200b/g, '');
                }
            }
            if (node.nodeType == Node.ELEMENT_NODE && !editor.node.isVoid(node)) {
                node.normalize();
                contents = editor.node.contents(node);
                markers = node.querySelectorAll('.fr-marker');
                if (contents.length - markers.length === 0) {
                    for (i = 0; i < contents.length; i++) {
                        if (contents[i].nodeType == Node.ELEMENT_NODE && (contents[i].getAttribute('class') || '').indexOf('fr-marker') < 0) {
                            return false;
                        }
                    }
                    for (i = 0; i < markers.length; i++) {
                        node.parentNode.insertBefore(markers[i].cloneNode(true), node);
                    }
                    node.parentNode.removeChild(node);
                    return false;
                }
            }
        }

        function _toHTML(el, is_pre) {
            if (el.nodeType == Node.COMMENT_NODE) return '<!--' + el.nodeValue + '-->';
            if (el.nodeType == Node.TEXT_NODE) {
                if (is_pre) {
                    return el.textContent.replace(/\&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                }
                return el.textContent.replace(/\&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\u00A0/g, '&nbsp;').replace(/\u0009/g, '');
            }
            if (el.nodeType != Node.ELEMENT_NODE) return el.outerHTML;
            if (el.nodeType == Node.ELEMENT_NODE && ['STYLE', 'SCRIPT', 'NOSCRIPT'].indexOf(el.tagName) >= 0) return el.outerHTML;
            if (el.nodeType == Node.ELEMENT_NODE && el.tagName == 'svg') {
                var temp = document.createElement('div');
                var node_clone = el.cloneNode(true);
                temp.appendChild(node_clone);
                return temp.innerHTML;
            }
            if (el.tagName == 'IFRAME') {
                return el.outerHTML.replace(/\&lt;/g, '<').replace(/\&gt;/g, '>');
            }
            var contents = el.childNodes;
            if (contents.length === 0) return el.outerHTML;
            var str = '';
            for (var i = 0; i < contents.length; i++) {
                if (el.tagName == 'PRE') is_pre = true;
                str += _toHTML(contents[i], is_pre);
            }
            return editor.node.openTagString(el) + str + editor.node.closeTagString(el);
        }

        var scripts = [];

        function _encode(dirty_html) {
            scripts = [];
            dirty_html = dirty_html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, function (str) {
                scripts.push(str);
                return '[FROALA.EDITOR.SCRIPT ' + (scripts.length - 1) + ']';
            });
            dirty_html = dirty_html.replace(/<noscript\b[^<]*(?:(?!<\/noscript>)<[^<]*)*<\/noscript>/gi, function (str) {
                scripts.push(str);
                return '[FROALA.EDITOR.NOSCRIPT ' + (scripts.length - 1) + ']';
            });
            dirty_html = dirty_html.replace(/<meta((?:[\w\W]*?)) http-equiv="/g, '<meta$1 data-fr-http-equiv="');
            dirty_html = dirty_html.replace(/<img((?:[\w\W]*?)) src="/g, '<img$1 data-fr-src="');
            return dirty_html;
        }

        function _decode(dirty_html) {
            dirty_html = dirty_html.replace(/\[FROALA\.EDITOR\.SCRIPT ([\d]*)\]/gi, function (str, a1) {
                if (editor.opts.htmlRemoveTags.indexOf('script') >= 0) {
                    return '';
                } else {
                    return scripts[parseInt(a1, 10)];
                }
            });
            dirty_html = dirty_html.replace(/\[FROALA\.EDITOR\.NOSCRIPT ([\d]*)\]/gi, function (str, a1) {
                if (editor.opts.htmlRemoveTags.indexOf('noscript') >= 0) {
                    return '';
                } else {
                    return scripts[parseInt(a1, 10)].replace(/\&lt;/g, '<').replace(/\&gt;/g, '>');
                }
            });
            dirty_html = dirty_html.replace(/<img((?:[\w\W]*?)) data-fr-src="/g, '<img$1 src="');
            return dirty_html;
        }

        function _cleanStyle(style) {
            var cleaned_style = style.replace(/;;/gi, ';');
            cleaned_style = cleaned_style.replace(/^;/gi, '');
            if (cleaned_style.charAt(cleaned_style.length) != ';') {
                cleaned_style += ';';
            }
            return cleaned_style;
        }

        function _cleanAttrs(attrs) {
            var nm;
            for (nm in attrs) {
                if (attrs.hasOwnProperty(nm)) {
                    var is_attr_allowed = nm.match(allowedAttrsRE);
                    var allowed_style_props_matches = null;
                    if (nm == 'style' && editor.opts.htmlAllowedStyleProps.length) {
                        allowed_style_props_matches = attrs[nm].match(allowedStylePropsRE);
                    }
                    if (is_attr_allowed && allowed_style_props_matches) {
                        attrs[nm] = _cleanStyle(allowed_style_props_matches.join(';'));
                    } else if (!is_attr_allowed || (nm == 'style' && !allowed_style_props_matches)) {
                        delete attrs[nm];
                    }
                }
            }
            var str = '';
            var keys = Object.keys(attrs).sort();
            for (var i = 0; i < keys.length; i++) {
                nm = keys[i];
                if (attrs[nm].indexOf('"') < 0) {
                    str += ' ' + nm + '="' + attrs[nm] + '"';
                } else {
                    str += ' ' + nm + '=\'' + attrs[nm] + '\'';
                }
            }
            return str;
        }

        function _rebuild(body_html, head_html, original_html) {
            if (editor.opts.fullPage) {
                var doctype = editor.html.extractDoctype(original_html);
                var html_attrs = _cleanAttrs(editor.html.extractNodeAttrs(original_html, 'html'));
                head_html = head_html == null ? editor.html.extractNode(original_html, 'head') || '<title></title>' : head_html;
                var head_attrs = _cleanAttrs(editor.html.extractNodeAttrs(original_html, 'head'));
                var body_attrs = _cleanAttrs(editor.html.extractNodeAttrs(original_html, 'body'));
                return doctype + '<html' + html_attrs + '><head' + head_attrs + '>' + head_html + '</head><body' + body_attrs + '>' + body_html + '</body></html>';
            }
            return body_html;
        }

        function _process(html, func) {
            var i;
            var doc = document.implementation.createHTMLDocument('Froala DOC');
            var el = doc.createElement('DIV');
            $(el).append(html);
            var new_html = '';
            if (el) {
                var els = editor.node.contents(el);
                for (i = 0; i < els.length; i++) {
                    func(els[i]);
                }
                els = editor.node.contents(el);
                for (i = 0; i < els.length; i++) {
                    new_html += _toHTML(els[i]);
                }
            }
            return new_html;
        }

        function exec(html, func, parse_head) {
            html = _encode(html);
            var b_html = html;
            var h_html = null;
            if (editor.opts.fullPage) {
                b_html = (editor.html.extractNode(html, 'body') || (html.indexOf('<body') >= 0 ? '' : html));
                if (parse_head) {
                    h_html = (editor.html.extractNode(html, 'head') || '');
                }
            }
            b_html = _process(b_html, func);
            if (h_html) h_html = _process(h_html, func);
            var new_html = _rebuild(b_html, h_html, html);
            return _decode(new_html);
        }

        function invisibleSpaces(dirty_html) {
            if (dirty_html.replace(/\u200b/g, '').length == dirty_html.length) return dirty_html;
            return editor.clean.exec(dirty_html, _removeInvisible);
        }

        function toHTML5() {
            var els = editor.el.querySelectorAll(Object.keys($.FE.HTML5Map).join(','));
            if (els.length) {
                var sel_saved = false;
                if (!editor.el.querySelector('.fr-marker')) {
                    editor.selection.save();
                    sel_saved = true;
                }
                for (var i = 0; i < els.length; i++) {
                    if (editor.node.attributes(els[i]) === '') {
                        $(els[i]).replaceWith('<' + $.FE.HTML5Map[els[i].tagName] + '>' + els[i].innerHTML + '</' + $.FE.HTML5Map[els[i].tagName] + '>');
                    }
                }
                if (sel_saved) {
                    editor.selection.restore();
                }
            }
        }

        function _convertHref(href) {
            var div = editor.doc.createElement('DIV');
            div.innerText = href;
            return div.textContent;
        }

        function _node(node) {
            if (node.tagName == 'SPAN' && (node.getAttribute('class') || '').indexOf('fr-marker') >= 0) return false;
            if (node.tagName == 'PRE') _cleanPre(node);
            if (node.nodeType == Node.ELEMENT_NODE) {
                if (node.getAttribute('data-fr-src') && node.getAttribute('data-fr-src').indexOf('blob:') !== 0) node.setAttribute('data-fr-src', editor.helpers.sanitizeURL(_convertHref(node.getAttribute('data-fr-src'))));
                if (node.getAttribute('href')) node.setAttribute('href', editor.helpers.sanitizeURL(_convertHref(node.getAttribute('href'))));
                if (node.getAttribute('src')) node.setAttribute('src', editor.helpers.sanitizeURL(_convertHref(node.getAttribute('src'))));
                if (node.getAttribute('data')) node.setAttribute('data', editor.helpers.sanitizeURL(_convertHref(node.getAttribute('data'))));
                if (['TABLE', 'TBODY', 'TFOOT', 'TR'].indexOf(node.tagName) >= 0) {
                    node.innerHTML = node.innerHTML.trim();
                }
            }
            if (!editor.opts.pasteAllowLocalImages && node.nodeType == Node.ELEMENT_NODE && node.tagName == 'IMG' && node.getAttribute('data-fr-src') && node.getAttribute('data-fr-src').indexOf('file://') === 0) {
                node.parentNode.removeChild(node);
                return false;
            }
            if (node.nodeType == Node.ELEMENT_NODE && $.FE.HTML5Map[node.tagName] && editor.node.attributes(node) === '') {
                var tg = $.FE.HTML5Map[node.tagName];
                var new_node = '<' + tg + '>' + node.innerHTML + '</' + tg + '>';
                node.insertAdjacentHTML('beforebegin', new_node);
                node = node.previousSibling;
                node.parentNode.removeChild(node.nextSibling);
            }
            if (!editor.opts.htmlAllowComments && node.nodeType == Node.COMMENT_NODE) {
                if (node.data.indexOf('[FROALA.EDITOR') !== 0) {
                    node.parentNode.removeChild(node);
                }
            } else if (node.tagName && node.tagName.match(removeTagsRE)) {
                node.parentNode.removeChild(node);
            } else if (node.tagName && !node.tagName.match(allowedTagsRE)) {
                if (node.tagName === 'svg') {
                    node.parentNode.removeChild(node);
                } else if (!(editor.browser.safari && node.tagName == 'path' && node.parentNode && node.parentNode.tagName == 'svg')) {
                    try {
                        node.outerHTML = node.innerHTML;
                    } catch (ex) {
                    }
                }
            } else {
                var attrs = node.attributes;
                if (attrs) {
                    for (var i = attrs.length - 1; i >= 0; i--) {
                        var attr = attrs[i];
                        var is_attr_allowed = attr.nodeName.match(allowedAttrsRE);
                        var allowed_style_props_matches = null;
                        if (attr.nodeName == 'style' && editor.opts.htmlAllowedStyleProps.length) {
                            allowed_style_props_matches = attr.value.match(allowedStylePropsRE);
                        }
                        if (is_attr_allowed && allowed_style_props_matches) {
                            attr.value = _cleanStyle(allowed_style_props_matches.join(';'));
                        } else if (!is_attr_allowed || (attr.nodeName == 'style' && !allowed_style_props_matches)) {
                            node.removeAttribute(attr.nodeName);
                        }
                    }
                }
            }
        }

        function _run(node) {
            var contents = editor.node.contents(node);
            for (var i = 0; i < contents.length; i++) {
                if (contents[i].nodeType != Node.TEXT_NODE) {
                    _run(contents[i]);
                }
            }
            _node(node);
        }

        function _cleanPre(pre) {
            var content = pre.innerHTML;
            if (content.indexOf('\n') >= 0) {
                pre.innerHTML = content.replace(/\n/g, '<br>');
            }
        }

        function html(dirty_html, denied_tags, denied_attrs, full_page) {
            if (typeof denied_tags == 'undefined') denied_tags = [];
            if (typeof denied_attrs == 'undefined') denied_attrs = [];
            if (typeof full_page == 'undefined') full_page = false;
            var allowed_tags = $.merge([], editor.opts.htmlAllowedTags);
            var i;
            for (i = 0; i < denied_tags.length; i++) {
                if (allowed_tags.indexOf(denied_tags[i]) >= 0) {
                    allowed_tags.splice(allowed_tags.indexOf(denied_tags[i]), 1);
                }
            }
            var allowed_attrs = $.merge([], editor.opts.htmlAllowedAttrs);
            for (i = 0; i < denied_attrs.length; i++) {
                if (allowed_attrs.indexOf(denied_attrs[i]) >= 0) {
                    allowed_attrs.splice(allowed_attrs.indexOf(denied_attrs[i]), 1);
                }
            }
            allowed_attrs.push('data-fr-.*');
            allowed_attrs.push('fr-.*');
            allowedTagsRE = new RegExp('^' + allowed_tags.join('$|^') + '$', 'gi');
            allowedAttrsRE = new RegExp('^' + allowed_attrs.join('$|^') + '$', 'gi');
            removeTagsRE = new RegExp('^' + editor.opts.htmlRemoveTags.join('$|^') + '$', 'gi');
            if (editor.opts.htmlAllowedStyleProps.length) {
                allowedStylePropsRE = new RegExp('((^|;|\\s)' + editor.opts.htmlAllowedStyleProps.join(':.+?(?=;|$))|((^|;|\\s)') + ':.+?(?=(;)|$))', 'gi');
            } else {
                allowedStylePropsRE = null;
            }
            dirty_html = exec(dirty_html, _run, true);
            return dirty_html;
        }

        function _tablesWrapTHEAD() {
            var trs = editor.el.querySelectorAll('tr');
            for (var i = 0; i < trs.length; i++) {
                var children = trs[i].children;
                var ok = true;
                for (var j = 0; j < children.length; j++) {
                    if (children[j].tagName != 'TH') {
                        ok = false;
                        break;
                    }
                }
                if (ok === false || children.length === 0) continue;
                var tr = trs[i];
                while (tr && tr.tagName != 'TABLE' && tr.tagName != 'THEAD') {
                    tr = tr.parentNode;
                }
                var thead = tr;
                if (thead.tagName != 'THEAD') {
                    thead = editor.doc.createElement('THEAD');
                    tr.insertBefore(thead, tr.firstChild);
                }
                thead.appendChild(trs[i]);
            }
        }

        function tables() {
            _tablesWrapTHEAD();
        }

        function _listsWrapMissplacedLI() {
            var lis = [];
            var filterListItem = function (li) {
                return !editor.node.isList(li.parentNode);
            };
            do {
                if (lis.length) {
                    var li = lis[0];
                    var ul = editor.doc.createElement('ul');
                    li.parentNode.insertBefore(ul, li);
                    do {
                        var tmp = li;
                        li = li.nextSibling;
                        ul.appendChild(tmp);
                    } while (li && li.tagName == 'LI');
                }
                lis = [];
                var li_sel = editor.el.querySelectorAll('li');
                for (var i = 0; i < li_sel.length; i++) {
                    if (filterListItem(li_sel[i])) lis.push(li_sel[i]);
                }
            } while (lis.length > 0);
        }

        function _listsJoinSiblings() {
            var sibling_lists = editor.el.querySelectorAll('ol + ol, ul + ul');
            for (var k = 0; k < sibling_lists.length; k++) {
                var list = sibling_lists[k];
                if (editor.node.isList(list.previousSibling) && editor.node.openTagString(list) == editor.node.openTagString(list.previousSibling)) {
                    var childs = editor.node.contents(list);
                    for (var i = 0; i < childs.length; i++) {
                        list.previousSibling.appendChild(childs[i]);
                    }
                    list.parentNode.removeChild(list);
                }
            }
        }

        function _listsRemoveEmpty() {
            var i;
            var do_remove;
            var removeEmptyList = function (lst) {
                if (!lst.querySelector('LI')) {
                    do_remove = true;
                    lst.parentNode.removeChild(lst);
                }
            };
            do {
                do_remove = false;
                var empty_lis = editor.el.querySelectorAll('li:empty');
                for (i = 0; i < empty_lis.length; i++) {
                    empty_lis[i].parentNode.removeChild(empty_lis[i]);
                }
                var remaining_lists = editor.el.querySelectorAll('ul, ol');
                for (i = 0; i < remaining_lists.length; i++) {
                    removeEmptyList(remaining_lists[i]);
                }
            } while (do_remove === true);
        }

        function _listsWrapLists() {
            var direct_lists = editor.el.querySelectorAll('ul > ul, ol > ol, ul > ol, ol > ul');
            for (var i = 0; i < direct_lists.length; i++) {
                var list = direct_lists[i];
                var prev_li = list.previousSibling;
                if (prev_li) {
                    if (prev_li.tagName == 'LI') {
                        prev_li.appendChild(list);
                    } else {
                        $(list).wrap('<li></li>');
                    }
                }
            }
        }

        function _listsNoTagAfterNested() {
            var nested_lists = editor.el.querySelectorAll('li > ul, li > ol');
            for (var i = 0; i < nested_lists.length; i++) {
                var lst = nested_lists[i];
                if (lst.nextSibling) {
                    var node = lst.nextSibling;
                    var $new_li = $('<li>');
                    $(lst.parentNode).after($new_li);
                    do {
                        var tmp = node;
                        node = node.nextSibling;
                        $new_li.append(tmp);
                    } while (node);
                }
            }
        }

        function _listsTypeInNested() {
            var nested_lists = editor.el.querySelectorAll('li > ul, li > ol');
            for (var i = 0; i < nested_lists.length; i++) {
                var lst = nested_lists[i];
                if (editor.node.isFirstSibling(lst)) {
                    $(lst).before('<br/>');
                } else if (lst.previousSibling && lst.previousSibling.tagName == 'BR') {
                    var prev_node = lst.previousSibling.previousSibling;
                    while (prev_node && editor.node.hasClass(prev_node, 'fr-marker')) {
                        prev_node = prev_node.previousSibling;
                    }
                    if (prev_node && prev_node.tagName != 'BR') {
                        $(lst.previousSibling).remove();
                    }
                }
            }
        }

        function _listsRemoveEmptyLI() {
            var empty_lis = editor.el.querySelectorAll('li:empty');
            for (var i = 0; i < empty_lis.length; i++) {
                $(empty_lis[i]).remove();
            }
        }

        function _listsFindMissplacedText() {
            var lists = editor.el.querySelectorAll('ul, ol');
            for (var i = 0; i < lists.length; i++) {
                var contents = editor.node.contents(lists[i]);
                var $li = null;
                for (var j = contents.length - 1; j >= 0; j--) {
                    if (contents[j].tagName != 'LI' && contents[j].tagName != 'UL' && contents[j].tagName != 'OL') {
                        if (!$li) {
                            $li = $('<li>');
                            $li.insertBefore(contents[j]);
                        }
                        $li.prepend(contents[j]);
                    } else {
                        $li = null;
                    }
                }
            }
        }

        function lists() {
            _listsWrapMissplacedLI();
            _listsJoinSiblings();
            _listsFindMissplacedText();
            _listsRemoveEmpty();
            _listsWrapLists();
            _listsNoTagAfterNested();
            _listsTypeInNested();
            _listsRemoveEmptyLI();
        }

        function _init() {
            if (editor.opts.fullPage) {
                $.merge(editor.opts.htmlAllowedTags, ['head', 'title', 'style', 'link', 'base', 'body', 'html', 'meta']);
            }
        }

        return {
            _init: _init,
            html: html,
            toHTML5: toHTML5,
            tables: tables,
            lists: lists,
            invisibleSpaces: invisibleSpaces,
            exec: exec
        }
    };
    $.FE.MODULES.spaces = function (editor) {
        function _normalizeNode(node, browser_way) {
            var p_node = node.previousSibling;
            var n_node = node.nextSibling;
            var txt = node.textContent;
            var parent_node = node.parentNode;
            if (editor.html.isPreformatted(parent_node)) return;
            if (browser_way) {
                txt = txt.replace(/[\f\n\r\t\v ]{2,}/g, ' ');
                if ((!n_node || n_node.tagName === 'BR' || editor.node.isBlock(n_node)) && (editor.node.isBlock(parent_node) || (editor.node.isLink(parent_node) && !parent_node.nextSibling) || editor.node.isElement(parent_node))) {
                    txt = txt.replace(/[\f\n\r\t\v ]{1,}$/g, '');
                }
                if ((!p_node || p_node.tagName === 'BR' || editor.node.isBlock(p_node)) && (editor.node.isBlock(parent_node) || (editor.node.isLink(parent_node) && !parent_node.previousSibling) || editor.node.isElement(parent_node))) {
                    txt = txt.replace(/^[\f\n\r\t\v ]{1,}/g, '');
                }
                if (editor.node.isBlock(n_node) || editor.node.isBlock(p_node)) {
                    txt = txt.replace(/^[\f\n\r\t\v ]{1,}/g, '');
                }
                if (txt === ' ' && ((p_node && editor.node.isVoid(p_node)) || (n_node && editor.node.isVoid(n_node))) && !((p_node && n_node && editor.node.isVoid(p_node)) || (n_node && p_node && editor.node.isVoid(n_node)))) {
                    txt = '';
                }
            }
            if (((!p_node && editor.node.isBlock(n_node)) || (!n_node && editor.node.isBlock(p_node))) && editor.node.isBlock(parent_node) && parent_node !== editor.el) {
                txt = txt.replace(/^[\f\n\r\t\v ]{1,}/g, '');
            }
            if (!browser_way) {
                txt = txt.replace(new RegExp($.FE.UNICODE_NBSP, 'g'), ' ');
            }
            var new_text = '';
            for (var t = 0; t < txt.length; t++) {
                if (txt.charCodeAt(t) == 32 && (t === 0 || new_text.charCodeAt(t - 1) == 32) && !((p_node && n_node && editor.node.isVoid(p_node)) || (p_node && n_node && editor.node.isVoid(n_node)))) {
                    new_text += $.FE.UNICODE_NBSP;
                } else {
                    new_text += txt[t];
                }
            }
            if (!n_node || (n_node && editor.node.isBlock(n_node)) || (n_node && n_node.nodeType == Node.ELEMENT_NODE && editor.win.getComputedStyle(n_node) && editor.win.getComputedStyle(n_node).display == 'block')) {
                if (!editor.node.isVoid(p_node)) {
                    new_text = new_text.replace(/ $/, $.FE.UNICODE_NBSP);
                }
            }
            if (p_node && !editor.node.isVoid(p_node) && !editor.node.isBlock(p_node)) {
                new_text = new_text.replace(/^\u00A0([^ $])/, ' $1');
                if (new_text.length === 1 && new_text.charCodeAt(0) === 160 && n_node && !editor.node.isVoid(n_node) && !editor.node.isBlock(n_node)) {
                    if (!(editor.node.hasClass(p_node, 'fr-marker') && editor.node.hasClass(n_node, 'fr-marker'))) {
                        new_text = ' ';
                    }
                }
            }
            if (!browser_way) {
                new_text = new_text.replace(/([^ \u00A0])\u00A0([^ \u00A0])/g, '$1 $2');
            }
            if (node.textContent != new_text) {
                node.textContent = new_text;
            }
        }

        function normalize(el, browser_way) {
            if (typeof el == 'undefined' || !el) el = editor.el;
            if (typeof browser_way == 'undefined') browser_way = false;
            if (el.getAttribute && el.getAttribute('contenteditable') == 'false') return;
            if (el.nodeType == Node.TEXT_NODE) {
                _normalizeNode(el, browser_way)
            } else if (el.nodeType == Node.ELEMENT_NODE) {
                var walker = editor.doc.createTreeWalker(el, NodeFilter.SHOW_TEXT, editor.node.filter(function (node) {
                    var temp_node = node.parentNode;
                    while (temp_node && temp_node !== editor.el) {
                        if (temp_node.tagName == 'STYLE' || temp_node.tagName == 'IFRAME') {
                            return false;
                        }
                        if (temp_node.tagName !== 'PRE') {
                            temp_node = temp_node.parentNode;
                        } else {
                            return false;
                        }
                    }
                    return node.textContent.match(/([ \u00A0\f\n\r\t\v]{2,})|(^[ \u00A0\f\n\r\t\v]{1,})|([ \u00A0\f\n\r\t\v]{1,}$)/g) != null && !editor.node.hasClass(node.parentNode, 'fr-marker');
                }), false);
                while (walker.nextNode()) {
                    _normalizeNode(walker.currentNode, browser_way);
                }
            }
        }

        function normalizeAroundCursor() {
            var nodes = [];
            var markers = editor.el.querySelectorAll('.fr-marker');
            for (var i = 0; i < markers.length; i++) {
                var node = null;
                var p_node = editor.node.blockParent(markers[i]);
                if (p_node) {
                    node = p_node;
                } else {
                    node = markers[i];
                }
                var next_node = node.nextSibling;
                var prev_node = node.previousSibling;
                while (next_node && next_node.tagName == 'BR') next_node = next_node.nextSibling;
                while (prev_node && prev_node.tagName == 'BR') prev_node = prev_node.previousSibling;
                if (node && nodes.indexOf(node) < 0) nodes.push(node);
                if (prev_node && nodes.indexOf(prev_node) < 0) nodes.push(prev_node);
                if (next_node && nodes.indexOf(next_node) < 0) nodes.push(next_node);
            }
            for (var j = 0; j < nodes.length; j++) {
                normalize(nodes[j]);
            }
        }

        return {normalize: normalize, normalizeAroundCursor: normalizeAroundCursor}
    };
    $.FE.UNICODE_NBSP = String.fromCharCode(160);
    $.FE.VOID_ELEMENTS = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'menuitem', 'meta', 'param', 'source', 'track', 'wbr'];
    $.FE.BLOCK_TAGS = ['address', 'article', 'aside', 'audio', 'blockquote', 'canvas', 'details', 'dd', 'div', 'dl', 'dt', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'hr', 'li', 'main', 'nav', 'noscript', 'ol', 'output', 'p', 'pre', 'section', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'ul', 'video'];
    $.extend($.FE.DEFAULTS, {
        htmlAllowedEmptyTags: ['textarea', 'a', 'iframe', 'object', 'video', 'style', 'script', '.fa', '.fr-emoticon', '.fr-inner', 'path', 'line'],
        htmlDoNotWrapTags: ['script', 'style'],
        htmlSimpleAmpersand: false,
        htmlIgnoreCSSProperties: [],
        htmlExecuteScripts: true
    });
    $.FE.MODULES.html = function (editor) {
        function defaultTag() {
            if (editor.opts.enter == $.FE.ENTER_P) return 'p';
            if (editor.opts.enter == $.FE.ENTER_DIV) return 'div';
            if (editor.opts.enter == $.FE.ENTER_BR) return null;
        }

        function isPreformatted(node, look_up) {
            if (!node || node === editor.el) return false;
            if (!look_up) {
                return ['PRE', 'SCRIPT', 'STYLE'].indexOf(node.tagName) != -1;
            } else {
                if (['PRE', 'SCRIPT', 'STYLE'].indexOf(node.tagName) != -1) {
                    return true;
                } else {
                    return isPreformatted(node.parentNode, look_up);
                }
            }
        }

        function emptyBlocks(around_markers) {
            var empty_blocks = [];
            var i;
            var els = [];
            if (around_markers) {
                var markers = editor.el.querySelectorAll('.fr-marker');
                for (i = 0; i < markers.length; i++) {
                    var p_node = editor.node.blockParent(markers[i]) || markers[i];
                    if (p_node) {
                        var next_node = p_node.nextSibling;
                        var prev_node = p_node.previousSibling;
                        if (p_node && els.indexOf(p_node) < 0 && editor.node.isBlock(p_node)) els.push(p_node);
                        if (prev_node && editor.node.isBlock(prev_node) && els.indexOf(prev_node) < 0) els.push(prev_node);
                        if (next_node && editor.node.isBlock(next_node) && els.indexOf(next_node) < 0) els.push(next_node);
                    }
                }
            } else {
                els = editor.el.querySelectorAll(blockTagsQuery());
            }
            var qr = blockTagsQuery();
            qr += ',' + $.FE.VOID_ELEMENTS.join(',');
            qr += ', .fr-inner', qr += ',' + editor.opts.htmlAllowedEmptyTags.join(':not(.fr-marker),') + ':not(.fr-marker)';
            for (i = els.length - 1; i >= 0; i--) {
                if (els[i].textContent && els[i].textContent.replace(/\u200B|\n/g, '').length > 0) continue;
                if (els[i].querySelectorAll(qr).length > 0) continue;
                var contents = editor.node.contents(els[i]);
                var found = false;
                for (var j = 0; j < contents.length; j++) {
                    if (contents[j].nodeType == Node.COMMENT_NODE) continue;
                    if (contents[j].textContent && contents[j].textContent.replace(/\u200B|\n/g, '').length > 0) {
                        found = true;
                        break;
                    }
                }
                if (!found) empty_blocks.push(els[i]);
            }
            return empty_blocks;
        }

        function emptyBlockTagsQuery() {
            return $.FE.BLOCK_TAGS.join(':empty, ') + ':empty';
        }

        function blockTagsQuery() {
            return $.FE.BLOCK_TAGS.join(', ');
        }

        function cleanEmptyTags(remove_blocks) {
            var els = $.merge([], $.FE.VOID_ELEMENTS);
            els = $.merge(els, editor.opts.htmlAllowedEmptyTags);
            if (typeof remove_blocks == 'undefined') {
                els = $.merge(els, $.FE.BLOCK_TAGS);
            } else {
                els = $.merge(els, $.FE.NO_DELETE_TAGS);
            }
            var elms;
            var ok;
            elms = editor.el.querySelectorAll('*:empty:not(' + els.join('):not(') + '):not(.fr-marker)');
            do {
                ok = false;
                for (var i = 0; i < elms.length; i++) {
                    if (elms[i].attributes.length === 0 || typeof elms[i].getAttribute('href') !== 'undefined') {
                        elms[i].parentNode.removeChild(elms[i]);
                        ok = true;
                    }
                }
                elms = editor.el.querySelectorAll('*:empty:not(' + els.join('):not(') + '):not(.fr-marker)');
            } while (elms.length && ok);
        }

        function _wrapElement(el, temp) {
            var default_tag = defaultTag();
            if (temp) default_tag = 'div';
            if (default_tag) {
                var main_doc = editor.doc.createDocumentFragment();
                var anchor = null;
                var found = false;
                var node = el.firstChild;
                var changed = false;
                while (node) {
                    var next_node = node.nextSibling;
                    if (node.nodeType == Node.ELEMENT_NODE && (editor.node.isBlock(node) || (editor.opts.htmlDoNotWrapTags.indexOf(node.tagName.toLowerCase()) >= 0 && !editor.node.hasClass(node, 'fr-marker')))) {
                        anchor = null;
                        main_doc.appendChild(node.cloneNode(true));
                    } else if (node.nodeType != Node.ELEMENT_NODE && node.nodeType != Node.TEXT_NODE) {
                        anchor = null;
                        main_doc.appendChild(node.cloneNode(true));
                    } else if (node.tagName == 'BR') {
                        if (anchor == null) {
                            anchor = editor.doc.createElement(default_tag);
                            changed = true;
                            if (temp) {
                                anchor.setAttribute('class', 'fr-temp-div');
                                anchor.setAttribute('data-empty', true);
                            }
                            anchor.appendChild(node.cloneNode(true));
                            main_doc.appendChild(anchor);
                        } else {
                            if (found === false) {
                                anchor.appendChild(editor.doc.createElement('br'));
                                if (temp) {
                                    anchor.setAttribute('class', 'fr-temp-div');
                                    anchor.setAttribute('data-empty', true);
                                }
                            }
                        }
                        anchor = null;
                    } else {
                        var txt = node.textContent;
                        if (node.nodeType !== Node.TEXT_NODE || (txt.replace(/\n/g, '').replace(/(^ *)|( *$)/g, '').length > 0 || (txt.replace(/(^ *)|( *$)/g, '').length && txt.indexOf('\n') < 0))) {
                            if (anchor == null) {
                                anchor = editor.doc.createElement(default_tag);
                                changed = true;
                                if (temp) anchor.setAttribute('class', 'fr-temp-div');
                                main_doc.appendChild(anchor);
                                found = false;
                            }
                            anchor.appendChild(node.cloneNode(true));
                            if (!found && (!editor.node.hasClass(node, 'fr-marker') && !(node.nodeType == Node.TEXT_NODE && txt.replace(/ /g, '').length === 0))) {
                                found = true;
                            }
                        } else {
                            changed = true;
                        }
                    }
                    node = next_node;
                }
                if (changed) {
                    el.innerHTML = '';
                    el.appendChild(main_doc);
                }
            }
        }

        function _wrapElements(els, temp) {
            for (var i = els.length - 1; i >= 0; i--) {
                _wrapElement(els[i], temp);
            }
        }

        function _wrap(temp, tables, blockquote, inner, li) {
            if (!editor.$wp) return false;
            if (typeof temp == 'undefined') temp = false;
            if (typeof tables == 'undefined') tables = false;
            if (typeof blockquote == 'undefined') blockquote = false;
            if (typeof inner == 'undefined') inner = false;
            if (typeof li == 'undefined') li = false;
            var wp_st = editor.$wp.scrollTop();
            _wrapElement(editor.el, temp);
            if (inner) {
                _wrapElements(editor.el.querySelectorAll('.fr-inner'), temp);
            }
            if (tables) {
                _wrapElements(editor.el.querySelectorAll('td, th'), temp);
            }
            if (blockquote) {
                _wrapElements(editor.el.querySelectorAll('blockquote'), temp);
            }
            if (li) {
                _wrapElements(editor.el.querySelectorAll('li'), temp);
            }
            if (wp_st != editor.$wp.scrollTop()) {
                editor.$wp.scrollTop(wp_st);
            }
        }

        function unwrap() {
            editor.$el.find('div.fr-temp-div').each(function () {
                if (this.previousSibling && this.previousSibling.nodeType === Node.TEXT_NODE) {
                    $(this).before('<br>');
                }
                if ($(this).attr('data-empty') || !this.nextSibling || (editor.node.isBlock(this.nextSibling) && !$(this.nextSibling).hasClass('fr-temp-div'))) {
                    $(this).replaceWith($(this).html());
                } else {
                    $(this).replaceWith($(this).html() + '<br>');
                }
            });
            editor.$el.find('.fr-temp-div').removeClass('fr-temp-div').filter(function () {
                return $(this).attr('class') === '';
            }).removeAttr('class');
        }

        function fillEmptyBlocks(around_markers) {
            var blocks = emptyBlocks(around_markers);
            if (editor.node.isEmpty(editor.el) && editor.opts.enter === $.FE.ENTER_BR) {
                blocks.push(editor.el)
            }
            for (var i = 0; i < blocks.length; i++) {
                var block = blocks[i];
                if (block.getAttribute('contenteditable') !== 'false' && !block.querySelector(editor.opts.htmlAllowedEmptyTags.join(':not(.fr-marker),') + ':not(.fr-marker)') && !editor.node.isVoid(block)) {
                    if (block.tagName != 'TABLE' && block.tagName != 'TBODY' && block.tagName != 'TR' && block.tagName != 'UL' && block.tagName != 'OL') block.appendChild(editor.doc.createElement('br'));
                }
            }
            if (editor.browser.msie && editor.opts.enter == $.FE.ENTER_BR) {
                var contents = editor.node.contents(editor.el);
                if (contents.length && contents[contents.length - 1].nodeType == Node.TEXT_NODE) {
                    editor.$el.append('<br>');
                }
            }
        }

        function blocks() {
            return editor.$el.get(0).querySelectorAll(blockTagsQuery());
        }

        function cleanBlankSpaces(el) {
            if (typeof el == 'undefined') el = editor.el;
            if (el && ['SCRIPT', 'STYLE', 'PRE'].indexOf(el.tagName) >= 0) return false;
            var walker = editor.doc.createTreeWalker(el, NodeFilter.SHOW_TEXT, editor.node.filter(function (node) {
                return node.textContent.match(/([ \n]{2,})|(^[ \n]{1,})|([ \n]{1,}$)/g) != null;
            }), false);
            while (walker.nextNode()) {
                var node = walker.currentNode;
                if (isPreformatted(node.parentNode, true)) continue;
                var is_block_or_element = editor.node.isBlock(node.parentNode) || editor.node.isElement(node.parentNode);
                var txt = node.textContent.replace(/(?!^)( ){2,}(?!$)/g, ' ').replace(/\n/g, ' ').replace(/^[ ]{2,}/g, ' ').replace(/[ ]{2,}$/g, ' ');
                if (is_block_or_element) {
                    var p_node = node.previousSibling;
                    var n_node = node.nextSibling;
                    if (p_node && n_node && txt == ' ') {
                        if (editor.node.isBlock(p_node) && editor.node.isBlock(n_node)) {
                            txt = '';
                        } else {
                            txt = ' ';
                        }
                    } else {
                        if (!p_node) txt = txt.replace(/^ */, '');
                        if (!n_node) txt = txt.replace(/ *$/, '');
                    }
                }
                node.textContent = txt;
            }
        }

        function _extractMatch(html, re, id) {
            var reg_exp = new RegExp(re, 'gi');
            var matches = reg_exp.exec(html);
            if (matches) {
                return matches[id];
            }
            return null;
        }

        function _newDoctype(string, doc) {
            var matches = string.match(/<!DOCTYPE ?([^ ]*) ?([^ ]*) ?"?([^"]*)"? ?"?([^"]*)"?>/i);
            if (matches) {
                return doc.implementation.createDocumentType(matches[1], matches[3], matches[4])
            } else {
                return doc.implementation.createDocumentType('html');
            }
        }

        function getDoctype(doc) {
            var node = doc.doctype;
            var doctype = '<!DOCTYPE html>';
            if (node) {
                doctype = '<!DOCTYPE '
                    + node.name
                    + (node.publicId ? ' PUBLIC "' + node.publicId + '"' : '')
                    + (!node.publicId && node.systemId ? ' SYSTEM' : '')
                    + (node.systemId ? ' "' + node.systemId + '"' : '')
                    + '>';
            }
            return doctype;
        }

        function _processBR(br) {
            var parent_node = br.parentNode;
            if (parent_node && (editor.node.isBlock(parent_node) || editor.node.isElement(parent_node)) && ['TD', 'TH'].indexOf(parent_node.tagName) < 0) {
                var prev_node = br.previousSibling;
                var next_node = br.nextSibling;
                while (prev_node && ((prev_node.nodeType == Node.TEXT_NODE && prev_node.textContent.replace(/\n|\r/g, '').length === 0) || editor.node.hasClass(prev_node, 'fr-tmp'))) {
                    prev_node = prev_node.previousSibling;
                }
                if (next_node) return false;
                if (prev_node && parent_node && prev_node.tagName != 'BR' && !editor.node.isBlock(prev_node) && !next_node && parent_node.textContent.replace(/\u200B/g, '').length > 0 && prev_node.textContent.length > 0 && !editor.node.hasClass(prev_node, 'fr-marker')) {
                    if (!(editor.el == parent_node && !next_node && editor.opts.enter == $.FE.ENTER_BR && editor.browser.msie)) {
                        br.parentNode.removeChild(br);
                    }
                }
            } else if (parent_node && !(editor.node.isBlock(parent_node) || editor.node.isElement(parent_node))) {
                if (!br.previousSibling && !br.nextSibling && editor.node.isDeletable(br.parentNode)) {
                    _processBR(br.parentNode);
                }
            }
        }

        function cleanBRs() {
            var brs = editor.el.getElementsByTagName('br');
            for (var i = 0; i < brs.length; i++) {
                _processBR(brs[i]);
            }
        }

        function _normalize() {
            if (!editor.opts.htmlUntouched) {
                cleanEmptyTags();
                _wrap();
                cleanBlankSpaces();
                editor.spaces.normalize(null, true);
                editor.html.fillEmptyBlocks();
                editor.clean.lists();
                editor.clean.tables();
                editor.clean.toHTML5();
                editor.html.cleanBRs();
            }
            editor.selection.restore();
            checkIfEmpty();
            editor.placeholder.refresh();
        }

        function checkIfEmpty() {
            if (editor.node.isEmpty(editor.el)) {
                if (defaultTag() != null) {
                    if (!editor.el.querySelector(blockTagsQuery()) && !editor.el.querySelector(editor.opts.htmlDoNotWrapTags.join(':not(.fr-marker),') + ':not(.fr-marker)')) {
                        if (editor.core.hasFocus()) {
                            editor.$el.html('<' + defaultTag() + '>' + $.FE.MARKERS + '<br/></' + defaultTag() + '>');
                            editor.selection.restore();
                        } else {
                            editor.$el.html('<' + defaultTag() + '>' + '<br/></' + defaultTag() + '>');
                        }
                    }
                } else {
                    if (!editor.el.querySelector('*:not(.fr-marker):not(br)')) {
                        if (editor.core.hasFocus()) {
                            editor.$el.html($.FE.MARKERS + '<br/>');
                            editor.selection.restore();
                        } else {
                            editor.$el.html('<br/>');
                        }
                    }
                }
            }
        }

        function extractNode(html, tag) {
            return _extractMatch(html, '<' + tag + '[^>]*?>([\\w\\W]*)<\/' + tag + '>', 1);
        }

        function extractNodeAttrs(html, tag) {
            var $dv = $('<div ' + (_extractMatch(html, '<' + tag + '([^>]*?)>', 1) || '') + '>');
            return editor.node.rawAttributes($dv.get(0));
        }

        function extractDoctype(html) {
            return (_extractMatch(html, '<!DOCTYPE([^>]*?)>', 0) || '<!DOCTYPE html>').replace(/\n/g, ' ').replace(/ {2,}/g, ' ');
        }

        function _setHtml($node, html) {
            if (editor.opts.htmlExecuteScripts) {
                $node.html(html);
            } else {
                $node.get(0).innerHTML = html;
            }
        }

        function set(html) {
            var clean_html = editor.clean.html((html || '').trim(), [], [], editor.opts.fullPage);
            if (!editor.opts.fullPage) {
                _setHtml(editor.$el, clean_html);
            } else {
                var body_html = (extractNode(clean_html, 'body') || (clean_html.indexOf('<body') >= 0 ? '' : clean_html));
                var body_attrs = extractNodeAttrs(clean_html, 'body');
                var head_html = extractNode(clean_html, 'head') || '<title></title>';
                var head_attrs = extractNodeAttrs(clean_html, 'head');
                var head_bad_html = $('<div>').append(head_html).contents().each(function () {
                    if (this.nodeType == Node.COMMENT_NODE || ['BASE', 'LINK', 'META', 'NOSCRIPT', 'SCRIPT', 'STYLE', 'TEMPLATE', 'TITLE'].indexOf(this.tagName) >= 0) {
                        this.parentNode.removeChild(this);
                    }
                }).end().html().trim();
                head_html = $('<div>').append(head_html).contents().map(function () {
                    if (this.nodeType == Node.COMMENT_NODE) {
                        return '<!--' + this.nodeValue + '-->';
                    } else if (['BASE', 'LINK', 'META', 'NOSCRIPT', 'SCRIPT', 'STYLE', 'TEMPLATE', 'TITLE'].indexOf(this.tagName) >= 0) {
                        return this.outerHTML;
                    } else {
                        return '';
                    }
                }).toArray().join('');
                var doctype = extractDoctype(clean_html);
                var html_attrs = extractNodeAttrs(clean_html, 'html');
                _setHtml(editor.$el, head_bad_html + '\n' + body_html);
                editor.node.clearAttributes(editor.el);
                editor.$el.attr(body_attrs);
                editor.$el.addClass('fr-view');
                editor.$el.attr('spellcheck', editor.opts.spellcheck);
                editor.$el.attr('dir', editor.opts.direction);
                _setHtml(editor.$head, head_html);
                editor.node.clearAttributes(editor.$head.get(0));
                editor.$head.attr(head_attrs);
                editor.node.clearAttributes(editor.$html.get(0));
                editor.$html.attr(html_attrs);
                editor.iframe_document.doctype.parentNode.replaceChild(_newDoctype(doctype, editor.iframe_document), editor.iframe_document.doctype);
            }
            var disabled = editor.edit.isDisabled();
            editor.edit.on();
            editor.core.injectStyle(editor.opts.iframeDefaultStyle + editor.opts.iframeStyle);
            _normalize();
            if (!editor.opts.useClasses) {
                editor.$el.find('[fr-original-class]').each(function () {
                    this.setAttribute('class', this.getAttribute('fr-original-class'));
                    this.removeAttribute('fr-original-class');
                });
                editor.$el.find('[fr-original-style]').each(function () {
                    this.setAttribute('style', this.getAttribute('fr-original-style'));
                    this.removeAttribute('fr-original-style');
                });
            }
            if (disabled) editor.edit.off();
            editor.events.trigger('html.set');
        }

        function _specifity(selector) {
            var idRegex = /(#[^\s\+>~\.\[:]+)/g;
            var attributeRegex = /(\[[^\]]+\])/g;
            var classRegex = /(\.[^\s\+>~\.\[:]+)/g;
            var pseudoElementRegex = /(::[^\s\+>~\.\[:]+|:first-line|:first-letter|:before|:after)/gi;
            var pseudoClassWithBracketsRegex = /(:[\w-]+\([^\)]*\))/gi;
            var pseudoClassRegex = /(:[^\s\+>~\.\[:]+)/g;
            var elementRegex = /([^\s\+>~\.\[:]+)/g;
            (function () {
                var regex = /:not\(([^\)]*)\)/g;
                if (regex.test(selector)) {
                    selector = selector.replace(regex, '     $1 ');
                }
            }());
            var s = (selector.match(idRegex) || []).length * 100 +
                (selector.match(attributeRegex) || []).length * 10 +
                (selector.match(classRegex) || []).length * 10 +
                (selector.match(pseudoClassWithBracketsRegex) || []).length * 10 +
                (selector.match(pseudoClassRegex) || []).length * 10 +
                (selector.match(pseudoElementRegex) || []).length;
            selector = selector.replace(/[\*\s\+>~]/g, ' ');
            selector = selector.replace(/[#\.]/g, ' ');
            s += (selector.match(elementRegex) || []).length;
            return s;
        }

        function _processOnGet(el) {
            editor.events.trigger('html.processGet', [el]);
            if (el && el.getAttribute && el.getAttribute('class') === '') {
                el.removeAttribute('class');
            }
            if (el && el.getAttribute && el.getAttribute('style') === '') {
                el.removeAttribute('style');
            }
            if (el && el.nodeType == Node.ELEMENT_NODE) {
                var els = el.querySelectorAll('[class=""],[style=""]');
                var i;
                for (i = 0; i < els.length; i++) {
                    var _el = els[i];
                    if (_el.getAttribute('class') === '') {
                        _el.removeAttribute('class');
                    }
                    if (_el.getAttribute('style') === '') {
                        _el.removeAttribute('style');
                    }
                }
                if (el.tagName === 'BR') {
                    _processBR(el);
                } else {
                    var brs = el.querySelectorAll('br');
                    for (i = 0; i < brs.length; i++) {
                        _processBR(brs[i]);
                    }
                }
            }
        }

        function _sortElementsBySpec(a, b) {
            return a[3] - b[3];
        }

        function get(keep_markers, keep_classes) {
            if (!editor.$wp) {
                return editor.$oel.clone().removeClass('fr-view').removeAttr('contenteditable').get(0).outerHTML;
            }
            var html = '';
            editor.events.trigger('html.beforeGet');
            var updated_elms = [];
            var elms_info = {};
            var i;
            var j;
            var elems_specs = [];
            var inputs = editor.el.querySelectorAll('input, textarea');
            for (i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('value', inputs[i].value);
            }
            if (!editor.opts.useClasses && !keep_classes) {
                var ignoreRegEx = new RegExp('^' + editor.opts.htmlIgnoreCSSProperties.join('$|^') + '$', 'gi')
                for (i = 0; i < editor.doc.styleSheets.length; i++) {
                    var rules;
                    var head_style = 0;
                    try {
                        rules = editor.doc.styleSheets[i].cssRules;
                        if (editor.doc.styleSheets[i].ownerNode && editor.doc.styleSheets[i].ownerNode.nodeType == 'STYLE') {
                            head_style = 1;
                        }
                    } catch (ex) {
                    }
                    if (rules) {
                        for (var idx = 0, len = rules.length; idx < len; idx++) {
                            if (rules[idx].selectorText) {
                                if (rules[idx].style.cssText.length > 0) {
                                    var selector = rules[idx].selectorText.replace(/body |\.fr-view /g, '').replace(/::/g, ':');
                                    var elms;
                                    try {
                                        elms = editor.el.querySelectorAll(selector);
                                    } catch (ex) {
                                        elms = [];
                                    }
                                    for (j = 0; j < elms.length; j++) {
                                        if (!elms[j].getAttribute('fr-original-style') && elms[j].getAttribute('style')) {
                                            elms[j].setAttribute('fr-original-style', elms[j].getAttribute('style'));
                                            updated_elms.push(elms[j]);
                                        } else if (!elms[j].getAttribute('fr-original-style')) {
                                            elms[j].setAttribute('fr-original-style', '');
                                            updated_elms.push(elms[j]);
                                        }
                                        if (!elms_info[elms[j]]) {
                                            elms_info[elms[j]] = {};
                                        }
                                        var spec = head_style * 1000 + _specifity(rules[idx].selectorText);
                                        var css_text = rules[idx].style.cssText.split(';');
                                        for (var k = 0; k < css_text.length; k++) {
                                            var rule = css_text[k].trim().split(':')[0];
                                            if (!rule) continue;
                                            if (rule.match(ignoreRegEx)) continue;
                                            if (!elms_info[elms[j]][rule]) {
                                                elms_info[elms[j]][rule] = 0;
                                                if ((elms[j].getAttribute('fr-original-style') || '').indexOf(rule + ':') >= 0) {
                                                    elms_info[elms[j]][rule] = 10000;
                                                }
                                            }
                                            if (spec >= elms_info[elms[j]][rule]) {
                                                elms_info[elms[j]][rule] = spec;
                                                if (css_text[k].trim().length) {
                                                    var info = css_text[k].trim().split(':');
                                                    info.splice(0, 1);
                                                    elems_specs.push([elms[j], rule.trim(), info.join(':').trim(), spec])
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                elems_specs.sort(_sortElementsBySpec);
                for (i = 0; i < elems_specs.length; i++) {
                    var specs_elem = elems_specs[i];
                    specs_elem[0].style[specs_elem[1]] = specs_elem[2];
                }
                for (i = 0; i < updated_elms.length; i++) {
                    if (updated_elms[i].getAttribute('class')) {
                        updated_elms[i].setAttribute('fr-original-class', updated_elms[i].getAttribute('class'));
                        updated_elms[i].removeAttribute('class');
                    }
                    if ((updated_elms[i].getAttribute('fr-original-style') || '').trim().length > 0) {
                        var original_rules = updated_elms[i].getAttribute('fr-original-style').split(';');
                        for (j = 0; j < original_rules.length; j++) {
                            if (original_rules[j].indexOf(':') > 0) {
                                var splits = original_rules[j].split(':');
                                var original_rule = splits[0];
                                splits.splice(0, 1);
                                updated_elms[i].style[original_rule.trim()] = splits.join(':').trim();
                            }
                        }
                    }
                }
            }
            if (!editor.node.isEmpty(editor.el)) {
                if (typeof keep_markers == 'undefined') keep_markers = false;
                if (!editor.opts.fullPage) {
                    html = editor.$el.html();
                } else {
                    html = getDoctype(editor.iframe_document);
                    editor.$el.removeClass('fr-view');
                    var heightMin = editor.opts.heightMin;
                    var height = editor.opts.height;
                    var heightMax = editor.opts.heightMax;
                    editor.opts.heightMin = null;
                    editor.opts.height = null;
                    editor.opts.heightMax = null;
                    editor.size.refresh();
                    html += '<html' + editor.node.attributes(editor.$html.get(0)) + '>' + editor.$html.html() + '</html>';
                    editor.opts.heightMin = heightMin;
                    editor.opts.height = height;
                    editor.opts.heightMax = heightMax;
                    editor.size.refresh();
                    editor.$el.addClass('fr-view');
                }
            } else if (editor.opts.fullPage) {
                html = getDoctype(editor.iframe_document);
                html += '<html' + editor.node.attributes(editor.$html.get(0)) + '>' + editor.$html.find('head').get(0).outerHTML + '<body></body></html>';
            }
            if (!editor.opts.useClasses && !keep_classes) {
                for (i = 0; i < updated_elms.length; i++) {
                    if (updated_elms[i].getAttribute('fr-original-class')) {
                        updated_elms[i].setAttribute('class', updated_elms[i].getAttribute('fr-original-class'));
                        updated_elms[i].removeAttribute('fr-original-class');
                    }
                    if (updated_elms[i].getAttribute('fr-original-style') != null && typeof updated_elms[i].getAttribute('fr-original-style') != 'undefined') {
                        if (updated_elms[i].getAttribute('fr-original-style').length !== 0) {
                            updated_elms[i].setAttribute('style', updated_elms[i].getAttribute('fr-original-style'));
                        } else {
                            updated_elms[i].removeAttribute('style');
                        }
                        updated_elms[i].removeAttribute('fr-original-style');
                    } else {
                        updated_elms[i].removeAttribute('style');
                    }
                }
            }
            if (editor.opts.fullPage) {
                html = html.replace(/<style data-fr-style="true">(?:[\w\W]*?)<\/style>/g, '');
                html = html.replace(/<link([^>]*)data-fr-style="true"([^>]*)>/g, '');
                html = html.replace(/<style(?:[\w\W]*?)class="firebugResetStyles"(?:[\w\W]*?)>(?:[\w\W]*?)<\/style>/g, '');
                html = html.replace(/<body((?:[\w\W]*?)) spellcheck="true"((?:[\w\W]*?))>((?:[\w\W]*?))<\/body>/g, '<body$1$2>$3</body>');
                html = html.replace(/<body((?:[\w\W]*?)) contenteditable="(true|false)"((?:[\w\W]*?))>((?:[\w\W]*?))<\/body>/g, '<body$1$3>$4</body>');
                html = html.replace(/<body((?:[\w\W]*?)) dir="([\w]*)"((?:[\w\W]*?))>((?:[\w\W]*?))<\/body>/g, '<body$1$3>$4</body>');
                html = html.replace(/<body((?:[\w\W]*?))class="([\w\W]*?)(fr-rtl|fr-ltr)([\w\W]*?)"((?:[\w\W]*?))>((?:[\w\W]*?))<\/body>/g, '<body$1class="$2$4"$5>$6</body>');
                html = html.replace(/<body((?:[\w\W]*?)) class=""((?:[\w\W]*?))>((?:[\w\W]*?))<\/body>/g, '<body$1$2>$3</body>');
            }
            if (editor.opts.htmlSimpleAmpersand) {
                html = html.replace(/\&amp;/gi, '&');
            }
            editor.events.trigger('html.afterGet');
            if (!keep_markers) {
                html = html.replace(/<span[^>]*? class\s*=\s*["']?fr-marker["']?[^>]+>\u200b<\/span>/gi, '');
            }
            html = editor.clean.invisibleSpaces(html);
            html = editor.clean.exec(html, _processOnGet);
            var new_html = editor.events.chainTrigger('html.get', html);
            if (typeof new_html == 'string') {
                html = new_html;
            }
            html = html.replace(/<pre(?:[\w\W]*?)>(?:[\w\W]*?)<\/pre>/g, function (str) {
                return str.replace(/<br>/g, '\n');
            });
            html = html.replace(/<meta((?:[\w\W]*?)) data-fr-http-equiv="/g, '<meta$1 http-equiv="');
            return html;
        }

        function getSelected() {
            var wrapSelection = function (container, node) {
                while (node && (node.nodeType == Node.TEXT_NODE || !editor.node.isBlock(node)) && !editor.node.isElement(node) && !editor.node.hasClass(node, 'fr-inner')) {
                    if (node && node.nodeType != Node.TEXT_NODE) {
                        $(container).wrapInner(editor.node.openTagString(node) + editor.node.closeTagString(node));
                    }
                    node = node.parentNode;
                }
                if (node && container.innerHTML == node.innerHTML) {
                    container.innerHTML = node.outerHTML;
                }
            }
            var selectionParent = function () {
                var parent = null;
                var sel;
                if (editor.win.getSelection) {
                    sel = editor.win.getSelection();
                    if (sel && sel.rangeCount) {
                        parent = sel.getRangeAt(0).commonAncestorContainer;
                        if (parent.nodeType != Node.ELEMENT_NODE) {
                            parent = parent.parentNode;
                        }
                    }
                } else if ((sel = editor.doc.selection) && sel.type != 'Control') {
                    parent = sel.createRange().parentElement();
                }
                if (parent != null && ($.inArray(editor.el, $(parent).parents()) >= 0 || parent == editor.el)) {
                    return parent;
                } else {
                    return null;
                }
            }
            var html = '';
            if (typeof editor.win.getSelection != 'undefined') {
                if (editor.browser.mozilla) {
                    editor.selection.save();
                    if (editor.$el.find('.fr-marker[data-type="false"]').length > 1) {
                        editor.$el.find('.fr-marker[data-type="false"][data-id="0"]').remove();
                        editor.$el.find('.fr-marker[data-type="false"]:last').attr('data-id', '0');
                        editor.$el.find('.fr-marker').not('[data-id="0"]').remove();
                    }
                    editor.selection.restore();
                }
                var ranges = editor.selection.ranges();
                for (var i = 0; i < ranges.length; i++) {
                    var container = document.createElement('div');
                    container.appendChild(ranges[i].cloneContents());
                    wrapSelection(container, selectionParent());
                    if ($(container).find('.fr-element').length > 0) {
                        container = editor.el;
                    }
                    html += container.innerHTML;
                }
            } else if (typeof editor.doc.selection != 'undefined') {
                if (editor.doc.selection.type == 'Text') {
                    html = editor.doc.selection.createRange().htmlText;
                }
            }
            return html;
        }

        function _hasBlockTags(html) {
            var tmp = editor.doc.createElement('div');
            tmp.innerHTML = html;
            return tmp.querySelector(blockTagsQuery()) !== null;
        }

        function _setCursorAtEnd(html) {
            var tmp = editor.doc.createElement('div');
            tmp.innerHTML = html;
            editor.selection.setAtEnd(tmp, true);
            return tmp.innerHTML;
        }

        function escapeEntities(str) {
            return str.replace(/</gi, '&lt;').replace(/>/gi, '&gt;').replace(/"/gi, '&quot;').replace(/'/gi, '&#39;')
        }

        function _unwrapForLists(html) {
            if (!editor.html.defaultTag()) return html;
            var tmp = editor.doc.createElement('div');
            tmp.innerHTML = html;
            var default_tag_els = tmp.querySelectorAll(':scope > ' + editor.html.defaultTag());
            for (var i = default_tag_els.length - 1; i >= 0; i--) {
                var el = default_tag_els[i];
                if (!editor.node.isBlock(el.previousSibling)) {
                    if (el.previousSibling && !editor.node.isEmpty(el)) {
                        $('<br>').insertAfter(el.previousSibling);
                    }
                    el.outerHTML = el.innerHTML;
                }
            }
            return tmp.innerHTML;
        }

        function insert(dirty_html, clean, do_split) {
            if (!editor.selection.isCollapsed()) {
                editor.selection.remove();
            }
            var clean_html;
            if (!clean) {
                clean_html = editor.clean.html(dirty_html);
            } else {
                clean_html = dirty_html;
            }
            if (dirty_html.indexOf('class="fr-marker"') < 0) {
                clean_html = _setCursorAtEnd(clean_html);
            }
            if (editor.node.isEmpty(editor.el) && !editor.opts.keepFormatOnDelete && _hasBlockTags(clean_html)) {
                editor.el.innerHTML = clean_html;
            } else {
                var marker = editor.markers.insert();
                if (!marker) {
                    editor.el.innerHTML = editor.el.innerHTML + clean_html;
                } else {
                    if (editor.node.isLastSibling(marker) && $(marker).parent().hasClass('fr-deletable')) {
                        $(marker).insertAfter($(marker).parent());
                    }
                    var deep_parent;
                    var block_parent = editor.node.blockParent(marker);
                    if ((_hasBlockTags(clean_html) || do_split) && (deep_parent = editor.node.deepestParent(marker) || (block_parent && block_parent.tagName == 'LI'))) {
                        if (block_parent && block_parent.tagName == 'LI') {
                            clean_html = _unwrapForLists(clean_html);
                        }
                        marker = editor.markers.split();
                        if (!marker) return false;
                        marker.outerHTML = clean_html;
                    } else {
                        marker.outerHTML = clean_html;
                    }
                }
            }
            _normalize();
            editor.keys.positionCaret();
            editor.events.trigger('html.inserted');
        }

        function cleanWhiteTags(ignore_selection) {
            var current_el = null;
            if (typeof ignore_selection == 'undefined') {
                current_el = editor.selection.element();
            }
            if (editor.opts.keepFormatOnDelete) return false;
            var current_white = current_el ? (current_el.textContent.match(/\u200B/g) || []).length - current_el.querySelectorAll('.fr-marker').length : 0;
            var total_white = (editor.el.textContent.match(/\u200B/g) || []).length - editor.el.querySelectorAll('.fr-marker').length;
            if (total_white == current_white) return false;
            var possible_elements;
            var removed;
            do {
                removed = false;
                possible_elements = editor.el.querySelectorAll('*:not(.fr-marker)');
                for (var i = 0; i < possible_elements.length; i++) {
                    var el = possible_elements[i];
                    if (current_el == el) continue;
                    var text = el.textContent;
                    if (el.children.length === 0 && text.length === 1 && text.charCodeAt(0) == 8203 && el.tagName !== 'TD') {
                        $(el).remove();
                        removed = true;
                    }
                }
            } while (removed);
        }

        function _init() {
            if (editor.$wp) {
                var cleanTags = function () {
                    cleanWhiteTags();
                    if (editor.placeholder) {
                        setTimeout(editor.placeholder.refresh, 0);
                    }
                }
                editor.events.on('mouseup', cleanTags);
                editor.events.on('keydown', cleanTags);
                editor.events.on('contentChanged', checkIfEmpty);
            }
        }

        return {
            defaultTag: defaultTag,
            isPreformatted: isPreformatted,
            emptyBlocks: emptyBlocks,
            emptyBlockTagsQuery: emptyBlockTagsQuery,
            blockTagsQuery: blockTagsQuery,
            fillEmptyBlocks: fillEmptyBlocks,
            cleanEmptyTags: cleanEmptyTags,
            cleanWhiteTags: cleanWhiteTags,
            cleanBlankSpaces: cleanBlankSpaces,
            blocks: blocks,
            getDoctype: getDoctype,
            set: set,
            get: get,
            getSelected: getSelected,
            insert: insert,
            wrap: _wrap,
            unwrap: unwrap,
            escapeEntities: escapeEntities,
            checkIfEmpty: checkIfEmpty,
            extractNode: extractNode,
            extractNodeAttrs: extractNodeAttrs,
            extractDoctype: extractDoctype,
            cleanBRs: cleanBRs,
            _init: _init
        }
    }
    $.extend($.FE.DEFAULTS, {height: null, heightMax: null, heightMin: null, width: null});
    $.FE.MODULES.size = function (editor) {
        function syncIframe() {
            refresh();
            if (editor.opts.height) {
                editor.$el.css('minHeight', editor.opts.height - editor.helpers.getPX(editor.$el.css('padding-top')) - editor.helpers.getPX(editor.$el.css('padding-bottom')));
            }
            editor.$iframe.height(editor.$el.outerHeight(true));
        }

        function refresh() {
            if (editor.opts.heightMin) {
                editor.$el.css('minHeight', editor.opts.heightMin);
            } else {
                editor.$el.css('minHeight', '');
            }
            if (editor.opts.heightMax) {
                editor.$wp.css('maxHeight', editor.opts.heightMax);
                editor.$wp.css('overflow', 'auto');
            } else {
                editor.$wp.css('maxHeight', '');
                editor.$wp.css('overflow', '');
            }
            if (editor.opts.height) {
                editor.$wp.height(editor.opts.height);
                editor.$wp.css('overflow', 'auto');
                editor.$el.css('minHeight', editor.opts.height - editor.helpers.getPX(editor.$el.css('padding-top')) - editor.helpers.getPX(editor.$el.css('padding-bottom')));
            } else {
                editor.$wp.css('height', '');
                if (!editor.opts.heightMin) editor.$el.css('minHeight', '');
                if (!editor.opts.heightMax) editor.$wp.css('overflow', '');
            }
            if (editor.opts.width) editor.$box.width(editor.opts.width);
        }

        function _init() {
            if (!editor.$wp) return false;
            refresh();
            if (editor.$iframe) {
                editor.events.on('keyup keydown', function () {
                    setTimeout(syncIframe, 0)
                }, true);
                editor.events.on('commands.after html.set init initialized paste.after', syncIframe);
            }
        }

        return {_init: _init, syncIframe: syncIframe, refresh: refresh}
    };
    $.extend($.FE.DEFAULTS, {language: null});
    $.FE.LANGUAGE = {};
    $.FE.MODULES.language = function (editor) {
        var lang;

        function translate(str) {
            if (lang && lang.translation[str] && lang.translation[str].length) {
                return lang.translation[str];
            } else {
                return str;
            }
        }

        function _init() {
            if ($.FE.LANGUAGE) {
                lang = $.FE.LANGUAGE[editor.opts.language];
            }
            if (lang && lang.direction) {
                editor.opts.direction = lang.direction;
            }
        }

        return {_init: _init, translate: translate}
    };
    $.extend($.FE.DEFAULTS, {placeholderText: 'Type something'});
    $.FE.MODULES.placeholder = function (editor) {
        function show() {
            if (!editor.$placeholder) _add();
            var margin_offset = editor.opts.iframe ? editor.$iframe.prev().outerHeight(true) : editor.$el.prev().outerHeight(true);
            var margin_top = 0;
            var margin_left = 0;
            var margin_right = 0;
            var padding_top = 0;
            var padding_left = 0;
            var padding_right = 0;
            var contents = editor.node.contents(editor.el);
            var alignment = $(editor.selection.element()).css('text-align');
            if (contents.length && contents[0].nodeType == Node.ELEMENT_NODE) {
                var $first_node = $(contents[0]);
                if ((!editor.opts.toolbarInline || editor.$el.prev().length > 0) && editor.ready) {
                    margin_top = editor.helpers.getPX($first_node.css('margin-top'));
                    padding_top = editor.helpers.getPX($first_node.css('padding-top'));
                    margin_left = editor.helpers.getPX($first_node.css('margin-left'));
                    margin_right = editor.helpers.getPX($first_node.css('margin-right'));
                    padding_left = editor.helpers.getPX($first_node.css('padding-left'));
                    padding_right = editor.helpers.getPX($first_node.css('padding-right'));
                }
                editor.$placeholder.css('font-size', $first_node.css('font-size'));
                editor.$placeholder.css('line-height', $first_node.css('line-height'));
            } else {
                editor.$placeholder.css('font-size', editor.$el.css('font-size'));
                editor.$placeholder.css('line-height', editor.$el.css('line-height'));
            }
            editor.$wp.addClass('show-placeholder');
            editor.$placeholder.css({
                marginTop: Math.max(editor.helpers.getPX(editor.$el.css('margin-top')), margin_top) + (margin_offset ? margin_offset : 0),
                paddingTop: Math.max(editor.helpers.getPX(editor.$el.css('padding-top')), padding_top),
                paddingLeft: Math.max(editor.helpers.getPX(editor.$el.css('padding-left')), padding_left),
                marginLeft: Math.max(editor.helpers.getPX(editor.$el.css('margin-left')), margin_left),
                paddingRight: Math.max(editor.helpers.getPX(editor.$el.css('padding-right')), padding_right),
                marginRight: Math.max(editor.helpers.getPX(editor.$el.css('margin-right')), margin_right),
                textAlign: alignment
            }).text(editor.language.translate(editor.opts.placeholderText || editor.$oel.attr('placeholder') || ''));
            editor.$placeholder.html(editor.$placeholder.text().replace(/\n/g, '<br>'));
            editor.size.refresh();
            if (editor.$placeholder.height() > editor.$el.height()) {
                var oldHeight = editor.opts.heightMin;
                editor.opts.heightMin = editor.$placeholder.height() + editor.$tb ? editor.$tb.height() : 0;
                editor.size.refresh();
                editor.opts.heightMin = oldHeight;
            }
        }

        function hide() {
            editor.$wp.removeClass('show-placeholder');
            editor.size.refresh();
        }

        function isVisible() {
            return !editor.$wp ? false : editor.node.hasClass(editor.$wp.get(0), 'show-placeholder');
        }

        function refresh() {
            if (!editor.$wp) return false;
            if (editor.core.isEmpty()) {
                show();
            } else {
                hide();
            }
        }

        function _add() {
            editor.$placeholder = $('<span class="fr-placeholder"></span>');
            editor.$wp.append(editor.$placeholder);
        }

        function _init() {
            if (!editor.$wp) return false;
            editor.events.on('init input keydown keyup contentChanged initialized', refresh);
        }

        return {_init: _init, show: show, hide: hide, refresh: refresh, isVisible: isVisible}
    };
    $.FE.MODULES.edit = function (editor) {
        function disableDesign() {
            if (editor.browser.mozilla) {
                try {
                    editor.doc.execCommand('enableObjectResizing', false, 'false');
                    editor.doc.execCommand('enableInlineTableEditing', false, 'false');
                } catch (ex) {
                }
            }
            if (editor.browser.msie) {
                try {
                    editor.doc.body.addEventListener('mscontrolselect', function (e) {
                        e.preventDefault();
                        return false;
                    });
                } catch (ex) {
                }
            }
        }

        var disabled = false;

        function on() {
            if (editor.$wp) {
                editor.$el.attr('contenteditable', true);
                editor.$el.removeClass('fr-disabled').attr('aria-disabled', false);
                if (editor.$tb) editor.$tb.removeClass('fr-disabled').removeAttr('aria-disabled');
                disableDesign();
            } else if (editor.$el.is('a')) {
                editor.$el.attr('contenteditable', true);
            }
            disabled = false;
        }

        function off() {
            editor.events.disableBlur();
            if (editor.$wp) {
                editor.$el.attr('contenteditable', false);
                editor.$el.addClass('fr-disabled').attr('aria-disabled', true);
                if (editor.$tb) editor.$tb.addClass('fr-disabled').attr('aria-disabled', true);
            } else if (editor.$el.is('a')) {
                editor.$el.attr('contenteditable', false);
            }
            editor.events.enableBlur();
            disabled = true;
        }

        function isDisabled() {
            return disabled;
        }

        function _init() {
            editor.events.on('focus', function () {
                if (isDisabled()) editor.edit.off(); else editor.edit.on();
            });
        }

        return {_init: _init, on: on, off: off, disableDesign: disableDesign, isDisabled: isDisabled}
    };
    $.extend($.FE.DEFAULTS, {
        documentReady: false,
        editorClass: null,
        typingTimer: 500,
        iframe: false,
        requestWithCORS: true,
        requestWithCredentials: false,
        requestHeaders: {},
        useClasses: true,
        spellcheck: true,
        iframeDefaultStyle: 'html{margin:0px;height:auto;}body{height:auto;padding:10px;background:transparent;color:#000000;position:relative;z-index: 2;-webkit-user-select:auto;margin:0px;overflow:hidden;min-height:20px;}body:after{content:"";display:block;clear:both;}body::-moz-selection{background:#b5d6fd;color:#000;}body::selection{background:#b5d6fd;color:#000;}',
        iframeStyle: '',
        iframeStyleFiles: [],
        direction: 'auto',
        zIndex: 1,
        tabIndex: null,
        disableRightClick: false,
        scrollableContainer: 'body',
        keepFormatOnDelete: false,
        theme: null
    })
    $.FE.MODULES.core = function (editor) {
        function injectStyle(style) {
            if (editor.opts.iframe) {
                editor.$head.find('style[data-fr-style], link[data-fr-style]').remove();
                editor.$head.append('<style data-fr-style="true">' + style + '</style>');
                for (var i = 0; i < editor.opts.iframeStyleFiles.length; i++) {
                    var $link = $('<link data-fr-style="true" rel="stylesheet" href="' + editor.opts.iframeStyleFiles[i] + '">');
                    $link.get(0).addEventListener('load', editor.size.syncIframe);
                    editor.$head.append($link);
                }
            }
        }

        function _initElementStyle() {
            if (!editor.opts.iframe) {
                editor.$el.addClass('fr-element fr-view');
            }
        }

        function _initStyle() {
            editor.$box.addClass('fr-box' + (editor.opts.editorClass ? ' ' + editor.opts.editorClass : ''));
            editor.$box.attr('role', 'application');
            editor.$wp.addClass('fr-wrapper');
            if (editor.opts.documentReady) {
                editor.$box.addClass('fr-document');
            }
            _initElementStyle();
            if (editor.opts.iframe) {
                editor.$iframe.addClass('fr-iframe');
                editor.$el.addClass('fr-view');
                for (var i = 0; i < editor.o_doc.styleSheets.length; i++) {
                    var rules;
                    try {
                        rules = editor.o_doc.styleSheets[i].cssRules;
                    } catch (ex) {
                    }
                    if (rules) {
                        for (var idx = 0, len = rules.length; idx < len; idx++) {
                            if (rules[idx].selectorText && (rules[idx].selectorText.indexOf('.fr-view') === 0 || rules[idx].selectorText.indexOf('.fr-element') === 0)) {
                                if (rules[idx].style.cssText.length > 0) {
                                    if (rules[idx].selectorText.indexOf('.fr-view') === 0) {
                                        editor.opts.iframeStyle += rules[idx].selectorText.replace(/\.fr-view/g, 'body') + '{' + rules[idx].style.cssText + '}';
                                    } else {
                                        editor.opts.iframeStyle += rules[idx].selectorText.replace(/\.fr-element/g, 'body') + '{' + rules[idx].style.cssText + '}';
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (editor.opts.direction != 'auto') {
                editor.$box.removeClass('fr-ltr fr-rtl').addClass('fr-' + editor.opts.direction);
            }
            editor.$el.attr('dir', editor.opts.direction);
            editor.$wp.attr('dir', editor.opts.direction);
            if (editor.opts.zIndex > 1) {
                editor.$box.css('z-index', editor.opts.zIndex);
            }
            if (editor.opts.theme) {
                editor.$box.addClass(editor.opts.theme + '-theme');
            }
            editor.opts.tabIndex = editor.opts.tabIndex || editor.$oel.attr('tabIndex');
            if (editor.opts.tabIndex) {
                editor.$el.attr('tabIndex', editor.opts.tabIndex);
            }
        }

        function isEmpty() {
            return editor.node.isEmpty(editor.el);
        }

        function _initDrag() {
            editor.drag_support = {
                filereader: typeof FileReader != 'undefined',
                formdata: !!editor.win.FormData,
                progress: 'upload' in new XMLHttpRequest()
            };
        }

        function getXHR(url, method) {
            var xhr = new XMLHttpRequest();
            xhr.open(method, url, true);
            if (editor.opts.requestWithCredentials) {
                xhr.withCredentials = true;
            }
            for (var header in editor.opts.requestHeaders) {
                if (editor.opts.requestHeaders.hasOwnProperty(header)) {
                    xhr.setRequestHeader(header, editor.opts.requestHeaders[header]);
                }
            }
            return xhr;
        }

        function _destroy(html) {
            if (editor.$oel.get(0).tagName == 'TEXTAREA') {
                editor.$oel.val(html);
            }
            if (editor.$box) {
                editor.$box.removeAttr('role');
            }
            if (editor.$wp) {
                if (editor.$oel.get(0).tagName == 'TEXTAREA') {
                    editor.$el.html('');
                    editor.$wp.html('');
                    editor.$box.replaceWith(editor.$oel);
                    editor.$oel.show();
                } else {
                    editor.$wp.replaceWith(html);
                    editor.$el.html('');
                    editor.$box.removeClass('fr-view fr-ltr fr-box ' + (editor.opts.editorClass || ''));
                    if (editor.opts.theme) {
                        editor.$box.addClass(editor.opts.theme + '-theme');
                    }
                }
            }
            this.$wp = null;
            this.$el = null;
            this.el = null;
            this.$box = null;
        }

        function hasFocus() {
            if (editor.browser.mozilla && editor.helpers.isMobile()) return editor.selection.inEditor();
            return editor.node.hasFocus(editor.el) || editor.$el.find('*:focus').length > 0;
        }

        function sameInstance($obj) {
            if (!$obj) return false;
            var inst = $obj.data('instance');
            return (inst ? inst.id == editor.id : false);
        }

        function _init() {
            $.FE.INSTANCES.push(editor);
            _initDrag();
            if (editor.$wp) {
                _initStyle();
                editor.html.set(editor._original_html);
                editor.$el.attr('spellcheck', editor.opts.spellcheck);
                if (editor.helpers.isMobile()) {
                    editor.$el.attr('autocomplete', editor.opts.spellcheck ? 'on' : 'off');
                    editor.$el.attr('autocorrect', editor.opts.spellcheck ? 'on' : 'off');
                    editor.$el.attr('autocapitalize', editor.opts.spellcheck ? 'on' : 'off');
                }
                if (editor.opts.disableRightClick) {
                    editor.events.$on(editor.$el, 'contextmenu', function (e) {
                        if (e.button == 2) {
                            return false;
                        }
                    });
                }
                try {
                    editor.doc.execCommand('styleWithCSS', false, false);
                } catch (ex) {
                }
            }
            if (editor.$oel.get(0).tagName == 'TEXTAREA') {
                editor.events.on('contentChanged', function () {
                    editor.$oel.val(editor.html.get());
                });
                editor.events.on('form.submit', function () {
                    editor.$oel.val(editor.html.get());
                });
                editor.events.on('form.reset', function () {
                    editor.html.set(editor._original_html);
                })
                editor.$oel.val(editor.html.get());
            }
            if (editor.helpers.isIOS()) {
                editor.events.$on(editor.$doc, 'selectionchange', function () {
                    if (!editor.$doc.get(0).hasFocus()) {
                        editor.$win.get(0).focus();
                    }
                });
            }
            editor.events.trigger('init');
            if (editor.opts.autofocus && !editor.opts.initOnClick && editor.$wp) {
                editor.events.on('initialized', function () {
                    editor.events.focus(true);
                })
            }
        }

        return {
            _init: _init,
            destroy: _destroy,
            isEmpty: isEmpty,
            getXHR: getXHR,
            injectStyle: injectStyle,
            hasFocus: hasFocus,
            sameInstance: sameInstance
        }
    }
    $.FE.MODULES.cursorLists = function (editor) {
        function _firstParentLI(node) {
            var p_node = node;
            while (p_node.tagName != 'LI') {
                p_node = p_node.parentNode;
            }
            return p_node;
        }

        function _firstParentList(node) {
            var p_node = node;
            while (!editor.node.isList(p_node)) {
                p_node = p_node.parentNode;
            }
            return p_node;
        }

        function _startEnter(marker) {
            var li = _firstParentLI(marker);
            var next_li = li.nextSibling;
            var prev_li = li.previousSibling;
            var default_tag = editor.html.defaultTag();
            var ul;
            if (editor.node.isEmpty(li, true) && next_li) {
                var o_str = '';
                var c_str = ''
                var p_node = marker.parentNode;
                while (!editor.node.isList(p_node) && p_node.parentNode && (p_node.parentNode.tagName !== 'LI' || p_node.parentNode === li)) {
                    o_str = editor.node.openTagString(p_node) + o_str;
                    c_str = c_str + editor.node.closeTagString(p_node);
                    p_node = p_node.parentNode;
                }
                o_str = editor.node.openTagString(p_node) + o_str;
                c_str = c_str + editor.node.closeTagString(p_node);
                var str = '';
                if (p_node.parentNode && p_node.parentNode.tagName == 'LI') {
                    str = c_str + '<li>' + $.FE.MARKERS + '<br>' + o_str;
                } else {
                    if (default_tag) {
                        str = c_str + '<' + default_tag + '>' + $.FE.MARKERS + '<br>' + '</' + default_tag + '>' + o_str;
                    } else {
                        str = c_str + $.FE.MARKERS + '<br>' + o_str;
                    }
                }
                while (['UL', 'OL'].indexOf(p_node.tagName) < 0 || (p_node.parentNode && p_node.parentNode.tagName === 'LI')) {
                    p_node = p_node.parentNode;
                }
                $(li).replaceWith('<span id="fr-break"></span>')
                var html = editor.node.openTagString(p_node) + $(p_node).html() + editor.node.closeTagString(p_node);
                html = html.replace(/<span id="fr-break"><\/span>/g, str);
                $(p_node).replaceWith(html);
                editor.$el.find('li:empty').remove();
            } else if ((prev_li && next_li) || !editor.node.isEmpty(li, true)) {
                var br_str = '<br>';
                var nd = marker.parentNode;
                while (nd && nd.tagName != 'LI') {
                    br_str = editor.node.openTagString(nd) + br_str + editor.node.closeTagString(nd);
                    nd = nd.parentNode;
                }
                $(li).before('<li>' + br_str + '</li>');
                $(marker).remove();
            } else if (!prev_li) {
                ul = _firstParentList(li);
                if (ul.parentNode && ul.parentNode.tagName == 'LI') {
                    if (next_li) {
                        $(ul.parentNode).before(editor.node.openTagString(li) + $.FE.MARKERS + '<br></li>');
                    } else {
                        $(ul.parentNode).after(editor.node.openTagString(li) + $.FE.MARKERS + '<br></li>');
                    }
                } else {
                    if (default_tag) {
                        $(ul).before('<' + default_tag + '>' + $.FE.MARKERS + '<br></' + default_tag + '>');
                    } else {
                        $(ul).before($.FE.MARKERS + '<br>');
                    }
                }
                $(li).remove();
            } else {
                ul = _firstParentList(li);
                var new_str = $.FE.MARKERS + '<br>';
                var ndx = marker.parentNode;
                while (ndx && ndx.tagName != 'LI') {
                    new_str = editor.node.openTagString(ndx) + new_str + editor.node.closeTagString(ndx);
                    ndx = ndx.parentNode;
                }
                if (ul.parentNode && ul.parentNode.tagName == 'LI') {
                    $(ul.parentNode).after('<li>' + new_str + '</li>');
                } else {
                    if (default_tag) {
                        $(ul).after('<' + default_tag + '>' + new_str + '</' + default_tag + '>');
                    } else {
                        $(ul).after(new_str);
                    }
                }
                $(li).remove();
            }
        }

        function _middleEnter(marker) {
            var li = _firstParentLI(marker);
            var str = '';
            var node = marker;
            var o_str = '';
            var c_str = '';
            while (node != li) {
                node = node.parentNode;
                var cls = (node.tagName == 'A' && editor.cursor.isAtEnd(marker, node)) ? 'fr-to-remove' : '';
                o_str = editor.node.openTagString($(node).clone().addClass(cls).get(0)) + o_str;
                c_str = editor.node.closeTagString(node) + c_str;
            }
            str = c_str + str + o_str + $.FE.MARKERS + (editor.opts.keepFormatOnDelete ? $.FE.INVISIBLE_SPACE : '');
            $(marker).replaceWith('<span id="fr-break"></span>');
            var html = editor.node.openTagString(li) + $(li).html() + editor.node.closeTagString(li);
            html = html.replace(/<span id="fr-break"><\/span>/g, str);
            $(li).replaceWith(html);
        }

        function _endEnter(marker) {
            var li = _firstParentLI(marker);
            var end_str = $.FE.MARKERS;
            var start_str = '';
            var node = marker;
            var add_invisible = false;
            while (node != li) {
                node = node.parentNode;
                var cls = (node.tagName == 'A' && editor.cursor.isAtEnd(marker, node)) ? 'fr-to-remove' : '';
                if (!add_invisible && node != li && !editor.node.isBlock(node)) {
                    add_invisible = true;
                    start_str = start_str + $.FE.INVISIBLE_SPACE;
                }
                start_str = editor.node.openTagString($(node).clone().addClass(cls).get(0)) + start_str;
                end_str = end_str + editor.node.closeTagString(node);
            }
            var str = start_str + end_str;
            $(marker).remove();
            $(li).after(str);
        }

        function _backspace(marker) {
            var li = _firstParentLI(marker);
            var prev_li = li.previousSibling;
            if (prev_li) {
                prev_li = $(prev_li).find(editor.html.blockTagsQuery()).get(-1) || prev_li;
                $(marker).replaceWith($.FE.MARKERS);
                var contents = editor.node.contents(prev_li);
                if (contents.length && contents[contents.length - 1].tagName == 'BR') {
                    $(contents[contents.length - 1]).remove();
                }
                $(li).find(editor.html.blockTagsQuery()).not('ol, ul, table').each(function () {
                    if (this.parentNode == li) {
                        $(this).replaceWith($(this).html() + (editor.node.isEmpty(this) ? '' : '<br>'));
                    }
                })
                var node = editor.node.contents(li)[0];
                var tmp;
                while (node && !editor.node.isList(node)) {
                    tmp = node.nextSibling;
                    $(prev_li).append(node);
                    node = tmp;
                }
                prev_li = li.previousSibling;
                while (node) {
                    tmp = node.nextSibling;
                    $(prev_li).append(node);
                    node = tmp;
                }
                contents = editor.node.contents(prev_li)
                if (contents.length > 1 && contents[contents.length - 1].tagName === 'BR') {
                    $(contents[contents.length - 1]).remove()
                }
                $(li).remove();
            } else {
                var ul = _firstParentList(li);
                $(marker).replaceWith($.FE.MARKERS);
                if (ul.parentNode && ul.parentNode.tagName == 'LI') {
                    var prev_node = ul.previousSibling;
                    if (editor.node.isBlock(prev_node)) {
                        $(li).find(editor.html.blockTagsQuery()).not('ol, ul, table').each(function () {
                            if (this.parentNode == li) {
                                $(this).replaceWith($(this).html() + (editor.node.isEmpty(this) ? '' : '<br>'));
                            }
                        });
                        $(prev_node).append($(li).html());
                    } else {
                        $(ul).before($(li).html());
                    }
                } else {
                    var default_tag = editor.html.defaultTag();
                    if (default_tag && $(li).find(editor.html.blockTagsQuery()).length === 0) {
                        $(ul).before('<' + default_tag + '>' + $(li).html() + '</' + default_tag + '>');
                    } else {
                        $(ul).before($(li).html());
                    }
                }
                $(li).remove();
                editor.html.wrap();
                if ($(ul).find('li').length === 0) $(ul).remove();
            }
        }

        function _del(marker) {
            var li = _firstParentLI(marker);
            var next_li = li.nextSibling;
            var contents;
            if (next_li) {
                contents = editor.node.contents(next_li);
                if (contents.length && contents[0].tagName == 'BR') {
                    $(contents[0]).remove();
                }
                $(next_li).find(editor.html.blockTagsQuery()).not('ol, ul, table').each(function () {
                    if (this.parentNode == next_li) {
                        $(this).replaceWith($(this).html() + (editor.node.isEmpty(this) ? '' : '<br>'));
                    }
                });
                var last_node = marker;
                var node = editor.node.contents(next_li)[0];
                var tmp;
                while (node && !editor.node.isList(node)) {
                    tmp = node.nextSibling;
                    $(last_node).after(node);
                    last_node = node;
                    node = tmp;
                }
                while (node) {
                    tmp = node.nextSibling;
                    $(li).append(node);
                    node = tmp;
                }
                $(marker).replaceWith($.FE.MARKERS);
                $(next_li).remove();
            } else {
                var next_node = li;
                while (!next_node.nextSibling && next_node != editor.el) {
                    next_node = next_node.parentNode;
                }
                if (next_node == editor.el) return false;
                next_node = next_node.nextSibling;
                if (editor.node.isBlock(next_node)) {
                    if ($.FE.NO_DELETE_TAGS.indexOf(next_node.tagName) < 0) {
                        $(marker).replaceWith($.FE.MARKERS);
                        contents = editor.node.contents(li);
                        if (contents.length && contents[contents.length - 1].tagName == 'BR') {
                            $(contents[contents.length - 1]).remove();
                        }
                        $(li).append($(next_node).html());
                        $(next_node).remove();
                    }
                } else {
                    contents = editor.node.contents(li);
                    if (contents.length && contents[contents.length - 1].tagName == 'BR') {
                        $(contents[contents.length - 1]).remove();
                    }
                    $(marker).replaceWith($.FE.MARKERS);
                    while (next_node && !editor.node.isBlock(next_node) && next_node.tagName != 'BR') {
                        $(li).append($(next_node));
                        next_node = next_node.nextSibling;
                    }
                }
            }
        }

        return {
            _startEnter: _startEnter,
            _middleEnter: _middleEnter,
            _endEnter: _endEnter,
            _backspace: _backspace,
            _del: _del
        }
    };
    $.FE.NO_DELETE_TAGS = ['TH', 'TD', 'TR', 'TABLE', 'FORM'];
    $.FE.SIMPLE_ENTER_TAGS = ['TH', 'TD', 'LI', 'DL', 'DT', 'FORM'];
    $.FE.MODULES.cursor = function (editor) {
        function _atEnd(node) {
            if (!node) return false;
            if (editor.node.isBlock(node)) return true;
            if (node.nextSibling && node.nextSibling.nodeType == Node.TEXT_NODE && node.nextSibling.textContent.replace(/\u200b/g, '').length === 0) {
                return _atEnd(node.nextSibling);
            }
            if (node.nextSibling && !(node.previousSibling && node.nextSibling.tagName == 'BR' && !node.nextSibling.nextSibling)) return false;
            return _atEnd(node.parentNode);
        }

        function _atStart(node) {
            if (!node) return false;
            if (editor.node.isBlock(node)) return true;
            if (node.previousSibling && node.previousSibling.nodeType == Node.TEXT_NODE && node.previousSibling.textContent.replace(/\u200b/g, '').length === 0) {
                return _atStart(node.previousSibling);
            }
            if (node.previousSibling) return false;
            if (!node.previousSibling && editor.node.hasClass(node.parentNode, 'fr-inner')) return true;
            return _atStart(node.parentNode);
        }

        function _isAtStart(node, container) {
            if (!node) return false;
            if (node == editor.$wp.get(0)) return false;
            if (node.previousSibling && node.previousSibling.nodeType == Node.TEXT_NODE && node.previousSibling.textContent.replace(/\u200b/g, '').length === 0) {
                return _isAtStart(node.previousSibling, container);
            }
            if (node.previousSibling) return false;
            if (node.parentNode == container) return true;
            return _isAtStart(node.parentNode, container);
        }

        function _isAtEnd(node, container) {
            if (!node) return false;
            if (node == editor.$wp.get(0)) return false;
            if (node.nextSibling && node.nextSibling.nodeType == Node.TEXT_NODE && node.nextSibling.textContent.replace(/\u200b/g, '').length === 0) {
                return _isAtEnd(node.nextSibling, container);
            }
            if (node.nextSibling && !(node.previousSibling && node.nextSibling.tagName == 'BR' && !node.nextSibling.nextSibling)) return false;
            if (node.parentNode == container) return true;
            return _isAtEnd(node.parentNode, container);
        }

        function _inLi(node) {
            return $(node).parentsUntil(editor.$el, 'LI').length > 0 && $(node).parentsUntil('LI', 'TABLE').length === 0;
        }

        function _getExtremityCharacterLength(text, first) {
            var special_chars_regex = new RegExp((first ? '^' : '') + '(([\\uD83C-\\uDBFF\\uDC00-\\uDFFF]+\\u200D)*[\\uD83C-\\uDBFF\\uDC00-\\uDFFF]{2})' + ((first ? '' : '$')), 'i');
            var matches = text.match(special_chars_regex);
            if (!matches) {
                return 1;
            } else {
                return matches[0].length;
            }
        }

        function _startBackspace(marker) {
            var quote = $(marker).parentsUntil(editor.$el, 'BLOCKQUOTE').length > 0;
            var deep_parent = editor.node.deepestParent(marker, [], !quote);
            var current_block = deep_parent;
            while (deep_parent && !deep_parent.previousSibling && deep_parent.tagName != 'BLOCKQUOTE' && deep_parent.parentElement != editor.el && !editor.node.hasClass(deep_parent.parentElement, 'fr-inner') && $.FE.SIMPLE_ENTER_TAGS.indexOf(deep_parent.parentElement.tagName) < 0) {
                deep_parent = deep_parent.parentElement;
            }
            if (deep_parent && deep_parent.tagName == 'BLOCKQUOTE') {
                var m_parent = editor.node.deepestParent(marker, [$(marker).parentsUntil(editor.$el, 'BLOCKQUOTE').get(0)]);
                if (m_parent && m_parent.previousSibling) {
                    deep_parent = m_parent;
                    current_block = m_parent;
                }
            }
            if (deep_parent !== null) {
                var prev_node = deep_parent.previousSibling;
                var contents;
                if (editor.node.isBlock(deep_parent) && editor.node.isEditable(deep_parent)) {
                    if (prev_node && $.FE.NO_DELETE_TAGS.indexOf(prev_node.tagName) < 0) {
                        if (editor.node.isDeletable(prev_node)) {
                            $(prev_node).remove();
                            $(marker).replaceWith($.FE.MARKERS);
                        } else {
                            if (editor.node.isEditable(prev_node)) {
                                if (editor.node.isBlock(prev_node)) {
                                    if (editor.node.isEmpty(prev_node) && !editor.node.isList(prev_node)) {
                                        $(prev_node).remove();
                                        $(marker).after(editor.opts.keepFormatOnDelete ? $.FE.INVISIBLE_SPACE : '');
                                    } else {
                                        if (editor.node.isList(prev_node)) {
                                            prev_node = $(prev_node).find('li:last').get(0);
                                        }
                                        contents = editor.node.contents(prev_node);
                                        if (contents.length && contents[contents.length - 1].tagName == 'BR') {
                                            $(contents[contents.length - 1]).remove();
                                        }
                                        if (prev_node.tagName == 'BLOCKQUOTE' && deep_parent.tagName != 'BLOCKQUOTE') {
                                            contents = editor.node.contents(prev_node);
                                            while (contents.length && editor.node.isBlock(contents[contents.length - 1])) {
                                                prev_node = contents[contents.length - 1];
                                                contents = editor.node.contents(prev_node);
                                            }
                                        } else if (prev_node.tagName != 'BLOCKQUOTE' && current_block.tagName == 'BLOCKQUOTE') {
                                            contents = editor.node.contents(current_block);
                                            while (contents.length && editor.node.isBlock(contents[0])) {
                                                current_block = contents[0];
                                                contents = editor.node.contents(current_block);
                                            }
                                        }
                                        if (editor.node.isEmpty(deep_parent)) {
                                            $(marker).remove();
                                            editor.selection.setAtEnd(prev_node, true);
                                        } else {
                                            $(marker).replaceWith($.FE.MARKERS);
                                            var prev_children = prev_node.childNodes;
                                            if (!editor.node.isBlock(prev_children[prev_children.length - 1])) {
                                                $(prev_node).append(current_block.innerHTML);
                                            } else {
                                                $(prev_children[prev_children.length - 1]).append(current_block.innerHTML);
                                            }
                                        }
                                        $(current_block).remove();
                                        if (editor.node.isEmpty(deep_parent)) {
                                            $(deep_parent).remove();
                                        }
                                    }
                                } else {
                                    $(marker).replaceWith($.FE.MARKERS);
                                    if (deep_parent.tagName == 'BLOCKQUOTE' && prev_node.nodeType == Node.ELEMENT_NODE) {
                                        $(prev_node).remove();
                                    } else {
                                        $(prev_node).after(editor.node.isEmpty(deep_parent) ? '' : $(deep_parent).html());
                                        $(deep_parent).remove();
                                        if (prev_node.tagName == 'BR') $(prev_node).remove();
                                    }
                                }
                            }
                        }
                    } else if (!prev_node) {
                        if (deep_parent && deep_parent.tagName == 'BLOCKQUOTE' && $(deep_parent).text().replace(/\u200B/g, '').length == 0) {
                            $(deep_parent).remove();
                        }
                    }
                } else {
                }
            }
        }

        function _middleBackspace(marker) {
            var prev_node = marker;
            while (!prev_node.previousSibling) {
                prev_node = prev_node.parentNode;
                if (editor.node.isElement(prev_node)) return false;
            }
            prev_node = prev_node.previousSibling;
            var contents;
            if (!editor.node.isBlock(prev_node) && editor.node.isEditable(prev_node)) {
                contents = editor.node.contents(prev_node);
                while (prev_node.nodeType != Node.TEXT_NODE && !editor.node.isDeletable(prev_node) && contents.length && editor.node.isEditable(prev_node)) {
                    prev_node = contents[contents.length - 1];
                    contents = editor.node.contents(prev_node);
                }
                if (prev_node.nodeType == Node.TEXT_NODE) {
                    var txt = prev_node.textContent;
                    var len = txt.length;
                    if (txt.length && txt[txt.length - 1] === '\n') {
                        prev_node.textContent = txt.substring(0, len - 2);
                        if (prev_node.textContent.length === 0) {
                            prev_node.parentNode.removeChild(prev_node);
                        }
                        return _middleBackspace(marker);
                    }
                    if (editor.opts.tabSpaces && txt.length >= editor.opts.tabSpaces) {
                        var tab_str = txt.substr(txt.length - editor.opts.tabSpaces, txt.length - 1);
                        if (tab_str.replace(/ /g, '').replace(new RegExp($.FE.UNICODE_NBSP, 'g'), '').length === 0) {
                            len = txt.length - editor.opts.tabSpaces + 1;
                        }
                    }
                    prev_node.textContent = txt.substring(0, len - _getExtremityCharacterLength(txt));
                    if (editor.opts.htmlUntouched && !marker.nextSibling && prev_node.textContent.length && prev_node.textContent[prev_node.textContent.length - 1] === ' ') {
                        prev_node.textContent = prev_node.textContent.substring(0, prev_node.textContent.length - 1) + $.FE.UNICODE_NBSP;
                    }
                    var deleted = (txt.length != prev_node.textContent.length);
                    if (prev_node.textContent.length === 0) {
                        if (deleted && editor.opts.keepFormatOnDelete) {
                            $(prev_node).after($.FE.INVISIBLE_SPACE + $.FE.MARKERS);
                        } else {
                            if (((prev_node.parentNode.childNodes.length == 2 && prev_node.parentNode == marker.parentNode) || prev_node.parentNode.childNodes.length == 1) && !editor.node.isBlock(prev_node.parentNode) && !editor.node.isElement(prev_node.parentNode) && editor.node.isDeletable(prev_node.parentNode)) {
                                $(prev_node.parentNode).after($.FE.MARKERS);
                                $(prev_node.parentNode).remove();
                            } else {
                                while (!editor.node.isElement(prev_node.parentNode) && editor.node.isEmpty(prev_node.parentNode) && $.FE.NO_DELETE_TAGS.indexOf(prev_node.parentNode.tagName) < 0) {
                                    var t_node = prev_node;
                                    prev_node = prev_node.parentNode;
                                    t_node.parentNode.removeChild(t_node);
                                }
                                $(prev_node).after($.FE.MARKERS);
                                if (editor.node.isElement(prev_node.parentNode) && !marker.nextSibling && prev_node.previousSibling && prev_node.previousSibling.tagName == 'BR') {
                                    $(marker).after('<br>');
                                }
                                prev_node.parentNode.removeChild(prev_node);
                            }
                        }
                    } else {
                        $(prev_node).after($.FE.MARKERS);
                    }
                } else if (editor.node.isDeletable(prev_node)) {
                    $(prev_node).after($.FE.MARKERS);
                    $(prev_node).remove();
                } else {
                    if (marker.nextSibling && marker.nextSibling.tagName == 'BR' && editor.node.isVoid(prev_node) && prev_node.tagName != 'BR') {
                        $(marker.nextSibling).remove();
                        $(marker).replaceWith($.FE.MARKERS);
                    } else if (editor.events.trigger('node.remove', [$(prev_node)]) !== false) {
                        $(prev_node).after($.FE.MARKERS);
                        $(prev_node).remove();
                    }
                }
            } else if ($.FE.NO_DELETE_TAGS.indexOf(prev_node.tagName) < 0 && (editor.node.isEditable(prev_node) || editor.node.isDeletable(prev_node))) {
                if (editor.node.isDeletable(prev_node)) {
                    $(marker).replaceWith($.FE.MARKERS);
                    $(prev_node).remove();
                } else if (editor.node.isEmpty(prev_node) && !editor.node.isList(prev_node)) {
                    $(prev_node).remove();
                    $(marker).replaceWith($.FE.MARKERS);
                } else {
                    if (editor.node.isList(prev_node)) prev_node = $(prev_node).find('li:last').get(0);
                    contents = editor.node.contents(prev_node);
                    if (contents && contents[contents.length - 1].tagName == 'BR') {
                        $(contents[contents.length - 1]).remove();
                    }
                    contents = editor.node.contents(prev_node);
                    while (contents && editor.node.isBlock(contents[contents.length - 1])) {
                        prev_node = contents[contents.length - 1];
                        contents = editor.node.contents(prev_node);
                    }
                    $(prev_node).append($.FE.MARKERS);
                    var next_node = marker;
                    while (!next_node.previousSibling) {
                        next_node = next_node.parentNode;
                    }
                    while (next_node && next_node.tagName !== 'BR' && !editor.node.isBlock(next_node)) {
                        var copy_node = next_node;
                        next_node = next_node.nextSibling;
                        $(prev_node).append(copy_node);
                    }
                    if (next_node && next_node.tagName == 'BR') $(next_node).remove();
                    $(marker).remove();
                }
            } else {
                if (marker.nextSibling && marker.nextSibling.tagName == 'BR') {
                    $(marker.nextSibling).remove();
                }
            }
        }

        function backspace() {
            var do_default = false;
            var marker = editor.markers.insert();
            if (!marker) return true;
            var p_node = marker.parentNode;
            while (p_node && !editor.node.isElement(p_node)) {
                if (p_node.getAttribute('contenteditable') === 'false') {
                    $(marker).replaceWith($.FE.MARKERS);
                    editor.selection.restore();
                    return false;
                } else if (p_node.getAttribute('contenteditable') === 'true') {
                    break;
                }
                p_node = p_node.parentNode;
            }
            editor.el.normalize();
            var prev_node = marker.previousSibling;
            if (prev_node) {
                var txt = prev_node.textContent;
                if (txt && txt.length && txt.charCodeAt(txt.length - 1) == 8203) {
                    if (txt.length == 1) {
                        $(prev_node).remove()
                    } else {
                        prev_node.textContent = prev_node.textContent.substr(0, txt.length - _getExtremityCharacterLength(txt));
                    }
                }
            }
            if (_atEnd(marker)) {
                do_default = _middleBackspace(marker);
            } else if (_atStart(marker)) {
                if (_inLi(marker) && _isAtStart(marker, $(marker).parents('li:first').get(0))) {
                    editor.cursorLists._backspace(marker);
                } else {
                    _startBackspace(marker);
                }
            } else {
                do_default = _middleBackspace(marker);
            }
            $(marker).remove();
            _cleanEmptyBlockquotes();
            editor.html.fillEmptyBlocks(true);
            if (!editor.opts.htmlUntouched) {
                editor.html.cleanEmptyTags();
                editor.clean.lists();
                editor.spaces.normalizeAroundCursor();
            }
            editor.selection.restore();
            return do_default;
        }

        function _endDel(marker) {
            var quote = $(marker).parentsUntil(editor.$el, 'BLOCKQUOTE').length > 0;
            var deep_parent = editor.node.deepestParent(marker, [], !quote);
            if (deep_parent && deep_parent.tagName == 'BLOCKQUOTE') {
                var m_parent = editor.node.deepestParent(marker, [$(marker).parentsUntil(editor.$el, 'BLOCKQUOTE').get(0)]);
                if (m_parent && m_parent.nextSibling) {
                    deep_parent = m_parent;
                }
            }
            if (deep_parent !== null) {
                var next_node = deep_parent.nextSibling;
                var contents;
                if (editor.node.isBlock(deep_parent) && (editor.node.isEditable(deep_parent) || editor.node.isDeletable(deep_parent))) {
                    if (next_node && $.FE.NO_DELETE_TAGS.indexOf(next_node.tagName) < 0) {
                        if (editor.node.isDeletable(next_node)) {
                            $(next_node).remove();
                            $(marker).replaceWith($.FE.MARKERS);
                        } else {
                            if (editor.node.isBlock(next_node) && editor.node.isEditable(next_node)) {
                                if (editor.node.isList(next_node)) {
                                    if (editor.node.isEmpty(deep_parent, true)) {
                                        $(deep_parent).remove();
                                        $(next_node).find('li:first').prepend($.FE.MARKERS);
                                    } else {
                                        var $li = $(next_node).find('li:first');
                                        if (deep_parent.tagName == 'BLOCKQUOTE') {
                                            contents = editor.node.contents(deep_parent);
                                            if (contents.length && editor.node.isBlock(contents[contents.length - 1])) {
                                                deep_parent = contents[contents.length - 1];
                                            }
                                        }
                                        if ($li.find('ul, ol').length === 0) {
                                            $(marker).replaceWith($.FE.MARKERS);
                                            $li.find(editor.html.blockTagsQuery()).not('ol, ul, table').each(function () {
                                                if (this.parentNode == $li.get(0)) {
                                                    $(this).replaceWith($(this).html() + (editor.node.isEmpty(this) ? '' : '<br>'));
                                                }
                                            });
                                            $(deep_parent).append(editor.node.contents($li.get(0)));
                                            $li.remove();
                                            if ($(next_node).find('li').length === 0) $(next_node).remove();
                                        }
                                    }
                                } else {
                                    contents = editor.node.contents(next_node);
                                    if (contents.length && contents[0].tagName == 'BR') {
                                        $(contents[0]).remove();
                                    }
                                    if (next_node.tagName != 'BLOCKQUOTE' && deep_parent.tagName == 'BLOCKQUOTE') {
                                        contents = editor.node.contents(deep_parent);
                                        while (contents.length && editor.node.isBlock(contents[contents.length - 1])) {
                                            deep_parent = contents[contents.length - 1];
                                            contents = editor.node.contents(deep_parent);
                                        }
                                    } else if (next_node.tagName == 'BLOCKQUOTE' && deep_parent.tagName != 'BLOCKQUOTE') {
                                        contents = editor.node.contents(next_node);
                                        while (contents.length && editor.node.isBlock(contents[0])) {
                                            next_node = contents[0];
                                            contents = editor.node.contents(next_node);
                                        }
                                    }
                                    $(marker).replaceWith($.FE.MARKERS);
                                    $(deep_parent).append(next_node.innerHTML);
                                    $(next_node).remove();
                                }
                            } else {
                                $(marker).replaceWith($.FE.MARKERS);
                                while (next_node && next_node.tagName !== 'BR' && !editor.node.isBlock(next_node) && editor.node.isEditable(next_node)) {
                                    var copy_node = next_node;
                                    next_node = next_node.nextSibling;
                                    $(deep_parent).append(copy_node);
                                }
                                if (next_node && next_node.tagName == 'BR' && editor.node.isEditable(next_node)) {
                                    $(next_node).remove();
                                }
                            }
                        }
                    }
                } else {
                }
            }
        }

        function _middleDel(marker) {
            var next_node = marker;
            while (!next_node.nextSibling) {
                next_node = next_node.parentNode;
                if (editor.node.isElement(next_node)) return false;
            }
            next_node = next_node.nextSibling;
            if (next_node.tagName == 'BR' && editor.node.isEditable(next_node)) {
                if (next_node.nextSibling) {
                    if (editor.node.isBlock(next_node.nextSibling) && editor.node.isEditable(next_node.nextSibling)) {
                        if ($.FE.NO_DELETE_TAGS.indexOf(next_node.nextSibling.tagName) < 0) {
                            next_node = next_node.nextSibling;
                            $(next_node.previousSibling).remove();
                        } else {
                            $(next_node).remove();
                            return;
                        }
                    }
                } else if (_atEnd(next_node)) {
                    if (_inLi(marker)) {
                        editor.cursorLists._del(marker);
                    } else {
                        var deep_parent = editor.node.deepestParent(next_node);
                        if (deep_parent) {
                            if (!editor.node.isEmpty(editor.node.blockParent(next_node)) || (editor.node.blockParent(next_node).nextSibling && $.FE.NO_DELETE_TAGS.indexOf(editor.node.blockParent(next_node).nextSibling.tagName)) < 0) {
                                $(next_node).remove();
                            }
                            _endDel(marker);
                        }
                    }
                    return;
                }
            }
            var contents;
            if (!editor.node.isBlock(next_node) && editor.node.isEditable(next_node)) {
                contents = editor.node.contents(next_node);
                while (next_node.nodeType != Node.TEXT_NODE && contents.length && !editor.node.isDeletable(next_node) && editor.node.isEditable(next_node)) {
                    next_node = contents[0];
                    contents = editor.node.contents(next_node);
                }
                if (next_node.nodeType == Node.TEXT_NODE) {
                    $(next_node).before($.FE.MARKERS);
                    if (next_node.textContent.length) {
                        next_node.textContent = next_node.textContent.substring(_getExtremityCharacterLength(next_node.textContent, true), next_node.textContent.length);
                    }
                } else if (editor.node.isDeletable(next_node)) {
                    $(next_node).before($.FE.MARKERS);
                    $(next_node).remove();
                } else {
                    if (editor.events.trigger('node.remove', [$(next_node)]) !== false) {
                        $(next_node).before($.FE.MARKERS);
                        $(next_node).remove();
                    }
                }
                $(marker).remove();
            } else if ($.FE.NO_DELETE_TAGS.indexOf(next_node.tagName) < 0 && (editor.node.isEditable(next_node) || editor.node.isDeletable(next_node))) {
                if (editor.node.isDeletable(next_node)) {
                    $(marker).replaceWith($.FE.MARKERS);
                    $(next_node).remove();
                } else {
                    if (editor.node.isList(next_node)) {
                        if (marker.previousSibling) {
                            $(next_node).find('li:first').prepend(marker);
                            editor.cursorLists._backspace(marker);
                        } else {
                            $(next_node).find('li:first').prepend($.FE.MARKERS);
                            $(marker).remove();
                        }
                    } else {
                        contents = editor.node.contents(next_node);
                        if (contents && contents.length && contents[0].tagName == 'BR') {
                            $(contents[0]).remove();
                        }
                        if (contents && next_node.tagName == 'BLOCKQUOTE') {
                            var node = contents[0];
                            $(marker).before($.FE.MARKERS);
                            while (node && node.tagName != 'BR') {
                                var tmp = node;
                                node = node.nextSibling;
                                $(marker).before(tmp);
                            }
                            if (node && node.tagName == 'BR') {
                                $(node).remove();
                            }
                        } else {
                            $(marker).after($(next_node).html()).after($.FE.MARKERS);
                            $(next_node).remove();
                        }
                    }
                }
            }
        }

        function del() {
            var marker = editor.markers.insert();
            if (!marker) return false;
            editor.el.normalize();
            if (_atEnd(marker)) {
                if (_inLi(marker)) {
                    if ($(marker).parents('li:first').find('ul, ol').length === 0) {
                        editor.cursorLists._del(marker);
                    } else {
                        var $li = $(marker).parents('li:first').find('ul:first, ol:first').find('li:first');
                        $li = $li.find(editor.html.blockTagsQuery()).get(-1) || $li;
                        $li.prepend(marker);
                        editor.cursorLists._backspace(marker);
                    }
                } else {
                    _endDel(marker);
                }
            } else if (_atStart(marker)) {
                _middleDel(marker);
            } else {
                _middleDel(marker);
            }
            $(marker).remove();
            _cleanEmptyBlockquotes();
            editor.html.fillEmptyBlocks(true);
            if (!editor.opts.htmlUntouched) {
                editor.html.cleanEmptyTags();
                editor.clean.lists();
            }
            editor.spaces.normalizeAroundCursor();
            editor.selection.restore();
        }

        function _cleanEmptyBlockquotes() {
            var blks = editor.el.querySelectorAll('blockquote:empty');
            for (var i = 0; i < blks.length; i++) {
                blks[i].parentNode.removeChild(blks[i]);
            }
        }

        function _cleanNodesToRemove() {
            editor.$el.find('.fr-to-remove').each(function () {
                var contents = editor.node.contents(this);
                for (var i = 0; i < contents.length; i++) {
                    if (contents[i].nodeType == Node.TEXT_NODE) {
                        contents[i].textContent = contents[i].textContent.replace(/\u200B/g, '');
                    }
                }
                $(this).replaceWith(this.innerHTML);
            })
        }

        function _endEnter(marker, shift, quote) {
            var deep_parent = editor.node.deepestParent(marker, [], !quote);
            var default_tag;
            if (deep_parent && deep_parent.tagName == 'BLOCKQUOTE') {
                if (_isAtEnd(marker, deep_parent)) {
                    default_tag = editor.html.defaultTag();
                    if (!shift) {
                        if (default_tag) {
                            $(deep_parent).after('<' + default_tag + '>' + $.FE.MARKERS + '<br>' + '</' + default_tag + '>');
                        } else {
                            $(deep_parent).after($.FE.MARKERS + '<br>');
                        }
                    } else {
                        $(marker).replaceWith('<br>' + $.FE.MARKERS);
                    }
                    $(marker).remove();
                    return false;
                } else {
                    _middleEnter(marker, shift, quote);
                    return false;
                }
            }
            if (deep_parent == null) {
                default_tag = editor.html.defaultTag();
                if (!default_tag || !editor.node.isElement(marker.parentNode)) {
                    if (marker.previousSibling && !$(marker.previousSibling).is('br') && !marker.nextSibling) {
                        $(marker).replaceWith('<br>' + $.FE.MARKERS + '<br>');
                    } else {
                        $(marker).replaceWith('<br>' + $.FE.MARKERS);
                    }
                } else {
                    $(marker).replaceWith('<' + default_tag + '>' + $.FE.MARKERS + '<br>' + '</' + default_tag + '>');
                }
            } else {
                var c_node = marker;
                var str = '';
                if (deep_parent.tagName == 'PRE' && !marker.nextSibling) shift = true;
                if (!editor.node.isBlock(deep_parent) || shift) {
                    str = '<br/>';
                }
                var c_str = '';
                var o_str = '';
                default_tag = editor.html.defaultTag();
                var open_default_tag = '';
                var close_default_tag = '';
                if (default_tag && editor.node.isBlock(deep_parent)) {
                    open_default_tag = '<' + default_tag + '>';
                    close_default_tag = '</' + default_tag + '>';
                    if (deep_parent.tagName == default_tag.toUpperCase()) {
                        open_default_tag = editor.node.openTagString($(deep_parent).clone().removeAttr('id').get(0));
                    }
                }
                do {
                    c_node = c_node.parentNode;
                    if (!shift || c_node != deep_parent || (shift && !editor.node.isBlock(deep_parent))) {
                        c_str = c_str + editor.node.closeTagString(c_node);
                        if (c_node == deep_parent && editor.node.isBlock(deep_parent)) {
                            o_str = open_default_tag + o_str;
                        } else {
                            var cls = (c_node.tagName == 'A' && _isAtEnd(marker, c_node)) ? 'fr-to-remove' : '';
                            o_str = editor.node.openTagString($(c_node).clone().addClass(cls).get(0)) + o_str;
                        }
                    }
                } while (c_node != deep_parent);
                str = c_str + str + o_str + ((marker.parentNode == deep_parent && editor.node.isBlock(deep_parent)) ? '' : $.FE.INVISIBLE_SPACE) + $.FE.MARKERS;
                if (editor.node.isBlock(deep_parent) && !$(deep_parent).find('*:last').is('br')) {
                    $(deep_parent).append('<br/>');
                }
                $(marker).after('<span id="fr-break"></span>');
                $(marker).remove();
                if ((!deep_parent.nextSibling || editor.node.isBlock(deep_parent.nextSibling)) && !editor.node.isBlock(deep_parent)) {
                    $(deep_parent).after('<br>');
                }
                var html;
                if (!shift && editor.node.isBlock(deep_parent)) {
                    html = editor.node.openTagString(deep_parent) + $(deep_parent).html() + close_default_tag;
                } else {
                    html = editor.node.openTagString(deep_parent) + $(deep_parent).html() + editor.node.closeTagString(deep_parent);
                }
                html = html.replace(/<span id="fr-break"><\/span>/g, str);
                $(deep_parent).replaceWith(html);
            }
        }

        function _startEnter(marker, shift, quote) {
            var deep_parent = editor.node.deepestParent(marker, [], !quote);
            var default_tag;
            if (deep_parent && deep_parent.tagName == 'TABLE') {
                $(deep_parent).find('td:first, th:first').prepend(marker);
                return _startEnter(marker, shift, quote);
            }
            if (deep_parent && deep_parent.tagName == 'BLOCKQUOTE') {
                if (_isAtStart(marker, deep_parent)) {
                    if (!shift) {
                        default_tag = editor.html.defaultTag();
                        if (default_tag) {
                            $(deep_parent).before('<' + default_tag + '>' + $.FE.MARKERS + '<br>' + '</' + default_tag + '>');
                        } else {
                            $(deep_parent).before($.FE.MARKERS + '<br>');
                        }
                        $(marker).remove();
                        return false;
                    }
                } else if (_isAtEnd(marker, deep_parent)) {
                    _endEnter(marker, shift, true);
                } else {
                    _middleEnter(marker, shift, true);
                }
            }
            if (deep_parent == null) {
                default_tag = editor.html.defaultTag();
                if (!default_tag || !editor.node.isElement(marker.parentNode)) {
                    $(marker).replaceWith('<br>' + $.FE.MARKERS);
                } else {
                    $(marker).replaceWith('<' + default_tag + '>' + $.FE.MARKERS + '<br>' + '</' + default_tag + '>');
                }
            } else {
                if (editor.node.isBlock(deep_parent)) {
                    if (deep_parent.tagName == 'PRE') shift = true;
                    if (shift) {
                        $(marker).remove();
                        $(deep_parent).prepend('<br>' + $.FE.MARKERS);
                    } else if (editor.node.isEmpty(deep_parent, true)) {
                        return _endEnter(marker, shift, quote);
                    } else {
                        if (!editor.opts.keepFormatOnDelete) {
                            $(deep_parent).before(editor.node.openTagString($(deep_parent).clone().removeAttr('id').get(0)) + '<br>' + editor.node.closeTagString(deep_parent));
                        } else {
                            var tmp = marker;
                            var str = $.FE.INVISIBLE_SPACE;
                            while (tmp != deep_parent && !editor.node.isElement(tmp)) {
                                tmp = tmp.parentNode;
                                str = editor.node.openTagString(tmp) + str + editor.node.closeTagString(tmp);
                            }
                            $(deep_parent).before(str);
                        }
                    }
                } else {
                    $(deep_parent).before('<br>');
                }
                $(marker).remove();
            }
        }

        function _middleEnter(marker, shift, quote) {
            var deep_parent = editor.node.deepestParent(marker, [], !quote);
            if (deep_parent == null) {
                if (editor.html.defaultTag() && marker.parentNode === editor.el) {
                    $(marker).replaceWith('<' + editor.html.defaultTag() + '>' + $.FE.MARKERS + '<br></' + editor.html.defaultTag() + '>');
                } else {
                    if ((!marker.nextSibling || editor.node.isBlock(marker.nextSibling))) {
                        $(marker).after('<br>');
                    }
                    $(marker).replaceWith('<br>' + $.FE.MARKERS);
                }
            } else {
                var c_node = marker;
                var str = '';
                if (deep_parent.tagName == 'PRE') shift = true;
                if (!editor.node.isBlock(deep_parent) || shift) {
                    str = '<br>';
                }
                var c_str = '';
                var o_str = '';
                do {
                    var tmp = c_node;
                    c_node = c_node.parentNode;
                    if (deep_parent.tagName == 'BLOCKQUOTE' && editor.node.isEmpty(tmp) && !editor.node.hasClass(tmp, 'fr-marker')) {
                        if ($(tmp).find(marker).length > 0) {
                            $(tmp).after(marker);
                        }
                    }
                    if (!(deep_parent.tagName == 'BLOCKQUOTE' && (_isAtEnd(marker, c_node) || _isAtStart(marker, c_node)))) {
                        if (!shift || c_node != deep_parent || (shift && !editor.node.isBlock(deep_parent))) {
                            c_str = c_str + editor.node.closeTagString(c_node);
                            var cls = (c_node.tagName == 'A' && _isAtEnd(marker, c_node)) ? 'fr-to-remove' : '';
                            o_str = editor.node.openTagString($(c_node).clone().addClass(cls).removeAttr('id').get(0)) + o_str;
                        } else if (deep_parent.tagName == 'BLOCKQUOTE' && shift) {
                            c_str = '';
                            o_str = '';
                        }
                    }
                } while (c_node != deep_parent);
                var add = ((deep_parent == marker.parentNode && editor.node.isBlock(deep_parent)) || marker.nextSibling);
                if (deep_parent.tagName == 'BLOCKQUOTE') {
                    if (marker.previousSibling && editor.node.isBlock(marker.previousSibling) && marker.nextSibling && marker.nextSibling.tagName == 'BR') {
                        $(marker.nextSibling).after(marker);
                        if (marker.nextSibling && marker.nextSibling.tagName == 'BR') {
                            $(marker.nextSibling).remove();
                        }
                    }
                    if (shift) {
                        str = c_str + str + $.FE.MARKERS + o_str;
                    } else {
                        var default_tag = editor.html.defaultTag();
                        str = c_str + str + (default_tag ? '<' + default_tag + '>' : '') + $.FE.MARKERS + '<br>' + (default_tag ? '</' + default_tag + '>' : '') + o_str;
                    }
                } else {
                    str = c_str + str + o_str + (add ? '' : $.FE.INVISIBLE_SPACE) + $.FE.MARKERS;
                }
                $(marker).replaceWith('<span id="fr-break"></span>');
                var html = editor.node.openTagString(deep_parent) + $(deep_parent).html() + editor.node.closeTagString(deep_parent);
                html = html.replace(/<span id="fr-break"><\/span>/g, str);
                $(deep_parent).replaceWith(html);
            }
        }

        function enter(shift) {
            var marker = editor.markers.insert();
            if (!marker) return true;
            var p_node = marker.parentNode;
            while (p_node && !editor.node.isElement(p_node)) {
                if (p_node.getAttribute('contenteditable') === 'false') {
                    $(marker).replaceWith($.FE.MARKERS);
                    editor.selection.restore();
                    return false;
                } else if (p_node.getAttribute('contenteditable') === 'true') {
                    break;
                }
                p_node = p_node.parentNode;
            }
            editor.el.normalize();
            var quote = false;
            if ($(marker).parentsUntil(editor.$el, 'BLOCKQUOTE').length > 0) {
                quote = true;
            }
            if ($(marker).parentsUntil(editor.$el, 'TD, TH').length) quote = false;
            if (_atEnd(marker)) {
                if (_inLi(marker) && !shift && !quote) {
                    editor.cursorLists._endEnter(marker);
                } else {
                    _endEnter(marker, shift, quote);
                }
            } else if (_atStart(marker)) {
                if (_inLi(marker) && !shift && !quote) {
                    editor.cursorLists._startEnter(marker);
                } else {
                    _startEnter(marker, shift, quote);
                }
            } else {
                if (_inLi(marker) && !shift && !quote) {
                    editor.cursorLists._middleEnter(marker);
                } else {
                    _middleEnter(marker, shift, quote);
                }
            }
            _cleanNodesToRemove();
            editor.html.fillEmptyBlocks(true);
            if (!editor.opts.htmlUntouched) {
                editor.html.cleanEmptyTags();
                editor.clean.lists();
                editor.spaces.normalizeAroundCursor();
            }
            editor.selection.restore();
        }

        return {enter: enter, backspace: backspace, del: del, isAtEnd: _isAtEnd, isAtStart: _isAtStart}
    }
    $.FE.ENTER_P = 0;
    $.FE.ENTER_DIV = 1;
    $.FE.ENTER_BR = 2;
    $.FE.KEYCODE = {
        BACKSPACE: 8,
        TAB: 9,
        ENTER: 13,
        SHIFT: 16,
        CTRL: 17,
        ALT: 18,
        ESC: 27,
        SPACE: 32,
        ARROW_LEFT: 37,
        ARROW_UP: 38,
        ARROW_RIGHT: 39,
        ARROW_DOWN: 40,
        DELETE: 46,
        ZERO: 48,
        ONE: 49,
        TWO: 50,
        THREE: 51,
        FOUR: 52,
        FIVE: 53,
        SIX: 54,
        SEVEN: 55,
        EIGHT: 56,
        NINE: 57,
        FF_SEMICOLON: 59,
        FF_EQUALS: 61,
        QUESTION_MARK: 63,
        A: 65,
        B: 66,
        C: 67,
        D: 68,
        E: 69,
        F: 70,
        G: 71,
        H: 72,
        I: 73,
        J: 74,
        K: 75,
        L: 76,
        M: 77,
        N: 78,
        O: 79,
        P: 80,
        Q: 81,
        R: 82,
        S: 83,
        T: 84,
        U: 85,
        V: 86,
        W: 87,
        X: 88,
        Y: 89,
        Z: 90,
        META: 91,
        NUM_ZERO: 96,
        NUM_ONE: 97,
        NUM_TWO: 98,
        NUM_THREE: 99,
        NUM_FOUR: 100,
        NUM_FIVE: 101,
        NUM_SIX: 102,
        NUM_SEVEN: 103,
        NUM_EIGHT: 104,
        NUM_NINE: 105,
        NUM_MULTIPLY: 106,
        NUM_PLUS: 107,
        NUM_MINUS: 109,
        NUM_PERIOD: 110,
        NUM_DIVISION: 111,
        F1: 112,
        F2: 113,
        F3: 114,
        F4: 115,
        F5: 116,
        F6: 117,
        F7: 118,
        F8: 119,
        F9: 120,
        F10: 121,
        F11: 122,
        F12: 123,
        FF_HYPHEN: 173,
        SEMICOLON: 186,
        DASH: 189,
        EQUALS: 187,
        COMMA: 188,
        HYPHEN: 189,
        PERIOD: 190,
        SLASH: 191,
        APOSTROPHE: 192,
        TILDE: 192,
        SINGLE_QUOTE: 222,
        OPEN_SQUARE_BRACKET: 219,
        BACKSLASH: 220,
        CLOSE_SQUARE_BRACKET: 221,
        IME: 229
    }
    $.extend($.FE.DEFAULTS, {enter: $.FE.ENTER_P, multiLine: true, tabSpaces: 0});
    $.FE.MODULES.keys = function (editor) {
        var IME = false;
        var ios_snapshot = null;

        function _enter(e) {
            if (!editor.opts.multiLine) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                if (!editor.helpers.isIOS()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                if (!editor.selection.isCollapsed()) editor.selection.remove();
                editor.cursor.enter();
            }
        }

        function _shiftEnter(e) {
            e.preventDefault();
            e.stopPropagation();
            if (editor.opts.multiLine) {
                if (!editor.selection.isCollapsed()) editor.selection.remove();
                editor.cursor.enter(true);
            }
        }

        function _ctlBackspace() {
            setTimeout(function () {
                editor.events.disableBlur();
                editor.events.focus();
            }, 0);
        }

        function _backspace(e) {
            if (editor.selection.isCollapsed()) {
                editor.cursor.backspace();
                if (editor.helpers.isIOS()) {
                    var range = editor.selection.ranges(0);
                    range.deleteContents();
                    range.insertNode(document.createTextNode('\u200B'));
                    var sel = editor.selection.get();
                    sel.modify('move', 'forward', 'character');
                } else {
                    e.preventDefault();
                    e.stopPropagation();
                }
            } else {
                e.preventDefault();
                e.stopPropagation();
                editor.selection.remove();
            }
            editor.placeholder.refresh();
        }

        function _del(e) {
            e.preventDefault();
            e.stopPropagation();
            if (editor.selection.text() === '') {
                editor.cursor.del();
            } else {
                editor.selection.remove();
            }
            editor.placeholder.refresh();
        }

        function _space(e) {
            var el = editor.selection.element();
            if (!editor.helpers.isMobile() && (el && el.tagName == 'A')) {
                e.preventDefault();
                e.stopPropagation();
                if (!editor.selection.isCollapsed()) editor.selection.remove();
                var marker = editor.markers.insert();
                if (marker) {
                    var prev_node = marker.previousSibling;
                    var next_node = marker.nextSibling;
                    if (!next_node && marker.parentNode && marker.parentNode.tagName == 'A') {
                        marker.parentNode.insertAdjacentHTML('afterend', '&nbsp;' + $.FE.MARKERS);
                        marker.parentNode.removeChild(marker);
                    } else {
                        if (prev_node && prev_node.nodeType == Node.TEXT_NODE && prev_node.textContent.length == 1 && prev_node.textContent.charCodeAt(0) == 160) {
                            prev_node.textContent = prev_node.textContent + ' ';
                        } else {
                            marker.insertAdjacentHTML('beforebegin', '&nbsp;')
                        }
                        marker.outerHTML = $.FE.MARKERS;
                    }
                    editor.selection.restore();
                }
            }
        }

        function _input() {
            if (editor.browser.mozilla && editor.selection.isCollapsed() && !IME) {
                var range = editor.selection.ranges(0);
                var start_container = range.startContainer;
                var start_offset = range.startOffset;
                if (start_container && start_container.nodeType == Node.TEXT_NODE && start_offset <= start_container.textContent.length && start_offset > 0 && start_container.textContent.charCodeAt(start_offset - 1) == 32) {
                    editor.selection.save();
                    editor.spaces.normalize();
                    editor.selection.restore();
                }
            }
        }

        function _cut() {
            if (editor.selection.isFull()) {
                setTimeout(function () {
                    var default_tag = editor.html.defaultTag();
                    if (default_tag) {
                        editor.$el.html('<' + default_tag + '>' + $.FE.MARKERS + '<br/></' + default_tag + '>');
                    } else {
                        editor.$el.html($.FE.MARKERS + '<br/>');
                    }
                    editor.selection.restore();
                    editor.placeholder.refresh();
                    editor.button.bulkRefresh();
                    editor.undo.saveStep();
                }, 0);
            }
        }

        function _tab(e) {
            if (editor.opts.tabSpaces > 0) {
                if (editor.selection.isCollapsed()) {
                    editor.undo.saveStep();
                    e.preventDefault();
                    e.stopPropagation();
                    var str = '';
                    for (var i = 0; i < editor.opts.tabSpaces; i++) str += '&nbsp;';
                    editor.html.insert(str);
                    editor.placeholder.refresh();
                    editor.undo.saveStep();
                } else {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!e.shiftKey) {
                        editor.commands.indent();
                    } else {
                        editor.commands.outdent();
                    }
                }
            }
        }

        function _mapKeyPress() {
            IME = false;
        }

        function _clearIME() {
            IME = false;
        }

        function isIME() {
            return IME;
        }

        var key_down_code;

        function _empty() {
            var default_tag = editor.html.defaultTag();
            if (default_tag) {
                editor.$el.html('<' + default_tag + '>' + $.FE.MARKERS + '<br/></' + default_tag + '>');
            } else {
                editor.$el.html($.FE.MARKERS + '<br/>');
            }
            editor.selection.restore();
        }

        function _mapKeyDown(e) {
            var sel_el = editor.selection.element();
            if (sel_el && ['INPUT', 'TEXTAREA'].indexOf(sel_el.tagName) >= 0) return true;
            if (e && isArrow(e.which)) {
                _removeInvisible();
                return true;
            }
            editor.events.disableBlur();
            ios_snapshot = null;
            var key_code = e.which;
            if (key_code === 16) return true;
            key_down_code = key_code;
            if (key_code === $.FE.KEYCODE.IME) {
                IME = true;
                return true;
            } else {
                IME = false;
            }
            var char_key = (isCharacter(key_code) && !ctrlKey(e) && !e.altKey);
            var del_key = (key_code == $.FE.KEYCODE.BACKSPACE || key_code == $.FE.KEYCODE.DELETE);
            var selection_key = (e.shiftKey && (key_code === 33 || key_code === 34 || key_code === 35 || key_code === 36));
            if ((!selection_key && editor.selection.isFull() && !editor.opts.keepFormatOnDelete && !editor.placeholder.isVisible()) || (del_key && editor.placeholder.isVisible() && editor.opts.keepFormatOnDelete)) {
                if (char_key || del_key) {
                    _empty();
                    if (!isCharacter(key_code)) {
                        e.preventDefault();
                        return true;
                    }
                }
            }
            if (key_code == $.FE.KEYCODE.ENTER) {
                if (e.shiftKey) {
                    _shiftEnter(e);
                } else {
                    _enter(e);
                }
            } else if (key_code === $.FE.KEYCODE.BACKSPACE && (e.metaKey || e.ctrlKey)) {
                _ctlBackspace();
            } else if (key_code == $.FE.KEYCODE.BACKSPACE && !ctrlKey(e) && !e.altKey) {
                if (!editor.placeholder.isVisible()) {
                    _backspace(e);
                } else {
                    if (!editor.opts.keepFormatOnDelete) {
                        _empty();
                    }
                    e.preventDefault();
                    e.stopPropagation();
                }
            } else if (key_code == $.FE.KEYCODE.DELETE && !ctrlKey(e) && !e.altKey && !e.shiftKey) {
                if (!editor.placeholder.isVisible()) {
                    _del(e);
                } else {
                    if (!editor.opts.keepFormatOnDelete) {
                        _empty();
                    }
                    e.preventDefault();
                    e.stopPropagation();
                }
            } else if (key_code == $.FE.KEYCODE.SPACE) {
                _space(e);
            } else if (key_code == $.FE.KEYCODE.TAB) {
                _tab(e);
            } else if (!ctrlKey(e) && isCharacter(e.which) && !editor.selection.isCollapsed() && !e.ctrlKey && !e.altKey) {
                editor.selection.remove();
            }
            editor.events.enableBlur();
        }

        function _replaceU200B(el) {
            var walker = editor.doc.createTreeWalker(el, NodeFilter.SHOW_TEXT, editor.node.filter(function (node) {
                return /\u200B/gi.test(node.textContent);
            }), false);
            while (walker.nextNode()) {
                var node = walker.currentNode;
                node.textContent = node.textContent.replace(/\u200B/gi, '');
            }
        }

        function positionCaret() {
            if (!editor.$wp) return true;
            var info;
            if (!editor.opts.height && !editor.opts.heightMax) {
                info = editor.position.getBoundingRect().top;
                if (editor.opts.toolbarBottom) info += editor.opts.toolbarStickyOffset;
                if (editor.opts.iframe) {
                    info += editor.$iframe.offset().top;
                    info -= editor.helpers.scrollTop();
                }
                info += editor.opts.toolbarStickyOffset;
                if (info > editor.o_win.innerHeight - 20) {
                    $(editor.o_win).scrollTop(info + editor.helpers.scrollTop() - editor.o_win.innerHeight + 20);
                }
                info = editor.position.getBoundingRect().top;
                if (!editor.opts.toolbarBottom) info -= editor.opts.toolbarStickyOffset;
                if (editor.opts.iframe) {
                    info += editor.$iframe.offset().top;
                    info -= editor.helpers.scrollTop();
                }
                if (info < editor.$tb.height() + 20) {
                    $(editor.o_win).scrollTop(info + editor.helpers.scrollTop() - editor.$tb.height() - 20);
                }
            } else {
                info = editor.position.getBoundingRect().top;
                if (editor.opts.iframe) {
                    info += editor.$iframe.offset().top;
                }
                if (info > editor.$wp.offset().top - editor.helpers.scrollTop() + editor.$wp.height() - 20) {
                    editor.$wp.scrollTop(info + editor.$wp.scrollTop() - (editor.$wp.height() + editor.$wp.offset().top) + editor.helpers.scrollTop() + 20);
                }
            }
        }

        function _removeInvisible() {
            var has_invisible = function (node) {
                if (!node) return false;
                var text = node.innerHTML;
                text = text.replace(/<span[^>]*? class\s*=\s*["']?fr-marker["']?[^>]+>\u200b<\/span>/gi, '');
                if (text && /\u200B/.test(text) && text.replace(/\u200B/gi, '').length > 0) return true;
                return false;
            }
            var ios_CJK = function (el) {
                var CJKRegEx = /[\u3041-\u3096\u30A0-\u30FF\u4E00-\u9FFF\u3130-\u318F\uAC00-\uD7AF]/gi;
                return !editor.helpers.isIOS() || ((el.textContent || '').match(CJKRegEx) || []).length === 0;
            }
            var el = editor.selection.element();
            if (has_invisible(el) && !editor.node.hasClass(el, 'fr-marker') && el.tagName != 'IFRAME' && ios_CJK(el)) {
                editor.selection.save();
                _replaceU200B(el);
                editor.selection.restore();
            }
        }

        function _mapKeyUp(e) {
            var sel_el = editor.selection.element();
            if (sel_el && ['INPUT', 'TEXTAREA'].indexOf(sel_el.tagName) >= 0) return true;
            if (e && e.which === 0 && key_down_code) {
                e.which = key_down_code;
            }
            if (editor.helpers.isAndroid() && editor.browser.mozilla) {
                return true;
            }
            if (IME) {
                return false;
            }
            if (e && editor.helpers.isIOS() && e.which == $.FE.KEYCODE.ENTER) {
                editor.doc.execCommand('undo')
            }
            if (!editor.selection.isCollapsed()) return true;
            if (e && (e.which === $.FE.KEYCODE.META || e.which == $.FE.KEYCODE.CTRL)) return true;
            if (e && isArrow(e.which)) return true;
            if (e && !editor.helpers.isIOS() && (e.which == $.FE.KEYCODE.ENTER || e.which == $.FE.KEYCODE.BACKSPACE || (e.which >= 37 && e.which <= 40 && !editor.browser.msie))) {
                try {
                    positionCaret();
                } catch (ex) {
                }
            }
            _removeInvisible()
        }

        function ctrlKey(e) {
            if (navigator.userAgent.indexOf('Mac OS X') != -1) {
                if (e.metaKey && !e.altKey) return true;
            } else {
                if (e.ctrlKey && !e.altKey) return true;
            }
            return false;
        }

        function isArrow(key_code) {
            if (key_code >= $.FE.KEYCODE.ARROW_LEFT && key_code <= $.FE.KEYCODE.ARROW_DOWN) {
                return true;
            }
        }

        function isCharacter(key_code) {
            if (key_code >= $.FE.KEYCODE.ZERO && key_code <= $.FE.KEYCODE.NINE) {
                return true;
            }
            if (key_code >= $.FE.KEYCODE.NUM_ZERO && key_code <= $.FE.KEYCODE.NUM_MULTIPLY) {
                return true;
            }
            if (key_code >= $.FE.KEYCODE.A && key_code <= $.FE.KEYCODE.Z) {
                return true;
            }
            if (editor.browser.webkit && key_code === 0) {
                return true;
            }
            switch (key_code) {
                case $.FE.KEYCODE.SPACE:
                case $.FE.KEYCODE.QUESTION_MARK:
                case $.FE.KEYCODE.NUM_PLUS:
                case $.FE.KEYCODE.NUM_MINUS:
                case $.FE.KEYCODE.NUM_PERIOD:
                case $.FE.KEYCODE.NUM_DIVISION:
                case $.FE.KEYCODE.SEMICOLON:
                case $.FE.KEYCODE.FF_SEMICOLON:
                case $.FE.KEYCODE.DASH:
                case $.FE.KEYCODE.EQUALS:
                case $.FE.KEYCODE.FF_EQUALS:
                case $.FE.KEYCODE.COMMA:
                case $.FE.KEYCODE.PERIOD:
                case $.FE.KEYCODE.SLASH:
                case $.FE.KEYCODE.APOSTROPHE:
                case $.FE.KEYCODE.SINGLE_QUOTE:
                case $.FE.KEYCODE.OPEN_SQUARE_BRACKET:
                case $.FE.KEYCODE.BACKSLASH:
                case $.FE.KEYCODE.CLOSE_SQUARE_BRACKET:
                    return true;
                default:
                    return false;
            }
        }

        var _typing_timeout;
        var _temp_snapshot;

        function _typingKeyDown(e) {
            var keycode = e.which;
            if (ctrlKey(e) || (keycode >= 37 && keycode <= 40) || (!isCharacter(keycode) && keycode != $.FE.KEYCODE.DELETE && keycode != $.FE.KEYCODE.BACKSPACE && keycode != $.FE.KEYCODE.ENTER && keycode != $.FE.KEYCODE.IME)) return true;
            if (!_typing_timeout) {
                _temp_snapshot = editor.snapshot.get();
                if (!editor.undo.canDo()) editor.undo.saveStep();
            }
            clearTimeout(_typing_timeout);
            _typing_timeout = setTimeout(function () {
                _typing_timeout = null;
                editor.undo.saveStep();
            }, Math.max(250, editor.opts.typingTimer));
        }

        function _typingKeyUp(e) {
            var keycode = e.which;
            if (ctrlKey(e) || (keycode >= 37 && keycode <= 40)) return true;
            if (_temp_snapshot && _typing_timeout) {
                editor.undo.saveStep(_temp_snapshot);
                _temp_snapshot = null;
            } else {
                if ((typeof keycode === 'undefined' || keycode === 0) && !_temp_snapshot && !_typing_timeout) {
                    editor.undo.saveStep();
                }
            }
        }

        function forceUndo() {
            if (_typing_timeout) {
                clearTimeout(_typing_timeout);
                editor.undo.saveStep();
                _temp_snapshot = null;
            }
        }

        function isBrowserAction(e) {
            var keycode = e.which;
            return ctrlKey(e) || keycode == $.FE.KEYCODE.F5;
        }

        function _isEmpty(node) {
            if (node && node.tagName == 'BR') return false;
            try {
                return ((node.textContent || '').length === 0 && node.querySelector && !node.querySelector(':scope > br')) || (node.childNodes && node.childNodes.length == 1 && node.childNodes[0].getAttribute && (node.childNodes[0].getAttribute('contenteditable') == 'false' || editor.node.hasClass(node.childNodes[0], 'fr-img-caption')));
            } catch (ex) {
                return false;
            }
        }

        function _allowTypingOnEdges(e) {
            var childs = editor.el.childNodes;
            var dt = editor.html.defaultTag();
            if (e.target && e.target !== editor.el) return true;
            if (childs.length === 0) return true;
            if (editor.$el.outerHeight() - e.offsetY <= 10) {
                if (_isEmpty(childs[childs.length - 1])) {
                    if (dt) {
                        editor.$el.append('<' + dt + '>' + $.FE.MARKERS + '<br></' + dt + '>');
                    } else {
                        editor.$el.append($.FE.MARKERS + '<br>');
                    }
                    editor.selection.restore();
                    positionCaret();
                }
            } else if (e.offsetY <= 10) {
                if (_isEmpty(childs[0])) {
                    if (dt) {
                        editor.$el.prepend('<' + dt + '>' + $.FE.MARKERS + '<br></' + dt + '>');
                    } else {
                        editor.$el.prepend($.FE.MARKERS + '<br>');
                    }
                    editor.selection.restore();
                    positionCaret();
                }
            }
        }

        function _clearTypingTimer() {
            if (_typing_timeout) {
                clearTimeout(_typing_timeout);
            }
        }

        function _init() {
            editor.events.on('keydown', _typingKeyDown);
            editor.events.on('input', _input);
            editor.events.on('mousedown', _clearIME);
            editor.events.on('keyup input', _typingKeyUp);
            editor.events.on('keypress', _mapKeyPress);
            editor.events.on('keydown', _mapKeyDown);
            editor.events.on('keyup', _mapKeyUp);
            editor.events.on('destroy', _clearTypingTimer);
            editor.events.on('html.inserted', _mapKeyUp);
            editor.events.on('cut', _cut);
            if (editor.opts.multiLine) {
                editor.events.on('click', _allowTypingOnEdges);
            }
        }

        return {
            _init: _init,
            ctrlKey: ctrlKey,
            isCharacter: isCharacter,
            isArrow: isArrow,
            forceUndo: forceUndo,
            isIME: isIME,
            isBrowserAction: isBrowserAction,
            positionCaret: positionCaret
        }
    };
    $.FE.MODULES.accessibility = function (editor) {
        var can_blur = true;

        function focusToolbarElement($el) {
            if (!$el || !$el.length || editor.$el.find('[contenteditable="true"]').is(':focus')) {
                return;
            }
            if (!$el.data('blur-event-set') && !$el.parents('.fr-popup').length) {
                editor.events.$on($el, 'blur', function () {
                    var inst = $el.parents('.fr-toolbar, .fr-popup').data('instance') || editor;
                    if (inst.events.blurActive() && !editor.core.hasFocus()) {
                        inst.events.trigger('blur');
                    }
                    setTimeout(function () {
                        inst.events.enableBlur();
                    }, 100);
                }, true);
                $el.data('blur-event-set', true);
            }
            var inst = $el.parents('.fr-toolbar, .fr-popup').data('instance') || editor;
            inst.events.disableBlur();
            $el.focus();
            editor.shared.$f_el = $el;
        }

        function focusToolbar($tb, last) {
            var position = last ? 'last' : 'first';
            var $btn = $tb.find('button:visible:not(.fr-disabled), .fr-group span.fr-command:visible')[position]();
            if ($btn.length) {
                focusToolbarElement($btn);
                return true;
            }
        }

        function focusContentElement($el) {
            if ($el.is('input, textarea, select')) {
                saveSelection();
            }
            editor.events.disableBlur();
            $el.focus();
            return true;
        }

        function focusContent($content, backward) {
            var $first_input = $content.find('input, textarea, button, select').filter(':visible').not(':disabled').filter(backward ? ':last' : ':first');
            if ($first_input.length) {
                return focusContentElement($first_input);
            }
            if (editor.shared.with_kb) {
                var $active_item = $content.find('.fr-active-item:visible:first');
                if ($active_item.length) {
                    return focusContentElement($active_item);
                }
                var $first_tab_index = $content.find('[tabIndex]:visible:first')
                if ($first_tab_index.length) {
                    return focusContentElement($first_tab_index);
                }
            }
        }

        function saveSelection() {
            if (editor.$el.find('.fr-marker').length === 0 && editor.core.hasFocus()) {
                editor.selection.save();
            }
        }

        function restoreSelection() {
            if (editor.$el.find('.fr-marker').length) {
                editor.events.disableBlur();
                editor.selection.restore();
                editor.events.enableBlur();
            }
        }

        function focusPopup($popup) {
            var $popup_content = $popup.children().not('.fr-buttons');
            if (!$popup_content.data('mouseenter-event-set') && !editor.browser.msie) {
                editor.events.$on($popup_content, 'mouseenter', '[tabIndex]', function (e) {
                    var inst = $popup.data('instance') || editor;
                    if (!can_blur) {
                        e.stopPropagation();
                        e.preventDefault();
                        return;
                    }
                    var $focused_item = $popup_content.find(':focus:first');
                    if ($focused_item.length && !$focused_item.is('input, button, textarea, select')) {
                        inst.events.disableBlur();
                        $focused_item.blur();
                        inst.events.disableBlur();
                        inst.events.focus();
                    }
                });
                $popup_content.data('mouseenter-event-set', true);
            }
            if (!focusContent($popup_content) && editor.shared.with_kb) {
                focusToolbar($popup.find('.fr-buttons'));
            }
        }

        function focusModal($modal) {
            if (!editor.core.hasFocus()) {
                editor.events.disableBlur();
                editor.events.focus();
            }
            editor.accessibility.saveSelection();
            editor.events.disableBlur();
            editor.$el.blur();
            editor.selection.clear();
            editor.events.disableBlur();
            if (editor.shared.with_kb) {
                $modal.find('.fr-command[tabIndex], [tabIndex]').first().focus();
            } else {
                $modal.find('[tabIndex]:first').focus();
            }
        }

        function focusToolbars() {
            var $popup = editor.popups.areVisible();
            if ($popup) {
                var $tb = $popup.find('.fr-buttons');
                if (!$tb.find('button:focus, .fr-group span:focus').length) {
                    return !focusToolbar($tb);
                } else {
                    return !focusToolbar($popup.data('instance').$tb)
                }
            }
            return !focusToolbar(editor.$tb);
        }

        function _getActiveFocusedDropdown() {
            var $activeDropdown = null;
            if (editor.shared.$f_el.is('.fr-dropdown.fr-active')) {
                $activeDropdown = editor.shared.$f_el;
            } else if (editor.shared.$f_el.closest('.fr-dropdown-menu').prev().is('.fr-dropdown.fr-active')) {
                $activeDropdown = editor.shared.$f_el.closest('.fr-dropdown-menu').prev();
            }
            return $activeDropdown;
        }

        function _moveHorizontally($tb, tab_key, forward) {
            if (editor.shared.$f_el) {
                var $activeDropdown = _getActiveFocusedDropdown();
                if ($activeDropdown) {
                    editor.button.click($activeDropdown);
                    editor.shared.$f_el = $activeDropdown;
                }
                var $buttons = $tb.find('button:visible:not(.fr-disabled), .fr-group span.fr-command:visible');
                var index = $buttons.index(editor.shared.$f_el);
                if ((index === 0 && !forward) || (index == $buttons.length - 1 && forward)) {
                    var status;
                    if (tab_key) {
                        if ($tb.parent().is('.fr-popup')) {
                            var $popup_content = $tb.parent().children().not('.fr-buttons')
                            status = !focusContent($popup_content, !forward);
                        }
                        if (status === false) {
                            editor.shared.$f_el = null;
                        }
                    }
                    if (!tab_key || status !== false) {
                        focusToolbar($tb, !forward);
                    }
                } else {
                    focusToolbarElement($($buttons.get(index + (forward ? 1 : -1))));
                }
                return false;
            }
        }

        function moveForward($tb, tab_key) {
            return _moveHorizontally($tb, tab_key, true);
        }

        function moveBackward($tb, tab_key) {
            return _moveHorizontally($tb, tab_key);
        }

        function _moveVertically(down) {
            if (editor.shared.$f_el) {
                var $destination;
                if (editor.shared.$f_el.is('.fr-dropdown.fr-active')) {
                    if (down) {
                        $destination = editor.shared.$f_el.next().find('.fr-command:not(.fr-disabled)').first();
                    } else {
                        $destination = editor.shared.$f_el.next().find('.fr-command:not(.fr-disabled)').last();
                    }
                    focusToolbarElement($destination);
                    return false;
                } else if (editor.shared.$f_el.is('a.fr-command')) {
                    if (down) {
                        $destination = editor.shared.$f_el.closest('li').nextAll(':visible:first').find('.fr-command:not(.fr-disabled)').first();
                    } else {
                        $destination = editor.shared.$f_el.closest('li').prevAll(':visible:first').find('.fr-command:not(.fr-disabled)').first();
                    }
                    if (!$destination.length) {
                        if (down) {
                            $destination = editor.shared.$f_el.closest('.fr-dropdown-menu').find('.fr-command:not(.fr-disabled)').first();
                        } else {
                            $destination = editor.shared.$f_el.closest('.fr-dropdown-menu').find('.fr-command:not(.fr-disabled)').last();
                        }
                    }
                    focusToolbarElement($destination);
                    return false;
                }
            }
        }

        function moveDown() {
            if (editor.shared.$f_el && editor.shared.$f_el.is('.fr-dropdown:not(.fr-active)')) {
                return enter();
            } else {
                return _moveVertically(true);
            }
        }

        function moveUp() {
            return _moveVertically();
        }

        function enter() {
            if (editor.shared.$f_el) {
                if (editor.shared.$f_el.hasClass('fr-dropdown')) {
                    editor.button.click(editor.shared.$f_el);
                } else if (editor.shared.$f_el.is('button.fr-back')) {
                    if (editor.opts.toolbarInline) {
                        editor.events.disableBlur();
                        editor.events.focus();
                    }
                    var $popup = editor.popups.areVisible(editor);
                    if ($popup) {
                        editor.shared.with_kb = false;
                    }
                    editor.button.click(editor.shared.$f_el);
                    focusPopupButton($popup);
                } else {
                    editor.events.disableBlur();
                    editor.button.click(editor.shared.$f_el);
                    if (editor.shared.$f_el.attr('data-popup')) {
                        var $visible_popup = editor.popups.areVisible(editor);
                        if ($visible_popup) $visible_popup.data('popup-button', editor.shared.$f_el);
                    } else if (editor.shared.$f_el.attr('data-modal')) {
                        var $visible_modal = editor.modals.areVisible(editor);
                        if ($visible_modal) $visible_modal.data('modal-button', editor.shared.$f_el);
                    }
                    editor.shared.$f_el = null;
                }
                return false;
            }
        }

        function focusEditor() {
            if (editor.shared.$f_el) {
                editor.events.disableBlur();
                editor.shared.$f_el.blur();
                editor.shared.$f_el = null;
            }
            if (editor.events.trigger('toolbar.focusEditor') === false) {
                return;
            }
            editor.events.disableBlur();
            if (!editor.browser.msie) editor.$el.focus();
            editor.events.focus();
        }

        function esc($tb) {
            if (editor.shared.$f_el) {
                var $activeDropdown = _getActiveFocusedDropdown();
                if ($activeDropdown) {
                    editor.button.click($activeDropdown);
                    focusToolbarElement($activeDropdown);
                } else if ($tb.parent().find('.fr-back:visible').length) {
                    editor.shared.with_kb = false;
                    if (editor.opts.toolbarInline) {
                        editor.events.disableBlur();
                        editor.events.focus();
                    }
                    editor.button.exec($tb.parent().find('.fr-back:visible:first'));
                    focusPopupButton($tb.parent());
                } else if (editor.shared.$f_el.is('button, .fr-group span')) {
                    if ($tb.parent().is('.fr-popup')) {
                        editor.accessibility.restoreSelection();
                        editor.shared.$f_el = null;
                        if (editor.events.trigger('toolbar.esc') !== false) {
                            editor.popups.hide($tb.parent());
                            if (editor.opts.toolbarInline) editor.toolbar.showInline(null, true);
                            focusPopupButton($tb.parent());
                        }
                    } else {
                        focusEditor();
                    }
                }
                return false;
            }
        }

        function exec(e, $tb) {
            var ctrlKey = navigator.userAgent.indexOf('Mac OS X') != -1 ? e.metaKey : e.ctrlKey;
            var keycode = e.which;
            var status = false;
            if (keycode == $.FE.KEYCODE.TAB && !ctrlKey && !e.shiftKey && !e.altKey) {
                status = moveForward($tb, true);
            } else if (keycode == $.FE.KEYCODE.ARROW_RIGHT && !ctrlKey && !e.shiftKey && !e.altKey) {
                status = moveForward($tb);
            } else if (keycode == $.FE.KEYCODE.TAB && !ctrlKey && e.shiftKey && !e.altKey) {
                status = moveBackward($tb, true);
            } else if (keycode == $.FE.KEYCODE.ARROW_LEFT && !ctrlKey && !e.shiftKey && !e.altKey) {
                status = moveBackward($tb);
            } else if (keycode == $.FE.KEYCODE.ARROW_UP && !ctrlKey && !e.shiftKey && !e.altKey) {
                status = moveUp();
            } else if (keycode == $.FE.KEYCODE.ARROW_DOWN && !ctrlKey && !e.shiftKey && !e.altKey) {
                status = moveDown();
            } else if ((keycode == $.FE.KEYCODE.ENTER || keycode == $.FE.KEYCODE.SPACE) && !ctrlKey && !e.shiftKey && !e.altKey) {
                status = enter();
            } else if (keycode == $.FE.KEYCODE.ESC && !ctrlKey && !e.shiftKey && !e.altKey) {
                status = esc($tb);
            } else if (keycode == $.FE.KEYCODE.F10 && !ctrlKey && !e.shiftKey && e.altKey) {
                status = focusToolbars();
            }
            if (!editor.shared.$f_el && status === undefined) {
                status = true;
            }
            if (!status && editor.keys.isBrowserAction(e)) {
                status = true;
            }
            if (status) {
                return true;
            } else {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }

        function registerToolbar($tb) {
            if (!$tb || !$tb.length) {
                return;
            }
            editor.events.$on($tb, 'keydown', function (e) {
                if (!$(e.target).is('a.fr-command, button.fr-command, .fr-group span.fr-command')) {
                    return true;
                }
                var inst = $tb.parents('.fr-popup').data('instance') || $tb.data('instance') || editor;
                editor.shared.with_kb = true;
                var status = inst.accessibility.exec(e, $tb);
                editor.shared.with_kb = false;
                return status;
            }, true);
            if (!editor.browser.msie) {
                editor.events.$on($tb, 'mouseenter', '[tabIndex]', function (e) {
                    var inst = $tb.parents('.fr-popup').data('instance') || $tb.data('instance') || editor;
                    if (!can_blur) {
                        e.stopPropagation();
                        e.preventDefault();
                        return;
                    } else {
                        var $hovered_el = $(e.currentTarget);
                        if (inst.shared.$f_el && inst.shared.$f_el.not($hovered_el)) {
                            inst.accessibility.focusEditor();
                        }
                    }
                }, true);
            }
        }

        function registerPopup(id) {
            var $popup = editor.popups.get(id);
            var ev = _getPopupEvents(id);
            registerToolbar($popup.find('.fr-buttons'));
            editor.events.$on($popup, 'mouseenter', 'tabIndex', ev._tiMouseenter, true);
            editor.events.$on($popup.children().not('.fr-buttons'), 'keydown', '[tabIndex]', ev._tiKeydown, true);
            editor.popups.onHide(id, function () {
                var inst = $popup.data('instance') || editor;
                inst.accessibility.restoreSelection();
            })
            editor.popups.onShow(id, function () {
                can_blur = false;
                setTimeout(function () {
                    can_blur = true;
                }, 0);
            });
        }

        function _getPopupEvents(id) {
            var $popup = editor.popups.get(id);
            return {
                _tiKeydown: function (e) {
                    var inst = $popup.data('instance') || editor;
                    if (inst.events.trigger('popup.tab', [e]) === false) {
                        return false;
                    }
                    var key_code = e.which;
                    var $focused_item = $popup.find(':focus:first');
                    if ($.FE.KEYCODE.TAB == key_code) {
                        e.preventDefault();
                        var $popup_content = $popup.children().not('.fr-buttons');
                        var inputs = $popup_content.find('input, textarea, button, select').filter(':visible').not('.fr-no-touch input, .fr-no-touch textarea, .fr-no-touch button, .fr-no-touch select, :disabled').toArray();
                        var idx = inputs.indexOf(this) + (e.shiftKey ? -1 : 1);
                        if (0 <= idx && idx < inputs.length) {
                            inst.events.disableBlur();
                            $(inputs[idx]).focus();
                            e.stopPropagation();
                            return false;
                        }
                        var $tb = $popup.find('.fr-buttons');
                        if ($tb.length && focusToolbar($tb, (e.shiftKey ? true : false))) {
                            e.stopPropagation();
                            return false;
                        }
                        if (focusContent($popup_content)) {
                            e.stopPropagation();
                            return false;
                        }
                    } else if ($.FE.KEYCODE.ENTER == key_code && e.target && e.target.tagName !== 'TEXTAREA') {
                        var $active_button = null;
                        if ($popup.find('.fr-submit:visible').length > 0) {
                            $active_button = $popup.find('.fr-submit:visible:first');
                        } else if ($popup.find('.fr-dismiss:visible').length) {
                            $active_button = $popup.find('.fr-dismiss:visible:first');
                        }
                        if ($active_button) {
                            e.preventDefault();
                            e.stopPropagation();
                            inst.events.disableBlur();
                            inst.button.exec($active_button);
                        }
                    } else if ($.FE.KEYCODE.ESC == key_code) {
                        e.preventDefault();
                        e.stopPropagation();
                        inst.accessibility.restoreSelection();
                        if (inst.popups.isVisible(id) && $popup.find('.fr-back:visible').length) {
                            if (inst.opts.toolbarInline) {
                                inst.events.disableBlur();
                                inst.events.focus();
                            }
                            inst.button.exec($popup.find('.fr-back:visible:first'));
                            focusPopupButton($popup);
                        } else if (inst.popups.isVisible(id) && $popup.find('.fr-dismiss:visible').length) {
                            inst.button.exec($popup.find('.fr-dismiss:visible:first'));
                        } else {
                            inst.popups.hide(id);
                            if (inst.opts.toolbarInline) inst.toolbar.showInline(null, true);
                            focusPopupButton($popup);
                        }
                        return false;
                    } else if ($.FE.KEYCODE.SPACE == key_code && ($focused_item.is('.fr-submit') || $focused_item.is('.fr-dismiss'))) {
                        e.preventDefault();
                        e.stopPropagation();
                        inst.events.disableBlur();
                        inst.button.exec($focused_item);
                        return true;
                    } else {
                        if (inst.keys.isBrowserAction(e)) {
                            e.stopPropagation();
                            return;
                        }
                        if ($focused_item.is('input[type=text], textarea')) {
                            e.stopPropagation();
                            return;
                        }
                        if ($.FE.KEYCODE.SPACE == key_code && ($focused_item.is('.fr-link-attr') || $focused_item.is('input[type=file]'))) {
                            e.stopPropagation();
                            return;
                        }
                        e.stopPropagation();
                        e.preventDefault();
                        return false;
                    }
                }, _tiMouseenter: function () {
                    var inst = $popup.data('instance') || editor;
                    _clearPopupButton(inst);
                }
            }
        }

        function focusPopupButton($popup) {
            var $popup_button = $popup.data('popup-button');
            if ($popup_button) {
                setTimeout(function () {
                    focusToolbarElement($popup_button);
                    $popup.data('popup-button', null);
                }, 0);
            }
        }

        function focusModalButton($modal) {
            var $modal_button = $modal.data('modal-button');
            if ($modal_button) {
                setTimeout(function () {
                    focusToolbarElement($modal_button);
                    $modal.data('modal-button', null);
                }, 0);
            }
        }

        function hasFocus() {
            return editor.shared.$f_el != null;
        }

        function _clearPopupButton(inst) {
            var $visible_popup = editor.popups.areVisible(inst);
            if ($visible_popup) {
                $visible_popup.data('popup-button', null);
            }
        }

        function _editorKeydownHandler(e) {
            var ctrlKey = navigator.userAgent.indexOf('Mac OS X') != -1 ? e.metaKey : e.ctrlKey;
            var keycode = e.which;
            if (keycode == $.FE.KEYCODE.F10 && !ctrlKey && !e.shiftKey && e.altKey) {
                editor.shared.with_kb = true;
                var $visible_popup = editor.popups.areVisible(editor);
                var focused_content = false;
                if ($visible_popup) {
                    focused_content = focusContent($visible_popup.children().not('.fr-buttons'));
                }
                if (!focused_content) {
                    focusToolbars();
                }
                editor.shared.with_kb = false;
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            return true;
        }

        function _init() {
            if (editor.$wp) {
                editor.events.on('keydown', _editorKeydownHandler, true);
            } else {
                editor.events.$on(editor.$win, 'keydown', _editorKeydownHandler, true);
            }
            editor.events.on('mousedown', function (e) {
                _clearPopupButton(editor);
                if (editor.shared.$f_el) {
                    editor.accessibility.restoreSelection();
                    e.stopPropagation();
                    editor.events.disableBlur();
                    editor.shared.$f_el = null;
                }
            }, true);
            editor.events.on('blur', function () {
                editor.shared.$f_el = null;
                _clearPopupButton(editor);
            }, true);
        }

        return {
            _init: _init,
            registerPopup: registerPopup,
            registerToolbar: registerToolbar,
            focusToolbarElement: focusToolbarElement,
            focusToolbar: focusToolbar,
            focusContent: focusContent,
            focusPopup: focusPopup,
            focusModal: focusModal,
            focusEditor: focusEditor,
            focusPopupButton: focusPopupButton,
            focusModalButton: focusModalButton,
            hasFocus: hasFocus,
            exec: exec,
            saveSelection: saveSelection,
            restoreSelection: restoreSelection
        }
    }
    $.FE.MODULES.format = function (editor) {
        function _openTag(tag, attrs) {
            var str = '<' + tag;
            for (var key in attrs) {
                if (attrs.hasOwnProperty(key)) {
                    str += ' ' + key + '="' + attrs[key] + '"';
                }
            }
            str += '>';
            return str;
        }

        function _closeTag(tag) {
            return '</' + tag + '>';
        }

        function _query(tag, attrs) {
            var selector = tag;
            for (var key in attrs) {
                if (attrs.hasOwnProperty(key)) {
                    if (key == 'id') selector += '#' + attrs[key]; else if (key == 'class') selector += '.' + attrs[key]; else selector += '[' + key + '="' + attrs[key] + '"]';
                }
            }
            return selector;
        }

        function _matches(el, selector) {
            if (!el || el.nodeType != Node.ELEMENT_NODE) return false;
            return (el.matches || el.matchesSelector || el.msMatchesSelector || el.mozMatchesSelector || el.webkitMatchesSelector || el.oMatchesSelector).call(el, selector);
        }

        function _processNodeFormat(start_node, tag, attrs) {
            if (!start_node) return;
            while (start_node.nodeType === Node.COMMENT_NODE) {
                start_node = start_node.nextSibling;
            }
            if (!start_node) return;
            if (editor.node.isBlock(start_node) && start_node.tagName !== 'HR') {
                _processNodeFormat(start_node.firstChild, tag, attrs);
                return false;
            }
            var $span = $(_openTag(tag, attrs)).insertBefore(start_node);
            var node = start_node;
            while (node && !$(node).is('.fr-marker') && $(node).find('.fr-marker').length === 0 && node.tagName != 'UL' && node.tagName != 'OL') {
                var tmp = node;
                if (editor.node.isBlock(node) && start_node.tagName !== 'HR') {
                    _processNodeFormat(node.firstChild, tag, attrs);
                    return false;
                }
                node = node.nextSibling;
                $span.append(tmp);
            }
            if (!node) {
                var p_node = $span.get(0).parentNode;
                while (p_node && !p_node.nextSibling && !editor.node.isElement(p_node)) {
                    p_node = p_node.parentNode;
                }
                if (p_node) {
                    var sibling = p_node.nextSibling;
                    if (sibling) {
                        if (!editor.node.isBlock(sibling)) {
                            _processNodeFormat(sibling, tag, attrs);
                        } else if (sibling.tagName === 'HR') {
                            _processNodeFormat(sibling.nextSibling, tag, attrs);
                        } else {
                            _processNodeFormat(sibling.firstChild, tag, attrs);
                        }
                    }
                }
            } else if ($(node).find('.fr-marker').length || node.tagName == 'UL' || node.tagName == 'OL') {
                _processNodeFormat(node.firstChild, tag, attrs);
            }
            if ($span.is(':empty')) {
                $span.remove();
            }
        }

        function apply(tag, attrs) {
            var i;
            if (typeof attrs == 'undefined') attrs = {};
            if (attrs.style) {
                delete attrs.style;
            }
            if (editor.selection.isCollapsed()) {
                editor.markers.insert();
                var $marker = editor.$el.find('.fr-marker');
                $marker.replaceWith(_openTag(tag, attrs) + $.FE.INVISIBLE_SPACE + $.FE.MARKERS + _closeTag(tag));
                editor.selection.restore();
            } else {
                editor.selection.save();
                var start_marker = editor.$el.find('.fr-marker[data-type="true"]').get(0).nextSibling;
                _processNodeFormat(start_marker, tag, attrs);
                var inner_spans;
                do {
                    inner_spans = editor.$el.find(_query(tag, attrs) + ' > ' + _query(tag, attrs));
                    for (i = 0; i < inner_spans.length; i++) {
                        inner_spans[i].outerHTML = inner_spans[i].innerHTML;
                    }
                } while (inner_spans.length);
                editor.el.normalize();
                var markers = editor.el.querySelectorAll('.fr-marker');
                for (i = 0; i < markers.length; i++) {
                    var $mk = $(markers[i]);
                    if ($mk.data('type') === true) {
                        if (_matches($mk.get(0).nextSibling, _query(tag, attrs))) {
                            $mk.next().prepend($mk);
                        }
                    } else {
                        if (_matches($mk.get(0).previousSibling, _query(tag, attrs))) {
                            $mk.prev().append($mk);
                        }
                    }
                }
                editor.selection.restore();
            }
        }

        function _split($node, tag, attrs, collapsed) {
            if (!collapsed) {
                var changed = false;
                if ($node.data('type') === true) {
                    while (editor.node.isFirstSibling($node.get(0)) && !$node.parent().is(editor.$el) && !$node.parent().is('ol') && !$node.parent().is('ul')) {
                        $node.parent().before($node);
                        changed = true;
                    }
                } else if ($node.data('type') === false) {
                    while (editor.node.isLastSibling($node.get(0)) && !$node.parent().is(editor.$el) && !$node.parent().is('ol') && !$node.parent().is('ul')) {
                        $node.parent().after($node);
                        changed = true;
                    }
                }
                if (changed) return true;
            }
            if ($node.parents(tag).length || typeof tag == 'undefined') {
                var close_str = '';
                var open_str = '';
                var $p_node = $node.parent();
                if ($p_node.is(editor.$el) || editor.node.isBlock($p_node.get(0))) return false;
                while (!editor.node.isBlock($p_node.parent().get(0)) && ((typeof tag == 'undefined') || (typeof tag != 'undefined' && !_matches($p_node.get(0), _query(tag, attrs))))) {
                    close_str = close_str + editor.node.closeTagString($p_node.get(0));
                    open_str = editor.node.openTagString($p_node.get(0)) + open_str;
                    $p_node = $p_node.parent();
                }
                var node_str = $node.get(0).outerHTML;
                $node.replaceWith('<span id="mark"></span>');
                var p_html = $p_node.html().replace(/<span id="mark"><\/span>/, close_str + editor.node.closeTagString($p_node.get(0)) + open_str + node_str + close_str + editor.node.openTagString($p_node.get(0)) + open_str);
                $p_node.replaceWith(editor.node.openTagString($p_node.get(0)) + p_html + editor.node.closeTagString($p_node.get(0)));
                return true;
            }
            return false;
        }

        function _processNodeRemove($node, should_remove, tag, attrs) {
            var contents = editor.node.contents($node.get(0));
            for (var i = 0; i < contents.length; i++) {
                var node = contents[i];
                if (editor.node.hasClass(node, 'fr-marker')) {
                    should_remove = (should_remove + 1) % 2;
                } else if (should_remove) {
                    if ($(node).find('.fr-marker').length > 0) {
                        should_remove = _processNodeRemove($(node), should_remove, tag, attrs);
                    } else {
                        var nodes = $(node).find(tag || '*:not(br)');
                        for (var j = nodes.length - 1; j >= 0; j--) {
                            var nd = nodes[j];
                            console.log(nd)
                            if (!editor.node.isBlock(nd) && !editor.node.isVoid(nd) && (typeof tag == 'undefined' || _matches(nd, _query(tag, attrs)))) {
                                if (!editor.node.hasClass(nd, 'fr-clone')) {
                                    nd.outerHTML = nd.innerHTML;
                                }
                            } else if (editor.node.isBlock(nd) && (typeof tag == 'undefined') && node.tagName != 'TABLE') {
                                editor.node.clearAttributes(nd);
                            }
                        }
                        if ((typeof tag == 'undefined' && node.nodeType == Node.ELEMENT_NODE && !editor.node.isVoid(node)) || _matches(node, _query(tag, attrs))) {
                            if (!editor.node.isBlock(node)) {
                                if (!editor.node.hasClass(node, 'fr-clone')) {
                                    node.outerHTML = node.innerHTML;
                                }
                            } else if (typeof tag == 'undefined' && node.nodeType == Node.ELEMENT_NODE && editor.node.isBlock(node) && node.tagName != 'TABLE') {
                                editor.node.clearAttributes(node);
                            }
                        } else if (typeof tag == 'undefined' && node.nodeType == Node.ELEMENT_NODE && editor.node.isBlock(node) && node.tagName != 'TABLE') {
                            editor.node.clearAttributes(node);
                        }
                    }
                } else {
                    if ($(node).find('.fr-marker').length > 0) {
                        should_remove = _processNodeRemove($(node), should_remove, tag, attrs);
                    }
                }
            }
            return should_remove;
        }

        function remove(tag, attrs) {
            if (typeof attrs == 'undefined') attrs = {};
            if (attrs.style) {
                delete attrs.style;
            }
            var collapsed = editor.selection.isCollapsed();
            editor.selection.save();
            var reassess = true;
            while (reassess) {
                reassess = false;
                var markers = editor.$el.find('.fr-marker');
                for (var i = 0; i < markers.length; i++) {
                    var $marker = $(markers[i]);
                    var $clone = null;
                    if (!$marker.attr('data-cloned') && !collapsed) {
                        $clone = $marker.clone().removeClass('fr-marker').addClass('fr-clone');
                        if ($marker.data('type') === true) {
                            $marker.attr('data-cloned', true).after($clone);
                        } else {
                            $marker.attr('data-cloned', true).before($clone);
                        }
                    }
                    if (_split($marker, tag, attrs, collapsed)) {
                        reassess = true;
                        break;
                    }
                }
            }
            _processNodeRemove(editor.$el, 0, tag, attrs);
            if (!collapsed) {
                editor.$el.find('.fr-marker').remove();
                editor.$el.find('.fr-clone').removeClass('fr-clone').addClass('fr-marker');
            }
            if (collapsed) {
                editor.$el.find('.fr-marker').before($.FE.INVISIBLE_SPACE).after($.FE.INVISIBLE_SPACE);
            }
            editor.html.cleanEmptyTags();
            editor.el.normalize();
            editor.selection.restore();
        }

        function toggle(tag, attrs) {
            if (is(tag, attrs)) {
                remove(tag, attrs);
            } else {
                apply(tag, attrs);
            }
        }

        function _cleanFormat(elem, prop) {
            var $elem = $(elem);
            $elem.css(prop, '');
            if ($elem.attr('style') === '') {
                $elem.replaceWith($elem.html());
            }
        }

        function _filterSpans(elem, prop) {
            return $(elem).attr('style').indexOf(prop + ':') === 0 || $(elem).attr('style').indexOf(';' + prop + ':') >= 0 || $(elem).attr('style').indexOf('; ' + prop + ':') >= 0;
        }

        function applyStyle(prop, val) {
            var i;
            var $marker;
            var $span = null;
            if (editor.selection.isCollapsed()) {
                editor.markers.insert();
                $marker = editor.$el.find('.fr-marker');
                var $parent = $marker.parent();
                if (editor.node.openTagString($parent.get(0)) == '<span style="' + prop + ': ' + $parent.css(prop) + ';">') {
                    if (editor.node.isEmpty($parent.get(0))) {
                        $span = $('<span style="' + prop + ': ' + val + ';">' + $.FE.INVISIBLE_SPACE + $.FE.MARKERS + '</span>');
                        $parent.replaceWith($span);
                    } else {
                        var x = {};
                        x['style*'] = prop + ':';
                        _split($marker, 'span', x, true);
                        $marker = editor.$el.find('.fr-marker');
                        if (val) {
                            $span = $('<span style="' + prop + ': ' + val + ';">' + $.FE.INVISIBLE_SPACE + $.FE.MARKERS + '</span>');
                            $marker.replaceWith($span);
                        } else {
                            $marker.replaceWith($.FE.INVISIBLE_SPACE + $.FE.MARKERS);
                        }
                    }
                    editor.html.cleanEmptyTags();
                } else if (editor.node.isEmpty($parent.get(0)) && $parent.is('span')) {
                    $marker.replaceWith($.FE.MARKERS);
                    $parent.css(prop, val);
                } else {
                    $span = $('<span style="' + prop + ': ' + val + ';">' + $.FE.INVISIBLE_SPACE + $.FE.MARKERS + '</span>');
                    $marker.replaceWith($span);
                }
                if ($span) {
                    _splitParents($span, prop, val);
                }
            } else {
                editor.selection.save();
                if (val == null || (prop == 'color' && editor.$el.find('.fr-marker').parents('u, a').length > 0)) {
                    var markers = editor.$el.find('.fr-marker');
                    for (i = 0; i < markers.length; i++) {
                        $marker = $(markers[i]);
                        if ($marker.data('type') === true) {
                            while (editor.node.isFirstSibling($marker.get(0)) && !$marker.parent().is(editor.$el) && !editor.node.isElement($marker.parent().get(0)) && !editor.node.isBlock($marker.parent().get(0))) {
                                $marker.parent().before($marker);
                            }
                        } else {
                            while (editor.node.isLastSibling($marker.get(0)) && !$marker.parent().is(editor.$el) && !editor.node.isElement($marker.parent().get(0)) && !editor.node.isBlock($marker.parent().get(0))) {
                                $marker.parent().after($marker);
                            }
                        }
                    }
                }
                var start_marker = editor.$el.find('.fr-marker[data-type="true"]').get(0).nextSibling;
                var attrs = {'class': 'fr-unprocessed'};
                if (val) attrs.style = prop + ': ' + val + ';'
                _processNodeFormat(start_marker, 'span', attrs);
                editor.$el.find('.fr-marker + .fr-unprocessed').each(function () {
                    $(this).prepend($(this).prev());
                });
                editor.$el.find('.fr-unprocessed + .fr-marker').each(function () {
                    $(this).prev().append(this);
                });
                if ((val || '').match(/\dem$/)) {
                    editor.$el.find('span.fr-unprocessed').removeClass('fr-unprocessed');
                }
                while (editor.$el.find('span.fr-unprocessed').length > 0) {
                    $span = editor.$el.find('span.fr-unprocessed:first').removeClass('fr-unprocessed');
                    $span.parent().get(0).normalize();
                    if ($span.parent().is('span') && $span.parent().get(0).childNodes.length == 1) {
                        $span.parent().css(prop, val);
                        var $child = $span;
                        $span = $span.parent();
                        $child.replaceWith($child.html());
                    }
                    var inner_spans = $span.find('span');
                    for (i = inner_spans.length - 1; i >= 0; i--) {
                        _cleanFormat(inner_spans[i], prop);
                    }
                    _splitParents($span, prop, val);
                }
            }
            _normalize();
        }

        function _splitParents($span, prop, val) {
            var i;
            var $outer_span = $span.parentsUntil(editor.$el, 'span[style]');
            var to_remove = [];
            for (i = $outer_span.length - 1; i >= 0; i--) {
                if (!_filterSpans($outer_span[i], prop)) {
                    to_remove.push($outer_span[i]);
                }
            }
            $outer_span = $outer_span.not(to_remove);
            if ($outer_span.length) {
                var c_str = '';
                var o_str = '';
                var ic_str = '';
                var io_str = '';
                var c_node = $span.get(0);
                do {
                    c_node = c_node.parentNode;
                    $(c_node).addClass('fr-split');
                    c_str = c_str + editor.node.closeTagString(c_node);
                    o_str = editor.node.openTagString($(c_node).clone().addClass('fr-split').get(0)) + o_str;
                    if ($outer_span.get(0) != c_node) {
                        ic_str = ic_str + editor.node.closeTagString(c_node);
                        io_str = editor.node.openTagString($(c_node).clone().addClass('fr-split').get(0)) + io_str;
                    }
                } while ($outer_span.get(0) != c_node);
                var str = c_str + editor.node.openTagString($($outer_span.get(0)).clone().css(prop, val || '').get(0)) + io_str + $span.css(prop, '').get(0).outerHTML + ic_str + '</span>' + o_str;
                $span.replaceWith('<span id="fr-break"></span>');
                var html = $outer_span.get(0).outerHTML;
                $($outer_span.get(0)).replaceWith(html.replace(/<span id="fr-break"><\/span>/g, str));
            }
        }

        function _normalize() {
            var i;
            while (editor.$el.find('.fr-split:empty').length > 0) {
                editor.$el.find('.fr-split:empty').remove();
            }
            editor.$el.find('.fr-split').removeClass('fr-split');
            editor.$el.find('[style=""]').removeAttr('style');
            editor.$el.find('[class=""]').removeAttr('class');
            editor.html.cleanEmptyTags();
            $(editor.$el.find('span').get().reverse()).each(function () {
                if (!this.attributes || this.attributes.length === 0) {
                    $(this).replaceWith(this.innerHTML);
                }
            });
            editor.el.normalize();
            var just_spans = editor.$el.find('span[style] + span[style]');
            for (i = 0; i < just_spans.length; i++) {
                var $x = $(just_spans[i]);
                var $p = $(just_spans[i]).prev();
                if ($x.get(0).previousSibling == $p.get(0) && editor.node.openTagString($x.get(0)) == editor.node.openTagString($p.get(0))) {
                    $x.prepend($p.html());
                    $p.remove();
                }
            }
            editor.$el.find('span[style] span[style]').each(function () {
                if ($(this).attr('style').indexOf('font-size') >= 0) {
                    var $parent = $(this).parents('span[style]');
                    if ($parent.attr('style').indexOf('background-color') >= 0) {
                        $(this).attr('style', $(this).attr('style') + ';' + $parent.attr('style'));
                        _split($(this), 'span[style]', {}, false);
                    }
                }
            });
            editor.el.normalize();
            editor.selection.restore();
        }

        function removeStyle(prop) {
            applyStyle(prop, null);
        }

        function is(tag, attrs) {
            if (typeof attrs == 'undefined') attrs = {};
            if (attrs.style) {
                delete attrs.style;
            }
            var range = editor.selection.ranges(0);
            var el = range.startContainer;
            if (el.nodeType == Node.ELEMENT_NODE) {
                if (el.childNodes.length > 0 && el.childNodes[range.startOffset]) {
                    el = el.childNodes[range.startOffset];
                }
            }
            if (!range.collapsed && el.nodeType == Node.TEXT_NODE && range.startOffset == (el.textContent || '').length) {
                while (!editor.node.isBlock(el.parentNode) && !el.nextSibling) {
                    el = el.parentNode;
                }
                if (el.nextSibling) {
                    el = el.nextSibling;
                }
            }
            var f_child = el;
            while (f_child && f_child.nodeType == Node.ELEMENT_NODE && !_matches(f_child, _query(tag, attrs))) {
                f_child = f_child.firstChild;
            }
            if (f_child && f_child.nodeType == Node.ELEMENT_NODE && _matches(f_child, _query(tag, attrs))) return true;
            var p_node = el;
            if (p_node && p_node.nodeType != Node.ELEMENT_NODE) p_node = p_node.parentNode;
            while (p_node && p_node.nodeType == Node.ELEMENT_NODE && p_node != editor.el && !_matches(p_node, _query(tag, attrs))) {
                p_node = p_node.parentNode;
            }
            if (p_node && p_node.nodeType == Node.ELEMENT_NODE && p_node != editor.el && _matches(p_node, _query(tag, attrs))) return true;
            return false;
        }

        return {is: is, toggle: toggle, apply: apply, remove: remove, applyStyle: applyStyle, removeStyle: removeStyle}
    }
    $.extend($.FE.DEFAULTS, {indentMargin: 20});
    $.FE.COMMANDS = {
        bold: {
            title: 'Bold', toggle: true, refresh: function ($btn) {
                var format = this.format.is('strong');
                $btn.toggleClass('fr-active', format).attr('aria-pressed', format);
            }
        },
        italic: {
            title: 'Italic', toggle: true, refresh: function ($btn) {
                var format = this.format.is('em');
                $btn.toggleClass('fr-active', format).attr('aria-pressed', format);
            }
        },
        underline: {
            title: 'Underline', toggle: true, refresh: function ($btn) {
                var format = this.format.is('u');
                $btn.toggleClass('fr-active', format).attr('aria-pressed', format);
            }
        },
        strikeThrough: {
            title: 'Strikethrough', toggle: true, refresh: function ($btn) {
                var format = this.format.is('s');
                $btn.toggleClass('fr-active', format).attr('aria-pressed', format);
            }
        },
        subscript: {
            title: 'Subscript', toggle: true, refresh: function ($btn) {
                var format = this.format.is('sub');
                $btn.toggleClass('fr-active', format).attr('aria-pressed', format);
            }
        },
        superscript: {
            title: 'Superscript', toggle: true, refresh: function ($btn) {
                var format = this.format.is('sup');
                $btn.toggleClass('fr-active', format).attr('aria-pressed', format);
            }
        },
        outdent: {title: 'Decrease Indent'},
        indent: {title: 'Increase Indent'},
        undo: {title: 'Undo', undo: false, forcedRefresh: true, disabled: true},
        redo: {title: 'Redo', undo: false, forcedRefresh: true, disabled: true},
        insertHR: {title: 'Insert Horizontal Line'},
        clearFormatting: {title: 'Clear Formatting'},
        selectAll: {title: 'Select All', undo: false}
    };
    $.FE.RegisterCommand = function (name, info) {
        $.FE.COMMANDS[name] = info;
    }
    $.FE.MODULES.commands = function (editor) {
        function _createDefaultTag(empty) {
            if (editor.html.defaultTag()) {
                empty = '<' + editor.html.defaultTag() + '>' + empty + '</' + editor.html.defaultTag() + '>';
            }
            return empty;
        }

        var mapping = {
            bold: function () {
                _execCommand('bold', 'strong');
            }, subscript: function () {
                if (editor.format.is('sup')) {
                    editor.format.remove('sup');
                }
                _execCommand('subscript', 'sub');
            }, superscript: function () {
                if (editor.format.is('sub')) {
                    editor.format.remove('sub');
                }
                _execCommand('superscript', 'sup');
            }, italic: function () {
                _execCommand('italic', 'em');
            }, strikeThrough: function () {
                _execCommand('strikeThrough', 's');
            }, underline: function () {
                _execCommand('underline', 'u');
            }, undo: function () {
                editor.undo.run();
            }, redo: function () {
                editor.undo.redo();
            }, indent: function () {
                _processIndent(1);
            }, outdent: function () {
                _processIndent(-1);
            }, show: function () {
                if (editor.opts.toolbarInline) {
                    editor.toolbar.showInline(null, true);
                }
            }, insertHR: function () {
                editor.selection.remove();
                var empty = '';
                if (editor.core.isEmpty()) {
                    empty = '<br>';
                    empty = _createDefaultTag(empty);
                }
                editor.html.insert('<hr id="fr-just">' + empty);
                var $hr = editor.$el.find('hr#fr-just');
                $hr.removeAttr('id');
                var check;
                if ($hr.next().length === 0) {
                    var default_tag = editor.html.defaultTag();
                    if (default_tag) {
                        $hr.after($('<' + default_tag + '>').append('<br>'));
                    } else {
                        $hr.after('<br>');
                    }
                }
                if ($hr.prev().is('hr')) {
                    check = editor.selection.setAfter($hr.get(0), false);
                } else if ($hr.next().is('hr')) {
                    check = editor.selection.setBefore($hr.get(0), false);
                } else {
                    editor.selection.setAfter($hr.get(0), false) || editor.selection.setBefore($hr.get(0), false);
                }
                if (!check && typeof check !== 'undefined') {
                    empty = $.FE.MARKERS + '<br>';
                    empty = _createDefaultTag(empty);
                    $hr.after(empty);
                }
                editor.selection.restore();
            }, clearFormatting: function () {
                editor.format.remove();
            }, selectAll: function () {
                editor.doc.execCommand('selectAll', false, false);
            }
        }

        function exec(cmd, params) {
            if (editor.events.trigger('commands.before', $.merge([cmd], params || [])) !== false) {
                var callback = ($.FE.COMMANDS[cmd] && $.FE.COMMANDS[cmd].callback) || mapping[cmd];
                var focus = true;
                var accessibilityFocus = false;
                if ($.FE.COMMANDS[cmd]) {
                    if (typeof $.FE.COMMANDS[cmd].focus != 'undefined') {
                        focus = $.FE.COMMANDS[cmd].focus;
                    }
                    if (typeof $.FE.COMMANDS[cmd].accessibilityFocus != 'undefined') {
                        accessibilityFocus = $.FE.COMMANDS[cmd].accessibilityFocus;
                    }
                }
                if ((!editor.core.hasFocus() && focus && !editor.popups.areVisible()) || (!editor.core.hasFocus() && accessibilityFocus && editor.accessibility.hasFocus())) {
                    editor.events.focus(true);
                }
                if ($.FE.COMMANDS[cmd] && $.FE.COMMANDS[cmd].undo !== false) {
                    if (editor.$el.find('.fr-marker').length) {
                        editor.events.disableBlur();
                        editor.selection.restore();
                    }
                    editor.undo.saveStep();
                }
                if (callback) {
                    callback.apply(editor, $.merge([cmd], params || []));
                }
                editor.events.trigger('commands.after', $.merge([cmd], params || []));
                if ($.FE.COMMANDS[cmd] && $.FE.COMMANDS[cmd].undo !== false) editor.undo.saveStep();
            }
        }

        function _execCommand(cmd, tag) {
            editor.format.toggle(tag);
        }

        function _processIndent(indent) {
            editor.selection.save();
            editor.html.wrap(true, true, true, true);
            editor.selection.restore();
            var blocks = editor.selection.blocks();
            for (var i = 0; i < blocks.length; i++) {
                if (blocks[i].tagName != 'LI' && blocks[i].parentNode.tagName != 'LI') {
                    var $block = $(blocks[i]);
                    var prop = (editor.opts.direction == 'rtl' || $block.css('direction') == 'rtl') ? 'margin-right' : 'margin-left';
                    var margin_left = editor.helpers.getPX($block.css(prop));
                    if ($block.width() < 2 * editor.opts.indentMargin && indent > 0) continue;
                    $block.css(prop, Math.max(margin_left + indent * editor.opts.indentMargin, 0) || '');
                    $block.removeClass('fr-temp-div');
                }
            }
            editor.selection.save();
            editor.html.unwrap();
            editor.selection.restore();
        }

        function callExec(k) {
            return function () {
                exec(k);
            }
        }

        var resp = {};
        for (var k in mapping) {
            if (mapping.hasOwnProperty(k)) {
                resp[k] = callExec(k);
            }
        }

        function _init() {
            editor.events.on('keydown', function (e) {
                var el = editor.selection.element();
                if (el && el.tagName == 'HR' && !editor.keys.isArrow(e.which)) {
                    e.preventDefault();
                    return false;
                }
            });
            editor.events.on('keyup', function (e) {
                var el = editor.selection.element();
                if (el && el.tagName == 'HR') {
                    if (e.which == $.FE.KEYCODE.ARROW_LEFT || e.which == $.FE.KEYCODE.ARROW_UP) {
                        if (el.previousSibling) {
                            if (!editor.node.isBlock(el.previousSibling)) {
                                $(el).before($.FE.MARKERS);
                            } else {
                                editor.selection.setAtEnd(el.previousSibling);
                            }
                            editor.selection.restore();
                            return false;
                        }
                    } else if (e.which == $.FE.KEYCODE.ARROW_RIGHT || e.which == $.FE.KEYCODE.ARROW_DOWN) {
                        if (el.nextSibling) {
                            if (!editor.node.isBlock(el.nextSibling)) {
                                $(el).after($.FE.MARKERS);
                            } else {
                                editor.selection.setAtStart(el.nextSibling);
                            }
                            editor.selection.restore();
                            return false;
                        }
                    }
                }
            })
            editor.events.on('mousedown', function (e) {
                if (e.target && e.target.tagName == 'HR') {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
            editor.events.on('mouseup', function () {
                var s_el = editor.selection.element();
                var e_el = editor.selection.endElement();
                if (s_el == e_el && s_el && s_el.tagName == 'HR') {
                    if (s_el.nextSibling) {
                        if (!editor.node.isBlock(s_el.nextSibling)) {
                            $(s_el).after($.FE.MARKERS);
                        } else {
                            editor.selection.setAtStart(s_el.nextSibling);
                        }
                    }
                    editor.selection.restore();
                }
            })
        }

        return $.extend(resp, {exec: exec, _init: _init});
    };
    $.FE.MODULES.data = function (l) {
        var d = "2B3B9A6C7C2C4C3H3I3B2==",
            s = "NCKB1zwtPA9tqzajXC2c2A7B-16VD3spzJ1C9C3D5oOF2OB1NB1LD7VA5QF4TE3gytXB2A4C-8VA2AC4E1D3GB2EB2KC3KD1MF1juuSB1A8C6yfbmd1B2a1A5qdsdB2tivbC3CB1KC1CH1eLA2sTF1B4I4H-7B-21UB6b1F5bzzzyAB4JC3MG2hjdKC1JE6C1E1cj1pD-16pUE5B4prra2B5ZB3D3C3pxj1EA6A3rnJA2C-7I-7JD9D1E1wYH1F3sTB5TA2G4H4ZA22qZA5BB3mjcvcCC3JB1xillavC-21VE6PC5SI4YC5C8mb1A3WC3BD2B5aoDA2qqAE3A5D-17fOD1D5RD4WC10tE6OAZC3nF-7b1C4A4D3qCF2fgmapcromlHA2QA6a1E1D3e1A6C2bie2F4iddnIA7B2mvnwcIB5OA1DB2OLQA3PB10WC7WC5d1E3uI-7b1D5D6b1E4D2arlAA4EA1F-11srxI-7MB1D7PF1E5B4adB-21YD5vrZH3D3xAC4E1A2GF2CF2J-7yNC2JE1MI2hH-7QB1C6B5B-9bA-7XB13a1B5VievwpKB4LA3NF-10H-9I-8hhaC-16nqPG4wsleTD5zqYF3h1G2B7B4yvGE2Pi1H-7C-21OE6B1uLD1kI4WC1E7C5g1D-8fue1C8C6c1D4D3Hpi1CC4kvGC2E1legallyXB4axVA11rsA4A-9nkdtlmzBA2GD3A13A6CB1dabE1lezrUE6RD5TB4A-7f1C8c1B5d1D4D3tyfCD5C2D2==",
            f = function () {
                for (var e = 0, t = document.domain, n = t.split("."), r = "_gd" + (new Date).getTime(); e < n.length - 1 && -1 == document.cookie.indexOf(r + "=" + r);) t = n.slice(-1 - ++e).join("."), document.cookie = r + "=" + r + ";domain=" + t + ";";
                return document.cookie = r + "=;expires=Thu, 01 Jan 1970 00:00:01 GMT;domain=" + t + ";", (t || "").replace(/(^\.*)|(\.*$)/g, "")
            }();

        function u(e) {
            return e
        }

        var E, p, g = u(function (e) {
            if (!e) return e;
            for (var t = "", n = u("charCodeAt"), r = u("fromCharCode"), C = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789".indexOf(e[0]), o = 1; o < e.length - 2; o++) {
                for (var i = a(++C), A = e[n](o), B = ""; /[0-9-]/.test(e[o + 1]);) B += e[++o];
                A = D(A, i, B = parseInt(B, 10) || 0), A ^= C - 1 & 31, t += String[r](A)
            }
            return t
        });

        function a(e) {
            for (var t = e.toString(), n = 0, r = 0; r < t.length; r++) n += parseInt(t.charAt(r), 10);
            return 10 < n ? n % 9 + 1 : n
        }

        function D(e, t, n) {
            for (var r = Math.abs(n); 0 < r--;) e -= t;
            return n < 0 && (e += 123), e
        }

        function h(e) {
            return e && "block" !== e.css("display") ? (e.remove(), !0) : e && 0 === l.helpers.getPX(e.css("height")) ? (e.remove(), !0) : !(!e || "absolute" !== e.css("position") && "fixed" !== e.css("position")) && (e.remove(), !0)
        }

        function v(e) {
            return e && 0 === l.$box.find(e).length
        }

        var e = 0;

        function m() {
            if (10 < e && (l[u(g("0ppecjvc=="))](), setTimeout(function () {
                $.FE = null
            }, 10)), !l.$box) return !1;
            l.$wp.prepend(g(u(g(s)))), E = l.$wp.find("> div:first"), p = E.find("> a"), "rtl" == l.opts.direction && E.css("left", "auto").css("right", 0).attr("direction", "rtl"), e++
        }

        function F(e) {
            for (var t = [g("9qqG-7amjlwq=="), g("KA3B3C2A6D1D5H5H1A3=="), g("3B9B3B5F3C4G3E3=="), g("QzbzvxyB2yA-9m=="), g("ji1kacwmgG5bc=="), g("nmA-13aogi1A3c1jd=="), g("BA9ggq=="), g("emznbjbH3fij=="), g("tkC-22d1qC-13sD1wzF-7==")], n = 0; n < t.length; n++) if (String.prototype.endsWith || (String.prototype.endsWith = function (e, t) {
                return (t === undefined || t > this.length) && (t = this.length), this.substring(t - e.length, t) === e
            }), e.endsWith(t[n])) return !0;
            return !1
        }

        return {
            _init: function () {
                var e = l.o_win.FEK;
                try {
                    e = e || localStorage && localStorage.FEK
                } catch (c) {
                }
                e = l.opts.key || e || [""];
                var t = g(u("ziRA1E3B9pA5B-11D-11xg1A3ZB5D1D4B-11ED2EG2pdeoC1clIH4wB-22yQD5uF4YE3E3A9=="));
                "string" == typeof e && (e = [e]);
                for (var n, r, C, o = !(l.ul = !0), i = 0, A = 0; A < e.length; A++) {
                    var B = (r = e[A], 3 === (C = (g(r) || "").split("|")).length ? C : [null, null, g(r) || ""]),
                        a = B[2];
                    if (a === g(u(g("mcVRDoB1BGILD7YFe1BTXBA7B6=="))) || 0 <= a.indexOf(f, a.length - f.length) || F(f)) {
                        if (!((null === (n = B[1]) || new Date(n) < new Date(g(d))) && 0 < (f || "").length) || F(f)) {
                            l.ul = !1;
                            break
                        }
                        o = !0, s = "RCZB17botVG4A-8yzia1C4A5DG3CD2cFB4qflmCE4I2FB1SC7F6PE4WE3RD6e2A4c1D3d1E2E3ehxdGE3CE2IB2LC1HG2LE1QA3QC7B-13cC-9epmkjc1B4e1C4pgjgvkOC5E1eNE1HB2LD2B-13WD5tvabUA5a1A4f1A2G3C2A-21cihKE3FE2DB2cccJE1iC-7G-7tD-17tVD6A-9qC-7QC7a1E4B4je1E3E2G2ecmsAA1xH-8HB11C1D1lgzQA3dTB8od1D4XE3ohb1B4E4D3mbLA10NA7C-21d1genodKC11PD9PE5tA-8UI3ZC5XB5B-11qXF2F-7wtwjAG3NA1IB1OD1HC1RD4QJ4evUF2D5XG2G4XA8pqocH1F3G2J2hcpHC4D1MD4C1MB8PD5klcQD1A8A6e2A3ed1E2A24A7HC5C3qA-9tiA-61dcC3MD1LE1D4SA3A9ZZXSE4g1C3Pa2C5ufbcGI3I2B4skLF2CA1vxB-22wgUC4kdH-8cVB5iwe1A2D3H3G-7DD5JC2ED2OH2JB10D3C2xHE1KA29PB11wdC-11C4cixb2C7a1C4YYE3B2A15uB-21wpCA1MF1NuC-21dyzD6pPG4I-7pmjc1A4yte1F3B-22yvCC3VbC-7qC-22qNE2hC1vH-8zad1RF6WF3DpI-7C8A-16hpf1F3D2ylalB-13BB2lpA-63IB3uOF6D5G4gabC-21UD2A3PH4ZA20B11b2C6ED4A2H3I1A15DB4KD2laC-8LA5B8B7==", i = B[0] || -1
                    }
                }
                var D = new Image;
                !0 === l.ul && (m(), D.src = o ? u(g(t)) + "e=" + i : u(g(t)) + "u"), !0 === l.ul && (l.events.on("contentChanged", function () {
                    (h(E) || h(p) || v(E) || v(p)) && m()
                }), l.events.on("html.get", function (e) {
                    return e + '<p data-f-id="pbf" style="text-align: center; font-size: 14px; margin-top: 30px; opacity: 0.65; font-family: sans-serif;">Powered by <a href="https://www.froala.com/wysiwyg-editor?pb=1" title="Froala Editor">Froala Editor</a></p>'
                })), l.events.on("html.set", function () {
                    var e = l.el.querySelector('[data-f-id="pbf"]');
                    e && $(e).remove()
                }), l.events.on("destroy", function () {
                    E && E.length && E.remove()
                }, !0)
            }
        }
    }
    $.extend($.FE.DEFAULTS, {
        pastePlain: false,
        pasteDeniedTags: ['colgroup', 'col', 'meta'],
        pasteDeniedAttrs: ['class', 'id', 'style'],
        pasteAllowedStyleProps: ['.*'],
        pasteAllowLocalImages: false
    });
    $.FE.MODULES.paste = function (editor) {
        var scroll_position;
        var clipboard_html;
        var clipboard_rtf;
        var $paste_div;
        var snapshot;

        function saveCopiedText(html, plain) {
            try {
                editor.win.localStorage.setItem('fr-copied-html', html);
                editor.win.localStorage.setItem('fr-copied-text', plain);
            } catch (ex) {
            }
        }

        function _handleCopy(e) {
            var copied_html = editor.html.getSelected();
            saveCopiedText(copied_html, $('<div>').html(copied_html).text())
            if (e.type == 'cut') {
                editor.undo.saveStep();
                setTimeout(function () {
                    editor.selection.save();
                    editor.html.wrap();
                    editor.selection.restore();
                    editor.events.focus();
                    editor.undo.saveStep();
                }, 0);
            }
        }

        var stop_paste = false;

        function _handlePaste(e) {
            if (editor.edit.isDisabled()) {
                return false;
            }
            if (stop_paste) {
                return false;
            }
            if (e.originalEvent) e = e.originalEvent;
            if (editor.events.trigger('paste.before', [e]) === false) {
                e.preventDefault();
                return false;
            }
            scroll_position = editor.$win.scrollTop();
            if (e && e.clipboardData && e.clipboardData.getData) {
                var types = '';
                var clipboard_types = e.clipboardData.types;
                if (editor.helpers.isArray(clipboard_types)) {
                    for (var i = 0; i < clipboard_types.length; i++) {
                        types += clipboard_types[i] + ';';
                    }
                } else {
                    types = clipboard_types;
                }
                clipboard_html = '';
                if (/text\/rtf/.test(types)) {
                    clipboard_rtf = e.clipboardData.getData('text/rtf');
                }
                if (/text\/html/.test(types) && !editor.browser.safari) {
                    clipboard_html = e.clipboardData.getData('text/html');
                } else if (/text\/rtf/.test(types) && editor.browser.safari) {
                    clipboard_html = clipboard_rtf;
                } else if (/public.rtf/.test(types) && editor.browser.safari) {
                    clipboard_html = e.clipboardData.getData('text/rtf');
                }
                if (clipboard_html !== '') {
                    _processPaste();
                    if (e.preventDefault) {
                        e.stopPropagation();
                        e.preventDefault();
                    }
                    return false;
                } else {
                    clipboard_html = null;
                }
            }
            _beforePaste();
            return false;
        }

        function _dropPaste(e) {
            if (e.originalEvent) e = e.originalEvent;
            if (e && e.dataTransfer && e.dataTransfer.getData) {
                var types = '';
                var clipboard_types = e.dataTransfer.types;
                if (editor.helpers.isArray(clipboard_types)) {
                    for (var i = 0; i < clipboard_types.length; i++) {
                        types += clipboard_types[i] + ';';
                    }
                } else {
                    types = clipboard_types;
                }
                clipboard_html = '';
                if (/text\/rtf/.test(types)) {
                    clipboard_rtf = e.dataTransfer.getData('text/rtf');
                }
                if (/text\/html/.test(types)) {
                    clipboard_html = e.dataTransfer.getData('text/html');
                } else if (/text\/rtf/.test(types) && editor.browser.safari) {
                    clipboard_html = clipboard_rtf;
                } else if (/text\/plain/.test(types) && !this.browser.mozilla) {
                    clipboard_html = editor.html.escapeEntities(e.dataTransfer.getData('text/plain')).replace(/\n/g, '<br>');
                }
                if (clipboard_html !== '') {
                    editor.keys.forceUndo();
                    snapshot = editor.snapshot.get();
                    editor.selection.save();
                    editor.$el.find('.fr-marker').removeClass('fr-marker').addClass('fr-marker-helper');
                    var ok = editor.markers.insertAtPoint(e);
                    editor.$el.find('.fr-marker').removeClass('fr-marker').addClass('fr-marker-placeholder');
                    editor.$el.find('.fr-marker-helper').addClass('fr-marker').removeClass('fr-marker-helper');
                    editor.selection.restore();
                    editor.selection.remove();
                    editor.$el.find('.fr-marker-placeholder').addClass('fr-marker').removeClass('fr-marker-placeholder');
                    if (ok !== false) {
                        var marker = editor.el.querySelector('.fr-marker');
                        $(marker).replaceWith($.FE.MARKERS);
                        editor.selection.restore();
                        _processPaste();
                        if (e.preventDefault) {
                            e.stopPropagation();
                            e.preventDefault();
                        }
                        return false;
                    }
                } else {
                    clipboard_html = null;
                }
            }
        }

        function _beforePaste() {
            editor.selection.save();
            editor.events.disableBlur();
            clipboard_html = null;
            if (!$paste_div) {
                $paste_div = $('<div contenteditable="true" style="position: fixed; top: 0; left: -9999px; height: 100%; width: 0; word-break: break-all; overflow:hidden; z-index: 2147483647; line-height: 140%; -moz-user-select: text; -webkit-user-select: text; -ms-user-select: text; user-select: text;" tabIndex="-1"></div>');
                if (editor.browser.webkit) {
                    $paste_div.css('top', editor.$sc.scrollTop());
                    editor.$el.after($paste_div);
                } else if (editor.browser.edge && editor.opts.iframe) {
                    editor.$el.append($paste_div);
                } else {
                    editor.$box.after($paste_div);
                }
                editor.events.on('destroy', function () {
                    $paste_div.remove();
                })
            } else {
                $paste_div.html('');
                if (editor.browser.edge && editor.opts.iframe) {
                    editor.$el.append($paste_div);
                }
            }
            if (editor.helpers.isIOS() && editor.$sc) {
                editor.$sc.overflow('hidden');
            }
            $paste_div.focus();
            if (editor.helpers.isIOS() && editor.$sc) {
                editor.$sc.overflow('hidden', '');
            }
            editor.win.setTimeout(_processPaste, 1);
        }

        function _wordClean(html) {
            var i;
            html = html.replace(/<p(.*?)class="?'?MsoListParagraph"?'? ([\s\S]*?)>([\s\S]*?)<\/p>/gi, '<ul><li>$3</li></ul>');
            html = html.replace(/<p(.*?)class="?'?NumberedText"?'? ([\s\S]*?)>([\s\S]*?)<\/p>/gi, '<ol><li>$3</li></ol>');
            html = html.replace(/<p(.*?)class="?'?MsoListParagraphCxSpFirst"?'?([\s\S]*?)(level\d)?([\s\S]*?)>([\s\S]*?)<\/p>/gi, '<ul><li$3>$5</li>');
            html = html.replace(/<p(.*?)class="?'?NumberedTextCxSpFirst"?'?([\s\S]*?)(level\d)?([\s\S]*?)>([\s\S]*?)<\/p>/gi, '<ol><li$3>$5</li>');
            html = html.replace(/<p(.*?)class="?'?MsoListParagraphCxSpMiddle"?'?([\s\S]*?)(level\d)?([\s\S]*?)>([\s\S]*?)<\/p>/gi, '<li$3>$5</li>');
            html = html.replace(/<p(.*?)class="?'?NumberedTextCxSpMiddle"?'?([\s\S]*?)(level\d)?([\s\S]*?)>([\s\S]*?)<\/p>/gi, '<li$3>$5</li>');
            html = html.replace(/<p(.*?)class="?'?MsoListBullet"?'?([\s\S]*?)(level\d)?([\s\S]*?)>([\s\S]*?)<\/p>/gi, '<li$3>$5</li>');
            html = html.replace(/<p(.*?)class="?'?MsoListParagraphCxSpLast"?'?([\s\S]*?)(level\d)?([\s\S]*?)>([\s\S]*?)<\/p>/gi, '<li$3>$5</li></ul>');
            html = html.replace(/<p(.*?)class="?'?NumberedTextCxSpLast"?'?([\s\S]*?)(level\d)?([\s\S]*?)>([\s\S]*?)<\/p>/gi, '<li$3>$5</li></ol>');
            html = html.replace(/<span([^<]*?)style="?'?mso-list:Ignore"?'?([\s\S]*?)>([\s\S]*?)<span/gi, '<span><span');
            html = html.replace(/<!--\[if \!supportLists\]-->([\s\S]*?)<!--\[endif\]-->/gi, '');
            html = html.replace(/<!\[if \!supportLists\]>([\s\S]*?)<!\[endif\]>/gi, '');
            html = html.replace(/(\n|\r| class=(")?Mso[a-zA-Z0-9]+(")?)/gi, ' ');
            html = html.replace(/<!--[\s\S]*?-->/gi, '');
            html = html.replace(/<(\/)*(meta|link|span|\\?xml:|st1:|o:|font)(.*?)>/gi, '');
            var word_tags = ['style', 'script', 'applet', 'embed', 'noframes', 'noscript'];
            for (i = 0; i < word_tags.length; i++) {
                var regex = new RegExp('<' + word_tags[i] + '.*?' + word_tags[i] + '(.*?)>', 'gi');
                html = html.replace(regex, '');
            }
            html = html.replace(/&nbsp;/gi, ' ');
            html = html.replace(/<td([^>]*)><\/td>/g, '<td$1><br></td>');
            html = html.replace(/<th([^>]*)><\/th>/g, '<th$1><br></th>');
            var oldHTML;
            do {
                oldHTML = html;
                html = html.replace(/<[^\/>][^>]*><\/[^>]+>/gi, '');
            } while (html != oldHTML);
            html = html.replace(/<lilevel([^1])([^>]*)>/gi, '<li data-indent="true"$2>');
            html = html.replace(/<lilevel1([^>]*)>/gi, '<li$1>');
            html = editor.clean.html(html, editor.opts.pasteDeniedTags, editor.opts.pasteDeniedAttrs);
            html = html.replace(/<a>(.[^<]+)<\/a>/gi, '$1');
            html = html.replace(/<br> */g, '<br>');
            var div = editor.o_doc.createElement('div')
            div.innerHTML = html;
            var lis = div.querySelectorAll('li[data-indent]');
            for (i = 0; i < lis.length; i++) {
                var li = lis[i];
                var p_li = li.previousElementSibling;
                if (p_li && p_li.tagName == 'LI') {
                    var list = p_li.querySelector(':scope > ul, :scope > ol');
                    if (!list) {
                        list = document.createElement('ul');
                        p_li.appendChild(list);
                    }
                    list.appendChild(li);
                } else {
                    li.removeAttribute('data-indent');
                }
            }
            editor.html.cleanBlankSpaces(div);
            html = div.innerHTML;
            return html;
        }

        function _plainPasteClean(html) {
            var el = null;
            var i;
            var div = editor.doc.createElement('div');
            div.innerHTML = html;
            var els = div.querySelectorAll('p, div, h1, h2, h3, h4, h5, h6, pre, blockquote');
            for (i = 0; i < els.length; i++) {
                el = els[i];
                el.outerHTML = '<' + (editor.html.defaultTag() || 'DIV') + '>' + el.innerHTML + '</' + (editor.html.defaultTag() || 'DIV') + '>'
            }
            els = div.querySelectorAll('*:not(' + 'p, div, h1, h2, h3, h4, h5, h6, pre, blockquote, ul, ol, li, table, tbody, thead, tr, td, br, img'.split(',').join('):not(') + ')');
            for (i = els.length - 1; i >= 0; i--) {
                el = els[i];
                el.outerHTML = el.innerHTML;
            }
            var cleanComments = function (node) {
                var contents = editor.node.contents(node);
                for (var i = 0; i < contents.length; i++) {
                    if (contents[i].nodeType != Node.TEXT_NODE && contents[i].nodeType != Node.ELEMENT_NODE) {
                        contents[i].parentNode.removeChild(contents[i]);
                    } else {
                        cleanComments(contents[i]);
                    }
                }
            };
            cleanComments(div);
            return div.innerHTML;
        }

        function _processPaste() {
            if (editor.browser.edge && editor.opts.iframe) {
                editor.$box.after($paste_div);
            }
            if (!snapshot) {
                editor.keys.forceUndo();
                snapshot = editor.snapshot.get();
            }
            if (!clipboard_html) {
                clipboard_html = $paste_div.get(0).innerHTML;
                editor.selection.restore();
                editor.events.enableBlur();
            }
            var is_word = clipboard_html.match(/(class=\"?Mso|class=\'?Mso|class="?Xl|class='?Xl|class=Xl|style=\"[^\"]*\bmso\-|style=\'[^\']*\bmso\-|w:WordDocument)/gi);
            var response = editor.events.chainTrigger('paste.beforeCleanup', clipboard_html);
            if (response && typeof (response) === 'string') {
                clipboard_html = response;
            }
            if (!is_word || (is_word && editor.events.trigger('paste.wordPaste', [clipboard_html]) !== false)) {
                clean(clipboard_html, is_word);
            }
        }

        function _isFromEditor(clipboard_html) {
            var possible_text = null;
            try {
                possible_text = editor.win.localStorage.getItem('fr-copied-text');
            } catch (ex) {
            }
            if (possible_text && $('<div>').html(clipboard_html).text().replace(/\u00A0/gi, ' ').replace(/\r|\n/gi, '') == possible_text.replace(/\u00A0/gi, ' ').replace(/\r|\n/gi, '')) {
                return true;
            }
            return false;
        }

        function _isDraggedFromEditor() {
            var possible_text = null;
            try {
                possible_text = editor.win.localStorage.getItem('fr-dragged-content-text');
            } catch (ex) {
            }
            if (possible_text && $('<div>').html(clipboard_html).text().replace(/\u00A0/gi, ' ').replace(/\r|\n/gi, '') == possible_text.replace(/\u00A0/gi, ' ').replace(/\r|\n/gi, '')) {
                return true;
            }
            return false;
        }

        function _buildTabs(len) {
            var tabs = '';
            var i = 0;
            while (i++ < len) {
                tabs += '&nbsp;'
            }
            return tabs;
        }

        function clean(clipboard_html, is_word, keep_formatting) {
            var els = null;
            var el = null;
            var i;
            if (clipboard_html.toLowerCase().indexOf('<body') >= 0) {
                var style = '';
                if (clipboard_html.indexOf('<style') >= 0) {
                    style = clipboard_html.replace(/[.\s\S\w\W<>]*(<style[^>]*>[\s]*[.\s\S\w\W<>]*[\s]*<\/style>)[.\s\S\w\W<>]*/gi, '$1');
                }
                clipboard_html = style + clipboard_html.replace(/[.\s\S\w\W<>]*<body[^>]*>[\s]*([.\s\S\w\W<>]*)[\s]*<\/body>[.\s\S\w\W<>]*/gi, '$1');
                var lastIndex = 0;
                var result = '';
                clipboard_html.replace(/<pre.*?>([\s\S]*?)<\/pre>/ig, function (match, p1, offset) {
                    if (offset > lastIndex) {
                        result += clipboard_html.substring(lastIndex, offset).replace(/ \n/g, ' ').replace(/\n /g, ' ').replace(/([^>])\n([^<])/g, '$1 $2');
                    }
                    result += match;
                    lastIndex = offset + match.length;
                });
                if (clipboard_html.length > lastIndex + 1) {
                    result += clipboard_html.substring(lastIndex, clipboard_html.length).replace(/ \n/g, ' ').replace(/\n /g, ' ').replace(/([^>])\n([^<])/g, '$1 $2');
                }
                clipboard_html = result;
            }
            var is_gdocs = false;
            if (clipboard_html.indexOf('id="docs-internal-guid') >= 0) {
                clipboard_html = clipboard_html.replace(/^[\w\W\s\S]* id="docs-internal-guid[^>]*>([\w\W\s\S]*)<\/b>[\w\W\s\S]*$/g, '$1');
                is_gdocs = true;
            }
            if (clipboard_html.indexOf('content="Sheets"') >= 0) {
                clipboard_html = clipboard_html.replace(/width:0px;/g, '')
            }
            var is_editor_content = false;
            var is_dragged_from_editor = false;
            if (!is_word) {
                is_editor_content = _isFromEditor(clipboard_html);
                is_dragged_from_editor = _isDraggedFromEditor(clipboard_html);
                if (is_editor_content) {
                    clipboard_html = editor.win.localStorage.getItem('fr-copied-html');
                }
                if (is_dragged_from_editor) {
                    is_editor_content = true;
                    clipboard_html = editor.win.localStorage.getItem('fr-dragged-content-html');
                }
                if (!is_editor_content) {
                    var htmlAllowedStylePropsCopy = editor.opts.htmlAllowedStyleProps;
                    editor.opts.htmlAllowedStyleProps = editor.opts.pasteAllowedStyleProps;
                    editor.opts.htmlAllowComments = false;
                    clipboard_html = clipboard_html.replace(/<span class="Apple-tab-span">\s*<\/span>/g, _buildTabs(editor.opts.tabSpaces || 4));
                    clipboard_html = clipboard_html.replace(/<span class="Apple-tab-span" style="white-space:pre">(\t*)<\/span>/g, function (str, x) {
                        return _buildTabs(x.length * (editor.opts.tabSpaces || 4));
                    })
                    clipboard_html = clipboard_html.replace(/\t/g, _buildTabs(editor.opts.tabSpaces || 4));
                    clipboard_html = editor.clean.html(clipboard_html, editor.opts.pasteDeniedTags, editor.opts.pasteDeniedAttrs);
                    editor.opts.htmlAllowedStyleProps = htmlAllowedStylePropsCopy;
                    editor.opts.htmlAllowComments = true;
                    clipboard_html = cleanEmptyTagsAndDivs(clipboard_html);
                    clipboard_html = clipboard_html.replace(/\r/g, '');
                    clipboard_html = clipboard_html.replace(/^ */g, '').replace(/ *$/g, '');
                }
            }
            if (is_word && (!editor.wordPaste || !keep_formatting)) {
                clipboard_html = clipboard_html.replace(/^\n*/g, '').replace(/^ /g, '');
                if (clipboard_html.indexOf('<colgroup>') === 0) {
                    clipboard_html = '<table>' + clipboard_html + '</table>';
                }
                clipboard_html = _wordClean(clipboard_html);
                clipboard_html = cleanEmptyTagsAndDivs(clipboard_html);
            }
            if (editor.opts.pastePlain && !is_editor_content) {
                clipboard_html = _plainPasteClean(clipboard_html);
            }
            var response = editor.events.chainTrigger('paste.afterCleanup', clipboard_html);
            if (typeof (response) === 'string') {
                clipboard_html = response;
            }
            if (clipboard_html !== '') {
                var tmp = editor.o_doc.createElement('div');
                tmp.innerHTML = clipboard_html;
                if (clipboard_html.indexOf('<body>') >= 0) {
                    editor.html.cleanBlankSpaces(tmp);
                    editor.spaces.normalize(tmp, true);
                } else {
                    editor.spaces.normalize(tmp);
                }
                var spans = tmp.getElementsByTagName('span');
                for (i = spans.length - 1; i >= 0; i--) {
                    var span = spans[i];
                    if (span.attributes.length === 0) {
                        span.outerHTML = span.innerHTML;
                    }
                }
                var selection_el = editor.selection.element();
                var in_list = false;
                if (selection_el && $(selection_el).parentsUntil(editor.el, 'ul, ol').length) {
                    in_list = true;
                }
                if (in_list) {
                    var list = tmp.children;
                    if (list.length == 1 && ['OL', 'UL'].indexOf(list[0].tagName) >= 0) {
                        list[0].outerHTML = list[0].innerHTML;
                    }
                }
                if (!is_gdocs) {
                    var brs = tmp.getElementsByTagName('br');
                    for (i = brs.length - 1; i >= 0; i--) {
                        var br = brs[i];
                        if (editor.node.isBlock(br.previousSibling)) {
                            br.parentNode.removeChild(br);
                        }
                    }
                }
                if (editor.opts.enter == $.FE.ENTER_BR) {
                    els = tmp.querySelectorAll('p, div');
                    for (i = els.length - 1; i >= 0; i--) {
                        el = els[i];
                        if (el.attributes.length === 0) {
                            el.outerHTML = el.innerHTML + (el.nextSibling && !editor.node.isEmpty(el) ? '<br>' : '');
                        }
                    }
                } else if (editor.opts.enter == $.FE.ENTER_DIV) {
                    els = tmp.getElementsByTagName('p');
                    for (i = els.length - 1; i >= 0; i--) {
                        el = els[i];
                        if (el.attributes.length === 0) {
                            el.outerHTML = '<div>' + el.innerHTML + '</div>';
                        }
                    }
                } else if (editor.opts.enter == $.FE.ENTER_P) {
                    if (tmp.childNodes.length == 1 && tmp.childNodes[0].tagName == 'P' && tmp.childNodes[0].attributes.length === 0) {
                        tmp.childNodes[0].outerHTML = tmp.childNodes[0].innerHTML;
                    }
                }
                clipboard_html = tmp.innerHTML;
                if (is_editor_content) {
                    clipboard_html = removeEmptyTags(clipboard_html);
                }
                editor.html.insert(clipboard_html, true);
            }
            _afterPaste();
            editor.undo.saveStep(snapshot);
            snapshot = null;
            editor.undo.saveStep();
        }

        function _afterPaste() {
            editor.events.trigger('paste.after');
        }

        function getRtfClipboard() {
            return clipboard_rtf;
        }

        function _filterNoAttrs(arry) {
            for (var t = arry.length - 1; t >= 0; t--) {
                if (arry[t].attributes && arry[t].attributes.length) {
                    arry.splice(t, 1);
                }
            }
            return arry;
        }

        function cleanEmptyTagsAndDivs(html) {
            var i;
            var div = editor.o_doc.createElement('div');
            div.innerHTML = html;
            var divs = _filterNoAttrs(Array.prototype.slice.call(div.querySelectorAll(':scope > div:not([style]), td > div:not([style]), th > div:not([style]), li > div:not([style])')));
            while (divs.length) {
                var dv = divs[divs.length - 1];
                if (editor.html.defaultTag() && editor.html.defaultTag() != 'div') {
                    if (dv.querySelector(editor.html.blockTagsQuery())) {
                        dv.outerHTML = dv.innerHTML;
                    } else {
                        dv.outerHTML = '<' + editor.html.defaultTag() + '>' + dv.innerHTML + '</' + editor.html.defaultTag() + '>';
                    }
                } else {
                    var els = dv.querySelectorAll('*');
                    if (!els.length || (els[els.length - 1].tagName !== 'BR' && dv.innerText.length === 0)) {
                        dv.outerHTML = dv.innerHTML + (dv.nextSibling ? '<br>' : '');
                    } else if (!(els.length && els[els.length - 1].tagName === 'BR' && !els[els.length - 1].nextSibling)) {
                        dv.outerHTML = dv.innerHTML + (dv.nextSibling ? '<br>' : '');
                    } else {
                        dv.outerHTML = dv.innerHTML;
                    }
                }
                divs = _filterNoAttrs(Array.prototype.slice.call(div.querySelectorAll(':scope > div:not([style]), td > div:not([style]), th > div:not([style]), li > div:not([style])')));
            }
            divs = _filterNoAttrs(Array.prototype.slice.call(div.querySelectorAll('div:not([style])')));
            while (divs.length) {
                for (i = 0; i < divs.length; i++) {
                    var el = divs[i];
                    var text = el.innerHTML.replace(/\u0009/gi, '').trim();
                    try {
                        el.outerHTML = text;
                    } catch (ex) {
                    }
                }
                divs = _filterNoAttrs(Array.prototype.slice.call(div.querySelectorAll('div:not([style])')));
            }
            return div.innerHTML;
        }

        function removeEmptyTags(html) {
            var i;
            var div = editor.o_doc.createElement('div');
            div.innerHTML = html;
            var empty_tags = div.querySelectorAll('*:empty:not(td):not(th):not(tr):not(iframe):not(svg):not(' + $.FE.VOID_ELEMENTS.join('):not(') + ')' + ':not(' + editor.opts.htmlAllowedEmptyTags.join('):not(') + ')');
            while (empty_tags.length) {
                for (i = 0; i < empty_tags.length; i++) {
                    empty_tags[i].parentNode.removeChild(empty_tags[i]);
                }
                empty_tags = div.querySelectorAll('*:empty:not(td):not(th):not(tr):not(iframe):not(svg):not(' + $.FE.VOID_ELEMENTS.join('):not(') + ')' + ':not(' + editor.opts.htmlAllowedEmptyTags.join('):not(') + ')');
            }
            return div.innerHTML;
        }

        function _dragStart(e) {
            if (e.originalEvent && e.originalEvent.target && e.originalEvent.target.nodeType == Node.TEXT_NODE) {
                try {
                    editor.win.localStorage.setItem('fr-dragged-content-html', e.originalEvent.dataTransfer.getData('text/html'));
                    editor.win.localStorage.setItem('fr-dragged-content-text', e.originalEvent.dataTransfer.getData('text/plain'));
                } catch (ex) {
                }
            }
        }

        function _init() {
            editor.el.addEventListener('copy', _handleCopy);
            editor.el.addEventListener('cut', _handleCopy);
            editor.el.addEventListener('paste', _handlePaste, {capture: true});
            editor.events.on('drop', _dropPaste);
            if (editor.browser.msie && editor.browser.version < 11) {
                editor.events.on('mouseup', function (e) {
                    if (e.button == 2) {
                        setTimeout(function () {
                            stop_paste = false;
                        }, 50);
                        stop_paste = true;
                    }
                }, true)
                editor.events.on('beforepaste', _handlePaste);
            }
            editor.events.on('dragstart', _dragStart, true);
            editor.events.on('destroy', _destroy);
        }

        function _destroy() {
            editor.el.removeEventListener('copy', _handleCopy);
            editor.el.removeEventListener('cut', _handleCopy);
            editor.el.removeEventListener('paste', _handlePaste);
        }

        return {
            _init: _init,
            cleanEmptyTagsAndDivs: cleanEmptyTagsAndDivs,
            getRtfClipboard: getRtfClipboard,
            saveCopiedText: saveCopiedText,
            clean: clean
        }
    };
    $.extend($.FE.DEFAULTS, {shortcutsEnabled: [], shortcutsHint: true});
    $.FE.SHORTCUTS_MAP = {};
    $.FE.RegisterShortcut = function (key, cmd, val, letter, shift, option) {
        $.FE.SHORTCUTS_MAP[(shift ? '^' : '') + (option ? '@' : '') + key] = {
            cmd: cmd,
            val: val,
            letter: letter,
            shift: shift,
            option: option
        }
        $.FE.DEFAULTS.shortcutsEnabled.push(cmd);
    }
    $.FE.RegisterShortcut($.FE.KEYCODE.E, 'show', null, 'E', false, false);
    $.FE.RegisterShortcut($.FE.KEYCODE.B, 'bold', null, 'B', false, false);
    $.FE.RegisterShortcut($.FE.KEYCODE.I, 'italic', null, 'I', false, false);
    $.FE.RegisterShortcut($.FE.KEYCODE.U, 'underline', null, 'U', false, false);
    $.FE.RegisterShortcut($.FE.KEYCODE.S, 'strikeThrough', null, 'S', false, false);
    $.FE.RegisterShortcut($.FE.KEYCODE.CLOSE_SQUARE_BRACKET, 'indent', null, ']', false, false);
    $.FE.RegisterShortcut($.FE.KEYCODE.OPEN_SQUARE_BRACKET, 'outdent', null, '[', false, false);
    $.FE.RegisterShortcut($.FE.KEYCODE.Z, 'undo', null, 'Z', false, false);
    $.FE.RegisterShortcut($.FE.KEYCODE.Z, 'redo', null, 'Z', true, false);
    $.FE.RegisterShortcut($.FE.KEYCODE.Y, 'redo', null, 'Y', false, false);
    $.FE.MODULES.shortcuts = function (editor) {
        var inverse_map = null;

        function get(cmd) {
            if (!editor.opts.shortcutsHint) return null;
            if (!inverse_map) {
                inverse_map = {};
                for (var key in $.FE.SHORTCUTS_MAP) {
                    if ($.FE.SHORTCUTS_MAP.hasOwnProperty(key) && editor.opts.shortcutsEnabled.indexOf($.FE.SHORTCUTS_MAP[key].cmd) >= 0) {
                        inverse_map[$.FE.SHORTCUTS_MAP[key].cmd + '.' + ($.FE.SHORTCUTS_MAP[key].val || '')] = {
                            shift: $.FE.SHORTCUTS_MAP[key].shift,
                            option: $.FE.SHORTCUTS_MAP[key].option,
                            letter: $.FE.SHORTCUTS_MAP[key].letter
                        }
                    }
                }
            }
            var srct = inverse_map[cmd];
            if (!srct) return null;
            return (editor.helpers.isMac() ? String.fromCharCode(8984) : editor.language.translate('Ctrl') + '+') +
                (srct.shift ? (editor.helpers.isMac() ? String.fromCharCode(8679) : editor.language.translate('Shift') + '+') : '') +
                (srct.option ? (editor.helpers.isMac() ? String.fromCharCode(8997) : editor.language.translate('Alt') + '+') : '') +
                srct.letter;
        }

        var active = false;

        function exec(e) {
            if (!editor.core.hasFocus()) return true;
            var keycode = e.which;
            var ctrlKey = navigator.userAgent.indexOf('Mac OS X') != -1 ? e.metaKey : e.ctrlKey;
            if (e.type == 'keyup' && active) {
                if (keycode != $.FE.KEYCODE.META) {
                    active = false;
                    return false;
                }
            }
            if (e.type == 'keydown') active = false;
            var map_key = (e.shiftKey ? '^' : '') + (e.altKey ? '@' : '') + keycode;
            if (ctrlKey && $.FE.SHORTCUTS_MAP[map_key]) {
                var cmd = $.FE.SHORTCUTS_MAP[map_key].cmd;
                if (cmd && editor.opts.shortcutsEnabled.indexOf(cmd) >= 0) {
                    var val = $.FE.SHORTCUTS_MAP[map_key].val;
                    var $btn;
                    if (cmd && !val) {
                        $btn = editor.$tb.find('.fr-command[data-cmd="' + cmd + '"]');
                    } else if (cmd && val) {
                        $btn = editor.$tb.find('.fr-command[data-cmd="' + cmd + '"][data-param1="' + val + '"]');
                    }
                    if ($btn.length) {
                        e.preventDefault();
                        e.stopPropagation();
                        $btn.parents('.fr-toolbar').data('instance', editor);
                        if (e.type == 'keydown') {
                            editor.button.exec($btn);
                            active = true;
                        }
                        return false;
                    } else if (cmd && (editor.commands[cmd] || ($.FE.COMMANDS[cmd] && $.FE.COMMANDS[cmd].callback))) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.type == 'keydown') {
                            (editor.commands[cmd] || $.FE.COMMANDS[cmd].callback)();
                            active = true;
                        }
                        return false;
                    }
                }
            }
        }

        function _init() {
            editor.events.on('keydown', exec, true);
            editor.events.on('keyup', exec, true);
        }

        return {_init: _init, get: get}
    }
    $.FE.MODULES.snapshot = function (editor) {
        function _getNodeIndex(node) {
            var childNodes = node.parentNode.childNodes;
            var idx = 0;
            var prevNode = null;
            for (var i = 0; i < childNodes.length; i++) {
                if (prevNode) {
                    var isEmptyText = (childNodes[i].nodeType === Node.TEXT_NODE && childNodes[i].textContent === '');
                    var twoTexts = (prevNode.nodeType === Node.TEXT_NODE && childNodes[i].nodeType === Node.TEXT_NODE);
                    var emptyPrevNode = (prevNode.nodeType === Node.TEXT_NODE && prevNode.textContent === '');
                    if (!isEmptyText && !twoTexts && !emptyPrevNode) idx++;
                }
                if (childNodes[i] == node) return idx;
                prevNode = childNodes[i];
            }
        }

        function _getNodeLocation(node) {
            var loc = [];
            if (!node.parentNode) return [];
            while (!editor.node.isElement(node)) {
                loc.push(_getNodeIndex(node));
                node = node.parentNode;
            }
            return loc.reverse();
        }

        function _getRealNodeOffset(node, offset) {
            while (node && node.nodeType === Node.TEXT_NODE) {
                var prevNode = node.previousSibling;
                if (prevNode && prevNode.nodeType == Node.TEXT_NODE) {
                    offset += prevNode.textContent.length;
                }
                node = prevNode;
            }
            return offset;
        }

        function _getRange(range) {
            return {
                scLoc: _getNodeLocation(range.startContainer),
                scOffset: _getRealNodeOffset(range.startContainer, range.startOffset),
                ecLoc: _getNodeLocation(range.endContainer),
                ecOffset: _getRealNodeOffset(range.endContainer, range.endOffset)
            }
        }

        function get() {
            var snapshot = {};
            editor.events.trigger('snapshot.before');
            snapshot.html = (editor.$wp ? editor.$el.html() : editor.$oel.get(0).outerHTML).replace(/ style=""/g, '');
            snapshot.ranges = [];
            if (editor.$wp && editor.selection.inEditor() && editor.core.hasFocus()) {
                var ranges = editor.selection.ranges();
                for (var i = 0; i < ranges.length; i++) {
                    snapshot.ranges.push(_getRange(ranges[i]));
                }
            }
            editor.events.trigger('snapshot.after', [snapshot]);
            return snapshot;
        }

        function _getNodeByLocation(loc) {
            var node = editor.el;
            for (var i = 0; i < loc.length; i++) {
                node = node.childNodes[loc[i]];
            }
            return node;
        }

        function _restoreRange(sel, range_snapshot) {
            try {
                var startNode = _getNodeByLocation(range_snapshot.scLoc);
                var startOffset = range_snapshot.scOffset;
                var endNode = _getNodeByLocation(range_snapshot.ecLoc);
                var endOffset = range_snapshot.ecOffset;
                var range = editor.doc.createRange();
                range.setStart(startNode, startOffset);
                range.setEnd(endNode, endOffset);
                sel.addRange(range);
            } catch (ex) {
                console.warn(ex)
            }
        }

        function restore(snapshot) {
            if (editor.$el.html() != snapshot.html) {
                if (editor.opts.htmlExecuteScripts) {
                    editor.$el.html(snapshot.html);
                } else {
                    editor.el.innerHTML = snapshot.html;
                }
            }
            var sel = editor.selection.get();
            editor.selection.clear();
            editor.events.focus(true);
            for (var i = 0; i < snapshot.ranges.length; i++) {
                _restoreRange(sel, snapshot.ranges[i]);
            }
        }

        function equal(s1, s2) {
            if (s1.html != s2.html) return false;
            if (editor.core.hasFocus() && JSON.stringify(s1.ranges) != JSON.stringify(s2.ranges)) return false;
            return true;
        }

        return {get: get, restore: restore, equal: equal}
    };
    $.FE.MODULES.undo = function (editor) {
        function _disableBrowserUndo(e) {
            var keyCode = e.which;
            var ctrlKey = editor.keys.ctrlKey(e);
            if (ctrlKey) {
                if (keyCode == 90 && e.shiftKey) {
                    e.preventDefault();
                }
                if (keyCode == 90) {
                    e.preventDefault();
                }
            }
        }

        function canDo() {
            if (editor.undo_stack.length === 0 || editor.undo_index <= 1) {
                return false;
            }
            return true;
        }

        function canRedo() {
            if (editor.undo_index == editor.undo_stack.length) {
                return false;
            }
            return true;
        }

        var last_html = null;

        function saveStep(snapshot) {
            if (!editor.undo_stack || editor.undoing || editor.el.querySelector('.fr-marker')) return false;
            if (typeof snapshot == 'undefined') {
                snapshot = editor.snapshot.get();
                if (!editor.undo_stack[editor.undo_index - 1] || !editor.snapshot.equal(editor.undo_stack[editor.undo_index - 1], snapshot)) {
                    dropRedo();
                    editor.undo_stack.push(snapshot);
                    editor.undo_index++;
                    if (snapshot.html != last_html) {
                        editor.events.trigger('contentChanged');
                        last_html = snapshot.html;
                    }
                }
            } else {
                dropRedo();
                if (editor.undo_index > 0) {
                    editor.undo_stack[editor.undo_index - 1] = snapshot;
                } else {
                    editor.undo_stack.push(snapshot);
                    editor.undo_index++;
                }
            }
        }

        function dropRedo() {
            if (!editor.undo_stack || editor.undoing) return false;
            while (editor.undo_stack.length > editor.undo_index) {
                editor.undo_stack.pop();
            }
        }

        function _do() {
            if (editor.undo_index > 1) {
                editor.undoing = true;
                var snapshot = editor.undo_stack[--editor.undo_index - 1];
                clearTimeout(editor._content_changed_timer);
                editor.snapshot.restore(snapshot);
                last_html = snapshot.html;
                editor.popups.hideAll();
                editor.toolbar.enable();
                editor.events.trigger('contentChanged');
                editor.events.trigger('commands.undo');
                editor.undoing = false;
            }
        }

        function _redo() {
            if (editor.undo_index < editor.undo_stack.length) {
                editor.undoing = true;
                var snapshot = editor.undo_stack[editor.undo_index++];
                clearTimeout(editor._content_changed_timer)
                editor.snapshot.restore(snapshot);
                last_html = snapshot.html;
                editor.popups.hideAll();
                editor.toolbar.enable();
                editor.events.trigger('contentChanged');
                editor.events.trigger('commands.redo');
                editor.undoing = false;
            }
        }

        function reset() {
            last_html = (editor.$wp ? editor.$el.html() : editor.$oel.get(0).outerHTML).replace(/ style=""/g, '');
            editor.undo_index = 0;
            editor.undo_stack = [];
        }

        function _destroy() {
            editor.undo_stack = [];
        }

        function _init() {
            reset();
            editor.events.on('initialized', function () {
                last_html = (editor.$wp ? editor.$el.html() : editor.$oel.get(0).outerHTML).replace(/ style=""/g, '');
            });
            editor.events.on('blur', function () {
                if (!editor.el.querySelector('.fr-dragging')) {
                    editor.undo.saveStep();
                }
            })
            editor.events.on('keydown', _disableBrowserUndo);
            editor.events.on('destroy', _destroy);
        }

        return {
            _init: _init,
            run: _do,
            redo: _redo,
            canDo: canDo,
            canRedo: canRedo,
            dropRedo: dropRedo,
            reset: reset,
            saveStep: saveStep
        }
    };
    $.FE.ICON_TEMPLATES = {
        font_awesome: '<i class="fa fa-[NAME]" aria-hidden="true"></i>',
        font_awesome_5: '<i class="fas fa-[FA5NAME]" aria-hidden="true"></i>',
        font_awesome_5r: '<i class="far fa-[FA5NAME]" aria-hidden="true"></i>',
        font_awesome_5l: '<i class="fal fa-[FA5NAME]" aria-hidden="true"></i>',
        font_awesome_5b: '<i class="fab fa-[FA5NAME]" aria-hidden="true"></i>',
        text: '<span style="text-align: center;">[NAME]</span>',
        image: '<img src=[SRC] alt=[ALT] />',
        svg: '<svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">[PATH]</svg>',
        empty: ' '
    }
    $.FE.ICONS = {
        bold: {NAME: 'bold'},
        italic: {NAME: 'italic'},
        underline: {NAME: 'underline'},
        strikeThrough: {NAME: 'strikethrough'},
        subscript: {NAME: 'subscript'},
        superscript: {NAME: 'superscript'},
        color: {NAME: 'tint'},
        outdent: {NAME: 'outdent'},
        indent: {NAME: 'indent'},
        undo: {NAME: 'rotate-left', FA5NAME: 'undo'},
        redo: {NAME: 'rotate-right', FA5NAME: 'redo'},
        insertHR: {NAME: 'minus'},
        clearFormatting: {NAME: 'eraser'},
        selectAll: {NAME: 'mouse-pointer'}
    }
    $.FE.DefineIconTemplate = function (name, options) {
        $.FE.ICON_TEMPLATES[name] = options;
    }
    $.FE.DefineIcon = function (name, options) {
        $.FE.ICONS[name] = options;
    }
    $.extend($.FE.DEFAULTS, {iconsTemplate: 'font_awesome'});
    $.FE.MODULES.icon = function (editor) {
        function create(command) {
            var icon = null;
            var info = $.FE.ICONS[command];
            if (typeof info != 'undefined') {
                var template = info.template || $.FE.ICON_DEFAULT_TEMPLATE || editor.opts.iconsTemplate;
                if (template && template.apply) {
                    template = template.apply(editor);
                }
                if (!info.FA5NAME) {
                    info.FA5NAME = info.NAME;
                }
                if (template && (template = $.FE.ICON_TEMPLATES[template])) {
                    icon = template.replace(/\[([a-zA-Z0-9]*)\]/g, function (str, a1) {
                        return (a1 == 'NAME' ? (info[a1] || command) : info[a1]);
                    });
                }
            }
            return (icon || command);
        }

        function getTemplate(command) {
            var info = $.FE.ICONS[command];
            var template = editor.opts.iconsTemplate;
            if (typeof info != 'undefined') {
                template = info.template || $.FE.ICON_DEFAULT_TEMPLATE || editor.opts.iconsTemplate;
                return template;
            }
            return template;
        }

        return {create: create, getTemplate: getTemplate}
    };
    $.extend($.FE.DEFAULTS, {tooltips: true});
    $.FE.MODULES.tooltip = function (editor) {
        function hide() {
            if (editor.helpers.isMobile()) return false;
            if (editor.$tooltip) editor.$tooltip.removeClass('fr-visible').css('left', '-3000px').css('position', 'fixed');
        }

        function to($el, above) {
            if (editor.helpers.isMobile()) return false;
            if (!$el.data('title')) {
                $el.data('title', $el.attr('title'));
            }
            if (!$el.data('title')) return false;
            if (!editor.$tooltip) _init();
            $el.removeAttr('title');
            editor.$tooltip.text(editor.language.translate($el.data('title')));
            editor.$tooltip.addClass('fr-visible');
            var left = $el.offset().left + ($el.outerWidth() - editor.$tooltip.outerWidth()) / 2;
            if (left < 0) left = 0;
            if (left + editor.$tooltip.outerWidth() > $(editor.o_win).width()) {
                left = $(editor.o_win).width() - editor.$tooltip.outerWidth();
            }
            if (typeof above == 'undefined') above = editor.opts.toolbarBottom;
            var top = !above ? $el.offset().top + $el.outerHeight() : $el.offset().top - editor.$tooltip.height();
            editor.$tooltip.css('position', '');
            editor.$tooltip.css('left', left);
            editor.$tooltip.css('top', Math.ceil(top));
            if ($(editor.o_doc).find('body:first').css('position') != 'static') {
                editor.$tooltip.css('margin-left', -$(editor.o_doc).find('body:first').offset().left);
                editor.$tooltip.css('margin-top', -$(editor.o_doc).find('body:first').offset().top);
            } else {
                editor.$tooltip.css('margin-left', '');
                editor.$tooltip.css('margin-top', '');
            }
        }

        function bind($el, selector, above) {
            if (editor.opts.tooltips && !editor.helpers.isMobile()) {
                editor.events.$on($el, 'mouseenter', selector, function (e) {
                    if (!editor.node.hasClass(e.currentTarget, 'fr-disabled') && !editor.edit.isDisabled()) {
                        to($(e.currentTarget), above);
                    }
                }, true);
                editor.events.$on($el, 'mouseleave ' + editor._mousedown + ' ' + editor._mouseup, selector, function () {
                    hide();
                }, true);
            }
        }

        function _init() {
            if (editor.opts.tooltips && !editor.helpers.isMobile()) {
                if (!editor.shared.$tooltip) {
                    editor.shared.$tooltip = $('<div class="fr-tooltip"></div>');
                    editor.$tooltip = editor.shared.$tooltip;
                    if (editor.opts.theme) {
                        editor.$tooltip.addClass(editor.opts.theme + '-theme');
                    }
                    $(editor.o_doc).find('body:first').append(editor.$tooltip);
                } else {
                    editor.$tooltip = editor.shared.$tooltip;
                }
                editor.events.on('shared.destroy', function () {
                    editor.$tooltip.html('').removeData().remove();
                    editor.$tooltip = null;
                }, true);
            }
        }

        return {hide: hide, to: to, bind: bind}
    };
    $.FE.MODULES.button = function (editor) {
        var buttons = [];
        if (editor.opts.toolbarInline || editor.opts.toolbarContainer) {
            if (!editor.shared.buttons) editor.shared.buttons = [];
            buttons = editor.shared.buttons;
        }
        var popup_buttons = [];
        if (!editor.shared.popup_buttons) editor.shared.popup_buttons = [];
        popup_buttons = editor.shared.popup_buttons;

        function _filterButtons(butons_list, selector, search_dropdowns) {
            var $filtered_buttons = $();
            for (var i = 0; i < butons_list.length; i++) {
                var $button = $(butons_list[i]);
                if ($button.is(selector)) {
                    $filtered_buttons = $filtered_buttons.add($button);
                }
                if (search_dropdowns && $button.is('.fr-dropdown')) {
                    var $dropdown_menu_items = $button.next().find(selector);
                    $filtered_buttons = $filtered_buttons.add($dropdown_menu_items);
                }
            }
            return $filtered_buttons;
        }

        function getButtons(selector, search_dropdowns) {
            var $buttons = $();
            var id;
            if (!selector) {
                return $buttons;
            }
            $buttons = $buttons.add(_filterButtons(buttons, selector, search_dropdowns));
            $buttons = $buttons.add(_filterButtons(popup_buttons, selector, search_dropdowns));
            for (id in editor.shared.popups) {
                if (editor.shared.popups.hasOwnProperty(id)) {
                    var $popup = editor.shared.popups[id];
                    var $popup_buttons = $popup.children().find(selector);
                    $buttons = $buttons.add($popup_buttons);
                }
            }
            for (id in editor.shared.modals) {
                if (editor.shared.modals.hasOwnProperty(id)) {
                    var $modal_hash = editor.shared.modals[id];
                    var $modal_buttons = $modal_hash.$modal.find(selector);
                    $buttons = $buttons.add($modal_buttons);
                }
            }
            return $buttons;
        }

        function _dropdownButtonClick($btn) {
            var $dropdown = $btn.next();
            var active = editor.node.hasClass($btn.get(0), 'fr-active');
            var $active_dropdowns = getButtons('.fr-dropdown.fr-active').not($btn);
            var inst = $btn.parents('.fr-toolbar, .fr-popup').data('instance') || editor;
            if (inst.helpers.isIOS() && !inst.el.querySelector('.fr-marker')) {
                inst.selection.save();
                inst.selection.clear();
                inst.selection.restore();
            }
            if (!active) {
                var cmd = $btn.data('cmd');
                $dropdown.find('.fr-command').removeClass('fr-active').attr('aria-selected', false);
                if ($.FE.COMMANDS[cmd] && $.FE.COMMANDS[cmd].refreshOnShow) {
                    $.FE.COMMANDS[cmd].refreshOnShow.apply(inst, [$btn, $dropdown]);
                }
                $dropdown.css('left', $btn.offset().left - $btn.parent().offset().left - (editor.opts.direction == 'rtl' ? $dropdown.width() - $btn.outerWidth() : 0));
                $dropdown.addClass('test-height')
                var ht = $dropdown.outerHeight();
                $dropdown.removeClass('test-height')
                $dropdown.css('top', '').css('bottom', '');
                if (!editor.opts.toolbarBottom && ($dropdown.offset().top + $btn.outerHeight() + ht < $(editor.o_doc).height())) {
                    $dropdown.css('top', $btn.position().top + $btn.outerHeight());
                } else {
                    $dropdown.css('bottom', $btn.parents('.fr-popup, .fr-toolbar').first().height() - $btn.position().top);
                }
            }
            $btn.addClass('fr-blink').toggleClass('fr-active');
            if ($btn.hasClass('fr-options')) {
                $btn.prev().toggleClass('fr-expanded');
            }
            if ($btn.hasClass('fr-active')) {
                $dropdown.attr('aria-hidden', false);
                $btn.attr('aria-expanded', true);
            } else {
                $dropdown.attr('aria-hidden', true);
                $btn.attr('aria-expanded', false);
            }
            setTimeout(function () {
                $btn.removeClass('fr-blink');
            }, 300);
            $dropdown.css('margin-left', '');
            if ($dropdown.offset().left + $dropdown.outerWidth() > editor.$sc.offset().left + editor.$sc.width()) {
                $dropdown.css('margin-left', -($dropdown.offset().left + $dropdown.outerWidth() - editor.$sc.offset().left - editor.$sc.width()))
            }
            if ($dropdown.offset().left < editor.$sc.offset().left && editor.opts.direction == 'rtl') {
                $dropdown.css('margin-left', editor.$sc.offset().left);
            }
            $active_dropdowns.removeClass('fr-active').attr('aria-expanded', false).next().attr('aria-hidden', true);
            $active_dropdowns.prev('.fr-expanded').removeClass('fr-expanded');
            $active_dropdowns.parent('.fr-toolbar:not(.fr-inline)').css('zIndex', '');
            if ($btn.parents('.fr-popup').length === 0 && !editor.opts.toolbarInline) {
                if (editor.node.hasClass($btn.get(0), 'fr-active')) {
                    editor.$tb.css('zIndex', (editor.opts.zIndex || 1) + 4);
                } else {
                    editor.$tb.css('zIndex', '');
                }
            }
            var $active_element = $dropdown.find('a.fr-command.fr-active:first');
            if (!editor.helpers.isMobile()) {
                if ($active_element.length) {
                    editor.accessibility.focusToolbarElement($active_element);
                } else {
                    editor.accessibility.focusToolbarElement($btn);
                }
            }
        }

        function exec($btn) {
            $btn.addClass('fr-blink');
            setTimeout(function () {
                $btn.removeClass('fr-blink');
            }, 500);
            var cmd = $btn.data('cmd');
            var params = [];
            while (typeof $btn.data('param' + (params.length + 1)) != 'undefined') {
                params.push($btn.data('param' + (params.length + 1)));
            }
            var $active_dropdowns = getButtons('.fr-dropdown.fr-active');
            if ($active_dropdowns.length) {
                $active_dropdowns.removeClass('fr-active').attr('aria-expanded', false).next().attr('aria-hidden', true);
                $active_dropdowns.prev('.fr-expanded').removeClass('fr-expanded');
                $active_dropdowns.parent('.fr-toolbar:not(.fr-inline)').css('zIndex', '');
            }
            $btn.parents('.fr-popup, .fr-toolbar').data('instance').commands.exec(cmd, params);
        }

        function _commandButtonClick($btn) {
            exec($btn);
        }

        function click($btn) {
            var inst = $btn.parents('.fr-popup, .fr-toolbar').data('instance');
            if ($btn.parents('.fr-popup').length === 0 && !$btn.data('popup')) {
                inst.popups.hideAll();
            }
            if (inst.popups.areVisible() && !inst.popups.areVisible(inst)) {
                for (var i = 0; i < $.FE.INSTANCES.length; i++) {
                    if ($.FE.INSTANCES[i] != inst && $.FE.INSTANCES[i].popups && $.FE.INSTANCES[i].popups.areVisible()) {
                        $.FE.INSTANCES[i].$el.find('.fr-marker').remove();
                    }
                }
                inst.popups.hideAll();
            }
            if (editor.node.hasClass($btn.get(0), 'fr-dropdown')) {
                _dropdownButtonClick($btn);
            } else {
                _commandButtonClick($btn);
                if ($.FE.COMMANDS[$btn.data('cmd')] && $.FE.COMMANDS[$btn.data('cmd')].refreshAfterCallback !== false) {
                    inst.button.bulkRefresh();
                }
            }
        }

        function _click(e) {
            var $btn = $(e.currentTarget);
            click($btn);
        }

        function hideActiveDropdowns($el) {
            var $active_dropdowns = $el.find('.fr-dropdown.fr-active');
            if ($active_dropdowns.length) {
                $active_dropdowns.removeClass('fr-active').attr('aria-expanded', false).next().attr('aria-hidden', true);
                $active_dropdowns.parent('.fr-toolbar:not(.fr-inline)').css('zIndex', '');
                $active_dropdowns.prev().removeClass('fr-expanded')
            }
        }

        function _dropdownMenuClick(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function _dropdownWrapperClick(e) {
            e.stopPropagation();
            if (!editor.helpers.isMobile()) {
                return false;
            }
        }

        function bindCommands($el, tooltipAbove) {
            editor.events.bindClick($el, '.fr-command:not(.fr-disabled)', _click);
            editor.events.$on($el, editor._mousedown + ' ' + editor._mouseup + ' ' + editor._move, '.fr-dropdown-menu', _dropdownMenuClick, true);
            editor.events.$on($el, editor._mousedown + ' ' + editor._mouseup + ' ' + editor._move, '.fr-dropdown-menu .fr-dropdown-wrapper', _dropdownWrapperClick, true);
            var _document = $el.get(0).ownerDocument;
            var _window = 'defaultView' in _document ? _document.defaultView : _document.parentWindow;
            var hideDropdowns = function (e) {
                if (!e || (e.type == editor._mouseup && e.target != $('html').get(0)) || (e.type == 'keydown' && ((editor.keys.isCharacter(e.which) && !editor.keys.ctrlKey(e)) || e.which == $.FE.KEYCODE.ESC))) {
                    hideActiveDropdowns($el);
                }
            }
            editor.events.$on($(_window), editor._mouseup + ' resize keydown', hideDropdowns, true);
            if (editor.opts.iframe) {
                editor.events.$on(editor.$win, editor._mouseup, hideDropdowns, true);
            }
            if (editor.node.hasClass($el.get(0), 'fr-popup')) {
                $.merge(popup_buttons, $el.find('.fr-btn').toArray());
            } else {
                $.merge(buttons, $el.find('.fr-btn').toArray());
            }
            editor.tooltip.bind($el, '.fr-btn, .fr-title', tooltipAbove);
        }

        function _content(command, info) {
            var c = '';
            if (info.html) {
                if (typeof info.html == 'function') {
                    c += info.html.call(editor);
                } else {
                    c += info.html;
                }
            } else {
                var options = info.options;
                if (typeof options == 'function') options = options();
                c += '<ul class="fr-dropdown-list" role="presentation">';
                for (var val in options) {
                    if (options.hasOwnProperty(val)) {
                        var shortcut = editor.shortcuts.get(command + '.' + val);
                        if (shortcut) {
                            shortcut = '<span class="fr-shortcut">' + shortcut + '</span>';
                        } else {
                            shortcut = '';
                        }
                        c += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="' + (info.type === 'options' ? command.replace(/Options/g, '') : command) + '" data-param1="' + val + '" title="' + options[val] + '">' + editor.language.translate(options[val]) + '</a></li>';
                    }
                }
                c += '</ul>';
            }
            return c;
        }

        function _build(command, info, visible) {
            info = $.extend(true, {}, info);
            if (editor.helpers.isMobile() && info.showOnMobile === false) return '';
            var display_selection = info.displaySelection;
            if (typeof display_selection == 'function') {
                display_selection = display_selection(editor);
            }
            var icon = '';
            if (info.type !== 'options') {
                if (display_selection) {
                    var default_selection = (typeof info.defaultSelection == 'function' ? info.defaultSelection(editor) : info.defaultSelection);
                    icon = '<span style="width:' + (info.displaySelectionWidth || 100) + 'px">' + editor.language.translate(default_selection || info.title) + '</span>';
                } else {
                    icon = editor.icon.create(info.icon || command);
                    icon += '<span class="fr-sr-only">' + (editor.language.translate(info.title) || '') + '</span>';
                }
            }
            var popup = info.popup ? ' data-popup="true"' : '';
            var modal = info.modal ? ' data-modal="true"' : '';
            var shortcut = editor.shortcuts.get(command + '.');
            if (shortcut) {
                shortcut = ' (' + shortcut + ')';
            } else {
                shortcut = '';
            }
            var button_id = command + '-' + editor.id;
            var dropdown_id = 'dropdown-menu-' + button_id;
            var btn = '<button id="' + button_id + '"type="button" tabIndex="-1" role="button"' + (info.toggle ? ' aria-pressed="false"' : '') + (info.type == 'dropdown' || info.type == 'options' ? ' aria-controls="' + dropdown_id + '" aria-expanded="false" aria-haspopup="true"' : '') + (info.disabled ? ' aria-disabled="true"' : '') + ' title="' + (editor.language.translate(info.title) || '') + shortcut + '" class="fr-command fr-btn' + (info.type == 'dropdown' || info.type == 'options' ? ' fr-dropdown' : '') + (info.type == 'options' ? ' fr-options' : '') + (' fr-btn-' + editor.icon.getTemplate(info.icon)) + (info.displaySelection ? ' fr-selection' : '') + (info.back ? ' fr-back' : '') + (info.disabled ? ' fr-disabled' : '') + (!visible ? ' fr-hidden' : '') + '" data-cmd="' + command + '"' + popup + modal + '>' + icon + '</button>';
            if (info.type == 'dropdown' || info.type == 'options') {
                var dropdown = '<div id="' + dropdown_id + '" class="fr-dropdown-menu" role="listbox" aria-labelledby="' + button_id + '" aria-hidden="true"><div class="fr-dropdown-wrapper" role="presentation"><div class="fr-dropdown-content" role="presentation">';
                dropdown += _content(command, info);
                dropdown += '</div></div></div>';
                btn += dropdown;
            }
            if (info.hasOptions && info.hasOptions.apply(editor)) {
                info.type = 'options';
                info.hasOptions = false;
                btn = '<div class="fr-btn-wrap">' + btn + _build(command + 'Options', info, visible) + '</div>';
            }
            return btn;
        }

        function buildList(buttons, visible_buttons) {
            var str = '';
            for (var i = 0; i < buttons.length; i++) {
                var cmd_name = buttons[i];
                var cmd_info = $.FE.COMMANDS[cmd_name];
                if (cmd_info && typeof cmd_info.plugin !== 'undefined' && editor.opts.pluginsEnabled.indexOf(cmd_info.plugin) < 0) continue;
                if (cmd_info) {
                    var visible = typeof visible_buttons != 'undefined' ? visible_buttons.indexOf(cmd_name) >= 0 : true;
                    str += _build(cmd_name, cmd_info, visible);
                } else if (cmd_name == '|') {
                    str += '<div class="fr-separator fr-vs" role="separator" aria-orientation="vertical"></div>';
                } else if (cmd_name == '-') {
                    str += '<div class="fr-separator fr-hs" role="separator" aria-orientation="horizontal"></div>';
                }
            }
            return str;
        }

        function refresh($btn) {
            var inst = $btn.parents('.fr-popup, .fr-toolbar').data('instance') || editor;
            var cmd = $btn.data('cmd');
            var $dropdown;
            if (!editor.node.hasClass($btn.get(0), 'fr-dropdown')) {
                $btn.removeClass('fr-active');
                if ($btn.attr('aria-pressed')) $btn.attr('aria-pressed', false);
            } else {
                $dropdown = $btn.next();
            }
            if ($.FE.COMMANDS[cmd] && $.FE.COMMANDS[cmd].refresh) {
                $.FE.COMMANDS[cmd].refresh.apply(inst, [$btn, $dropdown]);
            } else if (editor.refresh[cmd]) {
                inst.refresh[cmd]($btn, $dropdown);
            }
        }

        function _bulkRefresh(btns) {
            var inst = editor.$tb ? (editor.$tb.data('instance') || editor) : editor;
            if (editor.events.trigger('buttons.refresh') === false) return true;
            setTimeout(function () {
                var focused = (inst.selection.inEditor() && inst.core.hasFocus());
                for (var i = 0; i < btns.length; i++) {
                    var $btn = $(btns[i]);
                    var cmd = $btn.data('cmd');
                    if ($btn.parents('.fr-popup').length === 0) {
                        if (focused || ($.FE.COMMANDS[cmd] && $.FE.COMMANDS[cmd].forcedRefresh)) {
                            inst.button.refresh($btn);
                        } else {
                            if (!editor.node.hasClass($btn.get(0), 'fr-dropdown')) {
                                $btn.removeClass('fr-active');
                                if ($btn.attr('aria-pressed')) $btn.attr('aria-pressed', false);
                            }
                        }
                    } else if ($btn.parents('.fr-popup').is(':visible')) {
                        inst.button.refresh($btn);
                    }
                }
            }, 0);
        }

        function bulkRefresh() {
            _bulkRefresh(buttons);
            _bulkRefresh(popup_buttons);
        }

        function _destroy() {
            buttons = [];
            popup_buttons = [];
        }

        var refresh_timeout = null;

        function delayedBulkRefresh() {
            clearTimeout(refresh_timeout);
            refresh_timeout = setTimeout(bulkRefresh, 50);
        }

        function _init() {
            if (editor.opts.toolbarInline) {
                editor.events.on('toolbar.show', bulkRefresh);
            } else {
                editor.events.on('mouseup', delayedBulkRefresh);
                editor.events.on('keyup', delayedBulkRefresh);
                editor.events.on('blur', delayedBulkRefresh);
                editor.events.on('focus', delayedBulkRefresh);
                editor.events.on('contentChanged', delayedBulkRefresh);
                if (editor.helpers.isMobile()) {
                    editor.events.$on(editor.$doc, 'selectionchange', bulkRefresh);
                }
            }
            editor.events.on('shared.destroy', _destroy);
        }

        return {
            _init: _init,
            buildList: buildList,
            bindCommands: bindCommands,
            refresh: refresh,
            bulkRefresh: bulkRefresh,
            exec: exec,
            click: click,
            hideActiveDropdowns: hideActiveDropdowns,
            getButtons: getButtons
        }
    };
    $.FE.MODULES.modals = function (editor) {
        if (!editor.shared.modals) editor.shared.modals = {};
        var modals = editor.shared.modals;
        var $overlay;

        function get(id) {
            return modals[id];
        }

        function _modalHTML(head, body) {
            var html = '<div tabIndex="-1" class="fr-modal' + (editor.opts.theme ? ' ' + editor.opts.theme + '-theme' : '') + '"><div class="fr-modal-wrapper">';
            var close_button = '<span title="' + editor.language.translate('Cancel') + '" class="fr-modal-close">&times;</span>';
            html += '<div class="fr-modal-head">' + head + close_button + '</div>';
            html += '<div tabIndex="-1" class="fr-modal-body">' + body + '</div>';
            html += '</div></div>';
            return $(html);
        }

        function create(id, head, body) {
            if (!editor.shared.$overlay) {
                editor.shared.$overlay = $('<div class="fr-overlay">').appendTo('body:first');
            }
            $overlay = editor.shared.$overlay;
            if (editor.opts.theme) {
                $overlay.addClass(editor.opts.theme + '-theme');
            }
            if (!modals[id]) {
                var $modal = _modalHTML(head, body);
                modals[id] = {
                    $modal: $modal,
                    $head: $modal.find('.fr-modal-head'),
                    $body: $modal.find('.fr-modal-body')
                };
                if (!editor.helpers.isMobile()) {
                    $modal.addClass('fr-desktop');
                }
                $modal.appendTo('body:first');
                editor.events.$on($modal, 'click', '.fr-modal-close', function () {
                    hide(id);
                }, true);
                modals[id].$body.css('margin-top', modals[id].$head.outerHeight());
                editor.events.$on($modal, 'keydown', function (e) {
                    var keycode = e.which;
                    if (keycode == $.FE.KEYCODE.ESC) {
                        hide(id);
                        editor.accessibility.focusModalButton($modal);
                        return false;
                    } else if (!$(e.target).is('input[type=text], textarea') && keycode != $.FE.KEYCODE.ARROW_UP && keycode != $.FE.KEYCODE.ARROW_DOWN && !editor.keys.isBrowserAction(e)) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    } else {
                        return true;
                    }
                }, true);
                hide(id, true);
            }
            return modals[id];
        }

        function destroy() {
            for (var i in modals) {
                var modalHash = modals[i];
                modalHash && modalHash.$modal && modalHash.$modal.removeData().remove();
            }
            $overlay && $overlay.removeData().remove();
            modals = {};
        }

        function show(id) {
            if (!modals[id]) {
                return;
            }
            var $modal = modals[id].$modal;
            $modal.data('instance', editor);
            $modal.show();
            $overlay.show();
            $(editor.o_doc).find('body:first').addClass('prevent-scroll');
            if (editor.helpers.isMobile()) {
                $(editor.o_doc).find('body:first').addClass('fr-mobile');
            }
            $modal.addClass('fr-active');
            editor.accessibility.focusModal($modal);
        }

        function hide(id, init) {
            if (!modals[id]) {
                return;
            }
            var $modal = modals[id].$modal;
            var inst = $modal.data('instance') || editor
            inst.events.enableBlur();
            $modal.hide();
            $overlay.hide();
            $(inst.o_doc).find('body:first').removeClass('prevent-scroll fr-mobile');
            $modal.removeClass('fr-active');
            if (!init) {
                inst.accessibility.restoreSelection();
                inst.events.trigger('modals.hide');
            }
        }

        function resize(id) {
            if (!modals[id]) {
                return;
            }
            var modalHash = modals[id];
            var $modal = modalHash.$modal;
            var $body = modalHash.$body;
            var height = $(editor.o_win).height();
            var $wrapper = $modal.find('.fr-modal-wrapper');
            var allWrapperHeight = $wrapper.outerHeight(true);
            var exteriorBodyHeight = $wrapper.height() - ($body.outerHeight(true) - $body.height());
            var maxHeight = height - allWrapperHeight + exteriorBodyHeight;
            var body_content_height = $body.get(0).scrollHeight;
            var newHeight = 'auto';
            if (body_content_height > maxHeight) {
                newHeight = maxHeight;
            }
            $body.height(newHeight);
        }

        function isVisible(id) {
            var $modal;
            if (typeof id === 'string') {
                if (!modals[id]) {
                    return;
                }
                $modal = modals[id].$modal
            } else {
                $modal = id;
            }
            return ($modal && editor.node.hasClass($modal, 'fr-active') && editor.core.sameInstance($modal)) || false;
        }

        function areVisible(new_instance) {
            for (var id in modals) {
                if (modals.hasOwnProperty(id)) {
                    if (isVisible(id) && (typeof new_instance == 'undefined' || modals[id].$modal.data('instance') == new_instance)) return modals[id].$modal;
                }
            }
            return false;
        }

        function _init() {
            editor.events.on('shared.destroy', destroy, true);
        }

        return {
            _init: _init,
            get: get,
            create: create,
            show: show,
            hide: hide,
            resize: resize,
            isVisible: isVisible,
            areVisible: areVisible
        }
    };
    $.FE.POPUP_TEMPLATES = {'text.edit': '[_EDIT_]'};
    $.FE.RegisterTemplate = function (name, template) {
        $.FE.POPUP_TEMPLATES[name] = template;
    }
    $.FE.MODULES.popups = function (editor) {
        if (!editor.shared.popups) editor.shared.popups = {};
        var popups = editor.shared.popups;

        function setContainer(id, $container) {
            if (!$container.is(':visible')) $container = editor.$sc;
            if (!$container.is(popups[id].data('container'))) {
                popups[id].data('container', $container);
                $container.append(popups[id]);
            }
        }

        function refreshContainer(id, $container) {
            if (!$container.is(':visible')) $container = editor.$sc;
            if ($container.find([popups[id]]).length === 0) {
                $container.append(popups[id]);
            }
        }

        function show(id, left, top, obj_height) {
            if (!isVisible(id)) {
                if (areVisible() && editor.$el.find('.fr-marker').length > 0) {
                    editor.events.disableBlur();
                    editor.selection.restore();
                } else if (!areVisible()) {
                    editor.events.disableBlur();
                    editor.events.focus();
                    editor.events.enableBlur();
                }
            }
            hideAll([id]);
            if (!popups[id]) return false;
            var $active_dropdowns = editor.button.getButtons('.fr-dropdown.fr-active');
            $active_dropdowns.removeClass('fr-active').attr('aria-expanded', false).parent('.fr-toolbar').css('zIndex', '');
            $active_dropdowns.next().attr('aria-hidden', true);
            popups[id].data('instance', editor);
            if (editor.$tb) editor.$tb.data('instance', editor);
            var width = popups[id].outerWidth();
            var is_visible = isVisible(id);
            popups[id].addClass('fr-active').removeClass('fr-hidden').find('input, textarea').removeAttr('disabled');
            var $container = popups[id].data('container');
            refreshContainer(id, $container);
            if (editor.opts.toolbarInline && $container && editor.$tb && $container.get(0) == editor.$tb.get(0)) {
                setContainer(id, editor.$sc);
                top = editor.$tb.offset().top - editor.helpers.getPX(editor.$tb.css('margin-top'));
                left = editor.$tb.offset().left + editor.$tb.outerWidth() / 2 + (parseFloat(editor.$tb.find('.fr-arrow').css('margin-left')) || 0) + editor.$tb.find('.fr-arrow').outerWidth() / 2;
                if (editor.node.hasClass(editor.$tb.get(0), 'fr-above') && top) {
                    top += editor.$tb.outerHeight();
                }
                obj_height = 0;
            }
            $container = popups[id].data('container');
            if (editor.opts.iframe && !obj_height && !is_visible) {
                if (left) left -= editor.$iframe.offset().left;
                if (top) top -= editor.$iframe.offset().top;
            }
            if ($container.is(editor.$tb)) {
                editor.$tb.css('zIndex', (editor.opts.zIndex || 1) + 4);
            } else {
                popups[id].css('zIndex', (editor.opts.zIndex || 1) + 4);
            }
            if (left) left = left - width / 2;
            if (editor.opts.toolbarBottom && $container && editor.$tb && $container.get(0) == editor.$tb.get(0)) {
                popups[id].addClass('fr-above');
                if (top) top = top - popups[id].outerHeight();
            }
            popups[id].removeClass('fr-active');
            editor.position.at(left, top, popups[id], obj_height || 0);
            popups[id].addClass('fr-active');
            if (!is_visible) {
                editor.accessibility.focusPopup(popups[id]);
            }
            if (editor.opts.toolbarInline) editor.toolbar.hide();
            editor.events.trigger('popups.show.' + id);
            _events(id)._repositionPopup();
            _unmarkExit();
        }

        function onShow(id, callback) {
            editor.events.on('popups.show.' + id, callback);
        }

        function isVisible(id) {
            return (popups[id] && editor.node.hasClass(popups[id], 'fr-active') && editor.core.sameInstance(popups[id])) || false;
        }

        function areVisible(new_instance) {
            for (var id in popups) {
                if (popups.hasOwnProperty(id)) {
                    if (isVisible(id) && (typeof new_instance == 'undefined' || popups[id].data('instance') == new_instance)) return popups[id];
                }
            }
            return false;
        }

        function hide(id) {
            var $popup = null;
            if (typeof id !== 'string') {
                $popup = id;
            } else {
                $popup = popups[id];
            }
            if ($popup && editor.node.hasClass($popup, 'fr-active')) {
                $popup.removeClass('fr-active fr-above');
                editor.events.trigger('popups.hide.' + id);
                if (editor.$tb) {
                    if (editor.opts.zIndex > 1) {
                        editor.$tb.css('zIndex', editor.opts.zIndex + 1);
                    } else {
                        editor.$tb.css('zIndex', '');
                    }
                }
                editor.events.disableBlur();
                $popup.find('input, textarea, button').filter(':focus').blur();
                $popup.find('input, textarea').attr('disabled', 'disabled');
            }
        }

        function onHide(id, callback) {
            editor.events.on('popups.hide.' + id, callback);
        }

        function get(id) {
            var $popup = popups[id];
            if ($popup && !$popup.data('inst' + editor.id)) {
                var ev = _events(id);
                _bindInstanceEvents(ev, id);
            }
            return $popup;
        }

        function onRefresh(id, callback) {
            editor.events.on('popups.refresh.' + id, callback);
        }

        function refresh(id) {
            popups[id].data('instance', editor);
            editor.events.trigger('popups.refresh.' + id);
            var btns = popups[id].find('.fr-command');
            for (var i = 0; i < btns.length; i++) {
                var $btn = $(btns[i]);
                if ($btn.parents('.fr-dropdown-menu').length === 0) {
                    editor.button.refresh($btn);
                }
            }
        }

        function hideAll(except) {
            if (typeof except == 'undefined') except = [];
            for (var id in popups) {
                if (popups.hasOwnProperty(id)) {
                    if (except.indexOf(id) < 0) {
                        hide(id);
                    }
                }
            }
        }

        editor.shared.exit_flag = false;

        function _markExit() {
            editor.shared.exit_flag = true;
        }

        function _unmarkExit() {
            editor.shared.exit_flag = false;
        }

        function _canExit() {
            return editor.shared.exit_flag;
        }

        function _buildTemplate(id, template) {
            var html = $.FE.POPUP_TEMPLATES[id];
            if (!html) return null;
            if (typeof html == 'function') html = html.apply(editor);
            for (var nm in template) {
                if (template.hasOwnProperty(nm)) {
                    html = html.replace('[_' + nm.toUpperCase() + '_]', template[nm]);
                }
            }
            return html;
        }

        function _build(id, template) {
            var $popup;
            var $container;
            var html = _buildTemplate(id, template);
            if (!html) {
                $popup = $('<div class="fr-popup fr-empty"></div>');
                $container = $('body:first');
                $container.append($popup);
                $popup.data('container', $container);
                popups[id] = $popup;
                return $popup;
            }
            $popup = $('<div class="fr-popup' + (editor.helpers.isMobile() ? ' fr-mobile' : ' fr-desktop') + (editor.opts.toolbarInline ? ' fr-inline' : '') + '"><span class="fr-arrow"></span>' + html + '</div>');
            if (editor.opts.theme) {
                $popup.addClass(editor.opts.theme + '-theme');
            }
            if (editor.opts.zIndex > 1) {
                if (!editor.opts.editInPopup) {
                    editor.$tb.css('z-index', editor.opts.zIndex + 2);
                } else {
                    $popup.css('z-index', editor.opts.zIndex + 2);
                }
            }
            if (editor.opts.direction != 'auto') {
                $popup.removeClass('fr-ltr fr-rtl').addClass('fr-' + editor.opts.direction);
            }
            $popup.find('input, textarea').attr('dir', editor.opts.direction).attr('disabled', 'disabled');
            $container = $('body:first');
            $container.append($popup);
            $popup.data('container', $container);
            popups[id] = $popup;
            editor.button.bindCommands($popup, false);
            return $popup;
        }

        function _events(id) {
            var $popup = popups[id];
            return {
                _windowResize: function () {
                    var inst = $popup.data('instance') || editor;
                    if (!inst.helpers.isMobile() && $popup.is(':visible')) {
                        inst.events.disableBlur();
                        inst.popups.hide(id);
                        inst.events.enableBlur();
                    }
                }, _inputFocus: function (e) {
                    var inst = $popup.data('instance') || editor;
                    var $target = $(e.currentTarget);
                    if ($target.is('input:file')) {
                        $target.closest('.fr-layer').addClass('fr-input-focus');
                    }
                    e.preventDefault();
                    e.stopPropagation();
                    setTimeout(function () {
                        inst.events.enableBlur();
                    }, editor.browser.msie ? 100 : 0);
                    if (inst.helpers.isMobile()) {
                        var t = $(inst.o_win).scrollTop();
                        setTimeout(function () {
                            $(inst.o_win).scrollTop(t);
                        }, 0);
                    }
                }, _inputBlur: function (e) {
                    var inst = $popup.data('instance') || editor;
                    var $target = $(e.currentTarget);
                    if ($target.is('input:file')) {
                        $target.closest('.fr-layer').removeClass('fr-input-focus');
                    }
                    if (document.activeElement != this && $(this).is(':visible')) {
                        if (inst.events.blurActive()) {
                            inst.events.trigger('blur');
                        }
                        inst.events.enableBlur();
                    }
                }, _editorKeydown: function (e) {
                    var inst = $popup.data('instance') || editor;
                    if (!inst.keys.ctrlKey(e) && e.which != $.FE.KEYCODE.ALT && e.which != $.FE.KEYCODE.ESC) {
                        if (isVisible(id) && $popup.find('.fr-back:visible').length) {
                            inst.button.exec($popup.find('.fr-back:visible:first'))
                        } else {
                            if (e.which != $.FE.KEYCODE.ALT) {
                                inst.popups.hide(id);
                            }
                        }
                    }
                }, _preventFocus: function (e) {
                    var inst = $popup.data('instance') || editor;
                    var originalTarget = e.originalEvent ? (e.originalEvent.target || e.originalEvent.originalTarget) : null;
                    if (e.type != 'mouseup' && !$(originalTarget).is(':focus')) inst.events.disableBlur();
                    if (e.type == 'mouseup' && !($(originalTarget).hasClass('fr-command') || $(originalTarget).parents('.fr-command').length > 0) && !$(originalTarget).hasClass('fr-dropdown-content')) {
                        editor.button.hideActiveDropdowns($popup);
                    }
                    if ((editor.browser.safari || editor.browser.mozilla) && e.type == 'mousedown' && $(originalTarget).is('input[type=file]')) {
                        inst.events.disableBlur();
                    }
                    var input_selector = 'input, textarea, button, select, label, .fr-command';
                    if (originalTarget && !$(originalTarget).is(input_selector) && $(originalTarget).parents(input_selector).length === 0) {
                        e.stopPropagation();
                        return false;
                    } else if (originalTarget && $(originalTarget).is(input_selector)) {
                        e.stopPropagation();
                    }
                    _unmarkExit();
                }, _editorMouseup: function () {
                    if ($popup.is(':visible') && _canExit()) {
                        if ($popup.find('input:focus, textarea:focus, button:focus, select:focus').filter(':visible').length > 0) {
                            editor.events.disableBlur();
                        }
                    }
                }, _windowMouseup: function (e) {
                    if (!editor.core.sameInstance($popup)) return true;
                    var inst = $popup.data('instance') || editor;
                    if ($popup.is(':visible') && _canExit()) {
                        e.stopPropagation();
                        inst.markers.remove();
                        inst.popups.hide(id);
                        _unmarkExit();
                    }
                }, _windowKeydown: function (e) {
                    if (!editor.core.sameInstance($popup)) return true;
                    var inst = $popup.data('instance') || editor;
                    var key_code = e.which;
                    if ($.FE.KEYCODE.ESC == key_code) {
                        if (inst.popups.isVisible(id) && inst.opts.toolbarInline) {
                            e.stopPropagation();
                            if (inst.popups.isVisible(id)) {
                                if ($popup.find('.fr-back:visible').length) {
                                    inst.button.exec($popup.find('.fr-back:visible:first'));
                                    inst.accessibility.focusPopupButton($popup);
                                } else if ($popup.find('.fr-dismiss:visible').length) {
                                    inst.button.exec($popup.find('.fr-dismiss:visible:first'));
                                } else {
                                    inst.popups.hide(id);
                                    inst.toolbar.showInline(null, true);
                                    inst.accessibility.focusPopupButton($popup);
                                }
                            }
                            return false;
                        } else {
                            if (inst.popups.isVisible(id)) {
                                if ($popup.find('.fr-back:visible').length) {
                                    inst.button.exec($popup.find('.fr-back:visible:first'));
                                    inst.accessibility.focusPopupButton($popup);
                                } else if ($popup.find('.fr-dismiss:visible').length) {
                                    inst.button.exec($popup.find('.fr-dismiss:visible:first'));
                                } else {
                                    inst.popups.hide(id);
                                    inst.accessibility.focusPopupButton($popup);
                                }
                                return false;
                            }
                        }
                    }
                }, _doPlaceholder: function () {
                    var $label = $(this).next();
                    if ($label.length === 0 && $(this).attr('placeholder')) {
                        $(this).after('<label for="' + $(this).attr('id') + '">' + $(this).attr('placeholder') + '</label>');
                    }
                    $(this).toggleClass('fr-not-empty', $(this).val() !== '');
                }, _repositionPopup: function () {
                    if (!(editor.opts.height || editor.opts.heightMax) || editor.opts.toolbarInline) return true;
                    if (editor.$wp && isVisible(id) && $popup.parent().get(0) == editor.$sc.get(0)) {
                        var p_top = $popup.offset().top - editor.$wp.offset().top;
                        var w_height = editor.$wp.outerHeight();
                        if (editor.node.hasClass($popup.get(0), 'fr-above')) p_top += $popup.outerHeight();
                        if (p_top > w_height || p_top < 0) {
                            $popup.addClass('fr-hidden');
                        } else {
                            $popup.removeClass('fr-hidden');
                        }
                    }
                }
            }
        }

        function _bindInstanceEvents(ev, id) {
            editor.events.on('mouseup', ev._editorMouseup, true);
            if (editor.$wp) editor.events.on('keydown', ev._editorKeydown);
            editor.events.on('blur', function () {
                if (areVisible()) editor.markers.remove();
                hideAll();
            });
            if (editor.$wp && !editor.helpers.isMobile()) {
                editor.events.$on(editor.$wp, 'scroll.popup' + id, ev._repositionPopup);
            }
            editor.events.on('window.mouseup', ev._windowMouseup, true);
            editor.events.on('window.keydown', ev._windowKeydown, true);
            popups[id].data('inst' + editor.id, true);
            editor.events.on('destroy', function () {
                if (editor.core.sameInstance(popups[id])) {
                    popups[id].removeClass('fr-active').appendTo('body:first');
                }
            }, true)
        }

        function create(id, template) {
            var $popup = _build(id, template);
            var ev = _events(id);
            _bindInstanceEvents(ev, id);
            editor.events.$on($popup, 'mousedown mouseup touchstart touchend touch', '*', ev._preventFocus, true);
            editor.events.$on($popup, 'focus', 'input, textarea, button, select', ev._inputFocus, true);
            editor.events.$on($popup, 'blur', 'input, textarea, button, select', ev._inputBlur, true);
            editor.accessibility.registerPopup(id);
            editor.events.$on($popup, 'keydown keyup change input', 'input, textarea', ev._doPlaceholder, true);
            if (editor.helpers.isIOS()) {
                editor.events.$on($popup, 'touchend', 'label', function () {
                    $('#' + $(this).attr('for')).prop('checked', function (i, val) {
                        return !val;
                    })
                }, true);
            }
            editor.events.$on($(editor.o_win), 'resize', ev._windowResize, true);
            return $popup;
        }

        function _destroy() {
            for (var id in popups) {
                if (popups.hasOwnProperty(id)) {
                    var $popup = popups[id];
                    if ($popup) {
                        $popup.html('').removeData().remove();
                        popups[id] = null;
                    }
                }
            }
            popups = [];
        }

        function _init() {
            editor.events.on('shared.destroy', _destroy, true);
            editor.events.on('window.mousedown', _markExit);
            editor.events.on('window.touchmove', _unmarkExit);
            editor.events.$on($(editor.o_win), 'scroll', _unmarkExit);
            editor.events.on('mousedown', function (e) {
                if (areVisible()) {
                    e.stopPropagation();
                    editor.$el.find('.fr-marker').remove();
                    _markExit();
                    editor.events.disableBlur();
                }
            })
        }

        return {
            _init: _init,
            create: create,
            get: get,
            show: show,
            hide: hide,
            onHide: onHide,
            hideAll: hideAll,
            setContainer: setContainer,
            refresh: refresh,
            onRefresh: onRefresh,
            onShow: onShow,
            isVisible: isVisible,
            areVisible: areVisible
        }
    };
    $.FE.MODULES.position = function (editor) {
        function getBoundingRect() {
            var range = editor.selection.ranges(0);
            var boundingRect = range.getBoundingClientRect();
            if ((boundingRect.top === 0 && boundingRect.left === 0 && boundingRect.width === 0) || boundingRect.height === 0) {
                var remove = false;
                if (editor.$el.find('.fr-marker').length === 0) {
                    editor.selection.save();
                    remove = true;
                }
                var $marker = editor.$el.find('.fr-marker:first');
                $marker.css('display', 'inline');
                $marker.css('line-height', '');
                var offset = $marker.offset();
                var height = $marker.outerHeight();
                $marker.css('display', 'none');
                $marker.css('line-height', 0);
                boundingRect = {}
                boundingRect.left = offset.left;
                boundingRect.width = 0;
                boundingRect.height = height;
                boundingRect.top = offset.top - (editor.opts.iframe ? 0 : editor.helpers.scrollTop());
                boundingRect.right = 1;
                boundingRect.bottom = 1;
                boundingRect.ok = true;
                if (remove) editor.selection.restore();
            }
            return boundingRect;
        }

        function _topNormalized($el, top, obj_height) {
            var height = $el.outerHeight(true);
            if (!editor.helpers.isMobile() && editor.$tb && $el.parent().get(0) != editor.$tb.get(0)) {
                var p_offset = $el.parent().offset().top;
                var new_top = top - height - (obj_height || 0);
                if ($el.parent().get(0) == editor.$sc.get(0)) p_offset = p_offset - $el.parent().position().top;
                var s_height = editor.$sc.get(0).clientHeight;
                if (p_offset + top + height > editor.$sc.offset().top + s_height && $el.parent().offset().top + new_top > 0 && new_top > 0) {
                    if (new_top > editor.$wp.scrollTop()) {
                        top = new_top;
                        $el.addClass('fr-above');
                    }
                } else {
                    $el.removeClass('fr-above');
                }
            }
            return top;
        }

        function _leftNormalized($el, left) {
            var width = $el.outerWidth(true);
            var p_offset = $el.parent().offset().left;
            if ($el.parent().get(0) == editor.$sc.get(0)) p_offset = p_offset - $el.parent().position().left;
            if (p_offset + left + width > editor.$sc.get(0).clientWidth - 10) {
                left = editor.$sc.get(0).clientWidth - width - p_offset - 10;
            }
            if (left < 0) {
                left = 10;
            }
            return left;
        }

        function forSelection($el) {
            var selection_rect = getBoundingRect();
            $el.css({top: 0, left: 0});
            var top = selection_rect.top + selection_rect.height;
            var left = selection_rect.left + selection_rect.width / 2 - $el.get(0).offsetWidth / 2 + editor.helpers.scrollLeft();
            if (!editor.opts.iframe) {
                top += editor.helpers.scrollTop();
            }
            at(left, top, $el, selection_rect.height);
        }

        function at(left, top, $el, obj_height) {
            var $container = $el.data('container');
            if ($container && ($container.get(0).tagName !== 'BODY' || $container.css('position') != 'static')) {
                if (left) left -= $container.offset().left;
                if (top) top -= $container.offset().top;
                if ($container.get(0).tagName != 'BODY') {
                    if (left) left += $container.get(0).scrollLeft;
                    if (top) top += $container.get(0).scrollTop;
                } else if ($container.css('position') == 'absolute') {
                    if (left) left += $container.position().left;
                    if (top) top += $container.position().top;
                }
            }
            if (editor.opts.iframe && $container && editor.$tb && $container.get(0) != editor.$tb.get(0)) {
                if (left) left += editor.$iframe.offset().left;
                if (top) top += editor.$iframe.offset().top;
            }
            var new_left = _leftNormalized($el, left);
            if (left) {
                $el.css('left', new_left);
                var $arrow = $el.data('fr-arrow');
                if (!$arrow) {
                    $arrow = $el.find('.fr-arrow');
                    $el.data('fr-arrow', $arrow)
                }
                if (!$arrow.data('margin-left')) $arrow.data('margin-left', editor.helpers.getPX($arrow.css('margin-left')));
                $arrow.css('margin-left', left - new_left + $arrow.data('margin-left'));
            }
            if (top) {
                $el.css('top', _topNormalized($el, top, obj_height));
            }
        }

        function _updateIOSSticky(el) {
            var $el = $(el);
            var is_on = $el.is('.fr-sticky-on');
            var prev_top = $el.data('sticky-top');
            var scheduled_top = $el.data('sticky-scheduled');
            if (typeof prev_top == 'undefined') {
                $el.data('sticky-top', 0);
                var $dummy = $('<div class="fr-sticky-dummy" style="height: ' + $el.outerHeight() + 'px;"></div>');
                editor.$box.prepend($dummy);
            } else {
                editor.$box.find('.fr-sticky-dummy').css('height', $el.outerHeight());
            }
            if (editor.core.hasFocus() || editor.$tb.find('input:visible:focus').length > 0) {
                var x_scroll = editor.helpers.scrollTop();
                var x_top = Math.min(Math.max(x_scroll - editor.$tb.parent().offset().top, 0), editor.$tb.parent().outerHeight() - $el.outerHeight());
                if (x_top != prev_top && x_top != scheduled_top) {
                    clearTimeout($el.data('sticky-timeout'));
                    $el.data('sticky-scheduled', x_top);
                    if ($el.outerHeight() < x_scroll - editor.$tb.parent().offset().top) {
                        $el.addClass('fr-opacity-0');
                    }
                    $el.data('sticky-timeout', setTimeout(function () {
                        var c_scroll = editor.helpers.scrollTop();
                        var c_top = Math.min(Math.max(c_scroll - editor.$tb.parent().offset().top, 0), editor.$tb.parent().outerHeight() - $el.outerHeight());
                        if (c_top > 0 && editor.$tb.parent().get(0).tagName == 'BODY') c_top += editor.$tb.parent().position().top;
                        if (c_top != prev_top) {
                            $el.css('top', Math.max(c_top, 0));
                            $el.data('sticky-top', c_top);
                            $el.data('sticky-scheduled', c_top);
                        }
                        $el.removeClass('fr-opacity-0');
                    }, 100));
                }
                if (!is_on) {
                    $el.css('top', '0');
                    $el.width(editor.$tb.parent().width());
                    $el.addClass('fr-sticky-on');
                    editor.$box.addClass('fr-sticky-box');
                }
            } else {
                clearTimeout($(el).css('sticky-timeout'));
                $el.css('top', '0');
                $el.css('position', '');
                $el.width('');
                $el.data('sticky-top', 0);
                $el.removeClass('fr-sticky-on');
                editor.$box.removeClass('fr-sticky-box');
            }
        }

        function _updateSticky(el) {
            if (!el.offsetWidth) {
                return;
            }
            var el_top;
            var el_bottom;
            var $el = $(el);
            var height = $el.outerHeight();
            var prev_top = $el.data('sticky-top');
            var position = $el.data('sticky-position');
            var viewport_height = $(editor.opts.scrollableContainer == 'body' ? editor.o_win : editor.opts.scrollableContainer).outerHeight();
            var scrollable_top = 0;
            var scrollable_bottom = 0;
            if (editor.opts.scrollableContainer !== 'body') {
                scrollable_top = editor.$sc.offset().top;
                scrollable_bottom = $(editor.o_win).outerHeight() - scrollable_top - viewport_height;
            }
            var offset_top = editor.opts.scrollableContainer == 'body' ? editor.helpers.scrollTop() : scrollable_top;
            var is_on = $el.is('.fr-sticky-on');
            if (!$el.data('sticky-parent')) {
                $el.data('sticky-parent', $el.parent());
            }
            var $parent = $el.data('sticky-parent');
            var parent_top = $parent.offset().top;
            var parent_height = $parent.outerHeight();
            if (!$el.data('sticky-offset') && (typeof prev_top === 'undefined')) {
                $el.data('sticky-offset', true);
                $el.after('<div class="fr-sticky-dummy" style="height: ' + height + 'px;"></div>');
            } else {
                editor.$box.find('.fr-sticky-dummy').css('height', height + 'px');
            }
            if (!position) {
                var skip_setting_fixed = $el.css('top') !== 'auto' || $el.css('bottom') !== 'auto';
                if (!skip_setting_fixed) {
                    $el.css('position', 'fixed');
                }
                position = {
                    top: editor.node.hasClass($el.get(0), 'fr-top'),
                    bottom: editor.node.hasClass($el.get(0), 'fr-bottom')
                };
                if (!skip_setting_fixed) {
                    $el.css('position', '');
                }
                $el.data('sticky-position', position);
                $el.data('top', editor.node.hasClass($el.get(0), 'fr-top') ? $el.css('top') : 'auto');
                $el.data('bottom', editor.node.hasClass($el.get(0), 'fr-bottom') ? $el.css('bottom') : 'auto');
            }
            var isFixedToTop = function () {
                return parent_top < offset_top + el_top && parent_top + parent_height - height >= offset_top + el_top;
            }
            var isFixedToBottom = function () {
                return parent_top + height < offset_top + viewport_height - el_bottom && parent_top + parent_height > offset_top + viewport_height - el_bottom;
            }
            el_top = editor.helpers.getPX($el.data('top'));
            el_bottom = editor.helpers.getPX($el.data('bottom'));
            var at_top = (position.top && isFixedToTop() && (editor.helpers.isInViewPort(editor.$sc.get(0)) || editor.opts.scrollableContainer == 'body'));
            var at_bottom = (position.bottom && isFixedToBottom());
            if (at_top || at_bottom) {
                $el.css('width', $parent.get(0).getBoundingClientRect().width + 'px');
                if (!is_on) {
                    $el.addClass('fr-sticky-on')
                    $el.removeClass('fr-sticky-off');
                    if ($el.css('top')) {
                        if ($el.data('top') != 'auto') {
                            $el.css('top', editor.helpers.getPX($el.data('top')) + scrollable_top);
                        } else {
                            $el.data('top', 'auto');
                        }
                    }
                    if ($el.css('bottom')) {
                        if ($el.data('bottom') != 'auto') {
                            $el.css('bottom', editor.helpers.getPX($el.data('bottom')) + scrollable_bottom);
                        } else {
                            $el.css('bottom', 'auto');
                        }
                    }
                }
            } else {
                if (!editor.node.hasClass($el.get(0), 'fr-sticky-off')) {
                    $el.width('');
                    $el.removeClass('fr-sticky-on');
                    $el.addClass('fr-sticky-off');
                    if ($el.css('top') && $el.data('top') != 'auto' && position.top) {
                        $el.css('top', 0);
                    }
                    if ($el.css('bottom') && $el.data('bottom') != 'auto' && position.bottom) {
                        $el.css('bottom', 0);
                    }
                }
            }
        }

        function _testSticky() {
            return false;
        }

        function _initSticky() {
            if (!_testSticky()) {
                editor._stickyElements = [];
                if (editor.helpers.isIOS()) {
                    var animate = function () {
                        editor.helpers.requestAnimationFrame()(animate);
                        if (editor.events.trigger('position.refresh') === false) return;
                        for (var i = 0; i < editor._stickyElements.length; i++) {
                            _updateIOSSticky(editor._stickyElements[i]);
                        }
                    };
                    animate();
                    editor.events.$on($(editor.o_win), 'scroll', function () {
                        if (editor.core.hasFocus()) {
                            for (var i = 0; i < editor._stickyElements.length; i++) {
                                var $el = $(editor._stickyElements[i]);
                                var $parent = $el.parent();
                                var c_scroll = editor.helpers.scrollTop();
                                if ($el.outerHeight() < c_scroll - $parent.offset().top) {
                                    $el.addClass('fr-opacity-0');
                                    $el.data('sticky-top', -1);
                                    $el.data('sticky-scheduled', -1);
                                }
                            }
                        }
                    }, true);
                } else {
                    if (editor.opts.scrollableContainer !== 'body') {
                        editor.events.$on($(editor.opts.scrollableContainer), 'scroll', refresh, true);
                    }
                    editor.events.$on($(editor.o_win), 'scroll', refresh, true);
                    editor.events.$on($(editor.o_win), 'resize', refresh, true);
                    editor.events.on('initialized', refresh);
                    editor.events.on('focus', refresh);
                    editor.events.$on($(editor.o_win), 'resize', 'textarea', refresh, true);
                }
            }
            editor.events.on('destroy', function () {
                editor._stickyElements = [];
            });
        }

        function refresh() {
            if (editor._stickyElements) {
                for (var i = 0; i < editor._stickyElements.length; i++) {
                    _updateSticky(editor._stickyElements[i]);
                }
            }
        }

        function addSticky($el) {
            $el.addClass('fr-sticky');
            if (editor.helpers.isIOS()) $el.addClass('fr-sticky-ios');
            if (!_testSticky()) {
                $el.removeClass('fr-sticky');
                editor._stickyElements.push($el.get(0));
            }
        }

        function _init() {
            _initSticky();
        }

        return {
            _init: _init,
            forSelection: forSelection,
            addSticky: addSticky,
            refresh: refresh,
            at: at,
            getBoundingRect: getBoundingRect
        }
    };
    $.FE.MODULES.refresh = function (editor) {
        function undo($btn) {
            _setDisabled($btn, !editor.undo.canDo())
        }

        function redo($btn) {
            _setDisabled($btn, !editor.undo.canRedo());
        }

        function indent($btn) {
            if (editor.node.hasClass($btn.get(0), 'fr-no-refresh')) return false;
            var blocks = editor.selection.blocks();
            for (var i = 0; i < blocks.length; i++) {
                var p_node = blocks[i].previousSibling;
                while (p_node && p_node.nodeType == Node.TEXT_NODE && p_node.textContent.length === 0) {
                    p_node = p_node.previousSibling;
                }
                if (blocks[i].tagName == 'LI' && !p_node) {
                    _setDisabled($btn, true);
                } else {
                    _setDisabled($btn, false);
                    return true;
                }
            }
        }

        function outdent($btn) {
            if (editor.node.hasClass($btn.get(0), 'fr-no-refresh')) return false;
            var blocks = editor.selection.blocks();
            for (var i = 0; i < blocks.length; i++) {
                var prop = (editor.opts.direction == 'rtl' || $(blocks[i]).css('direction') == 'rtl') ? 'margin-right' : 'margin-left';
                if (blocks[i].tagName == 'LI' || blocks[i].parentNode.tagName == 'LI') {
                    _setDisabled($btn, false);
                    return true;
                }
                if (editor.helpers.getPX($(blocks[i]).css(prop)) > 0) {
                    _setDisabled($btn, false);
                    return true;
                }
            }
            _setDisabled($btn, true);
        }

        function _setDisabled($btn, disabled) {
            $btn.toggleClass('fr-disabled', disabled).attr('aria-disabled', disabled);
        }

        return {undo: undo, redo: redo, outdent: outdent, indent: indent}
    };
    $.extend($.FE.DEFAULTS, {editInPopup: false});
    $.FE.MODULES.textEdit = function (editor) {
        function _initPopup() {
            var txt = '<div id="fr-text-edit-' + editor.id + '" class="fr-layer fr-text-edit-layer"><div class="fr-input-line"><input type="text" placeholder="' + editor.language.translate('Text') + '" tabIndex="1"></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="updateText" tabIndex="2">' + editor.language.translate('Update') + '</button></div></div>'
            var template = {edit: txt};
            editor.popups.create('text.edit', template);
        }

        function _showPopup() {
            var $popup = editor.popups.get('text.edit');
            var text;
            if (editor.$el.prop('tagName') === 'INPUT') {
                text = editor.$el.attr('placeholder');
            } else {
                text = editor.$el.text();
            }
            $popup.find('input').val(text).trigger('change');
            editor.popups.setContainer('text.edit', editor.$sc);
            editor.popups.show('text.edit', editor.$el.offset().left + editor.$el.outerWidth() / 2, editor.$el.offset().top + editor.$el.outerHeight(), editor.$el.outerHeight());
        }

        function _initEvents() {
            editor.events.$on(editor.$el, editor._mouseup, function () {
                setTimeout(function () {
                    _showPopup();
                }, 10);
            })
        }

        function update() {
            var $popup = editor.popups.get('text.edit');
            var new_text = $popup.find('input').val();
            if (new_text.length === 0) new_text = editor.opts.placeholderText;
            if (editor.$el.prop('tagName') === 'INPUT') {
                editor.$el.attr('placeholder', new_text);
            } else {
                editor.$el.text(new_text);
            }
            editor.events.trigger('contentChanged');
            editor.popups.hide('text.edit');
        }

        function _init() {
            if (editor.opts.editInPopup) {
                _initPopup();
                _initEvents();
            }
        }

        return {_init: _init, update: update}
    };
    $.FE.RegisterCommand('updateText', {
        focus: false, undo: false, callback: function () {
            this.textEdit.update();
        }
    })
    $.extend($.FE.DEFAULTS, {
        toolbarBottom: false,
        toolbarButtons: null,
        toolbarButtonsXS: null,
        toolbarButtonsSM: null,
        toolbarButtonsMD: null,
        toolbarContainer: null,
        toolbarInline: false,
        toolbarSticky: true,
        toolbarStickyOffset: 0,
        toolbarVisibleWithoutSelection: false
    });
    $.FE.TOOLBAR_BUTTONS = ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', '|', 'fontFamily', 'fontSize', 'color', 'inlineClass', 'inlineStyle', 'paragraphStyle', 'lineHeight', '|', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'quote', '-', 'insertLink', 'insertImage', 'insertVideo', 'embedly', 'insertFile', 'insertTable', '|', 'emoticons', 'fontAwesome', 'specialCharacters', 'insertHR', 'selectAll', 'clearFormatting', '|', 'print', 'getPDF', 'spellChecker', 'help', 'html', '|', 'undo', 'redo'];
    $.FE.TOOLBAR_BUTTONS_MD = null;
    $.FE.TOOLBAR_BUTTONS_SM = ['bold', 'italic', 'underline', '|', 'fontFamily', 'fontSize', 'insertLink', 'insertImage', 'table', '|', 'undo', 'redo'];
    $.FE.TOOLBAR_BUTTONS_XS = ['bold', 'italic', 'fontFamily', 'fontSize', '|', 'undo', 'redo'];
    $.FE.MODULES.toolbar = function (editor) {
        var _buttons_map = [];
        _buttons_map[$.FE.XS] = editor.opts.toolbarButtonsXS || editor.opts.toolbarButtons || $.FE.TOOLBAR_BUTTONS_XS || $.FE.TOOLBAR_BUTTONS || [];
        _buttons_map[$.FE.SM] = editor.opts.toolbarButtonsSM || editor.opts.toolbarButtons || $.FE.TOOLBAR_BUTTONS_SM || $.FE.TOOLBAR_BUTTONS || [];
        _buttons_map[$.FE.MD] = editor.opts.toolbarButtonsMD || editor.opts.toolbarButtons || $.FE.TOOLBAR_BUTTONS_MD || $.FE.TOOLBAR_BUTTONS || [];
        _buttons_map[$.FE.LG] = editor.opts.toolbarButtons || $.FE.TOOLBAR_BUTTONS || [];

        function _addOtherButtons(buttons, toolbarButtons) {
            for (var i = 0; i < toolbarButtons.length; i++) {
                if (toolbarButtons[i] != '-' && toolbarButtons[i] != '|' && buttons.indexOf(toolbarButtons[i]) < 0) {
                    buttons.push(toolbarButtons[i]);
                }
            }
        }

        function _addButtons() {
            var _buttons = $.merge([], _screenButtons());
            _addOtherButtons(_buttons, _buttons_map[$.FE.XS]);
            _addOtherButtons(_buttons, _buttons_map[$.FE.SM]);
            _addOtherButtons(_buttons, _buttons_map[$.FE.MD]);
            _addOtherButtons(_buttons, _buttons_map[$.FE.LG]);
            for (var i = _buttons.length - 1; i >= 0; i--) {
                if (_buttons[i] != '-' && _buttons[i] != '|' && _buttons.indexOf(_buttons[i]) < i) {
                    _buttons.splice(i, 1);
                }
            }
            var buttons_list = editor.button.buildList(_buttons, _screenButtons());
            editor.$tb.append(buttons_list);
            editor.button.bindCommands(editor.$tb);
        }

        function _screenButtons() {
            var screen_size = editor.helpers.screenSize();
            return _buttons_map[screen_size];
        }

        function _showScreenButtons() {
            var c_buttons = _screenButtons();
            editor.$tb.find('.fr-separator').remove();
            editor.$tb.find('> .fr-command, > div.fr-btn-wrap').addClass('fr-hidden');
            for (var i = 0; i < c_buttons.length; i++) {
                if (c_buttons[i] == '|' || c_buttons[i] == '-') {
                    editor.$tb.append(editor.button.buildList([c_buttons[i]]));
                } else {
                    var $btn = editor.$tb.find('> .fr-command[data-cmd="' + c_buttons[i] + '"], > div.fr-btn-wrap > .fr-command[data-cmd="' + c_buttons[i] + '"]');
                    var $dropdown = null;
                    if (editor.node.hasClass($btn.next().get(0), 'fr-dropdown-menu')) $dropdown = $btn.next();
                    if (editor.node.hasClass($btn.next().get(0), 'fr-options')) {
                        $btn = $btn.parent();
                    }
                    $btn.removeClass('fr-hidden').appendTo(editor.$tb);
                    if ($dropdown) $dropdown.appendTo(editor.$tb);
                }
            }
        }

        function _setVisibility() {
            editor.events.$on($(editor.o_win), 'resize', _showScreenButtons);
            editor.events.$on($(editor.o_win), 'orientationchange', _showScreenButtons);
        }

        function showInline(e, force) {
            setTimeout(function () {
                if ((!e || e.which != $.FE.KEYCODE.ESC) && editor.selection.inEditor() && editor.core.hasFocus() && !editor.popups.areVisible()) {
                    if (editor.opts.toolbarVisibleWithoutSelection || (!editor.selection.isCollapsed() && !editor.keys.isIME()) || force) {
                        editor.$tb.data('instance', editor);
                        if (editor.events.trigger('toolbar.show', [e]) === false) return false;
                        editor.$tb.show();
                        if (!editor.opts.toolbarContainer) {
                            editor.position.forSelection(editor.$tb);
                        }
                        if (editor.opts.zIndex > 1) {
                            editor.$tb.css('z-index', editor.opts.zIndex + 1);
                        } else {
                            editor.$tb.css('z-index', null);
                        }
                    }
                }
            }, 0);
        }

        function hide(e) {
            if (e && e.type === 'blur' && document.activeElement === editor.el) {
                return false;
            }
            if (e && e.type === 'keydown' && editor.keys.ctrlKey(e)) return true;
            var $active_dropdowns = editor.button.getButtons('.fr-dropdown.fr-active');
            if ($active_dropdowns.next().find(editor.o_doc.activeElement).length) return true;
            if (editor.events.trigger('toolbar.hide') !== false) {
                editor.$tb.hide();
            }
        }

        function show() {
            if (editor.events.trigger('toolbar.show') === false) return false;
            editor.$tb.show();
        }

        var tm = null;

        function _showInlineWithTimeout(e) {
            clearTimeout(tm);
            if (!e || e.which != $.FE.KEYCODE.ESC) {
                tm = setTimeout(showInline, editor.opts.typingTimer);
            }
        }

        function _initInlineBehavior() {
            editor.events.on('window.mousedown', hide);
            editor.events.on('keydown', hide);
            editor.events.on('blur', hide);
            if (!editor.helpers.isMobile()) {
                editor.events.on('window.mouseup', showInline);
            }
            if (editor.helpers.isMobile()) {
                if (!editor.helpers.isIOS()) {
                    editor.events.on('window.touchend', showInline);
                    if (editor.browser.mozilla) {
                        setInterval(showInline, 200);
                    }
                }
            } else {
                editor.events.on('window.keyup', _showInlineWithTimeout);
            }
            editor.events.on('keydown', function (e) {
                if (e && e.which == $.FE.KEYCODE.ESC) {
                    hide();
                }
            });
            editor.events.on('keydown', function (e) {
                if (e.which == $.FE.KEYCODE.ALT) {
                    e.stopPropagation();
                    return false;
                }
            }, true);
            editor.events.$on(editor.$wp, 'scroll.toolbar', showInline);
            editor.events.on('commands.after', showInline);
            if (editor.helpers.isMobile()) {
                editor.events.$on(editor.$doc, 'selectionchange', _showInlineWithTimeout);
                editor.events.$on(editor.$doc, 'orientationchange', showInline);
            }
        }

        function _initPositioning() {
            if (editor.opts.toolbarInline) {
                editor.$sc.append(editor.$tb);
                editor.$tb.data('container', editor.$sc);
                editor.$tb.addClass('fr-inline');
                editor.$tb.prepend('<span class="fr-arrow"></span>')
                _initInlineBehavior();
                editor.opts.toolbarBottom = false;
            } else {
                if (editor.opts.toolbarBottom && !editor.helpers.isIOS()) {
                    editor.$box.append(editor.$tb);
                    editor.$tb.addClass('fr-bottom');
                    editor.$box.addClass('fr-bottom');
                } else {
                    editor.opts.toolbarBottom = false;
                    editor.$box.prepend(editor.$tb);
                    editor.$tb.addClass('fr-top');
                    editor.$box.addClass('fr-top');
                }
                editor.$tb.addClass('fr-basic');
                if (editor.opts.toolbarSticky) {
                    if (editor.opts.toolbarStickyOffset) {
                        if (editor.opts.toolbarBottom) {
                            editor.$tb.css('bottom', editor.opts.toolbarStickyOffset);
                        } else {
                            editor.$tb.css('top', editor.opts.toolbarStickyOffset);
                        }
                    }
                    editor.position.addSticky(editor.$tb);
                }
            }
        }

        function _sharedDestroy() {
            editor.$tb.html('').removeData().remove();
            editor.$tb = null;
        }

        function _destroy() {
            editor.$box.removeClass('fr-top fr-bottom fr-inline fr-basic');
            editor.$box.find('.fr-sticky-dummy').remove();
        }

        function _setDefaults() {
            if (editor.opts.theme) {
                editor.$tb.addClass(editor.opts.theme + '-theme');
            }
            if (editor.opts.zIndex > 1) {
                editor.$tb.css('z-index', editor.opts.zIndex + 1);
            }
            if (editor.opts.direction != 'auto') {
                editor.$tb.removeClass('fr-ltr fr-rtl').addClass('fr-' + editor.opts.direction);
            }
            if (!editor.helpers.isMobile()) {
                editor.$tb.addClass('fr-desktop');
            } else {
                editor.$tb.addClass('fr-mobile');
            }
            if (!editor.opts.toolbarContainer) {
                _initPositioning();
            } else {
                if (editor.opts.toolbarInline) {
                    _initInlineBehavior();
                    hide();
                }
                if (editor.opts.toolbarBottom) editor.$tb.addClass('fr-bottom'); else editor.$tb.addClass('fr-top');
            }
            _addButtons();
            _setVisibility();
            editor.accessibility.registerToolbar(editor.$tb);
            editor.events.$on(editor.$tb, editor._mousedown + ' ' + editor._mouseup, function (e) {
                var originalTarget = e.originalEvent ? (e.originalEvent.target || e.originalEvent.originalTarget) : null;
                if (originalTarget && originalTarget.tagName != 'INPUT' && !editor.edit.isDisabled()) {
                    e.stopPropagation();
                    e.preventDefault();
                    return false;
                }
            }, true);
        }

        function _init() {
            editor.$sc = $(editor.opts.scrollableContainer).first();
            if (!editor.$wp) return false;
            if (editor.opts.toolbarContainer) {
                if (!editor.shared.$tb) {
                    editor.shared.$tb = $('<div class="fr-toolbar"></div>');
                    editor.$tb = editor.shared.$tb;
                    $(editor.opts.toolbarContainer).append(editor.$tb);
                    _setDefaults();
                    editor.$tb.data('instance', editor);
                } else {
                    editor.$tb = editor.shared.$tb;
                    if (editor.opts.toolbarInline) _initInlineBehavior();
                }
                if (editor.opts.toolbarInline) {
                    editor.$box.addClass('fr-inline');
                } else {
                    editor.$box.addClass('fr-basic');
                }
                editor.events.on('focus', function () {
                    editor.$tb.data('instance', editor);
                }, true);
                editor.opts.toolbarInline = false;
            } else {
                if (editor.opts.toolbarInline) {
                    editor.$box.addClass('fr-inline');
                    if (!editor.shared.$tb) {
                        editor.shared.$tb = $('<div class="fr-toolbar"></div>');
                        editor.$tb = editor.shared.$tb;
                        _setDefaults();
                    } else {
                        editor.$tb = editor.shared.$tb;
                        _initInlineBehavior();
                    }
                } else {
                    editor.$box.addClass('fr-basic');
                    editor.$tb = $('<div class="fr-toolbar"></div>');
                    _setDefaults();
                    editor.$tb.data('instance', editor);
                }
            }
            editor.events.on('destroy', _destroy, true);
            editor.events.on(!editor.opts.toolbarInline && !editor.opts.toolbarContainer ? 'destroy' : 'shared.destroy', _sharedDestroy, true);
        }

        var disabled = false;

        function disable() {
            if (!disabled && editor.$tb) {
                editor.$tb.find('> .fr-command, .fr-btn-wrap > .fr-command').addClass('fr-disabled fr-no-refresh').attr('aria-disabled', true);
                disabled = true;
            }
        }

        function enable() {
            if (disabled && editor.$tb) {
                editor.$tb.find('> .fr-command, .fr-btn-wrap > .fr-command').removeClass('fr-disabled fr-no-refresh').attr('aria-disabled', false);
                disabled = false;
            }
            editor.button.bulkRefresh();
        }

        return {_init: _init, hide: hide, show: show, showInline: showInline, disable: disable, enable: enable}
    };
}));
!function (t) {
    "function" == typeof define && define.amd ? define(["jquery"], t) : "object" == typeof module && module.exports ? module.exports = function (e, a) {
        return a === undefined && (a = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), t(a)
    } : t(window.jQuery)
}(function (i) {
    i.extend(i.FE.DEFAULTS, {
        paragraphStyles: {
            "fr-text-gray": "Gray",
            "fr-text-bordered": "Bordered",
            "fr-text-spaced": "Spaced",
            "fr-text-uppercase": "Uppercase"
        }, paragraphMultipleStyles: !0
    }), i.FE.PLUGINS.paragraphStyle = function (o) {
        return {
            _init: function () {
            }, apply: function (e, a, t) {
                void 0 === a && (a = o.opts.paragraphStyles), void 0 === t && (t = o.opts.paragraphMultipleStyles);
                var r = "";
                t || ((r = Object.keys(a)).splice(r.indexOf(e), 1), r = r.join(" ")), o.selection.save(), o.html.wrap(!0, !0, !0, !0), o.selection.restore();
                var n = o.selection.blocks();
                o.selection.save();
                for (var s = i(n[0]).hasClass(e), l = 0; l < n.length; l++) i(n[l]).removeClass(r).toggleClass(e, !s), i(n[l]).hasClass("fr-temp-div") && i(n[l]).removeClass("fr-temp-div"), "" === i(n[l]).attr("class") && i(n[l]).removeAttr("class");
                o.html.unwrap(), o.selection.restore()
            }, refreshOnShow: function (e, a) {
                var t = o.selection.blocks();
                if (t.length) {
                    var r = i(t[0]);
                    a.find(".fr-command").each(function () {
                        var e = i(this).data("param1"), a = r.hasClass(e);
                        i(this).toggleClass("fr-active", a).attr("aria-selected", a)
                    })
                }
            }
        }
    }, i.FE.RegisterCommand("paragraphStyle", {
        type: "dropdown", html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', a = this.opts.paragraphStyles;
            for (var t in a) a.hasOwnProperty(t) && (e += '<li role="presentation"><a class="fr-command ' + t + '" tabIndex="-1" role="option" data-cmd="paragraphStyle" data-param1="' + t + '" title="' + this.language.translate(a[t]) + '">' + this.language.translate(a[t]) + "</a></li>");
            return e += "</ul>"
        }, title: "Paragraph Style", callback: function (e, a) {
            this.paragraphStyle.apply(a)
        }, refreshOnShow: function (e, a) {
            this.paragraphStyle.refreshOnShow(e, a)
        }, plugin: "paragraphStyle"
    }), i.FE.DefineIcon("paragraphStyle", {NAME: "magic"})
});
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = function (root, jQuery) {
            if (jQuery === undefined) {
                if (typeof window !== 'undefined') {
                    jQuery = require('jquery');
                } else {
                    jQuery = require('jquery')(root);
                }
            }
            return factory(jQuery);
        };
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    $.FE.PLUGINS.fullscreen = function (editor) {
        var old_scroll;

        function isActive() {
            return editor.$box.hasClass('fr-fullscreen');
        }

        var $placeholder;
        var height;
        var max_height;
        var z_index;

        function _on() {
            if (editor.helpers.isIOS() && editor.core.hasFocus()) {
                editor.$el.blur();
                setTimeout(toggle, 250);
                return false;
            }
            old_scroll = editor.helpers.scrollTop();
            editor.$box.toggleClass('fr-fullscreen');
            $('body:first').toggleClass('fr-fullscreen');
            $placeholder = $('<div style="display: none;"></div>');
            editor.$box.after($placeholder);
            if (editor.helpers.isMobile()) {
                editor.$tb.data('parent', editor.$tb.parent());
                editor.$tb.prependTo(editor.$box);
                if (editor.$tb.data('sticky-dummy')) {
                    editor.$tb.after(editor.$tb.data('sticky-dummy'));
                }
            }
            height = editor.opts.height;
            max_height = editor.opts.heightMax;
            z_index = editor.opts.zIndex;
            editor.position.refresh()
            editor.opts.height = editor.o_win.innerHeight - (editor.opts.toolbarInline ? 0 : editor.$tb.outerHeight());
            editor.opts.zIndex = 9990;
            editor.opts.heightMax = null;
            editor.size.refresh();
            if (editor.opts.toolbarInline) editor.toolbar.showInline();
            editor.events.trigger('charCounter.update');
            editor.events.trigger('codeView.update');
            editor.$win.trigger('scroll');
        }

        function _off() {
            if (editor.helpers.isIOS() && editor.core.hasFocus()) {
                editor.$el.blur();
                setTimeout(toggle, 250);
                return false;
            }
            editor.$box.toggleClass('fr-fullscreen');
            $('body:first').toggleClass('fr-fullscreen');
            editor.$tb.prependTo(editor.$tb.data('parent'));
            if (editor.$tb.data('sticky-dummy')) {
                editor.$tb.after(editor.$tb.data('sticky-dummy'));
            }
            editor.opts.height = height;
            editor.opts.heightMax = max_height;
            editor.opts.zIndex = z_index;
            editor.size.refresh();
            $(editor.o_win).scrollTop(old_scroll)
            if (editor.opts.toolbarInline) editor.toolbar.showInline();
            editor.events.trigger('charCounter.update');
            if (editor.opts.toolbarSticky) {
                if (editor.opts.toolbarStickyOffset) {
                    if (editor.opts.toolbarBottom) {
                        editor.$tb.css('bottom', editor.opts.toolbarStickyOffset).data('bottom', editor.opts.toolbarStickyOffset);
                    } else {
                        editor.$tb.css('top', editor.opts.toolbarStickyOffset).data('top', editor.opts.toolbarStickyOffset);
                    }
                }
            }
            editor.$win.trigger('scroll');
        }

        function toggle() {
            if (!isActive()) {
                _on();
            } else {
                _off();
            }
            refresh(editor.$tb.find('.fr-command[data-cmd="fullscreen"]'));
            $(window).trigger('oc.updateUi')
        }

        function refresh($btn) {
            var active = isActive();
            $btn.toggleClass('fr-active', active).attr('aria-pressed', active);
            $btn.find('> *:not(.fr-sr-only)').replaceWith(!active ? editor.icon.create('fullscreen') : editor.icon.create('fullscreenCompress'));
        }

        function _init() {
            if (!editor.$wp) return false;
            editor.events.$on($(editor.o_win), 'resize', function () {
                if (isActive()) {
                    _off();
                    _on();
                }
            });
            editor.events.on('toolbar.hide', function () {
                if (isActive() && editor.helpers.isMobile()) return false;
            })
            editor.events.on('position.refresh', function () {
                if (editor.helpers.isIOS()) {
                    return !isActive();
                }
            })
            editor.events.on('destroy', function () {
                if (isActive()) {
                    _off();
                }
            }, true);
        }

        return {_init: _init, toggle: toggle, refresh: refresh, isActive: isActive}
    }
    $.FE.RegisterCommand('fullscreen', {
        title: 'Fullscreen',
        undo: false,
        focus: false,
        accessibilityFocus: true,
        forcedRefresh: true,
        toggle: true,
        callback: function () {
            this.fullscreen.toggle();
        },
        refresh: function ($btn) {
            this.fullscreen.refresh($btn);
        },
        plugin: 'fullscreen'
    })
    $.FE.DefineIcon('fullscreen', {NAME: 'expand'});
    $.FE.DefineIcon('fullscreenCompress', {NAME: 'compress'});
}));
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = function (root, jQuery) {
            if (jQuery === undefined) {
                if (typeof window !== 'undefined') {
                    jQuery = require('jquery');
                } else {
                    jQuery = require('jquery')(root);
                }
            }
            return factory(jQuery);
        };
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    $.extend($.FE.DEFAULTS, {
        aceEditor: true,
        aceEditorVendorPath: '/',
        aceEditorOptions: {showLineNumbers: true, useSoftTabs: false, wrap: true, mode: 'ace/mode/html', tabSize: 2},
        codeBeautifierOptions: {
            end_with_newline: true,
            indent_inner_html: true,
            extra_liners: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'ul', 'ol', 'table', 'dl'],
            brace_style: 'expand',
            indent_char: '\t',
            indent_size: 1,
            wrap_line_length: 0
        },
        codeViewKeepActiveButtons: ['fullscreen']
    })
    $.FE.PLUGINS.codeView = function (editor) {
        var $html_area;
        var ace_editor;

        function isActive() {
            return editor.$box.hasClass('fr-code-view');
        }

        function get() {
            if (ace_editor) {
                return ace_editor.getValue();
            } else {
                return $html_area.val();
            }
        }

        function refresh() {
            if (isActive()) {
                if (ace_editor) {
                    ace_editor.resize();
                }
            }
        }

        var _can_focus = false;

        function _blur() {
            if (isActive()) {
                editor.events.trigger('blur')
            }
        }

        function _focus() {
            if (isActive() && _can_focus) {
                editor.events.trigger('focus')
            }
        }

        function _showText($btn) {
            var html = get();
            editor.html.set(html);
            editor.$el.blur();
            editor.$tb.find(' > .fr-command, > .fr-btn-wrap > .fr-command').not($btn).removeClass('fr-disabled').attr('aria-disabled', false);
            $btn.removeClass('fr-active').attr('aria-pressed', false);
            editor.events.focus(true);
            editor.placeholder.refresh();
            editor.undo.saveStep();
        }

        function _showHTML($btn) {
            if (!$html_area) {
                _initArea();
                if (!ace_editor && editor.opts.aceEditor && typeof ace != 'undefined') {
                    ace_editor = ace.edit($html_area.get(0));
                    ace.require('ace/config').set('basePath', editor.opts.aceEditorVendorPath);
                    ace_editor.setOptions(editor.opts.aceEditorOptions);
                    ace_editor.on('blur', _blur);
                    ace_editor.on('focus', _focus);
                } else {
                    editor.events.$on($html_area, 'keydown keyup change input', function () {
                        if (!editor.opts.height) {
                            this.rows = 1;
                            if (this.value.length === 0) {
                                this.style.height = 'auto';
                            } else {
                                this.style.height = this.scrollHeight + 'px';
                            }
                        } else {
                            this.removeAttribute('rows')
                        }
                    });
                    editor.events.$on($html_area, 'blur', _blur);
                    editor.events.$on($html_area, 'focus', _focus);
                }
            }
            editor.undo.saveStep();
            editor.html.cleanEmptyTags();
            editor.html.cleanWhiteTags(true);
            if (editor.core.hasFocus()) {
                if (!editor.core.isEmpty()) {
                    editor.selection.save();
                    editor.$el.find('.fr-marker[data-type="true"]:first').replaceWith('<span class="fr-tmp fr-sm">F</span>');
                    editor.$el.find('.fr-marker[data-type="false"]:last').replaceWith('<span class="fr-tmp fr-em">F</span>');
                }
            }
            var html = editor.html.get(false, true);
            editor.$el.find('span.fr-tmp').remove();
            editor.$box.toggleClass('fr-code-view', true);
            var was_focused = false;
            if (editor.core.hasFocus()) {
                was_focused = true;
                editor.events.disableBlur();
                editor.$el.blur();
            }
            html = html.replace(/<span class="fr-tmp fr-sm">F<\/span>/, 'FROALA-SM');
            html = html.replace(/<span class="fr-tmp fr-em">F<\/span>/, 'FROALA-EM');
            if (editor.codeBeautifier) {
                html = editor.codeBeautifier.run(html, editor.opts.codeBeautifierOptions);
            }
            var s_index;
            var e_index;
            if (ace_editor) {
                s_index = html.indexOf('FROALA-SM');
                e_index = html.indexOf('FROALA-EM');
                if (s_index > e_index) {
                    s_index = e_index;
                } else {
                    e_index = e_index - 9;
                }
                html = html.replace(/FROALA-SM/g, '').replace(/FROALA-EM/g, '')
                var s_line = html.substring(0, s_index).length - html.substring(0, s_index).replace(/\n/g, '').length;
                var e_line = html.substring(0, e_index).length - html.substring(0, e_index).replace(/\n/g, '').length;
                s_index = html.substring(0, s_index).length - html.substring(0, html.substring(0, s_index).lastIndexOf('\n') + 1).length;
                e_index = html.substring(0, e_index).length - html.substring(0, html.substring(0, e_index).lastIndexOf('\n') + 1).length;
                ace_editor.$blockScrolling = Infinity
                ace_editor.getSession().setValue(html);
                _can_focus = !was_focused;
                ace_editor.focus();
                _can_focus = true;
                ace_editor.selection.moveCursorToPosition({row: s_line, column: s_index});
                ace_editor.selection.selectToPosition({row: e_line, column: e_index});
                ace_editor.resize();
                ace_editor.session.getUndoManager().reset();
            } else {
                s_index = html.indexOf('FROALA-SM');
                e_index = html.indexOf('FROALA-EM') - 9;
                if (editor.opts.heightMin) {
                    $html_area.css('min-height', editor.opts.heightMin);
                }
                if (editor.opts.height) {
                    $html_area.css('height', editor.opts.height);
                }
                if (editor.opts.heightMax) {
                    $html_area.css('max-height', editor.opts.height || editor.opts.heightMax);
                }
                $html_area.val(html.replace(/FROALA-SM/g, '').replace(/FROALA-EM/g, '')).trigger('change');
                var scroll_top = $(editor.o_doc).scrollTop();
                _can_focus = !was_focused;
                $html_area.focus();
                _can_focus = true;
                $html_area.get(0).setSelectionRange(s_index, e_index);
                $(editor.o_doc).scrollTop(scroll_top);
            }
            editor.$tb.find(' > .fr-command, > .fr-btn-wrap > .fr-command').not($btn).filter(function () {
                return editor.opts.codeViewKeepActiveButtons.indexOf($(this).data('cmd')) < 0;
            }).addClass('fr-disabled').attr('aria-disabled', true);
            $btn.addClass('fr-active').attr('aria-pressed', true);
            if (!editor.helpers.isMobile() && editor.opts.toolbarInline) {
                editor.toolbar.hide();
            }
        }

        function toggle(val) {
            if (typeof val == 'undefined') val = !isActive();
            var $btn = editor.$tb.find('.fr-command[data-cmd="html"]');
            if (!val) {
                editor.$box.toggleClass('fr-code-view', false);
                _showText($btn);
            } else {
                editor.popups.hideAll();
                _showHTML($btn);
            }
        }

        function _destroy() {
            if (isActive()) {
                toggle(false);
            }
            $html_area.val('').removeData().remove();
            $html_area = null;
            if ($back_button) {
                $back_button.remove();
                $back_button = null;
            }
        }

        function _refreshToolbar() {
            var $btn = editor.$tb.find('.fr-command[data-cmd="html"]');
            if (!isActive()) {
                editor.$tb.find(' > .fr-command').not($btn).removeClass('fr-disabled').attr('aria-disabled', false);
                $btn.removeClass('fr-active').attr('aria-pressed', false);
            } else {
                editor.$tb.find(' > .fr-command').not($btn).filter(function () {
                    return editor.opts.codeViewKeepActiveButtons.indexOf($(this).data('cmd')) < 0;
                }).addClass('fr-disabled').attr('aria-disabled', false);
                $btn.addClass('fr-active').attr('aria-pressed', false);
            }
        }

        function _initArea() {
            $html_area = $('<textarea class="fr-code" tabIndex="-1">');
            editor.$wp.append($html_area);
            $html_area.attr('dir', editor.opts.direction);
            if (!editor.$box.hasClass('fr-basic')) {
                $back_button = $('<a data-cmd="html" title="Code View" class="fr-command fr-btn html-switch' + (editor.helpers.isMobile() ? '' : ' fr-desktop') + '" role="button" tabIndex="-1"><i class="fa fa-code"></i></button>');
                editor.$box.append($back_button);
                editor.events.bindClick(editor.$box, 'a.html-switch', function () {
                    editor.events.trigger('commands.before', ['html'])
                    toggle(false);
                    editor.events.trigger('commands.after', ['html'])
                });
            }
            var cancel = function () {
                return !isActive();
            }
            editor.events.on('buttons.refresh', cancel);
            editor.events.on('copy', cancel, true);
            editor.events.on('cut', cancel, true);
            editor.events.on('paste', cancel, true);
            editor.events.on('destroy', _destroy, true);
            editor.events.on('html.set', function () {
                if (isActive()) toggle(true);
            });
            editor.events.on('codeView.update', refresh);
            editor.events.on('form.submit', function () {
                if (isActive()) {
                    editor.html.set(get());
                    editor.events.trigger('contentChanged', [], true);
                }
            }, true);
        }

        var $back_button;

        function _init() {
            editor.events.on('focus', function () {
                if (editor.opts.toolbarContainer) {
                    _refreshToolbar()
                }
            });
            if (!editor.$wp) return false;
        }

        return {_init: _init, toggle: toggle, isActive: isActive, get: get}
    };
    $.FE.RegisterCommand('html', {
        title: 'Code View',
        undo: false,
        focus: false,
        forcedRefresh: true,
        toggle: true,
        callback: function () {
            this.codeView.toggle();
        },
        plugin: 'codeView'
    })
    $.FE.DefineIcon('html', {NAME: 'code'});
}));
!function (t) {
    "function" == typeof define && define.amd ? define(["jquery"], t) : "object" == typeof module && module.exports ? module.exports = function (a, e) {
        return e === undefined && (e = "undefined" != typeof window ? require("jquery") : require("jquery")(a)), t(e)
    } : t(window.jQuery)
}(function (g) {
    g.extend(g.FE.DEFAULTS, {
        paragraphFormat: {
            N: "Normal",
            H1: "Heading 1",
            H2: "Heading 2",
            H3: "Heading 3",
            H4: "Heading 4",
            PRE: "Code"
        }, paragraphFormatSelection: !1, paragraphDefaultSelection: "Paragraph Format"
    }), g.FE.PLUGINS.paragraphFormat = function (h) {
        function f(a, e) {
            var t = h.html.defaultTag();
            if (e && e.toLowerCase() != t) if (0 < a.find("ul, ol").length) {
                var r = g("<" + e + ">");
                a.prepend(r);
                for (var n = h.node.contents(a.get(0))[0]; n && ["UL", "OL"].indexOf(n.tagName) < 0;) {
                    var o = n.nextSibling;
                    r.append(n), n = o
                }
            } else a.html("<" + e + ">" + a.html() + "</" + e + ">")
        }

        return {
            apply: function (a) {
                "N" == a && (a = h.html.defaultTag()), h.selection.save(), h.html.wrap(!0, !0, !h.opts.paragraphFormat.BLOCKQUOTE, !0, !0), h.selection.restore();
                var e, t, r, n, o, i, p, l, s = h.selection.blocks();
                h.selection.save(), h.$el.find("pre").attr("skip", !0);
                for (var d = 0; d < s.length; d++) if (s[d].tagName != a && !h.node.isList(s[d])) {
                    var m = g(s[d]);
                    "LI" == s[d].tagName ? f(m, a) : "LI" == s[d].parentNode.tagName && s[d] ? (i = m, p = a, l = h.html.defaultTag(), p && p.toLowerCase() != l || (p = 'div class="fr-temp-div"'), i.replaceWith(g("<" + p + ">").html(i.html()))) : 0 <= ["TD", "TH"].indexOf(s[d].parentNode.tagName) ? (r = m, n = a, o = h.html.defaultTag(), n || (n = 'div class="fr-temp-div"' + (h.node.isEmpty(r.get(0), !0) ? ' data-empty="true"' : "")), n.toLowerCase() == o ? (h.node.isEmpty(r.get(0), !0) || r.append("<br/>"), r.replaceWith(r.html())) : r.replaceWith(g("<" + n + ">").html(r.html()))) : (e = m, (t = a) || (t = 'div class="fr-temp-div"' + (h.node.isEmpty(e.get(0), !0) ? ' data-empty="true"' : "")), e.replaceWith(g("<" + t + " " + h.node.attributes(e.get(0)) + ">").html(e.html()).removeAttr("data-empty")))
                }
                h.$el.find('pre:not([skip="true"]) + pre:not([skip="true"])').each(function () {
                    g(this).prev().append("<br>" + g(this).html()), g(this).remove()
                }), h.$el.find("pre").removeAttr("skip"), h.html.unwrap(), h.selection.restore()
            }, refreshOnShow: function (a, e) {
                var t = h.selection.blocks();
                if (t.length) {
                    var r = t[0], n = "N", o = h.html.defaultTag();
                    r.tagName.toLowerCase() != o && r != h.el && (n = r.tagName), e.find('.fr-command[data-param1="' + n + '"]').addClass("fr-active").attr("aria-selected", !0)
                } else e.find('.fr-command[data-param1="N"]').addClass("fr-active").attr("aria-selected", !0)
            }, refresh: function (a) {
                if (h.opts.paragraphFormatSelection) {
                    var e = h.selection.blocks();
                    if (e.length) {
                        var t = e[0], r = "N", n = h.html.defaultTag();
                        t.tagName.toLowerCase() != n && t != h.el && (r = t.tagName), 0 <= ["LI", "TD", "TH"].indexOf(r) && (r = "N"), a.find("> span").text(h.language.translate(h.opts.paragraphFormat[r]))
                    } else a.find("> span").text(h.language.translate(h.opts.paragraphFormat.N))
                }
            }
        }
    }, g.FE.RegisterCommand("paragraphFormat", {
        type: "dropdown", displaySelection: function (a) {
            return a.opts.paragraphFormatSelection
        }, defaultSelection: function (a) {
            return a.language.translate(a.opts.paragraphDefaultSelection)
        }, displaySelectionWidth: 125, html: function () {
            var a = '<ul class="fr-dropdown-list" role="presentation">', e = this.opts.paragraphFormat;
            for (var t in e) if (e.hasOwnProperty(t)) {
                var r = this.shortcuts.get("paragraphFormat." + t);
                r = r ? '<span class="fr-shortcut">' + r + "</span>" : "", a += '<li role="presentation"><' + ("N" == t ? this.html.defaultTag() || "DIV" : t) + ' style="padding: 0 !important; margin: 0 !important;" role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="paragraphFormat" data-param1="' + t + '" title="' + this.language.translate(e[t]) + '">' + this.language.translate(e[t]) + "</a></" + ("N" == t ? this.html.defaultTag() || "DIV" : t) + "></li>"
            }
            return a += "</ul>"
        }, title: "Paragraph Format", callback: function (a, e) {
            this.paragraphFormat.apply(e)
        }, refresh: function (a) {
            this.paragraphFormat.refresh(a)
        }, refreshOnShow: function (a, e) {
            this.paragraphFormat.refreshOnShow(a, e)
        }, plugin: "paragraphFormat"
    }), g.FE.DefineIcon("paragraphFormat", {NAME: "paragraph"})
});
!function (t) {
    "function" == typeof define && define.amd ? define(["jquery"], t) : "object" == typeof module && module.exports ? module.exports = function (e, n) {
        return n === undefined && (n = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), t(n)
    } : t(window.jQuery)
}(function (r) {
    r.FE.PLUGINS.align = function (a) {
        return {
            apply: function (e) {
                var n = a.selection.element();
                if (r(n).parents(".fr-img-caption").length) r(n).css("text-align", e); else {
                    a.selection.save(), a.html.wrap(!0, !0, !0, !0), a.selection.restore();
                    for (var t = a.selection.blocks(), i = 0; i < t.length; i++) r(t[i]).css("text-align", e).removeClass("fr-temp-div"), "" === r(t[i]).attr("class") && r(t[i]).removeAttr("class"), "" === r(t[i]).attr("style") && r(t[i]).removeAttr("style");
                    a.selection.save(), a.html.unwrap(), a.selection.restore()
                }
            }, refresh: function (e) {
                var n = a.selection.blocks();
                if (n.length) {
                    var t = a.helpers.getAlignment(r(n[0]));
                    e.find("> *:first").replaceWith(a.icon.create("align-" + t))
                }
            }, refreshOnShow: function (e, n) {
                var t = a.selection.blocks();
                if (t.length) {
                    var i = a.helpers.getAlignment(r(t[0]));
                    n.find('a.fr-command[data-param1="' + i + '"]').addClass("fr-active").attr("aria-selected", !0)
                }
            }, refreshForToolbar: function (e) {
                var n = a.selection.blocks();
                if (n.length) {
                    var t = a.helpers.getAlignment(r(n[0]));
                    "align" + (t = t.charAt(0).toUpperCase() + t.slice(1)) == e.attr("data-cmd") && e.addClass("fr-active")
                }
            }
        }
    }, r.FE.DefineIcon("align", {NAME: "align-left"}), r.FE.DefineIcon("align-left", {NAME: "align-left"}), r.FE.DefineIcon("align-right", {NAME: "align-right"}), r.FE.DefineIcon("align-center", {NAME: "align-center"}), r.FE.DefineIcon("align-justify", {NAME: "align-justify"}), r.FE.RegisterCommand("align", {
        type: "dropdown",
        title: "Align",
        options: {left: "Align Left", center: "Align Center", right: "Align Right", justify: "Align Justify"},
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', n = r.FE.COMMANDS.align.options;
            for (var t in n) n.hasOwnProperty(t) && (e += '<li role="presentation"><a class="fr-command fr-title" tabIndex="-1" role="option" data-cmd="align" data-param1="' + t + '" title="' + this.language.translate(n[t]) + '">' + this.icon.create("align-" + t) + '<span class="fr-sr-only">' + this.language.translate(n[t]) + "</span></a></li>");
            return e += "</ul>"
        },
        callback: function (e, n) {
            this.align.apply(n)
        },
        refresh: function (e) {
            this.align.refresh(e)
        },
        refreshOnShow: function (e, n) {
            this.align.refreshOnShow(e, n)
        },
        plugin: "align"
    }), r.FE.RegisterCommand("alignLeft", {
        type: "button", icon: "align-left", callback: function () {
            this.align.apply("left")
        }, refresh: function (e) {
            this.align.refreshForToolbar(e)
        }
    }), r.FE.RegisterCommand("alignRight", {
        type: "button", icon: "align-right", callback: function () {
            this.align.apply("right")
        }, refresh: function (e) {
            this.align.refreshForToolbar(e)
        }
    }), r.FE.RegisterCommand("alignCenter", {
        type: "button", icon: "align-center", callback: function () {
            this.align.apply("center")
        }, refresh: function (e) {
            this.align.refreshForToolbar(e)
        }
    }), r.FE.RegisterCommand("alignJustify", {
        type: "button", icon: "align-justify", callback: function () {
            this.align.apply("justify")
        }, refresh: function (e) {
            this.align.refreshForToolbar(e)
        }
    })
});
!function (n) {
    "function" == typeof define && define.amd ? define(["jquery"], n) : "object" == typeof module && module.exports ? module.exports = function (e, t) {
        return t === undefined && (t = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), n(t)
    } : n(window.jQuery)
}(function (u) {
    u.extend(u.FE.DEFAULTS, {listAdvancedTypes: !0}), u.FE.PLUGINS.lists = function (d) {
        function c(e) {
            return '<span class="fr-open-' + e.toLowerCase() + '"></span>'
        }

        function g(e) {
            return '<span class="fr-close-' + e.toLowerCase() + '"></span>'
        }

        function r(e, t) {
            !function (e, t) {
                for (var n = [], a = 0; a < e.length; a++) {
                    var r = e[a].parentNode;
                    "LI" == e[a].tagName && r.tagName != t && n.indexOf(r) < 0 && n.push(r)
                }
                for (a = n.length - 1; 0 <= a; a--) {
                    var o = u(n[a]);
                    o.replaceWith("<" + t.toLowerCase() + " " + d.node.attributes(o.get(0)) + ">" + o.html() + "</" + t.toLowerCase() + ">")
                }
            }(e, t);
            var n, a = d.html.defaultTag(), r = null;
            e.length && (n = "rtl" == d.opts.direction || "rtl" == u(e[0]).css("direction") ? "margin-right" : "margin-left");
            for (var o = 0; o < e.length; o++) if ("TD" != e[o].tagName && "TH" != e[o].tagName && "LI" != e[o].tagName) {
                var l = d.helpers.getPX(u(e[o]).css(n)) || 0;
                (e[o].style.marginLeft = null) === r && (r = l);
                var s = 0 < r ? "<" + t + ' style="' + n + ": " + r + 'px;">' : "<" + t + ">", i = "</" + t + ">";
                for (l -= r; 0 < l / d.opts.indentMargin;) s += "<" + t + ">", i += i, l -= d.opts.indentMargin;
                a && e[o].tagName.toLowerCase() == a ? u(e[o]).replaceWith(s + "<li" + d.node.attributes(e[o]) + ">" + u(e[o]).html() + "</li>" + i) : u(e[o]).wrap(s + "<li></li>" + i)
            }
            d.clean.lists()
        }

        function o(e) {
            var t, n;
            for (t = e.length - 1; 0 <= t; t--) for (n = t - 1; 0 <= n; n--) if (u(e[n]).find(e[t]).length || e[n] == e[t]) {
                e.splice(t, 1);
                break
            }
            var a = [];
            for (t = 0; t < e.length; t++) {
                var r = u(e[t]), o = e[t].parentNode, l = r.attr("class");
                if (r.before(g(o.tagName)), "LI" == o.parentNode.tagName) r.before(g("LI")), r.after(c("LI")); else {
                    var s = "";
                    l && (s += ' class="' + l + '"');
                    var i = "rtl" == d.opts.direction || "rtl" == r.css("direction") ? "margin-right" : "margin-left";
                    d.helpers.getPX(u(o).css(i)) && 0 <= (u(o).attr("style") || "").indexOf(i + ":") && (s += ' style="' + i + ":" + d.helpers.getPX(u(o).css(i)) + 'px;"'), d.html.defaultTag() && 0 === r.find(d.html.blockTagsQuery()).length && r.wrapInner("<" + d.html.defaultTag() + s + "></" + d.html.defaultTag() + ">"), d.node.isEmpty(r.get(0), !0) || 0 !== r.find(d.html.blockTagsQuery()).length || r.append("<br>"), r.append(c("LI")), r.prepend(g("LI"))
                }
                r.after(c(o.tagName)), "LI" == o.parentNode.tagName && (o = o.parentNode.parentNode), a.indexOf(o) < 0 && a.push(o)
            }
            for (t = 0; t < a.length; t++) {
                var p = u(a[t]), f = p.html();
                f = (f = f.replace(/<span class="fr-close-([a-z]*)"><\/span>/g, "</$1>")).replace(/<span class="fr-open-([a-z]*)"><\/span>/g, "<$1>"), p.replaceWith(d.node.openTagString(p.get(0)) + f + d.node.closeTagString(p.get(0)))
            }
            d.$el.find("li:empty").remove(), d.$el.find("ul:empty, ol:empty").remove(), d.clean.lists(), d.html.wrap()
        }

        function l(e) {
            d.selection.save();
            for (var t = 0; t < e.length; t++) {
                var n = e[t].previousSibling;
                if (n) {
                    var a = u(e[t]).find("> ul, > ol").last().get(0);
                    if (a) {
                        for (var r = u("<li>").prependTo(u(a)), o = d.node.contents(e[t])[0]; o && !d.node.isList(o);) {
                            var l = o.nextSibling;
                            r.append(o), o = l
                        }
                        u(n).append(u(a)), u(e[t]).remove()
                    } else {
                        var s = u(n).find("> ul, > ol").last().get(0);
                        if (s) u(s).append(u(e[t])); else {
                            var i = u("<" + e[t].parentNode.tagName + ">");
                            u(n).append(i), i.append(u(e[t]))
                        }
                    }
                }
            }
            d.clean.lists(), d.selection.restore()
        }

        function s(e) {
            d.selection.save(), o(e), d.selection.restore()
        }

        function e(e) {
            if ("indent" == e || "outdent" == e) {
                for (var t = !1, n = d.selection.blocks(), a = [], r = 0; r < n.length; r++) "LI" == n[r].tagName ? (t = !0, a.push(n[r])) : "LI" == n[r].parentNode.tagName && (t = !0, a.push(n[r].parentNode));
                t && ("indent" == e ? l(a) : s(a))
            }
        }

        return {
            _init: function () {
                d.events.on("commands.after", e), d.events.on("keydown", function (e) {
                    if (e.which == u.FE.KEYCODE.TAB) {
                        for (var t = d.selection.blocks(), n = [], a = 0; a < t.length; a++) "LI" == t[a].tagName ? n.push(t[a]) : "LI" == t[a].parentNode.tagName && n.push(t[a].parentNode);
                        if (1 < n.length || n.length && (d.selection.info(n[0]).atStart || d.node.isEmpty(n[0]))) return e.preventDefault(), e.stopPropagation(), e.shiftKey ? s(n) : l(n), !1
                    }
                }, !0)
            }, format: function (e, t) {
                var n, a;
                for (d.selection.save(), d.html.wrap(!0, !0, !0, !0), d.selection.restore(), a = d.selection.blocks(), n = 0; n < a.length; n++) "LI" != a[n].tagName && "LI" == a[n].parentNode.tagName && (a[n] = a[n].parentNode);
                if (d.selection.save(), function (e, t) {
                    for (var n = !0, a = 0; a < e.length; a++) {
                        if ("LI" != e[a].tagName) return !1;
                        e[a].parentNode.tagName != t && (n = !1)
                    }
                    return n
                }(a, e) ? t || o(a) : r(a, e), d.html.unwrap(), d.selection.restore(), t = t || "default") {
                    for (a = d.selection.blocks(), n = 0; n < a.length; n++) "LI" != a[n].tagName && "LI" == a[n].parentNode.tagName && (a[n] = a[n].parentNode);
                    for (n = 0; n < a.length; n++) "LI" == a[n].tagName && (u(a[n].parentNode).css("list-style-type", "default" === t ? "" : t), 0 === (u(a[n].parentNode).attr("style") || "").length && u(a[n].parentNode).removeAttr("style"))
                }
            }, refresh: function (e, t) {
                var n = u(d.selection.element());
                if (n.get(0) != d.el) {
                    var a = n.get(0);
                    (a = "LI" != a.tagName && a.firstElementChild && "LI" != a.firstElementChild.tagName ? n.parents("li").get(0) : "LI" == a.tagName || a.firstElementChild ? a.firstElementChild && "LI" == a.firstElementChild.tagName ? n.get(0).firstChild : n.get(0) : n.parents("li").get(0)) && a.parentNode.tagName == t && d.el.contains(a.parentNode) && e.addClass("fr-active")
                }
            }
        }
    }, u.FE.RegisterCommand("formatUL", {
        title: "Unordered List", type: "button", hasOptions: function () {
            return this.opts.listAdvancedTypes
        }, options: {"default": "Default", circle: "Circle", disc: "Disc", square: "Square"}, refresh: function (e) {
            this.lists.refresh(e, "UL")
        }, callback: function (e, t) {
            this.lists.format("UL", t)
        }, plugin: "lists"
    }), u.FE.RegisterCommand("formatOL", {
        title: "Ordered List",
        hasOptions: function () {
            return this.opts.listAdvancedTypes
        },
        options: {
            "default": "Default",
            "lower-alpha": "Lower Alpha",
            "lower-greek": "Lower Greek",
            "lower-roman": "Lower Roman",
            "upper-alpha": "Upper Alpha",
            "upper-roman": "Upper Roman"
        },
        refresh: function (e) {
            this.lists.refresh(e, "OL")
        },
        callback: function (e, t) {
            this.lists.format("OL", t)
        },
        plugin: "lists"
    }), u.FE.DefineIcon("formatUL", {NAME: "list-ul"}), u.FE.DefineIcon("formatOL", {NAME: "list-ol"})
});
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = function (root, jQuery) {
            if (jQuery === undefined) {
                if (typeof window !== 'undefined') {
                    jQuery = require('jquery');
                } else {
                    jQuery = require('jquery')(root);
                }
            }
            factory(jQuery);
            return jQuery;
        };
    } else {
        factory(jQuery);
    }
}(function ($) {
    $.extend($.FE.POPUP_TEMPLATES, {'file.insert': '[_BUTTONS_][_UPLOAD_LAYER_][_BY_URL_LAYER_][_PROGRESS_BAR_]'})
    $.extend($.FE.DEFAULTS, {
        fileUploadURL: 'http://i.froala.com/upload',
        fileUploadParam: 'file',
        fileUploadParams: {},
        fileUploadToS3: false,
        fileUploadMethod: 'POST',
        fileMaxSize: 10 * 1024 * 1024,
        fileAllowedTypes: ['*'],
        fileInsertButtons: ['fileBack', '|', 'fileUpload', 'fileByURL'],
        fileUseSelectedText: false
    });
    $.FE.PLUGINS.file = function (editor) {
        var BAD_LINK = 1;
        var MISSING_LINK = 2;
        var ERROR_DURING_UPLOAD = 3;
        var BAD_RESPONSE = 4;
        var MAX_SIZE_EXCEEDED = 5;
        var BAD_FILE_TYPE = 6;
        var NO_CORS_IE = 7;
        var error_messages = {};
        error_messages[BAD_LINK] = 'File cannot be loaded from the passed link.';
        error_messages[MISSING_LINK] = 'No link in upload response.';
        error_messages[ERROR_DURING_UPLOAD] = 'Error during file upload.';
        error_messages[BAD_RESPONSE] = 'Parsing response failed.';
        error_messages[MAX_SIZE_EXCEEDED] = 'File is too large.';
        error_messages[BAD_FILE_TYPE] = 'File file type is invalid.';
        error_messages[NO_CORS_IE] = 'Files can be uploaded only to same domain in IE 8 and IE 9.';

        function showInsertPopup() {
            var $btn = editor.$tb.find('.fr-command[data-cmd="insertFile"]');
            var $popup = editor.popups.get('file.insert');
            if (!$popup) $popup = _initInsertPopup();
            hideProgressBar();
            if (!$popup.hasClass('fr-active')) {
                editor.popups.refresh('file.insert');
                editor.popups.setContainer('file.insert', editor.$tb);
                var left = $btn.offset().left + $btn.outerWidth() / 2;
                var top = $btn.offset().top + (editor.opts.toolbarBottom ? 10 : $btn.outerHeight() - 10);
                editor.popups.show('file.insert', left, top, $btn.outerHeight());
            }
        }

        function showProgressBar() {
            var $popup = editor.popups.get('file.insert');
            if (!$popup) $popup = _initInsertPopup();
            $popup.find('.fr-layer.fr-active').removeClass('fr-active').addClass('fr-pactive');
            $popup.find('.fr-file-progress-bar-layer').addClass('fr-active');
            $popup.find('.fr-buttons').hide();
            _setProgressMessage('Uploading', 0);
        }

        function hideProgressBar(dismiss) {
            var $popup = editor.popups.get('file.insert');
            if ($popup) {
                $popup.find('.fr-layer.fr-pactive').addClass('fr-active').removeClass('fr-pactive');
                $popup.find('.fr-file-progress-bar-layer').removeClass('fr-active');
                $popup.find('.fr-buttons').show();
                if (dismiss) {
                    editor.events.focus();
                    editor.popups.hide('file.insert');
                }
            }
        }

        function _setProgressMessage(message, progress) {
            var $popup = editor.popups.get('file.insert');
            if ($popup) {
                var $layer = $popup.find('.fr-file-progress-bar-layer');
                $layer.find('h3').text(message + (progress ? ' ' + progress + '%' : ''));
                $layer.removeClass('fr-error');
                if (progress) {
                    $layer.find('div').removeClass('fr-indeterminate');
                    $layer.find('div > span').css('width', progress + '%');
                } else {
                    $layer.find('div').addClass('fr-indeterminate');
                }
            }
        }

        function _showErrorMessage(message) {
            showProgressBar();
            var $popup = editor.popups.get('file.insert');
            var $layer = $popup.find('.fr-file-progress-bar-layer');
            $layer.addClass('fr-error');
            var $message_header = $layer.find('h3');
            $message_header.text(message);
            editor.events.disableBlur();
            $message_header.focus();
        }

        function insertByURL() {
            var $popup = editor.popups.get('file.insert');
            var $input = $popup.find('.fr-file-by-url-layer input');
            var url = $input.val()
            if (url.length > 0) {
                var filename = url.substring(url.lastIndexOf('/') + 1);
                insert(editor.helpers.sanitizeURL($input.val()), filename, []);
                $input.val('');
                $input.blur();
            }
        }

        function insert(link, text, response) {
            editor.edit.on();
            editor.events.focus(true);
            editor.selection.restore();
            if (editor.opts.fileUseSelectedText && editor.selection.text().length) {
                text = editor.selection.text();
            }
            editor.html.insert('<a href="' + link + '" id="fr-inserted-file" class="fr-file">' + text + '</a>');
            var $file = editor.$el.find('#fr-inserted-file');
            $file.removeAttr('id');
            editor.popups.hide('file.insert');
            editor.undo.saveStep();
            _syncFiles();
            editor.events.trigger('file.inserted', [$file, response]);
        }

        function _parseResponse(response) {
            try {
                if (editor.events.trigger('file.uploaded', [response], true) === false) {
                    editor.edit.on();
                    return false;
                }
                var resp = $.parseJSON(response);
                if (resp.link) {
                    return resp;
                } else {
                    _throwError(MISSING_LINK, response);
                    return false;
                }
            } catch (ex) {
                _throwError(BAD_RESPONSE, response);
                return false;
            }
        }

        function _parseXMLResponse(response) {
            try {
                var link = $(response).find('Location').text();
                var key = $(response).find('Key').text();
                if (editor.events.trigger('file.uploadedToS3', [link, key, response], true) === false) {
                    editor.edit.on();
                    return false;
                }
                return link;
            } catch (ex) {
                _throwError(BAD_RESPONSE, response);
                return false;
            }
        }

        function _fileUploaded(text) {
            var status = this.status;
            var response = this.response;
            var responseXML = this.responseXML;
            var responseText = this.responseText;
            try {
                if (editor.opts.fileUploadToS3) {
                    if (status == 201) {
                        var link = _parseXMLResponse(responseXML);
                        if (link) {
                            insert(link, text, response || responseXML);
                        }
                    } else {
                        _throwError(BAD_RESPONSE, response || responseXML);
                    }
                } else {
                    if (status >= 200 && status < 300) {
                        var resp = _parseResponse(responseText);
                        if (resp) {
                            insert(resp.link, text, response || responseText);
                        }
                    } else {
                        _throwError(ERROR_DURING_UPLOAD, response || responseText);
                    }
                }
            } catch (ex) {
                _throwError(BAD_RESPONSE, response || responseText);
            }
        }

        function _fileUploadError() {
            _throwError(BAD_RESPONSE, this.response || this.responseText || this.responseXML);
        }

        function _fileUploadProgress(e) {
            if (e.lengthComputable) {
                var complete = (e.loaded / e.total * 100 | 0);
                _setProgressMessage('Uploading', complete);
            }
        }

        function _throwError(code, response) {
            editor.edit.on();
            _showErrorMessage(editor.language.translate('Something went wrong. Please try again.'));
            editor.events.trigger('file.error', [{code: code, message: error_messages[code]}, response]);
        }

        function _fileUploadAborted() {
            editor.edit.on();
            hideProgressBar(true);
        }

        function upload(files) {
            if (typeof files != 'undefined' && files.length > 0) {
                if (editor.events.trigger('file.beforeUpload', [files]) === false) {
                    return false;
                }
                var file = files[0];
                if (file.size > editor.opts.fileMaxSize) {
                    _throwError(MAX_SIZE_EXCEEDED);
                    return false;
                }
                if (editor.opts.fileAllowedTypes.indexOf('*') < 0 && editor.opts.fileAllowedTypes.indexOf(file.type.replace(/file\//g, '')) < 0) {
                    _throwError(BAD_FILE_TYPE);
                    return false;
                }
                var form_data;
                if (editor.drag_support.formdata) {
                    form_data = editor.drag_support.formdata ? new FormData() : null;
                }
                if (form_data) {
                    var key;
                    if (editor.opts.fileUploadToS3 !== false) {
                        form_data.append('key', editor.opts.fileUploadToS3.keyStart + (new Date()).getTime() + '-' + (file.name || 'untitled'));
                        form_data.append('success_action_status', '201');
                        form_data.append('X-Requested-With', 'xhr');
                        form_data.append('Content-Type', file.type);
                        for (key in editor.opts.fileUploadToS3.params) {
                            if (editor.opts.fileUploadToS3.params.hasOwnProperty(key)) {
                                form_data.append(key, editor.opts.fileUploadToS3.params[key]);
                            }
                        }
                    }
                    for (key in editor.opts.fileUploadParams) {
                        if (editor.opts.fileUploadParams.hasOwnProperty(key)) {
                            form_data.append(key, editor.opts.fileUploadParams[key]);
                        }
                    }
                    form_data.append(editor.opts.fileUploadParam, file);
                    var url = editor.opts.fileUploadURL;
                    if (editor.opts.fileUploadToS3) {
                        if (editor.opts.fileUploadToS3.uploadURL) {
                            url = editor.opts.fileUploadToS3.uploadURL;
                        } else {
                            url = 'https://' + editor.opts.fileUploadToS3.region + '.amazonaws.com/' + editor.opts.fileUploadToS3.bucket;
                        }
                    }
                    var xhr = editor.core.getXHR(url, editor.opts.fileUploadMethod);
                    xhr.onload = function () {
                        _fileUploaded.call(xhr, file.name);
                    };
                    xhr.onerror = _fileUploadError;
                    xhr.upload.onprogress = _fileUploadProgress;
                    xhr.onabort = _fileUploadAborted;
                    showProgressBar();
                    editor.edit.off();
                    var $popup = editor.popups.get('file.insert');
                    if ($popup) {
                        $popup.off('abortUpload').on('abortUpload', function () {
                            if (xhr.readyState != 4) {
                                xhr.abort();
                            }
                        })
                    }
                    xhr.send(form_data);
                }
            }
        }

        function _bindInsertEvents($popup) {
            editor.events.$on($popup, 'dragover dragenter', '.fr-file-upload-layer', function () {
                $(this).addClass('fr-drop');
                return false;
            }, true);
            editor.events.$on($popup, 'dragleave dragend', '.fr-file-upload-layer', function () {
                $(this).removeClass('fr-drop');
                return false;
            }, true);
            editor.events.$on($popup, 'drop', '.fr-file-upload-layer', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('fr-drop');
                var dt = e.originalEvent.dataTransfer;
                if (dt && dt.files) {
                    var inst = $popup.data('instance') || editor;
                    inst.file.upload(dt.files);
                }
            }, true);
            editor.events.$on($popup, 'change', '.fr-file-upload-layer input[type="file"]', function () {
                if (this.files) {
                    var inst = $popup.data('instance') || editor;
                    inst.file.upload(this.files);
                }
                $(this).val('');
            }, true);
        }

        function _hideInsertPopup() {
            hideProgressBar();
        }

        function _initInsertPopup(delayed) {
            if (delayed) {
                editor.popups.onHide('file.insert', _hideInsertPopup);
                return true;
            }
            var active;
            var file_buttons = '';
            if (editor.opts.fileInsertButtons.length > 1) {
                file_buttons = '<div class="fr-buttons">' + editor.button.buildList(editor.opts.fileInsertButtons) + '</div>';
            }
            var uploadIndex = editor.opts.fileInsertButtons.indexOf('fileUpload');
            var urlIndex = editor.opts.fileInsertButtons.indexOf('fileByURL');
            var upload_layer = '';
            if (uploadIndex >= 0) {
                active = ' fr-active';
                if (urlIndex >= 0 && uploadIndex > urlIndex) {
                    active = '';
                }
                upload_layer = '<div class="fr-file-upload-layer' + active + ' fr-layer fr-active" id="fr-file-upload-layer-' + editor.id + '"><strong>' + editor.language.translate('Drop file') + '</strong><br>(' + editor.language.translate('or click') + ')<div class="fr-form"><input type="file" name="' + editor.opts.fileUploadParam + '" accept="/*" tabIndex="-1" aria-labelledby="fr-file-upload-layer-' + editor.id + '" role="button"></div></div>'
            }
            var by_url_layer = '';
            if (urlIndex >= 0) {
                active = ' fr-active';
                if (uploadIndex >= 0 && urlIndex > uploadIndex) {
                    active = '';
                }
                by_url_layer = '<div class="fr-file-by-url-layer' + active + ' fr-layer" id="fr-file-by-url-layer-' + editor.id + '"><div class="fr-input-line"><input type="text" placeholder="http://" tabIndex="1"></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="fileInsertByURL" tabIndex="2">' + editor.language.translate('Insert') + '</button></div></div>'
            }
            var progress_bar_layer = '<div class="fr-file-progress-bar-layer fr-layer"><h3 tabIndex="-1" class="fr-message">Uploading</h3><div class="fr-loader"><span class="fr-progress"></span></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-dismiss" data-cmd="fileDismissError" tabIndex="2" role="button">OK</button></div></div>';
            var template = {
                buttons: file_buttons,
                upload_layer: upload_layer,
                by_url_layer: by_url_layer,
                progress_bar: progress_bar_layer
            };
            var $popup = editor.popups.create('file.insert', template);
            _bindInsertEvents($popup);
            return $popup;
        }

        function _onRemove(link) {
            if (editor.node.hasClass(link, 'fr-file')) {
                return editor.events.trigger('file.unlink', [link]);
            }
        }

        function _drop(e) {
            var dt = e.originalEvent.dataTransfer;
            if (dt && dt.files && dt.files.length) {
                var file = dt.files[0];
                if (file && typeof file.type != 'undefined') {
                    if (file.type.indexOf('image') < 0 && (editor.opts.fileAllowedTypes.indexOf(file.type) >= 0 || editor.opts.fileAllowedTypes.indexOf('*') >= 0)) {
                        editor.markers.remove();
                        editor.markers.insertAtPoint(e.originalEvent);
                        editor.$el.find('.fr-marker').replaceWith($.FE.MARKERS);
                        editor.popups.hideAll();
                        var $popup = editor.popups.get('file.insert');
                        if (!$popup) $popup = _initInsertPopup();
                        editor.popups.setContainer('file.insert', editor.$sc);
                        editor.popups.show('file.insert', e.originalEvent.pageX, e.originalEvent.pageY);
                        showProgressBar();
                        upload(dt.files);
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                }
            }
        }

        function _initEvents() {
            editor.events.on('drop', _drop);
            editor.events.$on(editor.$win, 'keydown', function (e) {
                var key_code = e.which;
                var $popup = editor.popups.get('file.insert');
                if ($popup && key_code == $.FE.KEYCODE.ESC) {
                    $popup.trigger('abortUpload');
                }
            });
            editor.events.on('destroy', function () {
                var $popup = editor.popups.get('file.insert');
                if ($popup) {
                    $popup.trigger('abortUpload');
                }
            });
        }

        function back() {
            editor.events.disableBlur();
            editor.selection.restore();
            editor.events.enableBlur();
            editor.popups.hide('file.insert');
            editor.toolbar.showInline();
        }

        var files;

        function _syncFiles() {
            var c_files = Array.prototype.slice.call(editor.el.querySelectorAll('a.fr-file'));
            var file_srcs = [];
            var i;
            for (i = 0; i < c_files.length; i++) {
                file_srcs.push(c_files[i].getAttribute('href'));
            }
            if (files) {
                for (i = 0; i < files.length; i++) {
                    if (file_srcs.indexOf(files[i].getAttribute('href')) < 0) {
                        editor.events.trigger('file.unlink', [files[i]]);
                    }
                }
            }
            files = c_files;
        }

        function showLayer(name) {
            var $popup = editor.popups.get('file.insert');
            var left;
            var top;
            if (!editor.opts.toolbarInline) {
                var $btn = editor.$tb.find('.fr-command[data-cmd="insertFile"]');
                left = $btn.offset().left + $btn.outerWidth() / 2;
                top = $btn.offset().top + (editor.opts.toolbarBottom ? 10 : $btn.outerHeight() - 10);
            } else {
                top = $popup.offset().top - editor.helpers.getPX($popup.css('margin-top'));
                if ($popup.hasClass('fr-above')) {
                    top += $popup.outerHeight();
                }
            }
            $popup.find('.fr-layer').removeClass('fr-active');
            $popup.find('.fr-' + name + '-layer').addClass('fr-active');
            editor.popups.show('file.insert', left, top, 0);
        }

        function refreshUploadButton($btn) {
            var $popup = editor.popups.get('file.insert');
            if ($popup.find('.fr-file-upload-layer').hasClass('fr-active')) {
                $btn.addClass('fr-active');
            }
        }

        function refreshByURLButton($btn) {
            var $popup = editor.popups.get('file.insert');
            if ($popup.find('.fr-file-by-url-layer').hasClass('fr-active')) {
                $btn.addClass('fr-active');
            }
        }

        function _init() {
            _initEvents();
            editor.events.on('link.beforeRemove', _onRemove);
            if (editor.$wp) {
                _syncFiles();
                editor.events.on('contentChanged', _syncFiles);
            }
            _initInsertPopup(true);
        }

        return {
            _init: _init,
            showInsertPopup: showInsertPopup,
            showLayer: showLayer,
            refreshUploadButton: refreshUploadButton,
            refreshByURLButton: refreshByURLButton,
            insertByURL: insertByURL,
            upload: upload,
            insert: insert,
            back: back,
            hideProgressBar: hideProgressBar
        }
    }
    $.FE.DefineIcon('insertFile', {NAME: 'file-o'});
    $.FE.RegisterCommand('insertFile', {
        title: 'Insert File',
        undo: false,
        focus: true,
        refreshAfterCallback: false,
        popup: true,
        callback: function () {
            if (!this.popups.isVisible('file.insert')) {
                this.file.showInsertPopup();
            } else {
                if (this.$el.find('.fr-marker').length) {
                    this.events.disableBlur();
                    this.selection.restore();
                }
                this.popups.hide('file.insert');
            }
        },
        plugin: 'file'
    });
    $.FE.DefineIcon('fileUpload', {NAME: 'upload'});
    $.FE.RegisterCommand('fileUpload', {
        title: 'Upload File', undo: false, focus: false, callback: function () {
            this.file.showLayer('file-upload');
        }, refresh: function ($btn) {
            this.file.refreshUploadButton($btn);
        }
    });
    $.FE.DefineIcon('fileByURL', {NAME: 'link'});
    $.FE.RegisterCommand('fileByURL', {
        title: 'By URL', undo: false, focus: false, callback: function () {
            this.file.showLayer('file-by-url');
        }, refresh: function ($btn) {
            this.file.refreshByURLButton($btn);
        }
    })
    $.FE.RegisterCommand('fileInsertByURL', {
        title: 'Insert File',
        undo: true,
        refreshAfterCallback: false,
        callback: function () {
            this.file.insertByURL();
        },
        refresh: function ($btn) {
            $btn.text(this.language.translate('Insert'));
        }
    })
    $.FE.DefineIcon('fileBack', {NAME: 'arrow-left'});
    $.FE.RegisterCommand('fileBack', {
        title: 'Back', undo: false, focus: false, back: true, refreshAfterCallback: false, callback: function () {
            this.file.back();
        }, refresh: function ($btn) {
            if (!this.opts.toolbarInline) {
                $btn.addClass('fr-hidden');
                $btn.next('.fr-separator').addClass('fr-hidden');
            } else {
                $btn.removeClass('fr-hidden');
                $btn.next('.fr-separator').removeClass('fr-hidden');
            }
        }
    });
    $.FE.RegisterCommand('fileDismissError', {
        title: 'OK', callback: function () {
            this.file.hideProgressBar(true);
        }
    })
}));
!function (a) {
    "function" == typeof define && define.amd ? define(["jquery"], a) : "object" == typeof module && module.exports ? module.exports = function (e, t) {
        return t === undefined && (t = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), a(t)
    } : a(window.jQuery)
}(function (be) {
    be.extend(be.FE.POPUP_TEMPLATES, {
        "image.insert": "[_BUTTONS_][_UPLOAD_LAYER_][_BY_URL_LAYER_][_PROGRESS_BAR_]",
        "image.edit": "[_BUTTONS_]",
        "image.alt": "[_BUTTONS_][_ALT_LAYER_]",
        "image.size": "[_BUTTONS_][_SIZE_LAYER_]"
    }), be.extend(be.FE.DEFAULTS, {
        imageInsertButtons: ["imageBack", "|", "imageUpload", "imageByURL"],
        imageEditButtons: ["imageReplace", "imageAlign", "imageCaption", "imageRemove", "|", "imageLink", "linkOpen", "linkEdit", "linkRemove", "-", "imageDisplay", "imageStyle", "imageAlt", "imageSize"],
        imageAltButtons: ["imageBack", "|"],
        imageSizeButtons: ["imageBack", "|"],
        imageUpload: !0,
        imageUploadURL: null,
        imageCORSProxy: "https://cors-anywhere.froala.com",
        imageUploadRemoteUrls: !0,
        imageUploadParam: "file",
        imageUploadParams: {},
        imageUploadToS3: !1,
        imageUploadMethod: "POST",
        imageMaxSize: 10485760,
        imageAllowedTypes: ["jpeg", "jpg", "png", "gif", "webp"],
        imageResize: !0,
        imageResizeWithPercent: !1,
        imageRoundPercent: !1,
        imageDefaultWidth: 300,
        imageDefaultAlign: "center",
        imageDefaultDisplay: "block",
        imageSplitHTML: !1,
        imageStyles: {"fr-rounded": "Rounded", "fr-bordered": "Bordered", "fr-shadow": "Shadow"},
        imageMove: !0,
        imageMultipleStyles: !0,
        imageTextNear: !0,
        imagePaste: !0,
        imagePasteProcess: !1,
        imageMinWidth: 16,
        imageOutputSize: !1,
        imageDefaultMargin: 5,
        imageAddNewLine: !1
    }), be.FE.PLUGINS.image = function (g) {
        var d, l, f, p, o, a, c = "https://i.froala.com/upload", t = !1, i = 1, u = 2, m = 3, h = 4, v = 5, b = 6,
            r = {};

        function y() {
            var e = g.popups.get("image.insert").find(".fr-image-by-url-layer input");
            e.val(""), d && e.val(d.attr("src")), e.trigger("change")
        }

        function n() {
            var e = g.popups.get("image.edit");
            if (e || (e = U()), e) {
                var t = he();
                ve() && (t = t.find(".fr-img-wrap")), g.popups.setContainer("image.edit", g.$sc), g.popups.refresh("image.edit");
                var a = t.offset().left + t.outerWidth() / 2, i = t.offset().top + t.outerHeight();
                d.hasClass("fr-uploading") ? I() : g.popups.show("image.edit", a, i, t.outerHeight())
            }
        }

        function w() {
            $()
        }

        function e() {
            for (var e, t, a = "IMG" == g.el.tagName ? [g.el] : g.el.querySelectorAll("img"), i = 0; i < a.length; i++) {
                var r = be(a[i]);
                !g.opts.htmlUntouched && g.opts.useClasses ? ((g.opts.imageDefaultAlign || g.opts.imageDefaultDisplay) && (0 < (t = r).parents(".fr-img-caption").length && (t = t.parents(".fr-img-caption:first")), t.hasClass("fr-dii") || t.hasClass("fr-dib") || (t.addClass("fr-fi" + ge(t)[0]), t.addClass("fr-di" + de(t)[0]), t.css("margin", ""), t.css("float", ""), t.css("display", ""), t.css("z-index", ""), t.css("position", ""), t.css("overflow", ""), t.css("vertical-align", ""))), g.opts.imageTextNear || (0 < r.parents(".fr-img-caption").length ? r.parents(".fr-img-caption:first").removeClass("fr-dii").addClass("fr-dib") : r.removeClass("fr-dii").addClass("fr-dib"))) : g.opts.htmlUntouched || g.opts.useClasses || (g.opts.imageDefaultAlign || g.opts.imageDefaultDisplay) && (0 < (e = r).parents(".fr-img-caption").length && (e = e.parents(".fr-img-caption:first")), pe(e, e.hasClass("fr-dib") ? "block" : e.hasClass("fr-dii") ? "inline" : null, e.hasClass("fr-fil") ? "left" : e.hasClass("fr-fir") ? "right" : ge(e)), e.removeClass("fr-dib fr-dii fr-fir fr-fil")), g.opts.iframe && r.on("load", g.size.syncIframe)
            }
        }

        function E(e) {
            void 0 === e && (e = !0);
            var t, a = Array.prototype.slice.call(g.el.querySelectorAll("img")), i = [];
            for (t = 0; t < a.length; t++) if (i.push(a[t].getAttribute("src")), be(a[t]).toggleClass("fr-draggable", g.opts.imageMove), "" === a[t].getAttribute("class") && a[t].removeAttribute("class"), "" === a[t].getAttribute("style") && a[t].removeAttribute("style"), a[t].parentNode && a[t].parentNode.parentNode && g.node.hasClass(a[t].parentNode.parentNode, "fr-img-caption")) {
                var r = a[t].parentNode.parentNode;
                g.browser.mozilla || r.setAttribute("contenteditable", !1), r.setAttribute("draggable", !1), r.classList.add("fr-draggable");
                var n = a[t].nextSibling;
                n && !g.browser.mozilla && n.setAttribute("contenteditable", !0)
            }
            if (o) for (t = 0; t < o.length; t++) i.indexOf(o[t].getAttribute("src")) < 0 && g.events.trigger("image.removed", [be(o[t])]);
            if (o && e) {
                var s = [];
                for (t = 0; t < o.length; t++) s.push(o[t].getAttribute("src"));
                for (t = 0; t < a.length; t++) s.indexOf(a[t].getAttribute("src")) < 0 && g.events.trigger("image.loaded", [be(a[t])])
            }
            o = a
        }

        function C() {
            if (l || function () {
                var e;
                g.shared.$image_resizer ? (l = g.shared.$image_resizer, p = g.shared.$img_overlay, g.events.on("destroy", function () {
                    l.removeClass("fr-active").appendTo(be("body:first"))
                }, !0)) : (g.shared.$image_resizer = be('<div class="fr-image-resizer"></div>'), l = g.shared.$image_resizer, g.events.$on(l, "mousedown", function (e) {
                    e.stopPropagation()
                }, !0), g.opts.imageResize && (l.append(s("nw") + s("ne") + s("sw") + s("se")), g.shared.$img_overlay = be('<div class="fr-image-overlay"></div>'), p = g.shared.$img_overlay, e = l.get(0).ownerDocument, be(e).find("body:first").append(p)));
                g.events.on("shared.destroy", function () {
                    l.html("").removeData().remove(), l = null, g.opts.imageResize && (p.remove(), p = null)
                }, !0), g.helpers.isMobile() || g.events.$on(be(g.o_win), "resize", function () {
                    d && !d.hasClass("fr-uploading") ? se(!0) : d && (C(), ce(), I(!1))
                });
                if (g.opts.imageResize) {
                    e = l.get(0).ownerDocument, g.events.$on(l, g._mousedown, ".fr-handler", R), g.events.$on(be(e), g._mousemove, S), g.events.$on(be(e.defaultView || e.parentWindow), g._mouseup, D), g.events.$on(p, "mouseleave", D);
                    var i = 1, r = null, n = 0;
                    g.events.on("keydown", function (e) {
                        if (d) {
                            var t = -1 != navigator.userAgent.indexOf("Mac OS X") ? e.metaKey : e.ctrlKey, a = e.which;
                            (a !== r || 200 < e.timeStamp - n) && (i = 1), (a == be.FE.KEYCODE.EQUALS || g.browser.mozilla && a == be.FE.KEYCODE.FF_EQUALS) && t && !e.altKey ? i = q.call(this, e, 1, 1, i) : (a == be.FE.KEYCODE.HYPHEN || g.browser.mozilla && a == be.FE.KEYCODE.FF_HYPHEN) && t && !e.altKey ? i = q.call(this, e, 2, -1, i) : g.keys.ctrlKey(e) || a != be.FE.KEYCODE.ENTER || (d.before("<br>"), k(d)), r = a, n = e.timeStamp
                        }
                    }, !0), g.events.on("keyup", function () {
                        i = 1
                    })
                }
            }(), !d) return !1;
            var e = g.$wp || g.$sc;
            e.append(l), l.data("instance", g);
            var t = e.scrollTop() - ("static" != e.css("position") ? e.offset().top : 0),
                a = e.scrollLeft() - ("static" != e.css("position") ? e.offset().left : 0);
            a -= g.helpers.getPX(e.css("border-left-width")), t -= g.helpers.getPX(e.css("border-top-width")), g.$el.is("img") && g.$sc.is("body") && (a = t = 0);
            var i = he();
            ve() && (i = i.find(".fr-img-wrap")), l.css("top", (g.opts.iframe ? i.offset().top : i.offset().top + t) - 1).css("left", (g.opts.iframe ? i.offset().left : i.offset().left + a) - 1).css("width", i.get(0).getBoundingClientRect().width).css("height", i.get(0).getBoundingClientRect().height).addClass("fr-active")
        }

        function s(e) {
            return '<div class="fr-handler fr-h' + e + '"></div>'
        }

        function A(e) {
            ve() ? d.parents(".fr-img-caption").css("width", e) : d.css("width", e)
        }

        function R(e) {
            if (!g.core.sameInstance(l)) return !0;
            if (e.preventDefault(), e.stopPropagation(), g.$el.find("img.fr-error").left) return !1;
            g.undo.canDo() || g.undo.saveStep();
            var t = e.pageX || e.originalEvent.touches[0].pageX;
            if ("mousedown" == e.type) {
                var a = g.$oel.get(0).ownerDocument, i = a.defaultView || a.parentWindow, r = !1;
                try {
                    r = i.location != i.parent.location && !(i.$ && i.$.FE)
                } catch (o) {
                }
                r && i.frameElement && (t += g.helpers.getPX(be(i.frameElement).offset().left) + i.frameElement.clientLeft)
            }
            (f = be(this)).data("start-x", t), f.data("start-width", d.width()), f.data("start-height", d.height());
            var n = d.width();
            if (g.opts.imageResizeWithPercent) {
                var s = d.parentsUntil(g.$el, g.html.blockTagsQuery()).get(0) || g.el;
                n = (n / be(s).outerWidth() * 100).toFixed(2) + "%"
            }
            A(n), p.show(), g.popups.hideAll(), fe()
        }

        function S(e) {
            if (!g.core.sameInstance(l)) return !0;
            var t;
            if (f && d) {
                if (e.preventDefault(), g.$el.find("img.fr-error").left) return !1;
                var a = e.pageX || (e.originalEvent.touches ? e.originalEvent.touches[0].pageX : null);
                if (!a) return !1;
                var i = a - f.data("start-x"), r = f.data("start-width");
                if ((f.hasClass("fr-hnw") || f.hasClass("fr-hsw")) && (i = 0 - i), g.opts.imageResizeWithPercent) {
                    var n = d.parentsUntil(g.$el, g.html.blockTagsQuery()).get(0) || g.el;
                    r = ((r + i) / be(n).outerWidth() * 100).toFixed(2), g.opts.imageRoundPercent && (r = Math.round(r)), A(r + "%"), (t = ve() ? (g.helpers.getPX(d.parents(".fr-img-caption").css("width")) / be(n).outerWidth() * 100).toFixed(2) : (g.helpers.getPX(d.css("width")) / be(n).outerWidth() * 100).toFixed(2)) === r || g.opts.imageRoundPercent || A(t + "%"), d.css("height", "").removeAttr("height")
                } else r + i >= g.opts.imageMinWidth && (A(r + i), t = ve() ? g.helpers.getPX(d.parents(".fr-img-caption").css("width")) : g.helpers.getPX(d.css("width"))), t !== r + i && A(t), ((d.attr("style") || "").match(/(^height:)|(; *height:)/) || d.attr("height")) && (d.css("height", f.data("start-height") * d.width() / f.data("start-width")), d.removeAttr("height"));
                C(), g.events.trigger("image.resize", [me()])
            }
        }

        function D(e) {
            if (!g.core.sameInstance(l)) return !0;
            if (f && d) {
                if (e && e.stopPropagation(), g.$el.find("img.fr-error").left) return !1;
                f = null, p.hide(), C(), n(), g.undo.saveStep(), g.events.trigger("image.resizeEnd", [me()])
            }
        }

        function x(e, t, a) {
            g.edit.on(), d && d.addClass("fr-error"), function (e) {
                I();
                var t = g.popups.get("image.insert").find(".fr-image-progress-bar-layer");
                t.addClass("fr-error");
                var a = t.find("h3");
                a.text(e), g.events.disableBlur(), a.focus()
            }(g.language.translate("Something went wrong. Please try again.")), !d && a && Q(a), g.events.trigger("image.error", [{
                code: e,
                message: r[e]
            }, t, a])
        }

        function U(e) {
            if (e) return g.$wp && g.events.$on(g.$wp, "scroll.image-edit", function () {
                d && g.popups.isVisible("image.edit") && (g.events.disableBlur(), n())
            }), !0;
            var t = "";
            if (0 < g.opts.imageEditButtons.length) {
                t += '<div class="fr-buttons">', t += g.button.buildList(g.opts.imageEditButtons);
                var a = {buttons: t += "</div>"};
                return g.popups.create("image.edit", a)
            }
            return !1
        }

        function I(e) {
            var t = g.popups.get("image.insert");
            if (t || (t = W()), t.find(".fr-layer.fr-active").removeClass("fr-active").addClass("fr-pactive"), t.find(".fr-image-progress-bar-layer").addClass("fr-active"), t.find(".fr-buttons").hide(), d) {
                var a = he();
                g.popups.setContainer("image.insert", g.$sc);
                var i = a.offset().left + a.width() / 2, r = a.offset().top + a.height();
                g.popups.show("image.insert", i, r, a.outerHeight())
            }
            void 0 === e && F(g.language.translate("Uploading"), 0)
        }

        function $(e) {
            var t = g.popups.get("image.insert");
            if (t && (t.find(".fr-layer.fr-pactive").addClass("fr-active").removeClass("fr-pactive"), t.find(".fr-image-progress-bar-layer").removeClass("fr-active"), t.find(".fr-buttons").show(), e || g.$el.find("img.fr-error").length)) {
                if (g.events.focus(), g.$el.find("img.fr-error").length && (g.$el.find("img.fr-error").remove(), g.undo.saveStep(), g.undo.run(), g.undo.dropRedo()), !g.$wp && d) {
                    var a = d;
                    se(!0), g.selection.setAfter(a.get(0)), g.selection.restore()
                }
                g.popups.hide("image.insert")
            }
        }

        function F(e, t) {
            var a = g.popups.get("image.insert");
            if (a) {
                var i = a.find(".fr-image-progress-bar-layer");
                i.find("h3").text(e + (t ? " " + t + "%" : "")), i.removeClass("fr-error"), t ? (i.find("div").removeClass("fr-indeterminate"), i.find("div > span").css("width", t + "%")) : i.find("div").addClass("fr-indeterminate")
            }
        }

        function k(e) {
            ne.call(e.get(0))
        }

        function N() {
            var e = be(this);
            g.popups.hide("image.insert"), e.removeClass("fr-uploading"), e.next().is("br") && e.next().remove(), k(e), g.events.trigger("image.loaded", [e])
        }

        function B(s, e, o, l, f) {
            g.edit.off(), F(g.language.translate("Loading image")), e && (s = g.helpers.sanitizeURL(s));
            var t = new Image;
            t.onload = function () {
                var e, t;
                if (l) {
                    g.undo.canDo() || l.hasClass("fr-uploading") || g.undo.saveStep();
                    var a = l.data("fr-old-src");
                    l.data("fr-image-pasted") && (a = null), g.$wp ? ((e = l.clone().removeData("fr-old-src").removeClass("fr-uploading").removeAttr("data-fr-image-pasted")).off("load"), a && l.attr("src", a), l.replaceWith(e)) : e = l;
                    for (var i = e.get(0).attributes, r = 0; r < i.length; r++) {
                        var n = i[r];
                        0 === n.nodeName.indexOf("data-") && e.removeAttr(n.nodeName)
                    }
                    if (void 0 !== o) for (t in o) o.hasOwnProperty(t) && "link" != t && e.attr("data-" + t, o[t]);
                    e.on("load", N), e.attr("src", s), g.edit.on(), E(!1), g.undo.saveStep(), g.events.disableBlur(), g.$el.blur(), g.events.trigger(a ? "image.replaced" : "image.inserted", [e, f])
                } else e = L(s, o, N), E(!1), g.undo.saveStep(), g.events.disableBlur(), g.$el.blur(), g.events.trigger("image.inserted", [e, f])
            }, t.onerror = function () {
                x(i)
            }, I(g.language.translate("Loading image")), t.src = s
        }

        function O(e) {
            F(g.language.translate("Loading image"));
            var t = this.status, a = this.response, i = this.responseXML, r = this.responseText;
            try {
                if (g.opts.imageUploadToS3) if (201 == t) {
                    var n = function (e) {
                        try {
                            var t = be(e).find("Location").text(), a = be(e).find("Key").text();
                            return !1 === g.events.trigger("image.uploadedToS3", [t, a, e], !0) ? (g.edit.on(), !1) : t
                        } catch (i) {
                            return x(h, e), !1
                        }
                    }(i);
                    n && B(n, !1, [], e, a || i)
                } else x(h, a || i, e); else if (200 <= t && t < 300) {
                    var s = function (e) {
                        try {
                            if (!1 === g.events.trigger("image.uploaded", [e], !0)) return g.edit.on(), !1;
                            var t = JSON.parse(e);
                            return t.link ? t : (x(u, e), !1)
                        } catch (a) {
                            return x(h, e), !1
                        }
                    }(r);
                    s && B(s.link, !1, s, e, a || r)
                } else x(m, a || r, e)
            } catch (o) {
                x(h, a || r, e)
            }
        }

        function P() {
            x(h, this.response || this.responseText || this.responseXML)
        }

        function T(e) {
            if (e.lengthComputable) {
                var t = e.loaded / e.total * 100 | 0;
                F(g.language.translate("Uploading"), t)
            }
        }

        function L(e, t, a) {
            var i, r = "";
            if (t && void 0 !== t) for (i in t) t.hasOwnProperty(i) && "link" != i && (r += " data-" + i + '="' + t[i] + '"');
            var n = g.opts.imageDefaultWidth;
            n && "auto" != n && (n += g.opts.imageResizeWithPercent ? "%" : "px");
            var s = be('<img src="' + e + '"' + r + (n ? ' style="width: ' + n + ';"' : "") + ">");
            pe(s, g.opts.imageDefaultDisplay, g.opts.imageDefaultAlign), s.on("load", a), s.on("error", a), g.edit.on(), g.events.focus(!0), g.selection.restore(), g.undo.saveStep(), g.opts.imageSplitHTML ? g.markers.split() : g.markers.insert(), g.html.wrap();
            var o = g.$el.find(".fr-marker");
            return o.length ? (o.parent().is("hr") && o.parent().after(o), g.node.isLastSibling(o) && o.parent().hasClass("fr-deletable") && o.insertAfter(o.parent()), o.replaceWith(s)) : g.$el.append(s), g.selection.clear(), s
        }

        function M() {
            g.edit.on(), $(!0)
        }

        function z(e, t) {
            if (void 0 !== e && 0 < e.length) {
                if (!1 === g.events.trigger("image.beforeUpload", [e, t])) return !1;
                var a, i = e[0];
                if ((null === g.opts.imageUploadURL || g.opts.imageUploadURL == c) && !g.opts.imageUploadToS3) return s = i, o = t || d, (l = new FileReader).onload = function () {
                    var e = l.result;
                    if (l.result.indexOf("svg+xml") < 0) {
                        for (var t = atob(l.result.split(",")[1]), a = [], i = 0; i < t.length; i++) a.push(t.charCodeAt(i));
                        e = window.URL.createObjectURL(new Blob([new Uint8Array(a)], {type: s.type})), g.image.insert(e, !1, null, o)
                    }
                }, I(), l.readAsDataURL(s), !1;
                if (i.name || (i.name = (new Date).getTime() + "." + (i.type || "image/jpeg").replace(/image\//g, "")), i.size > g.opts.imageMaxSize) return x(v), !1;
                if (g.opts.imageAllowedTypes.indexOf(i.type.replace(/image\//g, "")) < 0) return x(b), !1;
                if (g.drag_support.formdata && (a = g.drag_support.formdata ? new FormData : null), a) {
                    var r;
                    if (!1 !== g.opts.imageUploadToS3) for (r in a.append("key", g.opts.imageUploadToS3.keyStart + (new Date).getTime() + "-" + (i.name || "untitled")), a.append("success_action_status", "201"), a.append("X-Requested-With", "xhr"), a.append("Content-Type", i.type), g.opts.imageUploadToS3.params) g.opts.imageUploadToS3.params.hasOwnProperty(r) && a.append(r, g.opts.imageUploadToS3.params[r]);
                    for (r in g.opts.imageUploadParams) g.opts.imageUploadParams.hasOwnProperty(r) && a.append(r, g.opts.imageUploadParams[r]);
                    a.append(g.opts.imageUploadParam, i, i.name);
                    var n = g.opts.imageUploadURL;
                    g.opts.imageUploadToS3 && (n = g.opts.imageUploadToS3.uploadURL ? g.opts.imageUploadToS3.uploadURL : "https://" + g.opts.imageUploadToS3.region + ".amazonaws.com/" + g.opts.imageUploadToS3.bucket), function (t, a, e, r) {
                        function n() {
                            var e = be(this);
                            e.off("load"), e.addClass("fr-uploading"), e.next().is("br") && e.next().remove(), g.placeholder.refresh(), k(e), C(), I(), g.edit.off(), t.onload = function () {
                                O.call(t, e)
                            }, t.onerror = P, t.upload.onprogress = T, t.onabort = M, e.off("abortUpload").on("abortUpload", function () {
                                4 != t.readyState && t.abort()
                            }), t.send(a)
                        }

                        var s = new FileReader;
                        s.onload = function () {
                            var e = s.result;
                            if (s.result.indexOf("svg+xml") < 0) {
                                for (var t = atob(s.result.split(",")[1]), a = [], i = 0; i < t.length; i++) a.push(t.charCodeAt(i));
                                e = window.URL.createObjectURL(new Blob([new Uint8Array(a)], {type: "image/jpeg"}))
                            }
                            r ? (r.on("load", n), r.one("error", n), g.edit.on(), g.undo.saveStep(), r.data("fr-old-src", r.attr("src")), r.attr("src", e)) : L(e, null, n)
                        }, s.readAsDataURL(e)
                    }(g.core.getXHR(n, g.opts.imageUploadMethod), a, i, t || d)
                }
            }
            var s, o, l
        }

        function _(e) {
            if (e.is("img") && 0 < e.parents(".fr-img-caption").length) return e.parents(".fr-img-caption")
        }

        function K(e) {
            var t = e.originalEvent.dataTransfer;
            if (t && t.files && t.files.length) {
                var a = t.files[0];
                if (a && a.type && -1 !== a.type.indexOf("image") && 0 <= g.opts.imageAllowedTypes.indexOf(a.type.replace(/image\//g, ""))) {
                    if (!g.opts.imageUpload) return e.preventDefault(), e.stopPropagation(), !1;
                    g.markers.remove(), g.markers.insertAtPoint(e.originalEvent), g.$el.find(".fr-marker").replaceWith(be.FE.MARKERS), 0 === g.$el.find(".fr-marker").length && g.selection.setAtEnd(g.el), g.popups.hideAll();
                    var i = g.popups.get("image.insert");
                    i || (i = W()), g.popups.setContainer("image.insert", g.$sc);
                    var r = e.originalEvent.pageX, n = e.originalEvent.pageY;
                    return g.opts.iframe && (n += g.$iframe.offset().top, r += g.$iframe.offset().left), g.popups.show("image.insert", r, n), I(), 0 <= g.opts.imageAllowedTypes.indexOf(a.type.replace(/image\//g, "")) ? (se(!0), z(t.files)) : x(b), e.preventDefault(), e.stopPropagation(), !1
                }
            }
        }

        function W(e) {
            if (e) return g.popups.onRefresh("image.insert", y), g.popups.onHide("image.insert", w), !0;
            var t, a, i = "";
            g.opts.imageUpload || -1 === g.opts.imageInsertButtons.indexOf("imageUpload") || g.opts.imageInsertButtons.splice(g.opts.imageInsertButtons.indexOf("imageUpload"), 1);
            var r = g.button.buildList(g.opts.imageInsertButtons);
            "" !== r && (i = '<div class="fr-buttons">' + r + "</div>");
            var n = g.opts.imageInsertButtons.indexOf("imageUpload"),
                s = g.opts.imageInsertButtons.indexOf("imageByURL"), o = "";
            0 <= n && (t = " fr-active", 0 <= s && s < n && (t = ""), o = '<div class="fr-image-upload-layer' + t + ' fr-layer" id="fr-image-upload-layer-' + g.id + '"><strong>' + g.language.translate("Drop image") + "</strong><br>(" + g.language.translate("or click") + ')<div class="fr-form"><input type="file" accept="image/' + g.opts.imageAllowedTypes.join(", image/").toLowerCase() + '" tabIndex="-1" aria-labelledby="fr-image-upload-layer-' + g.id + '" role="button"></div></div>');
            var l = "";
            0 <= s && (t = " fr-active", 0 <= n && n < s && (t = ""), l = '<div class="fr-image-by-url-layer' + t + ' fr-layer" id="fr-image-by-url-layer-' + g.id + '"><div class="fr-input-line"><input id="fr-image-by-url-layer-text-' + g.id + '" type="text" placeholder="http://" tabIndex="1" aria-required="true"></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="imageInsertByURL" tabIndex="2" role="button">' + g.language.translate("Insert") + "</button></div></div>");
            var f, p = {
                buttons: i,
                upload_layer: o,
                by_url_layer: l,
                progress_bar: '<div class="fr-image-progress-bar-layer fr-layer"><h3 tabIndex="-1" class="fr-message">Uploading</h3><div class="fr-loader"><span class="fr-progress"></span></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-dismiss" data-cmd="imageDismissError" tabIndex="2" role="button">OK</button></div></div>'
            };
            return 1 <= g.opts.imageInsertButtons.length && (a = g.popups.create("image.insert", p)), g.$wp && g.events.$on(g.$wp, "scroll", function () {
                d && g.popups.isVisible("image.insert") && ce()
            }), f = a, g.events.$on(f, "dragover dragenter", ".fr-image-upload-layer", function () {
                return be(this).addClass("fr-drop"), !1
            }, !0), g.events.$on(f, "dragleave dragend", ".fr-image-upload-layer", function () {
                return be(this).removeClass("fr-drop"), !1
            }, !0), g.events.$on(f, "drop", ".fr-image-upload-layer", function (e) {
                e.preventDefault(), e.stopPropagation(), be(this).removeClass("fr-drop");
                var t = e.originalEvent.dataTransfer;
                if (t && t.files) {
                    var a = f.data("instance") || g;
                    a.events.disableBlur(), a.image.upload(t.files), a.events.enableBlur()
                }
            }, !0), g.helpers.isIOS() && g.events.$on(f, "touchstart", '.fr-image-upload-layer input[type="file"]', function () {
                be(this).trigger("click")
            }, !0), g.events.$on(f, "change", '.fr-image-upload-layer input[type="file"]', function () {
                if (this.files) {
                    var e = f.data("instance") || g;
                    e.events.disableBlur(), f.find("input:focus").blur(), e.events.enableBlur(), e.image.upload(this.files, d)
                }
                be(this).val("")
            }, !0), a
        }

        function H() {
            d && g.popups.get("image.alt").find("input").val(d.attr("alt") || "").trigger("change")
        }

        function Y() {
            var e = g.popups.get("image.alt");
            e || (e = X()), $(), g.popups.refresh("image.alt"), g.popups.setContainer("image.alt", g.$sc);
            var t = he();
            ve() && (t = t.find(".fr-img-wrap"));
            var a = t.offset().left + t.outerWidth() / 2, i = t.offset().top + t.outerHeight();
            g.popups.show("image.alt", a, i, t.outerHeight())
        }

        function X(e) {
            if (e) return g.popups.onRefresh("image.alt", H), !0;
            var t = {
                buttons: '<div class="fr-buttons">' + g.button.buildList(g.opts.imageAltButtons) + "</div>",
                alt_layer: '<div class="fr-image-alt-layer fr-layer fr-active" id="fr-image-alt-layer-' + g.id + '"><div class="fr-input-line"><input id="fr-image-alt-layer-text-' + g.id + '" type="text" placeholder="' + g.language.translate("Alternative Text") + '" tabIndex="1"></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="imageSetAlt" tabIndex="2" role="button">' + g.language.translate("Update") + "</button></div></div>"
            }, a = g.popups.create("image.alt", t);
            return g.$wp && g.events.$on(g.$wp, "scroll.image-alt", function () {
                d && g.popups.isVisible("image.alt") && Y()
            }), a
        }

        function j() {
            var e = g.popups.get("image.size");
            if (d) if (ve()) {
                var t = d.parent();
                t.get(0).style.width || (t = d.parent().parent()), e.find('input[name="width"]').val(t.get(0).style.width).trigger("change"), e.find('input[name="height"]').val(t.get(0).style.height).trigger("change")
            } else e.find('input[name="width"]').val(d.get(0).style.width).trigger("change"), e.find('input[name="height"]').val(d.get(0).style.height).trigger("change")
        }

        function G() {
            var e = g.popups.get("image.size");
            e || (e = V()), $(), g.popups.refresh("image.size"), g.popups.setContainer("image.size", g.$sc);
            var t = he();
            ve() && (t = t.find(".fr-img-wrap"));
            var a = t.offset().left + t.outerWidth() / 2, i = t.offset().top + t.outerHeight();
            g.popups.show("image.size", a, i, t.outerHeight())
        }

        function V(e) {
            if (e) return g.popups.onRefresh("image.size", j), !0;
            var t = {
                buttons: '<div class="fr-buttons">' + g.button.buildList(g.opts.imageSizeButtons) + "</div>",
                size_layer: '<div class="fr-image-size-layer fr-layer fr-active" id="fr-image-size-layer-' + g.id + '"><div class="fr-image-group"><div class="fr-input-line"><input id="fr-image-size-layer-width-' + g.id + '" type="text" name="width" placeholder="' + g.language.translate("Width") + '" tabIndex="1"></div><div class="fr-input-line"><input id="fr-image-size-layer-height' + g.id + '" type="text" name="height" placeholder="' + g.language.translate("Height") + '" tabIndex="1"></div></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="imageSetSize" tabIndex="2" role="button">' + g.language.translate("Update") + "</button></div></div>"
            }, a = g.popups.create("image.size", t);
            return g.$wp && g.events.$on(g.$wp, "scroll.image-size", function () {
                d && g.popups.isVisible("image.size") && G()
            }), a
        }

        function q(e, t, a, i) {
            return e.pageX = t, R.call(this, e), e.pageX = e.pageX + a * Math.floor(Math.pow(1.1, i)), S.call(this, e), D.call(this, e), ++i
        }

        function Q(e) {
            (e = e || he()) && !1 !== g.events.trigger("image.beforeRemove", [e]) && (g.popups.hideAll(), ue(), se(!0), g.undo.canDo() || g.undo.saveStep(), e.get(0) == g.el ? e.removeAttr("src") : (e.get(0).parentNode && "A" == e.get(0).parentNode.tagName ? (g.selection.setBefore(e.get(0).parentNode) || g.selection.setAfter(e.get(0).parentNode) || e.parent().after(be.FE.MARKERS), be(e.get(0).parentNode).remove()) : (g.selection.setBefore(e.get(0)) || g.selection.setAfter(e.get(0)) || e.after(be.FE.MARKERS), e.remove()), g.html.fillEmptyBlocks(), g.selection.restore()), g.undo.saveStep())
        }

        function J(e) {
            var t = e.which;
            if (d && (t == be.FE.KEYCODE.BACKSPACE || t == be.FE.KEYCODE.DELETE)) return e.preventDefault(), e.stopPropagation(), Q(), !1;
            if (d && t == be.FE.KEYCODE.ESC) {
                var a = d;
                return se(!0), g.selection.setAfter(a.get(0)), g.selection.restore(), e.preventDefault(), !1
            }
            if (d && (t == be.FE.KEYCODE.ARROW_LEFT || t == be.FE.KEYCODE.ARROW_RIGHT)) {
                var i = d.get(0);
                return se(!0), t == be.FE.KEYCODE.ARROW_LEFT ? g.selection.setBefore(i) : g.selection.setAfter(i), g.selection.restore(), e.preventDefault(), !1
            }
            return d && t != be.FE.KEYCODE.F10 && !g.keys.isBrowserAction(e) ? (e.preventDefault(), e.stopPropagation(), !1) : void 0
        }

        function Z(e) {
            if (e && "IMG" == e.tagName) {
                if (g.node.hasClass(e, "fr-uploading") || g.node.hasClass(e, "fr-error") ? e.parentNode.removeChild(e) : g.node.hasClass(e, "fr-draggable") && e.classList.remove("fr-draggable"), e.parentNode && e.parentNode.parentNode && g.node.hasClass(e.parentNode.parentNode, "fr-img-caption")) {
                    var t = e.parentNode.parentNode;
                    t.removeAttribute("contenteditable"), t.removeAttribute("draggable"), t.classList.remove("fr-draggable");
                    var a = e.nextSibling;
                    a && a.removeAttribute("contenteditable")
                }
            } else if (e && e.nodeType == Node.ELEMENT_NODE) for (var i = e.querySelectorAll("img.fr-uploading, img.fr-error, img.fr-draggable"), r = 0; r < i.length; r++) Z(i[r])
        }

        function ee(e) {
            if (!1 === g.events.trigger("image.beforePasteUpload", [e])) return !1;
            d = be(e), C(), n(), ce(), I(), d.one("load", function () {
                C(), I()
            });
            for (var t = be(e).attr("src").split(","), a = atob(t[1]), i = [], r = 0; r < a.length; r++) i.push(a.charCodeAt(r));
            z([new Blob([new Uint8Array(i)], {type: t[0].replace(/data\:/g, "").replace(/;base64/g, "")})], d)
        }

        function te() {
            g.opts.imagePaste ? g.$el.find("img[data-fr-image-pasted]").each(function (e, a) {
                if (g.opts.imagePasteProcess) {
                    var t = g.opts.imageDefaultWidth;
                    t && "auto" != t && (t += g.opts.imageResizeWithPercent ? "%" : "px"), be(a).css("width", t).removeClass("fr-dii fr-dib fr-fir fr-fil"), pe(be(a), g.opts.imageDefaultDisplay, g.opts.imageDefaultAlign)
                }
                if (0 === a.src.indexOf("data:")) ee(a); else if (0 === a.src.indexOf("blob:") || 0 === a.src.indexOf("http") && g.opts.imageUploadRemoteUrls && g.opts.imageCORSProxy) {
                    var i = new Image;
                    i.crossOrigin = "Anonymous", i.onload = function () {
                        var e = g.o_doc.createElement("CANVAS"), t = e.getContext("2d");
                        e.height = this.naturalHeight, e.width = this.naturalWidth, t.drawImage(this, 0, 0), setTimeout(function () {
                            ee(a)
                        }, 0), a.src = e.toDataURL("image/jpeg")
                    }, i.src = (0 === a.src.indexOf("blob:") ? "" : g.opts.imageCORSProxy + "/") + a.src
                } else 0 !== a.src.indexOf("http") || 0 === a.src.indexOf("https://mail.google.com/mail") ? (g.selection.save(), be(a).remove(), g.selection.restore()) : be(a).removeAttr("data-fr-image-pasted")
            }) : g.$el.find("img[data-fr-image-pasted]").remove()
        }

        function ae(e) {
            var t = e.target.result, a = g.opts.imageDefaultWidth;
            a && "auto" != a && (a += g.opts.imageResizeWithPercent ? "%" : "px"), g.undo.saveStep(), g.html.insert('<img data-fr-image-pasted="true" src="' + t + '"' + (a ? ' style="width: ' + a + ';"' : "") + ">");
            var i = g.$el.find('img[data-fr-image-pasted="true"]');
            i && pe(i, g.opts.imageDefaultDisplay, g.opts.imageDefaultAlign), g.events.trigger("paste.after")
        }

        function ie(e) {
            if (e && e.clipboardData && e.clipboardData.items) {
                var t = null;
                if (e.clipboardData.types && -1 != e.clipboardData.types.indexOf("text/rtf") || e.clipboardData.getData("text/rtf")) t = e.clipboardData.items[0].getAsFile(); else for (var a = 0; a < e.clipboardData.items.length && !(t = e.clipboardData.items[a].getAsFile()); a++) ;
                if (t) return i = t, (r = new FileReader).onload = ae, r.readAsDataURL(i), !1
            }
            var i, r
        }

        function re(e) {
            return e = e.replace(/<img /gi, '<img data-fr-image-pasted="true" ')
        }

        function ne(e) {
            if ("false" == be(this).parents("[contenteditable]:not(.fr-element):not(.fr-img-caption):not(body):first").attr("contenteditable")) return !0;
            if (e && "touchend" == e.type && a) return !0;
            if (e && g.edit.isDisabled()) return e.stopPropagation(), e.preventDefault(), !1;
            for (var t = 0; t < be.FE.INSTANCES.length; t++) be.FE.INSTANCES[t] != g && be.FE.INSTANCES[t].events.trigger("image.hideResizer");
            g.toolbar.disable(), e && (e.stopPropagation(), e.preventDefault()), g.helpers.isMobile() && (g.events.disableBlur(), g.$el.blur(), g.events.enableBlur()), g.opts.iframe && g.size.syncIframe(), d = be(this), ue(), C(), n(), g.browser.msie ? (g.popups.areVisible() && g.events.disableBlur(), g.win.getSelection && (g.win.getSelection().removeAllRanges(), g.win.getSelection().addRange(g.doc.createRange()))) : g.selection.clear(), g.helpers.isIOS() && (g.events.disableBlur(), g.$el.blur()), g.button.bulkRefresh(), g.events.trigger("video.hideResizer")
        }

        function se(e) {
            d && (oe || !0 === e) && (g.toolbar.enable(), l.removeClass("fr-active"), g.popups.hide("image.edit"), d = null, fe(), f = null, p && p.hide())
        }

        r[i] = "Image cannot be loaded from the passed link.", r[u] = "No link in upload response.", r[m] = "Error during file upload.", r[h] = "Parsing response failed.", r[v] = "File is too large.", r[b] = "Image file type is invalid.", r[7] = "Files can be uploaded only to same domain in IE 8 and IE 9.";
        var oe = !(r[8] = "Image file is corrupted.");

        function le() {
            oe = !0
        }

        function fe() {
            oe = !1
        }

        function pe(e, t, a) {
            !g.opts.htmlUntouched && g.opts.useClasses ? (e.removeClass("fr-fil fr-fir fr-dib fr-dii"), a && e.addClass("fr-fi" + a[0]), t && e.addClass("fr-di" + t[0])) : "inline" == t ? (e.css({
                display: "inline-block",
                verticalAlign: "bottom",
                margin: g.opts.imageDefaultMargin
            }), "center" == a ? e.css({
                "float": "none",
                marginBottom: "",
                marginTop: "",
                maxWidth: "calc(100% - " + 2 * g.opts.imageDefaultMargin + "px)",
                textAlign: "center"
            }) : "left" == a ? e.css({
                "float": "left",
                marginLeft: 0,
                maxWidth: "calc(100% - " + g.opts.imageDefaultMargin + "px)",
                textAlign: "left"
            }) : e.css({
                "float": "right",
                marginRight: 0,
                maxWidth: "calc(100% - " + g.opts.imageDefaultMargin + "px)",
                textAlign: "right"
            })) : "block" == t && (e.css({
                display: "block",
                "float": "none",
                verticalAlign: "top",
                margin: g.opts.imageDefaultMargin + "px auto",
                textAlign: "center"
            }), "left" == a ? e.css({marginLeft: 0, textAlign: "left"}) : "right" == a && e.css({
                marginRight: 0,
                textAlign: "right"
            }))
        }

        function ge(e) {
            if (void 0 === e && (e = he()), e) {
                if (e.hasClass("fr-fil")) return "left";
                if (e.hasClass("fr-fir")) return "right";
                if (e.hasClass("fr-dib") || e.hasClass("fr-dii")) return "center";
                var t = e.css("float");
                if (e.css("float", "none"), "block" == e.css("display")) {
                    if (e.css("float", ""), e.css("float") != t && e.css("float", t), 0 === parseInt(e.css("margin-left"), 10)) return "left";
                    if (0 === parseInt(e.css("margin-right"), 10)) return "right"
                } else {
                    if (e.css("float", ""), e.css("float") != t && e.css("float", t), "left" == e.css("float")) return "left";
                    if ("right" == e.css("float")) return "right"
                }
            }
            return "center"
        }

        function de(e) {
            void 0 === e && (e = he());
            var t = e.css("float");
            return e.css("float", "none"), "block" == e.css("display") ? (e.css("float", ""), e.css("float") != t && e.css("float", t), "block") : (e.css("float", ""), e.css("float") != t && e.css("float", t), "inline")
        }

        function ce() {
            var e = g.popups.get("image.insert");
            e || (e = W()), g.popups.isVisible("image.insert") || ($(), g.popups.refresh("image.insert"), g.popups.setContainer("image.insert", g.$sc));
            var t = he();
            ve() && (t = t.find(".fr-img-wrap"));
            var a = t.offset().left + t.outerWidth() / 2, i = t.offset().top + t.outerHeight();
            g.popups.show("image.insert", a, i, t.outerHeight(!0))
        }

        function ue() {
            if (d) {
                g.events.disableBlur(), g.selection.clear();
                var e = g.doc.createRange();
                e.selectNode(d.get(0)), g.browser.msie && e.collapse(!0), g.selection.get().addRange(e), g.events.enableBlur()
            }
        }

        function me() {
            return d
        }

        function he() {
            return ve() ? d.parents(".fr-img-caption:first") : d
        }

        function ve() {
            return !!d && 0 < d.parents(".fr-img-caption").length
        }

        return {
            _init: function () {
                var i;
                g.events.$on(g.$el, g._mousedown, "IMG" == g.el.tagName ? null : 'img:not([contenteditable="false"])', function (e) {
                    if ("false" == be(this).parents("[contenteditable]:not(.fr-element):not(.fr-img-caption):not(body):first").attr("contenteditable")) return !0;
                    g.helpers.isMobile() || g.selection.clear(), t = !0, g.popups.areVisible() && g.events.disableBlur(), g.browser.msie && (g.events.disableBlur(), g.$el.attr("contenteditable", !1)), g.draggable || "touchstart" == e.type || e.preventDefault(), e.stopPropagation()
                }), g.events.$on(g.$el, g._mouseup, "IMG" == g.el.tagName ? null : 'img:not([contenteditable="false"])', function (e) {
                    if ("false" == be(this).parents("[contenteditable]:not(.fr-element):not(.fr-img-caption):not(body):first").attr("contenteditable")) return !0;
                    t && (t = !1, e.stopPropagation(), g.browser.msie && (g.$el.attr("contenteditable", !0), g.events.enableBlur()))
                }), g.events.on("keyup", function (e) {
                    if (e.shiftKey && "" === g.selection.text().replace(/\n/g, "") && g.keys.isArrow(e.which)) {
                        var t = g.selection.element(), a = g.selection.endElement();
                        t && "IMG" == t.tagName ? k(be(t)) : a && "IMG" == a.tagName && k(be(a))
                    }
                }, !0), g.events.on("drop", K), g.events.on("element.beforeDrop", _), g.events.on("mousedown window.mousedown", le), g.events.on("window.touchmove", fe), g.events.on("mouseup window.mouseup", function () {
                    if (d) return se(), !1;
                    fe()
                }), g.events.on("commands.mousedown", function (e) {
                    0 < e.parents(".fr-toolbar").length && se()
                }), g.events.on("image.resizeEnd", function () {
                    g.opts.iframe && g.size.syncIframe()
                }), g.events.on("blur image.hideResizer commands.undo commands.redo element.dropped", function () {
                    se(!(t = !1))
                }), g.events.on("modals.hide", function () {
                    d && (ue(), g.selection.clear())
                }), g.events.on("image.resizeEnd", function () {
                    g.win.getSelection && k(d)
                }), g.opts.imageAddNewLine && g.events.on("image.inserted", function (e) {
                    var t = e.get(0);
                    for (t.nextSibling && "BR" === t.nextSibling.tagName && (t = t.nextSibling); t && !g.node.isElement(t);) t = g.node.isLastSibling(t) ? t.parentNode : null;
                    g.node.isElement(t) && (g.opts.enter === be.FE.ENTER_BR ? e.after("<br>") : be(g.node.blockParent(e.get(0))).after("<" + g.html.defaultTag() + "><br></" + g.html.defaultTag() + ">"))
                }), "IMG" == g.el.tagName && g.$el.addClass("fr-view"), g.events.$on(g.$el, g.helpers.isMobile() && !g.helpers.isWindowsPhone() ? "touchend" : "click", "IMG" == g.el.tagName ? null : 'img:not([contenteditable="false"])', ne), g.helpers.isMobile() && (g.events.$on(g.$el, "touchstart", "IMG" == g.el.tagName ? null : 'img:not([contenteditable="false"])', function () {
                    a = !1
                }), g.events.$on(g.$el, "touchmove", function () {
                    a = !0
                })), g.$wp ? (g.events.on("window.keydown keydown", J, !0), g.events.on("keyup", function (e) {
                    if (d && e.which == be.FE.KEYCODE.ENTER) return !1
                }, !0), g.events.$on(g.$el, "keydown", function () {
                    var e = g.selection.element();
                    e.nodeType === Node.TEXT_NODE && (e = e.parentNode), g.node.hasClass(e, "fr-inner") || (g.node.hasClass(e, "fr-img-caption") || (e = be(e).parents(".fr-img-caption").get(0)), g.node.hasClass(e, "fr-img-caption") && (be(e).after(be.FE.INVISIBLE_SPACE + be.FE.MARKERS), g.selection.restore()))
                })) : g.events.$on(g.$win, "keydown", J), g.events.on("toolbar.esc", function () {
                    if (d) {
                        if (g.$wp) g.events.disableBlur(), g.events.focus(); else {
                            var e = d;
                            se(!0), g.selection.setAfter(e.get(0)), g.selection.restore()
                        }
                        return !1
                    }
                }, !0), g.events.on("toolbar.focusEditor", function () {
                    if (d) return !1
                }, !0), g.events.on("window.cut window.copy", function (e) {
                    if (d && g.popups.isVisible("image.edit") && !g.popups.get("image.edit").find(":focus").length) {
                        var t = he();
                        ve() ? (t.before(be.FE.START_MARKER), t.after(be.FE.END_MARKER), g.selection.restore(), g.paste.saveCopiedText(t.get(0).outerHTML, t.text())) : (ue(), g.paste.saveCopiedText(d.get(0).outerHTML, d.attr("alt"))), "copy" == e.type ? setTimeout(function () {
                            k(d)
                        }) : (se(!0), g.undo.saveStep(), setTimeout(function () {
                            g.undo.saveStep()
                        }, 0))
                    }
                }, !0), g.browser.msie && g.events.on("keydown", function (e) {
                    if (!g.selection.isCollapsed() || !d) return !0;
                    var t = e.which;
                    t == be.FE.KEYCODE.C && g.keys.ctrlKey(e) ? g.events.trigger("window.copy") : t == be.FE.KEYCODE.X && g.keys.ctrlKey(e) && g.events.trigger("window.cut")
                }), g.events.$on(be(g.o_win), "keydown", function (e) {
                    var t = e.which;
                    if (d && t == be.FE.KEYCODE.BACKSPACE) return e.preventDefault(), !1
                }), g.events.$on(g.$win, "keydown", function (e) {
                    var t = e.which;
                    d && d.hasClass("fr-uploading") && t == be.FE.KEYCODE.ESC && d.trigger("abortUpload")
                }), g.events.on("destroy", function () {
                    d && d.hasClass("fr-uploading") && d.trigger("abortUpload")
                }), g.events.on("paste.before", ie), g.events.on("paste.beforeCleanup", re), g.events.on("paste.after", te), g.events.on("html.set", e), g.events.on("html.inserted", e), e(), g.events.on("destroy", function () {
                    o = []
                }), g.events.on("html.processGet", Z), g.opts.imageOutputSize && g.events.on("html.beforeGet", function () {
                    i = g.el.querySelectorAll("img");
                    for (var e = 0; e < i.length; e++) {
                        var t = i[e].style.width || be(i[e]).width(), a = i[e].style.height || be(i[e]).height();
                        t && i[e].setAttribute("width", ("" + t).replace(/px/, "")), a && i[e].setAttribute("height", ("" + a).replace(/px/, ""))
                    }
                }), g.opts.iframe && g.events.on("image.loaded", g.size.syncIframe), g.$wp && (E(), g.events.on("contentChanged", E)), g.events.$on(be(g.o_win), "orientationchange.image", function () {
                    setTimeout(function () {
                        d && k(d)
                    }, 100)
                }), U(!0), W(!0), V(!0), X(!0), g.events.on("node.remove", function (e) {
                    if ("IMG" == e.get(0).tagName) return Q(e), !1
                })
            },
            showInsertPopup: function () {
                var e = g.$tb.find('.fr-command[data-cmd="insertImage"]'), t = g.popups.get("image.insert");
                if (t || (t = W()), $(), !t.hasClass("fr-active")) if (g.popups.refresh("image.insert"), g.popups.setContainer("image.insert", g.$tb), e.is(":visible")) {
                    var a = e.offset().left + e.outerWidth() / 2,
                        i = e.offset().top + (g.opts.toolbarBottom ? 10 : e.outerHeight() - 10);
                    g.popups.show("image.insert", a, i, e.outerHeight())
                } else g.position.forSelection(t), g.popups.show("image.insert")
            },
            showLayer: function (e) {
                var t, a, i = g.popups.get("image.insert");
                if (d || g.opts.toolbarInline) {
                    if (d) {
                        var r = he();
                        ve() && (r = r.find(".fr-img-wrap")), a = r.offset().top + r.outerHeight(), t = r.offset().left + r.outerWidth() / 2
                    }
                } else {
                    var n = g.$tb.find('.fr-command[data-cmd="insertImage"]');
                    t = n.offset().left + n.outerWidth() / 2, a = n.offset().top + (g.opts.toolbarBottom ? 10 : n.outerHeight() - 10)
                }
                !d && g.opts.toolbarInline && (a = i.offset().top - g.helpers.getPX(i.css("margin-top")), i.hasClass("fr-above") && (a += i.outerHeight())), i.find(".fr-layer").removeClass("fr-active"), i.find(".fr-" + e + "-layer").addClass("fr-active"), g.popups.show("image.insert", t, a, d ? d.outerHeight() : 0), g.accessibility.focusPopup(i)
            },
            refreshUploadButton: function (e) {
                g.popups.get("image.insert").find(".fr-image-upload-layer").hasClass("fr-active") && e.addClass("fr-active").attr("aria-pressed", !0)
            },
            refreshByURLButton: function (e) {
                g.popups.get("image.insert").find(".fr-image-by-url-layer").hasClass("fr-active") && e.addClass("fr-active").attr("aria-pressed", !0)
            },
            upload: z,
            insertByURL: function () {
                var e = g.popups.get("image.insert").find(".fr-image-by-url-layer input");
                if (0 < e.val().length) {
                    I(), F(g.language.translate("Loading image"));
                    var t = e.val().trim();
                    if (g.opts.imageUploadRemoteUrls && g.opts.imageCORSProxy && g.opts.imageUpload) {
                        var a = new XMLHttpRequest;
                        a.onload = function () {
                            200 == this.status ? z([new Blob([this.response], {type: this.response.type || "image/png"})], d) : x(i)
                        }, a.onerror = function () {
                            B(t, !0, [], d)
                        }, a.open("GET", g.opts.imageCORSProxy + "/" + t, !0), a.responseType = "blob", a.send()
                    } else B(t, !0, [], d);
                    e.val(""), e.blur()
                }
            },
            align: function (e) {
                var t = he();
                t.removeClass("fr-fir fr-fil"), !g.opts.htmlUntouched && g.opts.useClasses ? "left" == e ? t.addClass("fr-fil") : "right" == e && t.addClass("fr-fir") : pe(t, de(), e), ue(), C(), n(), g.selection.clear()
            },
            refreshAlign: function (e) {
                d && e.find("> *:first").replaceWith(g.icon.create("image-align-" + ge()))
            },
            refreshAlignOnShow: function (e, t) {
                d && t.find('.fr-command[data-param1="' + ge() + '"]').addClass("fr-active").attr("aria-selected", !0)
            },
            display: function (e) {
                var t = he();
                t.removeClass("fr-dii fr-dib"), !g.opts.htmlUntouched && g.opts.useClasses ? "inline" == e ? t.addClass("fr-dii") : "block" == e && t.addClass("fr-dib") : pe(t, e, ge()), ue(), C(), n(), g.selection.clear()
            },
            refreshDisplayOnShow: function (e, t) {
                d && t.find('.fr-command[data-param1="' + de() + '"]').addClass("fr-active").attr("aria-selected", !0)
            },
            replace: ce,
            back: function () {
                d ? (g.events.disableBlur(), be(".fr-popup input:focus").blur(), k(d)) : (g.events.disableBlur(), g.selection.restore(), g.events.enableBlur(), g.popups.hide("image.insert"), g.toolbar.showInline())
            },
            get: me,
            getEl: he,
            insert: B,
            showProgressBar: I,
            remove: Q,
            hideProgressBar: $,
            applyStyle: function (e, t, a) {
                if (void 0 === t && (t = g.opts.imageStyles), void 0 === a && (a = g.opts.imageMultipleStyles), !d) return !1;
                var i = he();
                if (!a) {
                    var r = Object.keys(t);
                    r.splice(r.indexOf(e), 1), i.removeClass(r.join(" "))
                }
                "object" == typeof t[e] ? (i.removeAttr("style"), i.css(t[e].style)) : i.toggleClass(e), k(d)
            },
            showAltPopup: Y,
            showSizePopup: G,
            setAlt: function (e) {
                if (d) {
                    var t = g.popups.get("image.alt");
                    d.attr("alt", e || t.find("input").val() || ""), t.find("input:focus").blur(), k(d)
                }
            },
            setSize: function (e, t) {
                if (d) {
                    var a = g.popups.get("image.size");
                    e = e || a.find('input[name="width"]').val() || "", t = t || a.find('input[name="height"]').val() || "";
                    var i = /^[\d]+((px)|%)*$/g;
                    d.removeAttr("width").removeAttr("height"), e.match(i) ? d.css("width", e) : d.css("width", ""), t.match(i) ? d.css("height", t) : d.css("height", ""), ve() && (d.parents(".fr-img-caption").removeAttr("width").removeAttr("height"), e.match(i) ? d.parents(".fr-img-caption").css("width", e) : d.parents(".fr-img-caption").css("width", ""), t.match(i) ? d.parents(".fr-img-caption").css("height", t) : d.parents(".fr-img-caption").css("height", "")), a && a.find("input:focus").blur(), k(d)
                }
            },
            toggleCaption: function () {
                var e;
                if (d && !ve()) {
                    (e = d).parent().is("a") && (e = d.parent());
                    var t = e.width();
                    e.wrap("<span " + (g.browser.mozilla ? "" : 'contenteditable="false"') + 'class="fr-img-caption ' + d.attr("class") + '" style="' + (g.opts.useClasses ? "" : e.attr("style")) + '" draggable="false"></span>'), e.wrap('<span class="fr-img-wrap"></span>'), e.after('<span class="fr-inner"' + (g.browser.mozilla ? "" : ' contenteditable="true"') + ">" + be.FE.START_MARKER + "Image caption" + be.FE.END_MARKER + "</span>"), d.removeAttr("class").removeAttr("style").removeAttr("width"), d.parents(".fr-img-caption").css("width", t + "px"), se(!0), g.selection.restore()
                } else e = he(), d.insertAfter(e), d.attr("class", e.attr("class").replace("fr-img-caption", "")).attr("style", e.attr("style")), e.remove(), k(d)
            },
            hasCaption: ve,
            exitEdit: se,
            edit: k
        }
    }, be.FE.DefineIcon("insertImage", {NAME: "image"}), be.FE.RegisterShortcut(be.FE.KEYCODE.P, "insertImage", null, "P"), be.FE.RegisterCommand("insertImage", {
        title: "Insert Image",
        undo: !1,
        focus: !0,
        refreshAfterCallback: !1,
        popup: !0,
        callback: function () {
            this.popups.isVisible("image.insert") ? (this.$el.find(".fr-marker").length && (this.events.disableBlur(), this.selection.restore()), this.popups.hide("image.insert")) : this.image.showInsertPopup()
        },
        plugin: "image"
    }), be.FE.DefineIcon("imageUpload", {NAME: "upload"}), be.FE.RegisterCommand("imageUpload", {
        title: "Upload Image",
        undo: !1,
        focus: !1,
        toggle: !0,
        callback: function () {
            this.image.showLayer("image-upload")
        },
        refresh: function (e) {
            this.image.refreshUploadButton(e)
        }
    }), be.FE.DefineIcon("imageByURL", {NAME: "link"}), be.FE.RegisterCommand("imageByURL", {
        title: "By URL",
        undo: !1,
        focus: !1,
        toggle: !0,
        callback: function () {
            this.image.showLayer("image-by-url")
        },
        refresh: function (e) {
            this.image.refreshByURLButton(e)
        }
    }), be.FE.RegisterCommand("imageInsertByURL", {
        title: "Insert Image",
        undo: !0,
        refreshAfterCallback: !1,
        callback: function () {
            this.image.insertByURL()
        },
        refresh: function (e) {
            this.image.get() ? e.text(this.language.translate("Replace")) : e.text(this.language.translate("Insert"))
        }
    }), be.FE.DefineIcon("imageDisplay", {NAME: "star"}), be.FE.RegisterCommand("imageDisplay", {
        title: "Display",
        type: "dropdown",
        options: {inline: "Inline", block: "Break Text"},
        callback: function (e, t) {
            this.image.display(t)
        },
        refresh: function (e) {
            this.opts.imageTextNear || e.addClass("fr-hidden")
        },
        refreshOnShow: function (e, t) {
            this.image.refreshDisplayOnShow(e, t)
        }
    }), be.FE.DefineIcon("image-align", {NAME: "align-left"}), be.FE.DefineIcon("image-align-left", {NAME: "align-left"}), be.FE.DefineIcon("image-align-right", {NAME: "align-right"}), be.FE.DefineIcon("image-align-center", {NAME: "align-justify"}), be.FE.DefineIcon("imageAlign", {NAME: "align-justify"}), be.FE.RegisterCommand("imageAlign", {
        type: "dropdown",
        title: "Align",
        options: {left: "Align Left", center: "None", right: "Align Right"},
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', t = be.FE.COMMANDS.imageAlign.options;
            for (var a in t) t.hasOwnProperty(a) && (e += '<li role="presentation"><a class="fr-command fr-title" tabIndex="-1" role="option" data-cmd="imageAlign" data-param1="' + a + '" title="' + this.language.translate(t[a]) + '">' + this.icon.create("image-align-" + a) + '<span class="fr-sr-only">' + this.language.translate(t[a]) + "</span></a></li>");
            return e += "</ul>"
        },
        callback: function (e, t) {
            this.image.align(t)
        },
        refresh: function (e) {
            this.image.refreshAlign(e)
        },
        refreshOnShow: function (e, t) {
            this.image.refreshAlignOnShow(e, t)
        }
    }), be.FE.DefineIcon("imageReplace", {
        NAME: "exchange",
        FA5NAME: "exchange-alt"
    }), be.FE.RegisterCommand("imageReplace", {
        title: "Replace",
        undo: !1,
        focus: !1,
        popup: !0,
        refreshAfterCallback: !1,
        callback: function () {
            this.image.replace()
        }
    }), be.FE.DefineIcon("imageRemove", {NAME: "trash"}), be.FE.RegisterCommand("imageRemove", {
        title: "Remove",
        callback: function () {
            this.image.remove()
        }
    }), be.FE.DefineIcon("imageBack", {NAME: "arrow-left"}), be.FE.RegisterCommand("imageBack", {
        title: "Back",
        undo: !1,
        focus: !1,
        back: !0,
        callback: function () {
            this.image.back()
        },
        refresh: function (e) {
            this.image.get() || this.opts.toolbarInline ? (e.removeClass("fr-hidden"), e.next(".fr-separator").removeClass("fr-hidden")) : (e.addClass("fr-hidden"), e.next(".fr-separator").addClass("fr-hidden"))
        }
    }), be.FE.RegisterCommand("imageDismissError", {
        title: "OK", undo: !1, callback: function () {
            this.image.hideProgressBar(!0)
        }
    }), be.FE.DefineIcon("imageStyle", {NAME: "magic"}), be.FE.RegisterCommand("imageStyle", {
        title: "Style",
        type: "dropdown",
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', t = this.opts.imageStyles;
            for (var a in t) if (t.hasOwnProperty(a)) {
                var i = t[a];
                "object" == typeof i && (i = i.title), e += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="imageStyle" data-param1="' + a + '">' + this.language.translate(i) + "</a></li>"
            }
            return e += "</ul>"
        },
        callback: function (e, t) {
            this.image.applyStyle(t)
        },
        refreshOnShow: function (e, t) {
            var a = this.image.getEl();
            a && t.find(".fr-command").each(function () {
                var e = be(this).data("param1"), t = a.hasClass(e);
                be(this).toggleClass("fr-active", t).attr("aria-selected", t)
            })
        }
    }), be.FE.DefineIcon("imageAlt", {NAME: "info"}), be.FE.RegisterCommand("imageAlt", {
        undo: !1,
        focus: !1,
        popup: !0,
        title: "Alternative Text",
        callback: function () {
            this.image.showAltPopup()
        }
    }), be.FE.RegisterCommand("imageSetAlt", {
        undo: !0,
        focus: !1,
        title: "Update",
        refreshAfterCallback: !1,
        callback: function () {
            this.image.setAlt()
        }
    }), be.FE.DefineIcon("imageSize", {NAME: "arrows-alt"}), be.FE.RegisterCommand("imageSize", {
        undo: !1,
        focus: !1,
        popup: !0,
        title: "Change Size",
        callback: function () {
            this.image.showSizePopup()
        }
    }), be.FE.RegisterCommand("imageSetSize", {
        undo: !0,
        focus: !1,
        title: "Update",
        refreshAfterCallback: !1,
        callback: function () {
            this.image.setSize()
        }
    }), be.FE.DefineIcon("imageCaption", {
        NAME: "commenting",
        FA5NAME: "comment-alt"
    }), be.FE.RegisterCommand("imageCaption", {
        undo: !0,
        focus: !1,
        title: "Image Caption",
        refreshAfterCallback: !0,
        callback: function () {
            this.image.toggleCaption()
        },
        refresh: function (e) {
            this.image.get() && e.toggleClass("fr-active", this.image.hasCaption())
        }
    })
});
!function (t) {
    "function" == typeof define && define.amd ? define(["jquery"], t) : "object" == typeof module && module.exports ? module.exports = function (e, n) {
        return n === undefined && (n = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), t(n)
    } : t(window.jQuery)
}(function (o) {
    o.extend(o.FE.DEFAULTS, {
        inlineStyles: {
            "Big Red": "font-size: 20px; color: red;",
            "Small Blue": "font-size: 14px; color: blue;"
        }
    }), o.FE.PLUGINS.inlineStyle = function (l) {
        return {
            apply: function (e) {
                if ("" !== l.selection.text()) for (var n = e.split(";"), t = 0; t < n.length; t++) {
                    var i = n[t].split(":");
                    n[t].length && 2 == i.length && l.format.applyStyle(i[0].trim(), i[1].trim())
                } else l.html.insert('<span style="' + e + '">' + o.FE.INVISIBLE_SPACE + o.FE.MARKERS + "</span>")
            }
        }
    }, o.FE.RegisterCommand("inlineStyle", {
        type: "dropdown", html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', n = this.opts.inlineStyles;
            for (var t in n) {
                if (n.hasOwnProperty(t)) e += '<li role="presentation"><span style="' + (n[t] + (-1 === n[t].indexOf("display:block;") ? " display:block;" : "")) + '" role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="inlineStyle" data-param1="' + n[t] + '" title="' + this.language.translate(t) + '">' + this.language.translate(t) + "</a></span></li>"
            }
            return e += "</ul>"
        }, title: "Inline Style", callback: function (e, n) {
            this.inlineStyle.apply(n)
        }, plugin: "inlineStyle"
    }), o.FE.DefineIcon("inlineStyle", {NAME: "paint-brush"})
});
!function (i) {
    "function" == typeof define && define.amd ? define(["jquery"], i) : "object" == typeof module && module.exports ? module.exports = function (n, e) {
        return e === undefined && (e = "undefined" != typeof window ? require("jquery") : require("jquery")(n)), i(e)
    } : i(window.jQuery)
}(function (s) {
    s.extend(s.FE.DEFAULTS, {
        inlineClasses: {
            "fr-class-code": "Code",
            "fr-class-highlighted": "Highlighted",
            "fr-class-transparency": "Transparent"
        }
    }), s.FE.PLUGINS.inlineClass = function (i) {
        return {
            apply: function (n) {
                i.format.toggle("span", {"class": n})
            }, refreshOnShow: function (n, e) {
                e.find(".fr-command").each(function () {
                    var n = s(this).data("param1"), e = i.format.is("span", {"class": n});
                    s(this).toggleClass("fr-active", e).attr("aria-selected", e)
                })
            }
        }
    }, s.FE.RegisterCommand("inlineClass", {
        type: "dropdown", title: "Inline Class", html: function () {
            var n = '<ul class="fr-dropdown-list" role="presentation">', e = this.opts.inlineClasses;
            for (var i in e) e.hasOwnProperty(i) && (n += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="inlineClass" data-param1="' + i + '" title="' + e[i] + '">' + e[i] + "</a></li>");
            return n += "</ul>"
        }, callback: function (n, e) {
            this.inlineClass.apply(e)
        }, refreshOnShow: function (n, e) {
            this.inlineClass.refreshOnShow(n, e)
        }, plugin: "inlineClass"
    }), s.FE.DefineIcon("inlineClass", {NAME: "tag"})
});
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = function (root, jQuery) {
            if (jQuery === undefined) {
                if (typeof window !== 'undefined') {
                    jQuery = require('jquery');
                } else {
                    jQuery = require('jquery')(root);
                }
            }
            return factory(jQuery);
        };
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    $.extend($.FE.POPUP_TEMPLATES, {'link.edit': '[_BUTTONS_]', 'link.insert': '[_BUTTONS_][_INPUT_LAYER_]'})
    $.extend($.FE.DEFAULTS, {
        linkEditButtons: ['linkOpen', 'linkStyle', 'linkEdit', 'linkRemove'],
        linkInsertButtons: ['linkBack', '|', 'linkList'],
        linkAttributes: {},
        linkAutoPrefix: 'http://',
        linkStyles: {'fr-green': 'Green', 'fr-strong': 'Thick'},
        linkMultipleStyles: true,
        linkConvertEmailAddress: true,
        linkAlwaysBlank: false,
        linkAlwaysNoFollow: false,
        linkNoOpener: true,
        linkNoReferrer: true,
        linkList: [{text: 'Froala', href: 'https://froala.com', target: '_blank'}, {
            text: 'Google',
            href: 'https://google.com',
            target: '_blank'
        }, {displayText: 'Facebook', href: 'https://facebook.com'}],
        linkText: true
    });
    $.FE.PLUGINS.link = function (editor) {
        function get() {
            var $current_image = editor.image ? editor.image.get() : null;
            if (!$current_image && editor.$wp) {
                var c_el = editor.selection.ranges(0).commonAncestorContainer;
                try {
                    if (c_el && ((c_el.contains && c_el.contains(editor.el)) || !editor.el.contains(c_el) || editor.el == c_el)) c_el = null;
                } catch (ex) {
                    c_el = null;
                }
                if (c_el && c_el.tagName === 'A') return c_el;
                var s_el = editor.selection.element();
                var e_el = editor.selection.endElement();
                if (s_el.tagName != 'A' && !editor.node.isElement(s_el)) {
                    s_el = $(s_el).parentsUntil(editor.$el, 'a:first').get(0);
                }
                if (e_el.tagName != 'A' && !editor.node.isElement(e_el)) {
                    e_el = $(e_el).parentsUntil(editor.$el, 'a:first').get(0);
                }
                try {
                    if (e_el && ((e_el.contains && e_el.contains(editor.el)) || !editor.el.contains(e_el) || editor.el == e_el)) e_el = null;
                } catch (ex) {
                    e_el = null;
                }
                try {
                    if (s_el && ((s_el.contains && s_el.contains(editor.el)) || !editor.el.contains(s_el) || editor.el == s_el)) s_el = null;
                } catch (ex) {
                    s_el = null;
                }
                if (e_el && e_el == s_el && e_el.tagName == 'A') {
                    if ((editor.browser.msie || editor.helpers.isMobile()) && (editor.selection.info(s_el).atEnd || editor.selection.info(s_el).atStart)) {
                        return null;
                    }
                    return s_el;
                }
                return null;
            } else if (editor.el.tagName == 'A') {
                return editor.el;
            } else {
                if ($current_image && $current_image.get(0).parentNode && $current_image.get(0).parentNode.tagName == 'A') {
                    return $current_image.get(0).parentNode;
                }
            }
        }

        function allSelected() {
            var $current_image = editor.image ? editor.image.get() : null;
            var selectedLinks = [];
            if ($current_image) {
                if ($current_image.get(0).parentNode.tagName == 'A') {
                    selectedLinks.push($current_image.get(0).parentNode);
                }
            } else {
                var range;
                var containerEl;
                var links;
                var linkRange;
                if (editor.win.getSelection) {
                    var sel = editor.win.getSelection();
                    if (sel.getRangeAt && sel.rangeCount) {
                        linkRange = editor.doc.createRange();
                        for (var r = 0; r < sel.rangeCount; ++r) {
                            range = sel.getRangeAt(r);
                            containerEl = range.commonAncestorContainer;
                            if (containerEl && containerEl.nodeType != 1) {
                                containerEl = containerEl.parentNode;
                            }
                            if (containerEl && containerEl.nodeName.toLowerCase() == 'a') {
                                selectedLinks.push(containerEl);
                            } else {
                                links = containerEl.getElementsByTagName('a');
                                for (var i = 0; i < links.length; ++i) {
                                    linkRange.selectNodeContents(links[i]);
                                    if (linkRange.compareBoundaryPoints(range.END_TO_START, range) < 1 && linkRange.compareBoundaryPoints(range.START_TO_END, range) > -1) {
                                        selectedLinks.push(links[i]);
                                    }
                                }
                            }
                        }
                    }
                } else if (editor.doc.selection && editor.doc.selection.type != 'Control') {
                    range = editor.doc.selection.createRange();
                    containerEl = range.parentElement();
                    if (containerEl.nodeName.toLowerCase() == 'a') {
                        selectedLinks.push(containerEl);
                    } else {
                        links = containerEl.getElementsByTagName('a');
                        linkRange = editor.doc.body.createTextRange();
                        for (var j = 0; j < links.length; ++j) {
                            linkRange.moveToElementText(links[j]);
                            if (linkRange.compareEndPoints('StartToEnd', range) > -1 && linkRange.compareEndPoints('EndToStart', range) < 1) {
                                selectedLinks.push(links[j]);
                            }
                        }
                    }
                }
            }
            return selectedLinks;
        }

        function _edit(e) {
            if (editor.core.hasFocus()) {
                _hideEditPopup();
                if (e && e.type === 'keyup' && (e.altKey || e.which == $.FE.KEYCODE.ALT)) return true;
                setTimeout(function () {
                    if (!e || (e && (e.which == 1 || e.type != 'mouseup'))) {
                        var link = get();
                        var $current_image = editor.image ? editor.image.get() : null;
                        if (link && !$current_image) {
                            if (editor.image) {
                                var contents = editor.node.contents(link);
                                if (contents.length == 1 && contents[0].tagName == 'IMG') {
                                    var range = editor.selection.ranges(0);
                                    if (range.startOffset === 0 && range.endOffset === 0) {
                                        $(link).before($.FE.MARKERS);
                                    } else {
                                        $(link).after($.FE.MARKERS);
                                    }
                                    editor.selection.restore();
                                    return false;
                                }
                            }
                            if (e) {
                                e.stopPropagation();
                            }
                            _showEditPopup(link);
                        }
                    }
                }, editor.helpers.isIOS() ? 100 : 0);
            }
        }

        function _showEditPopup(link) {
            var $popup = editor.popups.get('link.edit');
            if (!$popup) $popup = _initEditPopup();
            var $link = $(link);
            if (!editor.popups.isVisible('link.edit')) {
                editor.popups.refresh('link.edit');
            }
            editor.popups.setContainer('link.edit', editor.$sc);
            var left = $link.offset().left + $(link).outerWidth() / 2;
            var top = $link.offset().top + $link.outerHeight();
            editor.popups.show('link.edit', left, top, $link.outerHeight());
        }

        function _hideEditPopup() {
            editor.popups.hide('link.edit');
        }

        function _initEditPopup() {
            var link_buttons = '';
            if (editor.opts.linkEditButtons.length >= 1) {
                if (editor.el.tagName == 'A' && editor.opts.linkEditButtons.indexOf('linkRemove') >= 0) {
                    editor.opts.linkEditButtons.splice(editor.opts.linkEditButtons.indexOf('linkRemove'), 1);
                }
                link_buttons = '<div class="fr-buttons">' + editor.button.buildList(editor.opts.linkEditButtons) + '</div>';
            }
            var template = {buttons: link_buttons};
            var $popup = editor.popups.create('link.edit', template);
            if (editor.$wp) {
                editor.events.$on(editor.$wp, 'scroll.link-edit', function () {
                    if (get() && editor.popups.isVisible('link.edit')) {
                        _showEditPopup(get());
                    }
                });
            }
            return $popup;
        }

        function _hideInsertPopup() {
        }

        function _refreshInsertPopup() {
            var $popup = editor.popups.get('link.insert');
            var link = get();
            if (link) {
                var $link = $(link);
                var text_inputs = $popup.find('input.fr-link-attr[type="text"]');
                var check_inputs = $popup.find('input.fr-link-attr[type="checkbox"]');
                var i;
                var $input;
                for (i = 0; i < text_inputs.length; i++) {
                    $input = $(text_inputs[i]);
                    $input.val($link.attr($input.attr('name') || ''));
                }
                check_inputs.prop('checked', false);
                for (i = 0; i < check_inputs.length; i++) {
                    $input = $(check_inputs[i]);
                    if ($link.attr($input.attr('name')) == $input.data('checked')) {
                        $input.prop('checked', true);
                    }
                }
                $popup.find('input.fr-link-attr[type="text"][name="text"]').val($link.text());
            } else {
                $popup.find('input.fr-link-attr[type="text"]').val('');
                $popup.find('input.fr-link-attr[type="checkbox"]').prop('checked', false);
                $popup.find('input.fr-link-attr[type="text"][name="text"]').val(editor.selection.text());
            }
            $popup.find('input.fr-link-attr').trigger('change');
            var $current_image = editor.image ? editor.image.get() : null;
            if ($current_image) {
                $popup.find('.fr-link-attr[name="text"]').parent().hide();
            } else {
                $popup.find('.fr-link-attr[name="text"]').parent().show();
            }
        }

        function _showInsertPopup() {
            var $btn = editor.$tb.find('.fr-command[data-cmd="insertLink"]');
            var $popup = editor.popups.get('link.insert');
            if (!$popup) $popup = _initInsertPopup();
            if (!$popup.hasClass('fr-active')) {
                editor.popups.refresh('link.insert');
                editor.popups.setContainer('link.insert', editor.$tb || editor.$sc);
                if ($btn.is(':visible')) {
                    var left = $btn.offset().left + $btn.outerWidth() / 2;
                    var top = $btn.offset().top + (editor.opts.toolbarBottom ? 10 : $btn.outerHeight() - 10);
                    editor.popups.show('link.insert', left, top, $btn.outerHeight());
                } else {
                    editor.position.forSelection($popup);
                    editor.popups.show('link.insert');
                }
            }
        }

        function _initInsertPopup(delayed) {
            if (delayed) {
                editor.popups.onRefresh('link.insert', _refreshInsertPopup);
                editor.popups.onHide('link.insert', _hideInsertPopup);
                return true;
            }
            var link_buttons = '';
            if (editor.opts.linkInsertButtons.length >= 1) {
                link_buttons = '<div class="fr-buttons">' + editor.button.buildList(editor.opts.linkInsertButtons) + '</div>';
            }
            var checkmark = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="10" height="10" viewBox="0 0 32 32"><path d="M27 4l-15 15-7-7-5 5 12 12 20-20z" fill="#FFF"></path></svg>';
            var input_layer = '';
            var tab_idx = 0;
            input_layer = '<div class="fr-link-insert-layer fr-layer fr-active" id="fr-link-insert-layer-' + editor.id + '">';
            input_layer += '<div class="fr-input-line"><input id="fr-link-insert-layer-url-' + editor.id + '" name="href" type="text" class="fr-link-attr" placeholder="' + editor.language.translate('URL') + '" tabIndex="' + (++tab_idx) + '"></div>';
            if (editor.opts.linkText) {
                input_layer += '<div class="fr-input-line"><input id="fr-link-insert-layer-text-' + editor.id + '" name="text" type="text" class="fr-link-attr" placeholder="' + editor.language.translate('Text') + '" tabIndex="' + (++tab_idx) + '"></div>';
            }
            for (var attr in editor.opts.linkAttributes) {
                if (editor.opts.linkAttributes.hasOwnProperty(attr)) {
                    var placeholder = editor.opts.linkAttributes[attr];
                    input_layer += '<div class="fr-input-line"><input name="' + attr + '" type="text" class="fr-link-attr" placeholder="' + editor.language.translate(placeholder) + '" tabIndex="' + (++tab_idx) + '"></div>';
                }
            }
            if (!editor.opts.linkAlwaysBlank) {
                input_layer += '<div class="fr-checkbox-line"><span class="fr-checkbox"><input name="target" class="fr-link-attr" data-checked="_blank" type="checkbox" id="fr-link-target-' + editor.id + '" tabIndex="' + (++tab_idx) + '"><span>' + checkmark + '</span></span><label for="fr-link-target-' + editor.id + '">' + editor.language.translate('Open in new tab') + '</label></div>';
            }
            input_layer += '<div class="fr-action-buttons"><button class="fr-command fr-submit" role="button" data-cmd="linkInsert" href="#" tabIndex="' + (++tab_idx) + '" type="button">' + editor.language.translate('Insert') + '</button></div></div>'
            var template = {buttons: link_buttons, input_layer: input_layer}
            var $popup = editor.popups.create('link.insert', template);
            if (editor.$wp) {
                editor.events.$on(editor.$wp, 'scroll.link-insert', function () {
                    var $current_image = editor.image ? editor.image.get() : null;
                    if ($current_image && editor.popups.isVisible('link.insert')) {
                        imageLink();
                    }
                    if (get && editor.popups.isVisible('link.insert')) {
                        update();
                    }
                });
            }
            return $popup;
        }

        function remove() {
            var link = get();
            var $current_image = editor.image ? editor.image.get() : null;
            if (editor.events.trigger('link.beforeRemove', [link]) === false) return false;
            if ($current_image && link) {
                $current_image.unwrap();
                editor.image.edit($current_image);
            } else if (link) {
                editor.selection.save();
                $(link).replaceWith($(link).html());
                editor.selection.restore();
                _hideEditPopup();
            }
        }

        function _init() {
            editor.events.on('keyup', function (e) {
                if (e.which != $.FE.KEYCODE.ESC) {
                    _edit(e);
                }
            });
            editor.events.on('window.mouseup', _edit);
            editor.events.$on(editor.$el, 'click', 'a', function (e) {
                if (editor.edit.isDisabled()) {
                    e.preventDefault();
                }
            });
            if (editor.helpers.isMobile()) {
                editor.events.$on(editor.$doc, 'selectionchange', _edit);
            }
            _initInsertPopup(true);
            if (editor.el.tagName == 'A') {
                editor.$el.addClass('fr-view');
            }
            editor.events.on('toolbar.esc', function () {
                if (editor.popups.isVisible('link.edit')) {
                    editor.events.disableBlur();
                    editor.events.focus();
                    return false;
                }
            }, true);
        }

        function usePredefined(val) {
            var link = editor.opts.linkList[val];
            var $popup = editor.popups.get('link.insert');
            var text_inputs = $popup.find('input.fr-link-attr[type="text"]');
            var check_inputs = $popup.find('input.fr-link-attr[type="checkbox"]');
            var $input;
            var i;
            for (i = 0; i < text_inputs.length; i++) {
                $input = $(text_inputs[i]);
                if (link[$input.attr('name')]) {
                    $input.val(link[$input.attr('name')]);
                } else if ($input.attr('name') != 'text') {
                    $input.val('');
                }
            }
            for (i = 0; i < check_inputs.length; i++) {
                $input = $(check_inputs[i]);
                $input.prop('checked', $input.data('checked') == link[$input.attr('name')]);
            }
            editor.accessibility.focusPopup($popup);
        }

        function insertCallback() {
            var $popup = editor.popups.get('link.insert');
            var text_inputs = $popup.find('input.fr-link-attr[type="text"]');
            var check_inputs = $popup.find('input.fr-link-attr[type="checkbox"]');
            var href = (text_inputs.filter('[name="href"]').val() || '').trim();
            var text = text_inputs.filter('[name="text"]').val();
            var attrs = {};
            var $input;
            var i;
            for (i = 0; i < text_inputs.length; i++) {
                $input = $(text_inputs[i]);
                if (['href', 'text'].indexOf($input.attr('name')) < 0) {
                    attrs[$input.attr('name')] = $input.val();
                }
            }
            for (i = 0; i < check_inputs.length; i++) {
                $input = $(check_inputs[i]);
                if ($input.is(':checked')) {
                    attrs[$input.attr('name')] = $input.data('checked');
                } else {
                    attrs[$input.attr('name')] = $input.data('unchecked') || null;
                }
            }
            var t = editor.helpers.scrollTop();
            insert(href, text, attrs);
            $(editor.o_win).scrollTop(t);
        }

        function _split() {
            if (!editor.selection.isCollapsed()) {
                editor.selection.save();
                var markers = editor.$el.find('.fr-marker').addClass('fr-unprocessed').toArray();
                while (markers.length) {
                    var $marker = $(markers.pop());
                    $marker.removeClass('fr-unprocessed');
                    var deep_parent = editor.node.deepestParent($marker.get(0));
                    if (deep_parent) {
                        var node = $marker.get(0);
                        var close_str = '';
                        var open_str = '';
                        do {
                            node = node.parentNode;
                            if (!editor.node.isBlock(node)) {
                                close_str = close_str + editor.node.closeTagString(node);
                                open_str = editor.node.openTagString(node) + open_str;
                            }
                        } while (node != deep_parent);
                        var marker_str = editor.node.openTagString($marker.get(0)) + $marker.html() + editor.node.closeTagString($marker.get(0));
                        $marker.replaceWith('<span id="fr-break"></span>');
                        var h = deep_parent.outerHTML;
                        h = h.replace(/<span id="fr-break"><\/span>/g, close_str + marker_str + open_str);
                        h = h.replace(open_str + close_str, '');
                        deep_parent.outerHTML = h;
                    }
                    markers = editor.$el.find('.fr-marker.fr-unprocessed').toArray();
                }
                editor.html.cleanEmptyTags();
                editor.selection.restore();
            }
        }

        function insert(href, text, attrs) {
            if (typeof attrs == 'undefined') attrs = {};
            if (editor.events.trigger('link.beforeInsert', [href, text, attrs]) === false) return false;
            var $current_image = editor.image ? editor.image.get() : null;
            if (!$current_image && editor.el.tagName != 'A') {
                editor.selection.restore();
                editor.popups.hide('link.insert');
            } else if (editor.el.tagName == 'A') {
                editor.$el.focus();
            }
            var original_href = href;
            if (editor.opts.linkConvertEmailAddress) {
                if (editor.helpers.isEmail(href) && !/^mailto:.*/i.test(href)) {
                    href = 'mailto:' + href;
                }
            }
            var local_path = /^([A-Za-z]:(\\){1,2}|[A-Za-z]:((\\){1,2}[^\\]+)+)(\\)?$/i;
            if (editor.opts.linkAutoPrefix !== '' && !new RegExp('^(' + $.FE.LinkProtocols.join('|') + '):.', 'i').test(href) && !/^data:image.*/i.test(href) && !/^(https?:|ftps?:|file:|)\/\//i.test(href) && !local_path.test(href)) {
                if (['/', '{', '[', '#', '(', '.'].indexOf((href || '')[0]) < 0) {
                    href = editor.opts.linkAutoPrefix + editor.helpers.sanitizeURL(href);
                }
            }
            href = editor.helpers.sanitizeURL(href);
            if (editor.opts.linkAlwaysBlank) attrs.target = '_blank';
            if (editor.opts.linkAlwaysNoFollow) attrs.rel = 'nofollow';
            if (editor.helpers.isEmail(original_href)) {
                attrs.target = null;
                attrs.rel = null;
            }
            if (attrs.target == '_blank') {
                if (editor.opts.linkNoOpener) {
                    if (!attrs.rel) attrs.rel = 'noopener'; else attrs.rel += ' noopener';
                }
                if (editor.opts.linkNoReferrer) {
                    if (!attrs.rel) attrs.rel = 'noreferrer'; else attrs.rel += ' noreferrer';
                }
            } else if (attrs.target == null) {
                if (attrs.rel) {
                    attrs.rel = attrs.rel.replace(/noopener/, '').replace(/noreferrer/, '');
                } else {
                    attrs.rel = null;
                }
            }
            text = text || '';
            if (href === editor.opts.linkAutoPrefix) {
                var $popup = editor.popups.get('link.insert');
                $popup.find('input[name="href"]').addClass('fr-error');
                editor.events.trigger('link.bad', [original_href]);
                return false;
            }
            var link = get();
            var $link;
            if (link) {
                $link = $(link);
                $link.attr('href', href);
                if (text.length > 0 && $link.text() != text && !$current_image) {
                    var child = $link.get(0);
                    while (child.childNodes.length === 1 && child.childNodes[0].nodeType == Node.ELEMENT_NODE) {
                        child = child.childNodes[0];
                    }
                    $(child).text(text);
                }
                if (!$current_image) {
                    $link.prepend($.FE.START_MARKER).append($.FE.END_MARKER);
                }
                $link.attr(attrs);
                if (!$current_image) {
                    editor.selection.restore();
                }
            } else {
                if (!$current_image) {
                    editor.format.remove('a');
                    if (editor.selection.isCollapsed()) {
                        text = (text.length === 0 ? original_href : text);
                        editor.html.insert('<a href="' + href + '">' + $.FE.START_MARKER + text.replace(/&/g, '&amp;').replace(/</, '&lt;', '>', '&gt;') + $.FE.END_MARKER + '</a>');
                        editor.selection.restore();
                    } else {
                        if (text.length > 0 && text != editor.selection.text().replace(/\n/g, '')) {
                            editor.selection.remove();
                            editor.html.insert('<a href="' + href + '">' + $.FE.START_MARKER + text.replace(/&/g, '&amp;') + $.FE.END_MARKER + '</a>');
                            editor.selection.restore();
                        } else {
                            _split();
                            editor.format.apply('a', {href: href});
                        }
                    }
                } else {
                    $current_image.wrap('<a href="' + href + '"></a>');
                }
                var links = allSelected();
                for (var i = 0; i < links.length; i++) {
                    $link = $(links[i]);
                    $link.attr(attrs);
                    $link.removeAttr('_moz_dirty');
                }
                if (links.length == 1 && editor.$wp && !$current_image) {
                    $(links[0]).prepend($.FE.START_MARKER).append($.FE.END_MARKER);
                    editor.selection.restore();
                }
            }
            if (!$current_image) {
                _edit();
            } else {
                var $pop = editor.popups.get('link.insert');
                if ($pop) {
                    $pop.find('input:focus').blur();
                }
                editor.image.edit($current_image);
            }
        }

        function update() {
            _hideEditPopup();
            var link = get();
            if (link) {
                var $popup = editor.popups.get('link.insert');
                if (!$popup) $popup = _initInsertPopup();
                if (!editor.popups.isVisible('link.insert')) {
                    editor.popups.refresh('link.insert');
                    editor.selection.save();
                    if (editor.helpers.isMobile()) {
                        editor.events.disableBlur();
                        editor.$el.blur();
                        editor.events.enableBlur();
                    }
                }
                editor.popups.setContainer('link.insert', editor.$sc);
                var $ref = (editor.image ? editor.image.get() : null) || $(link);
                var left = $ref.offset().left + $ref.outerWidth() / 2;
                var top = $ref.offset().top + $ref.outerHeight();
                editor.popups.show('link.insert', left, top, $ref.outerHeight());
            }
        }

        function back() {
            var $current_image = editor.image ? editor.image.get() : null;
            if (!$current_image) {
                editor.events.disableBlur();
                editor.selection.restore();
                editor.events.enableBlur();
                var link = get();
                if (link && editor.$wp) {
                    editor.selection.restore();
                    _hideEditPopup();
                    _edit();
                } else if (editor.el.tagName == 'A') {
                    editor.$el.focus();
                    _edit();
                } else {
                    editor.popups.hide('link.insert');
                    editor.toolbar.showInline();
                }
            } else {
                editor.image.back();
            }
        }

        function imageLink() {
            var $el = editor.image ? editor.image.getEl() : null;
            if ($el) {
                var $popup = editor.popups.get('link.insert');
                if (editor.image.hasCaption()) {
                    $el = $el.find('.fr-img-wrap');
                }
                if (!$popup) $popup = _initInsertPopup();
                _refreshInsertPopup(true);
                editor.popups.setContainer('link.insert', editor.$sc);
                var left = $el.offset().left + $el.outerWidth() / 2;
                var top = $el.offset().top + $el.outerHeight();
                editor.popups.show('link.insert', left, top, $el.outerHeight());
            }
        }

        function applyStyle(val, linkStyles, multipleStyles) {
            if (typeof multipleStyles == 'undefined') multipleStyles = editor.opts.linkMultipleStyles;
            if (typeof linkStyles == 'undefined') linkStyles = editor.opts.linkStyles;
            var link = get();
            if (!link) return false;
            if (!multipleStyles) {
                var styles = Object.keys(linkStyles);
                styles.splice(styles.indexOf(val), 1);
                $(link).removeClass(styles.join(' '));
            }
            $(link).toggleClass(val);
            _edit();
        }

        return {
            _init: _init,
            remove: remove,
            showInsertPopup: _showInsertPopup,
            usePredefined: usePredefined,
            insertCallback: insertCallback,
            insert: insert,
            update: update,
            get: get,
            allSelected: allSelected,
            back: back,
            imageLink: imageLink,
            applyStyle: applyStyle
        }
    }
    $.FE.DefineIcon('insertLink', {NAME: 'link'});
    $.FE.RegisterShortcut($.FE.KEYCODE.K, 'insertLink', null, 'K');
    $.FE.RegisterCommand('insertLink', {
        title: 'Insert Link', undo: false, focus: true, refreshOnCallback: false, popup: true, callback: function () {
            if (!this.popups.isVisible('link.insert')) {
                this.link.showInsertPopup();
            } else {
                if (this.$el.find('.fr-marker').length) {
                    this.events.disableBlur();
                    this.selection.restore();
                }
                this.popups.hide('link.insert');
            }
        }, plugin: 'link'
    })
    $.FE.DefineIcon('linkOpen', {NAME: 'external-link', FA5NAME: 'external-link-alt'});
    $.FE.RegisterCommand('linkOpen', {
        title: 'Open Link', undo: false, refresh: function ($btn) {
            var link = this.link.get();
            if (link) {
                $btn.removeClass('fr-hidden');
            } else {
                $btn.addClass('fr-hidden');
            }
        }, callback: function () {
            var link = this.link.get();
            if (link) {
                if (link.href.indexOf('mailto:') !== -1) {
                    this.o_win.open(link.href).close();
                } else {
                    this.o_win.open(link.href, '_blank', 'noopener');
                }
                this.popups.hide('link.edit');
            }
        }, plugin: 'link'
    })
    $.FE.DefineIcon('linkEdit', {NAME: 'edit'});
    $.FE.RegisterCommand('linkEdit', {
        title: 'Edit Link', undo: false, refreshAfterCallback: false, popup: true, callback: function () {
            this.link.update();
        }, refresh: function ($btn) {
            var link = this.link.get();
            if (link) {
                $btn.removeClass('fr-hidden');
            } else {
                $btn.addClass('fr-hidden');
            }
        }, plugin: 'link'
    })
    $.FE.DefineIcon('linkRemove', {NAME: 'unlink'});
    $.FE.RegisterCommand('linkRemove', {
        title: 'Unlink', callback: function () {
            this.link.remove();
        }, refresh: function ($btn) {
            var link = this.link.get();
            if (link) {
                $btn.removeClass('fr-hidden');
            } else {
                $btn.addClass('fr-hidden');
            }
        }, plugin: 'link'
    })
    $.FE.DefineIcon('linkBack', {NAME: 'arrow-left'});
    $.FE.RegisterCommand('linkBack', {
        title: 'Back', undo: false, focus: false, back: true, refreshAfterCallback: false, callback: function () {
            this.link.back();
        }, refresh: function ($btn) {
            var link = this.link.get() && this.doc.hasFocus();
            var $current_image = this.image ? this.image.get() : null;
            if (!$current_image && !link && !this.opts.toolbarInline) {
                $btn.addClass('fr-hidden');
                $btn.next('.fr-separator').addClass('fr-hidden');
            } else {
                $btn.removeClass('fr-hidden');
                $btn.next('.fr-separator').removeClass('fr-hidden');
            }
        }, plugin: 'link'
    });
    $.FE.DefineIcon('linkList', {NAME: 'search'});
    $.FE.RegisterCommand('linkList', {
        title: 'Choose Link',
        type: 'dropdown',
        focus: false,
        undo: false,
        refreshAfterCallback: false,
        html: function () {
            var c = '<ul class="fr-dropdown-list" role="presentation">';
            var options = this.opts.linkList;
            for (var i = 0; i < options.length; i++) {
                c += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="linkList" data-param1="' + i + '">' + (options[i].displayText || options[i].text) + '</a></li>';
            }
            c += '</ul>';
            return c;
        },
        callback: function (cmd, val) {
            this.link.usePredefined(val);
        },
        plugin: 'link'
    })
    $.FE.RegisterCommand('linkInsert', {
        focus: false, refreshAfterCallback: false, callback: function () {
            this.link.insertCallback();
        }, refresh: function ($btn) {
            var link = this.link.get();
            if (link) {
                $btn.text(this.language.translate('Update'));
            } else {
                $btn.text(this.language.translate('Insert'));
            }
        }, plugin: 'link'
    })
    $.FE.DefineIcon('imageLink', {NAME: 'link'})
    $.FE.RegisterCommand('imageLink', {
        title: 'Insert Link', undo: false, focus: false, popup: true, callback: function () {
            this.link.imageLink();
        }, refresh: function ($btn) {
            var link = this.link.get();
            var $prev;
            if (link) {
                $prev = $btn.prev();
                if ($prev.hasClass('fr-separator')) {
                    $prev.removeClass('fr-hidden');
                }
                $btn.addClass('fr-hidden');
            } else {
                $prev = $btn.prev();
                if ($prev.hasClass('fr-separator')) {
                    $prev.addClass('fr-hidden');
                }
                $btn.removeClass('fr-hidden');
            }
        }, plugin: 'link'
    })
    $.FE.DefineIcon('linkStyle', {NAME: 'magic'})
    $.FE.RegisterCommand('linkStyle', {
        title: 'Style', type: 'dropdown', html: function () {
            var c = '<ul class="fr-dropdown-list" role="presentation">';
            var options = this.opts.linkStyles;
            for (var cls in options) {
                if (options.hasOwnProperty(cls)) {
                    c += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="linkStyle" data-param1="' + cls + '">' + this.language.translate(options[cls]) + '</a></li>';
                }
            }
            c += '</ul>';
            return c;
        }, callback: function (cmd, val) {
            this.link.applyStyle(val);
        }, refreshOnShow: function ($btn, $dropdown) {
            var link = this.link.get();
            if (link) {
                var $link = $(link);
                $dropdown.find('.fr-command').each(function () {
                    var cls = $(this).data('param1');
                    var active = $link.hasClass(cls);
                    $(this).toggleClass('fr-active', active).attr('aria-selected', active);
                })
            }
        }, refresh: function ($btn) {
            var link = this.link.get();
            if (link) {
                $btn.removeClass('fr-hidden');
            } else {
                $btn.addClass('fr-hidden');
            }
        }, plugin: 'link'
    })
}));
!function (l) {
    "function" == typeof define && define.amd ? define(["jquery"], l) : "object" == typeof module && module.exports ? module.exports = function (e, t) {
        return t === undefined && (t = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), l(t)
    } : l(window.jQuery)
}(function (Z) {
    Z.extend(Z.FE.POPUP_TEMPLATES, {
        "table.insert": "[_BUTTONS_][_ROWS_COLUMNS_]",
        "table.edit": "[_BUTTONS_]",
        "table.colors": "[_BUTTONS_][_COLORS_][_CUSTOM_COLOR_]"
    }), Z.extend(Z.FE.DEFAULTS, {
        tableInsertMaxSize: 10,
        tableEditButtons: ["tableHeader", "tableRemove", "|", "tableRows", "tableColumns", "tableStyle", "-", "tableCells", "tableCellBackground", "tableCellVerticalAlign", "tableCellHorizontalAlign", "tableCellStyle"],
        tableInsertButtons: ["tableBack", "|"],
        tableResizer: !0,
        tableDefaultWidth: "100%",
        tableResizerOffset: 5,
        tableResizingLimit: 30,
        tableColorsButtons: ["tableBack", "|"],
        tableColors: ["#61BD6D", "#1ABC9C", "#54ACD2", "#2C82C9", "#9365B8", "#475577", "#CCCCCC", "#41A85F", "#00A885", "#3D8EB9", "#2969B0", "#553982", "#28324E", "#000000", "#F7DA64", "#FBA026", "#EB6B56", "#E25041", "#A38F84", "#EFEFEF", "#FFFFFF", "#FAC51C", "#F37934", "#D14841", "#B8312F", "#7C706B", "#D1D5D8", "REMOVE"],
        tableColorsStep: 7,
        tableCellStyles: {"fr-highlighted": "Highlighted", "fr-thick": "Thick"},
        tableStyles: {"fr-dashed-borders": "Dashed Borders", "fr-alternate-rows": "Alternate Rows"},
        tableCellMultipleStyles: !0,
        tableMultipleStyles: !0,
        tableInsertHelper: !0,
        tableInsertHelperOffset: 15
    }), Z.FE.PLUGINS.table = function (w) {
        var C, o, r, s, a, n, E;

        function h() {
            var e = O();
            if (e) {
                var t = w.popups.get("table.edit");
                if (t || (t = p()), t) {
                    w.popups.setContainer("table.edit", w.$sc);
                    var l = M(e), a = (l.left + l.right) / 2, r = l.bottom;
                    w.popups.show("table.edit", a, r, l.bottom - l.top), w.edit.isDisabled() && (1 < J().length && w.toolbar.disable(), w.$el.removeClass("fr-no-selection"), w.edit.on(), w.button.bulkRefresh(), w.selection.setAtEnd(w.$el.find(".fr-selected-cell:last").get(0)), w.selection.restore())
                }
            }
        }

        function f() {
            var e, t, l, a, r = O();
            if (r) {
                var s = w.popups.get("table.colors");
                s || (s = function () {
                    var e = "";
                    0 < w.opts.tableColorsButtons.length && (e = '<div class="fr-buttons fr-table-colors-buttons">' + w.button.buildList(w.opts.tableColorsButtons) + "</div>");
                    var t = "";
                    w.opts.colorsHEXInput && (t = '<div class="fr-table-colors-hex-layer fr-active fr-layer" id="fr-table-colors-hex-layer-' + w.id + '"><div class="fr-input-line"><input maxlength="7" id="fr-table-colors-hex-layer-text-' + w.id + '" type="text" placeholder="' + w.language.translate("HEX Color") + '" tabIndex="1" aria-required="true"></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="tableCellBackgroundCustomColor" tabIndex="2" role="button">' + w.language.translate("OK") + "</button></div></div>");
                    var l = {
                        buttons: e, colors: function () {
                            for (var e = '<div class="fr-table-colors">', t = 0; t < w.opts.tableColors.length; t++) 0 !== t && t % w.opts.tableColorsStep == 0 && (e += "<br>"), "REMOVE" != w.opts.tableColors[t] ? e += '<span class="fr-command" style="background: ' + w.opts.tableColors[t] + ';" tabIndex="-1" role="button" data-cmd="tableCellBackgroundColor" data-param1="' + w.opts.tableColors[t] + '"><span class="fr-sr-only">' + w.language.translate("Color") + " " + w.opts.tableColors[t] + "&nbsp;&nbsp;&nbsp;</span></span>" : e += '<span class="fr-command" data-cmd="tableCellBackgroundColor" tabIndex="-1" role="button" data-param1="REMOVE" title="' + w.language.translate("Clear Formatting") + '">' + w.icon.create("tableColorRemove") + '<span class="fr-sr-only">' + w.language.translate("Clear Formatting") + "</span></span>";
                            return e += "</div>"
                        }(), custom_color: t
                    }, a = w.popups.create("table.colors", l);
                    return w.events.$on(w.$wp, "scroll.table-colors", function () {
                        w.popups.isVisible("table.colors") && f()
                    }), u = a, w.events.on("popup.tab", function (e) {
                        var t = Z(e.currentTarget);
                        if (!w.popups.isVisible("table.colors") || !t.is("span")) return !0;
                        var l = e.which, a = !0;
                        if (Z.FE.KEYCODE.TAB == l) {
                            var r = u.find(".fr-buttons");
                            a = !w.accessibility.focusToolbar(r, !!e.shiftKey)
                        } else if (Z.FE.KEYCODE.ARROW_UP == l || Z.FE.KEYCODE.ARROW_DOWN == l || Z.FE.KEYCODE.ARROW_LEFT == l || Z.FE.KEYCODE.ARROW_RIGHT == l) {
                            var s = t.parent().find("span.fr-command"), n = s.index(t), o = w.opts.colorsStep,
                                i = Math.floor(s.length / o), f = n % o, c = Math.floor(n / o), d = c * o + f,
                                p = i * o;
                            Z.FE.KEYCODE.ARROW_UP == l ? d = ((d - o) % p + p) % p : Z.FE.KEYCODE.ARROW_DOWN == l ? d = (d + o) % p : Z.FE.KEYCODE.ARROW_LEFT == l ? d = ((d - 1) % p + p) % p : Z.FE.KEYCODE.ARROW_RIGHT == l && (d = (d + 1) % p);
                            var h = Z(s.get(d));
                            w.events.disableBlur(), h.focus(), a = !1
                        } else Z.FE.KEYCODE.ENTER == l && (w.button.exec(t), a = !1);
                        return !1 === a && (e.preventDefault(), e.stopPropagation()), a
                    }, !0), a;
                    var u
                }()), w.popups.setContainer("table.colors", w.$sc);
                var n = M(r), o = (n.left + n.right) / 2, i = n.bottom;
                e = w.popups.get("table.colors"), t = w.$el.find(".fr-selected-cell:first"), l = w.helpers.RGBToHex(t.css("background-color")), a = e.find(".fr-table-colors-hex-layer input"), e.find(".fr-selected-color").removeClass("fr-selected-color fr-active-item"), e.find('span[data-param1="' + l + '"]').addClass("fr-selected-color fr-active-item"), a.val(l).trigger("change"), w.popups.show("table.colors", o, i, n.bottom - n.top)
            }
        }

        function i() {
            0 === J().length && w.toolbar.enable()
        }

        function c(e) {
            if (e) return w.popups.onHide("table.insert", function () {
                w.popups.get("table.insert").find('.fr-table-size .fr-select-table-size > span[data-row="1"][data-col="1"]').trigger("mouseenter")
            }), !0;
            var t = "";
            0 < w.opts.tableInsertButtons.length && (t = '<div class="fr-buttons">' + w.button.buildList(w.opts.tableInsertButtons) + "</div>");
            var l, a = {
                buttons: t, rows_columns: function () {
                    for (var e = '<div class="fr-table-size"><div class="fr-table-size-info">1 &times; 1</div><div class="fr-select-table-size">', t = 1; t <= w.opts.tableInsertMaxSize; t++) {
                        for (var l = 1; l <= w.opts.tableInsertMaxSize; l++) {
                            var a = "inline-block";
                            2 < t && !w.helpers.isMobile() && (a = "none");
                            var r = "fr-table-cell ";
                            1 == t && 1 == l && (r += " hover"), e += '<span class="fr-command ' + r + '" tabIndex="-1" data-cmd="tableInsert" data-row="' + t + '" data-col="' + l + '" data-param1="' + t + '" data-param2="' + l + '" style="display: ' + a + ';" role="button"><span></span><span class="fr-sr-only">' + t + " &times; " + l + "&nbsp;&nbsp;&nbsp;</span></span>"
                        }
                        e += '<div class="new-line"></div>'
                    }
                    return e += "</div></div>"
                }()
            }, r = w.popups.create("table.insert", a);
            return w.events.$on(r, "mouseenter", ".fr-table-size .fr-select-table-size .fr-table-cell", function (e) {
                d(Z(e.currentTarget))
            }, !0), l = r, w.events.$on(l, "focus", "[tabIndex]", function (e) {
                var t = Z(e.currentTarget);
                d(t)
            }), w.events.on("popup.tab", function (e) {
                var t = Z(e.currentTarget);
                if (!w.popups.isVisible("table.insert") || !t.is("span, a")) return !0;
                var l, a = e.which;
                if (Z.FE.KEYCODE.ARROW_UP == a || Z.FE.KEYCODE.ARROW_DOWN == a || Z.FE.KEYCODE.ARROW_LEFT == a || Z.FE.KEYCODE.ARROW_RIGHT == a) {
                    if (t.is("span.fr-table-cell")) {
                        var r = t.parent().find("span.fr-table-cell"), s = r.index(t), n = w.opts.tableInsertMaxSize,
                            o = s % n, i = Math.floor(s / n);
                        Z.FE.KEYCODE.ARROW_UP == a ? i = Math.max(0, i - 1) : Z.FE.KEYCODE.ARROW_DOWN == a ? i = Math.min(w.opts.tableInsertMaxSize - 1, i + 1) : Z.FE.KEYCODE.ARROW_LEFT == a ? o = Math.max(0, o - 1) : Z.FE.KEYCODE.ARROW_RIGHT == a && (o = Math.min(w.opts.tableInsertMaxSize - 1, o + 1));
                        var f = i * n + o, c = Z(r.get(f));
                        d(c), w.events.disableBlur(), c.focus(), l = !1
                    }
                } else Z.FE.KEYCODE.ENTER == a && (w.button.exec(t), l = !1);
                return !1 === l && (e.preventDefault(), e.stopPropagation()), l
            }, !0), r
        }

        function d(e) {
            var t = e.data("row"), l = e.data("col"), a = e.parent();
            a.siblings(".fr-table-size-info").html(t + " &times; " + l), a.find("> span").removeClass("hover fr-active-item");
            for (var r = 1; r <= w.opts.tableInsertMaxSize; r++) for (var s = 0; s <= w.opts.tableInsertMaxSize; s++) {
                var n = a.find('> span[data-row="' + r + '"][data-col="' + s + '"]');
                r <= t && s <= l ? n.addClass("hover") : r <= t + 1 || r <= 2 && !w.helpers.isMobile() ? n.css("display", "inline-block") : 2 < r && !w.helpers.isMobile() && n.css("display", "none")
            }
            e.addClass("fr-active-item")
        }

        function p(e) {
            if (e) return w.popups.onHide("table.edit", i), !0;
            if (0 < w.opts.tableEditButtons.length) {
                var t = {buttons: '<div class="fr-buttons">' + w.button.buildList(w.opts.tableEditButtons) + "</div>"},
                    l = w.popups.create("table.edit", t);
                return w.events.$on(w.$wp, "scroll.table-edit", function () {
                    w.popups.isVisible("table.edit") && h()
                }), l
            }
            return !1
        }

        function u() {
            if (0 < J().length) {
                var e = Q();
                w.selection.setBefore(e.get(0)) || w.selection.setAfter(e.get(0)), w.selection.restore(), w.popups.hide("table.edit"), e.remove(), w.toolbar.enable()
            }
        }

        function b(e) {
            var t = Q();
            if (0 < t.length) {
                if (0 < w.$el.find("th.fr-selected-cell").length && "above" == e) return;
                var l, a, r, s = O(), n = $(s);
                a = "above" == e ? n.min_i : n.max_i;
                var o = "<tr>";
                for (l = 0; l < s[a].length; l++) if ("below" == e && a < s.length - 1 && s[a][l] == s[a + 1][l] || "above" == e && 0 < a && s[a][l] == s[a - 1][l]) {
                    if (0 === l || 0 < l && s[a][l] != s[a][l - 1]) {
                        var i = Z(s[a][l]);
                        i.attr("rowspan", parseInt(i.attr("rowspan"), 10) + 1)
                    }
                } else o += "<td><br></td>";
                o += "</tr>", r = 0 < w.$el.find("th.fr-selected-cell").length && "below" == e ? Z(t.find("tbody").not(t.find("table tbody"))) : Z(t.find("tr").not(t.find("table tr")).get(a)), "below" == e ? "TBODY" == r.prop("tagName") ? r.prepend(o) : r.after(o) : "above" == e && (r.before(o), w.popups.isVisible("table.edit") && h())
            }
        }

        function g(e, t, l) {
            var a, r, s, n, o, i = 0, f = O(l);
            if (e < (t = Math.min(t, f[0].length - 1))) for (r = e; r <= t; r++) if (!(e < r && f[0][r] == f[0][r - 1]) && 1 < (n = Math.min(parseInt(f[0][r].getAttribute("colspan"), 10) || 1, t - e + 1)) && f[0][r] == f[0][r + 1]) for (i = n - 1, a = 1; a < f.length; a++) if (f[a][r] != f[a - 1][r]) {
                for (s = r; s < r + n; s++) if (1 < (o = parseInt(f[a][s].getAttribute("colspan"), 10) || 1) && f[a][s] == f[a][s + 1]) s += i = Math.min(i, o - 1); else if (!(i = Math.max(0, i - 1))) break;
                if (!i) break
            }
            i && v(f, i, "colspan", 0, f.length - 1, e, t)
        }

        function m(e, t, l) {
            var a, r, s, n, o, i = 0, f = O(l);
            if (e < (t = Math.min(t, f.length - 1))) for (a = e; a <= t; a++) if (!(e < a && f[a][0] == f[a - 1][0]) && 1 < (n = Math.min(parseInt(f[a][0].getAttribute("rowspan"), 10) || 1, t - e + 1)) && f[a][0] == f[a + 1][0]) for (i = n - 1, r = 1; r < f[0].length; r++) if (f[a][r] != f[a][r - 1]) {
                for (s = a; s < a + n; s++) if (1 < (o = parseInt(f[s][r].getAttribute("rowspan"), 10) || 1) && f[s][r] == f[s + 1][r]) s += i = Math.min(i, o - 1); else if (!(i = Math.max(0, i - 1))) break;
                if (!i) break
            }
            i && v(f, i, "rowspan", e, t, 0, f[0].length - 1)
        }

        function v(e, t, l, a, r, s, n) {
            var o, i, f;
            for (o = a; o <= r; o++) for (i = s; i <= n; i++) a < o && e[o][i] == e[o - 1][i] || s < i && e[o][i] == e[o][i - 1] || 1 < (f = parseInt(e[o][i].getAttribute(l), 10) || 1) && (1 < f - t ? e[o][i].setAttribute(l, f - t) : e[o][i].removeAttribute(l))
        }

        function R(e, t, l, a, r) {
            m(e, t, r), g(l, a, r)
        }

        function t(e) {
            var t = w.$el.find(".fr-selected-cell");
            "REMOVE" != e ? t.css("background-color", w.helpers.HEXtoRGB(e)) : t.css("background-color", ""), h()
        }

        function O(e) {
            var f = [];
            return null == (e = e || null) && 0 < J().length && (e = Q()), e && e.find("tr:visible").not(e.find("table tr")).each(function (o, e) {
                var t = Z(e), i = 0;
                t.find("> th, > td").each(function (e, t) {
                    for (var l = Z(t), a = parseInt(l.attr("colspan"), 10) || 1, r = parseInt(l.attr("rowspan"), 10) || 1, s = o; s < o + r; s++) for (var n = i; n < i + a; n++) f[s] || (f[s] = []), f[s][n] ? i++ : f[s][n] = t;
                    i += a
                })
            }), f
        }

        function A(e, t) {
            for (var l = 0; l < t.length; l++) for (var a = 0; a < t[l].length; a++) if (t[l][a] == e) return {
                row: l,
                col: a
            }
        }

        function F(e, t, l) {
            for (var a = e + 1, r = t + 1; a < l.length;) {
                if (l[a][t] != l[e][t]) {
                    a--;
                    break
                }
                a++
            }
            for (a == l.length && a--; r < l[e].length;) {
                if (l[e][r] != l[e][t]) {
                    r--;
                    break
                }
                r++
            }
            return r == l[e].length && r--, {row: a, col: r}
        }

        function x() {
            w.el.querySelector(".fr-cell-fixed") && w.el.querySelector(".fr-cell-fixed").classList.remove("fr-cell-fixed"), w.el.querySelector(".fr-cell-handler") && w.el.querySelector(".fr-cell-handler").classList.remove("fr-cell-handler")
        }

        function D() {
            var e = w.$el.find(".fr-selected-cell");
            0 < e.length && e.each(function () {
                var e = Z(this);
                e.removeClass("fr-selected-cell"), "" === e.attr("class") && e.removeAttr("class")
            }), x()
        }

        function y() {
            w.events.disableBlur(), w.selection.clear(), w.$el.addClass("fr-no-selection"), w.$el.blur(), w.events.enableBlur()
        }

        function $(e) {
            var t = w.$el.find(".fr-selected-cell");
            if (0 < t.length) {
                var l, a = e.length, r = 0, s = e[0].length, n = 0;
                for (l = 0; l < t.length; l++) {
                    var o = A(t[l], e), i = F(o.row, o.col, e);
                    a = Math.min(o.row, a), r = Math.max(i.row, r), s = Math.min(o.col, s), n = Math.max(i.col, n)
                }
                return {min_i: a, max_i: r, min_j: s, max_j: n}
            }
            return null
        }

        function M(e) {
            var t = $(e), l = Z(e[t.min_i][t.min_j]), a = Z(e[t.min_i][t.max_j]), r = Z(e[t.max_i][t.min_j]);
            return {
                left: l.offset().left,
                right: a.offset().left + a.outerWidth(),
                top: l.offset().top,
                bottom: r.offset().top + r.outerHeight()
            }
        }

        function _(t, l) {
            if (Z(t).is(l)) D(), Z(t).addClass("fr-selected-cell"); else {
                y(), w.edit.off();
                var a = O(), r = A(t, a), s = A(l, a), n = function e(t, l, a, r, s) {
                    var n, o, i, f, c = t, d = l, p = a, h = r;
                    for (n = c; n <= d; n++) (1 < (parseInt(Z(s[n][p]).attr("rowspan"), 10) || 1) || 1 < (parseInt(Z(s[n][p]).attr("colspan"), 10) || 1)) && (f = F((i = A(s[n][p], s)).row, i.col, s), c = Math.min(i.row, c), d = Math.max(f.row, d), p = Math.min(i.col, p), h = Math.max(f.col, h)), (1 < (parseInt(Z(s[n][h]).attr("rowspan"), 10) || 1) || 1 < (parseInt(Z(s[n][h]).attr("colspan"), 10) || 1)) && (f = F((i = A(s[n][h], s)).row, i.col, s), c = Math.min(i.row, c), d = Math.max(f.row, d), p = Math.min(i.col, p), h = Math.max(f.col, h));
                    for (o = p; o <= h; o++) (1 < (parseInt(Z(s[c][o]).attr("rowspan"), 10) || 1) || 1 < (parseInt(Z(s[c][o]).attr("colspan"), 10) || 1)) && (f = F((i = A(s[c][o], s)).row, i.col, s), c = Math.min(i.row, c), d = Math.max(f.row, d), p = Math.min(i.col, p), h = Math.max(f.col, h)), (1 < (parseInt(Z(s[d][o]).attr("rowspan"), 10) || 1) || 1 < (parseInt(Z(s[d][o]).attr("colspan"), 10) || 1)) && (f = F((i = A(s[d][o], s)).row, i.col, s), c = Math.min(i.row, c), d = Math.max(f.row, d), p = Math.min(i.col, p), h = Math.max(f.col, h));
                    return c == t && d == l && p == a && h == r ? {
                        min_i: t,
                        max_i: l,
                        min_j: a,
                        max_j: r
                    } : e(c, d, p, h, s)
                }(Math.min(r.row, s.row), Math.max(r.row, s.row), Math.min(r.col, s.col), Math.max(r.col, s.col), a);
                D(), t.classList.add("fr-cell-fixed"), l.classList.add("fr-cell-handler");
                for (var o = n.min_i; o <= n.max_i; o++) for (var i = n.min_j; i <= n.max_j; i++) Z(a[o][i]).addClass("fr-selected-cell")
            }
        }

        function I(e) {
            var t = null, l = Z(e.target);
            return "TD" == e.target.tagName || "TH" == e.target.tagName ? t = e.target : 0 < l.closest("td").length ? t = l.closest("td").get(0) : 0 < l.closest("th").length && (t = l.closest("th").get(0)), 0 === w.$el.find(t).length ? null : t
        }

        function T() {
            D(), w.popups.hide("table.edit")
        }

        function e(e) {
            var t = I(e);
            if ("false" == Z(t).parents("[contenteditable]:not(.fr-element):not(.fr-img-caption):not(body):first").attr("contenteditable")) return !0;
            if (0 < J().length && !t && T(), !w.edit.isDisabled() || w.popups.isVisible("table.edit")) if (1 != e.which || 1 == e.which && w.helpers.isMac() && e.ctrlKey) (3 == e.which || 1 == e.which && w.helpers.isMac() && e.ctrlKey) && t && T(); else if (s = !0, t) {
                0 < J().length && !e.shiftKey && T(), e.stopPropagation(), w.events.trigger("image.hideResizer"), w.events.trigger("video.hideResizer"), r = !0;
                var l = t.tagName.toLowerCase();
                e.shiftKey && 0 < w.$el.find(l + ".fr-selected-cell").length ? Z(w.$el.find(l + ".fr-selected-cell").closest("table")).is(Z(t).closest("table")) ? _(a, t) : y() : ((w.keys.ctrlKey(e) || e.shiftKey) && (1 < J().length || 0 === Z(t).find(w.selection.element()).length && !Z(t).is(w.selection.element())) && y(), a = t, 0 < w.opts.tableEditButtons.length && _(a, a))
            }
        }

        function l(e) {
            if (w.popups.areVisible()) return !0;
            if (r || w.$tb.is(e.target) || w.$tb.is(Z(e.target).closest(w.$tb.get(0))) || (0 < J().length && w.toolbar.enable(), D()), !(1 != e.which || 1 == e.which && w.helpers.isMac() && e.ctrlKey)) {
                if (s = !1, r) r = !1, I(e) || 1 != J().length ? 0 < J().length && (w.selection.isCollapsed() ? h() : D()) : D();
                if (E) {
                    E = !1, C.removeClass("fr-moving"), w.$el.removeClass("fr-no-selection"), w.edit.on();
                    var t = parseFloat(C.css("left")) + w.opts.tableResizerOffset + w.$wp.offset().left;
                    w.opts.iframe && (t -= w.$iframe.offset().left), C.data("release-position", t), C.removeData("max-left"), C.removeData("max-right"), function () {
                        var e = C.data("origin"), t = C.data("release-position");
                        if (e !== t) {
                            var l = C.data("first"), a = C.data("second"), r = C.data("table"), s = r.outerWidth();
                            if (w.undo.canDo() || w.undo.saveStep(), null !== l && null !== a) {
                                var n, o, i, f = O(r), c = [], d = [], p = [], h = [];
                                for (n = 0; n < f.length; n++) o = Z(f[n][l]), i = Z(f[n][a]), c[n] = o.outerWidth(), p[n] = i.outerWidth(), d[n] = c[n] / s * 100, h[n] = p[n] / s * 100;
                                for (n = 0; n < f.length; n++) if (o = Z(f[n][l]), i = Z(f[n][a]), f[n][l] != f[n][a]) {
                                    var u = (d[n] * (c[n] + t - e) / c[n]).toFixed(4);
                                    o.css("width", u + "%"), i.css("width", (d[n] + h[n] - u).toFixed(4) + "%")
                                }
                            } else {
                                var b, g = r.parent(), m = s / g.width() * 100,
                                    v = (parseInt(r.css("margin-left"), 10) || 0) / g.width() * 100,
                                    E = (parseInt(r.css("margin-right"), 10) || 0) / g.width() * 100;
                                "rtl" == w.opts.direction && 0 === a || "rtl" != w.opts.direction && 0 !== a ? (b = (s + t - e) / s * m, r.css("margin-right", "calc(100% - " + Math.round(b).toFixed(4) + "% - " + Math.round(v).toFixed(4) + "%)")) : ("rtl" == w.opts.direction && 0 !== a || "rtl" != w.opts.direction && 0 === a) && (b = (s - t + e) / s * m, r.css("margin-left", "calc(100% - " + Math.round(b).toFixed(4) + "% - " + Math.round(E).toFixed(4) + "%)")), r.css("width", Math.round(b).toFixed(4) + "%")
                            }
                            w.selection.restore(), w.undo.saveStep(), w.events.trigger("table.resized", [r.get(0)])
                        }
                        C.removeData("origin"), C.removeData("release-position"), C.removeData("first"), C.removeData("second"), C.removeData("table")
                    }(), W()
                }
            }
        }

        function N(e) {
            if (!0 === r && 0 < w.opts.tableEditButtons.length) {
                if (Z(e.currentTarget).closest("table").is(Q())) {
                    if ("TD" == e.currentTarget.tagName && 0 === w.$el.find("th.fr-selected-cell").length) return void _(a, e.currentTarget);
                    if ("TH" == e.currentTarget.tagName && 0 === w.$el.find("td.fr-selected-cell").length) return void _(a, e.currentTarget)
                }
                y()
            }
        }

        function S(e, t, l, a) {
            for (var r, s = t; s != w.el && "TD" != s.tagName && "TH" != s.tagName && ("up" == a ? r = s.previousElementSibling : "down" == a && (r = s.nextElementSibling), !r);) s = s.parentNode;
            "TD" == s.tagName || "TH" == s.tagName ? function (e, t) {
                for (var l = e; l && "TABLE" != l.tagName && l.parentNode != w.el;) l = l.parentNode;
                if (l && "TABLE" == l.tagName) {
                    var a = O(Z(l));
                    "up" == t ? z(A(e, a), l, a) : "down" == t && B(A(e, a), l, a)
                }
            }(s, a) : r && ("up" == a && w.selection.setAtEnd(r), "down" == a && w.selection.setAtStart(r))
        }

        function z(e, t, l) {
            0 < e.row ? w.selection.setAtEnd(l[e.row - 1][e.col]) : S(0, t, 0, "up")
        }

        function B(e, t, l) {
            var a = parseInt(l[e.row][e.col].getAttribute("rowspan"), 10) || 1;
            e.row < l.length - a ? w.selection.setAtStart(l[e.row + a][e.col]) : S(0, t, 0, "down")
        }

        function W() {
            C && (C.find("div").css("opacity", 0), C.css("top", 0), C.css("left", 0), C.css("height", 0), C.find("div").css("height", 0), C.hide())
        }

        function k() {
            o && o.removeClass("fr-visible").css("left", "-9999px")
        }

        function K(e, t) {
            var l = Z(t), a = l.closest("table"), r = a.parent();
            if (t && "TD" != t.tagName && "TH" != t.tagName && (0 < l.closest("td").length ? t = l.closest("td") : 0 < l.closest("th").length && (t = l.closest("th"))), !t || "TD" != t.tagName && "TH" != t.tagName) C && l.get(0) != C.get(0) && l.parent().get(0) != C.get(0) && w.core.sameInstance(C) && W(); else {
                if (l = Z(t), 0 === w.$el.find(l).length) return !1;
                var s = l.offset().left - 1, n = s + l.outerWidth();
                if (Math.abs(e.pageX - s) <= w.opts.tableResizerOffset || Math.abs(n - e.pageX) <= w.opts.tableResizerOffset) {
                    var o, i, f, c, d, p = O(a), h = A(t, p), u = F(h.row, h.col, p), b = a.offset().top,
                        g = a.outerHeight() - 1;
                    "rtl" != w.opts.direction ? e.pageX - s <= w.opts.tableResizerOffset ? (f = s, 0 < h.col ? (c = s - j(h.col - 1, p) + w.opts.tableResizingLimit, d = s + j(h.col, p) - w.opts.tableResizingLimit, o = h.col - 1, i = h.col) : (o = null, i = 0, c = a.offset().left - 1 - parseInt(a.css("margin-left"), 10), d = a.offset().left - 1 + a.width() - p[0].length * w.opts.tableResizingLimit)) : n - e.pageX <= w.opts.tableResizerOffset && (f = n, u.col < p[u.row].length && p[u.row][u.col + 1] ? (c = n - j(u.col, p) + w.opts.tableResizingLimit, d = n + j(u.col + 1, p) - w.opts.tableResizingLimit, o = u.col, i = u.col + 1) : (o = u.col, i = null, c = a.offset().left - 1 + p[0].length * w.opts.tableResizingLimit, d = r.offset().left - 1 + r.width() + parseFloat(r.css("padding-left")))) : n - e.pageX <= w.opts.tableResizerOffset ? (f = n, 0 < h.col ? (c = n - j(h.col, p) + w.opts.tableResizingLimit, d = n + j(h.col - 1, p) - w.opts.tableResizingLimit, o = h.col, i = h.col - 1) : (o = null, i = 0, c = a.offset().left + p[0].length * w.opts.tableResizingLimit, d = r.offset().left - 1 + r.width() + parseFloat(r.css("padding-left")))) : e.pageX - s <= w.opts.tableResizerOffset && (f = s, u.col < p[u.row].length && p[u.row][u.col + 1] ? (c = s - j(u.col + 1, p) + w.opts.tableResizingLimit, d = s + j(u.col, p) - w.opts.tableResizingLimit, o = u.col + 1, i = u.col) : (o = u.col, i = null, c = r.offset().left + parseFloat(r.css("padding-left")), d = a.offset().left - 1 + a.width() - p[0].length * w.opts.tableResizingLimit)), C || (w.shared.$table_resizer || (w.shared.$table_resizer = Z('<div class="fr-table-resizer"><div></div></div>')), C = w.shared.$table_resizer, w.events.$on(C, "mousedown", function (e) {
                        return !w.core.sameInstance(C) || (0 < J().length && T(), 1 == e.which ? (w.selection.save(), E = !0, C.addClass("fr-moving"), y(), w.edit.off(), C.find("div").css("opacity", 1), !1) : void 0)
                    }), w.events.$on(C, "mousemove", function (e) {
                        if (!w.core.sameInstance(C)) return !0;
                        E && (w.opts.iframe && (e.pageX -= w.$iframe.offset().left), X(e))
                    }), w.events.on("shared.destroy", function () {
                        C.html("").removeData().remove(), C = null
                    }, !0), w.events.on("destroy", function () {
                        w.$el.find(".fr-selected-cell").removeClass("fr-selected-cell"), C.hide().appendTo(Z("body:first"))
                    }, !0)), C.data("table", a), C.data("first", o), C.data("second", i), C.data("instance", w), w.$wp.append(C);
                    var m = f - w.win.pageXOffset - w.opts.tableResizerOffset - w.$wp.offset().left,
                        v = b - w.$wp.offset().top + w.$wp.scrollTop();
                    w.opts.iframe && (m += w.$iframe.offset().left, v += w.$iframe.offset().top, c += w.$iframe.offset().left, d += w.$iframe.offset().left), C.data("max-left", c), C.data("max-right", d), C.data("origin", f - w.win.pageXOffset), C.css("top", v), C.css("left", m), C.css("height", g), C.find("div").css("height", g), C.css("padding-left", w.opts.tableResizerOffset), C.css("padding-right", w.opts.tableResizerOffset), C.show()
                } else w.core.sameInstance(C) && W()
            }
        }

        function L(e, t) {
            if (w.$box.find(".fr-line-breaker").is(":visible")) return !1;
            o || q(), w.$box.append(o), o.data("instance", w);
            var l, a = Z(t).find("tr:first"), r = e.pageX, s = 0, n = 0;
            w.opts.iframe && (s += w.$iframe.offset().left - w.helpers.scrollLeft(), n += w.$iframe.offset().top - w.helpers.scrollTop()), a.find("th, td").each(function () {
                var e = Z(this);
                return e.offset().left <= r && r < e.offset().left + e.outerWidth() / 2 ? (l = parseInt(o.find("a").css("width"), 10), o.css("top", n + e.offset().top - w.$box.offset().top - l - 5), o.css("left", s + e.offset().left - w.$box.offset().left - l / 2), o.data("selected-cell", e), o.data("position", "before"), o.addClass("fr-visible"), !1) : e.offset().left + e.outerWidth() / 2 <= r && r < e.offset().left + e.outerWidth() ? (l = parseInt(o.find("a").css("width"), 10), o.css("top", n + e.offset().top - w.$box.offset().top - l - 5), o.css("left", s + e.offset().left - w.$box.offset().left + e.outerWidth() - l / 2), o.data("selected-cell", e), o.data("position", "after"), o.addClass("fr-visible"), !1) : void 0
            })
        }

        function H(e, t) {
            if (w.$box.find(".fr-line-breaker").is(":visible")) return !1;
            o || q(), w.$box.append(o), o.data("instance", w);
            var l, a = Z(t), r = e.pageY, s = 0, n = 0;
            w.opts.iframe && (s += w.$iframe.offset().left - w.helpers.scrollLeft(), n += w.$iframe.offset().top - w.helpers.scrollTop()), a.find("tr").each(function () {
                var e = Z(this);
                return e.offset().top <= r && r < e.offset().top + e.outerHeight() / 2 ? (l = parseInt(o.find("a").css("width"), 10), o.css("top", n + e.offset().top - w.$box.offset().top - l / 2), o.css("left", s + e.offset().left - w.$box.offset().left - l - 5), o.data("selected-cell", e.find("td:first")), o.data("position", "above"), o.addClass("fr-visible"), !1) : e.offset().top + e.outerHeight() / 2 <= r && r < e.offset().top + e.outerHeight() ? (l = parseInt(o.find("a").css("width"), 10), o.css("top", n + e.offset().top - w.$box.offset().top + e.outerHeight() - l / 2), o.css("left", s + e.offset().left - w.$box.offset().left - l - 5), o.data("selected-cell", e.find("td:first")), o.data("position", "below"), o.addClass("fr-visible"), !1) : void 0
            })
        }

        function Y(e) {
            n = null;
            var t = w.doc.elementFromPoint(e.pageX - w.win.pageXOffset, e.pageY - w.win.pageYOffset);
            w.opts.tableResizer && (!w.popups.areVisible() || w.popups.areVisible() && w.popups.isVisible("table.edit")) && K(e, t), !w.opts.tableInsertHelper || w.popups.areVisible() || w.$tb.hasClass("fr-inline") && w.$tb.is(":visible") || function (e, t) {
                if (0 === J().length) {
                    var l, a, r;
                    if (t && ("HTML" == t.tagName || "BODY" == t.tagName || w.node.isElement(t))) for (l = 1; l <= w.opts.tableInsertHelperOffset; l++) {
                        if (a = w.doc.elementFromPoint(e.pageX - w.win.pageXOffset, e.pageY - w.win.pageYOffset + l), Z(a).hasClass("fr-tooltip")) return;
                        if (a && ("TH" == a.tagName || "TD" == a.tagName || "TABLE" == a.tagName) && (Z(a).parents(".fr-wrapper").length || w.opts.iframe)) return L(e, Z(a).closest("table"));
                        if (r = w.doc.elementFromPoint(e.pageX - w.win.pageXOffset + l, e.pageY - w.win.pageYOffset), Z(r).hasClass("fr-tooltip")) return;
                        if (r && ("TH" == r.tagName || "TD" == r.tagName || "TABLE" == r.tagName) && (Z(r).parents(".fr-wrapper").length || w.opts.iframe)) return H(e, Z(r).closest("table"))
                    }
                    w.core.sameInstance(o) && k()
                }
            }(e, t)
        }

        function P() {
            if (E) {
                var e = C.data("table").offset().top - w.win.pageYOffset;
                w.opts.iframe && (e += w.$iframe.offset().top - w.helpers.scrollTop()), C.css("top", e)
            }
        }

        function j(e, t) {
            var l, a = Z(t[0][e]).outerWidth();
            for (l = 1; l < t.length; l++) a = Math.min(a, Z(t[l][e]).outerWidth());
            return a
        }

        function V(e, t, l) {
            var a, r = 0;
            for (a = e; a <= t; a++) r += j(a, l);
            return r
        }

        function X(e) {
            if (1 < J().length && s && y(), !1 === s && !1 === r && !1 === E) n && clearTimeout(n), w.edit.isDisabled() && !w.popups.isVisible("table.edit") || (n = setTimeout(Y, 30, e)); else if (E) {
                var t = e.pageX - w.win.pageXOffset;
                w.opts.iframe && (t += w.$iframe.offset().left);
                var l = C.data("max-left"), a = C.data("max-right");
                l <= t && t <= a ? C.css("left", t - w.opts.tableResizerOffset - w.$wp.offset().left) : t < l && parseFloat(C.css("left"), 10) > l - w.opts.tableResizerOffset ? C.css("left", l - w.opts.tableResizerOffset - w.$wp.offset().left) : a < t && parseFloat(C.css("left"), 10) < a - w.opts.tableResizerOffset && C.css("left", a - w.opts.tableResizerOffset - w.$wp.offset().left)
            } else s && k()
        }

        function U(e) {
            w.node.isEmpty(e.get(0)) ? e.prepend(Z.FE.MARKERS) : e.prepend(Z.FE.START_MARKER).append(Z.FE.END_MARKER)
        }

        function q() {
            w.shared.$ti_helper || (w.shared.$ti_helper = Z('<div class="fr-insert-helper"><a class="fr-floating-btn" role="button" tabIndex="-1" title="' + w.language.translate("Insert") + '"><svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><path d="M22,16.75 L16.75,16.75 L16.75,22 L15.25,22.000 L15.25,16.75 L10,16.75 L10,15.25 L15.25,15.25 L15.25,10 L16.75,10 L16.75,15.25 L22,15.25 L22,16.75 Z"/></svg></a></div>'), w.events.bindClick(w.shared.$ti_helper, "a", function () {
                var e = o.data("selected-cell"), t = o.data("position"), l = o.data("instance") || w;
                "before" == t ? (w.undo.saveStep(), e.addClass("fr-selected-cell"), l.table.insertColumn(t), e.removeClass("fr-selected-cell"), w.undo.saveStep()) : "after" == t ? (w.undo.saveStep(), e.addClass("fr-selected-cell"), l.table.insertColumn(t), e.removeClass("fr-selected-cell"), w.undo.saveStep()) : "above" == t ? (w.undo.saveStep(), e.addClass("fr-selected-cell"), l.table.insertRow(t), e.removeClass("fr-selected-cell"), w.undo.saveStep()) : "below" == t && (w.undo.saveStep(), e.addClass("fr-selected-cell"), l.table.insertRow(t), e.removeClass("fr-selected-cell"), w.undo.saveStep()), k()
            }), w.events.on("shared.destroy", function () {
                w.shared.$ti_helper.html("").removeData().remove(), w.shared.$ti_helper = null
            }, !0), w.events.$on(w.shared.$ti_helper, "mousemove", function (e) {
                e.stopPropagation()
            }, !0), w.events.$on(Z(w.o_win), "scroll", function () {
                k()
            }, !0), w.events.$on(w.$wp, "scroll", function () {
                k()
            }, !0)), o = w.shared.$ti_helper, w.events.on("destroy", function () {
                o = null
            }), w.tooltip.bind(w.$box, ".fr-insert-helper > a.fr-floating-btn")
        }

        function G() {
            a = null, clearTimeout(n)
        }

        function J() {
            return w.el.querySelectorAll(".fr-selected-cell")
        }

        function Q() {
            var e = J();
            if (e.length) {
                for (var t = e[0]; t && "TABLE" != t.tagName && t.parentNode != w.el;) t = t.parentNode;
                return t && "TABLE" == t.tagName ? Z(t) : Z([])
            }
            return Z([])
        }

        return {
            _init: function () {
                if (!w.$wp) return !1;
                if (!w.helpers.isMobile()) {
                    E = r = s = !1, w.events.$on(w.$el, "mousedown", e), w.popups.onShow("image.edit", function () {
                        D(), r = s = !1
                    }), w.popups.onShow("link.edit", function () {
                        D(), r = s = !1
                    }), w.events.on("commands.mousedown", function (e) {
                        0 < e.parents(".fr-toolbar").length && D()
                    }), w.events.$on(w.$el, "mouseenter", "th, td", N), w.events.$on(w.$win, "mouseup", l), w.opts.iframe && w.events.$on(Z(w.o_win), "mouseup", l), w.events.$on(w.$win, "mousemove", X), w.events.$on(Z(w.o_win), "scroll", P), w.events.on("contentChanged", function () {
                        0 < J().length && (h(), w.$el.find("img").on("load.selected-cells", function () {
                            Z(this).off("load.selected-cells"), 0 < J().length && h()
                        }))
                    }), w.events.$on(Z(w.o_win), "resize", function () {
                        D()
                    }), w.events.on("toolbar.esc", function () {
                        if (0 < J().length) return w.events.disableBlur(), w.events.focus(), !1
                    }, !0), w.events.$on(Z(w.o_win), "keydown", function () {
                        s && r && (r = s = !1, w.$el.removeClass("fr-no-selection"), w.edit.on(), w.selection.setAtEnd(w.$el.find(".fr-selected-cell:last").get(0)), w.selection.restore(), D())
                    }), w.events.$on(w.$el, "keydown", function (e) {
                        e.shiftKey ? !1 === function (e) {
                            var t = J();
                            if (0 < t.length) {
                                var l, a, r = O(), s = e.which;
                                1 == t.length ? a = l = t[0] : (l = w.el.querySelector(".fr-cell-fixed"), a = w.el.querySelector(".fr-cell-handler"));
                                var n = A(a, r);
                                if (Z.FE.KEYCODE.ARROW_RIGHT == s) {
                                    if (n.col < r[0].length - 1) return _(l, r[n.row][n.col + 1]), !1
                                } else if (Z.FE.KEYCODE.ARROW_DOWN == s) {
                                    if (n.row < r.length - 1) return _(l, r[n.row + 1][n.col]), !1
                                } else if (Z.FE.KEYCODE.ARROW_LEFT == s) {
                                    if (0 < n.col) return _(l, r[n.row][n.col - 1]), !1
                                } else if (Z.FE.KEYCODE.ARROW_UP == s && 0 < n.row) return _(l, r[n.row - 1][n.col]), !1
                            }
                        }(e) && setTimeout(function () {
                            h()
                        }, 0) : function (e) {
                            var t = e.which, l = w.selection.blocks();
                            if (l.length && ("TD" == (l = l[0]).tagName || "TH" == l.tagName)) {
                                for (var a = l; a && "TABLE" != a.tagName && a.parentNode != w.el;) a = a.parentNode;
                                if (a && "TABLE" == a.tagName && (Z.FE.KEYCODE.ARROW_LEFT == t || Z.FE.KEYCODE.ARROW_UP == t || Z.FE.KEYCODE.ARROW_RIGHT == t || Z.FE.KEYCODE.ARROW_DOWN == t) && (0 < J().length && T(), w.browser.webkit && (Z.FE.KEYCODE.ARROW_UP == t || Z.FE.KEYCODE.ARROW_DOWN == t))) {
                                    var r = w.selection.ranges(0).startContainer;
                                    if (r.nodeType == Node.TEXT_NODE && (Z.FE.KEYCODE.ARROW_UP == t && r.previousSibling || Z.FE.KEYCODE.ARROW_DOWN == t && r.nextSibling)) return;
                                    e.preventDefault(), e.stopPropagation();
                                    var s = O(Z(a)), n = A(l, s);
                                    Z.FE.KEYCODE.ARROW_UP == t ? z(n, a, s) : Z.FE.KEYCODE.ARROW_DOWN == t && B(n, a, s), w.selection.restore()
                                }
                            }
                        }(e)
                    }), w.events.on("keydown", function (e) {
                        if (!1 === function (e) {
                            if (e.which == Z.FE.KEYCODE.TAB) {
                                var t;
                                if (0 < J().length) t = w.$el.find(".fr-selected-cell:last"); else {
                                    var l = w.selection.element();
                                    "TD" == l.tagName || "TH" == l.tagName ? t = Z(l) : l != w.el && (0 < Z(l).parentsUntil(w.$el, "td").length ? t = Z(l).parents("td:first") : 0 < Z(l).parentsUntil(w.$el, "th").length && (t = Z(l).parents("th:first")))
                                }
                                if (t) return e.preventDefault(), !!(0 < Z(w.selection.element()).parentsUntil(w.$el, "ol, ul").length && (0 < Z(w.selection.element()).parents("li").prev().length || Z(w.selection.element()).is("li") && 0 < Z(w.selection.element()).prev().length)) || (T(), e.shiftKey ? 0 < t.prev().length ? U(t.prev()) : 0 < t.closest("tr").length && 0 < t.closest("tr").prev().length ? U(t.closest("tr").prev().find("td:last")) : 0 < t.closest("tbody").length && 0 < t.closest("table").find("thead tr").length && U(t.closest("table").find("thead tr th:last")) : 0 < t.next().length ? U(t.next()) : 0 < t.closest("tr").length && 0 < t.closest("tr").next().length ? U(t.closest("tr").next().find("td:first")) : 0 < t.closest("thead").length && 0 < t.closest("table").find("tbody tr").length ? U(t.closest("table").find("tbody tr td:first")) : (t.addClass("fr-selected-cell"), b("below"), D(), U(t.closest("tr").next().find("td:first"))), w.selection.restore(), !1)
                            }
                        }(e)) return !1;
                        var t = J();
                        if (0 < t.length) {
                            if (0 < t.length && w.keys.ctrlKey(e) && e.which == Z.FE.KEYCODE.A) return D(), w.popups.isVisible("table.edit") && w.popups.hide("table.edit"), t = [], !0;
                            if (e.which == Z.FE.KEYCODE.ESC && w.popups.isVisible("table.edit")) return D(), w.popups.hide("table.edit"), e.preventDefault(), e.stopPropagation(), e.stopImmediatePropagation(), !(t = []);
                            if (1 < t.length && (e.which == Z.FE.KEYCODE.BACKSPACE || e.which == Z.FE.KEYCODE.DELETE)) {
                                w.undo.saveStep();
                                for (var l = 0; l < t.length; l++) Z(t[l]).html("<br>"), l == t.length - 1 && Z(t[l]).prepend(Z.FE.MARKERS);
                                return w.selection.restore(), w.undo.saveStep(), !(t = [])
                            }
                            if (1 < t.length && e.which != Z.FE.KEYCODE.F10 && !w.keys.isBrowserAction(e)) return e.preventDefault(), !(t = [])
                        } else if (!(t = []) === function (e) {
                            if (e.altKey && e.which == Z.FE.KEYCODE.SPACE) {
                                var t, l = w.selection.element();
                                if ("TD" == l.tagName || "TH" == l.tagName ? t = l : 0 < Z(l).closest("td").length ? t = Z(l).closest("td").get(0) : 0 < Z(l).closest("th").length && (t = Z(l).closest("th").get(0)), t) return e.preventDefault(), _(t, t), h(), !1
                            }
                        }(e)) return !1
                    }, !0);
                    var t = [];
                    w.events.on("html.beforeGet", function () {
                        t = J();
                        for (var e = 0; e < t.length; e++) t[e].className = (t[e].className || "").replace(/fr-selected-cell/g, "")
                    }), w.events.on("html.afterGet", function () {
                        for (var e = 0; e < t.length; e++) t[e].className = (t[e].className ? t[e].className.trim() + " " : "") + "fr-selected-cell";
                        t = []
                    }), c(!0), p(!0)
                }
                w.events.on("destroy", G)
            }, insert: function (e, t) {
                var l, a,
                    r = "<table " + (w.opts.tableDefaultWidth ? 'style="width: ' + w.opts.tableDefaultWidth + ';" ' : "") + 'class="fr-inserted-table"><tbody>',
                    s = 100 / t;
                for (l = 0; l < e; l++) {
                    for (r += "<tr>", a = 0; a < t; a++) r += "<td" + (w.opts.tableDefaultWidth ? ' style="width: ' + s.toFixed(4) + '%;"' : "") + ">", 0 === l && 0 === a && (r += Z.FE.MARKERS), r += "<br></td>";
                    r += "</tr>"
                }
                r += "</tbody></table>", w.html.insert(r), w.selection.restore();
                var n = w.$el.find(".fr-inserted-table");
                n.removeClass("fr-inserted-table"), w.events.trigger("table.inserted", [n.get(0)])
            }, remove: u, insertRow: b, deleteRow: function () {
                var e = Q();
                if (0 < e.length) {
                    var t, l, a, r = O(), s = $(r);
                    if (0 === s.min_i && s.max_i == r.length - 1) u(); else {
                        for (t = s.max_i; t >= s.min_i; t--) {
                            for (a = Z(e.find("tr").not(e.find("table tr")).get(t)), l = 0; l < r[t].length; l++) if (0 === l || r[t][l] != r[t][l - 1]) {
                                var n = Z(r[t][l]);
                                if (1 < parseInt(n.attr("rowspan"), 10)) {
                                    var o = parseInt(n.attr("rowspan"), 10) - 1;
                                    1 == o ? n.removeAttr("rowspan") : n.attr("rowspan", o)
                                }
                                if (t < r.length - 1 && r[t][l] == r[t + 1][l] && (0 === t || r[t][l] != r[t - 1][l])) {
                                    for (var i = r[t][l], f = l; 0 < f && r[t][f] == r[t][f - 1];) f--;
                                    0 === f ? Z(e.find("tr").not(e.find("table tr")).get(t + 1)).prepend(i) : Z(r[t + 1][f - 1]).after(i)
                                }
                            }
                            var c = a.parent();
                            a.remove(), 0 === c.find("tr").length && c.remove(), r = O(e)
                        }
                        R(0, r.length - 1, 0, r[0].length - 1, e), 0 < s.min_i ? w.selection.setAtEnd(r[s.min_i - 1][0]) : w.selection.setAtEnd(r[0][0]), w.selection.restore(), w.popups.hide("table.edit")
                    }
                }
            }, insertColumn: function (i) {
                var e = Q();
                if (0 < e.length) {
                    var f, c = O(), t = $(c);
                    f = "before" == i ? t.min_j : t.max_j;
                    var l, d = 100 / c[0].length, p = 100 / (c[0].length + 1);
                    e.find("th, td").each(function () {
                        (l = Z(this)).data("old-width", l.outerWidth() / e.outerWidth() * 100)
                    }), e.find("tr").not(e.find("table tr")).each(function (e) {
                        for (var t, l = Z(this), a = 0, r = 0; a - 1 < f;) {
                            if (!(t = l.find("> th, > td").get(r))) {
                                t = null;
                                break
                            }
                            t == c[e][a] ? (a += parseInt(Z(t).attr("colspan"), 10) || 1, r++) : (a += parseInt(Z(c[e][a]).attr("colspan"), 10) || 1, "after" == i && (t = 0 === r ? -1 : l.find("> th, > td").get(r - 1)))
                        }
                        var s, n = Z(t);
                        if ("after" == i && f < a - 1 || "before" == i && 0 < f && c[e][f] == c[e][f - 1]) {
                            if (0 === e || 0 < e && c[e][f] != c[e - 1][f]) {
                                var o = parseInt(n.attr("colspan"), 10) + 1;
                                n.attr("colspan", o), n.css("width", (n.data("old-width") * p / d + p).toFixed(4) + "%"), n.removeData("old-width")
                            }
                        } else s = 0 < l.find("th").length ? '<th style="width: ' + p.toFixed(4) + '%;"><br></th>' : '<td style="width: ' + p.toFixed(4) + '%;"><br></td>', -1 == t ? l.prepend(s) : null == t ? l.append(s) : "before" == i ? n.before(s) : "after" == i && n.after(s)
                    }), e.find("th, td").each(function () {
                        (l = Z(this)).data("old-width") && (l.css("width", (l.data("old-width") * p / d).toFixed(4) + "%"), l.removeData("old-width"))
                    }), w.popups.isVisible("table.edit") && h()
                }
            }, deleteColumn: function () {
                var e = Q();
                if (0 < e.length) {
                    var t, l, a, r = O(), s = $(r);
                    if (0 === s.min_j && s.max_j == r[0].length - 1) u(); else {
                        var n = 0;
                        for (t = 0; t < r.length; t++) for (l = 0; l < r[0].length; l++) (a = Z(r[t][l])).hasClass("fr-selected-cell") || (a.data("old-width", a.outerWidth() / e.outerWidth() * 100), (l < s.min_j || l > s.max_j) && (n += a.outerWidth() / e.outerWidth() * 100));
                        for (n /= r.length, l = s.max_j; l >= s.min_j; l--) for (t = 0; t < r.length; t++) if (0 === t || r[t][l] != r[t - 1][l]) if (a = Z(r[t][l]), 1 < (parseInt(a.attr("colspan"), 10) || 1)) {
                            var o = parseInt(a.attr("colspan"), 10) - 1;
                            1 == o ? a.removeAttr("colspan") : a.attr("colspan", o), a.css("width", (100 * (a.data("old-width") - j(l, r)) / n).toFixed(4) + "%"), a.removeData("old-width")
                        } else {
                            var i = Z(a.parent().get(0));
                            a.remove(), 0 === i.find("> th, > td").length && (0 === i.prev().length || 0 === i.next().length || i.prev().find("> th[rowspan], > td[rowspan]").length < i.prev().find("> th, > td").length) && i.remove()
                        }
                        R(0, r.length - 1, 0, r[0].length - 1, e), 0 < s.min_j ? w.selection.setAtEnd(r[s.min_i][s.min_j - 1]) : w.selection.setAtEnd(r[s.min_i][0]), w.selection.restore(), w.popups.hide("table.edit"), e.find("th, td").each(function () {
                            (a = Z(this)).data("old-width") && (a.css("width", (100 * a.data("old-width") / n).toFixed(4) + "%"), a.removeData("old-width"))
                        })
                    }
                }
            }, mergeCells: function () {
                if (1 < J().length && (0 === w.$el.find("th.fr-selected-cell").length || 0 === w.$el.find("td.fr-selected-cell").length)) {
                    x();
                    var e, t, l = $(O()), a = w.$el.find(".fr-selected-cell"), r = Z(a[0]),
                        s = r.parent().find(".fr-selected-cell"), n = r.closest("table"), o = r.html(), i = 0;
                    for (e = 0; e < s.length; e++) i += Z(s[e]).outerWidth();
                    for (r.css("width", Math.min(100, i / n.outerWidth() * 100).toFixed(4) + "%"), l.min_j < l.max_j && r.attr("colspan", l.max_j - l.min_j + 1), l.min_i < l.max_i && r.attr("rowspan", l.max_i - l.min_i + 1), e = 1; e < a.length; e++) "<br>" != (t = Z(a[e])).html() && "" !== t.html() && (o += "<br>" + t.html()), t.remove();
                    r.html(o), w.selection.setAtEnd(r.get(0)), w.selection.restore(), w.toolbar.enable(), m(l.min_i, l.max_i, n);
                    var f = n.find("tr:empty");
                    for (e = f.length - 1; 0 <= e; e--) Z(f[e]).remove();
                    g(l.min_j, l.max_j, n), h()
                }
            }, splitCellVertically: function () {
                if (1 == J().length) {
                    var e = w.$el.find(".fr-selected-cell"), t = parseInt(e.attr("colspan"), 10) || 1,
                        l = e.parent().outerWidth(), a = e.outerWidth(), r = e.clone().html("<br>"), s = O(),
                        n = A(e.get(0), s);
                    if (1 < t) {
                        var o = Math.ceil(t / 2);
                        a = V(n.col, n.col + o - 1, s) / l * 100;
                        var i = V(n.col + o, n.col + t - 1, s) / l * 100;
                        1 < o ? e.attr("colspan", o) : e.removeAttr("colspan"), 1 < t - o ? r.attr("colspan", t - o) : r.removeAttr("colspan"), e.css("width", a.toFixed(4) + "%"), r.css("width", i.toFixed(4) + "%")
                    } else {
                        var f;
                        for (f = 0; f < s.length; f++) if (0 === f || s[f][n.col] != s[f - 1][n.col]) {
                            var c = Z(s[f][n.col]);
                            if (!c.is(e)) {
                                var d = (parseInt(c.attr("colspan"), 10) || 1) + 1;
                                c.attr("colspan", d)
                            }
                        }
                        a = a / l * 100 / 2, e.css("width", a.toFixed(4) + "%"), r.css("width", a.toFixed(4) + "%")
                    }
                    e.after(r), D(), w.popups.hide("table.edit")
                }
            }, splitCellHorizontally: function () {
                if (1 == J().length) {
                    var e = w.$el.find(".fr-selected-cell"), t = e.parent(), l = e.closest("table"),
                        a = parseInt(e.attr("rowspan"), 10), r = O(), s = A(e.get(0), r), n = e.clone().html("<br>");
                    if (1 < a) {
                        var o = Math.ceil(a / 2);
                        1 < o ? e.attr("rowspan", o) : e.removeAttr("rowspan"), 1 < a - o ? n.attr("rowspan", a - o) : n.removeAttr("rowspan");
                        for (var i = s.row + o, f = 0 === s.col ? s.col : s.col - 1; 0 <= f && (r[i][f] == r[i][f - 1] || 0 < i && r[i][f] == r[i - 1][f]);) f--;
                        -1 == f ? Z(l.find("tr").not(l.find("table tr")).get(i)).prepend(n) : Z(r[i][f]).after(n)
                    } else {
                        var c, d = Z("<tr>").append(n);
                        for (c = 0; c < r[0].length; c++) if (0 === c || r[s.row][c] != r[s.row][c - 1]) {
                            var p = Z(r[s.row][c]);
                            p.is(e) || p.attr("rowspan", (parseInt(p.attr("rowspan"), 10) || 1) + 1)
                        }
                        t.after(d)
                    }
                    D(), w.popups.hide("table.edit")
                }
            }, addHeader: function () {
                var e = Q();
                if (0 < e.length && 0 === e.find("th").length) {
                    var t, l = "<thead><tr>", a = 0;
                    for (e.find("tr:first > td").each(function () {
                        var e = Z(this);
                        a += parseInt(e.attr("colspan"), 10) || 1
                    }), t = 0; t < a; t++) l += "<th><br></th>";
                    l += "</tr></thead>", e.prepend(l), h()
                }
            }, removeHeader: function () {
                var e = Q(), t = e.find("thead");
                if (0 < t.length) if (0 === e.find("tbody tr").length) u(); else if (t.remove(), 0 < J().length) h(); else {
                    w.popups.hide("table.edit");
                    var l = e.find("tbody tr:first td:first").get(0);
                    l && (w.selection.setAtEnd(l), w.selection.restore())
                }
            }, setBackground: t, showInsertPopup: function () {
                var e = w.$tb.find('.fr-command[data-cmd="insertTable"]'), t = w.popups.get("table.insert");
                if (t || (t = c()), !t.hasClass("fr-active")) {
                    w.popups.refresh("table.insert"), w.popups.setContainer("table.insert", w.$tb);
                    var l = e.offset().left + e.outerWidth() / 2,
                        a = e.offset().top + (w.opts.toolbarBottom ? 10 : e.outerHeight() - 10);
                    w.popups.show("table.insert", l, a, e.outerHeight())
                }
            }, showEditPopup: h, showColorsPopup: f, back: function () {
                0 < J().length ? h() : (w.popups.hide("table.insert"), w.toolbar.showInline())
            }, verticalAlign: function (e) {
                w.$el.find(".fr-selected-cell").css("vertical-align", e)
            }, horizontalAlign: function (e) {
                w.$el.find(".fr-selected-cell").css("text-align", e)
            }, applyStyle: function (e, t, l, a) {
                if (0 < t.length) {
                    if (!l) {
                        var r = Object.keys(a);
                        r.splice(r.indexOf(e), 1), t.removeClass(r.join(" "))
                    }
                    t.toggleClass(e)
                }
            }, selectedTable: Q, selectedCells: J, customColor: function () {
                var e = w.popups.get("table.colors").find(".fr-table-colors-hex-layer input");
                e.length && t(e.val())
            }, selectCells: _
        }
    }, Z.FE.DefineIcon("insertTable", {NAME: "table"}), Z.FE.RegisterCommand("insertTable", {
        title: "Insert Table",
        undo: !1,
        focus: !0,
        refreshOnCallback: !1,
        popup: !0,
        callback: function () {
            this.popups.isVisible("table.insert") ? (this.$el.find(".fr-marker").length && (this.events.disableBlur(), this.selection.restore()), this.popups.hide("table.insert")) : this.table.showInsertPopup()
        },
        plugin: "table"
    }), Z.FE.RegisterCommand("tableInsert", {
        callback: function (e, t, l) {
            this.table.insert(t, l), this.popups.hide("table.insert")
        }
    }), Z.FE.DefineIcon("tableHeader", {
        NAME: "header",
        FA5NAME: "heading"
    }), Z.FE.RegisterCommand("tableHeader", {
        title: "Table Header", focus: !1, toggle: !0, callback: function () {
            this.popups.get("table.edit").find('.fr-command[data-cmd="tableHeader"]').hasClass("fr-active") ? this.table.removeHeader() : this.table.addHeader()
        }, refresh: function (e) {
            var t = this.table.selectedTable();
            0 < t.length && (0 === t.find("th").length ? e.removeClass("fr-active").attr("aria-pressed", !1) : e.addClass("fr-active").attr("aria-pressed", !0))
        }
    }), Z.FE.DefineIcon("tableRows", {NAME: "bars"}), Z.FE.RegisterCommand("tableRows", {
        type: "dropdown",
        focus: !1,
        title: "Row",
        options: {above: "Insert row above", below: "Insert row below", "delete": "Delete row"},
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', t = Z.FE.COMMANDS.tableRows.options;
            for (var l in t) t.hasOwnProperty(l) && (e += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="tableRows" data-param1="' + l + '" title="' + this.language.translate(t[l]) + '">' + this.language.translate(t[l]) + "</a></li>");
            return e += "</ul>"
        },
        callback: function (e, t) {
            "above" == t || "below" == t ? this.table.insertRow(t) : this.table.deleteRow()
        }
    }), Z.FE.DefineIcon("tableColumns", {NAME: "bars fa-rotate-90"}), Z.FE.RegisterCommand("tableColumns", {
        type: "dropdown",
        focus: !1,
        title: "Column",
        options: {before: "Insert column before", after: "Insert column after", "delete": "Delete column"},
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', t = Z.FE.COMMANDS.tableColumns.options;
            for (var l in t) t.hasOwnProperty(l) && (e += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="tableColumns" data-param1="' + l + '" title="' + this.language.translate(t[l]) + '">' + this.language.translate(t[l]) + "</a></li>");
            return e += "</ul>"
        },
        callback: function (e, t) {
            "before" == t || "after" == t ? this.table.insertColumn(t) : this.table.deleteColumn()
        }
    }), Z.FE.DefineIcon("tableCells", {NAME: "square-o", FA5NAME: "square"}), Z.FE.RegisterCommand("tableCells", {
        type: "dropdown",
        focus: !1,
        title: "Cell",
        options: {merge: "Merge cells", "vertical-split": "Vertical split", "horizontal-split": "Horizontal split"},
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', t = Z.FE.COMMANDS.tableCells.options;
            for (var l in t) t.hasOwnProperty(l) && (e += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="tableCells" data-param1="' + l + '" title="' + this.language.translate(t[l]) + '">' + this.language.translate(t[l]) + "</a></li>");
            return e += "</ul>"
        },
        callback: function (e, t) {
            "merge" == t ? this.table.mergeCells() : "vertical-split" == t ? this.table.splitCellVertically() : this.table.splitCellHorizontally()
        },
        refreshOnShow: function (e, t) {
            1 < this.$el.find(".fr-selected-cell").length ? (t.find('a[data-param1="vertical-split"]').addClass("fr-disabled").attr("aria-disabled", !0), t.find('a[data-param1="horizontal-split"]').addClass("fr-disabled").attr("aria-disabled", !0), t.find('a[data-param1="merge"]').removeClass("fr-disabled").attr("aria-disabled", !1)) : (t.find('a[data-param1="merge"]').addClass("fr-disabled").attr("aria-disabled", !0), t.find('a[data-param1="vertical-split"]').removeClass("fr-disabled").attr("aria-disabled", !1), t.find('a[data-param1="horizontal-split"]').removeClass("fr-disabled").attr("aria-disabled", !1))
        }
    }), Z.FE.DefineIcon("tableRemove", {NAME: "trash"}), Z.FE.RegisterCommand("tableRemove", {
        title: "Remove Table",
        focus: !1,
        callback: function () {
            this.table.remove()
        }
    }), Z.FE.DefineIcon("tableStyle", {NAME: "paint-brush"}), Z.FE.RegisterCommand("tableStyle", {
        title: "Table Style",
        type: "dropdown",
        focus: !1,
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', t = this.opts.tableStyles;
            for (var l in t) t.hasOwnProperty(l) && (e += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="tableStyle" data-param1="' + l + '" title="' + this.language.translate(t[l]) + '">' + this.language.translate(t[l]) + "</a></li>");
            return e += "</ul>"
        },
        callback: function (e, t) {
            this.table.applyStyle(t, this.$el.find(".fr-selected-cell").closest("table"), this.opts.tableMultipleStyles, this.opts.tableStyles)
        },
        refreshOnShow: function (e, t) {
            var l = this.$el.find(".fr-selected-cell").closest("table");
            l && t.find(".fr-command").each(function () {
                var e = Z(this).data("param1"), t = l.hasClass(e);
                Z(this).toggleClass("fr-active", t).attr("aria-selected", t)
            })
        }
    }), Z.FE.DefineIcon("tableCellBackground", {NAME: "tint"}), Z.FE.RegisterCommand("tableCellBackground", {
        title: "Cell Background",
        focus: !1,
        popup: !0,
        callback: function () {
            this.table.showColorsPopup()
        }
    }), Z.FE.RegisterCommand("tableCellBackgroundColor", {
        undo: !0, focus: !1, callback: function (e, t) {
            this.table.setBackground(t)
        }
    }), Z.FE.DefineIcon("tableBack", {NAME: "arrow-left"}), Z.FE.RegisterCommand("tableBack", {
        title: "Back",
        undo: !1,
        focus: !1,
        back: !0,
        callback: function () {
            this.table.back()
        },
        refresh: function (e) {
            0 !== this.table.selectedCells().length || this.opts.toolbarInline ? (e.removeClass("fr-hidden"), e.next(".fr-separator").removeClass("fr-hidden")) : (e.addClass("fr-hidden"), e.next(".fr-separator").addClass("fr-hidden"))
        }
    }), Z.FE.DefineIcon("tableCellVerticalAlign", {
        NAME: "arrows-v",
        FA5NAME: "arrows-alt-v"
    }), Z.FE.RegisterCommand("tableCellVerticalAlign", {
        type: "dropdown",
        focus: !1,
        title: "Vertical Align",
        options: {Top: "Align Top", Middle: "Align Middle", Bottom: "Align Bottom"},
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">',
                t = Z.FE.COMMANDS.tableCellVerticalAlign.options;
            for (var l in t) t.hasOwnProperty(l) && (e += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="tableCellVerticalAlign" data-param1="' + l.toLowerCase() + '" title="' + this.language.translate(t[l]) + '">' + this.language.translate(l) + "</a></li>");
            return e += "</ul>"
        },
        callback: function (e, t) {
            this.table.verticalAlign(t)
        },
        refreshOnShow: function (e, t) {
            t.find('.fr-command[data-param1="' + this.$el.find(".fr-selected-cell").css("vertical-align") + '"]').addClass("fr-active").attr("aria-selected", !0)
        }
    }), Z.FE.DefineIcon("tableCellHorizontalAlign", {NAME: "align-left"}), Z.FE.DefineIcon("align-left", {NAME: "align-left"}), Z.FE.DefineIcon("align-right", {NAME: "align-right"}), Z.FE.DefineIcon("align-center", {NAME: "align-center"}), Z.FE.DefineIcon("align-justify", {NAME: "align-justify"}), Z.FE.RegisterCommand("tableCellHorizontalAlign", {
        type: "dropdown",
        focus: !1,
        title: "Horizontal Align",
        options: {left: "Align Left", center: "Align Center", right: "Align Right", justify: "Align Justify"},
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">',
                t = Z.FE.COMMANDS.tableCellHorizontalAlign.options;
            for (var l in t) t.hasOwnProperty(l) && (e += '<li role="presentation"><a class="fr-command fr-title" tabIndex="-1" role="option" data-cmd="tableCellHorizontalAlign" data-param1="' + l + '" title="' + this.language.translate(t[l]) + '">' + this.icon.create("align-" + l) + '<span class="fr-sr-only">' + this.language.translate(t[l]) + "</span></a></li>");
            return e += "</ul>"
        },
        callback: function (e, t) {
            this.table.horizontalAlign(t)
        },
        refresh: function (e) {
            var t = this.table.selectedCells();
            t.length && e.find("> *:first").replaceWith(this.icon.create("align-" + this.helpers.getAlignment(Z(t[0]))))
        },
        refreshOnShow: function (e, t) {
            t.find('.fr-command[data-param1="' + this.helpers.getAlignment(this.$el.find(".fr-selected-cell:first")) + '"]').addClass("fr-active").attr("aria-selected", !0)
        }
    }), Z.FE.DefineIcon("tableCellStyle", {NAME: "magic"}), Z.FE.RegisterCommand("tableCellStyle", {
        title: "Cell Style",
        type: "dropdown",
        focus: !1,
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', t = this.opts.tableCellStyles;
            for (var l in t) t.hasOwnProperty(l) && (e += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="tableCellStyle" data-param1="' + l + '" title="' + this.language.translate(t[l]) + '">' + this.language.translate(t[l]) + "</a></li>");
            return e += "</ul>"
        },
        callback: function (e, t) {
            this.table.applyStyle(t, this.$el.find(".fr-selected-cell"), this.opts.tableCellMultipleStyles, this.opts.tableCellStyles)
        },
        refreshOnShow: function (e, t) {
            var l = this.$el.find(".fr-selected-cell:first");
            l && t.find(".fr-command").each(function () {
                var e = Z(this).data("param1"), t = l.hasClass(e);
                Z(this).toggleClass("fr-active", t).attr("aria-selected", t)
            })
        }
    }), Z.FE.RegisterCommand("tableCellBackgroundCustomColor", {
        title: "OK", undo: !0, callback: function () {
            this.table.customColor()
        }
    }), Z.FE.DefineIcon("tableColorRemove", {NAME: "eraser"})
});
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = function (root, jQuery) {
            if (jQuery === undefined) {
                if (typeof window !== 'undefined') {
                    jQuery = require('jquery');
                } else {
                    jQuery = require('jquery')(root);
                }
            }
            factory(jQuery);
            return jQuery;
        };
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';
    $.extend($.FE.POPUP_TEMPLATES, {
        'video.insert': '[_BUTTONS_][_BY_URL_LAYER_][_EMBED_LAYER_]',
        'video.edit': '[_BUTTONS_]',
        'video.size': '[_BUTTONS_][_SIZE_LAYER_]'
    })
    $.extend($.FE.DEFAULTS, {
        videoInsertButtons: ['videoBack', '|', 'videoByURL', 'videoEmbed'],
        videoEditButtons: ['videoDisplay', 'videoAlign', 'videoSize', 'videoRemove'],
        videoResize: true,
        videoSizeButtons: ['videoBack', '|'],
        videoSplitHTML: false,
        videoTextNear: true,
        videoDefaultAlign: 'center',
        videoDefaultDisplay: 'block',
        videoMove: true
    });
    $.FE.VIDEO_PROVIDERS = [{
        test_regex: /^.*((youtu.be)|(youtube.com))\/((v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))?\??v?=?([^#\&\?]*).*/,
        url_regex: /(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/)?([0-9a-zA-Z_\-]+)(.+)?/g,
        url_text: '//www.youtube.com/embed/$1',
        html: '<iframe width="640" height="360" src="{url}?wmode=opaque" frameborder="0" allowfullscreen></iframe>'
    }, {
        test_regex: /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/,
        url_regex: /(?:https?:\/\/)?(?:www\.)?(?:vimeo\.com)\/(?:channels\/[A-z]+\/|groups\/[A-z]+\/videos\/)?(.+)/g,
        url_text: '//player.vimeo.com/video/$1',
        html: '<iframe width="640" height="360" src="{url}" frameborder="0" allowfullscreen></iframe>'
    }, {
        test_regex: /^.+(dailymotion.com|dai.ly)\/(video|hub)?\/?([^_]+)[^#]*(#video=([^_&]+))?/,
        url_regex: /(?:https?:\/\/)?(?:www\.)?(?:dailymotion\.com|dai\.ly)\/(?:video|hub)?\/?(.+)/g,
        url_text: '//www.dailymotion.com/embed/video/$1',
        html: '<iframe width="640" height="360" src="{url}" frameborder="0" allowfullscreen></iframe>'
    }, {
        test_regex: /^.+(screen.yahoo.com)\/[^_&]+/,
        url_regex: '',
        url_text: '',
        html: '<iframe width="640" height="360" src="{url}?format=embed" frameborder="0" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true" allowtransparency="true"></iframe>'
    }, {
        test_regex: /^.+(rutube.ru)\/[^_&]+/,
        url_regex: /(?:https?:\/\/)?(?:www\.)?(?:rutube\.ru)\/(?:video)?\/?(.+)/g,
        url_text: '//rutube.ru/play/embed/$1',
        html: '<iframe width="640" height="360" src="{url}" frameborder="0" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true" allowtransparency="true"></iframe>'
    }];
    $.FE.VIDEO_EMBED_REGEX = /^\W*((<iframe.*><\/iframe>)|(<embed.*>))\W*$/i;
    $.FE.PLUGINS.video = function (editor) {
        var $overlay;
        var $handler;
        var $video_resizer;
        var $current_video;

        function _refreshInsertPopup() {
            var $popup = editor.popups.get('video.insert');
            var $url_input = $popup.find('.fr-video-by-url-layer input');
            $url_input.val('').trigger('change');
            var $embed_area = $popup.find('.fr-video-embed-layer textarea');
            $embed_area.val('').trigger('change');
        }

        function showInsertPopup() {
            var $btn = editor.$tb.find('.fr-command[data-cmd="insertVideo"]');
            var $popup = editor.popups.get('video.insert');
            if (!$popup) $popup = _initInsertPopup();
            if (!$popup.hasClass('fr-active')) {
                editor.popups.refresh('video.insert');
                editor.popups.setContainer('video.insert', editor.$tb);
                var left = $btn.offset().left + $btn.outerWidth() / 2;
                var top = $btn.offset().top + (editor.opts.toolbarBottom ? 10 : $btn.outerHeight() - 10);
                editor.popups.show('video.insert', left, top, $btn.outerHeight());
            }
        }

        function _showEditPopup() {
            var $popup = editor.popups.get('video.edit');
            if (!$popup) $popup = _initEditPopup();
            editor.popups.setContainer('video.edit', $(editor.opts.scrollableContainer));
            editor.popups.refresh('video.edit');
            var $video_obj = $current_video.find('iframe, embed, video');
            var left = $video_obj.offset().left + $video_obj.outerWidth() / 2;
            var top = $video_obj.offset().top + $video_obj.outerHeight();
            editor.popups.show('video.edit', left, top, $video_obj.outerHeight());
        }

        function _initInsertPopup(delayed) {
            if (delayed) {
                editor.popups.onRefresh('video.insert', _refreshInsertPopup);
                return true;
            }
            var video_buttons = '';
            if (editor.opts.videoInsertButtons.length > 1) {
                video_buttons = '<div class="fr-buttons">' + editor.button.buildList(editor.opts.videoInsertButtons) + '</div>';
            }
            var by_url_layer = '';
            if (editor.opts.videoInsertButtons.indexOf('videoByURL') >= 0) {
                by_url_layer = '<div class="fr-video-by-url-layer fr-layer fr-active" id="fr-video-by-url-layer-' + editor.id + '"><div class="fr-input-line"><input type="text" placeholder="http://" tabIndex="1"></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="videoInsertByURL" tabIndex="2">' + editor.language.translate('Insert') + '</button></div></div>'
            }
            var embed_layer = '';
            if (editor.opts.videoInsertButtons.indexOf('videoEmbed') >= 0) {
                embed_layer = '<div class="fr-video-embed-layer fr-layer" id="fr-video-embed-layer-' + editor.id + '"><div class="fr-input-line"><textarea type="text" placeholder="' + editor.language.translate('Embedded Code') + '" tabIndex="1" rows="5"></textarea></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="videoInsertEmbed" tabIndex="2">' + editor.language.translate('Insert') + '</button></div></div>'
            }
            var template = {buttons: video_buttons, by_url_layer: by_url_layer, embed_layer: embed_layer}
            var $popup = editor.popups.create('video.insert', template);
            return $popup;
        }

        function showLayer(name) {
            var $popup = editor.popups.get('video.insert');
            var left;
            var top;
            if (!$current_video && !editor.opts.toolbarInline) {
                var $btn = editor.$tb.find('.fr-command[data-cmd="insertVideo"]');
                left = $btn.offset().left + $btn.outerWidth() / 2;
                top = $btn.offset().top + (editor.opts.toolbarBottom ? 10 : $btn.outerHeight() - 10);
            }
            if (editor.opts.toolbarInline) {
                top = $popup.offset().top - editor.helpers.getPX($popup.css('margin-top'));
                if ($popup.hasClass('fr-above')) {
                    top += $popup.outerHeight();
                }
            }
            $popup.find('.fr-layer').removeClass('fr-active');
            $popup.find('.fr-' + name + '-layer').addClass('fr-active');
            editor.popups.show('video.insert', left, top, 0);
        }

        function refreshByURLButton($btn) {
            var $popup = editor.popups.get('video.insert');
            if ($popup.find('.fr-video-by-url-layer').hasClass('fr-active')) {
                $btn.addClass('fr-active');
            }
        }

        function refreshEmbedButton($btn) {
            var $popup = editor.popups.get('video.insert');
            if ($popup.find('.fr-video-embed-layer').hasClass('fr-active')) {
                $btn.addClass('fr-active');
            }
        }

        function insert(embedded_code) {
            editor.events.focus(true);
            editor.selection.restore();
            editor.html.insert('<span contenteditable="false" draggable="true" class="fr-jiv fr-video fr-dv' + (editor.opts.videoDefaultDisplay[0]) + (editor.opts.videoDefaultAlign != 'center' ? ' fr-fv' + editor.opts.videoDefaultAlign[0] : '') + '">' + embedded_code + '</span>', false, editor.opts.videoSplitHTML);
            editor.popups.hide('video.insert');
            var $video = editor.$el.find('.fr-jiv');
            $video.removeClass('fr-jiv');
            $video.toggleClass('fr-draggable', editor.opts.videoMove);
            editor.events.trigger('video.inserted', [$video]);
        }

        function insertByURL(link) {
            if (typeof link == 'undefined') {
                var $popup = editor.popups.get('video.insert');
                link = $popup.find('.fr-video-by-url-layer input[type="text"]').val() || '';
            }
            var video = null;
            if (editor.helpers.isURL(link)) {
                for (var i = 0; i < $.FE.VIDEO_PROVIDERS.length; i++) {
                    var vp = $.FE.VIDEO_PROVIDERS[i];
                    if (vp.test_regex.test(link)) {
                        video = link.replace(vp.url_regex, vp.url_text);
                        video = vp.html.replace(/\{url\}/, video);
                        break;
                    }
                }
            }
            if (video) {
                insert(video);
            } else {
                editor.events.trigger('video.linkError', [link]);
            }
        }

        function insertEmbed(code) {
            if (typeof code == 'undefined') {
                var $popup = editor.popups.get('video.insert');
                code = $popup.find('.fr-video-embed-layer textarea').val() || '';
            }
            if (code.length === 0 || !$.FE.VIDEO_EMBED_REGEX.test(code)) {
                editor.events.trigger('video.codeError', [code]);
            } else {
                insert(code);
            }
        }

        function _handlerMousedown(e) {
            if (!editor.core.sameInstance($video_resizer)) return true;
            e.preventDefault();
            e.stopPropagation();
            var c_x = e.pageX || (e.originalEvent.touches ? e.originalEvent.touches[0].pageX : null);
            var c_y = e.pageY || (e.originalEvent.touches ? e.originalEvent.touches[0].pageY : null);
            if (!c_x || !c_y) {
                return false;
            }
            if (!editor.undo.canDo()) editor.undo.saveStep();
            $handler = $(this);
            $handler.data('start-x', c_x);
            $handler.data('start-y', c_y);
            $overlay.show();
            editor.popups.hideAll();
            _unmarkExit();
        }

        function _handlerMousemove(e) {
            if (!editor.core.sameInstance($video_resizer)) return true;
            if ($handler) {
                e.preventDefault()
                var c_x = e.pageX || (e.originalEvent.touches ? e.originalEvent.touches[0].pageX : null);
                var c_y = e.pageY || (e.originalEvent.touches ? e.originalEvent.touches[0].pageY : null);
                if (!c_x || !c_y) {
                    return false;
                }
                var s_x = $handler.data('start-x');
                var s_y = $handler.data('start-y');
                $handler.data('start-x', c_x);
                $handler.data('start-y', c_y);
                var diff_x = c_x - s_x;
                var diff_y = c_y - s_y;
                var $video_obj = $current_video.find('iframe, embed, video');
                var width = $video_obj.width();
                var height = $video_obj.height();
                if ($handler.hasClass('fr-hnw') || $handler.hasClass('fr-hsw')) {
                    diff_x = 0 - diff_x;
                }
                if ($handler.hasClass('fr-hnw') || $handler.hasClass('fr-hne')) {
                    diff_y = 0 - diff_y;
                }
                $video_obj.css('width', width + diff_x);
                $video_obj.css('height', height + diff_y);
                $video_obj.removeAttr('width');
                $video_obj.removeAttr('height');
                _repositionResizer();
            }
        }

        function _handlerMouseup(e) {
            if (!editor.core.sameInstance($video_resizer)) return true;
            if ($handler && $current_video) {
                if (e) e.stopPropagation();
                $handler = null;
                $overlay.hide();
                _repositionResizer();
                _showEditPopup();
                editor.undo.saveStep();
            }
        }

        function _getHandler(pos) {
            return '<div class="fr-handler fr-h' + pos + '"></div>';
        }

        function _initResizer() {
            var doc;
            if (!editor.shared.$video_resizer) {
                editor.shared.$video_resizer = $('<div class="fr-video-resizer"></div>');
                $video_resizer = editor.shared.$video_resizer;
                editor.events.$on($video_resizer, 'mousedown', function (e) {
                    e.stopPropagation();
                }, true);
                if (editor.opts.videoResize) {
                    $video_resizer.append(_getHandler('nw') + _getHandler('ne') + _getHandler('sw') + _getHandler('se'));
                    editor.shared.$vid_overlay = $('<div class="fr-video-overlay"></div>');
                    $overlay = editor.shared.$vid_overlay;
                    doc = $video_resizer.get(0).ownerDocument;
                    $(doc).find('body').append($overlay);
                }
            } else {
                $video_resizer = editor.shared.$video_resizer;
                $overlay = editor.shared.$vid_overlay;
                editor.events.on('destroy', function () {
                    $video_resizer.removeClass('fr-active').appendTo($('body'));
                }, true);
            }
            editor.events.on('shared.destroy', function () {
                $video_resizer.html('').removeData().remove();
                $video_resizer = null;
                if (editor.opts.videoResize) {
                    $overlay.remove();
                    $overlay = null;
                }
            }, true);
            if (!editor.helpers.isMobile()) {
                editor.events.$on($(editor.o_win), 'resize.video', function () {
                    _exitEdit(true);
                });
            }
            if (editor.opts.videoResize) {
                doc = $video_resizer.get(0).ownerDocument;
                editor.events.$on($video_resizer, editor._mousedown, '.fr-handler', _handlerMousedown);
                editor.events.$on($(doc), editor._mousemove, _handlerMousemove);
                editor.events.$on($(doc.defaultView || doc.parentWindow), editor._mouseup, _handlerMouseup);
                editor.events.$on($overlay, 'mouseleave', _handlerMouseup);
            }
        }

        function _repositionResizer() {
            if (!$video_resizer) _initResizer();
            (editor.$wp || $(editor.opts.scrollableContainer)).append($video_resizer);
            $video_resizer.data('instance', editor);
            var $video_obj = $current_video.find('iframe, embed, video');
            $video_resizer.css('top', (editor.opts.iframe ? $video_obj.offset().top - 1 : $video_obj.offset().top - editor.$wp.offset().top - 1) + editor.$wp.scrollTop()).css('left', (editor.opts.iframe ? $video_obj.offset().left - 1 : $video_obj.offset().left - editor.$wp.offset().left - 1) + editor.$wp.scrollLeft()).css('width', $video_obj.outerWidth()).css('height', $video_obj.height()).addClass('fr-active')
        }

        var touchScroll;

        function _edit(e) {
            if (e && e.type == 'touchend' && touchScroll) {
                return true;
            }
            e.preventDefault();
            e.stopPropagation();
            if (editor.edit.isDisabled()) {
                return false;
            }
            for (var i = 0; i < $.FE.INSTANCES.length; i++) {
                if ($.FE.INSTANCES[i] != editor) {
                    $.FE.INSTANCES[i].events.trigger('video.hideResizer');
                }
            }
            editor.toolbar.disable();
            if (editor.helpers.isMobile()) {
                editor.events.disableBlur();
                editor.$el.blur();
                editor.events.enableBlur();
            }
            $current_video = $(this);
            $(this).addClass('fr-active');
            if (editor.opts.iframe) {
                editor.size.syncIframe();
            }
            _repositionResizer();
            _showEditPopup();
            editor.selection.clear();
            editor.button.bulkRefresh();
            editor.events.trigger('image.hideResizer');
        }

        function _exitEdit(force_exit) {
            if ($current_video && (_canExit() || force_exit === true)) {
                $video_resizer.removeClass('fr-active');
                editor.toolbar.enable();
                $current_video.removeClass('fr-active');
                $current_video = null;
                _unmarkExit();
            }
        }

        editor.shared.vid_exit_flag = false;

        function _markExit() {
            editor.shared.vid_exit_flag = true;
        }

        function _unmarkExit() {
            editor.shared.vid_exit_flag = false;
        }

        function _canExit() {
            return editor.shared.vid_exit_flag;
        }

        function _initEvents() {
            editor.events.on('mousedown window.mousedown', _markExit);
            editor.events.on('window.touchmove', _unmarkExit);
            editor.events.on('mouseup window.mouseup', _exitEdit);
            editor.events.on('commands.mousedown', function ($btn) {
                if ($btn.parents('.fr-toolbar').length > 0) {
                    _exitEdit();
                }
            });
            editor.events.on('blur video.hideResizer commands.undo commands.redo element.dropped', function () {
                _exitEdit(true);
            });
        }

        function _initEditPopup() {
            var video_buttons = '';
            if (editor.opts.videoEditButtons.length >= 1) {
                video_buttons += '<div class="fr-buttons">';
                video_buttons += editor.button.buildList(editor.opts.videoEditButtons);
                video_buttons += '</div>';
            }
            var template = {buttons: video_buttons}
            var $popup = editor.popups.create('video.edit', template);
            editor.events.$on(editor.$wp, 'scroll.video-edit', function () {
                if ($current_video && editor.popups.isVisible('video.edit')) {
                    _showEditPopup();
                }
            });
            return $popup;
        }

        function _refreshSizePopup() {
            if ($current_video) {
                var $popup = editor.popups.get('video.size');
                var $video_obj = $current_video.find('iframe, embed, video')
                $popup.find('input[name="width"]').val($video_obj.get(0).style.width || $video_obj.attr('width')).trigger('change');
                $popup.find('input[name="height"]').val($video_obj.get(0).style.height || $video_obj.attr('height')).trigger('change');
            }
        }

        function showSizePopup() {
            var $popup = editor.popups.get('video.size');
            if (!$popup) $popup = _initSizePopup();
            editor.popups.refresh('video.size');
            editor.popups.setContainer('video.size', $(editor.opts.scrollableContainer));
            var $video_obj = $current_video.find('iframe, embed, video')
            var left = $video_obj.offset().left + $video_obj.width() / 2;
            var top = $video_obj.offset().top + $video_obj.height();
            editor.popups.show('video.size', left, top, $video_obj.height());
        }

        function _initSizePopup(delayed) {
            if (delayed) {
                editor.popups.onRefresh('video.size', _refreshSizePopup);
                return true;
            }
            var video_buttons = '';
            video_buttons = '<div class="fr-buttons">' + editor.button.buildList(editor.opts.videoSizeButtons) + '</div>';
            var size_layer = '';
            size_layer = '<div class="fr-video-size-layer fr-layer fr-active" id="fr-video-size-layer-' + editor.id + '"><div class="fr-video-group"><div class="fr-input-line"><input type="text" name="width" placeholder="' + editor.language.translate('Width') + '" tabIndex="1"></div><div class="fr-input-line"><input type="text" name="height" placeholder="' + editor.language.translate('Height') + '" tabIndex="1"></div></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="videoSetSize" tabIndex="2">' + editor.language.translate('Update') + '</button></div></div>';
            var template = {buttons: video_buttons, size_layer: size_layer}
            var $popup = editor.popups.create('video.size', template);
            editor.events.$on(editor.$wp, 'scroll', function () {
                if ($current_video && editor.popups.isVisible('video.size')) {
                    showSizePopup();
                }
            });
            return $popup;
        }

        function align(val) {
            $current_video.removeClass('fr-fvr fr-fvl');
            if (val == 'left') {
                $current_video.addClass('fr-fvl');
            } else if (val == 'right') {
                $current_video.addClass('fr-fvr');
            }
            _repositionResizer();
            _showEditPopup();
        }

        function refreshAlign($btn) {
            if (!$current_video) return false;
            if ($current_video.hasClass('fr-fvl')) {
                $btn.find('> *:first').replaceWith(editor.icon.create('align-left'));
            } else if ($current_video.hasClass('fr-fvr')) {
                $btn.find('> *:first').replaceWith(editor.icon.create('align-right'));
            } else {
                $btn.find('> *:first').replaceWith(editor.icon.create('align-justify'));
            }
        }

        function refreshAlignOnShow($btn, $dropdown) {
            var alignment = 'justify';
            if ($current_video.hasClass('fr-fvl')) {
                alignment = 'left';
            } else if ($current_video.hasClass('fr-fvr')) {
                alignment = 'right';
            }
            $dropdown.find('.fr-command[data-param1="' + alignment + '"]').addClass('fr-active');
        }

        function display(val) {
            $current_video.removeClass('fr-dvi fr-dvb');
            if (val == 'inline') {
                $current_video.addClass('fr-dvi');
            } else if (val == 'block') {
                $current_video.addClass('fr-dvb');
            }
            _repositionResizer();
            _showEditPopup();
        }

        function refreshDisplayOnShow($btn, $dropdown) {
            var d = 'block';
            if ($current_video.hasClass('fr-dvi')) {
                d = 'inline';
            }
            $dropdown.find('.fr-command[data-param1="' + d + '"]').addClass('fr-active');
        }

        function remove() {
            if ($current_video) {
                if (editor.events.trigger('video.beforeRemove', [$current_video]) !== false) {
                    var $video = $current_video;
                    editor.popups.hideAll();
                    _exitEdit(true);
                    editor.selection.setBefore($video.get(0)) || editor.selection.setAfter($video.get(0));
                    $video.remove();
                    editor.selection.restore();
                    editor.html.fillEmptyBlocks();
                    editor.events.trigger('video.removed', [$video]);
                }
            }
        }

        function _convertStyleToClasses($video) {
            if (!$video.hasClass('fr-dvi') && !$video.hasClass('fr-dvb')) {
                var flt = $video.css('float');
                $video.css('float', 'none');
                if ($video.css('display') == 'block') {
                    $video.css('float', flt);
                    if (parseInt($video.css('margin-left'), 10) === 0 && ($video.attr('style') || '').indexOf('margin-right: auto') >= 0) {
                        $video.addClass('fr-fvl');
                    } else if (parseInt($video.css('margin-right'), 10) === 0 && ($video.attr('style') || '').indexOf('margin-left: auto') >= 0) {
                        $video.addClass('fr-fvr');
                    }
                    $video.addClass('fr-dvb');
                } else {
                    $video.css('float', flt);
                    if ($video.css('float') == 'left') {
                        $video.addClass('fr-fvl');
                    } else if ($video.css('float') == 'right') {
                        $video.addClass('fr-fvr');
                    }
                    $video.addClass('fr-dvi');
                }
                $video.css('margin', '');
                $video.css('float', '');
                $video.css('display', '');
                $video.css('z-index', '');
                $video.css('position', '');
                $video.css('overflow', '');
                $video.css('vertical-align', '');
            }
            if (!editor.opts.videoTextNear) {
                $video.removeClass('fr-dvi').addClass('fr-dvb');
            }
        }

        function _refreshVideoList() {
            editor.$el.find('video').filter(function () {
                return $(this).parents('span.fr-video').length === 0;
            }).wrap('<span class="fr-video" contenteditable="false"></span>');
            editor.$el.find('embed, iframe').filter(function () {
                if (editor.browser.safari && this.getAttribute('src')) {
                    this.setAttribute('src', this.src);
                }
                if ($(this).parents('span.fr-video').length > 0) return false;
                var link = $(this).attr('src');
                for (var i = 0; i < $.FE.VIDEO_PROVIDERS.length; i++) {
                    var vp = $.FE.VIDEO_PROVIDERS[i];
                    if (vp.test_regex.test(link)) {
                        return true;
                    }
                }
                return false;
            }).map(function () {
                return $(this).parents('object').length === 0 ? this : $(this).parents('object').get(0);
            }).wrap('<span class="fr-video" contenteditable="false"></span>');
            var videos = editor.$el.find('span.fr-video');
            for (var i = 0; i < videos.length; i++) {
                _convertStyleToClasses($(videos[i]));
            }
            videos.toggleClass('fr-draggable', editor.opts.videoMove);
        }

        function _init() {
            _initEvents();
            if (editor.helpers.isMobile()) {
                editor.events.$on(editor.$el, 'touchstart', 'span.fr-video', function () {
                    touchScroll = false;
                })
                editor.events.$on(editor.$el, 'touchmove', function () {
                    touchScroll = true;
                });
            }
            editor.events.on('html.set', _refreshVideoList);
            _refreshVideoList();
            editor.events.$on(editor.$el, 'mousedown', 'span.fr-video', function (e) {
                e.stopPropagation();
            })
            editor.events.$on(editor.$el, 'click touchend', 'span.fr-video', _edit);
            editor.events.on('keydown', function (e) {
                var key_code = e.which;
                if ($current_video && (key_code == $.FE.KEYCODE.BACKSPACE || key_code == $.FE.KEYCODE.DELETE)) {
                    e.preventDefault();
                    remove();
                    return false;
                }
                if ($current_video && key_code == $.FE.KEYCODE.ESC) {
                    _exitEdit(true);
                    e.preventDefault();
                    return false;
                }
                if ($current_video && !editor.keys.ctrlKey(e)) {
                    e.preventDefault();
                    return false;
                }
            }, true);
            editor.events.on('keydown', function () {
                editor.$el.find('span.fr-video:empty').remove();
            })
            _initInsertPopup(true);
            _initSizePopup(true);
        }

        function back() {
            if ($current_video) {
                $current_video.trigger('click');
            } else {
                editor.events.disableBlur();
                editor.selection.restore();
                editor.events.enableBlur();
                editor.popups.hide('video.insert');
                editor.toolbar.showInline();
            }
        }

        function setSize(width, height) {
            if ($current_video) {
                var $popup = editor.popups.get('video.size');
                var $video_obj = $current_video.find('iframe, embed, video');
                $video_obj.css('width', width || $popup.find('input[name="width"]').val());
                $video_obj.css('height', height || $popup.find('input[name="height"]').val());
                if ($video_obj.get(0).style.width) $video_obj.removeAttr('width');
                if ($video_obj.get(0).style.height) $video_obj.removeAttr('height');
                $popup.find('input').blur();
                setTimeout(function () {
                    $current_video.trigger('click');
                }, editor.helpers.isAndroid() ? 50 : 0);
            }
        }

        function get() {
            return $current_video;
        }

        return {
            _init: _init,
            showInsertPopup: showInsertPopup,
            showLayer: showLayer,
            refreshByURLButton: refreshByURLButton,
            refreshEmbedButton: refreshEmbedButton,
            insertByURL: insertByURL,
            insertEmbed: insertEmbed,
            insert: insert,
            align: align,
            refreshAlign: refreshAlign,
            refreshAlignOnShow: refreshAlignOnShow,
            display: display,
            refreshDisplayOnShow: refreshDisplayOnShow,
            remove: remove,
            showSizePopup: showSizePopup,
            back: back,
            setSize: setSize,
            get: get
        }
    }
    $.FE.RegisterCommand('insertVideo', {
        title: 'Insert Video',
        undo: false,
        focus: true,
        refreshAfterCallback: false,
        popup: true,
        callback: function () {
            if (!this.popups.isVisible('video.insert')) {
                this.video.showInsertPopup();
            } else {
                if (this.$el.find('.fr-marker')) {
                    this.events.disableBlur();
                    this.selection.restore();
                }
                this.popups.hide('video.insert');
            }
        },
        plugin: 'video'
    })
    $.FE.DefineIcon('insertVideo', {NAME: 'video-camera'});
    $.FE.DefineIcon('videoByURL', {NAME: 'link'});
    $.FE.RegisterCommand('videoByURL', {
        title: 'By URL', undo: false, focus: false, callback: function () {
            this.video.showLayer('video-by-url');
        }, refresh: function ($btn) {
            this.video.refreshByURLButton($btn);
        }
    })
    $.FE.DefineIcon('videoEmbed', {NAME: 'code'});
    $.FE.RegisterCommand('videoEmbed', {
        title: 'Embedded Code', undo: false, focus: false, callback: function () {
            this.video.showLayer('video-embed');
        }, refresh: function ($btn) {
            this.video.refreshEmbedButton($btn);
        }
    })
    $.FE.RegisterCommand('videoInsertByURL', {
        undo: true, focus: true, callback: function () {
            this.video.insertByURL();
        }
    })
    $.FE.RegisterCommand('videoInsertEmbed', {
        undo: true, focus: true, callback: function () {
            this.video.insertEmbed();
        }
    })
    $.FE.DefineIcon('videoDisplay', {NAME: 'star'})
    $.FE.RegisterCommand('videoDisplay', {
        title: 'Display',
        type: 'dropdown',
        options: {inline: 'Inline', block: 'Break Text'},
        callback: function (cmd, val) {
            this.video.display(val);
        },
        refresh: function ($btn) {
            if (!this.opts.videoTextNear) $btn.addClass('fr-hidden');
        },
        refreshOnShow: function ($btn, $dropdown) {
            this.video.refreshDisplayOnShow($btn, $dropdown);
        }
    })
    $.FE.DefineIcon('videoAlign', {NAME: 'align-center'})
    $.FE.RegisterCommand('videoAlign', {
        type: 'dropdown',
        title: 'Align',
        options: {left: 'Align Left', justify: 'None', right: 'Align Right'},
        html: function () {
            var c = '<ul class="fr-dropdown-list">';
            var options = $.FE.COMMANDS.videoAlign.options;
            for (var val in options) {
                if (options.hasOwnProperty(val)) {
                    c += '<li><a class="fr-command fr-title" data-cmd="videoAlign" data-param1="' + val + '" title="' + this.language.translate(options[val]) + '">' + this.icon.create('align-' + val) + '</a></li>';
                }
            }
            c += '</ul>';
            return c;
        },
        callback: function (cmd, val) {
            this.video.align(val);
        },
        refresh: function ($btn) {
            this.video.refreshAlign($btn);
        },
        refreshOnShow: function ($btn, $dropdown) {
            this.video.refreshAlignOnShow($btn, $dropdown);
        }
    })
    $.FE.DefineIcon('videoRemove', {NAME: 'trash'})
    $.FE.RegisterCommand('videoRemove', {
        title: 'Remove', callback: function () {
            this.video.remove();
        }
    })
    $.FE.DefineIcon('videoSize', {NAME: 'arrows-alt'})
    $.FE.RegisterCommand('videoSize', {
        undo: false, focus: false, title: 'Change Size', callback: function () {
            this.video.showSizePopup();
        }
    });
    $.FE.DefineIcon('videoBack', {NAME: 'arrow-left'});
    $.FE.RegisterCommand('videoBack', {
        title: 'Back', undo: false, focus: false, back: true, callback: function () {
            this.video.back();
        }, refresh: function ($btn) {
            var $current_video = this.video.get();
            if (!$current_video && !this.opts.toolbarInline) {
                $btn.addClass('fr-hidden');
                $btn.next('.fr-separator').addClass('fr-hidden');
            } else {
                $btn.removeClass('fr-hidden');
                $btn.next('.fr-separator').removeClass('fr-hidden');
            }
        }
    });
    $.FE.RegisterCommand('videoSetSize', {
        undo: true, focus: false, callback: function () {
            this.video.setSize();
        }
    })
}));
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = function (root, jQuery) {
            if (jQuery === undefined) {
                if (typeof window !== 'undefined') {
                    jQuery = require('jquery');
                } else {
                    jQuery = require('jquery')(root);
                }
            }
            factory(jQuery);
            return jQuery;
        };
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';
    $.extend($.FE.POPUP_TEMPLATES, {
        'audio.insert': '[_BUTTONS_][_BY_URL_LAYER_][_EMBED_LAYER_]',
        'audio.edit': '[_BUTTONS_]',
        'audio.size': '[_BUTTONS_][_SIZE_LAYER_]'
    })
    $.extend($.FE.DEFAULTS, {
        audioInsertButtons: ['audioBack', '|', 'audioByURL', 'audioEmbed'],
        audioEditButtons: ['audioDisplay', 'audioAlign', 'audioSize', 'audioRemove'],
        audioResize: true,
        audioSizeButtons: ['audioBack', '|'],
        audioSplitHTML: false,
        audioTextNear: true,
        audioDefaultAlign: 'center',
        audioDefaultDisplay: 'block',
        audioMove: true
    });
    $.FE.VIDEO_PROVIDERS = [];
    $.FE.VIDEO_EMBED_REGEX = /^\W*((<iframe.*><\/iframe>)|(<embed.*>))\W*$/i;
    $.FE.PLUGINS.audio = function (editor) {
        var $overlay;
        var $handler;
        var $audio_resizer;
        var $current_audio;

        function _refreshInsertPopup() {
            var $popup = editor.popups.get('audio.insert');
            var $url_input = $popup.find('.fr-audio-by-url-layer input');
            $url_input.val('').trigger('change');
            var $embed_area = $popup.find('.fr-audio-embed-layer textarea');
            $embed_area.val('').trigger('change');
        }

        function showInsertPopup() {
            var $btn = editor.$tb.find('.fr-command[data-cmd="insertAudio"]');
            var $popup = editor.popups.get('audio.insert');
            if (!$popup) $popup = _initInsertPopup();
            if (!$popup.hasClass('fr-active')) {
                editor.popups.refresh('audio.insert');
                editor.popups.setContainer('audio.insert', editor.$tb);
                var left = $btn.offset().left + $btn.outerWidth() / 2;
                var top = $btn.offset().top + (editor.opts.toolbarBottom ? 10 : $btn.outerHeight() - 10);
                editor.popups.show('audio.insert', left, top, $btn.outerHeight());
            }
        }

        function _showEditPopup() {
            var $popup = editor.popups.get('audio.edit');
            if (!$popup) $popup = _initEditPopup();
            editor.popups.setContainer('audio.edit', $(editor.opts.scrollableContainer));
            editor.popups.refresh('audio.edit');
            var $audio_obj = $current_audio.find('iframe, embed, audio');
            var left = $audio_obj.offset().left + $audio_obj.outerWidth() / 2;
            var top = $audio_obj.offset().top + $audio_obj.outerHeight();
            editor.popups.show('audio.edit', left, top, $audio_obj.outerHeight());
        }

        function _initInsertPopup(delayed) {
            if (delayed) {
                editor.popups.onRefresh('audio.insert', _refreshInsertPopup);
                return true;
            }
            var audio_buttons = '';
            if (editor.opts.audioInsertButtons.length > 1) {
                audio_buttons = '<div class="fr-buttons">' + editor.button.buildList(editor.opts.audioInsertButtons) + '</div>';
            }
            var by_url_layer = '';
            if (editor.opts.audioInsertButtons.indexOf('audioByURL') >= 0) {
                by_url_layer = '<div class="fr-audio-by-url-layer fr-layer fr-active" id="fr-audio-by-url-layer-' + editor.id + '"><div class="fr-input-line"><input type="text" placeholder="http://" tabIndex="1"></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="audioInsertByURL" tabIndex="2">' + editor.language.translate('Insert') + '</button></div></div>'
            }
            var embed_layer = '';
            if (editor.opts.audioInsertButtons.indexOf('audioEmbed') >= 0) {
                embed_layer = '<div class="fr-audio-embed-layer fr-layer" id="fr-audio-embed-layer-' + editor.id + '"><div class="fr-input-line"><textarea type="text" placeholder="' + editor.language.translate('Embedded Code') + '" tabIndex="1" rows="5"></textarea></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="audioInsertEmbed" tabIndex="2">' + editor.language.translate('Insert') + '</button></div></div>'
            }
            var template = {buttons: audio_buttons, by_url_layer: by_url_layer, embed_layer: embed_layer}
            var $popup = editor.popups.create('audio.insert', template);
            return $popup;
        }

        function showLayer(name) {
            var $popup = editor.popups.get('audio.insert');
            var left;
            var top;
            if (!$current_audio && !editor.opts.toolbarInline) {
                var $btn = editor.$tb.find('.fr-command[data-cmd="insertAudio"]');
                left = $btn.offset().left + $btn.outerWidth() / 2;
                top = $btn.offset().top + (editor.opts.toolbarBottom ? 10 : $btn.outerHeight() - 10);
            }
            if (editor.opts.toolbarInline) {
                top = $popup.offset().top - editor.helpers.getPX($popup.css('margin-top'));
                if ($popup.hasClass('fr-above')) {
                    top += $popup.outerHeight();
                }
            }
            $popup.find('.fr-layer').removeClass('fr-active');
            $popup.find('.fr-' + name + '-layer').addClass('fr-active');
            editor.popups.show('audio.insert', left, top, 0);
        }

        function refreshByURLButton($btn) {
            var $popup = editor.popups.get('audio.insert');
            if ($popup.find('.fr-audio-by-url-layer').hasClass('fr-active')) {
                $btn.addClass('fr-active');
            }
        }

        function refreshEmbedButton($btn) {
            var $popup = editor.popups.get('audio.insert');
            if ($popup.find('.fr-audio-embed-layer').hasClass('fr-active')) {
                $btn.addClass('fr-active');
            }
        }

        function insert(embedded_code) {
            editor.events.focus(true);
            editor.selection.restore();
            editor.html.insert('<span contenteditable="false" draggable="true" class="fr-jiv fr-video fr-dv' + (editor.opts.audioDefaultDisplay[0]) + (editor.opts.audioDefaultAlign != 'center' ? ' fr-fv' + editor.opts.audioDefaultAlign[0] : '') + '">' + embedded_code + '</span>', false, editor.opts.audioSplitHTML);
            editor.popups.hide('audio.insert');
            var $audio = editor.$el.find('.fr-jiv');
            $audio.removeClass('fr-jiv');
            $audio.toggleClass('fr-draggable', editor.opts.audioMove);
            editor.events.trigger('audio.inserted', [$audio]);
        }

        function insertByURL(link) {
            if (typeof link == 'undefined') {
                var $popup = editor.popups.get('audio.insert');
                link = $popup.find('.fr-audio-by-url-layer input[type="text"]').val() || '';
            }
            var audio = null;
            if (editor.helpers.isURL(link)) {
                for (var i = 0; i < $.FE.VIDEO_PROVIDERS.length; i++) {
                    var vp = $.FE.VIDEO_PROVIDERS[i];
                    if (vp.test_regex.test(link)) {
                        audio = link.replace(vp.url_regex, vp.url_text);
                        audio = vp.html.replace(/\{url\}/, audio);
                        break;
                    }
                }
            }
            if (audio) {
                insert(audio);
            } else {
                editor.events.trigger('audio.linkError', [link]);
            }
        }

        function insertEmbed(code) {
            if (typeof code == 'undefined') {
                var $popup = editor.popups.get('audio.insert');
                code = $popup.find('.fr-audio-embed-layer textarea').val() || '';
            }
            if (code.length === 0 || !$.FE.VIDEO_EMBED_REGEX.test(code)) {
                editor.events.trigger('audio.codeError', [code]);
            } else {
                insert(code);
            }
        }

        function _handlerMousedown(e) {
            if (!editor.core.sameInstance($audio_resizer)) return true;
            e.preventDefault();
            e.stopPropagation();
            var c_x = e.pageX || (e.originalEvent.touches ? e.originalEvent.touches[0].pageX : null);
            var c_y = e.pageY || (e.originalEvent.touches ? e.originalEvent.touches[0].pageY : null);
            if (!c_x || !c_y) {
                return false;
            }
            if (!editor.undo.canDo()) editor.undo.saveStep();
            $handler = $(this);
            $handler.data('start-x', c_x);
            $handler.data('start-y', c_y);
            $overlay.show();
            editor.popups.hideAll();
            _unmarkExit();
        }

        function _handlerMousemove(e) {
            if (!editor.core.sameInstance($audio_resizer)) return true;
            if ($handler) {
                e.preventDefault()
                var c_x = e.pageX || (e.originalEvent.touches ? e.originalEvent.touches[0].pageX : null);
                var c_y = e.pageY || (e.originalEvent.touches ? e.originalEvent.touches[0].pageY : null);
                if (!c_x || !c_y) {
                    return false;
                }
                var s_x = $handler.data('start-x');
                var s_y = $handler.data('start-y');
                $handler.data('start-x', c_x);
                $handler.data('start-y', c_y);
                var diff_x = c_x - s_x;
                var diff_y = c_y - s_y;
                var $audio_obj = $current_audio.find('iframe, embed, audio');
                var width = $audio_obj.width();
                var height = $audio_obj.height();
                if ($handler.hasClass('fr-hnw') || $handler.hasClass('fr-hsw')) {
                    diff_x = 0 - diff_x;
                }
                if ($handler.hasClass('fr-hnw') || $handler.hasClass('fr-hne')) {
                    diff_y = 0 - diff_y;
                }
                $audio_obj.css('width', width + diff_x);
                $audio_obj.css('height', height + diff_y);
                $audio_obj.removeAttr('width');
                $audio_obj.removeAttr('height');
                _repositionResizer();
            }
        }

        function _handlerMouseup(e) {
            if (!editor.core.sameInstance($audio_resizer)) return true;
            if ($handler && $current_audio) {
                if (e) e.stopPropagation();
                $handler = null;
                $overlay.hide();
                _repositionResizer();
                _showEditPopup();
                editor.undo.saveStep();
            }
        }

        function _getHandler(pos) {
            return '<div class="fr-handler fr-h' + pos + '"></div>';
        }

        function _initResizer() {
            var doc;
            if (!editor.shared.$audio_resizer) {
                editor.shared.$audio_resizer = $('<div class="fr-video-resizer"></div>');
                $audio_resizer = editor.shared.$audio_resizer;
                editor.events.$on($audio_resizer, 'mousedown', function (e) {
                    e.stopPropagation();
                }, true);
                if (editor.opts.audioResize) {
                    $audio_resizer.append(_getHandler('nw') + _getHandler('ne') + _getHandler('sw') + _getHandler('se'));
                    editor.shared.$audio_overlay = $('<div class="fr-video-overlay"></div>');
                    $overlay = editor.shared.$audio_overlay;
                    doc = $audio_resizer.get(0).ownerDocument;
                    $(doc).find('body').append($overlay);
                }
            } else {
                $audio_resizer = editor.shared.$audio_resizer;
                $overlay = editor.shared.$audio_overlay;
                editor.events.on('destroy', function () {
                    $audio_resizer.removeClass('fr-active').appendTo($('body'));
                }, true);
            }
            editor.events.on('shared.destroy', function () {
                $audio_resizer.html('').removeData().remove();
                $audio_resizer = null;
                if (editor.opts.audioResize) {
                    $overlay.remove();
                    $overlay = null;
                }
            }, true);
            if (!editor.helpers.isMobile()) {
                editor.events.$on($(editor.o_win), 'resize.audio', function () {
                    _exitEdit(true);
                });
            }
            if (editor.opts.audioResize) {
                doc = $audio_resizer.get(0).ownerDocument;
                editor.events.$on($audio_resizer, editor._mousedown, '.fr-handler', _handlerMousedown);
                editor.events.$on($(doc), editor._mousemove, _handlerMousemove);
                editor.events.$on($(doc.defaultView || doc.parentWindow), editor._mouseup, _handlerMouseup);
                editor.events.$on($overlay, 'mouseleave', _handlerMouseup);
            }
        }

        function _repositionResizer() {
            if (!$audio_resizer) _initResizer();
            (editor.$wp || $(editor.opts.scrollableContainer)).append($audio_resizer);
            $audio_resizer.data('instance', editor);
            var $audio_obj = $current_audio.find('iframe, embed, audio');
            $audio_resizer.css('top', (editor.opts.iframe ? $audio_obj.offset().top - 1 : $audio_obj.offset().top - editor.$wp.offset().top - 1) + editor.$wp.scrollTop()).css('left', (editor.opts.iframe ? $audio_obj.offset().left - 1 : $audio_obj.offset().left - editor.$wp.offset().left - 1) + editor.$wp.scrollLeft()).css('width', $audio_obj.outerWidth()).css('height', $audio_obj.height()).addClass('fr-active')
        }

        var touchScroll;

        function _edit(e) {
            if (e && e.type == 'touchend' && touchScroll) {
                return true;
            }
            e.preventDefault();
            e.stopPropagation();
            if (editor.edit.isDisabled()) {
                return false;
            }
            for (var i = 0; i < $.FE.INSTANCES.length; i++) {
                if ($.FE.INSTANCES[i] != editor) {
                    $.FE.INSTANCES[i].events.trigger('audio.hideResizer');
                }
            }
            editor.toolbar.disable();
            if (editor.helpers.isMobile()) {
                editor.events.disableBlur();
                editor.$el.blur();
                editor.events.enableBlur();
            }
            $current_audio = $(this);
            $(this).addClass('fr-active');
            if (editor.opts.iframe) {
                editor.size.syncIframe();
            }
            _repositionResizer();
            _showEditPopup();
            editor.selection.clear();
            editor.button.bulkRefresh();
            editor.events.trigger('image.hideResizer');
        }

        function _exitEdit(force_exit) {
            if ($current_audio && (_canExit() || force_exit === true)) {
                $audio_resizer.removeClass('fr-active');
                editor.toolbar.enable();
                $current_audio.removeClass('fr-active');
                $current_audio = null;
                _unmarkExit();
            }
        }

        editor.shared.audio_exit_flag = false;

        function _markExit() {
            editor.shared.audio_exit_flag = true;
        }

        function _unmarkExit() {
            editor.shared.audio_exit_flag = false;
        }

        function _canExit() {
            return editor.shared.audio_exit_flag;
        }

        function _initEvents() {
            editor.events.on('mousedown window.mousedown', _markExit);
            editor.events.on('window.touchmove', _unmarkExit);
            editor.events.on('mouseup window.mouseup', _exitEdit);
            editor.events.on('commands.mousedown', function ($btn) {
                if ($btn.parents('.fr-toolbar').length > 0) {
                    _exitEdit();
                }
            });
            editor.events.on('blur audio.hideResizer commands.undo commands.redo element.dropped', function () {
                _exitEdit(true);
            });
        }

        function _initEditPopup() {
            var audio_buttons = '';
            if (editor.opts.audioEditButtons.length >= 1) {
                audio_buttons += '<div class="fr-buttons">';
                audio_buttons += editor.button.buildList(editor.opts.audioEditButtons);
                audio_buttons += '</div>';
            }
            var template = {buttons: audio_buttons}
            var $popup = editor.popups.create('audio.edit', template);
            editor.events.$on(editor.$wp, 'scroll.audio-edit', function () {
                if ($current_audio && editor.popups.isVisible('audio.edit')) {
                    _showEditPopup();
                }
            });
            return $popup;
        }

        function _refreshSizePopup() {
            if ($current_audio) {
                var $popup = editor.popups.get('audio.size');
                var $audio_obj = $current_audio.find('iframe, embed, audio')
                $popup.find('input[name="width"]').val($audio_obj.get(0).style.width || $audio_obj.attr('width')).trigger('change');
                $popup.find('input[name="height"]').val($audio_obj.get(0).style.height || $audio_obj.attr('height')).trigger('change');
            }
        }

        function showSizePopup() {
            var $popup = editor.popups.get('audio.size');
            if (!$popup) $popup = _initSizePopup();
            editor.popups.refresh('audio.size');
            editor.popups.setContainer('audio.size', $(editor.opts.scrollableContainer));
            var $audio_obj = $current_audio.find('iframe, embed, audio')
            var left = $audio_obj.offset().left + $audio_obj.width() / 2;
            var top = $audio_obj.offset().top + $audio_obj.height();
            editor.popups.show('audio.size', left, top, $audio_obj.height());
        }

        function _initSizePopup(delayed) {
            if (delayed) {
                editor.popups.onRefresh('audio.size', _refreshSizePopup);
                return true;
            }
            var audio_buttons = '';
            audio_buttons = '<div class="fr-buttons">' + editor.button.buildList(editor.opts.audioSizeButtons) + '</div>';
            var size_layer = '';
            size_layer = '<div class="fr-video-size-layer fr-layer fr-active" id="fr-video-size-layer-' + editor.id + '"><div class="fr-audio-group"><div class="fr-input-line"><input type="text" name="width" placeholder="' + editor.language.translate('Width') + '" tabIndex="1"></div><div class="fr-input-line"><input type="text" name="height" placeholder="' + editor.language.translate('Height') + '" tabIndex="1"></div></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="audioSetSize" tabIndex="2">' + editor.language.translate('Update') + '</button></div></div>';
            var template = {buttons: audio_buttons, size_layer: size_layer}
            var $popup = editor.popups.create('audio.size', template);
            editor.events.$on(editor.$wp, 'scroll', function () {
                if ($current_audio && editor.popups.isVisible('audio.size')) {
                    showSizePopup();
                }
            });
            return $popup;
        }

        function align(val) {
            $current_audio.removeClass('fr-fvr fr-fvl');
            if (val == 'left') {
                $current_audio.addClass('fr-fvl');
            } else if (val == 'right') {
                $current_audio.addClass('fr-fvr');
            }
            _repositionResizer();
            _showEditPopup();
        }

        function refreshAlign($btn) {
            if (!$current_audio) return false;
            if ($current_audio.hasClass('fr-fvl')) {
                $btn.find('> *:first').replaceWith(editor.icon.create('align-left'));
            } else if ($current_audio.hasClass('fr-fvr')) {
                $btn.find('> *:first').replaceWith(editor.icon.create('align-right'));
            } else {
                $btn.find('> *:first').replaceWith(editor.icon.create('align-justify'));
            }
        }

        function refreshAlignOnShow($btn, $dropdown) {
            var alignment = 'justify';
            if ($current_audio.hasClass('fr-fvl')) {
                alignment = 'left';
            } else if ($current_audio.hasClass('fr-fvr')) {
                alignment = 'right';
            }
            $dropdown.find('.fr-command[data-param1="' + alignment + '"]').addClass('fr-active');
        }

        function display(val) {
            $current_audio.removeClass('fr-dvi fr-dvb');
            if (val == 'inline') {
                $current_audio.addClass('fr-dvi');
            } else if (val == 'block') {
                $current_audio.addClass('fr-dvb');
            }
            _repositionResizer();
            _showEditPopup();
        }

        function refreshDisplayOnShow($btn, $dropdown) {
            var d = 'block';
            if ($current_audio.hasClass('fr-dvi')) {
                d = 'inline';
            }
            $dropdown.find('.fr-command[data-param1="' + d + '"]').addClass('fr-active');
        }

        function remove() {
            if ($current_audio) {
                if (editor.events.trigger('audio.beforeRemove', [$current_audio]) !== false) {
                    var $audio = $current_audio;
                    editor.popups.hideAll();
                    _exitEdit(true);
                    editor.selection.setBefore($audio.get(0)) || editor.selection.setAfter($audio.get(0));
                    $audio.remove();
                    editor.selection.restore();
                    editor.html.fillEmptyBlocks();
                    editor.events.trigger('audio.removed', [$audio]);
                }
            }
        }

        function _convertStyleToClasses($audio) {
            if (!$audio.hasClass('fr-dvi') && !$audio.hasClass('fr-dvb')) {
                var flt = $audio.css('float');
                $audio.css('float', 'none');
                if ($audio.css('display') == 'block') {
                    $audio.css('float', flt);
                    if (parseInt($audio.css('margin-left'), 10) === 0 && ($audio.attr('style') || '').indexOf('margin-right: auto') >= 0) {
                        $audio.addClass('fr-fvl');
                    } else if (parseInt($audio.css('margin-right'), 10) === 0 && ($audio.attr('style') || '').indexOf('margin-left: auto') >= 0) {
                        $audio.addClass('fr-fvr');
                    }
                    $audio.addClass('fr-dvb');
                } else {
                    $audio.css('float', flt);
                    if ($audio.css('float') == 'left') {
                        $audio.addClass('fr-fvl');
                    } else if ($audio.css('float') == 'right') {
                        $audio.addClass('fr-fvr');
                    }
                    $audio.addClass('fr-dvi');
                }
                $audio.css('margin', '');
                $audio.css('float', '');
                $audio.css('display', '');
                $audio.css('z-index', '');
                $audio.css('position', '');
                $audio.css('overflow', '');
                $audio.css('vertical-align', '');
            }
            if (!editor.opts.audioTextNear) {
                $audio.removeClass('fr-dvi').addClass('fr-dvb');
            }
        }

        function _refreshAudioList() {
            editor.$el.find('audio').filter(function () {
                return $(this).parents('span.fr-video').length === 0;
            }).wrap('<span class="fr-video" contenteditable="false"></span>');
            editor.$el.find('embed, iframe').filter(function () {
                if (editor.browser.safari && this.getAttribute('src')) {
                    this.setAttribute('src', this.src);
                }
                if ($(this).parents('span.fr-video').length > 0) return false;
                var link = $(this).attr('src');
                for (var i = 0; i < $.FE.VIDEO_PROVIDERS.length; i++) {
                    var vp = $.FE.VIDEO_PROVIDERS[i];
                    if (vp.test_regex.test(link)) {
                        return true;
                    }
                }
                return false;
            }).map(function () {
                return $(this).parents('object').length === 0 ? this : $(this).parents('object').get(0);
            }).wrap('<span class="fr-video" contenteditable="false"></span>');
            var audios = editor.$el.find('span.fr-video');
            for (var i = 0; i < audios.length; i++) {
                _convertStyleToClasses($(audios[i]));
            }
            audios.toggleClass('fr-draggable', editor.opts.audioMove);
        }

        function _init() {
            _initEvents();
            if (editor.helpers.isMobile()) {
                editor.events.$on(editor.$el, 'touchstart', 'span.fr-video', function () {
                    touchScroll = false;
                })
                editor.events.$on(editor.$el, 'touchmove', function () {
                    touchScroll = true;
                });
            }
            editor.events.on('html.set', _refreshAudioList);
            _refreshAudioList();
            editor.events.$on(editor.$el, 'mousedown', 'span.fr-video', function (e) {
                e.stopPropagation();
            })
            editor.events.$on(editor.$el, 'click touchend', 'span.fr-video', _edit);
            editor.events.on('keydown', function (e) {
                var key_code = e.which;
                if ($current_audio && (key_code == $.FE.KEYCODE.BACKSPACE || key_code == $.FE.KEYCODE.DELETE)) {
                    e.preventDefault();
                    remove();
                    return false;
                }
                if ($current_audio && key_code == $.FE.KEYCODE.ESC) {
                    _exitEdit(true);
                    e.preventDefault();
                    return false;
                }
                if ($current_audio && !editor.keys.ctrlKey(e)) {
                    e.preventDefault();
                    return false;
                }
            }, true);
            editor.events.on('keydown', function () {
                editor.$el.find('span.fr-video:empty').remove();
            })
            _initInsertPopup(true);
            _initSizePopup(true);
        }

        function back() {
            if ($current_audio) {
                $current_audio.trigger('click');
            } else {
                editor.events.disableBlur();
                editor.selection.restore();
                editor.events.enableBlur();
                editor.popups.hide('audio.insert');
                editor.toolbar.showInline();
            }
        }

        function setSize(width, height) {
            if ($current_audio) {
                var $popup = editor.popups.get('audio.size');
                var $audio_obj = $current_audio.find('iframe, embed, audio');
                $audio_obj.css('width', width || $popup.find('input[name="width"]').val());
                $audio_obj.css('height', height || $popup.find('input[name="height"]').val());
                if ($audio_obj.get(0).style.width) $audio_obj.removeAttr('width');
                if ($audio_obj.get(0).style.height) $audio_obj.removeAttr('height');
                $popup.find('input').blur();
                setTimeout(function () {
                    $current_audio.trigger('click');
                }, editor.helpers.isAndroid() ? 50 : 0);
            }
        }

        function get() {
            return $current_audio;
        }

        return {
            _init: _init,
            showInsertPopup: showInsertPopup,
            showLayer: showLayer,
            refreshByURLButton: refreshByURLButton,
            refreshEmbedButton: refreshEmbedButton,
            insertByURL: insertByURL,
            insertEmbed: insertEmbed,
            insert: insert,
            align: align,
            refreshAlign: refreshAlign,
            refreshAlignOnShow: refreshAlignOnShow,
            display: display,
            refreshDisplayOnShow: refreshDisplayOnShow,
            remove: remove,
            showSizePopup: showSizePopup,
            back: back,
            setSize: setSize,
            get: get
        }
    }
    $.FE.RegisterCommand('insertAudio', {
        title: 'Insert Audio',
        undo: false,
        focus: true,
        refreshAfterCallback: false,
        popup: true,
        callback: function () {
            if (!this.popups.isVisible('audio.insert')) {
                this.audio.showInsertPopup();
            } else {
                if (this.$el.find('.fr-marker')) {
                    this.events.disableBlur();
                    this.selection.restore();
                }
                this.popups.hide('audio.insert');
            }
        },
        plugin: 'audio'
    })
    $.FE.DefineIcon('insertAudio', {NAME: 'volume-up'});
    $.FE.DefineIcon('audioByURL', {NAME: 'link'});
    $.FE.RegisterCommand('audioByURL', {
        title: 'By URL', undo: false, focus: false, callback: function () {
            this.audio.showLayer('audio-by-url');
        }, refresh: function ($btn) {
            this.audio.refreshByURLButton($btn);
        }
    })
    $.FE.DefineIcon('audioEmbed', {NAME: 'code'});
    $.FE.RegisterCommand('audioEmbed', {
        title: 'Embedded Code', undo: false, focus: false, callback: function () {
            this.audio.showLayer('audio-embed');
        }, refresh: function ($btn) {
            this.audio.refreshEmbedButton($btn);
        }
    })
    $.FE.RegisterCommand('audioInsertByURL', {
        undo: true, focus: true, callback: function () {
            this.audio.insertByURL();
        }
    })
    $.FE.RegisterCommand('audioInsertEmbed', {
        undo: true, focus: true, callback: function () {
            this.audio.insertEmbed();
        }
    })
    $.FE.DefineIcon('audioDisplay', {NAME: 'star'})
    $.FE.RegisterCommand('audioDisplay', {
        title: 'Display',
        type: 'dropdown',
        options: {inline: 'Inline', block: 'Break Text'},
        callback: function (cmd, val) {
            this.audio.display(val);
        },
        refresh: function ($btn) {
            if (!this.opts.audioTextNear) $btn.addClass('fr-hidden');
        },
        refreshOnShow: function ($btn, $dropdown) {
            this.audio.refreshDisplayOnShow($btn, $dropdown);
        }
    })
    $.FE.DefineIcon('audioAlign', {NAME: 'align-center'})
    $.FE.RegisterCommand('audioAlign', {
        type: 'dropdown',
        title: 'Align',
        options: {left: 'Align Left', justify: 'None', right: 'Align Right'},
        html: function () {
            var c = '<ul class="fr-dropdown-list">';
            var options = $.FE.COMMANDS.audioAlign.options;
            for (var val in options) {
                if (options.hasOwnProperty(val)) {
                    c += '<li><a class="fr-command fr-title" data-cmd="audioAlign" data-param1="' + val + '" title="' + this.language.translate(options[val]) + '">' + this.icon.create('align-' + val) + '</a></li>';
                }
            }
            c += '</ul>';
            return c;
        },
        callback: function (cmd, val) {
            this.audio.align(val);
        },
        refresh: function ($btn) {
            this.audio.refreshAlign($btn);
        },
        refreshOnShow: function ($btn, $dropdown) {
            this.audio.refreshAlignOnShow($btn, $dropdown);
        }
    })
    $.FE.DefineIcon('audioRemove', {NAME: 'trash'})
    $.FE.RegisterCommand('audioRemove', {
        title: 'Remove', callback: function () {
            this.audio.remove();
        }
    })
    $.FE.DefineIcon('audioSize', {NAME: 'arrows-alt'})
    $.FE.RegisterCommand('audioSize', {
        undo: false, focus: false, title: 'Change Size', callback: function () {
            this.audio.showSizePopup();
        }
    });
    $.FE.DefineIcon('audioBack', {NAME: 'arrow-left'});
    $.FE.RegisterCommand('audioBack', {
        title: 'Back', undo: false, focus: false, back: true, callback: function () {
            this.audio.back();
        }, refresh: function ($btn) {
            var $current_audio = this.audio.get();
            if (!$current_audio && !this.opts.toolbarInline) {
                $btn.addClass('fr-hidden');
                $btn.next('.fr-separator').addClass('fr-hidden');
            } else {
                $btn.removeClass('fr-hidden');
                $btn.next('.fr-separator').removeClass('fr-hidden');
            }
        }
    });
    $.FE.RegisterCommand('audioSetSize', {
        undo: true, focus: false, callback: function () {
            this.audio.setSize();
        }
    })
}));
!function (n) {
    "function" == typeof define && define.amd ? define(["jquery"], n) : "object" == typeof module && module.exports ? module.exports = function (e, t) {
        return t === undefined && (t = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), n(t)
    } : n(window.jQuery)
}(function (a) {
    a.FE.PLUGINS.quote = function (r) {
        function o(e) {
            for (; e.parentNode && e.parentNode != r.el;) e = e.parentNode;
            return e
        }

        return {
            apply: function (e) {
                r.selection.save(), r.html.wrap(!0, !0, !0, !0), r.selection.restore(), "increase" == e ? function () {
                    var e, t = r.selection.blocks();
                    for (e = 0; e < t.length; e++) t[e] = o(t[e]);
                    r.selection.save();
                    var n = a("<blockquote>");
                    for (n.insertBefore(t[0]), e = 0; e < t.length; e++) n.append(t[e]);
                    r.html.unwrap(), r.selection.restore()
                }() : "decrease" == e && function () {
                    var e, t = r.selection.blocks();
                    for (e = 0; e < t.length; e++) "BLOCKQUOTE" != t[e].tagName && (t[e] = a(t[e]).parentsUntil(r.$el, "BLOCKQUOTE").get(0));
                    for (r.selection.save(), e = 0; e < t.length; e++) t[e] && a(t[e]).replaceWith(t[e].innerHTML);
                    r.html.unwrap(), r.selection.restore()
                }()
            }
        }
    }, a.FE.RegisterShortcut(a.FE.KEYCODE.SINGLE_QUOTE, "quote", "increase", "'"), a.FE.RegisterShortcut(a.FE.KEYCODE.SINGLE_QUOTE, "quote", "decrease", "'", !0), a.FE.RegisterCommand("quote", {
        title: "Quote",
        type: "dropdown",
        html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">',
                t = {increase: "Increase", decrease: "Decrease"};
            for (var n in t) if (t.hasOwnProperty(n)) {
                var r = this.shortcuts.get("quote." + n);
                e += '<li role="presentation"><a class="fr-command fr-active ' + n + '" tabIndex="-1" role="option" data-cmd="quote" data-param1="' + n + '" title="' + t[n] + '">' + this.language.translate(t[n]) + (r ? '<span class="fr-shortcut">' + r + "</span>" : "") + "</a></li>"
            }
            return e += "</ul>"
        },
        callback: function (e, t) {
            this.quote.apply(t)
        },
        plugin: "quote"
    }), a.FE.DefineIcon("quote", {NAME: "quote-left"})
});
!function (n) {
    "function" == typeof define && define.amd ? define(["jquery"], n) : "object" == typeof module && module.exports ? module.exports = function (e, t) {
        return t === undefined && (t = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), n(t)
    } : n(window.jQuery)
}(function (f) {
    f.extend(f.FE.DEFAULTS, {
        fontSize: ["8", "9", "10", "11", "12", "14", "18", "24", "30", "36", "48", "60", "72", "96"],
        fontSizeSelection: !1,
        fontSizeDefaultSelection: "12",
        fontSizeUnit: "px"
    }), f.FE.PLUGINS.fontSize = function (r) {
        return {
            apply: function (e) {
                r.format.applyStyle("font-size", e)
            }, refreshOnShow: function (e, t) {
                var n = f(r.selection.element()).css("font-size");
                "pt" === r.opts.fontSizeUnit && (n = Math.round(72 * parseFloat(n, 10) / 96) + "pt"), t.find(".fr-command.fr-active").removeClass("fr-active").attr("aria-selected", !1), t.find('.fr-command[data-param1="' + n + '"]').addClass("fr-active").attr("aria-selected", !0);
                var o = t.find(".fr-dropdown-list"), i = t.find(".fr-active").parent();
                i.length ? o.parent().scrollTop(i.offset().top - o.offset().top - (o.parent().outerHeight() / 2 - i.outerHeight() / 2)) : o.parent().scrollTop(0)
            }, refresh: function (e) {
                if (r.opts.fontSizeSelection) {
                    var t = r.helpers.getPX(f(r.selection.element()).css("font-size"));
                    "pt" === r.opts.fontSizeUnit && (t = Math.round(72 * parseFloat(t, 10) / 96) + "pt"), e.find("> span").text(t)
                }
            }
        }
    }, f.FE.RegisterCommand("fontSize", {
        type: "dropdown", title: "Font Size", displaySelection: function (e) {
            return e.opts.fontSizeSelection
        }, displaySelectionWidth: 30, defaultSelection: function (e) {
            return e.opts.fontSizeDefaultSelection
        }, html: function () {
            for (var e = '<ul class="fr-dropdown-list" role="presentation">', t = this.opts.fontSize, n = 0; n < t.length; n++) {
                var o = t[n];
                e += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="fontSize" data-param1="' + o + this.opts.fontSizeUnit + '" title="' + o + '">' + o + "</a></li>"
            }
            return e += "</ul>"
        }, callback: function (e, t) {
            this.fontSize.apply(t)
        }, refresh: function (e) {
            this.fontSize.refresh(e)
        }, refreshOnShow: function (e, t) {
            this.fontSize.refreshOnShow(e, t)
        }, plugin: "fontSize"
    }), f.FE.DefineIcon("fontSize", {NAME: "text-height"})
});
!function (n) {
    "function" == typeof define && define.amd ? define(["jquery"], n) : "object" == typeof module && module.exports ? module.exports = function (e, t) {
        return t === undefined && (t = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), n(t)
    } : n(window.jQuery)
}(function (l) {
    l.extend(l.FE.DEFAULTS, {
        fontFamily: {
            "Arial,Helvetica,sans-serif": "Arial",
            "Georgia,serif": "Georgia",
            "Impact,Charcoal,sans-serif": "Impact",
            "Tahoma,Geneva,sans-serif": "Tahoma",
            "Times New Roman,Times,serif,-webkit-standard": "Times New Roman",
            "Verdana,Geneva,sans-serif": "Verdana"
        }, fontFamilySelection: !1, fontFamilyDefaultSelection: "Font Family"
    }), l.FE.PLUGINS.fontFamily = function (o) {
        function i(e) {
            var t = e.replace(/(sans-serif|serif|monospace|cursive|fantasy)/gi, "").replace(/"|'| /g, "").split(",");
            return l.grep(t, function (e) {
                return 0 < e.length
            })
        }

        function r(e, t) {
            for (var n = 0; n < e.length; n++) for (var a = 0; a < t.length; a++) if (e[n].toLowerCase() == t[a].toLowerCase()) return [n, a];
            return null
        }

        function f() {
            var e = i(l(o.selection.element()).css("font-family")), t = [];
            for (var n in o.opts.fontFamily) if (o.opts.fontFamily.hasOwnProperty(n)) {
                var a = r(e, i(n));
                a && t.push([n, a])
            }
            return 0 === t.length ? null : (t.sort(function (e, t) {
                var n = e[1][0] - t[1][0];
                return 0 === n ? e[1][1] - t[1][1] : n
            }), t[0][0])
        }

        return {
            apply: function (e) {
                o.format.applyStyle("font-family", e)
            }, refreshOnShow: function (e, t) {
                t.find(".fr-command.fr-active").removeClass("fr-active").attr("aria-selected", !1), t.find('.fr-command[data-param1="' + f() + '"]').addClass("fr-active").attr("aria-selected", !0);
                var n = t.find(".fr-dropdown-list"), a = t.find(".fr-active").parent();
                a.length ? n.parent().scrollTop(a.offset().top - n.offset().top - (n.parent().outerHeight() / 2 - a.outerHeight() / 2)) : n.parent().scrollTop(0)
            }, refresh: function (e) {
                if (o.opts.fontFamilySelection) {
                    var t = l(o.selection.element()).css("font-family").replace(/(sans-serif|serif|monospace|cursive|fantasy)/gi, "").replace(/"|'|/g, "").split(",");
                    e.find("> span").text(o.opts.fontFamily[f()] || t[0] || o.language.translate(o.opts.fontFamilyDefaultSelection))
                }
            }
        }
    }, l.FE.RegisterCommand("fontFamily", {
        type: "dropdown", displaySelection: function (e) {
            return e.opts.fontFamilySelection
        }, defaultSelection: function (e) {
            return e.opts.fontFamilyDefaultSelection
        }, displaySelectionWidth: 120, html: function () {
            var e = '<ul class="fr-dropdown-list" role="presentation">', t = this.opts.fontFamily;
            for (var n in t) t.hasOwnProperty(n) && (e += '<li role="presentation"><a class="fr-command" tabIndex="-1" role="option" data-cmd="fontFamily" data-param1="' + n + '" style="font-family: ' + n + '" title="' + t[n] + '">' + t[n] + "</a></li>");
            return e += "</ul>"
        }, title: "Font Family", callback: function (e, t) {
            this.fontFamily.apply(t)
        }, refresh: function (e) {
            this.fontFamily.refresh(e)
        }, refreshOnShow: function (e, t) {
            this.fontFamily.refreshOnShow(e, t)
        }, plugin: "fontFamily"
    }), l.FE.DefineIcon("fontFamily", {NAME: "font"})
});
!function (t) {
    "function" == typeof define && define.amd ? define(["jquery"], t) : "object" == typeof module && module.exports ? module.exports = function (e, o) {
        return o === undefined && (o = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), t(o)
    } : t(window.jQuery)
}(function (g) {
    g.extend(g.FE.POPUP_TEMPLATES, {emoticons: "[_BUTTONS_][_EMOTICONS_]"}), g.extend(g.FE.DEFAULTS, {
        emoticonsStep: 8,
        emoticonsSet: [{code: "1f600", desc: "Grinning face"}, {
            code: "1f601",
            desc: "Grinning face with smiling eyes"
        }, {code: "1f602", desc: "Face with tears of joy"}, {
            code: "1f603",
            desc: "Smiling face with open mouth"
        }, {code: "1f604", desc: "Smiling face with open mouth and smiling eyes"}, {
            code: "1f605",
            desc: "Smiling face with open mouth and cold sweat"
        }, {code: "1f606", desc: "Smiling face with open mouth and tightly-closed eyes"}, {
            code: "1f607",
            desc: "Smiling face with halo"
        }, {code: "1f608", desc: "Smiling face with horns"}, {code: "1f609", desc: "Winking face"}, {
            code: "1f60a",
            desc: "Smiling face with smiling eyes"
        }, {code: "1f60b", desc: "Face savoring delicious food"}, {
            code: "1f60c",
            desc: "Relieved face"
        }, {code: "1f60d", desc: "Smiling face with heart-shaped eyes"}, {
            code: "1f60e",
            desc: "Smiling face with sunglasses"
        }, {code: "1f60f", desc: "Smirking face"}, {code: "1f610", desc: "Neutral face"}, {
            code: "1f611",
            desc: "Expressionless face"
        }, {code: "1f612", desc: "Unamused face"}, {code: "1f613", desc: "Face with cold sweat"}, {
            code: "1f614",
            desc: "Pensive face"
        }, {code: "1f615", desc: "Confused face"}, {code: "1f616", desc: "Confounded face"}, {
            code: "1f617",
            desc: "Kissing face"
        }, {code: "1f618", desc: "Face throwing a kiss"}, {
            code: "1f619",
            desc: "Kissing face with smiling eyes"
        }, {code: "1f61a", desc: "Kissing face with closed eyes"}, {
            code: "1f61b",
            desc: "Face with stuck out tongue"
        }, {code: "1f61c", desc: "Face with stuck out tongue and winking eye"}, {
            code: "1f61d",
            desc: "Face with stuck out tongue and tightly-closed eyes"
        }, {code: "1f61e", desc: "Disappointed face"}, {code: "1f61f", desc: "Worried face"}, {
            code: "1f620",
            desc: "Angry face"
        }, {code: "1f621", desc: "Pouting face"}, {code: "1f622", desc: "Crying face"}, {
            code: "1f623",
            desc: "Persevering face"
        }, {code: "1f624", desc: "Face with look of triumph"}, {
            code: "1f625",
            desc: "Disappointed but relieved face"
        }, {code: "1f626", desc: "Frowning face with open mouth"}, {
            code: "1f627",
            desc: "Anguished face"
        }, {code: "1f628", desc: "Fearful face"}, {code: "1f629", desc: "Weary face"}, {
            code: "1f62a",
            desc: "Sleepy face"
        }, {code: "1f62b", desc: "Tired face"}, {code: "1f62c", desc: "Grimacing face"}, {
            code: "1f62d",
            desc: "Loudly crying face"
        }, {code: "1f62e", desc: "Face with open mouth"}, {code: "1f62f", desc: "Hushed face"}, {
            code: "1f630",
            desc: "Face with open mouth and cold sweat"
        }, {code: "1f631", desc: "Face screaming in fear"}, {code: "1f632", desc: "Astonished face"}, {
            code: "1f633",
            desc: "Flushed face"
        }, {code: "1f634", desc: "Sleeping face"}, {code: "1f635", desc: "Dizzy face"}, {
            code: "1f636",
            desc: "Face without mouth"
        }, {code: "1f637", desc: "Face with medical mask"}],
        emoticonsButtons: ["emoticonsBack", "|"],
        emoticonsUseImage: !0
    }), g.FE.PLUGINS.emoticons = function (E) {
        function n() {
            if (!E.selection.isCollapsed()) return !1;
            var e = E.selection.element(), o = E.selection.endElement();
            if (e && E.node.hasClass(e, "fr-emoticon")) return e;
            if (o && E.node.hasClass(o, "fr-emoticon")) return o;
            var t = E.selection.ranges(0), s = t.startContainer;
            if (s.nodeType == Node.ELEMENT_NODE && 0 < s.childNodes.length && 0 < t.startOffset) {
                var n = s.childNodes[t.startOffset - 1];
                if (E.node.hasClass(n, "fr-emoticon")) return n
            }
            return !1
        }

        return {
            _init: function () {
                var e = function () {
                    for (var e = E.el.querySelectorAll(".fr-emoticon:not(.fr-deletable)"), o = 0; o < e.length; o++) e[o].className += " fr-deletable"
                };
                e(), E.events.on("html.set", e), E.events.on("keydown", function (e) {
                    if (E.keys.isCharacter(e.which) && E.selection.inEditor()) {
                        var o = E.selection.ranges(0), t = n();
                        E.node.hasClass(t, "fr-emoticon-img") && t && (0 === o.startOffset && E.selection.element() === t ? g(t).before(g.FE.MARKERS + g.FE.INVISIBLE_SPACE) : g(t).after(g.FE.INVISIBLE_SPACE + g.FE.MARKERS), E.selection.restore())
                    }
                }), E.events.on("keyup", function (e) {
                    for (var o = E.el.querySelectorAll(".fr-emoticon"), t = 0; t < o.length; t++) "undefined" != typeof o[t].textContent && 0 === o[t].textContent.replace(/\u200B/gi, "").length && g(o[t]).remove();
                    if (!(e.which >= g.FE.KEYCODE.ARROW_LEFT && e.which <= g.FE.KEYCODE.ARROW_DOWN)) {
                        var s = n();
                        E.node.hasClass(s, "fr-emoticon-img") && (g(s).append(g.FE.MARKERS), E.selection.restore())
                    }
                })
            }, insert: function (e, o) {
                var t = n(), s = E.selection.ranges(0);
                t ? (0 === s.startOffset && E.selection.element() === t ? g(t).before(g.FE.MARKERS + g.FE.INVISIBLE_SPACE) : 0 < s.startOffset && E.selection.element() === t && s.commonAncestorContainer.parentNode.classList.contains("fr-emoticon") && g(t).after(g.FE.INVISIBLE_SPACE + g.FE.MARKERS), E.selection.restore(), E.html.insert('<span class="fr-emoticon fr-deletable' + (o ? " fr-emoticon-img" : "") + '"' + (o ? ' style="background: url(' + o + ');"' : "") + ">" + (o ? "&nbsp;" : e) + "</span>&nbsp;" + g.FE.MARKERS, !0)) : E.html.insert('<span class="fr-emoticon fr-deletable' + (o ? " fr-emoticon-img" : "") + '"' + (o ? ' style="background: url(' + o + ');"' : "") + ">" + (o ? "&nbsp;" : e) + "</span>&nbsp;", !0)
            }, showEmoticonsPopup: function () {
                var e = E.$tb.find('.fr-command[data-cmd="emoticons"]'), o = E.popups.get("emoticons");
                if (o || (o = function () {
                    var e = "";
                    E.opts.toolbarInline && 0 < E.opts.emoticonsButtons.length && (e = '<div class="fr-buttons fr-emoticons-buttons">' + E.button.buildList(E.opts.emoticonsButtons) + "</div>");
                    var h, o = {
                        buttons: e, emoticons: function () {
                            for (var e = '<div style="text-align: center">', o = 0; o < E.opts.emoticonsSet.length; o++) 0 !== o && o % E.opts.emoticonsStep == 0 && (e += "<br>"), e += '<span class="fr-command fr-emoticon" tabIndex="-1" data-cmd="insertEmoticon" title="' + E.language.translate(E.opts.emoticonsSet[o].desc) + '" role="button" data-param1="' + E.opts.emoticonsSet[o].code + '">' + (E.opts.emoticonsUseImage ? '<img src="https://cdnjs.cloudflare.com/ajax/libs/emojione/2.0.1/assets/svg/' + E.opts.emoticonsSet[o].code + '.svg"/>' : "&#x" + E.opts.emoticonsSet[o].code + ";") + '<span class="fr-sr-only">' + E.language.translate(E.opts.emoticonsSet[o].desc) + "&nbsp;&nbsp;&nbsp;</span></span>";
                            return E.opts.emoticonsUseImage && (e += '<p style="font-size: 12px; text-align: center; padding: 0 5px;">Emoji free by <a class="fr-link" tabIndex="-1" href="http://emojione.com/" target="_blank" rel="nofollow noopener noreferrer" role="link" aria-label="Open Emoji One website.">Emoji One</a></p>'), e += "</div>"
                        }()
                    }, t = E.popups.create("emoticons", o);
                    return E.tooltip.bind(t, ".fr-emoticon"), h = t, E.events.on("popup.tab", function (e) {
                        var o = g(e.currentTarget);
                        if (!E.popups.isVisible("emoticons") || !o.is("span, a")) return !0;
                        var t, s, n, c = e.which;
                        if (g.FE.KEYCODE.TAB == c) {
                            if (o.is("span.fr-emoticon") && e.shiftKey || o.is("a") && !e.shiftKey) {
                                var i = h.find(".fr-buttons");
                                t = !E.accessibility.focusToolbar(i, !!e.shiftKey)
                            }
                            if (!1 !== t) {
                                var a = h.find("span.fr-emoticon:focus:first, span.fr-emoticon:visible:first, a");
                                o.is("span.fr-emoticon") && (a = a.not("span.fr-emoticon:not(:focus)")), s = a.index(o), s = e.shiftKey ? ((s - 1) % a.length + a.length) % a.length : (s + 1) % a.length, n = a.get(s), E.events.disableBlur(), n.focus(), t = !1
                            }
                        } else if (g.FE.KEYCODE.ARROW_UP == c || g.FE.KEYCODE.ARROW_DOWN == c || g.FE.KEYCODE.ARROW_LEFT == c || g.FE.KEYCODE.ARROW_RIGHT == c) {
                            if (o.is("span.fr-emoticon")) {
                                var f = o.parent().find("span.fr-emoticon");
                                s = f.index(o);
                                var d = E.opts.emoticonsStep, r = Math.floor(f.length / d), l = s % d,
                                    m = Math.floor(s / d), u = m * d + l, p = r * d;
                                g.FE.KEYCODE.ARROW_UP == c ? u = ((u - d) % p + p) % p : g.FE.KEYCODE.ARROW_DOWN == c ? u = (u + d) % p : g.FE.KEYCODE.ARROW_LEFT == c ? u = ((u - 1) % p + p) % p : g.FE.KEYCODE.ARROW_RIGHT == c && (u = (u + 1) % p), n = g(f.get(u)), E.events.disableBlur(), n.focus(), t = !1
                            }
                        } else g.FE.KEYCODE.ENTER == c && (o.is("a") ? o[0].click() : E.button.exec(o), t = !1);
                        return !1 === t && (e.preventDefault(), e.stopPropagation()), t
                    }, !0), t
                }()), !o.hasClass("fr-active")) {
                    E.popups.refresh("emoticons"), E.popups.setContainer("emoticons", E.$tb);
                    var t = e.offset().left + e.outerWidth() / 2,
                        s = e.offset().top + (E.opts.toolbarBottom ? 10 : e.outerHeight() - 10);
                    E.popups.show("emoticons", t, s, e.outerHeight())
                }
            }, hideEmoticonsPopup: function () {
                E.popups.hide("emoticons")
            }, back: function () {
                E.popups.hide("emoticons"), E.toolbar.showInline()
            }
        }
    }, g.FE.DefineIcon("emoticons", {
        NAME: "smile-o",
        FA5NAME: "smile"
    }), g.FE.RegisterCommand("emoticons", {
        title: "Emoticons",
        undo: !1,
        focus: !0,
        refreshOnCallback: !1,
        popup: !0,
        callback: function () {
            this.popups.isVisible("emoticons") ? (this.$el.find(".fr-marker").length && (this.events.disableBlur(), this.selection.restore()), this.popups.hide("emoticons")) : this.emoticons.showEmoticonsPopup()
        },
        plugin: "emoticons"
    }), g.FE.RegisterCommand("insertEmoticon", {
        callback: function (e, o) {
            this.emoticons.insert("&#x" + o + ";", this.opts.emoticonsUseImage ? "https://cdnjs.cloudflare.com/ajax/libs/emojione/2.0.1/assets/svg/" + o + ".svg" : null), this.emoticons.hideEmoticonsPopup()
        }
    }), g.FE.DefineIcon("emoticonsBack", {NAME: "arrow-left"}), g.FE.RegisterCommand("emoticonsBack", {
        title: "Back",
        undo: !1,
        focus: !1,
        back: !0,
        refreshAfterCallback: !1,
        callback: function () {
            this.emoticons.back()
        }
    })
});
!function (e) {
    "function" == typeof define && define.amd ? define(["jquery"], e) : "object" == typeof module && module.exports ? module.exports = function (o, r) {
        return r === undefined && (r = "undefined" != typeof window ? require("jquery") : require("jquery")(o)), e(r)
    } : e(window.jQuery)
}(function (C) {
    C.extend(C.FE.POPUP_TEMPLATES, {"colors.picker": "[_BUTTONS_][_TEXT_COLORS_][_BACKGROUND_COLORS_][_CUSTOM_COLOR_]"}), C.extend(C.FE.DEFAULTS, {
        colorsText: ["#61BD6D", "#1ABC9C", "#54ACD2", "#2C82C9", "#9365B8", "#475577", "#CCCCCC", "#41A85F", "#00A885", "#3D8EB9", "#2969B0", "#553982", "#28324E", "#000000", "#F7DA64", "#FBA026", "#EB6B56", "#E25041", "#A38F84", "#EFEFEF", "#FFFFFF", "#FAC51C", "#F37934", "#D14841", "#B8312F", "#7C706B", "#D1D5D8", "REMOVE"],
        colorsBackground: ["#61BD6D", "#1ABC9C", "#54ACD2", "#2C82C9", "#9365B8", "#475577", "#CCCCCC", "#41A85F", "#00A885", "#3D8EB9", "#2969B0", "#553982", "#28324E", "#000000", "#F7DA64", "#FBA026", "#EB6B56", "#E25041", "#A38F84", "#EFEFEF", "#FFFFFF", "#FAC51C", "#F37934", "#D14841", "#B8312F", "#7C706B", "#D1D5D8", "REMOVE"],
        colorsStep: 7,
        colorsHEXInput: !0,
        colorsDefaultTab: "text",
        colorsButtons: ["colorsBack", "|", "-"]
    });
    var c = ["text", "background"];
    C.FE.PLUGINS.colors = function (E) {
        function r() {
            E.popups.hide("colors.picker")
        }

        function s(o) {
            for (var r = "text" == o ? E.opts.colorsText : E.opts.colorsBackground, e = '<div class="fr-color-set fr-' + o + "-color" + (E.opts.colorsDefaultTab == o || "text" != E.opts.colorsDefaultTab && "background" != E.opts.colorsDefaultTab && "text" == o ? " fr-selected-set" : "") + '">', t = 0; t < r.length; t++) 0 !== t && t % E.opts.colorsStep == 0 && (e += "<br>"), "REMOVE" != r[t] ? e += '<span class="fr-command fr-select-color" style="background: ' + r[t] + ';" tabIndex="-1" aria-selected="false" role="button" data-cmd="' + o + 'Color" data-param1="' + r[t] + '"><span class="fr-sr-only">' + E.language.translate("Color") + " " + r[t] + "&nbsp;&nbsp;&nbsp;</span></span>" : e += '<span class="fr-command fr-select-color" data-cmd="' + o + 'Color" tabIndex="-1" role="button" data-param1="REMOVE" title="' + E.language.translate("Clear Formatting") + '">' + E.icon.create("remove") + '<span class="fr-sr-only">' + E.language.translate("Clear Formatting") + "</span></span>";
            return e + "</div>"
        }

        function l(o) {
            var r = E.popups.get("colors.picker"),
                e = r.find(".fr-" + o + "-color .fr-active-item").attr("data-param1"),
                t = r.find(".fr-color-hex-layer input"), a = r.find('.fr-colors-tab[data-param1="' + o + '"]');
            t.length && a.hasClass("fr-selected-tab") && t.val(e).trigger("change")
        }

        function t(o) {
            "REMOVE" != o ? E.format.applyStyle("background-color", E.helpers.HEXtoRGB(o)) : E.format.removeStyle("background-color"), r()
        }

        function a(o) {
            "REMOVE" != o ? E.format.applyStyle("color", E.helpers.HEXtoRGB(o)) : E.format.removeStyle("color"), r()
        }

        return {
            showColorsPopup: function () {
                var o = E.$tb.find('.fr-command[data-cmd="color"]'), r = E.popups.get("colors.picker");
                if (r || (r = function () {
                    var o, r = '<div class="fr-buttons fr-colors-buttons">';
                    E.opts.toolbarInline && 0 < E.opts.colorsButtons.length && (r += E.button.buildList(E.opts.colorsButtons)), r += (o = '<div class="fr-colors-tabs fr-group">', o += '<span class="fr-colors-tab ' + ("background" == E.opts.colorsDefaultTab ? "" : "fr-selected-tab ") + 'fr-command" tabIndex="-1" role="button" aria-pressed="' + ("background" != E.opts.colorsDefaultTab) + '" data-param1="text" data-cmd="colorChangeSet" title="' + E.language.translate("Text") + '">' + E.language.translate("Text") + "</span>", (o += '<span class="fr-colors-tab ' + ("background" == E.opts.colorsDefaultTab ? "fr-selected-tab" : "") + 'fr-command" tabIndex="-1" role="button" aria-pressed="' + ("background" == E.opts.colorsDefaultTab) + '" data-param1="background" data-cmd="colorChangeSet" title="' + E.language.translate("Background") + '">' + E.language.translate("Background") + "</span>") + "</div></div>");
                    var e = "";
                    E.opts.colorsHEXInput && (e = '<div class="fr-color-hex-layer fr-active fr-layer" id="fr-color-hex-layer-' + E.id + '"><div class="fr-input-line"><input maxlength="7" id="fr-color-hex-layer-text-' + E.id + '" type="text" placeholder="' + E.language.translate("HEX Color") + '" tabIndex="1" aria-required="true"></div><div class="fr-action-buttons"><button type="button" class="fr-command fr-submit" data-cmd="customColor" tabIndex="2" role="button">' + E.language.translate("OK") + "</button></div></div>");
                    var b,
                        t = {buttons: r, text_colors: s("text"), background_colors: s("background"), custom_color: e},
                        a = E.popups.create("colors.picker", t);
                    return b = a, E.events.on("popup.tab", function (o) {
                        var r = C(o.currentTarget);
                        if (!E.popups.isVisible("colors.picker") || !r.is("span")) return !0;
                        var e = o.which, t = !0;
                        if (C.FE.KEYCODE.TAB == e) {
                            var a = b.find(".fr-buttons");
                            t = !E.accessibility.focusToolbar(a, !!o.shiftKey)
                        } else if (C.FE.KEYCODE.ARROW_UP == e || C.FE.KEYCODE.ARROW_DOWN == e || C.FE.KEYCODE.ARROW_LEFT == e || C.FE.KEYCODE.ARROW_RIGHT == e) {
                            if (r.is("span.fr-select-color")) {
                                var s = r.parent().find("span.fr-select-color"), l = s.index(r), c = E.opts.colorsStep,
                                    n = Math.floor(s.length / c), i = l % c, p = Math.floor(l / c), u = p * c + i,
                                    d = n * c;
                                C.FE.KEYCODE.ARROW_UP == e ? u = ((u - c) % d + d) % d : C.FE.KEYCODE.ARROW_DOWN == e ? u = (u + c) % d : C.FE.KEYCODE.ARROW_LEFT == e ? u = ((u - 1) % d + d) % d : C.FE.KEYCODE.ARROW_RIGHT == e && (u = (u + 1) % d);
                                var f = C(s.get(u));
                                E.events.disableBlur(), f.focus(), t = !1
                            }
                        } else C.FE.KEYCODE.ENTER == e && (E.button.exec(r), t = !1);
                        return !1 === t && (o.preventDefault(), o.stopPropagation()), t
                    }, !0), a
                }()), !r.hasClass("fr-active")) if (E.popups.setContainer("colors.picker", E.$tb), c.map(function (o) {
                    !function (o) {
                        var r, e = E.popups.get("colors.picker"), t = C(E.selection.element());
                        r = "background" == o ? "background-color" : "color";
                        var a = e.find(".fr-" + o + "-color .fr-select-color");
                        for (a.find(".fr-selected-color").remove(), a.removeClass("fr-active-item"), a.not('[data-param1="REMOVE"]').attr("aria-selected", !1); t.get(0) != E.el;) {
                            if ("transparent" != t.css(r) && "rgba(0, 0, 0, 0)" != t.css(r)) {
                                var s = e.find(".fr-" + o + '-color .fr-select-color[data-param1="' + E.helpers.RGBToHex(t.css(r)) + '"]');
                                s.append('<span class="fr-selected-color" aria-hidden="true">\uf00c</span>'), s.addClass("fr-active-item").attr("aria-selected", !0);
                                break
                            }
                            t = t.parent()
                        }
                        l(o)
                    }(o)
                }), o.is(":visible")) {
                    var e = o.offset().left + o.outerWidth() / 2,
                        t = o.offset().top + (E.opts.toolbarBottom ? 10 : o.outerHeight() - 10);
                    E.popups.show("colors.picker", e, t, o.outerHeight())
                } else E.position.forSelection(r), E.popups.show("colors.picker")
            }, hideColorsPopup: r, changeSet: function (o, r) {
                o.hasClass("fr-selected-tab") || (o.siblings().removeClass("fr-selected-tab").attr("aria-pressed", !1), o.addClass("fr-selected-tab").attr("aria-pressed", !0), o.parents(".fr-popup").find(".fr-color-set").removeClass("fr-selected-set"), o.parents(".fr-popup").find(".fr-color-set.fr-" + r + "-color").addClass("fr-selected-set"), l(r)), E.accessibility.focusPopup(o.parents(".fr-popup"))
            }, background: t, customColor: function () {
                var o = E.popups.get("colors.picker"), r = o.find(".fr-color-hex-layer input");
                if (r.length) {
                    var e = r.val();
                    "background" == o.find(".fr-selected-tab").attr("data-param1") ? t(e) : a(e)
                }
            }, text: a, back: function () {
                E.popups.hide("colors.picker"), E.toolbar.showInline()
            }
        }
    }, C.FE.DefineIcon("colors", {NAME: "tint"}), C.FE.RegisterCommand("color", {
        title: "Colors",
        undo: !1,
        focus: !0,
        refreshOnCallback: !1,
        popup: !0,
        callback: function () {
            this.popups.isVisible("colors.picker") ? (this.$el.find(".fr-marker").length && (this.events.disableBlur(), this.selection.restore()), this.popups.hide("colors.picker")) : this.colors.showColorsPopup()
        },
        plugin: "colors"
    }), C.FE.RegisterCommand("textColor", {
        undo: !0, callback: function (o, r) {
            this.colors.text(r)
        }
    }), C.FE.RegisterCommand("backgroundColor", {
        undo: !0, callback: function (o, r) {
            this.colors.background(r)
        }
    }), C.FE.RegisterCommand("colorChangeSet", {
        undo: !1, focus: !1, callback: function (o, r) {
            var e = this.popups.get("colors.picker").find('.fr-command[data-cmd="' + o + '"][data-param1="' + r + '"]');
            this.colors.changeSet(e, r)
        }
    }), C.FE.DefineIcon("colorsBack", {NAME: "arrow-left"}), C.FE.RegisterCommand("colorsBack", {
        title: "Back",
        undo: !1,
        focus: !1,
        back: !0,
        refreshAfterCallback: !1,
        callback: function () {
            this.colors.back()
        }
    }), C.FE.RegisterCommand("customColor", {
        title: "OK", undo: !0, callback: function () {
            this.colors.customColor()
        }
    }), C.FE.DefineIcon("remove", {NAME: "eraser"})
});
!function (t) {
    "function" == typeof define && define.amd ? define(["jquery"], t) : "object" == typeof module && module.exports ? module.exports = function (e, n) {
        return n === undefined && (n = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), t(n)
    } : t(window.jQuery)
}(function (f) {
    f.FE.URLRegEx = "(^| |\\u00A0)(" + f.FE.LinkRegEx + "|([a-z0-9+-_.]{1,}@[a-z0-9+-_.]{1,}\\.[a-z0-9+-_]{1,}))$", f.FE.PLUGINS.url = function (i) {
        var l = null;

        function n(e, n, t) {
            for (var r = ""; t.length && "." == t[t.length - 1];) r += ".", t = t.substring(0, t.length - 1);
            var o = t;
            if (i.opts.linkConvertEmailAddress) i.helpers.isEmail(o) && !/^mailto:.*/i.test(o) && (o = "mailto:" + o); else if (i.helpers.isEmail(o)) return n + t;
            return /^((http|https|ftp|ftps|mailto|tel|sms|notes|data)\:)/i.test(o) || (o = "//" + o), (n || "") + "<a" + (i.opts.linkAlwaysBlank ? ' target="_blank"' : "") + (l ? ' rel="' + l + '"' : "") + ' data-fr-linked="true" href="' + o + '">' + t.replace(/&amp;/g, "&").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") + "</a>" + r
        }

        function a() {
            return new RegExp(f.FE.URLRegEx, "gi")
        }

        function s(e) {
            return i.opts.linkAlwaysNoFollow && (l = "nofollow"), i.opts.linkAlwaysBlank && (i.opts.linkNoOpener && (l ? l += " noopener" : l = "noopener"), i.opts.linkNoReferrer && (l ? l += " noreferrer" : l = "noreferrer")), e.replace(a(), n)
        }

        function p(e) {
            var n = e.split(" ");
            return n[n.length - 1]
        }

        function t() {
            var n = i.selection.ranges(0), t = n.startContainer;
            if (!t || t.nodeType !== Node.TEXT_NODE || n.startOffset !== (t.textContent || "").length) return !1;
            if (function e(n) {
                return !!n && ("A" === n.tagName || !(!n.parentNode || n.parentNode == i.el) && e(n.parentNode))
            }(t)) return !1;
            if (a().test(p(t.textContent))) {
                f(t).before(s(t.textContent));
                var r = f(t.parentNode).find("a[data-fr-linked]");
                r.removeAttr("data-fr-linked"), t.parentNode.removeChild(t), i.events.trigger("url.linked", [r.get(0)])
            } else if (t.textContent.split(" ").length <= 2 && t.previousSibling && "A" === t.previousSibling.tagName) {
                var o = t.previousSibling.innerText + t.textContent;
                a().test(p(o)) && (f(t.previousSibling).replaceWith(s(o)), t.parentNode.removeChild(t))
            }
        }

        return {
            _init: function () {
                i.events.on("keypress", function (e) {
                    !i.selection.isCollapsed() || "." != e.key && ")" != e.key && "(" != e.key || t()
                }, !0), i.events.on("keydown", function (e) {
                    var n = e.which;
                    !i.selection.isCollapsed() || n != f.FE.KEYCODE.ENTER && n != f.FE.KEYCODE.SPACE || t()
                }, !0), i.events.on("paste.beforeCleanup", function (e) {
                    if (i.helpers.isURL(e)) {
                        var n = null;
                        return i.opts.linkAlwaysBlank && (i.opts.linkNoOpener && (n ? n += " noopener" : n = "noopener"), i.opts.linkNoReferrer && (n ? n += " noreferrer" : n = "noreferrer")), "<a" + (i.opts.linkAlwaysBlank ? ' target="_blank"' : "") + (n ? ' rel="' + n + '"' : "") + ' href="' + e + '" >' + e + "</a>"
                    }
                })
            }
        }
    }
});
!function (n) {
    "function" == typeof define && define.amd ? define(["jquery"], n) : "object" == typeof module && module.exports ? module.exports = function (e, t) {
        return t === undefined && (t = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), n(t)
    } : n(window.jQuery)
}(function (v) {
    v.extend(v.FE.DEFAULTS, {
        lineBreakerTags: ["table", "hr", "form", "dl", "span.fr-video", ".fr-embedly"],
        lineBreakerOffset: 15,
        lineBreakerHorizontalOffset: 10
    }), v.FE.PLUGINS.lineBreaker = function (d) {
        var g, t, a;

        function s(e, t) {
            var n, r, a, o, i, s, f, l;
            if (null == e) i = (o = t.parent()).offset().top, n = (f = t.offset().top) - Math.min((f - i) / 2, d.opts.lineBreakerOffset), a = o.outerWidth(), r = o.offset().left; else if (null == t) (s = (o = e.parent()).offset().top + o.outerHeight()) < (l = e.offset().top + e.outerHeight()) && (s = (o = v(o).parent()).offset().top + o.outerHeight()), n = l + Math.min(Math.abs(s - l) / 2, d.opts.lineBreakerOffset), a = o.outerWidth(), r = o.offset().left; else {
                o = e.parent();
                var p = e.offset().top + e.height(), u = t.offset().top;
                if (u < p) return !1;
                n = (p + u) / 2, a = o.outerWidth(), r = o.offset().left
            }
            d.opts.iframe && (r += d.$iframe.offset().left - d.helpers.scrollLeft(), n += d.$iframe.offset().top - d.helpers.scrollTop()), d.$box.append(g), g.css("top", n - d.win.pageYOffset), g.css("left", r - d.win.pageXOffset), g.css("width", a), g.data("tag1", e), g.data("tag2", t), g.addClass("fr-visible").data("instance", d)
        }

        function f(e) {
            if (e) {
                var t = v(e);
                if (0 === d.$el.find(t).length) return null;
                if (e.nodeType != Node.TEXT_NODE && t.is(d.opts.lineBreakerTags.join(","))) return t;
                if (0 < t.parents(d.opts.lineBreakerTags.join(",")).length) return e = t.parents(d.opts.lineBreakerTags.join(",")).get(0), 0 !== d.$el.find(e).length && v(e).is(d.opts.lineBreakerTags.join(",")) ? v(e) : null
            }
            return null
        }

        function o(e, t) {
            var n = d.doc.elementFromPoint(e, t);
            return n && !v(n).closest(".fr-line-breaker").length && !d.node.isElement(n) && n != d.$wp.get(0) && function (e) {
                if ("undefined" != typeof e.inFroalaWrapper) return e.inFroalaWrapper;
                for (var t = e; e.parentNode && e.parentNode !== d.$wp.get(0);) e = e.parentNode;
                return t.inFroalaWrapper = e.parentNode == d.$wp.get(0), t.inFroalaWrapper
            }(n) ? n : null
        }

        function i(e, t, n) {
            for (var r = n, a = null; r <= d.opts.lineBreakerOffset && !a;) (a = o(e, t - r)) || (a = o(e, t + r)), r += n;
            return a
        }

        function l(e, t, n) {
            for (var r = null, a = 100; !r && e > d.$box.offset().left && e < d.$box.offset().left + d.$box.outerWidth() && 0 < a;) (r = o(e, t)) || (r = i(e, t, 5)), "left" == n ? e -= d.opts.lineBreakerHorizontalOffset : e += d.opts.lineBreakerHorizontalOffset, a -= d.opts.lineBreakerHorizontalOffset;
            return r
        }

        function n(e) {
            var t = a = null, n = null,
                r = d.doc.elementFromPoint(e.pageX - d.win.pageXOffset, e.pageY - d.win.pageYOffset);
            r && ("HTML" == r.tagName || "BODY" == r.tagName || d.node.isElement(r) || 0 <= (r.getAttribute("class") || "").indexOf("fr-line-breaker")) ? ((n = i(e.pageX - d.win.pageXOffset, e.pageY - d.win.pageYOffset, 1)) || (n = l(e.pageX - d.win.pageXOffset - d.opts.lineBreakerHorizontalOffset, e.pageY - d.win.pageYOffset, "left")), n || (n = l(e.pageX - d.win.pageXOffset + d.opts.lineBreakerHorizontalOffset, e.pageY - d.win.pageYOffset, "right")), t = f(n)) : t = f(r), t ? function (e, t) {
                var n, r, a = e.offset().top, o = e.offset().top + e.outerHeight();
                if (Math.abs(o - t) <= d.opts.lineBreakerOffset || Math.abs(t - a) <= d.opts.lineBreakerOffset) if (Math.abs(o - t) < Math.abs(t - a)) {
                    for (var i = (r = e.get(0)).nextSibling; i && i.nodeType == Node.TEXT_NODE && 0 === i.textContent.length;) i = i.nextSibling;
                    if (!i) return s(e, null);
                    if (n = f(i)) return s(e, n)
                } else {
                    if (!(r = e.get(0)).previousSibling) return s(null, e);
                    if (n = f(r.previousSibling)) return s(n, e)
                }
                g.removeClass("fr-visible").removeData("instance")
            }(t, e.pageY) : d.core.sameInstance(g) && g.removeClass("fr-visible").removeData("instance")
        }

        function e(e) {
            return !(g.hasClass("fr-visible") && !d.core.sameInstance(g)) && (d.popups.areVisible() || d.el.querySelector(".fr-selected-cell") ? (g.removeClass("fr-visible"), !0) : void (!1 !== t || d.edit.isDisabled() || (a && clearTimeout(a), a = setTimeout(n, 30, e))))
        }

        function r() {
            a && clearTimeout(a), g && g.hasClass("fr-visible") && g.removeClass("fr-visible").removeData("instance")
        }

        function p() {
            t = !0, r()
        }

        function u() {
            t = !1
        }

        function c(e) {
            e.preventDefault();
            var t = g.data("instance") || d;
            g.removeClass("fr-visible").removeData("instance");
            var n = g.data("tag1"), r = g.data("tag2"), a = d.html.defaultTag();
            null == n ? a && "TD" != r.parent().get(0).tagName && 0 === r.parents(a).length ? r.before("<" + a + ">" + v.FE.MARKERS + "<br></" + a + ">") : r.before(v.FE.MARKERS + "<br>") : a && "TD" != n.parent().get(0).tagName && 0 === n.parents(a).length ? n.after("<" + a + ">" + v.FE.MARKERS + "<br></" + a + ">") : n.after(v.FE.MARKERS + "<br>"), t.selection.restore()
        }

        return {
            _init: function () {
                if (!d.$wp) return !1;
                d.shared.$line_breaker || (d.shared.$line_breaker = v('<div class="fr-line-breaker"><a class="fr-floating-btn" role="button" tabIndex="-1" title="' + d.language.translate("Break") + '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect x="21" y="11" width="2" height="8"/><rect x="14" y="17" width="7" height="2"/><path d="M14.000,14.000 L14.000,22.013 L9.000,18.031 L14.000,14.000 Z"/></svg></a></div>')), g = d.shared.$line_breaker, d.events.on("shared.destroy", function () {
                    g.html("").removeData().remove(), g = null
                }, !0), d.events.on("destroy", function () {
                    g.removeData("instance").removeClass("fr-visible").appendTo("body:first"), clearTimeout(a)
                }, !0), d.events.$on(g, "mousemove", function (e) {
                    e.stopPropagation()
                }, !0), d.events.bindClick(g, "a", c), t = !1, d.events.$on(d.$win, "mousemove", e), d.events.$on(v(d.win), "scroll", r), d.events.on("popups.show.table.edit", r), d.events.on("commands.after", r), d.events.$on(v(d.win), "mousedown", p), d.events.$on(v(d.win), "mouseup", u)
            }
        }
    }
});
!function (a) {
    "function" == typeof define && define.amd ? define(["jquery"], a) : "object" == typeof module && module.exports ? module.exports = function (e, r) {
        return r === undefined && (r = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), a(r)
    } : a(window.jQuery)
}(function (c) {
    c.extend(c.FE.DEFAULTS, {entities: "&quot;&#39;&iexcl;&cent;&pound;&curren;&yen;&brvbar;&sect;&uml;&copy;&ordf;&laquo;&not;&shy;&reg;&macr;&deg;&plusmn;&sup2;&sup3;&acute;&micro;&para;&middot;&cedil;&sup1;&ordm;&raquo;&frac14;&frac12;&frac34;&iquest;&Agrave;&Aacute;&Acirc;&Atilde;&Auml;&Aring;&AElig;&Ccedil;&Egrave;&Eacute;&Ecirc;&Euml;&Igrave;&Iacute;&Icirc;&Iuml;&ETH;&Ntilde;&Ograve;&Oacute;&Ocirc;&Otilde;&Ouml;&times;&Oslash;&Ugrave;&Uacute;&Ucirc;&Uuml;&Yacute;&THORN;&szlig;&agrave;&aacute;&acirc;&atilde;&auml;&aring;&aelig;&ccedil;&egrave;&eacute;&ecirc;&euml;&igrave;&iacute;&icirc;&iuml;&eth;&ntilde;&ograve;&oacute;&ocirc;&otilde;&ouml;&divide;&oslash;&ugrave;&uacute;&ucirc;&uuml;&yacute;&thorn;&yuml;&OElig;&oelig;&Scaron;&scaron;&Yuml;&fnof;&circ;&tilde;&Alpha;&Beta;&Gamma;&Delta;&Epsilon;&Zeta;&Eta;&Theta;&Iota;&Kappa;&Lambda;&Mu;&Nu;&Xi;&Omicron;&Pi;&Rho;&Sigma;&Tau;&Upsilon;&Phi;&Chi;&Psi;&Omega;&alpha;&beta;&gamma;&delta;&epsilon;&zeta;&eta;&theta;&iota;&kappa;&lambda;&mu;&nu;&xi;&omicron;&pi;&rho;&sigmaf;&sigma;&tau;&upsilon;&phi;&chi;&psi;&omega;&thetasym;&upsih;&piv;&ensp;&emsp;&thinsp;&zwnj;&zwj;&lrm;&rlm;&ndash;&mdash;&lsquo;&rsquo;&sbquo;&ldquo;&rdquo;&bdquo;&dagger;&Dagger;&bull;&hellip;&permil;&prime;&Prime;&lsaquo;&rsaquo;&oline;&frasl;&euro;&image;&weierp;&real;&trade;&alefsym;&larr;&uarr;&rarr;&darr;&harr;&crarr;&lArr;&uArr;&rArr;&dArr;&hArr;&forall;&part;&exist;&empty;&nabla;&isin;&notin;&ni;&prod;&sum;&minus;&lowast;&radic;&prop;&infin;&ang;&and;&or;&cap;&cup;&int;&there4;&sim;&cong;&asymp;&ne;&equiv;&le;&ge;&sub;&sup;&nsub;&sube;&supe;&oplus;&otimes;&perp;&sdot;&lceil;&rceil;&lfloor;&rfloor;&lang;&rang;&loz;&spades;&clubs;&hearts;&diams;"}), c.FE.PLUGINS.entities = function (t) {
        var n, u;

        function i(e) {
            var r = e.textContent;
            if (r.match(n)) {
                for (var a = "", i = 0; i < r.length; i++) u[r[i]] ? a += u[r[i]] : a += r[i];
                e.textContent = a
            }
        }

        function o(e) {
            if (e && 0 <= ["STYLE", "SCRIPT", "svg", "IFRAME"].indexOf(e.tagName)) return !0;
            for (var r = t.node.contents(e), a = 0; a < r.length; a++) r[a].nodeType == Node.TEXT_NODE ? i(r[a]) : o(r[a]);
            e.nodeType == Node.TEXT_NODE && i(e)
        }

        function l(e) {
            return 0 === e.length ? "" : t.clean.exec(e, o).replace(/\&amp;/g, "&")
        }

        return {
            _init: function () {
                t.opts.htmlSimpleAmpersand || (t.opts.entities = t.opts.entities + "&amp;");
                var e = c("<div>").html(t.opts.entities).text(), r = t.opts.entities.split(";");
                u = {}, n = "";
                for (var a = 0; a < e.length; a++) {
                    var i = e.charAt(a);
                    u[i] = r[a] + ";", n += "\\" + i + (a < e.length - 1 ? "|" : "")
                }
                n = new RegExp("(" + n + ")", "g"), t.events.on("html.get", l, !0)
            }
        }
    }
});
!function (n) {
    "function" == typeof define && define.amd ? define(["jquery"], n) : "object" == typeof module && module.exports ? module.exports = function (e, t) {
        return t === undefined && (t = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), n(t)
    } : n(window.jQuery)
}(function (v) {
    v.extend(v.FE.DEFAULTS, {dragInline: !0}), v.FE.PLUGINS.draggable = function (f) {
        function e(e) {
            return !(!e.originalEvent || !e.originalEvent.target || e.originalEvent.target.nodeType != Node.TEXT_NODE) || (e.target && "A" == e.target.tagName && 1 == e.target.childNodes.length && "IMG" == e.target.childNodes[0].tagName && (e.target = e.target.childNodes[0]), v(e.target).hasClass("fr-draggable") ? (f.undo.canDo() || f.undo.saveStep(), f.opts.dragInline ? f.$el.attr("contenteditable", !0) : f.$el.attr("contenteditable", !1), f.opts.toolbarInline && f.toolbar.hide(), v(e.target).addClass("fr-dragging"), f.browser.msie || f.browser.edge || f.selection.clear(), void e.originalEvent.dataTransfer.setData("text", "Froala")) : (f.browser.msie || e.preventDefault(), !1))
        }

        function g(e) {
            return !(e && ("HTML" == e.tagName || "BODY" == e.tagName || f.node.isElement(e)))
        }

        function d(e, t, n) {
            f.opts.iframe && (e += f.$iframe.offset().top, t += f.$iframe.offset().left), p.offset().top != e && p.css("top", e), p.offset().left != t && p.css("left", t), p.width() != n && p.css("width", n)
        }

        function t(e) {
            e.originalEvent.dataTransfer.dropEffect = "move", f.opts.dragInline ? function () {
                for (var e = null, t = 0; t < v.FE.INSTANCES.length; t++) if ((e = v.FE.INSTANCES[t].$el.find(".fr-dragging")).length) return e.get(0)
            }() || !f.browser.msie && !f.browser.edge || e.preventDefault() : (e.preventDefault(), function (e) {
                var t = f.doc.elementFromPoint(e.originalEvent.pageX - f.win.pageXOffset, e.originalEvent.pageY - f.win.pageYOffset);
                if (!g(t)) {
                    for (var n = 0, r = t; !g(r) && r == t && 0 < e.originalEvent.pageY - f.win.pageYOffset - n;) n++, r = f.doc.elementFromPoint(e.originalEvent.pageX - f.win.pageXOffset, e.originalEvent.pageY - f.win.pageYOffset - n);
                    (!g(r) || p && 0 === f.$el.find(r).length && r != p.get(0)) && (r = null);
                    for (var a = 0, o = t; !g(o) && o == t && e.originalEvent.pageY - f.win.pageYOffset + a < v(f.doc).height();) a++, o = f.doc.elementFromPoint(e.originalEvent.pageX - f.win.pageXOffset, e.originalEvent.pageY - f.win.pageYOffset + a);
                    (!g(o) || p && 0 === f.$el.find(o).length && o != p.get(0)) && (o = null), t = null == o && r ? r : o && null == r ? o : o && r ? n < a ? r : o : null
                }
                if (v(t).hasClass("fr-drag-helper")) return;
                if (t && !f.node.isBlock(t) && (t = f.node.blockParent(t)), t && 0 <= ["TD", "TH", "TR", "THEAD", "TBODY"].indexOf(t.tagName) && (t = v(t).parents("table").get(0)), t && 0 <= ["LI"].indexOf(t.tagName) && (t = v(t).parents("UL, OL").get(0)), t && !v(t).hasClass("fr-drag-helper")) {
                    var i;
                    p || (v.FE.$draggable_helper || (v.FE.$draggable_helper = v('<div class="fr-drag-helper"></div>')), p = v.FE.$draggable_helper, f.events.on("shared.destroy", function () {
                        p.html("").removeData().remove(), p = null
                    }, !0)), i = e.originalEvent.pageY < v(t).offset().top + v(t).outerHeight() / 2;
                    var l = v(t), s = 0;
                    i || 0 !== l.next().length ? (i || (l = l.next()), "before" == p.data("fr-position") && l.is(p.data("fr-tag")) || (0 < l.prev().length && (s = parseFloat(l.prev().css("margin-bottom")) || 0), s = Math.max(s, parseFloat(l.css("margin-top")) || 0), d(l.offset().top - s / 2 - f.$box.offset().top, l.offset().left - f.win.pageXOffset - f.$box.offset().left, l.width()), p.data("fr-position", "before"))) : "after" == p.data("fr-position") && l.is(p.data("fr-tag")) || (s = parseFloat(l.css("margin-bottom")) || 0, d(l.offset().top + v(t).height() + s / 2 - f.$box.offset().top, l.offset().left - f.win.pageXOffset - f.$box.offset().left, l.width()), p.data("fr-position", "after")), p.data("fr-tag", l), p.addClass("fr-visible"), p.appendTo(f.$box)
                } else p && 0 < f.$box.find(p).length && p.removeClass("fr-visible")
            }(e))
        }

        function n(e) {
            e.originalEvent.dataTransfer.dropEffect = "move", f.opts.dragInline || e.preventDefault()
        }

        function r(e) {
            f.$el.attr("contenteditable", !0);
            var t = f.$el.find(".fr-dragging");
            p && p.hasClass("fr-visible") && f.$box.find(p).length ? a(e) : t.length && (e.preventDefault(), e.stopPropagation()), p && f.$box.find(p).length && p.removeClass("fr-visible"), t.removeClass("fr-dragging")
        }

        function a(e) {
            var t, n;
            "true" !== f.$el.attr("contenteditable") && f.$el.attr("contenteditable", !0);
            for (var r = 0; r < v.FE.INSTANCES.length; r++) if ((t = v.FE.INSTANCES[r].$el.find(".fr-dragging")).length) {
                n = v.FE.INSTANCES[r];
                break
            }
            if (t.length) {
                if (e.preventDefault(), e.stopPropagation(), p && p.hasClass("fr-visible") && f.$box.find(p).length) p.data("fr-tag")[p.data("fr-position")]('<span class="fr-marker"></span>'), p.removeClass("fr-visible"); else if (!1 === f.markers.insertAtPoint(e.originalEvent)) return !1;
                if (t.removeClass("fr-dragging"), !1 === (t = f.events.chainTrigger("element.beforeDrop", t))) return !1;
                var a = t;
                if (t.parent().is("A") && 1 == t.parent().get(0).childNodes.length && (a = t.parent()), f.core.isEmpty()) f.events.focus(); else f.$el.find(".fr-marker").replaceWith(v.FE.MARKERS), f.selection.restore();
                if (n == f || f.undo.canDo() || f.undo.saveStep(), f.core.isEmpty()) f.$el.html(a); else {
                    var o = f.markers.insert();
                    0 === a.find(o).length ? v(o).replaceWith(a) : 0 === t.find(o).length && v(o).replaceWith(t), t.after(v.FE.MARKERS), f.selection.restore()
                }
                return f.popups.hideAll(), f.selection.save(), f.$el.find(f.html.emptyBlockTagsQuery()).not("TD, TH, LI, .fr-inner").not(f.opts.htmlAllowedEmptyTags.join(",")).remove(), f.html.wrap(), f.html.fillEmptyBlocks(), f.selection.restore(), f.undo.saveStep(), f.opts.iframe && f.size.syncIframe(), n != f && (n.popups.hideAll(), n.$el.find(n.html.emptyBlockTagsQuery()).not("TD, TH, LI, .fr-inner").remove(), n.html.wrap(), n.html.fillEmptyBlocks(), n.undo.saveStep(), n.events.trigger("element.dropped"), n.opts.iframe && n.size.syncIframe()), f.events.trigger("element.dropped", [a]), !1
            }
            p && p.removeClass("fr-visible"), f.undo.canDo() || f.undo.saveStep(), setTimeout(function () {
                f.undo.saveStep()
            }, 0)
        }

        function o(e) {
            if (e && "DIV" == e.tagName && f.node.hasClass(e, "fr-drag-helper")) e.parentNode.removeChild(e); else if (e && e.nodeType == Node.ELEMENT_NODE) for (var t = e.querySelectorAll("div.fr-drag-helper"), n = 0; n < t.length; n++) t[n].parentNode.removeChild(t[n])
        }

        var p;
        return {
            _init: function () {
                f.opts.enter == v.FE.ENTER_BR && (f.opts.dragInline = !0), f.events.on("dragstart", e, !0), f.events.on("dragover", t, !0), f.events.on("dragenter", n, !0), f.events.on("document.dragend", r, !0), f.events.on("document.drop", r, !0), f.events.on("drop", a, !0), f.events.on("html.processGet", o)
            }
        }
    }
});
!function (n) {
    "function" == typeof define && define.amd ? define(["jquery"], n) : "object" == typeof module && module.exports ? module.exports = function (e, t) {
        return t === undefined && (t = "undefined" != typeof window ? require("jquery") : require("jquery")(e)), n(t)
    } : n(window.jQuery)
}(function (e) {
    e.FE.PLUGINS.codeBeautifier = function () {
        var e, t, n, i, X = {};

        function k(i, e) {
            var t = {"@page": !0, "@font-face": !0, "@keyframes": !0, "@media": !0, "@supports": !0, "@document": !0},
                n = {"@media": !0, "@supports": !0, "@document": !0};
            e = e || {}, i = (i = i || "").replace(/\r\n|[\r\u2028\u2029]/g, "\n");
            var r = e.indent_size || 4, s = e.indent_char || " ",
                _ = e.selector_separator_newline === undefined || e.selector_separator_newline,
                a = e.end_with_newline !== undefined && e.end_with_newline,
                o = e.newline_between_rules === undefined || e.newline_between_rules, l = e.eol ? e.eol : "\n";
            "string" == typeof r && (r = parseInt(r, 10)), e.indent_with_tabs && (s = "\t", r = 1), l = l.replace(/\\r/, "\r").replace(/\\n/, "\n");
            var h, c = /^\s+$/, u = -1, p = 0;

            function d() {
                return (h = i.charAt(++u)) || ""
            }

            function f(e) {
                var t, n = u;
                return e && E(), t = i.charAt(u + 1) || "", u = n - 1, d(), t
            }

            function T(e) {
                for (var t = u; d();) if ("\\" === h) d(); else {
                    if (-1 !== e.indexOf(h)) break;
                    if ("\n" === h) break
                }
                return i.substring(t, u + 1)
            }

            function E() {
                for (var e = ""; c.test(f());) d(), e += h;
                return e
            }

            function g() {
                var e = "";
                for (h && c.test(h) && (e = h); c.test(d());) e += h;
                return e
            }

            function x(e) {
                var t = u;
                for (e = "/" === f(), d(); d();) {
                    if (!e && "*" === h && "/" === f()) {
                        d();
                        break
                    }
                    if (e && "\n" === h) return i.substring(t, u)
                }
                return i.substring(t, u) + h
            }

            function w(e) {
                return i.substring(u - e.length, u).toLowerCase() === e
            }

            function K() {
                for (var e = 0, t = u + 1; t < i.length; t++) {
                    var n = i.charAt(t);
                    if ("{" === n) return !0;
                    if ("(" === n) e += 1; else if (")" === n) {
                        if (0 == e) return !1;
                        e -= 1
                    } else if (";" === n || "}" === n) return !1
                }
                return !1
            }

            var m = i.match(/^[\t ]*/)[0], R = new Array(r + 1).join(s), b = 0, v = 0;
            for (var S, A, k = {
                "{": function (e) {
                    k.singleSpace(), y.push(e), k.newLine()
                }, "}": function (e) {
                    k.newLine(), y.push(e), k.newLine()
                }, _lastCharWhitespace: function () {
                    return c.test(y[y.length - 1])
                }, newLine: function (e) {
                    y.length && (e || "\n" === y[y.length - 1] || k.trim(), y.push("\n"), m && y.push(m))
                }, singleSpace: function () {
                    y.length && !k._lastCharWhitespace() && y.push(" ")
                }, preserveSingleSpace: function () {
                    V && k.singleSpace()
                }, trim: function () {
                    for (; k._lastCharWhitespace();) y.pop()
                }
            }, y = [], O = !1, N = !1, D = !1, C = "", L = ""; ;) {
                var I = g(), V = "" !== I, P = -1 !== I.indexOf("\n");
                if (L = C, !(C = h)) break;
                if ("/" === h && "*" === f()) {
                    var j = 0 === b;
                    (P || j) && k.newLine(), y.push(x()), k.newLine(), j && k.newLine(!0)
                } else if ("/" === h && "/" === f()) P || "{" === L || k.trim(), k.singleSpace(), y.push(x()), k.newLine(); else if ("@" === h) {
                    k.preserveSingleSpace(), y.push(h);
                    var B = (void 0, S = u, A = T(": ,;{}()[]/='\""), u = S - 1, d(), A);
                    B.match(/[ :]$/) && (d(), B = T(": ").replace(/\s$/, ""), y.push(B), k.singleSpace()), (B = B.replace(/\s$/, "")) in t && (v += 1, B in n && (D = !0))
                } else "#" === h && "{" === f() ? (k.preserveSingleSpace(), y.push(T("}"))) : "{" === h ? "}" === f(!0) ? (E(), d(), k.singleSpace(), y.push("{}"), k.newLine(), o && 0 === b && k.newLine(!0)) : (b++, m += R, k["{"](h), D ? (D = !1, O = v < b) : O = v <= b) : "}" === h ? (b--, m = m.slice(0, -r), k["}"](h), N = O = !1, v && v--, o && 0 === b && k.newLine(!0)) : ":" === h ? (E(), !O && !D || w("&") || K() ? ":" === f() ? (d(), y.push("::")) : y.push(":") : (N = !0, y.push(":"), k.singleSpace())) : '"' === h || "'" === h ? (k.preserveSingleSpace(), y.push(T(h))) : ";" === h ? (N = !1, y.push(h), k.newLine()) : "(" === h ? w("url") ? (y.push(h), E(), d() && (")" !== h && '"' !== h && "'" !== h ? y.push(T(")")) : u--)) : (p++, k.preserveSingleSpace(), y.push(h), E()) : ")" === h ? (y.push(h), p--) : "," === h ? (y.push(h), E(), _ && !N && p < 1 ? k.newLine() : k.singleSpace()) : ("]" === h || ("[" === h ? k.preserveSingleSpace() : "=" === h ? (E(), h = "=") : k.preserveSingleSpace()), y.push(h))
            }
            var M = "";
            return m && (M += m), M += y.join("").replace(/[\r\n\t ]+$/, ""), a && (M += "\n"), "\n" != l && (M = M.replace(/[\n]/g, l)), M
        }

        function F(e, t) {
            for (var n = 0; n < t.length; n += 1) if (t[n] === e) return !0;
            return !1
        }

        function $(e) {
            return e.replace(/^\s+|\s+$/g, "")
        }

        function y(e, t) {
            return new r(e, t).beautify()
        }

        e = X, t = "\xaa\xb5\xba\xc0-\xd6\xd8-\xf6\xf8-\u02c1\u02c6-\u02d1\u02e0-\u02e4\u02ec\u02ee\u0370-\u0374\u0376\u0377\u037a-\u037d\u0386\u0388-\u038a\u038c\u038e-\u03a1\u03a3-\u03f5\u03f7-\u0481\u048a-\u0527\u0531-\u0556\u0559\u0561-\u0587\u05d0-\u05ea\u05f0-\u05f2\u0620-\u064a\u066e\u066f\u0671-\u06d3\u06d5\u06e5\u06e6\u06ee\u06ef\u06fa-\u06fc\u06ff\u0710\u0712-\u072f\u074d-\u07a5\u07b1\u07ca-\u07ea\u07f4\u07f5\u07fa\u0800-\u0815\u081a\u0824\u0828\u0840-\u0858\u08a0\u08a2-\u08ac\u0904-\u0939\u093d\u0950\u0958-\u0961\u0971-\u0977\u0979-\u097f\u0985-\u098c\u098f\u0990\u0993-\u09a8\u09aa-\u09b0\u09b2\u09b6-\u09b9\u09bd\u09ce\u09dc\u09dd\u09df-\u09e1\u09f0\u09f1\u0a05-\u0a0a\u0a0f\u0a10\u0a13-\u0a28\u0a2a-\u0a30\u0a32\u0a33\u0a35\u0a36\u0a38\u0a39\u0a59-\u0a5c\u0a5e\u0a72-\u0a74\u0a85-\u0a8d\u0a8f-\u0a91\u0a93-\u0aa8\u0aaa-\u0ab0\u0ab2\u0ab3\u0ab5-\u0ab9\u0abd\u0ad0\u0ae0\u0ae1\u0b05-\u0b0c\u0b0f\u0b10\u0b13-\u0b28\u0b2a-\u0b30\u0b32\u0b33\u0b35-\u0b39\u0b3d\u0b5c\u0b5d\u0b5f-\u0b61\u0b71\u0b83\u0b85-\u0b8a\u0b8e-\u0b90\u0b92-\u0b95\u0b99\u0b9a\u0b9c\u0b9e\u0b9f\u0ba3\u0ba4\u0ba8-\u0baa\u0bae-\u0bb9\u0bd0\u0c05-\u0c0c\u0c0e-\u0c10\u0c12-\u0c28\u0c2a-\u0c33\u0c35-\u0c39\u0c3d\u0c58\u0c59\u0c60\u0c61\u0c85-\u0c8c\u0c8e-\u0c90\u0c92-\u0ca8\u0caa-\u0cb3\u0cb5-\u0cb9\u0cbd\u0cde\u0ce0\u0ce1\u0cf1\u0cf2\u0d05-\u0d0c\u0d0e-\u0d10\u0d12-\u0d3a\u0d3d\u0d4e\u0d60\u0d61\u0d7a-\u0d7f\u0d85-\u0d96\u0d9a-\u0db1\u0db3-\u0dbb\u0dbd\u0dc0-\u0dc6\u0e01-\u0e30\u0e32\u0e33\u0e40-\u0e46\u0e81\u0e82\u0e84\u0e87\u0e88\u0e8a\u0e8d\u0e94-\u0e97\u0e99-\u0e9f\u0ea1-\u0ea3\u0ea5\u0ea7\u0eaa\u0eab\u0ead-\u0eb0\u0eb2\u0eb3\u0ebd\u0ec0-\u0ec4\u0ec6\u0edc-\u0edf\u0f00\u0f40-\u0f47\u0f49-\u0f6c\u0f88-\u0f8c\u1000-\u102a\u103f\u1050-\u1055\u105a-\u105d\u1061\u1065\u1066\u106e-\u1070\u1075-\u1081\u108e\u10a0-\u10c5\u10c7\u10cd\u10d0-\u10fa\u10fc-\u1248\u124a-\u124d\u1250-\u1256\u1258\u125a-\u125d\u1260-\u1288\u128a-\u128d\u1290-\u12b0\u12b2-\u12b5\u12b8-\u12be\u12c0\u12c2-\u12c5\u12c8-\u12d6\u12d8-\u1310\u1312-\u1315\u1318-\u135a\u1380-\u138f\u13a0-\u13f4\u1401-\u166c\u166f-\u167f\u1681-\u169a\u16a0-\u16ea\u16ee-\u16f0\u1700-\u170c\u170e-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176c\u176e-\u1770\u1780-\u17b3\u17d7\u17dc\u1820-\u1877\u1880-\u18a8\u18aa\u18b0-\u18f5\u1900-\u191c\u1950-\u196d\u1970-\u1974\u1980-\u19ab\u19c1-\u19c7\u1a00-\u1a16\u1a20-\u1a54\u1aa7\u1b05-\u1b33\u1b45-\u1b4b\u1b83-\u1ba0\u1bae\u1baf\u1bba-\u1be5\u1c00-\u1c23\u1c4d-\u1c4f\u1c5a-\u1c7d\u1ce9-\u1cec\u1cee-\u1cf1\u1cf5\u1cf6\u1d00-\u1dbf\u1e00-\u1f15\u1f18-\u1f1d\u1f20-\u1f45\u1f48-\u1f4d\u1f50-\u1f57\u1f59\u1f5b\u1f5d\u1f5f-\u1f7d\u1f80-\u1fb4\u1fb6-\u1fbc\u1fbe\u1fc2-\u1fc4\u1fc6-\u1fcc\u1fd0-\u1fd3\u1fd6-\u1fdb\u1fe0-\u1fec\u1ff2-\u1ff4\u1ff6-\u1ffc\u2071\u207f\u2090-\u209c\u2102\u2107\u210a-\u2113\u2115\u2119-\u211d\u2124\u2126\u2128\u212a-\u212d\u212f-\u2139\u213c-\u213f\u2145-\u2149\u214e\u2160-\u2188\u2c00-\u2c2e\u2c30-\u2c5e\u2c60-\u2ce4\u2ceb-\u2cee\u2cf2\u2cf3\u2d00-\u2d25\u2d27\u2d2d\u2d30-\u2d67\u2d6f\u2d80-\u2d96\u2da0-\u2da6\u2da8-\u2dae\u2db0-\u2db6\u2db8-\u2dbe\u2dc0-\u2dc6\u2dc8-\u2dce\u2dd0-\u2dd6\u2dd8-\u2dde\u2e2f\u3005-\u3007\u3021-\u3029\u3031-\u3035\u3038-\u303c\u3041-\u3096\u309d-\u309f\u30a1-\u30fa\u30fc-\u30ff\u3105-\u312d\u3131-\u318e\u31a0-\u31ba\u31f0-\u31ff\u3400-\u4db5\u4e00-\u9fcc\ua000-\ua48c\ua4d0-\ua4fd\ua500-\ua60c\ua610-\ua61f\ua62a\ua62b\ua640-\ua66e\ua67f-\ua697\ua6a0-\ua6ef\ua717-\ua71f\ua722-\ua788\ua78b-\ua78e\ua790-\ua793\ua7a0-\ua7aa\ua7f8-\ua801\ua803-\ua805\ua807-\ua80a\ua80c-\ua822\ua840-\ua873\ua882-\ua8b3\ua8f2-\ua8f7\ua8fb\ua90a-\ua925\ua930-\ua946\ua960-\ua97c\ua984-\ua9b2\ua9cf\uaa00-\uaa28\uaa40-\uaa42\uaa44-\uaa4b\uaa60-\uaa76\uaa7a\uaa80-\uaaaf\uaab1\uaab5\uaab6\uaab9-\uaabd\uaac0\uaac2\uaadb-\uaadd\uaae0-\uaaea\uaaf2-\uaaf4\uab01-\uab06\uab09-\uab0e\uab11-\uab16\uab20-\uab26\uab28-\uab2e\uabc0-\uabe2\uac00-\ud7a3\ud7b0-\ud7c6\ud7cb-\ud7fb\uf900-\ufa6d\ufa70-\ufad9\ufb00-\ufb06\ufb13-\ufb17\ufb1d\ufb1f-\ufb28\ufb2a-\ufb36\ufb38-\ufb3c\ufb3e\ufb40\ufb41\ufb43\ufb44\ufb46-\ufbb1\ufbd3-\ufd3d\ufd50-\ufd8f\ufd92-\ufdc7\ufdf0-\ufdfb\ufe70-\ufe74\ufe76-\ufefc\uff21-\uff3a\uff41-\uff5a\uff66-\uffbe\uffc2-\uffc7\uffca-\uffcf\uffd2-\uffd7\uffda-\uffdc", n = new RegExp("[" + t + "]"), i = new RegExp("[" + t + "\u0300-\u036f\u0483-\u0487\u0591-\u05bd\u05bf\u05c1\u05c2\u05c4\u05c5\u05c7\u0610-\u061a\u0620-\u0649\u0672-\u06d3\u06e7-\u06e8\u06fb-\u06fc\u0730-\u074a\u0800-\u0814\u081b-\u0823\u0825-\u0827\u0829-\u082d\u0840-\u0857\u08e4-\u08fe\u0900-\u0903\u093a-\u093c\u093e-\u094f\u0951-\u0957\u0962-\u0963\u0966-\u096f\u0981-\u0983\u09bc\u09be-\u09c4\u09c7\u09c8\u09d7\u09df-\u09e0\u0a01-\u0a03\u0a3c\u0a3e-\u0a42\u0a47\u0a48\u0a4b-\u0a4d\u0a51\u0a66-\u0a71\u0a75\u0a81-\u0a83\u0abc\u0abe-\u0ac5\u0ac7-\u0ac9\u0acb-\u0acd\u0ae2-\u0ae3\u0ae6-\u0aef\u0b01-\u0b03\u0b3c\u0b3e-\u0b44\u0b47\u0b48\u0b4b-\u0b4d\u0b56\u0b57\u0b5f-\u0b60\u0b66-\u0b6f\u0b82\u0bbe-\u0bc2\u0bc6-\u0bc8\u0bca-\u0bcd\u0bd7\u0be6-\u0bef\u0c01-\u0c03\u0c46-\u0c48\u0c4a-\u0c4d\u0c55\u0c56\u0c62-\u0c63\u0c66-\u0c6f\u0c82\u0c83\u0cbc\u0cbe-\u0cc4\u0cc6-\u0cc8\u0cca-\u0ccd\u0cd5\u0cd6\u0ce2-\u0ce3\u0ce6-\u0cef\u0d02\u0d03\u0d46-\u0d48\u0d57\u0d62-\u0d63\u0d66-\u0d6f\u0d82\u0d83\u0dca\u0dcf-\u0dd4\u0dd6\u0dd8-\u0ddf\u0df2\u0df3\u0e34-\u0e3a\u0e40-\u0e45\u0e50-\u0e59\u0eb4-\u0eb9\u0ec8-\u0ecd\u0ed0-\u0ed9\u0f18\u0f19\u0f20-\u0f29\u0f35\u0f37\u0f39\u0f41-\u0f47\u0f71-\u0f84\u0f86-\u0f87\u0f8d-\u0f97\u0f99-\u0fbc\u0fc6\u1000-\u1029\u1040-\u1049\u1067-\u106d\u1071-\u1074\u1082-\u108d\u108f-\u109d\u135d-\u135f\u170e-\u1710\u1720-\u1730\u1740-\u1750\u1772\u1773\u1780-\u17b2\u17dd\u17e0-\u17e9\u180b-\u180d\u1810-\u1819\u1920-\u192b\u1930-\u193b\u1951-\u196d\u19b0-\u19c0\u19c8-\u19c9\u19d0-\u19d9\u1a00-\u1a15\u1a20-\u1a53\u1a60-\u1a7c\u1a7f-\u1a89\u1a90-\u1a99\u1b46-\u1b4b\u1b50-\u1b59\u1b6b-\u1b73\u1bb0-\u1bb9\u1be6-\u1bf3\u1c00-\u1c22\u1c40-\u1c49\u1c5b-\u1c7d\u1cd0-\u1cd2\u1d00-\u1dbe\u1e01-\u1f15\u200c\u200d\u203f\u2040\u2054\u20d0-\u20dc\u20e1\u20e5-\u20f0\u2d81-\u2d96\u2de0-\u2dff\u3021-\u3028\u3099\u309a\ua640-\ua66d\ua674-\ua67d\ua69f\ua6f0-\ua6f1\ua7f8-\ua800\ua806\ua80b\ua823-\ua827\ua880-\ua881\ua8b4-\ua8c4\ua8d0-\ua8d9\ua8f3-\ua8f7\ua900-\ua909\ua926-\ua92d\ua930-\ua945\ua980-\ua983\ua9b3-\ua9c0\uaa00-\uaa27\uaa40-\uaa41\uaa4c-\uaa4d\uaa50-\uaa59\uaa7b\uaae0-\uaae9\uaaf2-\uaaf3\uabc0-\uabe1\uabec\uabed\uabf0-\uabf9\ufb20-\ufb28\ufe00-\ufe0f\ufe20-\ufe26\ufe33\ufe34\ufe4d-\ufe4f\uff10-\uff19\uff3f]"), e.newline = /[\n\r\u2028\u2029]/, e.lineBreak = new RegExp("\r\n|" + e.newline.source), e.allLineBreaks = new RegExp(e.lineBreak.source, "g"), e.isIdentifierStart = function (e) {
            return e < 65 ? 36 === e || 64 === e : e < 91 || (e < 97 ? 95 === e : e < 123 || 170 <= e && n.test(String.fromCharCode(e)))
        }, e.isIdentifierChar = function (e) {
            return e < 48 ? 36 === e : e < 58 || !(e < 65) && (e < 91 || (e < 97 ? 95 === e : e < 123 || 170 <= e && i.test(String.fromCharCode(e))))
        };
        var L = {
            BlockStatement: "BlockStatement",
            Statement: "Statement",
            ObjectLiteral: "ObjectLiteral",
            ArrayLiteral: "ArrayLiteral",
            ForInitializer: "ForInitializer",
            Conditional: "Conditional",
            Expression: "Expression"
        };

        function r(i, e) {
            var _, r, s, a, o, l, h, c, u, t, n, p, d, f = [], T = "";

            function E(e, t) {
                var n = 0;
                return e && (n = e.indentation_level, !_.just_added_newline() && e.line_indent_level > n && (n = e.line_indent_level)), {
                    mode: t,
                    parent: e,
                    last_text: e ? e.last_text : "",
                    last_word: e ? e.last_word : "",
                    declaration_statement: !1,
                    declaration_assignment: !1,
                    multiline_frame: !1,
                    if_block: !1,
                    else_block: !1,
                    do_block: !1,
                    do_while: !1,
                    in_case_statement: !1,
                    in_case: !1,
                    case_body: !1,
                    indentation_level: n,
                    line_indent_level: e ? e.line_indent_level : n,
                    start_line_index: _.get_line_number(),
                    ternary_depth: 0
                }
            }

            for (p = {
                TK_START_EXPR: function () {
                    O();
                    var e = L.Expression;
                    if ("[" === a.text) {
                        if ("TK_WORD" === o || ")" === c.last_text) return "TK_RESERVED" === o && F(c.last_text, s.line_starters) && (_.space_before_token = !0), v(e), R(), b(), void (d.space_in_paren && (_.space_before_token = !0));
                        e = L.ArrayLiteral, S(c.mode) && ("[" !== c.last_text && ("," !== c.last_text || "]" !== l && "}" !== l) || d.keep_array_indentation || K())
                    } else "TK_RESERVED" === o && "for" === c.last_text ? e = L.ForInitializer : "TK_RESERVED" === o && F(c.last_text, ["if", "while"]) && (e = L.Conditional);
                    ";" === c.last_text || "TK_START_BLOCK" === o ? K() : "TK_END_EXPR" === o || "TK_START_EXPR" === o || "TK_END_BLOCK" === o || "." === c.last_text ? w(a.wanted_newline) : "TK_RESERVED" === o && "(" === a.text || "TK_WORD" === o || "TK_OPERATOR" === o ? "TK_RESERVED" === o && ("function" === c.last_word || "typeof" === c.last_word) || "*" === c.last_text && "function" === l ? d.space_after_anon_function && (_.space_before_token = !0) : "TK_RESERVED" !== o || !F(c.last_text, s.line_starters) && "catch" !== c.last_text || d.space_before_conditional && (_.space_before_token = !0) : _.space_before_token = !0;
                    "(" === a.text && "TK_RESERVED" === o && "await" === c.last_word && (_.space_before_token = !0);
                    "(" === a.text && ("TK_EQUALS" !== o && "TK_OPERATOR" !== o || y() || w());
                    v(e), R(), d.space_in_paren && (_.space_before_token = !0);
                    b()
                }, TK_END_EXPR: function () {
                    for (; c.mode === L.Statement;) k();
                    c.multiline_frame && w("]" === a.text && S(c.mode) && !d.keep_array_indentation);
                    d.space_in_paren && ("TK_START_EXPR" !== o || d.space_in_empty_paren ? _.space_before_token = !0 : (_.trim(), _.space_before_token = !1));
                    "]" === a.text && d.keep_array_indentation ? (R(), k()) : (k(), R());
                    _.remove_redundant_indentation(u), c.do_while && u.mode === L.Conditional && (u.mode = L.Expression, c.do_block = !1, c.do_while = !1)
                }, TK_START_BLOCK: function () {
                    var e = D(1), t = D(2);
                    t && (":" === t.text && F(e.type, ["TK_STRING", "TK_WORD", "TK_RESERVED"]) || F(e.text, ["get", "set"]) && F(t.type, ["TK_WORD", "TK_RESERVED"])) ? F(l, ["class", "interface"]) ? v(L.BlockStatement) : v(L.ObjectLiteral) : v(L.BlockStatement);
                    var n = !e.comments_before.length && "}" === e.text && "function" === c.last_word && "TK_END_EXPR" === o;
                    "expand" === d.brace_style || "none" === d.brace_style && a.wanted_newline ? "TK_OPERATOR" !== o && (n || "TK_EQUALS" === o || "TK_RESERVED" === o && N(c.last_text) && "else" !== c.last_text) ? _.space_before_token = !0 : K(!1, !0) : "TK_OPERATOR" !== o && "TK_START_EXPR" !== o ? "TK_START_BLOCK" === o ? K() : _.space_before_token = !0 : S(u.mode) && "," === c.last_text && ("}" === l ? _.space_before_token = !0 : K());
                    R(), b()
                }, TK_END_BLOCK: function () {
                    for (; c.mode === L.Statement;) k();
                    var e = "TK_START_BLOCK" === o;
                    "expand" === d.brace_style ? e || K() : e || (S(c.mode) && d.keep_array_indentation ? (d.keep_array_indentation = !1, K(), d.keep_array_indentation = !0) : K());
                    k(), R()
                }, TK_WORD: C, TK_RESERVED: C, TK_SEMICOLON: function () {
                    O() && (_.space_before_token = !1);
                    for (; c.mode === L.Statement && !c.if_block && !c.do_block;) k();
                    R()
                }, TK_STRING: function () {
                    O() ? _.space_before_token = !0 : "TK_RESERVED" === o || "TK_WORD" === o ? _.space_before_token = !0 : "TK_COMMA" === o || "TK_START_EXPR" === o || "TK_EQUALS" === o || "TK_OPERATOR" === o ? y() || w() : K();
                    R()
                }, TK_EQUALS: function () {
                    O();
                    c.declaration_statement && (c.declaration_assignment = !0);
                    _.space_before_token = !0, R(), _.space_before_token = !0
                }, TK_OPERATOR: function () {
                    O();
                    if ("TK_RESERVED" === o && N(c.last_text)) return _.space_before_token = !0, void R();
                    if ("*" === a.text && "TK_DOT" === o) return void R();
                    if (":" === a.text && c.in_case) return c.case_body = !0, b(), R(), K(), void (c.in_case = !1);
                    if ("::" === a.text) return void R();
                    "TK_OPERATOR" === o && w();
                    var e = !0, t = !0;
                    F(a.text, ["--", "++", "!", "~"]) || F(a.text, ["-", "+"]) && (F(o, ["TK_START_BLOCK", "TK_START_EXPR", "TK_EQUALS", "TK_OPERATOR"]) || F(c.last_text, s.line_starters) || "," === c.last_text) ? (t = e = !1, !a.wanted_newline || "--" !== a.text && "++" !== a.text || K(!1, !0), ";" === c.last_text && A(c.mode) && (e = !0), "TK_RESERVED" === o ? e = !0 : "TK_END_EXPR" === o ? e = !("]" === c.last_text && ("--" === a.text || "++" === a.text)) : "TK_OPERATOR" === o && (e = F(a.text, ["--", "-", "++", "+"]) && F(c.last_text, ["--", "-", "++", "+"]), F(a.text, ["+", "-"]) && F(c.last_text, ["--", "++"]) && (t = !0)), c.mode !== L.BlockStatement && c.mode !== L.Statement || "{" !== c.last_text && ";" !== c.last_text || K()) : ":" === a.text ? 0 === c.ternary_depth ? e = !1 : c.ternary_depth -= 1 : "?" === a.text ? c.ternary_depth += 1 : "*" === a.text && "TK_RESERVED" === o && "function" === c.last_text && (t = e = !1);
                    _.space_before_token = _.space_before_token || e, R(), _.space_before_token = t
                }, TK_COMMA: function () {
                    if (c.declaration_statement) return A(c.parent.mode) && (c.declaration_assignment = !1), R(), void (c.declaration_assignment ? K(c.declaration_assignment = !1, !0) : (_.space_before_token = !0, d.comma_first && w()));
                    R(), c.mode === L.ObjectLiteral || c.mode === L.Statement && c.parent.mode === L.ObjectLiteral ? (c.mode === L.Statement && k(), K()) : (_.space_before_token = !0, d.comma_first && w())
                }, TK_BLOCK_COMMENT: function () {
                    if (_.raw) return _.add_raw_token(a), void (a.directives && "end" === a.directives.preserve && (d.test_output_raw || (_.raw = !1)));
                    if (a.directives) return K(!1, !0), R(), "start" === a.directives.preserve && (_.raw = !0), void K(!1, !0);
                    if (!X.newline.test(a.text) && !a.wanted_newline) return _.space_before_token = !0, R(), void (_.space_before_token = !0);
                    var e, t = function (e) {
                        e = e.replace(/\x0d/g, "");
                        var t = [], n = e.indexOf("\n");
                        for (; -1 !== n;) t.push(e.substring(0, n)), e = e.substring(n + 1), n = e.indexOf("\n");
                        e.length && t.push(e);
                        return t
                    }(a.text), n = !1, i = !1, r = a.whitespace_before, s = r.length;
                    K(!1, !0), 1 < t.length && (!function (e, t) {
                        for (var n = 0; n < e.length; n++) {
                            var i = $(e[n]);
                            if (i.charAt(0) !== t) return !1
                        }
                        return !0
                    }(t.slice(1), "*") ? function (e, t) {
                        for (var n, i = 0, r = e.length; i < r; i++) if ((n = e[i]) && 0 !== n.indexOf(t)) return !1;
                        return !0
                    }(t.slice(1), r) && (i = !0) : n = !0);
                    for (R(t[0]), e = 1; e < t.length; e++) K(!1, !0), n ? R(" " + t[e].replace(/^\s+/g, "")) : i && t[e].length > s ? R(t[e].substring(s)) : _.add_token(t[e]);
                    K(!1, !0)
                }, TK_COMMENT: function () {
                    a.wanted_newline ? K(!1, !0) : _.trim(!0);
                    _.space_before_token = !0, R(), K(!1, !0)
                }, TK_DOT: function () {
                    O();
                    "TK_RESERVED" === o && N(c.last_text) ? _.space_before_token = !0 : w(")" === c.last_text && d.break_chained_methods);
                    R()
                }, TK_UNKNOWN: function () {
                    R(), "\n" === a.text[a.text.length - 1] && K()
                }, TK_EOF: function () {
                    for (; c.mode === L.Statement;) k()
                }
            }, d = {}, (e = e || {}).braces_on_own_line !== undefined && (d.brace_style = e.braces_on_own_line ? "expand" : "collapse"), d.brace_style = e.brace_style ? e.brace_style : d.brace_style ? d.brace_style : "collapse", "expand-strict" === d.brace_style && (d.brace_style = "expand"), d.indent_size = e.indent_size ? parseInt(e.indent_size, 10) : 4, d.indent_char = e.indent_char ? e.indent_char : " ", d.eol = e.eol ? e.eol : "\n", d.preserve_newlines = e.preserve_newlines === undefined || e.preserve_newlines, d.break_chained_methods = e.break_chained_methods !== undefined && e.break_chained_methods, d.max_preserve_newlines = e.max_preserve_newlines === undefined ? 0 : parseInt(e.max_preserve_newlines, 10), d.space_in_paren = e.space_in_paren !== undefined && e.space_in_paren, d.space_in_empty_paren = e.space_in_empty_paren !== undefined && e.space_in_empty_paren, d.jslint_happy = e.jslint_happy !== undefined && e.jslint_happy, d.space_after_anon_function = e.space_after_anon_function !== undefined && e.space_after_anon_function, d.keep_array_indentation = e.keep_array_indentation !== undefined && e.keep_array_indentation, d.space_before_conditional = e.space_before_conditional === undefined || e.space_before_conditional, d.unescape_strings = e.unescape_strings !== undefined && e.unescape_strings, d.wrap_line_length = e.wrap_line_length === undefined ? 0 : parseInt(e.wrap_line_length, 10), d.e4x = e.e4x !== undefined && e.e4x, d.end_with_newline = e.end_with_newline !== undefined && e.end_with_newline, d.comma_first = e.comma_first !== undefined && e.comma_first, d.test_output_raw = e.test_output_raw !== undefined && e.test_output_raw, d.jslint_happy && (d.space_after_anon_function = !0), e.indent_with_tabs && (d.indent_char = "\t", d.indent_size = 1), d.eol = d.eol.replace(/\\r/, "\r").replace(/\\n/, "\n"), h = ""; 0 < d.indent_size;) h += d.indent_char, d.indent_size -= 1;
            var g = 0;
            if (i && i.length) {
                for (; " " === i.charAt(g) || "\t" === i.charAt(g);) T += i.charAt(g), g += 1;
                i = i.substring(g)
            }

            function x(e) {
                var t = e.newlines;
                if (d.keep_array_indentation && S(c.mode)) for (n = 0; n < t; n += 1) K(0 < n); else if (d.max_preserve_newlines && t > d.max_preserve_newlines && (t = d.max_preserve_newlines), d.preserve_newlines && 1 < e.newlines) {
                    K();
                    for (var n = 1; n < t; n += 1) K(!0)
                }
                p[(a = e).type]()
            }

            function w(e) {
                if (e = e !== undefined && e, !_.just_added_newline()) if (d.preserve_newlines && a.wanted_newline || e) K(!1, !0); else if (d.wrap_line_length) {
                    _.current_line.get_character_count() + a.text.length + (_.space_before_token ? 1 : 0) >= d.wrap_line_length && K(!1, !0)
                }
            }

            function K(e, t) {
                if (!t && ";" !== c.last_text && "," !== c.last_text && "=" !== c.last_text && "TK_OPERATOR" !== o) for (; c.mode === L.Statement && !c.if_block && !c.do_block;) k();
                _.add_new_line(e) && (c.multiline_frame = !0)
            }

            function m() {
                _.just_added_newline() && (d.keep_array_indentation && S(c.mode) && a.wanted_newline ? (_.current_line.push(a.whitespace_before), _.space_before_token = !1) : _.set_indent(c.indentation_level) && (c.line_indent_level = c.indentation_level))
            }

            function R(e) {
                _.raw ? _.add_raw_token(a) : (d.comma_first && "TK_COMMA" === o && _.just_added_newline() && "," === _.previous_line.last() && (_.previous_line.pop(), m(), _.add_token(","), _.space_before_token = !0), e = e || a.text, m(), _.add_token(e))
            }

            function b() {
                c.indentation_level += 1
            }

            function v(e) {
                c ? (t.push(c), u = c) : u = E(null, e), c = E(u, e)
            }

            function S(e) {
                return e === L.ArrayLiteral
            }

            function A(e) {
                return F(e, [L.Expression, L.ForInitializer, L.Conditional])
            }

            function k() {
                0 < t.length && (u = c, c = t.pop(), u.mode === L.Statement && _.remove_redundant_indentation(u))
            }

            function y() {
                return c.parent.mode === L.ObjectLiteral && c.mode === L.Statement && (":" === c.last_text && 0 === c.ternary_depth || "TK_RESERVED" === o && F(c.last_text, ["get", "set"]))
            }

            function O() {
                return !!("TK_RESERVED" === o && F(c.last_text, ["var", "let", "const"]) && "TK_WORD" === a.type || "TK_RESERVED" === o && "do" === c.last_text || "TK_RESERVED" === o && "return" === c.last_text && !a.wanted_newline || "TK_RESERVED" === o && "else" === c.last_text && ("TK_RESERVED" !== a.type || "if" !== a.text) || "TK_END_EXPR" === o && (u.mode === L.ForInitializer || u.mode === L.Conditional) || "TK_WORD" === o && c.mode === L.BlockStatement && !c.in_case && "--" !== a.text && "++" !== a.text && "function" !== l && "TK_WORD" !== a.type && "TK_RESERVED" !== a.type || c.mode === L.ObjectLiteral && (":" === c.last_text && 0 === c.ternary_depth || "TK_RESERVED" === o && F(c.last_text, ["get", "set"]))) && (v(L.Statement), b(), "TK_RESERVED" === o && F(c.last_text, ["var", "let", "const"]) && "TK_WORD" === a.type && (c.declaration_statement = !0), y() || w("TK_RESERVED" === a.type && F(a.text, ["do", "for", "if", "while"])), !0)
            }

            function N(e) {
                return F(e, ["case", "return", "do", "if", "throw", "else"])
            }

            function D(e) {
                var t = r + (e || 0);
                return t < 0 || t >= f.length ? null : f[t]
            }

            function C() {
                ("TK_RESERVED" === a.type && c.mode !== L.ObjectLiteral && F(a.text, ["set", "get"]) && (a.type = "TK_WORD"), "TK_RESERVED" === a.type && c.mode === L.ObjectLiteral) && (":" == D(1).text && (a.type = "TK_WORD"));
                if (O() || !a.wanted_newline || A(c.mode) || "TK_OPERATOR" === o && "--" !== c.last_text && "++" !== c.last_text || "TK_EQUALS" === o || !d.preserve_newlines && "TK_RESERVED" === o && F(c.last_text, ["var", "let", "const", "set", "get"]) || K(), c.do_block && !c.do_while) {
                    if ("TK_RESERVED" === a.type && "while" === a.text) return _.space_before_token = !0, R(), _.space_before_token = !0, void (c.do_while = !0);
                    K(), c.do_block = !1
                }
                if (c.if_block) if (c.else_block || "TK_RESERVED" !== a.type || "else" !== a.text) {
                    for (; c.mode === L.Statement;) k();
                    c.if_block = !1, c.else_block = !1
                } else c.else_block = !0;
                if ("TK_RESERVED" === a.type && ("case" === a.text || "default" === a.text && c.in_case_statement)) return K(), (c.case_body || d.jslint_happy) && (0 < c.indentation_level && (!c.parent || c.indentation_level > c.parent.indentation_level) && (c.indentation_level -= 1), c.case_body = !1), R(), c.in_case = !0, void (c.in_case_statement = !0);
                if ("TK_RESERVED" === a.type && "function" === a.text && ((F(c.last_text, ["}", ";"]) || _.just_added_newline() && !F(c.last_text, ["[", "{", ":", "=", ","])) && (_.just_added_blankline() || a.comments_before.length || (K(), K(!0))), "TK_RESERVED" === o || "TK_WORD" === o ? "TK_RESERVED" === o && F(c.last_text, ["get", "set", "new", "return", "export", "async"]) ? _.space_before_token = !0 : "TK_RESERVED" === o && "default" === c.last_text && "export" === l ? _.space_before_token = !0 : K() : "TK_OPERATOR" === o || "=" === c.last_text ? _.space_before_token = !0 : (c.multiline_frame || !A(c.mode) && !S(c.mode)) && K()), "TK_COMMA" !== o && "TK_START_EXPR" !== o && "TK_EQUALS" !== o && "TK_OPERATOR" !== o || y() || w(), "TK_RESERVED" === a.type && F(a.text, ["function", "get", "set"])) return R(), void (c.last_word = a.text);
                (n = "NONE", "TK_END_BLOCK" === o ? "TK_RESERVED" === a.type && F(a.text, ["else", "catch", "finally"]) ? "expand" === d.brace_style || "end-expand" === d.brace_style || "none" === d.brace_style && a.wanted_newline ? n = "NEWLINE" : (n = "SPACE", _.space_before_token = !0) : n = "NEWLINE" : "TK_SEMICOLON" === o && c.mode === L.BlockStatement ? n = "NEWLINE" : "TK_SEMICOLON" === o && A(c.mode) ? n = "SPACE" : "TK_STRING" === o ? n = "NEWLINE" : "TK_RESERVED" === o || "TK_WORD" === o || "*" === c.last_text && "function" === l ? n = "SPACE" : "TK_START_BLOCK" === o ? n = "NEWLINE" : "TK_END_EXPR" === o && (_.space_before_token = !0, n = "NEWLINE"), "TK_RESERVED" === a.type && F(a.text, s.line_starters) && ")" !== c.last_text && (n = "else" === c.last_text || "export" === c.last_text ? "SPACE" : "NEWLINE"), "TK_RESERVED" === a.type && F(a.text, ["else", "catch", "finally"])) ? "TK_END_BLOCK" !== o || "expand" === d.brace_style || "end-expand" === d.brace_style || "none" === d.brace_style && a.wanted_newline ? K() : (_.trim(!0), "}" !== _.current_line.last() && K(), _.space_before_token = !0) : "NEWLINE" === n ? "TK_RESERVED" === o && N(c.last_text) ? _.space_before_token = !0 : "TK_END_EXPR" !== o ? "TK_START_EXPR" === o && "TK_RESERVED" === a.type && F(a.text, ["var", "let", "const"]) || ":" === c.last_text || ("TK_RESERVED" === a.type && "if" === a.text && "else" === c.last_text ? _.space_before_token = !0 : K()) : "TK_RESERVED" === a.type && F(a.text, s.line_starters) && ")" !== c.last_text && K() : c.multiline_frame && S(c.mode) && "," === c.last_text && "}" === l ? K() : "SPACE" === n && (_.space_before_token = !0);
                R(), c.last_word = a.text, "TK_RESERVED" === a.type && "do" === a.text && (c.do_block = !0), "TK_RESERVED" === a.type && "if" === a.text && (c.if_block = !0)
            }

            o = "TK_START_BLOCK", l = "", (_ = new I(h, T)).raw = d.test_output_raw, t = [], v(L.BlockStatement), this.beautify = function () {
                var e, t;
                for (s = new V(i, d, h), f = s.tokenize(), r = 0; e = D();) {
                    for (var n = 0; n < e.comments_before.length; n++) x(e.comments_before[n]);
                    x(e), l = c.last_text, o = e.type, c.last_text = e.text, r += 1
                }
                return t = _.get_code(), d.end_with_newline && (t += "\n"), "\n" != d.eol && (t = t.replace(/[\n]/g, d.eol)), t
            }
        }

        function s(t) {
            var n = 0, i = -1, r = [], s = !0;
            this.set_indent = function (e) {
                n = t.baseIndentLength + e * t.indent_length, i = e
            }, this.get_character_count = function () {
                return n
            }, this.is_empty = function () {
                return s
            }, this.last = function () {
                return this._empty ? null : r[r.length - 1]
            }, this.push = function (e) {
                r.push(e), n += e.length, s = !1
            }, this.pop = function () {
                var e = null;
                return s || (e = r.pop(), n -= e.length, s = 0 === r.length), e
            }, this.remove_indent = function () {
                0 < i && (i -= 1, n -= t.indent_length)
            }, this.trim = function () {
                for (; " " === this.last();) {
                    r.pop();
                    n -= 1
                }
                s = 0 === r.length
            }, this.toString = function () {
                var e = "";
                return this._empty || (0 <= i && (e = t.indent_cache[i]), e += r.join("")), e
            }
        }

        function I(t, n) {
            n = n || "", this.indent_cache = [n], this.baseIndentLength = n.length, this.indent_length = t.length, this.raw = !1;
            var i = [];
            this.baseIndentString = n, this.indent_string = t, this.previous_line = null, this.current_line = null, this.space_before_token = !1, this.add_outputline = function () {
                this.previous_line = this.current_line, this.current_line = new s(this), i.push(this.current_line)
            }, this.add_outputline(), this.get_line_number = function () {
                return i.length
            }, this.add_new_line = function (e) {
                return (1 !== this.get_line_number() || !this.just_added_newline()) && (!(!e && this.just_added_newline()) && (this.raw || this.add_outputline(), !0))
            }, this.get_code = function () {
                return i.join("\n").replace(/[\r\n\t ]+$/, "")
            }, this.set_indent = function (e) {
                if (1 < i.length) {
                    for (; e >= this.indent_cache.length;) this.indent_cache.push(this.indent_cache[this.indent_cache.length - 1] + this.indent_string);
                    return this.current_line.set_indent(e), !0
                }
                return this.current_line.set_indent(0), !1
            }, this.add_raw_token = function (e) {
                for (var t = 0; t < e.newlines; t++) this.add_outputline();
                this.current_line.push(e.whitespace_before), this.current_line.push(e.text), this.space_before_token = !1
            }, this.add_token = function (e) {
                this.add_space_before_token(), this.current_line.push(e)
            }, this.add_space_before_token = function () {
                this.space_before_token && !this.just_added_newline() && this.current_line.push(" "), this.space_before_token = !1
            }, this.remove_redundant_indentation = function (e) {
                if (!e.multiline_frame && e.mode !== L.ForInitializer && e.mode !== L.Conditional) for (var t = e.start_line_index, n = i.length; t < n;) i[t].remove_indent(), t++
            }, this.trim = function (e) {
                for (e = e !== undefined && e, this.current_line.trim(t, n); e && 1 < i.length && this.current_line.is_empty();) i.pop(), this.current_line = i[i.length - 1], this.current_line.trim();
                this.previous_line = 1 < i.length ? i[i.length - 2] : null
            }, this.just_added_newline = function () {
                return this.current_line.is_empty()
            }, this.just_added_blankline = function () {
                return !!this.just_added_newline() && (1 === i.length || i[i.length - 2].is_empty())
            }
        }

        var Q = function (e, t, n, i, r, s) {
            this.type = e, this.text = t, this.comments_before = [], this.newlines = n || 0, this.wanted_newline = 0 < n, this.whitespace_before = i || "", this.parent = null, this.directives = null
        };

        function V(v, S, e) {
            var A = "\n\r\t ".split(""), k = /[0-9]/, y = /[01234567]/, O = /[0123456789abcdefABCDEF]/,
                N = "+ - * / % & ++ -- = += -= *= /= %= == === != !== > < >= <= >> << >>> >>>= >>= <<= && &= | || ! ~ , : ? ^ ^= |= :: =>".split(" ");
            this.line_starters = "continue,try,throw,return,var,let,const,if,switch,case,default,for,while,break,function,import,export".split(",");
            var D, C, L, I, V, P,
                j = this.line_starters.concat(["do", "in", "else", "get", "set", "new", "catch", "finally", "typeof", "yield", "async", "await"]),
                B = /([\s\S]*?)((?:\*\/)|$)/g, M = /([^\n\r\u2028\u2029]*)/g, U = /\/\* beautify( \w+[:]\w+)+ \*\//g,
                W = / (\w+)[:](\w+)/g, z = /([\s\S]*?)((?:\/\*\sbeautify\signore:end\s\*\/)|$)/g,
                G = /((<\?php|<\?=)[\s\S]*?\?>)|(<%[\s\S]*?%>)/g;

            function _() {
                var e, t, n = [];
                if (D = 0, C = "", P <= V) return ["", "TK_EOF"];
                t = I.length ? I[I.length - 1] : new Q("TK_START_BLOCK", "{");
                var i = v.charAt(V);
                for (V += 1; F(i, A);) {
                    if (X.newline.test(i) ? "\n" === i && "\r" === v.charAt(V - 2) || (D += 1, n = []) : n.push(i), P <= V) return ["", "TK_EOF"];
                    i = v.charAt(V), V += 1
                }
                if (n.length && (C = n.join("")), k.test(i)) {
                    var r = !0, s = !0, _ = k;
                    for ("0" === i && V < P && /[Xxo]/.test(v.charAt(V)) ? (s = r = !1, i += v.charAt(V), V += 1, _ = /[o]/.test(v.charAt(V)) ? y : O) : (i = "", V -= 1); V < P && _.test(v.charAt(V));) i += v.charAt(V), V += 1, r && V < P && "." === v.charAt(V) && (i += v.charAt(V), V += 1, r = !1), s && V < P && /[Ee]/.test(v.charAt(V)) && (i += v.charAt(V), (V += 1) < P && /[+-]/.test(v.charAt(V)) && (i += v.charAt(V), V += 1), r = s = !1);
                    return [i, "TK_WORD"]
                }
                if (X.isIdentifierStart(v.charCodeAt(V - 1))) {
                    if (V < P) for (; X.isIdentifierChar(v.charCodeAt(V)) && (i += v.charAt(V), (V += 1) !== P);) ;
                    return "TK_DOT" === t.type || "TK_RESERVED" === t.type && F(t.text, ["set", "get"]) || !F(i, j) ? [i, "TK_WORD"] : "in" === i ? [i, "TK_OPERATOR"] : [i, "TK_RESERVED"]
                }
                if ("(" === i || "[" === i) return [i, "TK_START_EXPR"];
                if (")" === i || "]" === i) return [i, "TK_END_EXPR"];
                if ("{" === i) return [i, "TK_START_BLOCK"];
                if ("}" === i) return [i, "TK_END_BLOCK"];
                if (";" === i) return [i, "TK_SEMICOLON"];
                if ("/" === i) {
                    var a = "";
                    if ("*" === v.charAt(V)) {
                        var o;
                        V += 1, B.lastIndex = V, a = "/*" + (o = B.exec(v))[0], V += o[0].length;
                        var l = function (e) {
                            if (!e.match(U)) return null;
                            var t = {};
                            W.lastIndex = 0;
                            for (var n = W.exec(e); n;) t[n[1]] = n[2], n = W.exec(e);
                            return t
                        }(a);
                        return l && "start" === l.ignore && (z.lastIndex = V, a += (o = z.exec(v))[0], V += o[0].length), [a = a.replace(X.lineBreak, "\n"), "TK_BLOCK_COMMENT", l]
                    }
                    if ("/" === v.charAt(V)) return V += 1, M.lastIndex = V, a = "//" + (o = M.exec(v))[0], V += o[0].length, [a, "TK_COMMENT"]
                }
                if ("`" === i || "'" === i || '"' === i || ("/" === i || S.e4x && "<" === i && v.slice(V - 1).match(/^<([-a-zA-Z:0-9_.]+|{[^{}]*}|!\[CDATA\[[\s\S]*?\]\])(\s+[-a-zA-Z:0-9_.]+\s*=\s*('[^']*'|"[^"]*"|{.*?}))*\s*(\/?)\s*>/)) && ("TK_RESERVED" === t.type && F(t.text, ["return", "case", "throw", "else", "do", "typeof", "yield"]) || "TK_END_EXPR" === t.type && ")" === t.text && t.parent && "TK_RESERVED" === t.parent.type && F(t.parent.text, ["if", "while", "for"]) || F(t.type, ["TK_COMMENT", "TK_START_EXPR", "TK_START_BLOCK", "TK_END_BLOCK", "TK_OPERATOR", "TK_EQUALS", "TK_EOF", "TK_SEMICOLON", "TK_COMMA"]))) {
                    var h = i, c = !1, u = !1;
                    if (e = i, "/" === h) for (var p = !1; V < P && (c || p || v.charAt(V) !== h) && !X.newline.test(v.charAt(V));) e += v.charAt(V), c ? c = !1 : (c = "\\" === v.charAt(V), "[" === v.charAt(V) ? p = !0 : "]" === v.charAt(V) && (p = !1)), V += 1; else if (S.e4x && "<" === h) {
                        var d = /<(\/?)([-a-zA-Z:0-9_.]+|{[^{}]*}|!\[CDATA\[[\s\S]*?\]\])(\s+[-a-zA-Z:0-9_.]+\s*=\s*('[^']*'|"[^"]*"|{.*?}))*\s*(\/?)\s*>/g,
                            f = v.slice(V - 1), T = d.exec(f);
                        if (T && 0 === T.index) {
                            for (var E = T[2], g = 0; T;) {
                                var x = !!T[1], w = T[2], K = !!T[T.length - 1] || "![CDATA[" === w.slice(0, 8);
                                if (w !== E || K || (x ? --g : ++g), g <= 0) break;
                                T = d.exec(f)
                            }
                            var m = T ? T.index + T[0].length : f.length;
                            return f = f.slice(0, m), V += m - 1, [f = f.replace(X.lineBreak, "\n"), "TK_STRING"]
                        }
                    } else for (; V < P && (c || v.charAt(V) !== h && ("`" === h || !X.newline.test(v.charAt(V))));) (c || "`" === h) && X.newline.test(v.charAt(V)) ? ("\r" === v.charAt(V) && "\n" === v.charAt(V + 1) && (V += 1), e += "\n") : e += v.charAt(V), c ? ("x" !== v.charAt(V) && "u" !== v.charAt(V) || (u = !0), c = !1) : c = "\\" === v.charAt(V), V += 1;
                    if (u && S.unescape_strings && (e = function (e) {
                        var t, n = !1, i = "", r = 0, s = "", _ = 0;
                        for (; n || r < e.length;) if (t = e.charAt(r), r++, n) {
                            if (n = !1, "x" === t) s = e.substr(r, 2), r += 2; else {
                                if ("u" !== t) {
                                    i += "\\" + t;
                                    continue
                                }
                                s = e.substr(r, 4), r += 4
                            }
                            if (!s.match(/^[0123456789abcdefABCDEF]+$/)) return e;
                            if (0 <= (_ = parseInt(s, 16)) && _ < 32) {
                                i += "x" === t ? "\\x" + s : "\\u" + s;
                                continue
                            }
                            if (34 === _ || 39 === _ || 92 === _) i += "\\" + String.fromCharCode(_); else {
                                if ("x" === t && 126 < _ && _ <= 255) return e;
                                i += String.fromCharCode(_)
                            }
                        } else "\\" === t ? n = !0 : i += t;
                        return i
                    }(e)), V < P && v.charAt(V) === h && (e += h, V += 1, "/" === h)) for (; V < P && X.isIdentifierStart(v.charCodeAt(V));) e += v.charAt(V), V += 1;
                    return [e, "TK_STRING"]
                }
                if ("#" === i) {
                    if (0 === I.length && "!" === v.charAt(V)) {
                        for (e = i; V < P && "\n" !== i;) e += i = v.charAt(V), V += 1;
                        return [$(e) + "\n", "TK_UNKNOWN"]
                    }
                    var R = "#";
                    if (V < P && k.test(v.charAt(V))) {
                        for (; R += i = v.charAt(V), (V += 1) < P && "#" !== i && "=" !== i;) ;
                        return "#" === i || ("[" === v.charAt(V) && "]" === v.charAt(V + 1) ? (R += "[]", V += 2) : "{" === v.charAt(V) && "}" === v.charAt(V + 1) && (R += "{}", V += 2)), [R, "TK_WORD"]
                    }
                }
                if ("<" === i && ("?" === v.charAt(V) || "%" === v.charAt(V))) {
                    G.lastIndex = V - 1;
                    var b = G.exec(v);
                    if (b) return i = b[0], V += i.length - 1, [i = i.replace(X.lineBreak, "\n"), "TK_STRING"]
                }
                if ("<" === i && "\x3c!--" === v.substring(V - 1, V + 3)) {
                    for (V += 3, i = "\x3c!--"; !X.newline.test(v.charAt(V)) && V < P;) i += v.charAt(V), V++;
                    return L = !0, [i, "TK_COMMENT"]
                }
                if ("-" === i && L && "--\x3e" === v.substring(V - 1, V + 2)) return L = !1, V += 2, ["--\x3e", "TK_COMMENT"];
                if ("." === i) return [i, "TK_DOT"];
                if (F(i, N)) {
                    for (; V < P && F(i + v.charAt(V), N) && (i += v.charAt(V), !(P <= (V += 1)));) ;
                    return "," === i ? [i, "TK_COMMA"] : "=" === i ? [i, "TK_EQUALS"] : [i, "TK_OPERATOR"]
                }
                return [i, "TK_UNKNOWN"]
            }

            this.tokenize = function () {
                var e, t, n;
                P = v.length, V = 0, L = !1, I = [];
                for (var i = null, r = [], s = []; !t || "TK_EOF" !== t.type;) {
                    for (n = _(), e = new Q(n[1], n[0], D, C); "TK_COMMENT" === e.type || "TK_BLOCK_COMMENT" === e.type || "TK_UNKNOWN" === e.type;) "TK_BLOCK_COMMENT" === e.type && (e.directives = n[2]), s.push(e), n = _(), e = new Q(n[1], n[0], D, C);
                    s.length && (e.comments_before = s, s = []), "TK_START_BLOCK" === e.type || "TK_START_EXPR" === e.type ? (e.parent = t, r.push(i), i = e) : ("TK_END_BLOCK" === e.type || "TK_END_EXPR" === e.type) && i && ("]" === e.text && "[" === i.text || ")" === e.text && "(" === i.text || "}" === e.text && "{" === i.text) && (e.parent = i.parent, i = r.pop()), I.push(e), t = e
                }
                return I
            }
        }

        return {
            run: function (e, t) {
                function _(e) {
                    return e.replace(/\s+$/g, "")
                }

                var n, i, r, T, s, a, E, o, l, g, x, w, h, c;
                for ((t = t || {}).wrap_line_length !== undefined && 0 !== parseInt(t.wrap_line_length, 10) || t.max_char === undefined || 0 === parseInt(t.max_char, 10) || (t.wrap_line_length = t.max_char), i = t.indent_inner_html !== undefined && t.indent_inner_html, r = t.indent_size === undefined ? 4 : parseInt(t.indent_size, 10), T = t.indent_char === undefined ? " " : t.indent_char, a = t.brace_style === undefined ? "collapse" : t.brace_style, s = 0 === parseInt(t.wrap_line_length, 10) ? 32786 : parseInt(t.wrap_line_length || 250, 10), E = t.unformatted || ["a", "span", "img", "bdo", "em", "strong", "dfn", "code", "samp", "kbd", "var", "cite", "abbr", "acronym", "q", "sub", "sup", "tt", "i", "b", "big", "small", "u", "s", "strike", "font", "ins", "del", "address", "pre"], o = t.preserve_newlines === undefined || t.preserve_newlines, l = o ? isNaN(parseInt(t.max_preserve_newlines, 10)) ? 32786 : parseInt(t.max_preserve_newlines, 10) : 0, g = t.indent_handlebars !== undefined && t.indent_handlebars, x = t.wrap_attributes === undefined ? "auto" : t.wrap_attributes, w = t.wrap_attributes_indent_size === undefined ? r : parseInt(t.wrap_attributes_indent_size, 10) || r, h = t.end_with_newline !== undefined && t.end_with_newline, c = Array.isArray(t.extra_liners) ? t.extra_liners.concat() : "string" == typeof t.extra_liners ? t.extra_liners.split(",") : "head,body,/html".split(","), t.indent_with_tabs && (T = "\t", r = 1), (n = new function () {
                    return this.pos = 0, this.token = "", this.current_mode = "CONTENT", this.tags = {
                        parent: "parent1",
                        parentcount: 1,
                        parent1: ""
                    }, this.tag_type = "", this.token_text = this.last_token = this.last_text = this.token_type = "", this.newlines = 0, this.indent_content = i, this.Utils = {
                        whitespace: "\n\r\t ".split(""),
                        single_token: "br,input,link,meta,source,!doctype,basefont,base,area,hr,wbr,param,img,isindex,embed".split(","),
                        extra_liners: c,
                        in_array: function (e, t) {
                            for (var n = 0; n < t.length; n++) if (e == t[n]) return !0;
                            return !1
                        }
                    }, this.is_whitespace = function (e) {
                        for (; 0 < e.length; e++) if (!this.Utils.in_array(e.charAt(0), this.Utils.whitespace)) return !1;
                        return !0
                    }, this.traverse_whitespace = function () {
                        var e = "";
                        if (e = this.input.charAt(this.pos), this.Utils.in_array(e, this.Utils.whitespace)) {
                            for (this.newlines = 0; this.Utils.in_array(e, this.Utils.whitespace);) o && "\n" == e && this.newlines <= l && (this.newlines += 1), this.pos++, e = this.input.charAt(this.pos);
                            return !0
                        }
                        return !1
                    }, this.space_or_wrap = function (e) {
                        this.line_char_count >= this.wrap_line_length ? (this.print_newline(!1, e), this.print_indentation(e)) : (this.line_char_count++, e.push(" "))
                    }, this.get_content = function () {
                        for (var e = "", t = []; "<" != this.input.charAt(this.pos);) {
                            if (this.pos >= this.input.length) return t.length ? t.join("") : ["", "TK_EOF"];
                            if (this.traverse_whitespace()) this.space_or_wrap(t); else {
                                if (g) {
                                    var n = this.input.substr(this.pos, 3);
                                    if ("{{#" == n || "{{/" == n) break;
                                    if ("{{!" == n) return [this.get_tag(), "TK_TAG_HANDLEBARS_COMMENT"];
                                    if ("{{" == this.input.substr(this.pos, 2) && "{{else}}" == this.get_tag(!0)) break
                                }
                                e = this.input.charAt(this.pos), this.pos++, this.line_char_count++, t.push(e)
                            }
                        }
                        return t.length ? t.join("") : ""
                    }, this.get_contents_to = function (e) {
                        if (this.pos == this.input.length) return ["", "TK_EOF"];
                        var t = "", n = new RegExp("</" + e + "\\s*>", "igm");
                        n.lastIndex = this.pos;
                        var i = n.exec(this.input), r = i ? i.index : this.input.length;
                        return this.pos < r && (t = this.input.substring(this.pos, r), this.pos = r), t
                    }, this.record_tag = function (e) {
                        this.tags[e + "count"] ? this.tags[e + "count"]++ : this.tags[e + "count"] = 1, this.tags[e + this.tags[e + "count"]] = this.indent_level, this.tags[e + this.tags[e + "count"] + "parent"] = this.tags.parent, this.tags.parent = e + this.tags[e + "count"]
                    }, this.retrieve_tag = function (e) {
                        if (this.tags[e + "count"]) {
                            for (var t = this.tags.parent; t && e + this.tags[e + "count"] != t;) t = this.tags[t + "parent"];
                            t && (this.indent_level = this.tags[e + this.tags[e + "count"]], this.tags.parent = this.tags[t + "parent"]), delete this.tags[e + this.tags[e + "count"] + "parent"], delete this.tags[e + this.tags[e + "count"]], 1 == this.tags[e + "count"] ? delete this.tags[e + "count"] : this.tags[e + "count"]--
                        }
                    }, this.indent_to_tag = function (e) {
                        if (this.tags[e + "count"]) {
                            for (var t = this.tags.parent; t && e + this.tags[e + "count"] != t;) t = this.tags[t + "parent"];
                            t && (this.indent_level = this.tags[e + this.tags[e + "count"]])
                        }
                    }, this.get_tag = function (e) {
                        var t, n, i = "", r = [], s = "", _ = !1, a = !0, o = this.pos, l = this.line_char_count;
                        e = e !== undefined && e;
                        do {
                            if (this.pos >= this.input.length) return e && (this.pos = o, this.line_char_count = l), r.length ? r.join("") : ["", "TK_EOF"];
                            if (i = this.input.charAt(this.pos), this.pos++, this.Utils.in_array(i, this.Utils.whitespace)) _ = !0; else {
                                if ("'" != i && '"' != i || (i += this.get_unformatted(i), _ = !0), "=" == i && (_ = !1), r.length && "=" != r[r.length - 1] && ">" != i && _) {
                                    if (this.space_or_wrap(r), _ = !1, !a && "force" == x && "/" != i) {
                                        this.print_newline(!0, r), this.print_indentation(r);
                                        for (var h = 0; h < w; h++) r.push(T)
                                    }
                                    for (var c = 0; c < r.length; c++) if (" " == r[c]) {
                                        a = !1;
                                        break
                                    }
                                }
                                if (g && "<" == n && i + this.input.charAt(this.pos) == "{{" && (i += this.get_unformatted("}}"), r.length && " " != r[r.length - 1] && "<" != r[r.length - 1] && (i = " " + i), _ = !0), "<" != i || n || (t = this.pos - 1, n = "<"), g && !n && 2 <= r.length && "{" == r[r.length - 1] && "{" == r[r.length - 2] && (t = "#" == i || "/" == i || "!" == i ? this.pos - 3 : this.pos - 2, n = "{"), this.line_char_count++, r.push(i), r[1] && ("!" == r[1] || "?" == r[1] || "%" == r[1])) {
                                    r = [this.get_comment(t)];
                                    break
                                }
                                if (g && r[1] && "{" == r[1] && r[2] && "!" == r[2]) {
                                    r = [this.get_comment(t)];
                                    break
                                }
                                if (g && "{" == n && 2 < r.length && "}" == r[r.length - 2] && "}" == r[r.length - 1]) break
                            }
                        } while (">" != i);
                        var u, p, d = r.join("");
                        u = -1 != d.indexOf(" ") ? d.indexOf(" ") : "{" == d[0] ? d.indexOf("}") : d.indexOf(">"), p = "<" != d[0] && g ? "#" == d[2] ? 3 : 2 : 1;
                        var f = d.substring(p, u).toLowerCase();
                        return "/" == d.charAt(d.length - 2) || this.Utils.in_array(f, this.Utils.single_token) ? e || (this.tag_type = "SINGLE") : g && "{" == d[0] && "else" == f ? e || (this.indent_to_tag("if"), this.tag_type = "HANDLEBARS_ELSE", this.indent_content = !0, this.traverse_whitespace()) : this.is_unformatted(f, E) ? (s = this.get_unformatted("</" + f + ">", d), r.push(s), this.pos, this.tag_type = "SINGLE") : "script" == f && (-1 == d.search("type") || -1 < d.search("type") && -1 < d.search(/\b(text|application)\/(x-)?(javascript|ecmascript|jscript|livescript)/)) ? e || (this.record_tag(f), this.tag_type = "SCRIPT") : "style" == f && (-1 == d.search("type") || -1 < d.search("type") && -1 < d.search("text/css")) ? e || (this.record_tag(f), this.tag_type = "STYLE") : "!" == f.charAt(0) ? e || (this.tag_type = "SINGLE", this.traverse_whitespace()) : e || ("/" == f.charAt(0) ? (this.retrieve_tag(f.substring(1)), this.tag_type = "END") : (this.record_tag(f), "html" != f.toLowerCase() && (this.indent_content = !0), this.tag_type = "START"), this.traverse_whitespace() && this.space_or_wrap(r), this.Utils.in_array(f, this.Utils.extra_liners) && (this.print_newline(!1, this.output), this.output.length && "\n" != this.output[this.output.length - 2] && this.print_newline(!0, this.output))), e && (this.pos = o, this.line_char_count = l), r.join("")
                    }, this.get_comment = function (e) {
                        var t = "", n = ">", i = !1;
                        this.pos = e;
                        var r = this.input.charAt(this.pos);
                        for (this.pos++; this.pos <= this.input.length && ((t += r)[t.length - 1] != n[n.length - 1] || -1 == t.indexOf(n));) !i && t.length < 10 && (0 === t.indexOf("<![if") ? (n = "<![endif]>", i = !0) : 0 === t.indexOf("<![cdata[") ? (n = "]]>", i = !0) : 0 === t.indexOf("<![") ? (n = "]>", i = !0) : 0 === t.indexOf("\x3c!--") ? (n = "--\x3e", i = !0) : 0 === t.indexOf("{{!") ? (n = "}}", i = !0) : 0 === t.indexOf("<?") ? (n = "?>", i = !0) : 0 === t.indexOf("<%") && (n = "%>", i = !0)), r = this.input.charAt(this.pos), this.pos++;
                        return t
                    }, this.get_unformatted = function (e, t) {
                        if (t && -1 != t.toLowerCase().indexOf(e)) return "";
                        var n = "", i = "", r = 0, s = !0;
                        do {
                            if (this.pos >= this.input.length) return i;
                            if (n = this.input.charAt(this.pos), this.pos++, this.Utils.in_array(n, this.Utils.whitespace)) {
                                if (!s) {
                                    this.line_char_count--;
                                    continue
                                }
                                if ("\n" == n || "\r" == n) {
                                    i += "\n", this.line_char_count = 0;
                                    continue
                                }
                            }
                            i += n, this.line_char_count++, s = !0, g && "{" == n && i.length && "{" == i[i.length - 2] && (r = (i += this.get_unformatted("}}")).length)
                        } while (-1 == i.toLowerCase().indexOf(e, r));
                        return i
                    }, this.get_token = function () {
                        var e;
                        if ("TK_TAG_SCRIPT" == this.last_token || "TK_TAG_STYLE" == this.last_token) {
                            var t = this.last_token.substr(7);
                            return "string" != typeof (e = this.get_contents_to(t)) ? e : [e, "TK_" + t]
                        }
                        return "CONTENT" == this.current_mode ? "string" != typeof (e = this.get_content()) ? e : [e, "TK_CONTENT"] : "TAG" == this.current_mode ? "string" != typeof (e = this.get_tag()) ? e : [e, "TK_TAG_" + this.tag_type] : void 0
                    }, this.get_full_indent = function (e) {
                        return (e = this.indent_level + e || 0) < 1 ? "" : new Array(e + 1).join(this.indent_string)
                    }, this.is_unformatted = function (e, t) {
                        if (!this.Utils.in_array(e, t)) return !1;
                        if ("a" != e.toLowerCase() || !this.Utils.in_array("a", t)) return !0;
                        var n = (this.get_tag(!0) || "").match(/^\s*<\s*\/?([a-z]*)\s*[^>]*>\s*$/);
                        return !(n && !this.Utils.in_array(n, t))
                    }, this.printer = function (e, t, n, i, r) {
                        this.input = e || "", this.output = [], this.indent_character = t, this.indent_string = "", this.indent_size = n, this.brace_style = r, this.indent_level = 0, this.wrap_line_length = i;
                        for (var s = this.line_char_count = 0; s < this.indent_size; s++) this.indent_string += this.indent_character;
                        this.print_newline = function (e, t) {
                            this.line_char_count = 0, t && t.length && (e || "\n" != t[t.length - 1]) && ("\n" != t[t.length - 1] && (t[t.length - 1] = _(t[t.length - 1])), t.push("\n"))
                        }, this.print_indentation = function (e) {
                            for (var t = 0; t < this.indent_level; t++) e.push(this.indent_string), this.line_char_count += this.indent_string.length
                        }, this.print_token = function (e) {
                            this.is_whitespace(e) && !this.output.length || ((e || "" !== e) && this.output.length && "\n" == this.output[this.output.length - 1] && (this.print_indentation(this.output), e = e.replace(/^\s+/g, "")), this.print_token_raw(e))
                        }, this.print_token_raw = function (e) {
                            0 < this.newlines && (e = _(e)), e && "" !== e && (1 < e.length && "\n" == e[e.length - 1] ? (this.output.push(e.slice(0, -1)), this.print_newline(!1, this.output)) : this.output.push(e));
                            for (var t = 0; t < this.newlines; t++) this.print_newline(0 < t, this.output);
                            this.newlines = 0
                        }, this.indent = function () {
                            this.indent_level++
                        }, this.unindent = function () {
                            0 < this.indent_level && this.indent_level--
                        }
                    }, this
                }).printer(e, T, r, s, a); ;) {
                    var u = n.get_token();
                    if (n.token_text = u[0], n.token_type = u[1], "TK_EOF" == n.token_type) break;
                    switch (n.token_type) {
                        case"TK_TAG_START":
                            n.print_newline(!1, n.output), n.print_token(n.token_text), n.indent_content && (n.indent(), n.indent_content = !1), n.current_mode = "CONTENT";
                            break;
                        case"TK_TAG_STYLE":
                        case"TK_TAG_SCRIPT":
                            n.print_newline(!1, n.output), n.print_token(n.token_text), n.current_mode = "CONTENT";
                            break;
                        case"TK_TAG_END":
                            if ("TK_CONTENT" == n.last_token && "" === n.last_text) {
                                var p = n.token_text.match(/\w+/)[0], d = null;
                                n.output.length && (d = n.output[n.output.length - 1].match(/(?:<|{{#)\/?\s*(\w+)/)), (null == d || d[1] != p && !n.Utils.in_array(d[1], E)) && n.print_newline(!1, n.output)
                            }
                            n.print_token(n.token_text), n.current_mode = "CONTENT";
                            break;
                        case"TK_TAG_SINGLE":
                            var f = n.token_text.match(/^\s*<([a-z-]+)/i);
                            f && n.Utils.in_array(f[1], E) || n.print_newline(!1, n.output), n.print_token(n.token_text), n.current_mode = "CONTENT";
                            break;
                        case"TK_TAG_HANDLEBARS_ELSE":
                            n.print_token(n.token_text), n.indent_content && (n.indent(), n.indent_content = !1), n.current_mode = "CONTENT";
                            break;
                        case"TK_TAG_HANDLEBARS_COMMENT":
                        case"TK_CONTENT":
                            n.print_token(n.token_text), n.current_mode = "TAG";
                            break;
                        case"TK_STYLE":
                        case"TK_SCRIPT":
                            if ("" !== n.token_text) {
                                n.print_newline(!1, n.output);
                                var K, m = n.token_text, R = 1;
                                "TK_SCRIPT" == n.token_type ? K = y : "TK_STYLE" == n.token_type && (K = k), "keep" == t.indent_scripts ? R = 0 : "separate" == t.indent_scripts && (R = -n.indent_level);
                                var b = n.get_full_indent(R);
                                if (K) m = K(m.replace(/^\s*/, b), t); else {
                                    var v = m.match(/^\s*/)[0].match(/[^\n\r]*$/)[0].split(n.indent_string).length - 1,
                                        S = n.get_full_indent(R - v);
                                    m = m.replace(/^\s*/, b).replace(/\r\n|\r|\n/g, "\n" + S).replace(/\s+$/, "")
                                }
                                m && (n.print_token_raw(m), n.print_newline(!0, n.output))
                            }
                            n.current_mode = "TAG";
                            break;
                        default:
                            "" !== n.token_text && n.print_token(n.token_text)
                    }
                    n.last_token = n.token_type, n.last_text = n.token_text
                }
                var A = n.output.join("").replace(/[\r\n\t ]+$/, "");
                return h && (A += "\n"), A
            }
        }
    }
});