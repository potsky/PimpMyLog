<?php

class Parser
{
    public static function getLinesFromBottom( $file , $count = 1 )
    {
        $fl    = @fopen( $file , "r" );
        $lines = array();
        $bytes = 0;

        if ( $fl === false ) return false;

        $count = max( 1 , (int) $count );

        for ($x_pos = 0, $ln = 0, $line = '', $still = true; $still ; $x_pos--) {

            if ( fseek( $fl, $x_pos, SEEK_END ) === -1 ) {
                $still = false;
                $char  = "\n";
            }
            else {
                $char = fgetc( $fl );
            }

            if ($char === "\n") {

                $deal = utf8_encode( $line );
                $line = '';

                if ($deal !== '') {
                    $lines[] = $deal;
                    $count--;
                    if ( $count === 0 ) $still = false;
                }

                // continue directly without keeping the \n
                continue;
            }
            $line = $char . $line;
            $bytes++;
        }

        fclose( $fl );

        return $lines;
    }
}
