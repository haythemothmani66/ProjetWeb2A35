#include <htc.h>

void main(){

//config

TRISA3 = 1;
TRISB = 0;

// initialisation

PORTB = 0;

while(1){

if(RA3 == 0)
  PORTB = 0x05 ; 
else
  PORTB = 0x0A ; 
}



}