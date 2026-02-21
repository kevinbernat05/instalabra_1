document.addEventListener('turbo:load', function() {
    const menuBar = document.querySelector('.menu-bar-left');
    const toggleIcon = document.getElementById('toggle-menu');
    const toggleArrow = document.getElementById('toggle-arrow');
    const btnPublish = document.querySelector(".publicar");
    const overlay = document.getElementById("blur");
    const windoww = document.getElementById("window");
    const closeWindow = document.getElementById("close-window");

    // Click en icono de menú para esconder barra
    if (toggleIcon) {
        toggleIcon.addEventListener('click', function() {
            if (menuBar) {
                menuBar.style.transform = 'translateX(-260px)';
                menuBar.style.opacity = '0';
            }
            if (toggleArrow) toggleArrow.style.opacity = '1'; // aparece suavemente
        });
    }

    // Click en flecha para mostrar barra
    if (toggleArrow) {
        toggleArrow.addEventListener('click', function () {
            if (menuBar) {
                menuBar.style.transform = 'translateX(0)';
                menuBar.style.opacity = '1';
            }
            if (toggleArrow) toggleArrow.style.opacity = '0'; // desaparece suavemente
        });
    }

    // Menú de publicar al darle click al boton
    if (btnPublish) {
        btnPublish.addEventListener("click", function(){
            if (windoww) windoww.style.display = "block";
            if (overlay) overlay.style.display = "block";
        });
    }

    if (closeWindow) {
        closeWindow.addEventListener("click", function(){
            if (windoww) windoww.style.display = "none";
            if (overlay) overlay.style.display = "none";
        });
    }

    if (overlay) {
        overlay.addEventListener("click", function(){
            if (windoww) windoww.style.display = "none";
            if (overlay) overlay.style.display = "none";
        });
    }
});