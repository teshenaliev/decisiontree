jQuery(function($){
	$('.select-user-button').click(function(){
		if ( $('.user-list').val() != '' ){
			window.location = '?select_user=true&user_id=' + $('.user-list').val();
		}
	});

	$('.sign-out-client-button').click(function(){
			window.location = $(this).attr('redirect-url') + ($(this).attr('redirect-url').indexOf('?')>=0?'&':'?') + 'sign_out_client=true';
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
			bootbox.alert('Please, select at least one category');
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
            			bootbox.alert('Questionnaire Complete');
            		}
            		else{
            			window.location = data.value;
            		}
            	}
	        }
	    });
	});
	//tree view add action
	$('.cftp-dt-add-value a.action-add').click(function(){
		if ($('.cftp-dt-add-value input[type=number]').val() == ''){
			bootbox.alert('Please, insert value');
		}
		else{
			var data = {
				'action': my_ajax_object.action,
				'operation': 'save-value',
				'current-post-id': $('article.decision_node').attr('id').replace('post-',''),
				'value': $('.cftp-dt-add-value input[type=number]').val(),
				'additional_note': $('.cftp-dt-add-value input.additional-note').val()
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
	            			bootbox.alert('Questionnaire Complete');
	            		}
	            		else if (data.value !='false'){
	            			window.location = data.value;
	            		}
	            		else{
	            			bootbox.alert('Value saved');
	            		}
	            	}
		        }
		    });
		}
	});
	//list view add action
	$('.list-view-action-column a.action-add').click(function(){
		var parentTr = $(this).parents('tr');
		if (parentTr.find('.question_value').val()==''){
			bootbox.alert("Please insert value", function() {
			});
		}
		else{
			var data = {
				'action': my_ajax_object.action,
				'operation': 'save-value',
				'current-post-id': parentTr.find('.question_id').val(),
				'value': parentTr.find('.question_value').val(),
				'additional_note': parentTr.find('.question_additional_note').val()
			};
			$.ajax({
		        type: "post",
		        datatype: "json",
		        url: my_ajax_object.ajax_url,
		        data: data,
		        success: function(data){
		        	data = jQuery.parseJSON( data );
		        	if (data.result == 'success'){
						bootbox.alert("Value saved", function() {
							
						});
		        	}
		        	else{
						bootbox.alert("Value not saved", function() {
						});
		        	}
	            	window.setTimeout(function(){
					    bootbox.hideAll();
					}, 2000);
		        }
		    });
		}
	});
	//list view ignore action
	$('.list-view-action-column a.action-ignore').click(function(){
	    bootbox.confirm("Ignoring this question will remove this question. Do you really want to continue?", function(result) {
		  	if (result == true){
		  		var parentTr = $(this).parents('tr');
				var data = {
					'action': my_ajax_object.action,
					'operation': 'ignore-value',
					'current-post-id': parentTr.find('.question_id').val()
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
		            			bootbox.alert('Questionnaire Complete');
		            		}
		            		else{
				        		$( "#dialog" ).dialog({
					              	modal: true,
					             	title: 'Answer ignored',
					             	width: 400,
					            	buttons : {
					                Ok: function() {
					                	parentTr.fadeOut('slow',
					                		function(){
					                			parentTr.remove();
					                		});
					                    $(this).dialog("close"); //closing on Ok click
					                }
					            }
					        	});
		            		}
		            	}
			        }
			    });
		  	}
		});
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
	            			bootbox.alert('Questionnaire Complete');
	            		}
	            		else{
	            			window.location = data.value;
	            		}
	            	}
		        }
		    });
	});
	$('.cftp-dt-add-value a.action-ignore').click(function(){
		bootbox.confirm("Ignoring this question will remove this question. Do you really want to continue?", function(result) {
			if (result == true){
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
		            			bootbox.alert('Questionnaire Complete');
		            		}
		            		else{
		            			window.location = data.value;
		            		}
		            	}
			        }
			    });
			}
		});
	});
})