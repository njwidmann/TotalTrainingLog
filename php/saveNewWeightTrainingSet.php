<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/19/2014
 * Time: 5:29 PM
 */

$username = strval($_POST['username']);
$setString = strval($_POST['set']);

$weightTrainingSet = json_decode($setString);
$exerciseID = $weightTrainingSet -> exerciseID;
$weightTrainingID = $weightTrainingSet -> weightTrainingID;
$weight = $weightTrainingSet -> weight;
$reps = $weightTrainingSet -> reps;


class saveNewWeightTrainingSet extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new saveNewWeightTrainingSet();

$sql = "insert into weightTrainingSets (username, exerciseID, weightTrainingID, weight, reps)" .
    "values ('" . $username . "'," . $exerciseID . "," . $weightTrainingID . ",'" . $weight . "','" . $reps . "');";

$ret = null;
$running = true;
while ($running) {

    $ret = $db->exec($sql);

    if ($ret) {
        $running = false;
    }
}


//retrieve the id of the values to put back into the array
$sql2 = "select weightTrainingSetID from weightTrainingSets where weightTrainingID = " . $weightTrainingID . ";";

$ret2 = null;
$running = true;
while ($running) {

    $ret2 = $db->query($sql2);

    if ($ret2) {
        $running = false;
    }
}

$weightTrainingSetIDArray = array();

while ($row = $ret2->fetchArray(SQLITE3_ASSOC)) {
    $weightTrainingSetIDArray[] = $row['weightTrainingSetID'];
}

$weightTrainingSetID = 0;
for ($i = 0; $i < count($weightTrainingSetIDArray); $i++) {
    if ($weightTrainingSetIDArray[$i] > $weightTrainingSetID) {
        $weightTrainingSetID = $weightTrainingSetIDArray[$i];
    }
}

$weightTrainingSet->weightTrainingID = $weightTrainingID;
$weightTrainingSet->weightTrainingSetID = $weightTrainingSetID;
$weightTrainingSet->exerciseID = $exerciseID;

$setString = json_encode($weightTrainingSet);

echo $setString;