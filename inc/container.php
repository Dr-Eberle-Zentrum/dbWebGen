<?php
	//------------------------------------------------------------------------------------------
	function render_navigation_bar() {
	//------------------------------------------------------------------------------------------
		global $APP;
		global $LOGIN;

		// when to turn off navigation bar?
		if(
			// if mode is set
			isset($_GET['mode'])

			// if not logged in, we show the navbar, since it will only show the app name anyway
			&& is_logged_in()

			&& (
				// either deliberately turned off
				(!in_array($_GET['mode'], array(MODE_VIEW, MODE_EDIT, MODE_LIST, MODE_NEW))
				&& isset($_GET[PLUGIN_PARAM_NAVBAR])
				&& $_GET[PLUGIN_PARAM_NAVBAR] == PLUGIN_NAVBAR_OFF)


				|| // or we are visualizing a stored query
				($_GET['mode'] == MODE_QUERY
				&& isset($_GET['id'])
				&&
					// not explictly turned on
					!(isset($_GET[PLUGIN_PARAM_NAVBAR])
					&& $_GET[PLUGIN_PARAM_NAVBAR] == PLUGIN_NAVBAR_ON)
				)

				|| // or we are in map pickin' mode
				 $_GET['mode'] == MODE_MAP_PICKER

				|| // or the navbar is just turned off goddammit
				(isset($_GET[PLUGIN_PARAM_NAVBAR])
				&& $_GET[PLUGIN_PARAM_NAVBAR] == PLUGIN_NAVBAR_OFF)
			)
		)
		{
			return;
		}

		if(is_popup() || is_inline())
			return;

		echo '<nav class="navbar navbar-default">' .
			'<div class="container-fluid">' .
			'<div class="navbar-header">';


		// collapsible
		echo '<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#main-navbar"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>';

		echo '<a class="navbar-brand" href="?">' . $APP['title'] . '</a></div>';

		if(is_logged_in() && !isset($_SESSION['pseudo_login'])) {
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

			if(GlobalSearch::is_enabled())
				echo '<li>' . GlobalSearch::render_searchbox() . "</li>\n";

			echo '</ul>';

			if(isset($LOGIN['users_table'])) {
				$user_details_href = null;
				if(is_string($LOGIN['users_table']) &&
					// if current user is the guest user, disallow editing of user details
					!(isset($LOGIN['guest_user']) && $LOGIN['guest_user'] == $_SESSION['user_data'][$LOGIN['username_field']]))
				{
					$user_details_href = '?' . http_build_query(array(
						'table' => $LOGIN['users_table'],
						'mode' => MODE_VIEW,
						$LOGIN['primary_key'] =>  $_SESSION['user_id']
					));
				}

				require_once 'setup/wizard.php';
				$setup_icon = SetupWizard::is_allowed() ? sprintf(
					'<li><a href="?mode=%s" id="setup-mode" title="%s"><span class="glyphicon glyphicon-cog"></span></a></li>',
					MODE_SETUP, l10n('setup.heading')
				): '';

				echo '<ul class="nav navbar-nav navbar-right"><li>'.
				($user_details_href? '<a name="" href="'. $user_details_href .'">' : '<a>').
				'<span class="glyphicon glyphicon-user"></span> '.
				(isset($LOGIN['name_field']) ? $_SESSION['user_data'][$LOGIN['name_field']] : '') .
				'</a></li>'.
				'<li><a href="javascript:void(0)" id="logout"><span class="glyphicon glyphicon-log-out"></span> '.l10n('login.logout-navbar-label').'</a></li>'.
				$setup_icon . '</ul>';
			}
		}

		echo '</div></div></nav>';
	}

	//------------------------------------------------------------------------------------------
	function build_main_menu(&$menu) {
	//------------------------------------------------------------------------------------------
		global $TABLES;
		global $APP;

		$menu = array();

		$menu[0] = array('name' => l10n('menu.new'), 'items' => array());
		if($APP['mainmenu_tables_autosort'])
			uasort($TABLES, 'sort_tables_new');
		foreach($TABLES as $table_name => $info)
			if(in_array(MODE_NEW, $info['actions']) && !is_table_hidden_from_menu($info, MODE_NEW))
				$menu[0]['items'][] = array('label' => $info['item_name'], 'href' => "?table={$table_name}&mode=" . MODE_NEW);

		$menu[1] = array('name' => l10n('menu.browse+edit'), 'items' => array());
		if($APP['mainmenu_tables_autosort'])
			uasort($TABLES, 'sort_tables_list');
		foreach($TABLES as $table_name => $info)
			if(in_array(MODE_LIST, $info['actions']) && !is_table_hidden_from_menu($info, MODE_LIST))
				$menu[1]['items'][] = array('label' => $info['display_name'], 'href' => "?table={$table_name}&mode=" . MODE_LIST);

		if(isset($APP['menu_complete_proc']) && trim($APP['menu_complete_proc']) != '')
			/*call_user_func*/ $APP['menu_complete_proc']($menu);

		// remove any empty menu
		foreach($menu as $index => $menu_info)
			if(!isset($menu_info['items']) || count($menu_info['items']) == 0)
				unset($menu[$index]);
	}

	//------------------------------------------------------------------------------------------
	function render_main_container() {
	//------------------------------------------------------------------------------------------
		global $APP;

		try {
			if(!is_logged_in()) {
				if(count($_POST) == 0 || !process_login()) {
					render_login();
					return;
				}
				else {
					header("Location: {$_SERVER['REQUEST_URI']}");
					exit;
				}
			}

			if(!isset($_GET['mode']))
				$_GET['mode'] = MODE_LIST;

			if(!isset($_GET['table']) && in_array($_GET['mode'], array(MODE_LIST, MODE_NEW, MODE_EDIT, MODE_VIEW))) {
				// for these modes 'table' param must be there
				if(isset($APP['render_main_page_proc']))
					$APP['render_main_page_proc']();
				else
					echo l10n('main-page.html');
			}

			else {
				switch($_GET['mode']) {
					case MODE_SETUP:
						require_once ENGINE_PATH_LOCAL . 'inc/setup/wizard.php';
						$w = new SetupWizard;
						echo $w->render();
						break;

					case MODE_NEW:
						require_once ENGINE_PATH_LOCAL . 'inc/fields/fields.php';
						require_once ENGINE_PATH_LOCAL . 'inc/new_edit.php';
						process_form();
						render_form();
						break;

					case MODE_EDIT:
						require_once ENGINE_PATH_LOCAL . 'inc/fields/fields.php';
						require_once ENGINE_PATH_LOCAL . 'inc/new_edit.php';
						if(!process_form())
							render_form();
						break;

					case MODE_LIST:
						require_once ENGINE_PATH_LOCAL . 'inc/list.php';
						render_list();
						break;

					case MODE_VIEW:
						require_once ENGINE_PATH_LOCAL . 'inc/view.php';
						render_view();
						break;

					case MODE_QUERY:
						require_once ENGINE_PATH_LOCAL . 'inc/fields/fields.php';
						require_once ENGINE_PATH_LOCAL . 'inc/query.php';
						$p = new QueryPage;
						$p->render();
						break;

					case MODE_MAP_PICKER:
						require_once ENGINE_PATH_LOCAL . 'inc/map_picker.php';
						render_map_picker();
						break;

					case MODE_PLUGIN:
						if(!isset($APP['additional_callable_plugin_functions']))
							return proc_error(l10n('error.no-plugin-functions'));

						if(!isset($_GET[PLUGIN_PARAM_FUNC])
							|| !in_array($_GET[PLUGIN_PARAM_FUNC], $APP['additional_callable_plugin_functions'])
							|| !function_exists($_GET[PLUGIN_PARAM_FUNC]))
							return proc_error(l10n('error.invalid-function', $_GET[PLUGIN_PARAM_FUNC]));

						// call the rendering function
						$_GET[PLUGIN_PARAM_FUNC]();
						break;

					case MODE_GLOBALSEARCH:
						echo GlobalSearch::render_result();
						break;

					case MODE_MERGE:
						require_once ENGINE_PATH_LOCAL . 'inc/merge.php';
						$p = new MergeRecordsPage;
						$p->render();
						break;

					default:
						throw new Exception(l10n('error.invalid-mode', $_GET['mode']));
				}
			}
		}
		catch(Exception $e) {
			proc_error(l10n('error.exception', $e->getMessage()));
		}
	}
?>
