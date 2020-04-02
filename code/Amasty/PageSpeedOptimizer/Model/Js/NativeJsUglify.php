<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Js;

class NativeJsUglify
{
    const SCRIPT = '!function(){var e=window.addEventListener||function(e,t){window.attachEvent("on"+e,t)},' .
    't=window.removeEventListener||function(e,t,r){window.detachEvent("on"+e,t)},r={cache:[],mobileScreenSize:500,' .
    'observersAdded:!1,addObservers:function(){this.observersAdded||(e("scroll",r.throttledLoad),' .
    'e("resize",r.throttledLoad),this.observersAdded=!0)},removeObservers:function(){t("scroll",r.throttledLoad,!1),' .
    't("resize",r.throttledLoad,!1),this.observersAdded=!1},throttleTimer:(new Date).getTime(),throttledLoad:' .
    'function(){var e=(new Date).getTime();e-r.throttleTimer>=200&&(r.throttleTimer=e,r.loadVisibleImages())},' .
    'loadVisibleImages:function(){for(var e=window.pageYOffset||document.documentElement.scrollTop,t=e-200,' .
    'n=e+(window.innerHeight||document.documentElement.clientHeight)+200,i=0;i<r.cache.length;){var a=r.cache[i],' .
    's=o(a);if(s>=t-(a.height||0)&&s<=n){var d=a.getAttribute("data-src-mobile");a.onload=function(){' .
    'this.className=this.className.replace(/(^|\s+)lazy-load(\s+|$)/,"$1lazy-loaded$2")},d&&screen.width' .
    '<=r.mobileScreenSize?a.src=d:a.src=a.getAttribute("data-amsrc"),a.removeAttribute("data-amsrc"),' .
    'a.removeAttribute("data-src-mobile"),r.cache.splice(i,1)}else i++}0===r.cache.length&&r.removeObservers()},' .
    'init:function(){document.querySelectorAll||(document.querySelectorAll=function(e){var t=document,' .
    'r=t.documentElement.firstChild,o=t.createElement("STYLE");return r.appendChild(o),t.__qsaels=[],' .
    'o.styleSheet.cssText=e+"{x:expression(document.__qsaels.push(this))}",window.scrollBy(0,0),t.__qsaels});' .
    'for(var e=document.querySelectorAll("img[data-amsrc]"),t=0;t<e.length;t++){var o=e[t];-1==r.cache.indexOf' .
    '(o)&&r.cache.push(o)}r.addObservers(),r.loadVisibleImages()}};function o(e){var t=0;if(e.offsetParent){' .
    'do{t+=e.offsetTop}while(e=e.offsetParent);return t}}r.init(),window.amlazycallback=r.init}();';
}
