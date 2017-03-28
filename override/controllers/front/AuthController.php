<?php
class AuthController extends AuthControllerCore
{
	/**
     * {@inheritdoc}
     */
    protected function processSubmitLogin()
    {
        $login = trim(Tools::getValue('login'));
        $passwd = trim(Tools::getValue('passwd'));

        $webServiceDiva = new WebServiceDiva('<ACTION>IDENTIFICATION', '<DOS>1<LOGIN>'.$login.'<WEBPASS>'.$passwd);

        try {
            $datas = $webServiceDiva->call();
            if ($datas && $datas->contact_trouve == 1 && $datas->identification == 1) {
                $customer = new Customer();
                if (!$customer->getByEmail($datas->email)) {
                    $customer->active = 1;
                    $customer->firstname = $datas->prenom ? $datas->prenom : '.';
                    $customer->lastname = $datas->nom ? $datas->nom : '.';
                    $customer->email = $datas->email;
                    $customer->active = 1;
                    $customer->passwd = Tools::encrypt($passwd);
                    $customer->add();
                } else {
                    $customer->firstname = $datas->prenom ? $datas->prenom : '.';
                    $customer->lastname = $datas->nom ? $datas->nom : '.';
                    $customer->passwd = Tools::encrypt($passwd);
                    $customer->update();
                }

                $_POST['email'] = $customer->email;
                $this->context->cookie->__set('tiers', $datas->tiers);

            } else {
                $_POST['email'] = 'fail@erreur.com';
            }

            parent::processSubmitLogin();

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }
    }
}
