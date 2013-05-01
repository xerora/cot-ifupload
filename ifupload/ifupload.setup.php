<?php
/* ====================
[BEGIN_COT_EXT]
Code=ifupload
Name=Independant File Upload
Category=
Description=Handle file upload fields anywhere
Version=1.0
Date=2013-april-30
Author=
Copyright=
Notes=
SQL=
Auth_guests=R
Lock_guests=RW12345A
Auth_members=R
Lock_members=
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
path=01:string::static:Root folder for files (with CHMOD 775/777)
exts=03:text::gif,jpg,jpeg,png,zip,rar,7z,gz,bz2,pdf,djvu,mp3,ogg,wma,avi,divx,mpg,mpeg,swf,txt:Allowed extensions (comma separated, no dots and spaces)
[END_COT_EXT_CONFIG]
==================== */

defined('COT_CODE') or die('Wrong URL');