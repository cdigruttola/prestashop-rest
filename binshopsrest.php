<?php
/**
 * BINSHOPS REST API
 *
 * @author BINSHOPS | Best In Shops
 * @copyright BINSHOPS | Best In Shops
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * Best In Shops eCommerce Solutions Inc.
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Binshopsrest extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'binshopsrest';
        $this->tab = 'others';
        $this->version = '2.2.3';
        $this->author = 'Binshops';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PrestaShop REST API');
        $this->description = $this->l('This module exposes REST API endpoints for your Prestashop website.');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->module_key = 'b3c3c0c41d0223b9ff10c87b8acb65f5';
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('BINSHOPSREST_LIVE_MODE', false);

        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') && $this->registerHook('moduleRoutes');
    }

    public function uninstall()
    {
        Configuration::deleteByName('BINSHOPSREST_LIVE_MODE');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitBinshopsrestModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBinshopsrestModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'BINSHOPSREST_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'BINSHOPSREST_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'BINSHOPSREST_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'BINSHOPSREST_LIVE_MODE' => Configuration::get('BINSHOPSREST_LIVE_MODE', true),
            'BINSHOPSREST_ACCOUNT_EMAIL' => Configuration::get('BINSHOPSREST_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'BINSHOPSREST_ACCOUNT_PASSWORD' => Configuration::get('BINSHOPSREST_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookModuleRoutes()
    {
        return [
            'module-binshopsrest-login' => [
                'rule' => 'rest/login',
                'keywords' => [],
                'controller' => 'login',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-register' => [
                'rule' => 'rest/register',
                'keywords' => [],
                'controller' => 'register',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-logout' => [
                'rule' => 'rest/logout',
                'keywords' => [],
                'controller' => 'logout',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-accountinfo' => [
                'rule' => 'rest/accountInfo',
                'keywords' => [],
                'controller' => 'accountinfo',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-accountedit' => [
                'rule' => 'rest/accountedit',
                'keywords' => [],
                'controller' => 'accountedit',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-bootstrap' => [
                'rule' => 'rest/bootstrap',
                'keywords' => [],
                'controller' => 'bootstrap',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-lightbootstrap' => [
                'rule' => 'rest/lightbootstrap',
                'keywords' => [],
                'controller' => 'lightbootstrap',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-cartitems' => [
                'rule' => 'rest/cartitems',
                'keywords' => [],
                'controller' => 'cartitems',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-productdetail' => [
                'rule' => 'rest/productdetail',
                'keywords' => [],
                'controller' => 'productdetail',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-addtocart' => [
                'rule' => 'rest/addtocart',
                'keywords' => [],
                'controller' => 'addtocart',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-removefromcart' => [
                'rule' => 'rest/removefromcart',
                'keywords' => [],
                'controller' => 'removefromcart',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-categoryproducts' => [
                'rule' => 'rest/categoryProducts',
                'keywords' => [],
                'controller' => 'categoryproducts',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-productsearch' => [
                'rule' => 'rest/productSearch',
                'keywords' => [],
                'controller' => 'productsearch',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-checkout' => [
                'rule' => 'rest/checkout',
                'keywords' => [],
                'controller' => 'checkout',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-featuredproducts' => [
                'rule' => 'rest/featuredproducts',
                'keywords' => [],
                'controller' => 'featuredproducts',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-address' => [
                'rule' => 'rest/address',
                'keywords' => [],
                'controller' => 'address',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-alladdresses' => [
                'rule' => 'rest/alladdresses',
                'keywords' => [],
                'controller' => 'alladdresses',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-addressform' => [
                'rule' => 'rest/addressform',
                'keywords' => [],
                'controller' => 'addressform',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-carriers' => [
                'rule' => 'rest/carriers',
                'keywords' => [],
                'controller' => 'carriers',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-setaddresscheckout' => [
                'rule' => 'rest/setaddresscheckout',
                'keywords' => [],
                'controller' => 'setaddresscheckout',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-setcarriercheckout' => [
                'rule' => 'rest/setcarriercheckout',
                'keywords' => [],
                'controller' => 'setcarriercheckout',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-paymentoptions' => [
                'rule' => 'rest/paymentoptions',
                'keywords' => [],
                'controller' => 'paymentoptions',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-resetpasswordemail' => [
                'rule' => 'rest/resetpasswordemail',
                'keywords' => [],
                'controller' => 'resetpasswordemail',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-resetpasswordcheck' => [
                'rule' => 'rest/resetpasswordcheck',
                'keywords' => [],
                'controller' => 'resetpasswordcheck',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-resetpasswordenter' => [
                'rule' => 'rest/resetpasswordenter',
                'keywords' => [],
                'controller' => 'resetpasswordenter',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-resetpasswordbyemail' => [
                'rule' => 'rest/resetpasswordbyemail',
                'keywords' => [],
                'controller' => 'resetpasswordbyemail',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
            'module-binshopsrest-hello' => [
                'rule' => 'rest',
                'keywords' => [],
                'controller' => 'hello',
                'params' => [
                    'fc' => 'module',
                    'module' => 'binshopsrest'
                ]
            ],
        ];
    }
}
