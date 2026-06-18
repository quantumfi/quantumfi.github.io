<?php
class MGController {
	function __construct($f3) {
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		header("Pragma: no-cache"); // HTTP 1.0.
		header("Expires: 0"); // Proxies.

		$this->db=new DB\SQL(
			$f3->get('db_dns') . $f3->get('db_name'),
			$f3->get('db_user'),
			$f3->get('db_pass')
		);
		
		$this->games = new games($this->db);
		$this->users = new users($this->db);
		$this->user_game = new user_game($this->db);
		$this->user_consent = new user_consent($this->db);
		
        $f3->set('year', date('Y'));
	}
	
	function afterroute() {
		//echo Template::instance()->render('template.htm');
		echo Template::instance()->render('layout.htm');
    }
	
	function sendCreateGameAlert($f3, $gameId, $user)
	{
		$qMAPI = 'http://quantumfi.com.au/api/sendmail/process.php';
			
		$to = $f3->get('CONFIG.adminEmail');
		$frN = 'Minority Game Robot';
		$fr = 'no-reply@quantumfi.com.au';
		$sbj = 'MG create game alert. Game Id: '.$gameId;
		$msg = 'Dear Admin, <br/><br/>'.$user.' has just created a new game </b><br/><br/>This is an auto-generated email. Please don\'t reply to this email.<br/><br/>Sincerely,<br/>Minority game team<br/><br/>';
		
		$cnt_array = array('fr'=>$fr, 'to'=>$to, 'subject'=>$sbj, 'message'=>$msg, 'frName'=>$frN);
		$cnt_json = json_encode($cnt_array);
		
		$this->post_json($qMAPI, $cnt_json);
	}

	function post_json($url, $json_content)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json_content);

		$json_response = curl_exec($curl);

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ( $status != 201 ) {
			//die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
		}

		curl_close($curl);

		$response = json_decode($json_response, true);
	}
}