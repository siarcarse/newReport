<?
function insertLog($table, $users, $id, $type, $desc="")
{
	$sql="INSERT INTO logs(time, date, users, tablefrom, tableid, type, description, host) VALUES('".date("H:i")."', '".date("Y-m-d")."', ".$users.", '".$table."', ".$id.", '".$type."', '".$desc."', '".$_SERVER['REMOTE_ADDR']."')";
	$log = new DB("logs", "id");
	$log->doSql($sql);
	//echo $sql;
}
?>
