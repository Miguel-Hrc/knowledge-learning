# 🧪 Symfony Application - Installation Guide

## ✅ Prérequis

| Logiciel        | Version minimale   |
|-----------------|--------------------|
| Symfony         | 7                  |
| Composer        | ✅                |
| Node.js & npm   | ✅                |
| MongoDB         | ✅                | 
|                 |                    | 
| ou WampServer   |                    | 
| PHP             | 8.4.8              |
| MySQL           | 8.0.42             |
| Apache          | 2.4.62.1           |              

---

## ⚙️ Installation

### 1. Cloner ou copier ce répertoire

```bash
git clone <repo-url> Symfony_application
cd Symfony_application2
```

### 2. Installer Symfony CLI

#### Sous **Windows** :
```powershell
scoop install symfony-cli
```

#### Sous **Linux/macOS** :
```bash
curl -sS https://get.symfony.com/cli/installer | bash
```

### 3. Installation du projet Symfony

```bash
composer install
```

---

## 📦 Installation des dépendances supplémentaires

```bash
composer require symfonycasts/verify-email-bundle
composer require --dev doctrine/doctrine-fixtures-bundle
composer require stripe/stripe-php
composer require symfony/dotenv --dev
composer require doctrine/doctrine-migrations-bundle
composer require doctrine/mongodb-odm-bundle
composer require --dev phpunit/phpunit
composer require --dev symfony/test-pack
composer require symfony/security-bundle

npm install bootstrap jquery @popperjs/core --save-dev
```

---

## 🛠️ Configuration de l’environnement

Modifier le fichier `.env` et mettre vos paramètres (vous pouvez décider de laisser vide l'URL SQL ou MongoDB selon la bdd que vous choisissez) :

```

APP_ENV=test #dev (test pour lancer les test et dev pour lancer l'appli en développement .Mettre le .dist en environnement de test sur la doctrine non utilisée, par exemple si USE_MONGODB=false et que vous êtes avec orm mettre le .dist sur la doctrine_mongodb.yaml qui devient  doctrine_mongodb.yaml.dist)

APP_SECRET=your_secret
APP_DEBUG=true

DATA_SOURCE=orm #mongodb #both (orm si la base de donnée est avec SQL MongoDB si la base de donnée est avec MongoDB. Changer le provider et le password_hasers dans security.yaml selon orm ou mongo)

LIMIT_PAGINATION_5=8

DATABASE_URL="mysql://root:<password>@127.0.0.1:3306/<db_name>?serverVersion=8.0.42&charset=utf8mb4"

MAILER_DSN=smtp://your_email@gmail.com:<your_email_password>@smtp.gmail.com:587?encryption=tls&auth_mode=login

MESSENGER_TRANSPORT_DSN=sync://

STRIPE_SECRET_KEY=sk_test_xxxx
STRIPE_PUBLIC_KEY=pk_test_xxxx

USE_MONGODB=false #si vous utilisez mongodb-> #true  

MONGODB_URL=mongodb+srv://<username>:<password>@<cluster0>.mongodb.net/
MONGODB_DB=<db_name>

```
---

## 🧑‍💻 Créer la base de données

```bash
php bin/console cache:clear
php bin/console doctrine:database:create
php bin/console doctrine:schema:validate
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
-

```
## 🧑‍💻 Créer la base de données mongo

```bash
php bin/console cache:clear
php bin/console doctrine:mongodb:schema:create
php bin/console doctrine:mongodb:mapping:info
php bin/console doctrine:mongodb:schema:update
php bin/console doctrine:mongodb:fixtures:load
```

---

## 🔐 Admin

Modifier le champ `roles` d'un utilisateur dans votre base de données :

```json
["ROLE_ADMIN"]
```

Utilisez Adminer, PhpMyAdmin ou DBeaver pour modifier directement dans la table `user`.
---

## 🚀 Exécuter les test

Modifier le fichier `.env.test` et mettre vos paramètres, si vous passez par mongo :

USE_MONGODB=true #false

MONGODB_URL=mongodb+srv://<username>:<password>@<cluster0>.mongodb.net/
MONGODB_DB=<db_name>

```bash
Test Orm :


php bin/console test:reset
php bin/phpunit --testsuite orm

Test Mongo :

php bin/console test:reset-mongo
php bin/phpunit --testsuite mongodb

```
---
## 🚀 Lancement du serveur

```bash
symfony server:start
```

#### Sous **Windows** :
```powershell
start.bat
```

#### Sous **Linux/macOS** :
```bash
chmod +x start.sh
```

## 🎉 Le projet est prêt !
Accédez à l'application via [http://localhost:8000](http://localhost:8000)

Fausse carte stripe pour essayer le système de paiement stripe : 

E-mail : anonymikuw@outlook.fr
Numéro : 4242 4242 4242 4242
Date :  10/29
CVC : 123
Titulaire : John Doe



