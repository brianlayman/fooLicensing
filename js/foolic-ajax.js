/**
 * FooLicensing AJAX functions
 */

(function (FOOLIC, $, undefined) {

	FOOLIC.bindLicensekeyHider = function () {
		$('.foolic-hide-licensekey').each(function() {
			var $this = $(this),
				key = $this.text(),
				$link = $('<a href="#">' + foolic_scripts.show_license_key + '</a>'),
				dashIndex = key.indexOf('-'),
				hiddenKey = key.substring(0, dashIndex + 1) + key.substring(dashIndex+1).replace(/[a-zA-Z 0-9.]/g,'#')
			$this.text(hiddenKey);
			$link.on('click', function(e) {
				e.preventDefault();
				$this.text(key);
				$(this).hide();
			});
			$this.after($link).show();
		});
	};

	FOOLIC.bindActionButtons = function () {

		$('body').on('click.foolic', '.foolic-action-detach, .foolic-action-attach', function (e) {
			e.preventDefault();

			if (!confirm(foolic_scripts.are_you_sure)) {
				return;
			}

			var $this  = $(this),
				action = $this.data('action'),
				licensekeyId = $this.data('licensekey-id'),
				domainId = $this.data('domain-id'),
				data   = {
					action: action,
					licensekey_id: licensekeyId,
					domain_id: domainId,
					nonce: foolic_scripts.ajax_nonce
				};

			$this.attr('disabled', 'disabled');

			$this.addClass('foolic-loading');

			$.ajax({
				type: "POST",
				data: data,
				dataType: "json",
				url: foolic_scripts.ajaxurl,
				success : function(response) {
					var messageClass = (response.success === 1) ? 'success' : 'failure',
						message = FOOLIC.fixMessage(response.message, $this),
						$message = $('<div class="foolic-message foolic-message-' + messageClass + '">' + message + '</div>');
					if (response.success === 1) {
						$message.append(' <a href="#" onclick="window.location.reload(); return false;">' + foolic_scripts.refresh_license + '</a>');
					}
					$this.after($message);
					$this.hide();
				},
				complete : function() {
					$this.removeClass('foolic-loading');
				}
			});
		});
	};

	FOOLIC.fixMessage = function(message, $this) {
		var domain = $this.parents('td:first').find('.foolic-domain:first').text(),
			plugin = $this.parents('.foolic_license_listing_item:first').find('h2:first').text();

		message = message.replace('{domain}', '<a href="' + domain + '">' + domain + '</a>');
		message = message.replace('{plugin}', '<strong>' + plugin + '</strong>');
		return message;
	};

	FOOLIC.ready = function () {
		FOOLIC.bindActionButtons();
		FOOLIC.bindLicensekeyHider();
	};

}(window.FOOLIC = window.FOOLIC || {}, jQuery));

jQuery(function () {
	FOOLIC.ready();
});