<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/13/2014
 * Time: 2:29 PM
 */

$username = strval($_GET['username']);
$year = strval($_GET['year']);
$month = strval($_GET['month']);
$weeksString = strval($_GET['weeksString']);

class retrieveDays extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new retrieveDays();

$weeksArray = json_decode($weeksString);
$lastDay = $weeksArray[count($weeksArray) - 1]->days[count($weeksArray[count($weeksArray) - 1]->days) - 1]->date;


for($weekNum = 0; $weekNum < count($weeksArray); $weekNum++) {

    for($dayNum = 0; $dayNum < count($weeksArray[$weekNum]->days); $dayNum++) {

        $date = $weeksArray[$weekNum]->days[$dayNum]->date;
            //retrieve day IDs to assign to day objects in the days array (nested in the weeks array)
        $sql = "select id from days where year = ".$year." and month = ".$month." and day = ".$date.";";

        $ret = null;
        $running = true;
        while ($running) {

            $ret = $db->query($sql);

            if ($ret) {
                $running = false;
            }
        }

        while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
            $dayID = $row['id'];
            $weeksArray[$weekNum]->days[$dayNum]->dayID = $dayID;

        }
            //retrieve exercise data
        $sql = "select name, exerciseID, weightTrainingBool, notes, planned from exercises where username = '".$username.
            "' and dayID = ".$dayID.";";

        $ret = null;
        $running = true;
        while ($running) {

            $ret = $db->query($sql);

            if ($ret) {
                $running = false;
            }
        }

        $exerciseNameArray = array();
        $exerciseIDArray = array();
        $weightTrainingBoolArray = array();
        $notesArray = array();
        $plannedArray = array();
        while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
            $exerciseNameArray[] = $row['name'];
            $exerciseIDArray[] = $row['exerciseID'];
            $weightTrainingBoolArray[] = $row['weightTrainingBool'];
            $plannedArray[] = $row['planned'];
            if(empty($row['notes'])) {
                $notesArray[] = "Notes...";
            } else {
                $notesArray[] = $row['notes'];
            }

        }


        for($exerciseNum = 0; $exerciseNum < count($exerciseIDArray); $exerciseNum++) {
            $exerciseID = $exerciseIDArray[$exerciseNum];
            $weightTrainingBool = $weightTrainingBoolArray[$exerciseNum];
            $notes = $notesArray[$exerciseNum];
            $planned = $plannedArray[$exerciseNum];

            if($notes == null || $notes == "null") {
                $notes == "Notes...";
            }

            if($planned == 0) {
                $planned = false;
            } elseif ($planned == 1) {
                $planned = true;
            }


            if ($weightTrainingBool == "false" || $weightTrainingBool == false) {
                //loop through exercise arrays and assign values to exercise objects
                $exercise = (object)array(
                    'name' => $exerciseNameArray[$exerciseNum],
                    'labels' => [],
                    'sets' => [],
                    'notes' => $notes,
                    'editing' => false,
                    'planned' => $planned,
                    'weightTraining' => false,
                    'exerciseID' => $exerciseID,
                    'dayID' => $dayID);

                $weeksArray[$weekNum]->days[$dayNum]->exercises[$exerciseNum] = $exercise;

                //load labels in arrays
                $sql = "select labelID, label from exerciseLabels where username = '" . $username .
                       "' and exerciseID = " . $exerciseID . ";";

                $ret = null;
                $running = true;
                while ($running) {

                    $ret = $db->query($sql);

                    if ($ret) {
                        $running = false;
                    }
                }

                $exerciseLabelIDArray = array();
                $exerciseLabelValueArray = array();

                while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
                    $exerciseLabelIDArray[] = $row['labelID'];
                    $exerciseLabelValueArray[] = $row['label'];
                }
                //loop through arrays and assign values to label objects
                for ($labelNum = 0; $labelNum < count($exerciseLabelIDArray); $labelNum++) {

                    $labelID = $exerciseLabelIDArray[$labelNum];

                    $label = (object)array(
                        'value' => $exerciseLabelValueArray[$labelNum],
                        'ID' => $exerciseLabelIDArray[$labelNum],
                        'exerciseID' => $exerciseID,
                        'setID' => null);

                    $weeksArray[$weekNum]->days[$dayNum]->exercises[$exerciseNum]->labels[$labelNum] = $label;
                }
                    //load sets from db
                $sql = "select setID from exerciseSets where username = '" . $username .
                    "' and exerciseID = " . $exerciseID . ";";

                $ret = null;
                $running = true;
                while ($running) {

                    $ret = $db->query($sql);

                    if ($ret) {
                        $running = false;
                    }
                }

                $exerciseSetIDArray = array();

                while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
                    $exerciseSetIDArray[] = $row['setID'];
                }

                for($setNum = 0; $setNum < count($exerciseSetIDArray); $setNum++) {
                    $setID = $exerciseSetIDArray[$setNum];

                    $set = (object)array(
                        'values' => [],
                        'exerciseID' => $exerciseID,
                        'setID' => $setID);

                    $weeksArray[$weekNum]->days[$dayNum]->exercises[$exerciseNum]->sets[$setNum] = $set;

                    //load set values from db
                    $sql = "select valueID, value from exerciseSetValues where username = '" . $username .
                        "' and setID = " . $setID . ";";

                    $ret = null;
                    $running = true;
                    while ($running) {

                        $ret = $db->query($sql);

                        if ($ret) {
                            $running = false;
                        }
                    }

                    $setValueIDArray = array();
                    $setValueValueArray = array();

                    while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
                        $setValueIDArray[] = $row['valueID'];
                        $setValueValueArray[] = $row['value'];
                    }

                    for($valueNum = 0; $valueNum < count($setValueIDArray); $valueNum++) {
                        $valueID = $setValueIDArray[$valueNum];
                        $valueValue = $setValueValueArray[$valueNum];

                        $value = (object)array(
                            'value' => $valueValue,
                            'ID' => $valueID,
                            'exerciseID' => $exerciseID,
                            'setID' => $setID );

                        $weeksArray[$weekNum]->days[$dayNum]->exercises[$exerciseNum]->sets[$setNum]->values[$valueNum] = $value;
                    }
                }


            } else {
                //if weightTraining = true

                $exercise = (object)array(
                    'name' => $exerciseNameArray[$exerciseNum],
                    'exercises' => [],
                    'editing' => false,
                    'notes' => $notes,
                    'planned' => $planned,
                    'weightTraining' => true,
                    'exerciseID' => $exerciseID,
                    'dayID' => $dayID);

                $weeksArray[$weekNum]->days[$dayNum]->exercises[$exerciseNum] = $exercise;

                //load sets from db
                $sql = "select weightTrainingID, name from weightTraining where username = '" . $username .
                    "' and exerciseID = " . $exerciseID . ";";

                $ret = null;
                $running = true;
                while ($running) {

                    $ret = $db->query($sql);

                    if ($ret) {
                        $running = false;
                    }
                }

                $weightTrainingIDArray = array();
                $weightTrainingExerciseNameArray = array();

                while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
                    $weightTrainingIDArray[] = $row['weightTrainingID'];
                    $weightTrainingExerciseNameArray[] = $row['name'];
                }

                for($weightTrainingExerciseNum = 0; $weightTrainingExerciseNum < count($weightTrainingIDArray); $weightTrainingExerciseNum++) {
                    $weightTrainingID = $weightTrainingIDArray[$weightTrainingExerciseNum];
                    $weightTrainingExerciseName = $weightTrainingExerciseNameArray[$weightTrainingExerciseNum];

                    $weightTrainingExercise = (object)array(
                        'name' => $weightTrainingExerciseName,
                        'weightTrainingID' => $weightTrainingID,
                        'exerciseID' => $exerciseID,
                        'sets' => [] );

                    $weeksArray[$weekNum]->days[$dayNum]->exercises[$exerciseNum]->exercises[$weightTrainingExerciseNum] = $weightTrainingExercise;

                    //load set values from db
                    $sql = "select weightTrainingSetID, weight, reps from weightTrainingSets where username = '" . $username .
                        "' and weightTrainingID = " . $weightTrainingID . ";";

                    $ret = null;
                    $running = true;
                    while ($running) {

                        $ret = $db->query($sql);

                        if ($ret) {
                            $running = false;
                        }
                    }

                    $weightTrainingSetIDArray = array();
                    $weightsArray = array();
                    $repsArray = array();

                    while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
                        $weightTrainingSetIDArray[] = $row['weightTrainingSetID'];
                        $weightsArray[] = $row['weight'];
                        $repsArray[] = $row['reps'];
                    }

                    for($weightTrainingSetNum = 0; $weightTrainingSetNum < count($weightTrainingSetIDArray); $weightTrainingSetNum++) {
                        $weightTrainingSetID = $weightTrainingSetIDArray[$weightTrainingSetNum];
                        $weight = $weightsArray[$weightTrainingSetNum];
                        $reps = $repsArray[$weightTrainingSetNum];

                        $weightTrainingSet = (object)array(
                            'weight' => $weight,
                            'reps' => $reps,
                            'exerciseID' => $exerciseID,
                            'weightTrainingSetID' => $weightTrainingSetID,
                            'weightTrainingID' => $weightTrainingID );

                        $weeksArray[$weekNum]->days[$dayNum]->exercises[$exerciseNum]->exercises[$weightTrainingExerciseNum]->sets[$weightTrainingSetNum] = $weightTrainingSet;
                    }
                }


            }

        }


    }
}

$weeks = json_encode($weeksArray);
echo $weeks;


