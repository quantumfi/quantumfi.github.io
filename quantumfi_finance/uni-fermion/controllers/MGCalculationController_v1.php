<?php
Class MGCalculationController extends MGController{
	
	function calculatePriceAndSave($f3, $resultAllPlayerByGameIdRound){
		$pricePerShare = (float)$f3->get('SESSION.pricePerShare');
		
		//GET TOTAL ATTENDANT, A
		$action = $this->getAttendant($resultAllPlayerByGameIdRound);
		
		//GET PRICE PER SHARE
		$newPricePerShare = $this->getPricePerShare($f3, $pricePerShare, $action);
		
		//GET CAPITAL
		//$this->initCashSharesCapital($f3, $pricePerShare);//CALCULATE BASED ON CURRENT PRICE
		$this->initCashSharesCapital($f3, $newPricePerShare);//CALCULATE BASED ON NEW PRICE
		$cash = $f3->get('SESSION.cash');
		$shares = $f3->get('SESSION.shares');
		$capital = $f3->get('SESSION.capital');
		
		//GET MINORITY ACTION
		$minorityAction = -$this->sign($action);
		
		//GET MINORITY COUNT
		$minorityCount = $this->getMinorityCount($f3, $minorityAction);
		
		//POPULATE DATA INTO DATABASE
		$this->saveUserGameForRound(
			$f3, 
			$resultAllPlayerByGameIdRound[0]->roundNo, 
			$newPricePerShare,  
			$minorityAction, 
			$shares, 
			$cash, 
			$capital,
			$minorityCount
		);
	}
	
	//GET MINORITY COUNT
	private function getMinorityCount($f3, $minorityAction){
		
		if ($f3->exists('SESSION.minorityCount') || $f3->get('SESSION.minorityCount') != null){
			if ($f3->get('SESSION.action') == $minorityAction){
				$mc = $f3->get('SESSION.minorityCount') + 1;
				
			}else{
				$mc = $f3->get('SESSION.minorityCount');
			}
			
		}else{
			if ($f3->get('SESSION.action') == $minorityAction){
				$mc = 1;
			}else{
				$mc = 0;
			}
		}
		
		$f3->set('SESSION.minorityCount', $mc);
		
		return $mc;
	}
	
	private function getAttendant($resultAllPlayerByGameIdRound){
		
		$len = count($resultAllPlayerByGameIdRound);
		
		for ($x=0; $x<$len; $x++){
			$action += $resultAllPlayerByGameIdRound[$x]['action'];
		}
		
		return $action;
	}
	
	//GET PRICE PER SHARE
	private function getPricePerShare($f3, $pricePerShare, $action){
		$totalPlayer = (float)$f3->get('SESSION.numOfPlayers');
		
		$denominator = $totalPlayer - $action;
		$numerator = $totalPlayer + $action;
		
		if ($denominator == 0){
			$denominator = 1;
			
		}else if ($numerator == 0){
			$numerator = 1;
		}
		
		$pricePerShare = $pricePerShare * ($numerator / ($denominator));
		
		$f3->set('SESSION.pricePerShare', $pricePerShare);
		
		return $pricePerShare;
	}
	
	//GET CASH, SHARES, CAPITAL
	private function initCashSharesCapital($f3, $pricePerShare){
		//SET ACCOUNT DEFAULT VALUE
		$cash = 100;
		$capital = 0;
		$xVal = 1;
		$shares = 10;
		
		if ($f3->exists('SESSION.cash')) $cash = $f3->get('SESSION.cash');
		
		if ($f3->exists('SESSION.capital')) $capital = $f3->get('SESSION.capital');
		
		if ($f3->exists('SESSION.shares')) $shares = $f3->get('SESSION.shares');

		//GET CAPITAL
		if ($f3->get('SESSION.action') == 1){
			$cash -= $xVal;
			$shares = $shares + $xVal / $pricePerShare;

		}else{
			$cash += $xVal;
			$shares = $shares - $xVal / $pricePerShare;
		}
		
		$capital = $cash + ($pricePerShare * $shares);
		
		 $f3->set('SESSION.cash', $cash);
		 $f3->set('SESSION.capital', $capital);
		 $f3->set('SESSION.shares', $shares);
		 
		//return $capital;
	}
	
	//POPULATE DATA INTO DATABASE
	private function saveUserGameForRound($f3, $roundNo, $pricePerShare, $minorityAction, $shares, $cash, $capital, $minorityCount){
		
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