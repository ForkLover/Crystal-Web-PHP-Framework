<?php
/*
  ______            _ _                _
 |  ____|          | | |              | |
 | |__ ___  ___  __| | |__   __ _  ___| | __
 |  __/ _ \/ _ \/ _` | '_ \ / _` |/ __| |/ /
 | | |  __/  __/ (_| | |_) | (_| | (__|   <
 |_|  \___|\___|\__,_|_.__/ \__,_|\___|_|\_\
            By Devphp
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'FeedbackModel.php';

class feedbackController extends Controller {
    private $catList = array(
        'bug' => 'Bug',
        'question' => 'Question',
        'feature' => 'Demande de fonctionnalit&eacute;'
    ); 

    public function index(){
        $session = Session::getInstance();
        $request = Request::getInstance();
        $template = Template::getInstance();
        $form = Form::getInstance();
        $page = Page::getInstance();
        $page->setPageTitle('Feedback');
        $page->setBreadcrumb('feedback', 'Feedback');
        $page->setHeaderCss('/assets/controller/feedback/style.css');
        $page->setHeaderJs('/assets/controller/feedback/app.js');

        if (isset($request->params['id'])){
            return $this->post($request->params['id']);
        }
        if (!isset($request->params['cat'])){
            $request->params['cat'] = 'bug';
        } else {
            $request->params['cat'] = strtolower($request->params['cat']);
            if (!isset($this->catList[$request->params['cat']])) {
                $request->params['cat'] = 'bug';
            }
        }


        $feedback = new FeedbackModel();
      //  $feedback->isSpamReply(1);

        $error = array();

        /**
         * Marque comme spam
         * @note faire un système qui enregistre l'ip du client (id;ip) et la supprimé lors du deuxieme clic
         * limité le nombre de marquage pour éviter le spam de spam ;-)
         */
        if (isset($request->data->spam)){
            $id = (int) $request->data->spam;
            $post = $feedback->isSpamPost($id);
            if ($post){
                die((string) $post);
            }
            die((string) '0');
        }

        /**
         * Marque comme spam
         * @note faire un système qui enregistre l'ip du client (id;ip) et la supprimé lors du deuxieme clic
         * limité le nombre de marquage pour éviter le spam de spam ;-)
         */
        if (isset($request->data->spamReply)){
            $id = (int) $request->data->spamReply;
            $reply = $feedback->isSpamReply($id);
            if ($reply){
                die((string) $reply);
            }
            die((string) '0');
        }

        /**
         * Création d'une requête
         */
        if (isset($request->data->cat, $request->data->subject, $request->data->description, $request->data->mail)) {
            if (!Securite::isMail($request->data->mail)) {
                $error['mail'] = 'Valeur incorrect';
            }
            if (strlen($request->data->subject) < 3) {
                $error['subject'] = 'Sujet trop court';
            }
            if (strlen($request->data->subject) > 50) {
                $error['subject'] = 'Sujet trop long';
            }
            if (strlen($request->data->description) < 3) {
                $error['description'] = 'Description trop court';
            }

            if (!count($error) && $this->authorized('post', 5)){
                $feedback->
                    setRequest($request->data->cat, $request->data->subject, $request->data->description, $request->data->mail);
                $this->action('post');
                return Router::redirect('feedback/cat/cat:' . $request->data->cat);
            } else {
                $session->setFlash('Veuillez patient&eacute; avant de poster &agrave; nouveau.', 'warning');
            }
        }

        /**
         * Réponse a une requête
         */
        if (isset($request->data->description, $request->data->mail, $request->data->reply)) {
            if (!Securite::isMail($request->data->mail)) {
                $error['mail'] = 'Adresse e-mail incorrect';
            }
            if (strlen($request->data->description) < 3) {
                $error['description'] = 'R&eacute;ponse trop courte';
            }

            if (!count($error)){
                if (!$this->authorized('reply-' . $request->data->reply, 5)){
                    die(json_encode(array('error' => array('message' => 'Veuillez patient&eacute; avant de poster &agrave; nouveau.'))));
                }
                if ($feedback->
                    replyTo($request->data->reply, $request->data->description, $request->data->mail)) {
                    $this->action('reply-' . $request->data->reply);
                    die(json_encode(array('success' => array('message' => 'R&eacute;ponse enregistr&eacute;'))));
                } else {
                    die(json_encode(array('error' => array('message' => 'Oops.. Une erreur c\'est produite.a'))));
                }
            } else {
                die(json_encode(array('error' => $error)));
            }
        }

        $form->setErrors($error);
        $template->page     = $request->page;
        $template->cat      = $request->params['cat'];
        $std = new stdClass();
        $std->cat = $request->params['cat'];
        $request->data = (isset($request->data->cat)) ? $request->data : $std;
        $template->listen   = $feedback->getList($template->cat, $request->page);
        if (__ISAJAX) {
            if (!count($template->listen)){die;}
            $page->setLayout('empty');
            return $template->show('listen.ajax');
        }
        $template->show('index');
    }


    private function post($id){
        $page = Page::getInstance();
        $template = Template::getInstance();
        $request = Request::getInstance();
        $feedback = new FeedbackModel();

        $template->post = $feedback->getId($id, /*reply*/ false);
        if (!$template->post){
            $c = new errorController();
            return $c->e404();
        }
        $page->setPageTitle('Feedback: ' . $template->post->subject);

        $template->page         = $request->page;
        $template->countReply   = $feedback->countReply($template->post->id);
        $template->reply        = $feedback->getReply($template->post->id, $request->page, 30);
        $template->show('post');
    }

    private function authorized($quoi, $limit = 5) {
        if (class_exists('memcache')){
            $mem = new Memcache;
            $mem->connect('127.0.0.1', 11211);
            $fb = $mem->get(__SQL . '_feedback_' . md5(Securite::ipX()));
            if (!$fb){
                return true;
            }elseif (isset($fb[$quoi])){
                if ($fb[$quoi] > $limit){
                    return false;
                }
            }
        }
        return true;
    }

    private function action($quoi){
        if (class_exists('memcache')){
            $mem = new Memcache;
            $mem->connect('127.0.0.1', 11211);
            $fb = $mem->get(__SQL . '_feedback_' . md5(Securite::ipX()));
            if (!$fb){
                $fb = array($quoi => 1);
            }elseif (isset($fb[$quoi])){
                $fb[$quoi]++;
            }
            $mem->set(__SQL . '_feedback_' . md5(Securite::ipX()), $fb, MEMCACHE_COMPRESSED, 300);
        }
        return true;
    }
}