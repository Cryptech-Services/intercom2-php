<?php
/*
 * Copyright 2019 Cryptech Services
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
// intercom2_config_B.php by loma oopaloopa

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

$ssl = new Intercom2_SSL_2WayConf();
$ssl->setPassphrase("garbanzo");
$ssl->setRootCert("/development/intercom2/certs/intercom2_CA.pem");
$ssl->setClientCert("/development/intercom2/certs/client_alice_4.key", "/development/intercom2/certs/client_alice_4.pem");

$intercom2 = new Intercom2($ssl);
$intercom2->configSelf(1);
$endPoint9 = $intercom2->configEndPoint(9, "EP=>alice_4.localdomain", "alice_4.localdomain", 23009);

?>