<?php

namespace S1calculate\Loan\Finance;

class S1_Service_Financial_LoanSchedule
{

    public function getPaymentDate($date,$start_date,$mn,$payment_day,$duration=0,$nextInSomeMonth=0)
	{
		
		
		$current_month           = date("n",strtotime($date));
		$start_month           = date("n",strtotime($start_date));
        $current_year           = date("Y",strtotime($date));		
		$next_month = ($start_month+$mn)%12;
		$next_month = $next_month == 0 ? 12:$next_month;
		
		if($nextInSomeMonth)
		{
			$next_month = $current_month;
		}
		
		$next_year = $next_month >= $current_month ? $current_year:$current_year+1;
		
		if($next_month == 2 &&  $payment_day == 29  && $next_year % 4 != 0)
		{
			$next_month = 2;
			$payment_day =28;
			
		}
		elseif($next_month == 2 &&  $payment_day > 28  && $next_year % 4 != 0)
		{
			$next_month = 2;
			$payment_day =28;
		}
		
		if(false == checkdate($next_month,$payment_day,$next_year ))
		{
			if($nextInSomeMonth)
			{
				$payment_day --;	
			}else
			{
				
				if($payment_day == 31)
				{
					$payment_day = 30;
				}
			}
			
		}
		
		 
		
		
		$end_date = mktime(0,0,0,$next_month,$payment_day,$next_year) ;
		$end_date_str = date("Y-m-d",$end_date);
		
		 
		 
		
		while($this->isWeekend($end_date_str))
		{
			$next_date = date("Y-m-d", strtotime('+1 day', strtotime($end_date_str))); 
			$end_date_str = $next_date ;
			
		}
		
	
		return $end_date_str ;
	}
    
    public function nextWorkDay($date)
	{
		  
		if($this->isWeekend($date) && $date != "2018-03-17" && $date != "2018-04-07"  && $date != "2018-05-05")
		{
			$date = date("Y-m-d", strtotime('+1 day', strtotime($date))); 
			return $this->nextWorkDay($date);
		}
		return $date;
	}
    public function isWeekend($date)
    {


                

        $weekDay = date('w', strtotime($date));
        $Y = date('Y', strtotime($date));
		
		
		$StateHolidays = array
                (
					(date("$Y-12-31")),
					(date("$Y-01-01")),
					(date("$Y-01-02")),
					// (date("$Y-01-03")),
					// (date("$Y-01-04")),
					// (date("$Y-01-05")),
					(date("$Y-01-06")),
					(date("$Y-01-07")),
					(date("$Y-01-28")),
					(date("$Y-03-08")),
					(date("$Y-04-24")),
					(date("$Y-05-01")),
					(date("$Y-05-09")),
					(date("$Y-05-28")),
					(date("$Y-07-05")),
					(date("$Y-09-21"))
                );
				
				
        $isHollyday = in_array($date, $StateHolidays) ? true:false;
		
		
		$freeDay = ($weekDay == 0 || $weekDay == 6 || $isHollyday);
		 
		
        return   $freeDay;
    }
}