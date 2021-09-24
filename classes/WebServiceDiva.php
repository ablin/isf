<?php
//5N8vQVHFTJUAN4is
class WebServiceDiva
{

    private $client;
    private $action;
    private $param;
    private $logger;

    public function __construct($action, $param) 
    {
        //$this->client = new SoapClient("http://interface59.ath.cx:8081/WebServiceDiva/WebServiceDiva.asmx?WSDL");
        //$this->client = new SoapClient("http://192.168.0.107:8081/WebServiceDiva/WebServiceDiva.asmx?WSDL");
        $this->client = new SoapClient("http://127.0.0.1:8090/ISF/WebServiceDiva.asmx?WSDL", array("trace" => 1, "exception" => 0));
        $this->action = $action;
        $this->param = $param;

        $this->logger = new FileLogger(0);

        $this->logger->setFilename(_PS_ROOT_DIR_."/log/".date('Ymd')."_ws.log");
        $this->logger->logDebug("Action : ".$this->action." / Params : ".$this->param);
    }

    public function call()
    {
        $response = $this->client->__call(
            'WebServiceDiva',
            array(
                'WebServiceDiva' => $this
            )
        );

        $this->logger->logDebug("Retour :".$response->retour);

        if ($return = json_decode($response->retour)) {
            return $return;
        } else {
            //traiter l'erreur
        }
    }

}
