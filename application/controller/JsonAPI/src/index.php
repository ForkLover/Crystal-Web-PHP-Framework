<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'libs' .DIRECTORY_SEPARATOR . 'JSONAPI.class.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'model' .DIRECTORY_SEPARATOR . 'JsonServerModel.php';

class jsonapiController extends Controller {

    public function index() {
       $acl = AccessControlList::getInstance();
       $request = Request::getInstance();
       if ($acl->isAllowed('jsonapi', 'manager')){
           if (isset($request->data->host, $request->data->port, $request->data->login, $request->data->password, $request->data->salt)){
               $jsonapi = new JSONAPI('37.187.88.196', 26033, 'devphp', 'emilie586', '');
                $callback = $jsonapi->call("getPlayerCount");
                if (isset($callback['result']) && $callback['result'] == 'success'){
                    die(json_encode($callback));
                } else {
                    die(json_encode(array('result' => false)));
                }
           }
           
           return Router::redirect('jsonapi/manager');
       }
       
       $c = new errorController();
       return $c->e403();
    }

    public function manager(){
        $page = Page::getInstance();
        $page->setBreadcrumb('jsonapi', 'JsonAPI');
        $page->setPageTitle("JsonAPI::Gestion");
        $template = Template::getInstance();
        $request = Request::getInstance();
        $template->session = $session = Session::getInstance();
        $acl = AccessControlList::getInstance();
        $form = Form::getInstance();

        if (!$session->isLogged()){
            return Router::redirect('auth');
        }

        if (!$acl->isAllowed()){
            $c = new errorController();
            return $c->e403();
        }
        
        $template->act = (isset($request->params['act'])) ? $request->params['act'] : false;
        
        $jsonServer = new JsonServerModel();
        $page->setLayout('admin');

        /**
         * Edition d'un serveur
         */
        if (isset($request->params['id'])){
            
            $sid = (int) $request->params['id'];
            $server = $jsonServer->getServerById($sid);
            if (!$server) {
                return Router::redirect('jsonapi/manager');
            }
    
            if (!isset($request->data->name, $request->data->server, $request->data->port, $request->data->user, $request->data->password, $request->data->salt)) {
                $request->data = $server;
            } elseif (isset($request->data->name, $request->data->server, $request->data->port, $request->data->user, $request->data->password, $request->data->salt)) {
                if (!strlen($request->data->name)) {
                    $form->setErrors(array('name' => 'Veuillez indiquer un nom'));
                } else {
                    $name = trim($request->data->name);
                    $server = trim($request->data->server);
                    $port = (int) $request->data->port;
                    $user = $request->data->user;
                    $password = $request->data->password;
                    $salt = $request->data->salt;
    
                    $jsonapi = new JSONAPI($server, $port, $user, $password, $salt);
                    $serverInfo = $jsonapi->call("getPlayerCount");
    
                    if (is_null($serverInfo) || $serverInfo['result'] != 'success') {
                        echo '<div class="well well-small">' .
                            'Les serveur est &eacute;teint ou la connection est refus&eacute;' .
                            '</div>';
                    } elseif ($jsonServer->editServer($sid, $name, $server, $port, $user, $password, $salt)) {
                        return Router::redirect('jsonapi/manager');
                    }
                }
            }
        }

        /**
         * Ajout d'un serveur
         */
        elseif (isset($request->data->name, $request->data->server, $request->data->port, $request->data->user, $request->data->password, $request->data->salt)) {
            if (!strlen($request->data->name)) {
                $form->setErrors(array('name' => 'Veuillez indiquer un nom'));
            } else {
                $name = trim($request->data->name);
                $server = trim($request->data->server);
                $port = (int) $request->data->port;
                $user = $request->data->user;
                $password = $request->data->password;
                $salt = $request->data->salt;

                $jsonapi = new JSONAPI($server, $port, $user, $password, $salt);
                $serverInfo = $jsonapi->call("getPlayerCount");
                if (is_null($serverInfo) || $serverInfo['result'] != 'success') {
                    echo '<div class="well well-small">' .
                        'Les serveur est &eacute;teint, lent ou la connection est refus&eacute;' .
                        '</div>';
                } elseif ($jsonServer->addServer($name, $server, $port, $user, $password, $salt)) {
                    return Router::redirect('jsonapi/manager');
                }
            }
        }

        /**
         * Suppression d'un serveur
         */
        elseif ($session->token()){
            if ($jsonServer->delete((int) $request->params['id'])){
                return Router::redirect('jsonapi/manager');
            }
        }

        $template->serverList = $jsonServer->getServerList();
        $template->show('manager');
    }
}
