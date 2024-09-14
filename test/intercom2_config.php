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
// intercom2_config.php by loma oopaloopa

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

$intercom2 = new Intercom2();
$intercom2->configSelf(1);
$endPoint2 = $intercom2->configEndPoint(2, "End Point 2", "labdog.localdomain", 23002);
$endPoint3 = $intercom2->configEndPoint(3, "End Point 3", "labdog.localdomain", 23003);

?>