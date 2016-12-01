<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 9/24/2014
 * Time: 6:41 PM
 */

$q = strval($_GET['q']);

class getMaster extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new getMaster();

/* if(!$db){
    echo $db->lastErrorMsg();
} else {
    echo "Opened database successfully\n";
}*/

$sql = "select master from users where user_name = '".$q."';";


$ret = null;
$running = true;
while ($running) {

    $ret = $db->query($sql);

    if ($ret) {
        $running = false;
    }
}

while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
    echo $row['master'];
}