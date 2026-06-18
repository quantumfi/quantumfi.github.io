<?php
//require_once 'ProbabilityDistribution/Noise.class.php';

class MGAccountController extends MGCalculationController{
	
	function beforeroute($f3) {
		if (!$f3->exists('SESSION.user') || $f3->get('SESSION.user') == ''){
			$f3->reroute('/login');
		}
	}
	
	private function continueGameAfterLogout($f3, $userId){
		//GET THE GAME ID OF THE LAST PLAYED - START
		$this->user_game->getByArrayPage(array('userId=? and roundNo=0', $userId), array('order'=>'gameId DESC', 'limit'=>1));
		$userGameId = $this->user_game->id;
		$gameId = $this->user_game->gameId;
		//GET THE GAME ID OF THE LAST PLAYED - END
		
		//GET USER GAME INFO ASSOCIATED WITH THE GAME ID - START
		$this->user_game->getByArrayPage(array('gameId=? and pricePerShare != 0', $gameId), array('order'=>'gameId DESC, roundNo DESC', 'limit'=>1));
		$userGameByGame = $this->user_game;
		//GET USER GAME INFO ASSOCIATED WITH THE GAME ID - END
		
		if ($gameId > 0){
			//CHECK IF THE GAME HAS ALREADY FINISHED - START
			$this->games->getById('id', $gameId);
			//echo '['.$this->games->status.']';
			//echo '['.($this->games->status == 'End' ).']';
			if ($this->games->status == 'End'){
				return false;
			}
			//CHECK IF THE GAME HAS ALREADY FINISHED - END
			
			//CHECK IF THE GAME WAS 2 HOURS OLD - START
			/*$timeFirst  = strtotime($this->games->created);
			$timeSecond = strtotime(date('Y-m-d H:i:s'));
			$twoHoursInSeconds = 2 * 60 * 60;
			
			if ($timeSecond - $timeFirst >= $twoHoursInSeconds){
				return false;
			}*/
			//CHECK IF THE GAME WAS 2 HOURS OLD - END
			
			//echo $this->db->log();
			//echo $this->games->pricePerShare;
			$f3->set('SESSION.initialPricePerShare',$this->games->pricePerShare);//game db
			$f3->set('SESSION.totalRound', $this->games->totalRound);//game db
			$f3->set('SESSION.numOfPlayers', $this->games->numOfPlayers);//game db
			$f3->set('SESSION.roundNo', $this->games->currRoundNo);//game db
			$f3->set('SESSION.noises', json_decode($this->games->noises));//game db

			$f3->set('SESSION.gameId', $gameId);//user game db
			$f3->set('SESSION.pricePerShare',$userGameByGame->pricePerShare);//user game db
			
			//To go to next round automatically
			$f3->set('SESSION.expiredTime', $this->getExpiredTime($f3));
			
			//To track if players has quit game or not
			$f3->set('SESSION.userGameId', $userGameId);
			
			$f3->set('POST.status', 'active');
			$this->user_game->edit('id', $userGameId);
			//echo $this->db->log();
			return true;
		}
		
		return false;
	}
	
	function gamespace($f3){
		$f3->set('disabled', '');
		$gameId = $f3->get('SESSION.gameId');
		
		if ($gameId == null){
			$isPlayer = $this->continueGameAfterLogout($f3, $f3->get('SESSION.userId'));
			
			if(!$isPlayer){
				$f3->reroute('/gameList');
			}
		}
		
		$roundNo = $f3->get('SESSION.roundNo');
		$numOfPlayers = $f3->get('SESSION.numOfPlayers');
		
		
		$resultByGameIdRoundNo = $this->user_game->getByArray(array('gameId=? and roundNo=?', $gameId, $roundNo));

		$submittedPlayerList = $this->getSubmittedActionPlayer($resultByGameIdRoundNo);
		
		foreach($submittedPlayerList as $username){
			if ($username == $f3->get('SESSION.user')){
				$f3->set('disabled', 'disabled');
				break;
			}
		}
		//$resultByGameIdRoundNo = $this->user_game->getByArray(array('gameId=? and roundNo=?', $gameId, $roundNo));
		//$len = count($resultByGameIdRoundNo);
		
		/*for ($i=0; $i<$len; $i++){
			if ($resultByGameIdRoundNo[$i]->userId == $f3->get('SESSION.userId')){
				$f3->set('disabled', 'disabled');
				break;
			}
		}*/
		
		if ($f3->get('SESSION.totalRound') <  $roundNo){
			$f3->set('disabled', 'disabled');
			$f3->set('isFinal', 'END');
		}
		
		$activeJoinedPlayers = $this->getActivePlayersAtRoundZero($gameId);
		
		//$f3->set('totalJoined', count($joinedPlayers));
		$f3->set('totalUnJoined', ($numOfPlayers - count($joinedPlayers)));
		$f3->set('history', $this->getHistory($f3));
		$f3->set('unSubmittedPlayers', $this->getUnSubmittedActionPlayers($submittedPlayerList, $activeJoinedPlayers));
		//$f3->set('unSubmittedPlayers', $this->getUnSubmittedActionPlayers($resultByGameIdRoundNo, $activeJoinedPlayers));
		
		$f3->set('inc', 'gamespace.htm');
		
	}
	
	private function waitingNoOfPlayersBeforeGameStart($f3, $gameId, $joinedPlayers){
		$numOfPlayers = $f3->get('SESSION.numOfPlayers');
		
		$joinedPlayersDifference = ($numOfPlayers - count($joinedPlayers));
		
		//Start the game if Joined players different is 0, means all players have joined the game. 
		//Check only at first round, no point checking at further than round 1.
		if ($joinedPlayersDifference == 0){
			$startTime =  date('Y-m-d H:i:s');
			
			$f3->set('SESSION.roundNo', 1);
			$f3->set('SESSION.startTime', $startTime);
			$f3->set('POST.startTime', $startTime);
			$f3->set('POST.status', 'Started');
			$f3->set('POST.id', $gameId);
			
			$this->games->edit('id', $gameId);
		}

		return $joinedPlayersDifference;
	}
	
	private function isExpiredAndUnsubmitted($f3, $unSubmitPlayers){		
		if(	$this->isExpired($f3) && 
			$this->isUnSubmitted($f3, $unSubmitPlayers)){

			$f3->set('SESSION.action', 0);
			return true;
		}
		
		return false;
	}
	
	//When ALL players had submitted action, going next round
	private function gotoNextRoundWhenAllActionSubmitted($f3, $unSubmitPlayers, $resultByGameIdRoundNo, $roundNo, $gameId){
		//if (count($resultByGameIdRoundNo) == count($joinedPlayers) && count($unSubmitPlayers) == 0){
		if ( count($unSubmitPlayers) == 0){
			$this->calculatePriceAndSave($f3, $resultByGameIdRoundNo);

			$newRoundNo = $roundNo + 1;
			$f3->set('SESSION.roundNo', $newRoundNo);
			$f3->set('SESSION.expiredTime', $this->getExpiredTime($f3));
			
			$this->games->getById('id', $gameId);
			$this->games->copyTo('POST');
			
			$f3->set('POST.currRoundNo', $newRoundNo);
			$f3->set('POST.id', $gameId);
			$this->games->edit('id', $gameId);

			return array($f3->get('SESSION.pricePerShare'), $newRoundNo);
			
		}else{
			return array(0, $roundNo);
		}
	}
	
	private function checkIsGameEnd($f3, $gameId, $newRoundNo){
		
		if ($f3->get('SESSION.totalRound') <  $newRoundNo){
			$f3->set('POST.status', 'End');
			$f3->set('POST.pricePerShare', $f3->get('SESSION.initialPricePerShare'));
			$f3->set('POST.id', $gameId);
			$this->games->edit('id', $gameId);
			return true;
		}
		
		return false;
	}
	
	/* AJAX get gameData
	
	1. calculate Price Per Share
	*/
	function gameData($f3){
		//$pName = $f3->get('PARAMS.pName');
		$roundNo = $f3->get('SESSION.roundNo');
		$newRoundNo = $f3->get('SESSION.roundNo');
		$gameId = $f3->get('SESSION.gameId');
		
		if ($roundNo > 0){
			//Get the difference of total Joined players
			$activeJoinedPlayers = $this->getActivePlayersAtRoundZero($gameId);
			
			$resultByGameIdRoundNo = $this->user_game->getByArray(array('gameId=? and roundNo=?', $gameId, $roundNo));
		
			$submittedPlayerList = $this->getSubmittedActionPlayer($resultByGameIdRoundNo);
			
			$unSubmitPlayers = $this->getUnSubmittedActionPlayers($submittedPlayerList, $activeJoinedPlayers);
			
			$isExpired = $this->isExpiredAndUnsubmitted($f3, $unSubmitPlayers);
			
			list($pricePerShare, $newRoundNo) = $this->gotoNextRoundWhenAllActionSubmitted($f3, $unSubmitPlayers, $resultByGameIdRoundNo, $roundNo, $gameId);
			
			$joinedPlayersDifference = 0;
			
		}else{
			$allJoinedPlayers = $this->getAllPlayersAtRoundZero($gameId);//Don't care if player status is active or quit
			
			$joinedPlayersDifference = $this->waitingNoOfPlayersBeforeGameStart($f3, $gameId, $allJoinedPlayers);
			
			$unSubmitPlayers = [];
			
			$pricePerShare = 0;
		}
		
		//echo $noises = $f3->get('SESSION.noises')[$roundNo];
		
		$f3->set('PARAMS.format', 'array');
			
		echo json_encode(array(
			'pricePerShare'=>round($pricePerShare, 2),
			//'x'=>round($f3->get('SESSION.x'), 2),
			'roundNo'=>$newRoundNo,
			'isFinal'=>$this->checkIsGameEnd($f3, $gameId, $newRoundNo),
			'isExpired'=>$isExpired,
			'totalUnJoined'=>$joinedPlayersDifference,
			'history'=>$this->getHistory($f3),
			'unSubmittedPlayers'=>$unSubmitPlayers
		));
		
		$f3->until(function() {//MUST return false to keep looping
			return !(time()%10);
		});
		
		die();
	}
	
	private function isUnSubmitted($f3, $unSubmitPlayers){
		return in_array($f3->get('SESSION.user'), $unSubmitPlayers);
	}
	
	private function isExpired($f3){
		return strtotime(date('Y-m-d H:i:s')) - $f3->get('SESSION.expiredTime') > 0;
	}
	
	private function getExpiredTime($f3){
		return strtotime(date('Y-m-d H:i:s')) + $f3->get('CONFIG.nextRoundInterval');
	}
	
	/*
	GET getHistory/@format
	*/
	function getHistory($f3){
		$result = $this->getByGameIdUserIdRoundNo($f3, 'gameId=? and userId=? and roundNo != ?');//ROUND NO. NOT EQUAL
		//$result = $this->getByGameIdUserIdRoundNo($f3, 'gameId=? and roundNo != ?');//ROUND NO. NOT EQUAL
		
		$len = count($result);
		
		$processed = array();
		$record = array();
		$countAct = 0;
		
		$roundNoMemory = -1;
		
		for ($x = $len-1; $x >= 0; $x--){
			/*$roundNoCurrent = $result[$x]->roundNo;
			
			if ($roundNoMemory != $roundNoCurrent){
				
				$roundNoMemory = $roundNoCurrent;*/
				
				$processed = array(
					'roundNo'=>$result[$x]->roundNo,
					'action'=>$this->toTerm($result[$x]->action),
					'minorityAction'=>$this->toTerm($result[$x]->minorityAction),
					'pricePerShare'=>round($result[$x]->pricePerShare, 2),
					'cash'=>round($result[$x]->cash, 2),
					'shares'=>round($result[$x]->shares, 2),
					'countAct'=>$result[$x]->minorityCount,
					'capital'=>round($result[$x]->capital, 2)
				);
				
				array_push ($record, $processed);
			//}
		}
		
		if ($f3->get('PARAMS.format') == 'json'){
			echo json_encode(array(
				'history'=>$record
			));
			die();
			
		}else{
			return $record;
		}
	}
	
	function getByGameIdUserIdRoundNo($f3, $queryStr){
		return $this->user_game->getByArray(array($queryStr, $f3->get('SESSION.gameId'), $f3->get('SESSION.userId'), $f3->get('SESSION.roundNo')));	
		//return $this->user_game->getByArray(array($queryStr, $f3->get('SESSION.gameId'), $f3->get('SESSION.roundNo')));	
	}
	
	function logoutGame($f3){
		$f3->set('SESSION.roundNo', $f3->get('SESSION.totalRound') + 100);
		
		$f3->reroute('/gameList');
	}
	
	function playerRank($f3){
		
		if ($f3->exists('PARAMS.hackGameId') && $f3->exists('PARAMS.hackTotalRound') ){
			$rank = $this->user_game->getByArray(array('gameId=? and roundNo=?', $f3->get('PARAMS.hackGameId'), $f3->get('PARAMS.hackTotalRound') ));
			
		}else{
			//$result = $this->user_game->getByArray(array('gameId=? and roundNo=?', $f3->get('SESSION.gameId'), $f3->get('SESSION.totalRound') ));
			$result = $this->user_game->getByArrayPage(array('gameId=?', $f3->get('SESSION.gameId')), array('order'=>'roundNo desc'));
			
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
							$rank[] = $row;
							break;
						
						}else{
							$capital = $this->getCapitalWithoutAction($row['cash'], $lastPricePerShare, $row['shares']);
							
							$rank[] = array('capital'=>$capital, 'minorityCount'=>$row['minorityCount'], 'username'=>$row['username']);
							break;
						}
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
	
	/*private function array_orderby()
	{
		$args = func_get_args();
		//var_dump( $args);
		$data = array_shift($args);
		foreach ($args as $n => $field) {
			if (is_string($field)) {
				$tmp = array();
				foreach ($data as $key => $row)
					$tmp[$key] = $row[$field];
				$args[$n] = $tmp;
				}
		}
		$args[] = &$data;
		call_user_func_array('array_multisort', $args);
		return array_pop($args);
	}*/

	/*GET|POST gameList, route to gameList/game waiting room
	1. View game list to join
	2. update game status if enough player
	3. save game session after join
	4. update game currJoin after join
	5. return full if numOfPlayers <= currJoin
	*/
	function gameList($f3){
		
		if ($f3->get('SESSION.totalRound') >  $f3->get('SESSION.roundNo')){
			$f3->reroute('/gamespace');
		
		}else if ($f3->exists('POST.gameId')){
			$gameId = $f3->get('POST.gameId');
			
			$this->games->getById('id',$gameId);
			
			$newJoin = $this->games->currJoin + 1;
			
			$result = $this->user_game->getByArray(array('username=? and gameId=?', $f3->get('SESSION.user'), $gameId));
			
			if (count($result) > 0){
				$f3->set('err_message', 'The same username has already joined the game!');
				
			}elseif ($newJoin > $this->games->numOfPlayers){
				$f3->set('err_message', 'The Game you have selected is unavailable!');
				
			}else{
				//UPDATE TOTAL JOIN INTO GAME
				$f3->set('POST.currJoin', $newJoin);
				
				$this->games->edit('id', $gameId);
				
				//SAVE USER AT ZERO ROUND INTO USER_GAME
				$this->saveNewlyJoinedPlayer($f3, $gameId);
				
				//GAME SESSION MUST BE SAVED EVERY GAME CREATED AND JOINED				
				$this->initialGameSession($f3, $gameId);
				
				$f3->reroute('/gamespace');
				
			}
		}
		
		$this->clearGameSession($f3);
		//$result = $this->games->getByArray(array('status=?', 'Waiting'));
		$result = $this->games->getByArrayPage(array('status=?', 'Waiting'), array('order'=>'id desc'));
		$f3->set('results', $result);
		$f3->set('inc', 'gameList.htm');
	}
	
	/* AJAX POST saveUserGame, return Json: 'success, wait for other player'
	
	1. save player action
	2. save userId
	3. save roundNo
	*/
	function saveUserGame($f3){
		
		if ($f3->exists('submitManually')){
			$f3->set('SESSION.action', 0); 
			
		}else{
			$postData = json_decode($f3->get('BODY'),true);
			$f3->set('SESSION.action', $postData["action"]);
			
		}
		
		$result = $this->user_game->getByArray(array(
			'gameId=? and roundNo=? and userId=?', 
			$f3->get('SESSION.gameId'), 
			$f3->get('SESSION.roundNo'), 
			$f3->get('SESSION.userId')
		));
		
		if (count($result) > 0){
			$msgArray = array('message','Your action has been submitted twice, please click the refresh button!');
			
		}else{
			
			$f3->set('POST.action', $f3->get('SESSION.action'));
			$f3->set('POST.gameId', $f3->get('SESSION.gameId'));
			$f3->set('POST.roundNo', $f3->get('SESSION.roundNo'));
			$f3->set('POST.userId', $f3->get('SESSION.userId'));
			$f3->set('POST.username', $f3->get('SESSION.user'));
			$f3->set('POST.pricePerShare', $f3->get('SESSION.pricePerShare'));
			
			$this->user_game->add();
			//echo $this->db->log();
			
			if ($this->user_game->id > 0){
				$msgArray = array('message','Your action has been submitted, please wait for other players.');
				
			}else{
				$msgArray = array('err_message','Action failed, please contact administrator.');
			}
		}
		if ($f3->exists('submitManually')){
			echo  $msgArray[1];
			
		}else{
			$this->echoNDie($msgArray);
		}
	}
	
	private function echoNDie($msgArray){
		echo json_encode($msgArray);
		die();
		
	}
	/* POST|GET saveGame. reroute to game space
	
	1. Create total round
	2. create gameUUID - cancelled
	3. create numOfPlayers
	*/
	function saveGame($f3){
		if ($f3->exists('SESSION.gameUUID')){
			$f3->reroute('/gamespace');
			
		}else if($f3->exists('POST.numOfPlayers')){
			$user = $f3->get('SESSION.user');
			
			//SAVE GAME INFORMATION
			$f3->set('POST.currJoin', 1);
			$f3->set('POST.createdBy', $user);
			$f3->set('POST.noises', json_encode($this->genNoises($f3->get('POST.totalRound'))));
			
			$this->games->add();
			
			//SAVE USER ZERO ROUND INTO USER_GAME
			$this->saveNewlyJoinedPlayer($f3, $this->games->id);
			
			//GAME SESSION MUST BE SAVED EVERY GAME CREATED AND JOINED
			$this->initialGameSession($f3, $this->games->id);
			
			//SEND EMAIL ALERT TO ADMIN ABOUT GAME CREATION
			$this->sendCreateGameAlert($f3, $this->games->id, $user);
			
			$f3->reroute('/gamespace');
		}
		
		$f3->set('inc', 'createGame.htm');
	}
	
	private function genNoises($totalRound){
		$noises = array();
		
		for ($x=1; $x<=$totalRound; $x++){
			//$noises[] = mt_rand(308, 412) * $this->nrand(0, 1) + mt_rand(-55, 55);
			$noises[] = round((mt_rand(308, 412) * $this->nrand(0, 1) + 0 ), 4);
		}
		return $noises;
	}
	
	private function getActivePlayersAtRoundZero($gameId){		
		return $this->getPlayersAtRoundZero(array('gameId=? and roundNo=0 and status="active"', $gameId ));
	}
	
	private function getAllPlayersAtRoundZero($gameId){	
		return $this->getPlayersAtRoundZero(array('gameId=? and roundNo=0', $gameId ));
	}
	
	private function getPlayersAtRoundZero($queryArray){
		$result = $this->user_game->getByArray($queryArray);
		
		$playersName = array_map(function($item){
			return $item['username'];
		}, $result);
	
		//$f3->set('SESSION.AllPlayersName', $playersName);
	
		return $playersName;
	}
	
	private function saveNewlyJoinedPlayer($f3, $gameId){
		$cash = $f3->get('CONFIG.cash');
		$shares = $f3->get('CONFIG.shares');
		$pricePerShare = $f3->get('POST.pricePerShare');
		
		$capital = $cash + ($pricePerShare * $shares);
		
		$f3->set('POST.gameId', $gameId);
		$f3->set('POST.roundNo', 0);
		$f3->set('POST.minorityCount', 0);
		$f3->set('POST.capital', $capital);
		$f3->set('POST.userId', $f3->get('SESSION.userId'));
		$f3->set('POST.username', $f3->get('SESSION.user'));
		$f3->set('POST.shares', $shares);
		$f3->set('POST.cash', $cash);
		$f3->set('POST.status', 'active');
		
		$this->user_game->add();
		
		//To track if players has quit game or not
		$f3->set('SESSION.userGameId', $this->user_game->id);
	}
	
	private function getSubmittedActionPlayer($resultByGameIdRoundNo){
		
		return array_map(function($item){
			return $item['username'];
		}, $resultByGameIdRoundNo);
	}
	
	//private function getSubmittedActionPlayers($resultByGameIdRoundNo){
	private function getUnSubmittedActionPlayers($submittedPlayerList, $allPlayers){
		if (count($submittedPlayerList) == 0){
			return $allPlayers;
		}
		/*
		if (count($resultByGameIdRoundNo) == 0){
			return $allPlayers;
		}
		$submittedPlayerList = array_map(function($item){
			return $item['username'];
		}, $resultByGameIdRoundNo);
		*/
		$unSubmittedPlayerList = [];
		
		$allPlayersCount = count($allPlayers);
		
		for ($i=0; $i<$allPlayersCount; $i++){
			$item = $allPlayers[$i];
			
			if (!in_array($item, $submittedPlayerList))
				array_push($unSubmittedPlayerList, $item);

		}
		
		return $unSubmittedPlayerList;
	}
	
	private function initialGameSession($f3, $gameId){
		$f3->set('SESSION.pricePerShare',$f3->get('POST.pricePerShare'));
		$f3->set('SESSION.initialPricePerShare',$f3->get('POST.pricePerShare'));
		$f3->set('SESSION.totalRound', $f3->get('POST.totalRound'));
		$f3->set('SESSION.numOfPlayers', $f3->get('POST.numOfPlayers'));
		$f3->set('SESSION.noises', json_decode($f3->get('POST.noises')));
		$f3->set('SESSION.gameId', $gameId);
		$f3->set('SESSION.roundNo', 0);
		$f3->set('SESSION.expiredTime', $this->getExpiredTime($f3));
	}
		
	private function toTerm($val){		
		if ($val == -1)
			return 'Sell';
		
		else if ($val == 1)
			return 'Buy';
		
		return 'None';
	}
	
	private function clearGameSession($f3){
		$f3->set('SESSION.cash',null);
		$f3->set('SESSION.capital',null);
		$f3->set('SESSION.shares',null);
		$f3->set('SESSION.action',null);
		$f3->set('SESSION.pricePerShare',null);
		$f3->set('SESSION.numOfPlayers',null);
		$f3->set('SESSION.minorityCount',null);
		$f3->set('SESSION.AllPlayersName',null);
		$f3->set('SESSION.x',null);
	}
	
	/* 
	* @param float  $mean, desired average 
	* @param number $sd, number of items in array 
	* @param number $min, minimum desired random number 
	* @param number $max, maximum desired random number 
	* @return array 
	*/ 
	private function array_distribute($mean,$sd,$min,$max){ 
		$result = array(); 
		$total_mean = intval($mean*$sd); 
		while($sd>1){ 
			$allowed_max = $total_mean - $sd - $min; 
			$allowed_min = intval($total_mean/$sd); 
			$random = mt_rand(max($min,$allowed_min),min($max,$allowed_max)); 
			$result[]=$random; 
			$sd--; 
			$total_mean-=$random; 
		} 
		$result[] = $total_mean; 
		return $result; 
	}
	
	private function nrand($mean, $sd){
		$x = mt_rand()/mt_getrandmax();
		$y = mt_rand()/mt_getrandmax();
		return sqrt(-2*log($x))*cos(2*pi()*$y)*$sd + $mean;
	}
}