<?php

require_once '../bison.php';

function decodeJSONcallback($value) {
    return json_decode($value);
}

function encodeJSONcallback($value) {
    return json_encode($value);
}

function decodeBISONcallback($value) {
    return BISON::decode($value);
}

function encodeBISONcallback($value) {
    return BISON::encode($value);
}

function timeit( $callback, $value ) {

    $start = microtime(true);
    $count = 0;
    $d;
    
    while( microtime(true) < $start + 0.0333 ) {
        $d = call_user_func( $callback, $value );
        $count++;
    }
    
    return floor( $count * 3.333 );

}

$data = array(

    "integers" => array(
        1,
        5,
        100,
        150,
        2501,
        47123,
        213123,
        2147483647,
        -1,
        -5,
        -100,
        -150,
        -2501,
        -47123,
        -213123,
        -2147483647
    ),

    "floats" => array(

        1.123,
        5.122,
        100.1494,
        150.303,
        2501.1234,
        47123.573,
        213123.45,
        21474836.12,

        -1.123,
        -5.122,
        -100.1494,
        -150.303,
        -2501.1234,
        -47123.573,
        -213123.45,
        -21474836.12

    ),

    "bools" => array(true, false, null, false, true, null, false, true, null, false, true, null, false, true, null),
    
    "objects" => array(

        array(),
        array( "'adasdasdas'" => 'foo'),
        array( "'adasdasdas'" => array() ),
        array( "'adasdasdas'" => array(), "'bdasdsad'" => array() ),
        array( "'cdasdasdas'" => array( "'fooOO'" => array() ), "'diadsdasdsad'" => array() )

    ),

    "arrays" => array(
        array(),
        array(array()),
        array(array()),
        array( array(), array() ),
        array( array(array()), array())
    ),

    "strings" => array(

        str_repeat( "-", 2),
        str_repeat( "-", 256),
        str_repeat( "-", 2560),
        str_repeat( "-", 70000)

    )

);

foreach( $data as $key => $value ) {

    echo "\n$key:";
    
    $count = count($value );
    
    echo "    encode BISON".
    timeit( "encodeBISONcallback", $value ) * $count;
 
    echo "    encode JSON".
    timeit( "encodeJSONcallback", $value ) * $count;
    
    $val = BISON::encode( $value );
    echo "    decode BISON".
    timeit( "decodeBISONcallback", $val ) * $count;

    $val = json_encode( $value );
    echo "    decode JSON".
    timeit( "decodeJSONcallback", $val ) * $count;
    
    $enc = BISON::encode( $value );
    $bl = strlen( $enc );
    $jl = strlen( json_encode( $value ) );
    
    $ratio = 1 / $jl * $bl;
    
    echo "     Ratio:".$ratio;
 
}