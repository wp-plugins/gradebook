<?php
if ( !current_user_can( 'manage_students' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
 	if ( isset($_POST['updateStudent']) && !isset($_POST['deleteit']) ) {
		check_admin_referer('gradebook_manage-student-options');
		
		$message = $gradebook->editStudent( $_POST['student_title'], $_POST['grade_calendar'], $_POST['type'], $_POST['student_id'] );
		echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
	}
	
	if ( isset( $_GET['edit'] ) ) {
		$student_id = $_GET['edit'];
		$student = $gradebook->getStudents( $student_id );
		$form_title = __( 'Student Preferences', 'gradebook' );
		$student_title = $student['title'];
		
		$student_preferences = $gradebook->getStudentPreferences( $student_id );
	}
	
	$grade_calendar = array( 1 => __('Show All', 'gradebook'), 2 => __('Only own grades', 'gradebook') );
	$student_types = array( 1 => __('Ball Game', 'gradebook'), 2 => __('Other', 'gradebook') );
		
	if ( 1 == $student_preferences->show_logo && !wp_mkdir_p( $gradebook->getImagePath() ) )
		echo "<div class='error'><p>".sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $gradebook->getImagePath() )."</p></div>";
	?>	
	<form action="" method="post">
		<?php wp_nonce_field( 'gradebook_manage-student-options' ) ?>
		
		<div class="wrap">
			<p class="gradebook_breadcrumb"><a href="edit.php?page=gradebook/manage-students.php"><?php _e( 'Gradebook', 'gradebook' ) ?></a> &raquo; <a href="edit.php?page=gradebook/show-student.php&amp;id=<?php echo $student_id ?>"><?php echo $student_title ?></a> &raquo; <?php echo $form_title ?></p>
			
			<h2><?php echo $form_title ?></h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="student_title"><?php _e( 'Name', 'gradebook' ) ?></label></th><td><input type="text" name="student_title" id="student_title" value="<?php echo $student_title ?>" size="30" /></td>
				</tr>
			
				<tr valign="top">
					<th scope="row"><label for="grade_calendar"><?php _e( 'Grades Plan', 'gradebook' ) ?></label></th>
					<td>
						<select size="1" name="grade_calendar" id="grade_calendar">
							<?php foreach ( $grade_calendar AS $id => $title ) : ?>
							<option value="<?php echo $id ?>"<?php if ( $id == $student_preferences->grade_calendar ) echo ' selected="selected"' ?>><?php echo $title ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			
				
			</table>
			<input type="hidden" name="student_id" value="<?php echo $student_id ?>" />
			<p class="submit"><input type="submit" name="updateStudent" value="<?php _e( 'Save Preferences', 'gradebook' ) ?> &raquo;" class="button" /></p>
		</div>
	</form>
<?php endif; ?>
