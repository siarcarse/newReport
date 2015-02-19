<?
$html.= '<tr>';
if ($showControls == TRUE) 
{
	if(isset($_GET['data'])) 
	{
		$data = $_GET['data'];
		$exp = explode(",", $data);
		if(in_array($result[$this->idField], $exp)) { $checked = "checked"; }
		else $checked = "";
	}
	$html.= '<td width="20"><input type="checkbox" name="checkbox[]" value="'.$result[$this->idField].'" '.$checked.' /></td>';  //si es true construye objetos de formulario
	
}
foreach ($result as $field=>$data)
{
	if(!in_array($field, $this->hideFieldsArray))
	{
		$objectToChange=NULL;
		$addRigthText=NULL;
		$addRigthImage=NULL;
		$replaceWithImage=NULL;
		$replaceWithText=NULL;
		$changeAttribValue=NULL;
		if($this->showObjectChangeIf[$field])
		{
			foreach($this->showObjectChangeIf[$field] as $id=>$item)
			{
				$logic=$item['logic'];
				$compare=$item['compare'];
				$object=$item['object'];
				$attrib=$item['attrib'];
				if($logic=='==')
				{
					if($result[$field]==$compare)
					{
						$objectToChange=$object;
						$attrib=$attrib;
					}
				}
				elseif($logic=='!=')
				{
					if($result[$field]!=$compare)
					{
						$objectToChange=$object;
						$attrib=$attrib;
					}
				}
				elseif($logic=='<=')
				{
					if($result[$field]<=$compare)
					{
						$objectToChange=$object;
						$attrib=$attrib;
					}
				}
				elseif($logic=='>=')
				{
					if($result[$field]>=$compare)
					{
						$objectToChange=$object;
						$attrib=$attrib;
					}
				}
				elseif($logic=='<')
				{
					if($result[$field]<$compare)
					{
						$objectToChange=$object;
						$attrib=$attrib;
					}
				}
				elseif($logic=='>')
				{
					if($result[$field]>$compare)
					{
						$objectToChange=$object;
						$attrib=$attrib;
					}
				}
				elseif($logic=='have')
				{
					if(strstr($result[$field], $compare))
					{
						$objectToChange=$object;
						$attrib=$attrib;
					}
				}
				if($objectToChange)
				{
					if($compare==NULL) { $compare=" "; $result[$field]=" "; }
					if($objectToChange=="addRigthText") { $addRigthText=$attrib; }
					elseif($objectToChange=="addRigthImage") { $addRigthImage='<img align="center" src="'.$attrib.'" border="0" />'; }
					elseif($objectToChange=="replaceWithImage") { $replaceWithImage=str_replace($compare, '<div align="center" id="tip"><img align="center" src="'.$attrib.'" border="0" title=" - '.$result[$field].'" /></div>', $result[$field]); $result[$field]=$replaceWithImage; }
					elseif($objectToChange=="replaceWithText") { $replaceWithText=str_replace($compare, $attrib, $result[$field]); $result[$field]=$replaceWithText; }
					break;
				}
			}
		}
		if($this->showObjectChange[$field])
		{
			$changeAttribValue=str_replace("%value%", $result[$field], $this->showObjectChange[$field]['attrib']);
			$changeAttribValue=str_replace("%id%", $result[$this->idField], $changeAttribValue);
		}
		if($replaceWithImage) { $value=$replaceWithImage;}
		elseif($replaceWithText) { $value=$replaceWithText;}
		elseif($changeAttribValue) { $value=$changeAttribValue; }
		else { $value=strip_tags($result[$field]); }
		if($value==NULL) { $value="&nbsp;"; } // visualizacion para que no quede una celda sin informacion
		if(strlen($value)>=400) { $value = substr($value, 0, 400).'...'; } //limita la cantidad de informacion en una celda a 400 caracteres... 
		$html.= '<td>';
		if($this->toolTipInShow[$field]['flag']==TRUE)
		{
			if($result[$field])
			{
				if($this->toolTipInShow[$field]['image'])
				{
					$image = $this->toolTipInShow[$field]['image'];
					$img = '<img src="'.$image.'" align="absmiddle" title=" - '.strip_tags($result[$field]).'" border="0" />';
				}
				else {
					$img = '<img src="'.$this->myPathShow.'images/info.png" align="absmiddle" title=" - '.strip_tags($result[$field]).'" border="0" />';
				}
				if($this->toolTipInShow[$field]['link'])
				{
					$link = $this->toolTipInShow[$field]['link'];
					$a = '<a href="#" onclick="jQuery.facebox('."'<iframe src=$link&id=".$result[$this->idField]." width=100% height=500 scrolling=auto frameborder=0 transparency></iframe>'".')">'.$img.'</a>';
				}
				else {
					$a = $img;
				}
			}
			else {
				if($this->toolTipInShow[$field]['backImage']) {
					$backImage = $this->toolTipInShow[$field]['backImage'];
					$a = '<img src="'.$backImage.'" align="absmiddle" border="0" />';
				}
				else {
					$a = '<img src="'.$this->myPathShow.'images/infoBack.png" align="absmiddle" border="0" />';
				}
			}
			$html.= '<div align="center" id="tip">'.$a.'</div>'.$addRigthImage.$addRigthText;
		}
		else {
			$html.= $this->latin($value).$addRigthImage.$addRigthText;
		}
		$html.= '</td>';
	}//fin hideFields
}//fin foreach
if(isset($_GET['selectOne']) or isset($_GET['selectMany']))
{
	if(isset($_GET['selectOne']))
	{
		if($this->selectField == NULL) { $this->selectField = $this->idField; }
		$val="";
		$i=0;
		$exps = explode(",", $_GET['selectOne']);
		foreach($exps as $exp)
		{
			
			$obj = explode("->", $exp);
			$val = $val.'selectOne('.$i.",'".$result[$obj[1]]."'".')'.";";
			$i++;
		}
		$html.= '<td><a href="#" onclick="'.$val.'window.close();"><img src="'.$this->myPathShow.'images/ok.png" border="0"></a></td>';
	}
}
else
{
	if (is_array($this->linksInShow)) //agega los links
	{
		foreach ($this->linksInShow as $name=>$attrs) //recorre la matriz de links
		{
			if($attrs['image'])
			{
				$object = '<div align="center"><img src="'.$attrs['image'].'" alt="'.$name.'" border="0" align="center" /></div>';
			}
			else { $object = $name; }
			if($attrs['disabled'])
			{
				if($attrs['disabled']['table'])
				{
					$tableDisabled = new DB($attrs['disabled']['table']);
					$rowDisabled = $tableDisabled->select(array($attrs['disabled']['field']=>$result[$this->idField]));
					//echo $tableDisabled->actualSql."<br>";
					if($tableDisabled->actualNumRows<=0)
					{
						$object = '<div align="center"><img src="'.$attrs['disabled']['image'].'" alt="'.$name.'" border="0" align="center" /></div>';
					}
					else
					{
						$attrs['tooltip'] = " - ".$tableDisabled->actualNumRows." Registros";
					}
				}
			}
			$attrs['others']=str_replace("###", $name.'='.$result[$this->idField], $attrs['others']);
			if(strstr($attrs['link'], "?")) { $equal = "&"; }
			else { $equal = "?"; }
			$html.= '<td align="center"><a id="tip" href="'.$attrs['link'].$equal.$name.'='.$result[$this->idField].'" '.$attrs['others'].' "title="'.$attrs['tooltip'].'" target="'.$attrs['target'].'">'.$object.'</a></td>'; 
		}
	}
	if (is_array($this->insertExternal)) //agega los links
	{
		foreach ($this->insertExternal as $name=>$attrs) //recorre la matriz de links
		{
			$url=str_replace("%value%", $result[$field], $attrs['url']);
			$url=str_replace("%id%", $result[$this->idField], $attrs['url']);
			//echo $attrs['url']."<br>";
			//echo $url."<br>";
			$html.= '<td>';
			$html.= file_get_contents($url);
			$html.= '</td>';
		}
	}
}
$html.= '</tr>';
?>
