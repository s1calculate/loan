<?php 

namespace S1calculate\Loan\Finance;
use S1calculate\Loan\Finance\Base_Fee;

class Loan_Other_Fee extends Base_Fee {
	//private $percent;    
	//private $fix_min;    
	//private $fix_max;    
	//private $loan_type;    


    /**
     * @param null $loan_amount
     * @return float|int
     */
	public function Calc($loan_amount = NULL)
	{
	    /*if($this->fix_fee == 0 && $this->fix_min == 0 && $this->fix_max == 0)
        {
            return 0;
        }*/
		if(isset($this->fix_fee) && $this->fix_fee > 0)
		{
			return $this->fix_fee;
		}

		if($loan_amount == NULL)
		{
            throw new \Exception('loan amount is Null');
		}
        if($loan_amount == 0){
            return 0;
        }
		$result = $loan_amount * $this->percent / 100;
		if($result > $this->fix_min && ($this->fix_max == 0 || $result < $this->fix_max))
		{
			return round($result,2);
		}
		elseif($result > $this->fix_max)
		{
			return $this->fix_max;
		}
		else
		{
			return $this->fix_min;
		}
	}


}