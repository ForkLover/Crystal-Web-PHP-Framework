<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ShopPaypalModel.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'PaypalAPI.class.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'SiteCashModel.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'SiteCashLogModel.php';

class sitecashController extends Controller {
    private $_config;
    private $_acl;
    private $_template;
    private $_request;
    private $_session;
    // SiteCashModel
    private $_SiteCash;
    // AuthModel
    private $_Auth;
    
    
    /**
     * Recupère la configuration et permet la gestion de la config
     */
    private function _getConfig() {
        $this->_acl = AccessControlList::getInstance();
        $this->_template = Template::getInstance();
        $this->_request = Request::getInstance();
        
        $o = new Cache('sitecash');
        $this->_config = $o->getCache();
        $this->_config = (!is_array($this->_config)) ? array() : $this->_config;

        // On inject dans le template les valeurs
        // La devise du site
        $this->_template->devise =  isset($this->_config['devise']) ? $this->_config['devise'] : false;
        // Le ratio paypal
        $this->_template->ratio = isset($this->_config['ratio']) ? $this->_config['ratio'] : false;
        // Le ratio téléphone
        $this->_template->ratioallo = isset($this->_config['ratioAllo']) ? $this->_config['ratioAllo'] : false;
        // La config rentabiliweb
        $this->_template->rentabiliweb = isset($this->_config['rentabiliweb']) ? $this->_config['rentabiliweb'] : false;
        // StarPass
        $this->_template->starpassId =  isset($this->_config['starpassId']) ? $this->_config['starpassId'] : false;

        $this->_template->paypal = isset($this->_config['paypal']) ? $this->_config['paypal'] : false;

        // Test si le client est autorisé a configuré
        // On l'appel apres avoir set la config, de façon a ce que la config soit accessible directement
        if ($this->_acl->isAllowed('sitecash','config')) {
            if (isset($this->_request->data->ratio) &&
                isset($this->_request->data->allopay) &&
                isset($this->_request->data->devise) &&

                isset($this->_request->data->rentaDocId) &&
                isset($this->_request->data->rentaSiteId) &&

                isset($this->_request->data->starpassId) &&

                isset($this->_request->data->apiusername) &&
                isset($this->_request->data->apimdp) &&
                isset($this->_request->data->apisign)
            ) {
                // Enregistrement de la devise du site
                $this->_config['devis'] = clean($this->_request->data->devise, 'str');

                // Ratio devise = SiteCash
                $this->_request->data->ratio = preg_replace('#,#', '.', $this->_request->data->ratio);
                if (is_numeric($this->_request->data->ratio)) {
                    $this->_config['ratio'] = $this->_request->data->ratio;
                }

                // Ratio Allopass, Rentabiliweb
                $this->_request->data->allopay = preg_replace('#,#', '.', $this->_request->data->allopay);
                if (is_numeric($this->_request->data->allopay)) {
                    $this->_config['ratioAllo'] = $this->_request->data->allopay;
                }

                $this->_config['rentabiliweb']['docId'] = $this->_request->data->rentaDocId;
                $this->_config['rentabiliweb']['siteId'] = $this->_request->data->rentaSiteId;

                $this->_config['starpassId'] = $this->_request->data->starpassId;

                $this->_config['paypal']['apiusername'] = $this->_request->data->apiusername;
                $this->_config['paypal']['apimdp'] = $this->_request->data->apimdp;
                $this->_config['paypal']['apisign'] = $this->_request->data->apisign;

                $o->setCache($this->_config);
            }
        }
    }

    /**
     * Gestion de la config
     */
    public function manager() {
        $this->_request = Request::getInstance();
        $this->_template = Template::getInstance();
        $this->_acl = AccessControlList::getInstance();
        
        $page = Page::getInstance();
        
        if (!$this->_acl->isAllowed()) {
            $c = new errorController(); 
            return $c->e403();
        }
        
        $page->setLayout('admin');
        $page->setPageTitle('SiteCash::Gestion');

        $this->_getConfig();
        
        if (isset($this->_request->data->perso, $this->_request->data->action, $this->_request->data->somme)) {
            $this->_Auth = new AuthModel();
            $user = current($this->_Auth->search($this->_request->data->perso, true));
            if (!$user) {
                $this->_template->msg = '<div class="well well-small">Membre introuvable</div>';
            } else {
                $this->_SiteCash = new SiteCashModel();
                $this->_SiteCash->setUid($user->id);
                $amout = $this->_SiteCash->getAmout();
                $somme = (int) $this->_request->data->somme;

                switch($this->_request->data->action) {
                    case 'down':
                        try {
                            if ($this->_SiteCash->remove($somme)) {
                                $this->_template->msg = '<div class="well well-small">' .
                                        'Membre ' . clean($user->username, 'slug') . ' poss&egrave;de ' . $this->_SiteCash->getAmout() . ' Cr&eacute;dits.<br>' .
                                        'Retrait effectu&eacute;' .
                                    '</div>';
                            } else {
                                $this->_template->msg = '<div class="well well-small">Requ&ecirc;te &eacute;chou&eacute;</div>';
                            }
                        } catch(Exception $e) {
                            $this->_template->msg = '<div class="well well-small">' . $e->getMessage() . '</div>';
                        }
                    break;
                    case 'up':
                        try {
                            if ($this->_SiteCash->give($somme)) {
                                $this->_template->msg = '<div class="well well-small">' .
                                    'Membre ' . clean($user->username, 'slug') . ' poss&egrave;de ' . $this->_SiteCash->getAmout() . ' Cr&eacute;dits.<br>' .
                                    'Ajout effectu&eacute;' .
                                    '</div>';
                            } else {
                                $this->_template->msg = '<div class="well well-small">Requ&ecirc;te &eacute;chou&eacute;</div>';
                            }
                        } catch(Exception $e) {
                            $this->_template->msg = '<div class="well well-small">' . $e->getMessage() . '</div>';
                        }
                    break;
                }
            }
        }
        $this->_template->show(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'manager');
    }

    /**
     * Affichage des crédit sitecash et gestion
     * @auth true
     * @acl sitecash.config - Gestion de la configuration
     */
    public function index() {
        $this->_session = Session::getInstance();
        $this->_template = Template::getInstance();
        $page = Page::getInstance();

        $page->setHeaderCss('/assets/plugins/jquery-ui/themes/base/jquery.ui.all.css');
        $page->setHeaderCss('/assets/plugins/jquery-ui/themes/base/jquery.ui.slider.extras.css');

        $page->setHeaderJs('/assets/plugins/jquery-ui/ui/jquery.ui.core.js');
        $page->setHeaderJs('/assets/plugins/jquery-ui/ui/jquery.ui.widget.js');
        $page->setHeaderJs('/assets/plugins/jquery-ui/ui/jquery.ui.mouse.js');
        $page->setHeaderJs('/assets/plugins/jquery-ui/ui/jquery.ui.slider.js');

        $page->setHeaderJs('/assets/plugins/jquery-ui/ui/jquery.ui.selectToUISlider.js');
        $page->setHeaderCss('/assets/plugins/jquery-ui/themes/redmond/jquery-ui-1.7.1.custom.css');

        if (!$this->_session->isLogged()) {
            $this->_session->setFlash("Vous devez &egrave;tre connect&eacute;", 'warning');
            return Router::redirect("auth");
        }

        $this->_SiteCash = new SiteCashModel();
        $this->_SiteCash->setUid($this->_session->user('id'));

        if ($this->_SiteCash->isLocked()) {
            $this->_session->setFlash('Compte v&eacute;rrouill&eacute;', 'warning');
            return Router::url();
        }

        $this->_getConfig();
        $page->setPageTitle('Rechargement mon compte')
            ->setBreadcrumb('sitecash', 'Mes cr&eacute;dits');

            $account = new SiteCashModel();
            $account->setUid($this->_session->user('id'));
            $siteLog = new SiteCashLogModel();
            $siteLog->setUid($this->_session->user('id'));

        $this->_template->devise = $account->getDevise();
        $this->_template->account = $account->getAccount();
        $this->_template->logEvent = $siteLog->getEvent();
        $this->_template->show(__DIR__ . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'index');
    }

    public function thanks() {
        $this->_session = Session::getInstance();
        $this->_template = Template::getInstance();
        $page = Page::getInstance();

        if (!$this->_session->isLogged()) {
            $this->_session->setFlash("Vous devez &egrave;tre connect&eacute;", 'warning');
            return Router::redirect("auth");
        }
        $this->_SiteCash = new SiteCashModel();
        $this->_SiteCash->setUid($this->_session->user('id'));

        if ($this->_SiteCash->isLocked()) {
            $this->_session->setFlash('Compte v&eacute;rrouill&eacute;', 'warning');
            return Router::url();
        }

        $this->_getConfig();
        $page->setPageTitle('Merci pour votre rechargement ');


        /* Rentabiliweb */
        /***************/
        // PHP5 avec register_long_arrays désactivé?
        if (!isset($HTTP_GET_VARS)) {
            $HTTP_SESSION_VARS    = $_SESSION;
            $HTTP_SERVER_VARS     = $_SERVER;
            $HTTP_GET_VARS        = $_GET;
        }

        // Construction de la requête pour vérifier le code
        if (isset($HTTP_GET_VARS['code'])) {
            $query      = 'http://payment.rentabiliweb.com/checkcode.php?';
            $query     .= 'docId='.$this->_config['rentabiliweb']['docId'];
            $query     .= '&siteId='.$this->_config['rentabiliweb']['siteId'];
            $query     .= '&code='.$HTTP_GET_VARS['code'];
            $query     .= "&REMOTE_ADDR=".$HTTP_SERVER_VARS['REMOTE_ADDR'];
            $result     = @file($query);

            if(isset($result[0]) && trim($result[0]) == "OK") {
                try {
                    $this->_SiteCash->give($this->_config['ratioAllo'], 'Versement de '.$this->_config['ratioAllo'].' '.$this->_config['devis'].' via Rentabiliweb');
                    $this->_session->setFlash('Versement de '.$this->_config['ratioAllo'].' '.$this->_config['devis'].' via Rentabiliweb');
                } catch (Exception $e) {
                    $this->_session->setFlash($e->getMessage(), 'warning');
                }
            } else {
                //  $LOGGER->setLog('boutique', 'Recharche refusé, le code est incorrect', $this->_session->user('id'), 20);
                //$log->set('[Monnaie] Le code ', $this->_session->user('id'));
                $this->_session->setFlash('Le code est incorrect.', 'error');
            }
            return Router::redirect('sitecash/reload');
        }
        /* Rentabiliweb */
        /***************/

        /* StarPass */
        /***********/
        if (isset($_POST['code1'], $_POST['DATAS'])) {
            // Déclaration des variables
            $ident=$idp=$ids=$idd=$codes=$code1=$code2=$code3=$code4=$code5=$datas='';
            $idp = 82273;
            // $ids n'est plus utilisé, mais il faut conserver la variable pour une question de compatibilité
            $idd = 216064;
            $ident=$idp.";".$ids.";".$idd;
            // On récupère le(s) code(s) sous la forme 'xxxxxxxx;xxxxxxxx'
            if(isset($_POST['code1'])) $code1 = $_POST['code1'];
            if(isset($_POST['code2'])) $code2 = ";".$_POST['code2'];
            if(isset($_POST['code3'])) $code3 = ";".$_POST['code3'];
            if(isset($_POST['code4'])) $code4 = ";".$_POST['code4'];
            if(isset($_POST['code5'])) $code5 = ";".$_POST['code5'];
            $codes=$code1.$code2.$code3.$code4.$code5;
            // On récupère le champ DATAS
            if(isset($_POST['DATAS'])) $datas = $_POST['DATAS'];
            // On encode les trois chaines en URL
            $ident=urlencode($ident);
            $codes=urlencode($codes);
            $datas=urlencode($datas);

            /* Envoi de la requête vers le serveur StarPass
            Dans la variable tab[0] on récupère la réponse du serveur
            Dans la variable tab[1] on récupère l'URL d'accès ou d'erreur suivant la réponse du serveur */
            $get_f=@file( "http://script.starpass.fr/check_php.php?ident=$ident&codes=$codes&DATAS=$datas" );
            if(!$get_f) {
                exit( "Votre serveur n'a pas accès au serveur de StarPass, merci de contacter votre hébergeur. " );
            }
            $tab = explode("|",$get_f[0]);

            if(!$tab[1]) {
                $url = "http://script.starpass.fr/error.php";
            } else {
                $url = $tab[1];
            }

            // dans $pays on a le pays de l'offre. exemple "fr"
            $pays = $tab[2];
            // dans $palier on a le palier de l'offre. exemple "Plus A"
            $palier = urldecode($tab[3]);
            // dans $id_palier on a l'identifiant de l'offre
            $id_palier = urldecode($tab[4]);
            // dans $type on a le type de l'offre. exemple "sms", "audiotel, "cb", etc.
            $type = urldecode($tab[5]);
            // vous pouvez à tout moment consulter la liste des paliers à l'adresse : http://script.starpass.fr/palier.php

            // Si $tab[0] ne répond pas "OUI" l'accès est refusé
            // On redirige sur l'URL d'erreur
            if( substr($tab[0],0,3) != "OUI" )
            {
                $this->_session->setFlash('Le code est incorrect.', 'error');
                header( "Location: $url" );
                exit;
            } else {
                try {
                    $this->_SiteCash->give($this->_config['ratioAllo'], 'Versement de '.$this->_config['ratioAllo'].' '.$this->_config['devis'].' via StarPass');
                    $this->_session->setFlash('Versement de '.$this->_config['ratioAllo'].' '.$this->_config['devis'].' via StarPass');
                } catch (Exception $e) {
                    $this->_session->setFlash($e->getMessage(), 'warning');
                }
            }
        }
        /* StarPass */
        /***********/

        /* Paypal */
        /*********/
        //  Construction de la requête pour vérifier le payement
        if (isset($_GET['token']) && isset($_GET['PayerID'])) {
            $paypal = new PaypalAPI();
            $paypal->setApi($this->_config['paypal']['apiusername'], $this->_config['paypal']['apimdp'], $this->_config['paypal']['apisign']);
            $paypal->setCurrencyCode('EUR');
            $paypal->setLocaleCode('FR');

            $paypalAmt = $this->_session->read('paypal');
            if ($paypalAmt == 0 || !$paypalAmt) {
                return $this->reload();
            }
            $ItemPrice = ($paypalAmt / $this->_config['ratio']);
            $paypal->preprocess(
                $paypalAmt . ' ' . $this->_config['devise'],
                $ItemPrice,
                $this->_config['devise'].$paypalAmt.'-'.$this->_config['ratio'].'-'.$this->_session->user('id'),
                1
                );
            try {
                $check = $paypal->doExpressCheckoutPayment($_GET['token'], $_GET['PayerID']);
                if (!$check) {
                    $this->_session->setFlash('Transaction d&eacute;j&agrave; effectu&eacute;', 'warning');
                } else {
                    $avg = $check->amt * $this->_config['ratio'];
                    $this->_SiteCash->give($avg, 'Versement de ' . $avg . ' ' . $this->_config['devise'] . ' via Paypal');
                    $this->_session->setFlash('Versement de ' . $avg . ' ' . $this->_config['devise'] . ' via Paypal');
                }
            } catch (Exception $e) {
                $this->_session->setFlash('Erreur interne', 'warning');
            }

        return Router::redirect('sitecash/reload');
        }
        /* Paypal */
        /*********/

        return Router::redirect('sitecash');
    }


    public function checkout() {
        $this->_getConfig();
        $this->_session = Session::getInstance();
        $this->_template = Template::getInstance();
        /* UNUSED
        $form = Form::getInstance();
        $this->_acl = AccessControlList::getInstance();
        //*/
        $this->_request = Request::getInstance();
        $page = Page::getInstance();

        if (!$this->_session->isLogged()) {
            $this->_session->setFlash("Vous devez &egrave;tre connect&eacute;", 'warning');
            return Router::redirect("auth");
        }
        $this->_SiteCash = new SiteCashModel();
        $this->_SiteCash->setUid($this->_session->user('id'));

        if ($this->_SiteCash->isLocked()) {
            $this->_session->setFlash('Compte v&eacute;rrouill&eacute;', 'warning');
            return Router::url();
        }

        $this->_getConfig();
        $page->setPageTitle('Contr&ocirc;lle du rechargement');

        $paypal = new PaypalAPI();
        $paypal->setApi($this->_config['paypal']['apiusername'], $this->_config['paypal']['apimdp'], $this->_config['paypal']['apisign']);
        $paypal->setCurrencyCode('EUR');
        $paypal->setLocaleCode('FR');

        $devise = html_entity_decode($this->_template->devise, ENT_COMPAT, 'UTF-8');
        if (isset($this->_request->data->paypal)) {
            
            if (!is_numeric($this->_request->data->paypal)) {
                return $this->reload();
            }
            
            $ItemPrice = ($this->_request->data->paypal / $this->_template->ratio);
            $paypal->preprocess(
                $this->_request->data->paypal . ' ' . $devise,
                $ItemPrice,
                'rec-' . $this->_request->data->paypal.'-r-'.$this->_config['ratio'].'-u-'.$this->_session->user('id'),
                1
                );

            try {
                $cko = $paypal->setExpressCheckout('sitecash/thanks', 'sitecash/cancel');
                // die($cko);
                $this->_session->write('paypal', urldecode($this->_request->data->paypal));
                return Router::redirect($cko);
            } catch(Exception $e) {
                if (ENABLE_LOG) {
                    $this->_session->setFlash($e->getMessage() . ' ' . $this->_session->read('paypal') .  ' ' . $ItemPrice, 'warning');
                } else {
                    $this->_session->setFlash('Impossible de poursuivre la requ&egrave;te', 'warning');
                }
            }
        }
        return Router::redirect('sitecash');
    }

    public function cancel() {
        $this->_session = Session::getInstance();
        $this->_template = Template::getInstance();

        $page = Page::getInstance();
        $page->setPageTitle('Annulation du rechargement ');

        if (!$this->_session->isLogged()) {
            $this->_session->setFlash("Vous devez &egrave;tre connect&eacute;", 'warning');
            return Router::redirect("auth");
        }
        
        $this->_SiteCash = new SiteCashModel();
        $this->_SiteCash->setUid($this->_session->user('id'));

        if ($this->_SiteCash->isLocked()) {
            $this->_session->setFlash('Compte v&eacute;rrouill&eacute;', 'warning');
            return Router::url();
        }

        $this->_getConfig();
        $page->setPageTitle('Contr&ocirc;lle du rechargement');

        if (isset($_GET['token']) && $this->_session->read('paypal')) {
            $paypal = new PaypalAPI();
            $paypal->setApi($this->_config['paypal']['apiusername'], $this->_config['paypal']['apimdp'], $this->_config['paypal']['apisign']);

            $paypal->setCurrencyCode('EUR');
            $paypal->setLocaleCode('FR');
            if ($paypal->cancel($_GET['token'])) {
                $this->_session->del('paypal');
            }
        }
        $this->_session->setFlash('Commande via Paypal annulée', 'warning');
        return Router::redirect('sitecash');
    }

    public function error() {
        $this->_session = Session::getInstance();
        $this->_session->setFlash('Erreur lors de la commande', 'warning');
        return Router::redirect('sitecash');
    }

    public function iframe(){
        $this->_getConfig();
        $page = Page::getInstance();
        $page->setLayout('empty');
        if (isset($this->_config['starpassId']) && !empty($this->_config['starpassId'])) {
            echo '<div id="starpass_' . $this->_config['starpassId'] . '"></div><script type="text/javascript" src="http://script.starpass.fr/script.php?idd=' . $this->_config['starpassId'] . '&amp;verif_en_php=1&amp;datas="></script><noscript>Veuillez activer le Javascript de votre navigateur s\'il vous pla&icirc;t.<br /><a href="http://www.starpass.fr/">Micro Paiement StarPass</a></noscript>';
        }
    }
}
