<?php
namespace LouisLun\LaravelUnipay\Enumerations;

enum RequestType: string {
    case CREDIT_CARD = 'creditcard';
    case ATM = 'atm';
    case CVS = 'cvs';
}
