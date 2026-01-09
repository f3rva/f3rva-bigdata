# F3 RVA Big Data Project

## Project Overview
`f3rva-bigdata` is a web application designed for F3 RVA to track and display workout data ("backblasts"), Area of Operations (AO) details, and PAX (member) statistics. It features a custom PHP backend and a Bootstrap/jQuery frontend.

## Architecture
The project follows a service-oriented architecture without a heavy framework, utilizing namespaces for organization.

*   **Backend:** PHP (Vanilla/Custom)
    *   **API:** REST-like endpoints located in `api/` (v1 and v2).
    *   **Service Layer:** Business logic resides in `service/` (e.g., `WorkoutService.php`).
    *   **Repository Layer:** Database interactions are handled in `repo/` (e.g., `WorkoutRepo.php`).
    *   **Models:** Data entities are defined in `model/`.
    *   **DAO:** Data Access Objects, specifically `ScraperDao.php` for parsing external content.
*   **Frontend:**
    *   **HTML/Templates:** Server-side rendered PHP files (e.g., `index.php`, `ao/detail.php`) using includes from `include/`.
    *   **CSS:** Bootstrap 5.3.3 (`css/f3.css` for custom styles).
    *   **JavaScript:** jQuery 3.7.1, Infinite Scroll, and custom scripts (e.g., `js/f3.home.js`).
*   **Database:** MySQL, accessed via `PDO` in `repo/Database.php`. Connection details are expected in a `settings.php` file in the root (likely ignored).

## Key Files & Directories
*   `index.php`: The main entry point displaying the list of workouts.
*   `auth.php`: Handles authentication logic (checks `Util::isLoggedIn()`).
*   `repo/Database.php`: Singleton class for database connectivity.
*   `service/WorkoutService.php`: Contains core business logic for retrieving and managing workouts.
*   `api/v2/`: Contains the latest API endpoints.
*   `include/`: Shared header, footer, and navigation templates.

## Development & Running
### Local Development
To run the application locally using the built-in PHP server:
```bash
php -S localhost:9000 -d xdebug.mode=debug -d short_open_tag=true
```
*   **Dependencies:** JavaScript and CSS dependencies are vendored in `js/` and `css/`. No `npm` or `composer` installation is required for basic execution.
*   **Configuration:** A `settings.php` file is required in the root directory for database credentials (referenced in `repo/Database.php`).

### Conventions
*   **Namespaces:** All classes are namespaced under `F3` (e.g., `F3\Service`, `F3\Model`).
*   **Paths:** The `__ROOT__` constant is defined in entry files to handle absolute file inclusions.
*   **Indentation:** The project primarily uses tabs for indentation.
*   **API Responses:** JSON format, typically checking `Access-Control-Allow-Origin`.

## Deployment
*   Deployment workflows are defined in `.github/workflows/`.
*   Current configuration suggests deployment via SSH/SCP (though some parts are commented out in `deploy-remote.yml`).
