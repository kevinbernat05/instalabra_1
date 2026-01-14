const menuBar = document.querySelector('.menu-bar-left');
const toggleIcon = document.getElementById('toggle-menu');
const toggleArrow = document.getElementById('toggle-arrow');
const btnPublish = document.querySelector(".publicar");
const overlay = document.getElementById("blur");
const windoww = document.getElementById("window");
const closeWindow = document.getElementById("close-window");

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

// Menú de publicar al darle click al boton
btnPublish.addEventListener("click", function(){
    windoww.style.display = "block";
    overlay.style.display = "block";
});

closeWindow.addEventListener("click", function(){
    windoww.style.display = "none";
    overlay.style.display = "none";
});

overlay.addEventListener("click", function(){
    windoww.style.display = "none";
    overlay.style.display = "none"
});