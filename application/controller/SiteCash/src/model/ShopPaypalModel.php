<?php
/**
* @title Connection
* @author Christophe BUFFET <developpeur@crystal-web.org> 
* @license Creative Commons By 
* @license http://creativecommons.org/licenses/by-nd/3.0/
* @description 
*/
Class ShopPaypalModel extends Model{

	public function install() {
	$this->query("
	CREATE TABLE IF NOT EXISTS `".__SQL."_ShopPaypal` (
	  `id` int(11) NOT NULL auto_increment,
	  `token` varchar(255) NOT NULL,
	  `timestamp` varchar(255) NOT NULL,
	  `correlationid` varchar(255) NOT NULL,
	  `ack` varchar(255) NOT NULL,
	  `version` varchar(255) NOT NULL,
	  `build` varchar(255) NOT NULL,
	--  `transactionid` varchar(255) NOT NULL, 
	--  `transactiontype` varchar(255) NOT NULL,
	--  `paymenttype` varchar(255) NOT NULL,
	--  `ordertime` varchar(255) NOT NULL,
	  `amt` varchar(255) NOT NULL,
	  `taxamt` varchar(255) NOT NULL,
	  `currencycode` varchar(255) NOT NULL,
	  `paymentstatus` varchar(255) NOT NULL,
	--  `pendingreason` varchar(255) NOT NULL,
	--  `reasoncode` varchar(255) NOT NULL,
	  `idmember` int(11) NOT NULL,
	--  `amtexpected` varchar(255) NOT NULL,
	  PRIMARY KEY  (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
	");
	}
	
	
	public function registerTransaction($uid, $currencyCode, $token, $timestamp, $amt, $taxamt = 0) {
		$token = urldecode($token);
		
		$this->setTable('ShopPaypal');
		$data = new stdClass();
		$data->idmember = (int) $uid;
		$data->currencycode = $currencyCode;
		$data->token = $token;
		$data->timestamp = $timestamp;
		$data->amt = (int) $amt;
		$data->taxamt = (int) $taxamt;
		$data->paymentstatus = 'pending';
		return $this->save($data);
	}
	
	public function updateTransaction($token, $paypalReturn) {
		$shop = $this->getToken($token);
		debug($this->sql);
		if (!$shop) {
			throw new Exception("Incorrect request, can't build it", 1);
		} elseif ($shop->ack =='Success') {
			return false;
		}
		
		$shop->timestamp = (isset($paypalReturn['TIMESTAMP'])) ? $paypalReturn['TIMESTAMP'] : $shop->timestamp;
		$shop->correlationid = (isset($paypalReturn['CORRELATIONID'])) ? $paypalReturn['CORRELATIONID'] : $shop->correlationid;
		$shop->ack = (isset($paypalReturn['ACK'])) ? $paypalReturn['ACK'] : $shop->ack;
		$shop->version = (isset($paypalReturn['VERSION'])) ? $paypalReturn['VERSION'] : $shop->version;
		$shop->build = (isset($paypalReturn['BUILD'])) ? $paypalReturn['BUILD'] : $shop->build;
	
		// initialisations
		$amttax = $amt = 0;
		$paymentstatus = 'init';
		
		for ($i=0;$i<9;$i++) {
			if (!isset($paypalReturn['PAYMENTINFO_'.$i.'_AMT'])) {
				$i=9;
			} else {
				$amt			+= $paypalReturn['PAYMENTINFO_'.$i.'_AMT'];
				$amttax			+= $paypalReturn['PAYMENTINFO_'.$i.'_TAXAMT'];
				$paymentstatus	=  $paypalReturn['PAYMENTINFO_'.$i.'_PAYMENTSTATUS'];
			}		
		}
		
		if ($shop->amt != $amt) {
			throw new Exception("Incorrect amout", 1);
		}
		
		$shop->taxamt = $amttax;
		$this->save($shop);
		return $shop;
	}
	
	private function getToken($token) {
		$this->setTable('ShopPaypal');
		$prepare = array(
			'fields' => 'id, idmember, paymentstatus, amt, taxamt, ack',
			'conditions' => array(
				'token' => $token
				),
			'limit' => '1',
			'order' => 'id DESC'
			);
		return $this->findFirst($prepare);
	}
	
	public function cancel($token) {
		$payResp = $this->getToken($token);
		if (!$payResp) { return false;
		} else {
			if (empty($payResp->ack)) {
				$this->delete($payResp->id);
			} else {
				return false;
			}
		}
	}
}