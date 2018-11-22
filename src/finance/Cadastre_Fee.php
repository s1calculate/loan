<?php 

namespace S1calculate\Loan\Finance;

class Cadastre_Fee extends Base_Fee {

	public function Calc()
	{

        if(isset($this->fix_fee) && $this->fix_fee > 0)
        {
            return $this->fix_fee;
        }
        else
        {
            return 0;
        }
	}
}