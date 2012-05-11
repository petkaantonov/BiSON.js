<?php

abstract class BitStream {

    protected static $chrTable;
    protected static $maskTable;
    protected static $powTable;
    
    protected $value = 0;
    protected $left = 8;
    protected $index = 0;
    protected $max = 0;

    public function __construct()
    {
        if( is_array( self::$chrTable ) ) {
            return;
        }
        
        self::$chrTable = array();
        self::$maskTable = array();
        self::$powTable = array();
        
        $l = 256;
        
        while( $l-- ) {
            self::$chrTable[$l] = chr($l);
        }
        
        $l = 9;
        
        while( $l-- ) {
            self::$maskTable[$l] = ~( ( self::$powTable[$l] = pow( 2, $l ) - 1 ) ^ 0xFF);
        }
                
    }
    
    abstract public function open( $data );
    abstract public function close();
}

?>