<?php
class WebServiceDiva
{

    private $client;
    private $action;
    private $param;
    private $logger;

    public function __construct($action, $param) 
    {
        $this->client = new SoapClient("http://interface59.ath.cx:8081/WebServiceDiva/WebServiceDiva.asmx?WSDL");
        $this->action = $action;
        $this->param = $param;
        $this->logger = new FileLogger(0);

        $this->logger->setFilename(_PS_ROOT_DIR_."/log/".date('Ymd')."_ws.log");
    }

    public function call()
    {   
        $response = $this->client->__call(
            'WebServiceDiva',
            array(
                'WebServiceDiva' => $this
            )
        );

        $this->logger->logDebug("Action : ".$this->action." / Params : ".$this->param);
        $this->logger->logDebug("Retour :".$response->retour);

        return json_decode($response->retour);
    }

}