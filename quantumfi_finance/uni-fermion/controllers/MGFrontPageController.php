<?php

class MGFrontPageController extends MGController{
	
	function index($f3){
		if (!$f3->exists('SESSION.user') || $f3->get('SESSION.user') == ''){
			$f3->reroute('/login');
			
		}else{
			$f3->reroute('/gamespace');
		}
	}
	
	function consentForm($f3){
		if ($f3->exists('POST.email')){//No Edit allowed because user from this page not protected by password.
			if ($this->saveConsentForm($f3)){
				$this->emailConsentForm($this->user_consent);
				
				$f3->set('message', 'An email has been sent to you. Please forward back that email to the sender for finishing the consent form process. Thanks!');
			}else{
				$f3->set('message', 'Missing mandatory fields!');
			}
		}
		
		$f3->set('inc', 'consentForm.htm');
	}
	
	private function emailConsentForm($consentFormObj)
	{
		$formMap = array('1'=>'Yes', '-1'=>'No');
		
		$qMAPI = 'http://quantumfi.com.au/api/sendmail/process.php';
		$to = $consentFormObj['email'];
		$frN = 'Karina Arias Calluari';
		$fr = ' kari0293@uni.sydney.edu.au';
		$sbj = "Re: FERMION consent form confirmation";
		$msg = 'Dear ' . $consentFormObj['name'] . ', <br/><br/>This email is to inform you that you have just filled up the consent form regarding the FERMION as below:<br/><br/>';
		$msg = $msg . '
<div class="col-lg-10 col-lg-offset-1 well">
	<label class="col-lg-6 control-label text-right radio">Name : '. $consentFormObj['name'] .'</label><br/>
	<label class="col-lg-6 control-label text-right radio">Email : '. $consentFormObj['email'] .'</label><br/>
</div>
<div class="col-lg-10 col-lg-offset-1 well">
	<h3 class="center-block text-center">AGENT-BASED SIMULATIONS OF STOCK MARKET USING A PRICE-FLUCTUATION GAMES MODEL</h3>
	<h4 class="center-block text-center">PARTICIPANT CONSENT FORM</h4>
	<p>
		I agree to take part in this research study.
	</p>
	<p>
		In giving my consent I state that:
	</p>
	<ul>
		<li><p>I understand the purpose of the study, what I will be asked to do, and any risks/benefits involved.</p></li>
		<li><p>I have read the Participant Information Statement and have been able to discuss my involvement in the study with the researchers if I wished to do so.</p></li>
		<li><p>The researchers have answered any questions that I had about the study and I am happy with the answers.</p></li>
		<li><p>I understand that being in this study is completely voluntary and I do not have to take part. My decision whether to be in the study will not affect my relationship with the researchers or anyone else at the University of Sydney now or in the future.</p></li>
		<li><p>I understand that I can withdraw from the study at any time.</p></li>
		<li><p>I understand that I may leave the game at any time if I do not wish to continue. I also understand that it will not be possible to withdraw my past actions once the group has started as it is a result of sum of all the actions.</p></li>
		<li><p>I understand that personal information about me that is collected over the course of this project will be stored securely and will only be used for purposes that I have agreed to. I understand that information about me will only be told to others with my permission, except as required by law.</p></li>
		<li><p>I understand that the results of this study may be published, but these publications will not contain my name or any identifiable information about me.</p></li>
		<li><p>I consent being identified during the game by the username I choose in the registration.</p> </li>
		<li><p>I consent to:</p></li>
	</ul>
	<div class="form-group">
		<label class="col-lg-6 control-label text-right radio">Being contacted about future studies : '. $formMap[ $consentFormObj['contacted']] .'</label>
	</div>
	<br/>
	<div class=""><p>Would you like to receive feedback about the overall results of this study? : '. $formMap[ $consentFormObj['feedback']] .'</p></div>
	
	<div class="">
		<div class=""><p>If you answered YES, please indicate your preferred email:</p></div>
		<div class="form-group">
			<label for="inputEmail" class="col-lg-2 control-label">Email : '. $consentFormObj['pref_email'] .'</label>
		</div>
	</div>
	<br/>
	<br/>
	<div class="row">
		<div class="col-lg-offset-2">
			I received the participant information statement : '. $formMap[ $consentFormObj['partiInfo']] .' <br/>
			I accept that results of this test will be used in future research : '. $formMap[ $consentFormObj['resultUsed']] .'<br/>
			I accept the terms and conditions : '. $formMap[ $consentFormObj['accept']] .'<br/>
		</div>
	</div>';
		
		$msg = $msg . '<br/><br/><h2><b>**NOTE: Please forward this email back to me if you agreed to proceed with the consent form confirmation.</b></h2><br/><br/>Sincerely,<br/>' .$frN. '<br/><br/>';
		
		$cnt_array = array('fr'=>$fr, 'to'=>$to, 'subject'=>$sbj, 'message'=>$msg, 'frName'=>$frN);
		$cnt_json = json_encode($cnt_array);
		
		$this->post_json($qMAPI, $cnt_json);
	}
	
	function login($f3){
		if ($f3->exists('POST.cred') && $f3->exists('POST.password')){
			$cred = $f3->get('POST.cred');
			
			$result = $this->users->getByArray(array('username=? or email=?', $cred, $cred));
			//echo '['.$result[0]['password'].']';
			$crypt = \Bcrypt::instance();
			$validPass = $crypt->verify($f3->get('POST.password'), $result[0]['password']);
			
			if ($validPass){
				$f3->set('SESSION.user', $result[0]['username']);
				$f3->set('SESSION.userId', $result[0]['id']);
				
				$f3->reroute('/gamespace');
				
			}else{
				$f3->set('err_message', 'Invalid access credential. please try again!');
			}
		}
		$f3->set('inc', 'login.htm');
	}
	
	function logout($f3){
		if ($f3->exists('SESSION.userGameId')){
			$f3->set('POST.status', 'quit');
			$this->user_game->edit('id', $f3->get('SESSION.userGameId'));
		}
		$f3->clear('SESSION');
		$f3->clear('COOKIE');
		
		$f3->set('SESSION.user',null);
		
		$f3->reroute('/');
	}
	
	function register($f3){
		if ($f3->exists('POST.username') && 
			$f3->exists('POST.email') && 
			$f3->exists('POST.password')
		){
			$result = $this->users->getByArray(array('username=? or email=?', $f3->get('POST.username'), $f3->get('POST.email')));
			
			if (count($result) > 0){
				$f3->set('err_message', 'Your username or email has been taken, please try again.');
				
			}else{
				$crypt = \Bcrypt::instance();
				
				$f3->set('POST.password', $crypt->hash($f3->get('POST.password')));
				
				$this->users->add();

				$f3->set('SESSION.user', $f3->get('POST.username'));
				$f3->set('SESSION.userId', $this->users->id);
				
				$f3->reroute('/gameList');
			}
		}
		
		$f3->set('inc', 'register.htm');
	}
	private function saveConsentForm($f3){
		if (
			$f3->exists('POST.name') && 
			$f3->exists('POST.email') && 
			//$f3->exists('POST.over18') && 
			//$f3->exists('POST.tel') && 
			//$f3->exists('POST.address') && 
			//$f3->exists('POST.video') && 
			//$f3->exists('POST.audio') && 
			//$f3->exists('POST.photographs') && 
			//$f3->exists('POST.transcripts') && 
			$f3->exists('POST.contacted') && 
			$f3->exists('POST.feedback') && 
			$f3->exists('POST.partiInfo') && 
			$f3->exists('POST.accept') && 
			//$f3->exists('POST.pref_address') && 
			//$f3->exists('POST.pref_tel') && 
			$f3->exists('POST.pref_email')
		){
			$this->user_consent->add();
			return true;
		}else{
			return false;
		}
	}
	
	function getRawData($f3){
		
		//$result = $this->user_game->getByArray(array('gameId=?', $f3->get('PARAMS.gameId')));
		$result = $this->user_game->getByArrayPage(array('gameId=?', $f3->get('PARAMS.gameId')), array('order'=>'roundNo, username'));
		
		$this->games->getById('id', $f3->get('PARAMS.gameId'));
		
		$this->games->copyTo('POST');
		
		//GET ALL PLAYERS USERNAME - START
		$len = count($result);
		$actionResult = [];
		$players = [];
		$numOfPlayers =  $f3->get('POST.numOfPlayers');
		for ($x=0; $x<=$len; $x++){
			if (count($players) < $numOfPlayers){
				array_push($players, $result[$x]['username']);
				
			}else{
				break;
			}
		}
		//GET ALL PLAYERS USERNAME - END
		
		//ARRANGE PLAYER AND ACTION TO CORRESPONDENT ROUND INTO MATRIX FORMAT - START
		$processingRound = 1;
		$playerPerRowArray = [];
		$playerRecordArray = [];
		
		for ($x=0; $x<=$len; $x++){
			$currentRound = $result[$x]['roundNo'];
			
			if($currentRound == 0){
				continue;
			}
			
			if ($processingRound != $currentRound ){
				
				$playerRecordArray[$processingRound] = $playerPerRowArray;
				$playerPerRowArray = [];
				
				//$processingRound = $currentRound;
				$processingRound ++;
				
				while($processingRound < $currentRound){
					$playerRecordArray[$processingRound] = [];
					$processingRound ++;
					
				}
			}
			
			$action = $result[$x]['action'] == null ? 0 : $result[$x]['action'];
			
			$playerPerRowArray[$result[$x]['username']] = $action;
			
			if($len == ($x+1)){
				$playerRecordArray[$processingRound] = $playerPerRowArray;
			}
		}
		//ARRANGE PLAYER AND ACTION TO CORRESPONDENT ROUND INTO MATTRIX FORMAT - END
		
		$f3->set('result', $result);  
		$f3->set('players', $players);  
		$f3->set('actionResult', $playerRecordArray);  
		$f3->set('inc', 'rawData.htm');
	}
	
	function priceRoundJson($f3){
		$result = $this->user_game->getByArrayPage(array('gameId=?', $f3->get('PARAMS.gameId')), array('order'=>'roundNo'));
		$game = $this->games->getById('id', $f3->get('PARAMS.gameId'));
		
		$roundNo = 0;
		$roundNoArray = [];
		$priceArray = [];

		array_push($roundNoArray, 0);
		array_push($priceArray, $this->games->pricePerShare);

		foreach($result as $data){
			if ($roundNo != $data->roundNo){
				$roundNo = $data->roundNo;
				array_push($roundNoArray, $data->roundNo);
				array_push($priceArray, round($data->pricePerShare, 2));
				
			}else{
				continue;
			}
		}
		
		echo json_encode(array($roundNoArray, array($priceArray)));
		die();
	}
	
	function capitalMCountPlayerJson($f3){
		$result = $this->user_game->getByArrayPage(array('gameId=?', $f3->get('PARAMS.gameId')), array('order'=>'roundNo desc'));
		
		$roundNo = 0;
		$capitalArray = [];
		$MCountArray = [];
		$playerArray = [];
		
		foreach($result as $data){
				
			if ($roundNo == 0 || $roundNo == $data->roundNo){
				$roundNo = $data->roundNo;
				array_push($playerArray, $data->username);
				array_push($capitalArray, $data->capital);
				array_push($MCountArray, $data->minorityCount);
				
			}else{
				break;
			}
		}
		
		echo json_encode(array($playerArray, array($capitalArray, $MCountArray)));
		die();
	}
	
	function graph($f3){
		$f3->set('inc', 'analysisData.htm');
	}
	
	function playerRank($f3){
		
		$result = $this->user_game->getByArrayPage(array('gameId=?', $f3->get('PARAMS.hackGameId')), array('order'=>'roundNo desc'));
			//$result = $this->user_game->getByArrayPage(array('gameId=?', $f3->get('SESSION.gameId')), array('order'=>'roundNo desc'));

		$playersName = array_map(function($item){
			if ($item['roundNo'] == 0){
				return $item['username'];
			}
		}, $result);

		$rank = [];
		$getLastGameResult = true;
		$lastPricePerShare = 0;
		$lastRoundNo = 0;
		
		foreach($playersName as $name){
			foreach($result as $row){
				
				if ($getLastGameResult){
					$getLastGameResult = false;
					$lastPricePerShare = $row['pricePerShare'];
					$lastRoundNo = $row['roundNo'];
				}
				
				if ($row['username'] == $name){
					if ($row['roundNo'] == $lastRoundNo){
						//$rank[] = $row;
						$rank[] = array('capital'=>number_format($row['capital'], 2), 'minorityCount'=>$row['minorityCount'], 'username'=>$row['username']);
						break;
					
					}else{
						$capital = $this->getCapitalWithoutAction($row['cash'], $lastPricePerShare, $row['shares']);
						
						$rank[] = array('capital'=>number_format($capital, 2), 'minorityCount'=>$row['minorityCount'], 'username'=>$row['username']);
						break;
					}
				}
			}
		}

		$f3->set('result', $rank);
		
		$f3->set('inc', 'ranks.htm');
	}
	
	private function getCapitalWithoutAction($cash, $newPricePerShare, $shares){
		
		return $cash + ($newPricePerShare * $shares);
	}
}