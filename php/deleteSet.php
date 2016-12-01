<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/19/2014
 * Time: 11:53 AM
 */

$setID = strval($_POST['setID']);

class deleteSet extends SQLite3
{

    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new deleteSet();


$sql =  "delete from exerciseSets where setID = ".$setID.";".
        "delete from exerciseSetValues where setID = ".$setID.";";


$ret = null;
$running = true;
while ($running) {

    $ret = $db->exec($sql);

    if ($ret) {
        $running = false;
    }
}

if(!$ret){
    echo $db->lastErrorMsg();
} else {
    echo "Set deleted successfully\n";
}


