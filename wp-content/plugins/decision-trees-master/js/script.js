jQuery(function($){
	$('.select-user-button').click(function(){
		if ( $('.user-list').val() != '' ){
			window.location = '?select_user=true&user_id=' + $('.user-list').val();
		}
	});

	$('.sign-out-client-button').click(function(){
			window.location = $(this).attr('redirect-url') + '?sign_out_client=true';
	});
	$('.selectable-continue-button').click(function(){
		var data = {
			'action': my_ajax_object.action,
			'operation': 'save-selectable',
		};
		var categorySelected = false;
		$('input[type=checkbox]').each(function(){
			if ($(this).is(':checked') == true){
				if (data.id != null)
					$.extend(data, { 'id' : data.id + ',' + $(this).attr('id').replace('decision-tree-','')});
				else
					$.extend(data, { 'id' : $(this).attr('id').replace('decision-tree-','')});
				categorySelected =true;
			}
		});
		if (categorySelected==false){
			alert('Please, select at least one category');
		}
		else{
			$.ajax({
		        type: "post",
		        datatype: "json",
		        url: my_ajax_object.ajax_url,
		        data: data,
		        success: function(data){
		        	data = jQuery.parseJSON( data );
	            	if (data.result == 'success'){
	            		location.reload();
	            	}
		        }
		    });
		}
	});
	$('.sequence-continue-button').click(function(){
		var data = {
			'action': my_ajax_object.action,
			'operation': 'get-next-sequence-url',
			'current-post-id': $('article.decision_node').attr('id').replace('post-','')
		};
		$.ajax({
	        type: "post",
	        datatype: "json",
	        url: my_ajax_object.ajax_url,
	        data: data,
	        success: function(data){
	        	data = jQuery.parseJSON( data );
            	if (data.result == 'success'){
            		if (data.value == -2){
            			alert('Questionnaire Complete');
            		}
            		else{
            			window.location = data.value;
            		}
            	}
	        }
	    });
	});
	$('.cftp-dt-add-value a.action-add').click(function(){
		if ($('.cftp-dt-add-value input[type=number]').val() == ''){
			alert('Please, insert value');
		}
		else{
			var data = {
				'action': my_ajax_object.action,
				'operation': 'save-value',
				'current-post-id': $('article.decision_node').attr('id').replace('post-',''),
				'value': $('.cftp-dt-add-value input[type=number]').val()
			};
			$.ajax({
		        type: "post",
		        datatype: "json",
		        url: my_ajax_object.ajax_url,
		        data: data,
		        success: function(data){
		        	data = jQuery.parseJSON( data );
	            	if (data.result == 'success'){
	            		if (data.value == -2){
	            			alert('Questionnaire Complete');
	            		}
	            		else{
	            			window.location = data.value;
	            		}
	            	}
		        }
		    });
		}
	});
	$('.cftp-dt-add-value a.action-skip').click(function(){
		var data = {
				'action': my_ajax_object.action,
				'operation': 'skip-value',
				'current-post-id': $('article.decision_node').attr('id').replace('post-','')
			};
			$.ajax({
		        type: "post",
		        datatype: "json",
		        url: my_ajax_object.ajax_url,
		        data: data,
		        success: function(data){
		        	data = jQuery.parseJSON( data );
	            	if (data.result == 'success'){
	            		if (data.value == -2){
	            			alert('Questionnaire Complete');
	            		}
	            		else{
	            			window.location = data.value;
	            		}
	            	}
		        }
		    });
	});
	$('.cftp-dt-add-value a.action-ignore').click(function(){
		var data = {
				'action': my_ajax_object.action,
				'operation': 'ignore-value',
				'current-post-id': $('article.decision_node').attr('id').replace('post-','')
			};
			$.ajax({
		        type: "post",
		        datatype: "json",
		        url: my_ajax_object.ajax_url,
		        data: data,
		        success: function(data){
		        	data = jQuery.parseJSON( data );
	            	if (data.result == 'success'){
	            		if (data.value == -2){
	            			alert('Questionnaire Complete');
	            		}
	            		else{
	            			window.location = data.value;
	            		}
	            	}
		        }
		    });
	});
})