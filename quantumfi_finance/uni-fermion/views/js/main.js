$(function() {
	//svar QF = QF || {};
	
	QF.forEach = function(array, action) {
	  for (var i = 0; i < array.length; i++)
		action(array[i]);
	}
	
    var xhr=(function fetch() {
        $.ajax({
            url:'gameData',
            type:'GET',
            success:function(data){
				
				obj = JSON.parse(data);
				
				var loadingImg = $('#loadingImgId').clone();
				loadingImg.attr('id', '').removeClass('hide')
				
				var playersC = $('#submittedPlayers');
				playersC.empty();
				//console.log('append loading img');
				loadingImg.appendTo(playersC);
				
				if (obj.isExpired){
					window.location.replace('logout');
				}
				if (obj.pricePerShare > 0){
					if (!obj.isFinal){
						
						$('#roundNo').html('Round ' + obj.roundNo);
						$('#buy, #sell, #hold').removeClass('disabled');						
					}
				
					$('input[name=price]').val(obj.pricePerShare);
					$('input[name=x]').val(obj.x);
					//$('#chart-area1').empty();
					var chartarea1 = $('#chartarea1')[0];
					var ctx = chartarea1.getContext("2d");
					
					// Store the current transformation matrix
					ctx.save();

					// Use the identity matrix while clearing the canvas
					ctx.setTransform(1, 0, 0, 1, 0, 0);
					ctx.clearRect(0, 0, chartarea1.width, chartarea1.height);

					// Restore the transform
					ctx.restore();
					
					QF.updateChart();
					//console.log('updateChart');
					
				}
				//else{
					
				$('#roundNo').html('Round ' + obj.roundNo);
				
				//Set Sumitted Action Players
				var playerArray = obj.unSubmittedPlayers;
				//console.log('process playerArray');
				
				$username = $('#usernameId').html().trim();
				if (playerArray.indexOf($username) != -1){//player unsubmitted action
					$('#buy, #sell, #hold').removeClass('disabled');
				}else{
					$('#buy, #sell, #hold').addClass('disabled');
				}
				
				if (playerArray.length > 0){
					playersC.empty();
				}
				
				for (var i = 0; i < playerArray.length; i++){
					var player = playerArray[i];
					
					//console.log('process '+player);
					var playerElem = $('#unSubmittedPlayersDummy').clone();
					
					playerElem.attr('id', player).html(player);
					
					playerElem.appendTo(playersC);
				}
					//console.log('appendTo playerArray');
					/*forEach(playerArray, function(player){
						var playerElem = $('#unSubmittedPlayersDummy').clone();
						playerElem
							.attr('id', player)
							.html(player)
						;
						console.log(player);
						playerElem.appendTo(playersC);
					});*/
				//}
				
				//$("#totalJoined").html(obj.totalJoined);
				$("#progressBar").addClass("hide");
				
				//if (obj.roundNo == 1){
					if (obj.totalUnJoined == 0){//Remove Alert
						$("#actionColumn").removeClass("hide");
						$("#unJoinedAlert").addClass("hide");
						
					}else{//Show Alert
						$("#totalUnJoined").html(obj.totalUnJoined);
						$("#actionColumn").addClass("hide");
						$("#unJoinedAlert").removeClass("hide");
						
					}
				//}
				
				if (obj.isFinal){
					$('#buy, #sell, #hold').addClass('disabled');
					$('#roundNo').html('END');
					$('#rankResult').removeClass('hide');
					$('#rawResult').removeClass('hide');
					
					$('#actionColumn').toggleClass('col-lg-3 col-lg-6');
					$('#waitingListColumn').addClass('hide');
					
				}
				//else{
				
					var 
						history = obj.history
						,histData = {}
						,tbody = $('#historyTbl tbody')
					;
					
					tbody.empty();
					
					for (var x=0; x<history.length; x++){
						
						$row = $('#historyTbl .dummyRow').clone();
						$row
							.attr('id', x)
							.removeClass('hide')
							.removeClass('dummyRow')
						;
						
						histData = history[x];
						
						$row.find('.rn').html(histData['roundNo']);
						$row.find('.ma').html(histData['minorityAction']);
						$row.find('.ya').html(histData['action']);
						$row.find('.pr').html(histData['pricePerShare']);
						$row.find('.ch').html(histData['cash']);
						$row.find('.sh').html(histData['shares']);
						$row.find('.cl').html(histData['capital']);
						$row.find('.mc').html(histData['countAct']);
						
						$row.appendTo(tbody);
					}

					if (!obj.isFinal){
						fetch();
					}
				//}
			}
        });
    })();
	
	function forEach(array, action) {
	  for (var i = 0; i < array.length; i++)
		action(array[i]);
	}
	
    $(window).on('beforeunload',function() {
        xhr.abort();
    });
	
	$('#fetch').bind('click', function(){
		fetch();
	});
	
	(QF.updateChart = function(){
		var $id=$('#gameId').html();
		QF.drawFromUrl('priceRound/'+$id, 'chartarea1');
	})();
});

//setInterval(refresh(), 9000);//Causing error for xhr.abort

function refresh(){
	location.reload();
}
var ua = navigator.userAgent, 
    event = (ua.match(/iPad/i)) ? "touchstart" : "click";

$('#refreshList').bind(event, function(){
	refresh();
});
$('#buy').bind(event, function(){
	submitAction('1');
});

$('#sell').bind(event, function(){
	submitAction('-1');
});

$('#hold').bind(event, function(){
	submitAction('0');
});

function submitAction(actionType){

	var mergedJson = JSON.stringify({
		action: actionType,
	});

	//console.log(mergedJson);
	$.post(
		'saveUserGame',
		mergedJson,
		successSubmitAction,
		'json'
	);
}

function successSubmitAction(data){
	$('#buy, #sell, #hold').addClass('disabled');
	
	$username = $('#usernameId').html().trim();
	$('#'+$username).addClass('hide');
}
