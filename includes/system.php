<?php

if(!file_exists(__DIR__.'/../tmp')){
    mkdir(__DIR__."/../tmp", 0755);
}

if(!file_exists(__DIR__.'/../protected')){
    mkdir(__DIR__."/../protected", 0755);
}
if(!file_exists(__DIR__.'/../protected/license')){
    mkdir(__DIR__."/../protected/license", 0755);
}