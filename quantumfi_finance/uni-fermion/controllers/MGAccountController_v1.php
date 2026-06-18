<?php
class MGAccountController extends MGController{
	
	function beforeroute($f3) {
		if (!$f3->exists('SESSION.user') || $f3->get('SESSION.user') == ''){
			$f3->reroute('/login');
		}
	}
	
	function gamespace($f3){
		//$this->games->getByArray(array('status=?', 'Waiting'));
		$result = $this->user_game->getByArray(array('gameId=? and roundNo=? and userId=?', $f3->get('SESSION.gameId'), $f3->get('SESSION.roundNo'), $f3->get('SESSION.userId')));
		//echo $this->db->log();
		if (count($result) > 0){
			$f3->set('disabled', 'disabled');
			
		}else{
			$f3->set('disabled', '');
		}
		
		if ($f3->get('SESSION.gameId') == null){
			$f3->reroute('/gameList');
			
		}
		
		if ($f3->get('SESSION.totalRound') <  $f3->get('SESSION.roundNo')){
			$f3->set('disabled', 'disabled');
			$f3->set('isFinal', 'END');
			$f3->set('SESSION.gameId', null);
		}
		
		$f3->set('inc', 'gamespace.htm');
		
	}
	
	/* AJAX get gameData
	
	1. calculate Price Per Share
	*/
	function gameData1($f3){
		
		$f3->until(function() {//MUST return false to keep looping
			//$ret = !(time()%10);
			//echo $ret;
			//return $ret;
			return false;
		});
		die();
	}
	
	function gameData($f3){
		$pName = $f3->get('PARAMS.pName');
		
		$result = $this->user_game->getByArray(array('gameId=? and roundNo=?', $f3->get('SESSION.gameId'), $f3->get('SESSION.roundNo')));
		$isFinal = 'false';
		
		$len = count($result);
			
		if ($f3->exists('SESSION.numOfPlayers') && $len == $f3->get('SESSION.numOfPlayers')){
			$pricePerShare = $this->calculatePricePerShare($f3, $result);

			$f3->set('SESSION.pricePerShare', $pricePerShare);
			$f3->set('SESSION.roundNo', $f3->get('SESSION.roundNo') + 1);
			
			
		}else{
			$pricePerShare = 0;
		}
		
		//GAME FINISH
		if ($f3->get('SESSION.totalRound') <  $f3->get('SESSION.roundNo')){
			$isFinal = 'true';
			$f3->set('SESSION.gameId', null);
			
			//CALCULATE MINORITY ACTION
			$minorityAction = 1;//DEFAULT MINORITY ACTION IS BUY
			$actionVal = 0;
			
			for ($x=0; $x<$len; $x++){
				$actionVal += $result[$len]['action'];
			}
			
			if ($actionVal > 0){
				$minorityAction = -1;//SET TO SELL IF BUY > SELL
			}
			
			
			//$this->user_game->reset();			
			$this->user_game->getByArray(array(
				'gameId = ? and userId = ? and roundNo = ?', 
				$f3->get('SESSION.gameId'), 
				$f3->get('SESSION.userId'), 
				$f3->get('SESSION.roundNo')
			));
			
			$this->user_game->copyTo('POST');
			$f3->set('POST.minorityAction', $minorityAction);
			
			$this->user_game->edit('id', $this->user_game->id);
		}
		
		echo json_encode(array(
			'pricePerShare'=>$pricePerShare,
			'roundNo'=>$f3->get('SESSION.roundNo'),
			'isFinal'=>$isFinal
		));
		
		$f3->until(function() {//MUST return false to keep looping
			//return !(time()%10);
			return false;
		});
		
		die();
	}
	
	function getHistory($f3){
		$result = $this->getByGameIdUserIdRoundNo($f3, 'gameId=? and userId=? and roundNo != ?');//ROUND NO. NOT EQUAL
		
		$len = count($result);
		$historyArray = array();
		
		for ($x=0; $x<$len; $x++){
			$roundNo = $result[$len]['roundNo'];
			$action = $result[$len]['action'];
			
			$historyArray = array(
				
			);
		}
		
		if ($f3->get('PARAMS.format') == 'json'){
			echo json_encode(array(
				'history'=>$result,
			));
			die();
			
		}else{
			return $result;
		}
	}
	
	function getByGameIdUserIdRoundNo($f3, $queryStr){
		return $this->user_game->getByArray(array($queryStr, $f3->get('SESSION.gameId'), $f3->get('SESSION.userId'), $f3->get('SESSION.roundNo')));	
	}
	
	/*GET|POST gameList, route to gameList/game waiting room
	1. View game list to join
	2. update game status if enough player
	3. save game session after join
	4. update game currJoin after join
	5. return full if numOfPlayers <= currJoin
	*/
	function gameList($f3){
		
		if ($f3->exists('SESSION.gameId') && $f3->get('SESSION.gameId') != null){
			$f3->reroute('/gamespace');
		
		}else 
		if ($f3->exists('POST.gameId')){
			$gameId = $f3->get('POST.gameId');
			
			$this->games->getById('id',$gameId);
			
			$newJoin = $this->games->currJoin + 1;
			
			if ($newJoin > $this->games->numOfPlayers){
				$f3->set('err_message', 'The Game you have selected is unavailable!');
				
			}else{
				$f3->set('POST.currJoin', $newJoin);
				
				$this->games->edit('id', $gameId);
				
				//GAME SESSION MUST BE SAVED EVERY GAME CREATED AND JOINED
				$f3->set('SESSION.pricePerShare',$f3->get('POST.pricePerShare'));
				$f3->set('SESSION.totalRound', $f3->get('POST.totalRound'));
				$f3->set('SESSION.numOfPlayers', $f3->get('POST.numOfPlayers'));
				$f3->set('SESSION.gameId', $f3->get('POST.gameId'));
				$f3->set('SESSION.roundNo', 1);
				
				$f3->reroute('/gamespace');
				
			}
		}
		
		$result = $this->games->getByArray(array('status=?', 'Waiting'));
		$f3->set('results', $result);
		$f3->set('inc', 'gameList.htm');
	}
	
	/* AJAX POST saveUserGame, return Json: 'success, wait for other player'
	
	1. save player action
	2. save userId
	3. save roundNo
	*/
	function saveUserGame($f3){
		
		$postData = json_decode($f3->get('BODY'),true);
		
		$f3->set('POST.action', $postData["action"]);
		$f3->set('POST.gameId', $f3->get('SESSION.gameId'));
		$f3->set('POST.roundNo', $f3->get('SESSION.roundNo'));
		$f3->set('POST.userId', $f3->get('SESSION.userId'));
		
		$this->user_game->add();
		//echo $this->db->log();
		
		if ($this->user_game->id > 0){
			echo json_encode(array('message','Your action has been submitted, please wait for other players.'));
			
		}else{
			echo json_encode(array('err_message','Action failed, please contact administrator.'));
		}
		
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
			$f3->set('POST.currJoin', 1);
			
			$this->games->add();
			
			//GAME SESSION MUST BE SAVED EVERY GAME CREATED AND JOINED
			$f3->set('SESSION.pricePerShare',$f3->get('POST.pricePerShare'));
			$f3->set('SESSION.totalRound', $f3->get('POST.totalRound'));
			$f3->set('SESSION.numOfPlayers', $f3->get('POST.numOfPlayers'));
			$f3->set('SESSION.gameId', $this->games->id);
			$f3->set('SESSION.roundNo', 1);
			
			$f3->reroute('/gamespace');
		}
		
		$f3->set('inc', 'createGame.htm');
	}
	
	private function calculatePricePerShare($f3, $result){
		$pricePerShare = (float)$f3->get('SESSION.pricePerShare');
		$totalPlayer = (float)$f3->get('SESSION.numOfPlayers');
		
		$len = count($result);
		
		for ($x=0; $x<$len; $x++){
			$action += $result[$x]['action'];
			
		}
		
		$denominator = $totalPlayer - $action;
		$numerator = $totalPlayer + $action;
		
		if ($denominator == 0){
			$denominator = 1;
			
		}else if ($numerator == 0){
			$numerator = 1;
		}
		
		return $pricePerShare * ($numerator / ($denominator));
	}
}