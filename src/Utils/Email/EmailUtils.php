<?php
namespace App\Utils\Email;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EmailUtils{
  public $charset,$htmlmsg,$plainmsg,$attachments;
  public $container;

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
          $filename = ($params['filename'])? $params['filename'] : $params['name'];
          // filename may be encoded, so see imap_mime_header_decode()
          $attachment=array("filename" => $filename, "msgno" => $mid, "encoding" => $p->encoding, "partno" => $partno,
                            "icon" => $this->container->get('router')->generate('getFilesImage', array('ext' => pathinfo($filename, PATHINFO_EXTENSION))));
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
            $charset = $params['charset'];  // assume all parts are same charset
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

}
