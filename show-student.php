<?php
if ( isset($_POST['updateStudent']) AND !isset($_POST['deleteit']) ) {
	if ( 'item' == $_POST['updateStudent'] ) {
		check_admin_referer('gradebook_manage-items');
		$home = isset( $_POST['home'] ) ? 1 : 0;
		if ( '' == $_POST['item_id'] ) {
			$message = $gradebook->addItem( $_POST['student_id'], $_POST['short_title'], $_POST['item'], $home );
		} else {
			$del_logo = isset( $_POST['del_logo'] ) ? true : false;
			$overwrite_image = isset( $_POST['overwrite_image'] ) ? true: false;
			$message = $gradebook->editItem( $_POST['item_id'], $_POST['short_title'], $_POST['item'], $home, $del_logo, $_POST['image_file'], $overwrite_image );
		}
	} elseif ( 'grade' == $_POST['updateStudent'] ) {
		check_admin_referer('gradebook_manage-grades');
		
		if ( '' == $_POST['grade_id'] ) {
			$num_grades = count($_POST['grade']);
			foreach ( $_POST['grade'] AS $grade_no ) {
				if ( $_POST['away_item'][$grade_no] != $_POST['home_item'][$grade_no] ) {
					$date = $_POST['grade_year'].'-'.str_pad($_POST['grade_month'], 2, 0, STR_PAD_LEFT).'-'.str_pad($_POST['grade_day'], 2, 0, STR_PAD_LEFT).' '.str_pad($_POST['begin_hour'][$grade_no], 2, 0, STR_PAD_LEFT).':'.str_pad($_POST['begin_minutes'][$grade_no], 2, 0, STR_PAD_LEFT).':00';
					
					$gradebook->addGrade( $date, $_POST['home_item'][$grade_no], $_POST['away_item'][$grade_no], $_POST['location'][$grade_no], $_POST['student_id'] );
				} else {
					$num_grades -= 1;
				}
			}
			$message = sprintf(__ngettext('%d Grade added', '%d Grades', $num_grades, 'gradebook'), $num_grades);
		} else {
			$date = $_POST['grade_year'].'-'.str_pad($_POST['grade_month'], 2, 0, STR_PAD_LEFT).'-'.str_pad($_POST['grade_day'], 2, 0, STR_PAD_LEFT).' '.str_pad($_POST['begin_hour'][1], 2, 0, STR_PAD_LEFT).':'.str_pad($_POST['begin_minutes'][1], 2, 0, STR_PAD_LEFT).':00';
			
			$message = $gradebook->editGrade( $date, $_POST['home_item'][1], $_POST['away_item'][1], $_POST['location'][1], $_POST['student_id'], $_POST['grade_id'] );
		}
	} elseif ( 'results' == $_POST['updateStudent'] ) {
		check_admin_referer('gradebook_grades');
		
		$message = $gradebook->updateResults( $_POST['grades'], $_POST['home_apparatus_points'], $_POST['away_apparatus_points'], $_POST['home_points'], $_POST['away_points'], $_POST['home_item'], $_POST['away_item'] );
	}
		
	echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
} elseif ( isset($_POST['deleteit']) AND isset($_POST['delete']) ) {
	if ( (isset( $_POST['item']) && 'items' == $_POST['item'] )  ) {
		check_admin_referer('gradebook_table');
		foreach ( $_POST['delete'] AS $item_id )
			$gradebook->delItem( $item_id);
	} elseif ( (isset( $_POST['item']) && 'grades' == $_POST['item'] ) ) {
		check_admin_referer('gradebook_grades');
		foreach ( $_POST['delete'] AS $grade_id )
			$gradebook->delGrade( $grade_id );
	}
}

$student_id = $_GET['id'];
$curr_student = $gradebook->getStudents( $student_id );

$student_title = $curr_student['title'];
$student_preferences = $gradebook->getStudentPreferences( $student_id );
$item_list = $gradebook->getItems( 'student_id = "'.$student_id.'"', 'ARRAY' );
?>
<div class="wrap">
	<p class="gradebook_breadcrumb"><a href="edit.php?page=gradebook/manage-students.php"><?php _e( 'Gradebook', 'gradebook' ) ?></a> &raquo; <?php echo $student_title ?></p>
	
	<h2 style="clear: none;"><?php echo $student_title ?></h2>
	
	<p>
		<a href="edit.php?page=gradebook/settings.php&amp;edit=<?php echo $student_id ?>"><?php _e( 'Preferences', 'gradebook' ) ?></a> &middot;
		<a href="edit.php?page=gradebook/item.php&amp;student_id=<?php echo $student_id ?>"><?php _e( 'Add Item (Assignment Type or Assignment Description)','gradebook' ) ?></a> &middot;
		<a href="edit.php?page=gradebook/grade.php&amp;student_id=<?php echo $student_id ?>"><?php _e( 'Add Grade','gradebook' ) ?></a>
	</p>
	
	
	<h3><?php _e( 'Grades Plan','gradebook' ) ?></h3>	
	<form id="competitions-filter" action="" method="post">
		<?php wp_nonce_field( 'gradebook_grades' ) ?>
		
		<div class="tablenav" style="margin-bottom: 0.1em;"><input type="submit" name="deleteit" value="<?php _e( 'Delete','gradebook' ) ?>" class="button-secondary" /></div>
		
		<table class="widefat" summary="" title="<?php _e( 'Grades Plan','gradebook' ) ?>" style="margin-bottom: 2em;">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Gradebook.checkAll(document.getElementById('competitions-filter'));" /></th>
			<th><?php _e( 'Date','gradebook' ) ?></th>
			<th><?php _e( 'Assignment','gradebook' ) ?></th>
			<th><?php _e( 'Observations','gradebook' ) ?></th>
			<th><?php _e( 'Grade','gradebook' ) ?></th>
			<th><?php _e( 'Final grade value (i.e: 3x->3:1, x/3->1:3)', 'gradebook' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php if ( $grades = $gradebook->getGrades( 'student_id = "'.$student_id.'"' ) ) : ?>
			<?php foreach ( $grades AS $grade ) :
				$class = ( 'alternate' == $class ) ? '' : 'alternate';
			?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column">
					<input type="hidden" name="grades[<?php echo $grade->id ?>]" value="<?php echo $grade->id ?>" />
					<input type="hidden" name="home_item[<?php echo $grade->id ?>]" value="<?php echo $grade->home_item ?>" />
					<input type="hidden" name="away_item[<?php echo $grade->id ?>]" value="<?php echo $grade->away_item ?>" />
					<input type="checkbox" value="<?php echo $grade->id ?>" name="delete[<?php echo $grade->id ?>]" /></th>
				<td><?php echo mysql2date(get_option('date_format'), $grade->date) ?></td>
				<td><a href="edit.php?page=gradebook/grade.php&amp;edit=<?php echo $grade->id ?>">
				<?php echo $item_list[$grade->home_item]['title'] ?> - <?php echo $item_list[$grade->away_item]['title'] ?>
				</td>
				<td><?php echo ( '' == $grade->location ) ? 'N/A' : $grade->location ?></td>
				<td><?php echo ( '0:0' == $grade->hour.":".$grade->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $grade->date) ?></td>
				<?php if ( $gradebook->isGymnasticsStudent( $student_id ) ) : ?>
				<td><input class="points" type="text" size="2" id="home_apparatus_points[<?php echo $grade->id ?>]" name="home_apparatus_points[<?php echo $grade->id ?>]" value="<?php echo $grade->home_apparatus_points ?>" /> : <input class="points" type="text" size="2" id="away_apparatus_points[<?php echo $grade->id ?>]" name="away_apparatus_points[<?php echo $grade->id ?>]" value="<?php echo $grade->away_apparatus_points ?>" /></td>
				<?php endif; ?>
				<td><input class="points" type="text" size="2" id="home_points[<?php echo $grade->id ?>]" name="home_points[<?php echo $grade->id ?>]" value="<?php echo $grade->home_points ?>" /> : <input class="points" type="text" size="2" id="away_points[<?php echo $grade->id ?>]" name="away_points[<?php echo $grade->id ?>]" value="<?php echo $grade->away_points ?>" /></td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
		
		<?php if ( count($grades) > 0 ) : ?>
			<input type="hidden" name="updateStudent" value="results" />
			<p class="submit"><input type="submit" name="updateResults" value="<?php _e( 'Update Results','gradebook' ) ?> &raquo;" class="button" /></p>
		<?php endif; ?>
		
		<input type="hidden" name="item" value="grades" />
	</form>
		<h3><?php _e( 'Table', 'gradebook' ) ?></h3>
	
	<form id="items-filter" action="" method="post">
		<?php wp_nonce_field( 'gradebook_table' ) ?>
			
		<div class="tablenav" style="margin-bottom: 0.1em;"><input type="submit" name="deleteit" value="<?php _e( 'Delete','gradebook' ) ?>" class="button-secondary" /></div>
		
		<table class="widefat" summary="" title="<?php _e( 'Items List', 'gradebook' ) ?>">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Gradebook.checkAll(document.getElementById('items-filter'));" /></th>
			<th class="num">#</th>
			
			<th><?php _e( 'ITEMS (Assignment Types and Assignment Descriptions)', 'gradebook' ) ?></th>
					
		</tr>
		</thead>
		<tbody id="the-list">
		<?php $items = $gradebook->rankItems( $student_id ) ?>
		<?php if ( count($items) > 0 ) : $rank = 0; ?>
		<?php foreach( $items AS $item ) : $rank++; $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class ?>">
			<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $item['id'] ?>" name="delete[<?php echo $item['id'] ?>]" /></th>
			<td class="num"><?php echo $rank ?></td>
			
			<td>
				<input type="hidden" name="item[<?php echo $item['id'] ?>]" value="<?php echo $item['title'] ?>" />
				<a href="edit.php?page=gradebook/item.php&amp;edit=<?php echo $item['id']; ?>"><?php echo $item['title'] ?></a>
			</td>
						
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
		<input type="hidden" name="item" value="items" />
	</form>
	</div>
</div>