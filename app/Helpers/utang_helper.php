<?php 
function getAbsDiffAmount($diff_amount){
    if($diff_amount<0){
        return $diff_amount*-1;
    }
    return $diff_amount;
}
