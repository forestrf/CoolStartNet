<?php

function isInteger($input){
    return(ctype_digit(strval($input)));
}