# SlackAIR

Pour lancer :

- Serveur Web : symfony server:start
- Serveur Websocket : php bin/console gos:websocket:server (-vv) Pour les logs
- Webpack : yarn encore dev / yarn encore production

Pour installer :
  - Installer composer / nodejs / yarn
  - Configurer la BDD et le serveur SMTP dans le .env
  - Faire un : composer install
  - Faire un : yarn install
  - Faire la migration de la bdd : php bin/console doctrine:migrations:migrate
  - Faire un : yarn encore production
