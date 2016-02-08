jQuery(function($){
	$('.select-user-button').click(function(){
		if ( $('.user-list').val() != '' ){
			window.location = '?select_user=true&user_id=' + $('.user-list').val();
		}
	});

	$('.sign-out-client-button').click(function(){
			window.location = $(this).attr('redirect-url') + '?sign_out_client=true';
	});
})