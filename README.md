# TodoAndCo
Openclassrooms, cursus PHP/symfony  
**Projet 8: Amélioration d'une application existante**

## Installation
- Cloner le projet
- Executer `composer install`
- Executer `npm install` ou `yarn install`
- Compiler les assets front: `npm run build` ou `yarn encore production`  
  (autres commandes disponibles, voir package.json)
  
- Configurer l'accès à mysql dans .env ou .env.local
- Créer la base de donnée : `php bin/console doctrine:database:create`  
  puis `php bin/console doctrine:schema:update --force`
- Installer les datafixtures si besoin : `php bin/console doctrine:fixtures:load`
  

- Pour les tests, utilisez l'extension php sqlite ou configurez votre base de donnée de test dans .env.test
- Pour executer les tests : `php bin/phpunit`
