<?php 
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user data
$stmt = $conn->prepare("SELECT username, email, profile_pic, theme_preference FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get user's anime list
$stmt = $conn->prepare("SELECT * FROM anime_list WHERE user_id = ? ORDER BY status");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$anime_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get flash message if exists
$flashMessage = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script defer src="script.js"></script>
</head>
<body class="<?php echo $user['theme_preference'] === 'dark' ? 'dark-mode' : ''; ?>">
    <div class="dashboard-container">
        <?php if ($flashMessage): ?>
            <div class="flash-message flash-<?php echo $flashMessage['type']; ?>">
                <?php echo $flashMessage['message']; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-header">
            <div class="user-profile">
                <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profile" class="user-avatar">
                <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
            </div>
            
            <div class="header-actions">
                <button class="theme-toggle">
                    <?php if ($user['theme_preference'] === 'dark'): ?>
                        <i class="fas fa-sun"></i>
                    <?php else: ?>
                        <i class="fas fa-moon"></i>
                    <?php endif; ?>
                </button>
                <a href="logout.php" class="btn btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <button class="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="dashboard-tabs">
            <button class="tab-button active" data-target="my-anime">
                <i class="fas fa-list"></i> My Anime
            </button>
            <button class="tab-button" data-target="trending-anime">
                <i class="fas fa-fire"></i> Trending
            </button>
            <button class="tab-button" data-target="favorites">
                <i class="fas fa-heart"></i> Favorites
            </button>
            <button class="tab-button" data-target="profile">
                <i class="fas fa-user"></i> Profile
            </button>
        </div>
        
        <div class="dashboard-content">
            <!-- My Anime List Section -->
            <div id="my-anime" class="content-section active">
                <div class="search-section">
                    <div class="search-bar">
                        <input type="text" class="search-input" placeholder="Search your anime...">
                        <button class="btn btn-sm">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <div class="filter-options">
                        <span class="filter-tag" data-filter="watching">Watching</span>
                        <span class="filter-tag" data-filter="completed">Completed</span>
                        <span class="filter-tag" data-filter="plan_to_watch">Plan to Watch</span>
                        <span class="filter-tag" data-filter="dropped">Dropped</span>
                    </div>
                </div>
                
                <div class="anime-grid">
                    <?php if (count($anime_list) > 0): ?>
                        <?php foreach ($anime_list as $anime): ?>
                            <div class="anime-card" data-status="<?php echo $anime['status']; ?>">
                                <img src="<?php echo $anime['image_url']; ?>" class="anime-card-img" alt="<?php echo htmlspecialchars($anime['title']); ?>">
                                <div class="anime-card-content">
                                    <h3 class="anime-card-title"><?php echo htmlspecialchars($anime['title']); ?></h3>
                                    <p class="anime-card-status">Status: <?php echo ucfirst(str_replace('_', ' ', $anime['status'])); ?></p>
                                    <?php if ($anime['rating']): ?>
                                        <p class="anime-card-status">Rating: <?php echo $anime['rating']; ?>/10</p>
                                    <?php endif; ?>
                                    <div class="anime-card-actions">
                                        <button class="btn btn-sm" onclick="showDetails(<?php echo $anime['anime_id']; ?>)">
                                            <i class="fas fa-info-circle"></i> Details
                                        </button>
                                        <?php if ($anime['favorite']): ?>
                                            <button class="btn btn-sm" style="background: var(--secondary);">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                            <p>You haven't added any anime to your list yet.</p>
                            <p>Check out the trending section to find some anime!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Trending Anime Section -->
            <div id="trending-anime" class="content-section">
                <h2>Trending Anime</h2>
                <p>Discover what's popular right now</p>
                
                <div class="anime-grid">
                    <!-- Will be filled by JavaScript -->
                    <div class="loader" style="margin: 2rem auto;"></div>
                </div>
            </div>
            
            <!-- Favorites Section -->
            <div id="favorites" class="content-section">
                <h2>My Favorites</h2>
                
                <div class="anime-grid">
                    <?php 
                    $favorite_anime = array_filter($anime_list, function($anime) {
                        return $anime['favorite'] == 1;
                    });
                    
                    if (count($favorite_anime) > 0): 
                        foreach ($favorite_anime as $anime):
                    ?>
                        <div class="anime-card">
                            <img src="<?php echo $anime['image_url']; ?>" class="anime-card-img" alt="<?php echo htmlspecialchars($anime['title']); ?>">
                            <div class="anime-card-content">
                                <h3 class="anime-card-title"><?php echo htmlspecialchars($anime['title']); ?></h3>
                                <p class="anime-card-status">Status: <?php echo ucfirst(str_replace('_', ' ', $anime['status'])); ?></p>
                                <?php if ($anime['rating']): ?>
                                    <p class="anime-card-status">Rating: <?php echo $anime['rating']; ?>/10</p>
                                <?php endif; ?>
                                <div class="anime-card-actions">
                                    <button class="btn btn-sm" onclick="showDetails(<?php echo $anime['anime_id']; ?>)">
                                        <i class="fas fa-info-circle"></i> Details
                                    </button>
                                    <button class="btn btn-sm" style="background: var(--secondary);" onclick="markFavorite(<?php echo $anime['anime_id']; ?>)">
                                        <i class="fas fa-heart-broken"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    else: 
                    ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 2rem;">
                            <p>You haven't marked any anime as favorite yet.</p>
                            <p>Click the heart icon on any anime to add it to your favorites!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Profile Section -->
            <div id="profile" class="content-section">
                <h2>Profile Settings</h2>
                
                <div class="profile-section">
                    <div class="profile-header">
                        <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profile" class="profile-pic">
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo count($anime_list); ?></div>
                            <div class="stat-label">Total Anime</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php 
                                echo count(array_filter($anime_list, function($anime) {
                                    return $anime['status'] == 'completed';
                                }));
                                ?>
                            </div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php echo count($favorite_anime); ?>
                            </div>
                            <div class="stat-label">Favorites</div>
                        </div>
                    </div>
                    
                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="input-group">
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            <label><i class="fas fa-user"></i> Username</label>
                        </div>
                        
                        <div class="input-group">
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <label><i class="fas fa-envelope"></i> Email</label>
                        </div>
                        
                        <div class="input-group">
                            <input type="file" id="profile-image" name="profile_image" accept="image/*">
                            <label for="profile-image"><i class="fas fa-image"></i> Profile Picture</label>
                        </div>
                        
                        <div class="input-group">
                            <input type="password" name="current_password">
                            <label><i class="fas fa-lock"></i> Current Password</label>
                            <span class="toggle-password"><i class="far fa-eye"></i></span>
                        </div>
                        
                        <div class="input-group">
                            <input type="password" name="new_password">
                            <label><i class="fas fa-key"></i> New Password (leave blank to keep current)</label>
                            <span class="toggle-password"><i class="far fa-eye"></i></span>
                        </div>
                        
                        <div class="input-group">
                            <input type="password" name="confirm_password">
                            <label><i class="fas fa-check"></i> Confirm New Password</label>
                            <span class="toggle-password"><i class="far fa-eye"></i></span>
                        </div>
                        
                        <button type="submit" class="btn">Update Profile <i class="fas fa-save"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Fetch Anime List in Real-Time
            function fetchAnimeList() {
                fetch('get_anime_list.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const animeGrid = document.querySelector('.anime-grid');
                            animeGrid.innerHTML = '';
                            data.anime_list.forEach(anime => {
                                const animeCard = document.createElement('div');
                                animeCard.className = 'anime-card';
                                animeCard.dataset.status = anime.status;
                                animeCard.innerHTML = `
                                    <img src="${anime.image_url}" class="anime-card-img" alt="${anime.title}">
                                    <div class="anime-card-content">
                                        <h3 class="anime-card-title">${anime.title}</h3>
                                        <p class="anime-card-status">Status: ${anime.status}</p>
                                        ${anime.rating ? `<p class="anime-card-status">Rating: ${anime.rating}/10</p>` : ''}
                                        <div class="anime-card-actions">
                                            <button class="btn btn-sm" onclick="showDetails(${anime.anime_id})">
                                                <i class="fas fa-info-circle"></i> Details
                                            </button>
                                            ${anime.favorite ? `<button class="btn btn-sm" style="background: var(--secondary);">
                                                <i class="fas fa-heart"></i>
                                            </button>` : ''}
                                        </div>
                                    </div>
                                `;
                                animeGrid.appendChild(animeCard);
                            });
                        } else {
                            console.error('Error fetching anime list:', data.message);
                        }
                    })
                    .catch(error => console.error('Error fetching anime list:', error));
            }

            // Fetch User Profile in Real-Time
            function fetchUserProfile() {
                fetch('get_user_profile.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector('.user-avatar').src = 'uploads/' + data.profile_pic;
                            document.querySelector('.dashboard-header h2').innerText = 'Welcome, ' + data.username + '!';
                        } else {
                            console.error('Error fetching user profile:', data.message);
                        }
                    })
                    .catch(error => console.error('Error fetching user profile:', error));
            }

            // Initialize Real-Time Fetching
            setInterval(fetchAnimeList, 5000); // Fetch anime list every 5 seconds
            setInterval(fetchUserProfile, 5000); // Fetch user profile every 5 seconds

            // Initial Fetch
            fetchAnimeList();
            fetchUserProfile();
        });
    </script>
</body>
</html>