<?php
session_start();
require_once('auth_check.php');

// Ensure user is authenticated
if (!isAuthenticated()) {
    redirectToLogin();
    exit();
}

$video = $_GET['video'] ?? '';
$video_id = $_GET['video_id'] ?? '';

if (empty($video) || empty($video_id)) {
    header("Location: index.php");
    exit();
}

// Debug logging
error_log("Received video path: " . $video);
error_log("Received video ID: " . $video_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Details - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .details-container {
            max-width: 800px;
            margin: 4rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .details-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .details-header h1 {
            font-size: 2rem;
            color: var(--white);
            margin-bottom: 0.5rem;
        }

        .details-header p {
            color: var(--gray-400);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--white);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select,
        .select2-container--default .select2-selection--multiple {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--gray-700);
            border-radius: 0.5rem;
            color: var(--white);
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group select option {
            background: var(--gray-800);
            color: var(--white);
            padding: 0.75rem;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Select2 Dark Theme */
        .select2-container--default .select2-selection--multiple {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--gray-700);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.25rem 0.5rem;
            margin: 0.25rem;
            border-radius: 0.25rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 0.5rem;
        }

        .select2-dropdown {
            background-color: var(--gray-800);
            border: 1px solid var(--gray-700);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: var(--gray-700);
            color: var(--white);
            border: 1px solid var(--gray-600);
        }

        .select2-container--default .select2-results__option {
            color: var(--white);
            padding: 0.5rem;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary);
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: var(--primary-dark);
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 768px) {
            .details-container {
                margin: 2rem 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include('components/header.php'); ?>
    
    <div class="details-container">
        <div class="details-header">
            <h1>Video Details</h1>
            <p>Add information about your video</p>
        </div>
        <form id="videoDetailsForm">
            <input type="hidden" id="videoPath" value="<?php echo htmlspecialchars($video); ?>">
            <input type="hidden" id="videoId" value="<?php echo htmlspecialchars($video_id); ?>">
            
            <div class="form-group">
                <label for="description"><i class="fas fa-align-left"></i> Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="category"><i class="fas fa-folder"></i> Category</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Penetrating with Languages">Penetrating with Languages</option>
                    <option value="Say Yes to Kids">Say Yes to Kids</option>
                    <option value="No One Left Behind">No One Left Behind</option>
                    <option value="Teens Teevolution">Teens Teevolution</option>
                    <option value="Youths Aglow">Youths Aglow</option>
                    <option value="Every Minister An Outreach">Every Minister An Outreach</option>
                    <option value="Digital">Digital</option>
                    <option value="Dignitaries Distribution">Dignitaries Distribution</option>
                    <option value="Strategic Distributions">Strategic Distributions</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="tags"><i class="fas fa-tags"></i> Tags</label>
                <select id="tags" name="tags[]" multiple="multiple" required>
                    <!-- Tags will be loaded from data/tags.json -->
                </select>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Save Details
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for tags with custom data
            const predefinedTags = {
                "Vehicles": ["Planes", "Buses", "Trains"],
                "Transport Terminals": ["Airports", "Bus Terminals", "Train Terminals"],
                "Homes": ["Apartment Blocks", "Detached Houses", "Residential Estates", "Neighbourhoods"],
                "Communities": ["The inner cities", "The Hinterlands", "Communities in crisis"],
                "Hospitals": ["Teaching Hospitals", "Clinics", "Paediatric Hospitals"],
                "Farms & Markets": [],
                "Public Spaces": ["Parks", "Malls", "Shops"],
                "Hospitality Businesses": ["Hotels", "Restaurants", "Event Centres"],
                "Public Buildings": ["Government offices", "Courthouses", "Police stations"],
                "Offices": ["Companies", "Factories"],
                "Dangerous Jobs": ["Mines", "Offshore Rigs"],
                "Military Bases": [],
                "Iconic Landmarks/Tourist Attractions": [],
                "People With Special Needs": ["Prisons", "Orphanages"]
            };

            // Flatten tags for Select2
            let allTags = [];
            Object.entries(predefinedTags).forEach(([category, tags]) => {
                allTags.push(category);
                if (tags.length > 0) {
                    allTags = allTags.concat(tags);
                }
            });

            $('#tags').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                placeholder: 'Add tags...',
                theme: 'default',
                data: allTags.map(tag => ({ id: tag, text: tag }))
            });

            // Handle form submission
            $('#videoDetailsForm').on('submit', function(e) {
                e.preventDefault();
                
                const data = {
                    videoPath: $('#videoPath').val(),
                    video_id: $('#videoId').val(),
                    description: $('#description').val(),
                    category: $('#category').val(),
                    tags: $('#tags').val()
                };

                // Send data to server
                fetch('save_video_details.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'index.php';
                    } else {
                        alert('Error saving video details: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving video details. Please try again.');
                });
            });
        });
    </script>
</body>
</html>
