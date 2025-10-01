document.addEventListener('DOMContentLoaded', function() {
    let scrollHandler = null;
    const parallax = document.querySelector(".parallaxe");
    
    if (!parallax) return; // Sortir si l'élément n'existe pas

    function initParallax() {
        // Nettoie l'ancien listener s'il existe
        if (scrollHandler) {
            window.removeEventListener("scroll", scrollHandler);
            scrollHandler = null;
        }

        // Vérifie la largeur de l'écran (pas d'effet sous 1024px)
        if (window.innerWidth > 1024) {
            scrollHandler = () => {
                let offset = window.scrollY * 0.4; // vitesse de défilement
                parallax.style.backgroundPosition = `center ${offset}px`;
            };
            
            window.addEventListener("scroll", scrollHandler, { passive: true });
        }
    }

    // Lance la fonction au chargement
    initParallax();
    
    // Relance si on redimensionne (utile si on passe mobile ↔ desktop)
    window.addEventListener("resize", initParallax);
});