
@desktop: ~"only screen and (min-width: @{responsive_breakpoint})";
@mobile: ~"only screen and (max-width: @{responsive_breakpoint})";

/* Menu Outer */
#cwsMenu-@{group_id}.cwsMenuOuter { background-color:@menubgcolor; max-width:100%; padding:@menupadding; }
#cwsMenu-@{group_id}.vertical { max-width:@menu_width; }

#cwsMenu-@{group_id} {
	
	.menuTitle { color:@titlecolor; background-color:@titlebgcolor; }
	.cwsMenu.mega-menu { max-width:@menu_width; margin:0 auto; }
	
	.cwsMenu a[class*="Level"] > .img { max-width:@itemimageheight; max-height:@itemimagewidth;
		img { max-width:100%; max-height:100%; }
	}
	
	/***** Root Menu Items styles ****/
	.cwsMenu > li > a { color:@lvl0color; font-weight:@lvl0weight; font-size:@lvl0size; padding:@lvl0padding; text-transform:@lvl0case; background-color:@lvl0bgcolor; border-radius:@lvl0corner; }
	.cwsMenu > li:hover > a { color:@lvl0colorh; background-color:@lvl0bgcolorh; }
	.cwsMenu > li.active > a { color:@lvl0colora; background-color:@lvl0bgcolora; }
	
	/***** 1 level Menu Items styles ****/
	.cwsMenu li.Level1 > a { color:@mmlvl1color; font-weight:@mmlvl1weight; font-size:@mmlvl1size; padding:@mmlvl1padding; text-transform:@mmlvl1case; background-color:@mmlvl1bgcolor; }
	.cwsMenu .megamenu li.Level1 > a { & when (@mmlvl1showdivider = 1) { border-bottom:1px solid @mmlvl1dvcolor; } }
	.cwsMenu li.Level1:hover > a { color:@mmlvl1colorh; background-color:@mmlvl1bgcolorh; }
	.cwsMenu li.Level1.active > a { color:@mmlvl1colora; background-color:@mmlvl1bgcolora; }
	
	/***** 2 level Menu Items styles ****/
	.cwsMenu li.Level2 { & when (@mmlvl2showdivider = 1) { border-top:1px solid @mmlvl2dvcolor; } }
	.cwsMenu li.Level2 > a { color:@mmlvl2color; font-size:@mmlvl2size; font-weight:@mmlvl2weight; text-transform:@mmlvl2case; padding:@mmlvl2padding; background-color:@mmlvl2bgcolor; }
	.cwsMenu li.Level2 > a:hover { color:@mmlvl2colorh; background-color:@mmlvl2bgcolorh; }
	.cwsMenu li.Level2.active > a { color:@mmlvl2colora; background-color:@mmlvl2bgcolora; }
	
	/***** 3 level Menu Items styles ****/
	.cwsMenu li.Level3,
	.cwsMenu li.Level3 li { & when (@mmlvl3showdivider = 1) { border-top:1px solid @mmlvl3dvcolor; } }
	.cwsMenu li.Level3 a { color:@mmlvl3color; font-size:@mmlvl3size; font-weight:@mmlvl3weight; text-transform:@mmlvl3case; padding:@mmlvl3padding; background-color:@mmlvl3bgcolor; }
	.cwsMenu li.Level3 a:hover { color:@mmlvl3colorh; background-color:@mmlvl3bgcolorh; }
	.cwsMenu li.Level3.active > a,
	.cwsMenu li.Level3 li.active > a { color:@mmlvl3colora; background-color:@mmlvl3bgcolora; }
	
	/* Mega Menu lavel 3 to...*/
	.cwsMenu li.Level3 li a:before { content:"."; display:block; float:left; height:@mmlvl3size+4; width:10px; font-size:0; }
	.cwsMenu li.Level3 li li a:before { width:20px; }
	
	@media @desktop {
		.cwsMenu.mega-menu li.parent>a>span.arw:after { display:none; }
		.cwsMenu li.parent>a>span.arw when (@lvl0arw = 1) { content:''; display:inline-block; position:static; margin-left:3px; width:0; height:0; vertical-align:middle;
			background:none; border:5px solid transparent; opacity:0.5; }
		
		/* Mega Menu box */
		.cwsMenu.mega-menu>li.megamenu > ul.subMenu { padding:@mmpnl-padding; background-color:@mmpnl-bgcolor; box-shadow:0px 2px 4px 0 rgba(0,0,0,0.3);
			 border-width:@mmpnl-bdwidth; border-style:solid; border-color:@mmpnl-bdcolor; border-radius:@mmpnl-corner; }
		.cwsMenu.mega-menu>li.megamenu > div.subMenu { padding:@mmpnl-padding; background-color:@mmpnl-bgcolor; box-shadow:0px 2px 4px 0 rgba(0,0,0,0.3); display: none;
			 border-width:@mmpnl-bdwidth; border-style:solid; border-color:@mmpnl-bdcolor; border-radius:@mmpnl-corner; transition: none; -webkit-transition: none; }
		.cwsMenu.mega-menu>li.megamenu ul.subMenu ul.subMenu { padding:0; margin:0; position:static; min-width:inherit; display:block; border:0; box-shadow:none; }
		
		/* Mega Menu lavel 1*/
		.cwsMenu li.megamenu li.Level1 { padding:@mmpnl-clm-padding; }
		
		/* Dropdown Menu box */
		.cwsMenu.mega-menu > li:not(.megamenu) ul.subMenu { padding:@ddpnl-padding; margin:0; width:@ddpnl-width; background-color:@ddpnl-bgcolor;
			border-width:@ddpnl-bdwidth; border-style:solid; border-color:@ddpnl-bdcolor; border-radius:@ddpnl-corner; box-shadow:0px 2px 4px 0 rgba(0,0,0,0.3); }
		.cwsMenu.mega-menu > li.column-1:not(.megamenu) li.first a { border-radius:@ddpnl-corner; border-bottom-left-radius:0; border-bottom-right-radius:0; }	
		.cwsMenu.mega-menu > li.column-1:not(.megamenu) li.last a { border-radius:@ddpnl-corner; border-top-left-radius:0; border-top-right-radius:0; }	
		.cwsMenu.mega-menu > li.column-1:not(.megamenu) li { & when (@ddshowdivider = 1) { border-top:1px solid @ddlinkdvcolor; } }
		.cwsMenu.mega-menu > li.column-1:not(.megamenu) li.first { border:0 none; }
		.cwsMenu.mega-menu > li.column-1:not(.megamenu) li a { color:@ddlinkcolor; font-size:@ddlinksize; font-weight:@ddlinkweight; text-transform:@ddlinkcase; padding:@ddlinkpadding; background-color:@ddlinkbgcolor; }
		.cwsMenu.mega-menu > li.column-1:not(.megamenu) li:hover > a { color:@ddlinkcolorh; background-color:@ddlinkbgcolorh; }
		.cwsMenu.mega-menu > li.column-1:not(.megamenu) li.active > a { color:@ddlinkcolora; background-color:@ddlinkbgcolora; }
		.cwsMenu.mega-menu > li.column-1:not(.megamenu) li.parent>a>span.arw when (@lvl0arw = 1) { margin-top:-3px; position:absolute; top:50%; right:5px; border-left-color:@ddlinkcolor; }
		.cwsMenu.mega-menu > li.column-1:not(.megamenu) li.parent:hover>a>span.arw when (@lvl0arw = 1) { border-left-color:@ddlinkcolorh; }
		.cwsMenu.mega-menu > li.column-1:not(.megamenu) li.parent.active>a>span.arw when (@lvl0arw = 1) { border-left-color:@ddlinkcolora; }
        
        .cwsMenu.mega-menu > li.column-1.aRight:not(.megamenu) li.parent>a>span.arw when (@lvl0arw = 1) { right:10px; }
		.cwsMenu.mega-menu > li.column-1.aRight:not(.megamenu) li.parent > a > span.arw when (@lvl0arw = 1){ border-right-color:@ddlinkcolor; border-left-color: transparent; }
		.cwsMenu.mega-menu > li.column-1.aRight:not(.megamenu) li.parent:hover>a>span.arw when (@lvl0arw = 1) { border-right-color:@ddlinkcolorh; }
		.cwsMenu.mega-menu > li.column-1.aRight:not(.megamenu) li.parent.active>a>span.arw when (@lvl0arw = 1) { border-right-color:@ddlinkcolora; }

		/***** Horizontal Menu ****/
		/* Horizontal Menu 0 Level  */
		.cwsMenu.horizontal > li { float:left; & when (@lvl0showdivider = 1) { border-right:1px solid @lvl0dvcolor; } }
		.cwsMenu.horizontal > li.parent > a>span.arw when (@lvl0arw = 1) { border-top-color:@lvl0color; }
		.cwsMenu.horizontal > li.parent:hover>a>span.arw when (@lvl0arw = 1) { border-top-color:@lvl0colorh; }
		.cwsMenu.horizontal > li.parent.active>a>span.arw when (@lvl0arw = 1) { border-top-color:@lvl0colora; }
		
		/* Horizontal Menu Dropdown position */
		.cwsMenu.horizontal li > ul.subMenu { top:99%; left:0; }
		.cwsMenu.horizontal li > div.subMenu { top:99%; left:0; }
		.cwsMenu.horizontal li.aRight ul.subMenu { left:auto; right:0; }
		.cwsMenu.horizontal li.column-1 ul li > ul { left:100%; top:-5px; }
		.cwsMenu.horizontal li.column-1.aRight ul li > ul { right:100%; }
		
		/***** Verticle Menu ****/
		.menuTitle { font-size:18px; padding:10px; margin:0; }
		
		/********** Verticle Menu 0 Level  **************/
		.cwsMenu.vertical > li { & when (@lvl0showdivider = 1) { border-top:1px solid @lvl0dvcolor; } }
		.cwsMenu.vertical > li:first-child { border-top:0 none; }
		.cwsMenu.vertical > li.parent > a>span.arw { position:absolute; right:5px; top:50%; margin-top:-5px; border-left-color:@lvl0color; }
		
		/* Verticle Menu Dropdown position */
		.cwsMenu.vertical li > ul.subMenu { top:-5px; left:100%; }
		.cwsMenu.vertical li.aRight > ul.subMenu,
		.cwsMenu.vertical li.aRight li > ul.subMenu { left:auto; right:100%; }
		
		/* Verticle Mega Menu */
		.cwsMenu.Verticle li.megamenu.column-5>ul.subMenu,
		.cwsMenu.Verticle li.megamenu.full-width>ul.subMenu { width:1000px; }/* 5Column or fullWidth */
		
		/* right to left align setting */
		.cwsMenuOuter.rtl .menuTitle { text-align:right; }
		.cwsMenuOuter.rtl { direction:rtl; }
		.cwsMenuOuter.rtl .cwsMenu li.parent>a:after { margin-left:0; margin-right:5px; }
		.cwsMenuOuter.rtl .cwsMenu.horizontal > li,
		.cwsMenuOuter.rtl .cwsMenu li.megamenu ul li.Level1 { float:right; }
		
		.cwsMenuOuter.rtl .cwsMenu.horizontal li > ul.subMenu { left:inherit; right:0; }
		.cwsMenuOuter.rtl .cwsMenu.horizontal li.column-1 ul li ul { right:100%; }
	
		.cwsMenuOuter.rtl .cwsMenu.vertical > li.parent > a:after { right:inherit; left:10px; margin:0; }
		.cwsMenuOuter.rtl .cwsMenu.vertical li.column-1 li.parent > a:after { right:inherit; left:5px; }
		.cwsMenuOuter.rtl .cwsMenu.vertical li.column-1.aLeft li.parent > a:after { border-right-color:transparent !important; }
		.cwsMenuOuter.rtl .cwsMenu.vertical > li.parent.aRight > a:after { left:5px; border-left-color:transparent !important; border-right:5px solid #666; }
		.cwsMenuOuter.rtl .cwsMenu.vertical li.column-1.aRight li.parent > a:after { border-left-color:transparent !important; border-right:5px solid #666; }
		
		.cwsMenuOuter.rtl .cwsMenu.mega-menu.horizontal li.column-1 li.parent > a:after { border-left-color:transparent !important; border-right:5px solid #666; right:inherit; left:5px; }

		/* Full Width On hover item */
		.cwsMenu.horizontal > li.full-width-mouse-hover, .cwsMenu.vertical > li.full-width-mouse-hover { position:static; }
		.cwsMenu.horizontal > li.full-width-mouse-hover a, .cwsMenu.vertical > li.full-width-mouse-hover a { position:relative; }
		.cwsMenu.horizontal > li.full-width-mouse-hover:last-child a:before, .cwsMenu.vertical > li.full-width-mouse-hover:last-child a:before { content:none; }
		.cwsMenu.horizontal > li.full-width-mouse-hover .subMenu a:before, .cwsMenu.vertical > li.full-width-mouse-hover .subMenu a:before { content: none; }
		.cwsMenu.horizontal > li.full-width-mouse-hover.parent > a > span.arw { margin: 5px 0 0 5px; }
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu > ul.subMenu { width:100%; left:0; right:0; margin:0 auto;
			-webkit-animation-duration: .9s; animation-duration: .9s; -webkit-animation-fill-mode: both; animation-fill-mode: both; -webkit-animation-name: fadeInUpSubMenu; animation-name: fadeInUpSubMenu;
			-moz-animation-duration: .2s; -webkit-animation-duration: .2s; animation-duration: .2s;
		}
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu > ul.subMenu { margin:0 auto;
			-webkit-animation-duration: .9s; animation-duration: .9s; -webkit-animation-fill-mode: both; animation-fill-mode: both; -webkit-animation-name: fadeInUpSubMenu; animation-name: fadeInUpSubMenu;
			-moz-animation-duration: .2s; -webkit-animation-duration: .2s; animation-duration: .2s;
		}
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent > a > span.arw,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent > a > span.arw { background-color:transparent; display:block !important; width: 0px !important;
			height:0px !important; border-top:5px solid transparent !important; border-bottom:5px solid transparent !important; border-left:6px solid @lvl0color !important; position:absolute !important; right:6px; top:50%; margin-top:-5px;
		}
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent.aRight a span.arw,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent.aRight a span.arw { right: 11px; margin-top: -5px; border-top:5px solid transparent !important; border-bottom:5px solid transparent !important; border-right:6px solid @lvl0color !important; border-left:5px solid transparent !important; }

		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent > a > span.arw:after,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent > a > span.arw:after { content:none; }
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent:not(.hideTitle) .Level1.subMenu,
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent .Level2.subMenu,
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent .Level3.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent:not(.hideTitle) .Level1.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent .Level2.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent .Level3.subMenu { display:none !important; position:absolute !important; top: 0 !important; left: calc(90%) !important;
			width: 100%; max-width:230px; background-color: @mmlvl2bgcolor; box-shadow: 1px 2px 5px -1px #888888 !important; -webkit-animation-duration: .9s; animation-duration: .9s; -webkit-animation-fill-mode: both;
			animation-fill-mode: both; -webkit-animation-name: fadeInSubSubMenu; animation-name: fadeInSubSubMenu; -moz-animation-duration: .2s; -webkit-animation-duration: .2s; animation-duration: .2s;
		}
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.aRight.parent.hideTitle .Level1.subMenu,
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.aRight.parent:not(.hideTitle) .Level1.subMenu,
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.aRight ul.subMenu,
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.aRight.parent:not(.hideTitle) ul li > ul,
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.aRight .Level2.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.aRight.parent.hideTitle .Level1.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.aRight.parent:not(.hideTitle) .Level1.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.aRight ul.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.aRight.parent:not(.hideTitle) ul li > ul,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.aRight .Level2.subMenu { left:auto !important; right:calc(97%) !important; }

		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent:hover .Level1.subMenu,
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent .Level1.subMenu li.parent:hover .Level2.subMenu,
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent .Level2.subMenu li.parent:hover .Level3.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent:hover .Level1.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent .Level1.subMenu li.parent:hover .Level2.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent .Level2.subMenu li.parent:hover .Level3.subMenu { display:block !important; }
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li:hover ul.Level1.subMenu,
		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.hideTitle ul.Level1.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li:hover ul.Level1.subMenu,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.hideTitle ul.Level1.subMenu { display:block !important; }

		.cwsMenu.horizontal > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent.hideTitle,
		.cwsMenu.vertical > li.full-width-mouse-hover.megamenu ul.subMenu > li.parent.hideTitle { padding:0 !important; }

		  @-webkit-keyframes fadeInUpSubMenu {
			from {
			  opacity: 0;
			  -webkit-transform: translate3d(0, 10%, 0);
			  transform: translate3d(0, 10%, 0);
			}
			to {
			  opacity: 1;
			  -webkit-transform: none;
			  transform: none;
			}
		  }
		  @keyframes fadeInUpSubMenu {
			from {
			  opacity: 0;
			  -webkit-transform: translate3d(0, 10%, 0);
			  transform: translate3d(0, 10%, 0);
			}
			to {
			  opacity: 1;
			  -webkit-transform: none;
			  transform: none;
			}
		  }
		  .fadeInUpSubMenu {
			-webkit-animation-name: fadeInUpSubMenu;
			animation-name: fadeInUpSubMenu;
		  }
		  @-webkit-keyframes fadeInSubSubMenu {
			from {
			  opacity: 0;
			  -webkit-transform: translate3d(-10%, 0, 0);
			  transform: translate3d(-10%, 0, 0);
			}
			to {
			  opacity: 1;
			  -webkit-transform: none;
			  transform: none;
			}
		  }
		  @keyframes fadeInSubSubMenu {
			from {
			  opacity: 0;
			  -webkit-transform: translate3d(-10%, 0, 0);
			  transform: translate3d(-10%, 0, 0);
			}
			to {
			  opacity: 1;
			  -webkit-transform: none;
			  transform: none;
			}
		  }
		  .fadeInSubSubMenu {
			-webkit-animation-name: fadeInSubSubMenu;
			animation-name: fadeInSubSubMenu;
		  }
		
	}
	
	@media @mobile {
		
.cwsMenu.mega-menu li > ul.subMenu, .cwsMenu.mega-menu li>div.subMenu { position:static; width:auto !important; }
		.cwsMenu.mega-menu li > ul.subMenu li, .cwsMenu.mega-menu li>div.subMenu li { float:none; width:auto !important; }
		
		.cwsMenu.mega-menu>li.megamenu div.subMenu { margin-top: 0 !important; padding: 0 !important; }
		.cwsMenu.mega-menu li>div.subMenu .container { padding-left:0; padding-right:0; }
		
	/* Mobile Menu */
		.cwsMenu.mega-menu > li { & when (@lvl0showdivider = 1) { border-top:1px solid @lvl0dvcolor; } }
		.cwsMenu.mega-menu li a > span.arw { display:block; }
		
		.cwsMenu li.Level1 { & when (@mmlvl1showdivider = 1) { border-top:1px solid @mmlvl1dvcolor; } }
		.cwsMenu li.Level1 > a { border:0 none; }
		
		.cwsMenu li.megamenu ul li.hideTitle>a.Level1 { display:none; }
		.cwsMenu li.megamenu ul li.hideTitle>.subMenu { display:block; }
		
		li.cmsbk a ~ div.cmsbk { display:none; }
		
		/*For Column wise create menu.*/
		.cwsMenu li.megamenu ul li.hideTitle>span { display:none; }
		
		/* Smart Expan Menu laval 2 */
		.cwsMenu.mega-menu li > ul,
		.cwsMenu.mega-menu li > div.subMenu { display:none; }
		
		/* right to left align setting */
		.cwsMenuOuter.rtl .cwsMenu.mega-menu li > span.arw { left:0; right:inherit; }
	}

}