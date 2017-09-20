<!--CSS for table result-->
<link rel="stylesheet" type="text/css" href="style.css">

<!--PHP code for calculator: 02-Dec-2016-->
<!--Go with: LoanCalc_TestData_25122016.xlsx for test data-->

<?php

function render_result_at_backend($asso_array, $title_table){

    echo "<p>". $title_table ."</p>";
    echo "<table class ='TFtable' border=1 cellspacing=0 cellpading=0\n>";
    for($i=0; $i<sizeof($asso_array); $i++) {
        echo "<tr>";
        foreach ($asso_array[$i] as $key=>$value) {
            if(($i!=0) && ($i!=(sizeof($asso_array)-1))){
                echo "<td>$value</td>";
            }elseif($i ==0){
                echo "<th>$value</th>";
            }elseif($i == (sizeof($asso_array)-1)){
                echo "<td class='totalline'>$value</td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Declare attributes related to schedule payment
$intervals_table = array(
    array('interval_name'=>'1 day', 'num_intervals_yearly'=>360, 'str_len_interval'=>'1D'),
    array('interval_name'=>'1 week', 'num_intervals_yearly'=>52, 'str_len_interval'=>'7D'),
    array('interval_name'=>'2 weeks', 'num_intervals_yearly'=>26, 'str_len_interval'=>'14D'),
    array('interval_name'=>'4 weeks', 'num_intervals_yearly'=>13, 'str_len_interval'=>'28D'),
    array('interval_name'=>'1 month', 'num_intervals_yearly'=>12, 'str_len_interval'=>'1M'),
    array('interval_name'=>'2 months', 'num_intervals_yearly'=>6, 'str_len_interval'=>'2M'),
    array('interval_name'=>'3 months', 'num_intervals_yearly'=>4, 'str_len_interval'=>'3M'),
    array('interval_name'=>'4 months', 'num_intervals_yearly'=>3, 'str_len_interval'=>'4M'),
    array('interval_name'=>'6 months', 'num_intervals_yearly'=>2, 'str_len_interval'=>'6M'),
    array('interval_name'=>'12 months', 'num_intervals_yearly'=>1, 'str_len_interval'=>'12M'));

//Update value in table
//2016-03-11: Use "&$var_name" ~ object reference (pass by reference)
function update_num_days_in_year(&$intervals_table, $interval_lookup, $new_num_interval){
    for($i = 0; $i<sizeof($intervals_table); $i++){
        $theinterval = $intervals_table[$i]['interval_name'];
        if ($theinterval == $interval_lookup){
            $intervals_table[$i]['num_intervals_yearly'] = $new_num_interval;
            return $intervals_table[$i]['num_intervals_yearly'];
        }
    }
}

//function for getting attributes  related to a given lookup_interval
//Return #num_compounding_interval for calculating $periodic_interest
//$str_len_interval for calculating the date for the next payment
function get_attributes_interval($lookup_interval, $intervals_table ){
    for($row = 0; $row <sizeof($intervals_table); $row++){
        $each_interval = $intervals_table[$row]['interval_name'];
        if ($each_interval == $lookup_interval){
            $num_intervals_yearly = $intervals_table[$row]['num_intervals_yearly'];
            $str_len_interval = $intervals_table[$row]['str_len_interval'];
            //echo "lookup interval= " . $lookup_interval . " , number of terms=" . $num_schedules . " , str next_date next = " . $str_len_interval;
            $result = array('Lookup_interval'=>$lookup_interval,'num_intervals_yearly' =>$num_intervals_yearly,
                            'str_len_interval' =>$str_len_interval);
            return $result;
        }
    }
    return null;
}

//http://php.net/manual/en/datetime.modify.php
//Using DateTime to overcome: Y38, '2013-01-31'+1M->March
//Input: $date: Date object, $month: 1,6, -1, -6
//Output: $date: Date object
function add_months($date,$months){

    $init=clone $date;
    $modifier=$months.' months';
    $back_modifier =-$months.' months';

    $date->modify($modifier);
    $back_to_init= clone $date;
    $back_to_init->modify($back_modifier);

    while($init->format('m')!=$back_to_init->format('m')){
        $date->modify('-1 day')    ;
        $back_to_init= clone $date;
        $back_to_init->modify($back_modifier);
    }
    return $init;
}

//Input: $date: Date object, $days: 1,7
//Output: $date: Date object
function add_days($date, $days){
    if($days >=0){
        $date ->add(new DateInterval('P'.$days.'D'));
    } else{
        $date ->sub(new DateInterval('P'.-$days.'D'));
    }
    return $date;
}
//Input $str_time_span: "1D", "12M", $start_date: '2016-01-31'
//Output: $next_date = '2016-02-18'
//Work both forward and backward with "1D" , "-1D"
function add_sub_days_months($start_date,$str_time_span){
    $next_date = new DateTime($start_date);
    //echo $next_date->format('Y-m-d'). "<br />";

    if (strchr($str_time_span,"D") =="D"){
        $pos = strpos($str_time_span,"D");
        $num_days = substr($str_time_span,0,$pos);
        add_days($next_date,$num_days);
        ///echo $num_days . "<br />";
        //$next_date ->add(new DateInterval('P'.$num_days.'D'));
        //echo $next_date->format('Y-m-d');
    }elseif (strchr($str_time_span,"M") =="M"){
        $pos = strpos($str_time_span,"M");
        $num_months = substr($str_time_span,0,$pos);
        //echo $num_months ."<br />";
        add_months($next_date, $num_months);
        //echo $next_date->format('Y-m-d');
    }else{
        echo "Check format of para: str_next_payment";
        return null;
    }
    return $next_date->format('Y-m-d');
}

//function for calculating difference of days between 2 dates
//Source: http://www.w3schools.com/php/func_date_date_diff.asp
//format inputs: $start_date, $end_date: '2016-10-26'
function num_days_diff ($start_date, $end_date){
    $date1=date_create($start_date);
    $date2=date_create($end_date);
    $diff=date_diff($date1,$date2);
    $num_days_diff = $diff->days;
    return $num_days_diff;
}

//
function calc_pmta ($loan_amt, $periodic_interest, $num_periodic_pmts){
    $periodic_pmt_amt =
        ($loan_amt*(pow(1+$periodic_interest,$num_periodic_pmts)))*$periodic_interest/(pow(1+$periodic_interest,$num_periodic_pmts) -1);
    return $periodic_pmt_amt;
}

function calc_apr_new($selected_pmt_interval,
                     $selected_comp_interval,$quoted_apr,$points,$intervals_table){

    $pmt_interval_att = get_attributes_interval($selected_pmt_interval, $intervals_table);
    $num_pmt_interval_p = $pmt_interval_att['num_intervals_yearly'];

    $comp_interval_att = get_attributes_interval($selected_comp_interval, $intervals_table);
    $num_comp_interval_c = $comp_interval_att['num_intervals_yearly'];
    //echo "np = " . $num_pmt_interval_p . ", mc= ". $num_comp_interval_c . " ARPq = " . $quoted_apr;

    //2.1 Consider the $points parameter -> to adjust APR before calculating APR new
    $apr_after_points = $quoted_apr - $points;

    //APRnew = [(1+ APR/c)^(c/p)-1]*p: => Effective Annual Rate (EAR) ==> APR actual
    $apr_new = (pow(1+$apr_after_points/$num_comp_interval_c,$num_comp_interval_c/$num_pmt_interval_p)-1)*$num_pmt_interval_p;
    $periodic_interest = $apr_new / $num_pmt_interval_p;//0.075

    return array('periodic_interest'=>$periodic_interest,'apr_new'=> $apr_new);

}//end of calc_newapr


function calc_pmt_periods_fixed_pmt($loan_amt,$loan_date,$first_pmt_due,$num_pmts,
                                    $str_len_pmt_interval, $num_days_in_year,
                                    $apr_new,$periodic_interest_rate,$pmta_amt,
                                    $has_interest_on_odd_days_interest,$pmt_of_points){

    //This 2 dim-asso array holds data of Periods.
    $periods = array();

    //for table heading
    array_push($periods,array("period"=>"Period",
            "date"=>"Date  ",
            "open_principal_bal"=>"Opening Principal Balance",
            "period_pmt"=>"Periodic Payment",
            "period_interest_pmt"=>"Interest Paid",
            "period_reduction_principal"=>"Principal Reduction",
            "end_principal_bal"=>"Ending Principal Balance")
    );
    //Declare variable total
    $total_actual_pmt = 0;
    $total_interest_pmt = 0;
    $total_reduction_principal =0;

    //Declare the Initial values for the first row data
    $period_date = $loan_date;
    $open_principal_bal = $loan_amt; //start with loan_amt
    $period_interest_pmt = 0;
    $period_reduction_principal = 0;
    $period_pmt = 0;
    $end_principal_bal = $open_principal_bal - $period_reduction_principal;

    //for the Approval date
    array_push($periods,array("period"=>'Approval:',
            "date"=>$period_date,
            "open_principal_bal"=>number_format($open_principal_bal, 2),
            "period_pmt"=>number_format($period_pmt, 2),
            "period_interest_pmt"=>number_format($period_interest_pmt, 2),
            "period_reduction_principal"=>number_format($period_reduction_principal, 2),
            "end_principal_bal"=>number_format($end_principal_bal, 2))
    );

    //Odd-days (Loan date-> P0): Only affect on the first payment
    $p0_date = add_sub_days_months($first_pmt_due, "-" . $str_len_pmt_interval);//note: "-" subtract

    if($loan_date <=$p0_date) {

        //Handle odd days interest on loan_amt for (Loan_date->P0)
        $odd_days = num_days_diff($loan_date, $p0_date);//note: function always return >0
        $odd_days_interest = ($loan_amt * $apr_new / $num_days_in_year) * $odd_days;
        $interest_on_odd_days_interest_p0_p1 = $odd_days_interest*$periodic_interest_rate;

        //Period 1:
        $period_date = $first_pmt_due;
        $open_principal_bal = $end_principal_bal;
        $interest_on_loan_amt_p0_p1 = $open_principal_bal*$periodic_interest_rate;


        if($has_interest_on_odd_days_interest =="No"){
            $period_interest_pmt = $odd_days_interest+
                $interest_on_loan_amt_p0_p1+$pmt_of_points;
        }elseif ($has_interest_on_odd_days_interest =="Yes"){
            $period_interest_pmt = $odd_days_interest+
                $interest_on_odd_days_interest_p0_p1+
                $interest_on_loan_amt_p0_p1+$pmt_of_points;
        }

        $period_reduction_principal = $pmta_amt - $interest_on_loan_amt_p0_p1;
        $period_pmt = $period_interest_pmt+ $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;

    }else{//loan_date > p0_date: short first period
        //Handle odd days interest on loan_amt for (P0->Loan_date)
        $odd_days = num_days_diff($loan_date, $p0_date);//note: function always return >0
        $odd_days_interest = ($loan_amt * $apr_new / $num_days_in_year) * $odd_days;

        //Period 1:
        $period_date = $first_pmt_due;
        $open_principal_bal = $end_principal_bal;

        $interest_on_loan_amt_p0_p1 = $open_principal_bal*$periodic_interest_rate;
        $period_interest_pmt = $interest_on_loan_amt_p0_p1 - $odd_days_interest+$pmt_of_points;//difference here

        $period_reduction_principal = $pmta_amt - $interest_on_loan_amt_p0_p1;
        $period_pmt = $period_interest_pmt+ $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;
    }

    // Calculate running total for Period 1
    $total_interest_pmt += $period_interest_pmt;
    $total_reduction_principal += $period_reduction_principal;
    $total_actual_pmt += $period_pmt;

    array_push($periods,array("period"=>1,
            "date"=>$period_date,
            "open_principal_bal"=>number_format($open_principal_bal, 2),
            "period_pmt"=>number_format($period_pmt, 2),
            "period_interest_pmt"=>number_format($period_interest_pmt, 2),
            "period_reduction_principal"=>number_format($period_reduction_principal, 2),
            "end_principal_bal"=>number_format($end_principal_bal, 2))
    );

    //Calculate for remaining payment date: P2->$num_pmts, and display
    for ($i = 2; $i < $num_pmts + 1; $i++) {
        $payment_date_prev = $period_date; //catch previous value
        $period_date = add_sub_days_months($payment_date_prev, $str_len_pmt_interval);
        $open_principal_bal = $end_principal_bal;

        //Calculate the interest each period
        $period_interest_pmt =$open_principal_bal*$periodic_interest_rate;

        $period_reduction_principal = $pmta_amt - $period_interest_pmt;
        $period_pmt = $pmta_amt;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;

        // Calculate running total
        $total_interest_pmt += $period_interest_pmt;
        $total_reduction_principal += $period_reduction_principal;
        $total_actual_pmt += $period_pmt;

        //for the period from 2->k inclusive
        array_push($periods,array("period"=>$i,
                  "date"=>$period_date,
                  "open_principal_bal"=>number_format($open_principal_bal, 2),
                  "period_pmt"=>number_format($period_pmt, 2),
                  "period_interest_pmt"=>number_format($period_interest_pmt, 2),
                  "period_reduction_principal"=>number_format($period_reduction_principal, 2),
                  "end_principal_bal"=>number_format($end_principal_bal, 2))
        );
    }//end of forloop for period 2 to k inclusive

    //for the total line in the table
    array_push($periods,array("period"=>"Total",
              "date"=>'',
              "open_principal_bal"=>'',
              "period_pmt"=>number_format($total_actual_pmt, 2),
              "period_interest_pmt"=>number_format($total_interest_pmt, 2),
              "period_reduction_principal"=>number_format($total_reduction_principal, 2),
              "end_principal_bal"=>''));

    return array("periods" =>$periods,
                "total_actual_pmt"=>$total_actual_pmt,
                "total_interest_pmt"=>$total_interest_pmt);
}//end case 'fixed_pmt'


function calc_pmt_periods_rule_78($loan_amt,$loan_date,$num_pmts,
                                  $total_interest_wo_odd_days_effect,
                                  $first_pmt_due,$str_len_pmt_interval,
                                  $apr_new,$num_days_in_year,$pmta_amt,
                                  $periodic_interest_rate,
                                  $has_interest_on_odd_days_interest,$pmt_of_points){

    //This 2 dim-asso array holds data of Periods.
    $periods =array();

    //for table heading
    array_push($periods,array("period"=>"Period",
            "date"=>"Date",
            "open_principal_bal"=>"Opening Principal Balance",
            "period_pmt"=>"Periodic Payment",
            "period_interest_pmt"=>"Interest Paid",
            "period_reduction_principal"=>"Principal Reduction",
            "end_principal_bal"=>"Ending Principal Balance")
    );
    //Declare variable total
    $total_actual_pmt = 0;
    $total_interest_pmt = 0;
    $total_reduction_principal =0;

    //Declare the Initial values for the first row data
    $period_date = $loan_date;
    $open_principal_bal = $loan_amt; //start with loan_amt
    $period_interest_pmt = 0;
    $period_reduction_principal = 0; //$period_pmt - $period_interest_pmt
    $period_pmt = 0; //start with: $plan_even_pay_method
    $end_principal_bal = $open_principal_bal - $period_reduction_principal;

    //for the period = Loan date
    array_push($periods,array("period"=>"Approval",
            "date"=>$period_date,
            "open_principal_bal"=>number_format($open_principal_bal, 2),
            "period_pmt"=>number_format($period_pmt, 2),
            "period_interest_pmt"=>number_format($period_interest_pmt, 2),
            "period_reduction_principal"=>number_format($period_reduction_principal, 2),
            "end_principal_bal"=>number_format($end_principal_bal, 2))
    );

    //Rule 78:
    //Interest each period: based on Remaining life of loan/sum_of_digits * Total Interest using fixed payment
    //It is NOT dependent on ending balance of principal
    //Interest on the first period for normal
    $period = 1;
    $sum_of_digits = $num_pmts * ($num_pmts + 1) / 2;
    $remaining_life = $num_pmts - $period + 1;
    $interest_on_loan_amt_p0_p1 = $total_interest_wo_odd_days_effect * $remaining_life / $sum_of_digits;

    //Odd-days (Loan date-> P0): Only affect on the first payment
    $p0_date = add_sub_days_months($first_pmt_due,"-".$str_len_pmt_interval);//note: "-" subtract

    if($loan_date <=$p0_date) {

        //Handle odd days interest on loan_amt for (Loan_date->P0)
        $odd_days = num_days_diff($loan_date, $p0_date);//note: function always return >0
        $odd_days_interest = ($loan_amt * $apr_new / $num_days_in_year) * $odd_days;
        $interest_on_odd_days_interest_p0_p1 = $odd_days_interest*$periodic_interest_rate;

        //Period 1:
        $period_date = $first_pmt_due;
        $open_principal_bal = $end_principal_bal;

        if($has_interest_on_odd_days_interest =="No"){
            $period_interest_pmt = $odd_days_interest+$interest_on_loan_amt_p0_p1+$pmt_of_points;
        }elseif ($has_interest_on_odd_days_interest =="Yes"){
            $period_interest_pmt = $odd_days_interest+$interest_on_odd_days_interest_p0_p1
                                    +$interest_on_loan_amt_p0_p1+$pmt_of_points;
        }

        $period_reduction_principal = $pmta_amt - $interest_on_loan_amt_p0_p1;
        $period_pmt = $period_interest_pmt + $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;

    }else{//loan_date > p0_date
        //Handle odd days interest on loan_amt for (P0->Loan_date)
        $odd_days = num_days_diff($loan_date, $p0_date);//note: function always return >0
        $odd_days_interest = ($loan_amt * $apr_new / $num_days_in_year) * $odd_days;

        //Period 1:
        $period_date = $first_pmt_due;
        $open_principal_bal = $end_principal_bal;

        $period_interest_pmt = $interest_on_loan_amt_p0_p1 - $odd_days_interest+$pmt_of_points;//deference here
        $period_reduction_principal = $pmta_amt - $interest_on_loan_amt_p0_p1;
        $period_pmt = $period_interest_pmt + $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;
    }

    // Calculate running total for Period 1
    $total_interest_pmt += $period_interest_pmt;
    $total_reduction_principal += $period_reduction_principal;
    $total_actual_pmt += $period_pmt;

    array_push($periods,array("period"=>1,
            "date"=>$period_date,
            "open_principal_bal"=>number_format($open_principal_bal, 2),
            "period_pmt"=>number_format($period_pmt, 2),
            "period_interest_pmt"=>number_format($period_interest_pmt, 2),
            "period_reduction_principal"=>number_format($period_reduction_principal, 2),
            "end_principal_bal"=>number_format($end_principal_bal, 2))
    );

    //Calculate for remaining payment date: P2->$num_pmts, and display
    for ($i = 2; $i < $num_pmts + 1; $i++) {
        $payment_date_prev = $period_date; //catch previous value
        $period_date = add_sub_days_months($payment_date_prev, $str_len_pmt_interval);

        $open_principal_bal = $end_principal_bal;

        //Calculate the interest each period
        $remaining_life = $num_pmts-$i+1;
        $period_interest_pmt = $total_interest_wo_odd_days_effect*$remaining_life/$sum_of_digits;

        $period_reduction_principal = $pmta_amt - $period_interest_pmt;
        $period_pmt = $pmta_amt;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;

        // Calculate running total
        $total_interest_pmt += $period_interest_pmt;
        $total_reduction_principal += $period_reduction_principal;
        $total_actual_pmt += $period_pmt;

        //for the period from 2->k inclusive
        array_push($periods,array("period"=>$i,
                "date"=>$period_date,
                "open_principal_bal"=>number_format($open_principal_bal, 2),
                "period_pmt"=>number_format($period_pmt, 2),
                "period_interest_pmt"=>number_format($period_interest_pmt, 2),
                "period_reduction_principal"=>number_format($period_reduction_principal, 2),
                "end_principal_bal"=>number_format($end_principal_bal, 2))
        );
    }//end of forloop for period 2 to k inclusive

    //for the total line in the table
    array_push($periods,array("period"=>"Total",
                "date"=>'',
                "open_principal_bal"=>'',
                "period_pmt"=>number_format($total_actual_pmt, 2),
                "period_interest_pmt"=>number_format($total_interest_pmt, 2),
                "period_reduction_principal"=>number_format($total_reduction_principal, 2),
                "end_principal_bal"=>''));

    return array("periods" =>$periods,
        "total_actual_pmt"=>$total_actual_pmt, "total_interest_pmt"=>$total_interest_pmt);

}//end calc_pmt_periods_rule_78


function calc_pmt_periods_fixed_principal($loan_amt,$loan_date,$num_pmts,
                                          $first_pmt_due,$str_len_pmt_interval,
                                          $apr_new,$periodic_interest_rate,
                                          $num_days_in_year,
                                          $has_interest_on_odd_days_interest,$pmt_of_points){

    //Periodic payment for method: Fixed-principal
    $period_fixed_principal = $loan_amt/$num_pmts;

    //This 2 dim-asso array holds data of Periods.
    $periods =array();
    //for table heading
    array_push($periods,array("period"=>"Period",
            "date"=>"Date",
            "open_principal_bal"=>"Opening Principal Balance",
            "period_pmt"=>"Periodic Payment",
            "period_interest_pmt"=>"Interest Paid",
            "period_reduction_principal"=>"Principal Reduction",
            "end_principal_bal"=>"Ending Principal Balance")
    );

    //Declare variable total
    $total_actual_pmt = 0;
    $total_interest_pmt = 0;
    $total_reduction_principal =0;

    //Declare the Initial values for the first row data
    $period_date = $loan_date;
    $open_principal_bal = $loan_amt; //start with loan_amt
    $period_interest_pmt = 0;
    $period_reduction_principal = 0;
    $period_pmt = 0;
    $end_principal_bal = $open_principal_bal - $period_reduction_principal;


    //for the period = Aprroval
    array_push($periods,array("period"=>"Approval",
            "date"=>$period_date,
            "open_principal_bal"=>number_format($open_principal_bal, 2),
            "period_pmt"=>number_format($period_pmt, 2),
            "period_interest_pmt"=>number_format($period_interest_pmt, 2),
            "period_reduction_principal"=>number_format($period_reduction_principal, 2),
            "end_principal_bal"=>number_format($end_principal_bal, 2))
    );

    //Odd-days (Loan date-> P0): Only affect on the first payment
    $p0_date = add_sub_days_months($first_pmt_due,"-".$str_len_pmt_interval);//note: "-" subtract

    if ($loan_date <=$p0_date) {

        //Handle odd days interest on loan_amt for (Loan_date->P0)
        $odd_days = num_days_diff($loan_date, $p0_date);//note: function always return >0
        $odd_days_interest = ($loan_amt * $apr_new / $num_days_in_year) * $odd_days;
        $interest_on_odd_days_interest_p0_p1 = $odd_days_interest*$periodic_interest_rate;

        //Period 1:
        $period_date = $first_pmt_due;
        $open_principal_bal = $end_principal_bal;
        $interest_on_loan_amt_p0_p1 = $open_principal_bal*$periodic_interest_rate;

        if($has_interest_on_odd_days_interest =="No"){
            $period_interest_pmt = $odd_days_interest
                                    +$interest_on_loan_amt_p0_p1+$pmt_of_points;
        }elseif ($has_interest_on_odd_days_interest =="Yes"){
            $period_interest_pmt = $odd_days_interest
                                    +$interest_on_odd_days_interest_p0_p1
                                    +$interest_on_loan_amt_p0_p1+$pmt_of_points;
        }

        $period_reduction_principal = $period_fixed_principal;
        $period_pmt = $period_interest_pmt + $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;

    }else{//loan_date > p0_date
        //Handle odd days interest on loan_amt for (Loan date->P1)
        $odd_days = num_days_diff($loan_date, $first_pmt_due);//note: function always return >0
        $odd_days_interest = ($loan_amt * $apr_new / $num_days_in_year) * $odd_days;//Difference here: Ld->P1

        //Period 1:
        $period_date = $first_pmt_due;
        $open_principal_bal = $end_principal_bal;

        $period_interest_pmt = $odd_days_interest+$pmt_of_points;

        $period_reduction_principal = $period_fixed_principal;
        $period_pmt = $period_interest_pmt + $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;
    }

    // Calculate running total for Period 1
    $total_interest_pmt += $period_interest_pmt;
    $total_reduction_principal += $period_reduction_principal;
    $total_actual_pmt += $period_pmt;

    array_push($periods,array("period"=>1,
            "date"=>$period_date,
            "open_principal_bal"=>number_format($open_principal_bal, 2),
            "period_pmt"=>number_format($period_pmt, 2),
            "period_interest_pmt"=>number_format($period_interest_pmt, 2),
            "period_reduction_principal"=>number_format($period_reduction_principal, 2),
            "end_principal_bal"=>number_format($end_principal_bal, 2))
    );


    //Calculate for remaining payment date, and display
    for ($i = 2; $i < $num_pmts + 1; $i++) {
        $payment_date_prev = $period_date; //catch previous value
        $period_date = add_sub_days_months($payment_date_prev, $str_len_pmt_interval);
        $open_principal_bal = $end_principal_bal;

        $period_interest_pmt = $open_principal_bal*$periodic_interest_rate;
        $period_reduction_principal = $period_fixed_principal;//fixed_principal over loan life

        $period_pmt = $period_interest_pmt + $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;

        // Calculate running total
        $total_interest_pmt += $period_interest_pmt;
        $total_reduction_principal += $period_reduction_principal;
        $total_actual_pmt += $period_pmt;

        //for the period from 2->k inclusive
        array_push($periods,array("period"=>$i,
                "date"=>$period_date,
                "open_principal_bal"=>number_format($open_principal_bal, 2),
                "period_pmt"=>number_format($period_pmt, 2),
                "period_interest_pmt"=>number_format($period_interest_pmt, 2),
                "period_reduction_principal"=>number_format($period_reduction_principal, 2),
                "end_principal_bal"=>number_format($end_principal_bal, 2))
        );
    }//end of forloop for period 2 to k inclusive

    //for the total line in the table
    array_push($periods,array("period"=>"Total",
            "date"=>'',
            "open_principal_bal"=>'',
            "period_pmt"=>number_format($total_actual_pmt, 2),
            "period_interest_pmt"=>number_format($total_interest_pmt, 2),
            "period_reduction_principal"=>number_format($total_reduction_principal, 2),
            "end_principal_bal"=>'')
    );
    return array("periods" =>$periods,
                "total_actual_pmt"=>$total_actual_pmt,
                "total_interest_pmt"=>$total_interest_pmt);
}//end calc_pmt_periods_fixed_principal



function calc_pmt_periods_interest_only($loan_amt,$loan_date,$first_pmt_due,
                                        $str_len_pmt_interval,$apr_new,
                                        $periodic_interest_rate,$num_pmts,
                                        $num_days_in_year,
                                        $has_interest_on_odd_days_interest,
                                        $pmt_of_points){
    //This 2 dim-asso array holds data of Periods.
    $periods =array();

    //for table heading
    array_push($periods,array("period"=>"Period",
            "date"=>"Date",
            "open_principal_bal"=>"Opening Principal Balance",
            "period_pmt"=>"Periodic Payment",
            "period_interest_pmt"=>"Interest Paid",
            "period_reduction_principal"=>"Principal Reduction",
            "end_principal_bal"=>"Ending Principal Balance")
    );

    //Declare variable total
    $total_actual_pmt = 0;
    $total_interest_pmt = 0;
    $total_reduction_principal =0;

    //Declare the Initial values for the first row data
    $period_date = $loan_date;
    $open_principal_bal = $loan_amt; //start with loan_amt
    $period_interest_pmt = 0;
    $period_reduction_principal = 0;
    $period_pmt = 0;
    $end_principal_bal = $open_principal_bal - $period_reduction_principal;


    //for the period = Aprroval
    array_push($periods,array("period"=>"Approval",
            "date"=>$period_date,
            "open_principal_bal"=>number_format($open_principal_bal, 2),
            "period_pmt"=>number_format($period_pmt, 2),
            "period_interest_pmt"=>number_format($period_interest_pmt, 2),
            "period_reduction_principal"=>number_format($period_reduction_principal, 2),
            "end_principal_bal"=>number_format($end_principal_bal, 2))
    );


    //Odd-days (Loan date-> P0): Only affect on the first payment
    $p0_date = add_sub_days_months($first_pmt_due,"-".$str_len_pmt_interval);//note: "-" subtract

    if($loan_date <= $p0_date) {

        //Handle odd days interest on loan_amt for (Loan_date->P0)
        $odd_days = num_days_diff($loan_date, $p0_date);//note: function always return >0
        $odd_days_interest = ($loan_amt * $apr_new / $num_days_in_year) * $odd_days;
        $interest_on_odd_days_interest_p0_p1 = $odd_days_interest*$periodic_interest_rate;

        //Period 1:
        $period_date = $first_pmt_due;
        $open_principal_bal = $end_principal_bal;
        $interest_on_loan_amt_p0_p1 = $open_principal_bal*$periodic_interest_rate;


        if($has_interest_on_odd_days_interest =="No"){
            $period_interest_pmt = $odd_days_interest
                +$interest_on_loan_amt_p0_p1+$pmt_of_points;
        }elseif ($has_interest_on_odd_days_interest =="Yes"){
            $period_interest_pmt = $odd_days_interest
                +$interest_on_odd_days_interest_p0_p1
                +$interest_on_loan_amt_p0_p1+$pmt_of_points;
        }

        if($num_pmts== 1) {
            $period_reduction_principal = $loan_amt;
        }else{
            $period_reduction_principal = 0;
        }

        $period_pmt = $period_interest_pmt + $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;

    }else{//loan_date > p0_date
        //Handle odd days interest on loan_amt for (Loan_date->P1)
        $odd_days = num_days_diff($loan_date, $first_pmt_due);//note: function always return >0
        $odd_days_interest = ($loan_amt * $apr_new / $num_days_in_year) * $odd_days;//difference here

        //Period 1:
        $period_date = $first_pmt_due;
        $open_principal_bal = $end_principal_bal;

        $period_interest_pmt = $odd_days_interest+$pmt_of_points;
        if($num_pmts== 1) {
            $period_reduction_principal = $loan_amt;
        }else{
            $period_reduction_principal = 0;
        }
        $period_pmt = $period_interest_pmt + $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;
    }

    // Calculate running total for Period 1:
    $total_interest_pmt += $period_interest_pmt;
    $total_reduction_principal += $period_reduction_principal;
    $total_actual_pmt += $period_pmt;

    array_push($periods, array("period" => 1,
            "date" => $period_date,
            "open_principal_bal" => number_format($open_principal_bal, 2),
            "period_pmt" => number_format($period_pmt, 2),
            "period_interest_pmt" => number_format($period_interest_pmt, 2),
            "period_reduction_principal" => number_format($period_reduction_principal, 2),
            "end_principal_bal" => number_format($end_principal_bal, 2))
    );

    //Calculate for remaining payment date, and display
    for ($i = 2; $i < $num_pmts + 1; $i++) {
        $payment_date_prev = $period_date; //catch previous value
        $period_date = add_sub_days_months($payment_date_prev, $str_len_pmt_interval);

        $open_principal_bal = $end_principal_bal;
        $period_interest_pmt =$open_principal_bal*$periodic_interest_rate;
        if ($i == $num_pmts) {
            $period_reduction_principal = $open_principal_bal;//to clear principal balance
        } else {
            $period_reduction_principal = 0;//pay principal only on the last period
        }
        $period_pmt = $period_interest_pmt + $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;

        // Calculate running total
        $total_interest_pmt += $period_interest_pmt;
        $total_reduction_principal += $period_reduction_principal;
        $total_actual_pmt += $period_pmt;

        //for the period from 2->k inclusive
        array_push($periods,array("period"=>$i,
                "date"=>$period_date,
                "open_principal_bal"=>number_format($open_principal_bal, 2),
                "period_pmt"=>number_format($period_pmt, 2),
                "period_interest_pmt"=>number_format($period_interest_pmt, 2),
                "period_reduction_principal"=>number_format($period_reduction_principal, 2),
                "end_principal_bal"=>number_format($end_principal_bal, 2))
        );
    }//end of forloop for period 2 to k inclusive

    //for the total line in the table
    array_push($periods,array("period"=>"Total",
            "date"=>'',
            "open_principal_bal"=>'',
            "period_pmt"=>number_format($total_actual_pmt, 2),
            "period_interest_pmt"=>number_format($total_interest_pmt, 2),
            "period_reduction_principal"=>number_format($total_reduction_principal, 2),
            "end_principal_bal"=>'')
    );
    return array("periods" =>$periods,"total_actual_pmt"=>$total_actual_pmt,
                "total_interest_pmt"=>$total_interest_pmt);

}//end calc_pmt_periods_interest_only

function calc_pmt_periods_no_interest($loan_amt,$loan_date,$first_pmt_due,
                                      $str_len_pmt_interval,$num_pmts,$pmt_of_points){

    $even_periodic_principal = $loan_amt/$num_pmts;

    //This 2 dim-asso array holds data of Periods.
    $periods =array();

    //for table heading
    array_push($periods,array("period"=>"Period",
            "date"=>"Date",
            "open_principal_bal"=>"Opening Principal Balance",
            "period_pmt"=>"Periodic Payment",
            "period_interest_pmt"=>"Interest Paid",
            "period_reduction_principal"=>"Principal Reduction",
            "end_principal_bal"=>"Ending Principal Balance")
    );

    //Declare variable total
    $total_actual_pmt = 0;
    $total_interest_pmt = 0;
    $total_reduction_principal =0;

    //Declare the Initial values for the first row data
    $period_date = $loan_date;
    $open_principal_bal = $loan_amt; //start with loan_amt
    $period_interest_pmt = 0;
    $period_reduction_principal = 0; //$period_pmt - $period_interest_pmt
    $period_pmt = 0; //start with: $plan_even_pay_method
    $end_principal_bal = $open_principal_bal - $period_reduction_principal;

    //for the Approval date
    array_push($periods,array("period"=>"Approval",
            "date"=>$period_date,
            "open_principal_bal"=>number_format($open_principal_bal, 2),
            "period_pmt"=>number_format($period_pmt, 2),
            "period_interest_pmt"=>number_format($period_interest_pmt, 2),
            "period_reduction_principal"=>number_format($period_reduction_principal, 2),
            "end_principal_bal"=>number_format($end_principal_bal, 2))
    );


    //Period 1:
    $period_date = $first_pmt_due;
    $open_principal_bal = $end_principal_bal;

    $period_interest_pmt = 0 + $pmt_of_points;
    $period_reduction_principal = $even_periodic_principal;
    $period_pmt = $period_interest_pmt + $period_reduction_principal;
    $end_principal_bal = $open_principal_bal - $period_reduction_principal;

    // Calculate running total for P1
    $total_interest_pmt += $period_interest_pmt;
    $total_reduction_principal += $period_reduction_principal;
    $total_actual_pmt += $period_pmt;

    array_push($periods,array("period"=>1,
            "date"=>$period_date,
            "open_principal_bal"=>number_format($open_principal_bal, 2),
            "period_pmt"=>number_format($period_pmt, 2),
            "period_interest_pmt"=>number_format($period_interest_pmt, 2),
            "period_reduction_principal"=>number_format($period_reduction_principal, 2),
            "end_principal_bal"=>number_format($end_principal_bal, 2))
    );

    //Calculate for remaining payment date, and display
    for ($i = 2; $i < $num_pmts + 1; $i++) {

        $payment_date_prev = $period_date; //catch previous value
        $period_date = add_sub_days_months($payment_date_prev, $str_len_pmt_interval);
        $open_principal_bal = $end_principal_bal;
        $period_interest_pmt =0;
        $period_reduction_principal = $even_periodic_principal;
        $period_pmt = $period_interest_pmt + $period_reduction_principal;
        $end_principal_bal = $open_principal_bal - $period_reduction_principal;

        // Calculate running total
        $total_interest_pmt += $period_interest_pmt;
        $total_reduction_principal += $period_reduction_principal;
        $total_actual_pmt += $period_pmt;

        //for the period from 2->k inclusive
        array_push($periods,array("period"=>$i,
                "date"=>$period_date,
                "open_principal_bal"=>number_format($open_principal_bal, 2),
                "period_pmt"=>number_format($period_pmt, 2),
                "period_interest_pmt"=>number_format($period_interest_pmt, 2),
                "period_reduction_principal"=>number_format($period_reduction_principal, 2),
                "end_principal_bal"=>number_format($end_principal_bal, 2))
        );
    }//end of forloop for period 2 to k inclusive

    //for the total line in the table
    array_push($periods,array("period"=>"Total",
            "date"=>'',
            "open_principal_bal"=>'',
            "period_pmt"=>number_format($total_actual_pmt, 2),
            "period_interest_pmt"=>number_format($total_interest_pmt, 2),
            "period_reduction_principal"=>number_format($total_reduction_principal, 2),
            "end_principal_bal"=>'')
    );
    return array("periods" =>$periods,
                "total_actual_pmt"=>$total_actual_pmt,
                "total_interest_pmt"=>$total_interest_pmt);

}//end fn calc_pmt_periods_no_interest


function display_user_input($num_days_in_year,$quoted_apr,$points,$loan_amt,$num_pmts,
                            $loan_date,$first_pmt_due,$selected_pmt_interval,
                            $selected_comp_interval,$amort_method,$has_interest_on_odd_days_interest){
    echo "<h3>Inputs:</h3>" .
        "<table border='1'>
            <tr>
                <td>Number of days in a year: " . $num_days_in_year ."</td>
                <td>Quoted APR(%): " . ($quoted_apr*100) . "</td>                                               
            </tr>
            <tr>
                <td>Points only applied Even Total Pay(%): " . number_format($points*100,1) ."</td>
                <td>Loan Amount($): " . number_format($loan_amt,2) . "</td>                                              
            </tr>
            <tr>
                <td>Number of payment: " . $num_pmts . "</td>
                <td>Loan dated on(yyyy-mm-dd): " . $loan_date . "</td>                                             
            </tr>
            <tr>
               <td>Payment Interval: " . $selected_pmt_interval . "</td>
               <td>First payment due: " .$first_pmt_due ."</td>                             
            </tr>
            <tr>
                <td>Compounding Interval: " . $selected_comp_interval ."</td>
                <td>Amortization Method: " . $amort_method . "</td>
            </tr>
            <tr>
                <td>Has Interest On Odd days Interest: " . $has_interest_on_odd_days_interest ."</td>
                <td></td>
            </tr>
         </table>";
}//end display_user_input

function display_result_summary($selected_pmt_interval,$first_pmt_due,$apr_new,$periodic_interest_rate,
                                $pmta_amt,$total_interest_pmt,$loan_amt,$total_actual_pmt){
    echo "<h3>Outputs:</h3>" .
    "<table border='1'>
        <tr>
            <td>Payment Interval: " . $selected_pmt_interval . "</td>
            <td>First payment date(yyyy-mm-dd): " . $first_pmt_due . "</td>                        
        </tr>
        <tr>
            <td>APR new(%): " . number_format($apr_new * 100, 3) . "</td>
            <td>Periodic interest(%): " . number_format($periodic_interest_rate * 100, 3) . "</td>            
        </tr>
        <tr>
            <td>Periodic Payment: $" . number_format($pmta_amt,2)."</td>
            <td>Total Interest Paid: $" . number_format($total_interest_pmt,2)."</td>
       </tr>
       <tr>           
            <td>Total Principal Paid: $" . number_format($loan_amt,2)."</td>
            <td>Total Payment Paid: $" . number_format($total_actual_pmt,2)."</td>            
       </tr>
     </table>";
}


// Reference: https://www.extension.iastate.edu/AgDM/wholefarm/html/c5-93.html
function calc_loan_fixed_pmt($loan_amt,$num_pmts,$num_days_in_year,$num_days_in_year,
                             $selected_comp_interval,$quoted_apr,$points,$loan_date,
                             $first_pmt_due,$selected_pmt_interval,$intervals_table,
                             $has_interest_on_odd_days_interest){

    //Update #days_in_year, that affects the daily interest
    $interval_lookup = '1 day';
    $r = update_num_days_in_year($intervals_table, $interval_lookup, $num_days_in_year);

    $pmt_interval_att = get_attributes_interval($selected_pmt_interval, $intervals_table);
    $str_len_pmt_interval = $pmt_interval_att['str_len_interval'];

    //Calculate apr_new
    $result_apr_new = calc_apr_new($selected_pmt_interval,
        $selected_comp_interval, $quoted_apr, $points, $intervals_table);
    $periodic_interest_rate = $result_apr_new['periodic_interest'];
    $apr_new = $result_apr_new['apr_new'];


    //Calculate periodic_pmt_amt
    $pmta_amt = calc_pmta($loan_amt, $periodic_interest_rate, $num_pmts);

    //Payment for Points
    $pmt_of_points = $loan_amt * $points;

    //Detail each pmt period
    $detailed_periods = calc_pmt_periods_fixed_pmt($loan_amt, $loan_date, $first_pmt_due, $num_pmts,
        $str_len_pmt_interval, $num_days_in_year,
        $apr_new, $periodic_interest_rate, $pmta_amt,
        $has_interest_on_odd_days_interest,$pmt_of_points);


    $periods = $detailed_periods['periods'];
    $total_actual_pmt = $detailed_periods['total_actual_pmt'];
    $total_interest_pmt = $detailed_periods['total_interest_pmt'];


    //Display result summary
    display_result_summary($selected_pmt_interval, $first_pmt_due, $apr_new,
        $periodic_interest_rate, $pmta_amt,
        $total_interest_pmt, $loan_amt, $total_actual_pmt);

    return array("selected_pmt_interval"=>$selected_pmt_interval,
        "apr_new"=>$apr_new,
        "first_pmt_due"=>$first_pmt_due,
        "periodic_pmt_amt" => $pmta_amt,
        "periodic_interest"=>$periodic_interest_rate,
        "total_actual_pmt"=>$total_actual_pmt,
        "total_interest_pmt"=>$total_interest_pmt,
        "pmt_of_points"=>$pmt_of_points,
        "periods"=>$periods);
}


//http://www.yorku.ca/amarshal/mortgage.htm
function calc_loan_canadian($loan_amt,$loan_date,$first_pmt_due,
                            $num_pmts,$quoted_apr,$selected_pmt_interval,
                            $num_days_in_year,$intervals_table,$points,
                            $has_interest_on_odd_days_interest){
    //Reset values specific for canadian_mortgage, in case
    $selected_comp_interval = "6 months";

    //Update #days_in_year in intervals_table, that affects the daily interest
    $interval_lookup = '1 day';
    $r = update_num_days_in_year($intervals_table, $interval_lookup, $num_days_in_year);

    $pmt_interval_att = get_attributes_interval($selected_pmt_interval, $intervals_table);
    $str_len_pmt_interval = $pmt_interval_att['str_len_interval'];

    //Calculate apr_new
    $result_apr_new = calc_apr_new($selected_pmt_interval,
        $selected_comp_interval, $quoted_apr, $points, $intervals_table);
    $periodic_interest_rate = $result_apr_new['periodic_interest'];
    $apr_new = $result_apr_new['apr_new'];

    //Calculate periodic_pmt_amt
    $pmta_amt = calc_pmta($loan_amt, $periodic_interest_rate, $num_pmts);

    //Payment for Points
    $pmt_of_points = $loan_amt * $points;

    //Detail each pmt period
    $detailed_periods = calc_pmt_periods_fixed_pmt($loan_amt, $loan_date, $first_pmt_due, $num_pmts,
        $str_len_pmt_interval, $num_days_in_year,
        $apr_new, $periodic_interest_rate, $pmta_amt,
        $has_interest_on_odd_days_interest,$pmt_of_points);
    $periods = $detailed_periods['periods'];
    $total_actual_pmt = $detailed_periods['total_actual_pmt'];
    $total_interest_pmt = $detailed_periods['total_interest_pmt'];

    //Display result summary
    display_result_summary($selected_pmt_interval, $first_pmt_due, $apr_new, $periodic_interest_rate,
        $pmta_amt, $total_interest_pmt, $loan_amt, $total_actual_pmt);

    return array("selected_pmt_interval"=>$selected_pmt_interval,
        "apr_new"=>$apr_new,
        "first_pmt_due"=>$first_pmt_due,
        "periodic_pmt_amt" => $pmta_amt,
        "periodic_interest"=>$periodic_interest_rate,
        "total_actual_pmt"=>$total_actual_pmt,
        "total_interest_pmt"=>$total_interest_pmt,
        "pmt_of_points"=>$pmt_of_points,
        "periods"=>$periods);
}


//https://loanstreet.com.my/learning-centre/how-banks-fooled-you-with-the-rule-of-78
//http://www.tiac.net/~mabaker/rule_of_78.html
//http://www.businessdictionary.com/definition/rule-of-78.html
function calc_loan_rule78($loan_amt,$loan_date,$selected_pmt_interval,
                          $num_pmts, $num_days_in_year,
                          $selected_comp_interval,$quoted_apr,
                          $first_pmt_due,$intervals_table,$points,
                          $has_interest_on_odd_days_interest){

    //Update #days_in_year in intervals_table, that affects the daily interest
    $interval_lookup = '1 day';
    $r = update_num_days_in_year($intervals_table, $interval_lookup, $num_days_in_year);

    $pmt_interval_att = get_attributes_interval($selected_pmt_interval, $intervals_table);
    $str_len_pmt_interval = $pmt_interval_att['str_len_interval'];

    //Calculate apr_new
    $result_apr_new = calc_apr_new($selected_pmt_interval,
        $selected_comp_interval, $quoted_apr, $points, $intervals_table);
    $periodic_interest_rate = $result_apr_new['periodic_interest'];
    $apr_new = $result_apr_new['apr_new'];

    //Calculate periodic_pmt_amt
    $pmta_amt = calc_pmta($loan_amt, $periodic_interest_rate, $num_pmts);

    //Payment for Points
    $pmt_of_points = $loan_amt * $points;

    //Get total_interest_pmt W/O effect of odds day
    $total_interest_wo_odd_days_effect = $pmta_amt * $num_pmts - $loan_amt;

    //Detail each pmt period using rule_of_78
    $detailed_periods_rule78 = calc_pmt_periods_rule_78($loan_amt, $loan_date, $num_pmts,
        $total_interest_wo_odd_days_effect,
        $first_pmt_due, $str_len_pmt_interval,
        $apr_new, $num_days_in_year, $pmta_amt,
        $periodic_interest_rate,
        $has_interest_on_odd_days_interest,$pmt_of_points);

    $periods = $detailed_periods_rule78['periods'];
    $total_actual_pmt = $detailed_periods_rule78['total_actual_pmt'];
    $total_interest_pmt = $detailed_periods_rule78['total_interest_pmt'];

    //Display result summary
    display_result_summary($selected_pmt_interval, $first_pmt_due, $apr_new, $periodic_interest_rate,
        $pmta_amt, $total_interest_pmt, $loan_amt, $total_actual_pmt);

    return array("selected_pmt_interval"=>$selected_pmt_interval,
        "apr_new"=>$apr_new,
        "first_pmt_due"=>$first_pmt_due,
        "periodic_pmt_amt" => $pmta_amt,
        "periodic_interest"=>$periodic_interest_rate,
        "total_actual_pmt"=>$total_actual_pmt,
        "total_interest_pmt"=>$total_interest_pmt,
        "pmt_of_points"=>$pmt_of_points,
        "periods"=>$periods);
}


// Reference: https://www.extension.iastate.edu/AgDM/wholefarm/html/c5-93.html
function calc_loan_fixed_principal($loan_amt,$loan_date,$first_pmt_due,
                                   $selected_pmt_interval,$quoted_apr,
                                   $selected_comp_interval,$num_pmts,
                                   $num_days_in_year, $intervals_table,$points,
                                   $has_interest_on_odd_days_interest){

    //Update #days_in_year, that affects the daily interest
    $interval_lookup = '1 day';
    $r = update_num_days_in_year($intervals_table, $interval_lookup, $num_days_in_year);

    $pmt_interval_att = get_attributes_interval($selected_pmt_interval, $intervals_table);
    $str_len_pmt_interval = $pmt_interval_att['str_len_interval'];

    //Calculate apr_new
    $result_apr_new = calc_apr_new($selected_pmt_interval,
        $selected_comp_interval, $quoted_apr, $points, $intervals_table);
    $periodic_interest_rate = $result_apr_new['periodic_interest'];
    $apr_new = $result_apr_new['apr_new'];

    //$pmta_amt of fixed principal => Just for display purpose
    $pmta_amt = $period_fixed_principal = $loan_amt / $num_pmts;

    //Payment for Points
    $pmt_of_points = $loan_amt * $points;

    //Detail each pmt period
    $detailed_periods = calc_pmt_periods_fixed_principal($loan_amt, $loan_date, $num_pmts,
        $first_pmt_due, $str_len_pmt_interval,
        $apr_new, $periodic_interest_rate,
        $num_days_in_year,
        $has_interest_on_odd_days_interest,$pmt_of_points);

    $periods = $detailed_periods['periods'];
    $total_actual_pmt = $detailed_periods['total_actual_pmt'];
    $total_interest_pmt = $detailed_periods['total_interest_pmt'];

    //Display result summary
    display_result_summary($selected_pmt_interval, $first_pmt_due, $apr_new, $periodic_interest_rate,
        $pmta_amt, $total_interest_pmt, $loan_amt, $total_actual_pmt);

    return array("selected_pmt_interval"=>$selected_pmt_interval,
        "apr_new"=>$apr_new,
        "first_pmt_due"=>$first_pmt_due,
        "periodic_pmt_amt" => $pmta_amt,
        "periodic_interest"=>$periodic_interest_rate,
        "total_actual_pmt"=>$total_actual_pmt,
        "total_interest_pmt"=>$total_interest_pmt,
        "pmt_of_points"=>$pmt_of_points,
        "periods"=>$periods);
}


function calc_loan_interest_only($loan_amt,$loan_date,$first_pmt_due,
                                 $selected_pmt_interval,$selected_comp_interval,
                                 $quoted_apr,$num_pmts,
                                 $has_interest_on_odd_days_interest,
                                 $intervals_table,$points,
                                 $num_days_in_year){

    //Update #days_in_year, that affects the daily interest
    $interval_lookup = '1 day';
    $r = update_num_days_in_year($intervals_table, $interval_lookup, $num_days_in_year);

    $pmt_interval_att = get_attributes_interval($selected_pmt_interval, $intervals_table);
    $str_len_pmt_interval = $pmt_interval_att['str_len_interval'];

    //Calculate apr_new
    $result_apr_new = calc_apr_new($selected_pmt_interval,
        $selected_comp_interval, $quoted_apr, $points, $intervals_table);
    $periodic_interest_rate = $result_apr_new['periodic_interest'];
    $apr_new = $result_apr_new['apr_new'];

    //$pmta_amt of fixed principal=> Just for display purpose
    $pmta_amt = $loan_amt;

    //Payment for Points
    $pmt_of_points = $loan_amt * $points;

    //Detail each pmt period
    $detailed_periods = calc_pmt_periods_interest_only($loan_amt, $loan_date, $first_pmt_due,
        $str_len_pmt_interval, $apr_new,
        $periodic_interest_rate, $num_pmts,
        $num_days_in_year, $has_interest_on_odd_days_interest,$pmt_of_points);

    $periods = $detailed_periods['periods'];
    $total_actual_pmt = $detailed_periods['total_actual_pmt'];
    $total_interest_pmt = $detailed_periods['total_interest_pmt'];

    //Display result summary
    display_result_summary($selected_pmt_interval, $first_pmt_due, $apr_new, $periodic_interest_rate,
        $pmta_amt, $total_interest_pmt, $loan_amt, $total_actual_pmt);

    return array("selected_pmt_interval"=>$selected_pmt_interval,
        "apr_new"=>$apr_new,
        "first_pmt_due"=>$first_pmt_due,
        "periodic_pmt_amt" => $pmta_amt,
        "periodic_interest"=>$periodic_interest_rate,
        "total_actual_pmt"=>$total_actual_pmt,
        "total_interest_pmt"=>$total_interest_pmt,
        "pmt_of_points"=>$pmt_of_points,
        "periods"=>$periods);
}


function calc_loan_no_interest($loan_amt,$loan_date,$first_pmt_due,
                               $selected_comp_interval,$quoted_apr,
                               $selected_pmt_interval,$num_pmts,
                               $intervals_table, $points,$num_days_in_year){

    //Update #days_in_year, that affects the daily interest
    $interval_lookup = '1 day';
    $r = update_num_days_in_year($intervals_table, $interval_lookup, $num_days_in_year);

    $pmt_interval_att = get_attributes_interval($selected_pmt_interval, $intervals_table);
    $str_len_pmt_interval = $pmt_interval_att['str_len_interval'];

    //Calculate apr_new
    $result_apr_new = calc_apr_new($selected_pmt_interval,
        $selected_comp_interval, $quoted_apr, $points, $intervals_table);
    $periodic_interest_rate = $result_apr_new['periodic_interest'];
    $apr_new = $result_apr_new['apr_new'];

    //$pmta_amt of fixed principal=> Just for display purpose
    $pmta_amt = $loan_amt / $num_pmts;

    //Payment for Points
    $pmt_of_points = $loan_amt * $points;

    //Detail each pmt period
    $detailed_periods = calc_pmt_periods_no_interest($loan_amt, $loan_date, $first_pmt_due,
        $str_len_pmt_interval, $num_pmts,$pmt_of_points);

    $periods = $detailed_periods['periods'];
    $total_actual_pmt = $detailed_periods['total_actual_pmt'];
    $total_interest_pmt = $detailed_periods['total_interest_pmt'];


    //Display result summary
    display_result_summary($selected_pmt_interval, $first_pmt_due, $apr_new, $periodic_interest_rate,
        $pmta_amt, $total_interest_pmt, $loan_amt, $total_actual_pmt);

    return array("selected_pmt_interval"=>$selected_pmt_interval,
        "apr_new"=>$apr_new,
        "first_pmt_due"=>$first_pmt_due,
        "periodic_pmt_amt" => $pmta_amt,
        "periodic_interest"=>$periodic_interest_rate,
        "total_actual_pmt"=>$total_actual_pmt,
        "total_interest_pmt"=>$total_interest_pmt,
        "pmt_of_points"=>$pmt_of_points,
        "periods"=>$periods);
}

function calcLoanPayments($loan_amt, $quoted_apr, $num_pmts,
                          $loan_date, $selected_pmt_interval,
                          $points, $amort_method, $num_days_in_year,
                          $first_pmt_due, $selected_comp_interval,
                          $intervals_table,$has_interest_on_odd_days_interest){

    // Display user inputs
    display_user_input($num_days_in_year,$quoted_apr,$points,$loan_amt,$num_pmts,
                        $loan_date,$first_pmt_due,$selected_pmt_interval,
                        $selected_comp_interval,$amort_method,$has_interest_on_odd_days_interest);


    //Handle each Method
    switch ($amort_method) {
        case 'fixed_pmt':
            $results = calc_loan_fixed_pmt($loan_amt,$num_pmts,$num_days_in_year,$num_days_in_year,
                $selected_comp_interval,$quoted_apr,$points,$loan_date,
                $first_pmt_due,$selected_pmt_interval,$intervals_table,
                $has_interest_on_odd_days_interest);
            break;

        case 'canadian_mortgage':
            $results = calc_loan_canadian($loan_amt,$loan_date,$first_pmt_due,
                                $num_pmts,$quoted_apr,$selected_pmt_interval,
                                $num_days_in_year,$intervals_table,$points,
                                $has_interest_on_odd_days_interest);
            break;

        case 'rule_78':
            $results = calc_loan_rule78($loan_amt,$loan_date,$selected_pmt_interval,
                                        $num_pmts, $num_days_in_year,
                                        $selected_comp_interval,$quoted_apr,
                                        $first_pmt_due,$intervals_table,$points,
                                        $has_interest_on_odd_days_interest);
            break;

        case 'fixed_principal':
            $results = calc_loan_fixed_principal($loan_amt,$loan_date,$first_pmt_due,
                                            $selected_pmt_interval,$quoted_apr,
                                            $selected_comp_interval,$num_pmts,
                                            $num_days_in_year,$intervals_table,$points,
                                            $has_interest_on_odd_days_interest);
            break;

        case 'interest_only':
            $results = calc_loan_interest_only($loan_amt,$loan_date,$first_pmt_due,
                                        $selected_pmt_interval,$selected_comp_interval,
                                        $quoted_apr,$num_pmts,
                                        $has_interest_on_odd_days_interest,
                                        $intervals_table,$points,
                                        $num_days_in_year);
            break;

        case 'no_interest':
            $results = calc_loan_no_interest($loan_amt,$loan_date,$first_pmt_due,
                                    $selected_comp_interval,$quoted_apr,
                                    $selected_pmt_interval,$num_pmts,$intervals_table,
                                    $points,$num_days_in_year);
            break;

        default:
            echo "Upcoming amortization method!!!";
    }//end switch statement

    $selected_pmt_interval = $results['selected_pmt_interval'];
    $apr_new = $results['apr_new'];
    $first_pmt_due = $results['first_pmt_due'];
    $pmta_amt = $results['periodic_pmt_amt'];
    $periodic_interest_rate = $results['periodic_interest'];
    $total_actual_pmt = $results['total_actual_pmt'];
    $total_interest_pmt = $results['total_interest_pmt'];
    $pmt_of_points = $results['pmt_of_points'];
    $periods = $results['periods'];

    //Return JSON, so that front-end part can use it to render
    return array(
        "err"=>0, "msg"=>"ok",
        "selected_pmt_interval"=>$selected_pmt_interval,
        "apr_new"=>$apr_new,
        "first_pmt_due"=>$first_pmt_due,
        "periodic_pmt_amt" => $pmta_amt,
        "periodic_interest"=>$periodic_interest_rate,
        "total_actual_pmt"=>$total_actual_pmt,
        "total_interest_pmt"=>$total_interest_pmt,
        "pmt_of_points"=>$pmt_of_points,
        "periods"=>$periods
    );

}//end of function: calcLoanPayments(...)


if(isset($_POST['num_days_in_year'])&& $_POST['num_days_in_year']!=""&&
    isset($_POST['loan_amt']) && $_POST['loan_amt']!=""&&
    isset($_POST['quoted_apr'])&& $_POST['quoted_apr']!=""&&
    isset($_POST['num_pmts']) && $_POST['num_pmts']!=""&
    isset($_POST['loan_date']) && $_POST['loan_date'] !=""&&
    isset($_POST['selected_pmt_interval']) && $_POST['selected_pmt_interval']!=""&&
    isset($_POST['points']) && $_POST['points']!=""&&
    isset($_POST['amort_method']) && $_POST['amort_method'] !=""&&
    isset($_POST['first_pmt_due']) && $_POST['first_pmt_due'] !=""&&
    isset($_POST['selected_comp_interval']) && $_POST['selected_comp_interval'] !=""){

    //===================================USER INPUTs================================================

    $num_days_in_year = $_POST['num_days_in_year']; //INPUT_1: 365
    $loan_amt = $_POST['loan_amt']; //INPUT_2: 32500
    $quoted_apr = ($_POST['quoted_apr'])/100; //INPUT_3: 0.075
    $num_pmts = $_POST['num_pmts']; //INPUT_4: 15
    $loan_date = $_POST['loan_date']; //INPUT_5 => Select from list: '2016-10-26'
    $selected_pmt_interval = $_POST['selected_pmt_interval']; //INPUT_6 => select from list of options: '3 months'
    $points = ($_POST['points'])/100;
    $amort_method =$_POST['amort_method'];
    $first_pmt_due = $_POST['first_pmt_due'];//'2016-12-23';//added
    $selected_comp_interval = $_POST['selected_comp_interval'];//'6 months';//added

    //Get default value, most common
    $has_interest_on_odd_days_interest ="No";

    if (isset($_POST['has_interest_on_odd_days_interest'])){
        $has_interest_on_odd_days_interest ="Yes";
    }else{
        $has_interest_on_odd_days_interest ="No";
    }

    //Validation user's input at backend
    if (!(is_numeric($quoted_apr) && $quoted_apr >= 0 && $quoted_apr <= 100)){
        die("APR must be a number between 0 to 100");
    }

    if (!(is_numeric($points) && $points >= 0 && $points <= 100)){
        die("Points must be a number between 0 to 100");
    }

    if (!(is_numeric($loan_amt) && $loan_amt >= 0 && $loan_amt >=0)){
        die("Loan amount must be a positive number");
    }

    if (!(is_numeric($num_pmts) && $num_pmts >= 0 && $num_pmts <= 100)){
        die("Number of payments must be a positive number");
    }

    if((strtotime($_POST["first_pmt_due"]) - strtotime($_POST["loan_date"])) <0){
        die("The first payment due date must be later than the loan date");
    }




    //===================================//END USER INPUTS================================================

    $result_str = array("err"=>1, "msg"=>"Input incorrect");
    $calculation_result = calcLoanPayments($loan_amt, $quoted_apr, $num_pmts,
                                $loan_date, $selected_pmt_interval,
                                $points, $amort_method, $num_days_in_year,
                                $first_pmt_due,$selected_comp_interval,
                                $intervals_table,$has_interest_on_odd_days_interest); //true: to return PHP array, otherwise, it will be an object.

    $result_str = $calculation_result;
    //10 data retrieved from this function above
    $periodic_even_pay = $calculation_result['periodic_pmt_amt'];//1
    $periodic_interest = $calculation_result['periodic_interest'];//3
    $total_actual_pmt = $calculation_result['total_actual_pmt'];//4
    $total_interest_pmt = $calculation_result['total_interest_pmt'];//5
    $pmt_of_points = $calculation_result['pmt_of_points'];//6
    $periods = $calculation_result['periods'];//7
    $selected_pmt_interval = $calculation_result['selected_pmt_interval'];//8
    $apr_new = $calculation_result['apr_new'];//9
    $first_pmt_due = $calculation_result['first_pmt_due'];//10

    //echo "period = " . $periods[3]["date"];
    render_result_at_backend($periods, "Amortization Schedule Table");


    //echo "<br />";
    //echo json_encode($result_str);

}else{
    echo "All fields are required.";
}

?>