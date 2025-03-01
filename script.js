document.addEventListener("DOMContentLoaded", function() {
    // Handle input animations
    document.querySelectorAll("input").forEach(input => {
        // Check if input has value on page load
        if (input.value !== "") {
            input.classList.add("has-value");
        }
        
        // Add events for input interactions
        input.addEventListener("focus", function() {
            this.parentElement.classList.add("focused");
        });
        
        input.addEventListener("blur", function() {
            this.parentElement.classList.remove("focused");
            if (this.value !== "") {
                this.classList.add("has-value");
            } else {
                this.classList.remove("has-value");
            }
        });
    });

    // Add subtle animation to the form
    const form = document.querySelector("form");
    if (form) {
        form.addEventListener("submit", function(e) {
            // Client-side validation
            const inputs = this.querySelectorAll('input[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    showInputError(input, 'This field is required');
                } else if (input.type === 'email' && !validateEmail(input.value)) {
                    isValid = false;
                    showInputError(input, 'Please enter a valid email');
                } else if (input.name === 'password' && input.value.length < 6) {
                    isValid = false;
                    showInputError(input, 'Password must be at least 6 characters');
                } else if (input.name === 'confirm_password') {
                    const passwordInput = document.querySelector('input[name="password"]');
                    if (input.value !== passwordInput.value) {
                        isValid = false;
                        showInputError(input, 'Passwords do not match');
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                return;
            }
            
            const button = this.querySelector("button[type='submit']");
            if (button) {
                button.innerHTML = '<div class="loader"></div> Processing...';
                button.style.pointerEvents = "none";
            }
        });
    }
    
    // Password visibility toggle
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
        });
    });
    
    // Add parallax effect to background
    document.addEventListener("mousemove", function(e) {
        const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
        const moveY = (e.clientY - window.innerHeight / 2) * 0.01;
        document.body.style.backgroundPosition = `calc(50% + ${moveX}px) calc(50% + ${moveY}px)`;
    });
    
    // Theme toggle functionality
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const isDarkMode = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode);
            this.innerHTML = isDarkMode ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            
            // Send theme preference to server if user is logged in
            if (document.querySelector('.dashboard-container')) {
                fetch('update_theme.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `theme=${isDarkMode ? 'dark' : 'light'}`
                });
            }
        });
        
        // Check for saved theme preference
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
    }
    
    // Dashboard tabs functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    if (tabButtons.length > 0) {
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all tabs
                tabButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Show corresponding content section
                const targetId = this.getAttribute('data-target');
                document.querySelectorAll('.content-section').forEach(section => {
                    section.classList.remove('active');
                });
                document.getElementById(targetId).classList.add('active');
                
                // Save active tab in session storage
                sessionStorage.setItem('activeTab', targetId);
            });
        });
        
        // Restore active tab from session storage if available
        const activeTab = sessionStorage.getItem('activeTab');
        if (activeTab) {
            document.querySelector(`.tab-button[data-target="${activeTab}"]`)?.click();
        } else {
            // Activate first tab by default
            tabButtons[0]?.click();
        }
    }
    
    // Anime search functionality
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const searchTerm = this.value.toLowerCase();
            const animeCards = document.querySelectorAll('.anime-card');
            
            animeCards.forEach(card => {
                const title = card.querySelector('.anime-card-title').textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }, 300));
    }
    
    // Filter tags functionality
    const filterTags = document.querySelectorAll('.filter-tag');
    if (filterTags.length > 0) {
        filterTags.forEach(tag => {
            tag.addEventListener('click', function() {
                this.classList.toggle('active');
                applyFilters();
            });
        });
    }
    
    // Auto dismiss flash messages
    const flashMessages = document.querySelectorAll('.flash-message');
    if (flashMessages.length > 0) {
        setTimeout(() => {
            flashMessages.forEach(message => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.remove();
                }, 500);
            });
        }, 5000);
    }
    
    // Load trending anime on dashboard
    const trendingSection = document.getElementById('trending-anime');
    if (trendingSection) {
        fetchTrendingAnime();
    }
    
    // Profile image upload preview
    const profileImageInput = document.getElementById('profile-image');
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-pic').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            document.querySelector('.dashboard-tabs').classList.toggle('show');
        });
    }
});

// Helper functions
function showInputError(input, message) {
    const errorElement = document.createElement('div');
    errorElement.className = 'input-error';
    errorElement.textContent = message;
    errorElement.style.color = 'var(--error)';
    errorElement.style.fontSize = '0.8rem';
    errorElement.style.marginTop = '0.25rem';
    
    // Remove any existing error message
    const existingError = input.parentElement.querySelector('.input-error');
    if (existingError) {
        existingError.remove();
    }
    
    input.parentElement.appendChild(errorElement);
    input.style.borderBottomColor = 'var(--error)';
    
    // Clear error after 3 seconds
    setTimeout(() => {
        errorElement.remove();
        input.style.borderBottomColor = '';
    }, 3000);
}

function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

async function fetchTrendingAnime() {
    const trendingGrid = document.querySelector('#trending-anime .anime-grid');
    if (!trendingGrid) return;
    
    trendingGrid.innerHTML = '<div class="loader" style="margin: 2rem auto;"></div>';
    
    try {
        const response = await fetch('fetch_trending.php');
        const data = await response.json();
        
        if (data.success && data.anime) {
            let html = '';
            data.anime.forEach(anime => {
                html += `
                <div class="anime-card">
                    <img src="${anime.image}" class="anime-card-img" alt="${anime.title}">
                    <div class="anime-card-content">
                        <h3 class="anime-card-title">${anime.title}</h3>
                        <p class="anime-card-status">Rating: ${anime.rating}/10</p>
                        <div class="anime-card-actions">
                            <button class="btn btn-sm" data-id="${anime.id}" onclick="addToList(${anime.id}, 'plan_to_watch')">
                                <i class="fas fa-plus"></i> Add
                            </button>
                            <button class="btn btn-sm btn-outline" onclick="showDetails(${anime.id})">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
                `;
            });
            
            trendingGrid.innerHTML = html;
        } else {
            trendingGrid.innerHTML = '<p>Failed to load trending anime. Please try again later.</p>';
        }
    } catch (error) {
        trendingGrid.innerHTML = '<p>An error occurred while fetching data.</p>';
        console.error('Error fetching trending anime:', error);
    }
}

function applyFilters() {
    const activeFilters = Array.from(document.querySelectorAll('.filter-tag.active'))
        .map(tag => tag.getAttribute('data-filter'));
    
    const animeCards = document.querySelectorAll('.anime-card');
    
    if (activeFilters.length === 0) {
        // Show all cards if no filters are active
        animeCards.forEach(card => card.style.display = 'block');
        return;
    }
    
    animeCards.forEach(card => {
        const status = card.getAttribute('data-status');
        if (activeFilters.includes(status)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function addToList(animeId, status) {
    fetch('update_list.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `anime_id=${animeId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Anime added to your list!', 'success');
        } else {
            showToast(data.message || 'Failed to add anime', 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred', 'error');
        console.error('Error adding anime to list:', error);
    });
}

function showDetails(animeId) {
    // Create modal for anime details
    const modal = document.createElement('div');
    modal.className = 'anime-modal';
    modal.innerHTML = `
        <div class="anime-modal-content">
            <span class="close-modal">&times;</span>
            <div class="anime-details">
                <div class="loader"></div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Add styles for modal
    const style = document.createElement('style');
    style.textContent = `
        .anime-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s;
        }
        .anime-modal-content {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }
        .dark-mode .anime-modal-content {
            background: #2D3748;
            color: #E2E8F0;
        }
        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
        }
        .anime-details {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .anime-detail-img {
            width: 200px;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .anime-detail-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }
        .dark-mode .anime-detail-title {
            color: #E2E8F0;
        }
        .anime-detail-info {
            margin-bottom: 1.5rem;
            color: var(--text-light);
        }
        .anime-synopsis {
            line-height: 1.6;
            color: var(--text-dark);
            text-align: left;
            margin-bottom: 1.5rem;
        }
        .dark-mode .anime-synopsis {
            color: #E2E8F0;
        }
        .anime-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }
    `;
    document.head.appendChild(style);
    
    // Close modal on click
    modal.querySelector('.close-modal').addEventListener('click', () => {
        modal.remove();
        style.remove();
    });
    
    // Close modal when clicking outside content
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
            style.remove();
        }
    });
    
    // Fetch anime details
    fetch(`get_anime_details.php?id=${animeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const anime = data.anime;
                const details = modal.querySelector('.anime-details');
                
                details.innerHTML = `
                    <img src="${anime.image}" class="anime-detail-img" alt="${anime.title}">
                    <h2 class="anime-detail-title">${anime.title}</h2>
                    <div class="anime-detail-info">
                        <span>Rating: ${anime.rating}/10</span> • 
                        <span>Episodes: ${anime.episodes}</span> • 
                        <span>Year: ${anime.year}</span>
                    </div>
                    <p class="anime-synopsis">${anime.synopsis}</p>
                    <div class="anime-actions">
                        <button class="btn btn-sm" onclick="addToList(${anime.id}, 'watching')">
                            <i class="fas fa-play"></i> Watching
                        </button>
                        <button class="btn btn-sm" onclick="addToList(${anime.id}, 'plan_to_watch')">
                            <i class="fas fa-list"></i> Plan to Watch
                        </button>
                        <button class="btn btn-sm" onclick="addToList(${anime.id}, 'completed')">
                            <i class="fas fa-check"></i> Completed
                        </button>
                        <button class="btn btn-sm btn-outline" onclick="markFavorite(${anime.id})">
                            <i class="far fa-heart"></i> Favorite
                        </button>
                    </div>
                `;
            } else {
                modal.querySelector('.anime-details').innerHTML = `
                    <p>Failed to load anime details. Please try again later.</p>
                `;
            }
        })
        .catch(error => {
            modal.querySelector('.anime-details').innerHTML = `
                <p>An error occurred while fetching data.</p>
            `;
            console.error('Error fetching anime details:', error);
        });
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Add styles for toast
    const style = document.createElement('style');
    style.textContent = `
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            animation: slideInRight 0.3s, fadeOut 0.3s 2.7s;
            max-width: 300px;
        }
        .toast-success {
            background-color: var(--success);
        }
        .toast-error {
            background-color: var(--error);
        }
        .toast-info {
            background-color: var(--info);
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    `;
    
    document.head.appendChild(style);
    document.body.append
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.remove();
        style.remove();
    }, 3000);
}

function markFavorite(animeId) {
    fetch('update_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `anime_id=${animeId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Added to favorites!', 'success');
        } else {
            showToast(data.message || 'Failed to update favorites', 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred', 'error');
        console.error('Error updating favorites:', error);
    });
}