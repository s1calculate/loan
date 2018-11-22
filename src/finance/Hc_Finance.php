<?php
namespace S1calculate\Loan\Finance;

use S1calculate\Loan\Finance\S1_Service_Financial_LoanSchedule;

Class Hc_Loan_Calculator
{
	
	public function __construct($loan_amount,$period,$percent,$start_date=false,$prevPaymentDay=false,$loan_fee_day=false,$onetime_fee=0,$cashing_fee=0,$mount_fee=0,$year_insurance_fee=0)
	{
		if ($start_date == false)
		{
			$start_date = date('m/d/Y');
		}

		if($prevPaymentDay==false)
		{
			$prevPaymentDay = $start_date;
		}
		
		if ($loan_fee_day == false)
		{
			$loan_fee_day = date('d',strtotime($start_date));
		}
		$this->schedule = $this->Monthly_Pay_Schedule($loan_amount,$period,$percent,$start_date,$prevPaymentDay,$loan_fee_day,$onetime_fee,$cashing_fee,$mount_fee,$year_insurance_fee);
		
	}
	
	public function Monthly_Pay_Schedule($loan_amount,$period,$percent,$start_date,$prevPaymentDay,$loan_fee_day,$onetime_fee,$cashing_fee,$mount_fee,$year_insurance_fee)
	{
		
		
		$monthly_interest = array();
		$principal_balance = array();
		$monthly_principal_amount = array();
		$perc = $percent/100;
		$nextPaymentDay = $this->Pay_Dates($loan_amount,$period,$start_date,$prevPaymentDay,$loan_fee_day);
		$diff_dates = $this->Pay_Dates_Diff($period,$nextPaymentDay);
		$annuity = $this->Annuity($loan_amount,$period,$start_date,$prevPaymentDay,$loan_fee_day,$percent);
		
		for($i = 0 ; $i< $period ; $i++)
		{
			if($i == 0)
			{
				$monthly_interest[$i] = round($loan_amount*$perc/365*$diff_dates[$i],2);
			}
			else
			{
				$monthly_interest[$i] = round($principal_balance[$i-1] * $perc/365*$diff_dates[$i],2);
			}
			
			$last_monthly_principal_amount = $annuity - $monthly_interest[$i];
			$monthly_principal_amount[$i] = $last_monthly_principal_amount;
			if($i == 0){
				$principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
			}
			else{
				$principal_balance[$i] = $principal_balance[$i-1] - $last_monthly_principal_amount;
			}
			if($i == $period-1){
				$monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
				$principal_balance[$i] = 0;
			}
			$schedule[$i] =  array( "principal_balance"=>$principal_balance[$i],"monthly_interest"=>$monthly_interest[$i],"monthly_principal_amount"=>$monthly_principal_amount[$i],"loan_pay_day"=>$nextPaymentDay[$i+1]);
		}
		$monthly_service_fee = $this->Monthly_Service_Fee($loan_amount,$period,$principal_balance,$diff_dates,$mount_fee);
		$annual_insurance_fee = $this->Annual_Insurance_Fee($loan_amount,$period,$year_insurance_fee); 
		//print_r($Amsekan_Spasarkman_Vchar);die('xxx');
		for($i=0 ;$i < $period; $i++)
		{
			$schedule[$i]+=["monthly_service_fee"=>$monthly_service_fee[$i],"annual_insurance_fee"=>$annual_insurance_fee[$i]];
		}
		$other_fee = $this->Onetime_And_Cashing_Fee($loan_amount,$onetime_fee,$cashing_fee);
		$all_in_schedule=array();
		$all_in_schedule = ["other_fee"=>$other_fee,"schedule"=>$schedule];
		
		return $all_in_schedule;
	}
	
	
	
	public function Annuity($loan_amount,$period,$start_date,$prevPaymentDay,$loan_fee_day,$percent)
	{
		
		$nextPaymentDay = $this->Pay_Dates($loan_amount,$period,$start_date,$prevPaymentDay,$loan_fee_day);
		$diff_dates = $this->Pay_Dates_Diff($period,$nextPaymentDay);
		
		
		$perc = $percent/100;
		$BZ = array();
		$CA = array();
		$CC = array();
		for($i=0 ; $i < $period ; $i++)
		{
			$BZ[$i] = round($perc/365*$diff_dates[$i],4);
			$CA[$i] = $BZ[$i] + 1;
			if($i != 0){
				$CC_count = round($CC[$i-1] * $CA[$i] + 1,4);
				$CC[$i] = $CC_count;
			}
			else{
				$CC[$i] = round(0 * $CA[$i] + 1,4); 
			}
		}
		
		$summ = $this->ca_multyple($CA);
		$AMSEKAN_HETVCHAR = round($loan_amount*$summ/$CC[$period-1],2);
		return $AMSEKAN_HETVCHAR;
	}
	
	public function Pay_Dates_Diff($period,$nextPaymentDay)
	{
		for($i=0 ; $i<=$period ; $i++)
		{
			if($i != 0)
			{
				$diff_dates_val=date_diff(date_create($nextPaymentDay[$i]),date_create($nextPaymentDay[$i-1]),true);
				$diff_date = $diff_dates_val->days;
				$diff_dates[$i-1] = $diff_date;
			}
		}
		return $diff_dates;
	}
	
	public function Pay_Dates($loan_amount,$period,$start_date,$prevPaymentDay,$loan_fee_day)
	{		
		$get_date = new S1_Service_Financial_LoanSchedule();
		for($i=0 ; $i<=$period ; $i++)
		{
			$nextPaymentDay[$i] = $get_date->getPaymentDate($prevPaymentDay,$start_date,$i,$loan_fee_day); 
			$prevPaymentDay = $nextPaymentDay[$i];
		}
		
		return $nextPaymentDay;
	}
	
	
	public function Annual_Insurance_Fee($loan_amount,$period,$year_insurance_fee)
	{
		$annual_insurance_fee = array();
		for($i = 0 ; $i< $period ; $i++)
		{
			if($year_insurance_fee >= 0)
			{
				$annual_insurance_fee[$i] = round($loan_amount*$year_insurance_fee/100/12,2);
			}
			else
			{
				$annual_insurance_fee[$i]=0;
			}
		}
		return $annual_insurance_fee;
	}
	
	
	public function Monthly_Service_Fee($loan_amount,$period,$principal_balance,$diff_dates,$mount_fee)
	{
		
		$Ams_spas_vchar = array();
		for($i = 0 ; $i< $period ; $i++)
		{
			if($mount_fee >= 0)
			{
				$Ams_spas_vchar[$i] = round($loan_amount*$mount_fee/100,2);
			}
			else
			{
				if($i==0)
				{
					$Ams_spas_vchar[$i] = round(-$loan_amount*$mount_fee/100*12/365*$diff_dates[$i],2);
				}
				else
				{
					$Ams_spas_vchar[$i] = round(-$principal_balance[$i-1]*$mount_fee/100*12/365*$diff_dates[$i],2);
				}
			}
		}
		return $Ams_spas_vchar;
	}
	
	public function Onetime_And_Cashing_Fee($loan_amount,$onetime_fee,$cashing_fee)
	{
		$sum = $loan_amount*$onetime_fee/100 + $loan_amount*$cashing_fee/100 ;
		return $sum;
	}
	
	public function ca_multyple($array)
	{
		$sum = 1;
		$count = count($array);
		for($i=0 ; $i < $count ; $i++)
		{
			$sum = $sum * $array[$i];
		}
		return $sum;
	}
}

?>