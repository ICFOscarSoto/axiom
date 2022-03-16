<?php
namespace App\Modules\HR\Helpers;

class HelperAsterisk{
  public function registerWorker($worker){
    if($worker->getCompany()->getVoipaddress()!=null && $worker->getAsteriskqueues()!=null && $worker->getExtension()!=null && $worker->getVoipregister()){
      $queues=explode(',',$worker->getAsteriskqueues());
      if(count($queues)>0){
        foreach($queues as $queue){
          $queue=trim($queue, " ");
          $cmd="sshpass -p ".$worker->getCompany()->getVoippassword()." ssh -o \"StrictHostKeyChecking no\" ".$worker->getCompany()->getVoipuser()."@".$worker->getCompany()->getVoipaddress()." asterisk -rx \\\"queue add member Local/".$worker->getExtension()."@from-queue/n to ".$queue."\\\"";
          shell_exec($cmd);
        }
      }
    }
  }

  public function unregisterWorker($worker){
    if($worker->getCompany()->getVoipaddress()!=null && $worker->getAsteriskqueues()!=null && $worker->getExtension()!=null && $worker->getVoipregister()){
      $queues=explode(',',$worker->getAsteriskqueues());
      if(count($queues)>0){
        foreach($queues as $queue){
          $queue=trim($queue, " ");
          $cmd="sshpass -p ".$worker->getCompany()->getVoippassword()." ssh -o \"StrictHostKeyChecking no\" ".$worker->getCompany()->getVoipuser()."@".$worker->getCompany()->getVoipaddress()." asterisk -rx \\\"queue remove member Local/".$worker->getExtension()."@from-queue/n from ".$queue."\\\"";
          shell_exec($cmd);
        }
      }
    }
  }

}
