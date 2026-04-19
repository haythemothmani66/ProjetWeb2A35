#include "MainWindow.h"
#include "connection.h"
#include <QApplication>
#include <QMessageBox>
#include <QSqlDatabase>
#include <QSqlQuery>
#include <QSqlError>
#include <QDebug>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);

    // Utiliser la classe Connection avec QODBC pour Oracle
    Connection& conn = Connection::createInstance();

    if (!conn.createConnection()) {
        QMessageBox::critical(nullptr, "Erreur de connexion",
                              "Impossible de se connecter à la base de données Oracle.\n"
                              "Vérifiez :\n"
                              "- Que Oracle est en cours d'exécution\n"
                              "- Que le DSN 'gestionsummerclub' est configuré\n"
                              "- Que les identifiants sont corrects (leaders/0000)");
        return -1;
    }

    qDebug() << "✓ Connexion Oracle établie avec succès";

    QSqlQuery query;

    // Vérifier/Créer la table club si elle n'existe pas
    if (!query.exec("CREATE TABLE club ("
                    "ID_C NUMBER PRIMARY KEY, "
                    "NOM_C VARCHAR2(100) NOT NULL, "
                    "TYPE_C VARCHAR2(50), "
                    "DATEETHEURE_C TIMESTAMP, "
                    "DUREE_C NUMBER, "
                    "NIVEAUADHERENTS_C VARCHAR2(50), "
                    "COACHASSIGNE VARCHAR2(100))")) {
        if (!query.lastError().text().contains("name is already used", Qt::CaseInsensitive) &&
            !query.lastError().text().contains("existe déjà", Qt::CaseInsensitive)) {
            qDebug() << "Info club:" << query.lastError().text();
        } else {
            qDebug() << "✓ Table 'club' déjà existante";
        }
    } else {
        qDebug() << "✓ Table 'club' créée avec succès";
    }

    // Vérifier/Créer la table COACH si elle n'existe pas
    if (!query.exec("CREATE TABLE COACH ("
                    "ID_CO NUMBER PRIMARY KEY, "
                    "NOM_CO VARCHAR2(100) NOT NULL, "
                    "PRENOM_CO VARCHAR2(100) NOT NULL, "
                    "SPECIALITE_CO VARCHAR2(50), "
                    "EXPERIENCE_CO NUMBER)")) {
        if (!query.lastError().text().contains("name is already used", Qt::CaseInsensitive) &&
            !query.lastError().text().contains("existe déjà", Qt::CaseInsensitive)) {
            qDebug() << "Info COACH:" << query.lastError().text();
        } else {
            qDebug() << "✓ Table 'COACH' déjà existante";
        }
    } else {
        qDebug() << "✓ Table 'COACH' créée avec succès";
    }

    // Vérifier/Créer la table MATERIELS si elle n'existe pas
    if (!query.exec("CREATE TABLE MATERIELS ("
                    "REF_M NUMBER PRIMARY KEY, "
                    "NOM_M VARCHAR2(100) NOT NULL, "
                    "STOCK_M NUMBER, "
                    "BESOIN_M VARCHAR2(100), "
                    "COUT_M NUMBER, "
                    "CATEGORIE_M VARCHAR2(100))")) {
        if (!query.lastError().text().contains("name is already used", Qt::CaseInsensitive) &&
            !query.lastError().text().contains("existe déjà", Qt::CaseInsensitive)) {
            qDebug() << "Info MATERIELS:" << query.lastError().text();
        } else {
            qDebug() << "✓ Table 'MATERIELS' déjà existante";
        }
    } else {
        qDebug() << "✓ Table 'MATERIELS' créée avec succès";
    }

    // Créer une séquence pour l'auto-incrémentation de REF_M
    if (!query.exec("CREATE SEQUENCE SEQ_MATERIELS_REF_M START WITH 1 INCREMENT BY 1")) {
        if (!query.lastError().text().contains("name is already used", Qt::CaseInsensitive) &&
            !query.lastError().text().contains("existe déjà", Qt::CaseInsensitive)) {
            qDebug() << "Info séquence SEQ_MATERIELS_REF_M:" << query.lastError().text();
        } else {
            qDebug() << "✓ Séquence 'SEQ_MATERIELS_REF_M' déjà existante";
        }
    } else {
        qDebug() << "✓ Séquence 'SEQ_MATERIELS_REF_M' créée avec succès";
    }

    // Créer un trigger pour assigner automatiquement la valeur de la séquence à REF_M
    if (!query.exec("CREATE OR REPLACE TRIGGER TRG_MATERIELS_REF_M "
                    "BEFORE INSERT ON MATERIELS "
                    "FOR EACH ROW "
                    "BEGIN "
                    "   IF :NEW.REF_M IS NULL THEN "
                    "       SELECT SEQ_MATERIELS_REF_M.NEXTVAL INTO :NEW.REF_M FROM DUAL; "
                    "   END IF; "
                    "END;")) {
        qDebug() << "Erreur création trigger TRG_MATERIELS_REF_M:" << query.lastError().text();
    } else {
        qDebug() << "✓ Trigger 'TRG_MATERIELS_REF_M' créé avec succès";
    }

    MainWindow w;
    w.show();

    return a.exec();
}
