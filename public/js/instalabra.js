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
document.addEventListener('DOMContentLoaded', function () {
    const bars = document.querySelectorAll('.vote-bar');

    bars.forEach(bar => {
        const likes = parseInt(bar.dataset.likes);
        const maxLikes = parseInt(bar.dataset.max) || 1;
        const fill = bar.querySelector('.fill');

        const porcentaje = likes > 0 ? (likes / maxLikes) * 100 : 0;

        setTimeout(function () {
            fill.style.width = porcentaje + '%';
        }, 100);
    });
});



// Function to update rankings
function updateRankings() {
    console.log('Updating rankings...');
    fetch('/api/trending', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(data => {
            const renderList = (id, items) => {
                const list = document.getElementById(id);
                if (!list) return;
                list.innerHTML = '';
                if (items.length === 0) {
                    list.innerHTML = '<p>No hay tendencias.</p>';
                    return;
                }

                items.forEach(item => {
                    const li = document.createElement('li');

                    // Logic to build LI
                    // Check max likes for bar
                    const percentage = item.likes > 0 ? (item.likes / item.max) * 100 : 0;

                    li.innerHTML = `
                        <span class="word-name">
                            <a href="/palabra/${item.id}">${item.palabra}</a>
                        </span>
                        <div class="vote-bar" data-likes="${item.likes}" data-max="${item.max}">
                            <div class="fill" style="width: ${percentage}%"></div>
                        </div>
                        <span class="vote-number">${item.likes}</span>
                    `;
                    list.appendChild(li);
                });
            };

            if (data.daily) renderList('ranking-daily', data.daily);
            if (data.monthly) renderList('ranking-monthly', data.monthly);
        })
        .catch(err => console.error('Error updating rankings:', err));
}

document.addEventListener("DOMContentLoaded", () => {

    // ================== AJAX LIKES ==================
    const likeForms = document.querySelectorAll('.ajax-like-form');
    likeForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const url = this.action;
            const btn = this.querySelector('button');
            const img = btn.querySelector('img');
            // Assuming count is sibling of form parent in .action div, or check logic
            // In index.html.twig:
            // <div class="action"> <form class="ajax-like-form">...</form> <span class="count">...</span> </div>
            const countSpan = this.parentElement.querySelector('.count');

            // Visual feedback immediately
            if (img) img.style.transform = 'scale(0.8)';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.liked !== undefined) {
                        // Update count
                        if (countSpan) countSpan.textContent = data.count;

                        // Update UI
                        if (img) {
                            img.style.transform = 'scale(1.2)';
                            setTimeout(() => img.style.transform = 'scale(1)', 200);
                            // Optional: Add class if liked for CSS styling (filter)
                            if (data.liked) {
                                btn.classList.add('liked'); // Can add CSS for this later
                            } else {
                                btn.classList.remove('liked');
                            }
                        } else {
                            // Text button (Profile)
                            btn.innerHTML = data.liked ? '💖 Ya te gusta' : '👍 Like';
                        }

                        // Trigger ranking update
                        updateRankings();
                    }
                })
                .catch(err => {
                    console.error('Error fetching like:', err);
                    // Fallback?
                });
        });
    });

    // ================== AJAX FOLLOW ==================
    const followLinks = document.querySelectorAll('.ajax-follow-link');
    followLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const btn = this.querySelector('button');
            if (btn) {
                btn.style.opacity = '0.7';
                btn.textContent = '...';
            }

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.following !== undefined) {
                        if (btn) {
                            btn.style.opacity = '1';
                            if (data.following) {
                                btn.textContent = 'Dejar de seguir';
                                // Maybe change style?
                            } else {
                                btn.textContent = 'Seguir';
                            }
                        }

                        // Update follower count if on profile
                        const followersDisplay = document.querySelector('.followers-count');
                        // Note: I didn't add class .followers-count to the profile yet, but could be useful.
                        if (data.followersCount !== undefined && followersDisplay) {
                            followersDisplay.textContent = data.followersCount;
                        }
                    }
                })
                .catch(err => {
                    console.error('Error follow:', err);
                    if (btn) btn.style.opacity = '1';
                });
        });
    });
});