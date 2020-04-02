define([
	'jquery',
	'underscore'
], function($, _) {
	return [
	'$rootScope',
	'$scope',
	'elementManager',
	'$uibModal',
	'$uibModalInstance',
	'magezonBuilderService',
	function(
		$rootScope,
		$scope,
		elementManager,
		$uibModal,
		$uibModalInstance,
		magezonBuilderService
	) {
		var mgz = this;

		mgz.search = '';

		mgz.searchElements = [];
		mgz.title          = 'Add Element';

		mgz.model = {};

		mgz.tabs = {};
		mgz.tabs['all'] = {
			name: 'All',
			type: 'all',
			elements: _.filter(elementManager.elements, function(element) {
				if (element.hasOwnProperty('modalVisible') && !element['modalVisible']) return false;
				return true;
			})
		};

		_.each(elementManager.groups, function(group) {
			group['elements'] = _.filter(elementManager.elements, function(element) {
				if (element.hasOwnProperty('modalVisible') && !element['modalVisible']) return false;
				return element.group == group.type;
			});
			if (group.elements.length) {
				mgz.tabs[group.type] = group;
			}
		});

		function resizeModal() {
			var headerPosition = $('.mgz-modal-elements .modal-content').offset().top;
			var headerHeight   = $('.mgz-modal-elements .nav.nav-tabs').outerHeight();
			var footerHeight   = $('.mgz-modal-elements .mgz-modal-header').outerHeight();
			var height         = $(window).height() - headerHeight - footerHeight - 30 - 60;
			if (height < $('.mgz-modal-elements .mgz-elements-wrapper > ul').height()) {
				$('.mgz-modal-elements .mgz-elements-wrapper').height(height);
			} else {
				$('.mgz-modal-elements .mgz-elements-wrapper').css('height', '');
			}
		}

		$uibModalInstance.rendered.then(function() {
			resizeModal();
	    });

	    $(window).resize(function(event) {
	    	if ($('.mgz-modal-elements').is(':visible')) {
	    		resizeModal();
	    	}
	    });

		mgz.ok = function() {
			$uibModalInstance.close();
		}

		mgz.cancel = function() {
			$uibModalInstance.dismiss('cancel');
		}

		mgz.activeTab = function() {
			mgz.search         = '';
			mgz.searchElements = [];
		}

		mgz.filterElements = function() {
			mgz.active = null;
			mgz.searchElements = _.filter(elementManager.elements, function(element) {
				if (element.hasOwnProperty('modalVisible') && !element['modalVisible']) return false;
				return (element.name.toLowerCase().indexOf(mgz.search.toLowerCase()) !== -1) || (element.description && element.description.toLowerCase().indexOf(mgz.search.toLowerCase()) !== -1) || (element.search && element.search.toLowerCase().indexOf(mgz.search.toLowerCase()) !== -1)
			});
		}

		mgz.addComponent = function(element) {
			$uibModalInstance.dismiss('cancel');

			var targetElement = magezonBuilderService.getTargetElement();
			var targetAction  = magezonBuilderService.getTargetAction();

			if (targetElement && targetAction) {
				switch(targetAction) {
					case 'replace':
						$rootScope.$broadcast('replaceComponent', {
							targetElement: targetElement,
							type: element.type
						});
					break;

					case 'addChildren':
						$rootScope.$broadcast('addChildrenComponent', {
							targetElement: targetElement,
							type: element.type
						});
					break;

					case 'addNext':
						$rootScope.$broadcast('addNextComponent', {
							targetElement: targetElement,
							type: element.type
						});
					break;
				}
			} else {
				$rootScope.$broadcast('rootAddComponent', element);
			}
		}

		mgz.openModal = function(element) {
			mgz.cancel();
			var result = $uibModal.open({
				templateUrl: magezonBuilderService.getViewFileUrl('Magezon_Builder/js/templates/modal/form.html'),
				controller: 'magezonBuilderModalElement',
				controllerAs: 'mgz',
				openedClass: 'mgz-modal-open',
				windowClass: 'mgz-modal mgz-modal-elements mgz-builder-element' + element.type + '-form',
				resolve: {
					formData: {
						element: element
					}
				}
			}).result.then(function() {}, function() {});
		}

		mgz.clearSearch = function() {
			mgz.activeTab();
			$('.mgz-elements-filter').trigger('focus');
		}

		$uibModalInstance.rendered.then(function() {
			$('.mgz-elements-filter').trigger('focus');
		});
	}];
});