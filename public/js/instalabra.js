// ================== FUNCIONES PRINCIPALES ==================

// Script simplificado para funcionalidades de UI que no dependan de datos falsos.
// Por ejemplo, el toggle del menú lateral si es necesario.

document.addEventListener("DOMContentLoaded", () => {
    console.log("Instalabra JS loaded (Cleaned).");
    // Mantener solo logica de UI si existe en menu.js u otros, 
    // pero borrarmos la inyección de datos falsos.
});


//Hacemos que dependiendo del número de likes la barra ocupe una parte de color u
//otra
document.addEventListener('DOMContentLoaded', function(){
    const bars = document.querySelectorAll('.vote-bar');

    bars.forEach(bar => {
        const likes = parseInt(bar.dataset.likes);
        const maxLikes = parseInt(bar.dataset.max) || 1;
        const fill = bar.querySelector('.fill');

        const porcentaje = likes > 0 ? (likes/maxLikes) * 100 : 0;

        setTimeout(function(){
            fill.style.width = porcentaje + '%';}, 100);
    });
});