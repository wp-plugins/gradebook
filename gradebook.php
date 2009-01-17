<?php

class WP_GradeBook
{
	/**
	 * supported image types
	 *
	 * @var array
	 */
	var $supported_image_types = array( "jpg", "jpeg", "png", "gif" );
	
	
	/**
	 * Array of months
	 *
	 * @param array
	 */
	var $months = array();

	
	/**
	 * Preferences of Student
	 *
	 * @param array
	 */
	var $preferences = array();
	
	
	/**
	 * error handling
	 *
	 * @param boolean
	 */
	var $error = false;
	
	
	/**
	 * error message
	 *
	 * @param string
	 */
	var $message = '';
	
	
	/**
	 * Initializes plugin
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
	 	global $wpdb;
	 	
		$wpdb->gradebook = $wpdb->prefix . 'gradebook_students';
		$wpdb->gradebook_items = $wpdb->prefix . 'gradebook_items';
		$wpdb->gradebook_grades = $wpdb->prefix . 'gradebook_grades';		

		$this->getMonths();
		return;
	}
	function WP_GradeBook()
	{
		$this->__construct();
	}
	
	
	/**
	 * get months
	 *
	 * @param none
	 * @return void
	 */
	function getMonths()
	{
		$locale = get_locale();
		setlocale(LC_ALL, $locale);
		for ( $month = 1; $month <= 12; $month++ ) 
			$this->months[$month] = htmlentities( strftime( "%B", mktime( 0,0,0, $month, date("m"), date("Y") ) ) );
	}
	
	
	/**
	 * return error message
	 *
	 * @param none
	 */
	function getErrorMessage()
	{
		if ($this->error)
			return $this->message;
	}
	
	
	/**
	 * print formatted error message
	 *
	 * @param none
	 */
	function printErrorMessage()
	{
		echo "\n<div class='error'><p>".$this->getErrorMessage()."</p></div>";
	}
	
	
	/**
	 * gets supported file types
	 *
	 * @param none
	 * @return array
	 */
	function getSupportedImageTypes()
	{
		return $this->supported_image_types;
	}
	
	
	/**
	 * checks if image type is supported
	 *
	 * @param string $filename image file
	 * @return boolean
	 */
	function imageTypeIsSupported( $filename )
	{
		if ( in_array($this->getImageType($filename), $this->supported_image_types) )
			return true;
		else
			return false;
	}
	
	
	/**
	 * gets image type of supplied image
	 *
	 * @param string $filename image file
	 * @return string
	 */
	function getImageType( $filename )
	{
		$file_info = pathinfo($filename);
		return strtolower($file_info['extension']);
	}
	
	
	/**
	 * returns image directory
	 *
	 * @param string|false $file
	 * @return string
	 */
	function getImagePath( $file = false )
	{
		if ( $file )
			return WP_CONTENT_DIR.'/gradebook/'.$file;
		else
			return WP_CONTENT_DIR.'/gradebook';
	}
	
	
	/**
	 * returns url of image directory
	 *
	 * @param string|false $file image file
	 * @return string
	 */
	function getImageUrl( $file = false )
	{
		if ( $file )
			return WP_CONTENT_URL.'/gradebook/'.$file;
		else
			return WP_CONTENT_URL.'/gradebook';
	}
	
	
	/**
	 * get students from database
	 *
	 * @param int $student_id (default: false)
	 * @param string $search
	 * @return array
	 */
	function getStudents( $student_id = false, $search = '' )
	{
		global $wpdb;
		
		$students = array();
		if ( $student_id ) {
			$students_sql = $wpdb->get_results( "SELECT title, id FROM {$wpdb->gradebook} WHERE id = '".$student_id."' ORDER BY id ASC" );
			
			$students['title'] = $students_sql[0]->title;
			$this->preferences = $this->getStudentPreferences( $student_id );
		} else {
			if ( $students_sql = $wpdb->get_results( "SELECT title, id FROM {$wpdb->gradebook} $search ORDER BY id ASC" ) ) {
				foreach( $students_sql AS $student ) {
					$students[$student->id]['title'] = $student->title;
				}
			}
		}
			
		return $students;
	}
	
	
	/**
	 * get student settings
	 * 
	 * @param int $student_id
	 * @return array
	 */
	function getStudentPreferences( $student_id )
	{
		global $wpdb;
		
		$preferences = $wpdb->get_results( "SELECT `forwin`, `fordraw`, `forloss`, `grade_calendar`, `type` FROM {$wpdb->gradebook} WHERE id = '".$student_id."'" );
		
		$preferences[0]->colors = maybe_unserialize($preferences[0]->colors);
		return $preferences[0];
	}
	
	
	/**
	 * gets student name
	 *
	 * @param int $student_id
	 * @return string
	 */
	function getStudentTitle( $student_id )
	{
		global $wpdb;
		$student = $wpdb->get_results( "SELECT `title` FROM {$wpdb->gradebook} WHERE id = '".$student_id."'" );
		return ( $student[0]->title );
	}
	
	
	/**
	 * get all active students
	 *
	 * @param none
	 * @return array
	 */
	function getActiveStudents()
	{
		return ( $this->getStudents( false, 'WHERE active = 1' ) );
	}
	

	/**
	 * checks if student is active
	 *
	 * @param int $student_id
	 * @return boolean
	 */
	function studentIsActive( $student_id )
	{
		global $wpdb;
		$student = $wpdb->get_results( "SELECT active FROM {$wpdb->gradebook} WHERE id = '".$student_id."'" );
		if ( 1 == $student[0]->active )
			return true;
		
		return false;
	}
	
	
	/**
	 * activates given student depending on status
	 *
	 * @param int $student_id
	 * @return boolean
	 */
	function activateStudent( $student_id )
	{
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->gradebook} SET active = '1' WHERE id = '".$student_id."'" );
		return true;
	}
	
	
	/**
	 * deactivate student
	 *
	 * @param int $student_id
	 * @return boolean
	 */
	function deactivateStudent( $student_id )
	{
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->gradebook} SET active = '0' WHERE id = '".$student_id."'" );	
		return true;
	}
	
	
	/**
	 * toggle student status text
	 *
	 * @param int $student_id
	 * @return string
	 */
	function toggleStudentStatusText( $student_id )
	{
		if ( $this->studentIsActive( $student_id ) )
			_e( 'Active', 'gradebook');
		else
			_e( 'Inactive', 'gradebook');
	}
	
	
	/**
	 * toogle student status action link
	 *
	 * @param int $student_id
	 * @return string
	 */
	function toggleStudentStatusAction( $student_id )
	{
		if ( $this->studentIsActive( $student_id ) )
			echo '<a href="edit.php?page=gradebook/manage-students.php&amp;deactivate_student='.$student_id.'">'.__( 'Deactivate', 'gradebook' ).'</a>';
		else
			echo '<a href="edit.php?page=gradebook/manage-students.php&amp;activate_student='.$student_id.'">'.__( 'Activate', 'gradebook' ).'</a>';
	}
	
	
	/**
	 * get items from database
	 *
	 * @param string $search search string for WHERE clause.
	 * @param string $output OBJECT | ARRAY
	 * @return array database results
	 */
	function getItems( $search, $output = 'OBJECT' )
	{
		global $wpdb;
		
		$items_sql = $wpdb->get_results( "SELECT `title`, `short_title`, `logo`, `home`, `student_id`, `id` FROM {$wpdb->gradebook_items} WHERE $search ORDER BY id ASC" );
		
		if ( 'ARRAY' == $output ) {
			$items = array();
			foreach ( $items_sql AS $item ) {
				$items[$item->id]['title'] = $item->title;
				$items[$item->id]['short_title'] = $item->short_title;
				$items[$item->id]['logo'] = $items->logo;
				$items[$item->id]['home'] = $item->home;
			}
			
			return $items;
		}
		return $items_sql;
	}
	
	
	/**
	 * get single item
	 *
	 * @param int $item_id
	 * @return object
	 */
	function getItem( $item_id )
	{
		$items = $this->getItems( "`id` = {$item_id}" );
		return $items[0];
	}
	
	
	/**
	 * gets number of items for specific student
	 *
	 * @param int $student_id
	 * @return int
	 */
	function getNumItems( $student_id )
	{
		global $wpdb;
	
		$num_items = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_items} WHERE `student_id` = '".$student_id."'" );
		return $num_items;
	}
	
	
	/**
	 * gets number of grades
	 *
	 * @param string $search
	 * @return int
	 */
	function getNumGrades( $student_id )
	{
		global $wpdb;
	
		$num_grades = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_grades} WHERE `student_id` = '".$student_id."'" );
		return $num_grades;
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $item_id
	 * @return int
	 */
	function getNumWonGrades( $item_id )
	{
		global $wpdb;
		$num_win = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_grades} WHERE `winner_id` = '".$item_id."'" );
		return $num_win;
	}
	
	
	/**
	 *not applicable
	 *
	 * @param int $item_id
	 * @return int
	 */
	function getNumDrawGrades( $item_id )
	{
		global $wpdb;
		$num_draw = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_grades} WHERE `winner_id` = -1 AND `loser_id` = -1 AND (`home_item` = '".$item_id."' OR `away_item` = '".$item_id."')" );
		return $num_draw;
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $item_id
	 * @return int
	 */
	function getNumLostGrades( $item_id )
	{
		global $wpdb;
		$num_lost = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_grades} WHERE `loser_id` = '".$item_id."'" );
		return $num_lost;
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $item_id
	 * @param int $student_id
	 * @param string $option
	 * @return int
	 */
	function calculatePoints( $item_id, $student_id, $option )
	{
		global $wpdb;
		
		$num_win = $this->getNumWonGrades( $item_id );
		$num_draw = $this->getNumDrawGrades( $item_id );
		$num_lost = $this->getNumLostGrades( $item_id );
		
		$points['plus'] = 0; $points['minus'] = 0;
		$points['plus'] = $num_win * $this->preferences->forwin + $num_draw * $this->preferences->fordraw + $num_lost * $student_settings->forloss;
		$points['minus'] = $num_draw * $this->preferences->fordraw + $num_lost * $this->preferences->forwin;
		return $points[$option];
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $item_id
	 * @param string $option
	 * @return int
	 */
	function calculateApparatusPoints( $item_id, $option )
	{
		global $wpdb;
		
		$apparatus_home = $wpdb->get_results( "SELECT `home_apparatus_points`, `away_apparatus_points` FROM {$wpdb->gradebook_grades} WHERE `home_item` = '".$item_id."'" );
		$apparatus_away = $wpdb->get_results( "SELECT `home_apparatus_points`, `away_apparatus_points` FROM {$wpdb->gradebook_grades} WHERE `away_item` = '".$item_id."'" );
			
		$apparatus_points['plus'] = 0;
		$apparatus_points['minus'] = 0;
		if ( count($apparatus_home) > 0 )
		foreach ( $apparatus_home AS $home_apparatus ) {
			$apparatus_points['plus'] += $home_apparatus->home_apparatus_points;
			$apparatus_points['minus'] += $home_apparatus->away_apparatus_points;
		}
		
		if ( count($apparatus_away) > 0 )
		foreach ( $apparatus_away AS $away_apparatus ) {
			$apparatus_points['plus'] += $away_apparatus->away_apparatus_points;
			$apparatus_points['minus'] += $away_apparatus->home_apparatus_points;
		}
		
		return $apparatus_points[$option];
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $item_id
	 * @param string $option
	 * @return int
	 */
	function calculateGoals( $item_id, $option )
	{
		global $wpdb;
		
		$goals_home = $wpdb->get_results( "SELECT `home_points`, `away_points` FROM {$wpdb->gradebook_grades} WHERE `home_item` = '".$item_id."'" );
		$goals_away = $wpdb->get_results( "SELECT `home_points`, `away_points` FROM {$wpdb->gradebook_grades} WHERE `away_item` = '".$item_id."'" );
			
		$goals['plus'] = 0;
		$goals['minus'] = 0;
		if ( count($goals_home) > 0 ) {
			foreach ( $goals_home AS $home_goals ) {
				$goals['plus'] += $home_goals->home_points;
				$goals['minus'] += $home_goals->away_points;
			}
		}
		
		if ( count($goals_away) > 0 ) {
			foreach ( $goals_away AS $away_goals ) {
				$goals['plus'] += $away_goals->away_points;
				$goals['minus'] += $away_goals->home_points;
			}
		}
		
		return $goals[$option];
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $plus
	 * @param int $minus
	 * @return int
	 */
	function calculateDiff( $plus, $minus )
	{
		$diff = $plus - $minus;
		if ( $diff >= 0 )
			$diff = '+'.$diff;
		
		return $diff;
	}
	
	
	/**
	 * get number of grades for item
	 *
	 * @param int $item_id
	 * @return int
	 */
	function getNumDoneGrades( $item_id )
	{
		global $wpdb;
		
		$num_grades = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_grades} WHERE (`home_item` = '".$item_id."' OR `away_item` = '".$item_id."') AND `home_points` IS NOT NULL AND `away_points` IS NOT NULL" );
		return $num_grades;
	}
	
	
	/**
	 * not applicable
	 *
	 * @param none
	 * @return boolean
	 */
	function isGymnasticsStudent( $student_id )
	{
		if ( 1 == $this->preferences->type )
			return true;
		else
			return false;
	}
	
	
	/**
	 * rank items
	 *
	 * @param array $items
	 * @return array $items ordered
	 */
	function rankItems( $student_id )
	{
		global $wpdb;

		$items = array();
		foreach ( $this->getItems( "student_id = '".$student_id."'" ) AS $item ) {
			$p['plus'] = $this->calculatePoints( $item->id, $student_id, 'plus' );
			$p['minus'] = $this->calculatePoints( $item->id, $student_id, 'minus' );
			
			$ap['plus'] = $this->calculateApparatusPoints( $item->id, 'plus' );
			$ap['minus'] = $this->calculateApparatusPoints( $item->id, 'minus' );
			
			$grade_points['plus'] = $this->calculateGoals( $item->id, 'plus' );
			$grade_points['minus'] = $this->calculateGoals( $item->id, 'minus' );
			
			if ( $this->isGymnasticsStudent( $student_id ) )
				$d = $this->calculateDiff( $ap['plus'], $ap['minus'] );
			else
				$d = $this->calculateDiff( $grade_points['plus'], $grade_points['minus'] );
						
			$items[] = array('id' => $item->id, 'home' => $item->home, 'title' => $item->title, 'short_title' => $item->short_title, 'logo' => $item->logo, 'points' => array('plus' => $p['plus'], 'minus' => $p['minus']), 'apparatus_points' => array('plus' => $ap['plus'], 'minus' => $ap['minus']), 'goals' => array('plus' => $grade_points['plus'], 'minus' => $grade_points['minus']), 'diff' => $d );
		}
		
		foreach ( $items AS $key => $row ) {
			$points[$key] = $row['points']['plus'];
			$apparatus_points[$key] = $row['apparatus_points']['plus'];
			$diff[$key] = $row['diff'];
		}
		
		if ( count($items) > 0 ) {
			if ( $this->isGymnasticsStudent($student_id) )
				array_multisort($points, SORT_DESC, $apparatus_points, SORT_DESC, $items);
			else
				array_multisort($points, SORT_DESC, $diff, SORT_DESC, $items);
		}
		
		return $items;
	}
	
	
	/**
	 * gets grades from database
	 * 
	 * @param string $search
	 * @return array
	 */
	function getGrades( $search, $output = 'OBJECT' )
	{
	 	global $wpdb;
		
		$sql = "SELECT `home_item`, `away_item`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `location`, `student_id`, `home_apparatus_points`, `away_apparatus_points`, `home_points`, `away_points`, `winner_id`, `id` FROM {$wpdb->gradebook_grades} WHERE $search ORDER BY `date` ASC";
		return $wpdb->get_results( $sql, $output );
	}
	
	
	/**
	 * get single grade
	 *
	 * @param int $grade_id
	 * @return object
	 */
	function getGrade( $grade_id )
	{
		$grades = $this->getGrades( "`id` = {$grade_id}" );
		return $grades[0];
	}
	
	
	/**
	 * add new Student
	 *
	 * @param string $title
	 * @return string
	 */
	function addStudent( $title )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "INSERT INTO {$wpdb->gradebook} (title) VALUES ('%s')", $title ) );
		return __('Student added', 'gradebook');
	}


	/**
	 * edit Student
	 *
	 * @param string $title
	 * @param int $forwin
	 * @param int $fordraw
	 * @param int $forloss
	 * @param int $grade_calendar
	 * @param int $type
	 * @param int $show_logo
	 * @param int $student_id
	 * @return string
	 */
	function editStudent( $title, $grade_calendar, $type, $student_id )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->gradebook} SET `title` = '%s', `grade_calendar` = '%d', `type` = '%d' = '%d' WHERE `id` = '%d'", $title, $grade_calendar, $type, $student_id ) );
		return __('Settings saved', 'gradebook');
	}


	/**
	 * delete Student 
	 *
	 * @param int $student_id
	 * @return void
	 */
	function delStudent( $student_id )
	{
		global $wpdb;
		
		foreach ( $this->getItems( "student_id = '".$student_id."'" ) AS $item )
			$this->delItem( $item->id );

		$wpdb->query( "DELETE FROM {$wpdb->gradebook} WHERE `id` = {$student_id}" );
	}

	
	/**
	 * add new item
	 *
	 * @param int $student_id
	 * @param string $short_title
	 * @param string $title
	 * @param int $home 1 | 0
	 * @return string
	 */
	function addItem( $student_id, $short_title, $title, $home )
	{
		global $wpdb;
			
		$sql = "INSERT INTO {$wpdb->gradebook_items} (title, short_title, home, student_id) VALUES ('%s', '%s', '%d', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $title, $short_title, $home, $student_id ) );
		$item_id = $wpdb->insert_id;

		if ( isset($_FILES['logo']) && $_FILES['logo']['name'] != '' )
			$this->uploadLogo($item_id, $_FILES['logo']);
		
		if ( $this->error ) $this->printErrorMessage();
			
		return __('Item added','gradebook');
	}


	/**
	 * edit item
	 *
	 * @param int $item_id
	 * @param string $short_title
	 * @param string $title
	 * @param int $home 1 | 0
	 * @param boolean $del_logo
	 * @param string $image_file
	 * @param boolean $overwrite_image
	 * @return string
	 */
	function editItem( $item_id, $short_title, $title, $home, $del_logo = false, $image_file = '', $overwrite_image = false )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->gradebook_items} SET `title` = '%s', `short_title` = '%s', `home` = '%d' WHERE `id` = %d", $title, $short_title, $home, $item_id ) );
			
		// Delete Image if options is checked
		if ($del_logo || $overwrite_image) {
			$wpdb->query("UPDATE {$wpdb->gradebook_items} SET `logo` = '' WHERE `id` = {$item_id}");
			$this->delLogo( $image_file );
		}
		
		if ( isset($_FILES['logo']) && $_FILES['logo']['name'] != '' )
			$this->uploadLogo($item_id, $_FILES['logo'], $overwrite_image);
		
		if ( $this->error ) $this->printErrorMessage();
			
		return __('Item updated','gradebook');
	}


	/**
	 * delete Item
	 *
	 * @param int $item_id
	 * @return void
	 */
	function delItem( $item_id )
	{
		global $wpdb;
		
		$item = $this->getItem( $item_id );
	
			
		$wpdb->query( "DELETE FROM {$wpdb->gradebook_grades} WHERE `home_item` = '".$item_id."' OR `away_item` = '".$item_id."'" );
		$wpdb->query( "DELETE FROM {$wpdb->gradebook_items} WHERE `id` = '".$item_id."'" );
		return;
	}


	
	
	/**
	 * add Grade
	 *
	 * @param string $date
	 * @param int $home_item
	 * @param int $away_item
	 * @param string $location
	 * @param int $student_id
	 * @return string
	 */
	function addGrade( $date, $home_item, $away_item, $location, $student_id )
	{
	 	global $wpdb;
		$sql = "INSERT INTO {$wpdb->gradebook_grades} (date, home_item, away_item, location, student_id) VALUES ('%s', '%d', '%d', '%s', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $date, $home_item, $away_item, $location, $student_id ) );
	}


	/**
	 * edit Grade	 *
	 * @param string $date
	 * @param int $home_item
	 * @param int $away_item
	 * @param string $location
	 * @param int $student_id
	 * @param int $cid
	 * @return string
	 */
	function editGrade( $date, $home_item, $away_item, $location, $student_id, $grade_id )
	{
	 	global $wpdb;
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->gradebook_grades} SET `date` = '%s', `home_item` = '%d', `away_item` = '%d', `location` = '%s', `student_id` = '%d' WHERE `id` = %d", $date, $home_item, $away_item, $location, $student_id, $grade_id ) );
		return __('Grade updated','gradebook');
	}


	/**
	 * delete Grade
	 *
	 * @param int $cid
	 * @return void
	 */
	function delGrade( $grade_id )
	{
	  	global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->gradebook_grades} WHERE `id` = '".$grade_id."'" );
		return;
	}


	/**
	 * update grade results
	 *
	 * @param array $grade_id
	 * @param array $home_apparatus_points
	 * @param array $away_apparatus_points
	 * @param array $home_points
	 * @param array $away_points
	 * @return string
	 */
	function updateResults( $grades, $home_apparatus_points, $away_apparatus_points, $home_points, $away_points, $home_item, $away_item )
	{
		global $wpdb;
		if ( null != $grades ) {
			foreach ( $grades AS $grade_id ) {
				$home_points[$grade_id] = ( '' == $home_points[$grade_id] ) ? 'NULL' : intval($home_points[$grade_id]);
				$away_points[$grade_id] = ( '' == $away_points[$grade_id] ) ? 'NULL' : intval($away_points[$grade_id]);
				$home_apparatus_points[$grade_id] = ( '' == $home_apparatus_points[$grade_id] ) ? 'NULL' : intval($home_apparatus_points[$grade_id]);
				$away_apparatus_points[$grade_id] = ( '' == $away_apparatus_points[$grade_id] ) ? 'NULL' : intval($away_apparatus_points[$grade_id]);
				
				$winner = $this->getGradeResult( $home_points[$grade_id], $away_points[$grade_id], $home_item[$grade_id], $away_item[$grade_id], 'winner' );
				$loser = $this->getGradeResult( $home_points[$grade_id], $away_points[$grade_id], $home_item[$grade_id], $away_item[$grade_id], 'loser' );
				
				$wpdb->query( "UPDATE {$wpdb->gradebook_grades} SET `home_points` = ".$home_points[$grade_id].", `away_points` = ".$away_points[$grade_id].", `home_apparatus_points` = ".$home_apparatus_points[$grade_id].", `away_apparatus_points` = ".$away_apparatus_points[$grade_id].", `winner_id` = ".intval($winner).", `loser_id` = ".intval($loser)." WHERE `id` = {$grade_id}" );
			}
		}
		return __('Updated Student Results','gradebook');
	}
	
	
	/**
	 * determine grade result
	 *
	 * @param int $home_points
	 * @param int $away_points
	 * @param int $home_item
	 * @param int $away_item
	 * @param string $option
	 * @return int
	 */
	function getGradeResult( $home_points, $away_points, $home_item, $away_item, $option )
	{
		if ( $home_points > $away_points ) {
			$grade['winner'] = $home_item;
			$grade['loser'] = $away_item;
		} elseif ( $home_points < $away_points ) {
			$grade['winner'] = $away_item;
			$grade['loser'] = $home_item;
		} elseif ( 'NULL' === $home_points && 'NULL' === $away_points ) {
			$grade['winner'] = 0;
			$grade['loser'] = 0;
		} else {
			$grade['winner'] = -1;
			$grade['loser'] = -1;
		}
		
		return $grade[$option];
	}
	
	
	/**
	 * replace shortcodes with respective HTML in posts or pages
	 *
	 * @param string $content
	 * @return string
	 */
	function insert( $content )
	{
		if ( stristr( $content, '[studentstandings' )) {
			$search = "@\[studentstandings\s*=\s*(\w+)\]@i";
			
			if ( preg_grade_all($search, $content , $grades) ) {
				if (is_array($grades)) {
					foreach($grades[1] AS $key => $v0) {
						$student_id = $v0;
						$search = $grades[0][$key];
						$replace = $this->getStandingsTable( $student_id );
			
						$content = str_replace($search, $replace, $content);
					}
				}
			}
		}
		
		if ( stristr ( $content, '[studentgrades' )) {
			$search = "@\[studentgrades\s*=\s*(\w+),(.*?)\]@i";
		
			if ( preg_grade_all($search, $content , $grades) ) {
				if (is_array($grades)) {
					foreach($grades[1] AS $key => $v0) {
						$student_id = $v0;
						$search = $grades[0][$key];
						$replace = $this->getGradeTable( $student_id, $grades[2][$key] );
			
						$content = str_replace($search, $replace, $content);
					}
				}
			}
		}
		
		if ( stristr ( $content, '[studentcrosstable' )) {
			$search = "@\[studentcrosstable\s*=\s*(\w+),(|embed|popup|)\]@i";
			
			if ( preg_grade_all($search, $content , $grades) ) {
				if ( is_array($grades) ) {
					foreach($grades[1] AS $key => $v0) {
						$student_id = $v0;
						$search = $grades[0][$key];
						$replace = $this->getCrossTable( $student_id, $grades[2][$key] );
						
						$content = str_replace( $search, $replace, $content );
					}
				}
			}
		}
		
		$content = str_replace('<p></p>', '', $content);
		return $content;
	}


	/**
	 * gets student standings table
	 *
	 * @param int $student_id
	 * @param boolean $widget
	 * @return string
	 */
	function getStandingsTable( $student_id, $widget = false )
	{
		global $wpdb;
		
		$this->preferences = $this->getStudentPreferences( $student_id );
		$secondary_points_title = ( $this->isGymnasticsStudent( $student_id ) ) ? __('AP','gradebook') : __('Goals','gradebook');
			
		$out = '</p><table class="gradebook" summary="" title="'.__( 'Standings', 'gradebook' ).' '.$this->getStudentTitle($student_id).'">';
		$out .= '<tr><th class="num">&#160;</th>';
		
		$out .= '<th>'.__( 'Club', 'gradebook' ).'</th>';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'Pld', 'gradebook' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'W','gradebook' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'T','gradebook' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'L','gradebook' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.$secondary_points_title.'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'Diff', 'gradebook' ).'</th>' : '';
		$out .= '<th class="num">'.__( 'Pts', 'gradebook' ).'</th>
		   	</tr>';

		$items = $this->rankItems( $student_id );
		if ( count($items) > 0 ) {
			$rank = 0; $class = array();
			foreach( $items AS $item ) {
				$rank++;
				$class = ( in_array('alternate', $class) ) ? array() : array('alternate');
				$home_class = ( 1 == $item['home'] ) ? 'home' : '';
				
				// Add Divider class
				if ( $rank == 1 || $rank == 3 || count($items)-$rank == 3 || count($items)-$rank == 1)
					$class[] =  'divider';
				
			 	$item_title = ( $widget ) ? $item['short_title'] : $item['title'];
			 	if ( $this->isGymnasticsStudent( $student_id ) )
			 		$secondary_points = $item['apparatus_points']['plus'].':'.$item['apparatus_points']['minus'];
				else
					$secondary_points = $item['goals']['plus'].':'.$item['goals']['minus'];
		
				$out .= "<tr class='".implode(' ', $class)."'>";
				$out .= "<td class='rank'>$rank</td>";
				if ( 1 == $this->preferences->show_logo ) {
					$out .= '<td class="logo">';
					if ( $item['logo'] != '' )
					$out .= "<img src='".$this->getImageUrl($item['logo'])."' alt='".__('Logo','gradebook')."' title='".__('Logo','gradebook')." ".$item['title']."' />";
					$out .= '</td>';
				}
				$out .= "<td><span class='$home_class'>".$item_title."</span></td>";
				$out .= ( !$widget ) ? "<td class='num'>".$this->getNumDoneGrades( $item['id'] )."</td>" : '';
				$out .= ( !$widget ) ? '<td class="num">'.$this->getNumWonGrades( $item['id'] ).'</td>' : '';
				$out .= ( !$widget ) ? '<td class="num">'.$this->getNumDrawGrades( $item['id'] ).'</td>' : '';
				$out .= ( !$widget ) ? '<td class="num">'.$this->getNumLostGrades( $item['id'] ).'</td>' : '';
				if ( $this->isGymnasticsStudent( $student_id ) && !$widget )
					$out .= "<td class='num'>".$item['apparatus_points']['plus'].":".$item['apparatus_points']['minus']."</td><td class='num'>".$item['diff']."</td>";
				elseif ( !$widget )
					$out .= "<td class='num'>".$item['goals']['plus'].":".$item['goals']['minus']."</td><td class='num'>".$item['diff']."</td>";
				
				if ( $this->isGymnasticsStudent( $student_id ) )
					$out .= "<td class='num'>".$item['points']['plus'].":".$item['points']['minus']."</td>";
				else
					$out .= "<td class='num'>".$item['points']['plus']."</td>";
				$out .= "</tr>";
			}
		}
		
		$out .= '</table><p>';
		
		return $out;
	}


	/**
	 * gets grade table for given student
	 *
	 * @param int $student_id
	 * @param string $date date in MySQL format YYYY-MM-DD
	 * @return string
	 */
	function getGradeTable( $student_id, $date = '' )
	{
		$students = $this->getStudents( $student_id );
		$preferences = $this->getStudentPreferences( $student_id );
		
		$items = $this->getItems( $student_id, 'ARRAY' );
		
		$search = "student_id = '".$student_id."'";
		if ( $date != '' ) {
			$dates = explode( '|', $date );
			$s = array();
			foreach ( $dates AS $date )
				$s[] = "`date` LIKE '$date __:__:__'";
				
			$search .= ' AND ('.implode(' OR ', $s).')';
		}
		$grades = $this->getGrades( $search );
		
		$home_only = false;
		if ( 2 == $preferences->grade_calendar )
			$home_only = true;
			
		if ( $grades ) {
			$out = "</p><table class='gradebook' summary='' title='".__( 'Grade Plan', 'gradebook' )." ".$students['title']."'>";
			$out .= "<tr>
					<th class='grade'>".__( 'Grade', 'gradebook' )."</th>
					<th class='score'>".__( 'Score', 'gradebook' )."</th>";
					if ( $this->isGymnasticsStudent( $student_id ) )
					$out .= "<th class='ap'>".__( 'AP', 'gradebook' )."</th>";	
			$out .=	"</tr>";
			foreach ( $grades AS $grade ) {
				$grade->home_apparatus_points = ( NULL == $grade->home_apparatus_points ) ? '-' : $grade->home_apparatus_points;
				$grade->away_apparatus_points = ( NULL == $grade->away_apparatus_points ) ? '-' : $grade->away_apparatus_points;
				$grade->home_points = ( NULL == $grade->home_points ) ? '-' : $grade->home_points;
				$grade->away_points = ( NULL == $grade->away_points ) ? '-' : $grade->away_points;
				
				if ( !$home_only || ($home_only && (1 == $items[$grade->home_item]['home'] || 1 == $items[$grade->away_item]['home'])) ) {
					$class = ( 'alternate' == $class ) ? '' : 'alternate';
					$location = ( '' == $grade->location ) ? 'N/A' : $grade->location;
					$start_time = ( '0' == $grade->hour && '0' == $grade->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $grade->date);
									
					$gradeclass = ( $this->isOwnHomeGrade( $grade->home_item, $items ) ) ? 'home' : '';
							
					$out .= "<tr class='$class'>";
					$out .= "<td class='grade'>".mysql2date(get_option('date_format'), $grade->date)." ".$start_time." ".$location."<br /><span class='$gradeclass'>".$items[$grade->home_item]['title'].' - '. $items[$grade->away_item]['title']."</span></td>";
					$out .= "<td class='score' valign='bottom'>".$grade->home_points.":".$grade->away_points."</td>";
					if ( $this->isGymnasticsStudent( $student_id ) )
						$out .= "<td class='ap' valign='bottom'>".$grade->home_apparatus_points.":".$grade->away_apparatus_points."</td>";
					$out .= "</tr>";
				}
			}
			$out .= "</table><p>";
		}
		
		return $out;
	}
	

	/**
	 * get cross-table with home item down the left and away item across the top
	 *
	 * @param int $student_id
	 * @return string
	 */
	function getCrossTable( $student_id, $mode )
	{
		$students = $this->getStudents( $student_id );
		$items = $this->rankItems( $student_id );
		$rank = 0;
		
		$out = "</p>";
		
		// Thickbox Popup
		if ( 'popup' == $mode ) {
 			$out .= "<div id='gradebook_crosstable' style='width=800px;overfow:auto;display:none;'><div>";
		}
		
		$out .= "<table class='gradebook crosstable' summary='' title='".__( 'Crosstable', 'gradebook' )." ".$students['title']."'>";
		$out .= "<th colspan='2' style='text-align: center;'>".__( 'Club', 'gradebook' )."</th>";
		for ( $i = 1; $i <= count($items); $i++ )
			$out .= "<th class='num'>".$i."</th>";
		$out .= "</tr>";
		foreach ( $items AS $item ) {
			$rank++; $home_class = ( 1 == $item['home'] ) ? 'home' : '';
			
			$out .= "<tr>";
			$out .= "<th scope='row' class='rank'>".$rank."</th><td><span class='$home_class'>".$item['title']."</span></td>";
			for ( $i = 1; $i <= count($items); $i++ ) {
				if ( ($rank == $i) )
					$out .= "<td class='num'>-</td>";
				else
					$out .= $this->getScore($item['id'], $items[$i-1]['id']);
			}
			$out .= "</tr>";
		}
		$out .= "</table>";
	
		// Thickbox Popup End
		if ( 'popup' == $mode ) {
			$out .= "</div></div>";
			$out .= "<p><a class='thickbox' href='#TB_inline?width=800&inlineId=gradebook_crosstable' title='".__( 'Crosstable', 'gradebook' )." ".$students['title']."'>".__( 'Crosstable', 'gradebook' )." ".$students['title']." (".__('Popup','gradebook').")</a></p>";
		}
		
		$out .= "<p>";
	
		return $out;
	}
	

	/**
	 * get grade and score for items
	 *
	 * @param int $curr_item_id
	 * @param int $opponent_id
	 * @return string
	 */
	function getScore($curr_item_id, $opponent_id)
	{
		global $wpdb;

		$grade = $this->getGrades("(`home_item` = $curr_item_id AND `away_item` = $opponent_id) OR (`home_item` = $opponent_id AND `away_item` = $curr_item_id)");
		$out = "<td class='num'>-:-</td>";
		if ( $grade ) {
			// grade at home
			if ( NULL == $grade[0]->home_points && NULL == $grade[0]->away_points )
				$out = "<td class='num'>-:-</td>";
			elseif ( $curr_item_id == $grade[0]->home_item )
				$out = "<td class='num'>".$grade[0]->home_points.":".$grade[0]->away_points."</td>";
			// grade away
			elseif ( $opponent_id == $grade[0]->home_item )
				$out = "<td class='num'>".$grade[0]->away_points.":".$grade[0]->home_points."</td>";
			
		}

		return $out;
	}


	/**
	 * test if grade is home grade
	 *
	 * @param array $items
	 * @return boolean
	 */
	function isOwnHomeGrade( $home_item, $items )
	{
		if ( 1 == $items[$home_item]['home'] )
			return true;
		else
			return false;
	}
	
	
	/**
	 * displays widget
	 *
	 * @param $args
	 *
	 */
	function displayWidget( $args )
	{
		$options = get_option( 'gradebook_widget' );
		$widget_id = $args['widget_id'];
		$student_id = $options[$widget_id];

		$defaults = array(
			'before_widget' => '<li id="'.sanitize_title(get_class($this)).'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'grade_display' => $options[$student_id]['grade_display'],
			'table_display' => $options[$student_id]['table_display'],
			'info_page_id' => $options[$student_id]['info'],
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		
		$student = $this->getStudents( $student_id );
		echo $before_widget . $before_title . $student['title'] . $after_title;
		
		echo "<div id='gradebook_widget'>";
		if ( 1 == $grade_display ) {
			$home_only = ( 2 == $this->preferences->grade_calendar ) ? true : false;
				
			echo "<p class='title'>".__( 'Upcoming Grades', 'gradebook' )."</p>";
			$grades = $this->getGrades( "student_id = '".$student_id."' AND DATEDIFF(NOW(), `date`) < 0" );
			$items = $this->getItems( $student_id, 'ARRAY' );
			
			if ( $grades ) {
				echo "<ul class='grades'>";
				foreach ( $grades AS $grade ) {
					if ( !$home_only || ($home_only && (1 == $items[$grade->home_item]['home'] || 1 == $items[$grade->away_item]['home'])) )
						echo "<li>".mysql2date(get_option('date_format'), $grade->date)." ".$items[$grade->home_item]['short_title']." - ".$items[$grade->away_item]['short_title']."</li>";
				}
				echo "</ul>";
			} else {
				echo "<p>".__( 'Nothing found', 'gradebook' )."</p>";
			}
		}
		if ( 1 == $table_display ) {
			echo "<p class='title'>".__( 'Table', 'gradebook' )."</p>";
			echo $this->getStandingsTable( $student_id, true );
		}
		if ( $info_page_id AND '' != $info_page_id )
			echo "<p class='info'><a href='".get_permalink( $info_page_id )."'>".__( 'More Info', 'gradebook' )."</a></p>";
		
		echo "</div>";
		echo $after_widget;
	}


	/**
	 * widget control panel
	 *
	 * @param none
	 */
	function widgetControl( $args )
	{
		extract( $args );
	 	$options = get_option( 'gradebook_widget' );
		if ( $_POST['student-submit'] ) {
			$options[$widget_id] = $student_id;
			$options[$student_id]['table_display'] = $_POST['table_display'][$student_id];
			$options[$student_id]['grade_display'] = $_POST['grade_display'][$student_id];
			$options[$student_id]['info'] = $_POST['info'][$student_id];
			
			update_option( 'gradebook_widget', $options );
		}
		
		$checked = ( 1 == $options[$student_id]['grade_display'] ) ? ' checked="checked"' : '';
		echo '<p style="text-align: left;"><label for="grade_display_'.$student_id.'" class="gradebook-widget">'.__( 'Show Grades','gradebook' ).'</label>';
		echo '<input type="checkbox" name="grade_display['.$student_id.']" id="grade_display_'.$student_id.'" value="1"'.$checked.'>';
		echo '</p>';
			
		$checked = ( 1 == $options[$student_id]['table_display'] ) ? ' checked="checked"' : '';
		echo '<p style="text-align: left;"><label for="table_display_'.$student_id.'" class="gradebook-widget">'.__( 'Show Table', 'gradebook' ).'</label>';
		echo '<input type="checkbox" name="table_display['.$student_id.']" id="table_display_'.$student_id.'" value="1"'.$checked.'>';
		echo '</p>';
		echo '<p style="text-align: left;"><label for="info['.$student_id.']" class="gradebook-widget">'.__( 'Page' ).'<label>';
		wp_dropdown_pages(array('name' => 'info['.$student_id.']', 'selected' => $options[$student_id]['info']));
		echo '</p>';		

		echo '<input type="hidden" name="student-submit" id="student-submit" value="1" />';
	}


	/**
	 * adds code to Wordpress head
	 *
	 * @param none
	 */
	function addHeaderCode($show_all=false)
	{
		$options = get_option('gradebook');
		
		echo "\n\n<!-- WP Gradebook Plugin Version ".GRADEBOOK_VERSION." START -->\n";
		echo "<link rel='stylesheet' href='".GRADEBOOK_URL."/style.css' type='text/css' />\n";

		if ( !is_admin() ) {
			// Table styles
			echo "\n<style type='text/css'>";
			echo "\n\ttable.gradebook th { background-color: ".$options['colors']['headers']." }";
			echo "\n\ttable.gradebook tr { background-color: ".$options['colors']['rows'][1]." }";
			echo "\n\ttable.gradebook tr.alternate { background-color: ".$options['colors']['rows'][0]." }";
			echo "\n\ttable.crosstable th, table.crosstable td { border: 1px solid ".$options['colors']['rows'][0]."; }";
			echo "\n</style>";
		}

		if ( is_admin() AND (isset( $_GET['page'] ) AND substr( $_GET['page'], 0, 13 ) == 'gradebook' || $_GET['page'] == 'gradebook') || $show_all ) {
			wp_register_script( 'gradebook', GRADEBOOK_URL.'/studentmanager.js', array('thickbox', 'colorpicker', 'sack' ), GRADEBOOK_VERSION );
			wp_print_scripts( 'gradebook' );
			echo '<link rel="stylesheet" href="'.get_option( 'siteurl' ).'/wp-includes/js/thickbox/thickbox.css" type="text/css" media="screen" />';
			
			?>
			<script type='text/javascript'>
			//<![CDATA[
				   GradeBookAjaxL10n = {
				   blogUrl: "<?php bloginfo( 'wpurl' ); ?>", pluginPath: "<?php echo GRADEBOOK_PATH; ?>", pluginUrl: "<?php echo GRADEBOOK_URL; ?>", requestUrl: "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php", imgUrl: "<?php echo GRADEBOOK_URL; ?>/images", Edit: "<?php _e("Edit"); ?>", Post: "<?php _e("Post"); ?>", Save: "<?php _e("Save"); ?>", Cancel: "<?php _e("Cancel"); ?>", pleaseWait: "<?php _e("Please wait..."); ?>", Revisions: "<?php _e("Page Revisions"); ?>", Time: "<?php _e("Insert time"); ?>"
				   }
			//]]>
			  </script>
			<?php
		}
		
		echo "<!-- WP GradeBook Plugin END -->\n\n";
	}


	/**
	 * add TinyMCE Button
	 *
	 * @param none
	 * @return void
	 */
	function addTinyMCEButton()
	{
		// Don't bother doing this stuff if the current user lacks permissions
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
		
		// Check for GradeBook capability
		if ( !current_user_can('manage_students') ) return;
		
		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", array(&$this, 'addTinyMCEPlugin'));
			add_filter('mce_buttons', array(&$this, 'registerTinyMCEButton'));
		}
	}
	function addTinyMCEPlugin( $plugin_array )
	{
		$plugin_array['GradeBook'] = GRADEBOOK_URL.'/tinymce/editor_plugin.js';
		return $plugin_array;
	}
	function registerTinyMCEButton( $buttons )
	{
		array_push($buttons, "separator", "GradeBook");
		return $buttons;
	}
	function changeTinyMCEVersion( $version )
	{
		return ++$version;
	}
	
	
	/**
	 * display global settings page (e.g. color scheme options)
	 *
	 * @param none
	 * @return void
	 */
	function displayOptionsPage()
	{
		$options = get_option('gradebook');
		
		if ( isset($_POST['updateGradeBook']) ) {
			check_admin_referer('gradebook_manage-global-student-options');
			$options['colors']['headers'] = $_POST['color_headers'];
			$options['colors']['rows'] = array( $_POST['color_rows_alt'], $_POST['color_rows'] );
			
			update_option( 'gradebook', $options );
			echo '<div id="message" class="updated fade"><p><strong>'.__( 'Settings saved', 'gradebook' ).'</strong></p></div>';
		}
		
		
		echo "\n<form action='' method='post'>";
		wp_nonce_field( 'gradebook_manage-global-student-options' );
		echo "\n<div class='wrap'>";
		echo "\n\t<h2>".__( 'Gradebook Global Settings', 'gradebook' )."</h2>";
		echo "\n\t<h3>".__( 'Color Scheme', 'gradebook' )."</h3>";
		echo "\n\t<table class='form-table'>";
		echo "\n\t<tr valign='top'>";
		echo "\n\t\t<th scope='row'><label for='color_headers'>".__( 'Table Headers', 'gradebook' )."</label></th><td><input type='text' name='color_headers' id='color_headers' value='".$options['colors']['headers']."' size='10' /><a href='#' class='colorpicker' onClick='cp.select(document.forms[0].color_headers,\"pick_color_headers\"); return false;' name='pick_color_headers' id='pick_color_headers'>&#160;&#160;&#160;</a></td>";
		echo "\n\t</tr>";
		echo "\n\t<tr valign='top'>";
		echo "\n\t<th scope='row'><label for='color_rows'>".__( 'Table Rows', 'gradebook' )."</label></th>";
		echo "\n\t\t<td>";
		echo "\n\t\t\t<p class='table_rows'><input type='text' name='color_rows_alt' id='color_rows_alt' value='".$options['colors']['rows'][0]."' size='10' /><a href='#' class='colorpicker' onClick='cp.select(document.forms[0].color_rows_alt,\"pick_color_rows_alt\"); return false;' name='pick_color_rows_alt' id='pick_color_rows_alt'>&#160;&#160;&#160;</a></p>";
		echo "\n\t\t\t<p class='table_rows'><input type='text' name='color_rows' id='color_rows' value='".$options['colors']['rows'][1]."' size='10' /><a href='#' class='colorpicker' onClick='cp.select(document.forms[0].color_rows,\"pick_color_rows\"); return false;' name='pick_color_rows' id='pick_color_rows'>&#160;&#160;&#160;</a></p>";
		echo "\n\t\t</td>";
		echo "\n\t</tr>";
		echo "\n\t</table>";
		echo "\n<input type='hidden' name='page_options' value='color_headers,color_rows,color_rows_alt' />";
		echo "\n<p class='submit'><input type='submit' name='updateGradeBook' value='".__( 'Save Preferences', 'gradebook' )." &raquo;' class='button' /></p>";
		echo "\n</form>";
	
		echo "<script language='javascript'>
			syncColor(\"pick_color_headers\", \"color_headers\", document.getElementById(\"color_headers\").value);
			syncColor(\"pick_color_rows\", \"color_rows\", document.getElementById(\"color_rows\").value);
			syncColor(\"pick_color_rows_alt\", \"color_rows_alt\", document.getElementById(\"color_rows_alt\").value);
		</script>";
		
		echo "<p>".sprintf(__( "To add and manage students, go to the <a href='%s'>Management Page</a>", 'gradebook' ), get_option( 'siteurl' ).'/wp-admin/edit.php?page=gradebook/manage-students.php')."</p>";
		if ( !function_exists('register_uninstall_hook') ) { ?>
		<div class="wrap">
			<h3 style='clear: both; padding-top: 1em;'><?php _e( 'Uninstall Gradebook', 'gradebook' ) ?></h3>
			<form method="get" action="index.php">
				<input type="hidden" name="gradebook" value="uninstall" />
				<p><input type="checkbox" name="delete_plugin" value="1" id="delete_plugin" /> <label for="delete_plugin"><?php _e( 'Yes I want to uninstall Gradebook Plugin. All Data will be deleted!', 'gradebook' ) ?></label> <input type="submit" value="<?php _e( 'Uninstall Gradebook', 'gradebook' ) ?> &raquo;" class="button" /></p>
			</form>
		</div>
		<?php }
	}
	
	
	/**
	 * initialize widget
	 *
	 * @param none
	 */
	function activateWidget()
	{
		if ( !function_exists('register_sidebar_widget') )
			return;
		
		foreach ( $this->getActiveStudents() AS $student_id => $student ) {
			$name = __( 'Student', 'gradebook' ) .' - '. $student['title'];
			register_sidebar_widget( $name , array( &$this, 'displayWidget' ) );
			register_widget_control( $name, array( &$this, 'widgetControl' ), '', '', array( 'student_id' => $student_id, 'widget_id' => sanitize_title($name) ) );
		}
	}


	/**
	 * initialize plugin
	 *
	 * @param none
	 */
	function activate()
	{
		global $wpdb;
		include_once( ABSPATH.'/wp-admin/includes/upgrade.php' );
		
		$options = array();
		$options['version'] = GRADEBOOK_VERSION;
		$options['colors']['headers'] = '#dddddd';
		$options['colors']['rows'] = array( '#ffffff', '#efefef' );
		
		$old_options = get_option( 'gradebook' );
		if ( !isset($old_options['version']) || version_compare($old_options['version'], GRADEBOOK_VERSION, '<') ) {
			require_once( GRADEBOOK_PATH . '/gradebook.php' );
			update_option( 'gradebook', $options );
		}
		
		$charset_collate = '';
		if ( $wpdb->supports_collation() ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
		
		$create_students_sql = "CREATE TABLE {$wpdb->gradebook} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 30 ) NOT NULL ,
						`forwin` tinyint( 4 ) NOT NULL default '2',
						`fordraw` tinyint( 4 ) NOT NULL default '1',
						`forloss` tinyint( 4 ) NOT NULL default '0',
						`grade_calendar` tinyint( 1 ) NOT NULL default '1',
						`type` tinyint( 1 ) NOT NULL default '2',
						`show_logo` tinyint( 1 ) NOT NULL default '0',
						`active` tinyint( 1 ) NOT NULL default '1' ,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->gradebook, $create_students_sql );
			
		$create_items_sql = "CREATE TABLE {$wpdb->gradebook_items} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 25 ) NOT NULL ,
						`short_title` varchar( 25 ) NOT NULL,
						`logo` varchar( 50 ) NOT NULL,
						`home` tinyint( 1 ) NOT NULL ,
						`student_id` int( 11 ) NOT NULL ,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->gradebook_items, $create_items_sql );
		
		$create_grades_sql = "CREATE TABLE {$wpdb->gradebook_grades} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`date` datetime NOT NULL ,
						`home_item` int( 11 ) NOT NULL ,
						`away_item` int( 11 ) NOT NULL ,
						`location` varchar( 100 ) NOT NULL ,
						`student_id` int( 11 ) NOT NULL ,
						`home_apparatus_points` tinyint( 4 ) NULL default NULL,
						`away_apparatus_points` tinyint( 4 ) NULL default NULL,
						`home_points` tinyint( 4 ) NULL default NULL,
						`away_points` tinyint( 4 ) NULL default NULL,
						`winner_id` int( 11 ) NOT NULL,
						`loser_id` int( 11 ) NOT NULL,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->gradebook_grades, $create_grades_sql );
			
		add_option( 'gradebook', $options, 'Gradebook Options', 'yes' );
		
		/*
		* Add widget options
		*/
		if ( function_exists('register_sidebar_widget') ) {
			$options = array();
			add_option( 'gradebook_widget', $options, 'Gradebook Widget Options', 'yes' );
		}
		
		/*
		* Set Capabilities
		*/
		$role = get_role('administrator');
		$role->add_cap('manage_students');
	}
	
	
	/**
	 * Uninstall Plugin
	 *
	 * @param none
	 */
	function uninstall()
	{
		global $wpdb;
		
		$wpdb->query( "DROP TABLE {$wpdb->gradebook_grades}" );
		$wpdb->query( "DROP TABLE {$wpdb->gradebook_items}" );
		$wpdb->query( "DROP TABLE {$wpdb->gradebook}" );
		
		delete_option( 'gradebook_widget' );
		delete_option( 'gradebook' );
		
		if ( !function_exists('register_uninstall_hook') ) {
			$plugin = basename(__FILE__, ".php") .'/plugin-hook.php';
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( function_exists( "deactivate_plugins" ) )
				deactivate_plugins( $plugin );
			else {
				$current = get_option('active_plugins');
				array_splice($current, array_search( $plugin, $current), 1 ); // Array-fu!
				update_option('active_plugins', $current);
				do_action('deactivate_' . trim( $plugin ));
			}
		}
	}
	
	
	/**
	 * adds menu to the admin interface
	 *
	 * @param none
	 */
	function addAdminMenu()
	{

		$plugin = 'gradebook/plugin-hook.php';
 		add_management_page( __( 'Grades', 'gradebook' ), __( 'GradeBook', 'gradebook' ), 'manage_students', basename( __FILE__, ".php" ).'/manage-students.php' );
		
		 
	}
	
	
	
}
?>
