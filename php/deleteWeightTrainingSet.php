<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/19/2014
 * Time: 5:55 PM
 */

$weightTrainingSetID = strval($_POST['weightTrainingSetID']);

class deleteWeightTrainingSet extends SQLite3
{

    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new deleteWeightTrainingSet();

$sql = "delete from weightTrainingSets where weightTrainingSetID = ".$weightTrainingSetID.";";


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
    echo "weight training set deleted successfully\n";
}