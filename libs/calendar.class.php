<?
session_start();

//include("../../libs/db.class.php");
include("../../js/calendarPicker/tc_calendar.php");
//include("../../libs/db.class.php");

class CALENDAR
{
	
	var $actualDate;
	var $nameDays = array("Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo");
	var $nameMonths = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	var $actualDateColor;
	var $actualTimeColor;
	var $availableColor;
	var $notUsableArray;
	var $othersOptionArray;
	var $event;
	var $dropOnWeek;
	var $clickOnCell;
	var $popUpHeight;
	var $popUpWidth;
	var $vista;
	var $eDate;
	var $iDate;
	
	//echo 'var eventType='.$_REQUEST['view'];

	/*AGREGADAS X SONIA*/
	var $titleCalendar; //titulo del calendario
	var $viewDefault; //vista por defecto del calendario
	var $initHour; //hora inicial del dia
	var $endHour; //hora final del dia
	var $initDay; //dia inicial de la semana	
	var $endDay; //dia final de la semana
	var $eventDB; //DB de los eventos
	
	function CALENDAR($date=NULL)
	{
		if($date)
		{
			$this->actualDate = strtotime($date);
		}
		else
		{
			$this->actualDate = mktime();
		}
	}
	public function showWeek($iTime=NULL, $eTime=NULL, $interval=5, $iDate=1, $eDate=6)
	{	
		//echo date("H:i", $iTime)." -- ".date("H:i", $eTime);
		$myRowSchedule = new DB("schedule", "id");	
		$this->iDate = $iDate;
		$this->eDate = $eDate;
		if(!$iTime) $iTime=mktime(0, 0, 0);
		if(!$eTime) $eTime=mktime(24, 60-$interval, 0);
		$totalTime = mktime(0, 0, 0) + $eTime - $iTime;
		$totalMin = idate("H", $totalTime)*60+idate("i", $totalTime);//-idate("i", $eTime);
		
		require_once("js.calendar.php");
		$myCal = new DB("calendar", "id");
		$db = new DB();
		include_once("../../controls.php");
		
		/*echo '<pre>';
		var_dump($this->othersOptionArray);
		echo '</pre>';*/
		
		echo '
		</div>
		<div id="drag">
		<table id="table_head" border="0" cellspacing="0" cellpadding="0" width="99%" rules="rows">
		<th id="th_hour" style="height: 15px;" width="55">Hora</th>';
		$firstDay = $this->actualDate;
		
		while(date("w",$firstDay)!=1)
		{
			$firstDay -= 3600;
		}
					
		$nameDayNum=$iDate-1;
		
		if($eDate-$iDate!=0) $width=round(100/$eDate);
		else $width=100;
		$actualCol = date("N")-1;
		for($i=0;$i<=($eDate-$iDate);$i++)
		{
			if($eDate-$iDate!=0)
			{
				$weekDay=mktime(0, 0, 0, date("m", $firstDay), date("d", $firstDay)+$i, date("Y", $firstDay));
			}else
			{
				$weekDay=mktime(0, 0, 0, date("m", $this->actualDate), date("d", $this->actualDate), date("Y", $this->actualDate));
			}
			
			if($this->actualDateColor) 
			{
				if($i==$actualCol) 
				{ 
					$cellColor='bgcolor="#'.$this->actualDateColor.'"';
					//$cellColor='bgcolor="#336699"';
				}
				else { $cellColor=""; }
			}
			if($nameDayNum==-1) $nameDayNum=7;//??
			echo '<th '.$cellColor.'id="table_th" class="mark" width="'.$width.'%">'.$this->nameDays[$nameDayNum].' '.date("j", $weekDay).' de '.$this->nameMonths[idate("n", $weekDay)-1].'</th>';
			$nameDayNum++;
		}
				
	    $id=0;
	    
		echo '<tr bgcolor="#8BB6DE">
		<td border="1"  style="border-left: 1px solid #93ADDF; border:1px solid #8BB6DE; padding: 2px 0px;" width="55">--:--</td>';
		
		
		for($i=0;$i<=($eDate-$iDate);$i++)
		{
			echo '<td border="1" id="1000/01/0'.$i.'-00:00'.$idClass.'" height="20" style="border-left: 1px solid #8BB6DE; border-bottom:1px solid #8BB6DE;">';
			while($this->event[$id]['date']=="1000-01-0".$i)
			{
				//creo los agendamientos (calendar)
				echo '<div id="'.$this->event[$id]['id'].'" class="drag t1" '.$onClick.' align="left" font-size: 10px;">'.$this->event[$id]['title'].'</div>';
				$id++;
			}	
			
			echo '</td>';
		}
		echo '</tr>';
		$franja = 0;
		$alguno = 0;
		
		$width=round(100/$eDate);
		
		if (($eDate-$iDate) == 0) {
			$width = "90";
		}
		
		echo '
		</table>
		<table id="table_calendar" name="dd-calendar" border="0" cellspacing="0" cellpadding="0" width="100%" rules="rows">';
		//construye las celdas del calendar
		for($i=0;$i<=$totalMin/$interval;$i++)
		{			
			$time=mktime(date("H", $iTime), date("i", $iTime));
			$time=date("H:i", mktime(date("H", $time), date("i", $time)+$interval*$i));
			if($i==0)
			{
				$myWeekDay=mktime(0, 0, 0, date("m", $firstDay), date("d", $firstDay), date("Y", $firstDay));
				$myInitialDay = date("Y/m/d", mktime(0, 0, 0, date("m", $myWeekDay), date("d", $myWeekDay), date("Y", $myWeekDay)));
				$myFinalDay = date("Y/m/d", mktime(0, 0, 0, date("m", $myWeekDay), date("d", $myWeekDay)+6, date("Y", $myWeekDay)));
			}
			
			if($_REQUEST['type']=="operator" || $_REQUEST['type']=="doctores")
			{
				if($_GET['filter']=="consulta")
				{
					$modSch=" AND room in (select id from room where modality=1)";
				}
				$ifPrintRows = $myRowSchedule->doSql("SELECT * FROM schedule WHERE date_s BETWEEN '$myInitialDay' AND '$myFinalDay' AND ((mi_hour<= '$time' AND me_hour>'$time') OR (ai_hour<= '$time' AND ae_hour>'$time'))$modSch AND users =".$_REQUEST['id']);
			}
			else
				$ifPrintRows = $myRowSchedule->doSql("SELECT * FROM schedule WHERE date_s BETWEEN '$myInitialDay' AND '$myFinalDay' AND ((mi_hour<= '$time' AND me_hour>'$time') OR (ai_hour<= '$time' AND ae_hour>'$time')) AND room =".$_REQUEST['id']);

			if($ifPrintRows['id']!="")
			{
				$alguno = 1;
				$franja = 0;
				echo '<tr>';
				for($j=$iDate-1;$j<=$eDate;$j++)
				{
					
					$nextTime=mktime(date("H", $iTime), date("i", $iTime));
					$nextTime=date("H:i", mktime(date("H", $nextTime), date("i", $nextTime)+($interval*($i+1)-1)));
										
					$class='';
					$idClass='';
					$cellDateColor='';
					$cellTimeColor='';
					$weekDay=mktime(0, 0, 0, date("m", $firstDay), date("d", $firstDay)+$j, date("Y", $firstDay));
				
					//echo date("d-m-Y", $weekDay)."<br>";
					
					//colores de fondo de celda
					if($this->actualDateColor) 
					{
						if($j==$actualCol+1) 
						{ 
							$cellDateColor='bgcolor="#'.$this->actualDateColor.'"'; 
						}  //pintamos la cabecera del color
						else 
						{ 
							$cellDateColor=""; 
						}
					}
					if($this->actualTimeColor) 
					{
						if(date("H:i")>=$time && date("H:i")<=$nextTime) 
						{ 
							$cellTimeColor='bgcolor="#'.$this->actualTimeColor.'"'; 
						}  //pintamos la cabecera del color
						else 
						{ 
							$cellTimeColor=""; 
						}
					}
					if($cellDateColor!="" && $cellTimeColor!="")
					{
						$cellTimeColor="";
						$cellDateColor='bgcolor="#'.$this->mixColors($this->actualDateColor, $this->actualTimeColor).'"';
					}
					
					if(is_array($this->othersOptionArray[$j]))
					{
						foreach($this->othersOptionArray[$j] as $item)
						{
							$othersOptioniTime = date("H:i", $item["iTime"]);
							$othersOptioneTime = date("H:i", $item["eTime"]);
							$othersOptionColor = $item["color"];
					
							//pinta la casilla segun el color correspondiente
							if($othersOptioniTime<=$time && $othersOptioneTime>$time)
							{
								
								$class='';
								$idClass='-'.$item['type'];
								if($cellTimeColor!="")
								{ 
									$cellDateColor='bgcolor="#'.$this->mixColors($othersOptionColor, $this->actualTimeColor).'"'; 
								}
								else
								{ 	
									$cellDateColor='bgcolor="#'.$othersOptionColor.'"';
								}
								break;
							}
						}
					}
					
					if(is_array($this->notUsableArray[$j]))
					{
						foreach($this->notUsableArray[$j] as $item)
						{
							$perNotiTime = date("H:i", $item["iTime"]);
							$perNoteTime = date("H:i", $item["eTime"]);
							$perNotColor = $item["color"];
							
							//echo "time=".$time." i=".$perNotiTime." e=".$perNoteTime."<br>";
							
							if($perNotiTime<=$time && $perNoteTime>$time)
							{
								$class='class="mark"';
								$idClass='-NOT';
								if($cellTimeColor!="")
								{ 
									$cellDateColor='bgcolor="#'.$this->mixColors($perNotColor, $this->actualTimeColor).'"'; 
								}
								else
								{ 
									$cellDateColor='bgcolor="#'.$perNotColor.'"';
								}
								break;
							}
						}
					}
			
					if($j==$iDate-1)
					{
						echo '<td class="td_hour" id="'.$time.'" width="55">'.$time.'</td>';
					
					}
					else
					{
						//$onClick='onclick="if(this.id.split('."'-'".')[2]!='."'NOT'".') if(this.innerHTML=='."''".'){ PopupCenter('."'".$this->clickOnCell.'&obj='."'+this.id, 'newEvent', ".$this->popUpWidth.", ".$this->popUpHeight.'); }"';
						
						if(findRole("calendar","insert"))
						{ 
							if($idClass!="-NOT" && $idClass!="")	$onClick='onclick="if(this.innerHTML=='."''".'){ PopupCenter('."'".$this->clickOnCell.'&obj='."'+this.id, 'newEvent', ".$this->popUpWidth.", ".$this->popUpHeight.'); }"';
							else $onClick="";
						}else
						{
							$onClick="";
						}
						
						$actualDay = date("Y/m/d", mktime(0, 0, 0, date("m", $weekDay), date("d", $weekDay)-1, date("Y", $weekDay)));
						if($cellDateColor=="")
						{
							$class='class="mark"';
						}
						
						//crea las columnas en el calendar
						echo '<td border="1" width="'.$width.'%" id="'.$actualDay.'-'.$time.$idClass.'" '.$cellDateColor.$cellTimeColor.$class.'style="border-left: 1px solid #93ADDF; border-bottom:1px solid #93ADDF;" '.$onClick.' onmouseover="glowHour(this.id, 1);" onmouseout="glowHour(this.id, 0);">';
						
						if(date("w", strtotime($this->event[$id]['date']))==0)
						{
							$number=7;
							
						}else
							{
							$number=date("w", strtotime($this->event[$id]['date']));
						}
						
						while($j==$number && ($time<=date("H:i", strtotime($this->event[$id]['time'])) && $nextTime>date("H:i", strtotime($this->event[$id]['time']))) && $id < count($this->event) ) 
						{
							
							$duration= $myCal->doSql("SELECT duration FROM exam WHERE id IN(SELECT exam FROM calendar WHERE id=".$this->event[$id]['id'].")");
							//$priority= $myCal->dosql("SELECT priority FROM calendar WHERE id=".$this->event[$id]['id']);
							$priority= $myCal->dosql("select count(*) as type from calendar where patient = (select patient from calendar where id=".$this->event[$id]['id'].")");
							//crea los objetos examenes (calendar)					
							if($priority['type'] >= 2){
								$priority['type'] = 1;
							}
							else {
								$priority['type']=2;
							}
							echo '<div id="'.$this->event[$id]['id'].'" class="drag t'.$priority['type'].'" '.$onClick.' align="left" font-family: Verdana font-size: 10px;">'.$this->event[$id]['title'].'</div>';
							
							$id++;
							
							if(date("w", strtotime($this->event[$id]['date']))==0)
							{
								$number=7;
							
							}else
								$number=date("w", strtotime($this->event[$id]['date']));
							
						}
						echo '</td>';
					}
				}
				echo '</tr>';
			}else
			{
				if($franja==0)
				{
					$franja=1;
					echo '<tr ><td height="10px" colspan="8" style="background:#336699" > ';
					echo '</td></tr>';
				}
				
			}
			
		}
		if($alguno==0)
		{
			//echo '<tr><td height="100%" colspan="8">&nbsp;</td></tr>';
			if($_REQUEST['type']=="operator" || $_REQUEST['type']=="doctores")
				echo '<script>alert("Este Operador no tiene horarios en esta semana");</script>';
			else
				echo '<script>alert("Esta Sala no esta ocupada por ningun Operador esta semana");</script>';
			exit();
			
		}
		//echo '<tr><td height="100%" colspan="8">&nbsp;</td></tr>';
		echo '</table></div>';
		
		$myDate = explode("/",$actualDay);
		
		$iweek=date("Y-m-d", mktime(0, 0, 0, $myDate[1], $myDate[2]-6, $myDate[0]));
		$fweek=date("Y-m-d", mktime(0, 0, 0, $myDate[1], $myDate[2], $myDate[0]));
				
		$newCalendar = new DB("calendar", "id");
		$newExam = new DB("exam", "id");
		
		
		if($_REQUEST['type']!="room")
		{
			$myNewCalendar = $newCalendar->doSql("SELECT * FROM calendar WHERE users=".$_REQUEST['id']." AND date_c BETWEEN '".$iweek."' AND '".$fweek."' ORDER BY hour_c ");
			
		}else
		{
			$myNewCalendar = $newCalendar->doSql("SELECT * FROM calendar WHERE room=".$_REQUEST['id']." AND date_c BETWEEN '".$iweek."' AND '".$fweek."' ORDER BY hour_c");
			
		}
		//cantidad maxima de celdas de un examen
		$amountRows = 9;
		if($myNewCalendar['id']!=NULL)
		{
			do
			{
				if($_REQUEST['type']!="room")
				{
					$myOperator = $myNewCalendar['room'];
				}else
					$myOperator = $myNewCalendar['users'];
				
				$myNewExam = $newExam->doSql("SELECT duration FROM exam WHERE id=".$myNewCalendar['exam']);		
				$myDateC = explode("-",$myNewCalendar['date_c']);
				$hourAux = explode(":", $myNewCalendar['hour_c']);
				$myHourC =date("H:i", mktime($hourAux[0], $hourAux[1], 0, 0, 0, 0));				
				$nRow = $myNewExam['duration'] / $interval;
				
				for($i=0;$i<$nRow;$i++) {
					if($i<$nRow) {
						$idRowspan[$i] = $myDateC[0]."/".$myDateC[1]."/".$myDateC[2].'-'.$myHourC."-".$myOperator;	
					}
					else {
						$idRowspan[$i]=0;			
					}
					
					$myHourC = date("H:i", mktime(date("H", strtotime($myHourC)), date("i", strtotime($myHourC))+ $interval, 0, 0, 0, 0));
				}

				if($nRow>1)
				{	
					?><script>
						document.getElementById('<? echo $idRowspan[0];?>').rowSpan="<? echo $nRow;?>";
					</script><?
						 
					for($i=1;$i<$nRow;$i++)
					{
						if ($idRowspan[$i] !=  null) {
							?><script>
							document.getElementById('<? echo $idRowspan[$i];?>').style.display = "none";
							</script><?
						}	
					}
				}
				
			}while($myNewCalendar = pg_fetch_assoc($newCalendar->actualResults));
		}

	}
	
	private function convertir_especiales_html($str){
		if (!isset($GLOBALS["carateres_latinos"])){
			$todas = get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES);
			$etiquetas = get_html_translation_table(HTML_SPECIALCHARS, ENT_NOQUOTES);
			$GLOBALS["carateres_latinos"] = array_diff($todas, $etiquetas);
		}
		$str = strtr($str, $GLOBALS["carateres_latinos"]);
		return $str;
	}
	
	private function mixColors($color1, $color2)
	{
		$r = dechex((hexdec(substr($color1, 0, 2))+hexdec(substr($color2, 0, 2)))/2);
		$g = dechex((hexdec(substr($color1, 2, 2))+hexdec(substr($color2, 2, 2)))/2);
		$b = dechex((hexdec(substr($color1, 4, 2))+hexdec(substr($color2, 4, 2)))/2);
		return $r.$g.$b;
	}
	
	function notUsable($weekDay, $iTime, $eTime, $color="F5A9A9")
	{
		$index = count($this->notUsableArray[$weekDay]);
		$this->notUsableArray[$weekDay][$index]["iTime"]=strtotime($iTime);
		$this->notUsableArray[$weekDay][$index]["eTime"]=strtotime($eTime);
		$this->notUsableArray[$weekDay][$index]["color"]=$color;
	}
	
	function othersOption($weekDay, $iTime, $eTime, $type, $color="DDDDDD")
	{
		if($weekDay==0) $weekDay=7;
		$index = count($this->othersOptionArray[$weekDay]);
		$this->othersOptionArray[$weekDay][$index]["iTime"]=strtotime($iTime);
		$this->othersOptionArray[$weekDay][$index]["eTime"]=strtotime($eTime);
		$this->othersOptionArray[$weekDay][$index]["color"]=$color;
		$this->othersOptionArray[$weekDay][$index]["type"]=$type;
	}
	
	function events($data)
	{
		$index = count($this->event);
		$this->event[$index]['id'] = $data['id'];
		$this->event[$index]['time'] = $data['time'];
		$this->event[$index]['date'] = $data['date'];
		$this->event[$index]['title'] = $this->convertir_especiales_html($data['title']);
		
		$this->event[$index]['phone'] = $data['color'];
		$this->event[$index]['color'] = $data['color'];
	}
	
	function mouseEvents($type, $otherOptionType=NULL)
	{
		include("events.calendar.php");
	}
	
	function showMonth($iDay=NULL,$eDay=NULL,$date=NULL)
	{
		require_once("js.calendar.php");
		$db = new DB();
		
		$bool =true;
		$numDay = 1;
		
		if(!$date){
			$dateFull=explode('-',date('Y-m-d'));
		}
		else{
			$dateFull=explode('-',$date);
		}
		
		$month=$dateFull[1];
		$year=$dateFull[0];

		$nameDays = array("Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo");
		$nameMonths = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
		
		$maxMonth = date('t', mktime (0,0,0, $month, 1,$year));
		$firstDayMonth = date("w", mktime(0,0,0,$month,1,$year)); if($firstDayMonth==0) $firstDayMonth=7;
		$numWeek = date(W,mktime(0,0,0,$month,$maxMonth,$year))-date(W,mktime(0,0,0,$month,1,$year))+1; 
		if($numWeek < 0) $numWeek = date(W,mktime(0,0,0,$month,$maxMonth,$year)) + 1;

		echo '
		<link rel="stylesheet" href="../../../style/calendario.css" type="text/css" media="screen" />
		<div id="drag">
		<table id="tableMonth" border="0" width="100%" height="100%" cellspacing="0" cellpadding="0"> 
		<tr>
			<td id="nameMonth" colspan="'.($eDay-$iDay +1).'" height="15px" class="mark">'.$nameMonths[$month-1].'</td></tr>';
		
		$width = 100/($eDay-$iDay +1);
		$height = 100/($numWeek+1);

		/*Muestro los dias de la semana*/
		echo '<tr>';
			for($k=$iDay-1;$k<=$eDay-1;$k++) 
			{
				if($k==($iDay-1)) $classNameDay = 'firstDay';
				else $classNameDay = '';
				echo '<td id="nameDay" width="'.$width.'%" class="mark '.$classNameDay.'">'.$nameDays[$k].'</td>';
			}
		echo '</tr>';
		$id=0;
		/*genero los dias del mes*/
		for($j=1;$j<=$numWeek;$j++)
		{	
			echo '<tr>';
			for($k=0;$k<7;$k++)
			{
				if($numDay==date('d')) $cellDateColor= $this->actualDateColor;
				else $cellDateColor ="";
				
				if($k==0) $nameId = 'initDayWeek';
				else  $nameId = 'day';
				if($j==$numWeek) $class = 'LastWeekDay';
				
				if(($k+1) == $firstDayMonth && $bool)
				{
					//echo '<td valign="top" id="'.$nameId.'" class="drag '.$class.'" height="'.$height.'%" style="background:#'.$cellDateColor.'" align="top">';
					
					if($_REQUEST['type']=="room")
					{
						
						$mySchedule = $db->doSql("SELECT * FROM schedule WHERE date_s='".$year."-".$month."-0".$numDay."' AND users=1 AND room =".$_REQUEST['id']);
						
						if($mySchedule['id']=="")
						{
							$myColor = $db->doSql("SELECT color FROM room WHERE id =".$_REQUEST['id']);
							echo '<td valign="top" id="'.$nameId.'" class="mark '.$class.'" height="'.$height.'%" style="background:#'.$myColor['color'].'">';	
						}else
						{
							echo '<td valign="top" id="'.$nameId.'" class="mark '.$class.'" height="'.$height.'%" style="background:#'.$cellDateColor.'">';		
						}		
						
					}else
					{
						
						$mySchedule = $db->doSql("SELECT * FROM schedule WHERE date_s='".$year."-".$month."-0".$numDay."' AND users=".$_REQUEST['id']);
						
						if($mySchedule['id']!="")
						{
							$myColor = $db->doSql("SELECT color FROM users WHERE id =".$_REQUEST['id']);
							echo '<td valign="top" id="'.$nameId.'" class="mark '.$class.'" height="'.$height.'%" style="background:#'.$myColor['color'].'">';	
						}else
						{
							echo '<td valign="top" id="'.$nameId.'" class="mark '.$class.'" height="'.$height.'%" style="background:#'.$cellDateColor.'">';	
						}
					}
					
					
					echo '<div id="numDay">'.$numDay.'</div>';
					//echo "1 dia del mes";
					while($numDay==date('d', strtotime($this->event[$id]['date'])))
					{
						
						echo '<div id="'.$this->event[$id]['id'].'" class="drag t1" '.$onClick.' align="center">'.$this->convertir_especiales_html($this->event[$id]['title']).'</div>';
						$id++;
					}
							
					echo '</td>';
					$bool = false;
					$numDay++;
				}
				else if($numDay <=$maxMonth && !$bool)
				{
					
					//$roomName = $room->doSql("SELECT name FROM room WHERE id=".$_REQUEST['id']."");
					
					
					
					if($_REQUEST['type']=="room")
					{
						
						if($numDay<10){
							$mySchedule = $db->doSql("SELECT * FROM schedule WHERE date_s='".$year."-".$month."-0".$numDay."' AND users=1 AND room =".$_REQUEST['id']);
						}
						else
						{
							$mySchedule = $db->doSql("SELECT * FROM schedule WHERE date_s='".$year."-".$month."-".$numDay."' AND users=1 AND room =".$_REQUEST['id']);
						}
						
						
						if($mySchedule['id']=="")
						{
							$myColor = $db->doSql("SELECT color FROM room WHERE id =".$_REQUEST['id']);
							echo '<td valign="top" id="'.$nameId.'" class="mark '.$class.'" height="'.$height.'%" style="background:#'.$myColor['color'].'">';	
							
							
						}else
						{
							echo '<td valign="top" id="'.$nameId.'" class="mark '.$class.'" height="'.$height.'%" style="background:#BBBBBB">';		
							
						}		
						
					}else
					{
						
						if($numDay<10){
							$mySchedule = $db->doSql("SELECT * FROM schedule WHERE date_s='".$year."-".$month."-0".$numDay."' AND users=".$_REQUEST['id']);
						}
						else
						{
							$mySchedule = $db->doSql("SELECT * FROM schedule WHERE date_s='".$year."-".$month."-".$numDay."' AND users=".$_REQUEST['id']);
						}
						
						
						if($mySchedule['id']!="")
						{
							$myColor = $db->doSql("SELECT color FROM users WHERE id =".$_REQUEST['id']);
							echo '<td valign="top" id="'.$nameId.'" class="mark '.$class.'" height="'.$height.'%" style="background:#'.$myColor['color'].'">';	
						}else
						{
							echo '<td valign="top" id="'.$nameId.'" class="mark '.$class.'" height="'.$height.'%" style="background:#'.$cellDateColor.'">';	
						}
					}
					
															
					echo '<div id="numDay">'.$numDay.'</div>';
					//echo " fin de mes";
					//var_dump($this->event);					
					while($numDay==date('d', strtotime($this->event[$id]['date'])))
					{
						if($this->event[$id]['date']==""){
							break; //ARREGLO... NO SE PORQUE PESCA INFINITOS EVENTOS SIN NOMBRES.. TONCES POR ESO ERA EL LOOP
						//SI LO QUE HAY EN EL EVENTO ES NADA (LO QUE PASABA) SALETE.. BY MARKO
						}
						//$this->event[$id]['date']
						echo '<div id="'.$this->event[$id]['id'].'" class="drag t1" '.$onClick.' align="center">'.$this->convertir_especiales_html($this->event[$id]['title']).'</div>';
						$id++;
						
					}
					
					echo '</td>';
					$numDay++;
				}
				else
				{
					echo '<td valign="top" id="'.$nameId.'" bgcolor="#DDDDDD" class="mark '.$class.'" height="'.$height.'%"><div id="numDay">--</div></td>';
				}
			}	
			echo '</tr>';
		}
		echo '</table>
		</div>';
	}

	function showFrameCalendar($title=NULL, $viewDefault=NULL,$actualDate=NULL)
	{
		
		//var_dump($actualDate);
		$eventDate=explode("-",$actualDate);
		$año = $eventDate[0];
		$mes = $eventDate[1];
		$dia = $eventDate[2];
		
		global $room;
		$operator = new DB("employee", "id");

		$this->titleCalendar = $this->convertir_especiales_html($title);
		
		if(!$viewDefault) {$this->viewDefault = 'week';}
		else { $this->viewDefault = $viewDefault;}

		echo '	
		<html>
		<head>
			<link rel="stylesheet" href="../../../style/calendario.css" type="text/css" media="screen" />
			<script src="../../js/jquery-1.6.4.min.js"></script>
			</head>
			<script>
			var show_sidebar = false;
			$(document).ready(function(){
					$("#calendar").fadeIn(500, function() {
						toggle_sidebar("+=218px");
						show_sidebar = true;
					});
					
					$("#trigger").click(function() {
						var px = "-=218px"						
						if (!show_sidebar) {							
							px = "+=218px"
							show_sidebar = true;
						}else {
							show_sidebar = false;
						}
						toggle_sidebar(px);
					});
					
				});
			function toggle_sidebar(px) {
				$("#sidebar").animate({
						marginLeft: px
				});
				$("#trigger").animate({
						marginLeft: px
				}, function() {
					if (show_sidebar) {
						$("#trigger").html("<<");							
					}
					else {
						$("#trigger").html(">>");
					}
				});
			}	
			</script>	
		</head>
		
		<body>';

		$myCalendar = new tc_calendar("date2");
		$myCalendar->SetPicture("images/iconCalendar.gif");
  	    $myCalendar->setDate($dia, $mes, $año);
		$myCalendar->setPath("./");
		$myCalendar->setYearSelect(1970, 2050);
		$myCalendar->dateAllow('2008-05-13', '2050-03-01', false);
		$myCalendar->startMonday(true);
		
		if(isset($_GET['view']))
		{
			$this->vista = $_GET['view'];
		}
		else
		{
			$this->vista = $this->viewDefault;
		}
		
		if(isset($_GET['actualDate']))
		{
			$this->actualDate = strtotime($_GET['actualDate']);
		}
		else
		{
			$this->actualDate = mktime();
		}
	
		if($this->vista=="day")
		{
			 $daysMove=1;
			 $monthMove=0;
		 
		}else
		if($this->vista=="week")
		{
			 $daysMove=7;
			 $monthMove=0;
		 
		}else
		if($this->vista=="month")
		{
			 $daysMove=0;
			 $monthMove=1;
		 
		}
			
		$previousDay = date("Y-m-d", mktime(0, 0, 0, date("m", $this->actualDate)-$monthMove, date("d", $this->actualDate)-$daysMove, date("Y", $this->actualDate)));
		$nextDay = date("Y-m-d", mktime(0, 0, 0, date("m", $this->actualDate)+$monthMove, date("d", $this->actualDate)+$daysMove, date("Y", $this->actualDate)));
		$todayDay = date("Y-m-d", mktime(0, 0, 0, date("m", $this->actualDate), date("d", $this->actualDate), date("Y", $this->actualDate)));
		$today = date("Y-m-d", mktime(0, 0, 0, date("m", mktime()), date("d", mktime()), date("Y", mktime())));
		
		
		
		
		if($_REQUEST['type']=="room")
		{
			
			$roomName = $room->doSql("SELECT name FROM room WHERE id=".$_REQUEST['id']."");
			$leyenda = " horario de la sala ".$roomName['name'];
			
			if($_REQUEST['view']=="month")
			{
				$this->titleCalendar = strtoupper($this->convertir_especiales_html($leyenda." (Solo Visualizacion)"));	
			}else
				$this->titleCalendar = strtoupper($this->convertir_especiales_html($leyenda));
			//var_dump($_REQUEST);
			
	
		}else
		{
			if(isset($_REQUEST['id'])){
				$operatorName = $operator->doSql("SELECT * FROM employee WHERE employee.id IN (SELECT employee FROM users WHERE id=".$_REQUEST['id'].")");
				$leyenda = "horario del operador ".$operatorName['name']." ".$operatorName['lastname'];
				$this->titleCalendar = strtoupper($leyenda);
					
			}
		}
		//var_dump($_REQUEST);
		?>
			<div id = "calendar">
				<div id = "sidebar">
					<div style="overflow: auto; height: 100%; width: 100%;">
						<div>
							<table id="tableIcon" border="0" width="100%" height="100%" cellspacing="0" cellpadding="0">
							<tr>
								<!--<td align="center"><? echo '<a id="todos" href="?filter=todos"><table id="menuIcon" border="0" cellspacing="0" cellpadding="0"><tr><td align="center"><img src="../../../images/iconMenu/all.png" border="0"/></td></tr><tr><td id="title" align="center">Todas</td></tr></table></a>';?></td>-->
								<!--<td align="center"><? echo '<a id="examenes" href="?filter=examen"><table id="menuIcon" border="0" cellspacing="0" cellpadding="0"><tr><td align="center"><img src="../../../images/iconMenu/examRoom.png" border="0"/></td></tr><tr><td id="title" align="center">Examenes</td></tr> </table></a>';?></td>-->
								<!--<td align="center"><? echo '<a id="consultas" href="?filter=consulta"><table id="menuIcon" border="0" cellspacing="0" cellpadding="0"><tr><td align="center"><img src="../../../images/iconMenu/box.png" border="0"/></td></tr><tr><td id="title" align="center">Consulta</td></tr></table></a>';?></td>-->
							</tr>
							<tr>
								<td colspan="3"><? //echo '<div algin="center" id="subTitle">&rArr;&nbsp;&nbsp;Vista Actual: '.strtoupper($_GET['filter']).'</div>';?></td>
							</tr>
							</table>
						</div>
						<div><? //$this->showSearch();?></div>
						<div><? echo $myCalendar->writeScript(); ?></div>
						<div><? $this->showMenuCalendar();?></div>
						<div><? //$this->showNomclature();?></div>
					</div>
				</div>
				<div id = "trigger">>></div>	
				<div id = "wrapper">
					<table id="headerCalendar" width="100%"  cellspacing="0" cellpadding="0">
						<tr>
							<td id="headerLeft">
								<ul class="tabs">
								<?if(isset($_REQUEST['id']))
								{
									//var_dump($_REQUEST);
									?>
								
									<li><a id="prev" href="?actualDate=<? echo $previousDay;?>&view=<? echo $this->vista;?>&id=<? echo $_REQUEST['id'];?>&date=<? echo $_REQUEST['actualDate'];?>&type=<? echo $_REQUEST['type'];?>&filter=<? echo $_REQUEST['filter'];?>&examen_code=<? echo $_REQUEST['examen_code'];?>"> << </a></li>
									<li><a id="next" href="?actualDate=<? echo $nextDay;?>&view=<? echo $this->vista;?>&id=<? echo $_REQUEST['id'];?>&date=<? echo $_REQUEST['actualDate'];?>&type=<? echo $_REQUEST['type'];?>&filter=<? echo $_REQUEST['filter'];?>&examen_code=<? echo $_REQUEST['examen_code'];?>"> >> </a></li>
									<li><a id="today" href="?actualDate=<?echo $today;?>&view=<? echo $this->vista;?>&id=<? echo $_REQUEST['id'];?>&date=<? echo $_REQUEST['actualDate'];?>&type=<? echo $_REQUEST['type'];?>&filter=<? echo $_REQUEST['filter']?>&examen_code=<? echo $_REQUEST['examen_code'];?>">Hoy</a></li>	
								<?}?>
								</ul>
							</td>
							<td id="headerCenter"><? echo $this->convertir_especiales_html($this->titleCalendar); ?></td>
							<td id="headerRight">
								<ul class="tabs">
								<?if(isset($_REQUEST['id'])){?>
									<li><a id="day" href="?view=day&actualDate=<? echo $todayDay;?>&id=<? echo $_REQUEST['id'];?>&date=<? echo $_REQUEST['actualDate'];?>&type=<? echo $_REQUEST['type'];?>&filter=<? echo $_REQUEST['filter']?>&examen_code=<? echo $_REQUEST['examen_code'];?>">Dia</a></li>
									<li><a id="week" href="?view=week&actualDate=<? echo $todayDay;?>&id=<? echo $_REQUEST['id'];?>&date=<? echo $_REQUEST['actualDate'];?>&type=<? echo $_REQUEST['type'];?>&filter=<? echo $_REQUEST['filter']?>&examen_code=<? echo $_REQUEST['examen_code'];?>">Semana</a></li>
									<li><a id="month" href="?view=month&actualDate=<? echo $todayDay;?>&id=<? echo $_REQUEST['id'];?>&date=<? echo $_REQUEST['actualDate'];?>&type=<? echo $_REQUEST['type'];?>&filter=<? echo $_REQUEST['filter']?>&examen_code=<? echo $_REQUEST['examen_code'];?>">Mes</a></li>
									<li id="year"><? $myYear = explode("-",$_REQUEST['actualDate']);  echo $myYear[0];?></li>
								<?}?>	
								</ul>
							</td>
						</tr>						
					</table>
					<? echo $this->showNomclature(); ?>
					<div class="tab_container">
						<div class="tab_content">
							<?if(isset($_REQUEST['id'])){?>
								<iframe id="iframeCalendar" src="iframeCalendar.php?view=<? echo $this->vista;?>&id=<? echo $_REQUEST['id'];?>&date=<? echo $_REQUEST['actualDate'];?>&type=<? echo $_REQUEST['type']?>&filter=<? echo $_REQUEST['filter']?>" name="iframeCalendar" width="100%" SCROLLING="AUTO"></iframe>
							<?}else
							  {
								  if(isset($_REQUEST['users'])){?>
									<iframe id="iframeCalendar" src="iframeCalendar.php?view=<? echo $this->vista;?>&id=<? echo $_REQUEST['users'];?>&date=<? echo $this->actualDate;?>&type=operator&filter<? echo $_REQUEST['filter']?>" name="main" width="100%" height="90%" SCROLLING="AUTO"></iframe>
									<?}else
									echo '<div id="messageCalendarInit">Seleccione un operador o una sala para ver su horario!</div>';
							  }?>
						</div>							
					</div>
				</div>		
			</div>
		</body>
		</html>
		<?
	}

	function menus($menu)
	{
		$index = count($this->menu);
		$this->menu[$index]['type'] = $menu['type'];
		$this->menu[$index]['name'] = $menu['name'];
	}
	
	function itemsMenu($item)
	{
		$index = count($this->itemMenu);
		$this->itemMenu[$index]['type'] = $item['type'];
		$this->itemMenu[$index]['id'] = $item['id'];
		$this->itemMenu[$index]['name'] = $item['name'];
		$this->itemMenu[$index]['color'] = $item['color'];
	}
	
	function showMenuCalendar()
	{
		
		for($i=0;$i<count($this->menu);$i++)
		{
			echo '<ul class="menu">
				  <li id="nameMenu"><a>'.$this->menu[$i]['name'].'</a></li>';
			for($j=0;$j<count($this->itemMenu);$j++)
			{
				if($this->menu[$i]['type']== $this->itemMenu[$j]['type'])
				{
					$styleColorItem ='float:right;width:20px; height:20px; background:#'.$this->itemMenu[$j]['color'];
					echo '<li id="itemMenu"><a href="?actualDate='.date("Y-m-d",$this->actualDate).'&view='.$this->vista.'&type='.$this->itemMenu[$j]['type'].'&id='.$this->itemMenu[$j]['id'].'&name='.$this->itemMenu[$j]['name'].'&filter='.$_REQUEST['filter'].'&examen_code='.$_REQUEST['examen_code'].'"><label >'.strtolower($this->itemMenu[$j]['name']).'</label><div style="'.$styleColorItem.'"></div></a></li>';
				}
			}
			
		    echo '</ul>';
		}
	}
	function showNomclature()
	{
	echo '<ul class="nomclature" >
			<li id="item"><img src="../../../images/calendar/red.png" border="0" align="absmiddle"> Agendado</li>
			<li id="item"><img src="../../../images/calendar/orange.png" border="0" align="absmiddle"> Confirmado</li>
			<li id="item"><img src="../../../images/calendar/yellow.png" border="0" align="absmiddle"> Pagado</li>
			<li id="item"><img src="../../../images/calendar/green.png" border="0" align="absmiddle"> En espera</li>
			<li id="item"><img src="../../../images/calendar/cyan.png" border="0" align="absmiddle"> Ingresado</li>
			<li id="item"><img src="../../../images/calendar/blue.png" border="0" align="absmiddle"> Finalizado</li>
			<li id="item"><img src="../../../images/calendar/mblue.png" border="0" align="absmiddle"> Asignado</li>
			<li id="item"><img src="../../../images/calendar/purple.png" border="0" align="absmiddle"> Informado</li>
			<li id="item"><img src="../../../images/calendar/grey.png" border="0" align="absmiddle"> Validado</li>
			<li id="item"><img src="../../../images/calendar/lred.png" border="0" align="absmiddle"> Despachado</li>
			<li id="item"><img src="../../../images/print.png" width="20" height="20" style = "cursor:pointer;" border="0" onclick="window.frames['."'iframeCalendar'".'].print();"/></li>
			<li id="item"><img src="../../../images/refresh.png" style = "cursor:pointer;" border="0" onclick="window.location.reload()"/></li>
			<li id="item"><div name="lastLog" id="lastLog" align="right"></div></li>
		</ul>
		';	
	}
		
	function showSearch()
	{ 
										
		echo '<form id="form_search_exam" action="calendar.php">';
		echo '<table align="center">';
		//echo '<tr><td colspan="2"><p id="title_search_exam">Busqueda Examen:</p></td></tr>';
		//echo '<tr><td><input id="input_exam" name="examen_code" type="text" value=""></td>';
		//echo '<td><input id="button" name="examSearch" type="submit" value="" onclick="submit();"></td></tr>';
		echo '<tr><td><input name="content" type="hidden" value="month"></td>';
		echo '<td><input name="type" type="hidden" value="Salas"></td></tr>';
		echo '<tr><td><input name="calendar" type="hidden" value="yes"></td>';
		echo '<td><input name="view" type="hidden" value="week"></td></tr></table></form>';
	
	}
}
?>


