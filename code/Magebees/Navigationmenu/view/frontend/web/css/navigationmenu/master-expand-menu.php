
/* Menu Outer */
#cwsMenu-@{group_id}.cwsMenuOuter { background-color:@menubgcolor; max-width:@menu_width; }

#cwsMenu-@{group_id} {
	
	.cwsMenu a[class*="Level"] > .img { max-width:@itemimageheight; max-height:@itemimagewidth;
		img { max-width:100%; max-height:100%; }
	}
	.menuTitle { color:@titlecolor; background-color:@titlebgcolor; }
	
	/***** Root Menu Items styles ****/
	.cwsMenu > li { & when (@lvl0showdivider = 1) { border-top:1px solid @lvl0dvcolor; } }
	.cwsMenu > li > a { color:@lvl0color; font-weight:@lvl0weight; font-size:@lvl0size; padding:@lvl0padding; text-transform:@lvl0case; background-color:@lvl0bgcolor; }
	.cwsMenu > li > a:hover { color:@lvl0colorh; background-color:@lvl0bgcolorh; }
	.cwsMenu > li.active > a { color:@lvl0colora; background-color:@lvl0bgcolora; }
	
	/***** 1 level Menu Items styles ****/
	.cwsMenu li.Level1 { border-top:1px solid @sublvl1dvcolor; }
	.cwsMenu li.Level1 > a { color:@sublvl1color; font-weight:@sublvl1weight; font-size:@sublvl1size; padding:@sublvl1padding; text-transform:@sublvl1case; background-color:@sublvl1bgcolor; }
	.cwsMenu li.Level1 > a:hover { color:@sublvl1colorh; background-color:@sublvl1bgcolorh; }
	.cwsMenu li.Level1.active > a { color:@sublvl1colora; background-color:@sublvl1bgcolora; }
	
	/***** 2 level Menu Items styles ****/
	.cwsMenu li.Level2 { border-top:1px solid @sublvl2dvcolor; }
	.cwsMenu li.Level2 > a { color:@sublvl2color; font-size:@sublvl2size; font-weight:@sublvl2weight; text-transform:@sublvl2case; padding:@sublvl2padding; background-color:@sublvl2bgcolor; }
	.cwsMenu li.Level2 > a:hover { color:@sublvl2colorh; background-color:@sublvl2bgcolorh; }
	.cwsMenu li.Level2.active > a { color:@sublvl2colora; background-color:@sublvl2bgcolora; }
	
	/***** 3 level Menu Items styles ****/
	.cwsMenu li.Level3, .cwsMenu li.Level3 li { border-top:1px solid @sublvl3dvcolor; }
	.cwsMenu li.Level3 a { color:@sublvl3color; font-size:@sublvl3size; font-weight:@sublvl3weight; text-transform:@sublvl3case; padding:@sublvl3padding; background-color:@sublvl3bgcolor; }
	.cwsMenu li.Level3 a:hover { color:@sublvl3colorh; background-color:@sublvl3bgcolorh; }
	.cwsMenu li.Level3.active > a,
	.cwsMenu li.Level3 li.active > a { color:@sublvl3colora; background-color:@sublvl3bgcolora; }
	

	/* Smart Expan Menu */
	.cwsMenu.smart-expand li a > span.arw { display:block; }
	.cwsMenu.smart-expand li.active > ul { display:block; }
	
	
	/* Smart Expan Menu laval 2 */
	.cwsMenu.smart-expand li > ul { display:none; padding:0; margin:0; }
	
	/* Always Expan Menu laval 2 */
	.cwsMenu.always-expand li > ul { display:block; }
	
	/* right to left align setting */
	.cwsMenuOuter.rtl .cwsMenu.smart-expand li > span.arw { right:inherit; left:0; }
	
}
