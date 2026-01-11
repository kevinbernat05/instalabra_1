const menuBar = document.querySelector('.menu-bar-left');
const toggleIcon = document.getElementById('toggle-menu');
const toggleArrow = document.getElementById('toggle-arrow');

// Click en icono de menú para esconder barra
toggleIcon.addEventListener('click', function() {
    menuBar.style.transform = 'translateX(-260px)';
    menuBar.style.opacity = '0';
    toggleArrow.style.opacity = '1'; // aparece suavemente
});

// Click en flecha para mostrar barra
toggleArrow.addEventListener('click', function () {
    menuBar.style.transform = 'translateX(0)';
    menuBar.style.opacity = '1';
    toggleArrow.style.opacity = '0'; // desaparece suavemente
});
