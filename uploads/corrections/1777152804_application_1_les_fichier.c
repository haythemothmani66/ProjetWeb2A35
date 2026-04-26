#include <stdio.h>
#include <stdlib.h>


typedef struct{
int id;
char nom[20];
char prenom[20];
float moy;
}etudiant;

void remplissage(char*fichier);
void affichage(char*fichier);

int main()
{
    printf("\n remplissage fichier etudiant \n");
    remplissage("fetudiant.txt");
    printf(" \n affichage fichier etudiant \n");
    affichage("fetudiant.txt");
    return 0;
}

void remplissage(char*fichier){
FILE*fe=NULL;
char rep,c;
etudiant e;
fe=fopen(fichier,"w");
do{
    printf("continuer (o \n)?");
    scanf(" %c",&rep);
    if((rep=="o") || (rep=="O")){
            printf("identifiant:");
            scanf("%d",&e.id);
            printf("\n nom:");
            fflush(stdin);
            gets(e.nom);
            printf("\n prenom");
            fflush(stdin);
            gets(e.prenom);
            printf("moyenne:");
            scanf("%f",&e.moy);
            printf("\n entreprise(o\n):");
            scanf("%c",2c);
            if(c=="o" || c=="O")
                fprintf(fe,"%d %s %s %2f\n",e.id,e.nom,e.prenom,e.moy);
    }
}while(rep=="o"||rep=="O");
}

void affichage(char*fichier)
{FILE*fe=NULL;
etudiant e;
fe=fopen(fichier,"r");
while(!feof(fe)){
    scanf(fe,"%d %s %s %2f \n",&e.id,e.nom,e.prenom,&e.moy);
    printf(fe,"%d %s %s %2f \n",&e.id,e.nom,e.prenom,&e.moy);
}
fclose(fe);
}



