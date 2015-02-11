<?php
Class SiteCashModel extends Model {
	
	private $uid;
	private $account;
	private $log;
	private $config;
	
	public function getConfig() {
		$o = new Cache('sitecash');
		$this->config = $o->getCache();
		if (!$this->config) {
			// La devise de l'argent du site
			$this->config['devise'] = 'Crédits';
			// Le ratio pour paypal
			$this->config['ratio'] = 10;
			// Le ratio par telephone
			$this->config['ratioAllo'] = 10;
			
			// Config pour rentabiliweb
			$this->config['rentabiliweb']['siteId'] = 0;
			$this->config['rentabiliweb']['docId'] = 0;

            // PayPal
            $this->config['paypal'] = array('apiusername' => NULL, 'apimdp' => NULL, 'apisign' => NULL);
			$o->setCache($this->config);
		}
		return $this->config;
	}
	
	/**
	 * @param int $uid Id de l'utilisateur
	 */
	public function setUid($uid /* User ID */) {
		$this->uid = (int) $uid;
		
		$this->log = new SiteCashLogModel();
		$this->log->setUid($this->uid);
		
		
		$this->account = $this->findFirst(
			array(
				'fields' => 'id, uid, amt, locked',
				'conditions' => array(
					'uid' => $this->uid
					)
				)
			);
		if (!$this->account) {
			$this->account = new stdClass();
			$this->account->uid = $this->uid;
			$this->account->amt = 0;
			$this->account->locked = 0;
		}
		$this->getConfig();
	}
	
	public function getDevise() {
		if (!isset($this->config['devise'])) {
			$this->getConfig();
		}
		return $this->config['devise'];
	}
	
	public function getFormatedAmout() {
		return number_format($this->account->amt, 0, '', ' ') . ' ' . $this->getDevise();
	}
	
	public function getAccount() {
		return $this->account;
	}
	
	public function getAmout() {
		return $this->account->amt;
	}
	

	
	public function give($amt, $message = "Ajout de cr&eacute;dit") {
		$this->account->amt += (int) $amt;
		if (!$this->save($this->account)) {
			throw new Exception("Database error code 1");
		}
		
		$this->log->add((int) $amt, $message);
		return $this->account->amt;
	}
	
	public function remove($amt, $message = "Retrait de cr&eacute;dit") {
		
		if ($this->isLocked()) {
			throw new Exception("Compte v&eacute;rrouill&eacute;");
		}
		
		if ( ($this->account->amt - $amt) < 0 ) {
			throw new Exception("Pas assez de cr&eacute;dit");
		}
	
		$this->account->amt = ($this->account->amt - $amt);
		if (!$this->save($this->account)) {
			throw new Exception("Database error code 1");
		}
		
		$this->log->remove((int) $amt, $message);
		return true;
	}
	
	public function isLocked() {
		return ($this->account->locked == 0) ? false : true;
	}
	
	public function lock() {
		$this->account->locked = 1;
	}
	
	public function unlock() {
		$this->account->locked = 0;
	}
	
	public function install() {
		$this->query("CREATE TABLE IF NOT EXISTS `" . __SQL . "_SiteCash` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `uid` int(11) NOT NULL,
		  `amt` int(11) NOT NULL,
		  `locked` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
	}
}