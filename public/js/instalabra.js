// ================== FUNCIONES PRINCIPALES ==================

// Script simplificado para funcionalidades de UI que no dependan de datos falsos.
// Por ejemplo, el toggle del menú lateral si es necesario.

document.addEventListener("turbo:load", () => {
  console.log("Instalabra JS loaded (Cleaned).");

  // Toggle Password Visibility
  document.querySelectorAll(".toggle-password").forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.parentElement.querySelector("input");
      const eyeOpen = this.querySelector(".eye-open");
      const eyeClosed = this.querySelector(".eye-closed");

      if (input.type === "password") {
        input.type = "text";
        eyeOpen.style.display = "none";
        eyeClosed.style.display = "block";
      } else {
        input.type = "password";
        eyeOpen.style.display = "block";
        eyeClosed.style.display = "none";
      }
    });
  });
});

//Hacemos que dependiendo del número de likes la barra ocupe una parte de color u
//otra
document.addEventListener("turbo:load", function () {
  const bars = document.querySelectorAll(".vote-bar");

  bars.forEach((bar) => {
    const likes = parseInt(bar.dataset.likes);
    const maxLikes = parseInt(bar.dataset.max) || 1;
    const fill = bar.querySelector(".fill");

    const porcentaje = likes > 0 ? (likes / maxLikes) * 100 : 0;

    setTimeout(function () {
      fill.style.width = porcentaje + "%";
    }, 100);
  });
});

// Function to update rankings
function updateRankings() {
  console.log("Updating rankings...");
  fetch("/api/trending", {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      Accept: "application/json",
    },
  })
    .then((res) => res.json())
    .then((data) => {
      const renderList = (id, items) => {
        const list = document.getElementById(id);
        if (!list) return;
        list.innerHTML = "";
        if (items.length === 0) {
          list.innerHTML = "<p>No hay tendencias.</p>";
          return;
        }

        items.forEach((item) => {
          const li = document.createElement("li");

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

      if (data.daily) renderList("ranking-daily", data.daily);
      if (data.monthly) renderList("ranking-monthly", data.monthly);
    })
    .catch((err) => console.error("Error updating rankings:", err));
}

document.addEventListener("turbo:load", () => {
  // ================== AJAX LIKES ==================
  const likeForms = document.querySelectorAll(".ajax-like-form");
  likeForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      const url = this.action;
      const btn = this.querySelector("button");
      const img = btn.querySelector("img");
      // Assuming count is sibling of form parent in .action div, or check logic
      // In index.html.twig:
      // <div class="action"> <form class="ajax-like-form">...</form> <span class="count">...</span> </div>
      const countSpan = this.parentElement.querySelector(".count");

      // Visual feedback immediately
      if (img) img.style.transform = "scale(0.8)";

      fetch(url, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.liked !== undefined) {
            // Update count
            if (countSpan) countSpan.textContent = data.count;

            // Update UI
            if (img) {
              img.style.transform = "scale(1.2)";
              setTimeout(() => (img.style.transform = "scale(1)"), 200);
              // Optional: Add class if liked for CSS styling (filter)
              if (data.liked) {
                btn.classList.add("liked"); // Can add CSS for this later
              } else {
                btn.classList.remove("liked");
              }
            } else {
              // Text button (Profile)
              btn.innerHTML = data.liked ? "Ya te gusta" : "Like";
            }

            // Trigger ranking update
            updateRankings();
          }
        })
        .catch((err) => {
          console.error("Error fetching like:", err);
          // Fallback?
        });
    });
  });

  // ================== AJAX FOLLOW ==================
  const followLinks = document.querySelectorAll(".ajax-follow-link");
  followLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const url = this.getAttribute("href");
      const btn = this.querySelector("button");
      if (btn) {
        btn.style.opacity = "0.7";
        btn.textContent = "...";
      }

      fetch(url, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.following !== undefined) {
            if (btn) {
              btn.style.opacity = "1";
              if (data.following) {
                btn.textContent = "Dejar de seguir";
                // Maybe change style?
              } else {
                btn.textContent = "Seguir";
              }
            }

            // Update follower count if on profile
            const followersDisplay = document.querySelector(".followers-count");
            // Note: I didn't add class .followers-count to the profile yet, but could be useful.
            if (data.followersCount !== undefined && followersDisplay) {
              followersDisplay.textContent = data.followersCount;
            }
          }
        })
        .catch((err) => {
          console.error("Error follow:", err);
          if (btn) btn.style.opacity = "1";
        });
    });
  });
});

// Modal para poder ver los followers y following de una persona
function openFollowers() {
  document.getElementById("followers-modal").style.display = "flex";
}

function openFollowing() {
  document.getElementById("following-modal").style.display = "flex";
}

function closeModal() {
  document.getElementById("followers-modal").style.display = "none";
  document.getElementById("following-modal").style.display = "none";
}

// ================== AJAX SEARCH ==================
document.addEventListener("turbo:load", () => {
  const searchInput = document.querySelector('.search-form input[name="q"]');
  const searchFilters = document.getElementById("search-filters");
  const resultsContainer = document.getElementById("search-results-container");

  // Only run if we are on the search page or the search input exists
  if (!searchInput) return;

  let searchTimeout = null;
  let currentQuery = searchInput.value || "";

  // Set focus to the end of the text if we just arrived at the search page
  if (window.location.pathname.includes("/buscar")) {
    searchInput.focus();
    if (currentQuery !== "") {
        searchInput.value = "";
        searchInput.value = currentQuery;
    }
  }

  // Determine initial filter based on active button, default to 'usuarios'
  let currentFilter = "usuarios";
  if (searchFilters) {
    const activeBtn = searchFilters.querySelector(".active");
    if (activeBtn) currentFilter = activeBtn.dataset.filter;
  }

    function performSearch(query, filter) {
        // If on search page and query is empty, allow clearing results or show all (depends on backend)
        if (!window.location.pathname.includes('/buscar')) {
            return;
        }

        fetch(`/buscar?q=${encodeURIComponent(query)}&filter=${filter}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!resultsContainer) {
                return;
            }
            
            let html = `<h3>${filter.charAt(0).toUpperCase() + filter.slice(1)}</h3>`;
            html += filter === 'definiciones' ? '<ul class="results-list">' : '<ul class="results-list">';

            if (data.results.length === 0) {
                if (query.trim() !== '') {
                    html += `<li>No se encontraron ${filter}</li>`;
                }
                html += '</ul>';
            } else {
                data.results.forEach(item => {
                    if (filter === 'usuarios') {
                        html += `<li><a style="text-decoration:none; color:inherit;" href="/usuario/${item.id}">${item.nombre}</a></li>`;
                    } else if (filter === 'palabras') {
                        html += `<li><a style="text-decoration:none; color:inherit;" href="/palabra/${item.id}">${item.palabra}</a></li>`;
                    } else if (filter === 'definiciones') {
                        html += `<li>${item.definicion} <br><small>(${item.palabra})</small></li>`;
                    }
                });
                html += '</ul>';
            }

            resultsContainer.innerHTML = html;
            
            // Update URL without reloading
            const newUrl = `/buscar?q=${encodeURIComponent(query)}&filter=${filter}`;
            window.history.replaceState({}, '', newUrl);
        })
        .catch(err => console.error("Error en búsqueda AJAX:", err));
    }

    // Redirect when focusing on the search bar if not on the search page
    searchInput.addEventListener('focus', () => {
        if (!window.location.pathname.includes('/buscar')) {
            window.location.href = `/buscar?q=${encodeURIComponent(searchInput.value)}&filter=${currentFilter}`;
        }
    });

    // Input typing event (debounced)
    searchInput.addEventListener('input', (e) => {
        currentQuery = e.target.value;
        
        // Only perform AJAX search instantly if we are ALREADY on the results page.
        // Otherwise, we let the user type and press Enter to submit the form normally.
        if (window.location.pathname.includes('/buscar')) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(currentQuery, currentFilter);
            }, 300); // 300ms debounce
        }
    });

    // Submitting form prevents default if on search page
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            if (window.location.pathname.includes('/buscar')) {
                e.preventDefault(); // Handled by JS input event if already there
                performSearch(currentQuery, currentFilter);
            }
        });
    }

  // Filter clicks
  if (searchFilters) {
    searchFilters.addEventListener("click", (e) => {
      if (
        e.target.tagName === "BUTTON" ||
        e.target.classList.contains("filter-btn")
      ) {
        e.preventDefault();
        // Remove active class from all
        searchFilters
          .querySelectorAll("button")
          .forEach((b) => b.classList.remove("active"));
        // Add active to clicked
        e.target.classList.add("active");

        currentFilter = e.target.dataset.filter;
        performSearch(currentQuery, currentFilter);
      }
    });
  }
});
