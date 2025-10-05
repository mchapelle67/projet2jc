# 🚗 2JC Automobiles

Site web créé pour un garage de réparation et de vente de véhicules d'occasion.  
**Statut :** En ligne !! https://2jc-automobiles.fr

---

## 🔹 Description
2JC Automobiles est un site web professionnel permettant aux clients de consulter les services du garage, de prendre rendez-vous pour des réparations, de consulter les ventes de véhicules d’occasion, et de bénéficier d’un suivi grâce à des fonctionnalités avancées.  

---

<img width="1500" height="6154" alt="readme" src="https://github.com/user-attachments/assets/ac6cc845-6bc1-484c-ae3f-19a53d37667c" />

## ⚙️ Installation

### Prérequis
- PHP >= 8.4
- Symfony
- Composer
- MySQL
- Serveur web type Apache 

### Instructions
1. Cloner le projet :  
   git clone https://github.com/mchapelle67/projet2jc.git)
   
2. Installer les dépendances :
  composer install

3. Configurer votre fichier .env avec vos informations de base de données et paramètres locaux.

# ✨ Fonctionnalités principales

- Formulaire de devis et prise de rendez-vous
- Système gestion des demandes de devis et de rendez-vous
- Cron job pour rappeler les rendez-vous dans les 48h, supprimer les données clients de plus de 3 ans (selon RGPD) et clôturer les rendez-vous passés.
- Service d'emailing automatique pour clients et administrateurs
- Espace admin complet avec hiérarchie de rôles
- Optimisation SEO et accessibilité : utilisation de balises sémantiques selon normes W3C, création d’un sitemap XML, definition des balises meta
- Tests unitaires avec PHPUnit
  
### Sécurisation avancée des données :

- Protection contre les failles d’upload
- Limitation des tentatives de connexion (rate limiter)
- Honeypot sur les formulaires
- Protection CSRF
 
# 🗂 Structure du projet

Le projet suit le design pattern MVC (Model-View-Controller) avec Symfony :

- public/           -> fichiers publics (CSS, JS, images)
- src/              -> controllers, services, entités
- templates/        -> vues Twig
- config/           -> configuration Symfony

#  🛠 Technologies et outils

- Langages & Frameworks : PHP, Symfony, HTML, CSS, JavaScript 🌐
- Base de données : MySQL 🗄️
- Design & Prototypage : Figma 🎨, Trello 📐
- Tests & qualité : PHPUnit ✅

# 📧 Contact

Manon Chapelle - [LinkedIn](https://www.linkedin.com/in/manon-chapelle67/)

# 📝 Licence
Projet propriétaire – Tous droits réservés © 2025
