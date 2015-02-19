<?php
header('Content-Type: text/html; charset=utf-8');
//Llama a el archivo de configuracion, y el archivo de lenguajes
include("db.conf.php");
include("db.lang.php");
//comienza la clase DB
class DB
{
//variables
var $tableSchema; //matriz donde queda almacenada la estructura de la tabla y sus atributos: tabla.campo=>atributos
var $tableName; //guarda el nombre de la tabla
var $itemTemp; //booleano que hace que se almacenen los registros en variables session antes de ser almacenadas en la tabla
var $relations; //matriz que almecena las tablas relacionadas
var $actualResults; //devuelve el ultimo resultado en forma de matriz asociativa
var $actualSql; // almacena el sql generado
var $actualNumRows; //cantidad de registros de una consulta
var $additionsNames; //almacena en una matriz los nombres de los campos, de las tablas relacionadas
var $idField; //guarda el campo primario de la tabla
var $formObjectChanges; //matriz que almecena los objetos de formulario que se desean cambiar: caja de texto normal a una oculta o de contraseña
var $formObjectLinks; //matriz que almacena los links que van en los objetos de formulario.
var $linksInShow;//matriz que almacena los links en el showData.
var $controls; //matriz de booleanos para el dibujo de los controles (boton insertar, boton modificar, boton eliminar, etiquetas, alineacion, etc)
var $selectField; //variable que guarda en campo que devuelve el showData.
var $lastId; //devuelve el id del ultimo objeto insertado
var $restrictions; //mantiene restricciones en los datos de las relaciones en el formulario
var $addValuesFormObject; //le agrega valor inicial al campo de texto en el formulario
var $autocomplete; //matriz que almacena los campos autocompletables
var $checkIfExist; //matriz que almacena los campos verificables
var $dbName; //cambia la base de datos si es necesario
var $showObjectChangeIf; //almacena los cambios en el show si existe una logica
var $showObjectChange; //almacena los cambios en el show
var $insertExternal; //matriz que almacena las url externas para la inclusion en showdata
var $orderColumnDataTable; //matriz que contiene el ordenamiento de datatables
var $toolTipInForm; //matriz que almacena tooltips en un objeto de formulario
var $toolTipInShow; //matriz (no asociativa) que almacena los cambios al dataShow en forma de tooltip.
var $designArray; //matriz de diseño
var $colsCount; //cantidad de columnas totales en el cambio de diseño con changeFormDesign
var $myPathForm='../../../';
var $myPathShow='../../';
var $hideFieldsArray= array(); //matriz de campos a esconder en un datatables.
var $toMany; //matriz que almacena las relaciones uno a muchos para la insercion de los registros

	//funcion constructora, se le asigna el nombre de la tabla, y el campo principal.
	//void DB(string tablaAsignada, string campoPrincipal)
	function DB($tableNameAssigned=NULL, $id=NULL, $db=NULL, $itemTemp=FALSE)
	{
		if($tableNameAssigned)
		{
			$this->idField = $id;
			$this->tableName = $tableNameAssigned;
			$this->dbName = $db;
			$this->itemTemp = $itemTemp;
			$conn = $this->connection();
			$this->tableSchema = $this->makeSchema(pg_meta_data($conn, $this->tableName), $this->tableName);
		}
	}
	//funcion que convierte la matriz devuelta por pg_meta_data desde nombre=>atributos a tabla.nombre=>atributos (esto es para tener en el mismo esquema mas de una tabla)
	//array makeSchema(array esquema, string nombredetabla)
	private function makeSchema($schema, $tableName)
	{
		$sql="SELECT table_name, column_name, data_type, udt_name, is_nullable, character_maximum_length FROM information_schema.columns WHERE table_name='$tableName' ORDER BY ordinal_position";
		$conn = $this->connection();
		$result = pg_query($conn, $sql);
		$rows = pg_fetch_assoc($result);
		do
		{
			$field = $rows['column_name'];
			$type = $rows['udt_name'];
			$len = $rows['character_maximum_length'];
			$isNull = $rows['is_nullable'];
			$newSchema["$tableName.$field"] = array('type'=>$type, 'len'=>$len, 'isNull'=>$isNull);
		} while ($rows = pg_fetch_assoc($result));
		/*foreach ($schema as $field=>$attrs)
		{
			$newSchema["$tableName.$field"] = $attrs;
		}*/
		return $newSchema;
	}
	//funcion que borra del esquema ($tableSchema) el o los campos deseados.
	//void exceptions(array excepciones)
	function exceptions($exceptions)
	{
		if($this->itemTemp && $_SESSION[$this->tableName]) // si son registros temporales
		{
			$this->hideFields($exceptions);
		}
		foreach ($exceptions as $exception)
		{
			foreach ($this->tableSchema as $field=>$attrs)
			{
				$tablePath = $this->tableName.".".$exception;
				if ($field == $tablePath) { unset($this->tableSchema[$field]); }
			}
		}
	}
	//funcion que agrega un campo tonto (que no hace nada) especial para colocar valores que no se guardan en la base de datos.
	function fooAdditions($name, $attrs=NULL)
	{
		$this->tableSchema[$name] = $attrs;
		$this->tableSchema[$name]['type'] = "foo";
	}
	//funcion que agrega un nuevo campo de tablas relacionadas en la matriz del esquema
	//void additions(string tablaRelacionada, array camposRelacionados)
	function additions($table, $additions)
	{
		if($table != $this->tableName)
		{
			$conn = $this->connection();
			$relation = $this->makeSchema(pg_meta_data($conn, $table), $table);
			foreach ($additions as $addition=>$name)
			{
				foreach ($relation as $field=>$attrs)
				{
					$tablePath = $table.".".$addition;
					if ($field == $tablePath)
					{
						$this->tableSchema[$tablePath] = $attrs;
						$this->additionsNames[$tablePath] = $name;
					}
				}
			}
		}
		else
		{
			foreach ($additions as $addition=>$name)
			{
				$tablePath = $addition;
				$this->tableSchema[$tablePath] = array('type'=>'numeric');
				$this->additionsNames[$tablePath] = $name;
			}
		}
	}
	//funcion que le pasa los parametros a la variable que almecena las relaciones
	//numeric relation(string tablaRelacionada, string campoDeEstaTabla, string campoDeTablaRelacionada, string nombreAliasDelCampoRelacionado = NULL, array restricciones)
	function relation($tableLinked, $thisField, $fieldLinked, $labelFieldLinked=NULL, $otherItems=NULL, $other=NULL, $newTableName=NULL)
	{
		$id = count($this->relations)+1;
		$this->relations[] = array(
					"id"=>$id,
					"thisTable"=>$this->tableName, 
					"thisField"=>$thisField, 
					"tableLinked"=>$tableLinked, 
					"fieldLinked"=>$fieldLinked,
					"otherItems"=>$otherItems, 
					"other"=>$other,
					"newTableName"=>$newTableName,
					"labelFieldLinked"=>$labelFieldLinked);
		return $id;
	}
	function relationToMany($tableLinked, $tableLinkedId, $fieldLinked, $url)
	{
		$this->fooAdditions($tableLinked, array("isNull"=>"YES"));
		$this->changeFormObject($tableLinked, "relation", NULL, NULL, $url);
		$this->toMany[$tableLinked]['fieldLinked'] = $fieldLinked;
		$this->toMany[$tableLinked]['tableLinkedId'] = $tableLinkedId;
	}
	function reOrder($field, $index)//field -> campo , index -> donde lo quiero poner
	{
		if($this->itemTemp && $_SESSION[$this->tableName]) //temporal
		{
			foreach($_SESSION[$this->tableName] as $id=>$fields)
			{
				foreach($fields as $value=>$attrs)
				{
					if($value==$field)
					{
						$item = $attrs;
					}
				}
				$i=0;
				$newTableSchema=NULL;
				foreach($fields as $value=>$attrs)
				{
					if($i==$index)
					{
						$newTableSchema[$field] = $item;
					}
					if($value!=$field)
					{
						$newTableSchema[$value] = $attrs;
					}
					$i++;
				}
				$_SESSION[$this->tableName][$id] = $newTableSchema;
			}
		}
		else //registros de db
		{
			foreach($this->tableSchema as $value=>$attrs)
			{
				if($value==$field)
				{
					$item = $attrs;
				}
			}
			$i=0;
			$newTableSchema=NULL;
			foreach($this->tableSchema as $value=>$attrs)
			{
				if($i==$index)
				{
					$newTableSchema[$field] = $item;
				}
				if($value!=$field)
				{
					$newTableSchema[$value] = $attrs;
				}
				$i++;
			}
			$this->tableSchema = $newTableSchema;
		}
	}
	//Funcion que realiza la coneccion a postgres
	//connect connection(void)
	private function connection()
	{
		//coloca en una variable los parametros de la configuracion
		global $hostname, $port, $dbname, $username, $password;
		if($this->dbName!=NULL)	$dbname = $this->dbName;		
		$params = "host=$hostname port=$port dbname=$dbname user=$username password=$password";
		$conn = pg_connect($params);
		return $conn;
	}
	//funcion que realiza un select a los campos que estan en ese momento en el esquema. si existe una relacion hace el vinculo a nivel sql. devuelve un arreglo con los registros
	//array function select(array condiciones=NULL, array ordenamiento=NULL, int limiteFinal=NULL, int limiteInicial=NULL)
	function select($wheres=NULL, $orders=NULL, $groups=NULL, $limit=NULL, $offset=NULL)
	{
		if (is_array($this->relations)) //si existe un objeto en la matriz de relaciones
		{
			foreach ($this->relations as $object)//recorre las relaciones
			{
				$thisTable = $object['thisTable'];
				$thisField = $object['thisField'];
				$tableLinked = $object['tableLinked'];
				$fieldLinked = $object['fieldLinked'];
				$newTableName = $object['newTableName'];
				if(!$this->itemTemp) //si no es temporal
				{
					if($newTableName) { $as = "$tableLinked AS $newTableName"; $tableLinked = $newTableName; }
					else { $as=$tableLinked; }
					$leftJoins = $leftJoins." LEFT JOIN $as on $thisTable.$thisField=$tableLinked.$fieldLinked";
				}
			}
		}
		$i = 0;
		foreach ($this->tableSchema as $field=>$attrs)//recorre el esquema
		{
			if (is_array($this->additionsNames))//si existen adiciones
			{
				foreach ($this->additionsNames as $table=>$name)//recorre las adiciones
				{
					if ($field == $table)
					{
						if($this->itemTemp && $_SESSION[$this->tableName])//registros de db
						{
							foreach($_SESSION[$this->tableName] as $id=>$items)
							{
								$db = new DB();
								$sqlTempAddition = "SELECT $field as $name FROM $tableLinked WHERE $fieldLinked=".$items[$thisField];
								$row = $db->doSql($sqlTempAddition);
								if($row)
								{
									$_SESSION[$this->tableName][$id][$name] = $row[$name];
								}
							}
						}
						else //registros temporales
						{
							$field = $field." as ".$name;
						}
					}
				}
			}
			if ($attrs['type']=='date' and !isset($_GET['update']))
			{
				$exp = explode(".", $field);
				$field = "to_char($field, 'DD/MM/YYYY') as ".$exp[1];
			}
			if ($i == 0) { $comma = ""; }
			else { $comma = ", "; }
			$fields = $fields.$comma.$field;
			$i+=1;
		}
		if(is_array($wheres))
		{
			$i = 0;
			foreach ($wheres as $where=>$condition)
			{
				if ($i == 0) { $and = " WHERE "; }
				else
				{
					if($where==$lastWhere) { $and=" OR"; }
					else { $and = " AND"; }
				}
				if (strstr($condition, "%"))
				{
					$equal = " LIKE ";
				}
				elseif($condition == 'null' || $condition == 'NULL' || $condition == null)
				{
					$equal = " IS ";
				}
				else
				{
					if($where) { $equal = "="; }
    				else { $equal=""; }
				}
				$sqlWheres = $sqlWheres.$and." $where$equal$condition";
				$i+=1;
				$lastWhere = $where;
			}
		}
		if(is_array($orders))
		{
			$i = 0;
			foreach ($orders as $order)
			{
				if ($i == 0) { $comma = " ORDER BY "; }
				else { $comma = ", "; }
				$sqlOrders = $sqlOrders.$comma.$order;
				$i+=1;
			}
		}
		if(is_array($groups))
		{
			$i = 0;
			foreach ($groups as $group)
			{
				if ($i == 0) { $comma = " GROUP BY "; }
				else { $comma = ", "; }
				$sqlGroups = $sqlGroups.$comma.$group;
				$i+=1;
			}
		}
		if ($limit != NULL)
		{
			$sqlLimit = " LIMIT $limit";
			if ($offset != NULL)
			{
				$sqlOffset = " OFFSET $offset";
			}
		}
		if(!$this->itemTemp)
		{
			$conn = $this->connection();
			$sql = "SELECT $fields FROM $this->tableName $leftJoins$sqlWheres$sqlOrders$sqlLimit$sqlOffset";
			//echo $sql;
			$this->actualSql = $sql;
			$this->actualResults = pg_query($conn, $sql);
			$this->actualNumRows = pg_num_rows($this->actualResults);
			return pg_fetch_assoc($this->actualResults);
		}
		else
		{
			return $_SESSION[$this->tableName];
		}
	}
	
	private function latin($str)
	{
		if (!isset($GLOBALS["carateres_latinos"])){
			$todas = get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES);
			$etiquetas = get_html_translation_table(HTML_SPECIALCHARS, ENT_NOQUOTES);
			$GLOBALS["carateres_latinos"] = array_diff($todas, $etiquetas);
		}
		$str = strtr($str, $GLOBALS["carateres_latinos"]);
		return $str;
	}
	//funcion que mira si existen remplazo de nombres en las tablas y los campos de esta.
	//string getLenguaje(string texto)
	private function getLenguaje($text)
	{
		global $languaje;
		$word = $this->latin(htmlentities($languaje[$this->tableName][$text]));
		if ($word == "") { return $text; }
		else { return $word; }
	}
	//funcion que muestra (en una tabla) los datos que estan en el esquema, se le pasa como parametro el resultado asociativo de los registros (como el resultado de la funcion select)
	//void showData(array resultado, boolean controles)
	function showData($result, $showControls = TRUE)
	{
		if ($result)
		{
			$html= '<table id="showData" class="showData" align="center" cellspacing="0" cellpadding="0" border="0">';
			$html.= '<thead>';
			$html.= '<tr>';
			if ($showControls == TRUE) { $html.= '<th></th>'; } //si es true construye objetos de formulario
			if($this->itemTemp) { $result = end($_SESSION[$this->tableName]); }
			foreach ($result as $field=>$data) //recorre el resultado y contruye las cabeceras de la tabla (nombre de los campos)
			{
				if(!in_array($field, $this->hideFieldsArray))
				{
					$html.= '<th bgcolor:"black">';
					$html.= $this->getLenguaje($field);
					$html.= '</th>';
				}
			}
			if(isset($_GET['selectOne']) or isset($_GET['selectMany']))
			{
				if(isset($_GET['selectOne']))
				{
					$html.= '<th>';
					$html.= "selec.";
					$html.= '</th>';
				}
			}
			else
			{
				if (is_array($this->linksInShow)) //agega los links
				{
					foreach ($this->linksInShow as $name=>$attrs) //recorre la matriz de links
					{
						if($attrs['title']==NULL)
						{
							$title = $name;
						}
						else
						{
							$title = $attrs['title'];
						}
						$html.= '<th>';
						$html.= $this->getLenguaje($title);
						$html.= '</th>';
					}
				}
				if (is_array($this->insertExternal)) //agega los links
				{
					foreach ($this->insertExternal as $name=>$attrs) //recorre la matriz de links
					{
						$html.= '<th>';
						$html.= $this->getLenguaje($name);
						$html.= '</th>';
					}
				}
			}
			$html.= '</thead>';
			$html.= '<tbody>';
			if($this->itemTemp) // es temporal
			{
				foreach($_SESSION[$this->tableName] as $id=>$result) //recorre los registros de session
				{
					include('core/showData.php');
				}
			}
			else //es desde la bd
			{
				do //recorre el resultado con los datos de cada campo.
				{
					include('core/showData.php');
				} while ($result = pg_fetch_assoc($this->actualResults));
			} //fin else itemTemp
			$html.= '</tbody>';
		}
		$html.= '<INPUT TYPE="hidden" NAME="actValue">';
		$html.= '</table>';
		$html.= '</form>';
		return $html;
	}

	//funcion que muestra el formulario para la inclusion o para la modificacion de un registro. si el parametro es nulo, es para insertar, de lo contrario es para modificar.
	//void showForm(string datoParaModificar= NULL)
	function showForm($data=NULL)
	{
		$this->insertJavaForm();
		echo '<div id="tip">';
		echo '<form id="form_main" name="'.$this->tableName.'" method="post" enctype="multipart/form-data" onSubmit="return false;">';
		echo '<table id="showForm" align="center">';
		$fieldCount=0;
		foreach ($this->tableSchema as $field=>$attrs)//recorre el esquema
		{
			$thisTable = "";
			$thisField = "";
			$tableLinked = "";
			$fieldLinked = "";
			$labelFieldLinked = "";
			$label = "";
			$relationOther = "";
			$otherItems = NULL;
			$simpleField = explode(".", $field); //divide el campo (tabla.campo)
			if ($data == NULL) { $value = ""; }
			else { $value = $data[$simpleField[1]]; } //pasa a value el nombre del campo (sin tabla)
			if(isset($_REQUEST[$simpleField[1]])) { $value = $_REQUEST[$simpleField[1]]; }
			//$value = htmlentities($value);
			$flag = FALSE;
			$restrictionWhere = NULL;
			if (is_array($this->relations)) //si existe una relacion entonces...
			{
				foreach ($this->relations as $object) //recorre las relaciones para ver si existe una igualdad de nombre con el campo en el esquema.
				{
					$thisTable = $object['thisTable'];
					$thisField = $object['thisField'];
					$tableLinked = $object['tableLinked'];
					$fieldLinked = $object['fieldLinked'];
					$labelFieldLinked = $object['labelFieldLinked'];
					$relationId = $object['id'];
					$relationOther = $object['other'];
					$otherItems = $object['otherItems'];
					if($labelFieldLinked != NULL){ $label = NULL; }
					else { $label = array($labelFieldLinked); }
					if ($thisTable.".".$thisField == $field) //si existe la igualdad la bandera es verdadera.
					{
						$flag = TRUE;
						if($this->restrictions[$relationId]!=NULL) { $restrictionWhere=$this->restrictions[$relationId]; }
						break;
					}
				}
			}
			if($this->colsCount)
			{
				if($this->designArray[$field]>=0)
				{
					if($this->designArray[$field]==0)
					{
						$colspan = '';
					}
					else
					{
						$colspanNum = ($this->designArray[$field])+1;
						$colspan = ' colspan="'.$colspanNum.'"';
						//$colspan = ' colspan="'.$this->designArray[$field].'"';
					}
					$fieldCount+=$this->designArray[$field];
					//echo 'fieldCount->'.$fieldCount.'--int->'.$this->designArray[$field].';;';
				}
				if($fieldCount==0) 
				{ 
					echo '<tr id="row_'.str_replace('.', '_', $field).'">'; 
					$rowCount++; 
					//echo '-----salto<br>';
				}
			}
			else
			{
				 echo '<tr id="row_'.str_replace('.', '_', $field).'">';
				 //echo '-------';
			}
			if($this->formObjectChanges[$field]['type']=='editor' || $this->formObjectChanges[$field]['type']=='basicEditor' || $this->formObjectChanges[$field]['type']=='relation')
			{
				$width = 'width="100%"';
			}
			else
			{
				$width = '';
			}
			echo '<td '.$colspan.'><table ID="tb_'.str_replace('.', '_', $field).'" '.$width.'><tr>';
			if($this->formObjectChanges[$field]['type']!='hidden')
			{
				if ($this->toolTipInForm[$field])
				{	
					$toolTip = '<img src="'.$this->myPathForm.'images/info.png" align="absmiddle" title="'.$this->toolTipInForm[$field].'" border="0" />';	
				}
				else
				{
					$toolTip = '';
				}
				echo '<td class="lbl" width="100px" ID="lbl_'.str_replace('.', '_', $field).'">'.$toolTip.' '.$this->getLenguaje($field).'</td>';
			}
			if ($flag == TRUE) // si la bandera de la relacion es verdadera, entonces cambia el campo de texto por un menu desplegable
			{
				$dataForCombo = new DB($tableLinked, $fieldLinked); // crea el esquema de la tabla relacionada
				
				$result = $dataForCombo->select($restrictionWhere, $label); // devuelve los registros
				echo '<td ID="td_'.str_replace('.', '_', $field).'">'.$this->fillComboDB($result, $labelFieldLinked, $fieldLinked, str_replace('.', '_', $field), $dataForCombo->actualResults, $value, $relationOther, $otherItems).'</td>'; // crea el menu desplegable
			}
			else // si la bandera es falsa entonces dibuja el campo de texto.
			{
				$this->makeObjectForm($field, $attrs, $value);
			}
			if (is_array($this->formObjectLinks)) //agega los links
			{
				foreach ($this->formObjectLinks as $name=>$attrs)
				{
					if($attrs['object']==$field)
					{
						if($attrs['image']!=NULL) { $object = '<img src="'.$attrs['image'].'" alt="'.$name.'" '.$attrs['others'].' border=0 />'; }
						else { $object = $name; }
						//if($attrs['others']==NULL) { $popup = 'onclick="jQuery.facebox('."'<iframe src=".$attrs['link']."&change=true  width=100% height=500 scrolling=auto frameborder=0 transparency></iframe>'".')"'; }
						if($attrs['others']==NULL) { $popup = 'onclick="'.$attrs['link'].'"'; }
						else { $popup = $attrs['others']; }
						echo '<td><a href="'.$attrs['link'].'">'.$object.'</a></td>';
					}
				}
			}
			echo '</tr></table></td>';
			if(($fieldCount+1)==$this->colsCount) 
			{ 
				echo '</tr>'; 
				//echo '-entre-';
				$fieldCount = 0; 
			}
			else
			{
				$fieldCount++;
				//echo '-entreElse-';
			}
		}
		echo '<tr><td colspan="2">';
		echo '<INPUT TYPE="hidden" NAME="save">';
		echo '<INPUT TYPE="submit" NAME="saveButton" VALUE="Guardar!" onclick="verify(this.form); return false;">';
		echo '</td></tr>';
		echo '</table>';
		echo '</form>';
		echo '</div>';
	}
	function insertJavaForm()
	{
		include("db.jsForm.php");
		if(is_array($this->autocomplete)) { $this->autocompleteJs(); }
		if(is_array($this->checkIfExist)) { $this->checkIfExistJS(); }
	}
	function makeObjectForm($field, $attrs, $value=NULL, $colspan=NULL)
	{
		$value = $this->latin($value);
		$others = $this->formObjectChanges[$field]['others'];
		if($this->formObjectChanges[$field]['enc']!=NULL) { $value=''; }
		if($this->formObjectChanges[$field]['type']!=NULL && $this->formObjectChanges[$field]['type']!="text")
		{
			if($this->formObjectChanges[$field]['type']=='textarea')
			{
				
			 	echo '<td '.$colspan.' ID="td_'.str_replace('.', '_', $field).'"><textarea  id="myTextarea" NAME="'.str_replace('.', '_', $field).'" '.$others.' cols="50">'.$value.'</textarea></td>';
					?>
					<script src="../js/jquery.elastic-1.2.js" type="text/javascript" charset="utf-8"></script>
					<script type="text/javascript">
						$(document).ready(function(){
							$('#myTextarea').elastic();
						});
					</script>
					<?
			}
			elseif($this->formObjectChanges[$field]['type']=='editor')
			{
				if($this->designArray[$field]==0)
				{
					echo '<tr id="row_'.str_replace('.', '_', $field).'"><td>';
				}
				else
				{
					$colspan = ' colspan="'.$this->colsCount.'"';
					echo '<tr id="row_'.str_replace('.', '_', $field).'"><td '.$colspan.' ID="td_'.str_replace('.', '_', $field).'">';
				}
				include_once($this->myPathShow."tools/ckeditor/ckeditor.php");
				$CKEditor = new CKEditor();
				$CKEditor->basePath = $this->myPathShow.'tools/ckeditor/';
				$CKEditor->editor(str_replace('.', '_', $field), $value);
				/*include_once($this->myPathShow."tools/fckeditor/fckeditor.php");
				$oFCKeditor = new FCKeditor(str_replace('.', '_', $field)) ;
				$oFCKeditor->BasePath = $this->myPathShow.'tools/fckeditor/' ;
				$oFCKeditor->Value = $value ;
				$oFCKeditor->Create() ;*/
				echo '</tr></td>';
			}
			elseif($this->formObjectChanges[$field]['type']=='basicEditor')
			{
				if($this->designArray[$field]==0)
				{
					echo '<tr id="row_'.str_replace('.', '_', $field).'"><td colspan="2">';
				}
				else
				{
					$colspanNum = ($this->designArray[$field])*2+2;
					$colspan = ' colspan="'.$colspanNum.'"';
					echo '<tr id="row_'.str_replace('.', '_', $field).'"><td '.$colspan.' ID="td_'.str_replace('.', '_', $field).'">';
				}
				include_once($this->myPathShow."tools/ckeditor/ckeditor.php");
				$CKEditor = new CKEditor();
				$CKEditor->basePath = $this->myPathShow.'tools/ckeditor/';
				$config['toolbar'] = array(
					array( 'Source', '-', 'Bold', 'Italic', 'Underline', 'Strike' ),
					array( 'Image', 'Link', 'Unlink', 'Anchor' )
				);
				$CKEditor->editor(str_replace('.', '_', $field), $value, $config);
				/*include_once($this->myPathShow."tools/fckeditor/fckeditor.php");
				$oFCKeditor = new FCKeditor(str_replace('.', '_', $field)) ;
				$oFCKeditor->BasePath = $this->myPathShow.'tools/fckeditor/' ;
				$oFCKeditor->Value = $value ;
				$oFCKeditor->ToolbarSet = 'Basic';
				$oFCKeditor->Height = '180';
				$oFCKeditor->Create() ;*/
				echo '</tr></td>';
			}
			elseif($this->formObjectChanges[$field]['type']=='relation')
			{
				if($this->designArray[$field]==0)
				{
					echo '<tr id="row_'.str_replace('.', '_', $field).'"><td colspan="2">';
				}
				else
				{
					$colspanNum = ($this->designArray[$field])*2+2;
					$colspan = ' colspan="'.$colspanNum.'"';
					echo '<tr id="row_'.str_replace('.', '_', $field).'"><td '.$colspan.' ID="td_'.str_replace('.', '_', $field).'">';
				}
				echo '<iframe name="relation" src="'.$others.'" width="100%" height="300" scrolling="auto" frameborder="1" transparency>
			      	<p>Tu navegador no puede usar iframes</p>
					</iframe>';
				echo '</tr></td>';
			}
			elseif($this->formObjectChanges[$field]['type']=='file')
			{
				global $filesPathDirectory;
				echo '<td '.$colspan.' ID="td_'.str_replace('.', '_', $field).'"><INPUT TYPE="file" NAME="'.str_replace('.', '_', $field).'" VALUE="" size="35" '.$others.'></td>';
				if($value!="") { echo '<td><a href="'.$filesPathDirectory.$value.'" target="_blank"><img src="'.$this->myPathForm.'images/download.png" align="absmiddle" border="0" /></a></td>'; }
			}
			elseif($this->formObjectChanges[$field]['type']=='date')
			{
				echo '<td '.$colspan.' ID="td_'.str_replace('.', '_', $field).'"><INPUT TYPE="text" class="date-picker" NAME="'.str_replace('.', '_', $field).'" VALUE="" size="35" '.$others.'></td>';
			}
			elseif($this->formObjectChanges[$field]['type']=='color')
			{
				echo '<td '.$colspan.' ID="td_'.str_replace('.', '_', $field).'"><INPUT TYPE="text" class="color" NAME="'.str_replace('.', '_', $field).'" VALUE="'.$value.'" size="35" '.$others.'></td>';
			}
			elseif($this->formObjectChanges[$field]['type']!='menu' && $this->formObjectChanges[$field]['type']!='textarea') //si existe un cambio el cambio y no es un menu
			{
				if($this->formObjectChanges[$field]['type']=='password') { $inputType='password'; } //si existe un cambio en los objetos de formulario, lo realiza
				if($this->formObjectChanges[$field]['type']=='hidden') { $inputType='hidden'; }
				if($this->formObjectChanges[$field]['type']=='checkbox') { $inputType='checkbox'; }
				if($this->addValuesFormObject[$field] != NULL) { $value = $this->addValuesFormObject[$field]; }
				echo '<td '.$colspan.' ID="td_'.str_replace('.', '_', $field).'"><INPUT TYPE="'.$inputType.'" NAME="'.str_replace('.', '_', $field).'" VALUE="'.$value.'" '.$others.'></td>'; //dibuja el campo de texto
			}
			else
			{
				echo '<td '.$colspan.' ID="td_'.str_replace('.', '_', $field).'">'.$this->fillCombo($this->formObjectChanges[$field]['items'], str_replace('.', '_', $field), $value, $others).'</td>'; // crea el menu desplegable
			}
		}
		else
		{
			if($this->formObjectChanges[$field]['type']!=NULL && $this->formObjectChanges[$field]['type']=="text") { $attrs['type']= "text"; }
			else { $inputType='text'; }
			if($attrs['len']>= 40) { $size = 40; }
			else { $size = $attrs['len']; }
			if($attrs['isNull']== "NO") { 
					$isNull = 'ID="isNull"'; $warning='<img src="'.$this->myPathForm.'images/warning.png" align="absmiddle" title=" - Campo Requerido" border="0" />'; 
			}
			else { $isNull = ''; $warning=''; }
			if($attrs['type']== "date") { $isDate = 'class="date-picker"'; }
			else  { $isDate = '';}
			if($attrs['type']== "int4" || $attrs['type']== "numeric" || $attrs['type']== "int8") { $isNumeric = 'onkeypress="return onlyNumeric(event);"'; }
			else  { $isNumeric = '';}
			if($attrs['type']== "password") { $inputType='password'; }
			if(is_array($this->autocomplete))
			{
				if($this->autocomplete[str_replace('.', '_', $field)] != NULL)
				{
					$isNull = 'ID="'.str_replace('.', '_', $field).'"';
				}
			}
			if(is_array($this->checkIfExist))
			{
				if($this->checkIfExist[str_replace('.', '_', $field)] != NULL)
				{
					$isNull = 'ID="'.str_replace('.', '_', $field).'_chk"';
					$loading = '<span id="'.str_replace('.', '_', $field).'_loading" style="display:none"></span><input id="'.str_replace('.', '_', $field).'_hchk" type="hidden">';
				}
			}
			if($this->addValuesFormObject[$field] != NULL) { $value = $this->addValuesFormObject[$field]; }
			echo '<td '.$colspan.' ID="td_'.str_replace('.', '_', $field).'"><INPUT TYPE="'.$inputType.'" NAME="'.str_replace('.', '_', $field).'" '.$isNull.' VALUE="'.$value.'" SIZE="'.$size.'" maxlength="'.$attrs['len'].'" '.$isDate.' '.$isNumeric.' '.$others.'>'.$warning.$loading.'</td>'; //dibuja el campo de texto
		}
	}
	function restriction($relation, $wheres)
	{
		$this->restrictions[$relation] = $wheres;
	}
	//funcion que llena, muestra y/o selecciona registros de la tabla relacionada en el formulario
	//string fillCombo(array resultado, string campoParaEtiqueta, string campoParaValor, string nombreDelCombo, array resultadoDeLaConeccion, string seleccionado)
	function fillComboDB($data, $label, $value, $name, $result, $selected, $other=NULL, $otherItems=NULL)
	{
		$combo = '<select ID="isNull" name="'.$name.'" '.$other.'>';
		if($otherItems)
		{
			foreach($otherItems as $oValue=>$oLabel)
			{
				$combo = $combo.'<option value="'.$oValue.'">'.$this->latin($oLabel).'</option>';
			}
		}
		do //recorre el resultado y llena el combo
		{
			if ($selected == $data[$value]) { $isSelected = "SELECTED"; } // si el seleccionado es igual al valor entonces selecciona el item.
			else { $isSelected = ""; }
			$combo = $combo.'<option value="'.$data[$value].'" '.$isSelected.'>'.$this->latin($data[$label]).'</option>';
		} while ($data = pg_fetch_assoc($result));
		$combo = $combo.'</select>';
		return $combo;
	}
	//funcion que llena un menu desplegable...
	//string fillCombo(array resultado, string campoParaEtiqueta, string campoParaValor, string nombreDelCombo, string seleccionado)
	function fillCombo($data, $name, $selected, $other=NULL)
	{
		$combo = '<select name="'.$name.'" '.$other.'>';
		foreach($data as $label=>$value)
		{
			if ($selected == $value) { $isSelected = "SELECTED"; } // si el seleccionado es igual al valor entonces selecciona el item.
			else { $isSelected = ""; }
			$combo = $combo.'<option value="'.$value.'" '.$isSelected.'>'.$this->latin($label).'</option>';
		}
		$combo = $combo.'</select>';
		return $combo;
	}
	function insertCombo($data, $label, $value, $name, $selected, $others=NULL)
	{
		$combo = '<select name="'.$name.'" '.$others.' ID="isNull">';
		do //recorre el resultado y llena el combo
		{
			if ($selected == $data[$value]) { $isSelected = "SELECTED"; } // si el seleccionado es igual al valor entonces selecciona el item.
			else { $isSelected = ""; }
			$combo = $combo.'<option value="'.$data[$value].'" '.$isSelected.'>'.$this->latin($data[$label]).'</option>';
		} while ($data = pg_fetch_assoc($this->actualResults));
		$combo = $combo.'</select>';
		return $combo;
	}
	//funcion que inserta los datos del formulario
	//void insertData(boolean mostrarFormularioDespuesDeInsertar = TRUE)
	function insertData($showForm = FALSE, $logDesc=NULL)
	{
		$data = $_REQUEST; //obtiene la matriz de variables enviadas (post o get)
		if (isset($data['save'])) //si la matriz tiene elementos
		{
			$i = 0;
			foreach ($this->tableSchema as $field=>$attrs)
			{
				if($attrs['type']!="foo")
				{
					$simpleData = $data[str_replace('.', '_', $field)];
					if(($attrs['type']=='numeric' || $attrs['type']=='date') && $simpleData=='') { }
					elseif($this->formObjectChanges[$field]['type']=="file" && $_FILES[str_replace('.', '_', $field)]['name'] =='') {}
					else
					{
						if($this->formObjectChanges[$field]['type']=="file")
						{
							global $filesPathDirectory;
							if (is_uploaded_file($_FILES[str_replace('.', '_', $field)]['tmp_name'])) {
							  copy($_FILES[str_replace('.', '_', $field)]['tmp_name'], $filesPathDirectory.$_FILES[str_replace('.', '_', $field)]['name']);
							  $simpleData = $_FILES[str_replace('.', '_', $field)]['name'];
							  $upload = true;
							}
							if(!$upload) {
								echo "El archivo no cumple con las reglas establecidas";
							} 
						}
						if ($i == 0) { $comma = ""; }
						else { $comma = ", "; }
						if($this->formObjectChanges[$field]['enc']=='md5') { $simpleData = md5($simpleData); }
						if ($attrs['type']=='timestamp') { $attrs['type']='date'; }
						if ($attrs['type']=='text') { $attrs['type']='varchar'; }
						if ($attrs['type']=='varchar' || $attrs['type']=='date') { $dataWithType = "'".$simpleData."'"; }
						elseif(($attrs['type']=='numeric' || $attrs['type']=='int4' || $attrs['type']=='int8') && $simpleData=='') { $dataWithType = 'null'; }
						else { $dataWithType = $simpleData; }
						$datas = $datas.$comma.$dataWithType;
						$simpleField = explode(".", $field);
						if($simpleField[1] == "user" || $simpleField[1] == "order") $simpleField[1] = '"'.$simpleField[1].'"';
						$fields = $fields.$comma.$simpleField[1];
						$selectFields[$simpleField[1]] = $dataWithType;
						if($this->itemTemp) {
							$arrayForItemTemp[$simpleField[1]] = $dataWithType;
						}
						$i += 1;
					}
				}
			}
			$sql = 'INSERT INTO '.$this->tableName.'('.$fields.') VALUES('.$datas.')';
			if($this->itemTemp)
			{
				$_SESSION[$this->tableName.'_sql'][] = $sql;
				$itemId = count($_SESSION[$this->tableName]);
				$arrayForItemTemp = array_reverse($arrayForItemTemp, TRUE);
				$arrayForItemTemp[$this->idField] = "$itemId";
				$arrayForItemTemp = array_reverse($arrayForItemTemp, TRUE);
				$_SESSION[$this->tableName][$itemId] = $arrayForItemTemp;
				$flag = TRUE;
				$state = TRUE;
			}
			else
			{
				//$sql = 'INSERT INTO '.$this->tableName.'('.$fields.') VALUES('.$datas.')';
				$conn = $this->connection();
				$state = pg_query($conn, $sql);
				$flag = TRUE;
				//echo $sql;
				$select = new DB($this->tableName, $this->idField);
				$id = $select->select($selectFields, array($this->idField." DESC"));
				$this->lastId = $id[$this->idField];
				/*if($upload)
				{
					$oldfilename = $filesPathDirectory.$_FILES[str_replace('.', '_', $field)]['name'];
					$ext = explode(".", $oldfilename);
					$newfilename = $filesPathDirectory.$this->lastId.".".$ext[count($ext)-1];
					rename($oldfilename, $newfilename);
				}*/
				if($this->toMany)
				{
					$showForm=FALSE;
					$stateCount = 0;
					foreach($this->toMany as $table=>$attrs)
					{
						$fieldLinked = $attrs['fieldLinked'];
						$tableLinkedId = $attrs['tableLinkedId'];
						if($_SESSION[$table])
						{
							foreach($_SESSION[$table] as $id=>$fields)
							{
								$valuesTemp = null;
								$fields[$fieldLinked] = $this->lastId;
								foreach($fields as $field=>$value)
								{
									if(in_array($field, $_SESSION[$table.'_fields']))
									{
										$valuesTemp[] = $value;
									}
								}
								$fieldsToSql = implode(',', $_SESSION[$table.'_fields']);
								//$fieldsToSql = implode(',', $_SESSION[$table.'_fields']).','.$fieldLinked;
								$valuesToSql = implode(',', $valuesTemp).','.$this->lastId;
								echo $valuesToSql;
								$sql = "INSERT INTO $table($fieldsToSql) values($valuesToSql)";
								echo $valuesTemp;
								$conn = $this->connection();
								$stateTemp = pg_query($conn, $sql);
								if(!$stateTemp) { $stateCount++; }
							}
						}
						unset($_SESSION[$table]);
						unset($_SESSION[$table.'_fields']);
						if($stateCount>0)
						{
							echo '<div class="itemTempError">'.$stateCount.'registros no insertados</div>';
						}
					}
				}
			}
		}
		if ($showForm or !isset($data['save']))
		{
			$this->showForm();
			$flag = FALSE;
		}
		else
		{
			if($state!=FALSE)
			{ 
				echo '<p id="ok" align="center" class="insertOk">Item Guardado Con exito!</p><br>';
				include("insertLog.php");
				insertLog($this->tableName, $_SESSION['UserId'], $this->lastId, "i", $logDesc);
			}
			else
			{
				echo '<p align="center" class="insertError">Error al Modificar el item: <br>'.pg_last_error().'</p><br>';
			}
		}
		return $flag;
	}
	//funcion que borra registros
	//void deleteData(array datos_a_borrar)
	function deleteData($data, $logDesc=NULL)
	{
		if (count($data) > 0)
		{
			if($this->itemTemp)
			{
				foreach($data['checkbox'] as $value)
				{
					unset($_SESSION[$this->tableName][$value]);
				}
				return TRUE;
			}
			else
			{
				if($this->tableSchema["$this->tableName.$this->idField"]['type']=="varchar")
				{
					foreach($data['checkbox'] as $value)
					{
						$newData[] = "'".$value."'";
					}
				}
				else { $newData = $data['checkbox']; }
				$inWhere = implode(",", $newData);
				if ($inWhere != "")
				{
					$sql = 'DELETE FROM '.$this->tableName.' WHERE '.$this->idField.' IN ('.$inWhere.')';
					$conn = $this->connection();
					if(pg_query($conn, $sql))
					{
						include("insertLog.php");
						foreach($data['checkbox'] as $value)
						{
							insertLog($this->tableName, $_SESSION['UserId'], $value, "d", $logDesc);
						}
						return TRUE;
					}
					else { return FALSE; }
				}
			}
		}
	}
	//funcion que modifica registros
	//void updateData(int numeroDeRegistro, boolean mostrarFormularioDespuesDeInsertar = TRUE)
	function updateData($id, $showForm = TRUE, $logDesc=NULL)
	{
		$data = $_REQUEST;
		if (isset($data['save']))
		{
			$i = 0;
			foreach ($this->tableSchema as $field=>$attrs)
			{
				if($attrs['type']!="foo")
				{
					$simpleData = $data[str_replace('.', '_', $field)];
					if($attrs['type']=='date' && $simpleData == ''){}
					elseif($this->formObjectChanges[$field]['type']=="file" && $_FILES[str_replace('.', '_', $field)]['name'] =='') {}
					else
					{
						if($this->formObjectChanges[$field]['type']=="file")
						{
							global $filesPathDirectory;
							if (is_uploaded_file($_FILES[str_replace('.', '_', $field)]['tmp_name'])) {
							  copy($_FILES[str_replace('.', '_', $field)]['tmp_name'], $filesPathDirectory.$_FILES[str_replace('.', '_', $field)]['name']);
							  $simpleData = $_FILES[str_replace('.', '_', $field)]['name'];
							  $upload = true;
							}
							if(!$upload) {
								echo "El archivo no cumple con las reglas establecidas";
							} 
						}
						if ($i == 0) { $comma = ""; }
						else { $comma = ", "; }
						if($this->formObjectChanges[$field]['enc']=='md5') { $simpleData = md5($simpleData); }
						if ($attrs['type']=='timestamp') { $attrs['type']='date'; }
						if ($attrs['type']=='text') { $attrs['type']='varchar'; }
						if ($attrs['type'] == 'varchar') { $dataWithType = "'".$simpleData."'"; }
						elseif ($attrs['type'] == 'date') { $dataWithType = "'".$simpleData."'"; }
						elseif(($attrs['type']=='numeric' || $attrs['type']=='int4' || $attrs['type']=='int8') && $simpleData=='') { $dataWithType = 'null'; }
						else { $dataWithType = $simpleData; }
						$simpleField = explode(".", $field);
						if($simpleField[1] == "user" || $simpleField[1] == "order") $simpleField[1] = '"'.$simpleField[1].'"';
						$datas = $datas.$comma.$simpleField[1].'='.$dataWithType;
						if($this->itemTemp) {
							$arrayForItemTemp[$simpleField[1]] = $dataWithType;
						}
						$i += 1;
					}
				}
			}
			if($this->itemTemp)
			{
				$arrayForItemTemp = array_reverse($arrayForItemTemp, TRUE);
				$arrayForItemTemp[$this->idField] = "$id";
				$arrayForItemTemp = array_reverse($arrayForItemTemp, TRUE);
				$_SESSION[$this->tableName][$id] = $arrayForItemTemp;
				$flag = TRUE;
				$state = TRUE;
			}
			else
			{
				$sql = 'UPDATE '.$this->tableName.' SET '.$datas.' WHERE '.$this->idField.'='.$id;
				$conn = $this->connection();
				$state = pg_query($conn, $sql);
				$flag = TRUE;
				$this->lastId = $id;
				include("insertLog.php");
				insertLog($this->tableName, $_SESSION['UserId'], $this->lastId, "u", $logDesc);
			}
			//echo $sql;
		}
		if ($showForm or !isset($data['save']))
		{
			if($this->itemTemp)
			{
				$select = $_SESSION[$this->tableName][$id];
			}
			else
			{
				$dataForForm = new DB($this->tableName, $this->idField);
				$select = $dataForForm->select(array($this->idField=>$id));
			}
			$this->showForm($select);
			$flag = FALSE;
		}
		else
		{
			if($state!=FALSE){ echo '<p align="center" id="ok" style="background:#FAFFCF url('.$this->myPathForm.'images/ok.png) no-repeat 40px 7px;margin:40px auto; width:300px; height:25px; font-size:11px;  font-weight:bold; padding-top:10px; border:2px solid #0d6f1c; -moz-border-radius:5px; text-align:center;">Item Modificado Con exito!</p><br>';}
			else echo '<h1>Error al Modificar el item: <br>'.pg_last_error().'</h1><br>';
		}
		return $flag;
	}
	
	function addValueFormObject($object, $value)
	{
		$this->addValuesFormObject[$object] = $value;
	}
	//funcion que cambia un campo de texto en otro, y le agrega encriptacion-
	//void changeFormObject(string NombreDelObjetoDeFormulario, string nuevoTipo, string encriptacion=NULL, array items, string atributos de campo)
	function changeFormObject($object, $type, $enc=NULL, $items=NULL, $others=NULL)
	{
		$this->formObjectChanges[$object]['type'] = $type;
		$this->formObjectChanges[$object]['enc'] = $enc;
		$this->formObjectChanges[$object]['items'] = $items;
		$this->formObjectChanges[$object]['others'] = $others;
	}
	//funcion que coloca un link (que puede ser una imagen) al lado de un objeto de formulario
	//void insertLinkInFormObject(string objeto, string nombre, string link, string imagen=NULL)
	function insertLinkInFormObject($object, $name, $link, $image=NULL, $others=NULL)
	{
		$this->formObjectLinks[$name]['object'] = $object;
		$this->formObjectLinks[$name]['link'] = $link;
		$this->formObjectLinks[$name]['image'] = $image;
		$this->formObjectLinks[$name]['others'] = $others;
	}
	//esconde uno o mas campos en el datatables.
	function hideFields($fields)
	{
		$this->hideFieldsArray = $fields;
	}
	//funcion que inserta en los registros links (que puede ser una imagen) al lado de cada registro, con el valor del campo principal con el nombre de variable. ej link?nombre=campoprincipal
	//void insertLinkInShow(string nombre, string link, string imagen=NULL, string otros(onclick, etc)=NULL)
	function insertLinkInShow($name, $link, $image=NULL, $others=NULL, $title=NULL, $tooltip=NULL, $disabled=NULL, $target='main')
	{
		$this->linksInShow[$name]['link'] = $link;
		$this->linksInShow[$name]['image'] = $image;
		$this->linksInShow[$name]['others'] = $others;
		$this->linksInShow[$name]['title'] = $title;
		$this->linksInShow[$name]['tooltip'] = $tooltip;
		$this->linksInShow[$name]['target'] = $target;
		$this->linksInShow[$name]['disabled']['table'] = $disabled['table'];
		$this->linksInShow[$name]['disabled']['field'] = $disabled['field'];
		$this->linksInShow[$name]['disabled']['image'] = $disabled['image'];
	}
	function showControls($labels = TRUE, $align="center")
	{
		$this->controls['labels'] = $labels;
		$this->controls['align'] = $align;
		
		include("db.jsShow.php");

		echo '<form id="form_main" name='.$this->tableName.' method="post" action="" onSubmit="return false;" >'; //si es true construye objetos de formulario
		$this->drawControls();
	}
	function control($name, $link=NULL, $tip=NULL)
	{
		$i = count($this->controls['schema'])+1;
		$this->controls['schema'][$i]['control'] = $name;
		$this->controls['schema'][$i]['link'] = $link;
		$this->controls['schema'][$i]['tooltip'] = $tip;
	}
	private function drawControls()
	{
		require_once("db.controls.php");
	}

	function insertLinkfoot($name, $link, $image=NULL, $others=NULL){
		echo "<tr><td><img src=$image><a href=$link>$name</a></td>";
	
	}
	private function autocompleteJs()
	{
		echo '
		<script>
		function findValue(li) {
			if( li == null ) return alert("No match!");
			if( !!li.extra ) var sValue = li.extra[0];
			else var sValue = li.selectValue;

			//alert("The value you selected was: " + sValue);
		}

		function selectItem(li) {
			findValue(li);';
		foreach($this->autocomplete as $object=>$attrs)
		{
			$exps = explode(",", $attrs['values']);
			foreach($exps as $exp)
			{
				$obj = explode("->", $exp);
				$val[] = $obj[0];
			}
			$i = -1;
			foreach ($val as $v)
			{
				if($v!=NULL and $i!=-1)
				{
					echo "\n".'			document.'.$v.'.value = li.extra['.$i.'];';
				}
				$i++;
			}
		}
		echo '
		}

		function formatItem(row) {';
		foreach($this->autocomplete as $object=>$attrs)
		{
			$i = 0;
			$exps = explode(",", $attrs['values']);
			foreach($exps as $exp)
			{
				$obj = explode("->", $exp);
				$val[] = $obj[0];
				$format = $format.'" - "+row['.$i.']+';
				$i++;
			}
			echo "\n".'			return '.$format.'"!";';
		}
		echo '
		}

		function lookupAjax(){';
		foreach($this->autocomplete as $object=>$attrs)
		{
			echo "\n".'			var oSuggest = $("#'.$object.'")[0].autocompleter;';
		}
		echo '
			oSuggest.findValue();
			return false;
		}

		$(document).ready(function() {';
		foreach($this->autocomplete as $object=>$attrs)
		{
			echo'
			$("#'.$object.'").autocomplete(
				"'.$this->myPathShow.'js/autocomplete/autocomplete.php?table='.$attrs["table"].'&values='.$attrs["values"].'",
				{
					delay:10,
					minChars:2,
					matchSubset:1,
					matchContains:1,
					cacheLength:10,
					onItemSelect:selectItem,
					onFindValue:findValue,
					formatItem:formatItem,
					autoFill:true
				}
			);';
		}
		echo'
		});
		</script>';
	}
	function autocomplete($object, $table, $valuesField)
	{
		$this->autocomplete[$object]['table'] = $table;
		$this->autocomplete[$object]['values'] = $valuesField;
	}
	function checkIfExistJS()
	{
		foreach($this->checkIfExist as $object=>$attrs)
		{
			if($attrs['persistant'])
			{
				$persistant = '$("#'.$object.'_chk").focus();';
			}
			echo '
		<script>
		$(document).ready(function()
		{
			$("#'.$object.'_chk").blur(function()
			{
			 $("#'.$object.'_loading").removeClass().addClass('."'messagebox'".').text("Verificando").fadeIn("slow");
			 $.post("../../js/check/check.php",{ value:$(this).val(), table:"'.$attrs['table'].'", field:"'.$attrs['field'].'", rest:"'.$attrs['restrictions'].'" } ,function(data)
			 {
			  if(data==0)
			  {
		  		console.log($( ":submit" ));
		  		$("input[type=submit]").attr("disabled", "disabled");
			   $("#'.$object.'_loading").fadeTo(200,0.1,function()
			   {
			    $(this).html("'.$attrs['message2'].'").addClass("'.$attrs['messageboxerror'].'").fadeTo(900,1);
			   });
			   '.$persistant.'
			  }
			  else
			  {
			  	$("input[type=submit]").removeAttr("disabled");
			   $("#'.$object.'_loading").fadeTo(200,0.1,function()
			   {
			    $(this).html("'.$attrs['message1'].'").addClass("'.$attrs['messageboxok'].'").fadeTo(900,1);
			   });
			  }
			  $("#'.$object.'_hchk").val(data);
			 });
			});
		});
		</script>';
		}

	}
	// void funcion checkItemIfExist($object as string, $table as string, $field as string, $restrictions as array = NULL)
	function checkItemIfExist($object, $table, $field, $restrictions=NULL, $message1='OK!!', $message2='Duplicado', $invertColor=FALSE, $persistant=FALSE)
	{
		$this->checkIfExist[$object]['table'] = $table;
		$this->checkIfExist[$object]['field'] = $field;
		$this->checkIfExist[$object]['restrictions'] = $restrictions;
		$this->checkIfExist[$object]['message1'] = $message1;
		$this->checkIfExist[$object]['message2'] = $message2;
		$this->checkIfExist[$object]['persistant'] = $persistant;
		$this->checkIfExist[$object]['messageboxerror'] = 'messageboxerror';
		$this->checkIfExist[$object]['messageboxok'] = 'messageboxok';
		if($invertColor!=FALSE)
		{
			$this->checkIfExist[$object]['messageboxerror'] = 'messageboxok';
			$this->checkIfExist[$object]['messageboxok'] = 'messageboxerror';
		}
			
	}
	// array function doSql($sql as string)
	function doSql($sql)
	{
		$conn = $this->connection();
		$this->actualSql = $sql;
		$this->actualResults = pg_query($conn, $sql);
		$this->actualNumRows = pg_num_rows($this->actualResults);
		return pg_fetch_assoc($this->actualResults);
	}
	/*function query($sql, $conn = FALSE){
		if($conn == FALSE)
			$conn = $this->connection();
		return pg_query($conn, $sql);
	}
	function rowDoSql($result)
	{
		return pg_fetch_assoc($result);
	}*/
	function changeItemInShowIf($field, $logic, $compare, $objectToChange, $attrib)
	{
		$i = count($this->showObjectChangeIf[$field])+1;
		$this->showObjectChangeIf[$field][$i]['logic']=$logic;
		$this->showObjectChangeIf[$field][$i]['compare']=$compare;
		$this->showObjectChangeIf[$field][$i]['object']=$objectToChange;
		$this->showObjectChangeIf[$field][$i]['attrib']=$attrib;
	}
	function changeItemInShow($field, $attrib)
	{
		$this->showObjectChange[$field]['attrib']=$attrib;
	}
	function insertExternalInShow($name, $url)
	{
		$this->insertExternal[$name]['url']=$url;
	}
	function setOrderColumnData($field, $way="desc")
	{
		$i=0;
		foreach($this->tableSchema as $value=>$attrs)
		{
			if($value == $this->tableName.".".$field) $index = $i;
			$i++;
		}
		$this->orderColumnDataTable['index'] = $index+1;
		$this->orderColumnDataTable['way'] = $way;
	}
	function getOrderColumnData($type)
	{
		if($this->orderColumnDataTable==NULL)
		{
			$this->orderColumnDataTable['index'] = 1;
			$this->orderColumnDataTable['way'] = "desc";
		}
		return $this->orderColumnDataTable[$type];
	}
	function toolTipInFormObject($object, $text)
	{
		$this->toolTipInForm[$object] = $text;
	}
	function toolTipInShow($field, $link=NULL, $image=NULL, $backImage=NULL)
	{
		$this->toolTipInShow[$field]['flag'] = TRUE;
		$this->toolTipInShow[$field]['link'] = $link;
		$this->toolTipInShow[$field]['image'] = $image;
		$this->toolTipInShow[$field]['backImage'] = $backImage;
	}
	function changeFormDesign($array)
	{
		$i=0;
		foreach($array as $cols)
		{
			foreach($cols as $item)
			{
				if($item)
				{
					$this->designArray[$item]=0;
					$itemAux = $item;
					$this->reOrder($item, $i);
					$i++;
				}
				else
				{
					$this->designArray[$itemAux]++;
				}
			}
			$this->colsCount = count($cols);
		}
	}
}
?>
