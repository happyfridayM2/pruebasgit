define([
	'jquery',
	'angular',
	'Magezon_Builder/js/factories/FormlyUtils'
], function($, angular, FormlyUtils) {
	return [
	'$rootScope',
	'$scope',
	'$timeout',
	'$uibModalInstance',
	'magezonBuilderService',
	'formlyConfig',
	function(
		$rootScope,
		$scope,
		$timeout,
		$uibModalInstance,
		magezonBuilderService,
		formlyConfig
	) {
		var mgz   = this;
		mgz.title = 'Templates';
		mgz.tabs  = [];

		magezonBuilderService.getBuilderConfig('profile.templates', function(result) {
			var newFields = angular.copy(result.form);
			var fields = FormlyUtils.processFields(newFields, 'children');
			magezonBuilderService.registerTypes(fields, formlyConfig);
			magezonBuilderService.processFormFields(fields, function(tabs) {
				$timeout(function () {
					mgz.tabs = tabs;
				});
			});
		});

		mgz.cancel = function() {
			$uibModalInstance.dismiss('cancel');
		}

	    $uibModalInstance.rendered.then(function() {
	    	$('.mgz-builder-element-form .modal-content').resizable({
	    		minHeight: 300,
	    		minWidth: 380,
	    		resize: function( event, ui ) {
	    			var headerHeight = $('.mgz-builder-element-form .nav.nav-tabs').outerHeight();
	    			var footerHeight = $('.mgz-builder-element-form .mgz-modal-footer').outerHeight();
	    			var height = ui.size.height - headerHeight - footerHeight - 40;
	    			$('.mgz-builder-element-form .tab-content').height(height);
	    		}
	    	});
	    	$('.mgz-builder-element-form .modal-dialog').draggable({
	    		handle: ".mgz-modal-header-inner"
	    	});
	    });
	}]
});