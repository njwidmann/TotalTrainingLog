<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/17/2014
 * Time: 1:18 PM
 */

$username = strval($_GET['username']);
$exerciseString = strval($_GET['exercise']);

$exercise = json_decode($exerciseString);

$labels = $exercise -> labels;

$numLabels = count($labels);


class saveNewExerciseLabels extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new saveNewExerciseLabels();

for($i = 0; $i < $numLabels; $i++) {
    $exerciseID = $labels[$i]->exerciseID;
    $labelValue = $labels[$i]->value;

    $sql = "insert into exerciseLabels (username, exerciseID, label)".
        "values ('".$username."',".$exerciseID.",'".$labelValue."');";

    $ret = $db->exec($sql);
}





//retrieve the id of the exercise to put back into the array
$sql2 = "select labelID from exerciseLabels where exerciseID = ".$exerciseID.";";

$ret2 = $db->query($sql2);

$retArray = array();

while($row = $ret2->fetchArray(SQLITE3_ASSOC) ) {
    $retArray[] = $row['labelID'];
}

/*$latestSetID = 0;
for($i = 0; $i < count($retArray); $i++) {
    if($retArray[$i] > $latestExerciseID) {
        $latestExerciseID = $retArray[$i];
    }
}*/

$labelIDsArray = json_encode($retArray);

echo $labelIDsArray;