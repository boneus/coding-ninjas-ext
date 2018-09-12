(function ($) {

	$(document).ready( function () {

		$('#tasks_table').DataTable();

		$('#side-menu a[href="/new-task"]').on( 'click', function( e ) {

			e.preventDefault();

			$('#newTaskModal').modal();
		} );

		$('#add-new-task').on( 'click', function( e ) {

			e.preventDefault();

			$.ajax({
				url: ajaxdata.ajax_url,
				type: 'post',
				data: {
					_ajax_nonce: ajaxdata.nonce,
					action: 'add_new_task',
					title: $('#newTaskModal #task-title').val(),
					freelancer: $('#newTaskModal #task-freelancer').val()
				},
				success: function( response ) {
					alert('Success!');
					location.reload();
				},
				error: function() {
					alert('Fail.');
				}
			});
		} );
	} );
}) (jQuery)