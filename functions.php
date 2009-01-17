<?php

function gradebook_show_grade_date_selection() {
	global $wpdb;
	$el_id = $_POST['el_id'];
	$student_id = intval($_POST['student_id']);
	
	$grades = $wpdb->get_results( "SELECT DATE_FORMAT(`date`, '%Y-%m-%d') AS date FROM {$wpdb->gradebook_grades} WHERE `student_id` = {$student_id}" );

	$dates = array();
	foreach ( $grades AS $grade )
		if ( !in_array($grade->date, $dates) )
			$dates[] = $grade->date;

	$date_selection = "<select size='1' name='grade_date' id='grade_date'><option value=''>".__( 'All Grades', 'gradebook' )."</option>";
	foreach ( $dates AS $date )
		$date_selection .= "<option value='".$date."'>".mysql2date(get_option('date_format'), $date)."</option>";
	$date_selection .= "</select>";

	$date_selection = addslashes_gpc($date_selection);
	die( "function displayGradeDateSelection() {
		var studentId = ".$student_id.";
		dateTitle = '".__("Date", "gradebook")."';
		dateSelection = '".$date_selection."';
		if ( studentId != 0 ) {
			out = \"<td><label for='grade_date'>\" + dateTitle + \"</label></td>\";
			out += \"<td>\" + dateSelection + \"</td>\";
			document.getElementById('$el_id').innerHTML = out;

			note = '".__( '<strong>Note:</strong> Previously,in the "Add Items" section, add the Assignment generic Type (I.e:exam,exercices...).To grade and assignment, add the Assignment Description (I.e:Lessons 1,2 & 3","Cervantes bio"...).', 'gradebook' )."';
			document.getElementById('grade_note').innerHTML = note;
		}
	}
	displayGradeDateSelection();
	");
}

?>
