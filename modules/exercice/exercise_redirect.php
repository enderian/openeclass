<?php 
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/


include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
include('exercise.lib.php');
 
// answer types
define('UNIQUE_ANSWER',1);
define('MULTIPLE_ANSWER',2);
define('FILL_IN_BLANKS',3);
define('MATCHING',4);

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Exercise';

include '../../include/baseTheme.php';

$tool_content = "";
$nameTools = $langExercice;
include('../../include/lib/textLib.inc.php');

$picturePath='../../courses/'.$currentCourseID.'/image';
$is_allowedToEdit=$is_adminOfCourse;
$dbNameGlu=$currentCourseID;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

$navigation[]=array("url" => "exercice.php","name" => $langExercices);

// if the object is not in the session
if(!session_is_registered('objExercise')) {
	// construction of Exercise
	$objExercise=new Exercise();

	// if the specified exercise doesn't exist or is disabled
	if(!$objExercise->read($exerciseId) && (!$is_allowedToEdit))
		{
		$tool_content .= $langExerciseNotFound;
		draw($tool_content, 2);
		exit();
	}

	// saves the object into the session
	session_register('objExercise');
}

setcookie("marvelous_cookie", "", time() - 3600, "/");
setcookie("marvelous_cookie_control", "", time() - 3600, "/");

$exerciseTitle=$objExercise->selectTitle();

$tool_content .= <<<cData
	<h3>${exerciseTitle}</h3>
	<p>${langExerciseExpired}</p>
	<p><center><a href="exercice.php">${langBack}</a></center></p>
cData;

draw($tool_content, 2);
?>
