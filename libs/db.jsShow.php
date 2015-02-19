<script src="js/jquery-1.6.4.min.js" type="text/javascript"></script>
<script type="text/javascript" language="javascript" src="js/dataTables/media/js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="js/dataTables/media/js/jquery.dataTables.js"></script>
<link href="js/dataTables/media/css/demos.css" media="screen" rel="stylesheet" type="text/css"/>
<script src="js/facebox/facebox.js" type="text/javascript"></script> 
<link href="js/facebox/facebox.css" media="screen" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="js/tooltip/jquery.tooltip.css" />
<script src="js/tooltip/lib/jquery.bgiframe.js" type="text/javascript"></script>
<script src="js/tooltip/lib/jquery.dimensions.js" type="text/javascript"></script>
<script src="js/tooltip/jquery.tooltip.js" type="text/javascript"></script>
<link href="../style/style.css" rel="stylesheet" type="text/css" />
<script>
	$(document).ready(function(){
		$("#form_main").fadeIn(600);
	});
function mark(p,f){
	for(ii=0;n=f.elements[ii];ii++){
		if(n.type=="checkbox"){
			if(p!=-1) { n.checked=p ;}
			else{
				if(n.checked==true){ n.checked=false; }
				else { n.checked=true; }
			}
		}
	}
}

function findChar(text, character){
	for(i=0;i<text.length;i++){
		if(text.charAt(i)==character) return true;
	}
	return false
}

function isMarked(f, act, an){
	obj=0;
	for(ii=0;n=f.elements[ii];ii++){
		if(n.type=="checkbox" && n.checked==true)obj++;
	}
	if(obj>0){
		if(findChar(act, "?")==true){ simbol="&"; }
		else { simbol="?"; }
		f.action=act+simbol+an+'=true';
		f.submit(); }
	else {
		msg='<div style="{ font-size:15px; }">no hay ningun item seleccionado!</div>';
		jQuery.facebox(msg);
	}
}
function data(f){
	d="";
	for(ii=0;n=f.elements[ii];ii++){
		if(n.type=="checkbox" && n.checked==true)d+=n.value+",";
	}
	return d;
}
<?
if(isset($_GET['selectOne']))
{
	$exps = explode(",", $_GET['selectOne']);
?>
function selectOne(i, v){
	o=new Array();
<?
	$i=0;
	foreach($exps as $exp)
	{
		$obj = explode("->", $exp);
		$val = $obj[0];
		echo '	o['.$i.']=window.opener.document.'.$val.';'."\n";
		$i++;
	}
?>
		if(o[i].type=="text"){
			o[i].value=v;
		}
		if(o[i].type=="select-one"){
			for (var j=0;j<o[i].options.length;j++) {
				if (o[i].options[j].value == v) o[i].options[j].selected = true;
			}
		}
	}
<? }
if(isset($_GET['selectMany']))
{
	$obj = str_replace("*", "&", $_GET['selectMany']);
	echo 'function selectMany(f){ window.opener.location.href="'.$obj.'&data="+data(f); window.close(); }';
}
?>
function PopupCenter(pageURL, title,w,h) {
	var left = (screen.width/2)-(w/2);
	var top = (screen.height/2)-(h/2);
  	win = window.open (pageURL, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
	//if(title!="selectOne" || title!="selectMany"){ isOpen(); }
	return win;
}
function isOpen(win){
	if (win && win.open && !win.closed) { setTimeout("isOpen(win)",500); }
	else { this.window.location.reload(); }
}
$(function() {
	$('a#tip').tooltip({
		track: true,
		delay: 0,
		showURL: false,
		showBody: " - ",
		fade: 250
	});
});


$(document).ready(function() {
	$('.showData').dataTable( {
		"bProcessing": false,
		"aaSorting": [[ <? echo $this->getOrderColumnData("index"); ?>, "<? echo $this->getOrderColumnData("way"); ?>" ]],
		"iDisplayLength": 25,
		"oLanguage": {
			"sUrl": "js/dataTables/media/language/es_ES.txt" }
	} );
} );

</script>
