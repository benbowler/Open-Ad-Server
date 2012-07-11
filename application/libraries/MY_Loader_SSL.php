<?php // -*- coding: UTF-8 -*-

/*
 ***********************************************************************************
 *  libraries/MY_Load_SSL.php
 *
 *  This file is part of SmartPPC6.
 *  Copyright (C) 2007 The Orbitsoft Team.
 *
 *  Author: Sergey A. Sukiyazov <sukiyazov@orbita1.ru>
 ***********************************************************************************
 */

// SmartPPC6 CA identifier
define('SSL_AUTHORITY_KEY_IDENTIFIER', 'keyid:FC:33:C6:E7:D2:E7:16:F6:D0:6C:68:90:A1:A8:87:4F:85:1D:DB:4E
DirName:/C=RU/ST=Rostov Region/L=Rostov-on-Don/O=OrbitSoft Ltd./OU=SmartPPC6/CN=SmartPPC6 ROOT CA/emailAddress=smartppc6@orbita1.ru
serial:9A:AE:03:DC:D0:1E:3E:CB');

// SmartPPC6 CA attributes
define('SSL_CA_FILE',                     APPPATH . '/certs/ca.crt');
define('SSL_CA_MD5_FINGERPRINT',          '90:D2:8D:46:CB:A6:3B:0A:7D:07:D9:EC:A6:93:43:9B');
define('SSL_CA_SERIAL',                   '9AAE03DCD01E3ECB');
define('SSL_CA_SERIAL_DEC',               '11145850374707887819');
define('SSL_CA_SUBJECT_KEY_IDENTIFIER',   'FC:33:C6:E7:D2:E7:16:F6:D0:6C:68:90:A1:A8:87:4F:85:1D:DB:4E');

// SmartPPC6 main certificate attributes
define('SSL_CERT_FILE',                   APPPATH . '/certs/smartppc6.crt');
define('SSL_CERT_MD5_FINGERPRINT',        '4A:59:F1:0E:4E:09:37:BA:43:C6:21:0B:88:9F:58:B0');
define('SSL_CERT_SERIAL',                 '02');
define('SSL_CERT_SERIAL_DEC',             '2');
define('SSL_CERT_SUBJECT_KEY_IDENTIFIER', 'D5:A0:B6:41:25:35:8D:99:0E:5E:35:1E:56:55:1B:37:90:0D:A1:85');

?>