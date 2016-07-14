<?php

namespace hooks\Mailer;


class Mail
{
    private
        $to, $subject, $message, $from, $replyTo, $bcc;

    public function to($to){
        $this->to = $to;
        return $this;
    }

    public function from($from){
        $this->from = $from;
        return $this;
    }

    public function replyTo($replyTo){
        $this->replyTo = $replyTo;
        return $this;
    }

    public function bcc($bcc){
        $this->bcc = $bcc;
        return $this;
    }

    public function subject($subject){
        $this->subject = $subject;
        return $this;
    }

    public function message($message, $noBodyWrap = false){
        if(!$noBodyWrap){
            $message = "<html><body style='font-family: Helvetica, Arial, sans-serif'>".$message."</body></html>";
        }

        $this->message = $message;
        return $this;
    }


    public function send()
    {

        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=utf-8";


        if($this->from == null){
            $this->from = "no-reply@" . $_SERVER["HTTP_HOST"];
        }

        $headers[] = "From: " . $this->from;

        if($this->replyTo != null){
            $headers[] = "Reply-To: " . $this->replyTo;
        }


        if($this->bcc != null){
            $headers[] = "Bcc: " . $this->bcc;
        }

        $date = new \DateTime("now");

        $headers[] = "Date: " . $date->format("D, d M y H:i:s O");
        $headers[] = "X-Mailer: PHP/".phpversion();

        return @mail($this->to, $this->subject, $this->message, implode("\r\n", $headers));
    }

}