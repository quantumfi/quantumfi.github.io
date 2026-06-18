<?php
//set_include_path(get_include_path() . get_include_path().'/phpseclib');
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib' . PATH_SEPARATOR );
require_once "Net/SSH2.php";

class MGSshController{
	
	function sshTemplate($f3){

		$ssh = new Net_SSH2('qfi-app-pro-1.srv.sydney.edu.au');
		if (!$ssh->login('fernando', '4P37P9gR')) {
			exit('Login Failed');
		}

		echo $ssh->exec('pwd');
		echo $ssh->exec('ls -la');
		/*
		$configuration = new Ssh\Configuration('qfi-app-pro-1.srv.sydney.edu.au');
		$authentication = new Ssh\Authentication\Password('fernando', '4P37P9gR');

		$session = new Session($configuration, $authentication);
		$exec = $session->getExec();

		echo $exec->run('ls -lah');
		
		$connection = ssh2_connect('qfi-app-pro-1.srv.sydney.edu.au', 22);
		ssh2_auth_password($connection, 'fernando', '4P37P9gR');

		$stream = ssh2_exec($connection, '/usr/local/bin/php -i');*/
		die();
	}
}