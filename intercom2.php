<?php
/*
 * Copyright 2021 Cryptech Services
 *
 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.
 */

////////////////////////////////////////////////////////////////////////////////
// intercom2.php by loma oopaloopa



namespace Intercom2;
use \Exception;



///////////////////////////////////////////////////////////////////////////////



const INTERCOM2_URI = "/intercom2/";



///////////////////////////////////////////////////////////////////////////////



class Intercom2_Exception extends Exception
    {
    public function __construct(string $errorMessage)
        {
        parent::__construct($errorMessage);
        }
    }

class Intercom2_NetworkCurlException extends Intercom2_Exception
    {
    private $curlErrorNumber;
    private $curlErrorStr;

    public function __construct($ch)
        {
        parent::__construct("Intercom2->sendMsg(): CURL (Network) Error: " . curl_error($ch) . ".");
        $this->curlErrorStr = curl_error($ch);
        $this->curlErrorNumber = curl_errno($ch);
        }

    public function getCurlErrorNumber() : int
        {
        return $this->curlErrorNumber;
        }

    public function getCurlErrorStr() : string
        {
        return $this->curlErrorStr;
        }
    }

class Intercom2_NotSSLException extends Intercom2_Exception
    {
    private $offendingIPAddr;

    public function __construct(string $offendingIPAddr)
        {
        parent::__construct("Intercom2->receive(): A non SSL connection has been attempted from $offendingIPAddr.");
        $this->offendingIPAddr = $offendingIPAddr;
        }

    public function getOffendingIPAddr() : string
        {
        return $this->offendingIPAddr;
        }
    }

class Intercom2_BadEndPointException extends Intercom2_Exception
    {
    private $badEndPointId;

    public function __construct(string $badEndPointId)
        {
        parent::__construct("Intercom2->receive(): Bad end point id $badEndPointId.");
        $this->badEndPointId = $badEndPointId;
        }

    public function getBadEndPointId() : string
        {
        return $this->badEndPointId;
        }
    }

class Intercom2_BadMsgFormatException extends Intercom2_Exception
    {
    private $badMsg;

    public function __construct(string $badMsg)
        {
        parent::__construct("Intercom2->receive(): Network message not in intercom2 message format.");
        $this->badMsg = $badMsg;
        }

    public function getBadMsg() : string
        {
        return $this->badMsg;
        }
    }

class Intercom2_BadMsgIdException extends Intercom2_Exception
    {
    private $badMsgId;

    public function __construct(string $badMsgId)
        {
        parent::__construct("Intercom2->receive(): Unknown message id $badMsgId.");
        $this->badMsgId = $badMsgId;
        }

    public function getBadMsgId() : string
        {
        return $this->badMsgId;
        }
    }

class Intercom2_ImposterException extends Intercom2_Exception
    {
    private $id;
    private $ipAddr;

    public function __construct(int $id, string $ipAddress)
        {
        parent::__construct("Intercom2: Imposter! IP address $ipAddress doesn't match what was given to configEndPoint() for intercom2 endpoint id $id.");
        $this->id = $id;
        $this->ipAddr = $ipAddress;
        }

    public function getIntercomId() : int
        {
        return $this->id;
        }

    public function getIPAddress() : string
        {
        return $this->ipAddr;
        }
    }



///////////////////////////////////////////////////////////////////////////////



interface Intercom2_MsgHandler
    {
    public function intercom2_processMsg(Intercom2_EndPoint $sender, string $rxData) : ?string;
    }

class Intercom2_MsgCb implements Intercom2_MsgHandler
    {
    private $msgProcessor;

    public function __construct(callable $msgProcessor)
        {
        $this->msgProcessor = $msgProcessor;
        }

    public function intercom2_processMsg(Intercom2_EndPoint $sender, string $rxData) : ?string
        {
        return call_user_func($this->msgProcessor, $sender, $rxData);
        }
    }



///////////////////////////////////////////////////////////////////////////////



class Intercom2_Reply
    {
    private $error;
    private $rxData;

    public function __construct(?Exception $error, ?string $rxData)
        {
        $this->error = $error;
        $this->rxData = $rxData;
        }

    public function getError() : ?Exception
        {
        return $this->error;
        }

    public function getRxData() : string
        {
        if ($this->error) throw $this->error;
        return $this->rxData ? $this->rxData : "";
        }
    }



///////////////////////////////////////////////////////////////////////////////



class Intercom2_EndPoint
    {
    private $id;
    private $context;
    private $host;
    private $port;
    private $ipAddr;

    public function __construct(int $id)
        {
        $this->id = $id;
        }

    public function init($context, string $host, int $port)
        {
        $this->context = $context;
        $this->host = $host;
        $this->port = $port;
        }

    public function getId() : int
        {
        return $this->id;
        }

    public function getContext()
        {
        return $this->context;
        }

    public function getHost() : string
        {
        return $this->host;
        }

    public function getPort() : int
        {
        return $this->port;
        }

    public function getIPAddress() : string
        {
        return $this->ipAddr;
        }

    public function getProtocollessURL() : string
        {
        return $this->port ? $this->host . ":" . $this->port . INTERCOM2_URI : $this->host . INTERCOM2_URI;
        }

    public function url(bool $useSSL, ?string $uriExtension = null) : string
        {
        $protocol = $useSSL ? "https" : "http";
        if (!$uriExtension) $uriExtension = "";
        return $protocol . "://" . $this->getProtocollessURL() . $uriExtension;
        }

    public function hasIPAddress(string $remoteIPStr) : bool
        {
        if ($this->actuallyCheckIPAddress($remoteIPStr))
            {
            $this->ipAddr = $remoteIPStr;
            return true;
            }
        else
            return false;
        }

    private function actuallyCheckIPAddress(string $remoteIPStr) : bool
        {
        $remote = inet_pton($remoteIPStr);
        $remoteIs4 = strlen($remote) == 4;
        $ip = @inet_pton($this->host);
        if ($ip !== false)
            {
            if (strlen($ip) == 4)
                return $remoteIs4 ? $this->equalIPBoth4($ip, $remote) : $this->equalIP4And6($ip, $remote);
            else
                return $remoteIs4 ? $this->equalIP4And6($remote, $ip) : $this->equalIPBoth6($remote, $ip);
            }
        $hosts4 = gethostbynamel($this->host);
        foreach ($hosts4 as $ipStr)
            {
            $ip = inet_pton($ipStr);
            if ($remoteIs4 ? $this->equalIPBoth4($ip, $remote) : $this->equalIP4And6($ip, $remote)) return true;
            }
        $records = dns_get_record($this->host,  DNS_A | DNS_AAAA);
        foreach ($records as $r)
            {
            $type = $r["type"];
            if ($type == "A")
                {
                $ip = inet_pton($r["ip"]);
                if ($remoteIs4 ? $this->equalIPBoth4($ip, $remote) : $this->equalIP4And6($ip, $remote)) return true;
                }
            else if ($type == "AAAA")
                {
                $ip = inet_pton($r["ipv6"]);
                if ($remoteIs4 ? $this->equalIP4And6($remote, $ip) : $this->equalIPBoth6($remote, $ip)) return true;
                }
            }
        return false;
        }

    private function equalIPBoth4($ipA, $ipB) : bool
        {
        return $ipA == $ipB;
        }
    
    private function equalIP4And6($ip4, $ip6) : bool
        {
        return
            $ip4 == "\127\0\0\1" && $ip6 == "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\1" ||
            $ip6 == "\0\0\0\0\0\0\0\0\255\255\255\255" . $ip4 ||
            $ip6 == "\0\0\0\0\0\0\0\0\0\0\0\0" . $ip4;
        }
    
    private function equalIPBoth6($ipA, $ipB) : bool
        {
        if (substr($ipA, 0, 12) == "\0\0\0\0\0\0\0\0\255\255\255\255") $ipA = "\0\0\0\0\0\0\0\0\0\0\0\0" . substr($ipA, 12);
        if (substr($ipB, 0, 12) == "\0\0\0\0\0\0\0\0\255\255\255\255") $ipB = "\0\0\0\0\0\0\0\0\0\0\0\0" . substr($ipB, 12);
        return
            $ipA == $ipB ||
            $ipA == "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\1" && $ipB == "\0\0\0\0\0\0\0\0\0\0\0\0\127\0\0\1" ||
            $ipB == "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\1" && $ipA == "\0\0\0\0\0\0\0\0\0\0\0\0\127\0\0\1";
        }
    }



///////////////////////////////////////////////////////////////////////////////



const INTERCOM2_HDR_PRELUDE     = "\r\n\$partLength:";
const INTERCOM2_HDR_PRELUDE_LEN = 14;
const INTERCOM2_HDR_LEN         = 32;



class Intercom2_Codec
    {
    private $input = "";
    private $inputLen = 0;
    private $i = 0;
    private $msgId = "";
    private $msgData = "";

    public static function format(?string $msgId, ?string $txData) : string
        {
        if (!$msgId) $msgId = "";
        if (!$txData) $txData = "";
        $idLen = strlen($msgId);
        $dataLen = strlen($txData);
        $parts = [ ];
        $parts[0] = static::formatHeader($idLen);
        if ($idLen) $parts[1] = $msgId;
        $parts[count($parts)] = static::formatHeader($dataLen);
        if ($dataLen) $parts[count($parts)] = $txData;
        return implode("", $parts);
        }

    private static function formatHeader(int $len) : string
        {
        $hex = dechex($len);
        return INTERCOM2_HDR_PRELUDE . substr("0000000000000000", strlen($hex)) . $hex . "\r\n";
        }

    public function parse(string $input) : bool
        {
        $this->msgId = $this->msgData = "";
        if (!$input) return false;
        $this->input = $input;
        $this->inputLen = strlen($input);
        $this->i = 0;
        if (INTERCOM2_HDR_LEN > $this->inputLen) return false;
        $idLen = $this->parseHeader();
        if ($idLen < 0 || $this->i + $idLen > $this->inputLen) return false;
        if ($idLen) $this->msgId = substr($this->input, $this->i, $idLen);
        $this->i += $idLen;
        if ($this->i == $this->inputLen) return true;
        if ($this->i + INTERCOM2_HDR_LEN > $this->inputLen) return false;
        $dataLen = $this->parseHeader();
        if ($dataLen < 0 || $this->i + $dataLen != $this->inputLen) return false;
        if ($dataLen) $this->msgData = substr($this->input, $this->i);
        return true;
        }

    public function id() : string
        {
        return $this->msgId;
        }

    public function data() : string
        {
        return $this->msgData;
        }

    private function parseHeader() : int
        {
        if ($this->i + INTERCOM2_HDR_LEN > $this->inputLen) return -1;
        if (strpos($this->input, INTERCOM2_HDR_PRELUDE, $this->i) != $this->i) return -1;
        $this->i += INTERCOM2_HDR_PRELUDE_LEN;
        $len = 0;
        $counter = 16;
        while ($counter--)
            {
            $d = $this->hexDigit($this->input[$this->i++]);
            if ($d < 0) return -1;
            $len = 16*$len + $d;
            }
        if ($this->input[$this->i++] != "\r") return -1;
        if ($this->input[$this->i++] != "\n") return -1;
        return $len;
        }

    private function hexDigit(string $digit) : int
        {
        switch ($digit)
            {
            case "0":           return  0;
            case "1":           return  1;
            case "2":           return  2;
            case "3":           return  3;
            case "4":           return  4;
            case "5":           return  5;
            case "6":           return  6;
            case "7":           return  7;
            case "8":           return  8;
            case "9":           return  9;
            case "A": case "a": return 10;
            case "B": case "b": return 11;
            case "C": case "c": return 12;
            case "D": case "d": return 13;
            case "E": case "e": return 14;
            case "F": case "f": return 15;
            default:            return -1;
            }
        }
    }



///////////////////////////////////////////////////////////////////////////////



interface Intercom2_SSL_Conf
    {
    public function setCurlOpts($ch);
    }

class Intercom2_SSL_2WayConf implements Intercom2_SSL_Conf
    {
    private $passPhrase;
    private $caCertFile;
    private $clientCertFile;
    private $clientKeyFile;

    public function setPassphrase(string $passPhrase) : Intercom2_SSL_2WayConf
        {
        $this->passPhrase = $passPhrase;
        return $this;
        }

    public function setClientPassphrase(string $passPhrase) : Intercom2_SSL_2WayConf
        {
        $this->passPhrase = $passPhrase;
        return $this;
        }

    public function setRootCert(string $certFile) : Intercom2_SSL_2WayConf
        {
        $this->caCertFile = $certFile;
        return $this;
        }

    public function setClientCert(string $keyFile, string $certFile) : Intercom2_SSL_2WayConf
        {
        $this->clientCertFile = $certFile;
        $this->clientKeyFile = $keyFile;
        return $this;
        }

    public function setCurlOpts($ch) : void
        {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true                 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2                    );
        curl_setopt($ch, CURLOPT_CAINFO,         $this->caCertFile    );
        curl_setopt($ch, CURLOPT_SSLCERT,        $this->clientCertFile);
        curl_setopt($ch, CURLOPT_SSLKEY,         $this->clientKeyFile );
        if ($this->passPhrase) curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->passPhrase);
        }
    }

class Intercom2_SSL_SimpleConf implements Intercom2_SSL_Conf
    {
    public function setCurlOpts($ch) : void
        {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0    );
        }
    }



///////////////////////////////////////////////////////////////////////////////



class Intercom2
    {
    private $endPointsById = [ ];
    private $msgHandlersById = [ ];
    private $ownUriExtension = "";
    private $ownId = 0;
    private $sslConf = null;

    public function __construct(?Intercom2_SSL_Conf $sslConf = null)
        {
        $this->sslConf = $sslConf ? $sslConf : null;
        }

    public function configSelf(int $id) : void
        {
        if ($id <= 0) throw new Exception("Intercom2->configSelf(): id parameter must be positive.");
        $this->ownUriExtension = "?$id";
        $this->ownId = $id;
        }

    public function getOwnId() : int
        {
        return $this->ownId;
        }

    public function configEndPoint(int $id, $context, string $host, int $port) : Intercom2_EndPoint
        {
        if ($id <= 0) throw new Exception("Intercom2->configEndPoint(): id parameter must be positive.");
        if (array_key_exists($id, $this->endPointsById))
            $ep = $this->endPointsById[$id];
        else
            {
            $ep = new Intercom2_EndPoint($id);
            $this->endPointsById[$id] = $ep;
            }
        $ep->init($context, $host, $port);
        return $ep;
        }

    public function configMsgHandler(string $msgId, Intercom2_MsgHandler $msgHandler) : void
        {
        $this->msgHandlersById[$msgId] = $msgHandler;
        }

    public function receive() : ?Intercom2_Exception
        {
        if ($this->sslConf != null && !$this->isHTTPS()) return new Intercom2_NotSSLException($_SERVER["REMOTE_ADDR"]);
        $idStr = $_SERVER["QUERY_STRING"];
        if (!is_numeric($idStr)) return new Intercom2_BadEndPointException($idStr);
        $idInt = 1*$idStr;
        if (!is_int($idInt) || !array_key_exists($idInt, $this->endPointsById)) return new Intercom2_BadEndPointException($idStr);
        $ep = $this->endPointsById[$idInt];
        if (!$ep->hasIPAddress($_SERVER["REMOTE_ADDR"])) return new Intercom2_ImposterException($ep->getId(), $_SERVER["REMOTE_ADDR"]);
        $input = file_get_contents("php://input");
        $msg = new Intercom2_Codec();
        if (!$msg->parse($input)) return new Intercom2_BadMsgFormatException($input);
        if (!array_key_exists($msg->id(), $this->msgHandlersById)) return new Intercom2_BadMsgIdException($msg->id());
        $reply = $this->msgHandlersById[$msg->id()]->intercom2_processMsg($ep, $msg->data());
        $len = $reply ? strlen($reply) : 0;
        header("Content-Type: text/plain; charset=UTF-8");
        header("Content-Length: $len");
        if ($len) echo $reply;
        return null;
        }

    private function isHTTPS() : bool
        {
        return
            (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on")
            || (isset($_SERVER["REQUEST_SCHEME"]) && $_SERVER["REQUEST_SCHEME"] === "https")
            || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https");
        }

    public function sendMsgEZ(int $ownId, int $remoteId, string $host, int $port, string $msgId, ?string $txData = null) : Intercom2_Reply
        {
        $this->configSelf($ownId);
        $ep = $this->configEndPoint($remoteId, null, $host, $port);
        return $this->sendMsg($ep, $msgId, $txData);
        }

    public function sendMsg(Intercom2_EndPoint $recipient, string $msgId, ?string $txData = null) : Intercom2_Reply
        {
        $msg = Intercom2_Codec::format($msgId, $txData);
        $len = strlen($msg);
        $headers = [ ];
        $headers[0] = "Content-Type: text/plain; charset=UTF-8";
        $headers[1] = "Content-Length: $len";
        $useSSL = $this->sslConf != null;
        $ch = null;
        try
            {
            $ch = curl_init();
            if ($useSSL) $this->sslConf->setCurlOpts($ch);
            curl_setopt($ch, CURLOPT_URL,            $recipient->url($useSSL, $this->ownUriExtension));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1                                               );
            curl_setopt($ch, CURLOPT_FAILONERROR,    1                                               );
            curl_setopt($ch, CURLOPT_POST,           1                                               );
            curl_setopt($ch, CURLOPT_POSTFIELDS,     $msg                                            ); 
            curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers                                        ); 
            $replyStr = curl_exec($ch);
            if ($replyStr === false)
                $reply = new Intercom2_Reply(new Intercom2_NetworkCurlException($ch), null);
            else
                $reply = new Intercom2_Reply(null, $replyStr);
            }
        finally
            {
            if ($ch) curl_close($ch);
            }
        return $reply;
        }
    }



///////////////////////////////////////////////////////////////////////////////

?>