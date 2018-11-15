function getTimeAgo(timestamp){
  textTimeAgo="Hace ";
  if(timestamp < 60) textTimeAgo = textTimeAgo+ Math.ceil(timestamp/5)*5+' segundos.';
   else if(timestamp < 3600) textTimeAgo = textTimeAgo+ Math.round(timestamp/60)+' minutos.';
     else if(timestamp < 86400) textTimeAgo = textTimeAgo+ Math.round(timestamp/3600)+' horas.';
       else if (timestamp < 2592000) textTimeAgo = textTimeAgo+ Math.round(timestamp/86400)+' días.';
         else if (timestamp < 946080000) textTimeAgo = textTimeAgo+ Math.round(timestamp/2592000)+' meses.';
          else if (timestamp < 11352960000) textTimeAgo = textTimeAgo+ Math.round(timestamp/946080000)+' años.';
  return textTimeAgo;
}

function getTimeDateShort(timestamp){
  /*var d = new Date(timestamp*1000);
  var today = new Date();
  var text = "";
  var day = "0" + (d.getDate()).slice(-2);
  var month = "0"+ (d.getMonth()+1)).slice(-2);
  var year = d.getFullYear();
  if(today.getFullYear() > datetime.getFullYear()){
      text=dateFormat(datetime, 'mm/dd/y');
  }else{
      if(today.getDate() > datetime.getDate()){
        text=dateFormat(datetime, 'M m');
      }else{
        text=dateFormat(datetime, 'g:ii a');
      }
  }*/

}
