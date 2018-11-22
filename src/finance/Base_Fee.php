<?php 

namespace S1calculate\Loan\Finance;

abstract class Base_Fee {
	public $fix_fee ;
	public $fee_type ;
	public $percent;    
	public $fix_min;    
	public $fix_max;    
	public $loan_type;    
	 

	abstract public function Calc();
	

	public function setPercent($v){
		if($v >= 0 && $v < 100)
		{
			$this->percent = $v;
		}
		else{
			throw new \Exception("Percent value invaide");
		}
	}
	public function setFixfee($v){
		$this->fix_fee = $v;
	}
	/**
	*
	* $v = 1  One time fee
	* $v = 2  every month fee
	* $v = 3  every year fee
	*
	*/
	public function setFeetype($v){
		if($v == 1 || $v == 2 || $v == 3)
		{
			$this->fee_type = $v;
		}
		else
		{
			throw new \Exception("fee type value need to be one of 1 , 2 , 3  integers ");
		}
	}
	public function setFixmin($v){
		$this->fix_min = $v;
	}
	public function setFixmax($v){
	    $this->fix_max = $v;
	}

    /**
     * @param $v
     * @throws \Exception
     * $v can have  1 , 2 , 3 or 4 integers
     */
	public function setLoantype($v){
		if($v == 1 || $v == 2 || $v == 3 || $v == 4)
		{
			$this->loan_type = $v;
		}
		else
		{
			throw new \Exception("Loan type value need to be one of 1 , 2 , 3 , 4 integers ");
		}
	}

	public function getLoantype(){
		return $this->loan_type;
	}
}