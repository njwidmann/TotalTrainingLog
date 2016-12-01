//wait for the page to load before executing any script
$(document).ready(function() {
  //hides content on page load except for the greeting
  $("#content").hide();
  //when user presses ok switch greeting to hidden and show content

  //dialog for exercise master list
  $("#dialog").dialog({
    autoOpen: false,
    width: 700,
    modal: true
  });


    //loading widget integration
    (function($) {
        $.widget("artistan.loading", $.ui.dialog, {
            options: {
                // your options
                spinnerClassSuffix: 'spinner',
                spinnerHtml: 'Loading',// allow for spans with callback for timeout...
                maxHeight: false,
                maxWidth: false,
                minHeight: 80,
                minWidth: 220,
                height: 80,
                width: 220,
                modal: true
            },

            _create: function() {
                $.ui.dialog.prototype._create.apply(this);
                // constructor
                $(this.uiDialog).children('*').hide();
                var self = this,
                    options = self.options;
                self.uiDialogSpinner = $('.ui-dialog-content',self.uiDialog)
                    .html(options.spinnerHtml)
                    .addClass('ui-dialog-'+options.spinnerClassSuffix);
            },
            _setOption: function(key, value) {
                var original = value;
                $.ui.dialog.prototype._setOption.apply(this, arguments);
                // process the setting of options
                var self = this;

                switch (key) {
                    case "innerHeight":
                        // remove old class and add the new one.
                        self.uiDialogSpinner.height(value);
                        break;
                    case "spinnerClassSuffix":
                        // remove old class and add the new one.
                        self.uiDialogSpinner.removeClass('ui-dialog-'+original).addClass('ui-dialog-'+value);
                        break;
                    case "spinnerHtml":
                        // convert whatever was passed in to a string, for html() to not throw up
                        self.uiDialogSpinner.html("" + (value || '&#160;'));
                        break;
                }
            },
            _size: function() {
                $.ui.dialog.prototype._size.apply(this, arguments);
            },
            // other methods
            loadStart: function(newHtml){
                if(typeof(newHtml)!='undefined'){
                    this._setOption('spinnerHtml',newHtml);
                }
                this.open();
            },
            loadStop: function(){
                this._setOption('spinnerHtml',this.options.spinnerHtml);
                this.close();
            }
        });
    })(jQuery);




    ///knockout stuff begins here
    var ExerciseModel = function(weeks) {
        var self = this;
        var username;



        self.weeks = ko.observableArray(ko.mapping.fromJS(weeks)());


            //set initial value of currentDate to today's date
        var currentDate = new Date();
            //set initial value of shownMonth to today's month
        var shownMonth = currentDate.getMonth();
            //set initial value of shownYear to today's year
        var shownYear = currentDate.getFullYear();

        var monthsInYear =  ['January', 'February', 'March', 'April',
            'May', 'June', 'July', 'August', 'September',
            'October', 'November', 'December'];

            //month name and year that are displayed at the top of the screen
        self.shownMonthName = ko.observable(monthsInYear[shownMonth] + " " + shownYear);

        var daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        var dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            //Change the current month
        function ChangeMonth(month, year) {
            //takes in month and year and assigns else gets current
            this.month = (isNaN(month) || month == null) ? currentDate.getMonth() : month;
            this.year = (isNaN(year) || year == null) ? currentDate.getFullYear() : year;
            this.weeks = [];
        }
            //create a simple array filled with objects with the dates for the month
        ChangeMonth.prototype.fetchWeeks = function () {
            //get first day of the month
            var firstDay = new Date(this.year, this.month, 1);
            var startingDay = firstDay.getDay(); //returns 0-6 (sun - sat)

            //find number of days in the month
            var monthLength = daysInMonth[this.month];

            //leap year compensation
            if (this.month == 1) { // February only!
                if ((this.year % 4 == 0 && this.year % 100 != 0) || this.year % 400 == 0) {
                    monthLength = 29;
                }
            }

            var day = 1;

            for (var i = 0; i < 9; i++) {
                //add new week
                this.weeks[i] = [];
                //add days
                for (var j = 0; j <= 6; j++) {
                    if (day <= monthLength && (i > 0 || j >= startingDay)) {
                        this.weeks[i][this.weeks[i].length] = {
                            date: day,
                            dayName: dayNames[j],
                            dayID: null
                        };
                        day++;

                    }
                }
                if (day > monthLength) {
                    break;
                }


            }


        };
            //takes simple array created above and sends it to db to collect data for those dates
        ChangeMonth.prototype.retrieveDays = function () {
            var weeks = [];
            for (var i = 0; i < this.weeks.length; i++) {
                weeks[i] = {days: []};
                for (var j = 0; j < this.weeks[i].length; j++) {
                    var date = this.weeks[i][j].date;
                    var dayName = this.weeks[i][j].dayName;
                    var year = this.year;
                    var month = this.month;
                    var lastDay = this.weeks[this.weeks.length - 1][this.weeks[this.weeks.length - 1].length - 1].date;


                    weeks[i].days[j] = {
                        dayID: 0,
                        name: dayName + " - " + date,
                        exercises: [],
                        date: date,
                        month: month,
                        year: year
                    };


                }
            }
            var weeksString = JSON.stringify(weeks);

            $("#loading_dialog").loading();
            $("#loading_dialog").loading("loadStart", "Please wait...");

            $.get("php/retrieveDays.php?year=" + year + "&month=" + month + "&weeksString=" + weeksString + "&username=" + username)
                .done(function (data) {
                    var weeks = JSON.parse(data);
                        //sets self.weeks = new returned weeks array
                    self.weeks(ko.mapping.fromJS(weeks)());

                    $("#loading_dialog").loading("loadStop");

                    $("#content").show("slow");

                    //update ui
                    $('.dayTabs').jqxTabs({position: 'top', selectionTracker: true, collapsible: true});

                    $(".jqxExpander").jqxExpander({expanded: false});
                });

        };
        //calls ChangeMonth to go to the next month
    self.nextMonth = function() {
        $("#content").hide();

        if (shownMonth == 11) {
            shownMonth = 0;
            shownYear++;
        } else {
            shownMonth++
        }

        self.shownMonthName(monthsInYear[shownMonth] + " " + shownYear);

        var month = new ChangeMonth(shownMonth, shownYear);
        month.fetchWeeks();
        month.retrieveDays();
    };
            //calls ChangeMonth to go to the previous month
        self.previousMonth = function() {


            if (shownMonth == 9 && shownYear == 2014) {
                alert("Sorry you can not go back any further.")
            } else if (shownMonth == 0) {
                $("#content").hide();

                shownMonth = 11;
                shownYear--;

                self.shownMonthName(monthsInYear[shownMonth] + " " + shownYear);

                var month = new ChangeMonth(shownMonth, shownYear);
                month.fetchWeeks();
                month.retrieveDays();
            } else {
                $("#content").hide();

                shownMonth--;

                self.shownMonthName(monthsInYear[shownMonth] + " " + shownYear);

                var month = new ChangeMonth(shownMonth, shownYear);
                month.fetchWeeks();
                month.retrieveDays();
            }



        };

        //do not display Master in the popup dialog until it has loaded
    self.exerciseMasterBool = ko.observable(false);

    self.resetMaster = function() {

        //preset exercises in the master list (my exercises)

        self.exerciseMasterList = ko.observableArray([
                //weight training (doesn't show up in "My Exercises" for user to edit)
            {
                name: ko.observable("Weight Training"),
                weightTraining: true,
                labels: null
            }
        ]);

            //Constructor for all other exercises
        function NewMasterExercise(name, labels) {
            this.name = ko.observable(name);
            this.labels = ko.observableArray([]);
            this.weightTraining = ko.observable(false);

            for(var i = 0; i < labels.length; i++) {
                this.labels()[i] = {
                    value: labels[i],
                    ID: null,
                    exerciseID: null,
                    setID: null
                }
            }

        }
            //Add other exercises to Master
        self.exerciseMasterList()[1] = new NewMasterExercise("Running", ["Distance", "Time"]);
        self.exerciseMasterList()[2] = new NewMasterExercise("Swimming", ["Stroke", "Laps", "Time"]);
        self.exerciseMasterList()[3] = new NewMasterExercise("Biking", ["Distance", "Time"]);
        self.exerciseMasterList()[4] = new NewMasterExercise("Walking", ["Distance", "Time"]);
        self.exerciseMasterList()[5] = new NewMasterExercise("Hiking", ["Distance", "Time"]);
        self.exerciseMasterList()[6] = new NewMasterExercise("XC Skiing", ["Distance", "Time"]);
        self.exerciseMasterList()[7] = new NewMasterExercise("Rowing", ["Distance", "Time"]);
        self.exerciseMasterList()[8] = new NewMasterExercise("Kayaking", ["Distance", "Time"]);

            //display Master List
        self.exerciseMasterBool(true);

    };

        //retrieves the master list (my exercises) of exercises from db
    self.getMaster = function(username) {
        console.log("username = " + username);
        $.get( "php/getMaster.php?q="+username)
            .done(function( data ) {
                console.log("data = " + data);
                if (data != "") {
                    var master = JSON.parse(data);
                    self.exerciseMasterList = ko.observableArray(ko.mapping.fromJS(master)());
                } else {
                    console.log("resetting master");
                    self.resetMaster();
                }

                    //display Master
                self.exerciseMasterBool(true);
            });

    };

    


        //add a new exercise to the master list (my exercises)
    self.addExerciseToMaster = function() {

            //shrink other dropdowns
        $(".jqxExpander").jqxExpander({expanded: false});
            //add new exercise to master
        self.exerciseMasterList.push(
        {
          name: ko.observable("Custom Exercise"),
          labels: ko.observableArray([
              {value: ko.observable("")} //creates one blank label (input field) by default
          ]),
          weightTraining: ko.observable(false)
        });

            //update ui (dropdown menus) and opens newly added exercise
        $(".jqxExpander").jqxExpander();
    };

        //add another input field to an exercise in the master list
    self.addFieldToMaster = function(exercise) {
      exercise.labels.push(
        {
          value: ko.observable("")
        }
      );
    };

        //edit master (my exercises)
    self.dialogOpener = function() {
            //open dialog
        $("#dialog").dialog("open");
            //update ui (dropdown menus)
      $(".jqxExpander").jqxExpander({expanded: false});
    };
        //saves updated master to db
    self.updateMaster = function(master, username) {
        var masterString = JSON.stringify(ko.toJS(master));
        $.post( "php/updateMaster.php", "q="+masterString+"&u="+username );

    };
            //calls updateMaster and closes dialog
        self.saveMaster = function() {
            self.updateMaster(self.exerciseMasterList, username);
            $("#dialog").dialog("close");
        };

            //removes an exercise from the db then updates master
        self.removeFromMaster = function(exercise) {
            self.exerciseMasterList.remove(exercise);
            self.updateMaster(self.exerciseMasterList, username);
        };

        //removes an exercise from the db then updates master
        self.removeFieldFromMaster = function(exercise, label) {

            exercise.labels.remove(label);


            self.updateMaster(self.exerciseMasterList, username);
        };

            //saves master to db when dialog is closed using the X in the top of the dialog
        $(document).on("click",".ui-dialog-titlebar-close", function() {
            self.saveMaster();

        });

        //saves a new exercise to db
    self.saveNewExercise = function(day) {
        //open loading dialog
        $("#loading_dialog").loading();
        $("#loading_dialog").loading("loadStart", "Please wait...");

        var dayJS = ko.toJS(day);
        var exercise = dayJS.exercises[dayJS.exercises.length - 1];
        var exerciseString = JSON.stringify(exercise);
        var weightTrainingBool = exercise.weightTraining;
        $.post( "php/saveNewExercise.php", "exercise="+exerciseString+"&username="+username+"&weightTrainingBool="+weightTrainingBool, function(data) {
                //ajax returns exercise JSON

                console.log(data);
                var exercise = JSON.parse(data);

                var exerciseID = exercise.exerciseID;

                day.exercises()[dayJS.exercises.length - 1].exerciseID = exerciseID;
                    //if exercise is not weight training
                if (exercise.weightTraining == false) {
                    var sets = exercise.sets;

                    for (var setNum = 0; setNum < sets.length; setNum++) {
                        var set = sets[setNum];
                        var setID = set.setID;
                            //update IDs
                        day.exercises()[dayJS.exercises.length - 1].sets()[setNum].setID = setID;
                        day.exercises()[dayJS.exercises.length - 1].sets()[setNum].exerciseID = exerciseID;

                        var values = set.values;
                        for (var valueNum = 0; valueNum < values.length; valueNum++) {
                            var value = values[valueNum];
                            var ID = value.ID;
                                //update IDs
                            day.exercises()[dayJS.exercises.length - 1].sets()[setNum].values()[valueNum].ID = ID;
                            day.exercises()[dayJS.exercises.length - 1].sets()[setNum].values()[valueNum].exerciseID = exerciseID;
                            day.exercises()[dayJS.exercises.length - 1].sets()[setNum].values()[valueNum].setID = setID;

                        }

                    }

                    var labels = exercise.labels
                    for (var labelNum = 0; labelNum < labels.length; labelNum++) {
                        var label = labels[labelNum];
                        var ID = label.ID;
                            //update IDs
                        day.exercises()[dayJS.exercises.length - 1].labels[labelNum].ID = ID;
                        day.exercises()[dayJS.exercises.length - 1].labels[labelNum].exerciseID = exerciseID;

                    }


                } else {
                        //if exercise = weight training
                    var weightTrainingExercises = exercise.exercises; //exercises (bench, squat, etc) inside of the weight training exercise

                    for (var weightTrainingExerciseNum = 0; weightTrainingExerciseNum < weightTrainingExercises.length; weightTrainingExerciseNum++) {
                        var weightTrainingExercise = weightTrainingExercises[weightTrainingExerciseNum];
                        var weightTrainingID = weightTrainingExercise.weightTrainingID;
                            //update IDs
                        day.exercises()[dayJS.exercises.length - 1].exercises()[weightTrainingExerciseNum].weightTrainingID = weightTrainingID;
                        day.exercises()[dayJS.exercises.length - 1].exercises()[weightTrainingExerciseNum].exerciseID = exerciseID;

                        var weightTrainingSets = weightTrainingExercise.sets
                        for (var weightTrainingSetNum = 0; weightTrainingSetNum < weightTrainingSets.length; weightTrainingSetNum++) {
                            var weightTrainingSet = weightTrainingSets[weightTrainingSetNum];
                            var weightTrainingSetID = weightTrainingSet.weightTrainingSetID;
                                //update IDs
                            day.exercises()[dayJS.exercises.length - 1].exercises()[weightTrainingExerciseNum].sets()[weightTrainingSetNum].weightTrainingID = weightTrainingID;
                            day.exercises()[dayJS.exercises.length - 1].exercises()[weightTrainingExerciseNum].sets()[weightTrainingSetNum].weightTrainingSetID = weightTrainingSetID;
                            day.exercises()[dayJS.exercises.length - 1].exercises()[weightTrainingExerciseNum].sets()[weightTrainingSetNum].exerciseID = exerciseID;

                        }

                    }
                } //end else

            //close loading dialog
            $("#loading_dialog").loading("loadStop");
            });

    };



        //save a new set to db
    self.saveNewSet = function(set) {

        //loading dialog
        $("#loading_dialog").loading();
        $("#loading_dialog").loading("loadStart", "Please wait...");

        var setJS = ko.toJS(set);
        var setString = JSON.stringify(setJS);
        var data = "set="+setString+"&username="+username
        $.post( "php/saveNewSet.php", data, function(data) {
                //ajax returns set JSON
                console.log(data);

                var setJS = JSON.parse(data);
                var setID = setJS.setID;
                var exerciseID = setJS.exerciseID;
                    //update IDs
                set.setID = setID;
                set.exerciseID = exerciseID;

                var values = setJS.values;
                for(var valueNum = 0; valueNum < values.length; valueNum++) {
                    var value = values[valueNum];
                    var ID = value.ID;
                        //update IDs
                    set.values()[valueNum].ID = ID;
                    set.values()[valueNum].exerciseID = exerciseID;
                    set.values()[valueNum].setID = setID;

                }
            $("#loading_dialog").loading("loadStop");
            });
    };

        //save weight training exercise (bench, squat, etc) to db
    self.saveNewWeightTrainingExercise = function(weightTraining) {

        $("#loading_dialog").loading();
        $("#loading_dialog").loading("loadStart", "Please wait...");

        var weightTrainingJS = ko.toJS(weightTraining);
        var numExercises = weightTrainingJS.exercises.length;
        var exercise = weightTrainingJS.exercises[weightTrainingJS.exercises.length - 1];
        var exerciseString = JSON.stringify(exercise);
        $.post( "php/saveNewWeightTrainingExercise.php", "exercise="+exerciseString+"&username="+username, function(data) {
                //returns weightTrainingExercise JSON

                console.log(data);
                var weightTrainingExercise = JSON.parse(data);
                var weightTrainingID = weightTrainingExercise.weightTrainingID;
                var exerciseID = weightTrainingExercise.exerciseID;
                    //update IDs
                weightTraining.exercises()[numExercises - 1].weightTrainingID = weightTrainingID;
                weightTraining.exercises()[numExercises - 1].exerciseID = exerciseID;

                var weightTrainingSets = weightTrainingExercise.sets;
                for (var weightTrainingSetNum = 0; weightTrainingSetNum < weightTrainingSets.length; weightTrainingSetNum++) {
                    var weightTrainingSet = weightTrainingSets[weightTrainingSetNum];
                    var weightTrainingSetID = weightTrainingSet.weightTrainingSetID;
                        //update IDs
                    weightTraining.exercises()[numExercises - 1].sets()[weightTrainingSetNum].weightTrainingID = weightTrainingID;
                    weightTraining.exercises()[numExercises - 1].sets()[weightTrainingSetNum].weightTrainingSetID = weightTrainingSetID;
                    weightTraining.exercises()[numExercises - 1].sets()[weightTrainingSetNum].exerciseID = exerciseID;

                }
            //close loading dialog
            $("#loading_dialog").loading("loadStop");
            });


    };
        //save a new weight training set to db
    self.saveNewWeightTrainingSet = function(exercise) {

        //loading dialog
        $("#loading_dialog").loading();
        $("#loading_dialog").loading("loadStart", "Please wait...");

        var exerciseJS = ko.toJS(exercise);
        var set = exerciseJS.sets[exerciseJS.sets.length - 1];
        var setString = JSON.stringify(set);
        $.post( "php/saveNewWeightTrainingSet.php", "set="+setString+"&username="+username, function(data) {

                console.log(data);
                var weightTrainingSet = JSON.parse(data);
                var weightTrainingSetID = weightTrainingSet.weightTrainingSetID;

                    //update ID
                exercise.sets()[exerciseJS.sets.length - 1].weightTrainingSetID = weightTrainingSetID;

            $("#loading_dialog").loading("loadStop");
            });

    };
        //update all values in the db for the current month
    self.saveAll = function() {

        //loading dialog
        $("#loading_dialog").loading();
        $("#loading_dialog").loading("loadStart", "Please wait...");

        var weeks = ko.toJS(self.weeks);
        var weeksString = JSON.stringify(weeks);
        var data = "weeks="+weeksString+"&username="+username;

        $.post( "php/saveAll.php", data, function( data ){
            if(data == null || data == "") {
                data = "Save Successful";
            } else {
                alert("An error occurred... Please check your internet connection and try saving again. " +
                "If this issue persists, it is likely that we may be experiencing server interruptions. " +
                "Sorry for any inconveniences.")
            }

            console.log(data);
            $("#loading_dialog").loading("loadStop");
        });
        /*
        for(var weekPOS = 0; weekPOS < weeks.length; weekPOS++) {
            var week = weeks[weekPOS];
            for(var dayPOS = 0; dayPOS < week.days.length; dayPOS++) {
                var day = JSON.stringify(week.days[dayPOS]);
                var dayID = day.dayID;

                $.get( "php/saveAll.php?day="+day+"&username="+username);

            }

        }*/


    };




        self.returnedName = ko.observable(); //corresponds to the selected exercise (from the drop-down)

        //add a new exercise
        self.addExercise = function(returnedName, day) {
            //if they haven't selected an exercise from the drop down...
            if (ko.toJS(returnedName) === undefined){
                alert("Please select an exercise");
            } else if (ko.toJS(day).exercises.length > 0) {
                $(".jqxExpander").jqxExpander({expanded: false});
            }
            //if they did not select weight training...
            if (ko.toJS(returnedName).weightTraining === false) {

                //create a new exercise
                var tempExercise = {
                    name: ko.toJS(returnedName).name,
                    labels: ko.toJS(returnedName).labels,
                    sets: ko.observableArray([
                        {
                            values: ko.observableArray(ko.toJS(returnedName).labels), //set values = labels
                            setID: null,
                            exerciseID: null
                        }
                    ]),
                    notes: ko.observable("Notes..."),
                    planned: ko.observable(false),
                    editing: ko.observable(true),
                    weightTraining: false,
                    exerciseID: null,
                    dayID: day.dayID


                };
                var tempExerciseJS = ko.toJS(tempExercise);
                for(var i = 0; i < tempExerciseJS.sets[0].values.length; i++) { //set values = ""
                    tempExercise.sets()[0].values()[i].value = "";
                }

                day.exercises.push(tempExercise);

                //update ui
                $(".jqxExpander").jqxExpander();
            } else {
                //if they select weightTraining
            day.exercises.push({
                name: ko.toJS(returnedName).name,
                exercises: ko.observableArray([
                    {
                        name: ko.observable(""),
                        weightTrainingID: null,
                        exerciseID: null,
                        sets: ko.observableArray([
                            {
                                weight: ko.observable(""),
                                reps: ko.observable(""),
                                weightTrainingSetID: null,
                                weightTrainingID: null,
                                exerciseID: null
                            }
                        ])

                    }
                ]),
                notes: ko.observable("Notes..."),
                planned: ko.observable(false),
                editing: ko.observable(true),
                weightTraining: ko.observable(true),
                exerciseID: null,
                dayID: day.dayID

            });

                //update ui
            $(".jqxExpander").jqxExpander();


	    }

        self.saveNewExercise(day); //calls save method. This will insert the new exercise into the db and give everything IDs
    };//end of addExercise()

        //selected previousExercise for copying exercises
        self.previousExercise = ko.observable();

        //sets previousExcercise to the copied exercise. Used below to addPreviousExercise
        self.copyExercise = function(exercise) {
            self.previousExercise = exercise
        };

        self.addPreviousExercise = function(previousExercise, day) {
            //if they have not selected an exercise to copy
        if (ko.toJS(previousExercise) === undefined){
            alert("Please select an exercise to copy");
        }
            //if they do not select weight training
        if (ko.toJS(previousExercise).weightTraining === false) {
            day.exercises.push({
                name: ko.toJS(previousExercise).name,
                labels: ko.toJS(previousExercise).labels,
                sets: ko.observableArray(),
                notes: ko.observable("Notes..."),
                planned: ko.observable(false),
                editing: ko.observable(true),
                weightTraining: false,
                exerciseID: null,
                dayID: day.dayID

            });

            var sets = ko.toJS(previousExercise).sets;

            for(var setNum = 0; setNum < sets.length; setNum++) {
                day.exercises()[day.exercises().length - 1].sets.push({
                    values: ko.observableArray(),
                    setID: null,
                    exerciseID: null
                });

                var values = sets[setNum].values
                for(var valueNum = 0; valueNum < values.length; valueNum++) {
                    day.exercises()[day.exercises().length - 1].sets()[setNum].values.push({
                        value: values[valueNum].value,
                        ID: null,
                        setID: null,
                        exerciseID: null
                    });
                }
            }

            $(".jqxExpander").jqxExpander();
            self.saveNewExercise(day);

        } else {
                //if previousExercise = weightTraining
            day.exercises.push({
                name: ko.observable(ko.toJS(previousExercise).name),
                exercises: ko.observableArray(),
                notes: ko.observable("Notes..."),
                planned: ko.observable(false),
                editing: ko.observable(true),
                weightTraining: ko.observable(true),
                exerciseID: null,
                dayID: ko.toJS(day).dayID

            });
            for(i=0;i < ko.toJS(previousExercise).exercises.length; i++) {
                var weightTraining = day.exercises()[ko.toJS(day).exercises.length - 1];
                weightTraining.exercises.push({
                    name: ko.observable(ko.toJS(previousExercise).exercises[i].name),
                    sets: ko.observableArray(),
                    weightTrainingID: null,
                    exerciseID: null
                });
                for(j=0;j < ko.toJS(previousExercise).exercises[i].sets.length;j++) {
                    weightTraining.exercises()[i].sets.push({
                        weight: ko.observable(ko.toJS(previousExercise).exercises[i].sets[j].weight),
                        reps: ko.observable(ko.toJS(previousExercise).exercises[i].sets[j].reps),
                        weightTrainingSetID: null,
                        weightTrainingID: null,
                        exerciseID: null
                    })
                }

            }
                //update ui
            $(".jqxExpander").jqxExpander();

                //save the copied exercise
            self.saveNewExercise(day);


        } //end else


    };//end of addPreviousExercise()

        //add a new weight training set
    self.addWeightsSet = function(exercise) {
        if (ko.toJS(exercise.sets).length > 0) {
            var tempSet = ko.toJS(exercise.sets)[ko.toJS(exercise.sets).length - 1];
        }

        exercise.sets.push(
            {
                weight: ko.observable(tempSet.weight),
                reps: ko.observable(tempSet.reps),
                weightTrainingSetID: null,
                weightTrainingID: ko.toJS(exercise).weightTrainingID,
                exerciseID: ko.toJS(exercise).exerciseID
            });
        self.saveNewWeightTrainingSet(exercise);
    };

            //add a new weight training exercise
       self.addWeightsExercise = function(weightTraining) {
                    var tempExercise = ko.toJS(weightTraining.exercises)[ko.toJS(weightTraining.exercises).length - 1];

                    weightTraining.exercises.push(
                        {
                            name: ko.observable(""),
                            weightTrainingID: null,
                            exerciseID: ko.toJS(weightTraining).exerciseID,
                            sets: ko.observableArray([
                                {
                                    weight: ko.observable(""),
                                    reps: ko.observable(""),
                                    weightTrainingSetID: null,
                                    weightTrainingID: null,
                                    exerciseID: ko.toJS(weightTraining).exerciseID
                                }
                            ])

                        });

                    self.saveNewWeightTrainingExercise(weightTraining);
                };

        //add a new set to a regular exercise
       self.addSet = function(exercise) {
           var tempSet = ko.toJS(exercise.sets)[ko.toJS(exercise.sets).length - 1];

           exercise.sets.push({
               values: ko.observableArray(tempSet.values),
               setID: null,
               exerciseID: ko.toJS(exercise).exerciseID
           });

                //save the new set
           self.saveNewSet(exercise.sets()[ko.toJS(exercise.sets).length - 1]);
       };




        //remove an exercise
        self.removeExercise = function(day, exercise) {

            var exerciseID = ko.toJS(exercise).exerciseID;
            $.post( "php/deleteExercise.php", "exerciseID="+exerciseID, function(data){
                console.log(data);
            }); //update db

            day.exercises.remove(exercise);

        };



        //remove set from regular exercise
    self.removeSet = function(exercise, day, set) {
        //in case it is the last set, remove the exercise in addition to the set
        if (ko.toJS(exercise).sets.length === 1) {

            var exerciseID = ko.toJS(exercise).exerciseID;
            $.post( "php/deleteExercise.php", "exerciseID="+exerciseID, function(data){
                console.log(data);
            }); //update db

            day.exercises.remove(exercise)
        } else {

            var setID = ko.toJS(set).setID;
            $.post( "php/deleteSet.php", "setID="+setID, function(data){
                console.log(data);
            }); //update db

            exercise.sets.remove(set);
        }



    };

    self.removeWeightsSet = function(exercise, weightTraining, day, set) {

        //weightTraining param corresponds to the exercise in the main exercises array
        //exercise param corresponds to the exercise inside of the weightTraining object (bench, squat, etc)

        if (ko.toJS(weightTraining).exercises.length === 1 && ko.toJS(exercise).sets.length === 1) {
                //in case it is the last set in the last weightTrainingExercise in the exercise, delete the entire exercise (weight training)
            var exerciseID = ko.toJS(weightTraining).exerciseID;
            $.post( "php/deleteExercise.php", "exerciseID="+exerciseID, function(data){
                console.log(data);
            }); //update db

            day.exercises.remove(weightTraining)

        } else if (ko.toJS(weightTraining).exercises.length > 1 && ko.toJS(exercise).sets.length === 1) {
                //in case it is the last set in the weightTrainingExercise, delete the exercise in addition to the set
            var weightTrainingID = ko.toJS(exercise).weightTrainingID;
            $.post( "php/deleteWeightTrainingExercise.php", "weightTrainingID="+weightTrainingID, function(data){
                console.log(data);
            }); //update db

            weightTraining.exercises.remove(exercise);
        } else {
                //delete the set
            var weightTrainingSetID = ko.toJS(set).weightTrainingSetID;
            $.post( "php/deleteWeightTrainingSet.php", "weightTrainingSetID="+weightTrainingSetID, function(data){
                console.log(data);
            }); //update db

            exercise.sets.remove(set)
        }



    };

        //For removing individual exercises (bench, squat, etc) from "Weight Training" exercises
    self.removeWeightTraining = function(weightTraining, day, exercise) {
        if (ko.toJS(weightTraining).exercises.length === 1) {
            var exerciseID = ko.toJS(weightTraining).exerciseID;
            $.post( "php/deleteExercise.php", "exerciseID="+exerciseID, function(data){
                console.log(data);
            }); //update db

            day.exercises.remove(weightTraining)
        } else {
            var weightTrainingID = ko.toJS(exercise).weightTrainingID;
            $.post( "php/deleteWeightTrainingExercise.php", "weightTrainingID="+weightTrainingID, function(data){
                console.log(data);
            }); //update db

            weightTraining.exercises.remove(exercise);
        }



    };

        $.get("php/getUsername.php")
            .done(function (result) {
                username = result
                console.log(result);
                self.getMaster(username);
            });
      
    


    if (ko.toJS(self.weeks).length === 0) {
        self.addWeek()
    }

};
// end knockout script 

//////////////////////////////////////////////////
    /////////////////////////////////////////////
    ////////////////////////////////////////////

        //get the days for the current month and load user data for them, then pass into knockout
    var loadUserDays = function(username) {
        var daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        var dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        var currentDate = new Date();

        function Month(month, year) {
            //takes in month and year and assigns else gets current
            this.month = (isNaN(month) || month == null) ? currentDate.getMonth() : month;
            this.year  = (isNaN(year) || year == null) ? currentDate.getFullYear() : year;
            this.weeks = [];
        }
            //gets array of dates
        Month.prototype.fetchWeeks = function() {
            //get first day of the month
            var firstDay = new Date(this.year, this.month, 1);
            var startingDay = firstDay.getDay(); //returns 0-6 (sun - sat)

            //find number of days in the month
            var monthLength = daysInMonth[this.month];

            //leap year compensation
            if (this.month == 1) { // February only!
                if((this.year % 4 == 0 && this.year % 100 != 0) || this.year % 400 == 0){
                    monthLength = 29;
                }
            }

            var day = 1;

            for(var i = 0; i < 9; i++) {
                //add new week
                this.weeks[i] = [];
                //add days
                for(var j = 0; j <= 6; j++) {
                    if(day <= monthLength && (i > 0 || j >= startingDay)) {
                        this.weeks[i][this.weeks[i].length] = {
                            date: day,
                            dayName: dayNames[j],
                            dayID: null
                        };
                        day++;

                    }
                }
                if(day > monthLength) {
                    break;
                }


            }



        };
                //passes dates array to db and retrieves user data for those days
        Month.prototype.retrieveDays = function() {
            var weeks = [];
            for(var i = 0; i < this.weeks.length; i++) {
                weeks[i] = {days: []};
                for(var j = 0; j < this.weeks[i].length; j++) {
                    var date = this.weeks[i][j].date;
                    var dayName = this.weeks[i][j].dayName;
                    var year = this.year;
                    var month = this.month;
                    var lastDay = this.weeks[this.weeks.length - 1][this.weeks[this.weeks.length - 1].length - 1].date;



                    weeks[i].days[j] = {
                        dayID: 0,
                        name: dayName + " - " + date,
                        exercises: [],
                        date: date,
                        month: month,
                        year: year
                    };


                }
            }
            var weeksString = JSON.stringify(weeks);
                //send to db
            //loading dialog
            $("#loading_dialog").loading();
            $("#loading_dialog").loading("loadStart", "Please wait...");

            $.get("php/retrieveDays.php?year=" + year + "&month=" + month + "&weeksString=" + weeksString+ "&username=" + username)
                .done(function (data) {
                    var weeks = JSON.parse(data);
                    var weeksKO = new ExerciseModel(weeks);
                    ko.applyBindings(weeksKO);

                    // close loading dialog
                    $("#loading_dialog").loading("loadStop");

                    $("#content").show("slow");


                        // update ui
                    $('.dayTabs').jqxTabs({position: 'top', selectionTracker: true, collapsible: true});

                    $(".jqxExpander").jqxExpander({expanded: false});

                });

        };
            //execute above methods
        var month = new Month();
        month.fetchWeeks();
        month.retrieveDays();
    };
        //when the user clicks "load my training log" load their data
    $("#showContent").click(function() {
        $(".greeting").hide();

        //var current_user = localStorage.getItem("username")

        $.get("php/getUsername.php")
            .done(function (username) {
                loadUserDays(username);

            });



    });

});