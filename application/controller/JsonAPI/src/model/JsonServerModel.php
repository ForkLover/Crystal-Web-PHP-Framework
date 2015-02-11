<?php
class JsonServerModel extends Model {
    public function install() {
        $this->query("CREATE TABLE IF NOT EXISTS `" . __SQL . "_JsonServer` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(64) NOT NULL,
          `server` varchar(256) NOT NULL,
          `port` int(5) NOT NULL,
          `user` varchar(256) NOT NULL,
          `password` varchar(256) NOT NULL,
          `salt` varchar(255) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");    
    }
    
    public function addServer($name, $server, $port, $user, $password, $salt) {
        $data = new stdClass();
        $data->name = $name;
        $data->server = $server;
        $data->port = $port;
        $data->user = $user;
        $data->password = encrypter($password);
        $data->salt = encrypter($salt);
        return $this->save($data);
    }

    public function editServer($sid, $name, $server, $port, $user, $password, $salt) {
        $data = $this->getServerById($sid);
        if (!$this->getServerById($sid)) {return;}
        $data->name = $name;
        $data->server = $server;
        $data->port = $port;
        $data->user = $user;
        $data->password = encrypter($password);
        $data->salt = encrypter($salt);
        return $this->save($data);
    }

    public function getServerList() {
        $tmp = $this->find();
        for($i=0;$i<count($tmp);$i++){
            $tmp[$i]->password = decrypter($tmp[$i]->password);
            $tmp[$i]->salt = decrypter($tmp[$i]->salt);
        }

        return $tmp;
    }

    public function getServerById($sid) {
        $sid = (int) $sid;
        $server = $this->findFirst(array('conditions' => 'id = ' . $sid));
        if ($server) {
            $server->password = decrypter($server->password);
            $server->salt = decrypter($server->salt);
        }

        return $server;
    }
}