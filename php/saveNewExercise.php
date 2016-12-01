<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/14/2014
 * Time: 6:56 PM
 */

$username = strval($_POST['username']);
$exerciseString = strval($_POST['exercise']);
$weightTrainingBool = strval($_POST['weightTrainingBool']);

$exercise = json_decode($exerciseString);

$dayID = $exercise->dayID;
$exerciseName = $exercise->name;
$notes = $exercise->notes;

class saveNewExercise extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new saveNewExercise();

//save new exercise into db
$sql = "insert into exercises (username, dayID, name, weightTrainingBool, notes)".
    "values ('".$username."',".$dayID.",'".$exerciseName."','".$weightTrainingBool."','".$notes."');";

$ret = null;
$running = true;
while ($running) {

    $ret = $db->exec($sql);

    if ($ret) {
        $running = false;
    }
}


//retrieve the id of the exercise to put back into the array
$sql2 = "select exerciseID from exercises where dayID = ".$dayID.";";

$ret2 = null;
$running = true;
while ($running) {

    $ret2 = $db->query($sql2);

    if ($ret2) {
        $running = false;
    }
}

$exerciseIDArray = array();

while($row = $ret2->fetchArray(SQLITE3_ASSOC) ) {
    $exerciseIDArray[] = $row['exerciseID'];
}

$exerciseID = 0;
for($i = 0; $i < count($exerciseIDArray); $i++) {
    if($exerciseIDArray[$i] > $exerciseID) {
        $exerciseID = $exerciseIDArray[$i];
    }
}

$exercise -> exerciseID = $exerciseID;

if($weightTrainingBool == false || $weightTrainingBool == "false") {
    $sets = $exercise->sets;
        //insert sets into db
    for ($setNum = 0; $setNum < count($sets); $setNum++) {
        $sql = "insert into exerciseSets (username, exerciseID)" .
            "values ('" . $username . "'," . $exerciseID . ");";

        $ret = null;
        $running = true;
        while ($running) {

            $ret = $db->exec($sql);

            if ($ret) {
                $running = false;
            }
        }

            //retrieve the id of the set to put back into the array
        $sql2 = "select setID from exerciseSets where exerciseID = " . $exerciseID . ";";

        $ret2 = null;
        $running = true;
        while ($running) {

            $ret2 = $db->query($sql2);

            if ($ret2) {
                $running = false;
            }
        }

        $setIDArray = array();

        while ($row = $ret2->fetchArray(SQLITE3_ASSOC)) {
            $setIDArray[] = $row['setID'];
        }

        $setID = 0;
        for ($i = 0; $i < count($setIDArray); $i++) {
            if ($setIDArray[$i] > $setID) {
                $setID = $setIDArray[$i];
            }
        }

        $exercise->sets[$setNum]->setID = $setID;
        $exercise->sets[$setNum]->exerciseID = $exerciseID;

        $values = $sets[$setNum]->values;
        for ($valueNum = 0; $valueNum < count($values); $valueNum++) {
            $value = $values[$valueNum]->value;

            $sql = "insert into exerciseSetValues (username, exerciseID, setID, value)" .
                "values ('" . $username . "'," . $exerciseID . "," . $setID . ",'" . $value . "');";

            $ret = null;
            $running = true;
            while ($running) {

                $ret = $db->exec($sql);

                if ($ret) {
                    $running = false;
                }
            }


                //retrieve the id of the values to put back into the array
            $sql2 = "select valueID from exerciseSetValues where setID = " . $setID . ";";

            $ret2 = null;
            $running = true;
            while ($running) {

                $ret2 = $db->query($sql2);

                if ($ret2) {
                    $running = false;
                }
            }

            $valueIDArray = array();

            while ($row = $ret2->fetchArray(SQLITE3_ASSOC)) {
                $valueIDArray[] = $row['valueID'];
            }

            $valueID = 0;
            for ($i = 0; $i < count($valueIDArray); $i++) {
                if ($valueIDArray[$i] > $valueID) {
                    $valueID = $valueIDArray[$i];
                }
            }

            $exercise->sets[$setNum]->values[$valueNum]->ID = $valueID;
            $exercise->sets[$setNum]->values[$valueNum]->setID = $setID;
            $exercise->sets[$setNum]->values[$valueNum]->exerciseID = $exerciseID;


        }


    }

    $labels = $exercise -> labels;
    for($labelNum = 0; $labelNum < count($labels); $labelNum++) {

        $labelValue = $labels[$labelNum]->value;

        $sql = "insert into exerciseLabels (username, exerciseID, label)".
            "values ('".$username."',".$exerciseID.",'".$labelValue."');";

        $ret = null;
        $running = true;
        while ($running) {

            $ret = $db->exec($sql);

            if ($ret) {
                $running = false;
            }
        }

            //retrieve the id of the exercise to put back into the array
        $sql2 = "select labelID from exerciseLabels where exerciseID = ".$exerciseID.";";

        $ret2 = null;
        $running = true;
        while ($running) {

            $ret2 = $db->query($sql2);

            if ($ret2) {
                $running = false;
            }
        }

        $labelIDArray = array();

        while($row = $ret2->fetchArray(SQLITE3_ASSOC) ) {
            $labelIDArray[] = $row['labelID'];
        }

        $labelID = 0;
        for ($i = 0; $i < count($labelIDArray); $i++) {
            if ($labelIDArray[$i] > $labelID) {
                $labelID = $labelIDArray[$i];
            }
        }

        $exercise->labels[$labelNum]->ID = $labelID;
        $exercise->labels[$labelNum]->exerciseID = $exerciseID;


    }
} else if ($weightTrainingBool == true || $weightTrainingBool == "true") {
    $weightTrainingExercises = $exercise->exercises;
    //insert weight training exercises into db
    for ($weightTrainingExerciseNum = 0; $weightTrainingExerciseNum < count($weightTrainingExercises); $weightTrainingExerciseNum++) {
        $weightTrainingExercise = $weightTrainingExercises[$weightTrainingExerciseNum];
        $weightTrainingExerciseName = $weightTrainingExercise -> name;

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

        $exercise->exercises[$weightTrainingExerciseNum]->weightTrainingID = $weightTrainingID;
        $exercise->exercises[$weightTrainingExerciseNum]->exerciseID = $exerciseID;

        $weightTrainingSets = $weightTrainingExercises[$weightTrainingExerciseNum]->sets;
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

            $exercise->exercises[$weightTrainingExerciseNum]->sets[$setNum]->weightTrainingID = $weightTrainingID;
            $exercise->exercises[$weightTrainingExerciseNum]->sets[$setNum]->weightTrainingSetID = $weightTrainingSetID;
            $exercise->exercises[$weightTrainingExerciseNum]->sets[$setNum]->exerciseID = $exerciseID;



        }


    }

}

$exerciseString = json_encode($exercise);

echo $exerciseString;