<?php 

namespace S1calculate\Loan\Finance;

class Loan_Application_Fee  extends Base_Fee{
	//private $percent;    
	//private $fix_min;    
	//private $fix_max;    
	//private $loan_type;    


    public function Calc($loan_amount = NULL)
    {

        if(isset($this->fix_fee) && $this->fix_fee > 0)
        {
            return $this->fix_fee;
        }
        if($loan_amount == NULL)
        {
            throw new \Exception('loan amount is Null');
        }
        if($loan_amount==0){
            return 0;
        }
        $result = $loan_amount * $this->percent / 100;
        if($result > $this->fix_min && ($this->fix_max == 0 || $result < $this->fix_max))
        {
            return $result;
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