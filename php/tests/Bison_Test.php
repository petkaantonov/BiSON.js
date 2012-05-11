<?php

require_once "../bison.php";


class BisonTest extends PHPUnit_FrameWork_TestCase {

    public function code( $data ) {
        $encoded = BISON::encode( $data );
        $this->assertEquals( BISON::decode( $encoded ), $data, "structures should be equal" );
    }
    
    public function testSimple() {
        $this->code( 0);
        $this->code( 1.23);
        $this->code( 'foo');
        $this->code( false);
        $this->code( true);
        $this->code( null);
        $this->code( array() );
        $this->code( (object)array());        
    }
    
    public function testSmallInteger() {
        $this->code( array(0));
        $this->code( array(1));
        $this->code( array(32));
        $this->code( array(64));
        $this->code( array(117));

        $this->code( array(-0));
        $this->code( array(-1));
        $this->code( array(-32));
        $this->code( array(-64));
        $this->code( array(-117));

        $this->code( array(0, 1, 1, 1, 0));
        $this->code( array(0, 1, 0, 1, 0));
        $this->code( array(0, 2, 0, 2, 0));    
    }
    
    
    
    public function testMediumInteger() {
        $this->code( array(116));
        $this->code( array(256));
        $this->code( array(1024));
        $this->code( array(65535));

        $this->code( array(-116));
        $this->code( array(-256));
        $this->code( array(-1024));
        $this->code( array(-65535));    
    }
    
    public function testBigInteger() {
        $this->code( array(65536));
        $this->code( array(5040213));
        $this->code( array(1010123024));
        $this->code( array(2147483647));

        $this->code( array(-65536));
        $this->code( array(-5040213));
        $this->code( array(-1010123024));
        $this->code( array(-2147483647));    
    }
    
    
    public function testSmallFloat() {
        $this->code( array(0.0));
        $this->code( array(1.15));
        $this->code( array(1.16));                                
        $this->code( array(32.045));        
        $this->code( array(64.171));

        $this->code( array(117.123912));

        $this->code( array(-0));
        $this->code( array(-1.15));
        $this->code( array(-1.16));
        $this->code( array(-1.123));
        $this->code( array(-32.045));
        $this->code( array(-64.171));
        $this->code( array(-117.123912));    
    }
    
    public function testMediumFloat() {
        $this->code( array(116.2137));
        $this->code( array(256.214));
        $this->code( array(1024.001));
        $this->code( array(65535.01));

        $this->code( array(-128.2137));
        $this->code( array(-256.214));
        $this->code( array(-1024.001));
        $this->code( array(-65535.01));    
    }
    
    public function testBigFloat() {

        $this->code( array(65536));
        $this->code( array(5040213));
        $this->code( array(1010123024));
        $this->code( array(2147483647));

        $this->code( array(-65536));
        $this->code( array(-5040213));
        $this->code( array(-1010123024));
        $this->code( array(-2147483647));    
    }
    
    public function testSmallString() {
        $this->code( array(str_repeat('-',0)));
        $this->code( array(str_repeat('-',1)));
        $this->code( array(str_repeat('-',28)));
        $this->code( array(str_repeat('-',29)));    
    }
    
    public function testMediumString() {
        $this->code( array(str_repeat('-',30)));
        $this->code( array(str_repeat('-',128)));
        $this->code( array(str_repeat('-',255)));    
    }
    
    public function testAsciiString() {
        $str = "";
        $l = 256;
        while( $l-- ) {
            $str.=chr($l);
        }
        
        $this->code($str);
    }
    
    //No unicode support
    public function testUnicodeString() {
    
    }
    
    public function testBoolean() {
        $this->code( array(true));
        $this->code( array(false));    
    }
    
    public function testNull() {
         $this->code( array(null));   
    }
    
    public function testArray() {
        $this->code( array(1, 2, 3));
        $this->code( array('foo', 'bla'));
        $this->code( array(4, 5, array(array(array(array('test'), 1)), 2)));    
    }
    
    public function testObject() {


        $this->code( (object)array(
            "a" => 1,
            "b" => 2,
            "c" => 3,
            "d" => 4
        ));

        $this->code( (object)array(
            "a" => 1.12,
            "b" => 2.12,
            "c" => 3.12,
            "d" => 4.12
        ));

        $this->code( (object)array(
            "hello" => 123,
            "foooo" => 213,
            "'test world'" => 1245
        ));

        $this->code( (object)array(
            "'1123'" => 'blub',
            "'_\$cucu'" => '....',
            "'   '" => 'hello'
        ));

        $this->code( (object)array(

            "'one'" => (object)array(
                "hello" => 123,
                "foooo" => 213,
                "'test world'" => 1245
            ),

            "'two'" => (object)array(
                "'1123'" => 'blub',
                "'_\$cucu'" => '....',
                "'   '" => 'hello'
            ),

            "'ooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo'" => (object)array(
                "longKey" => true
                            ),

                "three" => (object)array(

                            )

            ));
    
    }
    
    

    public function testMixed() {
    
    }

}