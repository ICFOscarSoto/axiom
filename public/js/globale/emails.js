$(document).ready(function() {
var flagEmails=true;
	function emailsRefresh(){
		$.getJSON( window.location.origin+"/api/emails/unreadlist", function( data ) {
		  var emailsCount=0;
		  var newEmails=0;
		  var lastId=0;
		  var majorId=0;
		  var timestamp=new Date() / 1000;
		  $("#email-header-list").text('');
		  $.each( data, function( key, val ) {
					var notificationTimeAgo=getTimeAgo(timestamp-val.timestamp);
		            email='  <li class="active">'+
		        					'			<a href="'+val.url+'">'+
		        					'				<span class="line">'+
		        					'				<strong>'+val.from+'</strong>- '+notificationTimeAgo+'</span>'+
		                  '				<span class="line desc small">'+val.subject+'</span>'+
		        					'		</a></li>';


					$("#email-header-list").prepend(email);
					if((val.timestamp>$("#email-last").val()) && ($("#email-last").val()!='')) newEmails++;
					emailsCount++;
					majorId=val.timestamp;
		  });
		  $("#email-last").val(majorId);
		  $("[id^='header-mail-count']").text(emailsCount);
		  if(newEmails>0) toastr.info('Tiene '+newEmails+' correo'+((newEmails > 1) ? 's' : '')+' nuevo'+((newEmails > 1) ? 's' : ''));
		}).always(function() {
			flagEmails=true;
		});
	}
/*	emailsRefresh();
	window.setInterval(function(){
		if(flagEmails){
			flagEmails=false;
			emailsRefresh();
		}
	}, 15000);
*/
	$('body').on('click', '.notification-view', function() {

	});

});
