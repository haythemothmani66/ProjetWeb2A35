#include "fonctions1.h"
#include <SDL/SDL.h>
#include <SDL/SDL_image.h>
#include <stdio.h>
#include <stdlib.h>

int main(int argc, char *argv[]) {
    // Initialize SDL
    if (SDL_Init(SDL_INIT_VIDEO | SDL_INIT_AUDIO) < 0) {
        fprintf(stderr, "Failed to initialize SDL: %s\n", SDL_GetError());
        return EXIT_FAILURE;
    }

    GameState game;

    if (!initGame(&game)) {
        fprintf(stderr, "Failed to initialize game.\n");
        SDL_Quit();
        return EXIT_FAILURE;
    }

    Uint32 lastTime = SDL_GetTicks();
    while (game.running) {
        handleInput(&game);
        updateGame(&game);
        renderGame(&game);

        // Maintain ~60 FPS (16.67ms per frame)
        Uint32 currentTime = SDL_GetTicks();
        Uint32 deltaTime = currentTime - lastTime;
        if (deltaTime < 16) {
            SDL_Delay(16 - deltaTime);
        }
        lastTime = currentTime;
    }

    cleanupGame(&game);
    return EXIT_SUCCESS;
}
