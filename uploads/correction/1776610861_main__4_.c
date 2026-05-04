#include <stdio.h>
#include <unistd.h>
#include <fcntl.h>
#include <errno.h>
#include <termios.h>
#include <string.h>
#include <sys/ioctl.h>
#include <stdint.h>
#include "serie.h"

// Global variable to handle program termination
volatile sig_atomic_t keep_running = 1;

// Signal handler for Ctrl+C (SIGINT)
void signal_handler(int sig) {
    keep_running = 0;
}

int main(int argc, char **argv)
{
    char buffer[100];                   // un buffer
    int i;
    uint8_t val1='1';
    uint8_t val2='2';
    // ouverture du port à 9600 bauds
    int fd = serialport_init("/dev/ttyACM0", 9600);
    if (fd==-1) return -1;

    // Set up signal handler
    signal(SIGINT, signal_handler);

    // boucle
    for ( ; ; ){
        // Check for termination signal
        if (!keep_running) break;

        //  lecture d'une ligne
        serialport_read_until(fd, buffer, '\r', 99, 10000);

        // suppression de la fin de ligne
        for (i=0 ; buffer[i]!='\r' && i<100 ; i++);
        buffer[i] = 0;
         if(strstr(buffer,"right"))
         {
        // écriture du résultat
        printf("right \n");
        serialport_writebyte(fd,val1);
         }
         if(strstr(buffer,"left"))
         {
        // écriture du résultat
        printf("left \n");
        serialport_writebyte(fd,val2);
         }
    }

    // fermeture du port
    serialport_flush(fd);
    serialport_close(fd);

    return 0;
}
