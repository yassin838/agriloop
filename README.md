# AgriLoop - socle commun

Ce depot contient uniquement les elements communs :

- template FreshCart ;
- connexion PDO ;
- table utilisateur ;
- inscription, connexion et deconnexion ;
- roles agriculteur, acheteur et administrateur ;
- scripts SQL communs.

Les formulaires, tables, modeles et back-office Dons/Associations
appartiennent a la branche `med-yassine-amri-dons-associations`.

## Branches

- `main` : socle commun ;
- `med-yassine-amri-dons-associations` : module Yassine ;
- branches individuelles des autres membres.

## Demarrer une branche membre

```powershell
git switch main
git pull origin main
git switch -c NOM-ET-NOMDUMODULE
```

Ne pas modifier directement main.
