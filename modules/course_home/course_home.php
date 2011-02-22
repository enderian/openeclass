<?php
/*===========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2010  Greek Universities Network - GUnet
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
*	Contact address:	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

/*
 * Course Home Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component creates the content for the course's home page
 *
 */

$require_current_course = TRUE;
$guest_allowed = true;
define('HIDE_TOOL_TITLE', 1);

//$courseHome is used by the breadcrumb logic
//See function draw() in baseTheme.php for details
//$courseHome = true;
//$path2add is used in init.php to fix relative paths
$path2add=1;
include '../../include/baseTheme.php';
$nameTools = $langIdentity;
$tool_content = $head_content = $main_content = $cunits_content = $bar_content = "";
add_units_navigation(TRUE);
$head_content .= '
<script type="text/javascript">
function confirmation ()
{
    if (confirm("'.$langConfirmDelete.'"))
        {return true;}
    else
        {return false;}
}
</script>
';

//For statistics: record login
$sql_log = "INSERT INTO logins SET user_id='$uid', ip='$_SERVER[REMOTE_ADDR]', date_time=NOW()";
db_query($sql_log, $currentCourse);
include '../../include/action.php';
$action = new action();
$action->record('MODULE_ID_UNITS');

$res = db_query("SELECT course_keywords, faculte, type, visible, titulaires, fake_code
                 FROM cours WHERE cours_id = $cours_id", $mysqlMainDb);
$result = mysql_fetch_array($res);
$keywords = q(trim($result['course_keywords']));
$faculte = $result['faculte'];
$type = $result['type'];
$visible = $result['visible'];
$professor = $result['titulaires'];
$fake_code = $result['fake_code'];

$main_extra = $description = $addon = '';
$res = db_query("SELECT res_id, title, comments FROM unit_resources WHERE unit_id =
                        (SELECT id FROM course_units WHERE course_id = $cours_id AND `order` = -1)
                        AND (visibility = 'v' OR res_id < 0)
                 ORDER BY `order`");
if ($res and mysql_num_rows($res) > 0) {
        while ($row = mysql_fetch_array($res)) {
                if ($row['res_id'] == -1) {
                        $description = standard_text_escape($row['comments']);
                } elseif ($row['res_id'] == -2) {
                        $addon = standard_text_escape($row['comments']);
                } else {
                        if (isset($idBloc[$row['res_id']]) and !empty($idBloc[$row['res_id']])) {
                                $element_id = "class='course_info' id='{$idBloc[$row['res_id']]}'";
                        } else {
                                $element_id = 'class="course_info other"';
                        }
                        $main_extra .= "<div $element_id><h1>" . q($row['title']) . "</h1>" .
                                standard_text_escape($row['comments']) . "</div>\n";
                }
        }
}
if ($is_adminOfCourse) {
        $edit_link = "&nbsp;<a href='../../modules/course_description/editdesc.php'><img src='../../template/classic/img/edit.png' title='$langEdit' alt='icon' /></a>";
} else {
        $edit_link = '';
}
$main_content .= "\n      <div class='course_info'>";
if (!empty($description)) {
        $main_content .= "\n      <div class='descr_title'>$langDescription$edit_link</div>\n$description";

} else {
        $main_content .= "\n      <p>$langThisCourseDescriptionIsEmpty$edit_link</p>";
}
if (!empty($keywords)) {
	$main_content .= "\n      <p id='keywords'><b>$langCourseKeywords</b> $keywords</p>";
}
$main_content .= "\n      </div>\n";

if (!empty($addon)) {
	$main_content .= "\n      <div class='course_info'><h1>$langCourseAddon</h1><p>$addon</p></div>";
}
$main_content .= $main_extra;

units_set_maxorder();

// other actions in course unit
if ($is_adminOfCourse) {
        if (isset($_REQUEST['edit_submit'])) {
                $main_content .= handle_unit_info_edit();
        } elseif (isset($_REQUEST['del'])) { // delete course unit
		$id = intval($_REQUEST['del']);
		db_query("DELETE FROM course_units WHERE id = '$id'");
		db_query("DELETE FROM unit_resources WHERE unit_id = '$id'");
		$main_content .= "<p class='success_small'>$langCourseUnitDeleted</p>";
	} elseif (isset($_REQUEST['vis'])) { // modify visibility
		$id = intval($_REQUEST['vis']);
		$sql = db_query("SELECT `visibility` FROM course_units WHERE id='$id'");
		list($vis) = mysql_fetch_row($sql);
		$newvis = ($vis == 'v')? 'i': 'v';
		db_query("UPDATE course_units SET visibility = '$newvis' WHERE id = $id AND course_id = $cours_id");
	} elseif (isset($_REQUEST['down'])) {
		$id = intval($_REQUEST['down']); // change order down
                move_order('course_units', 'id', $id, 'order', 'down',
                           "course_id=$cours_id");

	} elseif (isset($_REQUEST['up'])) { // change order up
		$id = intval($_REQUEST['up']);
                move_order('course_units', 'id', $id, 'order', 'up',
                           "course_id=$cours_id");
	}
}

// add course units
if ($is_adminOfCourse) {
        $cunits_content .= "\n  <div id='operations_container'>\n    <ul id='opslist'>" .
                        "\n      <li>$langCourseUnits : <a href='{$urlServer}modules/units/info.php'>$langAddUnit</a>&nbsp;<a href='{$urlServer}modules/units/info.php'><img src='../../template/classic/img/add.png' width='16' height='16 alt='icon'' title='$langAddUnit' alt='$langAddUnit' /></a></li>" .
                        "\n    </ul>\n  </div>\n";
}
        //$cunits_content .= "</p>\n\n\n";
if ($is_adminOfCourse) {
        list($last_id) = mysql_fetch_row(db_query("SELECT id FROM course_units
                                                   WHERE course_id = $cours_id AND `order` >= 0
                                                   ORDER BY `order` DESC LIMIT 1"));
	$query = "SELECT id, title, comments, visibility
		  FROM course_units WHERE course_id = $cours_id AND `order` >= 0
                  ORDER BY `order`";
} else {
	$query = "SELECT id, title, comments, visibility
		  FROM course_units WHERE course_id = $cours_id AND visibility='v' AND `order` >= 0
                  ORDER BY `order`";
}
$sql = db_query($query);
$first = true;
$count_index = 1;
while ($cu = mysql_fetch_array($sql)) {
                // Visibility icon
                $vis = $cu['visibility'];
                $icon_vis = ($vis == 'v')? 'visible.png': 'invisible.png';
                $class1_vis = ($vis == 'i')? ' class="invisible"': '';
                $class_vis = ($vis == 'i')? 'invisible': '';
                $cunits_content .= "\n\n\n      <table ";
                if ($is_adminOfCourse) {
                    $cunits_content .= "class='tbl'";
                } else {
                    $cunits_content .= "class='tbl'";
                }
                $cunits_content .= " width='99%'>";
                if ($is_adminOfCourse) {
                $cunits_content .= "\n      <tr class='odd'>".
                                   "\n        <td width='3%' class='right'>&nbsp;<b>$count_index.</b>&nbsp;</td>" .
                                   "\n        <td><a class=\"$class_vis\" href='${urlServer}modules/units/?id=$cu[id]'>" . q($cu['title']) . "</a></td>";
                } else {
                $cunits_content .= "\n      <tr class='odd'>".
                                   "\n        <td width='3%' class='right'>&nbsp;<b>$count_index.</b>&nbsp;</td>".
                                   "\n        <td><a class=\"$class_vis\" href='${urlServer}modules/units/?id=$cu[id]'>" . q($cu['title']) . "</a></td>";
                }

                if ($is_adminOfCourse) { // display actions
                        $cunits_content .= "\n        <td width='16'>".
                                "<a href='../../modules/units/info.php?edit=$cu[id]'>" .
                                "<img src='../../template/classic/img/edit.png' title='$langEdit' /></a></td>" .
                                "\n        <td width='16'><a href='$_SERVER[PHP_SELF]?del=$cu[id]' " .
                                "onClick=\"return confirmation();\">" .
                                "<img src='../../template/classic/img/delete.png' " .
                                "title='$langDelete' /></a></td>" .
                                "\n        <td width='16'><a href='$_SERVER[PHP_SELF]?vis=$cu[id]'>" .
                                "<img src='../../template/classic/img/$icon_vis' " .
                                "title='$langVisibility' /></a></td>";
                        if ($cu['id'] != $last_id) {
                                $cunits_content .= "\n        <td width='16'><a href='$_SERVER[PHP_SELF]?down=$cu[id]'>" .
                                "<img src='../../template/classic/img/down.png' title='$langDown' /></a></td>";
                        } else {
                                $cunits_content .= "\n        <td width='16'>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
                        }
                        if (!$first) {
                                $cunits_content .= "\n        <td width='16'><a href='$_SERVER[PHP_SELF]?up=$cu[id]'><img src='../../template/classic/img/up.png' title='$langUp' /></a></td>";
                        } else {
                                $cunits_content .= "\n        <td width='16'>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
                        }
                }
                $cunits_content .= "\n      </tr>\n      <tr>\n        <td ";
                if ($is_adminOfCourse) {
                    $cunits_content .= "colspan='7' $class1_vis>";
                } else {
                    $cunits_content .= "colspan='2'>";
                }
                $cunits_content .= standard_text_escape($cu['comments']) . "\n    </td>\n  </tr>\n" .
                                   "\n  </table>\n";
                $first = false;
                $count_index++;
        }

switch ($type){
	case 'pre': { //pre
		$lessonType = $langpre;
		break;
	}
	case 'post': {//post
		$lessonType = $langpost;
		break;
	}
	case 'other': { //other
		$lessonType = $langother;
		break;
	}
}

$bar_content .= "\n            <p><b>".$langCode."</b>: ".$fake_code."</p>".
                "\n            <p><b>".$langTeachers."</b>:<br /> ".$professor."</p>".
                "\n            <p><b>".$langFaculty."</b>: ".$faculte."</p>".
                "\n            <p><b>".$langType."</b>: ".$lessonType."</p>";

$require_help = TRUE;
$helpTopic = 'course_home';

if ($is_adminOfCourse) {
	$sql = "SELECT COUNT(user_id) AS numUsers
			FROM cours_user
			WHERE cours_id = $cours_id";
	$res = db_query($sql, $mysqlMainDb);
	while($result = mysql_fetch_row($res)) {
		$numUsers = $result[0];
	}

	//set the lang var for lessons visibility status
	switch ($visible){
		case 0: { //closed
			$lessonStatus = $langPrivate;
			break;
		}

		case 1: {//open with registration
			$lessonStatus = $langPrivOpen;
			break;
		}

		case 2: { //open
			$lessonStatus = $langPublic;
			break;
		}
	}
	$bar_content .= "\n            <p><b>$langConfidentiality</b>: $lessonStatus</p>";
	$bar_content .= "\n            <p><b>$langUsers</b>: <a href='$urlAppend/modules/user/user.php'>$numUsers $langRegistered</a></p>";
}

$tool_content .= "
<div id='content_course'>

   <table width='99%'>
   <tr>
      <td valign='top'>$main_content</td>
      <td width='180' valign='top'>

        <table class='tbl_courseid' width='200'>
        <tr class='title1'>
          <td  class='title1'>$langIdentity</td>
        </tr>
        <tr>
          <td class='smaller'>$bar_content</td>
        </tr>
        </table>

        <br />

        <table class='smaller'>
        <tr>
          <td align='left'>$langContactProf: <a href='../../modules/contact/index.php'><img src='../../template/classic/img/email.png' alt='icon' title='$langEmail' /></a></td>
          </tr>
        </table>

        <br />\n";

if ($is_adminOfCourse or
    (isset($_SESSION['saved_statut']) and $_SESSION['saved_statut'] == 1)) {
        if (isset($_SESSION['saved_statut'])) {
                $button_message = $langStudentViewDisable;
        } else {
                $button_message = $langStudentViewEnable;
        }
        $tool_content .="
        <table class='smaller'>
        <tr>
          <td>
            <form action='{$urlServer}student_view.php' method='post'>$button_message
              <input class='transfer_btn' type='submit' name='submit' value='&raquo;' />
            </form>
          </td>
        </tr>
        </table> ";
        /*
        $tool_content .=
                "<tr><td colspan='3' style='text-align: right'>" .
                "<form action='{$urlServer}student_view.php' method='post'>" .
                "<input type='submit' name='submit' value='$button_message' />" .
                "</form></td></tr>\n";
        */
}

$tool_content .= "
      </td>
   </tr>
   <tr>
      <td colspan='3' valign='top'>
        <p>&nbsp;</p>

        <table width='99%' class='tbl'>
        <tr>
          <td>$cunits_content</td>
        </tr>
        </table>

      </td>
   </tr>
   </table>

</div>
";
draw($tool_content, 2, '', $head_content);
