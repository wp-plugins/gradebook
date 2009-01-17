<?php
if ( isset($_POST['addStudent']) && !isset($_POST['deleteit']) ) {
	check_admin_referer('gradebook_add-student');
	$message = $gradebook->addStudent( $_POST['student_title'] );
	echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
} elseif ( isset($_GET['deactivate_student']) ) {
	$gradebook->deactivateStudent( $_GET['deactivate_student'] );
} elseif ( isset( $_GET['activate_student'] ) ) {
	$gradebook->activateStudent( $_GET['activate_student'] );
} elseif ( isset($_POST['deleteit']) && isset($_POST['delete']) ) {
	check_admin_referer('gradebook_delete-student');
	foreach ( $_POST['delete'] AS $student_id )
		$gradebook->delStudent( $student_id );
}
?>
<div class="wrap" style="margin-bottom: 1em;">
	<h2><?php _e( 'Gradebook', 'gradebook' ) ?></h2>
	
	<form id="students-filter" method="post" action="">
	<?php wp_nonce_field( 'gradebook_delete-student' ) ?>
	
	<div class="tablenav" style="margin-bottom: 0.1em;"><input type="submit" name="deleteit" value="<?php _e( 'Delete','gradebook' ) ?>" class="button-secondary" /></div>
	
	<table class="widefat" summary="" title="GradeBook">
		<thead>
		<tr>
                        <th scope="col" class="check-column"><input type="checkbox" onclick="Gradebook.checkAll(document.getElementById('students-filter'));" /></th>
			<th scope="col" class="num">ID</th>
			<th scope="col"><?php _e( 'Student', 'gradebook' ) ?></th>
			<th scope="col" class="num"><?php _e( 'Items', 'gradebook' ) ?></th>
			<th scope="col" class="num"><?php _e( 'Grades', 'gradebook' ) ?></th>
			<th scope="col"><?php _e( 'Status', 'gradebook' ) ?></th>
			<th scope="col"><?php _e( 'Action', 'gradebook' ) ?></th>
		</tr>
		<tbody id="the-list">
			<?php if ( $students = $gradebook->getStudents() ) : ?>
			<?php foreach ( $students AS $l_id => $student ) : ?>
			<?php $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $l_id ?>" name="delete[<?php echo $l_id ?>]" /></th>
				<td class="num"><?php echo $l_id ?></td>
				<td><a href="edit.php?page=gradebook/show-student.php&amp;id=<?php echo $l_id ?>"><?php echo $student['title'] ?></a></td>
				<td class="num"><?php echo $gradebook->getNumItems( $l_id ) ?></td>
				<td class="num"><?php echo $gradebook->getNumGrades( $l_id ) ?></td>
				<td><?php $gradebook->toggleStudentStatusText( $l_id ) ?></td>
				<td><?php $gradebook->toggleStudentStatusAction( $l_id ) ?></td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	</form>

	<!-- Add New Student -->
	<form action="" method="post" style="margin-top: 3em;">
		<?php wp_nonce_field( 'gradebook_add-student' ) ?>
		<h3><?php _e( 'Add Student', 'gradebook' ) ?></h3>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="student_title"><?php _e( 'Student', 'gradebook' ) ?></label></th><td><input type="text" name="student_title" id="student_title" value="" size="30" style="margin-bottom: 1em;" /></td>
		</tr>
		</table>
		<input type="hidden" name="student_id" value="" />
		<p class="submit"><input type="submit" name="addStudent" value="<?php _e( 'Add Student', 'gradebook' ) ?> &raquo;" class="button" /></p>
	</form>
</div>
