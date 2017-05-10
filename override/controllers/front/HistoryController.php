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
                'Date_Year' => Tools::getValue('Date_Year')
            ));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit'))
        {
            $webServiceDiva = new WebServiceDiva('<ACTION>ENTETES', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<PICOD>'.Tools::getValue('picod').'<ANNEE>'.Tools::getValue('Date_Year'));

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