<?php
namespace Bean;

/**
 * Created by PhpStorm.
 * User: hoshi
 * Date: 2017/08/03
 * Time: 18:28
 */
class Mail
{
    private $from;
    private $to;
    private $cc;
    private $bcc;
    private $title;
    private $returnPath;
    private $message;

    function sendMail(){
        $header = "From: {$this->from}";
        if (!empty($this->cc)) {
            $header .= "\n";
            $header .= "Cc: {$this->cc}";
        }
        if (!empty($this->bcc)) {
            $header .= "\n";
            $header .= "Bcc: {$this->bcc}";
        }
        $returnPath = (!empty($this->returnPath)) ? "-f {$this->returnPath}" : '';

        if (!empty($returnPath)) {
            return mb_send_mail($this->to, $this->title, $this->message, $header, $returnPath);
        } else {
            return mb_send_mail($this->to, $this->title, $this->message, $header);
        }
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @param mixed $to
     */
    public function setTo($to)
    {
        if (is_array($to)) {
            $to = implode(',', $to);
        }
        $this->to = $to;
    }

    /**
     * @param mixed $cc
     */
    public function setCc($cc)
    {
        if (is_array($cc)) {
            $cc = implode(',', $cc);
        }
        $this->cc = $cc;
    }

    /**
     * @param mixed $bcc
     */
    public function setBcc($bcc)
    {
        if (is_array($bcc)) {
            $bcc = implode(',', $bcc);
        }
        $this->bcc = $bcc;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param $returnPath
     */
    public function setReturnPath($returnPath)
    {
        $this->returnPath = $returnPath;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}