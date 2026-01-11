//Usuarios ficticios para realizar pruebas
const usuarios = [
    {
        nombre: "Ana",
        foto: "multimedia/Prueba/icon1.jpg",
        post: "Me encanta esta red social, es muy divertida y fácil de usar."},
        {
        nombre: "Luis",
        foto: "",
        post: "Acabo de subir una foto de mi viaje, ¡miren qué hermosa vista!"
    }];

//Seguidores sugeridos 
const sugeridos = [
    {
        usuario: "@maria123",
        foto: ""
    },
    {
        usuario: "@carlos_smith",
        foto: ""
    },
    {
        usuario: "@laura89",
        foto: "multimedia/Prueba/icon1.jpg"
    }

];

//Ranking diario de palabras
const rankingDiario = [
     { palabra: "Amor", votos: 120 },
    { palabra: "Libertad", votos: 95 },
    { palabra: "Calma", votos: 80 },
    { palabra: "Esperanza", votos: 60 },
    { palabra: "Luz", votos: 45 }
];

// Comentarios
const comentarios = {
    "Ana": [
        {usuario: "Luis", foto: "", mensaje: "¡Qué genial publicación!"},
        {usuario: "Maria", foto: "", mensaje: "Me encanta"},
        {usuario: "Carlos", foto: "", mensaje: "Totalmente de acuerdo contigo"}
    ]};

//Función para cargar los usuarios ficticios en la página
function cargarUsuarios() {
    const contenedor = document.querySelector(".feed");
    usuarios.forEach(function(u) {
        // Foto por defecto si no tiene
        const imgSrc = u.foto || "multimedia/Prueba/noicon.jpg";

        // Creamos el post entero
        const post = document.createElement("div");
        post.className = "single_post";
        post.innerHTML = `
            <div class="post_header">
                <div class="user_info">
                    <img src="${imgSrc}" alt="Foto de ${u.nombre}">
                    <span>${u.nombre}</span>
                </div>
            </div>
            <p>${u.post}</p>
        `;

        // Añadimos el post al contenedor
        contenedor.appendChild(post);
    });
}

//Función para cargar los seguidores sugeridos
function cargarSugeridos(){
    const contenedor = document.getElementById("user-suggestions");

    sugeridos.forEach(u => {
        const div = document.createElement("div");
        div.className = "user-suggestions";

        const imgSrc = u.foto || "multimedia/Prueba/noicon.jpg";

        div.innerHTML = `
            <img src="${imgSrc}" alt="${u.usuario}">
            <span>${u.usuario}</span>
            <button>Seguir</button>
        `;

        contenedor.appendChild(div);
    });
}

//Función para cargar el ranking diario
function cargarRanking() {
    const lista = document.getElementById("word-list");
    const votosMax = Math.max(...rankingDiario.map(r => r.votos));

    rankingDiario.forEach(item => {
        const li = document.createElement("li");

        const wordSpan = document.createElement("span");
        wordSpan.className = "word-name";
        wordSpan.textContent = item.palabra;

        const voteDiv = document.createElement("div");
        voteDiv.className = "vote-bar";

        // Relleno sólido proporcional
        const fillDiv = document.createElement("div");
        fillDiv.className = "fill";
        const widthPercent = (item.votos / votosMax) * 100;
        fillDiv.style.width = widthPercent + "%";

        // Número de votos encima del fondo opaco
        const numberDiv = document.createElement("div");
        numberDiv.className = "vote-number";
        numberDiv.textContent = item.votos;

        voteDiv.appendChild(fillDiv);
        voteDiv.appendChild(numberDiv);

        li.appendChild(wordSpan);
        li.appendChild(voteDiv);

        lista.appendChild(li);
    });
}

//Funcion para cargar los comentarios de un post
function cargarUsuarios() {
    const contenedor = document.querySelector(".feed");
    usuarios.forEach(function(u) {
        const imgSrc = u.foto || "multimedia/Prueba/noicon.jpg";

        // Creamos el post
        const post = document.createElement("div");
        post.className = "single_post";
        post.innerHTML = `
            <div class="post_header">
                <div class="user_info">
                    <img src="${imgSrc}" alt="Foto de ${u.nombre}">
                    <span>${u.nombre}</span>
                </div>
            </div>
            <p>${u.post}</p>
            <div class="comments" id="comments-${u.nombre}"></div>
        `;

        contenedor.appendChild(post);

        // Añadir comentarios
        const contenedorComentarios = document.getElementById(`comments-${u.nombre}`);
        const comentariosPost = comentarios[u.nombre] || [];

        if (comentariosPost.length === 0) {
            contenedorComentarios.innerHTML = `<span style="color:#999; font-style:italic;">Aún no hay comentarios</span>`;
        } else {
            comentariosPost.slice(0, 3).forEach(c => {
                const foto = c.foto || "multimedia/Prueba/noicon.jpg";
                const div = document.createElement("div");
                div.className = "comment";
                div.innerHTML = `
                    <img src="${foto}" alt="${c.usuario}">
                    <div class="comment-text">
                        <span class="username">${c.usuario}</span>
                        <span class="message">${c.mensaje}</span>
                    </div>
                `;
                contenedorComentarios.appendChild(div);
            });
        }
    });
}


// Llamamos a las fundciones al cargar la página
document.addEventListener("DOMContentLoaded", cargarUsuarios);
document.addEventListener("DOMContentLoaded", cargarSugeridos);
document.addEventListener("DOMContentLoaded", cargarRanking);