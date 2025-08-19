# 🧪 Symfony Application - Installation Guide

## ✅ Prérequis

| Logiciel        | Version minimale |
|----------------|------------------|
| PHP            | 8.4.8            |
| MySQL          | 8.0.42           |
| Apache         | 2.4.62.1         |
| Symfony        | 7                |
| Composer       | ✅               |
| Node.js & npm  | ✅               |
| Adminer (ou autre outil SQL) | ✅  |

---

## ⚙️ Installation

### 1. Cloner ou copier ce répertoire

```bash
git clone <repo-url> Symfony_application
cd Symfony_application
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

## 🛠️ Configuration de l’environnement

Créer et modifier le fichier avec vos données `.env` à la racine du projet (vous devez être connecté à votre bdd local) :

```
APP_ENV=dev
APP_SECRET=your_secret
APP_DEBUG=true

LIMIT_PAGINATION_5=8

DATABASE_URL="mysql://root:<password>@127.0.0.1:3306/<db_name>?serverVersion=8.0.42&charset=utf8mb4"

MAILER_DSN=smtp://your_email@gmail.com:<your_email_password>@smtp.gmail.com:587?encryption=tls&auth_mode=login

STRIPE_SECRET_KEY=sk_test_xxxx
STRIPE_PUBLIC_KEY=pk_test_xxxx
```

---

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

npm install bootstrap jquery @popperjs/core --save-dev
```

---

## 🧑‍💻 Créer la base de données

```bash
php bin/console cache:clear
php bin/console doctrine:database:create
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

---

## 🔐 Admin

Modifier le champ `roles` d'un utilisateur dans votre base de données :

```json
["ROLE_ADMIN"]
```

Utilisez Adminer, PhpMyAdmin ou DBeaver pour modifier directement dans la table `user`.

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
