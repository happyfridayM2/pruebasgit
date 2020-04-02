define([
	'jquery'
], function($) {

	return {
		expressionProperties: {
			'templateOptions.disabled': function(viewValue, modelValue, scope, a1, a2) {
				var elem = $('#' + scope.id).closest('.mgz-design-layout-wrapper');
				elem.removeClass('mgz-design-layout-all');
				elem.removeClass('mgz-design-layout-custom');
				elem.addClass('mgz-design-layout-' + viewValue);
				var firstTab = $(elem.find('.nav-item')[0]);
				if (viewValue == 'all') {
					firstTab.children('a').trigger('click');
				}
			}
		}
	}
})