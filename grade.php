<?php
if ( !current_user_can( 'manage_students' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	if ( isset( $_GET['edit'] ) ) {
		$form_title = __( 'Edit Grade', 'gradebook' );

		$grade = $gradebook->getGrade( $_GET['edit'] );

		if ( $grade ) {
			$student_id = $grade->student_id;
			$grade_day = $grade->day;
			$grade_month = $grade->month;
			$grade_year = $grade->year;
			$begin_hour = $grade->hour;
			$begin_minutes = $grade->minutes;
			$location = $grade->location;
			$home_item = $grade->home_item;
			$away_item = $grade->away_item;
			$grade_id = $grade->id;
	
			$student = $gradebook->getStudents( $student_id );
			$student_title = $student['title'];
			
			$max_grades = 1;
		}
	} else {
		$form_title = __( 'Add Grade', 'gradebook' );

		$student_id = $_GET['student_id'];
		$student = $gradebook->getStudents( $student_id );
		$student_title = $student['title'];
		$grade_day = ''; $grade_month = ''; $grade_year = date("Y"); $home_item = ''; $away_item = '';
		$begin_hour = ''; $begin_minutes = ''; $location = ''; $grade_id = ''; $max_grades = 10;
	}
	?>
	
	<div class="wrap">
	<p class="gradebook_breadcrumb"><a href="edit.php?page=gradebook/manage-students.php"><?php _e( 'Gradebook', 'gradebook' ) ?></a> &raquo; <a href="edit.php?page=gradebook/show-student.php&amp;id=<?php echo $student_id ?>"><?php echo $student_title ?></a> &raquo; <?php echo $form_title ?></p>
		<h2><?php echo $form_title ?></h2>
		
		<form class="gradebook" action="edit.php?page=gradebook/show-student.php&amp;id=<?php echo $student_id?>" method="post">
			<?php wp_nonce_field( 'gradebook_manage-grades' ) ?>
			
			<label for="date" class="date"><?php _e('Date', 'gradebook') ?>:</label>
			<select size="1" name="grade_day" class="date">
			<?php for ( $day = 1; $day <= 31; $day++ ) : ?>
				<option value="<?php echo $day ?>"<?php if ( $day == $grade_day ) echo ' selected="selected"' ?>><?php echo $day ?></option>
			<?php endfor; ?>
			</select>
			<select size="1" name="grade_month" class="date">
			<?php foreach ( $gradebook->months AS $key => $month ) : ?>
				<option value="<?php echo $key ?>"<?php if ( $key == $grade_month ) echo ' selected="selected"' ?>><?php echo $month ?></option>
			<?php endforeach; ?>
			</select>
			<select size="1" name="grade_year" class="date">
			<?php for ( $year = date("Y"); $year <= date("Y")+40; $year++ ) : ?>
				<option value="<?php echo $year ?>"<?php if ( $year == $grade_year ) echo ' selected="selected"' ?>><?php echo $year ?></option>
			<?php endfor; ?>
			</select>
			<br />
			
			<p class="grade_info"><?php _e( 'Note: Previously,in the "Add Items" section, add the Assignment generic Type (I.e:exam,exercices...).To grade and assignment, add the Assignment Description (I.e:Lessons 1,2 & 3","Cervantes bio"...).', 'gradebook' ) ?></p>
			<?php $items = $gradebook->getItems( "student_id = '".$student_id."'" ); ?>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e( 'Assignment Type', 'gradebook' ) ?></th>
						<th scope="col"><?php _e( 'Assignment Description', 'gradebook' ) ?></th>
						<th scope="col"><?php _e( 'Observations','gradebook' ) ?></th>
						<th scope="col"><?php _e( 'Grade','gradebook' ) ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
				<?php for ( $i = 1; $i <= $max_grades; $i++ ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
				<tr class="<?php echo $class ?>">
					<td>
						<select size="1" name="home_item[<?php echo $i ?>]" id="home_item[<?php echo $i ?>]">
						<?php foreach ( $items AS $item ) : ?>
							<option value="<?php echo $item->id ?>"<?php if ( $item->id == $home_item ) echo ' selected="selected"' ?>><?php echo $item->title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td>
						<select size="1" id="away_item[<?php echo $i ?>]" name="away_item[<?php echo $i ?>]">
						<?php foreach ( $items AS $item ) : ?>
							<option value="<?php echo $item->id ?>"<?php if ( $item->id == $away_item ) echo ' selected="selected"' ?>><?php echo $item->title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td><input type="text" name="location[<?php echo $i ?>]" id="location[<?php echo $i ?>]" size="20" value="<?php echo $location ?>" size="30" /></td>
					<td>
						<select size="1" name="begin_hour[<?php echo $i ?>]">
						<?php for ( $hour = 1; $hour <= 10; $hour++ ) : ?>
							<option value="<?php echo $hour ?>"<?php if ( $hour == $begin_hour ) echo ' selected="selected"' ?>><?php echo str_pad($hour, 1, 0, STR_PAD_LEFT) ?></option>
						<?php endfor; ?>
						</select>
						<select size="1" name="begin_minutes[<?php echo $i ?>]">
						<?php for ( $minute = 0; $minute <= 10; $minute++ ) : ?>
							<?php if ( 0 == $minute % 1 && 10 != $minute ) : ?>
							<option value="<?php echo $minute ?>"<?php if ( $minute == $begin_minutes ) echo ' selected="selected"' ?>><?php echo str_pad($minute, 1, 0, STR_PAD_LEFT) ?></option>
							<?php endif; ?>
						<?php endfor; ?>
						</select>
					</td>
				</tr>
				<input type="hidden" name="grade[<?php echo $i ?>]" value="<?php echo $i ?>" />
				<?php endfor; ?>
				</tbody>
			</table>
			
			<input type="hidden" name="grade_id" value="<?php echo $grade_id ?>" />
			<input type="hidden" name="student_id" value="<?php echo $student_id ?>" />
			<input type="hidden" name="updateStudent" value="grade" />
			
			<p class="submit"><input type="submit" value="<?php echo $form_title ?> &raquo;" class="button" /></p>
		</form>
	</div>
<?php endif; ?>