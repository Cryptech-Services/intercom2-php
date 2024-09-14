<?php
/*
 * Copyright 2020 Cryptech Services
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
// index.php by loma oopaloopa

// echo "Far out!";

require_once "../intercom2.php";
require_once "./test/intercom2_config_B.php";
use Intercom2\
    {
    Intercom2,
    Intercom2_Exception,
    Intercom2_NetworkCurlException,
    Intercom2_BadEndPointException,
    Intercom2_BadMsgFormatException,
    Intercom2_BadMsgIdException,
    Intercom2_ImposterException,
    Intercom2_MsgHandler,
    Intercom2_MsgCb,
    Intercom2_EndPoint,
    Intercom2_SSL_2WayConf,
    Intercom2_SSL_SimpleConf,
    Intercom2_Reply
    };

class EchoServiceClass implements Intercom2_MsgHandler
    {
    public function intercom2_processMsg(Intercom2_EndPoint $sender, string $rxData) : ?string
        {
        return "PHP EchoServiceClass says " . $sender->getContext() . " SENT " . $rxData;
        }
    }

function echoServiceFunction(Intercom2_EndPoint $sender, string $rxData) : ?string
    {
    return "PHP echoServiceFunction says " . $sender->getContext() . " SENT " . $rxData;
    }

$intercom2->configMsgHandler("echo.class",    new EchoServiceClass()                    );
$intercom2->configMsgHandler("echo.function", new Intercom2_MsgCb("echoServiceFunction"));

$error = $intercom2->receive();
if ($error)
    {
    echo "There was an Intercom2 receive error:\n";
    echo $error;
    }
?>