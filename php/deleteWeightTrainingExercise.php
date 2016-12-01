<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/19/2014
 * Time: 5:50 PM
 */

$weightTrainingID = strval($_POST['weightTrainingID']);

class deleteWeightTrainingExercise extends SQLite3
{

    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new deleteWeightTrainingExercise();

$sql =  "delete from weightTraining where weightTrainingID = ".$weightTrainingID.";".
    "delete from weightTrainingSets where weightTrainingID = ".$weightTrainingID.";";


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
    echo "weight training exercise deleted successfully\n";
}

