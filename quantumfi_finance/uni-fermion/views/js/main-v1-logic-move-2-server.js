$(function() {
    var xhr=(function fetch() {
        $.ajax({
            url:'/minority-game/gameData',
            type:'GET',
            success:function(data){
				obj = JSON.parse(data);
				console.log(obj.pricePerShare);
				if (obj.pricePerShare > 0){
					console.log(obj);
					
					if (obj.isFinal == 'true'){
						$('#roundNo').html('END');
						
					}else{
						
						$('#roundNo').html('Round ' + obj.roundNo);
						$('#buy, #sell').removeClass('disabled');
						
					}
				
					$('input[name=price]').val(obj.pricePerShare);
				}
				var pricePerShareTemp = obj.pricePerShare;
				
				var 
					history = obj.history
					,histData = {}
					,countAct = 0
					,cash = 100
					,shares = 10
					,xVal = 1
					,capital = 0
					,tbody = $('#historyTbl tbody')
				;
				
				tbody.empty();
				console.log('qqq');
				for (var x=0; x<history.length; x++){
					console.log(pricePerShareTemp);
					console.log('====');
					histData = history[x];
					
					console.log(histData);
					console.log(histData['roundNo']);
					console.log(histData['minorityAction']);
					console.log(histData['action']);
					console.log(histData['pricePerShare']);
					console.log('----------');
					if (histData['action'] == histData['minorityAction']){
						countAct++;
					}
					/*
					cash = cash - x
					share = share + x/price
					capital = cash + price * share
							= (cash - x) + price * (share + x/price)
							= cash - x + price*share + x 
							= cash + price*share
					*/
					
					if (histData['action'] == 'Buy'){
						cash -= xVal;
						shares = shares + xVal/parseFloat(histData['pricePerShare']);
						//shares = shares + xVal/obj.pricePerShareTemp;
						console.log(pricePerShareTemp);
						console.log(xVal/obj.pricePerShareTemp);
						console.log(shares);
						console.log('----');
						
					}else{
						cash += xVal;//101
						shares = shares - xVal/parseFloat(histData['pricePerShare']);//9
						//shares = shares - xVal/obj.pricePerShareTemp;//9
						//console.log(cash);
						console.log(shares);
						
					}
					
					
					capital = cash + (histData['pricePerShare'] * shares);
					//capital = cash + (obj.pricePerShareTemp * shares);
					
					console.log(capital);
					$row = $('#historyTbl .dummyRow').clone();
					$row
						.attr('id', x)
						.removeClass('hide')
						.removeClass('dummyRow')
					;
					$row.find('.rn').html(histData['roundNo']);
					$row.find('.ma').html(histData['minorityAction']);
					$row.find('.ya').html(histData['action']);
					$row.find('.pr').html(histData['pricePerShare']);
					//$row.find('.pr').html(obj.pricePerShareTemp);
					$row.find('.cl').html(capital);
					$row.find('.mc').html(countAct);
					
					
					console.log($row);
					$row.appendTo(tbody);
				}
				
				if (obj.isFinal != 'true'){
				  fetch();
				}
			}
        });
    })();
	
    $(window).on('beforeunload',function() {
        xhr.abort();
    });
	
	$('#fetch').bind('click', function(){
		fetch();
	});
});


$('#buy').bind('click', function(){
	submitAction('1');
});

$('#sell').bind('click', function(){
	submitAction('-1');
});

function submitAction(actionType){

	var mergedJson = JSON.stringify({
		action: actionType,
	});

	console.log(mergedJson);
	$.post(
		'/minority-game/saveUserGame',
		mergedJson,
		successSubmitAction,
		'json'
	);
}

function successSubmitAction(data){
	console.log(data);
	alert(data[1]);
	$('#buy, #sell').addClass('disabled');
}

function successGetGameData(data){
	if (data.pricePerShare > 0){
		console.log(data);
		$('input[name=price]').val(data.pricePerShare);
		$('#roundNo').html(data.roundNo);
		$('#buy, #sell').removeClass('disabled');
	}
	fetch();
}

/*$.ajax({
	url:'/minority-game/gameData',
	type:'GET',
	success:successGetGameData
});*/