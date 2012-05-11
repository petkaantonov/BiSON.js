<?php

require_once 'bitstream.php';

class BitStreamWriter extends BitStream {

    protected $output = "";

    public function writeRaw( $raw ) {
    
        if( $this->left !== 8 ) {
            $this->output .= self::$chrTable[$this->value];
            $this->value = 0;
            $this->left = 8;
        }
                    
        $this->output .= $raw;
    }
        
    public function write( $val, $count ) {
        $left = $this->left;
        $overflow = max( 0, $count - $left );
        $use = $left < $count ? $left : $count;
        $shift = $left - $use;
        
        
        $this->value += $val >> $overflow << $shift;
        
        $left -= $use;
        
        if( $left === 0 ) {
        
            $this->output .= self::$chrTable[$this->value];
            $this->left = 8;
            $this->value = 0;
            
            if( $overflow > 0 ) {
                $this->write( $val & self::$powTable[$overflow], $overflow );
            }
                   
        }
        else {
            $this->left = $left;
        }
    }
    
    public function open( $data ) {
    
    }
    
    public function close() {
        if( $this->value > 0 ) {
            $this->output .= self::$chrTable[$this->value];
        }

        return $this->output;
    }
}