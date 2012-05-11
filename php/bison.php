<?php

require_once "bisonarray.php";

class BISON {

    static public $powTable = array(
        1,
        10,
        100,
        1000,
        10000,
        100000,
        1000000,
        10000000,
        100000000,
        1000000000,
        10000000000,
        100000000000,
        1000000000000,
        10000000000000,
        1.0E+14,
        1.0E+15
    );

    static private function _is_object( $obj ) {
        
        if( is_array( $obj ) ) {
            return ($obj !== array_values($obj)); //The array is associative which means pretty much the same here as object
        }
        return is_object( $obj );
    }

    static private function _decode( ) {
    
    }
    
    static private function _encode( $writer, $value, $top ) {
    
        if( is_scalar ( $value ) ) {
        
            if( is_string( $value ) ) {
                $len = strlen( $value );

                $writer->write(3, 3);

                if ($len > 65535) {
                    $writer->write(31, 5);
                    $writer->write($len >> 24 & 0xff, 8);
                    $writer->write($len >> 16 & 0xff, 8);
                    $writer->write($len >> 8 & 0xff, 8);
                    $writer->write($len & 0xff, 8);

                } else if ($len > 255) {
                    $writer->write(30, 5);
                    $writer->write($len >> 8 & 0xff, 8);
                    $writer->write($len & 0xff, 8);

                } else if ($len > 28) {
                    $writer->write(29, 5);
                    $writer->write($len, 8);

                } else {
                    $writer->write($len, 5);
                }

                $writer->writeRaw($value);
            
            }
            
            else if( is_bool( $value ) ) {
                $writer->write( (int)$value, 5 );
            }
                        
            //Number, int/long or float/double
            else {
                $isDouble = is_double( $value );
                $sign = 0;
                
                if( $value < 0 ) {
                    $value = -$value;
                    $sign = 1;
                }
                $writer->write( 1 + $isDouble, 3 );
                
                if( $isDouble ) {
                    $shift = 1;
                    $step = 10;
                    
                    while( $step <= $value ) {
                        $shift++;
                        $step * 10;
                    }
                    
                    $shift = ( 8 - $shift ) + 1;
                    $value = round( $value * (1000000000 / $step) );
                    
                    while( $value / 10 === ( ( $value / 10 | 0 ) ) ) {
                        $value /= 10;
                        $shift--;
                    }
                }
                
                
                
                if ($value < 2) {
                    $writer->write($value, 4);

                } else if ($value < 16) {
                    $writer->write(1, 3);
                    $writer->write($value, 4);

                } else if ($value < 256) {
                    $writer->write(2, 3);
                    $writer->write($value, 8);

                } else if ($value < 4096) {
                    $writer->write(3, 3);
                    $writer->write($value >> 8 & 0xff, 4);
                    $writer->write($value & 0xff, 8);

                } else if ($value < 65536) {
                    $writer->write(4, 3);
                    $writer->write($value >> 8 & 0xff, 8);
                    $writer->write($value & 0xff, 8);

                } else if ($value < 1048576) {
                    $writer->write(5, 3);
                    $writer->write($value >> 16 & 0xff, 4);
                    $writer->write($value >> 8 & 0xff, 8);
                    $writer->write($value & 0xff, 8);

                } else if ($value < 16777216) {
                    $writer->write(6, 3);
                    $writer->write($value >> 16 & 0xff, 8);
                    $writer->write($value >> 8 & 0xff, 8);
                    $writer->write($value & 0xff, 8);

                } else {
                    $writer->write(7, 3);
                    $writer->write($value >> 24 & 0xff, 8);
                    $writer->write($value >> 16 & 0xff, 8);
                    $writer->write($value >> 8 & 0xff, 8);
                    $writer->write($value & 0xff, 8);
                }

                $writer->write($sign, 1);
                
                

                if ( $isDouble ) {
                    $writer->write($shift, 4);
                }
            }
        
        }
        else if( is_null( $value ) ) {
            $writer->write( 2, 5 );
        }
        else if( self::_is_object( $value ) ) { //Objects and associative arrays
            $writer->write( 5, 3 );
            foreach( $value as $key => $val  ) {
                self::_encode( $writer, $key, FALSE );
                self::_encode( $writer, $val, FALSE );
            }
            
            if( !$top ) {
                $writer->write( 6, 3 );
            }
        }
        else { //Normal sequential arrays
            $writer->write( 4, 3 );
            $len = count( $value );
            
            for( $i = 0; $i < $len; ++$i ) {
                self::_encode( $writer, $value[$i], FALSE );
            }
            
            if( !$top ) {
                $writer->write( 6, 3 );
            }
        }
    
    }

    static public function decode( $string, $associative_arrays = FALSE ) {
        require_once( "bitstreamreader.php" );
        
        $reader = new BitStreamReader();
        $reader->open( $string );
        
        $getKey = FALSE;
        $stack = array();
        $decoded = NULL;
        $key;
        $value;
        $i = -1;
        $top = NULL;
        $isObj = FALSE;
        
        $breaking = FALSE;
        $continuing = FALSE;
        
        while( true ) {
            $type = $reader->read(3);
            //TODO: use constants instead of magic integers
            switch( $type ) {
            
                //Null / bool / eos
                case 0:
                
                    $value = $reader->read(2);
                    
                    if( $value === 2 ) {
                        $value = NULL;
                    }
                    else if( $value < 2 ) {
                        $value = !!$value;
                    }
                    else if( $value === 3 ) {
                        $breaking = TRUE;
                    }
                    
                    break;
                
                
                //Int / Float
                case 1:
                case 2:
                
                    switch( $reader->read(3) ) {

                        case 0:
                            $value = $reader->read(1);
                            break;

                        case 1:
                            $value = $reader->read(4);
                            break;

                        case 2:
                            $value = $reader->read(8);
                            break;

                        case 3:
                            $value = ($reader->read(4) << 8)
                                    + $reader->read(8);

                            break;

                        case 4:
                            $value = ($reader->read(8) << 8)
                                    + $reader->read(8);

                            break;

                        case 5:
                            $value = ($reader->read(4) << 16)
                                    + ($reader->read(8) << 8)
                                    + $reader->read(8);

                            break;

                        case 6:
                            $value = ($reader->read(8) << 16)
                                    + ($reader->read(8) << 8)
                                    + $reader->read(8);

                            break;

                        case 7:
                            $value = ($reader->read(8) << 24)
                                    + ($reader->read(8) << 16)
                                    + ($reader->read(8) << 8)
                                    + $reader->read(8);

                            break;
                    }
                    
                    if( $reader->read(1) ) {
                        $value = -$value;
                    }
                    
                    if( $type === 2 ) {
                        $value /= self::$powTable[$reader->read(4)];
                    }
                
                    break;
                
                //String
                case 3:
                
                    $size = $reader->read(5);
                    
                    switch( $size ) {
                        case 31:
                            $size = ($reader->read(8) << 24)
                                   + ($reader->read(8) << 16)
                                   + ($reader->read(8) << 8)
                                   + $reader->read(8);

                            break;

                        case 30:
                            $size = ($reader->read(8) << 8)
                                   + $reader->read(8);

                            break;

                        case 29:
                            $size = $reader->read(8);
                            break;
                    }
                    
                    $value = $reader->readRaw($size);
                    
                    if( $getKey ) {
                        $key = $value;
                        $getKey = FALSE;
                        $continuing = TRUE;
                    }
                    
                    break;
            
                //Object or Array
                
                case 4:
                case 5:
                
                    $getKey = $type === 5;
                    
                    if( $getKey ) {
                        $value = new BisonArray( !$associative_arrays, FALSE );
                    }
                    else {
                        $value = new BisonArray( FALSE, TRUE );
                    }
                    
                    if( $decoded === NULL ) {
                        $decoded = $value;                        
                    }
                    else {
                        
                        if( $isObj ) {
                            $top[$key] = $value;
                        }
                        else {
                            $top->push($value);
                        }
                    }

                    $top = $stack[++$i] = $value;
                    $isObj = $top->is_associative();
                                        
                    $continuing = TRUE;
                
                    break;
                //Close Object
                case 6:
                    $top = $stack[--$i];
                    $getKey = $isObj = $top->is_associative();
                    $continuing = TRUE;
                    break;
            }
            
            if( $breaking ) {
                $breaking = FALSE;
                break;
            }
            else if( $continuing ) {
                $continuing = FALSE;
                continue;
            }

            if( $isObj ) {
                $top[$key] = $value;
                $getKey = TRUE;
            }
            else if( $top !== NULL ) {
                $top->push( $value );
            }
            else {
                return $value;
            }
        }
        
        return $decoded->toArray();
        
    }
        
    static public function encode( $value ) {
        require_once( "bitstreamwriter.php" );
        
        $writer = new BitStreamWriter();
        self::_encode( $writer, $value, true );
        $writer->write( 0, 3 );
        $writer->write( 3, 2 );
        return $writer->close();
    }

}