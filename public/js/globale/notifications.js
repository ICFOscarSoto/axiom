$(document).ready(function() {
var flagNotifications=true;
	function notificationsRefresh(){
		$.getJSON( window.location.origin+"/es/admin/api/notifications/unreadlist", function( data ) {
		  var notificationsCount=0;
		  var newNotifications=0;
		  var lastId=0;
		  var majorId=0;
		  var timestamp=new Date() / 1000;
		  $("#notifications-list").text('');
		  $.each( data, function( key, val ) {
			 var notificationTimeAgo=getTimeAgo(timestamp-val.timestamp);
			notification='<li class="unread notification-danger">' 				+
							 '<a href="#">' 												+
							 '<button type="button" attr-id="'+val.id+'" id="notification-view-'+val.id+'" class="btn btn-danger notification-view pull-right"><i class="entypo-cancel"></i></button>	'	+
							 '			<span class="line">'								+
							 '				<strong>'+val.text+'</strong>'			+
							 '			</span>'											+
							 '			<span class="line small">'						+
							 notificationTimeAgo										+
							 '			</span>'											+
							 '	</a>'															+
							 '</li>';
			$("#notifications-list").prepend(notification);
			if((val.id>$("#notification-last").val()) && ($("#notification-last").val()!='')) newNotifications++;
			notificationsCount++;
			majorId=val.id;
		  });
		  $("#notification-last").val(majorId);
		  $("[id^='notifications-count']").text(notificationsCount);
		  if(newNotifications>0) toastr.info('Tiene '+newNotifications+' notificacion'+((newNotifications > 1) ? 'es' : '')+' nueva'+((newNotifications > 1) ? 's' : ''));
		}).always(function() {
			flagNotifications=true;
		});
	}
	notificationsRefresh();
	window.setInterval(function(){
		if(flagNotifications){
			flagNotifications=false;
			notificationsRefresh();
		}
	}, 5000);

	$('body').on('click', '.notification-view', function() {
		var item = $(this).parent().parent().remove();
		$.getJSON(window.location.origin+"/es/admin/api/notifications/"+$(this).attr('attr-id')+"/read", function( data ) {
			if(data.result=="true"){
				item.parent().parent().remove();
				$("[id^='notifications-count']").text($("#notifications-count").text()-1);
			}
		});
	});

	$("#notifications-readall").click(function(){
		$.getJSON(window.location.origin+"/es/admin/api/notifications/readall", function( data ) {
			if(data.result=="true"){
				$("#notifications-list").html('');
				$("[id^='notifications-count']").text('0');
			}
		});
	});
} );
