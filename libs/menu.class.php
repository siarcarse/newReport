<!--
Date:  21-03-2010
Mod:   001
Author:Sonia Espinoza
Objetive: Busco que sea genérico para incorporar nuevos tipos de menu 
y diseños con sólo pasar a la clase MENU el nombre del nuevo menu

$nameMENU // nombre del menu que recibe como parámetro la clase
$jsFile   // variable usada para la inclusion de los archivos js que se necesiten

MENUES EXISTENTES:

menu  : menú vertical básico requiere ul con id='menu' y li
jdMenu: menú horizontal 

-->

<?php
class MENU
{
	
	var $menu;
	var $nameMenu;
	var $tagValue;
	var $jsFile;
	var $cssFile;
	
	function MENU($nameMENU=NULL)
	{
		
		$this->nameMenu= $nameMENU;
		
		switch ($this->nameMenu)
		{
			case 'menu':
				$this->tagValue = 'id="menu"';
				$this->jsFile = '<script src="/js/menu.js" type="text/javascript"></script>';
				break;
			
			case 'jdMenu':
				$this->tagValue = 'class="jd_menu jd_menu_slate"';
				$this->jsFile = '<script src="js/jdMenu.js" type="text/javascript"></script>';
				$this->cssFile = '<link rel="stylesheet" href="style/jdMenu.css" type="text/css" />
								  <link rel="stylesheet" href="style/jdMenu.slate.css" type="text/css" />';
				break;
			default;
				$this->tagValue = 'id="menu"';
				$this->jsFile = '<script src="/js/menu.js" type="text/javascript"></script>';
		}
	}
	function add($name, $link, $image, $father=NULL, $tooltip=NULL, $style=NULL)
	{
		$this->menu[]=array('name'=>$name, 'link'=>$link, 'image'=>$image, 'father'=>$father, 'tooltip'=>$tooltip, 'style'=>$style);
	}
	function show()
	{
		echo $this->jsFile;
		echo $this->cssFile;
		
		echo '<ul '.$this->tagValue.'>';
		$this->sorter();
		echo '</ul>';
	}
	function sorter($father=NULL, $count=0, $flagUl=FALSE, $flagLi=FALSE)
	{
		foreach ($this->menu as $item)
		{
			if ($item['father']==$father)
			{
				if($count == 0)
				{
					echo '<li>';
					$flagUl = TRUE;
				}
				else
				{
					if($flagUl){ echo '<ul id="'.$style.'">'; $flagUl=FALSE; }
					$liI = '<li>';
					$liF = '</li>';
				}
				if($item['tooltip']!=NULL) { $tooltip = 'title="'.$item['tooltip'].'" '; }
				else { $tooltip = ""; }
				if($item['image']!=NULL){ $image = '<img src="'.$item['image'].'" border="0" align="absmiddle"> ';}
				else { $image=""; }
				echo $liI.'<a href="'.$item['link'].'" id="'.$item['style'].'" '.$tooltip.'target="main">'.$image.str_repeat("", $count).$item['name'].'</a>'.$liF."\n";
				$count++;
				$this->sorter($item['name'], $count, $flagUl);
				$count--;
				if($count == 0)
				{
					echo '</ul></li>';
				}
				
			}
		}
	}
}

?>
