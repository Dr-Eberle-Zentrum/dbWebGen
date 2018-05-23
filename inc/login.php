<?php
	//------------------------------------------------------------------------------------------
	function render_login() {
	//------------------------------------------------------------------------------------------
		global $LOGIN;
		$heading = l10n('login.head');
		$btn = l10n('login.button');
		$guest_btn = l10n('login.guest-access');

		$guest_button = '';
		if(isset($LOGIN['guest_user']) && mb_strlen($LOGIN['guest_user']) > 0) {
			$guest_button = <<<HTML
				<hr />
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
					  <button id="guest-access" class="btn btn-success"><span class="glyphicon glyphicon glyphicon-triangle-right space-right"></span> $guest_btn</button>
					</div>
					<script>
						$(document).ready(function() {
							$('#guest-access').click(function() {
								$('#username').val('{$LOGIN['guest_user']}');
								$('#password').val('');
								$('form').submit();
							});
						});
					</script>
			  	</div>
HTML;
		}

		echo <<<END
		<h1>$heading</h1>
		<form class="form-horizontal" role="form" method="post">
		  <div class="form-group">
			<label class="control-label col-sm-2" for="username">{$LOGIN['form']['username']}:</label>
			<div class="col-sm-3">
			  <input type="text" class="form-control" name="username" id="username">
			</div>
		  </div>
		  <div class="form-group">
			<label class="control-label col-sm-2" for="password">{$LOGIN['form']['password']}:</label>
			<div class="col-sm-3">
			  <input type="password" class="form-control" name="password" id="password">
			</div>
		  </div>
		  <div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
			  <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-log-in space-right"></span> $btn</button>
			</div>
		  </div>
		  $guest_button
		</form>
END;
	}

	//------------------------------------------------------------------------------------------
	function verify_password($plain, $hash) {
	//------------------------------------------------------------------------------------------
		global $LOGIN;
		if(isset($LOGIN['password_verify_func']))
			return $LOGIN['password_verify_func']($plain, $hash);

		// default: simply compare
		if(!isset($LOGIN['password_hash_func']))
			return $hash == $plain;
		return $hash == $LOGIN['password_hash_func']($plain);
	}

	//------------------------------------------------------------------------------------------
	function process_login() {
	//------------------------------------------------------------------------------------------
		global $LOGIN;

		if(!isset($_POST['username']) || !isset($_POST['password']))
			return proc_error(l10n('error.missing-login-data', $LOGIN['form']['username'], $LOGIN['form']['password']));

		$error_msg = l10n('error.invalid-login', $LOGIN['form']['username'], $LOGIN['form']['password']);

		if(is_array($LOGIN['users_table'])) {
			// assoc array based authentication
			$found = false;
			foreach($LOGIN['users_table'] as $user) {
				if($user[$LOGIN['username_field']] == $_POST['username']) {
					$found = true;
					break;
				}
			}
			if(!$found)
				return proc_error($error_msg);
		}
		else {
			// database table based authentication
			$sql = sprintf('SELECT * FROM %s WHERE %s = ?',
				db_esc($LOGIN['users_table']),
				db_esc($LOGIN['username_field']));

			if(!db_get_single_row($sql, array($_POST['username']), $user))
				return false;
			if($user === false)
				return proc_error($error_msg);
		}

		if(!verify_password($_POST['password'], $user[$LOGIN['password_field']]))
			return proc_error($error_msg);

		session_login($user);
		return true;
	}

	//------------------------------------------------------------------------------------------
	function is_logged_in() {
	//------------------------------------------------------------------------------------------
		global $LOGIN;
		if(!isset($LOGIN) || count($LOGIN) == 0)
			return true;

		return isset($_SESSION['user_id']) && $_SESSION['user_id'] != '';
	}

	//------------------------------------------------------------------------------------------
	function session_login($user) {
	//------------------------------------------------------------------------------------------
		global $LOGIN;

		$_SESSION['user_id'] = $user[$LOGIN['primary_key']];
		$_SESSION['user_data'] = $user;
		if(isset($_SESSION['pseudo_login']))
			unset($_SESSION['pseudo_login']);

		// allow the app to do some initialization
		if(isset($LOGIN['login_success_proc']) && $LOGIN['login_success_proc'] != '') {
			if(!function_exists($LOGIN['login_success_proc']))
				proc_error(l10n('error.invalid-function', $LOGIN['login_success_proc']));
			else
				call_user_func($LOGIN['login_success_proc']);
		}
	}

	//------------------------------------------------------------------------------------------
	function session_logout() {
	//------------------------------------------------------------------------------------------
		header('Content-Type: text/plain');

		unset($_SESSION['user_id']);
		unset($_SESSION['user_data']);

		session_unset();
		session_destroy();

		return true;
	}
?>
