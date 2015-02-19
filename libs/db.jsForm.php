<?
//if(!isset($_SESSION)) { session_start(); }
//if(!isset($_SESSION['alive'])) { echo '<script>window.parent.location.href="../../login.php?exit=timeout";</script>'; }
//else { $_SESSION['alive'] = true; }
?>
<script src="../../js/jquery-1.2.6.js" type="text/javascript"></script>
<script type="text/javascript" src="../../js/calendar/date.js"></script>
<script type="text/javascript" src="../../js/calendar/jquery.datePicker.js"></script>
<script src="../../js/calendar/popcalendar.js" type="text/javascript"></script>
<link href="../../js/calendar/datePicker.css" rel="stylesheet" type="text/css" title="default" media="screen" />
<script src="../../js/facebox/facebox.js" type="text/javascript"></script> 
<link href="../../js/facebox/facebox.css" media="screen" rel="stylesheet" type="text/css"/>
<script src="../../js/autocomplete/jquery.autocomplete.js" type="text/javascript"></script> 
<link href="../../js/autocomplete/jquery.autocomplete.css" media="screen" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="../../js/tooltip/jquery.tooltip.css" />
<script src="../../js/tooltip/lib/jquery.bgiframe.js" type="text/javascript"></script>
<script src="../../js/tooltip/lib/jquery.dimensions.js" type="text/javascript"></script>
<script src="../../js/tooltip/jquery.tooltip.js" type="text/javascript"></script>
<link href="../../../style/style.css" rel="stylesheet" type="text/css" />
<script src="../../js/jscolor/jscolor.js" type="text/javascript"></script>
<script>
$(document).ready(function(){
		$("#form_main").fadeIn(600);
	});
function blinker(n, level){
	n.style.backgroundColor="#8AD0EA";
	setTimeout("document.getElementsByName('"+n.name+"')[0].style.backgroundColor=''", 50);
	if(level!=10){
		setTimeout("blinker(document.getElementsByName('"+n.name+"')[0], "+(level+1)+")",100);
	}
}
function verify(f){
	con=0;
	for(ii=0;n=f.elements[ii];ii++){
		if(n.id=="isNull" || str_right(n.id, 4)=="_chk"){
			if(n.value=='' || n.value==null){
				//n.style.backgroundColor='#FF9999';
				blinker(n, 0);
				con++;
			}
		}
	}
	if(con==0) { f.saveButton.disabled='true'; f.submit(); }
	else{
		msg='<div style="{ font-size:15px; }">Falta ingresar '+con+' valor(es) necesario(s)!</div>';
	}
}
function str_right(str, n){
    if (n <= 0)
       return "";
    else if (n > String(str).length)
       return str;
    else {
       var iLen = String(str).length;
       return String(str).substring(iLen, iLen - n);
    }
}

var win;
function PopupCenter(pageURL, title,w,h) {
	var left = (screen.width/2)-(w/2);
	var top = (screen.height/2)-(h/2);
  win = window.open (pageURL, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
	if(title!="selectOne" && title!="selectMany"){ isOpen(); }
}
function isOpen(){
	if (win && win.open && !win.closed) { setTimeout("isOpen(win)",500); }
	else {
		f = document.getElementById('form_main');
		console.log(f.name);
		if (f.name == "calendar"){
			f.submit();
		}
		else this.window.location.reload(); 
	}
}
$(function()
{
	$('.date-picker').datePicker({startDate:'1900-01-01'});
});
</script>
<script>
$(function() {
	$('#tip *').tooltip({
		track: true,
		delay: 0,
		showURL: false,
		showBody: " - ",
		fade: 250
	});
});
</script>
