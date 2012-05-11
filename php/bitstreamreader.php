<?php

require_once 'bitstream.php';

class BitStreamReader extends BitStream {

    protected $input;
    
    public function open( $data ) {
        $this->max = strlen( $data );
        $this->index = 0;
        $this->input = array_merge(unpack( "C*", $data ));
        $this->value = $this->input[0];
    }
    
    public function close() {
        return $this->input;
    }

    public function readRaw( $count ) {
    
        if( $this->left !== 8 ) {
            $this->index++;
            $this->value = 0;
            $this->left = 8;
        }
        
        $data = "";
        for( $i = 0; $i < $count; ++$i ) {
            $data .= chr( $this->input[$this->index+$i] );
        }
        
        $this->index += $count;
        $this->value = $this->input[$this->index];
        return $data;
    
    }
    
    public function read( $count ) {

        if( $this->index >= $this->max ) {
            return NULL;
        }

        $left = $this->left;

        $overflow = $count - $left;
        $use = $left < $count ? $left : $count;
        $shift = $left - $use;

        $val = ( $this->value & self::$maskTable[$left] ) >> $shift;
        $left -= $use;

        if( $left === 0 ) {
            $this->value = @$this->input[ ++$this->index ];
            $this->left = 8;
            if( $overflow > 0 ) {
                $val = $val << $overflow | $this->read( $overflow );
            }
        }
        else {
            $this->left = $left;
        }

        return $val;

    }

}