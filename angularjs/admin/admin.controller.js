/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
	angular.module('piwikApp').controller('LoginShibbolethAdminController', LoginShibbolethAdminController);
	LoginShibbolethAdminController.$inject = ['piwikApi'];
	function LoginShibbolethAdminController(piwikApi) {
		var self = this;
		// Save action is done here
		this.save = function () {
			var parent = $(this).closest('p'),
				loading = $('.loadingPiwik', parent),
				ajaxSuccess = $('.success', parent);
			this.loading = true;
			piwikApi.post(
				{
					module: 'API',
					method: 'LoginShibboleth.saveShibbolethConfig',
					format: 'JSON'
				}, { data: this.data }
			).then(function () {
				self.isLoading = false;
				var UI = require('piwik/UI');
				var notification = new UI.Notification();
				notification.show(_pk_translate('General_Done'), {
					context: 'success',
					noclear: true,
					type: 'toast',
					id: 'userCountryLocationProvider'
				});
				notification.scrollToNotification();
			}, function () {
				self.isLoading = false;
			});
		}
	}
})();