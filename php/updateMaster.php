<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 9/24/2014
 * Time: 6:46 PM
 */

$q = strval($_POST['q']);
$u = strval($_POST['u']);

class updateMaster extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new updateMaster();

/* if(!$db){
    echo $db->lastErrorMsg();
} else {
    echo "Opened database successfully\n";
}*/

$sql = "update users set master = '".$q."' where user_name = '".$u."';";


$ret = null;
$running = true;
while ($running) {

    $ret = $db->exec($sql);

    if ($ret) {
        $running = false;
    }
}

