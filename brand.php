<style>
    /* Importamos una fuente más "Fintech" y limpia (Inter) junto a la tecnológica (Orbitron) */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Orbitron:wght@700;900&display=swap');

    .nexus-brand-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 30px 15px;
        background: rgba(255, 255, 255, 0.03); /* Fondo sutil de cristal */
        border-radius: 12px;
        margin-bottom: 25px;
        border: 1px solid rgba(255, 255, 255, 0.05); /* Borde sutil estilo Nixo */
        user-select: none;
    }

    .nexus-title {
        font-family: 'Orbitron', sans-serif;
        font-weight: 900;
        font-size: 2.4rem;
        letter-spacing: 6px;
        margin: 0;
        /* Degradado metálico profesional */
        background: linear-gradient(180deg, #FFFFFF 10%, #C0C0C0 50%, #00f2ff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        /* Sombra sutil pero profunda */
        filter: drop-shadow(0 0 10px rgba(0, 242, 255, 0.6));
        text-transform: uppercase;
    }

    .nexus-divider {
        height: 1px;
        width: 60px;
        background: linear-gradient(90deg, transparent, #00f2ff, transparent);
        margin: 10px auto;
        opacity: 0.6;
    }

    .nexus-tagline {
        font-family: 'Inter', sans-serif;
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.5);
        letter-spacing: 2px;
        font-weight: 600;
        text-transform: uppercase;
    }
</style>

<div class="nexus-brand-container">
    <h2 class="nexus-title">NEXUS</h2>
    <div class="nexus-divider"></div>
    <span class="nexus-tagline">PLATAFORMA DE INVERSIÓN</span>
</div>
