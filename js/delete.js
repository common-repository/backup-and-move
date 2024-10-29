jQuery(document).ready( function($) {
	$("a[href=#delete_confirm]").click(function (event) {
		var answer = confirm("Are you sure. You want to delete "+$(this).attr("title")+" backup");
		if (answer){
			$.ajax({
				type: "POST",
				data: "backup_and_move_delid="+$(this).attr("title")+"&action=backup_and_move_check",
				url: ajaxurl,
				success: function(data){
					if(data=="valid")
					{
						location.reload();
					}
					else
					{
						alert("Error while deleting backup");
						location.reload();
					}	
				}
			});
		}
		event.preventDefault(); 
	});
	function create_new(bname,mstatus)
	{
		$.ajax({
			type: "POST",
			data: "backup_and_move_name="+bname+"&backup_and_move_mail="+mstatus+"&action=backup_and_move_create_new",
			url: ajaxurl,
			success: function(data){
				document.getElementById('formStatus').innerHTML="Backup created successfully ";
			}
		});
	}
	function delete_old(bname,mstatus)
	{
		
		$.ajax({
			type: "POST",
			data: "backup_and_move_new="+bname+"&action=backup_and_move_overwrite",
			url: ajaxurl,
			success: function(data){
				
				create_new(bname,mstatus);
			}
		});
	}

	$("#backup-and-move-form").submit(function (event) {
		event.preventDefault();   
		if($("input[name=backup_name]").val().length>0)
			// length > 0 or not
		{	
			if (/^[0-9A-Za-z]+$/.test($("input[name=backup_name]").val()))
			{
				// alphanumeric or not
				document.getElementById('formStatus').innerHTML="Please wait. It may take a few minutes. processing ...";
				$.ajax({
					type: "POST",
					data: "backup_and_move_new="+$("input[name=backup_name]").val()+"&action=backup_and_move_overwrite_check",
					url: ajaxurl,
					success: function(data)
					{
						if(data=="exists")
						{
							var overwrite = confirm("Are you sure. You want to overwrite previous backup with name "+$("input[name=backup_name]").val());
							if (overwrite)
							{
								delete_old($("input[name=backup_name]").val(),$("#mail_me").is(':checked'));
							}
							else
							{
								document.getElementById('formStatus').innerHTML="process terminated.";
							}
						}
						else
						{
							create_new($("input[name=backup_name]").val(),$("#mail_me").is(':checked'));
						}

					}
				});

			}
			else
			{
				document.getElementById('formStatus').innerHTML="Backup name shoud consist of only " +
				"alpanumeric characters.";	
			}
		}
		else
		{
			document.getElementById('formStatus').innerHTML="Please enter a backup name.";	
		}
	});
});