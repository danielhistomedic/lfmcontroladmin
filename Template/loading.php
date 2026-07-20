<?php
/**
 * Plantilla reutilizable de Loading Overlay con diseño premium y animaciones.
 */
?>
<!-- Global Page Loader -->
<div id="global-page-loader" class="global-loader-overlay">
    <div class="global-loader-container">
        <img src="<?= assets(); ?>/img/logo.png" alt="Logo" class="global-loader-logo">
        <div class="global-loader-spinner"></div>
    </div>
</div>

<style>
.global-loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 9999999;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 1;
    visibility: visible;
    transition: opacity 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), visibility 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* Compatibilidad con modo oscuro */
html.dark .global-loader-overlay {
    background-color: rgba(29, 33, 39, 0.95);
}

.global-loader-overlay.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.global-loader-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    animation: globalLoaderFadeIn 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.global-loader-logo {
    width: 160px;
    height: auto;
    max-width: 80%;
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.08));
    animation: globalLoaderPulse 2s infinite ease-in-out;
}

.global-loader-spinner {
    width: 48px;
    height: 48px;
    border: 3px solid rgba(0, 128, 159, 0.1);
    border-radius: 50%;
    border-top-color: #00809F;
    animation: globalLoaderSpin 0.9s cubic-bezier(0.5, 0.15, 0.5, 0.85) infinite;
    box-shadow: 0 4px 12px rgba(0, 128, 159, 0.05);
}

@keyframes globalLoaderSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes globalLoaderPulse {
    0%, 100% {
        transform: scale(0.97);
        opacity: 0.85;
    }
    50% {
        transform: scale(1.03);
        opacity: 1;
    }
}

@keyframes globalLoaderFadeIn {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
(function() {
    const loader = document.getElementById('global-page-loader');
    if (!loader) return;

    function hideLoader() {
        if (!loader.classList.contains('hidden')) {
            loader.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // Ocultar loader cuando la página cargue por completo
    if (document.readyState === 'complete') {
        hideLoader();
    } else {
        window.addEventListener('load', hideLoader);
    }

    // Fallback: ocultar el loader después de 4 segundos para evitar bloqueos
    setTimeout(hideLoader, 4000);
})();
</script>
