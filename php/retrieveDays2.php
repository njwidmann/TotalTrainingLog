<?php
/**
 * Created by PhpStorm.
 * User: Nick
 * Date: 10/13/2014
 * Time: 2:29 PM
 */


$year = strval($_GET['year']);
$month = strval($_GET['month']);
$weeksString = strval($_GET['weeksString']);

class retrieveDays2 extends SQLite3
{
    function __construct()
    {
        $this->open('../users.db');
    }
}

$db = new retrieveDays2();

$weeksArray = json_decode($weeksString);
$lastDay = $weeksArray[count($weeksArray) - 1]->days[count($weeksArray[count($weeksArray) - 1]->days) - 1]->date;



//var lastDay = this.weeks[this.weeks.length - 1][this.weeks[this.weeks.length - 1].length - 1].date;

 /*if(!$db){
    echo $db->lastErrorMsg();
} else {
    echo "Opened database successfully\n";
}*/

for($i = 0; $i < count($weeksArray); $i++) {

    for($j = 0; $j < count($weeksArray[$i]->days); $j++) {

        $date = $weeksArray[$i]->days[$j]->date;

        $sql = "select id from days where year = ".$year." and month = ".$month." and day = ".$date.";";

        $ret = $db->query($sql);

        while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
            $weeksArray[$i]->days[$j]->dayID = $row['id'];

        }

        if($date == $lastDay) {

        }


    }
}

$weeks = json_encode($weeksArray);
echo $weeks;



/*if(!$ret){
    echo $db->lastErrorMsg();
} else {
    echo $db->changes(), " Record retrieved successfully\n";
}*/

