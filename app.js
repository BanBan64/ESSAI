// Application PSE 2024 - Fiches de secourisme
// Gestion de la navigation, recherche et affichage des fiches

// ==========================================
// État de l'application
// ==========================================
let filtreCategorie = null;
let rechercheActive = '';

// ==========================================
// Initialisation
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    initialiserApp();
});

function initialiserApp() {
    afficherCompteurCategories();
    afficherFiches(fichesData);
    initialiserRecherche();
    initialiserNavigationCategories();
    initialiserClavier();
}

// ==========================================
// Affichage des fiches
// ==========================================
function afficherFiches(fiches) {
    const container = document.getElementById('fichesGrid');
    const noResults = document.getElementById('noResults');

    if (fiches.length === 0) {
        container.style.display = 'none';
        noResults.style.display = 'block';
        return;
    }

    container.style.display = 'grid';
    noResults.style.display = 'none';
    container.innerHTML = '';

    fiches.forEach(fiche => {
        const card = creerCarteFiche(fiche);
        container.appendChild(card);
    });

    // Mettre à jour le titre de section
    const sectionTitle = document.getElementById('sectionTitle');
    if (filtreCategorie) {
        const cat = categories[filtreCategorie];
        sectionTitle.textContent = `${cat.icon} ${cat.nom}`;
    } else if (rechercheActive) {
        sectionTitle.textContent = `🔍 Résultats de recherche (${fiches.length})`;
    } else {
        sectionTitle.textContent = `Toutes les fiches (${fiches.length})`;
    }
}

function creerCarteFiche(fiche) {
    const card = document.createElement('div');
    card.className = `fiche-card ${fiche.categorie}`;
    card.setAttribute('tabindex', '0');
    card.setAttribute('role', 'button');
    card.setAttribute('aria-label', `Ouvrir la fiche ${fiche.titre}`);

    // Header
    const header = document.createElement('div');
    header.className = 'fiche-header';

    const icon = document.createElement('div');
    icon.className = 'fiche-icon';
    icon.textContent = fiche.icon;

    const titleContainer = document.createElement('div');
    titleContainer.className = 'fiche-title';

    const title = document.createElement('h3');
    title.textContent = fiche.titre;

    const category = document.createElement('div');
    category.className = 'fiche-category';
    category.textContent = categories[fiche.categorie].nom;

    titleContainer.appendChild(title);
    titleContainer.appendChild(category);
    header.appendChild(icon);
    header.appendChild(titleContainer);

    // Description
    const description = document.createElement('p');
    description.className = 'fiche-description';
    description.textContent = fiche.description;

    // Tags
    const tagsContainer = document.createElement('div');
    tagsContainer.className = 'fiche-tags';
    fiche.tags.forEach(tag => {
        const tagEl = document.createElement('span');
        tagEl.className = 'fiche-tag';
        tagEl.textContent = tag;
        tagsContainer.appendChild(tagEl);
    });

    card.appendChild(header);
    card.appendChild(description);
    card.appendChild(tagsContainer);

    // Événements
    card.addEventListener('click', () => ouvrirFiche(fiche));
    card.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            ouvrirFiche(fiche);
        }
    });

    return card;
}

// ==========================================
// Modal - Affichage détaillé d'une fiche
// ==========================================
function ouvrirFiche(fiche) {
    const modal = document.getElementById('ficheModal');
    const content = document.getElementById('ficheContent');

    content.innerHTML = genererContenuFiche(fiche);
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Focus sur le bouton de fermeture
    setTimeout(() => {
        modal.querySelector('.modal-close').focus();
    }, 100);
}

function closeModal() {
    const modal = document.getElementById('ficheModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

function genererContenuFiche(fiche) {
    let html = `
        <div class="fiche-detail-header">
            <div style="font-size: 3rem; margin-bottom: 1rem;">${fiche.icon}</div>
            <h2 class="fiche-detail-title">${fiche.titre}</h2>
            <div class="fiche-detail-category">${categories[fiche.categorie].nom}</div>
        </div>
    `;

    const contenu = fiche.contenu;

    // Indication
    if (contenu.indication) {
        html += `
            <div class="fiche-detail-section">
                <h3>📋 Indication</h3>
                <p>${contenu.indication}</p>
            </div>
        `;
    }

    // Signes
    if (contenu.signes) {
        html += `
            <div class="fiche-detail-section">
                <h3>🔍 Signes</h3>
                <ul>
                    ${contenu.signes.map(signe => `<li>${signe}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    // Signes de gravité
    if (contenu.signes_gravite) {
        html += `
            <div class="fiche-detail-section">
                <h3>⚠️ Signes de gravité</h3>
                <ul>
                    ${contenu.signes_gravite.map(signe => `<li>${signe}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    // Test rapide (pour AVC)
    if (contenu.test_rapide) {
        html += `
            <div class="fiche-detail-alert">
                <h3>${contenu.test_rapide.titre}</h3>
                <ul>
                    ${contenu.test_rapide.etapes.map(etape => `<li><strong>${etape}</strong></li>`).join('')}
                </ul>
            </div>
        `;
    }

    // Procédure
    if (contenu.procedure) {
        html += `<div class="fiche-detail-section"><h3>⚕️ Conduite à tenir</h3>`;

        contenu.procedure.forEach((section, index) => {
            html += `
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: var(--color-urgence); margin-bottom: 0.5rem;">
                        ${index + 1}. ${section.titre}
                    </h4>
                    <ul>
                        ${section.etapes.map(etape => `<li>${etape}</li>`).join('')}
                    </ul>
                </div>
            `;
        });

        html += `</div>`;
    }

    // Alertes
    if (contenu.alertes) {
        contenu.alertes.forEach(alerte => {
            const classe = `fiche-detail-${alerte.type}`;
            const icone = alerte.type === 'danger' ? '⛔' :
                         alerte.type === 'info' ? 'ℹ️' : '⚠️';
            html += `
                <div class="${classe}">
                    <strong>${icone} ${alerte.type === 'danger' ? 'ATTENTION' : 'INFO'} :</strong>
                    ${alerte.texte}
                </div>
            `;
        });
    }

    // Arrêt
    if (contenu.arret) {
        html += `
            <div class="fiche-detail-info">
                <strong>🛑 Arrêt de la procédure :</strong>
                ${contenu.arret}
            </div>
        `;
    }

    return html;
}

// Fermer la modal avec Echap
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('ficheModal');
        if (modal.classList.contains('active')) {
            closeModal();
        }
    }
});

// Fermer la modal en cliquant en dehors
document.getElementById('ficheModal').addEventListener('click', (e) => {
    if (e.target.id === 'ficheModal') {
        closeModal();
    }
});

// ==========================================
// Recherche
// ==========================================
function initialiserRecherche() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        rechercheActive = query;

        if (query.length === 0) {
            searchResults.classList.remove('active');
            filtreCategorie = null;
            afficherFiches(fichesData);
            document.getElementById('resetFilter').style.display = 'none';
            return;
        }

        if (query.length < 2) {
            searchResults.classList.remove('active');
            return;
        }

        // Recherche dans les fiches
        const resultats = fichesData.filter(fiche => {
            const texteRecherche = `
                ${fiche.titre}
                ${fiche.description}
                ${fiche.tags.join(' ')}
                ${categories[fiche.categorie].nom}
            `.toLowerCase();

            return texteRecherche.includes(query);
        });

        // Afficher les suggestions
        if (resultats.length > 0 && resultats.length <= 5) {
            afficherSuggestions(resultats, searchResults);
        } else {
            searchResults.classList.remove('active');
        }

        // Afficher les résultats dans la grille
        filtreCategorie = null;
        afficherFiches(resultats);
        document.getElementById('resetFilter').style.display = 'block';
    });

    // Fermer les suggestions en cliquant ailleurs
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.remove('active');
        }
    });
}

function afficherSuggestions(resultats, container) {
    container.innerHTML = '';
    container.classList.add('active');

    resultats.forEach(fiche => {
        const item = document.createElement('div');
        item.className = 'search-result-item';
        item.innerHTML = `
            <div class="search-result-title">${fiche.icon} ${fiche.titre}</div>
            <div class="search-result-category">${categories[fiche.categorie].nom}</div>
        `;
        item.addEventListener('click', () => {
            ouvrirFiche(fiche);
            container.classList.remove('active');
        });
        container.appendChild(item);
    });
}

function resetSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('searchResults').classList.remove('active');
    rechercheActive = '';
    filtreCategorie = null;
    afficherFiches(fichesData);
    document.getElementById('resetFilter').style.display = 'none';
}

// ==========================================
// Navigation par catégories
// ==========================================
function initialiserNavigationCategories() {
    const categoryCards = document.querySelectorAll('.category-card');
    const resetBtn = document.getElementById('resetFilter');

    categoryCards.forEach(card => {
        card.addEventListener('click', () => {
            const categorie = card.getAttribute('data-category');
            filtrerParCategorie(categorie);
        });
    });

    resetBtn.addEventListener('click', () => {
        resetFiltre();
    });
}

function filtrerParCategorie(categorie) {
    filtreCategorie = categorie;
    rechercheActive = '';
    document.getElementById('searchInput').value = '';

    const fiches = fichesData.filter(f => f.categorie === categorie);
    afficherFiches(fiches);

    document.getElementById('resetFilter').style.display = 'block';

    // Scroll vers les fiches
    document.getElementById('fichesGrid').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

function resetFiltre() {
    filtreCategorie = null;
    rechercheActive = '';
    document.getElementById('searchInput').value = '';
    afficherFiches(fichesData);
    document.getElementById('resetFilter').style.display = 'none';
}

// ==========================================
// Compteurs de catégories
// ==========================================
function afficherCompteurCategories() {
    Object.keys(categories).forEach(catKey => {
        const count = fichesData.filter(f => f.categorie === catKey).length;
        const card = document.querySelector(`[data-category="${catKey}"]`);
        if (card) {
            const countEl = card.querySelector('.category-count');
            if (countEl) {
                countEl.textContent = `${count} fiche${count > 1 ? 's' : ''}`;
            }
        }
    });
}

// ==========================================
// Navigation clavier
// ==========================================
function initialiserClavier() {
    // Focus automatique sur la recherche avec "/"
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && !e.ctrlKey && !e.metaKey) {
            const searchInput = document.getElementById('searchInput');
            if (document.activeElement !== searchInput) {
                e.preventDefault();
                searchInput.focus();
            }
        }
    });
}

// ==========================================
// PWA - Mode hors ligne (optionnel)
// ==========================================
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // Service worker désactivé pour l'instant
        // Peut être activé ultérieurement pour le mode hors ligne
    });
}
