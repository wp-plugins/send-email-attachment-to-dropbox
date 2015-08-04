;(function($) {
	var mdrop = {
		init: function() {
			$('#mdrop-mail-fetch-form').on( 'submit', this.getMail );
			$('.mdrop-all-checked').on( 'change', this.allChecked );
            $('.mdrop-single-checked').on( 'change', this.singleChecked );
            $('#mdrop-list').on( 'click', '.mdrop-send-to-drop', this.singleDrop );
            $('#mdrop-list').on( 'click', '.mdrop-delete-single', this.delteSingle );
            $('#mdrop-list').on( 'submit', '#mdrop-form-action', this.dropAction );
            $('#mdrop-list').on( 'click', '.mdrop-send-to-post', this.postSingle );
            $('#mdrop').on( 'click', '.mdrop-read-me', this.readMe );

		},

		readMe: function(e) {
			e.preventDefault();
			$("#mdrop-read-me-wrap").toggle(500);
		},

		postSingle: function(e) {
			e.preventDefault();
			if ( ! confirm( 'Are your sure!' ) ) {
				return;
			}
			var self = $(this),
				trwrap = self.closest('.mdrop-tr-wrap'),
				message_id = [trwrap.find('.mdrop-single-checked').val()];
			trwrap.find('.mdrop-single-action-loading').addClass('mdrop-spinner');
			mdrop.post( message_id );
		},

		postMulti: function() {
			
			var action_fields = $('#mdrop-form-action').find('.mdrop-single-checked:checked'),
				message_id = [];
			$.each( action_fields, function( key, field ) {
				message_id.push( $(field).val() );
			});
			mdrop.post( message_id );
		},

		post: function( message_id ) {

			var data = {
					action: 'message_post',
					message_id: message_id,
					_wpnonce: mdrop_ajax_data._wpnonce,
				};
			$.post( mdrop_ajax_data.ajax_url, data, function( res ) {
				if ( res.success ) {
					location.reload();
				} else {

				}
			});
		},

		dropAction: function(e) {
			e.preventDefault();

			if ( ! confirm( 'Are your sure!' ) ) {
				return;
			}

			var self = $(this),
				select = self.find('.mdrop-action-dropdown').val();
			$('.mdrop-multi-action-loading').addClass('mdrop-spinner');
			if ( select === 'delete' ) {
				mdrop.deleteMulti();
			} else if ( select === 'dropbox' ) {
				mdrop.multiDrop();
			} else if ( select === 'post' ) {
				mdrop.postMulti();
			} else {
				$('.mdrop-multi-action-loading').removeClass('mdrop-spinner');
				alert( 'Please select any action' );
			}
		},

		deleteMulti: function() {
		
			var action_fields = $('#mdrop-form-action').find('.mdrop-single-checked:checked'),
				message_id = [];
			$.each( action_fields, function( key, field ) {
				message_id.push( $(field).val() );
			});
			mdrop.delete( message_id );
		},

		delteSingle: function(e) {
			e.preventDefault();
			if ( ! confirm( 'Are your sure!' ) ) {
				return;
			}
			var self = $(this),
				trwrap = self.closest('.mdrop-tr-wrap'),
				message_id = [trwrap.find('.mdrop-single-checked').val()];
			trwrap.find('.mdrop-single-action-loading').addClass('mdrop-spinner');
			mdrop.delete( message_id );
		},

		delete: function( message_id ) {

			var data = {
					action: 'message_delete',
					message_id: message_id,
					_wpnonce: mdrop_ajax_data._wpnonce,
				};
			$.post( mdrop_ajax_data.ajax_url, data, function( res ) {
				if ( res.success ) {
					location.reload();
				} else {

				}
			});
		},

		multiDrop: function() {
			if ( ! confirm( 'Are your sure!' ) ) {
				return;
			}

			var action_fields = $('#mdrop-form-action').find('.mdrop-single-checked:checked'),
				message_id = [];
			$.each( action_fields, function( key, field ) {
				message_id.push( $(field).val() );
			});
			mdrop.sendDrop( message_id );
		},

		singleDrop: function(e) {
			e.preventDefault();
			if ( ! confirm( 'Are your sure!' ) ) {
				return;
			}
			var self = $(this),
				trwrap = self.closest('.mdrop-tr-wrap'),
				message_id = [trwrap.find('.mdrop-single-checked').val()];
			trwrap.find('.mdrop-single-action-loading').addClass('mdrop-spinner');
			mdrop.sendDrop( message_id );
		},

		sendDrop: function( message_id ) {
			var data = {
					action: 'send_drop',
					message_id: message_id,
					_wpnonce: mdrop_ajax_data._wpnonce,
				};
			$.post( mdrop_ajax_data.ajax_url, data, function( res ) {
				if ( res.success ) {
					if ( res.data.runing_status ) {
						mdrop.sendDrop( res.data.message_id );
					} else {
						location.reload();
					}
				} else {

				}
			});
		},

		allChecked: function() {
            var self = $(this),
                table = self.closest('table');
            if ( self.is(':checked') ) {
                table.find('.mdrop-single-checked').prop( 'checked', true );
            } else {
                table.find('.mdrop-single-checked').prop( 'checked', false );
            }
        },

        singleChecked: function() {
            var all_checked = true;
            $('.mdrop-single-checked').each(function(){
                if( !$(this).is(':checked') ){
                    all_checked = false;
                }
            });

            if ( all_checked ) {
                $('.mdrop-all-checked').prop( 'checked', true );
            } else {
                $('.mdrop-all-checked').prop( 'checked', false );
            }
        },
		getMail: function(e) {
			e.preventDefault();

			var self = $(this),
				h3wrap = $('.mdrop-fetch-msg'),
				data = {
					action: 'check_mail',
					start: self.find('input[name="start"]').val(),
					end: self.find('input[name="end"]').val(),
					total: self.find('input[name="total"]').val(),
					_wpnonce: mdrop_ajax_data._wpnonce,
				}

			if ( parseInt(data.start) > parseInt(data.end) ) {
				alert( 'Please correct value insert' );
				return;
			}

			h3wrap.show();
			h3wrap.find('.mdrop-fetch-start').html(data.start);
			h3wrap.find('.mdrop-fetch-end').html(data.start);

			$('.mdrop-loading').addClass('mdrop-spinner');

			$.post( mdrop_ajax_data.ajax_url, data, function( res ) {
				if ( res.success ) {
					if ( res.data.request_status ) {
						mdrop.emailFetchingLoop( res.data.new_message_number, data.end, data.total );
					} else {
						$('.mdrop-loading').removeClass('mdrop-spinner');
						$('.mdrop-loading').html('Done');
						location.reload();
					}
				} else {

				}
			});
		},

		emailFetchingLoop: function( start, end, total ) {
			var h3wrap = $('.mdrop-fetch-msg'),
				data = {
					action: 'check_mail',
					start: start,
					end: end,
					total: total,
					_wpnonce: mdrop_ajax_data._wpnonce,
				}
			
			h3wrap.show();
			h3wrap.find('.mdrop-fetch-end').html( data.start );

			$.post( mdrop_ajax_data.ajax_url, data, function( res ) {
				if ( res.success ) {
					if ( res.data.request_status ) {
						mdrop.emailFetchingLoop( res.data.new_message_number, data.end, data.total );
						h3wrap.find('.mdrop-fetch-end').html( res.data.new_message_number );
					} else {
						$('.mdrop-loading').removeClass('mdrop-spinner');
						$('.mdrop-loading').html('Done');
						location.reload();
					}
				} else {
					
				}
			});
		}
	}
	mdrop.init();
})(jQuery);