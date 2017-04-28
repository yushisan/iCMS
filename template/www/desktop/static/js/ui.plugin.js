/*!
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
(function($) {
    $.fn.slider = function(options) {
        var defaults = {
            left_btn: '#slider-left',
            right_btn: '#slider-right',
            num_btn: "#slider-btn",
            classname: "active",
            item: "li",
            time: 3000,
            sync: null
        }
        var options = $.extend(defaults, options);
        return this.each(function() {
            var a = $(this)
              ,
            b = $(options.num_btn)
              ,
            l = $(options.left_btn)
              ,
            r = $(options.right_btn)
              ,
            current = 0
              ,
            timeOut = null
              ,
            auto = true
              ,
            len = $(options.item, a).length;

            if (options.sync) {
                var s = $(options.sync);
            }
            if (l) {
                $(l).click(function(event) {
                    event.preventDefault();
                    clearTimeout(timeOut);
                    current--;
                    show(current);
                }).mouseout(function() {
                    Timeout();
                });
            }
            if (r) {
                $(r).click(function(event) {
                    event.preventDefault();
                    clearTimeout(timeOut);
                    current++;
                    start(current);
                }).mouseout(function() {
                    Timeout();
                });
            }
            start();
            overout($(options.item, a));

            if (options.sync) {
                overout($(options.item, s));
            }
            if (b) {
                $(options.item, b).each(function(i) {
                    overout($(this), i);
                });
            }
            function Timeout() {
                timeOut = setTimeout(function() {
                    current++;
                    // console.log(current);
                    start(current);
                }, options.time);
            }
            function overout(that, i) {
                that.mouseover(function() {
                    clearTimeout(timeOut);
                    if (typeof i !== "undefined") {
                        show(i);
                    }
                }).mouseout(function() {
                    Timeout();
                })
            }
            function start() {
                show(current);
                Timeout();
            }
            function show(i) {
                if (i >= len) {
                    i = 0;
                }
                if (i < 0) {
                    i = len - 1;
                }
                current = i;
                if (options.sync) {
                    $(options.item, s).hide().eq(i).fadeIn();
                }
                $(options.item, a).hide().eq(i).fadeIn();

                if (b) {
                    $(options.item, b)
                    .removeClass(options.classname)
                    .eq(i)
                    .addClass(options.classname);
                }
            }
        });
    }
})(jQuery);

(function($) {
    $.fn.tabs = function(options) {
        var defaults = {
            item: ".tabs-pane",
            action: 'mouseover'
        }
        var options = $.extend(defaults, options);
        var container = $(this);
        $('[data-toggle="tab"]', container).each(function(i) {
            if (options.action == 'click') {
                $(this).click(function(event) {
                    event.preventDefault();
                    show(this)
                });
            }
            if (options.action == 'mouseover') {
                $(this).mouseover(function(event) {
                    event.preventDefault();
                    show(this)
                });
            }
        });

        function show(that) {
            var a = $(that)
              , target = a.attr('date-target');
            $(options.item, container).hide();
            $(target, container).show();
            $('[data-toggle="tab"]', container).parent().removeClass('active');
            a.parent().addClass('active');
        }
    }
})(jQuery);
/*!
 * Lazy Load - jQuery plugin for lazy loading images
 *
 */
(function($) {
    $.fn.lazyload = function(b) {
        var c = {
            attr: "data-src",
            container: $(window),
            callback: $.noop
        };
        var d = $.extend({}, c, b || {});
        d.cache = [];
        $(this).each(function() {
            var h = this.nodeName.toLowerCase()
              , g = $(this).attr(d.attr);
            var i = {
                obj: $(this),
                tag: h,
                url: g
            };
            d.cache.push(i)
        });
        var f = function(g) {
            if ($.isFunction(d.callback)) {
                d.callback.call(g.get(0))
            }
        }
        ;
        var e = function() {
            var g = d.container.height();
            if ($(window).get(0) === window) {
                contop = $(window).scrollTop()
            } else {
                contop = d.container.offset().top
            }
            $.each(d.cache, function(m, n) {
                var p = n.obj, j = n.tag, k = n.url, l, h;
                if (p) {
                    l = p.offset().top - contop,
                    l + p.height();
                    if ((l >= 0 && l < g) || (h > 0 && h <= g)) {
                        if (k) {
                            if (j === "img") {
                                f(p.attr("src", k))
                            } else {
                                p.load(k, {}, function() {
                                    f(p)
                                })
                            }
                        } else {
                            f(p)
                        }
                        n.obj = null
                    }
                }
            })
        }
        ;
        e();
        d.container.bind("scroll", e)
    }
})(jQuery);
/*!
 * Bootstrap v3.3.7 (http://getbootstrap.com)
 * Copyright 2011-2017 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */

/*!
 * Generated using the Bootstrap Customizer (http://getbootstrap.com/customize/?id=31a3ec205b40c7d146ef3d3c7e0706de)
 * Config saved to config.json and https://gist.github.com/31a3ec205b40c7d146ef3d3c7e0706de
 */
if("undefined"==typeof jQuery)throw new Error("Bootstrap's JavaScript requires jQuery");+function(t){"use strict";var e=t.fn.jquery.split(" ")[0].split(".");if(e[0]<2&&e[1]<9||1==e[0]&&9==e[1]&&e[2]<1||e[0]>3)throw new Error("Bootstrap's JavaScript requires jQuery version 1.9.1 or higher, but lower than version 4")}(jQuery),+function(t){"use strict";function e(e){var n=e.attr("data-target");n||(n=e.attr("href"),n=n&&/#[A-Za-z]/.test(n)&&n.replace(/.*(?=#[^\s]*$)/,""));var a=n&&t(n);return a&&a.length?a:e.parent()}function n(n){n&&3===n.which||(t(i).remove(),t(r).each(function(){var a=t(this),i=e(a),r={relatedTarget:this};i.hasClass("open")&&(n&&"click"==n.type&&/input|textarea/i.test(n.target.tagName)&&t.contains(i[0],n.target)||(i.trigger(n=t.Event("hide.bs.dropdown",r)),n.isDefaultPrevented()||(a.attr("aria-expanded","false"),i.removeClass("open").trigger(t.Event("hidden.bs.dropdown",r)))))}))}function a(e){return this.each(function(){var n=t(this),a=n.data("bs.dropdown");a||n.data("bs.dropdown",a=new s(this)),"string"==typeof e&&a[e].call(n)})}var i=".dropdown-backdrop",r='[data-toggle="dropdown"]',s=function(e){t(e).on("click.bs.dropdown",this.toggle)};s.VERSION="3.3.7",s.prototype.toggle=function(a){var i=t(this);if(!i.is(".disabled, :disabled")){var r=e(i),s=r.hasClass("open");if(n(),!s){"ontouchstart"in document.documentElement&&!r.closest(".navbar-nav").length&&t(document.createElement("div")).addClass("dropdown-backdrop").insertAfter(t(this)).on("click",n);var o={relatedTarget:this};if(r.trigger(a=t.Event("show.bs.dropdown",o)),a.isDefaultPrevented())return;i.trigger("focus").attr("aria-expanded","true"),r.toggleClass("open").trigger(t.Event("shown.bs.dropdown",o))}return!1}},s.prototype.keydown=function(n){if(/(38|40|27|32)/.test(n.which)&&!/input|textarea/i.test(n.target.tagName)){var a=t(this);if(n.preventDefault(),n.stopPropagation(),!a.is(".disabled, :disabled")){var i=e(a),s=i.hasClass("open");if(!s&&27!=n.which||s&&27==n.which)return 27==n.which&&i.find(r).trigger("focus"),a.trigger("click");var o=" li:not(.disabled):visible a",l=i.find(".dropdown-menu"+o);if(l.length){var d=l.index(n.target);38==n.which&&d>0&&d--,40==n.which&&d<l.length-1&&d++,~d||(d=0),l.eq(d).trigger("focus")}}}};var o=t.fn.dropdown;t.fn.dropdown=a,t.fn.dropdown.Constructor=s,t.fn.dropdown.noConflict=function(){return t.fn.dropdown=o,this},t(document).on("click.bs.dropdown.data-api",n).on("click.bs.dropdown.data-api",".dropdown form",function(t){t.stopPropagation()}).on("click.bs.dropdown.data-api",r,s.prototype.toggle).on("keydown.bs.dropdown.data-api",r,s.prototype.keydown).on("keydown.bs.dropdown.data-api",".dropdown-menu",s.prototype.keydown)}(jQuery);
