<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/19/2014
 * Time: 11:53 AM
 */

$exerciseID = strval($_POST['exerciseID']);

class deleteExercise extends SQLite3
{

    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new deleteExercise();


$sql =  "delete from exercises where exerciseID = ".$exerciseID.";".
        "delete from exerciseSets where exerciseID = ".$exerciseID.";".
        "delete from exerciseLabels where exerciseID = ".$exerciseID.";".
        "delete from exerciseSetValues where exerciseID = ".$exerciseID.";".
        "delete from weightTraining where exerciseID = ".$exerciseID.";".
        "delete from weightTrainingSets where exerciseID = ".$exerciseID.";";


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
    echo "Exercise deleted successfully\n";
}


