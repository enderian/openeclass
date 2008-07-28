<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/


$require_admin = TRUE;
include '../../include/baseTheme.php';
include '../../include/lib/fileUploadLib.inc.php';
include '../../include/lib/fileManageLib.inc.php';
include '../../include/pclzip/pclzip.lib.php';
$nameTools = $langRestoreCourse;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);

// Initialise $tool_content
$tool_content = "";
// Main body

if (isset($send_archive) and $_FILES['archiveZipped']['size'] > 0) {
	$tool_content .= "<table width=\"99%\"><caption>".$langFileSent."</caption><tbody>
	<tr><td width=\"3%\"nowrap>$langFileSentName</td><td>".$_FILES['archiveZipped']['name']."</td></tr>
	<tr><td width=\"3%\"nowrap>$langFileSentSize</td><td>".$_FILES['archiveZipped']['size']."</td></tr>
	<tr><td width=\"3%\"nowrap>$langFileSentType</td><td>".$_FILES['archiveZipped']['type']."</td></tr><tr>
	<td width=\"3%\"nowrap>$langFileSentTName</td><td>".$_FILES['archiveZipped']['tmp_name']."</td></tr>";
	$tool_content .= "</tbody></table><br>";
	$tool_content .= "<table width=\"99%\"><caption>".$langFileUnzipping."</caption><tbody>";
	$tool_content .= "<tr><td>".unpack_zip_show_files($archiveZipped)."</td></tr>";
	$tool_content .= "<tbody></table><br>";
}
elseif (isset($create_dir_for_course)) {
	//Try to create course with data uploaded
	$r = $restoreThis."/html";
	$course_code = create_course($course_code, $course_lang, $course_title,
		$course_desc, $course_fac, $course_vis, $course_prof, $course_type);
	move_dir($r, "$webDir/courses/$course_code");
	course_index("$webDir/courses/$course_code", $course_code);
	$tool_content .= "<p>$langCopyFiles $webDir/courses/$course_code</p><br><p>";
	$action = 1;
	$userid_map = array();
	// now we include the file for restoring
	ob_start();
	include("$restoreThis/backup.php");
	$tool_content .= ob_get_contents();
	ob_end_clean();
	$tool_content .= "</p>";
	if (!file_exists("../../courses/garbage"))
		mkdir("../../courses/garbage");
	if (!file_exists("../../courses/garbage/tmpUnzipping"))
		mkdir("../../courses/garbage/tmpUnzipping");
	rename("../../courses/tmpUnzipping", "../../courses/garbage/tmpUnzipping/".time()."");
	$tool_content .= "<br><center><p><a href=\"../admin/index.php\">$langBack</p></center>";
}
elseif (isset($send_path) and !empty($pathToArchive)) {
	if (file_exists($pathToArchive))
		unpack_zip_show_files($pathToArchive);
	else
		$tool_content .= $langFileNotFound;
	
}
elseif (isset($pathOf4path)) {
	// we know  where is the 4 paths to restore  the  course.
	// 2 Show content
	// $restoreThis: contains the path of the archived course

	// If $action == 0, the course isn't restored - the user just
	// gets a form with the archived course details.	
	$action = 0;
	ob_start();
	include("$restoreThis/backup.php");
	$tool_content .= ob_get_contents();
	ob_end_clean();
} else {

// -------------------------------------
// Displaying Form
// -------------------------------------
	$tool_content .= "<table width=\"99%\"><caption>$langFirstMethod</caption><tbody>
	<tr><td>$langRequest1<br><br><form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" name=\"sendZip\"  enctype=\"multipart/form-data\">
	<input type=\"file\" name=\"archiveZipped\" >
	<input type=\"submit\" name=\"send_archive\" value=\"".$langSend."\">
	</form></td></tr>
	</tbody></table><br>";
	$tool_content .= "<table width=\"99%\"><caption>$langSecondMethod</caption><tbody>
	<tr><td>$langRequest2<br><br><form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" name=\"sendPath\"  enctype=\"multipart/form-data\">
	<input type=\"text\" name=\"pathToArchive\">
	<input type=\"submit\" name=\"send_path\" value=\"".$langSend."\">
	</form></td></tr>
	</tbody></table><br>";
}

draw($tool_content,3, 'admin');


// Functions  restoring
function course_details ($code, $lang, $title, $desc, $fac, $vis, $prof, $type) {
	
	global $action, $restoreThis, $langNameOfLang;
	
	@include("../lang/greek/common.inc.php");
	@include("../lang/greek/messages.inc.php");

	//check for lesson language
	$languages = array();
	$langdirname = "../lang/";
	$handle = opendir($langdirname);
	if (!$handle) { die($langErrorLang); }
	while ($entries = readdir($handle)) {
		if ($entries == '.' or $entries == '..' or $entries == 'CVS')
			continue;
		if (is_dir($langdirname.$entries)) {
			if (isset($langNameOfLang[$entries])) {
				$languages[$entries] = $langNameOfLang[$entries];
			} else {
				$languages[$entries] = $entries;
			}
		}
	}
	closedir($handle);

//display the restoring form
	if (!$action) {
		echo "<form action='$_SERVER[PHP_SELF]' method='post'>";
  		echo "<table border='0'>";
		echo "<tr><td align='justify' colspan='2'>$langInfo1</td></tr>";
		echo "<tr><td align='justify' colspan='2'>$langInfo2</td></tr>";
		echo "<tr><td>&nbsp;</td></tr>";
		echo "<tr><td>$langCourseCode:</td><td><input type='text' name='course_code' value='$code'></td></tr>";
		echo "<tr><td>$langLanguage:</td><td>".selection($languages, 'course_lang', $lang)."</td></tr>";
		echo "<tr><td>$langTitle:</td><td><input type='text' name='course_title' value='$title' size='50'></td></tr>";
		echo "<tr><td>$langCourseDescription:</td><td><input type='text' name='course_desc' value='$desc' size='50'></td></tr>";
		echo "<tr><td>$langCourseFac:</td><td>".faculty_select($fac)."</td></tr>";
		echo "<tr><td>$langCourseOldFac:</td><td>$fac</td></tr>";
		echo "<tr><td>$langCourseVis:</td><td>".visibility_select($vis)."</td></tr>";
		echo "<tr><td>$langTeacher:</td><td><input type='text' name='course_prof' value='$prof' size='50'></td></tr>";
		echo "<tr><td>$langCourseType:</td><td>".type_select($type)."</td></tr>";
		echo "<tr><td>&nbsp;</td></tr>";
		echo "<tr><td colspan='2'><input type='checkbox' name='course_addusers' checked>$langUsersWillAdd </td></tr>";
		echo "<tr><td colspan='2'><input type='checkbox' name='course_prefix' checked>$langUserPrefix</td></tr>";
		echo "<tr><td>&nbsp;</td></tr><tr><td>";
		echo "<input type='submit' name='create_dir_for_course' value='$langOk'>";
		echo "<input type='hidden' name='restoreThis' value='$restoreThis'>";
		echo "</td></tr></table></form>";
	}
}

// inserting announcements into the main database
function announcement ($text, $date, $order) {
	global $action, $course_code, $mysqlMainDb;
	if (!$action) return;
	db_query("INSERT into `$mysqlMainDb`.annonces
		(contenu,code_cours,temps,ordre)
		VALUES (".
		join(", ", array(	
			quote($text),
			quote($course_code),
			quote($date),
			quote($order))).
			")");
}


// inserting users into the main database
function user ($userid, $name, $surname, $login, $password, $email, $statut, $phone, $department, $inst_id) {
	global $action, $course_code, $userid_map, $mysqlMainDb, $course_prefix, $course_addusers;
	global $langUserWith, $langAlready, $langWithUsername, $langUserisAdmin, $langUsernameSame, $langUserAlready, $langUName, $langPrevId, $langNewId, $langUserName;
	
	if (!$action) return;
	if (!$course_addusers and $statut != 1)  return;
	if (isset($userid_map[$userid])) {
		echo "<br>$langUserWith $userid_map[$userid] $langAlready\n";
		return;
	}
	// add prefix only to usernames that dont use LDAP login
	if ($course_prefix) {
		if ($statut == 1) {
			echo "<br>$langWithUsername $login $langUserisAdmin".
				" - $langUsernameSame";
		} else {
			$login = $course_code.'_'.$login;
		}
	}
	
	$u = mysql_query("SELECT * FROM `$mysqlMainDb`.user WHERE BINARY username=".quote($login));
	if (mysql_num_rows($u) > 0) 	{
		$res = mysql_fetch_array($u);
		$userid_map[$userid] = $res['user_id'];
		echo "<br>"; 
		echo "$langUserAlready <b>$login</b>. $langUName <i>$res[1] $res[2]</i>  !\n";
	} else {
		db_query("INSERT into `$mysqlMainDb`.user
			(nom, prenom, username, password, email, statut, phone, department)
			VALUES (".
			join(", ", array(	
				quote($name),
				quote($surname),
				quote($login),
				quote($password),
				quote($email),
				quote($statut),
				quote($phone),
				quote($department))).
				")");
		$userid_map[$userid] = mysql_insert_id();
	}
	
	db_query("INSERT into `$mysqlMainDb`.cours_user
		(code_cours,user_id,statut)
		VALUES (".
		join(", ", array(	
			quote($course_code),
			quote($userid_map[$userid]),
			quote($statut))).
			")");
	echo "<br> $langUserName=$login, $langPrevId=$userid, $langNewId=$userid_map[$userid]\n";
}

function query($sql) {
	global $action, $course_code;
	if (!$action) return;
	mysql_select_db($course_code);
	db_query($sql);
}

// function for inserting info about user group
function group( $userid, $team, $status, $role) {
	global $action, $userid_map, $course_code, $course_addusers;
	if (!$action) return;
	if (!$course_addusers) return;
	mysql_select_db($course_code);
	db_query("INSERT into user_group
		(user,team,status,role)
		VALUES (".
		join(", ", array(	
			quote($userid_map[$userid]),
			quote($team),
			quote($status),
			quote($role))).
			")");
	
}

// functions for inserting info about dropbox
function dropbox_file($userid, $filename, $filesize, $title, $description, $author, $uploadDate, $lastUploadDate) {
	global $action,$userid_map, $course_code, $course_addusers;
	if (!$action) return;
	if (!$course_addusers) return;
	mysql_select_db($course_code);
	db_query("INSERT into dropbox_file
		(uploaderId,filename,filesize,title,description,author,uploadDate,lastUploadDate)
		VALUES (".
		join(", ", array(
			quote($userid_map[$userid]),
			quote($filename),
			quote($filesize),
			quote($title),
			quote($description),
			quote($author),
			quote($uploadDate),
			quote($lastUploadDate))).
			")");
}

function dropbox_person($fileId, $personId) {
	global $action, $userid_map, $course_code, $course_addusers;
	if (!$action) return;
	if (!$course_addusers) return;
	mysql_select_db($course_code);
	db_query("INSERT into dropbox_person(fileId, personId)
		VALUES (".
		join(", ", array(
			quote($fileId),
			quote($userid_map[$personId]))).")");

}

function dropbox_post($fileId, $recipientId) {
	global $action, $userid_map, $course_code, $course_addusers;
	if (!$action) return;
	if (!$course_addusers) return;
	mysql_select_db($course_code);
	db_query("INSERT into dropbox_post (fileId, recipientId)
		VALUES (".
		join(", ", array(
			quote($fileId),
			quote($userid_map[$recipientId]))).")");
}


// insert an assignment submission, translating user id's
function assignment_submit($userid, $assignment_id, $submission_date,
	$submission_ip, $file_path, $file_name, $comments,
	$grade, $grade_comments, $grade_submission_date,
	$grade_submission_ip)
{
	global $action, $userid_map, $course_code, $course_addusers;
	if (!$action) return;
	if (!$course_addusers) return;
	mysql_select_db($course_code);
	$values = array();
	foreach (array($assignment_id, $submission_date,
		$submission_ip, $file_path, $file_name, $comments,
		$grade, $grade_comments, $grade_submission_date,
		$grade_submission_ip) as $v) {
		$values[] = quote($v);
	}
	db_query("INSERT into assignment_submit
		(uid, assignment_id, submission_date,
		 submission_ip, file_path, file_name,
		 comments, grade, grade_comments, grade_submission_date,
		 grade_submission_ip) VALUES (".
		 quote($userid_map[$userid]). ", ".
		 join(", ", $values). ")");
}

// creating course and inserting entries into the main database
function create_course($code, $lang, $title, $desc, $fac, $vis, $prof, $type) {
	global $mysqlMainDb;

	$repertoire = new_code(find_faculty_by_name($fac));
	
	if (mysql_select_db($repertoire)) {
		echo $langCourseExists;
		exit;
	}
	db_query("INSERT into `$mysqlMainDb`.cours
		(code, languageCourse, intitule, description, faculte, visible, titulaires, fake_code, type) 
		VALUES (".
		join(", ", array(	
			quote($repertoire),
			quote($lang),
			quote($title),
			quote($desc),
			quote($fac),
			quote($vis),
			quote($prof),
			quote($code),
			quote($type))).
		")");
	db_query("INSERT into `$mysqlMainDb`.cours_faculte
		(faculte,code)
		VALUES(".quote($fac).",".quote($repertoire).")");
						
	if (!db_query("CREATE DATABASE `$repertoire`")) {
		echo "Database $repertoire creation failure ";
		exit;
	}
	return $repertoire;
}

// crating course index.php file
function course_index($dir, $code) {
	$f = fopen("$dir/index.php", "w");
	fputs($f, "<?php
session_start();
\$dbname=\"$code\";
session_register(\"dbname\");
include(\"../../modules/course_home/course_home.php\");
?>
");
	fclose($f);
}


// form select about visibility
function visibility_select($current)
{
	global $langTypeOpen, $langTypeRegistration, $langTypeClosed;
	$ret = "";
 
	$ret .= "<select name=\"course_vis\">\n";
	foreach (array($langTypeOpen => '2', $langTypeRegistration => '1', $langTypeClosed => '0') 
			as $text => $type) {
		if($type == $current) {
			$ret .= "<option value=\"$type\" selected>$text</option>\n";
		} else {
			$ret .= "<option value=\"$type\">$text</option>\n";
		}
	}
	$ret .= "</select>\n";
return $ret;
}

// form select about type
function type_select($current)
{
	global $langPre, $langPost, $langOther;
	
	$ret = "";
	$ret .= "<select name=\"course_type\">\n";
	foreach (array($langPre => 'pre', $langPost => 'post', $langOther => 'other') as $text => $type) {
		if($type == $current) {
			$ret .= "<option value=\"$type\" selected>$text</option>\n";
		} else {
			$ret .= "<option value=\"$type\">$text</option>\n";
		}
	}
	$ret .= "</select>\n";
return $ret;
}

// form select about faculty
function faculty_select($current)
{
	global $mysqlMainDb;
	$ret = "";
	
	$ret .= "<select name=\"course_fac\">\n";
	$res = mysql_query("SELECT name FROM `$mysqlMainDb`.faculte ORDER BY number");
	while ($fac = mysql_fetch_array($res)) {
		if($fac['name'] == $current) {
			$ret .= "<option selected>$fac[name]</option>\n";
		} else {
			$ret .= "<option>$fac[name]</option>\n";
		}
	}
	$ret .= "</select>\n";
return $ret;
}

// Unzip backup file
function unpack_zip_show_files($zipfile)
{
	global $webDir, $uid, $langEndFileUnzip, $langLesFound, $langRestore, $langLesFiles;

	$retString = "";

	$destdir = $webDir."courses/tmpUnzipping/".$uid;
	mkpath("$destdir");
	$zip = new pclZip($zipfile);
	chdir($destdir);
	$state = $zip->extract(PCLZIP_OPT_REMOVE_PATH, "courses/");

	$retString .= "<br>$langEndFileUnzip<br><br>$langLesFound<ol>";
	$dirnameCourse = realpath("$destdir/archive/");
	if($dirnameCourse[strlen($dirnameCourse)-1] != '/')
		$dirnameCourse .= '/';
	$handle = opendir($dirnameCourse);

	while ($entries = readdir($handle)) {
		if ($entries == '.' or $entries == '..' or $entries == 'CVS')
			continue;
		if (is_dir($dirnameCourse.$entries))
			$retString .= "<li>".$entries."<br>".$langLesFiles."
			<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" name=\"restoreThis\">
			<ol>";
			$dirnameArchive = realpath("$destdir/archive/$entries/");
			if($dirnameArchive[strlen($dirnameArchive)-1]!='/')
				$dirnameArchive.='/';
			$handle2=opendir($dirnameArchive);
			while ($entries = readdir($handle2)) {
				if ($entries=='.'||$entries=='..'||$entries=='CVS')
					continue;
				if (is_dir($dirnameArchive.$entries))
					$retString.= "<li>
					<input type=\"radio\" checked name=\"restoreThis\" value=\"".realpath($dirnameArchive.$entries)."\"> ".$entries."
				</li>";
			}
			closedir($handle2);
			$retString .= "</ol><br>
			<input type=\"submit\" value=\"$langRestore\" name=\"pathOf4path\">
			</form></li>";
	}
	closedir($handle);
	$retString .= "</ol>\n";	
	chdir($webDir."modules/course_info");
	return $retString;
}
?>
