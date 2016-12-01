<!DOCTYPE html>
<html>

<head>
    <title>Nick's Training Log</title>

    <link rel="stylesheet" href="jqwidgets/styles/jqx.base.css" type="text/css" />

    <script type="text/javascript" src="scripts/jquery-1.11.1.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>

    <script type="text/javascript" src="knockout-3.2.0.js"></script>
    <script src="knockout-mapping.js"></script>
    <!-- add the jQWidgets framework -->
    <script type="text/javascript" src="jqwidgets/jqxcore.js"></script>
    <!-- add one or more widgets -->
    <script type="text/javascript" src="jqwidgets/jqxtabs.js"></script>
    <script type="text/javascript" src="jqwidgets/jqxmenu.js"></script>
    <script type="text/javascript" src="jqwidgets/jqxbuttons.js"></script>

    <script type="text/javascript" src="jqwidgets/jqxexpander.js"></script>

    <link rel='stylesheet' type='text/css' href='http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css' />
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <script src="jqxWidgetsIntegration.js"></script>
    <script src="script.js"></script>


</head>

<body>

<?php

/**
 * Class OneFileLoginApplication
 *
 * An entire php application with user registration, login and logout in one file.
 * Uses very modern password hashing via the PHP 5.5 password hashing functions.
 * This project includes a compatibility file to make these functions available in PHP 5.3.7+ and PHP 5.4+.
 *
 * @author Panique
 * @link https://github.com/panique/php-login-one-file/
 * @license http://opensource.org/licenses/MIT MIT License
 */
class OneFileLoginApplication
{
    /**
     * @var string Type of used database (currently only SQLite, but feel free to expand this with mysql etc)
     */
    private $db_type = "sqlite"; //

    /**
     * @var string Path of the database file (create this with _install.php)
     */
    private $db_sqlite_path = "./users.db";

    /**
     * @var object Database connection
     */
    private $db_connection = null;

    /**
     * @var bool Login status of user
     */
    private $user_is_logged_in = false;

    /**
     * @var string System messages, likes errors, notices, etc.
     */
    public $feedback = "";


    /**
     * Does necessary checks for PHP version and PHP password compatibility library and runs the application
     */
    public function __construct()
    {
        if ($this->performMinimumRequirementsCheck()) {
            $this->runApplication();
        }
    }

    /**
     * Performs a check for minimum requirements to run this application.
     * Does not run the further application when PHP version is lower than 5.3.7
     * Does include the PHP password compatibility library when PHP version lower than 5.5.0
     * (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
     * @return bool Success status of minimum requirements check, default is false
     */
    private function performMinimumRequirementsCheck()
    {
        if (version_compare(PHP_VERSION, '5.3.7', '<')) {
            echo "Sorry, Simple PHP Login does not run on a PHP version older than 5.3.7 !";
        } elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
            require_once("libraries/password_compatibility_library.php");
            return true;
        } elseif (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            return true;
        }
        // default return
        return false;
    }

    /**
     * This is basically the controller that handles the entire flow of the application.
     */
    public function runApplication()
    {
        // check is user wants to see register page (etc.)
        if (isset($_GET["action"]) && $_GET["action"] == "register") {
            $this->doRegistration();
            $this->showPageRegistration();
        } else {
            // start the session, always needed!
            $this->doStartSession();
            // check for possible user interactions (login with session/post data or logout)
            $this->performUserLoginAction();
            // show "page", according to user's login status
            if ($this->getUserLoginStatus()) {
                $this->showPageLoggedIn();
            } else {
                $this->showPageLoginForm();
            }
        }
    }

    /**
     * Creates a PDO database connection (in this case to a SQLite flat-file database)
     * @return bool Database creation success status, false by default
     */
    private function createDatabaseConnection()
    {
        try {
            $this->db_connection = new PDO($this->db_type . ':' . $this->db_sqlite_path);
            return true;
        } catch (PDOException $e) {
            $this->feedback = "PDO database connection problem: " . $e->getMessage();
        } catch (Exception $e) {
            $this->feedback = "General problem: " . $e->getMessage();
        }
        return false;
    }

    /**
     * Handles the flow of the login/logout process. According to the circumstances, a logout, a login with session
     * data or a login with post data will be performed
     */
    private function performUserLoginAction()
    {
        if (isset($_GET["action"]) && $_GET["action"] == "logout") {
            $this->doLogout();
        } elseif (!empty($_SESSION['user_name']) && ($_SESSION['user_is_logged_in'])) {
            $this->doLoginWithSessionData();
        } elseif (isset($_POST["login"])) {
            $this->doLoginWithPostData();
        }
    }

    /**
     * Simply starts the session.
     * It's cleaner to put this into a method than writing it directly into runApplication()
     */
    private function doStartSession()
    {
        session_start();
    }

    /**
     * Set a marker (NOTE: is this method necessary ?)
     */
    private function doLoginWithSessionData()
    {
        $this->user_is_logged_in = true; // ?
    }

    /**
     * Process flow of login with POST data
     */
    private function doLoginWithPostData()
    {
        if ($this->checkLoginFormDataNotEmpty()) {
            if ($this->createDatabaseConnection()) {
                $this->checkPasswordCorrectnessAndLogin();
            }
        }
    }

    /**
     * Logs the user out
     */
    private function doLogout()
    {
        $_SESSION = array();
        session_destroy();
        $this->user_is_logged_in = false;
        $this->feedback = "You were just logged out.";
        echo '   <script>
                    localStorage.removeItem("username");
                 </script>';
    }

    /**
     * The registration flow
     * @return bool
     */
    private function doRegistration()
    {
        if ($this->checkRegistrationData()) {
            if ($this->createDatabaseConnection()) {
                $this->createNewUser();
            }
        }
        // default return
        return false;
    }

    /**
     * Validates the login form data, checks if username and password are provided
     * @return bool Login form data check success state
     */
    private function checkLoginFormDataNotEmpty()
    {
        if (!empty($_POST['user_name']) && !empty($_POST['user_password'])) {
            return true;
        } elseif (empty($_POST['user_name'])) {
            $this->feedback = "Username field was empty.";
        } elseif (empty($_POST['user_password'])) {
            $this->feedback = "Password field was empty.";
        }
        // default return
        return false;
    }

    /**
     * Checks if user exits, if so: check if provided password matches the one in the database
     * @return bool User login success status
     */
    private function checkPasswordCorrectnessAndLogin()
    {
        // remember: the user can log in with username or email address
        $sql = 'SELECT user_name, user_email, user_password_hash
                FROM users
                WHERE user_name = :user_name OR user_email = :user_name
                LIMIT 1';
        $query = $this->db_connection->prepare($sql);
        $query->bindValue(':user_name', $_POST['user_name']);
        $query->execute();

        // Btw that's the weird way to get num_rows in PDO with SQLite:
        // if (count($query->fetchAll(PDO::FETCH_NUM)) == 1) {
        // Holy! But that's how it is. $result->numRows() works with SQLite pure, but not with SQLite PDO.
        // This is so crappy, but that's how PDO works.
        // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
        // If you meet the inventor of PDO, punch him. Seriously.
        $result_row = $query->fetchObject();
        if ($result_row) {
            // using PHP 5.5's password_verify() function to check password
            if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {
                // write user data into PHP SESSION [a file on your server]
                $_SESSION['user_name'] = $result_row->user_name;
                $_SESSION['user_email'] = $result_row->user_email;
                $_SESSION['user_is_logged_in'] = true;
                $this->user_is_logged_in = true;

                echo '<script>
                        localStorage.setItem("username", \'' . $_SESSION["user_name"] . '\');
                      </script>';
                return true;
            } else {
                $this->feedback = "Wrong password.";
            }
        } else {
            $this->feedback = "This user does not exist.";
        }
        // default return
        return false;
    }

    /**
     * Validates the user's registration input
     * @return bool Success status of user's registration data validation
     */
    private function checkRegistrationData()
    {
        // if no registration form submitted: exit the method
        if (!isset($_POST["register"])) {
            return false;
        }

        // validating the input
        if (!empty($_POST['user_name'])
            && strlen($_POST['user_name']) <= 64
            && strlen($_POST['user_name']) >= 2
            && preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])
            && !empty($_POST['user_email'])
            && strlen($_POST['user_email']) <= 64
            && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)
            && !empty($_POST['user_password_new'])
            && !empty($_POST['user_password_repeat'])
            && ($_POST['user_password_new'] === $_POST['user_password_repeat'])
        ) {
            // only this case return true, only this case is valid
            return true;
        } elseif (empty($_POST['user_name'])) {
            $this->feedback = "Empty Username";
        } elseif (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat'])) {
            $this->feedback = "Empty Password";
        } elseif ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
            $this->feedback = "Password and password repeat are not the same";
        } elseif (strlen($_POST['user_password_new']) < 6) {
            $this->feedback = "Password has a minimum length of 6 characters";
        } elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 2) {
            $this->feedback = "Username cannot be shorter than 2 or longer than 64 characters";
        } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])) {
            $this->feedback = "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters";
        } elseif (empty($_POST['user_email'])) {
            $this->feedback = "Email cannot be empty";
        } elseif (strlen($_POST['user_email']) > 64) {
            $this->feedback = "Email cannot be longer than 64 characters";
        } elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
            $this->feedback = "Your email address is not in a valid email format";
        } else {
            $this->feedback = "An unknown error occurred.";
        }

        // default return
        return false;
    }

    /**
     * Creates a new user.
     * @return bool Success status of user registration
     */
    private function createNewUser()
    {
        // remove html code etc. from username and email
        $user_name = htmlentities($_POST['user_name'], ENT_QUOTES);
        $user_email = htmlentities($_POST['user_email'], ENT_QUOTES);
        $user_password = $_POST['user_password_new'];
        // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 char hash string.
        // the constant PASSWORD_DEFAULT comes from PHP 5.5 or the password_compatibility_library
        $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);

        $sql = 'SELECT * FROM users WHERE user_name = :user_name OR user_email = :user_email';
        $query = $this->db_connection->prepare($sql);
        $query->bindValue(':user_name', $user_name);
        $query->bindValue(':user_email', $user_email);
        $query->execute();

        // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
        // If you meet the inventor of PDO, punch him. Seriously.
        $result_row = $query->fetchObject();
        if ($result_row) {
            $this->feedback = "Sorry, that username / email is already taken. Please choose another one.";
        } else {
            $sql = 'INSERT INTO users (user_name, user_password_hash, user_email)
                    VALUES(:user_name, :user_password_hash, :user_email)';
            $query = $this->db_connection->prepare($sql);
            $query->bindValue(':user_name', $user_name);
            $query->bindValue(':user_password_hash', $user_password_hash);
            $query->bindValue(':user_email', $user_email);
            // PDO's execute() gives back TRUE when successful, FALSE when not
            // @link http://stackoverflow.com/q/1661863/1114320
            $registration_success_state = $query->execute();

            if ($registration_success_state) {
                $this->feedback = 'Your account has been created successfully. You can now log in.
                                    <a href="' . $_SERVER['SCRIPT_NAME'] . '">Back to Login Area</a>';
                return true;
            } else {
                $this->feedback = "Sorry, your registration failed. Please go back and try again.";
            }
        }
        // default return
        return false;
    }

    /**
     * Simply returns the current status of the user's login
     * @return bool User's login status
     */
    public function getUserLoginStatus()
    {
        return $this->user_is_logged_in;
    }

    /**
     * Simple demo-"page" that will be shown when the user is logged in.
     * In a real application you would probably include an html-template here, but for this extremely simple
     * demo the "echo" statements are totally okay.
     */
    private function showPageLoggedIn()
    {
        if ($this->feedback) {
            echo $this->feedback . "<br/><br/>";
        }

        echo '<div class = "greeting">Hello ' . $_SESSION['user_name'] . ', you are logged in.</div>
        <a href="' . $_SERVER['SCRIPT_NAME'] . '?action=logout">Log out</a><br>';
        echo '<div class ="greeting"><br></div><div class = "greeting button" style="width: 12em" id = "showContent">Load My Training Log</div>';
    }

    /**
     * Simple demo-"page" with the login form.
     * In a real application you would probably include an html-template here, but for this extremely simple
     * demo the "echo" statements are totally okay.
     */
    private function showPageLoginForm()
    {
        if ($this->feedback) {
            echo $this->feedback . "<br/><br/>";
        }

        echo '<h3>Login</h3>';

        echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '" name="loginform">';
        echo '<table class = "buttonTable"> <tr> <td> <label for="login_input_username">Username (or email)</label> </td> ';
        echo '             <td> <input id="login_input_username" type="text" name="user_name" required /> </td> </tr> ';
        echo '        <tr> <td> <label for="login_input_password">Password</label> </td>';
        echo '             <td><input id="login_input_password" type="password" name="user_password" required /><td> ';
        echo '             <td><input type="submit"  name="login" value="Log in" /></td> </tr> </table><br>';
        echo '</form>';

        echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?action=register">Register new account</a><br><br>';

    }

    /**
     * Simple demo-"page" with the registration form.
     * In a real application you would probably include an html-template here, but for this extremely simple
     * demo the "echo" statements are totally okay.
     */
    private function showPageRegistration()
    {
        if ($this->feedback) {
            echo $this->feedback . "<br/><br/>";
        }

        echo '<h3>Registration</h3>';

        echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?action=register" name="registerform">';
        echo '<table class="buttonTable"> <tr> <td> <label for="login_input_username">Username (only letters and numbers, 2 to 64 characters)</label> </td>';
        echo '             <td> <input id="login_input_username" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required /> </td> </tr>';
        echo '        <tr> <td> <label for="login_input_email">User\'s email</label> </td>';
        echo '             <td> <input id="login_input_email" type="email" name="user_email" required /></td> </tr>';
        echo '        <tr> <td> <label for="login_input_password_new">Password (min. 6 characters)</label> </td>';
        echo '             <td> <input id="login_input_password_new" class="login_input" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" /></td></tr>';
        echo '        <tr> <td> <label for="login_input_password_repeat">Repeat password</label> </td>';
        echo '             <td> <input id="login_input_password_repeat" class="login_input" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" /></td></tr>';
        echo '        <tr> <td colspan="3">By creating an account you agree to the following terms of service: <br>
                                            <ul> <li> I understand that Total Training Log is not responsible for any loss of life, limb, or fat that may or <br>
                                             may not result from the use of Total Training Log.</li>
                                                 <li> I also understand that because Total Training Log is still in beta, it is regularly undergoing <br>
                                             changes, both minor and major. </li>
                                                 <li> I also am aware that as Total Training Log evolves, Total Training Log and affiliates cannot <br>
                                             guarantee that any training I log or even my account will be preserved. </li>
                                                 <li> I agree that Total Training Log brand, logos, and script is solely the property of Total Training <br>
                                             Log and may not be reproduced without prior permission. </li></ul></td> </tr>';
        echo '        <tr> <td> <input type="submit" name="register" value="Register" /> </td></tr></table><br>';
        echo '</form>';

        echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '">Back to Login Area</a>';
    }
}

// run the application
$application = new OneFileLoginApplication();

?>

<!-----------------------------    END USER LOGIN SCRIPT    -----------------------------
 --------------------------------       BEGIN HTML      --------------------------------->


<div id="content">


<div>
   <!-- <div class="button" style="width: 6em" data-bind='click: addWeek'>Add Week</div> <br>--->

   <table class="buttonTable">
       <tr>
           <td><br><div class="button" style="width: 8em" data-bind='click: previousMonth'>Previous Month</div></td>
           <td><h2 data-bind="text: shownMonthName"></h2></td>
           <td><br><div class="button" style="width: 6em" data-bind='click: nextMonth'>Next Month</div></td>
       </tr>
   </table>

<span data-bind="foreach: weeks">

    <div class="dayTabs">
        <ul data-bind="foreach:days">
            <li data-bind="text:name"></li>
        </ul>
        <!--ko foreach: {data: days, as: 'day'}-->
        <div>
            <br>
            <div class='jqxMenu' style='visibility: hidden; margin-left: 20px;  margin-right: 20px; height: 40px;'>
                <ul>
                    <li>
                        <div data-bind="if: $root.exerciseMasterBool">
                            <select data-bind="options: $root.exerciseMasterList,
                                     optionsText: 'name',
                                     value: $root.returnedName,
                                     optionsCaption: 'Select an exercise...'"></select>



                        </div>
                    </li>
                    <li><div class="button" data-bind='click: $root.addExercise.bind($data, $root.returnedName)'>Add</div></li>
                    <li><div class="button" data-bind="click: $root.addPreviousExercise.bind($data, $root.previousExercise)">Paste Exercise</div></li>
                    <li><div class="button" data-bind="click: $root.dialogOpener">Edit My Exercises</div></li>

                </ul>
            </div>
            <br>


            <!--if weight training is logged then show everything inside this span-->


								<span data-bind="if: exercises">
									<div data-bind="foreach: {data: exercises, as: 'exercise'}">
                                        <div data-bind="ifnot: weightTraining">
                                            <div class="jqxExpander" style='margin-left: 20px;  margin-right: 20px;'>
                                                <div><span data-bind="text: name, uniqueName: true"></span> <!--ko if: planned--><strong> - Planned </strong><!--/ko--></div>
                                                <div>

                                                    <table class="exerciseTable"  data-bind="ifnot: editing">
                                                        <thead>
                                                        <tr data-bind="foreach: labels">
                                                            <th data-bind="text: value"></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody data-bind="foreach: sets">
                                                        <tr data-bind="foreach: values">
                                                            <td data-bind='text: value, uniqueName: true'></td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                    <table class="exerciseTable"  data-bind="if: editing">
                                                        <thead>
                                                        <tr>
                                                            <!--ko foreach: labels-->
                                                            <th data-bind="text: value"></th>
                                                            <!--/ko-->
                                                            <th></th>
                                                        </tr>

                                                        </thead>
                                                        <tbody data-bind="foreach: sets">
                                                        <tr>
                                                            <!--ko foreach: values-->
                                                            <td>
                                                                <input data-bind='value: value, uniqueName: true' />
                                                            </td>
                                                            <!--/ko-->
                                                            <td>
                                                                <div class="button" data-bind='click: $root.removeSet.bind($data, exercise, day)'>Delete Set</div>
                                                            </td>
                                                        </tr>

                                                        </tbody>
                                                    </table>

                                                    <!--ko if:editing-->
                                                    <div class="textarea">
                                                        <textarea data-bind='value: notes'></textarea>
                                                    </div>
                                                    <!--/ko-->

                                                    <!--ko ifnot:editing-->
                                                    <div class="textarea">
                                                        <strong>Notes: </strong><span data-bind='text: notes'></span><br>
                                                    </div>
                                                    <!--/ko-->

                                                    <div class='jqxMenu' style='visibility: hidden; margin-left: 20px;  margin-right: 20px; height: 40px;'>
                                                        <ul>
                                                            <!--ko if:editing-->
                                                            <li>
                                                                <div class="button" data-bind='click: $root.addSet'>Add Set</div>

                                                            </li>
                                                            <!--/ko-->
                                                            <li>
                                                                <span data-bind="ifnot: editing">
													                <div class = "edit button" data-bind='
																            click: function() {
																            editing(true)
																        }
																    '>Edit</div>
												                </span>
												                <span data-bind="if: editing">
													                <div class = "save button" data-bind='
                                                                        click: function() {
																            editing(false);
																            $root.saveAll()
																        }
																    '>Save</div>
												                </span>
                                                            </li>
                                                            <!--ko if:editing-->
                                                            <li>
                                                                <span data-bind="ifnot: planned">
													                <div class = "button" data-bind='
																            click: function() {
																            planned(true);
																            $root.saveAll()
																        }
																    '>Make Exercise Planned</div>
												                </span>
												                <span data-bind="if: planned">
													                <div class = "button" data-bind='
                                                                        click: function() {
																            planned(false);
																            $root.saveAll()
																        }
																    '>Log Planned Exercise</div>
												                </span>
                                                            </li>
                                                            <!--/ko-->
                                                            <li>
                                                                <div class="button" data-bind='click: $root.removeExercise.bind($data, day)'>Delete</div>
                                                            </li>
                                                            <li>
                                                                <div class="button" data-bind="click:$root.copyExercise">Copy Exercise</div>
                                                            </li>

                                                        </ul>
                                                    </div>
                                                    <br>



                                                </div>
                                            </div>
                                        </div>

                                        <div data-bind="if: weightTraining">
                                            <div>
                                                <div class="jqxExpander" style='margin-left: 20px;  margin-right: 20px;'>
                                                    <div>
                                                        <span data-bind="text: name, uniqueName: true"></span>
                                                        <!--ko if: planned--><strong> - Planned </strong><!--/ko-->
                                                    </div>
                                                    <div>
                                                        <table class="exerciseTable" data-bind="ifnot: editing">
                                                            <thead>
                                                            <tr>
                                                                <th>Exercise</th>
                                                                <th colspan="3"></th>
                                                            </tr>
                                                            </thead>
                                                            <!--for each exercise, create a row in the table-->
                                                            <tbody  data-bind="foreach: {data: exercises, as: 'weightTrainingExercise'}">
                                                            <tr>
                                                                <!--value inputed here corresponds to the title of the exercise-->
                                                                <td><span class='required' data-bind='text: name, uniqueName: true'></span></td>

                                                                <td colspan="2">
                                                                    <table>
                                                                        <tbody>
                                                                        <tr>
                                                                            <td><strong>Weight</strong></td>
                                                                            <!--ko foreach: sets-->
                                                                            <td>
                                                                                <span data-bind='text: weight, uniqueName: true'></span>
                                                                            </td>
                                                                            <!--/ko-->
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Reps</strong></td>
                                                                            <!--ko foreach: sets-->
                                                                            <td>
                                                                                <span data-bind='text: reps, uniqueName: true'></span>
                                                                            </td>
                                                                            <!--/ko-->
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <div class="button" data-bind='click: $root.removeWeightTraining.bind($data, exercise, day)'>Delete</div>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                        <table class="exerciseTable" data-bind="if: editing">
                                                            <thead>
                                                            <tr>
                                                                <th>Exercise</th>
                                                                <th colspan="4"></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody data-bind="foreach: {data: exercises, as: 'weightTrainingExercise'}">
                                                            <tr>
                                                                <td>
                                                                    <input class='required' data-bind='value: name, uniqueName: true' />
                                                                </td>
                                                                <td colspan="2">
                                                                    <table>
                                                                        <tbody>
                                                                        <tr>
                                                                            <td><strong>Weight</strong></td>
                                                                            <!--ko foreach: sets-->
                                                                            <td>
                                                                                <input data-bind='value: weight, uniqueName: true' size="7"/>
                                                                            </td>
                                                                            <!--/ko-->
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Reps</strong></td>
                                                                            <!--ko foreach: sets-->
                                                                            <td>
                                                                                <input data-bind='value: reps, uniqueName: true' size="7"/>
                                                                            </td>
                                                                            <!--/ko-->
                                                                        </tr>
                                                                        <tr>
                                                                            <td></td>
                                                                            <!--ko foreach: sets-->
                                                                            <td>
                                                                                <div class="button" data-bind="click: $root.removeWeightsSet.bind($data, weightTrainingExercise, exercise, day)">Delete Set</div>
                                                                            </td>
                                                                            <!--/ko-->
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                                <td><div class="button" data-bind='click: $root.addWeightsSet'>Add Set</div></td>
                                                                <td>
                                                                    <div class="button" data-bind='click: $root.removeWeightTraining.bind($data, exercise, day)'>Delete</div>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>

                                                        <!--ko if:editing-->
                                                        <div class="textarea">
                                                            <textarea data-bind='value: notes'></textarea>
                                                        </div>
                                                        <!--/ko-->

                                                        <!--ko ifnot:editing-->
                                                        <div class="textarea">
                                                            <strong>Notes: </strong><span data-bind='text: notes'></span><br>
                                                        </div>
                                                        <!--/ko-->


                                                        <div class='jqxMenu' style='visibility: hidden; margin-left: 20px;  margin-right: 20px; height: 40px;'>
                                                            <ul>
                                                                <!--ko if:editing-->
                                                                <li>
                                                                    <div class="button" data-bind='click: $root.addWeightsExercise'>Add Exercise</div>

                                                                </li>
                                                                <!--/ko-->
                                                                <li>
                                                                <span data-bind="ifnot: editing">
													                <div class = "edit button" data-bind='
																            click: function() {
																            editing(true)
																        }
																    '>Edit</div>
												                </span>
												                <span data-bind="if: editing">
													                <div class = "save button" data-bind='
                                                                        click: function() {
																            editing(false);
																            $root.saveAll()
																        }
																    '>Save</div>
												                </span>
                                                                </li>
                                                                <!--ko if:editing-->
                                                                <li>
                                                                <span data-bind="ifnot: planned">
													                <div class = "button" data-bind='
																            click: function() {
																            planned(true);
																            $root.saveAll()
																        }
																    '>Make Exercise Planned</div>
												                </span>
												                <span data-bind="if: planned">
													                <div class = "button" data-bind='
                                                                        click: function() {
																            planned(false);
																            $root.saveAll()
																        }
																    '>Log Planned Exercise</div>
												                </span>
                                                                </li>
                                                                <!--/ko-->
                                                                <li>
                                                                    <div class="button" data-bind='click: $root.removeExercise.bind($data, day)'>Delete</div>
                                                                </li>
                                                                <li>
                                                                    <div class="button" data-bind="click:$root.copyExercise">Copy Exercise</div>
                                                                </li>

                                                            </ul>
                                                        </div>
                                                        <br>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
								</span>

            <br>
        </div>
        <!--/ko-->
    </div>

</span>
</div>
<div id="dialog">

    <!--ko if: exerciseMasterBool -->
        <!--ko foreach: {data: exerciseMasterList, as: 'exercise'}-->
            <!--ko ifnot: weightTraining-->
            <div class="jqxExpander" style='margin-left: 20px;  margin-right: 20px;'>
                <div> <span data-bind="text: name"></span> </div>
                <div>

                    <table class="exerciseTable">
                        <thead>
                            <tr>
                                <th>Exercise Name</th>
                                <th>Input Fields</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input data-bind="value: name" /></td>
                                <td>
                                    <div class="button" data-bind="click: $root.addFieldToMaster">Add Field</div>
                                </td>
                                <td></td>

                            </tr>
                            <!--ko foreach: labels-->
                            <tr>
                                <td></td>
                                <td>
                                    <input data-bind="value: value" />
                                </td>
                                <td>
                                    <div class="button" data-bind="click: $root.removeFieldFromMaster.bind($data, exercise)">Delete Field</div>
                                </td>
                            </tr>
                            <!--/ko-->

                        </tbody>
                    </table>

                    <div class="linkButton button" style='margin: 10px;  width: 10em; margin-left: 5em; margin-top: 0px' data-bind="click: $root.removeFromMaster">Delete Exercise</div>
                </div>
            </div>
            <!--/ko-->
        <!--/ko-->
    <!--/ko-->




    <div class="button" style="width: 6em; margin: 10px; float: left; margin-left: 5em" data-bind="click: $root.addExerciseToMaster">Add Exercise</div>
    <div class="button" style="width: 6em; margin: 10px; margin-left: 15em" data-bind="click: $root.saveMaster">Save</div>
</div>

</div>


<!--loading dialog -->
<div id="loading_dialog"></div>




</body>

</html>