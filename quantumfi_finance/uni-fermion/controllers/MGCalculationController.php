<?php

Class MGCalculationController extends MGController{

	function calculatePriceAndSave($f3, $resultAllPlayerByGameIdRound){

		//GET THE PRICE PER SHARE
		$currentPricePerShare = (float)$f3->get('SESSION.pricePerShare');

		//GETTING ALL VARIABLES FROM THE DATABASE

		//get Number of Players
		$N = (int)$f3->get('SESSION.numOfPlayers');

		//Get the investment(action) of all players
		$X = [];

		for ($i=0; $i<$N; $i++){
			array_push($X, $resultAllPlayerByGameIdRound[$i]['action']); 
		}
		
	    $action = $f3->get('SESSION.action');

		$investment = $action * $currentPricePerShare;

	    //CALCULATIONS
		//UPDATE VOLUMN OF BUYERS AND SELLERS - START
		//calculating Attendance, A = numberBuyers - numberSellers
		$A = $this->getAttendance($N, $X);
		//UPDATE VOLUMN OF BUYERS AND SELLERS - END

		
		$roundNo = $resultAllPlayerByGameIdRound[0]->roundNo;
		//var_dump( $f3->get('SESSION.noises'));
		//echo $roundNo;
		$noises = $f3->get('SESSION.noises')[$roundNo-1];
		//echo '['.$noises.']';
		//UPDATING MARKET VARIABLES - START
		//Calculating the new price
		$newPricePerShare = $this->getPricePerShareAXS($N, $currentPricePerShare, $A, $noises);
		//UPDATING MARKET VARIABLES - END

		
		
		//UPDATING AGENT WEALTH - START
		$cash     = $f3->get('CONFIG.cash');
		$shares   = $f3->get('CONFIG.shares');
		$minorityCount = 0;
		
		//!getting the capital results of all sessions.
		if ($f3->exists('SESSION.cash')) $cash = $f3->get('SESSION.cash');
		if ($f3->exists('SESSION.capital')) $capital = $f3->get('SESSION.capital');
		if ($f3->exists('SESSION.shares')) $shares = $f3->get('SESSION.shares');
		if ($f3->exists('SESSION.minorityCount')) $minorityCount = $f3->get('SESSION.minorityCount');
	   
		//Calculating the new capital
		list($cash, $capital, $shares) = $this->setCashSharesCapital($newPricePerShare, $currentPricePerShare, $cash, $capital, $shares, $investment);
		//UPDATING AGENT WEALTH - END
		
		
		//calculating MINORITY ACTION
		$minorityAction = -$this->sign($A);


		//calculating MINORITY COUNT
		$minorityCount = $this->getMinorityCount($minorityAction, $minorityCount, $action);


		//POPULATE DATA INTO DATABASE
		$this->saveUserGameForRound(
			$roundNo,
			$newPricePerShare, 
			$minorityAction,
			$shares,
			$cash,
			$capital,
			$minorityCount,
			$f3
		);

	   

		//UPDATE THE PLAYER SESSION

		$f3->set('SESSION.pricePerShare', $newPricePerShare);
		$f3->set('SESSION.minorityCount', $minorityCount);
		$f3->set('SESSION.cash', $cash);
		$f3->set('SESSION.capital', $capital);
		$f3->set('SESSION.shares', $shares);
		//$f3->set('SESSION.x', abs($investment));
		$f3->set('SESSION.x', $newPricePerShare);
	}

   
	//GET MINORITY COUNT
	private function getMinorityCount($minorityAction, $minorityCount, $action){
		if ($action == $minorityAction){
			$minorityCount++;
		}
		return $minorityCount;
	}


	private function getAttendance($N, $X){
		for ($i=0; $i<$N; $i++){
			$A += $X[$i];
		}
		return $A;
	}

   
	//GET PRICE PER SHARE
	private function getPricePerShare( $totalPlayers, $pricePerShare, $A){
		$denominator = $totalPlayers - $A;
		$numerator = $totalPlayers + $A;
		if ($denominator == 0){
			$denominator = 1;
		}else if ($numerator == 0){
			$numerator = 1;
		}
		$pricePerShare = $pricePerShare * ($numerator / ($denominator));
		return $pricePerShare;
	}

    private function getPricePerShareAXS( $totalPlayers, $pricePerShare, $A, $noises){
		
		//$pricePerShare = $pricePerShare * exp(($A - $noises) / ($pricePerShare*$totalPlayers));
		$pricePerShare = $pricePerShare * exp(($A) / ($pricePerShare*$totalPlayers));
		
		return $pricePerShare;
	}
   

	//GET CASH, SHARES, CAPITAL

	private function setCashSharesCapital($newPricePerShare, $currentPricePerShare, $cash, $capital, $shares, $investment){

		//GET CAPITAL
		$cash = $cash - $investment;
		$shares = $shares + $investment / $currentPricePerShare;
		$capital = $cash + ($newPricePerShare * $shares);
	   
		//return array('cash'=>$cash, 'capital'=>$capital, 'shares'=>$shares);
		return array($cash, $capital, $shares);
	}
	
	
	//POPULATE DATA INTO DATABASE
	private function saveUserGameForRound($roundNo, $pricePerShare, $minorityAction, $shares, $cash, $capital, $minorityCount, $f3){

		//RETRIEVE GAME DATA

		$this->user_game->getByArray(array(

			'gameId = ? and userId = ? and roundNo = ?',

			$f3->get('SESSION.gameId'),

			$f3->get('SESSION.userId'),

			$roundNo

		));

	   

		$this->user_game->copyTo('POST');

	   

		//SAVE NEW DATA

		$f3->set('POST.pricePerShare', $pricePerShare);//DISABLE THIS IF TRADED AT CURRENT PRICE

		$f3->set('POST.minorityAction', $minorityAction);

		$f3->set('POST.minorityCount', $minorityCount);

		$f3->set('POST.shares', $shares);

		$f3->set('POST.cash', $cash);

		$f3->set('POST.capital', $capital);

	   

		$this->user_game->edit('id', $this->user_game->id);

	}

   

	private function sign( $number ) {

		return ( $number > 0 ) ? 1 : ( ( $number < 0 ) ? -1 : 0 );

	}

}