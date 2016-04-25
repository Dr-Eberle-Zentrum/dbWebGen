<?
	//------------------------------------------------------------------------------------------
	function render_navigation_bar() {
	//------------------------------------------------------------------------------------------
		global $APP;
		global $LOGIN;
		
		if(is_popup())
			return;
		
		echo '<nav class="navbar navbar-default">' .
			'<div class="container-fluid">' .
			'<div class="navbar-header">'.
			'<a class="navbar-brand" href="?">' . $APP['title'] . '</a>'.
			'</div>';

		if(is_logged_in()) { 
			build_main_menu($menu); 
			echo '<ul class="nav navbar-nav">';
			foreach($menu as $main_item) {
				echo '<li><a class="dropdown-toggle" data-toggle="dropdown" href="#">' .
					html($main_item['name']) . '<span class="caret"></span></a><ul class="dropdown-menu">';
				
				foreach($main_item['items'] as $sub_item)
					echo "<li><a href='{$sub_item['href']}'>". html($sub_item['label']). "</a></li>\n";
				
				echo '</ul></li>';
			}
			echo '</ul>'; 
	
			echo '<ul class="nav navbar-nav navbar-right">'.
				'<li><a name=""><span class="glyphicon glyphicon-user"></span> '. 
				(isset($LOGIN['name_field']) ? $_SESSION['user_data'][$LOGIN['name_field']] : '') . 
				'</a></li>'.
				'<li><a href="#" id="logout"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li></ul>';
		} 
		
		echo '</div></nav>';
	}
	
	//------------------------------------------------------------------------------------------
	function render_main_container() {
	//------------------------------------------------------------------------------------------
		global $APP;		
		
		try {
			if(!is_logged_in()) {
				if(count($_POST) == 0 || !process_login())
					render_login();
				else {
					header("Location: {$_SERVER['REQUEST_URI']}");
					exit;
				}
			}
			
			else if(!isset($_GET['table'])) {
				if(isset($APP['render_main_page_proc']))
					$APP['render_main_page_proc']();
				else
					echo '<p>Choose an action from the top menu.</p>';
			}
				
			else {
				if(!isset($_GET['mode']))
					$_GET['mode'] = MODE_LIST;		
				
				switch($_GET['mode']) {
					case MODE_NEW:
						require_once ENGINE_PATH . 'inc/new_edit.php';
						process_form();				
						render_form(); 
						break;
					
					case MODE_EDIT:	
						require_once ENGINE_PATH . 'inc/new_edit.php';
						if(!process_form())
							render_form(); 
						break;
						
					case MODE_LIST:
						require_once ENGINE_PATH . 'inc/list.php';
						render_list();
						break;
						
					case MODE_VIEW:
						require_once ENGINE_PATH . 'inc/view.php';
						render_view();
						break;
						
					default:
						throw new Exception("Invalid mode '{$_GET['mode']}'.");
				}
			}
		}
		catch(Exception $e) {
			proc_error('Exception: ' . $e->getMessage());
		}
	}
?>