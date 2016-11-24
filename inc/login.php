<?
	//------------------------------------------------------------------------------------------
	function render_login() {
	//------------------------------------------------------------------------------------------
		global $LOGIN;
		
		echo <<<END
		<h1>Login</h1>
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
			  <input type="submit" class="btn btn-primary" value="Submit">
			</div>
		  </div>
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
			return proc_error('Please provide username and password.');
		
		$sql = sprintf('SELECT * FROM %s WHERE %s = ?',
			db_esc($LOGIN['users_table']), 
			db_esc($LOGIN['username_field']));
		
		$db = db_connect();
		if($db === false)
			return proc_error("Invalid database configuration.");
			
		$stmt = $db->prepare($sql);
		if($stmt === false)
			return proc_error("Invalid login parameters.", $db);
		
		if($stmt->execute(array($_POST['username'])) === false)
			return proc_error("Invalid login parameters.", $db);
		
		$error_msg = "Invalid {$LOGIN['form']['username']} and/or {$LOGIN['form']['password']}.";
		
		if(($user = $stmt->fetch(PDO::FETCH_ASSOC)) === false)
			return proc_error($error_msg);
		
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
		
		// allow the app to do some initialization
		if(isset($LOGIN['login_success_proc']) && $LOGIN['login_success_proc'] != '') {
			if(!function_exists($LOGIN['login_success_proc']))
				proc_error("Provided plugin function for 'login_success_proc' does not exist");
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