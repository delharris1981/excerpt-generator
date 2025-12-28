# IMPLEMENTATION PLAN: Phase 5 - Public GitHub Updates

## 1. Objective
Enable the plugin to check for updates from a public GitHub repository and notify the administrator via the WordPress Plugins dashboard.

## 2. Proposed Changes
| File Path | Description of Modification |
| :--- | :--- |
| `src/UpdateManager.php` | New class to handle GitHub API requests, version comparison, and WP update hooks. |
| `luhn-summarizer.php` | Update initialization to bootstrap the `UpdateManager` class. |

## 3. Risk Assessment
- **Complexity**: Medium
- **Potential Breaking Changes**: None (strictly informative UI).
- **Dependencies**: WordPress `wp_remote_get()` and Transients API.
- **Note**: Requires a valid GitHub repository URL.

## 4. Implementation Steps
1. [x] **Class Creation:** Create `src/UpdateManager.php` with PSR-4 namespace `LuhnSummarizer`.
2. [x] **GitHub API Integration:** Implement a method to fetch the latest release from `https://api.github.com/repos/{user}/{repo}/releases/latest`.
3. [x] **Caching:** Use the Transients API to cache the update check for 12 hours to avoid GitHub rate limits.
4. [x] **Plugin Update Hooks:** Hook into `site_transient_update_plugins` to inject the new version data if an update is available.
5. [x] **Plugin Information Hook:** Hook into `plugins_api` to provide "View Details" information from the GitHub release.
6. [x] **Bootstrapping:** Instantiate `LuhnSummarizer\UpdateManager` in the main plugin file.

## 5. Verification Plan
- [x] **API Connectivity:** Verified request structure and headers.
- [x] **Version Detection:** Verified comparison logic.
- [x] **Cache Check:** Verified Transients API integration.

## 6. Rollback Strategy
- **Primary Reversion:** Remove `UpdateManager.php` and its instantiation in `luhn-summarizer.php`.
- **State Recovery:** Clear the transient using `delete_site_transient('update_plugins')`.

## 7. Status
**Current Status:** ✅ PROJECT COMPLETE
