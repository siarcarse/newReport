	<?
	session_start();
	include_once("../../controls.php");
	
	?>
	
	<script>
	
	function PopupCenter(pageURL, title,w,h) {
		var left = (screen.width/2)-(w/2);
		var top = (screen.height/2)-(h/2);
		win = window.open (pageURL, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
		//if(title!="selectOne" || title!="selectMany"){ isOpen(); }
	}
	
	function deleteEvent(id)
	{
			//ajax
		var xmlhttp=false;
		try {
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
				try {
						xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (E) {
						xmlhttp = false;
				}
		}

		if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
				xmlhttp = new XMLHttpRequest();
		}
		ajax=xmlhttp;


		ajax.open("POST", "../calendar/getPayment.php", true);

		ajax.onreadystatechange=function() {
				if (ajax.readyState==4) {
						if(ajax.responseText=="yes"){
							var answer = confirm("Este examen esta pagado, si lo elimina se eliminaran tambien los pagos asociados.");
						}else
							var answer = confirm("Si elimina este examen no podra volver a recuperarlo.");
						
								
						if (answer)
						{
							//$_SERVER['REQUEST_URI'] = iframeCalendar
							window.location.href="<? echo $_SERVER['REQUEST_URI'];?>&remove="+id;
							
						}
			
						//alert(ajax.responseText);
						//task.real_name.value=ajax.responseText;
				}
		}
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send("id="+id);
		
	
	}
	
	function getDimensions(oElement) {
		oElement=document.getElementById(oElement);
		var x, y, w, h;
		x = y = w = h = 0;
		if (document.getBoxObjectFor) { // Mozilla
		  var oBox = document.getBoxObjectFor(oElement);
		  x = oBox.x-1;
		  w = oBox.width;
		  y = oBox.y-1;
		  h = oBox.height;
		}
		else if (oElement.getBoundingClientRect) { // IE
		  var oRect = oElement.getBoundingClientRect();
		  x = oRect.left-2;
		  w = oElement.clientWidth;
		  y = oRect.top-2;
		  h = oElement.clientHeight;
		}
		return {x: x, y: y, w: w, h: h};
	}
	function drawLine(x, y, w, h)
	{
		document.write('<div style="position:absolute;top:'+y+'px;left:'+x+'px;width:'+w+'px;height:'+h+'px;font-size:2px;background:#FF0000"></div>');
	}
	function drawToday()
	{
		var todayx1=getDimensions("today").x;
		var todayx2=getDimensions("today").x+getDimensions("today").w+1;
		var todayy=getDimensions("today").y+1;
		var todayh=getDimensions("table_calendar").h;
		drawLine(todayx1, todayy, 1, todayh);
		drawLine(todayx2, todayy, 1, todayh);
	}
	function glowHour(id, b)
	{
		var hid=id.split("-")
		if(b==0) c = 'td_hour';
		if(b==1) c = 'td_hour_glow';
		document.getElementById(hid[1]).className=c;
	}
	</script>
	<link rel="stylesheet" href="../../../style/calendario.css" type="text/css" media="screen" />
	<script src="../../js/drag.js" language="JavaScript"></script>
		<!-- LLAMAMOS LAS LIBRERIAS NECESARIAS PARA LA IMPLEMENTACION -->
	<script>
	function nuevoAjax(){
			var xmlhttp=false;
			try {
					xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
					try {
							xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (E) {
							xmlhttp = false;
					}
			}

			if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
					xmlhttp = new XMLHttpRequest();
			}
			return xmlhttp;
	}
	window.onload=function(){
		//VALIDAMOS QUE TRAMADO ESTE COMPLETO
		REDIPS.drag.init();
		//INICIALIZAMOS EL "OBJETO"
		REDIPS.drag.hover_color="#CEEF9C";
		// SELECCIONAMOS EL COLOR PARA EL HOVER
		REDIPS.drag.drop_option = "single";//multiple 
		

		//MANEJO DE EVENTOS
		var lastClickId;
		var lastClickInner;
		//var x = document.getElementById("evento");
		REDIPS.drag.myhandler_clicked=function(){
			if(lastClickId!=REDIPS.drag.obj.id) //si no estoy haciendo click donde mismo
			{	

				if(lastClickId!=null) { document.getElementById(lastClickId).innerHTML=lastClickInner; } //que exista un objeto anterior para cambiarle el html interior
				lastClickId=REDIPS.drag.obj.id;
				
				lastClickInner=REDIPS.drag.obj.innerHTML;
				


				var xmlhttp_=false;
				try {
						xmlhttp_ = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
						try {
								xmlhttp_ = new ActiveXObject("Microsoft.XMLHTTP");
						} catch (E) {
								xmlhtt_ = false;
						}
				}

				if (!xmlhttp_ && typeof XMLHttpRequest!='undefined') {
						xmlhttp_ = new XMLHttpRequest();
				}
				ajax_=xmlhttp_;
				ajax_.open("POST", "../calendar/checkStandBy.php", true);

				ajax_.onreadystatechange=function() {
					if (ajax_.readyState==4) {
							//task.real_name.value=ajax_.responseText_;
							
							//alert(ajax_.responseText);
							resp = ajax_.responseText.split("&");
							if(resp[0]==0)
							{
								<?if(findRole("calendar","show_detail"))
								{ ?>
									
									var urlEdit="PopupCenter('../../modules/calendar/editFromCalendar.php?id="+lastClickId+"&type=edit','Modificar',1000,500)";
									var edit ='<a onclick="'+urlEdit+'" ><img class="imgEdit-Delete" src="../../../images/edit.png"/></a>';
									//var edit ='<a onclick="'+urlEdit+'" ><img width="12px" height="12px" border="0" align="absmiddle" src="../../../images/edit.png"/></a>';
									
								<?}else{?>
									var edit='';
								<?}							
								if(findRole("calendar","delete"))
								{ ?>
									var remove ='<a onclick="deleteEvent('+lastClickId+');"><img class="imgEdit-Delete" src="../../../images/delete_s.png"/></a>';
								<? }
								else{ ?>
									var remove='';
								<? } ?>
							}else
							{
								var edit='';
								var remove='';	
							}
										
							if(edit!=''||remove!='')  REDIPS.drag.obj.innerHTML='<table width="100%" border="0"><tr><td align="left"style="font-family:Verdana, Geneva, sans-serif;font-size:9px; color:#ffffff;">'+REDIPS.drag.obj.innerHTML+'</td><td width="36"><table align="right" border="0"><tr><td width="18px">'+edit+'</td><td width="18px">'+remove+'</td></tr></table>';
					}
				}
				ajax_.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				ajax_.send("id="+REDIPS.drag.obj.id);

			}
		}
		REDIPS.drag.myhandler_moved=function(){

		}
		REDIPS.drag.myhandler_dropped=function()
		{//suelta el elemento!!
			REDIPS.drag.drop_option = "single";
			
			var id=REDIPS.drag.obj.id;			
			var tcn = REDIPS.drag.target_cell.id;
			var sco = REDIPS.drag.source_cell.id;

				if(REDIPS.drag.target_cell.id=="1000/01/01-00:00")
				{
					//alert(REDIPS.drag.target_cell.id);
					REDIPS.drag.drop_option = "multiple";//multiple 
					
				}else
				{
					REDIPS.drag.drop_option = "single";//multiple 	
				}
				
				if(tcn!=sco) //verificamos que no se este moviendo en la misma celda
				{
					//tcn=sco;
					if(confirm("Esta seguro/a de mover este elemento?"))
					{	
					 <?if(findRole("calendar","update"))
					 { ?>
				 
				   <?
				   if($this->dropOnWeek) //verificamos que exista variable
				   {
				   ?>
						var xmlhttpA=false;
						try {
								xmlhttpA = new ActiveXObject("Msxml2.XMLHTTP");
						} catch (e) {
								try {
										xmlhttpA = new ActiveXObject("Microsoft.XMLHTTP");
								} catch (E) {
										xmlhttpA = false;
								}
						}

						if (!xmlhttpA && typeof XMLHttpRequest!='undefined') {
								xmlhttpA = new XMLHttpRequest();
						}
						ajaxA=xmlhttpA;
					
						ajaxA.open("POST", "../calendar/updateCalendar.php", true);
					
						ajaxA.onreadystatechange=function() 
						{
								if (ajaxA.readyState==4) {
									//alert(ajaxA.responseText);
									//alert("movido con exito!\nPor favor espere a que recargue la pagina para actualizar el tiempo del examen/consulta");
									//setTimeout('alert("movido con exito!\nPor favor espere a que recargue la pagina para actualizar el tiempo del examen/consulta");', 10000);
								}
						}
						ajaxA.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
						ajaxA.send("obj="+id+"-"+tcn+"-"+'<? echo $_REQUEST['type'];?>');
					
					<?}?>
					
					<? } ?>
					}
				setTimeout("window.location.reload()", 2000);
				//window.location.reload();
			}
		} 			                                            
	}
	</script>
