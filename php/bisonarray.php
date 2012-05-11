<?php
//Simplify handling arrays, associative arrays and objects as the same thing
//Doing magic with references and normal arrays just doesn't work the way it works in js :/
class BisonArray implements ArrayAccess {

    private $array;
    private $convertToObject = TRUE;
    private $isSequential = FALSE;
    
    public function is_associative() {
        return !$this->isSequential;
    }
    
    public function __construct($obj = TRUE, $isSequential = FALSE ) {
        $this->array = array();
        $this->convertToObject = $obj;
        $this->isSequential = $isSequential;
    }
    
    public function toArray() {
        $array = $this->array;
        $this->array = NULL;
        foreach( $array as $key => &$value ) {
            if( is_object($value) ) { //Everything but scalars are supposed to be BisonArrays
                $value = $value->toArray();
            }
        }
        if( $this->isSequential ) {
            return $array;
        }
        
        if( $this->convertToObject ) {
            return (object)$array;
        }
        else {
            return $array;
        }
    }
    
    public function push( $value ) {
        $this->array[] = $value;
    }
    
    public function offsetExists( $offset ) {
        return array_key_exists( $offset, $this->array );
    }

    public function offsetGet( $offset ) {
        return $this->array[$offset];
    }
    
    public function offsetSet( $offset, $value ) {
        $this->array[$offset] = $value;
    }
    
    public function offsetUnset( $offset ) {
        unset( $this->array[$offset] );    
    }

}