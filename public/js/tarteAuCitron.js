tarteaucitron.init({
    "privacyUrl": "/politique-confidentialite", // Url de la politique de confidentialité
    "bodyPosition": "top", // top place le bandeau de consentement au début du code html, mieux pour l'accessibilité

    "hashtag": "#tarteaucitron", // Hashtag qui permet d'ouvrir le panneau de contrôle
    "cookieName": "tarteaucitron", // Nom du cookie (uniquement lettres et chiffres)

    "orientation": "top", // Position de la bannière (top - bottom - popup - banner)

    "groupServices": true, // Grouper les services par catégorie
    "showDetailsOnClick": true, // Cliquer pour ouvrir la description
    "serviceDefaultState": "wait", // Statut par défaut (true - wait - false)

    "showAlertSmall": false, // Afficher la petite bannière en bas à droite
    "cookieslist": false, // Afficher la liste des cookies via une mini bannière
    "cookieslistEmbed": false, // Afficher la liste des cookies dans le panneau de contrôle

    "closePopup": true, // Afficher un X pour fermer la bannière

    "showIcon": true, // Afficher un cookie pour ouvrir le panneau
    // "iconSrc": "", // Optionnel: URL ou image en base64
    "iconPosition": "BottomRight", // Position de l'icons: (BottomRight - BottomLeft - TopRight - TopLeft)

    "adblocker": false, // Afficher un message si un Adblocker est détecté

    "DenyAllCta": true, // Afficher le bouton Tout refuser
    "AcceptAllCta": true, // Afficher le bouton Tout accepter
    "highPrivacy": true, // Attendre le consentement
    "alwaysNeedConsent": false, // Demander le consentement même pour les services "Privacy by design"

    "handleBrowserDNTRequest": false, // Refuser tout par défaut si Do Not Track est activé sur le navigateur

    "removeCredit": false, // Retirer le lien de crédit vers tarteaucitron.io
    "moreInfoLink": true, // Afficher le lien En savoir plus

    "useExternalCss": false, // Mode expert : désactiver le chargement des fichiers .css tarteaucitron
    "useExternalJs": false, // Mode expert : désactiver le chargement des fichiers .js tarteaucitron

    // "cookieDomain": ".my-multisite-domaine.fr", // Optionnel: domaine principal pour partager le consentement avec des sous domaines

    "readmoreLink": "", // Changer le lien En savoir plus par défaut

    "mandatory": true, // Afficher un message pour l'utilisation de cookies obligatoires
    "mandatoryCta": false, // Afficher un bouton pour les cookies obligatoires (déconseillé)

    // "customCloserId": "", // Optionnel a11y: ID personnalisé pour ouvrir le panel

    "googleConsentMode": false, // Activer le Google Consent Mode v2 pour Google ads & GA4
    "bingConsentMode": false, // Activer le Bing Consent Mode pour Clarity & Bing Ads
    "softConsentMode": false, // Soft consent mode (le consentement est requis pour charger les tags)

    "dataLayer": false, // Envoyer un événement dans dataLayer avec le statut des services
    "serverSide": false, // Server side seulement, les tags ne sont pas chargé côté client

    "partnersList": false // Afficher le détail du nombre de partenaires sur la bandeau
});
