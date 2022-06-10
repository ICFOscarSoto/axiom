<?php

namespace App\Modules\Globale\Helpers\Html2Text;

class Html2TextException extends \Exception {

	var $more_info;

	public function __construct($message = "", $more_info = "") {
		parent::__construct($message);
		$this->more_info = $more_info;
	}

}
