<?php
Class SiteCashLogModel extends Model {
	public function install() {
		$this->query("CREATE TABLE IF NOT EXISTS `" . __SQL . "_SiteCashLog` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `uid` int(11) NOT NULL,
		  `message` varchar(255) NOT NULL,
		  `amt` int(11) NOT NULL,
		  `stats` char(1) NOT NULL,
		  `dtime` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
	}
	
	public function setUid($uid) {
		$this->uid = (int) $uid;
	}
	
	public function add($amt, $message = "Ajout de cr&eacute;dit") {
		$logger = new LogModel();
		$logger->setLog('sitecash', $message . ' +' . $amt, (int) $this->uid);
		
		$data = new stdClass();
		$data->uid = (int) $this->uid;
		$data->message = $message;
		$data->amt = (int) $amt;
		$data->stats = '+';
		$data->dtime = time();
		$this->save($data);
	}
	
	public function remove($amt, $message = "Retrait de cr&eacute;dit") {
		$logger = new LogModel();
		$logger->setLog('sitecash', $message . ' -' . $amt, (int) $this->uid);
		
		$data = new stdClass();
		$data->uid = (int) $this->uid;
		$data->message = $message;
		$data->amt = (int) $amt;
		$data->stats = '-';
		$data->dtime = time();
		$this->save($data);
	}
	
	public function event($message) {
		$data = new stdClass();
		$data->uid = (int) $this->uid;
		$data->message = $message;
		$data->amt = 0;
		$data->stats = '=';
		$data->dtime = time();
		$this->save($data);
	}
	
	public function getEvent($page = 1, $limit = 30) {
		$page = (int) $page;
		$page--;
		$limit = (int) $limit;
		
		$start = $page * $limit;
		
		$prepare = array(
			'fields' => 'message, amt, stats, dtime',
			'conditions' => array(
				'uid' => $this->uid
				),
			'order' => 'id DESC',
			'limit' => $start . ',' . $limit
			);
		return $this->find($prepare);
	}
}