<?php

//This is some example codes of rolling dices
    
    var robotLayoutOn_=false;
    function robotLayoutChange() {
      if (robotLayoutOn_==false) robotLayoutOn();
      else robotLayoutOff();
    }
    var animating=false;
    function robotLayoutOn() {
      if (animating==false) {
        animating=true;
        $(".wrap").animate({
          width: "940px"
        },1000,function(){
          $(".c_right").fadeIn(600,function(){
            animating=false;
          });
        });
        $("#_st_automat").addClass('current_Bot');
        robotLayoutOn_=true;
      }
    }
    function robotLayoutOff() {
      if (animating==false) {
        if (bB_active==true) startAutomat();
        animating=true;
        $(".c_right").fadeOut(600,function(){
          $(".wrap").animate({
            width: "454px"
          },1000,function(){
            animating=false;
          });    
        });
        $("#_st_automat").removeClass('current_Bot');
        robotLayoutOn_=false;
      }
    }
    
    var under_over=0;
    function inverse() {
      if (under_over==0) {
        $("#under_over_txt").html('ROLL OVER TO WIN');
        under_over=1;
        recountUnderOver();
      }
      else {
        $("#under_over_txt").html('ROLL UNDER TO WIN');
        under_over=0;
        recountUnderOver();
      }
    }
    var under_over_bB=0;
    function inverse_bB() {
      if (under_over_bB==0) {
        $("#under_over_txt_bB").html('ROLL OVER TO WIN');
        under_over_bB=1;
        recountUnderOver_bB();
      }
      else {
        $("#under_over_txt_bB").html('ROLL UNDER TO WIN');
        under_over_bB=0;
        recountUnderOver_bB();
      }
    }
    function clickdouble() {
      $("#bt_wager").val((parseFloat($("#bt_wager").val())*2).toFixed(8)).change();      
    }
    function clickmax() {
      $("#bt_wager").val($(".balance").html()).change();
    }
    function maxProfit() {
      var newval=parseFloat($("#bt_wager").val())*(10000*(1-(<?php echo $settings['house_edge']; ?>/100)));
      $("#bt_profit").val(newval).change();    
    }
    var rolling=false;
    var lastBet=(Date.now()-<?php echo $settings['rolls_mintime']; ?>-1000);
    function place(wager,multiplier,bot) {
      if ((rolling==false && (Date.now())>(lastBet+<?php echo $settings['rolls_mintime']; ?>)) || bot==true) {
        rolling=true;
        lastBet=Date.now();
        $("#betBtn").html('ROLLING');
        if (bot!=true) _stats_content('my_bets');      
        $.ajax({
          'url': './content/ajax/place.php?w='+wager+'&m='+multiplier+'&hl='+under_over+'&_unique=<?php echo $unique; ?>',
          'dataType': "json",
          'success': function(data) {
            if (data['error']=='yes') {
              if (data['data']=='too_small') alert('Error: Your bet is too small.');
              if (data['data']=='invalid_bet') alert('Error: Your balance is too small for this bet.');
              if (data['data']=='invalid_m') alert('Error: Invalid multiplier.');
              if (data['data']=='invalid_hl') alert('Error: Invalid under/over specifier.');
              if (data['data']=='too_big_bet') alert('Error: Your bet is too big. At this time we only accept bets which are not bigger than '+data['under']+' <?php echo $settings['currency_sign']; ?>.');
            }
            else {
              var result=data['result'];
              var win_lose=data['win_lose'];
              if (win_lose==1) winCeremonial();
              else shameCeremonial();
            }
            $("#betBtn").html('ROLL DICE');
            rolling=false;
            
            if (bot==true && data['error']=='no') {
              setTimeout(function(){
                bB_profit-=wager;
                if (win_lose==1) bB_profit+=(wager*multiplier);
                bB_profit=Math.round(bB_profit*1000000000)/1000000000;
                placed(win_lose);            
              },<?php echo $settings['rolls_mintime_bB']; ?>);
              if (operateMode==0) {
                operateNum--;
                $("#botBtn").html('ROLLS LEFT TO OPERATE: '+operateNum);
              }
            }
            if (bot==true && data['error']=='yes') {
              startAutomat();
            }
          }
        }); 
      }   
    }