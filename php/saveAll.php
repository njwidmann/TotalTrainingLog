<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/16/2014, 11/10/2014
 * Time: 7:24 PM
 */

$username = strval($_POST['username']);
$weeksString = strval($_POST['weeks']);

$weeks = json_decode($weeksString);

class saveAll extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new saveAll();

/*if(!$db){
    echo $db->lastErrorMsg();
} else {
    echo "Opened database successfully\n";
}*/

for($weekNum = 0; $weekNum < count($weeks); $weekNum++ ) {
    $week = $weeks[$weekNum];

    for($dayNum = 0; $dayNum < count($week -> days); $dayNum++) {
        $day = $week->days[$dayNum];

        for ($exerciseNum = 0; $exerciseNum < count($day->exercises); $exerciseNum++) {
            $exercise = $day->exercises[$exerciseNum];
            $exerciseID = $exercise->exerciseID;
            $notes = $exercise->notes;
            $planned = $exercise->planned;
            $plannedInt = 0;

            if ($planned == true || $planned == "true") {
                $plannedInt = 1;
            }


            $sql = "update exercises set notes = '" . $notes . "' where exerciseID = " . $exerciseID . ";" .
                "update exercises set planned = '" . $plannedInt . "' where exerciseID = " . $exerciseID . ";";

            $ret = $db->exec($sql);


            if ($exercise->weightTraining == false) {
                //update set values
                for ($setNum = 0; $setNum < count($exercise->sets); $setNum++) {
                    $set = $exercise->sets[$setNum];
                    for ($setValueNum = 0; $setValueNum < count($set->values); $setValueNum++) {
                        $setValue = $set->values[$setValueNum];
                        $value = $setValue->value;
                        $valueID = $setValue->ID;

                        $sql = "update exerciseSetValues set value = '" . $value . "' where valueID = " . $valueID . ";";

                        $ret = $db->exec($sql);
                    }
                }

            } else {
                for ($weightTrainingExerciseNum = 0; $weightTrainingExerciseNum < count($exercise->exercises); $weightTrainingExerciseNum++) {

                    $weightTrainingExercise = $exercise->exercises[$weightTrainingExerciseNum];
                    $weightTrainingID = $weightTrainingExercise->weightTrainingID;
                    $name = $weightTrainingExercise->name;

                    $sql = "update weightTraining set name = '" . $name . "' where weightTrainingID = " . $weightTrainingID . ";";

                    $ret = $db->exec($sql);

                    for ($weightTrainingSetNum = 0; $weightTrainingSetNum < count($weightTrainingExercise->sets); $weightTrainingSetNum++) {
                        $weightTrainingSet = $weightTrainingExercise->sets[$weightTrainingSetNum];
                        $weight = $weightTrainingSet->weight;
                        $reps = $weightTrainingSet->reps;
                        $weightTrainingSetID = $weightTrainingSet->weightTrainingSetID;

                        $sql = "update weightTrainingSets set weight = '" . $weight . "', reps = '" . $reps . "' where weightTrainingSetID = " . $weightTrainingSetID . ";";

                        $ret = $db->exec($sql);

                    }
                }
            }
        }
    }
}

