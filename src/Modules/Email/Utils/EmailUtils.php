<?php
namespace App\Modules\Email\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Helpers\HelperMail;

class EmailUtils{
  public $charset,$htmlmsg,$plainmsg;
  public $attachments=array();
  public $container=null;

  function readEmail($emailAccount, $emailFolder, $container, $id, $router){
    //$emailUtils = new EmailUtils();
    $connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'/novalidate-cert}'.$emailFolder->getName();
    $inbox = imap_open($connectionString,$emailAccount->getUsername() ,$emailAccount->getPassword());
    if(!$inbox) return null;
    $subject=imap_fetch_overview ($inbox, $id, 0);
    if(!count($subject)) return null;
    $emailSubject=$subject[0];

    $this->container=$this->container;
    $this->getmsg($inbox,$emailSubject->msgno);
    $message["id"]						=$emailSubject->uid;
    $message["subject"]				=isset($emailSubject->subject)?HelperMail::decode_header(imap_utf8($emailSubject->subject)):'';
    $message["from"]					=isset($emailSubject->from)?HelperMail::decode_header(imap_utf8($emailSubject->from)):'';
    $message["to"]						=isset($emailSubject->to)?HelperMail::decode_header(imap_utf8($emailSubject->to)):'';
    $message["message_id"]		=isset($emailSubject->message_id)?$emailSubject->message_id:'';
    $message["imgFrom"]			  =substr($router->generate('getUserImage', array('id' => 0)),1); //TODO Buscar foto del contacto en la agenda
    $message["content"]		  	=($this->htmlmsg!=null)?(preg_match('!!u', $this->htmlmsg)?$this->htmlmsg:utf8_encode($this->htmlmsg)):$this->plainmsg;
    $message["signature"]			=$emailAccount->getSignature();
    $message["attachments"]		=$this->attachments;
    $message["size"]					=$emailSubject->size;
    $message["uid"]						=$emailSubject->uid;
    $message["msgno"]					=$emailSubject->msgno;
    $message["recent"]				=$emailSubject->recent;
    $message["flagged"]				=$emailSubject->flagged;
    $message["answered"]			=$emailSubject->answered;
    $message["deleted"]				=$emailSubject->deleted;
    $message["seen"]					=$emailSubject->seen;
    $message["draft"]					=$emailSubject->draft;
    $message["date"]					=new \DateTime(date('Y-m-d H:i:s',$emailSubject->udate));
    $message["timestamp"]			=$message["date"]->getTimestamp();
    $message["url"]						=$router->generate('emailView', array('folder'=>$emailFolder->getId(), 'id' => $emailSubject->msgno));
    $message["urlDelete"]			=$router->generate('emailMove', array('id' => $emailSubject->uid, "origin"=> $emailFolder->getId(), "destination"=>$emailAccount->getTrashFolder()->getId()));
    $message["urlRead"]				=$router->generate('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Seen', 'value' => 1));
    $message["urlFlagged"]		=$router->generate('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Flagged', 'value' => 1));
    $message["urlUnRead"]			=$router->generate('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Seen', 'value' => 0));
    $message["urlUnFlagged"]	=$router->generate('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Flagged', 'value' => 0));
    return $message;
  }


  function getMsg($mbox,$mid) {
      // input $mbox = IMAP stream, $mid = message id
      // output all the following:
      $this->htmlmsg = $this->plainmsg = $this->charset = '';
      $this->attachments = array();

      // HEADER
      $h = imap_header($mbox,$mid);
      // add code here to get date, from, to, cc, subject...

      // BODY
      $s = imap_fetchstructure($mbox,$mid);
      if (isset($s->parts) && !$s->parts)  // simple
          $this->getPart($mbox,$mid,$s,0);  // pass 0 as part-number
      else {  // multipart: cycle through each part
          if(isset($s->parts)){
            foreach ($s->parts as $partno0=>$p)
                $this->getPart($mbox,$mid,$p,$partno0+1);
            }
          else{  //message without parts
            $this->getPart($mbox,$mid,$s,1);
          }
      }
  }

  function getAtachment($mbox,$mid,$encoding,$partno){
    return $data=$this->getData($mbox,$mid,$encoding,$partno);

  }

  function getData($mbox,$mid,$encoding,$partno){
    // $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
    // DECODE DATA
    $data = ($partno)?
        imap_fetchbody($mbox,$mid,$partno):  // multipart
        imap_body($mbox,$mid);  // simple
    // Any part may be encoded, even plain text messages, so check everything.
    if ($encoding==4) $data = quoted_printable_decode($data);
    elseif ($encoding==3) $data = base64_decode($data);
    return $data;
  }

  function getPart($mbox,$mid,$p,$partno) {
      // PARAMETERS
      // get all parameters, like charset, filenames of attachments, etc.
      $params = array();
      if ($p->parameters)
          foreach ($p->parameters as $x)
              $params[strtolower($x->attribute)] = $x->value;
      if (isset($p->dparameters) && $p->dparameters)
          foreach ($p->dparameters as $x)
              $params[strtolower($x->attribute)] = $x->value;

      // ATTACHMENT
      // Any part with a filename is an attachment,
      // so an attached text file (type 0) is not mistaken as the message.
      if ((isset($params['filename']) && $params['filename']) || (isset($params['name']) && $params['name'])) {
          // filename may be given as 'Filename' or 'Name' or both
          $filename = (isset($params['filename']))? $params['filename'] : $params['name'];
          // filename may be encoded, so see imap_mime_header_decode()
          $attachment=array("filename" => HelperMail::decode_header($filename), "msgno" => $mid, "encoding" => $p->encoding, "partno" => $partno,
                            "icon" => $this->container?$this->container->get('router')->generate('getFilesImage', array('ext' => pathinfo($filename, PATHINFO_EXTENSION))):'');
          $this->attachments[]=$attachment;
          //$this->attachments[$filename] = $data;  // this is a problem if two files have same name

      }else{
        $data=$this->getData($mbox,$mid,$p->encoding,$partno);

        // TEXT
        if ($p->type==0 && $data) {
            // Messages may be split in different parts because of inline attachments,
            // so append parts together with blank row.
            if (strtolower($p->subtype)=='plain')
                $this->plainmsg.= trim($data) ."\n\n";
            else
                $this->htmlmsg.= $data ."<br><br>";

            $charset = isset($params['charset'])?$params['charset']:'UTF8';  // assume all parts are same charset
        }

        // EMBEDDED MESSAGE
        // Many bounce notifications embed the original message as type 2,
        // but AOL uses type 1 (multipart), which is not handled here.
        // There are no PHP functions to parse embedded messages,
        // so this just appends the raw source to the main message.
        elseif ($p->type==2 && $data) {
            $this->plainmsg.= $data."\n\n";
        }

        // SUBPART RECURSION
        if (isset($p->parts) && $p->parts) {
            foreach ($p->parts as $partno0=>$p2)
                $this->getPart($mbox,$mid,$p2,$partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
        }
      }

  }

  function extractEmailsFromString($string){
    $matches = array();
    $pattern = '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}/i';
    preg_match_all($pattern, $string, $matches);
    $neaterArray = (array_values(array_unique($matches[0])));
    return $neaterArray;
  }

  function countAttachments($connection, $message_number) {

    $attachments = 0;
    $structure = imap_fetchstructure($connection, $message_number);
    if(isset($structure->parts) && count($structure->parts)) {
        for($i = 0; $i < count($structure->parts); $i++) {
            if($structure->parts[$i]->ifdparameters) {
                foreach($structure->parts[$i]->dparameters as $object) {
                    if(strtolower($object->attribute) == 'filename') {
                        $attachments++;
                    }
                }
            }else if($structure->parts[$i]->ifparameters) {
                foreach($structure->parts[$i]->parameters as $object) {
                    if(strtolower($object->attribute) == 'name') {
                        $attachments++;
                    }
                }
            }
        }
    }
    return $attachments;
  }

}
