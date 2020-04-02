require(['jquery'], function($){
$(window).load(function(){ 

$.fn.extend({
 equalHeights: function(){
  var top=0;
  var row=[];
  var classname=('equalHeights'+Math.random()).replace('.','');
  $(this).each(function(){
   var thistop=$(this).offset().top;
   if (thistop>top) {
    $('.'+classname).removeClass(classname); 
    top=thistop;
   }
   $(this).addClass(classname);
   $(this).height('auto');
   var h=(Math.max.apply(null, $('.'+classname).map(function(){ return $(this).outerHeight(); }).get()));
   $('.'+classname).height(h);
  }).removeClass(classname); 
 }    
});

$(function(){
  $(window).resize(function(){
    
 $('.product-item-name').equalHeights();
 $('.product-item-brandname').equalHeights();
 $('.product-brand').equalHeights();
  }).trigger('resize');
});
});
});
document.documentElement.addEventListener('touchstart', function(event) {
    if (event.touches.length > 1) {
        event.preventDefault();
    }
}, false);