<?php
class HistoryDetailController extends FrontController
{
    public $php_self = 'history-detail';

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign(array(
            'id' => Tools::getValue('id'),
            'order' => Tools::getValue('order'),
            'ordermessage' => $this->context->cookie->ordermessage
        ));

        unset($this->context->cookie->ordermessage);

        $webServiceDiva = new WebServiceDiva('<ACTION>DETAIL_PIECE', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<PICOD>'.Tools::getValue('picod').'<numero>'.Tools::getValue('id'));

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->lignes) {
                foreach ($datas->lignes as $ligne) {
                    if ($ligne->ref && $product_id = Product::getProductByReference($ligne->ref)) {
                        $ligne->link = $this->context->link->getProductLink($product_id);
                    } else {
                        $ligne->link = "";
                    }
                }
                $this->context->smarty->assign('lignes', $datas->lignes);
            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

        $this->setTemplate(_PS_THEME_DIR_.'history-detail.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addCSS(array(
            _THEME_CSS_DIR_.'history.css',
        ));
    }
}