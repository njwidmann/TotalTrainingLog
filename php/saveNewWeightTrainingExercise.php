<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/19/2014
 * Time: 4:54 PM
 */

$username = strval($_POST['username']);
$exerciseString = strval($_POST['exercise']);

$weightTrainingExercise = json_decode($exerciseString);
$exerciseID = $weightTrainingExercise -> exerciseID;
$weightTrainingExerciseName = $weightTrainingExercise -> name;

class saveNewWeightTrainingExercise extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new saveNewWeightTrainingExercise();

$sql = "insert into weightTraining (username, exerciseID, name)" .
    "values ('" . $username . "'," . $exerciseID . ",'" . $weightTrainingExerciseName . "');";

$ret = null;
$running = true;
while ($running) {

    $ret = $db->exec($sql);

    if ($ret) {
        $running = false;
    }
}

//retrieve the id of the set to put back into the array
$sql2 = "select weightTrainingID from weightTraining where exerciseID = " . $exerciseID . ";";

$ret2 = null;
$running = true;
while ($running) {

    $ret2 = $db->query($sql2);

    if ($ret2) {
        $running = false;
    }
}

$weightTrainingIDArray = array();

while ($row = $ret2->fetchArray(SQLITE3_ASSOC)) {
    $weightTrainingIDArray[] = $row['weightTrainingID'];
}

$weightTrainingID = 0;
for ($i = 0; $i < count($weightTrainingIDArray); $i++) {
    if ($weightTrainingIDArray[$i] > $weightTrainingID) {
        $weightTrainingID = $weightTrainingIDArray[$i];
    }
}

$weightTrainingExercise->weightTrainingID = $weightTrainingID;

$weightTrainingSets = $weightTrainingExercise->sets;
for ($setNum = 0; $setNum < count($weightTrainingSets); $setNum++) {
    $weightTrainingSet = $weightTrainingSets[$setNum];
    $weight = $weightTrainingSet -> weight;
    $reps = $weightTrainingSet -> reps;

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

    $weightTrainingExercise->sets[$setNum]->weightTrainingID = $weightTrainingID;
    $weightTrainingExercise->sets[$setNum]->weightTrainingSetID = $weightTrainingSetID;
    $weightTrainingExercise->sets[$setNum]->exerciseID = $exerciseID;


}

$weightTrainingExerciseString = json_encode($weightTrainingExercise);

echo $weightTrainingExerciseString;