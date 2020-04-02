define([
	"jquery",
	"jquery/ui",
	
	],

function (jQuery) {
	  "use strict";
		jQuery.widget('Navigationmenupro.cwsmenu', { 
		_init: function () {
				var cnt_nav = jQuery(this.element).parent().find("nav").length;
				
				if(jQuery(this.element).parent().hasClass("nav-sections-item-content") && cnt_nav < 2 ){
					this._assignControls()._listen();
					//this._cwstogglemenu();
				}
			this._cwstogglemenu();
			this._cwsactivemenu();
			this._assignControls()._listen();
			
        	},
			_create: function() {
				var responsive_breakpoint = this.options.responsive_breakpoint;
				var resizeScreen = responsive_breakpoint.replace("px", "");
				jQuery('ul.smart-expand').find("li.active").children().children("span.arw").removeClass("plush").addClass('minus');
				
				//set level number for group options
				jQuery(".cwsMenu .Level0 > .hideTitle").each(function() {
					jQuery(this).find(".Level1 > li.Level2").removeClass("Level2").addClass("Level1");
					jQuery(this).find(".Level2 > li.Level3").removeClass("Level3").addClass("Level2");
					jQuery(this).find(".Level3 > li.Level4").removeClass("Level4").addClass("Level3");
					jQuery(this).find(".Level4 > li.Level5").removeClass("Level5").addClass("Level4");
					//alert(this);
				});
			
				//Add padding in lavale 4/5 in expand menu
				var groupId = this.options.group_id;
				var spadding = parseInt(jQuery(groupId+" ul.smart-expand a.Level3").css("padding-left"));
				jQuery(groupId+" ul.smart-expand a.Level4").css("padding-left",spadding+10);
				jQuery(groupId+" ul.smart-expand a.Level5").css("padding-left",spadding+20);
				
				var apadding = parseInt(jQuery(groupId+" ul.always-expand a.Level3").css("padding-left"));
				jQuery(groupId+" ul.always-expand a.Level4").css("padding-left",apadding+10);
				jQuery(groupId+" ul.always-expand a.Level5").css("padding-left",apadding+20);
				
				jQuery(document).ready(jQuery.proxy(this._doneResizing,this));
				jQuery(window).resize(jQuery.proxy(this._doneResizing,this));
				
			},
			
			_assignControls: function () {
				this.controls = {
					toggleBtn: jQuery('[data-action="toggle-nav"]'),
					swipeArea: jQuery('.nav-sections')
				};

           	 return this;
  	   	  },
	
		_listen: function () {
				
			var controls = this.controls;
			var toggle = this.toggle;

			this._on(controls.toggleBtn, {'click': toggle});
			this._on(controls.swipeArea, {'swipeleft': toggle});
		},

		toggle: function (event) {
			//alert("TEST toggle");
			    event.stopImmediatePropagation();
			if (jQuery('html').hasClass('nav-open')) {
				jQuery('html').removeClass('nav-open');

				setTimeout(function () {
					jQuery('html').removeClass('nav-before-open');
				}, 300);
			} else {
				jQuery('html').addClass('nav-before-open');

				setTimeout(function () {
					jQuery('html').addClass('nav-open');
				}, 42);
			}
		},
		_cwstogglemenu: function (e) {
			var group_id = this.options.group_id;
			var currentClickedParent = null;
			/* Fix Smart Export Plus & Minus Issue In the Desk Top for set plus and minus icon properly.*/
			 jQuery(group_id+" > .cwsMenu.smart-expand li span.arw").click(function(e){
				e.stopImmediatePropagation();
			
				if(jQuery(this).hasClass("plush")) {
					jQuery(this).parent().parent().addClass("menu-active");
					if(jQuery(this).parent().next("ul").find("li").hasClass("hideTitle")) {
						jQuery(this).parent().next("ul").find("li").next("ul").slideDown();
					}
					jQuery(this).parent().next("ul").slideDown();
					jQuery(this).parent().next(".cmsbk").slideDown();
					jQuery(this).removeClass("plush");
					jQuery(this).addClass("minus");
				} else {
						
						jQuery(this).parent().parent().children().find('li.menu-active > ul').slideUp();
						jQuery(this).parent().parent().children().find('li.menu-active > .cmsbk').slideUp();
						jQuery(this).parent().parent().children().find('li span.arw').addClass("plush");
						jQuery(this).parent().parent().children().find('li span.arw').removeClass("minus");
						
						jQuery(this).parent().parent().removeClass("menu-active");
						jQuery(this).parent().parent().children().find('li').removeClass("menu-active");
					
					
					jQuery(this).parent().next("ul").slideUp();
					jQuery(this).parent().next(".cmsbk").slideUp();
					jQuery(this).addClass("plush");
					jQuery(this).removeClass("minus");
				}
				return false;
			});
		},
		_cwsactivemenu: function () {
			var pathname = window.location.pathname; // Returns path only
			var url      = window.location.href; 
			
			if(this.options.menu_type=='list-item'){
				jQuery(".cwsMenu li").removeClass("active");
				jQuery("a[href~='"+url+"']").parents().addClass("active");
				jQuery(this).find("li.active").parents('li').addClass('active');
				jQuery(this).find("li.active").parents('li').addClass('active');
				jQuery(".active").parents(this.options.group_id+" ul > li").addClass('active');
			}else{
				jQuery(".cwsMenu li").removeClass("active");
				jQuery("a[href~='"+url+"']").parents().addClass("active");	
			}
		},
		_doneResizing: function () {
			
			
			var responsive_breakpoint = this.options.responsive_breakpoint;
				var resizeScreen = responsive_breakpoint.replace("px", "");
				var resizeId;
				var resizeTimer;
				var groupId = this.options.group_id;
				var screenView = null;
				
				var rootLi = jQuery(groupId + ' > .mega-menu.horizontal > li');
				var toplinkHeight = jQuery(rootLi).height();
				jQuery(rootLi).children().find("ul.Level0").css("top",toplinkHeight);

			if (jQuery(window).width() > resizeScreen ) {
				if(screenView!="desktop")
				{
					jQuery('ul.cwsMenu.mega-menu').find("li > ul").css('display','');
					jQuery('ul.cwsMenu.mega-menu').find("li.active").parents('li').addClass('showSub');
					jQuery('ul.cwsMenu.mega-menu').find("li > ul.subMenu").parents('li').addClass('showSub');
					if((jQuery(groupId + ' > ul.cwsMenu.mega-menu').find("ul.subMenu:visible").length))
					{
						var openSubMenu = jQuery(groupId+' > ul.cwsMenu.mega-menu').find("li > ul.subMenu:visible").prev("a.hover");
						jQuery(openSubMenu).next("ul.subMenu").stop().slideUp(400, function(){});
						jQuery(groupId+' > ul.cwsMenu.mega-menu').find("a.hover > ul.subMenu:visible").slideUp();
						
						jQuery(groupId+' > ul.cwsMenu.mega-menu').find("a.hover").removeClass("hover");
					}
					jQuery('ul.cwsMenu.mega-menu').find("li > div > ul.subMenu").parents('li').addClass('showSub');
					if((jQuery(groupId + ' > ul.cwsMenu.mega-menu').find("div > ul.subMenu:visible").length))
					{
						var openSubMenu = jQuery(groupId+' > ul.cwsMenu.mega-menu').find("li > div > ul.subMenu:visible").prev("a.hover");
						jQuery(openSubMenu).next("ul.subMenu").stop().slideUp(400, function(){});
						jQuery(groupId+' > ul.cwsMenu.mega-menu').find("a.hover > div > ul.subMenu:visible").slideUp();
						
						jQuery(groupId+' > ul.cwsMenu.mega-menu').find("a.hover").removeClass("hover");
					}
					jQuery('a').removeClass("hover");
					
					jQuery(groupId+' > ul.cwsMenu.mega-menu').find('span.arw').addClass("plush");
					jQuery(groupId+' > ul.cwsMenu.mega-menu').find('span.arw').removeClass("minus");
					
					var rootLi = jQuery(groupId + ' > .mega-menu.horizontal > li');
					var toplinkHeight = jQuery(rootLi).height();
					jQuery(rootLi).children().find("ul.Level0").css("top",toplinkHeight);
					
				
				}else{
					
				}
				
				screenView = 'desktop';
				if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
					
					
					
					jQuery(groupId+" > ul.mega-menu li span.arw").unbind("click");
					jQuery(groupId+" > ul.mega-menu li.parent").unbind("mouseenter");
					jQuery(groupId+" > ul.mega-menu li.parent").unbind("mouseleave");
					var allowtouchclick = null;
					var allowtoggle = null;
				
					
					
					jQuery(groupId+' > ul.mega-menu li.parent a').on("touchstart", function (e) {
						"use strict"; //satisfy the code inspectors
				
						e.stopImmediatePropagation();
						
						if((jQuery(this).parent('li').hasClass('Level0'))) 
						   {
							  var link = jQuery(this); //preselect the link
							
						   if (link.hasClass('hover')) {
						
							   var link_class = jQuery(this).attr('class');
								var link_class = link_class.replace('parent', ''); 
								var link_class = link_class.replace('hover', ''); 
							   var link_class = link_class.replace('haslbl', ''); 
						
							   if(jQuery(this).next("ul"+"."+link_class).is(':visible'))
								{
								jQuery(this).next("ul"+"."+link_class).stop().slideUp(400, function(){
});
									jQuery(this).find('span.arw').addClass("plush");
									jQuery(this).find('span.arw').removeClass("minus");
link.removeClass("hover");
								}
						
							   
							   return true;
							   
						} else {
							var otherOpen = jQuery(groupId+" > ul.cwsMenu > li").find('a.hover');
							jQuery(otherOpen).next("ul.subMenu").slideUp();
							jQuery(otherOpen).find('span.arw').addClass("plush");
									jQuery(otherOpen).find('span.arw').removeClass("minus");
							otherOpen.removeClass("hover");
							
							if(jQuery(this).attr('class')){
								var link_class = jQuery(this).attr('class');
								var link_class = link_class.replace('parent', ''); 
								var link_class = link_class.replace('hover', ''); 
								var link_class = link_class.replace('haslbl', ''); 
								jQuery(this).next("ul"+"."+link_class).stop().slideDown(400, function(){

								});
								jQuery(this).find('span.arw').removeClass("plush");
									jQuery(this).find('span.arw').addClass("minus");
								
							}
							
							jQuery(groupId+" > ul.cwsMenu").find('a.hover').removeClass("hover");
							link.addClass("hover");
							
							e.preventDefault();
							
							return false; //extra, and to make sure the function has consistent return points
						}
						   
						}else if(jQuery(this).parents('li').last().hasClass('column-1')){
							  var link = jQuery(this); //preselect the link
							
							 if (link.hasClass('hover')) {
								 
								 if(jQuery(this).next("ul.subMenu").length)
								 {
									  if(jQuery(this).attr('class')){
										var link_class = jQuery(this).attr('class');
										var link_class = link_class.replace('parent', ''); 
										var link_class = link_class.replace('hover', ''); 
										  var link_class = link_class.replace('haslbl', ''); 
										
										if (jQuery(this).next("ul"+"."+link_class).is(':visible'))
										{
											jQuery(this).next("ul"+"."+link_class).stop().slideUp(400, function(){
});
											jQuery(this).find('span.arw').addClass("plush");
									jQuery(this).find('span.arw').removeClass("minus");
								return true;
										}else if (!jQuery(this).next("ul"+"."+link_class).is(':visible')){
										jQuery(this).next("ul"+"."+link_class).stop().slideDown(400, function(){

										});
											jQuery(this).find('span.arw').removeClass("plush");
											jQuery(this).find('span.arw').addClass("minus");
											jQuery(this).next("ul"+"."+link_class).prev("a").addClass("hover");
											e.preventDefault();
											return false; 
										}
										  
									}
									
								 }else{
									 
									 return true; 
								 } 
							   	
							} else {
								// Child Menu Still Available
								 if(jQuery(this).next("ul.subMenu").length)
								 {
								
									 if(jQuery(this).parent().parent().find("ul.subMenu:visible"))
									 {
										 // Slide Up Same 
									jQuery(this).parent().parent().find("ul.subMenu:visible").stop().slideUp(400, function(){
});
										 jQuery(this).parent().parent().find('span.arw').addClass("plush");
										 									 			jQuery(this).parent().parent().find('span.arw').removeClass("minus");
									
										// Remove hover class for the slide up menu 					 
													jQuery(this).parent().parent().find("ul.subMenu:visible").prev("a").removeClass("hover");
										
									 }
									 if(jQuery(this).attr('class')){
									var link_class = jQuery(this).attr('class');
									var link_class = link_class.replace('parent', ''); 
										 var link_class = link_class.replace('haslbl', ''); 
									if (!jQuery(this).next("ul"+"."+link_class).is(':visible'))
									{
										jQuery(this).next("ul"+"."+link_class).stop().slideDown(400, function(){
});
										jQuery(this).find('span.arw').removeClass("plush");
											jQuery(this).find('span.arw').addClass("minus");
										
									}
								}
								link.addClass("hover");
								
								e.preventDefault();
								return false; //extra, and to make sure the function has consistent return points
								 }else{
								
									return true; 
								 }
							}
						}
						
						
						
					}); 
				}else{
				
					var oneColumn = null;
					jQuery(groupId+" > ul.mega-menu li.parent").mouseenter(function(){
				
				
		
				
						if(jQuery(this).find("a").attr('class')){
						var link_class = jQuery(this).find("a").attr('class');
						var link_class = link_class.replace('parent', ''); 
						var link_class = link_class.replace('haslbl', ''); 
							
							jQuery(this).children().next("ul"+"."+link_class).stop().slideDown(400, function(){

						});
	                  jQuery(this).children().next("div"+"."+link_class).stop().slideDown(400, function(){
 
                        });
                            
							
							jQuery(this).find('span.arw').removeClass("plush");
										 									 											jQuery(this).find('span.arw').addClass("minus");
							
						}
						if((jQuery(this).parents('li').last().hasClass('column-1'))) {
							oneColumn = 1;
						}else{
							oneColumn = 0;
						}
					});
					jQuery(groupId+" > ul.mega-menu li.parent").mouseleave(function(){
						
				 		if(oneColumn==1){
				 	    	var link_class = jQuery(this).find("a").attr('class');
				     		var link_class = link_class.replace('parent', ''); 
							var link_class = link_class.replace('haslbl', ''); 
							jQuery(this).children().next("ul"+"."+link_class).stop().slideUp(400, function(){
						
							});
							jQuery(this).find('span.arw').addClass("plush");
										 									 					jQuery(this).find('span.arw').removeClass("minus");
							
				 		}else{
				 			jQuery(this).children().next("ul.Level0").stop().slideUp();	
							jQuery(this).children().next("div.Level0").stop().slideUp();	
							jQuery(this).children().find('span.arw').addClass("plush");
										 									 					jQuery(this).children().find('span.arw').removeClass("minus");
				 		}
				 
					});
				}
				
			}else{
				
				if(screenView!="smart-mobileview")
				{
					
					jQuery(groupId+' > ul.cwsMenu.mega-menu').find("li > ul.subMenu:visible").slideUp(400, function(){});
				jQuery(groupId+' > ul.cwsMenu.mega-menu').find('span.arw').addClass("plush");
				jQuery(groupId+' > ul.cwsMenu.mega-menu').find('span.arw').removeClass("minus");
					
					var otherOpen = jQuery(groupId+' > ul.cwsMenu.mega-menu').find("li > ul.subMenu");
						
						jQuery(otherOpen).slideUp();
						jQuery(otherOpen).children().find('ul.subMenu:visible').slideUp();
						jQuery(otherOpen).children().find('ul.subMenu:visible > .cmsbk').slideUp();
						jQuery(otherOpen).children().find('li span.arw').addClass("plush");
						jQuery(otherOpen).children().find('li span.arw').removeClass("minus");
					
					jQuery(groupId+' > ul.cwsMenu.mega-menu').find("li > div > ul.subMenu:visible").slideUp(400, function(){});
				
					
					var otherOpen = jQuery(groupId+' > ul.cwsMenu.mega-menu').find("li > div > ul.subMenu");
						
						jQuery(otherOpen).slideUp();
						jQuery(otherOpen).children().find('div > ul.subMenu:visible').slideUp();
						jQuery(otherOpen).children().find('div > ul.subMenu:visible > .cmsbk').slideUp();
						jQuery(otherOpen).children().find('li span.arw').addClass("plush");
						jQuery(otherOpen).children().find('li span.arw').removeClass("minus");
					
					
					var rootLi = jQuery(groupId + ' > .mega-menu.horizontal > li');
					var toplinkHeight = jQuery(rootLi).height();
					jQuery(rootLi).children().find("ul.Level0").css("top",toplinkHeight);
					
				}else{
			
				}
				screenView = 'smart-mobileview';
				jQuery(groupId+" > ul.mega-menu li.parent").unbind("mouseenter");
				jQuery(groupId+" > ul.mega-menu li.parent").unbind("mouseleave");
				jQuery(groupId+' > ul.mega-menu li.parent a').unbind("touchstart");
				
				
				
				jQuery(groupId+" > .cwsMenu.mega-menu li span.arw").click(function(e){
				
				
				
				e.stopImmediatePropagation();
			
				if(jQuery(this).hasClass("plush")) {
					
					
					if((jQuery(this).parents('li').last().hasClass('menu-active'))) {
						
								
					}else if(jQuery(groupId+" > ul.cwsMenu > li").siblings('.menu-active').length)
					{
						var otherOpen = jQuery(groupId+" > ul.cwsMenu > li").siblings('.menu-active');
					
						jQuery(otherOpen).find("ul.subMenu").slideUp();
					
						jQuery(otherOpen).find('span.arw').addClass("plush");
						jQuery(otherOpen).find('span.arw').removeClass("minus");
					
						jQuery(otherOpen).children().find('li.menu-active > ul').slideUp();
						jQuery(otherOpen).children().find('li.menu-active > .cmsbk').slideUp();
						jQuery(otherOpen).children().find('li span.arw').addClass("plush");
						jQuery(otherOpen).children().find('li span.arw').removeClass("minus");
						
						jQuery(otherOpen).removeClass("menu-active");
						jQuery(otherOpen).children().find('li').removeClass("menu-active");
					
						
						
						
					}else if(jQuery(groupId+" > div.cwsMenu > ul > li").siblings('.menu-active').length)
					{
						var otherOpen = jQuery(groupId+" > div.cwsMenu > ul > li").siblings('.menu-active');
					
						jQuery(otherOpen).find("div.subMenu").slideUp();
					
						jQuery(otherOpen).find('span.arw').addClass("plush");
						jQuery(otherOpen).find('span.arw').removeClass("minus");
					
						jQuery(otherOpen).children().find('li.menu-active > div.subMenu').slideUp();
						jQuery(otherOpen).children().find('li.menu-active > .cmsbk').slideUp();
						jQuery(otherOpen).children().find('li span.arw').addClass("plush");
						jQuery(otherOpen).children().find('li span.arw').removeClass("minus");
						
						jQuery(otherOpen).removeClass("menu-active");
						jQuery(otherOpen).children().find('li').removeClass("menu-active");
					
						
						
						
					}
					jQuery(this).parent().parent().addClass("menu-active");
					
					if(jQuery(this).parent().parent().find("li.hideTitle").length) {
						
						jQuery(this).parent().parent().children().find("li.hideTitle > ul.subMenu").slideDown();
						jQuery(this).parent().parent().children().find("li.hideTitle > div.subMenu").slideDown();
						
					}
					jQuery(this).parent().next("ul").slideDown();
					jQuery(this).parent().next("div.subMenu").slideDown();
					jQuery(this).parent().next(".cmsbk").slideDown();
					jQuery(this).removeClass("plush");
					
					jQuery(this).addClass("minus");
					jQuery(this).parent().next("ul").focus();
					
				} else {
						jQuery(this).parent().parent().children().find('li.menu-active > ul').slideUp();
						jQuery(this).parent().parent().children().find('li.menu-active > div.subMenu').slideUp();	
						jQuery(this).parent().parent().children().find('li.menu-active > .cmsbk').slideUp();
						jQuery(this).parent().parent().children().find('li span.arw').addClass("plush");
						jQuery(this).parent().parent().children().find('li span.arw').removeClass("minus");
						jQuery(this).parent().parent().removeClass("menu-active");
						jQuery(this).parent().parent().children().find('li').removeClass("menu-active");
						jQuery(this).parent().next("ul").slideUp();
						jQuery(this).parent().next("div.subMenu").slideUp();
						jQuery(this).parent().next(".cmsbk").slideUp();
						jQuery(this).addClass("plush");
						jQuery(this).removeClass("minus");
						jQuery(this).parent().next("ul").focus();
					
				}
				return false;
			});
				
				

			}
			
		},
		
			
	});
 	return jQuery.Navigationmenupro.cwsmenu;
});
