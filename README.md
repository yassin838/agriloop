# AgriLoop

AgriLoop est un projet web PHP/MySQL developpe en equipe par quatre membres.

## Modules

- Ilyes Mami : categories et offres
- Amri Med Yassine : utilisateurs communs, associations et dons
- Belhoula Firas : avis et signalements
- Aziz Sassi : parcelles et consommations d'eau

## Socle commun deja present

- template FreshCart
- fond GettyImages-2228011090
- connexion PDO
- table utilisateur
- inscription, authentification et deconnexion
- roles agriculteur, acheteur et administrateur
- configuration XAMPP MySQL
- fichiers SQL de creation et reinitialisation
- controles HTML, JavaScript et PHP
- gestion CRUD des utilisateurs dans le back-office
- gestion des rôles et controle serveur des autorisations
- gestion sécurisée des sessions, CSRF et en-têtes HTTP

## Structure du projet

```text
config/          connexion PDO
database/        scripts SQL et installation
model/           classes metier
controller/      validations et traitements
views/frontoffice/ pages utilisateur
views/backoffice/  pages administrateur
views/assets/      template, images et JavaScript
```

## Installation locale

1. Installer le projet dans `C:\xampp\htdocs\agriloop`.
2. Demarrer Apache et MySQL dans XAMPP.
3. Executer :

```powershell
cd C:\xampp\htdocs\agriloop
C:\xampp\php\php.exe database\reset.php
```

4. Ouvrir :

```text
http://localhost/agriloop/
```

5. phpMyAdmin :

```text
http://localhost/phpmyadmin
```

## Comptes de demonstration

```text
Agriculteur
Email : agriculteur@agriloop.tn
Mot de passe : Farmer123!

Acheteur
Email : acheteur@agriloop.tn
Mot de passe : Acheteur123!

Administrateur
Email : admin.agriloop@agriloop.tn
Mot de passe : AgriloopAdmin2026!
```

## Regles d'acces

- L'agriculteur peut proposer un don.
- L'acheteur peut proposer une association.
- L'administrateur peut accepter, modifier ou supprimer les associations.
- L'administrateur peut confirmer, annuler, modifier ou supprimer les dons.

## Organisation Git

`main` contient uniquement le socle commun valide :

- template
- connexion PDO
- utilisateur
- authentification
- navigation commune
- scripts de base

Chaque membre travaille dans sa branche personnelle :

```text
ilyes-mami-offres
med-yassine-amri-dons-associations
belhoula-firas-avis-signalements
aziz-sassi-parcelles-eau
```

Avant de commencer :

```powershell
git switch main
git pull origin main
git switch -c NOM-ET-NOMDUMODULE
```

Chaque membre ne doit modifier que son module, sauf pour un fichier commun
coordonne avec toute l'equipe.

Avant un merge :

```powershell
C:\xampp\php\php.exe -l fichier.php
git diff
git status
git add .
git commit -m "Description claire"
git push -u origin NOM-ET-NOMDUMODULE
```

Ne jamais modifier directement `main`.
