<?
include("libs/db.class.php");
$db = new DB();
$db2 = new DB();
if($_REQUEST['users']) $data = $db->doSql("SELECT id, study FROM template WHERE users=".$_REQUEST['users']);
else $data = $db->doSql("SELECT id, study FROM template");
do{
        $name = $data['study'];
        $id = $data['id'];
        $history[] = array(
            'id' => $id,
            'name' => $name
        );
}while($data = pg_fetch_assoc($db->actualResults));
echo json_encode($history);
?>