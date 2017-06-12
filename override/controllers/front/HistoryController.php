<?php
class HistoryController extends HistoryControllerCore
{
    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        if (Tools::isSubmit('submit'))
        {
            $this->context->smarty->assign(array(
                'submit' => 1,
                'picod' => Tools::getValue('picod'),
                'begin_Day' => Tools::getValue('begin_Day'),
                'begin_Month' => Tools::getValue('begin_Month'),
                'begin_Year' => Tools::getValue('begin_Year'),
                'end_Day' => Tools::getValue('end_Day'),
                'end_Month' => Tools::getValue('end_Month'),
                'end_Year' => Tools::getValue('end_Year')
            ));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit'))
        {
            $webServiceDiva = new WebServiceDiva('<ACTION>ENTETES', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<PICOD>'.Tools::getValue('picod').'<DEBUT>'.Tools::getValue('begin_Year').sprintf('%02d', Tools::getValue('begin_Month')).sprintf('%02d', Tools::getValue('begin_Day')).'<FIN>'.Tools::getValue('end_Year').sprintf('%02d', Tools::getValue('end_Month')).sprintf('%02d', Tools::getValue('end_Day')));

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
}