<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class PasswordController extends PasswordControllerCore
{
    /**
     * Start forms process
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        if (Tools::isSubmit('email')) {
            if (!($email = trim(Tools::getValue('email'))) || !Validate::isEmail($email)) {
                $this->errors[] = Tools::displayError('Invalid email address.');
            } else {
                $webServiceDiva = new WebServiceDiva('<ACTION>MDPREINIT', '<DOS>1<EMAIL>'.$email);

                try {
                    $datas = $webServiceDiva->call();

                    if ($datas && $datas->trouve == 0) {
                        $this->errors[] = Tools::displayError('The email address you entered is not associated with any account.');
                    } elseif ($datas && $datas->trouve == 1) {
                        $this->context->smarty->assign(array('confirmation' => 1));
                    } else {
                        $this->errors[] = Tools::displayError('An error occurred while sending the email.');
                    }

                } catch (SoapFault $fault) {
                    throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
                }
            }
        } elseif (Tools::isSubmit('passwd')) {
            if (($passwd = trim(Tools::getValue('passwd'))) !== ($confirmPasswd = trim(Tools::getValue('confirm-passwd')))) {
                $this->errors[] = Tools::displayError('Passwords do not match.');
            } else {
                $webServiceDiva = new WebServiceDiva('<ACTION>MDPMAJ', '<DOS>1<TOKEN>'.trim(Tools::getValue('token')).'<WEBPASS>'.sha1($passwd));

                try {
                    $datas = $webServiceDiva->call();

                    if ($datas && $datas->trouve == 0) {
                        $this->errors[] = Tools::displayError('The email address you entered is not associated with any account.');
                    } elseif ($datas && $datas->trouve == 1) {
                        $this->context->smarty->assign(array('confirmation' => 2));
                    } else {
                        $this->errors[] = Tools::displayError('An error occurred while update password.');
                    }

                } catch (SoapFault $fault) {
                    throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
                }
            }
        }
    }

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $password_token = trim(Tools::getValue('token'));
        if ($password_token) {
            $webServiceDiva = new WebServiceDiva('<ACTION>MDPTOKEN', '<DOS>1<TOKEN>'.$password_token);
            try {
                $datas = $webServiceDiva->call();

                if ($datas && $datas->trouve == 1) {
                    $this->context->smarty->assign(array('password_token' => $password_token));
                } else {
                    $this->errors[] = Tools::displayError('The link you entered is not associated with any account.');
                }

            } catch (SoapFault $fault) {
                throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
            }
        }
        parent::initContent();
        $this->setTemplate(_PS_THEME_DIR_.'password.tpl');
    }
}
