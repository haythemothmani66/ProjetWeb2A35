#include "fonctions.h"
#include <SDL/SDL.h>
#include <SDL/SDL_image.h>
#include <SDL/SDL_ttf.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <math.h>
#define PLATFORM_COUNT 100


SDL_Surface* scaleSurface(SDL_Surface *surface, int width, int height) {
    SDL_Surface *resized = SDL_CreateRGBSurface(0, width, height, surface->format->BitsPerPixel,
                                                 surface->format->Rmask, surface->format->Gmask,
                                                 surface->format->Bmask, surface->format->Amask);
    if (!resized) {
        printf("Failed to create scaled surface: %s\n", SDL_GetError());
        return surface;
    }
    SDL_Rect srcRect = {0, 0, surface->w, surface->h};
    SDL_Rect dstRect = {0, 0, width, height};
    if (SDL_SoftStretch(surface, &srcRect, resized, &dstRect) != 0) {
        printf("SoftStretch failed: %s\n", SDL_GetError());
        SDL_FreeSurface(resized);
        return surface;
    }
    return resized;
}

int initGame(GameState *game) {
    srand(time(NULL));
    if (SDL_Init(SDL_INIT_VIDEO) < 0) {
        printf("SDL init error: %s\n", SDL_GetError());
        return 0;
    }
    if (TTF_Init() == -1) {
        printf("TTF init error: %s\n", TTF_GetError());
        return 0;
    }

    game->screenWidth = 1920;
    game->screenHeight = 1080;
    game->screen = SDL_SetVideoMode(1920, 1080, 32, SDL_SWSURFACE | SDL_DOUBLEBUF | SDL_FULLSCREEN);
    if (!game->screen) {
        printf("Failed to set video mode: %s\n", SDL_GetError());
        return 0;
    }
    SDL_WM_SetCaption("Platform Game", NULL);

    game->background = IMG_Load("background.png");
    if (!game->background) {
        printf("Failed to load background: %s\n", IMG_GetError());
        return 0;
    }

    game->scaledBackground = scaleSurface(game->background, game->background->w, SCREEN_HEIGHT);
    if (!game->scaledBackground) {
        printf("Failed to scale background.\n");
        return 0;
    }

    game->helpImage = IMG_Load("help.png");
    if (!game->helpImage) {
        printf("Warning: Failed to load help image: %s\n", IMG_GetError());
    }

    for (int i = 0; i < PLATFORM_COUNT - 2; i++) {
        int isAnimated = (i != 0 && i % 5 == 0);
        game->platforms[i].image = IMG_Load(isAnimated ? "platform3.png" : "platform1.png");
        if (!game->platforms[i].image) {
            printf("Failed to load platform image (%d): %s\n", i, IMG_GetError());
            return 0;
        }
        game->platforms[i].position.x = 200 + i * 250 + (rand() % 80 - 40);
        game->platforms[i].position.y = isAnimated ? 200 + rand() % 30 : 500;
        game->platforms[i].originalY = game->platforms[i].position.y;
        game->platforms[i].isAnimated = isAnimated;
        game->platforms[i].animationOffset = rand() % 100;
        game->platforms[i].isVisible = 1;
    }

    int specialX = 400;
    int specialY = 300;

    for (int i = PLATFORM_COUNT - 2; i < PLATFORM_COUNT; i++) {
        game->platforms[i].image = IMG_Load("platform3.png");
        if (!game->platforms[i].image) {
            printf("Failed to load special platform image: %s\n", IMG_GetError());
            return 0;
        }
        game->platforms[i].position.x = specialX;
        game->platforms[i].originalY = game->platforms[i].position.y;
        game->platforms[i].isAnimated = 1;
        game->platforms[i].animationOffset = rand() % 100;
        game->platforms[i].isVisible = 1;
    }

    game->scrollX = 0;
    game->scrollY = 0;
    game->showHelp = 0;
    game->startTime = time(NULL);
    game->running = 1;

    return 1;
}

void handleInput(GameState *game) {
    SDL_Event event;
    const Uint8 *keystates = SDL_GetKeyState(NULL);

    while (SDL_PollEvent(&event)) {
        if (event.type == SDL_QUIT) game->running = 0;
        if (event.type == SDL_KEYDOWN) {
            if (event.key.keysym.sym == SDLK_ESCAPE) game->running = 0;
            if (event.key.keysym.sym == SDLK_h) game->showHelp = !game->showHelp;
        }
    }

    if (keystates[SDLK_LEFT]) game->scrollX -= 10;
    if (keystates[SDLK_RIGHT]) game->scrollX += 10;
    if (keystates[SDLK_UP]) game->scrollY -= 10;
    if (keystates[SDLK_DOWN]) game->scrollY += 10;
}

void updateGame(GameState *game) {
    int ticks = SDL_GetTicks();
    for (int i = 0; i < PLATFORM_COUNT; i++) {
        if (game->platforms[i].isAnimated) {
            game->platforms[i].position.y = game->platforms[i].originalY +
                (int)(5 * sin((ticks + game->platforms[i].animationOffset) * 0.005));
        }
    }
}

void drawScrollingBackground(GameState *game) {
    int bgWidth = game->scaledBackground->w;
    int xOffset = -(game->scrollX % bgWidth);
    SDL_Rect src = {0, 0, bgWidth, SCREEN_HEIGHT};
    SDL_Rect dest;
    for (int x = xOffset; x < SCREEN_WIDTH; x += bgWidth) {
        dest.x = x;
        dest.y = 0;
        SDL_BlitSurface(game->scaledBackground, &src, game->screen, &dest);
    }
}

void renderGame(GameState *game) {
    SDL_FillRect(game->screen, NULL, SDL_MapRGB(game->screen->format, 0, 0, 0));
    drawScrollingBackground(game);

    for (int i = 0; i < PLATFORM_COUNT; i++) {
        if (game->platforms[i].isVisible && game->platforms[i].image) {
            SDL_Rect pos = {
                game->platforms[i].position.x - game->scrollX,
                game->platforms[i].position.y - game->scrollY,
                0, 0
            };
            if (pos.x + game->platforms[i].image->w > 0 && pos.x < SCREEN_WIDTH) {
                SDL_BlitSurface(game->platforms[i].image, NULL, game->screen, &pos);
            }
        }
    }

    if (game->showHelp && game->helpImage) {
        SDL_Rect helpPos = {
            (SCREEN_WIDTH - game->helpImage->w) / 2,
            (SCREEN_HEIGHT - game->helpImage->h) / 2,
            0, 0
        };
        SDL_BlitSurface(game->helpImage, NULL, game->screen, &helpPos);
    }

    time_t currentTime = time(NULL);
    int elapsed = (int)difftime(currentTime, game->startTime);
    char timeText[64];
    sprintf(timeText, "Time: %02d:%02d", elapsed / 60, elapsed % 60);

    TTF_Font *font = TTF_OpenFont("arial.ttf", 24);
    if (font) {
        SDL_Color white = {255, 255, 255};
        SDL_Surface *text = TTF_RenderText_Solid(font, timeText, white);
        if (text) {
            SDL_Rect textPos = {10, 10, 0, 0};
            SDL_BlitSurface(text, NULL, game->screen, &textPos);
            SDL_FreeSurface(text);
        }
        TTF_CloseFont(font);
    }

    SDL_Flip(game->screen);
}

void cleanupGame(GameState *game) {
    if (game->background) SDL_FreeSurface(game->background);
    if (game->scaledBackground && game->scaledBackground != game->background) SDL_FreeSurface(game->scaledBackground);
    if (game->helpImage) SDL_FreeSurface(game->helpImage);
    for (int i = 0; i < PLATFORM_COUNT; i++) {
        if (game->platforms[i].image) SDL_FreeSurface(game->platforms[i].image);
    }
    TTF_Quit();
    SDL_Quit();
}
