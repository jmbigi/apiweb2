$(document).ready(function() { 
    // $('#user_status_check').bootstrapSwitch();
    // $("#user_status_check").bootstrapSwitch('state', true);

  
    // Listen for the 'init.bootstrapSwitch' event
    // $('#user_status_check').on('init.bootstrapSwitch', function(event, state) {
    //     alert('Bootstrap Switch initialized!');
    //     // You can add any initialization code here
    // });

    // $(document).on('switchChange.bootstrapSwitch','.user_status', function() { 
    //     var status = $(this).prop('checked') ? 1 : 0;
    //     var user_id = $(this).data('id');

    //     $.ajax({
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         type: 'POST',
    //         url: '/change_user_status',
    //         data: {
    //             'status': status,
    //             'id': user_id
    //         },
    //         success: function(data) {
    //             console.log(data.success);
    //         },
    //         error: function(xhr, textStatus, errorThrown) {
    //             // Handle error if the request fails
    //             console.error('Error:', errorThrown);
    //         }
    //     });
    // });

    
    $('.session_msg').fadeOut(3000);
   
    
    // $('.status_comment').hide();
    var statusErrElement = document.querySelector(".status_err");
    var default_status = $('.composer_status').val();
    if (default_status == 3 ) {
        $('.status_comment').show();
    } else {
        $('.status_comment').hide();
    }
    if (statusErrElement) {
        $('.status_comment').show();
    } 
    // else {
    //     $('.status_comment').hide();
    // }
    $(document).on('change', ".composer_status", function() {
        var status = $(this).val();
        if(status == 3){
           $('.status_comment').show();
        }else{
            $('.status_comment').hide();
        }
    });


    // change user status in user datatable
    $(document).on('change','#edit_user_status', function() { 
        var status = $(this).prop('checked') ? 1 : 0;
        var user_id = $(this).data('id');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: $(this).data('status'),
            data: {
                'status': status,
                'id': user_id
            },
            success: function(data) {
                console.log(data.success);
            },
            error: function(xhr, textStatus, errorThrown) {
                // Handle error if the request fails
                console.error('Error:', errorThrown);
            }
        });
    });

    //user datatable code
    var table = $('#user_datatable').DataTable({         
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'lfrtip',	        
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: $(this).data('url'),
            data: function(d) {
                d.page_length =$('.number_of_user').val();
               
              },
            dataType: 'json',
            method:'get',
        },
        columns: [
            { data: 'index', name: 'index', orderable: false, searchable: false },
            { "data": "name", "name":"name" },
            { "data": "register_date", "name":"created_at" },
            { "data": "last_updated", "name":"updated_at" },	  
            { "data": "status", "name":"status", orderable: false, searchable: false  },
            {data: 'action', name: 'action', orderable: false, searchable: false},          
        ],
        columnDefs: [
            {
                targets: 0, // The 'index' column
                data: null,
                render: function(data, type, row, meta) {
                    // Calculate the index based on the page and the row's position
                    var index = meta.row + meta.settings._iDisplayStart + 1;
    
                    // Manually construct the edit route URL
                    var main_route = $('#user_datatable').data('main_route');
                    var editRoute =  main_route +'user/edit/' + row.id;
    
                    return '<a href="' + editRoute + '">' + index + '</a>';
                },
            },
        ],
    });   
    table.on('draw.dt', function () {
        // $('input[name="my-checkbox"]').bootstrapSwitch();
        $('input.edit_user_status').bootstrapToggle({
            size: 'small',
            on: 'Active',
            off: 'Suspended',
            offstyle: 'danger'
        });
        
    }); 

    //composer datatable code
    var table = $('#composer_datatable').DataTable({         
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'lfrtip',	        
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: $(this).data('url'),
            data: function(d) {
                d.page_length =$('.number_of_user').val();
               
              },
            dataType: 'json',
            method:'get',
        },
        columns: [
            { data: 'index', name: 'index', orderable: false, searchable: false },
            { "data": "name", "name":"name" },
            { "data": "register_date", "name":"created_at", orderable: false, searchable: false },
            { "data": "last_updated", "name":"updated_at", orderable: false, searchable: false },	            
        ],
        columnDefs: [
            {
                targets: 0, // The 'index' column
                data: null,
                render: function(data, type, row, meta) {
                    // Calculate the index based on the page and the row's position
                    var index = meta.row + meta.settings._iDisplayStart + 1;
    
                    // Manually construct the edit route URL
                    // var editRoute = '/user/edit/' + row.id;
                    var main_route = $('#composer_datatable').data('main_route');
                    var editRoute =  main_route +'composer/show/' + row.id;
    
                    return '<a href="' + editRoute + '">' + index + '</a>';
                    // return '<a href="#">' + index + '</a>';
                },
            },
        ],
    });   


    //composer request datatable code
    var table = $('#composer_request_datatable').DataTable({         
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'lfrtip',	        
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: $(this).data('url'),
            data: function(d) {
                d.page_length =$('.number_of_user').val();
               
              },
            dataType: 'json',
            method:'get',
        },
        columns: [
            { data: 'composer_request_id', name: 'composer_request_id',orderable: false, searchable: false},
            { data: 'user_id', name: 'user_id'},
            { "data": "name", "name":"name" },
            { "data": "last_name", "name":"last_name" },
            { "data": "requested_date", "name":"requested_date"},
            { "data": "modified_date", "name":"modified_date"},
            // { "data": "composer_req_status", "name":"composer_req_status", orderable: false, searchable: false },
            // { "data": "composer_status", "name":"composer_status", orderable: false, searchable: false },
            // { "data": "composer_status_id", "name":"composer_status_id" },
            // { "data": "last_updated", "name":"updated_at" },	  
            { "data": "composer_req_status", "name":"composer_req_status" },	  
            { "data": "composer_status", "name":"composer_status" },	  
            {data: 'action', name: 'action', orderable: false, searchable: false},             
        ],
        createdRow: function (row, data, dataIndex) {
            // Add a class to the <tr> element here
            if(data.request_status_id == 1){
                $(row).addClass('pending_status');                
            }else if(data.request_status_id == 2){
                $(row).addClass('custom-class');
            }else if(data.request_status_id == 3){
                $(row).addClass('active_status');
            }
           
        },

    });   
    table.on('draw.dt', function () {
        // $('input[name="my-checkbox"]').bootstrapSwitch();
        $('input.edit_composer_status').bootstrapToggle({
            size: 'small',
            on: 'Active',
            off: 'Suspended',
            offstyle: 'danger'
        });
        
    }); 

    // Subscription plan listing

    var table = $('#subscription_datatable').DataTable({         
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'lfrtip',	        
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: $(this).data('url'),
            data: function(d) {
                d.page_length =$('.number_of_user').val();
               
              },
            dataType: 'json',
            method:'get',
        },
        columns: [
            { data: 'id', name: 'id',orderable: false, searchable: false},
            { "data": "name", "name":"name" },
            { "data": "description", "name":"description" },
            { "data": "price", "name":"price"},
            { "data": "no_of_subscribed_user", "name":"no_of_subscribed_user"},
            { "data": "start_date", "name":"start_date",orderable: false, searchable: false},
            { "data": "end_date", "name":"end_date",orderable: false, searchable: false},
            { "data": "status", "name":"status",orderable: false, searchable: false},	  
            {data: 'action', name: 'action', orderable: false, searchable: false},             
        ],
        columnDefs: [
            {
                targets: 0, 
                data: null,
                render: function(data, type, row, meta) {
                    var index = meta.row + meta.settings._iDisplayStart + 1;
                    var main_route = $('#subscription_datatable').data('main_route');
                    var editRoute =  main_route +'subscription-plan/edit/' + row.id;
                    return '<a href="' + editRoute + '">' + index + '</a>';
                },
            },
        ],
        // createdRow: function (row, data, dataIndex) {
        //     // Add a class to the <tr> element here
        //     if(data.request_status_id == 1){
        //         $(row).addClass('pending_status');                
        //     }else if(data.request_status_id == 2){
        //         $(row).addClass('custom-class');
        //     }else if(data.request_status_id == 3){
        //         $(row).addClass('active_status');
        //     }
           
        // },

    });   
    table.on('draw.dt', function () {
        // $('input[name="my-checkbox"]').bootstrapSwitch();
        $('input.edit_subscription_status').bootstrapToggle({
            size: 'small',
            on: 'Active',
            off: 'Suspended',
            offstyle: 'danger'
        });
        
    }); 

    //subscription plan status change
    $(document).on('change','#edit_subscription_status', function() { 
        var status = $(this).prop('checked') ? 1 : 0;
        var subscription_id = $(this).data('id');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: $(this).data('status'),
            data: {
                'status': status,
                'id': subscription_id
            },
            success: function(data) {
                console.log(data.success);
            },
            error: function(xhr, textStatus, errorThrown) {
                // Handle error if the request fails
                console.error('Error:', errorThrown);
            }
        });
    });

     //instrument datatable
     var table = $('#instrument_datatable').DataTable({         
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'lfrtip',	        
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: $(this).data('url'),
            data: function(d) {
                d.page_length =$('.number_of_user').val();
               
              },
            dataType: 'json',
            method:'get',
        },
        columns: [
            { data: 'index', name: 'index', orderable: false, searchable: false },
            { "data": "name", "name":"name" },
            { "data": "register_date", "name":"created_at" },
            { "data": "last_updated", "name":"updated_at" },	  
            { "data": "status", "name":"status", orderable: false, searchable: false  },
            {data: 'action', name: 'action', orderable: false, searchable: false},          
        ],
        columnDefs: [
            {
                targets: 0, // The 'index' column
                data: null,
                render: function(data, type, row, meta) {
                    // Calculate the index based on the page and the row's position
                    var index = meta.row + meta.settings._iDisplayStart + 1;
    
                    // Manually construct the edit route URL
                    var main_route = $('#instrument_datatable').data('main_route');
                    var editRoute =  main_route +'instrument/edit/' + row.id;
    
                    return '<a href="' + editRoute + '">' + index + '</a>';
                },
            },
        ],
    });   
    table.on('draw.dt', function () {
        // $('input[name="my-checkbox"]').bootstrapSwitch();
        $('input.edit_instrument_status').bootstrapToggle({
            size: 'small',
            on: 'Active',
            off: 'Suspended',
            offstyle: 'danger'
        });
        
    }); 

     // change instrument status
     $(document).on('change','#edit_instrument_status', function() { 
        var status = $(this).prop('checked') ? 1 : 0;
        var instrument_id = $(this).data('id');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: $(this).data('status'),
            data: {
                'status': status,
                'id': instrument_id
            },
            success: function(data) {
                console.log(data.success);
            },
            error: function(xhr, textStatus, errorThrown) {
                // Handle error if the request fails
                console.error('Error:', errorThrown);
            }
        });
    });

    // delete code for style music
    $(document).on('click', ".delete_instrument", function() {
		Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, delete it!'
		}).then((result) => {
			if (result.value) { 
		  		var instrument_id = $(this).data('id'); 
                var main_route = $('#instrument_datatable').data('main_route');
		        $.ajax({ 
		        	headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		          	type: "DELETE",
		          	url: main_route+'delete-instrument/'+instrument_id, 
		          	data: {'id': instrument_id},   
		          	success: function(data)
		          	{
		            	console.log(data.success)
		          	}
		        });
			    Swal.fire({
				  title: 'Deleted',
				  text: "Your record has been deleted.",
				  type: 'success'
				}).then(okay => {
						if (okay) {
					    	$('#instrument_datatable').DataTable().ajax.reload();
					  	}
			  		});
			}
		});
    });


    // style music datatable
    var table = $('#style_music_datatable').DataTable({         
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'lfrtip',	        
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: $(this).data('url'),
            data: function(d) {
                d.page_length =$('.number_of_user').val();
               
              },
            dataType: 'json',
            method:'get',
        },
        columns: [
            { data: 'index', name: 'index', orderable: false, searchable: false },
            { "data": "name", "name":"name" },
            { "data": "register_date", "name":"created_at" },
            { "data": "last_updated", "name":"updated_at" },	  
            { "data": "status", "name":"status", orderable: false, searchable: false  },
            {data: 'action', name: 'action', orderable: false, searchable: false},          
        ],
        columnDefs: [
            {
                targets: 0, // The 'index' column
                data: null,
                render: function(data, type, row, meta) {
                    // Calculate the index based on the page and the row's position
                    var index = meta.row + meta.settings._iDisplayStart + 1;
    
                    // Manually construct the edit route URL
                    var main_route = $('#style_music_datatable').data('main_route');
                    var editRoute =  main_route +'style-music/edit/' + row.id;
    
                    return '<a href="' + editRoute + '">' + index + '</a>';
                },
            },
        ],
    });   
    table.on('draw.dt', function () {
        // $('input[name="my-checkbox"]').bootstrapSwitch();
        $('input.edit_style_status').bootstrapToggle({
            size: 'small',
            on: 'Active',
            off: 'Suspended',
            offstyle: 'danger'
        });
        
    }); 

    // change style music status
    $(document).on('change','#edit_style_status', function() { 
        var status = $(this).prop('checked') ? 1 : 0;
        var style_id = $(this).data('id');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: $(this).data('status'),
            data: {
                'status': status,
                'id': style_id
            },
            success: function(data) {
                console.log(data.success);
            },
            error: function(xhr, textStatus, errorThrown) {
                // Handle error if the request fails
                console.error('Error:', errorThrown);
            }
        });
    });

    // delete code for style music
    $(document).on('click', ".delete_style", function() {
		Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, delete it!'
		}).then((result) => {
			if (result.value) { 
		  		var style_id = $(this).data('id'); 
                var main_route = $('#style_music_datatable').data('main_route');
		        $.ajax({ 
		        	headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		          	type: "DELETE",
		          	url: main_route+'delete-style-music/'+style_id, 
		          	data: {'id': style_id},   
		          	success: function(data)
		          	{
		            	console.log(data.success)
		          	}
		        });
			    Swal.fire({
				  title: 'Deleted',
				  text: "Your record has been deleted.",
				  type: 'success'
				}).then(okay => {
						if (okay) {
					    	$('#style_music_datatable').DataTable().ajax.reload();
					  	}
			  		});
			}
		});
    });

     // family instrument datatable
     var table = $('#family_instrument_datatable').DataTable({         
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'lfrtip',	        
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: $(this).data('url'),
            data: function(d) {
                d.page_length =$('.number_of_instrument').val();
               
              },
            dataType: 'json',
            method:'get',
        },
        columns: [
            { data: 'index', name: 'index', orderable: false, searchable: false },
            { "data": "name", "name":"name" },
            { "data": "register_date", "name":"created_at" },
            { "data": "last_updated", "name":"updated_at" },	  
            { "data": "status", "name":"status", orderable: false, searchable: false  },
            {data: 'action', name: 'action', orderable: false, searchable: false},          
        ],
        columnDefs: [
            {
                targets: 0, // The 'index' column
                data: null,
                render: function(data, type, row, meta) {
                    // Calculate the index based on the page and the row's position
                    var index = meta.row + meta.settings._iDisplayStart + 1;
    
                    // Manually construct the edit route URL
                    var main_route = $('#family_instrument_datatable').data('main_route');
                    var editRoute =  main_route +'family-instrument/edit/' + row.id;
    
                    return '<a href="' + editRoute + '">' + index + '</a>';
                },
            },
        ],
    });   
    table.on('draw.dt', function () {
        // $('input[name="my-checkbox"]').bootstrapSwitch();
        $('input.edit_family_instrument_status').bootstrapToggle({
            size: 'small',
            on: 'Active',
            off: 'Suspended',
            offstyle: 'danger'
        });
        
    }); 

     // change family instrument status
     $(document).on('change','#edit_family_instrument_status', function() { 
        var status = $(this).prop('checked') ? 1 : 0;
        var family_instrument_id = $(this).data('id');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: $(this).data('status'),
            data: {
                'status': status,
                'id': family_instrument_id
            },
            success: function(data) {
                console.log(data.success);
            },
            error: function(xhr, textStatus, errorThrown) {
                // Handle error if the request fails
                console.error('Error:', errorThrown);
            }
        });
    });

    // delete family instrument record
    $(document).on('click', ".delete_family_instrument", function() {
		Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, delete it!'
		}).then((result) => {
			if (result.value) { 
		  		var family_instrument_id = $(this).data('id'); 
                var main_route = $('#family_instrument_datatable').data('main_route');
		        $.ajax({ 
		        	headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		          	type: "DELETE",
		          	url: main_route+'delete-family-instrument/'+family_instrument_id, 
		          	data: {'id': family_instrument_id},   
		          	success: function(data)
		          	{
		            	console.log(data.success)
		          	}
		        });
			    Swal.fire({
				  title: 'Deleted',
				  text: "Your record has been deleted.",
				  type: 'success'
				}).then(okay => {
						if (okay) {
					    	$('#family_instrument_datatable').DataTable().ajax.reload();
					  	}
			  		});
			}
		});
    });

    //music score data table
    var table = $('#musicscore_datatable').DataTable({         
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'lfrtip',	        
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: $(this).data('url'),
            data: function(d) {
                d.page_length =$('.number_of_user').val();
               
              },
            dataType: 'json',
            method:'get',
        },
        columns: [
            { data: 'index', name: 'index', orderable: false, searchable: false },
            { "data": "name", "name":"name" },
            { "data": "composer", "name":"composer" },
            { "data": "publish_date", "name":"publish_date" },	  
            { "data": "total_view", "name":"total_view" },	  
            { "data": "status", "name":"status" },	  
            {data: 'action', name: 'action', orderable: false, searchable: false},          
        ],
        columnDefs: [
            {
                targets: 0, // The 'index' column
                data: null,
                render: function(data, type, row, meta) {
                    // Calculate the index based on the page and the row's position
                    var index = meta.row + meta.settings._iDisplayStart + 1;
    
                    // Manually construct the edit route URL
                    var main_route = $('#musicscore_datatable').data('main_route');
                    var editRoute =  main_route +'music-score/show/' + row.id;
    
                    return '<a href="' + editRoute + '">' + index + '</a>';
                },
            },
        ],
    });   
    table.on('draw.dt', function () {
        // $('input[name="my-checkbox"]').bootstrapSwitch();
        $('input.edit_music_score_status').bootstrapToggle({
            size: 'small',
            on: 'Active',
            off: 'Suspended',
            offstyle: 'danger'
        });
        
    }); 


    //subscribed user datatable code
    var table = $('#subscribed_user_datatable').DataTable({         
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'lrtip',	        
        ajax: {
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: $(this).data('url'),
            data: function(d) {
                d.page_length =$('.number_of_user').val();
               
              },
            dataType: 'json',
            method:'get',
        },
        columns: [
            { data: 'index', name: 'index', orderable: false, searchable: false },
            { "data": "username", "name":"username" },
            { "data": "planname", "name":"planname" },
            { "data": "register_date", "name":"register_date",orderable: false, searchable: false  },	          
            { "data": "plan_end_date", "name":"plan_end_date",orderable: false, searchable: false  },	          
        ],       
    });        
    //change music score status
    $(document).on('change','#edit_music_score_status', function() { 
        var status = $(this).prop('checked') ? 1 : 0;
        var music_score_id = $(this).data('id');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: $(this).data('status'),
            data: {
                'status': status,
                'id': music_score_id
            },
            success: function(data) {
                console.log(data.success);
            },
            error: function(xhr, textStatus, errorThrown) {
                // Handle error if the request fails
                console.error('Error:', errorThrown);
            }
        });
    });

    //Delete music score
    $(document).on('click', ".delete_music_score", function() {
		Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, delete it!'
		}).then((result) => {
			if (result.value) { 
		  		var music_score_id = $(this).data('id'); 
                var main_route = $('#musicscore_datatable').data('main_route');
		        $.ajax({ 
		        	headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		          	type: "DELETE",
		          	url: main_route+'delete-music-score/'+music_score_id, 
		          	data: {'id': music_score_id},   
		          	success: function(data)
		          	{
		            	console.log(data.success)
		          	}
		        });
			    Swal.fire({
				  title: 'Deleted',
				  text: "Your record has been deleted.",
				  type: 'success'
				}).then(okay => {
						if (okay) {
					    	$('#musicscore_datatable').DataTable().ajax.reload();
					  	}
			  		});
			}
		});
    });


    //Delete user code
    $(document).on('click', ".delete_user", function() {
		Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, delete it!'
		}).then((result) => {
			if (result.value) { 
		  		var user_id = $(this).data('id'); 
                var main_route = $('#user_datatable').data('main_route');
		        $.ajax({ 
		        	headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		          	type: "DELETE",
		          	url: main_route+'delete_user/'+user_id, 
		          	data: {'id': user_id},   
		          	success: function(data)
		          	{
		            	console.log(data.success)
		          	}
		        });
			    Swal.fire({
				  title: 'Deleted',
				  text: "Your record has been deleted.",
				  type: 'success'
				}).then(okay => {
						if (okay) {
					    	$('#user_datatable').DataTable().ajax.reload();
					  	}
			  		});
			}
		});
    });
    
    
     // change composer status in composer request datatable
    //  $(document).on('change','#edit_composer_status', function() { 
    //     var status = $(this).prop('checked') ? 1 : 0;
    //     var composer_status = $(this).data('id');

    //     $.ajax({
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         type: 'POST',
    //         url: '/change_composer_status',
    //         data: {
    //             'status': status,
    //             'id': composer_status
    //         },
    //         success: function(data) {
    //             console.log(data.success);
    //         },
    //         error: function(xhr, textStatus, errorThrown) {
    //             // Handle error if the request fails
    //             console.error('Error:', errorThrown);
    //         }
    //     });
    // });

    // Delete composer request
    $(document).on('click', ".delete_request", function() {
		Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, delete it!'
		}).then((result) => {
			if (result.value) { 
		  		var request_id = $(this).data('id'); 
                var main_route = $('#composer_request_datatable').data('main_route');
		        $.ajax({ 
		        	headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		          	type: "DELETE",
		          	url: main_route+'delete-composer-request/'+request_id, 
		          	data: {'id': request_id},   
		          	success: function(data)
		          	{
		            	console.log(data.success)
		          	}
		        });
			    Swal.fire({
				  title: 'Deleted',
				  text: "Your record has been deleted.",
				  type: 'success'
				}).then(okay => {
						if (okay) {
					    	$('#composer_request_datatable').DataTable().ajax.reload();
					  	}
			  	    });
			}
		});
    }); 
    
    $(document).on('click', ".delete_plan   ", function() {
		Swal.fire({
		  title: 'Are you sure?',
		  text: "You won't be able to revert this!",
		  type: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: 'Yes, delete it!'
		}).then((result) => {
			if (result.value) { 
		  		var request_id = $(this).data('id'); 
                var main_route = $('#subscription_datatable').data('main_route');
		        $.ajax({ 
		        	headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		          	type: "DELETE",
		          	url: main_route+'delete-subscription-plan/'+request_id, 
		          	data: {'id': request_id},   
		          	success: function(data)
		          	{   
                        var response_msg;
                        var response_title;
                        if(data.error){
                            response_msg = data.error;
                            response_title = "Delete Failed"
                        }else if(data.success){
                            response_msg = data.success;
                            response_title = "Deleted"

                        }
                        Swal.fire({
                            title: response_title,
                            text: response_msg,
                            type: (data.success ? 'success' : 'error')
                          }).then(okay => {
                                  if (okay) {
                                      $('#subscription_datatable').DataTable().ajax.reload();
                                    }
                                });
		            	console.log(data.success)
		            	console.log(data.error)
		          	}
		        });
			    // Swal.fire({
				//   title: 'Deleted',
				//   text: response_msg,
				//   type: 'success'
				// }).then(okay => {
				// 		if (okay) {
				// 	    	$('#subscription_datatable').DataTable().ajax.reload();
				// 	  	}
			  	//     });
			}
		});
    }); 

    // set comment on denied request
    // $(document).on('click', "#denie_req", function() {
	// 	Swal.fire({
    //         title: 'Why you denied request',
    //         input: 'text',
    //         inputAttributes: {
    //             autocapitalize: 'off'
    //         },
    //         showCancelButton: true,
    //         confirmButtonText: 'Deny',
    //         showLoaderOnConfirm: true,
    //         preConfirm: (reason) => {
    //             // Capture the input value and use it in the AJAX request
    //             return $.ajax({
    //                 type: 'POST',
    //                 url: 'store_denied_reason',
    //                 data: {
    //                     reason: reason // Pass the input value as 'reason'
    //                 },
    //                 success: function(data) {
    //                     // Handle the success response
    //                     Swal.fire('Response', data.message, 'success');
    //                 },                    
    //             });
    //         },
    //         allowOutsideClick: () => !Swal.isLoading()
    //     }).then((result) => {
    //         // Handle the result if needed
    //     });
    // });      

});



"use strict";

// Class definition
var KTWidgets = function () {
    // Private properties

    let months = JSON.parse(document.getElementById('stat_months').value);
    let dates = JSON.parse(document.getElementById('stat_dates').value);

    let dailyMonths = JSON.parse(document.getElementById('stat_daily_months').value);
    let dailyDates = JSON.parse(document.getElementById('stat_daily_dates').value);

    function calcMaxData(data) {
        let maxData = 1; // Cambiado a "maxData" para seguir la convención        
        if (data.length > 0) {
            // Obtener el valor máximo de "data"
            maxData = Math.max.apply(null, data);   
            // Redondear el valor máximo hacia arriba basado en su rango
            if (maxData > 1000) {
                maxData = Math.ceil(maxData / 1000) * 1000; // Redondear al siguiente múltiplo de 1000
            } else if (maxData > 500) {
                maxData = Math.ceil(maxData / 500) * 500; // Redondear al siguiente múltiplo de 100
            } else if (maxData > 200) {
                maxData = Math.ceil(maxData / 200) * 200; // Redondear al siguiente múltiplo de 100
            } else if (maxData > 100) {
                maxData = Math.ceil(maxData / 100) * 100; // Redondear al siguiente múltiplo de 100
            } else if (maxData > 50) {
                maxData = Math.ceil(maxData / 50) * 50; // Redondear al siguiente múltiplo de 100
            } else if (maxData > 20) {
                maxData = Math.ceil(maxData / 20) * 20; // Redondear al siguiente múltiplo de 100
            } else {
                maxData = Math.ceil(maxData / 10) * 10; // Redondear al siguiente múltiplo de 10 para valores menores o iguales a 100
            }
        }
        return maxData;
    }

    function graphOptions(name, data, categories, maxData, colorBase, colorLight) {
        let options = {
            series: [{
                name: name,
                data: data
            }],
            chart: {
                type: 'area',
                height: 250,
                toolbar: {
                    show: false
                },
            },
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: 'solid',
                opacity: 1
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3,
                colors: [colorBase]
            },
            xaxis: {
                categories: categories,
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: KTApp.getSettings()['colors']['gray']['gray-500'],
                        fontSize: '12px',
                        fontFamily: KTApp.getSettings()['font-family']
                    }
                },
                crosshairs: {
                    show: false,
                    position: 'front',
                    stroke: {
                        color: KTApp.getSettings()['colors']['gray']['gray-300'],
                        width: 1,
                        dashArray: 3
                    }
                },
                tooltip: {
                    enabled: true,
                    formatter: undefined,
                    offsetY: 0,
                    style: {
                        fontSize: '12px',
                        fontFamily: KTApp.getSettings()['font-family']
                    }
                }
            },
            yaxis: {
                min: 0,
                max: maxData,
                tickAmount: 5,
                labels: {
                    style: {
                        colors: KTApp.getSettings()['colors']['gray']['gray-500'],
                        fontSize: '12px',
                        fontFamily: KTApp.getSettings()['font-family']
                    }
                }
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px',
                    fontFamily: KTApp.getSettings()['font-family']
                },
                y: {
                    formatter: function (val) {
                        return val
                    }
                }
            },
            colors: [colorLight],
            markers: {
                size: 0,
                colors: [colorLight],
                strokeColor: [colorBase],
                strokeWidth: 3,
                strokeOpacity: 1,
                strokeDashArray: 0,
                fillOpacity: 1,
                discrete: [],
                shape: "circle",
                radius: 2,
                offsetX: 0,
                offsetY: 0,
                onClick: undefined,
                onDblClick: undefined,
                showNullDataPoints: true,
                hover: {
                    size: 8,
                    sizeOffset: 0
                }
            },
            fill: {
                gradient: {
                    enabled: false,
                    opacityFrom: 1,
                    opacityTo: 1
                }
            },
            grid: {
                borderColor: 'var(--tw-gray-200)',
                strokeDashArray: 5,
                clipMarkers: false,
                yaxis: {
                    lines: {
                        show: true
                    }
                },
                xaxis: {
                    lines: {
                        show: false
                    }
                },
            },
        };
        return options;
    }

    // General Controls
    var _initDaterangepicker = function () {
        if ($('#kt_dashboard_daterangepicker').length == 0) {
            return;
        }

        var picker = $('#kt_dashboard_daterangepicker');
        var start = moment();
        var end = moment();

        function cb(start, end, label) {
            var title = '';
            var range = '';

            if ((end - start) < 100 || label == 'Today') {
                title = 'Today:';
                range = start.format('MMM D');
            } else if (label == 'Yesterday') {
                title = 'Yesterday:';
                range = start.format('MMM D');
            } else {
                range = start.format('MMM D') + ' - ' + end.format('MMM D');
            }

            $('#kt_dashboard_daterangepicker_date').html(range);
            $('#kt_dashboard_daterangepicker_title').html(title);
        }

        picker.daterangepicker({
            direction: KTUtil.isRTL(),
            startDate: start,
            endDate: end,
            opens: 'left',
            applyClass: 'btn-primary',
            cancelClass: 'btn-light-primary',
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(start, end, '');
    }

    function makeStatGraph(grahElementId, dataElementId, categories, name, color) {
        let element = document.getElementById(grahElementId);
        if (!element) {
            return;
        }
        let data = JSON.parse(document.getElementById(dataElementId).value);
        let maxData = calcMaxData(data);

        let colorBase = KTApp.getSettings()['colors']['theme']['base'][color];
        let colorLight = KTApp.getSettings()['colors']['theme']['light'][color];
        let options = graphOptions(name, data, categories, maxData, colorBase, colorLight);

        let chart = new ApexCharts(element, options);
        chart.render();
    }

    // Stats widgets
    var _initStatsWidget7 = function () {
        // Composer Request
        let color = 'success';
        makeStatGraph('kt_stats_widget_7_chart', 'monthly_request_counts', months, 'Requests', color);
    }

    var _initStatsWidget12 = function () {
        // Registered User
        let element = document.getElementById('kt_stats_widget_12_chart');
        let color = KTUtil.hasAttr(element, 'data-color') ? KTUtil.attr(element, 'data-color') : 'primary';
        makeStatGraph('kt_stats_widget_12_chart', 'monthly_user_counts', months, 'Users', color);
    }

    var _initStatsWidget13 = function () {
        // Registered Composers
        let color = 'success';
        makeStatGraph('kt_stats_widget_13_chart', 'monthly_composer_counts', months, 'Composers', color);
    }

    var _initStatsWidget14 = function () {
        // Registered Music Score
        let element = document.getElementById('kt_stats_widget_14_chart');
        let color = KTUtil.hasAttr(element, 'data-color') ? KTUtil.attr(element, 'data-color') : 'primary';
        makeStatGraph('kt_stats_widget_14_chart', 'monthly_music_score_counts', months, 'Music scores', color);
    }
    
    var _initStatsWidgetUniqueScoreView = function () {
        // Unique Score View
        let color = 'warning';
        makeStatGraph('kt_stats_widget_unique_score_view_chart', 'weekly_unique_score_view_counts', months, 'Views', color);
    }
        
    var _initStatsWidgetActiveUser = function () {
        // Unique Score View
        let color = 'warning';
        makeStatGraph('kt_stats_widget_active_user_chart', 'weekly_active_user_counts', months, 'Users', color);
    }

    var _initStatsWidgetDailyScoreView = function () {
        // Daily Score View
        let color = 'danger';
        makeStatGraph('kt_stats_widget_daily_view_chart', 'daily_score_view_counts', dailyMonths, 'Views', color);
    }

    var _initStatsWidgetDailyActiveUser = function () {
        // Daily Score View
        let color = 'danger';
        makeStatGraph('kt_stats_widget_daily_active_user_chart', 'daily_active_user_counts', dailyMonths, 'Users', color);
    }

    // Public methods
    return {
        init: function () {
            // General Controls
            _initDaterangepicker();

            // Stats Widgets
            _initStatsWidget7();
            _initStatsWidget12();
            _initStatsWidget13();
            _initStatsWidget14();
            _initStatsWidgetUniqueScoreView();
            _initStatsWidgetActiveUser();
            _initStatsWidgetDailyScoreView();
            _initStatsWidgetDailyActiveUser();
        }
    }
}();

jQuery(document).ready(function () {
    KTWidgets.init();
});
