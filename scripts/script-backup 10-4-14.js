//wait for the page to load before executing any script
$(document).ready(function() {
  //hides content on page load except for the greeting
  $("#content").hide();
  //when user presses ok switch greeting to hidden and show content

  
  $("#dialog").dialog({
    autoOpen: false,
    width: 650,
    modal: true
  });


 
  ///knockout stuff begins here
var ExerciseModel = function(weeks) {
    var self = this;
    var username = localStorage.getItem("username");
    
    self.weeks = ko.observableArray( ko.mapping.fromJS(weeks)());


    //self.exerciseMasterList = ko.observableArray();
    self.exerciseMasterBool = ko.observable(false);

    self.resetMaster = function() {
  
      self.exerciseMasterList = ko.observableArray([
        {
          name: ko.observable("Weight Training"),
          weightTraining: true,
          labels: null
        },
				{
          name: ko.observable("Running"),
          labels: ko.observableArray([
            {value: "Distance"},
            {value: "Time"},
            {value: "Pace"}
          ]),
		  weightTraining: false
        },
        {
          name: ko.observable("Swimming"),
          labels: ko.observableArray([
            {value: "Stroke"},
            {value: "Laps"},
            {value: "Time"}
          ]),
					weightTraining: ko.observable(false)
        },
        {
          name: ko.observable("Biking"),
          labels: ko.observableArray([
            {value: "Distance"},
            {value: "Time"},
            {value: "Pace"}
          ]),
					weightTraining: ko.observable(false)
        },
        {
          name: ko.observable("Walking"),
          labels: ko.observableArray([
            {value: "Distance"},
            {value: "Time"},
            {value: "Pace"}
          ]),
					weightTraining: ko.observable(false)
        },
          {
          name: ko.observable("XC Skiing"),
          labels: ko.observableArray([
            {value: "Distance"},
            {value: "Time"},
            {value: "Pace"}
          ]),
					weightTraining: ko.observable(false)
        },
        {
          name: ko.observable("Kayaking"),
          labels: ko.observableArray([
            {value: "Distance"},
            {value: "Time"},
            {value: "Pace"}
          ]),
					weightTraining: ko.observable(false)
        }
      ]);
    self.exerciseMasterBool(true);
    };

    self.getMaster = function(username) {
        $.get( "getMaster.php?q="+username)
            .done(function( data ) {
                if (data != "") {
                    var master = JSON.parse(data);
                    self.exerciseMasterList = ko.observableArray(ko.mapping.fromJS(master)());
                } else {
                    self.resetMaster();
                }
                self.exerciseMasterBool(true);
            });

    }
    self.getMaster(username);
    
    self.returnedName = ko.observable();



    self.previousExercise = ko.observable();


    self.copyExercise = function(exercise) {
      self.previousExercise = exercise
    }
    
    self.addExerciseToMaster = function() {
      self.exerciseMasterList.push(
        {
          name: ko.observable(""),
          labels: ko.observableArray([]),
          weightTraining: ko.observable(false)
        });
    };
    
    self.addFieldToMaster = function(exercise) {
      exercise.labels.push(
        {
          value: ko.observable("")
        }
      );
    };
    
    self.dialogOpener = function() {
      $("#dialog").dialog("open");
    };
    
    self.updateMaster = function(master, username) {
        var master2 = JSON.stringify(ko.toJS(master));
        $.get( "updateMaster.php?q="+master2+"&u="+username );

    };
    self.updateJSON = function(userData, username) {
        var userData2 = JSON.stringify(ko.toJS(userData));
        $.get( "updateJSON.php?q="+userData2+"&u="+username );

    };

    self.save = function() {
        self.updateJSON(self.weeks, username);

    };

    self.saveMaster = function() {
        self.updateMaster(self.exerciseMasterList, username);
        $("#dialog").dialog("close");
    }
    
   self.removeFromMaster = function(exercise) {
       self.exerciseMasterList.remove(exercise);
       self.updateMaster(self.exerciseMasterList, username);
   };
   
   $(document).on("click",".ui-dialog-titlebar-close", function() {
      self.saveMaster();
      
    });
      
    self.addExercise = function(returnedName, day) {
        if (ko.toJS(returnedName) === undefined){
            alert("Please select an exercise");
        }
        if (ko.toJS(returnedName).weightTraining === false) {
				day.exercises.push({
        name: ko.toJS(returnedName).name,
        labels: ko.toJS(returnedName).labels,
        //labelsFromMaster(returnedName),
        //sets will show up as initial text in the entry boxes that will go away when clicked
        //I need to assign the same number of values to it as the labels though to create the boxes in the first place
         sets: ko.observableArray([
            {
              values: ko.toJS(returnedName).labels
            }
          ]),
        editing: ko.observable(true),
        weightTraining: false,
        addSet: function() {
            var tempLabels = ko.toJS(this.sets)[ko.toJS(this.sets).length - 1];

            this.sets.push( ko.mapping.fromJS(tempLabels));
        }
        
      });
          $(".jqxExpander").jqxExpander();
			} else {

					day.exercises.push({
							name: ko.toJS(returnedName).name,
							exercises: ko.observableArray([
									{
											name: ko.observable("Exercise"),
											sets: ko.observableArray([
												{
                            weight: ko.observable("Weight"),
												    reps: ko.observable("Reps")
                        }
											])
										
									}
							]),
							editing: ko.observable(true),
                            weightTraining: ko.observable(true),
        			        addExercise: function() {
            			        var tempExercise = ko.toJS(this.exercises)[ko.toJS(this.exercises).length - 1];

            			        this.exercises.push( ko.mapping.fromJS(tempExercise));

							}
					});


          $(".jqxExpander").jqxExpander();


	    }
    

    };//end of addExercise()

    self.addPreviousExercise = function(previousExercise, day) {
        if (ko.toJS(previousExercise) === undefined){
            alert("Please select an exercise to copy");
        }
        if (ko.toJS(previousExercise).weightTraining === false) {
            day.exercises.push(ko.mapping.fromJS({
                name: ko.toJS(previousExercise).name,
                labels: ko.toJS(previousExercise).labels,
                //labelsFromMaster(returnedName),
                //sets will show up as initial text in the entry boxes that will go away when clicked
                //I need to assign the same number of values to it as the labels though to create the boxes in the first place
                sets: ko.toJS(previousExercise).sets,
                editing: ko.observable(true),
                weightTraining: false
                

            }));
            $(".jqxExpander").jqxExpander();
        } else {

            day.exercises.push(ko.mapping.fromJS({
                name: ko.toJS(previousExercise).name,
                exercises: ko.toJS(previousExercise).exercises,
                editing: ko.observable(true),
                weightTraining: ko.observable(true),
                addExercise: function() {
                    var tempExercise = ko.toJS(this.exercises)[ko.toJS(this.exercises).length - 1];

                    this.exercises.push( ko.mapping.fromJS(tempExercise));

                }
            }));


            $(".jqxExpander").jqxExpander();


        }


    };//end of addPreviousExercise()
    
    	self.addWeightsSet = function(exercise) {
												if (ko.toJS(exercise.sets).length > 0) {
                                                    var tempSet = ko.toJS(exercise.sets)[ko.toJS(exercise.sets).length - 1];
                                                } else {
                                                    var tempSet =

                                                        {
                                                            weight: ko.observable("Weight"),
                                                            reps: ko.observable("Reps")
                                                        }


                                                }
                                                exercise.sets.push( ko.mapping.fromJS(tempSet));
       }
       
       self.addWeightsExercise = function(weightTraining) {
                    var tempExercise = ko.toJS(weightTraining.exercises)[ko.toJS(weightTraining.exercises).length - 1];

                    weightTraining.exercises.push( ko.mapping.fromJS(tempExercise));

                }
       
       self.addSet = function(exercise) {
                    var tempLabels = ko.toJS(exercise.sets)[ko.toJS(exercise.sets).length - 1];

                    exercise.sets.push( ko.mapping.fromJS(tempLabels));
                }
    
    
    //constructor for days
    function DayMaker(day) {

      this.name = day;
      this.exercises = ko.observableArray([]);
      this.returnedName = ko.observable("")
    }
    
    
    //add week function calls the dayMaker constructor for each day

    self.addWeek = function() {
      self.weeks.push(
        {
          days: ko.observableArray([
            new DayMaker("Monday"),
            new DayMaker("Tuesday"),
            new DayMaker("Wednesday"),
            new DayMaker("Thursday"),
            new DayMaker("Friday"),
            new DayMaker("Saturday"),
            new DayMaker("Sunday")
            
          ]),
          location: ko.toJS(self.weeks).length + 1
        }
      );
         $(function () {
            // Create jqxTabs.
             $('.dayTabs').jqxTabs({position: 'top', selectionTracker: true});
             $('.dayTabs').on('tabclick', function (event) {
                 self.returnedName = null;

             });
        });
  
    };

    
    //for adding a set to weight training
    self.addWeightsSet = function(exercise) {
      //pushes to the sets array inside of the exercise that is in the weightTraining array
      exercise.sets.push({
              weight: ko.observable(""),
              reps: ko.observable("")
      });
    };

    self.removeWeek = function(week) {
        self.weeks.remove(week);
        self.save();
    }

    self.removeSet = function(exercise, day, set) {
        //in case it is the last set
        if (ko.toJS(exercise).sets.length === 1) {
            day.exercises.remove(exercise)
        } else {
            exercise.sets.remove(set);
        }

        self.save();

    }

    self.removeWeightsSet = function(exercise, weightTraining, day, set) {
        //in case it is the last exercise in weight training
        if (ko.toJS(weightTraining).exercises.length === 1 && ko.toJS(exercise).sets.length === 1) {
            day.exercises.remove(weightTraining)
        //in case it is the last set in the exercise
        } else if (ko.toJS(weightTraining).exercises.length > 1 && ko.toJS(exercise).sets.length === 1) {
            weightTraining.exercises.remove(exercise);
        } else {
            exercise.sets.remove(set)
        }

        self.save();

    }



      //for removing exercises from the exercises array
      //this function takes two parameters. these come from the data-bind in index
    self.removeExercise = function(day, exercise) {
        
        day.exercises.remove(exercise);
       
       // save
       self.save();
    };
    //same as above but for removing weight training from the weightTraining array
    self.removeWeightTraining = function(weightTraining, day, exercise) {
        if (ko.toJS(weightTraining).exercises.length === 1) {
            day.exercises.remove(weightTraining)
        } else {
            weightTraining.exercises.remove(exercise);
        }

       // save
       self.save();
    };
      
    


    if (ko.toJS(self.weeks).length === 0) {
        self.addWeek()
    }

};
// end knockout script 

//////////////////////////////////////////////////
    /////////////////////////////////////////////
    ////////////////////////////////////////////

    getJSON = function(username) {
        $.get( "getJSON.php?q="+username)
            .done(function( data ) {

                if (data === ""){
                    data = '[{"days":[{"name":"Monday","exercises":[],"returnedName":""},{"name":"Tuesday","exercises":[],"returnedName":""},{"name":"Wednesday","exercises":[],"returnedName":""},{"name":"Thursday","exercises":[],"returnedName":""},{"name":"Friday","exercises":[],"returnedName":""},{"name":"Saturday","exercises":[],"returnedName":""},{"name":"Sunday","exercises":[],"returnedName":""}],"location":1}]';
                };

                var viewModel = JSON.parse(data);
                var viewModel2 = new ExerciseModel(viewModel);
                ko.applyBindings(viewModel2);
                $("#content").show("slow");

                $(function () {
                    // Create jqxTabs.
                    $('.dayTabs').jqxTabs({position: 'top', selectionTracker: true});
                    $('.dayTabs').on('tabclick', function (event) {
                        self.returnedName = ko.observable(null);

                    });
                });
                $(".jqxExpander").jqxExpander();
            });

    }

    $("#showContent").click(function() {
        $(".greeting").hide();

        var current_user = localStorage.getItem("username")
        getJSON(current_user);


    });

});