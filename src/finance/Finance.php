<?php
namespace S1calculate\Loan\Finance;

use S1calculate\Loan\Finance\Loan_Service_Fee;
use S1calculate\Loan\Finance\S1_Service_Financial_LoanSchedule;
use PhpOffice\PhpSpreadsheet\Calculation\Financial;


class Finance {
	private $pledge;    // Գրավի արժեք;

	private $loan_amount;    // Վարկի գումար;****
	private $loan_period;    // ՎԱՐԿԻ ԺԱՄԿԵՏ @st oreri;*******
	private $loan_percent;    // ՎԱՐԿԻ ՏՈԿՈՍԱԴՐՈՒՅՔ;*******
	private $loan_application_fee = 0;    // Վարկային հայտի ուսումնասիրության վճար;  (միանվագ) Թիվ/տոկոս նվազ. առ. արժեքներով ********
	private $loan_service_fee = 0;    // Վարկի սպասարկման վճար (տարեկան)  Թիվ/տոկոս ********

	//private $percent_period;    // Տոկոսի պարբերականություն (եռամսյակային/ամսական/սկզբում/վերջում);

    private $repayment_method = null;     // ՄԱՐՄԱՆ ԵՂԱՆԱԿ		(հավասարաչափ/ոչ հավասարաչափ)  ****
	private $collateral_assessment_fee =0;         // Գրավի գնահատման վճար (միանվագ)
	private $cash_service_fee =0;         // Կանխիկացման վճար  նվազ. առ. արժեքներով
	private $collateral_maintenance_fee =0;         // Գրավի պահպանման վճար (միանվագ)
	private $collateral_insurance_fee =0;         //  Գրավի ապահովագրության վճար (տարեկան)
	private $cadastre_fee =0;         // Կադաստրի հետ կապված վճար (միանվագ)  թիվ
    private $borrower_insurance_fee = 0;   // Վարկառուի դժբախտ պատահարներից ապահովագրության վճար (տարեկան)  թիվ/տոկոս
	private $pledge_state_fee = 0;   // Գրավի պետական գրանցման հետ կապված վճար  (միանվագ)  թիվ
	private $notary_validation_fee = 0;   // Նոտարական վավերացման վճար (միանվագ)  (միանվագ)  թիվ
    private $loan_other_fee = array();




    private $Grace_Period = null;// Արտոնյալ ժամկետ


    private $loan_principial_grace_period = null;    // Վարկի Արտոնյալ ժամկետ
    private $loan_interest_grace_period = null;      // Տոկոսի Արտոնյալ ժամկետ

    private $loan_repayment_period = null;     // ամսական/եռամսյակային/վերջում
    private $interest_repayment_period = null;     // ամսական/եռամսյակային/վերջում/սկզբում

    private $loan_amount_interest = 0 ;      // Վարկի գումար + ՎԱՐԿԻ ՏՈԿՈՍներ

    public function __construct(){

    }

    public function setLoanpledge($pledge)
    {
        $this->pledge = $pledge;
    }

	public function setLoanperiod($period)
	{
		if($period%365 == 0 && $period != 0)
		{
			$period = $period/365*12;
			$this->loan_period=$period;
		}
		elseif($period%30 == 0 && $period != 0)
		{
			$period = $period/30;
			$this->loan_period=$period;
		}
		elseif($period > 30  && $period != 0)
		{
			$period = round($period/30);
			$this->loan_period=$period;
		}
		elseif($period !=0 )
		{
			$period = 1;
			$this->loan_period=$period;
		}
		else
		{
			$this->loan_period=0;
		}
	}

	public function setLoanamount($amount)
	{
		$this->loan_amount = $amount;
	}

    public function getLoanamount(){
        return $this->loan_amount;
    }
    /**
     * @param $value
     * value can be 1 or 2
     */
    public function setLoanRepaymentMethod($value)
    {
        if($value == 1 || $value == 2) {
            $this->repayment_method = $value;
        }else{
            throw new \Exception("RepaymentMethod has invalid value");
        }
    }

    public function setLoanRepaymentPeriod($value)
    {
        if($value == 1 || $value == 2 || $value ==3) {
            $this->loan_repayment_period = $value;
        }else{
            throw new \Exception("LoanaRepaymentMethod  has invalid value");
        }
    }

    public function setLoanGracePeriodPrincipal($period)
    {
        if($period <= $this->loan_period && $period >= 0) {
            $this->loan_principial_grace_period = $period;
        }else{
            throw new \Exception("Grace Period Principal  has invalid value");
        }
    }

    public function setLoanGracePeriodInterest($period)
    {
        if($period <= $this->loan_period && $period >= 0) {
            $this->loan_interest_grace_period = $period;
        }else{
            throw new \Exception("InterestRepaymentPeriod  has invalid value");
        }
    }

    public function setLoanInterestRepaymentPeriod($value)
    {
        if($value == 1 || $value == 2 || $value == 3 || $value ==4) {
            $this->interest_repayment_period = $value;
        }else{
            throw new \Exception("InterestRepaymentPeriod  has invalid value");
        }
    }

	public function setLoanpercent($percent)
	{
		$this->loan_percent = $percent;
	}

    /**
     * @param int $FixPrice
     * @param float $Percent
     * @param int $FixMin
     * @param int $FixMax
     * @return double
     * if $FixPrice not 0 , function another parameters not using;
     */

	public function setLoanApplicationFee($FixPrice=0,$Percent=0.0,$FixMin=0,$FixMax=0,$LoanType=1)
	{
        $application_fee = new Loan_Application_Fee();
        if ($FixPrice != 0) {
            $application_fee->setFixfee($FixPrice);
            //$loan_aplication_fee = $application_fee->call();
        }
        if ($Percent >= 0 && $Percent < 100) {
            $application_fee->setPercent($Percent);
        }
        if ($FixMin <= $FixMax) {
            $application_fee->setFixmin($FixMin);
            $application_fee->setFixmax($FixMax);
        }
        if ($LoanType == 1 || $LoanType == 2 || $LoanType == 3) {
            $application_fee->setLoantype($LoanType);
        } else {
            throw  new \Exception('LoanType can use this values 1 , 2 or 3');
        }
        $this->loan_application_fee = $application_fee;
	}

    /**
     * @param int $FixPrice
     * @param float $Percent
     * @param int $FixMin
     * @param int $FixMax
     * @param int $LoanType
     * @return string
     */
	public function setLoanCollateralAssessmentFee($FixPrice=0,$Percent=0.0,$FixMin=0,$FixMax=0,$LoanType=1)
	{
        $collateral_assessment_fee = new Collateral_Assessment_Fee();
        if($FixPrice != 0)
        {
            $collateral_assessment_fee->setFixfee($FixPrice);

        }
        if($Percent >= 0 && $Percent < 100)
        {
            $collateral_assessment_fee->setPercent($Percent);
        }
        else
        {
            throw  new \Exception('Percent value incorrect');
        }
        if($FixMin <= $FixMax )
        {
            $collateral_assessment_fee->setFixmin($FixMin);
            $collateral_assessment_fee->setFixmax($FixMax);
        }
        if ($LoanType == 1 || $LoanType == 2 || $LoanType == 3) {
            $collateral_assessment_fee->setLoantype($LoanType);
        } else {
            throw  new \Exception('LoanType can use this values 1 , 2 or 3');
        }
        $this->collateral_assessment_fee = $collateral_assessment_fee;
	}

    /**
     * @param int $FixPrice
     * @param float $Percent
     * @param int $FixMin
     * @param int $FixMax
     * @param int $LoanType
     * @return string
     */
    public function setLoanCashServiceFee($FixPrice=0,$Percent=0.0,$FixMin=0,$FixMax=0,$LoanType=1)
    {
        $cash_service_fee = new Cash_Service_Fee();
        if ($FixPrice != 0) {
            $cash_service_fee->setFixfee($FixPrice);
        }
        if ($Percent >= 0 && $Percent < 100) {
            $cash_service_fee->setPercent($Percent);
        } else {
            throw  new \Exception('Percent value incorrect');
        }
        if ($FixMin <= $FixMax) {
            $cash_service_fee->setFixmin($FixMin);
            $cash_service_fee->setFixmax($FixMax);
        }
        if ($LoanType == 1 || $LoanType == 2 || $LoanType == 3) {
            $cash_service_fee->setLoantype($LoanType);
        } else {
            throw  new \Exception('LoanType can use this values 1 , 2 or 3');
        }
        $this->cash_service_fee = $cash_service_fee;
    }

    public function setLoanBorrowerInsuranceFee($FixPrice=0,$Percent=0.0,$FixMin=0,$FixMax=0,$LoanType=1)
    {
        $borrower_insurance_fee = new Borrower_Insurance_Fee();
        if ($FixPrice != 0) {
            $borrower_insurance_fee->setFixfee($FixPrice);
        }
        if ($Percent >= 0 && $Percent < 100) {
            $borrower_insurance_fee->setPercent($Percent);
        } else {
            throw  new \Exception('Percent value incorrect');
        }
        if ($FixMin <= $FixMax) {
            $borrower_insurance_fee->setFixmin($FixMin);
            $borrower_insurance_fee->setFixmax($FixMax);
        }
        if ($LoanType == 1 || $LoanType == 2 || $LoanType == 3) {
            $borrower_insurance_fee->setLoantype($LoanType);
        } else {
            throw  new \Exception('LoanType can use this values 1 , 2 or 3');
        }
        $this->borrower_insurance_fee = $borrower_insurance_fee;
    }

    public function setLoanCollateralInsuranceFee($FixPrice=0,$Percent=0.0,$FixMin=0,$FixMax=0,$LoanType=1)
    {
        $collateral_insurance_fee = new Collateral_Insurance_Fee();
        if ($FixPrice != 0) {
            $collateral_insurance_fee->setFixfee($FixPrice);
        }
        if ($Percent >= 0 && $Percent < 100) {
            $collateral_insurance_fee->setPercent($Percent);
        } else {
            throw  new \Exception('Percent value incorrect');
        }
        if ($FixMin <= $FixMax) {
            $collateral_insurance_fee->setFixmin($FixMin);
            $collateral_insurance_fee->setFixmax($FixMax);
        }
        if ($LoanType == 1 || $LoanType == 2 || $LoanType == 3 || $LoanType == 4) {
            $collateral_insurance_fee->setLoantype($LoanType);
        } else {
            throw  new \Exception('LoanType can use this values 1 , 2 or 3');
        }
        $this->collateral_insurance_fee = $collateral_insurance_fee;
    }

    public function setLoanCollateralMaintenanceFee($FixPrice=0,$Percent=0.0,$FixMin=0,$FixMax=0,$LoanType=1)
    {
        $collateral_maintenance_fee = new Collateral_Maintenance_Fee();
        if ($FixPrice != 0) {
            $collateral_maintenance_fee->setFixfee($FixPrice);
        }
        if ($Percent >= 0 && $Percent < 100) {
            $collateral_maintenance_fee->setPercent($Percent);
        } else {
            throw  new \Exception('Percent value incorrect');
        }
        if ($FixMin <= $FixMax) {
            $collateral_maintenance_fee->setFixmin($FixMin);
            $collateral_maintenance_fee->setFixmax($FixMax);
        }
        if ($LoanType == 1 || $LoanType == 2 || $LoanType == 3 || $LoanType == 4) {
            $collateral_maintenance_fee->setLoantype($LoanType);
        } else {
            throw  new \Exception('LoanType can use this values 1 , 2 or 3');
        }
        $this->collateral_maintenance_fee = $collateral_maintenance_fee;
    }

    public function setLoanNotaryValidationFee($FixPrice=0,$Percent=0.0,$FixMin=0,$FixMax=0,$LoanType=1)
    {

        $notary_validation_fee = new Notary_Validation_Fee();
        if ($FixPrice != 0) {
            $notary_validation_fee->setFixfee($FixPrice);
        }
        if ($Percent >= 0 && $Percent < 100) {
            $notary_validation_fee->setPercent($Percent);
        } else {
            throw  new \Exception('Percent value incorrect');
        }
        if ($FixMin <= $FixMax) {
            $notary_validation_fee->setFixmin($FixMin);
            $notary_validation_fee->setFixmax($FixMax);
        }
        if ($LoanType == 1 || $LoanType == 2 || $LoanType == 3) {
            $notary_validation_fee->setLoantype($LoanType);
        } else {
            throw  new \Exception('LoanType can use this values 1 , 2 or 3');
        }
        $this->notary_validation_fee = $notary_validation_fee;
    }

    /**
     * @param int $FixPrice
     * @param float $Percent
     * @param int $FixMin
     * @param int $FixMax
     * @param int $Loan_type    it have 3 value    1  ,  2  , 3
     * 1 is "վարկի մայր գումարի նկատմամբ"  , 2 is "վարկի մնացորդի նկատմամբ" , 3 is  "գրավի արժեքի նկատմամբ"
     *
     *
     * if $FixPrice not 0 , function another parameters not using;
     */
    public function setLoanServiceFee($FixPrice=0,$Percent=0.0,$FixMin=0,$FixMax=0,$LoanType=1)
    {
        //dd($LoanType);
        $service_fee = new Loan_Service_Fee();
        if ($FixPrice != 0) {
            $service_fee->setFixfee($FixPrice);
        }
        if ($Percent >= 0 && $Percent < 100) {
            $service_fee->setPercent($Percent);
        } else {

            throw  new \Exception('Percent value incorrect');
        }
        if ($FixMin <= $FixMax) {
            $service_fee->setFixmin($FixMin);
            $service_fee->setFixmax($FixMax);
        }
        if ($LoanType == 1 || $LoanType == 2 || $LoanType == 3) {
            $service_fee->setLoantype($LoanType);
        } else {
            throw  new \Exception('LoanType can use this values 1 , 2 or 3');
        }
        $this->loan_service_fee = $service_fee;
    }

    public function setLoanCadastreFee($FixPrice=0,$Percent=0.0,$FixMin=0,$FixMax=0,$LoanType=1)
    {
        $cadastre_fee = new Cadastre_Fee();
        if ($FixPrice != 0) {
            $cadastre_fee->setFixfee($FixPrice);
        }
        if ($Percent >= 0 && $Percent < 100) {
            $cadastre_fee->setPercent($Percent);
        } else {

            throw  new \Exception('Percent value incorrect');
        }
        if ($FixMin <= $FixMax) {
            $cadastre_fee->setFixmin($FixMin);
            $cadastre_fee->setFixmax($FixMax);
        }
        if ($LoanType == 1 || $LoanType == 2 || $LoanType == 3) {
            $cadastre_fee->setLoantype($LoanType);
        } else {
            throw  new \Exception('LoanType can use this values 1 , 2 or 3');
        }
        $this->cadastre_fee = $cadastre_fee;
    }

    public function setLoanPledgeStateFee($FixPrice=0)
    {
        try {
            $pledge_state_fee = new Cadastre_Fee();
            if ($FixPrice != 0) {
                $pledge_state_fee->setFixfee($FixPrice);
            }
            $this->pledge_state_fee = $pledge_state_fee;
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function setLoanOtherFee($FixPrice=0,$FeeType=1,$Percent=0.0,$FixMin=0,$FixMax=0,$LoanType=1)
    {
        $other_fee = new Loan_Other_Fee();

        $other_fee->setFixfee($FixPrice);
        if ($Percent >= 0 && $Percent < 100) {
            $other_fee->setPercent($Percent);
        } else {
            throw  new \Exception('Percent value incorrect');
        }
        if ($FixMin <= $FixMax) {
            $other_fee->setFixmin($FixMin);
            $other_fee->setFixmax($FixMax);
        }
        if ($LoanType == 1 || $LoanType == 2 || $LoanType == 3 || $LoanType == 4) {
            $other_fee->setLoantype($LoanType);
        } else {
            throw  new \Exception('LoanType can use this values 1 , 2 , 3 or 4');
        }
        if ($FeeType == 1 || $FeeType == 2 || $FeeType == 3) {
            $other_fee->setFeetype($FeeType);
        } else {
            throw  new \Exception('FeeType can use this values 1 , 2 or 3');
        }
        $this->loan_other_fee[] = $other_fee;
    }

    public function Calculate()
    {
        $result = $this->Monthly_Pay_Schedule($this->loan_amount,$this->loan_period,$this->loan_percent,false,false,false,0,0,0,0);
        $spreadsheet = new Financial();
        $dates = array();
        $mountly_pay = array();
        $other_fee = 0;
        $start_date = date('Y-m-d');
        $dates[0] = $start_date;
        $dates[1] = $start_date;
        foreach($result['other_fee'] as $value){
            $other_fee +=$value;
        }
        $mountly_pay[0] = -$this->getLoanamount();
        $mountly_pay[1] = $other_fee;

        foreach($result['schedule'] as $key => $value){
            if($key == 0){
                $mountly_pay[$key+2] = 0;
                $dates[$key+2] = 0;

            }elseif($key == 1) {
                $mountly_pay[$key+2] = 0;
                $dates[$key+2] = 0;
            }
            else{
                $mountly_pay[$key+2] = 0;
                $dates[$key+2] = 0;
            }
            //dd($dates,$mountly_pay);
            foreach ($value as $k => $v) {
                if ($k == "loan_pay_day") {
                    $dates[$key+2] = $v;
                } else {
                    if($k != "principal_balance") {
                        $mountly_pay[$key+2] += $v;
                    }
                }
            }
        }
        //dd($mountly_pay,$dates);
        $xirr = $spreadsheet->xirr($mountly_pay,$dates);
        $result['xirr'] = $xirr;
        return $result;
    }


	public function Monthly_Pay_Schedule($loan_amount,$period,$percent,$start_date=false,$prevPaymentDay=false,$loan_fee_day=false,$onetime_fee=0,$cashing_fee=0,$mount_fee=0,$year_insurance_fee=0)
	{
	    $this->loan_amount_interest = $this->loan_amount;
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

		$monthly_interest = array();
		$principal_balance = array();
        $monthly_interest_buffer = 0;
		$monthly_principal_amount = array();
		$perc = $percent/100;
		$nextPaymentDay = $this->Pay_Dates($loan_amount,$period,$start_date,$prevPaymentDay,$loan_fee_day);
		$diff_dates = $this->Pay_Dates_Diff($period,$nextPaymentDay);
        if(($this->repayment_method == null || $this->repayment_method == 1) && $this->loan_principial_grace_period == null && $this->loan_interest_grace_period ==null) {
            $annuity = $this->Annuity($loan_amount, $period, $start_date, $prevPaymentDay, $loan_fee_day, $percent,$this->loan_repayment_period);
        }
        else{
            $not_annuity = $this->Not_Annuity($loan_amount, $period, $this->loan_repayment_period,$this->loan_principial_grace_period);
        }
		for($i = 0 ; $i< $period ; $i++)
		{
		    if($this->interest_repayment_period == 1 || $this->interest_repayment_period == null) {        /* when interest is pay every mountly  */
                if($this->loan_interest_grace_period == null || $this->loan_interest_grace_period == 0) {
                    if ($i == 0) {
                        $monthly_interest[$i] = round($loan_amount * $perc / 365 * $diff_dates[$i], 2);
                    } else {
                        $monthly_interest[$i] = round($principal_balance[$i - 1] * $perc / 365 * $diff_dates[$i], 2);
                    }
                }else{
                    if(($i+1) <= $this->loan_interest_grace_period ) {
                        $monthly_interest[$i] = 0;
                    }else{
                        $monthly_interest[$i] = round($principal_balance[$i - 1] * $perc / 365 * $diff_dates[$i], 2);
                    }
                }
            }
            elseif($this->interest_repayment_period == 2 ){                         /* when interest is pay every 3 mounth  */
                if ($i == 0) {
                    $monthly_interest_buffer += round($loan_amount * $perc / 365 * $diff_dates[$i], 2);
                } else {
                    $monthly_interest_buffer += round($principal_balance[$i - 1] * $perc / 365 * $diff_dates[$i], 2);
                }
                if(($i+1) <= $this->loan_interest_grace_period ) {
                    $monthly_interest_buffer = 0;
                }
                if( ($i+1)%3==0 || $i == ($period-1) ){
                    $monthly_interest[$i] = $monthly_interest_buffer;
                    $monthly_interest_buffer = 0;
                }else{
                    $monthly_interest[$i] = 0;
                }
            }
            elseif($this->interest_repayment_period == 3 ){                            /* when interest is paid end of loan */
                if ($i == 0) {
                    $monthly_interest_buffer += round($loan_amount * $perc / 365 * $diff_dates[$i], 2);
                } else {
                    $monthly_interest_buffer += round($principal_balance[$i - 1] * $perc / 365 * $diff_dates[$i], 2);
                }
                if(($i+1) <= $this->loan_interest_grace_period ) {
                    $monthly_interest_buffer = 0;
                }
                if($i == ($period-1)){
                    if($this->loan_interest_grace_period == null || $this->loan_interest_grace_period == 0 || ($i+1) > $this->loan_interest_grace_period ) {
                        $monthly_interest[$i] = $monthly_interest_buffer;
                        $monthly_interest_buffer = 0;
                    }
                    else{
                        $monthly_interest[$i] = 0;
                        $monthly_interest_buffer = 0;
                    }
                }else{
                    $monthly_interest[$i] = 0;
                }
            }
            elseif($this->interest_repayment_period == 4 ){                                  /* when interest is paid first mounth */
                if ($i == 0) {
                    $monthly_interest_buffer += round($loan_amount * $perc / 365 * $diff_dates[$i], 2);
                } else {
                    $monthly_interest_buffer += round($principal_balance[$i - 1] * $perc / 365 * $diff_dates[$i], 2);
                }
                if($i == 0){
                    $monthly_interest[$i] = $monthly_interest_buffer;
                    //$monthly_interest_buffer = 0 ;
                }
                else{
                    $monthly_interest[$i] = 0;
                }
                if($i == ($period-1)){
                    $monthly_interest[0] = $monthly_interest_buffer;
                }
            }

            if($this->repayment_method == 1) {                                      /* WHEN Loan repayment method is ANNUITY  */
                if ($this->loan_repayment_period == 1) {
                    if($annuity >= $monthly_interest[$i]) {
                        $last_monthly_principal_amount = $annuity - $monthly_interest[$i];
                    }
                    else{
                        $last_monthly_principal_amount = 0;
                    }
                    $monthly_principal_amount[$i] = $last_monthly_principal_amount;
                    if ($i == 0) {
                        $principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
                    } else {
                        $principal_balance[$i] = $principal_balance[$i - 1] - $last_monthly_principal_amount;
                    }
                    if ($i == $period - 1) {
                        $monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
                        $principal_balance[$i] = 0;
                    }
                } elseif ($this->loan_repayment_period == 2) {
                    if (($i + 1) % 3 == 0) {
                        $last_monthly_principal_amount = $annuity - $monthly_interest[$i];
                        $monthly_principal_amount[$i] = $last_monthly_principal_amount;
                        if ($i == 0) {
                            $principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
                        } else {
                            $principal_balance[$i] = $principal_balance[$i - 1] - $last_monthly_principal_amount;
                        }
                        if ($i == $period - 1) {
                            $monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
                            $principal_balance[$i] = 0;
                        }
                    } else {
                        $last_monthly_principal_amount = 0;
                        $monthly_principal_amount[$i] = $last_monthly_principal_amount;
                        if ($i == 0) {
                            $principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
                        } else {
                            $principal_balance[$i] = $principal_balance[$i - 1] - $last_monthly_principal_amount;
                        }
                        if ($i == $period - 1) {
                            $monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
                            $principal_balance[$i] = 0;
                        }
                    }
                } elseif ($this->loan_repayment_period == 3) {
                    if ($i == ($period - 1)) {
                        $last_monthly_principal_amount = $annuity - $monthly_interest[$i];
                        $monthly_principal_amount[$i] = $last_monthly_principal_amount;
                        if ($i == 0) {
                            $principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
                        } else {
                            $principal_balance[$i] = $principal_balance[$i - 1] - $last_monthly_principal_amount;
                        }
                        if ($i == $period - 1) {
                            $monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
                            $principal_balance[$i] = 0;
                        }
                    } else {
                        $last_monthly_principal_amount = 0;
                        $monthly_principal_amount[$i] = $last_monthly_principal_amount;
                        if ($i == 0) {
                            $principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
                        } else {
                            $principal_balance[$i] = $principal_balance[$i - 1] - $last_monthly_principal_amount;
                        }
                        if ($i == $period - 1) {
                            $monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
                            $principal_balance[$i] = 0;
                        }
                    }
                }
            }
            else{                                                          /* WHEN Loan repayment method is NOT ANNUITY  */
                if ($this->loan_repayment_period == 1) {
                    $last_monthly_principal_amount = $not_annuity;
                    if($this->loan_principial_grace_period != null && $this->loan_principial_grace_period > 0){
                        if(($i+1) <= $this->loan_principial_grace_period){
                            $last_monthly_principal_amount = 0;
                        }
                    }
                    $monthly_principal_amount[$i] = $last_monthly_principal_amount;
                    if ($i == 0) {
                        $principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
                    } else {
                        $principal_balance[$i] = $principal_balance[$i - 1] - $last_monthly_principal_amount;
                    }
                    if ($i == $period - 1) {
                        $monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
                        $principal_balance[$i] = 0;
                    }
                } elseif ($this->loan_repayment_period == 2) {
                    if (($i + 1) % 3 == 0) {
                        $last_monthly_principal_amount = $not_annuity;
                        if($this->loan_principial_grace_period != null && $this->loan_principial_grace_period > 0){
                            if(($i+1) <= $this->loan_principial_grace_period){
                                $last_monthly_principal_amount = 0;
                            }
                        }
                        $monthly_principal_amount[$i] = $last_monthly_principal_amount;
                        if ($i == 0) {
                            $principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
                        } else {
                            $principal_balance[$i] = $principal_balance[$i - 1] - $last_monthly_principal_amount;
                        }
                        if ($i == $period - 1) {
                            $monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
                            $principal_balance[$i] = 0;
                        }
                    } else {
                        $last_monthly_principal_amount = 0;
                        if($this->loan_principial_grace_period != null && $this->loan_principial_grace_period > 0){
                            if(($i+1) <= $this->loan_principial_grace_period){
                                $last_monthly_principal_amount = 0;
                            }
                        }
                        $monthly_principal_amount[$i] = $last_monthly_principal_amount;
                        if ($i == 0) {
                            $principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
                        } else {
                            $principal_balance[$i] = $principal_balance[$i - 1] - $last_monthly_principal_amount;
                        }
                        if ($i == $period - 1) {
                            $monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
                            $principal_balance[$i] = 0;
                        }
                    }
                } elseif ($this->loan_repayment_period == 3) {
                    if ($i == ($period - 1)) {
                        $last_monthly_principal_amount = $not_annuity;

                        if($this->loan_principial_grace_period != null && $this->loan_principial_grace_period > 0){
                            if(($i+1) <= $this->loan_principial_grace_period){
                                $last_monthly_principal_amount = 0;
                            }
                        }
                        $monthly_principal_amount[$i] = $last_monthly_principal_amount;
                        if ($i == 0) {
                            $principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
                        } else {
                            $principal_balance[$i] = $principal_balance[$i - 1] - $last_monthly_principal_amount;
                        }
                        if ($i == $period - 1) {
                            $monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
                            $principal_balance[$i] = 0;
                        }
                    } else {
                        $last_monthly_principal_amount = 0;
                        if($this->loan_principial_grace_period != null && $this->loan_principial_grace_period > 0){
                            if(($i+1) <= $this->loan_principial_grace_period){
                                $last_monthly_principal_amount = 0;
                            }
                        }
                        $monthly_principal_amount[$i] = $last_monthly_principal_amount;
                        if ($i == 0) {
                            $principal_balance[$i] = $loan_amount - $last_monthly_principal_amount;
                        } else {
                            $principal_balance[$i] = $principal_balance[$i - 1] - $last_monthly_principal_amount;
                        }
                        if ($i == $period - 1) {
                            $monthly_principal_amount[$i] = $monthly_principal_amount[$i] + $principal_balance[$i];
                            $principal_balance[$i] = 0;
                        }
                    }
                }
            }
            $schedule[$i] = array("principal_balance" => $principal_balance[$i], "monthly_interest" => $monthly_interest[$i], "monthly_principal_amount" => $monthly_principal_amount[$i], "loan_pay_day" => $nextPaymentDay[$i + 1]);
            $this->loan_amount_interest +=$monthly_interest[$i];
		}
		if($this->interest_repayment_period == 4){
            $schedule[0]["monthly_interest"] = $monthly_interest[0];
        }
///		$monthly_service_fee = $this->Monthly_Service_Fee($loan_amount,$period,$principal_balance,$diff_dates,$mount_fee);
///		$annual_insurance_fee = $this->Annual_Insurance_Fee($loan_amount,$period,$year_insurance_fee);
		for($i=0 ;$i < $period; $i++)
		{
            if($i==0){
                $princ_bal = $loan_amount;
            }else{
                $princ_bal = $schedule[$i-1]['principal_balance'];
            }
            $other_fee_count = count($this->loan_other_fee);
            $this->loan_other_onetime_fee=array();
            for($j=0 ; $j<$other_fee_count ;$j++){
                if($this->loan_other_fee[$j]->{'fee_type'}==1){
                    if($this->loan_other_fee[$j]->{'fix_fee'} > 0){
                        $this->loan_other_onetime_fee[] = $this->loan_other_fee[$j]->Calc();
                    }else{
                        $other_fee_loantype = $this->loan_other_fee[$j]->{'loan_type'};
                        switch($other_fee_loantype){
                            case 1:
                                $this->loan_other_onetime_fee[] = $this->loan_other_fee[$j]->Calc($this->loan_amount);
                                break;
                            case 2:
                                $this->loan_other_onetime_fee[] = $this->loan_other_fee[$j]->Calc($princ_bal);
                                break;
                            case 3:
                                $this->loan_other_onetime_fee[] = $this->loan_other_fee[$j]->Calc($this->pledge);
                                break;
                            case 4:
                                $this->loan_other_year_fee[] = $this->loan_other_fee[$i]->Calc($this->loan_amount_interest);
                                break;
                        }
                    }
                }
            }
            if(!isset($year)) {
                $year = date("Y", strtotime($nextPaymentDay[$i + 1]));
                $this->loan_other_year_fee=array();
                $this->loan_other_mount_fee=array();
                for($j=0 ; $j<$other_fee_count ;$j++){
                    if($this->loan_other_fee[$j]->{'fee_type'}==3){
                        if($this->loan_other_fee[$j]->{'fix_fee'} > 0){
                            $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc();
                        }else{
                            $other_fee_loantype = $this->loan_other_fee[$j]->{'loan_type'};
                            switch($other_fee_loantype){
                                case 1:
                                    $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc($this->loan_amount);
                                    break;
                                case 2:
                                    $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc($princ_bal);
                                    break;
                                case 3:
                                    $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc($this->pledge);
                                    break;
                                case 4:
                                    $this->loan_other_year_fee[] = $this->loan_other_fee[$i]->Calc($this->loan_amount_interest);
                                    break;
                            }
                        }
                    }
                    if($this->loan_other_fee[$j]->{'fee_type'}==2){
                        if($this->loan_other_fee[$j]->{'fix_fee'} > 0){
                            $this->loan_other_mount_fee[] = $this->loan_other_fee[$j]->Calc();
                        }else{
                            $other_fee_loantype=$this->loan_other_fee[$j]->{'loan_type'};
                            switch($other_fee_loantype){
                                case 1:
                                    $this->loan_other_mount_fee[] = $this->loan_other_fee[$j]->Calc($this->loan_amount);
                                    break;
                                case 2:
                                    $this->loan_other_mount_fee[] = $this->loan_other_fee[$j]->Calc($princ_bal);
                                    break;
                                case 3:
                                    $this->loan_other_mount_fee[] = $this->loan_other_fee[$j]->Calc($this->pledge);
                                    break;
                                case 4:
                                    $this->loan_other_year_fee[] = $this->loan_other_fee[$i]->Calc($this->loan_amount_interest);
                                    break;
                            }
                        }
                    }
                }
                if(gettype($this->loan_service_fee) == "object")
                {
                    $loan_type = $this->loan_service_fee->getLoantype();
                    $value = $this->CalcValue($loan_type,$princ_bal);
                    $schedule[$i] += ["loan_service_fee"=>$this->loan_service_fee->Calc($value)];
                }
                if(gettype($this->collateral_insurance_fee) == "object")
                {
                    $loan_type = $this->collateral_insurance_fee->getLoantype();
                    //dd($loan_type);
                    $value = $this->CalcValue($loan_type,$princ_bal);
                    $schedule[$i] += ["collateral_insurance_fee"=>$this->collateral_insurance_fee->Calc($value)];
                }
                if(gettype($this->borrower_insurance_fee) == "object")
                {
                    $loan_type = $this->borrower_insurance_fee->getLoantype();
                    $value = $this->CalcValue($loan_type,$princ_bal);
                    $schedule[$i] += ["borrower_insurance_fee"=>$this->borrower_insurance_fee->Calc($value)];
                }
                for($t=0 ; $t<count($this->loan_other_year_fee);$t++)
                {
                    $schedule[$i] += ["loan_other_year_fee_".$t=>$this->loan_other_year_fee[$t]];
                }
                for($t=0 ; $t<count($this->loan_other_mount_fee);$t++)
                {
                    $schedule[$i] += ["loan_other_mount_fee_".$t=>$this->loan_other_mount_fee[$t]];
                }
            }
            else {
                if ($year != date("Y", strtotime($nextPaymentDay[$i + 1]))) {
                    $year = date("Y", strtotime($nextPaymentDay[$i + 1]));
                    $this->loan_other_year_fee=array();
                    $this->loan_other_mount_fee=array();
                    for($j=0 ; $j<$other_fee_count ;$j++){
                        if($this->loan_other_fee[$j]->{'fee_type'}==3){
                            if($this->loan_other_fee[$j]->{'fix_fee'} > 0){
                                $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc();
                            }else{
                                $other_fee_loantype=$this->loan_other_fee[$j]->{'loan_type'};
                                switch($other_fee_loantype){
                                    case 1:
                                        $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc($this->loan_amount);
                                        break;
                                    case 2:
                                        $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc($princ_bal);
                                        break;
                                    case 3:
                                        $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc($this->pledge);
                                        break;
                                    case 4:
                                        $this->loan_other_year_fee[] = $this->loan_other_fee[$i]->Calc($this->loan_amount_interest);
                                        break;
                                }
                            }
                        }
                        if($this->loan_other_fee[$j]->{'fee_type'}==2){
                            if($this->loan_other_fee[$j]->{'fix_fee'} > 0){
                                $this->loan_other_mount_fee[] = $this->loan_other_fee[$j]->Calc();
                            }else{
                                $other_fee_loantype=$this->loan_other_fee[$j]->{'loan_type'};
                                switch($other_fee_loantype){
                                    case 1:
                                        $this->loan_other_mount_fee[] = $this->loan_other_fee[$j]->Calc($this->loan_amount);
                                        break;
                                    case 2:
                                        $this->loan_other_mount_fee[] = $this->loan_other_fee[$j]->Calc($princ_bal);
                                        break;
                                    case 3:
                                        $this->loan_other_mount_fee[] = $this->loan_other_fee[$j]->Calc($this->pledge);
                                        break;
                                    case 4:
                                        $this->loan_other_year_fee[] = $this->loan_other_fee[$i]->Calc($this->loan_amount_interest);
                                        break;
                                }
                            }
                        }
                    }
                    if(gettype($this->loan_service_fee) == "object")
                    {
                        $loan_type = $this->loan_service_fee->getLoantype();
                        $value = $this->CalcValue($loan_type,$princ_bal);
                        $schedule[$i] += ["loan_service_fee" => $this->loan_service_fee->Calc($value)];
                    }
                    if(gettype($this->collateral_insurance_fee) == "object")
                    {
                        $loan_type = $this->collateral_insurance_fee->getLoantype();
                        $value = $this->CalcValue($loan_type,$princ_bal);
                        $schedule[$i] += ["collateral_insurance_fee"=>$this->collateral_insurance_fee->Calc($value)];
                    }
                    if(gettype($this->borrower_insurance_fee) == "object")
                    {
                        $loan_type = $this->borrower_insurance_fee->getLoantype();
                        $value = $this->CalcValue($loan_type,$princ_bal);
                        $schedule[$i] += ["borrower_insurance_fee"=>$this->borrower_insurance_fee->Calc($value)];
                    }
                    for($t=0 ; $t<count($this->loan_other_year_fee);$t++)
                    {
                        $schedule[$i] += ["loan_other_year_fee_".$t=>$this->loan_other_year_fee[$t]];
                    }
                    for($t=0 ; $t<count($this->loan_other_mount_fee);$t++)
                    {
                        $schedule[$i] += ["loan_other_mount_fee_".$t=>$this->loan_other_mount_fee[$t]];
                    }
                } else {
                    $this->loan_other_year_fee=array();
                    for($j=0 ; $j < $other_fee_count ;$j++){
                        if($this->loan_other_fee[$j]->{'fee_type'}==2){
                            if($this->loan_other_fee[$j]->{'fix_fee'} > 0){
                                $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc();
                            }else{
                                $other_fee_loantype=$this->loan_other_fee[$j]->{'loan_type'};
                                switch($other_fee_loantype){
                                    case 1:
                                        $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc($this->loan_amount);
                                        break;
                                    case 2:
                                        $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc($princ_bal);
                                        break;
                                    case 3:
                                        $this->loan_other_year_fee[] = $this->loan_other_fee[$j]->Calc($this->pledge);
                                        break;
                                    case 4:
                                        $this->loan_other_year_fee[] = $this->loan_other_fee[$i]->Calc($this->loan_amount_interest);
                                        break;
                                }
                            }
                        }
                    }
                    for($t=0 ; $t<count($this->loan_other_year_fee);$t++)
                    {
                        $schedule[$i] += ["loan_other_mount_fee_".$t=>$this->loan_other_year_fee[$t]];
                    }
                }
            }
			//$schedule[$i]+=["monthly_service_fee"=>$monthly_service_fee[$i],"annual_insurance_fee"=>$annual_insurance_fee[$i]];
		}
        $other_fee = array();
        if(gettype($this->loan_application_fee) == "object")
        {
            $loan_type = $this->loan_application_fee->getLoantype();
            $value = $this->CalcValue($loan_type, 0);
            $other_fee += ["loan_application_fee"=>$this->loan_application_fee->Calc($value)];
        }
        if(gettype($this->collateral_assessment_fee) == "object")
        {
            $loan_type = $this->collateral_assessment_fee->getLoantype();
            $value = $this->CalcValue($loan_type, 0);
            $other_fee += ["collateral_assessment_fee"=>$this->collateral_assessment_fee->Calc($value)];
        }
        if(gettype($this->cash_service_fee) == "object")
        {
            $loan_type = $this->cash_service_fee->getLoantype();
            $value = $this->CalcValue($loan_type,0);
            $other_fee += ["cash_service_fee"=>$this->cash_service_fee->Calc($value)];
        }
        if(gettype($this->collateral_maintenance_fee) == "object")
        {
            $loan_type = $this->collateral_maintenance_fee->getLoantype();
            $value = $this->CalcValue($loan_type,0);
            $other_fee += ["collateral_maintenance_fee"=>$this->collateral_maintenance_fee->Calc($value)];
        }
        if(gettype($this->notary_validation_fee) == "object")
        {
            $loan_type = $this->notary_validation_fee->getLoantype();
            $value = $this->CalcValue($loan_type,0);
            $other_fee += ["notary_validation_fee"=>$this->notary_validation_fee->Calc($value)];
        }
        if(gettype($this->pledge_state_fee) == "object")
        {
            $loan_type = $this->pledge_state_fee->getLoantype();
            $value = $this->CalcValue($loan_type,0);
            $other_fee += ["pledge_state_fee"=>$this->pledge_state_fee->Calc($value)];
        }
        if(gettype($this->cadastre_fee) == "object")
        {
            $loan_type = $this->cadastre_fee->getLoantype();
            $value = $this->CalcValue($loan_type,0);
            $other_fee += ["cadastre_fee"=>$this->cadastre_fee->Calc($value)];
        }
        for($t=0 ; $t<count($this->loan_other_onetime_fee);$t++)
        {
            $other_fee += ["loan_other_onetime_fee_".$t=>$this->loan_other_onetime_fee[$t]];
        }
		$all_in_schedule = array();
		$all_in_schedule = ["other_fee"=>$other_fee,"schedule"=>$schedule];

		return $all_in_schedule;
	}

	public function Annuity($loan_amount,$period,$start_date,$prevPaymentDay,$loan_fee_day,$percent,$loan_repayment_period)
	{

		$nextPaymentDay = $this->Pay_Dates($loan_amount,$period,$start_date,$prevPaymentDay,$loan_fee_day);
		$diff_dates = $this->Pay_Dates_Diff($period,$nextPaymentDay);

		$perc = $percent/100;
		$BZ = array();
		$CA = array();
		$CC = array();

		for($i=0 ; $i < $period ; $i++)
		{
		    if($loan_repayment_period ==1){
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
            elseif($loan_repayment_period == 2){
                if(($i+1)%3 == 0){
                    $BZ[$i] = round($perc/365*$diff_dates[$i],4);
                    $CA[$i] = $BZ[$i] + 1;
                    if($i != 0){
                        $CC_count = round($CC[$i-1] * $CA[$i] + 1,4);
                        $CC[$i] = $CC_count;
                    }
                    else{
                        $CC[$i] = round(0 * $CA[$i] + 1,4);
                    }
                }else{
                    $BZ[$i]=0;
                    //$BZ[$i] = round($perc/365*$diff_dates[$i],4);
                    $CA[$i] = $BZ[$i] + 1;
                    if($i != 0){
                        if($BZ[$i] !=0) {
                            $CC_count = round($CC[$i - 1] * $CA[$i] + 1, 4);
                            $CC[$i] = $CC_count;
                        }else{
                            $CC[$i] =$CC[$i-1];
                        }

                    }
                    else{
                        if($BZ[$i] !=0) {
                            $CC[$i] = round(0 * $CA[$i] + 1, 4);
                        }else{
                            $CC[$i]=0;
                        }
                    }
                }
            }
            elseif($loan_repayment_period == 3){
                if($i == ($period-1)){
                    $BZ[$i] = round($perc/365*$diff_dates[$i],4);
                    $CA[$i] = $BZ[$i] + 1;
                    if($i != 0){
                        $CC_count = round($CC[$i-1] * $CA[$i] + 1,4);
                        $CC[$i] = $CC_count;
                    }
                    else{
                        $CC[$i] = round(0 * $CA[$i] + 1,4);
                    }
                }else{
                    $BZ[$i]=0;
                    //$BZ[$i] = round($perc/365*$diff_dates[$i],4);
                    $CA[$i] = $BZ[$i] + 1;
                    if($i != 0){
                        if($BZ[$i] != 0) {
                            $CC_count = round($CC[$i - 1] * $CA[$i] + 1, 4);
                            $CC[$i] = $CC_count;
                        }else{
                            $CC[$i] =$CC[$i-1];
                        }

                    }
                    else{
                        if($BZ[$i] != 0) {
                            $CC[$i] = round(0 * $CA[$i] + 1, 4);
                        }else{
                            $CC[$i]=0;
                        }
                    }
                }
            }

		}
        //print_r($BZ);
		$summ = $this->ca_multyple($CA);
		//print_r($summ);
		//echo "\n";
		//print_r($CC);
		$AMSEKAN_HETVCHAR = round($loan_amount*$summ/$CC[$period-1],2);
        //print_r($AMSEKAN_HETVCHAR);die;
		return $AMSEKAN_HETVCHAR;

	}

    public function Not_Annuity($loan_amount,$period,$loan_repayment_period,$loan_principial_grace_period=null)
    {
        $period_fix = $period;
        if($loan_principial_grace_period == null || $loan_principial_grace_period == 0)
        {
            if($loan_repayment_period == 1) {
                $mountly_principial = round($loan_amount/$period_fix,2);
            }
            elseif($loan_repayment_period == 2){
                $dadar = ceil($period_fix/3);
                $mountly_principial = round($loan_amount/$dadar,2);
            }
            elseif($loan_repayment_period == 3){
                $dadar = 1;
                $mountly_principial = round($loan_amount/$dadar,2);
            }
            return $mountly_principial;
        }
        else {
            $count =0;
            if ($loan_repayment_period == 1) {
                if ($loan_principial_grace_period != null && $loan_principial_grace_period > 0) {
                    $period_fix = $period_fix - $loan_principial_grace_period;
                }
                $mountly_principial = round($loan_amount / $period_fix, 2);
            } elseif ($loan_repayment_period == 2) {
                for ($i = 0; $i < $period; $i++) {
                    if (($i + 1) % 3 == 0 && ($i + 1) > $loan_principial_grace_period) {
                        $count++;
                    } elseif ($i == $period_fix - 1 && ($i + 1) % 3 != 0) {
                        $count++;
                    }
                }
                $mountly_principial = round($loan_amount / $count, 2);
            } elseif ($loan_repayment_period == 3) {
                $dadar = 1;
                $mountly_principial = round($loan_amount / $dadar, 2);
            }
            return $mountly_principial;
        }
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

	public function CalcValue($v,$principal_balance){

        switch ($v) {
            case 1:
                $value = $this->loan_amount;
                break;
            case 2:
                $value = $principal_balance;
                break;
            case 3:
                $value = $this->pledge;
                break;
            case 4:
                $value = $this->loan_amount_interest;
                break;
            default:
                throw new \Exception(" Loantype proble  ");
                break;
        }
        return $value;
    }
}