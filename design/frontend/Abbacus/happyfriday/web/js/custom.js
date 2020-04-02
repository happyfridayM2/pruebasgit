require(['jquery','owl_carousel'],function($){
	jQuery(document).ready(function(){
		// var isDesktop = window.matchMedia("only screen and (min-width: 760px)").matches;

	 //    if (isDesktop) {
	 //       jQuery('.nav-sections #cwsMenu-1').appendTo('.cms-home .header.content, .catalog-category-view .header.content, .cms-page-view .header.content, .category-default-category .header.content, .catalogsearch-result-index .header.content, .catalog-product-view .header.content, .mpblog-post-index .header.content');
	 //    }
		// change shipping method on onepage checkout on click of radio button
		jQuery('body').on('click', '.custom-shipping-method-list .shipping-method-code', function(){
			var shippingMethodCode = jQuery(this).val();
			jQuery('#iwd_opc_shipping_method_group').val(shippingMethodCode).trigger('change');
		});
		// END - change shipping method on onepage checkout on click of radio button

		// change payment method on onepage checkout on click of radio button
		jQuery('body').on('click', '.custom-payment-method-list .payment-method-code', function(){
			var paymentMethodCode = jQuery(this).val();
			//jQuery('#iwd_opc_payment_method_select').val(paymentMethodCode).trigger('change');
			jQuery('#iwd_opc_payment_method_select').next().find('div[data-value='+paymentMethodCode+']').trigger('click');
		});
		// END - change payment method on onepage checkout on click of radio button


		jQuery('.product.info.detailed .product.data.items .data.item.title:first-child').click();
		jQuery(window).on('scroll', stickyheader);
		function stickyheader() {
			// if(jQuery(window).scrollTop() > 50) {
			// 	jQuery('.page-header').addClass('sticky');
			// 	jQuery('.block-search + .header.links').insertAfter(jQuery('.panel.header.bottom .minicart-wrapper'));
			// 	var height = jQuery('.page-header.sticky').innerHeight();
			// 	jQuery('.page-main').css('padding-top', height);
			// }
			// else {
			// 	jQuery('.page-header').removeClass('sticky');
			// 	jQuery('.minicart-wrapper + .header.links').insertAfter(jQuery('.panel.header.top .block.block-search'));
			// 	jQuery('.page-main').css('padding-top', 0);
			// }

			if(jQuery(window).scrollTop() > 31) {
				jQuery('.page-header').addClass('newsticky');
				var height = jQuery('.page-header.sticky.newsticky').innerHeight();
				jQuery('.page-main').css('padding-top', height);
			}
			else {
				jQuery('.page-header').removeClass('newsticky');
				jQuery('.page-main').css('padding-top', 0);
			}
		}
		function stickymobileheader() {
			if(jQuery(window).scrollTop() > 30) {
				jQuery('.page-header').addClass('stickyonmobile ');
				var height = jQuery('.page-header.stickyonmobile').innerHeight();
				jQuery('.page-main').css('padding-top', height);
			}
			else {
				jQuery('.page-header').removeClass('stickyonmobile ');
				jQuery('.page-main').css('padding-top', 0);
			}
		}
		if(!(window.matchMedia("(min-width: 768px)").matches)) {
			jQuery(window).off('scroll', stickyheader);
			jQuery(window).on('scroll', stickymobileheader);
			jQuery('.panel.header.top .block.block-search').appendTo('.headerwrapper');
		}
		else {
			jQuery(window).on('scroll', stickyheader);
			jQuery(window).off('scroll', stickymobileheader);
		}
		
		//search toggle in header 
		// jQuery('.panel.wrapper .block-search .label').on('click',function(){
		// 	jQuery(this).next('.control').slideToggle();
		// });


		// toggle review form on product view page
		jQuery('body').on('click','.add-review .review-form-toggle',function(){
			jQuery(this).parent().next('.review-form').toggle();
		});


		//toggle filter 

		jQuery('.catalog-topnav.amasty-catalog-topnav').slideUp();
		jQuery('body').on('click', '.toolbar-toggle', function(){
			jQuery('.catalog-topnav.amasty-catalog-topnav').slideToggle();
			jQuery(this).toggleClass('expanded');
		});
	

		
		//product viewpage custom arrow click functionality


		// var cntid = setInterval(imgcheck,1000); // set an interval to check if slick images have been loaded

		// function imgcheck() {
		// 	if(jQuery('.amasty-gallery-thumb-link').find('img').length>0) {
		// 		jQuery('.amasty-gallery-thumb-link').eq(0).click();
		// 		clearInterval(cntid);
		// 	}
		// }


		
			
		jQuery('.custom-arrow-container').on('click',function(e){
			e.stopPropagation();
		})

		jQuery('.custom-arrow-container .left-arrow a').on('click',function(e){
				var count = 0;	
				jQuery('.amasty-gallery-thumb-link').each(function(){
					if(jQuery(this).hasClass('active')) {
						count++;
					}
				});
			
				if(count == 0) {
					jQuery('.amasty-gallery-thumb-link').eq(jQuery('.amasty-gallery-thumb-link').length-1).click();
				}
				
				else if(count != 0){
					var index = jQuery('.amasty-gallery-thumb-link.active').index();
					if(index == 0) {
						jQuery('.amasty-gallery-thumb-link').eq(jQuery('.amasty-gallery-thumb-link').length-1).click();
					}
					else{
						jQuery('.amasty-gallery-thumb-link').eq(index-1).click();
					}
				}
		});
			
		jQuery('.custom-arrow-container .right-arrow a').on('click', function(){
			
			var count = 0;	
			jQuery('.amasty-gallery-thumb-link').each(function(){
				if(jQuery(this).hasClass('active')) {
					count++;
				}
			});

			if(count == 0) {
				jQuery('.amasty-gallery-thumb-link').eq(1).click();
			}
					
			else if(count != 0) {
				var index = jQuery('.amasty-gallery-thumb-link.active').index();
				if(index == (jQuery('.amasty-gallery-thumb-link').length-1)) {
					jQuery('.amasty-gallery-thumb-link').eq(0).click();
				}
				else{
					jQuery('.amasty-gallery-thumb-link').eq(index+1).click();
				}
			}
		});

	jQuery("#pageflip").click(function(){
  jQuery(".close-menu").click();
});
	
jQuery('.filter-current-subtitle').click(function() {
   jQuery('.items').toggle();
});

jQuery(".nav-toggle").click(function(){
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
  
});

	});
		

			
	jQuery(window).load(function(){
		var clickid = setInterval(autoclick,5000);
		var cnt = 0;
		function autoclick() {
			jQuery('.amasty-gallery-thumb-link').each(function(){
				if(jQuery(this).hasClass('active')) {
					cnt++;
				}
			});

			if (cnt == 0) {
				jQuery('#amasty-gallery-images .amasty-gallery-thumb-link').eq(0).click();
			}
			
			else {
				var index = jQuery('.amasty-gallery-thumb-link.active').index();
				if (index == (jQuery('#amasty-gallery-images .amasty-gallery-thumb-link').length - 1)) {
					jQuery('#amasty-gallery-images .amasty-gallery-thumb-link').eq(0).click();
				} else {
					jQuery('#amasty-gallery-images .amasty-gallery-thumb-link').eq(index+1).click();
				}
			}
		}
	});

	jQuery(".messages").show();
setTimeout(function() { jQuery(".messages").hide(); }, 4000);

jQuery('body') 
        .prepend(
                "<div class='closesss' id='pageflip'>"
                        + "</div>");

});

