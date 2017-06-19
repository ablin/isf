<?php
class HistoryController extends HistoryControllerCore
{
    private $picod = 2;
    private $begin_Year = '';
    private $begin_Month = '';
    private $begin_Day = '';
    private $end_Year = '';
    private $end_Month = '';
    private $end_Day = '';

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign(array(
            'submit' => 1,
            'picod' => $this->picod,
            'begin_Day' => $this->begin_Day,
            'begin_Month' => $this->begin_Month,
            'begin_Year' => $this->begin_Year,
            'end_Day' => $this->end_Day,
            'end_Month' => $this->end_Month,
            'end_Year' => $this->end_Year
        ));
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit'))
        {
            $this->picod = Tools::getValue('picod');
            $this->begin_Year = Tools::getValue('begin_Year');
            $this->begin_Month = Tools::getValue('begin_Month');
            $this->begin_Day = Tools::getValue('begin_Day');
            $this->end_Year = Tools::getValue('end_Year');
            $this->end_Month = Tools::getValue('end_Month');
            $this->end_Day = Tools::getValue('end_Day');
        } else {
            $this->begin_Year = date('Y', strtotime('-1 month'));
            $this->begin_Month = date('m', strtotime('-1 month'));
            $this->begin_Day = date('d', strtotime('-1 month'));
            $this->end_Year = date('Y');
            $this->end_Month = date('m');
            $this->end_Day = date('d');
        }

        $webServiceDiva = new WebServiceDiva('<ACTION>ENTETES', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<PICOD>'.$this->picod.'<DEBUT>'.$this->begin_Year.sprintf('%02d', $this->begin_Month).sprintf('%02d', $this->begin_Day).'<FIN>'.$this->end_Year.sprintf('%02d', $this->end_Month).sprintf('%02d', $this->end_Day));

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->entete) {
                $this->context->smarty->assign('entetes', $datas->entete);
            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }
    }
}