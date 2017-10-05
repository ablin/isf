<?php
class HistoryPrintController extends FrontController
{
    public $php_self = 'history-print';

    public function initContent()
    {
        $webServiceDiva = new WebServiceDiva('<ACTION>DL_PDF', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<LOGIN>'.$this->context->cookie->login.'<PICOD>'.Tools::getValue('picod').'<NUMERO>'.Tools::getValue('id'));

        try {
            $datas = $webServiceDiva->call();

            if ($datas) {
                header("Content-type: application/pdf");
                header("Content-Disposition: attachment; filename=".$datas->nomPDF);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.file");
                curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.file");
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                 
                curl_setopt($ch, CURLOPT_URL, $datas->URL);

                $result = curl_exec($ch);
                echo $result;
                curl_close($ch);
            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }
    }
}