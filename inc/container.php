<?
	//------------------------------------------------------------------------------------------
	function render_navigation_bar() {
	//------------------------------------------------------------------------------------------
		global $APP;
		global $LOGIN;
		
		if(is_popup() || is_inline())
			return;
		
		echo '<nav class="navbar navbar-default">' .
			'<div class="container-fluid">' .
			'<div class="navbar-header">';
			
		
		// collapsible
		echo '<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#main-navbar"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>';			
			
		echo '<a class="navbar-brand" href="?">' . $APP['title'] . '</a></div>';

		if(is_logged_in()) { 
			build_main_menu($menu); 
			echo '<div class="collapse navbar-collapse" id="main-navbar">';
			echo '<ul class="nav navbar-nav">';
			foreach($menu as $main_item) {
				echo '<li><a class="dropdown-toggle" data-toggle="dropdown" href="#">' .
					html($main_item['name']) . '<span class="caret"></span></a><ul class="dropdown-menu">';
				
				foreach($main_item['items'] as $sub_item) {
					if(is_string($sub_item)) // some separator or other shit
						echo "$sub_item\n";
					else
						echo "<li><a href='{$sub_item['href']}'>". html($sub_item['label']). "</a></li>\n";
				}
				
				echo '</ul></li>';
			}
			echo '</ul>'; 
	
			$user_details_href = '?' . http_build_query(array(
				'table' => $LOGIN['users_table'],
				'mode' => MODE_VIEW,
				$LOGIN['primary_key'] =>  $_SESSION['user_id']
			));
			
			echo '<ul class="nav navbar-nav navbar-right">'.
				'<li><a name="" href="'. $user_details_href .'"><span class="glyphicon glyphicon-user"></span> '. 
				(isset($LOGIN['name_field']) ? $_SESSION['user_data'][$LOGIN['name_field']] : '') . 
				'</a></li>'.
				'<li><a href="#" id="logout"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li></ul>';
		} 
		
		echo '</div></div></nav>';
	}
	
	//------------------------------------------------------------------------------------------
	function build_main_menu(&$menu) {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		global $APP;
		
		$menu = array();
		
		$menu[0] = array('name' => 'New', 'items' => array());
		if($APP['mainmenu_tables_autosort'])
			uasort($TABLES, 'sort_tables_new');
		foreach($TABLES as $table_name => $info)
			if(in_array(MODE_NEW, $info['actions']))
				$menu[0]['items'][] = array('label' => $info['item_name'], 'href' => "?table={$table_name}&mode=" . MODE_NEW);
		
		$menu[1] = array('name' => 'Browse & Edit', 'items' => array());
		if($APP['mainmenu_tables_autosort'])
			uasort($TABLES, 'sort_tables_list');
		foreach($TABLES as $table_name => $info)
			if(in_array(MODE_LIST, $info['actions']))
				$menu[1]['items'][] = array('label' => $info['display_name'], 'href' => "?table={$table_name}&mode=" . MODE_LIST);
			
		if(isset($APP['menu_complete_proc']) && trim($APP['menu_complete_proc']) != '')
			/*call_user_func*/ $APP['menu_complete_proc']($menu);
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