<?php
if ( !current_user_can( 'manage_students' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
else :

	if ( isset( $_GET['edit'] ) ) {
		if ( $item = $gradebook->getItem( $_GET['edit'] ) ) {
			$item_title = $item->title;
			$short_title = $item->short_title;
			$home = ( 1 == $item->home ) ? ' checked="checked"' : '';
			$item_id = $item->id;
			$logo = $item->logo;
			$student_id = $item->student_id;
		}
		$student = $gradebook->getStudents( $student_id );
		$student_title = $student['title'];
		
		$form_title = __( 'Edit Item (Assignment Type or Assignment Description)', 'gradebook' );
		$student_title = $student['title'];
	} else {
		$form_title = __( 'Add Item (Assignment Type or Assignment Description)', 'gradebook' ); $item_title = ''; $short_title = ''; $home = ''; $item_id = ''; $student_id = $_GET['student_id']; $logo = '';
		
		$student = $gradebook->getStudents( $student_id );
		$student_title = $student['title'];
	}
	$student_preferences = $gradebook->getStudentPreferences($student_id);
	
	if ( 1 == $student_preferences->show_logo && !wp_mkdir_p( $gradebook->getImagePath() ) )
		echo "<div class='error'><p>".sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $gradebook->getImagePath() )."</p></div>";
	?>

	<div class="wrap">
	<p class="gradebook_breadcrumb"><a href="edit.php?page=gradebook/manage-students.php"><?php _e( 'Gradebook', 'gradebook' ) ?></a> &raquo; <a href="edit.php?page=gradebook/show-student.php&amp;id=<?php echo $student_id ?>"><?php echo $student_title ?></a> &raquo; <?php echo $form_title ?></p>
		<h2><?php echo $form_title ?></h2>
		
		<form action="edit.php?page=gradebook/show-student.php&amp;id=<?php echo $student_id ?>" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'gradebook_manage-items' ) ?>
			
			<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="item"><?php _e( 'Item', 'gradebook' ) ?></label></th><td><input type="text" id="item" name="item" value="<?php echo $item_title ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="short_title"><?php _e( 'Short Name', 'gradebook' ) ?></label></th><td><input type="text" id="short_title" name="short_title" value="<?php echo $short_title ?>" /><br /><?php _e( '', 'gradebook' ) ?></td>
			</tr>
			<?php if ( 1 == $student_preferences->show_logo ) : ?>
			<tr valing="top">
				<th scope="row"><label for="logo"><?php _e( 'Logo', 'gradebook' ) ?></label></th>
				<td>
					<?php if ( '' != $logo ) : ?>
					<img src="<?php echo $gradebook->getImageUrl($logo)?>" class="alignright" />
					<?php endif; ?>
					<input type="file" name="logo" id="logo" size="35"/><p><?php _e( 'Supported file types', 'gradebook' ) ?>: <?php echo implode( ',',$gradebook->getSupportedImageTypes() ); ?></p>
					<?php if ( '' != $logo ) : ?>
					<p style="float: left;"><label for="overwrite_image"><?php _e( 'Overwrite existing image', 'gradebook' ) ?></label><input type="checkbox" id="overwrite_image" name="overwrite_image" value="1" style="margin-left: 1em;" /></p>
					<input type="hidden" name="image_file" value="<?php echo $logo ?>" />
					<p style="float: right;"><label for="del_logo"><?php _e( 'Delete Logo', 'gradebook' ) ?></label><input type="checkbox" id="del_logo" name="del_logo" value="1" style="margin-left: 1em;" /></p>
					<?php endif; ?>
				</td>
			</tr>
			<?php endif; ?>
			<tr valign="top">
				<th scope="row"><label for="home"><?php _e( 'Is an Assignment Type?', 'gradebook' ) ?></label></th><td><input type="checkbox" name="home" id="home"<?php echo $home ?>/></td>
			</tr>
			</table>
						
			<input type="hidden" name="item_id" value="<?php echo $item_id ?>" />	
			<input type="hidden" name="student_id" value="<?php echo $student_id ?>" />
			<input type="hidden" name="updateStudent" value="item" />
			
			<p class="submit"><input type="submit" value="<?php echo $form_title ?> &raquo;" class="button" /></p>
		</form>
	</div>
<?php endif; ?>